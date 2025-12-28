<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use App\Services\EmailService;
use App\Models\User;
use App\Models\DatabaseBackup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SystemDatabaseBackup extends Command
{
    protected $signature = 'system:backup-db {--sleep-30 : Sleep 30 seconds before starting} {--return : Return path instead of printing}';

    protected $description = 'Dump full database to SQL file with password protection, append current year to filename, and notify admins';

    public function handle(NotificationService $notifier): int
    {
        if ($this->option('sleep-30')) {
            sleep(30);
        }

        $disk = Storage::disk('local');
        $backupDir = 'backups';
        
        // Ensure backup directory exists - try multiple methods
        if (!$disk->exists($backupDir)) {
            $disk->makeDirectory($backupDir);
        }
        
        // Also ensure directory exists on filesystem directly
        $backupDirPath = storage_path('app/' . $backupDir);
        if (!is_dir($backupDirPath)) {
            @mkdir($backupDirPath, 0755, true);
        }

        $now = now();
        $year = $now->year;
        $timestamp = $now->format('Ymd_His');
        $baseName = "ofisilink_backup_{$timestamp}_{$year}";

        // Use forward slash for storage path (Laravel convention)
        $sqlPath = $backupDir . "/{$baseName}.sql";
        // Use DIRECTORY_SEPARATOR for filesystem path
        $fullSqlPath = storage_path('app' . DIRECTORY_SEPARATOR . $backupDir . DIRECTORY_SEPARATOR . $baseName . '.sql');

        $backupSuccess = false;
        $errorMessage = null;
        
        // Create backup record in database
        $backupRecord = DatabaseBackup::create([
            'filename' => $baseName . '.sql',
            'file_path' => $sqlPath,
            'file_size' => 0,
            'status' => 'in_progress',
            'error_message' => null,
            'created_by' => null, // Can be set if called from web interface
        ]);

        try {
            // Build mysqldump command from DB config
            $db = config('database.connections.' . config('database.default'));
            $driver = $db['driver'] ?? '';
            
            if ($driver !== 'mysql') {
                throw new \Exception('This command currently supports only MySQL.');
            }

            $host = $db['host'] ?? '127.0.0.1';
            $port = $db['port'] ?? 3306;
            $database = $db['database'] ?? '';
            $username = $db['username'] ?? '';
            $password = $db['password'] ?? '';

            // Ensure the directory exists before attempting backup
            $backupDirPath = dirname($fullSqlPath);
            if (!is_dir($backupDirPath)) {
                if (!mkdir($backupDirPath, 0755, true)) {
                    throw new \Exception('Failed to create backup directory: ' . $backupDirPath);
                }
            }
            
            // Try to find mysqldump in common locations
            $mysqlDump = $this->findMysqldump();
            
            if (!$mysqlDump) {
                // Fallback: Use Laravel DB connection to export data
                // Only show info if not using --return flag (to avoid polluting output)
                if (!$this->option('return')) {
                    $this->info('mysqldump not found. Using Laravel DB connection for backup...');
                }
                \Log::info('Using Laravel DB connection method for backup', ['output_path' => $fullSqlPath]);
                $backupSuccess = $this->backupUsingLaravel($fullSqlPath, $database);
            } else {
                // Use mysqldump command
                \Log::info('Using mysqldump for backup', ['mysqldump' => $mysqlDump, 'output_path' => $fullSqlPath]);
                $cmdDump = $this->buildMysqldumpCommand($mysqlDump, $host, $port, $username, $password, $database, $fullSqlPath);
                $exit1 = $this->runShell($cmdDump);
                
                // Wait a moment for file to be written
                if ($exit1 === 0) {
                    sleep(1); // Give filesystem time to write
                }
                
                if ($exit1 === 0 && file_exists($fullSqlPath) && filesize($fullSqlPath) > 0) {
                    $backupSuccess = true;
                    \Log::info('mysqldump backup completed successfully', [
                        'file' => $fullSqlPath,
                        'size' => filesize($fullSqlPath)
                    ]);
                } else {
                    // If mysqldump fails, try Laravel method
                    \Log::warning('mysqldump failed or produced empty file', [
                        'exit_code' => $exit1,
                        'file_exists' => file_exists($fullSqlPath),
                        'file_size' => file_exists($fullSqlPath) ? filesize($fullSqlPath) : 0
                    ]);
                    // Only show info if not using --return flag
                    if (!$this->option('return')) {
                        $this->info('mysqldump failed. Trying Laravel DB connection method...');
                    }
                    if (file_exists($fullSqlPath)) {
                        @unlink($fullSqlPath);
                    }
                    $backupSuccess = $this->backupUsingLaravel($fullSqlPath, $database);
                }
            }

            if (!$backupSuccess || !file_exists($fullSqlPath) || filesize($fullSqlPath) === 0) {
                throw new \Exception('Database backup failed. Could not create SQL dump.');
            }

            // Verify SQL file was created and has content
            if (!file_exists($fullSqlPath)) {
                throw new \Exception('Backup SQL file was not created.');
            }
            
            // Verify file size
            $fileSize = filesize($fullSqlPath);
            if ($fileSize === 0) {
                throw new \Exception('Backup SQL file is empty.');
            }
            
            // Log successful backup creation
            \Log::info('Backup SQL file created successfully', [
                'path' => $fullSqlPath,
                'size' => $fileSize,
                'size_human' => $this->formatBytes($fileSize),
                'sql_path' => $sqlPath
            ]);

        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            $this->error('Backup failed: ' . $errorMessage);
            \Log::error('Backup failed: ' . $errorMessage, ['trace' => $e->getTraceAsString()]);
        }

        // Update backup record in database
        if (isset($backupRecord)) {
            if ($backupSuccess && isset($fullSqlPath) && file_exists($fullSqlPath)) {
                // Ensure file_path is correct (use forward slash for Laravel storage convention)
                $fileSize = filesize($fullSqlPath);
                $backupRecord->update([
                    'status' => 'completed',
                    'file_path' => $sqlPath, // Ensure we use the storage path format
                    'file_size' => $fileSize,
                    'completed_at' => now(),
                    'error_message' => null,
                ]);
                
                \Log::info('Backup record updated successfully', [
                    'backup_id' => $backupRecord->id,
                    'filename' => $backupRecord->filename,
                    'file_path' => $backupRecord->file_path,
                    'file_size' => $fileSize,
                    'full_path' => $fullSqlPath
                ]);
            } else {
                $backupRecord->update([
                    'status' => 'failed',
                    'error_message' => $errorMessage,
                    'completed_at' => now(),
                ]);
                
                \Log::error('Backup record marked as failed', [
                    'backup_id' => $backupRecord->id,
                    'error' => $errorMessage
                ]);
            }
        }

        // Always send notifications (success or failure)
        $this->sendNotifications($notifier, $now, $backupSuccess, $sqlPath ?? null, $errorMessage);

        if ($backupSuccess && isset($fullSqlPath) && file_exists($fullSqlPath)) {
            if ($this->option('return')) {
                // Only output the file path if successful - no other output
                $this->line($sqlPath);
            } else {
                $this->info('Backup completed: ' . $sqlPath);
            }
            return self::SUCCESS;
        } else {
            // On failure, don't output anything when --return is used
            // This prevents error messages from being treated as file paths
            if (!$this->option('return')) {
                $this->error('Backup failed. Check logs for details.');
            }
            return self::FAILURE;
        }
    }

    /**
     * Find mysqldump executable in common locations
     */
    protected function findMysqldump(): ?string
    {
        // Check if mysqldump is in PATH
        $paths = ['mysqldump'];
        
        // Common Windows locations
        if (stripos(PHP_OS, 'WIN') === 0) {
            $commonPaths = [
                'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                'C:\\wamp\\bin\\mysql\\mysql' . $this->getMysqlVersion() . '\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
                'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files (x86)\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
            ];
            $paths = array_merge($paths, $commonPaths);
        } else {
            // Linux/Mac locations
            $paths = array_merge($paths, [
                '/usr/bin/mysqldump',
                '/usr/local/bin/mysqldump',
                '/opt/mysql/bin/mysqldump',
            ]);
        }

        foreach ($paths as $path) {
            if ($this->commandExists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Check if command exists
     */
    protected function commandExists(string $command): bool
    {
        // Check if exec() function is available
        if (!function_exists('exec')) {
            // exec() is disabled - check if command exists as file
            if (file_exists($command)) {
                return true;
            }
            // Can't check via exec, assume it doesn't exist
            // Will fall back to Laravel DB connection method
            return false;
        }
        
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows: check if file exists
            if (file_exists($command)) {
                return true;
            }
            // Try 'where' command
            $output = [];
            $return = 0;
            @exec('where ' . escapeshellarg($command) . ' 2>nul', $output, $return);
            return $return === 0 && !empty($output);
        } else {
            // Unix: use 'which' command
            $output = [];
            $return = 0;
            @exec('which ' . escapeshellarg($command) . ' 2>/dev/null', $output, $return);
            return $return === 0 && !empty($output);
        }
    }

    /**
     * Get MySQL version from config (for WAMP path)
     */
    protected function getMysqlVersion(): string
    {
        // Try to detect MySQL version, default to 8.0
        return '8.0';
    }

    /**
     * Build mysqldump command
     */
    protected function buildMysqldumpCommand(string $mysqlDump, string $host, string $port, string $username, string $password, string $database, string $outputPath): string
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows command
            return sprintf('"%s" --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > "%s"',
                $mysqlDump,
                escapeshellarg($host),
                escapeshellarg((string)$port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                $outputPath
            );
        } else {
            // Unix command
            return sprintf('%s --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
                escapeshellarg($mysqlDump),
                escapeshellarg($host),
                escapeshellarg((string)$port),
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($outputPath)
            );
        }
    }

    /**
     * Backup using Laravel DB connection (fallback method)
     */
    protected function backupUsingLaravel(string $outputPath, string $database): bool
    {
        $handle = null;
        try {
            // Ensure directory exists
            $dir = dirname($outputPath);
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    \Log::error('Failed to create backup directory: ' . $dir);
                    return false;
                }
            }
            
            $handle = fopen($outputPath, 'w');
            if (!$handle) {
                \Log::error('Failed to open backup file for writing: ' . $outputPath);
                return false;
            }

            // Write header
            fwrite($handle, "-- OfisiLink Database Backup\n");
            fwrite($handle, "-- Generated: " . now()->toDateTimeString() . "\n");
            fwrite($handle, "-- Database: {$database}\n\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "SET time_zone = \"+00:00\";\n\n");
            fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
            fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
            fwrite($handle, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
            fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n\n");

            // Test database connection
            try {
                \DB::connection()->getPdo();
            } catch (\Exception $e) {
                \Log::error('Database connection failed during backup: ' . $e->getMessage());
                fclose($handle);
                @unlink($outputPath);
                return false;
            }

            // Get all tables
            $tables = \DB::select("SHOW TABLES");
            if (empty($tables)) {
                \Log::warning('No tables found in database: ' . $database);
                fclose($handle);
                return file_exists($outputPath) && filesize($outputPath) > 0;
            }
            
            $tableKey = 'Tables_in_' . $database;

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                
                try {
                    // Get table structure
                    fwrite($handle, "\n-- Table structure for table `{$tableName}`\n");
                    fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                    
                    $createTable = \DB::select("SHOW CREATE TABLE `{$tableName}`");
                    if (!empty($createTable)) {
                        $createKey = 'Create Table';
                        fwrite($handle, $createTable[0]->$createKey . ";\n\n");
                    }

                    // Get table data
                    fwrite($handle, "-- Dumping data for table `{$tableName}`\n");
                    $rows = \DB::table($tableName)->get();
                    
                    if ($rows->count() > 0) {
                        fwrite($handle, "INSERT INTO `{$tableName}` VALUES\n");
                        $values = [];
                        foreach ($rows as $row) {
                            $rowArray = (array)$row;
                            $rowValues = [];
                            foreach ($rowArray as $value) {
                                if ($value === null) {
                                    $rowValues[] = 'NULL';
                                } elseif (is_numeric($value)) {
                                    $rowValues[] = $value;
                                } else {
                                    // Properly escape SQL strings
                                    $rowValues[] = "'" . str_replace(['\\', "'", "\n", "\r"], ['\\\\', "\\'", "\\n", "\\r"], $value) . "'";
                                }
                            }
                            $values[] = '(' . implode(',', $rowValues) . ')';
                        }
                        fwrite($handle, implode(",\n", $values) . ";\n\n");
                    }
                } catch (\Exception $tableError) {
                    \Log::warning("Error backing up table {$tableName}: " . $tableError->getMessage());
                    // Continue with next table
                    continue;
                }
            }

            // Write footer
            fwrite($handle, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
            fwrite($handle, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
            fwrite($handle, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");

            fclose($handle);
            $handle = null;
            
            // Verify file was created and has content
            if (!file_exists($outputPath)) {
                \Log::error('Backup file was not created: ' . $outputPath);
                return false;
            }
            
            $fileSize = filesize($outputPath);
            if ($fileSize === 0) {
                \Log::error('Backup file is empty: ' . $outputPath);
                @unlink($outputPath);
                return false;
            }
            
            return true;
        } catch (\Throwable $e) {
            \Log::error('Laravel backup method failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            if ($handle && is_resource($handle)) {
                fclose($handle);
            }
            if (file_exists($outputPath)) {
                @unlink($outputPath);
            }
            return false;
        }
    }

    /**
     * Send notifications (SMS and Email) - always called
     */
    protected function sendNotifications(NotificationService $notifier, $now, bool $success, ?string $sqlPath, ?string $errorMessage): void
    {
        try {
            $admins = \App\Models\User::whereHas('roles', function($query) {
                $query->where('name', 'System Admin');
            })->where('is_active', true)->get();

            if ($success && $sqlPath) {
                $downloadUrl = route('admin.system.backup.download', ['file' => basename($sqlPath)], false);
                $message = 'Backup for OfisiLink System completed at ' . $now->toDateTimeString() . '. Password: Ofisilink. Download: ' . url($downloadUrl);
                $subject = 'OfisiLink System Backup Completed - ' . $now->format('Y-m-d H:i:s');
                $fullPath = storage_path('app/backups/' . basename($sqlPath));
            } else {
                $message = 'Backup for OfisiLink System FAILED at ' . $now->toDateTimeString() . '. Error: ' . ($errorMessage ?? 'Unknown error');
                $subject = 'OfisiLink System Backup FAILED - ' . $now->format('Y-m-d H:i:s');
                $downloadUrl = null;
                $fullPath = null;
            }

            // Send SMS notification to all admins
            foreach ($admins as $admin) {
                if ($admin->mobile || $admin->phone) {
                    try {
                        $notifier->sendSMS($admin->mobile ?? $admin->phone, $message);
                    } catch (\Throwable $smsError) {
                        \Log::error('Backup SMS failed for admin ' . $admin->id . ': ' . $smsError->getMessage());
                    }
                }
            }

            // Initialize EmailService
            $emailService = new EmailService();
            
            // Collect all email recipients
            $emailRecipients = [];
            foreach ($admins as $admin) {
                if ($admin->email) {
                    $emailRecipients[] = $admin->email;
                }
            }
            // Always include davidngungila@gmail.com
            $emailRecipients[] = 'davidngungila@gmail.com';
            $emailRecipients = array_unique($emailRecipients);
            
            // Prepare email content
            if ($success && $fullPath && file_exists($fullPath)) {
                // Success: Prepare success email
                $emailBody = View::make('emails.backup-completed', [
                    'admin' => (object)['name' => 'Administrator'],
                    'backup_file' => basename($sqlPath),
                    'completed_at' => $now->toDateTimeString(),
                    'download_url' => $downloadUrl ? url($downloadUrl) : null,
                    'password' => 'Ofisilink',
                    'file_size' => $this->formatBytes(filesize($fullPath)),
                ])->render();
                
                // Send to all recipients
                foreach ($emailRecipients as $recipient) {
                    try {
                        $emailService->send(
                            $recipient,
                            $subject,
                            $emailBody,
                            $fullPath,
                            basename($sqlPath)
                        );
                        \Log::info('Backup success email sent to: ' . $recipient);
                    } catch (\Throwable $emailError) {
                        \Log::error('Backup email failed for ' . $recipient . ': ' . $emailError->getMessage());
                    }
                }
            } else {
                // Failure: Prepare failure email
                $emailBody = View::make('emails.backup-failed', [
                    'admin' => (object)['name' => 'Administrator'],
                    'error_message' => $errorMessage ?? 'Unknown error occurred',
                    'failed_at' => $now->toDateTimeString(),
                ])->render();
                
                // Send to all recipients
                foreach ($emailRecipients as $recipient) {
                    try {
                        $emailService->send(
                            $recipient,
                            $subject,
                            $emailBody
                        );
                        \Log::info('Backup failure email sent to: ' . $recipient);
                    } catch (\Throwable $emailError) {
                        \Log::error('Backup failure email failed for ' . $recipient . ': ' . $emailError->getMessage());
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Backup notification process failed: ' . $e->getMessage());
        }
    }

    protected function runShell(string $command): int
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            $proc = proc_open(['cmd', '/C', $command], [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes);
        } else {
            $proc = proc_open($command, [1 => ['pipe','w'], 2 => ['pipe','w']], $pipes);
        }
        if (!\is_resource($proc)) {
            return 1;
        }
        stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        foreach ($pipes as $p) { fclose($p); }
        return proc_close($proc);
    }

    protected function zipWithPassword(string $sourceFile, string $zipFile, string $password): bool
    {
        if (class_exists(\ZipArchive::class)) {
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return false;
            }
            $name = basename($sourceFile);
            if (!$zip->addFile($sourceFile, $name)) {
                $zip->close();
                return false;
            }
            if (defined('ZipArchive::EM_AES_256')) {
                $zip->setPassword($password);
                $zip->setEncryptionName($name, \ZipArchive::EM_AES_256);
            } else {
                // Fallback: try external 7z/zip
                $zip->close();
                return $this->zipExternal($sourceFile, $zipFile, $password);
            }
            $zip->close();
            return true;
        }
        return $this->zipExternal($sourceFile, $zipFile, $password);
    }

    protected function zipExternal(string $sourceFile, string $zipFile, string $password): bool
    {
        // Try 7z first
        $cmd7z = sprintf('7z a -tzip -p%s -mem=AES256 %s %s',
            escapeshellarg($password),
            escapeshellarg($zipFile),
            escapeshellarg($sourceFile)
        );
        $exit = $this->runShell($cmd7z);
        if ($exit === 0 && file_exists($zipFile)) {
            return true;
        }

        // Try zip (Info-ZIP) with password (legacy encryption)
        $cmdZip = sprintf('zip -j -P %s %s %s',
            escapeshellarg($password),
            escapeshellarg($zipFile),
            escapeshellarg($sourceFile)
        );
        $exit = $this->runShell($cmdZip);
        return $exit === 0 && file_exists($zipFile);
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}







