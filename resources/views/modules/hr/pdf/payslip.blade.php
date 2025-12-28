<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    @php
        // Helper function to safely get string value - prevents array output
        $safeString = function($value, $default = '') {
            if (is_array($value)) {
                \Log::warning('Array detected in view safeString helper', ['value' => $value]);
                return $default;
            }
            if (is_object($value) && !($value instanceof \DateTime) && !($value instanceof \Carbon\Carbon)) {
                try {
                    return method_exists($value, '__toString') ? (string)$value : $default;
                } catch (\Throwable $e) {
                    return $default;
                }
            }
            if ($value === null) {
                return $default;
            }
            try {
                return (string)$value;
            } catch (\Throwable $e) {
                return $default;
            }
        };
        
        // Helper to safely output in Blade - wraps htmlspecialchars with array check
        $safeOutput = function($value, $default = '') use ($safeString) {
            return htmlspecialchars($safeString($value, $default), ENT_QUOTES, 'UTF-8');
        };
        
        $empNoTitle = $safeString($payslip['emp_no'] ?? null, 'N/A');
        $payPeriodTitle = $safeString($payslip['pay_period_name'] ?? null, 'N/A');
    @endphp
    <title>Salary Slip - {{ $empNoTitle }} - {{ $payPeriodTitle }}</title>
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
        }
        .info-table th { 
            background-color: #f9f9f9; 
            font-weight: bold; 
            width: 25%; 
        }
        .info-table td { 
            border-bottom: 1px solid #eee; 
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
        .salary-table .deduction { 
            color: #d63384; 
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
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
        .mb-3 { margin-bottom: 15px; }
        .color-primary { color: #940000; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        // Ensure timezone and date_format are strings, not arrays
        $timezone = is_array($orgSettings->timezone ?? null) ? config('app.timezone', 'Africa/Dar_es_Salaam') : (string)($orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam'));
        $dateFormat = is_array($orgSettings->date_format ?? null) ? 'd M Y' : (string)($orgSettings->date_format ?? 'd M Y');
        $documentDate = now()->setTimezone($timezone)->format($dateFormat);
        $empNo = is_array($payslip['emp_no'] ?? null) ? 'N/A' : (string)($payslip['emp_no'] ?? 'N/A');
        $payPeriodValue = $payslip['pay_period'] ?? date('Y-m');
        $payPeriod = is_array($payPeriodValue) ? date('Y-m') : (string)$payPeriodValue;
        // Final safety check - ensure both are strings
        if (is_array($empNo)) $empNo = 'N/A';
        if (is_array($payPeriod)) $payPeriod = date('Y-m');
        $documentRef = 'PS-' . $empNo . '-' . $payPeriod;
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'OFFICIAL SALARY SLIP',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <div class="payslip-period">
        @php
            // Safely get pay_period for strtotime
            $payPeriodForDate = $payslip['pay_period'] ?? date('Y-m');
            if (is_array($payPeriodForDate)) {
                $payPeriodForDate = date('Y-m');
            } else {
                $payPeriodForDate = (string)$payPeriodForDate;
            }
            
            $payPeriodName = is_array($payslip['pay_period_name'] ?? null) ? date('F Y') : ($payslip['pay_period_name'] ?? date('F Y', strtotime($payPeriodForDate . '-01')));
            $payDate = is_array($payslip['pay_date'] ?? null) ? date('j M, Y') : ($payslip['pay_date'] ? \Carbon\Carbon::parse($payslip['pay_date'])->format('j M, Y') : date('j M, Y'));
        @endphp
        PAY PERIOD: {{ strtoupper($payPeriodName) }} | PAY DATE: {{ $payDate }}
    </div>

    <div class="section">
        <div class="section-title">EMPLOYEE INFORMATION</div>
        <table class="info-table">
            <tr>
                <th>Employee Name</th>
                <td>{{ $safeOutput($payslip['full_name'] ?? null, 'N/A') }}</td>
                <th>Employee Number</th>
                <td>{{ $safeOutput($payslip['emp_no'] ?? null, 'N/A') }}</td>
            </tr>
            <tr>
                <th>Department</th>
                <td>{{ $safeOutput($payslip['department_name'] ?? null, 'N/A') }}</td>
                <th>Position</th>
                <td>{{ $safeOutput($payslip['position'] ?? null, 'N/A') }}</td>
            </tr>
            <tr>
                <th>Employment Type</th>
                <td>{{ $safeOutput($payslip['employment_type'] ?? null, 'N/A') }}</td>
                <th>Bank Details</th>
                <td>
                    @php
                        $bankName = is_array($bank_details['bank_name'] ?? null) ? 'Not Provided' : ($bank_details['bank_name'] ?? 'Not Provided');
                        $accountNumber = is_array($bank_details['account_number'] ?? null) ? '' : ($bank_details['account_number'] ?? '');
                    @endphp
                    {{ htmlspecialchars($bankName) }} - 
                    @if(!empty($accountNumber) && is_string($accountNumber))
                        ****{{ substr($accountNumber, -4) }}
                    @else
                        Not Provided
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">SALARY BREAKDOWN</div>
        
        @php
            $basic = (float)($payslip['basic_salary'] ?? 0);
            $overtime = (float)($payslip['overtime_amount'] ?? 0);
            $bonus = (float)($payslip['bonus_amount'] ?? 0);
            $allowance = (float)($payslip['allowance_amount'] ?? 0);
            $grossEarnings = $basic + $overtime + $bonus + $allowance;
            
            $nssf = (float)($payslip['nssf_amount'] ?? 0);
            $paye = (float)($payslip['paye_amount'] ?? 0);
            $nhif = (float)($payslip['nhif_amount'] ?? 0);
            $heslb = (float)($payslip['heslb_amount'] ?? 0);
            $wcf = (float)($payslip['wcf_amount'] ?? 0);
            $otherDeductions = (float)($payslip['deduction_amount'] ?? 0);
            // Safely calculate fixed deductions total - ensure all amounts are numeric
            $fixedDeductionsTotal = 0;
            if (!empty($fixed_deductions) && is_array($fixed_deductions)) {
                foreach ($fixed_deductions as $deduction) {
                    if (isset($deduction['amount']) && !is_array($deduction['amount'])) {
                        $amount = is_numeric($deduction['amount']) ? (float)$deduction['amount'] : 0;
                        $fixedDeductionsTotal += $amount;
                    }
                }
            }
            
            $totalDeductions = $nssf + $paye + $nhif + $heslb + $wcf + $otherDeductions + $fixedDeductionsTotal;
            $netSalary = (float)($payslip['net_salary'] ?? 0);
            
            $formatCurrency = function($amount) {
                return 'TZS ' . number_format($amount, 0, '', ',');
            };
        @endphp
        
        <table class="salary-table">
            <tr>
                <th colspan="2">EARNINGS</th>
                <th class="amount">AMOUNT (TZS)</th>
            </tr>
            <tr>
                <td colspan="2">Basic Salary</td>
                <td class="amount">{{ $formatCurrency($basic) }}</td>
            </tr>
            @if($overtime > 0)
            <tr>
                <td colspan="2">Overtime Pay</td>
                <td class="amount">+ {{ $formatCurrency($overtime) }}</td>
            </tr>
            @endif
            @if($bonus > 0)
            <tr>
                <td colspan="2">Bonus & Incentives</td>
                <td class="amount">+ {{ $formatCurrency($bonus) }}</td>
            </tr>
            @endif
            @if($allowance > 0)
            <tr>
                <td colspan="2">Allowances</td>
                <td class="amount">+ {{ $formatCurrency($allowance) }}</td>
            </tr>
            @endif
            <tr class="subtotal">
                <td colspan="2"><strong>TOTAL GROSS SALARY</strong></td>
                <td class="amount"><strong>{{ $formatCurrency($grossEarnings) }}</strong></td>
            </tr>
            
            <tr>
                <th colspan="2">DEDUCTIONS</th>
                <th class="amount">AMOUNT (TZS)</th>
            </tr>
            @if($paye > 0)
            <tr>
                <td colspan="2">PAYE Tax</td>
                <td class="amount deduction">- {{ $formatCurrency($paye) }}</td>
            </tr>
            @endif
            @if($nssf > 0)
            <tr>
                <td colspan="2">NSSF Contribution</td>
                <td class="amount deduction">- {{ $formatCurrency($nssf) }}</td>
            </tr>
            @endif
            @if($nhif > 0)
            <tr>
                <td colspan="2">NHIF Contribution</td>
                <td class="amount deduction">- {{ $formatCurrency($nhif) }}</td>
            </tr>
            @endif
            @if($heslb > 0)
            <tr>
                <td colspan="2">HESLB Loan</td>
                <td class="amount deduction">- {{ $formatCurrency($heslb) }}</td>
            </tr>
            @endif
            @if($wcf > 0)
            <tr>
                <td colspan="2">WCF Contribution</td>
                <td class="amount deduction">- {{ $formatCurrency($wcf) }}</td>
            </tr>
            @endif
            @if($otherDeductions > 0)
            <tr>
                <td colspan="2">Other Deductions</td>
                <td class="amount deduction">- {{ $formatCurrency($otherDeductions) }}</td>
            </tr>
            @endif
            @foreach($fixed_deductions ?? [] as $deduction)
                @php
                    $deductionAmount = is_array($deduction['amount'] ?? null) ? 0 : (float)($deduction['amount'] ?? 0);
                    $deductionType = is_array($deduction['deduction_type'] ?? null) ? 'Other' : ($deduction['deduction_type'] ?? 'Other');
                @endphp
                @if($deductionAmount > 0)
                <tr>
                    <td colspan="2">{{ htmlspecialchars($deductionType) }}</td>
                    <td class="amount deduction">- {{ $formatCurrency($deductionAmount) }}</td>
                </tr>
                @endif
            @endforeach
            <tr class="subtotal">
                <td colspan="2"><strong>TOTAL DEDUCTIONS</strong></td>
                <td class="amount deduction"><strong>- {{ $formatCurrency($totalDeductions) }}</strong></td>
            </tr>
            
            <tr class="total">
                <td colspan="2"><strong>NET SALARY PAYABLE</strong></td>
                <td class="amount"><strong>{{ $formatCurrency($netSalary) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="net-salary">
        <div>NET SALARY PAYABLE</div>
        <div class="net-salary-amount">{{ $formatCurrency($netSalary) }}</div>
        <div class="net-salary-words">{{ \App\Services\PayrollPdfService::convertNumberToWords($netSalary) }} TANZANIAN SHILLINGS ONLY</div>
    </div>

    <div class="summary-breakdown">
        <div class="breakdown-row">
            <span class="breakdown-label">Gross Earnings:</span>
            <span class="breakdown-value">{{ $formatCurrency($grossEarnings) }}</span>
        </div>
        <div class="breakdown-row">
            <span class="breakdown-label">Total Deductions:</span>
            <span class="breakdown-value deduction">- {{ $formatCurrency($totalDeductions) }}</span>
        </div>
        <div class="breakdown-row text-bold">
            <span class="breakdown-label color-primary">Net Salary:</span>
            <span class="breakdown-value color-primary">{{ $formatCurrency($netSalary) }}</span>
        </div>
    </div>

    @include('components.pdf-disclaimer')

    <!-- Visible Footer Section -->
    <div style="margin-top: 40px; padding-top: 15px; border-top: 2px solid #e9ecef; page-break-inside: avoid;">
        <div style="text-align: center; font-size: 9px; color: #6c757d; line-height: 1.6;">
            <p style="margin: 5px 0; font-weight: bold; color: #940000;">CONFIDENTIAL DOCUMENT - SALARY SLIP</p>
            <p style="margin: 5px 0;">
                This document was automatically generated by {{ config('app.name', 'OfisiLink') }} System.
                No manual signature is required.
            </p>
            <p style="margin: 5px 0;">
                @php
                    $footerTimezone = is_array($orgSettings->timezone ?? null) ? config('app.timezone', 'Africa/Dar_es_Salaam') : (string)($orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam'));
                @endphp
                Generated on: {{ now()->setTimezone($footerTimezone)->format('d M Y, H:i:s') }} ({{ $footerTimezone }})
            </p>
            <p style="margin: 5px 0; color: #940000;">
                <strong>OfisiLink - Powered by EmCa Technologies</strong>
            </p>
        </div>
    </div>

    @include('components.pdf-footer')
</body>
</html>
