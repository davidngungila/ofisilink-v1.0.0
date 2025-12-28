<?php

namespace App\Traits;

use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Boot the trait
     */
    public static function bootLogsActivity()
    {
        // Log when model is created
        static::created(function ($model) {
            if (Auth::check()) {
                ActivityLogService::logCreated($model);
            }
        });

        // Log when model is updated
        static::updated(function ($model) {
            if (Auth::check()) {
                $oldValues = [];
                $newValues = [];
                
                foreach ($model->getChanges() as $key => $value) {
                    $newValues[$key] = $value;
                    $oldValues[$key] = $model->getOriginal($key);
                }
                
                ActivityLogService::logUpdated($model, $oldValues, $newValues);
            }
        });

        // Log when model is deleted
        static::deleted(function ($model) {
            if (Auth::check()) {
                ActivityLogService::logDeleted($model);
            }
        });
    }

    /**
     * Log a custom activity for this model
     */
    public function logActivity(string $action, string $description, array $properties = [])
    {
        return ActivityLogService::logAction($action, $description, $this, $properties);
    }

    /**
     * Log approval
     */
    public function logApproval(string $description = null, string $approvedBy = null, array $additionalData = [])
    {
        return ActivityLogService::logApproved($this, $description, $approvedBy, $additionalData);
    }

    /**
     * Log rejection
     */
    public function logRejection(string $description = null, string $rejectedBy = null, string $reason = null, array $additionalData = [])
    {
        return ActivityLogService::logRejected($this, $description, $rejectedBy, $reason, $additionalData);
    }

    /**
     * Log cancellation
     */
    public function logCancellation(string $description = null, string $cancelledBy = null, string $reason = null, array $additionalData = [])
    {
        return ActivityLogService::logCancelled($this, $description, $cancelledBy, $reason, $additionalData);
    }

    /**
     * Log status change
     */
    public function logStatusChange(string $oldStatus, string $newStatus, string $description = null, array $additionalData = [])
    {
        return ActivityLogService::logStatusChanged($this, $oldStatus, $newStatus, $description, $additionalData);
    }
}



