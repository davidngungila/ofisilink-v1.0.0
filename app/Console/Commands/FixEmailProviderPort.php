<?php

namespace App\Console\Commands;

use App\Models\NotificationProvider;
use Illuminate\Console\Command;

class FixEmailProviderPort extends Command
{
    protected $signature = 'email:fix-port';
    protected $description = 'Fix Gmail email provider port to correct value (587 for TLS, 465 for SSL)';

    public function handle()
    {
        $providers = NotificationProvider::where('type', 'email')
            ->where('mail_host', 'like', '%gmail.com%')
            ->get();
        
        if ($providers->isEmpty()) {
            $this->warn('No Gmail email providers found.');
            return self::SUCCESS;
        }
        
        foreach ($providers as $provider) {
            $this->info("Found provider: {$provider->name}");
            $this->line("  Current Port: {$provider->mail_port}");
            $this->line("  Current Encryption: {$provider->mail_encryption}");
            
            // Determine correct port based on encryption
            if ($provider->mail_encryption === 'ssl' || $provider->mail_encryption === 'smtps') {
                $correctPort = 465;
                $correctEncryption = 'ssl';
            } else {
                $correctPort = 587;
                $correctEncryption = 'tls';
            }
            
            if ($provider->mail_port != $correctPort) {
                $provider->mail_port = $correctPort;
                $provider->mail_encryption = $correctEncryption;
                $provider->save();
                
                $this->info("  ✓ Updated Port to: {$correctPort}");
                $this->info("  ✓ Updated Encryption to: {$correctEncryption}");
            } else {
                $this->info("  ✓ Port is already correct");
            }
        }
        
        $this->newLine();
        $this->info('Email provider ports fixed successfully!');
        
        return self::SUCCESS;
    }
}










