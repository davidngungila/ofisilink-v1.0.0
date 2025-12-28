<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        $query = Budget::with(['department', 'items.account']);

        if ($request->has('fiscal_year')) {
            $query->where('fiscal_year', $request->fiscal_year);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $budgets = $query->orderBy('fiscal_year', 'desc')->orderBy('start_date', 'desc')->paginate(20);
        $departments = \App\Models\Department::orderBy('name')->get();
        
        // If Department model doesn't exist, use empty collection
        if (!class_exists(\App\Models\Department::class)) {
            $departments = collect([]);
        }
        $accounts = ChartOfAccount::active()->orderBy('code')->get();

        return view('modules.accounting.budgeting.index', compact('budgets', 'departments', 'accounts'));
    }

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
                'message' => 'Budget created successfully',
                'budget' => $budget->load('items.account')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating budget: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateActuals($id)
    {
        $budget = Budget::findOrFail($id);
        
        foreach ($budget->items as $item) {
            $actual = GeneralLedger::where('account_id', $item->account_id)
                ->whereBetween('transaction_date', [$budget->start_date, $budget->end_date])
                ->selectRaw('SUM(CASE WHEN type = "Debit" THEN amount ELSE -amount END) as balance')
                ->value('balance') ?? 0;

            $item->actual_amount = abs($actual);
            $item->variance = $item->actual_amount - $item->budgeted_amount;
            $item->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Actual amounts updated successfully'
        ]);
    }

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
            'message' => 'Budget approved successfully'
        ]);
    }
}

