<?php

namespace App\Observers;

use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class ModelActivityObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        // Skip logging for ActivityLog itself to avoid infinite loops
        if (get_class($model) === \App\Models\ActivityLog::class) {
            return;
        }

        // Skip if model has a property to disable logging
        if (isset($model->disableActivityLogging) && $model->disableActivityLogging === true) {
            return;
        }

        // Only log if user is authenticated
        if (Auth::check()) {
            try {
                ActivityLogService::logCreated($model);
            } catch (\Exception $e) {
                // Don't fail the operation if logging fails
                \Log::warning('Failed to log model creation', [
                    'model' => get_class($model),
                    'model_id' => $model->id ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        // Skip logging for ActivityLog itself
        if (get_class($model) === \App\Models\ActivityLog::class) {
            return;
        }

        // Skip if model has a property to disable logging
        if (isset($model->disableActivityLogging) && $model->disableActivityLogging === true) {
            return;
        }

        // Only log if user is authenticated
        if (Auth::check()) {
            try {
                $changes = $model->getChanges();
                
                // Filter out timestamp-only changes
                $significantChanges = array_filter($changes, function($key) {
                    return !in_array($key, ['updated_at', 'created_at']);
                }, ARRAY_FILTER_USE_KEY);
                
                // Only log if there are significant changes
                if (!empty($significantChanges)) {
                    $oldValues = [];
                    $newValues = [];
                    
                    foreach ($significantChanges as $key => $value) {
                        $newValues[$key] = $value;
                        $oldValues[$key] = $model->getOriginal($key);
                    }
                    
                    ActivityLogService::logUpdated($model, $oldValues, $newValues);
                }
            } catch (\Exception $e) {
                // Don't fail the operation if logging fails
                \Log::warning('Failed to log model update', [
                    'model' => get_class($model),
                    'model_id' => $model->id ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        // Skip logging for ActivityLog itself
        if (get_class($model) === \App\Models\ActivityLog::class) {
            return;
        }

        // Skip if model has a property to disable logging
        if (isset($model->disableActivityLogging) && $model->disableActivityLogging === true) {
            return;
        }

        // Only log if user is authenticated
        if (Auth::check()) {
            try {
                ActivityLogService::logDeleted($model);
            } catch (\Exception $e) {
                // Don't fail the operation if logging fails
                \Log::warning('Failed to log model deletion', [
                    'model' => get_class($model),
                    'model_id' => $model->id ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        if (Auth::check()) {
            try {
                ActivityLogService::logAction('restored', "Restored " . class_basename($model) . " #{$model->id}", $model);
            } catch (\Exception $e) {
                \Log::warning('Failed to log model restoration', [
                    'model' => get_class($model),
                    'model_id' => $model->id ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}

