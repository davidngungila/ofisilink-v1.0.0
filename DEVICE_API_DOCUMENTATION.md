# ZKTeco UF200-S Device API Documentation

## Overview

This API enables bidirectional communication between your Laravel application and ZKTeco UF200-S biometric devices. The API supports both **push** (device sends data to Laravel) and **pull** (device requests data from Laravel) communication patterns.

---

## Base URL

```
https://your-domain.com/api/device
```

---

## Authentication

### Method 1: API Key (Recommended)

Include API key in request header or body:

**Header:**
```
X-API-Key: your-api-key-here
X-Device-ID: UF200-S-TRU7251200134
```

**Body (alternative):**
```json
{
  "api_key": "your-api-key-here",
  "device_id": "UF200-S-TRU7251200134"
}
```

### Method 2: IP Whitelist

Configure device IP address in Laravel device settings. Requests from registered device IPs are automatically authenticated.

---

## API Endpoints

### 1. Health Check

Check if API is accessible and healthy.

**Endpoint:** `GET /api/device/health`

**Request:**
```http
GET /api/device/health HTTP/1.1
Host: your-domain.com
```

**Response:**
```json
{
  "success": true,
  "status": "healthy",
  "timestamp": "2025-01-15T10:30:00Z",
  "version": "1.0.0"
}
```

---

## RECEIVING DATA FROM DEVICE (Push API)

### 2. Receive Attendance Record

Device sends single attendance record to Laravel.

**Endpoint:** `POST /api/device/attendance/push`

**Request Headers:**
```
Content-Type: application/json
X-Device-ID: UF200-S-TRU7251200134 (optional)
X-API-Key: your-api-key (optional)
```

**Request Body:**
```json
{
  "device_id": "UF200-S-TRU7251200134",
  "employee_id": "EMP001",
  "check_time": "2025-01-15T09:00:00Z",
  "check_type": "I",
  "verify_code": 15,
  "work_code": 0,
  "sn": "TRU7251200134"
}
```

**Field Descriptions:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `device_id` | string | Yes | Device identifier (must match registered device) |
| `employee_id` | string | Yes | Employee ID (must match Laravel employee_id or employee_number) |
| `check_time` | datetime | Yes | ISO 8601 format timestamp |
| `check_type` | string | No | `I` or `0` = Time In, `O` = Time Out |
| `verify_code` | integer | No | Verification method code (15=fingerprint, 1=password, etc.) |
| `work_code` | integer | No | Work code (0=normal) |
| `sn` | string | No | Device serial number |

**Success Response (200):**
```json
{
  "success": true,
  "message": "Attendance recorded successfully",
  "data": {
    "attendance_id": 12345,
    "employee_id": "EMP001",
    "check_time": "2025-01-15T09:00:00Z",
    "check_type": "in"
  }
}
```

**Error Responses:**

**404 - Employee Not Found:**
```json
{
  "success": false,
  "message": "Employee not found",
  "employee_id": "EMP001"
}
```

**404 - Device Not Found:**
```json
{
  "success": false,
  "message": "Device not found or not registered"
}
```

**422 - Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "employee_id": ["The employee id field is required."],
    "check_time": ["The check time must be a valid date."]
  }
}
```

---

### 3. Receive Batch Attendance Records

Device sends multiple attendance records in one request.

**Endpoint:** `POST /api/device/attendance/batch`

**Request Body:**
```json
{
  "device_id": "UF200-S-TRU7251200134",
  "records": [
    {
      "employee_id": "EMP001",
      "check_time": "2025-01-15T09:00:00Z",
      "check_type": "I",
      "verify_code": 15
    },
    {
      "employee_id": "EMP002",
      "check_time": "2025-01-15T09:05:00Z",
      "check_type": "I",
      "verify_code": 15
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Processed 2 records, 0 failed",
  "results": {
    "success": 2,
    "failed": 0,
    "errors": []
  }
}
```

---

### 4. Receive Device Status

Device sends status/heartbeat information.

**Endpoint:** `POST /api/device/status`

**Request Body:**
```json
{
  "device_id": "UF200-S-TRU7251200134",
  "status": "online",
  "battery_level": 85,
  "storage_usage": 45,
  "total_users": 150,
  "total_records": 5000
}
```

**Field Descriptions:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `device_id` | string | Yes | Device identifier |
| `status` | string | Yes | `online`, `offline`, or `error` |
| `battery_level` | integer | No | Battery percentage (0-100) |
| `storage_usage` | integer | No | Storage usage percentage (0-100) |
| `total_users` | integer | No | Total users on device |
| `total_records` | integer | No | Total attendance records on device |

**Response:**
```json
{
  "success": true,
  "message": "Device status updated"
}
```

---

## SENDING DATA TO DEVICE (Pull API)

### 5. Get Users/Employees List

Device requests list of all employees to sync.

**Endpoint:** `GET /api/device/users/{device_id}`

**Request:**
```http
GET /api/device/users/UF200-S-TRU7251200134 HTTP/1.1
Host: your-domain.com
```

**Response:**
```json
{
  "success": true,
  "device_id": "UF200-S-TRU7251200134",
  "device_name": "UF200-S",
  "total_users": 150,
  "users": [
    {
      "user_id": "EMP001",
      "name": "John Doe",
      "employee_number": "EMP001",
      "department": "IT",
      "privilege": 0,
      "enabled": true
    },
    {
      "user_id": "EMP002",
      "name": "Jane Smith",
      "employee_number": "EMP002",
      "department": "HR",
      "privilege": 0,
      "enabled": true
    }
  ]
}
```

**Field Descriptions:**

| Field | Description |
|-------|-------------|
| `user_id` | Employee ID (must match ZKBio Time.Net USERID) |
| `name` | Employee full name |
| `employee_number` | Employee number |
| `department` | Department name |
| `privilege` | `0` = User, `14` = Admin |
| `enabled` | `true` = Active, `false` = Disabled |

---

### 6. Get Specific User

Device requests data for a specific employee.

**Endpoint:** `GET /api/device/users/{device_id}/{employee_id}`

**Request:**
```http
GET /api/device/users/UF200-S-TRU7251200134/EMP001 HTTP/1.1
Host: your-domain.com
```

**Response:**
```json
{
  "success": true,
  "user": {
    "user_id": "EMP001",
    "name": "John Doe",
    "employee_number": "EMP001",
    "department": "IT",
    "privilege": 0,
    "enabled": true
  }
}
```

---

### 7. Get Server Time

Device requests server time for synchronization.

**Endpoint:** `GET /api/device/time/{device_id}`

**Request:**
```http
GET /api/device/time/UF200-S-TRU7251200134 HTTP/1.1
Host: your-domain.com
```

**Response:**
```json
{
  "success": true,
  "server_time": "2025-01-15T10:30:00Z",
  "server_time_unix": 1705312200,
  "timezone": "Africa/Dar_es_Salaam",
  "date": "2025-01-15",
  "time": "10:30:00"
}
```

---

### 8. Get Device Commands

Device checks for pending commands from Laravel.

**Endpoint:** `GET /api/device/commands/{device_id}`

**Request:**
```http
GET /api/device/commands/UF200-S-TRU7251200134 HTTP/1.1
Host: your-domain.com
```

**Response:**
```json
{
  "success": true,
  "device_id": "UF200-S-TRU7251200134",
  "commands": [
    {
      "command": "sync_time",
      "parameters": {},
      "created_at": "2025-01-15T10:25:00Z"
    }
  ]
}
```

**Available Commands:**

| Command | Description |
|---------|-------------|
| `sync_time` | Synchronize device time with server |
| `restart` | Restart device |
| `clear_data` | Clear all data |
| `clear_users` | Clear all users |
| `clear_attendance` | Clear attendance records |
| `update_firmware` | Update device firmware |

---

### 9. Send Command to Device

Laravel sends command to device (stored for device to pull).

**Endpoint:** `POST /api/device/commands/{device_id}`

**Request Body:**
```json
{
  "command": "sync_time",
  "parameters": {}
}
```

**Response:**
```json
{
  "success": true,
  "message": "Command queued successfully",
  "command": "sync_time"
}
```

---

## Integration with ZKBio Time.Net

### Option 1: Direct Device Communication (This API)

If your device supports HTTP API:
- Configure device to push attendance to: `POST /api/device/attendance/push`
- Device pulls user list from: `GET /api/device/users/{device_id}`
- Device syncs time from: `GET /api/device/time/{device_id}`

### Option 2: Via ZKBio Time.Net (Current Setup)

If using ZKBio Time.Net as intermediary:
- ZKBio Time.Net downloads from device
- Laravel reads from ZKBio Time.Net database
- Use: `php artisan attendance:sync-zkbiotime --all`

---

## Configuration in Laravel

### Step 1: Register Device

1. Go to **HR → Attendance → Settings → Devices**
2. Add device with:
   - **Device ID**: `UF200-S-TRU7251200134`
   - **IP Address**: `192.168.100.127`
   - **Port**: `4370`

### Step 2: Configure API Key (Optional)

1. Edit device settings
2. Go to **Advanced** tab
3. Set **API Key** in device settings JSON:
```json
{
  "api_key": "your-secure-api-key-here"
}
```

### Step 3: Configure Device Push URL

In ZKBio Time.Net or device settings:
- **Push URL**: `https://your-domain.com/api/device/attendance/push`
- **Method**: `POST`
- **Content-Type**: `application/json`

---

## Example Integration Code

### PHP (Laravel Server Side)

```php
// Send command to device
$response = Http::post('https://your-domain.com/api/device/commands/UF200-S-TRU7251200134', [
    'command' => 'sync_time',
    'parameters' => []
]);
```

### Python (Device Side)

```python
import requests
import json
from datetime import datetime

# Send attendance record
attendance_data = {
    "device_id": "UF200-S-TRU7251200134",
    "employee_id": "EMP001",
    "check_time": datetime.now().isoformat(),
    "check_type": "I",
    "verify_code": 15
}

response = requests.post(
    "https://your-domain.com/api/device/attendance/push",
    json=attendance_data,
    headers={
        "X-API-Key": "your-api-key",
        "Content-Type": "application/json"
    }
)

print(response.json())
```

### JavaScript/Node.js (Device Side)

```javascript
const axios = require('axios');

// Send attendance record
async function sendAttendance(employeeId, checkTime, checkType) {
  try {
    const response = await axios.post(
      'https://your-domain.com/api/device/attendance/push',
      {
        device_id: 'UF200-S-TRU7251200134',
        employee_id: employeeId,
        check_time: checkTime,
        check_type: checkType,
        verify_code: 15
      },
      {
        headers: {
          'X-API-Key': 'your-api-key',
          'Content-Type': 'application/json'
        }
      }
    );
    
    console.log('Success:', response.data);
  } catch (error) {
    console.error('Error:', error.response.data);
  }
}
```

---

## Error Handling

### Common Error Codes

| Code | Description | Solution |
|------|-------------|----------|
| `404` | Device/Employee not found | Register device or check employee ID |
| `422` | Validation error | Check request format and required fields |
| `500` | Server error | Check Laravel logs for details |

### Error Response Format

```json
{
  "success": false,
  "message": "Error description",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

## Security Best Practices

1. **Use HTTPS**: Always use HTTPS for API communication
2. **API Keys**: Use strong, unique API keys for each device
3. **IP Whitelist**: Restrict API access to known device IPs
4. **Rate Limiting**: Implement rate limiting to prevent abuse
5. **Logging**: Monitor API access logs for suspicious activity

---

## Testing

### Test Health Check

```bash
curl -X GET https://your-domain.com/api/device/health
```

### Test Attendance Push

```bash
curl -X POST https://your-domain.com/api/device/attendance/push \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "device_id": "UF200-S-TRU7251200134",
    "employee_id": "EMP001",
    "check_time": "2025-01-15T09:00:00Z",
    "check_type": "I"
  }'
```

### Test Get Users

```bash
curl -X GET https://your-domain.com/api/device/users/UF200-S-TRU7251200134 \
  -H "X-API-Key: your-api-key"
```

---

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Review device logs in: **HR → Attendance → Settings → Devices → View Logs**
3. Test API endpoints using curl or Postman
4. Contact system administrator

---

**Last Updated**: 2025-01-15
**API Version**: 1.0.0
**Laravel Version**: 10.x+






