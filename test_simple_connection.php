<?php
/**
 * Simple Connection Test - Fresh Start
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
echo "SIMPLE CONNECTION TEST - FRESH START\n";
echo "========================================\n\n";

echo "Device: {$deviceIP}:{$devicePort}\n";
echo "Comm Key: {$commKey}\n\n";

try {
    $zkteco = new ZKTecoService($deviceIP, $devicePort, $commKey);
    
    echo "Attempting connection...\n";
    $connected = $zkteco->connect();
    
    if ($connected) {
        echo "\n✓✓✓ CONNECTION SUCCESSFUL! ✓✓✓\n\n";
        
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
        
        $zkteco->disconnect();
        echo "\n✓ Disconnected\n";
        exit(0);
    }
} catch (\Exception $e) {
    echo "\n✗ Connection failed\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    echo "Please check:\n";
    echo "1. Comm Key on device (System → Communication → Comm Key)\n";
    echo "2. Device is powered on and restarted\n";
    echo "3. No other software is connected\n";
    exit(1);
}









