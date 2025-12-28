<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fare Certificate</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .certificate {
            background: white;
            padding: 40px;
            border: 3px solid #940000;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 120px;
            color: rgba(148, 0, 0, 0.1);
            font-weight: bold;
            z-index: 1;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #940000;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #940000;
            margin-bottom: 10px;
        }
        .certificate-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .certificate-number {
            font-size: 14px;
            color: #666;
        }
        .content {
            position: relative;
            z-index: 2;
            line-height: 1.8;
        }
        .employee-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #940000;
        }
        .fare-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
        }
        .amount-section {
            margin: 20px 0;
            padding: 20px;
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            text-align: center;
        }
        .amount-number {
            font-size: 24px;
            font-weight: bold;
            color: #940000;
        }
        .amount-words {
            font-size: 18px;
            font-style: italic;
            color: #333;
            margin-top: 10px;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .date {
            font-weight: bold;
        }
        .dependent-list {
            margin: 10px 0;
        }
        .dependent-item {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = $leaveRequest->fare_certificate_number ?? 'FC-' . $leaveRequest->id . '-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'FARE CERTIFICATE',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])
    
    <div class="certificate">
        <div class="watermark">OFISILINK</div>
        
        <div class="header" style="border: none; padding: 0; margin-bottom: 20px;">
            <div class="certificate-title">FARE CERTIFICATE</div>
            <div class="certificate-number">Certificate No: {{ $leaveRequest->fare_certificate_number ?? 'FC/' . date('Y') . '/' . str_pad($leaveRequest->id, 4, '0', STR_PAD_LEFT) }}</div>
        </div>

        <div class="content">
            <p>This is to certify that <strong>{{ $leaveRequest->employee->name }}</strong> (Employee ID: {{ $leaveRequest->employee->employee_id ?? 'N/A' }}) 
            has been granted fare allowance for <strong>{{ $leaveRequest->leaveType->name }}</strong> for the period from 
            <span class="date">{{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('F j, Y') }}</span> to 
            <span class="date">{{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('F j, Y') }}</span>.</p>

            <div class="employee-info">
                <h4>Employee Information:</h4>
                <p><strong>Name:</strong> {{ $leaveRequest->employee->name }}</p>
                <p><strong>Department:</strong> {{ $leaveRequest->employee->primaryDepartment->name ?? 'N/A' }}</p>
                <p><strong>Position:</strong> {{ $leaveRequest->employee->employee->position ?? 'N/A' }}</p>
                <p><strong>Employee ID:</strong> {{ $leaveRequest->employee->employee_id ?? 'N/A' }}</p>
            </div>

            <div class="fare-details">
                <h4>Fare Details:</h4>
                <p><strong>Leave Type:</strong> {{ $leaveRequest->leaveType->name }}</p>
                <p><strong>Duration:</strong> {{ $leaveRequest->total_days }} days</p>
                <p><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($leaveRequest->start_date)->format('F j, Y') }}</p>
                <p><strong>End Date:</strong> {{ \Carbon\Carbon::parse($leaveRequest->end_date)->format('F j, Y') }}</p>
                <p><strong>Destination:</strong> {{ $leaveRequest->leave_location }}</p>
                <p><strong>Payment Voucher:</strong> {{ $leaveRequest->payment_voucher_number ?? 'N/A' }}</p>
                <p><strong>Payment Date:</strong> {{ $paymentDate }}</p>
            </div>

            @if($leaveRequest->dependents && $leaveRequest->dependents->count() > 0)
            <div class="fare-details">
                <h4>Dependents:</h4>
                <div class="dependent-list">
                    @foreach($leaveRequest->dependents as $dependent)
                    <div class="dependent-item">
                        <strong>{{ $dependent->name }}</strong> ({{ $dependent->relationship }})
                        @if($dependent->fare_amount > 0)
                        - {{ number_format($dependent->fare_amount) }} TZS
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="amount-section">
                <h4>Total Approved Fare Amount</h4>
                <div class="amount-number">{{ $amountFormatted }} TZS</div>
                <div class="amount-words">{{ $amountWords }}</div>
            </div>

            <p>This certificate is issued on <span class="date">{{ $paymentDate }}</span> and the fare amount has been processed 
            through payment voucher <strong>{{ $leaveRequest->payment_voucher_number ?? 'N/A' }}</strong>.</p>

            <div class="mt-4 text-center">
                <p class="text-muted mb-0"><small>This is a system-generated document. No signatures required.</small></p>
                <p class="text-muted mb-0"><small>Generated on: {{ $paymentDate }}</small></p>
            </div>
        </div>

    </div>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
