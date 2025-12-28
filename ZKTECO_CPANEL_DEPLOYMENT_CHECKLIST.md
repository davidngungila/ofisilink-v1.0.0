# ZKTeco Device - cPanel Cloud Server Deployment Checklist

## 📋 Pre-Deployment Checklist

### Network Requirements
- [ ] ZKTeco device is powered on and connected to network
- [ ] Device has static IP address (or DHCP reservation)
- [ ] Device IP is accessible from your network
- [ ] Port 4370 is open on device/router firewall

### Cloud Server Requirements
- [ ] cPanel hosting account with SSH access (recommended)
- [ ] PHP 7.4+ installed
- [ ] Laravel application deployed
- [ ] SSL certificate installed (for Push SDK method)
- [ ] Domain name configured

---

## 🚀 Deployment Steps

### Step 1: Choose Connection Method

**Option A: VPN Connection** (Recommended for Production)
- Most secure
- Encrypted traffic
- Requires VPN server setup

**Option B: Port Forwarding**
- Quick setup
- Requires static public IP
- Less secure (exposes port 4370)

**Option C: Push SDK** (Recommended for Real-time)
- Device pushes data to server
- No direct TCP connection needed
- Best for multiple devices

---

### Step 2: Configure Network

#### For VPN Method:
1. [ ] Setup VPN server (OpenVPN/WireGuard)
2. [ ] Connect device to VPN network
3. [ ] Connect cloud server to VPN
4. [ ] Test connectivity: `ping DEVICE_VPN_IP`

#### For Port Forwarding Method:
1. [ ] Configure router port forwarding:
   - External Port: `4370`
   - Internal IP: Device IP
   - Protocol: TCP
2. [ ] Get public IP: `curl ifconfig.me`
3. [ ] Test from cloud server: `telnet PUBLIC_IP 4370`

#### For Push SDK Method:
1. [ ] Install SSL certificate in cPanel
2. [ ] Verify HTTPS works: `https://yourdomain.com`
3. [ ] Test endpoints:
   - `GET https://yourdomain.com/iclock/getrequest?SN=TEST`
   - Should return: `OK`

---

### Step 3: Configure cPanel

#### Enable PHP Sockets Extension:
1. [ ] Login to cPanel
2. [ ] Go to **Software** → **MultiPHP INI Editor**
3. [ ] Select your PHP version
4. [ ] Find `extension=sockets`
5. [ ] Enable it (remove `;` if commented)
6. [ ] Click **Save**

#### Verify PHP Extensions:
1. [ ] Create file: `public/phpinfo.php`
   ```php
   <?php phpinfo(); ?>
   ```
2. [ ] Visit: `https://yourdomain.com/phpinfo.php`
3. [ ] Search for "sockets" - should be enabled
4. [ ] **Delete** `phpinfo.php` after verification (security)

#### Configure Firewall (if using direct connection):
1. [ ] Go to **Security** → **IP Blocker** (optional)
2. [ ] Allow your device IP or VPN subnet
3. [ ] Or configure via SSH (if available):
   ```bash
   iptables -A INPUT -p tcp -s DEVICE_IP --dport 4370 -j ACCEPT
   ```

---

### Step 4: Update Laravel Configuration

#### Update `.env` file:

**For Direct Connection (VPN or Port Forwarding):**
```env
ZKTECO_IP=192.168.1.100  # Device IP (VPN or public IP)
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0  # Communication Key (usually 0)
ZKTECO_DEVICE_ID=6
```

**For Push SDK:**
```env
ZKTECO_PUSH_SDK_ENABLED=true
ZKTECO_SERVER_IP=yourdomain.com
ZKTECO_SERVER_PORT=443
ZKTECO_PUSH_SDK_ENDPOINT=/iclock/getrequest
ZKTECO_PUSH_SDK_DATA_ENDPOINT=/iclock/cdata
```

#### Clear Config Cache:
```bash
php artisan config:clear
php artisan cache:clear
```

---

### Step 5: Configure ZKTeco Device

#### For Direct Connection:
1. [ ] Device IP: Set static IP (e.g., `192.168.1.100`)
2. [ ] Port: `4370` (default)
3. [ ] Communication Key: `0` (default, or your key)

#### For Push SDK:
1. [ ] Go to Device Menu → **Communication** → **TCP/IP**
2. [ ] Enable **Push SDK** or **ADMS**
3. [ ] Server IP: `yourdomain.com` (or your server IP)
4. [ ] Server Port: `443` (HTTPS) or `80` (HTTP - not recommended)
5. [ ] Server Path: `/iclock/getrequest`
6. [ ] Save settings

---

### Step 6: Test Connection

#### Via SSH (Recommended):
```bash
# SSH into cloud server
cd /home/username/public_html/ofisi

# Test connection
php artisan tinker

# In tinker:
$zk = new \App\Services\ZKTecoService('DEVICE_IP', 4370, 0);
$zk->connect();
```

#### Via Web Interface:
1. [ ] Login to your system
2. [ ] Go to **HR → Attendance → Settings → Devices**
3. [ ] Click **Add Device** or edit existing
4. [ ] Enter:
   - IP Address: Device IP
   - Port: `4370`
   - Communication Key: `0`
5. [ ] Click **Test Connection**
6. [ ] Verify success message

#### For Push SDK:
1. [ ] Test ping endpoint:
   ```bash
   curl "https://yourdomain.com/iclock/getrequest?SN=TEST123"
   # Should return: OK
   ```
2. [ ] Check device logs for connection status
3. [ ] Monitor Laravel logs:
   ```bash
   tail -f storage/logs/laravel.log | grep "Push SDK"
   ```

---

### Step 7: Verify Endpoints (Push SDK)

The following endpoints should be accessible:

1. [ ] **Ping Endpoint:**
   - URL: `GET https://yourdomain.com/iclock/getrequest?SN=DEVICE_SERIAL`
   - Expected: `OK` response
   - Test: `curl "https://yourdomain.com/iclock/getrequest?SN=TEST"`

2. [ ] **Data Endpoint:**
   - URL: `POST https://yourdomain.com/iclock/cdata?SN=DEVICE_SERIAL&table=ATTLOG&c=log`
   - Expected: `OK` response
   - This is called automatically by device

---

### Step 8: Security Configuration

1. [ ] **Rate Limiting** (already implemented in routes)
   - Endpoints are throttled to prevent abuse

2. [ ] **SSL Certificate:**
   - [ ] Installed via cPanel → **SSL/TLS Status**
   - [ ] Let's Encrypt (free) recommended
   - [ ] HTTPS working: `https://yourdomain.com`

3. [ ] **Firewall Rules:**
   - [ ] Restrict access to known IPs (if possible)
   - [ ] Monitor failed connection attempts

4. [ ] **Log Monitoring:**
   ```bash
   # Monitor ZKTeco connections
   tail -f storage/logs/laravel.log | grep ZKTeco
   ```

---

### Step 9: Final Verification

#### Test Attendance Sync:
1. [ ] Register a user on the device
2. [ ] Scan fingerprint/face on device
3. [ ] Check if attendance appears in system:
   - **HR → Attendance → View Attendance**
   - Or via Push SDK logs

#### Test User Registration:
1. [ ] Create user in system
2. [ ] Register fingerprint via web interface
3. [ ] Verify user appears on device

#### Monitor Logs:
```bash
# Watch for errors
tail -f storage/logs/laravel.log

# Filter ZKTeco logs
tail -f storage/logs/laravel.log | grep -i zkteco
```

---

## 🐛 Troubleshooting

### Connection Timeout
- [ ] Verify device IP is correct
- [ ] Check network connectivity: `ping DEVICE_IP`
- [ ] Test port: `telnet DEVICE_IP 4370`
- [ ] Verify firewall rules
- [ ] Check VPN connection (if using VPN)

### PHP Sockets Error
- [ ] Verify sockets extension is enabled
- [ ] Check `phpinfo.php` output
- [ ] Restart PHP-FPM: `/scripts/restartsrv_php-fpm` (via SSH)

### Push SDK Not Working
- [ ] Verify SSL certificate is valid
- [ ] Test endpoint manually: `curl https://yourdomain.com/iclock/getrequest?SN=TEST`
- [ ] Check device network settings
- [ ] Verify device can reach your domain
- [ ] Check Laravel logs for errors

### Device Not Reachable
- [ ] Verify device is powered on
- [ ] Check network cable connection
- [ ] Verify device IP address
- [ ] Test from local network first
- [ ] Check router port forwarding (if using Method 2)

---

## 📊 Monitoring & Maintenance

### Daily Checks:
- [ ] Monitor Laravel logs for errors
- [ ] Verify attendance records are syncing
- [ ] Check device connectivity status

### Weekly Checks:
- [ ] Review connection logs
- [ ] Verify SSL certificate validity
- [ ] Check for failed sync attempts

### Monthly Checks:
- [ ] Review security logs
- [ ] Update device firmware (if needed)
- [ ] Backup device configurations

---

## 📝 Configuration Summary

### Environment Variables (.env):
```env
# Direct Connection
ZKTECO_IP=192.168.1.100
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0
ZKTECO_DEVICE_ID=6

# Push SDK
ZKTECO_PUSH_SDK_ENABLED=true
ZKTECO_SERVER_IP=yourdomain.com
ZKTECO_SERVER_PORT=443
ZKTECO_PUSH_SDK_ENDPOINT=/iclock/getrequest
ZKTECO_PUSH_SDK_DATA_ENDPOINT=/iclock/cdata

# Connection Settings
ZKTECO_TIMEOUT=60
ZKTECO_RETRY_ATTEMPTS=3
```

### Routes (Already Configured):
- `GET /iclock/getrequest` - Device ping
- `POST /iclock/cdata` - Data push
- `POST /zkteco/test-connection` - Test connection
- `POST /zkteco/device-info` - Get device info
- `POST /zkteco/users/sync-from-device` - Sync users
- `POST /zkteco/attendance/sync` - Sync attendance

---

## ✅ Deployment Complete

Once all items are checked:
1. [ ] Connection tested and working
2. [ ] Push SDK endpoints accessible (if using)
3. [ ] Attendance syncing correctly
4. [ ] Users can register fingerprints
5. [ ] Logs show no critical errors

**Your ZKTeco device is now connected to your cloud server! 🎉**

---

## 📞 Support Resources

- **Full Guide**: `ZKTECO_CLOUD_SERVER_CONNECTION_GUIDE.md`
- **Quick Setup**: `ZKTECO_CLOUD_QUICK_SETUP.md`
- **Laravel Logs**: `storage/logs/laravel.log`
- **ZKTeco Documentation**: Check device manual

---

**Last Updated**: 2025
**Version**: 1.0




