<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password {email} {--password=}';
    protected $description = 'Reset a user password';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->option('password');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }
        
        $this->info("User found: {$user->name} (ID: {$user->id})");
        $this->line("Email: {$user->email}");
        $this->line("Active: " . ($user->is_active ? 'Yes' : 'No'));
        $this->line("Has Password: " . (empty($user->password) ? 'No' : 'Yes'));
        
        if (!$password) {
            $password = $this->secret('Enter new password (min 8 characters):');
            $confirm = $this->secret('Confirm password:');
            
            if ($password !== $confirm) {
                $this->error('Passwords do not match.');
                return 1;
            }
        }
        
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return 1;
        }
        
        $user->password = Hash::make($password);
        $user->save();
        
        $this->info('Password has been reset successfully!');
        $this->line("User can now login with:");
        $this->line("  Email: {$user->email}");
        $this->line("  Password: [the password you just set]");
        
        return 0;
    }
}
