<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AttendanceDevice;
use App\Models\AttendanceLocation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanAttendances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendances:clean {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean all attendance records, devices, and locations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Count all records
        $attendancesCount = DB::table('attendances')->count();
        $devicesCount = DB::table('attendance_devices')->count();
        $locationsCount = DB::table('attendance_locations')->count();
        
        // Check if attendance_policies table exists
        $policiesCount = 0;
        if (DB::getSchemaBuilder()->hasTable('attendance_policies')) {
            $policiesCount = DB::table('attendance_policies')->count();
        }
        
        $totalCount = $attendancesCount + $devicesCount + $locationsCount + $policiesCount;

        if ($totalCount === 0) {
            $this->info('No attendance records, devices, or locations found in the database.');
            return Command::SUCCESS;
        }

        // Display summary
        $this->info('=== Attendance Cleanup ===');
        $this->line("  attendances: {$attendancesCount} records");
        $this->line("  attendance_devices: {$devicesCount} records");
        $this->line("  attendance_locations: {$locationsCount} records");
        if ($policiesCount > 0) {
            $this->line("  attendance_policies: {$policiesCount} records");
        }
        $this->newLine();
        $this->info("Total records to delete: {$totalCount}");

        if (!$this->option('force')) {
            if (!$this->confirm('Are you sure you want to delete all attendance data, devices, and locations? This action cannot be undone.')) {
                $this->info('Operation cancelled.');
                return Command::SUCCESS;
            }
        }

        $this->newLine();
        $this->info('Starting cleanup...');
        $this->newLine();

        try {
            DB::beginTransaction();

            $deletedCounts = [];

            // Delete attendances first (child records)
            if ($attendancesCount > 0) {
                $deleted = DB::table('attendances')->delete();
                $deletedCounts['attendances'] = $deleted;
                $this->info("  ✓ attendances: {$deleted} records deleted");
            }

            // Delete attendance devices
            if ($devicesCount > 0) {
                $deleted = DB::table('attendance_devices')->delete();
                $deletedCounts['attendance_devices'] = $deleted;
                $this->info("  ✓ attendance_devices: {$deleted} records deleted");
            }

            // Delete attendance locations
            if ($locationsCount > 0) {
                $deleted = DB::table('attendance_locations')->delete();
                $deletedCounts['attendance_locations'] = $deleted;
                $this->info("  ✓ attendance_locations: {$deleted} records deleted");
            }

            // Delete attendance policies if table exists
            if ($policiesCount > 0) {
                $deleted = DB::table('attendance_policies')->delete();
                $deletedCounts['attendance_policies'] = $deleted;
                $this->info("  ✓ attendance_policies: {$deleted} records deleted");
            }

            DB::commit();

            $this->newLine();
            $this->info('=== Cleanup Complete ===');
            $totalDeleted = array_sum($deletedCounts);
            $this->info("Total records deleted: {$totalDeleted}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to clean attendance data: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}





