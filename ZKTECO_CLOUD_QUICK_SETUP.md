# ZKTeco Cloud Server Quick Setup Guide

## 🚀 Fast Setup (3 Methods)

### Method 1: VPN (Recommended) ⭐
**Best for**: Production, Security

1. **Setup VPN** between device location and cloud server
2. **Device IP on VPN**: `192.168.1.100` (example)
3. **Update .env**:
   ```env
   ZKTECO_IP=192.168.1.100
   ZKTECO_PORT=4370
   ZKTECO_PASSWORD=0
   ```
4. **Test**: `php artisan tinker` → `$zk = new \App\Services\ZKTecoService('192.168.1.100'); $zk->connect();`

---

### Method 2: Port Forwarding
**Best for**: Quick setup, single device

1. **Router Setup**:
   - External Port: `4370`
   - Internal IP: Device IP (e.g., `192.168.1.100`)
   - Protocol: TCP

2. **Get Public IP**: `curl ifconfig.me`

3. **Update .env**:
   ```env
   ZKTECO_IP=YOUR_PUBLIC_IP
   ZKTECO_PORT=4370
   ZKTECO_PASSWORD=0
   ```

4. **Test**: From cloud server: `telnet YOUR_PUBLIC_IP 4370`

---

### Method 3: Push SDK (Real-time) ⭐⭐
**Best for**: Real-time attendance, multiple devices

1. **On Device**: 
   - Communication → TCP/IP → Enable Push SDK
   - Server IP: `yourdomain.com`
   - Server Port: `443` (HTTPS)

2. **In Laravel** (routes/web.php):
   ```php
   Route::post('/iclock/getrequest', [ZKTecoController::class, 'handlePushRequest']);
   Route::post('/iclock/cdata', [ZKTecoController::class, 'handlePushData']);
   ```

3. **Update .env**:
   ```env
   ZKTECO_PUSH_SDK_ENABLED=true
   ZKTECO_SERVER_IP=yourdomain.com
   ZKTECO_SERVER_PORT=443
   ```

---

## 🔧 cPanel Configuration

### Enable PHP Sockets
1. cPanel → **Software** → **MultiPHP INI Editor**
2. Find `extension=sockets`
3. Enable it
4. **Save**

### Test PHP Extensions
```bash
# Create: public/phpinfo.php
<?php phpinfo(); ?>
# Visit: https://yourdomain.com/phpinfo.php
# Search: "sockets"
```

### SSL Certificate (for Push SDK)
1. cPanel → **SSL/TLS Status**
2. Install Let's Encrypt (free)
3. Enable for your domain

---

## ✅ Quick Test

### Via SSH:
```bash
cd /home/username/public_html/ofisi
php artisan tinker

# Test connection
$zk = new \App\Services\ZKTecoService('DEVICE_IP', 4370, 0);
$zk->connect();
```

### Via Web:
1. Login to your system
2. Go to **HR → Attendance → Settings → Devices**
3. Click **Add Device**
4. Enter IP, Port, Comm Key
5. Click **Test Connection**

---

## 🐛 Common Issues

| Issue | Solution |
|-------|----------|
| Connection timeout | Check VPN/port forwarding, verify device IP |
| `socket_create()` error | Enable sockets extension in cPanel |
| Device not reachable | Check network, firewall, device power |
| SSL errors (Push SDK) | Install SSL certificate in cPanel |

---

## 📝 Environment Variables

```env
# Direct Connection
ZKTECO_IP=192.168.1.100
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0

# Push SDK
ZKTECO_PUSH_SDK_ENABLED=true
ZKTECO_SERVER_IP=yourdomain.com
ZKTECO_SERVER_PORT=443
ZKTECO_PUSH_SDK_ENDPOINT=/iclock/getrequest
ZKTECO_PUSH_SDK_DATA_ENDPOINT=/iclock/cdata
```

---

## 🎯 Recommended Setup

1. ✅ Use **VPN (Method 1)** for security
2. ✅ Enable **Push SDK (Method 3)** for real-time data
3. ✅ Use **HTTPS** (port 443) for Push SDK
4. ✅ Monitor logs: `tail -f storage/logs/laravel.log | grep ZKTeco`

---

**Need more details?** See `ZKTECO_CLOUD_SERVER_CONNECTION_GUIDE.md`




