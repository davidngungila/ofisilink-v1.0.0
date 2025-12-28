<?php
/**
 * Test Attendance Module Connection
 * Simulates the web interface connection test
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\ZKTecoServiceNew;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$deviceIP = $argv[1] ?? '192.168.100.108';
$devicePort = 4370;
$commKey = 0;

echo "========================================\n";
echo "ATTENDANCE MODULE CONNECTION TEST\n";
echo "========================================\n\n";

echo "Testing connection as used in /modules/hr/attendance...\n\n";
echo "Device: {$deviceIP}:{$devicePort}\n";
echo "Comm Key: {$commKey}\n\n";

try {
    $zkteco = new ZKTecoServiceNew($deviceIP, $devicePort, $commKey);
    
    echo "Connecting...\n";
    $connected = $zkteco->connect();
    
    if ($connected) {
        echo "✓ Connection successful!\n\n";
        
        echo "Getting device info...\n";
        $deviceInfo = $zkteco->getDeviceInfo();
        
        if ($deviceInfo) {
            echo "✓ Device info retrieved:\n";
            echo "  - Firmware: " . ($deviceInfo['firmware_version'] ?? 'N/A') . "\n";
            echo "  - Serial: " . ($deviceInfo['serial_number'] ?? 'N/A') . "\n";
        }
        
        echo "\nGetting users...\n";
        $users = $zkteco->getUsers();
        echo "✓ Found " . count($users) . " users\n";
        
        $zkteco->disconnect();
        
        echo "\n========================================\n";
        echo "✓✓✓ ALL TESTS PASSED! ✓✓✓\n";
        echo "========================================\n\n";
        echo "The attendance module connection is working!\n";
        echo "You can now use it at: /modules/hr/attendance-settings\n\n";
        
        exit(0);
    }
} catch (\Exception $e) {
    echo "\n✗ Connection failed\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    exit(1);
}









