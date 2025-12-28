<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeder.
     * Creates employee records for all users who don't have one.
     */
    public function run(): void
    {
        // Get all users who don't have employee records (including inactive)
        $users = User::whereDoesntHave('employee')->get();
        
        $count = 0;
        foreach ($users as $user) {
            try {
                // Create employee record for each user
                Employee::create([
                    'user_id' => $user->id,
                    'position' => $this->getPositionForRole($user),
                    'employment_type' => 'permanent',
                    'hire_date' => $user->hire_date ?? now()->subMonths(rand(1, 24)),
                    'salary' => $this->getSalaryForRole($user),
                    'tin_number' => 'TIN' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
                    'nssf_number' => 'NSSF' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
                    'nhif_number' => 'NHIF' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
                    'heslb_number' => $user->id % 3 == 0 ? 'HESLB' . str_pad($user->id, 8, '0', STR_PAD_LEFT) : null,
                    'has_student_loan' => $user->id % 3 == 0,
                ]);
                $count++;
            } catch (\Exception $e) {
                $this->command->warn("Failed to create employee record for user ID {$user->id}: " . $e->getMessage());
            }
        }
        
        $this->command->info("Employee records created for {$count} out of {$users->count()} users without employee records.");
        
        if ($count < $users->count()) {
            $this->command->warn("Some employee records could not be created. Please check the errors above.");
        }
    }
    
    private function getPositionForRole($user)
    {
        if ($user->hasRole('System Admin')) {
            return 'System Administrator';
        } elseif ($user->hasRole('CEO')) {
            return 'Chief Executive Officer';
        } elseif ($user->hasRole('HOD')) {
            return 'Head of Department';
        } elseif ($user->hasRole('HR Officer')) {
            return 'Human Resources Officer';
        } elseif ($user->hasRole('Accountant')) {
            return 'Accountant';
        } else {
            return 'Staff Member';
        }
    }
    
    private function getSalaryForRole($user)
    {
        if ($user->hasRole('System Admin')) {
            return 3000000; // 3M TZS
        } elseif ($user->hasRole('CEO')) {
            return 5000000; // 5M TZS
        } elseif ($user->hasRole('HOD')) {
            return 2500000; // 2.5M TZS
        } elseif ($user->hasRole('HR Officer')) {
            return 2000000; // 2M TZS
        } elseif ($user->hasRole('Accountant')) {
            return 1800000; // 1.8M TZS
        } else {
            return 800000; // 800K TZS
        }
    }
}