<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DropOrphanTables extends Command
{
    protected $signature = 'db:drop-orphan-tables {--force : Force execution without confirmation}';
    protected $description = 'Drop database tables that do not have corresponding migration files';

    public function handle()
    {
        $sqlFile = base_path('drop_orphan_tables.sql');
        
        if (!File::exists($sqlFile)) {
            $this->error("SQL file not found: {$sqlFile}");
            $this->info("Please run: php remove_orphan_tables.php first");
            return 1;
        }

        $sql = File::get($sqlFile);
        
        // Count tables to be dropped
        preg_match_all('/DROP TABLE IF EXISTS `([^`]+)`;/', $sql, $matches);
        $tableCount = count($matches[1]);
        
        $this->warn("⚠️  WARNING: This will drop {$tableCount} tables!");
        $this->newLine();
        $this->info("Tables to be dropped:");
        foreach (array_slice($matches[1], 0, 10) as $table) {
            $this->line("  - {$table}");
        }
        if ($tableCount > 10) {
            $this->line("  ... and " . ($tableCount - 10) . " more tables");
        }
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to proceed? This action cannot be undone!')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        try {
            $this->info('Executing SQL script...');
            
            // Disable foreign key checks and execute
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            
            // Execute each DROP statement
            $lines = explode("\n", $sql);
            $dropped = 0;
            $errors = 0;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || strpos($line, '--') === 0) {
                    continue;
                }
                
                if (preg_match('/DROP TABLE IF EXISTS `([^`]+)`;/', $line, $match)) {
                    try {
                        DB::statement($line);
                        $dropped++;
                        if ($this->getOutput()->isVerbose()) {
                            $this->line("  ✓ Dropped: {$match[1]}");
                        }
                    } catch (\Exception $e) {
                        $errors++;
                        $this->warn("  ✗ Failed to drop {$match[1]}: " . $e->getMessage());
                    }
                }
            }
            
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            
            $this->newLine();
            $this->info("✅ Operation completed!");
            $this->info("   Tables dropped: {$dropped}");
            if ($errors > 0) {
                $this->warn("   Errors: {$errors}");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error executing SQL: " . $e->getMessage());
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            return 1;
        }
    }
}

