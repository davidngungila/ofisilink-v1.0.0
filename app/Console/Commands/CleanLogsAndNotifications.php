<?php

namespace App\Console\Commands;

use App\Models\AccountingAuditLog;
use App\Models\ActivityLog;
use App\Models\ApplicationHistory;
use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanLogsAndNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean 
                            {--tables=* : Specific tables to clean (accounting_audit_logs, notifications, activity_logs, application_history)}
                            {--all : Clean all tables}
                            {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean accounting_audit_logs, notifications, activity_logs, and application_history tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tablesToClean = [];
        
        if ($this->option('all')) {
            $tablesToClean = ['accounting_audit_logs', 'notifications', 'activity_logs', 'application_history'];
        } elseif ($this->option('tables')) {
            $tablesToClean = $this->option('tables');
        } else {
            // Default: clean all if no options specified
            $tablesToClean = ['accounting_audit_logs', 'notifications', 'activity_logs', 'application_history'];
        }

        // Validate table names
        $validTables = ['accounting_audit_logs', 'notifications', 'activity_logs', 'application_history'];
        $invalidTables = array_diff($tablesToClean, $validTables);
        
        if (!empty($invalidTables)) {
            $this->error('Invalid table(s): ' . implode(', ', $invalidTables));
            $this->info('Valid tables are: ' . implode(', ', $validTables));
            return Command::FAILURE;
        }

        // Get counts for each table
        $counts = [];
        foreach ($tablesToClean as $table) {
            $counts[$table] = $this->getTableCount($table);
        }

        // Display summary
        $this->info('=== Logs and Notifications Cleanup ===');
        $this->newLine();
        
        $totalRecords = 0;
        foreach ($counts as $table => $count) {
            $this->line("  {$table}: {$count} records");
            $totalRecords += $count;
        }
        
        $this->newLine();
        $this->info("Total records to delete: {$totalRecords}");

        if ($totalRecords === 0) {
            $this->info('No records found to delete.');
            return Command::SUCCESS;
        }

        // Confirm deletion
        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete all these records? This action cannot be undone.')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        // Perform deletion
        $this->newLine();
        $this->info('Starting cleanup...');
        $this->newLine();

        try {
            DB::beginTransaction();

            $deletedCounts = [];
            
            foreach ($tablesToClean as $table) {
                $deleted = $this->cleanTable($table);
                $deletedCounts[$table] = $deleted;
                $this->info("  ✓ {$table}: {$deleted} records deleted");
            }

            DB::commit();

            $this->newLine();
            $this->info('=== Cleanup Complete ===');
            $totalDeleted = array_sum($deletedCounts);
            $this->info("Total records deleted: {$totalDeleted}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to clean tables: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Get record count for a table
     */
    private function getTableCount(string $table): int
    {
        try {
            switch ($table) {
                case 'accounting_audit_logs':
                    return AccountingAuditLog::count();
                case 'notifications':
                    return Notification::count();
                case 'activity_logs':
                    return ActivityLog::count();
                case 'application_history':
                    return ApplicationHistory::count();
                default:
                    return 0;
            }
        } catch (\Exception $e) {
            $this->warn("Could not count records in {$table}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clean a specific table
     */
    private function cleanTable(string $table): int
    {
        try {
            switch ($table) {
                case 'accounting_audit_logs':
                    return AccountingAuditLog::query()->delete();
                case 'notifications':
                    return Notification::query()->delete();
                case 'activity_logs':
                    return ActivityLog::query()->delete();
                case 'application_history':
                    return ApplicationHistory::query()->delete();
                default:
                    return 0;
            }
        } catch (\Exception $e) {
            $this->error("Failed to clean {$table}: " . $e->getMessage());
            throw $e;
        }
    }
}

