<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceDevice;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use PDO;
use PDOException;

class ZKBioTimeSyncService
{
    /**
     * Sync attendance records from ZKBio Time.Net database
     * 
     * @param AttendanceDevice $device The device configuration
     * @param Carbon|null $fromDate Start date for sync (null = last sync time)
     * @param Carbon|null $toDate End date for sync (null = now)
     * @return array Sync results
     */
    public function syncFromZKBioTime(AttendanceDevice $device, $fromDate = null, $toDate = null)
    {
        $results = [
            'success' => false,
            'synced' => 0,
            'skipped' => 0,
            'errors' => 0,
            'error_messages' => []
        ];

        try {
            // Get device settings
            $settings = $device->settings ?? [];
            $connectionConfig = $device->connection_config ?? [];
            
            // Extract ZKBio Time.Net configuration
            $zkbioServerIp = $settings['zkbio_server_ip'] ?? $connectionConfig['zkbio_server_ip'] ?? null;
            $zkbioDbType = $settings['zkbio_db_type'] ?? $connectionConfig['zkbio_db_type'] ?? 'sqlite';
            $zkbioDbPath = $settings['zkbio_db_path'] ?? $connectionConfig['zkbio_db_path'] ?? null;
            $zkbioDbHost = $settings['zkbio_db_host'] ?? $connectionConfig['zkbio_db_host'] ?? null;
            $zkbioDbName = $settings['zkbio_db_name'] ?? $connectionConfig['zkbio_db_name'] ?? ($zkbioDbPath && $zkbioDbType !== 'sqlite' ? $zkbioDbPath : 'attendance');
            $zkbioDbUser = $settings['zkbio_db_user'] ?? $connectionConfig['zkbio_db_user'] ?? null;
            $zkbioDbPassword = $settings['zkbio_db_password'] ?? $connectionConfig['zkbio_db_password'] ?? null;

            // For SQLite, if no path provided, try default locations
            if ($zkbioDbType === 'sqlite' && !$zkbioDbPath) {
                // Try default ZKBio Time.Net paths
                $defaultPaths = [
                    'C:\\ZKTeco\\ZKBioTime\\attendance.db',
                    'C:\\Program Files\\ZKTeco\\ZKBioTime\\attendance.db',
                    'C:\\Program Files (x86)\\ZKTeco\\ZKBioTime\\attendance.db',
                ];
                
                // If server IP is provided, try network path
                if ($zkbioServerIp) {
                    $defaultPaths[] = "\\\\{$zkbioServerIp}\\ZKTeco\\ZKBioTime\\attendance.db";
                }
                
                foreach ($defaultPaths as $path) {
                    if (file_exists($path)) {
                        $zkbioDbPath = $path;
                        break;
                    }
                }
                
                if (!$zkbioDbPath) {
                    throw new \Exception('ZKBio Time.Net database path not configured. Please set the database path in device settings.');
                }
            }
            
            // For MySQL/MSSQL, validate required fields
            if (in_array($zkbioDbType, ['mysql', 'mssql'])) {
                if (!$zkbioDbHost) {
                    throw new \Exception('Database host is required for ' . strtoupper($zkbioDbType) . ' connection. Please configure in device settings.');
                }
                if (!$zkbioDbName) {
                    throw new \Exception('Database name is required for ' . strtoupper($zkbioDbType) . ' connection. Please configure in device settings.');
                }
                if (!$zkbioDbUser) {
                    throw new \Exception('Database username is required for ' . strtoupper($zkbioDbType) . ' connection. Please configure in device settings.');
                }
            }
            
            // Log configuration for debugging
            Log::info('ZKBio Time.Net sync configuration', [
                'device_id' => $device->id,
                'device_name' => $device->name,
                'db_type' => $zkbioDbType,
                'db_host' => $zkbioDbHost,
                'db_name' => $zkbioDbName,
                'db_user' => $zkbioDbUser ? '***' : null,
                'server_ip' => $zkbioServerIp
            ]);

            // Connect to ZKBio Time.Net database
            $pdo = $this->connectToZKBioDatabase($zkbioDbType, $zkbioDbPath, $zkbioDbHost, $zkbioDbName, $zkbioDbUser, $zkbioDbPassword);

            // Determine date range
            if (!$fromDate) {
                $fromDate = $device->last_sync_at ? Carbon::parse($device->last_sync_at) : Carbon::now()->subDays(7);
            }
            if (!$toDate) {
                $toDate = Carbon::now();
            }

            // Get attendance records from ZKBio Time.Net
            $records = $this->fetchAttendanceRecords($pdo, $device, $fromDate, $toDate);

            // Process each record
            foreach ($records as $record) {
                try {
                    $synced = $this->processAttendanceRecord($record, $device);
                    if ($synced) {
                        $results['synced']++;
                    } else {
                        $results['skipped']++;
                    }
                } catch (\Exception $e) {
                    $results['errors']++;
                    $results['error_messages'][] = "Error processing record: " . $e->getMessage();
                    Log::error('ZKBio Time sync error', [
                        'device_id' => $device->id,
                        'record' => $record,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Update device last sync time
            $device->last_sync_at = Carbon::now();
            $device->is_online = true;
            $device->save();

            $results['success'] = true;

        } catch (\Exception $e) {
            $results['error_messages'][] = $e->getMessage();
            Log::error('ZKBio Time sync failed', [
                'device_id' => $device->id,
                'error' => $e->getMessage()
            ]);
        }

        return $results;
    }

    /**
     * Connect to ZKBio Time.Net database
     */
    private function connectToZKBioDatabase($dbType, $dbPath, $dbHost, $dbName, $dbUser, $dbPassword)
    {
        try {
            if ($dbType === 'sqlite') {
                // SQLite connection
                if (!$dbPath) {
                    // Default path for ZKBio Time.Net
                    $dbPath = '\\\\' . $dbHost . '\\ZKTeco\\ZKBioTime\\attendance.db';
                    // Or local path if on same machine
                    if (!file_exists($dbPath)) {
                        $dbPath = 'C:\\ZKTeco\\ZKBioTime\\attendance.db';
                    }
                }
                
                if (!file_exists($dbPath)) {
                    throw new \Exception("ZKBio Time.Net database not found at: {$dbPath}");
                }

                $pdo = new PDO("sqlite:{$dbPath}");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
            } elseif ($dbType === 'mysql') {
                // MySQL connection
                if (!$dbHost || !$dbName) {
                    throw new \Exception('MySQL connection requires host and database name');
                }
                
                $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
                
                try {
                    // Handle empty password (pass null instead of empty string)
                    $password = ($dbPassword && trim($dbPassword) !== '') ? $dbPassword : null;
                    $pdo = new PDO($dsn, $dbUser, $password, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 5,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]);
                } catch (PDOException $e) {
                    $errorMsg = $e->getMessage();
                    if (strpos($errorMsg, 'not allowed to connect') !== false) {
                        $hostname = gethostname();
                        throw new \Exception("MySQL connection denied. Please ensure:\n" .
                            "1. MySQL user '{$dbUser}' has access from host '{$hostname}'\n" .
                            "2. MySQL server allows remote connections\n" .
                            "3. Firewall allows MySQL port (3306)\n" .
                            "4. User has proper privileges on database '{$dbName}'\n" .
                            "\n🔧 TO FIX: Run this on MySQL server ({$dbHost}):\n" .
                            "   mysql -u root\n" .
                            "   GRANT ALL PRIVILEGES ON {$dbName}.* TO '{$dbUser}'@'%';\n" .
                            "   FLUSH PRIVILEGES;\n" .
                            "\nOr use the fix script: php artisan mysql:fix-access");
                    }
                    throw new \Exception("MySQL connection failed: {$errorMsg}");
                }
                
            } elseif ($dbType === 'mssql') {
                // MS SQL Server connection
                $dsn = "sqlsrv:Server={$dbHost};Database={$dbName}";
                $pdo = new PDO($dsn, $dbUser, $dbPassword);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                throw new \Exception("Unsupported database type: {$dbType}");
            }

            return $pdo;

        } catch (PDOException $e) {
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch attendance records from ZKBio Time.Net database
     */
    private function fetchAttendanceRecords($pdo, AttendanceDevice $device, Carbon $fromDate, Carbon $toDate)
    {
        try {
            // ZKBio Time.Net table structure (common structure)
            // Table name: CHECKINOUT or CHECKINOUTS (varies by version)
            // Common columns: USERID, CHECKTIME, CHECKTYPE, VERIFYCODE, SENSORID
            
            $tableName = 'CHECKINOUT'; // Try default table name
            
            // Check if table exists, try alternative names
            $tables = $this->getTableNames($pdo);
            if (!in_array('CHECKINOUT', $tables)) {
                if (in_array('CHECKINOUTS', $tables)) {
                    $tableName = 'CHECKINOUTS';
                } else {
                    throw new \Exception('ZKBio Time.Net attendance table not found');
                }
            }

            // Build query based on device IP
            $deviceIp = $device->ip_address;
            $fromDateStr = $fromDate->format('Y-m-d H:i:s');
            $toDateStr = $toDate->format('Y-m-d H:i:s');

            // Query attendance records
            // Note: SENSORID might contain device IP or device ID
            $sql = "SELECT USERID, CHECKTIME, CHECKTYPE, VERIFYCODE, SENSORID 
                    FROM {$tableName} 
                    WHERE CHECKTIME >= ? AND CHECKTIME <= ?
                    ORDER BY CHECKTIME ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([$fromDateStr, $toDateStr]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Filter by device if SENSORID contains device info
            // This is optional - you may want to sync all records
            $filteredRecords = [];
            foreach ($records as $record) {
                // If SENSORID matches device IP or device ID, include it
                // Otherwise, include all records (comment out filter if needed)
                if (empty($deviceIp) || 
                    strpos($record['SENSORID'] ?? '', $deviceIp) !== false ||
                    $record['SENSORID'] == $device->device_id) {
                    $filteredRecords[] = $record;
                }
            }

            return $filteredRecords;

        } catch (\Exception $e) {
            Log::error('Error fetching ZKBio Time records', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get table names from database
     */
    private function getTableNames($pdo)
    {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        
        if ($driver === 'sqlite') {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
        } elseif ($driver === 'mysql') {
            $stmt = $pdo->query("SHOW TABLES");
        } elseif ($driver === 'sqlsrv') {
            $stmt = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE'");
        } else {
            return [];
        }
        
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        
        return $tables;
    }

    /**
     * Process a single attendance record
     */
    private function processAttendanceRecord($record, AttendanceDevice $device)
    {
        try {
            // Extract data from ZKBio Time.Net record
            $employeeId = $record['USERID'] ?? null;
            $checkTime = $record['CHECKTIME'] ?? null;
            $checkType = $record['CHECKTYPE'] ?? null; // Usually 'I' for In, 'O' for Out
            $verifyCode = $record['VERIFYCODE'] ?? null; // Verification method code

            if (!$employeeId || !$checkTime) {
                return false;
            }

            // Parse check time
            $checkDateTime = Carbon::parse($checkTime);
            $attendanceDate = $checkDateTime->format('Y-m-d');
            $time = $checkDateTime->format('H:i:s');

            // Find user by employee ID
            // ZKBio Time.Net USERID should match your system's employee_id or employee_number
            $user = User::where('employee_id', $employeeId)
                       ->orWhereHas('employee', function($q) use ($employeeId) {
                           $q->where('employee_number', $employeeId);
                       })
                       ->first();

            if (!$user) {
                Log::warning('User not found for ZKBio Time record', [
                    'employee_id' => $employeeId,
                    'check_time' => $checkTime
                ]);
                return false;
            }

            // Determine if this is time in or time out
            // CHECKTYPE: 'I' = In, 'O' = Out, or use time logic
            $isTimeIn = ($checkType === 'I' || $checkType === '0' || $checkType === 0);
            
            // If CHECKTYPE is not clear, use logic: first record of day = In, second = Out
            if ($checkType === null || ($checkType !== 'I' && $checkType !== 'O')) {
                $existingAttendance = Attendance::where('user_id', $user->id)
                                               ->where('attendance_date', $attendanceDate)
                                               ->first();
                
                if ($existingAttendance && $existingAttendance->time_in) {
                    $isTimeIn = false; // Has time in, so this must be time out
                } else {
                    $isTimeIn = true; // No time in yet, so this is time in
                }
            }

            // Get or create attendance record
            $attendance = Attendance::where('user_id', $user->id)
                                   ->where('attendance_date', $attendanceDate)
                                   ->first();

            if (!$attendance) {
                $attendance = new Attendance();
                $attendance->user_id = $user->id;
                $attendance->employee_id = $user->employee?->id;
                $attendance->attendance_date = $attendanceDate;
                $attendance->status = Attendance::STATUS_PRESENT;
            }

            // Set time in or time out
            if ($isTimeIn) {
                if (!$attendance->time_in || $time < $attendance->time_in) {
                    $attendance->time_in = $time;
                    $attendance->is_late = $attendance->checkLate();
                }
            } else {
                if (!$attendance->time_out || $time > $attendance->time_out) {
                    $attendance->time_out = $time;
                    if ($attendance->time_in) {
                        $attendance->total_hours = $attendance->calculateTotalHours();
                    }
                }
            }

            // Set device information
            $attendance->attendance_method = Attendance::METHOD_BIOMETRIC;
            $attendance->attendance_device_id = $device->id;
            $attendance->device_id = $device->device_id;
            $attendance->device_type = $device->device_type;
            $attendance->verification_status = Attendance::VERIFICATION_VERIFIED;
            
            // Store metadata
            $attendance->metadata = array_merge($attendance->metadata ?? [], [
                'zkbio_verify_code' => $verifyCode,
                'zkbio_check_type' => $checkType,
                'zkbio_sensor_id' => $record['SENSORID'] ?? null,
                'synced_at' => Carbon::now()->toDateTimeString()
            ]);

            // Set location if device has location
            if ($device->location_id) {
                $attendance->location_id = $device->location_id;
            }

            $attendance->save();

            return true;

        } catch (\Exception $e) {
            Log::error('Error processing attendance record', [
                'record' => $record,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Sync all active devices
     */
    public function syncAllDevices()
    {
        $devices = AttendanceDevice::where('is_active', true)
                                  ->where('device_type', 'biometric')
                                  ->get();

        $totalSynced = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        foreach ($devices as $device) {
            try {
                $results = $this->syncFromZKBioTime($device);
                $totalSynced += $results['synced'];
                $totalSkipped += $results['skipped'];
                $totalErrors += $results['errors'];
            } catch (\Exception $e) {
                $totalErrors++;
                Log::error('Error syncing device', [
                    'device_id' => $device->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'synced' => $totalSynced,
            'skipped' => $totalSkipped,
            'errors' => $totalErrors
        ];
    }
}

