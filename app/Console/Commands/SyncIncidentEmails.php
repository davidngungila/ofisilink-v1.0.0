<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmailSyncService;

class SyncIncidentEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'incidents:sync-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize incidents from configured email accounts';

    /**
     * Execute the console command.
     */
    public function handle(EmailSyncService $emailSyncService)
    {
        $this->info('Starting email synchronization...');
        
        try {
            $count = $emailSyncService->syncAll();
            
            $this->info("Successfully synced {$count} new incident(s).");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error syncing emails: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
