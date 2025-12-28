<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use App\Models\FileFolder;
use App\Models\File as FileModel;
use App\Models\FileAccessRequest;
use App\Models\RackCategory;
use App\Models\RackFolder;
use App\Models\RackFile;
use App\Models\RackFileRequest;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\PettyCashVoucher;
use App\Models\ActivityLog;
use App\Models\PermissionRequest;
use App\Models\SickSheet;
use App\Models\Assessment;
use App\Models\AssessmentProgressReport;
use App\Models\Notification;
use App\Models\ImprestRequest;
use App\Models\ImprestAssignment;
use App\Models\PayrollItem;
use App\Models\Incident;
use App\Models\MainTask;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        
        // Determine which dashboard to show based on user's highest role
        if (in_array('System Admin', $userRoles)) {
            return $this->adminDashboard();
        } elseif (in_array('CEO', $userRoles) || in_array('Director', $userRoles)) {
            return $this->ceoDashboard();
        } elseif (in_array('HOD', $userRoles)) {
            return $this->hodDashboard();
        } elseif (in_array('Accountant', $userRoles)) {
            return $this->accountantDashboard();
        } elseif (in_array('HR Officer', $userRoles)) {
            return $this->hrDashboard();
        } else {
            return $this->staffDashboard();
        }
    }

    public function getUnreadNotifications(Request $request)
    {
        $user = Auth::user();
        $items = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->take(5)
            ->get(['id','message','link','created_at']);

        return response()->json([
            'success' => true,
            'notifications' => $items,
        ]);
    }

    public function adminDashboard()
    {
        $user = Auth::user();
        
        // System-wide statistics
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'total_departments' => Department::where('is_active', true)->count(),
            'total_roles' => Role::count(),
            'total_digital_files' => FileModel::count(),
            'total_digital_folders' => FileFolder::count(),
            'total_physical_files' => RackFile::count(),
            'total_physical_folders' => RackFolder::count(),
            'pending_file_requests' => FileAccessRequest::where('status', 'pending')->count(),
            'pending_physical_requests' => RackFileRequest::where('status', 'pending')->count(),
            'total_storage_used' => $this->getStorageUsed(),
            'total_petty_cash' => (float) PettyCashVoucher::sum('amount'),
            'total_imprest' => (float) ImprestRequest::sum('amount'),
            'total_payroll' => (float) Payroll::sum('total_amount'),
            'pending_leave_requests' => LeaveRequest::pending()->count(),
            'pending_payrolls' => Payroll::where('status', 'pending')->count(),
            'system_notifications' => Notification::where('is_read', false)->count(),
            // Additional comprehensive stats
            'pending_permissions' => PermissionRequest::whereIn('status', ['pending_hr', 'pending_hod', 'pending_hr_final'])->count(),
            'pending_sick_sheets' => SickSheet::whereIn('status', ['pending_hr', 'pending_hod'])->count(),
            'pending_assessments' => Assessment::where('status', 'pending_hod')->count(),
            'total_incidents' => Incident::count(),
            'open_incidents' => Incident::where('status', 'open')->count(),
            'total_tasks' => \App\Models\MainTask::count(),
            'active_tasks' => \App\Models\MainTask::where('status', 'in_progress')->count(),
            'total_imprest_assignments' => ImprestAssignment::count(),
            'pending_imprest_receipts' => ImprestAssignment::where('receipt_submitted', false)->count(),
            'current_month_payroll' => Payroll::whereYear('pay_period', now()->year)
                ->whereMonth('pay_period', now()->month)
                ->sum('total_amount'),
            'current_month_petty_cash' => PettyCashVoucher::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'current_month_imprest' => ImprestRequest::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
        ];

        // Department breakdown
        $departmentStats = Department::withCount([
            'primaryUsers' => function($q) {
                $q->where('users.is_active', true);
            },
            'fileFolders', 
            'rackFolders'
        ])
            ->where('departments.is_active', true)
            ->get();

        // File type distribution - ensure we have valid data
        try {
            $fileTypes = FileModel::select('file_type', DB::raw('count(*) as count'))
                ->whereNotNull('file_type')
                ->where('file_type', '!=', '')
                ->groupBy('file_type')
                ->get()
                ->map(function($item) {
                    return [
                        'file_type' => $item->file_type ?: 'Unknown',
                        'count' => (int)$item->count
                    ];
                });
        } catch (\Exception $e) {
            // If FileModel doesn't exist or has issues, provide empty data
            $fileTypes = collect([]);
        }

        // Recent system activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(20)
            ->get();

        // Monthly trends for charts
        $monthlyTrends = [
            'users' => User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
            'payroll' => Payroll::selectRaw('MONTH(pay_period) as month, SUM(total_amount) as total')
                ->whereYear('pay_period', now()->year)
                ->groupBy('month')
                ->get(),
            'petty_cash' => PettyCashVoucher::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
            'imprest' => ImprestRequest::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
            'files' => FileModel::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
        ];

        // Role distribution
        $roleDistribution = Role::withCount('users')
            ->where('is_active', true)
            ->orderBy('users_count', 'desc')
            ->limit(10)
            ->get();

        // Pending approvals across all modules
        $pendingApprovals = [
            'leave_requests' => LeaveRequest::with('user')->pending()->latest()->limit(5)->get(),
            'permission_requests' => PermissionRequest::with('user')->whereIn('status', ['pending_hr', 'pending_hod', 'pending_hr_final'])->latest()->limit(5)->get(),
            'sick_sheets' => SickSheet::with('employee')->whereIn('status', ['pending_hr', 'pending_hod'])->latest()->limit(5)->get(),
            'petty_cash' => PettyCashVoucher::with('user')->whereIn('status', ['pending_accountant', 'pending_hod', 'pending_ceo'])->latest()->limit(5)->get(),
            'imprest_requests' => ImprestRequest::with('accountant')->whereIn('status', ['pending_hod', 'pending_ceo'])->latest()->limit(5)->get(),
            'assessments' => Assessment::with('employee')->where('status', 'pending_hod')->latest()->limit(5)->get(),
        ];

        // System health indicators
        $systemHealth = [
            'database_status' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
            'storage_usage' => $this->getStorageUsed(),
            'cache_status' => \Illuminate\Support\Facades\Cache::getStore() ? 'active' : 'inactive',
            'queue_status' => 'active', // Can be enhanced with actual queue check
        ];

        return view('dashboards.admin', compact(
            'user', 
            'stats', 
            'departmentStats', 
            'fileTypes', 
            'recentActivities',
            'monthlyTrends',
            'roleDistribution',
            'pendingApprovals',
            'systemHealth'
        ));
    }

    public function ceoDashboard()
    {
        $user = Auth::user();
        
        // Executive-level statistics
        $stats = [
            'total_employees' => User::where('is_active', true)->count(),
            'departments_count' => Department::where('is_active', true)->count(),
            'pending_leave_requests' => LeaveRequest::pending()->count(),
            'approved_leave_requests' => LeaveRequest::approved()->count(),
            'total_payroll_amount' => Payroll::sum('total_amount'),
            'current_month_payroll' => Payroll::whereYear('pay_period', now()->year)
                ->whereMonth('pay_period', now()->month)
                ->sum('total_amount'),
            // CEO sees vouchers pending CEO final approval
            'pending_petty_cash' => PettyCashVoucher::where('status', 'pending_ceo')->sum('amount'),
            'pending_petty_cash_count' => PettyCashVoucher::where('status', 'pending_ceo')->count(),
            'pending_imprest' => ImprestRequest::where('status', 'pending_ceo')->sum('amount'),
            'pending_imprest_count' => ImprestRequest::where('status', 'pending_ceo')->count(),
            'digital_files_count' => FileModel::count(),
            'physical_files_count' => RackFile::count(),
            'file_access_requests' => FileAccessRequest::where('status', 'pending')->count(),
            'physical_file_requests' => RackFileRequest::where('status', 'pending')->count(),
        ];

        // Department performance
        $departmentPerformance = Department::withCount([
            'primaryUsers' => function($q) {
                $q->where('users.is_active', true);
            },
            'leaveRequests', 
            'fileFolders'
        ])
            ->where('departments.is_active', true)
            ->get();

        // Monthly trends
        $monthlyTrends = [
            'leave_requests' => LeaveRequest::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
            'file_uploads' => FileModel::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
            'payroll' => Payroll::selectRaw('MONTH(pay_period) as month, SUM(total_amount) as total')
                ->whereYear('pay_period', now()->year)
                ->groupBy('month')
                ->get(),
            'petty_cash' => PettyCashVoucher::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
            'imprest' => ImprestRequest::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
        ];

        // Recent approvals needed across all modules
        $pendingApprovals = [
            'leave_requests' => LeaveRequest::with('user')->pending()->latest()->limit(5)->get(),
            'file_access_requests' => FileAccessRequest::with('requester', 'file')->where('status', 'pending')->latest()->limit(5)->get(),
            // Show items pending CEO
            'petty_cash' => PettyCashVoucher::with('user')->where('status', 'pending_ceo')->latest()->limit(5)->get(),
            'imprest_requests' => ImprestRequest::with('accountant')->where('status', 'pending_ceo')->latest()->limit(5)->get(),
            'permission_requests' => PermissionRequest::with('user')->whereIn('status', ['pending_hr', 'pending_hod', 'pending_hr_final'])->latest()->limit(5)->get(),
            'sick_sheets' => SickSheet::with('employee')->whereIn('status', ['pending_hr', 'pending_hod'])->latest()->limit(5)->get(),
            'assessments' => Assessment::with('employee')->where('status', 'pending_hod')->latest()->limit(5)->get(),
            'progress_reports' => AssessmentProgressReport::with('activity.assessment.employee')->where('status', 'pending_approval')->latest()->limit(5)->get(),
        ];

        return view('dashboards.ceo', compact('user', 'stats', 'departmentPerformance', 'monthlyTrends', 'pendingApprovals'));
    }

    public function hodDashboard()
    {
        $user = Auth::user();
        $departmentId = $user->primary_department_id;
        
        // Department-specific statistics
        $stats = [
            'department_employees' => User::where('primary_department_id', $departmentId)->where('is_active', true)->count(),
            'pending_leave_requests' => LeaveRequest::whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            })->where('status', 'pending')->count(),
            'department_files' => FileFolder::where('department_id', $departmentId)->count(),
            'department_physical_files' => RackFolder::whereHas('category', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })->count(),
            'pending_file_requests' => FileAccessRequest::whereHas('file', function($q) use ($departmentId) {
                $q->whereHas('folder', function($q2) use ($departmentId) {
                    $q2->where('department_id', $departmentId);
                });
            })->where('status', 'pending')->count(),
            'pending_permissions' => PermissionRequest::whereHas('user', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            })->where('status', 'pending_hod')->count(),
            'pending_sick_sheets' => SickSheet::whereHas('employee', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            })->where('status', 'pending_hod')->count(),
            'pending_assessments' => Assessment::whereHas('employee', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            })->where('status', 'pending_hod')->count(),
        ];

        // Department employees
        $departmentEmployees = User::with(['roles', 'leaveRequests'])
            ->where('primary_department_id', $departmentId)
            ->where('is_active', true)
            ->get();

        // Pending approvals for department across all modules
        $pendingApprovals = [
            'leave_requests' => LeaveRequest::with('user')
                ->whereHas('user', function($q) use ($departmentId) {
                    $q->where('primary_department_id', $departmentId);
                })
                ->where('status', 'pending')
                ->latest()
                ->limit(10)
                ->get(),
            'file_access_requests' => FileAccessRequest::with(['user', 'file'])
                ->whereHas('file', function($q) use ($departmentId) {
                    $q->whereHas('folder', function($q2) use ($departmentId) {
                        $q2->where('department_id', $departmentId);
                    });
                })
                ->where('status', 'pending')
                ->latest()
                ->limit(10)
                ->get(),
            'permission_requests' => PermissionRequest::with('user')
                ->whereHas('user', function($q) use ($departmentId) {
                    $q->where('primary_department_id', $departmentId);
                })
                ->where('status', 'pending_hod')
                ->latest()
                ->limit(10)
                ->get(),
            'sick_sheets' => SickSheet::with('employee')
                ->whereHas('employee', function($q) use ($departmentId) {
                    $q->where('primary_department_id', $departmentId);
                })
                ->where('status', 'pending_hod')
                ->latest()
                ->limit(10)
                ->get(),
            'assessments' => Assessment::with('employee')
                ->whereHas('employee', function($q) use ($departmentId) {
                    $q->where('primary_department_id', $departmentId);
                })
                ->where('status', 'pending_hod')
                ->latest()
                ->limit(10)
                ->get(),
            'progress_reports' => AssessmentProgressReport::with('activity.assessment.employee')
                ->whereHas('activity.assessment.employee', function($q) use ($departmentId) {
                    $q->where('primary_department_id', $departmentId);
                })
                ->where('status', 'pending_approval')
                ->latest()
                ->limit(10)
                ->get(),
        ];

        // Department file activity
        $fileActivity = FileModel::with('folder')
            ->whereHas('folder', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->latest()
            ->limit(10)
            ->get();

        return view('dashboards.hod', compact('user', 'stats', 'departmentEmployees', 'pendingApprovals', 'fileActivity'));
    }

    public function accountantDashboard()
    {
        $user = Auth::user();
        
        // Financial statistics - Petty Cash
        $stats = [
            'total_petty_cash_amount' => (float) PettyCashVoucher::sum('amount'),
            'pending_accountant_amount' => (float) PettyCashVoucher::where('status', 'pending_accountant')->sum('amount'),
            'pending_accountant_count' => PettyCashVoucher::where('status', 'pending_accountant')->count(),
            'pending_hod_amount' => (float) PettyCashVoucher::where('status', 'pending_hod')->sum('amount'),
            'pending_ceo_amount' => (float) PettyCashVoucher::where('status', 'pending_ceo')->sum('amount'),
            'approved_for_payment_amount' => (float) PettyCashVoucher::where('status', 'approved_for_payment')->sum('amount'),
            'paid_amount' => (float) PettyCashVoucher::where('status', 'paid')->sum('amount'),
            'total_vouchers' => PettyCashVoucher::count(),
            'pending_payment_vouchers' => PettyCashVoucher::where('status', 'approved_for_payment')->count(),
            'paid_vouchers' => PettyCashVoucher::where('status', 'paid')->count(),
        ];

        // Imprest Statistics
        $imprestStats = [
            'total_imprest_amount' => (float) ImprestRequest::sum('amount'),
            'pending_hod' => ImprestRequest::where('status', 'pending_hod')->count(),
            'pending_ceo' => ImprestRequest::where('status', 'pending_ceo')->count(),
            'approved' => ImprestRequest::where('status', 'approved')->count(),
            'assigned' => ImprestRequest::where('status', 'assigned')->count(),
            'paid' => ImprestRequest::where('status', 'paid')->count(),
            'pending_verification' => ImprestRequest::where('status', 'pending_receipt_verification')->count(),
            'completed' => ImprestRequest::where('status', 'completed')->count(),
            'pending_hod_amount' => (float) ImprestRequest::where('status', 'pending_hod')->sum('amount'),
            'pending_ceo_amount' => (float) ImprestRequest::where('status', 'pending_ceo')->sum('amount'),
            'paid_amount' => (float) ImprestRequest::where('status', 'paid')->sum('amount'),
            'pending_receipts' => ImprestAssignment::whereHas('imprestRequest', function($q) {
                $q->where('status', 'paid');
            })->where('receipt_submitted', false)->count(),
        ];

        // Payroll Statistics
        $payrollStats = [
            'total_payrolls' => Payroll::count(),
            'current_month_total' => Payroll::whereYear('pay_period', now()->year)
                ->whereMonth('pay_period', now()->month)
                ->sum('total_amount'),
            'pending_payrolls' => Payroll::where('status', 'pending')->count(),
            'approved_payrolls' => Payroll::where('status', 'approved')->count(),
            'paid_payrolls' => Payroll::where('status', 'paid')->count(),
        ];

        // Monthly financial trends
        $monthlyTrends = [
            'petty_cash' => PettyCashVoucher::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
            'imprest' => ImprestRequest::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get(),
        ];

        // Recent financial activities
        $recentActivities = [
            'petty_cash_vouchers' => PettyCashVoucher::with(['creator'])->latest()->limit(5)->get(),
            'imprest_requests' => ImprestRequest::with(['accountant'])->latest()->limit(5)->get(),
            'payrolls' => Payroll::latest()->limit(5)->get(),
        ];

        // Pending actions
        $pendingActions = [
            'petty_cash_pending_accountant' => PettyCashVoucher::where('status', 'pending_accountant')->count(),
            'imprest_pending_assignment' => ImprestRequest::where('status', 'approved')->count(),
            'imprest_pending_payment' => ImprestRequest::where('status', 'assigned')->count(),
            'imprest_pending_verification' => ImprestRequest::where('status', 'pending_receipt_verification')->count(),
            'payroll_pending' => Payroll::where('status', 'pending')->count(),
        ];

        // Financial file categories
        $financialFiles = FileFolder::where('name', 'like', '%financial%')
            ->orWhere('name', 'like', '%accounting%')
            ->orWhere('name', 'like', '%budget%')
            ->orWhere('name', 'like', '%invoice%')
            ->withCount('files')
            ->get();

        return view('dashboards.accountant', compact('user', 'stats', 'imprestStats', 'payrollStats', 'monthlyTrends', 'recentActivities', 'pendingActions', 'financialFiles'));
    }

    public function hrDashboard()
    {
        $user = Auth::user();
        
        // HR-centric statistics
        $stats = [
            'total_employees' => User::where('is_active', true)->count(),
            'total_departments' => Department::where('is_active', true)->count(),
            'pending_permissions' => PermissionRequest::whereIn('status', ['pending_hr','pending_hr_final','return_pending'])->count(),
            'pending_sick_sheets' => SickSheet::whereIn('status', ['pending_hr','return_pending'])->count(),
            'pending_leave_requests' => method_exists(LeaveRequest::class, 'pending') ? LeaveRequest::pending()->count() : 0,
            'pending_payroll' => Payroll::where('status', 'pending')->count(),
            'total_payroll_amount' => Payroll::sum('total_amount'),
            'current_month_payroll' => Payroll::whereYear('pay_period', now()->year)
                ->whereMonth('pay_period', now()->month)
                ->sum('total_amount'),
        ];

        $employeeStats = User::select('primary_department_id', DB::raw('count(*) as count'))
            ->where('is_active', true)
            ->groupBy('primary_department_id')
            ->with('primaryDepartment')
            ->get();

        $leaveTrends = method_exists(LeaveRequest::class, 'selectRaw')
            ? LeaveRequest::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->whereYear('created_at', now()->year)
                ->groupBy('month')
                ->get()
            : collect([]);

        $recentActivities = [
            'permission_requests' => PermissionRequest::latest()->limit(10)->get(),
            'sick_sheets' => SickSheet::latest()->limit(10)->get(),
            'assessments' => Assessment::latest()->limit(10)->get(),
        ];

        $hrFiles = FileFolder::where('name', 'like', '%hr%')
            ->orWhere('name', 'like', '%employee%')
            ->withCount('files')
            ->get();

        return view('dashboards.hr', compact('user', 'stats', 'employeeStats', 'leaveTrends', 'recentActivities', 'hrFiles'));
    }

    public function staffDashboard()
    {
        $user = Auth::user();
        $userId = $user->id;
        
        // Personal statistics
        $stats = [
            'my_files' => FileModel::whereHas('userAssignments', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'my_leave_requests' => LeaveRequest::where('employee_id', $userId)->count(),
            'pending_leave_requests' => LeaveRequest::where('employee_id', $userId)->pending()->count(),
            'approved_leave_requests' => LeaveRequest::where('employee_id', $userId)->approved()->count(),
            'my_file_access_requests' => FileAccessRequest::where('user_id', $userId)->count(),
            'pending_file_access_requests' => FileAccessRequest::where('user_id', $userId)->where('status', 'pending')->count(),
            'my_petty_cash_requests' => PettyCashVoucher::where('user_id', $userId)->count(),
            'pending_petty_cash_requests' => PettyCashVoucher::where('user_id', $userId)->where('status', 'pending')->count(),
            'my_imprest_assignments' => ImprestAssignment::where('staff_id', $userId)->count(),
            'pending_imprest_receipts' => ImprestAssignment::where('staff_id', $userId)
                ->whereHas('imprestRequest', function($q) {
                    $q->where('status', 'paid');
                })
                ->where('receipt_submitted', false)->count(),
            'my_payroll_items' => PayrollItem::where('employee_id', $userId)->count(),
        ];

        // My files
        $myFiles = FileModel::with(['folder', 'userAssignments'])
            ->whereHas('userAssignments', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->latest()
            ->limit(10)
            ->get();

        // My recent activities across all modules
        $myActivities = [
            'leave_requests' => LeaveRequest::where('employee_id', $userId)->latest()->limit(5)->get(),
            'file_access_requests' => FileAccessRequest::where('user_id', $userId)->latest()->limit(5)->get(),
            'petty_cash_requests' => PettyCashVoucher::where('user_id', $userId)->latest()->limit(5)->get(),
            'imprest_assignments' => ImprestAssignment::where('staff_id', $userId)->with('imprestRequest')->latest()->limit(5)->get(),
            'permission_requests' => PermissionRequest::where('user_id', $userId)->latest()->limit(5)->get(),
            'sick_sheets' => SickSheet::where('employee_id', $userId)->latest()->limit(5)->get(),
            'assessments' => Assessment::where('employee_id', $userId)->latest()->limit(5)->get(),
        ];

        // Available files (public and department)
        $availableFiles = FileModel::with('folder')
            ->whereHas('folder', function($q) use ($user) {
                $q->where('access_level', 'public')
                  ->orWhere(function($q2) use ($user) {
                      $q2->where('access_level', 'department')
                         ->where('department_id', $user->primary_department_id);
                  });
            })
            ->whereDoesntHave('userAssignments', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->latest()
            ->limit(10)
            ->get();

        // Quick actions data
        $quickActions = [
            'departments' => Department::where('is_active', true)->get(),
            'file_folders' => FileFolder::where('access_level', 'public')
                ->orWhere(function($q) use ($user) {
                    $q->where('access_level', 'department')
                      ->where('department_id', $user->primary_department_id);
                })
                ->get(),
        ];

        return view('dashboards.staff', compact('user', 'stats', 'myFiles', 'myActivities', 'availableFiles', 'quickActions'));
    }

    private function getStorageUsed()
    {
        // Calculate total storage used by files
        $totalSize = FileModel::sum('file_size');
        return $this->formatBytes($totalSize);
    }

    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
}