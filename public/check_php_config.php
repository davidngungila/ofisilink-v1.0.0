<?php
/**
 * PHP Configuration Checker for ZKTeco Integration
 * Access this file via browser to check PHP configuration
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Configuration Check - ZKTeco</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .check-item { margin: 15px 0; padding: 15px; border-left: 4px solid #ddd; background: #f9f9f9; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        .info { border-left-color: #17a2b8; background: #d1ecf1; }
        .status { font-weight: bold; font-size: 1.1em; }
        .success .status { color: #28a745; }
        .error .status { color: #dc3545; }
        .warning .status { color: #856404; }
        .info .status { color: #0c5460; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 PHP Configuration Check - ZKTeco Integration</h1>
        
        <?php
        $checks = [];
        
        // Check 1: PHP Version
        $phpVersion = phpversion();
        $phpVersionOk = version_compare($phpVersion, '7.4.0', '>=');
        $checks[] = [
            'name' => 'PHP Version',
            'status' => $phpVersionOk ? 'success' : 'error',
            'message' => $phpVersionOk 
                ? "PHP {$phpVersion} is installed (✓)" 
                : "PHP {$phpVersion} is installed. Recommended: PHP 7.4+",
            'details' => "Current version: {$phpVersion}"
        ];
        
        // Check 2: Sockets Extension
        $socketsLoaded = function_exists('socket_create');
        $checks[] = [
            'name' => 'PHP Sockets Extension',
            'status' => $socketsLoaded ? 'success' : 'error',
            'message' => $socketsLoaded 
                ? 'Sockets extension is LOADED (✓)' 
                : 'Sockets extension is NOT LOADED (✗)',
            'details' => $socketsLoaded 
                ? 'socket_create() function is available' 
                : 'Enable extension=sockets in php.ini'
        ];
        
        // Check 3: PHP.ini Location
        $phpIniPath = php_ini_loaded_file();
        $phpIniScanned = php_ini_scanned_files();
        $checks[] = [
            'name' => 'PHP Configuration File',
            'status' => 'info',
            'message' => $phpIniPath ? "php.ini location: <code>{$phpIniPath}</code>" : 'No php.ini file found',
            'details' => $phpIniScanned ? "Additional ini files: {$phpIniScanned}" : 'No additional ini files'
        ];
        
        // Check 4: Extension Directory
        $extDir = ini_get('extension_dir');
        $checks[] = [
            'name' => 'Extension Directory',
            'status' => 'info',
            'message' => "Extension directory: <code>{$extDir}</code>",
            'details' => 'Make sure sockets.dll (Windows) or sockets.so (Linux) exists here'
        ];
        
        // Check 5: Loaded Extensions
        $loadedExtensions = get_loaded_extensions();
        $socketsInList = in_array('sockets', $loadedExtensions);
        $checks[] = [
            'name' => 'Loaded Extensions List',
            'status' => $socketsInList ? 'success' : 'error',
            'message' => $socketsInList 
                ? 'Sockets extension found in loaded extensions (✓)' 
                : 'Sockets extension NOT found in loaded extensions (✗)',
            'details' => 'Total extensions loaded: ' . count($loadedExtensions)
        ];
        
        // Check 6: Server Information
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
        $checks[] = [
            'name' => 'Web Server',
            'status' => 'info',
            'message' => "Server: {$serverSoftware}",
            'details' => 'PHP SAPI: ' . php_sapi_name()
        ];
        
        // Display all checks
        foreach ($checks as $check) {
            echo '<div class="check-item ' . $check['status'] . '">';
            echo '<div class="status">' . $check['name'] . '</div>';
            echo '<div>' . $check['message'] . '</div>';
            if (isset($check['details'])) {
                echo '<div style="margin-top: 5px; font-size: 0.9em; color: #666;">' . $check['details'] . '</div>';
            }
            echo '</div>';
        }
        
        // Instructions if sockets not loaded
        if (!$socketsLoaded) {
            echo '<div class="check-item error">';
            echo '<div class="status">⚠️ How to Fix</div>';
            echo '<div><strong>To enable PHP Sockets extension:</strong></div>';
            echo '<ol style="margin-top: 10px;">';
            echo '<li>Open the php.ini file: <code>' . ($phpIniPath ?: 'php.ini') . '</code></li>';
            echo '<li>Find the line: <code>;extension=sockets</code></li>';
            echo '<li>Remove the semicolon to uncomment: <code>extension=sockets</code></li>';
            echo '<li>Save the file</li>';
            echo '<li><strong>Restart your web server</strong> (Apache/Nginx) or Laragon</li>';
            echo '<li>Refresh this page to verify</li>';
            echo '</ol>';
            echo '</div>';
        }
        
        // Show phpinfo link
        echo '<div class="check-item info">';
        echo '<div class="status">ℹ️ More Information</div>';
        echo '<div>For detailed PHP configuration, check: <a href="phpinfo.php" target="_blank">phpinfo.php</a></div>';
        echo '</div>';
        ?>
        
        <a href="javascript:location.reload()" class="btn">🔄 Refresh Check</a>
    </div>
</body>
</html>










