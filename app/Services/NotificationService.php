<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\SystemSetting;
use App\Models\NotificationProvider;
use App\Models\DeviceToken;
use App\Services\ActivityLogService;
use App\Services\EmailService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Mail\Message;

class NotificationService
{
    protected $smsUsername;
    protected $smsPassword;
    protected $smsFrom;
    protected $smsUrl;
    protected $smsProvider;
    protected $emailProvider;

    public function __construct()
    {
        try {
            // Get primary providers from database
            $this->smsProvider = NotificationProvider::getPrimary('sms');
            $this->emailProvider = NotificationProvider::getPrimary('email');
            
            // Fallback to SystemSetting if no provider found
            if ($this->smsProvider) {
                $this->smsUsername = $this->smsProvider->sms_username;
                $this->smsPassword = $this->smsProvider->sms_password;
                $this->smsFrom = $this->smsProvider->sms_from;
                $this->smsUrl = $this->smsProvider->sms_url;
            } else {
                // Fallback to SystemSetting, then env
                $this->smsUsername = SystemSetting::getValue('sms_username') ?: env('SMS_USERNAME', 'emcatechn');
                $this->smsPassword = SystemSetting::getValue('sms_password') ?: env('SMS_PASSWORD', 'Emca@#12');
                $this->smsFrom = SystemSetting::getValue('sms_from') ?: env('SMS_FROM', 'OfisiLink');
                $this->smsUrl = SystemSetting::getValue('sms_url') ?: env('SMS_URL', 'https://messaging-service.co.tz/link/sms/v1/text/single');
            }
        } catch (\Exception $e) {
            // Table might not exist yet, use fallback
            Log::warning('NotificationProvider table not available, using SystemSetting fallback: ' . $e->getMessage());
            $this->smsUsername = SystemSetting::getValue('sms_username') ?: env('SMS_USERNAME', 'emcatechn');
            $this->smsPassword = SystemSetting::getValue('sms_password') ?: env('SMS_PASSWORD', 'Emca@#12');
            $this->smsFrom = SystemSetting::getValue('sms_from') ?: env('SMS_FROM', 'OfisiLink');
            $this->smsUrl = SystemSetting::getValue('sms_url') ?: env('SMS_URL', 'https://messaging-service.co.tz/link/sms/v1/text/single');
        }
    }

    /**
     * Send notification to user(s) via all channels
     * 
     * @param array|int $userIds User ID(s) to notify
     * @param string $message Message to send
     * @param string|null $link Optional link for in-app notification
     * @param string|null $subject Optional email subject
     * @param array $data Additional data for email template
     */
    public function notify($userIds, string $message, ?string $link = null, ?string $subject = null, array $data = [])
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        // Check if SMS or Email should be skipped (from $data array)
        $skipSMS = isset($data['skip_sms']) && $data['skip_sms'] === true;
        $skipEmail = isset($data['skip_email']) && $data['skip_email'] === true;

        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            // Enhance data with user information for email template
            $enhancedData = array_merge($data, [
                'recipient_name' => $user->name,
                'recipient_email' => $user->email,
                'recipient_id' => $user->id,
            ]);

            // Add employee information if available
            if ($user->employee) {
                $enhancedData['employee_id'] = $user->employee->employee_id ?? null;
                $enhancedData['department'] = $user->employee->department->name ?? null;
            }

            // 1. In-App Notification
            $this->sendInAppNotification($user->id, $message, $link);

            // 2. Push Notification (FCM) - send to all active device tokens
            if (!isset($data['skip_push']) || $data['skip_push'] !== true) {
                $this->sendPushNotification($user->id, $message, $link, $data);
            }

            // 3. SMS Notification - check both mobile and phone fields (skip if requested)
            if (!$skipSMS) {
                $phone = $user->mobile ?? $user->phone;
                if ($phone) {
                    try {
                        $smsResult = $this->sendSMS($phone, $message);
                        if ($smsResult) {
                            // Log SMS sent activity
                            ActivityLogService::logSMSSent($phone, $message, Auth::id(), $user->id, [
                                'notification_type' => 'multi_channel',
                                'link' => $link,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('SMS sending failed in notify method', [
                            'user_id' => $user->id,
                            'phone' => $phone,
                            'error' => $e->getMessage()
                        ]);
                        // Continue with other notifications even if SMS fails
                    }
                }
            }

            // 4. Email Notification - Always send email unless explicitly skipped
            if (!$skipEmail && $user->email) {
                try {
                    $emailSubject = $subject ?? 'OfisiLink Notification';
                    
                    // Add link to data for email template
                    $enhancedData['link'] = $link;
                    
                    $emailResult = $this->sendEmail($user->email, $emailSubject, $message, $enhancedData);
                    if ($emailResult) {
                        // Log email sent activity
                        ActivityLogService::logEmailSent($user->email, $emailSubject, Auth::id(), $user->id, [
                            'notification_type' => 'multi_channel',
                            'link' => $link,
                        ]);
                    } else {
                        Log::warning('Email sending failed in notify method', [
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'subject' => $emailSubject
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Email sending exception in notify method', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                    // Continue with other notifications even if email fails
                }
            }
        }

        // Log notification sent activity
        ActivityLogService::logNotificationSent($userIds, $message, $link, Auth::id(), [
            'subject' => $subject,
            'notification_count' => count($users),
        ]);

        // Broadcast for real-time toast notifications (if using Laravel Echo/WebSockets)
        $this->broadcastNotification($userIds, $message, $link);
    }

    /**
     * Send in-app notification
     */
    protected function sendInAppNotification(int $userId, string $message, ?string $link = null)
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'message' => $message,
                'link' => $link,
                'is_read' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create in-app notification: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS using GET method with URL parameters - as per provided example
     */
    public function sendSMS(string $phoneNumber, string $message, ?NotificationProvider $provider = null)
    {
        try {
            // Use provided provider or fallback to default
            $provider = $provider ?? $this->smsProvider;
            
            if ($provider) {
                $smsUsername = $provider->sms_username;
                $smsPassword = $provider->sms_password;
                $smsFrom = $provider->sms_from;
                $smsUrl = $provider->sms_url;
            } else {
                $smsUsername = $this->smsUsername;
                $smsPassword = $this->smsPassword;
                $smsFrom = $this->smsFrom;
                $smsUrl = $this->smsUrl;
            }
            // Validate phone number format
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            if (empty($phoneNumber) || !preg_match('/^255[0-9]{9}$/', $phoneNumber)) {
                // Try to fix format if not already in correct format
                if (!str_starts_with($phoneNumber, '255')) {
                    $phoneNumber = '255' . ltrim($phoneNumber, '0');
                }
                
                // Validate again after formatting
                if (!preg_match('/^255[0-9]{9}$/', $phoneNumber)) {
                    Log::error('SMS sending failed: Invalid phone number format', [
                        'phone' => $phoneNumber,
                        'expected_format' => '255XXXXXXXXX'
                    ]);
                    return false;
                }
            }

            // Debug log
            Log::info('Attempting to send SMS', [
                'phone' => $phoneNumber,
                'message' => substr($message, 0, 50) . (strlen($message) > 50 ? '...' : ''),
                'url' => $smsUrl,
                'from' => $smsFrom
            ]);

            // Check if URL contains '/api/sms/v1/test/text/single' - use POST with JSON
            $usePostMethod = strpos($smsUrl, '/api/sms/v1') !== false || strpos($smsUrl, '/api/') !== false;
            
            $curl = curl_init();
            
            if ($usePostMethod) {
                // Use POST method with JSON body and Basic Auth (as per test_sms_direct.php)
                $auth = base64_encode($smsUsername . ':' . $smsPassword);
                
                $body = json_encode([
                    'from' => $smsFrom,
                    'to' => $phoneNumber,
                    'text' => $message,
                    'reference' => 'ofisilink_' . time()
                ]);
                
                Log::debug('SMS API Request (POST)', [
                    'url' => $smsUrl,
                    'method' => 'POST',
                    'from' => $smsFrom,
                    'to' => $phoneNumber,
                    'body' => $body
                ]);
                
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $smsUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $body,
                    CURLOPT_HTTPHEADER => [
                        'Authorization: Basic ' . $auth,
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_USERAGENT => 'OfisiLink-SMS-Client/1.0'
                ));
            } else {
                // Use GET method with URL parameters (legacy support)
                $text = urlencode($message);
                $password = urlencode($smsPassword);
                
                $url = $smsUrl . 
                       '?username=' . urlencode($smsUsername) . 
                       '&password=' . $password . 
                       '&from=' . urlencode($smsFrom) . 
                       '&to=' . $phoneNumber . 
                       '&text=' . $text;

                Log::debug('SMS API Request (GET)', [
                    'url' => $url,
                    'method' => 'GET',
                    'from' => $smsFrom,
                    'to' => $phoneNumber
                ]);

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_USERAGENT => 'OfisiLink-SMS-Client/1.0'
                ));
            }

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            $curlErrno = curl_errno($curl);

            // Log detailed response
            Log::info('SMS Response', [
                'http_code' => $httpCode,
                'response' => $response
            ]);

            if ($curlErrno) {
                $errorMsg = "cURL Error ({$curlErrno}): {$curlError}";
                Log::error('SMS cURL Error', [
                    'error_code' => $curlErrno,
                    'error_message' => $curlError,
                    'phone' => $phoneNumber,
                    'error' => $errorMsg
                ]);
                curl_close($curl);
                throw new \Exception($errorMsg);
            } else {
                curl_close($curl);

                // Check if SMS was sent successfully based on response
                if ($httpCode == 200) {
                    // Check response content for success indicators
                    $responseLower = strtolower($response ?? '');
                    $responseData = json_decode($response, true);
                    
                    if (strpos($responseLower, 'success') !== false || 
                        strpos($responseLower, '200') !== false ||
                        strpos($responseLower, 'accepted') !== false ||
                        strpos($responseLower, 'sent') !== false ||
                        ($responseData !== null && isset($responseData['success']) && $responseData['success']) ||
                        ($responseData !== null && !isset($responseData['error']))) {
                        
                        Log::info('SMS sent successfully', [
                            'phone' => $phoneNumber,
                            'response' => $response
                        ]);
                        
                        // Log SMS activity if not already logged (for direct SMS calls)
                        try {
                            $userId = Auth::id();
                            $user = User::where('mobile', $phoneNumber)
                                ->orWhere('phone', $phoneNumber)
                                ->first();
                            ActivityLogService::logSMSSent($phoneNumber, $message, $userId, $user?->id, [
                                'provider' => $provider ? $provider->name : 'default',
                                'sms_from' => $smsFrom,
                                'response_code' => $httpCode,
                            ]);
                        } catch (\Exception $e) {
                            // Don't fail SMS sending if activity log fails
                            Log::warning('Failed to log SMS activity', ['error' => $e->getMessage()]);
                        }
                        
                        return true;
                    } else {
                        $errorMsg = 'SMS API returned 200 but response indicates failure';
                        if ($responseData && isset($responseData['error'])) {
                            $errorMsg .= ': ' . $responseData['error'];
                        } elseif ($responseData && isset($responseData['message'])) {
                            $errorMsg .= ': ' . $responseData['message'];
                        }
                        
                        Log::warning('SMS API returned 200 but content indicates failure', [
                            'phone' => $phoneNumber,
                            'response' => $response,
                            'error' => $errorMsg
                        ]);
                        throw new \Exception($errorMsg);
                    }
                } else {
                    $errorMsg = "SMS failed with HTTP code {$httpCode}";
                    if ($response) {
                        $errorMsg .= ': ' . substr($response, 0, 200);
                    }
                    
                    Log::error('SMS failed with HTTP code', [
                        'http_code' => $httpCode,
                        'response' => $response,
                        'phone' => $phoneNumber,
                        'error' => $errorMsg
                    ]);
                    throw new \Exception($errorMsg);
                }
            }
        } catch (\Exception $e) {
            Log::error('SMS sending exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'phone' => $phoneNumber ?? 'unknown',
                'message_text' => $message ?? 'unknown',
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Send email notification using EmailService (PHPMailer)
     */
    public function sendEmail(string $email, string $subject, string $message, array $data = [], ?NotificationProvider $provider = null)
    {
        try {
            // Use provided provider or fallback to default
            $provider = $provider ?? $this->emailProvider;
            
            // Initialize EmailService
            $emailService = new EmailService();
            
            // Configure EmailService with provider settings
            if ($provider) {
                $emailService->updateConfig([
                    'host' => $provider->mail_host ?? 'smtp.gmail.com',
                    'port' => $provider->mail_port ?? 587,
                    'username' => $provider->mail_username ?? '',
                    'password' => $provider->mail_password ?? '',
                    'encryption' => $provider->mail_encryption ?? 'tls',
                    'from_address' => $provider->mail_from_address ?? '',
                    'from_name' => $provider->mail_from_name ?? 'OfisiLink',
                ]);
            } else {
                // Fallback to SystemSetting or env
                $emailService->updateConfig([
                    'host' => SystemSetting::getValue('mail_host') ?: env('MAIL_HOST', 'smtp.gmail.com'),
                    'port' => SystemSetting::getValue('mail_port') ?: env('MAIL_PORT', 587),
                    'username' => SystemSetting::getValue('mail_username') ?: env('MAIL_USERNAME', ''),
                    'password' => SystemSetting::getValue('mail_password') ?: env('MAIL_PASSWORD', ''),
                    'encryption' => SystemSetting::getValue('mail_encryption') ?: env('MAIL_ENCRYPTION', 'tls'),
                    'from_address' => SystemSetting::getValue('mail_from_address') ?: env('MAIL_FROM_ADDRESS', ''),
                    'from_name' => SystemSetting::getValue('mail_from_name') ?: env('MAIL_FROM_NAME', 'OfisiLink'),
                ]);
            }
            
            // Check if message is already HTML (contains HTML tags)
            $isHtml = isset($data['is_html']) && $data['is_html'] === true;
            
            // If message contains HTML tags, treat it as HTML
            if (!$isHtml && (stripos($message, '<html') !== false || stripos($message, '<!DOCTYPE') !== false || stripos($message, '<body') !== false)) {
                $isHtml = true;
            }
            
            // Render email template or use HTML directly
            if ($isHtml) {
                // Message is already HTML, use it directly
                $emailBody = $message;
            } else {
                // Render email template for plain text messages
                $emailBody = View::make('emails.notification', [
                    'emailMessage' => $message,
                    'data' => $data,
                ])->render();
            }
            
            // Send email using EmailService
            $result = $emailService->send($email, $subject, $emailBody);
            
            if ($result) {
                // Log email activity if not already logged (for direct email calls)
                try {
                    $userId = Auth::id();
                    $user = User::where('email', $email)->first();
                    ActivityLogService::logEmailSent($email, $subject, $userId, $user?->id, [
                        'provider' => $provider ? $provider->name : 'default',
                        'method' => 'PHPMailer',
                    ]);
                } catch (\Exception $e) {
                    // Don't fail email sending if activity log fails
                    Log::warning('Failed to log email activity', ['error' => $e->getMessage()]);
                }
            }
            
            return $result;
        } catch (\Exception $e) {
            Log::error('Email sending error: ' . $e->getMessage(), [
                'email' => $email,
                'provider_id' => $provider ? $provider->id : null,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
    
    /**
     * Update mail configuration from SystemSetting (fallback)
     */
    protected function updateMailConfigFromSettings()
    {
        $mailer = SystemSetting::getValue('mail_mailer', config('mail.default', 'smtp'));
        $host = SystemSetting::getValue('mail_host', config('mail.mailers.smtp.host', ''));
        $port = SystemSetting::getValue('mail_port', config('mail.mailers.smtp.port', 587));
        $username = SystemSetting::getValue('mail_username', config('mail.mailers.smtp.username', ''));
        $password = SystemSetting::getValue('mail_password', config('mail.mailers.smtp.password', ''));
        $encryption = SystemSetting::getValue('mail_encryption', config('mail.mailers.smtp.encryption', 'tls'));
        $fromAddress = SystemSetting::getValue('mail_from_address', config('mail.from.address', ''));
        $fromName = SystemSetting::getValue('mail_from_name', config('mail.from.name', 'OfisiLink'));

        config([
            'mail.default' => $mailer,
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);
    }

    /**
     * Broadcast notification for real-time updates (toast notifications)
     */
    protected function broadcastNotification(array $userIds, string $message, ?string $link = null)
    {
        // This will be handled by Laravel Broadcasting/WebSockets if configured
        // For now, we'll log it. Frontend can poll for new notifications or use Server-Sent Events
        try {
            event(new \App\Events\NotificationSent($userIds, $message, $link));
        } catch (\Exception $e) {
            // If event broadcasting is not set up, silently fail
            Log::debug('Broadcasting not configured: ' . $e->getMessage());
        }
    }

    /**
     * Notify users by role
     */
    public function notifyByRole(array $roleNames, string $message, ?string $link = null, ?string $subject = null, array $data = [])
    {
        $userIds = User::whereHas('roles', function($query) use ($roleNames) {
            $query->whereIn('name', $roleNames);
        })->pluck('id')->toArray();

        if (!empty($userIds)) {
            $this->notify($userIds, $message, $link, $subject, $data);
        }
    }

    /**
     * Notify department
     */
    public function notifyDepartment(int $departmentId, string $message, ?string $link = null, ?string $subject = null, array $data = [])
    {
        $userIds = User::where('primary_department_id', $departmentId)
            ->pluck('id')
            ->toArray();

        if (!empty($userIds)) {
            $this->notify($userIds, $message, $link, $subject, $data);
        }
    }

    /**
     * Notify HOD of a department
     */
    public function notifyHOD(int $departmentId, string $message, ?string $link = null, ?string $subject = null, array $data = [])
    {
        $hod = User::where('primary_department_id', $departmentId)
            ->whereHas('roles', function($query) {
                $query->where('name', 'HOD');
            })
            ->first();

        if ($hod) {
            $this->notify($hod->id, $message, $link, $subject, $data);
        }
    }

    /**
     * Notify accountant(s)
     */
    public function notifyAccountant(string $message, ?string $link = null, ?string $subject = null, array $data = [])
    {
        $this->notifyByRole(['Accountant'], $message, $link, $subject, $data);
    }

    /**
     * Notify CEO/Director
     */
    public function notifyCEO(string $message, ?string $link = null, ?string $subject = null, array $data = [])
    {
        $this->notifyByRole(['CEO', 'Director'], $message, $link, $subject, $data);
    }

    /**
     * Notify HR
     */
    public function notifyHR(string $message, ?string $link = null, ?string $subject = null, array $data = [])
    {
        $this->notifyByRole(['HR Officer'], $message, $link, $subject, $data);
    }

    /**
     * Send push notification via FCM (Firebase Cloud Messaging)
     * 
     * @param int|array $userIds User ID(s) to send push notification to
     * @param string $message Notification message
     * @param string|null $link Optional deep link
     * @param array $data Additional data payload
     */
    public function sendPushNotification($userIds, string $message, ?string $link = null, array $data = [])
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        // Get FCM server key from settings
        $fcmServerKey = SystemSetting::getValue('fcm_server_key') ?: env('FCM_SERVER_KEY');
        
        if (!$fcmServerKey) {
            Log::warning('FCM Server Key not configured. Push notifications disabled.');
            return false;
        }

        // Get all active device tokens for these users
        $deviceTokens = DeviceToken::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->get();

        if ($deviceTokens->isEmpty()) {
            Log::debug('No active device tokens found for push notification', ['user_ids' => $userIds]);
            return false;
        }

        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $successCount = 0;
        $failureCount = 0;

        foreach ($deviceTokens as $deviceToken) {
            try {
                $payload = [
                    'to' => $deviceToken->token,
                    'notification' => [
                        'title' => $data['title'] ?? 'OfisiLink',
                        'body' => $message,
                        'sound' => 'default',
                        'badge' => $this->getUnreadNotificationCount($deviceToken->user_id),
                    ],
                    'data' => array_merge([
                        'message' => $message,
                        'link' => $link,
                        'type' => $data['type'] ?? 'notification',
                        'timestamp' => now()->toIso8601String(),
                    ], $data),
                    'priority' => 'high',
                ];

                // For iOS, use 'apns' configuration
                if ($deviceToken->device_type === 'ios') {
                    $payload['apns'] = [
                        'payload' => [
                            'aps' => [
                                'sound' => 'default',
                                'badge' => $this->getUnreadNotificationCount($deviceToken->user_id),
                                'content-available' => 1,
                            ],
                        ],
                    ];
                }

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $fcmServerKey,
                    'Content-Type' => 'application/json',
                ])->post($fcmUrl, $payload);

                if ($response->successful()) {
                    $result = $response->json();
                    
                    // Check if FCM returned an error
                    if (isset($result['failure']) && $result['failure'] > 0) {
                        // Token might be invalid, deactivate it
                        if (isset($result['results'][0]['error'])) {
                            $error = $result['results'][0]['error'];
                            if (in_array($error, ['InvalidRegistration', 'NotRegistered', 'MismatchSenderId'])) {
                                $deviceToken->deactivate();
                                Log::info('Deactivated invalid device token', [
                                    'token_id' => $deviceToken->id,
                                    'error' => $error
                                ]);
                            }
                        }
                        $failureCount++;
                    } else {
                        $deviceToken->markAsUsed();
                        $successCount++;
                    }
                } else {
                    Log::error('FCM API request failed', [
                        'status' => $response->status(),
                        'response' => $response->body(),
                        'device_token_id' => $deviceToken->id
                    ]);
                    $failureCount++;
                }
            } catch (\Exception $e) {
                Log::error('Exception sending push notification', [
                    'device_token_id' => $deviceToken->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $failureCount++;
            }
        }

        Log::info('Push notification sent', [
            'user_ids' => $userIds,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'total_tokens' => $deviceTokens->count()
        ]);

        return $successCount > 0;
    }

    /**
     * Get unread notification count for badge
     */
    protected function getUnreadNotificationCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Send push notification to specific device token
     */
    public function sendPushToDevice(string $token, string $message, ?string $link = null, array $data = [])
    {
        $deviceToken = DeviceToken::where('token', $token)
            ->where('is_active', true)
            ->first();

        if (!$deviceToken) {
            return false;
        }

        return $this->sendPushNotification($deviceToken->user_id, $message, $link, $data);
    }
}
