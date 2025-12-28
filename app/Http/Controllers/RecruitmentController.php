<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentJob;
use App\Models\JobApplication;
use App\Models\ApplicationDocument;
use App\Models\ApplicationEvaluation;
use App\Models\ApplicationHistory;
use App\Models\InterviewSchedule;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class RecruitmentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the recruitment management page
     */
    public function index()
    {
        $user = Auth::user();
        
        // Check permissions - System Admin can approve all jobs
        $canCreateJobs = $user->hasAnyRole(['HR Officer', 'System Admin']);
        $canApproveJobs = $user->hasAnyRole(['CEO', 'Director', 'System Admin']);
        $canManageApplications = $user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director']);
        $canShortlist = $user->hasAnyRole(['HR Officer', 'System Admin']);
        $canEditPendingJobs = $user->hasAnyRole(['HR Officer', 'System Admin']);
        $isSystemAdmin = $user->hasRole('System Admin');

        // Auto-close jobs with passed deadlines
        RecruitmentJob::where('application_deadline', '<', now())
            ->where('status', 'Active')
            ->update(['status' => 'Closed']);

        // Get all jobs with application counts
        $jobs = RecruitmentJob::withCount('applications')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get pending approval jobs for CEO/System Admin
        $pendingApprovalJobs = collect();
        if ($canApproveJobs) {
            $pendingApprovalJobs = RecruitmentJob::with('creator')
                ->where('status', 'Pending Approval')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Get my pending jobs for editing
        $myPendingJobs = collect();
        if ($canEditPendingJobs) {
            $myPendingJobs = RecruitmentJob::withCount('applications')
                ->where('status', 'Pending Approval')
                ->where('created_by', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Calculate statistics
        $stats = [];
        if ($canManageApplications) {
            $stats['active_vacancies'] = RecruitmentJob::where('status', 'Active')->count();
            $stats['total_applications'] = JobApplication::count();
            $stats['pending_approval'] = $pendingApprovalJobs->count();
            $stats['my_pending_jobs'] = $myPendingJobs->count();
        }

        // Enhanced statistics
        $advancedStats = [];
        if ($canManageApplications) {
            $advancedStats['total_jobs'] = RecruitmentJob::count();
            $advancedStats['active_jobs'] = RecruitmentJob::where('status', 'Active')->count();
            $advancedStats['pending_approval'] = RecruitmentJob::where('status', 'Pending Approval')->count();
            $advancedStats['closed_jobs'] = RecruitmentJob::where('status', 'Closed')->count();
            $advancedStats['rejected_jobs'] = RecruitmentJob::where('status', 'Rejected')->count();
            
            $advancedStats['total_applications'] = JobApplication::count();
            $advancedStats['shortlisted'] = JobApplication::where('status', 'Shortlisted')->count();
            $advancedStats['interviewing'] = JobApplication::where('status', 'Interviewing')->count();
            $advancedStats['offer_extended'] = JobApplication::where('status', 'Offer Extended')->count();
            $advancedStats['hired'] = JobApplication::where('status', 'Hired')->count();
            $advancedStats['rejected_applications'] = JobApplication::where('status', 'Rejected')->count();
            
            // Recent activity
            $advancedStats['recent_applications'] = JobApplication::where('created_at', '>=', now()->subDays(7))->count();
            $advancedStats['upcoming_interviews'] = InterviewSchedule::upcoming()->count();
        }

        return view('modules.hr.recruitment', compact(
            'jobs',
            'pendingApprovalJobs',
            'myPendingJobs',
            'stats',
            'advancedStats',
            'canCreateJobs',
            'canApproveJobs',
            'canManageApplications',
            'canShortlist',
            'canEditPendingJobs',
            'isSystemAdmin'
        ));
    }

    /**
     * Handle AJAX requests
     */
    public function handleRequest(Request $request)
    {
        $action = $request->input('action');
        $user = Auth::user();

        try {
            DB::beginTransaction();

            switch ($action) {
                case 'create_job':
                    return $this->createJob($request, $user);
                case 'edit_job':
                    return $this->editJob($request, $user);
                case 'approve_job':
                    return $this->approveJob($request, $user);
                case 'reject_job':
                    return $this->rejectJob($request, $user);
                case 'get_job_details':
                    return $this->getJobDetails($request, $user);
                case 'get_job_details_for_edit':
                    return $this->getJobDetailsForEdit($request, $user);
                case 'close_job':
                    return $this->closeJob($request, $user);
                case 'get_job_details_and_applications':
                    return $this->getJobDetailsAndApplications($request, $user);
                case 'get_bulk_applications':
                    return $this->getBulkApplications($request, $user);
                case 'get_application_details':
                    return $this->getApplicationDetails($request, $user);
                case 'update_application_status':
                    return $this->updateApplicationStatus($request, $user);
                case 'save_evaluation':
                    return $this->saveEvaluation($request, $user);
                case 'bulk_update_status':
                    return $this->bulkUpdateStatus($request, $user);
                case 'bulk_delete':
                    return $this->bulkDelete($request, $user);
                case 'schedule_interview':
                    return $this->scheduleInterview($request, $user);
                case 'get_interview_schedules':
                    return $this->getInterviewSchedules($request, $user);
                case 'update_interview_status':
                    return $this->updateInterviewStatus($request, $user);
                case 'get_application_history':
                    return $this->getApplicationHistory($request, $user);
                case 'export_jobs_pdf':
                    return $this->exportJobsPdf($request, $user);
                case 'export_applications_excel':
                    return $this->exportApplicationsExcel($request, $user);
                case 'get_analytics':
                    return $this->getAnalytics($request, $user);
                case 'submit_application':
                    return $this->submitApplication($request, $user);
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action requested.'
                    ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new job vacancy
     */
    private function createJob(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: You cannot create job vacancies.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'job_title' => 'required|string|max:255',
            'job_description' => 'required|string|max:2000',
            'qualifications' => 'required|string|max:2000',
            'application_deadline' => 'required|date|after:today',
            'interview_mode' => 'required|array|min:1',
            'interview_mode.*' => 'in:Written,Oral,Practical',
            'required_attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $job = RecruitmentJob::create([
            'job_title' => $request->job_title,
            'job_description' => $request->job_description,
            'qualifications' => $request->qualifications,
            'application_deadline' => $request->application_deadline,
            'required_attachments' => $request->required_attachments ?? [],
            'interview_mode' => $request->interview_mode,
            'status' => 'Pending Approval',
            'created_by' => $user->id,
        ]);

        // Notify approvers
        $approvers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['CEO', 'Director', 'System Admin']);
        })->get();

        foreach ($approvers as $approver) {
            $this->notificationService->notify(
                $approver->id,
                "New job vacancy '{$job->job_title}' requires your approval.",
                route('modules.hr.recruitment'),
                'Job Vacancy Pending Approval'
            );
        }

        // Log activity
        ActivityLogService::logCreated($job, "Created job vacancy: {$job->job_title}", [
            'job_title' => $job->job_title,
            'status' => $job->status,
            'application_deadline' => $job->application_deadline,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Job Vacancy created and submitted for approval.'
        ]);
    }

    /**
     * Edit a pending job
     */
    private function editJob(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: You cannot edit job vacancies.'
            ], 403);
        }

        $job = RecruitmentJob::where('id', $request->job_id)
            ->where(function($q) use ($user) {
                $q->where('created_by', $user->id);
                // System Admin can edit any pending job
                if ($user->hasRole('System Admin')) {
                    $q->orWhere('status', 'Pending Approval');
                }
            })
            ->where('status', 'Pending Approval')
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found or you don\'t have permission to edit it.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'job_title' => 'required|string|max:255',
            'job_description' => 'required|string|max:2000',
            'qualifications' => 'required|string|max:2000',
            'application_deadline' => 'required|date|after:today',
            'interview_mode' => 'required|array|min:1',
            'interview_mode.*' => 'in:Written,Oral,Practical',
            'required_attachments' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $job->update([
            'job_title' => $request->job_title,
            'job_description' => $request->job_description,
            'qualifications' => $request->qualifications,
            'application_deadline' => $request->application_deadline,
            'required_attachments' => $request->required_attachments ?? [],
            'interview_mode' => $request->interview_mode,
        ]);

        // Log activity
        $oldValues = array_intersect_key($job->getOriginal(), $job->getChanges());
        ActivityLogService::logUpdated($job, $oldValues, $job->getChanges(), "Updated job vacancy: {$job->job_title}", [
            'job_title' => $job->job_title,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Job Vacancy updated successfully.'
        ]);
    }

    /**
     * Approve a job - System Admin can also approve
     */
    private function approveJob(Request $request, $user)
    {
        if (!$user->hasAnyRole(['CEO', 'Director', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: You cannot approve jobs.'
            ], 403);
        }

        $job = RecruitmentJob::where('id', $request->job_id)
            ->where('status', 'Pending Approval')
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve job. It may have already been processed.'
            ], 404);
        }

        $job->update([
            'status' => 'Active',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // Notify job creator
        if ($job->creator) {
            $this->notificationService->notify(
                $job->created_by,
                "Your job vacancy '{$job->job_title}' has been approved and is now active.",
                route('modules.hr.recruitment'),
                'Job Vacancy Approved'
            );
        }

        // Log activity
        ActivityLogService::logAction('job_approved', "Approved job vacancy: {$job->job_title}", $job, [
            'job_title' => $job->job_title,
            'approved_by' => $user->name,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Job has been approved and is now active.'
        ]);
    }

    /**
     * Reject a job - System Admin can also reject
     */
    private function rejectJob(Request $request, $user)
    {
        if (!$user->hasAnyRole(['CEO', 'Director', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: You cannot reject jobs.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $job = RecruitmentJob::where('id', $request->job_id)
            ->where('status', 'Pending Approval')
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject job. It may have already been processed.'
            ], 404);
        }

        $job->update([
            'status' => 'Rejected',
            'rejection_reason' => $request->reason,
        ]);

        // Notify job creator
        if ($job->creator) {
            $this->notificationService->notify(
                $job->created_by,
                "Your job vacancy '{$job->job_title}' has been rejected. Reason: {$request->reason}",
                route('modules.hr.recruitment'),
                'Job Vacancy Rejected'
            );
        }

        // Log activity
        ActivityLogService::logAction('job_rejected', "Rejected job vacancy: {$job->job_title}", $job, [
            'job_title' => $job->job_title,
            'rejected_by' => $user->name,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Job has been rejected.'
        ]);
    }

    /**
     * Get job details
     */
    private function getJobDetails(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: You cannot view job details.'
            ], 403);
        }

        $job = RecruitmentJob::with('creator')
            ->find($request->job_id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job vacancy not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'details' => $job
        ]);
    }

    /**
     * Get job details for editing
     */
    private function getJobDetailsForEdit(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: You cannot edit job vacancies.'
            ], 403);
        }

        $job = RecruitmentJob::with('creator')
            ->where('id', $request->job_id)
            ->where(function($q) use ($user) {
                $q->where('created_by', $user->id);
                if ($user->hasRole('System Admin')) {
                    $q->orWhere('status', 'Pending Approval');
                }
            })
            ->where('status', 'Pending Approval')
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job vacancy not found or you don\'t have permission to edit it.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'details' => $job
        ]);
    }

    /**
     * Close a job manually
     */
    private function closeJob(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: You cannot close jobs.'
            ], 403);
        }

        $job = RecruitmentJob::where('id', $request->job_id)
            ->whereIn('status', ['Active', 'Pending Approval'])
            ->first();

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close job. It may have already been closed or processed.'
            ], 404);
        }

        $job->update(['status' => 'Closed']);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Job has been manually closed.'
        ]);
    }

    /**
     * Get job details and applications
     */
    private function getJobDetailsAndApplications(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $job = RecruitmentJob::with('creator')
            ->find($request->job_id);

        if (!$job) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found.'
            ], 404);
        }

        $applications = JobApplication::with('evaluation')
            ->where('job_id', $request->job_id)
            ->orderBy('application_date', 'desc')
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'first_name' => $app->first_name,
                    'last_name' => $app->last_name,
                    'email' => $app->email,
                    'phone' => $app->phone,
                    'status' => $app->status,
                    'application_date' => $app->application_date,
                    'total_score' => $app->evaluation ? $app->evaluation->total_score : null,
                ];
            });

        return response()->json([
            'success' => true,
            'details' => $job,
            'applications' => $applications
        ]);
    }

    /**
     * Get applications for bulk operations
     */
    private function getBulkApplications(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $query = JobApplication::with(['job', 'evaluation']);

        // Filter by job if provided
        if ($request->has('job_id') && $request->job_id) {
            $query->where('job_id', $request->job_id);
        }

        $applications = $query->orderBy('application_date', 'desc')
            ->get()
            ->map(function ($app) {
                return [
                    'id' => $app->id,
                    'first_name' => $app->first_name,
                    'last_name' => $app->last_name,
                    'email' => $app->email,
                    'phone' => $app->phone,
                    'status' => $app->status,
                    'application_date' => $app->application_date->format('Y-m-d'),
                    'job_title' => $app->job ? $app->job->job_title : 'N/A',
                    'total_score' => $app->evaluation ? $app->evaluation->total_score : null,
                ];
            });

        return response()->json([
            'success' => true,
            'applications' => $applications,
            'total' => $applications->count()
        ]);
    }

    /**
     * Get application details
     */
    private function getApplicationDetails(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $application = JobApplication::with(['documents', 'evaluation.interviewer', 'job'])
            ->find($request->application_id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found.'
            ], 404);
        }

        // Get interview schedules
        $interviews = InterviewSchedule::with('interviewer')
            ->where('application_id', $application->id)
            ->orderBy('scheduled_at', 'desc')
            ->get();

        // Get history
        $history = ApplicationHistory::with('changedBy')
            ->where('application_id', $application->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'details' => $application,
            'documents' => $application->documents,
            'evaluation' => $application->evaluation,
            'interviews' => $interviews,
            'history' => $history
        ]);
    }

    /**
     * Update application status
     */
    private function updateApplicationStatus(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: You cannot update application status.'
            ], 403);
        }

        $validStatuses = ['Applied', 'Shortlisted', 'Rejected', 'Interviewing', 'Offer Extended', 'Hired'];
        
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', $validStatuses),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided.'
            ], 422);
        }

        $application = JobApplication::find($request->application_id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found.'
            ], 404);
        }

        $oldStatus = $application->status;
        $updateData = ['status' => $request->status];

        if ($request->status === 'Shortlisted') {
            $updateData['shortlisted_by'] = $user->id;
            $updateData['shortlisted_at'] = now();
        }

        $application->update($updateData);

        // Create history record
        ApplicationHistory::create([
            'application_id' => $application->id,
            'status_from' => $oldStatus,
            'status_to' => $request->status,
            'changed_by' => $user->id,
            'notes' => $request->notes ?? null,
        ]);

        // Log activity
        ActivityLogService::logAction('application_status_changed', "Changed application status from {$oldStatus} to {$request->status}", $application, [
            'application_id' => $application->id,
            'old_status' => $oldStatus,
            'new_status' => $request->status,
            'changed_by' => $user->name,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Application status updated to '{$request->status}'."
        ]);
    }

    /**
     * Save evaluation
     */
    private function saveEvaluation(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: You cannot save evaluations.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'written_score' => 'nullable|numeric|min:0|max:100',
            'practical_score' => 'nullable|numeric|min:0|max:100',
            'oral_score' => 'nullable|numeric|min:0|max:100',
            'comments' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $application = JobApplication::find($request->application_id);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found.'
            ], 404);
        }

        ApplicationEvaluation::updateOrCreate(
            ['application_id' => $request->application_id],
            [
                'interviewer_id' => $user->id,
                'written_score' => $request->written_score ?: null,
                'practical_score' => $request->practical_score ?: null,
                'oral_score' => $request->oral_score ?: null,
                'comments' => $request->comments,
            ]
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Evaluation saved successfully.'
        ]);
    }

    /**
     * Bulk update application status
     */
    private function bulkUpdateStatus(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:job_applications,id',
            'status' => 'required|in:Applied,Shortlisted,Rejected,Interviewing,Offer Extended,Hired',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $applications = JobApplication::whereIn('id', $request->application_ids)->get();
        $updated = 0;

        foreach ($applications as $application) {
            $oldStatus = $application->status;
            $application->update(['status' => $request->status]);

            if ($request->status === 'Shortlisted') {
                $application->update([
                    'shortlisted_by' => $user->id,
                    'shortlisted_at' => now(),
                ]);
            }

            ApplicationHistory::create([
                'application_id' => $application->id,
                'status_from' => $oldStatus,
                'status_to' => $request->status,
                'changed_by' => $user->id,
            ]);

            $updated++;
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} application(s) to '{$request->status}'."
        ]);
    }

    /**
     * Bulk delete applications
     */
    private function bulkDelete(Request $request, $user)
    {
        if (!$user->hasRole('System Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed: Only System Admin can delete applications.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:job_applications,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $applications = JobApplication::whereIn('id', $request->application_ids)->get();
        $deleted = 0;

        foreach ($applications as $application) {
            // Delete associated documents
            foreach ($application->documents as $doc) {
                if (Storage::exists('public/recruitment/' . $doc->file_path)) {
                    Storage::delete('public/recruitment/' . $doc->file_path);
                }
            }
            $application->delete();
            $deleted++;
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} application(s)."
        ]);
    }

    /**
     * Schedule interview
     */
    private function scheduleInterview(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'application_id' => 'required|exists:job_applications,id',
            'interview_type' => 'required|in:Written,Oral,Practical',
            'scheduled_at' => 'required|date|after:now',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'interviewer_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $interview = InterviewSchedule::create([
            'application_id' => $request->application_id,
            'interview_type' => $request->interview_type,
            'scheduled_at' => $request->scheduled_at,
            'location' => $request->location,
            'notes' => $request->notes,
            'scheduled_by' => $user->id,
            'interviewer_id' => $request->interviewer_id,
            'status' => 'Scheduled',
        ]);

        // Notify applicant and interviewer
        $application = JobApplication::find($request->application_id);
        if ($application) {
            // Update application status to Interviewing if not already
            if ($application->status !== 'Interviewing') {
                $application->update(['status' => 'Interviewing']);
            }

            // Send notification to applicant (if email/phone available)
            // Note: This would require a public notification system or email
        }

        if ($request->interviewer_id) {
            $this->notificationService->notify(
                $request->interviewer_id,
                "You have been scheduled to conduct a {$request->interview_type} interview on " . Carbon::parse($request->scheduled_at)->format('M d, Y H:i'),
                route('modules.hr.recruitment'),
                'Interview Scheduled'
            );
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Interview scheduled successfully.'
        ]);
    }

    /**
     * Get interview schedules
     */
    private function getInterviewSchedules(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $query = InterviewSchedule::with(['application.job', 'interviewer', 'scheduledBy']);

        if ($request->has('application_id')) {
            $query->where('application_id', $request->application_id);
        }

        if ($request->has('filter')) {
            $filter = $request->filter;
            if ($filter === 'upcoming') {
                $query->where('interview_date', '>=', now()->format('Y-m-d'))
                      ->where('status', 'Scheduled');
            } elseif ($filter === 'completed') {
                $query->where('status', 'Completed');
            } elseif ($filter === 'cancelled') {
                $query->where('status', 'Cancelled');
            }
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $interviews = $query->orderBy('scheduled_at', 'asc')->get();

        // Normalize payload to what the UI expects
        $normalized = $interviews->map(function ($s) {
            return [
                'id' => $s->id,
                'status' => $s->status,
                'interview_mode' => $s->interview_type, // map to expected key
                'interview_date' => optional($s->scheduled_at)->toDateString(),
                'interview_time' => optional($s->scheduled_at)->format('H:i'),
                'location' => $s->location,
                'application' => $s->relationLoaded('application') && $s->application ? [
                    'id' => $s->application->id,
                    'first_name' => $s->application->first_name,
                    'last_name' => $s->application->last_name,
                    'job' => $s->application->relationLoaded('job') && $s->application->job ? [
                        'id' => $s->application->job->id,
                        'job_title' => $s->application->job->job_title,
                    ] : null,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'schedules' => $normalized,
        ]);
    }

    /**
     * Update interview status
     */
    private function updateInterviewStatus(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'interview_id' => 'required|exists:interview_schedules,id',
            'status' => 'required|in:Scheduled,Completed,Cancelled,Rescheduled',
            'feedback' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $interview = InterviewSchedule::find($request->interview_id);
        $interview->update([
            'status' => $request->status,
            'feedback' => $request->feedback,
            'completed_at' => $request->status === 'Completed' ? now() : null,
        ]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Interview status updated successfully.'
        ]);
    }

    /**
     * Get application history
     */
    private function getApplicationHistory(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $query = ApplicationHistory::with(['changedBy', 'application.job']);

        if ($request->has('application_id')) {
            $query->where('application_id', $request->application_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('application', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status_to', $request->status);
        }

        $history = $query->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json([
            'success' => true,
            'history' => $history
        ]);
    }

    /**
     * Export jobs PDF
     */
    private function exportJobsPdf(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $jobs = RecruitmentJob::with(['creator', 'approver'])
            ->withCount('applications')
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('modules.hr.pdf.recruitment-jobs', compact('jobs'));
        $filename = 'Recruitment_Jobs_' . date('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Export applications Excel
     */
    private function exportApplicationsExcel(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $query = JobApplication::with(['job', 'evaluation']);

        if ($request->has('job_id')) {
            $query->where('job_id', $request->job_id);
        }

        $applications = $query->orderBy('application_date', 'desc')->get();

        $filename = 'Applications_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->streamDownload(function() use ($applications) {
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($output, [
                'Job Title', 'Name', 'Email', 'Phone', 'Status', 
                'Applied Date', 'Written Score', 'Practical Score', 'Oral Score', 'Total Score'
            ]);

            foreach ($applications as $app) {
                fputcsv($output, [
                    $app->job->job_title ?? 'N/A',
                    $app->first_name . ' ' . $app->last_name,
                    $app->email,
                    $app->phone,
                    $app->status,
                    $app->application_date->format('Y-m-d'),
                    $app->evaluation->written_score ?? 'N/A',
                    $app->evaluation->practical_score ?? 'N/A',
                    $app->evaluation->oral_score ?? 'N/A',
                    $app->evaluation->total_score ?? 'N/A',
                ]);
            }

            fclose($output);
        }, $filename, $headers);
    }

    /**
     * Get analytics data
     */
    private function getAnalytics(Request $request, $user)
    {
        if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director'])) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization Failed.'
            ], 403);
        }

        $dateFrom = $request->date_from ?? now()->subMonths(6)->format('Y-m-d');
        $dateTo = $request->date_to ?? now()->format('Y-m-d');

        $analytics = [
            'jobs_by_status' => RecruitmentJob::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            'applications_by_status' => JobApplication::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            'applications_over_time' => JobApplication::selectRaw('DATE(application_date) as date, COUNT(*) as count')
                ->whereBetween('application_date', [$dateFrom, $dateTo])
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'top_jobs' => RecruitmentJob::withCount('applications')
                ->where('status', 'Active')
                ->orderBy('applications_count', 'desc')
                ->limit(10)
                ->get(),
            'hiring_rate' => [
                'total' => JobApplication::count(),
                'hired' => JobApplication::where('status', 'Hired')->count(),
                'rate' => JobApplication::count() > 0 
                    ? round((JobApplication::where('status', 'Hired')->count() / JobApplication::count()) * 100, 2)
                    : 0,
            ],
        ];

        return response()->json([
            'success' => true,
            'analytics' => $analytics
        ]);
    }

    /**
     * Submit application (for external applicants)
     */
    private function submitApplication(Request $request, $user)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:recruitment_jobs,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'documents' => 'nullable|array',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $job = RecruitmentJob::find($request->job_id);
        
        if ($job->status !== 'Active') {
            return response()->json([
                'success' => false,
                'message' => 'This job vacancy is not currently accepting applications.'
            ], 400);
        }

        if ($job->application_deadline < now()) {
            return response()->json([
                'success' => false,
                'message' => 'The application deadline for this job has passed.'
            ], 400);
        }

        $application = JobApplication::create([
            'job_id' => $request->job_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => 'Applied',
            'application_date' => now(),
        ]);

        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('recruitment', $filename, 'public');

                ApplicationDocument::create([
                    'application_id' => $application->id,
                    'document_type' => $file->getClientMimeType(),
                    'original_filename' => $file->getClientOriginalName(),
                    'file_path' => $filename,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);
            }
        }

        // Create history
        ApplicationHistory::create([
            'application_id' => $application->id,
            'status_from' => null,
            'status_to' => 'Applied',
            'changed_by' => null,
            'notes' => 'Application submitted',
        ]);

        // Notify HR
        $hrUsers = User::whereHas('roles', function($q) {
            $q->where('name', 'HR Officer');
        })->pluck('id')->toArray();

        if (!empty($hrUsers)) {
            $this->notificationService->notify(
                $hrUsers,
                "New application received for '{$job->job_title}' from {$request->first_name} {$request->last_name}",
                route('modules.hr.recruitment'),
                'New Job Application'
            );
        }

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully. We will contact you soon.',
            'application_id' => $application->id
        ]);
    }

    /**
     * Display public careers page
     */
    public function publicCareers()
    {
        return view('public.careers');
    }

    /**
     * Get public jobs API (for public careers page)
     */
    public function getPublicJobs()
    {
        $query = RecruitmentJob::query()
            ->where('status', 'Active')
            ->where('application_deadline', '>=', now()->format('Y-m-d'));

        // Filters
        $search = request('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('job_title', 'like', "%{$search}%")
                  ->orWhere('job_description', 'like', "%{$search}%")
                  ->orWhere('qualifications', 'like', "%{$search}%");
            });
        }

        // Optional: department/location/type if added to schema later
        if (request()->filled('department')) {
            $query->where('department', request('department'));
        }
        if (request()->filled('location')) {
            $query->where('location', request('location'));
        }
        if (request()->filled('employment_type')) {
            $query->where('employment_type', request('employment_type'));
        }

        // Sorting
        $sort = request('sort', 'newest');
        if ($sort === 'deadline') {
            $query->orderBy('application_deadline', 'asc');
        } elseif ($sort === 'title_asc') {
            $query->orderBy('job_title', 'asc');
        } elseif ($sort === 'title_desc') {
            $query->orderBy('job_title', 'desc');
        } else { // newest
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = max(1, min(50, (int) request('per_page', 12)));
        $page = max(1, (int) request('page', 1));
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        $jobs = collect($paginator->items())->map(function ($job) {
            return [
                'id' => $job->id,
                'job_title' => $job->job_title,
                'job_description' => $job->job_description,
                'qualifications' => $job->qualifications,
                'application_deadline' => $job->application_deadline->format('Y-m-d'),
                'interview_mode' => $job->interview_mode,
                'required_attachments' => $job->required_attachments,
                'created_at' => $job->created_at->format('Y-m-d H:i:s'),
                'department' => $job->department ?? null,
                'location' => $job->location ?? null,
                'employment_type' => $job->employment_type ?? null,
            ];
        });

        return response()->json([
            'jobs' => $jobs,
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ]
        ]);
    }

    /**
     * Submit public application
     */
    public function submitPublicApplication(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_id' => 'required|exists:recruitment_jobs,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'current_address' => 'nullable|string|max:500',
            'cover_letter' => 'nullable|string|max:2000',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'doc_types' => 'nullable|array',
            'sms_opt_in' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $job = RecruitmentJob::find($request->job_id);
            
            if ($job->status !== 'Active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This job vacancy is not currently accepting applications.'
                ], 400);
            }

            if ($job->application_deadline < now()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The application deadline for this job has passed.'
                ], 400);
            }

            $application = JobApplication::create([
                'job_id' => $request->job_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => 'Applied',
                'application_date' => now(),
            ]);

            // Handle document uploads
            if ($request->hasFile('attachments')) {
                $docTypes = $request->doc_types ?? [];
                $files = $request->file('attachments');
                
                foreach ($files as $index => $file) {
                    $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('recruitment', $filename, 'public');

                    ApplicationDocument::create([
                        'application_id' => $application->id,
                        'document_type' => $docTypes[$index] ?? 'Document',
                        'original_filename' => $file->getClientOriginalName(),
                        'file_path' => $filename,
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ]);
                }
            }

            // Create history
            ApplicationHistory::create([
                'application_id' => $application->id,
                'status_from' => null,
                'status_to' => 'Applied',
                'changed_by' => null,
                'notes' => 'Application submitted via public careers page',
            ]);

            // Notify HR
            $hrUsers = User::whereHas('roles', function($q) {
                $q->where('name', 'HR Officer');
            })->pluck('id')->toArray();

            if (!empty($hrUsers)) {
                $this->notificationService->notify(
                    $hrUsers,
                    "New application received for '{$job->job_title}' from {$request->first_name} {$request->last_name}",
                    route('modules.hr.recruitment'),
                    'New Job Application'
                );
            }

            DB::commit();

            // Optional: send SMS confirmation to applicant if opted-in
            try {
                if ((bool)$request->boolean('sms_opt_in')) {
                    $this->notificationService->sendSMS(
                        $request->phone,
                        'Thank you for applying to ' . ($job->job_title ?? 'our job') . '. We will contact you if shortlisted.'
                    );
                }
            } catch (\Exception $e) {
                // Do not fail the request if SMS fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully. We will contact you soon.',
                'application_id' => $application->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting your application. Please try again.'
            ], 500);
        }
    }
}
