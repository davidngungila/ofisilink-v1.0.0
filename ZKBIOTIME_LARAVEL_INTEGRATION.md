# ZKBio Time.Net & Laravel Integration Guide

## How It Works

This document explains how ZKBio Time.Net integrates with your Laravel application to sync attendance records from the ZKTeco UF200-S biometric device.

---

## Architecture Overview

```
┌─────────────────┐         ┌──────────────────┐         ┌─────────────────┐
│  UF200-S Device │  ────>  │  ZKBio Time.Net  │  ────>  │  Laravel App    │
│  (Biometric)    │         │  (Windows PC)    │         │  (Web Server)   │
└─────────────────┘         └──────────────────┘         └─────────────────┘
     │                              │                            │
     │                              │                            │
     │ 1. Employee scans            │ 2. Records stored          │ 3. Sync service
     │    fingerprint               │    in database             │    reads database
     │                              │                            │
     │                              │ 3. Auto-download           │ 4. Creates attendance
     │                              │    every 5 minutes          │    records in Laravel
     └──────────────────────────────┴────────────────────────────┴─────────────────┘
```

---

## Data Flow

### Step 1: Employee Attendance Recording
1. Employee scans fingerprint on **UF200-S device**
2. Device records attendance with:
   - Employee ID (USERID)
   - Timestamp (CHECKTIME)
   - Check Type (CHECKTYPE: 'I' for In, 'O' for Out)
   - Verification Code (VERIFYCODE)

### Step 2: ZKBio Time.Net Processing
1. **ZKBio Time.Net** automatically downloads records from device (every 5 minutes)
2. Records are stored in database:
   - **SQLite**: `C:\ZKTeco\ZKBioTime\attendance.db` (default)
   - **MySQL/MSSQL**: If configured
3. Database table: `CHECKINOUT` or `CHECKINOUTS`

### Step 3: Laravel Sync Service
1. **Laravel scheduled task** runs every 5 minutes
2. **ZKBioTimeSyncService** connects to ZKBio Time.Net database
3. Reads attendance records since last sync
4. Maps Employee IDs to Laravel users
5. Creates/updates attendance records in Laravel database

### Step 4: Attendance Records in Laravel
- Records appear in attendance management system
- Can be viewed, filtered, and exported
- Integrated with payroll and reporting

---

## Integration Components

### 1. ZKBioTimeSyncService (`app/Services/ZKBioTimeSyncService.php`)

**Purpose**: Handles all database operations to sync attendance from ZKBio Time.Net

**Key Methods**:
- `syncFromZKBioTime()` - Syncs attendance for a specific device
- `syncAllDevices()` - Syncs all active biometric devices
- `connectToZKBioDatabase()` - Connects to ZKBio Time.Net database
- `fetchAttendanceRecords()` - Retrieves records from ZKBio database
- `processAttendanceRecord()` - Processes and saves each record

**Database Support**:
- SQLite (default ZKBio Time.Net database)
- MySQL
- MS SQL Server

### 2. Sync Command (`app/Console/Commands/SyncZKBioTimeAttendance.php`)

**Purpose**: Command-line interface to manually trigger syncs

**Usage**:
```bash
# Sync all active devices
php artisan attendance:sync-zkbiotime --all

# Sync specific device
php artisan attendance:sync-zkbiotime --device=1

# Sync last 30 days
php artisan attendance:sync-zkbiotime --days=30
```

### 3. Scheduled Task (`app/Console/Kernel.php`)

**Purpose**: Automatically syncs attendance every 5 minutes

**Configuration**:
```php
$schedule->command('attendance:sync-zkbiotime')->everyFiveMinutes();
```

**Note**: Requires Laravel scheduler to be running:
```bash
# Add to crontab (Linux) or Task Scheduler (Windows)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Device Configuration (`attendance_devices` table)

**Settings stored in `settings` JSON field**:
```json
{
  "zkbio_server_ip": "192.168.1.50",
  "zkbio_db_type": "sqlite",
  "zkbio_db_path": "\\\\192.168.1.50\\ZKTeco\\ZKBioTime\\attendance.db",
  "zkbio_sync_interval": 5,
  "zkbio_auto_sync": true
}
```

---

## Database Connection Methods

### Method 1: SQLite (Default - Same Machine)

**If ZKBio Time.Net and Laravel are on the same Windows PC:**

```php
// Local file path
$dbPath = 'C:\\ZKTeco\\ZKBioTime\\attendance.db';
```

**Configuration in Device Settings**:
- **ZKBio Time.Net Server IP**: `127.0.0.1` or leave empty
- **Database Type**: `SQLite`
- **Database Path**: `C:\ZKTeco\ZKBioTime\attendance.db`

### Method 2: SQLite (Network Share)

**If ZKBio Time.Net is on a different Windows PC:**

```php
// Network share path
$dbPath = '\\\\192.168.1.50\\ZKTeco\\ZKBioTime\\attendance.db';
```

**Requirements**:
- Network share must be accessible
- Laravel server must have read permissions
- Windows file sharing enabled on ZKBio Time.Net PC

**Configuration**:
- **ZKBio Time.Net Server IP**: `192.168.1.50` (PC IP)
- **Database Type**: `SQLite`
- **Database Path**: `\\192.168.1.50\ZKTeco\ZKBioTime\attendance.db`

### Method 3: MySQL/MSSQL

**If ZKBio Time.Net uses MySQL or MSSQL:**

**Configuration**:
- **ZKBio Time.Net Server IP**: Database server IP
- **Database Type**: `MySQL` or `MSSQL`
- **Database Host**: `192.168.1.50`
- **Database Name**: `zkbiotime` (or your database name)
- **Database User**: Database username
- **Database Password**: Database password

---

## Employee ID Mapping

**Critical**: Employee IDs must match between systems!

### ZKBio Time.Net Employee ID
- Stored in `USERID` field in `CHECKINOUT` table
- Set when adding users in ZKBio Time.Net

### Laravel Employee ID
- Stored in `users.employee_id` or `employees.employee_number`
- Must match ZKBio Time.Net `USERID`

### Mapping Process
```php
// Service looks for user by:
1. users.employee_id = ZKBio USERID
2. employees.employee_number = ZKBio USERID
```

**If IDs don't match:**
- Records will be skipped
- Warning logged in Laravel logs
- No attendance record created

---

## Sync Process Details

### Automatic Sync (Recommended)

1. **Laravel Scheduler** runs every 5 minutes
2. Checks all active biometric devices
3. For each device:
   - Checks if sync interval has passed
   - Connects to ZKBio Time.Net database
   - Fetches records since last sync
   - Processes each record
   - Updates device `last_sync_at` timestamp

### Manual Sync

**Via Command Line**:
```bash
php artisan attendance:sync-zkbiotime --all
```

**Via Web Interface** (if implemented):
- Go to Attendance Settings → Devices
- Click "Sync Now" button for specific device

### Sync Interval

- **Default**: 5 minutes
- **Configurable**: Per device in settings
- **Minimum**: 1 minute
- **Maximum**: 1440 minutes (24 hours)

---

## Attendance Record Processing

### Time In/Out Detection

The service determines if a record is "Time In" or "Time Out" using:

1. **CHECKTYPE field**:
   - `'I'` or `'0'` = Time In
   - `'O'` = Time Out

2. **Logic fallback** (if CHECKTYPE unclear):
   - First record of day = Time In
   - Second record of day = Time Out

### Record Creation

For each ZKBio Time.Net record:

1. **Find User**: Match Employee ID
2. **Get/Create Attendance**: For attendance date
3. **Set Time In/Out**: Based on CHECKTYPE
4. **Calculate Hours**: If both time in and out exist
5. **Set Status**: Present, Late, etc.
6. **Store Metadata**: ZKBio Time.Net specific data

### Duplicate Prevention

- If attendance record exists for date, it's updated (not duplicated)
- Latest time is used if multiple records exist
- Metadata is merged, not replaced

---

## Error Handling

### Common Errors

1. **Database Connection Failed**
   - **Cause**: Database path incorrect or inaccessible
   - **Solution**: Check path, permissions, network connectivity

2. **User Not Found**
   - **Cause**: Employee ID mismatch
   - **Solution**: Ensure Employee IDs match in both systems

3. **Table Not Found**
   - **Cause**: ZKBio Time.Net database structure different
   - **Solution**: Check table name (CHECKINOUT vs CHECKINOUTS)

4. **Sync Timeout**
   - **Cause**: Too many records to process
   - **Solution**: Reduce sync date range, increase PHP timeout

### Logging

All errors are logged to Laravel log file:
```
storage/logs/laravel.log
```

Search for: `ZKBio Time sync`

---

## Configuration Steps

### Step 1: Configure Device in Laravel

1. Go to **HR → Attendance → Settings → Devices**
2. Click **Add Device**
3. Fill in device information:
   - Name: "Main Office UF200-S"
   - Device Type: Biometric
   - IP Address: Device IP (e.g., 192.168.1.100)
   - Port: 4370

### Step 2: Configure ZKBio Time.Net Integration

1. Go to **Step 3: UF200-S Config** tab
2. Enter ZKBio Time.Net settings:
   - **Server IP**: PC running ZKBio Time.Net
   - **Database Type**: SQLite (or MySQL/MSSQL)
   - **Database Path**: Path to attendance.db
   - **Sync Interval**: 5 minutes
   - Enable **Automatic Sync**

3. Click **Save Device**

### Step 3: Test Connection

1. Click **Test Connection** button
2. Verify connection is successful
3. Check Laravel logs for any errors

### Step 4: Run Initial Sync

```bash
# Sync all devices
php artisan attendance:sync-zkbiotime --all

# Or sync specific device
php artisan attendance:sync-zkbiotime --device=1 --days=30
```

### Step 5: Verify Attendance Records

1. Go to **HR → Attendance**
2. Check if records appear
3. Verify employee IDs match
4. Check time in/out times

---

## Troubleshooting

### Issue: No Records Syncing

**Check**:
1. ZKBio Time.Net is running
2. Device is connected in ZKBio Time.Net
3. Auto-download is enabled in ZKBio Time.Net
4. Database path is correct
5. Employee IDs match
6. Laravel scheduler is running

**Solution**:
```bash
# Check scheduler status
php artisan schedule:list

# Run manual sync with verbose output
php artisan attendance:sync-zkbiotime --all -v
```

### Issue: Database Connection Failed

**Check**:
1. Database file exists
2. Network share is accessible
3. Permissions are correct
4. Firewall allows connection

**Solution**:
- Test network path: `\\192.168.1.50\ZKTeco\ZKBioTime\attendance.db`
- Check Windows file sharing
- Verify Laravel server can access network share

### Issue: Employee IDs Don't Match

**Check**:
1. ZKBio Time.Net USERID
2. Laravel users.employee_id
3. Laravel employees.employee_number

**Solution**:
- Update Employee IDs to match
- Re-upload users to device if needed
- Check ZKBio Time.Net user management

---

## Performance Considerations

### Sync Frequency

- **Recommended**: 5 minutes
- **High Volume**: 1-2 minutes
- **Low Volume**: 10-15 minutes

### Database Access

- **SQLite**: Fast for local access, slower for network
- **MySQL/MSSQL**: Better for network access, requires setup

### Record Volume

- **Typical**: 50-200 records per sync
- **Large Office**: 500+ records per sync
- **Processing Time**: ~1-5 seconds per 100 records

---

## Security Considerations

### Database Access

- **SQLite**: File permissions should be restricted
- **MySQL/MSSQL**: Use dedicated database user with read-only access
- **Network**: Use VPN or secure network for database access

### Employee Data

- Employee IDs are sensitive information
- Ensure database connections are encrypted (for MySQL/MSSQL)
- Log access for audit purposes

---

## Maintenance

### Daily

- Monitor sync logs
- Check for failed syncs
- Verify attendance records

### Weekly

- Review sync performance
- Check for missing records
- Verify employee ID mappings

### Monthly

- Backup ZKBio Time.Net database
- Review and optimize sync intervals
- Clean up old metadata

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review ZKBio Time.Net logs
3. Test database connection manually
4. Contact system administrator

---

**Last Updated**: 2025
**Version**: 1.0
**Laravel Version**: 10.x+
**ZKBio Time.Net**: Latest version







