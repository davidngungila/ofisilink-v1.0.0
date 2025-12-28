<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use App\Models\NotificationProvider;
use Illuminate\Console\Command;

class TestEmail extends Command
{
    protected $signature = 'email:test {email? : Email address to send test email to}';
    protected $description = 'Test email configuration using PHPMailer with NotificationProvider support';

    public function handle()
    {
        $email = $this->argument('email') ?? $this->ask('Enter email address to send test email to');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address: ' . $email);
            return self::FAILURE;
        }
        
        $this->info('Testing email configuration...');
        $this->info('Sending test email to: ' . $email);
        $this->newLine();
        
        // Try to use NotificationProvider first
        $provider = NotificationProvider::getPrimary('email');
        
        if ($provider) {
            $this->info('Using Email Provider: ' . $provider->name);
            $this->line('  Host: ' . $provider->mail_host);
            $this->line('  Port: ' . $provider->mail_port);
            $this->line('  Username: ' . $provider->mail_username);
            $this->line('  Encryption: ' . $provider->mail_encryption);
            $this->newLine();
            
            $result = $provider->testEmail($email);
        } else {
            $this->warn('No primary email provider found, using system settings');
            $this->newLine();
            
            $emailService = new EmailService();
            $result = $emailService->testConfiguration($email);
        }
        
        if ($result['success']) {
            $this->info('✓ ' . $result['message']);
            $this->info('Email configuration is working correctly!');
            $this->newLine();
            $this->info('Check your inbox (and spam folder) for the test email.');
            return self::SUCCESS;
        } else {
            $this->error('✗ ' . $result['message']);
            if (isset($result['error'])) {
                $this->error('Error: ' . $result['error']);
            }
            if (isset($result['suggestion'])) {
                $this->newLine();
                $this->warn('Troubleshooting Suggestions:');
                $this->line($result['suggestion']);
            }
            $this->newLine();
            
            if ($provider) {
                $this->warn('Provider Configuration:');
                $this->line('  Host: ' . $provider->mail_host);
                $this->line('  Port: ' . $provider->mail_port);
                $this->line('  Username: ' . $provider->mail_username);
                $this->line('  Encryption: ' . $provider->mail_encryption);
            } else {
                $this->warn('Current Configuration:');
                $this->line('  MAIL_HOST=' . env('MAIL_HOST', 'smtp.gmail.com'));
                $this->line('  MAIL_PORT=' . env('MAIL_PORT', '587'));
                $this->line('  MAIL_USERNAME=' . env('MAIL_USERNAME', ''));
                $this->line('  MAIL_PASSWORD=' . (env('MAIL_PASSWORD') ? '***' : 'NOT SET'));
                $this->line('  MAIL_ENCRYPTION=' . env('MAIL_ENCRYPTION', 'tls'));
            }
            
            return self::FAILURE;
        }
    }
}

