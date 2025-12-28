<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CleanAndAddUsersSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Cleaning existing users and employees...');
        
        // Clean up existing data
        // Note: Using DB facade for MySQL foreign key handling
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
        
        // Delete employees first (due to foreign key constraints)
        Employee::query()->delete();
        
        // Delete user roles
        DB::table('user_roles')->delete();
        
        // Delete user departments
        DB::table('user_departments')->delete();
        
        // Delete all users except system admin (if exists)
        User::where('email', '!=', 'admin@ofisilink.com')
            ->where('email', '!=', 'admin@emca.tech')
            ->delete();
        
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        $this->command->info('Existing data cleaned.');
        $this->command->info('Creating new users...');
        
        // Get roles
        $roles = [
            'Accountant' => Role::where('name', 'Accountant')->first(),
            'Business Operation' => Role::where('name', 'Staff')->first(),
            'CEO' => Role::where('name', 'CEO')->first(),
            'CTO HOD' => Role::where('name', 'HOD')->first(),
            'CTO - SACCOS CBS' => Role::where('name', 'HOD')->first(),
            'ICT Officer' => Role::where('name', 'Staff')->first(),
            'Office HR' => Role::where('name', 'HR Officer')->first(),
            'OfficE ADMIN' => Role::where('name', 'System Admin')->first(),
            'OfficE DIRECTOR' => Role::where('name', 'Director')->first(),
        ];
        
        // Get departments (create if they don't exist)
        $financeDept = Department::firstOrCreate(
            ['code' => 'FIN'],
            ['name' => 'Finance', 'description' => 'Finance Department']
        );
        
        $itDept = Department::firstOrCreate(
            ['code' => 'IT'],
            ['name' => 'Information Technology', 'description' => 'IT Department']
        );
        
        $hrDept = Department::firstOrCreate(
            ['code' => 'HR'],
            ['name' => 'Human Resources', 'description' => 'HR Department']
        );
        
        $adminDept = Department::firstOrCreate(
            ['code' => 'ADMIN'],
            ['name' => 'Administration', 'description' => 'Administration Department']
        );
        
        $operationsDept = Department::firstOrCreate(
            ['code' => 'OPS'],
            ['name' => 'Operations', 'description' => 'Business Operations Department']
        );
        
        // User data from the provided list
        $users = [
            [
                'first_name' => 'Mariana',
                'last_name' => 'Swai',
                'email' => 'mariana.swai@emca.tech',
                'title' => 'Accountant',
                'status' => 'Active',
                'role' => 'Accountant',
                'department' => $financeDept,
            ],
            [
                'first_name' => 'Neema',
                'last_name' => 'Kipokola',
                'email' => 'neema.kipokola@emca.tech',
                'title' => 'Business Operation',
                'status' => 'Active',
                'role' => 'Business Operation',
                'department' => $operationsDept,
            ],
            [
                'first_name' => 'Caroline',
                'last_name' => 'Shija',
                'email' => 'carolineshija@emca.tech',
                'title' => 'CEO',
                'status' => 'Active',
                'role' => 'CEO',
                'department' => $adminDept,
            ],
            [
                'first_name' => 'Emmanuel',
                'last_name' => 'Masaga',
                'email' => 'masaga303@emca.tech',
                'title' => 'CTO HOD',
                'status' => 'Active',
                'role' => 'CTO HOD',
                'department' => $itDept,
            ],
            [
                'first_name' => 'Paul',
                'last_name' => 'Mathu',
                'email' => 'paul.mathu@emca.tech',
                'title' => 'CTO - SACCOS CBS',
                'status' => 'Active',
                'role' => 'CTO - SACCOS CBS',
                'department' => $itDept,
            ],
            [
                'first_name' => 'Ally',
                'last_name' => 'Ally',
                'email' => 'ally.ally@emca.tech',
                'title' => 'ICT Officer',
                'status' => 'Active',
                'role' => 'ICT Officer',
                'department' => $itDept,
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Ngungila',
                'email' => 'david.ngungila@emca.tech',
                'title' => 'ICT Officer',
                'status' => 'Active',
                'role' => 'ICT Officer',
                'department' => $itDept,
            ],
            [
                'first_name' => 'Hassani',
                'last_name' => 'Saidi',
                'email' => 'hassani.saidi@emca.tech',
                'title' => 'ICT Officer',
                'status' => 'Active',
                'role' => 'ICT Officer',
                'department' => $itDept,
            ],
            [
                'first_name' => 'Joseph',
                'last_name' => 'Wawa',
                'email' => 'joseph.wawa@emca.tech',
                'title' => 'ICT Officer',
                'status' => 'Active',
                'role' => 'ICT Officer',
                'department' => $itDept,
            ],
            [
                'first_name' => 'Ofeni',
                'last_name' => 'Fred',
                'email' => 'ofeni.fred@emca.tech',
                'title' => 'ICT Officer',
                'status' => 'Active',
                'role' => 'ICT Officer',
                'department' => $itDept,
            ],
            [
                'first_name' => 'Abia (Naomi)',
                'last_name' => 'Habari',
                'email' => 'abia.habari@emca.tech',
                'title' => 'Office HR',
                'status' => 'Active',
                'role' => 'Office HR',
                'department' => $hrDept,
            ],
            [
                'first_name' => 'EmCa',
                'last_name' => 'Techonologies',
                'email' => 'emca@emca.tech',
                'title' => 'OfficE ADMIN',
                'status' => 'Active',
                'role' => 'OfficE ADMIN',
                'department' => $adminDept,
            ],
            [
                'first_name' => 'Internship',
                'last_name' => 'Opportunity',
                'email' => 'intern@emca.tech',
                'title' => 'OfficE DIRECTOR',
                'status' => 'Active',
                'role' => 'OfficE DIRECTOR',
                'department' => $adminDept,
            ],
        ];
        
        $createdCount = 0;
        
        foreach ($users as $index => $userData) {
            try {
                // Create user
                $user = User::create([
                    'name' => $userData['first_name'] . ' ' . $userData['last_name'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password'), // Default password - should be changed on first login
                    'employee_id' => 'EMP' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'phone' => '0622239304',
                    'mobile' => '0622239304',
                    'hire_date' => now()->subMonths(rand(1, 24)),
                    'is_active' => $userData['status'] === 'Active',
                    'primary_department_id' => $userData['department']->id,
                ]);
                
                // Assign role
                $role = $roles[$userData['role']];
                if ($role) {
                    $user->roles()->attach($role->id, [
                        'department_id' => $userData['department']->id,
                        'is_active' => true,
                        'assigned_at' => now(),
                    ]);
                }
                
                // Assign department
                $user->departments()->attach($userData['department']->id, [
                    'is_primary' => true,
                    'is_active' => true,
                    'joined_at' => now(),
                ]);
                
                // Create employee record
                Employee::create([
                    'user_id' => $user->id,
                    'position' => $userData['title'],
                    'employment_type' => 'permanent',
                    'hire_date' => $user->hire_date,
                    'salary' => 0, // Set salary as needed
                ]);
                
                $createdCount++;
                $this->command->info("✓ Created: {$user->name} ({$user->email}) - {$userData['title']}");
                
            } catch (\Exception $e) {
                $this->command->error("✗ Failed to create user {$userData['email']}: " . $e->getMessage());
            }
        }
        
        $this->command->info("\n==========================================");
        $this->command->info("Successfully created {$createdCount} users!");
        $this->command->info("==========================================");
        $this->command->info("\nDefault password for all users: password");
        $this->command->info("Please change passwords on first login.");
        $this->command->info("\n");
    }
}

