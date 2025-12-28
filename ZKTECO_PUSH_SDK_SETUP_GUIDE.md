# ZKTeco Push SDK Setup Guide - Real-Time Attendance Updates

## Overview

The ZKTeco Push SDK (ADMS Protocol) enables **real-time attendance tracking** by allowing your UF200-S device to automatically push attendance records to your Laravel system as soon as employees scan their fingerprints.

---

## 📥 Download Push SDK

### Option 1: Download from ZKTeco Official Website

1. **Visit ZKTeco Support:**
   - Go to: https://www.zkteco.com/en/support/downloads/
   - Search for: "Push SDK" or "ADMS SDK"

2. **Download Files:**
   - **Push SDK Package** (usually includes):
     - `PushSDK.dll` (Windows)
     - `PushSDK.so` (Linux)
     - Documentation PDF
     - Example code

3. **Alternative Direct Links:**
   - ZKTeco Developer Portal: https://developer.zkteco.com/
   - GitHub (Community): Search "ZKTeco Push SDK"

### Option 2: Use Built-in Device Push Feature

UF200-S devices have built-in Push SDK support. You only need to configure the device settings.

---

## 🔧 Configuration Steps

### Step 1: Enable PHP Sockets Extension

**⚠️ IMPORTANT:** The web server must have PHP sockets extension enabled.

#### For Laragon:

1. **Open PHP.ini:**
   ```
   C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.ini
   ```

2. **Find this line:**
   ```ini
   ;extension=sockets
   ```

3. **Remove the semicolon:**
   ```ini
   extension=sockets
   ```

4. **Save the file**

5. **Restart Laragon:**
   - Right-click Laragon tray icon → **Stop All**
   - Wait 5 seconds
   - Right-click → **Start All**

6. **Verify:**
   - Visit: `http://localhost/check_php_config.php`
   - Should show: "Sockets extension is LOADED ✓"

#### For Other Servers:

- **XAMPP:** Edit `C:\xampp\php\php.ini`
- **WAMP:** Edit `C:\wamp\bin\php\php8.x.x\php.ini`
- **Linux:** Edit `/etc/php/8.x/apache2/php.ini` or `/etc/php/8.x/fpm/php.ini`

---

### Step 2: Configure Device Push URL

#### In ZKBio Time.Net:

1. **Open ZKBio Time.Net** on your Windows PC

2. **Go to Device Management:**
   - Right-click your UF200-S device
   - Select **Device Settings** or **Communication Settings**

3. **Configure Push URL:**
   - **Push URL:** `http://your-server-ip/iclock/getrequest`
   - **Data URL:** `http://your-server-ip/iclock/cdata`
   - **Enable Push:** ✓ Checked
   - **Push Interval:** 5 seconds (recommended)

4. **Click OK** to save

#### Directly on UF200-S Device:

1. **Access Device Menu:**
   - Press device menu button
   - Enter admin password

2. **Go to Communication Settings:**
   - Navigate: **System → Communication → Push Settings**

3. **Configure:**
   - **Server IP:** Your Laravel server IP (e.g., `192.168.1.100`)
   - **Server Port:** `80` (HTTP) or `443` (HTTPS)
   - **Push URL:** `/iclock/getrequest`
   - **Data URL:** `/iclock/cdata`
   - **Enable Push:** `Yes`

4. **Save and Restart Device**

---

### Step 3: Verify Laravel Endpoints

Your Laravel system already has Push SDK endpoints configured:

#### Endpoints Available:

1. **Device Ping/Command Request:**
   ```
   GET http://your-domain.com/iclock/getrequest?SN=DEVICE_SERIAL
   ```

2. **Device Data Push:**
   ```
   POST http://your-domain.com/iclock/cdata?SN=DEVICE_SERIAL&table=ATTLOG&c=log
   ```

#### Test Endpoints:

1. **Test Ping Endpoint:**
   ```bash
   curl "http://localhost/iclock/getrequest?SN=TEST123"
   ```
   Should return: `OK`

2. **Check Routes:**
   ```bash
   php artisan route:list | grep iclock
   ```

---

### Step 4: Configure Firewall

**Important:** Your device needs to reach your Laravel server.

#### Windows Firewall:

1. **Open Windows Defender Firewall**
2. **Allow Inbound Rule:**
   - Port: `80` (HTTP) or `443` (HTTPS)
   - Protocol: `TCP`
   - Action: `Allow`

#### Router/Network:

- Ensure device and server are on the same network
- If using public IP, configure port forwarding

---

## 🧪 Testing Push SDK

### Test 1: Device Ping

1. **Configure device** with Push URL
2. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```
3. **Device should ping** every 5-10 seconds
4. **Look for:**
   ```
   ZKTeco Push SDK ping {"sn":"DEVICE_SERIAL","ip":"192.168.1.100"}
   ```

### Test 2: Attendance Push

1. **Scan fingerprint** on device
2. **Check logs** for:
   ```
   ZKTeco Push SDK data received {"sn":"DEVICE_SERIAL","table":"ATTLOG","command":"log"}
   ```
3. **Check database:**
   ```sql
   SELECT * FROM attendances ORDER BY created_at DESC LIMIT 10;
   ```

---

## 📋 Configuration Checklist

- [ ] PHP sockets extension enabled in php.ini
- [ ] Web server restarted (Laragon/Apache/Nginx)
- [ ] Device Push URL configured in ZKBio Time.Net
- [ ] Device Push URL configured on UF200-S device
- [ ] Firewall allows incoming connections on port 80/443
- [ ] Device and server on same network
- [ ] Laravel routes accessible (`/iclock/getrequest`, `/iclock/cdata`)
- [ ] Test ping successful
- [ ] Test attendance push successful

---

## 🔍 Troubleshooting

### Issue: "PHP sockets extension is not loaded"

**Solution:**
1. Check php.ini location: Visit `http://localhost/check_php_config.php`
2. Edit the correct php.ini file
3. Uncomment `extension=sockets`
4. **Restart web server** (not just PHP)
5. Clear Laravel cache: `php artisan config:clear`

### Issue: Device not pinging

**Check:**
- Device Push URL is correct
- Server IP is reachable from device network
- Firewall is not blocking connections
- Laravel routes are accessible

**Test:**
```bash
# From device network, test:
curl http://SERVER_IP/iclock/getrequest?SN=TEST
```

### Issue: Attendance not being received

**Check:**
- Laravel logs for errors
- Database connection
- User enrollment (employee must be enrolled on device)
- Device serial number matches

---

## 📚 Additional Resources

- **ZKTeco Official Documentation:** https://www.zkteco.com/en/support/
- **Push SDK API Reference:** See `DEVICE_API_DOCUMENTATION.md`
- **PHP Configuration Checker:** `http://localhost/check_php_config.php`
- **PHP Info:** `http://localhost/phpinfo.php`

---

## 🎯 Quick Start Summary

1. **Enable sockets:** Edit `php.ini` → `extension=sockets` → Restart server
2. **Configure device:** Set Push URL to `http://YOUR_SERVER_IP/iclock/getrequest`
3. **Test:** Scan fingerprint → Check Laravel logs → Verify attendance in database

---

**Need Help?** Check the troubleshooting section or review Laravel logs at `storage/logs/laravel.log`










