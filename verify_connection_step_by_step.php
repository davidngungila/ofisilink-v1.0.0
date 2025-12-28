<?php
/**
 * Step-by-Step Connection Verification
 * Following the successful documentation pattern exactly
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\ZKTecoService;

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$deviceIP = $argv[1] ?? '192.168.100.108';
$devicePort = 4370;
$commKey = 0;

echo "========================================\n";
echo "STEP-BY-STEP CONNECTION VERIFICATION\n";
echo "Following Documentation Pattern\n";
echo "========================================\n\n";

echo "Step 1: Configuration Check\n";
echo "----------------------------\n";
echo "Device IP: {$deviceIP}\n";
echo "Port: {$devicePort}\n";
echo "Comm Key: {$commKey}\n";
echo "Device ID: NOT USED (per documentation)\n\n";

echo "Step 2: Network Test\n";
echo "----------------------------\n";
$pingCommand = "ping -n 1 -w 1000 {$deviceIP}";
@exec($pingCommand, $pingOutput, $pingResult);
if ($pingResult === 0) {
    echo "✓ Device is reachable\n\n";
} else {
    echo "✗ Device not reachable\n\n";
    exit(1);
}

echo "Step 3: Port Test\n";
echo "----------------------------\n";
$testSocket = @fsockopen($deviceIP, $devicePort, $errno, $errstr, 5);
if ($testSocket) {
    echo "✓ Port {$devicePort} is open\n";
    fclose($testSocket);
} else {
    echo "✗ Port {$devicePort} is NOT accessible\n";
    echo "  Error: {$errstr} (Code: {$errno})\n\n";
    exit(1);
}
echo "\n";

echo "Step 4: Connection Test (Documentation Method)\n";
echo "----------------------------\n";
echo "Testing with Comm Key = 0, NO Device ID (as per documentation)...\n\n";

try {
    // Create service WITHOUT Device ID (documentation doesn't use it for connection)
    $zkteco = new ZKTecoService($deviceIP, $devicePort, $commKey, null);
    
    echo "Attempting connection...\n";
    $connected = $zkteco->connect(8);
    
    if ($connected) {
        echo "✓✓✓ CONNECTION SUCCESSFUL! ✓✓✓\n\n";
        
        echo "Step 5: Device Info Verification\n";
        echo "----------------------------\n";
        try {
            $deviceInfo = $zkteco->getDeviceInfo();
            if ($deviceInfo) {
                echo "✓ Device information retrieved:\n";
                foreach ($deviceInfo as $key => $value) {
                    if ($value !== null && $value !== '') {
                        echo "  - " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
                    }
                }
            } else {
                echo "⚠ Device info not available\n";
            }
        } catch (\Exception $e) {
            echo "⚠ Could not get device info: " . substr($e->getMessage(), 0, 60) . "...\n";
        }
        
        echo "\n";
        echo "Step 6: Disconnect\n";
        echo "----------------------------\n";
        $zkteco->disconnect();
        echo "✓ Disconnected successfully\n\n";
        
        echo "========================================\n";
        echo "✓✓✓ ALL STEPS COMPLETED SUCCESSFULLY! ✓✓✓\n";
        echo "========================================\n\n";
        echo "Connection is working as per documentation!\n";
        echo "Configuration:\n";
        echo "  Comm Key: {$commKey}\n";
        echo "  Device ID: NOT USED (connection works without it)\n\n";
        
        exit(0);
    } else {
        echo "✗ Connection failed\n\n";
    }
} catch (\Exception $e) {
    echo "✗ Connection error:\n";
    echo "  " . substr($e->getMessage(), 0, 200) . "...\n\n";
}

echo "========================================\n";
echo "✗ CONNECTION FAILED\n";
echo "========================================\n\n";
echo "The connection failed even with the exact documentation pattern.\n\n";
echo "This suggests:\n";
echo "1. Comm Key on device is NOT 0\n";
echo "2. Device requires different protocol\n";
echo "3. Device needs to be restarted\n";
echo "4. Another application is connected\n\n";
echo "Please verify on device:\n";
echo "- System → Communication → Comm Key (must be exactly 0)\n";
echo "- Restart device completely\n";
echo "- Close all other ZKTeco software\n\n";

exit(1);









