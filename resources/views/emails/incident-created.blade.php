<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Incident Registered - OfisiLink</title>
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
        .incident-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 2px solid #940000;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }
        .incident-box .reference-number {
            text-align: center;
            margin-bottom: 20px;
        }
        .incident-box .reference-number .label {
            font-size: 12px;
            color: #666666;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .incident-box .reference-number .number {
            font-size: 32px;
            font-weight: 700;
            color: #940000;
            letter-spacing: 2px;
            font-family: 'Courier New', monospace;
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
        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .priority-high {
            background-color: #f8d7da;
            color: #721c24;
        }
        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }
        .priority-low {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .description-box {
            background-color: #ffffff;
            border-left: 4px solid #940000;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
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
            .incident-box .reference-number .number {
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
            <div class="subtitle">Incident Management System</div>
        </div>

        <!-- Body -->
        <div class="email-body">
            <div class="greeting">
                Dear {{ $incident->reporter_name ?? 'Valued Customer' }},
            </div>

            <div class="message">
                We have received your issue and reported it under the reference number below. We apologize for any inconvenience caused.
            </div>

            <!-- Incident Reference Box -->
            <div class="incident-box">
                <div class="reference-number">
                    <div class="label">Reference Number</div>
                    <div class="number">{{ $incident->incident_no ?? $incident->incident_code ?? 'N/A' }}</div>
                </div>

                <div class="info-section">
                    <h3>Incident Details</h3>
                    <div class="info-item">
                        <strong>Title:</strong>
                        <span>{{ $incident->title ?? $incident->subject ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Priority:</strong>
                        <span>
                            <span class="priority-badge priority-{{ strtolower($incident->priority ?? 'medium') }}">
                                {{ ucfirst($incident->priority ?? 'Medium') }}
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>Status:</strong>
                        <span>{{ ucfirst(str_replace('_', ' ', $incident->status ?? 'New')) }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Category:</strong>
                        <span>{{ ucfirst($incident->category ?? 'Technical') }}</span>
                    </div>
                    <div class="info-item">
                        <strong>Registered:</strong>
                        <span>{{ $incident->created_at->format('M j, Y g:i A') }}</span>
                    </div>
                    @if($incident->assignedTo)
                    <div class="info-item">
                        <strong>Assigned To:</strong>
                        <span>{{ $incident->assignedTo->name }}</span>
                    </div>
                    @endif
                </div>

                @if($incident->description)
                <div class="description-box">
                    <strong style="color: #940000; display: block; margin-bottom: 10px;">Description:</strong>
                    <div style="color: #555555; white-space: pre-wrap;">{{ $incident->description }}</div>
                </div>
                @endif
            </div>

            <div class="message" style="margin-top: 25px;">
                Our support team will review your incident and take appropriate action. You will be notified of any updates regarding this incident.
            </div>

            <div class="button-container">
                <a href="{{ route('modules.incidents.show', $incident->id) ?? '#' }}" class="btn-primary">View Incident Details</a>
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
