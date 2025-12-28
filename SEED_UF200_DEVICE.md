# Seed UF200-S Device Data

## Device Information

- **Device Name**: UF200-S
- **Serial Number**: TRU7251200134
- **MAC Address**: 00:17:61:10:50:2d
- **IP Address**: 192.168.100.127
- **Port**: 4370
- **Manufacturer**: ZKTECO CO., LTD.
- **Fingerprint Algorithm**: ZKFinger VX10.0
- **Face Algorithm**: ZKFace VX7.0
- **Platform**: ZLM60_TFT

## How to Seed

### Option 1: Using Laravel Seeder (Recommended)

```bash
# Run only the device seeder
php artisan db:seed --class=AttendanceDeviceSeeder

# Or run all seeders (includes device seeder)
php artisan db:seed
```

### Option 2: Using SQL File

```bash
# Run SQL file directly
mysql -u root -p ofisi < database/seeders/uf200_s_device_seed.sql
```

Or import via phpMyAdmin/MySQL Workbench:
1. Open `database/seeders/uf200_s_device_seed.sql`
2. Copy and paste into SQL query window
3. Execute

### Option 3: Using Tinker

```bash
php artisan tinker
```

Then paste:
```php
use App\Models\AttendanceDevice;

AttendanceDevice::create([
    'name' => 'UF200-S',
    'device_id' => 'UF200-S-TRU7251200134',
    'device_type' => 'biometric',
    'model' => 'UF200-S',
    'manufacturer' => 'ZKTECO CO., LTD.',
    'serial_number' => 'TRU7251200134',
    'ip_address' => '192.168.100.127',
    'mac_address' => '00:17:61:10:50:2d',
    'port' => 4370,
    'connection_type' => 'network',
    'connection_config' => [
        'subnet_mask' => '255.255.255.0',
        'gateway' => '192.168.100.1',
        'dns' => '0.0.0.0',
        'dhcp' => true,
    ],
    'is_active' => true,
    'sync_interval_minutes' => 5,
    'capabilities' => [
        'fingerprint_algorithm' => 'ZKFinger VX10.0',
        'face_algorithm' => 'ZKFace VX7.0',
        'platform' => 'ZLM60_TFT',
        'supports_fingerprint' => true,
        'supports_face_recognition' => true,
    ],
    'settings' => [
        'zkbio_db_type' => 'mysql',
        'zkbio_db_host' => '192.168.100.109',
        'zkbio_db_name' => 'ofisi',
        'zkbio_db_user' => 'root',
    ],
    'notes' => 'ZKTeco UF200-S Biometric Device - Serial: TRU7251200134',
]);
```

## After Seeding

1. **Configure ZKBio Time.Net Settings**:
   - Go to: **HR → Attendance → Settings → Devices**
   - Find "UF200-S" device
   - Click **Edit**
   - Go to **UF200-S Config** tab
   - Configure ZKBio Time.Net database connection:
     - **ZKBio Time.Net Server IP**: IP of PC running ZKBio Time.Net
     - **Database Type**: MySQL (or SQLite/MSSQL)
     - **Database Host**: 192.168.100.109
     - **Database Name**: ofisi
     - **Database User**: root
     - **Database Password**: (leave empty if no password)

2. **Test Connection**:
   - Click **Test Connection** button
   - Verify connection status shows "Online"

3. **Run Initial Sync**:
   ```bash
   php artisan attendance:sync-zkbiotime --device=UF200-S
   ```

## Verify Device

Check if device was seeded:
```bash
php artisan tinker
```

```php
use App\Models\AttendanceDevice;
AttendanceDevice::where('serial_number', 'TRU7251200134')->first();
```

## Files Created

- `database/seeders/AttendanceDeviceSeeder.php` - Laravel seeder class
- `database/seeders/uf200_s_device_seed.sql` - SQL seed file
- `SEED_UF200_DEVICE.md` - This guide






