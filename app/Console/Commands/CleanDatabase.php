<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanDatabase extends Command
{
    protected $signature = 'db:clean {--force : Force execution without confirmation}';
    protected $description = 'Clean database data while preserving users, employees, and system settings';

    protected $tablesToKeep = [
        // Laravel System Tables
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
        'password_reset_tokens',
        
        // User & Employee Data
        'users',
        'employees',
        'employee_educations',
        'employee_family',
        'employee_next_of_kin',
        'employee_referees',
        'employee_documents',
        'employee_skills',
        'employee_work_history',
        'employee_salary_deductions',
        'employee_training',
        'employee_performance',
        'employee_overtimes',
        'employee_bonuses',
        'employee_allowances',
        
        // Role & Permission System
        'roles',
        'permissions',
        'role_permissions',
        'user_roles',
        'user_departments',
        
        // Department & Organization
        'departments',
        'positions',
        
        // System Settings
        'system_settings',
        'organization_settings',
        'database_backups',
        'notification_providers',
        
        // Finance Settings (GL Accounts, Cash Boxes, Chart of Accounts)
        'gl_accounts',
        'cash_boxes',
        'chart_of_accounts',
        'tax_settings',
        
        // Leave Types (Configuration)
        'leave_types',
        
        // Asset Categories (Configuration)
        'asset_categories',
        'fixed_asset_categories',
        
        // Task Categories (Configuration)
        'task_categories',
        
        // Attendance Configuration
        'attendance_locations',
        'attendance_devices',
        'attendance_policies',
        'work_schedules',
        
        // Bank Accounts (Configuration)
        'bank_accounts',
    ];

    public function handle()
    {
        // Get all tables in the database
        $allTables = $this->getAllTables();
        
        // Filter tables to clean (exclude tables to keep)
        $tablesToClean = array_filter($allTables, function($table) {
            return !in_array($table, $this->tablesToKeep);
        });
        
        $tablesToClean = array_values($tablesToClean);
        
        if (empty($tablesToClean)) {
            $this->info('No tables to clean. All tables are protected.');
            return 0;
        }
        
        $this->warn("⚠️  WARNING: This will DELETE ALL DATA from " . count($tablesToClean) . " tables!");
        $this->newLine();
        $this->info("Tables that will be CLEANED (data deleted, structure kept):");
        foreach ($tablesToClean as $table) {
            $count = DB::table($table)->count();
            $this->line("  - {$table} ({$count} records)");
        }
        $this->newLine();
        $this->info("Tables that will be PRESERVED:");
        foreach ($this->tablesToKeep as $table) {
            if (in_array($table, $allTables)) {
                $count = DB::table($table)->count();
                $this->line("  ✓ {$table} ({$count} records)");
            }
        }
        $this->newLine();
        $this->error("⚠️  THIS ACTION CANNOT BE UNDONE!");
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you absolutely sure you want to proceed?', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
            
            // Double confirmation
            if (!$this->confirm('This will delete ALL data from the listed tables. Type YES to confirm:', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            $this->info('Starting database cleanup...');
            $this->newLine();
            
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            
            $cleaned = 0;
            $errors = 0;
            $totalRecords = 0;
            
            $progressBar = $this->output->createProgressBar(count($tablesToClean));
            $progressBar->start();
            
            foreach ($tablesToClean as $table) {
                try {
                    $count = DB::table($table)->count();
                    $totalRecords += $count;
                    
                    // Use TRUNCATE for better performance, but handle foreign key constraints
                    if ($count > 0) {
                        DB::table($table)->truncate();
                    }
                    
                    $cleaned++;
                    $progressBar->advance();
                } catch (\Exception $e) {
                    // If TRUNCATE fails (due to foreign keys), try DELETE
                    try {
                        DB::table($table)->delete();
                        $cleaned++;
                        $progressBar->advance();
                    } catch (\Exception $e2) {
                        $errors++;
                        $this->newLine();
                        $this->warn("  ✗ Failed to clean {$table}: " . $e2->getMessage());
                        $progressBar->advance();
                    }
                }
            }
            
            $progressBar->finish();
            $this->newLine(2);
            
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            
            $this->info("✅ Database cleanup completed!");
            $this->info("   Tables cleaned: {$cleaned}");
            $this->info("   Total records deleted: " . number_format($totalRecords));
            if ($errors > 0) {
                $this->warn("   Errors: {$errors}");
            }
            $this->newLine();
            $this->info("✓ User accounts preserved");
            $this->info("✓ Employee data preserved");
            $this->info("✓ System settings preserved");
            $this->info("✓ Role & permission system preserved");
            $this->info("✓ Configuration tables preserved");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            return 1;
        }
    }

    protected function getAllTables(): array
    {
        $databaseName = DB::connection()->getDatabaseName();
        $tables = DB::select("SHOW TABLES");
        $tableKey = "Tables_in_{$databaseName}";
        
        return array_map(function($table) use ($tableKey) {
            return $table->$tableKey;
        }, $tables);
    }
}

