@extends('layouts.app')

@section('title', 'View Payroll - ' . $payroll->pay_period . ' - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-white">
                                <i class="bx bx-file-invoice me-2"></i>Payroll Details
                            </h4>
                            <p class="mb-0 text-white-50">
                                Pay Period: <strong>{{ \Carbon\Carbon::parse($payroll->pay_period . '-01')->format('F Y') }}</strong> | 
                                Status: <strong>{{ ucfirst($payroll->status) }}</strong>
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light me-2">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                            @if($payroll->status === 'paid' || $payroll->status === 'approved')
                            <a href="{{ route('payroll.report.pdf', $payroll->id) }}" class="btn btn-light" target="_blank">
                                <i class="bx bx-download me-1"></i>Download PDF
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $employeeCount = $payroll->items->count();
        
        $totals = [
            'basic_salary' => $payroll->items->sum('basic_salary'),
            'overtime_amount' => $payroll->items->sum('overtime_amount'),
            'overtime_hours' => $payroll->items->sum('overtime_hours'),
            'bonus_amount' => $payroll->items->sum('bonus_amount'),
            'allowance_amount' => $payroll->items->sum('allowance_amount'),
            'gross_salary' => 0,
            'nssf_amount' => $payroll->items->sum('nssf_amount'),
            'nhif_amount' => $payroll->items->sum('nhif_amount'),
            'heslb_amount' => $payroll->items->sum('heslb_amount'),
            'paye_amount' => $payroll->items->sum('paye_amount'),
            'wcf_amount' => $payroll->items->sum('wcf_amount'),
            'sdl_amount' => $payroll->items->sum('sdl_amount'),
            'deduction_amount' => $payroll->items->sum('deduction_amount'),
            'other_deductions' => $payroll->items->sum('other_deductions'),
            'total_deductions' => 0,
            'net_salary' => $payroll->items->sum('net_salary'),
            'total_employer_cost' => $payroll->items->sum('total_employer_cost'),
        ];
        $totals['gross_salary'] = $totals['basic_salary'] + $totals['overtime_amount'] + $totals['bonus_amount'] + $totals['allowance_amount'];
        $totals['total_deductions'] = $totals['nssf_amount'] + $totals['nhif_amount'] + $totals['heslb_amount'] + $totals['paye_amount'] + $totals['wcf_amount'] + $totals['sdl_amount'] + $totals['deduction_amount'] + $totals['other_deductions'];
        
        // Calculate averages
        $averages = [
            'basic_salary' => $employeeCount > 0 ? $totals['basic_salary'] / $employeeCount : 0,
            'gross_salary' => $employeeCount > 0 ? $totals['gross_salary'] / $employeeCount : 0,
            'net_salary' => $employeeCount > 0 ? $totals['net_salary'] / $employeeCount : 0,
            'total_deductions' => $employeeCount > 0 ? $totals['total_deductions'] / $employeeCount : 0,
            'overtime_hours' => $employeeCount > 0 ? $totals['overtime_hours'] / $employeeCount : 0,
        ];
        
        // Calculate percentages
        $percentages = [
            'deductions_to_gross' => $totals['gross_salary'] > 0 ? ($totals['total_deductions'] / $totals['gross_salary']) * 100 : 0,
            'net_to_gross' => $totals['gross_salary'] > 0 ? ($totals['net_salary'] / $totals['gross_salary']) * 100 : 0,
            'paye_to_gross' => $totals['gross_salary'] > 0 ? ($totals['paye_amount'] / $totals['gross_salary']) * 100 : 0,
            'nssf_to_gross' => $totals['gross_salary'] > 0 ? ($totals['nssf_amount'] / $totals['gross_salary']) * 100 : 0,
            'employer_cost_to_gross' => $totals['gross_salary'] > 0 ? ($totals['total_employer_cost'] / $totals['gross_salary']) * 100 : 0,
        ];
        
        // Department-wise breakdown
        $departmentBreakdown = [];
        foreach($payroll->items as $item) {
            $deptName = $item->employee->primaryDepartment->name ?? 'Unassigned';
            if (!isset($departmentBreakdown[$deptName])) {
                $departmentBreakdown[$deptName] = [
                    'count' => 0,
                    'basic_salary' => 0,
                    'overtime_amount' => 0,
                    'bonus_amount' => 0,
                    'allowance_amount' => 0,
                    'gross_salary' => 0,
                    'total_deductions' => 0,
                    'net_salary' => 0,
                    'total_employer_cost' => 0,
                ];
            }
            $departmentBreakdown[$deptName]['count']++;
            $departmentBreakdown[$deptName]['basic_salary'] += $item->basic_salary;
            $departmentBreakdown[$deptName]['overtime_amount'] += $item->overtime_amount;
            $departmentBreakdown[$deptName]['bonus_amount'] += $item->bonus_amount;
            $departmentBreakdown[$deptName]['allowance_amount'] += $item->allowance_amount;
            $departmentBreakdown[$deptName]['gross_salary'] += ($item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount);
            $departmentBreakdown[$deptName]['total_deductions'] += ($item->paye_amount + $item->nssf_amount + $item->nhif_amount + $item->heslb_amount + $item->wcf_amount + $item->sdl_amount + $item->deduction_amount + $item->other_deductions);
            $departmentBreakdown[$deptName]['net_salary'] += $item->net_salary;
            $departmentBreakdown[$deptName]['total_employer_cost'] += $item->total_employer_cost;
        }
        
        // Sort departments by gross salary (descending)
        uasort($departmentBreakdown, function($a, $b) {
            return $b['gross_salary'] <=> $a['gross_salary'];
        });
        
        // Calculate min, max, median
        $grossSalaries = $payroll->items->map(function($item) {
            return $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
        })->sort()->values();
        
        $netSalaries = $payroll->items->map(function($item) {
            return $item->net_salary;
        })->sort()->values();
        
        $statistics = [
            'min_gross' => $grossSalaries->first() ?? 0,
            'max_gross' => $grossSalaries->last() ?? 0,
            'median_gross' => $grossSalaries->count() > 0 ? $grossSalaries->get(floor($grossSalaries->count() / 2)) : 0,
            'min_net' => $netSalaries->first() ?? 0,
            'max_net' => $netSalaries->last() ?? 0,
            'median_net' => $netSalaries->count() > 0 ? $netSalaries->get(floor($netSalaries->count() / 2)) : 0,
        ];
    @endphp

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-primary border-top border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small">Total Employees</h6>
                            <h2 class="text-primary mb-0">{{ number_format($employeeCount) }}</h2>
                            <small class="text-muted">Active in payroll</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-primary rounded">
                                <i class="bx bx-user fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-success border-top border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small">Total Gross Salary</h6>
                            <h2 class="text-success mb-0">TZS {{ number_format($totals['gross_salary'], 0) }}</h2>
                            <small class="text-muted">Avg: TZS {{ number_format($averages['gross_salary'], 0) }}</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-success rounded">
                                <i class="bx bx-trending-up fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-danger border-top border-4 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small">Total Deductions</h6>
                            <h2 class="text-danger mb-0">TZS {{ number_format($totals['total_deductions'], 0) }}</h2>
                            <small class="text-muted">{{ number_format($percentages['deductions_to_gross'], 2) }}% of gross</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-danger rounded">
                                <i class="bx bx-trending-down fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-info border-top border-4 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-white-50 mb-2 text-uppercase small">Total Net Pay</h6>
                            <h2 class="text-white mb-0">TZS {{ number_format($totals['net_salary'], 0) }}</h2>
                            <small class="text-white-50">{{ number_format($percentages['net_to_gross'], 2) }}% of gross</small>
                        </div>
                        <div class="avatar avatar-lg">
                            <div class="avatar-initial bg-white text-primary rounded">
                                <i class="bx bx-money fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Employer Cost</h6>
                    <h4 class="text-warning mb-1">TZS {{ number_format($totals['total_employer_cost'], 0) }}</h4>
                    <small class="text-muted">{{ number_format($percentages['employer_cost_to_gross'], 2) }}% of gross</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-secondary shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Average Net Pay</h6>
                    <h4 class="text-secondary mb-1">TZS {{ number_format($averages['net_salary'], 0) }}</h4>
                    <small class="text-muted">Per employee</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Overtime Hours</h6>
                    <h4 class="text-success mb-1">{{ number_format($totals['overtime_hours'], 1) }}</h4>
                    <small class="text-muted">Avg: {{ number_format($averages['overtime_hours'], 1) }} hrs/emp</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Overtime Pay</h6>
                    <h4 class="text-primary mb-1">TZS {{ number_format($totals['overtime_amount'], 0) }}</h4>
                    <small class="text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['overtime_amount'] / $totals['gross_salary']) * 100, 2) : 0 }}% of gross</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Breakdown Section -->
    <div class="row mb-4">
        <!-- Earnings Breakdown -->
        <div class="col-lg-6 mb-4">
            <div class="card border-success shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bx bx-trending-up me-2"></i>EARNINGS BREAKDOWN</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Earning Type</th>
                                    <th class="text-end">Amount (TZS)</th>
                                    <th class="text-end">% of Gross</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="bx bx-money me-2 text-success"></i><strong>Basic Salary</strong></td>
                                    <td class="text-end fw-bold text-success">TZS {{ number_format($totals['basic_salary'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['basic_salary'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @if($totals['overtime_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-time me-2 text-info"></i>Overtime Pay</td>
                                    <td class="text-end fw-bold text-success">TZS {{ number_format($totals['overtime_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['overtime_amount'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endif
                                @if($totals['bonus_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-gift me-2 text-warning"></i>Bonus & Incentives</td>
                                    <td class="text-end fw-bold text-success">TZS {{ number_format($totals['bonus_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['bonus_amount'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endif
                                @if($totals['allowance_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-dollar me-2 text-primary"></i>Allowances</td>
                                    <td class="text-end fw-bold text-success">TZS {{ number_format($totals['allowance_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['allowance_amount'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endif
                                <tr class="table-success border-top border-3">
                                    <td><strong><i class="bx bx-calculator me-2"></i>TOTAL GROSS SALARY</strong></td>
                                    <td class="text-end"><strong class="fs-5 text-success">TZS {{ number_format($totals['gross_salary'], 2) }}</strong></td>
                                    <td class="text-end"><strong class="fs-5 text-success">100.00%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deductions Breakdown -->
        <div class="col-lg-6 mb-4">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bx bx-trending-down me-2"></i>DEDUCTIONS BREAKDOWN</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Deduction Type</th>
                                    <th class="text-end">Amount (TZS)</th>
                                    <th class="text-end">% of Gross</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($totals['paye_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-receipt me-2 text-danger"></i><strong>PAYE Tax</strong></td>
                                    <td class="text-end fw-bold text-danger">TZS {{ number_format($totals['paye_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ number_format($percentages['paye_to_gross'], 2) }}%</td>
                                </tr>
                                @endif
                                @if($totals['nssf_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-shield me-2 text-danger"></i><strong>NSSF Contribution</strong></td>
                                    <td class="text-end fw-bold text-danger">TZS {{ number_format($totals['nssf_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ number_format($percentages['nssf_to_gross'], 2) }}%</td>
                                </tr>
                                @endif
                                @if($totals['nhif_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-plus-medical me-2 text-danger"></i>NHIF Contribution</td>
                                    <td class="text-end fw-bold text-danger">TZS {{ number_format($totals['nhif_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['nhif_amount'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endif
                                @if($totals['heslb_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-book me-2 text-danger"></i>HESLB Loan</td>
                                    <td class="text-end fw-bold text-danger">TZS {{ number_format($totals['heslb_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['heslb_amount'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endif
                                @if($totals['wcf_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-buildings me-2 text-danger"></i>WCF Contribution</td>
                                    <td class="text-end fw-bold text-danger">TZS {{ number_format($totals['wcf_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['wcf_amount'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endif
                                @if($totals['sdl_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-money me-2 text-danger"></i>SDL (Skills Development)</td>
                                    <td class="text-end fw-bold text-danger">TZS {{ number_format($totals['sdl_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['sdl_amount'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endif
                                @if($totals['deduction_amount'] > 0)
                                <tr>
                                    <td><i class="bx bx-minus-circle me-2 text-danger"></i>Other Deductions</td>
                                    <td class="text-end fw-bold text-danger">TZS {{ number_format($totals['deduction_amount'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['deduction_amount'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endif
                                @if($totals['other_deductions'] > 0)
                                <tr>
                                    <td><i class="bx bx-minus-circle me-2 text-danger"></i>Additional Deductions</td>
                                    <td class="text-end fw-bold text-danger">TZS {{ number_format($totals['other_deductions'], 2) }}</td>
                                    <td class="text-end text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($totals['other_deductions'] / $totals['gross_salary']) * 100, 2) : 0 }}%</td>
                                </tr>
                                @endif
                                <tr class="table-danger border-top border-3">
                                    <td><strong><i class="bx bx-calculator me-2"></i>TOTAL DEDUCTIONS</strong></td>
                                    <td class="text-end"><strong class="fs-5 text-danger">TZS {{ number_format($totals['total_deductions'], 2) }}</strong></td>
                                    <td class="text-end"><strong class="fs-5 text-danger">{{ number_format($percentages['deductions_to_gross'], 2) }}%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Net Pay Calculation & Statistics -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card border-info shadow-lg h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <h5 class="mb-4 text-white"><i class="bx bx-calculator me-2"></i>NET PAY CALCULATION SUMMARY</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="calculation-breakdown">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-white-50">Total Gross Salary:</span>
                                    <strong class="fs-5">TZS {{ number_format($totals['gross_salary'], 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="text-white-50">Less: Total Deductions:</span>
                                    <strong class="fs-5">- TZS {{ number_format($totals['total_deductions'], 2) }}</strong>
                                </div>
                                <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="fs-5 fw-bold">NET PAY:</span>
                                    <strong class="fs-2 fw-bold" style="text-shadow: 2px 2px 4px rgba(0,0,0,0.3);">TZS {{ number_format($totals['net_salary'], 2) }}</strong>
                                </div>
                                <div class="mt-4">
                                    <small class="text-white-50 d-block">Net Pay represents {{ number_format($percentages['net_to_gross'], 2) }}% of Gross Salary</small>
                                    <small class="text-white-50 d-block">Total Employer Cost: TZS {{ number_format($totals['total_employer_cost'], 2) }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="statistics-summary">
                                <h6 class="text-white-50 mb-3">Key Statistics</h6>
                                <div class="mb-2">
                                    <small class="text-white-50 d-block">Average Gross Salary</small>
                                    <strong class="fs-5">TZS {{ number_format($averages['gross_salary'], 2) }}</strong>
                                </div>
                                <hr class="my-2" style="border-color: rgba(255,255,255,0.3);">
                                <div class="mb-2">
                                    <small class="text-white-50 d-block">Average Net Pay</small>
                                    <strong class="fs-5">TZS {{ number_format($averages['net_salary'], 2) }}</strong>
                                </div>
                                <hr class="my-2" style="border-color: rgba(255,255,255,0.3);">
                                <div class="mb-2">
                                    <small class="text-white-50 d-block">Average Deductions</small>
                                    <strong class="fs-5">TZS {{ number_format($averages['total_deductions'], 2) }}</strong>
                                </div>
                                <hr class="my-2" style="border-color: rgba(255,255,255,0.3);">
                                <div>
                                    <small class="text-white-50 d-block">Average Employer Cost</small>
                                    <strong class="fs-5">TZS {{ $employeeCount > 0 ? number_format($totals['total_employer_cost'] / $employeeCount, 2) : 0 }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bx bx-bar-chart-alt-2 me-2"></i>Salary Range</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Minimum Gross</small>
                        <strong class="fs-5 text-success">TZS {{ number_format($statistics['min_gross'], 2) }}</strong>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Maximum Gross</small>
                        <strong class="fs-5 text-danger">TZS {{ number_format($statistics['max_gross'], 2) }}</strong>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Median Gross</small>
                        <strong class="fs-5 text-info">TZS {{ number_format($statistics['median_gross'], 2) }}</strong>
                    </div>
                    <hr>
                    <div>
                        <small class="text-muted d-block mb-1">Range</small>
                        <strong class="fs-6 text-muted">TZS {{ number_format($statistics['max_gross'] - $statistics['min_gross'], 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Department Breakdown -->
    @if(count($departmentBreakdown) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-buildings me-2"></i>DEPARTMENT-WISE BREAKDOWN</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Department</th>
                                    <th class="text-center">Employees</th>
                                    <th class="text-end">Basic Salary</th>
                                    <th class="text-end">Overtime</th>
                                    <th class="text-end">Bonus</th>
                                    <th class="text-end">Allowance</th>
                                    <th class="text-end">Gross Salary</th>
                                    <th class="text-end">Deductions</th>
                                    <th class="text-end">Net Pay</th>
                                    <th class="text-end">Employer Cost</th>
                                    <th class="text-end">% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departmentBreakdown as $deptName => $deptData)
                                <tr>
                                    <td><strong>{{ $deptName }}</strong></td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $deptData['count'] }}</span></td>
                                    <td class="text-end">TZS {{ number_format($deptData['basic_salary'], 2) }}</td>
                                    <td class="text-end">TZS {{ number_format($deptData['overtime_amount'], 2) }}</td>
                                    <td class="text-end">TZS {{ number_format($deptData['bonus_amount'], 2) }}</td>
                                    <td class="text-end">TZS {{ number_format($deptData['allowance_amount'], 2) }}</td>
                                    <td class="text-end fw-bold text-success">TZS {{ number_format($deptData['gross_salary'], 2) }}</td>
                                    <td class="text-end text-danger">TZS {{ number_format($deptData['total_deductions'], 2) }}</td>
                                    <td class="text-end fw-bold text-primary">TZS {{ number_format($deptData['net_salary'], 2) }}</td>
                                    <td class="text-end text-warning">TZS {{ number_format($deptData['total_employer_cost'], 2) }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-info">
                                            {{ $totals['gross_salary'] > 0 ? number_format(($deptData['gross_salary'] / $totals['gross_salary']) * 100, 2) : 0 }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-light border-top border-3 fw-bold">
                                    <td><strong>TOTALS</strong></td>
                                    <td class="text-center"><strong>{{ $employeeCount }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($totals['basic_salary'], 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($totals['overtime_amount'], 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($totals['bonus_amount'], 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($totals['allowance_amount'], 2) }}</strong></td>
                                    <td class="text-end"><strong class="text-success">TZS {{ number_format($totals['gross_salary'], 2) }}</strong></td>
                                    <td class="text-end"><strong class="text-danger">TZS {{ number_format($totals['total_deductions'], 2) }}</strong></td>
                                    <td class="text-end"><strong class="text-primary">TZS {{ number_format($totals['net_salary'], 2) }}</strong></td>
                                    <td class="text-end"><strong class="text-warning">TZS {{ number_format($totals['total_employer_cost'], 2) }}</strong></td>
                                    <td class="text-end"><strong>100.00%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Payroll Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Payroll Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Pay Period:</th>
                                    <td>{{ \Carbon\Carbon::parse($payroll->pay_period . '-01')->format('F Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Pay Date:</th>
                                    <td>{{ $payroll->pay_date ? \Carbon\Carbon::parse($payroll->pay_date)->format('d M Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge bg-{{ $payroll->status === 'paid' ? 'success' : ($payroll->status === 'approved' ? 'info' : ($payroll->status === 'reviewed' ? 'warning' : 'primary')) }}">
                                            {{ ucfirst($payroll->status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Processed By:</th>
                                    <td>{{ $payroll->processor->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                @if($payroll->reviewer)
                                <tr>
                                    <th width="40%">Reviewed By:</th>
                                    <td>{{ $payroll->reviewer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Reviewed At:</th>
                                    <td>{{ $payroll->reviewed_at ? $payroll->reviewed_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                </tr>
                                @endif
                                @if($payroll->approver)
                                <tr>
                                    <th>Approved By:</th>
                                    <td>{{ $payroll->approver->name }}</td>
                                </tr>
                                <tr>
                                    <th>Approved At:</th>
                                    <td>{{ $payroll->approved_at ? $payroll->approved_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                </tr>
                                @endif
                                @if($payroll->payer)
                                <tr>
                                    <th>Paid By:</th>
                                    <td>{{ $payroll->payer->name }}</td>
                                </tr>
                                <tr>
                                    <th>Paid At:</th>
                                    <td>{{ $payroll->paid_at ? $payroll->paid_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    @if($payroll->review_notes)
                    <div class="alert alert-info mt-3">
                        <strong>Review Notes:</strong> {{ $payroll->review_notes }}
                    </div>
                    @endif
                    @if($payroll->approval_notes)
                    <div class="alert alert-info mt-3">
                        <strong>Approval Notes:</strong> {{ $payroll->approval_notes }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Payroll Details Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Employee Payroll Details</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Employee Details</th>
                                    <th>Department</th>
                                    <th class="text-end">Basic Salary</th>
                                    <th class="text-end">Overtime</th>
                                    <th class="text-end">Bonus</th>
                                    <th class="text-end">Allowance</th>
                                    <th class="text-end">Gross Salary</th>
                                    <th class="text-end">PAYE</th>
                                    <th class="text-end">NSSF</th>
                                    <th class="text-end">NHIF</th>
                                    <th class="text-end">HESLB</th>
                                    <th class="text-end">Other Ded.</th>
                                    <th class="text-end">Net Pay</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payroll->items as $index => $item)
                                @php
                                    $employee = $item->employee;
                                    $gross = $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
                                    $itemDeductions = $item->paye_amount + $item->nssf_amount + $item->nhif_amount + $item->heslb_amount + $item->wcf_amount + $item->sdl_amount + $item->deduction_amount + ($item->other_deductions ?? 0);
                                    $deductionPercent = $gross > 0 ? ($itemDeductions / $gross) * 100 : 0;
                                    $netPercent = $gross > 0 ? ($item->net_salary / $gross) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $employee->name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">ID: {{ $employee->employee->employee_id ?? 'N/A' }}</small>
                                            @if($employee->bankAccounts && $employee->bankAccounts->count() > 0)
                                                @php $primaryBank = $employee->bankAccounts->where('is_primary', true)->first() ?? $employee->bankAccounts->first(); @endphp
                                                <br><small class="text-info" title="Bank Account">
                                                    <i class="bx bx-credit-card me-1"></i>{{ $primaryBank->bank_name ?? 'Bank' }}: {{ substr($primaryBank->account_number ?? '', -4) }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $employee->primaryDepartment->name ?? 'N/A' }}</strong>
                                        @if($employee->salaryDeductions && $employee->salaryDeductions->count() > 0)
                                            @php
                                                $fixedTotal = 0;
                                                foreach($employee->salaryDeductions as $ded) {
                                                    if($ded->frequency === 'monthly' || ($ded->frequency === 'one-time' && $ded->start_date <= now() && (!$ded->end_date || $ded->end_date >= now()))) {
                                                        $fixedTotal += $ded->amount;
                                                    }
                                                }
                                            @endphp
                                            <br><small class="text-warning" title="Fixed Deductions">
                                                <i class="bx bx-money me-1"></i>Fixed: TZS {{ number_format($fixedTotal, 0) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($item->basic_salary, 2) }}
                                        <br><small class="text-muted">{{ $gross > 0 ? number_format(($item->basic_salary / $gross) * 100, 1) : 0 }}%</small>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($item->overtime_amount, 2) }}
                                        @if($item->overtime_hours > 0)
                                        <br><small class="text-muted">{{ number_format($item->overtime_hours, 1) }} hrs</small>
                                        @endif
                                    </td>
                                    <td class="text-end">{{ number_format($item->bonus_amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->allowance_amount, 2) }}</td>
                                    <td class="text-end fw-bold text-success">
                                        {{ number_format($gross, 2) }}
                                        <br><small class="text-muted">{{ $totals['gross_salary'] > 0 ? number_format(($gross / $totals['gross_salary']) * 100, 2) : 0 }}%</small>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($item->paye_amount, 2) }}
                                        <br><small class="text-muted">{{ $gross > 0 ? number_format(($item->paye_amount / $gross) * 100, 1) : 0 }}%</small>
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($item->nssf_amount, 2) }}
                                        <br><small class="text-muted">{{ $gross > 0 ? number_format(($item->nssf_amount / $gross) * 100, 1) : 0 }}%</small>
                                    </td>
                                    <td class="text-end">{{ number_format($item->nhif_amount, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->heslb_amount, 2) }}</td>
                                    <td class="text-end">
                                        {{ number_format($item->deduction_amount + ($item->other_deductions ?? 0), 2) }}
                                        <br><small class="text-muted">{{ number_format($deductionPercent, 1) }}%</small>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        {{ number_format($item->net_salary, 2) }}
                                        <br><small class="text-muted">{{ number_format($netPercent, 1) }}%</small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            <a href="{{ route('payroll.payslip.page', $item->id) }}" class="btn btn-outline-primary btn-sm mb-1" target="_blank" title="View Payslip">
                                                <i class="bx bx-show"></i> View
                                            </a>
                                            <a href="{{ route('payroll.payslip.pdf', $item->id) }}" class="btn btn-outline-danger btn-sm" target="_blank" title="Download PDF">
                                                <i class="bx bx-file"></i> PDF
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                                <tr class="table-light border-top border-3 fw-bold">
                                    <td colspan="3" class="text-end"><strong>TOTALS:</strong></td>
                                    <td class="text-end">
                                        <strong>TZS {{ number_format($totals['basic_salary'], 2) }}</strong>
                                        <br><small class="text-muted">100.00%</small>
                                    </td>
                                    <td class="text-end">
                                        <strong>TZS {{ number_format($totals['overtime_amount'], 2) }}</strong>
                                        <br><small class="text-muted">{{ number_format($totals['overtime_hours'], 1) }} hrs</small>
                                    </td>
                                    <td class="text-end"><strong>TZS {{ number_format($totals['bonus_amount'], 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($totals['allowance_amount'], 2) }}</strong></td>
                                    <td class="text-end">
                                        <strong class="text-success">TZS {{ number_format($totals['gross_salary'], 2) }}</strong>
                                        <br><small class="text-muted">100.00%</small>
                                    </td>
                                    <td class="text-end">
                                        <strong>TZS {{ number_format($totals['paye_amount'], 2) }}</strong>
                                        <br><small class="text-muted">{{ number_format($percentages['paye_to_gross'], 2) }}%</small>
                                    </td>
                                    <td class="text-end">
                                        <strong>TZS {{ number_format($totals['nssf_amount'], 2) }}</strong>
                                        <br><small class="text-muted">{{ number_format($percentages['nssf_to_gross'], 2) }}%</small>
                                    </td>
                                    <td class="text-end"><strong>TZS {{ number_format($totals['nhif_amount'], 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($totals['heslb_amount'], 2) }}</strong></td>
                                    <td class="text-end">
                                        <strong class="text-danger">TZS {{ number_format($totals['deduction_amount'] + $totals['other_deductions'], 2) }}</strong>
                                        <br><small class="text-muted">{{ number_format($percentages['deductions_to_gross'], 2) }}%</small>
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success">TZS {{ number_format($totals['net_salary'], 2) }}</strong>
                                        <br><small class="text-muted">{{ number_format($percentages['net_to_gross'], 2) }}%</small>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr class="table-secondary fw-bold">
                                    <td colspan="3" class="text-end"><strong>AVERAGES:</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($averages['basic_salary'], 2) }}</strong></td>
                                    <td class="text-end">
                                        <strong>TZS {{ number_format($employeeCount > 0 ? $totals['overtime_amount'] / $employeeCount : 0, 2) }}</strong>
                                        <br><small class="text-muted">{{ number_format($averages['overtime_hours'], 1) }} hrs</small>
                                    </td>
                                    <td class="text-end"><strong>TZS {{ number_format($employeeCount > 0 ? $totals['bonus_amount'] / $employeeCount : 0, 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($employeeCount > 0 ? $totals['allowance_amount'] / $employeeCount : 0, 2) }}</strong></td>
                                    <td class="text-end"><strong class="text-success">TZS {{ number_format($averages['gross_salary'], 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($employeeCount > 0 ? $totals['paye_amount'] / $employeeCount : 0, 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($employeeCount > 0 ? $totals['nssf_amount'] / $employeeCount : 0, 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($employeeCount > 0 ? $totals['nhif_amount'] / $employeeCount : 0, 2) }}</strong></td>
                                    <td class="text-end"><strong>TZS {{ number_format($employeeCount > 0 ? $totals['heslb_amount'] / $employeeCount : 0, 2) }}</strong></td>
                                    <td class="text-end"><strong class="text-danger">TZS {{ number_format($averages['total_deductions'], 2) }}</strong></td>
                                    <td class="text-end"><strong class="text-success">TZS {{ number_format($averages['net_salary'], 2) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

