<?php

namespace App\Console\Commands;

use App\Models\PermissionRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAllPermissionRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:clean-all {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all permission requests from the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = PermissionRequest::count();

        if ($count === 0) {
            $this->info('No permission requests found in the database.');
            return Command::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to delete all {$count} permission request(s)? This action cannot be undone.")) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->info("Deleting {$count} permission request(s)...");

        try {
            DB::beginTransaction();

            // Delete all permission requests
            $deleted = PermissionRequest::query()->delete();

            DB::commit();

            $this->info("Successfully deleted {$deleted} permission request(s).");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to delete permission requests: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}






