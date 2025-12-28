<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NotificationProvider;
use App\Models\SystemSetting;

class NotificationProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update or create providers (don't skip if they exist)

        // Get values from SystemSetting or use previous EmCa SMS configuration
        $smsUsername = SystemSetting::getValue('sms_username') ?: 'emcatechn';
        $smsPassword = SystemSetting::getValue('sms_password') ?: 'Emca@#12';
        $smsFrom = SystemSetting::getValue('sms_from') ?: 'OfisiLink';
        $smsUrl = SystemSetting::getValue('sms_url') ?: 'https://messaging-service.co.tz/link/sms/v1/text/single';

        // Get email values from SystemSetting or use defaults
        $mailMailer = SystemSetting::getValue('mail_mailer') ?: 'smtp';
        $mailHost = SystemSetting::getValue('mail_host') ?: '127.0.0.1';
        $mailPort = SystemSetting::getValue('mail_port') ?: 2525;
        $mailUsername = SystemSetting::getValue('mail_username') ?: '';
        $mailPassword = SystemSetting::getValue('mail_password') ?: '';
        $mailEncryption = SystemSetting::getValue('mail_encryption') ?: 'tls';
        $mailFromAddress = SystemSetting::getValue('mail_from_address') ?: 'hello@example.com';
        $mailFromName = SystemSetting::getValue('mail_from_name') ?: 'OfisiLink';

        // Create or Update Primary SMS Provider
        $smsProvider = NotificationProvider::updateOrCreate(
            [
                'name' => 'Primary SMS Gateway',
                'type' => 'sms',
            ],
            [
                'is_active' => true,
                'is_primary' => true,
                'priority' => 100,
                'description' => 'Primary SMS gateway provider configured from system settings',
                'sms_username' => $smsUsername,
                'sms_password' => $smsPassword,
                'sms_from' => $smsFrom,
                'sms_url' => $smsUrl,
            ]
        );

        $this->command->info('Created Primary SMS Provider: ' . $smsProvider->name);
        $this->command->info('  - Username: ' . $smsUsername);
        $this->command->info('  - From: ' . $smsFrom);
        $this->command->info('  - URL: ' . $smsUrl);

        // Create or Update Primary Email Provider
        $emailProvider = NotificationProvider::updateOrCreate(
            [
                'name' => 'Primary SMTP Server',
                'type' => 'email',
            ],
            [
                'is_active' => true,
                'is_primary' => true,
                'priority' => 100,
                'description' => 'Primary email provider configured from system settings',
                'mailer_type' => $mailMailer,
                'mail_host' => $mailHost,
                'mail_port' => $mailPort,
                'mail_username' => $mailUsername,
                'mail_password' => $mailPassword,
                'mail_encryption' => $mailEncryption,
                'mail_from_address' => $mailFromAddress,
                'mail_from_name' => $mailFromName,
            ]
        );

        $this->command->info('Created Primary Email Provider: ' . $emailProvider->name);
        $this->command->info('  - Mailer: ' . $mailMailer);
        $this->command->info('  - Host: ' . $mailHost);
        $this->command->info('  - Port: ' . $mailPort);
        $this->command->info('  - From: ' . $mailFromAddress . ' (' . $mailFromName . ')');

        $this->command->info('Notification providers seeded successfully!');
    }
}
