<?php
/**
 * Test New ZKTeco Library Connection
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
echo "ZKTeco Connection Test - Using Library\n";
echo "========================================\n\n";

echo "Device: {$deviceIP}:{$devicePort}\n";
echo "Comm Key: {$commKey}\n\n";

try {
    $zkteco = new ZKTecoServiceNew($deviceIP, $devicePort, $commKey);
    
    echo "Connecting to device...\n";
    $connected = $zkteco->connect();
    
    if ($connected) {
        echo "✓✓✓ CONNECTION SUCCESSFUL! ✓✓✓\n\n";
        
        echo "Getting device information...\n";
        $deviceInfo = $zkteco->getDeviceInfo();
        
        if ($deviceInfo) {
            echo "\nDevice Information:\n";
            foreach ($deviceInfo as $key => $value) {
                if ($value !== null && $value !== '') {
                    echo "  - " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
                }
            }
        }
        
        echo "\nGetting users from device...\n";
        $users = $zkteco->getUsers();
        echo "  Found " . count($users) . " users\n";
        
        echo "\nGetting attendance records...\n";
        $attendance = $zkteco->getAttendance();
        echo "  Found " . count($attendance) . " attendance records\n";
        
        $zkteco->disconnect();
        echo "\n✓ Disconnected successfully\n";
        
        echo "\n========================================\n";
        echo "✓✓✓ ALL TESTS PASSED! ✓✓✓\n";
        echo "========================================\n";
        
        exit(0);
    }
} catch (\Exception $e) {
    echo "\n✗ Connection failed\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Please check:\n";
    echo "1. Comm Key on device (System → Communication → Comm Key)\n";
    echo "2. Device is powered on\n";
    echo "3. Network connection is working\n";
    exit(1);
}









