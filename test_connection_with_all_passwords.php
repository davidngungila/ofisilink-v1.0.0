<?php
/**
 * Test ZKTeco Connection with Multiple Passwords
 * 
 * This script tests connection with common password values to find the correct Comm Key
 * Run: php test_connection_with_all_passwords.php [device_ip]
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\ZKTecoService;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$deviceIP = $argv[1] ?? '192.168.100.108';
$devicePort = 4370;

echo "========================================\n";
echo "ZKTeco Connection Test - All Passwords\n";
echo "Server IP: 192.168.100.103\n";
echo "Device IP: {$deviceIP}\n";
echo "========================================\n\n";

// Common Comm Key values to try
$passwordsToTry = [0, 1, 12345, 54321, 123456, 654321, 8888, 9999];

echo "Testing connection with different Communication Keys...\n";
echo "This will help identify the correct Comm Key on your device.\n\n";

foreach ($passwordsToTry as $password) {
    echo "Testing with Comm Key: {$password}...\n";
    
    try {
        $zkteco = new ZKTecoService($deviceIP, $devicePort, $password, null);
        $connected = $zkteco->connect(4); // Try all 4 methods
        
        if ($connected) {
            echo "  ✓ SUCCESS! Connection established with Comm Key: {$password}\n";
            
            // Verify by getting device info
            try {
                $deviceInfo = $zkteco->getDeviceInfo();
                echo "  ✓ Device info retrieved successfully!\n\n";
                
                echo "========================================\n";
                echo "✓ FOUND CORRECT COMM KEY!\n";
                echo "========================================\n\n";
                echo "Correct Communication Key: {$password}\n\n";
                echo "Device Information:\n";
                if ($deviceInfo) {
                    echo "  IP: " . ($deviceInfo['ip'] ?? 'N/A') . "\n";
                    echo "  Port: " . ($deviceInfo['port'] ?? 'N/A') . "\n";
                    echo "  Model: " . ($deviceInfo['model'] ?? 'N/A') . "\n";
                    echo "  Name: " . ($deviceInfo['device_name'] ?? 'N/A') . "\n";
                    if (isset($deviceInfo['firmware_version'])) {
                        echo "  Firmware: " . $deviceInfo['firmware_version'] . "\n";
                    }
                }
                
                echo "\n========================================\n";
                echo "Next Steps:\n";
                echo "========================================\n";
                echo "1. Update your .env file:\n";
                echo "   ZKTECO_PASSWORD={$password}\n\n";
                echo "2. Or use password {$password} when testing connection\n\n";
                
                $zkteco->disconnect();
                exit(0);
            } catch (\Exception $e) {
                echo "  ⚠ Connected but failed to get device info\n";
                $zkteco->disconnect();
            }
        } else {
            echo "  ✗ Failed\n";
        }
    } catch (\Exception $e) {
        $errorMsg = $e->getMessage();
        // Show short error
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
echo "✗ None of the common Comm Keys worked\n";
echo "========================================\n\n";
echo "Tried Comm Keys: " . implode(', ', $passwordsToTry) . "\n\n";
echo "Please:\n";
echo "1. Check device Comm Key manually:\n";
echo "   Device menu → System → Communication → Comm Key\n";
echo "2. Note the exact number shown\n";
echo "3. Test with that number:\n";
echo "   php test_device_connection_only.php {$deviceIP} 4370 [YOUR_COMM_KEY]\n\n";

exit(1);









