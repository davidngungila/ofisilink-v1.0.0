<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'OfisiLink Notification' }}</title>
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
            white-space: pre-wrap;
        }
        .message-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-left: 4px solid #940000;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .info-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .info-section h3 {
            font-size: 16px;
            color: #940000;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .info-item {
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-item strong {
            color: #333333;
            display: inline-block;
            min-width: 140px;
        }
        .info-item span {
            color: #555555;
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
            <div class="subtitle">System Notification</div>
        </div>

        <!-- Body -->
        <div class="email-body">
            @php
                // Extract user information from data
                $staffName = $data['staff_name'] ?? $data['creator_name'] ?? $data['user_name'] ?? $data['employee_name'] ?? null;
                $requestId = $data['request_id'] ?? $data['voucher_no'] ?? $data['request_no'] ?? $data['reference_number'] ?? null;
                $amount = $data['amount'] ?? $data['requested_amount'] ?? $data['total_amount'] ?? null;
                $purpose = $data['purpose'] ?? $data['reason'] ?? $data['description'] ?? null;
                $documentAttached = $data['document_attached'] ?? $data['has_attachment'] ?? $data['attachment'] ?? false;
                $documentName = $data['document_name'] ?? $data['attachment_name'] ?? null;
                $status = $data['status'] ?? null;
                $comments = $data['comments'] ?? $data['remarks'] ?? null;
                $requestDate = $data['request_date'] ?? $data['created_at'] ?? $data['submitted_at'] ?? null;
                $department = $data['department'] ?? $data['department_name'] ?? null;
                $employeeId = $data['employee_id'] ?? $data['staff_id'] ?? null;
            @endphp

            @if($staffName)
                <div class="greeting">
                    Dear {{ $staffName }},
                </div>
            @endif

            <div class="message-box">
                {!! nl2br(e($emailMessage ?? $message ?? 'No message provided')) !!}
            </div>

            @if($requestId || $amount || $purpose || $status)
                <div class="info-section">
                    <h3>Request Details</h3>
                    
                    @if($requestId)
                        <div class="info-item">
                            <strong>Reference Number:</strong>
                            <span style="color: #940000; font-weight: 600; font-size: 16px;">{{ $requestId }}</span>
                        </div>
                    @endif

                    @if($staffName && !isset($data['staff_name']))
                        <div class="info-item">
                            <strong>Staff Name:</strong>
                            <span>{{ $staffName }}</span>
                        </div>
                    @endif

                    @if($employeeId)
                        <div class="info-item">
                            <strong>Employee ID:</strong>
                            <span>{{ $employeeId }}</span>
                        </div>
                    @endif

                    @if($department)
                        <div class="info-item">
                            <strong>Department:</strong>
                            <span>{{ $department }}</span>
                        </div>
                    @endif

                    @if($amount)
                        <div class="info-item">
                            <strong>Requested Amount:</strong>
                            <span style="color: #940000; font-weight: 600; font-size: 18px;">
                                TZS {{ is_numeric($amount) ? number_format($amount, 2) : $amount }}
                            </span>
                        </div>
                    @endif

                    @if($purpose)
                        <div class="info-item" style="flex-direction: column; align-items: flex-start;">
                            <strong style="margin-bottom: 8px;">Purpose/Reason:</strong>
                            <span style="white-space: pre-wrap; line-height: 1.8;">{{ $purpose }}</span>
                        </div>
                    @endif

                    @if($status)
                        <div class="info-item">
                            <strong>Status:</strong>
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; text-transform: uppercase;
                                @if(strtolower($status) == 'approved' || strtolower($status) == 'approved_for_payment')
                                    background-color: #d4edda; color: #155724;
                                @elseif(strtolower($status) == 'rejected' || strtolower($status) == 'declined')
                                    background-color: #f8d7da; color: #721c24;
                                @elseif(strtolower($status) == 'pending' || strtolower($status) == 'pending_approval')
                                    background-color: #fff3cd; color: #856404;
                                @else
                                    background-color: #d1ecf1; color: #0c5460;
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </div>
                    @endif

                    @if($requestDate)
                        <div class="info-item">
                            <strong>Request Date:</strong>
                            <span>{{ is_string($requestDate) ? $requestDate : (is_object($requestDate) && method_exists($requestDate, 'format') ? $requestDate->format('M j, Y g:i A') : $requestDate) }}</span>
                        </div>
                    @endif

                    @if($comments)
                        <div class="info-item" style="flex-direction: column; align-items: flex-start;">
                            <strong style="margin-bottom: 8px;">Comments/Remarks:</strong>
                            <span style="white-space: pre-wrap; line-height: 1.8; color: #555555;">{{ $comments }}</span>
                        </div>
                    @endif

                    @if($documentAttached || $documentName)
                        <div class="info-item" style="background-color: #e7f3ff; padding: 15px; border-radius: 6px; margin-top: 10px;">
                            <strong style="color: #004085;">📎 Document Information:</strong>
                            <div style="margin-top: 8px;">
                                @if($documentAttached === true || $documentAttached === 'true' || $documentAttached === 1)
                                    <span style="color: #28a745; font-weight: 600;">✓ Document is attached to this email</span>
                                @elseif($documentName)
                                    <span style="color: #555555;">Document: <strong>{{ $documentName }}</strong></span>
                                @else
                                    <span style="color: #555555;">Document has been attached to this request</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if(isset($data) && !empty($data))
                @php
                    $excludedKeys = ['is_html', 'skip_sms', 'skip_email', 'type', 'purpose', 'otp_code', 'staff_name', 'creator_name', 'user_name', 'employee_name', 'request_id', 'voucher_no', 'request_no', 'reference_number', 'amount', 'requested_amount', 'total_amount', 'purpose', 'reason', 'description', 'document_attached', 'has_attachment', 'attachment', 'document_name', 'attachment_name', 'status', 'comments', 'remarks', 'request_date', 'created_at', 'submitted_at', 'department', 'department_name', 'employee_id', 'staff_id'];
                    $additionalData = array_filter($data, function($key) use ($excludedKeys) {
                        return !in_array($key, $excludedKeys);
                    }, ARRAY_FILTER_USE_KEY);
                @endphp

                @if(!empty($additionalData))
                    <div class="info-section">
                        <h3>Additional Information</h3>
                        @foreach($additionalData as $key => $value)
                            <div class="info-item">
                                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                <span>{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT) : (is_bool($value) ? ($value ? 'Yes' : 'No') : $value) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

            @if(isset($link) && $link)
                <div class="button-container">
                    <a href="{{ $link }}" class="btn-primary">View Full Details</a>
                </div>
            @endif

            <div class="divider"></div>

            <div style="font-size: 14px; color: #666666; line-height: 1.8; background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
                <p style="margin-bottom: 10px;"><strong>Next Steps:</strong></p>
                <ul style="margin: 0; padding-left: 20px; color: #555555;">
                    @if($status && (strtolower($status) == 'pending' || strtolower($status) == 'pending_approval'))
                        <li>Your request is currently under review</li>
                        <li>You will be notified once a decision has been made</li>
                        <li>Please check your notifications regularly for updates</li>
                    @elseif($status && (strtolower($status) == 'approved' || strtolower($status) == 'approved_for_payment'))
                        <li>Your request has been approved</li>
                        <li>Please proceed with the next steps as instructed</li>
                        <li>If payment is involved, expect processing within the standard timeframe</li>
                    @elseif($status && (strtolower($status) == 'rejected' || strtolower($status) == 'declined'))
                        <li>Your request has been reviewed</li>
                        <li>Please check the comments/remarks for details</li>
                        <li>You may submit a new request if needed</li>
                    @else
                        <li>This is an automated notification from OfisiLink System</li>
                        <li>Please review the details above carefully</li>
                        <li>Contact your administrator if you have any questions</li>
                    @endif
                </ul>
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
