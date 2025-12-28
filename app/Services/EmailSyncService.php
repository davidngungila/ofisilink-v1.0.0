<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentEmailConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class EmailSyncService
{
    /**
     * Sync emails from all active email configurations
     */
    public function syncAll($dateFrom = null, $dateTo = null, $syncMode = 'all')
    {
        $configs = IncidentEmailConfig::getActive();
        $totalSynced = 0;

        foreach ($configs as $config) {
            try {
                $count = $this->syncEmailConfig($config, $dateFrom, $dateTo, $syncMode);
                $totalSynced += $count;
                
                $config->update([
                    'last_sync_at' => now(),
                    'sync_count' => ($config->sync_count ?? 0) + $count,
                    'connection_status' => 'connected',
                    'connection_error' => null,
                ]);
            } catch (\Exception $e) {
                Log::error('Email sync failed for ' . $config->email_address . ': ' . $e->getMessage());
                $config->update([
                    'failed_sync_count' => ($config->failed_sync_count ?? 0) + 1,
                    'connection_status' => 'failed',
                    'connection_error' => $e->getMessage(),
                ]);
            }
        }

        return $totalSynced;
    }

    /**
     * Sync emails from a specific email configuration
     */
    public function syncEmailConfig(IncidentEmailConfig $config, $dateFrom = null, $dateTo = null, $syncMode = 'all')
    {
        try {
            if ($config->protocol === 'imap') {
                return $this->syncImap($config, $dateFrom, $dateTo, $syncMode);
            } else {
                return $this->syncPop3($config, $dateFrom, $dateTo, $syncMode);
            }
        } catch (\Exception $e) {
            Log::error('Email sync error for ' . $config->email_address . ': ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync emails from IMAP
     */
    protected function syncImap(IncidentEmailConfig $config, $dateFrom = null, $dateTo = null, $syncMode = 'all')
    {
        $connection = null;
        $syncedCount = 0;
        $maxMessages = 100; // Limit to prevent crashes

        try {
            $mailbox = '{' . $config->host . ':' . $config->port . '/' . ($config->ssl_enabled ? 'ssl' : 'notls') . '}' . ($config->folder ?? 'INBOX');
            
            // Set timeout to prevent hanging
            set_time_limit(300); // 5 minutes max
            
            // Set IMAP timeouts to prevent hanging
            @imap_timeout(IMAP_OPENTIMEOUT, 30);
            @imap_timeout(IMAP_READTIMEOUT, 30);
            @imap_timeout(IMAP_WRITETIMEOUT, 30);
            @imap_timeout(IMAP_CLOSETIMEOUT, 30);
            
            // Open connection without OP_HALFOPEN to allow searching
            $connection = @imap_open($mailbox, $config->username, $config->password, 0, 1);

            if (!$connection) {
                $error = imap_last_error() ?: 'Unknown error';
                imap_errors(); // Clear error stack
                imap_alerts(); // Clear alert stack
                
                // Provide more helpful error messages
                $errorMessage = $error;
                if (stripos($errorMessage, 'too many login failures') !== false || stripos($errorMessage, 'login failures') !== false) {
                    $errorMessage = 'Too many login failures. The email account may be temporarily locked.';
                } elseif (stripos($errorMessage, 'authentication failed') !== false || stripos($errorMessage, 'invalid credentials') !== false) {
                    $errorMessage = 'Authentication failed. Please check your username and password.';
                } elseif (stripos($errorMessage, 'connection refused') !== false || stripos($errorMessage, 'cannot connect') !== false) {
                    $errorMessage = 'Cannot connect to email server. Please check the host and port settings.';
                }
                
                Log::error("IMAP connection failed for {$config->email_address}: {$errorMessage}");
                throw new \Exception('Failed to connect to IMAP server: ' . $errorMessage);
            }

            // Build search criteria based on sync mode
            $searchCriteria = $this->buildSearchCriteria($config, $dateFrom, $dateTo, $syncMode);
            
            Log::info("Email sync search criteria for {$config->email_address}: {$searchCriteria}");
            
            $messages = @imap_search($connection, $searchCriteria);
            
            if ($messages === false) {
                $error = imap_last_error() ?: 'No messages found or search failed';
                Log::warning("IMAP search failed for {$config->email_address}: {$error}. Criteria: {$searchCriteria}");
                imap_close($connection);
                return 0;
            }
            
            if (!is_array($messages) || empty($messages)) {
                Log::info("No new emails found for {$config->email_address} with criteria: {$searchCriteria}");
                imap_close($connection);
                return 0;
            }
            
            Log::info("Found " . count($messages) . " email(s) to process for {$config->email_address}");

            // Limit messages to prevent crashes
            if (count($messages) > $maxMessages) {
                $messages = array_slice($messages, 0, $maxMessages);
                Log::warning("Limiting sync to {$maxMessages} messages for " . $config->email_address);
            }

            foreach ($messages as $messageNumber) {
                try {
                    $header = @imap_headerinfo($connection, $messageNumber);
                    if (!$header) {
                        continue;
                    }

                    // Check date range if specified
                    if ($dateFrom || $dateTo) {
                        $emailDate = isset($header->date) ? strtotime($header->date) : time();
                        if ($dateFrom && $emailDate < strtotime($dateFrom)) {
                            continue;
                        }
                        if ($dateTo && $emailDate > strtotime($dateTo . ' 23:59:59')) {
                            continue;
                        }
                    }

                    // Check if this email already processed
                    $messageId = $header->message_id ?? null;
                    if ($messageId && Incident::where('email_message_id', $messageId)->exists()) {
                        continue;
                    }

                    // Get email body - handle multipart emails
                    $structure = @imap_fetchstructure($connection, $messageNumber);
                    $body = '';
                    
                    if ($structure) {
                        // Check if email is multipart
                        if (isset($structure->parts) && count($structure->parts) > 0) {
                            // Multipart email - try to get text/plain part
                            foreach ($structure->parts as $partNum => $part) {
                                $partNumber = $partNum + 1;
                                $partBody = @imap_fetchbody($connection, $messageNumber, (string)$partNumber);
                                
                                if ($partBody && isset($part->subtype) && strtolower($part->subtype) === 'plain') {
                                    // Decode based on encoding
                                    if (isset($part->encoding)) {
                                        if ($part->encoding == 3) { // BASE64
                                            $partBody = @imap_base64($partBody);
                                        } elseif ($part->encoding == 4) { // QUOTED-PRINTABLE
                                            $partBody = quoted_printable_decode($partBody);
                                        }
                                    }
                                    $body = $partBody;
                                    break;
                                }
                            }
                            
                            // If no plain text found, try HTML part
                            if (empty($body)) {
                                foreach ($structure->parts as $partNum => $part) {
                                    $partNumber = $partNum + 1;
                                    $partBody = @imap_fetchbody($connection, $messageNumber, (string)$partNumber);
                                    
                                    if ($partBody && isset($part->subtype) && strtolower($part->subtype) === 'html') {
                                        // Decode HTML and strip tags
                                        if (isset($part->encoding)) {
                                            if ($part->encoding == 3) { // BASE64
                                                $partBody = @imap_base64($partBody);
                                            } elseif ($part->encoding == 4) { // QUOTED-PRINTABLE
                                                $partBody = quoted_printable_decode($partBody);
                                            }
                                        }
                                        $body = strip_tags($partBody);
                                        break;
                                    }
                                }
                            }
                        } else {
                            // Simple email - get body directly
                            $body = @imap_body($connection, $messageNumber);
                            if ($body && isset($structure->encoding)) {
                                if ($structure->encoding == 3) { // BASE64
                                    $body = @imap_base64($body);
                                } elseif ($structure->encoding == 4) { // QUOTED-PRINTABLE
                                    $body = quoted_printable_decode($body);
                                }
                            }
                        }
                    } else {
                        // Fallback to simple body fetch
                        $body = @imap_body($connection, $messageNumber);
                    }
                    
                    if (empty($body)) {
                        Log::warning("Could not fetch body for message {$messageNumber}");
                        continue;
                    }
                    
                    // Extract email data
                    $subject = $header->subject ?? 'No Subject';
                    // Decode subject using imap_mime_header_decode if available
                    if (function_exists('imap_mime_header_decode') && preg_match('/=\?/', $subject)) {
                        $decoded = imap_mime_header_decode($subject);
                        $decodedSubject = '';
                        foreach ($decoded as $part) {
                            $decodedSubject .= $part->text;
                        }
                        if (!empty($decodedSubject)) {
                            $subject = $decodedSubject;
                        }
                    }
                    
                    $fromEmail = isset($header->from[0]) ? ($header->from[0]->mailbox . '@' . $header->from[0]->host) : 'unknown@unknown.com';
                    $fromName = isset($header->from[0]->personal) ? $header->from[0]->personal : $fromEmail;
                    // Decode from name if encoded
                    if (function_exists('imap_mime_header_decode') && preg_match('/=\?/', $fromName)) {
                        $decoded = imap_mime_header_decode($fromName);
                        $decodedName = '';
                        foreach ($decoded as $part) {
                            $decodedName .= $part->text;
                        }
                        if (!empty($decodedName)) {
                            $fromName = $decodedName;
                        }
                    }
                    
                    // Get recipient email (TO field) to verify it matches configured email
                    $toEmail = null;
                    if (isset($header->to) && is_array($header->to)) {
                        foreach ($header->to as $toAddr) {
                            if (isset($toAddr->mailbox) && isset($toAddr->host)) {
                                $toEmail = strtolower($toAddr->mailbox . '@' . $toAddr->host);
                                break;
                            }
                        }
                    }
                    
                    // Also check CC and BCC if needed
                    if (!$toEmail && isset($header->cc) && is_array($header->cc)) {
                        foreach ($header->cc as $ccAddr) {
                            if (isset($ccAddr->mailbox) && isset($ccAddr->host)) {
                                $ccEmail = strtolower($ccAddr->mailbox . '@' . $ccAddr->host);
                                if ($ccEmail === strtolower($config->email_address)) {
                                    $toEmail = $ccEmail;
                                    break;
                                }
                            }
                        }
                    }
                    
                    // Verify email is sent to the configured email address (optional check)
                    // This ensures we only process emails intended for this incident reporting account
                    $configEmailLower = strtolower($config->email_address);
                    if ($toEmail && $toEmail !== $configEmailLower) {
                        // Email is not addressed to the configured account, but we'll still process it
                        // as it might be forwarded or the account might be in BCC
                        Log::info("Email from {$fromEmail} is not directly addressed to {$config->email_address}, but processing anyway (might be forwarded/BCC)");
                    }
                    
                    // Try to extract phone from body or use default
                    $phone = $this->extractPhoneFromEmail($body);
                    
                    // Parse body (remove email headers and signatures)
                    $cleanBody = $this->cleanEmailBody($body);
                    
                    // Determine priority from subject/keywords
                    $priority = $this->determinePriority($subject, $cleanBody);
                    
                    // Determine category
                    $category = $this->determineCategory($subject, $cleanBody);
                    
                    // Build incident data
                    $incidentData = [
                        'subject' => $subject,
                        'title' => $subject, // Also set title for consistency
                        'description' => $cleanBody ?: 'No description',
                        'priority' => ucfirst(strtolower($priority)),
                        'category' => $category,
                        'source' => 'email',
                        'email_message_id' => $messageId,
                        'email_thread_id' => $header->references ?? null,
                        'email_received_at' => isset($header->date) ? date('Y-m-d H:i:s', strtotime($header->date)) : now(),
                        'status' => 'New',
                    ];

                    // Add reporter fields based on column names
                    if (Schema::hasColumn('incidents', 'reporter_name')) {
                        $incidentData['reporter_name'] = $fromName;
                        $incidentData['reporter_email'] = $fromEmail;
                        if ($phone) {
                            $incidentData['reporter_phone'] = $phone;
                        }
                    } else {
                        $incidentData['reported_by_name'] = $fromName;
                        $incidentData['reported_by_email'] = $fromEmail;
                        if ($phone) {
                            $incidentData['reported_by_phone'] = $phone;
                        }
                    }
                    
                    // Create incident
                    try {
                        $incident = Incident::create($incidentData);
                        Log::info("Created incident {$incident->incident_no} from email: {$subject}");

                        // Handle attachments (with error handling)
                        try {
                            $this->saveAttachments($connection, $messageNumber, $incident);
                        } catch (\Exception $e) {
                            Log::warning('Error saving attachments for message ' . $messageNumber . ': ' . $e->getMessage());
                        }
                        
                        $syncedCount++;
                        
                        // Mark as read after successful import
                        @imap_setflag_full($connection, $messageNumber, "\\Seen");
                        
                    } catch (\Exception $e) {
                        Log::error('Error creating incident from email message ' . $messageNumber . ': ' . $e->getMessage());
                        Log::error('Incident data: ' . json_encode($incidentData));
                        // Don't mark as read if creation failed
                        continue;
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error processing email message ' . $messageNumber . ': ' . $e->getMessage());
                    Log::error('Stack trace: ' . $e->getTraceAsString());
                    continue;
                }
            }

            imap_close($connection);
            return $syncedCount;

        } catch (\Exception $e) {
            if ($connection) {
                imap_close($connection);
            }
            throw $e;
        }
    }

    /**
     * Sync emails from POP3
     */
    protected function syncPop3(IncidentEmailConfig $config, $dateFrom = null, $dateTo = null, $syncMode = 'all')
    {
        // POP3 implementation (similar to IMAP but using pop3 functions)
        // For now, we'll use IMAP functions as they work with POP3 servers too
        return $this->syncImap($config, $dateFrom, $dateTo, $syncMode);
    }

    /**
     * Build search criteria for email sync
     */
    protected function buildSearchCriteria(IncidentEmailConfig $config, $dateFrom = null, $dateTo = null, $syncMode = 'all')
    {
        $criteria = [];

        if ($syncMode === 'live') {
            // Live mode: sync emails received after last sync, including UNSEEN
            // Also include recent emails that might have been missed
            if ($config->last_sync_at) {
                // Get emails since last sync (subtract 1 hour to catch any missed emails)
                $lastSyncDate = clone $config->last_sync_at;
                $lastSync = $lastSyncDate->subHour()->format('d-M-Y');
                $criteria[] = "SINCE \"{$lastSync}\"";
            } else {
                // First sync: get last 7 days of emails
                $criteria[] = "SINCE \"" . date('d-M-Y', strtotime('-7 days')) . "\"";
            }
            // In live mode, prefer UNSEEN but also check recent emails
            // We'll filter duplicates by message_id later
            $criteria[] = 'UNSEEN';
        } elseif ($dateFrom && $dateTo) {
            // Date range mode
            $from = date('d-M-Y', strtotime($dateFrom));
            $to = date('d-M-Y', strtotime($dateTo));
            $criteria[] = "SINCE \"{$from}\"";
            $criteria[] = "BEFORE \"" . date('d-M-Y', strtotime($to . ' +1 day')) . "\"";
            $criteria[] = 'UNSEEN';
        } elseif ($dateFrom) {
            // From date only
            $from = date('d-M-Y', strtotime($dateFrom));
            $criteria[] = "SINCE \"{$from}\"";
            $criteria[] = 'UNSEEN';
        } elseif ($dateTo) {
            // To date only
            $to = date('d-M-Y', strtotime($dateTo . ' +1 day'));
            $criteria[] = "BEFORE \"{$to}\"";
            $criteria[] = 'UNSEEN';
        } else {
            // Default: get all unseen emails
            $criteria[] = 'UNSEEN';
        }

        return implode(' ', $criteria) ?: 'UNSEEN';
    }

    /**
     * Extract phone number from email body
     */
    protected function extractPhoneFromEmail($body)
    {
        // Try to find phone number patterns in email body
        $patterns = [
            '/phone[:\s]*([0-9+\s\-()]+)/i',
            '/mobile[:\s]*([0-9+\s\-()]+)/i',
            '/tel[:\s]*([0-9+\s\-()]+)/i',
            '/(255[0-9]{9})/',
            '/(0[67][0-9]{8})/',
            '/(\+255[0-9]{9})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $body, $matches)) {
                $phone = preg_replace('/[^0-9+]/', '', $matches[1]);
                // Format to Tanzania format
                if (strlen($phone) >= 9) {
                    $phone = ltrim($phone, '0');
                    if (!str_starts_with($phone, '255')) {
                        $phone = '255' . $phone;
                    }
                    if (strlen($phone) === 12) {
                        return $phone;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Clean email body from signatures and headers
     */
    protected function cleanEmailBody($body)
    {
        // Remove quoted text (replies)
        $body = preg_replace('/^>.*$/m', '', $body);
        
        // Remove common email signatures
        $body = preg_replace('/--\s*\n.*$/s', '', $body);
        $body = preg_replace('/Sent from.*$/mi', '', $body);
        
        // Clean up multiple spaces and newlines
        $body = preg_replace('/\n{3,}/', "\n\n", $body);
        $body = trim($body);
        
        return $body;
    }

    /**
     * Determine priority from email content
     */
    protected function determinePriority($subject, $body)
    {
        $text = strtolower($subject . ' ' . $body);
        
        if (preg_match('/\b(urgent|critical|emergency|asap|immediate)\b/i', $text)) {
            return 'critical';
        }
        if (preg_match('/\b(high|important|priority)\b/i', $text)) {
            return 'high';
        }
        if (preg_match('/\b(low|minor|not urgent)\b/i', $text)) {
            return 'low';
        }
        
        return 'medium';
    }

    /**
     * Determine category from email content
     */
    protected function determineCategory($subject, $body)
    {
        $text = strtolower($subject . ' ' . $body);
        
        if (preg_match('/\b(hr|human resources|employee|staff|leave|payroll)\b/i', $text)) {
            return 'hr';
        }
        if (preg_match('/\b(facilities|building|maintenance|office|equipment)\b/i', $text)) {
            return 'facilities';
        }
        if (preg_match('/\b(security|theft|access|alarm)\b/i', $text)) {
            return 'security';
        }
        if (preg_match('/\b(technical|it|system|software|hardware|network|server)\b/i', $text)) {
            return 'technical';
        }
        
        return 'other';
    }

    /**
     * Save email attachments
     */
    protected function saveAttachments($connection, $messageNumber, $incident)
    {
        $attachments = [];
        $structure = imap_fetchstructure($connection, $messageNumber);
        
        if (isset($structure->parts) && count($structure->parts)) {
            foreach ($structure->parts as $partNum => $part) {
                if (isset($part->disposition) && strtolower($part->disposition) === 'attachment') {
                    $attachment = imap_fetchbody($connection, $messageNumber, $partNum + 1);
                    $attachment = imap_base64($attachment);
                    
                    $filename = $part->dparameters[0]->value ?? 'attachment_' . time();
                    $filePath = 'incidents/' . $incident->id . '/' . $filename;
                    
                    Storage::put('public/' . $filePath, $attachment);
                    $attachments[] = $filePath;
                }
            }
        }
        
        if (!empty($attachments)) {
            $incident->update(['attachments' => $attachments]);
        }
    }
}




