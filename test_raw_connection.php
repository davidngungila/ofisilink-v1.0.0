<?php
/**
 * Raw Connection Test - Debug Protocol Level
 * Tests the actual bytes being sent and received
 */

require __DIR__ . '/vendor/autoload.php';

$deviceIP = '192.168.100.108';
$devicePort = 4370;
$commKey = 0;

echo "========================================\n";
echo "RAW PROTOCOL CONNECTION TEST\n";
echo "========================================\n\n";

echo "Device: {$deviceIP}:{$devicePort}\n";
echo "Comm Key: {$commKey}\n\n";

// Open socket
echo "[1] Opening socket...\n";
$socket = @fsockopen($deviceIP, $devicePort, $errno, $errstr, 10);

if ($socket === false) {
    echo "✗ Failed to open socket: {$errstr} ({$errno})\n";
    exit(1);
}

echo "✓ Socket opened\n\n";

// Set timeout
stream_set_timeout($socket, 10);
stream_set_blocking($socket, true);

// Create CONNECT command - Method 1: No password data
echo "[2] Creating CONNECT command (no password data)...\n";
$command = 1000; // CMD_CONNECT
$sessionId = 0;
$replyId = 0;
$param = 0;

// Build header: command (2 bytes), session (2 bytes), reply (2 bytes), param (2 bytes)
$header = pack('vvvv', $command, $sessionId, $replyId, $param);
echo "  Header (hex): " . bin2hex($header) . "\n";
echo "  Header length: " . strlen($header) . " bytes\n";

// No password data
$data = '';

// Calculate checksum
$checksum = 0;
for ($i = 0; $i < strlen($header); $i++) {
    $checksum += ord($header[$i]);
}
$checksum = $checksum & 0xFFFF; // Mask to 16 bits
$checksumBytes = pack('v', $checksum);

echo "  Checksum: " . sprintf('0x%04X', $checksum) . "\n";
echo "  Checksum (hex): " . bin2hex($checksumBytes) . "\n";

// Full command
$fullCommand = $header . $data . $checksumBytes;
echo "  Full command (hex): " . bin2hex($fullCommand) . "\n";
echo "  Full command length: " . strlen($fullCommand) . " bytes\n\n";

// Send command
echo "[3] Sending CONNECT command...\n";
$sent = @fwrite($socket, $fullCommand, strlen($fullCommand));
if ($sent === false || $sent === 0) {
    echo "✗ Failed to send command\n";
    fclose($socket);
    exit(1);
}
echo "✓ Sent {$sent} bytes\n\n";

// Wait a bit
usleep(500000); // 0.5 second

// Check if connection closed
echo "[4] Checking socket status...\n";
if (feof($socket)) {
    echo "✗ Device closed connection immediately\n";
    echo "  This means device rejected the connection\n\n";
    fclose($socket);
    exit(1);
}
echo "✓ Socket still open\n\n";

// Try to read reply
echo "[5] Reading reply...\n";
$read = [$socket];
$write = null;
$except = null;
$changed = @stream_select($read, $write, $except, 5);

if ($changed === false) {
    echo "✗ Error in stream_select\n";
    fclose($socket);
    exit(1);
}

if ($changed === 0) {
    echo "⚠ No data available (timeout)\n";
    if (feof($socket)) {
        echo "✗ Device closed connection (timeout)\n";
    }
    fclose($socket);
    exit(1);
}

echo "✓ Data available\n\n";

// Read reply
$reply = @fread($socket, 1024);
if ($reply === false || $reply === '') {
    echo "✗ Failed to read reply\n";
    if (feof($socket)) {
        echo "  Device closed connection\n";
    }
    fclose($socket);
    exit(1);
}

echo "[6] Reply received:\n";
echo "  Reply length: " . strlen($reply) . " bytes\n";
echo "  Reply (hex): " . bin2hex($reply) . "\n";

if (strlen($reply) >= 8) {
    $header = unpack('vcommand/vsession/vreply/vparam', substr($reply, 0, 8));
    echo "  Command: " . $header['command'] . "\n";
    echo "  Session: " . $header['session'] . "\n";
    echo "  Reply ID: " . $header['reply'] . "\n";
    echo "  Param: " . $header['param'] . "\n";
    
    // Check if it's ACK_OK (2000)
    if ($header['command'] == 2000) {
        echo "\n✓✓✓ SUCCESS! Device accepted connection! ✓✓✓\n";
        echo "  Session ID: " . $header['session'] . "\n\n";
        fclose($socket);
        exit(0);
    } elseif ($header['command'] == 2005) {
        echo "\n✗ Authentication failed (CMD_ACK_UNAUTH)\n";
        echo "  Wrong Comm Key!\n\n";
    } elseif ($header['command'] == 2001) {
        echo "\n✗ Error response (CMD_ACK_ERROR)\n";
    } else {
        echo "\n⚠ Unexpected command: " . $header['command'] . "\n";
    }
} else {
    echo "\n✗ Reply too short (expected at least 8 bytes)\n";
}

fclose($socket);
exit(1);









