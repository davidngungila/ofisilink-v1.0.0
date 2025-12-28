# Email Configuration Guide - PHPMailer Setup

This guide will help you configure professional email sending using PHPMailer in your OfisiLink system.

## Installation

First, install PHPMailer using Composer:

```bash
cd ofisi
composer update phpmailer/phpmailer
```

Or if you have SSL issues, manually add to `composer.json` and run:

```bash
composer install --no-interaction
```

## Configuration

### 1. Update `.env` File

Add or update the following settings in your `.env` file:

```env
# Email Configuration (PHPMailer)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=davidngungila@gmail.com
MAIL_PASSWORD=vlxcdpwaizofnti
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=davidngungila@gmail.com
MAIL_FROM_NAME="OfisiLink System"
MAIL_DEBUG=0
```

### 2. Important Notes

- **App Password**: For Gmail, use an App Password (not your regular password)
  - Go to: Google Account → Security → 2-Step Verification → App passwords
  - Generate a new app password and use it (remove spaces)
  - The password `vlxcdpwaizofnti` is the app password without spaces

- **Security**: 
  - Never commit `.env` file to version control
  - Keep your app password secure
  - If compromised, revoke and generate a new app password

### 3. Clear Configuration Cache

After updating `.env`, run:

```bash
php artisan config:clear
php artisan cache:clear
```

## Testing Email Configuration

### Test Email Command

Use the built-in test command:

```bash
php artisan email:test davidngungila@gmail.com
```

Or test with any email:

```bash
php artisan email:test your-email@example.com
```

### Manual Testing

You can also test programmatically:

```php
use App\Services\EmailService;

$emailService = new EmailService();
$result = $emailService->testConfiguration('davidngungila@gmail.com');

if ($result['success']) {
    echo "Email sent successfully!";
} else {
    echo "Error: " . $result['error'];
}
```

## Using EmailService

### Basic Usage

```php
use App\Services\EmailService;

$emailService = new EmailService();

// Send simple email
$emailService->send(
    'recipient@example.com',
    'Subject',
    '<h1>HTML Email Body</h1>'
);

// Send email with attachment
$emailService->send(
    'recipient@example.com',
    'Subject',
    '<h1>Email with Attachment</h1>',
    '/path/to/file.sql',
    'backup.sql'
);

// Send to multiple recipients
$recipients = ['admin1@example.com', 'admin2@example.com'];
$emailService->sendToMultiple(
    $recipients,
    'Subject',
    '<h1>Email Body</h1>',
    '/path/to/file.sql',
    'backup.sql'
);
```

### Dynamic Configuration

You can update configuration dynamically:

```php
$emailService = new EmailService();
$emailService->updateConfig([
    'host' => 'smtp.other.com',
    'port' => 465,
    'username' => 'user@example.com',
    'password' => 'password',
    'encryption' => 'ssl'
]);
```

## Troubleshooting

### Common Issues

1. **SSL Certificate Error**
   - The EmailService is configured to skip SSL verification for compatibility
   - For production, consider using proper SSL certificates

2. **Authentication Failed**
   - Verify your Gmail app password is correct
   - Ensure 2-factor authentication is enabled
   - Check that "Less secure app access" is enabled (or use App Passwords)

3. **Connection Timeout**
   - Check firewall settings
   - Verify port 587 (TLS) or 465 (SSL) is not blocked
   - Try different ports: 587 for TLS, 465 for SSL

4. **Emails Not Received**
   - Check spam/junk folder
   - Verify recipient email address
   - Check Laravel logs: `storage/logs/laravel.log`
   - Enable debug mode: Set `MAIL_DEBUG=2` in `.env`

### Debug Mode

Enable verbose debugging:

```env
MAIL_DEBUG=2
```

This will show detailed SMTP communication in logs.

### Check Logs

View email sending logs:

```bash
tail -f storage/logs/laravel.log | grep -i email
```

## Features

- ✅ Professional PHPMailer library
- ✅ Easy configuration via `.env`
- ✅ Support for attachments
- ✅ Multiple recipients
- ✅ HTML email support
- ✅ Automatic plain text fallback
- ✅ Comprehensive error logging
- ✅ Test command for verification

## Backup Email Notifications

The backup system automatically uses EmailService to send:
- Success notifications with SQL file attachments
- Failure notifications with error details
- To all System Administrators
- To davidngungila@gmail.com

All emails are sent using the professional PHPMailer library for reliable delivery.










