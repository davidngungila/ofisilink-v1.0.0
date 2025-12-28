<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'company_name',
                'value' => 'EmCa Technologies LIMITED',
                'type' => 'text',
                'description' => 'Company legal name',
            ],
            [
                'key' => 'trading_name',
                'value' => 'EmCa Tech',
                'type' => 'text',
                'description' => 'Trading name',
            ],
            [
                'key' => 'address',
                'value' => 'P.O.Box 20, Moshi - Kilimanjaro - Tanzania',
                'type' => 'text',
                'description' => 'Company address',
            ],
            [
                'key' => 'phone',
                'value' => '+255 697 792 300',
                'type' => 'text',
                'description' => 'Contact phone',
            ],
            [
                'key' => 'email',
                'value' => 'emca@emca.tech',
                'type' => 'email',
                'description' => 'Contact email',
            ],
            [
                'key' => 'website',
                'value' => 'www.emca.tech',
                'type' => 'url',
                'description' => 'Company website',
            ],
            [
                'key' => 'tax_id',
                'value' => 'N/A',
                'type' => 'text',
                'description' => 'Tax identification number',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
