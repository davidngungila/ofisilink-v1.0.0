# ZKTeco User Capture and Registration Guide

## Overview

This guide explains how to capture users from your ZKTeco device and register users to the device using direct connection.

**Device Information:**
- IP: `192.168.100.108`
- Port: `4370`
- Comm Key: `0`
- Model: ZKTeco
- Firmware: Ver 6.60 Sep 27 2019

---

## API Endpoints

### 1. Capture Users from Device

**Endpoint:** `POST /zkteco/users/capture-from-device`

**Description:** Fetches all users registered on the ZKTeco device via direct connection.

**Request Body:**
```json
{
    "ip": "192.168.100.108",
    "port": 4370,
    "password": 0
}
```

**Response:**
```json
{
    "success": true,
    "message": "Users captured successfully from device",
    "total": 5,
    "users": [
        {
            "uid": 1,
            "name": "John Doe",
            "password": "",
            "card": 0,
            "role": 0
        },
        {
            "uid": 2,
            "name": "Jane Smith",
            "password": "",
            "card": 0,
            "role": 0
        }
    ]
}
```

**Usage Example (JavaScript/AJAX):**
```javascript
fetch('/zkteco/users/capture-from-device', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        ip: '192.168.100.108',
        port: 4370,
        password: 0
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Users captured:', data.users);
        console.log('Total users:', data.total);
    } else {
        console.error('Error:', data.message);
    }
});
```

---

### 2. Register User to Device

**Endpoint:** `POST /zkteco/users/{userId}/register-to-device`

**Description:** Registers a user directly to the ZKTeco device using device connection.

**Request Body:**
```json
{
    "ip": "192.168.100.108",
    "port": 4370,
    "password": 0
}
```

**Response:**
```json
{
    "success": true,
    "message": "User registered to device successfully",
    "user": {
        "id": 44,
        "name": "Abia (Naomi) Habari",
        "enroll_id": "44"
    }
}
```

**Usage Example (JavaScript/AJAX):**
```javascript
const userId = 44; // User ID from your system

fetch(`/zkteco/users/${userId}/register-to-device`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        ip: '192.168.100.108',
        port: 4370,
        password: 0
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('User registered successfully:', data.user);
        alert('User registered to device successfully!');
    } else {
        console.error('Error:', data.message);
        alert('Registration failed: ' + data.message);
    }
});
```

---

## Requirements

### Before Registering a User

1. **User must have an `enroll_id`**
   - The `enroll_id` is used as the User ID on the device
   - If user doesn't have an `enroll_id`, generate one first

2. **Device Connection**
   - Device must be online and accessible
   - IP address, port, and Comm Key must be correct
   - Test connection first using `/zkteco/test-connection`

---

## How It Works

### Capture Users Process:
1. Connects to ZKTeco device at specified IP/Port
2. Authenticates using Comm Key (password)
3. Fetches all users from device
4. Returns list of users with their details

### Register User Process:
1. Connects to ZKTeco device at specified IP/Port
2. Authenticates using Comm Key (password)
3. Enables device (required for registration)
4. Registers user with:
   - **UID**: User's `enroll_id` from system
   - **Name**: First name only (max 8 characters - device limit)
   - **Role**: 0 (regular user)
   - **Card**: 0 (no card)
5. Verifies user was registered by checking device
6. Updates user's `registered_on_device` status in database

---

## Error Handling

### Common Errors:

1. **Connection Failed**
   - Check device IP, port, and network connectivity
   - Verify Comm Key is correct (usually 0)
   - Ensure device is powered on

2. **User Missing Enroll ID**
   - User must have an `enroll_id` before registration
   - Generate `enroll_id` first using user management

3. **Registration Failed**
   - Device may be busy
   - User may already exist on device
   - Try again after a few seconds

---

## Testing

### Test Connection First:
```javascript
fetch('/zkteco/test-connection', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        ip: '192.168.100.108',
        port: 4370,
        password: 0
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        console.log('Device connected:', data.device_info);
    } else {
        console.error('Connection failed:', data.message);
    }
});
```

---

## Notes

- **Name Limitation**: Device only supports 8 characters for user names. The system automatically truncates to first name only.
- **Enroll ID**: Must be a numeric value. Used as the User ID on the device.
- **Direct Connection**: These endpoints connect directly to the device, not through external API.
- **Device Enable**: Device is automatically enabled before registration (required by ZKTeco protocol).

---

## Related Endpoints

- `POST /zkteco/test-connection` - Test device connection
- `POST /zkteco/device-info` - Get device information
- `POST /zkteco/users/{id}/register` - Register via external API (alternative method)
- `POST /zkteco/users/{id}/unregister` - Remove user from device



