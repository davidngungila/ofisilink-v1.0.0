<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class ActivityLogService
{
    /**
     * Log an activity with comprehensive details
     */
    public static function log(
        string $action,
        string $description,
        $model = null,
        array $properties = [],
        ?int $userId = null,
        ?Request $request = null
    ) {
        try {
            $userId = $userId ?? Auth::id();
            $request = $request ?? request();
            
            // Build comprehensive properties
            $metadata = array_merge($properties, [
                'timestamp' => now()->toDateTimeString(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'route' => $request->route() ? $request->route()->getName() : null,
            ]);
            
            // Add request data (excluding sensitive fields)
            $requestData = $request->except(['password', 'password_confirmation', '_token', 'api_token']);
            if (!empty($requestData)) {
                $metadata['request_data'] = $requestData;
            }
            
            // Add old and new values if provided
            if (isset($properties['old_values']) || isset($properties['new_values'])) {
                $metadata['changes'] = [
                    'old' => $properties['old_values'] ?? [],
                    'new' => $properties['new_values'] ?? []
                ];
            }
            
            return ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model ? $model->id : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'properties' => $metadata,
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the operation
            Log::error('Failed to create activity log', [
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Log model creation
     */
    public static function logCreated($model, string $description = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = $description ?? "Created {$modelName} #{$model->id}";
        
        return self::log('created', $description, $model, array_merge([
            'model_data' => $model->toArray(),
        ], $additionalData));
    }
    
    /**
     * Log model update with old and new values
     */
    public static function logUpdated($model, array $oldValues = [], array $newValues = [], string $description = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = $description ?? "Updated {$modelName} #{$model->id}";
        
        // Get changed attributes if old/new values not provided
        if (empty($oldValues) && empty($newValues) && method_exists($model, 'getChanges')) {
            $changes = $model->getChanges();
            $original = $model->getOriginal();
            foreach ($changes as $key => $value) {
                $newValues[$key] = $value;
                $oldValues[$key] = $original[$key] ?? null;
            }
        }
        
        return self::log('updated', $description, $model, array_merge([
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'changed_fields' => array_keys($newValues),
        ], $additionalData));
    }
    
    /**
     * Log model deletion
     */
    public static function logDeleted($model, string $description = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $modelId = $model->id ?? 'unknown';
        $description = $description ?? "Deleted {$modelName} #{$modelId}";
        
        return self::log('deleted', $description, $model, array_merge([
            'deleted_data' => $model->toArray(),
        ], $additionalData));
    }
    
    /**
     * Log user login
     */
    public static function logLogin($user, array $additionalData = [])
    {
        return self::log('login', "User {$user->name} ({$user->email}) logged in", $user, array_merge([
            'login_time' => now()->toDateTimeString(),
        ], $additionalData));
    }
    
    /**
     * Log user logout
     */
    public static function logLogout($user, array $additionalData = [])
    {
        return self::log('logout', "User {$user->name} ({$user->email}) logged out", $user, $additionalData);
    }
    
    /**
     * Log password reset
     */
    public static function logPasswordReset($user, $adminUser = null, array $additionalData = [])
    {
        $adminName = $adminUser ? $adminUser->name : 'System';
        return self::log('password_reset', "Password reset for user {$user->name} ({$user->email}) by {$adminName}", $user, array_merge([
            'reset_by' => $adminUser ? $adminUser->id : null,
            'reset_by_name' => $adminName,
        ], $additionalData));
    }
    
    /**
     * Log role change
     */
    public static function logRoleChange($user, array $oldRoles = [], array $newRoles = [], $adminUser = null, array $additionalData = [])
    {
        $adminName = $adminUser ? $adminUser->name : 'System';
        return self::log('role_changed', "Roles changed for user {$user->name} ({$user->email}) by {$adminName}", $user, array_merge([
            'old_roles' => $oldRoles,
            'new_roles' => $newRoles,
            'changed_by' => $adminUser ? $adminUser->id : null,
            'changed_by_name' => $adminName,
        ], $additionalData));
    }
    
    /**
     * Log custom action
     */
    public static function logAction(string $action, string $description, $model = null, array $properties = [])
    {
        return self::log($action, $description, $model, $properties);
    }

    /**
     * Log approval action
     */
    public static function logApproved($model, string $description = null, string $approvedBy = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = $description ?? "Approved {$modelName} #{$model->id}" . ($approvedBy ? " by {$approvedBy}" : "");
        
        return self::log('approved', $description, $model, array_merge([
            'approved_by' => $approvedBy,
            'approved_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log rejection action
     */
    public static function logRejected($model, string $description = null, string $rejectedBy = null, string $reason = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = $description ?? "Rejected {$modelName} #{$model->id}" . ($rejectedBy ? " by {$rejectedBy}" : "");
        
        return self::log('rejected', $description, $model, array_merge([
            'rejected_by' => $rejectedBy,
            'rejection_reason' => $reason,
            'rejected_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log cancellation action
     */
    public static function logCancelled($model, string $description = null, string $cancelledBy = null, string $reason = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = $description ?? "Cancelled {$modelName} #{$model->id}" . ($cancelledBy ? " by {$cancelledBy}" : "");
        
        return self::log('cancelled', $description, $model, array_merge([
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
            'cancelled_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log SMS sent
     */
    public static function logSMSSent(string $phoneNumber, string $message, ?int $userId = null, ?int $recipientUserId = null, array $additionalData = [])
    {
        $description = "SMS sent to {$phoneNumber}";
        if ($recipientUserId) {
            $description .= " (User ID: {$recipientUserId})";
        }
        
        return self::log('sms_sent', $description, null, array_merge([
            'phone_number' => $phoneNumber,
            'message_preview' => substr($message, 0, 100),
            'message_length' => strlen($message),
            'recipient_user_id' => $recipientUserId,
            'sent_at' => now()->toDateTimeString(),
        ], $additionalData), $userId);
    }

    /**
     * Log notification sent
     */
    public static function logNotificationSent($recipientUserIds, string $message, ?string $link = null, ?int $userId = null, array $additionalData = [])
    {
        $recipientIds = is_array($recipientUserIds) ? $recipientUserIds : [$recipientUserIds];
        $description = "Notification sent to " . count($recipientIds) . " user(s)";
        
        return self::log('notification_sent', $description, null, array_merge([
            'recipient_user_ids' => $recipientIds,
            'recipient_count' => count($recipientIds),
            'message_preview' => substr($message, 0, 100),
            'link' => $link,
            'sent_at' => now()->toDateTimeString(),
        ], $additionalData), $userId);
    }

    /**
     * Log email sent
     */
    public static function logEmailSent(string $email, string $subject, ?int $userId = null, ?int $recipientUserId = null, array $additionalData = [])
    {
        $description = "Email sent to {$email}";
        if ($recipientUserId) {
            $description .= " (User ID: {$recipientUserId})";
        }
        
        return self::log('email_sent', $description, null, array_merge([
            'email' => $email,
            'subject' => $subject,
            'recipient_user_id' => $recipientUserId,
            'sent_at' => now()->toDateTimeString(),
        ], $additionalData), $userId);
    }

    /**
     * Log status change
     */
    public static function logStatusChanged($model, string $oldStatus, string $newStatus, string $description = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = $description ?? "Status changed for {$modelName} #{$model->id} from {$oldStatus} to {$newStatus}";
        
        return self::log('status_changed', $description, $model, array_merge([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log bulk operation
     */
    public static function logBulkOperation(string $action, int $count, string $description = null, array $additionalData = [])
    {
        $description = $description ?? "Bulk {$action} operation performed on {$count} item(s)";
        
        return self::log('bulk_' . $action, $description, null, array_merge([
            'item_count' => $count,
            'operation_type' => $action,
            'performed_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log file upload
     */
    public static function logFileUpload($model, string $fileName, string $fileType = null, int $fileSize = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = "File uploaded: {$fileName} for {$modelName} #{$model->id}";
        
        return self::log('file_uploaded', $description, $model, array_merge([
            'file_name' => $fileName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'uploaded_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log file download
     */
    public static function logFileDownload($model, string $fileName, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = "File downloaded: {$fileName} from {$modelName} #{$model->id}";
        
        return self::log('file_downloaded', $description, $model, array_merge([
            'file_name' => $fileName,
            'downloaded_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log file deletion
     */
    public static function logFileDeleted($model, string $fileName, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = "File deleted: {$fileName} from {$modelName} #{$model->id}";
        
        return self::log('file_deleted', $description, $model, array_merge([
            'file_name' => $fileName,
            'deleted_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log payment processed
     */
    public static function logPaymentProcessed($model, float $amount, string $paymentMethod = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = "Payment processed for {$modelName} #{$model->id}: " . number_format($amount, 2);
        
        return self::log('payment_processed', $description, $model, array_merge([
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'processed_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log export operation
     */
    public static function logExport(string $exportType, int $recordCount = null, array $additionalData = [])
    {
        $description = "Data exported: {$exportType}";
        if ($recordCount !== null) {
            $description .= " ({$recordCount} records)";
        }
        
        return self::log('exported', $description, null, array_merge([
            'export_type' => $exportType,
            'record_count' => $recordCount,
            'exported_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log import operation
     */
    public static function logImport(string $importType, int $recordCount = null, int $successCount = null, int $failureCount = null, array $additionalData = [])
    {
        $description = "Data imported: {$importType}";
        if ($recordCount !== null) {
            $description .= " ({$successCount} successful, {$failureCount} failed)";
        }
        
        return self::log('imported', $description, null, array_merge([
            'import_type' => $importType,
            'record_count' => $recordCount,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'imported_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log system configuration change
     */
    public static function logConfigChanged(string $configKey, $oldValue = null, $newValue = null, array $additionalData = [])
    {
        $description = "System configuration changed: {$configKey}";
        
        return self::log('config_changed', $description, null, array_merge([
            'config_key' => $configKey,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log comment added
     */
    public static function logCommentAdded($model, string $comment, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = "Comment added to {$modelName} #{$model->id}";
        
        return self::log('comment_added', $description, $model, array_merge([
            'comment_preview' => substr($comment, 0, 100),
            'added_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log assignment
     */
    public static function logAssigned($model, int $assignedToUserId, string $assignedToName = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = "Assigned {$modelName} #{$model->id} to " . ($assignedToName ?? "User #{$assignedToUserId}");
        
        return self::log('assigned', $description, $model, array_merge([
            'assigned_to_user_id' => $assignedToUserId,
            'assigned_to_name' => $assignedToName,
            'assigned_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log unassignment
     */
    public static function logUnassigned($model, int $unassignedFromUserId, string $unassignedFromName = null, array $additionalData = [])
    {
        $modelName = class_basename($model);
        $description = "Unassigned {$modelName} #{$model->id} from " . ($unassignedFromName ?? "User #{$unassignedFromUserId}");
        
        return self::log('unassigned', $description, $model, array_merge([
            'unassigned_from_user_id' => $unassignedFromUserId,
            'unassigned_from_name' => $unassignedFromName,
            'unassigned_at' => now()->toDateTimeString(),
        ], $additionalData));
    }

    /**
     * Log database table operation (for DB::table() operations)
     */
    public static function logTableOperation(string $action, string $tableName, array $affectedIds = [], string $description = null, array $additionalData = [])
    {
        $description = $description ?? "{$action} operation on {$tableName} table" . (!empty($affectedIds) ? " (IDs: " . implode(', ', array_slice($affectedIds, 0, 10)) . (count($affectedIds) > 10 ? '...' : '') . ")" : "");
        
        return self::log($action, $description, null, array_merge([
            'table_name' => $tableName,
            'affected_ids' => $affectedIds,
            'affected_count' => count($affectedIds),
            'operation_type' => 'table_operation',
        ], $additionalData));
    }

    /**
     * Log bulk update operation
     */
    public static function logBulkUpdate(string $tableName, int $affectedCount, array $oldValues = [], array $newValues = [], string $description = null, array $additionalData = [])
    {
        $description = $description ?? "Bulk updated {$affectedCount} record(s) in {$tableName}";
        
        return self::log('bulk_updated', $description, null, array_merge([
            'table_name' => $tableName,
            'affected_count' => $affectedCount,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ], $additionalData));
    }

    /**
     * Log bulk delete operation
     */
    public static function logBulkDelete(string $tableName, int $affectedCount, array $deletedIds = [], string $description = null, array $additionalData = [])
    {
        $description = $description ?? "Bulk deleted {$affectedCount} record(s) from {$tableName}";
        
        return self::log('bulk_deleted', $description, null, array_merge([
            'table_name' => $tableName,
            'affected_count' => $affectedCount,
            'deleted_ids' => $deletedIds,
        ], $additionalData));
    }
}






