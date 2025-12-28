<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanAccountingModuleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning all Accounting Module data...');
        
        // Disable foreign key checks for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        $deletedCounts = [];
        
        // 1. Delete General Ledger entries first (depends on accounts, journals, etc.)
        $this->command->info('Deleting General Ledger entries...');
        $deletedCounts['general_ledger'] = DB::table('general_ledger')->count();
        DB::table('general_ledger')->delete();
        
        // 2. Delete Journal Entry Lines (depends on Journal Entries)
        $this->command->info('Deleting Journal Entry Lines...');
        $deletedCounts['journal_entry_lines'] = DB::table('journal_entry_lines')->count();
        DB::table('journal_entry_lines')->delete();
        
        // 3. Delete Journal Entries
        $this->command->info('Deleting Journal Entries...');
        $deletedCounts['journal_entries'] = DB::table('journal_entries')->count();
        DB::table('journal_entries')->delete();
        
        // 4. Delete Invoice Payments (depends on Invoices)
        $this->command->info('Deleting Invoice Payments...');
        $deletedCounts['invoice_payments'] = DB::table('invoice_payments')->count();
        DB::table('invoice_payments')->delete();
        
        // 5. Delete Invoice Items (depends on Invoices)
        $this->command->info('Deleting Invoice Items...');
        $deletedCounts['invoice_items'] = DB::table('invoice_items')->count();
        DB::table('invoice_items')->delete();
        
        // 6. Delete Invoices
        $this->command->info('Deleting Invoices...');
        $deletedCounts['invoices'] = DB::table('invoices')->count();
        DB::table('invoices')->delete();
        
        // 7. Delete Credit Memo Items (depends on Credit Memos)
        $this->command->info('Deleting Credit Memo Items...');
        if (DB::getSchemaBuilder()->hasTable('credit_memo_items')) {
            $deletedCounts['credit_memo_items'] = DB::table('credit_memo_items')->count();
            DB::table('credit_memo_items')->delete();
        }
        
        // 8. Delete Credit Memos (if table exists)
        $this->command->info('Deleting Credit Memos...');
        if (DB::getSchemaBuilder()->hasTable('credit_memos')) {
            $deletedCounts['credit_memos'] = DB::table('credit_memos')->count();
            DB::table('credit_memos')->delete();
        }
        
        // 9. Delete Customers
        $this->command->info('Deleting Customers...');
        $deletedCounts['customers'] = DB::table('customers')->count();
        DB::table('customers')->delete();
        
        // 10. Delete Bill Payments (depends on Bills)
        $this->command->info('Deleting Bill Payments...');
        $deletedCounts['bill_payments'] = DB::table('bill_payments')->count();
        DB::table('bill_payments')->delete();
        
        // 11. Delete Bill Items (depends on Bills)
        $this->command->info('Deleting Bill Items...');
        $deletedCounts['bill_items'] = DB::table('bill_items')->count();
        DB::table('bill_items')->delete();
        
        // 12. Delete Bills
        $this->command->info('Deleting Bills...');
        $deletedCounts['bills'] = DB::table('bills')->count();
        DB::table('bills')->delete();
        
        // 13. Delete Vendors
        $this->command->info('Deleting Vendors...');
        $deletedCounts['vendors'] = DB::table('vendors')->count();
        DB::table('vendors')->delete();
        
        // 14. Delete Budget Items (depends on Budgets)
        $this->command->info('Deleting Budget Items...');
        $deletedCounts['budget_items'] = DB::table('budget_items')->count();
        DB::table('budget_items')->delete();
        
        // 15. Delete Budgets
        $this->command->info('Deleting Budgets...');
        $deletedCounts['budgets'] = DB::table('budgets')->count();
        DB::table('budgets')->delete();
        
        // 16. Delete Fixed Asset Depreciations (depends on Fixed Assets)
        $this->command->info('Deleting Fixed Asset Depreciations...');
        if (DB::getSchemaBuilder()->hasTable('fixed_asset_depreciations')) {
            $deletedCounts['fixed_asset_depreciations'] = DB::table('fixed_asset_depreciations')->count();
            DB::table('fixed_asset_depreciations')->delete();
        }
        
        // 17. Delete Fixed Asset Maintenances (depends on Fixed Assets)
        $this->command->info('Deleting Fixed Asset Maintenances...');
        if (DB::getSchemaBuilder()->hasTable('fixed_asset_maintenances')) {
            $deletedCounts['fixed_asset_maintenances'] = DB::table('fixed_asset_maintenances')->count();
            DB::table('fixed_asset_maintenances')->delete();
        }
        
        // 18. Delete Fixed Asset Disposals (depends on Fixed Assets)
        $this->command->info('Deleting Fixed Asset Disposals...');
        if (DB::getSchemaBuilder()->hasTable('fixed_asset_disposals')) {
            $deletedCounts['fixed_asset_disposals'] = DB::table('fixed_asset_disposals')->count();
            DB::table('fixed_asset_disposals')->delete();
        }
        
        // 19. Delete Fixed Assets
        $this->command->info('Deleting Fixed Assets...');
        if (DB::getSchemaBuilder()->hasTable('fixed_assets')) {
            $deletedCounts['fixed_assets'] = DB::table('fixed_assets')->count();
            DB::table('fixed_assets')->delete();
        }
        
        // 20. Delete Fixed Asset Categories
        $this->command->info('Deleting Fixed Asset Categories...');
        if (DB::getSchemaBuilder()->hasTable('fixed_asset_categories')) {
            $count = DB::table('fixed_asset_categories')->count();
            if (DB::getSchemaBuilder()->hasColumn('fixed_asset_categories', 'is_system')) {
                $deletedCounts['fixed_asset_categories'] = DB::table('fixed_asset_categories')
                    ->where('is_system', false)
                    ->count();
                DB::table('fixed_asset_categories')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['fixed_asset_categories'] = $count;
                DB::table('fixed_asset_categories')->delete();
            }
        }
        
        // 21. Delete Bank Accounts
        $this->command->info('Deleting Bank Accounts...');
        if (DB::getSchemaBuilder()->hasTable('bank_accounts')) {
            $deletedCounts['bank_accounts'] = DB::table('bank_accounts')->count();
            DB::table('bank_accounts')->delete();
        }
        
        // 22. Delete Bank Reconciliations (if table exists)
        $this->command->info('Deleting Bank Reconciliations...');
        if (DB::getSchemaBuilder()->hasTable('bank_reconciliations')) {
            $deletedCounts['bank_reconciliations'] = DB::table('bank_reconciliations')->count();
            DB::table('bank_reconciliations')->delete();
        }
        
        // 23. Delete Tax Settings
        $this->command->info('Deleting Tax Settings...');
        if (DB::getSchemaBuilder()->hasTable('tax_settings')) {
            if (DB::getSchemaBuilder()->hasColumn('tax_settings', 'is_system')) {
                $deletedCounts['tax_settings'] = DB::table('tax_settings')
                    ->where('is_system', false)
                    ->count();
                DB::table('tax_settings')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['tax_settings'] = DB::table('tax_settings')->count();
                DB::table('tax_settings')->delete();
            }
        }
        
        // 24. Delete Accounting Audit Logs
        $this->command->info('Deleting Accounting Audit Logs...');
        if (DB::getSchemaBuilder()->hasTable('accounting_audit_logs')) {
            $deletedCounts['accounting_audit_logs'] = DB::table('accounting_audit_logs')->count();
            DB::table('accounting_audit_logs')->delete();
        }
        
        // 25. Delete Chart of Accounts (only non-system ones)
        $this->command->info('Deleting Chart of Accounts (non-system)...');
        if (DB::getSchemaBuilder()->hasTable('chart_of_accounts')) {
            if (DB::getSchemaBuilder()->hasColumn('chart_of_accounts', 'is_system')) {
                $deletedCounts['chart_of_accounts'] = DB::table('chart_of_accounts')
                    ->where('is_system', false)
                    ->count();
                DB::table('chart_of_accounts')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['chart_of_accounts'] = DB::table('chart_of_accounts')->count();
                DB::table('chart_of_accounts')->delete();
            }
        }
        
        // 26. Delete GL Accounts (if separate table exists)
        $this->command->info('Deleting GL Accounts...');
        if (DB::getSchemaBuilder()->hasTable('gl_accounts')) {
            if (DB::getSchemaBuilder()->hasColumn('gl_accounts', 'is_system')) {
                $deletedCounts['gl_accounts'] = DB::table('gl_accounts')
                    ->where('is_system', false)
                    ->count();
                DB::table('gl_accounts')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['gl_accounts'] = DB::table('gl_accounts')->count();
                DB::table('gl_accounts')->delete();
            }
        }
        
        // Re-enable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        $this->command->info("\n==========================================");
        $this->command->info("Successfully cleaned all Accounting Module data!");
        $this->command->info("==========================================");
        $this->command->info("\nDeleted Records:");
        foreach ($deletedCounts as $table => $count) {
            $this->command->info("  - {$table}: {$count}");
        }
        $totalDeleted = array_sum($deletedCounts);
        $this->command->info("\nTotal records deleted: {$totalDeleted}");
        $this->command->info("\n");
    }
}

