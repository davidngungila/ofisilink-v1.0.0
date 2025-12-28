<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GlAccount;
use App\Models\CashBox;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceSetupController extends Controller
{
    public function index()
    {
        $gls = GlAccount::with('chartOfAccount')->orderBy('code')->get();
        $cashBoxes = CashBox::with('chartOfAccount')->orderBy('name')->get();
        return view('modules.finance.settings', compact('gls', 'cashBoxes'));
    }

    /**
     * Sync GL Account to Chart of Accounts
     */
    private function syncGlAccountToChartOfAccounts(GlAccount $glAccount): ?ChartOfAccount
    {
        // If already synced, return existing
        if ($glAccount->chart_of_account_id) {
            return $glAccount->chartOfAccount;
        }

        // Check if Chart of Account already exists with same code
        $chartAccount = ChartOfAccount::where('code', $glAccount->code)->first();
        
        if (!$chartAccount && $glAccount->category) {
            // Create new Chart of Account based on GL Account
            $accountType = $glAccount->getAccountType();
            $accountCategory = $glAccount->getAccountCategory();

            if ($accountType) {
                $chartAccount = ChartOfAccount::create([
                    'code' => $glAccount->code,
                    'name' => $glAccount->name,
                    'type' => $accountType,
                    'category' => $accountCategory,
                    'is_active' => $glAccount->is_active,
                    'created_by' => Auth::id(),
                ]);

                // Link GL Account to Chart of Account
                $glAccount->update(['chart_of_account_id' => $chartAccount->id]);
            }
        } elseif ($chartAccount) {
            // Link existing Chart of Account
            $glAccount->update(['chart_of_account_id' => $chartAccount->id]);
        }

        return $chartAccount;
    }

    /**
     * Sync Cash Box to Chart of Accounts
     */
    private function syncCashBoxToChartOfAccounts(CashBox $cashBox): ?ChartOfAccount
    {
        // If already synced, return existing
        if ($cashBox->chart_of_account_id) {
            return $cashBox->chartOfAccount;
        }

        // Check if Chart of Account already exists for this cash box
        $code = 'CASH-' . strtoupper($cashBox->currency ?? 'TZS') . '-' . strtoupper(str_replace(' ', '-', $cashBox->name));
        $chartAccount = ChartOfAccount::where('code', $code)
            ->orWhere('name', 'like', '%' . $cashBox->name . '%')
            ->where('type', 'Asset')
            ->first();

        if (!$chartAccount) {
            // Create new Chart of Account for Cash Box (always an Asset)
            $chartAccount = ChartOfAccount::create([
                'code' => $code,
                'name' => 'Cash - ' . $cashBox->name,
                'type' => 'Asset',
                'category' => 'Current Asset',
                'opening_balance' => $cashBox->current_balance ?? 0,
                'is_active' => $cashBox->is_active,
                'created_by' => Auth::id(),
            ]);

            // Link Cash Box to Chart of Account
            $cashBox->update(['chart_of_account_id' => $chartAccount->id]);
        } else {
            // Link existing Chart of Account
            $cashBox->update(['chart_of_account_id' => $chartAccount->id]);
        }

        return $chartAccount;
    }

    public function storeGl(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:gl_accounts,code',
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $gl = GlAccount::create($data);
            
            // Auto-sync to Chart of Accounts
            $this->syncGlAccountToChartOfAccounts($gl);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'gl' => $gl->load('chartOfAccount'),
                'message' => 'GL Account created and synced to Chart of Accounts'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating GL Account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateGl(Request $request, GlAccount $gl)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:gl_accounts,code,' . $gl->id,
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $gl->update($data);
            
            // Sync to Chart of Accounts (will update if exists, create if not)
            $chartAccount = $this->syncGlAccountToChartOfAccounts($gl);
            
            // Update Chart of Account if it exists
            if ($chartAccount) {
                $accountType = $gl->getAccountType();
                $accountCategory = $gl->getAccountCategory();
                
                $chartAccount->update([
                    'code' => $gl->code,
                    'name' => $gl->name,
                    'type' => $accountType ?? $chartAccount->type,
                    'category' => $accountCategory ?? $chartAccount->category,
                    'is_active' => $gl->is_active,
                    'updated_by' => Auth::id(),
                ]);
            }
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'GL Account updated and synced to Chart of Accounts'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating GL Account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyGl(GlAccount $gl)
    {
        DB::beginTransaction();
        try {
            // Don't delete Chart of Account if it has transactions
            if ($gl->chartOfAccount) {
                $chartAccount = $gl->chartOfAccount;
                if ($chartAccount->ledgerEntries()->count() > 0 || $chartAccount->journalLines()->count() > 0) {
                    // Just unlink, don't delete Chart of Account
                    $gl->update(['chart_of_account_id' => null]);
                } else {
                    // Safe to delete Chart of Account
                    $chartAccount->delete();
                }
            }
            
            $gl->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting GL Account: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeCashBox(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:cash_boxes,name',
            'currency' => 'nullable|string|max:8',
            'current_balance' => 'nullable|numeric',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $cb = CashBox::create($data);
            
            // Auto-sync to Chart of Accounts
            $this->syncCashBoxToChartOfAccounts($cb);
            
            DB::commit();
            return response()->json([
                'success' => true,
                'cashBox' => $cb->load('chartOfAccount'),
                'message' => 'Cash Box created and synced to Chart of Accounts'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating Cash Box: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateCashBox(Request $request, CashBox $cashBox)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:cash_boxes,name,' . $cashBox->id,
            'currency' => 'nullable|string|max:8',
            'current_balance' => 'nullable|numeric',
            'is_active' => 'boolean'
        ]);

        DB::beginTransaction();
        try {
            $cashBox->update($data);
            
            // Sync to Chart of Accounts
            $chartAccount = $this->syncCashBoxToChartOfAccounts($cashBox);
            
            // Update Chart of Account if it exists
            if ($chartAccount) {
                $chartAccount->update([
                    'name' => 'Cash - ' . $cashBox->name,
                    'opening_balance' => $cashBox->current_balance ?? 0,
                    'is_active' => $cashBox->is_active,
                    'updated_by' => Auth::id(),
                ]);
            }
            
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Cash Box updated and synced to Chart of Accounts'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error updating Cash Box: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyCashBox(CashBox $cashBox)
    {
        DB::beginTransaction();
        try {
            // Don't delete Chart of Account if it has transactions
            if ($cashBox->chartOfAccount) {
                $chartAccount = $cashBox->chartOfAccount;
                if ($chartAccount->ledgerEntries()->count() > 0 || $chartAccount->journalLines()->count() > 0) {
                    // Just unlink, don't delete Chart of Account
                    $cashBox->update(['chart_of_account_id' => null]);
                } else {
                    // Safe to delete Chart of Account
                    $chartAccount->delete();
                }
            }
            
            $cashBox->delete();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting Cash Box: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all existing GL Accounts and Cash Boxes to Chart of Accounts
     */
    public function syncAll(Request $request)
    {
        DB::beginTransaction();
        try {
            $syncedGl = 0;
            $syncedCb = 0;

            // Sync all GL Accounts
            $glAccounts = GlAccount::whereNull('chart_of_account_id')->get();
            foreach ($glAccounts as $gl) {
                if ($this->syncGlAccountToChartOfAccounts($gl)) {
                    $syncedGl++;
                }
            }

            // Sync all Cash Boxes
            $cashBoxes = CashBox::whereNull('chart_of_account_id')->get();
            foreach ($cashBoxes as $cb) {
                if ($this->syncCashBoxToChartOfAccounts($cb)) {
                    $syncedCb++;
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Synced {$syncedGl} GL Accounts and {$syncedCb} Cash Boxes to Chart of Accounts"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error syncing: ' . $e->getMessage()
            ], 500);
        }
    }
}








