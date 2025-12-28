<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLocation;
use App\Models\AttendanceDevice;
use App\Models\WorkSchedule;
use App\Models\AttendancePolicy;
use App\Models\Department;
use App\Models\User;
use App\Models\Attendance;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceSettingsController extends Controller
{
    /**
     * Display attendance settings management page
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }
        
        $locations = AttendanceLocation::with(['creator', 'devices', 'workSchedules', 'policies'])->get();
        $devices = AttendanceDevice::with(['location', 'creator'])->get();
        $schedules = WorkSchedule::with(['location', 'department', 'creator'])->get();
        $policies = AttendancePolicy::with(['location', 'department', 'creator'])->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        // Load employees for enrollment
        // Auto-generate enroll_id from employee_id if not set
        $employees = User::with(['employee', 'primaryDepartment'])
            ->whereHas('employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Auto-generate enroll_id from employee_id for employees without enroll_id
        foreach ($employees as $employee) {
            if (!$employee->enroll_id && $employee->employee && $employee->employee->employee_id) {
                // Extract numeric part from employee_id (e.g., "EMP20251107DU" -> "20251107")
                $enrollId = preg_replace('/[^0-9]/', '', $employee->employee->employee_id);
                if (empty($enrollId)) {
                    // If no numeric part, use user id
                    $enrollId = (string)$employee->id;
                }
                
                // Check if enroll_id already exists
                $exists = User::where('enroll_id', $enrollId)->where('id', '!=', $employee->id)->exists();
                if ($exists) {
                    // Append user id to make it unique
                    $enrollId = $enrollId . $employee->id;
                }
                
                // Save enroll_id
                $employee->enroll_id = $enrollId;
                $employee->save();
            }
        }
        
        // Reload employees with updated enroll_id
        $employees = User::with(['employee', 'primaryDepartment'])
            ->whereHas('employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Statistics
        $stats = [
            'total_locations' => $locations->count(),
            'active_locations' => $locations->where('is_active', true)->count(),
            'total_devices' => $devices->count(),
            'active_devices' => $devices->where('is_active', true)->count(),
            'online_devices' => $devices->where('is_online', true)->count(),
            'total_schedules' => $schedules->count(),
            'active_schedules' => $schedules->where('is_active', true)->count(),
            'total_policies' => $policies->count(),
            'active_policies' => $policies->where('is_active', true)->count(),
        ];
        
        // Calculate enrollment stats
        $stats['total_employees'] = $employees->count();
        $stats['enrolled_employees'] = $employees->where('registered_on_device', true)->count();
        
        // Use index view (dashboard)
        return view('modules.hr.attendance-settings-index', compact(
            'locations',
            'devices',
            'schedules',
            'policies',
            'departments',
            'employees',
            'stats'
        ));
    }

    /**
     * Display devices management page
     */
    public function devices()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }
        
        $devices = AttendanceDevice::with(['location', 'creator'])->get();
        $locations = AttendanceLocation::where('is_active', true)->orderBy('name')->get();
        
        $stats = [
            'total_devices' => $devices->count(),
            'active_devices' => $devices->where('is_active', true)->count(),
            'online_devices' => $devices->where('is_online', true)->count(),
        ];
        
        return view('modules.hr.attendance-settings-devices', compact('devices', 'stats', 'locations'));
    }

    /**
     * Get devices list (API endpoint)
     */
    public function getDevices()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $devices = AttendanceDevice::with(['location'])
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'devices' => $devices
        ]);
    }

    /**
     * Get a single device (API endpoint)
     */
    public function getDevice($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $device = AttendanceDevice::with(['location', 'creator', 'updater'])->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'device' => $device
        ]);
    }

    /**
     * Display enrollment management page
     */
    public function enrollment()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        // Load employees for enrollment
        // Auto-generate enroll_id from employee_id if not set
        $employees = User::with(['employee', 'primaryDepartment'])
            ->whereHas('employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Auto-generate enroll_id from employee_id for employees without enroll_id
        foreach ($employees as $employee) {
            if (!$employee->enroll_id && $employee->employee && $employee->employee->employee_id) {
                // Extract numeric part from employee_id (e.g., "EMP20251107DU" -> "20251107")
                $enrollId = preg_replace('/[^0-9]/', '', $employee->employee->employee_id);
                if (empty($enrollId)) {
                    // If no numeric part, use user id
                    $enrollId = (string)$employee->id;
                }
                
                // Check if enroll_id already exists
                $exists = User::where('enroll_id', $enrollId)->where('id', '!=', $employee->id)->exists();
                if ($exists) {
                    // Append user id to make it unique
                    $enrollId = $enrollId . $employee->id;
                }
                
                // Save enroll_id
                $employee->enroll_id = $enrollId;
                $employee->save();
            }
        }
        
        // Reload employees with updated enroll_id
        $employees = User::with(['employee', 'primaryDepartment'])
            ->whereHas('employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'email' => $emp->email,
                    'enroll_id' => $emp->enroll_id,
                    'registered_on_device' => $emp->registered_on_device ?? false,
                    'device_registered_at' => $emp->device_registered_at ? $emp->device_registered_at->toIso8601String() : null,
                    'employee' => [
                        'employee_id' => $emp->employee->employee_id ?? null,
                        'department_id' => $emp->employee->department_id ?? null,
                    ],
                    'primary_department' => [
                        'id' => $emp->primaryDepartment->id ?? null,
                        'name' => $emp->primaryDepartment->name ?? null
                    ],
                ];
            });
        
        // Get first active device for enrollment connection settings
        $device = AttendanceDevice::where('is_active', true)
            ->whereNotNull('ip_address')
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Extract device connection details from database, fallback to config
        if ($device) {
            $deviceIp = $device->ip_address ?? config('zkteco.ip', '192.168.100.108');
            $devicePort = $device->port ?? config('zkteco.port', 4370);
            $deviceCommKey = isset($device->settings['comm_key']) ? $device->settings['comm_key'] : config('zkteco.password', 0);
        } else {
            $deviceIp = config('zkteco.ip', '192.168.100.108');
            $devicePort = config('zkteco.port', 4370);
            $deviceCommKey = config('zkteco.password', 0);
        }
        
        return view('modules.hr.attendance-settings-enrollment', compact('employees', 'departments', 'deviceIp', 'devicePort', 'deviceCommKey'));
    }

    /**
     * Display schedules management page
     */
    public function schedules()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }
        
        $schedules = WorkSchedule::with(['location', 'department', 'creator'])->get();
        $locations = AttendanceLocation::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        $stats = [
            'total_schedules' => $schedules->count(),
            'active_schedules' => $schedules->where('is_active', true)->count(),
        ];
        
        return view('modules.hr.attendance-settings-schedules', compact('schedules', 'locations', 'departments', 'stats'));
    }

    /**
     * Display policies management page
     */
    public function policies()
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }
        
        $policies = AttendancePolicy::with(['location', 'department', 'creator'])->get();
        $locations = AttendanceLocation::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        $stats = [
            'total_policies' => $policies->count(),
            'active_policies' => $policies->where('is_active', true)->count(),
        ];
        
        return view('modules.hr.attendance-settings-policies', compact('policies', 'locations', 'departments', 'stats'));
    }

    // ==================== LOCATIONS MANAGEMENT ====================

    /**
     * Get all locations (for AJAX)
     */
    public function getLocations()
    {
        try {
            $locations = AttendanceLocation::with(['creator'])
                ->orderBy('name')
                ->get();
            
            return response()->json([
                'success' => true,
                'locations' => $locations,
            ]);
        } catch (\Exception $e) {
            Log::error('Get locations error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load locations.',
            ], 500);
        }
    }

    /**
     * Store a new location
     */
    public function storeLocation(Request $request)
    {
        // Convert boolean fields properly before validation
        $data = $request->all();
        $data['is_active'] = $this->convertToBoolean($request->is_active, true);
        $data['require_gps'] = $this->convertToBoolean($request->require_gps, false);
        $data['allow_remote'] = $this->convertToBoolean($request->allow_remote, false);
        
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:attendance_locations,code',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:10|max:10000',
            'is_active' => 'boolean',
            'require_gps' => 'boolean',
            'allow_remote' => 'boolean',
            'allowed_methods' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $location = AttendanceLocation::create([
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'radius_meters' => $data['radius_meters'] ?? 100,
                'is_active' => $data['is_active'],
                'require_gps' => $data['require_gps'],
                'allow_remote' => $data['allow_remote'],
                'allowed_methods' => $data['allowed_methods'] ?? null,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location created successfully.',
                'location' => $location->load('creator'),
            ]);
        } catch (\Exception $e) {
            Log::error('Create location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create location.',
            ], 500);
        }
    }

    /**
     * Update a location
     */
    public function updateLocation(Request $request, $id)
    {
        $location = AttendanceLocation::findOrFail($id);
        
        // Convert boolean fields properly before validation
        $data = $request->all();
        $data['is_active'] = $this->convertToBoolean($request->is_active, $location->is_active);
        $data['require_gps'] = $this->convertToBoolean($request->require_gps, $location->require_gps);
        $data['allow_remote'] = $this->convertToBoolean($request->allow_remote, $location->allow_remote);
        
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:attendance_locations,code,' . $id,
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'radius_meters' => 'nullable|integer|min:10|max:10000',
            'is_active' => 'boolean',
            'require_gps' => 'boolean',
            'allow_remote' => 'boolean',
            'allowed_methods' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $location->update([
                'name' => $data['name'],
                'code' => strtoupper($data['code']),
                'description' => $data['description'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'radius_meters' => $data['radius_meters'] ?? 100,
                'is_active' => $data['is_active'],
                'require_gps' => $data['require_gps'],
                'allow_remote' => $data['allow_remote'],
                'allowed_methods' => $data['allowed_methods'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location updated successfully.',
                'location' => $location->load('creator', 'updater'),
            ]);
        } catch (\Exception $e) {
            Log::error('Update location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update location.',
            ], 500);
        }
    }

    /**
     * Delete a location
     */
    public function deleteLocation($id)
    {
        $location = AttendanceLocation::findOrFail($id);
        
        // Check if location has devices or attendances
        if ($location->devices()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete location with associated devices.',
            ], 422);
        }

        $location->delete();

        return response()->json([
            'success' => true,
            'message' => 'Location deleted successfully.',
        ]);
    }

    // ==================== DEVICES MANAGEMENT ====================

    /**
     * Store a new device
     */
    public function storeDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'device_id' => 'required|string|max:255|unique:attendance_devices,device_id',
            'device_type' => 'required|string|in:biometric,rfid,fingerprint,face_recognition,card_swipe,mobile',
            'model' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'location_id' => 'nullable|exists:attendance_locations,id',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'port' => 'nullable|integer|min:1|max:65535',
            'connection_type' => 'required|string|in:network,usb,bluetooth,wifi',
            'connection_config' => 'nullable|array',
            'capabilities' => 'nullable|array',
            'settings' => 'nullable|array',
            'sync_interval_minutes' => 'nullable|integer|min:1|max:1440',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $device = AttendanceDevice::create([
                'name' => $request->name,
                'device_id' => $request->device_id,
                'device_type' => $request->device_type,
                'model' => $request->model,
                'manufacturer' => $request->manufacturer,
                'serial_number' => $request->serial_number,
                'location_id' => $request->location_id,
                'ip_address' => $request->ip_address,
                'mac_address' => $request->mac_address,
                'port' => $request->port,
                'connection_type' => $request->connection_type,
                'connection_config' => $request->connection_config,
                'capabilities' => $request->capabilities,
                'settings' => $request->settings,
                'sync_interval_minutes' => $request->sync_interval_minutes ?? 5,
                'notes' => $request->notes,
                'is_active' => $request->is_active ?? true,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device created successfully.',
                'device' => $device->load('location', 'creator'),
            ]);
        } catch (\Exception $e) {
            Log::error('Create device error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create device.',
            ], 500);
        }
    }

    /**
     * Update a device
     */
    public function updateDevice(Request $request, $id)
    {
        $device = AttendanceDevice::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'device_id' => 'required|string|max:255|unique:attendance_devices,device_id,' . $id,
            'device_type' => 'required|string|in:biometric,rfid,fingerprint,face_recognition,card_swipe,mobile',
            'model' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'location_id' => 'nullable|exists:attendance_locations,id',
            'ip_address' => 'nullable|ip',
            'mac_address' => 'nullable|string|max:17',
            'port' => 'nullable|integer|min:1|max:65535',
            'connection_type' => 'required|string|in:network,usb,bluetooth,wifi',
            'connection_config' => 'nullable|array',
            'capabilities' => 'nullable|array',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'sync_interval_minutes' => 'nullable|integer|min:1|max:1440',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $device->update([
                'name' => $request->name,
                'device_id' => $request->device_id,
                'device_type' => $request->device_type,
                'model' => $request->model,
                'manufacturer' => $request->manufacturer,
                'serial_number' => $request->serial_number,
                'location_id' => $request->location_id,
                'ip_address' => $request->ip_address,
                'mac_address' => $request->mac_address,
                'port' => $request->port,
                'connection_type' => $request->connection_type,
                'connection_config' => $request->connection_config,
                'capabilities' => $request->capabilities,
                'settings' => $request->settings,
                'is_active' => $request->is_active ?? true,
                'sync_interval_minutes' => $request->sync_interval_minutes ?? 5,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Device updated successfully.',
                'device' => $device->load('location', 'creator', 'updater'),
            ]);
        } catch (\Exception $e) {
            Log::error('Update device error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device.',
            ], 500);
        }
    }

    /**
     * Delete a device
     */
    public function deleteDevice($id)
    {
        $device = AttendanceDevice::findOrFail($id);
        $device->delete();

        return response()->json([
            'success' => true,
            'message' => 'Device deleted successfully.',
        ]);
    }

    /**
     * Test device connection
     */
    public function testDevice($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        try {
            $device = AttendanceDevice::findOrFail($id);
            
            // Test connection
            $isOnline = $this->testDeviceConnection($device);
            
            // Update device status
            if ($isOnline) {
                $device->is_online = true;
                $device->last_sync_at = Carbon::now();
                $device->save();
            } else {
                $device->is_online = false;
                $device->save();
            }
            
            // Log the test
            ActivityLogService::log(
                $isOnline ? 'test_success' : 'test_failed',
                "Device connection test: {$device->name} - " . ($isOnline ? 'Online' : 'Offline'),
                $device,
                ['ip_address' => $device->ip_address, 'is_online' => $isOnline]
            );
            
            return response()->json([
                'success' => true,
                'is_online' => $isOnline,
                'message' => $isOnline ? 'Device is online and connected' : 'Device is offline or unreachable',
                'last_sync' => $device->last_sync_at ? $device->last_sync_at->format('Y-m-d H:i:s') : null,
                'device' => $device,
            ]);
        } catch (\Exception $e) {
            Log::error('Test device error: ' . $e->getMessage());
            
            if (isset($device)) {
                ActivityLogService::log(
                    'test_error',
                    "Device connection test failed: {$device->name} - " . $e->getMessage(),
                    $device,
                    ['error' => $e->getMessage()]
                );
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to test device: ' . $e->getMessage()
            ], 500);
        }
    }

    // ==================== WORK SCHEDULES MANAGEMENT ====================

    /**
     * Store a new work schedule
     */
    public function storeSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:work_schedules,code',
            'description' => 'nullable|string',
            'location_id' => 'nullable|exists:attendance_locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'work_hours' => 'required|integer|min:1|max:24',
            'break_duration_minutes' => 'nullable|integer|min:0|max:480',
            'break_start_time' => 'nullable|date_format:H:i',
            'break_end_time' => 'nullable|date_format:H:i',
            'late_tolerance_minutes' => 'nullable|integer|min:0|max:120',
            'early_leave_tolerance_minutes' => 'nullable|integer|min:0|max:120',
            'overtime_threshold_minutes' => 'nullable|integer|min:0|max:120',
            'working_days' => 'nullable|array',
            'is_flexible' => 'boolean',
            'flexible_start_min' => 'nullable|date_format:H:i',
            'flexible_start_max' => 'nullable|date_format:H:i',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $schedule = WorkSchedule::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'location_id' => $request->location_id,
                'department_id' => $request->department_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'work_hours' => $request->work_hours,
                'break_duration_minutes' => $request->break_duration_minutes ?? 60,
                'break_start_time' => $request->break_start_time,
                'break_end_time' => $request->break_end_time,
                'late_tolerance_minutes' => $request->late_tolerance_minutes ?? 15,
                'early_leave_tolerance_minutes' => $request->early_leave_tolerance_minutes ?? 15,
                'overtime_threshold_minutes' => $request->overtime_threshold_minutes ?? 30,
                'working_days' => $request->working_days ?? [1,2,3,4,5], // Mon-Fri default
                'is_flexible' => $request->is_flexible ?? false,
                'flexible_start_min' => $request->flexible_start_min,
                'flexible_start_max' => $request->flexible_start_max,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work schedule created successfully.',
                'schedule' => $schedule->load('location', 'department', 'creator'),
            ]);
        } catch (\Exception $e) {
            Log::error('Create schedule error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create work schedule.',
            ], 500);
        }
    }

    /**
     * Update a work schedule
     */
    public function updateSchedule(Request $request, $id)
    {
        $schedule = WorkSchedule::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:work_schedules,code,' . $id,
            'description' => 'nullable|string',
            'location_id' => 'nullable|exists:attendance_locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'work_hours' => 'required|integer|min:1|max:24',
            'break_duration_minutes' => 'nullable|integer|min:0|max:480',
            'break_start_time' => 'nullable|date_format:H:i',
            'break_end_time' => 'nullable|date_format:H:i',
            'late_tolerance_minutes' => 'nullable|integer|min:0|max:120',
            'early_leave_tolerance_minutes' => 'nullable|integer|min:0|max:120',
            'overtime_threshold_minutes' => 'nullable|integer|min:0|max:120',
            'working_days' => 'nullable|array',
            'is_flexible' => 'boolean',
            'flexible_start_min' => 'nullable|date_format:H:i',
            'flexible_start_max' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $schedule->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'location_id' => $request->location_id,
                'department_id' => $request->department_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'work_hours' => $request->work_hours,
                'break_duration_minutes' => $request->break_duration_minutes ?? 60,
                'break_start_time' => $request->break_start_time,
                'break_end_time' => $request->break_end_time,
                'late_tolerance_minutes' => $request->late_tolerance_minutes ?? 15,
                'early_leave_tolerance_minutes' => $request->early_leave_tolerance_minutes ?? 15,
                'overtime_threshold_minutes' => $request->overtime_threshold_minutes ?? 30,
                'working_days' => $request->working_days ?? [1,2,3,4,5],
                'is_flexible' => $request->is_flexible ?? false,
                'flexible_start_min' => $request->flexible_start_min,
                'flexible_start_max' => $request->flexible_start_max,
                'is_active' => $request->is_active ?? true,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Work schedule updated successfully.',
                'schedule' => $schedule->load('location', 'department', 'creator', 'updater'),
            ]);
        } catch (\Exception $e) {
            Log::error('Update schedule error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update work schedule.',
            ], 500);
        }
    }

    /**
     * Delete a work schedule
     */
    public function deleteSchedule($id)
    {
        $schedule = WorkSchedule::findOrFail($id);
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Work schedule deleted successfully.',
        ]);
    }

    // ==================== ATTENDANCE POLICIES MANAGEMENT ====================

    /**
     * Store a new attendance policy
     */
    public function storePolicy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:attendance_policies,code',
            'description' => 'nullable|string',
            'location_id' => 'nullable|exists:attendance_locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'require_approval_for_late' => 'boolean',
            'require_approval_for_early_leave' => 'boolean',
            'require_approval_for_overtime' => 'boolean',
            'allow_remote_attendance' => 'boolean',
            'max_remote_days_per_month' => 'nullable|integer|min:0|max:31',
            'auto_approve_verified' => 'boolean',
            'require_photo_for_manual' => 'boolean',
            'require_location_for_mobile' => 'boolean',
            'max_late_minutes_per_month' => 'nullable|integer|min:0',
            'max_early_leave_minutes_per_month' => 'nullable|integer|min:0',
            'allowed_attendance_methods' => 'nullable|array',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $policy = AttendancePolicy::create([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'location_id' => $request->location_id,
                'department_id' => $request->department_id,
                'require_approval_for_late' => $request->require_approval_for_late ?? false,
                'require_approval_for_early_leave' => $request->require_approval_for_early_leave ?? false,
                'require_approval_for_overtime' => $request->require_approval_for_overtime ?? false,
                'allow_remote_attendance' => $request->allow_remote_attendance ?? false,
                'max_remote_days_per_month' => $request->max_remote_days_per_month,
                'auto_approve_verified' => $request->auto_approve_verified ?? true,
                'require_photo_for_manual' => $request->require_photo_for_manual ?? false,
                'require_location_for_mobile' => $request->require_location_for_mobile ?? true,
                'max_late_minutes_per_month' => $request->max_late_minutes_per_month,
                'max_early_leave_minutes_per_month' => $request->max_early_leave_minutes_per_month,
                'allowed_attendance_methods' => $request->allowed_attendance_methods,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance policy created successfully.',
                'policy' => $policy->load('location', 'department', 'creator'),
            ]);
        } catch (\Exception $e) {
            Log::error('Create policy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendance policy.',
            ], 500);
        }
    }

    /**
     * Update an attendance policy
     */
    public function updatePolicy(Request $request, $id)
    {
        $policy = AttendancePolicy::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:attendance_policies,code,' . $id,
            'description' => 'nullable|string',
            'location_id' => 'nullable|exists:attendance_locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'require_approval_for_late' => 'boolean',
            'require_approval_for_early_leave' => 'boolean',
            'require_approval_for_overtime' => 'boolean',
            'allow_remote_attendance' => 'boolean',
            'max_remote_days_per_month' => 'nullable|integer|min:0|max:31',
            'auto_approve_verified' => 'boolean',
            'require_photo_for_manual' => 'boolean',
            'require_location_for_mobile' => 'boolean',
            'max_late_minutes_per_month' => 'nullable|integer|min:0',
            'max_early_leave_minutes_per_month' => 'nullable|integer|min:0',
            'allowed_attendance_methods' => 'nullable|array',
            'is_active' => 'boolean',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $policy->update([
                'name' => $request->name,
                'code' => strtoupper($request->code),
                'description' => $request->description,
                'location_id' => $request->location_id,
                'department_id' => $request->department_id,
                'require_approval_for_late' => $request->require_approval_for_late ?? false,
                'require_approval_for_early_leave' => $request->require_approval_for_early_leave ?? false,
                'require_approval_for_overtime' => $request->require_approval_for_overtime ?? false,
                'allow_remote_attendance' => $request->allow_remote_attendance ?? false,
                'max_remote_days_per_month' => $request->max_remote_days_per_month,
                'auto_approve_verified' => $request->auto_approve_verified ?? true,
                'require_photo_for_manual' => $request->require_photo_for_manual ?? false,
                'require_location_for_mobile' => $request->require_location_for_mobile ?? true,
                'max_late_minutes_per_month' => $request->max_late_minutes_per_month,
                'max_early_leave_minutes_per_month' => $request->max_early_leave_minutes_per_month,
                'allowed_attendance_methods' => $request->allowed_attendance_methods,
                'is_active' => $request->is_active ?? true,
                'effective_from' => $request->effective_from,
                'effective_to' => $request->effective_to,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance policy updated successfully.',
                'policy' => $policy->load('location', 'department', 'creator', 'updater'),
            ]);
        } catch (\Exception $e) {
            Log::error('Update policy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance policy.',
            ], 500);
        }
    }

    /**
     * Delete an attendance policy
     */
    public function deletePolicy($id)
    {
        $policy = AttendancePolicy::findOrFail($id);
        $policy->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance policy deleted successfully.',
        ]);
    }

    /**
     * Convert various input formats to proper boolean value
     * Handles: true/false, 1/0, "1"/"0", "true"/"false", "on"/"off", "yes"/"no", null
     * 
     * @param mixed $value The value to convert
     * @param bool $default Default value if conversion fails
     * @return bool
     */
    private function convertToBoolean($value, $default = false)
    {
        if ($value === null || $value === '') {
            return $default;
        }
        
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_numeric($value)) {
            return (bool) $value;
        }
        
        if (is_string($value)) {
            $value = strtolower(trim($value));
            if (in_array($value, ['true', '1', 'on', 'yes', 'y'])) {
                return true;
            }
            if (in_array($value, ['false', '0', 'off', 'no', 'n', ''])) {
                return false;
            }
        }
        
        return $default;
    }

    // ==================== EMPLOYEES API ====================

    /**
     * Get employees list for enrollment (API endpoint)
     */
    public function getEmployeesList(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Load employees
        $employees = User::with(['employee', 'primaryDepartment'])
            ->whereHas('employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Auto-generate enroll_id from employee_id for employees without enroll_id
        foreach ($employees as $employee) {
            if (!$employee->enroll_id && $employee->employee && $employee->employee->employee_id) {
                // Extract numeric part from employee_id (e.g., "EMP20251107DU" -> "20251107")
                $enrollId = preg_replace('/[^0-9]/', '', $employee->employee->employee_id);
                if (empty($enrollId)) {
                    // If no numeric part, use user id
                    $enrollId = (string)$employee->id;
                }
                
                // Check if enroll_id already exists
                $exists = User::where('enroll_id', $enrollId)->where('id', '!=', $employee->id)->exists();
                if ($exists) {
                    // Append user id to make it unique
                    $enrollId = $enrollId . $employee->id;
                }
                
                // Save enroll_id
                $employee->enroll_id = $enrollId;
                $employee->save();
            }
        }
        
        // Reload employees with updated enroll_id
        $employees = User::with(['employee', 'primaryDepartment'])
            ->whereHas('employee')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $employeesData = $employees->map(function($emp) {
            return [
                'id' => $emp->id,
                'user_id' => $emp->id,
                'employee_id' => $emp->employee->employee_id ?? null,
                'employee_number' => $emp->employee->employee_id ?? null,
                'name' => $emp->name,
                'full_name' => $emp->name,
                'email' => $emp->email,
                'phone' => $emp->phone,
                'mobile' => $emp->mobile,
                'enroll_id' => $emp->enroll_id,
                'registered_on_device' => $emp->registered_on_device ?? false,
                'device_registered_at' => $emp->device_registered_at ? $emp->device_registered_at->toIso8601String() : null,
                'employee' => [
                    'employee_id' => $emp->employee->employee_id ?? null,
                    'department_id' => $emp->employee->department_id ?? null,
                ],
                'department' => [
                    'name' => $emp->primaryDepartment->name ?? null
                ],
                'primary_department' => [
                    'id' => $emp->primaryDepartment->id ?? null,
                    'name' => $emp->primaryDepartment->name ?? null
                ],
                'is_active' => $emp->is_active,
                'photo' => $emp->photo,
            ];
        });

        return response()->json([
            'success' => true,
            'employees' => $employeesData,
            'count' => $employeesData->count()
        ]);
    }

    // ==================== DASHBOARD DATA ====================

    /**
     * Get real-time dashboard data
     */
    public function getDashboardData(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $today = Carbon::today();
        
        // Get today's attendance stats
        $presentCount = Attendance::whereDate('attendance_date', $today)
            ->where('status', 'present')
            ->distinct('user_id')
            ->count('user_id');
        
        $lateCount = Attendance::whereDate('attendance_date', $today)
            ->where('is_late', true)
            ->count();
        
        $absentCount = User::whereHas('employee')
            ->where('is_active', true)
            ->whereDoesntHave('attendances', function($query) use ($today) {
                $query->whereDate('attendance_date', $today);
            })
            ->count();
        
        $onlineDevices = AttendanceDevice::where('is_active', true)
            ->where('is_online', true)
            ->count();
        
        // Get recent check-ins (last 30 minutes)
        $recentCheckins = Attendance::with(['user', 'attendanceDevice'])
            ->whereNotNull('time_in')
            ->where('time_in', '>=', Carbon::now()->subMinutes(30))
            ->orderBy('time_in', 'desc')
            ->limit(20)
            ->get()
            ->map(function($attendance) {
                $timeIn = $attendance->time_in;
                if (is_string($timeIn)) {
                    $timeIn = Carbon::parse($timeIn);
                }
                return [
                    'timestamp' => $timeIn ? $timeIn->toIso8601String() : Carbon::now()->toIso8601String(),
                    'user_name' => $attendance->user->name ?? 'Unknown',
                    'device_name' => $attendance->attendanceDevice->name ?? ($attendance->device_id ?? 'N/A'),
                    'status' => 'in'
                ];
            });

        return response()->json([
            'success' => true,
            'present_count' => $presentCount,
            'late_count' => $lateCount,
            'absent_count' => $absentCount,
            'online_devices' => $onlineDevices,
            'recent_checkins' => $recentCheckins
        ]);
    }

    // ==================== REPORTS ====================

    /**
     * Generate attendance report
     */
    public function generateReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'report_type' => 'required|string|in:daily,weekly,monthly,custom,summary,late_analysis,absenteeism,overtime',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $reportType = $request->report_type;
            $dateFrom = Carbon::parse($request->date_from);
            $dateTo = Carbon::parse($request->date_to);

            $query = Attendance::with(['user.employee', 'user.primaryDepartment', 'device', 'location'])
                ->whereBetween('attendance_date', [$dateFrom, $dateTo]);

            $report = $this->prepareAttendanceReport($query, $reportType, $dateFrom, $dateTo);

            return response()->json([
                'success' => true,
                'report' => $report
            ]);
        } catch (\Exception $e) {
            Log::error('Report generation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export attendance report
     */
    public function exportReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            abort(403, 'Unauthorized');
        }

        $reportType = $request->get('type', 'summary');
        $format = $request->get('format', 'excel');
        $dateFrom = Carbon::parse($request->get('from', Carbon::today()->subMonth()));
        $dateTo = Carbon::parse($request->get('to', Carbon::today()));

        $query = Attendance::with(['user.employee', 'user.primaryDepartment', 'device'])
            ->whereBetween('attendance_date', [$dateFrom, $dateTo]);

        $report = $this->prepareAttendanceReport($query, $reportType, $dateFrom, $dateTo);

        if ($format === 'pdf') {
            return $this->exportPDF($report, $reportType, $dateFrom, $dateTo);
        } else {
            return $this->exportExcel($report, $reportType, $dateFrom, $dateTo);
        }
    }

    /**
     * Prepare attendance report data
     */
    private function prepareAttendanceReport($query, $reportType, $dateFrom, $dateTo)
    {
        switch ($reportType) {
            case 'daily':
                return $this->generateDailyReport($query, $dateFrom, $dateTo);
            case 'weekly':
                return $this->generateWeeklyReport($query, $dateFrom, $dateTo);
            case 'monthly':
                return $this->generateMonthlyReport($query, $dateFrom, $dateTo);
            case 'late_analysis':
                return $this->generateLateAnalysisReport($query, $dateFrom, $dateTo);
            case 'absenteeism':
                return $this->generateAbsenteeismReport($query, $dateFrom, $dateTo);
            case 'overtime':
                return $this->generateOvertimeReport($query, $dateFrom, $dateTo);
            case 'summary':
            default:
                return $this->generateSummaryReport($query, $dateFrom, $dateTo);
        }
    }

    private function generateSummaryReport($query, $dateFrom, $dateTo)
    {
        $attendances = $query->get();
        
        $columns = ['Employee ID', 'Name', 'Department', 'Date', 'Time In', 'Time Out', 'Status', 'Hours'];
        $data = $attendances->map(function($att) {
            return [
                $att->user->employee_id ?? 'N/A',
                $att->user->name ?? 'Unknown',
                $att->user->primaryDepartment->name ?? 'N/A',
                $att->attendance_date->format('Y-m-d'),
                $att->time_in ? $att->time_in->format('H:i') : 'N/A',
                $att->time_out ? $att->time_out->format('H:i') : 'N/A',
                ucfirst($att->status),
                $att->total_hours ? round($att->total_hours / 60, 2) . ' hrs' : 'N/A'
            ];
        })->toArray();

        return [
            'columns' => $columns,
            'data' => $data,
            'summary' => [
                'total_records' => $attendances->count(),
                'present_count' => $attendances->where('status', 'present')->count(),
                'late_count' => $attendances->where('is_late', true)->count(),
                'absent_count' => $attendances->where('status', 'absent')->count(),
            ]
        ];
    }

    private function generateDailyReport($query, $dateFrom, $dateTo)
    {
        return $this->generateSummaryReport($query->whereDate('attendance_date', $dateFrom), $dateFrom, $dateFrom);
    }

    private function generateWeeklyReport($query, $dateFrom, $dateTo)
    {
        return $this->generateSummaryReport($query, $dateFrom, $dateTo);
    }

    private function generateMonthlyReport($query, $dateFrom, $dateTo)
    {
        return $this->generateSummaryReport($query, $dateFrom, $dateTo);
    }

    private function generateLateAnalysisReport($query, $dateFrom, $dateTo)
    {
        $lateAttendances = $query->where('is_late', true)->get();
        
        $columns = ['Employee ID', 'Name', 'Department', 'Date', 'Scheduled Time', 'Actual Time', 'Late By'];
        $data = $lateAttendances->map(function($att) {
            $lateMinutes = $att->metadata['late_minutes'] ?? 0;
            return [
                $att->user->employee_id ?? 'N/A',
                $att->user->name ?? 'Unknown',
                $att->user->primaryDepartment->name ?? 'N/A',
                $att->attendance_date->format('Y-m-d'),
                '09:00', // Default, should come from schedule
                $att->time_in ? $att->time_in->format('H:i') : 'N/A',
                $lateMinutes . ' minutes'
            ];
        })->toArray();

        return [
            'columns' => $columns,
            'data' => $data,
            'summary' => [
                'total_late' => $lateAttendances->count(),
                'avg_late_minutes' => $lateAttendances->avg(function($att) {
                    return $att->metadata['late_minutes'] ?? 0;
                })
            ]
        ];
    }

    private function generateAbsenteeismReport($query, $dateFrom, $dateTo)
    {
        $allUsers = User::whereHas('employee')->where('is_active', true)->get();
        $presentUserIds = $query->pluck('user_id')->unique();
        $absentUsers = $allUsers->whereNotIn('id', $presentUserIds);
        
        $columns = ['Employee ID', 'Name', 'Department', 'Days Absent'];
        $data = $absentUsers->map(function($user) use ($dateFrom, $dateTo) {
            $daysAbsent = $dateFrom->diffInDays($dateTo) + 1;
            return [
                $user->employee_id ?? 'N/A',
                $user->name ?? 'Unknown',
                $user->primaryDepartment->name ?? 'N/A',
                $daysAbsent
            ];
        })->toArray();

        return [
            'columns' => $columns,
            'data' => $data,
            'summary' => [
                'total_absent' => $absentUsers->count()
            ]
        ];
    }

    private function generateOvertimeReport($query, $dateFrom, $dateTo)
    {
        $overtimeAttendances = $query->where('is_overtime', true)->get();
        
        $columns = ['Employee ID', 'Name', 'Department', 'Date', 'Regular Hours', 'Overtime Hours'];
        $data = $overtimeAttendances->map(function($att) {
            $regularHours = 8; // Default
            $totalHours = $att->total_hours ? round($att->total_hours / 60, 2) : 0;
            $overtimeHours = max(0, $totalHours - $regularHours);
            
            return [
                $att->user->employee_id ?? 'N/A',
                $att->user->name ?? 'Unknown',
                $att->user->primaryDepartment->name ?? 'N/A',
                $att->attendance_date->format('Y-m-d'),
                $regularHours . ' hrs',
                $overtimeHours . ' hrs'
            ];
        })->toArray();

        return [
            'columns' => $columns,
            'data' => $data,
            'summary' => [
                'total_overtime' => $overtimeAttendances->count()
            ]
        ];
    }

    private function exportPDF($report, $reportType, $dateFrom, $dateTo)
    {
        // PDF export implementation
        return response()->json([
            'success' => false,
            'message' => 'PDF export coming soon'
        ]);
    }

    private function exportExcel($report, $reportType, $dateFrom, $dateTo)
    {
        // Excel export implementation
        return response()->json([
            'success' => false,
            'message' => 'Excel export coming soon'
        ]);
    }

    // ==================== GENERAL SETTINGS ====================

    /**
     * Save general settings
     */
    public function saveGeneralSettings(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $settings = [
            'attendance_default_method' => $request->default_attendance_method ?? 'biometric',
            'attendance_timezone' => $request->timezone ?? 'Africa/Dar_es_Salaam',
            'attendance_auto_approve_verified' => $request->enable_auto_approval ?? true,
            'attendance_require_photo_manual' => $request->require_photo_for_manual ?? false,
            'attendance_require_location_mobile' => $request->require_location_for_mobile ?? true,
            'attendance_late_tolerance' => $request->late_tolerance ?? 15,
            'attendance_early_leave_tolerance' => $request->early_leave_tolerance ?? 15,
            'attendance_overtime_threshold' => $request->overtime_threshold ?? 30,
            'attendance_default_break_duration' => $request->default_break_duration ?? 60,
            'attendance_data_retention_days' => $request->data_retention_days ?? 365,
            'attendance_auto_archive' => $request->auto_archive ?? true,
        ];

        foreach ($settings as $key => $value) {
            $type = is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'text');
            SystemSetting::setValue($key, $value, $type);
        }

        ActivityLogService::logAction(
            'attendance_settings_updated',
            'General attendance settings updated',
            null,
            ['settings' => array_keys($settings)]
        );

        return response()->json([
            'success' => true,
            'message' => 'General settings saved successfully'
        ]);
    }

    // ==================== NOTIFICATIONS ====================

    /**
     * Save notification settings
     */
    public function saveNotificationSettings(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $settings = [
            'notify_device_offline' => $request->notify_device_offline ?? false,
            'notify_device_sync_failed' => $request->notify_device_sync_failed ?? false,
            'notify_fingerprint_failed' => $request->notify_fingerprint_failed ?? false,
            'notify_missing_checkin' => $request->notify_missing_checkin ?? false,
            'notify_late_arrival' => $request->notify_late_arrival ?? false,
            'notify_absenteeism' => $request->notify_absenteeism ?? false,
            'admin_email' => $request->admin_email,
            'email_frequency' => $request->email_frequency ?? 'realtime',
            'sms_provider' => $request->sms_provider,
            'sms_phone' => $request->sms_phone,
        ];

        foreach ($settings as $key => $value) {
            $type = is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'text');
            SystemSetting::setValue('attendance_notification_' . $key, $value, $type);
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification settings saved successfully'
        ]);
    }

    /**
     * Test SMS sending
     */
    public function testSMS(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $notificationService = new NotificationService();
            $message = "Test SMS from OfisiLink Attendance System. If you receive this, SMS integration is working correctly.";
            
            $result = $notificationService->sendSMS($request->phone, $message);
            
            if ($result) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test SMS sent successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send SMS. Please check SMS configuration in System Settings.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Test SMS error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error sending SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save sync settings
     */
    public function saveSyncSettings(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $settings = [
            'attendance_default_sync_mode' => $request->default_sync_mode ?? 'push',
            'attendance_polling_interval' => $request->polling_interval ?? 5,
            'attendance_auto_sync_enabled' => $request->auto_sync_enabled ?? true,
            'attendance_auto_failure_detection' => $request->auto_failure_detection ?? true,
            'attendance_failure_threshold' => $request->failure_threshold ?? 5,
        ];

        foreach ($settings as $key => $value) {
            $type = is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'text');
            SystemSetting::setValue($key, $value, $type);
        }

        return response()->json([
            'success' => true,
            'message' => 'Sync settings saved successfully'
        ]);
    }

    /**
     * Save failure detection settings
     */
    public function saveFailureSettings(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $settings = [
            'attendance_auto_failure_detection' => $request->auto_failure_detection ?? true,
            'attendance_failure_threshold' => $request->failure_threshold ?? 5,
        ];

        foreach ($settings as $key => $value) {
            $type = is_bool($value) ? 'boolean' : (is_numeric($value) ? 'number' : 'text');
            SystemSetting::setValue($key, $value, $type);
        }

        return response()->json([
            'success' => true,
            'message' => 'Failure detection settings saved successfully'
        ]);
    }

    /**
     * Save security settings
     */
    public function saveSecuritySettings(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $settings = [
            'attendance_api_auth_method' => $request->api_auth_method ?? 'token',
            'attendance_api_key' => $request->api_key ?? '',
            'attendance_enable_api_logging' => $request->enable_api_logging ?? true,
            'attendance_require_https' => $request->require_https ?? false,
            'attendance_allowed_ips' => $request->allowed_ips ?? [],
            'attendance_rate_limit' => $request->rate_limit ?? 60,
            'attendance_enable_audit_log' => $request->enable_audit_log ?? true,
            // Device API Settings
            'device_api_key' => $request->device_api_key ?? '',
            'device_api_logging' => $request->device_api_logging ?? true,
            'device_api_require_https' => $request->device_api_require_https ?? false,
        ];

        foreach ($settings as $key => $value) {
            $type = is_bool($value) ? 'boolean' : (is_array($value) ? 'json' : (is_numeric($value) ? 'number' : 'text'));
            SystemSetting::setValue($key, is_array($value) ? json_encode($value) : $value, $type);
        }

        return response()->json([
            'success' => true,
            'message' => 'Security settings saved successfully'
        ]);
    }

    /**
     * Run maintenance
     */
    public function runMaintenance(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Admin only'
            ], 403);
        }

        try {
            // Clean up old attendance logs (older than retention period)
            $retentionDays = SystemSetting::getValue('attendance_data_retention_days', 365);
            $cutoffDate = Carbon::now()->subDays($retentionDays);
            
            $deletedCount = Attendance::where('attendance_date', '<', $cutoffDate)->delete();
            
            // Optimize database
            DB::statement('OPTIMIZE TABLE attendances');
            
            return response()->json([
                'success' => true,
                'message' => 'Maintenance completed successfully',
                'deleted_records' => $deletedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Maintenance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to run maintenance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            \Artisan::call('cache:clear');
            \Artisan::call('config:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Clear cache error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check system health
     */
    public function checkSystemHealth(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $health = [
                'status' => 'healthy',
                'database' => 'connected',
                'devices_online' => AttendanceDevice::where('is_online', true)->count(),
                'total_devices' => AttendanceDevice::count(),
                'recent_errors' => 0
            ];
            
            return response()->json([
                'success' => true,
                'health' => $health,
                'status' => 'healthy'
            ]);
        } catch (\Exception $e) {
            Log::error('System health check error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Health check failed: ' . $e->getMessage(),
                'status' => 'unhealthy'
            ], 500);
        }
    }

    /**
     * Check device failures
     */
    public function checkDeviceFailures(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $threshold = SystemSetting::getValue('attendance_failure_threshold', 5);
            $now = Carbon::now();
            
            $devices = AttendanceDevice::where('is_active', true)->get();
            $failedDevices = [];
            
            foreach ($devices as $device) {
                $isFailed = false;
                $errorMessage = '';
                
                // Check if device is offline
                if (!$device->is_online) {
                    $isFailed = true;
                    $errorMessage = 'Device is offline';
                }
                
                // Check last sync time
                if ($device->last_sync_at) {
                    $minutesSinceSync = $now->diffInMinutes($device->last_sync_at);
                    if ($minutesSinceSync > $threshold) {
                        $isFailed = true;
                        $errorMessage = "No sync for {$minutesSinceSync} minutes";
                    }
                } else {
                    $isFailed = true;
                    $errorMessage = 'Never synced';
                }
                
                // Check IP connectivity (basic ping test)
                if ($device->ip_address && $isFailed) {
                    // In production, you might want to actually ping the device
                    // For now, we'll just mark it based on sync status
                }
                
                if ($isFailed) {
                    $failedDevices[] = [
                        'id' => $device->id,
                        'name' => $device->name,
                        'ip_address' => $device->ip_address,
                        'device_id' => $device->device_id,
                        'last_sync_at' => $device->last_sync_at ? $device->last_sync_at->diffForHumans() : 'Never',
                        'error_message' => $errorMessage,
                        'is_online' => $device->is_online
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'failed_devices' => $failedDevices,
                'failed_count' => count($failedDevices),
                'total_devices' => $devices->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Check device failures error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check device failures: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test all devices
     */
    public function testAllDevices(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $devices = AttendanceDevice::where('is_active', true)->get();
            $successful = 0;
            $failed = [];
            
            foreach ($devices as $device) {
                // Test device connection
                $isOnline = $this->testDeviceConnection($device);
                
                if ($isOnline) {
                    $device->markOnline();
                    $successful++;
                } else {
                    $device->markOffline();
                    $failed[] = [
                        'id' => $device->id,
                        'name' => $device->name,
                        'error' => 'Connection timeout'
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'total' => $devices->count(),
                'successful' => $successful,
                'failed' => $failed,
                'failed_count' => count($failed)
            ]);
        } catch (\Exception $e) {
            Log::error('Test all devices error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to test devices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test single device connection
     */
    private function testDeviceConnection($device)
    {
        if (!$device->ip_address) {
            return false;
        }
        
        // Basic connection test - in production, use actual ping or socket connection
        // For now, we'll check if device was recently synced
        if ($device->last_sync_at) {
            $minutesSinceSync = Carbon::now()->diffInMinutes($device->last_sync_at);
            return $minutesSinceSync < 10; // Consider online if synced within 10 minutes
        }
        
        return false;
    }

    /**
     * Retry failed device
     */
    public function retryDevice(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $device = AttendanceDevice::findOrFail($id);
            
            // Attempt to reconnect
            $isOnline = $this->testDeviceConnection($device);
            
            if ($isOnline) {
                $device->markOnline();
                return response()->json([
                    'success' => true,
                    'message' => 'Device reconnected successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Device connection failed. Please check network settings.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Retry device error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retry device: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save device step (for step-by-step saving)
     */
    public function saveDeviceStep(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $step = $request->save_step;
        $deviceId = $request->id;
        
        try {
            if ($deviceId) {
                // Update existing device
                $device = AttendanceDevice::findOrFail($deviceId);
            } else {
                // Create new device if step 1
                if ($step == 1) {
                    $validator = Validator::make($request->all(), [
                        'name' => 'required|string|max:255',
                        'device_id' => 'required|string|unique:attendance_devices,device_id',
                        'device_type' => 'required|string',
                    ]);

                    if ($validator->fails()) {
                        return response()->json([
                            'success' => false,
                            'errors' => $validator->errors()
                        ], 422);
                    }
                    
                    $device = new AttendanceDevice();
                    $device->created_by = $user->id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please complete Step 1 first'
                    ], 400);
                }
            }
            
            // Update fields based on step
            switch($step) {
                case 1:
                    $device->name = $request->name;
                    $device->device_id = $request->device_id;
                    $device->device_type = $request->device_type ?? 'biometric';
                    $device->location_id = $request->location_id;
                    $device->is_active = $request->is_active ?? true;
                    break;
                case 2:
                    $device->connection_type = $request->connection_type ?? 'network';
                    $device->ip_address = $request->ip_address;
                    $device->port = $request->port ?? 4370;
                    $device->sync_interval_minutes = $request->sync_interval_minutes ?? 5;
                    break;
                case 3:
                    // Store ZKBio Time.Net config in settings
                    $settings = $device->settings ?? [];
                    $settings['zkbio_server_ip'] = $request->zkbio_server_ip;
                    $settings['zkbio_db_type'] = $request->zkbio_db_type ?? 'sqlite';
                    $settings['zkbio_db_path'] = $request->zkbio_db_path;
                    if ($request->zkbio_db_host) {
                        $settings['zkbio_db_host'] = $request->zkbio_db_host;
                    }
                    if ($request->zkbio_db_user) {
                        $settings['zkbio_db_user'] = $request->zkbio_db_user;
                    }
                    if ($request->zkbio_db_password) {
                        $settings['zkbio_db_password'] = $request->zkbio_db_password;
                    }
                    $device->settings = $settings;
                    break;
                case 4:
                    if ($request->capabilities) {
                        $device->capabilities = is_array($request->capabilities) 
                            ? $request->capabilities 
                            : json_decode($request->capabilities, true);
                    }
                    if ($request->settings) {
                        $existingSettings = $device->settings ?? [];
                        $newSettings = is_array($request->settings) 
                            ? $request->settings 
                            : json_decode($request->settings, true);
                        $device->settings = array_merge($existingSettings, $newSettings);
                    }
                    break;
            }
            
            $device->updated_by = $user->id;
            $device->save();
            
            // Log the activity
            \App\Services\ActivityLogService::log(
                $step == 1 ? 'created' : 'updated',
                "Device {$device->name} - Step {$step} saved",
                $device,
                ['step' => $step, 'device_id' => $device->id]
            );
            
            return response()->json([
                'success' => true,
                'message' => "Step {$step} saved successfully",
                'device_id' => $device->id,
                'step' => $step
            ]);
        } catch (\Exception $e) {
            Log::error('Save device step error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save step: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save policy step (for step-by-step saving)
     */
    public function savePolicyStep(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $step = $request->save_step;
        $policyId = $request->id;
        
        try {
            if ($policyId) {
                // Update existing policy
                $policy = AttendancePolicy::findOrFail($policyId);
            } else {
                // Create new policy if step 1
                if ($step == 1) {
                    $validator = Validator::make($request->all(), [
                        'name' => 'required|string|max:255',
                        'code' => 'required|string|unique:attendance_policies,code',
                    ]);

                    if ($validator->fails()) {
                        return response()->json([
                            'success' => false,
                            'errors' => $validator->errors()
                        ], 422);
                    }
                    
                    $policy = new AttendancePolicy();
                    $policy->created_by = $user->id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please complete Step 1 first'
                    ], 400);
                }
            }
            
            // Update fields based on step
            switch($step) {
                case 1:
                    $policy->name = $request->name;
                    $policy->code = $request->code;
                    $policy->description = $request->description;
                    $policy->location_id = $request->location_id;
                    $policy->department_id = $request->department_id;
                    $policy->effective_from = $request->effective_from;
                    $policy->effective_to = $request->effective_to;
                    $policy->is_active = $request->is_active ?? true;
                    break;
                case 2:
                    $policy->require_approval_for_late = $request->require_approval_for_late ?? false;
                    $policy->require_approval_for_early_leave = $request->require_approval_for_early_leave ?? false;
                    $policy->require_approval_for_overtime = $request->require_approval_for_overtime ?? false;
                    $policy->auto_approve_verified = $request->auto_approve_verified ?? false;
                    $policy->require_photo_for_manual = $request->require_photo_for_manual ?? false;
                    $policy->require_location_for_mobile = $request->require_location_for_mobile ?? false;
                    break;
                case 3:
                    $policy->allow_remote_attendance = $request->allow_remote_attendance ?? false;
                    $policy->max_remote_days_per_month = $request->max_remote_days_per_month;
                    $policy->max_late_minutes_per_month = $request->max_late_minutes_per_month;
                    $policy->max_early_leave_minutes_per_month = $request->max_early_leave_minutes_per_month;
                    if ($request->allowed_attendance_methods) {
                        $policy->allowed_attendance_methods = is_array($request->allowed_attendance_methods) 
                            ? $request->allowed_attendance_methods 
                            : json_decode($request->allowed_attendance_methods, true);
                    }
                    break;
            }
            
            $policy->updated_by = $user->id;
            $policy->save();
            
            return response()->json([
                'success' => true,
                'message' => "Step {$step} saved successfully",
                'policy_id' => $policy->id,
                'step' => $step
            ]);
        } catch (\Exception $e) {
            Log::error('Save policy step error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save step: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enroll user to device - Complete implementation with biometric registration
     */
    public function enrollUser(Request $request)
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
            'device_id' => 'nullable|exists:attendance_devices,id',
            'ip_address' => 'nullable|ip',
            'port' => 'nullable|integer|min:1|max:65535',
            'comm_key' => 'nullable|integer|min:0|max:65535',
            'fingers' => 'nullable|array',
            'fingers.*' => 'integer|between:1,10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get user
            $targetUser = User::with('employee')->findOrFail($request->user_id);
            
            // Get or find device
            $device = null;
            if ($request->device_id) {
                $device = AttendanceDevice::findOrFail($request->device_id);
            } elseif ($request->ip_address) {
                // Find device by IP or create a temporary one
                $device = AttendanceDevice::where('ip_address', $request->ip_address)->first();
                
                if (!$device) {
                    // Create a temporary device record
                    $device = AttendanceDevice::create([
                        'name' => 'Device ' . $request->ip_address,
                        'device_id' => 'temp_' . $request->ip_address,
                        'device_type' => 'biometric',
                        'ip_address' => $request->ip_address,
                        'port' => $request->port ?? 4370,
                        'connection_type' => 'network',
                        'is_active' => true,
                        'settings' => [
                            'comm_key' => $request->comm_key ?? 0
                        ],
                        'created_by' => Auth::id(),
                    ]);
                }
            } else {
                throw new \Exception('Either device_id or ip_address must be provided');
            }

            // Ensure user has enroll_id
            if (!$targetUser->enroll_id) {
                // Generate enroll_id from employee_id
                if ($targetUser->employee && $targetUser->employee->employee_id) {
                    $enrollId = preg_replace('/[^0-9]/', '', $targetUser->employee->employee_id);
                    if (empty($enrollId)) {
                        $enrollId = (string)$targetUser->id;
                    }
                    
                    // Check if enroll_id already exists
                    $exists = User::where('enroll_id', $enrollId)->where('id', '!=', $targetUser->id)->exists();
                    if ($exists) {
                        $enrollId = $enrollId . $targetUser->id;
                    }
                    
                    $targetUser->enroll_id = $enrollId;
                    $targetUser->save();
                } else {
                    $targetUser->enroll_id = (string)$targetUser->id;
                    $targetUser->save();
                }
            }

            // Check if device is online and accessible
            if (!$device->ip_address) {
                throw new \Exception('Device IP address is not configured');
            }

            // Get connection parameters
            $deviceIp = $request->ip_address ?? $device->ip_address;
            $devicePort = $request->port ?? $device->port ?? 4370;
            $commKey = $request->comm_key ?? $device->settings['comm_key'] ?? 0;
            
            if (!$deviceIp) {
                throw new \Exception('Device IP address is not configured');
            }
            
            // Initialize ZKTeco service with device settings (using proper SDK)
            $zktecoService = new \App\Services\ZKTecoService(
                $deviceIp,
                $devicePort,
                $commKey
            );

            // Test connection first
            if (!$zktecoService->connect()) {
                throw new \Exception('Failed to connect to device. Please check IP address and network connectivity.');
            }

            // Get or generate UID (use enroll_id as UID if numeric, otherwise use user ID)
            $uid = (int)$targetUser->enroll_id;
            if ($uid < 1 || $uid > 65535) {
                $uid = $targetUser->id % 65535; // Ensure within valid range
                if ($uid < 1) $uid = 1;
            }

            // Register user to device
            $userName = substr($targetUser->name, 0, 24); // Device limit is 24 characters
            $enrollId = (string)$targetUser->enroll_id;
            
            Log::info("Registering user to device", [
                'user_id' => $targetUser->id,
                'user_name' => $userName,
                'enroll_id' => $enrollId,
                'uid' => $uid,
                'device_id' => $device->id,
                'device_ip' => $device->ip_address
            ]);

            $registrationResult = $zktecoService->registerUser(
                $uid,
                $enrollId,  // userid (enroll_id)
                $userName,
                '', // password
                0,  // role
                0   // cardno
            );

            if (!$registrationResult) {
                throw new \Exception('Failed to register user on device. Please check device connection and settings.');
            }

            // Update user record
            $targetUser->registered_on_device = true;
            $targetUser->device_registered_at = Carbon::now();
            $targetUser->save();

            // Update device last sync
            $device->last_sync_at = Carbon::now();
            $device->is_online = true;
            $device->save();

            // Send SMS notification to user
            try {
                $notificationService = new \App\Services\NotificationService();
                $employeePhone = $targetUser->mobile ?? $targetUser->phone;
                
                if ($employeePhone) {
                    // Get first name from user name
                    $firstName = explode(' ', $targetUser->name)[0];
                    $date = Carbon::now()->format('M d, Y');
                    
                    $message = "Hello {$firstName},\n\nYou have been successfully registered to the biometric attendance system.\n\nEnroll ID: {$enrollId}\nDevice: {$device->name}\nDate: {$date}\n\nYou can now use your fingerprint to check in and check out. Please visit the device to enroll your fingerprint.";
                    
                    $notificationService->sendSMS($employeePhone, $message);
                    Log::info('Registration SMS sent to user', [
                        'user_id' => $targetUser->id,
                        'phone' => $employeePhone
                    ]);
                }
            } catch (\Exception $smsError) {
                Log::warning('Failed to send registration SMS', [
                    'user_id' => $targetUser->id,
                    'error' => $smsError->getMessage()
                ]);
                // Don't fail the enrollment if SMS fails
            }

            // Log activity
            ActivityLogService::log(
                'user_enrolled',
                "User {$targetUser->name} enrolled to device {$device->name}",
                $targetUser,
                [
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'enroll_id' => $enrollId,
                    'uid' => $uid
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User successfully registered to biometric device. User can now use fingerprint scanner.',
                'enrollment' => [
                    'user_id' => $targetUser->id,
                    'user_name' => $targetUser->name,
                    'device_id' => $device->id,
                    'device_name' => $device->name,
                    'enroll_id' => $enrollId,
                    'uid' => $uid,
                    'registered_at' => $targetUser->device_registered_at->toIso8601String()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Enrollment error: ' . $e->getMessage(), [
                'user_id' => $request->user_id,
                'device_id' => $request->device_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to enroll user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync all users to all devices - Complete implementation
     */
    public function syncAllUsers(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $users = User::whereHas('employee')
                ->where('is_active', true)
                ->get();
            $devices = AttendanceDevice::where('is_active', true)
                ->whereNotNull('ip_address')
                ->get();
            
            $syncedCount = 0;
            $failedCount = 0;
            $errors = [];
            
            foreach ($devices as $device) {
                try {
                    // Initialize ZKTeco service
                    $zktecoService = new \App\Services\ZKTecoService(
                        $device->ip_address,
                        $device->port ?? 4370,
                        $device->settings['comm_key'] ?? 0
                    );

                    // Test connection
                    if (!$zktecoService->connect()) {
                        $errors[] = "Device {$device->name}: Connection failed";
                        $failedCount++;
                        continue;
                    }

                    foreach ($users as $targetUser) {
                        try {
                            // Ensure user has enroll_id
                            if (!$targetUser->enroll_id) {
                                if ($targetUser->employee && $targetUser->employee->employee_id) {
                                    $enrollId = preg_replace('/[^0-9]/', '', $targetUser->employee->employee_id);
                                    if (empty($enrollId)) {
                                        $enrollId = (string)$targetUser->id;
                                    }
                                    
                                    $exists = User::where('enroll_id', $enrollId)->where('id', '!=', $targetUser->id)->exists();
                                    if ($exists) {
                                        $enrollId = $enrollId . $targetUser->id;
                                    }
                                    
                                    $targetUser->enroll_id = $enrollId;
                                    $targetUser->save();
                                } else {
                                    $targetUser->enroll_id = (string)$targetUser->id;
                                    $targetUser->save();
                                }
                            }

                            // Get or generate UID
                            $uid = (int)$targetUser->enroll_id;
                            if ($uid < 1 || $uid > 65535) {
                                $uid = $targetUser->id % 65535;
                                if ($uid < 1) $uid = 1;
                            }

                            // Register user to device
                            $userName = substr($targetUser->name, 0, 24);
                            $enrollId = (string)$targetUser->enroll_id;
                            
                            $result = $zktecoService->registerUser(
                                $uid,
                                $userName,
                                '',
                                0,
                                0
                            );

                            if ($result) {
                                $wasAlreadyRegistered = $targetUser->registered_on_device;
                                $targetUser->registered_on_device = true;
                                $targetUser->device_registered_at = Carbon::now();
                                $targetUser->save();
                                $syncedCount++;
                                
                                // Send SMS only if this is the first time registering (not already registered)
                                if (!$wasAlreadyRegistered) {
                                    try {
                                        $notificationService = new \App\Services\NotificationService();
                                        $employeePhone = $targetUser->mobile ?? $targetUser->phone;
                                        
                                        if ($employeePhone) {
                                            // Get first name from user name
                                            $firstName = explode(' ', $targetUser->name)[0];
                                            $date = Carbon::now()->format('M d, Y');
                                            
                                            $message = "Hello {$firstName},\n\nYou have been successfully registered to the biometric attendance system.\n\nEnroll ID: {$enrollId}\nDevice: {$device->name}\nDate: {$date}\n\nYou can now use your fingerprint to check in and check out. Please visit the device to enroll your fingerprint.";
                                            
                                            $notificationService->sendSMS($employeePhone, $message);
                                            Log::info('Registration SMS sent to user (sync all)', [
                                                'user_id' => $targetUser->id,
                                                'phone' => $employeePhone
                                            ]);
                                        }
                                    } catch (\Exception $smsError) {
                                        Log::warning('Failed to send registration SMS (sync all)', [
                                            'user_id' => $targetUser->id,
                                            'error' => $smsError->getMessage()
                                        ]);
                                        // Don't fail the sync if SMS fails
                                    }
                                }
                            } else {
                                $errors[] = "User {$targetUser->name} failed on device {$device->name}";
                                $failedCount++;
                            }
                        } catch (\Exception $e) {
                            $errors[] = "User {$targetUser->name} on device {$device->name}: " . $e->getMessage();
                            $failedCount++;
                            Log::error("Sync user error: " . $e->getMessage());
                        }
                    }

                    // Update device status
                    $device->last_sync_at = Carbon::now();
                    $device->is_online = true;
                    $device->save();

                } catch (\Exception $e) {
                    $errors[] = "Device {$device->name}: " . $e->getMessage();
                    $failedCount++;
                    Log::error("Sync device error: " . $e->getMessage());
                }
            }

            ActivityLogService::log(
                'users_synced',
                "Synced {$syncedCount} users to devices",
                null,
                [
                    'synced_count' => $syncedCount,
                    'failed_count' => $failedCount,
                    'total_users' => $users->count(),
                    'total_devices' => $devices->count()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Synced {$syncedCount} users to devices. {$failedCount} failed.",
                'synced_count' => $syncedCount,
                'failed_count' => $failedCount,
                'total_users' => $users->count(),
                'total_devices' => $devices->count(),
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Sync all users error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get device logs
     */
    public function getDeviceLogs($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $device = AttendanceDevice::findOrFail($id);
            
            // Get activity logs related to this device
            $activityLogs = \App\Models\ActivityLog::where('model_type', AttendanceDevice::class)
                ->where('model_id', $id)
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();
            
            // Get Laravel logs related to this device
            $laravelLogs = [];
            $logFile = storage_path('logs/laravel.log');
            if (file_exists($logFile)) {
                $logContent = file_get_contents($logFile);
                $lines = explode("\n", $logContent);
                $devicePattern = "device.*{$device->id}|device_id.*{$device->device_id}|{$device->name}";
                
                foreach (array_reverse($lines) as $line) {
                    if (preg_match("/{$devicePattern}/i", $line) || 
                        preg_match("/ZKBio Time|UF200|sync.*device/i", $line)) {
                        $laravelLogs[] = [
                            'line' => $line,
                            'type' => $this->parseLogType($line)
                        ];
                        if (count($laravelLogs) >= 50) break;
                    }
                }
            }
            
            // Format logs
            $logs = [];
            
            // Add activity logs
            foreach ($activityLogs as $log) {
                $logs[] = [
                    'id' => $log->id,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'time_ago' => $log->created_at->diffForHumans(),
                    'action' => $log->action,
                    'status' => $this->determineLogStatus($log->action),
                    'message' => $log->description,
                    'description' => $log->description,
                    'details' => $log->properties
                ];
            }
            
            // Add sync logs from ZKBio Time sync
            $syncLogs = DB::table('activity_logs')
                ->where('description', 'like', "%device {$device->id}%")
                ->orWhere('description', 'like', "%{$device->name}%")
                ->orWhere('description', 'like', "%ZKBio Time%")
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
            
            foreach ($syncLogs as $log) {
                $logs[] = [
                    'id' => $log->id ?? null,
                    'created_at' => Carbon::parse($log->created_at)->format('Y-m-d H:i:s'),
                    'time_ago' => Carbon::parse($log->created_at)->diffForHumans(),
                    'action' => $log->action ?? 'sync',
                    'status' => $this->determineLogStatus($log->action ?? 'sync'),
                    'message' => $log->description ?? 'Sync activity',
                    'description' => $log->description ?? '',
                    'details' => json_decode($log->properties ?? '{}', true)
                ];
            }
            
            // Sort by date (newest first)
            usort($logs, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            // Limit to 100 most recent
            $logs = array_slice($logs, 0, 100);
            
            return response()->json([
                'success' => true,
                'device_name' => $device->name,
                'device_id' => $device->id,
                'logs' => $logs,
                'total' => count($logs)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching device logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch device logs: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Determine log status from action
     */
    private function determineLogStatus($action)
    {
        $statusMap = [
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'warning',
            'sync' => 'info',
            'sync_success' => 'success',
            'sync_error' => 'error',
            'sync_failed' => 'error',
            'test' => 'info',
            'test_success' => 'success',
            'test_failed' => 'error',
        ];
        
        return $statusMap[strtolower($action)] ?? 'info';
    }
    
    /**
     * Parse log type from log line
     */
    private function parseLogType($line)
    {
        if (stripos($line, 'error') !== false) return 'error';
        if (stripos($line, 'warning') !== false) return 'warning';
        if (stripos($line, 'success') !== false) return 'success';
        return 'info';
    }

    /**
     * Sync all devices - Capture attendance from all devices and save to database
     */
    public function syncAllDevices(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $devices = AttendanceDevice::where('is_active', true)
                ->whereNotNull('ip_address')
                ->get();
            
            $syncedCount = 0;
            $totalRecords = 0;
            $errors = [];
            $results = [];
            
            foreach ($devices as $device) {
                try {
                    // Use ZKTecoService to sync directly from device
                    $zktecoService = new \App\Services\ZKTecoService(
                        $device->ip_address,
                        $device->port ?? 4370,
                        $device->settings['comm_key'] ?? 0
                    );
                    
                    // Sync attendances from device to database
                    $syncResult = $zktecoService->syncAttendancesToDatabase();
                    
                    $deviceRecords = $syncResult['synced'] ?? 0;
                    $totalRecords += $deviceRecords;
                    
                    // Update device status
                    $device->last_sync_at = Carbon::now();
                    $device->is_online = true;
                    $device->save();
                    
                    $syncedCount++;
                    $results[] = [
                        'device_id' => $device->id,
                        'device_name' => $device->name,
                        'records_synced' => $deviceRecords,
                        'skipped' => $syncResult['skipped'] ?? 0
                    ];
                    
                } catch (\Exception $e) {
                    $errors[] = "Device {$device->name}: " . $e->getMessage();
                    $device->is_online = false;
                    $device->save();
                    Log::error("Sync device {$device->name} error: " . $e->getMessage());
                }
            }

            ActivityLogService::log(
                'devices_synced',
                "Synced attendance from {$syncedCount} devices. {$totalRecords} records captured.",
                null,
                [
                    'synced_devices' => $syncedCount,
                    'total_records' => $totalRecords,
                    'total_devices' => $devices->count()
                ]
            );

            return response()->json([
                'success' => true,
                'message' => "Synced {$syncedCount} devices. {$totalRecords} attendance records captured.",
                'synced_devices' => $syncedCount,
                'total_records' => $totalRecords,
                'total_devices' => $devices->count(),
                'results' => $results,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            Log::error('Sync all devices error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync devices: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync single device - Capture attendance from specific device
     */
    public function syncDevice(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $device = AttendanceDevice::findOrFail($id);
            
            if (!$device->ip_address) {
                return response()->json([
                    'success' => false,
                    'message' => 'Device IP address is not configured'
                ], 400);
            }

            // Use ZKTecoService to sync directly from device
            $zktecoService = new \App\Services\ZKTecoService(
                $device->ip_address,
                $device->port ?? 4370,
                $device->settings['comm_key'] ?? 0
            );
            
            // Sync attendances from device to database
            $syncResult = $zktecoService->syncAttendancesToDatabase();
            
            // Update device status
            $device->last_sync_at = Carbon::now();
            $device->is_online = true;
            $device->save();

            ActivityLogService::log(
                'device_synced',
                "Synced attendance from device {$device->name}",
                $device,
                [
                    'records_synced' => $syncResult['synced'] ?? 0,
                    'skipped' => $syncResult['skipped'] ?? 0
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Device synced successfully',
                'device' => [
                    'id' => $device->id,
                    'name' => $device->name
                ],
                'sync_result' => $syncResult
            ]);
        } catch (\Exception $e) {
            Log::error('Sync device error: ' . $e->getMessage());
            
            if (isset($device)) {
                $device->is_online = false;
                $device->save();
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync device: ' . $e->getMessage()
            ], 500);
        }
    }
}
