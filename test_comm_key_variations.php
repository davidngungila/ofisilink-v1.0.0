<?php
/**
 * Test Connection with Comm Key Variations
 * Tests various Comm Key values including edge cases
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
echo "COMM KEY VARIATIONS TEST\n";
echo "Device ID: 6\n";
echo "========================================\n\n";

// Extended list including edge cases
$commKeysToTry = [
    0,           // Standard default
    1, 2, 3, 4, 5, 6, 7, 8, 9, 10,  // Single digits
    11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
    100, 200, 300, 400, 500, 600, 700, 800, 900,
    1234, 4321, 12345, 54321,
    1111, 2222, 3333, 4444, 5555, 6666, 7777, 8888, 9999,
    123456, 654321,
    0000, 00000, 000000,  // Multiple zeros (though PHP treats as 0)
];

echo "Testing " . count($commKeysToTry) . " different Comm Key values...\n";
echo "Device ID: {$deviceId}\n\n";

foreach ($commKeysToTry as $commKey) {
    echo "Testing Comm Key: {$commKey} (with Device ID {$deviceId})...\n";
    
    try {
        $zkteco = new ZKTecoService($deviceIP, $devicePort, $commKey, $deviceId);
        $connected = $zkteco->connect(8); // Try all 8 methods
        
        if ($connected) {
            echo "  ✓✓✓ SUCCESS! Connection established! ✓✓✓\n";
            echo "  ✓ Comm Key: {$commKey}\n";
            echo "  ✓ Device ID: {$deviceId}\n\n";
            
            // Verify by getting device info
            try {
                $deviceInfo = $zkteco->getDeviceInfo();
                echo "  ✓ Device info retrieved successfully!\n\n";
                
                echo "========================================\n";
                echo "✓✓✓ CONNECTION SUCCESSFUL! ✓✓✓\n";
                echo "========================================\n\n";
                echo "WORKING CONFIGURATION:\n";
                echo "  Comm Key: {$commKey}\n";
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
                echo "Update config/zkteco.php:\n";
                echo "  'password' => {$commKey},\n";
                echo "  'device_id' => {$deviceId},\n\n";
                
                $zkteco->disconnect();
                exit(0);
            } catch (\Exception $e) {
                echo "  ⚠ Connected but failed to get device info\n";
                echo "  Error: " . substr($e->getMessage(), 0, 60) . "...\n";
                $zkteco->disconnect();
                exit(0); // Still success if connected
            }
        } else {
            echo "  ✗ Failed\n";
        }
    } catch (\Exception $e) {
        $errorMsg = $e->getMessage();
        if (strpos($errorMsg, 'Device closed') !== false) {
            echo "  ✗ Device closed connection\n";
        } else {
            $shortError = substr($errorMsg, 0, 50);
            echo "  ✗ {$shortError}...\n";
        }
    }
}

echo "\n========================================\n";
echo "✗ None of the Comm Keys worked\n";
echo "========================================\n\n";
echo "Tested " . count($commKeysToTry) . " different Comm Key values.\n";
echo "All failed with Device ID {$deviceId}.\n\n";
echo "The device is rejecting all authentication attempts.\n\n";
echo "Please:\n";
echo "1. Check device Comm Key manually on device\n";
echo "2. Check device model and firmware version\n";
echo "3. Restart device completely\n";
echo "4. Close all other ZKTeco software\n\n";

exit(1);









