<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDevice;
use App\Services\ZKTecoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ZKTecoTestController extends Controller
{
    /**
     * Test device connection page
     */
    public function testConnection()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }
        
        return view('modules.hr.zkteco-test-connection');
    }

    /**
     * Register user to device page
     */
    public function registerUser()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }
        
        $devices = AttendanceDevice::where('is_active', true)->get();
        $employees = \App\Models\User::whereHas('employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('modules.hr.zkteco-register-user', compact('devices', 'employees'));
    }

    /**
     * Retrieve data from device page
     */
    public function retrieveData()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }
        
        $devices = AttendanceDevice::where('is_active', true)->get();
        
        return view('modules.hr.zkteco-retrieve-data', compact('devices'));
    }

    /**
     * API: Test connection
     */
    public function apiTestConnection(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'comm_key' => 'nullable|integer|min:0|max:65535',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ip = $request->ip_address;
            $port = $request->port ?? 4370;
            $commKey = $request->comm_key ?? 0;

            $zktecoService = new ZKTecoService($ip, $port, $commKey);
            
            // Test connection
            $connected = $zktecoService->connect();
            
            if (!$connected) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to device. Please check IP address, port, and network connectivity.'
                ], 400);
            }

            // Get device info
            $deviceInfo = $zktecoService->getDeviceInfo();
            $users = $zktecoService->getUsers();
            
            $zktecoService->disconnect();

            return response()->json([
                'success' => true,
                'message' => 'Connection successful!',
                'device_info' => $deviceInfo,
                'users_count' => count($users),
                'users' => array_slice($users, 0, 10) // First 10 users
            ]);
        } catch (\Exception $e) {
            Log::error('Test connection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Register user to device
     */
    public function apiRegisterUser(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'comm_key' => 'nullable|integer|min:0|max:65535',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $targetUser = \App\Models\User::with('employee')->findOrFail($request->user_id);
            
            // Ensure user has enroll_id
            if (!$targetUser->enroll_id) {
                if ($targetUser->employee && $targetUser->employee->employee_id) {
                    $enrollId = preg_replace('/[^0-9]/', '', $targetUser->employee->employee_id);
                    if (empty($enrollId)) {
                        $enrollId = (string)$targetUser->id;
                    }
                    $targetUser->enroll_id = $enrollId;
                    $targetUser->save();
                } else {
                    $targetUser->enroll_id = (string)$targetUser->id;
                    $targetUser->save();
                }
            }

            $ip = $request->ip_address;
            $port = $request->port ?? 4370;
            $commKey = $request->comm_key ?? 0;

            $zktecoService = new ZKTecoService($ip, $port, $commKey);
            
            // Connect
            if (!$zktecoService->connect()) {
                throw new \Exception('Failed to connect to device');
            }

            // Get or generate UID
            $uid = (int)$targetUser->enroll_id;
            if ($uid < 1 || $uid > 65535) {
                $uid = $targetUser->id % 65535;
                if ($uid < 1) $uid = 1;
            }

            // Register user
            $userName = substr($targetUser->name, 0, 24);
            $enrollId = (string)$targetUser->enroll_id;
            
            $result = $zktecoService->registerUser(
                $uid,
                $enrollId,
                $userName,
                '',
                0,
                0
            );

            if ($result) {
                $wasAlreadyRegistered = $targetUser->registered_on_device;
                $targetUser->registered_on_device = true;
                $targetUser->device_registered_at = \Carbon\Carbon::now();
                $targetUser->save();
                
                // Send SMS notification if this is first time registration
                if (!$wasAlreadyRegistered) {
                    try {
                        $notificationService = new \App\Services\NotificationService();
                        $employeePhone = $targetUser->mobile ?? $targetUser->phone;
                        
                        if ($employeePhone) {
                            // Get first name from user name
                            $firstName = explode(' ', $targetUser->name)[0];
                            $date = \Carbon\Carbon::now()->format('M d, Y');
                            
                            $message = "Hello {$firstName},\n\nYou have been successfully registered to the biometric attendance system.\n\nEnroll ID: {$enrollId}\nDate: {$date}\n\nYou can now use your fingerprint to check in and check out. Please visit the device to enroll your fingerprint.";
                            
                            $notificationService->sendSMS($employeePhone, $message);
                            Log::info('Registration SMS sent to user (test controller)', [
                                'user_id' => $targetUser->id,
                                'phone' => $employeePhone
                            ]);
                        }
                    } catch (\Exception $smsError) {
                        Log::warning('Failed to send registration SMS (test controller)', [
                            'user_id' => $targetUser->id,
                            'error' => $smsError->getMessage()
                        ]);
                        // Don't fail the registration if SMS fails
                    }
                }
            }

            $zktecoService->disconnect();

            return response()->json([
                'success' => $result,
                'message' => $result ? 'User registered successfully' : 'Failed to register user',
                'user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'enroll_id' => $enrollId,
                    'uid' => $uid
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Register user error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Retrieve data from device
     */
    public function apiRetrieveData(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'ip_address' => 'required|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'comm_key' => 'nullable|integer|min:0|max:65535',
            'data_type' => 'required|in:users,attendances,all',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $ip = $request->ip_address;
            $port = $request->port ?? 4370;
            $commKey = $request->comm_key ?? 0;
            $dataType = $request->data_type;

            $zktecoService = new ZKTecoService($ip, $port, $commKey);
            
            // Connect
            if (!$zktecoService->connect()) {
                throw new \Exception('Failed to connect to device');
            }

            $data = [];

            if ($dataType === 'users' || $dataType === 'all') {
                $users = $zktecoService->getUsers();
                $data['users'] = $users; // Return ALL users
                $data['users_count'] = count($users);
            }

            if ($dataType === 'attendances' || $dataType === 'all') {
                $attendances = $zktecoService->getAttendances();
                
                // Log raw data structure for debugging
                if (count($attendances) > 0) {
                    Log::info('Raw attendance data structure (first record):', [
                        'sample' => $attendances[0],
                        'total_count' => count($attendances)
                    ]);
                }
                
                // Format attendance data for better display
                $formattedAttendances = [];
                
                // First pass: Parse all data and prepare for sorting
                foreach ($attendances as $att) {
                    $formatted = [];
                    
                    // UID - sequential record number
                    $formatted['uid'] = $att['uid'] ?? $att['id'] ?? 'N/A';
                    
                    // User ID - actual enroll ID
                    $formatted['user_id'] = $att['user_id'] ?? $att['pin'] ?? $att['enroll_id'] ?? 'N/A';
                    
                    // Parse timestamp - handle multiple formats
                    $timestamp = null;
                    if (isset($att['record_time'])) {
                        $timestamp = $att['record_time'];
                    } elseif (isset($att['timestamp'])) {
                        $timestamp = $att['timestamp'];
                    } elseif (isset($att['time'])) {
                        $timestamp = $att['time'];
                    } elseif (isset($att['datetime'])) {
                        $timestamp = $att['datetime'];
                    }
                    
                    // Format date and time
                    $dateTime = null;
                    if ($timestamp) {
                        try {
                            // If it's a Unix timestamp
                            if (is_numeric($timestamp)) {
                                $dateTime = \Carbon\Carbon::createFromTimestamp($timestamp);
                            } else {
                                // Try parsing as string
                                $dateTime = \Carbon\Carbon::parse($timestamp);
                            }
                            $formatted['date'] = $dateTime->format('Y-m-d');
                            $formatted['time'] = $dateTime->format('H:i:s');
                            $formatted['datetime'] = $dateTime->format('Y-m-d H:i:s');
                            $formatted['timestamp'] = $dateTime->timestamp;
                        } catch (\Exception $e) {
                            $formatted['date'] = $timestamp;
                            $formatted['time'] = 'N/A';
                            $formatted['datetime'] = $timestamp;
                            $formatted['timestamp'] = null;
                        }
                    } else {
                        $formatted['date'] = 'N/A';
                        $formatted['time'] = 'N/A';
                        $formatted['datetime'] = 'N/A';
                        $formatted['timestamp'] = null;
                    }
                    
                    // Store original status from device for reference
                    if (isset($att['state'])) {
                        $formatted['original_status'] = $att['state'] == 1 ? 'Check Out' : 'Check In';
                        $formatted['original_status_code'] = $att['state'];
                    } elseif (isset($att['status'])) {
                        $formatted['original_status'] = is_numeric($att['status']) 
                            ? ($att['status'] == 1 ? 'Check Out' : 'Check In')
                            : $att['status'];
                        $formatted['original_status_code'] = is_numeric($att['status']) ? $att['status'] : null;
                    }
                    
                    // Type/Verify Mode
                    if (isset($att['type'])) {
                        $type = $att['type'];
                        if ($type == 0 || $type == 255) {
                            $formatted['type'] = 'Fingerprint';
                            $formatted['type_code'] = $type;
                        } else {
                            $formatted['type'] = 'Type ' . $type;
                            $formatted['type_code'] = $type;
                        }
                    } elseif (isset($att['verify'])) {
                        $formatted['type'] = $att['verify'];
                        $formatted['type_code'] = null;
                    } elseif (isset($att['verify_mode'])) {
                        $formatted['type'] = $att['verify_mode'];
                        $formatted['type_code'] = null;
                    } else {
                        $formatted['type'] = 'N/A';
                        $formatted['type_code'] = null;
                    }
                    
                    // Punch mode
                    if (isset($att['punch'])) {
                        $formatted['punch'] = $att['punch'];
                    } else {
                        $formatted['punch'] = 'N/A';
                    }
                    
                    // Device IP
                    if (isset($att['device_ip'])) {
                        $formatted['device_ip'] = $att['device_ip'];
                    }
                    
                    // Keep all original fields for reference
                    $formatted['raw'] = $att;
                    
                    // Store dateTime for sorting
                    $formatted['_sort_datetime'] = $dateTime ? $dateTime->timestamp : 0;
                    
                    $formattedAttendances[] = $formatted;
                }
                
                // Sort by user_id, date, and time to ensure proper chronological order
                usort($formattedAttendances, function($a, $b) {
                    // First sort by user_id
                    $userCompare = strcmp($a['user_id'], $b['user_id']);
                    if ($userCompare !== 0) {
                        return $userCompare;
                    }
                    
                    // Then by date
                    $dateCompare = strcmp($a['date'], $b['date']);
                    if ($dateCompare !== 0) {
                        return $dateCompare;
                    }
                    
                    // Finally by timestamp
                    return $a['_sort_datetime'] <=> $b['_sort_datetime'];
                });
                
                // Apply Check In/Out logic after sorting
                // First record per day per user = Check In, Second = Check Out, Third = Check In, etc.
                $userDateCounts = [];
                foreach ($formattedAttendances as &$formatted) {
                    $userDateKey = $formatted['user_id'] . '_' . $formatted['date'];
                    
                    if (!isset($userDateCounts[$userDateKey])) {
                        $userDateCounts[$userDateKey] = 0;
                    }
                    $userDateCounts[$userDateKey]++;
                    
                    // Odd number (1st, 3rd, 5th...) = Check In
                    // Even number (2nd, 4th, 6th...) = Check Out
                    $recordNumber = $userDateCounts[$userDateKey];
                    if ($recordNumber % 2 == 1) {
                        $formatted['status'] = 'Check In';
                        $formatted['status_code'] = 0;
                    } else {
                        $formatted['status'] = 'Check Out';
                        $formatted['status_code'] = 1;
                    }
                    
                    // Remove sorting helper
                    unset($formatted['_sort_datetime']);
                }
                
                $data['attendances'] = $formattedAttendances; // Return ALL formatted attendances
                $data['attendances_count'] = count($formattedAttendances);
            }

            $deviceInfo = $zktecoService->getDeviceInfo();
            $data['device_info'] = $deviceInfo;

            $zktecoService->disconnect();

            return response()->json([
                'success' => true,
                'message' => 'Data retrieved successfully',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Retrieve data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve data: ' . $e->getMessage()
            ], 500);
        }
    }
}

