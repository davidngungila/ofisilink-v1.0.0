<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AttendanceDevice;
use App\Services\ZKBioTimeSyncService;
use Illuminate\Support\Facades\Log;

class SyncAttendanceDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-devices {--device-id= : Sync specific device by ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync attendance records from biometric devices to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting attendance device sync...');

        $deviceId = $this->option('device-id');
        
        if ($deviceId) {
            // Sync specific device
            $device = AttendanceDevice::find($deviceId);
            
            if (!$device) {
                $this->error("Device with ID {$deviceId} not found");
                return 1;
            }
            
            if (!$device->is_active) {
                $this->warn("Device {$device->name} is not active. Skipping...");
                return 0;
            }
            
            $this->syncDevice($device);
        } else {
            // Sync all active devices
            $devices = AttendanceDevice::where('is_active', true)
                ->whereNotNull('ip_address')
                ->get();
            
            if ($devices->isEmpty()) {
                $this->warn('No active devices found to sync');
                return 0;
            }
            
            $this->info("Found {$devices->count()} active device(s)");
            
            $syncedCount = 0;
            $failedCount = 0;
            
            foreach ($devices as $device) {
                try {
                    $this->syncDevice($device);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $this->error("Failed to sync device {$device->name}: " . $e->getMessage());
                    $failedCount++;
                    Log::error("Auto sync device error", [
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            $this->info("Sync completed: {$syncedCount} successful, {$failedCount} failed");
        }
        
        return 0;
    }
    
    /**
     * Sync a single device
     */
    private function syncDevice(AttendanceDevice $device)
    {
        $this->line("Syncing device: {$device->name} ({$device->ip_address})...");
        
        try {
            $zkbioSyncService = new ZKBioTimeSyncService();
            $result = $zkbioSyncService->syncFromZKBioTime($device);
            
            if ($result['success']) {
                $this->info("✓ Device {$device->name}: {$result['synced']} records synced, {$result['skipped']} skipped");
            } else {
                $this->warn("⚠ Device {$device->name}: Sync completed with errors");
                if (!empty($result['error_messages'])) {
                    foreach ($result['error_messages'] as $error) {
                        $this->line("  - {$error}");
                    }
                }
            }
        } catch (\Exception $e) {
            $this->error("✗ Device {$device->name}: " . $e->getMessage());
            throw $e;
        }
    }
}

