# ZKTeco Device Connection to Cloud Server (cPanel) - Complete Guide

## Overview

Connecting a ZKTeco biometric device to a cloud server hosted on cPanel requires special network configuration because:
- ZKTeco devices use **TCP/IP direct connections** on port **4370**
- Cloud servers typically don't allow direct TCP connections from external devices
- The device must be accessible from your cloud server's IP address

---

## 🎯 Connection Methods

There are **3 main approaches** to connect ZKTeco devices to a cloud server:

### Method 1: VPN Connection (Recommended for Production)
### Method 2: Port Forwarding with Static IP
### Method 3: Reverse Connection (Push SDK) - Best for Real-time

---

## 📋 Method 1: VPN Connection (Most Secure)

### Prerequisites
- VPN server (OpenVPN, WireGuard, or commercial VPN)
- Static IP for your ZKTeco device (or router with port forwarding)
- VPN client access on your cloud server

### Step-by-Step Setup

#### 1. Setup VPN Server
```bash
# Install OpenVPN on a server/router near your ZKTeco device
# Or use a commercial VPN service
```

#### 2. Configure ZKTeco Device Network
- Connect device to network with VPN access
- Assign static IP to device (e.g., `192.168.1.100`)
- Ensure device can reach VPN server

#### 3. Configure Cloud Server VPN Client
```bash
# On your cloud server (via SSH in cPanel)
# Install OpenVPN client
yum install openvpn  # For CentOS/RHEL
# OR
apt-get install openvpn  # For Ubuntu/Debian

# Connect to VPN
openvpn --config /path/to/client.ovpn
```

#### 4. Update Laravel Configuration
```env
# .env file on cloud server
ZKTECO_IP=192.168.1.100  # Device IP on VPN network
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0
```

#### 5. Test Connection
```bash
# SSH into cloud server
cd /home/username/public_html/ofisi
php artisan tinker

# Test connection
$zkteco = new \App\Services\ZKTecoService('192.168.1.100', 4370, 0);
$zkteco->connect();
```

---

## 📋 Method 2: Port Forwarding with Static IP

### Prerequisites
- Static public IP address for your ZKTeco device location
- Router with port forwarding capability
- Firewall rules allowing port 4370

### Step-by-Step Setup

#### 1. Configure Router Port Forwarding
1. Access router admin panel (usually `192.168.1.1`)
2. Navigate to **Port Forwarding** or **Virtual Server**
3. Add rule:
   - **External Port**: `4370`
   - **Internal IP**: Device IP (e.g., `192.168.1.100`)
   - **Internal Port**: `4370`
   - **Protocol**: TCP
   - **Status**: Enabled

#### 2. Get Your Public IP Address
```bash
# Find your public IP
curl ifconfig.me
# Or visit: https://whatismyipaddress.com
```

#### 3. Configure Firewall
- Allow incoming connections on port 4370
- Restrict access to your cloud server's IP only (if possible)

#### 4. Update Laravel Configuration
```env
# .env file on cloud server
ZKTECO_IP=YOUR_PUBLIC_IP  # Your static public IP
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0
```

#### 5. Test Connection
```bash
# From cloud server, test connectivity
telnet YOUR_PUBLIC_IP 4370

# If connection succeeds, test in Laravel
php artisan tinker
$zkteco = new \App\Services\ZKTecoService('YOUR_PUBLIC_IP', 4370, 0);
$zkteco->connect();
```

---

## 📋 Method 3: Reverse Connection (Push SDK) - Recommended for Real-time

This method uses ZKTeco's **Push SDK** where the device **pushes data** to your server instead of pulling.

### Prerequisites
- ZKTeco device with Push SDK support
- Public HTTPS endpoint on your cloud server
- SSL certificate (required for HTTPS)

### Step-by-Step Setup

#### 1. Configure Device to Push to Cloud Server

On your ZKTeco device:
1. Go to **Communication** → **TCP/IP**
2. Enable **Push SDK** or **ADMS**
3. Set **Server IP**: Your cloud server's public IP or domain
4. Set **Server Port**: `443` (HTTPS) or `80` (HTTP - not recommended)
5. Set **Server Path**: `/iclock/getrequest` (or your custom endpoint)

#### 2. Create Push SDK Endpoints in Laravel

Add routes in `routes/web.php`:
```php
// ZKTeco Push SDK endpoints
Route::post('/iclock/getrequest', [ZKTecoController::class, 'handlePushRequest']);
Route::post('/iclock/cdata', [ZKTecoController::class, 'handlePushData']);
```

#### 3. Implement Push Handler in Controller

```php
// In ZKTecoController.php
public function handlePushRequest(Request $request)
{
    // Device sends: GET /iclock/getrequest?SN=DEVICE_SERIAL
    $sn = $request->get('SN');
    
    // Return commands for device
    return response("OK", 200)
        ->header('Content-Type', 'text/plain');
}

public function handlePushData(Request $request)
{
    // Device sends attendance data
    $data = $request->all();
    
    // Process attendance records
    // Save to database
    
    return response("OK", 200)
        ->header('Content-Type', 'text/plain');
}
```

#### 4. Update Configuration
```env
# .env file
ZKTECO_PUSH_SDK_ENABLED=true
ZKTECO_SERVER_IP=yourdomain.com
ZKTECO_SERVER_PORT=443
```

---

## 🔧 cPanel-Specific Configuration

### 1. Enable PHP Sockets Extension

#### Via cPanel MultiPHP INI Editor:
1. Login to cPanel
2. Go to **Software** → **MultiPHP INI Editor**
3. Select your PHP version
4. Find `extension=sockets` and enable it
5. Click **Save**

#### Via SSH (if you have access):
```bash
# Edit php.ini
nano /opt/cpanel/ea-php81/root/etc/php.ini

# Find and uncomment:
extension=sockets

# Restart PHP-FPM
/scripts/restartsrv_php-fpm
```

### 2. Check PHP Extensions
```bash
# Create test file: public/phpinfo.php
<?php phpinfo(); ?>

# Visit: https://yourdomain.com/phpinfo.php
# Search for "sockets" - should show as enabled
```

### 3. Configure Firewall Rules

#### In cPanel:
1. Go to **Security** → **IP Blocker**
2. Allow your ZKTeco device IP (if using Method 2)
3. Or allow VPN subnet (if using Method 1)

#### Via SSH (if available):
```bash
# Allow port 4370 (if using direct connection)
iptables -A INPUT -p tcp --dport 4370 -j ACCEPT
iptables-save
```

### 4. Test Network Connectivity

```bash
# SSH into cloud server
# Test if you can reach device
ping DEVICE_IP

# Test port connectivity
telnet DEVICE_IP 4370
# OR
nc -zv DEVICE_IP 4370
```

---

## 🔒 Security Considerations

### 1. Use HTTPS for Push SDK
- Always use HTTPS (port 443) for Push SDK endpoints
- Install SSL certificate via cPanel → **SSL/TLS Status**

### 2. Restrict Access
- Use firewall rules to limit access to device IP only
- Consider using API keys for authentication

### 3. VPN is Most Secure
- Method 1 (VPN) is recommended for production
- Encrypts all traffic between device and server

### 4. Rate Limiting
```php
// In routes/web.php
Route::middleware('throttle:60,1')->group(function () {
    Route::post('/iclock/getrequest', [ZKTecoController::class, 'handlePushRequest']);
    Route::post('/iclock/cdata', [ZKTecoController::class, 'handlePushData']);
});
```

---

## 🧪 Testing Connection from Cloud Server

### Test Script (via SSH)

Create `test_cloud_connection.php`:
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use App\Services\ZKTecoService;

$ip = 'YOUR_DEVICE_IP';
$port = 4370;
$password = 0;

echo "Testing ZKTeco connection to: $ip:$port\n";

try {
    $zkteco = new ZKTecoService($ip, $port, $password);
    
    if ($zkteco->connect()) {
        echo "✅ Connection successful!\n";
        
        // Get device info
        $info = $zkteco->getDeviceInfo();
        print_r($info);
        
        $zkteco->disconnect();
    } else {
        echo "❌ Connection failed\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

Run:
```bash
cd /home/username/public_html/ofisi
php test_cloud_connection.php
```

---

## 🐛 Troubleshooting

### Issue 1: Connection Timeout
**Symptoms**: Connection times out from cloud server

**Solutions**:
- Check if device IP is accessible: `ping DEVICE_IP`
- Verify port 4370 is open: `telnet DEVICE_IP 4370`
- Check firewall rules on device/router
- Ensure device is on same network (VPN) or has public IP

### Issue 2: PHP Sockets Not Available
**Symptoms**: `Call to undefined function socket_create()`

**Solutions**:
- Enable sockets extension in cPanel MultiPHP INI Editor
- Or use `fsockopen` method (already implemented in your codebase)

### Issue 3: Device Not Reachable
**Symptoms**: Cannot ping or connect to device

**Solutions**:
- Verify device IP address is correct
- Check network connectivity
- Ensure device is powered on and connected
- Verify VPN connection (if using Method 1)
- Check router port forwarding (if using Method 2)

### Issue 4: SSL Certificate Issues (Push SDK)
**Symptoms**: Device cannot connect via HTTPS

**Solutions**:
- Install valid SSL certificate in cPanel
- Ensure certificate is not expired
- Use Let's Encrypt (free) via cPanel → **SSL/TLS Status**

---

## 📝 Recommended Setup for Production

### Best Practice Configuration:

1. **Use VPN (Method 1)** for secure connection
2. **Enable Push SDK (Method 3)** for real-time attendance
3. **Use HTTPS** for all Push SDK endpoints
4. **Implement rate limiting** on endpoints
5. **Monitor logs** regularly:
   ```bash
   tail -f storage/logs/laravel.log | grep ZKTeco
   ```

### Environment Variables (.env):
```env
# ZKTeco Configuration
ZKTECO_IP=192.168.1.100  # VPN IP or public IP
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0
ZKTECO_DEVICE_ID=6

# Push SDK Configuration
ZKTECO_PUSH_SDK_ENABLED=true
ZKTECO_SERVER_IP=yourdomain.com
ZKTECO_SERVER_PORT=443
ZKTECO_PUSH_SDK_ENDPOINT=/iclock/getrequest
ZKTECO_PUSH_SDK_DATA_ENDPOINT=/iclock/cdata

# Connection Settings
ZKTECO_TIMEOUT=60
ZKTECO_RETRY_ATTEMPTS=3
```

---

## 🚀 Quick Start Checklist

- [ ] Choose connection method (VPN recommended)
- [ ] Configure network (VPN/Port Forwarding)
- [ ] Enable PHP sockets extension in cPanel
- [ ] Update `.env` with device IP
- [ ] Test connection via SSH
- [ ] Configure Push SDK (if using Method 3)
- [ ] Install SSL certificate (for HTTPS)
- [ ] Set up firewall rules
- [ ] Test from web interface
- [ ] Monitor logs for errors

---

## 📞 Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Test network connectivity first
3. Verify PHP extensions are enabled
4. Check firewall and security settings
5. Review device network configuration

---

## 📚 Additional Resources

- ZKTeco Official Documentation
- Laravel Network Configuration
- cPanel PHP Configuration Guide
- OpenVPN Setup Guide (for VPN method)

---

**Last Updated**: 2025
**Version**: 1.0




