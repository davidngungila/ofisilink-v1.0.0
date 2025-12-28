<?php
/**
 * PHP Sockets Extension Checker
 * 
 * Run this file to check if the PHP sockets extension is enabled.
 * Access via browser: http://your-domain/check_sockets_extension.php
 */

echo "<h1>PHP Sockets Extension Check</h1>";

// Check if function exists
if (function_exists('socket_create')) {
    echo "<p style='color: green;'><strong>✓ PHP Sockets extension is ENABLED</strong></p>";
    echo "<p>You can use ZKTeco device connections.</p>";
    
    // Try to create a socket to verify it works
    try {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket !== false) {
            echo "<p style='color: green;'>✓ Socket creation test: SUCCESS</p>";
            socket_close($socket);
        } else {
            echo "<p style='color: orange;'>⚠ Socket creation test: FAILED (but extension is loaded)</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Socket creation test: ERROR - " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'><strong>✗ PHP Sockets extension is NOT ENABLED</strong></p>";
    echo "<h2>How to Enable PHP Sockets Extension:</h2>";
    echo "<ol>";
    echo "<li><strong>Find your php.ini file:</strong><br>";
    echo "Location: " . php_ini_loaded_file() . "<br>";
    echo "If empty, check: " . php_ini_scanned_files() . "</li>";
    echo "<li><strong>Edit php.ini:</strong><br>";
    echo "Find the line: <code>;extension=sockets</code> (it may be commented with ;)<br>";
    echo "Uncomment it to: <code>extension=sockets</code><br>";
    echo "Or add this line if it doesn't exist: <code>extension=sockets</code></li>";
    echo "<li><strong>Restart your web server:</strong><br>";
    echo "- Apache: <code>sudo service apache2 restart</code> or <code>sudo systemctl restart apache2</code><br>";
    echo "- Nginx + PHP-FPM: <code>sudo service php-fpm restart</code> or <code>sudo systemctl restart php-fpm</code><br>";
    echo "- XAMPP: Restart Apache from XAMPP Control Panel<br>";
    echo "- WAMP: Restart all services from WAMP menu</li>";
    echo "<li><strong>Verify:</strong> Refresh this page to confirm the extension is enabled</li>";
    echo "</ol>";
    
    echo "<h3>Alternative: Enable via Command Line (if you have access)</h3>";
    echo "<pre>";
    echo "# For Ubuntu/Debian:\n";
    echo "sudo apt-get install php-sockets\n";
    echo "sudo service apache2 restart\n\n";
    echo "# For CentOS/RHEL:\n";
    echo "sudo yum install php-sockets\n";
    echo "sudo systemctl restart httpd\n";
    echo "</pre>";
}

echo "<hr>";
echo "<h2>PHP Information:</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>PHP.ini File:</strong> " . (php_ini_loaded_file() ?: 'Not found') . "</p>";
echo "<p><strong>Server API:</strong> " . php_sapi_name() . "</p>";

echo "<h3>Loaded Extensions (search for 'sockets'):</h3>";
$extensions = get_loaded_extensions();
sort($extensions);
echo "<pre>" . implode("\n", $extensions) . "</pre>";

if (in_array('sockets', $extensions)) {
    echo "<p style='color: green;'><strong>✓ 'sockets' found in loaded extensions list</strong></p>";
} else {
    echo "<p style='color: red;'><strong>✗ 'sockets' NOT found in loaded extensions list</strong></p>";
}


