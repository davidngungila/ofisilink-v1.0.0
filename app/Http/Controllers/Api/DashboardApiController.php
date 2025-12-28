<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Notification;
use App\Models\MainTask;
use App\Models\File as FileModel;
use App\Models\FileAccessRequest;
use App\Models\RackFileRequest;
use App\Models\PettyCashVoucher;
use App\Models\ImprestRequest;
use App\Models\PermissionRequest;
use App\Models\SickSheet;
use App\Models\Assessment;
use App\Models\Payroll;
use App\Models\Incident;

class DashboardApiController extends Controller
{
    /**
     * Get dashboard data based on user role
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        // Determine dashboard type
        if (in_array('System Admin', $userRoles)) {
            return $this->adminDashboard($user);
        } elseif (in_array('CEO', $userRoles) || in_array('Director', $userRoles)) {
            return $this->ceoDashboard($user);
        } elseif (in_array('HOD', $userRoles)) {
            return $this->hodDashboard($user);
        } elseif (in_array('Accountant', $userRoles)) {
            return $this->accountantDashboard($user);
        } elseif (in_array('HR Officer', $userRoles)) {
            return $this->hrDashboard($user);
        } else {
            return $this->staffDashboard($user);
        }
    }

    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        $stats = [];
        
        if (in_array('System Admin', $userRoles)) {
            $stats = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'total_departments' => Department::where('is_active', true)->count(),
                'pending_leave_requests' => LeaveRequest::pending()->count(),
                'pending_file_requests' => FileAccessRequest::where('status', 'pending')->count(),
                'pending_physical_requests' => RackFileRequest::where('status', 'pending')->count(),
            ];
        } elseif (in_array('HOD', $userRoles)) {
            $departmentId = $user->primary_department_id;
            $stats = [
                'department_employees' => User::where('primary_department_id', $departmentId)->where('is_active', true)->count(),
                'pending_leave_requests' => LeaveRequest::whereHas('user', function($q) use ($departmentId) {
                    $q->where('primary_department_id', $departmentId);
                })->pending()->count(),
                'pending_tasks' => MainTask::whereHas('activities.assignedUsers', function($q) use ($departmentId) {
                    $q->whereHas('user', function($u) use ($departmentId) {
                        $u->where('primary_department_id', $departmentId);
                    });
                })->where('status', '!=', 'completed')->count(),
            ];
        } else {
            $stats = [
                'my_leave_requests' => LeaveRequest::where('employee_id', $user->id)->count(),
                'pending_leave_requests' => LeaveRequest::where('employee_id', $user->id)->pending()->count(),
                'my_tasks' => MainTask::whereHas('activities.assignedUsers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->count(),
                'pending_tasks' => MainTask::whereHas('activities.assignedUsers', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })->where('status', '!=', 'completed')->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get notifications
     */
    public function notifications(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 20);
        
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'message' => $notification->message,
                    'link' => $notification->link,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => Notification::where('user_id', $user->id)->where('is_read', false)->count()
        ]);
    }

    private function adminDashboard($user)
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'total_departments' => Department::where('is_active', true)->count(),
            'pending_leave_requests' => LeaveRequest::pending()->count(),
            'pending_file_requests' => FileAccessRequest::where('status', 'pending')->count(),
            'pending_permissions' => PermissionRequest::whereIn('status', ['pending_hr', 'pending_hod', 'pending_hr_final'])->count(),
            'pending_sick_sheets' => SickSheet::whereIn('status', ['pending_hr', 'pending_hod'])->count(),
            'total_incidents' => Incident::count(),
        ];

        return response()->json([
            'success' => true,
            'type' => 'admin',
            'data' => $stats
        ]);
    }

    private function ceoDashboard($user)
    {
        $stats = [
            'total_employees' => User::where('is_active', true)->count(),
            'pending_leave_requests' => LeaveRequest::pending()->count(),
            'pending_petty_cash' => PettyCashVoucher::where('status', 'pending_ceo')->sum('amount'),
            'pending_imprest' => ImprestRequest::where('status', 'pending_ceo')->sum('amount'),
            'current_month_payroll' => Payroll::whereYear('pay_period', now()->year)
                ->whereMonth('pay_period', now()->month)
                ->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'type' => 'ceo',
            'data' => $stats
        ]);
    }

    private function hodDashboard($user)
    {
        $departmentId = $user->primary_department_id;
        
        $stats = [
            'department_employees' => User::where('primary_department_id', $departmentId)->where('is_active', true)->count(),
            'pending_leave_requests' => LeaveRequest::whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            })->pending()->count(),
            'pending_permissions' => PermissionRequest::whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            })->whereIn('status', ['pending_hod'])->count(),
        ];

        return response()->json([
            'success' => true,
            'type' => 'hod',
            'data' => $stats
        ]);
    }

    private function accountantDashboard($user)
    {
        $stats = [
            'pending_petty_cash' => PettyCashVoucher::where('status', 'pending_accountant')->count(),
            'pending_imprest' => ImprestRequest::where('status', 'pending_hod')->count(),
            'current_month_payroll' => Payroll::whereYear('pay_period', now()->year)
                ->whereMonth('pay_period', now()->month)
                ->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'type' => 'accountant',
            'data' => $stats
        ]);
    }

    private function hrDashboard($user)
    {
        $stats = [
            'pending_leave_requests' => LeaveRequest::pending()->count(),
            'pending_permissions' => PermissionRequest::whereIn('status', ['pending_hr', 'pending_hr_final'])->count(),
            'pending_sick_sheets' => SickSheet::whereIn('status', ['pending_hr'])->count(),
            'pending_assessments' => Assessment::where('status', 'pending_hod')->count(),
        ];

        return response()->json([
            'success' => true,
            'type' => 'hr',
            'data' => $stats
        ]);
    }

    private function staffDashboard($user)
    {
        $stats = [
            'my_leave_requests' => LeaveRequest::where('employee_id', $user->id)->count(),
            'pending_leave_requests' => LeaveRequest::where('employee_id', $user->id)->pending()->count(),
            'my_tasks' => MainTask::whereHas('activities.assignedUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->count(),
            'pending_tasks' => MainTask::whereHas('activities.assignedUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->where('status', '!=', 'completed')->count(),
        ];

        return response()->json([
            'success' => true,
            'type' => 'staff',
            'data' => $stats
        ]);
    }
}







