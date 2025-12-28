# Verify Device Communication Key - Step by Step

## 🔴 Current Status

✅ **Network:** Working (device is reachable)  
✅ **Port:** Working (port 4370 is open)  
❌ **Authentication:** Failing (device closes connection)

**This means:** The Communication Key on the device does NOT match what we're sending (0).

---

## 📋 Step-by-Step: Verify Comm Key on Device

### Step 1: Access Device Menu

1. **On ZKTeco Device:**
   - Press the **Menu** button (usually on the device screen)
   - Enter **admin password** (if required)
   - If you don't know admin password, try: `0`, `12345`, `54321`, or check device manual

### Step 2: Navigate to Communication Settings

1. **Menu Path:**
   ```
   Menu → System → Communication → Comm Key
   ```
   
   OR
   
   ```
   Menu → System → Network → Comm Key
   ```

2. **Look at the screen:**
   - You should see a number displayed
   - **Write down this number exactly**
   - It might be: `0`, `1`, `12345`, `54321`, or another number

### Step 3: Check Current Value

**Important:** The value shown on the device screen is the actual Comm Key.

- If it shows **`0`** → Use password `0` in application
- If it shows **`1`** → Use password `1` in application
- If it shows **`12345`** → Use password `12345` in application
- If it shows **any other number** → Use that exact number

### Step 4: Reset to 0 (Recommended)

1. **On Device:**
   - Navigate to: **System → Communication → Comm Key**
   - **Change the value to `0`** (zero)
   - **Save settings** (usually OK or Save button)
   - **Confirm** if prompted

2. **Restart Device:**
   - **Power off** device (unplug power cable)
   - **Wait 15 seconds**
   - **Power on** device
   - **Wait 60 seconds** for full boot
   - Check device display shows ready

3. **Verify After Restart:**
   - Go back to: **System → Communication → Comm Key**
   - **Verify it still shows `0`**
   - If it changed, set it to `0` again and restart

### Step 5: Test Connection Again

After resetting Comm Key to 0 and restarting:

```bash
php test_device_connection_only.php 192.168.100.108 4370 0
```

---

## 🔍 Alternative: Find Current Comm Key

If you can't reset the Comm Key, find out what it is:

### Method 1: Check Device Display

1. Some devices show Comm Key on main screen
2. Check device settings menu
3. Look for "Comm Key", "Password", or "Communication Key"

### Method 2: Try Common Values

Test with common Comm Key values:

```bash
# Try password 0
php test_device_connection_only.php 192.168.100.108 4370 0

# Try password 1
php test_device_connection_only.php 192.168.100.108 4370 1

# Try password 12345
php test_device_connection_only.php 192.168.100.108 4370 12345

# Try password 54321
php test_device_connection_only.php 192.168.100.108 4370 54321
```

### Method 3: Check ZKBio Time.Net

If you have ZKBio Time.Net installed:

1. Open ZKBio Time.Net
2. Add device with IP: `192.168.100.108`
3. Check what Comm Key it uses
4. Use the same Comm Key in your application

---

## ⚠️ Important Notes

### Comm Key Must Match Exactly

- If device Comm Key is `0` → Use `0` in application
- If device Comm Key is `1` → Use `1` in application
- **Case-sensitive:** `0` is different from `1`
- **No spaces:** `0` not ` 0` (with space)

### After Changing Comm Key

1. **Always restart device** after changing Comm Key
2. **Wait 60 seconds** after restart
3. **Verify** Comm Key is still correct after restart
4. **Test connection** immediately after restart

### Device May Have Multiple Settings

Some devices have:
- **Comm Key** (for network communication)
- **Admin Password** (for device menu access)
- **User Password** (for user authentication)

**We need the Comm Key**, not admin password or user password.

---

## 🎯 Quick Checklist

Before testing connection:

- [ ] Accessed device menu
- [ ] Navigated to: System → Communication → Comm Key
- [ ] **Saw the actual number** on device screen
- [ ] **Wrote down the number**
- [ ] Set Comm Key to `0` (if not already)
- [ ] Saved settings on device
- [ ] **Restarted device** (power off/on)
- [ ] Waited 60 seconds after restart
- [ ] Verified Comm Key is still `0` after restart
- [ ] Using same number in application

---

## 📞 If Still Not Working

If you've verified Comm Key is `0` on device, restarted device, and still getting errors:

1. **Check Device Firmware:**
   - Device menu → System → Version
   - Note firmware version
   - May need firmware update

2. **Try Different Comm Key Values:**
   - Test with `1`, `12345`, `54321`
   - One of these might work

3. **Check Device Mode:**
   - Ensure device is in "Network" mode
   - Not in "USB" mode
   - Not in "Maintenance" mode

4. **Contact ZKTeco Support:**
   - Email: service@zkteco.com
   - Provide device model and firmware version
   - Mention connection closes during authentication

---

## ✅ Success Indicator

When Comm Key is correct, you'll see:

```
✓ CONNECTION SUCCESSFUL!
Working Method: Method 1: No password data (simplest)
Password Used: 0
Device Information: [details]
```

---

**Most Important:** The Comm Key on the device screen must match exactly what you use in the application!









