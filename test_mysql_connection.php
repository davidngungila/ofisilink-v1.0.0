<?php
/**
 * Test MySQL Connection to ZKBio Time.Net Database
 * Run: php test_mysql_connection.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\AttendanceDevice;

echo "=== MySQL Connection Test for ZKBio Time.Net ===\n\n";

$device = AttendanceDevice::find(4); // UF 2000 HQ

if (!$device) {
    echo "❌ Device not found!\n";
    exit(1);
}

echo "Device: {$device->name} (ID: {$device->id})\n";
$settings = $device->settings ?? [];

$host = $settings['zkbio_db_host'] ?? '192.168.100.109';
$db = $settings['zkbio_db_path'] ?? 'ofisi';
$user = $settings['zkbio_db_user'] ?? 'root';
$pass = $settings['zkbio_db_password'] ?? '';

echo "Host: {$host}\n";
echo "Database: {$db}\n";
echo "User: {$user}\n";
echo "Password: " . ($pass ? '***' : 'NOT SET (no password)') . "\n\n";

echo "Testing connection...\n";

try {
    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
    // If password is empty, pass null instead of empty string
    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ];
    $pdo = new PDO($dsn, $user, $pass ?: null, $pdoOptions);
    
    echo "✅ MySQL connection successful!\n\n";
    
    // Test if we can query the database
    echo "Testing database access...\n";
    $stmt = $pdo->query("SELECT DATABASE() as current_db");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Current database: {$result['current_db']}\n";
    
    // Check if CHECKINOUT table exists (ZKBio Time.Net table)
    $stmt = $pdo->query("SHOW TABLES LIKE 'CHECKINOUT'");
    if ($stmt->rowCount() > 0) {
        echo "✅ CHECKINOUT table found!\n";
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM CHECKINOUT");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Total attendance records: {$result['count']}\n";
    } else {
        echo "⚠️  CHECKINOUT table not found. Checking for alternative table names...\n";
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Available tables: " . implode(', ', $tables) . "\n";
    }
    
    echo "\n✅ All tests passed! You can now run the sync command.\n";
    echo "Command: php artisan attendance:sync-zkbiotime --device=\"UF 2000 HQ\"\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n\n";
    
    if (strpos($e->getMessage(), 'not allowed to connect') !== false) {
        echo "🔧 SOLUTION: Grant MySQL access\n";
        echo "Run this on MySQL server (192.168.100.109):\n";
        echo "  mysql -u root -p\n";
        echo "  GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%' IDENTIFIED BY 'your_password';\n";
        echo "  FLUSH PRIVILEGES;\n\n";
        echo "See fix_mysql_access.sql for complete instructions.\n";
    } elseif (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "🔧 SOLUTION: Check username and password in device settings.\n";
    } elseif (strpos($e->getMessage(), "Unknown database") !== false) {
        echo "🔧 SOLUTION: Database '{$db}' does not exist. Check database name in device settings.\n";
    } else {
        echo "🔧 Check:\n";
        echo "  1. MySQL service is running on {$host}\n";
        echo "  2. Firewall allows port 3306\n";
        echo "  3. MySQL bind-address allows remote connections\n";
        echo "  4. Network connectivity: ping {$host}\n";
    }
    
    exit(1);
}

