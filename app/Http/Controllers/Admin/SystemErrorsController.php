<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SystemErrorsController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:System Admin');
    }

    /**
     * Display recent system errors page
     */
    public function index(Request $request)
    {
        // If AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            $errors = $this->getRecentErrors($request);
            $search = $request->get('search', '');
            
            // Filter by search if provided
            if (!empty($search)) {
                $errors = $errors->filter(function($error) use ($search) {
                    return stripos($error['message'], $search) !== false ||
                           stripos($error['trace'] ?? '', $search) !== false;
                });
            }
            
            return response()->json([
                'success' => true,
                'errors' => $errors->values()->toArray()
            ]);
        }
        
        // Regular page load
        $errors = $this->getRecentErrors($request);
        
        return view('admin.system-errors', compact('errors'));
    }

    /**
     * Get recent errors from log files
     */
    protected function getRecentErrors(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $errors = [];
        $limit = $request->get('limit', 100);
        $level = $request->get('level', 'all'); // all, error, warning, critical
        
        if (!File::exists($logFile)) {
            return collect([]);
        }

        try {
            $lines = file($logFile);
            if (!$lines) {
                return collect([]);
            }

            $currentError = null;
            $errorBuffer = [];
            $inTrace = false;
            
            // Read from end of file (most recent first)
            $lines = array_reverse($lines);
            
            foreach ($lines as $line) {
                $trimmed = trim($line);
                
                // Check if line starts a new log entry (Laravel format)
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.(\w+):\s*(.+)$/', $line, $matches)) {
                    // Save previous error if exists
                    if ($currentError && $this->shouldIncludeError($currentError, $level)) {
                        if (!empty($errorBuffer)) {
                            $currentError['details'] = $errorBuffer;
                        }
                        $errors[] = $currentError;
                        if (count($errors) >= $limit) {
                            break;
                        }
                    }
                    
                    // Start new error
                    $currentError = [
                        'timestamp' => $matches[1],
                        'level' => strtolower($matches[2]),
                        'message' => $matches[3],
                        'details' => [],
                        'trace' => null,
                    ];
                    $errorBuffer = [];
                    $inTrace = false;
                } elseif ($currentError) {
                    // Continue collecting error details
                    if (!empty($trimmed)) {
                        // Check if this is a stack trace line
                        if (preg_match('/^(Stack trace:|#\d+)/', $trimmed) || $inTrace) {
                            $inTrace = true;
                            $currentError['trace'] = ($currentError['trace'] ?? '') . $trimmed . "\n";
                        } elseif (preg_match('/^\{.*\}$/', $trimmed) || strpos($trimmed, 'at ') !== false) {
                            // JSON context or file location
                            $errorBuffer[] = $trimmed;
                        } else {
                            // Regular detail line
                            if (!$inTrace) {
                                $errorBuffer[] = $trimmed;
                            }
                        }
                    }
                }
            }
            
            // Don't forget the last error
            if ($currentError && $this->shouldIncludeError($currentError, $level) && count($errors) < $limit) {
                if (!empty($errorBuffer)) {
                    $currentError['details'] = $errorBuffer;
                }
                $errors[] = $currentError;
            }
            
        } catch (\Exception $e) {
            Log::error('Error reading log file: ' . $e->getMessage());
        }

        return collect($errors);
    }

    /**
     * Check if error should be included based on level filter
     */
    protected function shouldIncludeError(array $error, string $level): bool
    {
        if ($level === 'all') {
            return in_array($error['level'], ['error', 'critical', 'emergency', 'alert', 'warning']);
        }
        
        return $error['level'] === $level;
    }

    /**
     * Get error statistics
     */
    public function getStatistics(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $stats = [
            'total_errors' => 0,
            'errors_today' => 0,
            'errors_this_week' => 0,
            'errors_this_month' => 0,
            'by_level' => [
                'error' => 0,
                'critical' => 0,
                'emergency' => 0,
                'alert' => 0,
                'warning' => 0,
            ],
        ];

        if (!File::exists($logFile)) {
            return response()->json(['success' => true, 'stats' => $stats]);
        }

        try {
            $lines = file($logFile);
            $today = now()->startOfDay();
            $weekStart = now()->startOfWeek();
            $monthStart = now()->startOfMonth();

            foreach ($lines as $line) {
                if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] local\.(\w+):/', $line, $matches)) {
                    $timestamp = Carbon::createFromFormat('Y-m-d H:i:s', $matches[1]);
                    $level = strtolower($matches[2]);
                    
                    if (in_array($level, ['error', 'critical', 'emergency', 'alert', 'warning'])) {
                        $stats['total_errors']++;
                        
                        if (isset($stats['by_level'][$level])) {
                            $stats['by_level'][$level]++;
                        }
                        
                        if ($timestamp->isSameDay($today)) {
                            $stats['errors_today']++;
                        }
                        if ($timestamp->isAfter($weekStart)) {
                            $stats['errors_this_week']++;
                        }
                        if ($timestamp->isAfter($monthStart)) {
                            $stats['errors_this_month']++;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error calculating statistics: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /**
     * Clear log file
     */
    public function clearLogs(Request $request)
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            
            if (File::exists($logFile)) {
                File::put($logFile, '');
                
                // Log activity
                \App\Services\ActivityLogService::logAction(
                    'system_logs_cleared',
                    'System error logs cleared',
                    null,
                    ['cleared_by' => auth()->id()]
                );
                
                return response()->json([
                    'success' => true,
                    'message' => 'Log file cleared successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Log file not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error clearing log file: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear log file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download log file
     */
    public function downloadLogs()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (!File::exists($logFile)) {
            abort(404, 'Log file not found');
        }
        
        return response()->download($logFile, 'laravel-errors-' . date('Y-m-d-His') . '.log');
    }
}

