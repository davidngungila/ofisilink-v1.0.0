<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Backup Failed - OfisiLink</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #940000 0%, #b30000 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .email-header .subtitle {
            font-size: 14px;
            margin-top: 8px;
            opacity: 0.95;
        }
        .email-body {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .message {
            font-size: 16px;
            color: #555555;
            margin-bottom: 25px;
            line-height: 1.8;
        }
        .error-box {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 2px solid #dc3545;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .error-box .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .error-box .message {
            font-size: 18px;
            font-weight: 600;
            color: #721c24;
            margin: 0;
        }
        .error-details {
            background-color: #ffffff;
            border-left: 4px solid #dc3545;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .error-details h3 {
            color: #dc3545;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .error-details .error-message {
            font-size: 16px;
            font-weight: 600;
            color: #721c24;
            margin-bottom: 10px;
        }
        .error-details .error-time {
            font-size: 14px;
            color: #666666;
        }
        .troubleshooting {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .troubleshooting h3 {
            font-size: 16px;
            color: #940000;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .troubleshooting ul {
            margin: 0;
            padding-left: 20px;
            color: #555555;
        }
        .troubleshooting li {
            margin-bottom: 10px;
            line-height: 1.6;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .btn-primary {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #940000 0%, #b30000 100%);
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(148, 0, 0, 0.2);
        }
        .footer {
            background-color: #f8f9fa;
            padding: 25px 30px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #666666;
            line-height: 1.6;
        }
        .footer .company-name {
            color: #940000;
            font-weight: 600;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            .email-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>OfisiLink</h1>
            <div class="subtitle">System Backup Notification</div>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Dear {{ $admin->name ?? 'Administrator' }},
            </div>

            <div class="error-box">
                <div class="icon">⚠️</div>
                <div class="message">Database Backup Failed</div>
            </div>

            <div class="message">
                The database backup for OfisiLink System has <strong>FAILED</strong>. Please investigate this issue immediately to ensure regular backups are functioning properly.
            </div>

            <div class="error-details">
                <h3>Error Details</h3>
                <div class="error-message">
                    {{ $error_message ?? 'Unknown error occurred' }}
                </div>
                <div class="error-time">
                    <strong>Failed At:</strong> {{ $failed_at ?? now()->format('M j, Y g:i A') }}
                </div>
            </div>

            <div class="troubleshooting">
                <h3>🔧 Troubleshooting Steps</h3>
                <ul>
                    <li>Check server logs for more detailed error information</li>
                    <li>Verify database connection is working properly</li>
                    <li>Ensure sufficient disk space is available on the server</li>
                    <li>Check if mysqldump is installed and accessible (or Laravel DB connection is working)</li>
                    <li>Verify file permissions for the backup directory</li>
                    <li>Try running a manual backup from the System Status page</li>
                    <li>Check if the database server is running and accessible</li>
                </ul>
            </div>

            <div class="message" style="margin-top: 25px;">
                <strong>Action Required:</strong> Please investigate this issue immediately to ensure regular backups are functioning properly. Regular backups are critical for data protection and disaster recovery.
            </div>

            <div class="button-container">
                <a href="{{ route('admin.system.status') ?? '#' }}" class="btn-primary">Go to System Status</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="company-name">OfisiLink System</p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p style="margin-top: 10px; font-size: 12px; color: #999999;">
                © {{ date('Y') }} OfisiLink. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
