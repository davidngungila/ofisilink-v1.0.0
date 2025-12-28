<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentActivity;
use App\Models\AssessmentProgressReport;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AssessmentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = Auth::user();
        $isHR = $user->hasRole('HR Officer');
        $isHOD = $user->hasRole('HOD');
        $isCEO = $user->hasRole('CEO');
        $isAdmin = $user->hasRole('System Admin');
        $isManager = $isHR || $isHOD || $isCEO || $isAdmin;

        $query = Assessment::with(['employee.primaryDepartment', 'activities', 'hodApprover']);

        if (!$isManager) {
            // Regular staff see only their own assessments
            $query->where('employee_id', $user->id);
        } elseif ($isHR) {
            // HR sees ALL assessments from ALL staff - no filter needed
            // Query remains as is (no where clause)
        } elseif ($isHOD && !$isAdmin) {
            // HOD sees assessments from ALL staff in their department
            if ($user->primary_department_id) {
                $query->whereHas('employee', function($q) use ($user) {
                    $q->where('primary_department_id', $user->primary_department_id);
                });
            } else {
                // If HOD has no department, return empty result
                $query->whereRaw('1 = 0');
            }
        } elseif ($isCEO && !$isAdmin) {
            // CEO sees all - no filter needed
        }

        $assessments = $query->orderBy('created_at', 'desc')->get();

        // For staff users, load activities with progress reports upfront
        if (!$isManager) {
            $assessments->load(['activities.progressReports' => function($q) {
                $q->with('hodApprover')->orderBy('created_at', 'desc');
            }]);
        }

        $awaitingMyAction = collect();
        $myAssessments = collect();
        $otherAssessments = collect();

        foreach ($assessments as $assessment) {
            $isOwn = $assessment->employee_id == $user->id;
            $awaitingAction = false;

            // HOD and HR can approve assessments
            if (($isHOD || $isHR) && !$isOwn && !$isAdmin) {
                if ($assessment->status === 'pending_hod') {
                    $awaitingAction = true;
                }
            }

            if ($awaitingAction) {
                $awaitingMyAction->push($assessment);
            } elseif ($isOwn) {
                $myAssessments->push($assessment);
            } else {
                $otherAssessments->push($assessment);
            }
        }

        // For HOD: pending progress reports from department staff
        $pendingReports = collect();
        if ($isHOD && !$isAdmin) {
            if ($user->primary_department_id) {
                $pendingReports = AssessmentProgressReport::with(['activity.assessment.employee.primaryDepartment'])
                    ->where('status', 'pending_approval')
                    ->whereHas('activity.assessment.employee', function($q) use ($user) {
                        $q->where('primary_department_id', $user->primary_department_id);
                    })
                    ->latest('report_date')
                    ->limit(50)
                    ->get();
            } else {
                $pendingReports = collect();
            }
        } elseif ($isAdmin) {
            $pendingReports = AssessmentProgressReport::with(['activity.assessment.employee.primaryDepartment'])
                ->where('status', 'pending_approval')
                ->latest('report_date')
                ->limit(50)
                ->get();
        }

        // For Staff: compute current-period submission status per activity
        // Enhanced to check all periods properly based on frequency
        $currentPeriodStatus = [];
        if (!$isManager) {
            $now = Carbon::now();
            // Relationships are already loaded on $assessments above
            
            foreach ($myAssessments as $ass) {
                foreach ($ass->activities as $act) {
                    $start = null; 
                    $end = null;
                    
                    // Determine period based on frequency
                    if ($act->reporting_frequency === 'daily') {
                        $start = $now->copy()->startOfDay();
                        $end = $now->copy()->endOfDay();
                    } elseif ($act->reporting_frequency === 'weekly') {
                        $start = $now->copy()->startOfWeek();
                        $end = $now->copy()->endOfWeek();
                    } elseif ($act->reporting_frequency === 'monthly') {
                        $start = $now->copy()->startOfMonth();
                        $end = $now->copy()->endOfMonth();
                    } elseif ($act->reporting_frequency === 'quarterly') {
                        $quarter = ceil($now->month / 3);
                        $start = Carbon::create($now->year, ($quarter - 1) * 3 + 1, 1)->startOfMonth();
                        $end = $start->copy()->addMonths(2)->endOfMonth();
                    } else {
                        // Default to monthly if unknown
                        $start = $now->copy()->startOfMonth();
                        $end = $now->copy()->endOfMonth();
                    }
                    
                    // Check for existing report in this period (check all statuses)
                    $existing = AssessmentProgressReport::where('activity_id', $act->id)
                        ->where(function($query) use ($start, $end, $act, $now) {
                            if ($act->reporting_frequency === 'daily') {
                                $query->whereDate('report_date', $now->toDateString());
                            } elseif ($act->reporting_frequency === 'weekly') {
                                $query->whereBetween('report_date', [$start->toDateString(), $end->toDateString()]);
                            } elseif ($act->reporting_frequency === 'monthly') {
                                $query->whereYear('report_date', $now->year)
                                      ->whereMonth('report_date', $now->month);
                            } elseif ($act->reporting_frequency === 'quarterly') {
                                $quarter = ceil($now->month / 3);
                                $qStart = Carbon::create($now->year, ($quarter - 1) * 3 + 1, 1);
                                $qEnd = $qStart->copy()->endOfQuarter();
                                $query->whereBetween('report_date', [$qStart->toDateString(), $qEnd->toDateString()]);
                            }
                        })
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($existing) {
                        $currentPeriodStatus[$act->id] = [
                            'exists' => true,
                            'status' => $existing->status,
                            'report_date' => optional($existing->report_date)->toDateString(),
                            'report_id' => $existing->id,
                            'submitted_at' => optional($existing->created_at)->toDateTimeString(),
                            'approver' => optional($existing->hodApprover)->name,
                            'comments' => $existing->hod_comments,
                        ];
                    } else {
                        $currentPeriodStatus[$act->id] = [ 
                            'exists' => false, 
                            'status' => null, 
                            'report_date' => null,
                            'report_id' => null,
                            'submitted_at' => null,
                            'approver' => null,
                            'comments' => null,
                        ];
                    }
                }
            }
        }

        // Get comprehensive statistics for admin
        $statistics = [];
        if ($isAdmin || $isHR) {
            $statistics = [
                'total_assessments' => Assessment::count(),
                'approved_assessments' => Assessment::where('status', 'approved')->count(),
                'pending_assessments' => Assessment::where('status', 'pending_hod')->count(),
                'rejected_assessments' => Assessment::where('status', 'rejected')->count(),
                'total_activities' => AssessmentActivity::count(),
                'total_reports' => AssessmentProgressReport::count(),
                'pending_reports' => AssessmentProgressReport::where('status', 'pending_approval')->count(),
                'approved_reports' => AssessmentProgressReport::where('status', 'approved')->count(),
                'rejected_reports' => AssessmentProgressReport::where('status', 'rejected')->count(),
                'reports_this_month' => AssessmentProgressReport::whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)->count(),
            ];
        }
        
        return view('modules.hr.assessments', compact(
            'awaitingMyAction', 'myAssessments', 'otherAssessments', 'pendingReports',
            'isHR', 'isHOD', 'isCEO', 'isAdmin', 'isManager', 'currentPeriodStatus', 'statistics'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'main_responsibility' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contribution_percentage' => 'required|numeric|min:0|max:100',
            'activities' => 'required|array|min:1',
            'activities.*.activity_name' => 'required|string|max:255',
            'activities.*.description' => 'nullable|string',
            'activities.*.reporting_frequency' => 'required|in:daily,weekly,monthly',
        ]);

        $user = Auth::user();
        $totalPercentage = (float)$request->contribution_percentage;

        DB::beginTransaction();
        try {
            $assessment = Assessment::create([
                'employee_id' => $user->id,
                'main_responsibility' => $request->main_responsibility,
                'description' => $request->description,
                'contribution_percentage' => $totalPercentage,
                'status' => 'pending_hod',
            ]);

            // Auto distribute activity contribution equally based on count
            $count = max(1, count($request->activities));
            // Handle rounding so the sum matches exactly the main contribution
            $base = floor(($totalPercentage / $count) * 100) / 100; // 2 decimal base
            $remainder = round($totalPercentage - ($base * $count), 2);

            foreach (array_values($request->activities) as $index => $activityData) {
                $contrib = $base + ($remainder > 0 ? min(0.01, $remainder) : 0);
                $remainder = round($remainder - ($contrib - $base), 2);
                AssessmentActivity::create([
                    'assessment_id' => $assessment->id,
                    'activity_name' => $activityData['activity_name'],
                    'description' => $activityData['description'] ?? null,
                    'reporting_frequency' => $activityData['reporting_frequency'],
                    'contribution_percentage' => round($contrib, 2),
                ]);
            }

            // Send notifications with SMS
            try {
                // Notify employee
                $employeeMessage = "Assessment Submitted: Your responsibility assessment '{$assessment->main_responsibility}' has been submitted and is pending HOD approval.";
                $this->notificationService->notify(
                    $user->id,
                    $employeeMessage,
                    route('modules.hr.assessments'),
                    'Assessment Submitted'
                );

                // Notify HOD with SMS
                if ($user->primary_department_id) {
                    $hodMessage = "New Assessment: Responsibility assessment '{$assessment->main_responsibility}' from {$user->name} requires your approval.";
                    $this->notificationService->notifyHOD(
                        $user->primary_department_id,
                        $hodMessage,
                        route('modules.hr.assessments'),
                        'New Assessment Pending Approval',
                        ['responsibility' => $assessment->main_responsibility, 'staff_name' => $user->name]
                    );
                }
                
                \Log::info('Assessment submitted - SMS notifications sent', [
                    'assessment_id' => $assessment->id,
                    'employee_id' => $user->id,
                    'department_id' => $user->primary_department_id
                ]);
            } catch (\Exception $e) {
                \Log::error('Notification error in store(): ' . $e->getMessage());
            }

            DB::commit();
            
            // Log activity
            ActivityLogService::logCreated($assessment, "Created assessment: {$assessment->main_responsibility}", [
                'main_responsibility' => $assessment->main_responsibility,
                'contribution_percentage' => $assessment->contribution_percentage,
                'activities_count' => count($request->activities),
                'status' => $assessment->status,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Assessment submitted successfully!',
                'id' => $assessment->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function hodApprove(Request $request, Assessment $assessment)
    {
        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // HR and HOD can approve assessments
        if (!$user->hasAnyRole(['HOD', 'HR Officer', 'System Admin'])) {
            abort(403);
        }

        $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'required|string|max:1000',
        ]);

        // System Admin can approve at any level, others must wait for pending_hod
        if (!$isSystemAdmin && $assessment->status !== 'pending_hod') {
            return response()->json(['success' => false, 'message' => 'Assessment is not pending HOD approval']);
        }

        // Check department for HOD (HR and System Admin can approve from any department)
        if ($user->hasRole('HOD') && !$user->hasRole('HR Officer') && !$isSystemAdmin) {
            if (($assessment->employee->primary_department_id ?? null) !== $user->primary_department_id) {
                return response()->json(['success' => false, 'message' => 'You can only approve assessments from your department']);
            }
        }

        $newStatus = $request->decision === 'approve' ? 'approved' : 'rejected';

        $assessment->update([
            'status' => $newStatus,
            'hod_approved_at' => now(),
            'hod_approved_by' => $user->id,
            'hod_comments' => $request->comments,
        ]);

        // Send notifications with SMS
        try {
            $employee = $assessment->employee;
            $approverName = $user->name;
            
            if ($request->decision === 'approve') {
                $approveMessage = "Assessment Approved: Your responsibility assessment '{$assessment->main_responsibility}' has been approved by {$approverName}. You can now start reporting progress.";
                $this->notificationService->notify(
                    $employee->id,
                    $approveMessage,
                    route('modules.hr.assessments'),
                    'Assessment Approved'
                );
                
                \Log::info('Assessment approved - SMS notification sent', [
                    'assessment_id' => $assessment->id,
                    'employee_id' => $employee->id,
                    'approver_id' => $user->id
                ]);
            } else {
                $rejectMessage = "Assessment Rejected: Your responsibility assessment '{$assessment->main_responsibility}' has been rejected by {$approverName}. Please check the comments for details.";
                $this->notificationService->notify(
                    $employee->id,
                    $rejectMessage,
                    route('modules.hr.assessments'),
                    'Assessment Rejected'
                );
                
                \Log::info('Assessment rejected - SMS notification sent', [
                    'assessment_id' => $assessment->id,
                    'employee_id' => $employee->id,
                    'approver_id' => $user->id
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Notification error in hodApprove(): ' . $e->getMessage());
        }

        // Log activity
        ActivityLogService::logAction('assessment_hod_approved', ucfirst($request->decision) . " assessment by HOD", $assessment, [
            'assessment_id' => $assessment->id,
            'employee_id' => $assessment->employee_id,
            'decision' => $request->decision,
            'comments' => $request->comments,
            'approved_by' => $user->name,
        ]);

        return response()->json(['success' => true, 'message' => 'Approval decision submitted']);
    }

    public function submitProgressReport(Request $request, AssessmentActivity $activity)
    {
        $user = Auth::user();

        $request->validate([
            'report_date' => 'required|date',
            'progress_text' => 'required|string|max:5000',
        ]);

        // Check if activity belongs to user
        if ($activity->assessment->employee_id !== $user->id) {
            abort(403);
        }

        // Check if assessment is approved
        if ($activity->assessment->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Assessment must be approved by HOD before submitting progress reports'
            ], 422);
        }

        // Check frequency constraints
        $reportDate = Carbon::parse($request->report_date);
        $today = Carbon::today();
        
        // Check if report already exists for this period (check all statuses, not just approved)
        $existingReport = AssessmentProgressReport::where('activity_id', $activity->id)
            ->where(function($query) use ($reportDate, $activity) {
                if ($activity->reporting_frequency === 'daily') {
                    $query->whereDate('report_date', $reportDate->toDateString());
                } elseif ($activity->reporting_frequency === 'weekly') {
                    $startOfWeek = $reportDate->copy()->startOfWeek();
                    $endOfWeek = $reportDate->copy()->endOfWeek();
                    $query->whereBetween('report_date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()]);
                } elseif ($activity->reporting_frequency === 'monthly') {
                    $query->whereYear('report_date', $reportDate->year)
                          ->whereMonth('report_date', $reportDate->month);
                } elseif ($activity->reporting_frequency === 'quarterly') {
                    $quarter = ceil($reportDate->month / 3);
                    $startOfQuarter = Carbon::create($reportDate->year, ($quarter - 1) * 3 + 1, 1);
                    $endOfQuarter = $startOfQuarter->copy()->endOfQuarter();
                    $query->whereBetween('report_date', [$startOfQuarter->toDateString(), $endOfQuarter->toDateString()]);
                }
            })
            ->first();

        if ($existingReport) {
            $frequencyLabel = ucfirst($activity->reporting_frequency);
            return response()->json([
                'success' => false,
                'message' => "You have already submitted a {$frequencyLabel} report for this period. Only one submission is allowed per period."
            ], 422);
        }

        // No date window restrictions - allow submission at any time
        // Only restriction is one submission per period (already checked above)

        DB::beginTransaction();
        try {
            $report = AssessmentProgressReport::create([
                'activity_id' => $activity->id,
                'report_date' => $reportDate,
                'progress_text' => $request->progress_text,
                'status' => 'pending_approval',
            ]);

            // Send notifications with SMS
            try {
                // Notify employee
                $employeeMessage = "Progress Report Submitted: You have submitted a progress report for '{$activity->activity_name}'. It is pending HOD approval.";
                $this->notificationService->notify(
                    $user->id,
                    $employeeMessage,
                    route('modules.hr.assessments'),
                    'Progress Report Submitted'
                );

                // Notify HOD with SMS
                if (($activity->assessment->employee->primary_department_id ?? null)) {
                    $hodMessage = "New Progress Report: Progress report for '{$activity->activity_name}' from {$user->name} requires your approval.";
                    $this->notificationService->notifyHOD(
                        $activity->assessment->employee->primary_department_id,
                        $hodMessage,
                        route('modules.hr.assessments'),
                        'New Progress Report',
                        ['activity' => $activity->activity_name, 'staff_name' => $user->name]
                    );
                }
                
                \Log::info('Progress report submitted - SMS notifications sent', [
                    'report_id' => $report->id,
                    'activity_id' => $activity->id,
                    'employee_id' => $user->id,
                    'department_id' => $activity->assessment->employee->primary_department_id
                ]);
            } catch (\Exception $e) {
                \Log::error('Notification error in submitProgressReport(): ' . $e->getMessage());
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Progress report submitted successfully!',
                'id' => $report->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveProgressReport(Request $request, AssessmentProgressReport $report)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HOD', 'System Admin'])) {
            abort(403);
        }

        $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'nullable|string|max:1000',
        ]);

        if ($report->status !== 'pending_approval') {
            return response()->json(['success' => false, 'message' => 'Report is not pending approval']);
        }

        // Check department
        if ($user->hasRole('HOD') && !$user->hasRole('System Admin')) {
            if (($report->activity->assessment->employee->primary_department_id ?? null) !== $user->primary_department_id) {
                return response()->json(['success' => false, 'message' => 'You can only approve reports from your department']);
            }
        }

        $newStatus = $request->decision === 'approve' ? 'approved' : 'rejected';

        $report->update([
            'status' => $newStatus,
            'hod_approved_at' => now(),
            'hod_approved_by' => $user->id,
            'hod_comments' => $request->comments,
        ]);

        // Send notifications with SMS
        try {
            $employee = $report->activity->assessment->employee;
            $activityName = $report->activity->activity_name;
            $approverName = $user->name;

            if ($request->decision === 'approve') {
                $approveMessage = "Progress Report Approved: Your progress report for '{$activityName}' has been approved by {$approverName}.";
                $this->notificationService->notify(
                    $employee->id,
                    $approveMessage,
                    route('modules.hr.assessments'),
                    'Progress Report Approved'
                );
                
                \Log::info('Progress report approved - SMS notification sent', [
                    'report_id' => $report->id,
                    'activity_name' => $activityName,
                    'employee_id' => $employee->id,
                    'approver_id' => $user->id
                ]);
            } else {
                $rejectMessage = "Progress Report Rejected: Your progress report for '{$activityName}' has been rejected by {$approverName}. Please check the comments for details.";
                $this->notificationService->notify(
                    $employee->id,
                    $rejectMessage,
                    route('modules.hr.assessments'),
                    'Progress Report Rejected'
                );
                
                \Log::info('Progress report rejected - SMS notification sent', [
                    'report_id' => $report->id,
                    'activity_name' => $activityName,
                    'employee_id' => $employee->id,
                    'approver_id' => $user->id
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Notification error in approveProgressReport(): ' . $e->getMessage());
        }

        // Log activity
        ActivityLogService::logAction('assessment_progress_report_approved', ucfirst($request->decision) . " progress report for assessment activity", $report, [
            'report_id' => $report->id,
            'activity_name' => $report->activity->activity_name ?? 'N/A',
            'decision' => $request->decision,
            'comments' => $request->comments,
            'approved_by' => $user->name,
        ]);

        return response()->json(['success' => true, 'message' => 'Approval decision submitted']);
    }

    public function getActivityReports(Request $request, AssessmentActivity $activity)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['HOD','HR Officer','System Admin','CEO','Director']);

        // Authorization: staff can view own; managers can view department/all
        $ownerId = $activity->assessment->employee_id;
        if (!$isManager && $ownerId !== $user->id) {
            abort(403);
        }
        if ($user->hasRole('HOD') && !$user->hasRole('System Admin') && !$user->hasRole('HR Officer')) {
            if (($activity->assessment->employee->primary_department_id ?? null) !== $user->primary_department_id) {
                abort(403);
            }
        }

        $year = (int) $request->input('year', date('Y'));
        $reports = AssessmentProgressReport::with('hodApprover')
            ->where('activity_id', $activity->id)
            ->whereYear('report_date', $year)
            ->orderBy('report_date','desc')
            ->limit(100)
            ->get(['id','report_date','progress_text','status','hod_approved_at','hod_approved_by','hod_comments']);

        return response()->json([
            'success' => true,
            'activity' => [
                'id' => $activity->id,
                'name' => $activity->activity_name,
                'frequency' => $activity->reporting_frequency,
            ],
            'year' => $year,
            'reports' => $reports->map(function($r){
                return [
                    'id' => $r->id,
                    'date' => optional($r->report_date)->toDateString(),
                    'text' => $r->progress_text,
                    'status' => $r->status,
                    'approved_at' => optional($r->hod_approved_at)->toDateTimeString(),
                    'approver' => optional($r->hodApprover)->name,
                    'comments' => $r->hod_comments,
                ];
            })
        ]);
    }

    public function getAssessmentDetails(Request $request, Assessment $assessment)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['HOD','HR Officer','System Admin','CEO','Director']);

        // Authorization: owner or manager (and HOD only for own department)
        if (!$isManager && $assessment->employee_id !== $user->id) {
            abort(403);
        }
        if ($user->hasRole('HOD') && !$user->hasRole('System Admin') && !$user->hasRole('HR Officer')) {
            if (($assessment->employee->primary_department_id ?? null) !== $user->primary_department_id) {
                abort(403);
            }
        }

        $year = (int) $request->input('year', date('Y'));
        $assessment->load(['employee','activities' => function($q) use ($year) {
            $q->with(['progressReports' => function($qr) use ($year) {
                $qr->whereYear('report_date', $year)->orderBy('report_date','desc');
            }]);
        }]);

        $data = [
            'id' => $assessment->id,
            'employee' => optional($assessment->employee)->only(['id','name','email']),
            'main_responsibility' => $assessment->main_responsibility,
            'description' => $assessment->description,
            'status' => $assessment->status,
            'contribution' => (float)$assessment->contribution_percentage,
            'activities' => $assessment->activities->map(function($a) {
                return [
                    'id' => $a->id,
                    'name' => $a->activity_name,
                    'frequency' => $a->reporting_frequency,
                    'contribution' => (float)$a->contribution_percentage,
                    'reports' => $a->progressReports->map(function($r){
                        return [
                            'id' => $r->id,
                            'date' => optional($r->report_date)->toDateString(),
                            'text' => $r->progress_text,
                            'status' => $r->status,
                        ];
                    })
                ];
            }),
        ];

        return response()->json(['success' => true, 'year' => $year, 'assessment' => $data]);
    }

    public function calculatePerformance(Request $request, $employeeId = null)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
            }

            $isHR = $user->hasRole('HR Officer');
            $isCEO = $user->hasRole('CEO');
            $isAdmin = $user->hasRole('System Admin');

            if (!$isHR && !$isCEO && !$isAdmin) {
                $employeeId = $user->id;
            } elseif (!$employeeId) {
                $employeeId = $user->id;
            }

            // Validate employeeId
            if (!$employeeId) {
                return response()->json(['success' => false, 'message' => 'Employee ID is required'], 400);
            }

            $year = (int)$request->input('year', date('Y'));
            if ($year < 2000 || $year > 2100) {
                $year = date('Y');
            }

            $assessments = Assessment::where('employee_id', $employeeId)
                ->where('status', 'approved')
                ->with(['activities' => function($query) {
                    $query->whereNotNull('reporting_frequency');
                }, 'activities.progressReports' => function($query) use ($year) {
                    $query->where('status', 'approved')
                          ->whereYear('report_date', $year);
                }])
                ->get();

            $totalPerformance = 0;
            $performanceDetails = [];

            foreach ($assessments as $assessment) {
                if (!$assessment->activities || $assessment->activities->isEmpty()) {
                    continue;
                }

                $responsibilityPerformance = 0;
                $activityDetails = [];

                foreach ($assessment->activities as $activity) {
                    if (!$activity) {
                        continue;
                    }

                    $reports = $activity->progressReports ?? collect();
                    $expectedReports = 0;

                    try {
                        if ($activity->reporting_frequency === 'daily') {
                            $startDate = Carbon::create($year, 1, 1);
                            $endDate = Carbon::create($year, 12, 31);
                            $expectedReports = $startDate->diffInDays($endDate) + 1;
                        } elseif ($activity->reporting_frequency === 'weekly') {
                            $expectedReports = 52;
                        } elseif ($activity->reporting_frequency === 'monthly') {
                            $expectedReports = 12;
                        } else {
                            // Unknown frequency, skip
                            continue;
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Error calculating expected reports for activity ' . $activity->id . ': ' . $e->getMessage());
                        continue;
                    }

                    $onTime = 0;
                    foreach ($reports as $rep) {
                        if (!$rep) {
                            continue;
                        }

                        try {
                            $createdAt = $rep->created_at ? Carbon::parse($rep->created_at) : null;
                            $repDate = $rep->report_date ? Carbon::parse($rep->report_date) : null;
                            
                            if (!$createdAt || !$repDate) {
                                continue;
                            }

                            if ($activity->reporting_frequency === 'daily') {
                                if ($createdAt->isSameDay($repDate)) {
                                    $onTime++;
                                }
                            } elseif ($activity->reporting_frequency === 'weekly') {
                                if ($createdAt->isSameWeek($repDate)) {
                                    $onTime++;
                                }
                            } else { // monthly
                                if ($createdAt->isSameMonth($repDate)) {
                                    $onTime++;
                                }
                            }
                        } catch (\Exception $e) {
                            \Log::warning('Error processing report ' . $rep->id . ': ' . $e->getMessage());
                            continue;
                        }
                    }

                    $activityScore = $expectedReports > 0 ? min(100, ($onTime / $expectedReports) * 100) : 0;

                    $submittedReports = $reports->count();
                    $contribution = (float)($activity->contribution_percentage ?? 0);
                    
                    $activityDetails[] = [
                        'activity' => $activity->activity_name ?? 'Unknown',
                        'frequency' => $activity->reporting_frequency ?? 'unknown',
                        'expected' => $expectedReports,
                        'submitted' => $submittedReports,
                        'score' => round($activityScore, 2),
                        'contribution' => $contribution,
                    ];

                    $responsibilityPerformance += ($activityScore * $contribution / 100);
                }

                $assessmentContribution = (float)($assessment->contribution_percentage ?? 0);
                
                $performanceDetails[] = [
                    'responsibility' => $assessment->main_responsibility ?? 'Unknown',
                    'contribution' => $assessmentContribution,
                    'performance' => round($responsibilityPerformance, 2),
                    'activities' => $activityDetails,
                ];

                $totalPerformance += ($responsibilityPerformance * $assessmentContribution / 100);
            }

            return response()->json([
                'success' => true,
                'year' => $year,
                'total_performance' => round($totalPerformance, 2),
                'details' => $performanceDetails,
            ]);
        } catch (\Throwable $e) {
            \Log::error('calculatePerformance error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'employeeId' => $employeeId ?? 'null',
                'year' => $request->input('year', 'null')
            ]);
            return response()->json([
                'success' => false, 
                'message' => 'Unable to compute performance: ' . $e->getMessage()
            ], 200);
        }
    }

    public function exportPerformance(Request $request, $employeeId)
    {
        $user = Auth::user();
        $isHR = $user->hasRole('HR Officer');
        $isCEO = $user->hasRole('CEO') || $user->hasRole('Director');
        $isAdmin = $user->hasRole('System Admin');

        if (!$isHR && !$isCEO && !$isAdmin && (int)$employeeId !== (int)$user->id) {
            abort(403);
        }

        $year = (int)($request->input('year', date('Y')));
        if ($year < 2000 || $year > 2100) {
            $year = date('Y');
        }

        $assessments = Assessment::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->with(['activities' => function($query) {
                $query->whereNotNull('reporting_frequency');
            }, 'activities.progressReports' => function($q) use ($year) {
                $q->whereYear('report_date', $year);
            }, 'employee'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Compute performance details (same logic as calculatePerformance)
        $totalPerformance = 0;
        $performanceDetails = [];

        foreach ($assessments as $assessment) {
            if (!$assessment->activities || $assessment->activities->isEmpty()) {
                continue;
            }

            $responsibilityPerformance = 0;
            $activityDetails = [];
            
            foreach ($assessment->activities as $activity) {
                if (!$activity) {
                    continue;
                }

                $reports = $activity->progressReports->where('status', 'approved') ?? collect();
                $expected = 0;
                
                try {
                    if ($activity->reporting_frequency === 'daily') {
                        $startDate = Carbon::create($year, 1, 1);
                        $endDate = Carbon::create($year, 12, 31);
                        $expected = $startDate->diffInDays($endDate) + 1;
                    } elseif ($activity->reporting_frequency === 'weekly') {
                        $expected = 52;
                    } elseif ($activity->reporting_frequency === 'monthly') {
                        $expected = 12;
                    } else {
                        // Unknown frequency, skip
                        continue;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error calculating expected reports for activity ' . $activity->id . ': ' . $e->getMessage());
                    continue;
                }

                $onTime = 0;
                foreach ($reports as $rep) {
                    if (!$rep) {
                        continue;
                    }

                    try {
                        $createdAt = $rep->created_at ? Carbon::parse($rep->created_at) : null;
                        $repDate = $rep->report_date ? Carbon::parse($rep->report_date) : null;
                        
                        if (!$createdAt || !$repDate) {
                            continue;
                        }

                        if ($activity->reporting_frequency === 'daily') {
                            if ($createdAt->isSameDay($repDate)) {
                                $onTime++;
                            }
                        } elseif ($activity->reporting_frequency === 'weekly') {
                            if ($createdAt->isSameWeek($repDate)) {
                                $onTime++;
                            }
                        } else { // monthly
                            if ($createdAt->isSameMonth($repDate)) {
                                $onTime++;
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Error processing report ' . $rep->id . ': ' . $e->getMessage());
                        continue;
                    }
                }
                
                $submitted = $reports->count();
                $score = $expected > 0 ? min(100, ($onTime / $expected) * 100) : 0;
                $contribution = (float)($activity->contribution_percentage ?? 0);
                
                $activityDetails[] = [
                    'name' => $activity->activity_name ?? 'Unknown',
                    'frequency' => $activity->reporting_frequency ?? 'unknown',
                    'expected' => $expected,
                    'submitted' => $submitted,
                    'score' => round($score, 2),
                    'contribution' => $contribution,
                ];
                $responsibilityPerformance += ($score * $contribution / 100);
            }
            
            $assessmentContribution = (float)($assessment->contribution_percentage ?? 0);
            
            $performanceDetails[] = [
                'responsibility' => $assessment->main_responsibility ?? 'Unknown',
                'contribution' => $assessmentContribution,
                'performance' => round($responsibilityPerformance, 2),
                'activities' => $activityDetails,
            ];
            $totalPerformance += ($responsibilityPerformance * $assessmentContribution / 100);
        }

        $data = [
            'employee' => optional($assessments->first())->employee,
            'year' => $year,
            'assessments' => $assessments,
            'total_performance' => round($totalPerformance, 2),
            'performance_details' => $performanceDetails,
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('modules.hr.assessments-pdf', $data);
        $pdf->setPaper('A4', 'portrait');
        $fileName = 'Performance_Report_'.$employeeId.'_'.$year.'.pdf';
        return $pdf->stream($fileName);
    }

    /**
     * Update assessment (Admin/HR only)
     */
    public function update(Request $request, Assessment $assessment)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer'])) {
            abort(403, 'Only System Admins and HR Officers can update assessments');
        }

        $request->validate([
            'main_responsibility' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contribution_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'nullable|in:pending_hod,approved,rejected',
        ]);

        try {
            $oldValues = array_intersect_key($assessment->getOriginal(), $assessment->getChanges());
            $assessment->update([
                'main_responsibility' => $request->main_responsibility,
                'description' => $request->description,
                'contribution_percentage' => $request->contribution_percentage,
                'status' => $request->status ?? $assessment->status,
            ]);

            // Log activity
            ActivityLogService::logUpdated($assessment, $oldValues, $assessment->getChanges(), "Updated assessment: {$assessment->main_responsibility}", [
                'assessment_id' => $assessment->id,
                'employee_id' => $assessment->employee_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Assessment updated successfully',
                'assessment' => $assessment->load(['employee', 'activities', 'hodApprover'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete assessment (Admin only)
     */
    public function destroy(Assessment $assessment)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('System Admin')) {
            abort(403, 'Only System Admins can delete assessments');
        }

        try {
            // Delete related activities and reports
            DB::beginTransaction();
            
            foreach ($assessment->activities as $activity) {
                $activity->progressReports()->delete();
            }
            $assessment->activities()->delete();
            $assessment->delete();
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assessment deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete assessment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update activity (Admin/HR only)
     */
    public function updateActivity(Request $request, AssessmentActivity $activity)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer'])) {
            abort(403, 'Only System Admins and HR Officers can update activities');
        }

        $request->validate([
            'activity_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'reporting_frequency' => 'required|in:daily,weekly,monthly,quarterly',
            'contribution_percentage' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $oldValues = array_intersect_key($activity->getOriginal(), $activity->getChanges());
            $activity->update([
                'activity_name' => $request->activity_name,
                'description' => $request->description,
                'reporting_frequency' => $request->reporting_frequency,
                'contribution_percentage' => $request->contribution_percentage,
            ]);

            // Log activity
            ActivityLogService::logUpdated($activity, $oldValues, $activity->getChanges(), "Updated assessment activity: {$activity->activity_name}", [
                'activity_id' => $activity->id,
                'assessment_id' => $activity->assessment_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully',
                'activity' => $activity->load('assessment')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete activity (Admin only)
     */
    public function destroyActivity(AssessmentActivity $activity)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('System Admin')) {
            abort(403, 'Only System Admins can delete activities');
        }

        try {
            DB::beginTransaction();
            $activity->progressReports()->delete();
            $activity->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Activity deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete progress report (Admin only)
     */
    public function destroyProgressReport(AssessmentProgressReport $report)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('System Admin')) {
            abort(403, 'Only System Admins can delete progress reports');
        }

        try {
            $report->delete();

            return response()->json([
                'success' => true,
                'message' => 'Progress report deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete progress report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get comprehensive assessment data for admin
     */
    public function getComprehensiveData(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer'])) {
            abort(403);
        }

        $year = (int) $request->input('year', date('Y'));
        $departmentId = $request->input('department_id');
        $status = $request->input('status');

        $query = Assessment::with([
            'employee.primaryDepartment',
            'activities.progressReports' => function($q) use ($year) {
                $q->whereYear('report_date', $year)->with('hodApprover');
            },
            'hodApprover'
        ]);

        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $assessments = $query->orderBy('created_at', 'desc')->get();

        // Calculate performance metrics
        $metrics = [];
        foreach ($assessments as $assessment) {
            $totalReports = 0;
            $approvedReports = 0;
            $pendingReports = 0;
            
            foreach ($assessment->activities as $activity) {
                $reports = $activity->progressReports->filter(function($r) use ($year) {
                    return $r->report_date && \Carbon\Carbon::parse($r->report_date)->year == $year;
                });
                $totalReports += $reports->count();
                $approvedReports += $reports->where('status', 'approved')->count();
                $pendingReports += $reports->where('status', 'pending_approval')->count();
            }

            $rejectedCount = 0;
            foreach ($assessment->activities as $activity) {
                $reports = $activity->progressReports->filter(function($r) use ($year) {
                    return $r->report_date && \Carbon\Carbon::parse($r->report_date)->year == $year;
                });
                $rejectedCount += $reports->where('status', 'rejected')->count();
            }

            $metrics[$assessment->id] = [
                'total_reports' => $totalReports,
                'approved_reports' => $approvedReports,
                'pending_reports' => $pendingReports,
                'rejection_rate' => $totalReports > 0 
                    ? round(($rejectedCount / $totalReports) * 100, 2)
                    : 0,
            ];
        }

        return response()->json([
            'success' => true,
            'year' => $year,
            'assessments' => $assessments,
            'metrics' => $metrics,
        ]);
    }

    /**
     * Show assessment details page
     */
    public function show(Assessment $assessment)
    {
        $user = Auth::user();
        $isHR = $user->hasRole('HR Officer');
        $isHOD = $user->hasRole('HOD');
        $isCEO = $user->hasRole('CEO');
        $isAdmin = $user->hasRole('System Admin');
        $isManager = $isHR || $isHOD || $isCEO || $isAdmin;
        
        // Authorization check
        $isOwn = $assessment->employee_id == $user->id;
        if (!$isManager && !$isOwn) {
            abort(403);
        }
        
        if ($isHOD && !$isAdmin && !$isHR) {
            if (($assessment->employee->primary_department_id ?? null) !== $user->primary_department_id) {
                abort(403);
            }
        }
        
        // Load relationships
        $assessment->load([
            'employee.primaryDepartment',
            'hodApprover',
            'activities' => function($q) {
                $q->orderBy('created_at', 'asc');
            },
            'activities.progressReports' => function($q) {
                $q->with('hodApprover')->orderBy('report_date', 'desc')->orderBy('created_at', 'desc');
            }
        ]);
        
        // Calculate performance metrics
        $currentYear = date('Y');
        $performanceData = $this->calculatePerformanceMetrics($assessment, $currentYear);
        
        // Get timeline events
        $timeline = $this->getAssessmentTimeline($assessment);
        
        return view('modules.hr.assessment-details', compact(
            'assessment',
            'isHR',
            'isHOD',
            'isCEO',
            'isAdmin',
            'isManager',
            'isOwn',
            'performanceData',
            'timeline',
            'currentYear'
        ));
    }

    /**
     * Get calendar events for FullCalendar
     */
    public function getCalendarEvents(Request $request)
    {
        $user = Auth::user();
        $isHR = $user->hasRole('HR Officer');
        $isHOD = $user->hasRole('HOD');
        $isCEO = $user->hasRole('CEO');
        $isAdmin = $user->hasRole('System Admin');
        $isManager = $isHR || $isHOD || $isCEO || $isAdmin;
        
        $start = $request->input('start');
        $end = $request->input('end');
        
        $query = AssessmentProgressReport::with([
            'activity.assessment.employee',
            'hodApprover'
        ]);
        
        if (!$isManager) {
            $query->whereHas('activity.assessment', function($q) use ($user) {
                $q->where('employee_id', $user->id);
            });
        } elseif ($isHOD && !$isAdmin && !$isHR) {
            $query->whereHas('activity.assessment.employee', function($q) use ($user) {
                $q->where('primary_department_id', $user->primary_department_id);
            });
        }
        
        if ($start && $end) {
            $query->whereBetween('report_date', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ]);
        }
        
        $reports = $query->get();
        
        $events = [];
        foreach ($reports as $report) {
            $statusColor = $report->status === 'approved' ? '#28a745' : 
                          ($report->status === 'pending_approval' ? '#ffc107' : '#dc3545');
            
            $events[] = [
                'id' => 'report_' . $report->id,
                'title' => $report->activity->activity_name . ' - ' . ucfirst($report->status),
                'start' => $report->report_date->format('Y-m-d'),
                'color' => $statusColor,
                'extendedProps' => [
                    'type' => 'progress_report',
                    'report_id' => $report->id,
                    'activity' => $report->activity->activity_name,
                    'employee' => $report->activity->assessment->employee->name ?? 'N/A',
                    'status' => $report->status,
                ]
            ];
        }
        
        // Add assessment creation dates
        $assessmentQuery = Assessment::with('employee');
        if (!$isManager) {
            $assessmentQuery->where('employee_id', $user->id);
        } elseif ($isHOD && !$isAdmin && !$isHR) {
            $assessmentQuery->whereHas('employee', function($q) use ($user) {
                $q->where('primary_department_id', $user->primary_department_id);
            });
        }
        
        if ($start && $end) {
            $assessmentQuery->whereBetween('created_at', [
                Carbon::parse($start)->startOfDay(),
                Carbon::parse($end)->endOfDay()
            ]);
        }
        
        $assessments = $assessmentQuery->get();
        foreach ($assessments as $assessment) {
            $statusColor = $assessment->status === 'approved' ? '#28a745' : 
                          ($assessment->status === 'pending_hod' ? '#ffc107' : '#dc3545');
            
            $events[] = [
                'id' => 'assessment_' . $assessment->id,
                'title' => $assessment->main_responsibility . ' - ' . ucfirst(str_replace('_', ' ', $assessment->status)),
                'start' => $assessment->created_at->format('Y-m-d'),
                'color' => $statusColor,
                'extendedProps' => [
                    'type' => 'assessment',
                    'assessment_id' => $assessment->id,
                    'employee' => $assessment->employee->name ?? 'N/A',
                    'status' => $assessment->status,
                ]
            ];
        }
        
        return response()->json($events);
    }

    /**
     * Get analytics data for charts
     */
    public function getAnalytics(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD'])) {
            abort(403);
        }
        
        $year = (int) $request->input('year', date('Y'));
        $departmentId = $request->input('department_id');
        
        $query = Assessment::with([
            'employee.primaryDepartment',
            'activities.progressReports' => function($q) use ($year) {
                $q->whereYear('report_date', $year);
            }
        ]);
        
        if ($user->hasRole('HOD') && !$user->hasRole('System Admin') && !$user->hasRole('HR Officer')) {
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('primary_department_id', $user->primary_department_id);
            });
        }
        
        if ($departmentId) {
            $query->whereHas('employee', function($q) use ($departmentId) {
                $q->where('primary_department_id', $departmentId);
            });
        }
        
        $assessments = $query->get();
        
        // Status distribution
        $statusData = [
            'approved' => $assessments->where('status', 'approved')->count(),
            'pending_hod' => $assessments->where('status', 'pending_hod')->count(),
            'rejected' => $assessments->where('status', 'rejected')->count(),
        ];
        
        // Monthly trend
        $monthlyTrend = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyTrend[$i] = [
                'assessments' => $assessments->filter(function($a) use ($i) {
                    return $a->created_at->month == $i;
                })->count(),
                'reports' => $assessments->sum(function($a) use ($i, $year) {
                    return $a->activities->sum(function($act) use ($i, $year) {
                        return $act->progressReports->filter(function($r) use ($i, $year) {
                            return $r->report_date && $r->report_date->month == $i && $r->report_date->year == $year;
                        })->count();
                    });
                }),
            ];
        }
        
        // Department distribution
        $departmentData = [];
        foreach ($assessments as $assessment) {
            $deptName = $assessment->employee->primaryDepartment->name ?? 'No Department';
            if (!isset($departmentData[$deptName])) {
                $departmentData[$deptName] = 0;
            }
            $departmentData[$deptName]++;
        }
        
        // Top performers
        $topPerformers = [];
        foreach ($assessments->where('status', 'approved') as $assessment) {
            $employeeName = $assessment->employee->name ?? 'Unknown';
            $totalReports = $assessment->activities->sum(function($act) use ($year) {
                return $act->progressReports->where('status', 'approved')->count();
            });
            if (!isset($topPerformers[$employeeName])) {
                $topPerformers[$employeeName] = 0;
            }
            $topPerformers[$employeeName] += $totalReports;
        }
        arsort($topPerformers);
        $topPerformers = array_slice($topPerformers, 0, 10, true);
        
        // Report status distribution
        $reportStatusData = [
            'approved' => 0,
            'pending_approval' => 0,
            'rejected' => 0,
        ];
        foreach ($assessments as $assessment) {
            foreach ($assessment->activities as $activity) {
                foreach ($activity->progressReports as $report) {
                    if (isset($reportStatusData[$report->status])) {
                        $reportStatusData[$report->status]++;
                    }
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'year' => $year,
            'status_distribution' => $statusData,
            'monthly_trend' => $monthlyTrend,
            'department_distribution' => $departmentData,
            'top_performers' => $topPerformers,
            'report_status_distribution' => $reportStatusData,
        ]);
    }

    /**
     * Calculate performance metrics for an assessment
     */
    private function calculatePerformanceMetrics(Assessment $assessment, $year)
    {
        $totalReports = 0;
        $approvedReports = 0;
        $pendingReports = 0;
        $rejectedReports = 0;
        
        foreach ($assessment->activities as $activity) {
            $reports = $activity->progressReports->filter(function($r) use ($year) {
                return $r->report_date && $r->report_date->year == $year;
            });
            
            $totalReports += $reports->count();
            $approvedReports += $reports->where('status', 'approved')->count();
            $pendingReports += $reports->where('status', 'pending_approval')->count();
            $rejectedReports += $reports->where('status', 'rejected')->count();
        }
        
        $approvalRate = $totalReports > 0 ? round(($approvedReports / $totalReports) * 100, 2) : 0;
        $rejectionRate = $totalReports > 0 ? round(($rejectedReports / $totalReports) * 100, 2) : 0;
        
        return [
            'total_reports' => $totalReports,
            'approved_reports' => $approvedReports,
            'pending_reports' => $pendingReports,
            'rejected_reports' => $rejectedReports,
            'approval_rate' => $approvalRate,
            'rejection_rate' => $rejectionRate,
            'activities_count' => $assessment->activities->count(),
        ];
    }

    /**
     * Get assessment timeline
     */
    private function getAssessmentTimeline(Assessment $assessment)
    {
        $timeline = [];
        
        // Created
        $timeline[] = [
            'event' => 'Assessment Created',
            'date' => $assessment->created_at,
            'user' => $assessment->employee->name ?? 'Unknown',
            'description' => "Created assessment: {$assessment->main_responsibility}",
            'icon' => 'bx-file',
            'color' => 'primary',
        ];
        
        // HOD Approval/Rejection
        if ($assessment->hod_approved_at) {
            $timeline[] = [
                'event' => $assessment->status === 'approved' ? 'Assessment Approved' : 'Assessment Rejected',
                'date' => $assessment->hod_approved_at,
                'user' => $assessment->hodApprover->name ?? 'Unknown',
                'description' => $assessment->hod_comments ?? '',
                'icon' => $assessment->status === 'approved' ? 'bx-check-circle' : 'bx-x-circle',
                'color' => $assessment->status === 'approved' ? 'success' : 'danger',
            ];
        }
        
        // Progress reports (most recent first)
        $reports = $assessment->activities->flatMap(function($activity) {
            return $activity->progressReports;
        })->sortByDesc('created_at')->take(10);
        
        foreach ($reports as $report) {
            $statusLabel = ucfirst(str_replace('_', ' ', $report->status));
            $timeline[] = [
                'event' => "Progress Report: {$statusLabel}",
                'date' => $report->created_at,
                'user' => $assessment->employee->name ?? 'Unknown',
                'description' => "Report for {$report->activity->activity_name} - " . Str::limit($report->progress_text, 100),
                'icon' => 'bx-file',
                'color' => $report->status === 'approved' ? 'success' : ($report->status === 'pending_approval' ? 'warning' : 'danger'),
            ];
            
            if ($report->hod_approved_at) {
                $timeline[] = [
                    'event' => "Report {$statusLabel}",
                    'date' => $report->hod_approved_at,
                    'user' => $report->hodApprover->name ?? 'Unknown',
                    'description' => $report->hod_comments ?? '',
                    'icon' => $report->status === 'approved' ? 'bx-check-circle' : 'bx-x-circle',
                    'color' => $report->status === 'approved' ? 'success' : 'danger',
                ];
            }
        }
        
        // Sort by date descending
        usort($timeline, function($a, $b) {
            return $b['date']->timestamp - $a['date']->timestamp;
        });
        
        return $timeline;
    }

    /**
     * Show create assessment page
     */
    public function create()
    {
        return view('modules.hr.assessments-create');
    }

    /**
     * Show edit assessment page
     */
    public function edit(Assessment $assessment)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer'])) {
            abort(403);
        }
        
        $assessment->load(['employee', 'activities']);
        
        return view('modules.hr.assessments-edit', compact('assessment'));
    }

    /**
     * Show approve assessment page
     */
    public function approvePage(Assessment $assessment)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HOD', 'HR Officer', 'System Admin'])) {
            abort(403);
        }
        
        if ($assessment->status !== 'pending_hod') {
            return redirect()->route('assessments.show', $assessment->id)
                ->with('error', 'Assessment is not pending approval');
        }
        
        return view('modules.hr.assessments-approve', compact('assessment'));
    }

    /**
     * Show reject assessment page
     */
    public function rejectPage(Assessment $assessment)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HOD', 'HR Officer', 'System Admin'])) {
            abort(403);
        }
        
        if ($assessment->status !== 'pending_hod') {
            return redirect()->route('assessments.show', $assessment->id)
                ->with('error', 'Assessment is not pending approval');
        }
        
        return view('modules.hr.assessments-reject', compact('assessment'));
    }

    /**
     * Show activity reports page
     */
    public function activityReportsPage(AssessmentActivity $activity)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['HOD','HR Officer','System Admin','CEO','Director']);
        
        $ownerId = $activity->assessment->employee_id;
        if (!$isManager && $ownerId !== $user->id) {
            abort(403);
        }
        
        if ($user->hasRole('HOD') && !$user->hasRole('System Admin') && !$user->hasRole('HR Officer')) {
            if (($activity->assessment->employee->primary_department_id ?? null) !== $user->primary_department_id) {
                abort(403);
            }
        }
        
        $activity->load(['assessment.employee', 'progressReports.hodApprover']);
        
        return view('modules.hr.assessments-activity-reports', compact('activity'));
    }

    /**
     * Show create progress report page
     */
    public function progressCreatePage(AssessmentActivity $activity)
    {
        $user = Auth::user();
        
        if ($activity->assessment->employee_id !== $user->id) {
            abort(403);
        }
        
        if ($activity->assessment->status !== 'approved') {
            return redirect()->route('modules.hr.assessments')
                ->with('error', 'Assessment must be approved before submitting progress reports');
        }
        
        return view('modules.hr.assessments-progress-create', compact('activity'));
    }

    /**
     * Show analytics page
     */
    public function analyticsPage()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD'])) {
            abort(403);
        }
        
        return view('modules.hr.assessments-analytics');
    }
}

