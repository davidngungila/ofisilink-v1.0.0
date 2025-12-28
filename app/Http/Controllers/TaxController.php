<?php

namespace App\Http\Controllers;

use App\Models\TaxSetting;
use App\Models\ChartOfAccount;
use App\Models\Invoice;
use App\Models\Bill;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TaxController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Tax Settings Management
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getTaxSettingsData($request);
        }

        $accounts = ChartOfAccount::where('type', 'Liability')->active()->orderBy('code')->get();

        return view('modules.accounting.taxation.index', compact('accounts'));
    }

    /**
     * Get Tax Settings Data (AJAX)
     */
    public function getTaxSettingsData(Request $request)
    {
        try {
            $filterParams = $request->only([
                'tax_type', 'q', 'is_active', 'page', 'per_page'
            ]);

            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            $validated = \Validator::make($filterParams, [
                'tax_type' => 'nullable|string|max:255',
                'q' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ])->validate();

            $query = TaxSetting::with('account');

            if (!empty($validated['tax_type'])) {
                $query->where('tax_type', $validated['tax_type']);
            }

            if (isset($validated['is_active']) && $validated['is_active'] !== null) {
                $query->where('is_active', $validated['is_active']);
            }

            if (!empty($validated['q'])) {
                $searchTerm = $validated['q'];
                $query->where(function($q) use ($searchTerm) {
                    $q->where('tax_name', 'like', "%{$searchTerm}%")
                      ->orWhere('tax_code', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            $allTaxSettings = $query->orderBy('tax_type')->orderBy('tax_name')->get();

            // Calculate summary
            $totalSettings = $allTaxSettings->count();
            $activeSettings = $allTaxSettings->where('is_active', true)->count();
            $inactiveSettings = $allTaxSettings->where('is_active', false)->count();
            $taxTypes = $allTaxSettings->groupBy('tax_type')->map->count();

            // Format tax settings
            $formattedSettings = $allTaxSettings->map(function($tax) {
                return [
                    'id' => $tax->id,
                    'tax_name' => $tax->tax_name ?? 'N/A',
                    'tax_code' => $tax->tax_code ?? '',
                    'tax_type' => $tax->tax_type ?? '',
                    'rate' => round($tax->rate ?? 0, 2),
                    'account_code' => $tax->account ? $tax->account->code : null,
                    'account_name' => $tax->account ? $tax->account->name : null,
                    'description' => $tax->description ?? null,
                    'is_active' => (bool)($tax->is_active ?? true),
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedSettings->count();
            $paginatedSettings = $formattedSettings->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_settings' => $totalSettings,
                    'active_settings' => $activeSettings,
                    'inactive_settings' => $inactiveSettings,
                    'tax_types' => $taxTypes,
                    'count' => $totalEntries
                ],
                'tax_settings' => $paginatedSettings,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading tax settings data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading tax settings: ' . $e->getMessage(),
                'summary' => [
                    'total_settings' => 0,
                    'active_settings' => 0,
                    'inactive_settings' => 0,
                    'tax_types' => [],
                    'count' => 0
                ],
                'tax_settings' => []
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'tax_name' => 'required|string|max:255',
            'tax_code' => 'required|string|unique:tax_settings,tax_code',
            'rate' => 'required|numeric|min:0|max:100',
            'tax_type' => 'required|in:VAT,GST,Withholding Tax,PAYE,Corporate Tax,Other',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
        ]);

        try {
            $taxSetting = TaxSetting::create([
                'tax_name' => $request->tax_name,
                'tax_code' => $request->tax_code,
                'rate' => $request->rate,
                'tax_type' => $request->tax_type,
                'description' => $request->description,
                'account_id' => $request->account_id,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tax setting created successfully',
                'taxSetting' => $taxSetting->load('account')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating tax setting: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update($id, Request $request)
    {
        $taxSetting = TaxSetting::findOrFail($id);

        $request->validate([
            'tax_name' => 'required|string|max:255',
            'tax_code' => 'required|string|unique:tax_settings,tax_code,' . $id,
            'rate' => 'required|numeric|min:0|max:100',
            'account_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
        ]);

        $taxSetting->update([
            'tax_name' => $request->tax_name,
            'tax_code' => $request->tax_code,
            'rate' => $request->rate,
            'tax_type' => $request->tax_type,
            'description' => $request->description,
            'account_id' => $request->account_id,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tax setting updated successfully',
            'taxSetting' => $taxSetting->load('account')
        ]);
    }

    public function destroy($id)
    {
        $taxSetting = TaxSetting::findOrFail($id);
        
        try {
            $taxSetting->delete();
            return response()->json([
                'success' => true,
                'message' => 'Tax setting deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting tax setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tax Reports
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        // Get VAT collected and paid
        $vatTax = TaxSetting::where('tax_type', 'VAT')->first();
        $vatCollected = 0;
        $vatPaid = 0;
        $vatNet = 0;

        if ($vatTax) {
            // Calculate from invoices (VAT collected)
            $invoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->get();
            $vatCollected = $invoices->sum('tax_amount');

            // Calculate from bills (VAT paid)
            $bills = Bill::whereBetween('bill_date', [$startDate, $endDate])->get();
            $vatPaid = $bills->sum('tax_amount');
            
            $vatNet = $vatCollected - $vatPaid;
        }

        // Get GST collected and paid
        $gstTax = TaxSetting::where('tax_type', 'GST')->first();
        $gstCollected = 0;
        $gstPaid = 0;
        $gstNet = 0;

        if ($gstTax) {
            $gstInvoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->get();
            $gstCollected = $gstInvoices->sum('tax_amount');
            $gstBills = Bill::whereBetween('bill_date', [$startDate, $endDate])->get();
            $gstPaid = $gstBills->sum('tax_amount');
            $gstNet = $gstCollected - $gstPaid;
        }

        // Get Withholding Tax
        $whtTax = TaxSetting::where('tax_type', 'Withholding Tax')->first();
        $whtTotal = 0;

        if ($whtTax) {
            // Calculate WHT from bills (typically deducted from vendor payments)
            $whtBills = Bill::whereBetween('bill_date', [$startDate, $endDate])->get();
            $whtTotal = $whtBills->sum('tax_amount');
        }

        // Get PAYE from payroll
        $payeTotal = 0;
        $payeDetails = [];
        
        $payrolls = Payroll::whereBetween('pay_period_start', [$startDate, $endDate])
            ->where('status', 'Posted')
            ->with('items')
            ->get();

        foreach ($payrolls as $payroll) {
            $payrollPaye = $payroll->items->sum('paye_amount');
            $payeTotal += $payrollPaye;
            
            if ($payrollPaye > 0) {
                $payeDetails[] = [
                    'payroll_id' => $payroll->id,
                    'pay_period' => $payroll->pay_period_start->format('M Y'),
                    'paye_amount' => $payrollPaye,
                    'employee_count' => $payroll->items->count(),
                ];
            }
        }

        // Get Corporate Tax (if applicable)
        $corporateTax = TaxSetting::where('tax_type', 'Corporate Tax')->first();
        $corporateTaxAmount = 0;

        // Summary statistics
        $totalTaxCollected = $vatCollected + $gstCollected;
        $totalTaxPaid = $vatPaid + $gstPaid + $whtTotal;
        $totalTaxOwed = $totalTaxCollected - $totalTaxPaid + $payeTotal;

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getTaxReportsData($request);
        }

        return view('modules.accounting.taxation.reports');
    }

    /**
     * Get Tax Reports Data (AJAX)
     */
    public function getTaxReportsData(Request $request)
    {
        try {
            $filterParams = $request->only([
                'start_date', 'end_date'
            ]);

            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            $validated = \Validator::make($filterParams, [
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ])->validate();

            $startDate = $validated['start_date'] ?? now()->startOfYear()->format('Y-m-d');
            $endDate = $validated['end_date'] ?? now()->format('Y-m-d');

            // Get VAT collected and paid
            $vatTax = TaxSetting::where('tax_type', 'VAT')->first();
            $vatCollected = 0;
            $vatPaid = 0;
            $vatNet = 0;

            if ($vatTax) {
                $invoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->get();
                $vatCollected = round($invoices->sum('tax_amount'), 2);
                $bills = Bill::whereBetween('bill_date', [$startDate, $endDate])->get();
                $vatPaid = round($bills->sum('tax_amount'), 2);
                $vatNet = round($vatCollected - $vatPaid, 2);
            }

            // Get GST collected and paid
            $gstTax = TaxSetting::where('tax_type', 'GST')->first();
            $gstCollected = 0;
            $gstPaid = 0;
            $gstNet = 0;

            if ($gstTax) {
                $gstInvoices = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->get();
                $gstCollected = round($gstInvoices->sum('tax_amount'), 2);
                $gstBills = Bill::whereBetween('bill_date', [$startDate, $endDate])->get();
                $gstPaid = round($gstBills->sum('tax_amount'), 2);
                $gstNet = round($gstCollected - $gstPaid, 2);
            }

            // Get Withholding Tax
            $whtTax = TaxSetting::where('tax_type', 'Withholding Tax')->first();
            $whtTotal = 0;

            if ($whtTax) {
                $whtBills = Bill::whereBetween('bill_date', [$startDate, $endDate])->get();
                $whtTotal = round($whtBills->sum('tax_amount'), 2);
            }

            // Get PAYE from payroll
            $payeTotal = 0;
            $payeDetails = [];
            
            $payrolls = Payroll::whereBetween('pay_period_start', [$startDate, $endDate])
                ->where('status', 'Posted')
                ->with('items')
                ->get();

            foreach ($payrolls as $payroll) {
                $payrollPaye = $payroll->items->sum('paye_amount');
                $payeTotal += $payrollPaye;
                
                if ($payrollPaye > 0) {
                    $payeDetails[] = [
                        'payroll_id' => $payroll->id,
                        'pay_period' => $payroll->pay_period_start->format('M Y'),
                        'paye_amount' => round($payrollPaye, 2),
                        'employee_count' => $payroll->items->count(),
                    ];
                }
            }

            $payeTotal = round($payeTotal, 2);

            // Summary statistics
            $totalTaxCollected = round($vatCollected + $gstCollected, 2);
            $totalTaxPaid = round($vatPaid + $gstPaid + $whtTotal, 2);
            $totalTaxOwed = round($totalTaxCollected - $totalTaxPaid + $payeTotal, 2);

            return response()->json([
                'success' => true,
                'summary' => [
                    'vat_collected' => $vatCollected,
                    'vat_paid' => $vatPaid,
                    'vat_net' => $vatNet,
                    'gst_collected' => $gstCollected,
                    'gst_paid' => $gstPaid,
                    'gst_net' => $gstNet,
                    'wht_total' => $whtTotal,
                    'paye_total' => $payeTotal,
                    'total_tax_collected' => $totalTaxCollected,
                    'total_tax_paid' => $totalTaxPaid,
                    'total_tax_owed' => $totalTaxOwed,
                ],
                'paye_details' => $payeDetails,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading tax reports data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading tax reports: ' . $e->getMessage(),
                'summary' => [
                    'vat_collected' => 0,
                    'vat_paid' => 0,
                    'vat_net' => 0,
                    'gst_collected' => 0,
                    'gst_paid' => 0,
                    'gst_net' => 0,
                    'wht_total' => 0,
                    'paye_total' => 0,
                    'total_tax_collected' => 0,
                    'total_tax_paid' => 0,
                    'total_tax_owed' => 0,
                ],
                'paye_details' => []
            ], 500);
        }
    }

    /**
     * Export Tax Report as PDF
     */
    public function exportReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        // Reuse the same logic from reports method
        $vatCollected = Invoice::whereBetween('invoice_date', [$startDate, $endDate])->sum('tax_amount');
        $vatPaid = Bill::whereBetween('bill_date', [$startDate, $endDate])->sum('tax_amount');
        
        $payrolls = Payroll::whereBetween('pay_period_start', [$startDate, $endDate])
            ->where('status', 'Posted')
            ->with('items')
            ->get();
        
        $payeTotal = $payrolls->sum(function($payroll) {
            return $payroll->items->sum('paye_amount');
        });

        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'vatCollected' => $vatCollected,
            'vatPaid' => $vatPaid,
            'vatNet' => $vatCollected - $vatPaid,
            'payeTotal' => $payeTotal,
        ];

        $pdf = Pdf::loadView('modules.accounting.taxation.pdf.report', $data);
        return $pdf->download('tax-report-' . $startDate . '-to-' . $endDate . '.pdf');
    }

    /**
     * PAYE Management
     */
    public function payeManagement(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');

        // Get PAYE statistics
        $payrolls = Payroll::whereBetween('pay_period_start', [$startDate, $endDate])
            ->where('status', 'Posted')
            ->with(['items.employee'])
            ->get();

        $payeSummary = [
            'total_paye' => 0,
            'total_employees' => 0,
            'total_gross_salary' => 0,
            'total_net_salary' => 0,
            'payroll_count' => $payrolls->count(),
        ];

        $payeBreakdown = [];
        
        foreach ($payrolls as $payroll) {
            $payrollPaye = $payroll->items->sum('paye_amount');
            $payrollGross = $payroll->items->sum(function($item) {
                return $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
            });
            $payrollNet = $payroll->items->sum('net_salary');
            
            $payeSummary['total_paye'] += $payrollPaye;
            $payeSummary['total_gross_salary'] += $payrollGross;
            $payeSummary['total_net_salary'] += $payrollNet;
            $payeSummary['total_employees'] += $payroll->items->count();

            if ($payrollPaye > 0) {
                $payeBreakdown[] = [
                    'payroll' => $payroll,
                    'paye_amount' => $payrollPaye,
                    'gross_salary' => $payrollGross,
                    'net_salary' => $payrollNet,
                    'employee_count' => $payroll->items->count(),
                ];
            }
        }

        // Get employee-level PAYE details
        $employeePayeDetails = PayrollItem::whereHas('payroll', function($query) use ($startDate, $endDate) {
            $query->whereBetween('pay_period_start', [$startDate, $endDate])
                  ->where('status', 'Posted');
        })
        ->where('paye_amount', '>', 0)
        ->with(['employee', 'payroll'])
        ->orderBy('paye_amount', 'desc')
        ->get()
        ->groupBy('employee_id')
        ->map(function($items) {
            $employee = $items->first()->employee;
            return [
                'employee' => $employee,
                'total_paye' => $items->sum('paye_amount'),
                'total_gross' => $items->sum(function($item) {
                    return $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
                }),
                'payroll_count' => $items->count(),
                'items' => $items,
            ];
        })
        ->values();

        // Get PAYE tax brackets from service
        $statutoryCalculator = app(\App\Services\TanzaniaStatutoryCalculator::class);
        $payeBrackets = $statutoryCalculator->getStatutoryRates()['paye_brackets'];

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getPayeManagementData($request);
        }

        return view('modules.accounting.taxation.paye-management');
    }

    /**
     * Get PAYE Management Data (AJAX)
     */
    public function getPayeManagementData(Request $request)
    {
        try {
            $filterParams = $request->only([
                'start_date', 'end_date'
            ]);

            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            $validated = \Validator::make($filterParams, [
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ])->validate();

            $startDate = $validated['start_date'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $validated['end_date'] ?? now()->endOfMonth()->format('Y-m-d');

            // Get PAYE statistics
            $payrolls = Payroll::whereBetween('pay_period_start', [$startDate, $endDate])
                ->where('status', 'Posted')
                ->with(['items.employee'])
                ->get();

            $payeSummary = [
                'total_paye' => 0,
                'total_employees' => 0,
                'total_gross_salary' => 0,
                'total_net_salary' => 0,
                'payroll_count' => $payrolls->count(),
            ];

            $payeBreakdown = [];
            
            foreach ($payrolls as $payroll) {
                $payrollPaye = round($payroll->items->sum('paye_amount'), 2);
                $payrollGross = round($payroll->items->sum(function($item) {
                    return $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
                }), 2);
                $payrollNet = round($payroll->items->sum('net_salary'), 2);
                
                $payeSummary['total_paye'] += $payrollPaye;
                $payeSummary['total_gross_salary'] += $payrollGross;
                $payeSummary['total_net_salary'] += $payrollNet;
                $payeSummary['total_employees'] += $payroll->items->count();

                if ($payrollPaye > 0) {
                    $payeBreakdown[] = [
                        'payroll_id' => $payroll->id,
                        'pay_period' => $payroll->pay_period_start->format('M Y'),
                        'pay_period_start' => $payroll->pay_period_start->format('Y-m-d'),
                        'paye_amount' => $payrollPaye,
                        'gross_salary' => $payrollGross,
                        'net_salary' => $payrollNet,
                        'employee_count' => $payroll->items->count(),
                    ];
                }
            }

            $payeSummary['total_paye'] = round($payeSummary['total_paye'], 2);
            $payeSummary['total_gross_salary'] = round($payeSummary['total_gross_salary'], 2);
            $payeSummary['total_net_salary'] = round($payeSummary['total_net_salary'], 2);

            // Get employee-level PAYE details
            $employeePayeDetails = PayrollItem::whereHas('payroll', function($query) use ($startDate, $endDate) {
                $query->whereBetween('pay_period_start', [$startDate, $endDate])
                      ->where('status', 'Posted');
            })
            ->where('paye_amount', '>', 0)
            ->with(['employee', 'payroll'])
            ->orderBy('paye_amount', 'desc')
            ->get()
            ->groupBy('employee_id')
            ->map(function($items) {
                $employee = $items->first()->employee;
                return [
                    'employee_id' => $employee ? $employee->id : null,
                    'employee_name' => $employee ? $employee->name : 'N/A',
                    'employee_email' => $employee ? $employee->email : null,
                    'total_paye' => round($items->sum('paye_amount'), 2),
                    'total_gross' => round($items->sum(function($item) {
                        return $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
                    }), 2),
                    'payroll_count' => $items->count(),
                    'items' => $items->map(function($item) {
                        return [
                            'payroll_id' => $item->payroll_id,
                            'pay_period' => $item->payroll ? $item->payroll->pay_period_start->format('M Y') : '',
                            'paye_amount' => round($item->paye_amount, 2),
                            'gross_salary' => round($item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount, 2),
                            'net_salary' => round($item->net_salary, 2),
                        ];
                    }),
                ];
            })
            ->values();

            // Get PAYE tax brackets from service
            $statutoryCalculator = app(\App\Services\TanzaniaStatutoryCalculator::class);
            $payeBrackets = $statutoryCalculator->getStatutoryRates()['paye_brackets'];

            return response()->json([
                'success' => true,
                'summary' => $payeSummary,
                'paye_breakdown' => $payeBreakdown,
                'employee_paye_details' => $employeePayeDetails,
                'paye_brackets' => $payeBrackets,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading PAYE management data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading PAYE management data: ' . $e->getMessage(),
                'summary' => [
                    'total_paye' => 0,
                    'total_employees' => 0,
                    'total_gross_salary' => 0,
                    'total_net_salary' => 0,
                    'payroll_count' => 0,
                ],
                'paye_breakdown' => [],
                'employee_paye_details' => [],
                'paye_brackets' => []
            ], 500);
        }
    }

    /**
     * Get PAYE calculation details for an employee
     */
    public function getPayeCalculation(Request $request)
    {
        $request->validate([
            'gross_salary' => 'required|numeric|min:0',
        ]);

        $statutoryCalculator = app(\App\Services\TanzaniaStatutoryCalculator::class);
        $breakdown = $statutoryCalculator->calculateNetSalary(
            $request->gross_salary,
            $request->overtime_amount ?? 0,
            $request->bonus_amount ?? 0,
            $request->allowance_amount ?? 0,
            $request->employee_id ?? null,
            $request->additional_deductions ?? 0
        );

        return response()->json([
            'success' => true,
            'breakdown' => $breakdown,
        ]);
    }
}

