<?php
/**
 * Comprehensive ZKTeco Connection Test
 * 
 * Tests all connection methods with Device ID 6 and Comm Key 0
 * Provides detailed diagnostics
 * 
 * Run: php test_connection_comprehensive.php [device_ip]
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\ZKTecoService;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$deviceIP = $argv[1] ?? '192.168.100.108';
$devicePort = 4370;
$commKey = 0;
$deviceId = 6;

echo "========================================\n";
echo "COMPREHENSIVE ZKTeco CONNECTION TEST\n";
echo "========================================\n\n";

echo "Configuration:\n";
echo "  Device IP: {$deviceIP}\n";
echo "  Port: {$devicePort}\n";
echo "  Comm Key: {$commKey}\n";
echo "  Device ID: {$deviceId}\n";
echo "  Server IP: 192.168.100.103\n\n";

// Step 1: Network connectivity
echo "========================================\n";
echo "STEP 1: Network Connectivity Test\n";
echo "========================================\n\n";

$pingCommand = "ping -n 1 -w 1000 {$deviceIP}";
@exec($pingCommand, $pingOutput, $pingResult);
if ($pingResult === 0) {
    echo "✓ Device is reachable on network\n";
    echo "  Response: " . implode("\n  ", array_slice($pingOutput, 0, 2)) . "\n\n";
} else {
    echo "⚠ Ping test failed\n";
    echo "  Note: Some devices don't respond to ping, but connection may still work\n\n";
}

// Step 2: Port connectivity
echo "========================================\n";
echo "STEP 2: Port Connectivity Test\n";
echo "========================================\n\n";

$testSocket = @fsockopen($deviceIP, $devicePort, $errno, $errstr, 5);
if ($testSocket) {
    echo "✓ Port {$devicePort} is open and accessible\n";
    fclose($testSocket);
} else {
    echo "✗ Port {$devicePort} is NOT accessible\n";
    echo "  Error: {$errstr} (Code: {$errno})\n";
    echo "  This means the device is not accepting connections on port {$devicePort}\n\n";
    echo "Troubleshooting:\n";
    echo "  1. Check device is powered on\n";
    echo "  2. Verify device IP is correct: {$deviceIP}\n";
    echo "  3. Check firewall is not blocking port {$devicePort}\n";
    echo "  4. Verify device network settings\n\n";
    exit(1);
}
echo "\n";

// Step 3: Connection attempts
echo "========================================\n";
echo "STEP 3: Connection Attempts\n";
echo "========================================\n\n";

$methods = [
    ['name' => 'Without Device ID', 'deviceId' => null],
    ['name' => 'With Device ID 6', 'deviceId' => 6],
];

$success = false;
$workingMethod = null;

foreach ($methods as $method) {
    echo "--- Testing: {$method['name']} ---\n";
    
    try {
        $zkteco = new ZKTecoService($deviceIP, $devicePort, $commKey, $method['deviceId']);
        
        echo "  Attempting connection (8 methods with verification)...\n";
        $connected = $zkteco->connect(8);
        
        if ($connected) {
            echo "  ✓ CONNECTION SUCCESSFUL!\n\n";
            
            // Try to get device info
            echo "  Retrieving device information...\n";
            $deviceInfo = $zkteco->getDeviceInfo();
            
            if ($deviceInfo) {
                echo "  ✓ Device information retrieved:\n";
                foreach ($deviceInfo as $key => $value) {
                    if ($value !== null && $value !== '') {
                        echo "    - " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
                    }
                }
            } else {
                echo "  ⚠ Device info not available, but connection was established\n";
            }
            
            $zkteco->disconnect();
            echo "  ✓ Disconnected successfully\n\n";
            
            $success = true;
            $workingMethod = $method['name'];
            break;
        }
    } catch (\Exception $e) {
        $errorMsg = $e->getMessage();
        echo "  ✗ Connection failed\n";
        echo "  Error: " . substr($errorMsg, 0, 150) . "...\n\n";
    }
    
    echo "\n";
}

// Final result
echo "========================================\n";
if ($success) {
    echo "✓ CONNECTION SUCCESSFUL!\n";
    echo "========================================\n\n";
    echo "Working Method: {$workingMethod}\n";
    echo "Comm Key: {$commKey}\n";
    if ($workingMethod === 'With Device ID 6') {
        echo "Device ID: {$deviceId}\n";
    }
    echo "\n";
    echo "Your device is now connected and ready to use!\n";
    exit(0);
} else {
    echo "✗ CONNECTION FAILED\n";
    echo "========================================\n\n";
    echo "All connection methods failed.\n\n";
    echo "Troubleshooting Steps:\n\n";
    echo "1. VERIFY COMM KEY ON DEVICE:\n";
    echo "   - Device menu → System → Communication → Comm Key\n";
    echo "   - Must be exactly: {$commKey}\n";
    echo "   - If different, either:\n";
    echo "     a) Change device Comm Key to {$commKey}\n";
    echo "     b) Use the actual Comm Key from device in connection\n\n";
    echo "2. VERIFY DEVICE ID:\n";
    echo "   - Device menu → System → Communication → Device ID\n";
    echo "   - Should be: {$deviceId}\n";
    echo "   - If not visible, Device ID may not be required\n\n";
    echo "3. RESTART DEVICE:\n";
    echo "   - Power off → wait 15 seconds → power on\n";
    echo "   - Wait 60 seconds after restart\n";
    echo "   - Try connection again\n\n";
    echo "4. CHECK FOR OTHER CONNECTIONS:\n";
    echo "   - Close ZKBio Time.Net if running\n";
    echo "   - Close any other ZKTeco software\n";
    echo "   - Wait 10 seconds\n";
    echo "   - Try connection again\n\n";
    echo "5. TEST WITH DIFFERENT COMM KEY:\n";
    echo "   Run: php test_connection_with_all_passwords.php {$deviceIP}\n";
    echo "   This will test common Comm Key values\n\n";
    exit(1);
}

