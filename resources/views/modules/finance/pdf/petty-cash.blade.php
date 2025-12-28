<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Petty Cash Voucher - {{ $pettyCash->voucher_no ?? 'N/A' }}</title>
    <style>
        @page { 
            margin: 20px 30px 70px 30px;
        }
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #333; 
            font-size: 12px; 
            line-height: 1.4; 
        }
        
        .payslip-period { 
            background-color: #940000; 
            color: white; 
            padding: 8px; 
            text-align: center; 
            font-weight: bold; 
            margin: 15px 0; 
        }
        
        .section { margin-bottom: 20px; }
        .section-title { 
            background-color: #f5f5f5; 
            padding: 8px 12px; 
            font-weight: bold; 
            border-left: 4px solid #940000; 
            margin-bottom: 10px; 
        }
        
        .info-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
        }
        .info-table th, .info-table td { 
            padding: 8px 12px; 
            text-align: left; 
            vertical-align: top; 
            border: 1px solid #ddd;
        }
        .info-table th { 
            background-color: #f9f9f9; 
            font-weight: bold; 
            width: 25%; 
        }
        
        .salary-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0; 
        }
        .salary-table th, .salary-table td { 
            padding: 10px 12px; 
            border: 1px solid #ddd; 
        }
        .salary-table th { 
            background-color: #940000; 
            color: white; 
            font-weight: bold; 
            text-align: left; 
        }
        .salary-table .amount { 
            text-align: right; 
            font-family: 'Courier New', monospace; 
        }
        .salary-table .subtotal { 
            background-color: #f9f9f9; 
            font-weight: bold; 
        }
        .salary-table .total { 
            background-color: #e8f4fd; 
            font-weight: bold; 
            font-size: 14px; 
        }
        
        .net-salary { 
            background: linear-gradient(135deg, #940000, #b30000); 
            color: white; 
            padding: 20px; 
            text-align: center; 
            border-radius: 8px; 
            margin: 20px 0; 
        }
        .net-salary-amount { 
            font-size: 28px; 
            font-weight: bold; 
            margin: 10px 0; 
            font-family: 'Courier New', monospace; 
        }
        .net-salary-words { 
            font-style: italic; 
            margin-top: 10px; 
            font-size: 11px; 
        }
        
        .summary-breakdown { 
            background-color: #f8f9fa; 
            padding: 15px; 
            border-radius: 5px; 
            margin: 15px 0; 
        }
        .breakdown-row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 8px; 
            padding-bottom: 5px; 
            border-bottom: 1px dashed #ddd; 
        }
        .breakdown-label { 
            font-weight: 600; 
        }
        .breakdown-value { 
            font-family: 'Courier New', monospace; 
        }
        
        .workflow-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .workflow-table th, .workflow-table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
        }
        .workflow-table th {
            background-color: #940000;
            color: white;
            font-weight: bold;
        }
        .workflow-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .status-icon {
            text-align: center;
            font-weight: bold;
            font-size: 14pt;
        }
        .status-icon.completed::before {
            content: "✓";
            color: #28a745;
        }
        .status-icon.pending::before {
            content: "⏳";
            color: #ffc107;
        }
        
        .comment-box {
            background-color: #f8f9fa;
            padding: 12px 15px;
            border-left: 4px solid #940000;
            margin: 12px 0;
            border-radius: 4px;
        }
        .comment-label {
            font-weight: bold;
            color: #940000;
            margin-bottom: 6px;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mb-3 { margin-bottom: 15px; }
        .color-primary { color: #940000; }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 10px;
        }
        .status-badge.pending { background-color: #ffc107; color: #000; }
        .status-badge.approved { background-color: #28a745; color: white; }
        .status-badge.completed { background-color: #6c757d; color: white; }
        .status-badge.rejected { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = $pettyCash->voucher_no ?? 'N/A';
        
        // Format currency helper
        $formatCurrency = function($amount) {
            return 'TZS ' . number_format((float)$amount, 2, '.', ',');
        };
        
        // Use PayrollPdfService for number to words conversion
        $convertNumberToWords = function($number) {
            return \App\Services\PayrollPdfService::convertNumberToWords((int)$number);
        };
        
        // Get status badge class
        $getStatusClass = function($status) {
            $status = strtolower($status);
            if (str_contains($status, 'pending')) return 'pending';
            if (str_contains($status, 'approved') || str_contains($status, 'paid')) return 'approved';
            if (str_contains($status, 'retired') || str_contains($status, 'completed')) return 'completed';
            if (str_contains($status, 'rejected')) return 'rejected';
            return 'default';
        };
        
        // Load relationships if not loaded
        if (!$pettyCash->relationLoaded('lines')) {
            $pettyCash->load(['lines', 'creator', 'accountant', 'hod', 'ceo', 'paidBy']);
        }
        
        $voucherNo = htmlspecialchars($pettyCash->voucher_no);
        $date = $pettyCash->date ? \Carbon\Carbon::parse($pettyCash->date)->format('l, F d, Y') : 'N/A';
        $payee = htmlspecialchars($pettyCash->payee);
        $purpose = htmlspecialchars($pettyCash->purpose);
        $amount = (float)$pettyCash->amount;
        $status = ucwords(str_replace(['_', '-'], ' ', $pettyCash->status));
        $statusClass = $getStatusClass($pettyCash->status);
        
        $creatorName = $pettyCash->creator ? htmlspecialchars($pettyCash->creator->name) : 'N/A';
        $createdAt = $pettyCash->created_at ? $pettyCash->created_at->setTimezone($timezone)->format('d M Y, H:i') : 'N/A';
        
        $accountantName = $pettyCash->accountant ? htmlspecialchars($pettyCash->accountant->name) : 'Pending';
        $accountantDate = $pettyCash->accountant_verified_at ? $pettyCash->accountant_verified_at->setTimezone($timezone)->format('d M Y, H:i') : 'Not Verified';
        $accountantComments = $pettyCash->accountant_comments ? htmlspecialchars($pettyCash->accountant_comments) : '';
        
        $hodName = $pettyCash->hod ? htmlspecialchars($pettyCash->hod->name) : 'Pending';
        $hodDate = $pettyCash->hod_approved_at ? $pettyCash->hod_approved_at->setTimezone($timezone)->format('d M Y, H:i') : 'Not Approved';
        $hodComments = $pettyCash->hod_comments ? htmlspecialchars($pettyCash->hod_comments) : '';
        
        $ceoName = $pettyCash->ceo ? htmlspecialchars($pettyCash->ceo->name) : 'Pending';
        $ceoDate = $pettyCash->ceo_approved_at ? $pettyCash->ceo_approved_at->setTimezone($timezone)->format('d M Y, H:i') : 'Not Approved';
        $ceoComments = $pettyCash->ceo_comments ? htmlspecialchars($pettyCash->ceo_comments) : '';
        
        $paidByName = $pettyCash->paidBy ? htmlspecialchars($pettyCash->paidBy->name) : 'Not Paid';
        $paidDate = $pettyCash->paid_at ? $pettyCash->paid_at->setTimezone($timezone)->format('d M Y, H:i') : '';
        $paymentMethod = $pettyCash->payment_method ? htmlspecialchars($pettyCash->payment_method) : 'N/A';
        $paymentReference = $pettyCash->payment_reference ? htmlspecialchars($pettyCash->payment_reference) : 'N/A';
        
        $retiredDate = $pettyCash->retired_at ? $pettyCash->retired_at->setTimezone($timezone)->format('d M Y, H:i') : '';
        $retirementComments = $pettyCash->retirement_comments ? htmlspecialchars($pettyCash->retirement_comments) : '';
        
        $attachmentCount = $pettyCash->attachments ? count($pettyCash->attachments) : 0;
        $retirementCount = $pettyCash->retirement_receipts ? count($pettyCash->retirement_receipts) : 0;
        $progressPercentage = $pettyCash->progress_percentage ?? 0;
        
        $amountInWords = $convertNumberToWords((int)$amount) . ' Tanzanian Shillings Only';
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'PETTY CASH VOUCHER',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <div class="payslip-period">
        VOUCHER NO: {{ $voucherNo }} | DATE: {{ $date }} | STATUS: <span class="status-badge {{ $statusClass }}">{{ $status }}</span>
    </div>

    <div class="section">
        <div class="section-title">REQUEST INFORMATION</div>
        <table class="info-table">
            <tr>
                <th>Voucher Number</th>
                <td><strong>{{ $voucherNo }}</strong></td>
                <th>Request Date</th>
                <td>{{ $date }}</td>
            </tr>
            <tr>
                <th>Payee</th>
                <td>{{ $payee }}</td>
                <th>Requested By</th>
                <td>{{ $creatorName }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span class="status-badge {{ $statusClass }}">{{ $status }}</span></td>
                <th>Created On</th>
                <td>{{ $createdAt }}</td>
            </tr>
            <tr>
                <th>Purpose</th>
                <td colspan="3">{{ nl2br($purpose) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">EXPENSE BREAKDOWN</div>
        <table class="salary-table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th>Description</th>
                    <th width="12%" class="text-center">Quantity</th>
                    <th width="20%" class="text-right">Unit Price (TZS)</th>
                    <th width="20%" class="text-right">Total (TZS)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $lineNumber = 1;
                    $totalQty = 0;
                @endphp
                @foreach($pettyCash->lines as $line)
                    @php
                        $totalQty += (float)$line->qty;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $lineNumber++ }}</td>
                        <td>{{ htmlspecialchars($line->description) }}</td>
                        <td class="text-center">{{ number_format((float)$line->qty, 2) }}</td>
                        <td class="amount">{{ $formatCurrency($line->unit_price) }}</td>
                        <td class="amount">{{ $formatCurrency($line->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total">
                    <td colspan="2" class="text-right text-bold">TOTAL AMOUNT</td>
                    <td class="text-center text-bold">{{ number_format($totalQty, 2) }}</td>
                    <td colspan="2" class="amount text-bold">{{ $formatCurrency($amount) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="net-salary">
        <div>REQUESTED AMOUNT</div>
        <div class="net-salary-amount">{{ $formatCurrency($amount) }}</div>
        <div class="net-salary-words">{{ $amountInWords }}</div>
    </div>

    <div class="section">
        <div class="section-title">WORKFLOW & APPROVAL CHAIN</div>
        <table class="workflow-table">
            <thead>
                <tr>
                    <th width="8%">Status</th>
                    <th width="25%">Stage</th>
                    <th width="32%">Approver</th>
                    <th width="35%">Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="status-icon completed"></td>
                    <td><strong>Request Submitted</strong></td>
                    <td>{{ $creatorName }}</td>
                    <td>{{ $createdAt }}</td>
                </tr>
                <tr>
                    <td class="status-icon {{ $pettyCash->accountant_verified_at ? 'completed' : (in_array($pettyCash->status, ['pending_accountant']) ? 'pending' : '') }}"></td>
                    <td><strong>Accountant Verification</strong></td>
                    <td>{{ $accountantName }}</td>
                    <td>{{ $accountantDate }}</td>
                </tr>
                <tr>
                    <td class="status-icon {{ $pettyCash->hod_approved_at ? 'completed' : (in_array($pettyCash->status, ['pending_hod', 'pending_ceo', 'approved_for_payment', 'paid', 'pending_retirement_review', 'retired']) ? 'pending' : '') }}"></td>
                    <td><strong>HOD Approval</strong></td>
                    <td>{{ $hodName }}</td>
                    <td>{{ $hodDate }}</td>
                </tr>
                <tr>
                    <td class="status-icon {{ $pettyCash->ceo_approved_at ? 'completed' : (in_array($pettyCash->status, ['pending_ceo', 'approved_for_payment', 'paid', 'pending_retirement_review', 'retired']) ? 'pending' : '') }}"></td>
                    <td><strong>CEO Approval</strong></td>
                    <td>{{ $ceoName }}</td>
                    <td>{{ $ceoDate }}</td>
                </tr>
                <tr>
                    <td class="status-icon {{ $pettyCash->paid_at ? 'completed' : (in_array($pettyCash->status, ['approved_for_payment', 'paid', 'pending_retirement_review', 'retired']) ? 'pending' : '') }}"></td>
                    <td><strong>Payment Processed</strong></td>
                    <td>{{ $paidByName }}</td>
                    <td>{{ $paidDate ?: 'Not Paid' }}</td>
                </tr>
                <tr>
                    <td class="status-icon {{ $pettyCash->retired_at ? 'completed' : (in_array($pettyCash->status, ['pending_retirement_review', 'retired']) ? 'pending' : '') }}"></td>
                    <td><strong>Retirement Completed</strong></td>
                    <td>{{ $pettyCash->accountant ? htmlspecialchars($pettyCash->accountant->name) : 'Accountant' }}</td>
                    <td>{{ $retiredDate ?: 'Not Retired' }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($accountantComments || $hodComments || $ceoComments || $retirementComments)
    <div class="section">
        <div class="section-title">COMMENTS & NOTES</div>
        @if($accountantComments)
        <div class="comment-box">
            <div class="comment-label">Accountant Comments:</div>
            <div>{{ nl2br($accountantComments) }}</div>
        </div>
        @endif
        @if($hodComments)
        <div class="comment-box">
            <div class="comment-label">HOD Comments:</div>
            <div>{{ nl2br($hodComments) }}</div>
        </div>
        @endif
        @if($ceoComments)
        <div class="comment-box">
            <div class="comment-label">CEO Comments:</div>
            <div>{{ nl2br($ceoComments) }}</div>
        </div>
        @endif
        @if($retirementComments)
        <div class="comment-box">
            <div class="comment-label">Retirement Notes:</div>
            <div>{{ nl2br($retirementComments) }}</div>
        </div>
        @endif
    </div>
    @endif

    <div class="summary-breakdown">
        <div class="breakdown-row">
            <span class="breakdown-label">Supporting Documents:</span>
            <span class="breakdown-value">{{ $attachmentCount }} file(s)</span>
        </div>
        <div class="breakdown-row">
            <span class="breakdown-label">Retirement Receipts:</span>
            <span class="breakdown-value">{{ $retirementCount }} file(s)</span>
        </div>
        <div class="breakdown-row">
            <span class="breakdown-label">Progress:</span>
            <span class="breakdown-value">{{ $progressPercentage }}% Complete</span>
        </div>
        <div class="breakdown-row">
            <span class="breakdown-label">Payment Method:</span>
            <span class="breakdown-value">{{ $paymentMethod }}</span>
        </div>
        <div class="breakdown-row">
            <span class="breakdown-label">Payment Reference:</span>
            <span class="breakdown-value">{{ $paymentReference }}</span>
        </div>
    </div>

    @include('components.pdf-disclaimer')

    <!-- Visible Footer Section -->
    <div style="margin-top: 40px; padding-top: 15px; border-top: 2px solid #e9ecef; page-break-inside: avoid;">
        <div style="text-align: center; font-size: 9px; color: #6c757d; line-height: 1.6;">
            <p style="margin: 5px 0; font-weight: bold; color: #940000;">CONFIDENTIAL DOCUMENT - PETTY CASH VOUCHER</p>
            <p style="margin: 5px 0;">
                This document was automatically generated by {{ config('app.name', 'OfisiLink') }} System.
                No manual signature is required.
            </p>
            <p style="margin: 5px 0;">
                Generated on: {{ now()->setTimezone($orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam'))->format('d M Y, H:i:s') }} ({{ $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam') }})
            </p>
            <p style="margin: 5px 0;">
                Voucher ID: {{ $pettyCash->id }} | Status: {{ $status }}
            </p>
            <p style="margin: 5px 0; color: #940000;">
                <strong>OfisiLink - Powered by EmCa Technologies</strong>
            </p>
        </div>
    </div>

    @include('components.pdf-footer')
</body>
</html>

