<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AttendanceDevice;
use Illuminate\Support\Facades\DB;

class AttendanceDeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if device already exists
        $existingDevice = AttendanceDevice::where('device_id', 'TRU7251200134')
            ->orWhere('serial_number', 'TRU7251200134')
            ->orWhere('ip_address', '192.168.100.127')
            ->first();

        if ($existingDevice) {
            $this->command->warn('Device with Serial Number TRU7251200134 or IP 192.168.100.127 already exists. Skipping...');
            return;
        }

        // Create the UF200-S device
        AttendanceDevice::create([
            'name' => 'UF200-S',
            'device_id' => 'UF200-S-TRU7251200134', // Unique identifier
            'device_type' => AttendanceDevice::TYPE_BIOMETRIC,
            'model' => 'UF200-S',
            'manufacturer' => 'ZKTECO CO., LTD.',
            'serial_number' => 'TRU7251200134',
            'location_id' => null, // Set this if you have a location
            'ip_address' => '192.168.100.127',
            'mac_address' => '00:17:61:10:50:2d',
            'port' => 4370,
            'connection_type' => AttendanceDevice::CONNECTION_NETWORK,
            'connection_config' => [
                'subnet_mask' => '255.255.255.0',
                'gateway' => '192.168.100.1',
                'dns' => '0.0.0.0',
                'dhcp' => true,
            ],
            'is_active' => true,
            'is_online' => false, // Will be updated when connection is tested
            'last_sync_at' => null,
            'sync_interval_minutes' => 5,
            'capabilities' => [
                'fingerprint_algorithm' => 'ZKFinger VX10.0',
                'face_algorithm' => 'ZKFace VX7.0',
                'platform' => 'ZLM60_TFT',
                'supports_fingerprint' => true,
                'supports_face_recognition' => true,
            ],
            'settings' => [
                // ZKBio Time.Net configuration (to be configured via UI)
                'zkbio_server_ip' => null, // Set this to the IP of PC running ZKBio Time.Net
                'zkbio_db_type' => 'mysql', // or 'sqlite', 'mssql'
                'zkbio_db_host' => '192.168.100.109', // MySQL server IP
                'zkbio_db_name' => 'ofisi', // Database name
                'zkbio_db_user' => 'root', // Database user
                'zkbio_db_password' => null, // Database password (if any)
                'zkbio_db_path' => null, // For SQLite only
            ],
            'notes' => 'ZKTeco UF200-S Biometric Device - Serial: TRU7251200134',
            'created_by' => null, // Set to user ID if available
            'updated_by' => null,
        ]);

        $this->command->info('✅ UF200-S device seeded successfully!');
        $this->command->info('   Device Name: UF200-S');
        $this->command->info('   IP Address: 192.168.100.127');
        $this->command->info('   Serial Number: TRU7251200134');
        $this->command->warn('⚠️  Remember to configure ZKBio Time.Net settings via the web interface!');
    }
}






