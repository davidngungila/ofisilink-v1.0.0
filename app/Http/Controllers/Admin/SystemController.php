<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\DatabaseBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class SystemController extends Controller
{
    public function index()
    {
        // Enable query logging for performance metrics
        DB::enableQueryLog();
        
        $health = $this->getHealthSummary();
        
        // Get live user statistics
        $liveStats = $this->getLiveUserStats();
        
        // Get system information
        $systemInfo = $this->getSystemInfo();
        
        // Get backup schedule
        $backupSchedule = $this->getBackupSchedule();
        
        // Get advanced metrics
        $advancedMetrics = $this->getAdvancedMetrics();
        
        // Get recent system events
        $recentEvents = $this->getRecentSystemEvents();
        
        // Get performance metrics (after queries)
        $performanceMetrics = $this->getPerformanceMetrics();
        
        return view('admin.system', compact('health', 'liveStats', 'systemInfo', 'backupSchedule', 'advancedMetrics', 'recentEvents', 'performanceMetrics'));
    }

    public function healthCheck()
    {
        return response()->json([
            'success' => true,
            'health' => $this->getHealthSummary(true),
        ]);
    }

    protected function getHealthSummary(bool $deep = false): array
    {
        $summary = [
            'app' => [
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'version' => app()->version(),
            ],
            'checks' => [
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'mail' => $this->checkMail(),
                'storage' => $this->checkStorage(),
            ],
        ];

        if ($deep) {
            $summary['checks']['queue'] = $this->checkQueue();
        }

        return $summary;
    }

    protected function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function checkCache(): array
    {
        try {
            $key = 'health:' . Str::random(8);
            Cache::put($key, 'ok', 5);
            $val = Cache::get($key);
            return ['ok' => $val === 'ok'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function checkMail(): array
    {
        try {
            // Ensure mailer is configured (no actual send)
            $transport = Mail::getSymfonyTransport();
            return ['ok' => $transport !== null];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function checkStorage(): array
    {
        try {
            $path = 'health_checks';
            Storage::put($path . '/.probe', 'ok');
            $exists = Storage::exists($path . '/.probe');
            if ($exists) {
                Storage::delete($path . '/.probe');
            }
            return ['ok' => $exists];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    protected function checkQueue(): array
    {
        try {
            // Basic sanity: queue connection can be resolved
            $connection = config('queue.default');
            return ['ok' => !empty($connection), 'connection' => $connection];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function backupNow(Request $request)
    {
        try {
            // Get the latest backup record to update created_by
            $latestBackup = DatabaseBackup::where('status', 'in_progress')
                ->orderBy('created_at', 'desc')
                ->first();
            
            // Run backup command - notifications are sent automatically from the command
            $exitCode = \Artisan::call('system:backup-db', [
                '--return' => true,
            ]);
            
            // Update created_by if we have a backup record and user is authenticated
            if ($latestBackup && Auth::check()) {
                $latestBackup->update(['created_by' => Auth::id()]);
            }
            
            $output = trim(\Artisan::output());
            
            // Check if command failed
            if ($exitCode !== 0) {
                \Log::error('Backup command failed with exit code: ' . $exitCode, ['output' => $output]);
                return response()->json([
                    'success' => false, 
                    'message' => 'Backup failed. ' . ($output ?: 'Check server logs for details.')
                ], 500);
            }
            
            // Validate output is a valid file path
            if (empty($output)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Backup command returned no output. Check server logs.'
                ], 500);
            }
            
            // Check if output contains error messages (not a file path)
            if (stripos($output, 'error') !== false || 
                stripos($output, 'failed') !== false || 
                stripos($output, 'not found') !== false ||
                stripos($output, 'mysqldump') !== false) {
                \Log::error('Backup command output contains errors: ' . $output);
                return response()->json([
                    'success' => false, 
                    'message' => 'Backup failed: ' . $output
                ], 500);
            }
            
            // Validate the file path format (should be like backups/filename.sql)
            if (!preg_match('/^backups\/.+\.sql$/', $output)) {
                \Log::error('Invalid backup file path format: ' . $output);
                return response()->json([
                    'success' => false, 
                    'message' => 'Backup command returned invalid file path. Output: ' . $output
                ], 500);
            }
            
            // Check if file actually exists
            $fullPath = storage_path('app/' . $output);
            if (!file_exists($fullPath)) {
                \Log::error('Backup file does not exist: ' . $fullPath);
                return response()->json([
                    'success' => false, 
                    'message' => 'Backup file was not created. Check server logs for details.'
                ], 500);
            }
            
            $file = basename($output);
            // URL encode the filename to handle special characters properly
            $url = route('admin.system.backup.download', ['file' => $file]);
            
            // Notifications are already sent by the command, but we confirm here
            return response()->json([
                'success' => true, 
                'download_url' => $url,
                'message' => 'Backup completed successfully. All administrators have been notified.'
            ]);
        } catch (\Throwable $e) {
            \Log::error('Backup failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false, 
                'message' => 'Backup failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listBackups()
    {
        try {
            // Get backups from database
            $dbBackups = DatabaseBackup::orderBy('created_at', 'desc')->get();
            
            $backups = [];
            
            foreach ($dbBackups as $backup) {
                // Check if file still exists
                $fileExists = $backup->fileExists();
                
                // Always generate download URL - let the download method handle file existence
                // This prevents null URLs that cause "null.htm" errors
                // URL encode filename to handle special characters
                $downloadUrl = route('admin.system.backup.download', ['file' => $backup->filename]);
                
                $backups[] = [
                    'id' => $backup->id,
                    'filename' => $backup->filename,
                    'size' => $backup->file_size > 0 ? $this->formatBytes($backup->file_size) : 'Unknown',
                    'created_at' => $backup->created_at->format('Y-m-d H:i:s'),
                    'status' => $backup->status,
                    'error_message' => $backup->error_message,
                    'file_exists' => $fileExists,
                    'download_url' => $downloadUrl,
                ];
            }
            
            return response()->json([
                'success' => true,
                'backups' => $backups,
                'total' => count($backups),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error listing backups: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Error listing backups: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function downloadBackup(string $file)
    {
        try {
            // Sanitize filename - remove any path traversal attempts
            $safe = basename($file);
            
            // Remove any dangerous characters
            $safe = preg_replace('/[^a-zA-Z0-9._-]/', '', $safe);
            
            // First, try to find the backup record in the database
            $backup = DatabaseBackup::where('filename', $safe)->first();
            
            // Determine the file path - use database record if available, otherwise construct it
            if ($backup && $backup->file_path) {
                $path = $backup->file_path;
            } else {
                // Fallback to constructing path from filename
                $path = 'backups/' . $safe;
            }
            
            // Normalize path separators for cross-platform compatibility
            $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, trim($path, '/\\'));
            
            // Build full path - try multiple methods
            $full = null;
            $attempts = [];
            
            // Method 1: Use Storage facade
            try {
                $full = \Storage::disk('local')->path($path);
                $attempts[] = ['method' => 'Storage::path', 'path' => $full, 'exists' => file_exists($full)];
            } catch (\Exception $e) {
                $attempts[] = ['method' => 'Storage::path', 'error' => $e->getMessage()];
            }
            
            // Method 2: Direct storage_path construction
            if (!$full || !file_exists($full)) {
                $storageAppPath = rtrim(storage_path('app'), DIRECTORY_SEPARATOR);
                $full = $storageAppPath . DIRECTORY_SEPARATOR . $path;
                $attempts[] = ['method' => 'storage_path', 'path' => $full, 'exists' => file_exists($full)];
            }
            
            // Method 3: Try with realpath if file exists
            if ($full && file_exists($full)) {
                $resolved = realpath($full);
                if ($resolved !== false) {
                    $full = $resolved;
                    $attempts[] = ['method' => 'realpath', 'path' => $full, 'exists' => true];
                }
            }
            
            // Check if file exists on filesystem
            if (!$full || !file_exists($full)) {
                \Log::warning('Backup file not found on filesystem', [
                    'file' => $file,
                    'filename' => $safe,
                    'path' => $path,
                    'full_path' => $full,
                    'backup_record_exists' => $backup ? 'yes' : 'no',
                    'backup_file_path' => $backup ? $backup->file_path : null,
                    'attempts' => $attempts,
                    'storage_app_path' => storage_path('app'),
                    'backups_dir_exists' => is_dir(storage_path('app/backups')),
                    'backups_dir_files' => is_dir(storage_path('app/backups')) ? array_slice(scandir(storage_path('app/backups')), 2) : []
                ]);
                
                // Return proper error response
                $errorMessage = 'Backup file not found: ' . $safe . '. The file may have been deleted or moved.';
                
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 404);
                } else {
                    // Return a simple HTML error page instead of aborting
                    return response('<html><body><h1>File Not Found</h1><p>' . htmlspecialchars($errorMessage) . '</p><p><a href="' . url()->previous() . '">Go Back</a></p></body></html>', 404)
                        ->header('Content-Type', 'text/html; charset=utf-8');
                }
            }
            
            // Check file is readable
            if (!is_readable($full)) {
                \Log::error('Backup file not readable', [
                    'file' => $file,
                    'full_path' => $full
                ]);
                
                $errorMessage = 'Backup file is not accessible: ' . $safe;
                
                if (request()->expectsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 403);
                } else {
                    // Return a simple HTML error page instead of aborting
                    return response('<html><body><h1>Access Denied</h1><p>' . htmlspecialchars($errorMessage) . '</p><p><a href="' . url()->previous() . '">Go Back</a></p></body></html>', 403)
                        ->header('Content-Type', 'text/html; charset=utf-8');
                }
            }
            
            // Get file size
            $fileSize = filesize($full);
            
            return new StreamedResponse(function () use ($full) {
                $stream = fopen($full, 'rb');
                if ($stream === false) {
                    \Log::error('Failed to open backup file for streaming', ['file' => $full]);
                    return;
                }
                fpassthru($stream);
                fclose($stream);
            }, 200, [
                'Content-Type' => 'application/sql',
                'Content-Disposition' => 'attachment; filename="' . $safe . '"',
                'Content-Length' => $fileSize,
                'Cache-Control' => 'no-cache, must-revalidate',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error downloading backup', [
                'file' => $file,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = 'Error downloading backup: ' . $e->getMessage();
            
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            } else {
                // Return a simple HTML error page instead of aborting
                return response('<html><body><h1>Server Error</h1><p>' . htmlspecialchars($errorMessage) . '</p><p><a href="' . url()->previous() . '">Go Back</a></p></body></html>', 500)
                    ->header('Content-Type', 'text/html; charset=utf-8');
            }
        }
    }

    /**
     * Get live user statistics
     */
    protected function getLiveUserStats(): array
    {
        $sessionLifetime = config('session.lifetime', 120); // minutes
        $activeThreshold = now()->subMinutes($sessionLifetime)->timestamp;
        
        // Get active sessions (users logged in within session lifetime)
        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>=', $activeThreshold)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
        
        // Get total users
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        
        // Get users currently working (active in last 15 minutes)
        $workingThreshold = now()->subMinutes(15)->timestamp;
        $workingUsers = DB::table('sessions')
            ->where('last_activity', '>=', $workingThreshold)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
        
        // Get recent logins (last 24 hours)
        $recentLogins = DB::table('sessions')
            ->where('last_activity', '>=', now()->subDay()->timestamp)
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
        
        return [
            'live_logged_in' => $activeSessions,
            'currently_working' => $workingUsers,
            'total_users' => $totalUsers,
            'active_users' => $activeUsers,
            'recent_logins_24h' => $recentLogins,
        ];
    }

    /**
     * Get comprehensive system information
     */
    protected function getSystemInfo(): array
    {
        // Database info
        $dbSize = $this->getDatabaseSize();
        
        // Storage info
        $storageInfo = $this->getStorageInfo();
        
        // Server info
        $serverInfo = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ];
        
        // Application info
        $appInfo = [
            'version' => app()->version(),
            'environment' => config('app.env'),
            'debug_mode' => config('app.debug'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'url' => config('app.url'),
        ];
        
        // Queue info
        $queueInfo = [
            'driver' => config('queue.default'),
            'connection' => config('queue.connections.' . config('queue.default') . '.connection', 'N/A'),
        ];
        
        return [
            'database' => $dbSize,
            'storage' => $storageInfo,
            'server' => $serverInfo,
            'application' => $appInfo,
            'queue' => $queueInfo,
        ];
    }

    /**
     * Get database size
     */
    protected function getDatabaseSize(): array
    {
        try {
            $database = config('database.connections.' . config('database.default') . '.database');
            $result = DB::select("SELECT 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                FROM information_schema.TABLES 
                WHERE table_schema = ?", [$database]);
            
            $sizeMB = $result[0]->size_mb ?? 0;
            
            // Get table count
            $tableCount = DB::select("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE table_schema = ?", [$database]);
            $tables = $tableCount[0]->count ?? 0;
            
            return [
                'size_mb' => round($sizeMB, 2),
                'size_gb' => round($sizeMB / 1024, 2),
                'tables' => $tables,
            ];
        } catch (\Exception $e) {
            return [
                'size_mb' => 0,
                'size_gb' => 0,
                'tables' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get storage information
     */
    protected function getStorageInfo(): array
    {
        try {
            $storagePath = storage_path();
            $totalSpace = disk_total_space($storagePath);
            $freeSpace = disk_free_space($storagePath);
            $usedSpace = $totalSpace - $freeSpace;
            
            return [
                'total_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
                'used_gb' => round($usedSpace / 1024 / 1024 / 1024, 2),
                'free_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                'used_percent' => round(($usedSpace / $totalSpace) * 100, 2),
            ];
        } catch (\Exception $e) {
            return [
                'total_gb' => 0,
                'used_gb' => 0,
                'free_gb' => 0,
                'used_percent' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get backup schedule configuration
     */
    protected function getBackupSchedule(): array
    {
        $schedule = SystemSetting::getValue('backup_schedule', 'daily');
        $scheduleTime = SystemSetting::getValue('backup_schedule_time', '23:59');
        $enabled = SystemSetting::getValue('backup_auto_enabled', true);
        $retentionDays = SystemSetting::getValue('backup_retention_days', 30);
        
        return [
            'enabled' => $enabled,
            'frequency' => $schedule,
            'time' => $scheduleTime,
            'retention_days' => $retentionDays,
        ];
    }

    /**
     * Update backup schedule
     */
    public function updateBackupSchedule(Request $request)
    {
        $request->validate([
            'enabled' => 'required|boolean',
            'frequency' => 'required|in:daily,weekly,monthly',
            'time' => 'required|date_format:H:i',
            'retention_days' => 'required|integer|min:1|max:365',
        ]);
        
        SystemSetting::setValue('backup_auto_enabled', $request->enabled, 'boolean', 'Enable automatic backups');
        SystemSetting::setValue('backup_schedule', $request->frequency, 'text', 'Backup frequency');
        SystemSetting::setValue('backup_schedule_time', $request->time, 'text', 'Backup schedule time');
        SystemSetting::setValue('backup_retention_days', $request->retention_days, 'number', 'Number of days to retain backups');
        
        // Log activity
        ActivityLogService::logAction('system_backup_schedule_updated', "Updated backup schedule: frequency={$request->frequency}, enabled=" . ($request->enabled ? 'yes' : 'no'), null, [
            'frequency' => $request->frequency,
            'time' => $request->time,
            'enabled' => $request->enabled,
            'retention_days' => $request->retention_days,
        ]);
        
        // Update the scheduled task in Kernel.php would require manual update
        // For now, we'll store it and admin can update Kernel.php manually or use a job scheduler
        
        return response()->json([
            'success' => true,
            'message' => 'Backup schedule updated successfully',
            'schedule' => $this->getBackupSchedule(),
        ]);
    }

    /**
     * Get live user stats (AJAX endpoint)
     */
    public function getLiveStats()
    {
        return response()->json([
            'success' => true,
            'stats' => $this->getLiveUserStats(),
        ]);
    }

    /**
     * Get advanced system metrics
     */
    protected function getAdvancedMetrics(): array
    {
        // Memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);
        $memoryLimit = $this->parseBytes(ini_get('memory_limit'));
        
        // PHP extensions
        $extensions = get_loaded_extensions();
        $importantExtensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'curl', 'zip', 'gd', 'json'];
        $extensionStatus = [];
        foreach ($importantExtensions as $ext) {
            $extensionStatus[$ext] = extension_loaded($ext);
        }
        
        // Recent errors from logs
        $recentErrors = $this->getRecentLogErrors();
        
        // System load (if available)
        $systemLoad = $this->getSystemLoad();
        
        return [
            'memory' => [
                'current_mb' => round($memoryUsage / 1024 / 1024, 2),
                'peak_mb' => round($memoryPeak / 1024 / 1024, 2),
                'limit_mb' => $memoryLimit ? round($memoryLimit / 1024 / 1024, 2) : 'Unlimited',
                'usage_percent' => $memoryLimit ? round(($memoryUsage / $memoryLimit) * 100, 2) : 0,
            ],
            'extensions' => $extensionStatus,
            'recent_errors' => $recentErrors,
            'system_load' => $systemLoad,
        ];
    }

    /**
     * Get recent system events
     */
    protected function getRecentSystemEvents(): array
    {
        $events = [];
        
        // Recent activity logs
        try {
            $recentActivities = DB::table('activity_logs')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            foreach ($recentActivities as $activity) {
                $events[] = [
                    'type' => 'activity',
                    'title' => ucfirst($activity->action ?? 'Unknown') . ' - ' . ($activity->model_type ? class_basename($activity->model_type) : 'System'),
                    'description' => $activity->description ?? 'No description',
                    'time' => $activity->created_at,
                    'icon' => $this->getActivityIcon($activity->action ?? 'unknown'),
                ];
            }
        } catch (\Exception $e) {
            // Ignore if table doesn't exist
        }
        
        return $events;
    }

    /**
     * Get performance metrics
     */
    protected function getPerformanceMetrics(): array
    {
        // Database query performance
        $dbQueries = DB::getQueryLog();
        $queryCount = count($dbQueries);
        $queryTime = 0;
        foreach ($dbQueries as $query) {
            $queryTime += $query['time'] ?? 0;
        }
        
        // Cache hit rate (if using cache)
        $cacheStats = $this->getCacheStats();
        
        return [
            'database' => [
                'query_count' => $queryCount,
                'total_time_ms' => round($queryTime, 2),
                'avg_time_ms' => $queryCount > 0 ? round($queryTime / $queryCount, 2) : 0,
            ],
            'cache' => $cacheStats,
        ];
    }

    /**
     * Get cache statistics
     */
    protected function getCacheStats(): array
    {
        try {
            $driver = config('cache.default');
            return [
                'driver' => $driver,
                'status' => Cache::getStore() !== null ? 'active' : 'inactive',
            ];
        } catch (\Exception $e) {
            return [
                'driver' => 'unknown',
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get recent log errors
     */
    protected function getRecentLogErrors(): array
    {
        $errors = [];
        $logFile = storage_path('logs/laravel.log');
        
        if (file_exists($logFile)) {
            try {
                $lines = file($logFile);
                $errorLines = array_filter($lines, function($line) {
                    return stripos($line, 'error') !== false || 
                           stripos($line, 'exception') !== false ||
                           stripos($line, 'failed') !== false;
                });
                
                $recentErrors = array_slice(array_reverse($errorLines), 0, 5);
                
                foreach ($recentErrors as $error) {
                    $errors[] = [
                        'message' => substr(trim($error), 0, 200),
                        'time' => $this->extractLogTime($error),
                    ];
                }
            } catch (\Exception $e) {
                // Ignore log read errors
            }
        }
        
        return $errors;
    }

    /**
     * Get system load
     */
    protected function getSystemLoad(): array
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return [
                '1min' => round($load[0] ?? 0, 2),
                '5min' => round($load[1] ?? 0, 2),
                '15min' => round($load[2] ?? 0, 2),
            ];
        }
        
        return [
            '1min' => 'N/A',
            '5min' => 'N/A',
            '15min' => 'N/A',
        ];
    }

    /**
     * Parse bytes from ini_get values
     */
    protected function parseBytes($value): ?int
    {
        if ($value === '-1' || $value === false) {
            return null;
        }
        
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Get activity icon
     */
    protected function getActivityIcon(string $action): string
    {
        $icons = [
            'created' => 'bx-plus-circle',
            'updated' => 'bx-edit',
            'deleted' => 'bx-trash',
            'login' => 'bx-log-in',
            'logout' => 'bx-log-out',
            'viewed' => 'bx-show',
        ];
        
        return $icons[strtolower($action)] ?? 'bx-info-circle';
    }

    /**
     * Extract time from log line
     */
    protected function extractLogTime(string $line): ?string
    {
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]/', $line, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get all active users for management
     */
    public function getUsers(Request $request)
    {
        try {
            $query = User::with(['roles', 'primaryDepartment', 'blocker']);

            // Search
            if ($request->has('search') && $request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('email', 'like', '%' . $request->search . '%')
                      ->orWhere('employee_id', 'like', '%' . $request->search . '%')
                      ->orWhere('phone', 'like', '%' . $request->search . '%');
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status !== '') {
                if ($request->status === 'active') {
                    $query->where('is_active', true)->whereNull('blocked_at');
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                } elseif ($request->status === 'blocked') {
                    $query->whereNotNull('blocked_at');
                }
            }

            // Filter by role
            if ($request->has('role') && $request->role) {
                $query->whereHas('roles', function($q) use ($request) {
                    $q->where('roles.id', $request->role);
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            $users = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

            $formattedUsers = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'employee_id' => $user->employee_id,
                    'phone' => $user->phone ?? $user->mobile,
                    'is_active' => $user->is_active,
                    'is_blocked' => $user->is_blocked,
                    'blocked_at' => $user->blocked_at ? $user->blocked_at->format('Y-m-d H:i:s') : null,
                    'blocked_until' => $user->blocked_until ? $user->blocked_until->format('Y-m-d H:i:s') : null,
                    'block_reason' => $user->block_reason,
                    'blocked_by_name' => $user->blocker ? $user->blocker->name : null,
                    'roles' => $user->roles->pluck('name')->toArray(),
                    'department' => $user->primaryDepartment ? $user->primaryDepartment->name : null,
                    'created_at' => $user->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'users' => $formattedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading users: ' . $e->getMessage(),
                'users' => [],
                'pagination' => []
            ], 500);
        }
    }

    /**
     * Block a user
     */
    public function blockUser(Request $request, User $user)
    {
        $request->validate([
            'duration_type' => 'required|in:forever,hours,days,weeks,months',
            'duration_value' => 'required_if:duration_type,!=,forever|integer|min:1',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $blockedUntil = null;
            
            if ($request->duration_type === 'forever') {
                $blockedUntil = null; // Forever blocked
            } else {
                $value = $request->duration_value ?? 1;
                switch ($request->duration_type) {
                    case 'hours':
                        $blockedUntil = now()->addHours($value);
                        break;
                    case 'days':
                        $blockedUntil = now()->addDays($value);
                        break;
                    case 'weeks':
                        $blockedUntil = now()->addWeeks($value);
                        break;
                    case 'months':
                        $blockedUntil = now()->addMonths($value);
                        break;
                }
            }

            $user->update([
                'blocked_at' => now(),
                'blocked_until' => $blockedUntil,
                'block_reason' => $request->reason,
                'blocked_by' => Auth::id(),
            ]);

            // Logout user if currently logged in
            if ($user->id !== Auth::id()) {
                // Invalidate all sessions for this user
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'User blocked successfully',
                'user' => [
                    'id' => $user->id,
                    'is_blocked' => $user->fresh()->is_blocked,
                    'blocked_until' => $user->blocked_until ? $user->blocked_until->format('Y-m-d H:i:s') : 'Forever',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error blocking user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unblock a user
     */
    public function unblockUser(User $user)
    {
        try {
            $user->update([
                'blocked_at' => null,
                'blocked_until' => null,
                'block_reason' => null,
                'blocked_by' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User unblocked successfully',
                'user' => [
                    'id' => $user->id,
                    'is_blocked' => false,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unblocking user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleUserStatus(User $user)
    {
        try {
            // Prevent deactivating yourself
            if ($user->id === Auth::id() && $user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account'
                ], 403);
            }

            $user->update([
                'is_active' => !$user->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => $user->is_active ? 'User activated successfully' : 'User deactivated successfully',
                'user' => [
                    'id' => $user->id,
                    'is_active' => $user->is_active,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling user status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser(User $user)
    {
        try {
            // Prevent deleting yourself
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            $userName = $user->name;
            $userData = $user->toArray();
            $user->delete();

            // Log activity
            ActivityLogService::logDeleted($user, "Deleted user: {$userName}", [
                'user_id' => $userData['id'] ?? null,
                'name' => $userName,
                'email' => $userData['email'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => "User '{$userName}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all active sessions
     */
    public function getActiveSessions(Request $request)
    {
        try {
            $sessionLifetime = config('session.lifetime', 120); // minutes
            $activeThreshold = now()->subMinutes($sessionLifetime)->timestamp;
            
            $query = DB::table('sessions')
                ->where('last_activity', '>=', $activeThreshold)
                ->whereNotNull('user_id')
                ->orderBy('last_activity', 'desc');
            
            // Filter by user if provided
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            
            $sessions = $query->get();
            
            $formattedSessions = $sessions->map(function($session) {
                try {
                    $user = User::find($session->user_id);
                    
                    // Get IP and user agent directly from sessions table columns
                    $ipAddress = $session->ip_address ?? 'Unknown';
                    $userAgent = $session->user_agent ?? 'Unknown';
                    
                    // If not in columns, try to extract from payload as fallback
                    if ($ipAddress === 'Unknown' || $userAgent === 'Unknown') {
                        try {
                            $payload = unserialize(base64_decode($session->payload));
                            if (is_array($payload)) {
                                $ipAddress = $ipAddress === 'Unknown' ? ($payload['ip'] ?? $payload['ip_address'] ?? 'Unknown') : $ipAddress;
                                $userAgent = $userAgent === 'Unknown' ? ($payload['user_agent'] ?? 'Unknown') : $userAgent;
                            }
                        } catch (\Exception $e) {
                            // Keep default values
                        }
                    }
                    
                    $isCurrentSession = $session->id === session()->getId();
                    
                    // Detect if this is a mobile app session
                    $isMobileApp = false;
                    $deviceType = 'Web';
                    $appVersion = null;
                    
                    if ($userAgent && $userAgent !== 'Unknown') {
                        $userAgentLower = strtolower($userAgent);
                        // Check for mobile app identifiers
                        if (stripos($userAgent, 'OfisiLink') !== false || 
                            stripos($userAgent, 'Mobile App') !== false ||
                            stripos($userAgent, 'OfisiLinkApp') !== false ||
                            stripos($userAgent, 'okhttp') !== false ||
                            (stripos($userAgent, 'android') !== false && stripos($userAgent, 'ofisi') !== false) ||
                            (stripos($userAgent, 'ios') !== false && stripos($userAgent, 'ofisi') !== false)) {
                            $isMobileApp = true;
                            
                            // Extract device type
                            if (stripos($userAgent, 'android') !== false) {
                                $deviceType = 'Android';
                            } elseif (stripos($userAgent, 'ios') !== false || stripos($userAgent, 'iphone') !== false || stripos($userAgent, 'ipad') !== false) {
                                $deviceType = 'iOS';
                            } else {
                                $deviceType = 'Mobile';
                            }
                            
                            // Try to extract app version
                            if (preg_match('/version[\/\s]?([\d\.]+)/i', $userAgent, $matches)) {
                                $appVersion = $matches[1];
                            } elseif (preg_match('/v([\d\.]+)/i', $userAgent, $matches)) {
                                $appVersion = $matches[1];
                            }
                        } elseif (stripos($userAgent, 'mobile') !== false || 
                                  stripos($userAgent, 'android') !== false || 
                                  stripos($userAgent, 'iphone') !== false ||
                                  stripos($userAgent, 'ipad') !== false) {
                            // Mobile browser (not app)
                            $deviceType = 'Mobile Browser';
                        }
                    }
                    
                    return [
                        'id' => $session->id,
                        'user_id' => $session->user_id,
                        'user_name' => $user ? $user->name : 'Unknown User',
                        'user_email' => $user ? $user->email : 'N/A',
                        'ip_address' => $ipAddress,
                        'user_agent' => $userAgent,
                        'last_activity' => Carbon::createFromTimestamp($session->last_activity)->format('Y-m-d H:i:s'),
                        'last_activity_human' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                        'is_current' => $isCurrentSession,
                        'is_active' => now()->timestamp - $session->last_activity < 900, // Active in last 15 minutes
                        'is_mobile_app' => $isMobileApp,
                        'device_type' => $deviceType,
                        'app_version' => $appVersion,
                    ];
                } catch (\Exception $e) {
                    \Log::error('Error formatting session: ' . $e->getMessage(), ['session_id' => $session->id ?? 'unknown']);
                    return null;
                }
            })->filter(); // Remove null entries
            
            return response()->json([
                'success' => true,
                'sessions' => $formattedSessions,
                'total' => $formattedSessions->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading sessions: ' . $e->getMessage(),
                'sessions' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Revoke a specific session
     */
    public function revokeSession(Request $request, string $sessionId)
    {
        try {
            // Prevent revoking your own current session
            if ($sessionId === session()->getId()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot revoke your own current session'
                ], 403);
            }
            
            $deleted = DB::table('sessions')->where('id', $sessionId)->delete();
            
            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'Session revoked successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found or already revoked'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error revoking session: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke all sessions for a specific user
     */
    public function revokeAllUserSessions(Request $request, User $user)
    {
        try {
            // Prevent revoking all sessions for yourself
            if ($user->id === Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot revoke all your own sessions. Please use logout instead.'
                ], 403);
            }
            
            $deleted = DB::table('sessions')->where('user_id', $user->id)->delete();
            
            return response()->json([
                'success' => true,
                'message' => "All sessions for {$user->name} have been revoked successfully",
                'revoked_count' => $deleted
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error revoking user sessions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke all sessions except current
     */
    public function revokeAllSessions(Request $request)
    {
        try {
            $currentSessionId = session()->getId();
            $deleted = DB::table('sessions')
                ->where('id', '!=', $currentSessionId)
                ->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'All sessions have been revoked successfully (except your current session)',
                'revoked_count' => $deleted
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error revoking sessions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a backup
     */
    public function deleteBackup(DatabaseBackup $backup)
    {
        try {
            $filename = $backup->filename;
            $filePath = $backup->file_path;
            
            // Delete the file from storage
            if ($filePath) {
                try {
                    // Try using Storage facade first
                    if (\Storage::disk('local')->exists($filePath)) {
                        \Storage::disk('local')->delete($filePath);
                    } else {
                        // Fallback: try direct file deletion
                        $fullPath = storage_path('app/' . $filePath);
                        if (file_exists($fullPath)) {
                            @unlink($fullPath);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete backup file', [
                        'backup_id' => $backup->id,
                        'file_path' => $filePath,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with database record deletion even if file deletion fails
                }
            }
            
            // Delete the database record
            $backup->delete();
            
            // Log activity
            ActivityLogService::logAction('backup_deleted', "Deleted backup: {$filename}", null, [
                'backup_id' => $backup->id,
                'filename' => $filename,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Backup '{$filename}' has been deleted successfully."
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting backup', [
                'backup_id' => $backup->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting backup: ' . $e->getMessage()
            ], 500);
        }
    }
}


