# ZKBio Time.Net Integration - Quick Start Guide

## How It Works with Your Laravel Application

### Simple Explanation

1. **Employee scans fingerprint** on UF200-S device
2. **ZKBio Time.Net** (Windows software) downloads records from device every 5 minutes
3. **Laravel sync service** reads ZKBio Time.Net database every 5 minutes
4. **Attendance records** appear in your Laravel web application

---

## Setup Checklist

### ✅ Step 1: Install ZKBio Time.Net
- Download and install on Windows PC
- Connect UF200-S device
- Configure auto-download (every 5 minutes)

### ✅ Step 2: Configure Device in Laravel
- Go to: **HR → Attendance → Settings → Devices**
- Add device with IP address
- Configure ZKBio Time.Net connection settings

### ✅ Step 3: Ensure Employee IDs Match
- ZKBio Time.Net USERID = Laravel employee_id or employee_number
- This is **critical** for records to sync!

### ✅ Step 4: Test Sync
```bash
php artisan attendance:sync-zkbiotime --all
```

### ✅ Step 5: Enable Automatic Sync
- Laravel scheduler runs automatically (if configured)
- Or set up cron job: `* * * * * php artisan schedule:run`

---

## Database Connection Options

### Option 1: Same PC (Easiest)
- ZKBio Time.Net and Laravel on same Windows PC
- Database path: `C:\ZKTeco\ZKBioTime\attendance.db`

### Option 2: Network Share
- ZKBio Time.Net on different PC
- Network path: `\\192.168.1.50\ZKTeco\ZKBioTime\attendance.db`
- Requires Windows file sharing enabled

### Option 3: MySQL/MSSQL
- ZKBio Time.Net configured with MySQL/MSSQL
- Connect Laravel directly to database
- Requires database credentials

---

## Key Files

| File | Purpose |
|------|---------|
| `app/Services/ZKBioTimeSyncService.php` | Main sync service |
| `app/Console/Commands/SyncZKBioTimeAttendance.php` | Command to run sync |
| `app/Console/Kernel.php` | Scheduled task (every 5 min) |
| `ZKBIOTIME_UF200_SETUP_GUIDE.md` | Complete setup guide |
| `ZKBIOTIME_LARAVEL_INTEGRATION.md` | Technical integration details |

---

## Common Commands

```bash
# Sync all devices
php artisan attendance:sync-zkbiotime --all

# Sync specific device
php artisan attendance:sync-zkbiotime --device=1

# Sync last 30 days
php artisan attendance:sync-zkbiotime --days=30

# Check scheduled tasks
php artisan schedule:list
```

---

## Troubleshooting

### No records syncing?
1. Check ZKBio Time.Net is running
2. Verify database path is correct
3. Ensure Employee IDs match
4. Check Laravel logs: `storage/logs/laravel.log`

### Database connection failed?
1. Verify database file exists
2. Check network share is accessible
3. Test path manually
4. Check file permissions

### Employee IDs don't match?
1. Check ZKBio Time.Net USERID
2. Check Laravel `users.employee_id`
3. Update IDs to match
4. Re-sync after updating

---

## Data Flow Diagram

```
Employee → UF200-S Device → ZKBio Time.Net → Laravel Database → Web Interface
   ↓            ↓                  ↓                ↓                  ↓
Scan      Records          Downloads        Sync Service      View/Export
Finger    Attendance       Every 5 min     Every 5 min      Attendance
```

---

## Important Notes

⚠️ **Employee ID Matching**: Employee IDs MUST match between ZKBio Time.Net and Laravel

⚠️ **Database Access**: Laravel server must be able to access ZKBio Time.Net database

⚠️ **Scheduler**: Laravel scheduler must be running for automatic sync

⚠️ **Network**: If using network share, ensure stable network connection

---

## Support

- **Setup Guide**: See `ZKBIOTIME_UF200_SETUP_GUIDE.md`
- **Integration Details**: See `ZKBIOTIME_LARAVEL_INTEGRATION.md`
- **Logs**: Check `storage/logs/laravel.log` for errors

---

**Quick Test**: Run `php artisan attendance:sync-zkbiotime --all` and check attendance page for new records!







