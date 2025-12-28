<div class="payroll-details-view">
    <!-- Payroll Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 text-white">
                        <i class="bx bx-calendar me-2"></i>Payroll: {{ $payroll->pay_period }}
                    </h5>
                    <small class="text-white-50">
                        Pay Date: {{ $payroll->pay_date ? \Carbon\Carbon::parse($payroll->pay_date)->format('F j, Y') : 'N/A' }}
                        @if($payroll->processor)
                            | Processed by: {{ $payroll->processor->name }}
                        @endif
                        @if($payroll->approver)
                            | Approved by: {{ $payroll->approver->name }}
                        @endif
                    </small>
                </div>
                <div>
                    <span class="badge bg-light text-dark fs-6">
                        Status: {{ ucfirst($payroll->status) }}
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="border-end">
                        <h6 class="text-muted mb-2">Total Employees</h6>
                        <h4 class="text-primary">{{ $items->count() }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border-end">
                        <h6 class="text-muted mb-2">Total Gross</h6>
                        <h4 class="text-success">TZS {{ number_format($totals['gross_salary'], 0) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border-end">
                        <h6 class="text-muted mb-2">Total Deductions</h6>
                        <h4 class="text-danger">TZS {{ number_format($totals['total_deductions'], 0) }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <h6 class="text-muted mb-2">Total Net Pay</h6>
                    <h4 class="text-success">TZS {{ number_format($totals['net_salary'], 0) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Employee Details Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Employee Payroll Details</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Department</th>
                            <th class="text-end">Basic</th>
                            <th class="text-end">Overtime</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Allowance</th>
                            <th class="text-end">Gross</th>
                            <th class="text-end">PAYE</th>
                            <th class="text-end">NSSF</th>
                            <th class="text-end">NHIF</th>
                            <th class="text-end">HESLB</th>
                            <th class="text-end">Ded.</th>
                            <th class="text-end">Net</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $index => $item)
                        @php
                            $employee = $item->employee;
                            $gross = $item->basic_salary + $item->overtime_amount + $item->bonus_amount + $item->allowance_amount;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div>
                                    <strong>{{ $employee->name ?? 'N/A' }}</strong><br>
                                    <small class="text-muted">{{ $employee->employee_id ?? 'N/A' }}</small>
                                    @if($employee->bankAccounts && $employee->bankAccounts->count() > 0)
                                        @php $primaryBank = $employee->bankAccounts->where('is_primary', true)->first() ?? $employee->bankAccounts->first(); @endphp
                                        <br><small class="text-info" title="Bank Account">
                                            <i class="bx bx-credit-card me-1"></i>{{ $primaryBank->bank_name ?? 'Bank' }}: {{ substr($primaryBank->account_number ?? '', -4) }}
                                        </small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                {{ $employee->primaryDepartment->name ?? 'N/A' }}
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
                                        <i class="bx bx-money me-1"></i>Fixed: {{ number_format($fixedTotal, 0) }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($item->basic_salary, 0) }}</td>
                            <td class="text-end">{{ number_format($item->overtime_amount, 0) }}</td>
                            <td class="text-end">{{ number_format($item->bonus_amount, 0) }}</td>
                            <td class="text-end">{{ number_format($item->allowance_amount, 0) }}</td>
                            <td class="text-end fw-bold">{{ number_format($gross, 0) }}</td>
                            <td class="text-end">{{ number_format($item->paye_amount, 0) }}</td>
                            <td class="text-end">{{ number_format($item->nssf_amount, 0) }}</td>
                            <td class="text-end">{{ number_format($item->nhif_amount, 0) }}</td>
                            <td class="text-end">{{ number_format($item->heslb_amount, 0) }}</td>
                            <td class="text-end">{{ number_format($item->deduction_amount, 0) }}</td>
                            <td class="text-end fw-bold text-success">{{ number_format($item->net_salary, 0) }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewPayslip({{ $item->id }})">
                                    View
                                </button>
                                <a href="{{ route('payroll.payslip.pdf', $item->id) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                    PDF
                                </a>
                            </td>
                        </tr>
                        @endforeach
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">TOTALS:</td>
                            <td class="text-end">{{ number_format($totals['basic_salary'], 0) }}</td>
                            <td class="text-end">{{ number_format($totals['overtime_amount'], 0) }}</td>
                            <td class="text-end">{{ number_format($totals['bonus_amount'], 0) }}</td>
                            <td class="text-end">{{ number_format($totals['allowance_amount'], 0) }}</td>
                            <td class="text-end">{{ number_format($totals['gross_salary'], 0) }}</td>
                            <td class="text-end">{{ number_format($totals['paye_amount'], 0) }}</td>
                            <td class="text-end">{{ number_format($totals['nssf_amount'], 0) }}</td>
                            <td class="text-end">{{ number_format($totals['nhif_amount'], 0) }}</td>
                            <td class="text-end">{{ number_format($totals['heslb_amount'], 0) }}</td>
                            <td class="text-end">{{ number_format($totals['deduction_amount'], 0) }}</td>
                            <td class="text-end text-success">{{ number_format($totals['net_salary'], 0) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

