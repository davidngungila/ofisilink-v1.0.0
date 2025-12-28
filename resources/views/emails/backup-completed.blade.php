<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Backup Completed - OfisiLink</title>
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
        .success-box {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border: 2px solid #28a745;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
            text-align: center;
        }
        .success-box .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .success-box .message {
            font-size: 18px;
            font-weight: 600;
            color: #155724;
            margin: 0;
        }
        .info-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-section h3 {
            font-size: 16px;
            color: #940000;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .info-item {
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: flex-start;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-item strong {
            color: #333333;
            display: inline-block;
            min-width: 140px;
            font-weight: 600;
        }
        .info-item span {
            color: #555555;
            flex: 1;
        }
        .info-item a {
            color: #940000;
            text-decoration: none;
            word-break: break-all;
        }
        .info-item a:hover {
            text-decoration: underline;
        }
        .password-box {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 2px solid #ffc107;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }
        .password-box h3 {
            color: #856404;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 16px;
        }
        .password-box .password {
            font-size: 24px;
            font-weight: 700;
            color: #856404;
            font-family: 'Courier New', monospace;
            text-align: center;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 6px;
            margin: 15px 0;
            letter-spacing: 2px;
        }
        .password-box .note {
            font-size: 13px;
            color: #856404;
            margin-top: 10px;
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

            <div class="success-box">
                <div class="icon">✅</div>
                <div class="message">Database Backup Completed Successfully</div>
            </div>

            <div class="message">
                The database backup for OfisiLink System has been completed successfully. The backup file is attached to this email.
            </div>

            <div class="info-section">
                <h3>Backup Details</h3>
                <div class="info-item">
                    <strong>File Name:</strong>
                    <span>{{ $backup_file ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <strong>File Size:</strong>
                    <span>{{ $file_size ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <strong>Completed At:</strong>
                    <span>{{ $completed_at ?? now()->format('M j, Y g:i A') }}</span>
                </div>
                @if(isset($download_url))
                <div class="info-item">
                    <strong>Download URL:</strong>
                    <span><a href="{{ $download_url }}">{{ $download_url }}</a></span>
                </div>
                @endif
            </div>

            @if(isset($password))
            <div class="password-box">
                <h3>⚠️ Important: Database Password</h3>
                <div class="password">{{ $password }}</div>
                <div class="note">
                    This is a normal SQL file. The password is for database access when restoring. The password is case-sensitive. Please store this backup securely and do not share it with unauthorized personnel.
                </div>
            </div>
            @endif

            <div class="message" style="margin-top: 25px;">
                <strong>Security Note:</strong> This backup contains sensitive data. Please store it securely and do not share it with unauthorized personnel.
            </div>

            @if(isset($download_url))
            <div class="button-container">
                <a href="{{ $download_url }}" class="btn-primary">Download Backup File</a>
            </div>
            @endif
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
