<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@ofisi.com'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'employee_id' => 'ADMIN001',
                'phone' => '+1234567890',
                'hire_date' => now(),
                'is_active' => true,
            ]
        );

        // Get System Admin role
        $systemAdminRole = Role::where('name', 'System Admin')->first();
        
        // Get Administration department
        $adminDepartment = Department::where('code', 'ADMIN')->first();

        // Assign System Admin role to super admin
        if ($systemAdminRole && $adminDepartment) {
            $superAdmin->roles()->syncWithoutDetaching([$systemAdminRole->id => [
                'department_id' => $adminDepartment->id,
                'is_active' => true,
                'assigned_at' => now(),
            ]]);

            // Set primary department
            $superAdmin->update(['primary_department_id' => $adminDepartment->id]);

            // Add to department
            $superAdmin->departments()->syncWithoutDetaching([$adminDepartment->id => [
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => now(),
            ]]);
        }
    }
}