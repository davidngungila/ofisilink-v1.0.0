<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Send reminders daily at 08:00
        $schedule->command('assessments:send-reminders')->dailyAt('08:00');
        // Daily full DB backup at 23:59:30
        $schedule->command('system:backup-db --sleep-30')->dailyAt('23:59');
        // Sync incident emails in live mode every 1 minute
        $schedule->call(function () {
            $configs = \App\Models\IncidentEmailConfig::where('is_active', true)->get();
            foreach ($configs as $config) {
                // Check if live mode is enabled (check sync_settings)
                $syncSettings = $config->sync_settings ?? [];
                if (isset($syncSettings['live_mode']) && $syncSettings['live_mode'] === true) {
                    \App\Jobs\SyncIncidentEmailsJob::dispatch($config->id, null, null, 'live');
                }
            }
        })->everyMinute();
        
        // Sync ZKBio Time.Net attendance records every 5 minutes
        $schedule->command('attendance:sync-zkbiotime')->everyFiveMinutes();
        
        // Sync attendance devices automatically every 5 minutes
        $schedule->command('attendance:sync-devices')->everyFiveMinutes()->withoutOverlapping();
        
        // Live capture: Sync attendance from ZKTeco devices every minute (real-time)
        // NOTE: This only works for devices on the same network (local development)
        // For cloud servers, use Push SDK instead (devices push data to server)
        $schedule->call(function () {
            // Check if Push SDK is enabled - if so, skip direct connection sync
            $pushSdkEnabled = config('zkteco.push_sdk.enabled', false);
            if ($pushSdkEnabled) {
                // Push SDK is enabled - devices push data automatically
                // Only update device status from Push SDK pings
                \Illuminate\Support\Facades\Log::debug('Push SDK enabled - skipping direct connection sync');
                return;
            }
            
            $devices = \App\Models\AttendanceDevice::where('is_active', true)
                ->whereNotNull('ip_address')
                ->get();
            
            foreach ($devices as $device) {
                try {
                    // Check if device IP is reachable (local network only)
                    // Only connect to private/local IPs, skip public IPs
                    $deviceIp = $device->ip_address;
                    $isPrivateIp = filter_var($deviceIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
                    
                    if (!$isPrivateIp) {
                        // This is a public IP - likely a cloud server scenario
                        // Skip direct connection, use Push SDK instead
                        \Illuminate\Support\Facades\Log::debug('Skipping direct connection for public IP device - use Push SDK instead', [
                            'device' => $device->name,
                            'ip' => $deviceIp
                        ]);
                        continue;
                    }
                    
                    $zktecoService = new \App\Services\ZKTecoService(
                        $device->ip_address,
                        $device->port ?? 4370,
                        $device->settings['comm_key'] ?? 0
                    );
                    
                    // Sync attendances from device to database (live capture)
                    $syncResult = $zktecoService->syncAttendancesToDatabase();
                    
                    // Update device status
                    $device->last_sync_at = \Carbon\Carbon::now();
                    $device->is_online = true;
                    $device->save();
                    
                    \Illuminate\Support\Facades\Log::info('Live capture sync completed', [
                        'device' => $device->name,
                        'synced' => $syncResult['synced'] ?? 0,
                        'skipped' => $syncResult['skipped'] ?? 0
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Live capture sync error', [
                        'device' => $device->name ?? 'Unknown',
                        'error' => $e->getMessage()
                    ]);
                    
                    // Mark device as offline if connection fails
                    try {
                        $device->is_online = false;
                        $device->save();
                    } catch (\Exception $saveError) {
                        // Ignore save errors
                    }
                }
            }
        })->everyMinute()->withoutOverlapping();
        
        // Ensure continuous attendance sync is running (every 10 seconds)
        // This checks if the process is running and starts it if not
        $schedule->call(function () {
            $processName = 'attendance:sync-continuous';
            $command = 'php ' . base_path('artisan') . ' ' . $processName;
            
            // Check if process is already running
            $isRunning = false;
            if (PHP_OS_FAMILY === 'Windows') {
                // Windows: Check using tasklist
                $output = shell_exec("tasklist /FI \"IMAGENAME eq php.exe\" 2>NUL");
                $isRunning = strpos($output, 'php.exe') !== false && 
                            strpos(shell_exec("wmic process where \"commandline like '%{$processName}%'\" get processid 2>NUL"), 'ProcessId') !== false;
            } else {
                // Linux/Unix: Check using ps
                $output = shell_exec("ps aux | grep '[a]ttendance:sync-continuous'");
                $isRunning = !empty(trim($output));
            }
            
            if (!$isRunning) {
                \Illuminate\Support\Facades\Log::info('Starting continuous attendance sync process');
                
                if (PHP_OS_FAMILY === 'Windows') {
                    // Windows: Start in background
                    $command = "start /B {$command} > NUL 2>&1";
                    pclose(popen($command, 'r'));
                } else {
                    // Linux/Unix: Start in background with nohup
                    $command = "nohup {$command} >> " . storage_path('logs/attendance-sync-continuous.log') . " 2>&1 &";
                    shell_exec($command);
                }
            }
        })->everyMinute()->withoutOverlapping(30);
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}


