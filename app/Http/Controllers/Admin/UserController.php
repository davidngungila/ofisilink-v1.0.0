<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class UserController extends Controller
{
    /**
     * Display a listing of users with advanced filtering and search.
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'primaryDepartment']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        // Filter by department
        if ($request->has('department') && $request->department) {
            $query->where('primary_department_id', $request->department);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['name', 'email', 'employee_id', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->get('per_page', 20);
        $users = $query->paginate($perPage)->withQueryString();
        $roles = Role::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        // Statistics
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'by_department' => User::select('primary_department_id', DB::raw('count(*) as count'))
                ->whereNotNull('primary_department_id')
                ->groupBy('primary_department_id')
                ->with('primaryDepartment')
                ->get(),
            'by_role' => DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->select('roles.name', 'roles.display_name', DB::raw('count(*) as count'))
                ->groupBy('roles.id', 'roles.name', 'roles.display_name')
                ->get(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ]
            ]);
        }

        return view('admin.users.index', compact('users', 'roles', 'departments', 'stats'));
    }

    /**
     * Show the form for creating a new user.
     * IMPORTANT: Redirects to employee registration page to ensure all users have employee details.
     */
    public function create()
    {
        // Redirect to employee registration page - all users MUST have employee details
        return redirect()->route('modules.hr.employees')->with('info', 'To add a new user, please use the Employee Registration form. All users must have complete employee details before they can access the system.');
    }

    /**
     * Store a newly created user in database.
     * IMPORTANT: This method is disabled - all users must be created through Employee Registration.
     */
    public function store(Request $request)
    {
        // Redirect to employee registration - users cannot be created without employee details
        return redirect()->route('modules.hr.employees')
            ->with('error', 'Direct user creation is not allowed. Please use the Employee Registration form to add new users. All users must have complete employee details.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'departments', 'primaryDepartment', 'activityLogs' => function($q) {
            $q->latest()->limit(10);
        }]);
        
        // Return JSON if AJAX request
        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'employee_id' => $user->employee_id,
                    'phone' => $user->phone,
                    'hire_date' => $user->hire_date?->format('Y-m-d'),
                    'is_active' => $user->is_active,
                    'primary_department' => $user->primaryDepartment ? [
                        'id' => $user->primaryDepartment->id,
                        'name' => $user->primaryDepartment->name,
                    ] : null,
                    'roles' => $user->roles->map(function($role) {
                        return [
                            'id' => $role->id,
                            'name' => $role->name,
                            'display_name' => $role->display_name,
                        ];
                    }),
                    'departments' => $user->departments->map(function($dept) {
                        return [
                            'id' => $dept->id,
                            'name' => $dept->name,
                            'is_primary' => $dept->pivot->is_primary ?? false,
                        ];
                    }),
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);
        }
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();
        $user->load(['roles', 'departments', 'primaryDepartment', 'employee']);
        
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    /**
     * Send password reset SMS immediately
     */
    public function sendPasswordResetSMS(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        // Only System Admin can reset passwords
        if (!$currentUser || !$currentUser->hasRole('System Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only System Administrators can reset passwords.'
            ], 403);
        }
        
        // Generate password
        $uppercase = Str::random(4);
        $lowercase = Str::random(4);
        $numbers = rand(1000, 9999);
        $newPassword = str_shuffle($uppercase . $lowercase . $numbers);
        
        // Hash password for storage
        $hashedPassword = Hash::make($newPassword);
        
        // Save password to database immediately
        DB::beginTransaction();
        try {
            $oldPasswordHash = $user->password; // Store old hash for logging
            $user->update(['password' => $hashedPassword]);
            DB::commit();
            
            // Log password reset activity with full details
            ActivityLogService::logPasswordReset($user, $currentUser, [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'employee_id' => $user->employee_id,
                'admin_id' => $currentUser->id,
                'admin_name' => $currentUser->name,
                'admin_email' => $currentUser->email,
                'password_reset_method' => 'auto_generate',
                'password_length' => strlen($newPassword),
            ]);
            
            Log::info('Password reset - password saved to database immediately', [
                'user_id' => $user->id,
                'admin_id' => $currentUser->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to save password to database', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save password: ' . $e->getMessage()
            ], 500);
        }
        
        // Send SMS immediately
        $smsStatus = [
            'staff_sms_sent' => false,
            'admin_sms_sent' => false,
            'staff_sms_error' => null,
            'admin_sms_error' => null
        ];
        
        try {
            $notificationService = new NotificationService();
            
            // SMS to the affected staff member
            $staffPhone = $user->mobile ?? $user->phone;
            if ($staffPhone) {
                $staffMessage = "Hello {$user->name},\n\nYour password has been reset by System Administrator.\n\nYour new login credentials:\nEmail: {$user->email}\nPassword: {$newPassword}\n\nPlease login and change your password immediately.\n\nOfisiLink System";
                
                $smsSent = $notificationService->sendSMS($staffPhone, $staffMessage);
                $smsStatus['staff_sms_sent'] = $smsSent;
                if ($smsSent) {
                    Log::info('Password reset SMS sent to staff member', [
                        'user_id' => $user->id,
                        'phone' => $staffPhone
                    ]);
                } else {
                    $smsStatus['staff_sms_error'] = 'Failed to send SMS to staff member. Please check phone number and SMS settings.';
                    Log::warning('Failed to send password reset SMS to staff member', [
                        'user_id' => $user->id,
                        'phone' => $staffPhone
                    ]);
                }
            } else {
                $smsStatus['staff_sms_error'] = 'Staff member has no phone number registered.';
                Log::warning('Cannot send password reset SMS to staff - no phone number', [
                    'user_id' => $user->id
                ]);
            }

            // SMS to the admin who reset the password
            $adminPhone = $currentUser->mobile ?? $currentUser->phone;
            if ($adminPhone) {
                $employeeId = $user->employee_id ?? 'N/A';
                $adminMessage = "Hello {$currentUser->name},\n\nYou have successfully reset the password for:\nUser: {$user->name} ({$user->email})\nEmployee ID: {$employeeId}\n\nNew Password: {$newPassword}\n\nThis password has been sent to the user via SMS.\n\nOfisiLink System";
                
                $smsSent = $notificationService->sendSMS($adminPhone, $adminMessage);
                $smsStatus['admin_sms_sent'] = $smsSent;
                if ($smsSent) {
                    Log::info('Password reset confirmation SMS sent to admin', [
                        'admin_id' => $currentUser->id,
                        'phone' => $adminPhone,
                        'affected_user_id' => $user->id
                    ]);
                } else {
                    $smsStatus['admin_sms_error'] = 'Failed to send SMS to admin. Please check phone number and SMS settings.';
                    Log::warning('Failed to send password reset confirmation SMS to admin', [
                        'admin_id' => $currentUser->id,
                        'phone' => $adminPhone
                    ]);
                }
            } else {
                $smsStatus['admin_sms_error'] = 'Admin has no phone number registered.';
                Log::warning('Cannot send password reset confirmation SMS to admin - no phone number', [
                    'admin_id' => $currentUser->id
                ]);
            }
        } catch (\Exception $e) {
            $smsStatus['staff_sms_error'] = 'SMS sending error: ' . $e->getMessage();
            $smsStatus['admin_sms_error'] = 'SMS sending error: ' . $e->getMessage();
            Log::error('Error sending password reset SMS notifications', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        return response()->json([
            'success' => true,
            'password' => $newPassword,
            'sms_status' => $smsStatus,
            'email_status' => $emailStatus,
            'message' => 'Password generated and notifications sent via SMS' . ($emailStatus['staff_email_sent'] ? ' and email' : '') . '.'
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        // Check if password reset is requested - only System Admin can reset passwords
        if (!empty($request->input('auto_generate_password'))) {
            if (!$currentUser || !$currentUser->hasRole('System Admin')) {
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only System Administrators can reset passwords.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'Only System Administrators can reset passwords.')->withInput();
            }
        }

        // Only allow editing: password, email, phone, roles, and status
        // Make fields independent - allow partial updates
        $validationRules = [
            'phone' => 'nullable|string|max:255',
            'auto_generate_password' => 'boolean',
            'phone_otp_verified' => 'boolean',
            'generated_password_value' => 'nullable|string',
        ];
        
        // Email is required only if provided
        if ($request->has('email') && $request->email) {
            $validationRules['email'] = ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)];
        }
        
        // Roles required only for System Admin
        if ($currentUser && $currentUser->hasRole('System Admin')) {
            $validationRules['roles'] = 'required|array|min:1';
            $validationRules['roles.*'] = 'exists:roles,id';
        } else {
            $validationRules['roles'] = 'nullable|array';
            $validationRules['roles.*'] = 'exists:roles,id';
        }
        
        $validationRules['is_active'] = 'boolean';
        
        $validated = $request->validate($validationRules);
        
        // Check phone OTP verification for staff editing their own phone
        if ($currentUser && $currentUser->id == $user->id && !$currentUser->hasRole('System Admin')) {
            $phoneChanged = $request->has('phone') && $request->phone != $user->phone;
            if ($phoneChanged && empty($request->input('phone_otp_verified'))) {
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You must verify your current phone number with OTP before changing it.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'You must verify your current phone number with OTP before changing it.')->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $updateData = [];
            
            // Only update email if provided
            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }
            
            // Only update phone if provided
            if (isset($validated['phone'])) {
                $updateData['phone'] = $validated['phone'] ?? null;
            }

            $newPassword = null;
            $passwordReset = false;

            // Check if password was already reset (via sendPasswordResetSMS endpoint)
            // Password is already saved in DB, so we just mark it as reset for logging
            if (!empty($request->input('auto_generate_password'))) {
                // Password was already saved when button was clicked
                // We don't need to save it again, just mark that reset was done
                $passwordReset = true;
                $newPassword = null; // Not needed since already saved
                
                Log::info('Password reset form submitted - password already saved', [
                    'user_id' => $user->id,
                    'admin_id' => $currentUser->id
                ]);
            }

            // Update status
            if (isset($validated['is_active'])) {
                $updateData['is_active'] = $validated['is_active'];
            }

            // Store old values for logging
            $oldValues = [];
            if (isset($validated['email']) && $validated['email'] != $user->email) {
                $oldValues['email'] = $user->email;
            }
            if (isset($validated['phone']) && $validated['phone'] != $user->phone) {
                $oldValues['phone'] = $user->phone;
            }
            if (isset($validated['is_active']) && $validated['is_active'] != $user->is_active) {
                $oldValues['is_active'] = $user->is_active;
            }
            
            // Only update if there's data to update
            if (!empty($updateData)) {
                $user->update($updateData);
                
                // Log user update with details
                $newValues = [];
                foreach ($updateData as $key => $value) {
                    $newValues[$key] = $value;
                }
                
                ActivityLogService::logUpdated($user, $oldValues, $newValues, 
                    "Updated user {$user->name} ({$user->email})", [
                        'updated_by' => $currentUser->id,
                        'updated_by_name' => $currentUser->name,
                        'fields_updated' => array_keys($updateData),
                    ]);
            }

            // Get old roles for comparison
            $oldRoleIds = $user->roles->pluck('id')->toArray();
            $oldRoleNames = $user->roles->pluck('name')->toArray();
            $newRoleIds = isset($validated['roles']) && is_array($validated['roles']) ? $validated['roles'] : [];
            sort($oldRoleIds);
            sort($newRoleIds);
            $rolesChanged = $oldRoleIds !== $newRoleIds;

            // Update roles - Only System Admin can change roles
            if ($rolesChanged) {
                if (!$currentUser || !$currentUser->hasRole('System Admin')) {
                    DB::rollback();
                    if (request()->expectsJson() || request()->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only System Administrators can change user roles.'
                        ], 403);
                    }
                    return redirect()->back()->with('error', 'Only System Administrators can change user roles.')->withInput();
                }
            }

            // Update roles only if provided and user is System Admin
            if (isset($validated['roles']) && is_array($validated['roles']) && count($validated['roles']) > 0) {
                $user->roles()->sync([]);
                $newRoles = [];
                $newRoleNames = [];
                foreach ($validated['roles'] as $roleId) {
                    $role = Role::find($roleId);
                    if ($role) {
                        $newRoles[] = $role;
                        $newRoleNames[] = $role->name;
                        $user->roles()->attach($roleId, [
                            'is_active' => true,
                            'assigned_at' => now(),
                        ]);
                    }
                }
                
                // Log role change with full details
                if ($rolesChanged) {
                    ActivityLogService::logRoleChange($user, $oldRoleNames, $newRoleNames, $currentUser, [
                        'old_role_ids' => $oldRoleIds,
                        'new_role_ids' => $newRoleIds,
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'user_name' => $user->name,
                    ]);
                }
            } else {
                // If no roles provided, keep existing roles
                $newRoles = $user->roles;
            }

            DB::commit();

            // Track SMS sending status
            $smsStatus = [
                'staff_sms_sent' => false,
                'admin_sms_sent' => false,
                'staff_sms_error' => null,
                'admin_sms_error' => null
            ];

            // Send SMS notification if roles were changed
            if ($rolesChanged) {
                try {
                    $notificationService = new NotificationService();
                    
                    // Get role names for SMS
                    $roleNames = collect($newRoles)->pluck('display_name')->join(', ');
                    
                    // SMS to the affected user
                    $userPhone = $user->mobile ?? $user->phone;
                    if ($userPhone) {
                        $userMessage = "Hello {$user->name},\n\nYour account roles have been updated by System Administrator.\n\nNew Roles: {$roleNames}\n\nIf you did not request this change, please contact your administrator immediately.\n\nOfisiLink System";
                        
                        $notificationService->sendSMS($userPhone, $userMessage);
                    }

                    // SMS to the admin who made the change
                    $adminPhone = $currentUser->mobile ?? $currentUser->phone;
                    if ($adminPhone) {
                        $employeeId = $user->employee_id ?? 'N/A';
                        $adminMessage = "Hello {$currentUser->name},\n\nYou have successfully updated roles for:\nUser: {$user->name} ({$user->email})\nEmployee ID: {$employeeId}\n\nNew Roles: {$roleNames}\n\nThis change has been sent to the user via SMS.\n\nOfisiLink System";
                        
                        $notificationService->sendSMS($adminPhone, $adminMessage);
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending role change SMS notifications', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // SMS already sent when button was clicked, so we don't send again here
            // Just log that password was saved
            if ($passwordReset && $newPassword) {
                Log::info('Password reset completed - password saved to database', [
                    'user_id' => $user->id,
                    'admin_id' => $currentUser->id,
                    'sms_sent_earlier' => true
                ]);
            }
            
            // Return JSON for AJAX requests
            if (request()->expectsJson() || request()->ajax()) {
                $message = 'User updated successfully.';
                if ($passwordReset) {
                    $smsInfo = [];
                    if ($smsStatus['staff_sms_sent']) {
                        $smsInfo[] = 'SMS sent to staff member';
                    } else {
                        $smsInfo[] = 'SMS to staff: ' . ($smsStatus['staff_sms_error'] ?? 'Not sent');
                    }
                    if ($smsStatus['admin_sms_sent']) {
                        $smsInfo[] = 'SMS sent to admin';
                    } else {
                        $smsInfo[] = 'SMS to admin: ' . ($smsStatus['admin_sms_error'] ?? 'Not sent');
                    }
                    $message .= ' ' . implode('. ', $smsInfo) . '.';
                }
                if ($rolesChanged) {
                    $message .= ' Role change SMS notifications have been sent to both the admin and the user.';
                }
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'user' => $user->fresh(['roles', 'primaryDepartment']),
                    'password_reset' => $passwordReset,
                    'new_password' => $passwordReset ? $newPassword : null,
                    'sms_status' => $smsStatus,
                    'roles_changed' => $rolesChanged
                ]);
            }
            
            $redirectMessage = 'User updated successfully.';
            if ($passwordReset) {
                $redirectMessage .= ' Password reset completed.';
                if ($newPassword) {
                    // Store password in session to display
                    session()->flash('generated_password', $newPassword);
                    session()->flash('password_user_name', $user->name);
                    session()->flash('password_user_email', $user->email);
                    session()->flash('sms_status', $smsStatus);
                }
            }
            if ($rolesChanged) {
                $redirectMessage .= ' Role change SMS notifications have been sent to both the admin and the user.';
            }
            
            return redirect()->route('admin.users.edit', $user)->with('success', $redirectMessage);
        } catch (\Exception $e) {
            DB::rollback();
            
            // Return JSON for AJAX requests
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update user: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deletion of super admin
        if ($user->hasRole('System Admin')) {
            return redirect()->back()->with('error', 'Cannot delete System Administrator.');
        }

        try {
            $user->delete();
            
            // Return JSON for AJAX requests
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully.'
                ]);
            }
            
            return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            // Return JSON for AJAX requests
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        try {
            $user->update(['is_active' => !$user->is_active]);
            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully.',
                'is_active' => $user->is_active
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user status.'
            ], 500);
        }
    }

    /**
     * Bulk activate users
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            $count = User::whereIn('id', $request->user_ids)
                ->where('is_active', false)
                ->update(['is_active' => true]);

            return response()->json([
                'success' => true,
                'message' => "Successfully activated {$count} user(s).",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk deactivate users
     */
    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            // Prevent deactivating system admin
            $count = User::whereIn('id', $request->user_ids)
                ->where('is_active', true)
                ->whereDoesntHave('roles', function($q) {
                    $q->where('name', 'System Admin');
                })
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => "Successfully deactivated {$count} user(s).",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            // Prevent deleting system admin
            $users = User::whereIn('id', $request->user_ids)
                ->whereDoesntHave('roles', function($q) {
                    $q->where('name', 'System Admin');
                })
                ->get();

            $count = $users->count();
            foreach ($users as $user) {
                $user->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} user(s).",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export users to CSV with advanced options including passwords
     */
    public function export(Request $request)
    {
        $currentUser = Auth::user();
        
        // Only System Admin can export with passwords
        $includePasswords = $request->has('include_passwords') && $request->include_passwords == '1';
        if ($includePasswords && (!$currentUser || !$currentUser->hasRole('System Admin'))) {
            return response()->json([
                'success' => false,
                'message' => 'Only System Administrators can export user passwords.'
            ], 403);
        }

        $query = User::with(['roles', 'primaryDepartment', 'employee']);

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('employee_id', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('role') && $request->role) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.id', $request->role);
            });
        }

        if ($request->has('department') && $request->department) {
            $query->where('primary_department_id', $request->department);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === 'active');
        }

        // Additional filters
        if ($request->has('email_verified') && $request->email_verified !== '') {
            if ($request->email_verified == '1') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($request->has('created_from') && $request->created_from) {
            $query->where('created_at', '>=', $request->created_from);
        }

        if ($request->has('created_to') && $request->created_to) {
            $query->where('created_at', '<=', $request->created_to . ' 23:59:59');
        }

        $users = $query->get();

        $format = $request->get('format', 'csv'); // csv or excel
        $filename = 'users_export_' . date('Y-m-d_His') . '.' . ($format === 'excel' ? 'xlsx' : 'csv');
        
        // Build headers array
        $headers = [
            'ID', 'Name', 'Email', 'Employee ID', 'Phone', 'Mobile',
            'Department', 'Position', 'Roles', 'Status', 
            'Email Verified', 'Email Verified At',
            'Marital Status', 'Date of Birth', 'Gender', 'Nationality', 'Address',
            'Hire Date', 'Created At', 'Updated At'
        ];

        if ($includePasswords) {
            $headers[] = 'Password (Hashed)';
            $headers[] = 'Password (Plain - Temporary)';
        }

        if ($format === 'excel') {
            return $this->exportToExcel($users, $filename, $headers, $includePasswords);
        }

        // CSV Export
        $csvHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Add BOM for UTF-8 Excel compatibility
        $callback = function() use ($users, $headers, $includePasswords) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, $headers);

            // Data
            foreach ($users as $user) {
                $row = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->employee_id ?? 'N/A',
                    $user->phone ?? 'N/A',
                    $user->mobile ?? 'N/A',
                    $user->primaryDepartment->name ?? 'N/A',
                    ($user->employee && $user->employee->position) ? $user->employee->position->name : 'N/A',
                    $user->roles->pluck('display_name')->join(', '),
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : 'N/A',
                    $user->marital_status ?? 'N/A',
                    $user->date_of_birth ? $user->date_of_birth->format('Y-m-d') : 'N/A',
                    $user->gender ?? 'N/A',
                    $user->nationality ?? 'N/A',
                    $user->address ?? 'N/A',
                    $user->hire_date ? $user->hire_date->format('Y-m-d') : 'N/A',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->updated_at->format('Y-m-d H:i:s'),
                ];

                if ($includePasswords) {
                    $row[] = $user->password; // Hashed password
                    // Generate temporary password for export (same as default)
                    $row[] = 'welcome123'; // Default password note
                }

                fputcsv($file, $row);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $csvHeaders);
    }

    /**
     * Export to Excel format (using PhpSpreadsheet if available, otherwise CSV)
     */
    private function exportToExcel($users, $filename, $headers, $includePasswords)
    {
        try {
            // Create new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator('OfisiLink System')
                ->setTitle('Users Export')
                ->setSubject('Users Data Export')
                ->setDescription('Exported users data from OfisiLink system');
            
            // Set header row with styling
            $headerRow = 1;
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $headerRow, $header);
                $sheet->getStyle($col . $headerRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                $col++;
            }
            
            // Set column widths
            $sheet->getColumnDimension('A')->setWidth(8);  // ID
            $sheet->getColumnDimension('B')->setWidth(25); // Name
            $sheet->getColumnDimension('C')->setWidth(30); // Email
            $sheet->getColumnDimension('D')->setWidth(15); // Employee ID
            $sheet->getColumnDimension('E')->setWidth(15); // Phone
            $sheet->getColumnDimension('F')->setWidth(15); // Mobile
            $sheet->getColumnDimension('G')->setWidth(20); // Department
            $sheet->getColumnDimension('H')->setWidth(20); // Position
            $sheet->getColumnDimension('I')->setWidth(25); // Roles
            $sheet->getColumnDimension('J')->setWidth(12); // Status
            $sheet->getColumnDimension('K')->setWidth(15); // Email Verified
            $sheet->getColumnDimension('L')->setWidth(20); // Email Verified At
            $sheet->getColumnDimension('M')->setWidth(15); // Marital Status
            $sheet->getColumnDimension('N')->setWidth(15); // Date of Birth
            $sheet->getColumnDimension('O')->setWidth(10); // Gender
            $sheet->getColumnDimension('P')->setWidth(15); // Nationality
            $sheet->getColumnDimension('Q')->setWidth(30); // Address
            $sheet->getColumnDimension('R')->setWidth(15); // Hire Date
            $sheet->getColumnDimension('S')->setWidth(20); // Created At
            $sheet->getColumnDimension('T')->setWidth(20); // Updated At
            if ($includePasswords) {
                $sheet->getColumnDimension('U')->setWidth(30); // Password (Hashed)
                $sheet->getColumnDimension('V')->setWidth(25); // Password (Plain)
            }
            
            // Freeze header row
            $sheet->freezePane('A2');
            
            // Add data rows
            $row = 2;
            foreach ($users as $user) {
                try {
                    // Safely get department name
                    $departmentName = 'N/A';
                    if ($user->primaryDepartment) {
                        $departmentName = $user->primaryDepartment->name ?? 'N/A';
                    } elseif ($user->relationLoaded('primaryDepartment') && $user->primaryDepartment) {
                        $departmentName = $user->primaryDepartment->name ?? 'N/A';
                    }
                    
                    // Safely get position name
                    $positionName = 'N/A';
                    if ($user->employee && $user->employee->position) {
                        $positionName = $user->employee->position->name ?? 'N/A';
                    }
                    
                    // Safely get roles
                    $roles = 'N/A';
                    if ($user->relationLoaded('roles') && $user->roles) {
                        $roleNames = $user->roles->pluck('display_name')->filter();
                        if ($roleNames->isEmpty()) {
                            $roleNames = $user->roles->pluck('name')->filter();
                        }
                        $roles = $roleNames->join(', ') ?: 'N/A';
                    }
                    
                    $col = 'A';
                    $data = [
                        $user->id ?? 'N/A',
                        $user->name ?? 'N/A',
                        $user->email ?? 'N/A',
                        $user->employee_id ?? 'N/A',
                        $user->phone ?? 'N/A',
                        $user->mobile ?? 'N/A',
                        $departmentName,
                        $positionName,
                        $roles,
                        $user->is_active ? 'Active' : 'Inactive',
                        $user->email_verified_at ? 'Yes' : 'No',
                        $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : 'N/A',
                        $user->marital_status ?? 'N/A',
                        ($user->date_of_birth && is_object($user->date_of_birth)) ? $user->date_of_birth->format('Y-m-d') : ($user->date_of_birth ?? 'N/A'),
                        $user->gender ?? 'N/A',
                        $user->nationality ?? 'N/A',
                        $user->address ?? 'N/A',
                        ($user->hire_date && is_object($user->hire_date)) ? $user->hire_date->format('Y-m-d') : ($user->hire_date ?? 'N/A'),
                        ($user->created_at && is_object($user->created_at)) ? $user->created_at->format('Y-m-d H:i:s') : ($user->created_at ?? 'N/A'),
                        ($user->updated_at && is_object($user->updated_at)) ? $user->updated_at->format('Y-m-d H:i:s') : ($user->updated_at ?? 'N/A'),
                    ];

                    if ($includePasswords) {
                        $data[] = $user->password ?? 'N/A'; // Hashed password
                        $data[] = 'welcome123'; // Default password note
                    }
                    
                    // Write data to cells
                    foreach ($data as $value) {
                        $sheet->setCellValue($col . $row, $value);
                        $col++;
                    }
                    
                    // Apply alternating row colors for better readability
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':' . $col . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F2F2F2'],
                            ],
                        ]);
                    }
                    
                    // Add borders to cells
                    $lastCol = $includePasswords ? 'V' : 'T';
                    $sheet->getStyle('A' . $row . ':' . $lastCol . $row)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                    ]);
                    
                    $row++;
                } catch (\Exception $e) {
                    \Log::error('Error exporting user row', [
                        'user_id' => $user->id ?? 'unknown',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Continue with next user instead of breaking
                    continue;
                }
            }
            
            // Auto-filter on header row
            $lastCol = $includePasswords ? 'V' : 'T';
            $sheet->setAutoFilter('A1:' . $lastCol . '1');
            
            // Create writer and save to temporary file
            $writer = new Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'users_export_');
            $writer->save($tempFile);
            
            // Return file as download
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('Error in exportToExcel', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to CSV if PhpSpreadsheet fails
            return $this->exportToCSV($users, $filename, $headers, $includePasswords);
        }
    }
    
    /**
     * Fallback CSV export method
     */
    private function exportToCSV($users, $filename, $headers, $includePasswords)
    {
        $csvHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . str_replace('.xlsx', '.csv', $filename) . '"',
        ];

        $callback = function() use ($users, $headers, $includePasswords) {
            $file = fopen('php://output', 'w');
            
            if (!$file) {
                throw new \Exception('Failed to open output stream');
            }
            
            // Add UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, $headers);

            // Data
            foreach ($users as $user) {
                try {
                    // Safely get department name
                    $departmentName = 'N/A';
                    if ($user->primaryDepartment) {
                        $departmentName = $user->primaryDepartment->name ?? 'N/A';
                    } elseif ($user->relationLoaded('primaryDepartment') && $user->primaryDepartment) {
                        $departmentName = $user->primaryDepartment->name ?? 'N/A';
                    }
                    
                    // Safely get position name
                    $positionName = 'N/A';
                    if ($user->employee && $user->employee->position) {
                        $positionName = $user->employee->position->name ?? 'N/A';
                    }
                    
                    // Safely get roles
                    $roles = 'N/A';
                    if ($user->relationLoaded('roles') && $user->roles) {
                        $roleNames = $user->roles->pluck('display_name')->filter();
                        if ($roleNames->isEmpty()) {
                            $roleNames = $user->roles->pluck('name')->filter();
                        }
                        $roles = $roleNames->join(', ') ?: 'N/A';
                    }
                    
                    $row = [
                        $user->id ?? 'N/A',
                        $user->name ?? 'N/A',
                        $user->email ?? 'N/A',
                        $user->employee_id ?? 'N/A',
                        $user->phone ?? 'N/A',
                        $user->mobile ?? 'N/A',
                        $departmentName,
                        $positionName,
                        $roles,
                        $user->is_active ? 'Active' : 'Inactive',
                        $user->email_verified_at ? 'Yes' : 'No',
                        $user->email_verified_at ? $user->email_verified_at->format('Y-m-d H:i:s') : 'N/A',
                        $user->marital_status ?? 'N/A',
                        ($user->date_of_birth && is_object($user->date_of_birth)) ? $user->date_of_birth->format('Y-m-d') : ($user->date_of_birth ?? 'N/A'),
                        $user->gender ?? 'N/A',
                        $user->nationality ?? 'N/A',
                        $user->address ?? 'N/A',
                        ($user->hire_date && is_object($user->hire_date)) ? $user->hire_date->format('Y-m-d') : ($user->hire_date ?? 'N/A'),
                        ($user->created_at && is_object($user->created_at)) ? $user->created_at->format('Y-m-d H:i:s') : ($user->created_at ?? 'N/A'),
                        ($user->updated_at && is_object($user->updated_at)) ? $user->updated_at->format('Y-m-d H:i:s') : ($user->updated_at ?? 'N/A'),
                    ];

                    if ($includePasswords) {
                        $row[] = $user->password ?? 'N/A'; // Hashed password
                        $row[] = 'welcome123'; // Default password note
                    }

                    fputcsv($file, $row);
                } catch (\Exception $e) {
                    \Log::error('Error exporting user row', [
                        'user_id' => $user->id ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    continue;
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $csvHeaders);
    }

    /**
     * Refresh email verification for a user
     */
    public function refreshEmailVerification(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        // Only System Admin can refresh email verification
        if (!$currentUser || !$currentUser->hasRole('System Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only System Administrators can refresh email verification.'
            ], 403);
        }

        try {
            // Clear email verification
            $user->email_verified_at = null;
            $user->save();

            // Log the action
            ActivityLogService::logAction('email_verification_refreshed', 
                "Email verification refreshed for user {$user->name} ({$user->email})", 
                $user, [
                    'refreshed_by' => $currentUser->id,
                    'refreshed_by_name' => $currentUser->name,
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                ]);

            Log::info('Email verification refreshed', [
                'user_id' => $user->id,
                'admin_id' => $currentUser->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email verification has been refreshed. User will need to verify their email again.',
                'email_verified_at' => null
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to refresh email verification', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh email verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk refresh email verification
     */
    public function bulkRefreshEmailVerification(Request $request)
    {
        $currentUser = Auth::user();
        
        // Only System Admin can refresh email verification
        if (!$currentUser || !$currentUser->hasRole('System Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only System Administrators can refresh email verification.'
            ], 403);
        }

        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        try {
            $users = User::whereIn('id', $request->user_ids)->get();
            $count = 0;

            foreach ($users as $user) {
                $user->email_verified_at = null;
                $user->save();
                $count++;

                // Log each refresh
                ActivityLogService::logAction('email_verification_refreshed', 
                    "Email verification refreshed for user {$user->name} ({$user->email})", 
                    $user, [
                        'refreshed_by' => $currentUser->id,
                        'refreshed_by_name' => $currentUser->name,
                        'bulk_operation' => true,
                    ]);
            }

            Log::info('Bulk email verification refresh completed', [
                'count' => $count,
                'admin_id' => $currentUser->id
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully refreshed email verification for {$count} user(s).",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to bulk refresh email verification', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh email verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics for dashboard
     */
    public function statistics()
    {
        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'by_department' => User::select('primary_department_id', DB::raw('count(*) as count'))
                ->whereNotNull('primary_department_id')
                ->groupBy('primary_department_id')
                ->with('primaryDepartment')
                ->get()
                ->map(function($item) {
                    return [
                        'department' => $item->primaryDepartment->name ?? 'N/A',
                        'count' => $item->count
                    ];
                }),
            'by_role' => DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->select('roles.display_name', DB::raw('count(*) as count'))
                ->groupBy('roles.id', 'roles.display_name')
                ->get()
                ->map(function($item) {
                    return [
                        'role' => $item->display_name,
                        'count' => $item->count
                    ];
                }),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'this_month' => User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return response()->json($stats);
    }
}