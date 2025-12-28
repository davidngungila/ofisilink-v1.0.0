<?php

namespace App\Console\Commands;

use App\Models\ChartOfAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanAllChartOfAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart-of-accounts:clean-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all chart of accounts data from the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $accountsCount = ChartOfAccount::count();

        if ($accountsCount === 0) {
            $this->info('No chart of accounts found in the database.');
            return Command::SUCCESS;
        }

        // Check for related records
        $ledgerEntriesCount = DB::table('general_ledger')->whereNotNull('account_id')->count();
        $journalLinesCount = DB::table('journal_entry_lines')->whereNotNull('account_id')->count();
        $bankAccountsCount = 0;
        if (Schema::hasTable('bank_accounts') && Schema::hasColumn('bank_accounts', 'account_id')) {
            $bankAccountsCount = DB::table('bank_accounts')->whereNotNull('account_id')->count();
        }

        if (!$this->option('force')) {
            $this->info("Found chart of accounts data:");
            $this->line("  - Accounts: {$accountsCount}");
            if ($ledgerEntriesCount > 0) {
                $this->warn("  - General Ledger Entries: {$ledgerEntriesCount} (may prevent deletion)");
            }
            if ($journalLinesCount > 0) {
                $this->warn("  - Journal Entry Lines: {$journalLinesCount} (may prevent deletion)");
            }
            if ($bankAccountsCount > 0) {
                $this->warn("  - Bank Accounts: {$bankAccountsCount} (may prevent deletion)");
            }
            
            if (!$this->confirm("Are you sure you want to delete all chart of accounts? This action cannot be undone.")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info("Deleting all chart of accounts...");

        try {
            DB::beginTransaction();

            // Step 1: Break parent-child relationships by setting parent_id to null
            $this->info("Breaking parent-child relationships...");
            DB::table('chart_of_accounts')->update(['parent_id' => null]);
            
            // Step 2: Temporarily disable foreign key checks
            if (config('database.default') === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            // Step 3: Delete all chart of accounts
            $deleted = ChartOfAccount::query()->delete();
            $this->info("Deleted {$deleted} chart of account(s).");

            // Step 4: Re-enable foreign key checks
            if (config('database.default') === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }

            DB::commit();

            $this->info("Successfully deleted all chart of accounts data.");
            
            if ($ledgerEntriesCount > 0 || $journalLinesCount > 0 || $bankAccountsCount > 0) {
                $this->warn("Note: Related records in general_ledger, journal_entry_lines, or bank_accounts may have orphaned account_id references.");
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            if (config('database.default') === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
            DB::rollBack();
            $this->error("Failed to delete chart of accounts: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

