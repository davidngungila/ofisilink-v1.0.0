<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;

class AssignRolesToTestUsers extends Command
{
    protected $signature = 'users:assign-roles';
    protected $description = 'Assign roles to existing test users';

    public function handle()
    {
        $this->info('Assigning roles to test users...');
        $this->newLine();

        // Get roles
        $roles = [
            'System Admin' => Role::where('name', 'System Admin')->first(),
            'CEO' => Role::where('name', 'CEO')->first(),
            'HOD' => Role::where('name', 'HOD')->first(),
            'HR Officer' => Role::where('name', 'HR Officer')->first(),
            'Accountant' => Role::where('name', 'Accountant')->first(),
            'Staff' => Role::where('name', 'Staff')->first(),
        ];

        // Get test users
        $testUsers = User::where('email', 'like', 'testuser%@ofisi.com')
            ->orWhere('email', 'like', '%@ofisi.com')
            ->orderBy('id')
            ->get();

        if ($testUsers->isEmpty()) {
            $this->warn('No test users found.');
            return 1;
        }

        $assigned = 0;
        $userIndex = 0;

        foreach ($testUsers as $user) {
            $userIndex++;
            
            // Skip if user already has roles
            if ($user->roles->isNotEmpty()) {
                $this->line("⊘ Skipping {$user->email} (already has roles)");
                continue;
            }

            // Assign roles based on index
            if ($userIndex === 1 && $roles['System Admin']) {
                $user->roles()->attach($roles['System Admin']->id);
                $this->line("✓ {$user->email} → System Admin");
                $assigned++;
            } elseif ($userIndex === 2 && $roles['CEO']) {
                $user->roles()->attach($roles['CEO']->id);
                $this->line("✓ {$user->email} → CEO");
                $assigned++;
            } elseif ($userIndex === 3 && $roles['HR Officer']) {
                $user->roles()->attach($roles['HR Officer']->id);
                $this->line("✓ {$user->email} → HR Officer");
                $assigned++;
            } elseif ($userIndex === 4 && $roles['HOD']) {
                $user->roles()->attach($roles['HOD']->id);
                $this->line("✓ {$user->email} → HOD");
                $assigned++;
            } elseif ($userIndex === 5 && $roles['Accountant']) {
                $user->roles()->attach($roles['Accountant']->id);
                $this->line("✓ {$user->email} → Accountant");
                $assigned++;
            } elseif ($roles['Staff']) {
                // Assign Staff role to remaining users
                $user->roles()->attach($roles['Staff']->id);
                $this->line("✓ {$user->email} → Staff");
                $assigned++;
            }
        }

        $this->newLine();
        $this->info("Successfully assigned roles to {$assigned} users!");

        // Display summary
        $this->newLine();
        $this->table(
            ['Email', 'Name', 'Roles'],
            $testUsers->map(function($user) {
                return [
                    $user->email,
                    $user->name,
                    $user->roles->pluck('name')->join(', ') ?: 'No Role',
                ];
            })->toArray()
        );

        return 0;
    }
}
