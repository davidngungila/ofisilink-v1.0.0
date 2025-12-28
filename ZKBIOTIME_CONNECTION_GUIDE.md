# How to Connect ZKBio Time.Net - Step by Step Guide

## Overview

This guide shows you how to connect ZKBio Time.Net with your UF200-S device and configure it to work with your Laravel application.

---

## Step 1: Install ZKBio Time.Net

### Download and Install

1. **Download ZKBio Time.Net**
   - Visit: https://www.zkteco.com
   - Go to: **Downloads → Software → ZKBio Time.Net**
   - Download the latest Windows version

2. **Install the Software**
   - Run installer as **Administrator**
   - Follow installation wizard
   - Default installation path: `C:\ZKTeco\ZKBioTime`
   - Complete installation

3. **Launch ZKBio Time.Net**
   - Open from Start Menu
   - Create admin account (remember password)
   - Login to ZKBio Time.Net

---

## Step 2: Add UF200-S Device

### Configure Device in ZKBio Time.Net

1. **Open Device Management**
   - In ZKBio Time.Net, press **F2** or go to **Device Management**
   - Click **Add Device** button

2. **Enter Device Information**
   ```
   Device Name: UF200-S (or any name you prefer)
   IP Address: 192.168.100.127
   Port: 4370
   Device Type: UF200-S or ZKTeco Standalone
   Username: admin (default)
   Password: (device admin password, if set)
   ```

3. **Test Connection**
   - Click **Test Connection** button
   - Wait for connection status
   - ✅ **Success**: Device appears in list with green status
   - ❌ **Failed**: Check:
     - Device is powered on
     - Device IP is correct (192.168.100.127)
     - PC and device are on same network
     - Firewall allows port 4370

4. **Save Device**
   - Click **OK** to save
   - Device should appear in device list

---

## Step 3: Configure Database Connection (MySQL)

Since your system uses MySQL, configure ZKBio Time.Net to use MySQL:

### Option A: Configure MySQL in ZKBio Time.Net

1. **Open System Settings**
   - Go to **System Settings** (or press **F4**)
   - Click **Database** tab

2. **Select Database Type**
   - Select **MySQL** (not SQLite)

3. **Enter MySQL Connection Details**
   ```
   Server/Host: 192.168.100.109
   Port: 3306
   Database Name: ofisi
   Username: root
   Password: (your MySQL password, or leave empty if no password)
   ```

4. **Test Connection**
   - Click **Test Connection**
   - ✅ **Success**: "Connection successful"
   - ❌ **Failed**: Check:
     - MySQL server is running
     - MySQL allows remote connections
     - Firewall allows port 3306
     - User has proper permissions

5. **Save Settings**
   - Click **OK** to save
   - ZKBio Time.Net will create necessary tables in database

---

## Step 4: Configure Auto-Download

### Enable Automatic Data Download

1. **Open Device Settings**
   - In Device Management, select your UF200-S device
   - Click **Settings** or **Properties**

2. **Configure Download Settings**
   - Go to **Communication** or **Download** tab
   - Enable **Auto Download** or **Auto Sync**
   - Set **Download Interval**: `5 minutes` (recommended)
   - Enable **Download Attendance Records**
   - Enable **Download User Data** (optional)

3. **Save Settings**
   - Click **OK** to save
   - Device will now automatically download records every 5 minutes

---

## Step 5: Add Employees to ZKBio Time.Net

### Import or Add Employees

1. **Open User Management**
   - Press **F3** or go to **User Management**
   - Click **Add User** or **Import Users**

2. **Add Employee Manually**
   ```
   Employee ID: EMP001 (MUST match Laravel employee_id)
   Name: Employee Full Name
   Department: Select or create department
   Privilege: User (not Admin)
   ```

3. **Import from Excel (Bulk)**
   - Click **Import** → **Import from Excel/CSV**
   - Prepare Excel file with columns:
     - Employee ID (must match Laravel)
     - Name
     - Department
   - Select file and import

4. **Upload Users to Device**
   - Select all users (Ctrl+A)
   - Click **Upload User** button
   - Select UF200-S device
   - Click **OK** to upload

---

## Step 6: Configure in Laravel

### Register Device in Laravel

1. **Go to Attendance Settings**
   - Login to Laravel web application
   - Navigate to: **HR → Attendance → Settings**
   - Click **Devices** tab

2. **Add Device** (if not already added)
   - Click **Add Device** or **Register New Device**
   - Fill in:
     ```
     Device Name: UF200-S
     Device ID: UF200-S-TRU7251200134
     Device Type: Biometric
     IP Address: 192.168.100.127
     Port: 4370
     Connection Type: Network
     ```

3. **Configure ZKBio Time.Net Settings**
   - Go to **Step 3: UF200-S Config** tab
   - Enter:
     ```
     ZKBio Time.Net Server IP: (IP of PC running ZKBio Time.Net)
     Database Type: MySQL
     Database Host: 192.168.100.109
     Database Name: ofisi
     Database User: root
     Database Password: (leave empty if no password)
     Sync Interval: 5 minutes
     ```
   - Click **Save** for this tab

4. **Test Connection**
   - Click **Test Connection** button
   - ✅ **Success**: Shows "Online" status
   - ❌ **Failed**: Check MySQL connection settings

---

## Step 7: Test the Connection

### Verify Everything Works

1. **Test in ZKBio Time.Net**
   - In Device Management, select UF200-S
   - Click **Download Data** or **Sync Now**
   - Check if records are downloaded successfully

2. **Test in Laravel**
   ```bash
   # Run sync command
   php artisan attendance:sync-zkbiotime --device=UF200-S
   ```
   
   Or sync all devices:
   ```bash
   php artisan attendance:sync-zkbiotime --all
   ```

3. **Check Results**
   - Go to: **HR → Attendance**
   - Check if attendance records appear
   - Verify employee IDs match

---

## Step 8: Verify Employee ID Matching

### Critical: Employee IDs Must Match!

**ZKBio Time.Net:**
- Employee ID is stored in `USERID` field
- Set when adding users in ZKBio Time.Net

**Laravel:**
- Employee ID is in `users.employee_id` or `employees.employee_number`
- Must match ZKBio Time.Net `USERID` exactly

**To Check:**
1. In ZKBio Time.Net: **User Management** → Check Employee ID
2. In Laravel: **HR → Employees** → Check Employee ID/Number
3. **They must be identical!**

**If IDs don't match:**
- Update Employee IDs in ZKBio Time.Net to match Laravel
- Or update Laravel to match ZKBio Time.Net
- Re-upload users to device after updating

---

## Troubleshooting

### Issue: Device Not Connecting in ZKBio Time.Net

**Solutions:**
1. ✅ Check device IP: `192.168.100.127`
2. ✅ Ping device from PC: `ping 192.168.100.127`
3. ✅ Check firewall allows port 4370
4. ✅ Verify device is powered on
5. ✅ Check network cable connection

### Issue: MySQL Connection Failed

**Solutions:**
1. ✅ Verify MySQL server is running on `192.168.100.109`
2. ✅ Check MySQL allows remote connections
3. ✅ Test connection: `mysql -h 192.168.100.109 -u root -p`
4. ✅ Grant access if needed (see `WHEN_TO_RUN_MYSQL_FIX.md`)
5. ✅ Check firewall allows port 3306

### Issue: No Records Syncing to Laravel

**Solutions:**
1. ✅ Ensure ZKBio Time.Net is running
2. ✅ Check auto-download is enabled
3. ✅ Verify database connection in Laravel device settings
4. ✅ Check Employee IDs match
5. ✅ Run manual sync: `php artisan attendance:sync-zkbiotime --all`
6. ✅ Check Laravel logs: `storage/logs/laravel.log`

### Issue: Employee IDs Don't Match

**Solutions:**
1. ✅ Check ZKBio Time.Net USERID
2. ✅ Check Laravel employee_id or employee_number
3. ✅ Update IDs to match
4. ✅ Re-upload users to device
5. ✅ Re-sync attendance records

---

## Quick Checklist

- [ ] ZKBio Time.Net installed and running
- [ ] UF200-S device added in ZKBio Time.Net
- [ ] Device connection tested successfully
- [ ] MySQL database configured in ZKBio Time.Net
- [ ] Auto-download enabled (every 5 minutes)
- [ ] Employees added to ZKBio Time.Net
- [ ] Employee IDs match between systems
- [ ] Users uploaded to device
- [ ] Device registered in Laravel
- [ ] ZKBio Time.Net settings configured in Laravel
- [ ] Connection tested in Laravel
- [ ] Sync command tested successfully

---

## Current Configuration Summary

Based on your setup:

- **Device**: UF200-S (Serial: TRU7251200134)
- **Device IP**: 192.168.100.127
- **Port**: 4370
- **Database**: MySQL
- **Database Host**: 192.168.100.109
- **Database Name**: ofisi
- **Database User**: root
- **Database Password**: (none)

---

## Next Steps After Connection

1. **Enroll Fingerprints**
   - In ZKBio Time.Net: User Management → Select user → Enroll Fingerprint
   - Or enroll directly on device

2. **Test Attendance Recording**
   - Scan fingerprint on device
   - Wait 5 minutes for auto-download
   - Check ZKBio Time.Net for record
   - Run sync in Laravel
   - Verify record appears in Laravel

3. **Enable Automatic Sync**
   - Laravel scheduler runs automatically every 5 minutes
   - Or set up cron: `* * * * * php artisan schedule:run`

---

## Support Files

- **Full Setup Guide**: `ZKBIOTIME_UF200_SETUP_GUIDE.md`
- **Quick Start**: `ZKBIOTIME_QUICK_START.md`
- **Integration Details**: `ZKBIOTIME_LARAVEL_INTEGRATION.md`
- **MySQL Fix**: `WHEN_TO_RUN_MYSQL_FIX.md`

---

**Need Help?** Check Laravel logs: `storage/logs/laravel.log` for detailed error messages.






