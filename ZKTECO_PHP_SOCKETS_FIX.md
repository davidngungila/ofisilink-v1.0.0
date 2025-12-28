# Fix: PHP Sockets Extension Not Loaded

## Problem

You're seeing: **"PHP sockets extension is not loaded"**

Even though the extension is enabled in `php.ini`, the web server might be using a different configuration.

---

## ✅ Solution Steps

### Step 1: Verify Current PHP Configuration

1. **Visit the diagnostic page:**
   ```
   http://localhost/check_php_config.php
   ```
   Or:
   ```
   http://your-domain.com/check_php_config.php
   ```

2. **Check the results:**
   - If it shows "Sockets extension is LOADED" → Problem solved!
   - If it shows "Sockets extension is NOT LOADED" → Continue to Step 2

### Step 2: Check Which PHP.ini is Being Used

The diagnostic page will show you the exact `php.ini` file path that your web server is using.

**Common locations:**
- Laragon: `C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.ini`
- XAMPP: `C:\xampp\php\php.ini`
- WAMP: `C:\wamp\bin\php\php8.x.x\php.ini`

### Step 3: Enable Sockets Extension

1. **Open the php.ini file** shown in the diagnostic page

2. **Search for:**
   ```ini
   ;extension=sockets
   ```

3. **Remove the semicolon:**
   ```ini
   extension=sockets
   ```

4. **Save the file**

### Step 4: Restart Web Server

**⚠️ CRITICAL:** You MUST restart the web server for changes to take effect!

#### For Laragon:
1. Right-click Laragon tray icon
2. Click **Stop All**
3. Wait 5 seconds
4. Click **Start All**

#### For XAMPP:
1. Stop Apache in XAMPP Control Panel
2. Wait 5 seconds
3. Start Apache again

#### For WAMP:
1. Click WAMP icon → **Restart All Services**

#### For Linux (Apache):
```bash
sudo systemctl restart apache2
# or
sudo service apache2 restart
```

#### For Linux (Nginx + PHP-FPM):
```bash
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

### Step 5: Verify Fix

1. **Refresh the diagnostic page:**
   ```
   http://localhost/check_php_config.php
   ```

2. **Should now show:**
   - ✅ "Sockets extension is LOADED"
   - ✅ "socket_create() function is available"

3. **Test connection again:**
   - Go to `/modules/hr/attendance`
   - Click "Test Connection"
   - Should work now!

---

## 🔍 Troubleshooting

### Issue: Still shows "NOT LOADED" after restart

**Possible causes:**

1. **Wrong php.ini file:**
   - The web server might be using a different php.ini
   - Check the diagnostic page for the exact path
   - Make sure you edited the correct file

2. **Extension file missing:**
   - Check if `php_sockets.dll` exists in extension directory
   - Windows: Usually in `C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\ext\`
   - If missing, reinstall PHP or download the extension

3. **PHP CLI vs Web Server:**
   - CLI PHP might use different php.ini than web server
   - Always check the web server's php.ini (shown in diagnostic page)

4. **Cache issue:**
   - Clear Laravel cache:
     ```bash
     php artisan config:clear
     php artisan cache:clear
     ```

### Issue: Can't find php.ini

**Solution:**
1. Visit: `http://localhost/phpinfo.php`
2. Look for "Loaded Configuration File"
3. That's the php.ini your web server is using

---

## 📋 Quick Checklist

- [ ] Visited `http://localhost/check_php_config.php`
- [ ] Found the correct php.ini path
- [ ] Edited php.ini: `extension=sockets` (no semicolon)
- [ ] Saved the file
- [ ] **Restarted web server** (Laragon/Apache/Nginx)
- [ ] Refreshed diagnostic page
- [ ] Shows "Sockets extension is LOADED"
- [ ] Tested connection - works!

---

## 🆘 Still Not Working?

1. **Check Laravel logs:**
   ```
   storage/logs/laravel.log
   ```

2. **Check PHP error logs:**
   - Laragon: `C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php_error.log`
   - XAMPP: `C:\xampp\php\logs\php_error_log`

3. **Verify extension file exists:**
   - Windows: `php_sockets.dll` in `ext/` folder
   - Linux: `sockets.so` in extension directory

4. **Try alternative:**
   - Use `extension_dir` directive in php.ini
   - Or use full path: `extension=C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\ext\php_sockets.dll`

---

**Remember:** Always restart the web server after changing php.ini!










