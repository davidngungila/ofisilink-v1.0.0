<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\BankAccount;
use App\Models\EmployeeEducation;
use App\Models\EmployeeFamily;
use App\Models\EmployeeNextOfKin;
use App\Models\EmployeeReferee;
use App\Models\EmployeeSalaryDeduction;
use App\Models\EmployeeDocument;
use App\Models\ActivityLog;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Determine access level
        $canViewAll = $user->hasAnyRole(['CEO', 'HOD', 'HR Officer', 'System Admin']);
        $canEditAll = $user->hasAnyRole(['HR Officer', 'System Admin']);
        
        if ($canViewAll) {
            // Build query for all employees - ensure one-to-one relationship
            $query = User::with(['employee', 'primaryDepartment', 'roles'])
                ->whereHas('employee'); // Only show users with employee records
            
            // Advanced filtering
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('department')) {
                $query->where('primary_department_id', $request->department);
            }
            
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }
            
            if ($request->filled('employment_type')) {
                $query->whereHas('employee', function($q) use ($request) {
                    $q->where('employment_type', $request->employment_type);
                });
            }
            
            if ($request->filled('salary_range')) {
                $range = explode('-', $request->salary_range);
                if (count($range) === 2) {
                    $query->whereHas('employee', function($q) use ($range) {
                        $q->whereBetween('salary', [$range[0], $range[1]]);
                    });
                } elseif (str_ends_with($request->salary_range, '+')) {
                    $min = (int) rtrim($request->salary_range, '+');
                    $query->whereHas('employee', function($q) use ($min) {
                        $q->where('salary', '>=', $min);
                    });
                }
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            
            switch ($sortBy) {
                case 'department':
                    $query->join('departments', 'users.primary_department_id', '=', 'departments.id')
                          ->orderBy('departments.name', $sortOrder)
                          ->select('users.*');
                    break;
                case 'salary':
                    $query->join('employees', 'users.id', '=', 'employees.user_id')
                          ->orderBy('employees.salary', $sortOrder)
                          ->select('users.*');
                    break;
                case 'hire_date':
                    $query->orderBy('users.hire_date', $sortOrder);
                    break;
                default:
                    $query->orderBy('users.name', $sortOrder);
            }
            
            $employees = $query->paginate($request->get('per_page', 20));
            
            // Calculate completion percentage for each employee
            foreach ($employees as $emp) {
                // Load necessary relationships for completion calculation
                $emp->loadMissing([
                    'family', 'nextOfKin', 'referees', 'educations', 'bankAccounts'
                ]);
                $emp->completion_percentage = $this->calculateEmployeeCompletion($emp);
            }
            
            $employee = null; // Not needed for manager view
        } else {
            // Show only current user for staff - ensure employee record exists
            $employee = $user->load(['employee', 'primaryDepartment', 'roles']);
            
            // Generate Employee ID if missing
            if (empty($employee->employee_id)) {
                try {
                    $employeeId = $this->generateEmployeeId($employee->hire_date, $employee->primary_department_id);
                    $employee->update(['employee_id' => $employeeId]);
                    $employee->refresh();
                    Log::info('Auto-generated Employee ID for staff user', [
                        'user_id' => $employee->id,
                        'employee_id' => $employeeId
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to generate Employee ID for staff user', [
                        'user_id' => $employee->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Create employee record if it doesn't exist (one-to-one enforcement)
            if (!$employee->employee) {
                Employee::create([
                    'user_id' => $employee->id,
                    'position' => 'Staff Member',
                    'employment_type' => 'permanent',
                    'hire_date' => $employee->hire_date ?? now(),
                    'salary' => 0,
                ]);
                $employee->refresh();
                $employee->load('employee');
            }
            
            // Load additional data for staff view
            $employee->loadMissing([
                'employee', 
                'primaryDepartment', 
                'roles',
                'family',
                'nextOfKin',
                'referees',
                'educations',
                'bankAccounts',
                'employeeDocuments'
            ]);
            
            // Get payroll history
            $payrollHistory = \App\Models\PayrollItem::where('employee_id', $employee->id)
                ->with(['payroll'])
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            
            // Get leave balance
            $currentYear = now()->year;
            $leaveBalances = \App\Models\LeaveBalance::where('employee_id', $employee->id)
                ->where('financial_year', $currentYear)
                ->with('leaveType')
                ->get();
            
            // Get recent leave requests
            $recentLeaves = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                ->with('leaveType')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Get attendance summary (last 30 days)
            $attendanceSummary = \App\Models\Attendance::where('employee_id', $employee->employee->id ?? null)
                ->where('attendance_date', '>=', now()->subDays(30))
                ->orderBy('attendance_date', 'desc')
                ->get();
            
            // Calculate attendance stats
            $totalDays = 30;
            $presentDays = $attendanceSummary->where('status', 'present')->count();
            $absentDays = $totalDays - $presentDays;
            $attendanceRate = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;
            
            // Get performance reviews from assessments (approved assessments act as performance reviews)
            $performanceReviews = \App\Models\Assessment::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->with(['hodApprover'])
                ->orderBy('hod_approved_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($assessment) {
                    // Transform assessment to performance review format
                    return (object)[
                        'id' => $assessment->id,
                        'overall_rating' => $assessment->contribution_percentage ?? 0, // Use contribution as rating
                        'review_date' => $assessment->hod_approved_at ?? $assessment->created_at,
                        'reviewer' => $assessment->hodApprover->name ?? 'N/A',
                        'main_responsibility' => $assessment->main_responsibility,
                        'status' => $assessment->status,
                    ];
                });
            
            // Get recent activity logs - ONLY activities performed BY this employee
            // Only show activities where user_id matches the employee (not other users' activities)
            $recentActivities = ActivityLog::where('user_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
            
            // Calculate completion percentage
            $employee->completion_percentage = $this->calculateEmployeeCompletion($employee);
            
            // Create a simple paginator to avoid errors in the view
            $employees = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([$employee]),
                1,
                1,
                1
            );
        }
        
        // Handle export requests
        if ($request->has('export')) {
            $exportType = $request->get('export');
            $selectedIds = $request->get('ids') ? explode(',', $request->get('ids')) : null;
            
            // Build export query
            $exportQuery = User::with(['employee', 'primaryDepartment', 'roles'])
                ->whereHas('employee');
            
            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $exportQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('department')) {
                $exportQuery->where('primary_department_id', $request->department);
            }
            
            if ($request->filled('status')) {
                $exportQuery->where('is_active', $request->status === 'active');
            }
            
            // Apply ID filter if provided (for bulk export)
            if ($selectedIds && !empty($selectedIds)) {
                $exportQuery->whereIn('users.id', $selectedIds);
            }
            
            $employees = $exportQuery->orderBy('users.name')->get();
            
            // Calculate completion for each employee
            foreach ($employees as $emp) {
                $emp->loadMissing(['family', 'nextOfKin', 'referees', 'educations', 'bankAccounts']);
            }
            
            if ($exportType === 'excel') {
                return $this->exportToExcel($employees);
            } elseif ($exportType === 'pdf') {
                return $this->exportToPDF($employees);
            }
        }
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $roles = \App\Models\Role::where('is_active', true)->orderBy('display_name')->get();
        $positions = \App\Models\Position::where('is_active', true)->orderBy('title')->get();
        
        // Auto-sync: Create employee records for users that don't have them
        if ($canEditAll) {
            $this->syncEmployeeRecords();
        }
        
        // Get recent activities from ActivityLog database - Employee/User related activities
        $recentActivities = ActivityLog::with('user')
            ->where(function($query) {
                $query->where('action', 'like', '%employee%')
                      ->orWhere('action', 'like', '%user%')
                      ->orWhere('action', 'like', '%create%')
                      ->orWhere('action', 'like', '%update%')
                      ->orWhere('action', 'like', '%delete%')
                      ->orWhere('action', 'like', '%edit%')
                      ->orWhere('action', 'like', '%register%')
                      ->orWhere('model_type', 'App\\Models\\User')
                      ->orWhere('model_type', 'App\\Models\\Employee')
                      ->orWhere('description', 'like', '%employee%')
                      ->orWhere('description', 'like', '%user%');
            })
            ->latest()
            ->limit(10)
            ->get();
        
        // For staff view, pass additional data
        if (!$canViewAll && isset($employee)) {
            return view('modules.hr.employees', compact(
                'employees', 
                'employee', 
                'departments', 
                'roles', 
                'positions', 
                'canViewAll', 
                'canEditAll', 
                'payrollHistory',
                'leaveBalances',
                'recentLeaves',
                'attendanceSummary',
                'performanceReviews',
                'recentActivities',
                'presentDays',
                'absentDays',
                'attendanceRate'
            ));
        }
        
        return view('modules.hr.employees', compact('employees', 'employee', 'departments', 'roles', 'positions', 'canViewAll', 'canEditAll', 'recentActivities'));
    }
    
    /**
     * Sync employee records for all users that don't have employee records
     * Also generates Employee IDs for users without them
     */
    private function syncEmployeeRecords()
    {
        $usersWithoutEmployees = User::whereDoesntHave('employee')->where('is_active', true)->get();
        $count = 0;
        
        foreach ($usersWithoutEmployees as $user) {
            // Only create if user doesn't already have an employee record
            if (!$user->employee) {
                // Generate Employee ID if user doesn't have one
                if (empty($user->employee_id)) {
                    try {
                        $employeeId = $this->generateEmployeeId($user->hire_date, $user->primary_department_id);
                        $user->update(['employee_id' => $employeeId]);
                        Log::info('Auto-generated Employee ID during sync', [
                            'user_id' => $user->id,
                            'employee_id' => $employeeId
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to generate Employee ID during sync', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                Employee::create([
                    'user_id' => $user->id,
                    'position' => $this->getDefaultPosition($user),
                    'employment_type' => 'permanent',
                    'hire_date' => $user->hire_date ?? now(),
                    'salary' => 0,
                ]);
                $count++;
            }
        }
        
        // Also ensure all users have employee records and Employee IDs
        $allUsers = User::where('is_active', true)->get();
        foreach ($allUsers as $user) {
            // Generate Employee ID if missing
            if (empty($user->employee_id)) {
                try {
                    $employeeId = $this->generateEmployeeId($user->hire_date, $user->primary_department_id);
                    $user->update(['employee_id' => $employeeId]);
                    Log::info('Auto-generated Employee ID during sync', [
                        'user_id' => $user->id,
                        'employee_id' => $employeeId
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to generate Employee ID during sync', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Ensure user has employee record
            if (!$user->employee) {
                Employee::create([
                    'user_id' => $user->id,
                    'position' => $this->getDefaultPosition($user),
                    'employment_type' => 'permanent',
                    'hire_date' => $user->hire_date ?? now(),
                    'salary' => 0,
                ]);
                $count++;
            }
        }
        
        return $count;
    }
    
    /**
     * Get default position based on user roles
     */
    private function getDefaultPosition($user)
    {
        if ($user->hasRole('System Admin')) {
            return 'System Administrator';
        } elseif ($user->hasRole('CEO')) {
            return 'Chief Executive Officer';
        } elseif ($user->hasRole('HOD')) {
            return 'Head of Department';
        } elseif ($user->hasRole('HR Officer')) {
            return 'Human Resources Officer';
        } elseif ($user->hasRole('Accountant')) {
            return 'Accountant';
        } else {
            return 'Staff Member';
        }
    }
    
    /**
     * Generate unique Employee ID based on format: EMP + YYYYMMDD + Department Code
     * Format: EMP20251107DU (where DU is the first 2 uppercase letters of department code)
     * Uses hire_date if provided, otherwise uses current date
     * Uses department code if provided, otherwise uses 'XX' as default
     */
    private function generateEmployeeId($hireDate = null, $departmentId = null)
    {
        try {
            // Use hire date if provided, otherwise use current date
            $date = $hireDate ? date('Ymd', strtotime($hireDate)) : date('Ymd');
            
            // Get department code (first 2 uppercase letters)
            $deptCode = 'XX'; // Default code
            if ($departmentId) {
                $department = Department::find($departmentId);
                if ($department && $department->code) {
                    // Get first 2 uppercase letters of department code
                    $code = strtoupper($department->code);
                    $deptCode = substr($code, 0, 2);
                    // If code is single character, pad with X
                    if (strlen($deptCode) < 2) {
                        $deptCode = str_pad($deptCode, 2, 'X', STR_PAD_RIGHT);
                    }
                } else {
                    // If department not found, try to get from department name
                    if ($department && $department->name) {
                        $name = strtoupper(preg_replace('/[^A-Z]/', '', $department->name));
                        $deptCode = substr($name, 0, 2);
                        if (strlen($deptCode) < 2) {
                            $deptCode = 'XX';
                        }
                    }
                }
            }
            
            // Base format: EMP + YYYYMMDD + Department Code
            $baseId = 'EMP' . $date . $deptCode;
            
            // Check if this ID already exists
            $employeeId = $baseId;
            $counter = 1;
            $maxAttempts = 100;
            
            while (User::where('employee_id', $employeeId)->exists() && $counter < $maxAttempts) {
                // If ID exists, add sequential number: EMP20251107DU-2
                $employeeId = $baseId . '-' . $counter;
                $counter++;
            }
            
            if ($counter >= $maxAttempts) {
                Log::error('Failed to generate unique Employee ID after max attempts', [
                    'date' => $date,
                    'department_id' => $departmentId,
                    'dept_code' => $deptCode,
                    'base_id' => $baseId
                ]);
                throw new \Exception('Failed to generate unique Employee ID. Please try again.');
            }
            
            return $employeeId;
            
        } catch (\Exception $e) {
            Log::error('Error generating Employee ID', [
                'error' => $e->getMessage(),
                'hire_date' => $hireDate,
                'department_id' => $departmentId
            ]);
            throw $e;
        }
    }
    
    public function show($id)
    {
        $user = Auth::user();
        $loadAll = request()->get('load_all') === 'true';
        
        // Cache role checks to avoid repeated database queries
        $canViewAll = cache()->remember("user_{$user->id}_can_view_employees", 3600, function() use ($user) {
            return $user->hasAnyRole(['CEO', 'HOD', 'HR Officer', 'System Admin']);
        });
        
        $canEdit = cache()->remember("user_{$user->id}_can_edit_employees", 3600, function() use ($user) {
            return $user->hasAnyRole(['HR Officer', 'System Admin']);
        });
        
        // Check permissions first before loading data
        if (!$canViewAll && $user->id != $id) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to employee details.'
                ], 403);
            }
            abort(403, 'Unauthorized access to employee details.');
        }
        
        // Use cache for employee data if not editing (data changes less frequently when viewing)
        $cacheKey = $loadAll ? "employee_full_{$id}" : "employee_basic_{$id}";
        
        $startTime = microtime(true);
        $employee = cache()->remember($cacheKey, $loadAll ? 300 : 600, function() use ($id, $loadAll) {
            // Load all columns and relationships for complete employee view
            $employee = User::select([
                'id', 'name', 'email', 'phone', 'mobile', 'employee_id', 'photo',
                'primary_department_id', 'hire_date', 'is_active', 'marital_status',
                'date_of_birth', 'gender', 'nationality', 'address',
                'created_at', 'updated_at'
            ])
            ->where('id', $id)
            ->with([
                'employee' => function($query) {
                    // Load all employee fields including emergency contact
                    $query->select('id', 'user_id', 'position', 'employment_type', 'salary', 
                        'tin_number', 'nssf_number', 
                        'nhif_number', 'heslb_number', 'has_student_loan',
                        'emergency_contact_name', 'emergency_contact_phone', 
                        'emergency_contact_relationship', 'emergency_contact_address');
                },
                'primaryDepartment' => function($query) {
                    $query->select('departments.id', 'departments.name', 'departments.code');
                },
                'roles' => function($query) {
                    $query->select('roles.id', 'roles.name', 'roles.display_name');
                },
            ])
            ->first();
            
            if (!$employee) {
                return null;
            }
            
            // Always load all relationships for complete employee view
            // Load additional relationships for comprehensive display
            $relationshipsToLoad = [];
            
            // Load departments safely
            if (Schema::hasTable('departments') && Schema::hasTable('user_departments')) {
                $relationshipsToLoad['departments'] = function($query) {
                    $query->select('departments.id', 'departments.name', 'departments.code', 'departments.is_active')
                          ->withPivot(['is_primary', 'is_active', 'joined_at']);
                };
            }
            
            // Load bank accounts safely - check if table exists
            if (Schema::hasTable('bank_accounts')) {
                $relationshipsToLoad['bankAccounts'] = function($query) {
                    $query->select('id', 'user_id', 'bank_name', 'account_number', 'account_name', 'branch_name', 'swift_code', 'is_primary')
                          ->orderBy('is_primary', 'desc');
                };
                $relationshipsToLoad['primaryBankAccount'] = function($query) {
                    $query->select('id', 'user_id', 'bank_name', 'account_number');
                };
            }
            
            // Load educations safely
            if (Schema::hasTable('employee_educations')) {
                $relationshipsToLoad['educations'] = function($query) {
                    $query->select('id', 'user_id', 'institution_name', 'qualification', 'start_year', 'end_year', 'grade', 'field_of_study', 'description')
                          ->orderBy('order')
                          ->orderBy('end_year', 'desc');
                };
            }
            
            // Load family safely
            if (Schema::hasTable('employee_family')) {
                $relationshipsToLoad['family'] = function($query) {
                    $query->select('id', 'user_id', 'name', 'relationship', 'date_of_birth', 'is_dependent', 'gender', 'occupation', 'phone', 'email', 'address');
                };
            }
            
            // Load next of kin safely
            if (Schema::hasTable('employee_next_of_kin')) {
                $relationshipsToLoad['nextOfKin'] = function($query) {
                    $query->select('id', 'user_id', 'name', 'relationship', 'phone', 'email', 'address', 'id_number');
                };
            }
            
            // Load referees safely
            if (Schema::hasTable('employee_referees')) {
                $relationshipsToLoad['referees'] = function($query) {
                    $query->select('id', 'user_id', 'name', 'position', 'organization', 'phone', 'email', 'relationship', 'address')
                          ->orderBy('order');
                };
            }
            
            // Load salary deductions safely
            if (Schema::hasTable('employee_salary_deductions')) {
                $relationshipsToLoad['salaryDeductions'] = function($query) {
                    $query->select('id', 'employee_id', 'deduction_type', 'description', 'amount', 'frequency', 'start_date', 'end_date', 'is_active', 'notes')
                          ->orderBy('start_date', 'desc');
                };
            }
            
            // Load documents safely
            if (Schema::hasTable('employee_documents')) {
                $relationshipsToLoad['documents'] = function($query) {
                    $query->select('id', 'user_id', 'document_type', 'document_name', 'file_path', 'file_name', 'file_type', 'file_size', 'issue_date', 'expiry_date', 'issued_by', 'document_number', 'description', 'is_active', 'uploaded_by', 'created_at', 'updated_at')
                          ->where('is_active', true)
                          ->with('uploader:id,name')
                          ->orderBy('created_at', 'desc');
                };
                $relationshipsToLoad['employeeDocuments'] = function($query) {
                    $query->select('id', 'user_id', 'document_type', 'document_name', 'file_path', 'file_name', 'file_type', 'file_size', 'issue_date', 'expiry_date', 'issued_by', 'document_number', 'description', 'is_active', 'uploaded_by', 'created_at', 'updated_at')
                          ->where('is_active', true)
                          ->with('uploader:id,name')
                          ->orderBy('created_at', 'desc');
                };
            }
            
            // Load relationships if any exist
            if (!empty($relationshipsToLoad)) {
                $employee->load($relationshipsToLoad);
            }
            
            return $employee;
        });
        
        $queryTime = round((microtime(true) - $startTime) * 1000, 2);
        
        // Log slow queries for debugging
        if ($queryTime > 1000) {
            Log::warning("Slow employee query", [
                'employee_id' => $id,
                'load_all' => $loadAll,
                'query_time_ms' => $queryTime,
                'from_cache' => cache()->has($cacheKey)
            ]);
        }
        
        if (!$employee) {
            Log::warning("Employee not found", ['employee_id' => $id, 'user_id' => $user->id]);
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found.'
                ], 404);
            }
            abort(404, 'Employee not found.');
        }
        
        // Generate Employee ID if missing
        if (empty($employee->employee_id)) {
            try {
                $employeeId = $this->generateEmployeeId($employee->hire_date, $employee->primary_department_id);
                $employee->update(['employee_id' => $employeeId]);
                $employee->refresh();
                // Clear cache since we updated the employee
                cache()->forget($cacheKey);
                Log::info('Auto-generated Employee ID in show method', [
                    'user_id' => $employee->id,
                    'employee_id' => $employeeId
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to generate Employee ID in show method', [
                    'user_id' => $employee->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Ensure employee relationship is loaded and exists
        if (!$employee->employee) {
            // Create employee record if missing
            Employee::create([
                'user_id' => $employee->id,
                'position' => $this->getDefaultPosition($employee),
                'employment_type' => 'permanent',
                'hire_date' => $employee->hire_date ?? now(),
                'salary' => 0,
            ]);
            $employee->refresh();
            $employee->load('employee');
            
            // Clear cache since we updated the employee
            cache()->forget($cacheKey);
        }
        
        // Calculate completion percentage
        $completionPercentage = $this->calculateEmployeeCompletion($employee);
        
        // Add photo_url to employee data if photo exists
        if ($employee->photo) {
            $employee->photo_url = asset('storage/photos/' . $employee->photo);
        }
        
        // Add file_url to documents if they exist
        if ($employee->relationLoaded('documents') && $employee->documents && $employee->documents->isNotEmpty()) {
            foreach ($employee->documents as $document) {
                // Extract filename from file_path (format: public/documents/filename.ext)
                $fileName = basename($document->file_path);
                $document->file_url = asset('storage/documents/' . $fileName);
            }
        }
        if ($employee->relationLoaded('employeeDocuments') && $employee->employeeDocuments && $employee->employeeDocuments->isNotEmpty()) {
            foreach ($employee->employeeDocuments as $document) {
                $fileName = basename($document->file_path);
                $document->file_url = asset('storage/documents/' . $fileName);
            }
        }
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'employee' => $employee,
                'canEdit' => $canEdit,
                'completion_percentage' => $completionPercentage,
                '_performance' => [
                    'query_time_ms' => $queryTime,
                    'cached' => cache()->has($cacheKey),
                    'load_all' => $loadAll
                ]
            ]);
        }
        
        // Return view for browser requests
        return view('modules.hr.employee-show', compact('employee', 'canEdit', 'completionPercentage'));
    }
    
    /**
     * Calculate employee profile completion percentage
     */
    private function calculateEmployeeCompletion($employee)
    {
        $totalSections = 9;
        $completedSections = 0;
        
        try {
            // 1. Personal Information (required)
            if (!empty($employee->name) && !empty($employee->email) && !empty($employee->employee_id)) {
                $completedSections++;
            }
            
            // 2. Employment Information
            if ($employee->employee && !empty($employee->employee->position) && !empty($employee->primary_department_id)) {
                $completedSections++;
            }
            
            // 3. Emergency Contact
            if ($employee->employee && (
                !empty($employee->employee->emergency_contact_name) ||
                !empty($employee->employee->emergency_contact_phone)
            )) {
                $completedSections++;
            }
            
            // 4. Family Information - check if relationship exists and has data
            try {
                $familyCount = $employee->relationLoaded('family') ? $employee->family->count() : 
                               (Schema::hasTable('employee_family') ? 
                                \App\Models\EmployeeFamily::where('user_id', $employee->id)->count() : 0);
                if ($familyCount > 0) {
                    $completedSections++;
                }
            } catch (\Exception $e) {
                // Relationship not loaded or table doesn't exist, skip
            }
            
            // 5. Next of Kin
            try {
                $nextOfKinCount = $employee->relationLoaded('nextOfKin') ? $employee->nextOfKin->count() : 
                                  (Schema::hasTable('employee_next_of_kin') ? 
                                   \App\Models\EmployeeNextOfKin::where('user_id', $employee->id)->count() : 0);
                if ($nextOfKinCount > 0) {
                    $completedSections++;
                }
            } catch (\Exception $e) {
                // Relationship not loaded or table doesn't exist, skip
            }
            
            // 6. Referees
            try {
                $refereesCount = $employee->relationLoaded('referees') ? $employee->referees->count() : 
                                 (Schema::hasTable('employee_referees') ? 
                                  \App\Models\EmployeeReferee::where('user_id', $employee->id)->count() : 0);
                if ($refereesCount > 0) {
                    $completedSections++;
                }
            } catch (\Exception $e) {
                // Relationship not loaded or table doesn't exist, skip
            }
            
            // 7. Education
            try {
                $educationCount = $employee->relationLoaded('educations') ? $employee->educations->count() : 
                                  (Schema::hasTable('employee_educations') ? 
                                   \App\Models\EmployeeEducation::where('user_id', $employee->id)->count() : 0);
                if ($educationCount > 0) {
                    $completedSections++;
                }
            } catch (\Exception $e) {
                // Relationship not loaded or table doesn't exist, skip
            }
            
            // 8. Bank Details
            try {
                $bankCount = $employee->relationLoaded('bankAccounts') ? $employee->bankAccounts->count() : 
                            (Schema::hasTable('bank_accounts') ? 
                             \App\Models\BankAccount::where('user_id', $employee->id)->count() : 0);
                if ($bankCount > 0) {
                    $completedSections++;
                }
            } catch (\Exception $e) {
                // Relationship not loaded or table doesn't exist, skip
            }
            
            // 9. Statutory/Deductions
            if ($employee->employee && (
                !empty($employee->employee->tin_number) ||
                !empty($employee->employee->nssf_number) ||
                !empty($employee->employee->nhif_number)
            )) {
                $completedSections++;
            }
        } catch (\Exception $e) {
            Log::warning('Error calculating employee completion', [
                'employee_id' => $employee->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
        
        return round(($completedSections / $totalSections) * 100, 1);
    }
    
    /**
     * Send welcome SMS to new employee with login credentials
     * Sends only to the phone field (not mobile)
     */
    private function sendWelcomeSMS($employee, $password = null)
    {
        try {
            // Use only the phone field, not mobile
            $phone = $employee->phone;
            
            if (empty($phone)) {
                Log::warning('Cannot send welcome SMS: No phone number', [
                    'user_id' => $employee->id,
                    'email' => $employee->email
                ]);
                return [
                    'sent' => false,
                    'phone' => null,
                    'message' => null,
                    'error' => 'No phone number available'
                ];
            }
            
            // Get login URL
            $loginUrl = config('app.url') . '/login';
            
            // Build welcome message with credentials - Always include username and password for new staff
            $message = "Welcome to OfisiLink!\n\n";
            $message .= "Your account has been created successfully.\n\n";
            $message .= "Login Details:\n";
            $message .= "Username/Email: {$employee->email}\n";
            
            // Always include password for new staff
            if ($password) {
                $message .= "Password: {$password}\n\n";
            } else {
                // Default password if not provided
                $message .= "Password: welcome123\n\n";
            }
            
            $message .= "Please change your password after first login.\n\n";
            $message .= "Login URL: {$loginUrl}\n\n";
            $message .= "Employee ID: {$employee->employee_id}\n\n";
            $message .= "Thank you for joining us!";
            
            // Send SMS using NotificationService
            $notificationService = app(NotificationService::class);
            $smsSent = $notificationService->sendSMS($phone, $message);
            
            if ($smsSent) {
                Log::info('Welcome SMS sent successfully to new employee', [
                    'user_id' => $employee->id,
                    'email' => $employee->email,
                    'phone' => $phone,
                    'employee_id' => $employee->employee_id
                ]);
            } else {
                Log::warning('Welcome SMS sending failed', [
                    'user_id' => $employee->id,
                    'email' => $employee->email,
                    'phone' => $phone
                ]);
            }
            
            return [
                'sent' => $smsSent,
                'phone' => $phone,
                'message' => $message
            ];
            
        } catch (\Exception $e) {
            Log::error('Error sending welcome SMS', [
                'user_id' => $employee->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Send congratulations SMS when registration is complete
     */
    private function sendCongratulationsSMS($employee)
    {
        try {
            $phone = $employee->mobile ?? $employee->phone;
            
            if (empty($phone)) {
                Log::warning('Cannot send congratulations SMS: No phone number', [
                    'user_id' => $employee->id,
                    'email' => $employee->email
                ]);
                return false;
            }
            
            $message = "🎉 Congratulations {$employee->name}!\n\n";
            $message .= "Your employee registration with OfisiLink has been completed successfully!\n\n";
            $message .= "Employee ID: {$employee->employee_id}\n";
            $message .= "Department: " . ($employee->primaryDepartment->name ?? 'N/A') . "\n\n";
            $message .= "Welcome aboard! We're excited to have you as part of our team.\n\n";
            $message .= "Best regards,\nOfisiLink Team";
            
            // Send SMS using NotificationService
            $notificationService = app(NotificationService::class);
            $smsSent = $notificationService->sendSMS($phone, $message);
            
            if ($smsSent) {
                Log::info('Congratulations SMS sent successfully to new employee', [
                    'user_id' => $employee->id,
                    'email' => $employee->email,
                    'phone' => $phone,
                    'employee_id' => $employee->employee_id
                ]);
            } else {
                Log::warning('Congratulations SMS sending failed', [
                    'user_id' => $employee->id,
                    'email' => $employee->email,
                    'phone' => $phone
                ]);
            }
            
            return [
                'sent' => $smsSent,
                'phone' => $phone,
                'message' => $message
            ];
            
        } catch (\Exception $e) {
            Log::error('Error sending congratulations SMS', [
                'user_id' => $employee->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'sent' => false,
                'phone' => $phone ?? null,
                'message' => null
            ];
        }
    }
    
    /**
     * Send notification SMS to HOD (Head of Department)
     */
    private function sendNotificationToHOD($employee)
    {
        try {
            $notificationService = app(NotificationService::class);
            $department = $employee->primaryDepartment;
            
            if (!$department) {
                Log::warning('Cannot send HOD notification: Employee has no department', [
                    'user_id' => $employee->id
                ]);
                return ['sent' => false, 'phone' => null, 'message' => null];
            }
            
            // Get HOD of the employee's department
            $hod = User::where('primary_department_id', $department->id)
                ->whereHas('roles', function($query) {
                    $query->where('name', 'HOD');
                })
                ->where('is_active', true)
                ->first();
            
            if (!$hod) {
                Log::info('No HOD found for department', [
                    'department_id' => $department->id,
                    'department_name' => $department->name
                ]);
                return ['sent' => false, 'phone' => null, 'message' => 'No HOD found for department'];
            }
            
            $phone = $hod->mobile ?? $hod->phone;
            if (empty($phone)) {
                Log::warning('Cannot send HOD notification: No phone number', [
                    'hod_id' => $hod->id,
                    'hod_name' => $hod->name
                ]);
                return ['sent' => false, 'phone' => null, 'message' => 'HOD has no phone number'];
            }
            
            $message = "New Employee Registration\n\n";
            $message .= "A new employee has been registered in your department.\n\n";
            $message .= "Employee Details:\n";
            $message .= "Name: {$employee->name}\n";
            $message .= "Employee ID: {$employee->employee_id}\n";
            $message .= "Department: {$department->name}\n";
            $message .= "Position: " . ($employee->employee->position ?? 'N/A') . "\n";
            $message .= "Email: {$employee->email}\n\n";
            $message .= "Please review the employee registration in the system.\n\n";
            $message .= "OfisiLink";
            
            $smsSent = $notificationService->sendSMS($phone, $message);
            
            if ($smsSent) {
                Log::info('HOD notification SMS sent successfully', [
                    'hod_id' => $hod->id,
                    'employee_id' => $employee->id,
                    'phone' => $phone
                ]);
            } else {
                Log::warning('HOD notification SMS sending failed', [
                    'hod_id' => $hod->id,
                    'employee_id' => $employee->id,
                    'phone' => $phone
                ]);
            }
            
            return [
                'sent' => $smsSent,
                'phone' => $phone,
                'message' => $message,
                'recipient' => $hod->name
            ];
            
        } catch (\Exception $e) {
            Log::error('Error sending HOD notification SMS', [
                'employee_id' => $employee->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return ['sent' => false, 'phone' => null, 'message' => null];
        }
    }
    
    /**
     * Send notification SMS to CEO
     */
    private function sendNotificationToCEO($employee)
    {
        try {
            $notificationService = app(NotificationService::class);
            
            // Get all users with CEO or Director role
            $ceos = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['CEO', 'Director']);
            })
            ->where('is_active', true)
            ->get();
            
            if ($ceos->isEmpty()) {
                Log::info('No CEO/Director found to notify', []);
                return ['sent' => false, 'phone' => null, 'message' => 'No CEO/Director found'];
            }
            
            $results = [];
            $anySent = false;
            
            foreach ($ceos as $ceo) {
                $phone = $ceo->mobile ?? $ceo->phone;
                if (empty($phone)) {
                    Log::warning('CEO has no phone number', [
                        'ceo_id' => $ceo->id,
                        'ceo_name' => $ceo->name
                    ]);
                    continue;
                }
                
                $message = "New Employee Registration\n\n";
                $message .= "A new employee has been registered in the system.\n\n";
                $message .= "Employee Details:\n";
                $message .= "Name: {$employee->name}\n";
                $message .= "Employee ID: {$employee->employee_id}\n";
                $message .= "Department: " . ($employee->primaryDepartment->name ?? 'N/A') . "\n";
                $message .= "Position: " . ($employee->employee->position ?? 'N/A') . "\n";
                $message .= "Email: {$employee->email}\n";
                $message .= "Hire Date: " . ($employee->hire_date ? $employee->hire_date->format('d M Y') : 'N/A') . "\n\n";
                $message .= "OfisiLink";
                
                $smsSent = $notificationService->sendSMS($phone, $message);
                
                if ($smsSent) {
                    $anySent = true;
                    Log::info('CEO notification SMS sent successfully', [
                        'ceo_id' => $ceo->id,
                        'employee_id' => $employee->id,
                        'phone' => $phone
                    ]);
                } else {
                    Log::warning('CEO notification SMS sending failed', [
                        'ceo_id' => $ceo->id,
                        'employee_id' => $employee->id,
                        'phone' => $phone
                    ]);
                }
                
                $results[] = [
                    'sent' => $smsSent,
                    'phone' => $phone,
                    'recipient' => $ceo->name
                ];
            }
            
            // Return the first result or a summary
            if (!empty($results)) {
                $firstResult = $results[0];
                return [
                    'sent' => $anySent,
                    'phone' => $firstResult['phone'],
                    'message' => $message ?? null,
                    'recipients' => array_column($results, 'recipient'),
                    'all_results' => $results
                ];
            }
            
            return ['sent' => false, 'phone' => null, 'message' => null];
            
        } catch (\Exception $e) {
            Log::error('Error sending CEO notification SMS', [
                'employee_id' => $employee->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return ['sent' => false, 'phone' => null, 'message' => null];
        }
    }
    
    /**
     * Send notification SMS to HR Officers
     */
    private function sendNotificationToHR($employee)
    {
        try {
            $notificationService = app(NotificationService::class);
            
            // Get all HR Officers
            $hrOfficers = User::whereHas('roles', function($query) {
                $query->where('name', 'HR Officer');
            })
            ->where('is_active', true)
            ->get();
            
            if ($hrOfficers->isEmpty()) {
                Log::info('No HR Officer found to notify', []);
                return ['sent' => false, 'phone' => null, 'message' => 'No HR Officer found'];
            }
            
            $results = [];
            $anySent = false;
            $message = null;
            
            foreach ($hrOfficers as $hr) {
                $phone = $hr->mobile ?? $hr->phone;
                if (empty($phone)) {
                    Log::warning('HR Officer has no phone number', [
                        'hr_id' => $hr->id,
                        'hr_name' => $hr->name
                    ]);
                    continue;
                }
                
                if (!$message) {
                    // Build message once
                    $message = "New Employee Registration Completed\n\n";
                    $message .= "Employee registration has been finalized.\n\n";
                    $message .= "Employee Details:\n";
                    $message .= "Name: {$employee->name}\n";
                    $message .= "Employee ID: {$employee->employee_id}\n";
                    $message .= "Department: " . ($employee->primaryDepartment->name ?? 'N/A') . "\n";
                    $message .= "Position: " . ($employee->employee->position ?? 'N/A') . "\n";
                    $message .= "Email: {$employee->email}\n";
                    $message .= "Phone: " . ($employee->phone ?? 'N/A') . "\n";
                    $message .= "Hire Date: " . ($employee->hire_date ? $employee->hire_date->format('d M Y') : 'N/A') . "\n\n";
                    $message .= "Welcome SMS with login credentials has been sent to the employee.\n\n";
                    $message .= "OfisiLink";
                }
                
                $smsSent = $notificationService->sendSMS($phone, $message);
                
                if ($smsSent) {
                    $anySent = true;
                    Log::info('HR notification SMS sent successfully', [
                        'hr_id' => $hr->id,
                        'employee_id' => $employee->id,
                        'phone' => $phone
                    ]);
                } else {
                    Log::warning('HR notification SMS sending failed', [
                        'hr_id' => $hr->id,
                        'employee_id' => $employee->id,
                        'phone' => $phone
                    ]);
                }
                
                $results[] = [
                    'sent' => $smsSent,
                    'phone' => $phone,
                    'recipient' => $hr->name
                ];
            }
            
            // Return the first result or a summary
            if (!empty($results)) {
                $firstResult = $results[0];
                return [
                    'sent' => $anySent,
                    'phone' => $firstResult['phone'],
                    'message' => $message,
                    'recipients' => array_column($results, 'recipient'),
                    'all_results' => $results
                ];
            }
            
            return ['sent' => false, 'phone' => null, 'message' => null];
            
        } catch (\Exception $e) {
            Log::error('Error sending HR notification SMS', [
                'employee_id' => $employee->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            return ['sent' => false, 'phone' => null, 'message' => null];
        }
    }
    
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check if user can edit
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to edit employee details.'], 403);
        }
        
        $employee = User::findOrFail($id);
        // Support both 'section' and 'stage' parameters for compatibility
        $section = $request->get('section', $request->get('stage', 'personal'));
        
        // Map stage names to section names for controller compatibility
        $sectionMap = [
            'banking' => 'bank',
            'next-of-kin' => 'next-of-kin',
            'emergency' => 'emergency',
            'personal' => 'personal',
            'employment' => 'employment',
            'family' => 'family',
            'referees' => 'referees',
            'education' => 'education',
            'deductions' => 'deductions',
            'profile' => 'profile',
            'documents' => 'documents',
            'statutory' => 'statutory',
        ];
        $section = $sectionMap[$section] ?? $section;
        
        // Log the mapping for debugging
        Log::info('Section mapping', [
            'original' => $request->get('section', $request->get('stage', 'personal')),
            'mapped' => $section,
            'employee_id' => $id
        ]);
        
        // Generate Employee ID if missing
        if (empty($employee->employee_id)) {
            try {
                $employeeId = $this->generateEmployeeId($employee->hire_date, $employee->primary_department_id);
                $employee->update(['employee_id' => $employeeId]);
                $employee->refresh();
                Log::info('Auto-generated Employee ID in update method', [
                    'user_id' => $employee->id,
                    'employee_id' => $employeeId
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to generate Employee ID in update method', [
                    'user_id' => $employee->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Ensure employee record exists - CRITICAL
        if (!$employee->employee) {
            Log::warning('Update attempted on user without employee record', [
                'user_id' => $id,
                'updated_by' => $user->id
            ]);
            // Create employee record if missing
            Employee::create([
                'user_id' => $employee->id,
                'position' => $this->getDefaultPosition($employee),
                'employment_type' => 'permanent',
                'hire_date' => $employee->hire_date ?? now(),
                'salary' => 0,
            ]);
            $employee->refresh();
            $employee->load('employee');
        }
        
        // Log update attempt for audit trail
        Log::info('Employee update started', [
            'employee_id' => $id,
            'section' => $section,
            'updated_by' => $user->id,
            'ip_address' => $request->ip()
        ]);
        
        try {
            DB::beginTransaction();
            
            // Handle all 12 sections
            $sectionHandled = false;
            if ($section === 'personal') {
                $this->updatePersonalInfo($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'employment') {
                $this->updateEmployment($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'emergency') {
                $this->updateEmergencyContact($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'family') {
                $this->updateFamily($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'next-of-kin') {
                $this->updateNextOfKin($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'referees') {
                $this->updateReferees($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'bank' || $section === 'banking') {
                $this->updateBankAccounts($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'education') {
                $this->updateEducation($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'deductions') {
                $this->updateDeductions($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'profile') {
                $this->updateProfile($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'documents') {
                $this->updateDocuments($employee, $request);
                $sectionHandled = true;
            } elseif ($section === 'statutory') {
                $this->updateStatutory($employee, $request);
                $sectionHandled = true;
            }
            
            if (!$sectionHandled) {
                throw new \Exception("Unknown section: {$section}. Valid sections are: personal, employment, emergency, family, next-of-kin, referees, bank/banking, education, deductions, profile, documents, statutory");
            }
            
            DB::commit();
            
            Log::info('Employee section updated successfully', [
                'employee_id' => $id,
                'section' => $section,
                'updated_by' => $user->id
            ]);
            
            // Clear cache after update
            cache()->forget("employee_basic_{$id}");
            cache()->forget("employee_full_{$id}");
            
            // Reload employee with all relationships for response
            $employee->refresh();
            $employee->loadMissing([
                'employee' => function($query) {
                    $query->select('id', 'user_id', 'position', 'employment_type', 'salary',
                        'tin_number', 'nssf_number', 'nhif_number', 'heslb_number', 'has_student_loan',
                        'emergency_contact_name', 'emergency_contact_phone', 
                        'emergency_contact_relationship', 'emergency_contact_address');
                },
                'primaryDepartment' => function($query) {
                    $query->select('departments.id', 'departments.name');
                },
                'roles:id,name,display_name',
                'family', 'nextOfKin', 'referees', 'educations', 'bankAccounts'
            ]);
            
            // Load salary deductions if table exists
            if (Schema::hasTable('employee_salary_deductions')) {
                $employee->load('salaryDeductions');
            }
            
            // Log successful update
            Log::info('Employee update completed successfully', [
                'employee_id' => $id,
                'section' => $section,
                'updated_by' => $user->id
            ]);
            
            // Create activity log entry
            $sectionNames = [
                'personal' => 'Personal Information',
                'employment' => 'Employment Information',
                'emergency' => 'Emergency Contact',
                'family' => 'Family Information',
                'next-of-kin' => 'Next of Kin',
                'referees' => 'Referees',
                'bank' => 'Banking Information',
                'banking' => 'Banking Information',
                'education' => 'Education',
                'deductions' => 'Deductions',
                'profile' => 'Profile',
                'documents' => 'Documents',
                'statutory' => 'Statutory Information'
            ];
            
            $sectionName = $sectionNames[$section] ?? ucfirst($section);
            $employeeIdentifier = $employee->employee_id ?? $employee->id;
            
            // Try to create activity log, but don't fail if it doesn't work
            try {
                $oldValues = array_intersect_key($employee->getOriginal(), $employee->getChanges());
                ActivityLogService::logUpdated($employee, $oldValues, $employee->getChanges(), "Updated {$sectionName} for employee: {$employee->name} (ID: {$employeeIdentifier})", [
                    'section' => $section,
                    'section_name' => $sectionName,
                ]);
            } catch (\Exception $logError) {
                // Log the error but don't fail the update
                Log::warning('Failed to create activity log for employee update', [
                    'employee_id' => $id,
                    'section' => $section,
                    'error' => $logError->getMessage()
                ]);
            }
            
            // Ensure response is sent immediately
            // Calculate updated completion percentage (relationships already loaded above)
            $completionPercentage = $this->calculateEmployeeCompletion($employee);
            
            return response()->json([
                'success' => true,
                'message' => "{$sectionName} updated successfully.",
                'employee' => $employee,
                'section' => $section,
                'completion_percentage' => $completionPercentage
            ], 200)->header('Content-Type', 'application/json');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            
            // Log validation error
            Log::warning('Employee update validation failed', [
                'employee_id' => $id,
                'section' => $section,
                'errors' => $e->errors(),
                'updated_by' => $user->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log error
            Log::error('Employee update failed', [
                'employee_id' => $id,
                'section' => $section,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'updated_by' => $user->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating employee details: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    private function updatePersonalInfo($employee, $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id,' . $employee->id,
            'primary_department_id' => 'required|exists:departments,id',
            'hire_date' => 'nullable|date',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }
        
            $userData = $request->only([
                'name', 'email', 'phone', 'employee_id', 'primary_department_id', 'hire_date', 'is_active'
            ]);
        
        // Only update employee_id if provided and different
        if (isset($userData['employee_id']) && empty($userData['employee_id'])) {
            // If employee_id is empty, don't update it (keep existing)
            unset($userData['employee_id']);
        } elseif (isset($userData['employee_id']) && $userData['employee_id'] !== $employee->employee_id) {
            // Validate uniqueness if changed
            $exists = User::where('employee_id', $userData['employee_id'])
                ->where('id', '!=', $employee->id)
                ->exists();
            if ($exists) {
                throw new \Exception('Employee ID already exists. Please use a different ID.');
            }
        } else {
            // Keep existing employee_id if not provided
            unset($userData['employee_id']);
        }
            
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }
            
            $employee->update($userData);
        
        Log::info('Personal info updated', [
            'user_id' => $employee->id,
            'updated_fields' => array_keys($userData)
        ]);
    }
    
    private function updateEmployment($employee, $request)
    {
        $employeeRecord = $employee->employee;
        if (!$employeeRecord) {
            $employeeRecord = Employee::create(['user_id' => $employee->id]);
        }
        
        $employeeData = $request->only(['employment_type', 'salary']);
        
        // Handle position - use custom if provided, otherwise use selected position
        if ($request->filled('position_custom')) {
            $employeeData['position'] = $request->position_custom;
        } elseif ($request->filled('position') && $request->position !== '__custom__') {
            $employeeData['position'] = $request->position;
        } elseif ($request->filled('position') && $request->position === '__custom__' && !$request->filled('position_custom')) {
            // If custom is selected but no custom value, keep existing position
            $employeeData['position'] = $employeeRecord->position;
        }
        
        $employeeRecord->update($employeeData);
        
        // Handle role assignment - check for both 'roles' and 'roles[]' (FormData can send either)
        $roles = [];
        if ($request->has('roles')) {
            $rolesInput = $request->input('roles');
            if (is_array($rolesInput)) {
                $roles = $rolesInput;
            } elseif (is_string($rolesInput) && $rolesInput === '[]') {
                $roles = []; // Empty array string
            }
        } elseif ($request->has('roles[]')) {
            // Handle FormData array format
            $rolesInput = $request->input('roles[]');
            if (is_array($rolesInput)) {
                $roles = $rolesInput;
            } else {
                $roles = [$rolesInput]; // Single value
            }
            // Filter out null/empty values
            $roles = array_filter($roles, function($roleId) {
                return !empty($roleId);
            });
        }
        
        if ($request->has('roles') || $request->has('roles[]')) {
            // Convert to integers and sync
            $roleIds = array_map('intval', $roles);
            $roleIds = array_unique($roleIds);
            
            if (empty($roleIds)) {
                // If empty array, remove all roles
                $employee->roles()->detach();
                Log::info('All roles removed from employee', [
                    'user_id' => $employee->id
                ]);
            } else {
                // Sync roles with pivot data
                $employee->roles()->sync(
                    collect($roleIds)->mapWithKeys(function($roleId) {
                        return [$roleId => [
                            'is_active' => true,
                            'assigned_at' => now()
                        ]];
                    })->toArray()
                );
                Log::info('Roles synced for employee', [
                    'user_id' => $employee->id,
                    'role_ids' => $roleIds,
                    'roles_count' => count($roleIds)
                ]);
            }
        } else {
            // If roles not provided, don't change existing roles
            Log::info('Roles not provided in request, keeping existing roles', [
                'user_id' => $employee->id,
                'current_roles' => $employee->roles->pluck('id')->toArray()
            ]);
        }
    }
    
    private function updateEmergencyContact($employee, $request)
    {
            $employeeRecord = $employee->employee;
        if (!$employeeRecord) {
            $employeeRecord = Employee::create(['user_id' => $employee->id]);
        }
        
        // Only update columns that exist in the database
        $updateData = [];
        
        // Check if columns exist before adding to update data
        $columns = ['emergency_contact_name', 'emergency_contact_phone', 
                     'emergency_contact_relationship', 'emergency_contact_address'];
        
        foreach ($columns as $column) {
            if (Schema::hasColumn('employees', $column)) {
                // Use input() instead of get() to ensure we get empty strings too
                // Check if the field exists in the request (even if empty)
                if ($request->has($column)) {
                    $value = $request->input($column);
                    // Allow null/empty strings - they should be saved to clear fields
                    $updateData[$column] = $value !== null ? (string)$value : null;
                } else {
                    // If field not in request, set to null to preserve existing value or clear
                    // Only update if explicitly provided
                    $updateData[$column] = null;
                }
            }
        }
        
        // Log the data being saved
        Log::info('Emergency contact update data', [
            'user_id' => $employee->id,
            'update_data' => $updateData,
            'request_data' => $request->only(['emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship', 'emergency_contact_address']),
            'all_request_keys' => array_keys($request->all())
        ]);
        
        // Always update if we have data to update (even if some fields are empty)
        if (!empty($updateData)) {
            $result = $employeeRecord->update($updateData);
            $savedData = $employeeRecord->fresh()->only(['emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship', 'emergency_contact_address']);
            Log::info('Emergency contact saved successfully', [
                'user_id' => $employee->id,
                'employee_record_id' => $employeeRecord->id,
                'updated_fields' => array_keys($updateData),
                'update_result' => $result,
                'saved_data' => $savedData
            ]);
        } else {
            Log::warning('No emergency contact data to update', [
                'user_id' => $employee->id,
                'request_keys' => array_keys($request->all()),
                'columns_checked' => $columns
            ]);
        }
    }
    
    private function updateFamily($employee, $request)
    {
        if ($request->has('family') && is_array($request->family)) {
            $existingIds = [];
            $processedCount = 0;
            
            foreach ($request->family as $index => $familyMember) {
                // Skip empty entries
                if (empty($familyMember) || !is_array($familyMember)) {
                    continue;
                }
                
                // Validate required fields - name is typically required
                $hasName = isset($familyMember['name']) && !empty(trim($familyMember['name']));
                
                // Require at least name
                if (!$hasName) {
                    Log::warning('Skipping family member entry without required fields', [
                        'user_id' => $employee->id,
                        'index' => $index,
                        'has_name' => $hasName
                    ]);
                    continue;
                }
                
                $processedCount++;
                
                $familyData = [
                    'user_id' => $employee->id,
                    'name' => trim($familyMember['name']),
                    'relationship' => !empty($familyMember['relationship']) ? trim($familyMember['relationship']) : null,
                    'date_of_birth' => !empty($familyMember['date_of_birth']) ? $familyMember['date_of_birth'] : null,
                    'gender' => !empty($familyMember['gender']) ? trim($familyMember['gender']) : null,
                    'occupation' => !empty($familyMember['occupation']) ? trim($familyMember['occupation']) : null,
                    'phone' => !empty($familyMember['phone']) ? trim($familyMember['phone']) : null,
                    'is_dependent' => isset($familyMember['is_dependent']) ? filter_var($familyMember['is_dependent'], FILTER_VALIDATE_BOOLEAN) : false,
                ];
                
                if (isset($familyMember['id']) && !empty($familyMember['id'])) {
                    // Update existing record
                    $existingIds[] = (int)$familyMember['id'];
                    EmployeeFamily::where('id', $familyMember['id'])
                        ->where('user_id', $employee->id)
                        ->update($familyData);
                } else {
                    // Create new record
                    $newRecord = EmployeeFamily::create($familyData);
                    $existingIds[] = $newRecord->id;
                }
            }
            
            // Delete records that were removed (only if we have data to process)
            if (!empty($existingIds)) {
                EmployeeFamily::where('user_id', $employee->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();
            } elseif (empty($request->family) || $processedCount === 0) {
                // If empty array sent or no valid entries, don't delete - user might just be viewing
                Log::info('Empty or invalid family array received, keeping existing records', [
                    'user_id' => $employee->id,
                    'processed_count' => $processedCount
                ]);
            }
            
            Log::info('Family information updated', [
                'user_id' => $employee->id,
                'records_count' => count($existingIds),
                'processed_count' => $processedCount
            ]);
        } else {
            // If no family data provided, don't delete - user might just be viewing
            Log::info('Family not provided in request, keeping existing records', [
                'user_id' => $employee->id,
                'current_count' => EmployeeFamily::where('user_id', $employee->id)->count()
            ]);
        }
    }
    
    private function updateNextOfKin($employee, $request)
    {
        if ($request->has('next_of_kin') && is_array($request->next_of_kin)) {
            $existingIds = [];
            $processedCount = 0;
            
            foreach ($request->next_of_kin as $index => $kin) {
                // Skip empty entries
                if (empty($kin) || !is_array($kin)) {
                    continue;
                }
                
                // Validate required fields
                $hasName = isset($kin['name']) && !empty(trim($kin['name']));
                $hasRelationship = isset($kin['relationship']) && !empty(trim($kin['relationship']));
                $hasPhone = isset($kin['phone']) && !empty(trim($kin['phone']));
                $hasAddress = isset($kin['address']) && !empty(trim($kin['address']));
                
                // Require name, relationship, phone, and address
                if (!$hasName || !$hasRelationship || !$hasPhone || !$hasAddress) {
                    Log::warning('Skipping next of kin entry without required fields', [
                        'user_id' => $employee->id,
                        'index' => $index,
                        'has_name' => $hasName,
                        'has_relationship' => $hasRelationship,
                        'has_phone' => $hasPhone,
                        'has_address' => $hasAddress
                    ]);
                    continue;
                }
                
                $processedCount++;
                
                $kinData = [
                    'user_id' => $employee->id,
                    'name' => trim($kin['name']),
                    'relationship' => trim($kin['relationship']),
                    'phone' => trim($kin['phone']),
                    'email' => !empty($kin['email']) ? trim($kin['email']) : null,
                    'address' => trim($kin['address']),
                    'id_number' => !empty($kin['id_number']) ? trim($kin['id_number']) : null,
                ];
                
                if (isset($kin['id']) && !empty($kin['id'])) {
                    // Update existing record
                    $existingIds[] = (int)$kin['id'];
                    EmployeeNextOfKin::where('id', $kin['id'])
                        ->where('user_id', $employee->id)
                        ->update($kinData);
                } else {
                    // Create new record
                    $newRecord = EmployeeNextOfKin::create($kinData);
                    $existingIds[] = $newRecord->id;
                }
            }
            
            // Delete records that were removed (only if we have data to process)
            if (!empty($existingIds)) {
                EmployeeNextOfKin::where('user_id', $employee->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();
            } elseif (empty($request->next_of_kin) || $processedCount === 0) {
                // If empty array sent or no valid entries, don't delete - user might just be viewing
                Log::info('Empty or invalid next of kin array received, keeping existing records', [
                    'user_id' => $employee->id,
                    'processed_count' => $processedCount
                ]);
            }
            
            Log::info('Next of kin updated', [
                'user_id' => $employee->id,
                'records_count' => count($existingIds),
                'processed_count' => $processedCount
            ]);
        } else {
            // If no next of kin data provided, don't delete - user might just be viewing
            Log::info('Next of kin not provided in request, keeping existing records', [
                'user_id' => $employee->id,
                'current_count' => EmployeeNextOfKin::where('user_id', $employee->id)->count()
            ]);
        }
    }
    
    private function updateReferees($employee, $request)
    {
        // Check if referees data exists in request (even if empty array)
        if ($request->has('referees')) {
            $refereesData = $request->input('referees');
            
            // Handle both array and empty string/null cases
            if (!is_array($refereesData)) {
                // Check if it's a JSON string
                if (is_string($refereesData) && $refereesData === '[]') {
                    $refereesData = [];
                } else {
                    $refereesData = [];
                }
            }
            
            $existingIds = [];
            $processedCount = 0;
            
            foreach ($refereesData as $index => $referee) {
                // Skip completely empty entries
                if (empty($referee) || !is_array($referee)) {
                    continue;
                }
                
                // Check if this entry has required fields: name and phone
                $hasName = isset($referee['name']) && !empty(trim($referee['name']));
                $hasPhone = isset($referee['phone']) && !empty(trim($referee['phone']));
                
                if (!$hasName || !$hasPhone) {
                    // Skip entries without required fields
                    Log::warning('Skipping referee entry without required fields', [
                        'user_id' => $employee->id,
                        'index' => $index,
                        'has_name' => $hasName,
                        'has_phone' => $hasPhone,
                        'data' => $referee
                    ]);
                    continue;
                }
                
                $processedCount++;
                
                // Prepare referee data - ensure all fields are properly set
                $refereeData = [
                    'user_id' => $employee->id,
                    'name' => trim($referee['name']),
                    'phone' => trim($referee['phone']), // Required field
                    'position' => !empty($referee['position']) ? trim($referee['position']) : null,
                    'organization' => !empty($referee['organization']) ? trim($referee['organization']) : null,
                    'email' => !empty($referee['email']) ? trim($referee['email']) : null,
                    'relationship' => !empty($referee['relationship']) ? trim($referee['relationship']) : null,
                    'address' => !empty($referee['address']) ? trim($referee['address']) : null,
                    'order' => (int)$index
                ];
                
                if (isset($referee['id']) && !empty($referee['id'])) {
                    // Update existing record
                    $existingIds[] = (int)$referee['id'];
                    EmployeeReferee::where('id', $referee['id'])
                        ->where('user_id', $employee->id)
                        ->update($refereeData);
                } else {
                    // Create new record
                    try {
                        $newRecord = EmployeeReferee::create($refereeData);
                    $existingIds[] = $newRecord->id;
                        Log::info('New referee created', [
                            'user_id' => $employee->id,
                            'referee_id' => $newRecord->id,
                            'name' => $refereeData['name']
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create referee', [
                            'user_id' => $employee->id,
                            'error' => $e->getMessage(),
                            'data' => $refereeData
                        ]);
                        throw $e;
                    }
                }
            }
            
            // Delete records that were removed (only if we have data to process)
            if (!empty($existingIds)) {
                $deletedCount = EmployeeReferee::where('user_id', $employee->id)
                ->whereNotIn('id', $existingIds)
                ->delete();
                Log::info('Referees cleanup completed', [
                    'user_id' => $employee->id,
                    'deleted_count' => $deletedCount
                ]);
            } elseif (empty($refereesData) || $processedCount === 0) {
                // If empty array sent or no valid entries, don't delete - user might just be viewing
                Log::info('Empty or invalid referees array received, keeping existing records', [
                    'user_id' => $employee->id,
                    'processed_count' => $processedCount
                ]);
            }
            
            Log::info('Referees updated', [
                'user_id' => $employee->id,
                'records_count' => count($existingIds),
                'processed_count' => $processedCount,
                'request_keys' => array_keys($request->all())
            ]);
        } else {
            // If referees key not in request, don't change anything
            Log::info('Referees not provided in request, keeping existing records', [
                'user_id' => $employee->id,
                'current_count' => EmployeeReferee::where('user_id', $employee->id)->count()
            ]);
        }
    }
    
    private function updateBankAccounts($employee, $request)
    {
        if ($request->has('bank_accounts') && is_array($request->bank_accounts)) {
            $existingIds = [];
            $primaryIndex = $request->input('primary_bank_account');
            $processedCount = 0;
            
            // First, unset all primary flags
            BankAccount::where('user_id', $employee->id)->update(['is_primary' => false]);
            
            foreach ($request->bank_accounts as $index => $account) {
                // Skip empty entries
                if (empty($account) || !is_array($account)) {
                    continue;
                }
                
                // Validate required fields
                $hasBankName = isset($account['bank_name']) && !empty(trim($account['bank_name']));
                $hasAccountNumber = isset($account['account_number']) && !empty(trim($account['account_number']));
                
                // Require at least bank_name and account_number
                if (!$hasBankName || !$hasAccountNumber) {
                    Log::warning('Skipping bank account entry without required fields', [
                        'user_id' => $employee->id,
                        'index' => $index,
                        'has_bank_name' => $hasBankName,
                        'has_account_number' => $hasAccountNumber
                    ]);
                    continue;
                }
                
                $processedCount++;
                
                // Prepare account data - clean and validate
                $accountData = [
                    'user_id' => $employee->id,
                    'bank_name' => trim($account['bank_name']),
                    'account_number' => trim($account['account_number']),
                    'account_name' => !empty($account['account_name']) ? trim($account['account_name']) : null,
                    'branch_name' => !empty($account['branch_name']) ? trim($account['branch_name']) : null,
                    'swift_code' => !empty($account['swift_code']) ? trim($account['swift_code']) : null,
                ];
                
                // Determine if this is the primary account
                $isPrimary = false;
                if ($primaryIndex !== null && (string)$primaryIndex == (string)$index) {
                    $isPrimary = true;
                } elseif (isset($account['is_primary']) && ($account['is_primary'] == '1' || $account['is_primary'] === true || $account['is_primary'] === 'true')) {
                    $isPrimary = true;
                } elseif ($processedCount === 1 && empty($existingIds)) {
                    // Make first account primary if no primary is set
                    $isPrimary = true;
                }
                
                $accountData['is_primary'] = $isPrimary;
                
                if (isset($account['id']) && !empty($account['id'])) {
                    // Update existing record
                    $existingIds[] = (int)$account['id'];
                    try {
                        BankAccount::where('id', $account['id'])
                            ->where('user_id', $employee->id)
                            ->update($accountData);
                        Log::info('Bank account updated', [
                            'user_id' => $employee->id,
                            'account_id' => $account['id'],
                            'bank_name' => $accountData['bank_name']
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to update bank account', [
                            'user_id' => $employee->id,
                            'account_id' => $account['id'],
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                } else {
                    // Create new record
                    try {
                        $newRecord = BankAccount::create($accountData);
                        $existingIds[] = $newRecord->id;
                        Log::info('Bank account created', [
                            'user_id' => $employee->id,
                            'account_id' => $newRecord->id,
                            'bank_name' => $accountData['bank_name']
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create bank account', [
                            'user_id' => $employee->id,
                            'error' => $e->getMessage(),
                            'data' => $accountData
                        ]);
                        throw $e;
                    }
                }
            }
            
            // Delete records that were removed (only if we have data to process)
            if (!empty($existingIds)) {
                $deletedCount = BankAccount::where('user_id', $employee->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();
                Log::info('Bank account records cleanup completed', [
                    'user_id' => $employee->id,
                    'deleted_count' => $deletedCount
                ]);
            } elseif (empty($request->bank_accounts) || $processedCount === 0) {
                // If empty array sent or no valid entries, don't delete - user might just be viewing
                Log::info('Empty or invalid bank accounts array received, keeping existing records', [
                    'user_id' => $employee->id,
                    'processed_count' => $processedCount
                ]);
            }
            
            Log::info('Bank accounts updated successfully', [
                'user_id' => $employee->id,
                'records_count' => count($existingIds),
                'processed_count' => $processedCount
            ]);
        } else {
            // If no bank_accounts key in request, don't change anything
            Log::info('Bank accounts not provided in request, keeping existing records', [
                'user_id' => $employee->id,
                'current_count' => BankAccount::where('user_id', $employee->id)->count()
            ]);
        }
    }
    
    private function updateEducation($employee, $request)
    {
        // Check if educations data exists in request (even if empty array)
        if ($request->has('educations')) {
            $educationsData = $request->input('educations');
            
            // Handle both array and empty string/null cases
            if (!is_array($educationsData)) {
                // Check if it's a JSON string
                if (is_string($educationsData) && $educationsData === '[]') {
                    $educationsData = [];
                } else {
                    $educationsData = [];
                }
            }
            
            $existingIds = [];
            $processedCount = 0;
            
            foreach ($educationsData as $index => $education) {
                // Skip completely empty entries
                if (empty($education) || !is_array($education)) {
                    continue;
                }
                
                // Check if this entry has at least institution_name (required field)
                $hasInstitution = isset($education['institution_name']) && !empty(trim($education['institution_name']));
                $hasQualification = isset($education['qualification']) && !empty(trim($education['qualification']));
                
                // Require at least institution_name or qualification
                if (!$hasInstitution && !$hasQualification) {
                    // Skip entries without required fields
                    Log::warning('Skipping education entry without institution or qualification', [
                        'user_id' => $employee->id,
                        'index' => $index,
                        'data' => $education
                    ]);
                    continue;
                }
                
                $processedCount++;
                
                // Prepare education data - ensure all fields are properly set
                $educationData = [
                    'user_id' => $employee->id,
                    'institution_name' => trim($education['institution_name'] ?? ''),
                    'qualification' => trim($education['qualification'] ?? ''),
                    'field_of_study' => trim($education['field_of_study'] ?? ''),
                    'start_year' => !empty($education['start_year']) ? (int)$education['start_year'] : null,
                    'end_year' => !empty($education['end_year']) ? (int)$education['end_year'] : null,
                    'grade' => trim($education['grade'] ?? ''),
                    'description' => trim($education['description'] ?? ''),
                    'order' => (int)$index
                ];
                
                // Remove empty strings and convert to null for optional fields
                foreach ($educationData as $key => $value) {
                    if ($key !== 'user_id' && $key !== 'order' && $key !== 'start_year' && $key !== 'end_year' && empty($value)) {
                        $educationData[$key] = null;
                    }
                }
                
                if (isset($education['id']) && !empty($education['id'])) {
                    // Update existing record
                    $existingIds[] = (int)$education['id'];
                    EmployeeEducation::where('id', $education['id'])
                        ->where('user_id', $employee->id)
                        ->update($educationData);
                } else {
                    // Create new record
                    try {
                        $newRecord = EmployeeEducation::create($educationData);
                    $existingIds[] = $newRecord->id;
                        Log::info('New education record created', [
                            'user_id' => $employee->id,
                            'education_id' => $newRecord->id,
                            'institution' => $educationData['institution_name']
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create education record', [
                            'user_id' => $employee->id,
                            'error' => $e->getMessage(),
                            'data' => $educationData
                        ]);
                        throw $e;
                    }
                }
            }
            
            // Delete records that were removed (only if we have data to process)
            if (!empty($existingIds)) {
                $deletedCount = EmployeeEducation::where('user_id', $employee->id)
                ->whereNotIn('id', $existingIds)
                ->delete();
                Log::info('Education records cleanup completed', [
                    'user_id' => $employee->id,
                    'deleted_count' => $deletedCount
                ]);
            } elseif (empty($educationsData) || $processedCount === 0) {
                // If empty array sent or no valid entries, don't delete - user might just be viewing
                Log::info('Empty or invalid educations array received, keeping existing records', [
                    'user_id' => $employee->id,
                    'processed_count' => $processedCount
                ]);
            }
            
            Log::info('Education records updated', [
                'user_id' => $employee->id,
                'records_count' => count($existingIds),
                'processed_count' => $processedCount,
                'request_keys' => array_keys($request->all())
            ]);
        } else {
            // If educations key not in request, don't change anything
            Log::info('Educations not provided in request, keeping existing records', [
                'user_id' => $employee->id,
                'current_count' => EmployeeEducation::where('user_id', $employee->id)->count()
            ]);
        }
    }
    
    private function updateStatutory($employee, $request)
    {
        $employeeRecord = $employee->employee;
        if (!$employeeRecord) {
            $employeeRecord = Employee::create(['user_id' => $employee->id]);
        }
        
        $updateData = $request->only([
            'tin_number', 'nssf_number', 'nhif_number', 'heslb_number', 'has_student_loan'
        ]);
        
        // Handle boolean conversion for has_student_loan
        if (isset($updateData['has_student_loan'])) {
            $updateData['has_student_loan'] = filter_var($updateData['has_student_loan'], FILTER_VALIDATE_BOOLEAN);
        }
        
        $employeeRecord->update($updateData);
        
        // Handle salary deductions (loans, advances, etc.)
        if ($request->has('deductions') && is_array($request->deductions)) {
            $deductionsData = $request->input('deductions');
            $existingIds = [];
            $processedCount = 0;
            
            foreach ($deductionsData as $index => $deduction) {
                // Skip empty entries
                if (empty($deduction) || !is_array($deduction)) {
                    continue;
                }
                
                // Validate required fields
                $hasDeductionType = isset($deduction['deduction_type']) && !empty(trim($deduction['deduction_type']));
                $hasAmount = isset($deduction['amount']) && !empty($deduction['amount']) && (float)$deduction['amount'] > 0;
                $hasStartDate = isset($deduction['start_date']) && !empty(trim($deduction['start_date']));
                
                // Require deduction_type, amount, and start_date
                if (!$hasDeductionType || !$hasAmount || !$hasStartDate) {
                    Log::warning('Skipping deduction entry without required fields', [
                        'user_id' => $employee->id,
                        'index' => $index,
                        'has_type' => $hasDeductionType,
                        'has_amount' => $hasAmount,
                        'has_start_date' => $hasStartDate
                    ]);
                    continue;
                }
                
                // Validate date range - start_date must be before end_date if end_date is provided
                $startDate = $deduction['start_date'];
                $endDate = !empty($deduction['end_date']) ? $deduction['end_date'] : null;
                
                if ($endDate && strtotime($startDate) > strtotime($endDate)) {
                    Log::warning('Invalid date range for deduction - start_date is after end_date', [
                        'user_id' => $employee->id,
                        'index' => $index,
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ]);
                    throw new \Exception("Deduction at index {$index}: Start date must be before or equal to end date.");
                }
                
                $processedCount++;
                
                // Prepare deduction data - clean and validate
                $deductionData = [
                    'employee_id' => $employee->id,
                    'deduction_type' => trim($deduction['deduction_type']),
                    'description' => !empty($deduction['description']) ? trim($deduction['description']) : null,
                    'amount' => (float)($deduction['amount']),
                    'frequency' => in_array($deduction['frequency'] ?? 'monthly', ['monthly', 'one-time']) 
                        ? ($deduction['frequency'] ?? 'monthly') 
                        : 'monthly',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => isset($deduction['is_active']) 
                        ? filter_var($deduction['is_active'], FILTER_VALIDATE_BOOLEAN) 
                        : true,
                    'notes' => !empty($deduction['notes']) ? trim($deduction['notes']) : null,
                ];
                
                if (isset($deduction['id']) && !empty($deduction['id'])) {
                    // Update existing record
                    $existingIds[] = (int)$deduction['id'];
                    try {
                        EmployeeSalaryDeduction::where('id', $deduction['id'])
                            ->where('employee_id', $employee->id)
                            ->update($deductionData);
                        Log::info('Salary deduction updated', [
                            'user_id' => $employee->id,
                            'deduction_id' => $deduction['id'],
                            'type' => $deductionData['deduction_type'],
                            'amount' => $deductionData['amount']
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to update salary deduction', [
                            'user_id' => $employee->id,
                            'deduction_id' => $deduction['id'],
                            'error' => $e->getMessage()
                        ]);
                        throw $e;
                    }
                } else {
                    // Create new record
                    try {
                        $newRecord = EmployeeSalaryDeduction::create($deductionData);
                        $existingIds[] = $newRecord->id;
                        Log::info('Salary deduction created', [
                            'user_id' => $employee->id,
                            'deduction_id' => $newRecord->id,
                            'type' => $deductionData['deduction_type'],
                            'amount' => $deductionData['amount']
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to create salary deduction', [
                            'user_id' => $employee->id,
                            'error' => $e->getMessage(),
                            'data' => $deductionData
                        ]);
                        throw $e;
                    }
                }
            }
            
            // Delete records that were removed (only if we have data to process)
            if (!empty($existingIds)) {
                $deletedCount = EmployeeSalaryDeduction::where('employee_id', $employee->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();
                Log::info('Salary deduction records cleanup completed', [
                    'user_id' => $employee->id,
                    'deleted_count' => $deletedCount
                ]);
            } elseif (empty($deductionsData) || $processedCount === 0) {
                // If empty array sent or no valid entries, don't delete - user might just be viewing
                Log::info('Empty or invalid deductions array received, keeping existing records', [
                    'user_id' => $employee->id,
                    'processed_count' => $processedCount
                ]);
            }
            
            Log::info('Salary deductions updated successfully', [
                'user_id' => $employee->id,
                'deductions_count' => count($existingIds),
                'processed_count' => $processedCount
            ]);
        } else {
            // If no deductions key in request, don't change anything
            Log::info('Deductions not provided in request, keeping existing records', [
                'user_id' => $employee->id,
                'current_count' => EmployeeSalaryDeduction::where('employee_id', $employee->id)->count()
            ]);
        }
        
        Log::info('Statutory information updated', [
            'user_id' => $employee->id,
            'updated_fields' => array_keys($updateData)
        ]);
    }
    
    private function updateDeductions($employee, $request)
    {
        // Handle salary deductions (loans, advances, etc.)
        if ($request->has('deductions') && is_array($request->deductions)) {
            $deductionsData = $request->input('deductions');
            $existingIds = [];
            $processedCount = 0;
            
            foreach ($deductionsData as $index => $deduction) {
                // Skip empty entries
                if (empty($deduction) || !is_array($deduction)) {
                    continue;
                }
                
                // Validate required fields
                $hasDeductionType = isset($deduction['deduction_type']) && !empty(trim($deduction['deduction_type']));
                $hasAmount = isset($deduction['amount']) && !empty($deduction['amount']) && (float)$deduction['amount'] > 0;
                $hasStartDate = isset($deduction['start_date']) && !empty(trim($deduction['start_date']));
                
                // Require deduction_type, amount, and start_date
                if (!$hasDeductionType || !$hasAmount || !$hasStartDate) {
                    Log::warning('Skipping deduction entry without required fields', [
                        'user_id' => $employee->id,
                        'index' => $index,
                        'has_type' => $hasDeductionType,
                        'has_amount' => $hasAmount,
                        'has_start_date' => $hasStartDate
                    ]);
                    continue;
                }
                
                // Validate date range
                $startDate = $deduction['start_date'];
                $endDate = !empty($deduction['end_date']) ? $deduction['end_date'] : null;
                
                if ($endDate && strtotime($startDate) > strtotime($endDate)) {
                    throw new \Exception("Deduction at index {$index}: Start date must be before or equal to end date.");
                }
                
                $processedCount++;
                
                $deductionData = [
                    'employee_id' => $employee->id,
                    'deduction_type' => trim($deduction['deduction_type']),
                    'description' => !empty($deduction['description']) ? trim($deduction['description']) : null,
                    'amount' => (float)($deduction['amount']),
                    'frequency' => in_array($deduction['frequency'] ?? 'monthly', ['monthly', 'one-time']) 
                        ? ($deduction['frequency'] ?? 'monthly') 
                        : 'monthly',
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_active' => isset($deduction['is_active']) 
                        ? filter_var($deduction['is_active'], FILTER_VALIDATE_BOOLEAN) 
                        : true,
                    'notes' => !empty($deduction['notes']) ? trim($deduction['notes']) : null,
                ];
                
                if (isset($deduction['id']) && !empty($deduction['id'])) {
                    $existingIds[] = (int)$deduction['id'];
                    EmployeeSalaryDeduction::where('id', $deduction['id'])
                        ->where('employee_id', $employee->id)
                        ->update($deductionData);
                } else {
                    $newDeduction = EmployeeSalaryDeduction::create($deductionData);
                    $existingIds[] = $newDeduction->id;
                }
            }
            
            // Delete records that were removed
            if (!empty($existingIds)) {
                EmployeeSalaryDeduction::where('employee_id', $employee->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();
            }
            
            Log::info('Deductions updated', [
                'user_id' => $employee->id,
                'deductions_count' => count($existingIds),
                'processed_count' => $processedCount
            ]);
        }
    }
    
    private function updateProfile($employee, $request)
    {
        $profileData = $request->only(['marital_status', 'date_of_birth', 'gender', 'nationality', 'address']);
        
        // Update user profile fields
        $updateData = [];
        if (isset($profileData['marital_status']) && !empty($profileData['marital_status'])) {
            $updateData['marital_status'] = $profileData['marital_status'];
        } elseif (isset($profileData['marital_status']) && empty($profileData['marital_status'])) {
            $updateData['marital_status'] = null;
        }
        if (isset($profileData['date_of_birth']) && !empty($profileData['date_of_birth'])) {
            $updateData['date_of_birth'] = $profileData['date_of_birth'];
        } elseif (isset($profileData['date_of_birth']) && empty($profileData['date_of_birth'])) {
            $updateData['date_of_birth'] = null;
        }
        if (isset($profileData['gender']) && !empty($profileData['gender'])) {
            $updateData['gender'] = $profileData['gender'];
        } elseif (isset($profileData['gender']) && empty($profileData['gender'])) {
            $updateData['gender'] = null;
        }
        if (isset($profileData['nationality']) && !empty($profileData['nationality'])) {
            $updateData['nationality'] = $profileData['nationality'];
        } elseif (isset($profileData['nationality']) && empty($profileData['nationality'])) {
            $updateData['nationality'] = null;
        }
        if (isset($profileData['address']) && !empty($profileData['address'])) {
            $updateData['address'] = $profileData['address'];
        } elseif (isset($profileData['address']) && empty($profileData['address'])) {
            $updateData['address'] = null;
        }
        
        if (!empty($updateData)) {
            $employee->update($updateData);
        }
        
        // Handle photo upload
        if ($request->hasFile('photo')) {
            if (!Storage::exists('public/photos')) {
                Storage::makeDirectory('public/photos');
            }
            
            if ($employee->photo && Storage::exists('public/photos/' . $employee->photo)) {
                Storage::delete('public/photos/' . $employee->photo);
            }
            
            $photoName = time() . '_' . $employee->id . '.' . $request->file('photo')->getClientOriginalExtension();
            $request->file('photo')->storeAs('public/photos', $photoName);
            $employee->update(['photo' => $photoName]);
        }
        
        Log::info('Profile updated', [
            'user_id' => $employee->id,
            'has_photo' => $request->hasFile('photo'),
            'updated_fields' => array_keys($updateData)
        ]);
    }
    
    private function updateDocuments($employee, $request)
    {
        if ($request->has('documents') && is_array($request->documents)) {
            if (!Storage::exists('public/documents')) {
                Storage::makeDirectory('public/documents');
            }
            
            $existingIds = [];
            
            foreach ($request->documents as $index => $doc) {
                // Skip empty entries
                if (empty($doc) || !is_array($doc)) {
                    continue;
                }
                
                // Skip if both document_type and document_name are empty (documents are optional)
                if (empty($doc['document_type']) && empty($doc['document_name'])) {
                    continue;
                }
                
                // Set defaults if not provided
                if (empty($doc['document_type'])) {
                    $doc['document_type'] = 'Other';
                }
                if (empty($doc['document_name'])) {
                    $doc['document_name'] = 'Document';
                }
                
                $documentData = [
                    'user_id' => $employee->id,
                    'document_type' => trim($doc['document_type']),
                    'document_name' => trim($doc['document_name']),
                    'document_number' => !empty($doc['document_number']) ? trim($doc['document_number']) : null,
                    'issue_date' => !empty($doc['issue_date']) ? $doc['issue_date'] : null,
                    'expiry_date' => !empty($doc['expiry_date']) ? $doc['expiry_date'] : null,
                    'issued_by' => !empty($doc['issued_by']) ? trim($doc['issued_by']) : null,
                    'description' => !empty($doc['description']) ? trim($doc['description']) : null,
                ];
                
                // Handle file upload
                $fileKey = "documents.{$index}.file";
                if ($request->hasFile($fileKey)) {
                    $file = $request->file($fileKey);
                    $fileName = time() . '_' . $employee->id . '_' . $file->getClientOriginalName();
                    $filePath = $file->storeAs('public/documents', $fileName);
                    $documentData['file_path'] = $fileName;
                    $documentData['file_name'] = $file->getClientOriginalName();
                    $documentData['file_type'] = $file->getClientMimeType();
                    $documentData['file_size'] = $file->getSize();
                } elseif (isset($doc['id']) && !empty($doc['id'])) {
                    // If updating without new file, keep existing file info
                    $existingDoc = EmployeeDocument::where('id', $doc['id'])
                        ->where('user_id', $employee->id)
                        ->first();
                    if ($existingDoc) {
                        $documentData['file_path'] = $existingDoc->file_path;
                        $documentData['file_name'] = $existingDoc->file_name;
                        $documentData['file_type'] = $existingDoc->file_type;
                        $documentData['file_size'] = $existingDoc->file_size;
                    }
                }
                
                // Add default values for required fields if not set
                if (!isset($documentData['file_name'])) {
                    $documentData['file_name'] = $documentData['document_name'] . '.pdf';
                }
                if (!isset($documentData['file_type'])) {
                    $documentData['file_type'] = 'application/pdf';
                }
                if (!isset($documentData['file_size'])) {
                    $documentData['file_size'] = 0;
                }
                if (!isset($documentData['file_path'])) {
                    $documentData['file_path'] = null;
                }
                
                $documentData['is_active'] = true;
                $documentData['uploaded_by'] = Auth::id();
                
                if (isset($doc['id']) && !empty($doc['id'])) {
                    // Update existing document
                    $existingIds[] = (int)$doc['id'];
                    $existingDoc = EmployeeDocument::where('id', $doc['id'])
                        ->where('user_id', $employee->id)
                        ->first();
                    
                    if ($existingDoc) {
                        // Delete old file if new one is uploaded
                        if ($request->hasFile($fileKey) && $existingDoc->file_path) {
                            if (Storage::exists('public/documents/' . $existingDoc->file_path)) {
                                Storage::delete('public/documents/' . $existingDoc->file_path);
                            }
                        }
                        
                        $existingDoc->update($documentData);
                    }
                } else {
                    // Create new document (documents are optional, can be created without file)
                    $newDoc = EmployeeDocument::create($documentData);
                    $existingIds[] = $newDoc->id;
                }
            }
            
            // Delete documents that were removed (only if we have data to process)
            if (!empty($existingIds)) {
                EmployeeDocument::where('user_id', $employee->id)
                    ->whereNotIn('id', $existingIds)
                    ->delete();
            }
            
            Log::info('Documents updated', [
                'user_id' => $employee->id,
                'documents_count' => count($existingIds)
            ]);
        }
    }
    
    public function toggleStatus($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to change employee status.'], 403);
        }
        
        $employee = User::findOrFail($id);
        
        // Prevent deactivating system admin
        if ($employee->hasRole('System Admin') && $employee->is_active) {
            return response()->json(['success' => false, 'message' => 'Cannot deactivate System Administrator.'], 400);
        }
        
        $employee->update(['is_active' => !$employee->is_active]);
        
        $status = $employee->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "Employee {$status} successfully.",
            'is_active' => $employee->is_active
        ]);
    }
    
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Only HR and System Admin can create employees
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to create employees.'], 403);
        }
        
        // Check if this is a draft save (stage-by-stage) - IMPORTANT: Every step is saved automatically
        $isDraft = $request->has('save_as_draft') && $request->save_as_draft == '1';
        $stage = $request->get('stage', 'personal');
        
        // Log the registration attempt for audit trail
        Log::info('Employee registration started', [
            'stage' => $stage,
            'is_draft' => $isDraft,
            'user_id' => $request->user_id ?? null,
            'created_by' => $user->id,
            'ip_address' => $request->ip()
        ]);
        
        // Different validation rules based on stage
        $rules = [];
        
        if ($stage === 'personal') {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'employee_id' => 'nullable|string|max:50', // Will be auto-generated if not provided
                'primary_department_id' => 'required|exists:departments,id',
                'hire_date' => 'nullable|date',
            ];
            
            if ($request->has('user_id') && $request->user_id) {
                // Updating existing user
                $rules['email'] = 'required|email|max:255|unique:users,email,' . $request->user_id;
                $rules['employee_id'] = 'nullable|string|max:50|unique:users,employee_id,' . $request->user_id;
            } else {
                // Creating new user - employee_id will be auto-generated if not provided
                $rules['email'] = 'required|email|max:255|unique:users,email';
                // Password is optional - will default to 'welcome123' if not provided
                if ($request->filled('password')) {
                    $rules['password'] = 'string|min:8|confirmed';
                }
                // employee_id is optional - will be auto-generated if not provided
            }
        } elseif ($stage === 'employment') {
            $rules = [
                'position' => 'nullable|string|max:255',
                'employment_type' => 'nullable|string|in:permanent,contract,intern',
                'salary' => 'nullable|numeric|min:0',
                'roles' => 'nullable|array',
                'roles.*' => 'exists:roles,id',
            ];
        } elseif ($stage === 'banking') {
            $rules = [
                'bank_accounts' => 'nullable|array',
                'bank_accounts.*.bank_name' => 'required|string|max:255',
                'bank_accounts.*.account_number' => 'required|string|max:50',
                'bank_accounts.*.account_name' => 'nullable|string|max:255',
                'bank_accounts.*.branch_name' => 'nullable|string|max:255',
                'bank_accounts.*.swift_code' => 'nullable|string|max:50',
                'bank_accounts.*.is_primary' => 'boolean',
            ];
        } elseif ($stage === 'emergency') {
            $rules = [
                'emergency_contact_name' => 'nullable|string|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relationship' => 'nullable|string|max:255',
                'emergency_contact_address' => 'nullable|string',
            ];
        } elseif ($stage === 'family') {
            $rules = [
                'family' => 'nullable|array',
                'family.*.name' => 'required|string|max:255',
                'family.*.relationship' => 'required|string|max:255',
                'family.*.date_of_birth' => 'nullable|date',
                'family.*.gender' => 'nullable|string|in:Male,Female',
                'family.*.occupation' => 'nullable|string|max:255',
                'family.*.phone' => 'nullable|string|max:20',
                'family.*.is_dependent' => 'boolean',
            ];
        } elseif ($stage === 'next-of-kin') {
            $rules = [
                'next_of_kin' => 'nullable|array',
                'next_of_kin.*.name' => 'required|string|max:255',
                'next_of_kin.*.relationship' => 'required|string|max:255',
                'next_of_kin.*.phone' => 'required|string|max:20',
                'next_of_kin.*.email' => 'nullable|email|max:255',
                'next_of_kin.*.address' => 'required|string',
                'next_of_kin.*.id_number' => 'nullable|string|max:50',
            ];
        } elseif ($stage === 'referees') {
            $rules = [
                'referees' => 'nullable|array',
                'referees.*.name' => 'required|string|max:255',
                'referees.*.position' => 'nullable|string|max:255',
                'referees.*.organization' => 'nullable|string|max:255',
                'referees.*.phone' => 'required|string|max:20',
                'referees.*.email' => 'nullable|email|max:255',
                'referees.*.relationship' => 'nullable|string',
            ];
        } elseif ($stage === 'education') {
            $rules = [
                'educations' => 'nullable|array',
                'educations.*.institution_name' => 'required|string|max:255',
                'educations.*.qualification' => 'required|string|max:255',
                'educations.*.field_of_study' => 'nullable|string|max:255',
                'educations.*.start_year' => 'nullable|integer|min:1900|max:' . date('Y'),
                'educations.*.end_year' => 'nullable|integer|min:1900|max:' . date('Y'),
                'educations.*.grade' => 'nullable|string|max:50',
            ];
        } elseif ($stage === 'deductions') {
            // Deductions are completely optional - employee can have or not have deductions
            $rules = [
                'deductions' => 'nullable|array',
                'deductions.*.deduction_type' => 'nullable|string|max:255',
                'deductions.*.description' => 'nullable|string',
                'deductions.*.amount' => 'nullable|numeric|min:0',
                'deductions.*.frequency' => 'nullable|string|in:monthly,one-time',
                'deductions.*.start_date' => 'nullable|date',
                'deductions.*.end_date' => 'nullable|date|after_or_equal:deductions.*.start_date',
                'deductions.*.is_active' => 'nullable|boolean',
                'deductions.*.notes' => 'nullable|string',
            ];
        } elseif ($stage === 'profile') {
            $rules = [
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'marital_status' => 'nullable|string|max:50',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|string|in:Male,Female,Other',
                'nationality' => 'nullable|string|max:100',
                'address' => 'nullable|string',
            ];
        } elseif ($stage === 'documents') {
            // Documents are completely optional - employee can have or not have documents
            $rules = [
                'documents' => 'nullable|array',
                'documents.*.document_type' => 'nullable|string|max:255',
                'documents.*.document_name' => 'nullable|string|max:255',
                'documents.*.file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
                'documents.*.document_number' => 'nullable|string|max:255',
                'documents.*.issue_date' => 'nullable|date',
                'documents.*.expiry_date' => 'nullable|date|after_or_equal:documents.*.issue_date',
                'documents.*.issued_by' => 'nullable|string|max:255',
                'documents.*.description' => 'nullable|string',
            ];
        } elseif ($stage === 'statutory') {
            $rules = [
                'tin_number' => 'nullable|string|max:50',
                'nssf_number' => 'nullable|string|max:50',
                'nhif_number' => 'nullable|string|max:50',
                'heslb_number' => 'nullable|string|max:50',
                'has_student_loan' => 'boolean',
            ];
        }
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            // Format errors for better display
            $formattedErrors = [];
            foreach ($validator->errors()->toArray() as $key => $messages) {
                // Clean up field names for display
                $displayKey = str_replace('_', ' ', $key);
                $displayKey = ucwords($displayKey);
                $formattedErrors[$displayKey] = $messages;
            }
            
            return response()->json([
                'success' => false, 
                'message' => 'Please correct the validation errors below.',
                'errors' => $formattedErrors
            ], 422);
        }
        
        try {
            DB::beginTransaction(); // Start transaction for all operations
            
            $employeeUser = null;
            $employeeRecord = null;
            
            // Check if user already exists (for draft continuation - multi-step registration)
            if ($request->has('user_id') && $request->user_id) {
                $employeeUser = User::findOrFail($request->user_id);
                
                // Update user details with validation
                $userData = $request->only(['name', 'email', 'phone', 'employee_id', 'primary_department_id', 'hire_date']);
                
                // Validate email uniqueness if changed
                if (isset($userData['email']) && $userData['email'] !== $employeeUser->email) {
                    $emailExists = User::where('email', $userData['email'])->where('id', '!=', $employeeUser->id)->exists();
                    if ($emailExists) {
                        throw new \Exception('Email address is already in use by another user.');
                    }
                }
                
                // Validate employee_id uniqueness if changed
                if (isset($userData['employee_id']) && $userData['employee_id'] && $userData['employee_id'] !== $employeeUser->employee_id) {
                    $empIdExists = User::where('employee_id', $userData['employee_id'])->where('id', '!=', $employeeUser->id)->exists();
                    if ($empIdExists) {
                        throw new \Exception('Employee ID is already in use by another user.');
                    }
                }
                
                if ($request->filled('password')) {
                    $userData['password'] = Hash::make($request->password);
                }
                
                $employeeUser->update($userData);
                
                // Log the update
                Log::info('Employee user updated during registration', [
                    'user_id' => $employeeUser->id,
                    'stage' => $stage,
                    'updated_fields' => array_keys($userData)
                ]);
                
                // Ensure employee record exists (should always exist at this point)
                $employeeRecord = $employeeUser->employee;
                if (!$employeeRecord) {
                    $employeeRecord = Employee::create([
                        'user_id' => $employeeUser->id,
                        'position' => 'Staff Member',
                        'employment_type' => 'permanent',
                        'hire_date' => $employeeUser->hire_date ?? now(),
                        'salary' => 0,
                    ]);
                    Log::warning('Employee record was missing during update, created new one', [
                        'user_id' => $employeeUser->id
                    ]);
                }
            } else {
                // Auto-generate Employee ID if not provided
                $employeeId = $request->employee_id;
                if (empty($employeeId)) {
                    $employeeId = $this->generateEmployeeId($request->hire_date, $request->primary_department_id);
                    Log::info('Auto-generated Employee ID', [
                        'employee_id' => $employeeId,
                        'hire_date' => $request->hire_date,
                        'department_id' => $request->primary_department_id
                    ]);
                } else {
                    // Validate provided employee_id is unique
                    $exists = User::where('employee_id', $employeeId)->exists();
                    if ($exists) {
                        throw new \Exception('Employee ID already exists. Please use a different ID or leave it blank to auto-generate.');
                    }
                }
                
                // Always use default password 'welcome123' for new employees
                $password = 'welcome123';
                Log::info('Using default password for new employee', [
                    'email' => $request->email,
                    'employee_id' => $employeeId
                ]);
                
                // Create new user with comprehensive data
                $employeeUser = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($password), // Ensure password is hashed
                    'phone' => $request->phone,
                    'employee_id' => $employeeId, // Use auto-generated or provided ID
                    'primary_department_id' => $request->primary_department_id,
                    'hire_date' => $request->hire_date ?? now(),
                    'is_active' => true, // Ensure new employees are active
                ]);
                
                // Log user creation for audit trail
                Log::info('New employee user created', [
                    'user_id' => $employeeUser->id,
                    'email' => $employeeUser->email,
                    'employee_id' => $employeeUser->employee_id,
                    'department_id' => $employeeUser->primary_department_id,
                    'has_password' => !empty($employeeUser->password),
                    'is_active' => $employeeUser->is_active,
                    'created_by' => $user->id,
                    'stage' => $stage
                ]);
                
                // CRITICAL: Create employee record immediately (one-to-one relationship - mandatory)
                $employeeRecord = Employee::create([
                    'user_id' => $employeeUser->id,
                    'position' => 'Staff Member', // Default, will be updated in employment stage
                    'employment_type' => 'permanent',
                    'hire_date' => $request->hire_date ?? now(),
                    'salary' => 0,
                ]);
                
                Log::info('Employee record created for new user', [
                    'user_id' => $employeeUser->id,
                    'employee_id' => $employeeRecord->id
                ]);
            }
            
            // Update employee record based on stage - Each stage saves data independently
            if ($stage === 'employment') {
                $employeeData = $request->only(['employment_type', 'salary']);
                
                // Handle position - use custom if provided, otherwise use selected position
                if ($request->filled('position_custom')) {
                    $employeeData['position'] = $request->position_custom;
                } elseif ($request->filled('position') && $request->position !== '__custom__') {
                    $employeeData['position'] = $request->position;
                }
                
                $employeeRecord->update($employeeData);
                
                // Update roles with proper sync - handle both empty and non-empty arrays
                if ($request->has('roles')) {
                    $roles = is_array($request->roles) ? $request->roles : [];
                    if (empty($roles)) {
                        // If empty array, remove all roles
                        $employeeUser->roles()->detach();
                        Log::info('All roles removed during employment stage', [
                            'user_id' => $employeeUser->id
                        ]);
                    } else {
                        // Sync roles with pivot data
                        $employeeUser->roles()->sync(
                            collect($roles)->mapWithKeys(function($roleId) {
                                return [(int)$roleId => [
                                    'is_active' => true,
                                    'assigned_at' => now()
                                ]];
                            })->toArray()
                        );
                        Log::info('Roles synced during employment stage', [
                            'user_id' => $employeeUser->id,
                            'role_ids' => $roles,
                            'roles_count' => count($roles)
                        ]);
                    }
                }
                
                Log::info('Employment stage data saved', [
                    'user_id' => $employeeUser->id,
                    'employee_id' => $employeeRecord->id,
                    'position' => $employeeData['position'] ?? null,
                    'roles_count' => count($request->roles ?? [])
                ]);
            } elseif ($stage === 'banking') {
                // Handle multiple bank accounts with proper upsert logic
                if ($request->has('bank_accounts') && is_array($request->bank_accounts)) {
                    // First, unset all primary flags
                    BankAccount::where('user_id', $employeeUser->id)->update(['is_primary' => false]);
                    
                    foreach ($request->bank_accounts as $account) {
                        $accountData = array_merge($account, ['user_id' => $employeeUser->id]);
                        
                        // Ensure only one primary account
                        if (isset($account['is_primary']) && ($account['is_primary'] == '1' || $account['is_primary'] === true || $account['is_primary'] === 'true')) {
                            $accountData['is_primary'] = true;
                            // Unset others again to be safe
                            BankAccount::where('user_id', $employeeUser->id)
                                ->where('id', '!=', $account['id'] ?? 0)
                                ->update(['is_primary' => false]);
                        } else {
                            $accountData['is_primary'] = false;
                        }
                        
                        if (isset($account['id']) && !empty($account['id'])) {
                            // Update existing
                            BankAccount::where('id', $account['id'])
                                ->where('user_id', $employeeUser->id)
                                ->update($accountData);
                        } else {
                            // Create new
                            BankAccount::create($accountData);
                        }
                    }
                } else {
                    // Legacy single bank account support
                    $employeeData = $request->only(['bank_name', 'bank_account_number']);
                    if (!empty($employeeData['bank_name']) || !empty($employeeData['bank_account_number'])) {
                        // Unset existing primary
                        BankAccount::where('user_id', $employeeUser->id)->update(['is_primary' => false]);
                        
                        BankAccount::create([
                            'user_id' => $employeeUser->id,
                            'bank_name' => $employeeData['bank_name'] ?? '',
                            'account_number' => $employeeData['bank_account_number'] ?? '',
                            'is_primary' => true,
                        ]);
                    }
                }
                
                Log::info('Banking stage data saved', [
                    'user_id' => $employeeUser->id,
                    'accounts_count' => count($request->bank_accounts ?? [])
                ]);
            } elseif ($stage === 'statutory') {
                $employeeData = $request->only(['tin_number', 'nssf_number', 'nhif_number', 'heslb_number', 'has_student_loan']);
                $employeeRecord->update($employeeData);
                
                Log::info('Statutory stage data saved - Registration complete', [
                    'user_id' => $employeeUser->id,
                    'employee_id' => $employeeRecord->id
                ]);
            } elseif ($stage === 'emergency') {
                // Only update columns that exist in the database
                $updateData = [];
                $columns = ['emergency_contact_name', 'emergency_contact_phone', 
                           'emergency_contact_relationship', 'emergency_contact_address'];
                
                foreach ($columns as $column) {
                    if (\Illuminate\Support\Facades\Schema::hasColumn('employees', $column)) {
                        $updateData[$column] = $request->get($column);
                    }
                }
                
                if (!empty($updateData)) {
                    $employeeRecord->update($updateData);
                }
                
                Log::info('Emergency contact stage data saved', [
                    'user_id' => $employeeUser->id
                ]);
            } elseif ($stage === 'family') {
                if ($request->has('family') && is_array($request->family)) {
                    // Handle upsert logic for family members
                    $existingIds = [];
                    foreach ($request->family as $familyMember) {
                        if (isset($familyMember['id']) && !empty($familyMember['id'])) {
                            // Update existing
                            $existingIds[] = $familyMember['id'];
                            EmployeeFamily::where('id', $familyMember['id'])
                                ->where('user_id', $employeeUser->id)
                                ->update(array_merge($familyMember, ['user_id' => $employeeUser->id]));
                        } else {
                            // Create new
                            $newRecord = EmployeeFamily::create(array_merge($familyMember, ['user_id' => $employeeUser->id]));
                            $existingIds[] = $newRecord->id;
                        }
                    }
                    // Delete removed family members
                    EmployeeFamily::where('user_id', $employeeUser->id)
                        ->whereNotIn('id', $existingIds)
                        ->delete();
                }
                
                Log::info('Family stage data saved', [
                    'user_id' => $employeeUser->id,
                    'family_count' => count($request->family ?? [])
                ]);
            } elseif ($stage === 'next-of-kin') {
                if ($request->has('next_of_kin') && is_array($request->next_of_kin)) {
                    // Handle upsert logic for next of kin
                    $existingIds = [];
                    foreach ($request->next_of_kin as $kin) {
                        if (isset($kin['id']) && !empty($kin['id'])) {
                            // Update existing
                            $existingIds[] = $kin['id'];
                            EmployeeNextOfKin::where('id', $kin['id'])
                                ->where('user_id', $employeeUser->id)
                                ->update(array_merge($kin, ['user_id' => $employeeUser->id]));
                        } else {
                            // Create new
                            $newRecord = EmployeeNextOfKin::create(array_merge($kin, ['user_id' => $employeeUser->id]));
                            $existingIds[] = $newRecord->id;
                        }
                    }
                    // Delete removed next of kin
                    EmployeeNextOfKin::where('user_id', $employeeUser->id)
                        ->whereNotIn('id', $existingIds)
                        ->delete();
                }
                
                Log::info('Next of kin stage data saved', [
                    'user_id' => $employeeUser->id,
                    'next_of_kin_count' => count($request->next_of_kin ?? [])
                ]);
            } elseif ($stage === 'referees') {
                if ($request->has('referees') && is_array($request->referees)) {
                    // Handle upsert logic for referees
                    $existingIds = [];
                    foreach ($request->referees as $index => $referee) {
                        if (isset($referee['id']) && !empty($referee['id'])) {
                            // Update existing
                            $existingIds[] = $referee['id'];
                            EmployeeReferee::where('id', $referee['id'])
                                ->where('user_id', $employeeUser->id)
                                ->update(array_merge($referee, [
                            'user_id' => $employeeUser->id,
                            'order' => $index
                        ]));
                        } else {
                            // Create new
                            $newRecord = EmployeeReferee::create(array_merge($referee, [
                                'user_id' => $employeeUser->id,
                                'order' => $index
                            ]));
                            $existingIds[] = $newRecord->id;
                        }
                    }
                    // Delete removed referees
                    EmployeeReferee::where('user_id', $employeeUser->id)
                        ->whereNotIn('id', $existingIds)
                        ->delete();
                }
                
                Log::info('Referees stage data saved', [
                    'user_id' => $employeeUser->id,
                    'referees_count' => count($request->referees ?? [])
                ]);
            } elseif ($stage === 'education') {
                if ($request->has('educations') && is_array($request->educations)) {
                    // Handle upsert logic for education
                    $existingIds = [];
                    foreach ($request->educations as $index => $education) {
                        if (isset($education['id']) && !empty($education['id'])) {
                            // Update existing
                            $existingIds[] = $education['id'];
                            EmployeeEducation::where('id', $education['id'])
                                ->where('user_id', $employeeUser->id)
                                ->update(array_merge($education, [
                            'user_id' => $employeeUser->id,
                            'order' => $index
                        ]));
                        } else {
                            // Create new
                            $newRecord = EmployeeEducation::create(array_merge($education, [
                                'user_id' => $employeeUser->id,
                                'order' => $index
                            ]));
                            $existingIds[] = $newRecord->id;
                        }
                    }
                    // Delete removed education records
                    EmployeeEducation::where('user_id', $employeeUser->id)
                        ->whereNotIn('id', $existingIds)
                        ->delete();
                }
                
                Log::info('Education stage data saved', [
                    'user_id' => $employeeUser->id,
                    'educations_count' => count($request->educations ?? [])
                ]);
            } elseif ($stage === 'deductions') {
                // Handle salary deductions
                if ($request->has('deductions') && is_array($request->deductions)) {
                    $deductionsData = $request->input('deductions');
                    $existingIds = [];
                    $processedCount = 0;
                    
                    foreach ($deductionsData as $index => $deduction) {
                        if (empty($deduction) || !is_array($deduction)) {
                            continue;
                        }
                        
                        $hasDeductionType = isset($deduction['deduction_type']) && !empty(trim($deduction['deduction_type']));
                        $hasAmount = isset($deduction['amount']) && !empty($deduction['amount']) && (float)$deduction['amount'] > 0;
                        $hasStartDate = isset($deduction['start_date']) && !empty(trim($deduction['start_date']));
                        
                        if (!$hasDeductionType || !$hasAmount || !$hasStartDate) {
                            continue;
                        }
                        
                        $deductionData = [
                            'employee_id' => $employeeUser->id,
                            'deduction_type' => trim($deduction['deduction_type']),
                            'description' => !empty($deduction['description']) ? trim($deduction['description']) : null,
                            'amount' => (float)($deduction['amount']),
                            'frequency' => in_array($deduction['frequency'] ?? 'monthly', ['monthly', 'one-time']) 
                                ? ($deduction['frequency'] ?? 'monthly') 
                                : 'monthly',
                            'start_date' => $deduction['start_date'],
                            'end_date' => !empty($deduction['end_date']) ? $deduction['end_date'] : null,
                            'is_active' => isset($deduction['is_active']) 
                                ? filter_var($deduction['is_active'], FILTER_VALIDATE_BOOLEAN) 
                                : true,
                            'notes' => !empty($deduction['notes']) ? trim($deduction['notes']) : null,
                        ];
                        
                        if (isset($deduction['id']) && !empty($deduction['id'])) {
                            $existingIds[] = (int)$deduction['id'];
                            EmployeeSalaryDeduction::where('id', $deduction['id'])
                                ->where('employee_id', $employeeUser->id)
                                ->update($deductionData);
                        } else {
                            $newRecord = EmployeeSalaryDeduction::create($deductionData);
                            $existingIds[] = $newRecord->id;
                        }
                        $processedCount++;
                    }
                    
                    if (!empty($existingIds)) {
                        EmployeeSalaryDeduction::where('employee_id', $employeeUser->id)
                            ->whereNotIn('id', $existingIds)
                            ->delete();
                    }
                }
                
                Log::info('Deductions stage data saved', [
                    'user_id' => $employeeUser->id,
                    'deductions_count' => count($request->deductions ?? [])
                ]);
            } elseif ($stage === 'profile') {
                // Handle profile photo and additional personal info
                $profileData = $request->only(['marital_status', 'date_of_birth', 'gender', 'nationality', 'address']);
                
                // Update user profile fields - include all fields
                $updateData = [];
                if (isset($profileData['marital_status']) && !empty($profileData['marital_status'])) {
                    $updateData['marital_status'] = $profileData['marital_status'];
                } elseif (isset($profileData['marital_status']) && empty($profileData['marital_status'])) {
                    $updateData['marital_status'] = null;
                }
                if (isset($profileData['date_of_birth']) && !empty($profileData['date_of_birth'])) {
                    $updateData['date_of_birth'] = $profileData['date_of_birth'];
                } elseif (isset($profileData['date_of_birth']) && empty($profileData['date_of_birth'])) {
                    $updateData['date_of_birth'] = null;
                }
                if (isset($profileData['gender']) && !empty($profileData['gender'])) {
                    $updateData['gender'] = $profileData['gender'];
                } elseif (isset($profileData['gender']) && empty($profileData['gender'])) {
                    $updateData['gender'] = null;
                }
                if (isset($profileData['nationality']) && !empty($profileData['nationality'])) {
                    $updateData['nationality'] = $profileData['nationality'];
                } elseif (isset($profileData['nationality']) && empty($profileData['nationality'])) {
                    $updateData['nationality'] = null;
                }
                if (isset($profileData['address']) && !empty($profileData['address'])) {
                    $updateData['address'] = $profileData['address'];
                } elseif (isset($profileData['address']) && empty($profileData['address'])) {
                    $updateData['address'] = null;
                }
                
                if (!empty($updateData)) {
                    $employeeUser->update($updateData);
                }
                
                // Handle photo upload
                if ($request->hasFile('photo')) {
                    if (!Storage::exists('public/photos')) {
                        Storage::makeDirectory('public/photos');
                    }
                    
                    if ($employeeUser->photo && Storage::exists('public/photos/' . $employeeUser->photo)) {
                        Storage::delete('public/photos/' . $employeeUser->photo);
                    }
                    
                    $photoName = time() . '_' . $employeeUser->id . '.' . $request->file('photo')->getClientOriginalExtension();
                    $request->file('photo')->storeAs('public/photos', $photoName);
                    $employeeUser->update(['photo' => $photoName]);
                }
                
                Log::info('Profile stage data saved', [
                    'user_id' => $employeeUser->id,
                    'has_photo' => $request->hasFile('photo')
                ]);
            } elseif ($stage === 'documents') {
                // Handle document uploads
                if ($request->has('documents') && is_array($request->documents)) {
                    if (!Storage::exists('public/documents')) {
                        Storage::makeDirectory('public/documents');
                    }
                    
                    foreach ($request->documents as $index => $doc) {
                        // Check if file exists for this document index
                        $fileKey = "documents.{$index}.file";
                        if ($request->hasFile($fileKey)) {
                            $file = $request->file($fileKey);
                            $fileName = time() . '_' . $employeeUser->id . '_' . $file->getClientOriginalName();
                            $filePath = $file->storeAs('public/documents', $fileName);
                            
                            EmployeeDocument::create([
                                'user_id' => $employeeUser->id,
                                'document_type' => $doc['document_type'] ?? 'Other',
                                'document_name' => $doc['document_name'] ?? $file->getClientOriginalName(),
                                'file_path' => $filePath,
                                'file_name' => $file->getClientOriginalName(),
                                'file_type' => $file->getClientMimeType(),
                                'file_size' => $file->getSize(),
                                'document_number' => $doc['document_number'] ?? null,
                                'issue_date' => !empty($doc['issue_date']) ? $doc['issue_date'] : null,
                                'expiry_date' => !empty($doc['expiry_date']) ? $doc['expiry_date'] : null,
                                'issued_by' => $doc['issued_by'] ?? null,
                                'description' => $doc['description'] ?? null,
                                'is_active' => true,
                                'uploaded_by' => Auth::id(),
                            ]);
                        }
                    }
                }
                
                Log::info('Documents stage data saved', [
                    'user_id' => $employeeUser->id,
                    'documents_count' => count($request->documents ?? [])
                ]);
            }
            
            // Commit all changes
            DB::commit();
            
            // Clear any cached employee data
            cache()->forget("employee_basic_{$employeeUser->id}");
            cache()->forget("employee_full_{$employeeUser->id}");
            
            // Reload employee with relationships
            $employeeUser->refresh();
            $employeeUser->loadMissing([
                'employee', 'primaryDepartment', 'roles',
                'family', 'nextOfKin', 'referees', 'educations', 'bankAccounts'
            ]);
            
            // Calculate completion percentage
            $completionPercentage = $this->calculateEmployeeCompletion($employeeUser);
            
            $message = $isDraft ? "Employee information saved successfully at {$stage} stage. Progress saved!" : "Employee created successfully at {$stage} stage.";
            
            // Check if registration is complete (last stage before review)
            $isComplete = $stage === 'documents';
            $isNewEmployee = !$request->has('user_id') || empty($request->user_id);
            
            // Note: SMS will be sent after final review and registration completion
            
            // Log successful completion of stage
            Log::info('Employee registration stage completed', [
                'user_id' => $employeeUser->id,
                'stage' => $stage,
                'is_draft' => $isDraft,
                'is_complete' => $isComplete,
                'is_new_employee' => $isNewEmployee,
                'next_stage' => $this->getNextStage($stage),
                'completion_percentage' => $completionPercentage
            ]);
            
            // Log activity
            if ($isNewEmployee) {
                ActivityLogService::logCreated($employeeUser, "Created employee: {$employeeUser->name} (ID: " . ($employeeUser->employee_id ?? $employeeUser->id) . ")", [
                    'employee_id' => $employeeUser->employee_id ?? $employeeUser->id,
                    'name' => $employeeUser->name,
                    'email' => $employeeUser->email,
                    'stage' => $stage,
                    'is_complete' => $isComplete,
                ]);
            } else {
                $oldValues = array_intersect_key($employeeUser->getOriginal(), $employeeUser->getChanges());
                ActivityLogService::logUpdated($employeeUser, $oldValues, $employeeUser->getChanges(), "Updated employee: {$employeeUser->name} (ID: " . ($employeeUser->employee_id ?? $employeeUser->id) . ") - Stage: {$stage}", [
                    'employee_id' => $employeeUser->employee_id ?? $employeeUser->id,
                    'stage' => $stage,
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'user_id' => $employeeUser->id,
                'employee' => $employeeUser,
                'next_stage' => $this->getNextStage($stage),
                'is_complete' => $isComplete,
                'progress' => $this->getRegistrationProgress($stage),
                'completion_percentage' => $completionPercentage,
                'can_review' => $isComplete
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Re-throw validation exceptions to return proper error format
            DB::rollBack();
            Log::warning('Employee registration validation failed', [
                'stage' => $stage,
                'errors' => $e->errors(),
                'created_by' => $user->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your inputs.',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            Log::error('Database error creating/updating employee', [
                'error' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? null,
                'driver_code' => $e->errorInfo[1] ?? null,
                'stage' => $stage
            ]);
            
            // Handle specific database errors
            $errorMessage = 'A database error occurred. ';
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $errorMessage .= 'The email or employee ID already exists.';
            } elseif (str_contains($e->getMessage(), 'foreign key constraint')) {
                $errorMessage .= 'Invalid department or role selected.';
            } else {
                $errorMessage .= 'Please try again or contact administrator.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error_details' => config('app.debug') ? $e->getMessage() : null
            ], 500);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating/updating employee', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'stage' => $stage,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'created_by' => $user->id,
                'request_data' => $request->except(['password', 'password_confirmation'])
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred: ' . ($e->getMessage() ?? 'Unknown error'),
                'error_details' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }
    
    /**
     * Get registration progress percentage
     */
    private function getRegistrationProgress($currentStage)
    {
        $stages = [
            'personal' => 9.09,
            'employment' => 18.18,
            'emergency' => 27.27,
            'family' => 36.36,
            'next-of-kin' => 45.45,
            'referees' => 54.54,
            'education' => 63.63,
            'banking' => 72.72,
            'deductions' => 81.81,
            'profile' => 90.90,
            'documents' => 100,
            'review' => 100
        ];
        
        return $stages[$currentStage] ?? 0;
    }
    
    /**
     * Show independent employee registration page
     */
    public function create()
    {
        $user = Auth::user();
        
        // Only HR and System Admin can register employees
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized to register employees.');
        }
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $roles = \App\Models\Role::where('is_active', true)->orderBy('name')->get();
        $positions = \App\Models\Position::where('is_active', true)->orderBy('title')->get();
        
        return view('modules.hr.employee-register', compact('departments', 'roles', 'positions'));
    }

    /**
     * Show edit page for employee (full page with all sections)
     */
    public function edit($userId)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized to edit employees.');
        }
        
        $employee = User::with([
            'employee', 'primaryDepartment', 'roles',
            'family', 'nextOfKin', 'referees', 'educations', 
            'bankAccounts', 'salaryDeductions', 'documents'
        ])->findOrFail($userId);
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $roles = \App\Models\Role::where('is_active', true)->orderBy('name')->get();
        $positions = \App\Models\Position::where('is_active', true)->orderBy('title')->get();
        
        return view('modules.hr.employee-edit', compact('employee', 'departments', 'roles', 'positions'));
    }

    /**
     * Show review page for employee registration
     */
    public function review($userId)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized to review employee registration.');
        }
        
        $employee = User::with([
            'employee', 'primaryDepartment', 'roles',
            'family', 'nextOfKin', 'referees', 'educations', 
            'bankAccounts', 'salaryDeductions', 'documents'
        ])->findOrFail($userId);
        
        return view('modules.hr.employee-review', compact('employee'));
    }

    /**
     * Finalize employee registration and send SMS
     */
    public function finalize(Request $request, $userId)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        
        try {
            $employeeUser = User::with(['employee', 'primaryDepartment'])->findOrFail($userId);
            
            // Ensure user is active (registration is complete)
            $employeeUser->update(['is_active' => true]);
            
            // Get password from request or generate temporary one
            $password = $request->password ?? null;
            
            // Track SMS sending results
            $smsResults = [
                'welcome' => ['sent' => false, 'phone' => null, 'message' => null],
                'congratulations' => ['sent' => false, 'phone' => null, 'message' => null],
                'hod' => ['sent' => false, 'phone' => null, 'message' => null],
                'ceo' => ['sent' => false, 'phone' => null, 'message' => null],
                'hr' => ['sent' => false, 'phone' => null, 'message' => null]
            ];
            
            // Get default password if not provided (default is 'welcome123')
            if (!$password) {
                $password = 'welcome123';
            }
            
            // Send welcome SMS with login credentials to new employee
            try {
                $phone = $employeeUser->mobile ?? $employeeUser->phone;
                $smsResults['welcome'] = $this->sendWelcomeSMS($employeeUser, $password);
                $smsResults['congratulations'] = $this->sendCongratulationsSMS($employeeUser);
            } catch (\Exception $smsError) {
                Log::warning('Failed to send SMS to employee', [
                    'user_id' => $employeeUser->id,
                    'error' => $smsError->getMessage()
                ]);
            }
            
            // Send notification SMS to HOD, CEO, and HR
            try {
                $smsResults['hod'] = $this->sendNotificationToHOD($employeeUser);
                $smsResults['ceo'] = $this->sendNotificationToCEO($employeeUser);
                $smsResults['hr'] = $this->sendNotificationToHR($employeeUser);
            } catch (\Exception $smsError) {
                Log::warning('Failed to send notification SMS to managers', [
                    'user_id' => $employeeUser->id,
                    'error' => $smsError->getMessage()
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Employee registration finalized successfully! SMS notifications have been sent to the employee, HOD, CEO, and HR.',
                'employee' => $employeeUser,
                'sms_results' => $smsResults
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error finalizing employee registration', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to finalize registration: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF for employee registration
     */
    public function generateRegistrationPDF($userId)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized to generate PDF.');
        }
        
        $employee = User::with([
            'employee', 'primaryDepartment', 'roles',
            'family', 'nextOfKin', 'referees', 'educations', 
            'bankAccounts', 'salaryDeductions', 'documents'
        ])->findOrFail($userId);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.hr.pdf.employee-registration', [
            'employee' => $employee,
            'generated_at' => now()->format('F j, Y \a\t g:i A')
        ]);
        
        $pdf->setPaper('A4', 'portrait');
        $filename = 'Employee_Registration_' . $employee->employee_id . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->stream($filename);
    }
    
    /**
     * Send SMS to employee
     */
    public function sendSMS(Request $request, $userId)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        
        try {
            $employee = User::with(['employee', 'primaryDepartment'])->findOrFail($userId);
            
            // Generate or retrieve password
            $password = $request->password ?? $this->generateOrRetrievePassword($employee);
            
            // Send welcome SMS with login credentials to phone field only
            $smsResult = $this->sendWelcomeSMS($employee, $password);
            
            if ($smsResult && is_array($smsResult) && $smsResult['sent']) {
                return response()->json([
                    'success' => true,
                    'message' => 'SMS sent successfully to ' . ($smsResult['phone'] ?? 'employee'),
                    'phone' => $smsResult['phone'] ?? null,
                    'password_set' => true
                ]);
            } else {
                $errorMsg = $smsResult['error'] ?? 'Failed to send SMS. Please check if employee has a valid phone number.';
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'phone' => $employee->phone ?? null
                ], 422);
            }
            
        } catch (\Exception $e) {
            Log::error('Error sending SMS to employee', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error sending SMS: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate or retrieve password for employee
     * If password is not set, generates a new one and updates the user
     */
    private function generateOrRetrievePassword($employee)
    {
        // Check if employee already has a password set
        // If password is not set (null or empty), generate a new one
        if (empty($employee->password)) {
            // Generate a random password
            $password = $this->generateRandomPassword();
            
            // Update the employee's password
            $employee->password = Hash::make($password);
            $employee->save();
            
            Log::info('Generated new password for employee', [
                'user_id' => $employee->id,
                'email' => $employee->email
            ]);
            
            return $password;
        }
        
        // If password exists, we can't retrieve it (it's hashed)
        // So we generate a new one and update it
        $password = $this->generateRandomPassword();
        $employee->password = Hash::make($password);
        $employee->save();
        
        Log::info('Reset password for employee', [
            'user_id' => $employee->id,
            'email' => $employee->email
        ]);
        
        return $password;
    }
    
    /**
     * Generate a random password
     */
    private function generateRandomPassword($length = 8)
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $password;
    }

    /**
     * Get next stage after current stage
     */
    private function getNextStage($currentStage)
    {
        $stages = [
            'personal' => 'employment', 
            'employment' => 'emergency',
            'emergency' => 'family',
            'family' => 'next-of-kin',
            'next-of-kin' => 'referees',
            'referees' => 'education',
            'education' => 'banking',
            'banking' => 'deductions',
            'deductions' => 'profile',
            'profile' => 'documents',
            'documents' => 'statutory',
            'statutory' => 'review'
        ];
        return $stages[$currentStage] ?? null;
    }
    
    /**
     * Sync all users to have employee records
     */
    public function syncAllEmployees()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        
        try {
            $count = $this->syncEmployeeRecords();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$count} employee records.",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function uploadPhoto(Request $request, $id)
    {
        $user = Auth::user();
        
        // Allow all staff to upload their own photo, and HR/Admin to upload any photo
        if (!$user->hasAnyRole(['HR Officer', 'System Admin']) && $user->id != $id) {
            return response()->json(['success' => false, 'message' => 'You can only upload your own profile picture.'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120' // Increased to 5MB
        ]);
        
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'success' => false, 
                'message' => implode(' ', $errors),
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $employeeUser = User::findOrFail($id);
            
            // Ensure storage directory exists
            if (!Storage::exists('public/photos')) {
                Storage::makeDirectory('public/photos');
            }
            
            // Delete old photo if exists
            if ($employeeUser->photo && Storage::exists('public/photos/' . $employeeUser->photo)) {
                Storage::delete('public/photos/' . $employeeUser->photo);
            }
            
            // Store new photo with unique name
            $photoName = time() . '_' . $employeeUser->id . '.' . $request->file('photo')->getClientOriginalExtension();
            $photoPath = $request->file('photo')->storeAs('public/photos', $photoName);
            
            // Update user photo in database
            $employeeUser->update(['photo' => $photoName]);
            
            // Also update employee record if it exists
            if ($employeeUser->employee) {
                // Employee record exists, but photo is stored on users table
                // This is fine as we're already updating the user record
            }
            
            // Log the activity
            $employeeIdentifier = $employeeUser->employee_id ?? $employeeUser->id;
            ActivityLogService::logAction('employee_profile_picture_uploaded', "Uploaded profile picture for employee: {$employeeUser->name} (ID: {$employeeIdentifier})", $employeeUser, [
                'employee_id' => $employeeIdentifier,
                'employee_name' => $employeeUser->name,
            ]);
            
            DB::commit();
            
            Log::info('Profile picture uploaded successfully', [
                'user_id' => $employeeUser->id,
                'photo_name' => $photoName,
                'uploaded_by' => $user->id
            ]);
            
            // Reload user to get fresh data
            $employeeUser->refresh();
            
            // Build photo URL - use asset() helper for proper URL generation
            $photoUrl = asset('storage/photos/' . $photoName);
            
            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded and saved successfully.',
                'photo_url' => $photoUrl,
                'photo' => $photoName,
                'employee' => [
                    'id' => $employeeUser->id,
                    'name' => $employeeUser->name,
                    'photo' => $photoName,
                    'photo_url' => $photoUrl
                ]
            ], 200)->header('Content-Type', 'application/json');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            Log::error('Employee not found for photo upload', [
                'employee_id' => $id,
                'user_id' => $user->id
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Employee not found. Please refresh and try again.'
            ], 404);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading profile picture', [
                'employee_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading photo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload documents for an employee
     */
    public function uploadDocuments(Request $request, $id)
    {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to upload documents.'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'files.*' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx|max:10240', // 10MB max
            'document_type' => 'required|string|max:255',
            'document_name' => 'required|string|max:255',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'issued_by' => 'nullable|string|max:255',
            'document_number' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $employeeUser = User::findOrFail($id);
            
            // Ensure storage directory exists
            if (!Storage::exists('public/documents')) {
                Storage::makeDirectory('public/documents');
            }
            
            $uploadedDocuments = [];
            $files = $request->file('files');
            
            if (!$files || !is_array($files)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files provided.'
                ], 422);
            }
            
            foreach ($files as $file) {
                // Generate unique filename
                $fileName = time() . '_' . $employeeUser->id . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('public/documents', $fileName);
                
                // Create document record
                $document = EmployeeDocument::create([
                    'user_id' => $employeeUser->id,
                    'document_type' => $request->document_type,
                    'document_name' => $request->document_name,
                    'file_path' => $filePath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'issue_date' => $request->issue_date,
                    'expiry_date' => $request->expiry_date,
                    'issued_by' => $request->issued_by,
                    'document_number' => $request->document_number,
                    'description' => $request->description,
                    'uploaded_by' => $user->id,
                    'is_active' => true
                ]);
                
                $uploadedDocuments[] = [
                    'id' => $document->id,
                    'document_name' => $document->document_name,
                    'file_url' => asset('storage/documents/' . $fileName)
                ];
            }
            
            // Log the activity
            ActivityLogService::logAction('employee_documents_uploaded', "Uploaded " . count($uploadedDocuments) . " document(s) for employee: {$employeeUser->name} (ID: " . ($employeeUser->employee_id ?? $employeeUser->id) . ")", $employeeUser, [
                'employee_id' => $employeeUser->employee_id ?? $employeeUser->id,
                'employee_name' => $employeeUser->name,
                'documents_count' => count($uploadedDocuments),
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => count($uploadedDocuments) . ' document(s) uploaded successfully.',
                'count' => count($uploadedDocuments),
                'documents' => $uploadedDocuments
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error uploading documents', [
                'employee_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading documents: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get document details
     */
    public function getDocument($employeeId, $documentId)
    {
        try {
            $document = EmployeeDocument::where('id', $documentId)
                ->where('user_id', $employeeId)
                ->with(['uploader:id,name'])
                ->firstOrFail();
            
            // Add file URL
            $fileName = basename($document->file_path);
            $document->file_url = asset('storage/documents/' . $fileName);
            
            return response()->json([
                'success' => true,
                'document' => $document
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching document', [
                'document_id' => $documentId,
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Document not found.'
            ], 404);
        }
    }
    
    /**
     * Download a document
     */
    public function downloadDocument($employeeId, $documentId)
    {
        try {
            $document = EmployeeDocument::where('id', $documentId)
                ->where('user_id', $employeeId)
                ->where('is_active', true)
                ->firstOrFail();
            
            $filePath = storage_path('app/' . $document->file_path);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found.'
                ], 404);
            }
            
            return response()->download($filePath, $document->file_name);
            
        } catch (\Exception $e) {
            Log::error('Error downloading document', [
                'document_id' => $documentId,
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while downloading the document.'
            ], 500);
        }
    }
    
    /**
     * Delete a document
     */
    public function deleteDocument($employeeId, $documentId)
    {
        $user = Auth::user();
        
        // Check authorization
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to delete documents.'], 403);
        }
        
        try {
            DB::beginTransaction();
            
            $document = EmployeeDocument::where('id', $documentId)
                ->where('user_id', $employeeId)
                ->firstOrFail();
            
            $employeeUser = User::findOrFail($employeeId);
            
            // Delete file from storage
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
            
            // Delete document record (soft delete by setting is_active to false)
            $document->update(['is_active' => false]);
            
            // Log the activity
            $documentData = $document->toArray();
            ActivityLogService::logDeleted($document, "Deleted document '{$document->document_name}' for employee: {$employeeUser->name} (ID: " . ($employeeUser->employee_id ?? $employeeUser->id) . ")", [
                'document_name' => $document->document_name,
                'employee_id' => $employeeUser->employee_id ?? $employeeUser->id,
                'employee_name' => $employeeUser->name,
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully.'
            ], 200);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Document not found.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting document', [
                'document_id' => $documentId,
                'employee_id' => $employeeId,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the document: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Handle bulk actions for employees (activate/deactivate)
     */
    public function bulkAction(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        
        $request->validate([
            'action' => 'required|in:activate,deactivate',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'required|exists:users,id'
        ]);
        
        try {
            DB::beginTransaction();
            
            $employeeIds = $request->employee_ids;
            $action = $request->action;
            $isActive = $action === 'activate';
            
            $employees = User::whereIn('id', $employeeIds)->get();
            $updatedCount = 0;
            $skippedCount = 0;
            
            foreach ($employees as $employee) {
                // Prevent deactivating system admin
                if (!$isActive && $employee->hasRole('System Admin')) {
                    $skippedCount++;
                    continue;
                }
                
                $employee->update(['is_active' => $isActive]);
                $updatedCount++;
                
                // Log the activity
                ActivityLogService::logAction('employee_bulk_' . $action, "Bulk {$action}d employee: {$employee->name} (ID: " . ($employee->employee_id ?? $employee->id) . ")", $employee, [
                    'action' => $action,
                    'employee_id' => $employee->employee_id ?? $employee->id,
                    'employee_name' => $employee->name,
                ]);
            }
            
            DB::commit();
            
            $message = "Successfully {$action}d {$updatedCount} employee(s)";
            if ($skippedCount > 0) {
                $message .= ". {$skippedCount} employee(s) skipped (System Admin cannot be deactivated).";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'skipped_count' => $skippedCount
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk action', [
                'action' => $request->action,
                'employee_ids' => $request->employee_ids,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while performing bulk action: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send SMS to multiple employees
     */
    public function bulkSMS(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'required|exists:users,id',
            'message' => 'nullable|string|max:500',
            'send_credentials' => 'nullable|boolean'
        ]);
        
        try {
            $employeeIds = $request->employee_ids;
            $customMessage = $request->message;
            $sendCredentials = $request->boolean('send_credentials', false);
            
            $employees = User::whereIn('id', $employeeIds)->get();
            $sentCount = 0;
            $failedCount = 0;
            $results = [];
            
            $notificationService = new NotificationService();
            
            foreach ($employees as $employee) {
                // Use phone field only (not mobile)
                $phone = $employee->phone;
                
                if (!$phone) {
                    $failedCount++;
                    $results[] = [
                        'employee' => $employee->name,
                        'status' => 'failed',
                        'reason' => 'No phone number'
                    ];
                    continue;
                }
                
                try {
                    $message = $customMessage;
                    
                    // If sending credentials, generate password and send welcome SMS
                    if ($sendCredentials) {
                        $password = $this->generateOrRetrievePassword($employee);
                        $smsResult = $this->sendWelcomeSMS($employee, $password);
                        
                        if ($smsResult && is_array($smsResult) && $smsResult['sent']) {
                            $sentCount++;
                            $results[] = [
                                'employee' => $employee->name,
                                'status' => 'success',
                                'phone' => $phone,
                                'password_set' => true
                            ];
                        } else {
                            $failedCount++;
                            $results[] = [
                                'employee' => $employee->name,
                                'status' => 'failed',
                                'reason' => $smsResult['error'] ?? 'SMS service error'
                            ];
                        }
                    } else {
                        // Send custom message
                        if (empty($message)) {
                            $failedCount++;
                            $results[] = [
                                'employee' => $employee->name,
                                'status' => 'failed',
                                'reason' => 'No message provided'
                            ];
                            continue;
                        }
                        
                        $smsResult = $notificationService->sendSMS($phone, $message);
                        
                        if ($smsResult && is_array($smsResult) && ($smsResult['sent'] ?? false)) {
                            $sentCount++;
                            $results[] = [
                                'employee' => $employee->name,
                                'status' => 'success',
                                'phone' => $phone
                            ];
                        } else {
                            $failedCount++;
                            $results[] = [
                                'employee' => $employee->name,
                                'status' => 'failed',
                                'reason' => 'SMS service error'
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $results[] = [
                        'employee' => $employee->name,
                        'status' => 'failed',
                        'reason' => $e->getMessage()
                    ];
                }
            }
            
            // Log the activity
            ActivityLogService::logAction('employee_bulk_sms', "Sent SMS to {$sentCount} employee(s). Failed: {$failedCount}", null, [
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_count' => $sentCount + $failedCount,
                'send_credentials' => $sendCredentials
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "SMS sent to {$sentCount} employee(s). {$failedCount} failed.",
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in bulk SMS', [
                'employee_ids' => $request->employee_ids,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending SMS: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate passwords and send SMS to all employees
     */
    public function bulkGeneratePasswordsAndSendSMS(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        
        try {
            // Get all active employees with phone numbers
            $employees = User::whereNotNull('phone')
                ->where('phone', '!=', '')
                ->get();
            
            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No employees with phone numbers found.'
                ], 404);
            }
            
            $sentCount = 0;
            $failedCount = 0;
            $results = [];
            
            foreach ($employees as $employee) {
                $phone = $employee->phone;
                
                if (empty($phone)) {
                    $failedCount++;
                    $results[] = [
                        'employee' => $employee->name,
                        'email' => $employee->email,
                        'status' => 'failed',
                        'reason' => 'No phone number'
                    ];
                    continue;
                }
                
                try {
                    // Generate or retrieve password
                    $password = $this->generateOrRetrievePassword($employee);
                    
                    // Send welcome SMS with credentials
                    $smsResult = $this->sendWelcomeSMS($employee, $password);
                    
                    if ($smsResult && is_array($smsResult) && $smsResult['sent']) {
                        $sentCount++;
                        $results[] = [
                            'employee' => $employee->name,
                            'email' => $employee->email,
                            'status' => 'success',
                            'phone' => $phone,
                            'password_set' => true
                        ];
                    } else {
                        $failedCount++;
                        $results[] = [
                            'employee' => $employee->name,
                            'email' => $employee->email,
                            'status' => 'failed',
                            'reason' => $smsResult['error'] ?? 'SMS service error'
                        ];
                    }
                } catch (\Exception $e) {
                    $failedCount++;
                    $results[] = [
                        'employee' => $employee->name,
                        'email' => $employee->email,
                        'status' => 'failed',
                        'reason' => $e->getMessage()
                    ];
                }
            }
            
            // Log the activity
            ActivityLogService::logAction('employee_bulk_password_sms', "Generated passwords and sent SMS to {$sentCount} employee(s). Failed: {$failedCount}", null, [
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_count' => $sentCount + $failedCount,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Passwords generated and SMS sent to {$sentCount} employee(s). {$failedCount} failed.",
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'total_count' => $employees->count(),
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in bulk password generation and SMS', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate employee reports
     */
    public function generateReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }
        
        $reportType = $request->get('type', 'summary');
        $format = $request->get('format', 'pdf');
        
        try {
            $query = User::with(['employee', 'primaryDepartment', 'roles', 'family', 'nextOfKin', 'referees', 'educations', 'bankAccounts', 'salaryDeductions'])
                ->whereHas('employee');
            
            // Apply filters if provided
            if ($request->filled('department')) {
                $query->where('primary_department_id', $request->department);
            }
            
            if ($request->filled('status')) {
                $query->where('is_active', $request->status === 'active');
            }
            
            $employees = $query->orderBy('name')->get();
            
            // Calculate completion for each employee
            foreach ($employees as $emp) {
                $emp->completion_percentage = $this->calculateEmployeeCompletion($emp);
            }
            
            // Prepare report data based on type
            $reportData = $this->prepareReportData($employees, $reportType);
            
            if ($format === 'pdf') {
                return $this->generatePDFReport($reportData, $reportType);
            } else {
                return $this->generateExcelReport($reportData, $reportType);
            }
            
        } catch (\Exception $e) {
            Log::error('Error generating report', [
                'report_type' => $reportType,
                'format' => $format,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while generating the report: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Prepare report data based on report type
     */
    private function prepareReportData($employees, $reportType)
    {
        $data = [
            'employees' => $employees,
            'reportType' => $reportType,
            'generatedDate' => now()->format('Y-m-d H:i:s'),
            'generatedBy' => Auth::user()->name,
            'totalCount' => $employees->count()
        ];
        
        switch ($reportType) {
            case 'summary':
                $data['summary'] = [
                    'total' => $employees->count(),
                    'active' => $employees->where('is_active', true)->count(),
                    'inactive' => $employees->where('is_active', false)->count(),
                    'departments' => $employees->groupBy('primaryDepartment.name')->map->count(),
                    'employmentTypes' => $employees->groupBy('employee.employment_type')->map->count(),
                    'averageSalary' => $employees->where('employee.salary', '>', 0)->avg('employee.salary') ?? 0,
                    'totalSalary' => $employees->sum('employee.salary') ?? 0
                ];
                break;
                
            case 'detailed':
                // Full details already loaded in employees
                break;
                
            case 'department':
                $data['byDepartment'] = $employees->groupBy(function($emp) {
                    return $emp->primaryDepartment ? $emp->primaryDepartment->name : 'Unassigned';
                })->map(function($deptEmployees) {
                    return [
                        'count' => $deptEmployees->count(),
                        'active' => $deptEmployees->where('is_active', true)->count(),
                        'employees' => $deptEmployees,
                        'totalSalary' => $deptEmployees->sum(function($emp) {
                            return $emp->employee ? ($emp->employee->salary ?? 0) : 0;
                        }),
                        'averageSalary' => $deptEmployees->filter(function($emp) {
                            return $emp->employee && ($emp->employee->salary ?? 0) > 0;
                        })->avg(function($emp) {
                            return $emp->employee->salary ?? 0;
                        }) ?? 0
                    ];
                });
                break;
                
            case 'salary':
                $data['salaryStats'] = [
                    'total' => $employees->sum('employee.salary') ?? 0,
                    'average' => $employees->where('employee.salary', '>', 0)->avg('employee.salary') ?? 0,
                    'min' => $employees->where('employee.salary', '>', 0)->min('employee.salary') ?? 0,
                    'max' => $employees->max('employee.salary') ?? 0,
                    'byDepartment' => $employees->groupBy(function($emp) {
                        return $emp->primaryDepartment ? $emp->primaryDepartment->name : 'Unassigned';
                    })->map(function($deptEmployees) {
                        $salaries = $deptEmployees->filter(function($emp) {
                            return $emp->employee && ($emp->employee->salary ?? 0) > 0;
                        });
                        return [
                            'total' => $salaries->sum(function($emp) {
                                return $emp->employee->salary ?? 0;
                            }),
                            'average' => $salaries->avg(function($emp) {
                                return $emp->employee->salary ?? 0;
                            }) ?? 0,
                            'count' => $salaries->count()
                        ];
                    }),
                    'byEmploymentType' => $employees->groupBy('employee.employment_type')->map(function($typeEmployees) {
                        return [
                            'total' => $typeEmployees->sum('employee.salary') ?? 0,
                            'average' => $typeEmployees->where('employee.salary', '>', 0)->avg('employee.salary') ?? 0,
                            'count' => $typeEmployees->where('employee.salary', '>', 0)->count()
                        ];
                    })
                ];
                break;
                
            case 'completion':
                $data['completionStats'] = [
                    'average' => $employees->avg('completion_percentage') ?? 0,
                    'complete' => $employees->where('completion_percentage', '>=', 100)->count(),
                    'incomplete' => $employees->where('completion_percentage', '<', 100)->count(),
                    'byRange' => [
                        '0-25%' => $employees->where('completion_percentage', '<', 25)->count(),
                        '25-50%' => $employees->whereBetween('completion_percentage', [25, 50])->count(),
                        '50-75%' => $employees->whereBetween('completion_percentage', [50, 75])->count(),
                        '75-100%' => $employees->whereBetween('completion_percentage', [75, 100])->count(),
                        '100%' => $employees->where('completion_percentage', '>=', 100)->count()
                    ]
                ];
                // Sort by completion percentage
                $data['employees'] = $employees->sortByDesc('completion_percentage')->values();
                break;
        }
        
        return $data;
    }
    
    /**
     * Generate PDF Report
     */
    private function generatePDFReport($data, $reportType)
    {
        $view = 'modules.hr.pdf.reports.' . $reportType;
        
        // Check if view exists, fallback to summary
        if (!view()->exists($view)) {
            $view = 'modules.hr.pdf.reports.summary';
        }
        
        $pdf = Pdf::loadView($view, $data);
        $filename = 'employee_' . $reportType . '_report_' . date('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Generate Excel Report
     */
    private function generateExcelReport($data, $reportType)
    {
        $filename = 'employee_' . $reportType . '_report_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($data, $reportType) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            switch ($reportType) {
                case 'summary':
                    fputcsv($file, ['Employee ID', 'Name', 'Email', 'Department', 'Position', 'Status', 'Salary', 'Completion %']);
                    foreach ($data['employees'] as $emp) {
                        fputcsv($file, [
                            $emp->employee_id ?? 'N/A',
                            $emp->name ?? 'N/A',
                            $emp->email ?? 'N/A',
                            $emp->primaryDepartment->name ?? 'N/A',
                            $emp->employee->position ?? 'N/A',
                            $emp->is_active ? 'Active' : 'Inactive',
                            $emp->employee->salary ?? 0,
                            number_format($emp->completion_percentage ?? 0, 2) . '%'
                        ]);
                    }
                    break;
                    
                case 'detailed':
                    fputcsv($file, ['Employee ID', 'Name', 'Email', 'Phone', 'Department', 'Position', 'Employment Type', 'Salary', 'Hire Date', 'Status', 'Completion %']);
                    foreach ($data['employees'] as $emp) {
                        fputcsv($file, [
                            $emp->employee_id ?? 'N/A',
                            $emp->name ?? 'N/A',
                            $emp->email ?? 'N/A',
                            $emp->phone ?? 'N/A',
                            $emp->primaryDepartment->name ?? 'N/A',
                            $emp->employee->position ?? 'N/A',
                            $emp->employee->employment_type ?? 'N/A',
                            $emp->employee->salary ?? 0,
                            $emp->hire_date ? date('Y-m-d', strtotime($emp->hire_date)) : 'N/A',
                            $emp->is_active ? 'Active' : 'Inactive',
                            number_format($emp->completion_percentage ?? 0, 2) . '%'
                        ]);
                    }
                    break;
                    
                case 'department':
                    fputcsv($file, ['Department', 'Total Employees', 'Active', 'Inactive', 'Total Salary', 'Average Salary']);
                    foreach ($data['byDepartment'] as $deptName => $deptData) {
                        fputcsv($file, [
                            $deptName,
                            $deptData['count'],
                            $deptData['active'],
                            $deptData['count'] - $deptData['active'],
                            number_format($deptData['totalSalary'], 2),
                            number_format($deptData['averageSalary'], 2)
                        ]);
                    }
                    break;
                    
                case 'salary':
                    fputcsv($file, ['Employee ID', 'Name', 'Department', 'Position', 'Employment Type', 'Salary', 'Status']);
                    foreach ($data['employees']->sortByDesc('employee.salary') as $emp) {
                        if ($emp->employee && $emp->employee->salary > 0) {
                            fputcsv($file, [
                                $emp->employee_id ?? 'N/A',
                                $emp->name ?? 'N/A',
                                $emp->primaryDepartment->name ?? 'N/A',
                                $emp->employee->position ?? 'N/A',
                                $emp->employee->employment_type ?? 'N/A',
                                $emp->employee->salary ?? 0,
                                $emp->is_active ? 'Active' : 'Inactive'
                            ]);
                        }
                    }
                    break;
                    
                case 'completion':
                    fputcsv($file, ['Employee ID', 'Name', 'Department', 'Position', 'Completion %', 'Status']);
                    foreach ($data['employees'] as $emp) {
                        fputcsv($file, [
                            $emp->employee_id ?? 'N/A',
                            $emp->name ?? 'N/A',
                            $emp->primaryDepartment->name ?? 'N/A',
                            $emp->employee->position ?? 'N/A',
                            number_format($emp->completion_percentage ?? 0, 2) . '%',
                            ($emp->completion_percentage ?? 0) >= 100 ? 'Complete' : 'Incomplete'
                        ]);
                    }
                    break;
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Export employees to Excel
     */
    private function exportToExcel($employees)
    {
        try {
            $filename = 'employees_export_' . date('Y-m-d_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($employees) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8 Excel compatibility
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Headers
                fputcsv($file, [
                    'Employee ID', 'Name', 'Email', 'Phone', 'Department', 'Position', 
                    'Employment Type', 'Salary', 'Hire Date', 'Status', 'Profile Completion %'
                ]);
                
                // Data rows
                foreach ($employees as $employee) {
                    // Load relationships if not loaded
                    $employee->loadMissing(['family', 'nextOfKin', 'referees', 'educations', 'bankAccounts']);
                    $completion = $this->calculateEmployeeCompletion($employee);
                    fputcsv($file, [
                        $employee->employee_id ?? 'N/A',
                        $employee->name ?? 'N/A',
                        $employee->email ?? 'N/A',
                        $employee->phone ?? 'N/A',
                        $employee->primaryDepartment->name ?? 'N/A',
                        $employee->employee->position ?? 'N/A',
                        $employee->employee->employment_type ?? 'N/A',
                        $employee->employee->salary ?? 0,
                        $employee->hire_date ? date('Y-m-d', strtotime($employee->hire_date)) : 'N/A',
                        $employee->is_active ? 'Active' : 'Inactive',
                        number_format($completion, 2) . '%'
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
            
        } catch (\Exception $e) {
            Log::error('Error exporting to Excel', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to export employees: ' . $e->getMessage());
        }
    }
    
    /**
     * Export employees to PDF
     */
    private function exportToPDF($employees)
    {
        try {
            $data = [
                'employees' => $employees,
                'exportDate' => now()->format('Y-m-d H:i:s'),
                'totalCount' => $employees->count()
            ];
            
            $pdf = Pdf::loadView('modules.hr.pdf.employees-export', $data);
            $filename = 'employees_export_' . date('Y-m-d_His') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            Log::error('Error exporting to PDF', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to export employees: ' . $e->getMessage());
        }
    }
}
