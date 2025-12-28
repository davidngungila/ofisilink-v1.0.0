<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanTasksAndIncidentsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning all Task Management and Incident Management data...');
        
        // Disable foreign key checks for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        $deletedCounts = [];
        
        // ============================================
        // TASK MANAGEMENT CLEANUP
        // ============================================
        $this->command->info("\n--- Task Management Cleanup ---");
        
        // 1. Delete Task Activities (depends on Tasks)
        $this->command->info('Deleting Task Activities...');
        if (DB::getSchemaBuilder()->hasTable('task_activities')) {
            $deletedCounts['task_activities'] = DB::table('task_activities')->count();
            DB::table('task_activities')->delete();
        }
        
        // 2. Delete Task Attachments (depends on Tasks)
        $this->command->info('Deleting Task Attachments...');
        if (DB::getSchemaBuilder()->hasTable('task_attachments')) {
            $deletedCounts['task_attachments'] = DB::table('task_attachments')->count();
            DB::table('task_attachments')->delete();
        }
        
        // 3. Delete Task Comments (depends on Tasks)
        $this->command->info('Deleting Task Comments...');
        if (DB::getSchemaBuilder()->hasTable('task_comments')) {
            $deletedCounts['task_comments'] = DB::table('task_comments')->count();
            DB::table('task_comments')->delete();
        }
        
        // 4. Delete Sub Tasks/Responsibilities (depends on Main Tasks)
        $this->command->info('Deleting Sub Tasks/Responsibilities...');
        if (DB::getSchemaBuilder()->hasTable('sub_responsibilities')) {
            $deletedCounts['sub_responsibilities'] = DB::table('sub_responsibilities')->count();
            DB::table('sub_responsibilities')->delete();
        }
        
        // 5. Delete Main Tasks
        $this->command->info('Deleting Main Tasks...');
        if (DB::getSchemaBuilder()->hasTable('main_tasks')) {
            $deletedCounts['main_tasks'] = DB::table('main_tasks')->count();
            DB::table('main_tasks')->delete();
        }
        
        // 6. Delete Task Categories (only non-system ones)
        $this->command->info('Deleting Task Categories (non-system)...');
        if (DB::getSchemaBuilder()->hasTable('task_categories')) {
            if (DB::getSchemaBuilder()->hasColumn('task_categories', 'is_system')) {
                $deletedCounts['task_categories'] = DB::table('task_categories')
                    ->where('is_system', false)
                    ->count();
                DB::table('task_categories')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['task_categories'] = DB::table('task_categories')->count();
                DB::table('task_categories')->delete();
            }
        }
        
        // ============================================
        // INCIDENT MANAGEMENT CLEANUP
        // ============================================
        $this->command->info("\n--- Incident Management Cleanup ---");
        
        // 7. Delete Incident Updates/Comments (depends on Incidents)
        $this->command->info('Deleting Incident Updates...');
        if (DB::getSchemaBuilder()->hasTable('incident_updates')) {
            $deletedCounts['incident_updates'] = DB::table('incident_updates')->count();
            DB::table('incident_updates')->delete();
        }
        
        // 8. Delete Incident Inbox (depends on Incidents)
        $this->command->info('Deleting Incident Inbox...');
        if (DB::getSchemaBuilder()->hasTable('incident_inbox')) {
            $deletedCounts['incident_inbox'] = DB::table('incident_inbox')->count();
            DB::table('incident_inbox')->delete();
        }
        
        // 9. Delete Incidents
        $this->command->info('Deleting Incidents...');
        if (DB::getSchemaBuilder()->hasTable('incidents')) {
            $deletedCounts['incidents'] = DB::table('incidents')->count();
            DB::table('incidents')->delete();
        }
        
        // 10. Delete Incident Email Configs (keep system configs if is_system column exists)
        $this->command->info('Deleting Incident Email Configs...');
        if (DB::getSchemaBuilder()->hasTable('incident_email_configs')) {
            if (DB::getSchemaBuilder()->hasColumn('incident_email_configs', 'is_system')) {
                $deletedCounts['incident_email_configs'] = DB::table('incident_email_configs')
                    ->where('is_system', false)
                    ->count();
                DB::table('incident_email_configs')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['incident_email_configs'] = DB::table('incident_email_configs')->count();
                DB::table('incident_email_configs')->delete();
            }
        }
        
        // Re-enable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        $this->command->info("\n==========================================");
        $this->command->info("Successfully cleaned all Tasks and Incidents data!");
        $this->command->info("==========================================");
        $this->command->info("\nDeleted Records:");
        
        $taskTotal = 0;
        $incidentTotal = 0;
        
        $taskTables = ['task_activities', 'task_attachments', 'task_comments', 'sub_responsibilities', 'main_tasks', 'task_categories'];
        $incidentTables = ['incident_updates', 'incident_inbox', 'incidents', 'incident_email_configs'];
        
        $this->command->info("\nTask Management:");
        foreach ($taskTables as $table) {
            $count = $deletedCounts[$table] ?? 0;
            $taskTotal += $count;
            $this->command->info("  - {$table}: {$count}");
        }
        
        $this->command->info("\nIncident Management:");
        foreach ($incidentTables as $table) {
            $count = $deletedCounts[$table] ?? 0;
            $incidentTotal += $count;
            $this->command->info("  - {$table}: {$count}");
        }
        
        $totalDeleted = array_sum($deletedCounts);
        $this->command->info("\nSummary:");
        $this->command->info("  - Task Management: {$taskTotal}");
        $this->command->info("  - Incident Management: {$incidentTotal}");
        $this->command->info("  - Grand Total: {$totalDeleted}");
        $this->command->info("\n");
    }
}

