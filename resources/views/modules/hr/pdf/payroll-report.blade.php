<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Report - {{ $payroll->pay_period ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            color: #333;
            line-height: 1.4;
        }
        .container {
            max-width: 297mm;
            margin: 0 auto;
            padding: 0;
            background: #fff;
        }
        .summary-cards {
            display: flex;
            justify-content: space-around;
            margin-bottom: 15px;
            gap: 10px;
        }
        .summary-card {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #f8f9fa;
            text-align: center;
        }
        .summary-card h3 {
            font-size: 10px;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .summary-card .amount {
            font-size: 16px;
            font-weight: bold;
            color: #059669;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8px;
        }
        table th, table td {
            padding: 5px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background: #2563eb;
            color: #fff;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }
        table td {
            font-size: 8px;
        }
        .amount-col {
            text-align: right;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .section-title {
            background: #2563eb;
            color: #fff;
            padding: 6px;
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 8px;
        }
        .totals-row {
            background: #e8f4f8 !important;
            font-weight: bold;
        }
        .footer-totals {
            background: #d1fae5;
            padding: 10px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .footer-totals table {
            margin: 0;
        }
        .footer-totals table td {
            border: none;
            padding: 4px 8px;
        }
        .page-break {
            page-break-after: always;
        }
        .department-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .info-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 9px;
        }
        @media print {
            .container {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'PAYROLL-' . ($payroll->id ?? '') . '-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'PAYROLL REPORT - ' . strtoupper($payroll->pay_period ?? 'N/A'),
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])
    
    <div class="container">

        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card">
                <h3>Total Employees</h3>
                <div class="amount">{{ $items->count() }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Gross Salary</h3>
                <div class="amount">{{ number_format($totals['gross_salary'] ?? 0, 0) }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Deductions</h3>
                <div class="amount" style="color: #dc2626;">{{ number_format($totals['total_deductions'] ?? 0, 0) }}</div>
            </div>
            <div class="summary-card">
                <h3>Total Net Pay</h3>
                <div class="amount">{{ number_format($totals['net_salary'] ?? 0, 0) }}</div>
            </div>
            <div class="summary-card">
                <h3>Employer Cost</h3>
                <div class="amount" style="color: #f59e0b;">{{ number_format($totals['total_employer_cost'] ?? 0, 0) }}</div>
            </div>
        </div>

        <!-- Payroll Information -->
        <div class="info-box">
            <div>
                <strong>Pay Period:</strong> {{ $payroll->pay_period ?? 'N/A' }}<br>
                <strong>Pay Date:</strong> {{ $payroll->pay_date ? \Carbon\Carbon::parse($payroll->pay_date)->format('F j, Y') : 'N/A' }}<br>
                <strong>Status:</strong> {{ ucfirst($payroll->status ?? 'N/A') }}
            </div>
            <div>
                @if($processor)
                <strong>Processed By:</strong> {{ $processor->name ?? 'N/A' }}<br>
                @endif
                @if($reviewer)
                <strong>Reviewed By:</strong> {{ $reviewer->name ?? 'N/A' }}<br>
                @endif
                @if($approver)
                <strong>Approved By:</strong> {{ $approver->name ?? 'N/A' }}<br>
                @endif
                @if($payer)
                <strong>Paid By:</strong> {{ $payer->name ?? 'N/A' }}
                @endif
            </div>
        </div>

        <!-- Employee Details Table -->
        <div class="section-title">EMPLOYEE PAYROLL DETAILS</div>
        <table>
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="12%">Employee</th>
                    <th width="10%">Department</th>
                    <th width="8%" class="amount-col">Basic</th>
                    <th width="8%" class="amount-col">Overtime</th>
                    <th width="8%" class="amount-col">Bonus</th>
                    <th width="8%" class="amount-col">Allowance</th>
                    <th width="8%" class="amount-col">Gross</th>
                    <th width="6%" class="amount-col">PAYE</th>
                    <th width="6%" class="amount-col">NSSF</th>
                    <th width="6%" class="amount-col">NHIF</th>
                    <th width="6%" class="amount-col">HESLB</th>
                    <th width="6%" class="amount-col">Ded.</th>
                    <th width="7%" class="amount-col">Net</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $index => $item)
                @php
                    $employee = $item->employee;
                    $employeeGross = $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $employee->name ?? 'N/A' }}</strong><br>
                        <small>{{ $employee->employee_id ?? 'N/A' }}</small>
                    </td>
                    <td>{{ $employee->primaryDepartment->name ?? 'N/A' }}</td>
                    <td class="amount-col">{{ number_format($item->basic_salary ?? 0, 0) }}</td>
                    <td class="amount-col">{{ number_format($item->overtime_amount ?? 0, 0) }}</td>
                    <td class="amount-col">{{ number_format($item->bonus_amount ?? 0, 0) }}</td>
                    <td class="amount-col">{{ number_format($item->allowance_amount ?? 0, 0) }}</td>
                    <td class="amount-col"><strong>{{ number_format($employeeGross, 0) }}</strong></td>
                    <td class="amount-col">{{ number_format($item->paye_amount ?? 0, 0) }}</td>
                    <td class="amount-col">{{ number_format($item->nssf_amount ?? 0, 0) }}</td>
                    <td class="amount-col">{{ number_format($item->nhif_amount ?? 0, 0) }}</td>
                    <td class="amount-col">{{ number_format($item->heslb_amount ?? 0, 0) }}</td>
                    <td class="amount-col">{{ number_format($item->deduction_amount ?? 0, 0) }}</td>
                    <td class="amount-col"><strong>{{ number_format($item->net_salary ?? 0, 0) }}</strong></td>
                </tr>
                @endforeach
                <!-- Totals Row -->
                <tr class="totals-row">
                    <td colspan="3"><strong>TOTALS</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['basic_salary'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['overtime_amount'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['bonus_amount'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['allowance_amount'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['gross_salary'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['paye_amount'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['nssf_amount'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['nhif_amount'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['heslb_amount'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['deduction_amount'], 0) }}</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['net_salary'], 0) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Department Breakdown -->
        @if(!empty($departmentBreakdown))
        <div class="section-title">DEPARTMENT BREAKDOWN</div>
        <table>
            <thead>
                <tr>
                    <th>Department</th>
                    <th class="amount-col">Employees</th>
                    <th class="amount-col">Total Gross</th>
                    <th class="amount-col">Total Deductions</th>
                    <th class="amount-col">Total Net</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departmentBreakdown as $deptName => $deptData)
                <tr>
                    <td><strong>{{ $deptName }}</strong></td>
                    <td class="amount-col">{{ $deptData['count'] }}</td>
                    <td class="amount-col">{{ number_format($deptData['total_gross'], 0) }}</td>
                    <td class="amount-col">{{ number_format($deptData['total_deductions'], 0) }}</td>
                    <td class="amount-col"><strong>{{ number_format($deptData['total_net'], 0) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Final Summary -->
        <div class="footer-totals">
            <table>
                <tr>
                    <td width="70%"><strong>Total Gross Salary:</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['gross_salary'], 0) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Total Statutory Deductions:</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['paye_amount'] + $totals['nssf_amount'] + $totals['nhif_amount'] + $totals['heslb_amount'], 0) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Total Additional Deductions:</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['deduction_amount'], 0) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Total Deductions:</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['total_deductions'], 0) }}</strong></td>
                </tr>
                <tr style="background: #d1fae5; border-top: 2px solid #059669;">
                    <td><strong style="font-size: 14px;">TOTAL NET SALARY PAYABLE:</strong></td>
                    <td class="amount-col"><strong style="font-size: 14px; color: #059669;">{{ number_format($totals['net_salary'], 0) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Total Employer Contributions:</strong></td>
                    <td class="amount-col"><strong>{{ number_format($totals['employer_nssf'] + $totals['employer_wcf'] + $totals['employer_sdl'], 0) }}</strong></td>
                </tr>
                <tr>
                    <td><strong>Total Employer Cost:</strong></td>
                    <td class="amount-col"><strong style="color: #f59e0b;">{{ number_format($totals['total_employer_cost'], 0) }}</strong></td>
                </tr>
            </table>
        </div>

    </div>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>







