<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\EmailService;
use Illuminate\Support\Facades\View;

class NotificationProvider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'is_active',
        'is_primary',
        'priority',
        'mailer_type',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'sms_username',
        'sms_password',
        'sms_from',
        'sms_url',
        'additional_settings',
        'description',
        'last_tested_at',
        'last_test_status',
        'last_test_message',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'priority' => 'integer',
        'mail_port' => 'integer',
        'last_test_status' => 'boolean',
        'last_tested_at' => 'datetime',
        'additional_settings' => 'array',
    ];

    /**
     * Get the primary provider for a given type
     */
    public static function getPrimary(string $type): ?self
    {
        return static::where('type', $type)
            ->where('is_active', true)
            ->where('is_primary', true)
            ->orderBy('priority', 'desc')
            ->first();
    }

    /**
     * Get all active providers for a given type, ordered by priority
     */
    public static function getActive(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('type', $type)
            ->where('is_active', true)
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Set this provider as primary (and unset others)
     */
    public function setAsPrimary(): void
    {
        // Unset other primary providers of the same type
        static::where('type', $this->type)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);
        
        // Set this as primary
        $this->update(['is_primary' => true]);
    }

    /**
     * Test email configuration using EmailService (PHPMailer)
     */
    public function testEmail(string $testEmail): array
    {
        try {
            // Use EmailService (PHPMailer) for reliable email sending
            $emailService = new EmailService();
            
            // Auto-correct Gmail ports if wrong
            $port = $this->mail_port ?? 587;
            $encryption = $this->mail_encryption ?? 'tls';
            $host = $this->mail_host ?? 'smtp.gmail.com';
            
            // Gmail port validation and auto-correction
            if (stripos($host, 'gmail.com') !== false) {
                if ($port == 2525 || $port == 25 || ($port != 587 && $port != 465)) {
                    // Auto-correct to standard Gmail port
                    if ($encryption === 'ssl' || $encryption === 'smtps') {
                        $port = 465;
                        $encryption = 'ssl';
                    } else {
                        $port = 587;
                        $encryption = 'tls';
                    }
                    \Log::warning("Gmail port auto-corrected to {$port} with {$encryption} encryption", [
                        'provider_id' => $this->id,
                        'original_port' => $this->mail_port
                    ]);
                }
            }
            
            // Clean password (remove spaces for app passwords)
            $password = $this->mail_password ?? '';
            if (stripos($host, 'gmail.com') !== false && strpos($password, ' ') !== false) {
                $password = str_replace(' ', '', $password);
            }
            
            // Configure EmailService with provider settings
            $emailService->updateConfig([
                'host' => $host,
                'port' => $port,
                'username' => $this->mail_username ?? '',
                'password' => $password,
                'encryption' => $encryption,
                'from_address' => $this->mail_from_address ?? $this->mail_username ?? '',
                'from_name' => $this->mail_from_name ?? 'OfisiLink',
            ]);
            
            // Use testConfiguration method which includes connection testing
            $testResult = $emailService->testConfiguration($testEmail);
            
            // Update last test status
            $this->update([
                'last_tested_at' => now(),
                'last_test_status' => $testResult['success'],
                'last_test_message' => $testResult['message'] ?? ($testResult['error'] ?? null)
            ]);
            
            if ($testResult['success']) {
                return [
                    'success' => true,
                    'message' => 'Test email sent successfully!',
                    'error' => null
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $this->sanitizeString($testResult['message'] ?? 'Failed to send test email'),
                    'error' => $this->sanitizeString($testResult['error'] ?? 'Unknown error'),
                    'suggestion' => $this->sanitizeString($testResult['suggestion'] ?? null)
                ];
            }
        } catch (\Exception $e) {
            // Sanitize error message
            $errorMessage = $this->sanitizeString($e->getMessage());
            
            // Update last test status
            $this->update([
                'last_tested_at' => now(),
                'last_test_status' => false,
                'last_test_message' => $errorMessage
            ]);
            
            return [
                'success' => false,
                'message' => 'Error testing email configuration: ' . $errorMessage,
                'error' => $errorMessage,
                'suggestion' => $this->sanitizeString($this->getErrorSuggestion($errorMessage))
            ];
        }
    }

    /**
     * Test SMS configuration
     */
    public function testSMS(string $testPhone): array
    {
        try {
            $notifier = app(\App\Services\NotificationService::class);
            $result = $notifier->sendSMS($testPhone, 'Test SMS from OfisiLink System. If you receive this, SMS configuration is working correctly.', $this);

            $this->update([
                'last_tested_at' => now(),
                'last_test_status' => $result,
                'last_test_message' => $result ? 'Test SMS sent successfully' : 'SMS sending failed',
            ]);

            return [
                'success' => $result,
                'message' => $result ? 'SMS sent successfully' : 'SMS sending failed',
            ];
        } catch (\Exception $e) {
            $this->update([
                'last_tested_at' => now(),
                'last_test_status' => false,
                'last_test_message' => 'Test failed: ' . $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'SMS test failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check connection status
     */
    public function checkConnectionStatus(): array
    {
        if ($this->type === 'email') {
            return $this->checkEmailConnection();
        } else {
            return $this->checkSMSConnection();
        }
    }

    /**
     * Check email connection status
     */
    protected function checkEmailConnection(): array
    {
        try {
            $host = $this->mail_host ?? '';
            $port = $this->mail_port ?? 587;
            
            if (empty($host) || empty($port)) {
                return [
                    'status' => 'disconnected',
                    'message' => 'Email configuration incomplete (missing host or port)',
                ];
            }
            
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            
            if ($connection) {
                fclose($connection);
                return [
                    'status' => 'connected',
                    'message' => 'Email server connection successful',
                ];
            } else {
                return [
                    'status' => 'disconnected',
                    'message' => 'Cannot connect to email server: ' . ($errstr ?? 'Connection timeout'),
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error checking email status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check SMS connection status
     */
    protected function checkSMSConnection(): array
    {
        try {
            $username = $this->sms_username ?? '';
            $password = $this->sms_password ?? '';
            $url = $this->sms_url ?? '';
            
            if (empty($username) || empty($password) || empty($url)) {
                return [
                    'status' => 'disconnected',
                    'message' => 'SMS configuration incomplete',
                ];
            }
            
            $parsedUrl = parse_url($url);
            if (!$parsedUrl || !isset($parsedUrl['host'])) {
                return [
                    'status' => 'disconnected',
                    'message' => 'Invalid SMS gateway URL',
                ];
            }
            
            $host = $parsedUrl['host'];
            $port = $parsedUrl['port'] ?? (isset($parsedUrl['scheme']) && $parsedUrl['scheme'] === 'https' ? 443 : 80);
            
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            
            if ($connection) {
                fclose($connection);
                return [
                    'status' => 'connected',
                    'message' => 'SMS gateway connection successful',
                ];
            } else {
                return [
                    'status' => 'disconnected',
                    'message' => 'Cannot connect to SMS gateway: ' . ($errstr ?? 'Connection timeout'),
                ];
            }
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Error checking SMS status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sanitize string to remove sensitive information and format safely
     * 
     * @param string|null $string String to sanitize
     * @param int $maxLength Maximum length (0 = no limit)
     * @return string
     */
    protected function sanitizeString(?string $string, int $maxLength = 500): string
    {
        if ($string === null) {
            return '';
        }

        // Remove null bytes and other control characters
        $string = str_replace(["\0", "\r"], '', $string);
        
        // Trim whitespace
        $string = trim($string);
        
        // Limit length if specified
        if ($maxLength > 0 && strlen($string) > $maxLength) {
            $string = substr($string, 0, $maxLength) . '...';
        }
        
        return $string;
    }

    /**
     * Get error-specific troubleshooting suggestion
     */
    protected function getErrorSuggestion(string $error): string
    {
        $errorLower = strtolower($error);
        
        if (stripos($errorLower, 'connect') !== false || stripos($errorLower, 'timeout') !== false) {
            return 'Connection error. Check your network connection and server settings.';
        }
        
        if (stripos($errorLower, 'authentication') !== false || stripos($errorLower, 'login') !== false) {
            return 'Authentication failed. Verify your credentials are correct.';
        }
        
        if (stripos($errorLower, 'ssl') !== false || stripos($errorLower, 'tls') !== false) {
            return 'SSL/TLS error. Check your encryption settings.';
        }
        
        return 'Please check your configuration and try again.';
    }
}
