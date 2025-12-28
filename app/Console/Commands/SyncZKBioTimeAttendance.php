<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZKBioTimeSyncService;
use App\Models\AttendanceDevice;
use Illuminate\Support\Facades\Log;

class SyncZKBioTimeAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-zkbiotime 
                            {--device= : Sync specific device by ID or name}
                            {--all : Sync all active devices}
                            {--days=7 : Number of days to sync (default: 7)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize attendance records from ZKBio Time.Net database';

    /**
     * Execute the console command.
     */
    public function handle(ZKBioTimeSyncService $syncService)
    {
        $this->info('Starting ZKBio Time.Net attendance synchronization...');

        try {
            if ($this->option('all')) {
                // Sync all active devices
                $this->info('Syncing all active biometric devices...');
                $results = $syncService->syncAllDevices();
                
                $this->info("Sync completed:");
                $this->info("  - Synced: {$results['synced']} records");
                $this->info("  - Skipped: {$results['skipped']} records");
                $this->info("  - Errors: {$results['errors']}");
                
            } elseif ($this->option('device')) {
                // Sync specific device (by ID or name)
                $deviceIdentifier = $this->option('device');
                
                // Try to find by ID first (if numeric)
                if (is_numeric($deviceIdentifier)) {
                    $device = AttendanceDevice::find($deviceIdentifier);
                } else {
                    // Try to find by name or device_id
                    $device = AttendanceDevice::where('name', $deviceIdentifier)
                                             ->orWhere('device_id', $deviceIdentifier)
                                             ->first();
                }
                
                if (!$device) {
                    $this->error("Device '{$deviceIdentifier}' not found.");
                    $this->info("Available devices:");
                    $devices = AttendanceDevice::where('device_type', 'biometric')->get();
                    foreach ($devices as $d) {
                        $this->line("  - ID: {$d->id}, Name: {$d->name}, Device ID: {$d->device_id}");
                    }
                    return Command::FAILURE;
                }
                
                if ($device->device_type !== 'biometric') {
                    $this->error("Device '{$device->name}' is not a biometric device.");
                    return Command::FAILURE;
                }
                
                $this->info("Syncing device: {$device->name} (ID: {$device->id})");
                
                $days = (int) $this->option('days');
                $fromDate = \Carbon\Carbon::now()->subDays($days);
                
                $results = $syncService->syncFromZKBioTime($device, $fromDate);
                
                if ($results['success']) {
                    $this->info("Sync completed for device {$device->name}:");
                    $this->info("  - Synced: {$results['synced']} records");
                    $this->info("  - Skipped: {$results['skipped']} records");
                    $this->info("  - Errors: {$results['errors']}");
                    
                    if (!empty($results['error_messages'])) {
                        foreach ($results['error_messages'] as $error) {
                            $this->warn("  - Error: {$error}");
                        }
                    }
                } else {
                    $this->error("Sync failed for device {$device->name}");
                    foreach ($results['error_messages'] as $error) {
                        $this->error("  - {$error}");
                    }
                    return Command::FAILURE;
                }
                
            } else {
                // Sync devices based on sync interval
                $this->info('Syncing devices based on configured sync intervals...');
                
                $devices = AttendanceDevice::where('is_active', true)
                                          ->where('device_type', 'biometric')
                                          ->get();
                
                $syncedCount = 0;
                foreach ($devices as $device) {
                    $syncInterval = $device->sync_interval_minutes ?? 5;
                    $lastSync = $device->last_sync_at;
                    
                    // Check if it's time to sync
                    if (!$lastSync || \Carbon\Carbon::now()->diffInMinutes($lastSync) >= $syncInterval) {
                        $this->info("Syncing device: {$device->name}");
                        
                        $days = (int) $this->option('days');
                        $fromDate = $lastSync ? \Carbon\Carbon::parse($lastSync) : \Carbon\Carbon::now()->subDays($days);
                        
                        $results = $syncService->syncFromZKBioTime($device, $fromDate);
                        
                        if ($results['success']) {
                            $syncedCount++;
                            $this->info("  ✓ Synced {$results['synced']} records");
                        } else {
                            $this->warn("  ✗ Sync failed: " . implode(', ', $results['error_messages']));
                        }
                    } else {
                        $this->line("  - Skipping {$device->name} (synced " . $lastSync->diffForHumans() . ")");
                    }
                }
                
                $this->info("Completed. Synced {$syncedCount} device(s).");
            }
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error during sync: ' . $e->getMessage());
            Log::error('ZKBio Time sync command error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return Command::FAILURE;
        }
    }
}

