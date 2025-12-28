<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\EmailSyncService;
use App\Models\IncidentEmailConfig;
use Illuminate\Support\Facades\Log;

class SyncIncidentEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 2; // Retry twice on failure

    protected $configId;
    protected $dateFrom;
    protected $dateTo;
    protected $syncMode; // 'all', 'range', 'live'

    /**
     * Create a new job instance.
     */
    public function __construct($configId = null, $dateFrom = null, $dateTo = null, $syncMode = 'all')
    {
        $this->configId = $configId;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->syncMode = $syncMode;
    }

    /**
     * Execute the job.
     */
    public function handle(EmailSyncService $emailSyncService)
    {
        try {
            if ($this->configId) {
                // Sync specific config
                $config = IncidentEmailConfig::findOrFail($this->configId);
                try {
                    $count = $emailSyncService->syncEmailConfig(
                        $config, 
                        $this->dateFrom, 
                        $this->dateTo, 
                        $this->syncMode
                    );
                    $config->update([
                        'last_sync_at' => now(),
                        'sync_count' => ($config->sync_count ?? 0) + $count,
                        'connection_status' => 'connected',
                        'connection_error' => null,
                    ]);
                } catch (\Exception $e) {
                    $config->update([
                        'failed_sync_count' => ($config->failed_sync_count ?? 0) + 1,
                        'connection_status' => 'failed',
                        'connection_error' => $e->getMessage(),
                    ]);
                    throw $e;
                }
            } else {
                // Sync all active configs
                $count = $emailSyncService->syncAll(
                    $this->dateFrom, 
                    $this->dateTo, 
                    $this->syncMode
                );
            }

            Log::info("Email sync completed. Synced {$count} incident(s).");
            
        } catch (\Exception $e) {
            Log::error('Email sync job failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('Email sync job failed permanently: ' . $exception->getMessage());
        
        if ($this->configId) {
            $config = IncidentEmailConfig::find($this->configId);
            if ($config) {
                $config->update([
                    'failed_sync_count' => ($config->failed_sync_count ?? 0) + 1,
                    'connection_status' => 'failed',
                    'connection_error' => $exception->getMessage(),
                ]);
            }
        }
    }
}

