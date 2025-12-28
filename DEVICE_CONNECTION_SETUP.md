# Device Connection Setup - Server IP: 192.168.100.103

## 🖥️ Your Server Configuration

- **Server IP:** `192.168.100.103` (Your PC)
- **Server Port:** `8002` (Laravel server)
- **Network:** `192.168.100.x` (Same network as device)

---

## 📱 Device Configuration Required

### Step 1: Device Network Settings

On your ZKTeco device:

1. **Access Device Menu:**
   - Press **Menu** button on device
   - Enter admin password

2. **Set Network Settings:**
   - Navigate: **System → Communication → Network → TCP/IP**
   - **IP Address:** Set to device IP (e.g., `192.168.100.108`)
   - **Subnet Mask:** `255.255.255.0`
   - **Gateway:** `192.168.100.1` (or your router IP)
   - **Port:** `4370`
   - **Save settings**

3. **Set Communication Key:**
   - Navigate: **System → Communication → Comm Key**
   - **Set to:** `0` (zero)
   - **Save settings**

4. **Restart Device:**
   - Power off device
   - Wait 15 seconds
   - Power on device
   - Wait 60 seconds for full boot

---

## 🔧 Server Configuration

### Environment Variables (.env)

```env
# Your server IP (for Push SDK configuration)
ZKTECO_SERVER_IP=192.168.100.103
ZKTECO_SERVER_PORT=8002

# Device IP (the ZKTeco device IP)
ZKTECO_IP=192.168.100.108
ZKTECO_PORT=4370
ZKTECO_PASSWORD=0
```

---

## 🧪 Test Device Connection

### Option 1: Command Line Test (Recommended)

```bash
cd ofisi
php test_device_connection_only.php
```

Or with specific device IP:
```bash
php test_device_connection_only.php 192.168.100.108 4370 0
```

This will:
- Test network connectivity
- Test port connectivity
- Try all 4 connection methods
- Show which method works
- Display device information

### Option 2: Web Interface

1. Start server:
   ```bash
   php artisan serve --host=192.168.100.103 --port=8002
   ```

2. Open browser:
   ```
   http://192.168.100.103:8002/modules/hr/attendance
   ```

3. Click **Test Connection** button
4. Enter device IP: `192.168.100.108`
5. Port: `4370`
6. Password: `0`
7. Click **Test Connection**

---

## 🔍 Connection Methods Tested

The system automatically tries **4 different connection methods**:

1. **Method 1:** No password data (simplest) ⭐ Tried first
2. **Method 2:** Minimal command (header only)
3. **Method 3:** Password data (little-endian)
4. **Method 4:** Password data (big-endian)

**Stops on first success!**

---

## ✅ Success Indicators

When connection is successful, you'll see:

```
✓ CONNECTION SUCCESSFUL!
Working Method: Method 1: No password data (simplest)
Password Used: 0

Device Information:
  IP: 192.168.100.108
  Port: 4370
  Model: UF200-S
  Name: ZKTeco UF200-S
  Firmware: [version]
```

---

## 🐛 Troubleshooting

### Issue: "Device closed connection during authentication"

**Most Common Cause:** Wrong Communication Key

**Solution:**
1. On device: **System → Communication → Comm Key**
2. **Set to exactly 0** (zero)
3. **Save settings**
4. **Restart device** (power off/on)
5. **Wait 60 seconds** after restart
6. Try connection again

### Issue: "Cannot connect to port 4370"

**Solution:**
1. Check device is powered on
2. Verify device IP is correct
3. Test port: `Test-NetConnection -ComputerName 192.168.100.108 -Port 4370`
4. Check Windows Firewall (allow port 4370)
5. Ensure device and server are on same network (192.168.100.x)

### Issue: "Network unreachable"

**Solution:**
1. Ping device: `ping 192.168.100.108`
2. Check network cable/WiFi
3. Verify device IP settings
4. Ensure same network segment

---

## 📋 Quick Checklist

Before testing connection:

- [ ] Device is powered on
- [ ] Device IP is set correctly (e.g., 192.168.100.108)
- [ ] Device port is 4370
- [ ] Communication Key is 0 on device
- [ ] Device was restarted after changing Comm Key
- [ ] Server IP is 192.168.100.103
- [ ] Server and device are on same network (192.168.100.x)
- [ ] Firewall allows port 4370
- [ ] No other software (ZKBio Time.Net) is connected

---

## 🎯 Network Diagram

```
┌─────────────────────┐         ┌─────────────────────┐
│   Your PC Server     │         │   ZKTeco Device     │
│                      │         │                      │
│ IP: 192.168.100.103  │◄───────►│ IP: 192.168.100.108  │
│ Port: 8002 (HTTP)    │         │ Port: 4370 (ZKTeco) │
│                      │         │                      │
│ Laravel Application  │         │ UF200-S Device      │
└─────────────────────┘         └─────────────────────┘
         │                                │
         └────────── Same Network ────────┘
              (192.168.100.x)
```

---

## 🚀 Quick Start

1. **Configure device** (see Step 1 above)
2. **Test connection:**
   ```bash
   php test_device_connection_only.php
   ```
3. **If successful:** Device is ready for use!
4. **If failed:** Follow troubleshooting steps

---

**Server IP:** 192.168.100.103  
**Server Port:** 8002  
**Device Network:** 192.168.100.x









