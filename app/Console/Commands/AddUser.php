<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddUser extends Command
{
    protected $signature = 'user:add 
                            {phone : Phone number}
                            {--name= : User name}
                            {--email= : Email address}
                            {--password= : Password (min 8 characters)}
                            {--employee-id= : Employee ID}
                            {--department-id= : Department ID}';

    protected $description = 'Add a new user to the database';

    public function handle()
    {
        $phone = $this->argument('phone');
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');
        $employeeId = $this->option('employee-id');
        $departmentId = $this->option('department-id');

        // Validate phone number format
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (empty($phone) || strlen($phone) < 9) {
            $this->error('Invalid phone number. Must be at least 9 digits.');
            return 1;
        }

        // Check if user with this phone already exists
        $existingUser = User::where('phone', $phone)->first();
        
        // Also check mobile if column exists
        if (Schema::hasColumn('users', 'mobile')) {
            $mobileUser = User::where('mobile', $phone)->first();
            if ($mobileUser && !$existingUser) {
                $existingUser = $mobileUser;
            }
        }
        
        if ($existingUser) {
            $this->error("User with phone number '{$phone}' already exists (ID: {$existingUser->id}, Email: {$existingUser->email})");
            return 1;
        }

        // Get name if not provided
        if (!$name) {
            $name = $this->ask('Enter user name');
            if (empty($name)) {
                $this->error('Name is required.');
                return 1;
            }
        }

        // Get email if not provided
        if (!$email) {
            $email = $this->ask('Enter email address');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Valid email address is required.');
                return 1;
            }
        }

        // Check if email already exists
        $emailExists = User::where('email', $email)->first();
        if ($emailExists) {
            $this->error("User with email '{$email}' already exists (ID: {$emailExists->id})");
            return 1;
        }

        // Get password if not provided
        if (!$password) {
            $password = $this->secret('Enter password (min 8 characters)');
            $confirmPassword = $this->secret('Confirm password');
            
            if ($password !== $confirmPassword) {
                $this->error('Passwords do not match.');
                return 1;
            }
        }

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return 1;
        }

        try {
            DB::beginTransaction();

            // Create user data
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'phone' => $phone,
                'employee_id' => $employeeId,
                'primary_department_id' => $departmentId,
                'hire_date' => now(),
                'is_active' => true,
            ];
            
            // Add mobile if column exists
            if (Schema::hasColumn('users', 'mobile')) {
                $userData['mobile'] = $phone;
            }
            
            $user = User::create($userData);

            // Create employee record (one-to-one relationship)
            $employee = Employee::create([
                'user_id' => $user->id,
                'position' => 'Staff Member',
                'employment_type' => 'permanent',
                'hire_date' => now(),
                'salary' => 0,
            ]);

            DB::commit();

            $this->info("User created successfully!");
            $this->line("  ID: {$user->id}");
            $this->line("  Name: {$user->name}");
            $this->line("  Email: {$user->email}");
            $this->line("  Phone: {$user->phone}");
            $this->line("  Employee ID: " . ($user->employee_id ?? 'Not set'));
            $this->line("  Active: " . ($user->is_active ? 'Yes' : 'No'));
            $this->line("");
            $this->line("User can now login with:");
            $this->line("  Email: {$user->email}");
            $this->line("  Password: [the password you set]");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error creating user: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}
