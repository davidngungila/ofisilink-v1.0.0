<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class EmcaUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get departments
        $adminDept = Department::where('code', 'ADMIN')->first();
        $defaultDept = Department::first(); // Fallback to first department if ADMIN doesn't exist

        // Get System Admin role for admin user
        $systemAdminRole = Role::where('name', 'System Admin')->first();
        $staffRole = Role::where('name', 'Staff')->first();

        $users = [
            [
                'name' => 'Mariana Swai',
                'email' => 'mariana.swai@emca.tech',
                'employee_id' => 'EMP001',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2024-06-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'Neema Kipokola',
                'email' => 'neema.kipokola@emca.tech',
                'employee_id' => 'EMP002',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2024-02-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'Caroline Shija',
                'email' => 'carolineshija@emca.tech',
                'employee_id' => 'EMP003',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2024-10-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'Emmanuel Masaga',
                'email' => 'masaga303@emca.tech',
                'employee_id' => 'EMP004',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2024-08-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'Paul Mathu',
                'email' => 'paul.mathu@emca.tech',
                'employee_id' => 'EMP005',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2025-08-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'Ally Ally',
                'email' => 'ally.ally@emca.tech',
                'employee_id' => 'EMP006',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2025-03-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'David Ngungila',
                'email' => 'david.ngungila@emca.tech',
                'employee_id' => 'EMP007',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2025-04-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'Hassani Saidi',
                'email' => 'hassani.saidi@emca.tech',
                'employee_id' => 'EMP008',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2025-05-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'Joseph Wawa',
                'email' => 'joseph.wawa@emca.tech',
                'employee_id' => 'EMP009',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2024-10-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => false, // Inactive user
            ],
            [
                'name' => 'Ofeni Fred',
                'email' => 'ofeni.fred@emca.tech',
                'employee_id' => 'EMP010',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2024-04-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'Abia (Naomi) Habari',
                'email' => 'abia.habari@emca.tech',
                'employee_id' => 'EMP011',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2025-02-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
            [
                'name' => 'EmCa Techonologies',
                'email' => 'emca@emca.tech',
                'employee_id' => 'EMP012',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2025-01-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
                'photo' => '1763652870_45.png',
                'marital_status' => 'single',
                'date_of_birth' => '2025-11-03',
                'gender' => 'Other',
                'nationality' => 'Tanzania',
                'address' => 'Moshi Kilimanjaro',
            ],
            [
                'name' => 'Internship Opportunity',
                'email' => 'intern@emca.tech',
                'employee_id' => 'EMP013',
                'phone' => '255622239304',
                'mobile' => '0622239304',
                'hire_date' => '2024-04-20',
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 33,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            // Set default password
            $password = Hash::make('password');
            
            // Check if user exists
            $user = User::where('employee_id', $userData['employee_id'])->first();
            
            if ($user) {
                // Update existing user but preserve password if already set
                $userData['password'] = $user->password; // Keep existing password
                $user->update($userData);
            } else {
                // Create new user with default password
                $userData['password'] = $password;
                $user = User::create($userData);
            }

            // Assign role and department
            $deptId = $userData['primary_department_id'];
            
            if ($staffRole && $deptId) {
                $user->roles()->syncWithoutDetaching([$staffRole->id => [
                    'department_id' => $deptId,
                    'is_active' => true,
                    'assigned_at' => now(),
                ]]);
                
                $user->departments()->syncWithoutDetaching([$deptId => [
                    'is_primary' => true,
                    'is_active' => true,
                    'joined_at' => $user->hire_date ?? now(),
                ]]);
            }
        }

        // Handle Super Administrator separately
        $superAdmin = User::updateOrCreate(
            ['employee_id' => 'ADMIN001'],
            [
                'name' => 'Super Administrator',
                'email' => 'admin@ofisi.com',
                'password' => Hash::make('password'),
                'phone' => '2551234567890',
                'hire_date' => now(),
                'primary_department_id' => $adminDept->id ?? $defaultDept->id ?? 34,
                'is_active' => true,
            ]
        );

        // Assign System Admin role
        if ($systemAdminRole && $superAdmin->primary_department_id) {
            $superAdmin->roles()->syncWithoutDetaching([$systemAdminRole->id => [
                'department_id' => $superAdmin->primary_department_id,
                'is_active' => true,
                'assigned_at' => now(),
            ]]);
            
            $superAdmin->departments()->syncWithoutDetaching([$superAdmin->primary_department_id => [
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => $superAdmin->hire_date ?? now(),
            ]]);
        }

        $this->command->info('EmCa users seeded successfully!');
        $this->command->info('Total users: ' . count($users) . ' + 1 Super Admin');
        $this->command->info('Default password for all users: password');
    }
}





