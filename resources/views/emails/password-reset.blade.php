<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'Password Reset - OfisiLink' }}</title>
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
        .credentials-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px solid #940000;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }
        .credentials-box h3 {
            font-size: 16px;
            color: #940000;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }
        .credential-item {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
        }
        .credential-item:last-child {
            border-bottom: none;
        }
        .credential-label {
            font-weight: 600;
            color: #333333;
            min-width: 100px;
            font-size: 14px;
        }
        .credential-value {
            color: #555555;
            flex: 1;
            font-size: 15px;
        }
        .password-box {
            background-color: #fff3cd;
            border: 2px dashed #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .password-box .label {
            font-size: 12px;
            color: #856404;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .password-box .password {
            font-size: 28px;
            font-weight: 700;
            color: #856404;
            font-family: 'Courier New', monospace;
            letter-spacing: 3px;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 6px;
            margin: 10px 0;
        }
        .warning-box {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .warning-box h3 {
            color: #856404;
            margin-bottom: 12px;
            font-weight: 600;
            font-size: 16px;
        }
        .warning-box ul {
            margin: 0;
            padding-left: 20px;
            color: #856404;
        }
        .warning-box li {
            margin-bottom: 8px;
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
            .password-box .password {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <h1>OfisiLink</h1>
            <div class="subtitle">Password Reset Notification</div>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Dear {{ $userName ?? 'Valued User' }},
            </div>

            <div class="message">
                @if(isset($resetBy) && $resetBy === 'admin')
                    Your password has been reset by a System Administrator.
                @else
                    Your password has been reset successfully.
                @endif
                Your new login credentials are provided below.
            </div>

            <!-- Credentials Box -->
            <div class="credentials-box">
                <h3>Your New Login Credentials</h3>
                
                <div class="credential-item">
                    <span class="credential-label">Email:</span>
                    <span class="credential-value">{{ $userEmail ?? 'N/A' }}</span>
                </div>
                
                <div class="password-box">
                    <div class="label">Your New Password</div>
                    <div class="password">{{ $newPassword ?? 'N/A' }}</div>
                </div>
            </div>

            <!-- Warning Box -->
            <div class="warning-box">
                <h3>⚠️ Important Security Instructions</h3>
                <ul>
                    <li><strong>Login immediately</strong> using the credentials above</li>
                    <li><strong>Change your password</strong> to a secure password of your choice after logging in</li>
                    <li><strong>Do not share</strong> your password with anyone, including OfisiLink support staff</li>
                    <li><strong>Keep your password secure</strong> and never write it down in an insecure location</li>
                    @if(isset($resetBy) && $resetBy === 'admin')
                    <li><strong>This password was reset by an administrator</strong> - please change it immediately for security</li>
                    @endif
                </ul>
            </div>

            <div class="button-container">
                <a href="{{ $loginUrl ?? route('login') }}" class="btn-primary">Login to Your Account</a>
            </div>

            <div class="message" style="margin-top: 25px; font-size: 14px; color: #666666;">
                <p>If you did not request this password reset, please contact your system administrator immediately to secure your account.</p>
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










