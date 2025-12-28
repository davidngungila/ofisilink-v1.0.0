# Device API Summary - ZKTeco UF200-S

## ✅ What Has Been Created

### 1. API Controller
**File:** `app/Http/Controllers/DeviceApiController.php`

**Features:**
- ✅ Receive attendance records from device (push)
- ✅ Receive batch attendance records
- ✅ Receive device status/heartbeat
- ✅ Send employee/user list to device (pull)
- ✅ Send server time for sync
- ✅ Send/receive device commands
- ✅ Health check endpoint

### 2. API Routes
**File:** `routes/web.php`

**Registered Routes:**
- `POST /api/device/attendance/push` - Receive single attendance
- `POST /api/device/attendance/batch` - Receive multiple attendance
- `POST /api/device/status` - Receive device status
- `GET /api/device/users/{device_id}` - Get all users
- `GET /api/device/users/{device_id}/{employee_id}` - Get specific user
- `GET /api/device/time/{device_id}` - Get server time
- `GET /api/device/commands/{device_id}` - Get pending commands
- `POST /api/device/commands/{device_id}` - Send command to device
- `GET /api/device/health` - Health check

### 3. Documentation
- ✅ `DEVICE_API_DOCUMENTATION.md` - Complete API documentation
- ✅ `DEVICE_API_QUICK_REFERENCE.md` - Quick reference guide
- ✅ `DEVICE_API_SUMMARY.md` - This summary

---

## 📋 Data Flow

### FROM Device TO Laravel (Push)

**1. Attendance Records**
```
Device → POST /api/device/attendance/push
Data: {
  device_id, employee_id, check_time, check_type
}
```

**2. Device Status**
```
Device → POST /api/device/status
Data: {
  device_id, status, battery_level, storage_usage
}
```

### FROM Laravel TO Device (Pull)

**1. Employee List**
```
Device → GET /api/device/users/{device_id}
Laravel → Returns: { users: [...] }
```

**2. Server Time**
```
Device → GET /api/device/time/{device_id}
Laravel → Returns: { server_time, timezone }
```

**3. Commands**
```
Device → GET /api/device/commands/{device_id}
Laravel → Returns: { commands: [...] }
```

---

## 🔧 Configuration Steps

### Step 1: Register Device in Laravel

1. Go to: **HR → Attendance → Settings → Devices**
2. Click **Add Device**
3. Fill in:
   - **Name**: `UF200-S`
   - **Device ID**: `UF200-S-TRU7251200134`
   - **IP Address**: `192.168.100.127`
   - **Port**: `4370`
   - **Device Type**: `Biometric`

### Step 2: Configure API Key (Optional)

1. Edit device
2. Go to **Advanced** tab
3. Add to settings:
```json
{
  "api_key": "your-secure-api-key-here"
}
```

### Step 3: Configure Device Push URL

**In ZKBio Time.Net or Device Settings:**
- **Push URL**: `https://your-domain.com/api/device/attendance/push`
- **Method**: `POST`
- **Content-Type**: `application/json`

**Or if device supports direct HTTP:**
- Configure device to push to: `POST /api/device/attendance/push`

---

## 📤 What to Send FROM Device

### Attendance Record
```json
POST /api/device/attendance/push
{
  "device_id": "UF200-S-TRU7251200134",
  "employee_id": "EMP001",
  "check_time": "2025-01-15T09:00:00Z",
  "check_type": "I",
  "verify_code": 15
}
```

### Device Status
```json
POST /api/device/status
{
  "device_id": "UF200-S-TRU7251200134",
  "status": "online",
  "battery_level": 85
}
```

---

## 📥 What to Receive TO Device

### Employee List
```json
GET /api/device/users/UF200-S-TRU7251200134
Response: {
  "users": [
    {
      "user_id": "EMP001",
      "name": "John Doe",
      "employee_number": "EMP001",
      "department": "IT",
      "privilege": 0,
      "enabled": true
    }
  ]
}
```

### Server Time
```json
GET /api/device/time/UF200-S-TRU7251200134
Response: {
  "server_time": "2025-01-15T10:30:00Z",
  "timezone": "Africa/Dar_es_Salaam"
}
```

### Commands
```json
GET /api/device/commands/UF200-S-TRU7251200134
Response: {
  "commands": [
    {
      "command": "sync_time",
      "parameters": {}
    }
  ]
}
```

---

## 🔐 Authentication

### Method 1: API Key
```http
X-API-Key: your-api-key-here
X-Device-ID: UF200-S-TRU7251200134
```

### Method 2: IP Whitelist
- Device IP must be registered in Laravel
- Requests from registered IPs are auto-authenticated

---

## ✅ Testing

### Test Health
```bash
curl https://your-domain.com/api/device/health
```

### Test Push Attendance
```bash
curl -X POST https://your-domain.com/api/device/attendance/push \
  -H "Content-Type: application/json" \
  -d '{
    "device_id": "UF200-S-TRU7251200134",
    "employee_id": "EMP001",
    "check_time": "2025-01-15T09:00:00Z",
    "check_type": "I"
  }'
```

### Test Get Users
```bash
curl https://your-domain.com/api/device/users/UF200-S-TRU7251200134
```

---

## 📝 Important Notes

1. **Employee ID Matching:**
   - Device `employee_id` MUST match Laravel `employee_id` or `employee_number`
   - Check in: `HR → Employees` → Employee ID/Number

2. **Device Registration:**
   - Device must be registered before API accepts data
   - Register via: `HR → Attendance → Settings → Devices`

3. **Time Format:**
   - Always use ISO 8601: `2025-01-15T09:00:00Z`
   - Laravel converts to server timezone automatically

4. **Check Type:**
   - `I` or `0` = Time In
   - `O` = Time Out

---

## 🚀 Next Steps

1. ✅ **Device API Created** - All endpoints ready
2. ✅ **Routes Registered** - All 9 routes active
3. ⏳ **Configure Device** - Set push URL in ZKBio Time.Net or device
4. ⏳ **Test Connection** - Use curl commands above
5. ⏳ **Monitor Logs** - Check `storage/logs/laravel.log` for API calls

---

## 📚 Documentation Files

- **Full API Docs**: `DEVICE_API_DOCUMENTATION.md`
- **Quick Reference**: `DEVICE_API_QUICK_REFERENCE.md`
- **This Summary**: `DEVICE_API_SUMMARY.md`

---

**Status**: ✅ Ready for Integration
**Last Updated**: 2025-01-15






