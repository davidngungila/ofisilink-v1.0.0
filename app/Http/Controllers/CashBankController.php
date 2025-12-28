<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\GeneralLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ActivityLogService;

class CashBankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Bank Accounts Management
     */
    public function bankAccounts(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportBankAccountsPdf($request);
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportBankAccountsExcel($request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getBankAccountsData($request);
        }

        $accounts = ChartOfAccount::where('type', 'Asset')
            ->where('category', 'Current Asset')
            ->active()
            ->orderBy('code')
            ->get();

        return view('modules.accounting.cash-bank.accounts', compact('accounts'));
    }

    /**
     * Store a new bank account
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:255|unique:bank_accounts,account_number',
                'account_name' => 'nullable|string|max:255',
                'branch_name' => 'nullable|string|max:255',
                'swift_code' => 'nullable|string|max:255',
                'balance' => 'nullable|numeric|min:0',
                'account_id' => 'nullable|exists:chart_of_accounts,id',
                'is_primary' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'notes' => 'nullable|string|max:1000',
            ]);

            $bankAccount = \App\Models\BankAccount::create([
                'user_id' => null, // Organization bank accounts have null user_id
                'name' => $validated['name'],
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'] ?? null,
                'branch_name' => $validated['branch_name'] ?? null,
                'swift_code' => $validated['swift_code'] ?? null,
                'balance' => $validated['balance'] ?? 0,
                'account_id' => $validated['account_id'] ?? null,
                'is_primary' => $validated['is_primary'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Bank account created', ['bank_account_id' => $bankAccount->id, 'user_id' => $user->id]);

            // Log activity
            ActivityLogService::logCreated($bankAccount, "Created bank account: {$bankAccount->bank_name} - {$bankAccount->account_number}", [
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'account_name' => $bankAccount->account_name,
                'balance' => $bankAccount->balance,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank account created successfully',
                'bank_account' => $bankAccount
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating bank account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create bank account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a bank account
     */
    public function update(Request $request, $id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $bankAccount = \App\Models\BankAccount::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|string|max:255|unique:bank_accounts,account_number,' . $id,
                'account_name' => 'nullable|string|max:255',
                'branch_name' => 'nullable|string|max:255',
                'swift_code' => 'nullable|string|max:255',
                'balance' => 'nullable|numeric|min:0',
                'account_id' => 'nullable|exists:chart_of_accounts,id',
                'is_primary' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'notes' => 'nullable|string|max:1000',
            ]);

            $bankAccount->update([
                'name' => $validated['name'],
                'bank_name' => $validated['bank_name'],
                'account_number' => $validated['account_number'],
                'account_name' => $validated['account_name'] ?? null,
                'branch_name' => $validated['branch_name'] ?? null,
                'swift_code' => $validated['swift_code'] ?? null,
                'balance' => $validated['balance'] ?? $bankAccount->balance,
                'account_id' => $validated['account_id'] ?? null,
                'is_primary' => $validated['is_primary'] ?? false,
                'is_active' => $validated['is_active'] ?? true,
                'notes' => $validated['notes'] ?? null,
            ]);

            Log::info('Bank account updated', ['bank_account_id' => $bankAccount->id, 'user_id' => $user->id]);

            // Log activity
            ActivityLogService::logUpdated($bankAccount, "Updated bank account: {$bankAccount->bank_name} - {$bankAccount->account_number}", [
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'account_name' => $bankAccount->account_name,
                'balance' => $bankAccount->balance,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank account updated successfully',
                'bank_account' => $bankAccount
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error updating bank account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bank account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a bank account
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            
            if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $bankAccount = \App\Models\BankAccount::findOrFail($id);

            // Check if account has transactions (optional - you may want to prevent deletion if there are transactions)
            // For now, we'll allow deletion

            $bankAccountData = [
                'bank_name' => $bankAccount->bank_name,
                'account_number' => $bankAccount->account_number,
                'account_name' => $bankAccount->account_name,
            ];

            $bankAccount->delete();

            Log::info('Bank account deleted', ['bank_account_id' => $id, 'user_id' => $user->id]);

            // Log activity
            ActivityLogService::logDeleted('BankAccount', "Deleted bank account: {$bankAccountData['bank_name']} - {$bankAccountData['account_number']}", $bankAccountData);

            return response()->json([
                'success' => true,
                'message' => 'Bank account deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting bank account: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bank account: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Bank Accounts Data (AJAX)
     */
    public function getBankAccountsData(Request $request)
    {
        try {
            // Only get filter parameters
            $filterParams = $request->only([
                'bank_name', 'is_active', 'q', 'page', 'per_page'
            ]);

            // Convert empty strings to null
            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            // Validate only filter parameters
            $validated = \Validator::make($filterParams, [
                'bank_name' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
                'q' => 'nullable|string|max:255',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ])->validate();

            $query = \App\Models\BankAccount::with(['user', 'account']);

            // Bank name filter
            if (!empty($validated['bank_name'])) {
                $query->where('bank_name', 'like', "%{$validated['bank_name']}%");
            }

            // Active status filter
            if (isset($validated['is_active']) && $validated['is_active'] !== null) {
                $query->where('is_active', $validated['is_active']);
            }

            // Search filter
            if (!empty($validated['q'])) {
                $searchTerm = $validated['q'];
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('account_name', 'like', "%{$searchTerm}%")
                      ->orWhere('bank_name', 'like', "%{$searchTerm}%")
                      ->orWhere('account_number', 'like', "%{$searchTerm}%")
                      ->orWhere('branch_name', 'like', "%{$searchTerm}%");
                });
            }

            // Get all accounts for summary calculation
            $allAccounts = $query->orderBy('bank_name')->orderBy('account_name')->get();
            
            // Calculate totals
            $totalAccounts = $allAccounts->count();
            $totalBalance = round($allAccounts->sum('balance'), 2);
            $activeAccounts = $allAccounts->where('is_active', true)->count();
            $primaryAccounts = $allAccounts->where('is_primary', true)->count();

            // Format accounts
            $formattedAccounts = $allAccounts->map(function($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name ?? $account->account_name ?? 'N/A',
                    'bank_name' => $account->bank_name ?? 'N/A',
                    'account_number' => $account->account_number ?? 'N/A',
                    'account_name' => $account->account_name ?? 'N/A',
                    'branch_name' => $account->branch_name ?? null,
                    'swift_code' => $account->swift_code ?? null,
                    'balance' => (float)($account->balance ?? 0),
                    'is_active' => (bool)($account->is_active ?? true),
                    'is_primary' => (bool)($account->is_primary ?? false),
                    'user_name' => $account->user ? $account->user->name : 'N/A',
                    'account_code' => $account->account ? $account->account->code : null,
                    'notes' => $account->notes ?? null,
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 20);
            $totalEntries = $formattedAccounts->count();
            $paginatedAccounts = $formattedAccounts->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_accounts' => $totalAccounts,
                    'total_balance' => $totalBalance,
                    'active_accounts' => $activeAccounts,
                    'primary_accounts' => $primaryAccounts,
                    'count' => $totalEntries
                ],
                'accounts' => $paginatedAccounts,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading bank accounts data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading bank accounts: ' . $e->getMessage(),
                'summary' => [
                    'total_accounts' => 0,
                    'total_balance' => 0,
                    'active_accounts' => 0,
                    'primary_accounts' => 0,
                    'count' => 0
                ],
                'accounts' => []
            ], 500);
        }
    }

    /**
     * Export Bank Accounts PDF
     */
    private function exportBankAccountsPdf($request)
    {
        try {
            $query = \App\Models\BankAccount::with(['user', 'account']);
            
            if ($request->has('bank_name') && !empty($request->bank_name)) {
                $query->where('bank_name', 'like', "%{$request->bank_name}%");
            }
            
            if ($request->has('is_active') && $request->is_active !== null) {
                $query->where('is_active', $request->is_active);
            }
            
            $bankAccounts = $query->orderBy('bank_name')->orderBy('account_name')->get();

            $data = [
                'bankAccounts' => $bankAccounts,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.bank-accounts', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Bank_Accounts_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Bank Accounts PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Bank Accounts Excel
     */
    private function exportBankAccountsExcel($request)
    {
        try {
            $query = \App\Models\BankAccount::with(['user', 'account']);
            
            if ($request->has('bank_name') && !empty($request->bank_name)) {
                $query->where('bank_name', 'like', "%{$request->bank_name}%");
            }
            
            if ($request->has('is_active') && $request->is_active !== null) {
                $query->where('is_active', $request->is_active);
            }
            
            $bankAccounts = $query->orderBy('bank_name')->orderBy('account_name')->get();

            $filename = 'Bank_Accounts_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($bankAccounts) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Account Name', 'Bank Name', 'Account Number', 'Branch', 
                    'SWIFT Code', 'Balance', 'Status', 'Primary', 'User'
                ]);

                // Data rows
                foreach ($bankAccounts as $account) {
                    fputcsv($file, [
                        $account->name ?? $account->account_name ?? 'N/A',
                        $account->bank_name ?? 'N/A',
                        $account->account_number ?? 'N/A',
                        $account->branch_name ?? '',
                        $account->swift_code ?? '',
                        number_format($account->balance ?? 0, 2),
                        ($account->is_active ?? true) ? 'Active' : 'Inactive',
                        ($account->is_primary ?? false) ? 'Yes' : 'No',
                        $account->user ? $account->user->name : 'N/A',
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Bank Accounts Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Bank Reconciliation
     */
    public function reconciliation(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin'])) {
            abort(403);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportReconciliationPdf($request);
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportReconciliationExcel($request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getReconciliationData($request);
        }

        $bankAccounts = \App\Models\BankAccount::orderBy('bank_name')->get();
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');

        return view('modules.accounting.cash-bank.reconciliation', compact('bankAccounts', 'startDate', 'endDate'));
    }

    /**
     * Get Reconciliation Data (AJAX)
     */
    public function getReconciliationData(Request $request)
    {
        try {
            // Only get filter parameters
            $filterParams = $request->only([
                'bank_account_id', 'start_date', 'end_date', 'q', 'page', 'per_page'
            ]);

            // Convert empty strings to null
            foreach ($filterParams as $key => $value) {
                if ($value === '') {
                    $filterParams[$key] = null;
                }
            }

            // Validate only filter parameters
            $validated = \Validator::make($filterParams, [
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'q' => 'nullable|string|max:255',
                'page' => 'nullable|integer|min:1',
                'per_page' => 'nullable|integer|min:1|max:100',
            ])->validate();

            if (empty($validated['bank_account_id'])) {
                return response()->json([
                    'success' => true,
                    'summary' => [
                        'total_transactions' => 0,
                        'total_debits' => 0,
                        'total_credits' => 0,
                        'opening_balance' => 0,
                        'closing_balance' => 0,
                        'count' => 0
                    ],
                    'transactions' => [],
                    'message' => 'Please select a bank account'
                ]);
            }

            $bankAccount = \App\Models\BankAccount::find($validated['bank_account_id']);
            $startDate = $validated['start_date'] ?? now()->startOfMonth()->format('Y-m-d');
            $endDate = $validated['end_date'] ?? now()->endOfMonth()->format('Y-m-d');

            // Find chart of account linked to bank account or search for cash/bank account
            $cashAccount = null;
            if ($bankAccount->account_id) {
                $cashAccount = ChartOfAccount::find($bankAccount->account_id);
            }
            
            if (!$cashAccount) {
                $cashAccount = ChartOfAccount::where(function($q) use ($bankAccount) {
                    $q->where('code', 'LIKE', '%CASH%')
                      ->orWhere('code', 'LIKE', '%BANK%')
                      ->orWhere('name', 'LIKE', '%' . ($bankAccount->bank_name ?? '') . '%');
                })->active()->first();
            }

            if (!$cashAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'No chart of account found for this bank account',
                    'summary' => [
                        'total_transactions' => 0,
                        'total_debits' => 0,
                        'total_credits' => 0,
                        'opening_balance' => 0,
                        'closing_balance' => 0,
                        'count' => 0
                    ],
                    'transactions' => []
                ], 404);
            }

            $query = GeneralLedger::where('account_id', $cashAccount->id)
                ->with(['account', 'creator']);

            // Date range filter
            $query->whereBetween('transaction_date', [$startDate, $endDate]);

            // Search filter
            if (!empty($validated['q'])) {
                $searchTerm = $validated['q'];
                $query->where(function($q) use ($searchTerm) {
                    $q->where('reference_no', 'like', "%{$searchTerm}%")
                      ->orWhere('description', 'like', "%{$searchTerm}%");
                });
            }

            // Get all transactions for summary
            $allTransactions = $query->orderBy('transaction_date')->orderBy('id')->get();
            
            // Calculate opening balance (balance before start date)
            $openingBalance = GeneralLedger::where('account_id', $cashAccount->id)
                ->whereDate('transaction_date', '<', $startDate)
                ->selectRaw('SUM(CASE WHEN type = "Debit" THEN amount ELSE -amount END) as balance')
                ->value('balance') ?? 0;
            $openingBalance += ($cashAccount->opening_balance ?? 0);

            // Calculate totals
            $totalDebits = round($allTransactions->where('type', 'Debit')->sum('amount'), 2);
            $totalCredits = round($allTransactions->where('type', 'Credit')->sum('amount'), 2);
            $closingBalance = round($openingBalance + $totalDebits - $totalCredits, 2);

            // Format transactions with running balance
            $runningBalance = $openingBalance;
            $formattedTransactions = $allTransactions->map(function($transaction) use (&$runningBalance) {
                if ($transaction->type === 'Debit') {
                    $runningBalance += $transaction->amount;
                } else {
                    $runningBalance -= $transaction->amount;
                }
                
                return [
                    'id' => $transaction->id,
                    'transaction_date' => $transaction->transaction_date ? $transaction->transaction_date->format('Y-m-d') : '',
                    'transaction_date_display' => $transaction->transaction_date ? $transaction->transaction_date->format('d M Y') : '',
                    'reference_no' => $transaction->reference_no ?? '-',
                    'description' => $transaction->description ?? '-',
                    'type' => $transaction->type,
                    'debit' => $transaction->type === 'Debit' ? (float)$transaction->amount : 0,
                    'credit' => $transaction->type === 'Credit' ? (float)$transaction->amount : 0,
                    'balance' => round($runningBalance, 2),
                    'created_by' => $transaction->creator ? $transaction->creator->name : 'N/A',
                ];
            });

            // Pagination
            $page = max(1, (int)($validated['page'] ?? 1));
            $perPage = (int)($validated['per_page'] ?? 50);
            $totalEntries = $formattedTransactions->count();
            $paginatedTransactions = $formattedTransactions->forPage($page, $perPage)->values();

            return response()->json([
                'success' => true,
                'summary' => [
                    'total_transactions' => $totalEntries,
                    'total_debits' => $totalDebits,
                    'total_credits' => $totalCredits,
                    'opening_balance' => round($openingBalance, 2),
                    'closing_balance' => $closingBalance,
                    'count' => $totalEntries
                ],
                'transactions' => $paginatedTransactions,
                'bank_account' => [
                    'id' => $bankAccount->id,
                    'name' => $bankAccount->name ?? $bankAccount->account_name ?? 'N/A',
                    'bank_name' => $bankAccount->bank_name ?? 'N/A',
                    'account_number' => $bankAccount->account_number ?? 'N/A',
                ],
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading reconciliation data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading reconciliation: ' . $e->getMessage(),
                'summary' => [
                    'total_transactions' => 0,
                    'total_debits' => 0,
                    'total_credits' => 0,
                    'opening_balance' => 0,
                    'closing_balance' => 0,
                    'count' => 0
                ],
                'transactions' => []
            ], 500);
        }
    }

    /**
     * Export Reconciliation PDF
     */
    private function exportReconciliationPdf($request)
    {
        try {
            // Similar logic to getReconciliationData but return PDF
            $data = $this->getReconciliationData($request);
            $data = json_decode($data->getContent(), true);
            
            if (!$data['success']) {
                return redirect()->back()->with('error', $data['message'] ?? 'Failed to generate PDF');
            }

            $pdfData = [
                'transactions' => $data['transactions'],
                'summary' => $data['summary'],
                'bank_account' => $data['bank_account'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.reconciliation', $pdfData);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Bank_Reconciliation_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Reconciliation PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Reconciliation Excel
     */
    private function exportReconciliationExcel($request)
    {
        try {
            $data = $this->getReconciliationData($request);
            $data = json_decode($data->getContent(), true);
            
            if (!$data['success']) {
                return redirect()->back()->with('error', $data['message'] ?? 'Failed to generate Excel');
            }

            $filename = 'Bank_Reconciliation_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                
                // Headers
                fputcsv($file, [
                    'Date', 'Reference', 'Description', 'Debit', 'Credit', 'Balance'
                ]);

                // Data rows
                foreach ($data['transactions'] as $transaction) {
                    fputcsv($file, [
                        $transaction['transaction_date_display'] ?? '',
                        $transaction['reference_no'] ?? '',
                        $transaction['description'] ?? '',
                        number_format($transaction['debit'] ?? 0, 2),
                        number_format($transaction['credit'] ?? 0, 2),
                        number_format($transaction['balance'] ?? 0, 2),
                    ]);
                }

                // Summary row
                fputcsv($file, ['', '', 'TOTALS', 
                    number_format($data['summary']['total_debits'] ?? 0, 2),
                    number_format($data['summary']['total_credits'] ?? 0, 2),
                    number_format($data['summary']['closing_balance'] ?? 0, 2)
                ]);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Reconciliation Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Cash Flow Statement
     */
    public function cashFlowStatement(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['Accountant', 'System Admin', 'CEO'])) {
            abort(403);
        }

        // Check if PDF export requested
        if ($request->has('export') && $request->export == 'pdf') {
            return $this->exportCashFlowPdf($request);
        }

        // Check if Excel export requested
        if ($request->has('export') && $request->export == 'excel') {
            return $this->exportCashFlowExcel($request);
        }

        // Check if AJAX data request
        if ($request->ajax() || $request->wantsJson()) {
            return $this->getCashFlowData($request);
        }

        $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');

        return view('modules.accounting.cash-bank.cash-flow-statement', compact('startDate', 'endDate'));
    }

    /**
     * Get Cash Flow Data (AJAX)
     */
    public function getCashFlowData(Request $request)
    {
        try {
            // Only get filter parameters
            $filterParams = $request->only([
                'start_date', 'end_date'
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
            ])->validate();

            $startDate = $validated['start_date'] ?? now()->startOfYear()->format('Y-m-d');
            $endDate = $validated['end_date'] ?? now()->format('Y-m-d');

            // Get detailed cash flow breakdown
            $operatingDetails = $this->calculateOperatingCashFlowDetailed($startDate, $endDate);
            $investingDetails = $this->calculateInvestingCashFlowDetailed($startDate, $endDate);
            $financingDetails = $this->calculateFinancingCashFlowDetailed($startDate, $endDate);

            $operatingCash = $operatingDetails['total'];
            $investingCash = $investingDetails['total'];
            $financingCash = $financingDetails['total'];
            $netCashFlow = $operatingCash + $investingCash + $financingCash;

            return response()->json([
                'success' => true,
                'summary' => [
                    'operating_cash' => round($operatingCash, 2),
                    'investing_cash' => round($investingCash, 2),
                    'financing_cash' => round($financingCash, 2),
                    'net_cash_flow' => round($netCashFlow, 2),
                ],
                'operating' => $operatingDetails,
                'investing' => $investingDetails,
                'financing' => $financingDetails,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading cash flow data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading cash flow: ' . $e->getMessage(),
                'summary' => [
                    'operating_cash' => 0,
                    'investing_cash' => 0,
                    'financing_cash' => 0,
                    'net_cash_flow' => 0,
                ],
                'operating' => ['items' => [], 'total' => 0],
                'investing' => ['items' => [], 'total' => 0],
                'financing' => ['items' => [], 'total' => 0],
            ], 500);
        }
    }

    private function calculateOperatingCashFlow($startDate, $endDate)
    {
        $details = $this->calculateOperatingCashFlowDetailed($startDate, $endDate);
        return $details['total'];
    }

    private function calculateOperatingCashFlowDetailed($startDate, $endDate)
    {
        $items = [];
        
        // Net income from income statement
        $incomeAccounts = ChartOfAccount::where('type', 'Income')->active()->get();
        $totalIncome = 0;
        foreach ($incomeAccounts as $account) {
            $credits = GeneralLedger::where('account_id', $account->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('type', 'Credit')
                ->sum('amount');
            if ($credits > 0) {
                $items[] = [
                    'description' => $account->name,
                    'code' => $account->code,
                    'amount' => $credits,
                    'type' => 'income'
                ];
                $totalIncome += $credits;
            }
        }

        $expenseAccounts = ChartOfAccount::where('type', 'Expense')->active()->get();
        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            $debits = GeneralLedger::where('account_id', $account->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('type', 'Debit')
                ->sum('amount');
            if ($debits > 0) {
                $items[] = [
                    'description' => $account->name,
                    'code' => $account->code,
                    'amount' => -$debits,
                    'type' => 'expense'
                ];
                $totalExpenses += $debits;
            }
        }

        $netIncome = $totalIncome - $totalExpenses;

        return [
            'items' => $items,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'total' => $netIncome
        ];
    }

    private function calculateInvestingCashFlow($startDate, $endDate)
    {
        $details = $this->calculateInvestingCashFlowDetailed($startDate, $endDate);
        return $details['total'];
    }

    private function calculateInvestingCashFlowDetailed($startDate, $endDate)
    {
        $items = [];
        
        // Fixed asset purchases/sales
        $fixedAssets = ChartOfAccount::where('type', 'Asset')
            ->where(function($q) {
                $q->where('category', 'Fixed Asset')
                  ->orWhere('name', 'like', '%Fixed Asset%')
                  ->orWhere('name', 'like', '%Property%')
                  ->orWhere('name', 'like', '%Equipment%');
            })
            ->active()
            ->get();

        $totalInvesting = 0;
        foreach ($fixedAssets as $account) {
            $debits = GeneralLedger::where('account_id', $account->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('type', 'Debit')
                ->sum('amount');
            $credits = GeneralLedger::where('account_id', $account->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('type', 'Credit')
                ->sum('amount');
            
            $net = $credits - $debits; // Sales (credits) - Purchases (debits)
            
            if (abs($net) > 0.01) {
                $items[] = [
                    'description' => $account->name,
                    'code' => $account->code,
                    'amount' => $net,
                    'type' => $net > 0 ? 'sale' : 'purchase'
                ];
                $totalInvesting += $net;
            }
        }

        return [
            'items' => $items,
            'total' => $totalInvesting
        ];
    }

    private function calculateFinancingCashFlow($startDate, $endDate)
    {
        $details = $this->calculateFinancingCashFlowDetailed($startDate, $endDate);
        return $details['total'];
    }

    private function calculateFinancingCashFlowDetailed($startDate, $endDate)
    {
        $items = [];
        
        // Equity accounts
        $equityAccounts = ChartOfAccount::where('type', 'Equity')->active()->get();
        $totalEquity = 0;
        foreach ($equityAccounts as $account) {
            $credits = GeneralLedger::where('account_id', $account->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('type', 'Credit')
                ->sum('amount');
            $debits = GeneralLedger::where('account_id', $account->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('type', 'Debit')
                ->sum('amount');
            
            $net = $credits - $debits;
            
            if (abs($net) > 0.01) {
                $items[] = [
                    'description' => $account->name,
                    'code' => $account->code,
                    'amount' => $net,
                    'type' => 'equity'
                ];
                $totalEquity += $net;
            }
        }

        // Long-term liabilities (loans)
        $liabilityAccounts = ChartOfAccount::where('type', 'Liability')
            ->where(function($q) {
                $q->where('category', 'Non-Current Liability')
                  ->orWhere('name', 'like', '%Loan%')
                  ->orWhere('name', 'like', '%Debt%');
            })
            ->active()
            ->get();

        $totalLoans = 0;
        foreach ($liabilityAccounts as $account) {
            $credits = GeneralLedger::where('account_id', $account->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('type', 'Credit')
                ->sum('amount');
            $debits = GeneralLedger::where('account_id', $account->id)
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->where('type', 'Debit')
                ->sum('amount');
            
            $net = $credits - $debits; // Borrowings (credits) - Repayments (debits)
            
            if (abs($net) > 0.01) {
                $items[] = [
                    'description' => $account->name,
                    'code' => $account->code,
                    'amount' => $net,
                    'type' => 'loan'
                ];
                $totalLoans += $net;
            }
        }

        return [
            'items' => $items,
            'total_equity' => $totalEquity,
            'total_loans' => $totalLoans,
            'total' => $totalEquity + $totalLoans
        ];
    }

    /**
     * Export Cash Flow PDF
     */
    private function exportCashFlowPdf($request)
    {
        try {
            $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
            $endDate = $request->end_date ?? now()->format('Y-m-d');
            
            $operatingDetails = $this->calculateOperatingCashFlowDetailed($startDate, $endDate);
            $investingDetails = $this->calculateInvestingCashFlowDetailed($startDate, $endDate);
            $financingDetails = $this->calculateFinancingCashFlowDetailed($startDate, $endDate);
            
            $operatingCash = $operatingDetails['total'];
            $investingCash = $investingDetails['total'];
            $financingCash = $financingDetails['total'];
            $netCashFlow = $operatingCash + $investingCash + $financingCash;

            $data = [
                'operating' => $operatingDetails,
                'investing' => $investingDetails,
                'financing' => $financingDetails,
                'operatingCash' => $operatingCash,
                'investingCash' => $investingCash,
                'financingCash' => $financingCash,
                'netCashFlow' => $netCashFlow,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'companyName' => config('app.name', 'Company'),
                'generatedAt' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('modules.accounting.pdf.cash-flow-statement', $data);
            $pdf->setPaper('A4', 'landscape');
            $filename = 'Cash_Flow_Statement_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Cash Flow PDF Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export Cash Flow Excel
     */
    private function exportCashFlowExcel($request)
    {
        try {
            $startDate = $request->start_date ?? now()->startOfYear()->format('Y-m-d');
            $endDate = $request->end_date ?? now()->format('Y-m-d');
            
            $operatingDetails = $this->calculateOperatingCashFlowDetailed($startDate, $endDate);
            $investingDetails = $this->calculateInvestingCashFlowDetailed($startDate, $endDate);
            $financingDetails = $this->calculateFinancingCashFlowDetailed($startDate, $endDate);
            
            $operatingCash = $operatingDetails['total'];
            $investingCash = $investingDetails['total'];
            $financingCash = $financingDetails['total'];
            $netCashFlow = $operatingCash + $investingCash + $financingCash;

            $filename = 'Cash_Flow_Statement_' . now()->format('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($operatingDetails, $investingDetails, $financingDetails, $operatingCash, $investingCash, $financingCash, $netCashFlow) {
                $file = fopen('php://output', 'w');
                
                // Operating Activities
                fputcsv($file, ['OPERATING ACTIVITIES']);
                fputcsv($file, ['Description', 'Amount']);
                foreach ($operatingDetails['items'] as $item) {
                    fputcsv($file, [
                        $item['description'] ?? '',
                        number_format($item['amount'] ?? 0, 2),
                    ]);
                }
                fputcsv($file, ['', 'TOTAL OPERATING', number_format($operatingCash, 2)]);
                fputcsv($file, []); // Empty row

                // Investing Activities
                fputcsv($file, ['INVESTING ACTIVITIES']);
                fputcsv($file, ['Description', 'Amount']);
                foreach ($investingDetails['items'] as $item) {
                    fputcsv($file, [
                        $item['description'] ?? '',
                        number_format($item['amount'] ?? 0, 2),
                    ]);
                }
                fputcsv($file, ['', 'TOTAL INVESTING', number_format($investingCash, 2)]);
                fputcsv($file, []); // Empty row

                // Financing Activities
                fputcsv($file, ['FINANCING ACTIVITIES']);
                fputcsv($file, ['Description', 'Amount']);
                foreach ($financingDetails['items'] as $item) {
                    fputcsv($file, [
                        $item['description'] ?? '',
                        number_format($item['amount'] ?? 0, 2),
                    ]);
                }
                fputcsv($file, ['', 'TOTAL FINANCING', number_format($financingCash, 2)]);
                fputcsv($file, ['', 'NET CASH FLOW', number_format($netCashFlow, 2)]);

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Cash Flow Excel Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }
}

