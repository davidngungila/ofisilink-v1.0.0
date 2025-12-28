<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class TestSMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms:test {phone?} {--message=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SMS sending functionality';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService)
    {
        $phone = $this->argument('phone') ?? $this->ask('Enter phone number (e.g., 255712345678)');
        $message = $this->option('message') ?? $this->ask('Enter message', 'Test message from OfisiLink');

        $this->info("Testing SMS sending...");
        $this->info("Phone: {$phone}");
        $this->info("Message: {$message}");
        $this->newLine();

        // Show configuration
        $this->info("Configuration:");
        $this->line("SMS_URL: " . env('SMS_URL', 'https://messaging-service.co.tz/api/sms/v1/test/text/single'));
        $this->line("SMS_FROM: " . env('SMS_FROM', 'N-SMS'));
        $this->line("SMS_USERNAME: " . env('SMS_USERNAME', 'im23n'));
        $this->newLine();

        // Test phone number formatting
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        if (!str_starts_with($cleaned, '255')) {
            $cleaned = '255' . ltrim($cleaned, '0');
        }
        $this->info("Formatted phone: {$cleaned}");

        if (!preg_match('/^255[0-9]{9}$/', $cleaned)) {
            $this->error("Invalid phone number format! Expected: 255XXXXXXXXX (12 digits)");
            return 1;
        }

        // Send SMS
        $this->info("Sending SMS...");
        $result = $notificationService->sendSMS($phone, $message);

        if ($result) {
            $this->info("✓ SMS sent successfully!");
            $this->line("Check logs for details: storage/logs/laravel.log");
            return 0;
        } else {
            $this->error("✗ SMS sending failed!");
            $this->line("Check logs for error details: storage/logs/laravel.log");
            $this->newLine();
            $this->line("Recent SMS log entries:");
            $this->line("Run: tail -f storage/logs/laravel.log | findstr SMS");
            $this->newLine();
            $this->warn("Current Error: HTTP 403 - Not Authorized");
            $this->line("This indicates the SMS API is rejecting the credentials.");
            $this->line("Please contact messaging-service.co.tz to verify account access.");
            return 1;
        }
    }
}

