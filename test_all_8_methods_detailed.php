<?php
/**
 * Detailed Test - All 8 Connection Methods
 * Shows which method is being tried and detailed results
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
echo "DETAILED TEST - ALL 8 CONNECTION METHODS\n";
echo "========================================\n\n";

echo "Configuration:\n";
echo "  Device IP: {$deviceIP}\n";
echo "  Port: {$devicePort}\n";
echo "  Comm Key: {$commKey}\n";
echo "  Device ID: {$deviceId}\n\n";

$methods = [
    ['name' => 'Without Device ID', 'deviceId' => null],
    ['name' => 'With Device ID 6', 'deviceId' => 6],
];

foreach ($methods as $method) {
    echo "========================================\n";
    echo "Testing: {$method['name']}\n";
    echo "========================================\n\n";
    
    try {
        $zkteco = new ZKTecoService($deviceIP, $devicePort, $commKey, $method['deviceId']);
        
        echo "Attempting 8 connection methods...\n\n";
        
        for ($attempt = 1; $attempt <= 8; $attempt++) {
            echo "[Method {$attempt}/8] ";
            
            $methodNames = [
                1 => 'No password data, no Device ID in param',
                2 => 'Minimal command (header only)',
                3 => 'Password data (little-endian), no Device ID in param',
                4 => 'Password data (big-endian), no Device ID in param',
                5 => 'No password data, WITH Device ID in param',
                6 => 'Password data (little-endian), WITH Device ID in param',
                7 => 'Password data + Device ID in data field',
                8 => 'Alternative with Reply ID = 0',
            ];
            
            echo $methodNames[$attempt] . "...\n";
            
            try {
                // Create a new instance for each attempt to reset state
                $zkteco = new ZKTecoService($deviceIP, $devicePort, $commKey, $method['deviceId']);
                
                // Try only this specific method
                $connected = false;
                $lastException = null;
                
                // Open socket
                $errno = 0;
                $errstr = '';
                $socket = @fsockopen($deviceIP, $devicePort, $errno, $errstr, 10);
                
                if ($socket === false) {
                    echo "  ✗ Failed to open socket\n\n";
                    continue;
                }
                
                stream_set_timeout($socket, 10);
                stream_set_blocking($socket, true);
                
                // Create command based on attempt number
                $command = '';
                $passwordData = '';
                
                if ($attempt === 1) {
                    $passwordData = '';
                    $command = $zkteco->createCommand(1000, $passwordData, '', false);
                } elseif ($attempt === 2) {
                    $command = $zkteco->createMinimalConnectCommand(false);
                } elseif ($attempt === 3) {
                    $passwordData = pack('V', $commKey);
                    $command = $zkteco->createCommand(1000, $passwordData, '', false);
                } elseif ($attempt === 4) {
                    $passwordData = pack('N', $commKey);
                    $command = $zkteco->createCommand(1000, $passwordData, '', false);
                } elseif ($attempt === 5) {
                    $passwordData = '';
                    $command = $zkteco->createCommand(1000, $passwordData, '', true);
                } elseif ($attempt === 6) {
                    $passwordData = pack('V', $commKey);
                    $command = $zkteco->createCommand(1000, $passwordData, '', true);
                } elseif ($attempt === 7) {
                    if ($method['deviceId'] !== null && $method['deviceId'] > 0) {
                        $passwordData = pack('V', $commKey);
                        $passwordData .= pack('C', $method['deviceId']);
                        $command = $zkteco->createCommand(1000, $passwordData, '', false);
                    } else {
                        echo "  ⚠ Skipped (no Device ID)\n\n";
                        fclose($socket);
                        continue;
                    }
                } else {
                    $zkteco->replyId = 0;
                    $passwordData = pack('V', $commKey);
                    $command = $zkteco->createCommand(1000, $passwordData, '', true);
                }
                
                // Send command
                $sent = @fwrite($socket, $command, strlen($command));
                if ($sent === false) {
                    echo "  ✗ Failed to send command\n\n";
                    fclose($socket);
                    continue;
                }
                
                usleep(200000); // 0.2 second
                
                // Check if connection closed
                if (feof($socket)) {
                    echo "  ✗ Device closed connection\n\n";
                    fclose($socket);
                    continue;
                }
                
                // Try to read reply
                $read = [$socket];
                $write = null;
                $except = null;
                $changed = @stream_select($read, $write, $except, 5);
                
                if ($changed > 0) {
                    $reply = @fread($socket, 1024);
                    if ($reply && strlen($reply) >= 8) {
                        // Check if valid reply
                        $header = unpack('vcommand/vsession/vreply/vparam', substr($reply, 0, 8));
                        if ($header['command'] == 2000 || $header['command'] == 2002) {
                            echo "  ✓ CONNECTION SUCCESSFUL!\n";
                            echo "  ✓ Method {$attempt} worked!\n\n";
                            fclose($socket);
                            
                            // Now test with full service
                            $zkteco = new ZKTecoService($deviceIP, $devicePort, $commKey, $method['deviceId']);
                            if ($zkteco->connect(8)) {
                                $deviceInfo = $zkteco->getDeviceInfo();
                                echo "  ✓ Device info retrieved:\n";
                                if ($deviceInfo) {
                                    foreach ($deviceInfo as $key => $value) {
                                        if ($value !== null && $value !== '') {
                                            echo "    - " . ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
                                        }
                                    }
                                }
                                $zkteco->disconnect();
                            }
                            
                            echo "\n========================================\n";
                            echo "✓ SUCCESS! Method {$attempt} worked!\n";
                            echo "========================================\n\n";
                            exit(0);
                        } else {
                            echo "  ✗ Invalid reply (command: {$header['command']})\n\n";
                        }
                    } else {
                        echo "  ✗ No valid reply received\n\n";
                    }
                } else {
                    echo "  ✗ Timeout waiting for reply\n\n";
                }
                
                fclose($socket);
            } catch (\Exception $e) {
                echo "  ✗ Error: " . substr($e->getMessage(), 0, 80) . "...\n\n";
            }
        }
        
        echo "All 8 methods failed for {$method['name']}\n\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n\n";
    }
}

echo "========================================\n";
echo "✗ ALL METHODS FAILED\n";
echo "========================================\n\n";
echo "All 8 connection methods failed.\n";
echo "The device is rejecting all authentication attempts.\n\n";
echo "Please verify on the device:\n";
echo "1. Comm Key is exactly 0\n";
echo "2. Device ID is 6\n";
echo "3. Device is restarted\n";
echo "4. No other software is connected\n\n";

exit(1);









