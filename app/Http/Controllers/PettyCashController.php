<?php

namespace App\Http\Controllers;

use App\Models\PettyCashVoucher;
use App\Models\PettyCashVoucherLine;
use App\Models\GeneralLedger;
use App\Models\ChartOfAccount;
use App\Models\GlAccount;
use App\Models\CashBox;
use App\Models\Invoice;
use App\Models\Bill;
use App\Models\InvoicePayment;
use App\Models\BillPayment;
use App\Models\CreditMemo;
use App\Services\NotificationService;
use App\Services\PettyCashPdfService;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PettyCashController extends Controller
{
    protected $notificationService;

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
            'all' => PettyCashVoucher::count(),
            'pending_accountant' => PettyCashVoucher::where('status', 'pending_accountant')
                ->where(function($q) {
                    $q->whereColumn('created_by', '!=', 'accountant_id')
                      ->orWhereNull('accountant_id');
                })
                ->count(),
            'pending_hod' => PettyCashVoucher::where('status', 'pending_hod')
                ->where(function($q) {
                    $q->whereColumn('created_by', '!=', 'accountant_id')
                      ->orWhereNull('accountant_id');
                })
                ->count(),
            'pending_ceo' => PettyCashVoucher::where('status', 'pending_ceo')->count(),
            'approved_for_payment' => PettyCashVoucher::where('status', 'approved_for_payment')
                ->where(function($q) {
                    $q->whereColumn('created_by', '!=', 'accountant_id')
                      ->orWhereNull('accountant_id');
                })
                ->count(),
            'paid' => PettyCashVoucher::where('status', 'paid')->count(),
            'pending_retirement_review' => PettyCashVoucher::where('status', 'pending_retirement_review')->count(),
            'retired' => PettyCashVoucher::where('status', 'retired')->count(),
        ];

        $user = Auth::user();
        $isStaff = $user->hasAnyRole(['Staff', 'Employee']) && !$user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO', 'Director']);
        
        // If staff, redirect to my requests page
        if ($isStaff) {
            return redirect()->route('petty-cash.my-requests');
        }
        
        // Get my requests count for all users
        $myRequestsCount = PettyCashVoucher::where('created_by', $user->id)->count();
        
        return view('modules.finance.petty-cash-dashboard', compact('stats', 'myRequestsCount'));
    }

    /**
     * Create New Petty Cash Request Page
     */
    public function create()
    {
        $user = Auth::user();
        
        // Check if user has an existing non-retired request
        $hasActiveRequest = PettyCashVoucher::where('created_by', $user->id)
            ->whereNotIn('status', ['retired', 'rejected'])
            ->exists();
        
        if ($hasActiveRequest) {
            $activeVoucher = PettyCashVoucher::where('created_by', $user->id)
                ->whereNotIn('status', ['retired', 'rejected'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            return redirect()->route('petty-cash.my-requests')
                ->with('error', 'You have an active petty cash request (' . $activeVoucher->voucher_no . ') that must be retired or rejected before creating a new one.');
        }
        
        // Any authenticated user can create a petty cash request
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();

        return view('modules.finance.petty-cash-create', compact('glAccounts', 'cashBoxes'));
    }

    /**
     * Pending Accountant Verification Page
     */
    public function pendingAccountant(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = PettyCashVoucher::where('status', 'pending_accountant')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->with(['creator', 'lines', 'accountant', 'hod', 'ceo'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $vouchers = $query->paginate(20);
        $count = PettyCashVoucher::where('status', 'pending_accountant')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();

        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();

        return view('modules.finance.petty-cash-pending-accountant', compact('vouchers', 'count', 'glAccounts', 'cashBoxes'));
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

        // Regular vouchers: Exclude direct vouchers
        // Direct vouchers are identified by: created_by == accountant_id AND accountant_id is not null AND accountant_verified_at is not null
        // Regular vouchers: created_by != accountant_id (even if accountant_id is set after verification)
        $query = PettyCashVoucher::where('status', 'pending_hod')
            ->where(function($q) {
                // Exclude direct vouchers by ensuring created_by != accountant_id
                // Use whereRaw for more reliable comparison
                // This works because:
                // - Regular vouchers: created_by = staff ID, accountant_id = accountant ID (different)
                // - Direct vouchers: created_by = accountant ID, accountant_id = accountant ID (same)
                $q->whereRaw('created_by != accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->with(['creator', 'lines', 'accountant', 'hod', 'ceo']);

        // Advanced filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('payee', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('amount_min')) {
            $query->where('total_amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('total_amount', '<=', $request->amount_max);
        }

        if ($request->filled('creator_id')) {
            $query->where('created_by', $request->creator_id);
        }

        if ($request->filled('voucher_type')) {
            if ($request->voucher_type === 'direct') {
                // Direct vouchers: created_by == accountant_id AND accountant_id is not null AND accountant_verified_at is not null
                $query->whereRaw('created_by = accountant_id')
                      ->whereNotNull('accountant_id')
                      ->whereNotNull('accountant_verified_at');
            } else {
                // Regular vouchers: created_by != accountant_id OR accountant_id is null
                $query->where(function($q) {
                    $q->whereRaw('created_by != accountant_id')
                      ->orWhereNull('accountant_id');
                });
            }
        }

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $vouchers = $query->paginate($request->get('per_page', 20))->appends($request->query());
        $count = $query->count();

        // Get filter options
        $creators = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Staff', 'Employee']);
        })->orderBy('name')->get();

        // Statistics
        $stats = [
            'pending_hod' => PettyCashVoucher::where('status', 'pending_hod')
                ->where(function($q) {
                    $q->whereColumn('created_by', '!=', 'accountant_id')
                      ->orWhereNull('accountant_id');
                })
                ->count(),
        ];

        return view('modules.finance.petty-cash-pending-hod', compact('vouchers', 'count', 'creators', 'stats'));
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

        $query = PettyCashVoucher::where('status', 'pending_ceo')
            ->with(['creator', 'lines', 'accountant', 'hod', 'ceo'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $vouchers = $query->paginate(20);
        $count = PettyCashVoucher::where('status', 'pending_ceo')->count();

        return view('modules.finance.petty-cash-pending-ceo', compact('vouchers', 'count'));
    }

    /**
     * Approved for Payment Page
     */
    public function approved(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = PettyCashVoucher::where('status', 'approved_for_payment')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->with(['creator', 'lines', 'accountant', 'hod', 'ceo'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $vouchers = $query->paginate(20);
        $count = PettyCashVoucher::where('status', 'approved_for_payment')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();
        
        // Get bank accounts for payment
        $bankAccounts = \App\Models\BankAccount::where('is_active', true)->orderBy('bank_name')->get();

        return view('modules.finance.petty-cash-approved', compact('vouchers', 'count', 'glAccounts', 'cashBoxes', 'bankAccounts'));
    }

    /**
     * Paid (Awaiting Retirement) Page
     */
    public function paid(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = PettyCashVoucher::where('status', 'paid')
            ->with(['creator', 'lines', 'accountant', 'hod', 'ceo', 'paidBy'])
            ->orderBy('paid_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('paid_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('paid_at', '<=', $request->to_date);
        }

        $vouchers = $query->paginate(20);
        $count = PettyCashVoucher::where('status', 'paid')->count();

        return view('modules.finance.petty-cash-paid', compact('vouchers', 'count'));
    }

    /**
     * Pending Retirement Review Page
     */
    public function pendingRetirement(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized.');
        }

        $query = PettyCashVoucher::where('status', 'pending_retirement_review')
            ->with(['creator', 'lines', 'accountant', 'hod', 'ceo', 'paidBy'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $vouchers = $query->paginate(20);
        $count = PettyCashVoucher::where('status', 'pending_retirement_review')->count();

        return view('modules.finance.petty-cash-pending-retirement', compact('vouchers', 'count'));
    }

    /**
     * Retired (Completed) Page
     */
    public function retired(Request $request)
    {
        $query = PettyCashVoucher::where('status', 'retired')
            ->with(['creator', 'lines', 'accountant', 'hod', 'ceo', 'paidBy'])
            ->orderBy('retired_at', 'desc');

        if ($request->filled('from_date')) {
            $query->whereDate('retired_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('retired_at', '<=', $request->to_date);
        }

        $vouchers = $query->paginate(20);
        $count = PettyCashVoucher::where('status', 'retired')->count();

        return view('modules.finance.petty-cash-retired', compact('vouchers', 'count'));
    }

    /**
     * All Vouchers Page with Advanced Filtering
     */
    public function all(Request $request)
    {
        $user = Auth::user();
        
        // Build query with all vouchers
        $query = PettyCashVoucher::with(['creator', 'lines', 'accountant', 'hod', 'ceo', 'paidBy']);

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('payee', 'like', "%{$search}%")
                  ->orWhereHas('creator', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                  });
            });
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Apply created date range filter
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Apply amount range filter
        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        // Apply creator filter
        if ($request->filled('creator_id')) {
            $query->where('created_by', $request->creator_id);
        }

        // Apply accountant filter
        if ($request->filled('accountant_id')) {
            $query->where('accountant_id', $request->accountant_id);
        }

        // Apply direct voucher filter
        if ($request->filled('is_direct')) {
            if ($request->is_direct === 'yes') {
                $query->whereColumn('created_by', 'accountant_id')
                      ->whereNotNull('accountant_id')
                      ->whereNotNull('accountant_verified_at');
            } elseif ($request->is_direct === 'no') {
                $query->where(function($q) {
                    $q->whereColumn('created_by', '!=', 'accountant_id')
                      ->orWhereNull('accountant_id');
                });
            }
        }

        // Order by
        $orderBy = $request->get('order_by', 'created_at');
        $orderDir = $request->get('order_dir', 'desc');
        $query->orderBy($orderBy, $orderDir);

        $vouchers = $query->paginate($request->get('per_page', 20))->appends($request->query());
        $count = $query->count();

        // Get filter options
        $creators = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Staff', 'Employee', 'Accountant']);
        })->orderBy('name')->get();

        $accountants = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['Accountant', 'System Admin']);
        })->orderBy('name')->get();

        // Statistics for filter summary
        $stats = [
            'all' => PettyCashVoucher::count(),
            'pending_accountant' => PettyCashVoucher::where('status', 'pending_accountant')->count(),
            'pending_hod' => PettyCashVoucher::where('status', 'pending_hod')->count(),
            'pending_ceo' => PettyCashVoucher::where('status', 'pending_ceo')->count(),
            'approved_for_payment' => PettyCashVoucher::where('status', 'approved_for_payment')->count(),
            'paid' => PettyCashVoucher::where('status', 'paid')->count(),
            'pending_retirement_review' => PettyCashVoucher::where('status', 'pending_retirement_review')->count(),
            'retired' => PettyCashVoucher::where('status', 'retired')->count(),
        ];

        return view('modules.finance.petty-cash-all', compact('vouchers', 'count', 'stats', 'creators', 'accountants'));
    }

    /**
     * My Petty Cash Requests (List View)
     */
    public function myRequests(Request $request)
    {
        $user = Auth::user();
        
        // Build query with filters
        $query = PettyCashVoucher::where('created_by', $user->id)
            ->with(['lines', 'creator', 'accountant', 'hod', 'ceo']);
        
        // Apply status filter if provided
        $status = $request->get('status', 'all');
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('payee', 'like', "%{$search}%");
            });
        }
        
        $vouchers = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());
        
        // Calculate statistics for staff
        $stats = [
            'all' => PettyCashVoucher::where('created_by', $user->id)->count(),
            'pending_accountant' => PettyCashVoucher::where('created_by', $user->id)
                ->where('status', 'pending_accountant')->count(),
            'pending_hod' => PettyCashVoucher::where('created_by', $user->id)
                ->where('status', 'pending_hod')->count(),
            'pending_ceo' => PettyCashVoucher::where('created_by', $user->id)
                ->where('status', 'pending_ceo')->count(),
            'approved_for_payment' => PettyCashVoucher::where('created_by', $user->id)
                ->where('status', 'approved_for_payment')->count(),
            'paid' => PettyCashVoucher::where('created_by', $user->id)
                ->where('status', 'paid')->count(),
            'pending_retirement_review' => PettyCashVoucher::where('created_by', $user->id)
                ->where('status', 'pending_retirement_review')->count(),
            'retired' => PettyCashVoucher::where('created_by', $user->id)
                ->where('status', 'retired')->count(),
        ];

        // Check if user has any pending requests (not retired or rejected)
        $hasActiveRequest = PettyCashVoucher::where('created_by', $user->id)
            ->whereNotIn('status', ['retired', 'rejected'])
            ->exists();

        // Fetch GL Accounts and Cash Boxes for reference details
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();
        
        return view('modules.finance.petty', compact('vouchers', 'hasActiveRequest', 'glAccounts', 'cashBoxes', 'stats', 'status'));
    }

    public function store(Request $request)
    {
        // Check if user has an existing non-retired request
        $hasActiveRequest = PettyCashVoucher::where('created_by', Auth::id())
            ->whereNotIn('status', ['retired', 'rejected'])
            ->exists();
        
        if ($hasActiveRequest) {
            $activeVoucher = PettyCashVoucher::where('created_by', Auth::id())
                ->whereNotIn('status', ['retired', 'rejected'])
                ->orderBy('created_at', 'desc')
                ->first();
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'You have an active petty cash request (' . $activeVoucher->voucher_no . ') that must be retired or rejected before creating a new one. Please complete your existing request first.');
        }

        $request->validate([
            'date' => 'required|date',
            'payee' => 'required|string|max:255',
            'purpose' => 'required|string',
            'line_description' => 'required|array|min:1',
            'line_description.*' => 'required|string|max:255',
            'line_qty' => 'required|array|min:1',
            'line_qty.*' => 'required|numeric|min:0.01',
            'line_unit_price' => 'required|array|min:1',
            'line_unit_price.*' => 'required|numeric|min:0.01',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        DB::beginTransaction();
        try {
            // Generate voucher number in format: PCV20251031-001
            $today = date('Ymd');
            $lastVoucher = PettyCashVoucher::whereDate('created_at', today())
                ->where('voucher_no', 'like', 'PCV' . $today . '-%')
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = 1;
            if ($lastVoucher && preg_match('/PCV\d{8}-(\d{3})/', $lastVoucher->voucher_no, $matches)) {
                $sequence = (int)$matches[1] + 1;
            }
            
            $voucherNo = 'PCV' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->line_qty as $key => $qty) {
                $totalAmount += $qty * $request->line_unit_price[$key];
            }

            // Handle file uploads
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('petty-cash/attachments', $filename, 'public');
                    $attachments[] = $path;
                }
            }

            // Create voucher - Regular voucher workflow (must go through all approvals)
            // Ensure this is NOT a direct voucher by explicitly setting accountant_id to NULL
            // This prevents any potential bypass where someone tries to set accountant_id in the request
            $voucher = PettyCashVoucher::create([
                'voucher_no' => $voucherNo,
                'date' => $request->date,
                'payee' => $request->payee,
                'purpose' => $request->purpose,
                'amount' => $totalAmount,
                'created_by' => Auth::id(), // Staff member creating the request
                'accountant_id' => null, // Explicitly set to null to ensure regular voucher workflow
                'accountant_verified_at' => null, // Explicitly set to null
                'attachments' => $attachments,
                'status' => 'pending_accountant', // Must start at accountant verification
                // Note: Direct vouchers are created via storeDirectVoucher() method only
                // Regular vouchers MUST go through: Accountant → HOD → CEO → Payment → Retirement
            ]);

            // Create voucher lines
            foreach ($request->line_description as $key => $description) {
                $qty = (float) $request->line_qty[$key];
                $unitPrice = (float) $request->line_unit_price[$key];
                PettyCashVoucherLine::create([
                    'voucher_id' => $voucher->id,
                    'description' => $description,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'total' => $qty * $unitPrice,
                ]);
            }

            // Send notifications (wrapped in try-catch to not block the main transaction)
            try {
                $user = Auth::user();
                $link = route('petty-cash.index');
                
                // Prepare complete notification data
                $notificationData = [
                    'voucher_no' => $voucherNo,
                    'staff_name' => $user->name,
                    'amount' => $totalAmount,
                    'purpose' => $request->purpose,
                    'payee' => $request->payee,
                    'status' => 'pending_accountant',
                    'request_date' => $voucher->created_at->format('M j, Y g:i A'),
                    'document_attached' => !empty($attachments),
                    'document_name' => !empty($attachments) ? count($attachments) . ' document(s) attached' : null,
                ];
                
                // Add employee/department info if available
                if ($user->employee) {
                    $notificationData['employee_id'] = $user->employee->employee_id ?? null;
                    $notificationData['department'] = $user->employee->department->name ?? null;
                }
                
                // Notify staff
                $this->notificationService->notify(
                    $user->id,
                    "Dear {$user->name},\n\nYour petty cash request #{$voucherNo} for TZS " . number_format($totalAmount, 2) . " has been submitted successfully and is pending accountant review.\n\nPurpose: {$request->purpose}\nPayee: {$request->payee}" . (!empty($attachments) ? "\n\nDocuments have been attached to this request." : ''),
                    $link,
                    'Petty Cash Request Submitted',
                    $notificationData
                );

                // Notify accountant
                $this->notificationService->notifyAccountant(
                    "Dear Accountant,\n\nNew petty cash request #{$voucherNo} from staff {$user->name} for TZS " . number_format($totalAmount, 2) . " is pending your review.\n\nPurpose: {$request->purpose}\nPayee: {$request->payee}" . (!empty($attachments) ? "\n\nDocuments have been attached to this request." : ''),
                    route('petty-cash.pending-accountant'),
                    'New Petty Cash Request Pending Review',
                    $notificationData
                );
            } catch (\Exception $notificationError) {
                // Log notification error but don't fail the transaction
                \Log::error('Notification error in petty cash store: ' . $notificationError->getMessage());
            }

            DB::commit();
            
            // Log petty cash creation
            ActivityLogService::logCreated($voucher, "Created petty cash voucher #{$voucherNo} for TZS " . number_format($totalAmount, 2), [
                'voucher_no' => $voucherNo,
                'amount' => $totalAmount,
                'payee' => $request->payee,
                'purpose' => $request->purpose,
                'created_by' => Auth::id(),
                'created_by_name' => Auth::user()->name,
                'status' => 'pending_accountant',
            ]);
            
            return redirect()->route('petty-cash.index')->with('success', 'Petty cash request submitted successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Petty cash store error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->with('error', 'Failed to create request: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(PettyCashVoucher $pettyCash)
    {
        $user = Auth::user();
        $pettyCash->load(['lines', 'creator', 'accountant', 'hod', 'ceo', 'paidBy']);
        
        // Determine user roles and permissions
        $isAccountant = $user->hasAnyRole(['Accountant', 'System Admin']);
        $isHOD = $user->hasAnyRole(['HOD', 'System Admin']);
        $isCEO = $user->hasAnyRole(['CEO', 'Director', 'System Admin']);
        $isCreator = $pettyCash->created_by == $user->id;
        // Direct vouchers are identified by:
        // 1. Created by an accountant (created_by === accountant_id) - KEY DIFFERENCE
        // 2. Accountant ID is set (accountant_id is not null)
        // 3. Accountant verified at creation time (accountant_verified_at is set)
        // Regular vouchers: created_by != accountant_id (staff creates, accountant verifies later)
        // For regular vouchers: created_by = staff ID, accountant_id = accountant ID (different people)
        // For direct vouchers: created_by = accountant ID, accountant_id = accountant ID (same person)
        $isDirectVoucher = $pettyCash->created_by !== null &&
                          $pettyCash->accountant_id !== null &&
                          $pettyCash->created_by === $pettyCash->accountant_id &&
                          $pettyCash->accountant_verified_at !== null;
        
        // Load GL Accounts and Cash Boxes for accountant verification
        $glAccounts = $isAccountant ? GlAccount::where('is_active', true)->orderBy('code')->get() : collect();
        $cashBoxes = $isAccountant ? CashBox::where('is_active', true)->orderBy('name')->get() : collect();
        
        return view('modules.finance.petty-show', compact('pettyCash', 'isAccountant', 'isHOD', 'isCEO', 'isCreator', 'isDirectVoucher', 'glAccounts', 'cashBoxes'));
    }

    public function destroy(PettyCashVoucher $pettyCash)
    {
        if (!$pettyCash->canBeDeleted()) {
            return redirect()->back()->with('error', 'This request cannot be deleted.');
        }

        if ($pettyCash->created_by !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only delete your own requests.');
        }

        DB::beginTransaction();
        try {
            // Delete attachments
            if ($pettyCash->attachments) {
                foreach ($pettyCash->attachments as $attachment) {
                    Storage::disk('public')->delete($attachment);
                }
            }

            // Delete lines
            $pettyCash->lines()->delete();
            
            // Delete voucher
            $pettyCash->delete();

            DB::commit();
            return redirect()->route('petty-cash.index')->with('success', 'Request deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to delete request: ' . $e->getMessage());
        }
    }

    /**
     * Store direct voucher created by accountant (in-office vouchers)
     * Only accessible by Accountant or System Admin
     */
    public function storeDirectVoucher(Request $request)
    {
        // Check authorization
        $user = Auth::user();
        if (!$user->hasRole('Accountant') && !$user->hasRole('System Admin')) {
            abort(403, 'Only accountants and system admins can create direct vouchers.');
        }

        $request->validate([
            'date' => 'required|date',
            'payee' => 'required|string|max:255',
            'purpose' => 'required|string',
            'line_description' => 'required|array|min:1',
            'line_description.*' => 'required|string|max:255',
            'line_qty' => 'required|array|min:1',
            'line_qty.*' => 'required|numeric|min:0.01',
            'line_unit_price' => 'required|array|min:1',
            'line_unit_price.*' => 'required|numeric|min:0.01',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'gl_account_id' => 'required|integer|exists:gl_accounts,id',
            'cash_box_id' => 'required|integer|exists:cash_boxes,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Generate voucher number in format: PCV20251031-001
            $today = date('Ymd');
            $lastVoucher = PettyCashVoucher::whereDate('created_at', today())
                ->where('voucher_no', 'like', 'PCV' . $today . '-%')
                ->orderBy('id', 'desc')
                ->first();
            
            $sequence = 1;
            if ($lastVoucher && preg_match('/PCV\d{8}-(\d{3})/', $lastVoucher->voucher_no, $matches)) {
                $sequence = (int)$matches[1] + 1;
            }
            
            $voucherNo = 'PCV' . $today . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
            
            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->line_qty as $key => $qty) {
                $totalAmount += $qty * $request->line_unit_price[$key];
            }

            // Handle file uploads
            $attachments = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('petty-cash/attachments', $filename, 'public');
                    $attachments[] = $path;
                }
            }

            // ============================================
            // CREATE DIRECT VOUCHER (Accountant Created)
            // ============================================
            // Direct vouchers are created by accountants for in-office expenses
            // Key characteristics:
            // - created_by = accountant_id (same person) → This identifies it as direct voucher
            // - accountant_verified_at = set at creation (already verified)
            // - status = pending_hod (skips accountant verification step)
            // - Flow: Accountant Creates → HOD Approve → Paid (skips CEO)
            // - Status progression: pending_hod → paid
            // ============================================
            $voucher = PettyCashVoucher::create([
                'voucher_no' => $voucherNo,
                'date' => $request->date,
                'payee' => $request->payee,
                'purpose' => $request->purpose,
                'amount' => $totalAmount,
                'created_by' => Auth::id(), // Accountant is the creator
                'accountant_id' => Auth::id(), // Accountant verified it immediately (same person)
                'accountant_verified_at' => now(), // Already verified at creation
                'gl_account_id' => $request->gl_account_id,
                'cash_box_id' => $request->cash_box_id,
                'accountant_comments' => $request->notes ?? 'Direct voucher created in-office by accountant.',
                'attachments' => $attachments,
                'status' => 'pending_hod', // Skip accountant verification, go directly to HOD
            ]);
            
            \Log::info('Direct Voucher Created', [
                'voucher_no' => $voucherNo,
                'created_by' => Auth::id(),
                'accountant_id' => Auth::id(),
                'is_direct_voucher' => true,
                'initial_status' => 'pending_hod',
                'next_step' => 'HOD Approval → Paid (skips CEO)',
            ]);

            // Create voucher lines
            foreach ($request->line_description as $key => $description) {
                $qty = (float) $request->line_qty[$key];
                $unitPrice = (float) $request->line_unit_price[$key];
                PettyCashVoucherLine::create([
                    'voucher_id' => $voucher->id,
                    'description' => $description,
                    'qty' => $qty,
                    'unit_price' => $unitPrice,
                    'total' => $qty * $unitPrice,
                ]);
            }

            // Send notifications
            try {
                $accountant = Auth::user();
                $link = route('petty-cash.direct-vouchers.index');
                
                    // Notify accountant
                    $this->notificationService->notify(
                        $accountant->id,
                        "Direct voucher #{$voucherNo} has been created successfully and forwarded to HOD for approval.",
                        route('petty-cash.direct-vouchers.index'),
                        'Direct Voucher Created'
                    );

                // Notify all HODs for direct vouchers
                $this->notificationService->notifyByRole(
                    ['HOD'],
                    "New direct petty cash voucher #{$voucherNo} created by {$accountant->name} for TZS " . number_format($totalAmount, 2) . " is pending your approval.",
                    route('petty-cash.direct-vouchers.index'),
                    'New Direct Petty Cash Voucher Pending Approval',
                    ['voucher_no' => $voucherNo, 'accountant_name' => $accountant->name, 'amount' => number_format($totalAmount, 2)]
                );
            } catch (\Exception $notificationError) {
                \Log::error('Notification error in direct voucher store: ' . $notificationError->getMessage());
            }

            DB::commit();
            
            // Log activity
            ActivityLogService::logCreated($voucher, "Created direct petty cash voucher #{$voucherNo} for TZS " . number_format($totalAmount, 2), [
                'voucher_no' => $voucherNo,
                'amount' => $totalAmount,
                'payee' => $request->payee,
                'purpose' => $request->purpose,
                'is_direct_voucher' => true,
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Direct voucher created successfully.',
                    'voucher_no' => $voucherNo,
                ]);
            }
            
            return redirect()->route('petty-cash.direct-vouchers.index')->with('success', 'Direct voucher created successfully and forwarded to HOD.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Direct voucher store error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create direct voucher: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Failed to create direct voucher: ' . $e->getMessage())
                ->withInput();
        }
    }

    // Accountant methods
    public function accountantIndex(Request $request)
    {
        // Handle status parameter - 'all' shows dashboard, otherwise specific status
        $status = $request->get('status');
        if (empty($status) || $status === '') {
            $status = 'pending_accountant';
        }
        // If status is 'all', show dashboard view with buttons
        if ($status === 'all') {
            $status = 'all'; // Keep as 'all' for dashboard view
        }
        $type = $request->get('type', 'regular'); // 'regular' or 'direct'
        
        // Ensure only accountants and system admins can access
        $user = Auth::user();
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403, 'Unauthorized. Only Accountants and System Admins can access this page.');
        }
        
        // Helper to check if voucher is direct (created by accountant and verified immediately)
        $isDirectVoucher = function($voucher) {
            return $voucher->created_by === $voucher->accountant_id && 
                   $voucher->accountant_verified_at !== null &&
                   $voucher->accountant_verified_at->eq($voucher->created_at);
        };
        
        // Get counts for regular vouchers
        $pendingAccountantCount = PettyCashVoucher::where('status', 'pending_accountant')
            ->where(function($q) use ($isDirectVoucher) {
                // Regular vouchers only
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        $pendingHodCount = PettyCashVoucher::where('status', 'pending_hod')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        $pendingCeoCount = PettyCashVoucher::where('status', 'pending_ceo')->count();
        $approvedCount = PettyCashVoucher::where('status', 'approved_for_payment')->count();
        $paidCount = PettyCashVoucher::where('status', 'paid')->count();
        $pendingRetirementCount = PettyCashVoucher::where('status', 'pending_retirement_review')->count();
        $retiredCount = PettyCashVoucher::where('status', 'retired')->count();
        $rejectedCount = PettyCashVoucher::where('status', 'rejected')
            ->whereNotNull('accountant_id')
            ->count();
        
        // Get counts for direct vouchers (already used vouchers)
        $directPendingHodCount = PettyCashVoucher::where('status', 'pending_hod')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->whereNotNull('accountant_verified_at')
            ->count();
        $directPaidCount = PettyCashVoucher::where('status', 'paid')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->count();
        $directRejectedCount = PettyCashVoucher::where('status', 'rejected')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->count();
        
        // Build query based on status and type
        // If status is 'all', don't query vouchers (show dashboard)
        $query = null;
        $vouchers = collect();
        
        if ($status !== 'all') {
            $query = PettyCashVoucher::with(['lines', 'creator', 'hod', 'ceo']);
        }
        
        if ($status !== 'all' && $type === 'direct') {
            // Direct vouchers only
            $query->whereColumn('created_by', 'accountant_id')
                  ->whereNotNull('accountant_id')
                  ->whereNotNull('accountant_verified_at');
            
            switch($status) {
                case 'pending_hod':
                    $query->where('status', 'pending_hod');
                    break;
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'rejected':
                    $query->where('status', 'rejected');
                    break;
                default:
                    $query->where('status', 'pending_hod');
            }
        } elseif ($status !== 'all') {
            // Regular vouchers only
            $query->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            });
        
            switch($status) {
                case 'pending_accountant':
                    $query->where('status', 'pending_accountant');
                    break;
                case 'pending_hod':
                    $query->where('status', 'pending_hod');
                    break;
                case 'pending_ceo':
                    $query->where('status', 'pending_ceo');
                    break;
                case 'approved':
                    $query->where('status', 'approved_for_payment');
                    break;
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'pending_retirement':
                    $query->where('status', 'pending_retirement_review');
                    break;
                case 'retired':
                    $query->where('status', 'retired');
                    break;
                case 'rejected':
                    $query->where('status', 'rejected')
                          ->whereNotNull('accountant_id');
                    break;
                default:
                    $query->where('status', 'pending_accountant');
            }
            
            // Apply search filter if provided
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('voucher_no', 'like', "%{$search}%")
                      ->orWhere('purpose', 'like', "%{$search}%")
                      ->orWhere('payee', 'like', "%{$search}%")
                      ->orWhereHas('creator', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                      });
                });
            }
            
            // Apply date range filter
            if ($request->has('date_from') && !empty($request->date_from)) {
                $query->whereDate('date', '>=', $request->date_from);
            }
            if ($request->has('date_to') && !empty($request->date_to)) {
                $query->whereDate('date', '<=', $request->date_to);
            }
            
            // Apply amount range filter
            if ($request->has('amount_min') && !empty($request->amount_min)) {
                $query->where('amount', '>=', $request->amount_min);
            }
            if ($request->has('amount_max') && !empty($request->amount_max)) {
                $query->where('amount', '<=', $request->amount_max);
            }
            
            $vouchers = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());
        }
        
        // Calculate statistics
        $stats = [
            'total_amount' => (float) PettyCashVoucher::sum('amount'),
            'pending_accountant_amount' => (float) PettyCashVoucher::where('status', 'pending_accountant')
                ->where(function($q) {
                    $q->whereColumn('created_by', '!=', 'accountant_id')
                      ->orWhereNull('accountant_id');
                })
                ->sum('amount'),
            'pending_hod_amount' => (float) PettyCashVoucher::where('status', 'pending_hod')
                ->where(function($q) {
                    $q->whereColumn('created_by', '!=', 'accountant_id')
                      ->orWhereNull('accountant_id');
                })
                ->sum('amount'),
            'pending_ceo_amount' => (float) PettyCashVoucher::where('status', 'pending_ceo')->sum('amount'),
            'approved_amount' => (float) PettyCashVoucher::where('status', 'approved_for_payment')->sum('amount'),
            'paid_amount' => (float) PettyCashVoucher::where('status', 'paid')->sum('amount'),
            'direct_pending_hod_amount' => (float) PettyCashVoucher::where('status', 'pending_hod')
                ->whereColumn('created_by', 'accountant_id')
                ->whereNotNull('accountant_id')
                ->sum('amount'),
            'direct_paid_amount' => (float) PettyCashVoucher::where('status', 'paid')
                ->whereColumn('created_by', 'accountant_id')
                ->whereNotNull('accountant_id')
                ->sum('amount'),
            'current_month_total' => (float) PettyCashVoucher::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'current_month_count' => PettyCashVoucher::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];
        
        $counts = [
            'pending_accountant' => $pendingAccountantCount,
            'pending_hod' => $pendingHodCount,
            'pending_ceo' => $pendingCeoCount,
            'approved' => $approvedCount,
            'paid' => $paidCount,
            'pending_retirement' => $pendingRetirementCount,
            'retired' => $retiredCount,
            'rejected' => $rejectedCount,
            // Direct voucher counts (already used vouchers)
            'direct_pending_hod' => $directPendingHodCount,
            'direct_paid' => $directPaidCount,
            'direct_rejected' => $directRejectedCount,
        ];
        
        // Fetch GL Accounts and Cash Boxes for selection
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();

        return view('modules.finance.petty-accountant', compact('vouchers', 'status', 'type', 'counts', 'stats', 'glAccounts', 'cashBoxes'));
    }

    /**
     * Direct Vouchers Management Page
     * In-Office Expenses (Already Used) - HOD/Admin Approval Only
     */
    public function directVouchersIndex(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status', 'pending_hod'); // Default to pending_hod
        
        // Helper to check if voucher is direct (created by accountant and verified immediately)
        $isDirectVoucher = function($voucher) {
            return $voucher->created_by === $voucher->accountant_id && 
                   $voucher->accountant_verified_at !== null &&
                   $voucher->accountant_verified_at->eq($voucher->created_at);
        };
        
        // Get counts for direct vouchers
        $directPendingHodCount = PettyCashVoucher::where('status', 'pending_hod')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->whereNotNull('accountant_verified_at')
            ->count();
        $directPaidCount = PettyCashVoucher::where('status', 'paid')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->count();
        $directRejectedCount = PettyCashVoucher::where('status', 'rejected')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->count();
        
        // Build query for direct vouchers only
        $query = PettyCashVoucher::whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->whereNotNull('accountant_verified_at')
            ->with(['lines', 'creator', 'accountant', 'hod', 'ceo']);
        
        switch($status) {
            case 'pending_hod':
                $query->where('status', 'pending_hod');
                break;
            case 'paid':
                $query->where('status', 'paid');
                break;
            case 'rejected':
                $query->where('status', 'rejected');
                break;
            default:
                $query->where('status', 'pending_hod');
        }
        
        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('payee', 'like', "%{$search}%")
                  ->orWhereHas('creator', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        
        // Apply amount range filter
        if ($request->has('amount_min') && !empty($request->amount_min)) {
            $query->where('amount', '>=', $request->amount_min);
        }
        if ($request->has('amount_max') && !empty($request->amount_max)) {
            $query->where('amount', '<=', $request->amount_max);
        }
        
        $vouchers = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());
        
        // Calculate statistics
        $stats = [
            'direct_pending_hod_amount' => (float) PettyCashVoucher::where('status', 'pending_hod')
                ->whereColumn('created_by', 'accountant_id')
                ->whereNotNull('accountant_id')
                ->sum('amount'),
            'direct_paid_amount' => (float) PettyCashVoucher::where('status', 'paid')
                ->whereColumn('created_by', 'accountant_id')
                ->whereNotNull('accountant_id')
                ->sum('amount'),
            'direct_rejected_amount' => (float) PettyCashVoucher::where('status', 'rejected')
                ->whereColumn('created_by', 'accountant_id')
                ->whereNotNull('accountant_id')
                ->sum('amount'),
            'current_month_total' => (float) PettyCashVoucher::whereColumn('created_by', 'accountant_id')
                ->whereNotNull('accountant_id')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'current_month_count' => PettyCashVoucher::whereColumn('created_by', 'accountant_id')
                ->whereNotNull('accountant_id')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];
        
        $counts = [
            'direct_pending_hod' => $directPendingHodCount,
            'direct_paid' => $directPaidCount,
            'direct_rejected' => $directRejectedCount,
        ];
        
        // Fetch GL Accounts and Cash Boxes for selection (for Accountant)
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();
        
        // Determine user role
        $isAccountant = $user->hasAnyRole(['Accountant', 'System Admin']);
        $isHOD = $user->hasAnyRole(['HOD', 'System Admin']);

        return view('modules.finance.petty-direct-vouchers', compact('vouchers', 'status', 'counts', 'stats', 'glAccounts', 'cashBoxes', 'isAccountant', 'isHOD'));
    }

    public function accountantVerify(Request $request, PettyCashVoucher $pettyCash)
    {
        try {
            $request->validate([
                'action' => 'required|in:approve,reject',
                'gl_account_id' => 'required_if:action,approve|nullable|integer',
                'cash_box_id' => 'required_if:action,approve|nullable|integer',
                'comments' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }

        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can verify at any level, others must wait for pending_accountant
        if (!$isSystemAdmin && $pettyCash->status !== 'pending_accountant') {
            $errorMsg = 'This request is not pending accountant verification.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg
                ], 403);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        DB::beginTransaction();
        try {
            $voucherNo = $pettyCash->voucher_no;
            $creator = $pettyCash->creator;
            $accountant = Auth::user();
            
            // ============================================
            // DETECT VOUCHER TYPE BEFORE VERIFICATION
            // ============================================
            // Check if this is a regular voucher (created by staff)
            // Regular vouchers: created_by != accountant_id (before verification)
            $isRegularVoucher = $pettyCash->created_by !== Auth::id();
            
            if ($request->action === 'approve') {
                // ============================================
                // ACCOUNTANT VERIFICATION - REGULAR VOUCHER
                // ============================================
                // When accountant verifies a regular voucher (created by staff):
                // - Set accountant_id to current accountant
                // - Set accountant_verified_at timestamp
                // - Move status from pending_accountant → pending_hod
                // - After this: created_by (staff) != accountant_id (accountant) → Regular voucher
                // - Flow continues: HOD → CEO → Payment
                // ============================================
                
                $pettyCash->update([
                    'status' => 'pending_hod',
                    'accountant_id' => Auth::id(),
                    'accountant_verified_at' => now(),
                    'gl_account_id' => $request->gl_account_id,
                    'cash_box_id' => $request->cash_box_id,
                    'accountant_comments' => $request->comments,
                ]);
                
                \Log::info('Accountant Verification - Regular Voucher', [
                    'voucher_no' => $pettyCash->voucher_no,
                    'created_by' => $pettyCash->created_by,
                    'accountant_id' => Auth::id(),
                    'is_regular_voucher' => $isRegularVoucher,
                    'new_status' => 'pending_hod',
                    'next_step' => 'HOD Approval → CEO Approval → Payment',
                ]);
                
                // Notify accountant of successful verification
                $this->notificationService->notify(
                    $accountant->id,
                    "Your verification of petty cash request #{$voucherNo} from {$creator->name} has been successfully completed and forwarded to HOD.",
                    route('petty-cash.pending-accountant'),
                    'Verification Successful'
                );
                
                // Notify HOD
                if ($creator->primary_department_id) {
                    $this->notificationService->notifyHOD(
                        $creator->primary_department_id,
                        "New petty cash request #{$voucherNo} from {$creator->name} for TZS " . number_format($pettyCash->amount, 2) . " is pending your approval.",
                        route('petty-cash.pending-hod'),
                        'New Petty Cash Request Pending Approval',
                        ['voucher_no' => $voucherNo, 'staff_name' => $creator->name, 'amount' => number_format($pettyCash->amount, 2)]
                    );
                }
                
                $message = 'Request verified and forwarded to HOD for approval.';
            } else {
                $pettyCash->update([
                    'status' => 'rejected',
                    'accountant_id' => Auth::id(),
                    'accountant_verified_at' => now(),
                    'accountant_comments' => $request->comments,
                ]);
                
                // Notify staff of rejection
                $this->notificationService->notify(
                    $creator->id,
                    "Your petty cash request #{$voucherNo} has been rejected. Please check the comments.",
                    route('petty-cash.index'),
                    'Petty Cash Request Rejected'
                );
                
                $message = 'Request rejected.';
            }

            DB::commit();
            
            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message
                ]);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Accountant verify error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'voucher_id' => $pettyCash->id,
                'user_id' => Auth::id()
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process request: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to process request: ' . $e->getMessage());
        }
    }




    // HOD methods
    public function hodIndex(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has HOD role
        if (!$user->hasAnyRole(['HOD', 'System Admin'])) {
            abort(403);
        }
        
        // Handle empty status parameter - default to pending_hod
        $status = $request->get('status');
        if (empty($status) || $status === '') {
            $status = 'pending_hod';
        }
        $type = $request->get('type', 'regular'); // 'regular' or 'direct'
        
        // Get counts for regular vouchers
        $pendingHodCount = PettyCashVoucher::where('status', 'pending_hod')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        $pendingCeoCount = PettyCashVoucher::where('status', 'pending_ceo')->count();
        $approvedCount = PettyCashVoucher::where('status', 'approved_for_payment')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        $paidCount = PettyCashVoucher::where('status', 'paid')->count();
        $rejectedCount = PettyCashVoucher::where('status', 'rejected')
            ->whereNotNull('hod_id')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        $retiredCount = PettyCashVoucher::where('status', 'retired')
            ->whereNotNull('hod_id')
            ->count();
        
        // Get counts for direct vouchers (already used vouchers)
        $directPendingHodCount = PettyCashVoucher::where('status', 'pending_hod')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->whereNotNull('accountant_verified_at')
            ->count();
        $directPaidCount = PettyCashVoucher::where('status', 'paid')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->count();
        $directRejectedCount = PettyCashVoucher::where('status', 'rejected')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->whereNotNull('hod_id')
            ->count();
        
        // Build query based on status and type
        $query = PettyCashVoucher::with(['lines', 'creator', 'accountant', 'ceo', 'creator.primaryDepartment']);
        
        // Apply filters
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhere('payee', 'like', "%{$search}%")
                  ->orWhereHas('creator', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('date', '<=', $request->date_to);
        }
        
        if ($request->has('amount_min') && !empty($request->amount_min)) {
            $query->where('amount', '>=', $request->amount_min);
        }
        
        if ($request->has('amount_max') && !empty($request->amount_max)) {
            $query->where('amount', '<=', $request->amount_max);
        }
        
        if ($type === 'direct') {
            // Direct vouchers only
            $query->whereColumn('created_by', 'accountant_id')
                  ->whereNotNull('accountant_id')
                  ->whereNotNull('accountant_verified_at');
            
            switch($status) {
                case 'pending_hod':
                    $query->where('status', 'pending_hod');
                    break;
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'rejected':
                    $query->where('status', 'rejected')
                          ->whereNotNull('hod_id');
                    break;
                default:
                    $query->where('status', 'pending_hod');
            }
        } else {
            // Regular vouchers only
            $query->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            });
        
        switch($status) {
            case 'pending_hod':
                $query->where('status', 'pending_hod');
                break;
            case 'pending_ceo':
                $query->where('status', 'pending_ceo');
                break;
            case 'approved':
                $query->where('status', 'approved_for_payment');
                break;
            case 'paid':
                $query->where('status', 'paid');
                break;
            case 'rejected':
                $query->where('status', 'rejected')
                      ->whereNotNull('hod_id');
                break;
            case 'retired':
                $query->where('status', 'retired')
                      ->whereNotNull('hod_id');
                break;
            default:
                $query->where('status', 'pending_hod');
            }
        }
        
        $vouchers = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get invoices pending approval
        $pendingInvoices = Invoice::where('status', 'Pending for Approval')
            ->with(['customer', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $pendingInvoicesCount = $pendingInvoices->count();
        
        // Get bills pending approval (if bills have approval workflow)
        $pendingBills = Bill::where('status', 'Draft')
            ->with(['vendor', 'items', 'payments'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $pendingBillsCount = $pendingBills->count();
        
        // Get invoice payments for pending invoices
        $invoicePayments = InvoicePayment::whereIn('invoice_id', $pendingInvoices->pluck('id'))
            ->with(['invoice.customer', 'bankAccount'])
            ->orderBy('payment_date', 'desc')
            ->get();
        
        // Get bill payments for pending bills
        $billPayments = BillPayment::whereIn('bill_id', $pendingBills->pluck('id'))
            ->with(['bill.vendor', 'bankAccount'])
            ->orderBy('payment_date', 'desc')
            ->get();
        
        // Get credit memos pending approval
        $pendingCreditMemos = CreditMemo::where('status', 'Pending for Approval')
            ->with(['customer', 'invoice', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        $pendingCreditMemosCount = $pendingCreditMemos->count();
        
        $counts = [
            'pending_hod' => $pendingHodCount,
            'pending_ceo' => $pendingCeoCount,
            'approved' => $approvedCount,
            'paid' => $paidCount,
            'rejected' => $rejectedCount,
            'retired' => $retiredCount,
            // Direct voucher counts (already used vouchers)
            'direct_pending_hod' => $directPendingHodCount,
            'direct_paid' => $directPaidCount,
            'direct_rejected' => $directRejectedCount,
            // Invoice and Bill counts
            'pending_invoices' => $pendingInvoicesCount,
            'pending_bills' => $pendingBillsCount,
            'pending_credit_memos' => $pendingCreditMemosCount,
        ];
        
        return view('modules.finance.petty-hod', compact('vouchers', 'status', 'type', 'counts', 'pendingInvoices', 'pendingBills', 'invoicePayments', 'billPayments', 'pendingCreditMemos'));
    }





    public function hodApprove(Request $request, PettyCashVoucher $pettyCash)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string',
        ]);

        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can approve at any level, others must wait for pending_hod
        if (!$isSystemAdmin && $pettyCash->status !== 'pending_hod') {
            return redirect()->back()->with('error', 'This request is not pending HOD approval.');
        }

        DB::beginTransaction();
        try {
            $voucherNo = $pettyCash->voucher_no;
            $creator = $pettyCash->creator;
            $hod = Auth::user();
            
            // ============================================
            // DETECT VOUCHER TYPE AND DETERMINE FLOW
            // ============================================
            // REGULAR VOUCHER (Staff Request):
            //   - created_by = Staff ID (different from accountant)
            //   - accountant_id = Accountant ID (set when accountant verifies)
            //   - Flow: Staff → Accountant Verify → HOD Approve → CEO Approve → Payment
            //   - Status progression: pending_accountant → pending_hod → pending_ceo → approved_for_payment → paid
            //
            // DIRECT VOUCHER (Accountant Created):
            //   - created_by = Accountant ID (same as accountant_id)
            //   - accountant_id = Accountant ID (set at creation)
            //   - accountant_verified_at = set at creation (already verified)
            //   - Flow: Accountant Creates → HOD Approve → Paid (skips CEO)
            //   - Status progression: pending_hod → paid
            // ============================================
            
            $isDirectVoucher = $pettyCash->created_by !== null &&
                              $pettyCash->accountant_id !== null &&
                              $pettyCash->created_by === $pettyCash->accountant_id &&
                              $pettyCash->accountant_verified_at !== null;
            
            // Log detection for debugging
            \Log::info('HOD Approval - Voucher Detection', [
                'voucher_no' => $pettyCash->voucher_no,
                'created_by' => $pettyCash->created_by,
                'accountant_id' => $pettyCash->accountant_id,
                'accountant_verified_at' => $pettyCash->accountant_verified_at,
                'is_direct_voucher' => $isDirectVoucher,
                'current_status' => $pettyCash->status,
            ]);
            
            if ($request->action === 'approve') {
                if ($isDirectVoucher) {
                    // ============================================
                    // DIRECT VOUCHER PATH: HOD → PAID (Skip CEO)
                    // ============================================
                    // Direct vouchers are already used in-office, so they skip CEO approval
                    // and go directly to "Paid" status after HOD approval
                    $pettyCash->update([
                        'status' => 'paid',
                        'hod_id' => Auth::id(),
                        'hod_approved_at' => now(),
                        'hod_comments' => $request->comments ?? 'Direct voucher approved - already used in-office.',
                        'paid_at' => now(),
                        'paid_by' => Auth::id(),
                    ]);
                    
                    // Notify HOD
                    $this->notificationService->notify(
                        $hod->id,
                        "You have approved direct petty cash voucher #{$voucherNo} created by {$creator->name}. Status updated to Paid (already used).",
                        route('petty-cash.direct-vouchers.index'),
                        'Direct Voucher Approved & Completed'
                    );
                    
                    // Notify accountant (creator)
                    $this->notificationService->notify(
                        $creator->id,
                        "Your direct petty cash voucher #{$voucherNo} has been approved by HOD and marked as Paid (complete).",
                        route('petty-cash.direct-vouchers.index'),
                        'Direct Voucher Approved & Completed'
                    );
                    
                    $message = 'Direct voucher approved and marked as Paid (already used).';
                    
                    \Log::info('HOD Approval - Direct Voucher', [
                        'voucher_no' => $pettyCash->voucher_no,
                        'new_status' => 'paid',
                        'message' => 'Direct voucher approved - marked as paid (skips CEO)',
                    ]);
                } else {
                    // ============================================
                    // REGULAR VOUCHER PATH: HOD → CEO → PAYMENT
                    // ============================================
                    // Regular vouchers (created by staff) must go through CEO approval
                    // Status: pending_hod → pending_ceo → approved_for_payment → paid
                $pettyCash->update([
                    'status' => 'pending_ceo',
                    'hod_id' => Auth::id(),
                    'hod_approved_at' => now(),
                    'hod_comments' => $request->comments,
                ]);
                
                // Prepare complete notification data
                $notificationData = [
                    'voucher_no' => $voucherNo,
                    'staff_name' => $creator->name,
                    'amount' => $pettyCash->amount,
                    'purpose' => $pettyCash->purpose,
                    'payee' => $pettyCash->payee,
                    'status' => 'pending_ceo',
                    'request_date' => $pettyCash->created_at->format('M j, Y g:i A'),
                    'document_attached' => !empty($pettyCash->attachments),
                    'document_name' => !empty($pettyCash->attachments) ? count($pettyCash->attachments) . ' document(s) attached' : null,
                    'hod_comments' => $request->comments ?? null,
                ];
                
                // Add employee/department info if available
                if ($creator->employee) {
                    $notificationData['employee_id'] = $creator->employee->employee_id ?? null;
                    $notificationData['department'] = $creator->employee->department->name ?? null;
                }
                
                // Notify HOD of successful approval
                $this->notificationService->notify(
                    $hod->id,
                    "Dear {$hod->name},\n\nYou have approved petty cash request #{$voucherNo} from staff {$creator->name} for TZS " . number_format($pettyCash->amount, 2) . " and it has been forwarded to CEO for final approval.\n\nPurpose: {$pettyCash->purpose}\nPayee: {$pettyCash->payee}" . ($request->comments ? "\n\nYour Comments: {$request->comments}" : ''),
                    route('petty-cash.hod.index'),
                    'Petty Cash Request Approved',
                    $notificationData
                );
                
                \Log::info('HOD Approval - Regular Voucher', [
                    'voucher_no' => $pettyCash->voucher_no,
                    'created_by' => $pettyCash->created_by,
                    'accountant_id' => $pettyCash->accountant_id,
                    'is_direct_voucher' => false,
                    'new_status' => 'pending_ceo',
                    'next_step' => 'CEO Approval → Payment',
                    'message' => 'Regular voucher approved - forwarded to CEO',
                ]);
                
                // Notify staff
                $this->notificationService->notify(
                    $creator->id,
                    "Dear {$creator->name},\n\nYour petty cash request #{$voucherNo} for TZS " . number_format($pettyCash->amount, 2) . " has been approved by HOD and is pending CEO approval.\n\nPurpose: {$pettyCash->purpose}\nPayee: {$pettyCash->payee}" . ($request->comments ? "\n\nHOD Comments: {$request->comments}" : ''),
                    route('petty-cash.index'),
                    'Petty Cash Request Approved by HOD',
                    $notificationData
                );
                
                // Notify CEO
                $this->notificationService->notifyCEO(
                    "Dear CEO,\n\nNew petty cash request #{$voucherNo} from staff {$creator->name} for TZS " . number_format($pettyCash->amount, 2) . " is pending your final approval.\n\nPurpose: {$pettyCash->purpose}\nPayee: {$pettyCash->payee}" . (!empty($pettyCash->attachments) ? "\n\nDocuments have been attached to this request." : ''),
                    route('petty-cash.pending-ceo'),
                    'New Petty Cash Request Pending Final Approval',
                    $notificationData
                );
                
                $message = 'Request approved and forwarded to CEO for final approval.';
                }
            } else {
                $pettyCash->update([
                    'status' => 'rejected',
                    'hod_id' => Auth::id(),
                    'hod_approved_at' => now(),
                    'hod_comments' => $request->comments,
                ]);
                
                // Notify creator of rejection
                if ($isDirectVoucher) {
                    // Direct voucher rejection - notify accountant
                    $this->notificationService->notify(
                        $creator->id,
                        "Your direct petty cash voucher #{$voucherNo} has been rejected by HOD. Please check the comments.",
                        route('petty-cash.direct-vouchers.index'),
                        'Direct Voucher Rejected'
                    );
                    
                    // Also notify HOD
                    $this->notificationService->notify(
                        $hod->id,
                        "You have rejected direct petty cash voucher #{$voucherNo} created by {$creator->name}.",
                        route('petty-cash.direct-vouchers.index'),
                        'Direct Voucher Rejected'
                    );
                    
                    $message = 'Direct voucher rejected.';
                } else {
                    // Regular voucher rejection
                    $this->notificationService->notify(
                        $creator->id,
                        "Your petty cash request #{$voucherNo} has been rejected by HOD. Please check the comments.",
                        route('petty-cash.index'),
                        'Petty Cash Request Rejected'
                    );
                    
                    $message = 'Request rejected.';
                }
            }

            DB::commit();
            
            // Log activity
            ActivityLogService::logAction('petty_cash_hod_' . $request->action, ucfirst($request->action) . " petty cash voucher #{$voucherNo} by HOD", $pettyCash, [
                'voucher_no' => $voucherNo,
                'action' => $request->action,
                'comments' => $request->comments,
                'is_direct_voucher' => $isDirectVoucher ?? false,
                'new_status' => $pettyCash->status,
            ]);
            
            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'is_direct_voucher' => $isDirectVoucher ?? false,
                    'new_status' => $pettyCash->status
                ]);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('HOD approve error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'voucher_id' => $pettyCash->id,
                'user_id' => Auth::id()
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process request: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to process request: ' . $e->getMessage());
        }
    }





    // CEO methods
    public function ceoIndex(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has CEO role
        if (!$user->hasAnyRole(['CEO', 'Director', 'System Admin'])) {
            abort(403);
        }
        
        // Handle empty status parameter - default to pending_ceo
        $status = $request->get('status');
        if (empty($status) || $status === '') {
            $status = 'pending_ceo';
        }
        $type = $request->get('type', 'regular'); // 'regular' or 'direct'
        
        // Get counts for regular vouchers
        $pendingCeoCount = PettyCashVoucher::where('status', 'pending_ceo')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        $approvedCount = PettyCashVoucher::where('status', 'approved_for_payment')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        $paidCount = PettyCashVoucher::where('status', 'paid')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        $rejectedCount = PettyCashVoucher::where('status', 'rejected')
            ->whereNotNull('ceo_id')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        $retiredCount = PettyCashVoucher::where('status', 'retired')
            ->whereNotNull('ceo_id')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })
            ->count();
        
        // Get counts for direct vouchers (already used vouchers - these don't go to CEO)
        // But we'll show them for reference
        $directPendingHodCount = PettyCashVoucher::where('status', 'pending_hod')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->whereNotNull('accountant_verified_at')
            ->count();
        $directPaidCount = PettyCashVoucher::where('status', 'paid')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->count();
        $directRejectedCount = PettyCashVoucher::where('status', 'rejected')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->whereNotNull('hod_id')
            ->count();
        
        // Build query based on status and type
        $query = PettyCashVoucher::with(['lines', 'creator', 'accountant', 'hod', 'creator.primaryDepartment']);
        
        if ($type === 'all') {
            // Show all vouchers (both regular and direct) - no filtering by type
            // No status filtering either - show all statuses
        } elseif ($type === 'direct') {
            // Direct vouchers only (for reference - these don't require CEO approval)
            $query->whereColumn('created_by', 'accountant_id')
                  ->whereNotNull('accountant_id')
                  ->whereNotNull('accountant_verified_at');
            
            switch($status) {
                case 'pending_hod':
                    $query->where('status', 'pending_hod');
                    break;
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'rejected':
                    $query->where('status', 'rejected')
                          ->whereNotNull('hod_id');
                    break;
                default:
                    $query->where('status', 'pending_hod');
            }
        } else {
            // Regular vouchers only
            $query->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            });
            
            switch($status) {
                case 'pending_ceo':
                    $query->where('status', 'pending_ceo');
                    break;
                case 'approved':
                    $query->where('status', 'approved_for_payment');
                    break;
                case 'paid':
                    $query->where('status', 'paid');
                    break;
                case 'rejected':
                    $query->where('status', 'rejected')
                          ->whereNotNull('ceo_id');
                    break;
                case 'retired':
                    $query->where('status', 'retired')
                          ->whereNotNull('ceo_id');
                    break;
                default:
                    $query->where('status', 'pending_ceo');
            }
        }
        
        $vouchers = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->query());
        
        $counts = [
            'pending_ceo' => $pendingCeoCount,
            'approved' => $approvedCount,
            'paid' => $paidCount,
            'rejected' => $rejectedCount,
            'retired' => $retiredCount,
            // Direct voucher counts (for reference)
            'direct_pending_hod' => $directPendingHodCount,
            'direct_paid' => $directPaidCount,
            'direct_rejected' => $directRejectedCount,
        ];
        
        return view('modules.finance.petty-ceo', compact('vouchers', 'status', 'type', 'counts'));
    }

    public function ceoApprove(Request $request, PettyCashVoucher $pettyCash)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string',
        ]);

        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can approve at any level, others must wait for pending_ceo
        if (!$isSystemAdmin && $pettyCash->status !== 'pending_ceo') {
            return redirect()->back()->with('error', 'This request is not pending CEO approval.');
        }

        DB::beginTransaction();
        try {
            $voucherNo = $pettyCash->voucher_no;
            $creator = $pettyCash->creator;
            $ceo = Auth::user();
            
            if ($request->action === 'approve') {
                $pettyCash->update([
                    'status' => 'approved_for_payment',
                    'ceo_id' => Auth::id(),
                    'ceo_approved_at' => now(),
                    'ceo_comments' => $request->comments,
                ]);
                
                // Prepare complete notification data
                $notificationData = [
                    'voucher_no' => $voucherNo,
                    'staff_name' => $creator->name,
                    'amount' => $pettyCash->amount,
                    'purpose' => $pettyCash->purpose,
                    'payee' => $pettyCash->payee,
                    'status' => 'approved_for_payment',
                    'request_date' => $pettyCash->created_at->format('M j, Y g:i A'),
                    'document_attached' => !empty($pettyCash->attachments),
                    'document_name' => !empty($pettyCash->attachments) ? count($pettyCash->attachments) . ' document(s) attached' : null,
                    'ceo_comments' => $request->comments ?? null,
                ];
                
                // Add employee/department info if available
                if ($creator->employee) {
                    $notificationData['employee_id'] = $creator->employee->employee_id ?? null;
                    $notificationData['department'] = $creator->employee->department->name ?? null;
                }
                
                // Notify CEO of successful approval
                $this->notificationService->notify(
                    $ceo->id,
                    "Dear {$ceo->name},\n\nYou have approved petty cash request #{$voucherNo} from staff {$creator->name} for TZS " . number_format($pettyCash->amount, 2) . ". It is now ready for payment.\n\nPurpose: {$pettyCash->purpose}\nPayee: {$pettyCash->payee}" . ($request->comments ? "\n\nYour Comments: {$request->comments}" : ''),
                    route('petty-cash.pending-ceo'),
                    'Petty Cash Request Approved',
                    $notificationData
                );
                
                // Notify staff
                $this->notificationService->notify(
                    $creator->id,
                    "Dear {$creator->name},\n\nYour petty cash request #{$voucherNo} for TZS " . number_format($pettyCash->amount, 2) . " has been approved by CEO and is ready for payment.\n\nPurpose: {$pettyCash->purpose}\nPayee: {$pettyCash->payee}" . ($request->comments ? "\n\nCEO Comments: {$request->comments}" : '') . "\n\nPlease expect payment processing within the standard timeframe.",
                    route('petty-cash.index'),
                    'Petty Cash Request Approved by CEO',
                    $notificationData
                );
                
                // Notify accountant to proceed with payment
                $this->notificationService->notifyAccountant(
                    "Dear Accountant,\n\nPetty cash request #{$voucherNo} from staff {$creator->name} for TZS " . number_format($pettyCash->amount, 2) . " has been approved by CEO and is ready for payment.\n\nPurpose: {$pettyCash->purpose}\nPayee: {$pettyCash->payee}" . (!empty($pettyCash->attachments) ? "\n\nDocuments have been attached to this request." : '') . "\n\nPlease proceed with payment processing.",
                    route('petty-cash.approved'),
                    'Petty Cash Request Ready for Payment',
                    $notificationData
                );
                
                $message = 'Request approved for payment.';
            } else {
                $pettyCash->update([
                    'status' => 'rejected',
                    'ceo_id' => Auth::id(),
                    'ceo_approved_at' => now(),
                    'ceo_comments' => $request->comments,
                ]);
                
                // Notify staff of rejection
                $this->notificationService->notify(
                    $creator->id,
                    "Your petty cash request #{$voucherNo} has been rejected by CEO. Please check the comments.",
                    route('petty-cash.index'),
                    'Petty Cash Request Rejected'
                );
                
                $message = 'Request rejected.';
            }

            DB::commit();
            
            // Log activity
            ActivityLogService::logAction('petty_cash_ceo_' . $request->action, ucfirst($request->action) . " petty cash voucher {$voucherNo} by CEO", $pettyCash, [
                'voucher_no' => $voucherNo,
                'action' => $request->action,
                'comments' => $request->comments,
                'approved_by' => Auth::user()->name,
            ]);
            
            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'new_status' => $pettyCash->status
                ]);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('CEO approve error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'voucher_id' => $pettyCash->id,
                'user_id' => Auth::id()
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process request: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to process request: ' . $e->getMessage());
        }
    }

    // Payment methods
    public function paymentIndex()
    {
        $vouchers = PettyCashVoucher::where('status', 'approved_for_payment')
            ->with(['lines', 'creator', 'accountant', 'hod', 'ceo'])
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        // Calculate statistics
        $stats = (object)[
            'total_count' => PettyCashVoucher::where('status', 'approved_for_payment')->count(),
            'today_count' => PettyCashVoucher::where('status', 'approved_for_payment')
                ->whereDate('created_at', today())
                ->count(),
            'total_amount' => PettyCashVoucher::where('status', 'approved_for_payment')
                ->sum('amount'),
            'today_amount' => PettyCashVoucher::where('status', 'approved_for_payment')
                ->whereDate('created_at', today())
                ->sum('amount'),
        ];

        return view('modules.finance.petty-payment', compact('vouchers', 'stats'));
    }

    public function markPaid(Request $request, PettyCashVoucher $pettyCash)
    {
        $request->validate([
            'payment_method' => 'required|string|max:50',
            'paid_amount' => 'required|numeric|min:0.01',
            'payment_currency' => 'nullable|string|max:10',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:100',
            'payment_reference' => 'nullable|string|max:150',
            'payment_notes' => 'nullable|string',
            'payment_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can mark as paid at any level, others must wait for approved_for_payment
        if (!$isSystemAdmin && $pettyCash->status !== 'approved_for_payment') {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This request is not approved for payment.'
                ], 400);
            }
            return redirect()->back()->with('error', 'This request is not approved for payment.');
        }

        $attachmentPath = null;
        if ($request->hasFile('payment_attachment')) {
            $file = $request->file('payment_attachment');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('petty-cash/payments', $filename, 'public');
        }

        $updateData = [
            'status' => 'paid',
            'paid_by' => Auth::id(),
            'paid_at' => now(),
        ];
        // Only set detail fields if columns exist (prevents SQL error before migration runs)
        if (Schema::hasColumn('petty_cash_vouchers', 'payment_method')) $updateData['payment_method'] = $request->payment_method;
        if (Schema::hasColumn('petty_cash_vouchers', 'paid_amount')) $updateData['paid_amount'] = $request->paid_amount;
        if (Schema::hasColumn('petty_cash_vouchers', 'payment_currency')) $updateData['payment_currency'] = $request->payment_currency;
        if (Schema::hasColumn('petty_cash_vouchers', 'bank_name')) $updateData['bank_name'] = $request->bank_name;
        if (Schema::hasColumn('petty_cash_vouchers', 'account_number')) $updateData['account_number'] = $request->account_number;
        if (Schema::hasColumn('petty_cash_vouchers', 'payment_reference')) $updateData['payment_reference'] = $request->payment_reference;
        if (Schema::hasColumn('petty_cash_vouchers', 'payment_notes')) $updateData['payment_notes'] = $request->payment_notes;
        if (Schema::hasColumn('petty_cash_vouchers', 'payment_attachment_path')) $updateData['payment_attachment_path'] = $attachmentPath;

        try {
            DB::beginTransaction();
            
            $pettyCash->update($updateData);
            
            // Create General Ledger entries
            $amount = $request->paid_amount ?? $pettyCash->amount;
            $voucherNo = $pettyCash->voucher_no;
            $description = "Petty Cash Payment - {$voucherNo}: {$pettyCash->purpose}";
            
            // Get GL Account (expense account) from voucher
            $glAccount = null;
            if ($pettyCash->gl_account_id) {
                $glAccount = GlAccount::find($pettyCash->gl_account_id);
            }
            
            // Get Cash Box if available
            $cashBox = null;
            if ($pettyCash->cash_box_id) {
                $cashBox = CashBox::find($pettyCash->cash_box_id);
            }
            
            // Find or create Chart of Account for expense
            $expenseAccount = null;
            if ($glAccount) {
                // Map GlAccount to ChartOfAccount
                $expenseAccount = ChartOfAccount::where('code', $glAccount->code)
                    ->orWhere('name', $glAccount->name)
                    ->first();
                
                // If not found, create one based on GlAccount
                if (!$expenseAccount && $glAccount->category) {
                    $accountType = $this->mapCategoryToType($glAccount->category);
                    $accountCategory = $this->mapCategoryToEnum($glAccount->category, $accountType);
                    
                    $expenseAccount = ChartOfAccount::create([
                        'code' => $glAccount->code,
                        'name' => $glAccount->name,
                        'type' => $accountType,
                        'category' => $accountCategory,
                        'is_active' => true,
                    ]);
                }
            }
            
            // Find or create Chart of Account for cash/bank
            $cashAccount = null;
            if ($cashBox) {
                // Find cash account by cash box name or create default
                $cashAccount = ChartOfAccount::where('name', 'like', '%' . $cashBox->name . '%')
                    ->where('type', 'Asset')
                    ->first();
                
                if (!$cashAccount) {
                    // Create default cash account
                    $cashAccount = ChartOfAccount::firstOrCreate(
                        ['code' => 'CASH-' . strtoupper($cashBox->currency)],
                        [
                            'name' => 'Cash - ' . $cashBox->name,
                            'type' => 'Asset',
                            'category' => 'Current Asset',
                            'is_active' => true,
                        ]
                    );
                }
            } else {
                // Default cash account if no cash box
                $cashAccount = ChartOfAccount::where('code', 'CASH')
                    ->orWhere('name', 'like', '%Cash%')
                    ->where('type', 'Asset')
                    ->first();
                
                if (!$cashAccount) {
                    $cashAccount = ChartOfAccount::firstOrCreate(
                        ['code' => 'CASH'],
                        [
                            'name' => 'Cash on Hand',
                            'type' => 'Asset',
                            'category' => 'Current Asset',
                            'is_active' => true,
                        ]
                    );
                }
            }
            
            // Ensure both accounts exist before creating entries (Double Entry Bookkeeping)
            if (!$expenseAccount || !$cashAccount) {
                throw new \Exception('Required Chart of Accounts not found. Please set up Expense and Cash accounts in Chart of Accounts.');
            }
            
            // Debit: Expense Account (increases expense)
            GeneralLedger::create([
                'account_id' => $expenseAccount->id,
                'transaction_date' => $pettyCash->paid_at ?? now(),
                'reference_type' => 'PettyCashVoucher',
                'reference_id' => $pettyCash->id,
                'reference_no' => $voucherNo,
                'type' => 'Debit',
                'amount' => $amount,
                'description' => $description,
                'source' => 'petty_cash',
                'created_by' => Auth::id(),
            ]);
            
            // Credit: Cash Account (decreases cash)
            GeneralLedger::create([
                'account_id' => $cashAccount->id,
                'transaction_date' => $pettyCash->paid_at ?? now(),
                'reference_type' => 'PettyCashVoucher',
                'reference_id' => $pettyCash->id,
                'reference_no' => $voucherNo,
                'type' => 'Credit',
                'amount' => $amount,
                'description' => $description,
                'source' => 'petty_cash',
                'created_by' => Auth::id(),
            ]);
            
            // Update cash box balance if cash payment
            if ($cashBox && $request->payment_method === 'cash') {
                $cashBox->current_balance -= $amount;
                if ($cashBox->current_balance < 0) {
                    \Log::warning('Cashbox balance went negative', [
                        'cashbox_id' => $cashBox->id,
                        'balance' => $cashBox->current_balance,
                        'voucher_id' => $pettyCash->id
                    ]);
                }
                $cashBox->save();
            }
            
            DB::commit();
        } catch (\Illuminate\Database\QueryException $ex) {
            DB::rollBack();
            // If columns don't exist yet, persist minimal payment info and continue
            if (str_contains(strtolower($ex->getMessage()), 'unknown column')) {
                $pettyCash->update([
                    'status' => 'paid',
                    'paid_by' => Auth::id(),
                    'paid_at' => now(),
                ]);
                // Tag request so UI can warn user
                $request->merge(['_payment_details_skipped' => true]);
            } else {
                throw $ex;
            }
        }

        // Send notifications
        try {
            $voucherNo = $pettyCash->voucher_no;
            $creator = $pettyCash->creator;
            $accountant = Auth::user();
            $amount = $request->paid_amount ?? $pettyCash->amount;
            $currency = $request->payment_currency ?? 'TZS';
            
            // Notify staff that payment has been made
            $this->notificationService->notify(
                $creator->id,
                "Your petty cash request #{$voucherNo} has been paid. Amount: " . number_format($amount, 2) . " {$currency}. Please submit your receipts to complete the retirement process.",
                route('petty-cash.index'),
                'Petty Cash Payment Completed',
                ['voucher_no' => $voucherNo, 'amount' => number_format($amount, 2)]
            );
            
            // Notify accountant
            $this->notificationService->notify(
                $accountant->id,
                "Payment processed successfully for petty cash request #{$voucherNo} from {$creator->name}.",
                route('petty-cash.payment.index'),
                'Payment Completed'
            );
        } catch (\Exception $e) {
            \Log::error('Notification error in petty cash payment: ' . $e->getMessage());
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $request->get('_payment_details_skipped') ? 'Payment recorded. Note: details were not saved (run DB migration).' : 'Payment recorded successfully.'
            ]);
        }
        return redirect()->back()->with(
            $request->get('_payment_details_skipped') ? 'warning' : 'success',
            $request->get('_payment_details_skipped') ? 'Payment recorded. Note: payment details were not saved (run DB migration).' : 'Payment recorded successfully.'
        );
    }
    
    /**
     * Map GlAccount category to ChartOfAccount type
     */
    private function mapCategoryToType($category)
    {
        $mapping = [
            'Assets' => 'Asset',
            'Liabilities' => 'Liability',
            'Equity' => 'Equity',
            'Income' => 'Income',
            'Expense' => 'Expense',
        ];
        
        return $mapping[$category] ?? 'Expense';
    }

    /**
     * Map GlAccount category to valid ChartOfAccount enum category
     */
    private function mapCategoryToEnum($glCategory, $type)
    {
        if (empty($glCategory)) {
            return null;
        }

        $glCategory = trim($glCategory);
        
        // Map based on type and category
        switch ($type) {
            case 'Asset':
                if (stripos($glCategory, 'Current') !== false) {
                    return 'Current Asset';
                } elseif (stripos($glCategory, 'Fixed') !== false) {
                    return 'Fixed Asset';
                } elseif (stripos($glCategory, 'Non-Current') !== false || stripos($glCategory, 'Non Current') !== false) {
                    return 'Non-Current Asset';
                }
                return 'Current Asset'; // Default for Asset
                
            case 'Liability':
                if (stripos($glCategory, 'Current') !== false) {
                    return 'Current Liability';
                } elseif (stripos($glCategory, 'Non-Current') !== false || stripos($glCategory, 'Non Current') !== false) {
                    return 'Non-Current Liability';
                }
                return 'Current Liability'; // Default for Liability
                
            case 'Equity':
                if (stripos($glCategory, 'Retained') !== false) {
                    return 'Retained Earnings';
                }
                return 'Equity'; // Default for Equity
                
            case 'Income':
                if (stripos($glCategory, 'Operating') !== false) {
                    return 'Operating Income';
                } elseif (stripos($glCategory, 'Non-Operating') !== false || stripos($glCategory, 'Non Operating') !== false) {
                    return 'Non-Operating Income';
                }
                return 'Operating Income'; // Default for Income
                
            case 'Expense':
                if (stripos($glCategory, 'Operating') !== false) {
                    return 'Operating Expense';
                } elseif (stripos($glCategory, 'Non-Operating') !== false || stripos($glCategory, 'Non Operating') !== false) {
                    return 'Non-Operating Expense';
                } elseif (stripos($glCategory, 'COGS') !== false || stripos($glCategory, 'Cost of Goods') !== false) {
                    return 'Cost of Goods Sold';
                }
                return 'Operating Expense'; // Default for Expense
                
            default:
                return null;
        }
    }



    
    // Retirement methods
    public function retirementIndex()
    {
        $user = Auth::user();
        
        // Staff sees their own retirements
        $myRetirements = PettyCashVoucher::where('status', 'paid')
            ->where('created_by', $user->id)
            ->with(['lines', 'creator', 'accountant', 'hod', 'ceo'])
            ->orderBy('paid_at', 'asc')
            ->get();

        // Accountant and System Admin see pending retirement reviews
        $pendingRetirements = collect();
        if ($user->hasAnyRole(['Accountant', 'System Admin'])) {
            $pendingRetirements = PettyCashVoucher::where('status', 'pending_retirement_review')
                ->with(['lines', 'creator', 'accountant', 'hod', 'ceo'])
                ->orderBy('paid_at', 'asc')
                ->get();
        }

        return view('modules.finance.petty-retirement', compact('myRetirements', 'pendingRetirements'));
    }

    public function submitRetirement(Request $request, PettyCashVoucher $pettyCash)
    {
        $request->validate([
            'retirement_receipts' => 'required|array|min:1',
            'retirement_receipts.*' => 'file|mimes:pdf,jpg,jpeg,png|max:10240',
            'retirement_comments' => 'nullable|string',
        ]);

        if ($pettyCash->status !== 'paid') {
            return redirect()->back()->with('error', 'This request is not paid yet.');
        }

        if ($pettyCash->created_by !== Auth::id()) {
            return redirect()->back()->with('error', 'You can only retire your own requests.');
        }

        DB::beginTransaction();
        try {
            // Handle retirement receipt uploads
            $receipts = [];
            foreach ($request->file('retirement_receipts') as $file) {
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('petty-cash/retirements', $filename, 'public');
                $receipts[] = $path;
            }

            $pettyCash->update([
                'status' => 'pending_retirement_review',
                'retirement_receipts' => $receipts,
                'retirement_comments' => $request->retirement_comments,
            ]);

            // Send notifications
            $voucherNo = $pettyCash->voucher_no;
            $creator = Auth::user();

            // Notify staff
            $this->notificationService->notify(
                $creator->id,
                "You have submitted retirement receipts for petty cash request #{$voucherNo}. It is now pending accountant review.",
                route('petty-cash.index'),
                'Retirement Submitted'
            );

            // Notify accountant
            $this->notificationService->notifyAccountant(
                "New retirement submitted for petty cash request #{$voucherNo} by {$creator->name}. Please review and approve.",
                route('petty-cash.pending-retirement'),
                'New Retirement Submitted',
                ['voucher_no' => $voucherNo, 'staff_name' => $creator->name]
            );

            DB::commit();
            return redirect()->back()->with('success', 'Retirement submitted for review.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to submit retirement: ' . $e->getMessage());
        }
    }

    public function approveRetirement(Request $request, PettyCashVoucher $pettyCash)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string',
        ]);

        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        // System Admin can approve at any level, others must wait for pending_retirement_review
        if (!$isSystemAdmin && $pettyCash->status !== 'pending_retirement_review') {
            return redirect()->back()->with('error', 'This retirement is not pending review.');
        }

        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            return redirect()->back()->with('error', 'Only accountants or System Admin can approve retirements.');
        }

        DB::beginTransaction();
        try {
            $voucherNo = $pettyCash->voucher_no;
            $creator = $pettyCash->creator;
            $accountant = Auth::user();

            if ($request->action === 'approve') {
                $pettyCash->update([
                    'status' => 'retired',
                    'retired_at' => now(),
                    'retirement_comments' => $request->comments ? 
                        ($pettyCash->retirement_comments . "\n\nAccountant: " . $request->comments) : 
                        $pettyCash->retirement_comments,
                ]);

                // Notify all parties (remove duplicates)
                $notifyIds = [$creator->id];
                if ($pettyCash->accountant_id) $notifyIds[] = $pettyCash->accountant_id;
                if ($pettyCash->hod_id) $notifyIds[] = $pettyCash->hod_id;
                if ($pettyCash->ceo_id) $notifyIds[] = $pettyCash->ceo_id;
                $notifyIds = array_unique($notifyIds);

                $this->notificationService->notify(
                    $notifyIds,
                    "Petty cash request #{$voucherNo} retirement has been approved and completed by {$accountant->name}.",
                    route('petty-cash.index'),
                    'Retirement Approved',
                    ['voucher_no' => $voucherNo, 'staff_name' => $creator->name]
                );

                $message = 'Retirement approved successfully.';
            } else {
                $pettyCash->update([
                    'status' => 'paid', // Return to paid status
                    'retirement_comments' => $request->comments ? 
                        ($pettyCash->retirement_comments . "\n\nAccountant Rejection: " . $request->comments) : 
                        $pettyCash->retirement_comments,
                ]);

                // Notify staff of rejection
                $this->notificationService->notify(
                    $creator->id,
                    "Your retirement for petty cash request #{$voucherNo} has been rejected. Please check the comments and resubmit.",
                    route('petty-cash.index'),
                    'Retirement Rejected'
                );

                $message = 'Retirement rejected.';
            }

            DB::commit();
            
            // Log activity
            ActivityLogService::logAction('petty_cash_retirement_' . $request->action, ucfirst($request->action) . " petty cash retirement for voucher {$voucherNo}", $pettyCash, [
                'voucher_no' => $voucherNo,
                'action' => $request->action,
                'comments' => $request->comments,
                'approved_by' => Auth::user()->name,
            ]);
            
            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'new_status' => $pettyCash->status
                ]);
            }
            
            return redirect()->back()->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Retirement approve error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'voucher_id' => $pettyCash->id,
                'user_id' => Auth::id()
            ]);
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process retirement: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to process retirement: ' . $e->getMessage());
        }
    }

    // API methods for AJAX
    public function getDetails(PettyCashVoucher $pettyCash)
    {
        try {
            $pettyCash->load(['lines', 'creator', 'accountant', 'hod', 'ceo']);

            $html = view('modules.finance.partials.petty-details', [
                'voucher' => $pettyCash,
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate PDF for petty cash voucher
     */
    public function generatePdf(PettyCashVoucher $pettyCash)
    {
        // Load all relationships
        $pettyCash->load(['lines', 'creator', 'accountant', 'hod', 'ceo', 'paidBy']);

        // Check access - user must be creator or have manager role
        $user = Auth::user();
        $userRoles = $user->roles()->pluck('name')->toArray();
        $canViewAll = !empty(array_intersect(['CEO', 'HR Officer', 'Accountant', 'HOD', 'System Admin'], $userRoles));

        if ($pettyCash->created_by !== $user->id && !$canViewAll) {
            abort(403, 'You are not authorized to view this voucher.');
        }

        try {
            // Initialize PDF service
            $pdfService = new PettyCashPdfService();

            // Generate HTML using Blade template (logo is handled by pdf-header component)
            $html = $pdfService->generateHtml($pettyCash);

            // Generate PDF
            $pdf = Pdf::loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);

            $filename = 'Petty_Cash_Voucher_' . $pettyCash->voucher_no . '_' . date('Y-m-d') . '.pdf';

            return $pdf->stream($filename);

        } catch (\Exception $e) {
            \Log::error('Petty cash PDF generation error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Unified Petty Cash Management Dashboard
     * Handles all roles (Accountant, HOD, CEO) in a single page
     */
    public function unified(Request $request)
    {
        $user = Auth::user();
        $filter = $request->get('filter', 'my_action');
        $search = $request->get('search');
        $statusFilter = $request->get('status');
        $dateFilter = $request->get('date');

        // Determine user role and what they can see
        $isAccountant = $user->hasAnyRole(['Accountant', 'System Admin']);
        $isHOD = $user->hasAnyRole(['HOD', 'System Admin']);
        $isCEO = $user->hasAnyRole(['CEO', 'System Admin']);

        // Build query
        $query = PettyCashVoucher::with(['lines', 'creator', 'accountant', 'hod', 'ceo']);

        // Apply filters based on role and filter type
        switch($filter) {
            case 'my_action':
                if ($isAccountant) {
                    $query->where('status', 'pending_accountant')
                          ->where(function($q) {
                              $q->whereColumn('created_by', '!=', 'accountant_id')
                                ->orWhereNull('accountant_id');
                          });
                } elseif ($isHOD) {
                    $query->where('status', 'pending_hod');
                } elseif ($isCEO) {
                    $query->where('status', 'pending_ceo');
                }
                break;

            case 'pending_hod':
                $query->where('status', 'pending_hod')
                      ->where(function($q) {
                          $q->whereColumn('created_by', '!=', 'accountant_id')
                            ->orWhereNull('accountant_id');
                      });
                break;

            case 'pending_ceo':
                $query->where('status', 'pending_ceo');
                break;

            case 'approved':
                $query->where('status', 'approved_for_payment');
                break;

            case 'paid':
                $query->where('status', 'paid');
                break;

            case 'retirement':
                if ($isAccountant) {
                    $query->where('status', 'pending_retirement_review');
                }
                break;

            case 'direct':
                $query->whereColumn('created_by', 'accountant_id')
                      ->whereNotNull('accountant_id')
                      ->whereNotNull('accountant_verified_at');
                break;

            case 'all':
                // Show all vouchers user has access to
                break;
        }

        // Apply additional filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('voucher_no', 'like', "%{$search}%")
                  ->orWhere('payee', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        if ($dateFilter) {
            $query->whereDate('date', $dateFilter);
        }

        $vouchers = $query->orderBy('created_at', 'desc')->paginate(20)->appends($request->except('page'));

        // Calculate counts - EXACTLY match the logic from accountantIndex and hodIndex methods
        // Regular vouchers pending accountant (exclude direct vouchers)
        $pendingAccountantCount = PettyCashVoucher::where('status', 'pending_accountant')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })->count();
        
        // Regular vouchers pending HOD (exclude direct vouchers) - this is what HOD sees for regular vouchers
        $pendingHodRegularCount = PettyCashVoucher::where('status', 'pending_hod')
            ->where(function($q) {
                $q->whereColumn('created_by', '!=', 'accountant_id')
                  ->orWhereNull('accountant_id');
            })->count();
        
        // Direct vouchers pending HOD
        $directPendingHodCount = PettyCashVoucher::where('status', 'pending_hod')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->whereNotNull('accountant_verified_at')
            ->count();
        
        // Pending CEO (all vouchers - regular vouchers only go to CEO, direct vouchers don't)
        $pendingCeoCount = PettyCashVoucher::where('status', 'pending_ceo')->count();
        
        // Approved for payment
        $approvedCount = PettyCashVoucher::where('status', 'approved_for_payment')->count();
        
        // Paid
        $paidCount = PettyCashVoucher::where('status', 'paid')->count();
        
        // Pending retirement
        $pendingRetirementCount = PettyCashVoucher::where('status', 'pending_retirement_review')->count();
        
        // Direct vouchers paid
        $directPaidCount = PettyCashVoucher::where('status', 'paid')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->count();
        
        // Direct vouchers rejected
        $directRejectedCount = PettyCashVoucher::where('status', 'rejected')
            ->whereColumn('created_by', 'accountant_id')
            ->whereNotNull('accountant_id')
            ->count();
        
        // Calculate counts array
        // For HOD: pending_hod should show ALL (regular + direct) because HOD sees both
        // For Accountant: pending_hod should show only regular (they see direct separately)
        $counts = [
            'pending_accountant' => $pendingAccountantCount,
            'pending_hod_regular' => $pendingHodRegularCount, // Regular vouchers only
            'pending_hod' => $isHOD ? ($pendingHodRegularCount + $directPendingHodCount) : $pendingHodRegularCount, // For HOD: all, for others: regular only
            'pending_ceo' => $pendingCeoCount,
            'approved' => $approvedCount,
            'paid' => $paidCount,
            'pending_retirement' => $pendingRetirementCount,
            'direct_pending' => $directPendingHodCount,
            'direct_paid' => $directPaidCount,
            'direct_rejected' => $directRejectedCount,
            'pending_action' => $this->getPendingActionCount($user),
            // Total amount of all pending vouchers
            'total_amount' => PettyCashVoucher::whereIn('status', ['pending_accountant', 'pending_hod', 'pending_ceo'])
                ->sum('amount')
        ];

        // If AJAX request, return JSON with HTML
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('modules.finance.partials.petty-vouchers-list', ['vouchers' => $vouchers])->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'counts' => $counts
            ]);
        }

        // Regular request - return full page
        return view('modules.finance.petty-unified', [
            'vouchers' => $vouchers,
            'counts' => $counts,
            'filter' => $filter
        ]);
    }

    /**
     * Get pending action count based on user role
     */
    private function getPendingActionCount($user)
    {
        if ($user->hasAnyRole(['Accountant', 'System Admin'])) {
            // Accountant: Regular vouchers pending verification (exclude direct vouchers)
            return PettyCashVoucher::where('status', 'pending_accountant')
                ->where(function($q) {
                    $q->whereColumn('created_by', '!=', 'accountant_id')
                      ->orWhereNull('accountant_id');
                })->count();
        } elseif ($user->hasAnyRole(['HOD', 'System Admin'])) {
            // HOD: All vouchers pending HOD approval (includes both regular and direct)
            return PettyCashVoucher::where('status', 'pending_hod')->count();
        } elseif ($user->hasAnyRole(['CEO', 'System Admin', 'Director'])) {
            // CEO: All vouchers pending CEO approval
            return PettyCashVoucher::where('status', 'pending_ceo')->count();
        }
        return 0;
    }

    /**
     * Get voucher details as partial for modal
     */
    public function details($pettyCash)
    {
        try {
            $voucher = PettyCashVoucher::with(['lines', 'creator', 'accountant', 'hod', 'ceo'])->findOrFail($pettyCash);
            
            $html = view('modules.finance.partials.petty-details', ['voucher' => $voucher])->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            \Log::error('Petty cash details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading details: ' . $e->getMessage()
            ], 500);
        }
    }
}
