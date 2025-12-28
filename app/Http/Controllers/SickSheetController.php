<?php

namespace App\Http\Controllers;

use App\Models\SickSheet;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SickSheetController extends Controller
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
        $isDirector = $user->hasRole('Director');
        $isAdmin = $user->hasRole('System Admin');
        
        // Check if user can see all tabs (HR, HOD, Director, or Admin)
        $canSeeAllTabs = $isHR || $isHOD || $isDirector || $isAdmin;

        $query = SickSheet::with(['employee', 'hrReviewer', 'hodApprover', 'hrFinalVerifier']);

        // Staff (non-HR, non-HOD, non-Director, non-Admin) can only see their own
        if (!$canSeeAllTabs) {
            $query->where('employee_id', $user->id);
        } elseif ($isHOD && !$isAdmin && !$isDirector) {
            // HOD can see their department's sheets
            $query->whereHas('employee', function($q) use ($user) {
                $q->where('primary_department_id', $user->primary_department_id);
            });
        }

        $sickSheets = $query->orderBy('created_at', 'desc')->get();

        $awaitingMyAction = collect();
        $myRequests = collect();
        $otherRequests = collect();

        foreach ($sickSheets as $sheet) {
            $isOwn = $sheet->employee_id == $user->id;
            $awaitingAction = false;

            if ($isHR && !$isOwn) {
                if (in_array($sheet->status, ['pending_hr', 'return_pending'])) {
                    $awaitingAction = true;
                }
            }

            if ($isHOD && !$isOwn && !$isAdmin && !$isDirector) {
                if ($sheet->status === 'pending_hod') {
                    $awaitingAction = true;
                }
            }

            if ($awaitingAction) {
                $awaitingMyAction->push($sheet);
            } elseif ($isOwn) {
                $myRequests->push($sheet);
            } else {
                $otherRequests->push($sheet);
            }
        }

        // Get status-based counts for tabs (only for users who can see all tabs)
        $pendingHR = $canSeeAllTabs ? SickSheet::where('status', 'pending_hr')->count() : 0;
        $pendingHOD = $canSeeAllTabs ? SickSheet::where('status', 'pending_hod')->count() : 0;
        $returnPending = $canSeeAllTabs ? SickSheet::where('status', 'return_pending')->count() : 0;
        $allPending = $pendingHR + $pendingHOD + $returnPending;
        
        // Get all sheets grouped by status for detailed view (only for users who can see all tabs)
        if ($canSeeAllTabs) {
            $allSheets = SickSheet::with(['employee', 'hrReviewer', 'hodApprover', 'hrFinalVerifier'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // For staff, only show their own sheets
            $allSheets = $myRequests;
        }

        return view('modules.hr.sick-sheets', compact(
            'awaitingMyAction', 'myRequests', 'otherRequests',
            'isHR', 'isHOD', 'isDirector', 'isAdmin', 'canSeeAllTabs',
            'pendingHR', 'pendingHOD', 'returnPending', 'allPending', 'allSheets'
        ));
    }

    public function store(Request $request)
    {
        // Ensure JSON response
        if (!$request->wantsJson() && !$request->ajax()) {
            $request->headers->set('Accept', 'application/json');
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401)->header('Content-Type', 'application/json');
        }

        // Validate manually to ensure JSON response
        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'medical_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422)->header('Content-Type', 'application/json');
        }

        DB::beginTransaction();
        try {
            // Generate sheet number
            $today = date('Ymd');
            $lastSheet = SickSheet::whereDate('created_at', today())
                ->where('sheet_number', 'like', 'SS' . $today . '-%')
                ->orderBy('id', 'desc')
                ->first();

            $sequence = 1;
            if ($lastSheet && preg_match('/SS\d{8}-(\d{3})/', $lastSheet->sheet_number, $matches)) {
                $sequence = (int)$matches[1] + 1;
            }

            $sheetNumber = 'SS' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);

            // Upload medical document
            $file = $request->file('medical_document');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('sick-sheets', $filename, 'public');

            // Calculate total days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            $sickSheet = SickSheet::create([
                'sheet_number' => $sheetNumber,
                'employee_id' => $user->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'medical_document_path' => $path,
                'status' => 'pending_hr',
            ]);

            // Send notifications
            try {
                $this->notificationService->notify(
                    $user->id,
                    "Your sick sheet #{$sheetNumber} has been submitted and is pending HR review.",
                    route('modules.hr.sick-sheets'),
                    'Sick Sheet Submitted'
                );

                $this->notificationService->notifyHR(
                    "New sick sheet #{$sheetNumber} from {$user->name} for {$totalDays} day(s) is pending your review.",
                    route('modules.hr.sick-sheets'),
                    'New Sick Sheet Submitted',
                    ['sheet_number' => $sheetNumber, 'staff_name' => $user->name, 'days' => $totalDays]
                );
            } catch (\Exception $e) {
                \Log::error('Notification error: ' . $e->getMessage());
            }

            DB::commit();
            
            // Log activity
            ActivityLogService::logCreated($sickSheet, "Created sick sheet {$sheetNumber} for {$totalDays} days", [
                'sheet_number' => $sheetNumber,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $totalDays,
                'status' => $sickSheet->status,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Sick sheet submitted successfully!',
                'id' => $sickSheet->id
            ]);
        } catch (\Throwable $e) {
            DB::rollback();
            \Log::error('Sick sheet store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'request_data' => $request->except(['medical_document']) // Don't log file contents
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit sick sheet: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    public function hrReview(Request $request, SickSheet $sickSheet)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403);
        }

        $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'required|string|max:1000',
        ]);

        if ($sickSheet->status !== 'pending_hr') {
            return response()->json(['success' => false, 'message' => 'Sheet is not pending HR review']);
        }

        $newStatus = $request->decision === 'approve' ? 'pending_hod' : 'rejected';

        $sickSheet->update([
            'status' => $newStatus,
            'hr_reviewed_at' => now(),
            'hr_reviewed_by' => $user->id,
            'hr_comments' => $request->comments,
        ]);

        // Send notifications
        try {
            $sheetNo = $sickSheet->sheet_number;
            $employee = $sickSheet->employee;

            if ($request->decision === 'approve') {
                $this->notificationService->notify(
                    $employee->id,
                    "Your sick sheet #{$sheetNo} has been reviewed by HR and forwarded to HOD for approval.",
                    route('modules.hr.sick-sheets'),
                    'Sick Sheet Forwarded to HOD'
                );

                if ($employee->primary_department_id) {
                    $this->notificationService->notifyHOD(
                        $employee->primary_department_id,
                        "New sick sheet #{$sheetNo} from {$employee->name} is pending your approval.",
                        route('modules.hr.sick-sheets'),
                        'New Sick Sheet Pending Approval',
                        ['sheet_number' => $sheetNo, 'staff_name' => $employee->name]
                    );
                }
            } else {
                $this->notificationService->notify(
                    $employee->id,
                    "Your sick sheet #{$sheetNo} has been rejected by HR. Please check the comments.",
                    route('modules.hr.sick-sheets'),
                    'Sick Sheet Rejected'
                );
            }
        } catch (\Exception $e) {
            \Log::error('Notification error: ' . $e->getMessage());
        }

        // Log activity
        ActivityLogService::logAction('sick_sheet_hr_reviewed', ucfirst($request->decision) . " sick sheet {$sickSheet->sheet_number} by HR", $sickSheet, [
            'sheet_number' => $sickSheet->sheet_number,
            'decision' => $request->decision,
            'comments' => $request->comments,
            'reviewed_by' => $user->name,
        ]);

        return response()->json(['success' => true, 'message' => 'Review completed']);
    }

    public function hodApprove(Request $request, SickSheet $sickSheet)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HOD', 'System Admin'])) {
            abort(403);
        }

        $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'nullable|string|max:1000',
        ]);

        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can approve at any level, others must wait for pending_hod
        if (!$isSystemAdmin && $sickSheet->status !== 'pending_hod') {
            return response()->json(['success' => false, 'message' => 'Sheet is not pending HOD approval']);
        }

        // Check department - System Admin bypasses this
        if ($user->hasRole('HOD') && !$isSystemAdmin) {
            if ($sickSheet->employee->primary_department_id !== $user->primary_department_id) {
                return response()->json(['success' => false, 'message' => 'You can only approve sheets from your department']);
            }
        }

        $newStatus = $request->decision === 'approve' ? 'approved' : 'rejected';

        $sickSheet->update([
            'status' => $newStatus,
            'hod_approved_at' => now(),
            'hod_approved_by' => $user->id,
            'hod_comments' => $request->comments,
        ]);

        // Send notifications
        try {
            $sheetNo = $sickSheet->sheet_number;
            $employee = $sickSheet->employee;

            if ($request->decision === 'approve') {
                $this->notificationService->notify(
                    $employee->id,
                    "Your sick sheet #{$sheetNo} has been approved by HOD. Your leave is approved.",
                    route('modules.hr.sick-sheets'),
                    'Sick Sheet Approved'
                );
            } else {
                $this->notificationService->notify(
                    $employee->id,
                    "Your sick sheet #{$sheetNo} has been rejected by HOD. Please check the comments.",
                    route('modules.hr.sick-sheets'),
                    'Sick Sheet Rejected'
                );
            }
        } catch (\Exception $e) {
            \Log::error('Notification error: ' . $e->getMessage());
        }

        // Log activity
        ActivityLogService::logAction('sick_sheet_hod_approved', ucfirst($request->decision) . " sick sheet {$sickSheet->sheet_number} by HOD", $sickSheet, [
            'sheet_number' => $sickSheet->sheet_number,
            'decision' => $request->decision,
            'comments' => $request->comments,
            'approved_by' => $user->name,
        ]);

        return response()->json(['success' => true, 'message' => 'Approval decision submitted']);
    }

    public function confirmReturn(Request $request, SickSheet $sickSheet)
    {
        $user = Auth::user();

        if ($sickSheet->employee_id !== $user->id) {
            abort(403);
        }

        if ($sickSheet->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Sheet is not approved']);
        }

        $request->validate([
            'return_remarks' => 'nullable|string|max:1000',
        ]);

        $sickSheet->update([
            'status' => 'return_pending',
            'return_submitted_at' => now(),
            'return_remarks' => $request->return_remarks,
        ]);

        // Send notifications
        try {
            $sheetNo = $sickSheet->sheet_number;
            $this->notificationService->notify(
                $user->id,
                "You have submitted your return confirmation for sick sheet #{$sheetNo}. It is pending HR verification.",
                route('modules.hr.sick-sheets'),
                'Return Confirmation Submitted'
            );

            $this->notificationService->notifyHR(
                "Return confirmation submitted for sick sheet #{$sheetNo} by {$user->name}. Please verify.",
                route('modules.hr.sick-sheets'),
                'Return Confirmation Pending',
                ['sheet_number' => $sheetNo, 'staff_name' => $user->name]
            );
        } catch (\Exception $e) {
            \Log::error('Notification error: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Return confirmation submitted']);
    }

    public function hrFinalVerification(Request $request, SickSheet $sickSheet)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403);
        }

        $request->validate([
            'decision' => 'required|in:approve,reject',
            'comments' => 'nullable|string|max:1000',
        ]);

        if ($sickSheet->status !== 'return_pending') {
            return response()->json(['success' => false, 'message' => 'Return is not pending verification']);
        }

        $newStatus = $request->decision === 'approve' ? 'completed' : 'rejected';

        $sickSheet->update([
            'status' => $newStatus,
            'hr_final_verified_at' => now(),
            'hr_final_verified_by' => $user->id,
            'hr_final_comments' => $request->comments,
        ]);

        // Send notifications
        try {
            $sheetNo = $sickSheet->sheet_number;
            $employee = $sickSheet->employee;

            if ($request->decision === 'approve') {
                $notifyIds = [$employee->id];
                if ($sickSheet->hr_reviewed_by) $notifyIds[] = $sickSheet->hr_reviewed_by;
                if ($sickSheet->hod_approved_by) $notifyIds[] = $sickSheet->hod_approved_by;

                $this->notificationService->notify(
                    $notifyIds,
                    "Sick sheet #{$sheetNo} return has been verified and completed by HR.",
                    route('modules.hr.sick-sheets'),
                    'Sick Sheet Completed',
                    ['sheet_number' => $sheetNo, 'staff_name' => $employee->name]
                );
            } else {
                $this->notificationService->notify(
                    $employee->id,
                    "Your return confirmation for sick sheet #{$sheetNo} has been rejected. Please check the comments.",
                    route('modules.hr.sick-sheets'),
                    'Return Confirmation Rejected'
                );
            }
        } catch (\Exception $e) {
            \Log::error('Notification error: ' . $e->getMessage());
        }

        // Log activity
        ActivityLogService::logAction('sick_sheet_hr_final_verified', ucfirst($request->decision) . " sick sheet {$sickSheet->sheet_number} return verification by HR", $sickSheet, [
            'sheet_number' => $sickSheet->sheet_number,
            'decision' => $request->decision,
            'comments' => $request->comments,
            'verified_by' => $user->name,
        ]);

        return response()->json(['success' => true, 'message' => 'Verification completed']);
    }
}

