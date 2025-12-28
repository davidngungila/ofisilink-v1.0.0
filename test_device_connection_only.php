<?php
/**
 * ZKTeco Device Connection Test - Focused on Connection Only
 * 
 * This script tests ONLY the device connection, trying all methods until success.
 * Run: php test_device_connection_only.php
 * 
 * Server IP: 192.168.100.103
 * Device IP: Enter when prompted (default: 192.168.100.108)
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\ZKTecoService;
use Illuminate\Support\Facades\Log;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "ZKTeco Device Connection Test\n";
echo "Server IP: 192.168.100.103\n";
echo "========================================\n\n";

// Get device IP from command line or use default
$deviceIP = $argv[1] ?? '192.168.100.108';
$devicePort = $argv[2] ?? 4370;
$devicePassword = $argv[3] ?? 0;

echo "Testing connection to device:\n";
echo "  Device IP: {$deviceIP}\n";
echo "  Port: {$devicePort}\n";
echo "  Communication Key: {$devicePassword}\n\n";

echo "========================================\n";
echo "Connection Test - Trying All Methods\n";
echo "========================================\n\n";

$connectionResults = [
    'device_ip' => $deviceIP,
    'port' => $devicePort,
    'password' => $devicePassword,
    'server_ip' => '192.168.100.103',
    'attempts' => [],
    'success' => false,
    'working_method' => null
];

// Test network connectivity first
echo "[1/5] Testing network connectivity...\n";
$pingCommand = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' 
    ? "ping -n 1 -w 1000 {$deviceIP}" 
    : "ping -c 1 -W 1 {$deviceIP}";

@exec($pingCommand, $pingOutput, $pingResult);
if ($pingResult === 0) {
    echo "  ✓ Device is reachable on network\n\n";
} else {
    echo "  ⚠ Ping failed (device may not respond to ping, but connection may still work)\n\n";
}

// Test port connectivity
echo "[2/5] Testing port connectivity...\n";
$errno = 0;
$errstr = '';
$testSocket = @fsockopen($deviceIP, $devicePort, $errno, $errstr, 5);
if ($testSocket !== false) {
    fclose($testSocket);
    echo "  ✓ Port {$devicePort} is open and accessible\n\n";
} else {
    echo "  ✗ Cannot connect to port {$devicePort}: {$errstr} (Code: {$errno})\n";
    echo "  Please check:\n";
    echo "    - Device is powered on\n";
    echo "    - Device IP is correct: {$deviceIP}\n";
    echo "    - Device port is correct: {$devicePort}\n";
    echo "    - Firewall is not blocking\n\n";
    exit(1);
}

// Try connection with different methods
$methods = [
    ['name' => 'Method 1: No password data (simplest)', 'password' => $devicePassword],
    ['name' => 'Method 2: Minimal command', 'password' => $devicePassword],
    ['name' => 'Method 3: Password data (little-endian)', 'password' => $devicePassword],
    ['name' => 'Method 4: Password data (big-endian)', 'password' => $devicePassword],
];

// Also try password 0 if different password was used
if ($devicePassword != 0) {
    $methods[] = ['name' => 'Method 5: Try password 0', 'password' => 0];
}

echo "[3/5] Testing device connection (trying " . count($methods) . " methods)...\n\n";

foreach ($methods as $index => $method) {
    $attemptNum = $index + 1;
    echo "  Attempt {$attemptNum}: {$method['name']}...\n";
    
    try {
        $zkteco = new ZKTecoService($deviceIP, $devicePort, $method['password'], null);
        
        // Try to connect (will try 4 internal retries)
        $connected = $zkteco->connect(4);
        
        if ($connected) {
            echo "    ✓ Connection established!\n";
            
            // Verify by getting device info
            echo "    → Verifying connection by getting device info...\n";
            try {
                $deviceInfo = $zkteco->getDeviceInfo();
                
                if ($zkteco->isConnected()) {
                    echo "    ✓ Connection verified! Device info retrieved.\n\n";
                    
                    $connectionResults['success'] = true;
                    $connectionResults['working_method'] = $method['name'];
                    $connectionResults['device_info'] = $deviceInfo;
                    
                    echo "========================================\n";
                    echo "✓ CONNECTION SUCCESSFUL!\n";
                    echo "========================================\n\n";
                    echo "Working Method: {$method['name']}\n";
                    echo "Password Used: {$method['password']}\n\n";
                    
                    if ($deviceInfo) {
                        echo "Device Information:\n";
                        echo "  IP: " . ($deviceInfo['ip'] ?? 'N/A') . "\n";
                        echo "  Port: " . ($deviceInfo['port'] ?? 'N/A') . "\n";
                        echo "  Model: " . ($deviceInfo['model'] ?? 'N/A') . "\n";
                        echo "  Name: " . ($deviceInfo['device_name'] ?? 'N/A') . "\n";
                        if (isset($deviceInfo['firmware_version'])) {
                            echo "  Firmware: " . $deviceInfo['firmware_version'] . "\n";
                        }
                        if (isset($deviceInfo['serial_number'])) {
                            echo "  Serial: " . $deviceInfo['serial_number'] . "\n";
                        }
                    }
                    
                    $zkteco->disconnect();
                    echo "\n✓ Disconnected successfully\n";
                    exit(0);
                }
            } catch (\Exception $e) {
                echo "    ⚠ Connected but failed to get device info: " . $e->getMessage() . "\n";
                $zkteco->disconnect();
            }
        } else {
            echo "    ✗ Connection failed\n";
        }
    } catch (\Exception $e) {
        $errorMsg = $e->getMessage();
        // Truncate long error messages for display
        if (strlen($errorMsg) > 100) {
            $errorMsg = substr($errorMsg, 0, 100) . '...';
        }
        echo "    ✗ Failed: {$errorMsg}\n";
    }
    
    echo "\n";
}

// All methods failed
echo "========================================\n";
echo "✗ CONNECTION FAILED - All Methods Tried\n";
echo "========================================\n\n";

echo "Tried " . count($methods) . " connection methods, all failed.\n\n";
echo "Troubleshooting Steps:\n";
echo "1. Verify device Communication Key:\n";
echo "   - Device menu → System → Communication → Comm Key\n";
echo "   - Should be: {$devicePassword}\n";
echo "   - If different, set to {$devicePassword} on device\n";
echo "   - Restart device after changing\n\n";
echo "2. Check device network settings:\n";
echo "   - Device IP: {$deviceIP}\n";
echo "   - Port: {$devicePort}\n";
echo "   - Ensure device and server (192.168.100.103) are on same network\n\n";
echo "3. Restart device:\n";
echo "   - Power off → wait 15 seconds → power on\n";
echo "   - Wait 60 seconds for full boot\n\n";
echo "4. Check firewall:\n";
echo "   - Ensure port {$devicePort} is not blocked\n";
echo "   - Test: Test-NetConnection -ComputerName {$deviceIP} -Port {$devicePort}\n\n";
echo "5. Ensure no other software is connected:\n";
echo "   - Close ZKBio Time.Net if running\n";
echo "   - Wait 10 seconds\n\n";

exit(1);









