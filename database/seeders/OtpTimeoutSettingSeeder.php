<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class OtpTimeoutSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add OTP timeout setting if it doesn't exist
        SystemSetting::updateOrCreate(
            ['key' => 'otp_timeout_minutes'],
            [
                'value' => '10',
                'type' => 'number',
                'description' => 'OTP code validity period in minutes. This setting controls how long OTP codes remain valid for login, password reset, and other verification purposes.'
            ]
        );
        
        // Add max login attempts setting
        SystemSetting::updateOrCreate(
            ['key' => 'max_login_attempts'],
            [
                'value' => '5',
                'type' => 'number',
                'description' => 'Maximum number of failed login attempts allowed before account lockout. Recommended: 3-10 attempts.'
            ]
        );
        
        // Add session timeout setting
        SystemSetting::updateOrCreate(
            ['key' => 'session_timeout_minutes'],
            [
                'value' => '120',
                'type' => 'number',
                'description' => 'Automatic session timeout in minutes. Users will be automatically logged out after this period of inactivity. Recommended: 15-1440 minutes (1 day).'
            ]
        );
    }
}




