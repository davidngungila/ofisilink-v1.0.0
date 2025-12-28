# Mobile API Quick Reference

## Base URL
```
https://your-domain.com/api/mobile/v1
```

## Authentication
```
Authorization: Bearer {token}
```

## Common Endpoints

### Authentication
```
POST   /auth/login              - Login
POST   /auth/login-otp          - Request OTP
POST   /auth/verify-otp         - Verify OTP
GET    /auth/me                 - Current user
POST   /auth/logout             - Logout
```

### Dashboard
```
GET    /dashboard               - Dashboard data
GET    /dashboard/stats         - Statistics
GET    /dashboard/notifications - Notifications
```

### Profile
```
GET    /profile                 - Get profile
PUT    /profile                 - Update profile
POST   /profile/photo           - Update photo
```

### Attendance
```
GET    /attendance              - List attendance
GET    /attendance/my           - My attendance
POST   /attendance/check-in     - Check in
POST   /attendance/check-out    - Check out
```

### Leave
```
GET    /leaves                  - List leaves
GET    /leaves/my               - My leaves
POST   /leaves                  - Create leave
POST   /leaves/{id}/approve     - Approve leave
GET    /leaves/balance          - Leave balance
```

### Tasks
```
GET    /tasks                   - List tasks
GET    /tasks/my                - My tasks
POST   /tasks                   - Create task
POST   /tasks/{id}/complete     - Complete task
```

### Files
```
GET    /files/digital            - Digital files
POST   /files/digital/upload     - Upload file
GET    /files/digital/{id}/download - Download
GET    /files/physical           - Physical files
POST   /files/physical/{id}/request - Request file
```

### Finance
```
GET    /finance/petty-cash       - Petty cash
POST   /finance/petty-cash       - Create voucher
GET    /finance/imprest          - Imprest
POST   /finance/imprest          - Create request
GET    /finance/payroll/my       - My payroll
```

### HR
```
GET    /hr/permissions           - Permissions
POST   /hr/permissions           - Request permission
GET    /hr/sick-sheets           - Sick sheets
POST   /hr/sick-sheets           - Submit sick sheet
GET    /hr/assessments            - Assessments
GET    /hr/jobs                  - Job postings
```

### Notifications
```
GET    /notifications            - All notifications
GET    /notifications/unread     - Unread only
POST   /notifications/{id}/read  - Mark as read
```

## Common Query Parameters

### Pagination
```
?per_page=20&page=1
```

### Filtering
```
?status=pending
?date_from=2024-01-01&date_to=2024-01-31
?department_id=1
```

### Search
```
?q=search_term
```

## Response Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Example Requests

### Login
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}'
```

### Get Dashboard
```bash
curl -X GET https://192.168.100.105:8004/api/mobile/v1/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Create Leave Request
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/leaves \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "leave_type_id": 1,
    "start_date": "2024-02-01",
    "end_date": "2024-02-05",
    "reason": "Vacation"
  }'
```

### Upload File
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/files/digital/upload \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "file=@document.pdf" \
  -F "folder_id=1" \
  -F "name=My Document"
```

## Notes

- All dates in `YYYY-MM-DD` format
- All timestamps in ISO 8601 format
- File uploads use multipart/form-data
- JSON requests use application/json
- Always include Authorization header for protected routes

