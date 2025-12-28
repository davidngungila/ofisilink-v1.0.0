# Quick Fix: Device Closed Connection Error

## 🚨 Error You're Seeing

```
Device closed the connection unexpectedly. Possible causes:
• Device restarted or lost power
• Network connection was interrupted
• Device firmware error
```

---

## ⚡ Quick Fix (Try This First!)

### Step 1: Check Communication Key

**On Your ZKTeco Device:**
1. Press **Menu** button
2. Enter admin password
3. Go to: **System → Communication → Comm Key**
4. **Note the number** (usually `0`)

**In Your Application:**
- Use the **exact same number** as the Communication Key
- Default is usually `0` (zero)
- If unsure, try `0` first

---

### Step 2: Restart Device

1. **Power off** the device (unplug)
2. **Wait 10 seconds**
3. **Power on** the device
4. **Wait 30-60 seconds** for full boot
5. **Try connection again**

---

### Step 3: Test with Password 0

If you're not sure of the password, try `0`:

**In your connection settings:**
- IP: Your device IP (e.g., `192.168.1.100`)
- Port: `4370`
- **Password/Comm Key: `0`**

---

## 🔍 Common Solutions

| Problem | Solution |
|---------|----------|
| Wrong Communication Key | Set to `0` on device, use `0` in app |
| Device needs restart | Power off/on, wait 60 seconds |
| Other software connected | Close ZKBio Time.Net, wait 10 sec |
| Network unstable | Check ping, verify cable/WiFi |
| Firmware outdated | Update device firmware |

---

## 📝 Detailed Guide

For complete troubleshooting steps, see:
**`ZKTECO_DEVICE_CLOSED_CONNECTION_FIX.md`**

---

## ✅ Success Check

When fixed, you should see:
- ✅ Connection test shows "Success"
- ✅ Device information displays
- ✅ No error messages

---

**Most Common Fix:** Set Communication Key to `0` on device, use password `0` in app, restart device.









