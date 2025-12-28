# Activity Logging System - Complete Guide

## Overview

The activity logging system has been enhanced to automatically log **ALL** create, update, and delete operations across the entire system. This ensures comprehensive audit trails for compliance and debugging.

## How It Works

### 1. **Automatic Logging (Global Observer)**

A global `ModelActivityObserver` has been registered that automatically logs:
- ✅ **Model Creation** - When any model is created
- ✅ **Model Updates** - When any model is updated (excluding timestamp-only changes)
- ✅ **Model Deletions** - When any model is deleted

**Location:** `app/Observers/ModelActivityObserver.php`  
**Registered in:** `app/Providers/AppServiceProvider.php`

### 2. **What Gets Logged Automatically**

All Eloquent model operations are automatically logged:
```php
// These are automatically logged:
$user = User::create([...]);           // ✅ Logged
$user->update([...]);                  // ✅ Logged
$user->delete();                       // ✅ Logged

$leaveRequest = LeaveRequest::find(1);
$leaveRequest->status = 'approved';
$leaveRequest->save();                 // ✅ Logged
```

### 3. **What Doesn't Get Logged Automatically**

Operations that bypass Eloquent events:
- ❌ `DB::table('users')->update([...])` - Use manual logging (see below)
- ❌ `DB::table('users')->delete()` - Use manual logging (see below)
- ❌ Bulk operations using `Model::query()->update([...])` - Use manual logging (see below)

## Manual Logging for Special Cases

### For DB::table() Operations

When using `DB::table()` directly, you need to manually log:

```php
use App\Services\ActivityLogService;

// Example: Bulk update using DB::table()
$affected = DB::table('users')
    ->where('status', 'inactive')
    ->update(['status' => 'active']);

// Log the operation
ActivityLogService::logTableOperation(
    'updated',
    'users',
    [], // IDs if available
    "Bulk updated {$affected} users to active status",
    ['old_status' => 'inactive', 'new_status' => 'active']
);
```

### For Bulk Delete Operations

```php
$deletedIds = [1, 2, 3, 4, 5];
DB::table('notifications')
    ->whereIn('id', $deletedIds)
    ->delete();

ActivityLogService::logBulkDelete(
    'notifications',
    count($deletedIds),
    $deletedIds,
    "Bulk deleted " . count($deletedIds) . " notifications"
);
```

### For Bulk Update Operations

```php
$affectedCount = User::where('department_id', 5)
    ->update(['department_id' => 1]);

ActivityLogService::logBulkUpdate(
    'users',
    $affectedCount,
    ['department_id' => 5],
    ['department_id' => 1],
    "Moved {$affectedCount} users to new department"
);
```

## Disabling Logging for Specific Operations

If you need to temporarily disable logging for a specific model operation:

```php
$model = User::find(1);
$model->disableActivityLogging = true; // Disable logging
$model->update([...]); // This won't be logged
```

## Available Logging Methods

### Basic Operations
- `ActivityLogService::logCreated($model, $description, $additionalData)`
- `ActivityLogService::logUpdated($model, $oldValues, $newValues, $description, $additionalData)`
- `ActivityLogService::logDeleted($model, $description, $additionalData)`

### Approval Actions
- `ActivityLogService::logApproved($model, $description, $approvedBy, $additionalData)`
- `ActivityLogService::logRejected($model, $description, $rejectedBy, $reason, $additionalData)`
- `ActivityLogService::logCancelled($model, $description, $cancelledBy, $reason, $additionalData)`

### Notifications
- `ActivityLogService::logSMSSent($phoneNumber, $message, $userId, $recipientUserId, $additionalData)`
- `ActivityLogService::logNotificationSent($recipientUserIds, $message, $link, $userId, $additionalData)`
- `ActivityLogService::logEmailSent($email, $subject, $userId, $recipientUserId, $additionalData)`

### Bulk Operations
- `ActivityLogService::logBulkUpdate($tableName, $affectedCount, $oldValues, $newValues, $description, $additionalData)`
- `ActivityLogService::logBulkDelete($tableName, $affectedCount, $deletedIds, $description, $additionalData)`
- `ActivityLogService::logTableOperation($action, $tableName, $affectedIds, $description, $additionalData)`

## Common Issues and Solutions

### Issue: Some updates/deletes are not logged

**Solution 1:** Check if you're using `DB::table()` - use manual logging
```php
// ❌ Not logged automatically
DB::table('users')->where('id', 1)->update([...]);

// ✅ Use manual logging
DB::table('users')->where('id', 1)->update([...]);
ActivityLogService::logTableOperation('updated', 'users', [1], "Updated user #1");
```

**Solution 2:** Check if model has `disableActivityLogging` set
```php
// Make sure this is not set
unset($model->disableActivityLogging);
```

**Solution 3:** Ensure user is authenticated
```php
// Logging only works when user is authenticated
if (Auth::check()) {
    // Logging will work
}
```

### Issue: Too many logs for timestamp updates

**Solution:** The observer automatically filters out timestamp-only updates. Only significant field changes are logged.

### Issue: Logging fails and breaks the operation

**Solution:** All logging is wrapped in try-catch blocks. If logging fails, it won't break your operation - it will just log a warning.

## Best Practices

1. **Always use Eloquent models** when possible - automatic logging works best
2. **Manually log DB::table() operations** - they bypass Eloquent events
3. **Use descriptive descriptions** - helps with audit trails
4. **Include relevant metadata** - old/new values, reasons, etc.
5. **Don't disable logging unnecessarily** - only for special cases

## Viewing Activity Logs

Access the activity log interface at: `/admin/activity-log`

Features:
- Filter by user, action type, model, date range
- View detailed change history
- Export to CSV
- Real-time statistics

## Troubleshooting

If logs are still missing:

1. Check `app/Providers/AppServiceProvider.php` - ensure observer is registered
2. Check `app/Observers/ModelActivityObserver.php` - ensure it's working
3. Check Laravel logs for any errors: `storage/logs/laravel.log`
4. Verify user is authenticated when operation occurs
5. Check if model class exists and is using Eloquent

## Summary

✅ **Automatic logging** is now enabled for ALL Eloquent model operations  
✅ **Manual logging** available for DB::table() and bulk operations  
✅ **Comprehensive coverage** of all system actions  
✅ **Error handling** ensures operations never fail due to logging issues

The system is now fully auditable with complete activity trails!



