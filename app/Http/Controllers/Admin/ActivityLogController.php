<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PettyCashVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    /**
     * Get badge class for action type
     */
    protected function getActionBadgeClass($action)
    {
        $action = strtolower($action ?? 'unknown');
        
        $actionClasses = [
            // Basic CRUD
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'danger',
            'viewed' => 'warning',
            
            // Authentication
            'login' => 'primary',
            'logout' => 'secondary',
            'password_reset' => 'warning',
            
            // Approval/Rejection
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'warning',
            
            // Status changes
            'status_changed' => 'info',
            
            // Notifications
            'sms_sent' => 'primary',
            'notification_sent' => 'info',
            'email_sent' => 'success',
            
            // File operations
            'file_uploaded' => 'success',
            'file_downloaded' => 'info',
            'file_deleted' => 'danger',
            
            // Financial
            'payment_processed' => 'success',
            
            // Bulk operations
            'bulk_approved' => 'success',
            'bulk_rejected' => 'danger',
            'bulk_cancelled' => 'warning',
            'bulk_updated' => 'info',
            'bulk_deleted' => 'danger',
            
            // Data operations
            'exported' => 'info',
            'imported' => 'success',
            
            // System
            'config_changed' => 'warning',
            'role_changed' => 'info',
            
            // Comments and assignments
            'comment_added' => 'info',
            'assigned' => 'success',
            'unassigned' => 'warning',
            
            // Specific module actions
            'petty_cash_hod_approved' => 'success',
            'petty_cash_hod_rejected' => 'danger',
            'petty_cash_ceo_approved' => 'success',
            'petty_cash_ceo_rejected' => 'danger',
            'imprest_hod_approved' => 'success',
            'imprest_ceo_approved' => 'success',
            'leave_hr_reviewed' => 'info',
            'leave_hod_reviewed' => 'info',
            'leave_ceo_reviewed' => 'info',
            'assessment_hod_approved' => 'success',
            'payroll_processed' => 'success',
            'payroll_reviewed' => 'info',
            'payroll_approved' => 'success',
            'payroll_paid' => 'success',
        ];
        
        // Check for partial matches (e.g., 'petty_cash_hod_approved' matches 'approved')
        foreach ($actionClasses as $key => $class) {
            if (str_contains($action, $key)) {
                return $class;
            }
        }
        
        return $actionClasses[$action] ?? 'secondary';
    }
    public function index(Request $request)
    {
        $query = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select(
                'activity_logs.id',
                'activity_logs.user_id',
                'activity_logs.action',
                'activity_logs.description',
                'activity_logs.model_type',
                'activity_logs.model_id',
                'activity_logs.properties',
                'activity_logs.ip_address',
                'activity_logs.user_agent',
                'activity_logs.created_at',
                'activity_logs.updated_at',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('activity_logs.created_at', 'desc');

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('activity_logs.user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('activity_logs.action', 'like', '%' . $request->action . '%');
        }

        if ($request->filled('model')) {
            $query->where('activity_logs.model_type', 'like', '%' . $request->model . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('activity_logs.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('activity_logs.created_at', '<=', $request->date_to);
        }

        $activities = $query->paginate(50);
        $users = User::select('id', 'name', 'email')->get();

        return view('admin.activity-log', compact('activities', 'users'));
    }

    public function getActivityData(Request $request)
    {
        $query = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select(
                'activity_logs.id',
                'activity_logs.user_id',
                'activity_logs.action',
                'activity_logs.description',
                'activity_logs.model_type',
                'activity_logs.model_id',
                'activity_logs.properties',
                'activity_logs.ip_address',
                'activity_logs.user_agent',
                'activity_logs.created_at',
                'activity_logs.updated_at',
                'users.name as user_name',
                'users.email as user_email'
            );

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('activity_logs.user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('activity_logs.action', 'like', '%' . $request->action . '%');
        }

        if ($request->filled('model')) {
            $query->where('activity_logs.model_type', 'like', '%' . $request->model . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('activity_logs.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('activity_logs.created_at', '<=', $request->date_to);
        }

        // If specific activity ID is requested (for view more/details)
        if ($request->filled('activity_id')) {
            $query->where('activity_logs.id', $request->activity_id);
        }

        $perPage = $request->get('per_page', 50);
        $activities = $query->orderBy('activity_logs.created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'activities' => $activities
        ]);
    }

    public function getStatistics()
    {
        $today = Carbon::today();
        $week = Carbon::now()->subWeek();
        $month = Carbon::now()->subMonth();

        $stats = [
            'today' => DB::table('activity_logs')->whereDate('created_at', $today)->count(),
            'week' => DB::table('activity_logs')->where('created_at', '>=', $week)->count(),
            'month' => DB::table('activity_logs')->where('created_at', '>=', $month)->count(),
            'total' => DB::table('activity_logs')->count(),
        ];

        // Top users by activity
        $topUsers = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('users.name', 'users.email', DB::raw('COUNT(*) as activity_count'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('activity_count', 'desc')
            ->limit(5)
            ->get();

        // Activity by action type
        $actionStats = DB::table('activity_logs')
            ->select('action', DB::raw('COUNT(*) as count'))
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'top_users' => $topUsers,
            'action_stats' => $actionStats
        ]);
    }

    public function export(Request $request)
    {
        $query = DB::table('activity_logs')
            ->leftJoin('users', 'activity_logs.user_id', '=', 'users.id')
            ->select(
                'activity_logs.id',
                'activity_logs.user_id',
                'activity_logs.action',
                'activity_logs.description',
                'activity_logs.model_type',
                'activity_logs.model_id',
                'activity_logs.properties',
                'activity_logs.ip_address',
                'activity_logs.user_agent',
                'activity_logs.created_at',
                'activity_logs.updated_at',
                'users.name as user_name',
                'users.email as user_email'
            );

        // Apply same filters as index
        if ($request->filled('user_id')) {
            $query->where('activity_logs.user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('activity_logs.action', 'like', '%' . $request->action . '%');
        }

        if ($request->filled('model')) {
            $query->where('activity_logs.model_type', 'like', '%' . $request->model . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('activity_logs.created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('activity_logs.created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('activity_logs.created_at', 'desc')->get();

        $filename = 'activity_log_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, ['ID', 'Date & Time', 'User', 'Email', 'Action', 'Model Type', 'Model ID', 'Description', 'Properties', 'IP Address', 'User Agent']);
            
            // CSV data
            foreach ($activities as $activity) {
                $properties = $activity->properties ? (is_string($activity->properties) ? $activity->properties : json_encode($activity->properties)) : '';
                fputcsv($file, [
                    $activity->id,
                    $activity->created_at,
                    $activity->user_name ?? 'System',
                    $activity->user_email ?? 'N/A',
                    $activity->action ?? 'N/A',
                    $activity->model_type ?? 'N/A',
                    $activity->model_id ?? 'N/A',
                    $activity->description ?? 'N/A',
                    $properties,
                    $activity->ip_address ?? 'N/A',
                    $activity->user_agent ?? 'N/A'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
