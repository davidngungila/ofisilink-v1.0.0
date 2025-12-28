# ZKTeco Device - Live Server Configuration Guide

## 🚀 Your Live Server Information

- **Domain**: `ofisilink.com`
- **Live URL**: `https://live.ofisilink.com/`
- **Server IP**: `67.227.213.152`
- **Home Directory**: `/home/ofisilink`
- **Push SDK Endpoints**: 
  - Ping: `https://live.ofisilink.com/iclock/getrequest`
  - Data: `https://live.ofisilink.com/iclock/cdata`

---

## 📋 Quick Configuration Steps

### Step 1: Update Environment Variables

Edit your `.env` file on the live server:

```env
# ZKTeco Push SDK Configuration
ZKTECO_PUSH_SDK_ENABLED=true
ZKTECO_SERVER_IP=live.ofisilink.com
ZKTECO_SERVER_PORT=443
ZKTECO_PUSH_SDK_ENDPOINT=/iclock/getrequest
ZKTECO_PUSH_SDK_DATA_ENDPOINT=/iclock/cdata

# Direct Connection (if using VPN or port forwarding)
ZKTECO_IP=YOUR_DEVICE_IP
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0
```

### Step 2: Configure ZKTeco Device

On your ZKTeco device:

1. **Press MENU** button on device
2. Go to: **System** → **Communication** → **TCP/IP** → **Push SDK** (or **ADMS**)
3. Enable **Push SDK** (set to **ON**)
4. Configure settings:
   - **Server IP**: `live.ofisilink.com` (or `67.227.213.152`)
   - **Server Port**: `443` (HTTPS) or `80` (HTTP - not recommended)
   - **Server Path**: `/iclock/getrequest`
   - **Protocol**: HTTPS (recommended) or HTTP
5. **Save** settings

### Step 3: Verify SSL Certificate

Ensure your SSL certificate is installed and valid:

1. Login to cPanel: `https://live.ofisilink.com:2083`
2. Go to **SSL/TLS Status**
3. Install Let's Encrypt certificate (free) if not already installed
4. Verify HTTPS works: `https://live.ofisilink.com`

### Step 4: Test Endpoints

#### Test Ping Endpoint:
```bash
curl "https://live.ofisilink.com/iclock/getrequest?SN=TEST123"
# Expected response: OK
```

#### Test from Browser:
Visit: `https://live.ofisilink.com/iclock/getrequest?SN=TEST123`
Should return: `OK`

---

## 🔧 cPanel Configuration

### 1. Enable PHP Sockets Extension

1. Login to cPanel: `https://live.ofisilink.com:2083`
2. Go to **Software** → **MultiPHP INI Editor**
3. Select your PHP version (PHP 8.1+ recommended)
4. Find `extension=sockets`
5. Enable it (remove `;` if commented)
6. Click **Save**

### 2. Verify PHP Extensions

Create test file: `public/phpinfo.php`
```php
<?php phpinfo(); ?>
```

Visit: `https://live.ofisilink.com/phpinfo.php`
- Search for "sockets" - should be enabled
- **Delete** `phpinfo.php` after verification (security)

### 3. Check File Permissions

Ensure Laravel can write logs:
```bash
# Via SSH (if available)
cd /home/ofisilink/public_html/ofisi
chmod -R 775 storage bootstrap/cache
chown -R ofisilink:ofisilink storage bootstrap/cache
```

### 4. Clear Cache

```bash
# Via SSH or cPanel Terminal
cd /home/ofisilink/public_html/ofisi
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

---

## 🌐 Network Configuration Options

### Option 1: Push SDK (Recommended) ⭐

**Best for**: Cloud servers, real-time data, multiple devices

**Device Configuration**:
- Server IP: `live.ofisilink.com` or `67.227.213.152`
- Server Port: `443` (HTTPS)
- Server Path: `/iclock/getrequest`
- Protocol: HTTPS

**Advantages**:
- ✅ No direct TCP connection needed
- ✅ Works through firewalls
- ✅ Real-time data push
- ✅ Automatic device registration

### Option 2: VPN Connection

**Best for**: Secure direct connection

**Setup**:
1. Setup VPN server near your device
2. Connect device to VPN network
3. Connect cloud server to VPN
4. Use VPN IP addresses in configuration

### Option 3: Port Forwarding

**Best for**: Quick setup, single device

**Setup**:
1. Configure router port forwarding (port 4370)
2. Use public IP: `67.227.213.152` (if static) or your device's public IP
3. Update `.env`: `ZKTECO_IP=YOUR_PUBLIC_IP`

---

## ✅ Testing Checklist

- [ ] SSL certificate installed and valid
- [ ] PHP sockets extension enabled
- [ ] Environment variables updated
- [ ] Cache cleared
- [ ] Device configured with server details
- [ ] Ping endpoint tested: `https://live.ofisilink.com/iclock/getrequest?SN=TEST`
- [ ] Device shows "Connected" or "Online" status
- [ ] Test attendance scan on device
- [ ] Check Laravel logs for attendance records

---

## 🧪 Test Commands

### Via SSH (if available):
```bash
cd /home/ofisilink/public_html/ofisi

# Test connection
php artisan tinker
$zk = new \App\Services\ZKTecoService('DEVICE_IP', 4370, 0);
$zk->connect();

# Check logs
tail -f storage/logs/laravel.log | grep "Push SDK"
```

### Via cPanel Terminal:
```bash
cd ~/public_html/ofisi
php artisan route:list | grep iclock
```

### Test Endpoints:
```bash
# Ping endpoint
curl "https://live.ofisilink.com/iclock/getrequest?SN=TEST123"

# Data endpoint (test)
curl -X POST "https://live.ofisilink.com/iclock/cdata?SN=TEST123&table=ATTLOG&c=log" \
  -H "Content-Type: text/plain" \
  -d "PIN=1001	DateTime=2025-11-30 14:32:13	Verified=0	Status=0"
```

---

## 📊 Monitoring

### Check Device Status:
1. Login to your system: `https://live.ofisilink.com`
2. Go to **HR → Attendance → Settings → Devices**
3. View registered devices and their status

### Monitor Logs:
```bash
# Via SSH
tail -f /home/ofisilink/public_html/ofisi/storage/logs/laravel.log | grep -i zkteco

# Or via cPanel File Manager
# Navigate to: storage/logs/laravel.log
```

### Check Device Connectivity:
- Device should ping server every few seconds
- Check logs for "ZKTeco Push SDK ping" messages
- Device status should show as "Online" in database

---

## 🔒 Security Recommendations

1. **Use HTTPS**: Always use port 443 (HTTPS) for Push SDK
2. **SSL Certificate**: Ensure valid SSL certificate is installed
3. **Firewall**: Consider restricting access to known device IPs (if possible)
4. **Rate Limiting**: Already implemented in routes
5. **Log Monitoring**: Regularly check logs for suspicious activity

---

## 🐛 Troubleshooting

### Issue: Device Cannot Connect
**Solutions**:
- Verify device can reach `live.ofisilink.com`
- Check if HTTPS port 443 is accessible
- Verify SSL certificate is valid
- Check device network settings

### Issue: 419 CSRF Token Error
**Solutions**:
- Verify CSRF exemption is configured in `bootstrap/app.php`
- Clear cache: `php artisan config:clear`
- Check routes are under `iclock/*` prefix

### Issue: PHP Sockets Not Available
**Solutions**:
- Enable sockets extension in cPanel MultiPHP INI Editor
- Restart PHP-FPM via cPanel or SSH
- Verify in `phpinfo.php`

### Issue: Attendance Not Syncing
**Solutions**:
- Check device Push SDK is enabled
- Verify device serial number matches
- Check Laravel logs for errors
- Verify user has `enroll_id` set

---

## 📝 Configuration Summary

### Environment Variables (.env):
```env
# Push SDK (Recommended)
ZKTECO_PUSH_SDK_ENABLED=true
ZKTECO_SERVER_IP=live.ofisilink.com
ZKTECO_SERVER_PORT=443
ZKTECO_PUSH_SDK_ENDPOINT=/iclock/getrequest
ZKTECO_PUSH_SDK_DATA_ENDPOINT=/iclock/cdata

# Direct Connection (Alternative)
ZKTECO_IP=YOUR_DEVICE_IP
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0
```

### Device Settings:
- **Server IP**: `live.ofisilink.com` or `67.227.213.152`
- **Server Port**: `443` (HTTPS)
- **Server Path**: `/iclock/getrequest`
- **Protocol**: HTTPS

---

## 🎯 Quick Start

1. **Update `.env`** with your server details
2. **Enable PHP sockets** in cPanel
3. **Configure device** with Push SDK settings
4. **Test endpoints** using curl or browser
5. **Monitor logs** for device activity
6. **Test attendance** by scanning on device

---

## 📞 Support

- **Live URL**: https://live.ofisilink.com/
- **cPanel**: https://live.ofisilink.com:2083
- **Logs**: `/home/ofisilink/public_html/ofisi/storage/logs/laravel.log`
- **Documentation**: See `ZKTECO_PUSH_SDK_IMPLEMENTATION_STATUS.md`

---

**Last Updated**: 2025
**Server**: live.ofisilink.com
**Status**: ✅ Ready for Configuration




