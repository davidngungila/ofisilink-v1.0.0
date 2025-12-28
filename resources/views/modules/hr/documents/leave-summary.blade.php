<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Leave Request Summary - {{ $leaveRequest->employee->name }}</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        h1, h2, h3, h4 { color: #940000; margin: 0 0 10px 0; font-weight: bold; }
        h1 { font-size: 22pt; border-bottom: 2px solid #940000; padding-bottom: 10px; margin-bottom: 25px; text-align: center; }
        h2 { font-size: 16pt; background-color: #fceeee; padding: 12px; margin-top: 25px; border-left: 4px solid #940000; }
        h3 { font-size: 13pt; border-bottom: 1px solid #f0d0d0; padding-bottom: 5px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 8px; text-align: left; vertical-align: top; }
        .bordered-table th, .bordered-table td { border: 1px solid #ddd; }
        .bordered-table th { background-color: #f9f9f9; font-weight: bold; }
        .summary-box { background-color: #fff9f9; border: 1px solid #f0d0d0; padding: 20px; margin-bottom: 25px; border-radius: 5px; }
        .summary-table td { padding: 10px; border: 1px solid #f0d0d0; }
        .summary-table td.label { font-weight: bold; color: #940000; width: 30%; background-color: #fceeee; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 10px; color: white; font-weight: bold; font-size: 9pt; }
        .timeline-item { border-left: 2px solid #940000; padding-left: 15px; margin-bottom: 15px; }
        .timeline-date { font-weight: bold; color: #940000; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'LEAVE-' . $leaveRequest->id . '-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'LEAVE REQUEST SUMMARY',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])
    
    <h1>Leave Request Summary</h1>
    
    <div class="summary-box">
        <h2>Employee Information</h2>
        <table class="summary-table" style="width: 100%;">
            <tr>
                <td class="label">Employee Name:</td>
                <td>{{ $leaveRequest->employee->name ?? 'N/A' }}</td>
                <td class="label">Employee ID:</td>
                <td>{{ $leaveRequest->employee->employee->employee_number ?? $leaveRequest->employee->employee_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Department:</td>
                <td>{{ $leaveRequest->employee->primaryDepartment->name ?? 'N/A' }}</td>
                <td class="label">Position:</td>
                <td>{{ $leaveRequest->employee->employee->position ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td colspan="3">{{ $leaveRequest->employee->email ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
    
    <div class="summary-box">
        <h2>Leave Details</h2>
        <table class="summary-table" style="width: 100%;">
            <tr>
                <td class="label">Leave Type:</td>
                <td>{{ $leaveRequest->leaveType->name ?? 'N/A' }}</td>
                <td class="label">Status:</td>
                <td>
                    @php
                        $statusColors = [
                            'pending_hr_review' => '#ffc107',
                            'pending_hod_approval' => '#17a2b8',
                            'pending_ceo_approval' => '#007bff',
                            'approved_pending_docs' => '#28a745',
                            'on_leave' => '#28a745',
                            'completed' => '#343a40',
                            'rejected' => '#dc3545',
                            'rejected_for_edit' => '#fd7e14',
                            'cancelled' => '#6c757d'
                        ];
                        $statusText = [
                            'pending_hr_review' => 'Pending HR Review',
                            'pending_hod_approval' => 'Pending HOD Approval',
                            'pending_ceo_approval' => 'Pending CEO Approval',
                            'approved_pending_docs' => 'Approved - Pending Docs',
                            'on_leave' => 'On Leave',
                            'completed' => 'Completed',
                            'rejected' => 'Rejected',
                            'rejected_for_edit' => 'Rejected - For Edit',
                            'cancelled' => 'Cancelled'
                        ];
                    $color = $statusColors[$leaveRequest->status] ?? '#6c757d';
                        $text = $statusText[$leaveRequest->status] ?? $leaveRequest->status;
                    @endphp
                    <span class="status-badge" style="background-color: {{ $color }};">{{ $text }}</span>
                </td>
            </tr>
            <tr>
                <td class="label">Start Date:</td>
                <td>{{ $leaveRequest->start_date ? (\Carbon\Carbon::parse($leaveRequest->start_date)->format('F j, Y')) : 'N/A' }}</td>
                <td class="label">End Date:</td>
                <td>{{ $leaveRequest->end_date ? (\Carbon\Carbon::parse($leaveRequest->end_date)->format('F j, Y')) : 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Total Days:</td>
                <td><strong>{{ $leaveRequest->total_days ?? 0 }}</strong></td>
                <td class="label">Location:</td>
                <td>{{ $leaveRequest->leave_location ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Reason:</td>
                <td colspan="3">{{ $leaveRequest->reason ?? 'No reason provided' }}</td>
            </tr>
        </table>
    </div>
    
    @if($leaveRequest->dependents && $leaveRequest->dependents->count() > 0)
    <div class="summary-box">
        <h2>Dependents</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Relationship</th>
                    <th>Fare Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaveRequest->dependents as $dependent)
                <tr>
                    <td>{{ $dependent->name ?? 'N/A' }}</td>
                    <td>{{ $dependent->relationship ?? 'N/A' }}</td>
                    <td>{{ number_format(floatval($dependent->fare_amount ?? 0), 2) }} TZS</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($leaveRequest->total_fare_approved && $leaveRequest->total_fare_approved > 0)
        <p style="text-align: right; margin-top: 10px;"><strong>Total Fare Approved: {{ number_format(floatval($leaveRequest->total_fare_approved), 2) }} TZS</strong></p>
        @endif
    </div>
    @endif
    
    @if($leaveRequest->approval_letter_number || $leaveRequest->leave_certificate_number)
    <div class="summary-box">
        <h2>Document Information</h2>
        <table class="summary-table">
            @if($leaveRequest->approval_letter_number)
            <tr>
                <td class="label">Approval Letter No:</td>
                <td>{{ $leaveRequest->approval_letter_number }}</td>
                @if($leaveRequest->approval_date)
                <td class="label">Approval Date:</td>
                <td>{{ \Carbon\Carbon::parse($leaveRequest->approval_date)->format('F j, Y') }}</td>
                @endif
            </tr>
            @endif
            @if($leaveRequest->leave_certificate_number)
            <tr>
                <td class="label">Leave Certificate No:</td>
                <td>{{ $leaveRequest->leave_certificate_number }}</td>
            </tr>
            @endif
            @if($leaveRequest->fare_certificate_number)
            <tr>
                <td class="label">Fare Certificate No:</td>
                <td>{{ $leaveRequest->fare_certificate_number }}</td>
            </tr>
            @endif
            @if($leaveRequest->payment_voucher_number)
            <tr>
                <td class="label">Payment Voucher No:</td>
                <td>{{ $leaveRequest->payment_voucher_number }}</td>
                @if($leaveRequest->payment_date)
                <td class="label">Payment Date:</td>
                <td>{{ \Carbon\Carbon::parse($leaveRequest->payment_date)->format('F j, Y') }}</td>
                @endif
            </tr>
            @endif
        </table>
    </div>
    @endif
    
    @if($leaveRequest->comments || $leaveRequest->hr_officer_comments)
    <div class="summary-box">
        <h2>Comments & Notes</h2>
        @if($leaveRequest->hr_officer_comments)
        <h4>HR Officer Comments:</h4>
        <p style="padding: 10px; background-color: #f8f9fa; border-left: 3px solid #940000;">{{ $leaveRequest->hr_officer_comments }}</p>
        @endif
        @if($leaveRequest->comments)
        <h4>Additional Comments:</h4>
        <p style="padding: 10px; background-color: #f8f9fa; border-left: 3px solid #940000;">{{ $leaveRequest->comments }}</p>
        @endif
    </div>
    @endif
    
    @if($leaveRequest->actual_return_date)
    <div class="summary-box">
        <h2>Return Information</h2>
        <table class="summary-table">
            <tr>
                <td class="label">Actual Return Date:</td>
                <td>{{ $leaveRequest->actual_return_date ? (\Carbon\Carbon::parse($leaveRequest->actual_return_date)->format('F j, Y')) : 'N/A' }}</td>
                <td class="label">Health Status:</td>
                <td>{{ ucfirst($leaveRequest->health_status ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td class="label">Work Readiness:</td>
                <td colspan="3">{{ ucfirst(str_replace('_', ' ', $leaveRequest->work_readiness ?? 'N/A')) }}</td>
            </tr>
            @if($leaveRequest->return_comments)
            <tr>
                <td class="label">Return Comments:</td>
                <td colspan="3">{{ $leaveRequest->return_comments }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif
    
    <div class="summary-box">
        <h2>Processing Timeline</h2>
        <div class="timeline-item">
            <span class="timeline-date">{{ $leaveRequest->created_at ? \Carbon\Carbon::parse($leaveRequest->created_at)->format('F j, Y, h:i A') : 'N/A' }}</span>
            <p>Leave request submitted by {{ $leaveRequest->employee->name ?? 'Employee' }}</p>
        </div>
        @if($leaveRequest->reviewed_at)
        <div class="timeline-item">
            <span class="timeline-date">{{ \Carbon\Carbon::parse($leaveRequest->reviewed_at)->format('F j, Y, h:i A') }}</span>
            <p>Reviewed by {{ $leaveRequest->reviewer ? $leaveRequest->reviewer->name : 'N/A' }}</p>
        </div>
        @endif
        @if($leaveRequest->documents_processed_at)
        <div class="timeline-item">
            <span class="timeline-date">{{ \Carbon\Carbon::parse($leaveRequest->documents_processed_at)->format('F j, Y, h:i A') }}</span>
            <p>Documents processed by {{ $leaveRequest->documentProcessor ? $leaveRequest->documentProcessor->name : 'N/A' }}</p>
        </div>
        @endif
        @if($leaveRequest->return_submitted_at)
        <div class="timeline-item">
            <span class="timeline-date">{{ \Carbon\Carbon::parse($leaveRequest->return_submitted_at)->format('F j, Y, h:i A') }}</span>
            <p>Return form submitted</p>
        </div>
        @endif
    </div>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
