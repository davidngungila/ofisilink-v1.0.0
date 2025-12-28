<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DropUnusedVisitorWorkflowTables extends Command
{
    protected $signature = 'db:drop-unused-visitor-workflow {--force : Force execution without confirmation}';
    protected $description = 'Drop unused visitor management and workflow engine tables';

    public function handle()
    {
        $tables = [
            // Visitor Management System Tables
            'visitor_reason',
            'visitor_reservation',
            'visitor_visitor',
            'visitor_visitorbiodata',
            'visitor_visitorbiophoto',
            'visitor_visitorconfig',
            'visitor_visitorlog',
            'visitor_visitortransaction',
            'visitor_visitor_acc_groups',
            'visitor_visitor_area',
            
            // Workflow Engine System Tables
            'workflow_nodeinstance',
            'workflow_workflowengine',
            'workflow_workflowengine_employee',
            'workflow_workflowinstance',
            'workflow_workflownode',
            'workflow_workflownode_approver',
            'workflow_workflownode_notifier',
            'workflow_workflowrole',
        ];

        $this->warn("⚠️  WARNING: This will drop " . count($tables) . " unused tables!");
        $this->newLine();
        $this->info("Tables to be dropped:");
        foreach ($tables as $table) {
            $this->line("  - {$table}");
        }
        $this->newLine();
        $this->info("Note: work_schedules table will be KEPT (it's actively used)");
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to proceed? This action cannot be undone!')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            $this->info('Dropping unused tables...');
            
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            
            $dropped = 0;
            $errors = 0;
            
            foreach ($tables as $table) {
                try {
                    if (DB::getSchemaBuilder()->hasTable($table)) {
                        DB::statement("DROP TABLE IF EXISTS `{$table}`;");
                        $dropped++;
                        if ($this->getOutput()->isVerbose()) {
                            $this->line("  ✓ Dropped: {$table}");
                        }
                    } else {
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

