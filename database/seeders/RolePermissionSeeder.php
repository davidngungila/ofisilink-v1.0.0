<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            // Dashboard
            ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'module' => 'Dashboard'],
            
            // User Management
            ['name' => 'users.view', 'display_name' => 'View Users', 'module' => 'User Management'],
            ['name' => 'users.create', 'display_name' => 'Create Users', 'module' => 'User Management'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users', 'module' => 'User Management'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users', 'module' => 'User Management'],
            
            // Role Management
            ['name' => 'roles.view', 'display_name' => 'View Roles', 'module' => 'Role Management'],
            ['name' => 'roles.create', 'display_name' => 'Create Roles', 'module' => 'Role Management'],
            ['name' => 'roles.edit', 'display_name' => 'Edit Roles', 'module' => 'Role Management'],
            ['name' => 'roles.delete', 'display_name' => 'Delete Roles', 'module' => 'Role Management'],
            
            // Department Management
            ['name' => 'departments.view', 'display_name' => 'View Departments', 'module' => 'Department Management'],
            ['name' => 'departments.create', 'display_name' => 'Create Departments', 'module' => 'Department Management'],
            ['name' => 'departments.edit', 'display_name' => 'Edit Departments', 'module' => 'Department Management'],
            ['name' => 'departments.delete', 'display_name' => 'Delete Departments', 'module' => 'Department Management'],
            
            // Financial Operations
            ['name' => 'petty_cash.view', 'display_name' => 'View Petty Cash', 'module' => 'Financial Operations'],
            ['name' => 'petty_cash.create', 'display_name' => 'Create Petty Cash', 'module' => 'Financial Operations'],
            ['name' => 'petty_cash.edit', 'display_name' => 'Edit Petty Cash', 'module' => 'Financial Operations'],
            ['name' => 'petty_cash.approve', 'display_name' => 'Approve Petty Cash', 'module' => 'Financial Operations'],
            
            ['name' => 'impress.view', 'display_name' => 'View Impress', 'module' => 'Financial Operations'],
            ['name' => 'impress.create', 'display_name' => 'Create Impress', 'module' => 'Financial Operations'],
            ['name' => 'impress.edit', 'display_name' => 'Edit Impress', 'module' => 'Financial Operations'],
            ['name' => 'impress.approve', 'display_name' => 'Approve Impress', 'module' => 'Financial Operations'],
            
            // General Ledger
            ['name' => 'gl_accounts.view', 'display_name' => 'View GL Accounts', 'module' => 'General Ledger'],
            ['name' => 'gl_accounts.create', 'display_name' => 'Create GL Accounts', 'module' => 'General Ledger'],
            ['name' => 'gl_accounts.edit', 'display_name' => 'Edit GL Accounts', 'module' => 'General Ledger'],
            ['name' => 'journals.view', 'display_name' => 'View Journals', 'module' => 'General Ledger'],
            ['name' => 'journals.create', 'display_name' => 'Create Journals', 'module' => 'General Ledger'],
            ['name' => 'journals.edit', 'display_name' => 'Edit Journals', 'module' => 'General Ledger'],
            
            // File Management
            ['name' => 'files.view', 'display_name' => 'View Files', 'module' => 'File Management'],
            ['name' => 'files.create', 'display_name' => 'Create Files', 'module' => 'File Management'],
            ['name' => 'files.edit', 'display_name' => 'Edit Files', 'module' => 'File Management'],
            ['name' => 'files.delete', 'display_name' => 'Delete Files', 'module' => 'File Management'],
            
            // Task Management
            ['name' => 'tasks.view', 'display_name' => 'View Tasks', 'module' => 'Task Management'],
            ['name' => 'tasks.create', 'display_name' => 'Create Tasks', 'module' => 'Task Management'],
            ['name' => 'tasks.edit', 'display_name' => 'Edit Tasks', 'module' => 'Task Management'],
            ['name' => 'tasks.assign', 'display_name' => 'Assign Tasks', 'module' => 'Task Management'],
            
            // HR Tools
            ['name' => 'leave.view', 'display_name' => 'View Leave', 'module' => 'HR Tools'],
            ['name' => 'leave.create', 'display_name' => 'Create Leave', 'module' => 'HR Tools'],
            ['name' => 'leave.approve', 'display_name' => 'Approve Leave', 'module' => 'HR Tools'],
            ['name' => 'employee.view', 'display_name' => 'View Employee', 'module' => 'HR Tools'],
            ['name' => 'employee.create', 'display_name' => 'Create Employee', 'module' => 'HR Tools'],
            ['name' => 'employee.edit', 'display_name' => 'Edit Employee', 'module' => 'HR Tools'],
            ['name' => 'payroll.view', 'display_name' => 'View Payroll', 'module' => 'HR Tools'],
            ['name' => 'payroll.create', 'display_name' => 'Create Payroll', 'module' => 'HR Tools'],
            ['name' => 'recruitment.view', 'display_name' => 'View Recruitment', 'module' => 'HR Tools'],
            ['name' => 'recruitment.create', 'display_name' => 'Create Recruitment', 'module' => 'HR Tools'],
            
            // Incident Management
            ['name' => 'incidents.view', 'display_name' => 'View Incidents', 'module' => 'Incident Management'],
            ['name' => 'incidents.create', 'display_name' => 'Create Incidents', 'module' => 'Incident Management'],
            ['name' => 'incidents.edit', 'display_name' => 'Edit Incidents', 'module' => 'Incident Management'],
            
            // Assessment
            ['name' => 'assessments.view', 'display_name' => 'View Assessments', 'module' => 'Assessment'],
            ['name' => 'assessments.create', 'display_name' => 'Create Assessments', 'module' => 'Assessment'],
            ['name' => 'assessments.edit', 'display_name' => 'Edit Assessments', 'module' => 'Assessment'],
            
            // System Administration
            ['name' => 'system.settings', 'display_name' => 'System Settings', 'module' => 'System Administration'],
            ['name' => 'activity.logs', 'display_name' => 'Activity Logs', 'module' => 'System Administration'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create Roles
        $roles = [
            [
                'name' => 'System Admin',
                'display_name' => 'System Administrator',
                'description' => 'Full system access with all permissions',
                'permissions' => Permission::all()->pluck('name')->toArray()
            ],
            [
                'name' => 'CEO',
                'display_name' => 'Chief Executive Officer',
                'description' => 'Executive level access',
                'permissions' => [
                    'dashboard.view',
                    'users.view', 'users.create', 'users.edit',
                    'departments.view', 'departments.create', 'departments.edit',
                    'petty_cash.view', 'petty_cash.approve',
                    'impress.view', 'impress.approve',
                    'files.view', 'files.create', 'files.edit',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
                    'leave.view', 'leave.approve',
                    'employee.view', 'employee.create', 'employee.edit',
                    'recruitment.view', 'recruitment.create',
                    'incidents.view', 'incidents.create', 'incidents.edit',
                    'assessments.view', 'assessments.create', 'assessments.edit',
                ]
            ],
            [
                'name' => 'Director',
                'display_name' => 'Director',
                'description' => 'Director level access',
                'permissions' => [
                    'dashboard.view',
                    'users.view', 'users.edit',
                    'departments.view', 'departments.edit',
                    'petty_cash.view', 'petty_cash.approve',
                    'impress.view', 'impress.approve',
                    'files.view', 'files.create', 'files.edit',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
                    'leave.view', 'leave.approve',
                    'employee.view', 'employee.edit',
                    'recruitment.view', 'recruitment.create',
                    'incidents.view', 'incidents.create', 'incidents.edit',
                    'assessments.view', 'assessments.create', 'assessments.edit',
                ]
            ],
            [
                'name' => 'HOD',
                'display_name' => 'Head of Department',
                'description' => 'Department head access',
                'permissions' => [
                    'dashboard.view',
                    'users.view',
                    'departments.view',
                    'petty_cash.view', 'petty_cash.approve',
                    'impress.view', 'impress.approve',
                    'files.view', 'files.create', 'files.edit',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
                    'leave.view', 'leave.approve',
                    'employee.view', 'employee.edit',
                    'incidents.view', 'incidents.create', 'incidents.edit',
                    'assessments.view', 'assessments.create', 'assessments.edit',
                ]
            ],
            [
                'name' => 'Accountant',
                'display_name' => 'Accountant',
                'description' => 'Financial operations access',
                'permissions' => [
                    'dashboard.view',
                    'petty_cash.view', 'petty_cash.create', 'petty_cash.edit',
                    'impress.view', 'impress.create', 'impress.edit',
                    'gl_accounts.view', 'gl_accounts.create', 'gl_accounts.edit',
                    'journals.view', 'journals.create', 'journals.edit',
                    'files.view', 'files.create', 'files.edit',
                    'tasks.view', 'tasks.create', 'tasks.edit',
                    'leave.view', 'leave.create',
                    'employee.view',
                    'incidents.view', 'incidents.create',
                    'assessments.view', 'assessments.create',
                ]
            ],
            [
                'name' => 'HR Officer',
                'display_name' => 'Human Resources Officer',
                'description' => 'HR operations access',
                'permissions' => [
                    'dashboard.view',
                    'users.view', 'users.create', 'users.edit',
                    'files.view', 'files.create', 'files.edit',
                    'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.assign',
                    'leave.view', 'leave.create', 'leave.approve',
                    'employee.view', 'employee.create', 'employee.edit',
                    'payroll.view', 'payroll.create',
                    'recruitment.view', 'recruitment.create',
                    'incidents.view', 'incidents.create', 'incidents.edit',
                    'assessments.view', 'assessments.create', 'assessments.edit',
                ]
            ],
            [
                'name' => 'Staff',
                'display_name' => 'Staff Member',
                'description' => 'Basic staff access',
                'permissions' => [
                    'dashboard.view',
                    'petty_cash.view', 'petty_cash.create',
                    'impress.view', 'impress.create',
                    'files.view', 'files.create', 'files.edit',
                    'tasks.view', 'tasks.create', 'tasks.edit',
                    'leave.view', 'leave.create',
                    'employee.view',
                    'incidents.view', 'incidents.create',
                    'assessments.view', 'assessments.create',
                ]
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);
            
            $role = Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
            
            $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}