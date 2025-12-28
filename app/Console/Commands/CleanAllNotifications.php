<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAllNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clean-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all notifications from the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Notification::count();

        if ($count === 0) {
            $this->info('No notifications found in the database.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to delete all {$count} notifications? This action cannot be undone.")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info("Deleting {$count} notifications...");

        try {
            DB::beginTransaction();

            // Delete all notifications
            $deleted = Notification::query()->delete();

            DB::commit();

            $this->info("Successfully deleted {$deleted} notification(s).");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to delete notifications: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}






