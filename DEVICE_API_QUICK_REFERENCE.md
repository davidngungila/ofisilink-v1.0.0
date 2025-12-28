# Device API Quick Reference

## Where to Configure

### 1. In Laravel (Web Interface)

**Location:** `HR → Attendance → Settings → Devices`

1. **Register Device:**
   - Device Name: `UF200-S`
   - Device ID: `UF200-S-TRU7251200134`
   - IP Address: `192.168.100.127`
   - Port: `4370`

2. **Set API Key (Optional):**
   - Edit device → Advanced tab
   - Add to settings JSON:
   ```json
   {
     "api_key": "your-secure-key-here"
   }
   ```

### 2. In ZKBio Time.Net

**Push URL Configuration:**
- Go to **Device Settings** → **Communication** → **Push URL**
- Set to: `https://your-domain.com/api/device/attendance/push`
- Method: `POST`
- Content-Type: `application/json`

---

## What Data to Send FROM Device

### Attendance Record (Push)

**Endpoint:** `POST /api/device/attendance/push`

**Required Data:**
```json
{
  "device_id": "UF200-S-TRU7251200134",
  "employee_id": "EMP001",
  "check_time": "2025-01-15T09:00:00Z",
  "check_type": "I"
}
```

**Optional Data:**
- `verify_code`: Verification method (15=fingerprint)
- `work_code`: Work code (0=normal)
- `sn`: Device serial number

### Device Status (Heartbeat)

**Endpoint:** `POST /api/device/status`

**Required Data:**
```json
{
  "device_id": "UF200-S-TRU7251200134",
  "status": "online"
}
```

**Optional Data:**
- `battery_level`: 0-100
- `storage_usage`: 0-100
- `total_users`: Number of users
- `total_records`: Number of records

---

## What Data to Receive TO Device

### 1. Employee/User List

**Endpoint:** `GET /api/device/users/{device_id}`

**Device Requests:**
```
GET /api/device/users/UF200-S-TRU7251200134
```

**Laravel Responds With:**
```json
{
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

**Use This To:**
- Sync employees to device
- Update user information
- Enable/disable users

### 2. Server Time

**Endpoint:** `GET /api/device/time/{device_id}`

**Device Requests:**
```
GET /api/device/time/UF200-S-TRU7251200134
```

**Laravel Responds With:**
```json
{
  "server_time": "2025-01-15T10:30:00Z",
  "server_time_unix": 1705312200,
  "timezone": "Africa/Dar_es_Salaam"
}
```

**Use This To:**
- Synchronize device time
- Ensure accurate timestamps

### 3. Commands

**Endpoint:** `GET /api/device/commands/{device_id}`

**Device Requests:**
```
GET /api/device/commands/UF200-S-TRU7251200134
```

**Laravel Responds With:**
```json
{
  "commands": [
    {
      "command": "sync_time",
      "parameters": {}
    }
  ]
}
```

**Available Commands:**
- `sync_time` - Sync device time
- `restart` - Restart device
- `clear_data` - Clear all data
- `clear_users` - Clear users
- `clear_attendance` - Clear attendance records

---

## Data Flow Diagram

```
┌─────────────────┐                    ┌──────────────────┐
│  UF200-S Device │                    │  Laravel Server  │
└─────────────────┘                    └──────────────────┘
         │                                       │
         │ 1. POST /attendance/push              │
         │    {employee_id, check_time, ...}    │
         ├──────────────────────────────────────>│
         │                                       │
         │ 2. GET /users/{device_id}            │
         ├──────────────────────────────────────>│
         │                                       │
         │ <─────────────────────────────────────┤
         │    {users: [...]}                     │
         │                                       │
         │ 3. GET /time/{device_id}              │
         ├──────────────────────────────────────>│
         │                                       │
         │ <─────────────────────────────────────┤
         │    {server_time: "..."}               │
         │                                       │
         │ 4. GET /commands/{device_id}          │
         ├──────────────────────────────────────>│
         │                                       │
         │ <─────────────────────────────────────┤
         │    {commands: [...]}                  │
         │                                       │
```

---

## Quick Test Commands

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

### Test Get Time
```bash
curl https://your-domain.com/api/device/time/UF200-S-TRU7251200134
```

---

## Important Notes

1. **Employee ID Matching:**
   - Device `employee_id` MUST match Laravel `employee_id` or `employee_number`
   - If IDs don't match, attendance records will be skipped

2. **Device Registration:**
   - Device must be registered in Laravel before API will accept data
   - Register via: `HR → Attendance → Settings → Devices`

3. **Authentication:**
   - Use API key in header: `X-API-Key: your-key`
   - Or configure IP whitelist in device settings

4. **Time Format:**
   - Always use ISO 8601: `2025-01-15T09:00:00Z`
   - Laravel will convert to server timezone

---

## Troubleshooting

### Issue: "Device not found"
- **Solution:** Register device in Laravel first

### Issue: "Employee not found"
- **Solution:** Ensure employee_id matches between device and Laravel

### Issue: "Validation failed"
- **Solution:** Check required fields: `device_id`, `employee_id`, `check_time`

### Issue: API not responding
- **Solution:** Check Laravel logs: `storage/logs/laravel.log`

---

**For full documentation, see:** `DEVICE_API_DOCUMENTATION.md`






