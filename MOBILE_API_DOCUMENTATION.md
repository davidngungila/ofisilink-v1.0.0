# OfisiLink Mobile API Documentation

## Overview

This document provides comprehensive documentation for the OfisiLink Mobile API. All endpoints are prefixed with `/api/mobile/v1/` and require authentication via Bearer token (except login endpoints).

**Base URL:** `https://your-domain.com/api/mobile/v1`

## Authentication

The API uses Bearer token authentication. You need to include the token in the `Authorization` header:

```
Authorization: Bearer {your_token_here}
```

### Getting a Token

1. Login with email and password to get a token
2. Use the token in all subsequent requests
3. Token expires after a period of inactivity (configurable)

---

## Table of Contents

1. [Authentication Endpoints](#authentication-endpoints)
2. [Dashboard](#dashboard)
3. [Profile](#profile)
4. [Users](#users)
5. [Departments](#departments)
6. [Attendance](#attendance)
7. [Leave Management](#leave-management)
8. [Task Management](#task-management)
9. [File Management](#file-management)
10. [Finance](#finance)
11. [HR Management](#hr-management)
12. [Notifications](#notifications)

---

## Authentication Endpoints

### Login

**POST** `/auth/login`

Login with email and password.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "iPhone 14 Pro" // Optional
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "phone": "+255712345678",
      "employee_id": "EMP001",
      "photo": "https://domain.com/storage/photos/photo.jpg",
      "primary_department": {
        "id": 1,
        "name": "IT Department"
      },
      "roles": [
        {
          "id": 1,
          "name": "Staff",
          "display_name": "Staff"
        }
      ]
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz",
    "token_type": "Bearer"
  }
}
```

### Login with OTP

**POST** `/auth/login-otp`

Request OTP for login.

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent to your registered phone number",
  "data": {
    "otp": "123456", // Remove in production
    "expires_at": "2024-01-01T12:00:00Z"
  }
}
```

### Verify OTP

**POST** `/auth/verify-otp`

Verify OTP and complete login.

**Request Body:**
```json
{
  "email": "user@example.com",
  "otp": "123456",
  "device_name": "iPhone 14 Pro" // Optional
}
```

**Response:** Same as login response

### Get Current User

**GET** `/auth/me`

Get current authenticated user information.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+255712345678",
    "employee_id": "EMP001",
    "photo": "https://domain.com/storage/photos/photo.jpg",
    "primary_department": {
      "id": 1,
      "name": "IT Department"
    },
    "roles": [...],
    "permissions": [...]
  }
}
```

### Logout

**POST** `/auth/logout`

Logout and revoke current token.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### Change Password

**PUT** `/auth/change-password`

Change user password.

**Request Body:**
```json
{
  "current_password": "oldpassword",
  "new_password": "newpassword123",
  "new_password_confirmation": "newpassword123"
}
```

### Forgot Password

**POST** `/auth/forgot-password`

Request password reset OTP.

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

### Reset Password

**POST** `/auth/reset-password`

Reset password using OTP.

**Request Body:**
```json
{
  "email": "user@example.com",
  "otp": "123456",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

---

## Dashboard

### Get Dashboard

**GET** `/dashboard`

Get dashboard data based on user role.

**Response:**
```json
{
  "success": true,
  "type": "staff", // admin, ceo, hod, accountant, hr, staff
  "data": {
    "my_leave_requests": 5,
    "pending_leave_requests": 2,
    "my_tasks": 10,
    "pending_tasks": 3
  }
}
```

### Get Dashboard Statistics

**GET** `/dashboard/stats`

Get dashboard statistics.

**Response:**
```json
{
  "success": true,
  "data": {
    "total_users": 100,
    "active_users": 95,
    "pending_leave_requests": 5
  }
}
```

### Get Notifications

**GET** `/dashboard/notifications?limit=20`

Get recent notifications.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "message": "Your leave request has been approved",
      "link": "/modules/leave/1",
      "is_read": false,
      "created_at": "2024-01-01T12:00:00Z"
    }
  ],
  "unread_count": 5
}
```

---

## Profile

### Get Profile

**GET** `/profile`

Get user profile information.

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+255712345678",
    "employee_id": "EMP001",
    "photo": "https://domain.com/storage/photos/photo.jpg",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "marital_status": "single",
    "nationality": "Tanzanian",
    "address": "123 Main St",
    "hire_date": "2020-01-01",
    "primary_department": {
      "id": 1,
      "name": "IT Department"
    },
    "roles": [...]
  }
}
```

### Update Profile

**PUT** `/profile`

Update user profile.

**Request Body:**
```json
{
  "name": "John Doe",
  "phone": "+255712345678",
  "date_of_birth": "1990-01-01",
  "gender": "male",
  "marital_status": "single",
  "nationality": "Tanzanian",
  "address": "123 Main St"
}
```

### Update Profile Photo

**POST** `/profile/photo`

Update profile photo.

**Request:** Multipart form data
- `photo`: Image file (jpeg, png, jpg, gif, max 2MB)

**Response:**
```json
{
  "success": true,
  "message": "Photo updated successfully",
  "data": {
    "photo": "https://domain.com/storage/photos/new_photo.jpg"
  }
}
```

---

## Users

### Get Users

**GET** `/users?per_page=20&department_id=1`

Get list of users (filtered by role).

**Query Parameters:**
- `per_page`: Number of items per page (default: 20)
- `department_id`: Filter by department
- `is_active`: Filter by active status (true/false)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "user@example.com",
      "employee_id": "EMP001",
      "photo": "https://domain.com/storage/photos/photo.jpg"
    }
  ]
}
```

### Get Single User

**GET** `/users/{id}`

Get single user details.

### Search Users

**GET** `/users/search?q=john`

Search users by name or email.

---

## Departments

### Get Departments

**GET** `/departments`

Get all active departments.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "IT Department",
      "code": "IT",
      "description": "Information Technology",
      "head": {
        "id": 5,
        "name": "Jane Smith",
        "email": "jane@example.com"
      },
      "member_count": 25
    }
  ]
}
```

### Get Single Department

**GET** `/departments/{id}`

Get single department details.

### Get Department Members

**GET** `/departments/{id}/members`

Get all members of a department.

---

## Attendance

### Get Attendance Records

**GET** `/attendance?date=2024-01-01&user_id=1&per_page=20`

Get attendance records with filters.

**Query Parameters:**
- `date`: Filter by date (YYYY-MM-DD)
- `date_from`: Start date
- `date_to`: End date
- `user_id`: Filter by user
- `enroll_id`: Filter by enroll ID
- `status`: Filter by status
- `per_page`: Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user": {
        "id": 1,
        "name": "John Doe",
        "enroll_id": "123"
      },
      "attendance_date": "2024-01-01",
      "check_in_time": "2024-01-01 08:00:00",
      "check_out_time": "2024-01-01 17:00:00",
      "status": "1",
      "verify_mode": "Fingerprint",
      "device_ip": "192.168.1.100"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 100,
    "per_page": 20,
    "last_page": 5
  }
}
```

### Get My Attendance

**GET** `/attendance/my?date_from=2024-01-01&date_to=2024-01-31`

Get current user's attendance records.

### Get Single Attendance

**GET** `/attendance/{id}`

Get single attendance record.

### Get Daily Summary

**GET** `/attendance/daily/2024-01-01`

Get daily attendance summary.

**Response:**
```json
{
  "success": true,
  "date": "2024-01-01",
  "data": [
    {
      "user": {
        "id": 1,
        "name": "John Doe",
        "enroll_id": "123"
      },
      "date": "2024-01-01",
      "check_in": "08:00:00",
      "check_out": "17:00:00",
      "duration": "9:00:00"
    }
  ],
  "total": 25
}
```

### Get Attendance Summary

**GET** `/attendance/summary?month=2024-01`

Get attendance summary for a period.

### Check In

**POST** `/attendance/check-in`

Manual check-in (if allowed).

**Request Body:**
```json
{
  "latitude": -6.7924,
  "longitude": 39.2083,
  "notes": "Working from office"
}
```

### Check Out

**POST** `/attendance/check-out`

Manual check-out (if allowed).

**Request Body:**
```json
{
  "latitude": -6.7924,
  "longitude": 39.2083,
  "notes": "End of work day"
}
```

---

## Leave Management

### Get Leave Requests

**GET** `/leaves?status=pending&leave_type_id=1&per_page=20`

Get leave requests (filtered by role).

**Query Parameters:**
- `status`: Filter by status
- `leave_type_id`: Filter by leave type
- `per_page`: Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "employee": {
        "id": 1,
        "name": "John Doe",
        "email": "user@example.com"
      },
      "leave_type": {
        "id": 1,
        "name": "Annual Leave"
      },
      "start_date": "2024-02-01",
      "end_date": "2024-02-05",
      "days": 5,
      "reason": "Family vacation",
      "status": "pending",
      "created_at": "2024-01-15T10:00:00Z"
    }
  ]
}
```

### Get My Leave Requests

**GET** `/leaves/my`

Get current user's leave requests.

### Get Pending Leave Requests

**GET** `/leaves/pending`

Get pending leave requests (managers only).

### Get Single Leave Request

**GET** `/leaves/{id}`

Get single leave request with details.

### Create Leave Request

**POST** `/leaves`

Create a new leave request.

**Request Body:**
```json
{
  "leave_type_id": 1,
  "start_date": "2024-02-01",
  "end_date": "2024-02-05",
  "reason": "Family vacation",
  "dependents": [
    {
      "name": "Jane Doe",
      "relationship": "Spouse",
      "fare_amount": 50000
    }
  ]
}
```

### Update Leave Request

**PUT** `/leaves/{id}`

Update leave request (only if pending).

**Request Body:**
```json
{
  "start_date": "2024-02-01",
  "end_date": "2024-02-05",
  "reason": "Updated reason"
}
```

### Cancel Leave Request

**POST** `/leaves/{id}/cancel`

Cancel a leave request.

### Approve Leave Request

**POST** `/leaves/{id}/approve`

Approve a leave request (managers only).

### Reject Leave Request

**POST** `/leaves/{id}/reject`

Reject a leave request (managers only).

**Request Body:**
```json
{
  "rejection_reason": "Insufficient leave balance"
}
```

### Get Leave Balance

**GET** `/leaves/balance`

Get current user's leave balance.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "leave_type": {
        "id": 1,
        "name": "Annual Leave"
      },
      "total_days": 21,
      "used_days": 5,
      "remaining_days": 16
    }
  ]
}
```

### Get Leave Types

**GET** `/leaves/types`

Get all available leave types.

---

## Task Management

### Get Tasks

**GET** `/tasks?status=in_progress&per_page=20`

Get tasks (filtered by role).

**Query Parameters:**
- `status`: Filter by status
- `per_page`: Items per page

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Website Redesign",
      "description": "Redesign company website",
      "status": "in_progress",
      "priority": "High",
      "start_date": "2024-01-01",
      "end_date": "2024-03-31",
      "team_leader": {
        "id": 5,
        "name": "Jane Smith"
      },
      "activities_count": 5,
      "created_at": "2024-01-01T10:00:00Z"
    }
  ]
}
```

### Get My Tasks

**GET** `/tasks/my`

Get tasks where user is team leader.

### Get Assigned Tasks

**GET** `/tasks/assigned`

Get tasks assigned to current user.

### Get Single Task

**GET** `/tasks/{id}`

Get single task with details.

### Create Task

**POST** `/tasks`

Create a new task (managers only).

**Request Body:**
```json
{
  "name": "Website Redesign",
  "description": "Redesign company website",
  "start_date": "2024-01-01",
  "end_date": "2024-03-31",
  "team_leader_id": 5,
  "priority": "High",
  "status": "Not Started"
}
```

### Update Task

**PUT** `/tasks/{id}`

Update a task.

### Complete Task

**POST** `/tasks/{id}/complete`

Mark task as completed.

### Assign Users to Task

**POST** `/tasks/{id}/assign`

Assign users to a task activity.

**Request Body:**
```json
{
  "activity_id": 10,
  "user_ids": [1, 2, 3]
}
```

### Get Task Activities

**GET** `/tasks/{id}/activities`

Get all activities for a task.

### Create Activity

**POST** `/tasks/{id}/activities`

Create a new activity for a task.

**Request Body:**
```json
{
  "name": "Design mockups",
  "start_date": "2024-01-01",
  "end_date": "2024-01-15",
  "priority": "High"
}
```

### Complete Activity

**POST** `/tasks/{id}/activities/{activityId}/complete`

Mark activity as completed.

### Submit Activity Report

**POST** `/tasks/{id}/activities/{activityId}/report`

Submit a progress report for an activity.

**Request Body:**
```json
{
  "report": "Completed design mockups for homepage and product pages"
}
```

---

## File Management

### Digital Files

#### Get Digital Files

**GET** `/files/digital?folder_id=1&per_page=20`

Get digital files.

#### Get Digital Folders

**GET** `/files/digital/folders`

Get all digital file folders.

#### Get Folder Contents

**GET** `/files/digital/folders/{id}`

Get files in a specific folder.

#### Get Single File

**GET** `/files/digital/{id}`

Get single digital file details.

#### Upload File

**POST** `/files/digital/upload`

Upload a digital file.

**Request:** Multipart form data
- `file`: File to upload (max 10MB)
- `folder_id`: Folder ID
- `name`: File name (optional)
- `description`: File description (optional)

#### Download File

**GET** `/files/digital/{id}/download`

Download a digital file.

#### Request File Access

**POST** `/files/digital/{id}/request-access`

Request access to a file.

**Request Body:**
```json
{
  "reason": "Need access for project work"
}
```

#### Get My Access Requests

**GET** `/files/digital/my-requests`

Get current user's file access requests.

#### Get Pending Access Requests

**GET** `/files/digital/pending-requests`

Get pending access requests (managers only).

#### Approve Access Request

**POST** `/files/digital/requests/{id}/approve`

Approve a file access request.

#### Reject Access Request

**POST** `/files/digital/requests/{id}/reject`

Reject a file access request.

#### Search Digital Files

**GET** `/files/digital/search?q=report`

Search digital files.

### Physical Racks

#### Get Physical Files

**GET** `/files/physical`

Get physical rack files.

#### Get Physical Categories

**GET** `/files/physical/categories`

Get physical rack categories.

#### Get Rack Contents

**GET** `/files/physical/racks/{id}`

Get files in a physical rack.

#### Get Single Physical File

**GET** `/files/physical/{id}`

Get single physical file details.

#### Request Physical File

**POST** `/files/physical/{id}/request`

Request a physical file.

**Request Body:**
```json
{
  "reason": "Need for reference"
}
```

#### Get My Physical Requests

**GET** `/files/physical/my-requests`

Get current user's physical file requests.

#### Get Pending Physical Requests

**GET** `/files/physical/pending-requests`

Get pending physical file requests (managers only).

#### Approve Physical Request

**POST** `/files/physical/requests/{id}/approve`

Approve a physical file request.

#### Reject Physical Request

**POST** `/files/physical/requests/{id}/reject`

Reject a physical file request.

#### Return Physical File

**POST** `/files/physical/requests/{id}/return`

Return a physical file.

---

## Finance

### Petty Cash

#### Get Petty Cash Vouchers

**GET** `/finance/petty-cash?status=pending&per_page=20`

Get petty cash vouchers.

#### Get Single Voucher

**GET** `/finance/petty-cash/{id}`

Get single petty cash voucher.

#### Create Voucher

**POST** `/finance/petty-cash`

Create a petty cash voucher.

**Request Body:**
```json
{
  "amount": 50000,
  "purpose": "Office supplies",
  "items": [
    {
      "description": "Stationery",
      "amount": 30000
    },
    {
      "description": "Printer paper",
      "amount": 20000
    }
  ]
}
```

#### Update Voucher

**PUT** `/finance/petty-cash/{id}`

Update a voucher (only if pending).

#### Approve Voucher

**POST** `/finance/petty-cash/{id}/approve`

Approve a voucher (managers only).

#### Reject Voucher

**POST** `/finance/petty-cash/{id}/reject`

Reject a voucher (managers only).

### Imprest

#### Get Imprest Requests

**GET** `/finance/imprest?per_page=20`

Get imprest requests.

#### Get Single Request

**GET** `/finance/imprest/{id}`

Get single imprest request.

#### Create Request

**POST** `/finance/imprest`

Create an imprest request.

**Request Body:**
```json
{
  "amount": 500000,
  "purpose": "Business trip to Dar es Salaam"
}
```

#### Approve Request

**POST** `/finance/imprest/{id}/approve`

Approve an imprest request.

#### Reject Request

**POST** `/finance/imprest/{id}/reject`

Reject an imprest request.

#### Submit Receipt

**POST** `/finance/imprest/{id}/submit-receipt`

Submit receipt for imprest.

**Request:** Multipart form data
- `receipt_file`: Receipt file (PDF, JPG, PNG, max 5MB)
- `amount_used`: Amount used

### Payroll

#### Get Payroll Records

**GET** `/finance/payroll?per_page=20`

Get payroll records.

#### Get My Payroll

**GET** `/finance/payroll/my`

Get current user's payroll records.

#### Get Single Payroll

**GET** `/finance/payroll/{id}`

Get single payroll record with details.

---

## HR Management

### Permission Requests

#### Get Permission Requests

**GET** `/hr/permissions?per_page=20`

Get permission requests.

#### Get My Permissions

**GET** `/hr/permissions/my`

Get current user's permission requests.

#### Get Single Permission

**GET** `/hr/permissions/{id}`

Get single permission request.

#### Create Permission Request

**POST** `/hr/permissions`

Create a permission request.

**Request Body:**
```json
{
  "start_date": "2024-02-01",
  "end_date": "2024-02-01",
  "reason": "Personal appointment"
}
```

#### Approve Permission

**POST** `/hr/permissions/{id}/approve`

Approve a permission request.

#### Reject Permission

**POST** `/hr/permissions/{id}/reject`

Reject a permission request.

#### Confirm Return

**POST** `/hr/permissions/{id}/confirm-return`

Confirm return from permission.

### Sick Sheets

#### Get Sick Sheets

**GET** `/hr/sick-sheets?per_page=20`

Get sick sheet records.

#### Get My Sick Sheets

**GET** `/hr/sick-sheets/my`

Get current user's sick sheets.

#### Get Single Sick Sheet

**GET** `/hr/sick-sheets/{id}`

Get single sick sheet.

#### Create Sick Sheet

**POST** `/hr/sick-sheets`

Submit a sick sheet.

**Request:** Multipart form data
- `start_date`: Start date
- `end_date`: End date
- `medical_document`: Medical document (PDF, JPG, PNG, DOC, DOCX, max 5MB)
- `doctor_name`: Doctor name (optional)
- `hospital_name`: Hospital name (optional)

#### Approve Sick Sheet

**POST** `/hr/sick-sheets/{id}/approve`

Approve a sick sheet.

#### Reject Sick Sheet

**POST** `/hr/sick-sheets/{id}/reject`

Reject a sick sheet.

### Assessments

#### Get Assessments

**GET** `/hr/assessments?per_page=20`

Get performance assessments.

#### Get My Assessments

**GET** `/hr/assessments/my`

Get current user's assessments.

#### Get Single Assessment

**GET** `/hr/assessments/{id}`

Get single assessment.

#### Create Assessment

**POST** `/hr/assessments`

Create a performance assessment.

**Request Body:**
```json
{
  "title": "Q1 2024 Performance",
  "activities": [
    {
      "name": "Complete project A",
      "contribution_percentage": 50
    },
    {
      "name": "Team collaboration",
      "contribution_percentage": 50
    }
  ]
}
```

#### Submit Progress Report

**POST** `/hr/assessments/{id}/progress`

Submit a progress report for an assessment.

**Request Body:**
```json
{
  "activity_id": 10,
  "progress_percentage": 75,
  "notes": "Almost complete"
}
```

#### Get Assessment Progress

**GET** `/hr/assessments/{id}/progress`

Get progress reports for an assessment.

### Recruitment

#### Get Jobs

**GET** `/hr/jobs`

Get available job postings.

#### Get Single Job

**GET** `/hr/jobs/{id}`

Get single job details.

#### Get Job Applications

**GET** `/hr/jobs/{id}/applications`

Get applications for a job (HR only).

#### Apply for Job

**POST** `/hr/jobs/{id}/apply`

Apply for a job.

**Request:** Multipart form data
- `cover_letter`: Cover letter text
- `resume`: Resume file (PDF, DOC, DOCX, max 5MB)

### Employees

#### Get Employees

**GET** `/hr/employees?per_page=20`

Get employee list (HR/HOD only).

#### Get Single Employee

**GET** `/hr/employees/{id}`

Get single employee details.

### Incidents

#### Get Incidents

**GET** `/incidents?per_page=20`

Get incident reports.

#### Get My Incidents

**GET** `/incidents/my`

Get current user's incident reports.

#### Get Single Incident

**GET** `/incidents/{id}`

Get single incident details.

#### Create Incident

**POST** `/incidents`

Report an incident.

**Request Body:**
```json
{
  "title": "Network outage",
  "description": "Network connection lost in building A",
  "severity": "high",
  "location": "Building A, Floor 2"
}
```

#### Update Incident

**PUT** `/incidents/{id}`

Update an incident.

**Request Body:**
```json
{
  "status": "resolved",
  "notes": "Issue resolved"
}
```

#### Add Incident Update

**POST** `/incidents/{id}/update`

Add an update to an incident.

**Request Body:**
```json
{
  "update_text": "Working on resolving the issue"
}
```

---

## Notifications

### Get Notifications

**GET** `/notifications?limit=50`

Get all notifications.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "message": "Your leave request has been approved",
      "link": "/modules/leave/1",
      "is_read": false,
      "created_at": "2024-01-01T12:00:00Z"
    }
  ],
  "unread_count": 5
}
```

### Get Unread Notifications

**GET** `/notifications/unread?limit=20`

Get unread notifications only.

### Get Single Notification

**GET** `/notifications/{id}`

Get single notification (automatically marks as read).

### Mark as Read

**POST** `/notifications/{id}/read`

Mark a notification as read.

### Mark All as Read

**POST** `/notifications/read-all`

Mark all notifications as read.

---

## Error Responses

All endpoints may return error responses in the following format:

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": ["Error message for field"]
  }
}
```

### HTTP Status Codes

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

---

## Rate Limiting

API requests are rate-limited to prevent abuse. Current limits:
- 60 requests per minute per user
- 1000 requests per hour per user

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Time when limit resets

---

## Pagination

List endpoints support pagination with the following query parameters:
- `per_page`: Number of items per page (default: 20, max: 100)
- `page`: Page number (default: 1)

Pagination response includes:
```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "total": 100,
    "per_page": 20,
    "last_page": 5
  }
}
```

---

## File Uploads

File upload endpoints accept multipart form data. Supported file types and sizes:
- Images: JPG, JPEG, PNG, GIF (max 2-5MB)
- Documents: PDF, DOC, DOCX (max 5-10MB)

---

## Best Practices

1. **Always include Authorization header** for protected endpoints
2. **Handle errors gracefully** - Check `success` field in responses
3. **Implement token refresh** - Refresh tokens before expiration
4. **Cache data appropriately** - Cache static data like departments, leave types
5. **Use pagination** - Don't fetch all records at once
6. **Handle network errors** - Implement retry logic for failed requests
7. **Validate data client-side** - Reduce unnecessary API calls
8. **Use appropriate HTTP methods** - GET for reading, POST for creating, PUT for updating

---

## Support

For API support and questions:
- Email: support@ofisilink.com
- Documentation: https://docs.ofisilink.com
- Status Page: https://status.ofisilink.com

---

## Changelog

### Version 1.0.0 (2024-01-01)
- Initial API release
- All core endpoints implemented
- Authentication with Bearer tokens
- Support for all major modules

---

**Last Updated:** January 2024
**API Version:** 1.0.0







