<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanDatabaseExceptSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clean-except-system {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean entire database except System Settings, Users, Roles, Permissions, Organization Settings, System Health, and Employees';

    /**
     * Tables to preserve (protected tables)
     */
    protected $protectedTables = [
        // Users and Authentication
        'users',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
        
        // Roles and Permissions
        'roles',
        'permissions',
        'role_permissions',
        'user_roles',
        
        // Organization Settings
        'organization_settings',
        
        // System Settings
        'system_settings',
        'settings',
        
        // Employees
        'employees',
        'departments',
        'user_departments',
        
        // System Health / Logs (if needed)
        'database_backups',
        
        // Laravel Framework Tables
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Database Cleanup (Preserving System Data) ===');
        $this->newLine();

        // Get all tables
        $allTables = $this->getAllTables();
        $tablesToClean = array_diff($allTables, $this->protectedTables);
        
        // Sort tables for better display
        sort($tablesToClean);
        sort($this->protectedTables);

        // Display protected tables
        $this->info('Protected Tables (will NOT be cleaned):');
        foreach ($this->protectedTables as $table) {
            if (in_array($table, $allTables)) {
                $count = DB::table($table)->count();
                $this->line("  ✓ {$table}: {$count} records");
            }
        }

        $this->newLine();
        $this->warn('Tables to be cleaned:');
        
        // Count records in tables to clean
        $totalRecords = 0;
        $tableCounts = [];
        
        foreach ($tablesToClean as $table) {
            try {
                $count = DB::table($table)->count();
                if ($count > 0) {
                    $tableCounts[$table] = $count;
                    $totalRecords += $count;
                    $this->line("  - {$table}: {$count} records");
                }
            } catch (\Exception $e) {
                $this->warn("  ⚠ {$table}: Could not count records - {$e->getMessage()}");
            }
        }

        if (empty($tableCounts)) {
            $this->info('No records found to clean.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $this->error("Total records to delete: {$totalRecords} from " . count($tableCounts) . " tables");

        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  WARNING: This will delete ALL data from the above tables. This action CANNOT be undone. Are you sure?')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('Starting cleanup...');
        $this->newLine();

        try {
            DB::beginTransaction();

            $deletedCounts = [];
            $errors = [];

            foreach ($tableCounts as $table => $count) {
                try {
                    // Disable foreign key checks temporarily for this table
                    DB::statement('SET FOREIGN_KEY_CHECKS=0');
                    
                    $deleted = DB::table($table)->delete();
                    $deletedCounts[$table] = $deleted;
                    
                    // Re-enable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    
                    $this->info("  ✓ {$table}: {$deleted} records deleted");
                } catch (\Exception $e) {
                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                    $errors[] = "{$table}: {$e->getMessage()}";
                    $this->error("  ✗ {$table}: Failed - {$e->getMessage()}");
                }
            }

            DB::commit();

            $this->newLine();
            $this->info('=== Cleanup Complete ===');
            $totalDeleted = array_sum($deletedCounts);
            $this->info("Total records deleted: {$totalDeleted}");

            if (!empty($errors)) {
                $this->newLine();
                $this->warn('Some tables had errors:');
                foreach ($errors as $error) {
                    $this->line("  - {$error}");
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->error('Failed to clean database: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Get all tables in the database
     */
    private function getAllTables(): array
    {
        $database = DB::connection()->getDatabaseName();
        $tables = DB::select("SHOW TABLES");
        
        $tableList = [];
        $key = "Tables_in_{$database}";
        
        foreach ($tables as $table) {
            $tableList[] = $table->$key;
        }
        
        return $tableList;
    }
}

