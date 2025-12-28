<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkSchedule;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class WorkScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Work Schedules...');

        // Get first department and first admin user for relationships
        $department = Department::where('is_active', true)->first();
        $admin = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['System Admin', 'HR Officer']);
        })->first();

        $schedules = [
            [
                'name' => 'Standard Office Hours',
                'code' => 'STD-001',
                'description' => 'Standard 8-hour work day from 8:00 AM to 5:00 PM with 1-hour lunch break',
                'department_id' => $department?->id,
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'work_hours' => 8,
                'break_duration_minutes' => 60,
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
                'late_tolerance_minutes' => 15,
                'early_leave_tolerance_minutes' => 15,
                'overtime_threshold_minutes' => 30,
                'working_days' => [1, 2, 3, 4, 5], // Monday to Friday
                'is_flexible' => false,
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
                'effective_to' => null,
                'created_by' => $admin?->id,
            ],
            [
                'name' => 'Flexible Working Hours',
                'code' => 'FLX-001',
                'description' => 'Flexible schedule allowing employees to start between 7:00 AM and 9:00 AM',
                'department_id' => $department?->id,
                'start_time' => '08:00:00',
                'end_time' => '17:00:00',
                'work_hours' => 8,
                'break_duration_minutes' => 60,
                'break_start_time' => '12:00:00',
                'break_end_time' => '13:00:00',
                'late_tolerance_minutes' => 30,
                'early_leave_tolerance_minutes' => 15,
                'overtime_threshold_minutes' => 30,
                'working_days' => [1, 2, 3, 4, 5],
                'is_flexible' => true,
                'flexible_start_min' => '07:00:00',
                'flexible_start_max' => '09:00:00',
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
                'effective_to' => null,
                'created_by' => $admin?->id,
            ],
            [
                'name' => 'Shift A - Morning',
                'code' => 'SHF-A',
                'description' => 'Morning shift from 6:00 AM to 2:00 PM',
                'department_id' => $department?->id,
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'work_hours' => 8,
                'break_duration_minutes' => 30,
                'break_start_time' => '10:00:00',
                'break_end_time' => '10:30:00',
                'late_tolerance_minutes' => 15,
                'early_leave_tolerance_minutes' => 15,
                'overtime_threshold_minutes' => 30,
                'working_days' => [1, 2, 3, 4, 5, 6], // Monday to Saturday
                'is_flexible' => false,
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
                'effective_to' => null,
                'created_by' => $admin?->id,
            ],
            [
                'name' => 'Shift B - Afternoon',
                'code' => 'SHF-B',
                'description' => 'Afternoon shift from 2:00 PM to 10:00 PM',
                'department_id' => $department?->id,
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'work_hours' => 8,
                'break_duration_minutes' => 30,
                'break_start_time' => '18:00:00',
                'break_end_time' => '18:30:00',
                'late_tolerance_minutes' => 15,
                'early_leave_tolerance_minutes' => 15,
                'overtime_threshold_minutes' => 30,
                'working_days' => [1, 2, 3, 4, 5, 6],
                'is_flexible' => false,
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
                'effective_to' => null,
                'created_by' => $admin?->id,
            ],
            [
                'name' => 'Part-Time Morning',
                'code' => 'PTM-001',
                'description' => 'Part-time schedule from 9:00 AM to 1:00 PM',
                'department_id' => $department?->id,
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'work_hours' => 4,
                'break_duration_minutes' => 0,
                'break_start_time' => null,
                'break_end_time' => null,
                'late_tolerance_minutes' => 15,
                'early_leave_tolerance_minutes' => 15,
                'overtime_threshold_minutes' => 30,
                'working_days' => [1, 2, 3, 4, 5],
                'is_flexible' => false,
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
                'effective_to' => null,
                'created_by' => $admin?->id,
            ],
        ];

        foreach ($schedules as $scheduleData) {
            // Check if schedule already exists
            $existing = WorkSchedule::where('code', $scheduleData['code'])->first();
            if (!$existing) {
                WorkSchedule::create($scheduleData);
                $this->command->info("Created schedule: {$scheduleData['name']} ({$scheduleData['code']})");
            } else {
                $this->command->warn("Schedule {$scheduleData['code']} already exists, skipping...");
            }
        }

        $this->command->info('Work Schedules seeded successfully!');
    }
}







