<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ZKTecoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SyncAttendanceContinuous extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:sync-continuous {--stop-after=0 : Stop after N seconds (0 = run forever)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuously sync attendance from external API every 10 seconds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting continuous attendance sync (every 10 seconds)...');
        $this->info('Press Ctrl+C to stop');
        
        $stopAfter = (int) $this->option('stop-after');
        $startTime = time();
        $iteration = 0;
        
        while (true) {
            $iteration++;
            $currentTime = time();
            
            // Check if we should stop
            if ($stopAfter > 0 && ($currentTime - $startTime) >= $stopAfter) {
                $this->info("Stopped after {$stopAfter} seconds");
                break;
            }
            
            try {
                $this->line("[" . now()->format('Y-m-d H:i:s') . "] Sync iteration #{$iteration}...");
                
                $controller = app(ZKTecoController::class);
                $request = new Request();
                $response = $controller->syncAttendanceFromApi($request);
                
                $data = json_decode($response->getContent(), true);
                
                if ($data['success'] ?? false) {
                    $this->info("✓ Synced: {$data['synced']}, Skipped: {$data['skipped']}, Errors: {$data['errors']}");
                } else {
                    $this->error("✗ Sync failed: " . ($data['message'] ?? 'Unknown error'));
                }
                
            } catch (\Exception $e) {
                $this->error("✗ Error: " . $e->getMessage());
                Log::error('Continuous attendance sync error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // Wait 10 seconds before next iteration
            sleep(10);
        }
        
        $this->info('Continuous sync stopped.');
        return 0;
    }
}



