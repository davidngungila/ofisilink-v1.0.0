<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class SendLoginNotification
{
    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        try {
            $user = $event->user;
            
            // Get login details
            $ipAddress = request()->ip();
            $userAgent = request()->userAgent();
            $loginTime = now()->format('Y-m-d H:i:s');
            
            // Build login notification message (simplified for toast notification)
            $message = "You have successfully logged into your OfisiLink account.";
            
            // Send only in-app notification (toast) - skip SMS and Email to avoid slow redirect
            $this->notificationService->notify(
                $user->id,
                $message,
                route('dashboard'), // Link to dashboard
                'Login Notification - OfisiLink',
                [
                    'type' => 'login',
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'login_time' => $loginTime,
                    'skip_sms' => true,  // Skip SMS to avoid slow redirect
                    'skip_email' => true // Skip Email to avoid slow redirect
                ]
            );
            
            Log::info('Login notification sent (in-app only)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip_address' => $ipAddress,
                'sms_skipped' => true,
                'email_skipped' => true
            ]);
        } catch (\Exception $e) {
            // Don't fail login if notification fails
            Log::warning('Failed to send login notification', [
                'user_id' => $event->user->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }
}




