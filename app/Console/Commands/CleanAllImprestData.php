<?php

namespace App\Console\Commands;

use App\Models\ImprestRequest;
use App\Models\ImprestAssignment;
use App\Models\ImprestReceipt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanAllImprestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imprest:clean-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all imprest data from the system (requests, assignments, receipts, and approval history)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $requestsCount = ImprestRequest::count();
        $assignmentsCount = ImprestAssignment::count();
        $receiptsCount = ImprestReceipt::count();
        
        // Check approval history table if it exists
        $historyCount = 0;
        if (Schema::hasTable('imprest_approval_history')) {
            $historyCount = DB::table('imprest_approval_history')->count();
        }

        $totalCount = $requestsCount + $assignmentsCount + $receiptsCount + $historyCount;

        if ($totalCount === 0) {
            $this->info('No imprest data found in the database.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->info("Found imprest data:");
            $this->line("  - Requests: {$requestsCount}");
            $this->line("  - Assignments: {$assignmentsCount}");
            $this->line("  - Receipts: {$receiptsCount}");
            if ($historyCount > 0) {
                $this->line("  - Approval History: {$historyCount}");
            }
            $this->line("  - Total: {$totalCount}");
            
            if (!$this->confirm("Are you sure you want to delete all imprest data? This action cannot be undone.")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info("Deleting all imprest data...");

        try {
            DB::beginTransaction();

            // Delete in order to respect foreign key constraints
            // 1. Delete receipts first (depends on assignments)
            $deletedReceipts = ImprestReceipt::query()->delete();
            $this->info("Deleted {$deletedReceipts} receipt(s).");

            // 2. Delete assignments (depends on requests)
            $deletedAssignments = ImprestAssignment::query()->delete();
            $this->info("Deleted {$deletedAssignments} assignment(s).");

            // 3. Delete approval history (depends on requests)
            if (Schema::hasTable('imprest_approval_history')) {
                $deletedHistory = DB::table('imprest_approval_history')->delete();
                $this->info("Deleted {$deletedHistory} approval history record(s).");
            }

            // 4. Delete requests (main table)
            $deletedRequests = ImprestRequest::query()->delete();
            $this->info("Deleted {$deletedRequests} request(s).");

            DB::commit();

            $this->info("Successfully deleted all imprest data.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to delete imprest data: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}






