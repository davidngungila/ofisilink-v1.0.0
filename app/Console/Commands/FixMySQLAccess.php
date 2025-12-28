<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;
use PDOException;

class FixMySQLAccess extends Command
{
    protected $signature = 'mysql:fix-access 
                            {--host= : MySQL server host}
                            {--database= : Database name}
                            {--user= : MySQL username}
                            {--password= : MySQL password}
                            {--from-host= : Hostname/IP to grant access from}';

    protected $description = 'Automatically fix MySQL access permissions for ZKBio Time.Net sync';

    public function handle()
    {
        $this->info('=== MySQL Access Fix Tool ===');
        $this->newLine();

        // Get parameters
        $host = $this->option('host') ?: $this->ask('MySQL Server Host', '192.168.100.109');
        $database = $this->option('database') ?: $this->ask('Database Name', 'ofisi');
        $user = $this->option('user') ?: $this->ask('MySQL Username', 'root');
        $password = $this->option('password') ?: $this->secret('MySQL Password (leave empty if no password)');
        $fromHost = $this->option('from-host') ?: gethostname();

        $this->newLine();
        $this->info("Attempting to connect to MySQL server: {$host}");
        $this->info("Database: {$database}");
        $this->info("User: {$user}");
        $this->info("Granting access from: {$fromHost}");
        $this->newLine();

        try {
            // Try to connect to MySQL server
            $dsn = "mysql:host={$host};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $password ?: null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5
            ]);

            $this->info('✅ Connected to MySQL server successfully!');
            $this->newLine();

            // Check MySQL version
            $version = $pdo->query("SELECT VERSION() as version")->fetch(PDO::FETCH_ASSOC)['version'];
            $this->info("MySQL Version: {$version}");
            
            $isMySQL8 = version_compare($version, '8.0.0', '>=');
            $this->newLine();

            // Try to grant access
            $this->info('Attempting to grant access...');

            try {
                if ($isMySQL8) {
                    // MySQL 8.0+ syntax
                    $this->info('Using MySQL 8.0+ syntax...');
                    
                    // Try to create user if doesn't exist
                    try {
                        $pdo->exec("CREATE USER IF NOT EXISTS '{$user}'@'{$fromHost}'");
                        $this->info("✓ User '{$user}'@'{$fromHost}' created/verified");
                    } catch (PDOException $e) {
                        // User might already exist, continue
                        $this->warn("User might already exist: " . $e->getMessage());
                    }
                    
                    // Grant privileges
                    $pdo->exec("GRANT ALL PRIVILEGES ON {$database}.* TO '{$user}'@'{$fromHost}'");
                    $this->info("✓ Privileges granted");
                    
                } else {
                    // MySQL 5.7 and below
                    $this->info('Using MySQL 5.7 syntax...');
                    $pdo->exec("GRANT ALL PRIVILEGES ON {$database}.* TO '{$user}'@'{$fromHost}'");
                    $this->info("✓ Privileges granted");
                }

                // Flush privileges
                $pdo->exec("FLUSH PRIVILEGES");
                $this->info("✓ Privileges flushed");
                $this->newLine();

                // Verify
                $this->info('Verifying access...');
                $grants = $pdo->query("SHOW GRANTS FOR '{$user}'@'{$fromHost}'")->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($grants)) {
                    $this->info('✅ Access granted successfully!');
                    $this->newLine();
                    $this->info('Grants:');
                    foreach ($grants as $grant) {
                        $this->line("  - {$grant}");
                    }
                    $this->newLine();
                    $this->info('✅ MySQL access fixed! You can now run the sync command.');
                    return 0;
                } else {
                    $this->warn('⚠️  Could not verify grants, but command executed. Please test manually.');
                }

            } catch (PDOException $e) {
                $this->error('❌ Failed to grant access: ' . $e->getMessage());
                $this->newLine();
                $this->showManualInstructions($host, $database, $user, $fromHost, $isMySQL8);
                return 1;
            }

        } catch (PDOException $e) {
            $this->error('❌ Cannot connect to MySQL server: ' . $e->getMessage());
            $this->newLine();
            $this->warn('This command needs to be run from a machine that can connect to the MySQL server.');
            $this->warn('You may need to run it directly on the MySQL server (192.168.100.109).');
            $this->newLine();
            $this->showManualInstructions($host, $database, $user, $fromHost, false);
            return 1;
        }
    }

    private function showManualInstructions($host, $database, $user, $fromHost, $isMySQL8)
    {
        $this->info('=== Manual Fix Instructions ===');
        $this->newLine();
        $this->info('Run these commands on the MySQL server (' . $host . '):');
        $this->newLine();
        $this->line('1. Connect to MySQL:');
        $this->line('   mysql -u ' . $user);
        $this->newLine();
        $this->line('2. Run this SQL command:');
        
        if ($isMySQL8) {
            $this->line('   CREATE USER IF NOT EXISTS \'' . $user . '\'@\'' . $fromHost . '\';');
            $this->line('   GRANT ALL PRIVILEGES ON ' . $database . '.* TO \'' . $user . '\'@\'' . $fromHost . '\';');
        } else {
            $this->line('   GRANT ALL PRIVILEGES ON ' . $database . '.* TO \'' . $user . '\'@\'' . $fromHost . '\';');
        }
        
        $this->line('   FLUSH PRIVILEGES;');
        $this->newLine();
        $this->line('OR (easier - allow from any host):');
        $this->line('   GRANT ALL PRIVILEGES ON ' . $database . '.* TO \'' . $user . '\'@\'%\';');
        $this->line('   FLUSH PRIVILEGES;');
        $this->newLine();
    }
}







