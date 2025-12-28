# Fix MySQL Connection for ZKBio Time.Net Sync

## Current Configuration
- **Device**: UF 2000 HQ (ID: 4)
- **Database Type**: MySQL
- **Database Host**: 192.168.100.109
- **Database Name**: ofisi
- **Database User**: root

## Error
```
Host 'DESKTOP-6371IRP.mshome.net' is not allowed to connect to this MySQL server
```

## Solution: Grant MySQL Access

### Step 1: Connect to MySQL Server (192.168.100.109)

SSH or RDP to the MySQL server, then run:

```sql
-- Option 1: Grant access from specific host
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'DESKTOP-6371IRP.mshome.net' IDENTIFIED BY 'your_password';
FLUSH PRIVILEGES;

-- Option 2: Grant access from any host (less secure, but easier)
GRANT ALL PRIVILEGES ON ofisi.* TO 'root'@'%' IDENTIFIED BY 'your_password';
FLUSH PRIVILEGES;

-- Option 3: Create dedicated user for ZKBio Time.Net
CREATE USER 'zkbiotime'@'%' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE ON ofisi.* TO 'zkbiotime'@'%';
FLUSH PRIVILEGES;
```

### Step 2: Check MySQL Configuration

On the MySQL server, edit `my.cnf` or `my.ini`:

```ini
[mysqld]
bind-address = 0.0.0.0  # Allow connections from any IP
# OR
bind-address = 192.168.100.109  # Allow connections from specific IP
```

Restart MySQL service after changes.

### Step 3: Check Firewall

Ensure MySQL port (3306) is open:
- Windows Firewall: Allow port 3306
- Router/Network: Allow port 3306

### Step 4: Test Connection

From Laravel server, test connection:
```bash
mysql -h 192.168.100.109 -u root -p ofisi
```

---

## Alternative: Switch to SQLite (Easier)

If MySQL setup is complex, switch to SQLite:

1. **Go to**: Attendance Settings → Devices → Edit "UF 2000 HQ"
2. **Step 3**: UF200-S Config
3. **Change**:
   - Database Type: **SQLite**
   - Database Path: `C:\ZKTeco\ZKBioTime\attendance.db` (or actual path)
4. **Save**

Then run:
```bash
php artisan attendance:sync-zkbiotime --device="UF 2000 HQ"
```

---

## Update Device Configuration via Command

If you want to update via command line:

```bash
php artisan tinker
```

Then:
```php
$device = \App\Models\AttendanceDevice::find(4);
$settings = $device->settings ?? [];
$settings['zkbio_db_type'] = 'sqlite';
$settings['zkbio_db_path'] = 'C:\\ZKTeco\\ZKBioTime\\attendance.db';
$device->settings = $settings;
$device->save();
```







