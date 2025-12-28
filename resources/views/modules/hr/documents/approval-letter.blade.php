<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Approval Letter</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 20px;
            background-color: #ffffff;
            line-height: 1.8;
        }
        .letter {
            background: white;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        .letter-header {
            text-align: right;
            margin-bottom: 30px;
            border-bottom: 2px solid #940000;
            padding-bottom: 15px;
        }
        .letter-number {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .letter-date {
            font-size: 14px;
            color: #666;
        }
        .company-info {
            margin-bottom: 30px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #940000;
            margin-bottom: 10px;
        }
        .company-address {
            font-size: 12px;
            color: #666;
            line-height: 1.6;
        }
        .recipient-info {
            margin-bottom: 20px;
        }
        .recipient-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subject {
            font-weight: bold;
            margin: 20px 0;
            font-size: 16px;
            color: #940000;
        }
        .letter-body {
            text-align: justify;
            margin-bottom: 30px;
        }
        .letter-body p {
            margin-bottom: 15px;
        }
        .leave-details {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #940000;
        }
        .leave-details table {
            width: 100%;
            border-collapse: collapse;
        }
        .leave-details td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .leave-details td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 250px;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            height: 50px;
            margin-bottom: 10px;
        }
        .signature-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .signature-title {
            font-size: 12px;
            color: #666;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .date {
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $approvalDateFormatted = $leaveRequest->approval_date ? \Carbon\Carbon::parse($leaveRequest->approval_date)->format('F j, Y') : now()->format('F j, Y');
        $startDateFormatted = \Carbon\Carbon::parse($leaveRequest->start_date)->format('F j, Y');
        $endDateFormatted = \Carbon\Carbon::parse($leaveRequest->end_date)->format('F j, Y');
        $resumptionDate = \Carbon\Carbon::parse($leaveRequest->end_date)->addDay()->format('F j, Y');
    @endphp
    
    <div class="letter">
        <div class="letter-header">
            <div class="letter-number">Ref: {{ $leaveRequest->approval_letter_number ?? date('Ymd') . '-001' }}</div>
            <div class="letter-date">Date: {{ $approvalDateFormatted }}</div>
        </div>
        
        <div class="company-info">
            <div class="company-name">{{ $orgSettings->company_name ?? 'OFISILINK' }}</div>
            <div class="company-address">
                {{ $orgSettings->company_address ?? 'Dar es Salaam, Tanzania' }}<br>
                {{ $orgSettings->company_phone ?? '' }}<br>
                {{ $orgSettings->company_email ?? '' }}
            </div>
        </div>
        
        <div class="recipient-info">
            <div class="recipient-name">{{ $leaveRequest->employee->name }}</div>
            <div>{{ $leaveRequest->employee->employee->position ?? 'Employee' }}</div>
            <div>{{ $leaveRequest->employee->primaryDepartment->name ?? 'Department' }}</div>
            <div>{{ $orgSettings->company_name ?? 'OFISILINK' }}</div>
        </div>
        
        <div class="subject">
            SUBJECT: APPROVAL OF {{ strtoupper($leaveRequest->leaveType->name) }} LEAVE
        </div>
        
        <div class="letter-body">
            <p>Dear {{ $leaveRequest->employee->name }},</p>
            
            <p>Following your application for <strong>{{ $leaveRequest->leaveType->name }}</strong> leave, I am pleased to inform you that your request has been approved by the management.</p>
            
            <div class="leave-details">
                <table>
                    <tr>
                        <td>Leave Type:</td>
                        <td>{{ $leaveRequest->leaveType->name }}</td>
                    </tr>
                    <tr>
                        <td>Start Date:</td>
                        <td>{{ $startDateFormatted }}</td>
                    </tr>
                    <tr>
                        <td>End Date:</td>
                        <td>{{ $endDateFormatted }}</td>
                    </tr>
                    <tr>
                        <td>Total Days:</td>
                        <td>{{ $leaveRequest->total_days }} days</td>
                    </tr>
                    <tr>
                        <td>Location During Leave:</td>
                        <td>{{ $leaveRequest->leave_location }}</td>
                    </tr>
                    <tr>
                        <td>Expected Resumption Date:</td>
                        <td>{{ $resumptionDate }}</td>
                    </tr>
                    @if($leaveRequest->dependents && $leaveRequest->dependents->count() > 0)
                    <tr>
                        <td>Dependents:</td>
                        <td>
                            @foreach($leaveRequest->dependents as $dependent)
                                {{ $dependent->name }} ({{ $dependent->relationship }})@if(!$loop->last), @endif
                            @endforeach
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
            
            <p>You are hereby granted permission to proceed on leave as per the details above. Please ensure that all your pending work is properly handed over to your supervisor or designated colleague before your departure.</p>
            
            <p>You are expected to resume your duties on <span class="date">{{ $resumptionDate }}</span>. Should there be any circumstances that may prevent you from resuming on the scheduled date, please notify the Human Resources Department in advance.</p>
            
            @if($leaveRequest->fare_approved_amount > 0)
            <p>Your fare allowance has been approved and processed. The payment details will be communicated separately through the Finance Department.</p>
            @endif
            
            <p>We wish you a restful and enjoyable leave period.</p>
        </div>
        
        <div class="footer">
            <p>This is a system-generated document. For any queries, please contact the Human Resources Department.</p>
            <p>Document Reference: {{ $leaveRequest->approval_letter_number ?? 'N/A' }} | Generated on: {{ $documentDate }}</p>
        </div>
    </div>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>

