<div class="payslip-details-view">
    @php
        $payslip = $payslipData;
        $payroll = $payslip['payroll'];
        $employee = $payslip['employee'];
        
        $gross = $payslip['basic_salary'] + $payslip['overtime_amount'] + $payslip['bonus_amount'] + $payslip['allowance_amount'];
        $deductions = $payslip['nssf_amount'] + $payslip['nhif_amount'] + $payslip['heslb_amount'] + $payslip['paye_amount'] + $payslip['wcf_amount'] + $payslip['deduction_amount'] + $payslip['other_deductions'];
    @endphp

    <!-- Payroll Information Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #940000 0%, #d63384 100%);">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 text-white">
                        <i class="bx bx-calendar-check me-2"></i>Pay Period: {{ $payroll['pay_period'] ?? 'N/A' }}
                    </h5>
                    <small class="text-white-50">
                        @if($payroll['pay_date'])
                            Pay Date: {{ \Carbon\Carbon::parse($payroll['pay_date'])->format('F j, Y') }}
                        @endif
                        @if($payroll['processed_by'])
                            | Processed by: {{ $payroll['processed_by'] }}
                        @endif
                    </small>
                </div>
                <div>
                    <span class="badge bg-light text-dark fs-6">
                        Status: {{ ucfirst($payroll['status'] ?? 'processed') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="bx bx-trending-up me-2"></i>EARNINGS</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <tbody>
                        <tr>
                            <td><i class="bx bx-money me-2 text-muted"></i>Basic Salary</td>
                            <td class="text-end"><strong class="text-success">TZS {{ number_format($payslip['basic_salary'] ?? 0, 0) }}</strong></td>
                        </tr>
                        @if(($payslip['overtime_amount'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-time me-2 text-muted"></i>Overtime Pay</td>
                            <td class="text-end"><strong class="text-success">+ TZS {{ number_format($payslip['overtime_amount'], 0) }}</strong></td>
                        </tr>
                        @endif
                        @if(($payslip['bonus_amount'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-gift me-2 text-muted"></i>Bonus & Incentives</td>
                            <td class="text-end"><strong class="text-success">+ TZS {{ number_format($payslip['bonus_amount'], 0) }}</strong></td>
                        </tr>
                        @endif
                        @if(($payslip['allowance_amount'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-dollar me-2 text-muted"></i>Allowances</td>
                            <td class="text-end"><strong class="text-success">+ TZS {{ number_format($payslip['allowance_amount'], 0) }}</strong></td>
                        </tr>
                        @endif
                        <tr class="table-success">
                            <td><strong><i class="bx bx-calculator me-2"></i>TOTAL GROSS SALARY</strong></td>
                            <td class="text-end"><strong class="fs-5 text-success">TZS {{ number_format($gross, 0) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deductions Card -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-danger text-white">
            <h6 class="mb-0"><i class="bx bx-trending-down me-2"></i>DEDUCTIONS</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <tbody>
                        @if(($payslip['paye_amount'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-receipt me-2 text-muted"></i>PAYE Tax</td>
                            <td class="text-end"><strong class="text-danger">- TZS {{ number_format($payslip['paye_amount'], 0) }}</strong></td>
                        </tr>
                        @endif
                        @if(($payslip['nssf_amount'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-shield me-2 text-muted"></i>NSSF Contribution</td>
                            <td class="text-end"><strong class="text-danger">- TZS {{ number_format($payslip['nssf_amount'], 0) }}</strong></td>
                        </tr>
                        @endif
                        @if(($payslip['nhif_amount'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-plus-medical me-2 text-muted"></i>NHIF Contribution</td>
                            <td class="text-end"><strong class="text-danger">- TZS {{ number_format($payslip['nhif_amount'], 0) }}</strong></td>
                        </tr>
                        @endif
                        @if(($payslip['heslb_amount'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-book me-2 text-muted"></i>HESLB Loan</td>
                            <td class="text-end"><strong class="text-danger">- TZS {{ number_format($payslip['heslb_amount'], 0) }}</strong></td>
                        </tr>
                        @endif
                        @if(($payslip['wcf_amount'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-buildings me-2 text-muted"></i>WCF Contribution</td>
                            <td class="text-end"><strong class="text-danger">- TZS {{ number_format($payslip['wcf_amount'], 0) }}</strong></td>
                        </tr>
                        @endif
                        @if(($payslip['deduction_amount'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-minus-circle me-2 text-muted"></i>Other Deductions</td>
                            <td class="text-end"><strong class="text-danger">- TZS {{ number_format($payslip['deduction_amount'], 0) }}</strong></td>
                        </tr>
                        @endif
                        @if(($payslip['other_deductions'] ?? 0) > 0)
                        <tr>
                            <td><i class="bx bx-minus-circle me-2 text-muted"></i>Additional Deductions</td>
                            <td class="text-end"><strong class="text-danger">- TZS {{ number_format($payslip['other_deductions'], 0) }}</strong></td>
                        </tr>
                        @endif
                        <tr class="table-danger">
                            <td><strong><i class="bx bx-calculator me-2"></i>TOTAL DEDUCTIONS</strong></td>
                            <td class="text-end"><strong class="fs-5 text-danger">- TZS {{ number_format($deductions, 0) }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Net Salary Highlight Card -->
    <div class="card border-0 shadow-lg mb-3" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
        <div class="card-body text-center text-white p-4">
            <h6 class="mb-3 text-white-50"><i class="bx bx-money me-2"></i>NET SALARY PAYABLE</h6>
            <h1 class="mb-3 fw-bold" style="font-size: 2.5rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">TZS {{ number_format($payslip['net_salary'] ?? 0, 0) }}</h1>
            <div class="row mt-4">
                <div class="col-md-4">
                    <small class="text-white-50 d-block">Gross Salary</small>
                    <strong class="fs-5">TZS {{ number_format($gross, 0) }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-white-50 d-block">Total Deductions</small>
                    <strong class="fs-5">- TZS {{ number_format($deductions, 0) }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-white-50 d-block">Net Pay</small>
                    <strong class="fs-5">TZS {{ number_format($payslip['net_salary'] ?? 0, 0) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Breakdown -->
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-3"><i class="bx bx-info-circle me-2"></i>Quick Summary</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Gross Earnings:</span>
                        <strong class="text-success">TZS {{ number_format($gross, 0) }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Deductions:</span>
                        <strong class="text-danger">- TZS {{ number_format($deductions, 0) }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted fw-bold">Net Pay:</span>
                        <strong class="text-primary fs-5">TZS {{ number_format($payslip['net_salary'] ?? 0, 0) }}</strong>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-3"><i class="bx bx-user me-2"></i>Employee Details</h6>
                    <div class="mb-2">
                        <small class="text-muted d-block">Employee Name</small>
                        <strong>{{ $employee['name'] ?? 'N/A' }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="mb-2">
                        <small class="text-muted d-block">Employee ID</small>
                        <strong>{{ $employee['employee_id'] ?? 'N/A' }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="mb-2">
                        <small class="text-muted d-block">Department</small>
                        <strong>{{ $employee['department'] ?? 'N/A' }}</strong>
                    </div>
                    <hr class="my-2">
                    <div>
                        <small class="text-muted d-block">Position</small>
                        <strong>{{ $employee['position'] ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>






