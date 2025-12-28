<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CleanHRModuleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning all HR Module data...');
        
        // Disable foreign key checks for MySQL
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        $deletedCounts = [];
        
        // ============================================
        // LEAVE MANAGEMENT CLEANUP
        // ============================================
        $this->command->info("\n--- Leave Management Cleanup ---");
        
        // 1. Delete Leave Documents (depends on Leave Requests)
        $this->command->info('Deleting Leave Documents...');
        if (DB::getSchemaBuilder()->hasTable('leave_documents')) {
            $deletedCounts['leave_documents'] = DB::table('leave_documents')->count();
            DB::table('leave_documents')->delete();
        }
        
        // 2. Delete Leave Dependents (depends on Leave Requests)
        $this->command->info('Deleting Leave Dependents...');
        if (DB::getSchemaBuilder()->hasTable('leave_dependents')) {
            $deletedCounts['leave_dependents'] = DB::table('leave_dependents')->count();
            DB::table('leave_dependents')->delete();
        }
        
        // 3. Delete Leave Recommendations (depends on Leave Requests)
        $this->command->info('Deleting Leave Recommendations...');
        if (DB::getSchemaBuilder()->hasTable('leave_recommendations')) {
            $deletedCounts['leave_recommendations'] = DB::table('leave_recommendations')->count();
            DB::table('leave_recommendations')->delete();
        }
        
        // 4. Delete Leave Balances
        $this->command->info('Deleting Leave Balances...');
        if (DB::getSchemaBuilder()->hasTable('leave_balances')) {
            $deletedCounts['leave_balances'] = DB::table('leave_balances')->count();
            DB::table('leave_balances')->delete();
        }
        
        // 5. Delete Leave Requests
        $this->command->info('Deleting Leave Requests...');
        if (DB::getSchemaBuilder()->hasTable('leave_requests')) {
            $deletedCounts['leave_requests'] = DB::table('leave_requests')->count();
            DB::table('leave_requests')->delete();
        }
        
        // 6. Delete Leave Types (only non-system ones)
        $this->command->info('Deleting Leave Types (non-system)...');
        if (DB::getSchemaBuilder()->hasTable('leave_types')) {
            if (DB::getSchemaBuilder()->hasColumn('leave_types', 'is_system')) {
                $deletedCounts['leave_types'] = DB::table('leave_types')
                    ->where('is_system', false)
                    ->count();
                DB::table('leave_types')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['leave_types'] = DB::table('leave_types')->count();
                DB::table('leave_types')->delete();
            }
        }
        
        // ============================================
        // ATTENDANCE CLEANUP
        // ============================================
        $this->command->info("\n--- Attendance Cleanup ---");
        
        // 7. Delete Attendance Records
        $this->command->info('Deleting Attendance Records...');
        if (DB::getSchemaBuilder()->hasTable('attendances')) {
            $deletedCounts['attendances'] = DB::table('attendances')->count();
            DB::table('attendances')->delete();
        }
        
        // 8. Delete Attendance Devices
        $this->command->info('Deleting Attendance Devices...');
        if (DB::getSchemaBuilder()->hasTable('attendance_devices')) {
            $deletedCounts['attendance_devices'] = DB::table('attendance_devices')->count();
            DB::table('attendance_devices')->delete();
        }
        
        // 9. Delete Attendance Locations
        $this->command->info('Deleting Attendance Locations...');
        if (DB::getSchemaBuilder()->hasTable('attendance_locations')) {
            $deletedCounts['attendance_locations'] = DB::table('attendance_locations')->count();
            DB::table('attendance_locations')->delete();
        }
        
        // 10. Delete Attendance Policies
        $this->command->info('Deleting Attendance Policies...');
        if (DB::getSchemaBuilder()->hasTable('attendance_policies')) {
            $deletedCounts['attendance_policies'] = DB::table('attendance_policies')->count();
            DB::table('attendance_policies')->delete();
        }
        
        // 11. Delete Work Schedules (if exists)
        $this->command->info('Deleting Work Schedules...');
        if (DB::getSchemaBuilder()->hasTable('work_schedules')) {
            $deletedCounts['work_schedules'] = DB::table('work_schedules')->count();
            DB::table('work_schedules')->delete();
        }
        
        // ============================================
        // ASSESSMENTS CLEANUP
        // ============================================
        $this->command->info("\n--- Assessments Cleanup ---");
        
        // 12. Delete Assessment Progress Reports (depends on Assessment Activities)
        $this->command->info('Deleting Assessment Progress Reports...');
        if (DB::getSchemaBuilder()->hasTable('assessment_progress_reports')) {
            $deletedCounts['assessment_progress_reports'] = DB::table('assessment_progress_reports')->count();
            DB::table('assessment_progress_reports')->delete();
        }
        
        // 13. Delete Assessment Activities (depends on Assessments)
        $this->command->info('Deleting Assessment Activities...');
        if (DB::getSchemaBuilder()->hasTable('assessment_activities')) {
            $deletedCounts['assessment_activities'] = DB::table('assessment_activities')->count();
            DB::table('assessment_activities')->delete();
        }
        
        // 14. Delete Assessments
        $this->command->info('Deleting Assessments...');
        if (DB::getSchemaBuilder()->hasTable('assessments')) {
            $deletedCounts['assessments'] = DB::table('assessments')->count();
            DB::table('assessments')->delete();
        }
        
        // ============================================
        // SICK SHEETS CLEANUP
        // ============================================
        $this->command->info("\n--- Sick Sheets Cleanup ---");
        
        // 15. Delete Sick Sheets
        $this->command->info('Deleting Sick Sheets...');
        if (DB::getSchemaBuilder()->hasTable('sick_sheets')) {
            $deletedCounts['sick_sheets'] = DB::table('sick_sheets')->count();
            DB::table('sick_sheets')->delete();
        }
        
        // ============================================
        // PERMISSIONS CLEANUP
        // ============================================
        $this->command->info("\n--- Permissions Cleanup ---");
        
        // 16. Delete Permission Requests
        $this->command->info('Deleting Permission Requests...');
        if (DB::getSchemaBuilder()->hasTable('permission_requests')) {
            $deletedCounts['permission_requests'] = DB::table('permission_requests')->count();
            DB::table('permission_requests')->delete();
        }
        
        // ============================================
        // PAYROLL CLEANUP
        // ============================================
        $this->command->info("\n--- Payroll Cleanup ---");
        
        // 17. Delete Payroll Items (depends on Payrolls)
        $this->command->info('Deleting Payroll Items...');
        if (DB::getSchemaBuilder()->hasTable('payroll_items')) {
            $deletedCounts['payroll_items'] = DB::table('payroll_items')->count();
            DB::table('payroll_items')->delete();
        }
        
        // 18. Delete Payrolls
        $this->command->info('Deleting Payrolls...');
        if (DB::getSchemaBuilder()->hasTable('payrolls')) {
            $deletedCounts['payrolls'] = DB::table('payrolls')->count();
            DB::table('payrolls')->delete();
        }
        
        // ============================================
        // RECRUITMENT CLEANUP
        // ============================================
        $this->command->info("\n--- Recruitment Cleanup ---");
        
        // 19. Delete Application Documents (depends on Job Applications)
        $this->command->info('Deleting Application Documents...');
        if (DB::getSchemaBuilder()->hasTable('application_documents')) {
            $deletedCounts['application_documents'] = DB::table('application_documents')->count();
            DB::table('application_documents')->delete();
        }
        
        // 20. Delete Application Evaluations (depends on Job Applications)
        $this->command->info('Deleting Application Evaluations...');
        if (DB::getSchemaBuilder()->hasTable('application_evaluations')) {
            $deletedCounts['application_evaluations'] = DB::table('application_evaluations')->count();
            DB::table('application_evaluations')->delete();
        }
        
        // 21. Delete Application Histories (depends on Job Applications)
        $this->command->info('Deleting Application Histories...');
        if (DB::getSchemaBuilder()->hasTable('application_histories')) {
            $deletedCounts['application_histories'] = DB::table('application_histories')->count();
            DB::table('application_histories')->delete();
        }
        
        // 22. Delete Interview Schedules (depends on Job Applications)
        $this->command->info('Deleting Interview Schedules...');
        if (DB::getSchemaBuilder()->hasTable('interview_schedules')) {
            $deletedCounts['interview_schedules'] = DB::table('interview_schedules')->count();
            DB::table('interview_schedules')->delete();
        }
        
        // 23. Delete Job Applications (depends on Recruitment Jobs)
        $this->command->info('Deleting Job Applications...');
        if (DB::getSchemaBuilder()->hasTable('job_applications')) {
            $deletedCounts['job_applications'] = DB::table('job_applications')->count();
            DB::table('job_applications')->delete();
        }
        
        // 24. Delete Recruitment Jobs
        $this->command->info('Deleting Recruitment Jobs...');
        if (DB::getSchemaBuilder()->hasTable('recruitment_jobs')) {
            $deletedCounts['recruitment_jobs'] = DB::table('recruitment_jobs')->count();
            DB::table('recruitment_jobs')->delete();
        }
        
        // ============================================
        // DEPARTMENTS & POSITIONS CLEANUP
        // ============================================
        $this->command->info("\n--- Departments & Positions Cleanup ---");
        
        // 25. Delete User-Department relationships (keep users, just remove department associations)
        $this->command->info('Deleting User-Department relationships...');
        if (DB::getSchemaBuilder()->hasTable('user_departments')) {
            $deletedCounts['user_departments'] = DB::table('user_departments')->count();
            DB::table('user_departments')->delete();
        }
        
        // 26. Delete Positions (only non-system ones)
        $this->command->info('Deleting Positions (non-system)...');
        if (DB::getSchemaBuilder()->hasTable('positions')) {
            if (DB::getSchemaBuilder()->hasColumn('positions', 'is_system')) {
                $deletedCounts['positions'] = DB::table('positions')
                    ->where('is_system', false)
                    ->count();
                DB::table('positions')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['positions'] = DB::table('positions')->count();
                DB::table('positions')->delete();
            }
        }
        
        // 27. Delete Departments (only non-system ones)
        $this->command->info('Deleting Departments (non-system)...');
        if (DB::getSchemaBuilder()->hasTable('departments')) {
            if (DB::getSchemaBuilder()->hasColumn('departments', 'is_system')) {
                $deletedCounts['departments'] = DB::table('departments')
                    ->where('is_system', false)
                    ->count();
                DB::table('departments')
                    ->where('is_system', false)
                    ->delete();
            } else {
                $deletedCounts['departments'] = DB::table('departments')->count();
                DB::table('departments')->delete();
            }
        }
        
        // Re-enable foreign key checks
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        $this->command->info("\n==========================================");
        $this->command->info("Successfully cleaned all HR Module data!");
        $this->command->info("==========================================");
        $this->command->info("\nDeleted Records:");
        
        $leaveTotal = 0;
        $attendanceTotal = 0;
        $assessmentTotal = 0;
        $otherTotal = 0;
        
        $leaveTables = ['leave_documents', 'leave_dependents', 'leave_recommendations', 'leave_balances', 'leave_requests', 'leave_types'];
        $attendanceTables = ['attendances', 'attendance_devices', 'attendance_locations', 'attendance_policies', 'work_schedules'];
        $assessmentTables = ['assessment_progress_reports', 'assessment_activities', 'assessments'];
        $otherTables = ['sick_sheets', 'permission_requests', 'payroll_items', 'payrolls', 'application_documents', 'application_evaluations', 'application_histories', 'interview_schedules', 'job_applications', 'recruitment_jobs', 'user_departments', 'positions', 'departments'];
        
        $this->command->info("\nLeave Management:");
        foreach ($leaveTables as $table) {
            $count = $deletedCounts[$table] ?? 0;
            $leaveTotal += $count;
            $this->command->info("  - {$table}: {$count}");
        }
        
        $this->command->info("\nAttendance:");
        foreach ($attendanceTables as $table) {
            $count = $deletedCounts[$table] ?? 0;
            $attendanceTotal += $count;
            $this->command->info("  - {$table}: {$count}");
        }
        
        $this->command->info("\nAssessments:");
        foreach ($assessmentTables as $table) {
            $count = $deletedCounts[$table] ?? 0;
            $assessmentTotal += $count;
            $this->command->info("  - {$table}: {$count}");
        }
        
        $this->command->info("\nOther HR Data:");
        foreach ($otherTables as $table) {
            $count = $deletedCounts[$table] ?? 0;
            $otherTotal += $count;
            $this->command->info("  - {$table}: {$count}");
        }
        
        $totalDeleted = array_sum($deletedCounts);
        $this->command->info("\nSummary:");
        $this->command->info("  - Leave Management: {$leaveTotal}");
        $this->command->info("  - Attendance: {$attendanceTotal}");
        $this->command->info("  - Assessments: {$assessmentTotal}");
        $this->command->info("  - Other HR Data: {$otherTotal}");
        $this->command->info("  - Grand Total: {$totalDeleted}");
        $this->command->info("\n");
    }
}

