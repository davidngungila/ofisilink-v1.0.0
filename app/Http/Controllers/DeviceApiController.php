<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDevice;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeviceApiController extends Controller
{
    /**
     * API Authentication - Verify device token/API key
     */
    private function authenticateDevice(Request $request)
    {
        // Get API key from header or request
        $apiKey = $request->header('X-API-Key') ?? $request->input('api_key');
        $deviceId = $request->header('X-Device-ID') ?? $request->input('device_id');
        
        // Find device by ID or IP
        $device = null;
        if ($deviceId) {
            $device = AttendanceDevice::where('device_id', $deviceId)
                ->orWhere('ip_address', $request->ip())
                ->first();
        } else {
            $device = AttendanceDevice::where('ip_address', $request->ip())->first();
        }
        
        if (!$device) {
            return null;
        }
        
        // Verify API key if configured
        $settings = $device->settings ?? [];
        $deviceApiKey = $settings['api_key'] ?? null;
        
        if ($deviceApiKey && $apiKey !== $deviceApiKey) {
            return null;
        }
        
        return $device;
    }

    /**
     * ============================================
     * RECEIVING DATA FROM DEVICE (Push API)
     * ============================================
     */

    /**
     * Receive attendance record from device (Push API)
     * POST /api/device/attendance/push
     */
    public function receiveAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'employee_id' => 'required|string',
            'check_time' => 'required|date',
            'check_type' => 'nullable|in:I,O,0,1', // I=In, O=Out, 0=In, 1=Out
            'verify_code' => 'nullable|integer', // Verification method
            'work_code' => 'nullable|integer',
            'sn' => 'nullable|string', // Device serial number
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Find device
            $device = AttendanceDevice::where('device_id', $request->device_id)
                ->orWhere('ip_address', $request->ip())
                ->orWhere('serial_number', $request->sn)
                ->first();

            if (!$device) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device not found or not registered',
                ], 404);
            }

            // Find user by employee ID
            $user = User::where('employee_id', $request->employee_id)
                ->orWhereHas('employee', function($q) use ($request) {
                    $q->where('employee_number', $request->employee_id);
                })
                ->first();

            if (!$user) {
                Log::warning('Device API: User not found', [
                    'employee_id' => $request->employee_id,
                    'device_id' => $device->id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found',
                    'employee_id' => $request->employee_id,
                ], 404);
            }

            // Parse check time
            $checkTime = Carbon::parse($request->check_time);
            $attendanceDate = $checkTime->format('Y-m-d');
            $time = $checkTime->format('H:i:s');

            // Determine if time in or out
            $checkType = $request->check_type ?? 'I';
            $isTimeIn = in_array($checkType, ['I', '0', 0]);

            // Get or create attendance record
            $attendance = Attendance::where('user_id', $user->id)
                ->where('attendance_date', $attendanceDate)
                ->first();

            if (!$attendance) {
                $attendance = new Attendance();
                $attendance->user_id = $user->id;
                $attendance->employee_id = $user->employee?->id;
                $attendance->attendance_date = $attendanceDate;
            }

            // Set time in or out
            if ($isTimeIn) {
                $attendance->time_in = $time;
                $attendance->status = Attendance::STATUS_PRESENT;
                $attendance->is_late = $attendance->checkLate();
            } else {
                $attendance->time_out = $time;
                if ($attendance->time_in) {
                    $attendance->total_hours = $attendance->calculateTotalHours();
                }
            }

            // Set device info
            $attendance->device_id = $device->id;
            $attendance->attendance_method = Attendance::METHOD_BIOMETRIC;
            $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
            $attendance->metadata = [
                'device_serial' => $request->sn ?? $device->serial_number,
                'verify_code' => $request->verify_code,
                'work_code' => $request->work_code,
                'check_type' => $checkType,
                'device_timestamp' => $request->check_time,
                'received_at' => Carbon::now()->toIso8601String(),
            ];
            $attendance->ip_address = $request->ip();
            $attendance->save();

            // Update device last sync
            $device->last_sync_at = Carbon::now();
            $device->is_online = true;
            $device->save();

            Log::info('Device API: Attendance received', [
                'device_id' => $device->id,
                'employee_id' => $request->employee_id,
                'check_time' => $request->check_time,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'employee_id' => $request->employee_id,
                    'check_time' => $checkTime->toIso8601String(),
                    'check_type' => $isTimeIn ? 'in' : 'out',
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('Device API: Error receiving attendance', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Receive batch attendance records
     * POST /api/device/attendance/batch
     */
    public function receiveBatchAttendance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'records' => 'required|array|min:1',
            'records.*.employee_id' => 'required|string',
            'records.*.check_time' => 'required|date',
            'records.*.check_type' => 'nullable|in:I,O,0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $device = AttendanceDevice::where('device_id', $request->device_id)
            ->orWhere('ip_address', $request->ip())
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($request->records as $index => $record) {
            try {
                // Create a new request for each record
                $recordRequest = new Request($record);
                $recordRequest->merge(['device_id' => $request->device_id]);
                
                $response = $this->receiveAttendance($recordRequest);
                $responseData = json_decode($response->getContent(), true);
                
                if ($responseData['success'] ?? false) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'index' => $index,
                        'employee_id' => $record['employee_id'] ?? null,
                        'error' => $responseData['message'] ?? 'Unknown error',
                    ];
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'index' => $index,
                    'employee_id' => $record['employee_id'] ?? null,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Processed {$results['success']} records, {$results['failed']} failed",
            'results' => $results,
        ], 200);
    }

    /**
     * Receive device status/heartbeat
     * POST /api/device/status
     */
    public function receiveDeviceStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string',
            'status' => 'required|in:online,offline,error',
            'battery_level' => 'nullable|integer|min:0|max:100',
            'storage_usage' => 'nullable|integer|min:0|max:100',
            'total_users' => 'nullable|integer',
            'total_records' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $device = AttendanceDevice::where('device_id', $request->device_id)
            ->orWhere('ip_address', $request->ip())
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        // Update device status
        $device->is_online = ($request->status === 'online');
        $device->last_sync_at = Carbon::now();
        
        // Store status in metadata
        $settings = $device->settings ?? [];
        $settings['device_status'] = [
            'status' => $request->status,
            'battery_level' => $request->battery_level,
            'storage_usage' => $request->storage_usage,
            'total_users' => $request->total_users,
            'total_records' => $request->total_records,
            'last_update' => Carbon::now()->toIso8601String(),
        ];
        $device->settings = $settings;
        $device->save();

        return response()->json([
            'success' => true,
            'message' => 'Device status updated',
        ], 200);
    }

    /**
     * ============================================
     * SENDING DATA TO DEVICE (Pull API)
     * ============================================
     */

    /**
     * Get employees/users to sync to device
     * GET /api/device/users/{device_id}
     */
    public function getUsersForDevice(Request $request, $deviceId)
    {
        $device = AttendanceDevice::where('device_id', $deviceId)
            ->orWhere('id', $deviceId)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        // Get all active employees
        $employees = User::with('employee')
            ->whereHas('employee', function($q) {
                $q->where('status', 'active');
            })
            ->get();

        $users = [];
        foreach ($employees as $user) {
            $users[] = [
                'user_id' => $user->employee_id ?? $user->employee?->employee_number ?? $user->id,
                'name' => $user->name,
                'employee_number' => $user->employee_id ?? $user->employee?->employee_number,
                'department' => $user->employee?->department?->name ?? 'N/A',
                'privilege' => $user->hasRole('System Admin') ? 14 : 0, // 14=Admin, 0=User
                'enabled' => true,
            ];
        }

        return response()->json([
            'success' => true,
            'device_id' => $device->device_id,
            'device_name' => $device->name,
            'total_users' => count($users),
            'users' => $users,
        ], 200);
    }

    /**
     * Get specific user data
     * GET /api/device/users/{device_id}/{employee_id}
     */
    public function getUserForDevice(Request $request, $deviceId, $employeeId)
    {
        $device = AttendanceDevice::where('device_id', $deviceId)
            ->orWhere('id', $deviceId)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        $user = User::where('employee_id', $employeeId)
            ->orWhereHas('employee', function($q) use ($employeeId) {
                $q->where('employee_number', $employeeId);
            })
            ->with('employee.department')
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'user_id' => $user->employee_id ?? $user->employee?->employee_number ?? $user->id,
                'name' => $user->name,
                'employee_number' => $user->employee_id ?? $user->employee?->employee_number,
                'department' => $user->employee?->department?->name ?? 'N/A',
                'privilege' => $user->hasRole('System Admin') ? 14 : 0,
                'enabled' => true,
            ],
        ], 200);
    }

    /**
     * Get server time for device sync
     * GET /api/device/time/{device_id}
     */
    public function getServerTime(Request $request, $deviceId)
    {
        $device = AttendanceDevice::where('device_id', $deviceId)
            ->orWhere('id', $deviceId)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        $now = Carbon::now();

        return response()->json([
            'success' => true,
            'server_time' => $now->toIso8601String(),
            'server_time_unix' => $now->timestamp,
            'timezone' => config('app.timezone', 'UTC'),
            'date' => $now->format('Y-m-d'),
            'time' => $now->format('H:i:s'),
        ], 200);
    }

    /**
     * Get device commands/actions
     * GET /api/device/commands/{device_id}
     */
    public function getDeviceCommands(Request $request, $deviceId)
    {
        $device = AttendanceDevice::where('device_id', $deviceId)
            ->orWhere('id', $deviceId)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        // Get pending commands from settings
        $settings = $device->settings ?? [];
        $commands = $settings['pending_commands'] ?? [];

        // Clear commands after reading
        if (!empty($commands)) {
            unset($settings['pending_commands']);
            $device->settings = $settings;
            $device->save();
        }

        return response()->json([
            'success' => true,
            'device_id' => $device->device_id,
            'commands' => $commands,
        ], 200);
    }

    /**
     * Send command to device (store for device to pull)
     * POST /api/device/commands/{device_id}
     */
    public function sendDeviceCommand(Request $request, $deviceId)
    {
        $validator = Validator::make($request->all(), [
            'command' => 'required|string|in:sync_time,restart,clear_data,clear_users,clear_attendance,update_firmware',
            'parameters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $device = AttendanceDevice::where('device_id', $deviceId)
            ->orWhere('id', $deviceId)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found',
            ], 404);
        }

        // Store command in device settings
        $settings = $device->settings ?? [];
        if (!isset($settings['pending_commands'])) {
            $settings['pending_commands'] = [];
        }

        $settings['pending_commands'][] = [
            'command' => $request->command,
            'parameters' => $request->parameters ?? [],
            'created_at' => Carbon::now()->toIso8601String(),
        ];

        $device->settings = $settings;
        $device->save();

        return response()->json([
            'success' => true,
            'message' => 'Command queued successfully',
            'command' => $request->command,
        ], 200);
    }

    /**
     * Health check endpoint
     * GET /api/device/health
     */
    public function healthCheck()
    {
        return response()->json([
            'success' => true,
            'status' => 'healthy',
            'timestamp' => Carbon::now()->toIso8601String(),
            'version' => config('app.version', '1.0.0'),
        ], 200);
    }
}






