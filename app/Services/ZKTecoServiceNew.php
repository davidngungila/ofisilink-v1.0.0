<?php

namespace App\Services;

use ZKLib\ZKLib;
use ZKLib\User;
use Illuminate\Support\Facades\Log;
use Exception;

class ZKTecoServiceNew
{
    private $ip;
    private $port;
    private $password;
    private $zk;

    public function __construct($ip, $port = 4370, $password = 0)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->password = $password;
        $this->zk = new ZKLib($ip, $port);
    }

    /**
     * Connect to ZKTeco device
     */
    public function connect()
    {
        try {
            Log::info('Attempting ZKTeco connection', [
                'ip' => $this->ip,
                'port' => $this->port,
                'password' => $this->password
            ]);

            // Connect to device
            $connected = $this->zk->connect();
            
            if ($connected) {
                // Set password if provided
                if ($this->password > 0) {
                    $this->zk->setPassword($this->password);
                }
                
                Log::info('ZKTeco connected successfully', [
                    'ip' => $this->ip,
                    'port' => $this->port
                ]);
                
                return true;
            }
            
            throw new Exception("Failed to connect to device at {$this->ip}:{$this->port}");
            
        } catch (Exception $e) {
            Log::error('ZKTeco connection failed', [
                'ip' => $this->ip,
                'port' => $this->port,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Disconnect from device
     */
    public function disconnect()
    {
        try {
            if ($this->zk) {
                $this->zk->disconnect();
                Log::info('ZKTeco disconnected', ['ip' => $this->ip]);
            }
        } catch (Exception $e) {
            Log::warning('Error during disconnect', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Get device information
     */
    public function getDeviceInfo()
    {
        try {
            if (!$this->zk) {
                $this->connect();
            }

            $version = $this->zk->getVersion();
            $serial = $this->zk->getSerialNumber();
            
            return [
                'ip' => $this->ip,
                'port' => $this->port,
                'firmware_version' => $version,
                'serial_number' => $serial,
                'device_name' => 'ZKTeco Device',
                'model' => 'ZKTeco'
            ];
        } catch (Exception $e) {
            Log::error('Get device info error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get all users from device
     */
    public function getUsers()
    {
        try {
            if (!$this->zk) {
                $this->connect();
            }

            $users = $this->zk->getUser();
            
            // Convert to array format for compatibility
            $formatted = [];
            foreach ($users as $user) {
                try {
                    // Sanitize name to handle encoding issues
                    $name = $user->getName();
                    if ($name !== null && $name !== '') {
                        // Convert to string if not already
                        $name = (string)$name;
                        
                        // Remove invalid UTF-8 characters using iconv
                        $name = @iconv('UTF-8', 'UTF-8//IGNORE', $name);
                        
                        // If iconv fails, try mb_convert_encoding
                        if ($name === false) {
                            $name = mb_convert_encoding($user->getName(), 'UTF-8', 'UTF-8');
                            $name = @iconv('UTF-8', 'UTF-8//IGNORE', $name);
                        }
                        
                        // Final cleanup - remove null bytes and control characters (except newlines and tabs)
                        $name = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/u', '', $name);
                        
                        // Ensure it's valid UTF-8, if not try ISO-8859-1 conversion
                        if (!mb_check_encoding($name, 'UTF-8')) {
                            $name = mb_convert_encoding($name, 'UTF-8', 'ISO-8859-1');
                            // Remove invalid chars again after conversion
                            $name = @iconv('UTF-8', 'UTF-8//IGNORE', $name);
                        }
                        
                        // Trim and ensure it's not empty
                        $name = trim($name);
                        if (empty($name)) {
                            $name = 'User_' . ($user->getRecordId() ?? '');
                        }
                    } else {
                        $name = 'User_' . ($user->getRecordId() ?? '');
                    }
                    
                    $formatted[] = [
                        'uid' => $user->getRecordId(),
                        'name' => $name,
                        'password' => $user->getPassword(),
                        'card' => $user->getCardNo(),
                        'role' => $user->getRole(),
                    ];
                } catch (Exception $e) {
                    // If individual user processing fails, log and continue
                    Log::warning('Error processing user from device', [
                        'uid' => $user->getRecordId() ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    // Add user with safe defaults
                    try {
                        $formatted[] = [
                            'uid' => $user->getRecordId() ?? 0,
                            'name' => 'User_' . ($user->getRecordId() ?? 'Unknown'),
                            'password' => $user->getPassword() ?? '',
                            'card' => $user->getCardNo() ?? 0,
                            'role' => $user->getRole() ?? 0,
                        ];
                    } catch (Exception $e2) {
                        // Skip this user if we can't even get basic info
                        Log::error('Skipping user due to processing error', [
                            'error' => $e2->getMessage()
                        ]);
                    }
                }
            }
            
            return $formatted;
        } catch (Exception $e) {
            Log::error('Get users error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Enable device (required before registration)
     */
    public function enableDevice()
    {
        try {
            if (!$this->zk) {
                $this->connect();
            }
            
            $result = $this->zk->enable();
            Log::info('Device enabled', ['ip' => $this->ip, 'result' => $result]);
            return $result;
        } catch (Exception $e) {
            Log::error('Enable device error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Disable device
     */
    public function disableDevice()
    {
        try {
            if (!$this->zk) {
                $this->connect();
            }
            
            $result = $this->zk->disable();
            Log::info('Device disabled', ['ip' => $this->ip, 'result' => $result]);
            return $result;
        } catch (Exception $e) {
            Log::error('Disable device error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Unregister/Delete user from device
     */
    public function unregisterUser($uid)
    {
        try {
            if (!$this->zk) {
                $this->connect();
            }

            // Ensure device is enabled before deletion
            $this->enableDevice();

            Log::info('Unregistering user from device', [
                'ip' => $this->ip,
                'port' => $this->port,
                'uid' => $uid
            ]);

            // Delete user from device
            $result = $this->zk->deleteUser($uid);
            
            Log::info('deleteUser command executed', [
                'uid' => $uid,
                'result' => $result,
                'result_type' => gettype($result)
            ]);

            // Wait for device to process
            usleep(1000000); // 1 second

            // Verify user is deleted from device
            $users = $this->zk->getUser();
            $userExists = false;
            
            foreach ($users as $deviceUser) {
                if ($deviceUser->getRecordId() == $uid || $deviceUser->getUserId() == $uid) {
                    $userExists = true;
                    break;
                }
            }
            
            if ($userExists) {
                Log::warning('User still exists on device after deletion attempt', ['uid' => $uid]);
                return false;
            }
            
            Log::info('✓ User successfully unregistered from device', ['uid' => $uid]);
            return true;
            
        } catch (Exception $e) {
            Log::error('Unregister user error', [
                'uid' => $uid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Register user to device - Force registration with multiple methods
     */
    public function registerUser($uid, $name, $password = '', $role = 0, $card = 0)
    {
        Log::info('Starting user registration process', [
            'uid' => $uid,
            'uid_type' => gettype($uid),
            'name' => $name,
            'name_length' => strlen($name),
            'role' => $role,
            'card' => $card,
            'device_ip' => $this->ip,
            'device_port' => $this->port
        ]);

        // Ensure device is connected and enabled before starting
        if (!$this->zk) {
            $this->connect();
        }
        
        // Check connection status
        if (!$this->isConnected()) {
            Log::error('Device not connected, attempting reconnect', [
                'ip' => $this->ip,
                'port' => $this->port
            ]);
            $this->connect();
        }

        // Ensure device is enabled - critical for registration
        try {
            $enableResult = $this->enableDevice();
            Log::info('Device enabled for registration', [
                'enable_result' => $enableResult,
                'ip' => $this->ip
            ]);
        } catch (Exception $e) {
            Log::error('Failed to enable device', [
                'error' => $e->getMessage(),
                'ip' => $this->ip
            ]);
            // Continue anyway - some devices might not need explicit enable
        }

        $errors = [];

        // Try Method 1: Delete first, then register
        try {
            Log::info('Method 1: Delete first, then register', ['uid' => $uid]);
            $result = $this->registerUserMethod1($uid, $name, $password, $role, $card);
            if ($result) {
                Log::info('Method 1 succeeded', ['uid' => $uid]);
                return true;
            } else {
                $errors[] = 'Method 1: Registration returned false';
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $errors[] = 'Method 1: ' . $errorMsg;
            Log::warning('Method 1 failed', [
                'uid' => $uid,
                'error' => $errorMsg,
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Try Method 2: Direct register without delete
        try {
            Log::info('Method 2: Direct register', ['uid' => $uid]);
            $result = $this->registerUserMethod2($uid, $name, $password, $role, $card);
            if ($result) {
                Log::info('Method 2 succeeded', ['uid' => $uid]);
                return true;
            } else {
                $errors[] = 'Method 2: Registration returned false';
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $errors[] = 'Method 2: ' . $errorMsg;
            Log::warning('Method 2 failed', [
                'uid' => $uid,
                'error' => $errorMsg,
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Try Method 3: Register with different parameters
        try {
            Log::info('Method 3: Register with alternative parameters', ['uid' => $uid]);
            $result = $this->registerUserMethod3($uid, $name, $password, $role, $card);
            if ($result) {
                Log::info('Method 3 succeeded', ['uid' => $uid]);
                return true;
            } else {
                $errors[] = 'Method 3: Registration returned false';
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            $errors[] = 'Method 3: ' . $errorMsg;
            Log::warning('Method 3 failed', [
                'uid' => $uid,
                'error' => $errorMsg,
                'trace' => $e->getTraceAsString()
            ]);
        }

        // All methods failed - log detailed error
        Log::error('All registration methods failed', [
            'uid' => $uid,
            'name' => $name,
            'errors' => $errors,
            'device_ip' => $this->ip,
            'device_port' => $this->port
        ]);
        
        return false;
    }

    /**
     * Method 1: Delete first, then register fresh
     */
    private function registerUserMethod1($uid, $name, $password = '', $role = 0, $card = 0)
    {
        if (!$this->zk) {
            $this->connect();
        }

        $this->enableDevice();

        // Prepare user data
        if (empty($name)) {
            $name = 'User_' . $uid;
        }
        $name = trim($name);
        $name = substr($name, 0, 8);
        $password = str_pad((string)$password, 5, "\0");
        $card = (int)$card;

        // Delete user if exists
        try {
            $existingUsers = $this->zk->getUser();
            foreach ($existingUsers as $existingUser) {
                if ($existingUser->getRecordId() == $uid || $existingUser->getUserId() == $uid) {
                    Log::info('Deleting existing user', ['uid' => $uid]);
                    $this->zk->deleteUser($uid);
                    usleep(1500000); // 1.5 seconds
                    break;
                }
            }
        } catch (Exception $e) {
            Log::info('No existing user to delete', ['uid' => $uid]);
        }

        // Register user
        // ZKLib User constructor: User($uid, $role, $password, $name, $card, $group, $timezone, $userid)
        // Original format that was working: User($uid, $role, $password, $name, $card, 0, 0, $uid)
        $user = new \ZKLib\User($uid, $role, $password, $name, $card, 0, 0, $uid);
        
        Log::info('Method 1: Calling setUser on device', [
            'uid' => $uid,
            'name' => $name,
            'role' => $role,
            'card' => $card,
            'user_object' => [
                'recordId' => $user->getRecordId(),
                'userId' => $user->getUserId(),
                'name' => $user->getName()
            ]
        ]);
        
        // Try setUser multiple times if needed
        $maxAttempts = 3;
        $setUserSuccess = false;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
        $result = $this->zk->setUser($user);
        
                Log::info('Method 1: setUser attempt ' . $attempt, [
                    'uid' => $uid,
                    'result' => $result,
                    'result_type' => gettype($result),
                    'result_bool' => (bool)$result
                ]);
                
                // Even if setUser returns false, the user might still be registered
                // We'll verify by checking the device
                $setUserSuccess = true;
                break;
            } catch (Exception $e) {
                Log::warning('Method 1: setUser attempt ' . $attempt . ' failed', [
                    'uid' => $uid,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt < $maxAttempts) {
                    usleep(1000000); // Wait 1 second before retry
                }
            }
        }
        
        // Wait longer for device to process registration
        usleep(3000000); // 3 seconds
        
        // Always verify by checking device, don't rely on setUser return value
        return $this->verifyUserOnDevice($uid, $name);
    }

    /**
     * Method 2: Direct register without delete
     */
    private function registerUserMethod2($uid, $name, $password = '', $role = 0, $card = 0)
    {
        if (!$this->zk) {
            $this->connect();
        }

        $this->enableDevice();

        if (empty($name)) {
            $name = 'User_' . $uid;
        }
        $name = trim($name);
        $name = substr($name, 0, 8);
        $password = str_pad((string)$password, 5, "\0");
        $card = (int)$card;

        // Try registering directly (may overwrite existing)
        // ZKLib User constructor: User($uid, $role, $password, $name, $card, $group, $timezone, $userid)
        $user = new \ZKLib\User($uid, $role, $password, $name, $card, 0, 0, $uid);
        
        Log::info('Method 2: Calling setUser on device (direct register)', [
            'uid' => $uid,
            'name' => $name
        ]);
        
        // Try setUser multiple times if needed
        $maxAttempts = 3;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
        $result = $this->zk->setUser($user);
        
                Log::info('Method 2: setUser attempt ' . $attempt, [
                    'uid' => $uid,
                    'result' => $result,
                    'result_type' => gettype($result)
                ]);
                
                break; // Exit loop if no exception
            } catch (Exception $e) {
                Log::warning('Method 2: setUser attempt ' . $attempt . ' failed', [
                    'uid' => $uid,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt < $maxAttempts) {
                    usleep(1000000); // Wait 1 second before retry
                }
            }
        }
        
        // Wait longer for device to process
        usleep(3000000); // 3 seconds
        
        // Always verify by checking device
        return $this->verifyUserOnDevice($uid, $name);
    }

    /**
     * Method 3: Register with minimal data
     */
    private function registerUserMethod3($uid, $name, $password = '', $role = 0, $card = 0)
    {
        if (!$this->zk) {
            $this->connect();
        }

        $this->enableDevice();

        // Use minimal name
        $name = substr(trim($name ?: 'User_' . $uid), 0, 8);
        $password = '';
        $card = 0;
        $role = 0;

        // Try with minimal parameters
        // ZKLib User constructor: User($uid, $role, $password, $name, $card, $group, $timezone, $userid)
        $user = new \ZKLib\User($uid, $role, $password, $name, $card, 0, 0, $uid);
        
        Log::info('Method 3: Calling setUser on device (minimal params)', [
            'uid' => $uid,
            'name' => $name
        ]);
        
        // Try setUser multiple times if needed
        $maxAttempts = 3;
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
        $result = $this->zk->setUser($user);
        
                Log::info('Method 3: setUser attempt ' . $attempt, [
                    'uid' => $uid,
                    'result' => $result,
                    'result_type' => gettype($result)
                ]);
                
                break; // Exit loop if no exception
            } catch (Exception $e) {
                Log::warning('Method 3: setUser attempt ' . $attempt . ' failed', [
                    'uid' => $uid,
                    'error' => $e->getMessage()
                ]);
                
                if ($attempt < $maxAttempts) {
                    usleep(1000000); // Wait 1 second before retry
                }
            }
        }
        
        // Wait even longer for device to process
        usleep(4000000); // 4 seconds
        
        // Always verify by checking device
        return $this->verifyUserOnDevice($uid, $name);
    }

    /**
     * Verify user exists on device
     */
    private function verifyUserOnDevice($uid, $name)
    {
        $maxRetries = 8; // Increased retries for slower devices
        
        for ($retry = 1; $retry <= $maxRetries; $retry++) {
            try {
                $users = $this->zk->getUser();
                
                Log::info('Verifying user on device', [
                    'uid' => $uid,
                    'uid_type' => gettype($uid),
                    'attempt' => $retry . '/' . $maxRetries,
                    'total_users' => count($users)
                ]);
                
                $allDeviceUids = [];
                foreach ($users as $deviceUser) {
                    $deviceUid = $deviceUser->getRecordId();
                    $deviceUserId = $deviceUser->getUserId();
                    $allDeviceUids[] = [
                        'recordId' => $deviceUid,
                        'recordId_type' => gettype($deviceUid),
                        'userId' => $deviceUserId,
                        'userId_type' => gettype($deviceUserId),
                        'name' => $deviceUser->getName()
                    ];
                    
                    // Compare both as integers and as strings to handle type mismatches
                    if ($deviceUid == $uid || 
                        $deviceUserId == $uid ||
                        (int)$deviceUid == (int)$uid ||
                        (int)$deviceUserId == (int)$uid ||
                        (string)$deviceUid === (string)$uid ||
                        (string)$deviceUserId === (string)$uid) {
                        Log::info('✓ User verified on device', [
                            'uid' => $uid,
                            'device_recordId' => $deviceUid,
                            'device_userId' => $deviceUserId,
                            'device_name' => $deviceUser->getName(),
                            'match_type' => 'found',
                            'attempt' => $retry
                        ]);
                        return true;
                    }
                }
                
                // Log all device UIDs for debugging on last attempt
                if ($retry == $maxRetries) {
                    Log::warning('User not found - all device UIDs', [
                        'expected_uid' => $uid,
                        'expected_uid_type' => gettype($uid),
                        'device_uids' => $allDeviceUids,
                        'total_device_users' => count($users)
                    ]);
                }
                
                if ($retry < $maxRetries) {
                    usleep(1500000); // 1.5 seconds between retries (increased for slower devices)
                }
            } catch (Exception $e) {
                Log::warning('Error during verification attempt', [
                    'attempt' => $retry,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                if ($retry < $maxRetries) {
                    usleep(1500000);
                }
            }
        }
        
        Log::warning('User not found on device after all verification attempts', [
            'uid' => $uid,
            'uid_type' => gettype($uid),
            'name' => $name,
            'total_attempts' => $maxRetries
        ]);
        
        return false;
    }

    /**
     * Get attendance records
     */
    public function getAttendance()
    {
        try {
            if (!$this->zk) {
                $this->connect();
            }

            Log::info('Calling ZKLib getAttendance()', [
                'ip' => $this->ip,
                'port' => $this->port
            ]);

            $attendance = $this->zk->getAttendance();
            
            if ($attendance === null || !is_array($attendance)) {
                Log::warning('ZKLib getAttendance() returned invalid data', [
                    'ip' => $this->ip,
                    'type' => gettype($attendance),
                    'value' => $attendance
                ]);
                return [];
            }
            
            Log::info('Received attendance records from ZKLib', [
                'ip' => $this->ip,
                'count' => count($attendance)
            ]);
            
            // Convert to expected format
            $formatted = [];
            foreach ($attendance as $att) {
                try {
                $formatted[] = [
                    'uid' => $att->getUserId(),
                    'timestamp' => $att->getDateTime()->format('Y-m-d H:i:s'),
                    'status' => $att->getStatus(),
                    'verify' => $att->getType(),
                ];
                } catch (Exception $e) {
                    Log::warning('Error formatting attendance record', [
                        'error' => $e->getMessage(),
                        'ip' => $this->ip
                    ]);
                    // Continue with next record
                }
            }
            
            Log::info('Formatted attendance records', [
                'ip' => $this->ip,
                'formatted_count' => count($formatted)
            ]);
            
            return $formatted;
        } catch (Exception $e) {
            Log::error('Get attendance error', [
                'error' => $e->getMessage(),
                'ip' => $this->ip,
                'port' => $this->port,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get attendances (alias for getAttendance for compatibility)
     */
    public function getAttendances()
    {
        return $this->getAttendance();
    }
    
    /**
     * Check if connected
     */
    public function isConnected()
    {
        return $this->zk !== null;
    }
    
    /**
     * Get fingerprint count for user
     */
    public function getFingerprintCount($uid)
    {
        try {
            if (!$this->zk) {
                $this->connect();
            }

            // Get user templates - library doesn't expose fingerprint count directly
            // Return 1 if user exists (fingerprint may be enrolled)
            $users = $this->zk->getUser();
            foreach ($users as $user) {
                if ($user->getRecordId() == $uid) {
                    return 1; // User exists, assume fingerprint may be enrolled
                }
            }
            
            return 0;
        } catch (Exception $e) {
            Log::error('Get fingerprint count error', [
                'uid' => $uid,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Clear attendance records
     */
    public function clearAttendance()
    {
        try {
            if (!$this->zk) {
                $this->connect();
            }

            return $this->zk->clearAttendance();
        } catch (Exception $e) {
            Log::error('Clear attendance error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

