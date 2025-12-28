<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class BudgetingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Budgets Management
     */
    public function budgets(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO', 'Finance Manager'])) {
            abort(403);
        }

        $query = Budget::with(['department', 'items.account', 'creator']);

        // Filters
        if ($request->has('fiscal_year') && !empty($request->fiscal_year)) {
            $query->where('fiscal_year', $request->fiscal_year);
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('budget_type') && !empty($request->budget_type)) {
            $query->where('budget_type', $request->budget_type);
        }

        if ($request->has('department_id') && !empty($request->department_id)) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('budget_name', 'like', "%{$search}%")
                  ->orWhere('fiscal_year', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('start_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('end_date', '<=', $request->date_to);
        }

        // Check for exports
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportBudgetsPdf($query->get(), $request);
        }

        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportBudgetsExcel($query->get(), $request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getBudgetsData($request);
        }

        $departments = \App\Models\Department::orderBy('name')->get();
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        
        // Get fiscal years
        $fiscalYears = Budget::select('fiscal_year')
            ->distinct()
            ->orderBy('fiscal_year', 'desc')
            ->pluck('fiscal_year');

        return view('modules.accounting.budgeting.budgets', compact('departments', 'accounts', 'fiscalYears'));
    }

    /**
     * Get Budgets Data (AJAX)
     */
    public function getBudgetsData(Request $request)
    {
        try {
            // Only get filter parameters
            $filterParams = $request->only([
                'fiscal_year', 'status', 'budget_type', 'department_id', 'search', 
                'date_from', 'date_to', 'page', 'per_page'
            ]);

            // Convert empty strings to null
            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            // Validate only filter parameters
            $validated = \Validator::make($filterParams, [
                'fiscal_year' => 'nullable|string|max:255',
                'status' => 'nullable|string|max:255',
                'budget_type' => 'nullable|string|max:255',
                'department_id' => 'nullable|exists:departments,id',
                'search' => 'nullable|string|max:255',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ])->validate();

            $query = Budget::with(['department', 'items.account', 'creator']);

            // Apply filters
            if (!empty($validated['fiscal_year'])) {
                $query->where('fiscal_year', $validated['fiscal_year']);
            }

            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (!empty($validated['budget_type'])) {
                $query->where('budget_type', $validated['budget_type']);
            }

            if (!empty($validated['department_id'])) {
                $query->where('department_id', $validated['department_id']);
            }

            if (!empty($validated['search'])) {
                $search = $validated['search'];
                $query->where(function($q) use ($search) {
                    $q->where('budget_name', 'like', "%{$search}%")
                      ->orWhere('fiscal_year', 'like', "%{$search}%");
                });
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('start_date', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('end_date', '<=', $validated['date_to']);
            }

            // Get all budgets for summary calculation
            $allBudgets = $query->orderBy('fiscal_year', 'desc')
                ->orderBy('start_date', 'desc')
                ->get();

            // Calculate summary
            $totalBudgets = $allBudgets->count();
            $approvedBudgets = $allBudgets->where('status', 'Approved')->count();
            $totalBudgeted = round($allBudgets->sum('total_budgeted'), 2);
            $totalActual = round($allBudgets->sum('total_actual'), 2);
            $totalVariance = round($totalBudgeted - $totalActual, 2);

            // Format budgets
            $formattedBudgets = $allBudgets->map(function($budget) {
                $variance = ($budget->total_budgeted ?? 0) - ($budget->total_actual ?? 0);
                $variancePercent = ($budget->total_budgeted ?? 0) > 0 
                    ? round(($variance / $budget->total_budgeted) * 100, 2) 
                    : 0;

                return [
                    'id' => $budget->id,
                    'budget_name' => $budget->budget_name ?? 'N/A',
                    'fiscal_year' => $budget->fiscal_year ?? '',
                    'budget_type' => $budget->budget_type ?? '',
                    'status' => $budget->status ?? '',
                    'start_date' => $budget->start_date ? $budget->start_date->format('Y-m-d') : '',
                    'start_date_display' => $budget->start_date ? $budget->start_date->format('d M Y') : '',
                    'end_date' => $budget->end_date ? $budget->end_date->format('Y-m-d') : '',
                    'end_date_display' => $budget->end_date ? $budget->end_date->format('d M Y') : '',
                    'department_name' => $budget->department ? $budget->department->name : 'N/A',
                    'total_budgeted' => round($budget->total_budgeted ?? 0, 2),
                    'total_actual' => round($budget->total_actual ?? 0, 2),
                    'total_variance' => round($variance, 2),
                    'variance_percent' => $variancePercent,
                    'items_count' => $budget->items ? $budget->items->count() : 0,
                    'created_by' => $budget->creator ? $budget->creator->name : 'N/A',
                    'created_at' => $budget->created_at ? $budget->created_at->format('Y-m-d H:i:s') : '',
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedBudgets->count();
            $paginatedBudgets = $formattedBudgets->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_budgets' => $totalBudgets,
                    'approved_budgets' => $approvedBudgets,
                    'total_budgeted' => $totalBudgeted,
                    'total_actual' => $totalActual,
                    'total_variance' => $totalVariance,
                    'count' => $totalEntries
                ],
                'budgets' => $paginatedBudgets,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading budgets data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading budgets: ' . $e->getMessage(),
                'summary' => [
                    'total_budgets' => 0,
                    'approved_budgets' => 0,
                    'total_budgeted' => 0,
                    'total_actual' => 0,
                    'total_variance' => 0,
                    'count' => 0
                ],
                'budgets' => []
            ], 500);
        }
    }

    /**
     * Budget Reports - Actual vs Budgeted Analysis
     */
    public function budgetReports(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO', 'Finance Manager'])) {
            abort(403);
        }

        $budgetId = $request->budget_id ?? null;
        $fiscalYear = $request->fiscal_year ?? date('Y');
        $period = $request->period ?? 'monthly'; // monthly, quarterly, yearly

        $query = Budget::with(['items.account', 'department']);

        if ($budgetId) {
            $query->where('id', $budgetId);
        } else {
            $query->where('fiscal_year', $fiscalYear)
                  ->where('status', 'Approved');
        }

        $budgets = $query->get();

        $reports = [];
        foreach ($budgets as $budget) {
            $this->updateBudgetActuals($budget);
            
            $report = [
                'budget' => $budget,
                'total_budgeted' => $budget->total_budgeted,
                'total_actual' => $budget->total_actual,
                'total_variance' => $budget->total_variance,
                'variance_percentage' => $budget->total_budgeted > 0 
                    ? ($budget->total_variance / $budget->total_budgeted) * 100 
                    : 0,
                'items' => $budget->items->map(function($item) {
                    return [
                        'account' => $item->account->name ?? 'N/A',
                        'account_code' => $item->account->code ?? '',
                        'budgeted' => $item->budgeted_amount,
                        'actual' => $item->actual_amount,
                        'variance' => $item->variance,
                        'variance_percentage' => $item->budgeted_amount > 0 
                            ? ($item->variance / $item->budgeted_amount) * 100 
                            : 0,
                    ];
                }),
            ];

            // Period-based breakdown
            if ($period === 'monthly') {
                $report['monthly_breakdown'] = $this->getMonthlyBreakdown($budget);
            } elseif ($period === 'quarterly') {
                $report['quarterly_breakdown'] = $this->getQuarterlyBreakdown($budget);
            }

            $reports[] = $report;
        }

        // Summary statistics
        $summary = [
            'total_budgets' => $budgets->count(),
            'total_budgeted' => $budgets->sum('total_budgeted'),
            'total_actual' => $budgets->sum('total_actual'),
            'total_variance' => $budgets->sum('total_variance'),
            'favorable_variance' => $budgets->sum(function($b) {
                return $b->total_variance < 0 ? abs($b->total_variance) : 0;
            }),
            'unfavorable_variance' => $budgets->sum(function($b) {
                return $b->total_variance > 0 ? $b->total_variance : 0;
            }),
        ];

        // Check for exports
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportBudgetReportPdf($reports, $summary, $request);
        }

        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportBudgetReportExcel($reports, $summary, $request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getBudgetReportsData($request);
        }

        $allBudgets = Budget::where('status', 'Approved')->orderBy('fiscal_year', 'desc')->get();
        $fiscalYears = Budget::select('fiscal_year')->distinct()->orderBy('fiscal_year', 'desc')->pluck('fiscal_year');

        return view('modules.accounting.budgeting.budget-reports', compact('allBudgets', 'fiscalYears'));
    }

    /**
     * Get Budget Reports Data (AJAX)
     */
    public function getBudgetReportsData(Request $request)
    {
        try {
            $filterParams = $request->only([
                'budget_id', 'fiscal_year', 'period'
            ]);

            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            $validated = \Validator::make($filterParams, [
                'budget_id' => 'nullable|exists:budgets,id',
                'fiscal_year' => 'nullable|string|max:255',
                'period' => 'nullable|in:monthly,quarterly,yearly',
            ])->validate();

            $budgetId = $validated['budget_id'] ?? null;
            $fiscalYear = $validated['fiscal_year'] ?? date('Y');
            $period = $validated['period'] ?? 'monthly';

            $query = Budget::with(['items.account', 'department']);

            if ($budgetId) {
                $query->where('id', $budgetId);
            } else {
                $query->where('fiscal_year', $fiscalYear)
                      ->where('status', 'Approved');
            }

            $budgets = $query->get();

            $reports = [];
            foreach ($budgets as $budget) {
                $this->updateBudgetActuals($budget);
                
                $report = [
                    'budget_id' => $budget->id,
                    'budget_name' => $budget->budget_name,
                    'fiscal_year' => $budget->fiscal_year,
                    'department_name' => $budget->department ? $budget->department->name : 'N/A',
                    'total_budgeted' => round($budget->total_budgeted, 2),
                    'total_actual' => round($budget->total_actual, 2),
                    'total_variance' => round($budget->total_variance, 2),
                    'variance_percentage' => $budget->total_budgeted > 0 
                        ? round(($budget->total_variance / $budget->total_budgeted) * 100, 2)
                        : 0,
                    'items' => $budget->items->map(function($item) {
                        return [
                            'account' => $item->account->name ?? 'N/A',
                            'account_code' => $item->account->code ?? '',
                            'budgeted' => round($item->budgeted_amount, 2),
                            'actual' => round($item->actual_amount, 2),
                            'variance' => round($item->variance, 2),
                            'variance_percentage' => $item->budgeted_amount > 0 
                                ? round(($item->variance / $item->budgeted_amount) * 100, 2)
                                : 0,
                        ];
                    }),
                ];

                // Period-based breakdown
                if ($period === 'monthly') {
                    $report['monthly_breakdown'] = $this->getMonthlyBreakdown($budget);
                } elseif ($period === 'quarterly') {
                    $report['quarterly_breakdown'] = $this->getQuarterlyBreakdown($budget);
                }

                $reports[] = $report;
            }

            // Summary statistics
            $summary = [
                'total_budgets' => $budgets->count(),
                'total_budgeted' => round($budgets->sum('total_budgeted'), 2),
                'total_actual' => round($budgets->sum('total_actual'), 2),
                'total_variance' => round($budgets->sum('total_variance'), 2),
                'favorable_variance' => round($budgets->sum(function($b) {
                    return $b->total_variance < 0 ? abs($b->total_variance) : 0;
                }), 2),
                'unfavorable_variance' => round($budgets->sum(function($b) {
                    return $b->total_variance > 0 ? $b->total_variance : 0;
                }), 2),
            ];

            if ($summary['total_budgeted'] > 0) {
                $summary['variance_percentage'] = round(($summary['total_variance'] / $summary['total_budgeted']) * 100, 2);
            } else {
                $summary['variance_percentage'] = 0;
            }

            return response()->json([
                'success' => true,
                'summary' => $summary,
                'reports' => $reports,
                'filters' => [
                    'budget_id' => $budgetId,
                    'fiscal_year' => $fiscalYear,
                    'period' => $period,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading budget reports data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading budget reports: ' . $e->getMessage(),
                'summary' => [
                    'total_budgets' => 0,
                    'total_budgeted' => 0,
                    'total_actual' => 0,
                    'total_variance' => 0,
                    'favorable_variance' => 0,
                    'unfavorable_variance' => 0,
                    'variance_percentage' => 0,
                ],
                'reports' => []
            ], 500);
        }
    }

    /**
     * Forecasting - Financial Projections
     */
    public function forecasting(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO', 'Finance Manager'])) {
            abort(403);
        }

        $forecastType = $request->type ?? 'revenue'; // revenue, expense, cash_flow
        $period = $request->period ?? 12; // months to forecast
        $method = $request->method ?? 'trend'; // trend, moving_average, exponential_smoothing

        // Get historical data
        $startDate = Carbon::now()->subMonths($period * 2);
        $endDate = Carbon::now();

        $historicalData = $this->getHistoricalData($startDate, $endDate, $forecastType);

        // Generate forecast
        $forecast = $this->generateForecast($historicalData, $period, $method);

        // Calculate confidence intervals
        $confidenceIntervals = $this->calculateConfidenceIntervals($forecast, $historicalData);

        // Get account categories for breakdown
        $accounts = ChartOfAccount::active()
            ->whereIn('type', $forecastType === 'revenue' ? ['Income'] : ['Expense'])
            ->orderBy('code')
            ->get();

        $accountForecasts = [];
        foreach ($accounts as $account) {
            $accountData = $this->getAccountHistoricalData($account->id, $startDate, $endDate);
            if (count($accountData) > 0) {
                $accountForecasts[] = [
                    'account' => $account,
                    'forecast' => $this->generateForecast($accountData, $period, $method),
                    'historical' => $accountData,
                ];
            }
        }

        // Check for exports
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportForecastPdf($forecast, $accountForecasts, $request);
        }

        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportForecastExcel($forecast, $accountForecasts, $request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getForecastingData($request);
        }

        return view('modules.accounting.budgeting.forecasting');
    }

    /**
     * Get Forecasting Data (AJAX)
     */
    public function getForecastingData(Request $request)
    {
        try {
            $filterParams = $request->only([
                'type', 'period', 'method'
            ]);

            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            $validated = \Validator::make($filterParams, [
                'type' => 'nullable|in:revenue,expense,cash_flow',
                'period' => 'nullable|integer|min:1|max:60',
                'method' => 'nullable|in:trend,moving_average,exponential_smoothing',
            ])->validate();

            $forecastType = $validated['type'] ?? 'revenue';
            $period = (int)($validated['period'] ?? 12);
            $method = $validated['method'] ?? 'trend';

            // Get historical data
            $startDate = Carbon::now()->subMonths($period * 2);
            $endDate = Carbon::now();

            $historicalData = $this->getHistoricalData($startDate, $endDate, $forecastType);

            // Generate forecast
            $forecast = $this->generateForecast($historicalData, $period, $method);

            // Calculate confidence intervals
            $confidenceIntervals = $this->calculateConfidenceIntervals($forecast, $historicalData);

            // Get account categories for breakdown
            $accounts = ChartOfAccount::active()
                ->whereIn('type', $forecastType === 'revenue' ? ['Income'] : ['Expense'])
                ->orderBy('code')
                ->get();

            $accountForecasts = [];
            foreach ($accounts as $account) {
                $accountData = $this->getAccountHistoricalData($account->id, $startDate, $endDate);
                if (count($accountData) > 0) {
                    $accountForecasts[] = [
                        'account_id' => $account->id,
                        'account_code' => $account->code,
                        'account_name' => $account->name,
                        'forecast' => $this->generateForecast($accountData, $period, $method),
                        'historical' => $accountData,
                    ];
                }
            }

            // Calculate summary
            $totalForecasted = round(collect($forecast)->sum('amount'), 2);
            $averageMonthly = round(collect($forecast)->avg('amount'), 2);
            $first = $forecast[0]['amount'] ?? 0;
            $last = end($forecast)['amount'] ?? 0;
            $growthRate = $first > 0 ? round((($last - $first) / $first) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_forecasted' => $totalForecasted,
                    'average_monthly' => $averageMonthly,
                    'growth_rate' => $growthRate,
                    'period' => $period,
                ],
                'forecast' => $forecast,
                'confidence_intervals' => $confidenceIntervals,
                'account_forecasts' => $accountForecasts,
                'historical_data' => $historicalData,
                'filters' => [
                    'type' => $forecastType,
                    'period' => $period,
                    'method' => $method,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading forecasting data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading forecasting data: ' . $e->getMessage(),
                'summary' => [
                    'total_forecasted' => 0,
                    'average_monthly' => 0,
                    'growth_rate' => 0,
                    'period' => 0,
                ],
                'forecast' => [],
                'confidence_intervals' => [],
                'account_forecasts' => [],
                'historical_data' => [],
            ], 500);
        }
    }

    /**
     * Store Budget
     */
    public function store(Request $request)
    {
        $request->validate([
            'budget_name' => 'required|string|max:255',
            'budget_type' => 'required|in:Annual,Quarterly,Monthly,Custom',
            'fiscal_year' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'items' => 'required|array|min:1',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.budgeted_amount' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $budget = Budget::create([
                'budget_name' => $request->budget_name,
                'budget_type' => $request->budget_type,
                'fiscal_year' => $request->fiscal_year,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'department_id' => $request->department_id,
                'status' => 'Draft',
                'notes' => $request->notes,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'account_id' => $item['account_id'],
                    'budgeted_amount' => $item['budgeted_amount'],
                    'actual_amount' => 0,
                    'variance' => 0,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget created successfully! Budget: ' . $budget->budget_name,
                'budget' => $budget->load('items.account')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating budget: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating budget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Budget
     */
    public function update(Request $request, $id)
    {
        $budget = Budget::findOrFail($id);

        if ($budget->status === 'Approved') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot edit an approved budget'
            ], 400);
        }

        $request->validate([
            'budget_name' => 'required|string|max:255',
            'budget_type' => 'required|in:Annual,Quarterly,Monthly,Custom',
            'fiscal_year' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $budget->update([
                'budget_name' => $request->budget_name,
                'budget_type' => $request->budget_type,
                'fiscal_year' => $request->fiscal_year,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'department_id' => $request->department_id,
                'notes' => $request->notes,
            ]);

            // Delete existing items
            $budget->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                BudgetItem::create([
                    'budget_id' => $budget->id,
                    'account_id' => $item['account_id'],
                    'budgeted_amount' => $item['budgeted_amount'],
                    'actual_amount' => $item['actual_amount'] ?? 0,
                    'variance' => ($item['actual_amount'] ?? 0) - $item['budgeted_amount'],
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget updated successfully!',
                'budget' => $budget->load('items.account')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating budget: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating budget: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Budget
     */
    public function show($id)
    {
        $budget = Budget::with(['items.account', 'department', 'creator'])->findOrFail($id);
        $this->updateBudgetActuals($budget);
        
        return response()->json([
            'success' => true,
            'budget' => $budget
        ]);
    }

    /**
     * Update Actuals
     */
    public function updateActuals($id)
    {
        $budget = Budget::findOrFail($id);
        $this->updateBudgetActuals($budget);

        return response()->json([
            'success' => true,
            'message' => 'Actual amounts updated successfully',
            'budget' => $budget->load('items.account')
        ]);
    }

    /**
     * Approve Budget
     */
    public function approve($id)
    {
        $budget = Budget::findOrFail($id);
        
        $budget->update([
            'status' => 'Approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Budget approved successfully!'
        ]);
    }

    /**
     * Delete Budget
     */
    public function destroy($id)
    {
        $budget = Budget::findOrFail($id);

        if ($budget->status === 'Approved') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete an approved budget'
            ], 400);
        }

        $budget->items()->delete();
        $budget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Budget deleted successfully'
        ]);
    }

    /**
     * Helper: Update Budget Actuals
     */
    private function updateBudgetActuals($budget)
    {
        foreach ($budget->items as $item) {
            $actual = GeneralLedger::where('account_id', $item->account_id)
                ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date])
                ->selectRaw('SUM(CASE WHEN type = "Debit" THEN amount ELSE -amount END) as balance')
                ->value('balance') ?? 0;

            $item->actual_amount = abs($actual);
            $item->variance = $item->actual_amount - $item->budgeted_amount;
            $item->save();
        }
    }

    /**
     * Helper: Get Monthly Breakdown
     */
    private function getMonthlyBreakdown($budget)
    {
        $breakdown = [];
        $start = Carbon::parse($budget->start_date);
        $end = Carbon::parse($budget->end_date);

        while ($start <= $end) {
            $monthStart = $start->copy()->startOfMonth();
            $monthEnd = $start->copy()->endOfMonth();
            
            if ($monthEnd > $end) {
                $monthEnd = $end;
            }

            $monthData = [
                'month' => $start->format('M Y'),
                'budgeted' => 0,
                'actual' => 0,
            ];

            foreach ($budget->items as $item) {
                // Calculate monthly budget (proportional)
                $totalDays = Carbon::parse($budget->start_date)->diffInDays(Carbon::parse($budget->end_date)) + 1;
                $monthDays = $monthStart->diffInDays($monthEnd) + 1;
                $monthBudget = ($item->budgeted_amount / $totalDays) * $monthDays;
                $monthData['budgeted'] += $monthBudget;

                // Get actual for the month
                $monthActual = GeneralLedger::where('account_id', $item->account_id)
                    ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                    ->selectRaw('SUM(CASE WHEN type = "Debit" THEN amount ELSE -amount END) as balance')
                    ->value('balance') ?? 0;
                $monthData['actual'] += abs($monthActual);
            }

            $monthData['variance'] = $monthData['actual'] - $monthData['budgeted'];
            $breakdown[] = $monthData;

            $start->addMonth();
        }

        return $breakdown;
    }

    /**
     * Helper: Get Quarterly Breakdown
     */
    private function getQuarterlyBreakdown($budget)
    {
        $breakdown = [];
        $start = Carbon::parse($budget->start_date);
        $end = Carbon::parse($budget->end_date);

        $quarters = [];
        while ($start <= $end) {
            $quarter = ceil($start->month / 3);
            $quarterKey = $start->year . '-Q' . $quarter;

            if (!isset($quarters[$quarterKey])) {
                $quarters[$quarterKey] = [
                    'quarter' => $quarterKey,
                    'budgeted' => 0,
                    'actual' => 0,
                ];
            }

            $start->addMonth();
        }

        foreach ($quarters as $key => &$quarterData) {
            [$year, $q] = explode('-Q', $key);
            $quarterStart = Carbon::create($year, ($q - 1) * 3 + 1, 1);
            $quarterEnd = $quarterStart->copy()->endOfQuarter();

            if ($quarterStart < Carbon::parse($budget->start_date)) {
                $quarterStart = Carbon::parse($budget->start_date);
            }
            if ($quarterEnd > Carbon::parse($budget->end_date)) {
                $quarterEnd = Carbon::parse($budget->end_date);
            }

            foreach ($budget->items as $item) {
                $totalDays = Carbon::parse($budget->start_date)->diffInDays(Carbon::parse($budget->end_date)) + 1;
                $quarterDays = $quarterStart->diffInDays($quarterEnd) + 1;
                $quarterBudget = ($item->budgeted_amount / $totalDays) * $quarterDays;
                $quarterData['budgeted'] += $quarterBudget;

                $quarterActual = GeneralLedger::where('account_id', $item->account_id)
                    ->whereBetween('transaction_date', [$quarterStart, $quarterEnd])
                    ->selectRaw('SUM(CASE WHEN type = "Debit" THEN amount ELSE -amount END) as balance')
                    ->value('balance') ?? 0;
                $quarterData['actual'] += abs($quarterActual);
            }

            $quarterData['variance'] = $quarterData['actual'] - $quarterData['budgeted'];
        }

        return array_values($quarters);
    }

    /**
     * Helper: Get Historical Data
     */
    private function getHistoricalData($startDate, $endDate, $type)
    {
        $accountType = $type === 'revenue' ? 'Income' : 'Expense';
        
        // Use the correct table name 'general_ledger' (singular) not 'general_ledgers' (plural)
        $data = GeneralLedger::join('chart_of_accounts', 'general_ledger.account_id', '=', 'chart_of_accounts.id')
            ->where('chart_of_accounts.type', $accountType)
            ->whereBetween('general_ledger.transaction_date', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(general_ledger.transaction_date, "%Y-%m") as month')
            ->selectRaw('SUM(CASE WHEN general_ledger.type = "Debit" THEN general_ledger.amount ELSE -general_ledger.amount END) as amount')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->month,
                    'amount' => abs($item->amount),
                ];
            });

        return $data->toArray();
    }

    /**
     * Helper: Get Account Historical Data
     */
    private function getAccountHistoricalData($accountId, $startDate, $endDate)
    {
        $data = GeneralLedger::where('account_id', $accountId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('DATE_FORMAT(transaction_date, "%Y-%m") as month')
            ->selectRaw('SUM(CASE WHEN type = "Debit" THEN amount ELSE -amount END) as amount')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->month,
                    'amount' => abs($item->amount),
                ];
            });

        return $data->toArray();
    }

    /**
     * Helper: Generate Forecast
     */
    private function generateForecast($historicalData, $period, $method)
    {
        if (count($historicalData) < 2) {
            return [];
        }

        $forecast = [];
        $lastDate = Carbon::parse(end($historicalData)['date'] . '-01');
        $amounts = array_column($historicalData, 'amount');

        for ($i = 1; $i <= $period; $i++) {
            $forecastDate = $lastDate->copy()->addMonths($i);
            
            if ($method === 'trend') {
                $value = $this->calculateTrend($amounts);
            } elseif ($method === 'moving_average') {
                $value = $this->calculateMovingAverage($amounts, 3);
            } else { // exponential_smoothing
                $value = $this->calculateExponentialSmoothing($amounts);
            }

            $forecast[] = [
                'date' => $forecastDate->format('Y-m'),
                'amount' => max(0, $value),
            ];
        }

        return $forecast;
    }

    /**
     * Helper: Calculate Trend
     */
    private function calculateTrend($values)
    {
        $n = count($values);
        if ($n < 2) return end($values);

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($values as $index => $value) {
            $x = $index + 1;
            $y = $value;
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        return $slope * ($n + 1) + $intercept;
    }

    /**
     * Helper: Calculate Moving Average
     */
    private function calculateMovingAverage($values, $period = 3)
    {
        $n = count($values);
        if ($n < $period) return end($values);

        $recent = array_slice($values, -$period);
        return array_sum($recent) / count($recent);
    }

    /**
     * Helper: Calculate Exponential Smoothing
     */
    private function calculateExponentialSmoothing($values, $alpha = 0.3)
    {
        $n = count($values);
        if ($n < 2) return end($values);

        $forecast = $values[0];
        foreach (array_slice($values, 1) as $value) {
            $forecast = $alpha * $value + (1 - $alpha) * $forecast;
        }

        return $forecast;
    }

    /**
     * Helper: Calculate Confidence Intervals
     */
    private function calculateConfidenceIntervals($forecast, $historicalData)
    {
        if (count($historicalData) < 2) {
            return [];
        }

        $amounts = array_column($historicalData, 'amount');
        $mean = array_sum($amounts) / count($amounts);
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $amounts)) / count($amounts);
        $stdDev = sqrt($variance);

        return array_map(function($item) use ($stdDev) {
            return [
                'date' => $item['date'],
                'lower' => max(0, $item['amount'] - 1.96 * $stdDev),
                'upper' => $item['amount'] + 1.96 * $stdDev,
            ];
        }, $forecast);
    }

    /**
     * Export Budgets PDF
     */
    private function exportBudgetsPdf($budgets, $request)
    {
        try {
            $data = [
                'budgets' => $budgets,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
                'filters' => $request->only(['fiscal_year', 'status', 'budget_type']),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.budgets', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Budgets_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Budgets PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Budgets Excel
     */
    private function exportBudgetsExcel($budgets, $request)
    {
        try {
            $filename = 'Budgets_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($budgets) {
                $file = fopen('php://output', 'w');
                
                fputcsv($file, ['Budget Name', 'Type', 'Fiscal Year', 'Start Date', 'End Date', 'Status', 'Total Budgeted', 'Total Actual', 'Variance']);
                
                foreach ($budgets as $budget) {
                    fputcsv($file, [
                        $budget->budget_name,
                        $budget->budget_type,
                        $budget->fiscal_year,
                        $budget->start_date->format('Y-m-d'),
                        $budget->end_date->format('Y-m-d'),
                        $budget->status,
                        number_format($budget->total_budgeted, 2),
                        number_format($budget->total_actual, 2),
                        number_format($budget->total_variance, 2),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Budgets Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export Budget Report PDF
     */
    private function exportBudgetReportPdf($reports, $summary, $request)
    {
        try {
            $data = [
                'reports' => $reports,
                'summary' => $summary,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.budget-report', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Budget_Report_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Budget Report PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Budget Report Excel
     */
    private function exportBudgetReportExcel($reports, $summary, $request)
    {
        try {
            $filename = 'Budget_Report_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($reports, $summary) {
                $file = fopen('php://output', 'w');
                
                fputcsv($file, ['Budget Report - Actual vs Budgeted Analysis']);
                fputcsv($file, ['Generated: ' . now()->format('d M Y H:i:s')]);
                fputcsv($file, []);
                fputcsv($file, ['Summary']);
                fputcsv($file, ['Total Budgeted', 'Total Actual', 'Total Variance', 'Favorable', 'Unfavorable']);
                fputcsv($file, [
                    number_format($summary['total_budgeted'], 2),
                    number_format($summary['total_actual'], 2),
                    number_format($summary['total_variance'], 2),
                    number_format($summary['favorable_variance'], 2),
                    number_format($summary['unfavorable_variance'], 2),
                ]);
                fputcsv($file, []);
                
                foreach ($reports as $report) {
                    fputcsv($file, ['Budget: ' . $report['budget']->budget_name]);
                    fputcsv($file, ['Account', 'Budgeted', 'Actual', 'Variance', 'Variance %']);
                    foreach ($report['items'] as $item) {
                        fputcsv($file, [
                            $item['account'],
                            number_format($item['budgeted'], 2),
                            number_format($item['actual'], 2),
                            number_format($item['variance'], 2),
                            number_format($item['variance_percentage'], 2) . '%',
                        ]);
                    }
                    fputcsv($file, []);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Budget Report Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export Forecast PDF
     */
    private function exportForecastPdf($forecast, $accountForecasts, $request)
    {
        try {
            $data = [
                'forecast' => $forecast,
                'accountForecasts' => $accountForecasts,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.forecast', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Forecast_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Forecast PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Forecast Excel
     */
    private function exportForecastExcel($forecast, $accountForecasts, $request)
    {
        try {
            $filename = 'Forecast_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($forecast, $accountForecasts) {
                $file = fopen('php://output', 'w');
                
                fputcsv($file, ['Financial Forecast']);
                fputcsv($file, ['Generated: ' . now()->format('d M Y H:i:s')]);
                fputcsv($file, []);
                fputcsv($file, ['Period', 'Forecasted Amount']);
                foreach ($forecast as $item) {
                    fputcsv($file, [
                        $item['date'],
                        number_format($item['amount'], 2),
                    ]);
                }
                fputcsv($file, []);

                foreach ($accountForecasts as $accountForecast) {
                    fputcsv($file, ['Account: ' . $accountForecast['account']->name]);
                    fputcsv($file, ['Period', 'Forecasted Amount']);
                    foreach ($accountForecast['forecast'] as $item) {
                        fputcsv($file, [
                            $item['date'],
                            number_format($item['amount'], 2),
                        ]);
                    }
                    fputcsv($file, []);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Forecast Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }
}


