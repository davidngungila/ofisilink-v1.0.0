<?php
/**
 * Test with Device ID 6 in param field
 */

$deviceIP = '192.168.100.108';
$devicePort = 4370;
$commKey = 0;
$deviceId = 6;

echo "========================================\n";
echo "TEST WITH DEVICE ID 6 IN PARAM FIELD\n";
echo "========================================\n\n";

echo "Device: {$deviceIP}:{$devicePort}\n";
echo "Comm Key: {$commKey}\n";
echo "Device ID: {$deviceId}\n\n";

// Open socket
$socket = @fsockopen($deviceIP, $devicePort, $errno, $errstr, 10);
if ($socket === false) {
    echo "✗ Failed to open socket\n";
    exit(1);
}
echo "✓ Socket opened\n\n";

stream_set_timeout($socket, 10);
stream_set_blocking($socket, true);

// Method 1: No password, Device ID in param
echo "[Method 1] No password, Device ID 6 in param...\n";
$command = 1000;
$sessionId = 0;
$replyId = 0;
$param = $deviceId; // Device ID in param

$header = pack('vvvv', $command, $sessionId, $replyId, $param);
$data = '';
$checksum = 0;
for ($i = 0; $i < strlen($header); $i++) {
    $checksum += ord($header[$i]);
}
$checksum = $checksum & 0xFFFF;
$fullCommand = $header . $data . pack('v', $checksum);

echo "  Command (hex): " . bin2hex($fullCommand) . "\n";
@fwrite($socket, $fullCommand, strlen($fullCommand));
usleep(500000);

if (feof($socket)) {
    echo "  ✗ Device closed connection\n\n";
    fclose($socket);
    
    // Try Method 2
    $socket = @fsockopen($deviceIP, $devicePort, $errno, $errstr, 10);
    if ($socket) {
        stream_set_timeout($socket, 10);
        stream_set_blocking($socket, true);
        
        echo "[Method 2] Password data, Device ID 6 in param...\n";
        $header = pack('vvvv', 1000, 0, 0, $deviceId);
        $passwordData = pack('V', $commKey);
        $checksum = 0;
        for ($i = 0; $i < strlen($header); $i++) {
            $checksum += ord($header[$i]);
        }
        for ($i = 0; $i < strlen($passwordData); $i++) {
            $checksum += ord($passwordData[$i]);
        }
        $checksum = $checksum & 0xFFFF;
        $fullCommand = $header . $passwordData . pack('v', $checksum);
        
        echo "  Command (hex): " . bin2hex($fullCommand) . "\n";
        @fwrite($socket, $fullCommand, strlen($fullCommand));
        usleep(500000);
        
        if (feof($socket)) {
            echo "  ✗ Device closed connection\n\n";
        } else {
            $read = [$socket];
            $changed = @stream_select($read, null, null, 2);
            if ($changed > 0) {
                $reply = @fread($socket, 1024);
                if ($reply && strlen($reply) >= 8) {
                    $h = unpack('vcommand/vsession', substr($reply, 0, 8));
                    if ($h['command'] == 2000) {
                        echo "  ✓✓✓ SUCCESS! Connection accepted! ✓✓✓\n";
                        echo "  Session ID: " . $h['session'] . "\n\n";
                        fclose($socket);
                        exit(0);
                    }
                }
            }
        }
    }
    fclose($socket);
    exit(1);
} else {
    $read = [$socket];
    $changed = @stream_select($read, null, null, 2);
    if ($changed > 0) {
        $reply = @fread($socket, 1024);
        if ($reply && strlen($reply) >= 8) {
            $h = unpack('vcommand/vsession', substr($reply, 0, 8));
            if ($h['command'] == 2000) {
                echo "  ✓✓✓ SUCCESS! Connection accepted! ✓✓✓\n";
                echo "  Session ID: " . $h['session'] . "\n\n";
                fclose($socket);
                exit(0);
            }
        }
    }
}

echo "\n========================================\n";
echo "✗ ALL METHODS FAILED\n";
echo "========================================\n\n";
echo "The device is closing the connection immediately.\n";
echo "This means:\n";
echo "1. Comm Key is WRONG (not 0)\n";
echo "2. Device requires different protocol\n";
echo "3. Device needs restart\n";
echo "4. Another application is connected\n\n";
echo "Please check Comm Key on device:\n";
echo "  System → Communication → Comm Key\n";

fclose($socket);
exit(1);









