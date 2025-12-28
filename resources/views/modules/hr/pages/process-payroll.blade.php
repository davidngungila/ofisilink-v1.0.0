@extends('layouts.app')

@section('title', 'Process New Payroll - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Enhanced Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-calculator me-2"></i>Process New Payroll
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Select employees, configure earnings and deductions, then process payroll for review
                            </p>
                        </div>
                        <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light btn-lg shadow-sm">
                            <i class="bx bx-arrow-back me-2"></i>Back to Payroll
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="processPayrollForm" action="{{ route('payroll.process') }}" method="POST">
        @csrf
        
        <!-- Enhanced Basic Information Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="mb-0 text-white fw-bold">
                            <i class="bx bx-calendar me-2"></i>Payroll Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="pay_period" class="form-label fw-semibold">
                                    <i class="bx bx-calendar-check me-1"></i>Pay Period <span class="text-danger">*</span>
                                </label>
                                <input type="month" class="form-control form-control-lg" id="pay_period" name="pay_period" value="{{ $selectedMonth ?? '' }}" required>
                                <small class="text-muted">Select the month for this payroll period</small>
                            </div>
                            <div class="col-md-4">
                                <label for="pay_date" class="form-label fw-semibold">
                                    <i class="bx bx-calendar me-1"></i>Pay Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-lg" id="pay_date" name="pay_date" required>
                                <small class="text-muted">Expected payment date for employees</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bx bx-cog me-1"></i>Manage Monthly Records
                                </label>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('payroll.overtime.index', ['month' => $selectedMonth ?? now()->format('Y-m')]) }}" class="btn btn-primary btn-sm" target="_blank">
                                        <i class="bx bx-time me-1"></i>Overtime
                                    </a>
                                    <a href="{{ route('payroll.bonus.index', ['month' => $selectedMonth ?? now()->format('Y-m')]) }}" class="btn btn-success btn-sm" target="_blank">
                                        <i class="bx bx-gift me-1"></i>Bonus
                                    </a>
                                    <a href="{{ route('payroll.allowance.index', ['month' => $selectedMonth ?? now()->format('Y-m')]) }}" class="btn btn-info btn-sm" target="_blank">
                                        <i class="bx bx-money me-1"></i>Allowance
                                    </a>
                                </div>
                                <small class="text-muted">Manage monthly records before processing</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Statistics Dashboard -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-light border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-bar-chart-alt-2 me-2"></i>Real-Time Payroll Statistics
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                                    <div class="card-body text-center p-3">
                                        <div class="avatar avatar-lg mx-auto mb-2 bg-primary">
                                            <i class="bx bx-user-check fs-3 text-white"></i>
                                        </div>
                                        <div class="fw-bold text-primary fs-3" id="stat-selected">0</div>
                                        <small class="text-muted d-block">Selected Employees</small>
                                        <small class="text-muted" id="stat-total-employees">of {{ $employees->count() }} total</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                                    <div class="card-body text-center p-3">
                                        <div class="avatar avatar-lg mx-auto mb-2 bg-success">
                                            <i class="bx bx-trending-up fs-3 text-white"></i>
                                        </div>
                                        <div class="fw-bold text-success fs-4" id="stat-gross">0</div>
                                        <small class="text-muted d-block">Gross Salary</small>
                                        <small class="text-muted" id="stat-gross-percent">0%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);">
                                    <div class="card-body text-center p-3">
                                        <div class="avatar avatar-lg mx-auto mb-2 bg-danger">
                                            <i class="bx bx-minus-circle fs-3 text-white"></i>
                                        </div>
                                        <div class="fw-bold text-danger fs-4" id="stat-deductions">0</div>
                                        <small class="text-muted d-block">Total Deductions</small>
                                        <small class="text-muted" id="stat-deductions-percent">0%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);">
                                    <div class="card-body text-center p-3">
                                        <div class="avatar avatar-lg mx-auto mb-2 bg-info">
                                            <i class="bx bx-check-circle fs-3 text-white"></i>
                                        </div>
                                        <div class="fw-bold text-info fs-4" id="stat-net">0</div>
                                        <small class="text-muted d-block">Net Salary</small>
                                        <small class="text-muted" id="stat-net-percent">0%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);">
                                    <div class="card-body text-center p-3">
                                        <div class="avatar avatar-lg mx-auto mb-2 bg-warning">
                                            <i class="bx bx-building fs-3 text-white"></i>
                                        </div>
                                        <div class="fw-bold text-warning fs-4" id="stat-employer">0</div>
                                        <small class="text-muted d-block">Employer Cost</small>
                                        <small class="text-muted" id="stat-employer-percent">0%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6">
                                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);">
                                    <div class="card-body text-center p-3">
                                        <div class="avatar avatar-lg mx-auto mb-2 bg-purple">
                                            <i class="bx bx-bar-chart fs-3 text-white"></i>
                                        </div>
                                        <div class="fw-bold text-purple fs-4" id="stat-avg">0</div>
                                        <small class="text-muted d-block">Average Net</small>
                                        <small class="text-muted">Per Employee</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Employee Selection Card -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h5 class="mb-0 text-white fw-bold">
                                <i class="bx bx-user me-2"></i>Employee Selection
                            </h5>
                            <div class="btn-group btn-group-sm mt-2 mt-md-0">
                                <button type="button" class="btn btn-light" id="select-all">
                                    <i class="bx bx-check-square me-1"></i>Select All
                                </button>
                                <button type="button" class="btn btn-light" id="deselect-all">
                                    <i class="bx bx-x me-1"></i>Deselect All
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <!-- Search and Filter Bar -->
                        <div class="p-3 bg-light border-bottom">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <i class="bx bx-search"></i>
                                        </span>
                                        <input type="text" class="form-control" id="employee-search" placeholder="Search by name or employee ID...">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="department-filter">
                                        <option value="">All Departments</option>
                                        @php
                                            $departments = $employees->pluck('primaryDepartment')->filter()->unique('id');
                                        @endphp
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="status-filter">
                                        <option value="all">All Employees</option>
                                        <option value="selected">Selected Only</option>
                                        <option value="unselected">Unselected Only</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-secondary w-100" id="clear-filters">
                                        <i class="bx bx-refresh me-1"></i>Clear
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <span id="filtered-count">{{ $employees->count() }}</span> of <span id="total-count">{{ $employees->count() }}</span> employees shown
                                </small>
                            </div>
                        </div>
                        <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                            <table class="table table-hover table-sm">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th width="120">Basic Salary</th>
                                        <th width="100">Overtime (Hrs)</th>
                                        <th width="120">Bonus</th>
                                        <th width="120">Allowance</th>
                                        <th width="120">Deductions</th>
                                        <th width="150">Statutory</th>
                                        <th width="120">Net Salary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $employee)
                                        <tr class="employee-row" 
                                            data-employee-id="{{ $employee->id }}"
                                            data-employee-name="{{ strtolower($employee->name ?? '') }}"
                                            data-employee-id-value="{{ strtolower($employee->employee->employee_id ?? '') }}"
                                            data-department-id="{{ $employee->primaryDepartment->id ?? '' }}"
                                            data-department-name="{{ strtolower($employee->primaryDepartment->name ?? '') }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" 
                                                       class="form-check-input employee-checkbox" id="emp_{{ $employee->id }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr($employee->name ?? 'N', 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold small">{{ $employee->name ?? 'N/A' }}</div>
                                                        <small class="text-muted">{{ $employee->employee->employee_id ?? 'N/A' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <small>{{ $employee->primaryDepartment->name ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <input type="hidden" name="basic_salary[{{ $employee->id }}]" 
                                                       value="{{ $employee->employee->salary ?? 0 }}" 
                                                       class="salary-input">
                                                <div class="fw-bold text-primary">{{ number_format($employee->employee->salary ?? 0, 0) }}</div>
                                            </td>
                                            <td>
                                                @php
                                                    $overtime = $overtimes[$employee->id] ?? null;
                                                    $overtimeHours = $overtime ? $overtime->hours : 0;
                                                    $overtimeAmount = $overtime ? $overtime->amount : 0;
                                                @endphp
                                                <input type="hidden" name="overtime_hours[{{ $employee->id }}]" 
                                                       value="{{ $overtimeHours }}" 
                                                       class="overtime-input" 
                                                       data-employee-id="{{ $employee->id }}"
                                                       data-overtime-amount="{{ $overtimeAmount }}">
                                                <div class="fw-semibold">{{ number_format($overtimeHours, 2) }} hrs</div>
                                                @if($overtimeAmount > 0)
                                                    <small class="text-success">TZS {{ number_format($overtimeAmount, 0) }}</small>
                                                @else
                                                    <small class="text-muted">No overtime</small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $bonus = $bonuses[$employee->id] ?? null;
                                                    $bonusAmount = $bonus ? $bonus->amount : 0;
                                                @endphp
                                                <input type="hidden" name="bonus_amount[{{ $employee->id }}]" 
                                                       value="{{ $bonusAmount }}" 
                                                       class="bonus-input" 
                                                       data-employee-id="{{ $employee->id }}">
                                                @if($bonusAmount > 0)
                                                    <div class="fw-semibold text-success">TZS {{ number_format($bonusAmount, 0) }}</div>
                                                    @if($bonus && $bonus->bonus_type)
                                                        <small class="text-muted">{{ $bonus->bonus_type }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $allowance = $allowances[$employee->id] ?? null;
                                                    $allowanceAmount = $allowance ? $allowance->amount : 0;
                                                @endphp
                                                <input type="hidden" name="allowance_amount[{{ $employee->id }}]" 
                                                       value="{{ $allowanceAmount }}" 
                                                       class="allowance-input" 
                                                       data-employee-id="{{ $employee->id }}">
                                                @if($allowanceAmount > 0)
                                                    <div class="fw-semibold text-info">TZS {{ number_format($allowanceAmount, 0) }}</div>
                                                    @if($allowance && $allowance->allowance_type)
                                                        <small class="text-muted">{{ $allowance->allowance_type }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="number" name="deduction_amount[{{ $employee->id }}]" 
                                                       value="0" step="1000" min="0"
                                                       class="form-control form-control-sm deduction-input" 
                                                       data-employee-id="{{ $employee->id }}"
                                                       placeholder="Other">
                                                @if($employee->salaryDeductions && $employee->salaryDeductions->count() > 0)
                                                    @php
                                                        $applicableDeductions = [];
                                                        $fixedTotal = 0;
                                                        foreach($employee->salaryDeductions as $ded) {
                                                            if($ded->is_active && ($ded->frequency === 'monthly' || ($ded->frequency === 'one-time' && $ded->start_date <= now() && (!$ded->end_date || $ded->end_date >= now())))) {
                                                                // Exclude statutory deductions (they're shown in statutory column)
                                                                if(!in_array($ded->deduction_type, ['PAYE', 'NSSF', 'NHIF', 'HESLB', 'WCF', 'SDL'])) {
                                                                    $applicableDeductions[] = [
                                                                        'type' => $ded->deduction_type,
                                                                        'amount' => $ded->amount
                                                                    ];
                                                                    $fixedTotal += $ded->amount;
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    @if(count($applicableDeductions) > 0)
                                                        <div class="mt-1 employee-fixed-deductions" data-fixed-deductions="{{ $fixedTotal }}" data-employee-id="{{ $employee->id }}">
                                                            @foreach($applicableDeductions as $ded)
                                                                <small class="d-block text-info" style="font-size: 0.75rem;">
                                                                    <strong>{{ $ded['type'] }}:</strong> {{ number_format($ded['amount'], 0) }}
                                                                </small>
                                                            @endforeach
                                                            <small class="d-block text-primary fw-bold mt-1" style="font-size: 0.75rem;">
                                                                Total: {{ number_format($fixedTotal, 0) }}
                                                            </small>
                                                        </div>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                <div class="statutory-deductions text-center" data-employee-id="{{ $employee->id }}">
                                                    @php
                                                        $statutoryTypes = ['PAYE', 'NSSF', 'NHIF', 'HESLB', 'WCF', 'SDL'];
                                                        $storedStatutory = collect();
                                                        $storedTotal = 0;
                                                        
                                                        if($employee->salaryDeductions && $employee->salaryDeductions->whereIn('deduction_type', $statutoryTypes)->count() > 0) {
                                                            $storedStatutory = $employee->salaryDeductions->whereIn('deduction_type', $statutoryTypes)
                                                                ->where('is_active', true)
                                                                ->where(function($q) {
                                                                    $q->where('frequency', 'monthly')
                                                                      ->orWhere(function($q2) {
                                                                          $q2->where('frequency', 'one-time')
                                                                             ->where('start_date', '<=', now())
                                                                             ->where(function($q3) {
                                                                                 $q3->whereNull('end_date')->orWhere('end_date', '>=', now());
                                                                             });
                                                                      });
                                                                });
                                                            $storedTotal = $storedStatutory->sum('amount');
                                                        }
                                                        
                                                        $storedTypes = $storedStatutory->pluck('deduction_type')->toArray();
                                                        $missingTypes = array_diff($statutoryTypes, $storedTypes);
                                                    @endphp
                                                    <div class="small">
                                                        @if($storedStatutory->count() > 0)
                                                            @foreach($storedStatutory as $ded)
                                                                <div class="text-success mb-1">
                                                                    <strong>{{ $ded->deduction_type }}:</strong> {{ number_format($ded->amount, 0) }}
                                                                </div>
                                                            @endforeach
                                                            @if(count($missingTypes) > 0)
                                                                <div class="mt-2 pt-2 border-top">
                                                                    <small class="text-muted d-block mb-1">Missing: {{ implode(', ', $missingTypes) }}</small>
                                                                    <small class="text-info">Manage from Deduction Management page</small>
                                                                </div>
                                                            @endif
                                                            <div class="fw-bold mt-2 text-success">Total: {{ number_format($storedTotal, 0) }}</div>
                                                        @else
                                                            <div class="text-muted mb-2">No statutory deductions stored</div>
                                                            <small class="text-info">Manage from Deduction Management page</small>
                                                        @endif
                                                    </div>
                                                    <div class="employee-stored-statutory d-none" data-stored-statutory="{{ $storedTotal }}" data-employee-id="{{ $employee->id }}"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="estimated-net fw-bold text-success" data-employee-id="{{ $employee->id }}">
                                                    {{ number_format($employee->employee->salary ?? 0, 0) }}
                                                </div>
                                                <small class="text-muted d-block" id="net-percent-{{ $employee->id }}">-</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light border-top">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bx bx-arrow-back me-1"></i>Cancel
                            </a>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-info btn-lg" id="preview-btn">
                                    <i class="bx bx-show me-1"></i>Preview Summary
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="process-btn">
                                    <i class="bx bx-calculator me-1"></i>Process Payroll
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Set defaults
    const currentDate = new Date();
    const currentMonth = currentDate.getFullYear() + '-' + String(currentDate.getMonth() + 1).padStart(2, '0');
    $('#pay_period').val(currentMonth);
    
    const nextMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 5);
    $('#pay_date').val(nextMonth.toISOString().split('T')[0]);
    
    // Select all functionality
    $('#selectAll').change(function() {
        $('.employee-checkbox').prop('checked', this.checked);
        updateStats();
    });
    
    $('#select-all').click(function() {
        $('.employee-checkbox, #selectAll').prop('checked', true);
        updateStats();
    });
    
    $('#deselect-all').click(function() {
        $('.employee-checkbox, #selectAll').prop('checked', false);
        updateStats();
    });
    
    // Individual checkbox
    $(document).on('change', '.employee-checkbox', function() {
        updateStats();
        updateSelectAll();
    });
    
    // Input changes - update both employee net and overall stats (only deduction-input is editable now)
    $(document).on('input change', '.deduction-input', function() {
        const employeeId = $(this).data('employee-id');
        if (employeeId) {
            updateEmployeeNet(employeeId);
        }
        updateStats();
    });
    
    // Initialize calculations on page load since overtime, bonus, allowance are pre-calculated
    $(document).ready(function() {
        $('.employee-row').each(function() {
            const employeeId = $(this).data('employee-id');
            if (employeeId) {
                updateEmployeeNet(employeeId);
            }
        });
    });
    
    // Update employee net salary in real-time
    function updateEmployeeNet(employeeId) {
        const row = $(`.employee-row[data-employee-id="${employeeId}"]`);
        if (!row.length) return;
        
        const basicSalary = parseFloat(row.find('.salary-input').val()) || 0;
        const overtimeInput = row.find('.overtime-input');
        const overtimeHours = parseFloat(overtimeInput.val()) || 0;
        // Get pre-calculated overtime amount from data attribute
        const overtimeAmount = parseFloat(overtimeInput.attr('data-overtime-amount')) || 0;
        const bonus = parseFloat(row.find('.bonus-input').val()) || 0;
        const allowance = parseFloat(row.find('.allowance-input').val()) || 0;
        const deduction = parseFloat(row.find('.deduction-input').val()) || 0;
        const fixedDeductionsEl = row.find('.employee-fixed-deductions');
        const fixedDeductions = fixedDeductionsEl.length ? parseFloat(fixedDeductionsEl.attr('data-fixed-deductions')) || 0 : 0;
        
        // Calculate gross salary (overtime amount is already calculated from monthly records)
        const gross = basicSalary + overtimeAmount + bonus + allowance;
        
        // Get statutory deductions
        const storedStatutoryEl = row.find('.employee-stored-statutory');
        let statutoryTotal = 0;
        
        if (storedStatutoryEl.length) {
            statutoryTotal = parseFloat(storedStatutoryEl.attr('data-stored-statutory')) || 0;
        }
        
        // If no stored amount, try to extract from text
        if (statutoryTotal === 0) {
            const statutoryText = row.find('.statutory-deductions').text();
            const totalMatch = statutoryText.match(/Total:\s*([\d,]+)/i);
            if (totalMatch) {
                statutoryTotal = parseFloat(totalMatch[1].replace(/,/g, '')) || 0;
            }
        }
        
        // Calculate total deductions and net
        const totalDeductions = statutoryTotal + deduction + fixedDeductions;
        const net = gross - totalDeductions;
        
        // Update the net salary display
        const netElement = row.find('.estimated-net');
        netElement.text(formatNumber(net));
        netElement.removeClass('text-success text-danger').addClass(net >= 0 ? 'text-success' : 'text-danger');
        
        // Update percentage of gross
        const grossPercent = gross > 0 ? ((net / gross) * 100).toFixed(1) : 0;
        const percentElement = row.find(`#net-percent-${employeeId}`);
        if (percentElement.length) {
            percentElement.text(`${grossPercent}% of gross`);
            percentElement.removeClass('text-success text-danger text-muted').addClass(
                grossPercent >= 70 ? 'text-success' : (grossPercent >= 50 ? 'text-warning' : 'text-danger')
            );
        }
    }
    
    
    // Update select all checkbox
    function updateSelectAll() {
        const total = $('.employee-checkbox').length;
        const checked = $('.employee-checkbox:checked').length;
        $('#selectAll').prop('checked', total === checked);
    }
    
    // Format number
    function formatNumber(num) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(num || 0);
    }
    
    
    // Form submission
    $('#processPayrollForm').submit(function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const selectedCount = $('.employee-checkbox:checked').length;
        console.log('Selected employees:', selectedCount);
        
        if (selectedCount === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Employees Selected',
                text: 'Please select at least one employee to process payroll.'
            });
            return false;
        }
        
        // Validate required fields
        const payPeriod = $('#pay_period').val();
        const payDate = $('#pay_date').val();
        
        if (!payPeriod || !payDate) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please fill in Pay Period and Pay Date.'
            });
            return false;
        }
        
        console.log('Submitting payroll:', { payPeriod, payDate, selectedCount });
        
        Swal.fire({
            title: 'Processing Payroll',
            text: `Processing payroll for ${selectedCount} employee(s)...`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Submit form
        const formData = new FormData(this);
        console.log('Form data prepared, submitting...');
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            timeout: 120000, // 2 minutes timeout
            success: function(response) {
                console.log('Payroll processing response:', response);
                if (response.success) {
                    let message = response.message || 'Payroll has been processed successfully and sent for review.';
                    
                    // Show employee errors if any
                    if (response.has_errors && response.employee_errors && Object.keys(response.employee_errors).length > 0) {
                        let errorMsg = '\n\nSome employees had errors:\n';
                        Object.entries(response.employee_errors).forEach(([empId, errors]) => {
                            const empName = $(`.employee-row[data-employee-id="${empId}"]`).find('td').eq(1).text().trim() || `Employee ID: ${empId}`;
                            errorMsg += `\n${empName}:\n`;
                            if (Array.isArray(errors)) {
                                errors.forEach(err => {
                                    errorMsg += `  - ${err}\n`;
                                });
                            } else {
                                errorMsg += `  - ${errors}\n`;
                            }
                        });
                        message += errorMsg;
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'Payroll Processed with Warnings',
                            html: message.replace(/\n/g, '<br>'),
                            width: '600px',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route("modules.hr.payroll") }}';
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Payroll Processed',
                            text: message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = '{{ route("modules.hr.payroll") }}';
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Processing Failed',
                        text: response.message || 'An error occurred while processing payroll.'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Payroll processing error:', { xhr, status, error });
                let message = 'An error occurred while processing payroll.';
                const response = xhr.responseJSON;
                
                if (response) {
                    if (response.message) {
                        message = response.message;
                    }
                    
                    // Handle validation errors
                    if (xhr.status === 422 && response.errors) {
                        const errorList = Object.values(response.errors).flat().join('\n');
                        message = 'Validation errors:\n' + errorList;
                    }
                    
                    // Handle employee-specific errors
                    if (response.employee_errors && Object.keys(response.employee_errors).length > 0) {
                        let employeeErrorMsg = '\n\nEmployee Errors:\n';
                        Object.entries(response.employee_errors).forEach(([empId, errors]) => {
                            const empName = $(`.employee-row[data-employee-id="${empId}"]`).find('td').eq(1).text().trim() || `Employee ID: ${empId}`;
                            employeeErrorMsg += `\n${empName}:\n`;
                            if (Array.isArray(errors)) {
                                errors.forEach(err => {
                                    employeeErrorMsg += `  - ${err}\n`;
                                });
                            } else {
                                employeeErrorMsg += `  - ${errors}\n`;
                            }
                        });
                        message += employeeErrorMsg;
                    }
                } else if (xhr.status === 0) {
                    message = 'Network error. Please check your connection and try again.';
                } else if (xhr.status === 500) {
                    message = 'Server error occurred. Please contact support.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Processing Failed',
                    html: message.replace(/\n/g, '<br>'),
                    width: '600px'
                });
            }
        });
    });
    
    // Employee Search and Filter
    $('#employee-search').on('input', function() {
        filterEmployees();
    });
    
    $('#department-filter, #status-filter').on('change', function() {
        filterEmployees();
    });
    
    $('#clear-filters').on('click', function() {
        $('#employee-search').val('');
        $('#department-filter').val('');
        $('#status-filter').val('all');
        filterEmployees();
    });
    
    function filterEmployees() {
        const searchTerm = $('#employee-search').val().toLowerCase();
        const departmentFilter = $('#department-filter').val();
        const statusFilter = $('#status-filter').val();
        
        let visibleCount = 0;
        
        $('.employee-row').each(function() {
            const $row = $(this);
            const employeeName = $row.data('employee-name') || '';
            const employeeId = $row.data('employee-id-value') || '';
            const departmentId = $row.data('department-id') || '';
            const isSelected = $row.find('.employee-checkbox').is(':checked');
            
            const matchesSearch = !searchTerm || 
                employeeName.includes(searchTerm) || 
                employeeId.includes(searchTerm);
            
            const matchesDepartment = !departmentFilter || departmentId == departmentFilter;
            
            const matchesStatus = statusFilter === 'all' ||
                (statusFilter === 'selected' && isSelected) ||
                (statusFilter === 'unselected' && !isSelected);
            
            if (matchesSearch && matchesDepartment && matchesStatus) {
                $row.show();
                visibleCount++;
            } else {
                $row.hide();
            }
        });
        
        $('#filtered-count').text(visibleCount);
    }
    
    // Enhanced Preview Summary Modal
    $('#preview-btn').on('click', function() {
        const selected = $('.employee-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Employees Selected',
                text: 'Please select at least one employee to preview.',
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            });
            return;
        }
        
        // Collect all employee data
        let employees = [];
        let totalGross = 0, totalDeductions = 0, totalNet = 0, totalEmployerCost = 0;
        let totalBasic = 0, totalOvertime = 0, totalBonus = 0, totalAllowance = 0;
        let totalStatutory = 0, totalOtherDeductions = 0;
        let departmentBreakdown = {};
        
        selected.each(function() {
            const employeeId = $(this).val();
            const row = $(`.employee-row[data-employee-id="${employeeId}"]`);
            const employeeName = row.find('td').eq(1).find('.fw-semibold').text().trim();
            const employeeIdValue = row.find('td').eq(1).find('small').text().trim();
            const department = row.find('td').eq(2).text().trim();
            const basicSalary = parseFloat(row.find('.salary-input').val()) || 0;
            const overtimeHours = parseFloat(row.find('.overtime-input').val()) || 0;
            const bonus = parseFloat(row.find('.bonus-input').val()) || 0;
            const allowance = parseFloat(row.find('.allowance-input').val()) || 0;
            const deduction = parseFloat(row.find('.deduction-input').val()) || 0;
            const fixedDeductionsEl = row.find('.employee-fixed-deductions');
            const fixedDeductions = fixedDeductionsEl.length ? parseFloat(fixedDeductionsEl.attr('data-fixed-deductions')) || 0 : 0;
            
            const hourlyRate = basicSalary / (22 * 8);
            const overtimeAmount = overtimeHours * hourlyRate * 1.5;
            const gross = basicSalary + overtimeAmount + bonus + allowance;
            
            const storedStatutoryEl = row.find('.employee-stored-statutory');
            const statutoryTotal = storedStatutoryEl.length ? parseFloat(storedStatutoryEl.attr('data-stored-statutory')) || 0 : 0;
            
            const deductions = statutoryTotal + deduction + fixedDeductions;
            const net = gross - deductions;
            const employerCost = gross * 1.095;
            
            employees.push({
                name: employeeName,
                id: employeeIdValue,
                department: department,
                basic: basicSalary,
                overtime: overtimeAmount,
                overtimeHours: overtimeHours,
                bonus: bonus,
                allowance: allowance,
                gross: gross,
                statutory: statutoryTotal,
                otherDeductions: deduction + fixedDeductions,
                deductions: deductions,
                net: net,
                employerCost: employerCost
            });
            
            totalBasic += basicSalary;
            totalOvertime += overtimeAmount;
            totalBonus += bonus;
            totalAllowance += allowance;
            totalGross += gross;
            totalStatutory += statutoryTotal;
            totalOtherDeductions += (deduction + fixedDeductions);
            totalDeductions += deductions;
            totalNet += net;
            totalEmployerCost += employerCost;
            
            // Department breakdown
            if (!departmentBreakdown[department]) {
                departmentBreakdown[department] = {
                    count: 0,
                    gross: 0,
                    deductions: 0,
                    net: 0
                };
            }
            departmentBreakdown[department].count++;
            departmentBreakdown[department].gross += gross;
            departmentBreakdown[department].deductions += deductions;
            departmentBreakdown[department].net += net;
        });
        
        const avgGross = totalGross / employees.length;
        const avgNet = totalNet / employees.length;
        const avgDeductions = totalDeductions / employees.length;
        const deductionPercent = totalGross > 0 ? ((totalDeductions / totalGross) * 100).toFixed(2) : 0;
        const netPercent = totalGross > 0 ? ((totalNet / totalGross) * 100).toFixed(2) : 0;
        
        // Build comprehensive preview HTML
        let previewHtml = `
            <div style="max-height: 70vh; overflow-y: auto;">
                <!-- Summary Statistics Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                            <div class="card-body text-center p-3">
                                <div class="fw-bold text-primary fs-4">${employees.length}</div>
                                <small class="text-muted">Employees</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                            <div class="card-body text-center p-3">
                                <div class="fw-bold text-success fs-4">TZS ${formatNumber(totalGross)}</div>
                                <small class="text-muted">Total Gross</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);">
                            <div class="card-body text-center p-3">
                                <div class="fw-bold text-danger fs-4">TZS ${formatNumber(totalDeductions)}</div>
                                <small class="text-muted">Total Deductions (${deductionPercent}%)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);">
                            <div class="card-body text-center p-3">
                                <div class="fw-bold text-info fs-4">TZS ${formatNumber(totalNet)}</div>
                                <small class="text-muted">Total Net (${netPercent}%)</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Earnings Breakdown -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-trending-up me-2"></i>Earnings Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">% of Gross</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Basic Salary</strong></td>
                                        <td class="text-end">TZS ${formatNumber(totalBasic)}</td>
                                        <td class="text-end">${totalGross > 0 ? ((totalBasic / totalGross) * 100).toFixed(2) : 0}%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Overtime Pay</strong></td>
                                        <td class="text-end">TZS ${formatNumber(totalOvertime)}</td>
                                        <td class="text-end">${totalGross > 0 ? ((totalOvertime / totalGross) * 100).toFixed(2) : 0}%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bonus & Incentives</strong></td>
                                        <td class="text-end">TZS ${formatNumber(totalBonus)}</td>
                                        <td class="text-end">${totalGross > 0 ? ((totalBonus / totalGross) * 100).toFixed(2) : 0}%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Allowances</strong></td>
                                        <td class="text-end">TZS ${formatNumber(totalAllowance)}</td>
                                        <td class="text-end">${totalGross > 0 ? ((totalAllowance / totalGross) * 100).toFixed(2) : 0}%</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td><strong>Total Gross Salary</strong></td>
                                        <td class="text-end fw-bold">TZS ${formatNumber(totalGross)}</td>
                                        <td class="text-end fw-bold">100.00%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Deductions Breakdown -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="bx bx-minus-circle me-2"></i>Deductions Breakdown</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">% of Gross</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Statutory Deductions</strong></td>
                                        <td class="text-end text-danger">TZS ${formatNumber(totalStatutory)}</td>
                                        <td class="text-end">${totalGross > 0 ? ((totalStatutory / totalGross) * 100).toFixed(2) : 0}%</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Other Deductions</strong></td>
                                        <td class="text-end text-danger">TZS ${formatNumber(totalOtherDeductions)}</td>
                                        <td class="text-end">${totalGross > 0 ? ((totalOtherDeductions / totalGross) * 100).toFixed(2) : 0}%</td>
                                    </tr>
                                    <tr class="table-danger">
                                        <td><strong>Total Deductions</strong></td>
                                        <td class="text-end fw-bold text-danger">TZS ${formatNumber(totalDeductions)}</td>
                                        <td class="text-end fw-bold">${deductionPercent}%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Employee Details Table -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="bx bx-user me-2"></i>Employee Details</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th class="text-end">Basic</th>
                                        <th class="text-end">Overtime</th>
                                        <th class="text-end">Bonus</th>
                                        <th class="text-end">Allowance</th>
                                        <th class="text-end">Gross</th>
                                        <th class="text-end">Deductions</th>
                                        <th class="text-end">Net</th>
                                        <th class="text-end">% of Gross</th>
                                    </tr>
                                </thead>
                                <tbody>`;
        
        employees.forEach(emp => {
            const netPercent = emp.gross > 0 ? ((emp.net / emp.gross) * 100).toFixed(1) : 0;
            previewHtml += `
                                    <tr>
                                        <td>
                                            <strong>${escapeHtml(emp.name)}</strong><br>
                                            <small class="text-muted">${escapeHtml(emp.id)}</small>
                                        </td>
                                        <td>${escapeHtml(emp.department)}</td>
                                        <td class="text-end">${formatNumber(emp.basic)}</td>
                                        <td class="text-end">${formatNumber(emp.overtime)}<br><small class="text-muted">(${emp.overtimeHours}h)</small></td>
                                        <td class="text-end">${formatNumber(emp.bonus)}</td>
                                        <td class="text-end">${formatNumber(emp.allowance)}</td>
                                        <td class="text-end fw-bold text-primary">${formatNumber(emp.gross)}</td>
                                        <td class="text-end text-danger">${formatNumber(emp.deductions)}</td>
                                        <td class="text-end fw-bold text-success">${formatNumber(emp.net)}</td>
                                        <td class="text-end"><span class="badge bg-info">${netPercent}%</span></td>
                                    </tr>`;
        });
        
        previewHtml += `
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th class="text-end">${formatNumber(totalBasic)}</th>
                                        <th class="text-end">${formatNumber(totalOvertime)}</th>
                                        <th class="text-end">${formatNumber(totalBonus)}</th>
                                        <th class="text-end">${formatNumber(totalAllowance)}</th>
                                        <th class="text-end fw-bold text-primary">${formatNumber(totalGross)}</th>
                                        <th class="text-end fw-bold text-danger">${formatNumber(totalDeductions)}</th>
                                        <th class="text-end fw-bold text-success">${formatNumber(totalNet)}</th>
                                        <th class="text-end"><span class="badge bg-primary">${netPercent}%</span></th>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Average</th>
                                        <th class="text-end">${formatNumber(totalBasic / employees.length)}</th>
                                        <th class="text-end">${formatNumber(totalOvertime / employees.length)}</th>
                                        <th class="text-end">${formatNumber(totalBonus / employees.length)}</th>
                                        <th class="text-end">${formatNumber(totalAllowance / employees.length)}</th>
                                        <th class="text-end fw-bold">${formatNumber(avgGross)}</th>
                                        <th class="text-end fw-bold">${formatNumber(avgDeductions)}</th>
                                        <th class="text-end fw-bold">${formatNumber(avgNet)}</th>
                                        <th class="text-end">-</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Department Breakdown -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="bx bx-building me-2"></i>Department Breakdown</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Department</th>
                                        <th class="text-center">Employees</th>
                                        <th class="text-end">Gross</th>
                                        <th class="text-end">Deductions</th>
                                        <th class="text-end">Net</th>
                                        <th class="text-end">% of Total</th>
                                    </tr>
                                </thead>
                                <tbody>`;
        
        Object.keys(departmentBreakdown).sort().forEach(dept => {
            const deptData = departmentBreakdown[dept];
            const deptPercent = totalNet > 0 ? ((deptData.net / totalNet) * 100).toFixed(1) : 0;
            previewHtml += `
                                    <tr>
                                        <td><strong>${escapeHtml(dept)}</strong></td>
                                        <td class="text-center"><span class="badge bg-primary">${deptData.count}</span></td>
                                        <td class="text-end">${formatNumber(deptData.gross)}</td>
                                        <td class="text-end text-danger">${formatNumber(deptData.deductions)}</td>
                                        <td class="text-end fw-bold text-success">${formatNumber(deptData.net)}</td>
                                        <td class="text-end"><span class="badge bg-info">${deptPercent}%</span></td>
                                    </tr>`;
        });
        
        previewHtml += `
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-center"><span class="badge bg-primary">${employees.length}</span></th>
                                        <th class="text-end fw-bold">${formatNumber(totalGross)}</th>
                                        <th class="text-end fw-bold text-danger">${formatNumber(totalDeductions)}</th>
                                        <th class="text-end fw-bold text-success">${formatNumber(totalNet)}</th>
                                        <th class="text-end"><span class="badge bg-primary">100%</span></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Summary Statistics -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);">
                            <div class="card-body text-center p-3">
                                <div class="fw-bold text-warning fs-5">TZS ${formatNumber(totalEmployerCost)}</div>
                                <small class="text-muted">Total Employer Cost</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);">
                            <div class="card-body text-center p-3">
                                <div class="fw-bold text-purple fs-5">TZS ${formatNumber(avgNet)}</div>
                                <small class="text-muted">Average Net Pay</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%);">
                            <div class="card-body text-center p-3">
                                <div class="fw-bold text-teal fs-5">${deductionPercent}%</div>
                                <small class="text-muted">Deduction Ratio</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        Swal.fire({
            title: '<div class="text-center"><h4 class="mb-1"><i class="bx bx-bar-chart-alt-2 me-2"></i>Payroll Preview Summary</h4><small class="text-muted">Comprehensive breakdown of selected employees</small></div>',
            html: previewHtml,
            width: '95%',
            maxWidth: '1400px',
            confirmButtonText: '<i class="bx bx-check me-1"></i>Close',
            confirmButtonColor: '#667eea',
            customClass: {
                container: 'swal2-container',
                popup: 'swal2-popup',
                title: 'swal2-title',
                htmlContainer: 'swal2-html-container',
                confirmButton: 'swal2-confirm'
            },
            didOpen: () => {
                // Ensure it's on top
                const swalContainer = document.querySelector('.swal2-container');
                if (swalContainer) {
                    swalContainer.style.zIndex = '99999';
                }
            }
        });
    });
    
    // Update statistics with percentages
    function updateStats() {
        const selected = $('.employee-checkbox:checked');
        const count = selected.length;
        const totalEmployees = {{ $employees->count() }};
        
        $('#stat-selected').text(count);
        $('#stat-total-employees').text(`of ${totalEmployees} total`);
        
        if (count === 0) {
            $('#stat-gross, #stat-deductions, #stat-net, #stat-employer, #stat-avg').text('0');
            $('#stat-gross-percent, #stat-deductions-percent, #stat-net-percent, #stat-employer-percent').text('0%');
            return;
        }
        
        let totalGross = 0, totalDeductions = 0, totalNet = 0, totalEmployer = 0;
        
        selected.each(function() {
            const employeeId = $(this).val();
            const row = $(`.employee-row[data-employee-id="${employeeId}"]`);
            const basicSalary = parseFloat(row.find('.salary-input').val()) || 0;
            const overtimeHours = parseFloat(row.find('.overtime-input').val()) || 0;
            const bonus = parseFloat(row.find('.bonus-input').val()) || 0;
            const allowance = parseFloat(row.find('.allowance-input').val()) || 0;
            const deduction = parseFloat(row.find('.deduction-input').val()) || 0;
            const fixedDeductionsEl = row.find('.employee-fixed-deductions');
            const fixedDeductions = fixedDeductionsEl.length ? parseFloat(fixedDeductionsEl.attr('data-fixed-deductions')) || 0 : 0;
            
            const hourlyRate = basicSalary / (22 * 8);
            const overtimeAmount = overtimeHours * hourlyRate * 1.5;
            const gross = basicSalary + overtimeAmount + bonus + allowance;
            
            const storedStatutoryEl = row.find('.employee-stored-statutory');
            const statutoryTotal = storedStatutoryEl.length ? parseFloat(storedStatutoryEl.attr('data-stored-statutory')) || 0 : 0;
            
            const deductions = statutoryTotal + deduction + fixedDeductions;
            const net = gross - deductions;
            const employerCost = gross * 1.095;
            
            totalGross += gross;
            totalDeductions += deductions;
            totalNet += net;
            totalEmployer += employerCost;
        });
        
        const deductionPercent = totalGross > 0 ? ((totalDeductions / totalGross) * 100).toFixed(1) : 0;
        const netPercent = totalGross > 0 ? ((totalNet / totalGross) * 100).toFixed(1) : 0;
        const employerPercent = totalGross > 0 ? (((totalEmployer - totalGross) / totalGross) * 100).toFixed(1) : 0;
        
        $('#stat-gross').text(formatNumber(totalGross));
        $('#stat-deductions').text(formatNumber(totalDeductions));
        $('#stat-net').text(formatNumber(totalNet));
        $('#stat-employer').text(formatNumber(totalEmployer));
        $('#stat-avg').text(formatNumber(count > 0 ? totalNet / count : 0));
        
        $('#stat-deductions-percent').text(`${deductionPercent}% of gross`);
        $('#stat-net-percent').text(`${netPercent}% of gross`);
        $('#stat-employer-percent').text(`+${employerPercent}%`);
        $('#stat-gross-percent').text(`${count} employees`);
    }
    
    // Add CSS for better styling
    $('head').append(`
        <style>
            .text-purple { color: #a855f7 !important; }
            .text-teal { color: #00897b !important; }
            .employee-row:hover { background-color: #f8f9fa !important; }
            .employee-row.selected { background-color: #e7f3ff !important; }
            .card { transition: all 0.3s ease; }
            .card:hover { transform: translateY(-2px); }
            .avatar { transition: all 0.3s ease; }
            
            /* Ensure SweetAlert2 appears above all modals */
            .swal2-container {
                z-index: 99999 !important;
            }
            .swal2-popup {
                z-index: 100000 !important;
            }
            .swal2-backdrop-show {
                z-index: 99998 !important;
            }
            .swal2-html-container {
                max-height: 70vh;
                overflow-y: auto;
            }
        </style>
    `);
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Highlight selected rows
    $(document).on('change', '.employee-checkbox', function() {
        const $row = $(this).closest('.employee-row');
        if ($(this).is(':checked')) {
            $row.addClass('selected');
        } else {
            $row.removeClass('selected');
        }
        filterEmployees();
    });
    
    // Initialize - update all employee net salaries on page load
    $('.employee-row').each(function() {
        const employeeId = $(this).data('employee-id');
        if (employeeId) {
            updateEmployeeNet(employeeId);
        }
    });
    
    updateStats();
    filterEmployees();
});
</script>
@endpush
@endsection
