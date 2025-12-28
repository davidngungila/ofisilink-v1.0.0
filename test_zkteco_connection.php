<?php
/**
 * Test ZKTeco Connection Script
 * 
 * This script tests the ZKTeco device connection functionality
 * Run: php test_zkteco_connection.php
 */

require __DIR__ . '/vendor/autoload.php';

use App\Services\ZKTecoService;

echo "=== ZKTeco Connection Test ===\n\n";

// Test configuration
$testIP = '192.168.1.201'; // Change to your device IP
$testPort = 4370;
$testPassword = 0;

echo "Testing connection to: {$testIP}:{$testPort}\n";
echo "Password/Comm Key: {$testPassword}\n\n";

try {
    // Create service instance
    $zkteco = new ZKTecoService($testIP, $testPort, $testPassword);
    
    echo "Attempting to connect...\n";
    
    // Test connection
    $connected = $zkteco->connect();
    
    if ($connected) {
        echo "✓ Connection successful!\n\n";
        
        // Get device info
        echo "Retrieving device information...\n";
        $deviceInfo = $zkteco->getDeviceInfo();
        
        if ($deviceInfo) {
            echo "✓ Device information retrieved:\n";
            echo "  - IP: " . ($deviceInfo['ip'] ?? 'N/A') . "\n";
            echo "  - Port: " . ($deviceInfo['port'] ?? 'N/A') . "\n";
            echo "  - Device Name: " . ($deviceInfo['device_name'] ?? 'N/A') . "\n";
            echo "  - Model: " . ($deviceInfo['model'] ?? 'N/A') . "\n";
            echo "  - Firmware Version: " . ($deviceInfo['firmware_version'] ?? 'N/A') . "\n";
            echo "  - Serial Number: " . ($deviceInfo['serial_number'] ?? 'N/A') . "\n";
        } else {
            echo "⚠ Device info not available\n";
        }
        
        // Disconnect
        $zkteco->disconnect();
        echo "\n✓ Disconnected successfully\n";
        
        echo "\n=== Test Result: SUCCESS ===\n";
        echo "The ZKTeco connection is working correctly!\n";
        
    } else {
        echo "✗ Connection failed\n";
        echo "\n=== Test Result: FAILED ===\n";
        echo "Please check:\n";
        echo "  1. Device IP address is correct\n";
        echo "  2. Device is powered on and connected to network\n";
        echo "  3. Port 4370 is not blocked by firewall\n";
        echo "  4. Device and server are on the same network\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "\n=== Test Result: ERROR ===\n";
    echo "Error details: " . $e->getMessage() . "\n";
    echo "\nTroubleshooting:\n";
    echo "  1. Check if sockets extension is enabled: php -m | findstr sockets\n";
    echo "  2. Verify device IP and port are correct\n";
    echo "  3. Check network connectivity: ping {$testIP}\n";
    echo "  4. Ensure device communication key (password) is correct\n";
}

echo "\n";










