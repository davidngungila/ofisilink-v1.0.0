@extends('layouts.app')

@section('title', 'Payslip - ' . ($payrollItem->employee->name ?? 'Employee') . ' - ' . ($payrollItem->payroll->pay_period ?? '') . ' - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-white">
                                <i class="bx bx-receipt me-2"></i>Employee Payslip
                            </h4>
                            <p class="mb-0 text-white-50">
                                {{ $payrollItem->employee->name ?? 'N/A' }} | 
                                {{ \Carbon\Carbon::parse($payrollItem->payroll->pay_period . '-01')->format('F Y') ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light me-2">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                            <a href="{{ route('payroll.payslip.pdf', $payrollItem->id) }}" class="btn btn-light" target="_blank">
                                <i class="bx bx-download me-1"></i>Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    @php
        $payslipData = [
            'id' => $payrollItem->id,
            'employee_id' => $payrollItem->employee_id,
            'basic_salary' => (float)($payrollItem->basic_salary ?? 0),
            'overtime_amount' => (float)($payrollItem->overtime_amount ?? 0),
            'overtime_hours' => (float)($payrollItem->overtime_hours ?? 0),
            'bonus_amount' => (float)($payrollItem->bonus_amount ?? 0),
            'allowance_amount' => (float)($payrollItem->allowance_amount ?? 0),
            'nssf_amount' => (float)($payrollItem->nssf_amount ?? 0),
            'nhif_amount' => (float)($payrollItem->nhif_amount ?? 0),
            'heslb_amount' => (float)($payrollItem->heslb_amount ?? 0),
            'paye_amount' => (float)($payrollItem->paye_amount ?? 0),
            'wcf_amount' => (float)($payrollItem->wcf_amount ?? 0),
            'sdl_amount' => (float)($payrollItem->sdl_amount ?? 0),
            'deduction_amount' => (float)($payrollItem->deduction_amount ?? 0),
            'other_deductions' => (float)($payrollItem->other_deductions ?? 0),
            'net_salary' => (float)($payrollItem->net_salary ?? 0),
            'status' => $payrollItem->status ?? 'processed',
            'payroll' => [
                'id' => $payrollItem->payroll->id ?? null,
                'pay_period' => $payrollItem->payroll->pay_period ?? '',
                'pay_date' => $payrollItem->payroll->pay_date ? $payrollItem->payroll->pay_date->format('Y-m-d') : null,
                'status' => $payrollItem->payroll->status ?? 'processed',
                'processed_by' => ($payrollItem->payroll->processor ?? null) ? $payrollItem->payroll->processor->name : 'N/A',
                'reviewed_by' => ($payrollItem->payroll->reviewer ?? null) ? $payrollItem->payroll->reviewer->name : null,
                'approved_by' => ($payrollItem->payroll->approver ?? null) ? $payrollItem->payroll->approver->name : null,
                'paid_by' => ($payrollItem->payroll->payer ?? null) ? $payrollItem->payroll->payer->name : null,
            ],
            'employee' => [
                'id' => $payrollItem->employee->id ?? null,
                'name' => $payrollItem->employee->name ?? 'N/A',
                'employee_id' => $payrollItem->employee->employee->employee_id ?? $payrollItem->employee->id ?? 'N/A',
                'department' => ($payrollItem->employee->primaryDepartment ?? null) ? $payrollItem->employee->primaryDepartment->name : 'N/A',
                'position' => ($payrollItem->employee->employee ?? null) ? $payrollItem->employee->employee->position : 'N/A',
            ]
        ];
        $grossSalary = $payslipData['basic_salary'] + $payslipData['overtime_amount'] + $payslipData['bonus_amount'] + $payslipData['allowance_amount'];
        $totalDeductions = $payslipData['paye_amount'] + $payslipData['nssf_amount'] + $payslipData['nhif_amount'] + $payslipData['heslb_amount'] + $payslipData['wcf_amount'] + $payslipData['sdl_amount'] + $payslipData['deduction_amount'] + $payslipData['other_deductions'];
        $calculatedNetPay = $grossSalary - $totalDeductions;
        $netPay = $payslipData['net_salary'] > 0 ? $payslipData['net_salary'] : $calculatedNetPay;
        
        // Calculate percentages
        $payePercent = $grossSalary > 0 ? ($payslipData['paye_amount'] / $grossSalary) * 100 : 0;
        $nssfPercent = $grossSalary > 0 ? ($payslipData['nssf_amount'] / $grossSalary) * 100 : 0;
        $deductionPercent = $grossSalary > 0 ? ($totalDeductions / $grossSalary) * 100 : 0;
        $netPercent = $grossSalary > 0 ? ($netPay / $grossSalary) * 100 : 0;
    @endphp


    <!-- Payslip Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 text-white">
                                <i class="bx bx-receipt me-2"></i>Payslip
                            </h5>
                            <small class="text-white-50">
                                {{ \Carbon\Carbon::parse($payslipData['payroll']['pay_period'] . '-01')->format('F Y') ?? 'N/A' }}
                            </small>
                        </div>
                        <div>
                            <span class="badge bg-light text-dark">
                                {{ ucfirst($payslipData['payroll']['status']) }}
                            </span>
                        </div>
                    </div>
                </div>
                <br>
                <div class="card-body">
                    <!-- Employee Information -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bx bx-user me-2"></i>Employee Information</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2"><strong>Name:</strong> {{ $payslipData['employee']['name'] }}</p>
                                    <p class="mb-2"><strong>Employee ID:</strong> <code>{{ $payslipData['employee']['employee_id'] }}</code></p>
                                    <p class="mb-2"><strong>Department:</strong> {{ $payslipData['employee']['department'] }}</p>
                                    <p class="mb-0"><strong>Position:</strong> {{ $payslipData['employee']['position'] }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0 text-white"><i class="bx bx-calendar me-2"></i>Payroll Processing Information</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>Pay Period:</strong><br>
                                        <span class="text-muted">{{ \Carbon\Carbon::parse($payslipData['payroll']['pay_period'] . '-01')->format('F Y') ?? 'N/A' }}</span>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Pay Date:</strong><br>
                                        <span class="text-muted">{{ $payslipData['payroll']['pay_date'] ? \Carbon\Carbon::parse($payslipData['payroll']['pay_date'])->format('l, F j, Y') : 'N/A' }}</span>
                                    </p>
                                    <p class="mb-2">
                                        <strong>Status:</strong><br>
                                        <span class="badge bg-{{ $payslipData['payroll']['status'] === 'paid' ? 'success' : ($payslipData['payroll']['status'] === 'approved' ? 'info' : 'warning') }}">
                                            {{ ucfirst($payslipData['payroll']['status']) }}
                                        </span>
                                    </p>
                                    <hr>
                                    <p class="mb-2">
                                        <strong>Processed By:</strong><br>
                                        <span class="text-muted">{{ $payslipData['payroll']['processed_by'] }}</span>
                                    </p>
                                    @if($payslipData['payroll']['reviewed_by'])
                                    <p class="mb-2">
                                        <strong>Reviewed By:</strong><br>
                                        <span class="text-muted">{{ $payslipData['payroll']['reviewed_by'] }}</span>
                                    </p>
                                    @endif
                                    @if($payslipData['payroll']['approved_by'])
                                    <p class="mb-2">
                                        <strong>Approved By:</strong><br>
                                        <span class="text-muted">{{ $payslipData['payroll']['approved_by'] }}</span>
                                    </p>
                                    @endif
                                    @if($payslipData['payroll']['paid_by'])
                                    <p class="mb-0">
                                        <strong>Paid By:</strong><br>
                                        <span class="text-muted">{{ $payslipData['payroll']['paid_by'] }}</span>
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Earnings Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-success shadow-sm">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0"><i class="bx bx-trending-up me-2"></i>EARNINGS BREAKDOWN</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Earning Type</th>
                                                    <th class="text-end">Amount (TZS)</th>
                                                    <th class="text-end">% of Gross</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-money me-2 text-success"></i>
                                                        <strong>Basic Salary</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-success">TZS {{ number_format($payslipData['basic_salary'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['basic_salary'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @if($payslipData['overtime_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-time me-2 text-info"></i>
                                                        Overtime Pay
                                                        <small class="text-muted d-block ms-4">({{ number_format($payslipData['overtime_hours'], 2) }} hours)</small>
                                                    </td>
                                                    <td class="text-end fw-bold text-success">+ TZS {{ number_format($payslipData['overtime_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['overtime_amount'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['bonus_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-gift me-2 text-warning"></i>
                                                        <strong>Bonus & Incentives</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-success">+ TZS {{ number_format($payslipData['bonus_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['bonus_amount'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['allowance_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-dollar me-2 text-primary"></i>
                                                        <strong>Allowances</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-success">+ TZS {{ number_format($payslipData['allowance_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['allowance_amount'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @endif
                                                <tr class="table-success border-top border-3">
                                                    <td>
                                                        <strong><i class="bx bx-calculator me-2"></i>TOTAL GROSS SALARY</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="fs-5 text-success">TZS {{ number_format($grossSalary, 2) }}</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="fs-5 text-success">100.00%</strong>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Deductions Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-danger shadow-sm">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0"><i class="bx bx-trending-down me-2"></i>DEDUCTIONS BREAKDOWN</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Deduction Type</th>
                                                    <th class="text-end">Amount (TZS)</th>
                                                    <th class="text-end">% of Gross</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($payslipData['paye_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-receipt me-2 text-danger"></i>
                                                        <strong>PAYE (Pay As You Earn Tax)</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-danger">- TZS {{ number_format($payslipData['paye_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($payePercent, 2) }}%</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['nssf_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-shield me-2 text-danger"></i>
                                                        <strong>NSSF (National Social Security Fund)</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-danger">- TZS {{ number_format($payslipData['nssf_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ number_format($nssfPercent, 2) }}%</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['nhif_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-plus-medical me-2 text-danger"></i>
                                                        <strong>NHIF (National Health Insurance Fund)</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-danger">- TZS {{ number_format($payslipData['nhif_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['nhif_amount'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['heslb_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-book me-2 text-danger"></i>
                                                        <strong>HESLB (Higher Education Students' Loans Board)</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-danger">- TZS {{ number_format($payslipData['heslb_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['heslb_amount'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['wcf_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-buildings me-2 text-danger"></i>
                                                        <strong>WCF (Workers Compensation Fund)</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-danger">- TZS {{ number_format($payslipData['wcf_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['wcf_amount'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['sdl_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-money me-2 text-danger"></i>
                                                        <strong>SDL (Skills Development Levy)</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-danger">- TZS {{ number_format($payslipData['sdl_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['sdl_amount'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['deduction_amount'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-minus-circle me-2 text-danger"></i>
                                                        <strong>Other Deductions</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-danger">- TZS {{ number_format($payslipData['deduction_amount'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['deduction_amount'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @endif
                                                @if($payslipData['other_deductions'] > 0)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-minus-circle me-2 text-danger"></i>
                                                        <strong>Additional Deductions</strong>
                                                    </td>
                                                    <td class="text-end fw-bold text-danger">- TZS {{ number_format($payslipData['other_deductions'], 2) }}</td>
                                                    <td class="text-end text-muted">{{ $grossSalary > 0 ? number_format(($payslipData['other_deductions'] / $grossSalary) * 100, 2) : 0 }}%</td>
                                                </tr>
                                                @endif
                                                <tr class="table-danger border-top border-3">
                                                    <td>
                                                        <strong><i class="bx bx-calculator me-2"></i>TOTAL DEDUCTIONS</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="fs-5 text-danger">- TZS {{ number_format($totalDeductions, 2) }}</strong>
                                                    </td>
                                                    <td class="text-end">
                                                        <strong class="fs-5 text-danger">{{ number_format($deductionPercent, 2) }}%</strong>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Net Pay Calculation Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-info shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="card-body text-white">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="mb-3 text-white"><i class="bx bx-calculator me-2"></i>NET PAY CALCULATION</h5>
                                            <div class="calculation-breakdown">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-white-50">Gross Salary:</span>
                                                    <strong class="fs-5">TZS {{ number_format($grossSalary, 2) }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-white-50">Less: Total Deductions:</span>
                                                    <strong class="fs-5">- TZS {{ number_format($totalDeductions, 2) }}</strong>
                                                </div>
                                                <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fs-5 fw-bold">NET PAY:</span>
                                                    <strong class="fs-2 fw-bold" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">TZS {{ number_format($netPay, 2) }}</strong>
                                                </div>
                                                <div class="mt-3 text-center">
                                                    <small class="text-white-50">Net Pay represents {{ number_format($netPercent, 2) }}% of Gross Salary</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="net-pay-highlight">
                                                <h6 class="text-white-50 mb-3">NET SALARY PAYABLE</h6>
                                                <h1 class="mb-0 fw-bold" style="font-size: 3rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">
                                                    TZS {{ number_format($netPay, 0) }}
                                                </h1>
                                                <div class="mt-4">
                                                    <div class="badge bg-light text-dark fs-6 px-3 py-2">
                                                        {{ number_format($netPercent, 1) }}% of Gross
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-success shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-3"><i class="bx bx-trending-up me-2"></i>Gross Earnings</h6>
                                    <h3 class="text-success mb-2">TZS {{ number_format($grossSalary, 2) }}</h3>
                                    <small class="text-muted">100% of total earnings</small>
                                    <hr class="my-3">
                                    <div class="text-start">
                                        <small class="d-block text-muted mb-1">Basic: TZS {{ number_format($payslipData['basic_salary'], 2) }}</small>
                                        @if($payslipData['overtime_amount'] > 0)
                                        <small class="d-block text-muted mb-1">Overtime: TZS {{ number_format($payslipData['overtime_amount'], 2) }}</small>
                                        @endif
                                        @if($payslipData['bonus_amount'] > 0)
                                        <small class="d-block text-muted mb-1">Bonus: TZS {{ number_format($payslipData['bonus_amount'], 2) }}</small>
                                        @endif
                                        @if($payslipData['allowance_amount'] > 0)
                                        <small class="d-block text-muted">Allowance: TZS {{ number_format($payslipData['allowance_amount'], 2) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-danger shadow-sm h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-muted mb-3"><i class="bx bx-trending-down me-2"></i>Total Deductions</h6>
                                    <h3 class="text-danger mb-2">TZS {{ number_format($totalDeductions, 2) }}</h3>
                                    <small class="text-muted">{{ number_format($deductionPercent, 2) }}% of gross salary</small>
                                    <hr class="my-3">
                                    <div class="text-start">
                                        @if($payslipData['paye_amount'] > 0)
                                        <small class="d-block text-muted mb-1">PAYE: TZS {{ number_format($payslipData['paye_amount'], 2) }}</small>
                                        @endif
                                        @if($payslipData['nssf_amount'] > 0)
                                        <small class="d-block text-muted mb-1">NSSF: TZS {{ number_format($payslipData['nssf_amount'], 2) }}</small>
                                        @endif
                                        @if($payslipData['nhif_amount'] > 0)
                                        <small class="d-block text-muted mb-1">NHIF: TZS {{ number_format($payslipData['nhif_amount'], 2) }}</small>
                                        @endif
                                        @if($payslipData['heslb_amount'] > 0)
                                        <small class="d-block text-muted mb-1">HESLB: TZS {{ number_format($payslipData['heslb_amount'], 2) }}</small>
                                        @endif
                                        @if(($payslipData['wcf_amount'] + $payslipData['sdl_amount'] + $payslipData['deduction_amount'] + $payslipData['other_deductions']) > 0)
                                        <small class="d-block text-muted">Others: TZS {{ number_format($payslipData['wcf_amount'] + $payslipData['sdl_amount'] + $payslipData['deduction_amount'] + $payslipData['other_deductions'], 2) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-primary shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <div class="card-body text-center text-white">
                                    <h6 class="text-white-50 mb-3"><i class="bx bx-money me-2"></i>Net Pay</h6>
                                    <h3 class="text-white mb-2">TZS {{ number_format($netPay, 2) }}</h3>
                                    <small class="text-white-50">{{ number_format($netPercent, 2) }}% of gross salary</small>
                                    <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                                    <div class="text-start text-white-50">
                                        <small class="d-block mb-2">
                                            <strong>Calculation:</strong><br>
                                            Gross: TZS {{ number_format($grossSalary, 2) }}<br>
                                            Less Deductions: TZS {{ number_format($totalDeductions, 2) }}<br>
                                            <strong class="text-white">= Net Pay: TZS {{ number_format($netPay, 2) }}</strong>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

