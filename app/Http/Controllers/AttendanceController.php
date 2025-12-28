<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AttendanceLocation;
use App\Models\AttendanceDevice;
use App\Services\NotificationService;
use App\Services\GeocodingService;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Get organization timezone
     */
    private function getTimezone()
    {
        try {
            $orgSettings = \App\Models\OrganizationSetting::getSettings();
            return $orgSettings->timezone ?? 'Africa/Dar_es_Salaam';
        } catch (\Exception $e) {
            return 'Africa/Dar_es_Salaam';
        }
    }
    
    /**
     * Display attendance management page
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Check permissions
        $canViewAll = $user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD']);
        $canManage = $user->hasAnyRole(['HR Officer', 'System Admin']);
        
        $query = Attendance::with(['user', 'employee', 'employee.department', 'approver']);
        
        // Only show attendance records captured from ZKTeco devices
        // Filter by: biometric/fingerprint method, or has device_ip, or has verify_mode
        $query->where(function($q) {
            $q->whereIn('attendance_method', ['biometric', 'fingerprint'])
              ->orWhereNotNull('device_ip')
              ->orWhereNotNull('verify_mode')
              ->orWhereNotNull('attendance_device_id');
        });
        
        // Advanced date filtering
        if ($request->filled('report_period')) {
            $reportPeriod = $request->report_period;
            $today = Carbon::today($this->getTimezone());
            
            switch ($reportPeriod) {
                case 'today':
                    $query->whereDate('attendance_date', $today);
                    break;
                case 'yesterday':
                    $query->whereDate('attendance_date', $today->copy()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('attendance_date', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek()
                    ]);
                    break;
                case 'last_week':
                    $query->whereBetween('attendance_date', [
                        $today->copy()->subWeek()->startOfWeek(),
                        $today->copy()->subWeek()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth('attendance_date', $today->month)
                          ->whereYear('attendance_date', $today->year);
                    break;
                case 'last_month':
                    $query->whereMonth('attendance_date', $today->copy()->subMonth()->month)
                          ->whereYear('attendance_date', $today->copy()->subMonth()->year);
                    break;
                case 'this_year':
                    $query->whereYear('attendance_date', $today->year);
                    break;
                case 'last_year':
                    $query->whereYear('attendance_date', $today->copy()->subYear()->year);
                    break;
                case 'custom':
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $query->dateRange($request->start_date, $request->end_date);
                    }
                    break;
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        } else {
            // Default to current month
            $query->whereMonth('attendance_date', Carbon::now()->month)
                  ->whereYear('attendance_date', Carbon::now()->year);
        }
        
        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }
        
        // Filter by department
        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('primary_department_id', $request->department_id);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        
        // Filter by method
        if ($request->filled('method')) {
            $query->byMethod($request->method);
        }
        
        // Filter by verification status
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }
        
        // Filter by location
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        
        // Filter by schedule
        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        }
        
        // Filter by policy (based on department or location)
        if ($request->filled('policy_id')) {
            $policyId = $request->policy_id;
            $query->where(function($q) use ($policyId) {
                $q->whereHas('user', function($userQuery) use ($policyId) {
                    $userQuery->whereHas('employee', function($empQuery) use ($policyId) {
                        $empQuery->whereHas('department', function($deptQuery) use ($policyId) {
                            $deptQuery->whereHas('attendancePolicies', function($policyQuery) use ($policyId) {
                                $policyQuery->where('attendance_policies.id', $policyId);
                            });
                        });
                    });
                })->orWhereHas('attendanceLocation', function($locQuery) use ($policyId) {
                    $locQuery->whereHas('policies', function($policyQuery) use ($policyId) {
                        $policyQuery->where('attendance_policies.id', $policyId);
                    });
                });
            });
        }
        
        // If user can't view all, show only their own
        if (!$canViewAll) {
            $query->where('user_id', $user->id);
            
            // For regular users, ignore employee_id filter if they try to filter by someone else
            if ($request->filled('employee_id') && $request->employee_id != $user->id) {
                $query->where('user_id', $user->id); // Force their own ID
            }
            
            // Ignore department filter for regular users
            // They can only see their own attendance regardless of department filter
        }
        
        // Search - restrict search for regular users to only their own records
        if ($request->filled('search')) {
            $search = $request->search;
            if ($canViewAll) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('employee_id', 'like', "%{$search}%");
                });
            } else {
                // Regular users can only search within their own records
                $query->where('user_id', $user->id);
                $query->where(function($q) use ($search, $user) {
                    $q->whereHas('user', function($userQuery) use ($search, $user) {
                        $userQuery->where('id', $user->id)
                                  ->where(function($subQuery) use ($search) {
                                      $subQuery->where('name', 'like', "%{$search}%")
                                                ->orWhere('email', 'like', "%{$search}%")
                                                ->orWhere('employee_id', 'like', "%{$search}%");
                                  });
                    });
                });
            }
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
                            ->orderBy('time_in', 'desc')
                            ->paginate(50);
        
        // Get filter options
        $employees = $canViewAll 
            ? User::whereHas('employee')->orderBy('name')->get()
            : collect([$user]);
        
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $methods = Attendance::getAttendanceMethods();
        $locations = AttendanceLocation::where('is_active', true)->orderBy('name')->get();
        $schedules = \App\Models\WorkSchedule::where('is_active', true)->orderBy('name')->get();
        $policies = \App\Models\AttendancePolicy::where('is_active', true)->orderBy('name')->get();
        
        // Statistics
        $stats = $this->getStatistics($request, $canViewAll);
        
        // Get first active device for ZKTeco sync section
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
        
        return view('modules.hr.attendance', compact(
            'attendances',
            'employees',
            'departments',
            'methods',
            'locations',
            'schedules',
            'policies',
            'stats',
            'canViewAll',
            'canManage',
            'deviceIp',
            'devicePort',
            'deviceCommKey'
        ));
    }

    /**
     * Get attendance statistics
     */
    private function getStatistics(Request $request, $canViewAll)
    {
        $query = Attendance::query();
        
        // Apply same filters as main query
        if ($request->filled('report_period')) {
            $reportPeriod = $request->report_period;
            $today = Carbon::today($this->getTimezone());
            
            switch ($reportPeriod) {
                case 'today':
                    $query->whereDate('attendance_date', $today);
                    break;
                case 'yesterday':
                    $query->whereDate('attendance_date', $today->copy()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('attendance_date', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek()
                    ]);
                    break;
                case 'last_week':
                    $query->whereBetween('attendance_date', [
                        $today->copy()->subWeek()->startOfWeek(),
                        $today->copy()->subWeek()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth('attendance_date', $today->month)
                          ->whereYear('attendance_date', $today->year);
                    break;
                case 'last_month':
                    $query->whereMonth('attendance_date', $today->copy()->subMonth()->month)
                          ->whereYear('attendance_date', $today->copy()->subMonth()->year);
                    break;
                case 'this_year':
                    $query->whereYear('attendance_date', $today->year);
                    break;
                case 'last_year':
                    $query->whereYear('attendance_date', $today->copy()->subYear()->year);
                    break;
                case 'custom':
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $query->dateRange($request->start_date, $request->end_date);
                    }
                    break;
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        } else {
            $query->whereMonth('attendance_date', Carbon::now()->month)
                  ->whereYear('attendance_date', Carbon::now()->year);
        }
        
        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('primary_department_id', $request->department_id);
            });
        }
        
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        
        if (!$canViewAll) {
            $query->where('user_id', Auth::id());
        }
        
        return [
            'total_records' => $query->count(),
            'present' => (clone $query)->where('status', Attendance::STATUS_PRESENT)->count(),
            'absent' => (clone $query)->where('status', Attendance::STATUS_ABSENT)->count(),
            'late' => (clone $query)->where('is_late', true)->count(),
            'pending_verification' => (clone $query)->where('verification_status', Attendance::VERIFICATION_PENDING)->count(),
            'average_hours' => (clone $query)->whereNotNull('total_hours')->avg('total_hours') ?? 0,
        ];
    }

    /**
     * Record time in
     */
    public function timeIn(Request $request)
    {
        $user = Auth::user();
        $timezone = $this->getTimezone();
        $today = Carbon::today($timezone);
        
        // Check if already clocked in today
        $existing = Attendance::where('user_id', $user->id)
                              ->where('attendance_date', $today)
                              ->first();
        
        if ($existing && $existing->time_in) {
            return response()->json([
                'success' => false,
                'message' => 'You have already clocked in today.',
            ], 422);
        }
        
        $validator = Validator::make($request->all(), [
            'attendance_method' => 'required|in:' . implode(',', array_keys(Attendance::getAttendanceMethods())),
            'device_id' => 'nullable|string|max:255',
            'device_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Validate location for manual attendance (must be near office)
        $locationValidation = null;
        $detectedLocation = null;
        if ($request->attendance_method === Attendance::METHOD_MANUAL) {
            $locationValidation = $this->validateOfficeLocation($request->latitude, $request->longitude);
            if (!$locationValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $locationValidation['message'],
                    'distance' => $locationValidation['distance'] ?? null,
                    'required_radius' => $locationValidation['required_radius'] ?? null,
                    'nearest_location' => $locationValidation['nearest_location'] ?? null,
                ], 422);
            }
            $detectedLocation = $locationValidation['matched_location'] ?? null;
        }
        
        try {
            DB::beginTransaction();
            
            $attendance = $existing ?? new Attendance();
            $attendance->user_id = $user->id;
            $attendance->employee_id = $user->employee?->id;
            $attendance->attendance_date = $today;
            $attendance->time_in = Carbon::now($timezone)->format('H:i:s');
            $attendance->attendance_method = $request->attendance_method;
            $attendance->device_id = $request->device_id;
            $attendance->device_type = $request->device_type;
            $attendance->location = $request->location;
            $attendance->latitude = $request->latitude;
            $attendance->longitude = $request->longitude;
            $attendance->ip_address = $request->ip();
            $attendance->notes = $request->notes;
            $attendance->status = Attendance::STATUS_PRESENT;
            
            // Set location_id if matched
            if ($detectedLocation) {
                $attendance->location_id = $detectedLocation->id;
            }
            
            // Auto-detect location name using reverse geocoding
            if ($request->latitude && $request->longitude) {
                try {
                    $geocodingService = new GeocodingService();
                    $locationName = $geocodingService->getLocationName(
                        $request->latitude,
                        $request->longitude,
                        $detectedLocation ? $detectedLocation->name : null
                    );
                    $attendance->location_name = $locationName;
                } catch (\Exception $e) {
                    Log::warning('Failed to get location name', [
                        'error' => $e->getMessage(),
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude
                    ]);
                    // Fallback to location name if available
                    $attendance->location_name = $detectedLocation ? $detectedLocation->name : ($request->location ?? null);
                }
            }
            
            // Check if late (assuming 9:00 AM as standard time in)
            $expectedTimeIn = '09:00:00';
            // Temporarily set time_in as string for checkLate to work properly
            $timeInStr = $attendance->time_in;
            $attendance->is_late = false;
            try {
                $expected = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $expectedTimeIn);
                $actual = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $timeInStr);
                $attendance->is_late = $actual->gt($expected);
            } catch (\Exception $e) {
                \Log::warning('Error checking late status: ' . $e->getMessage());
            }
            
            if ($attendance->is_late) {
                $attendance->status = Attendance::STATUS_LATE;
            }
            
            // Set verification status based on method
            // Manual attendance always requires HR verification
            if ($request->attendance_method === Attendance::METHOD_MANUAL) {
                $attendance->verification_status = Attendance::VERIFICATION_PENDING;
            } elseif (in_array($request->attendance_method, [
                Attendance::METHOD_BIOMETRIC,
                Attendance::METHOD_FINGERPRINT,
                Attendance::METHOD_FACE_RECOGNITION,
                Attendance::METHOD_RFID
            ])) {
                $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
            } else {
                // Other methods (mobile_app, card_swipe) also require verification
                $attendance->verification_status = Attendance::VERIFICATION_PENDING;
            }
            
            $attendance->save();
            
            DB::commit();
            
            // Send SMS notifications for sign in
            $this->sendSignInNotifications($user, $attendance);
            
            // Notify HR if manual attendance requires verification
            if ($attendance->verification_status === Attendance::VERIFICATION_PENDING) {
                try {
                    $this->notifyHRForVerification($user, $attendance, 'clock_in');
                } catch (\Exception $e) {
                    \Log::warning('Failed to notify HR for verification: ' . $e->getMessage());
                }
            }
            
            $message = 'Time in recorded successfully.';
            if ($attendance->verification_status === Attendance::VERIFICATION_PENDING) {
                $message .= ' Your attendance is pending HR verification.';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'attendance' => $attendance->load('user'),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Time in error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record time in: ' . $e->getMessage(),
                'error_details' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Record time out
     */
    public function timeOut(Request $request)
    {
        $user = Auth::user();
        $timezone = $this->getTimezone();
        $today = Carbon::today($timezone);
        
        $attendance = Attendance::where('user_id', $user->id)
                               ->where('attendance_date', $today)
                               ->first();
        
        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Please clock in first.',
            ], 422);
        }
        
        if ($attendance->time_out) {
            return response()->json([
                'success' => false,
                'message' => 'You have already clocked out today.',
            ], 422);
        }
        
        $validator = Validator::make($request->all(), [
            'attendance_method' => 'nullable|in:' . implode(',', array_keys(Attendance::getAttendanceMethods())),
            'device_id' => 'nullable|string|max:255',
            'device_type' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Validate location for manual attendance (must be near office)
        $locationValidation = null;
        $detectedLocation = null;
        $attendanceMethod = $request->attendance_method ?? $attendance->attendance_method;
        if ($attendanceMethod === Attendance::METHOD_MANUAL) {
            $locationValidation = $this->validateOfficeLocation($request->latitude, $request->longitude);
            if (!$locationValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $locationValidation['message'],
                    'distance' => $locationValidation['distance'] ?? null,
                    'required_radius' => $locationValidation['required_radius'] ?? null,
                    'nearest_location' => $locationValidation['nearest_location'] ?? null,
                ], 422);
            }
            $detectedLocation = $locationValidation['matched_location'] ?? null;
        }
        
        try {
            DB::beginTransaction();
            
            $attendance->time_out = Carbon::now($timezone)->format('H:i:s');
            $attendance->attendance_method = $request->attendance_method ?? $attendance->attendance_method;
            $attendance->device_id = $request->device_id ?? $attendance->device_id;
            $attendance->device_type = $request->device_type ?? $attendance->device_type;
            $attendance->location = $request->location ?? $attendance->location;
            $attendance->latitude = $request->latitude ?? $attendance->latitude;
            $attendance->longitude = $request->longitude ?? $attendance->longitude;
            $attendance->notes = $request->notes ?? $attendance->notes;
            
            // Update location_id if matched
            if ($detectedLocation) {
                $attendance->location_id = $detectedLocation->id;
            }
            
            // Auto-detect location name using reverse geocoding
            if ($request->latitude && $request->longitude) {
                try {
                    $geocodingService = new GeocodingService();
                    $locationName = $geocodingService->getLocationName(
                        $request->latitude,
                        $request->longitude,
                        $detectedLocation ? $detectedLocation->name : null
                    );
                    $attendance->location_name = $locationName;
                } catch (\Exception $e) {
                    Log::warning('Failed to get location name', [
                        'error' => $e->getMessage(),
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude
                    ]);
                    // Fallback to location name if available
                    $attendance->location_name = $detectedLocation ? $detectedLocation->name : ($request->location ?? null);
                }
            }
            
            // If manual method, ensure verification status is pending
            if ($attendance->attendance_method === Attendance::METHOD_MANUAL && 
                $attendance->verification_status !== Attendance::VERIFICATION_VERIFIED) {
                $attendance->verification_status = Attendance::VERIFICATION_PENDING;
            }
            
            // Calculate total hours
            $attendance->total_hours = $attendance->calculateTotalHours();
            
            // Check for early leave (assuming 5:00 PM as standard time out)
            $expectedTimeOut = '17:00:00';
            
            // Extract time string from time_out (handle datetime cast)
            $timeOutValue = $attendance->time_out;
            if ($timeOutValue instanceof Carbon) {
                $timeOutStr = $timeOutValue->format('H:i:s');
            } elseif (is_string($timeOutValue) && strpos($timeOutValue, ' ') !== false) {
                // It's a datetime string, extract just the time part
                $timeOutStr = Carbon::parse($timeOutValue)->format('H:i:s');
            } else {
                // It's already a time string
                $timeOutStr = $timeOutValue;
            }
            
            $actualTimeOut = Carbon::parse($today->format('Y-m-d') . ' ' . $timeOutStr);
            $expectedTimeOutCarbon = Carbon::parse($today->format('Y-m-d') . ' ' . $expectedTimeOut);
            
            if ($actualTimeOut->lt($expectedTimeOutCarbon)) {
                $attendance->is_early_leave = true;
                if ($attendance->status === Attendance::STATUS_PRESENT) {
                    $attendance->status = Attendance::STATUS_EARLY_LEAVE;
                }
            }
            
            // Check for overtime
            if ($actualTimeOut->gt($expectedTimeOutCarbon)) {
                $overtimeMinutes = $actualTimeOut->diffInMinutes($expectedTimeOutCarbon);
                if ($overtimeMinutes > 30) { // More than 30 minutes
                    $attendance->is_overtime = true;
                }
            }
            
            $attendance->save();
            
            DB::commit();
            
            // Send SMS notifications for sign out
            $this->sendSignOutNotifications($user, $attendance);
            
            // Notify HR if manual attendance requires verification
            if ($attendance->verification_status === Attendance::VERIFICATION_PENDING) {
                try {
                    $this->notifyHRForVerification($user, $attendance, 'clock_out');
                } catch (\Exception $e) {
                    \Log::warning('Failed to notify HR for verification: ' . $e->getMessage());
                }
            }
            
            $message = 'Time out recorded successfully.';
            if ($attendance->verification_status === Attendance::VERIFICATION_PENDING) {
                $message .= ' Your attendance is pending HR verification.';
            }
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'attendance' => $attendance->load('user'),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Time out error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to record time out: ' . $e->getMessage(),
                'error_details' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Record break start
     */
    public function breakStart(Request $request)
    {
        $user = Auth::user();
        $timezone = $this->getTimezone();
        $today = Carbon::today($timezone);
        
        try {
            $attendance = Attendance::where('user_id', $user->id)
                                   ->where('attendance_date', $today)
                                   ->first();
            
            if (!$attendance || !$attendance->time_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please clock in first.',
                ], 422);
            }
            
            if ($attendance->break_start) {
                return response()->json([
                    'success' => false,
                    'message' => 'Break already started.',
                ], 422);
            }
            
            $attendance->break_start = Carbon::now($timezone)->format('H:i:s');
            $attendance->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Break started successfully.',
                'attendance' => $attendance->load('user'),
            ]);
        } catch (\Exception $e) {
            Log::error('Break start error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start break: ' . $e->getMessage(),
                'error_details' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Record break end
     */
    public function breakEnd(Request $request)
    {
        $user = Auth::user();
        $timezone = $this->getTimezone();
        $today = Carbon::today($timezone);
        
        try {
            $attendance = Attendance::where('user_id', $user->id)
                                   ->where('attendance_date', $today)
                                   ->first();
            
            if (!$attendance || !$attendance->break_start) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please start break first.',
                ], 422);
            }
            
            if ($attendance->break_end) {
                return response()->json([
                    'success' => false,
                    'message' => 'Break already ended.',
                ], 422);
            }
            
            DB::beginTransaction();
            
            $attendance->break_end = Carbon::now($timezone)->format('H:i:s');
            
            // Calculate break duration
            try {
                $breakStart = Carbon::parse($today->format('Y-m-d') . ' ' . $attendance->break_start);
                $breakEnd = Carbon::parse($today->format('Y-m-d') . ' ' . $attendance->break_end);
                $attendance->break_duration = $breakEnd->diffInMinutes($breakStart);
            } catch (\Exception $e) {
                Log::warning('Error calculating break duration: ' . $e->getMessage());
                // Set a default if calculation fails
                $attendance->break_duration = 0;
            }
            
            // Recalculate total hours
            if ($attendance->time_out) {
                try {
                    $attendance->total_hours = $attendance->calculateTotalHours();
                } catch (\Exception $e) {
                    Log::warning('Error recalculating total hours: ' . $e->getMessage());
                }
            }
            
            $attendance->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Break ended successfully.',
                'attendance' => $attendance->load('user'),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Break end error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to end break: ' . $e->getMessage(),
                'error_details' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get current attendance status
     */
    public function getCurrentStatus()
    {
        $user = Auth::user();
        $timezone = $this->getTimezone();
        $today = Carbon::today($timezone);
        
        $attendance = Attendance::where('user_id', $user->id)
                               ->where('attendance_date', $today)
                               ->first();
        
        // Format attendance data with time strings
        $attendanceData = null;
        if ($attendance) {
            $attendanceData = [
                'id' => $attendance->id,
                'user_id' => $attendance->user_id,
                'attendance_date' => $attendance->attendance_date->format('Y-m-d'),
                'time_in' => $attendance->time_in_string,
                'time_out' => $attendance->time_out_string,
                'break_start' => $attendance->break_start_string,
                'break_end' => $attendance->break_end_string,
                'total_hours' => $attendance->total_hours,
                'break_duration' => $attendance->break_duration,
                'status' => $attendance->status,
                'attendance_method' => $attendance->attendance_method,
                'is_late' => $attendance->is_late,
                'is_early_leave' => $attendance->is_early_leave,
                'is_overtime' => $attendance->is_overtime,
                'verification_status' => $attendance->verification_status,
            ];
        }
        
        return response()->json([
            'success' => true,
            'attendance' => $attendanceData,
            'can_clock_in' => !$attendance || !$attendance->time_in,
            'can_clock_out' => $attendance && $attendance->time_in && !$attendance->time_out,
            'can_start_break' => $attendance && $attendance->time_in && !$attendance->break_start && !$attendance->time_out,
            'can_end_break' => $attendance && $attendance->break_start && !$attendance->break_end,
        ]);
    }

    /**
     * Manual attendance entry (HR only)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'attendance_date' => 'required|date',
            'time_in' => 'required|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i|after:time_in',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'status' => 'required|in:' . implode(',', [
                Attendance::STATUS_PRESENT,
                Attendance::STATUS_ABSENT,
                Attendance::STATUS_LATE,
                Attendance::STATUS_EARLY_LEAVE,
                Attendance::STATUS_HALF_DAY,
                Attendance::STATUS_ON_LEAVE,
            ]),
            'attendance_method' => 'required|in:' . implode(',', array_keys(Attendance::getAttendanceMethods())),
            'notes' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            $attendanceDate = Carbon::parse($request->attendance_date);
            
            // Check if record already exists
            $existing = Attendance::where('user_id', $request->user_id)
                                 ->where('attendance_date', $attendanceDate->format('Y-m-d'))
                                 ->first();
            
            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance record already exists for this date.',
                ], 422);
            }
            
            $employee = Employee::where('user_id', $request->user_id)->first();
            
            $attendance = new Attendance();
            $attendance->user_id = $request->user_id;
            $attendance->employee_id = $employee?->id;
            $attendance->attendance_date = $attendanceDate;
            $attendance->time_in = $request->time_in;
            $attendance->time_out = $request->time_out;
            $attendance->break_start = $request->break_start;
            $attendance->break_end = $request->break_end;
            $attendance->status = $request->status;
            $attendance->attendance_method = $request->attendance_method;
            $attendance->notes = $request->notes;
            $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
            $attendance->approved_by = $user->id;
            $attendance->approved_at = Carbon::now();
            
            // Calculate hours if both times provided
            if ($attendance->time_in && $attendance->time_out) {
                $attendance->total_hours = $attendance->calculateTotalHours();
            }
            
            // Calculate break duration
            if ($attendance->break_start && $attendance->break_end) {
                $breakStart = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $attendance->break_start);
                $breakEnd = Carbon::parse($attendanceDate->format('Y-m-d') . ' ' . $attendance->break_end);
                $attendance->break_duration = $breakEnd->diffInMinutes($breakStart);
            }
            
            $attendance->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance record created successfully.',
                'attendance' => $attendance->load(['user', 'employee']),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Create attendance error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendance record.',
            ], 500);
        }
    }

    /**
     * Update attendance record (HR and System Admin only)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HR Officers and System Admins can update attendance records.',
            ], 403);
        }
        
        $attendance = Attendance::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'time_in' => 'nullable|date_format:H:i',
            'time_out' => 'nullable|date_format:H:i',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i',
            'status' => 'nullable|in:' . implode(',', [
                Attendance::STATUS_PRESENT,
                Attendance::STATUS_ABSENT,
                Attendance::STATUS_LATE,
                Attendance::STATUS_EARLY_LEAVE,
                Attendance::STATUS_HALF_DAY,
                Attendance::STATUS_ON_LEAVE,
            ]),
            'verification_status' => 'nullable|in:' . implode(',', [
                Attendance::VERIFICATION_PENDING,
                Attendance::VERIFICATION_VERIFIED,
                Attendance::VERIFICATION_REJECTED,
            ]),
            'remarks' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            DB::beginTransaction();
            
            if ($request->filled('time_in')) {
                $attendance->time_in = $request->time_in;
            }
            if ($request->filled('time_out')) {
                $attendance->time_out = $request->time_out;
            }
            if ($request->filled('break_start')) {
                $attendance->break_start = $request->break_start;
            }
            if ($request->filled('break_end')) {
                $attendance->break_end = $request->break_end;
            }
            if ($request->filled('status')) {
                $attendance->status = $request->status;
            }
            if ($request->filled('verification_status')) {
                $attendance->verification_status = $request->verification_status;
                if ($request->verification_status === Attendance::VERIFICATION_VERIFIED) {
                    $attendance->approved_by = $user->id;
                    $attendance->approved_at = Carbon::now();
                }
            }
            if ($request->filled('remarks')) {
                $attendance->remarks = $request->remarks;
            }
            if ($request->filled('notes')) {
                $attendance->notes = $request->notes;
            }
            
            // Recalculate hours
            if ($attendance->time_in && $attendance->time_out) {
                $attendance->total_hours = $attendance->calculateTotalHours();
            }
            
            // Recalculate break duration
            if ($attendance->break_start && $attendance->break_end) {
                $breakStart = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $attendance->break_start);
                $breakEnd = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $attendance->break_end);
                $attendance->break_duration = $breakEnd->diffInMinutes($breakStart);
            }
            
            $attendance->save();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance record updated successfully.',
                'attendance' => $attendance->load(['user', 'employee', 'approver']),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update attendance error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendance record.',
            ], 500);
        }
    }

    /**
     * Get single attendance record with detailed information
     */
    public function show($id)
    {
        $user = Auth::user();
        
        // Check permissions - HR and System Admin can view any, others only their own
        $canViewAll = $user->hasAnyRole(['HR Officer', 'System Admin']);
        
        $attendance = Attendance::with([
            'user', 
            'employee', 
            'employee.department', 
            'approver',
            'workSchedule',
            'attendanceLocation',
            'attendanceDevice'
        ])->findOrFail($id);
        
        // If user can't view all, check if it's their own attendance
        if (!$canViewAll && $attendance->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only view your own attendance records.',
            ], 403);
        }
        
        // Get policy based on department or location
        $policy = null;
        if ($attendance->employee && $attendance->employee->department) {
            $policy = \App\Models\AttendancePolicy::where('department_id', $attendance->employee->department->id)
                ->where('is_active', true)
                ->first();
        }
        if (!$policy && $attendance->location_id) {
            $policy = \App\Models\AttendancePolicy::where('location_id', $attendance->location_id)
                ->where('is_active', true)
                ->first();
        }
        
        // Calculate late/early details
        $lateDetails = $this->calculateLateDetails($attendance);
        $earlyLeaveDetails = $this->calculateEarlyLeaveDetails($attendance);
        $timeSpentDetails = $this->calculateTimeSpentDetails($attendance);
        
        // Format attendance data for JSON response
        $attendanceData = $attendance->toArray();
        
        // Ensure check_in_time and check_out_time are properly formatted
        if ($attendance->check_in_time) {
            $attendanceData['check_in_time'] = $attendance->check_in_time->format('Y-m-d H:i:s');
            $attendanceData['check_in_time_formatted'] = $attendance->check_in_time->format('H:i:s');
        }
        if ($attendance->check_out_time) {
            $attendanceData['check_out_time'] = $attendance->check_out_time->format('Y-m-d H:i:s');
            $attendanceData['check_out_time_formatted'] = $attendance->check_out_time->format('H:i:s');
        }
        
        // Format time_in and time_out as strings
        if ($attendance->time_in) {
            $attendanceData['time_in'] = $attendance->time_in_string ?? (is_string($attendance->time_in) ? $attendance->time_in : $attendance->time_in->format('H:i:s'));
        }
        if ($attendance->time_out) {
            $attendanceData['time_out'] = $attendance->time_out_string ?? (is_string($attendance->time_out) ? $attendance->time_out : $attendance->time_out->format('H:i:s'));
        }
        
        // Ensure status is a string, not numeric
        if (is_numeric($attendance->status)) {
            $statusMap = [
                0 => 'present',
                1 => 'absent',
                2 => 'late',
                3 => 'early_leave',
                4 => 'half_day',
                5 => 'on_leave'
            ];
            $attendanceData['status'] = $statusMap[$attendance->status] ?? 'present';
        }
        
        // Auto-verify biometric/device records
        $biometricMethods = ['biometric', 'fingerprint', 'face_recognition', 'rfid'];
        $isDeviceRecord = $attendance->device_ip || 
                         $attendance->attendance_device_id || 
                         $attendance->verify_mode ||
                         ($attendance->attendance_method && in_array(strtolower($attendance->attendance_method), $biometricMethods));
        
        if ($isDeviceRecord) {
            // Auto-verify device records
            $attendanceData['verification_status'] = 'verified';
            // Also update in database if not already verified
            if ($attendance->verification_status !== Attendance::VERIFICATION_VERIFIED) {
                $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
                $attendance->save();
            }
        }
        
        return response()->json([
            'success' => true,
            'attendance' => $attendanceData,
            'policy' => $policy,
            'late_details' => $lateDetails,
            'early_leave_details' => $earlyLeaveDetails,
            'time_spent_details' => $timeSpentDetails,
        ]);
    }
    
    /**
     * Calculate late arrival details
     */
    private function calculateLateDetails($attendance)
    {
        if (!$attendance->workSchedule || !$attendance->check_in_time) {
            return null;
        }
        
        $schedule = $attendance->workSchedule;
        $expectedStart = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $schedule->start_time->format('H:i:s'));
        $actualStart = $attendance->check_in_time;
        
        if ($actualStart->gt($expectedStart)) {
            $lateMinutes = $actualStart->diffInMinutes($expectedStart);
            $lateHours = floor($lateMinutes / 60);
            $lateMins = $lateMinutes % 60;
            
            return [
                'is_late' => true,
                'late_minutes' => $lateMinutes,
                'late_hours' => $lateHours,
                'late_minutes_remainder' => $lateMins,
                'expected_time' => $expectedStart->format('H:i:s'),
                'actual_time' => $actualStart->format('H:i:s'),
                'tolerance_minutes' => $schedule->late_tolerance_minutes ?? 15,
                'within_tolerance' => $lateMinutes <= ($schedule->late_tolerance_minutes ?? 15),
            ];
        }
        
        return [
            'is_late' => false,
            'expected_time' => $expectedStart->format('H:i:s'),
            'actual_time' => $actualStart->format('H:i:s'),
        ];
    }
    
    /**
     * Calculate early leave details
     */
    private function calculateEarlyLeaveDetails($attendance)
    {
        if (!$attendance->workSchedule || !$attendance->check_out_time) {
            return null;
        }
        
        $schedule = $attendance->workSchedule;
        $expectedEnd = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $schedule->end_time->format('H:i:s'));
        $actualEnd = $attendance->check_out_time;
        
        if ($actualEnd->lt($expectedEnd)) {
            $earlyMinutes = $expectedEnd->diffInMinutes($actualEnd);
            $earlyHours = floor($earlyMinutes / 60);
            $earlyMins = $earlyMinutes % 60;
            
            return [
                'is_early' => true,
                'early_minutes' => $earlyMinutes,
                'early_hours' => $earlyHours,
                'early_minutes_remainder' => $earlyMins,
                'expected_time' => $expectedEnd->format('H:i:s'),
                'actual_time' => $actualEnd->format('H:i:s'),
                'tolerance_minutes' => $schedule->early_leave_tolerance_minutes ?? 15,
                'within_tolerance' => $earlyMinutes <= ($schedule->early_leave_tolerance_minutes ?? 15),
            ];
        }
        
        return [
            'is_early' => false,
            'expected_time' => $expectedEnd->format('H:i:s'),
            'actual_time' => $actualEnd->format('H:i:s'),
        ];
    }
    
    /**
     * Calculate time spent in office
     */
    private function calculateTimeSpentDetails($attendance)
    {
        if (!$attendance->check_in_time) {
            return null;
        }
        
        $checkIn = $attendance->check_in_time;
        $checkOut = $attendance->check_out_time ?? Carbon::now();
        
        $totalMinutes = $checkOut->diffInMinutes($checkIn);
        $totalHours = floor($totalMinutes / 60);
        $totalMins = $totalMinutes % 60;
        
        // Subtract break duration if exists
        $breakMinutes = $attendance->break_duration ?? 0;
        $workMinutes = max(0, $totalMinutes - $breakMinutes);
        $workHours = floor($workMinutes / 60);
        $workMins = $workMinutes % 60;
        
        // Expected work hours from schedule
        $expectedHours = 0;
        if ($attendance->workSchedule) {
            $expectedHours = $attendance->workSchedule->work_hours ?? 8;
        }
        
        return [
            'check_in' => $checkIn->format('H:i:s'),
            'check_out' => $checkOut ? $checkOut->format('H:i:s') : 'Not checked out',
            'total_minutes' => $totalMinutes,
            'total_hours' => $totalHours,
            'total_minutes_remainder' => $totalMins,
            'break_minutes' => $breakMinutes,
            'work_minutes' => $workMinutes,
            'work_hours' => $workHours,
            'work_minutes_remainder' => $workMins,
            'expected_hours' => $expectedHours,
            'hours_difference' => $workHours - $expectedHours,
            'is_complete' => $attendance->check_out_time !== null,
        ];
    }
    
    /**
     * Download PDF for single attendance record
     */
    public function downloadPDF($id)
    {
        $user = Auth::user();
        $canViewAll = $user->hasAnyRole(['HR Officer', 'System Admin']);
        
        $attendance = Attendance::with([
            'user', 
            'employee', 
            'employee.department', 
            'approver',
            'workSchedule',
            'attendanceLocation',
            'attendanceDevice'
        ])->findOrFail($id);
        
        // If user can't view all, check if it's their own attendance
        if (!$canViewAll && $attendance->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        
        // Get policy
        $policy = null;
        if ($attendance->employee && $attendance->employee->department) {
            $policy = \App\Models\AttendancePolicy::where('department_id', $attendance->employee->department->id)
                ->where('is_active', true)
                ->first();
        }
        if (!$policy && $attendance->location_id) {
            $policy = \App\Models\AttendancePolicy::where('location_id', $attendance->location_id)
                ->where('is_active', true)
                ->first();
        }
        
        // Calculate details
        $lateDetails = $this->calculateLateDetails($attendance);
        $earlyLeaveDetails = $this->calculateEarlyLeaveDetails($attendance);
        $timeSpentDetails = $this->calculateTimeSpentDetails($attendance);
        
        $filename = 'attendance_' . $attendance->id . '_' . date('Y-m-d_His') . '.pdf';
        
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $html = view('modules.hr.attendance-single-pdf', compact('attendance', 'policy', 'lateDetails', 'earlyLeaveDetails', 'timeSpentDetails'))->render();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            return $pdf->download($filename);
        } else {
            // Fallback: return HTML view
            return view('modules.hr.attendance-single-pdf', compact('attendance', 'policy', 'lateDetails', 'earlyLeaveDetails', 'timeSpentDetails'));
        }
    }
    
    /**
     * Verify attendance record (HR and System Admin only)
     */
    public function verify(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HR Officers and System Admins can verify attendance.',
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'verification_status' => 'required|in:' . implode(',', [
                Attendance::VERIFICATION_VERIFIED,
                Attendance::VERIFICATION_REJECTED,
            ]),
            'notes' => 'nullable|string|max:1000',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        try {
            $attendance = Attendance::findOrFail($id);
            
            DB::beginTransaction();
            
            $attendance->verification_status = $request->verification_status;
            $attendance->approved_by = $user->id;
            $attendance->approved_at = Carbon::now($this->getTimezone());
            
            if ($request->filled('notes')) {
                $attendance->notes = ($attendance->notes ? $attendance->notes . "\n\n" : '') . 
                                   '[HR Verification: ' . now()->format('Y-m-d H:i:s') . '] ' . $request->notes;
            }
            
            $attendance->save();
            
            DB::commit();
            
            // Notify employee of verification result
            try {
                $notificationService = new NotificationService();
                $statusText = $request->verification_status === Attendance::VERIFICATION_VERIFIED 
                    ? 'verified' 
                    : 'rejected';
                $message = "Your attendance for {$attendance->attendance_date->format('M d, Y')} has been {$statusText} by HR.";
                $url = route('modules.hr.attendance');
                
                $notificationService->notify(
                    $attendance->user_id,
                    $message,
                    $url,
                    'Attendance Verification Update',
                    ['attendance_id' => $attendance->id, 'status' => $request->verification_status]
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to notify employee of verification: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance verification updated successfully.',
                'attendance' => $attendance->load(['user', 'employee', 'approver']),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Verify attendance error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'attendance_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify attendance: ' . $e->getMessage(),
                'error_details' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Delete all attendance records (HR and System Admin only)
     */
    public function deleteAll(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HR Officers and System Admins can delete all attendance records.',
            ], 403);
        }
        
        try {
            $count = Attendance::count();
            
            if ($count === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No attendance records to delete'
                ], 400);
            }
            
            // Permanently delete all attendance records
            Attendance::query()->delete();
            
            Log::info("Deleted all {$count} attendance records from database", [
                'user_id' => $user->id,
                'deleted_count' => $count
            ]);
            
            ActivityLogService::log(
                'attendance_deleted_all',
                "Deleted all {$count} attendance records",
                null,
                ['deleted_count' => $count]
            );
            
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$count} attendance record(s) from database",
                'deleted_count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Delete all attendances error: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance records: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete attendance record (HR only)
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->hasAnyRole(['HR Officer', 'System Admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }
        
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully.',
        ]);
    }

    /**
     * API endpoint for biometric devices
     */
    public function biometricRecord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|string',
            'device_id' => 'required|string',
            'device_type' => 'required|string',
            'action' => 'required|in:in,out',
            'timestamp' => 'required|date',
            'biometric_data' => 'nullable|string',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Find user by employee_id
        $user = User::where('employee_id', $request->employee_id)
                   ->orWhereHas('employee', function($q) use ($request) {
                       $q->where('employee_number', $request->employee_id);
                   })
                   ->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        }
        
        $timestamp = Carbon::parse($request->timestamp);
        $attendanceDate = $timestamp->format('Y-m-d');
        $time = $timestamp->format('H:i:s');
        
        $attendance = Attendance::where('user_id', $user->id)
                               ->where('attendance_date', $attendanceDate)
                               ->first();
        
        if (!$attendance) {
            $attendance = new Attendance();
            $attendance->user_id = $user->id;
            $attendance->employee_id = $user->employee?->id;
            $attendance->attendance_date = $attendanceDate;
        }
        
        if ($request->action === 'in') {
            $attendance->time_in = $time;
            $attendance->status = Attendance::STATUS_PRESENT;
            $attendance->is_late = $attendance->checkLate();
        } else {
            $attendance->time_out = $time;
            if ($attendance->time_in) {
                $attendance->total_hours = $attendance->calculateTotalHours();
            }
        }
        
        $attendance->attendance_method = Attendance::METHOD_BIOMETRIC;
        $attendance->device_id = $request->device_id;
        $attendance->device_type = $request->device_type;
        $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
        $attendance->metadata = [
            'biometric_data' => $request->biometric_data,
            'device_timestamp' => $request->timestamp,
        ];
        $attendance->ip_address = $request->ip();
        $attendance->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully.',
            'attendance' => $attendance,
        ]);
    }

    /**
     * Export attendance data
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $canViewAll = $user->hasAnyRole(['HR Officer', 'System Admin', 'CEO', 'HOD']);
        
        $format = $request->get('format', 'excel'); // excel, pdf, csv
        
        $query = Attendance::with(['user', 'employee', 'employee.department', 'workSchedule', 'attendanceLocation']);
        
        // Apply same filters as index method
        if ($request->filled('report_period')) {
            $reportPeriod = $request->report_period;
            $today = Carbon::today($this->getTimezone());
            
            switch ($reportPeriod) {
                case 'today':
                    $query->whereDate('attendance_date', $today);
                    break;
                case 'yesterday':
                    $query->whereDate('attendance_date', $today->copy()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('attendance_date', [
                        $today->copy()->startOfWeek(),
                        $today->copy()->endOfWeek()
                    ]);
                    break;
                case 'last_week':
                    $query->whereBetween('attendance_date', [
                        $today->copy()->subWeek()->startOfWeek(),
                        $today->copy()->subWeek()->endOfWeek()
                    ]);
                    break;
                case 'this_month':
                    $query->whereMonth('attendance_date', $today->month)
                          ->whereYear('attendance_date', $today->year);
                    break;
                case 'last_month':
                    $query->whereMonth('attendance_date', $today->copy()->subMonth()->month)
                          ->whereYear('attendance_date', $today->copy()->subMonth()->year);
                    break;
                case 'this_year':
                    $query->whereYear('attendance_date', $today->year);
                    break;
                case 'last_year':
                    $query->whereYear('attendance_date', $today->copy()->subYear()->year);
                    break;
                case 'custom':
                    if ($request->filled('start_date') && $request->filled('end_date')) {
                        $query->dateRange($request->start_date, $request->end_date);
                    }
                    break;
            }
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }
        
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }
        
        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('primary_department_id', $request->department_id);
            });
        }
        
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        
        if ($request->filled('method')) {
            $query->byMethod($request->method);
        }
        
        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }
        
        if ($request->filled('schedule_id')) {
            $query->where('schedule_id', $request->schedule_id);
        }
        
        if ($request->filled('policy_id')) {
            $policyId = $request->policy_id;
            $query->where(function($q) use ($policyId) {
                $q->whereHas('user', function($userQuery) use ($policyId) {
                    $userQuery->whereHas('employee', function($empQuery) use ($policyId) {
                        $empQuery->whereHas('department', function($deptQuery) use ($policyId) {
                            $deptQuery->whereHas('attendancePolicies', function($policyQuery) use ($policyId) {
                                $policyQuery->where('attendance_policies.id', $policyId);
                            });
                        });
                    });
                })->orWhereHas('attendanceLocation', function($locQuery) use ($policyId) {
                    $locQuery->whereHas('policies', function($policyQuery) use ($policyId) {
                        $policyQuery->where('attendance_policies.id', $policyId);
                    });
                });
            });
        }
        
        if (!$canViewAll) {
            $query->where('user_id', $user->id);
        }
        
        $attendances = $query->orderBy('attendance_date', 'desc')
                            ->orderBy('time_in', 'desc')
                            ->get();
        
        if ($format === 'pdf') {
            return $this->exportPDF($attendances, $request);
        } elseif ($format === 'excel') {
            return $this->exportExcel($attendances, $request);
        } else {
            // Default to CSV
            return $this->exportCSV($attendances, $request);
        }
    }
    
    /**
     * Export to CSV
     */
    private function exportCSV($attendances, $request)
    {
        $filename = 'attendance_export_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID',
                'Date',
                'Employee Name',
                'Employee ID',
                'Enroll ID',
                'Department',
                'Check In',
                'Check Out',
                'Status',
                'Verify Mode',
                'Device IP',
                'Schedule',
                'Location',
                'Total Hours',
                'Late',
                'Early Leave',
                'Overtime',
                'Verification Status',
            ]);
            
            // Data
            foreach ($attendances as $attendance) {
                $checkIn = $attendance->check_in_time 
                    ? $attendance->check_in_time->format('H:i:s') 
                    : ($attendance->time_in ? (is_string($attendance->time_in) ? $attendance->time_in : $attendance->time_in->format('H:i:s')) : '');
                $checkOut = $attendance->check_out_time 
                    ? $attendance->check_out_time->format('H:i:s') 
                    : ($attendance->time_out ? (is_string($attendance->time_out) ? $attendance->time_out : $attendance->time_out->format('H:i:s')) : '');
                
                fputcsv($file, [
                    $attendance->id,
                    $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : '',
                    $attendance->user->name ?? '',
                    $attendance->user->employee_id ?? ($attendance->user->employee->employee_id ?? ''),
                    $attendance->enroll_id ?? '',
                    $attendance->employee->department->name ?? '',
                    $checkIn,
                    $checkOut,
                    ucfirst(str_replace('_', ' ', $attendance->status ?? 'N/A')),
                    $attendance->verify_mode ?? 'N/A',
                    $attendance->device_ip ?? 'N/A',
                    $attendance->workSchedule->name ?? 'N/A',
                    $attendance->attendanceLocation->name ?? ($attendance->location_name ?? 'N/A'),
                    $attendance->formatted_total_hours ?? '0:00',
                    $attendance->is_late ? 'Yes' : 'No',
                    $attendance->is_early_leave ? 'Yes' : 'No',
                    $attendance->is_overtime ? 'Yes' : 'No',
                    ucfirst($attendance->verification_status ?? 'N/A'),
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Export to Excel
     */
    private function exportExcel($attendances, $request)
    {
        $filename = 'attendance_export_' . date('Y-m-d_His') . '.xlsx';
        
        // Use PhpSpreadsheet if available, otherwise fallback to CSV with .xlsx extension
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = ['ID', 'Date', 'Employee Name', 'Employee ID', 'Enroll ID', 'Department', 'Check In', 'Check Out', 'Status', 'Verify Mode', 'Device IP', 'Schedule', 'Location', 'Total Hours', 'Late', 'Early Leave', 'Overtime', 'Verification Status'];
            $sheet->fromArray($headers, null, 'A1');
            
            // Style header row
            $sheet->getStyle('A1:R1')->getFont()->setBold(true);
            $sheet->getStyle('A1:R1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF4472C4');
            $sheet->getStyle('A1:R1')->getFont()->getColor()->setARGB('FFFFFFFF');
            
            // Add data
            $row = 2;
            foreach ($attendances as $attendance) {
                $checkIn = $attendance->check_in_time 
                    ? $attendance->check_in_time->format('H:i:s') 
                    : ($attendance->time_in ? (is_string($attendance->time_in) ? $attendance->time_in : $attendance->time_in->format('H:i:s')) : '');
                $checkOut = $attendance->check_out_time 
                    ? $attendance->check_out_time->format('H:i:s') 
                    : ($attendance->time_out ? (is_string($attendance->time_out) ? $attendance->time_out : $attendance->time_out->format('H:i:s')) : '');
                
                $sheet->setCellValue('A' . $row, $attendance->id);
                $sheet->setCellValue('B' . $row, $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : '');
                $sheet->setCellValue('C' . $row, $attendance->user->name ?? '');
                $sheet->setCellValue('D' . $row, $attendance->user->employee_id ?? ($attendance->user->employee->employee_id ?? ''));
                $sheet->setCellValue('E' . $row, $attendance->enroll_id ?? '');
                $sheet->setCellValue('F' . $row, $attendance->employee->department->name ?? '');
                $sheet->setCellValue('G' . $row, $checkIn);
                $sheet->setCellValue('H' . $row, $checkOut);
                $sheet->setCellValue('I' . $row, ucfirst(str_replace('_', ' ', $attendance->status ?? 'N/A')));
                $sheet->setCellValue('J' . $row, $attendance->verify_mode ?? 'N/A');
                $sheet->setCellValue('K' . $row, $attendance->device_ip ?? 'N/A');
                $sheet->setCellValue('L' . $row, $attendance->workSchedule->name ?? 'N/A');
                $sheet->setCellValue('M' . $row, $attendance->attendanceLocation->name ?? ($attendance->location_name ?? 'N/A'));
                $sheet->setCellValue('N' . $row, $attendance->formatted_total_hours ?? '0:00');
                $sheet->setCellValue('O' . $row, $attendance->is_late ? 'Yes' : 'No');
                $sheet->setCellValue('P' . $row, $attendance->is_early_leave ? 'Yes' : 'No');
                $sheet->setCellValue('Q' . $row, $attendance->is_overtime ? 'Yes' : 'No');
                $sheet->setCellValue('R' . $row, ucfirst($attendance->verification_status ?? 'N/A'));
                $row++;
            }
            
            // Auto-size columns
            foreach (range('A', 'R') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $tempFile = tempnam(sys_get_temp_dir(), 'attendance_export');
            $writer->save($tempFile);
            
            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
        } else {
            // Fallback to CSV with .xlsx extension (will open in Excel)
            return $this->exportCSV($attendances, $request);
        }
    }
    
    /**
     * Export to PDF
     */
    private function exportPDF($attendances, $request)
    {
        $filename = 'attendance_export_' . date('Y-m-d_His') . '.pdf';
        
        // Use DomPDF if available, otherwise return error
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $html = view('modules.hr.attendance-export-pdf', compact('attendances'))->render();
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            return $pdf->download($filename);
        } else {
            // Fallback: return HTML view that can be printed as PDF
            return view('modules.hr.attendance-export-pdf', compact('attendances'));
        }
    }

    /**
     * Send SMS notifications for sign in
     * Sends SMS to both the staff member who signed in and all HR Officers
     */
    private function sendSignInNotifications(User $user, Attendance $attendance)
    {
        try {
            $notificationService = new NotificationService();
            $timeIn = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $attendance->time_in);
            $date = $attendance->attendance_date->format('M d, Y');
            $time = $timeIn->format('h:i A');
            
            // Get location info for messages
            $locationInfo = '';
            if ($attendance->location_name) {
                $locationInfo = " Location: {$attendance->location_name}.";
            } elseif ($attendance->attendanceLocation) {
                $locationInfo = " Location: {$attendance->attendanceLocation->name}.";
            }
            
            // Get inspirational message based on time of day
            $hour = (int)Carbon::now()->format('H');
            $inspirationalMessage = '';
            if ($hour >= 5 && $hour < 12) {
                // Morning messages
                $morningMessages = [
                    "Good morning! 🌅 Start your day with positivity and purpose.",
                    "Rise and shine! ☀️ Today is a new opportunity to excel.",
                    "Morning! 💪 Let's make today productive and successful.",
                    "Good morning! 🌟 Every day is a fresh start - make it count!",
                    "Morning! 🚀 Set your goals and achieve them today!"
                ];
                $inspirationalMessage = $morningMessages[array_rand($morningMessages)];
            } else {
                $inspirationalMessage = "Have a productive day! 💼";
            }
            
            // Message for employee (staff who signed in)
            $employeeMessage = "✅ Check-In Successful!\n\nTime: {$time}\nDate: {$date}{$locationInfo}\n\n{$inspirationalMessage}" . 
                             ($attendance->is_late ? "\n\n⚠️ Note: You are marked as LATE." : "");
            
            // Message for HR and Admin
            $adminMessage = "📋 ATTENDANCE ALERT: {$user->name} has signed in at {$time} on {$date}.{$locationInfo} " .
                          "Method: " . ucfirst(str_replace('_', ' ', $attendance->attendance_method)) . ". " .
                          ($attendance->is_late ? "⚠️ Status: LATE" : "✅ Status: On Time");
            
            // ========== SEND SMS TO STAFF MEMBER WHO SIGNED IN ==========
            $employeePhone = $user->mobile ?? $user->phone;
            if ($employeePhone) {
                try {
                    $smsSent = $notificationService->sendSMS($employeePhone, $employeeMessage);
                    if ($smsSent) {
                        Log::info('Sign in SMS sent successfully to employee', [
                            'user_id' => $user->id,
                            'employee_name' => $user->name,
                            'phone' => $employeePhone
                        ]);
                    } else {
                        Log::warning('Sign in SMS failed to send to employee', [
                            'user_id' => $user->id,
                            'phone' => $employeePhone
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Exception sending sign in SMS to employee', [
                        'user_id' => $user->id,
                        'phone' => $employeePhone,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::warning('No phone number found for employee to send sign in SMS', [
                    'user_id' => $user->id,
                    'employee_name' => $user->name
                ]);
            }
            
            // ========== SEND SMS TO ALL HR OFFICERS ==========
            try {
                $hrOfficers = User::whereHas('roles', function($query) {
                    $query->whereIn('name', ['HR Officer', 'System Admin']);
                })->get();
                
                $smsSentCount = 0;
                $smsFailedCount = 0;
                
                foreach ($hrOfficers as $hrOfficer) {
                    $hrPhone = $hrOfficer->mobile ?? $hrOfficer->phone;
                    if ($hrPhone) {
                        try {
                            $smsSent = $notificationService->sendSMS($hrPhone, $adminMessage);
                            if ($smsSent) {
                                $smsSentCount++;
                                Log::info('Sign in SMS sent to HR Officer', [
                                    'hr_user_id' => $hrOfficer->id,
                                    'hr_name' => $hrOfficer->name,
                                    'phone' => $hrPhone,
                                    'employee' => $user->name
                                ]);
                            } else {
                                $smsFailedCount++;
                                Log::warning('Sign in SMS failed to send to HR Officer', [
                                    'hr_user_id' => $hrOfficer->id,
                                    'phone' => $hrPhone
                                ]);
                            }
                        } catch (\Exception $e) {
                            $smsFailedCount++;
                            Log::error('Exception sending sign in SMS to HR Officer', [
                                'hr_user_id' => $hrOfficer->id,
                                'phone' => $hrPhone,
                                'error' => $e->getMessage()
                            ]);
                        }
                    } else {
                        Log::warning('No phone number found for HR Officer', [
                            'hr_user_id' => $hrOfficer->id,
                            'hr_name' => $hrOfficer->name
                        ]);
                    }
                    
                    // Also send in-app notification
                    try {
                        $notificationService->notify(
                            $hrOfficer->id,
                            $adminMessage,
                            route('modules.hr.attendance'),
                            'Employee Sign In Notification'
                        );
                    } catch (\Exception $e) {
                        Log::warning('Failed to send in-app notification to HR Officer', [
                            'hr_user_id' => $hrOfficer->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                Log::info('Sign in SMS notifications summary', [
                    'employee' => $user->name,
                    'total_hr_officers' => $hrOfficers->count(),
                    'sms_sent_count' => $smsSentCount,
                    'sms_failed_count' => $smsFailedCount
                ]);
                
            } catch (\Exception $e) {
                Log::error('Error sending sign in notifications to HR Officers', [
                    'employee' => $user->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error in sendSignInNotifications method', [
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Validate if user is within office location radius
     * Checks all active locations and finds the nearest one
     */
    private function validateOfficeLocation($latitude, $longitude)
    {
        // Get all active office locations with GPS enabled
        $officeLocations = AttendanceLocation::where('is_active', true)
            ->where('require_gps', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        
        // If no office location is configured, reject attendance (location is required)
        if ($officeLocations->isEmpty()) {
            return [
                'valid' => false,
                'message' => 'No office location is configured. Please contact HR to set up office location with GPS coordinates.',
                'required_radius' => 100,
            ];
        }
        
        // If GPS coordinates are not provided, reject
        if (!$latitude || !$longitude) {
            $defaultRadius = 100; // Default 100 meters
            return [
                'valid' => false,
                'message' => 'GPS location is required for manual attendance. Please enable location services and try again.',
                'required_radius' => $defaultRadius,
            ];
        }
        
        // Check distance to all locations and find the nearest one
        $nearestLocation = null;
        $nearestDistance = null;
        $allDistances = [];
        
        foreach ($officeLocations as $location) {
            $distance = $location->calculateDistance(
                $location->latitude,
                $location->longitude,
                $latitude,
                $longitude
            );
            
            $allDistances[] = [
                'location' => $location->name,
                'distance' => $distance,
                'radius' => $location->radius_meters
            ];
            
            // Track nearest location
            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearestDistance = $distance;
                $nearestLocation = $location;
            }
        }
        
        // Check if within radius of nearest location
        if ($nearestLocation && $nearestDistance <= $nearestLocation->radius_meters) {
            $distanceM = round($nearestDistance, 0);
            $radiusM = $nearestLocation->radius_meters;
            
            return [
                'valid' => true,
                'message' => "Location verified successfully. You are within {$nearestLocation->name} (within {$distanceM}m of {$radiusM}m radius).",
                'distance' => $nearestDistance,
                'required_radius' => $nearestLocation->radius_meters,
                'office_location' => $nearestLocation->name,
                'matched_location' => $nearestLocation,
            ];
        }
        
        // User is outside all locations
        $distanceM = round($nearestDistance, 0);
        $radiusM = $nearestLocation ? $nearestLocation->radius_meters : 100;
        $distanceKm = round($nearestDistance / 1000, 2);
        $radiusKm = round($radiusM / 1000, 2);
        
        $locationName = $nearestLocation ? $nearestLocation->name : 'the office';
        
        return [
            'valid' => false,
            'message' => "You are outside the allowed range. You are {$distanceM}m ({$distanceKm} km) away from {$locationName}. Please be within {$radiusM}m ({$radiusKm} km) to clock in.",
            'distance' => $nearestDistance,
            'required_radius' => $radiusM,
            'nearest_location' => $nearestLocation ? $nearestLocation->name : null,
            'all_distances' => $allDistances,
        ];
    }
    
    /**
     * Get office location settings for frontend
     * Returns all active locations
     */
    public function getOfficeLocation()
    {
        $officeLocations = AttendanceLocation::where('is_active', true)
            ->where('require_gps', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();
        
        if ($officeLocations->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No office locations configured.',
            ]);
        }
        
        $locations = $officeLocations->map(function ($location) {
            return [
                'id' => $location->id,
                'name' => $location->name,
                'address' => $location->address,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'radius_meters' => $location->radius_meters,
                'radius_km' => round($location->radius_meters / 1000, 2),
            ];
        });
        
        return response()->json([
            'success' => true,
            'locations' => $locations,
            'primary_location' => $locations->first(), // For backward compatibility
        ]);
    }
    
    /**
     * Get today's attendance status for current user
     */
    public function today()
    {
        $user = Auth::user();
        $timezone = $this->getTimezone();
        $today = Carbon::today($timezone);
        
        $attendance = Attendance::where('user_id', $user->id)
            ->where('attendance_date', $today)
            ->first();
        
        return response()->json([
            'success' => true,
            'attendance' => $attendance ? [
                'id' => $attendance->id,
                'time_in' => $attendance->time_in ? (is_string($attendance->time_in) ? $attendance->time_in : $attendance->time_in->format('H:i:s')) : null,
                'time_out' => $attendance->time_out ? (is_string($attendance->time_out) ? $attendance->time_out : $attendance->time_out->format('H:i:s')) : null,
                'status' => $attendance->status,
                'verification_status' => $attendance->verification_status,
            ] : null,
        ]);
    }
    
    /**
     * Validate location for manual attendance
     */
    public function validateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Latitude and longitude are required.',
            ], 422);
        }
        
        $validation = $this->validateOfficeLocation($request->latitude, $request->longitude);
        
        return response()->json([
            'success' => true,
            'valid' => $validation['valid'],
            'message' => $validation['message'],
            'distance' => $validation['distance'] ?? null,
            'required_radius' => $validation['required_radius'] ?? null,
            'nearest_location' => $validation['nearest_location'] ?? null,
            'office_location' => $validation['office_location'] ?? null,
        ]);
    }
    
    /**
     * Notify HR for manual attendance verification
     */
    private function notifyHRForVerification(User $user, Attendance $attendance, $action = 'clock_in')
    {
        try {
            $notificationService = new NotificationService();
            
            // Get HR officers
            $hrOfficers = User::whereHas('roles', function($query) {
                $query->whereIn('name', ['HR Officer', 'System Admin']);
            })->get();
            
            $actionText = $action === 'clock_in' ? 'clocked in' : 'clocked out';
            $time = $action === 'clock_in' 
                ? Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $attendance->time_in)->format('h:i A')
                : Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $attendance->time_out)->format('h:i A');
            
            $date = $attendance->attendance_date->format('M d, Y');
            $message = "Manual attendance requires verification: {$user->name} {$actionText} at {$time} on {$date}. Please review and verify.";
            $url = route('modules.hr.attendance') . '?verification_status=pending&employee_id=' . $user->id;
            
            foreach ($hrOfficers as $hrOfficer) {
                $notificationService->notify(
                    $hrOfficer->id,
                    $message,
                    $url,
                    'Manual Attendance Verification Required',
                    ['attendance_id' => $attendance->id, 'employee_name' => $user->name, 'action' => $action]
                );
            }
        } catch (\Exception $e) {
            \Log::error('Error notifying HR for verification: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Send SMS notifications for sign out
     */
    private function sendSignOutNotifications(User $user, Attendance $attendance)
    {
        try {
            $notificationService = new NotificationService();
            $timeOut = Carbon::parse($attendance->attendance_date->format('Y-m-d') . ' ' . $attendance->time_out);
            $date = $attendance->attendance_date->format('M d, Y');
            $time = $timeOut->format('h:i A');
            
            // Calculate total hours
            $totalHours = $attendance->total_hours ? floor($attendance->total_hours / 60) : 0;
            $totalMinutes = $attendance->total_hours ? ($attendance->total_hours % 60) : 0;
            $hoursText = $totalHours > 0 ? "{$totalHours}h {$totalMinutes}m" : "{$totalMinutes}m";
            
            // Get inspirational message for check-out
            $hour = (int)Carbon::now()->format('H');
            $inspirationalMessage = '';
            if ($hour >= 17 && $hour < 22) {
                // Evening messages
                $eveningMessages = [
                    "Well done today! 🌟 Rest well and recharge for tomorrow.",
                    "Great work today! 🌙 Take time to relax and unwind.",
                    "Excellent effort! 💫 You've accomplished a lot today.",
                    "Outstanding work! 🌆 Enjoy your evening and rest well.",
                    "Fantastic day! 🌃 Thank you for your dedication!"
                ];
                $inspirationalMessage = $eveningMessages[array_rand($eveningMessages)];
            } else {
                $inspirationalMessage = "Thank you for your hard work! 🙏";
            }
            
            // Message for employee
            $employeeMessage = "✅ Check-Out Successful!\n\nTime: {$time}\nDate: {$date}\nTotal Hours: {$hoursText}" .
                             ($attendance->is_early_leave ? "\n⚠️ Note: You left early." : "") .
                             ($attendance->is_overtime ? "\n⭐ Note: You worked overtime - great dedication!" : "") .
                             "\n\n{$inspirationalMessage}";
            
            // Message for HR and Admin
            $adminMessage = "{$user->name} has signed out at {$time} on {$date}. " .
                          "Total hours: {$hoursText}. " .
                          ($attendance->is_early_leave ? "⚠️ Status: Early Leave. " : "") .
                          ($attendance->is_overtime ? "✅ Status: Overtime. " : "") .
                          "Method: " . ucfirst(str_replace('_', ' ', $attendance->attendance_method));
            
            // Send SMS to employee
            $employeePhone = $user->mobile ?? $user->phone;
            if ($employeePhone) {
                try {
                    $notificationService->sendSMS($employeePhone, $employeeMessage);
                    Log::info('Sign out SMS sent to employee', [
                        'user_id' => $user->id,
                        'phone' => $employeePhone
                    ]);
                } catch (\Exception $e) {
                    Log::warning('Failed to send sign out SMS to employee', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Send SMS to HR Officers
            try {
                $notificationService->notifyByRole(
                    ['HR Officer'],
                    $adminMessage,
                    route('modules.hr.attendance'),
                    'Employee Sign Out Notification'
                );
                Log::info('Sign out notifications sent to HR Officers', [
                    'employee' => $user->name
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send sign out notifications to HR', [
                    'error' => $e->getMessage()
                ]);
            }
            
            // Send SMS to System Admins
            try {
                $notificationService->notifyByRole(
                    ['System Admin'],
                    $adminMessage,
                    route('modules.hr.attendance'),
                    'Employee Sign Out Notification'
                );
                Log::info('Sign out notifications sent to System Admins', [
                    'employee' => $user->name
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to send sign out notifications to Admins', [
                    'error' => $e->getMessage()
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error sending sign out notifications', [
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

