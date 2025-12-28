<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanActivityLogSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning all Activity Log data...');
        
        // Disable foreign key checks for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        $deletedCounts = [];
        
        // Delete Activity Logs
        $this->command->info('Deleting Activity Logs...');
        if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
            $deletedCounts['activity_logs'] = DB::table('activity_logs')->count();
            DB::table('activity_logs')->delete();
        }
        
        // Re-enable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        $this->command->info("\n==========================================");
        $this->command->info("Successfully cleaned all Activity Log data!");
        $this->command->info("==========================================");
        $this->command->info("\nDeleted Records:");
        $this->command->info("  - activity_logs: " . ($deletedCounts['activity_logs'] ?? 0));
        
        $totalDeleted = array_sum($deletedCounts);
        $this->command->info("\nTotal records deleted: {$totalDeleted}");
        $this->command->info("\n");
    }
}

