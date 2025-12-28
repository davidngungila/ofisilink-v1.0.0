<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\ImprestRequest;
use App\Models\User;
use App\Models\ImprestAssignment;
use App\Models\ImprestReceipt;
use App\Models\GeneralLedger;
use App\Models\ChartOfAccount;
use App\Models\GlAccount;
use App\Models\CashBox;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\NotificationService;
use App\Services\ActivityLogService;

class ImprestController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Dashboard - Shows navigation cards with counts
     */
    public function index(Request $request)
    {
        // Calculate statistics
        $stats = [
            'all' => ImprestRequest::count(),
            'pending_hod' => ImprestRequest::where('status', 'pending_hod')->count(),
            'pending_ceo' => ImprestRequest::where('status', 'pending_ceo')->count(),
            'approved' => ImprestRequest::where('status', 'approved')->count(),
            'assigned' => ImprestRequest::where('status', 'assigned')->count(),
            'paid' => ImprestRequest::where('status', 'paid')->count(),
            'pending_receipt_verification' => ImprestRequest::where('status', 'pending_receipt_verification')->count(),
            'completed' => ImprestRequest::where('status', 'completed')->count(),
        ];

        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['Staff', 'Employee']) && !$user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO', 'Director']);
        
        // If staff, redirect to my assignments page
        if ($isStaff) {
            return redirect()->route('imprest.my-assignments');
        }
        
        // Get my assignments count for staff (for dashboard card)
        $myAssignmentsCount = 0;
        if ($user->hasAnyRole(['Staff', 'Employee'])) {
            $myAssignmentsCount = ImprestAssignment::where('staff_id', auth()->id())->count();
        }
        
        return view('modules.finance.imprest-dashboard', compact('stats', 'myAssignmentsCount'));
    }

    /**
     * Create New Imprest Request Page
     */
    public function create()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized. Only Accountant can create imprest requests.');
        }

        return view('modules.finance.imprest-create');
    }

    /**
     * Pending HOD Approval Page
     */
    public function pendingHod(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HOD', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = ImprestRequest::where('status', 'pending_hod')
            ->with(['accountant', 'assignments.staff', 'assignments.receipts'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(20);
        $count = ImprestRequest::where('status', 'pending_hod')->count();

        return view('modules.finance.imprest-pending-hod', compact('requests', 'count'));
    }

    /**
     * Pending CEO Approval Page
     */
    public function pendingCeo(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['CEO', 'Director', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = ImprestRequest::where('status', 'pending_ceo')
            ->with(['accountant', 'assignments.staff', 'assignments.receipts'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(20);
        $count = ImprestRequest::where('status', 'pending_ceo')->count();

        return view('modules.finance.imprest-pending-ceo', compact('requests', 'count'));
    }

    /**
     * Approved (Assign Staff) Page
     */
    public function approved(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = ImprestRequest::where('status', 'approved')
            ->with(['accountant', 'assignments.staff', 'assignments.receipts'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(20);
        $count = ImprestRequest::where('status', 'approved')->count();
        $staffMembers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Staff', 'Employee']);
        })->with('primaryDepartment')->get();

        // Get staff with unretired imprests for display
        $staffWithUnretiredImprests = ImprestAssignment::whereHas('imprestRequest', function($q) {
                $q->where('status', '!=', 'completed');
            })
            ->with(['imprestRequest', 'staff'])
            ->get()
            ->groupBy('staff_id')
            ->map(function($assignments) {
                return $assignments->map(function($assignment) {
                    return [
                        'staff_id' => $assignment->staff_id,
                        'staff_name' => $assignment->staff->name ?? 'Unknown',
                        'request_no' => $assignment->imprestRequest->request_no ?? 'N/A',
                        'status' => $assignment->imprestRequest->status ?? 'unknown',
                        'id' => $assignment->imprestRequest->id ?? null
                    ];
                })->toArray();
            })
            ->toArray();

        return view('modules.finance.imprest-approved', compact('requests', 'count', 'staffMembers', 'staffWithUnretiredImprests'));
    }

    /**
     * Assigned (Payment) Page
     */
    public function assigned(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = ImprestRequest::where('status', 'assigned')
            ->with(['accountant', 'assignments.staff', 'assignments.receipts'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(20);
        $count = ImprestRequest::where('status', 'assigned')->count();
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();
        
        // Get bank accounts for payment
        $bankAccounts = \App\Models\BankAccount::where('is_active', true)->orderBy('bank_name')->get();

        return view('modules.finance.imprest-assigned', compact('requests', 'count', 'glAccounts', 'cashBoxes', 'bankAccounts'));
    }

    /**
     * Paid (Awaiting Receipts) Page
     */
    public function paid(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = ImprestRequest::where('status', 'paid')
            ->with(['accountant', 'assignments.staff', 'assignments.receipts', 'receipts'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(20);
        $count = ImprestRequest::where('status', 'paid')->count();

        return view('modules.finance.imprest-paid', compact('requests', 'count'));
    }

    /**
     * Pending Verification Page
     */
    public function pendingVerification(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = ImprestRequest::where('status', 'pending_receipt_verification')
            ->with(['accountant', 'assignments.staff', 'assignments.receipts', 'receipts.verifiedBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(20);
        $count = ImprestRequest::where('status', 'pending_receipt_verification')->count();

        return view('modules.finance.imprest-pending-verification', compact('requests', 'count'));
    }

    /**
     * Completed Page
     */
    public function completed(Request $request)
    {
        $query = ImprestRequest::where('status', 'completed')
            ->with(['accountant', 'assignments.staff', 'assignments.receipts', 'receipts.verifiedBy'])
            ->orderBy('completed_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('completed_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('completed_at', '<=', $request->to_date);
        }

        $requests = $query->paginate(20);
        $count = ImprestRequest::where('status', 'completed')->count();

        return view('modules.finance.imprest-completed', compact('requests', 'count'));
    }

    /**
     * My Assignments Page (for Staff)
     */
    public function myAssignments(Request $request)
    {
        $user = Auth::user();
        
        // Allow staff, employee, and system admin
        if (!$user->hasAnyRole(['Staff', 'Employee', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        // Get all assignments for this staff member (all statuses)
        $query = ImprestAssignment::where('staff_id', auth()->id())
            ->with([
                'imprestRequest.accountant',
                'imprestRequest.assignments.staff',
                'imprestRequest.assignments.staff.primaryDepartment',
                'receipts',
                'receipts.submittedBy',
                'receipts.verifiedBy',
                'staff.primaryDepartment',
                'bankAccount'
            ]);

        // Apply status filter if provided
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->whereHas('imprestRequest', function($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('imprestRequest', function($q) use ($search) {
                $q->where('request_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());

        // Calculate statistics
        $stats = [
            'all' => ImprestAssignment::where('staff_id', auth()->id())->count(),
            'assigned' => ImprestAssignment::where('staff_id', auth()->id())
                ->whereHas('imprestRequest', function($q) {
                    $q->where('status', 'assigned');
                })->count(),
            'paid' => ImprestAssignment::where('staff_id', auth()->id())
                ->whereHas('imprestRequest', function($q) {
                    $q->where('status', 'paid');
                })->count(),
            'pending_receipt' => ImprestAssignment::where('staff_id', auth()->id())
                ->whereHas('imprestRequest', function($q) {
                    $q->where('status', 'paid');
                })
                ->where('receipt_submitted', false)
                ->count(),
            'completed' => ImprestAssignment::where('staff_id', auth()->id())
                ->whereHas('imprestRequest', function($q) {
                    $q->where('status', 'completed');
                })->count(),
        ];

        return view('modules.finance.imprest-my-assignments', compact('assignments', 'stats', 'status'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Accountant can create imprest requests.'
            ], 403);
        }

        $request->validate([
            'purpose' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expected_return_date' => 'nullable|date|after_or_equal:today',
            'priority' => 'required|in:normal,high,urgent',
            'description' => 'nullable|string|max:2000'
        ]);

        try {
            DB::beginTransaction();

            $imprestRequest = ImprestRequest::create([
                'request_no' => 'IMP-' . str_pad(ImprestRequest::count() + 1, 6, '0', STR_PAD_LEFT),
                'accountant_id' => Auth::id(),
                'purpose' => $request->purpose,
                'amount' => $request->amount,
                'expected_return_date' => $request->expected_return_date,
                'priority' => $request->priority,
                'description' => $request->description,
                'status' => 'pending_hod',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            // Log activity
            ActivityLogService::logCreated($imprestRequest, "Created imprest request {$imprestRequest->request_no} for TZS " . number_format($imprestRequest->amount, 2), [
                'request_no' => $imprestRequest->request_no,
                'amount' => $imprestRequest->amount,
                'purpose' => $imprestRequest->purpose,
                'priority' => $imprestRequest->priority,
            ]);

            // Notify HOD to review
            $link = route('imprest.show', ['id' => $imprestRequest->id]);
            $this->notificationService->notifyHOD(
                Auth::user()->primary_department_id ?? 0,
                "New imprest request {$imprestRequest->request_no} pending your review",
                $link,
                'Imprest Request Pending HOD Review'
            );

            return response()->json([
                'success' => true,
                'message' => 'Imprest request created successfully',
                'data' => $imprestRequest
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating imprest request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = Auth::user();
            $isSystemAdmin = $user->hasRole('System Admin');
            $isAccountant = $user->hasAnyRole(['Accountant', 'System Admin']);
            $isStaff = $user->hasAnyRole(['Staff', 'Employee']) && !$isSystemAdmin && !$isAccountant; // System Admin and Accountant are not considered staff
            
            // First, check if the imprest request exists at all
            $imprestRequest = ImprestRequest::with([
                'accountant',
                'assignments.staff',
                'assignments.receipts',
                'assignments.staff.primaryDepartment',
                'assignments.staff.primaryBankAccount',
                'receipts',
                'receipts.submittedBy',
                'receipts.verifiedBy',
                'receipts.assignment',
                'hodApproval',
                'ceoApproval'
            ])->find($id);
            
            // If imprest request doesn't exist, return 404
            if (!$imprestRequest) {
                abort(404, 'Imprest request not found.');
            }
            
            // System Admin and Accountant have full access to everything - skip access check
            // If user is staff (and not System Admin or Accountant), check if they have access to this request
            if ($isStaff && !$isSystemAdmin && !$isAccountant) {
                $hasAssignment = false;
                if ($imprestRequest->assignments && $imprestRequest->assignments->count() > 0) {
                    $hasAssignment = $imprestRequest->assignments->contains(function($assignment) use ($user) {
                        return $assignment && $assignment->staff_id == $user->id;
                    });
                }
                if (!$hasAssignment) {
                    abort(403, 'You do not have access to this imprest request.');
                }
            }
        
        // Sync receipt_submitted flag for assignments that have receipts but flag is false
        if ($imprestRequest->assignments) {
            foreach ($imprestRequest->assignments as $assignment) {
                if ($assignment && $assignment->receipts) {
                    $hasReceipts = $assignment->receipts->count() > 0;
                    if ($hasReceipts && !$assignment->receipt_submitted) {
                        // Update the flag if receipts exist but flag is not set
                        try {
                            $assignment->update([
                                'receipt_submitted' => true,
                                'receipt_submitted_at' => $assignment->receipts->min('submitted_at') ?? now()
                            ]);
                            // Refresh the relationship
                            $assignment->refresh();
                        } catch (\Exception $e) {
                            \Log::warning('Error syncing receipt_submitted flag: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
        
        // Refresh the model to get latest data from database (especially after payment updates)
        $imprestRequest->refresh();
        
        // Reload the relationships to get updated data with all necessary information
        $imprestRequest->load([
            'assignments.staff.primaryDepartment',
            'assignments.staff.primaryBankAccount',
            'assignments.receipts',
            'assignments.receipts.submittedBy',
            'assignments.receipts.verifiedBy',
            'receipts',
            'receipts.submittedBy',
            'receipts.verifiedBy',
            'receipts.assignment'
        ]);
        
        // Filter assignments to only show staff's own assignment if they are staff (not System Admin, Accountant, HOD, or CEO)
        // All approvers (HOD, CEO, Accountant, System Admin) should see ALL assignments with full details
        if ($isStaff && !$isSystemAdmin && !$isAccountant && !$user->hasAnyRole(['HOD', 'CEO', 'Director'])) {
            $imprestRequest->setRelation('assignments', $imprestRequest->assignments->filter(function($assignment) use ($user) {
                return $assignment && $assignment->staff_id === $user->id;
            }));
            // Also filter receipts to only show receipts from their assignment
            $imprestRequest->setRelation('receipts', $imprestRequest->receipts->filter(function($receipt) use ($user) {
                return $receipt && $receipt->assignment && $receipt->assignment->staff_id === $user->id;
            }));
        }

        // Get user roles for action buttons - System Admin and Accountant have all permissions
        $isHOD = $isSystemAdmin || $isAccountant || $user->hasAnyRole(['HOD']);
        $isCEO = $isSystemAdmin || $isAccountant || $user->hasAnyRole(['CEO', 'Director']);
        // $isAccountant already set above
        
        // Get staff members for assignment (if needed) - System Admin can always assign
        $staffMembers = collect();
        $staffWithUnretiredImprests = [];
        if (($isAccountant || $isSystemAdmin) && $imprestRequest->status === 'approved') {
            $staffMembers = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['Staff', 'Employee']);
            })->with('primaryDepartment')->get();
            
            // Get staff with unretired imprests
            $staffWithUnretiredImprests = ImprestAssignment::whereHas('imprestRequest', function($q) {
                    $q->where('status', '!=', 'completed');
                })
                ->with(['imprestRequest', 'staff'])
                ->get()
                ->groupBy('staff_id')
                ->map(function($assignments) {
                    return $assignments->map(function($assignment) {
                        return [
                            'staff_id' => $assignment->staff_id,
                            'staff_name' => $assignment->staff->name ?? 'Unknown',
                            'request_no' => $assignment->imprestRequest->request_no ?? 'N/A',
                            'status' => $assignment->imprestRequest->status ?? 'unknown',
                            'id' => $assignment->imprestRequest->id ?? null
                        ];
                    })->toArray();
                })
                ->toArray();
        }
        
        // Get bank accounts for payment (if needed) - System Admin and Accountant can always process payment
        $bankAccounts = collect();
        if ($isAccountant && in_array($imprestRequest->status, ['assigned', 'paid'])) {
            $bankAccounts = \App\Models\BankAccount::where('is_active', true)->orderBy('bank_name')->get();
        }
        
        // Return full page view
        return view('modules.finance.imprest-show', compact(
            'imprestRequest',
            'isHOD',
            'isCEO',
            'isAccountant',
            'isStaff',
            'isSystemAdmin',
            'staffMembers',
            'staffWithUnretiredImprests',
            'bankAccounts'
        ));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Imprest request not found.');
        } catch (\Exception $e) {
            \Log::error('Error showing imprest request: ' . $e->getMessage());
            abort(500, 'An error occurred while loading the imprest request.');
        }
    }

    public function hodApprove($id)
    {
        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        if (!$user->hasAnyRole(['HOD', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HOD or System Admin can approve requests.'
            ], 403);
        }

        try {
            $imprestRequest = ImprestRequest::findOrFail($id);
            
            // System Admin can approve at any level, others must wait for pending_hod
            if (!$isSystemAdmin && $imprestRequest->status !== 'pending_hod') {
                return response()->json([
                    'success' => false,
                    'message' => 'This request is not pending HOD approval'
                ], 400);
            }

            DB::beginTransaction();

            $imprestRequest->update([
                'status' => 'pending_ceo',
                'hod_approved_at' => now(),
                'hod_approved_by' => Auth::id()
            ]);

            DB::commit();

            // Log activity
            ActivityLogService::logAction('imprest_hod_approved', "HOD approved imprest request {$imprestRequest->request_no}", $imprestRequest, [
                'request_no' => $imprestRequest->request_no,
                'amount' => $imprestRequest->amount,
                'approved_by' => Auth::user()->name,
            ]);

            // Notify CEO for approval and alert accountant
            $link = route('imprest.show', ['id' => $imprestRequest->id]);
            $this->notificationService->notifyCEO(
                "Imprest request {$imprestRequest->request_no} approved by HOD, pending your approval",
                $link,
                'Imprest Pending CEO Approval'
            );
            $this->notificationService->notifyAccountant(
                "Imprest {$imprestRequest->request_no} moved to CEO approval",
                $link,
                'Imprest Status Update'
            );

            return response()->json([
                'success' => true,
                'message' => 'Imprest request approved by HOD'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error approving request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ceoApprove($id)
    {
        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        if (!$user->hasAnyRole(['CEO', 'Director', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only CEO/Director or System Admin can give final approval.'
            ], 403);
        }

        try {
            $imprestRequest = ImprestRequest::findOrFail($id);
            
            // System Admin can approve at any level, others must wait for pending_ceo
            if (!$isSystemAdmin && $imprestRequest->status !== 'pending_ceo') {
                return response()->json([
                    'success' => false,
                    'message' => 'This request is not pending CEO approval'
                ], 400);
            }

            DB::beginTransaction();

            $imprestRequest->update([
                'status' => 'approved',
                'ceo_approved_at' => now(),
                'ceo_approved_by' => Auth::id()
            ]);

            DB::commit();

            // Log activity
            ActivityLogService::logAction('imprest_ceo_approved', "CEO approved imprest request {$imprestRequest->request_no}", $imprestRequest, [
                'request_no' => $imprestRequest->request_no,
                'amount' => $imprestRequest->amount,
                'approved_by' => Auth::user()->name,
            ]);

            // Notify accountant to assign staff
            $link = route('imprest.show', ['id' => $imprestRequest->id]);
            $this->notificationService->notifyAccountant(
                "Imprest request {$imprestRequest->request_no} approved by CEO. Please assign staff members.",
                $link,
                'Imprest Approved - Assign Staff'
            );

            return response()->json([
                'success' => true,
                'message' => 'Imprest request approved by CEO'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error approving request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Assign Staff Page
     */
    public function assignStaffPage($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $imprestRequest = ImprestRequest::with(['assignments.staff', 'accountant'])->findOrFail($id);
        
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can assign staff at any level, others must wait for approved
        if (!$isSystemAdmin && $imprestRequest->status !== 'approved') {
            return redirect()->route('imprest.show', $id)->with('error', 'This request is not approved for staff assignment');
        }

        // Get all staff members
        $staffMembers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Staff', 'Employee']);
        })->with('primaryDepartment')->get();

        // Get staff with unretired imprests
        $staffWithUnretiredImprests = ImprestAssignment::whereHas('imprestRequest', function($q) {
                $q->where('status', '!=', 'completed');
            })
            ->with(['imprestRequest', 'staff'])
            ->get()
            ->groupBy('staff_id')
            ->map(function($assignments) {
                return $assignments->map(function($assignment) {
                    return [
                        'staff_id' => $assignment->staff_id,
                        'staff_name' => $assignment->staff->name ?? 'Unknown',
                        'request_no' => $assignment->imprestRequest->request_no ?? 'N/A',
                        'status' => $assignment->imprestRequest->status ?? 'unknown',
                        'id' => $assignment->imprestRequest->id ?? null
                    ];
                })->toArray();
            })
            ->toArray();

        // Get already assigned staff IDs
        $assignedStaffIds = $imprestRequest->assignments->pluck('staff_id')->toArray();

        return view('modules.finance.imprest-assign-staff', compact(
            'imprestRequest',
            'staffMembers',
            'staffWithUnretiredImprests',
            'assignedStaffIds'
        ));
    }

    public function assignStaff(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Accountant can assign staff.'
            ], 403);
        }

        $request->validate([
            'imprest_id' => 'required|exists:imprest_requests,id',
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'exists:users,id',
            'assignment_notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $imprestRequest = ImprestRequest::with('assignments')->findOrFail($request->imprest_id);

            $isSystemAdmin = $user->hasRole('System Admin');
            
            // System Admin can assign staff at any level, others must wait for approved
            if (!$isSystemAdmin && $imprestRequest->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'This request is not approved for staff assignment'
                ], 400);
            }

            // Prevent duplicate assignments
            $existingStaffIds = $imprestRequest->assignments->pluck('staff_id')->toArray();
            $newStaffIds = array_diff($request->staff_ids, $existingStaffIds);
            
            if (empty($newStaffIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected staff members are already assigned to this request'
                ], 400);
            }

            // Check if any staff members have unretired (incomplete) imprest assignments
            $staffWithUnretiredImprests = ImprestAssignment::whereIn('staff_id', $newStaffIds)
                ->whereHas('imprestRequest', function($q) {
                    $q->where('status', '!=', 'completed');
                })
                ->with(['imprestRequest', 'staff'])
                ->get();

            if ($staffWithUnretiredImprests->count() > 0) {
                $affectedStaff = [];
                $unretiredImprests = [];
                
                foreach ($staffWithUnretiredImprests as $assignment) {
                    $staffName = $assignment->staff->name ?? 'Unknown';
                    $requestNo = $assignment->imprestRequest->request_no ?? 'N/A';
                    $status = $assignment->imprestRequest->status ?? 'Unknown';
                    $imprestId = $assignment->imprestRequest->id;
                    
                    if (!isset($affectedStaff[$assignment->staff_id])) {
                        $affectedStaff[$assignment->staff_id] = [
                            'name' => $staffName,
                            'imprests' => []
                        ];
                    }
                    
                    $affectedStaff[$assignment->staff_id]['imprests'][] = [
                        'request_no' => $requestNo,
                        'status' => $status,
                        'id' => $imprestId
                    ];
                    
                    $unretiredImprests[] = [
                        'staff_id' => $assignment->staff_id,
                        'staff_name' => $staffName,
                        'request_no' => $requestNo,
                        'status' => ucwords(str_replace('_', ' ', $status)),
                        'imprest_id' => $imprestId
                    ];
                }

                $staffNames = collect($affectedStaff)->pluck('name')->implode(', ');
                $message = "The following staff members have unretired imprest assignments and must complete them before being assigned to a new imprest: {$staffNames}. Please ensure all previous imprests are retired (completed) before assigning new amounts.";

                // Notify accountant
                $link = route('imprest.index');
                $this->notificationService->notifyAccountant(
                    "Cannot assign staff to imprest {$imprestRequest->request_no}. Some staff have unretired imprests that must be completed first.",
                    $link,
                    'Imprest Assignment Blocked - Unretired Imprests'
                );

                // Notify affected staff members
                foreach ($affectedStaff as $staffId => $staffInfo) {
                    $imprestList = collect($staffInfo['imprests'])->pluck('request_no')->implode(', ');
                    $staffLink = route('imprest.index');
                    $this->notificationService->notify(
                        $staffId,
                        "You cannot be assigned to a new imprest because you have unretired imprest(s): {$imprestList}. Please complete and retire your previous imprest(s) first.",
                        $staffLink,
                        'Complete Previous Imprest Required'
                    );
                }

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'unretired_imprests' => $unretiredImprests,
                    'affected_staff' => array_values($affectedStaff)
                ], 400);
            }

            // Calculate amount per staff (excluding already assigned staff)
            $totalAssigned = $imprestRequest->assignments->count();
            $newAssignmentsCount = count($newStaffIds);
            $amountPerStaff = $imprestRequest->amount / ($totalAssigned + $newAssignmentsCount);
            
            // Update existing assignments if adding new staff (recalculate amounts)
            if ($totalAssigned > 0) {
                foreach ($imprestRequest->assignments as $assignment) {
                    $assignment->update(['assigned_amount' => $amountPerStaff]);
                }
            }

            // Create assignments for each new staff member
            foreach ($newStaffIds as $staffId) {
                ImprestAssignment::create([
                    'imprest_request_id' => $imprestRequest->id,
                    'staff_id' => $staffId,
                    'assigned_amount' => $amountPerStaff,
                    'assignment_notes' => $request->assignment_notes,
                    'assigned_by' => Auth::id(),
                    'assigned_at' => now()
                ]);
            }

            $imprestRequest->update([
                'status' => 'assigned'
            ]);

            // Notify accountant that staff are assigned and ready for payment
            $link = route('imprest.show', ['id' => $imprestRequest->id]);
            $this->notificationService->notifyAccountant(
                "Staff assigned for imprest {$imprestRequest->request_no}. Proceed with payment.",
                $link,
                'Staff Assigned - Ready for Payment'
            );

            DB::commit();

            // Log activity
            $assignedStaffNames = User::whereIn('id', $newStaffIds)->pluck('name')->implode(', ');
            ActivityLogService::logAction('imprest_staff_assigned', "Assigned staff to imprest request {$imprestRequest->request_no}", $imprestRequest, [
                'request_no' => $imprestRequest->request_no,
                'assigned_staff_ids' => $newStaffIds,
                'assigned_staff_names' => $assignedStaffNames,
                'amount_per_staff' => $amountPerStaff,
                'total_assigned' => count($newStaffIds) * $amountPerStaff,
            ]);

            // Notify newly assigned staff only
            $link = route('imprest.show', ['id' => $imprestRequest->id]);
            foreach ($newStaffIds as $sid) {
                $this->notificationService->notify(
                    $sid,
                    "You have been assigned to imprest {$imprestRequest->request_no}. Submit receipts after payment.",
                    $link,
                    'Imprest Assignment'
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Staff assigned successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error assigning staff: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:bank_transfer,mobile_money,cash',
            'payment_date' => 'required|date',
            'payment_reference' => 'nullable|string',
            'payment_notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Get all assigned imprest requests (must have staff assigned first)
            $assignedRequests = ImprestRequest::where('status', 'assigned')
                ->whereHas('assignments')
                ->get();

            if ($assignedRequests->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No assigned requests found for bulk payment'
                ], 400);
            }

            foreach ($assignedRequests as $imprestRequest) {
                // Update payment info directly on the request
                $imprestRequest->update([
                    'status' => 'paid',
                    'paid_at' => $request->payment_date ? \Carbon\Carbon::parse($request->payment_date) : now(),
                    'payment_method' => $request->payment_method,
                    'payment_reference' => $request->payment_reference,
                    'payment_notes' => $request->payment_notes,
                ]);

                // Notify assigned staff to submit receipts
                $link = route('imprest.show', ['id' => $imprestRequest->id]);
                $assignedUserIds = $imprestRequest->assignments()->pluck('staff_id')->toArray();
                if (!empty($assignedUserIds)) {
                    $this->notificationService->notify(
                        $assignedUserIds,
                        "Imprest {$imprestRequest->request_no} has been paid. Please submit your receipts.",
                        $link,
                        'Imprest Paid - Receipt Submission Required'
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bulk payment processed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAssignment($id)
    {
        $user = Auth::user();
        $assignment = ImprestAssignment::with(['imprestRequest', 'receipts', 'staff'])
            ->findOrFail($id);
        
        // Check if assignment belongs to current user (for staff)
        if ($user->hasAnyRole(['Staff', 'Employee']) && $assignment->staff_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this assignment'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'assignment' => $assignment
        ]);
    }

    /**
     * Show View Receipts Page (for Accountant/Admin verification)
     */
    public function viewReceiptsPage($assignmentId)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }
        
        $assignment = ImprestAssignment::with([
            'imprestRequest',
            'receipts.submittedBy',
            'receipts.verifiedBy',
            'staff'
        ])->findOrFail($assignmentId);

        return view('modules.finance.imprest-view-receipts', compact('assignment'));
    }

    /**
     * Show View My Receipts Page
     */
    public function viewMyReceiptsPage($assignmentId)
    {
        $user = Auth::user();
        
        $assignment = ImprestAssignment::with([
            'imprestRequest',
            'receipts.submittedBy',
            'receipts.verifiedBy',
            'staff'
        ])->findOrFail($assignmentId);
        
        // Check if assignment belongs to current user (for staff)
        if ($user->hasAnyRole(['Staff', 'Employee']) && $assignment->staff_id !== $user->id) {
            abort(403, 'You are not assigned to this imprest request.');
        }

        return view('modules.finance.imprest-my-receipts', compact('assignment'));
    }

    /**
     * Show Submit Receipt Page
     */
    public function submitReceiptPage($assignmentId)
    {
        $user = Auth::user();
        
        $assignment = ImprestAssignment::with(['imprestRequest', 'staff', 'receipts'])->findOrFail($assignmentId);
        
        // Check if assignment belongs to current user (for staff)
        if ($user->hasAnyRole(['Staff', 'Employee']) && $assignment->staff_id !== $user->id) {
            abort(403, 'You are not assigned to this imprest request.');
        }
        
        // Check if imprest is paid
        if ($assignment->imprestRequest->status !== 'paid') {
            return redirect()->route('imprest.show', $assignment->imprestRequest->id)
                ->with('error', 'This imprest request has not been paid yet.');
        }

        return view('modules.finance.imprest-submit-receipt', compact('assignment'));
    }

    public function submitReceipt(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:imprest_assignments,id',
            'receipt_amount' => 'required|numeric|min:0',
            'receipt_description' => 'required|string',
            'receipt_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $assignment = ImprestAssignment::findOrFail($request->assignment_id);

            // Check if assignment belongs to current user
            if ($assignment->staff_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to this imprest request'
                ], 403);
            }

            // Check if imprest is paid
            if ($assignment->imprestRequest->status !== 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This imprest request has not been paid yet'
                ], 400);
            }

            // Upload receipt file with better error handling
            $file = $request->file('receipt_file');
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file uploaded. Please try again.'
                ], 400);
            }
            
            // Sanitize filename - remove special characters
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
            $filePath = $file->storeAs('imprest_receipts', $fileName, 'public');
            
            if (!$filePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload file. Please check storage permissions.'
                ], 500);
            }

            // Check if this is first receipt submission BEFORE creating
            $isFirstReceipt = $assignment->receipts()->count() === 0;
            
            // Create receipt record
            ImprestReceipt::create([
                'assignment_id' => $assignment->id,
                'receipt_amount' => $request->receipt_amount,
                'receipt_description' => $request->receipt_description,
                'receipt_file_path' => $filePath,
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
                'is_verified' => false
            ]);
            
            // Always update assignment receipt_submitted flag if receipts exist
            // This ensures the flag is set correctly even if it was missed before
            if (!$assignment->receipt_submitted || $isFirstReceipt) {
                $assignment->update([
                    'receipt_submitted' => true,
                    'receipt_submitted_at' => $isFirstReceipt ? now() : ($assignment->receipt_submitted_at ?? now())
                ]);
            }
            
            // Refresh the assignment to ensure relationships are up to date
            $assignment->refresh();

            // Check if all assignments have submitted receipts
            $imprestRequest = $assignment->imprestRequest;
            $allReceiptsSubmitted = $imprestRequest->assignments()
                ->where('receipt_submitted', false)->count() === 0;

            // Update imprest status to awaiting verification if all receipts submitted
            if ($allReceiptsSubmitted) {
                $imprestRequest->update([
                    'status' => 'pending_receipt_verification'
                ]);
            } elseif ($imprestRequest->status === 'paid') {
                // Keep status as paid if not all receipts submitted yet
                // Status remains 'paid' until all staff submit receipts
            }

            DB::commit();

            // Log activity
            $receipt = ImprestReceipt::where('assignment_id', $assignment->id)->latest()->first();
            if ($receipt) {
                ActivityLogService::logAction('imprest_receipt_submitted', "Submitted receipt for imprest assignment #{$assignment->id}", $receipt, [
                    'assignment_id' => $assignment->id,
                    'imprest_request_no' => $assignment->imprestRequest->request_no,
                    'receipt_amount' => $request->receipt_amount,
                    'receipt_description' => $request->receipt_description,
                    'submitted_by' => Auth::user()->name,
                ]);
            }

            // Notify accountant of receipt submission
            $link = route('imprest.show', ['id' => $assignment->imprest_request_id]);
            $this->notificationService->notifyAccountant(
                "Receipt submitted for imprest {$assignment->imprestRequest->request_no} by {$assignment->staff->name}",
                $link,
                'Imprest Receipt Submitted'
            );

            // Check if all receipts submitted - notify accountant
            $allReceiptsSubmitted = $assignment->imprestRequest->assignments()
                ->where('receipt_submitted', false)->count() === 0;
                
            if ($allReceiptsSubmitted) {
                $this->notificationService->notifyAccountant(
                    "All receipts submitted for imprest {$assignment->imprestRequest->request_no}. Please verify all receipts.",
                    $link,
                    'Imprest Receipts Ready for Verification'
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Receipt submitted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error submitting receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generatePDF($id)
    {
        $imprestRequest = ImprestRequest::with([
            'accountant',
            'assignments.staff',
            'assignments.receipts',
            'receipts',
            'receipts.submittedBy',
            'receipts.verifiedBy',
            'hodApproval',
            'ceoApproval'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('modules.finance.imprest-pdf', compact('imprestRequest'));
        
        return $pdf->download('imprest_report_' . $imprestRequest->request_no . '.pdf');
    }

    public function verifyReceipt(Request $request, $receiptId)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Accountant can verify receipts.'
            ], 403);
        }

        $request->validate([
            'action' => 'required|in:approve,reject',
            'verification_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $receipt = ImprestReceipt::with('assignment.imprestRequest')->findOrFail($receiptId);

            if ($receipt->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'This receipt has already been verified'
                ], 400);
            }

            $receipt->update([
                'is_verified' => $request->action === 'approve',
                'verified_at' => $request->action === 'approve' ? now() : null,
                'verified_by' => $request->action === 'approve' ? Auth::id() : null,
                'verification_notes' => $request->verification_notes
            ]);

            // Check if all receipts are verified
            $imprestRequest = $receipt->assignment->imprestRequest;
            
            // Ensure all assignments have submitted receipts
            $allReceiptsSubmitted = $imprestRequest->assignments()
                ->where('receipt_submitted', false)->count() === 0;
            
            // Check if all submitted receipts are verified
            $totalReceipts = $imprestRequest->receipts()->count();
            $verifiedReceipts = $imprestRequest->receipts()
                ->where('is_verified', true)->count();
            
            $allReceiptsVerified = $allReceiptsSubmitted && 
                                   $totalReceipts > 0 && 
                                   $verifiedReceipts === $totalReceipts;

            $link = route('imprest.show', ['id' => $imprestRequest->id]);
            
            // Only complete if all receipts submitted AND all verified (approved)
            if ($allReceiptsVerified && $request->action === 'approve') {
                $imprestRequest->update([
                    'status' => 'completed',
                    'completed_at' => now()
                ]);
                
                // Notify all assigned staff
                $assignedStaffIds = $imprestRequest->assignments()->pluck('staff_id')->toArray();
                foreach ($assignedStaffIds as $staffId) {
                    $this->notificationService->notify(
                        $staffId,
                        "Imprest request {$imprestRequest->request_no} has been completed. All receipts verified.",
                        $link,
                        'Imprest Request Completed'
                    );
                }
            } elseif ($request->action === 'reject') {
                // When a receipt is rejected, ensure status remains pending_receipt_verification
                // This allows staff to resubmit receipts
                if ($imprestRequest->status !== 'pending_receipt_verification') {
                    // If status somehow changed, revert it back
                    $imprestRequest->update([
                        'status' => 'pending_receipt_verification'
                    ]);
                }
            }
            // If action is approve but not all verified, status remains pending_receipt_verification

            DB::commit();

            // Log activity
            ActivityLogService::logAction('imprest_receipt_verified', ucfirst($request->action) . " receipt for imprest request {$imprestRequest->request_no}", $receipt, [
                'receipt_id' => $receipt->id,
                'imprest_request_no' => $imprestRequest->request_no,
                'action' => $request->action,
                'receipt_amount' => $receipt->receipt_amount,
                'verification_notes' => $request->verification_notes ?? null,
                'verified_by' => Auth::user()->name,
                'all_receipts_verified' => $allReceiptsVerified,
            ]);

            if ($request->action === 'approve') {
                $this->notificationService->notify(
                    $receipt->submitted_by,
                    "Your receipt for imprest {$imprestRequest->request_no} has been verified and approved.",
                    $link,
                    'Receipt Verified'
                );

                if ($allReceiptsVerified) {
                    $this->notificationService->notifyAccountant(
                        "All receipts verified for imprest {$imprestRequest->request_no}. Request completed.",
                        $link,
                        'Imprest Completed'
                    );
                }
            } else {
                $this->notificationService->notify(
                    $receipt->submitted_by,
                    "Your receipt for imprest {$imprestRequest->request_no} was rejected. Please review and resubmit.",
                    $link,
                    'Receipt Rejected'
                );
            }

            return response()->json([
                'success' => true,
                'message' => $request->action === 'approve' ? 'Receipt verified successfully' : 'Receipt rejected',
                'all_verified' => $allReceiptsVerified
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error verifying receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getReceiptDetails($receiptId)
    {
        try {
            $receipt = ImprestReceipt::with([
                'assignment.imprestRequest',
                'assignment.staff',
                'submittedBy',
                'verifiedBy'
            ])->findOrFail($receiptId);

            return response()->json([
                'success' => true,
                'receipt' => [
                    'id' => $receipt->id,
                    'receipt_amount' => $receipt->receipt_amount,
                    'receipt_description' => $receipt->receipt_description,
                    'receipt_file_path' => $receipt->receipt_file_path,
                    'submitted_at' => $receipt->submitted_at ? $receipt->submitted_at->format('d M Y, H:i') : null,
                    'submitted_by' => $receipt->submittedBy ? $receipt->submittedBy->name : 'N/A',
                    'is_verified' => $receipt->is_verified,
                    'verified_at' => $receipt->verified_at ? $receipt->verified_at->format('d M Y, H:i') : null,
                    'verified_by' => $receipt->verifiedBy ? $receipt->verifiedBy->name : null,
                    'verification_notes' => $receipt->verification_notes,
                    'assignment' => [
                        'id' => $receipt->assignment->id,
                        'assigned_amount' => $receipt->assignment->assigned_amount,
                        'staff_name' => $receipt->assignment->staff->name ?? 'N/A',
                    ],
                    'imprest_request' => [
                        'id' => $receipt->assignment->imprestRequest->id,
                        'request_no' => $receipt->assignment->imprestRequest->request_no,
                        'purpose' => $receipt->assignment->imprestRequest->purpose,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading receipt details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Payment Page
     */
    public function paymentPage($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $imprestRequest = ImprestRequest::with([
            'assignments.staff.primaryBankAccount', 
            'assignments.bankAccount', 
            'accountant'
        ])->findOrFail($id);
        
        // Allow access if status is 'assigned' or 'paid' (for partial payments)
        if (!in_array($imprestRequest->status, ['assigned', 'paid'])) {
            return redirect()->route('imprest.show', $id)->with('error', 'This request is not ready for payment. Staff must be assigned first.');
        }

        // Get bank accounts for payment (organization bank accounts)
        $bankAccounts = \App\Models\BankAccount::where('is_active', true)
            ->whereNull('user_id') // Organization bank accounts (not employee accounts)
            ->orderBy('bank_name')
            ->get();

        return view('modules.finance.imprest-payment', compact(
            'imprestRequest',
            'bankAccounts'
        ));
    }

    public function processPayment(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Accountant can process payments.'
            ], 403);
        }

        $paymentMode = $request->input('payment_mode', 'manual');
        
        try {
            if ($paymentMode === 'bulk') {
                // Validate bulk payment
                $request->validate([
                    'bulk_paid_amount' => 'required|numeric|min:0.01',
                    'bulk_payment_method' => 'required|in:bank_transfer,mobile_money,cash',
                    'bulk_payment_date' => 'required|date',
                    'bulk_payment_reference' => 'nullable|string|max:255',
                    'bulk_payment_notes' => 'nullable|string',
                    'bulk_bank_account_id' => 'nullable|exists:bank_accounts,id',
                    'bulk_bank_name' => 'nullable|string|max:255',
                    'bulk_account_number' => 'nullable|string|max:255',
                    'bulk_mobile_number' => 'nullable|string|max:255',
                    'bulk_mobile_provider' => 'nullable|string|max:255',
                ]);
            } else {
                // Validate manual payments
                $request->validate([
                    'assignments' => 'required|array|min:1',
                    'assignments.*.assignment_id' => 'required|exists:imprest_assignments,id',
                    'assignments.*.staff_id' => 'required|exists:users,id',
                    'assignments.*.paid_amount' => 'required|numeric|min:0.01',
                    'assignments.*.payment_method' => 'required|in:bank_transfer,mobile_money,cash',
                    'assignments.*.payment_date' => 'required|date',
                    'assignments.*.payment_reference' => 'nullable|string|max:255',
                    'assignments.*.payment_notes' => 'nullable|string',
                    'assignments.*.bank_account_id' => 'nullable|exists:bank_accounts,id',
                    'assignments.*.bank_name' => 'nullable|string|max:255',
                    'assignments.*.account_number' => 'nullable|string|max:255',
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $e->errors()),
                'errors' => $e->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $imprestRequest = ImprestRequest::with('assignments')->findOrFail($id);

            if ($imprestRequest->status !== 'assigned') {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This request is not ready for payment. Staff must be assigned first. Current status: ' . $imprestRequest->status
                ], 400);
            }

            // Verify that staff are actually assigned
            if ($imprestRequest->assignments->count() === 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No staff members assigned to this request. Please assign staff first.'
                ], 400);
            }

            // Handle bulk payment
            if ($paymentMode === 'bulk') {
                return $this->processBulkPayment($request, $imprestRequest);
            }

            // Validate total payment amount doesn't exceed request amount (manual mode)
            $totalPaidAmount = 0;
            foreach ($request->assignments as $assignmentData) {
                $totalPaidAmount += floatval($assignmentData['paid_amount']);
            }

            if ($totalPaidAmount > $imprestRequest->amount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Total payment amount (TZS ' . number_format($totalPaidAmount, 2) . ') exceeds request amount (TZS ' . number_format($imprestRequest->amount, 2) . ')'
                ], 400);
            }

            $requestNo = $imprestRequest->request_no;
            $processedCount = 0;
            $totalProcessedAmount = 0;

            // Find or create expense account for imprest
            $expenseAccount = \App\Models\ChartOfAccount::where('code', 'IMPREST')
                ->orWhere('name', 'like', '%Imprest%')
                ->where('type', 'Expense')
                ->first();
            
            if (!$expenseAccount) {
                $expenseAccount = \App\Models\ChartOfAccount::firstOrCreate(
                    ['code' => 'IMPREST'],
                    [
                        'name' => 'Imprest Expense',
                        'type' => 'Expense',
                        'category' => 'Operating Expense',
                        'is_active' => true,
                    ]
                );
            }

            // Process each assignment payment
            foreach ($request->assignments as $assignmentData) {
                $assignment = ImprestAssignment::findOrFail($assignmentData['assignment_id']);
                
                // Verify assignment belongs to this imprest request
                if ($assignment->imprest_request_id != $imprestRequest->id) {
                    continue;
                }

                // Parse payment date
                try {
                    $paymentDate = \Carbon\Carbon::parse($assignmentData['payment_date']);
                } catch (\Exception $e) {
                    continue;
                }

                $paidAmount = floatval($assignmentData['paid_amount']);
                $paymentMethod = $assignmentData['payment_method'];

                // Validate bank transfer fields
                if ($paymentMethod === 'bank_transfer') {
                    if (empty($assignmentData['bank_account_id']) && (empty($assignmentData['bank_name']) || empty($assignmentData['account_number']))) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Bank details are required for bank transfer payment for ' . ($assignment->staff->name ?? 'staff')
                        ], 400);
                    }
                }

                // Update assignment with payment details
                $assignment->update([
                    'paid_amount' => $paidAmount,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $paymentDate,
                    'payment_reference' => $assignmentData['payment_reference'] ?? null,
                    'payment_notes' => $assignmentData['payment_notes'] ?? null,
                    'bank_account_id' => $assignmentData['bank_account_id'] ?? null,
                    'bank_name' => $assignmentData['bank_name'] ?? null,
                    'account_number' => $assignmentData['account_number'] ?? null,
                    'paid_at' => now(),
                    'paid_by' => Auth::id(),
                ]);

                // Find or create cash/bank account based on payment method
                $cashAccount = null;
                if ($paymentMethod === 'cash') {
                    $cashAccount = \App\Models\ChartOfAccount::where('code', 'CASH')
                        ->orWhere('name', 'like', '%Cash%')
                        ->where('type', 'Asset')
                        ->first();
                    
                    if (!$cashAccount) {
                        $cashAccount = \App\Models\ChartOfAccount::firstOrCreate(
                            ['code' => 'CASH'],
                            [
                                'name' => 'Cash on Hand',
                                'type' => 'Asset',
                                'category' => 'Current Asset',
                                'is_active' => true,
                            ]
                        );
                    }
                } else {
                    // For bank/mobile money
                    $cashAccount = \App\Models\ChartOfAccount::where('code', 'BANK')
                        ->orWhere('name', 'like', '%Bank%')
                        ->where('type', 'Asset')
                        ->first();
                    
                    if (!$cashAccount) {
                        $cashAccount = \App\Models\ChartOfAccount::firstOrCreate(
                            ['code' => 'BANK'],
                            [
                                'name' => 'Bank Account',
                                'type' => 'Asset',
                                'category' => 'Current Asset',
                                'is_active' => true,
                            ]
                        );
                    }
                }

                if (!$expenseAccount || !$cashAccount) {
                    throw new \Exception('Required Chart of Accounts not found. Please set up Expense and Cash/Bank accounts in Chart of Accounts.');
                }

                // Create General Ledger entries for this payment
                $staffName = $assignment->staff->name ?? 'Staff';
                $description = "Imprest Payment - {$requestNo} - {$staffName}: {$imprestRequest->purpose}";
                
                // Debit: Expense Account (increases expense)
                \App\Models\GeneralLedger::create([
                    'account_id' => $expenseAccount->id,
                    'transaction_date' => $paymentDate,
                    'reference_type' => 'ImprestAssignment',
                    'reference_id' => $assignment->id,
                    'reference_no' => $requestNo . '-' . $assignment->id,
                    'type' => 'Debit',
                    'amount' => $paidAmount,
                    'description' => $description,
                    'source' => 'imprest',
                    'created_by' => Auth::id(),
                ]);
                
                // Credit: Cash/Bank Account (decreases cash/bank)
                \App\Models\GeneralLedger::create([
                    'account_id' => $cashAccount->id,
                    'transaction_date' => $paymentDate,
                    'reference_type' => 'ImprestAssignment',
                    'reference_id' => $assignment->id,
                    'reference_no' => $requestNo . '-' . $assignment->id,
                    'type' => 'Credit',
                    'amount' => $paidAmount,
                    'description' => $description,
                    'source' => 'imprest',
                    'created_by' => Auth::id(),
                ]);

                $processedCount++;
                $totalProcessedAmount += $paidAmount;
            }

            // Reload assignments to get updated data - refresh from database
            $imprestRequest->refresh();
            
            // Reload assignments from database to get fresh payment data
            $imprestRequest->load(['assignments' => function($query) {
                $query->with(['staff', 'receipts']);
            }]);
            
            // Also refresh each assignment individually to ensure we have latest data
            foreach ($imprestRequest->assignments as $assignment) {
                $assignment->refresh();
            }
            
            // Check if all assignments are paid (check the collection, not the database query)
            $allAssignmentsPaid = $imprestRequest->assignments->every(function($assignment) {
                return !is_null($assignment->paid_at) && !is_null($assignment->paid_amount) && $assignment->paid_amount > 0;
            });
            
            if ($allAssignmentsPaid) {
                // Update imprest request status to paid
                $imprestRequest->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => 'multiple', // Multiple payment methods used
                    'payment_reference' => 'Multiple payments processed',
                ]);
            } else {
                // Update status to 'paid' if at least one payment was processed (for partial payments)
                if ($processedCount > 0) {
                    $imprestRequest->update([
                        'status' => 'paid', // Allow partial payments
                    ]);
                }
            }

            DB::commit();

            // Log activity
            \App\Services\ActivityLogService::logAction('imprest_payment_processed', "Processed {$processedCount} payment(s) for imprest request {$imprestRequest->request_no}", $imprestRequest, [
                'request_no' => $imprestRequest->request_no,
                'total_amount' => $totalProcessedAmount,
                'processed_count' => $processedCount,
                'all_paid' => $allAssignmentsPaid,
            ]);

            // Notify assigned staff to submit receipts
            try {
                $link = route('imprest.show', ['id' => $imprestRequest->id]);
                $paidStaffIds = [];
                foreach ($request->assignments as $assignmentData) {
                    $paidStaffIds[] = $assignmentData['staff_id'];
                }
                
                if (!empty($paidStaffIds)) {
                    foreach (array_unique($paidStaffIds) as $userId) {
                        $this->notificationService->notify(
                            $userId,
                            "Your imprest payment for {$imprestRequest->request_no} has been processed. Please submit your receipts.",
                            $link,
                            'Imprest Paid - Receipt Submission Required',
                            ['skip_sms' => true] // Skip SMS for payment notifications
                        );
                    }
                }
            } catch (\Exception $e) {
                // Log notification error but don't fail the payment
                \Log::warning('Failed to send payment notification: ' . $e->getMessage());
            }

            $message = "Successfully processed {$processedCount} payment(s) totaling TZS " . number_format($totalProcessedAmount, 2);
            if (!$allAssignmentsPaid) {
                $message .= ". Some assignments are still pending payment.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'processed_count' => $processedCount,
                'total_amount' => $totalProcessedAmount,
                'all_paid' => $allAssignmentsPaid,
                'redirect_url' => route('imprest.show', ['id' => $imprestRequest->id]),
                'data' => [
                    'request_no' => $imprestRequest->request_no,
                    'status' => $imprestRequest->status,
                    'paid_at' => $imprestRequest->paid_at,
                    'assignments_count' => $imprestRequest->assignments->count(),
                    'paid_assignments_count' => $imprestRequest->assignments->whereNotNull('paid_at')->count(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Imprest request not found.'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Payment processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_id' => $id,
                'user_id' => Auth::id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportPDF()
    {
        $imprestRequests = ImprestRequest::with([
            'accountant',
            'assignments.staff',
            'receipts'
        ])->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('modules.finance.imprest-export-pdf', compact('imprestRequests'));
        
        return $pdf->download('imprest_export_' . date('Y-m-d') . '.pdf');
    }

    public function verificationPage(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized. Only Accountant can access verification page.');
        }

        // Get all imprest requests with pending receipt verification
        $query = ImprestRequest::where('status', 'pending_receipt_verification')
            ->with([
                'accountant',
                'assignments.staff',
                'assignments.receipts.submittedBy',
                'assignments.receipts.verifiedBy',
                'receipts.submittedBy',
                'receipts.verifiedBy',
                'receipts.assignment'
            ])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('request_no')) {
            $query->where('request_no', 'like', '%' . $request->request_no . '%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $imprestRequests = $query->paginate(20);

        // Get all unverified receipts for quick access
        $unverifiedReceipts = ImprestReceipt::where('is_verified', false)
            ->with([
                'assignment.imprestRequest',
                'assignment.staff',
                'submittedBy'
            ])
            ->orderBy('submitted_at', 'desc')
            ->get();

        // Statistics
        $stats = [
            'pending_verification' => ImprestRequest::where('status', 'pending_receipt_verification')->count(),
            'unverified_receipts' => ImprestReceipt::where('is_verified', false)->count(),
            'verified_today' => ImprestReceipt::where('is_verified', true)
                ->whereDate('verified_at', today())
                ->count(),
        ];

        return view('modules.finance.imprest-verification', compact(
            'imprestRequests',
            'unverifiedReceipts',
            'stats'
        ));
    }

    public function bulkVerifyReceipts(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Accountant can verify receipts.'
            ], 403);
        }

        $request->validate([
            'receipt_ids' => 'required|array|min:1',
            'receipt_ids.*' => 'exists:imprest_receipts,id',
            'action' => 'required|in:approve,reject',
            'verification_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $receipts = ImprestReceipt::whereIn('id', $request->receipt_ids)
                ->where('is_verified', false)
                ->with('assignment.imprestRequest')
                ->get();

            if ($receipts->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'No unverified receipts found to process'
                ], 400);
            }

            $verifiedCount = 0;
            $rejectedCount = 0;
            $completedImprests = [];

            foreach ($receipts as $receipt) {
                $receipt->update([
                    'is_verified' => $request->action === 'approve',
                    'verified_at' => $request->action === 'approve' ? now() : null,
                    'verified_by' => $request->action === 'approve' ? Auth::id() : null,
                    'verification_notes' => $request->verification_notes
                ]);

                if ($request->action === 'approve') {
                    $verifiedCount++;
                } else {
                    $rejectedCount++;
                }

                // Check if all receipts for this imprest are verified
                $imprestRequest = $receipt->assignment->imprestRequest;
                
                // Ensure all assignments have submitted receipts
                $allReceiptsSubmitted = $imprestRequest->assignments()
                    ->where('receipt_submitted', false)->count() === 0;
                
                // Check if all submitted receipts are verified
                $totalReceipts = $imprestRequest->receipts()->count();
                $verifiedReceipts = $imprestRequest->receipts()
                    ->where('is_verified', true)->count();
                
                $allReceiptsVerified = $allReceiptsSubmitted && 
                                       $totalReceipts > 0 && 
                                       $verifiedReceipts === $totalReceipts;

                if ($allReceiptsVerified && $request->action === 'approve' && !in_array($imprestRequest->id, $completedImprests)) {
                    $imprestRequest->update([
                        'status' => 'completed',
                        'completed_at' => now()
                    ]);
                    
                    $completedImprests[] = $imprestRequest->id;

                    // Notify all assigned staff
                    $assignedStaffIds = $imprestRequest->assignments()->pluck('staff_id')->toArray();
                    $link = route('imprest.show', ['id' => $imprestRequest->id]);
                    foreach ($assignedStaffIds as $staffId) {
                        $this->notificationService->notify(
                            $staffId,
                            "Imprest request {$imprestRequest->request_no} has been completed. All receipts verified.",
                            $link,
                            'Imprest Request Completed'
                        );
                    }
                }
            }

            DB::commit();

            // Notify users about their receipt verification
            $link = route('imprest.verification');
            foreach ($receipts as $receipt) {
                if ($request->action === 'approve') {
                    $this->notificationService->notify(
                        $receipt->submitted_by,
                        "Your receipt for imprest {$receipt->assignment->imprestRequest->request_no} has been verified and approved.",
                        $link,
                        'Receipt Verified'
                    );
                } else {
                    $this->notificationService->notify(
                        $receipt->submitted_by,
                        "Your receipt for imprest {$receipt->assignment->imprestRequest->request_no} was rejected. Please review and resubmit.",
                        $link,
                        'Receipt Rejected'
                    );
                }
            }

            // Notify accountant if imprests completed
            if (!empty($completedImprests)) {
                $completedCount = count($completedImprests);
                $this->notificationService->notifyAccountant(
                    "{$completedCount} imprest request(s) completed after bulk verification.",
                    $link,
                    'Imprest Requests Completed'
                );
            }

            return response()->json([
                'success' => true,
                'message' => $request->action === 'approve' 
                    ? "Successfully verified {$verifiedCount} receipt(s)" 
                    : "Successfully rejected {$rejectedCount} receipt(s)",
                'verified_count' => $verifiedCount,
                'rejected_count' => $rejectedCount,
                'completed_imprests' => count($completedImprests)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error processing bulk verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process individual payment for a single assignment
     */
    public function processIndividualPayment(Request $request, $id, $assignmentId)
    {
        try {
            $user = Auth::user();
            
            // Check authorization
            if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Only Accountants and System Admins can process payments.'
                ], 403);
            }

            // Validate request
            $request->validate([
                'paid_amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:bank_transfer,mobile_money,cash',
                'payment_date' => 'required|date',
                'payment_reference' => 'nullable|string|max:255',
                'payment_notes' => 'nullable|string',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'bank_name' => 'nullable|string|max:255',
                'account_number' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();

            $imprestRequest = ImprestRequest::with('assignments')->findOrFail($id);
            $assignment = ImprestAssignment::with('staff')->findOrFail($assignmentId);

            // Verify assignment belongs to this imprest request
            if ($assignment->imprest_request_id != $imprestRequest->id) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Assignment does not belong to this imprest request.'
                ], 400);
            }

            // Check if already paid
            if ($assignment->paid_at && $assignment->paid_amount > 0) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This assignment has already been paid.'
                ], 400);
            }

            // Validate payment method specific fields
            $paymentMethod = $request->payment_method;
            $paidAmount = floatval($request->paid_amount);
            $paymentDate = \Carbon\Carbon::parse($request->payment_date);

            if ($paymentMethod === 'bank_transfer') {
                if (empty($request->bank_account_id) && (empty($request->bank_name) || empty($request->account_number))) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Bank details are required for bank transfer payment'
                    ], 400);
                }
            } else if ($paymentMethod === 'mobile_money') {
                if (empty($request->account_number)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Mobile number is required for mobile money payment'
                    ], 400);
                }
                if (empty($request->bank_name)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Mobile money provider is required'
                    ], 400);
                }
            }

            // Validate amount doesn't exceed assigned amount
            if ($paidAmount > $assignment->assigned_amount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount (TZS ' . number_format($paidAmount, 2) . ') exceeds assigned amount (TZS ' . number_format($assignment->assigned_amount, 2) . ')'
                ], 400);
            }

            // Find or create expense account
            $expenseAccount = \App\Models\ChartOfAccount::where('code', 'IMPREST')
                ->orWhere('name', 'like', '%Imprest%')
                ->where('type', 'Expense')
                ->first();
            
            if (!$expenseAccount) {
                $expenseAccount = \App\Models\ChartOfAccount::firstOrCreate(
                    ['code' => 'IMPREST'],
                    [
                        'name' => 'Imprest Expense',
                        'type' => 'Expense',
                        'category' => 'Operating Expense',
                        'is_active' => true,
                    ]
                );
            }

            // Find or create cash/bank account based on payment method
            $cashAccount = null;
            if ($paymentMethod === 'cash') {
                $cashAccount = \App\Models\ChartOfAccount::where('code', 'CASH')
                    ->orWhere('name', 'like', '%Cash%')
                    ->where('type', 'Asset')
                    ->first();
                
                if (!$cashAccount) {
                    $cashAccount = \App\Models\ChartOfAccount::firstOrCreate(
                        ['code' => 'CASH'],
                        [
                            'name' => 'Cash on Hand',
                            'type' => 'Asset',
                            'category' => 'Current Asset',
                            'is_active' => true,
                        ]
                    );
                }
            } else {
                $cashAccount = \App\Models\ChartOfAccount::where('code', 'BANK')
                    ->orWhere('name', 'like', '%Bank%')
                    ->where('type', 'Asset')
                    ->first();
                
                if (!$cashAccount) {
                    $cashAccount = \App\Models\ChartOfAccount::firstOrCreate(
                        ['code' => 'BANK'],
                        [
                            'name' => 'Bank Account',
                            'type' => 'Asset',
                            'category' => 'Current Asset',
                            'is_active' => true,
                        ]
                    );
                }
            }

            if (!$expenseAccount || !$cashAccount) {
                throw new \Exception('Required Chart of Accounts not found. Please set up Expense and Cash/Bank accounts in Chart of Accounts.');
            }

            // Update assignment with payment details
            $updateData = [
                'paid_amount' => $paidAmount,
                'payment_method' => $paymentMethod,
                'payment_date' => $paymentDate,
                'payment_reference' => $request->payment_reference ?? null,
                'payment_notes' => $request->payment_notes ?? null,
                'paid_at' => now(),
                'paid_by' => Auth::id(),
            ];

            if ($paymentMethod === 'bank_transfer') {
                $updateData['bank_account_id'] = $request->bank_account_id ?? null;
                $updateData['bank_name'] = $request->bank_name ?? null;
                $updateData['account_number'] = $request->account_number ?? null;
            } else if ($paymentMethod === 'mobile_money') {
                $updateData['bank_name'] = $request->bank_name ?? null; // Provider stored in bank_name
                $updateData['account_number'] = $request->account_number ?? null; // Mobile number stored in account_number
            }

            $assignment->update($updateData);
            $assignment->refresh();

            // Create General Ledger entries
            $requestNo = $imprestRequest->request_no;
            $staffName = $assignment->staff->name ?? 'Staff';
            $description = "Imprest Payment - {$requestNo} - {$staffName}: {$imprestRequest->purpose}";
            
            // Debit: Expense Account
            \App\Models\GeneralLedger::create([
                'account_id' => $expenseAccount->id,
                'transaction_date' => $paymentDate,
                'reference_type' => 'ImprestAssignment',
                'reference_id' => $assignment->id,
                'reference_no' => $requestNo . '-' . $assignment->id,
                'type' => 'Debit',
                'amount' => $paidAmount,
                'description' => $description,
                'source' => 'imprest',
                'created_by' => Auth::id(),
            ]);
            
            // Credit: Cash/Bank Account
            \App\Models\GeneralLedger::create([
                'account_id' => $cashAccount->id,
                'transaction_date' => $paymentDate,
                'reference_type' => 'ImprestAssignment',
                'reference_id' => $assignment->id,
                'reference_no' => $requestNo . '-' . $assignment->id,
                'type' => 'Credit',
                'amount' => $paidAmount,
                'description' => $description,
                'source' => 'imprest',
                'created_by' => Auth::id(),
            ]);

            // Refresh imprest request and check if all assignments are paid
            $imprestRequest->refresh();
            $imprestRequest->load('assignments');
            
            $allAssignmentsPaid = $imprestRequest->assignments->every(function($ass) {
                return !is_null($ass->paid_at) && !is_null($ass->paid_amount) && $ass->paid_amount > 0;
            });
            
            if ($allAssignmentsPaid) {
                $imprestRequest->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $paymentMethod,
                    'payment_reference' => $request->payment_reference ?? 'Individual payments processed',
                ]);
            } else {
                // Update status to 'paid' if at least one payment was processed
                $imprestRequest->update([
                    'status' => 'paid', // Allow partial payments
                ]);
            }

            DB::commit();

            // Log activity
            \App\Services\ActivityLogService::logAction('imprest_individual_payment_processed', "Processed individual payment for {$staffName} - Assignment ID: {$assignment->id}", $imprestRequest, [
                'request_no' => $imprestRequest->request_no,
                'assignment_id' => $assignment->id,
                'staff_name' => $staffName,
                'paid_amount' => $paidAmount,
                'payment_method' => $paymentMethod,
            ]);

            // Notify staff member
            try {
                $link = route('imprest.show', ['id' => $imprestRequest->id]);
                $this->notificationService->notify(
                    $assignment->staff_id,
                    "Payment of TZS " . number_format($paidAmount, 2) . " has been processed for your imprest assignment. Please submit your receipts.",
                    $link,
                    'Imprest Payment Processed',
                    ['skip_sms' => true] // Skip SMS for payment notifications
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to send payment notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully processed payment of TZS " . number_format($paidAmount, 2) . " for {$staffName}",
                'paid_amount' => $paidAmount,
                'staff_name' => $staffName,
                'all_paid' => $allAssignmentsPaid,
                'redirect_url' => route('imprest.show', ['id' => $imprestRequest->id])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . implode(', ', $e->errors()),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error processing individual payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process bulk payment - single transaction for all staff
     */
    private function processBulkPayment(Request $request, $imprestRequest)
    {
        try {
            DB::beginTransaction();

            $paidAmount = floatval($request->bulk_paid_amount);
            $paymentMethod = $request->bulk_payment_method;
            $paymentDate = \Carbon\Carbon::parse($request->bulk_payment_date);
            $equalAmountPerStaff = $paidAmount / max($imprestRequest->assignments->count(), 1);

            // Validate bank transfer fields if needed
            if ($paymentMethod === 'bank_transfer') {
                if (empty($request->bulk_bank_account_id) && (empty($request->bulk_bank_name) || empty($request->bulk_account_number))) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Bank details are required for bank transfer payment'
                    ], 400);
                }
            }

            // Validate mobile money fields if needed
            if ($paymentMethod === 'mobile_money') {
                if (empty($request->bulk_mobile_number) || empty($request->bulk_mobile_provider)) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Mobile number and provider are required for mobile money payment'
                    ], 400);
                }
            }

            $requestNo = $imprestRequest->request_no;
            $processedCount = 0;

            // Find or create expense account for imprest
            $expenseAccount = \App\Models\ChartOfAccount::where('code', 'IMPREST')
                ->orWhere('name', 'like', '%Imprest%')
                ->where('type', 'Expense')
                ->first();
            
            if (!$expenseAccount) {
                $expenseAccount = \App\Models\ChartOfAccount::firstOrCreate(
                    ['code' => 'IMPREST'],
                    [
                        'name' => 'Imprest Expense',
                        'type' => 'Expense',
                        'category' => 'Operating Expense',
                        'is_active' => true,
                    ]
                );
            }

            // Process payment for each assignment with equal amounts
            // Get fresh assignments from database to ensure we're updating the right records
            $assignments = ImprestAssignment::where('imprest_request_id', $imprestRequest->id)->get();
            
            foreach ($assignments as $assignment) {
                // Update assignment with payment details
                $updateData = [
                    'paid_amount' => $equalAmountPerStaff,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $paymentDate,
                    'payment_reference' => $request->bulk_payment_reference ?? null,
                    'payment_notes' => $request->bulk_payment_notes ?? null,
                    'paid_at' => now(),
                    'paid_by' => Auth::id(),
                ];
                
                // Set bank/mobile details based on payment method
                if ($paymentMethod === 'bank_transfer') {
                    $updateData['bank_account_id'] = $request->bulk_bank_account_id ?? null;
                    $updateData['bank_name'] = $request->bulk_bank_name ?? null;
                    $updateData['account_number'] = $request->bulk_account_number ?? null;
                } else if ($paymentMethod === 'mobile_money') {
                    $updateData['bank_name'] = $request->bulk_mobile_provider ?? null;
                    $updateData['account_number'] = $request->bulk_mobile_number ?? null;
                }
                
                $assignment->update($updateData);
                $assignment->refresh(); // Refresh to ensure data is saved

                $processedCount++;
            }

            // Find or create cash/bank account based on payment method
            $cashAccount = null;
            if ($paymentMethod === 'cash') {
                $cashAccount = \App\Models\ChartOfAccount::where('code', 'CASH')
                    ->orWhere('name', 'like', '%Cash%')
                    ->where('type', 'Asset')
                    ->first();
                
                if (!$cashAccount) {
                    $cashAccount = \App\Models\ChartOfAccount::firstOrCreate(
                        ['code' => 'CASH'],
                        [
                            'name' => 'Cash on Hand',
                            'type' => 'Asset',
                            'category' => 'Current Asset',
                            'is_active' => true,
                        ]
                    );
                }
            } else {
                $cashAccount = \App\Models\ChartOfAccount::where('code', 'BANK')
                    ->orWhere('name', 'like', '%Bank%')
                    ->where('type', 'Asset')
                    ->first();
                
                if (!$cashAccount) {
                    $cashAccount = \App\Models\ChartOfAccount::firstOrCreate(
                        ['code' => 'BANK'],
                        [
                            'name' => 'Bank Account',
                            'type' => 'Asset',
                            'category' => 'Current Asset',
                            'is_active' => true,
                        ]
                    );
                }
            }

            if (!$expenseAccount || !$cashAccount) {
                throw new \Exception('Required Chart of Accounts not found. Please set up Expense and Cash/Bank accounts in Chart of Accounts.');
            }

            // Create single General Ledger entry for bulk payment
            $description = "Bulk Imprest Payment - {$requestNo}: {$imprestRequest->purpose}";
            
            // Debit: Expense Account
            \App\Models\GeneralLedger::create([
                'account_id' => $expenseAccount->id,
                'transaction_date' => $paymentDate,
                'reference_type' => 'ImprestRequest',
                'reference_id' => $imprestRequest->id,
                'reference_no' => $requestNo,
                'type' => 'Debit',
                'amount' => $paidAmount,
                'description' => $description,
                'source' => 'imprest',
                'created_by' => Auth::id(),
            ]);
            
            // Credit: Cash/Bank Account
            \App\Models\GeneralLedger::create([
                'account_id' => $cashAccount->id,
                'transaction_date' => $paymentDate,
                'reference_type' => 'ImprestRequest',
                'reference_id' => $imprestRequest->id,
                'reference_no' => $requestNo,
                'type' => 'Credit',
                'amount' => $paidAmount,
                'description' => $description,
                'source' => 'imprest',
                'created_by' => Auth::id(),
            ]);

            // Reload assignments to get updated data - refresh from database
            $imprestRequest->refresh();
            
            // Reload assignments from database to get fresh payment data
            $imprestRequest->load(['assignments' => function($query) {
                $query->with(['staff', 'receipts']);
            }]);
            
            // Also refresh each assignment individually to ensure we have latest data
            foreach ($imprestRequest->assignments as $assignment) {
                $assignment->refresh();
            }
            
            // Update imprest request status to paid (all assignments are paid in bulk mode)
            $imprestRequest->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $paymentMethod,
                'payment_reference' => $request->bulk_payment_reference ?? 'Bulk payment processed',
            ]);

            DB::commit();

            // Log activity
            \App\Services\ActivityLogService::logAction('imprest_bulk_payment_processed', "Processed bulk payment for imprest request {$imprestRequest->request_no}", $imprestRequest, [
                'request_no' => $imprestRequest->request_no,
                'total_amount' => $paidAmount,
                'processed_count' => $processedCount,
                'payment_method' => $paymentMethod,
            ]);

            // Notify assigned staff to submit receipts
            try {
                $link = route('imprest.show', ['id' => $imprestRequest->id]);
                foreach ($imprestRequest->assignments as $assignment) {
                    if ($assignment->staff) {
                        $this->notificationService->notify(
                            $assignment->staff_id,
                            "Payment of TZS " . number_format($equalAmountPerStaff, 2) . " has been processed for your imprest assignment. Please submit your receipts.",
                            $link,
                            'Imprest Payment Processed',
                            ['skip_sms' => true] // Skip SMS for payment notifications
                        );
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Error sending payment notifications: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully processed bulk payment of TZS " . number_format($paidAmount, 2) . " for {$processedCount} staff member(s)",
                'processed_count' => $processedCount,
                'total_amount' => $paidAmount,
                'redirect_url' => route('imprest.show', ['id' => $imprestRequest->id])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error processing bulk payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }
}
