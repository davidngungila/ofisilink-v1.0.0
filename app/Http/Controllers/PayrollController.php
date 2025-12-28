<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Services\TanzaniaStatutoryCalculator;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use App\Models\CashBox;
use App\Models\GlAccount;
use App\Models\EmployeeSalaryDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class PayrollController extends Controller
{
    protected $statutoryCalculator;

    public function __construct(TanzaniaStatutoryCalculator $statutoryCalculator)
    {
        $this->statutoryCalculator = $statutoryCalculator;
    }

    public function index(Request $request)
    {
        try {
        $user = Auth::user();
        
        // Determine access level
        $is_hr_officer = $user->hasRole('HR Officer');
        $is_admin = $user->hasRole('System Admin');
        $is_ceo = $user->hasRole('CEO');
        $is_hod = $user->hasRole('HOD');
        $is_accountant = $user->hasRole('Accountant');
        $is_staff = $user->hasRole('Staff');

        $can_process_payroll = $is_hr_officer || $is_admin;
        $can_review_payroll = $is_hod || $is_admin;
        $can_approve_payroll = $is_ceo || $is_admin;
        $can_pay_payroll = $is_accountant || $is_admin;
        $can_view_payroll = $can_process_payroll || $can_approve_payroll || $can_pay_payroll || $is_hod || $is_staff;

        $pageMode = ($can_process_payroll || $can_review_payroll || $can_approve_payroll || $can_pay_payroll) ? 'manager' : 'staff';

        $payrolls = [];
        $employees = [];
        $payroll_stats = [];
        $employee_details = null;

        if ($pageMode === 'manager') {
            // Use raw queries to ensure accurate totals
            $payrolls = Payroll::with(['processor', 'reviewer', 'approver', 'payer'])
                            ->select('payrolls.*')
                            ->selectRaw('(SELECT COUNT(*) FROM payroll_items WHERE payroll_items.payroll_id = payrolls.id) as employee_count')
                            ->selectRaw('(SELECT COALESCE(SUM(payroll_items.net_salary), 0) FROM payroll_items WHERE payroll_items.payroll_id = payrolls.id) as total_amount')
                            ->selectRaw('(SELECT COALESCE(SUM(payroll_items.total_employer_cost), 0) FROM payroll_items WHERE payroll_items.payroll_id = payrolls.id) as total_employer_cost')
                            ->orderBy('pay_period', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(20);

                // Load employees with relationships safely
                try {
            $employees = User::where('is_active', true)
                                    ->whereHas('employee')
                                    ->with([
                                        'primaryDepartment', 
                                        'employee',
                                        'bankAccounts' => function($query) {
                                            $query->orderBy('is_primary', 'desc')
                                                  ->orderBy('created_at', 'desc');
                                        },
                                        'salaryDeductions' => function($query) {
                                            $query->orderBy('is_active', 'desc')
                                                  ->orderBy('created_at', 'desc');
                                        }
                                    ])
                            ->get();
                } catch (\Exception $e) {
                    \Log::error('Error loading employees for payroll', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Fallback to basic employee loading
                    $employees = User::where('is_active', true)
                                    ->whereHas('employee')
                                    ->with(['primaryDepartment', 'employee'])
                                    ->get();
                }

            $current_month = Carbon::now()->format('Y-m');

            // Calculate payroll statistics using the helper method
            $stats = $this->getPayrollStatistics();

        } else { // Staff View
            // Initialize empty stats and employees for staff view
            $stats = [
                'total_processed' => 0,
                'pending_review' => 0,
                'pending_approval' => 0,
                'approved_unpaid' => 0,
                'current_month_total' => 0,
                'current_month_employer_cost' => 0,
                'employees_count' => 0,
            ];
            $employees = collect(); // Empty collection for staff view
            $payrolls = PayrollItem::where('employee_id', $user->id)
                                ->with('payroll')
                                ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                                ->orderBy('payrolls.pay_period', 'desc')
                                ->orderBy('payrolls.created_at', 'desc')
                                ->select('payroll_items.*')
                                ->paginate(12);

            $employee_details = User::where('id', $user->id)
                                    ->with('employee', 'primaryDepartment')
                                    ->first();
        }

        $recent_periods = Payroll::select('pay_period')->distinct()->orderByDesc('pay_period')->limit(6)->get();

        // Fetch GL Accounts from finance settings (GlAccount) - same as petty cash
        // These accounts are managed in /finance/settings
        $glAccounts = GlAccount::where('is_active', true)
            ->orderBy('code')
            ->get();
        
        // Fetch Chart of Accounts for double-entry bookkeeping
        $chartAccounts = \App\Models\ChartOfAccount::where('is_active', true)
            ->orderBy('type')
            ->orderBy('code')
            ->get()
            ->groupBy('type');
        
        // Still fetch cash boxes for optional cash payment tracking
        $cashBoxes = CashBox::where('is_active', true)->orderBy('name')->get();

        // Fetch all active deductions for summary
        $deductionsSummary = [];
        if ($pageMode === 'manager') {
            try {
                $deductionsSummary = EmployeeSalaryDeduction::where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                    })
                    ->with('employee')
                    ->get()
                    ->groupBy('employee_id')
                    ->map(function($deductions) {
                        return [
                            'employee_id' => $deductions->first()->employee_id,
                            'employee_name' => $deductions->first()->employee->name ?? 'N/A',
                            'monthly_total' => $deductions->where('frequency', 'monthly')->sum('amount'),
                            'one_time_total' => $deductions->where('frequency', 'one-time')->sum('amount'),
                            'count' => $deductions->count()
                        ];
                    })
                    ->values();
            } catch (\Exception $e) {
                \Log::warning('Failed to load deductions summary: ' . $e->getMessage());
            }
        }

        return view('modules.hr.payroll', compact(
            'payrolls', 'employees', 'stats', 'employee_details', 'recent_periods',
            'pageMode', 'can_process_payroll', 'can_review_payroll', 'can_approve_payroll', 'can_pay_payroll',
            'glAccounts', 'chartAccounts', 'cashBoxes', 'deductionsSummary'
        ));
            
        } catch (\Exception $e) {
            \Log::error('Payroll index error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a simple JSON error response to avoid the exception renderer issue
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while loading the payroll page.',
                    'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error'
                ], 500);
            }
            
            // For regular requests, return a simple HTML error
            return response('<html><body><h1>Error Loading Payroll</h1><p>An error occurred. Please check the logs or contact support.</p>' . (config('app.debug') ? '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>' : '') . '</body></html>', 500);
        }
    }

    private function getPageMode($user)
    {
        if ($user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director', 'HOD', 'Accountant'])) {
            return 'manager';
        }
        return 'staff';
    }

    private function managerView($user)
    {
        // Fetch payroll history with comprehensive data
        $payrolls = Payroll::with(['processedBy', 'reviewedBy', 'approvedBy', 'paidBy', 'items'])
            ->selectRaw('
                payrolls.*,
                COUNT(payroll_items.id) as employee_count,
                COALESCE(SUM(payroll_items.net_salary), 0) as total_amount,
                COALESCE(SUM(payroll_items.total_employer_cost), 0) as total_employer_cost
            ')
            ->leftJoin('payroll_items', 'payrolls.id', '=', 'payroll_items.payroll_id')
            ->groupBy('payrolls.id')
            ->orderBy('payrolls.pay_period', 'desc')
            ->orderBy('payrolls.created_at', 'desc')
            ->paginate(20);

        // Fetch active employees for payroll processing
        $employees = User::with([
            'employee', 
            'primaryDepartment',
            'bankAccounts' => function($query) {
                $query->where('is_primary', true)
                      ->orderBy('is_primary', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->limit(1);
            },
            'salaryDeductions' => function($query) {
                $query->where('is_active', true)
                      ->where(function($q) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                      });
            }
        ])
            ->where('is_active', true)
            ->whereHas('employee')
            ->orderBy('primary_department_id')
            ->orderBy('name')
            ->get();

        // Payroll statistics
        $stats = $this->getPayrollStatistics();

        return view('modules.hr.payroll', compact('payrolls', 'employees', 'stats'));
    }

    private function staffView($user)
    {
        // Fetch user's payroll history
        $payrolls = PayrollItem::with(['payroll', 'employee'])
            ->where('employee_id', $user->id)
            ->orderBy('payrolls.pay_period', 'desc')
            ->paginate(12);

        // Fetch current employee details
        $employeeDetails = $user->employee;

        return view('modules.hr.payroll-staff', compact('payrolls', 'employeeDetails'));
    }

    private function getPayrollStatistics()
    {
        try {
        $currentMonth = Carbon::now()->format('Y-m');
        $currentYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->format('Y-m');
        $lastYear = $currentYear - 1;
        
        \Log::info('Calculating payroll statistics', [
            'current_month' => $currentMonth,
            'current_year' => $currentYear,
        ]);

            // Use direct queries for better performance and accuracy
        $stats = [
                'total_processed' => (int)DB::table('payrolls')
                    ->where('status', '!=', 'cancelled')
                    ->count(),
                'pending_review' => (int)DB::table('payrolls')
                    ->where('status', 'processed')
                    ->count(),
                'pending_approval' => (int)DB::table('payrolls')
                    ->where('status', 'reviewed')
                    ->count(),
                'approved_unpaid' => (int)DB::table('payrolls')
                    ->where('status', 'approved')
                    ->count(),
                'current_month_total' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.pay_period', 'like', $currentMonth . '%')
                    ->whereIn('payrolls.status', ['processed', 'reviewed', 'approved', 'paid'])
                    ->sum('payroll_items.net_salary') ?? 0,
                'current_month_employer_cost' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.pay_period', 'like', $currentMonth . '%')
                    ->whereIn('payrolls.status', ['processed', 'reviewed', 'approved', 'paid'])
                    ->sum('payroll_items.total_employer_cost') ?? 0,
                'current_month_gross' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.pay_period', 'like', $currentMonth . '%')
                    ->whereIn('payrolls.status', ['processed', 'reviewed', 'approved', 'paid'])
                    ->selectRaw('COALESCE(SUM(basic_salary + overtime_amount + bonus_amount + allowance_amount), 0) as gross')
                    ->value('gross') ?? 0,
                'current_month_deductions' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.pay_period', 'like', $currentMonth . '%')
                    ->whereIn('payrolls.status', ['processed', 'reviewed', 'approved', 'paid'])
                    ->selectRaw('COALESCE(SUM(deduction_amount + nssf_amount + paye_amount + nhif_amount + heslb_amount + wcf_amount + sdl_amount + other_deductions), 0) as total')
                    ->value('total') ?? 0,
                'last_month_total' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.pay_period', 'like', $lastMonth . '%')
                    ->where('payrolls.status', 'paid')
                    ->sum('payroll_items.net_salary') ?? 0,
                'year_to_date_total' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.pay_period', 'like', $currentYear . '-%')
                    ->where('payrolls.status', 'paid')
                    ->sum('payroll_items.net_salary') ?? 0,
                'year_to_date_employer_cost' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.pay_period', 'like', $currentYear . '-%')
                    ->where('payrolls.status', 'paid')
                    ->sum('payroll_items.total_employer_cost') ?? 0,
                'last_year_total' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.pay_period', 'like', $lastYear . '-%')
                    ->where('payrolls.status', 'paid')
                    ->sum('payroll_items.net_salary') ?? 0,
                'employees_count' => (int)DB::table('users')
                    ->where('is_active', true)
                    ->whereExists(function($query) {
                        $query->select(DB::raw(1))
                              ->from('employees')
                              ->whereColumn('employees.user_id', 'users.id');
                    })
                    ->count(),
                'paid_payrolls_count' => (int)DB::table('payrolls')
                    ->where('status', 'paid')
                    ->count(),
                'total_all_time_net' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.status', 'paid')
                    ->sum('payroll_items.net_salary') ?? 0,
                'total_all_time_employer_cost' => (float)DB::table('payroll_items')
                    ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                    ->where('payrolls.status', 'paid')
                    ->sum('payroll_items.total_employer_cost') ?? 0,
                'average_monthly_payroll' => (float)(function() use ($currentYear) {
                    try {
                        $monthlyTotals = DB::table('payroll_items')
                            ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                            ->where('payrolls.status', 'paid')
                            ->selectRaw('pay_period, SUM(net_salary) as monthly_total')
                            ->groupBy('pay_period')
                            ->pluck('monthly_total');
                        
                        return $monthlyTotals->count() > 0 ? (float)$monthlyTotals->avg() : 0;
                    } catch (\Exception $e) {
                        \Log::warning('Error calculating average monthly payroll: ' . $e->getMessage());
                        return 0;
                    }
                })(),
            ];

            // Calculate monthly trends (last 12 months)
            $monthlyTrends = DB::table('payroll_items')
                ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                ->where('payrolls.status', 'paid')
                ->where('payrolls.pay_period', '>=', Carbon::now()->subMonths(11)->format('Y-m'))
                ->selectRaw('payrolls.pay_period, 
                    COALESCE(SUM(net_salary), 0) as net_total,
                    COALESCE(SUM(basic_salary + overtime_amount + bonus_amount + allowance_amount), 0) as gross_total,
                    COALESCE(SUM(deduction_amount + nssf_amount + paye_amount + nhif_amount + heslb_amount + wcf_amount + sdl_amount + other_deductions), 0) as deductions_total,
                    COALESCE(SUM(total_employer_cost), 0) as employer_cost_total,
                    COUNT(DISTINCT payroll_items.employee_id) as employee_count')
                ->groupBy('payrolls.pay_period')
                ->orderBy('payrolls.pay_period')
                ->get();

            $stats['monthly_trends'] = $monthlyTrends;

            // Department-wise breakdown
            $departmentStats = DB::table('payroll_items')
                ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                ->join('users', 'payroll_items.employee_id', '=', 'users.id')
                ->leftJoin('departments', 'users.primary_department_id', '=', 'departments.id')
                ->where('payrolls.pay_period', 'like', $currentMonth . '%')
                ->whereIn('payrolls.status', ['processed', 'reviewed', 'approved', 'paid'])
                ->selectRaw('departments.id, departments.name,
                    COUNT(DISTINCT payroll_items.employee_id) as employee_count,
                    COALESCE(SUM(payroll_items.net_salary), 0) as net_total,
                    COALESCE(SUM(payroll_items.basic_salary + payroll_items.overtime_amount + payroll_items.bonus_amount + payroll_items.allowance_amount), 0) as gross_total,
                    COALESCE(SUM(payroll_items.deduction_amount + payroll_items.nssf_amount + payroll_items.paye_amount + payroll_items.nhif_amount + payroll_items.heslb_amount + payroll_items.wcf_amount + payroll_items.sdl_amount + payroll_items.other_deductions), 0) as deductions_total,
                    COALESCE(SUM(payroll_items.total_employer_cost), 0) as employer_cost_total,
                    COALESCE(AVG(payroll_items.net_salary), 0) as avg_net_salary')
                ->groupBy('departments.id', 'departments.name')
                ->orderByDesc('net_total')
                ->get();

            $stats['department_breakdown'] = $departmentStats;

            // Top earners (current month)
            $topEarners = DB::table('payroll_items')
                ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                ->join('users', 'payroll_items.employee_id', '=', 'users.id')
                ->leftJoin('employees', 'users.id', '=', 'employees.user_id')
                ->where('payrolls.pay_period', 'like', $currentMonth . '%')
                ->whereIn('payrolls.status', ['processed', 'reviewed', 'approved', 'paid'])
                ->selectRaw('users.id, users.name, employees.employee_id,
                    payroll_items.net_salary,
                    COALESCE((payroll_items.basic_salary + payroll_items.overtime_amount + payroll_items.bonus_amount + payroll_items.allowance_amount), 0) as gross_salary,
                    COALESCE((payroll_items.deduction_amount + payroll_items.nssf_amount + payroll_items.paye_amount + payroll_items.nhif_amount + payroll_items.heslb_amount + payroll_items.wcf_amount + payroll_items.sdl_amount + payroll_items.other_deductions), 0) as total_deductions')
                ->orderByDesc('payroll_items.net_salary')
                ->limit(10)
                ->get();

            $stats['top_earners'] = $topEarners;

            // Deduction analysis
            $deductionAnalysis = DB::table('payroll_items')
                ->join('payrolls', 'payroll_items.payroll_id', '=', 'payrolls.id')
                ->where('payrolls.pay_period', 'like', $currentMonth . '%')
                ->whereIn('payrolls.status', ['processed', 'reviewed', 'approved', 'paid'])
                ->selectRaw('
                    COALESCE(SUM(payroll_items.paye_amount), 0) as total_paye,
                    COALESCE(SUM(payroll_items.nssf_amount), 0) as total_nssf_employee,
                    COALESCE(SUM(payroll_items.employer_nssf), 0) as total_nssf_employer,
                    COALESCE(SUM(payroll_items.nhif_amount), 0) as total_nhif,
                    COALESCE(SUM(payroll_items.heslb_amount), 0) as total_heslb,
                    COALESCE(SUM(payroll_items.wcf_amount), 0) as total_wcf,
                    COALESCE(SUM(payroll_items.sdl_amount), 0) as total_sdl,
                    COALESCE(SUM(payroll_items.other_deductions), 0) as total_other_deductions,
                    COALESCE(SUM(payroll_items.deduction_amount), 0) as total_additional_deductions')
                ->first();

            $stats['deduction_analysis'] = $deductionAnalysis;

            \Log::info('Payroll statistics calculated', [
                'total_processed' => $stats['total_processed'],
                'current_month_total' => $stats['current_month_total'],
                'employees_count' => $stats['employees_count'],
            ]);

        return $stats;
        } catch (\Exception $e) {
            \Log::error('Error calculating payroll statistics', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            // Return default values on error
            return [
                'total_processed' => 0,
                'pending_review' => 0,
                'pending_approval' => 0,
                'approved_unpaid' => 0,
                'current_month_total' => 0,
                'current_month_employer_cost' => 0,
                'current_month_gross' => 0,
                'current_month_deductions' => 0,
                'last_month_total' => 0,
                'year_to_date_total' => 0,
                'year_to_date_employer_cost' => 0,
                'last_year_total' => 0,
                'employees_count' => 0,
                'paid_payrolls_count' => 0,
                'total_all_time_net' => 0,
                'total_all_time_employer_cost' => 0,
                'average_monthly_payroll' => 0,
                'monthly_trends' => collect(),
                'department_breakdown' => collect(),
                'top_earners' => collect(),
                'deduction_analysis' => (object)[],
            ];
        }
    }

    public function processPayroll(Request $request)
    {
        $request->validate([
            'pay_period' => 'required|string',
            'pay_date' => 'required|date',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
            'overtime_hours' => 'array',
            'bonus_amount' => 'array',
            'allowance_amount' => 'array',
            'deduction_amount' => 'array',
        ]);

        // Check if payroll already exists for this period
        $existingPayroll = Payroll::where('pay_period', $request->pay_period)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingPayroll) {
            return response()->json([
                'success' => false,
                'message' => "Payroll for period {$request->pay_period} has already been processed.",
                'employee_errors' => []
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Create payroll record
            $payroll = Payroll::create([
                'pay_period' => $request->pay_period,
                'pay_date' => $request->pay_date,
                'processed_by' => Auth::id(),
                'status' => 'processed',
            ]);

            $processedCount = 0;
            $employeeErrors = []; // Per-employee errors

            foreach ($request->employee_ids as $employeeId) {
                $employeeErrorList = [];
                $isValid = true;

                try {
                    $employee = User::with([
                        'employee',
                        'bankAccounts' => function($query) {
                            $query->where('is_primary', true)
                                  ->orderBy('is_primary', 'desc')
                                  ->orderBy('created_at', 'desc')
                                  ->limit(1);
                        },
                        'salaryDeductions' => function($query) {
                            $query->where('is_active', true)
                                  ->where(function($q) {
                                      $q->whereNull('end_date')
                                        ->orWhere('end_date', '>=', now());
                                  });
                        }
                    ])->find($employeeId);
                    
                    // Validate employee exists
                    if (!$employee || !$employee->employee) {
                        $employeeErrorList[] = "Employee not found or employee record missing.";
                        $employeeErrors[$employeeId] = $employeeErrorList;
                        continue;
                    }

                    // Validate employee has salary
                    if (!$employee->employee->salary || $employee->employee->salary <= 0) {
                        $employeeErrorList[] = "Employee has invalid or missing salary.";
                        $isValid = false;
                    }

                    $basicSalary = $employee->employee->salary ?? 0;
                    
                    // Get pre-calculated values from monthly records
                    $payPeriod = $request->pay_period; // Format: YYYY-MM
                    $overtimeRecord = \App\Models\EmployeeOvertime::where('employee_id', $employeeId)
                        ->where('month', $payPeriod)
                        ->where('is_active', true)
                        ->first();
                    $bonusRecord = \App\Models\EmployeeBonus::where('employee_id', $employeeId)
                        ->where('month', $payPeriod)
                        ->where('is_active', true)
                        ->first();
                    $allowanceRecord = \App\Models\EmployeeAllowance::where('employee_id', $employeeId)
                        ->where('month', $payPeriod)
                        ->where('is_active', true)
                        ->first();
                    
                    // Use pre-calculated values from monthly records
                    $overtimeHours = $overtimeRecord ? $overtimeRecord->hours : 0;
                    $overtimeAmount = $overtimeRecord ? $overtimeRecord->amount : 0;
                    $bonusAmount = $bonusRecord ? $bonusRecord->amount : 0;
                    $allowanceAmount = $allowanceRecord ? $allowanceRecord->amount : 0;
                    
                    // Additional deduction (still editable in process page)
                    $additionalDeduction = $this->validateNumericInput($request->deduction_amount[$employeeId] ?? 0, 0, null, 'Deduction amount', $employeeErrorList);

                    // Validate deduction doesn't exceed gross salary
                    $estimatedGross = $basicSalary + $overtimeAmount + $bonusAmount + $allowanceAmount;
                    if ($additionalDeduction > $estimatedGross * 0.5) {
                        $employeeErrorList[] = "Warning: Additional deduction exceeds 50% of estimated gross salary.";
                    }

                    if (!empty($employeeErrorList)) {
                        $employeeErrors[$employeeId] = $employeeErrorList;
                    }

                    if (!$isValid || !empty($employeeErrorList)) {
                        continue;
                    }

                    // Get deductions from employee_salary_deductions table
                    // Separate statutory deductions from other deductions
                    $statutoryDeductions = [
                        'PAYE' => 0,
                        'NSSF' => 0,
                        'NHIF' => 0,
                        'HESLB' => 0,
                        'WCF' => 0,
                        'SDL' => 0
                    ];
                    $otherDeductionsTotal = 0;
                    $fixedDeductionsList = [];
                    
                    if ($employee->relationLoaded('salaryDeductions') && $employee->salaryDeductions) {
                        foreach ($employee->salaryDeductions as $deduction) {
                            if (!$deduction->is_active) {
                                continue; // Skip inactive deductions
                            }
                            
                            $isApplicable = false;
                            if ($deduction->frequency === 'monthly') {
                                $isApplicable = true;
                            } elseif ($deduction->frequency === 'one-time' && 
                                     $deduction->start_date <= now() && 
                                     (!$deduction->end_date || $deduction->end_date >= now())) {
                                $isApplicable = true;
                            }
                            
                            if ($isApplicable) {
                                $deductionType = $deduction->deduction_type;
                                
                                // Check if it's a statutory deduction
                                if (in_array($deductionType, ['PAYE', 'NSSF', 'NHIF', 'HESLB', 'WCF', 'SDL'])) {
                                    // Use stored statutory deduction amount
                                    $statutoryDeductions[$deductionType] = $deduction->amount;
                                } else {
                                    // Other deductions (loans, advances, etc.)
                                    $otherDeductionsTotal += $deduction->amount;
                                }
                                
                                $fixedDeductionsList[] = [
                                    'type' => $deductionType,
                                    'amount' => $deduction->amount,
                                    'description' => $deduction->description
                                ];
                            }
                        }
                    }

                    // Use ONLY stored statutory deductions - NO CALCULATION
                    // If statutory deduction is not stored, it will be 0
                    $grossSalary = $basicSalary + $overtimeAmount + $bonusAmount + $allowanceAmount;
                    
                    // Use ONLY stored statutory deductions - NO CALCULATION
                    $finalStatutory = [
                        'PAYE' => $statutoryDeductions['PAYE'],
                        'NSSF' => $statutoryDeductions['NSSF'],
                        'NHIF' => $statutoryDeductions['NHIF'],
                        'HESLB' => $statutoryDeductions['HESLB'],
                        'WCF' => $statutoryDeductions['WCF'],
                        'SDL' => $statutoryDeductions['SDL']
                    ];
                    
                    // Calculate total deductions (ONLY stored statutory + other deductions)
                    $totalStatutoryDeductions = array_sum($finalStatutory);
                    $totalDeductions = $totalStatutoryDeductions + $otherDeductionsTotal + $additionalDeduction;
                    $netSalary = $grossSalary - $totalDeductions;
                    
                    // Build breakdown for response (use ONLY stored amounts)
                    $breakdown = [
                        'gross_salary' => $grossSalary,
                        'paye' => $finalStatutory['PAYE'],
                        'nssf' => ['employee' => $finalStatutory['NSSF'], 'employer' => 0], // No calculation
                        'nhif' => $finalStatutory['NHIF'],
                        'heslb' => $finalStatutory['HESLB'],
                        'wcf' => $finalStatutory['WCF'],
                        'sdl' => $finalStatutory['SDL'],
                        'other_deductions' => $otherDeductionsTotal + $additionalDeduction,
                        'total_deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'uses_stored_statutory' => array_filter($statutoryDeductions, fn($v) => $v > 0) // Track which statutory deductions were from storage
                    ];

                    // Validate net salary is positive
                    if ($breakdown['net_salary'] < 0) {
                        $employeeErrors[$employeeId] = ["Net salary is negative. Please review deductions."];
                        continue;
                    }

                    // Employer contributions - set to 0 since we're not calculating
                    $nssfEmployer = 0;
                    $wcfEmployer = 0;
                    $sdlEmployer = 0;
                    $totalEmployerCost = $grossSalary; // Only basic salary, no employer contributions calculated
                    
                    // Create payroll item with validation
                    $payrollItem = PayrollItem::create([
                        'payroll_id' => $payroll->id,
                        'employee_id' => $employeeId,
                        'basic_salary' => $basicSalary,
                        'overtime_hours' => $overtimeHours,
                        'overtime_amount' => $overtimeAmount,
                        'bonus_amount' => $bonusAmount,
                        'allowance_amount' => $allowanceAmount,
                        'deduction_amount' => $otherDeductionsTotal + $additionalDeduction, // Only non-statutory deductions
                        'nssf_amount' => $finalStatutory['NSSF'],
                        'paye_amount' => $finalStatutory['PAYE'],
                        'nhif_amount' => $finalStatutory['NHIF'],
                        'heslb_amount' => $finalStatutory['HESLB'],
                        'wcf_amount' => $finalStatutory['WCF'],
                        'sdl_amount' => $finalStatutory['SDL'],
                        'other_deductions' => $otherDeductionsTotal + $additionalDeduction,
                        'employer_nssf' => $nssfEmployer,
                        'employer_wcf' => $wcfEmployer,
                        'employer_sdl' => $sdlEmployer,
                        'total_employer_cost' => $totalEmployerCost,
                        'net_salary' => $netSalary,
                        'status' => 'processed',
                    ]);

                    if ($payrollItem && $payrollItem->id) {
                    $processedCount++;
                        \Log::info("Payroll item created successfully", [
                            'payroll_id' => $payroll->id,
                            'item_id' => $payrollItem->id,
                            'employee_id' => $employeeId,
                            'net_salary' => $payrollItem->net_salary,
                            'basic_salary' => $payrollItem->basic_salary
                        ]);
                    } else {
                        throw new \Exception("Failed to create payroll item for employee {$employeeId}");
                    }

                } catch (\Exception $e) {
                    $employeeErrors[$employeeId] = ["Processing error: " . $e->getMessage()];
                    \Log::error("Payroll processing error for employee {$employeeId}: " . $e->getMessage());
                }
            }

            if ($processedCount > 0) {
                // Send SMS notifications to HR and HOD
                try {
                    $notificationService = app(NotificationService::class);
                    
                    // Get HR officers
                    $hrOfficers = User::whereHas('roles', function($query) {
                        $query->where('name', 'HR Officer');
                    })->where('is_active', true)->get();
                    
                    // Get HODs (Head of Departments) - all active HODs
                    $hods = User::whereHas('roles', function($query) {
                        $query->where('name', 'HOD');
                    })->where('is_active', true)->get();
                    
                    $processor = Auth::user();
                    $payPeriod = $payroll->pay_period;
                    $payDate = $payroll->pay_date ? \Carbon\Carbon::parse($payroll->pay_date)->format('M d, Y') : 'N/A';
                    
                    // Calculate total amounts for notification
                    $totalNet = DB::table('payroll_items')
                        ->where('payroll_id', $payroll->id)
                        ->sum('net_salary');
                    
                    // Notify HR Officers
                    if ($hrOfficers->count() > 0) {
                        foreach ($hrOfficers as $hrOfficer) {
                            $message = "PAYROLL ALERT: Payroll for {$payPeriod} processed successfully. {$processedCount} employee(s), Total: TZS " . number_format($totalNet, 0) . ". Pay Date: {$payDate}. Waiting for HOD review. - OfisiLink";
                            $link = route('payroll.index');
                            try {
                                $notificationService->notify($hrOfficer->id, $message, $link, 'Payroll Processed Successfully');
                                \Log::info("Payroll notification sent to HR", ['hr_id' => $hrOfficer->id, 'name' => $hrOfficer->name]);
                            } catch (\Exception $e) {
                                \Log::warning('Failed to notify HR officer: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    // Notify HODs - they need to review
                    if ($hods->count() > 0) {
                        foreach ($hods as $hod) {
                            $message = "PAYROLL REVIEW REQUIRED: New payroll for period {$payPeriod} needs your review. {$processedCount} employee(s), Total: TZS " . number_format($totalNet, 0) . ". Processed by: {$processor->name}. Please review at: " . url('/payroll') . " - OfisiLink";
                            $link = route('payroll.index');
                            try {
                                $notificationService->notify($hod->id, $message, $link, 'Payroll Review Required - Action Needed');
                                \Log::info("Payroll review notification sent to HOD", ['hod_id' => $hod->id, 'name' => $hod->name]);
                            } catch (\Exception $e) {
                                \Log::warning('Failed to notify HOD: ' . $e->getMessage());
                            }
                        }
                    }
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to send payroll notifications: ' . $e->getMessage());
                    // Continue even if notifications fail
                }
                
                DB::commit();
                
                // Log activity
                ActivityLogService::logAction('payroll_processed', "Processed payroll for period {$payroll->pay_period} - {$processedCount} employees", $payroll, [
                    'pay_period' => $payroll->pay_period,
                    'pay_date' => $payroll->pay_date,
                    'processed_count' => $processedCount,
                    'status' => $payroll->status,
                ]);
                
                $message = "Payroll processed successfully for {$processedCount} employees. Waiting for HOD review. SMS notifications sent to HR and HOD.";
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'payroll_id' => $payroll->id,
                    'processed_count' => $processedCount,
                    'employee_errors' => $employeeErrors, // Return per-employee errors
                    'has_errors' => !empty($employeeErrors)
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => "No employees were processed successfully.",
                    'employee_errors' => $employeeErrors
                ], 400);
            }

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error("Payroll processing failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payroll: ' . $e->getMessage(),
                'employee_errors' => []
            ], 500);
        }
    }

    /**
     * Validate numeric input and return sanitized value
     */
    private function validateNumericInput($value, $min = 0, $max = null, $fieldName = '', &$errorList = [])
    {
        $value = floatval($value ?? 0);
        
        if ($value < $min) {
            $errorList[] = "{$fieldName} cannot be negative.";
            return 0;
        }
        
        if ($max !== null && $value > $max) {
            $errorList[] = "{$fieldName} exceeds maximum allowed value ({$max}).";
            return $max;
        }
        
        return $value;
    }

    public function reviewPayroll(Request $request, Payroll $payroll)
    {
        $request->validate([
            'review_action' => 'required|in:approve,reject',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        // Check if user can review this payroll (HOD can only review their department)
        if (!Auth::user()->hasRole('HOD') && !Auth::user()->hasRole('System Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only HOD can review payroll.'
            ], 403);
        }

        if ($payroll->status !== 'processed') {
            return response()->json([
                'success' => false,
                'message' => 'Payroll is not in processed status.'
            ], 400);
        }

        $newStatus = $request->review_action === 'approve' ? 'reviewed' : 'rejected';

        $payroll->update([
            'status' => $newStatus,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'review_notes' => $request->review_notes,
        ]);

        // Log activity
        ActivityLogService::logAction('payroll_reviewed', ucfirst($request->review_action) . " payroll for period {$payroll->pay_period}", $payroll, [
            'pay_period' => $payroll->pay_period,
            'action' => $request->review_action,
            'reviewed_by' => Auth::user()->name,
            'review_notes' => $request->review_notes,
        ]);

        $actionMessage = $request->review_action === 'approve' ? 
            "Payroll reviewed successfully. Waiting for CEO approval." : 
            "Payroll rejected. HR needs to make adjustments.";

        return response()->json([
            'success' => true,
            'message' => $actionMessage
        ]);
    }

    public function approvePayroll(Request $request, Payroll $payroll)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $isSystemAdmin = $user->hasRole('System Admin');
        
        if (!$user->hasAnyRole(['CEO', 'Director', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only CEO/Director or System Admin can approve payroll.'
            ], 403);
        }

        // System Admin can approve at any level, others must wait for reviewed
        if (!$isSystemAdmin && $payroll->status !== 'reviewed') {
            return response()->json([
                'success' => false,
                'message' => 'Payroll must be reviewed before approval.'
            ], 400);
        }

        $payroll->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'approval_notes' => $request->approval_notes,
        ]);

        // Log activity
        ActivityLogService::logAction('payroll_approved', "Approved payroll for period {$payroll->pay_period}", $payroll, [
            'pay_period' => $payroll->pay_period,
            'approved_by' => Auth::user()->name,
            'approval_notes' => $request->approval_notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payroll approved successfully. Accountant can now process payments.'
        ]);
    }

    public function markAsPaid(Request $request, Payroll $payroll)
    {
        $request->validate([
            'payment_method' => 'required|in:bank_transfer,cash,cheque',
            'payment_date' => 'required|date',
            'transaction_ref' => 'nullable|string|max:100',
            'debit_account_id' => 'required|exists:chart_of_accounts,id',
            'credit_account_id' => 'required|exists:chart_of_accounts,id',
            'cash_box_id' => 'nullable|exists:cash_boxes,id',
            'transaction_details' => 'nullable|string|max:1000',
        ]);

        if (!Auth::user()->hasRole('Accountant') && !Auth::user()->hasRole('System Admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only Accountant can mark payroll as paid.'
            ], 403);
        }

        if ($payroll->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Payroll must be approved before payment.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $payroll->update([
                'status' => 'paid',
                'paid_by' => Auth::id(),
                'paid_at' => now(),
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'transaction_reference' => $request->transaction_ref,
            ]);

            // Update individual payroll items status
            $payroll->items()->update(['status' => 'paid']);

            // Reload items to calculate totals
            $payroll->load('items');
            
            // Calculate total payroll amount
            $totalNetSalary = $payroll->items->sum('net_salary');
            $totalGrossSalary = $payroll->items->sum('basic_salary') + 
                               $payroll->items->sum('overtime_amount') + 
                               $payroll->items->sum('bonus_amount') + 
                               $payroll->items->sum('allowance_amount');

            // Get selected Chart of Accounts for double-entry bookkeeping
            $debitAccount = ChartOfAccount::findOrFail($request->debit_account_id);
            $creditAccount = ChartOfAccount::findOrFail($request->credit_account_id);
            
            // Validate account types
            if ($debitAccount->type !== 'Expense') {
                return response()->json([
                    'success' => false,
                    'message' => 'Debit account must be an Expense account for salary payments.'
                ], 400);
            }
            
            if ($creditAccount->type !== 'Asset') {
                return response()->json([
                    'success' => false,
                    'message' => 'Credit account must be an Asset account (Cash/Bank) for salary payments.'
                ], 400);
            }

            // Create GL entries (Double Entry Bookkeeping)
            $referenceNo = 'PAYROLL-' . $payroll->pay_period . '-' . $payroll->id;
            $description = "Payroll payment for {$payroll->pay_period} - " . ($request->transaction_details ?? 'Salary payment');

            // Debit: Expense Account (Salary Expense)
            GeneralLedger::create([
                'account_id' => $debitAccount->id,
                'transaction_date' => $request->payment_date,
                'reference_type' => 'Payroll',
                'reference_id' => $payroll->id,
                'reference_no' => $referenceNo,
                'type' => 'Debit',
                'amount' => $totalNetSalary,
                'balance' => 0, // Will be calculated by GL system
                'description' => $description,
                'source' => 'Payroll',
                'created_by' => Auth::id(),
            ]);

            // Credit: Asset Account (Cash/Bank)
            GeneralLedger::create([
                'account_id' => $creditAccount->id,
                'transaction_date' => $request->payment_date,
                'reference_type' => 'Payroll',
                'reference_id' => $payroll->id,
                'reference_no' => $referenceNo,
                'type' => 'Credit',
                'amount' => $totalNetSalary,
                'balance' => 0, // Will be calculated by GL system
                'description' => $description,
                'source' => 'Payroll',
                'created_by' => Auth::id(),
            ]);

            // Update cashbox balance if cash payment
            if ($request->payment_method === 'cash' && $request->cash_box_id) {
                $cashBox = CashBox::findOrFail($request->cash_box_id);
                $cashBox->current_balance -= $totalNetSalary;
                if ($cashBox->current_balance < 0) {
                    \Log::warning('Cashbox balance went negative', [
                        'cashbox_id' => $cashBox->id,
                        'balance' => $cashBox->current_balance,
                        'payroll_id' => $payroll->id
                    ]);
                }
                $cashBox->save();
            }

            // Update payroll with GL accounts info
            $payroll->update([
                'gl_account_id' => $creditAccount->id, // Store credit account (cash/bank) for reference
                'cash_box_id' => $request->cash_box_id,
                'transaction_details' => $request->transaction_details,
            ]);

            // Load payroll items with employee relationships for SMS
            $payroll->load(['items.employee']);
            
            // Send SMS notifications to all employees
            $notificationService = app(NotificationService::class);
            $smsResults = [];
            $successCount = 0;
            $failedCount = 0;
            
            foreach ($payroll->items as $item) {
                if ($item->employee) {
                    $employee = $item->employee;
                    $phone = $employee->mobile ?? $employee->phone;
                    
                    if ($phone) {
                        // Format phone number
                        $phone = preg_replace('/[^0-9]/', '', $phone);
                        if (!str_starts_with($phone, '255')) {
                            $phone = '255' . ltrim($phone, '0');
                        }
                        
                        // Prepare salary payment message
                        $payPeriod = \Carbon\Carbon::parse($payroll->pay_period . '-01')->format('F Y');
                        $netSalary = number_format($item->net_salary, 0);
                        $paymentMethod = ucfirst(str_replace('_', ' ', $request->payment_method));
                        
                        $smsMessage = "Dear {$employee->name}, your salary for {$payPeriod} has been processed. Net Amount: TZS {$netSalary}. Payment Method: {$paymentMethod}. Payment Date: " . \Carbon\Carbon::parse($request->payment_date)->format('d/m/Y') . ". Thank you! - OfisiLink";
                        
                        try {
                            $smsSent = $notificationService->sendSMS($phone, $smsMessage);
                            
                            $smsResults[] = [
                                'employee_id' => $employee->id,
                                'employee_name' => $employee->name,
                                'phone' => $phone,
                                'sent' => $smsSent,
                                'message' => $smsMessage
                            ];
                            
                            if ($smsSent) {
                                $successCount++;
                            } else {
                                $failedCount++;
                            }
                        } catch (\Exception $e) {
                            \Log::error('Failed to send payroll SMS to employee', [
                                'employee_id' => $employee->id,
                                'phone' => $phone,
                                'error' => $e->getMessage()
                            ]);
                            
                            $smsResults[] = [
                                'employee_id' => $employee->id,
                                'employee_name' => $employee->name,
                                'phone' => $phone,
                                'sent' => false,
                                'error' => $e->getMessage(),
                                'message' => $smsMessage
                            ];
                            
                            $failedCount++;
                        }
                    } else {
                        $smsResults[] = [
                            'employee_id' => $employee->id,
                            'employee_name' => $employee->name,
                            'phone' => null,
                            'sent' => false,
                            'error' => 'No phone number found',
                            'message' => null
                        ];
                        $failedCount++;
                    }
                }
            }

            DB::commit();

            // Log activity
            ActivityLogService::logAction('payroll_paid', "Marked payroll as paid for period {$payroll->pay_period}", $payroll, [
                'pay_period' => $payroll->pay_period,
                'payment_method' => $request->payment_method,
                'payment_date' => $request->payment_date,
                'total_net_salary' => $totalNetSalary,
                'total_gross_salary' => $totalGrossSalary,
                'paid_by' => Auth::user()->name,
            ]);

            $message = "Payroll marked as paid successfully. ";
            if ($successCount > 0) {
                $message .= "SMS notifications sent to {$successCount} employee(s). ";
            }
            if ($failedCount > 0) {
                $message .= "Failed to send SMS to {$failedCount} employee(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'sms_results' => $smsResults,
                'sms_summary' => [
                    'total' => count($smsResults),
                    'success' => $successCount,
                    'failed' => $failedCount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Failed to mark payroll as paid', [
                'payroll_id' => $payroll->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark payroll as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPayrollDetails(Payroll $payroll)
    {
        try {
            // Check authorization
            $user = Auth::user();
            if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director', 'HOD', 'Accountant'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view payroll details.'
                ], 403);
            }

        $payroll->load([
                'processor',
                'reviewer', 
                'approver',
                'payer',
                'items.employee' => function($query) {
                    $query->with([
                        'primaryDepartment',
                        'bankAccounts' => function($q) {
                            $q->where('is_primary', true)->orWhere(function($q2) {
                                $q2->orderBy('is_primary', 'desc');
                            });
                        },
                        'salaryDeductions' => function($q) {
                            $q->where('is_active', true)
                              ->where(function($q2) {
                                  $q2->whereNull('end_date')
                                    ->orWhere('end_date', '>=', now());
                              });
                        }
                    ]);
                }
            ]);

            // Calculate totals and prepare data for view
            $totals = [
                'basic_salary' => $payroll->items->sum('basic_salary'),
                'overtime_amount' => $payroll->items->sum('overtime_amount'),
                'bonus_amount' => $payroll->items->sum('bonus_amount'),
                'allowance_amount' => $payroll->items->sum('allowance_amount'),
                'gross_salary' => 0,
                'nssf_amount' => $payroll->items->sum('nssf_amount'),
                'nhif_amount' => $payroll->items->sum('nhif_amount'),
                'heslb_amount' => $payroll->items->sum('heslb_amount'),
                'paye_amount' => $payroll->items->sum('paye_amount'),
                'deduction_amount' => $payroll->items->sum('deduction_amount'),
                'total_deductions' => 0,
                'net_salary' => $payroll->items->sum('net_salary'),
                'total_employer_cost' => $payroll->items->sum('total_employer_cost'),
            ];

            $totals['gross_salary'] = $totals['basic_salary'] 
                + $totals['overtime_amount'] 
                + $totals['bonus_amount'] 
                + $totals['allowance_amount'];

            $totals['total_deductions'] = $totals['nssf_amount'] 
                + $totals['nhif_amount'] 
                + $totals['heslb_amount'] 
                + $totals['paye_amount'] 
                + $totals['deduction_amount'];

            // Generate HTML for the modal content
            $html = view('modules.hr.partials.payroll-details', [
                'payroll' => $payroll,
                'items' => $payroll->items,
                'totals' => $totals
            ])->render();

        return response()->json([
            'success' => true,
                'html' => $html,
            'payroll' => $payroll,
                'items' => $payroll->items,
                'totals' => $totals
            ]);

        } catch (\Exception $e) {
            \Log::error('Payroll details error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load payroll details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPayslip($payrollItem)
    {
        try {
            // Handle route model binding - accept both ID and model
            if (is_numeric($payrollItem)) {
                $payrollItem = PayrollItem::find($payrollItem);
            }
            
            if (!$payrollItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payslip not found.'
                ], 404);
            }
            
        // Check authorization
            $user = Auth::user();
            if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director', 'HOD', 'Accountant']) 
                && $payrollItem->employee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to view this payslip.'
            ], 403);
        }

            \Log::info('Loading payslip', ['payroll_item_id' => $payrollItem->id, 'user_id' => $user->id]);

            // Load relationships safely with try-catch for each
        $payrollItem->load([
            'payroll',
                'employee'
            ]);
            
            // Load optional relationships safely
            try {
                if ($payrollItem->employee) {
                    $payrollItem->employee->load([
                        'primaryDepartment', 
                        'employee',
                        'bankAccounts' => function($query) {
                            $query->where('is_primary', true)
                                  ->orderBy('is_primary', 'desc')
                                  ->orderBy('created_at', 'desc')
                                  ->limit(1);
                        },
                        'salaryDeductions' => function($query) {
                            $query->where('is_active', true)
                                  ->where(function($q) {
                                      $q->whereNull('end_date')
                                        ->orWhere('end_date', '>=', now());
                                  });
                        }
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load employee relationships: ' . $e->getMessage());
            }
            
            try {
                if ($payrollItem->payroll) {
                    $payrollItem->payroll->load('processor', 'reviewer', 'approver', 'payer');
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to load payroll relationships: ' . $e->getMessage());
            }

            // Ensure payroll is loaded
            if (!$payrollItem->payroll) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll record not found for this payslip.'
                ], 404);
            }
            
            // Ensure employee is loaded
            if (!$payrollItem->employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee record not found for this payslip.'
                ], 404);
            }

            // Format the response data with safe access
            $payslipData = [
                'id' => $payrollItem->id,
                'employee_id' => $payrollItem->employee_id,
                'basic_salary' => (float)($payrollItem->basic_salary ?? 0),
                'overtime_amount' => (float)($payrollItem->overtime_amount ?? 0),
                'overtime_hours' => (float)($payrollItem->overtime_hours ?? 0),
                'bonus_amount' => (float)($payrollItem->bonus_amount ?? 0),
                'allowance_amount' => (float)($payrollItem->allowance_amount ?? 0),
                'nssf_amount' => (float)($payrollItem->nssf_amount ?? 0),
                'nhif_amount' => (float)($payrollItem->nhif_amount ?? 0),
                'heslb_amount' => (float)($payrollItem->heslb_amount ?? 0),
                'paye_amount' => (float)($payrollItem->paye_amount ?? 0),
                'wcf_amount' => (float)($payrollItem->wcf_amount ?? 0),
                'deduction_amount' => (float)($payrollItem->deduction_amount ?? 0),
                'other_deductions' => (float)($payrollItem->other_deductions ?? 0),
                'net_salary' => (float)($payrollItem->net_salary ?? 0),
                'status' => $payrollItem->status ?? 'processed',
                'payroll' => [
                    'id' => $payrollItem->payroll->id ?? null,
                    'pay_period' => $payrollItem->payroll->pay_period ?? '',
                    'pay_date' => $payrollItem->payroll->pay_date ? $payrollItem->payroll->pay_date->format('Y-m-d') : null,
                    'status' => $payrollItem->payroll->status ?? 'processed',
                    'processed_by' => ($payrollItem->payroll->processor ?? null) ? $payrollItem->payroll->processor->name : 'N/A',
                    'reviewed_by' => ($payrollItem->payroll->reviewer ?? null) ? $payrollItem->payroll->reviewer->name : null,
                    'approved_by' => ($payrollItem->payroll->approver ?? null) ? $payrollItem->payroll->approver->name : null,
                    'paid_by' => ($payrollItem->payroll->payer ?? null) ? $payrollItem->payroll->payer->name : null,
                ],
                'employee' => [
                    'id' => $payrollItem->employee->id ?? null,
                    'name' => $payrollItem->employee->name ?? 'N/A',
                    'employee_id' => $payrollItem->employee->employee_id ?? $payrollItem->employee->id ?? 'N/A',
                    'department' => ($payrollItem->employee->primaryDepartment ?? null) ? $payrollItem->employee->primaryDepartment->name : 'N/A',
                    'position' => ($payrollItem->employee->employee ?? null) ? $payrollItem->employee->employee->position : 'N/A',
                ]
            ];

            // Generate HTML for the modal content
            $html = view('modules.hr.partials.payslip-details', [
                'payslipData' => $payslipData
            ])->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'payslip' => $payslipData
            ]);

        } catch (\Exception $e) {
            \Log::error('Error loading payslip: ' . $e->getMessage(), [
                'payroll_item_id' => $payrollItem->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load payslip: ' . $e->getMessage()
            ], 500);
        }
    }

    public function calculateEmployeeDeductions(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'basic_salary' => 'required|numeric|min:0',
            'overtime_hours' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'allowance_amount' => 'nullable|numeric|min:0',
            'additional_deductions' => 'nullable|numeric|min:0',
        ]);

        try {
            $basicSalary = $request->basic_salary;
            $overtimeHours = $request->overtime_hours ?? 0;
            $bonusAmount = $request->bonus_amount ?? 0;
            $allowanceAmount = $request->allowance_amount ?? 0;
            $additionalDeductions = $request->additional_deductions ?? 0;

            // Calculate overtime amount
            $overtimeRate = $basicSalary / (22 * 8);
            $overtimeAmount = $overtimeHours * $overtimeRate * 1.5;

            // Calculate gross salary
            $grossSalary = $basicSalary + $overtimeAmount + $bonusAmount + $allowanceAmount;

            // Get fixed deductions (if table exists)
            $fixedDeductions = collect([]);
            $fixedDeductionsTotal = 0;
            try {
                if (Schema::hasTable('employee_salary_deductions')) {
                    $deductionsQuery = DB::table('employee_salary_deductions')
                ->where('employee_id', $request->employee_id)
                        ->where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                        });
                    
                    $fixedDeductions = $deductionsQuery->get();
                    
                    // Calculate total for monthly deductions and applicable one-time deductions
                    foreach ($fixedDeductions as $deduction) {
                        if ($deduction->frequency === 'monthly') {
                            $fixedDeductionsTotal += $deduction->amount;
                        } elseif ($deduction->frequency === 'one-time' && 
                                 $deduction->start_date <= now() && 
                                 (!$deduction->end_date || $deduction->end_date >= now())) {
                            $fixedDeductionsTotal += $deduction->amount;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Table doesn't exist, use empty collection
                \Log::warning('employee_salary_deductions table not found: ' . $e->getMessage());
            }

            // Separate statutory deductions from other fixed deductions - ONLY USE STORED VALUES
            $statutoryDeductions = [
                'PAYE' => 0,
                'NSSF' => 0,
                'NHIF' => 0,
                'HESLB' => 0,
                'WCF' => 0,
                'SDL' => 0
            ];
            $otherFixedDeductionsTotal = 0;
            
            // Check for stored statutory deductions - ONLY USE WHAT'S STORED, NO CALCULATION
            foreach ($fixedDeductions as $deduction) {
                $deductionType = $deduction->deduction_type ?? '';
                $isApplicable = false;
                
                if ($deduction->frequency === 'monthly') {
                    $isApplicable = true;
                } elseif ($deduction->frequency === 'one-time' && 
                         $deduction->start_date <= now() && 
                         (!$deduction->end_date || $deduction->end_date >= now())) {
                    $isApplicable = true;
                }
                
                if ($isApplicable) {
                    if (in_array($deductionType, ['PAYE', 'NSSF', 'NHIF', 'HESLB', 'WCF', 'SDL'])) {
                        // Use ONLY stored statutory deduction amount - NO CALCULATION
                        $statutoryDeductions[$deductionType] = $deduction->amount ?? 0;
                    } else {
                        // Other fixed deductions
                        $otherFixedDeductionsTotal += $deduction->amount ?? 0;
                    }
                }
            }
            
            // Include other fixed deductions (non-statutory) in total additional deductions
            $totalAdditionalDeductions = $additionalDeductions + $otherFixedDeductionsTotal;
            
            // Calculate gross salary
            $grossSalary = $basicSalary + $overtimeAmount + $bonusAmount + $allowanceAmount;
            
            // Use ONLY stored statutory deductions - NO CALCULATION
            $finalPaye = $statutoryDeductions['PAYE'];
            $finalNssfEmployee = $statutoryDeductions['NSSF'];
            $finalNssfEmployer = 0; // Not used when only using stored values
            $finalNhif = $statutoryDeductions['NHIF'];
            $finalHeslb = $statutoryDeductions['HESLB'];
            $finalWcf = $statutoryDeductions['WCF'];
            $finalSdl = $statutoryDeductions['SDL'];
            
            // Calculate totals with ONLY stored values
            $totalStatutoryDeductions = $finalPaye + $finalNssfEmployee + $finalNhif + $finalHeslb + $finalWcf + $finalSdl;
            $totalDeductions = $totalStatutoryDeductions + $otherFixedDeductionsTotal + $additionalDeductions;
            $netSalary = $grossSalary - $totalDeductions;

            // Get HESLB status
            $heslbData = DB::table('employees')
                ->where('user_id', $request->employee_id)
                ->select('has_student_loan', 'heslb_number')
                ->first();

            return response()->json([
                'success' => true,
                'deductions' => [
                    'paye' => $finalPaye,
                    'nssf_employee' => $finalNssfEmployee,
                    'nssf_employer' => $finalNssfEmployer,
                    'nhif' => $finalNhif,
                    'heslb' => $finalHeslb,
                    'wcf' => $finalWcf,
                    'sdl' => $finalSdl,
                ],
                'breakdown' => [
                    'paye' => $finalPaye,
                    'nssf' => ['employee' => $finalNssfEmployee, 'employer' => $finalNssfEmployer],
                    'nhif' => $finalNhif,
                    'heslb' => $finalHeslb,
                    'wcf' => $finalWcf,
                    'sdl' => $finalSdl,
                    'total_deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'total_employer_cost' => $breakdown['employer_contributions'] ?? 0,
                    'gross_salary' => $grossSalary,
                    'fixed_deductions' => $fixedDeductions,
                    'fixed_deductions_total' => $otherFixedDeductionsTotal,
                    'has_student_loan' => $heslbData->has_student_loan ?? 0,
                    'heslb_number' => $heslbData->heslb_number ?? '',
                    'bank_accounts' => $this->getEmployeeBankAccounts($request->employee_id),
                    'uses_stored_statutory' => array_filter($statutoryDeductions, fn($v) => $v > 0) // Track which are stored
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Calculation error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get employee bank accounts
     */
    private function getEmployeeBankAccounts($employeeId)
    {
        try {
            if (Schema::hasTable('bank_accounts')) {
                $accounts = DB::table('bank_accounts')
                    ->where('user_id', $employeeId)
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                return $accounts->map(function($account) {
                    return [
                        'bank_name' => $account->bank_name ?? '',
                        'account_number' => $account->account_number ?? '',
                        'account_name' => $account->account_name ?? '',
                        'branch_name' => $account->branch_name ?? '',
                        'swift_code' => $account->swift_code ?? '',
                        'is_primary' => $account->is_primary ?? false
                    ];
                })->toArray();
            }
        } catch (\Exception $e) {
            \Log::warning('Bank accounts table not found: ' . $e->getMessage());
        }
        
        return [];
    }

    public function exportPayroll(Payroll $payroll)
    {
        try {
            // Check authorization
            $user = Auth::user();
            if (!$user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director', 'HOD', 'Accountant'])) {
                abort(403, 'You are not authorized to export payroll data.');
            }

            // Load payroll with relationships
            $payroll->load([
            'items.employee' => function($query) {
                $query->with([
                    'primaryDepartment',
                    'bankAccounts' => function($q) {
                        $q->where('is_primary', true)->orWhere(function($q2) {
                            $q2->orderBy('is_primary', 'desc');
                        });
                    },
                    'salaryDeductions' => function($q) {
                        $q->where('is_active', true)
                          ->where(function($q2) {
                              $q2->whereNull('end_date')
                                ->orWhere('end_date', '>=', now());
                          });
                    }
                ]);
            },
                'processor',
                'reviewer',
                'approver',
                'payer'
            ]);

            // Prepare filename
            $filename = 'Payroll_' . str_replace('-', '_', $payroll->pay_period) . '_' . date('Y-m-d') . '.csv';

            // Create CSV headers
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            // Use streamDownload for proper CSV export
            return response()->streamDownload(function() use ($payroll) {
                // Create output stream
                $output = fopen('php://output', 'w');

                // Add UTF-8 BOM for Excel compatibility
                fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write CSV headers
            fputcsv($output, [
                'Employee ID',
                'Employee Name',
                'Department',
                'Position',
                'Basic Salary',
                'Overtime Hours',
                'Overtime Amount',
                'Bonus',
                'Allowance',
                'Gross Salary',
                'PAYE',
                'NSSF',
                'NHIF',
                'HESLB',
                'WCF',
                'Other Deductions',
                'Total Deductions',
                'Net Salary',
                'Status'
            ]);

            // Write payroll items
            foreach ($payroll->items as $item) {
                $employee = $item->employee;
                $gross = $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
                $totalDeductions = $item->nssf_amount + $item->nhif_amount + $item->heslb_amount + $item->paye_amount + $item->deduction_amount + $item->wcf_amount;

                fputcsv($output, [
                    $employee->employee_id ?? $employee->id,
                    $employee->name ?? 'N/A',
                    $employee->primaryDepartment->name ?? 'N/A',
                    $employee->employee->position ?? 'N/A',
                    number_format($item->basic_salary, 2, '.', ''),
                    number_format($item->overtime_hours, 2, '.', ''),
                    number_format($item->overtime_amount, 2, '.', ''),
                    number_format($item->bonus_amount, 2, '.', ''),
                    number_format($item->allowance_amount, 2, '.', ''),
                    number_format($gross, 2, '.', ''),
                    number_format($item->paye_amount, 2, '.', ''),
                    number_format($item->nssf_amount, 2, '.', ''),
                    number_format($item->nhif_amount, 2, '.', ''),
                    number_format($item->heslb_amount, 2, '.', ''),
                    number_format($item->wcf_amount, 2, '.', ''),
                    number_format($item->deduction_amount, 2, '.', ''),
                    number_format($totalDeductions, 2, '.', ''),
                    number_format($item->net_salary, 2, '.', ''),
                    ucfirst($item->status ?? 'processed')
                ]);
            }

            // Add summary row
            fputcsv($output, []); // Empty row
            fputcsv($output, ['SUMMARY']);
            fputcsv($output, [
                'Total Employees',
                count($payroll->items),
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ]);

            // Calculate totals
            $totals = [
                'basic' => $payroll->items->sum('basic_salary'),
                'overtime' => $payroll->items->sum('overtime_amount'),
                'bonus' => $payroll->items->sum('bonus_amount'),
                'allowance' => $payroll->items->sum('allowance_amount'),
                'gross' => 0,
                'paye' => $payroll->items->sum('paye_amount'),
                'nssf' => $payroll->items->sum('nssf_amount'),
                'nhif' => $payroll->items->sum('nhif_amount'),
                'heslb' => $payroll->items->sum('heslb_amount'),
                'wcf' => $payroll->items->sum('wcf_amount'),
                'deductions' => $payroll->items->sum('deduction_amount'),
                'net' => $payroll->items->sum('net_salary'),
            ];
            $totals['gross'] = $totals['basic'] + $totals['overtime'] + $totals['bonus'] + $totals['allowance'];
            $totals['total_deductions'] = $totals['paye'] + $totals['nssf'] + $totals['nhif'] + $totals['heslb'] + $totals['wcf'] + $totals['deductions'];

            fputcsv($output, []); // Empty row
            fputcsv($output, ['TOTALS', '', '', '', 
                number_format($totals['basic'], 2, '.', ''),
                '',
                number_format($totals['overtime'], 2, '.', ''),
                number_format($totals['bonus'], 2, '.', ''),
                number_format($totals['allowance'], 2, '.', ''),
                number_format($totals['gross'], 2, '.', ''),
                number_format($totals['paye'], 2, '.', ''),
                number_format($totals['nssf'], 2, '.', ''),
                number_format($totals['nhif'], 2, '.', ''),
                number_format($totals['heslb'], 2, '.', ''),
                number_format($totals['wcf'], 2, '.', ''),
                number_format($totals['deductions'], 2, '.', ''),
                number_format($totals['total_deductions'], 2, '.', ''),
                number_format($totals['net'], 2, '.', ''),
                ''
            ]);

            // Add metadata
            fputcsv($output, []); // Empty row
            fputcsv($output, ['PAYROLL INFORMATION']);
            fputcsv($output, ['Pay Period', $payroll->pay_period]);
            fputcsv($output, ['Pay Date', $payroll->pay_date ? $payroll->pay_date->format('Y-m-d') : 'N/A']);
            fputcsv($output, ['Status', ucfirst($payroll->status)]);
            fputcsv($output, ['Processed By', $payroll->processor->name ?? 'N/A']);
            if ($payroll->approver) {
                fputcsv($output, ['Approved By', $payroll->approver->name]);
            }
            if ($payroll->payer) {
                fputcsv($output, ['Paid By', $payroll->payer->name]);
            }
            fputcsv($output, ['Generated', now()->format('Y-m-d H:i:s')]);

            fclose($output);
            }, $filename, $headers);

        } catch (\Exception $e) {
            \Log::error('Payroll export error: ' . $e->getMessage());
            
            if (request()->wantsJson()) {
        return response()->json([
                    'success' => false,
                    'message' => 'Failed to export payroll: ' . $e->getMessage()
                ], 500);
            }
            
            abort(500, 'Failed to export payroll: ' . $e->getMessage());
        }
    }

    public function generatePayslipPdf(PayrollItem $payrollItem)
    {
        // Check authorization
        if (!Auth::user()->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director', 'HOD', 'Accountant']) 
            && $payrollItem->employee_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this payslip.');
        }

        $payrollItem->load([
            'payroll',
            'employee.primaryDepartment',
            'employee.employee'
        ]);

        $pdfService = app(\App\Services\PayrollPdfService::class);
        return $pdfService->generatePayslip($payrollItem);
    }

    public function generatePayrollReportPdf(Payroll $payroll)
    {
        // Check authorization
        if (!Auth::user()->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'Director', 'HOD', 'Accountant'])) {
            abort(403, 'You are not authorized to export payroll reports.');
        }

        $payroll->load([
            'items.employee.primaryDepartment',
            'items.employee.employee',
            'processor',
            'reviewer',
            'approver',
            'payer'
        ]);

        $pdfService = app(\App\Services\PayrollPdfService::class);
        return $pdfService->generatePayrollReport($payroll);
    }

    /**
     * Map GlAccount category to valid ChartOfAccount enum category
     * 
     * @param string|null $glCategory
     * @param string $type
     * @return string|null
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
                // Default: if category is just "Expense", map to Operating Expense
                return 'Operating Expense';
                
            default:
                return null;
        }
    }

    /**
     * Get all deductions for an employee
     */
    public function getEmployeeDeductions(Request $request, $employeeId)
    {
        try {
            $deductions = EmployeeSalaryDeduction::where('employee_id', $employeeId)
                ->orderBy('start_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'deductions' => $deductions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load deductions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new deduction for an employee
     */
    public function storeDeduction(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'deduction_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'amount' => 'nullable|numeric|min:0',
            'calculation_method' => 'nullable|in:fixed,percentage,statutory',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'frequency' => 'required|in:monthly,one-time',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $employee = User::with('employee')->find($request->employee_id);
            
            if (!$employee || !$employee->employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            $basicSalary = $employee->employee->salary ?? 0;
            $calculationMethod = $request->calculation_method ?? 'fixed';
            $deductionAmount = 0;

            // Calculate amount based on method
            if ($calculationMethod === 'fixed') {
                $deductionAmount = $request->amount ?? 0;
            } elseif ($calculationMethod === 'percentage') {
                $percentage = $request->percentage ?? 0;
                $deductionAmount = ($basicSalary * $percentage) / 100;
            } elseif ($calculationMethod === 'statutory') {
                // Use statutory calculator
                $statutoryCalculator = app(TanzaniaStatutoryCalculator::class);
                $breakdown = $statutoryCalculator->calculateNetSalary($basicSalary, 0, 0, 0, $request->employee_id, 0);
                
                switch ($request->deduction_type) {
                    case 'PAYE':
                        $deductionAmount = $breakdown['paye'] ?? 0;
                        break;
                    case 'NSSF':
                        $deductionAmount = $breakdown['nssf']['employee'] ?? 0;
                        break;
                    case 'NHIF':
                        $deductionAmount = $breakdown['nhif'] ?? 0;
                        break;
                    case 'HESLB':
                        if (!($employee->employee->has_student_loan ?? false)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Employee does not have a student loan. HESLB deduction is only applicable to employees with student loans.'
                            ], 400);
                        }
                        $deductionAmount = $breakdown['heslb'] ?? 0;
                        break;
                    case 'WCF':
                        $deductionAmount = $breakdown['wcf'] ?? 0;
                        break;
                    case 'SDL':
                        $deductionAmount = $breakdown['sdl'] ?? 0;
                        break;
                    default:
                        return response()->json([
                            'success' => false,
                            'message' => 'Statutory calculation not available for this deduction type'
                        ], 400);
                }
            }

            if ($deductionAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Calculated deduction amount is zero or invalid'
                ], 400);
            }

            $deduction = EmployeeSalaryDeduction::create([
                'employee_id' => $request->employee_id,
                'deduction_type' => $request->deduction_type,
                'description' => $request->description ?? ($calculationMethod === 'statutory' ? "{$request->deduction_type} - Statutory Deduction" : null),
                'amount' => round($deductionAmount, 2),
                'frequency' => $request->frequency,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->is_active ?? true,
                'notes' => $request->notes ?? ($calculationMethod === 'statutory' ? "Calculated using statutory formula. Salary: TZS " . number_format($basicSalary, 2) : null),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deduction created successfully',
                'deduction' => $deduction,
                'calculated_amount' => $deductionAmount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create deduction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing deduction
     */
    public function updateDeduction(Request $request, $deductionId)
    {
        $request->validate([
            'deduction_type' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:monthly,one-time',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $deduction = EmployeeSalaryDeduction::findOrFail($deductionId);
            
            $deduction->update([
                'deduction_type' => $request->deduction_type,
                'description' => $request->description,
                'amount' => $request->amount,
                'frequency' => $request->frequency,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->is_active ?? $deduction->is_active,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Deduction updated successfully',
                'deduction' => $deduction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update deduction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a deduction
     */
    public function deleteDeduction($deductionId)
    {
        try {
            $deduction = EmployeeSalaryDeduction::findOrFail($deductionId);
            $deduction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Deduction deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete deduction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create bulk statutory deductions
     */
    public function createBulkDeductions(Request $request)
    {
        $request->validate([
            'deduction_type' => 'required|in:PAYE,NSSF,NHIF,HESLB,WCF,SDL',
            'frequency' => 'required|in:monthly,one-time',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
        ]);

        try {
            $statutoryCalculator = app(TanzaniaStatutoryCalculator::class);
            $createdCount = 0;
            $skippedCount = 0;
            $errors = [];

            foreach ($request->employee_ids as $employeeId) {
                try {
                    $employee = User::with('employee')->find($employeeId);
                    
                    if (!$employee || !$employee->employee) {
                        $skippedCount++;
                        $errors[] = "Employee ID {$employeeId}: Employee record not found";
                        continue;
                    }

                    $basicSalary = $employee->employee->salary ?? 0;
                    if ($basicSalary <= 0) {
                        $skippedCount++;
                        $errors[] = "{$employee->name}: No salary set";
                        continue;
                    }

                    // Calculate deduction amount based on type
                    $grossSalary = $basicSalary; // For bulk, we use basic salary as gross
                    $deductionAmount = 0;

                    switch ($request->deduction_type) {
                        case 'PAYE':
                            $breakdown = $statutoryCalculator->calculateNetSalary($basicSalary, 0, 0, 0, $employeeId, 0);
                            $deductionAmount = $breakdown['paye'] ?? 0;
                            break;
                        case 'NSSF':
                            $breakdown = $statutoryCalculator->calculateNetSalary($basicSalary, 0, 0, 0, $employeeId, 0);
                            $deductionAmount = $breakdown['nssf']['employee'] ?? 0;
                            break;
                        case 'NHIF':
                            $breakdown = $statutoryCalculator->calculateNetSalary($basicSalary, 0, 0, 0, $employeeId, 0);
                            $deductionAmount = $breakdown['nhif'] ?? 0;
                            break;
                        case 'HESLB':
                            // Only for employees with student loans
                            if (!($employee->employee->has_student_loan ?? false)) {
                                $skippedCount++;
                                $errors[] = "{$employee->name}: No student loan";
                                continue 2;
                            }
                            $breakdown = $statutoryCalculator->calculateNetSalary($basicSalary, 0, 0, 0, $employeeId, 0);
                            $deductionAmount = $breakdown['heslb'] ?? 0;
                            break;
                        case 'WCF':
                            $breakdown = $statutoryCalculator->calculateNetSalary($basicSalary, 0, 0, 0, $employeeId, 0);
                            $deductionAmount = $breakdown['wcf'] ?? 0;
                            break;
                        case 'SDL':
                            $breakdown = $statutoryCalculator->calculateNetSalary($basicSalary, 0, 0, 0, $employeeId, 0);
                            $deductionAmount = $breakdown['sdl'] ?? 0;
                            break;
                    }

                    if ($deductionAmount <= 0) {
                        $skippedCount++;
                        $errors[] = "{$employee->name}: Calculated amount is zero";
                        continue;
                    }

                    // Check if deduction already exists
                    $existing = EmployeeSalaryDeduction::where('employee_id', $employeeId)
                        ->where('deduction_type', $request->deduction_type)
                        ->where('is_active', true)
                        ->where(function($q) use ($request) {
                            $q->whereNull('end_date')
                              ->orWhere('end_date', '>=', $request->start_date);
                        })
                        ->first();

                    if ($existing) {
                        $skippedCount++;
                        $errors[] = "{$employee->name}: Active {$request->deduction_type} deduction already exists";
                        continue;
                    }

                    // Create deduction
                    EmployeeSalaryDeduction::create([
                        'employee_id' => $employeeId,
                        'deduction_type' => $request->deduction_type,
                        'description' => $request->description ?? "{$request->deduction_type} - Statutory Deduction",
                        'amount' => $deductionAmount,
                        'frequency' => $request->frequency,
                        'start_date' => $request->start_date,
                        'end_date' => $request->end_date,
                        'is_active' => true,
                        'notes' => $request->notes ?? "Bulk created - Calculated based on salary: TZS " . number_format($basicSalary, 2),
                    ]);

                    $createdCount++;
                } catch (\Exception $e) {
                    $skippedCount++;
                    $errors[] = "Employee ID {$employeeId}: " . $e->getMessage();
                    \Log::error("Bulk deduction creation error for employee {$employeeId}: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Bulk deductions created: {$createdCount} successful, {$skippedCount} skipped",
                'created_count' => $createdCount,
                'skipped_count' => $skippedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bulk deductions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate statutory deduction preview for employees
     */
    public function calculateBulkDeductionPreview(Request $request)
    {
        $request->validate([
            'deduction_type' => 'required|in:PAYE,NSSF,NHIF,HESLB,WCF,SDL',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
        ]);

        try {
            $statutoryCalculator = app(TanzaniaStatutoryCalculator::class);
            $previews = [];

            foreach ($request->employee_ids as $employeeId) {
                $employee = User::with('employee')->find($employeeId);
                
                if (!$employee || !$employee->employee) {
                    continue;
                }

                $basicSalary = $employee->employee->salary ?? 0;
                $deductionAmount = 0;
                $applicable = true;
                $reason = '';

                if ($basicSalary <= 0) {
                    $applicable = false;
                    $reason = 'No salary set';
                } else {
                    $breakdown = $statutoryCalculator->calculateNetSalary($basicSalary, 0, 0, 0, $employeeId, 0);
                    
                    switch ($request->deduction_type) {
                        case 'PAYE':
                            $deductionAmount = $breakdown['paye'] ?? 0;
                            break;
                        case 'NSSF':
                            $deductionAmount = $breakdown['nssf']['employee'] ?? 0;
                            break;
                        case 'NHIF':
                            $deductionAmount = $breakdown['nhif'] ?? 0;
                            break;
                        case 'HESLB':
                            if (!($employee->employee->has_student_loan ?? false)) {
                                $applicable = false;
                                $reason = 'No student loan';
                            } else {
                                $deductionAmount = $breakdown['heslb'] ?? 0;
                            }
                            break;
                        case 'WCF':
                            $deductionAmount = $breakdown['wcf'] ?? 0;
                            break;
                        case 'SDL':
                            $deductionAmount = $breakdown['sdl'] ?? 0;
                            break;
                    }
                }

                $previews[] = [
                    'employee_id' => $employeeId,
                    'employee_name' => $employee->name,
                    'basic_salary' => $basicSalary,
                    'deduction_amount' => $deductionAmount,
                    'applicable' => $applicable,
                    'reason' => $reason
                ];
            }

            return response()->json([
                'success' => true,
                'previews' => $previews
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate preview: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Deduction Management Page
     */
    public function showDeductionManagement(Request $request)
    {
        $user = Auth::user();
        $can_manage_deductions = $user->hasAnyRole(['HR Officer', 'System Admin']);
        
        if (!$can_manage_deductions) {
            abort(403, 'You do not have permission to manage deductions.');
        }

        // Load employees with relationships
        $employees = User::where('is_active', true)
            ->whereHas('employee')
            ->with([
                'primaryDepartment', 
                'employee',
                'bankAccounts' => function($query) {
                    $query->orderBy('is_primary', 'desc')->orderBy('created_at', 'desc');
                },
                'salaryDeductions' => function($query) {
                    $query->where('is_active', true)
                          ->where(function($q) {
                              $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                          })
                          ->orderBy('created_at', 'desc');
                }
            ])
            ->orderBy('name')
            ->get();

        // Get statistics
        $totalEmployees = $employees->count();
        $employeesWithDeductions = $employees->filter(function($emp) {
            return $emp->salaryDeductions && $emp->salaryDeductions->count() > 0;
        })->count();
        
        $totalMonthlyDeductions = $employees->sum(function($emp) {
            return $emp->salaryDeductions->where('frequency', 'monthly')->sum('amount');
        });
        
        $totalOneTimeDeductions = $employees->sum(function($emp) {
            return $emp->salaryDeductions->where('frequency', 'one-time')->sum('amount');
        });

        $deductionTypes = EmployeeSalaryDeduction::where('is_active', true)
            ->select('deduction_type', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('deduction_type')
            ->get();

        return view('modules.hr.deduction-management', compact(
            'employees', 
            'totalEmployees', 
            'employeesWithDeductions', 
            'totalMonthlyDeductions', 
            'totalOneTimeDeductions',
            'deductionTypes'
        ));
    }

    /**
     * Get all employees with their STORED deductions summary (from database only)
     * This method only returns deductions stored in employee_salary_deductions table
     * It does NOT calculate statutory deductions - only shows what's already stored
     */
    public function getDeductionsSummary(Request $request)
    {
        try {
            $employees = User::where('is_active', true)
                ->whereHas('employee')
                ->with([
                    'employee',
                    'primaryDepartment',
                    'salaryDeductions' => function($query) {
                        $query->where('is_active', true)
                              ->where(function($q) {
                                  $q->whereNull('end_date')
                                    ->orWhere('end_date', '>=', now());
                              });
                    }
                ])
                ->get()
                ->map(function($user) {
                    $activeDeductions = $user->salaryDeductions ?? collect([]);
                    $monthlyTotal = $activeDeductions->where('frequency', 'monthly')->sum('amount');
                    $oneTimeTotal = $activeDeductions->where('frequency', 'one-time')->sum('amount');
                    
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'employee_id' => $user->employee->employee_id ?? $user->id,
                        'department' => $user->primaryDepartment->name ?? 'N/A',
                        'basic_salary' => $user->employee->salary ?? 0,
                        'total_monthly_deductions' => $monthlyTotal,
                        'total_one_time_deductions' => $oneTimeTotal,
                        'deductions_count' => $activeDeductions->count(),
                        'deductions' => $activeDeductions
                    ];
                });

            return response()->json([
                'success' => true,
                'employees' => $employees
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load deductions summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Process Payroll Page
     */
    public function showProcessPage(Request $request)
    {
        $user = Auth::user();
        $can_process_payroll = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
        
        if (!$can_process_payroll) {
            abort(403, 'You do not have permission to process payroll.');
        }

        // Get selected month (default to current month)
        $selectedMonth = $request->get('month', \Carbon\Carbon::now()->format('Y-m'));

        // Load employees with relationships
        $employees = User::where('is_active', true)
            ->whereHas('employee')
            ->with([
                'primaryDepartment', 
                'employee',
                'bankAccounts' => function($query) {
                    $query->orderBy('is_primary', 'desc')->orderBy('created_at', 'desc');
                },
                'salaryDeductions' => function($query) {
                    $query->where('is_active', true)
                          ->where(function($q) {
                              $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                          })
                          ->orderBy('created_at', 'desc');
                }
            ])
            ->get();

        // Load monthly overtime, bonus, and allowance records
        $overtimes = \App\Models\EmployeeOvertime::where('month', $selectedMonth)
            ->where('is_active', true)
            ->get()
            ->keyBy('employee_id');

        $bonuses = \App\Models\EmployeeBonus::where('month', $selectedMonth)
            ->where('is_active', true)
            ->get()
            ->keyBy('employee_id');

        $allowances = \App\Models\EmployeeAllowance::where('month', $selectedMonth)
            ->where('is_active', true)
            ->get()
            ->keyBy('employee_id');

        // Fetch Chart of Accounts for double-entry bookkeeping
        $chartAccounts = ChartOfAccount::where('is_active', true)
            ->orderBy('type')
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        $cashBoxes = CashBox::where('is_active', true)->orderBy('name')->get();

        return view('modules.hr.pages.process-payroll', compact(
            'employees', 
            'chartAccounts', 
            'cashBoxes',
            'selectedMonth',
            'overtimes',
            'bonuses',
            'allowances'
        ));
    }

    /**
     * Show Review Payroll Page
     */
    public function showReviewPage(Payroll $payroll)
    {
        $user = Auth::user();
        $can_review_payroll = $user->hasRole('HOD') || $user->hasRole('System Admin');
        
        if (!$can_review_payroll) {
            abort(403, 'You do not have permission to review payroll.');
        }

        if ($payroll->status !== 'processed') {
            return redirect()->route('modules.hr.payroll')
                ->with('error', 'Payroll is not in processed status.');
        }

        $payroll->load(['items.employee.employee', 'processor', 'items.employee.primaryDepartment']);

        return view('modules.hr.pages.review-payroll', compact('payroll'));
    }

    /**
     * Show Approve Payroll Page
     */
    public function showApprovePage(Payroll $payroll)
    {
        $user = Auth::user();
        $can_approve_payroll = $user->hasAnyRole(['CEO', 'Director', 'System Admin']);
        
        if (!$can_approve_payroll) {
            abort(403, 'You do not have permission to approve payroll.');
        }

        if ($payroll->status !== 'reviewed') {
            return redirect()->route('modules.hr.payroll')
                ->with('error', 'Payroll must be reviewed before approval.');
        }

        $payroll->load(['items.employee.employee', 'processor', 'reviewer', 'items.employee.primaryDepartment']);

        return view('modules.hr.pages.approve-payroll', compact('payroll'));
    }

    /**
     * Show Pay Payroll Page
     */
    public function showPayPage(Payroll $payroll)
    {
        $user = Auth::user();
        $can_pay_payroll = $user->hasRole('Accountant') || $user->hasRole('System Admin');
        
        if (!$can_pay_payroll) {
            abort(403, 'You do not have permission to pay payroll.');
        }

        if ($payroll->status !== 'approved') {
            return redirect()->route('modules.hr.payroll')
                ->with('error', 'Payroll must be approved before payment.');
        }

        $payroll->load(['items.employee.employee', 'processor', 'reviewer', 'approver', 'items.employee.primaryDepartment']);

        // Fetch Chart of Accounts for double-entry bookkeeping
        $chartAccounts = ChartOfAccount::where('is_active', true)
            ->orderBy('type')
            ->orderBy('code')
            ->get()
            ->groupBy('type');

        $cashBoxes = CashBox::where('is_active', true)->orderBy('name')->get();

        return view('modules.hr.pages.pay-payroll', compact('payroll', 'chartAccounts', 'cashBoxes'));
    }

    /**
     * Show View Payroll Details Page
     */
    public function showViewPage(Payroll $payroll)
    {
        $user = Auth::user();
        $can_view = $user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'Accountant', 'System Admin', 'Staff']);
        
        if (!$can_view) {
            abort(403, 'You do not have permission to view payroll details.');
        }

        $payroll->load([
            'items.employee.employee',
            'items.employee.primaryDepartment',
            'processor',
            'reviewer',
            'approver',
            'payer'
        ]);

        return view('modules.hr.pages.view-payroll', compact('payroll'));
    }

    /**
     * Show View Payslip Page
     */
    public function showPayslipPage(PayrollItem $payrollItem)
    {
        $user = Auth::user();
        
        // Check if user can view this payslip
        $can_view = $user->hasAnyRole(['HR Officer', 'HOD', 'CEO', 'Accountant', 'System Admin']) 
                    || $payrollItem->employee_id === $user->id;
        
        if (!$can_view) {
            abort(403, 'You do not have permission to view this payslip.');
        }

        $payrollItem->load([
            'payroll',
            'employee.employee',
            'employee.primaryDepartment'
        ]);

        return view('modules.hr.pages.view-payslip', compact('payrollItem'));
    }
}
