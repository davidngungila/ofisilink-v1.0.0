<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanFilesAndRacksSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning all Digital Files and Physical Racks data...');
        
        // Disable foreign key checks for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        $deletedCounts = [];
        
        // ============================================
        // DIGITAL FILES CLEANUP
        // ============================================
        $this->command->info("\n--- Digital Files Cleanup ---");
        
        // 1. Delete File Activities (depends on Files)
        $this->command->info('Deleting File Activities...');
        if (DB::getSchemaBuilder()->hasTable('file_activities')) {
            $deletedCounts['file_activities'] = DB::table('file_activities')->count();
            DB::table('file_activities')->delete();
        }
        
        // 2. Delete File Access Requests (depends on Files)
        $this->command->info('Deleting File Access Requests...');
        if (DB::getSchemaBuilder()->hasTable('file_access_requests')) {
            $deletedCounts['file_access_requests'] = DB::table('file_access_requests')->count();
            DB::table('file_access_requests')->delete();
        }
        
        // 3. Delete File User Assignments (depends on Files)
        $this->command->info('Deleting File User Assignments...');
        if (DB::getSchemaBuilder()->hasTable('file_user_assignments')) {
            $deletedCounts['file_user_assignments'] = DB::table('file_user_assignments')->count();
            DB::table('file_user_assignments')->delete();
        }
        
        // 4. Delete Files (depends on File Folders)
        $this->command->info('Deleting Files...');
        if (DB::getSchemaBuilder()->hasTable('files')) {
            $deletedCounts['files'] = DB::table('files')->count();
            DB::table('files')->delete();
        }
        
        // 5. Delete File Folders (hierarchical - delete children first)
        $this->command->info('Deleting File Folders...');
        if (DB::getSchemaBuilder()->hasTable('file_folders')) {
            // Delete all folders (cascade will handle children)
            $deletedCounts['file_folders'] = DB::table('file_folders')->count();
            DB::table('file_folders')->delete();
        }
        
        // ============================================
        // PHYSICAL RACKS CLEANUP
        // ============================================
        $this->command->info("\n--- Physical Racks Cleanup ---");
        
        // 6. Delete Rack Activities (depends on Rack Files)
        $this->command->info('Deleting Rack Activities...');
        if (DB::getSchemaBuilder()->hasTable('rack_activities')) {
            $deletedCounts['rack_activities'] = DB::table('rack_activities')->count();
            DB::table('rack_activities')->delete();
        }
        
        // 7. Delete Rack File Requests (depends on Rack Files)
        $this->command->info('Deleting Rack File Requests...');
        if (DB::getSchemaBuilder()->hasTable('rack_file_requests')) {
            $deletedCounts['rack_file_requests'] = DB::table('rack_file_requests')->count();
            DB::table('rack_file_requests')->delete();
        }
        
        // 8. Delete Rack Files (depends on Rack Folders)
        $this->command->info('Deleting Rack Files...');
        if (DB::getSchemaBuilder()->hasTable('rack_files')) {
            $deletedCounts['rack_files'] = DB::table('rack_files')->count();
            DB::table('rack_files')->delete();
        }
        
        // 9. Delete Rack Folders (depends on Rack Categories)
        $this->command->info('Deleting Rack Folders...');
        if (DB::getSchemaBuilder()->hasTable('rack_folders')) {
            $deletedCounts['rack_folders'] = DB::table('rack_folders')->count();
            DB::table('rack_folders')->delete();
        }
        
        // 10. Delete Rack Categories (only non-system ones, if is_system column exists)
        $this->command->info('Deleting Rack Categories...');
        if (DB::getSchemaBuilder()->hasTable('rack_categories')) {
            if (DB::getSchemaBuilder()->hasColumn('rack_categories', 'is_system')) {
                $deletedCounts['rack_categories'] = DB::table('rack_categories')
                    ->where('is_system', false)
                    ->count();
                DB::table('rack_categories')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['rack_categories'] = DB::table('rack_categories')->count();
                DB::table('rack_categories')->delete();
            }
        }
        
        // Re-enable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        $this->command->info("\n==========================================");
        $this->command->info("Successfully cleaned all Files and Racks data!");
        $this->command->info("==========================================");
        $this->command->info("\nDeleted Records:");
        
        $digitalTotal = 0;
        $racksTotal = 0;
        
        $digitalTables = ['file_activities', 'file_access_requests', 'file_user_assignments', 'files', 'file_folders'];
        $racksTables = ['rack_activities', 'rack_file_requests', 'rack_files', 'rack_folders', 'rack_categories'];
        
        $this->command->info("\nDigital Files:");
        foreach ($digitalTables as $table) {
            $count = $deletedCounts[$table] ?? 0;
            $digitalTotal += $count;
            $this->command->info("  - {$table}: {$count}");
        }
        
        $this->command->info("\nPhysical Racks:");
        foreach ($racksTables as $table) {
            $count = $deletedCounts[$table] ?? 0;
            $racksTotal += $count;
            $this->command->info("  - {$table}: {$count}");
        }
        
        $totalDeleted = array_sum($deletedCounts);
        $this->command->info("\nSummary:");
        $this->command->info("  - Digital Files Total: {$digitalTotal}");
        $this->command->info("  - Physical Racks Total: {$racksTotal}");
        $this->command->info("  - Grand Total: {$totalDeleted}");
        $this->command->info("\n");
    }
}

