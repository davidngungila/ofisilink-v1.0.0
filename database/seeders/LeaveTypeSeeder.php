<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LeaveType;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'description' => 'Regular annual vacation leave',
                'max_days' => 28,
                'requires_approval' => true,
            ],
            [
                'name' => 'Sick Leave',
                'description' => 'Medical leave for illness or injury',
                'max_days' => 30,
                'requires_approval' => true,
            ],
            [
                'name' => 'Maternity Leave',
                'description' => 'Leave for new mothers',
                'max_days' => 90,
                'requires_approval' => true,
            ],
            [
                'name' => 'Paternity Leave',
                'description' => 'Leave for new fathers',
                'max_days' => 14,
                'requires_approval' => true,
            ],
            [
                'name' => 'Emergency Leave',
                'description' => 'Leave for family emergencies',
                'max_days' => 7,
                'requires_approval' => true,
            ],
            [
                'name' => 'Study Leave',
                'description' => 'Leave for educational purposes',
                'max_days' => 30,
                'requires_approval' => true,
            ],
            [
                'name' => 'Compassionate Leave',
                'description' => 'Leave for bereavement',
                'max_days' => 5,
                'requires_approval' => true,
            ],
            [
                'name' => 'Personal Leave',
                'description' => 'Personal matters leave',
                'max_days' => 3,
                'requires_approval' => true,
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::updateOrCreate(
                ['name' => $leaveType['name']],
                $leaveType
            );
        }

        $this->command->info('Leave types seeded successfully!');
    }
}