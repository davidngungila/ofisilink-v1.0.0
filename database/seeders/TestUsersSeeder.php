<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $staffRole = Role::where('name', 'Staff')->first();
        $accountantRole = Role::where('name', 'Accountant')->first();
        $hodRole = Role::where('name', 'HOD')->first();
        $ceoRole = Role::where('name', 'CEO')->first();
        $hrRole = Role::where('name', 'HR Officer')->first();

        // Get departments
        $financeDept = Department::where('code', 'FIN')->first();
        $hrDept = Department::where('code', 'HR')->first();
        $adminDept = Department::where('code', 'ADMIN')->first();
        $itDept = Department::where('code', 'IT')->first();

        // Create Staff User
        $staffUser = User::updateOrCreate(
            ['employee_id' => 'EMP001'],
            [
                'name' => 'John Doe',
                'email' => 'staff@ofisi.com',
                'password' => Hash::make('password'),
                'phone' => '+255 123 456 789',
                'hire_date' => now()->subMonths(6),
                'is_active' => true,
            ]
        );

        // Create Accountant User
        $accountantUser = User::updateOrCreate(
            ['employee_id' => 'ACC001'],
            [
                'name' => 'Jane Smith',
                'email' => 'accountant@ofisi.com',
                'password' => Hash::make('password'),
                'phone' => '+255 123 456 790',
                'hire_date' => now()->subMonths(12),
                'is_active' => true,
            ]
        );

        // Create HOD User
        $hodUser = User::updateOrCreate(
            ['employee_id' => 'HOD001'],
            [
                'name' => 'Michael Johnson',
                'email' => 'hod@ofisi.com',
                'password' => Hash::make('password'),
                'phone' => '+255 123 456 791',
                'hire_date' => now()->subMonths(24),
                'is_active' => true,
            ]
        );

        // Create CEO User
        $ceoUser = User::updateOrCreate(
            ['employee_id' => 'CEO001'],
            [
                'name' => 'Sarah Wilson',
                'email' => 'ceo@ofisi.com',
                'password' => Hash::make('password'),
                'phone' => '+255 123 456 792',
                'hire_date' => now()->subMonths(36),
                'is_active' => true,
            ]
        );

        // Create HR Officer User
        $hrUser = User::updateOrCreate(
            ['employee_id' => 'HR001'],
            [
                'name' => 'David Brown',
                'email' => 'hr@ofisi.com',
                'password' => Hash::make('password'),
                'phone' => '+255 123 456 793',
                'hire_date' => now()->subMonths(18),
                'is_active' => true,
            ]
        );

        // Assign roles and departments
        if ($staffRole && $itDept) {
            $staffUser->roles()->syncWithoutDetaching([$staffRole->id => [
                'department_id' => $itDept->id,
                'is_active' => true,
                'assigned_at' => now(),
            ]]);
            $staffUser->update(['primary_department_id' => $itDept->id]);
            $staffUser->departments()->syncWithoutDetaching([$itDept->id => [
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => now(),
            ]]);
        }

        if ($accountantRole && $financeDept) {
            $accountantUser->roles()->syncWithoutDetaching([$accountantRole->id => [
                'department_id' => $financeDept->id,
                'is_active' => true,
                'assigned_at' => now(),
            ]]);
            $accountantUser->update(['primary_department_id' => $financeDept->id]);
            $accountantUser->departments()->syncWithoutDetaching([$financeDept->id => [
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => now(),
            ]]);
        }

        if ($hodRole && $financeDept) {
            $hodUser->roles()->syncWithoutDetaching([$hodRole->id => [
                'department_id' => $financeDept->id,
                'is_active' => true,
                'assigned_at' => now(),
            ]]);
            $hodUser->update(['primary_department_id' => $financeDept->id]);
            $hodUser->departments()->syncWithoutDetaching([$financeDept->id => [
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => now(),
            ]]);
        }

        if ($ceoRole && $adminDept) {
            $ceoUser->roles()->syncWithoutDetaching([$ceoRole->id => [
                'department_id' => $adminDept->id,
                'is_active' => true,
                'assigned_at' => now(),
            ]]);
            $ceoUser->update(['primary_department_id' => $adminDept->id]);
            $ceoUser->departments()->syncWithoutDetaching([$adminDept->id => [
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => now(),
            ]]);
        }

        if ($hrRole && $hrDept) {
            $hrUser->roles()->syncWithoutDetaching([$hrRole->id => [
                'department_id' => $hrDept->id,
                'is_active' => true,
                'assigned_at' => now(),
            ]]);
            $hrUser->update(['primary_department_id' => $hrDept->id]);
            $hrUser->departments()->syncWithoutDetaching([$hrDept->id => [
                'is_primary' => true,
                'is_active' => true,
                'joined_at' => now(),
            ]]);
        }

        $this->command->info('Test users created successfully!');
        $this->command->info('Staff: staff@ofisi.com / password');
        $this->command->info('Accountant: accountant@ofisi.com / password');
        $this->command->info('HOD: hod@ofisi.com / password');
        $this->command->info('CEO: ceo@ofisi.com / password');
        $this->command->info('HR Officer: hr@ofisi.com / password');
    }
}