<?php

namespace App\Http\Controllers;

use App\Services\ZKTecoServiceNew;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ZKTecoController extends Controller
{
    /**
     * Test device connection with step-by-step progress
     */
    /**
     * Test connection directly to device - tries all methods until success
     * This is a DIRECT server-side connection, not AJAX
     */
    public function testConnection(Request $request)
    {
        // Convert string values to proper types before validation
        $data = $request->all();
        if (isset($data['port'])) {
            $data['port'] = is_numeric($data['port']) ? (int)$data['port'] : null;
        }
        if (isset($data['password'])) {
            $data['password'] = is_numeric($data['password']) ? (int)$data['password'] : null;
        }
        if (isset($data['device_id'])) {
            $data['device_id'] = is_numeric($data['device_id']) ? (int)$data['device_id'] : null;
        }
        
        $validator = Validator::make($data, [
            'ip' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'password' => 'nullable|integer',
            'device_id' => 'nullable|integer|min:1|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Use validated data or defaults
        $ip = $data['ip'];
        $port = $data['port'] ?? config('zkteco.port', 4370);
        $password = $data['password'] ?? config('zkteco.password', 0);
        // Device ID: Try provided value, or default to 6 if not provided (based on user's device)
        $deviceId = $data['device_id'] ?? config('zkteco.device_id', 6);

        $connectionResults = [
            'ip' => $ip,
            'port' => $port,
            'password' => $password,
            'device_id' => $deviceId,
            'attempts' => [],
            'success' => false,
            'device_info' => null,
            'working_method' => null,
            'error' => null
        ];

        // Try connection with library (simpler approach)
        try {
            $zkteco = new ZKTecoServiceNew($ip, $port, $password);
            
            $connectionResults['attempts'][] = [
                'method' => 'Library Connection',
                'status' => 'testing',
                'message' => 'Testing connection...'
            ];
            
            $connected = $zkteco->connect();
                
            if ($connected) {
                // Connection successful! Now verify by getting device info
                try {
                    $deviceInfo = $zkteco->getDeviceInfo();
                    
                    $connectionResults['success'] = true;
                    $connectionResults['device_info'] = $deviceInfo ?: [
                        'ip' => $ip,
                        'port' => $port,
                        'device_name' => 'ZKTeco Device',
                        'model' => 'ZKTeco',
                        'firmware_version' => 'Connected',
                    ];
                    $connectionResults['working_method'] = 'Library Connection';
                    $connectionResults['working_password'] = $password;
                    
                    $connectionResults['attempts'][0]['status'] = 'success';
                    $connectionResults['attempts'][0]['message'] = 'Connection successful! Device info retrieved.';
                    
                    $zkteco->disconnect();
                    
                    // Success! Return JSON for AJAX requests, redirect for form submissions
                    if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
                        return response()->json([
                            'success' => true,
                            'message' => 'Device connected successfully!',
                            'device_info' => $connectionResults['device_info'],
                            'working_method' => 'Library Connection',
                            'working_password' => $password,
                            'attempts' => $connectionResults['attempts']
                        ]);
                    }
                    
                    return back()->with([
                        'success' => true,
                        'message' => 'Device connected successfully!',
                        'device_info' => $connectionResults['device_info'],
                        'working_method' => 'Library Connection',
                        'connection_results' => $connectionResults
                    ]);
                } catch (\Exception $e) {
                    // Connection established but can't get device info
                    $zkteco->disconnect();
                    $connectionResults['attempts'][0]['status'] = 'partial';
                    $connectionResults['attempts'][0]['message'] = 'Connected but failed to get device info: ' . $e->getMessage();
                    $connectionResults['error'] = $e->getMessage();
                }
            } else {
                $connectionResults['attempts'][0]['status'] = 'failed';
                $connectionResults['attempts'][0]['message'] = 'Connection failed';
                $connectionResults['error'] = 'Connection failed';
            }
        } catch (\Exception $e) {
            $connectionResults['attempts'][] = [
                'method' => 'Library Connection',
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
            $connectionResults['error'] = $e->getMessage();
        }

        // All methods failed
        $connectionResults['error'] = 'All connection methods failed. Please check device settings.';
        
        if ($request->expectsJson() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect using all methods',
                'connection_results' => $connectionResults
            ], 422);
        }
        
        return back()->with([
            'error' => true,
            'message' => 'Failed to connect to device. All connection methods were tried.',
            'connection_results' => $connectionResults
        ]);
    }

    /**
     * Get device information
     */
    public function getDeviceInfo(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'password' => 'nullable|integer',
        ]);

        try {
            $zkteco = new ZKTecoServiceNew(
                $request->ip,
                $request->port ?? 4370,
                $request->password ?? 0
            );

            $zkteco->connect();
            $deviceInfo = $zkteco->getDeviceInfo();
            $zkteco->disconnect();

            return response()->json([
                'success' => true,
                'device_info' => $deviceInfo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Register user to device via API
     * Uses external API endpoint as defined in config
     */
    public function registerUser(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);

            if (!$user->enroll_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have an enroll_id. Please set enroll_id first.',
                ], 422);
            }

            // Use exact API endpoint as defined
            $apiUrl = "http://192.168.100.100:8000/api/v1/users/register";
            $timeout = config('zkteco.timeout', 60); // Increased timeout for external API calls

            // Extract first name only (before first space)
            $firstName = trim(explode(' ', $user->name)[0]);

            Log::info('Registering user via external API', [
                'user_id' => $userId,
                'enroll_id' => $user->enroll_id,
                'full_name' => $user->name,
                'first_name' => $firstName,
                'api_url' => $apiUrl,
            ]);
            
            $response = Http::timeout($timeout)->post($apiUrl, [
                'id' => $user->enroll_id,
                'name' => $firstName, // Send only first name
            ]);

            $responseData = $response->json();
            $statusCode = $response->status();

            if ($response->successful() && ($responseData['success'] ?? false)) {
                // Update user registration status if not already set
                if (!$user->registered_on_device) {
                    $user->registered_on_device = true;
                    $user->device_registered_at = Carbon::now();
                    $user->save();
                }

                Log::info('User registered successfully via external API', [
                    'user_id' => $userId,
                    'enroll_id' => $user->enroll_id,
                    'api_response' => $responseData
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User registered to device successfully via API',
                    'data' => $responseData['data'] ?? null
                ]);
            } else {
                $errorMessage = $responseData['message'] ?? 'Failed to register user via API';
                
                Log::warning('User registration failed via external API', [
                    'user_id' => $userId,
                    'enroll_id' => $user->enroll_id,
                    'api_response' => $responseData,
                    'status_code' => $statusCode,
                    'api_url' => $apiUrl
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => $responseData['errors'] ?? null
                ], $statusCode ?: 422);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('API connection error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'api_url' => $apiUrl ?? 'N/A'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to API server: ' . $e->getMessage(),
            ], 503);
        } catch (\Exception $e) {
            Log::error('Register user error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration error: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Unregister user from device
     */
    public function unregisterUser(Request $request, $userId)
    {
        // Convert string values to proper types before validation
        $data = $request->all();
        if (isset($data['port'])) {
            $data['port'] = is_numeric($data['port']) ? (int)$data['port'] : null;
        }
        if (isset($data['password'])) {
            $data['password'] = is_numeric($data['password']) ? (int)$data['password'] : null;
        }

        $validator = Validator::make($data, [
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'password' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($userId);

            if (!$user->enroll_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have an enroll_id.',
                ], 422);
            }

            Log::info('Attempting to unregister user from device', [
                'user_id' => $userId,
                'enroll_id' => $user->enroll_id,
                'name' => $user->name,
                'ip' => $data['ip'],
                'port' => $data['port'],
                'password' => $data['password']
            ]);

            $zkteco = new ZKTecoServiceNew(
                $data['ip'],
                $data['port'] ?? 4370,
                $data['password'] ?? 0
            );

            $connected = $zkteco->connect();
            
            if (!$connected) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check device IP, port, and password.',
                ], 422);
            }

            $result = $zkteco->unregisterUser((int)$user->enroll_id);
            
            $zkteco->disconnect();

            if ($result) {
                $user->registered_on_device = false;
                $user->device_registered_at = null;
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'User unregistered from device successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to unregister user from device. User may still exist on device.',
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('Unregister user error', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error unregistering user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync users from device to system via API
     * Uses external API endpoint to get all registered users
     */
    public function syncUsersFromDevice(Request $request)
    {
        // Use exact API endpoint as defined
        $apiUrl = "http://192.168.100.100:8000/api/v1/users";
        $timeout = config('zkteco.timeout', 60); // Increased timeout for external API calls
        
        try {
            Log::info('Syncing users from device via external API', [
                'ip' => $request->ip(),
                'api_url' => $apiUrl,
            ]);

            // Make HTTP request to external API
            $response = Http::timeout($timeout)->get($apiUrl, [
                'registered' => 'true', // Get registered users
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to fetch users from external API',
                    'status_code' => $response->status()
                ], $response->status() ?: 422);
            }

            $responseData = $response->json();

            if (!($responseData['success'] ?? false) || !isset($responseData['data'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API response format',
                    'response' => $responseData
                ], 422);
            }

            $apiUsers = $responseData['data'];
            $total = is_array($apiUsers) ? count($apiUsers) : 0;
            $synced = 0;
            $updated = 0;
            $errors = 0;

            // Users are already in the system via API
            // This method now just returns the list of registered users
            Log::info('Users retrieved from external API', [
                'total_users' => $total,
                'api_url' => $apiUrl
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Users synced successfully from external API',
                'synced' => $synced,
                'updated' => $updated,
                'errors' => $errors,
                'total' => $total,
                'data' => $apiUsers
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('API connection error during user sync', [
                'error' => $e->getMessage(),
                'api_url' => $apiUrl ?? 'N/A'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to API server: ' . $e->getMessage(),
            ], 503);
        } catch (\Exception $e) {
            Log::error('Sync users from device via external API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Sync users from system to device via API
     * Uses /api/v1/users/register endpoint for each user
     */
    public function syncUsersToDevice(Request $request)
    {
        // Optional: specific user IDs to enroll
        $validator = Validator::make($request->all(), [
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get users to enroll
            $query = User::where('registered_on_device', false)
                        ->whereNotNull('enroll_id')
                        ->where('is_active', true)
                        ->whereHas('employee'); // Only employees

            // If specific user IDs provided, filter by them
            if ($request->has('user_ids') && !empty($request->user_ids)) {
                $query->whereIn('id', $request->user_ids);
            }

            $users = $query->get();

            if ($users->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No employees to enroll. All employees are already registered or missing Enroll ID.',
                    'registered' => 0,
                    'skipped' => 0,
                    'failed' => 0,
                ]);
            }

            $registered = 0;
            $failed = 0;
            $skipped = 0;

            // Use exact API endpoint as defined
            $apiUrl = "http://192.168.100.100:8000/api/v1/users/register";
            $timeout = config('zkteco.timeout', 60); // Increased timeout for external API calls
            
            foreach ($users as $user) {
                try {
                    if (!$user->enroll_id) {
                        $skipped++;
                        Log::info('Skipping user - no enroll_id', [
                            'user_id' => $user->id,
                            'name' => $user->name
                        ]);
                        continue;
                    }

                    // Check if user is already registered
                    if ($user->registered_on_device) {
                        $skipped++;
                        continue;
                    }

                    // Extract first name only (before first space)
                    $firstName = trim(explode(' ', $user->name)[0]);

                    Log::info('Registering user via external API (bulk)', [
                        'user_id' => $user->id,
                        'enroll_id' => $user->enroll_id,
                        'full_name' => $user->name,
                        'first_name' => $firstName,
                        'api_url' => $apiUrl
                    ]);
                    
                    // Make HTTP request to external API
                    $response = Http::timeout($timeout)->post($apiUrl, [
                        'id' => $user->enroll_id,
                        'name' => $firstName, // Send only first name
                    ]);

                    $responseData = $response->json();
                    $statusCode = $response->status();

                    if ($response->successful() && ($responseData['success'] ?? false)) {
                        // Update user registration status if not already set
                        if (!$user->registered_on_device) {
                            $user->registered_on_device = true;
                            $user->device_registered_at = Carbon::now();
                            $user->save();
                        }
                        $registered++;
                        
                        Log::info('User successfully registered via external API', [
                            'user_id' => $user->id,
                            'enroll_id' => $user->enroll_id,
                            'api_response' => $responseData
                        ]);
                    } else {
                        $failed++;
                        $errorMessage = $responseData['message'] ?? 'Failed to register user via API';
                        Log::warning('Failed to register user via external API', [
                            'user_id' => $user->id,
                            'enroll_id' => $user->enroll_id,
                            'error' => $errorMessage,
                            'api_response' => $responseData,
                            'status_code' => $statusCode
                        ]);
                    }
                    
                    // Small delay between API calls to avoid overwhelming the server
                    usleep(100000); // 0.1 seconds
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    $failed++;
                    Log::error('API connection error during bulk registration', [
                        'user_id' => $user->id,
                        'enroll_id' => $user->enroll_id ?? 'N/A',
                        'error' => $e->getMessage(),
                        'api_url' => $apiUrl
                    ]);
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Error registering user via external API', [
                        'user_id' => $user->id,
                        'enroll_id' => $user->enroll_id ?? 'N/A',
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully registered {$registered} employee(s) to device",
                'registered' => $registered,
                'skipped' => $skipped,
                'failed' => $failed,
                'total' => $users->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Sync users to device error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Sync attendance from device
     * Supports both direct device connection and API endpoint
     * If IP is provided, uses direct device connection (like enrollment page)
     * Otherwise, uses external API endpoint
     */
    public function syncAttendance(Request $request)
    {
        // If IP is provided, use direct device connection (like enrollment page operations)
        if ($request->filled('ip')) {
            return $this->syncAttendanceFromDeviceDirect($request);
        }
        
        // Otherwise, use API endpoint
        return $this->syncAttendanceFromApi($request);
    }
    
    /**
     * Sync attendance directly from device (like enrollment page operations)
     */
    private function syncAttendanceFromDeviceDirect(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'password' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ip = $request->ip;
            $port = $request->port ?? 4370;
            $password = $request->password ?? 0;

            Log::info('Syncing attendance directly from device (like enrollment page)', [
                'ip' => $ip,
                'port' => $port,
                'password' => $password
            ]);

            // Use ZKTecoService to sync directly from device (same as enrollment page)
            $zktecoService = new \App\Services\ZKTecoService($ip, $port, $password);
            
            // Sync attendances from device to database
            $syncResult = $zktecoService->syncAttendancesToDatabase();

            Log::info('Attendance sync from device completed', [
                'ip' => $ip,
                'synced' => $syncResult['synced'] ?? 0,
                'skipped' => $syncResult['skipped'] ?? 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance synced successfully from device',
                'synced' => $syncResult['synced'] ?? 0,
                'skipped' => $syncResult['skipped'] ?? 0,
                'users_verified' => $syncResult['users_verified'] ?? 0,
                'details' => $syncResult['details'] ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Error syncing attendance from device', [
                'ip' => $request->ip ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error syncing attendance from device: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Legacy method - kept for backward compatibility but redirects to API sync
     * @deprecated Use syncAttendanceFromApi instead
     */
    private function syncAttendanceFromDeviceLegacy(Request $request)
    {
        // Get device configuration from request or config
        $ip = $request->input('ip') ?? config('zkteco.ip');
        $port = $request->input('port') ?? config('zkteco.port', 4370);
        $password = $request->input('password') ?? config('zkteco.password', 0);
        
        // Validate IP address
        if (empty($ip)) {
            return response()->json([
                'success' => false,
                'message' => 'Device IP address is required. Please provide IP address in the request or configure it in config/zkteco.php',
            ], 422);
        }
        
        $service = null;
        $synced = 0;
        $skipped = 0;
        $errors = 0;
        
        try {
            Log::info('Syncing attendance directly from device (LEGACY - not recommended)', [
                'ip' => $ip,
                'port' => $port,
                'request_ip' => $request->ip(),
                'request_data' => $request->all()
            ]);

            // Initialize ZKTeco service
            $service = new ZKTecoServiceNew($ip, $port, $password);
            
            // Connect to device with timeout handling
            Log::info('Attempting to connect to device...', ['ip' => $ip, 'port' => $port]);
            $service->connect();
            Log::info('Successfully connected to device', ['ip' => $ip]);
            
            Log::info('Connected to device, fetching attendance records...', [
                'ip' => $ip
            ]);

            // Get attendance records directly from device
            Log::info('Fetching attendance records from device...', ['ip' => $ip]);
            
            $deviceAttendances = $service->getAttendance();
            
            if ($deviceAttendances === null || !is_array($deviceAttendances)) {
                Log::warning('Invalid attendance data returned from device', [
                    'ip' => $ip,
                    'type' => gettype($deviceAttendances),
                    'data' => $deviceAttendances
                ]);
                $deviceAttendances = [];
            }
            
            if (empty($deviceAttendances)) {
                Log::info('No attendance records found on device', ['ip' => $ip]);
                
                if ($service) {
                    $service->disconnect();
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'No attendance records found on device',
                    'synced' => 0,
                    'skipped' => 0,
                    'errors' => 0,
                    'total' => 0,
                ]);
            }

            $total = count($deviceAttendances);
            
            Log::info('Received attendance records from device', [
                'total_records' => $total,
                'device_ip' => $ip
            ]);

            // Group attendance records by user and date
            $groupedAttendances = [];
            
            foreach ($deviceAttendances as $attRecord) {
                $uid = $attRecord['uid'] ?? null;
                $timestamp = $attRecord['timestamp'] ?? null;
                
                if (!$uid || !$timestamp) {
                    $skipped++;
                    continue;
                }

                try {
                    $punchTime = Carbon::parse($timestamp);
                    $attendanceDate = $punchTime->format('Y-m-d');
                    
                    // Group by user_id and date
                    $key = $uid . '_' . $attendanceDate;
                    
                    if (!isset($groupedAttendances[$key])) {
                        $groupedAttendances[$key] = [
                            'uid' => $uid,
                            'date' => $attendanceDate,
                            'times' => []
                        ];
                    }
                    
                    $groupedAttendances[$key]['times'][] = $punchTime;
                    
                } catch (\Exception $e) {
                    $skipped++;
                    Log::warning('Error parsing attendance timestamp', [
                        'uid' => $uid,
                        'timestamp' => $timestamp,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Process grouped attendance records
            foreach ($groupedAttendances as $group) {
                try {
                    $uid = $group['uid'];
                    $attendanceDate = $group['date'];
                    $times = $group['times'];
                    
                    // Sort times chronologically
                    usort($times, function($a, $b) {
                        return $a->lt($b) ? -1 : 1;
                    });
                    
                    // Find user by enroll_id (uid from device)
                    $user = User::where('enroll_id', (string)$uid)
                        ->orWhere('enroll_id', (int)$uid)
                        ->first();
                    
                    if (!$user) {
                        $skipped++;
                        Log::warning('Skipping attendance - user not found', [
                            'uid' => $uid,
                            'enroll_id' => $uid,
                            'attendance_date' => $attendanceDate
                        ]);
                        continue;
                    }

                    // Find or create attendance record
                    $attendance = Attendance::where('user_id', $user->id)
                        ->where('attendance_date', $attendanceDate)
                        ->first();

                    if (!$attendance) {
                        $attendance = new Attendance();
                        $attendance->user_id = $user->id;
                        $attendance->employee_id = $user->employee?->id;
                        $attendance->enroll_id = (string)$uid;
                        $attendance->attendance_date = $attendanceDate;
                        $attendance->attendance_method = Attendance::METHOD_FINGERPRINT;
                        $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
                        $attendance->status = Attendance::STATUS_PRESENT;
                        $attendance->device_ip = $ip;
                        $attendance->verify_mode = 'Fingerprint';
                    }

                    // Set check-in time (earliest time of the day)
                    if (!empty($times)) {
                        $firstTime = $times[0];
                        if (!$attendance->check_in_time || $firstTime->lt($attendance->check_in_time)) {
                            $attendance->check_in_time = $firstTime;
                            $attendance->time_in = $firstTime;
                            $attendance->punch_time = $firstTime;
                        }
                    }

                    // Set check-out time (latest time of the day)
                    if (count($times) > 1) {
                        $lastTime = end($times);
                        if (!$attendance->check_out_time || $lastTime->gt($attendance->check_out_time)) {
                            $attendance->check_out_time = $lastTime;
                            $attendance->time_out = $lastTime;
                        }
                    } elseif (count($times) === 1) {
                        // Only one punch - could be check-in or check-out
                        // If we already have a check-in from earlier, this might be check-out
                        // Otherwise, treat as check-in only
                        $singleTime = $times[0];
                        if ($attendance->check_in_time && $singleTime->gt($attendance->check_in_time)) {
                            $attendance->check_out_time = $singleTime;
                            $attendance->time_out = $singleTime;
                        }
                    }

                    // Calculate total hours if both times are set
                    if ($attendance->check_in_time && $attendance->check_out_time) {
                        $attendance->total_hours = $attendance->calculateTotalHours();
                    }

                    // Check if late
                    if ($attendance->check_in_time) {
                        $attendance->is_late = $attendance->checkLate();
                    }

                    $attendance->save();

                    $synced++;
                    
                    Log::info('Attendance synced successfully from device', [
                        'user_id' => $user->id,
                        'enroll_id' => $uid,
                        'attendance_date' => $attendanceDate,
                        'check_in' => $attendance->check_in_time?->format('Y-m-d H:i:s'),
                        'check_out' => $attendance->check_out_time?->format('Y-m-d H:i:s'),
                        'punch_count' => count($times)
                    ]);

                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error syncing attendance record from device', [
                        'error' => $e->getMessage(),
                        'uid' => $group['uid'] ?? 'unknown',
                        'date' => $group['date'] ?? 'unknown',
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Disconnect from device
            if ($service) {
                $service->disconnect();
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$synced} attendance record(s) from device",
                'synced' => $synced,
                'skipped' => $skipped,
                'errors' => $errors,
                'total' => $total,
                'device_ip' => $ip
            ]);

        } catch (\Exception $e) {
            // Disconnect if still connected
            if ($service) {
                try {
                    $service->disconnect();
                } catch (\Exception $disconnectError) {
                    // Ignore disconnect errors
                }
            }

            Log::error('Sync attendance from device error', [
                'error' => $e->getMessage(),
                'ip' => $ip ?? 'N/A',
                'port' => $port ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error syncing attendance from device: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Test connection to device API
     * Diagnostic endpoint to check API connectivity
     */
    public function testDeviceApiConnection(Request $request)
    {
        $apiUrl = "http://192.168.100.100:8000/api/v1/attendances";
        $timeout = 10; // Shorter timeout for quick test
        
        $results = [
            'api_url' => $apiUrl,
            'tests' => []
        ];
        
        // Test 1: Basic connectivity (ping-like test)
        try {
            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            $startTime = microtime(true);
            $result = curl_exec($ch);
            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000, 2);
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                $results['tests']['connectivity'] = [
                    'status' => 'failed',
                    'message' => 'Connection failed: ' . $curlError,
                    'response_time_ms' => $responseTime
                ];
            } else {
                $results['tests']['connectivity'] = [
                    'status' => 'success',
                    'message' => "Connected successfully (HTTP {$httpCode})",
                    'response_time_ms' => $responseTime,
                    'http_code' => $httpCode
                ];
            }
        } catch (\Exception $e) {
            $results['tests']['connectivity'] = [
                'status' => 'failed',
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
        
        // Test 2: Full API request
        try {
            $response = Http::timeout($timeout)->get($apiUrl, ['page' => 1, 'per_page' => 1]);
            
            if ($response->successful()) {
                $data = $response->json();
                $results['tests']['api_request'] = [
                    'status' => 'success',
                    'message' => 'API responded successfully',
                    'has_data' => isset($data['data']),
                    'record_count' => count($data['data'] ?? [])
                ];
                $results['success'] = true;
            } else {
                $results['tests']['api_request'] = [
                    'status' => 'failed',
                    'message' => "API returned HTTP {$response->status()}",
                    'http_code' => $response->status(),
                    'response_body' => substr($response->body(), 0, 200)
                ];
                $results['success'] = false;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $results['tests']['api_request'] = [
                'status' => 'failed',
                'message' => 'Connection timeout: ' . $e->getMessage()
            ];
            $results['success'] = false;
        } catch (\Exception $e) {
            $results['tests']['api_request'] = [
                'status' => 'failed',
                'message' => 'Error: ' . $e->getMessage()
            ];
            $results['success'] = false;
        }
        
        // Test 3: DNS/Network check
        $host = parse_url($apiUrl, PHP_URL_HOST);
        $port = parse_url($apiUrl, PHP_URL_PORT) ?: 80;
        
        try {
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            if ($connection) {
                fclose($connection);
                $results['tests']['network'] = [
                    'status' => 'success',
                    'message' => "Port {$port} is open on {$host}"
                ];
            } else {
                $results['tests']['network'] = [
                    'status' => 'failed',
                    'message' => "Cannot connect to {$host}:{$port} - {$errstr} (Error {$errno})"
                ];
            }
        } catch (\Exception $e) {
            $results['tests']['network'] = [
                'status' => 'failed',
                'message' => 'Network test failed: ' . $e->getMessage()
            ];
        }
        
        return response()->json($results);
    }

    /**
     * Sync attendance from external API endpoint to local database
     * Data source: External API endpoint (http://192.168.100.100:8000/api/v1/attendances)
     * NOT from local device/computer - always from the external API endpoint
     * Fetches attendance from external API and saves only for users that exist in local system
     */
    public function syncAttendanceFromApi(Request $request)
    {
        try {
            $synced = 0;
            $skipped = 0;
            $errors = 0;
            $page = 1;
            $perPage = 50;
            $hasMore = true;
            
            // IMPORTANT: Data source is ALWAYS the external API endpoint, NOT local device
            // Use exact API endpoint as defined - this is the ONLY data source
            $apiUrl = "http://192.168.100.100:8000/api/v1/attendances";
            $timeout = config('zkteco.timeout', 60);
            
            Log::info('Starting attendance sync from external API endpoint (NOT from local device)', [
                'api_url' => $apiUrl,
                'data_source' => 'external_api_endpoint',
                'note' => 'Data is fetched from external API, not from local computer/device',
                'sync_mode' => $request->has('manual') ? 'manual' : 'automatic'
            ]);
            
            // Get all local users with enroll_id for quick lookup
            $localUsers = User::whereNotNull('enroll_id')
                ->get()
                ->keyBy(function($user) {
                    return (string)$user->enroll_id;
                });
            
            Log::info('Local users loaded for sync', [
                'total_users' => $localUsers->count()
            ]);
            
            // Fetch all pages from API
            while ($hasMore) {
                try {
                    Log::info('Fetching attendance page from API', [
                        'page' => $page,
                        'per_page' => $perPage
                    ]);
                    
                    $response = Http::timeout($timeout)->get($apiUrl, [
                        'page' => $page,
                        'per_page' => $perPage
                    ]);
                    
                    if (!$response->successful()) {
                        throw new \Exception("API returned status {$response->status()}: {$response->body()}");
                    }
                    
                    $data = $response->json();
                    
                    if (!isset($data['success']) || !$data['success'] || !isset($data['data'])) {
                        throw new \Exception("Invalid API response format");
                    }
                    
                    $attendances = $data['data'];
                    $pagination = $data['pagination'] ?? [];
                    
                    if (empty($attendances)) {
                        $hasMore = false;
                        break;
                    }
                    
                    Log::info('Processing attendance records from API', [
                        'page' => $page,
                        'records_count' => count($attendances)
                    ]);
                    
                    // Process each attendance record
                    foreach ($attendances as $attData) {
                        try {
                            $userData = $attData['user'] ?? [];
                            $enrollId = $userData['enroll_id'] ?? null;
                            
                            if (!$enrollId) {
                                $skipped++;
                                Log::warning('Skipping attendance - no enroll_id', [
                                    'attendance_id' => $attData['id'] ?? 'unknown'
                                ]);
                                continue;
                            }
                            
                            // Check if user exists in local system
                            $user = $localUsers->get((string)$enrollId);
                            
                            if (!$user) {
                                $skipped++;
                                Log::warning('Skipping attendance - user not found in local system', [
                                    'enroll_id' => $enrollId,
                                    'attendance_id' => $attData['id'] ?? 'unknown',
                                    'user_name' => $userData['name'] ?? 'unknown'
                                ]);
                                continue;
                            }
                            
                            // Parse attendance date and times
                            $attendanceDate = $attData['attendance_date'] ?? null;
                            $checkInTime = $attData['check_in_time'] ?? null;
                            $checkOutTime = $attData['check_out_time'] ?? null;
                            
                            if (!$attendanceDate) {
                                $skipped++;
                                continue;
                            }
                            
                            // Find or create attendance record
                            $attendance = Attendance::where('user_id', $user->id)
                                ->where('attendance_date', $attendanceDate)
                                ->first();
                            
                            if (!$attendance) {
                                $attendance = new Attendance();
                                $attendance->user_id = $user->id;
                                $attendance->employee_id = $user->employee?->id;
                                $attendance->enroll_id = (string)$enrollId;
                                $attendance->attendance_date = $attendanceDate;
                                $attendance->attendance_method = Attendance::METHOD_FINGERPRINT;
                                $attendance->verification_status = Attendance::VERIFICATION_VERIFIED; // Auto-verify device records
                                $attendance->status = Attendance::STATUS_PRESENT;
                                $attendance->device_ip = $attData['device_ip'] ?? null;
                                $attendance->verify_mode = $attData['verify_mode'] ?? 'Fingerprint';
                            } else {
                                // Ensure existing records from device are also verified
                                if ($attendance->device_ip || $attendance->verify_mode) {
                                    $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
                                }
                            }
                            
                            // Set check-in time (extract only time, not date)
                            if ($checkInTime) {
                                try {
                                    // Parse the datetime string
                                    $checkIn = Carbon::parse($checkInTime);
                                    
                                    // Extract only time (HH:MM:SS) and combine with attendance date
                                    $timeOnly = $checkIn->format('H:i:s');
                                    $checkInDateTime = Carbon::parse($attendanceDate . ' ' . $timeOnly);
                                    
                                    if (!$attendance->check_in_time || $checkInDateTime->lt($attendance->check_in_time)) {
                                        $attendance->check_in_time = $checkInDateTime;
                                        $attendance->time_in = $timeOnly; // Store only time (HH:MM:SS)
                                        $attendance->punch_time = $checkInDateTime;
                                        // Set status to PRESENT when checked in
                                        $attendance->status = Attendance::STATUS_PRESENT;
                                    }
                                } catch (\Exception $e) {
                                    Log::warning('Error parsing check_in_time', [
                                        'time' => $checkInTime,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                            
                            // Set check-out time (extract only time, not date)
                            if ($checkOutTime) {
                                try {
                                    // Parse the datetime string
                                    $checkOut = Carbon::parse($checkOutTime);
                                    
                                    // Extract only time (HH:MM:SS) and combine with attendance date
                                    $timeOnly = $checkOut->format('H:i:s');
                                    $checkOutDateTime = Carbon::parse($attendanceDate . ' ' . $timeOnly);
                                    
                                    if (!$attendance->check_out_time || $checkOutDateTime->gt($attendance->check_out_time)) {
                                        $attendance->check_out_time = $checkOutDateTime;
                                        $attendance->time_out = $timeOnly; // Store only time (HH:MM:SS)
                                    }
                                } catch (\Exception $e) {
                                    Log::warning('Error parsing check_out_time', [
                                        'time' => $checkOutTime,
                                        'error' => $e->getMessage()
                                    ]);
                                }
                            }
                            
                            // Calculate total hours if both times are set
                            if ($attendance->check_in_time && $attendance->check_out_time) {
                                try {
                                    $checkIn = Carbon::parse($attendance->check_in_time);
                                    $checkOut = Carbon::parse($attendance->check_out_time);
                                    
                                    // Handle overnight shifts
                                    if ($checkOut->lt($checkIn)) {
                                        $checkOut->addDay();
                                    }
                                    
                                    $totalMinutes = $checkOut->diffInMinutes($checkIn);
                                    $attendance->total_hours = $totalMinutes;
                                } catch (\Exception $e) {
                                    // Fallback to model method
                                    $attendance->total_hours = $attendance->calculateTotalHours();
                                }
                            }
                            
                            // Set status: If checked in, must be PRESENT (even if not checked out)
                            if ($attendance->check_in_time) {
                                $attendance->status = Attendance::STATUS_PRESENT;
                                // Check if late
                                $attendance->is_late = $attendance->checkLate();
                                if ($attendance->is_late) {
                                    $attendance->status = Attendance::STATUS_LATE;
                                }
                            } else {
                                // No check-in = Absent
                                $attendance->status = Attendance::STATUS_ABSENT;
                            }
                            
                            // Ensure device records are auto-verified
                            if ($attendance->device_ip || $attendance->verify_mode || $attendance->attendance_method === Attendance::METHOD_FINGERPRINT) {
                                $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
                            }
                            
                            // Check if this is a new check-in or check-out (before saving)
                            $hadCheckInBefore = $attendance->check_in_time ? true : false;
                            $hadCheckOutBefore = $attendance->check_out_time ? true : false;
                            
                            $attendance->save();
                            $synced++;
                            
                            // Refresh to get latest data
                            $attendance->refresh();
                            
                            // Check if this is a new check-in or check-out (after saving)
                            $isNewCheckIn = $checkInTime && (!$hadCheckInBefore || ($attendance->check_in_time && !$hadCheckInBefore));
                            $isNewCheckOut = $checkOutTime && (!$hadCheckOutBefore || ($attendance->check_out_time && !$hadCheckOutBefore));
                            
                            // Send SMS notifications for check-in/check-out
                            if ($isNewCheckIn || $isNewCheckOut) {
                                try {
                                    $notificationService = new \App\Services\NotificationService();
                                    $employeePhone = $user->mobile ?? $user->phone;
                                    
                                    if ($employeePhone) {
                                        // Get first name from user name
                                        $firstName = explode(' ', $user->name)[0];
                                        
                                        if ($isNewCheckIn && $attendance->check_in_time) {
                                            // Send check-in SMS with inspirational message
                                            $timeIn = $attendance->check_in_time->format('h:i A');
                                            $date = $attendance->attendance_date->format('M d, Y');
                                            
                                            // Get inspirational message based on time of day (without icons)
                                            $hour = (int)$attendance->check_in_time->format('H');
                                            $inspirationalMessage = '';
                                            if ($hour >= 5 && $hour < 12) {
                                                $morningMessages = [
                                                    "Good morning! Start your day with positivity and purpose.",
                                                    "Rise and shine! Today is a new opportunity to excel.",
                                                    "Morning! Let's make today productive and successful.",
                                                    "Good morning! Every day is a fresh start - make it count!",
                                                    "Morning! Set your goals and achieve them today!"
                                                ];
                                                $inspirationalMessage = $morningMessages[array_rand($morningMessages)];
                                            } else {
                                                $inspirationalMessage = "Have a productive day!";
                                            }
                                            
                                            $message = "Hello {$firstName},\n\nCheck-In Successful!\n\nTime: {$timeIn}\nDate: {$date}\n\n{$inspirationalMessage}" . 
                                                      ($attendance->is_late ? "\n\nNote: You are marked as LATE." : "");
                                            
                                            $notificationService->sendSMS($employeePhone, $message);
                                            Log::info('Check-in SMS sent to employee', [
                                                'user_id' => $user->id,
                                                'phone' => $employeePhone
                                            ]);
                                        }
                                        
                                        if ($isNewCheckOut && $attendance->check_out_time) {
                                            // Send check-out SMS with inspirational message
                                            $timeOut = $attendance->check_out_time->format('h:i A');
                                            $date = $attendance->attendance_date->format('M d, Y');
                                            
                                            // Calculate total hours
                                            $totalHours = $attendance->total_hours ? floor($attendance->total_hours / 60) : 0;
                                            $totalMinutes = $attendance->total_hours ? ($attendance->total_hours % 60) : 0;
                                            $hoursText = $totalHours > 0 ? "{$totalHours}h {$totalMinutes}m" : "{$totalMinutes}m";
                                            
                                            // Get inspirational message for check-out (without icons)
                                            $hour = (int)$attendance->check_out_time->format('H');
                                            $inspirationalMessage = '';
                                            if ($hour >= 17 && $hour < 22) {
                                                $eveningMessages = [
                                                    "Well done today! Rest well and recharge for tomorrow.",
                                                    "Great work today! Take time to relax and unwind.",
                                                    "Excellent effort! You've accomplished a lot today.",
                                                    "Outstanding work! Enjoy your evening and rest well.",
                                                    "Fantastic day! Thank you for your dedication!"
                                                ];
                                                $inspirationalMessage = $eveningMessages[array_rand($eveningMessages)];
                                            } else {
                                                $inspirationalMessage = "Thank you for your hard work!";
                                            }
                                            
                                            $message = "Hello {$firstName},\n\nCheck-Out Successful!\n\nTime: {$timeOut}\nDate: {$date}\nTotal Hours: {$hoursText}" .
                                                      ($attendance->is_early_leave ? "\n\nNote: You left early." : "") .
                                                      ($attendance->is_overtime ? "\nNote: You worked overtime - great dedication!" : "") .
                                                      "\n\n{$inspirationalMessage}";
                                            
                                            $notificationService->sendSMS($employeePhone, $message);
                                            Log::info('Check-out SMS sent to employee', [
                                                'user_id' => $user->id,
                                                'phone' => $employeePhone
                                            ]);
                                        }
                                    }
                                } catch (\Exception $smsError) {
                                    Log::warning('Failed to send SMS notification', [
                                        'user_id' => $user->id,
                                        'error' => $smsError->getMessage()
                                    ]);
                                }
                            }
                            
                            Log::info('Attendance synced from API', [
                                'user_id' => $user->id,
                                'enroll_id' => $enrollId,
                                'attendance_date' => $attendanceDate,
                                'attendance_id' => $attData['id'] ?? 'unknown'
                            ]);
                            
                        } catch (\Exception $e) {
                            $errors++;
                            Log::error('Error processing attendance record from API', [
                                'attendance_data' => $attData,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    }
                    
                    // Check if there are more pages
                    $currentPage = $pagination['current_page'] ?? $page;
                    $lastPage = $pagination['last_page'] ?? 1;
                    $hasMore = $currentPage < $lastPage;
                    $page++;
                    
                    // Safety limit - don't loop forever
                    if ($page > 1000) {
                        Log::warning('Reached page limit, stopping sync', ['page' => $page]);
                        break;
                    }
                    
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    Log::error('Connection error fetching attendance from API', [
                        'page' => $page,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                } catch (\Exception $e) {
                    Log::error('Error fetching attendance page from API', [
                        'page' => $page,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }
            
            Log::info('Attendance sync from API completed', [
                'synced' => $synced,
                'skipped' => $skipped,
                'errors' => $errors
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$synced} attendance record(s) from external API",
                'synced' => $synced,
                'skipped' => $skipped,
                'errors' => $errors,
                'total_processed' => $synced + $skipped + $errors
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error syncing attendance from API', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error syncing attendance from API: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance records from device API
     * Acts as a proxy to avoid CORS issues
     */
    public function getDeviceAttendance(Request $request)
    {
        try {
            $page = $request->input('page', 1);
            $perPage = $request->input('per_page', 50);
            
            Log::info('Fetching attendance from local database', [
                'page' => $page,
                'per_page' => $perPage
            ]);
            
            // Build query with relationships
            $query = Attendance::with(['user', 'user.employee'])
                ->orderBy('attendance_date', 'desc')
                ->orderBy('check_in_time', 'desc');
            
            // Paginate results
            $attendances = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Format response to match expected structure
            $formattedData = $attendances->map(function($attendance) {
                $user = $attendance->user;
                $employee = $user->employee ?? null;
                
                // Determine status - convert to string format expected by frontend
                $status = '1'; // Default to present
                if ($attendance->status === 'absent') {
                    $status = '0';
                } elseif ($attendance->status === 'late') {
                    $status = '1'; // Still present but late
                }
                
                return [
                    'id' => $attendance->id,
                    'user' => [
                        'id' => $user->id ?? null,
                        'name' => $user->name ?? 'Unknown',
                        'enroll_id' => $user->enroll_id ?? null,
                        'employee_id' => $employee->employee_id ?? null,
                    ],
                    'attendance_date' => $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : null,
                    'check_in_time' => $attendance->check_in_time ? $attendance->check_in_time->format('Y-m-d H:i:s') : null,
                    'check_out_time' => $attendance->check_out_time ? $attendance->check_out_time->format('Y-m-d H:i:s') : null,
                    'status' => $status,
                    'verify_mode' => $attendance->verify_mode ?? 'Fingerprint',
                    'device_ip' => $attendance->device_ip ?? null,
                ];
            });
            
            Log::info('Successfully fetched attendance from local database', [
                'total_records' => $attendances->total(),
                'current_page' => $attendances->currentPage(),
                'per_page' => $attendances->perPage()
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $formattedData,
                'pagination' => [
                    'current_page' => $attendances->currentPage(),
                    'total' => $attendances->total(),
                    'per_page' => $attendances->perPage(),
                    'last_page' => $attendances->lastPage(),
                    'from' => $attendances->firstItem(),
                    'to' => $attendances->lastItem(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching attendance from local database', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attendance: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get verify mode string from code
     */
    private function getVerifyMode($code)
    {
        $modes = [
            0 => 'Fingerprint',
            1 => 'Password',
            2 => 'Card',
            15 => 'Face',
        ];

        return $modes[$code] ?? 'Unknown';
    }

    /**
     * Check fingerprints for a user
     */
    public function checkFingerprints(Request $request, $userId)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'required|integer|min:1|max:65535',
            'password' => 'nullable|integer',
        ]);

        try {
            $user = User::findOrFail($userId);

            if (!$user->enroll_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have an enroll_id',
                ], 422);
            }

            $zkteco = new ZKTecoServiceNew(
                $request->ip,
                $request->port ?? 4370,
                $request->password ?? 0
            );

            $zkteco->connect();
            $count = $zkteco->getFingerprintCount((int)$user->enroll_id);
            $zkteco->disconnect();

            return response()->json([
                'success' => true,
                'fingerprint_count' => $count,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Capture users from ZKTeco device (direct connection)
     * Fetches all users registered on the device
     */
    public function captureUsersFromDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'password' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ip = $request->ip;
            $port = $request->port ?? 4370;
            $password = $request->password ?? 0;

            Log::info('Capturing users from ZKTeco device', [
                'ip' => $ip,
                'port' => $port,
                'password' => $password
            ]);

            $zkteco = new ZKTecoServiceNew($ip, $port, $password);
            
            // Connect to device
            $connected = $zkteco->connect();
            
            if (!$connected) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check device IP, port, and password.',
                ], 422);
            }

            // Get all users from device
            $deviceUsers = $zkteco->getUsers();
            
            $zkteco->disconnect();

            Log::info('Users captured from device', [
                'ip' => $ip,
                'total_users' => count($deviceUsers)
            ]);

            // Sanitize users array to ensure valid UTF-8 encoding
            $sanitizedUsers = array_map(function($user) {
                return [
                    'uid' => $user['uid'] ?? 0,
                    'name' => $this->sanitizeUtf8($user['name'] ?? ''),
                    'password' => $user['password'] ?? '',
                    'card' => $user['card'] ?? 0,
                    'role' => $user['role'] ?? 0,
                ];
            }, $deviceUsers);

            return response()->json([
                'success' => true,
                'message' => 'Users captured successfully from device',
                'total' => count($sanitizedUsers),
                'users' => $sanitizedUsers,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE);

        } catch (\Exception $e) {
            Log::error('Capture users from device error', [
                'ip' => $request->ip ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error capturing users from device: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Register user to ZKTeco device (direct connection)
     * Registers a user directly to the device using device connection
     */
    public function registerUserToDevice(Request $request, $userId)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'password' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::findOrFail($userId);

            if (!$user->enroll_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have an enroll_id. Please set enroll_id first.',
                ], 422);
            }

            $ip = $request->ip;
            $port = $request->port ?? 4370;
            $password = $request->password ?? 0;

            Log::info('Registering user to ZKTeco device (direct connection)', [
                'user_id' => $userId,
                'enroll_id' => $user->enroll_id,
                'name' => $user->name,
                'ip' => $ip,
                'port' => $port,
                'password' => $password
            ]);

            $zkteco = new ZKTecoServiceNew($ip, $port, $password);
            
            // Connect to device
            $connected = $zkteco->connect();
            
            if (!$connected) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check device IP, port, and password.',
                ], 422);
            }

            // Extract first name only (device name limit is 8 characters)
            $firstName = trim(explode(' ', $user->name)[0]);
            $firstName = substr($firstName, 0, 8); // Device limit

            // Register user to device
            $result = $zkteco->registerUser(
                (int)$user->enroll_id,
                $firstName,
                '', // password
                0,  // role (0 = user, 14 = admin)
                0   // card
            );
            
            $zkteco->disconnect();

            if ($result) {
                // Update user registration status
                $wasAlreadyRegistered = $user->registered_on_device;
                if (!$wasAlreadyRegistered) {
                    $user->registered_on_device = true;
                    $user->device_registered_at = Carbon::now();
                    $user->save();
                }

                // Send SMS notification if this is first time registration
                if (!$wasAlreadyRegistered) {
                    try {
                        $notificationService = new \App\Services\NotificationService();
                        $employeePhone = $user->mobile ?? $user->phone;
                        
                        if ($employeePhone) {
                            // Get first name from user name
                            $firstName = explode(' ', $user->name)[0];
                            $date = Carbon::now()->format('M d, Y');
                            
                            $message = "Hello {$firstName},\n\nYou have been successfully registered to the biometric attendance system.\n\nEnroll ID: {$user->enroll_id}\nDate: {$date}\n\nYou can now use your fingerprint to check in and check out. Please visit the device to enroll your fingerprint.";
                            
                            $notificationService->sendSMS($employeePhone, $message);
                            Log::info('Registration SMS sent to user (direct connection)', [
                                'user_id' => $userId,
                                'phone' => $employeePhone
                            ]);
                        }
                    } catch (\Exception $smsError) {
                        Log::warning('Failed to send registration SMS (direct connection)', [
                            'user_id' => $userId,
                            'error' => $smsError->getMessage()
                        ]);
                        // Don't fail the registration if SMS fails
                    }
                }

                Log::info('User registered successfully to device (direct connection)', [
                    'user_id' => $userId,
                    'enroll_id' => $user->enroll_id,
                    'name' => $user->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User registered to device successfully',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'enroll_id' => $user->enroll_id,
                    ]
                ]);
            } else {
                Log::warning('User registration failed (direct connection)', [
                    'user_id' => $userId,
                    'enroll_id' => $user->enroll_id,
                    'name' => $user->name
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to register user to device. User may not have been added to device.',
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error('Register user to device error (direct connection)', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error registering user to device: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sanitize UTF-8 string to remove invalid characters
     */
    private function sanitizeUtf8($string)
    {
        if ($string === null || $string === '') {
            return '';
        }

        // Convert to string
        $string = (string)$string;

        // Remove invalid UTF-8 characters using iconv
        $sanitized = @iconv('UTF-8', 'UTF-8//IGNORE', $string);
        
        // If iconv fails, use original string and try mb_convert_encoding
        if ($sanitized === false) {
            $sanitized = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
            $sanitized = @iconv('UTF-8', 'UTF-8//IGNORE', $sanitized);
            if ($sanitized === false) {
                $sanitized = $string; // Fallback to original
            }
        }
        
        $string = $sanitized;

        // Remove control characters (except newlines and tabs)
        $string = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $string);

        // Ensure valid UTF-8
        if (!mb_check_encoding($string, 'UTF-8')) {
            $string = mb_convert_encoding($string, 'UTF-8', 'ISO-8859-1');
            $string = @iconv('UTF-8', 'UTF-8//IGNORE', $string);
            if ($string === false) {
                $string = ''; // If all else fails, return empty
            }
        }

        return trim($string);
    }
}

