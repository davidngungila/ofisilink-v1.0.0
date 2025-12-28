<?php

namespace App\Console\Commands;

use App\Models\MainTask;
use App\Models\TaskActivity;
use App\Models\TaskComment;
use App\Models\TaskAttachment;
use App\Models\TaskCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanAllTasksData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:clean-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all tasks data from the system (main tasks, activities, comments, attachments, assignments, reports, and categories)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mainTasksCount = MainTask::count();
        $activitiesCount = TaskActivity::count();
        $commentsCount = TaskComment::count();
        $attachmentsCount = TaskAttachment::count();
        
        $assignmentsCount = 0;
        if (Schema::hasTable('activity_assignments')) {
            $assignmentsCount = DB::table('activity_assignments')->count();
        }
        
        $reportsCount = 0;
        if (Schema::hasTable('activity_reports')) {
            $reportsCount = DB::table('activity_reports')->count();
        }
        
        $categoriesCount = 0;
        if (Schema::hasTable('task_categories')) {
            $categoriesCount = DB::table('task_categories')->count();
        }

        $totalCount = $mainTasksCount + $activitiesCount + $commentsCount + $attachmentsCount + $assignmentsCount + $reportsCount + $categoriesCount;

        if ($totalCount === 0) {
            $this->info('No tasks data found in the database.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->info("Found tasks data:");
            $this->line("  - Main Tasks: {$mainTasksCount}");
            $this->line("  - Task Activities: {$activitiesCount}");
            $this->line("  - Comments: {$commentsCount}");
            $this->line("  - Attachments: {$attachmentsCount}");
            if ($assignmentsCount > 0) {
                $this->line("  - Activity Assignments: {$assignmentsCount}");
            }
            if ($reportsCount > 0) {
                $this->line("  - Activity Reports: {$reportsCount}");
            }
            if ($categoriesCount > 0) {
                $this->line("  - Task Categories: {$categoriesCount}");
            }
            $this->line("  - Total: {$totalCount}");
            
            if (!$this->confirm("Are you sure you want to delete all tasks data? This action cannot be undone.")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info("Deleting all tasks data...");

        try {
            DB::beginTransaction();

            // Step 1: Delete activity reports (depends on task_activities)
            if (Schema::hasTable('activity_reports')) {
                $deletedReports = DB::table('activity_reports')->delete();
                $this->info("Deleted {$deletedReports} activity report(s).");
            }

            // Step 2: Delete activity assignments (depends on task_activities)
            if (Schema::hasTable('activity_assignments')) {
                $deletedAssignments = DB::table('activity_assignments')->delete();
                $this->info("Deleted {$deletedAssignments} activity assignment(s).");
            }

            // Step 3: Delete task comments (depends on main_tasks and task_activities)
            $deletedComments = TaskComment::query()->delete();
            $this->info("Deleted {$deletedComments} comment(s).");

            // Step 4: Delete task attachments (depends on main_tasks and task_activities)
            $deletedAttachments = TaskAttachment::query()->delete();
            $this->info("Deleted {$deletedAttachments} attachment(s).");

            // Step 5: Break self-referential dependency in task_activities (depends_on_id)
            $this->info("Breaking activity dependencies...");
            DB::table('task_activities')->update(['depends_on_id' => null]);

            // Step 6: Delete task activities (depends on main_tasks)
            $deletedActivities = TaskActivity::query()->delete();
            $this->info("Deleted {$deletedActivities} activity/activities.");

            // Step 7: Delete main tasks (parent table)
            $deletedMainTasks = MainTask::query()->delete();
            $this->info("Deleted {$deletedMainTasks} main task(s).");

            // Step 8: Delete task categories (independent)
            if (Schema::hasTable('task_categories')) {
                $deletedCategories = DB::table('task_categories')->delete();
                $this->info("Deleted {$deletedCategories} task categor(y/ies).");
            }

            DB::commit();

            $this->info("Successfully deleted all tasks data.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to delete tasks data: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}






