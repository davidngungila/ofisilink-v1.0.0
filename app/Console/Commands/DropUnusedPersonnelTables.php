<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DropUnusedPersonnelTables extends Command
{
    protected $signature = 'db:drop-unused-personnel {--force : Force execution without confirmation}';
    protected $description = 'Drop unused personnel management system tables';

    public function handle()
    {
        $tables = [
            'personnel_area',
            'personnel_assignareaemployee',
            'personnel_certification',
            'personnel_company',
            'personnel_department',
            'personnel_employee',
            'personnel_employeecalendar',
            'personnel_employeecertification',
            'personnel_employeecustomattribute',
            'personnel_employeeextrainfo',
            'personnel_employeeprofile',
            'personnel_employee_area',
            'personnel_employee_flow_role',
            'personnel_employment',
            'personnel_position',
            'personnel_resign',
        ];

        $this->warn("⚠️  WARNING: This will drop " . count($tables) . " unused personnel management tables!");
        $this->newLine();
        $this->info("Tables to be dropped:");
        foreach ($tables as $table) {
            $this->line("  - {$table}");
        }
        $this->newLine();
        $this->info("Note: These tables are from a legacy personnel system.");
        $this->info("Your system uses 'employees' and 'users' tables instead.");
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to proceed? This action cannot be undone!')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            $this->info('Dropping unused personnel tables...');
            
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            
            $dropped = 0;
            $errors = 0;
            $notFound = 0;
            
            foreach ($tables as $table) {
                try {
                    if (DB::getSchemaBuilder()->hasTable($table)) {
                        DB::statement("DROP TABLE IF EXISTS `{$table}`;");
                        $dropped++;
                        if ($this->getOutput()->isVerbose()) {
                            $this->line("  ✓ Dropped: {$table}");
                        }
                    } else {
                        $notFound++;
                        if ($this->getOutput()->isVerbose()) {
                            $this->line("  - Skipped: {$table} (does not exist)");
                        }
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->warn("  ✗ Failed to drop {$table}: " . $e->getMessage());
                }
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            
            $this->newLine();
            $this->info("✅ Operation completed!");
            $this->info("   Tables dropped: {$dropped}");
            if ($notFound > 0) {
                $this->line("   Tables not found: {$notFound}");
            }
            if ($errors > 0) {
                $this->warn("   Errors: {$errors}");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            return 1;
        }
    }
}

