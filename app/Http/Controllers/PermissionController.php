<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PermissionRequest;
use App\Models\User;
use App\Models\Department;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PermissionController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = Auth::user();
        
        // Determine access level
        $isHOD = $user->hasRole('HOD');
        $isHR = $user->hasRole('HR Officer');
        $isAdmin = $user->hasRole('System Admin');
        $isCEO = $user->hasAnyRole(['CEO','Director']);
        
        // Get user's department for HOD filtering
        $userDepartmentId = $user->primary_department_id ?? $user->department_id ?? null;
        
        // Get all requests or filter by user
        $query = PermissionRequest::with(['user.primaryDepartment', 'hrInitialReviewer', 'hodReviewer', 'hrFinalReviewer']);
        
        if (!$isHOD && !$isAdmin && !$isHR) {
            // Staff sees only their requests
            $query->where('user_id', $user->id);
        } elseif ($isHOD && !$isAdmin) {
            // HOD sees their department requests
            $query->whereHas('user', function($q) use ($userDepartmentId) {
                $q->where('primary_department_id', $userDepartmentId);
            });
        }
        
        $allRequests = $query->orderBy('created_at', 'desc')->get();
        
        // Categorize requests and calculate counts
        $awaitingMyAction = collect();
        $myRequests = collect();
        $otherRequests = collect();
        $processedByMe = collect();
        $pendingHR = collect();
        $pendingHOD = collect();
        $pendingHRFinal = collect();
        $returnPending = collect();
        $approved = collect();
        $rejected = collect();
        $completed = collect();
        
        foreach ($allRequests as $request) {
            $isOwnRequest = ($request->user_id == $user->id);
            
            // Determine if awaiting action
            $awaitingAction = false;
            
            // System Admin can see all requests (except completed and own requests)
            if ($isAdmin && !$isOwnRequest) {
                // Admin can see all non-completed requests as awaiting action
                if (!in_array($request->status, ['completed'])) {
                    $awaitingAction = true;
                }
            } elseif ($isHR && !$isOwnRequest) {
                // HR can see pending_hr and pending_hr_final and return_pending
                if (in_array($request->status, ['pending_hr', 'pending_hr_final', 'return_pending'])) {
                    $awaitingAction = true;
                }
            } elseif ($isHOD && !$isOwnRequest) {
                // HOD can see pending_hod
                if ($request->status === 'pending_hod') {
                    $awaitingAction = true;
                }
            }
            
            if ($awaitingAction) {
                $awaitingMyAction->push($request);
            } elseif ($isOwnRequest) {
                $myRequests->push($request);
            } else {
                $otherRequests->push($request);
            }

            // Track processed by me (including System Admin actions)
            if (
                ($isHR && ($request->hr_initial_reviewed_by === $user->id || $request->hr_final_reviewed_by === $user->id)) ||
                ($isHOD && $request->hod_reviewed_by === $user->id) ||
                ($isAdmin && ($request->hr_initial_reviewed_by === $user->id || $request->hod_reviewed_by === $user->id || $request->hr_final_reviewed_by === $user->id))
            ) {
                $processedByMe->push($request);
            }
            
            // Categorize by status for tabs
            switch ($request->status) {
                case 'pending_hr':
                    $pendingHR->push($request);
                    break;
                case 'pending_hod':
                    $pendingHOD->push($request);
                    break;
                case 'pending_hr_final':
                    $pendingHRFinal->push($request);
                    break;
                case 'return_pending':
                    $returnPending->push($request);
                    break;
                case 'approved':
                    $approved->push($request);
                    break;
                case 'rejected':
                case 'return_rejected':
                    $rejected->push($request);
                    break;
                case 'completed':
                    $completed->push($request);
                    break;
            }
        }
        
        // Calculate total pending
        $totalPending = $pendingHR->count() + $pendingHOD->count() + $pendingHRFinal->count() + $returnPending->count();
        
        return view('modules.hr.permissions', compact(
            'allRequests', 'awaitingMyAction', 'myRequests', 'otherRequests', 'processedByMe',
            'isHOD', 'isHR', 'isAdmin', 'isCEO',
            'totalPending', 'pendingHR', 'pendingHOD', 'pendingHRFinal', 'returnPending',
            'approved', 'rejected', 'completed'
        ));
    }
    
    /**
     * Get calendar events for permission requests
     */
    public function getCalendarEvents(Request $request)
    {
        $user = Auth::user();
        
        $isHOD = $user->hasRole('HOD');
        $isHR = $user->hasRole('HR Officer');
        $isAdmin = $user->hasRole('System Admin');
        $userDepartmentId = $user->primary_department_id ?? $user->department_id ?? null;
        
        $query = PermissionRequest::with(['user.primaryDepartment']);
        
        if (!$isHOD && !$isAdmin && !$isHR) {
            $query->where('user_id', $user->id);
        } elseif ($isHOD && !$isAdmin) {
            $query->whereHas('user', function($q) use ($userDepartmentId) {
                $q->where('primary_department_id', $userDepartmentId);
            });
        }
        
        $start = $request->get('start', now()->startOfMonth()->toDateString());
        $end = $request->get('end', now()->endOfMonth()->toDateString());
        
        $requests = $query->where(function($q) use ($start, $end) {
                $q->whereBetween('start_datetime', [$start, $end])
                  ->orWhereBetween('end_datetime', [$start, $end])
                  ->orWhere(function($subQ) use ($start, $end) {
                      $subQ->where('start_datetime', '<=', $start)
                           ->where('end_datetime', '>=', $end);
                  });
            })
            ->get();
        
        $events = [];
        foreach ($requests as $req) {
            $statusColors = [
                'pending_hr' => '#ffc107',
                'pending_hod' => '#17a2b8',
                'pending_hr_final' => '#007bff',
                'approved' => '#28a745',
                'rejected' => '#dc3545',
                'return_pending' => '#fd7e14',
                'completed' => '#6c757d',
            ];
            
            $color = $statusColors[$req->status] ?? '#6c757d';
            $duration = Carbon::parse($req->start_datetime)->diffInDays(Carbon::parse($req->end_datetime));
            
            $events[] = [
                'id' => $req->id,
                'title' => $req->user->name . ' - ' . ucfirst($req->reason_type),
                'start' => $req->start_datetime->toIso8601String(),
                'end' => $req->end_datetime->toIso8601String(),
                'color' => $color,
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'request_id' => $req->request_id,
                    'status' => $req->status,
                    'reason_type' => $req->reason_type,
                    'duration' => $duration . ' ' . ($req->time_mode === 'days' ? 'days' : 'hours'),
                    'department' => $req->user->primaryDepartment->name ?? 'N/A',
                ],
            ];
        }
        
        return response()->json($events);
    }
    
    /**
     * Get analytics data
     */
    public function getAnalytics(Request $request)
    {
        $user = Auth::user();
        
        $isHOD = $user->hasRole('HOD');
        $isHR = $user->hasRole('HR Officer');
        $isAdmin = $user->hasRole('System Admin');
        $userDepartmentId = $user->primary_department_id ?? $user->department_id ?? null;
        
        $query = PermissionRequest::with(['user.primaryDepartment']);
        
        if (!$isHOD && !$isAdmin && !$isHR) {
            $query->where('user_id', $user->id);
        } elseif ($isHOD && !$isAdmin) {
            $query->whereHas('user', function($q) use ($userDepartmentId) {
                $q->where('primary_department_id', $userDepartmentId);
            });
        }
        
        $period = $request->get('period', 'month'); // month, quarter, year
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());
        
        $requests = $query->whereBetween('created_at', [$startDate, $endDate])->get();
        
        // Status distribution
        $statusData = $requests->groupBy('status')->map->count();
        
        // Monthly trend
        $monthlyTrend = $requests->groupBy(function($req) {
            return $req->created_at->format('Y-m');
        })->map->count();
        
        // Department distribution
        $departmentData = $requests->groupBy(function($req) {
            return $req->user->primaryDepartment->name ?? 'No Department';
        })->map->count();
        
        // Reason type distribution
        $reasonData = $requests->groupBy('reason_type')->map->count();
        
        // Average processing time
        $avgProcessingTime = $requests->filter(function($req) {
            return $req->hr_final_reviewed && $req->created_at;
        })->map(function($req) {
            return $req->created_at->diffInDays($req->hr_final_reviewed);
        })->avg();
        
        // Approval rate
        $total = $requests->count();
        $approved = $requests->where('status', 'approved')->count();
        $rejected = $requests->whereIn('status', ['rejected', 'return_rejected'])->count();
        $approvalRate = $total > 0 ? ($approved / $total) * 100 : 0;
        
        // Top requesters
        $topRequesters = $requests->groupBy('user_id')
            ->map(function($userRequests) {
                return [
                    'count' => $userRequests->count(),
                    'name' => $userRequests->first()->user->name ?? 'Unknown',
                ];
            })
            ->sortByDesc('count')
            ->take(10)
            ->values();
        
        return response()->json([
            'status_distribution' => $statusData,
            'monthly_trend' => $monthlyTrend,
            'department_distribution' => $departmentData,
            'reason_distribution' => $reasonData,
            'avg_processing_time' => round($avgProcessingTime ?? 0, 1),
            'approval_rate' => round($approvalRate, 2),
            'total_requests' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'top_requesters' => $topRequesters,
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'time_mode' => 'required|in:hours,days',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'reason_type' => 'required|in:official,personal,medical,emergency,other',
            'reason_description' => 'required|string|max:1000',
        ]);
        
        $user = Auth::user();
        
        // Generate unique request ID
        $today = date('Ymd');
        $lastRequest = PermissionRequest::whereDate('created_at', today())
            ->where('request_id', 'like', 'PR' . $today . '-%')
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = 1;
        if ($lastRequest && preg_match('/PR\d{8}-(\d{3})/', $lastRequest->request_id, $matches)) {
            $sequence = (int)$matches[1] + 1;
        }
        
        $requestId = 'PR' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
        
        $permissionRequest = PermissionRequest::create([
            'request_id' => $requestId,
            'user_id' => $user->id,
            'name' => $validated['name'],
            'time_mode' => $validated['time_mode'],
            'start_datetime' => $validated['start_datetime'],
            'end_datetime' => $validated['end_datetime'],
            'reason_type' => $validated['reason_type'],
            'reason_description' => $validated['reason_description'],
            'status' => 'pending_hr',
        ]);

        // Send notifications with SMS
        try {
            // Notify staff with SMS
            $this->notificationService->notify(
                $user->id,
                "Permission Request Submitted: Your permission request #{$requestId} has been submitted and is pending HR review.",
                route('modules.hr.permissions'),
                'Permission Request Submitted'
            );
            \Log::info('SMS notification sent to staff for permission request submission: ' . $user->id);

            // Notify HR with SMS
            $this->notificationService->notifyHR(
                "New Permission Request: Permission request #{$requestId} from {$user->name} is pending your review.",
                route('modules.hr.permissions'),
                'New Permission Request',
                ['request_id' => $requestId, 'staff_name' => $user->name]
            );
            \Log::info('SMS notification sent to HR for new permission request: ' . $requestId);
        } catch (\Exception $e) {
            \Log::error('Notification error in permission store: ' . $e->getMessage());
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Permission request submitted successfully! Your request has been sent for review.',
            'request_id' => $requestId,
            'id' => $permissionRequest->id
        ]);
    }
    
    /**
     * HR Initial Review (Staff → HR)
     */
    public function hrInitialReview(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
                return response()->json(['success' => false, 'message' => 'Unauthorized. Only HR Officers can review permissions.'], 403);
            }
            
            $validated = $request->validate([
                'decision' => 'required|in:approve,reject',
                'comments' => 'required|string|max:1000',
            ]);
            
            $permissionRequest = PermissionRequest::with('user')->findOrFail($id);
            $isAdmin = $user->hasRole('System Admin');
            
            // System Admin can approve at any stage (except completed), others must wait for pending_hr
            if (!$isAdmin) {
                if ($permissionRequest->status !== 'pending_hr') {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Request is not pending HR review. Current status: ' . ucwords(str_replace('_', ' ', $permissionRequest->status))
                    ], 400);
                }
            } else {
                // Admin can review at any stage except completed
                if ($permissionRequest->status === 'completed') {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Cannot modify a completed request.'
                    ], 400);
                }
            }
            
            // Determine new status based on decision and current status
            if ($validated['decision'] === 'approve') {
                // If admin is approving, move to next stage or approve directly if already past HOD
                if ($isAdmin && in_array($permissionRequest->status, ['pending_hr_final', 'approved'])) {
                    $newStatus = 'approved';
                } else {
                    $newStatus = 'pending_hod';
                }
            } else {
                $newStatus = 'rejected';
            }
            
            $permissionRequest->update([
                'status' => $newStatus,
                'hr_initial_reviewed' => now(),
                'hr_initial_reviewed_by' => $user->id,
                'hr_initial_comments' => $validated['comments'],
            ]);

        // Send notifications with SMS
        try {
            $requestId = $permissionRequest->request_id;
            $staff = $permissionRequest->user;
            
            if ($validated['decision'] === 'approve') {
                // Notify HR of successful review
                $this->notificationService->notify(
                    $user->id,
                    "HR Review Complete: You have reviewed permission request #{$requestId} from {$staff->name} and forwarded it to HOD.",
                    route('modules.hr.permissions'),
                    'Permission Request Reviewed'
                );
                \Log::info('SMS notification sent to HR reviewer: ' . $user->id);

                // Notify staff with SMS
                $this->notificationService->notify(
                    $staff->id,
                    "Permission Request Reviewed: Your permission request #{$requestId} has been reviewed by HR and forwarded to HOD for approval.",
                    route('modules.hr.permissions'),
                    'Permission Request Forwarded to HOD'
                );
                \Log::info('SMS notification sent to staff for HR review: ' . $staff->id);

                // Notify HOD with SMS
                if ($staff->primary_department_id) {
                    $this->notificationService->notifyHOD(
                        $staff->primary_department_id,
                        "New Permission Request: Permission request #{$requestId} from {$staff->name} is pending your approval.",
                        route('modules.hr.permissions'),
                        'New Permission Request Pending Approval',
                        ['request_id' => $requestId, 'staff_name' => $staff->name]
                    );
                    \Log::info('SMS notification sent to HOD for permission request: ' . $requestId);
                }
            } else {
                // Notify staff of rejection with SMS
                $this->notificationService->notify(
                    $staff->id,
                    "Permission Request Rejected: Your permission request #{$requestId} has been rejected by HR. Please check the comments.",
                    route('modules.hr.permissions'),
                    'Permission Request Rejected'
                );
                \Log::info('SMS notification sent to staff for rejection: ' . $staff->id);
            }
        } catch (\Exception $e) {
            \Log::error('Notification error in hrInitialReview: ' . $e->getMessage());
        }
        
        $message = $validated['decision'] === 'approve' 
            ? 'Request reviewed and forwarded to HOD' 
            : 'Request rejected';
        
        return response()->json(['success' => true, 'message' => $message]);
        
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission request not found'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('HR Initial Review Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing the review: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * HOD Review (HR → HOD)
     */
    public function hodReview(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HOD', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'required|string|max:1000',
        ]);
        
        $permissionRequest = PermissionRequest::findOrFail($id);
        $isAdmin = $user->hasRole('System Admin');
        
        // System Admin can approve at any stage (except completed), others must wait for pending_hod
        if (!$isAdmin) {
            if ($permissionRequest->status !== 'pending_hod') {
                return response()->json(['success' => false, 'message' => 'Request is not pending HOD review']);
            }
            
            // Check if HOD can review (same department)
            if ($user->hasRole('HOD')) {
                $requestDeptId = $permissionRequest->user->primary_department_id ?? $permissionRequest->user->department_id ?? null;
                $hodDeptId = $user->primary_department_id ?? $user->department_id ?? null;
                
                if ($requestDeptId !== $hodDeptId) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'You can only review requests from your department'
                    ]);
                }
            }
        } else {
            // Admin can review at any stage except completed
            if ($permissionRequest->status === 'completed') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot modify a completed request.'
                ], 400);
            }
        }
        
        // Determine new status based on decision and current status
        if ($validated['decision'] === 'approve') {
            // If admin is approving, move to next stage or approve directly if already past HR final
            if ($isAdmin && in_array($permissionRequest->status, ['pending_hr_final', 'approved'])) {
                $newStatus = 'approved';
            } else {
                $newStatus = 'pending_hr_final';
            }
        } else {
            $newStatus = 'rejected';
        }
        
        $oldStatus = $permissionRequest->status;
        $permissionRequest->update([
            'status' => $newStatus,
            'hod_reviewed' => now(),
            'hod_reviewed_by' => $user->id,
            'hod_comments' => $validated['comments'],
        ]);

        // Log activity
        if ($validated['decision'] === 'approve') {
            ActivityLogService::logApproved($permissionRequest, "HOD approved permission request #{$permissionRequest->request_id}", $user->name, [
                'hod_comments' => $validated['comments'],
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        } else {
            ActivityLogService::logRejected($permissionRequest, "HOD rejected permission request #{$permissionRequest->request_id}", $user->name, $validated['comments'], [
                'hod_comments' => $validated['comments'],
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }

        // Send notifications with SMS
        try {
            $requestId = $permissionRequest->request_id;
            $staff = $permissionRequest->user;
            
            if ($validated['decision'] === 'approve') {
                // Notify HOD with SMS
                $this->notificationService->notify(
                    $user->id,
                    "Permission Approved: You have approved permission request #{$requestId} from {$staff->name} and it has been forwarded to HR for final approval.",
                    route('modules.hr.permissions'),
                    'Permission Request Approved'
                );
                \Log::info('SMS notification sent to HOD: ' . $user->id);

                // Notify staff with SMS
                $this->notificationService->notify(
                    $staff->id,
                    "Permission Approved by HOD: Your permission request #{$requestId} has been approved by HOD and is pending HR final approval.",
                    route('modules.hr.permissions'),
                    'Permission Request Approved by HOD'
                );
                \Log::info('SMS notification sent to staff for HOD approval: ' . $staff->id);

                // Notify HR with SMS
                $this->notificationService->notifyHR(
                    "Permission Request Pending Final Approval: Permission request #{$requestId} from {$staff->name} is pending your final approval.",
                    route('modules.hr.permissions'),
                    'Permission Request Pending Final Approval',
                    ['request_id' => $requestId, 'staff_name' => $staff->name]
                );
                \Log::info('SMS notification sent to HR for final approval: ' . $requestId);
            } else {
                // Notify staff of rejection with SMS
                $this->notificationService->notify(
                    $staff->id,
                    "Permission Request Rejected: Your permission request #{$requestId} has been rejected by HOD. Please check the comments.",
                    route('modules.hr.permissions'),
                    'Permission Request Rejected'
                );
                \Log::info('SMS notification sent to staff for HOD rejection: ' . $staff->id);
            }
        } catch (\Exception $e) {
            \Log::error('Notification error in hodReview: ' . $e->getMessage());
        }
        
        $message = $validated['decision'] === 'approve' 
            ? 'Request approved and forwarded to HR for final approval' 
            : 'Request rejected';
        
        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * HR Final Approval (HOD → HR)
     */
    public function hrFinalApproval(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'nullable|string|max:1000',
        ]);
        
        $permissionRequest = PermissionRequest::findOrFail($id);
        $isAdmin = $user->hasRole('System Admin');
        
        // System Admin can approve at any stage (except completed), others must wait for pending_hr_final
        if (!$isAdmin) {
            if ($permissionRequest->status !== 'pending_hr_final') {
                return response()->json(['success' => false, 'message' => 'Request is not pending HR final approval']);
            }
        } else {
            // Admin can approve at any stage except completed
            if ($permissionRequest->status === 'completed') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Cannot modify a completed request.'
                ], 400);
            }
        }
        
        $oldStatus = $permissionRequest->status;
        $newStatus = $validated['decision'] === 'approve' ? 'approved' : 'rejected';
        
        $permissionRequest->update([
            'status' => $newStatus,
            'hr_final_reviewed' => now(),
            'hr_final_reviewed_by' => $user->id,
            'hr_final_comments' => $validated['comments'],
        ]);

        // Log activity
        if ($validated['decision'] === 'approve') {
            ActivityLogService::logApproved($permissionRequest, "HR granted final approval for permission request #{$permissionRequest->request_id}", $user->name, [
                'hr_final_comments' => $validated['comments'],
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        } else {
            ActivityLogService::logRejected($permissionRequest, "HR rejected permission request #{$permissionRequest->request_id}", $user->name, $validated['comments'], [
                'hr_final_comments' => $validated['comments'],
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }

        // Send notifications with SMS
        try {
            $requestId = $permissionRequest->request_id;
            $staff = $permissionRequest->user;
            
            if ($validated['decision'] === 'approve') {
                // Notify HR with SMS
                $this->notificationService->notify(
                    $user->id,
                    "Final Approval Granted: You have granted final approval for permission request #{$requestId} from {$staff->name}. They may now proceed.",
                    route('modules.hr.permissions'),
                    'Permission Request Approved'
                );
                \Log::info('SMS notification sent to HR for final approval: ' . $user->id);

                // Notify staff with SMS
                $this->notificationService->notify(
                    $staff->id,
                    "Permission Request Approved: Your permission request #{$requestId} has been approved by HR. You may proceed with your request. Please confirm your return after completion.",
                    route('modules.hr.permissions'),
                    'Permission Request Approved - You May Proceed'
                );
                \Log::info('SMS notification sent to staff for final approval: ' . $staff->id);
            } else {
                // Notify staff of rejection with SMS
                $this->notificationService->notify(
                    $staff->id,
                    "Permission Request Rejected: Your permission request #{$requestId} has been rejected by HR. Please check the comments.",
                    route('modules.hr.permissions'),
                    'Permission Request Rejected'
                );
                \Log::info('SMS notification sent to staff for final rejection: ' . $staff->id);
            }
        } catch (\Exception $e) {
            \Log::error('Notification error in hrFinalApproval: ' . $e->getMessage());
        }
        
        $message = $validated['decision'] === 'approve' 
            ? 'Final approval granted. Staff may proceed.' 
            : 'Request rejected';
        
        return response()->json(['success' => true, 'message' => $message]);
    }
    
    public function confirmReturn(Request $request, $id)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'return_datetime' => 'required|date',
            'return_remarks' => 'nullable|string|max:1000',
        ]);
        
        $permissionRequest = PermissionRequest::findOrFail($id);
        
        if ($permissionRequest->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }
        
        if ($permissionRequest->status !== 'approved') {
            return response()->json([
                'success' => false, 
                'message' => 'Request must be approved before confirming return. Current status: ' . ucwords(str_replace('_', ' ', $permissionRequest->status))
            ]);
        }
        
        $permissionRequest->update([
            'return_datetime' => $validated['return_datetime'],
            'return_remarks' => $validated['return_remarks'],
            'return_submitted_at' => now(),
            'status' => 'return_pending',
        ]);

        // Send notifications with SMS
        try {
            $requestId = $permissionRequest->request_id;
            
            // Notify staff with SMS
            $this->notificationService->notify(
                $user->id,
                "Return Confirmation Submitted: You have submitted your return confirmation for permission request #{$requestId}. It is pending HR verification.",
                route('modules.hr.permissions'),
                'Return Confirmation Submitted'
            );
            \Log::info('SMS notification sent to staff for return confirmation: ' . $user->id);

            // Notify HR with SMS
            $this->notificationService->notifyHR(
                "Return Confirmation Pending: Return confirmation submitted for permission request #{$requestId} by {$user->name}. Please verify.",
                route('modules.hr.permissions'),
                'Return Confirmation Pending',
                ['request_id' => $requestId, 'staff_name' => $user->name]
            );
            \Log::info('SMS notification sent to HR for return confirmation: ' . $requestId);
        } catch (\Exception $e) {
            \Log::error('Notification error in confirmReturn: ' . $e->getMessage());
        }
        
        return response()->json(['success' => true, 'message' => 'Return confirmation submitted successfully']);
    }
    
    /**
     * HR Return Verification (Staff Return → HR)
     */
    public function hrReturnVerification(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized']);
        }
        
        $validated = $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'nullable|string|max:1000',
        ]);
        
        $permissionRequest = PermissionRequest::findOrFail($id);
        $isAdmin = $user->hasRole('System Admin');
        
        // System Admin can verify returns at any stage (if return_datetime exists), others must wait for return_pending
        if (!$isAdmin) {
            if ($permissionRequest->status !== 'return_pending') {
                return response()->json(['success' => false, 'message' => 'Return is not pending verification']);
            }
        } else {
            // Admin can verify return if return_datetime exists and status is not completed
            if ($permissionRequest->status === 'completed') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Request is already completed.'
                ], 400);
            }
            if (!$permissionRequest->return_datetime) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Staff has not confirmed return yet.'
                ], 400);
            }
        }
        
        $newStatus = $validated['decision'] === 'approve' ? 'completed' : 'return_rejected';
        
        $updateData = [
            'status' => $newStatus,
        ];
        
        // Store return verification comments separately to avoid overwriting final approval comments
        $returnComments = $validated['comments'] ?? '';
        if ($returnComments) {
            $updateData['hr_final_comments'] = ($permissionRequest->hr_final_comments ? $permissionRequest->hr_final_comments . "\n\n--- Return Verification ---\n" : '') . $returnComments;
        }
        
        // Update reviewed fields only if not already set, or add return verification timestamp
        if (!$permissionRequest->hr_final_reviewed) {
            $updateData['hr_final_reviewed'] = now();
            $updateData['hr_final_reviewed_by'] = $user->id;
        }
        
        $permissionRequest->update($updateData);

        // Send notifications
        try {
            $requestId = $permissionRequest->request_id;
            $staff = $permissionRequest->user;
            
            if ($validated['decision'] === 'approve') {
                // Notify staff
                $this->notificationService->notify(
                    $staff->id,
                    "Your return for permission request #{$requestId} has been verified and completed by HR. The request is now closed.",
                    route('modules.hr.permissions'),
                    'Permission Request Completed'
                );
                
                // Notify HR reviewer (if different from current user)
                if ($permissionRequest->hr_initial_reviewed_by && $permissionRequest->hr_initial_reviewed_by !== $user->id) {
                    $this->notificationService->notify(
                        $permissionRequest->hr_initial_reviewed_by,
                        "Permission request #{$requestId} from {$staff->name} has been completed and closed.",
                        route('modules.hr.permissions'),
                        'Permission Request Completed'
                    );
                }
                
                // Notify HOD (if exists)
                if ($permissionRequest->hod_reviewed_by && $permissionRequest->hod_reviewed_by !== $user->id) {
                    $this->notificationService->notify(
                        $permissionRequest->hod_reviewed_by,
                        "Permission request #{$requestId} from {$staff->name} has been completed and closed.",
                        route('modules.hr.permissions'),
                        'Permission Request Completed'
                    );
                }
            } else {
                // Notify staff of rejection
                $this->notificationService->notify(
                    $staff->id,
                    "Your return confirmation for permission request #{$requestId} has been rejected by HR. Please check the comments and resubmit your return confirmation.",
                    route('modules.hr.permissions'),
                    'Return Confirmation Rejected'
                );
            }
        } catch (\Exception $e) {
            \Log::error('Notification error: ' . $e->getMessage());
        }
        
        return response()->json(['success' => true, 'message' => 'Return verification completed']);
    }
    
    public function show($id)
    {
        $user = Auth::user();
        $permissionRequest = PermissionRequest::with([
            'user.primaryDepartment',
            'hrInitialReviewer',
            'hodReviewer',
            'hrFinalReviewer'
        ])->findOrFail($id);
        
        // Check access
        if ($permissionRequest->user_id !== $user->id && 
            !$user->hasAnyRole(['HOD', 'HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized access');
        }
        
        // Determine user roles
        $isHOD = $user->hasRole('HOD');
        $isHR = $user->hasRole('HR Officer');
        $isAdmin = $user->hasRole('System Admin');
        $isCEO = $user->hasAnyRole(['CEO','Director']);
        $isOwn = ($permissionRequest->user_id == $user->id);
        
        // Calculate duration
        $startDate = Carbon::parse($permissionRequest->start_datetime);
        $endDate = Carbon::parse($permissionRequest->end_datetime);
        
        if ($permissionRequest->time_mode === 'days') {
            $duration = $startDate->diffInDays($endDate) + 1; // +1 to include both start and end days
        } else {
            $duration = $startDate->diffInHours($endDate);
        }
        
        // Build timeline
        $timeline = [];
        $timeline[] = [
            'title' => 'Request Submitted',
            'description' => 'Permission request was created',
            'date' => $permissionRequest->created_at,
            'icon' => 'bx-file',
            'color' => 'primary',
            'user' => $permissionRequest->user->name
        ];
        
        if ($permissionRequest->hr_initial_reviewed) {
            $timeline[] = [
                'title' => 'HR Initial Review',
                'description' => $permissionRequest->hr_initial_comments ?? 'Reviewed by HR',
                'date' => $permissionRequest->hr_initial_reviewed,
                'icon' => 'bx-user-check',
                'color' => $permissionRequest->status === 'rejected' && !$permissionRequest->hod_reviewed ? 'danger' : 'info',
                'user' => $permissionRequest->hrInitialReviewer->name ?? 'HR Officer',
                'decision' => $permissionRequest->status === 'rejected' && !$permissionRequest->hod_reviewed ? 'Rejected' : 'Approved'
            ];
        }
        
        if ($permissionRequest->hod_reviewed) {
            $timeline[] = [
                'title' => 'HOD Review',
                'description' => $permissionRequest->hod_comments ?? 'Reviewed by HOD',
                'date' => $permissionRequest->hod_reviewed,
                'icon' => 'bx-check-circle',
                'color' => $permissionRequest->status === 'rejected' && $permissionRequest->hod_reviewed ? 'danger' : 'info',
                'user' => $permissionRequest->hodReviewer->name ?? 'HOD',
                'decision' => $permissionRequest->status === 'rejected' && $permissionRequest->hod_reviewed ? 'Rejected' : 'Approved'
            ];
        }
        
        if ($permissionRequest->hr_final_reviewed) {
            $timeline[] = [
                'title' => 'HR Final Approval',
                'description' => $permissionRequest->hr_final_comments ?? 'Final approval granted',
                'date' => $permissionRequest->hr_final_reviewed,
                'icon' => 'bx-check-double',
                'color' => $permissionRequest->status === 'approved' ? 'success' : 'danger',
                'user' => $permissionRequest->hrFinalReviewer->name ?? 'HR Officer',
                'decision' => $permissionRequest->status === 'approved' ? 'Approved' : 'Rejected'
            ];
        }
        
        if ($permissionRequest->return_datetime) {
            $timeline[] = [
                'title' => 'Return Confirmed',
                'description' => $permissionRequest->return_remarks ?? 'Staff confirmed return',
                'date' => $permissionRequest->return_submitted_at ?? $permissionRequest->return_datetime,
                'icon' => 'bx-undo',
                'color' => 'warning',
                'user' => $permissionRequest->user->name
            ];
        }
        
        if ($permissionRequest->status === 'completed') {
            $timeline[] = [
                'title' => 'Request Completed',
                'description' => 'Return verified and request closed',
                'date' => $permissionRequest->updated_at,
                'icon' => 'bx-task',
                'color' => 'success',
                'user' => 'System'
            ];
        }
        
        // Calculate processing times
        $processingTimes = [];
        if ($permissionRequest->hr_initial_reviewed) {
            $processingTimes['hr_initial'] = $permissionRequest->created_at->diffInHours($permissionRequest->hr_initial_reviewed);
        }
        if ($permissionRequest->hod_reviewed && $permissionRequest->hr_initial_reviewed) {
            $processingTimes['hod'] = $permissionRequest->hr_initial_reviewed->diffInHours($permissionRequest->hod_reviewed);
        }
        if ($permissionRequest->hr_final_reviewed && $permissionRequest->hod_reviewed) {
            $processingTimes['hr_final'] = $permissionRequest->hod_reviewed->diffInHours($permissionRequest->hr_final_reviewed);
        }
        if ($permissionRequest->hr_final_reviewed) {
            $processingTimes['total'] = $permissionRequest->created_at->diffInHours($permissionRequest->hr_final_reviewed);
        }
        
        return view('modules.hr.permission-details', compact(
            'permissionRequest',
            'isHOD', 'isHR', 'isAdmin', 'isCEO', 'isOwn',
            'duration', 'timeline', 'processingTimes'
        ));
    }

    /**
     * Generate PDF for permission request
     */
    public function generatePdf($id)
    {
        $user = Auth::user();
        
        $permissionRequest = PermissionRequest::with([
            'user.primaryDepartment',
            'hrInitialReviewer',
            'hodReviewer',
            'hrFinalReviewer'
        ])->findOrFail($id);
        
        // Check access
        $isOwnRequest = ($permissionRequest->user_id === $user->id);
        $isManager = $user->hasAnyRole(['HOD', 'HR Officer', 'CEO', 'System Admin']);
        
        if (!$isOwnRequest && !$isManager) {
            abort(403, 'You are not authorized to view this report.');
        }
        
        try {
            // Get logo
            $logoSrc = null;
            $logoPath = public_path('assets/img/office_link_logo.png');
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoSrc = 'data:image/png;base64,' . $logoData;
            } else {
                // Fallback logo path
                $logoPath = public_path('assets/img/logo.png');
                if (file_exists($logoPath)) {
                    $logoData = base64_encode(file_get_contents($logoPath));
                    $logoSrc = 'data:image/png;base64,' . $logoData;
                }
            }
            
            // Format request data for PDF
            $requestData = [
                'request_id' => $permissionRequest->request_id,
                'first_name' => $permissionRequest->user->name,
                'last_name' => '',
                'email' => $permissionRequest->user->email ?? 'N/A',
                'phone_number' => $permissionRequest->user->phone ?? ($permissionRequest->user->mobile ?? 'N/A'),
                'department_name' => $permissionRequest->user->primaryDepartment->name ?? 'N/A',
                'status' => $permissionRequest->status,
                'time_mode' => $permissionRequest->time_mode,
                'start_datetime' => $permissionRequest->start_datetime,
                'end_datetime' => $permissionRequest->end_datetime,
                'reason_type' => $permissionRequest->reason_type,
                'reason_description' => $permissionRequest->reason_description,
                'created_at' => $permissionRequest->created_at,
                'hod_reviewed' => $permissionRequest->hod_reviewed,
                'hod_comments' => $permissionRequest->hod_comments,
                'hod_reviewer_name' => $permissionRequest->hodReviewer->name ?? null,
                'hr_initial_reviewed' => $permissionRequest->hr_initial_reviewed,
                'hr_initial_comments' => $permissionRequest->hr_initial_comments,
                'hr_initial_reviewer_name' => $permissionRequest->hrInitialReviewer->name ?? null,
                'hr_final_reviewed' => $permissionRequest->hr_final_reviewed,
                'hr_final_comments' => $permissionRequest->hr_final_comments,
                'hr_final_reviewer_name' => $permissionRequest->hrFinalReviewer->name ?? null,
                'return_datetime' => $permissionRequest->return_datetime,
                'return_remarks' => $permissionRequest->return_remarks,
                'return_submitted_at' => $permissionRequest->return_submitted_at,
                'hod_return_reviewed' => $permissionRequest->hod_return_reviewed,
                'hod_return_comments' => $permissionRequest->hod_return_comments,
            ];
            
            // Calculate duration
            $startDate = Carbon::parse($permissionRequest->start_datetime);
            $endDate = Carbon::parse($permissionRequest->end_datetime);
            $duration = $endDate->diffInHours($startDate);
            if ($permissionRequest->time_mode === 'days') {
                $duration = $endDate->diffInDays($startDate);
            }
            
            $data = [
                'request' => $requestData,
                'logoSrc' => $logoSrc,
                'main_color' => '#940000',
                'generation_date' => Carbon::now()->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A'),
                'duration' => $duration,
                'time_mode' => $permissionRequest->time_mode,
            ];
            
            $pdf = Pdf::loadView('modules.hr.permissions-pdf', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);
            
            $filename = "Permission_Report_{$permissionRequest->request_id}.pdf";
            
            return $pdf->stream($filename);
            
        } catch (\Exception $e) {
            \Log::error('Permission PDF generation error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_id' => $id
            ]);
            
            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
        }
    }
}
