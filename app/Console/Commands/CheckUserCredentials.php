<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CheckUserCredentials extends Command
{
    protected $signature = 'user:check-credentials {email}';
    protected $description = 'Check user credentials and password status';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }
        
        $this->info("User found: {$user->name} (ID: {$user->id})");
        $this->line("Email: {$user->email}");
        $this->line("Active: " . ($user->is_active ? 'Yes' : 'No'));
        $this->line("Has Password: " . (empty($user->password) ? 'No' : 'Yes'));
        $this->line("Phone: " . ($user->phone ?? $user->mobile ?? 'Not set'));
        $this->line("Employee Record: " . ($user->employee ? 'Yes' : 'No'));
        $this->line("Roles: " . $user->roles->pluck('name')->join(', ') ?: 'None');
        
        if (empty($user->password)) {
            $this->warn("WARNING: User does not have a password set!");
            if ($this->confirm('Would you like to set a password for this user?')) {
                $password = $this->secret('Enter new password (min 8 characters):');
                if (strlen($password) < 8) {
                    $this->error('Password must be at least 8 characters.');
                    return 1;
                }
                $user->password = Hash::make($password);
                $user->save();
                $this->info('Password has been set successfully!');
            }
        }
        
        return 0;
    }
}
