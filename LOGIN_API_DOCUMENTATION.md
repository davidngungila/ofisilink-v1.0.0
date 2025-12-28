# Login API Documentation

## Base URL
```
https://192.168.100.105:8004/api/mobile/v1
```

## Overview

The Login API provides multiple authentication methods for mobile applications:
1. **Email/Password Login** - Traditional login with credentials
2. **OTP Login** - One-Time Password authentication via SMS/Email
3. **Password Reset** - Forgot password and reset functionality

All authentication endpoints return a Bearer token that must be used for subsequent API requests.

---

## Authentication Endpoints

### 1. Login with Email and Password

**Endpoint:** `POST /auth/login`

**Description:** Authenticate user with email and password. Returns user information and access token.

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123",
  "device_name": "iPhone 14 Pro"
}
```

**Parameters:**
- `email` (required, string): User's email address
- `password` (required, string): User's password
- `device_name` (optional, string): Name/identifier of the device making the request

**Success Response (200 OK):**
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
      "photo": "https://192.168.100.105:8004/storage/photos/photo.jpg",
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
      ],
      "permissions": [
        {
          "id": 1,
          "name": "view-dashboard",
          "display_name": "View Dashboard"
        }
      ]
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
    "token_type": "Bearer"
  }
}
```

**Error Responses:**

**401 Unauthorized - Invalid Credentials:**
```json
{
  "success": false,
  "message": "Invalid credentials"
}
```

**403 Forbidden - Account Inactive:**
```json
{
  "success": false,
  "message": "Account is inactive. Please contact administrator."
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

**cURL Example:**
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "password123",
    "device_name": "iPhone 14 Pro"
  }'
```

**JavaScript/Fetch Example:**
```javascript
const response = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123',
    device_name: 'iPhone 14 Pro'
  })
});

const data = await response.json();
if (data.success) {
  const token = data.data.token;
  const user = data.data.user;
  // Store token for future requests
  localStorage.setItem('auth_token', token);
}
```

**React Native Example:**
```javascript
import axios from 'axios';

const login = async (email, password) => {
  try {
    const response = await axios.post(
      'https://192.168.100.105:8004/api/mobile/v1/auth/login',
      {
        email: email,
        password: password,
        device_name: 'React Native App'
      }
    );
    
    if (response.data.success) {
      const token = response.data.data.token;
      const user = response.data.data.user;
      
      // Store token securely
      await AsyncStorage.setItem('auth_token', token);
      await AsyncStorage.setItem('user_data', JSON.stringify(user));
      
      return { success: true, token, user };
    }
  } catch (error) {
    if (error.response) {
      return { 
        success: false, 
        message: error.response.data.message 
      };
    }
    return { success: false, message: 'Network error' };
  }
};
```

---

### 2. Request OTP for Login

**Endpoint:** `POST /auth/login-otp`

**Description:** Request a One-Time Password (OTP) to be sent to user's registered phone number and email for login.

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Parameters:**
- `email` (required, string): User's email address

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "OTP sent to your registered phone number",
  "data": {
    "otp": "123456",
    "expires_at": "2024-01-01T12:10:00Z"
  }
}
```

**Note:** In production, the `otp` field should NOT be returned in the response. It's included here for testing purposes only.

**Error Responses:**

**404 Not Found - User Not Found:**
```json
{
  "success": false,
  "message": "User not found"
}
```

**403 Forbidden - Account Inactive:**
```json
{
  "success": false,
  "message": "Account is inactive"
}
```

**cURL Example:**
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/auth/login-otp \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com"
  }'
```

**JavaScript Example:**
```javascript
const requestOTP = async (email) => {
  const response = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/login-otp', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email })
  });
  
  const data = await response.json();
  return data;
};
```

---

### 3. Verify OTP and Login

**Endpoint:** `POST /auth/verify-otp`

**Description:** Verify the OTP code and complete login. Returns user information and access token.

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "otp": "123456",
  "device_name": "iPhone 14 Pro"
}
```

**Parameters:**
- `email` (required, string): User's email address
- `otp` (required, string, 6 digits): The OTP code received
- `device_name` (optional, string): Name/identifier of the device

**Success Response (200 OK):**
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
      "photo": "https://192.168.100.105:8004/storage/photos/photo.jpg",
      "primary_department": {
        "id": 1,
        "name": "IT Department"
      },
      "roles": [...],
      "permissions": [...]
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890",
    "token_type": "Bearer"
  }
}
```

**Error Responses:**

**401 Unauthorized - Invalid/Expired OTP:**
```json
{
  "success": false,
  "message": "Invalid or expired OTP"
}
```

**404 Not Found - User Not Found:**
```json
{
  "success": false,
  "message": "User not found"
}
```

**cURL Example:**
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/auth/verify-otp \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "otp": "123456",
    "device_name": "iPhone 14 Pro"
  }'
```

**React Native Example:**
```javascript
const verifyOTP = async (email, otp) => {
  try {
    const response = await axios.post(
      'https://192.168.100.105:8004/api/mobile/v1/auth/verify-otp',
      {
        email: email,
        otp: otp,
        device_name: 'React Native App'
      }
    );
    
    if (response.data.success) {
      const token = response.data.data.token;
      const user = response.data.data.user;
      
      await AsyncStorage.setItem('auth_token', token);
      await AsyncStorage.setItem('user_data', JSON.stringify(user));
      
      return { success: true, token, user };
    }
  } catch (error) {
    return { 
      success: false, 
      message: error.response?.data?.message || 'Verification failed' 
    };
  }
};
```

---

### 4. Resend OTP

**Endpoint:** `POST /auth/resend-otp`

**Description:** Resend OTP code to user's registered phone number and email.

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Parameters:**
- `email` (required, string): User's email address

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "OTP resent successfully",
  "data": {
    "otp": "654321",
    "expires_at": "2024-01-01T12:15:00Z"
  }
}
```

**cURL Example:**
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/auth/resend-otp \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com"
  }'
```

---

### 5. Get Current User

**Endpoint:** `GET /auth/me`

**Description:** Get information about the currently authenticated user.

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "phone": "+255712345678",
    "employee_id": "EMP001",
    "photo": "https://192.168.100.105:8004/storage/photos/photo.jpg",
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
    ],
    "permissions": [
      {
        "id": 1,
        "name": "view-dashboard",
        "display_name": "View Dashboard"
      }
    ]
  }
}
```

**Error Responses:**

**401 Unauthorized - Invalid/Expired Token:**
```json
{
  "success": false,
  "message": "Unauthenticated"
}
```

**cURL Example:**
```bash
curl -X GET https://192.168.100.105:8004/api/mobile/v1/auth/me \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**JavaScript Example:**
```javascript
const getCurrentUser = async () => {
  const token = localStorage.getItem('auth_token');
  
  const response = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/me', {
    method: 'GET',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  const data = await response.json();
  return data;
};
```

---

### 6. Logout

**Endpoint:** `POST /auth/logout`

**Description:** Logout and revoke the current access token.

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

**cURL Example:**
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/auth/logout \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json"
```

**JavaScript Example:**
```javascript
const logout = async () => {
  const token = localStorage.getItem('auth_token');
  
  const response = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/logout', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  const data = await response.json();
  
  if (data.success) {
    // Remove token from storage
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_data');
  }
  
  return data;
};
```

---

### 7. Refresh Token

**Endpoint:** `POST /auth/refresh`

**Description:** Generate a new access token and revoke the old one. Useful for token rotation.

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "device_name": "iPhone 14 Pro"
}
```

**Parameters:**
- `device_name` (optional, string): Name/identifier of the device

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "token": "2|newtokenabcdefghijklmnopqrstuvwxyz",
    "token_type": "Bearer"
  }
}
```

**cURL Example:**
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/auth/refresh \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "device_name": "iPhone 14 Pro"
  }'
```

---

### 8. Change Password

**Endpoint:** `PUT /auth/change-password`

**Description:** Change user's password. Requires current password.

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "current_password": "oldpassword123",
  "new_password": "newpassword456",
  "new_password_confirmation": "newpassword456"
}
```

**Parameters:**
- `current_password` (required, string): Current password
- `new_password` (required, string, min: 8): New password
- `new_password_confirmation` (required, string): Confirmation of new password (must match)

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

**Error Responses:**

**401 Unauthorized - Incorrect Current Password:**
```json
{
  "success": false,
  "message": "Current password is incorrect"
}
```

**422 Validation Error:**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "new_password": [
      "The new password must be at least 8 characters.",
      "The new password confirmation does not match."
    ]
  }
}
```

**cURL Example:**
```bash
curl -X PUT https://192.168.100.105:8004/api/mobile/v1/auth/change-password \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "oldpassword123",
    "new_password": "newpassword456",
    "new_password_confirmation": "newpassword456"
  }'
```

---

### 9. Forgot Password

**Endpoint:** `POST /auth/forgot-password`

**Description:** Request a password reset OTP to be sent to user's registered phone number and email.

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "user@example.com"
}
```

**Parameters:**
- `email` (required, string): User's email address

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Password reset OTP sent",
  "data": {
    "otp": "789012",
    "expires_at": "2024-01-01T12:30:00Z"
  }
}
```

**Note:** The API returns success even if the email doesn't exist (for security reasons). The OTP is sent only if the email exists.

**cURL Example:**
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com"
  }'
```

---

### 10. Reset Password

**Endpoint:** `POST /auth/reset-password`

**Description:** Reset password using OTP code received via email/SMS.

**Request Headers:**
```
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "user@example.com",
  "otp": "789012",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Parameters:**
- `email` (required, string): User's email address
- `otp` (required, string, 6 digits): OTP code received
- `password` (required, string, min: 8): New password
- `password_confirmation` (required, string): Confirmation of new password (must match)

**Success Response (200 OK):**
```json
{
  "success": true,
  "message": "Password reset successfully"
}
```

**Error Responses:**

**401 Unauthorized - Invalid/Expired OTP:**
```json
{
  "success": false,
  "message": "Invalid or expired OTP"
}
```

**404 Not Found - User Not Found:**
```json
{
  "success": false,
  "message": "User not found"
}
```

**cURL Example:**
```bash
curl -X POST https://192.168.100.105:8004/api/mobile/v1/auth/reset-password \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "otp": "789012",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }'
```

---

## Complete Login Flow Examples

### Flow 1: Email/Password Login

```javascript
// Step 1: Login
const loginResponse = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'user@example.com',
    password: 'password123',
    device_name: 'My App'
  })
});

const loginData = await loginResponse.json();

if (loginData.success) {
  // Step 2: Store token
  const token = loginData.data.token;
  localStorage.setItem('auth_token', token);
  
  // Step 3: Use token for subsequent requests
  const userResponse = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/me', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  });
  
  const userData = await userResponse.json();
  console.log('User:', userData.data);
}
```

### Flow 2: OTP Login

```javascript
// Step 1: Request OTP
const otpRequestResponse = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/login-otp', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'user@example.com' })
});

const otpRequestData = await otpRequestResponse.json();

if (otpRequestData.success) {
  // Step 2: User enters OTP (in real app, get from SMS/Email)
  const otpCode = prompt('Enter OTP:'); // In real app, use SMS/Email
  
  // Step 3: Verify OTP
  const verifyResponse = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/verify-otp', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      email: 'user@example.com',
      otp: otpCode,
      device_name: 'My App'
    })
  });
  
  const verifyData = await verifyResponse.json();
  
  if (verifyData.success) {
    // Step 4: Store token
    const token = verifyData.data.token;
    localStorage.setItem('auth_token', token);
  }
}
```

### Flow 3: Password Reset

```javascript
// Step 1: Request password reset OTP
const forgotPasswordResponse = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/forgot-password', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'user@example.com' })
});

const forgotPasswordData = await forgotPasswordResponse.json();

if (forgotPasswordData.success) {
  // Step 2: User enters OTP and new password
  const otpCode = prompt('Enter OTP from email/SMS:');
  const newPassword = prompt('Enter new password:');
  const confirmPassword = prompt('Confirm new password:');
  
  // Step 3: Reset password
  const resetResponse = await fetch('https://192.168.100.105:8004/api/mobile/v1/auth/reset-password', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      email: 'user@example.com',
      otp: otpCode,
      password: newPassword,
      password_confirmation: confirmPassword
    })
  });
  
  const resetData = await resetResponse.json();
  
  if (resetData.success) {
    alert('Password reset successfully!');
  }
}
```

---

## Token Usage

After successful login, include the token in all subsequent API requests:

```
Authorization: Bearer {token}
```

**Example:**
```javascript
const token = localStorage.getItem('auth_token');

fetch('https://192.168.100.105:8004/api/mobile/v1/dashboard', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  }
});
```

---

## Error Handling

Always check the `success` field in the response:

```javascript
const response = await fetch(url, options);
const data = await response.json();

if (data.success) {
  // Handle success
  console.log(data.data);
} else {
  // Handle error
  console.error(data.message);
  if (data.errors) {
    // Handle validation errors
    console.error(data.errors);
  }
}
```

---

## Security Notes

1. **Never store passwords in plain text** - Always hash passwords
2. **Use HTTPS** - Always use HTTPS in production
3. **Store tokens securely** - Use secure storage (Keychain on iOS, Keystore on Android)
4. **Token expiration** - Tokens may expire; implement refresh logic
5. **OTP expiration** - OTPs expire after 10 minutes (configurable)
6. **Rate limiting** - API endpoints are rate-limited to prevent abuse

---

## Testing

### Test Credentials

Use your actual user credentials from the system. If you need test accounts, create them through the admin panel.

### Postman Collection

Import these endpoints into Postman for easy testing:

1. Create a new collection: "OfisiLink Mobile API"
2. Set base URL: `https://192.168.100.105:8004/api/mobile/v1`
3. Add environment variable: `token` for storing the Bearer token
4. Add all login endpoints
5. Set up automatic token injection in collection settings

---

## Support

For issues or questions:
- Check the main API documentation: `MOBILE_API_DOCUMENTATION.md`
- Review setup guide: `MOBILE_API_SETUP.md`
- Contact development team

---

**Last Updated:** January 2024  
**API Version:** 1.0.0







