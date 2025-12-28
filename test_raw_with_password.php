<?php
/**
 * Raw Connection Test WITH Password Data
 */

$deviceIP = '192.168.100.108';
$devicePort = 4370;
$commKey = 0;

echo "========================================\n";
echo "RAW PROTOCOL TEST - WITH PASSWORD DATA\n";
echo "========================================\n\n";

echo "Device: {$deviceIP}:{$devicePort}\n";
echo "Comm Key: {$commKey}\n\n";

// Open socket
echo "[1] Opening socket...\n";
$socket = @fsockopen($deviceIP, $devicePort, $errno, $errstr, 10);

if ($socket === false) {
    echo "✗ Failed: {$errstr} ({$errno})\n";
    exit(1);
}
echo "✓ Socket opened\n\n";

stream_set_timeout($socket, 10);
stream_set_blocking($socket, true);

// Create CONNECT command WITH password data (little-endian)
echo "[2] Creating CONNECT command (WITH password data)...\n";
$command = 1000; // CMD_CONNECT
$sessionId = 0;
$replyId = 0;
$param = 0;

// Build header
$header = pack('vvvv', $command, $sessionId, $replyId, $param);
echo "  Header (hex): " . bin2hex($header) . "\n";

// Password data (4 bytes, little-endian)
$passwordData = pack('V', $commKey);
echo "  Password data (hex): " . bin2hex($passwordData) . "\n";
echo "  Password value: {$commKey}\n";

// Calculate checksum on header + data
$checksum = 0;
for ($i = 0; $i < strlen($header); $i++) {
    $checksum += ord($header[$i]);
}
for ($i = 0; $i < strlen($passwordData); $i++) {
    $checksum += ord($passwordData[$i]);
}
$checksum = $checksum & 0xFFFF;
$checksumBytes = pack('v', $checksum);

echo "  Checksum: " . sprintf('0x%04X', $checksum) . "\n";

// Full command
$fullCommand = $header . $passwordData . $checksumBytes;
echo "  Full command (hex): " . bin2hex($fullCommand) . "\n";
echo "  Full command length: " . strlen($fullCommand) . " bytes\n\n";

// Send command
echo "[3] Sending CONNECT command...\n";
$sent = @fwrite($socket, $fullCommand, strlen($fullCommand));
if ($sent === false || $sent === 0) {
    echo "✗ Failed to send\n";
    fclose($socket);
    exit(1);
}
echo "✓ Sent {$sent} bytes\n\n";

// Wait
usleep(500000); // 0.5 second

// Check socket
echo "[4] Checking socket...\n";
if (feof($socket)) {
    echo "✗ Device closed connection\n";
    fclose($socket);
    exit(1);
}
echo "✓ Socket still open\n\n";

// Read reply
echo "[5] Reading reply...\n";
$read = [$socket];
$write = null;
$except = null;
$changed = @stream_select($read, $write, $except, 5);

if ($changed === false || $changed === 0) {
    echo "⚠ No reply (timeout or error)\n";
    if (feof($socket)) {
        echo "✗ Device closed connection\n";
    }
    fclose($socket);
    exit(1);
}

$reply = @fread($socket, 1024);
if ($reply === false || $reply === '') {
    echo "✗ Failed to read reply\n";
    fclose($socket);
    exit(1);
}

echo "[6] Reply received:\n";
echo "  Length: " . strlen($reply) . " bytes\n";
echo "  Hex: " . bin2hex($reply) . "\n";

if (strlen($reply) >= 8) {
    $header = unpack('vcommand/vsession/vreply/vparam', substr($reply, 0, 8));
    echo "  Command: " . $header['command'] . "\n";
    echo "  Session: " . $header['session'] . "\n";
    
    if ($header['command'] == 2000) {
        echo "\n✓✓✓ SUCCESS! Connection accepted! ✓✓✓\n";
        echo "  Session ID: " . $header['session'] . "\n\n";
        fclose($socket);
        exit(0);
    } elseif ($header['command'] == 2005) {
        echo "\n✗ Authentication failed (UNAUTH)\n";
    } else {
        echo "\n⚠ Command: " . $header['command'] . "\n";
    }
}

fclose($socket);
exit(1);









