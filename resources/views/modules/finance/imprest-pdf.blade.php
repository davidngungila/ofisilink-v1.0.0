<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Imprest Request Report - {{ $imprestRequest->request_no }}</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #333; 
            font-size: 11px; 
            line-height: 1.4;
        }
        .status-box { 
            padding: 12px; 
            text-align: center; 
            font-size: 16px; 
            font-weight: bold; 
            margin-bottom: 20px; 
            border-radius: 5px; 
            text-transform: capitalize; 
        }
        .status-approved, .status-assigned { 
            background-color: #e4f8e9; 
            color: #28a745; 
            border: 1px solid #28a745; 
        }
        .status-paid { 
            background-color: #e7f3ff; 
            color: #007bff; 
            border: 1px solid #007bff; 
        }
        .status-completed { 
            background-color: #e4f8e9; 
            color: #28a745; 
            border: 1px solid #28a745; 
        }
        .status-pending_hod, .status-pending_ceo, .status-pending_receipt_verification { 
            background-color: #fff8e6; 
            color: #ffc107; 
            border: 1px solid #ffc107; 
        }
        .section-title { 
            background-color: #940000; 
            color: #fff; 
            padding: 10px; 
            font-size: 14px; 
            margin-top: 20px; 
            margin-bottom: 0px; 
            font-weight: bold;
        }
        .content-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 0;
            margin-bottom: 15px;
        }
        .content-table th, .content-table td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: left; 
        }
        .content-table th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            color: #555; 
        }
        .timeline { 
            border-left: 2px solid #940000; 
            padding-left: 20px; 
            margin-top: 10px; 
        }
        .timeline-item { 
            position: relative; 
            padding-bottom: 15px; 
        }
        .timeline-item::before { 
            content: ''; 
            position: absolute; 
            left: -26.5px; 
            top: 3px; 
            width: 10px; 
            height: 10px; 
            background-color: #fff; 
            border: 2px solid #940000; 
            border-radius: 50%; 
        }
        .timeline-item strong { 
            display: block; 
            font-size: 12px; 
            color: #333;
            margin-bottom: 3px;
        }
        .timeline-item .meta { 
            font-size: 10px; 
            color: #777; 
        }
        .timeline-item .comment { 
            background-color: #f9f9f9; 
            border-left: 3px solid #ddd; 
            padding: 8px; 
            margin-top: 5px; 
            font-style: italic; 
            font-size: 10px;
        }
        .priority-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .priority-urgent {
            background-color: #fdecec;
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        .priority-high {
            background-color: #fff8e6;
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        .priority-normal {
            background-color: #e7f3ff;
            color: #007bff;
            border: 1px solid #007bff;
        }
        .receipts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .receipts-table th,
        .receipts-table td {
            border: 1px solid #ccc;
            padding: 6px;
            font-size: 10px;
        }
        .receipts-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = $imprestRequest->request_no ?? 'IMPREST-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'IMPREST REQUEST REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <div class="status-box status-{{ $imprestRequest->status }}">
        Status: {{ ucwords(str_replace('_', ' ', $imprestRequest->status)) }}
    </div>

    <h3 class="section-title">Request Details</h3>
    <table class="content-table">
        <tr>
            <th style="width: 25%;">Request Number</th>
            <td style="width: 25%;">{{ $imprestRequest->request_no }}</td>
            <th style="width: 25%;">Accountant</th>
            <td style="width: 25%;">{{ $imprestRequest->accountant->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <th>Purpose</th>
            <td colspan="3">{{ $imprestRequest->purpose }}</td>
        </tr>
        <tr>
            <th>Amount</th>
            <td><strong>TZS {{ number_format($imprestRequest->amount, 2) }}</strong></td>
            <th>Priority</th>
            <td>
                <span class="priority-badge priority-{{ $imprestRequest->priority }}">
                    {{ ucfirst($imprestRequest->priority) }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Created Date</th>
            <td>{{ $imprestRequest->created_at->format('M j, Y, g:i A') }}</td>
            <th>Expected Return Date</th>
            <td>{{ $imprestRequest->expected_return_date ? $imprestRequest->expected_return_date->format('M j, Y') : 'N/A' }}</td>
        </tr>
        @if($imprestRequest->description)
        <tr>
            <th>Description</th>
            <td colspan="3">{!! nl2br(e($imprestRequest->description)) !!}</td>
        </tr>
        @endif
    </table>

    @if($imprestRequest->assignments->count() > 0)
    <h3 class="section-title">Staff Assignments</h3>
    <table class="content-table">
        <thead>
            <tr>
                <th>Staff Member</th>
                <th>Department</th>
                <th class="text-right">Assigned Amount</th>
                <th>Assignment Date</th>
                <th>Receipt Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($imprestRequest->assignments as $assignment)
            <tr>
                <td>{{ $assignment->staff->name ?? 'N/A' }}</td>
                <td>{{ $assignment->staff->primaryDepartment->name ?? 'N/A' }}</td>
                <td class="text-right"><strong>TZS {{ number_format($assignment->assigned_amount, 2) }}</strong></td>
                <td>{{ $assignment->assigned_at ? $assignment->assigned_at->format('M j, Y') : 'N/A' }}</td>
                <td class="text-center">
                    @if($assignment->receipt_submitted)
                        <span style="color: #28a745; font-weight: bold;">Submitted</span>
                        @if($assignment->receipts->where('is_verified', true)->count() > 0)
                            <br><small style="color: #28a745;">Verified</small>
                        @else
                            <br><small style="color: #ffc107;">Pending Verification</small>
                        @endif
                    @else
                        <span style="color: #ffc107; font-weight: bold;">Pending</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if($imprestRequest->receipts->count() > 0)
    <h3 class="section-title">Submitted Receipts</h3>
    <table class="receipts-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Amount</th>
                <th>Submitted Date</th>
                <th>Submitted By</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($imprestRequest->receipts as $receipt)
            <tr>
                <td>{{ $receipt->receipt_description }}</td>
                <td class="text-right">TZS {{ number_format($receipt->receipt_amount, 2) }}</td>
                <td>{{ $receipt->submitted_at ? $receipt->submitted_at->format('M j, Y, g:i A') : 'N/A' }}</td>
                <td>{{ $receipt->submittedBy->name ?? 'N/A' }}</td>
                <td class="text-center">
                    @if($receipt->is_verified)
                        <span style="color: #28a745; font-weight: bold;">Verified</span>
                        @if($receipt->verifiedBy)
                            <br><small>{{ $receipt->verifiedBy->name }}</small>
                        @endif
                    @else
                        <span style="color: #ffc107; font-weight: bold;">Pending</span>
                    @endif
                </td>
            </tr>
            @if($receipt->verification_notes)
            <tr>
                <td colspan="5" style="font-size: 9px; font-style: italic; color: #777; padding-left: 20px;">
                    Verification Notes: {{ $receipt->verification_notes }}
                </td>
            </tr>
            @endif
            @endforeach
        </tbody>
    </table>
    @endif

    @if($imprestRequest->paid_at)
    <h3 class="section-title">Payment Information</h3>
    <table class="content-table">
        <tr>
            <th style="width: 25%;">Payment Date</th>
            <td>{{ $imprestRequest->paid_at->format('M j, Y, g:i A') }}</td>
            <th style="width: 25%;">Payment Method</th>
            <td>{{ $imprestRequest->payment_method ? ucwords(str_replace('_', ' ', $imprestRequest->payment_method)) : 'N/A' }}</td>
        </tr>
        @if($imprestRequest->payment_reference)
        <tr>
            <th>Payment Reference</th>
            <td colspan="3"><strong>{{ $imprestRequest->payment_reference }}</strong></td>
        </tr>
        @endif
        @if($imprestRequest->payment_notes)
        <tr>
            <th>Payment Notes</th>
            <td colspan="3">{!! nl2br(e($imprestRequest->payment_notes)) !!}</td>
        </tr>
        @endif
    </table>
    @endif

    <h3 class="section-title">Request Timeline</h3>
    <div class="timeline">
        <div class="timeline-item">
            <strong>Request Created</strong>
            <div class="meta">{{ $imprestRequest->created_at->format('M j, Y, g:i A') }} by {{ $imprestRequest->accountant->name ?? 'Accountant' }}</div>
        </div>

        @if($imprestRequest->hod_approved_at)
        <div class="timeline-item">
            <strong>HOD Approved</strong>
            <div class="meta">{{ $imprestRequest->hod_approved_at->format('M j, Y, g:i A') }}</div>
            @if($imprestRequest->hodApproval)
                <div class="meta">Approved by {{ $imprestRequest->hodApproval->name }}</div>
            @endif
        </div>
        @endif

        @if($imprestRequest->ceo_approved_at)
        <div class="timeline-item">
            <strong>CEO Approved</strong>
            <div class="meta">{{ $imprestRequest->ceo_approved_at->format('M j, Y, g:i A') }}</div>
            @if($imprestRequest->ceoApproval)
                <div class="meta">Final approval by {{ $imprestRequest->ceoApproval->name }}</div>
            @endif
        </div>
        @endif

        @if($imprestRequest->assignments->count() > 0)
        <div class="timeline-item">
            <strong>Staff Assigned</strong>
            <div class="meta">{{ $imprestRequest->assignments->first()->assigned_at->format('M j, Y') ?? 'N/A' }}</div>
            <div class="meta">{{ $imprestRequest->assignments->count() }} staff member(s) assigned</div>
        </div>
        @endif

        @if($imprestRequest->paid_at)
        <div class="timeline-item">
            <strong>Payment Processed</strong>
            <div class="meta">{{ $imprestRequest->paid_at->format('M j, Y, g:i A') }}</div>
            @if($imprestRequest->payment_method)
                <div class="meta">Payment method: {{ ucwords(str_replace('_', ' ', $imprestRequest->payment_method)) }}</div>
            @endif
        </div>
        @endif

        @if($imprestRequest->completed_at)
        <div class="timeline-item">
            <strong>Request Completed</strong>
            <div class="meta">{{ $imprestRequest->completed_at->format('M j, Y, g:i A') }}</div>
            <div class="meta">All receipts submitted and verified</div>
        </div>
        @endif
    </div>

    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
