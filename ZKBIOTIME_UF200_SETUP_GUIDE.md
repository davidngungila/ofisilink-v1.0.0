# ZKBio Time.Net & ZKTeco UF200-S Setup Guide

## Overview
This guide provides step-by-step instructions to configure ZKBio Time.Net software with ZKTeco UF200-S biometric device and integrate it with the attendance management system.

---

## Prerequisites

### Hardware Requirements
- **ZKTeco UF200-S** Biometric Device
- **Windows PC** (Windows 7/8/10/11) - Recommended to run 24/7
- **Network Connection** (Ethernet or Wi-Fi)
- **Ethernet Cable** (for stable connection)

### Software Requirements
- **ZKBio Time.Net** (Latest version from ZKTeco)
- **Database** (SQLite default, or MySQL/MSSQL for advanced setup)
- **Web Browser** (for accessing attendance system)

---

## Part 1: ZKBio Time.Net Installation

### Step 1: Download and Install ZKBio Time.Net

1. **Download ZKBio Time.Net**
   - Visit ZKTeco official website: https://www.zkteco.com
   - Navigate to Downloads → Software → ZKBio Time.Net
   - Download the latest version compatible with Windows

2. **Install ZKBio Time.Net**
   - Run the installer as Administrator
   - Follow installation wizard
   - Choose installation directory (default: `C:\ZKTeco\ZKBioTime`)
   - Complete installation

3. **Launch ZKBio Time.Net**
   - Open ZKBio Time.Net from Start Menu
   - First launch will prompt for initial setup
   - Create admin account (remember credentials)

---

## Part 2: UF200-S Device Network Configuration

### Step 2: Configure Device IP Address

1. **Access Device Menu**
   - On UF200-S device, press **Menu** button
   - Enter admin password (default: usually empty or "admin")
   - Navigate to **System Settings → Network**

2. **Set Static IP Address** (Recommended)
   - Select **TCP/IP** mode
   - Set **IP Address**: e.g., `192.168.1.100`
   - Set **Subnet Mask**: e.g., `255.255.255.0`
   - Set **Gateway**: e.g., `192.168.1.1`
   - Set **Port**: `4370` (default ZKTeco port)
   - Save settings

3. **Verify Network Connection**
   - Ensure device and PC are on same network
   - Ping device IP from PC: `ping 192.168.1.100`
   - If ping fails, check network cable and firewall settings

---

## Part 3: Connect Device to ZKBio Time.Net

### Step 3: Add Device in ZKBio Time.Net

1. **Open Device Management**
   - In ZKBio Time.Net, go to **Device Management** (or press `F2`)
   - Click **Add Device** button

2. **Enter Device Information**
   - **Device Name**: Enter a descriptive name (e.g., "Main Office UF200-S")
   - **IP Address**: Enter device IP (e.g., `192.168.1.100`)
   - **Port**: `4370`
   - **Device Type**: Select **UF200-S** or **ZKTeco Standalone**
   - **Username**: `admin` (default)
   - **Password**: Enter device admin password

3. **Test Connection**
   - Click **Test Connection** button
   - Wait for connection status
   - If successful, device will appear in device list
   - If failed, check:
     - Device IP address is correct
     - Device is powered on and connected to network
     - Firewall is not blocking port 4370
     - Device password is correct

4. **Save Device**
   - Click **OK** to save device configuration
   - Device should now appear in device list with green status

---

## Part 4: Configure Device Communication Settings

### Step 4: Set Up Auto Download

1. **Device Settings**
   - Right-click on connected device → **Device Settings**
   - Go to **Communication** tab

2. **Configure Auto Download**
   - Enable **Auto Download** checkbox
   - Set **Download Interval**: `5` minutes (recommended)
   - Set **Connection Type**: TCP/IP
   - Click **OK** to save

3. **Manual Download Test**
   - Right-click device → **Download Attendance**
   - Verify attendance records are downloaded successfully
   - Check **Transaction Management** to see records

---

## Part 5: Employee Management and Enrollment

### Step 5: Add Employees to ZKBio Time.Net

1. **User Management**
   - Go to **User Management** (or press `F3`)
   - Click **Add User** or **Import Users**

2. **Add Employee Manually**
   - **Employee ID**: Must match your system Employee ID
   - **Name**: Employee full name
   - **Department**: Select or create department
   - **Privilege**: Select **User** (not Admin)
   - Click **OK** to save

3. **Import Employees (Bulk)**
   - Click **Import** → **Import from Excel/CSV**
   - Prepare Excel file with columns:
     - Employee ID
     - Name
     - Department
   - Select file and import
   - Verify all employees are imported correctly

### Step 6: Upload Users to Device

1. **Select Device and Users**
   - In User Management, select employees to enroll
   - Or select all users (Ctrl+A)

2. **Upload to Device**
   - Click **Upload User** button
   - Select target device (UF200-S)
   - Click **OK** to upload
   - Wait for upload completion

3. **Verify Users on Device**
   - On UF200-S device, go to **User Management**
   - Verify employees are listed
   - Employee IDs should match your system

---

## Part 6: Fingerprint Enrollment

### Step 7: Enroll Fingerprints

**Option A: Enroll via ZKBio Time.Net (Recommended)**

1. **Select User**
   - In User Management, select employee
   - Click **Enroll Fingerprint** button

2. **Follow Enrollment Process**
   - Select device (UF200-S)
   - Place finger on scanner
   - Follow on-screen instructions
   - Scan finger 3 times for better accuracy
   - Repeat for second finger (recommended: 2 fingers per user)

3. **Verify Enrollment**
   - Check enrollment status in user list
   - Status should show "Enrolled"

**Option B: Enroll Directly on Device**

1. **Access Device Menu**
   - On UF200-S, press **Menu**
   - Enter admin password
   - Go to **User Management**

2. **Select User and Enroll**
   - Select employee from list
   - Choose **Enroll Fingerprint**
   - Place finger on scanner
   - Follow device prompts
   - Scan 3 times per finger

---

## Part 7: Database Configuration

### Step 8: Configure Database (Optional - Advanced)

**Default SQLite Database:**
- Location: `C:\ZKTeco\ZKBioTime\attendance.db`
- No additional configuration needed
- Accessible from web system if shared

**MySQL/MSSQL Configuration:**

1. **System Settings**
   - Go to **System Settings → Database**
   - Select database type (MySQL or MSSQL)

2. **Enter Database Credentials**
   - **Server**: Database server IP/hostname
   - **Port**: Database port (3306 for MySQL, 1433 for MSSQL)
   - **Database Name**: Create database (e.g., `zkbiotime`)
   - **Username**: Database username
   - **Password**: Database password

3. **Test Connection**
   - Click **Test Connection**
   - If successful, click **OK** to save
   - ZKBio Time.Net will create necessary tables

---

## Part 8: Web System Integration

### Step 9: Configure Web System Settings

1. **Access Attendance Settings**
   - Login to web system
   - Navigate to **HR → Attendance → Settings**
   - Go to **Devices** tab

2. **Add UF200-S Device**
   - Click **Add Device**
   - Fill in device information:
     - **Device Name**: e.g., "Main Office UF200-S"
     - **Device Type**: Biometric
     - **Manufacturer**: ZKTeco
     - **Model**: UF200-S
     - **IP Address**: Device IP (e.g., `192.168.1.100`)
     - **Port**: `4370`

3. **Configure ZKBio Time.Net Integration**
   - Go to **Step 3: UF200-S Config** tab
   - Enter **ZKBio Time.Net Server IP**: PC IP running ZKBio Time.Net
   - Set **ZKBio Time.Net Port**: `4370` (default)
   - Set **Database Type**: SQLite (or MySQL/MSSQL if configured)
   - Set **Sync Interval**: `5` minutes
   - Enable **Automatic Sync**
   - Enable **Sync Employee Data to Device**

4. **Test Connection**
   - Click **Test Connection** button
   - Verify connection is successful
   - If failed, check:
     - ZKBio Time.Net is running
     - Database is accessible
     - Network connectivity between servers

5. **Save Configuration**
   - Click **Save Device**
   - Device configuration is now complete

---

## Part 9: Schedule Automatic Download

### Step 10: Set Up Scheduled Downloads

1. **Schedule Download**
   - In ZKBio Time.Net, go to **Tools → Schedule Download**
   - Click **Add Schedule**

2. **Configure Schedule**
   - **Schedule Name**: e.g., "Daily Attendance Download"
   - **Device**: Select UF200-S device
   - **Frequency**: Daily
   - **Time**: Set time (e.g., every 5 minutes or specific times)
   - **Action**: Download Attendance
   - Click **OK** to save

3. **Enable Schedule**
   - Check **Enable** checkbox
   - Schedule will run automatically

---

## Part 10: Verification and Testing

### Step 11: Test Complete System

1. **Test Biometric Attendance**
   - Have employee scan fingerprint on UF200-S
   - Verify attendance is recorded on device
   - Wait for auto download (5 minutes) or manually download
   - Check ZKBio Time.Net **Transaction Management**
   - Verify record appears in web system attendance list

2. **Test Manual Attendance**
   - In web system, click **Add Record**
   - Select employee and enter attendance manually
   - Verify record is saved

3. **Verify Data Sync**
   - Check attendance records in web system
   - Verify biometric records from device appear
   - Verify employee data matches between systems

---

## Troubleshooting

### Common Issues and Solutions

**Issue 1: Cannot Connect to Device**
- **Solution**: 
  - Verify device IP address is correct
  - Check network connectivity (ping device)
  - Verify firewall allows port 4370
  - Check device is powered on
  - Verify device password is correct

**Issue 2: Attendance Not Syncing to Web System**
- **Solution**:
  - Verify ZKBio Time.Net is running
  - Check database connection settings
  - Verify sync interval is enabled
  - Check web system logs for errors
  - Ensure database is accessible from web server

**Issue 3: Employee ID Mismatch**
- **Solution**:
  - Ensure Employee ID in ZKBio Time.Net matches system Employee ID
  - Re-upload users to device if IDs were changed
  - Verify employee exists in web system

**Issue 4: Fingerprint Not Recognized**
- **Solution**:
  - Re-enroll fingerprint with better quality
  - Clean fingerprint scanner
  - Ensure finger is placed correctly
  - Enroll multiple fingers for better recognition

**Issue 5: Database Connection Failed**
- **Solution**:
  - Verify database server is running
  - Check database credentials
  - Verify network connectivity to database server
  - Check database permissions

---

## Maintenance

### Daily Tasks
- Monitor ZKBio Time.Net is running
- Check device connection status
- Verify attendance records are syncing

### Weekly Tasks
- Review attendance records for accuracy
- Check device logs for errors
- Backup ZKBio Time.Net database

### Monthly Tasks
- Clean fingerprint scanner
- Update ZKBio Time.Net software if available
- Review and optimize sync intervals
- Backup all attendance data

---

## Support and Resources

- **ZKTeco Official Website**: https://www.zkteco.com
- **ZKBio Time.Net Documentation**: Included in software installation
- **Device Manual**: UF200-S user manual
- **Technical Support**: Contact ZKTeco support or your system administrator

---

## Quick Reference

### Default Settings
- **Device Port**: 4370
- **Device Username**: admin
- **Default Password**: (usually empty or "admin")
- **Database Location**: `C:\ZKTeco\ZKBioTime\attendance.db`
- **Sync Interval**: 5 minutes (recommended)

### Important IPs to Note
- **Device IP**: `192.168.1.100` (example - use your actual device IP)
- **ZKBio Time.Net Server IP**: `192.168.1.50` (example - use your PC IP)
- **Web Server IP**: (your web server IP address)

### Ports Used
- **4370**: ZKTeco device communication port
- **3306**: MySQL database port (if using MySQL)
- **1433**: MSSQL database port (if using MSSQL)

---

**Last Updated**: 2025
**Version**: 1.0
**Device Model**: ZKTeco UF200-S
**Software**: ZKBio Time.Net







