# ZKTeco Push SDK - Complete Installation Guide

## ⚠️ Important Note

**ZKTeco Push SDK is NOT a PHP package to install via Composer.**

The Push SDK is:
- ✅ **Built into ZKTeco devices** (UF200-S has it built-in)
- ✅ **Built into ZKBio Time.Net** (Windows software)
- ✅ **Protocol/Feature** - not a library

**What you need:**
1. ✅ PHP sockets extension (already enabled)
2. 📥 ZKBio Time.Net software (Windows application)
3. ⚙️ Device configuration (set Push URL)

---

## 📥 Download ZKBio Time.Net

### Method 1: Official Website

1. **Visit ZKTeco Downloads:**
   ```
   https://www.zkteco.com/en/support/downloads/
   ```

2. **Search for:** "ZKBio Time.Net"

3. **Download:** Latest version for Windows

### Method 2: Direct Links

- **Product Page:** https://www.zkteco.com/en/product/software/zkbio-time-net/
- **Support Portal:** https://support.zkteco.com/
- **Contact Support:** support@zkteco.com

### Method 3: Use Download Helper

Run the batch file:
```bash
download_zkbiotime.bat
```

This will open the download page in your browser.

---

## 🔧 Installation Steps

### Step 1: Install ZKBio Time.Net

1. **Download** ZKBio Time.Net installer
2. **Run installer** on Windows PC
3. **Follow installation wizard**
4. **Launch** ZKBio Time.Net

### Step 2: Connect Device

1. **In ZKBio Time.Net:**
   - Go to **Device Management**
   - Click **Add Device** or **Search Device**
   - Enter device IP: `192.168.1.100` (your device IP)
   - Enter port: `4370`
   - Click **Connect**

### Step 3: Configure Push URL

1. **Right-click device** → **Device Settings**
2. **Go to Communication tab**
3. **Set Push URL:**
   ```
   http://YOUR_SERVER_IP/iclock/getrequest
   ```
4. **Set Data URL:**
   ```
   http://YOUR_SERVER_IP/iclock/cdata
   ```
5. **Enable Push:** ✓
6. **Push Interval:** 5 seconds
7. **Click OK**

### Step 4: Verify Laravel Endpoints

Your Laravel system already has these endpoints:

- ✅ `GET /iclock/getrequest` - Device ping
- ✅ `POST /iclock/cdata` - Device data push

**Test:**
```bash
curl "http://localhost/iclock/getrequest?SN=TEST"
# Should return: OK
```

---

## ✅ Verification Checklist

- [ ] ZKBio Time.Net downloaded and installed
- [ ] Device connected in ZKBio Time.Net
- [ ] Push URL configured: `http://YOUR_IP/iclock/getrequest`
- [ ] Data URL configured: `http://YOUR_IP/iclock/cdata`
- [ ] Push enabled in device settings
- [ ] PHP sockets extension loaded (check: `http://localhost/check_php_config.php`)
- [ ] Laravel endpoints accessible
- [ ] Device pinging Laravel (check logs)
- [ ] Attendance records being received

---

## 🧪 Testing

### Test 1: Check PHP Sockets

Visit: `http://localhost/check_php_config.php`

Should show: ✅ "Sockets extension is LOADED"

### Test 2: Check Device Ping

1. Configure device with Push URL
2. Check Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log
   ```
3. Should see:
   ```
   ZKTeco Push SDK ping {"sn":"DEVICE_SERIAL","ip":"192.168.1.100"}
   ```

### Test 3: Test Attendance Push

1. Scan fingerprint on device
2. Check logs for:
   ```
   ZKTeco Push SDK data received
   ```
3. Check database for new attendance record

---

## 📚 Resources

- **ZKTeco Official:** https://www.zkteco.com/en/
- **Downloads:** https://www.zkteco.com/en/support/downloads/
- **Support:** https://support.zkteco.com/
- **PHP Config Check:** `http://localhost/check_php_config.php`

---

## 🎯 Summary

**You don't install Push SDK as a package.** You:

1. ✅ **Enable PHP sockets** - Done (check with diagnostic page)
2. 📥 **Download ZKBio Time.Net** - From ZKTeco website
3. ⚙️ **Configure device** - Set Push URL in device settings
4. ✅ **Laravel ready** - Endpoints already configured

**That's it!** The Push SDK protocol works automatically once configured.

