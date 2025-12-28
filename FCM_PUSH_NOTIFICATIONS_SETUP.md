# FCM (Firebase Cloud Messaging) Push Notifications Setup Guide

## Overview

This guide explains how to set up and use Firebase Cloud Messaging (FCM) push notifications in the OfisiLink mobile application.

## Prerequisites

1. Firebase project created at [Firebase Console](https://console.firebase.google.com/)
2. FCM Server Key from Firebase project settings
3. Mobile app configured with Firebase SDK

## Setup Steps

### 1. Get FCM Server Key

1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Select your project (or create a new one)
3. Click on the gear icon ⚙️ next to "Project Overview"
4. Select "Project settings"
5. Go to the "Cloud Messaging" tab
6. Copy the **Server key** (under "Cloud Messaging API (Legacy)")

### 2. Configure FCM Server Key in OfisiLink

#### Option A: Using System Settings (Recommended)

1. Login to OfisiLink admin panel
2. Go to System Settings
3. Add a new setting:
   - **Key:** `fcm_server_key`
   - **Value:** Your FCM Server Key
   - **Type:** Text

#### Option B: Using Environment Variable

Add to your `.env` file:

```env
FCM_SERVER_KEY=your_fcm_server_key_here
```

### 3. Run Migration

Run the migration to create the device_tokens table:

```bash
php artisan migrate
```

## Mobile App Integration

### Flutter Example

#### 1. Add Dependencies

Add to `pubspec.yaml`:

```yaml
dependencies:
  firebase_core: ^2.24.2
  firebase_messaging: ^14.7.9
  flutter_local_notifications: ^16.3.0
```

#### 2. Initialize Firebase

```dart
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  runApp(MyApp());
}
```

#### 3. Get FCM Token and Register

```dart
import 'package:firebase_messaging/firebase_messaging.dart';
import 'dart:io';

class PushNotificationService {
  final FirebaseMessaging _fcm = FirebaseMessaging.instance;
  
  Future<void> initialize() async {
    // Request permission
    NotificationSettings settings = await _fcm.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    
    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      // Get FCM token
      String? token = await _fcm.getToken();
      
      if (token != null) {
        // Register token with your API
        await registerDeviceToken(token);
      }
      
      // Listen for token refresh
      _fcm.onTokenRefresh.listen((newToken) {
        registerDeviceToken(newToken);
      });
    }
  }
  
  Future<void> registerDeviceToken(String token) async {
    try {
      final response = await http.post(
        Uri.parse('https://192.168.100.105:8004/api/mobile/v1/device/register'),
        headers: {
          'Authorization': 'Bearer $yourAuthToken',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'token': token,
          'device_type': Platform.isIOS ? 'ios' : 'android',
          'device_id': await _getDeviceId(),
          'device_name': await _getDeviceName(),
          'app_version': '1.0.0',
          'os_version': Platform.operatingSystemVersion,
        }),
      );
      
      if (response.statusCode == 200 || response.statusCode == 201) {
        print('Device token registered successfully');
      }
    } catch (e) {
      print('Error registering device token: $e');
    }
  }
  
  Future<String> _getDeviceId() async {
    // Use device_info_plus package
    final deviceInfo = DeviceInfoPlugin();
    if (Platform.isAndroid) {
      final androidInfo = await deviceInfo.androidInfo;
      return androidInfo.id;
    } else if (Platform.isIOS) {
      final iosInfo = await deviceInfo.iosInfo;
      return iosInfo.identifierForVendor ?? '';
    }
    return '';
  }
  
  Future<String> _getDeviceName() async {
    final deviceInfo = DeviceInfoPlugin();
    if (Platform.isAndroid) {
      final androidInfo = await deviceInfo.androidInfo;
      return '${androidInfo.manufacturer} ${androidInfo.model}';
    } else if (Platform.isIOS) {
      final iosInfo = await deviceInfo.iosInfo;
      return '${iosInfo.name} (${iosInfo.model})';
    }
    return 'Unknown Device';
  }
}
```

#### 4. Handle Background Messages

Create `firebase_messaging_background.dart`:

```dart
import 'package:firebase_messaging/firebase_messaging.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  print('Handling background message: ${message.messageId}');
}
```

Register in `main.dart`:

```dart
FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
```

#### 5. Handle Foreground Messages

```dart
FirebaseMessaging.onMessage.listen((RemoteMessage message) {
  print('Received foreground message: ${message.notification?.title}');
  
  // Show local notification
  showLocalNotification(
    title: message.notification?.title ?? 'OfisiLink',
    body: message.notification?.body ?? '',
    payload: message.data['link'],
  );
});
```

### React Native Example

#### 1. Install Dependencies

```bash
npm install @react-native-firebase/app @react-native-firebase/messaging
```

#### 2. Get FCM Token

```javascript
import messaging from '@react-native-firebase/messaging';

async function registerDeviceToken() {
  try {
    // Request permission
    const authStatus = await messaging().requestPermission();
    
    if (authStatus === messaging.AuthorizationStatus.AUTHORIZED) {
      // Get FCM token
      const token = await messaging().getToken();
      
      // Register with API
      await fetch('https://192.168.100.105:8004/api/mobile/v1/device/register', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${authToken}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          token: token,
          device_type: Platform.OS === 'ios' ? 'ios' : 'android',
          device_name: `${Platform.OS} Device`,
          app_version: '1.0.0',
        }),
      });
    }
  } catch (error) {
    console.error('Error registering device token:', error);
  }
}
```

## API Endpoints

### Register Device Token

**POST** `/api/mobile/v1/device/register`

**Request:**
```json
{
  "token": "fcm_token_here",
  "device_type": "android",
  "device_id": "device_unique_id",
  "device_name": "Samsung Galaxy S21",
  "app_version": "1.0.0",
  "os_version": "Android 12"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Device token registered successfully",
  "data": {
    "id": 1,
    "device_type": "android",
    "device_name": "Samsung Galaxy S21"
  }
}
```

### Unregister Device Token

**DELETE** `/api/mobile/v1/device/unregister`

**Request:**
```json
{
  "token": "fcm_token_here"
}
```

### Get User's Device Tokens

**GET** `/api/mobile/v1/device/tokens`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "device_type": "android",
      "device_name": "Samsung Galaxy S21",
      "app_version": "1.0.0",
      "os_version": "Android 12",
      "last_used_at": "2024-01-15T10:00:00Z",
      "created_at": "2024-01-15T09:00:00Z"
    }
  ]
}
```

## How It Works

1. **User logs in** to mobile app
2. **App gets FCM token** from Firebase
3. **App registers token** with OfisiLink API
4. **Token stored** in `device_tokens` table
5. **When notification sent**, system:
   - Gets all active device tokens for user(s)
   - Sends push notification via FCM API
   - Updates token usage timestamp
   - Deactivates invalid tokens automatically

## Notification Payload

When a notification is sent, the FCM payload includes:

```json
{
  "notification": {
    "title": "OfisiLink",
    "body": "Your leave request has been approved",
    "sound": "default",
    "badge": 5
  },
  "data": {
    "message": "Your leave request has been approved",
    "link": "/modules/leave/123",
    "type": "leave_approval",
    "timestamp": "2024-01-15T10:00:00Z"
  }
}
```

## Testing

### Test Push Notification

You can test push notifications using:

1. **Firebase Console:**
   - Go to Cloud Messaging
   - Click "Send test message"
   - Enter FCM token
   - Send test notification

2. **cURL:**
```bash
curl -X POST https://fcm.googleapis.com/fcm/send \
  -H "Authorization: key=YOUR_SERVER_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "FCM_TOKEN_HERE",
    "notification": {
      "title": "Test",
      "body": "This is a test notification"
    }
  }'
```

## Troubleshooting

### Token Not Registered

- Check if FCM is properly initialized in app
- Verify API endpoint is correct
- Check authentication token is valid

### Notifications Not Received

1. **Check FCM Server Key:**
   - Verify key is correct in system settings
   - Ensure key has Cloud Messaging API enabled

2. **Check Device Token:**
   - Verify token is active in database
   - Check if token was deactivated (invalid tokens are auto-deactivated)

3. **Check App Permissions:**
   - Ensure notification permissions are granted
   - Check if app is in background/foreground

4. **Check Logs:**
   - Review Laravel logs for FCM errors
   - Check Firebase Console for delivery reports

### Invalid Token Errors

FCM automatically deactivates tokens that return:
- `InvalidRegistration` - Token is invalid
- `NotRegistered` - Token is not registered
- `MismatchSenderId` - Wrong sender ID

These tokens are automatically deactivated in the system.

## Best Practices

1. **Register token after login** - Ensure user is authenticated
2. **Refresh token on app start** - Handle token refresh
3. **Unregister on logout** - Remove token when user logs out
4. **Handle token refresh** - Listen for FCM token refresh events
5. **Limit tokens per user** - System limits to 5 tokens per user (oldest deactivated)

## Security Notes

1. **Never expose FCM Server Key** in mobile app
2. **Use HTTPS** for all API calls
3. **Validate tokens** server-side
4. **Deactivate old tokens** automatically
5. **Rate limit** token registration endpoints

## Support

For issues:
- Check Firebase Console for delivery reports
- Review Laravel logs: `storage/logs/laravel.log`
- Verify FCM Server Key is correct
- Test with Firebase Console test message

---

**Last Updated:** January 2024  
**Version:** 1.0.0




