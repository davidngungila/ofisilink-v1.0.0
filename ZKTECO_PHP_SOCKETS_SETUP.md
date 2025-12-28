# ZKTeco PHP Sockets Extension Setup Guide

## Overview
The ZKTeco integration requires the PHP Sockets extension to communicate with biometric devices via TCP/IP sockets.

## Check Current Status

Run this command to check if sockets extension is loaded:
```bash
php -r "echo function_exists('socket_create') ? 'Sockets extension is available' : 'Sockets extension is NOT available';"
```

## Enable Sockets Extension (Windows - Laragon)

1. **Locate php.ini file:**
   ```bash
   php --ini
   ```
   This will show the path to your php.ini file (usually: `C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.ini`)

2. **Edit php.ini:**
   - Open the php.ini file in a text editor (as Administrator)
   - Search for `;extension=sockets` or `extension=sockets`
   - If you find `;extension=sockets` (with semicolon), remove the semicolon to enable it:
     ```ini
     extension=sockets
     ```
   - If the line doesn't exist, add it in the extensions section

3. **Restart PHP/Apache:**
   - In Laragon: Click "Stop All" then "Start All"
   - Or restart your web server

4. **Verify:**
   ```bash
   php -r "echo function_exists('socket_create') ? 'Sockets extension is available ✓' : 'Sockets extension is NOT available ✗';"
   ```

## Alternative: Check if Already Built-in

On some PHP installations, sockets might be built-in. Check with:
```bash
php -m | findstr sockets
```

If sockets appears in the list, it's already enabled.

## Troubleshooting

### If sockets extension is not found:
1. Ensure you're using the correct PHP version
2. Check if `php_sockets.dll` exists in your PHP `ext` directory
3. For Windows, ensure the DLL file matches your PHP architecture (x64 or x86)

### For Production Servers:
- Contact your hosting provider to enable the sockets extension
- Or use a VPS/server where you have control over PHP configuration

## Required PHP Functions

The ZKTeco integration uses these socket functions:
- `socket_create()`
- `socket_connect()`
- `socket_send()`
- `socket_recv()`
- `socket_close()`
- `socket_set_option()`

All these functions are part of the sockets extension.










