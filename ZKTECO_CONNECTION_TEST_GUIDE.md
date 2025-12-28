# ZKTeco Connection Test Guide

## Overview
The ZKTeco device connection test feature allows you to verify connectivity to your UF200-S biometric device before saving device configuration.

## How to Use

### Step 1: Open Device Modal
1. Navigate to **HR → Attendance → Settings**
2. Click on the **Devices** tab
3. Click **Add Device** or edit an existing device

### Step 2: Enter Connection Details
1. Go to **Step 2: Connection** tab
2. Enter the following:
   - **IP Address**: Device IP address (e.g., 192.168.1.100)
   - **Port**: Default is 4370
   - **Communication Key**: Usually 0 (default)

### Step 3: Test Connection
1. Click the **Test Connection** button
2. Wait for the connection test to complete
3. Review the results:
   - **Success**: Device is online and responding
   - **Failed**: Check IP, port, and network connectivity

### Step 4: Auto-Fill Device Details
On successful connection:
- Device name will be auto-filled (if available)
- Model will be set to "UF200-S"
- Serial number will be auto-filled (if available)

### Step 5: Save Device
1. Complete all required fields
2. Click **Save All & Close**
3. Device will be saved with verified connection details

## Troubleshooting

### Connection Failed
1. **Check IP Address**: Ensure device IP is correct
2. **Check Port**: Default is 4370, verify on device
3. **Check Network**: Ensure device and server are on same network
4. **Check Firewall**: Ensure port 4370 is not blocked
5. **Check Device**: Ensure device is powered on and connected to network

### PHP Sockets Extension
If you see "sockets extension not available" error:
1. Enable sockets extension in php.ini
2. Restart web server
3. See `ZKTECO_PHP_SOCKETS_SETUP.md` for detailed instructions

### Device Not Responding
1. Ping the device IP from server
2. Check device network settings
3. Verify device is not in sleep mode
4. Check device communication key (password)

## Technical Details

### Connection Test Process
1. Creates TCP socket connection to device
2. Sends authentication command
3. Retrieves device information
4. Disconnects cleanly
5. Returns device details

### API Endpoint
- **Route**: `POST /zkteco/test-connection`
- **Parameters**:
  - `ip` (required): Device IP address
  - `port` (required): Device port (default: 4370)
  - `password` (optional): Communication key (default: 0)

### Response Format
```json
{
  "success": true,
  "message": "Device connected successfully",
  "device_info": {
    "ip": "192.168.1.100",
    "port": 4370,
    "device_name": "ZKTeco UF200-S",
    "model": "UF200-S",
    "firmware_version": "6.60.1.2",
    "serial_number": "ABC123456"
  }
}
```

## Best Practices

1. **Always Test Before Saving**: Verify connection before saving device
2. **Use Static IP**: Configure device with static IP for reliability
3. **Document Settings**: Keep record of device IP and communication key
4. **Regular Testing**: Test connection periodically to ensure device is online
5. **Network Security**: Ensure device network is secure and isolated if possible










