<?php
/**
 * Test ZKTeco Connection with Device ID 6 and All Passwords
 * 
 * Tests connection with Device ID 6 and various Comm Key values
 * Run: php test_connection_device_id_6_all_passwords.php [device_ip]
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\ZKTecoService;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$deviceIP = $argv[1] ?? '192.168.100.108';
$devicePort = 4370;
$deviceId = 6;

echo "========================================\n";
echo "ZKTeco Connection Test - Device ID 6\n";
echo "Testing All Comm Keys with Device ID 6\n";
echo "========================================\n\n";

echo "Configuration:\n";
echo "  Device IP: {$deviceIP}\n";
echo "  Port: {$devicePort}\n";
echo "  Device ID: {$deviceId}\n\n";

// Extended list of Comm Keys to try
$passwordsToTry = [
    0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10,
    12345, 54321, 123456, 654321, 
    8888, 9999, 1111, 2222, 3333, 4444, 5555, 6666, 7777,
    1234, 4321, 0000, 123, 321
];

echo "Testing connection with Device ID 6 and different Comm Keys...\n";
echo "This will help identify the correct Comm Key.\n\n";

foreach ($passwordsToTry as $password) {
    echo "Testing Comm Key: {$password} (with Device ID 6)...\n";
    
    try {
        // Try with Device ID 6
        $zkteco = new ZKTecoService($deviceIP, $devicePort, $password, $deviceId);
        $connected = $zkteco->connect(4); // Try all 4 methods
        
        if ($connected) {
            echo "  ✓ SUCCESS! Connection established!\n";
            echo "  ✓ Comm Key: {$password}\n";
            echo "  ✓ Device ID: {$deviceId}\n\n";
            
            // Verify by getting device info
            try {
                $deviceInfo = $zkteco->getDeviceInfo();
                echo "  ✓ Device info retrieved successfully!\n\n";
                
                echo "========================================\n";
                echo "✓ CONNECTION SUCCESSFUL!\n";
                echo "========================================\n\n";
                echo "Working Configuration:\n";
                echo "  Comm Key: {$password}\n";
                echo "  Device ID: {$deviceId}\n\n";
                
                echo "Device Information:\n";
                if ($deviceInfo) {
                    foreach ($deviceInfo as $key => $value) {
                        if ($value !== null && $value !== '') {
                            echo "  - " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
                        }
                    }
                }
                
                echo "\n========================================\n";
                echo "Next Steps:\n";
                echo "========================================\n";
                echo "1. Update config/zkteco.php:\n";
                echo "   'password' => {$password},\n";
                echo "   'device_id' => {$deviceId},\n\n";
                echo "2. Or update .env:\n";
                echo "   ZKTECO_PASSWORD={$password}\n";
                echo "   ZKTECO_DEVICE_ID={$deviceId}\n\n";
                
                $zkteco->disconnect();
                exit(0);
            } catch (\Exception $e) {
                echo "  ⚠ Connected but failed to get device info: " . $e->getMessage() . "\n";
                $zkteco->disconnect();
                exit(0); // Still success if connected
            }
        } else {
            echo "  ✗ Failed\n";
        }
    } catch (\Exception $e) {
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, 'Device closed') !== false) {
            echo "  ✗ Device closed connection (wrong Comm Key)\n";
        } else {
            $shortError = substr($errorMsg, 0, 60);
            echo "  ✗ {$shortError}...\n";
        }
    }
    
    echo "\n";
}

echo "========================================\n";
echo "✗ None of the Comm Keys worked\n";
echo "========================================\n\n";
echo "Tried " . count($passwordsToTry) . " different Comm Keys with Device ID 6.\n\n";
echo "The device is rejecting all authentication attempts.\n\n";
echo "Please verify on the device:\n";
echo "1. Comm Key value:\n";
echo "   Device menu → System → Communication → Comm Key\n";
echo "   Write down the EXACT number shown\n\n";
echo "2. Device ID:\n";
echo "   Device menu → System → Communication → Device ID\n";
echo "   Should be: {$deviceId}\n\n";
echo "3. Restart device:\n";
echo "   Power off → wait 15 sec → power on → wait 60 sec\n\n";
echo "4. Check if device requires special protocol:\n";
echo "   - Check device firmware version\n";
echo "   - Check device model number\n";
echo "   - May need different connection method\n\n";

exit(1);









