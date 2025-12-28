<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendancePolicy;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class AttendancePolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Attendance Policies...');

        // Get first department and first admin user for relationships
        $department = Department::where('is_active', true)->first();
        $admin = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['System Admin', 'HR Officer']);
        })->first();

        $policies = [
            [
                'name' => 'Standard Attendance Policy',
                'code' => 'POL-STD-001',
                'description' => 'Standard attendance policy for all employees with biometric verification',
                'department_id' => $department?->id,
                'require_approval_for_late' => false,
                'require_approval_for_early_leave' => true,
                'require_approval_for_overtime' => true,
                'allow_remote_attendance' => false,
                'max_remote_days_per_month' => null,
                'auto_approve_verified' => true,
                'require_photo_for_manual' => true,
                'require_location_for_mobile' => true,
                'max_late_minutes_per_month' => 120,
                'max_early_leave_minutes_per_month' => 60,
                'allowed_attendance_methods' => ['biometric', 'fingerprint', 'face_recognition'],
                'penalty_rules' => [
                    'late_penalty' => [
                        'after_minutes' => 15,
                        'deduction_per_minute' => 0.5,
                        'max_deduction_per_day' => 2
                    ],
                    'absence_penalty' => [
                        'deduction_per_day' => 1,
                        'require_medical_certificate' => true
                    ]
                ],
                'reward_rules' => [
                    'perfect_attendance_bonus' => [
                        'monthly' => 5000,
                        'quarterly' => 15000,
                        'yearly' => 50000
                    ],
                    'early_arrival_bonus' => [
                        'minutes_before' => 15,
                        'bonus_per_day' => 500
                    ]
                ],
                'notification_settings' => [
                    'notify_on_late' => true,
                    'notify_on_absence' => true,
                    'notify_on_overtime' => true,
                    'notify_hr_on_manual' => true
                ],
                'approval_workflow' => [
                    'late_approval' => ['supervisor', 'hr'],
                    'early_leave_approval' => ['supervisor', 'hr'],
                    'overtime_approval' => ['supervisor', 'hr', 'ceo']
                ],
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
                'effective_to' => null,
                'created_by' => $admin?->id,
            ],
            [
                'name' => 'Flexible Work Policy',
                'code' => 'POL-FLX-001',
                'description' => 'Policy for employees with flexible working arrangements',
                'department_id' => $department?->id,
                'require_approval_for_late' => false,
                'require_approval_for_early_leave' => false,
                'require_approval_for_overtime' => true,
                'allow_remote_attendance' => true,
                'max_remote_days_per_month' => 10,
                'auto_approve_verified' => true,
                'require_photo_for_manual' => false,
                'require_location_for_mobile' => false,
                'max_late_minutes_per_month' => 300,
                'max_early_leave_minutes_per_month' => 300,
                'allowed_attendance_methods' => ['biometric', 'fingerprint', 'mobile_app', 'manual'],
                'penalty_rules' => [
                    'late_penalty' => [
                        'after_minutes' => 30,
                        'deduction_per_minute' => 0.3,
                        'max_deduction_per_day' => 1.5
                    ]
                ],
                'reward_rules' => [
                    'perfect_attendance_bonus' => [
                        'monthly' => 3000,
                        'quarterly' => 10000
                    ]
                ],
                'notification_settings' => [
                    'notify_on_late' => false,
                    'notify_on_absence' => true,
                    'notify_on_overtime' => true
                ],
                'approval_workflow' => [
                    'late_approval' => [],
                    'early_leave_approval' => [],
                    'overtime_approval' => ['supervisor']
                ],
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
                'effective_to' => null,
                'created_by' => $admin?->id,
            ],
            [
                'name' => 'Shift Work Policy',
                'code' => 'POL-SHF-001',
                'description' => 'Policy for shift workers with strict attendance requirements',
                'department_id' => $department?->id,
                'require_approval_for_late' => true,
                'require_approval_for_early_leave' => true,
                'require_approval_for_overtime' => true,
                'allow_remote_attendance' => false,
                'max_remote_days_per_month' => null,
                'auto_approve_verified' => true,
                'require_photo_for_manual' => true,
                'require_location_for_mobile' => true,
                'max_late_minutes_per_month' => 60,
                'max_early_leave_minutes_per_month' => 30,
                'allowed_attendance_methods' => ['biometric', 'fingerprint', 'rfid'],
                'penalty_rules' => [
                    'late_penalty' => [
                        'after_minutes' => 5,
                        'deduction_per_minute' => 1,
                        'max_deduction_per_day' => 4
                    ],
                    'absence_penalty' => [
                        'deduction_per_day' => 2,
                        'require_medical_certificate' => true,
                        'three_absences_warning' => true
                    ]
                ],
                'reward_rules' => [
                    'perfect_attendance_bonus' => [
                        'monthly' => 10000,
                        'quarterly' => 30000,
                        'yearly' => 100000
                    ]
                ],
                'notification_settings' => [
                    'notify_on_late' => true,
                    'notify_on_absence' => true,
                    'notify_on_overtime' => true,
                    'notify_hr_on_manual' => true,
                    'notify_supervisor_on_late' => true
                ],
                'approval_workflow' => [
                    'late_approval' => ['supervisor', 'hr', 'manager'],
                    'early_leave_approval' => ['supervisor', 'hr'],
                    'overtime_approval' => ['supervisor', 'hr', 'manager', 'ceo']
                ],
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
                'effective_to' => null,
                'created_by' => $admin?->id,
            ],
            [
                'name' => 'Remote Work Policy',
                'code' => 'POL-RMT-001',
                'description' => 'Policy for remote workers with location verification',
                'department_id' => $department?->id,
                'require_approval_for_late' => false,
                'require_approval_for_early_leave' => false,
                'require_approval_for_overtime' => false,
                'allow_remote_attendance' => true,
                'max_remote_days_per_month' => 20,
                'auto_approve_verified' => true,
                'require_photo_for_manual' => true,
                'require_location_for_mobile' => true,
                'max_late_minutes_per_month' => 180,
                'max_early_leave_minutes_per_month' => 180,
                'allowed_attendance_methods' => ['mobile_app', 'manual', 'biometric'],
                'penalty_rules' => [
                    'late_penalty' => [
                        'after_minutes' => 30,
                        'deduction_per_minute' => 0.2,
                        'max_deduction_per_day' => 1
                    ]
                ],
                'reward_rules' => [
                    'perfect_attendance_bonus' => [
                        'monthly' => 4000
                    ]
                ],
                'notification_settings' => [
                    'notify_on_late' => false,
                    'notify_on_absence' => true,
                    'notify_on_overtime' => false
                ],
                'approval_workflow' => [
                    'late_approval' => [],
                    'early_leave_approval' => [],
                    'overtime_approval' => []
                ],
                'is_active' => true,
                'effective_from' => Carbon::now()->startOfYear(),
                'effective_to' => null,
                'created_by' => $admin?->id,
            ],
        ];

        foreach ($policies as $policyData) {
            // Check if policy already exists
            $existing = AttendancePolicy::where('code', $policyData['code'])->first();
            if (!$existing) {
                AttendancePolicy::create($policyData);
                $this->command->info("Created policy: {$policyData['name']} ({$policyData['code']})");
            } else {
                $this->command->warn("Policy {$policyData['code']} already exists, skipping...");
            }
        }

        $this->command->info('Attendance Policies seeded successfully!');
    }
}







