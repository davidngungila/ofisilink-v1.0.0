<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SeedTestUsers extends Command
{
    protected $signature = 'users:seed-test {--count=10 : Number of test users to create}';
    protected $description = 'Create multiple test users for system testing';

    public function handle()
    {
        $count = (int) $this->option('count');
        
        if ($count < 1 || $count > 50) {
            $this->error('Count must be between 1 and 50.');
            return 1;
        }

        $this->info("Creating {$count} test users...");
        $this->newLine();

        // Get or create roles
        $roles = $this->getRoles();
        
        // Get departments
        $departments = Department::all();
        if ($departments->isEmpty()) {
            $this->warn('No departments found. Creating users without departments.');
        }

        // Default password for all test users
        $defaultPassword = 'password123';
        
        $createdUsers = [];
        
        try {
            DB::beginTransaction();

            for ($i = 1; $i <= $count; $i++) {
                // Generate unique test data
                $name = "Test User {$i}";
                $email = "testuser{$i}@ofisi.com";
                $phone = "2556" . str_pad((string)(122000000 + $i), 7, '0', STR_PAD_LEFT);
                $employeeId = "EMP" . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
                
                // Check if user already exists
                $existingUser = User::where('email', $email)->orWhere('phone', $phone)->first();
                if ($existingUser) {
                    $this->warn("User {$i} already exists: {$email}");
                    continue;
                }

                // Select random department
                $department = $departments->isNotEmpty() ? $departments->random() : null;
                
                // Create user data
                $userData = [
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($defaultPassword),
                    'phone' => $phone,
                    'employee_id' => $employeeId,
                    'primary_department_id' => $department ? $department->id : null,
                    'hire_date' => now()->subDays(rand(30, 365)),
                    'is_active' => true,
                ];
                
                // Add mobile if column exists
                if (Schema::hasColumn('users', 'mobile')) {
                    $userData['mobile'] = $phone;
                }
                
                $user = User::create($userData);

                // Create employee record
                $employee = Employee::create([
                    'user_id' => $user->id,
                    'position' => $this->getRandomPosition(),
                    'employment_type' => $this->getRandomEmploymentType(),
                    'hire_date' => $user->hire_date,
                    'salary' => rand(500000, 5000000),
                ]);

                // Assign random role (mostly Staff, some get higher roles)
                $role = $this->assignRole($user, $i, $roles);
                
                // Assign department if exists
                if ($department) {
                    $user->departments()->syncWithoutDetaching([$department->id => [
                        'joined_at' => $user->hire_date,
                        'is_primary' => true,
                    ]]);
                }

                $createdUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'employee_id' => $user->employee_id,
                    'role' => $role,
                    'department' => $department ? $department->name : 'None',
                ];

                $this->line("✓ Created: {$name} ({$email}) - Role: {$role}");
            }

            DB::commit();

            $this->newLine();
            $this->info("Successfully created " . count($createdUsers) . " test users!");
            $this->newLine();
            
            // Display summary
            $this->table(
                ['ID', 'Name', 'Email', 'Phone', 'Employee ID', 'Role', 'Department'],
                array_map(function($user) {
                    return [
                        $user['id'],
                        $user['name'],
                        $user['email'],
                        $user['phone'],
                        $user['employee_id'],
                        $user['role'],
                        $user['department'],
                    ];
                }, $createdUsers)
            );
            
            $this->newLine();
            $this->info("Login Credentials (for all users):");
            $this->line("  Password: {$defaultPassword}");
            $this->newLine();
            $this->comment("Note: After login, OTP will be sent to the registered phone number.");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error creating test users: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }

    private function getRoles()
    {
        $roleNames = ['System Admin', 'CEO', 'HOD', 'HR Officer', 'Accountant', 'Staff'];
        $roles = [];
        
        foreach ($roleNames as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $roles[$roleName] = $role;
            }
        }
        
        return $roles;
    }

    private function assignRole($user, $userIndex, $roles)
    {
        // First user gets System Admin
        if ($userIndex === 1 && isset($roles['System Admin'])) {
            $user->roles()->attach($roles['System Admin']->id);
            return 'System Admin';
        }
        
        // Second user gets CEO
        if ($userIndex === 2 && isset($roles['CEO'])) {
            $user->roles()->attach($roles['CEO']->id);
            return 'CEO';
        }
        
        // Third user gets HR Officer
        if ($userIndex === 3 && isset($roles['HR Officer'])) {
            $user->roles()->attach($roles['HR Officer']->id);
            return 'HR Officer';
        }
        
        // Fourth user gets HOD
        if ($userIndex === 4 && isset($roles['HOD'])) {
            $user->roles()->attach($roles['HOD']->id);
            return 'HOD';
        }
        
        // Fifth user gets Accountant
        if ($userIndex === 5 && isset($roles['Accountant'])) {
            $user->roles()->attach($roles['Accountant']->id);
            return 'Accountant';
        }
        
        // Randomly assign some Staff with occasional higher roles (10% chance)
        $random = rand(1, 100);
        if ($random <= 5 && isset($roles['HR Officer'])) {
            $user->roles()->attach($roles['HR Officer']->id);
            return 'HR Officer';
        } elseif ($random <= 8 && isset($roles['Accountant'])) {
            $user->roles()->attach($roles['Accountant']->id);
            return 'Accountant';
        } elseif ($random <= 10 && isset($roles['HOD'])) {
            $user->roles()->attach($roles['HOD']->id);
            return 'HOD';
        } else {
            // Default to Staff
            if (isset($roles['Staff'])) {
                $user->roles()->attach($roles['Staff']->id);
                return 'Staff';
            }
            return 'No Role';
        }
    }

    private function getRandomPosition()
    {
        $positions = [
            'Software Developer',
            'Business Analyst',
            'Project Manager',
            'Sales Executive',
            'Marketing Officer',
            'Customer Support',
            'Finance Officer',
            'Administrative Assistant',
            'Operations Manager',
            'Quality Assurance',
            'Data Analyst',
            'Graphic Designer',
        ];
        
        return $positions[array_rand($positions)];
    }

    private function getRandomEmploymentType()
    {
        $types = ['permanent', 'contract', 'temporary', 'intern'];
        return $types[array_rand($types)];
    }
}
