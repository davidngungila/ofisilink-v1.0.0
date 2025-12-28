<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'OTP Verification - OfisiLink' }}</title>
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
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            margin-bottom: 30px;
            line-height: 1.8;
        }
        .otp-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px dashed #940000;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-label {
            font-size: 14px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 700;
            color: #940000;
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            text-shadow: 0 2px 4px rgba(148, 0, 0, 0.1);
        }
        .otp-validity {
            font-size: 14px;
            color: #666666;
            margin-top: 15px;
        }
        .otp-validity strong {
            color: #940000;
        }
        .info-box {
            background-color: #fff9e6;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 0;
            font-size: 14px;
            color: #856404;
            line-height: 1.6;
        }
        .security-note {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .security-note h3 {
            font-size: 16px;
            color: #940000;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .security-note ul {
            margin: 0;
            padding-left: 20px;
            color: #555555;
            font-size: 14px;
        }
        .security-note li {
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
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #b30000 0%, #cc0000 100%);
            box-shadow: 0 6px 12px rgba(148, 0, 0, 0.3);
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
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #dee2e6, transparent);
            margin: 25px 0;
        }
        @media only screen and (max-width: 600px) {
            .email-body {
                padding: 30px 20px;
            }
            .otp-code {
                font-size: 36px;
                letter-spacing: 6px;
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
            <div class="subtitle">Secure Authentication System</div>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Hello {{ $userName ?? 'Valued User' }},
            </div>

            <div class="message">
                @if(isset($purpose) && $purpose === 'password_reset')
                    You have requested to reset your password. Use the OTP code below to verify your identity and proceed with password reset.
                @else
                    You have requested a One-Time Password (OTP) for secure login to your OfisiLink account. Use the code below to complete your authentication.
                @endif
            </div>

            <!-- OTP Code Display -->
            <div class="otp-container">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{{ $otpCode ?? '000000' }}</div>
                <div class="otp-validity">
                    Valid for <strong>{{ $validityMinutes ?? 10 }} minutes</strong>
                </div>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <p>
                    <strong>⚠️ Important:</strong> This code will expire in {{ $validityMinutes ?? 10 }} minutes. 
                    Do not share this code with anyone. OfisiLink staff will never ask for your OTP.
                </p>
            </div>

            <!-- Security Note -->
            <div class="security-note">
                <h3>🔒 Security Tips</h3>
                <ul>
                    <li>Never share your OTP with anyone, including OfisiLink support staff</li>
                    <li>This code is valid for one-time use only</li>
                    <li>If you didn't request this code, please secure your account immediately</li>
                    <li>Always verify the sender's email address before entering any code</li>
                </ul>
            </div>

            @if(isset($loginUrl))
            <div class="button-container">
                <a href="{{ $loginUrl }}" class="btn-primary">Go to Login Page</a>
            </div>
            @endif

            <div class="divider"></div>

            <div style="font-size: 14px; color: #666666; line-height: 1.8;">
                <p><strong>Need Help?</strong></p>
                <p>If you're having trouble with the OTP or didn't request this code, please contact your system administrator immediately.</p>
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










