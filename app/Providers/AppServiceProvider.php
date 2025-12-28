<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;
use App\Listeners\SendLoginNotification;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force Tanzania timezone globally
        $tanzaniaTimezone = 'Africa/Dar_es_Salaam';
        date_default_timezone_set($tanzaniaTimezone);
        config(['app.timezone' => $tanzaniaTimezone]);
        
        // Set Carbon locale
        Carbon::setLocale('en');
        
        // Override Carbon's now() to always use Tanzania timezone
        // This ensures all Carbon::now() calls use Tanzania timezone
        if (!Carbon::hasTestNow()) {
            Carbon::macro('nowTZ', function() use ($tanzaniaTimezone) {
                return Carbon::now($tanzaniaTimezone);
            });
        }
        
        // Ensure OrganizationSetting defaults to Tanzania timezone
        try {
            $orgSettings = \App\Models\OrganizationSetting::getSettings();
            if (empty($orgSettings->timezone) || $orgSettings->timezone === 'UTC') {
                $orgSettings->timezone = $tanzaniaTimezone;
                $orgSettings->save();
            }
        } catch (\Exception $e) {
            // Ignore if database is not ready yet
        }
        
        Blade::if('role', function ($roles) {
            $user = auth()->user();
            if (!$user) return false;
            if (!is_array($roles)) $roles = [$roles];
            if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) return true;
            return $user->hasAnyRole($roles);
        });
        
        // Set default stream context for SSL to disable verification globally
        // This is needed for SMTP servers with self-signed certificates
        stream_context_set_default([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ],
        ]);

        // Register global model events for activity logging using Model's static events
        // This will automatically log all create, update, and delete operations
        // for all Eloquent models (except ActivityLog itself to avoid loops)
        
        Model::created(function ($model) {
            if (get_class($model) !== \App\Models\ActivityLog::class && Auth::check()) {
                if (!isset($model->disableActivityLogging) || !$model->disableActivityLogging) {
                    try {
                        ActivityLogService::logCreated($model);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to log model creation', [
                            'model' => get_class($model),
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        });

        Model::updated(function ($model) {
            if (get_class($model) !== \App\Models\ActivityLog::class && Auth::check()) {
                if (!isset($model->disableActivityLogging) || !$model->disableActivityLogging) {
                    try {
                        $changes = $model->getChanges();
                        $significantChanges = array_filter($changes, function($key) {
                            return !in_array($key, ['updated_at', 'created_at']);
                        }, ARRAY_FILTER_USE_KEY);
                        
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
                        \Log::warning('Failed to log model update', [
                            'model' => get_class($model),
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        });

        Model::deleted(function ($model) {
            if (get_class($model) !== \App\Models\ActivityLog::class && Auth::check()) {
                if (!isset($model->disableActivityLogging) || !$model->disableActivityLogging) {
                    try {
                        ActivityLogService::logDeleted($model);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to log model deletion', [
                            'model' => get_class($model),
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
        });

        // Register login event listener to send email notifications
        Event::listen(Login::class, SendLoginNotification::class);
    }
}
