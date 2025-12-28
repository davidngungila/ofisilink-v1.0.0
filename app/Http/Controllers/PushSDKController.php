<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PushSDKController extends Controller
{
    /**
     * Device ping/command request endpoint
     * GET /iclock/getrequest?SN=XXXXXXXXXX
     * 
     * Device polls this endpoint to:
     * 1. Check if server is available
     * 2. Get commands from server (USER ADD, USER DEL, etc.)
     */
    public function getRequest(Request $request)
    {
        $sn = $request->get('SN');
        $deviceIp = $request->ip();
        
        Log::info('ZKTeco Push SDK ping', [
            'sn' => $sn,
            'ip' => $deviceIp,
            'user_agent' => $request->userAgent(),
        ]);

        // Register/update device in database
        if ($sn) {
            $this->registerOrUpdateDevice($sn, $deviceIp, $request);
        }

        // Get pending commands for this device
        $commands = $this->getPendingCommands($sn);

        // If there are commands, return them; otherwise return OK
        if (!empty($commands)) {
            $commandString = implode("\n", $commands);
            Log::info('Sending commands to device', [
                'sn' => $sn,
                'commands' => $commands,
            ]);
            
            return response($commandString, 200)
                ->header('Content-Type', 'text/plain');
        }

        // Return OK to acknowledge device
        return response('OK', 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Device data push endpoint (attendance/users)
     * POST /iclock/cdata?SN=XXXXXXXXXX&table=ATTLOG&c=log
     */
    public function cdata(Request $request)
    {
        $sn = $request->get('SN');
        $table = $request->get('table');
        $command = $request->get('c');

        Log::info('ZKTeco Push SDK data received', [
            'sn' => $sn,
            'table' => $table,
            'command' => $command,
            'ip' => $request->ip(),
        ]);

        try {
            if ($table === 'ATTLOG' && $command === 'log') {
                // Process attendance log
                $data = $request->getContent();
                $this->processAttendanceLog($data, $sn, $request->ip());
            } elseif ($table === 'USER' && $command === 'log') {
                // Process user data
                $data = $request->getContent();
                $this->processUserLog($data, $sn);
            }

            // Return OK to acknowledge
            return response('OK', 200)
                ->header('Content-Type', 'text/plain');

        } catch (\Exception $e) {
            Log::error('Push SDK data processing error', [
                'sn' => $sn,
                'table' => $table,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Still return OK to prevent device from retrying
            return response('OK', 200)
                ->header('Content-Type', 'text/plain');
        }
    }

    /**
     * Process attendance log from device
     * Format: PIN=1001	DateTime=2025-11-30 14:32:13	Verified=0	Status=0
     */
    private function processAttendanceLog($data, $deviceSn, $deviceIp)
    {
        $lines = explode("\n", trim($data));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            try {
                // Parse line: PIN=1001	DateTime=2025-11-30 14:32:13	Verified=0	Status=0
                $parts = preg_split('/[\s\t]+/', $line);
                $fields = [];
                
                foreach ($parts as $part) {
                    if (strpos($part, '=') !== false) {
                        list($key, $value) = explode('=', $part, 2);
                        $fields[$key] = $value;
                    }
                }

                if (!isset($fields['PIN']) || !isset($fields['DateTime'])) {
                    continue;
                }

                $enrollId = $fields['PIN'];
                $dateTime = $fields['DateTime'];
                $verified = $fields['Verified'] ?? 0;
                $status = $fields['Status'] ?? 0;

                // Find user by enroll_id
                $user = User::where('enroll_id', $enrollId)->first();

                if (!$user) {
                    Log::warning('User not found for attendance log', [
                        'enroll_id' => $enrollId,
                        'datetime' => $dateTime,
                    ]);
                    continue;
                }

                $punchTime = Carbon::parse($dateTime);
                $attendanceDate = $punchTime->format('Y-m-d');

                DB::beginTransaction();

                // Find or create attendance record
                $attendance = Attendance::where('user_id', $user->id)
                                      ->where('attendance_date', $attendanceDate)
                                      ->first();

                if (!$attendance) {
                    $attendance = new Attendance();
                    $attendance->user_id = $user->id;
                    $attendance->employee_id = $user->employee?->id;
                    $attendance->enroll_id = $enrollId;
                    $attendance->attendance_date = $attendanceDate;
                    $attendance->attendance_method = Attendance::METHOD_BIOMETRIC;
                    $attendance->device_ip = $deviceIp;
                    $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
                }

                // Set punch time
                $attendance->punch_time = $punchTime;
                $attendance->status_code = (int)$status;
                $attendance->verify_mode = $this->getVerifyMode((int)$verified);

                // Determine check-in or check-out
                $hasCheckIn = $attendance->check_in_time !== null;
                $hasCheckOut = $attendance->check_out_time !== null;

                if (!$hasCheckIn) {
                    // First scan of the day = Check In
                    $attendance->check_in_time = $punchTime;
                    $attendance->time_in = $punchTime->format('H:i:s');
                    $attendance->status = Attendance::STATUS_PRESENT;
                } elseif (!$hasCheckOut) {
                    // Second scan of the day = Check Out
                    $attendance->check_out_time = $punchTime;
                    $attendance->time_out = $punchTime->format('H:i:s');
                    
                    // Calculate total hours
                    if ($attendance->check_in_time) {
                        $attendance->total_hours = $attendance->calculateTotalHours();
                    }
                }
                // Additional scans are ignored (both times already set)

                $attendance->save();

                DB::commit();

                Log::info('Attendance processed from Push SDK', [
                    'user_id' => $user->id,
                    'enroll_id' => $enrollId,
                    'date' => $attendanceDate,
                    'punch_time' => $punchTime->format('Y-m-d H:i:s'),
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error processing attendance log line', [
                    'line' => $line,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Process user log from device
     * Format: PIN=1001	Name=John Doe	Privilege=0	Card=12345678
     */
    private function processUserLog($data, $deviceSn)
    {
        $lines = explode("\n", trim($data));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            try {
                // Parse line: PIN=1001	Name=John Doe	Privilege=0	Card=12345678
                $parts = preg_split('/[\s\t]+/', $line);
                $fields = [];
                
                foreach ($parts as $part) {
                    if (strpos($part, '=') !== false) {
                        list($key, $value) = explode('=', $part, 2);
                        $fields[$key] = $value;
                    }
                }

                if (!isset($fields['PIN'])) {
                    continue;
                }

                $enrollId = $fields['PIN'];
                $name = $fields['Name'] ?? 'User ' . $enrollId;
                $privilege = $fields['Privilege'] ?? 0;
                $card = $fields['Card'] ?? 0;

                // Find or create user by enroll_id
                $user = User::where('enroll_id', $enrollId)->first();

                if (!$user) {
                    Log::info('Creating new user from device', [
                        'enroll_id' => $enrollId,
                        'name' => $name,
                    ]);
                    
                    // Create user (you may want to adjust this based on your requirements)
                    $user = User::create([
                        'enroll_id' => $enrollId,
                        'name' => $name,
                        'email' => 'user' . $enrollId . '@example.com', // Temporary email
                        'password' => bcrypt('password'), // Temporary password
                        'registered_on_device' => true,
                        'device_registered_at' => now(),
                    ]);
                } else {
                    // Update user info
                    $user->update([
                        'name' => $name,
                        'registered_on_device' => true,
                        'device_registered_at' => now(),
                    ]);
                }

                Log::info('User processed from Push SDK', [
                    'user_id' => $user->id,
                    'enroll_id' => $enrollId,
                    'name' => $name,
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing user log line', [
                    'line' => $line,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Register or update device in database
     */
    private function registerOrUpdateDevice($serialNumber, $ipAddress, Request $request)
    {
        try {
            $device = AttendanceDevice::where('serial_number', $serialNumber)
                ->orWhere('device_id', $serialNumber)
                ->first();

            if ($device) {
                // Update existing device
                $device->update([
                    'ip_address' => $ipAddress,
                    'is_online' => true,
                    'last_sync_at' => now(),
                ]);
            } else {
                // Create new device
                AttendanceDevice::create([
                    'name' => 'ZKTeco Device ' . $serialNumber,
                    'device_id' => $serialNumber,
                    'serial_number' => $serialNumber,
                    'device_type' => AttendanceDevice::TYPE_BIOMETRIC,
                    'manufacturer' => 'ZKTeco',
                    'ip_address' => $ipAddress,
                    'port' => 4370,
                    'connection_type' => AttendanceDevice::CONNECTION_NETWORK,
                    'is_active' => true,
                    'is_online' => true,
                    'last_sync_at' => now(),
                ]);

                Log::info('New ZKTeco device registered', [
                    'serial_number' => $serialNumber,
                    'ip' => $ipAddress,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error registering device', [
                'serial_number' => $serialNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get pending commands for device
     * Commands can be: USER ADD, USER DEL, etc.
     */
    private function getPendingCommands($serialNumber)
    {
        // TODO: Implement command queue system
        // For now, return empty array
        // In the future, you can store commands in database and return them here
        
        // Example commands:
        // return [
        //     "USER ADD PIN=1001\tName=John Doe\tPrivilege=0\tCard=12345678",
        //     "USER DEL PIN=1002",
        // ];
        
        return [];
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
}










