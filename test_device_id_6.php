<?php
/**
 * Test ZKTeco Connection with Device ID 6 and Comm Key 0
 * 
 * This script tests connection specifically for:
 * - Device ID: 6
 * - Comm Key: 0
 * - No password
 * 
 * Run: php test_device_id_6.php [device_ip]
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
echo "ZKTeco Connection Test\n";
echo "Device ID: 6, Comm Key: 0\n";
echo "Server IP: 192.168.100.103\n";
echo "========================================\n\n";

echo "Device Configuration:\n";
echo "  Device IP: {$deviceIP}\n";
echo "  Port: {$devicePort}\n";
echo "  Communication Key: {$commKey}\n";
echo "  Device ID: {$deviceId}\n";
echo "  Password: None\n\n";

echo "========================================\n";
echo "Testing Connection Methods\n";
echo "========================================\n\n";

// Test 1: Without Device ID (standard)
echo "[Test 1] Connection WITHOUT Device ID...\n";
try {
    $zkteco1 = new ZKTecoService($deviceIP, $devicePort, $commKey, null);
    $connected1 = $zkteco1->connect(4);
    
    if ($connected1) {
        echo "  ✓ SUCCESS! Connected without Device ID\n";
        $deviceInfo = $zkteco1->getDeviceInfo();
        echo "  ✓ Device info retrieved\n\n";
        echo "========================================\n";
        echo "✓ CONNECTION SUCCESSFUL!\n";
        echo "========================================\n\n";
        echo "Working Method: Without Device ID\n";
        echo "Comm Key: {$commKey}\n\n";
        if ($deviceInfo) {
            echo "Device Information:\n";
            echo "  IP: " . ($deviceInfo['ip'] ?? 'N/A') . "\n";
            echo "  Model: " . ($deviceInfo['model'] ?? 'N/A') . "\n";
            echo "  Name: " . ($deviceInfo['device_name'] ?? 'N/A') . "\n";
        }
        $zkteco1->disconnect();
        exit(0);
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: " . substr($e->getMessage(), 0, 80) . "...\n\n";
}

// Test 2: With Device ID 6
echo "[Test 2] Connection WITH Device ID 6...\n";
try {
    $zkteco2 = new ZKTecoService($deviceIP, $devicePort, $commKey, $deviceId);
    $connected2 = $zkteco2->connect(4);
    
    if ($connected2) {
        echo "  ✓ SUCCESS! Connected with Device ID 6\n";
        $deviceInfo = $zkteco2->getDeviceInfo();
        echo "  ✓ Device info retrieved\n\n";
        echo "========================================\n";
        echo "✓ CONNECTION SUCCESSFUL!\n";
        echo "========================================\n\n";
        echo "Working Method: With Device ID 6\n";
        echo "Comm Key: {$commKey}\n";
        echo "Device ID: {$deviceId}\n\n";
        if ($deviceInfo) {
            echo "Device Information:\n";
            echo "  IP: " . ($deviceInfo['ip'] ?? 'N/A') . "\n";
            echo "  Model: " . ($deviceInfo['model'] ?? 'N/A') . "\n";
            echo "  Name: " . ($deviceInfo['device_name'] ?? 'N/A') . "\n";
        }
        $zkteco2->disconnect();
        exit(0);
    }
} catch (\Exception $e) {
    echo "  ✗ Failed: " . substr($e->getMessage(), 0, 80) . "...\n\n";
}

// All tests failed
echo "========================================\n";
echo "✗ CONNECTION FAILED\n";
echo "========================================\n\n";
echo "Tried both with and without Device ID 6, both failed.\n\n";
echo "Troubleshooting:\n";
echo "1. Verify on device:\n";
echo "   - Comm Key is exactly 0\n";
echo "   - Device ID is 6 (if required)\n";
echo "   - Device menu → System → Communication\n\n";
echo "2. Restart device:\n";
echo "   - Power off → wait 15 sec → power on\n";
echo "   - Wait 60 seconds after restart\n\n";
echo "3. Check device settings:\n";
echo "   - Ensure no other software is connected\n";
echo "   - Check device firmware version\n\n";

exit(1);









