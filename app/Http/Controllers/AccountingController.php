<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\GeneralLedger;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Bill;
use App\Models\Invoice;
use App\Models\CreditMemo;
use App\Models\Budget;
use App\Models\TaxSetting;
use App\Models\AccountingAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ActivityLogService;

class AccountingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Main Accounting Dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403, 'Unauthorized access to accounting module');
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getAccountingDashboardData($request);
        }

        return view('modules.accounting.index');
    }

    /**
     * Get Accounting Dashboard Data (AJAX)
     */
    public function getAccountingDashboardData(Request $request)
    {
        try {
            // Get quick stats
            $stats = [
                'total_accounts' => ChartOfAccount::active()->count(),
                'pending_journals' => JournalEntry::where('status', 'Draft')->count(),
                'total_outstanding_bills' => round(Bill::whereIn('status', ['Pending', 'Partially Paid', 'Draft'])->sum('balance'), 2),
                'total_receivables' => round(Invoice::whereIn('status', ['Sent', 'Partially Paid', 'Overdue', 'Approved'])->sum('balance'), 2),
                'active_budgets' => Budget::where('status', 'Active')->count(),
                'total_bills' => Bill::count(),
                'total_invoices' => Invoice::count(),
                'posted_journals' => JournalEntry::where('status', 'Posted')->count(),
                'pending_invoices' => Invoice::where('status', 'Pending for Approval')->count(),
                'pending_bills' => Bill::where('status', 'Draft')->count(),
            ];

            // Recent activities
            $recentJournals = JournalEntry::with('creator')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function($journal) {
                    return [
                        'id' => $journal->id,
                        'entry_no' => $journal->entry_no,
                        'entry_date' => $journal->entry_date->format('d M Y'),
                        'status' => $journal->status,
                        'created_by' => $journal->creator->name ?? 'N/A',
                        'total_debit' => round($journal->total_debit, 2),
                        'total_credit' => round($journal->total_credit, 2),
                    ];
                });

            $recentBills = Bill::with('vendor')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function($bill) {
                    return [
                        'id' => $bill->id,
                        'bill_no' => $bill->bill_no,
                        'vendor_name' => $bill->vendor->name ?? 'N/A',
                        'total_amount' => round($bill->total_amount, 2),
                        'balance' => round($bill->balance, 2),
                        'status' => $bill->status,
                        'bill_date' => $bill->bill_date->format('d M Y'),
                    ];
                });

            $recentInvoices = Invoice::with('customer')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_no' => $invoice->invoice_no,
                        'customer_name' => $invoice->customer->name ?? 'N/A',
                        'total_amount' => round($invoice->total_amount, 2),
                        'balance' => round($invoice->balance, 2),
                        'status' => $invoice->status,
                        'invoice_date' => $invoice->invoice_date->format('d M Y'),
                    ];
                });

            // Monthly trends (last 6 months)
            $monthlyTrends = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $startOfMonth = $date->copy()->startOfMonth();
                $endOfMonth = $date->copy()->endOfMonth();
                
                $monthlyTrends[] = [
                    'month' => $date->format('M Y'),
                    'bills' => round(Bill::whereBetween('bill_date', [$startOfMonth, $endOfMonth])->sum('total_amount'), 2),
                    'invoices' => round(Invoice::whereBetween('invoice_date', [$startOfMonth, $endOfMonth])->sum('total_amount'), 2),
                    'payments_received' => round(DB::table('invoice_payments')->whereBetween('payment_date', [$startOfMonth, $endOfMonth])->sum('amount'), 2),
                    'payments_made' => round(DB::table('bill_payments')->whereBetween('payment_date', [$startOfMonth, $endOfMonth])->sum('amount'), 2),
                ];
            }

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'recent_journals' => $recentJournals,
                'recent_bills' => $recentBills,
                'recent_invoices' => $recentInvoices,
                'monthly_trends' => $monthlyTrends,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading accounting dashboard data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading dashboard data: ' . $e->getMessage(),
                'stats' => [
                    'total_accounts' => 0,
                    'pending_journals' => 0,
                    'total_outstanding_bills' => 0,
                    'total_receivables' => 0,
                    'active_budgets' => 0,
                ],
                'recent_journals' => [],
                'recent_bills' => [],
                'recent_invoices' => [],
                'monthly_trends' => [],
            ], 500);
        }
    }

    /**
     * Chart of Accounts Management - Advanced
     */
    public function chartOfAccounts(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $query = ChartOfAccount::with(['parent', 'children', 'creator', 'updater']);

        // Advanced filtering
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status);
        }

        if ($request->has('parent_id')) {
            if ($request->parent_id === 'null') {
                $query->whereNull('parent_id');
            } elseif ($request->parent_id) {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Balance range filter
        if ($request->has('balance_min') || $request->has('balance_max')) {
            $query->with(['ledgerEntries' => function($q) {
                $q->selectRaw('account_id, SUM(CASE WHEN type = "Debit" THEN amount ELSE -amount END) as balance')
                  ->groupBy('account_id');
            }]);
        }

        // View mode: tree or list
        $viewMode = $request->get('view', 'list'); // 'list' or 'tree'

        if ($viewMode === 'tree') {
            // Get root accounts (no parent) for tree view
            $rootAccounts = ChartOfAccount::whereNull('parent_id')
                ->with(['children' => function($q) {
                    $q->orderBy('code');
                }])
                ->orderBy('type')
                ->orderBy('code')
                ->get();
            
            $accounts = $query->whereNull('parent_id')->orderBy('type')->orderBy('code')->get();
        } else {
            $accounts = $query->orderBy('type')->orderBy('code')->get();
        }
        
        // Group by type for display
        $groupedAccounts = $accounts->groupBy('type');
        
        // Get all accounts for parent dropdown
        $allAccounts = ChartOfAccount::orderBy('code')->get();

        // Get statistics
        $stats = [
            'total' => ChartOfAccount::count(),
            'active' => ChartOfAccount::active()->count(),
            'by_type' => ChartOfAccount::selectRaw('type, COUNT(*) as count')->groupBy('type')->pluck('count', 'type'),
        ];

        // Check if export requested
        if ($request->has('export')) {
            if ($request->export === 'pdf') {
                return $this->exportChartOfAccountsPdf($accounts, $groupedAccounts);
            } elseif ($request->export === 'excel') {
                return $this->exportChartOfAccountsExcel($accounts);
            } elseif ($request->export === 'csv') {
                return $this->exportChartOfAccountsCsv($accounts);
            }
        }

        return view('modules.accounting.chart-of-accounts', compact(
            'accounts', 'groupedAccounts', 'allAccounts', 'stats', 'viewMode'
        ));
    }

    /**
     * Get account transaction history
     */
    public function getAccountTransactions(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        
        $query = GeneralLedger::where('account_id', $id)
            ->with(['creator'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->has('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $transactions = $query->paginate($request->get('per_page', 50));

        // Calculate running balance
        $openingBalance = $account->opening_balance ?? 0;
        $runningBalance = $openingBalance;
        
        $transactions->getCollection()->transform(function($transaction) use (&$runningBalance) {
            if ($transaction->type === 'Debit') {
                $runningBalance += $transaction->amount;
            } else {
                $runningBalance -= $transaction->amount;
            }
            $transaction->running_balance = $runningBalance;
            return $transaction;
        });

        return response()->json([
            'success' => true,
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'opening_balance' => $openingBalance,
                'current_balance' => $account->current_balance,
            ],
            'transactions' => $transactions,
        ]);
    }

    /**
     * Get account balance trend
     */
    public function getAccountBalanceTrend(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        
        $startDate = $request->get('start_date', now()->subMonths(6)->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));

        $transactions = GeneralLedger::where('account_id', $id)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $openingBalance = $account->opening_balance ?? 0;
        $balance = $openingBalance;
        $trend = [];

        foreach ($transactions as $transaction) {
            if ($transaction->type === 'Debit') {
                $balance += $transaction->amount;
            } else {
                $balance -= $transaction->amount;
            }
            
            $trend[] = [
                'date' => $transaction->transaction_date->format('Y-m-d'),
                'balance' => $balance,
            ];
        }

        return response()->json([
            'success' => true,
            'trend' => $trend,
            'opening_balance' => $openingBalance,
            'current_balance' => $balance,
        ]);
    }

    /**
     * Bulk operations on accounts
     */
    public function bulkOperations(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'account_ids' => 'required|array',
            'account_ids.*' => 'exists:chart_of_accounts,id',
        ]);

        $accounts = ChartOfAccount::whereIn('id', $request->account_ids)->get();
        $count = 0;

        try {
            DB::beginTransaction();

            foreach ($accounts as $account) {
                switch ($request->action) {
                    case 'activate':
                        if (!$account->is_active) {
                            $account->update(['is_active' => true, 'updated_by' => Auth::id()]);
                            $count++;
                        }
                        break;
                    case 'deactivate':
                        if ($account->is_active && !$account->is_system) {
                            $account->update(['is_active' => false, 'updated_by' => Auth::id()]);
                            $count++;
                        }
                        break;
                    case 'delete':
                        if ($account->canBeDeleted()) {
                            $oldData = $account->toArray();
                            $account->delete();
                            $this->logAudit('Chart of Accounts', 'Delete', 'ChartOfAccount', $account->id, $oldData, null);
                            $count++;
                        }
                        break;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully {$request->action}d {$count} account(s)",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk operation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk operation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export Chart of Accounts to Excel
     */
    private function exportChartOfAccountsExcel($accounts)
    {
        // This would require Laravel Excel package
        // For now, return CSV
        return $this->exportChartOfAccountsCsv($accounts);
    }

    /**
     * Export Chart of Accounts to CSV
     */
    private function exportChartOfAccountsCsv($accounts)
    {
        $filename = 'chart_of_accounts_' . now()->format('Ymd_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($accounts) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, ['Code', 'Name', 'Type', 'Category', 'Parent', 'Opening Balance', 'Current Balance', 'Status', 'Created']);
            
            // Data
            foreach ($accounts as $account) {
                fputcsv($file, [
                    $account->code,
                    $account->name,
                    $account->type,
                    $account->category ?? 'N/A',
                    $account->parent ? $account->parent->code : 'N/A',
                    $account->opening_balance,
                    $account->current_balance,
                    $account->is_active ? 'Active' : 'Inactive',
                    $account->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Chart of Accounts PDF
     */
    private function exportChartOfAccountsPdf($accounts, $groupedAccounts)
    {
        try {
            $data = [
                'accounts' => $accounts,
                'groupedAccounts' => $groupedAccounts,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.chart-of-accounts', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Chart_of_Accounts_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Chart of Accounts PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Show Chart of Account
     */
    public function showAccount($id)
    {
        $account = ChartOfAccount::with(['parent', 'children', 'ledgerEntries' => function($q) {
            $q->latest()->limit(10);
        }])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'account' => [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'category' => $account->category,
                'parent_id' => $account->parent_id,
                'parent' => $account->parent ? ['code' => $account->parent->code, 'name' => $account->parent->name] : null,
                'description' => $account->description,
                'opening_balance' => $account->opening_balance,
                'opening_balance_date' => $account->opening_balance_date,
                'current_balance' => $account->current_balance,
                'is_active' => $account->is_active,
                'sort_order' => $account->sort_order,
                'created_at' => $account->created_at,
                'children' => $account->children->map(function($child) {
                    return ['id' => $child->id, 'code' => $child->code, 'name' => $child->name, 'is_active' => $child->is_active];
                })
            ]
        ]);
    }

    /**
     * Store Chart of Account
     */
    public function storeAccount(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:Asset,Liability,Equity,Income,Expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric',
        ]);

        try {
            $account = ChartOfAccount::create([
                'code' => $request->code,
                'name' => $request->name,
                'type' => $request->type,
                'category' => $request->category,
                'parent_id' => $request->parent_id,
                'description' => $request->description,
                'opening_balance' => $request->opening_balance ?? 0,
                'opening_balance_date' => $request->opening_balance_date,
                'sort_order' => $request->sort_order ?? 0,
                'is_active' => $request->has('is_active') ? true : false,
                'created_by' => Auth::id(),
            ]);

            $this->logAudit('Chart of Accounts', 'Create', 'ChartOfAccount', $account->id, null, $account->toArray());

            // Log activity
            ActivityLogService::logCreated($account, "Created chart of account: {$account->code} - {$account->name}", [
                'code' => $account->code,
                'name' => $account->name,
                'type' => $account->type,
                'category' => $account->category,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'account' => $account->load('parent', 'children')
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Chart of Account
     */
    public function updateAccount(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Asset,Liability,Equity,Income,Expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id|not_in:' . $id,
            'opening_balance' => 'nullable|numeric',
        ]);

        try {
            $oldData = $account->toArray();
            
            $account->update([
                'name' => $request->name,
                'type' => $request->type,
                'category' => $request->category,
                'parent_id' => $request->parent_id,
                'description' => $request->description,
                'opening_balance' => $request->opening_balance ?? $account->opening_balance,
                'opening_balance_date' => $request->opening_balance_date,
                'sort_order' => $request->sort_order ?? $account->sort_order,
                'is_active' => $request->has('is_active') ? true : false,
                'updated_by' => Auth::id(),
            ]);

            $this->logAudit('Chart of Accounts', 'Update', 'ChartOfAccount', $account->id, $oldData, $account->toArray());

            // Log activity
            $oldValues = array_intersect_key($oldData, $account->getChanges());
            $newValues = $account->getChanges();
            ActivityLogService::logUpdated($account, $oldValues, $newValues, "Updated chart of account: {$account->code} - {$account->name}", [
                'code' => $account->code,
                'name' => $account->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account updated successfully',
                'account' => $account->load('parent', 'children')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete Chart of Account
     */
    public function deleteAccount($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        
        if (!$account->canBeDeleted()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete account. It has transactions, child accounts, or is a system account.'
            ], 400);
        }

        try {
            $oldData = $account->toArray();
            $account->delete();
            
            $this->logAudit('Chart of Accounts', 'Delete', 'ChartOfAccount', $id, $oldData, null);

            // Log activity
            ActivityLogService::logDeleted($account, "Deleted chart of account: {$oldData['code']} - {$oldData['name']}", [
                'code' => $oldData['code'] ?? null,
                'name' => $oldData['name'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Journal Entries
     */
    public function journalEntries(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $query = JournalEntry::with(['creator', 'lines.account']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }

        $entries = $query->orderBy('entry_date', 'desc')->orderBy('id', 'desc')->paginate(20);
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        
        // Fetch GL Accounts and Cash Boxes for reference details
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportJournalEntriesPdf($query->get());
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getJournalEntriesData($request);
        }

        return view('modules.accounting.journal-entries', compact('entries', 'accounts', 'glAccounts', 'cashBoxes'));
    }

    /**
     * Get Journal Entries Data (AJAX)
     */
    public function getJournalEntriesData(Request $request)
    {
        try {
            $validated = $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'status' => 'nullable|string|in:Draft,Posted,Reversed',
                'source' => 'nullable|string',
                'q' => 'nullable|string',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:5|max:100'
            ]);

            $query = JournalEntry::with(['creator', 'lines.account']);

            if (!empty($validated['date_from'])) {
                $query->whereDate('entry_date', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('entry_date', '<=', $validated['date_to']);
            }

            if (!empty($validated['status'])) {
                $query->where('status', $validated['status']);
            }

            if (!empty($validated['source'])) {
                $query->where('source', $validated['source']);
            }

            if (!empty($validated['q'])) {
                $search = $validated['q'];
                $query->where(function($q) use ($search) {
                    $q->where('entry_no', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('reference_no', 'like', "%{$search}%")
                      ->orWhere('source', 'like', "%{$search}%");
                });
            }

            // Get all entries for summary calculation
            $allEntries = $query->get();
            
            // Calculate totals
            $totalDebit = round($allEntries->sum('total_debits'), 2);
            $totalCredit = round($allEntries->sum('total_credits'), 2);
            $balance = round($totalDebit - $totalCredit, 2);

            // Format entries
            $formattedEntries = $allEntries->map(function($entry) {
                return [
                    'id' => $entry->id,
                    'entry_no' => $entry->entry_no,
                    'date' => $entry->entry_date->format('Y-m-d'),
                    'date_display' => $entry->entry_date->format('d M Y'),
                    'description' => $entry->description,
                    'reference' => $entry->reference_no ?? '-',
                    'source' => $entry->source,
                    'status' => $entry->status,
                    'debit' => (float)$entry->total_debits,
                    'credit' => (float)$entry->total_credits,
                    'created_by' => $entry->creator ? $entry->creator->name : 'System',
                    'posted_at' => $entry->posted_at ? $entry->posted_at->format('d M Y, H:i') : null,
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedEntries->count();
            $paginatedEntries = $formattedEntries->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'balance' => $balance,
                    'count' => $totalEntries
                ],
                'entries' => $paginatedEntries,
                'page' => $page,
                'per_page' => $perPage
            ]);
        } catch (\Exception $e) {
            \Log::error('Journal Entries data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading journal entries data: ' . $e->getMessage(),
                'summary' => [
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'balance' => 0,
                    'count' => 0
                ],
                'entries' => []
            ], 500);
        }
    }

    /**
     * Export Journal Entries PDF
     */
    private function exportJournalEntriesPdf($entries)
    {
        try {
            $data = [
                'entries' => $entries,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
                'filters' => request()->only(['status', 'date_from', 'date_to']),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.journal-entries', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Journal_Entries_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Journal Entries PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Store Journal Entry
     */
    public function storeJournalEntry(Request $request)
    {
        $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.type' => 'required|in:Debit,Credit',
            'lines.*.amount' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $entry = JournalEntry::create([
                'entry_no' => JournalEntry::generateEntryNo(),
                'entry_date' => $request->entry_date,
                'reference_no' => $request->reference_no,
                'description' => $request->description,
                'source' => $request->source ?? 'Manual',
                'source_ref' => $request->source_ref,
                'notes' => $request->notes,
                'status' => 'Draft',
                'created_by' => Auth::id(),
            ]);

            foreach ($request->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'type' => $line['type'],
                    'amount' => $line['amount'],
                    'description' => $line['description'] ?? null,
                    'reference' => $line['reference'] ?? null,
                ]);
            }

            // Check if balanced
            if (!$entry->isBalanced()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Journal entry is not balanced. Total debits must equal total credits.'
                ], 400);
            }

            // Auto-post if requested
            if ($request->auto_post) {
                $this->postJournalEntry($entry->id);
            }

            DB::commit();

            $this->logAudit('Journal Entries', 'Create', 'JournalEntry', $entry->id, null, $entry->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Journal entry created successfully',
                'entry' => $entry->load('lines.account')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating journal entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating journal entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show Journal Entry
     */
    public function showJournalEntry($id)
    {
        $entry = JournalEntry::with(['creator', 'poster', 'lines.account'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'entry' => [
                'id' => $entry->id,
                'entry_no' => $entry->entry_no,
                'entry_date' => $entry->entry_date,
                'reference_no' => $entry->reference_no,
                'description' => $entry->description,
                'source' => $entry->source,
                'source_ref' => $entry->source_ref,
                'notes' => $entry->notes,
                'status' => $entry->status,
                'total_debits' => $entry->total_debits,
                'total_credits' => $entry->total_credits,
                'created_at' => $entry->created_at,
                'posted_at' => $entry->posted_at,
                'creator' => $entry->creator ? ['name' => $entry->creator->name] : null,
                'poster' => $entry->poster ? ['name' => $entry->poster->name] : null,
                'lines' => $entry->lines->map(function($line) {
                    return [
                        'id' => $line->id,
                        'account_id' => $line->account_id,
                        'account' => $line->account ? ['code' => $line->account->code, 'name' => $line->account->name] : null,
                        'type' => $line->type,
                        'amount' => $line->amount,
                        'description' => $line->description
                    ];
                })
            ]
        ]);
    }

    /**
     * Update Journal Entry
     */
    public function updateJournalEntry(Request $request, $id)
    {
        $entry = JournalEntry::findOrFail($id);
        
        if ($entry->status !== 'Draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft entries can be edited'
            ], 400);
        }

        $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.type' => 'required|in:Debit,Credit',
            'lines.*.amount' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $entry->update([
                'entry_date' => $request->entry_date,
                'reference_no' => $request->reference_no,
                'description' => $request->description,
                'source' => $request->source ?? $entry->source,
                'source_ref' => $request->source_ref,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Delete existing lines
            $entry->lines()->delete();

            // Create new lines
            foreach ($request->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $line['account_id'],
                    'type' => $line['type'],
                    'amount' => $line['amount'],
                    'description' => $line['description'] ?? null,
                    'reference' => $line['reference'] ?? null,
                ]);
            }

            // Check if balanced
            if (!$entry->fresh()->isBalanced()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Journal entry is not balanced. Total debits must equal total credits.'
                ], 400);
            }

            // Auto-post if requested
            if ($request->auto_post) {
                $this->postJournalEntry($entry->id);
            }

            DB::commit();

            $oldData = $entry->getOriginal();
            $this->logAudit('Journal Entries', 'Update', 'JournalEntry', $entry->id, $oldData, $entry->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Journal entry updated successfully',
                'entry' => $entry->load('lines.account')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating journal entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating journal entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Post Journal Entry
     */
    public function postJournalEntry($id)
    {
        try {
            $entry = JournalEntry::findOrFail($id);

            if (!$entry->canBePosted()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Journal entry cannot be posted. It may not be balanced or already posted.'
                ], 400);
            }

            $oldStatus = $entry->status;
            $entry->post();

            $this->logAudit('Journal Entries', 'Post', 'JournalEntry', $entry->id, ['status' => $oldStatus], ['status' => $entry->status]);

            return response()->json([
                'success' => true,
                'message' => 'Journal entry posted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error posting journal entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error posting journal entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * General Ledger View
     */
    public function generalLedger(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        $query = GeneralLedger::with(['account', 'creator']);

        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }

        $entries = $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc')->paginate(50);
        $accounts = ChartOfAccount::active()->orderBy('code')->get();
        
        // Fetch GL Accounts and Cash Boxes for reference details
        $glAccounts = \App\Models\GlAccount::where('is_active', true)->orderBy('code')->get();
        $cashBoxes = \App\Models\CashBox::where('is_active', true)->orderBy('name')->get();

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportGeneralLedgerPdf($query->get(), $request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getGeneralLedgerData($request);
        }

        return view('modules.accounting.general-ledger', compact('entries', 'accounts', 'glAccounts', 'cashBoxes'));
    }

    /**
     * Get General Ledger Data (AJAX)
     */
    public function getGeneralLedgerData(Request $request)
    {
        try {
            $validated = $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date',
                'account_id' => 'nullable|integer',
                'type' => 'nullable|string|in:Debit,Credit',
                'q' => 'nullable|string',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:5|max:100'
            ]);

            $query = GeneralLedger::with(['account', 'creator']);

            if (!empty($validated['account_id'])) {
                $query->where('account_id', $validated['account_id']);
            }

            if (!empty($validated['date_from'])) {
                $query->whereDate('transaction_date', '>=', $validated['date_from']);
            }

            if (!empty($validated['date_to'])) {
                $query->whereDate('transaction_date', '<=', $validated['date_to']);
            }

            if (!empty($validated['type'])) {
                $query->where('type', $validated['type']);
            }

            if (!empty($validated['q'])) {
                $search = $validated['q'];
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('reference_no', 'like', "%{$search}%")
                      ->orWhereHas('account', function($acc) use ($search) {
                          $acc->where('name', 'like', "%{$search}%")
                              ->orWhere('code', 'like', "%{$search}%");
                      });
                });
            }

            // Get all entries for summary calculation
            $allEntries = $query->get();
            
            // Calculate running balance
            $runningBalance = 0;
            if (!empty($validated['account_id'])) {
                $account = \App\Models\ChartOfAccount::find($validated['account_id']);
                $runningBalance = $account ? $account->opening_balance : 0;
            }

            $entriesWithBalance = $allEntries->map(function($entry) use (&$runningBalance) {
                if (in_array($entry->account->type, ['Asset', 'Expense'])) {
                    $runningBalance += ($entry->type === 'Debit' ? $entry->amount : -$entry->amount);
                } else {
                    $runningBalance += ($entry->type === 'Credit' ? $entry->amount : -$entry->amount);
                }
                
                return [
                    'id' => $entry->id,
                    'date' => $entry->transaction_date->format('Y-m-d'),
                    'date_display' => $entry->transaction_date->format('d M Y'),
                    'account_code' => $entry->account->code,
                    'account_name' => $entry->account->name,
                    'account_type' => $entry->account->type,
                    'reference' => $entry->reference_no ?? '-',
                    'description' => $entry->description,
                    'type' => $entry->type,
                    'debit' => $entry->type === 'Debit' ? (float)$entry->amount : 0,
                    'credit' => $entry->type === 'Credit' ? (float)$entry->amount : 0,
                    'balance' => $runningBalance,
                    'source' => $entry->source ?? 'general',
                    'created_by' => $entry->creator ? $entry->creator->name : 'System'
                ];
            });

            // Calculate totals
            $totalDebit = round($allEntries->where('type', 'Debit')->sum('amount'), 2);
            $totalCredit = round($allEntries->where('type', 'Credit')->sum('amount'), 2);
            $balance = round($totalDebit - $totalCredit, 2);

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $entriesWithBalance->count();
            $paginatedEntries = $entriesWithBalance->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_debit' => $totalDebit,
                    'total_credit' => $totalCredit,
                    'balance' => $balance,
                    'count' => $totalEntries
                ],
                'entries' => $paginatedEntries,
                'page' => $page,
                'per_page' => $perPage
            ]);
        } catch (\Exception $e) {
            \Log::error('General Ledger data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading ledger data: ' . $e->getMessage(),
                'summary' => [
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'balance' => 0,
                    'count' => 0
                ],
                'entries' => []
            ], 500);
        }
    }

    /**
     * Export General Ledger PDF
     */
    private function exportGeneralLedgerPdf($entries, $request)
    {
        try {
            $account = null;
            $runningBalance = 0;
            if ($request->account_id) {
                $account = ChartOfAccount::find($request->account_id);
                $runningBalance = $account ? $account->opening_balance : 0;
            }

            $data = [
                'entries' => $entries,
                'account' => $account,
                'runningBalance' => $runningBalance,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
                'filters' => $request->only(['account_id', 'date_from', 'date_to', 'type']),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.general-ledger', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'General_Ledger_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('General Ledger PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Trial Balance
     */
    public function trialBalance(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportTrialBalancePdf($request);
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportTrialBalanceExcel($request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getTrialBalanceData($request);
        }

        $date = $request->date ?? now()->format('Y-m-d');
        $accounts = ChartOfAccount::active()->orderBy('code')->get();

        return view('modules.accounting.trial-balance', compact('date', 'accounts'));
    }

    /**
     * Get Trial Balance Data (AJAX)
     */
    public function getTrialBalanceData(Request $request)
    {
        try {
            // Only get filter parameters
            $filterParams = $request->only([
                'date', 'account_type', 'q', 'show_zero_balance', 'page', 'per_page'
            ]);

            // Convert empty strings to null
            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            // Validate only filter parameters
            $validated = \Validator::make($filterParams, [
                'date' => 'nullable|date',
                'account_type' => 'nullable|string',
                'q' => 'nullable|string|max:255',
                'show_zero_balance' => 'nullable|boolean',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ])->validate();

            $date = $validated['date'] ?? now()->format('Y-m-d');

            $query = ChartOfAccount::active();

            // Account type filter
            if (!empty($validated['account_type'])) {
                $query->where('type', $validated['account_type']);
            }

            // Search filter
            if (!empty($validated['q'])) {
                $searchTerm = $validated['q'];
                $query->where(function($q) use ($searchTerm) {
                    $q->where('code', 'like', "%{$searchTerm}%")
                      ->orWhere('name', 'like', "%{$searchTerm}%");
                });
            }

            $accounts = $query->with(['ledgerEntries' => function($query) use ($date) {
                $query->whereDate('transaction_date', '<=', $date);
            }])
            ->orderBy('code')
            ->get()
            ->map(function($account) {
                $debits = $account->ledgerEntries->where('type', 'Debit')->sum('amount');
                $credits = $account->ledgerEntries->where('type', 'Credit')->sum('amount');
                
                return [
                    'account_id' => $account->id,
                    'account_code' => $account->code,
                    'account_name' => $account->name,
                    'account_type' => $account->type,
                    'debits' => (float)$debits,
                    'credits' => (float)$credits,
                    'balance' => (float)($debits - $credits),
                ];
            });

            // Filter zero balance accounts if requested
            if (empty($validated['show_zero_balance']) || !$validated['show_zero_balance']) {
                $accounts = $accounts->filter(function($item) {
                    return abs($item['balance']) > 0.01 || $item['debits'] > 0 || $item['credits'] > 0;
                });
            }

            // Calculate totals
            $totalDebits = round($accounts->sum('debits'), 2);
            $totalCredits = round($accounts->sum('credits'), 2);
            $totalBalance = round($totalDebits - $totalCredits, 2);
            $accountCount = $accounts->count();

            // Format accounts
            $formattedAccounts = $accounts->map(function($account) {
                return [
                    'id' => $account['account_id'],
                    'code' => $account['account_code'],
                    'name' => $account['account_name'],
                    'type' => $account['account_type'],
                    'debits' => $account['debits'],
                    'credits' => $account['credits'],
                    'balance' => $account['balance'],
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 50);
            $totalEntries = $formattedAccounts->count();
            $paginatedAccounts = $formattedAccounts->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_debits' => $totalDebits,
                    'total_credits' => $totalCredits,
                    'total_balance' => $totalBalance,
                    'account_count' => $accountCount,
                    'is_balanced' => abs($totalBalance) < 0.01,
                    'count' => $totalEntries
                ],
                'accounts' => $paginatedAccounts,
                'date' => $date,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading trial balance data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading trial balance: ' . $e->getMessage(),
                'summary' => [
                    'total_debits' => 0,
                    'total_credits' => 0,
                    'total_balance' => 0,
                    'account_count' => 0,
                    'is_balanced' => false,
                    'count' => 0
                ],
                'accounts' => []
            ], 500);
        }
    }

    /**
     * Export Trial Balance PDF
     */
    private function exportTrialBalancePdf($request)
    {
        try {
            $date = $request->date ?? now()->format('Y-m-d');

            $accounts = ChartOfAccount::active()
                ->with(['ledgerEntries' => function($query) use ($date) {
                    $query->whereDate('transaction_date', '<=', $date);
                }])
                ->orderBy('code')
                ->get()
                ->map(function($account) {
                    $debits = $account->ledgerEntries->where('type', 'Debit')->sum('amount');
                    $credits = $account->ledgerEntries->where('type', 'Credit')->sum('amount');
                    
                    return [
                        'account' => $account,
                        'debits' => $debits,
                        'credits' => $credits,
                        'balance' => $debits - $credits,
                    ];
                })
                ->filter(function($item) {
                    return abs($item['balance']) > 0.01 || $item['debits'] > 0 || $item['credits'] > 0;
                });

            $totalDebits = $accounts->sum('debits');
            $totalCredits = $accounts->sum('credits');

            $data = [
                'accounts' => $accounts,
                'date' => $date,
                'totalDebits' => $totalDebits,
                'totalCredits' => $totalCredits,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.trial-balance', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Trial_Balance_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Trial Balance PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Trial Balance Excel
     */
    private function exportTrialBalanceExcel($request)
    {
        try {
            $date = $request->date ?? now()->format('Y-m-d');

            $accounts = ChartOfAccount::active()
                ->with(['ledgerEntries' => function($query) use ($date) {
                    $query->whereDate('transaction_date', '<=', $date);
                }])
                ->orderBy('code')
                ->get()
                ->map(function($account) {
                    $debits = $account->ledgerEntries->where('type', 'Debit')->sum('amount');
                    $credits = $account->ledgerEntries->where('type', 'Credit')->sum('amount');
                    
                    return [
                        'account' => $account,
                        'debits' => $debits,
                        'credits' => $credits,
                        'balance' => $debits - $credits,
                    ];
                })
                ->filter(function($item) {
                    return abs($item['balance']) > 0.01 || $item['debits'] > 0 || $item['credits'] > 0;
                });

            $totalDebits = $accounts->sum('debits');
            $totalCredits = $accounts->sum('credits');

            $filename = 'Trial_Balance_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($accounts, $totalDebits, $totalCredits) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Account Code', 'Account Name', 'Account Type', 
                    'Debits', 'Credits', 'Balance'
                ]);

                // Data rows
                foreach ($accounts as $item) {
                    fputcsv($file, [
                        $item['account']->code ?? '',
                        $item['account']->name ?? '',
                        $item['account']->type ?? '',
                        number_format($item['debits'] ?? 0, 2),
                        number_format($item['credits'] ?? 0, 2),
                        number_format($item['balance'] ?? 0, 2),
                    ]);
                }

                // Totals row
                fputcsv($file, ['', 'TOTALS', '', number_format($totalDebits, 2), number_format($totalCredits, 2), number_format($totalDebits - $totalCredits, 2)]);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Trial Balance Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Balance Sheet
     */
    public function balanceSheet(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            $date = $request->date ?? now()->format('Y-m-d');
            $assets = $this->getAccountBalances('Asset', $date);
            $liabilities = $this->getAccountBalances('Liability', $date);
            $equity = $this->getAccountBalances('Equity', $date);
            $totalAssets = $assets->sum('balance');
            $totalLiabilities = $liabilities->sum('balance');
            $totalEquity = $equity->sum('balance');
            return $this->exportBalanceSheetPdf($assets, $liabilities, $equity, $totalAssets, $totalLiabilities, $totalEquity, $date);
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportBalanceSheetExcel($request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getBalanceSheetData($request);
        }

        $date = $request->date ?? now()->format('Y-m-d');

        return view('modules.accounting.balance-sheet', compact('date'));
    }

    /**
     * Get Balance Sheet Data (AJAX)
     */
    public function getBalanceSheetData(Request $request)
    {
        try {
            // Only get filter parameters
            $filterParams = $request->only([
                'date', 'account_type', 'q', 'show_zero_balance', 'page', 'per_page'
            ]);

            // Convert empty strings to null
            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            // Validate only filter parameters
            $validated = \Validator::make($filterParams, [
                'date' => 'nullable|date',
                'account_type' => 'nullable|in:Asset,Liability,Equity',
                'q' => 'nullable|string|max:255',
                'show_zero_balance' => 'nullable|boolean',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ])->validate();

            $date = $validated['date'] ?? now()->format('Y-m-d');

            // Get account balances with detailed information
            $assets = $this->getAccountBalancesDetailed('Asset', $date, $validated);
            $liabilities = $this->getAccountBalancesDetailed('Liability', $date, $validated);
            $equity = $this->getAccountBalancesDetailed('Equity', $date, $validated);

            $totalAssets = round($assets->sum('balance'), 2);
            $totalLiabilities = round($liabilities->sum('balance'), 2);
            $totalEquity = round($equity->sum('balance'), 2);
            $totalLiabilitiesEquity = round($totalLiabilities + $totalEquity, 2);
            $difference = round(abs($totalAssets - $totalLiabilitiesEquity), 2);
            $isBalanced = $difference < 0.01;

            // Format accounts
            $formattedAssets = $assets->map(function($item) {
                return [
                    'id' => $item['account']->id,
                    'code' => $item['account']->code,
                    'name' => $item['account']->name,
                    'type' => $item['account']->type,
                    'category' => $item['account']->category ?? '',
                    'opening_balance' => $item['opening_balance'] ?? 0,
                    'debits' => $item['debits'] ?? 0,
                    'credits' => $item['credits'] ?? 0,
                    'balance' => $item['balance'],
                ];
            })->values();

            $formattedLiabilities = $liabilities->map(function($item) {
                return [
                    'id' => $item['account']->id,
                    'code' => $item['account']->code,
                    'name' => $item['account']->name,
                    'type' => $item['account']->type,
                    'category' => $item['account']->category ?? '',
                    'opening_balance' => $item['opening_balance'] ?? 0,
                    'debits' => $item['debits'] ?? 0,
                    'credits' => $item['credits'] ?? 0,
                    'balance' => $item['balance'],
                ];
            })->values();

            $formattedEquity = $equity->map(function($item) {
                return [
                    'id' => $item['account']->id,
                    'code' => $item['account']->code,
                    'name' => $item['account']->name,
                    'type' => $item['account']->type,
                    'category' => $item['account']->category ?? '',
                    'opening_balance' => $item['opening_balance'] ?? 0,
                    'debits' => $item['debits'] ?? 0,
                    'credits' => $item['credits'] ?? 0,
                    'balance' => $item['balance'],
                ];
            })->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_assets' => $totalAssets,
                    'total_liabilities' => $totalLiabilities,
                    'total_equity' => $totalEquity,
                    'total_liabilities_equity' => $totalLiabilitiesEquity,
                    'difference' => $difference,
                    'is_balanced' => $isBalanced,
                    'assets_count' => $formattedAssets->count(),
                    'liabilities_count' => $formattedLiabilities->count(),
                    'equity_count' => $formattedEquity->count(),
                ],
                'assets' => $formattedAssets,
                'liabilities' => $formattedLiabilities,
                'equity' => $formattedEquity,
                'date' => $date,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading balance sheet data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading balance sheet: ' . $e->getMessage(),
                'summary' => [
                    'total_assets' => 0,
                    'total_liabilities' => 0,
                    'total_equity' => 0,
                    'total_liabilities_equity' => 0,
                    'difference' => 0,
                    'is_balanced' => false,
                    'assets_count' => 0,
                    'liabilities_count' => 0,
                    'equity_count' => 0,
                ],
                'assets' => [],
                'liabilities' => [],
                'equity' => []
            ], 500);
        }
    }

    /**
     * Get Account Balances with Detailed Information
     */
    private function getAccountBalancesDetailed($type, $date, $filters = [])
    {
        $query = ChartOfAccount::where('type', $type)->active();

        // Account type filter (for sub-filtering)
        if (!empty($filters['account_type']) && $filters['account_type'] === $type) {
            // Already filtered by type
        }

        // Search filter
        if (!empty($filters['q'])) {
            $searchTerm = $filters['q'];
            $query->where(function($q) use ($searchTerm) {
                $q->where('code', 'like', "%{$searchTerm}%")
                  ->orWhere('name', 'like', "%{$searchTerm}%");
            });
        }

        $startDate = $filters['start_date'] ?? null;
        
        $query->with(['ledgerEntries' => function($q) use ($date, $startDate) {
            $q->whereDate('transaction_date', '<=', $date);
            if ($startDate) {
                $q->whereDate('transaction_date', '>=', $startDate);
            }
        }]);

        return $query->orderBy('code')->get()->map(function($account) use ($type, $startDate) {
            $debits = $account->ledgerEntries->where('type', 'Debit')->sum('amount');
            $credits = $account->ledgerEntries->where('type', 'Credit')->sum('amount');
            
            // For date range queries (like income statement), don't include opening balance
            // For single date queries (like balance sheet), include opening balance
            $openingBalance = $startDate ? 0 : ($account->opening_balance ?? 0);
            
            // Assets and Expenses: Debit - Credit
            // Liabilities, Equity, Income: Credit - Debit
            if (in_array($account->type, ['Asset', 'Expense'])) {
                $balance = $debits - $credits + $openingBalance;
            } else {
                $balance = $credits - $debits + $openingBalance;
            }

            return [
                'account' => $account,
                'opening_balance' => (float)$openingBalance,
                'debits' => (float)$debits,
                'credits' => (float)$credits,
                'balance' => (float)$balance,
            ];
        })->filter(function($item) use ($filters) {
            // Filter zero balance accounts if requested
            if (empty($filters['show_zero_balance']) || !$filters['show_zero_balance']) {
                return abs($item['balance']) > 0.01;
            }
            return true;
        });
    }

    /**
     * Export Balance Sheet Excel
     */
    private function exportBalanceSheetExcel($request)
    {
        try {
            $date = $request->date ?? now()->format('Y-m-d');
            $assets = $this->getAccountBalances('Asset', $date);
            $liabilities = $this->getAccountBalances('Liability', $date);
            $equity = $this->getAccountBalances('Equity', $date);
            $totalAssets = $assets->sum('balance');
            $totalLiabilities = $liabilities->sum('balance');
            $totalEquity = $equity->sum('balance');

            $filename = 'Balance_Sheet_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($assets, $liabilities, $equity, $totalAssets, $totalLiabilities, $totalEquity) {
                $file = fopen('php://output', 'w');
                
                // Assets Section
                fputcsv($file, ['ASSETS']);
                fputcsv($file, ['Account Code', 'Account Name', 'Balance']);
                foreach ($assets as $item) {
                    fputcsv($file, [
                        $item['account']->code ?? '',
                        $item['account']->name ?? '',
                        number_format($item['balance'] ?? 0, 2),
                    ]);
                }
                fputcsv($file, ['', 'TOTAL ASSETS', number_format($totalAssets, 2)]);
                fputcsv($file, []); // Empty row

                // Liabilities Section
                fputcsv($file, ['LIABILITIES']);
                fputcsv($file, ['Account Code', 'Account Name', 'Balance']);
                foreach ($liabilities as $item) {
                    fputcsv($file, [
                        $item['account']->code ?? '',
                        $item['account']->name ?? '',
                        number_format($item['balance'] ?? 0, 2),
                    ]);
                }
                fputcsv($file, ['', 'TOTAL LIABILITIES', number_format($totalLiabilities, 2)]);
                fputcsv($file, []); // Empty row

                // Equity Section
                fputcsv($file, ['EQUITY']);
                fputcsv($file, ['Account Code', 'Account Name', 'Balance']);
                foreach ($equity as $item) {
                    fputcsv($file, [
                        $item['account']->code ?? '',
                        $item['account']->name ?? '',
                        number_format($item['balance'] ?? 0, 2),
                    ]);
                }
                fputcsv($file, ['', 'TOTAL EQUITY', number_format($totalEquity, 2)]);
                fputcsv($file, ['', 'TOTAL LIABILITIES & EQUITY', number_format($totalLiabilities + $totalEquity, 2)]);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Balance Sheet Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export Balance Sheet PDF
     */
    private function exportBalanceSheetPdf($assets, $liabilities, $equity, $totalAssets, $totalLiabilities, $totalEquity, $date)
    {
        try {
            $data = [
                'assets' => $assets,
                'liabilities' => $liabilities,
                'equity' => $equity,
                'totalAssets' => $totalAssets,
                'totalLiabilities' => $totalLiabilities,
                'totalEquity' => $totalEquity,
                'date' => $date,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.balance-sheet', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Balance_Sheet_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Balance Sheet PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Income Statement (Profit & Loss)
     */
    public function incomeStatement(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
            $endDate = $request->end_date ?? now()->format('Y-m-d');
            $income = $this->getAccountBalances('Income', $endDate, $startDate);
            $expenses = $this->getAccountBalances('Expense', $endDate, $startDate);
            $totalIncome = $income->sum('balance');
            $totalExpenses = $expenses->sum('balance');
            $netIncome = $totalIncome - $totalExpenses;
            return $this->exportIncomeStatementPdf($income, $expenses, $totalIncome, $totalExpenses, $netIncome, $startDate, $endDate);
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportIncomeStatementExcel($request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getIncomeStatementData($request);
        }

        $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        return view('modules.accounting.income-statement', compact('startDate', 'endDate'));
    }

    /**
     * Get Income Statement Data (AJAX)
     */
    public function getIncomeStatementData(Request $request)
    {
        try {
            // Only get filter parameters
            $filterParams = $request->only([
                'start_date', 'end_date', 'account_type', 'q', 'show_zero_balance'
            ]);

            // Convert empty strings to null
            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            // Validate only filter parameters
            $validated = \Validator::make($filterParams, [
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'account_type' => 'nullable|in:Income,Expense',
                'q' => 'nullable|string|max:255',
                'show_zero_balance' => 'nullable|boolean',
            ])->validate();

            $startDate = $validated['start_date'] ?? now()->startOfYear()->format('Y-m-d');
            $endDate = $validated['end_date'] ?? now()->format('Y-m-d');

            // Get account balances with detailed information
            $income = $this->getAccountBalancesDetailed('Income', $endDate, array_merge($validated, ['start_date' => $startDate]));
            $expenses = $this->getAccountBalancesDetailed('Expense', $endDate, array_merge($validated, ['start_date' => $startDate]));

            $totalIncome = round($income->sum('balance'), 2);
            $totalExpenses = round($expenses->sum('balance'), 2);
            $netIncome = round($totalIncome - $totalExpenses, 2);
            $profitMargin = $totalIncome > 0 ? round(($netIncome / $totalIncome) * 100, 2) : 0;

            // Format accounts
            $formattedIncome = $income->map(function($item) {
                return [
                    'id' => $item['account']->id,
                    'code' => $item['account']->code,
                    'name' => $item['account']->name,
                    'type' => $item['account']->type,
                    'category' => $item['account']->category ?? '',
                    'opening_balance' => $item['opening_balance'] ?? 0,
                    'debits' => $item['debits'] ?? 0,
                    'credits' => $item['credits'] ?? 0,
                    'balance' => $item['balance'],
                ];
            })->values();

            $formattedExpenses = $expenses->map(function($item) {
                return [
                    'id' => $item['account']->id,
                    'code' => $item['account']->code,
                    'name' => $item['account']->name,
                    'type' => $item['account']->type,
                    'category' => $item['account']->category ?? '',
                    'opening_balance' => $item['opening_balance'] ?? 0,
                    'debits' => $item['debits'] ?? 0,
                    'credits' => $item['credits'] ?? 0,
                    'balance' => $item['balance'],
                ];
            })->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_income' => $totalIncome,
                    'total_expenses' => $totalExpenses,
                    'net_income' => $netIncome,
                    'profit_margin' => $profitMargin,
                    'income_count' => $formattedIncome->count(),
                    'expenses_count' => $formattedExpenses->count(),
                ],
                'income' => $formattedIncome,
                'expenses' => $formattedExpenses,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading income statement data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading income statement: ' . $e->getMessage(),
                'summary' => [
                    'total_income' => 0,
                    'total_expenses' => 0,
                    'net_income' => 0,
                    'profit_margin' => 0,
                    'income_count' => 0,
                    'expenses_count' => 0,
                ],
                'income' => [],
                'expenses' => []
            ], 500);
        }
    }

    /**
     * Export Income Statement Excel
     */
    private function exportIncomeStatementExcel($request)
    {
        try {
            $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
            $endDate = $request->end_date ?? now()->format('Y-m-d');
            $income = $this->getAccountBalances('Income', $endDate, $startDate);
            $expenses = $this->getAccountBalances('Expense', $endDate, $startDate);
            $totalIncome = $income->sum('balance');
            $totalExpenses = $expenses->sum('balance');
            $netIncome = $totalIncome - $totalExpenses;

            $filename = 'Income_Statement_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($income, $expenses, $totalIncome, $totalExpenses, $netIncome) {
                $file = fopen('php://output', 'w');
                
                // Income Section
                fputcsv($file, ['INCOME']);
                fputcsv($file, ['Account Code', 'Account Name', 'Amount']);
                foreach ($income as $item) {
                    fputcsv($file, [
                        $item['account']->code ?? '',
                        $item['account']->name ?? '',
                        number_format($item['balance'] ?? 0, 2),
                    ]);
                }
                fputcsv($file, ['', 'TOTAL INCOME', number_format($totalIncome, 2)]);
                fputcsv($file, []); // Empty row

                // Expenses Section
                fputcsv($file, ['EXPENSES']);
                fputcsv($file, ['Account Code', 'Account Name', 'Amount']);
                foreach ($expenses as $item) {
                    fputcsv($file, [
                        $item['account']->code ?? '',
                        $item['account']->name ?? '',
                        number_format($item['balance'] ?? 0, 2),
                    ]);
                }
                fputcsv($file, ['', 'TOTAL EXPENSES', number_format($totalExpenses, 2)]);
                fputcsv($file, ['', 'NET INCOME', number_format($netIncome, 2)]);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Income Statement Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Export Income Statement PDF
     */
    private function exportIncomeStatementPdf($income, $expenses, $totalIncome, $totalExpenses, $netIncome, $startDate, $endDate)
    {
        try {
            $data = [
                'income' => $income,
                'expenses' => $expenses,
                'totalIncome' => $totalIncome,
                'totalExpenses' => $totalExpenses,
                'netIncome' => $netIncome,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.income-statement', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Income_Statement_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Income Statement PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Helper: Get account balances
     */
    private function getAccountBalances($type, $endDate, $startDate = null)
    {
        $query = ChartOfAccount::where('type', $type)->active();

        if ($startDate) {
            $query->with(['ledgerEntries' => function($q) use ($startDate, $endDate) {
                $q->whereBetween('transaction_date', [$startDate, $endDate]);
            }]);
        } else {
            $query->with(['ledgerEntries' => function($q) use ($endDate) {
                $q->whereDate('transaction_date', '<=', $endDate);
            }]);
        }

        return $query->get()->map(function($account) {
            $debits = $account->ledgerEntries->where('type', 'Debit')->sum('amount');
            $credits = $account->ledgerEntries->where('type', 'Credit')->sum('amount');
            
            // Assets and Expenses: Debit - Credit
            // Liabilities, Equity, Income: Credit - Debit
            if (in_array($account->type, ['Asset', 'Expense'])) {
                $balance = $debits - $credits;
            } else {
                $balance = $credits - $debits;
            }

            return [
                'account' => $account,
                'balance' => $balance + ($account->opening_balance ?? 0),
            ];
        })->filter(function($item) {
            return abs($item['balance']) > 0.01;
        });
    }

    /**
     * Log audit trail
     */
    private function logAudit($module, $action, $recordType, $recordId, $oldValues = null, $newValues = null)
    {
        AccountingAuditLog::create([
            'module' => $module,
            'action' => $action,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => "{$action} {$recordType} #{$recordId}",
            'ip_address' => request()->ip(),
            'user_id' => Auth::id(),
        ]);
    }
}

