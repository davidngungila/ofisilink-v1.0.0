# Quick Start: Running Server on Port 8002

## 🚀 Start Server

### Windows:
Double-click: `start_server_8002.bat`

Or run from command line:
```cmd
cd ofisi
php artisan serve --host=127.0.0.1 --port=8002
```

### Linux/Mac:
```bash
cd ofisi
chmod +x start_server_8002.sh
./start_server_8002.sh
```

Or run directly:
```bash
cd ofisi
php artisan serve --host=127.0.0.1 --port=8002
```

---

## 🌐 Access URLs

Once server is running, access:

- **Home:** http://127.0.0.1:8002
- **Attendance Module:** http://127.0.0.1:8002/modules/hr/attendance
- **Dashboard:** http://127.0.0.1:8002/dashboard

---

## 🔧 ZKTeco Connection Issues

If you're getting "Connection Failed! Failed to receive reply from device":

### Quick Fixes:

1. **Check Device IP:**
   - Verify device IP address is correct
   - Device and server must be on same network

2. **Test Network Connection:**
   ```cmd
   ping 192.168.1.100
   ```
   (Replace with your device IP)

3. **Check Firewall:**
   - Windows Firewall may be blocking port 4370
   - Allow port 4370 in firewall settings

4. **Restart Device:**
   - Power off device
   - Wait 10 seconds
   - Power on device
   - Wait 30-60 seconds for boot
   - Try connection again

5. **Check Communication Key:**
   - Default is usually `0` (zero)
   - Verify in device settings

### Detailed Troubleshooting:

See: `ZKTECO_CONNECTION_TROUBLESHOOTING.md`

---

## 📝 Notes

- Server runs on `127.0.0.1:8002` (localhost only)
- To access from other devices on network, use your computer's IP:
  ```cmd
  php artisan serve --host=0.0.0.0 --port=8002
  ```
- Press `Ctrl+C` to stop the server

---

## ✅ Connection Improvements

The ZKTeco connection service now includes:
- ✅ Automatic retry (2 attempts)
- ✅ Better error messages with troubleshooting tips
- ✅ Network connectivity checks
- ✅ Increased timeout (10 seconds)
- ✅ Detailed logging

---

**Need Help?** Check `ZKTECO_CONNECTION_TROUBLESHOOTING.md` for detailed troubleshooting steps.









