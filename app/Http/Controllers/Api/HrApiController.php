<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PermissionRequest;
use App\Models\SickSheet;
use App\Models\Assessment;
use App\Models\AssessmentProgressReport;
use App\Models\RecruitmentJob;
use App\Models\JobApplication;
use App\Models\Employee;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class HrApiController extends Controller
{
    // Permission Requests
    
    public function permissionIndex(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD']);
        
        $query = PermissionRequest::with(['user:id,name,email']);
        
        if (!$isManager) {
            $query->where('user_id', $user->id);
        } elseif ($user->hasRole('HOD')) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('primary_department_id', $user->primary_department_id);
            });
        }
        
        $permissions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function myPermissions()
    {
        $user = Auth::user();
        
        $permissions = PermissionRequest::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function permissionShow($id)
    {
        $permission = PermissionRequest::with(['user'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $permission
        ]);
    }

    public function permissionStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $permission = PermissionRequest::create([
            'user_id' => $user->id,
            'request_id' => 'PR' . date('Ymd') . '-' . str_pad(PermissionRequest::count() + 1, 3, '0', STR_PAD_LEFT),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'status' => 'pending_hr',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission request created successfully',
            'data' => $permission
        ], 201);
    }

    public function permissionApprove($id)
    {
        $permission = PermissionRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Update status based on current status and user role
        if ($permission->status == 'pending_hr' && $user->hasRole('HR Officer')) {
            $permission->update(['status' => 'pending_hod']);
        } elseif ($permission->status == 'pending_hod' && $user->hasRole('HOD')) {
            $permission->update(['status' => 'pending_hr_final']);
        } elseif ($permission->status == 'pending_hr_final' && $user->hasRole('HR Officer')) {
            $permission->update(['status' => 'approved']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Permission request approved'
        ]);
    }

    public function permissionReject($id)
    {
        $permission = PermissionRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $permission->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Permission request rejected'
        ]);
    }

    public function permissionConfirmReturn($id)
    {
        $permission = PermissionRequest::findOrFail($id);
        $user = Auth::user();

        if ($permission->user_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $permission->update(['status' => 'return_pending']);

        return response()->json([
            'success' => true,
            'message' => 'Return confirmed'
        ]);
    }

    // Sick Sheets

    public function sickSheetIndex(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD']);
        
        $query = SickSheet::with(['employee:id,name,email']);
        
        if (!$isManager) {
            $query->where('employee_id', $user->id);
        }
        
        $sheets = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $sheets
        ]);
    }

    public function mySickSheets()
    {
        $user = Auth::user();
        
        $sheets = SickSheet::where('employee_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sheets
        ]);
    }

    public function sickSheetShow($id)
    {
        $sheet = SickSheet::with(['employee'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $sheet
        ]);
    }

    public function sickSheetStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'medical_document' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'doctor_name' => 'nullable|string',
            'hospital_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $file = $request->file('medical_document');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('sick_sheets', $filename, 'public');

        $sheet = SickSheet::create([
            'employee_id' => $user->id,
            'sheet_number' => 'SS' . date('Ymd') . '-' . str_pad(SickSheet::count() + 1, 3, '0', STR_PAD_LEFT),
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => \Carbon\Carbon::parse($request->start_date)->diffInDays(\Carbon\Carbon::parse($request->end_date)) + 1,
            'medical_document' => $path,
            'doctor_name' => $request->doctor_name,
            'hospital_name' => $request->hospital_name,
            'status' => 'pending_hr',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sick sheet submitted successfully',
            'data' => $sheet
        ], 201);
    }

    public function sickSheetApprove($id)
    {
        $sheet = SickSheet::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if ($sheet->status == 'pending_hr' && $user->hasRole('HR Officer')) {
            $sheet->update(['status' => 'pending_hod']);
        } elseif ($sheet->status == 'pending_hod' && $user->hasRole('HOD')) {
            $sheet->update(['status' => 'approved']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sick sheet approved'
        ]);
    }

    public function sickSheetReject($id)
    {
        $sheet = SickSheet::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $sheet->update(['status' => 'rejected']);

        return response()->json([
            'success' => true,
            'message' => 'Sick sheet rejected'
        ]);
    }

    // Assessments

    public function assessmentIndex(Request $request)
    {
        $user = Auth::user();
        
        $query = Assessment::with(['employee:id,name,email']);
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD', 'CEO'])) {
            $query->where('employee_id', $user->id);
        }
        
        $assessments = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $assessments
        ]);
    }

    public function myAssessments()
    {
        $user = Auth::user();
        
        $assessments = Assessment::where('employee_id', $user->id)
            ->with('activities')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $assessments
        ]);
    }

    public function assessmentShow($id)
    {
        $assessment = Assessment::with(['employee', 'activities'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $assessment
        ]);
    }

    public function assessmentStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'activities' => 'required|array|min:1',
            'activities.*.name' => 'required|string',
            'activities.*.contribution_percentage' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $assessment = Assessment::create([
            'employee_id' => $user->id,
            'title' => $request->title,
            'status' => 'pending_hod',
        ]);

        foreach ($request->activities as $activity) {
            $assessment->activities()->create([
                'name' => $activity['name'],
                'contribution_percentage' => $activity['contribution_percentage'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Assessment created successfully',
            'data' => $assessment
        ], 201);
    }

    public function assessmentSubmitProgress(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'activity_id' => 'required|exists:assessment_activities,id',
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $report = AssessmentProgressReport::create([
            'activity_id' => $request->activity_id,
            'progress_percentage' => $request->progress_percentage,
            'notes' => $request->notes,
            'status' => 'pending_approval',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progress report submitted successfully',
            'data' => $report
        ], 201);
    }

    public function assessmentProgress($id)
    {
        $assessment = Assessment::with(['activities.progressReports'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $assessment
        ]);
    }

    // Recruitment

    public function jobIndex(Request $request)
    {
        $jobs = RecruitmentJob::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jobs
        ]);
    }

    public function jobShow($id)
    {
        $job = RecruitmentJob::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $job
        ]);
    }

    public function jobApplications($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $applications = JobApplication::where('job_id', $id)
            ->with(['applicant'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $applications
        ]);
    }

    public function jobApply(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cover_letter' => 'required|string',
            'resume' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $job = RecruitmentJob::findOrFail($id);
        $user = Auth::user();

        $file = $request->file('resume');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('job_applications', $filename, 'public');

        $application = JobApplication::create([
            'job_id' => $job->id,
            'applicant_id' => $user->id,
            'cover_letter' => $request->cover_letter,
            'resume' => $path,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Application submitted successfully',
            'data' => $application
        ], 201);
    }

    // Employees

    public function employeeIndex(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = Employee::with(['user:id,name,email']);
        
        if ($user->hasRole('HOD')) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('primary_department_id', $user->primary_department_id);
            });
        }
        
        $employees = $query->orderBy('employee_id')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $employees
        ]);
    }

    public function employeeShow($id)
    {
        $employee = Employee::with(['user'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    }

    // Incidents

    public function incidentIndex(Request $request)
    {
        $user = Auth::user();
        $isManager = $user->hasAnyRole(['System Admin', 'HR Officer', 'HOD']);
        
        $query = Incident::with(['reporter:id,name,email']);
        
        if (!$isManager) {
            $query->where('reporter_id', $user->id);
        }
        
        $incidents = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $incidents
        ]);
    }

    public function myIncidents()
    {
        $user = Auth::user();
        
        $incidents = Incident::where('reporter_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $incidents
        ]);
    }

    public function incidentShow($id)
    {
        $incident = Incident::with(['reporter', 'updates'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $incident
        ]);
    }

    public function incidentStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
            'location' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();

        $incident = Incident::create([
            'reporter_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'severity' => $request->severity,
            'location' => $request->location,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Incident reported successfully',
            'data' => $incident
        ], 201);
    }

    public function incidentUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $incident = Incident::findOrFail($id);
        $user = Auth::user();

        if ($incident->reporter_id != $user->id && 
            !$user->hasAnyRole(['System Admin', 'HR Officer', 'HOD'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $incident->update($request->only(['status']));

        if ($request->has('notes')) {
            $incident->updates()->create([
                'user_id' => $user->id,
                'update_text' => $request->notes,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Incident updated successfully'
        ]);
    }

    public function incidentAddUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'update_text' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $incident = Incident::findOrFail($id);
        $user = Auth::user();

        $incident->updates()->create([
            'user_id' => $user->id,
            'update_text' => $request->update_text,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Update added successfully'
        ], 201);
    }
}







