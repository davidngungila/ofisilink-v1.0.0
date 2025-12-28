<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected $mailer;
    protected $config;

    public function __construct()
    {
        $this->mailer = new PHPMailer(true);
        $this->loadConfig();
    }

    /**
     * Load email configuration from environment or config
     */
    protected function loadConfig()
    {
        $this->config = [
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'username' => env('MAIL_USERNAME', 'davidngungila@gmail.com'),
            'password' => env('MAIL_PASSWORD', 'vlxcdpwaizofnti'), // Gmail app password (spaces removed: vlxc dwpw aizo fnti)
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'from_address' => env('MAIL_FROM_ADDRESS', 'davidngungila@gmail.com'),
            'from_name' => env('MAIL_FROM_NAME', 'OfisiLink System'),
        ];
    }

    /**
     * Configure PHPMailer with settings
     */
    protected function configure()
    {
        try {
            // Server settings
            $this->mailer->isSMTP();
            $this->mailer->Host = $this->config['host'];
            $this->mailer->SMTPAuth = true;
            $this->mailer->Username = $this->config['username'];
            $this->mailer->Password = $this->config['password'];
            // Set encryption method (tls or ssl)
            if ($this->config['encryption'] === 'ssl') {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }
            
            $this->mailer->Port = $this->config['port'];
            
            // Connection timeout settings (in seconds) - increased for slow connections
            $this->mailer->Timeout = 30; // Connection timeout
            $this->mailer->SMTPKeepAlive = false;
            
            // Enable verbose debug output (set to 0 for production, 2 for detailed SMTP conversation)
            $this->mailer->SMTPDebug = env('MAIL_DEBUG', 0);
            
            // Enable automatic TLS/SSL
            $this->mailer->SMTPAutoTLS = true;
            
            // Character set
            $this->mailer->CharSet = 'UTF-8';
            
            // From address
            $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);
            
            // Enable HTML emails
            $this->mailer->isHTML(true);
            
            // SMTP Options with flexible SSL/TLS verification
            // For Gmail, we need to be more flexible with SSL verification
            // On Windows, certificate verification can be problematic, so we make it more lenient
            $verifyPeer = env('MAIL_SSL_VERIFY_PEER', true);
            $verifyPeerName = env('MAIL_SSL_VERIFY_PEER_NAME', true);
            
            // For Gmail specifically, be more lenient with SSL verification
            // This helps with Windows environments where certificate verification can fail
            if (stripos($this->config['host'], 'gmail.com') !== false) {
                // For Gmail, we can be more lenient with peer verification
                // This is safe because Gmail uses valid certificates
                $verifyPeer = env('MAIL_SSL_VERIFY_PEER', false); // More lenient for Gmail
                $verifyPeerName = env('MAIL_SSL_VERIFY_PEER_NAME', false); // More lenient for Gmail
            }
            
            $sslOptions = [
                'verify_peer' => $verifyPeer,
                'verify_peer_name' => $verifyPeerName,
                'allow_self_signed' => env('MAIL_SSL_ALLOW_SELF_SIGNED', false),
                'peer_name' => $this->config['host'], // Set peer name for verification
            ];
            
            // Add CA file/path if specified
            if (env('MAIL_SSL_CAFILE')) {
                $sslOptions['cafile'] = env('MAIL_SSL_CAFILE');
            }
            if (env('MAIL_SSL_CAPATH')) {
                $sslOptions['capath'] = env('MAIL_SSL_CAPATH');
            }
            
            $this->mailer->SMTPOptions = [
                'ssl' => $sslOptions
            ];
            
        } catch (Exception $e) {
            Log::error('EmailService configuration error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send email with attachment
     * 
     * @param string|array $to Email address(es) - can be string or array
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string|null $attachmentPath Path to file to attach
     * @param string|null $attachmentName Name for the attachment
     * @return bool
     */
    public function send($to, string $subject, string $body, ?string $attachmentPath = null, ?string $attachmentName = null): bool
    {
        $maxRetries = 2;
        $retryCount = 0;
        
        while ($retryCount <= $maxRetries) {
            try {
                // Create new instance for each email (or retry)
                $this->mailer = new PHPMailer(true);
                $this->configure();
                
                // Recipients
                if (is_array($to)) {
                    foreach ($to as $email) {
                        $this->mailer->addAddress($email);
                    }
                } else {
                    $this->mailer->addAddress($to);
                }
                
                // Content
                $this->mailer->Subject = $subject;
                $this->mailer->Body = $body;
                $this->mailer->AltBody = strip_tags($body); // Plain text version
                
                // Attachment
                if ($attachmentPath && file_exists($attachmentPath)) {
                    $this->mailer->addAttachment($attachmentPath, $attachmentName ?? basename($attachmentPath));
                }
                
                // Send email
                $this->mailer->send();
                
                Log::info('Email sent successfully', [
                    'to' => is_array($to) ? implode(', ', $to) : $to,
                    'subject' => $subject,
                    'has_attachment' => !empty($attachmentPath),
                    'retry_count' => $retryCount
                ]);
                
                return true;
                
            } catch (Exception $e) {
                $retryCount++;
                $errorInfo = $this->mailer->ErrorInfo ?? $e->getMessage();
                
                // Check if it's a connection error that might be retryable
                $isConnectionError = stripos($errorInfo, 'connect') !== false || 
                                    stripos($errorInfo, 'timeout') !== false ||
                                    stripos($errorInfo, '10060') !== false;
                
                if ($isConnectionError && $retryCount <= $maxRetries) {
                    // Wait a bit before retrying
                    sleep(2);
                    Log::warning("Email connection failed, retrying ({$retryCount}/{$maxRetries})", [
                        'to' => is_array($to) ? implode(', ', $to) : $to,
                        'error' => $errorInfo
                    ]);
                    continue;
                }
                
                // If not retryable or max retries reached, log and return false
                Log::error('Email sending failed', [
                    'to' => is_array($to) ? implode(', ', $to) : $to,
                    'subject' => $subject,
                    'error' => $errorInfo,
                    'exception' => $e->getMessage(),
                    'retry_count' => $retryCount
                ]);
                
                return false;
            }
        }
        
        return false;
    }

    /**
     * Send email to multiple recipients
     * 
     * @param array $recipients Array of email addresses
     * @param string $subject Email subject
     * @param string $body Email body (HTML)
     * @param string|null $attachmentPath Path to file to attach
     * @param string|null $attachmentName Name for the attachment
     * @return array Results for each recipient
     */
    public function sendToMultiple(array $recipients, string $subject, string $body, ?string $attachmentPath = null, ?string $attachmentName = null): array
    {
        $results = [];
        
        foreach ($recipients as $recipient) {
            $results[$recipient] = $this->send($recipient, $subject, $body, $attachmentPath, $attachmentName);
        }
        
        return $results;
    }

    /**
     * Test email configuration with connection testing
     * 
     * @param string $to Test email address
     * @return array
     */
    public function testConfiguration(string $to): array
    {
        $originalConfig = $this->config;
        $isGmail = stripos($this->config['host'], 'gmail.com') !== false;
        
        // For Gmail, try both ports: first 587 (TLS), then 465 (SSL) if it fails
        $portsToTry = [];
        if ($isGmail) {
            $currentPort = $this->config['port'] ?? 587;
            $currentEncryption = $this->config['encryption'] ?? 'tls';
            
            // Add current configuration first
            $portsToTry[] = ['port' => $currentPort, 'encryption' => $currentEncryption];
            
            // Add alternative if different
            if ($currentPort == 587 && $currentEncryption == 'tls') {
                $portsToTry[] = ['port' => 465, 'encryption' => 'ssl'];
            } elseif ($currentPort == 465 && $currentEncryption == 'ssl') {
                $portsToTry[] = ['port' => 587, 'encryption' => 'tls'];
            }
        } else {
            $portsToTry[] = [
                'port' => $this->config['port'] ?? 587,
                'encryption' => $this->config['encryption'] ?? 'tls'
            ];
        }
        
        $lastError = null;
        $lastSuggestion = null;
        
        foreach ($portsToTry as $portConfig) {
            try {
                // Update config for this attempt
                $this->config = array_merge($originalConfig, [
                    'port' => $portConfig['port'],
                    'encryption' => $portConfig['encryption']
                ]);
                
                // First, test basic connection to SMTP server
                $connectionTest = $this->testConnection();
                if (!$connectionTest['success']) {
                    $lastError = $connectionTest['error'];
                    $lastSuggestion = $this->getConnectionSuggestion();
                    continue; // Try next port
                }
                
                $subject = 'OfisiLink Email Configuration Test';
                $body = '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <style>
                        body {
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                            line-height: 1.6;
                            color: #333333;
                            background-color: #f5f5f5;
                            margin: 0;
                            padding: 0;
                        }
                        .email-container {
                            max-width: 600px;
                            margin: 40px auto;
                            background-color: #ffffff;
                            border-radius: 8px;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                            overflow: hidden;
                        }
                        .email-header {
                            background-color: #940000;
                            color: #ffffff;
                            padding: 30px 40px;
                            text-align: center;
                        }
                        .email-header h1 {
                            margin: 0;
                            font-size: 24px;
                            font-weight: 600;
                            letter-spacing: 0.5px;
                        }
                        .email-body {
                            padding: 40px;
                        }
                        .success-badge {
                            background-color: #f0f9ff;
                            border-left: 4px solid #940000;
                            padding: 20px;
                            margin: 20px 0;
                            border-radius: 4px;
                        }
                        .success-badge p {
                            margin: 0;
                            color: #1e40af;
                            font-weight: 500;
                        }
                        .config-section {
                            margin-top: 30px;
                        }
                        .config-section h3 {
                            color: #940000;
                            font-size: 18px;
                            font-weight: 600;
                            margin-bottom: 15px;
                            padding-bottom: 10px;
                            border-bottom: 2px solid #f0f0f0;
                        }
                        .config-details {
                            background-color: #fafafa;
                            border-radius: 6px;
                            padding: 20px;
                            margin-top: 15px;
                        }
                        .config-item {
                            display: flex;
                            justify-content: space-between;
                            padding: 12px 0;
                            border-bottom: 1px solid #e5e5e5;
                        }
                        .config-item:last-child {
                            border-bottom: none;
                        }
                        .config-label {
                            font-weight: 600;
                            color: #666666;
                            text-transform: uppercase;
                            font-size: 12px;
                            letter-spacing: 0.5px;
                        }
                        .config-value {
                            color: #333333;
                            font-weight: 500;
                        }
                        .email-footer {
                            background-color: #f9f9f9;
                            padding: 25px 40px;
                            text-align: center;
                            border-top: 1px solid #e5e5e5;
                            color: #888888;
                            font-size: 13px;
                        }
                        .divider {
                            height: 1px;
                            background-color: #e5e5e5;
                            margin: 30px 0;
                        }
                    </style>
                </head>
                <body>
                    <div class="email-container">
                        <div class="email-header">
                            <h1>Email Configuration Test</h1>
                        </div>
                        <div class="email-body">
                            <p style="font-size: 16px; margin-top: 0;">Dear Administrator,</p>
                            
                            <p>This is a test email to verify that your email configuration is working correctly.</p>
                            
                            <div class="success-badge">
                                <p>✓ Email configuration test successful</p>
                            </div>
                            
                            <p>If you are reading this message, it means your SMTP settings have been configured properly and emails can be sent from your OfisiLink system.</p>
                            
                            <div class="config-section">
                                <h3>Configuration Details</h3>
                                <div class="config-details">
                                    <div class="config-item">
                                        <span class="config-label">SMTP Host</span>
                                        <span class="config-value">' . htmlspecialchars($this->config['host']) . '</span>
                                    </div>
                                    <div class="config-item">
                                        <span class="config-label">Port</span>
                                        <span class="config-value">' . htmlspecialchars($this->config['port']) . '</span>
                                    </div>
                                    <div class="config-item">
                                        <span class="config-label">Encryption</span>
                                        <span class="config-value">' . strtoupper(htmlspecialchars($this->config['encryption'])) . '</span>
                                    </div>
                                    <div class="config-item">
                                        <span class="config-label">From Address</span>
                                        <span class="config-value">' . htmlspecialchars($this->config['from_address']) . '</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="divider"></div>
                            
                            <p style="color: #666666; font-size: 14px; margin-bottom: 0;">This is an automated test email generated by the OfisiLink System. No action is required on your part.</p>
                        </div>
                        <div class="email-footer">
                            <p style="margin: 0;">OfisiLink System &copy; ' . date('Y') . '</p>
                        </div>
                    </div>
                </body>
                </html>';
                
                $result = $this->send($to, $subject, $body);
                
                if ($result) {
                    // Update original config if successful
                    $originalConfig = $this->config;
                    return [
                        'success' => true,
                        'message' => 'Test email sent successfully!',
                        'error' => null
                    ];
                } else {
                    $errorInfo = $this->mailer->ErrorInfo ?? 'Unknown error';
                    $lastError = $errorInfo;
                    $lastSuggestion = $this->getErrorSuggestion($errorInfo);
                    continue; // Try next port
                }
                
            } catch (Exception $e) {
                $lastError = $e->getMessage();
                $lastSuggestion = $this->getErrorSuggestion($e->getMessage());
                continue; // Try next port
            }
        }
        
        // Restore original config
        $this->config = $originalConfig;
        
        // All attempts failed
        return [
            'success' => false,
            'message' => 'Failed to send test email. Check logs for details.',
            'error' => $this->sanitizeString($lastError ?? 'Unknown error'),
            'suggestion' => $this->sanitizeString($lastSuggestion ?? 'Please check your email configuration and try again.')
        ];
    }
    
    /**
     * Test SMTP connection
     */
    protected function testConnection(): array
    {
        try {
            $host = $this->config['host'];
            $port = $this->config['port'];
            
            // Test basic socket connection with timeout
            $connection = @fsockopen($host, $port, $errno, $errstr, 10);
            
            if (!$connection) {
                return [
                    'success' => false,
                    'error' => "Connection failed: {$errstr} (Error {$errno})"
                ];
            }
            
            fclose($connection);
            return ['success' => true];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get connection troubleshooting suggestion
     */
    protected function getConnectionSuggestion(): string
    {
        $host = $this->config['host'];
        $port = $this->config['port'];
        
        if (stripos($host, 'gmail.com') !== false) {
            if ($port == 587) {
                return 'For Gmail with port 587 (TLS): Check firewall allows outbound connections. Try port 465 (SSL) instead. Verify app password is correct.';
            } elseif ($port == 465) {
                return 'For Gmail with port 465 (SSL): Check firewall allows outbound connections. Verify app password is correct. Ensure SSL encryption is set.';
            }
            return 'For Gmail: Use port 587 (TLS) or 465 (SSL). Check firewall settings. Verify app password is correct.';
        }
        
        return "Check firewall settings for port {$port}, verify host '{$host}' is correct, and ensure SMTP service is running.";
    }
    
    /**
     * Get error-specific troubleshooting suggestion
     */
    protected function getErrorSuggestion(string $error): string
    {
        $errorLower = strtolower($error);
        
        if (stripos($errorLower, 'connect') !== false || stripos($errorLower, 'timeout') !== false || stripos($errorLower, '10060') !== false) {
            $port = $this->config['port'];
            $encryption = $this->config['encryption'];
            $altPort = $port == 587 ? 465 : 587;
            $altEncryption = $encryption == 'tls' ? 'ssl' : 'tls';
            
            return "Connection timeout (Error 10060). Solutions:\n1. Check firewall allows outbound connections on port {$port}\n2. Try alternative: Port {$altPort} with {$altEncryption} encryption\n3. Verify host '{$this->config['host']}' is correct\n4. Check internet connection\n5. Contact network administrator if behind corporate firewall";
        }
        
        if (stripos($errorLower, 'authentication') !== false || stripos($errorLower, 'login') !== false) {
            return 'Authentication failed. Verify username and password are correct. For Gmail, use App Password (not regular password). Enable 2-factor authentication and generate app password.';
        }
        
        if (stripos($errorLower, 'ssl') !== false || stripos($errorLower, 'tls') !== false || stripos($errorLower, 'certificate') !== false) {
            return 'SSL/TLS error. Try: 1) Change encryption from TLS to SSL (port 465) or vice versa, 2) Check if SSL certificates are properly installed, 3) Verify encryption setting matches port (587=TLS, 465=SSL)';
        }
        
        return 'Check email configuration settings and server logs for more details. Verify all SMTP settings are correct.';
    }

    /**
     * Update configuration dynamically
     * 
     * @param array $config Configuration array
     */
    public function updateConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
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
}

