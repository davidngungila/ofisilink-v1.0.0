@extends('layouts.app')

@section('title', 'Payroll Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Professional Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-calculator me-2"></i>Payroll Management System
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive payroll processing, approvals, and payments with advanced statutory calculations
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($pageMode === 'manager' && $can_process_payroll)
                            <a href="{{ route('payroll.process.page') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-plus-circle me-2"></i>Process New Payroll
                            </a>
                            <a href="{{ route('payroll.deductions.index') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-money-withdraw me-2"></i>Manage Deductions
                            </a>
                            <a href="{{ route('payroll.overtime.index') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-time-five me-2"></i>Manage Overtime
                            </a>
                            <a href="{{ route('payroll.bonus.index') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-gift me-2"></i>Manage Bonus
                            </a>
                            <a href="{{ route('payroll.allowance.index') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-money me-2"></i>Manage Allowance
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($pageMode === 'manager')
        <!-- Advanced Statistics Dashboard -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3 bg-primary">
                                <i class="bx bx-calculator fs-2 text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1 small">Total Processed</h6>
                                <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_processed'] ?? 0 }}</h3>
                                <small class="text-success">
                                    <i class="bx bx-trending-up me-1"></i>All Time
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                <i class="bx bx-time fs-2 text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1 small">Pending Review</h6>
                                <h3 class="mb-0 fw-bold text-warning">{{ $stats['pending_review'] ?? 0 }}</h3>
                                <small class="text-warning">
                                    <i class="bx bx-info-circle me-1"></i>Awaiting HOD
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                <i class="bx bx-check-circle fs-2 text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1 small">Pending Approval</h6>
                                <h3 class="mb-0 fw-bold text-info">{{ $stats['pending_approval'] ?? 0 }}</h3>
                                <small class="text-info">
                                    <i class="bx bx-user-check me-1"></i>Awaiting CEO
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                <i class="bx bx-dollar fs-2 text-white"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1 small">Approved Unpaid</h6>
                                <h3 class="mb-0 fw-bold text-success">{{ $stats['approved_unpaid'] ?? 0 }}</h3>
                                <small class="text-success">
                                    <i class="bx bx-check-double me-1"></i>Ready for Payment
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics Row -->
        @php
            $currentMonthTotal = $stats['current_month_total'] ?? 0;
            $currentMonthEmployerCost = $stats['current_month_employer_cost'] ?? 0;
            $currentMonthGross = $stats['current_month_gross'] ?? 0;
            $currentMonthDeductions = $stats['current_month_deductions'] ?? 0;
            $lastMonthTotal = $stats['last_month_total'] ?? 0;
            $yearToDateTotal = $stats['year_to_date_total'] ?? 0;
            $yearToDateEmployerCost = $stats['year_to_date_employer_cost'] ?? 0;
            $lastYearTotal = $stats['last_year_total'] ?? 0;
            $totalAllTimeNet = $stats['total_all_time_net'] ?? 0;
            $totalAllTimeEmployerCost = $stats['total_all_time_employer_cost'] ?? 0;
            $averageMonthlyPayroll = $stats['average_monthly_payroll'] ?? 0;
            $paidPayrollsCount = $stats['paid_payrolls_count'] ?? 0;
            $employeesCount = $stats['employees_count'] ?? 0;
            
            // Calculate percentage changes
            $monthChange = $lastMonthTotal > 0 ? (($currentMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100 : 0;
            $yearChange = $lastYearTotal > 0 ? (($yearToDateTotal - $lastYearTotal) / $lastYearTotal) * 100 : 0;
        @endphp
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 small">Current Month Net Pay</h6>
                                <h4 class="mb-0 fw-bold text-primary">TZS {{ number_format($currentMonthTotal, 0) }}</h4>
                                @if($lastMonthTotal > 0)
                                <small class="{{ $monthChange >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bx bx-{{ $monthChange >= 0 ? 'trending-up' : 'trending-down' }} me-1"></i>
                                    {{ number_format(abs($monthChange), 1) }}% vs last month
                                </small>
                                @endif
                            </div>
                            <div class="avatar avatar-lg bg-primary bg-opacity-10">
                                <i class="bx bx-money fs-1 text-primary"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 small">Current Month Gross</h6>
                                <h4 class="mb-0 fw-bold text-warning">TZS {{ number_format($currentMonthGross, 0) }}</h4>
                                <small class="text-muted">Before deductions</small>
                            </div>
                            <div class="avatar avatar-lg" style="background: rgba(245, 158, 11, 0.1);">
                                <i class="bx bx-trending-up fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 small">Current Month Deductions</h6>
                                <h4 class="mb-0 fw-bold text-danger">TZS {{ number_format($currentMonthDeductions, 0) }}</h4>
                                <small class="text-muted">{{ $currentMonthGross > 0 ? number_format(($currentMonthDeductions / $currentMonthGross) * 100, 1) : 0 }}% of gross</small>
                            </div>
                            <div class="avatar avatar-lg" style="background: rgba(239, 68, 68, 0.1);">
                                <i class="bx bx-minus-circle fs-1 text-danger"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 small">Total Employees</h6>
                                <h4 class="mb-0 fw-bold text-purple">{{ $employeesCount }}</h4>
                                <small class="text-muted">Active Staff</small>
                            </div>
                            <div class="avatar avatar-lg" style="background: rgba(168, 85, 247, 0.1);">
                                <i class="bx bx-group fs-1 text-purple"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Year-to-Date and All-Time Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 small">Year-to-Date Net Pay</h6>
                                <h4 class="mb-0 fw-bold text-info">TZS {{ number_format($yearToDateTotal, 0) }}</h4>
                                @if($lastYearTotal > 0)
                                <small class="{{ $yearChange >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bx bx-{{ $yearChange >= 0 ? 'trending-up' : 'trending-down' }} me-1"></i>
                                    {{ number_format(abs($yearChange), 1) }}% vs last year
                                </small>
                                @else
                                <small class="text-muted">{{ date('Y') }} Total</small>
                                @endif
                            </div>
                            <div class="avatar avatar-lg" style="background: rgba(59, 130, 246, 0.1);">
                                <i class="bx bx-calendar fs-1 text-info"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 small">Year-to-Date Employer Cost</h6>
                                <h4 class="mb-0 fw-bold text-warning">TZS {{ number_format($yearToDateEmployerCost, 0) }}</h4>
                                <small class="text-muted">Total cost this year</small>
                            </div>
                            <div class="avatar avatar-lg" style="background: rgba(245, 158, 11, 0.1);">
                                <i class="bx bx-building fs-1 text-warning"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 small">All-Time Net Pay</h6>
                                <h4 class="mb-0 fw-bold text-success">TZS {{ number_format($totalAllTimeNet, 0) }}</h4>
                                <small class="text-muted">{{ $paidPayrollsCount }} paid payrolls</small>
                            </div>
                            <div class="avatar avatar-lg" style="background: rgba(16, 185, 129, 0.1);">
                                <i class="bx bx-dollar fs-1 text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2 small">Average Monthly Payroll</h6>
                                <h4 class="mb-0 fw-bold text-indigo">TZS {{ number_format($averageMonthlyPayroll, 0) }}</h4>
                                <small class="text-muted">Historical average</small>
                            </div>
                            <div class="avatar avatar-lg" style="background: rgba(99, 102, 241, 0.1);">
                                <i class="bx bx-bar-chart-alt-2 fs-1 text-indigo"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Action Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-primary text-white border-bottom">
                        <h5 class="mb-0 fw-bold text-white">
                            <i class="bx bx-bolt-circle me-2"></i>Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if($can_process_payroll)
                            <div class="col-lg-3 col-md-6">
                                <a href="{{ route('payroll.process.page') }}" class="card border-primary h-100 text-decoration-none hover-lift">
                                    <div class="card-body text-center">
                                        <div class="avatar avatar-xl mx-auto mb-3 bg-primary">
                                            <i class="bx bx-calculator fs-1 text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Process Payroll</h6>
                                        <small class="text-muted">Create new payroll</small>
                                    </div>
                                </a>
                            </div>
                            @endif
    
                            @if($can_review_payroll)
                            <div class="col-lg-3 col-md-6">
                                <a href="javascript:void(0);" onclick="viewPendingReview()" class="card border-warning h-100 text-decoration-none hover-lift">
                                    <div class="card-body text-center">
                                        <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                            <i class="bx bx-check fs-1 text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Review Payroll</h6>
                                        <small class="text-muted">Pending reviews</small>
                                    </div>
                                </a>
                            </div>
                            @endif
                            
                            @if($can_approve_payroll)
                            <div class="col-lg-3 col-md-6">
                                <a href="javascript:void(0);" onclick="viewPendingApproval()" class="card border-info h-100 text-decoration-none hover-lift">
                                    <div class="card-body text-center">
                                        <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                            <i class="bx bx-check-circle fs-1 text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Approve Payroll</h6>
                                        <small class="text-muted">Pending approvals</small>
                                    </div>
                                </a>
                            </div>
                            @endif
                            
                            @if($can_pay_payroll)
                            <div class="col-lg-3 col-md-6">
                                <a href="javascript:void(0);" onclick="viewApprovedPayroll()" class="card border-success h-100 text-decoration-none hover-lift">
                                    <div class="card-body text-center">
                                        <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                            <i class="bx bx-dollar fs-1 text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Process Payment</h6>
                                        <small class="text-muted">Ready to pay</small>
                                    </div>
                                </a>
                            </div>
                            @endif
                            
                            @if($can_process_payroll)
                            <div class="col-lg-3 col-md-6">
                                <a href="{{ route('payroll.deductions.index') }}" class="card border-primary h-100 text-decoration-none hover-lift">
                                    <div class="card-body text-center">
                                        <div class="avatar avatar-xl mx-auto mb-3 bg-primary">
                                            <i class="bx bx-money-withdraw fs-1 text-white"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1">Manage Deductions</h6>
                                        <small class="text-muted">PAYE, NSSF, NHIF, etc.</small>
                                    </div>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Analytics Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-lg">
                    <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <h5 class="mb-0 text-white fw-bold">
                            <i class="bx bx-bar-chart-alt-2 me-2"></i>Advanced Analytics & Insights
                        </h5>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-justified mb-4" id="analyticsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="trends-tab" data-bs-toggle="tab" data-bs-target="#trends" type="button" role="tab">
                                    <i class="bx bx-trending-up me-2"></i>Monthly Trends
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="departments-tab" data-bs-toggle="tab" data-bs-target="#departments" type="button" role="tab">
                                    <i class="bx bx-building me-2"></i>Department Breakdown
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="earners-tab" data-bs-toggle="tab" data-bs-target="#earners" type="button" role="tab">
                                    <i class="bx bx-user-check me-2"></i>Top Earners
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="deductions-tab" data-bs-toggle="tab" data-bs-target="#deductions" type="button" role="tab">
                                    <i class="bx bx-money-withdraw me-2"></i>Deduction Analysis
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="analyticsTabsContent">
                            <!-- Monthly Trends Tab -->
                            <div class="tab-pane fade show active" id="trends" role="tabpanel">
                                @php
                                    $monthlyTrends = $stats['monthly_trends'] ?? collect();
                                @endphp
                                @if($monthlyTrends->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Period</th>
                                                <th class="text-end">Employees</th>
                                                <th class="text-end">Gross Salary</th>
                                                <th class="text-end">Deductions</th>
                                                <th class="text-end">Net Pay</th>
                                                <th class="text-end">Employer Cost</th>
                                                <th class="text-end">Total Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($monthlyTrends as $trend)
                                            @php
                                                $totalCost = ($trend->net_total ?? 0) + ($trend->employer_cost_total ?? 0);
                                            @endphp
                                            <tr>
                                                <td><strong>{{ date('F Y', strtotime($trend->pay_period . '-01')) }}</strong></td>
                                                <td class="text-end">{{ number_format($trend->employee_count ?? 0) }}</td>
                                                <td class="text-end">TZS {{ number_format($trend->gross_total ?? 0, 0) }}</td>
                                                <td class="text-end text-danger">TZS {{ number_format($trend->deductions_total ?? 0, 0) }}</td>
                                                <td class="text-end text-success fw-bold">TZS {{ number_format($trend->net_total ?? 0, 0) }}</td>
                                                <td class="text-end text-warning">TZS {{ number_format($trend->employer_cost_total ?? 0, 0) }}</td>
                                                <td class="text-end text-primary fw-bold">TZS {{ number_format($totalCost, 0) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th>Total</th>
                                                <th class="text-end">{{ number_format($monthlyTrends->sum('employee_count')) }}</th>
                                                <th class="text-end">TZS {{ number_format($monthlyTrends->sum('gross_total'), 0) }}</th>
                                                <th class="text-end text-danger">TZS {{ number_format($monthlyTrends->sum('deductions_total'), 0) }}</th>
                                                <th class="text-end text-success fw-bold">TZS {{ number_format($monthlyTrends->sum('net_total'), 0) }}</th>
                                                <th class="text-end text-warning">TZS {{ number_format($monthlyTrends->sum('employer_cost_total'), 0) }}</th>
                                                <th class="text-end text-primary fw-bold">TZS {{ number_format($monthlyTrends->sum('net_total') + $monthlyTrends->sum('employer_cost_total'), 0) }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>No monthly trend data available yet.
                                </div>
                                @endif
                            </div>

                            <!-- Department Breakdown Tab -->
                            <div class="tab-pane fade" id="departments" role="tabpanel">
                                @php
                                    $departmentStats = $stats['department_breakdown'] ?? collect();
                                @endphp
                                @if($departmentStats->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Department</th>
                                                <th class="text-end">Employees</th>
                                                <th class="text-end">Avg Net Salary</th>
                                                <th class="text-end">Gross Total</th>
                                                <th class="text-end">Deductions</th>
                                                <th class="text-end">Net Total</th>
                                                <th class="text-end">Employer Cost</th>
                                                <th class="text-end">Total Cost</th>
                                                <th class="text-end">% of Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $grandTotalNet = $departmentStats->sum('net_total');
                                            @endphp
                                            @foreach($departmentStats as $dept)
                                            @php
                                                $totalCost = ($dept->net_total ?? 0) + ($dept->employer_cost_total ?? 0);
                                                $percentage = $grandTotalNet > 0 ? (($dept->net_total ?? 0) / $grandTotalNet) * 100 : 0;
                                            @endphp
                                            <tr>
                                                <td><strong>{{ $dept->name ?? 'Unassigned' }}</strong></td>
                                                <td class="text-end">{{ number_format($dept->employee_count ?? 0) }}</td>
                                                <td class="text-end">TZS {{ number_format($dept->avg_net_salary ?? 0, 0) }}</td>
                                                <td class="text-end">TZS {{ number_format($dept->gross_total ?? 0, 0) }}</td>
                                                <td class="text-end text-danger">TZS {{ number_format($dept->deductions_total ?? 0, 0) }}</td>
                                                <td class="text-end text-success fw-bold">TZS {{ number_format($dept->net_total ?? 0, 0) }}</td>
                                                <td class="text-end text-warning">TZS {{ number_format($dept->employer_cost_total ?? 0, 0) }}</td>
                                                <td class="text-end text-primary fw-bold">TZS {{ number_format($totalCost, 0) }}</td>
                                                <td class="text-end">
                                                    <span class="badge bg-info">{{ number_format($percentage, 1) }}%</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th>Total</th>
                                                <th class="text-end">{{ number_format($departmentStats->sum('employee_count')) }}</th>
                                                <th class="text-end">TZS {{ number_format($departmentStats->avg('avg_net_salary'), 0) }}</th>
                                                <th class="text-end">TZS {{ number_format($departmentStats->sum('gross_total'), 0) }}</th>
                                                <th class="text-end text-danger">TZS {{ number_format($departmentStats->sum('deductions_total'), 0) }}</th>
                                                <th class="text-end text-success fw-bold">TZS {{ number_format($departmentStats->sum('net_total'), 0) }}</th>
                                                <th class="text-end text-warning">TZS {{ number_format($departmentStats->sum('employer_cost_total'), 0) }}</th>
                                                <th class="text-end text-primary fw-bold">TZS {{ number_format($departmentStats->sum('net_total') + $departmentStats->sum('employer_cost_total'), 0) }}</th>
                                                <th class="text-end">100%</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>No department breakdown data available for the current month.
                                </div>
                                @endif
                            </div>

                            <!-- Top Earners Tab -->
                            <div class="tab-pane fade" id="earners" role="tabpanel">
                                @php
                                    $topEarners = $stats['top_earners'] ?? collect();
                                @endphp
                                @if($topEarners->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Rank</th>
                                                <th>Employee</th>
                                                <th>Employee ID</th>
                                                <th class="text-end">Gross Salary</th>
                                                <th class="text-end">Deductions</th>
                                                <th class="text-end">Net Salary</th>
                                                <th class="text-end">Deduction %</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($topEarners as $index => $earner)
                                            @php
                                                $deductionPercent = ($earner->gross_salary ?? 0) > 0 ? (($earner->total_deductions ?? 0) / ($earner->gross_salary ?? 1)) * 100 : 0;
                                            @endphp
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ $index < 3 ? 'primary' : 'secondary' }}">
                                                        #{{ $index + 1 }}
                                                    </span>
                                                </td>
                                                <td><strong>{{ $earner->name ?? 'N/A' }}</strong></td>
                                                <td><code>{{ $earner->employee_id ?? 'N/A' }}</code></td>
                                                <td class="text-end">TZS {{ number_format($earner->gross_salary ?? 0, 0) }}</td>
                                                <td class="text-end text-danger">TZS {{ number_format($earner->total_deductions ?? 0, 0) }}</td>
                                                <td class="text-end text-success fw-bold">TZS {{ number_format($earner->net_salary ?? 0, 0) }}</td>
                                                <td class="text-end">
                                                    <span class="badge bg-{{ $deductionPercent > 30 ? 'danger' : ($deductionPercent > 20 ? 'warning' : 'success') }}">
                                                        {{ number_format($deductionPercent, 1) }}%
                                                    </span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>No top earners data available for the current month.
                                </div>
                                @endif
                            </div>

                            <!-- Deduction Analysis Tab -->
                            <div class="tab-pane fade" id="deductions" role="tabpanel">
                                @php
                                    $deductionAnalysis = $stats['deduction_analysis'] ?? (object)[];
                                    $totalDeductions = ($deductionAnalysis->total_paye ?? 0) + 
                                                     ($deductionAnalysis->total_nssf_employee ?? 0) + 
                                                     ($deductionAnalysis->total_nhif ?? 0) + 
                                                     ($deductionAnalysis->total_heslb ?? 0) + 
                                                     ($deductionAnalysis->total_wcf ?? 0) + 
                                                     ($deductionAnalysis->total_sdl ?? 0) + 
                                                     ($deductionAnalysis->total_other_deductions ?? 0) + 
                                                     ($deductionAnalysis->total_additional_deductions ?? 0);
                                @endphp
                                @if($totalDeductions > 0)
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-primary text-white">
                                                <h6 class="mb-0"><i class="bx bx-money-withdraw me-2"></i>Statutory Deductions</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td><strong>PAYE Tax</strong></td>
                                                            <td class="text-end">TZS {{ number_format($deductionAnalysis->total_paye ?? 0, 0) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-info">{{ $totalDeductions > 0 ? number_format((($deductionAnalysis->total_paye ?? 0) / $totalDeductions) * 100, 1) : 0 }}%</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>NSSF (Employee)</strong></td>
                                                            <td class="text-end">TZS {{ number_format($deductionAnalysis->total_nssf_employee ?? 0, 0) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-info">{{ $totalDeductions > 0 ? number_format((($deductionAnalysis->total_nssf_employee ?? 0) / $totalDeductions) * 100, 1) : 0 }}%</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>NHIF</strong></td>
                                                            <td class="text-end">TZS {{ number_format($deductionAnalysis->total_nhif ?? 0, 0) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-info">{{ $totalDeductions > 0 ? number_format((($deductionAnalysis->total_nhif ?? 0) / $totalDeductions) * 100, 1) : 0 }}%</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>HESLB</strong></td>
                                                            <td class="text-end">TZS {{ number_format($deductionAnalysis->total_heslb ?? 0, 0) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-info">{{ $totalDeductions > 0 ? number_format((($deductionAnalysis->total_heslb ?? 0) / $totalDeductions) * 100, 1) : 0 }}%</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>WCF</strong></td>
                                                            <td class="text-end">TZS {{ number_format($deductionAnalysis->total_wcf ?? 0, 0) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-info">{{ $totalDeductions > 0 ? number_format((($deductionAnalysis->total_wcf ?? 0) / $totalDeductions) * 100, 1) : 0 }}%</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>SDL</strong></td>
                                                            <td class="text-end">TZS {{ number_format($deductionAnalysis->total_sdl ?? 0, 0) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-info">{{ $totalDeductions > 0 ? number_format((($deductionAnalysis->total_sdl ?? 0) / $totalDeductions) * 100, 1) : 0 }}%</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-warning text-white">
                                                <h6 class="mb-0"><i class="bx bx-list-ul me-2"></i>Other Deductions</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <td><strong>Other Deductions</strong></td>
                                                            <td class="text-end">TZS {{ number_format($deductionAnalysis->total_other_deductions ?? 0, 0) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-warning">{{ $totalDeductions > 0 ? number_format((($deductionAnalysis->total_other_deductions ?? 0) / $totalDeductions) * 100, 1) : 0 }}%</span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Additional Deductions</strong></td>
                                                            <td class="text-end">TZS {{ number_format($deductionAnalysis->total_additional_deductions ?? 0, 0) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-warning">{{ $totalDeductions > 0 ? number_format((($deductionAnalysis->total_additional_deductions ?? 0) / $totalDeductions) * 100, 1) : 0 }}%</span>
                                                            </td>
                                                        </tr>
                                                        <tr class="table-primary">
                                                            <td><strong>Total Deductions</strong></td>
                                                            <td class="text-end fw-bold">TZS {{ number_format($totalDeductions, 0) }}</td>
                                                            <td class="text-end">
                                                                <span class="badge bg-primary">100%</span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm bg-light">
                                            <div class="card-body">
                                                <div class="row text-center">
                                                    <div class="col-md-3">
                                                        <h6 class="text-muted mb-1">Total Statutory</h6>
                                                        <h4 class="text-primary fw-bold">TZS {{ number_format(($deductionAnalysis->total_paye ?? 0) + ($deductionAnalysis->total_nssf_employee ?? 0) + ($deductionAnalysis->total_nhif ?? 0) + ($deductionAnalysis->total_heslb ?? 0) + ($deductionAnalysis->total_wcf ?? 0) + ($deductionAnalysis->total_sdl ?? 0), 0) }}</h4>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <h6 class="text-muted mb-1">Total Other</h6>
                                                        <h4 class="text-warning fw-bold">TZS {{ number_format(($deductionAnalysis->total_other_deductions ?? 0) + ($deductionAnalysis->total_additional_deductions ?? 0), 0) }}</h4>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <h6 class="text-muted mb-1">NSSF Employer</h6>
                                                        <h4 class="text-info fw-bold">TZS {{ number_format($deductionAnalysis->total_nssf_employer ?? 0, 0) }}</h4>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <h6 class="text-muted mb-1">Deduction Ratio</h6>
                                                        <h4 class="text-success fw-bold">{{ $currentMonthGross > 0 ? number_format(($totalDeductions / $currentMonthGross) * 100, 1) : 0 }}%</h4>
                                                        <small class="text-muted">of Gross Salary</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i>No deduction analysis data available for the current month.
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Dashboard -->
        <div class="row">
            <div class="col-12">
                @include('modules.hr.payroll-manager')
            </div>
        </div>
    @else
        @include('modules.hr.payroll-staff')
    @endif
    
</div>

@push('styles')
<style>
.hover-lift {
    transition: all 0.3s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
.text-purple {
    color: #a855f7 !important;
}
.payroll-card {
    transition: all 0.3s ease;
}
.payroll-card:hover {
    background-color: #f8f9fa;
    transform: translateX(5px);
}
#payrollMainTabs .nav-link {
    color: #6c757d;
    border-bottom: 2px solid transparent;
}
#payrollMainTabs .nav-link.active {
    color: var(--bs-primary);
    border-bottom-color: var(--bs-primary);
    font-weight: 600;
}
#analyticsTabs .nav-link {
    color: #6c757d;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}
#analyticsTabs .nav-link:hover {
    color: var(--bs-primary);
    border-bottom-color: rgba(var(--bs-primary-rgb), 0.3);
}
#analyticsTabs .nav-link.active {
    color: var(--bs-primary);
    border-bottom-color: var(--bs-primary);
    font-weight: 600;
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}
.text-indigo {
    color: #6366f1 !important;
}
</style>
@endpush

<!-- Advanced Process Payroll Modal with Tabs -->
<div class="modal fade" id="processPayrollModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 10071;">
    <div class="modal-dialog modal-fullscreen" role="document" style="z-index: 10072;">
        <div class="modal-content shadow-lg" style="z-index: 10073; border: none;">
            <div class="modal-header bg-primary text-white" style="border-bottom: 3px solid rgba(255,255,255,0.2);">
                <div class="d-flex align-items-center w-100">
                    <div class="flex-grow-1">
                        <h5 class="modal-title text-white mb-1" id="processPayrollModalTitle">
                            <i class="bx bx-calculator me-2"></i>Process New Payroll
                        </h5>
                        <small class="text-white-50">Complete payroll processing with real-time statistics</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            
            <!-- Real-Time Statistics Dashboard (Sticky Top) -->
            <div class="bg-light border-bottom shadow-sm" id="payrollStatsDashboard" style="position: sticky; top: 0; z-index: 100; padding: 1rem;">
                <div class="row g-3">
                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-2">
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <i class="bx bx-user-circle fs-4 text-primary me-2"></i>
                                    <div>
                                        <div class="fw-bold text-primary fs-5" id="stat-selected-employees">0</div>
                                        <small class="text-muted">Selected</small>
                                    </div>
                                </div>
                                <small class="text-muted">of <span id="stat-total-employees">{{ $employees->count() ?? 0 }}</span> total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-2">
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <i class="bx bx-money fs-4 text-success me-2"></i>
                                    <div>
                                        <div class="fw-bold text-success fs-5" id="stat-total-gross">0</div>
                                        <small class="text-muted">Gross Salary</small>
                                    </div>
                                </div>
                                <small class="text-muted">TZS</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-2">
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <i class="bx bx-minus-circle fs-4 text-danger me-2"></i>
                                    <div>
                                        <div class="fw-bold text-danger fs-5" id="stat-total-deductions">0</div>
                                        <small class="text-muted">Deductions</small>
                                    </div>
                                </div>
                                <small class="text-muted">TZS</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-2">
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <i class="bx bx-check-circle fs-4 text-info me-2"></i>
                                    <div>
                                        <div class="fw-bold text-info fs-5" id="stat-total-net">0</div>
                                        <small class="text-muted">Net Salary</small>
                                    </div>
                                </div>
                                <small class="text-muted">TZS</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-2">
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <i class="bx bx-building fs-4 text-warning me-2"></i>
                                    <div>
                                        <div class="fw-bold text-warning fs-5" id="stat-total-employer-cost">0</div>
                                        <small class="text-muted">Employer Cost</small>
                                    </div>
                                </div>
                                <small class="text-muted">TZS</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body text-center p-2">
                                <div class="d-flex align-items-center justify-content-center mb-1">
                                    <i class="bx bx-bar-chart-alt-2 fs-4 text-secondary me-2"></i>
                                    <div>
                                        <div class="fw-bold text-secondary fs-5" id="stat-avg-net">0</div>
                                        <small class="text-muted">Avg Net</small>
                                    </div>
                                </div>
                                <small class="text-muted">per employee</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <form id="processPayrollForm">
                <div class="modal-body p-0">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-fill border-bottom bg-white" id="payrollTabs" role="tablist" style="position: sticky; top: 120px; z-index: 99;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-info-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab">
                                <i class="bx bx-info-circle me-1"></i>Basic Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="employees-tab" data-bs-toggle="tab" data-bs-target="#employees" type="button" role="tab">
                                <i class="bx bx-user me-1"></i>Employees 
                                <span class="badge bg-label-primary ms-1" id="selected-count">0</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab">
                                <i class="bx bx-show me-1"></i>Preview & Summary
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-4" id="payrollTabContent">
                        <!-- Basic Information Tab -->
                        <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Workflow:</strong> HR Processes → HOD Reviews → CEO Approves → Accountant Pays
                            </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pay_period" class="form-label">Pay Period <span class="text-danger">*</span></label>
                                <input type="month" class="form-control" id="pay_period" name="pay_period" required>
                                        <small class="text-muted">Select the month for this payroll period</small>
                            </div>
                        </div>


                       
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pay_date" class="form-label">Pay Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="pay_date" name="pay_date" required>
                                        <small class="text-muted">Date when salary will be paid</small>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">Payroll Guidelines</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="mb-0">
                                                <li>Overtime hours are calculated at 1.5x the hourly rate</li>
                                                <li>Statutory deductions include: PAYE, NSSF, NHIF, HESLB (if applicable), WCF, SDL</li>
                                                <li>All amounts are in Tanzanian Shillings (TZS)</li>
                                                <li>Review all employee inputs before processing</li>
                                            </ul>
                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                    
                        <!-- Employees Tab -->
                        <div class="tab-pane fade bg-primary bg-opacity-10" id="employees" role="tabpanel" style="min-height: 100%; padding: 1.5rem !important;">
                            <div class="card shadow-lg border-0" style="background: white; border-radius: 10px;">
                                <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Important:</strong> Statutory deductions (PAYE, NSSF, NHIF, HESLB, WCF, SDL) stored in Deduction Management will be used automatically. 
                                If not stored, they will be calculated using statutory formulas. This prevents duplicate deductions.
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0"><i class="bx bx-user me-2"></i>Employee Payroll Input</h6>
                                    <small class="text-muted">Select employees and enter their payroll details. Stored statutory deductions will be used automatically.</small>
                                </div>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-employees">
                                        <i class="bx bx-check-square me-1"></i>Select All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-employees">
                                        <i class="bx bx-x me-1"></i>Deselect All
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info" id="validate-all">
                                        <i class="bx bx-check-double me-1"></i>Validate All
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                                <table class="table table-bordered table-hover table-sm" id="employeeSelectionTable">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th width="40">
                                            <input type="checkbox" id="selectAllEmployees" class="form-check-input">
                                        </th>
                                            <th width="200">Employee Details</th>
                                            <th width="120">Department</th>
                                            <th width="120">Basic Salary</th>
                                            <th width="100">Overtime (Hrs)</th>
                                            <th width="120">Bonus (TZS)</th>
                                            <th width="120">Allowance (TZS)</th>
                                            <th width="120">Deductions (TZS)</th>
                                            <th width="150">Statutory Deductions</th>
                                            <th width="140">Employer Cost</th>
                                            <th width="120">Net Salary</th>
                                            <th width="100">Actions</th>
                                            <th width="200">Validation Issues</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($employees as $employee)
                                        <tr class="employee-row" data-employee-id="{{ $employee->id }}">
                                            <td class="text-center">
                                                <input type="checkbox" name="employee_ids[]" value="{{ $employee->id }}" 
                                                       class="form-check-input employee-checkbox" id="emp_{{ $employee->id }}">
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($employee->name ?? 'N', 0, 1) }}</span>
                                                </div>
                                                <div>
                                                        <h6 class="mb-0 small">{{ $employee->name ?? 'N/A' }}</h6>
                                                        <small class="text-muted">{{ $employee->employee_id ?? 'N/A' }}</small>
                                                        @if(isset($employee->employee) && $employee->employee->has_student_loan)
                                                        <br><small class="text-warning"><i class="bx bx-graduation me-1"></i>HESLB</small>
                                                        @endif
                                                        @if($employee->bankAccounts && $employee->bankAccounts->count() > 0)
                                                        @php $primaryBank = $employee->bankAccounts->where('is_primary', true)->first() ?? $employee->bankAccounts->first(); @endphp
                                                        <br><small class="text-info" title="Bank: {{ $primaryBank->bank_name ?? 'N/A' }}, Account: {{ $primaryBank->account_number ?? 'N/A' }}">
                                                            <i class="bx bx-credit-card me-1"></i>{{ $primaryBank->bank_name ?? 'Bank' }}
                                                        </small>
                                                        @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                                <small>{{ $employee->primaryDepartment->name ?? 'N/A' }}</small>
                                                @if($employee->salaryDeductions && $employee->salaryDeductions->count() > 0)
                                                <br><small class="text-muted">
                                                    <i class="bx bx-money me-1"></i>{{ $employee->salaryDeductions->count() }} deduction(s)
                                                </small>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="hidden" name="basic_salary[{{ $employee->id }}]" 
                                                   value="{{ $employee->employee->salary ?? 0 }}" 
                                                       class="salary-input" 
                                                       data-employee-id="{{ $employee->id }}">
                                                <div class="fw-bold text-primary">{{ number_format($employee->employee->salary ?? 0, 0) }}</div>
                                        </td>
                                        <td>
                                            <input type="number" name="overtime_hours[{{ $employee->id }}]" 
                                                       value="0" step="0.5" min="0" max="744"
                                                   class="form-control form-control-sm overtime-input" 
                                                       data-employee-id="{{ $employee->id }}"
                                                       placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" name="bonus_amount[{{ $employee->id }}]" 
                                                       value="0" step="1000" min="0"
                                                   class="form-control form-control-sm bonus-input" 
                                                       data-employee-id="{{ $employee->id }}"
                                                       placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" name="allowance_amount[{{ $employee->id }}]" 
                                                       value="0" step="1000" min="0"
                                                   class="form-control form-control-sm allowance-input" 
                                                       data-employee-id="{{ $employee->id }}"
                                                       placeholder="0">
                                        </td>
                                        <td>
                                            <input type="number" name="deduction_amount[{{ $employee->id }}]" 
                                                       value="0" step="1000" min="0"
                                                   class="form-control form-control-sm deduction-input" 
                                                       data-employee-id="{{ $employee->id }}"
                                                       placeholder="0">
                                            @if($employee->salaryDeductions && $employee->salaryDeductions->count() > 0)
                                            @php
                                                $fixedTotal = 0;
                                                foreach($employee->salaryDeductions as $ded) {
                                                    if($ded->frequency === 'monthly' || ($ded->frequency === 'one-time' && $ded->start_date <= now() && (!$ded->end_date || $ded->end_date >= now()))) {
                                                        $fixedTotal += $ded->amount;
                                                    }
                                                }
                                            @endphp
                                            <small class="text-info d-block mt-1" data-employee-id="{{ $employee->id }}" data-fixed-deductions="{{ $fixedTotal }}">
                                                <i class="bx bx-info-circle me-1"></i>Fixed: {{ number_format($fixedTotal, 0) }}
                                            </small>
                                            @endif
                                        </td>
                                        <td>
                                                <small class="statutory-deductions text-muted" data-employee-id="{{ $employee->id }}">
                                                    <div class="text-center">
                                                        <span class="spinner-border spinner-border-sm text-primary d-none" role="status"></span>
                                                        <span class="text-muted small">Click to calculate</span>
                                                    </div>
                                                </small>
                                                @if($employee->salaryDeductions && $employee->salaryDeductions->whereIn('deduction_type', ['PAYE', 'NSSF', 'NHIF', 'HESLB', 'WCF', 'SDL'])->count() > 0)
                                                @php
                                                    $storedStatutory = $employee->salaryDeductions->whereIn('deduction_type', ['PAYE', 'NSSF', 'NHIF', 'HESLB', 'WCF', 'SDL'])
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
                                                @endphp
                                                <div class="mt-1">
                                                    <small class="badge bg-label-success" title="Stored statutory deductions will be used">
                                                        <i class="bx bx-check-circle me-1"></i>Stored: {{ number_format($storedTotal, 0) }}
                                                    </small>
                                                </div>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="employer-cost fw-bold text-info" data-employee-id="{{ $employee->id }}">
                                                    {{ number_format($employee->employee->salary ?? 0, 0) }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="estimated-net fw-bold text-success" data-employee-id="{{ $employee->id }}">
                                                    {{ number_format($employee->employee->salary ?? 0, 0) }}
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info" onclick="showEmployeeFullDetails({{ $employee->id }})" title="View Full Details">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                            </td>
                                            <td class="validation-issues" data-employee-id="{{ $employee->id }}">
                                                <div class="employee-validation-errors" data-employee-id="{{ $employee->id }}">
                                                    <small class="text-success"><i class="bx bx-check-circle me-1"></i>Ready</small>
                                                </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                        <!-- Preview Tab -->
                        <div class="tab-pane fade" id="preview" role="tabpanel">
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="mb-3"><i class="bx bx-bar-chart-alt-2 me-2"></i>Payroll Summary & Statistics</h5>
                                </div>
                            </div>
                            
                            <!-- Detailed Statistics Cards -->
                            <div class="row g-3 mb-4" id="detailed-stats-cards">
                                <div class="col-md-3">
                                    <div class="card border-primary h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-md bg-label-primary rounded">
                                                        <i class="bx bx-money fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0 text-muted">Total Basic Salary</h6>
                                                    <h4 class="mb-0 text-primary" id="preview-total-basic">TZS 0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-md bg-label-success rounded">
                                                        <i class="bx bx-time fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0 text-muted">Overtime Amount</h6>
                                                    <h4 class="mb-0 text-success" id="preview-total-overtime">TZS 0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-info h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-md bg-label-info rounded">
                                                        <i class="bx bx-gift fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0 text-muted">Bonus & Allowance</h6>
                                                    <h4 class="mb-0 text-info" id="preview-total-bonus-allowance">TZS 0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-warning h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-md bg-label-warning rounded">
                                                        <i class="bx bx-shield fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0 text-muted">Statutory Deductions</h6>
                                                    <h4 class="mb-0 text-warning" id="preview-total-statutory">TZS 0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-danger h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-md bg-label-danger rounded">
                                                        <i class="bx bx-money-withdraw fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0 text-muted">Fixed & Other Deductions</h6>
                                                    <h4 class="mb-0 text-danger" id="preview-total-fixed-other">TZS 0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border-success h-100">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-md bg-label-success rounded">
                                                        <i class="bx bx-check-circle fs-4"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h6 class="mb-0 text-muted">Total Net Salary</h6>
                                                    <h4 class="mb-0 text-success" id="preview-total-net">TZS 0</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Breakdown Table -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="bx bx-table me-2"></i>Detailed Breakdown by Employee</h6>
                                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="updatePreviewBreakdown()">
                                                <i class="bx bx-refresh me-1"></i>Refresh
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-info mb-3">
                                                <i class="bx bx-info-circle me-2"></i>
                                                <strong>Note:</strong> Hover over deduction amounts to see detailed breakdown. All deductions (statutory, fixed, and other) are included in the calculations.
                                            </div>
                                            <div id="payroll-summary-content">
                                                <div class="alert alert-info">
                                                    <i class="bx bx-info-circle me-2"></i>Select employees and enter details to see the detailed summary.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Deduction Summary by Type -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card border-info">
                                        <div class="card-header bg-info bg-opacity-10">
                                            <h6 class="mb-0"><i class="bx bx-calculator me-2"></i>Deduction Summary by Type</h6>
                                        </div>
                                        <div class="card-body">
                                            <div id="deduction-summary-by-type">
                                                <div class="alert alert-info mb-0">
                                                    <i class="bx bx-info-circle me-2"></i>Select employees and calculate deductions to see the summary.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Additional Summary Cards -->
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning bg-opacity-10">
                                            <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Important Information</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="mb-0">
                                                <li><strong>Statutory Deductions:</strong> PAYE, NSSF, NHIF, HESLB (if applicable), WCF, SDL</li>
                                                <li><strong>Fixed Deductions:</strong> Monthly and applicable one-time deductions stored in the system</li>
                                                <li><strong>Other Deductions:</strong> Additional deductions entered manually during payroll processing</li>
                                                <li><strong>Net Salary:</strong> Gross Salary minus all deductions</li>
                                                <li>All amounts are in Tanzanian Shillings (TZS)</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="process-payroll-btn">
                        <i class="bx bx-calculator me-1"></i>Process Payroll for HOD Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payroll Details Modal -->
<div class="modal fade" id="payrollDetailsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 10072;">
    <div class="modal-dialog modal-fullscreen" role="document" style="z-index: 10073;">
        <div class="modal-content" style="z-index: 10074;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="payrollDetailsModalTitle">
                    <i class="bx bx-file-invoice me-2"></i>Payroll Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="payrollDetailsContent" style="max-height: calc(100vh - 150px); overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
            </div>
                    <p class="mt-3 text-muted">Loading payroll details...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Close
                </button>
                <a href="#" class="btn btn-danger" id="downloadPayrollReportPdf" target="_blank">
                    PDF Report
                </a>
                <button type="button" class="btn btn-success" id="exportPayrollBtn">
                    <i class="bx bx-download me-2"></i>Export Excel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payslip Modal - Advanced View -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 10073;">
    <div class="modal-dialog modal-fullscreen-lg-down" role="document" style="z-index: 10074; max-width: 95%;">
        <div class="modal-content shadow-lg border-0" style="z-index: 10075; border-radius: 10px;">
            <div class="modal-header bg-gradient-primary text-white" style="background: #940000; border-radius: 10px 10px 0 0;">
                <div class="d-flex align-items-center justify-content-between w-100">
                    <div>
                        <h4 class="modal-title text-white mb-1" id="payslipModalTitle">
                            <i class="bx bx-receipt me-2"></i><strong>Employee Payslip Details</strong>
                        </h4>
                        <small class="text-white-50">Complete payroll breakdown and calculations</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="opacity: 1;"></button>
                </div>
            </div>
            <div class="modal-body bg-light" id="payslipContent" style="max-height: calc(100vh - 200px); overflow-y: auto; padding: 25px;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted fs-5">Loading payslip details... Please wait.</p>
                </div>
            </div>
            <div class="modal-footer bg-white border-top" style="padding: 15px 25px; border-radius: 0 0 10px 10px;">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Close
                </button>
                <a href="#" class="btn btn-danger" id="downloadPayslipPdf" target="_blank" style="display: none;">
                    <i class="bx bx-file-pdf me-2"></i>Download PDF
                </a>
                <button type="button" class="btn btn-primary" id="printPayslipBtn" style="display: none;">
                    <i class="bx bx-printer me-2"></i>Print Payslip
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Employee Full Details Modal -->
<div class="modal fade" id="employeeFullDetailsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 10090 !important;">
    <div class="modal-dialog modal-xl" role="document" style="z-index: 10091 !important;">
        <div class="modal-content" style="z-index: 10092 !important;">
            <div class="modal-header bg-primary text-white" style="border-bottom: 3px solid rgba(255,255,255,0.2);">
                <h5 class="modal-title text-white">
                    <i class="bx bx-user me-2"></i>Employee Full Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="employeeFullDetailsContent" style="max-height: calc(100vh - 200px); overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading employee details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Review Payroll Modal -->
<div class="modal fade" id="reviewPayrollModal" tabindex="-1" aria-hidden="true" style="z-index: 10075;">
    <div class="modal-dialog" role="document" style="z-index: 10076;">
        <div class="modal-content" style="z-index: 10077;">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewPayrollModalTitle">Review Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewPayrollForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Review Action</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="review_action" id="approveReview" value="approve" checked>
                            <label class="form-check-label" for="approveReview">
                                Approve for CEO Review
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="review_action" id="rejectReview" value="reject">
                            <label class="form-check-label" for="rejectReview">
                                Reject - Return to HR
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="review_notes" class="form-label">Review Notes</label>
                        <textarea class="form-control" id="review_notes" name="review_notes" rows="3" placeholder="Enter your review comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check me-2"></i>Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Approve Payroll Modal -->
<div class="modal fade" id="approvePayrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvePayrollModalTitle">Approve Payroll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approvePayrollForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="approval_notes" class="form-label">Approval Notes</label>
                        <textarea class="form-control" id="approval_notes" name="approval_notes" rows="3" placeholder="Enter your approval comments..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle me-2"></i>Approve Payroll
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 10074;">
    <div class="modal-dialog modal-lg" role="document" style="z-index: 10075;">
        <div class="modal-content" style="z-index: 10076;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white" id="markPaidModalTitle">
                    <i class="bx bx-dollar me-2"></i>Mark Payroll as Paid - Double Entry Accounting
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="markPaidForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Double Entry Bookkeeping:</strong> This will create two GL entries - Debit (Expense) and Credit (Cash/Bank Account)
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" id="payment_method" name="payment_method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="payment_date" name="payment_date" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Double Entry GL Accounts Selection -->
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bx bx-book me-2"></i>General Ledger Accounts (Double Entry)
                            </h6>
                        </div>
                        <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                        <label for="debit_account_id" class="form-label">
                                            <i class="bx bx-arrow-to-left text-danger me-1"></i>Debit Account (Expense) <span class="text-danger">*</span>
                                </label>
                                        <select class="form-select" id="debit_account_id" name="debit_account_id" required>
                                            <option value="">-- Select Expense Account --</option>
                                            @if(isset($chartAccounts) && isset($chartAccounts['Expense']))
                                                @foreach($chartAccounts['Expense'] as $account)
                                                    <option value="{{ $account->id }}" 
                                                            @if(str_contains(strtolower($account->name), 'salary') || str_contains(strtolower($account->code), 'salary')) selected @endif>
                                                        {{ $account->code }} — {{ $account->name }}
                                                        @if($account->category) <small>({{ $account->category }})</small> @endif
                                                    </option>
                                    @endforeach
                                            @endif
                                </select>
                                        <small class="text-muted">Account to be debited (Salary Expense)</small>
                                </div>
                            </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="credit_account_id" class="form-label">
                                            <i class="bx bx-arrow-to-right text-success me-1"></i>Credit Account (Cash/Bank) <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="credit_account_id" name="credit_account_id" required>
                                            <option value="">-- Select Cash/Bank Account --</option>
                                            @if(isset($chartAccounts))
                                                @if(isset($chartAccounts['Asset']))
                                                    @foreach($chartAccounts['Asset'] as $account)
                                                        <option value="{{ $account->id }}">
                                                            {{ $account->code }} — {{ $account->name }}
                                                            @if($account->category) <small>({{ $account->category }})</small> @endif
                                                        </option>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </select>
                                        <small class="text-muted">Account to be credited (Cash/Bank Account)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-warning mb-0">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Note:</strong> If accounts are not listed, please create them in 
                                <a href="{{ route('modules.accounting.index') }}" target="_blank" class="alert-link">Chart of Accounts</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="transaction_ref" class="form-label">Transaction Reference</label>
                                <input type="text" class="form-control" id="transaction_ref" name="transaction_ref" placeholder="Enter transaction reference (e.g., cheque number, transfer ref)...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3" id="cashbox_field" style="display: none;">
                                <label for="cash_box_id" class="form-label">
                                    <i class="bx bx-money me-1"></i>Cash Box (Optional)
                                </label>
                                <select class="form-select" id="cash_box_id" name="cash_box_id">
                                    <option value="">-- Not Required --</option>
                                    @if(isset($cashBoxes) && $cashBoxes->count() > 0)
                                        @foreach($cashBoxes as $cb)
                                        <option value="{{ $cb->id }}" data-balance="{{ $cb->current_balance }}">
                                            {{ $cb->name }} ({{ $cb->currency ?? 'TZS' }})
                                        </option>
                                    @endforeach
                                    @endif
                                </select>
                                <small class="text-muted">Optional: For cash payments tracking</small>
                                </div>
                            </div>
                        </div>
                    
                    <div class="mb-3">
                        <label for="transaction_details" class="form-label">Transaction Details / Description</label>
                        <textarea class="form-control" id="transaction_details" name="transaction_details" rows="3" placeholder="Enter additional transaction details or notes..."></textarea>
                        <small class="text-muted">This will be included in the GL entry description</small>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle me-2"></i>Mark as Paid & Post to GL
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SweetAlert2 -->
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<style>
/* Ensure modals and popups appear on top */
.modal {
    z-index: 1050 !important;
}
.modal-backdrop {
    z-index: 1040 !important;
}
.swal2-container {
    z-index: 10050 !important;
}
.swal2-popup {
    z-index: 10051 !important;
}
.swal2-backdrop-show {
    z-index: 10049 !important;
}
.swal2-high-z-index {
    z-index: 10052 !important;
}
.swal2-container-high-z {
    z-index: 10053 !important;
}
/* Process Payroll Modal should be highest */
#processPayrollModal {
    z-index: 10010 !important;
}
#processPayrollModal .modal-backdrop {
    z-index: 10009 !important;
}
/* Payroll Details Modal */
#payrollDetailsModal {
    z-index: 10008 !important;
}
/* Payslip Modal - Highest Priority */
#payslipModal {
    z-index: 10062 !important;
}
#payslipModal .modal-backdrop {
    z-index: 10061 !important;
}
/* Ensure payslip modal appears in front of everything */
.modal.show#payslipModal {
    z-index: 10063 !important;
}
#payslipModal .modal-dialog {
    z-index: 10064 !important;
}
#payslipModal .modal-content {
    z-index: 10065 !important;
}
/* Toast notifications on top */
.toast-container {
    z-index: 10060 !important;
}
/* Bootstrap tooltips and popovers */
.tooltip {
    z-index: 10070 !important;
}
.popover {
    z-index: 10069 !important;
}
/* Deduction and Employee Details Modals - Highest Priority */
#deductionModal {
    z-index: 10090 !important;
}
#deductionModal .modal-backdrop {
    z-index: 10089 !important;
}
#bulkDeductionModal {
    z-index: 10091 !important;
}
#bulkDeductionModal .modal-backdrop {
    z-index: 10090 !important;
}
#employeeDeductionsModal {
    z-index: 10092 !important;
}
#employeeDeductionsModal .modal-backdrop {
    z-index: 10091 !important;
}
#employeeFullDetailsModal {
    z-index: 10090 !important;
}
#employeeFullDetailsModal .modal-backdrop {
    z-index: 10089 !important;
}
.modal.show#employeeFullDetailsModal {
    z-index: 10090 !important;
}
.modal.show#deductionModal {
    z-index: 10090 !important;
}
/* Ensure validation errors are visible */
.employee-row.table-danger {
    background-color: #fee2e2 !important;
    border: 2px solid #dc2626 !important;
}
.employee-row.table-warning {
    background-color: #fef3c7 !important;
    border: 2px solid #f59e0b !important;
}
.validation-errors {
    background: #fee2e2;
    padding: 8px;
    border-radius: 4px;
    border-left: 4px solid #dc2626;
}
</style>
<script>
// Ensure Swal is available globally with high z-index
if (typeof Swal !== 'undefined') {
    // Configure SweetAlert2 to always appear on top
    Swal.mixin({
        customClass: {
            container: 'swal2-container-high-z',
            popup: 'swal2-high-z-index'
        },
        didOpen: (popup) => {
            const swalContainer = popup.closest('.swal2-container');
            if (swalContainer) {
                swalContainer.style.zIndex = '10054';
            }
        }
    });
} else {
    console.error('SweetAlert2 not loaded!');
}

$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    let calculationQueue = [];
    let isCalculating = false;
    
    // Verify Swal is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 failed to load. Using fallback.');
        window.Swal = {
            fire: function(optsOrTitle, text, icon) {
                if (typeof optsOrTitle === 'object') {
                    const title = optsOrTitle.title || '';
                    const html = optsOrTitle.html || optsOrTitle.text || '';
                    // Create visible alert with high z-index
                    const alertDiv = $('<div>').css({
                        'position': 'fixed',
                        'top': '50%',
                        'left': '50%',
                        'transform': 'translate(-50%, -50%)',
                        'z-index': '10060',
                        'background': 'white',
                        'padding': '20px',
                        'border-radius': '8px',
                        'box-shadow': '0 4px 20px rgba(0,0,0,0.3)',
                        'max-width': '500px',
                        'border': '2px solid #dc2626'
                    }).html(`<h4>${title}</h4><p>${html}</p><button class="btn btn-primary mt-3" onclick="$(this).closest('div').remove()">OK</button>`);
                    $('body').append(alertDiv);
                    return Promise.resolve({ isConfirmed: true });
                } else {
                    alert(optsOrTitle + (text ? '\n\n' + text : ''));
                    return Promise.resolve({ isConfirmed: true });
                }
            },
            close: function() {},
            showLoading: function() {}
        };
    } else {
        // Configure Swal to always appear on top
        const originalFire = Swal.fire;
        Swal.fire = function(opts) {
            if (typeof opts === 'object') {
                opts.customClass = opts.customClass || {};
                opts.customClass.container = 'swal2-container-high-z';
                opts.customClass.popup = 'swal2-high-z-index';
                opts.allowOutsideClick = opts.allowOutsideClick !== false;
            }
            return originalFire.call(this, opts);
        };
    }
    
    // Set default pay period to current month
    const currentDate = new Date();
    const currentMonth = currentDate.getFullYear() + '-' + String(currentDate.getMonth() + 1).padStart(2, '0');
    $('#pay_period').val(currentMonth);
    
    // Set default pay date to next month 5th
    const nextMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 5);
    $('#pay_date').val(nextMonth.toISOString().split('T')[0]);
    
    // Initialize when modal opens
    $('#processPayrollModal').on('shown.bs.modal', function() {
        initializePayrollCalculations();
        updateSelectedCount();
        updatePayrollStatistics(); // Initialize statistics dashboard
        updatePreviewBreakdown(); // Initialize preview breakdown
    });
    
    // Update preview when switching to preview tab
    $('#preview-tab').on('shown.bs.tab', function() {
        updatePreviewBreakdown();
    });
    
    // Select all employees checkbox
    $('#selectAllEmployees').change(function() {
        $('.employee-checkbox').prop('checked', this.checked);
        updateSelectedCount();
        if (this.checked) {
            $('.employee-checkbox:checked').each(function() {
                const employeeId = $(this).val();
                queueEmployeeCalculation(employeeId);
            });
        } else {
            updatePreviewBreakdown();
        }
    });
    
    // Select/Deselect All buttons
    $('#select-all-employees').click(function() {
        $('.employee-checkbox').prop('checked', true);
        $('#selectAllEmployees').prop('checked', true);
        updateSelectedCount();
        $('.employee-checkbox').each(function() {
            queueEmployeeCalculation($(this).val());
        });
        updatePreviewBreakdown();
    });
    
    $('#deselect-all-employees').click(function() {
        $('.employee-checkbox').prop('checked', false);
        $('#selectAllEmployees').prop('checked', false);
        updateSelectedCount();
        updatePreviewBreakdown();
    });
    
    // Individual employee checkbox
    $('.employee-checkbox').change(function() {
        updateSelectedCount();
        const employeeId = $(this).val();
        if ($(this).is(':checked')) {
            queueEmployeeCalculation(employeeId);
        } else {
            // Clear validation display
            clearEmployeeValidation(employeeId);
        }
        updateSelectAllCheckbox();
        // Update preview breakdown when selection changes
        updatePreviewBreakdown();
    });
    
    // Input changes with debouncing
    let calculationTimeout;
    $(document).on('input change', '.overtime-input, .bonus-input, .allowance-input, .deduction-input', function() {
        const employeeId = $(this).data('employee-id');
        
        // Validate input immediately
        validateEmployeeInput(employeeId);
        
        // Update statistics immediately
        updatePayrollStatistics();
        
        // Clear previous timeout
        clearTimeout(calculationTimeout);
        
        // Debounce calculation
        calculationTimeout = setTimeout(() => {
            if ($(`.employee-checkbox[value="${employeeId}"]`).is(':checked')) {
                queueEmployeeCalculation(employeeId);
                // Update stats after calculation
                setTimeout(() => {
                    updatePayrollStatistics();
                    updatePreviewBreakdown();
                }, 100);
            } else {
                // Update preview even if not checked (to remove from preview)
                updatePreviewBreakdown();
            }
        }, 500);
    });
    
    // Validate all button
    $('#validate-all').click(function() {
        $('.employee-checkbox:checked').each(function() {
            const employeeId = $(this).val();
            validateEmployeeInput(employeeId);
            queueEmployeeCalculation(employeeId);
        });
    });
    
    // Update selected count and statistics
    function updateSelectedCount() {
        const count = $('.employee-checkbox:checked').length;
        $('#selected-count').text(count);
        updatePayrollStatistics();
    }
    
    // Update real-time statistics dashboard
    function updatePayrollStatistics() {
        const selectedEmployees = $('.employee-checkbox:checked');
        const totalEmployees = $('.employee-checkbox').length;
        const selectedCount = selectedEmployees.length;
        
        // Update selected employees count
        $('#stat-selected-employees').text(selectedCount);
        $('#stat-total-employees').text(totalEmployees);
        
        if (selectedCount === 0) {
            // Reset all stats to zero
            $('#stat-total-gross').text('0');
            $('#stat-total-deductions').text('0');
            $('#stat-total-net').text('0');
            $('#stat-total-employer-cost').text('0');
            $('#stat-avg-net').text('0');
            $('#preview-total-basic').text('TZS 0');
            $('#preview-total-overtime').text('TZS 0');
            $('#preview-total-bonus-allowance').text('TZS 0');
            $('#preview-total-statutory').text('TZS 0');
            $('#preview-total-fixed-other').text('TZS 0');
            $('#preview-total-net').text('TZS 0');
            return;
        }
        
        let totalBasic = 0;
        let totalOvertime = 0;
        let totalBonus = 0;
        let totalAllowance = 0;
        let totalOtherDeductions = 0;
        let totalStatutory = 0;
        let totalNet = 0;
        let totalEmployerCost = 0;
        
        selectedEmployees.each(function() {
            const employeeId = $(this).val();
            const row = $(`.employee-row[data-employee-id="${employeeId}"]`);
            
            // Get basic salary
            const basicSalary = parseFloat(row.find('.salary-input').val()) || 0;
            totalBasic += basicSalary;
            
            // Get overtime
            const overtimeHours = parseFloat(row.find('.overtime-input').val()) || 0;
            const hourlyRate = basicSalary / (22 * 8); // Assuming 22 working days, 8 hours per day
            const overtimeAmount = overtimeHours * hourlyRate * 1.5;
            totalOvertime += overtimeAmount;
            
            // Get bonus and allowance
            const bonus = parseFloat(row.find('.bonus-input').val()) || 0;
            const allowance = parseFloat(row.find('.allowance-input').val()) || 0;
            totalBonus += bonus;
            totalAllowance += allowance;
            
            // Get other deductions
            const otherDeductions = parseFloat(row.find('.deduction-input').val()) || 0;
            const fixedDeductions = parseFloat(row.find('[data-fixed-deductions]').attr('data-fixed-deductions')) || 0;
            totalOtherDeductions += (otherDeductions + fixedDeductions);
            
            // Get statutory deductions (from displayed text or stored)
            const statutoryText = row.find('.statutory-deductions').text();
            let statutoryAmount = 0;
            
            // Try to extract from displayed breakdown
            const payeMatch = statutoryText.match(/PAYE[:\s]+([\d,]+)/i);
            const nssfMatch = statutoryText.match(/NSSF[:\s]+([\d,]+)/i);
            const nhifMatch = statutoryText.match(/NHIF[:\s]+([\d,]+)/i);
            const heslbMatch = statutoryText.match(/HESLB[:\s]+([\d,]+)/i);
            const wcfMatch = statutoryText.match(/WCF[:\s]+([\d,]+)/i);
            const sdlMatch = statutoryText.match(/SDL[:\s]+([\d,]+)/i);
            
            if (payeMatch) statutoryAmount += parseFloat(payeMatch[1].replace(/,/g, '')) || 0;
            if (nssfMatch) statutoryAmount += parseFloat(nssfMatch[1].replace(/,/g, '')) || 0;
            if (nhifMatch) statutoryAmount += parseFloat(nhifMatch[1].replace(/,/g, '')) || 0;
            if (heslbMatch) statutoryAmount += parseFloat(heslbMatch[1].replace(/,/g, '')) || 0;
            if (wcfMatch) statutoryAmount += parseFloat(wcfMatch[1].replace(/,/g, '')) || 0;
            if (sdlMatch) statutoryAmount += parseFloat(sdlMatch[1].replace(/,/g, '')) || 0;
            
            // If no breakdown found, try stored amount
            if (statutoryAmount === 0) {
                const storedBadge = row.find('.badge.bg-label-success');
                if (storedBadge.length) {
                    const storedText = storedBadge.text();
                    const storedMatch = storedText.match(/Stored[:\s]+([\d,]+)/i);
                    if (storedMatch) {
                        statutoryAmount = parseFloat(storedMatch[1].replace(/,/g, '')) || 0;
                    }
                }
            }
            
            totalStatutory += statutoryAmount;
            
            // Calculate gross and net
            const gross = basicSalary + overtimeAmount + bonus + allowance;
            const totalDeductions = statutoryAmount + otherDeductions + fixedDeductions;
            const net = gross - totalDeductions;
            totalNet += net;
            
            // Employer cost (basic + overtime + bonus + allowance + employer contributions)
            // Employer contributions: NSSF employer (5%), WCF (1%), SDL (3.5%)
            const employerNSSF = gross * 0.05; // 5% employer NSSF
            const employerWCF = gross * 0.01; // 1% WCF
            const employerSDL = gross * 0.035; // 3.5% SDL
            const employerCost = gross + employerNSSF + employerWCF + employerSDL;
            totalEmployerCost += employerCost;
        });
        
        const totalGross = totalBasic + totalOvertime + totalBonus + totalAllowance;
        const totalDeductions = totalStatutory + totalOtherDeductions;
        const avgNet = selectedCount > 0 ? totalNet / selectedCount : 0;
        
        // Format numbers
        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        };
        
        // Update statistics dashboard
        $('#stat-selected-employees').text(selectedCount);
        $('#stat-total-gross').text(formatCurrency(totalGross));
        $('#stat-total-deductions').text(formatCurrency(totalDeductions));
        $('#stat-total-net').text(formatCurrency(totalNet));
        $('#stat-total-employer-cost').text(formatCurrency(totalEmployerCost));
        $('#stat-avg-net').text(formatCurrency(avgNet));
        
        // Update preview tab statistics
        $('#preview-total-basic').text('TZS ' + formatCurrency(totalBasic));
        $('#preview-total-overtime').text('TZS ' + formatCurrency(totalOvertime));
        $('#preview-total-bonus-allowance').text('TZS ' + formatCurrency(totalBonus + totalAllowance));
        $('#preview-total-statutory').text('TZS ' + formatCurrency(totalStatutory));
        $('#preview-total-fixed-other').text('TZS ' + formatCurrency(totalOtherDeductions));
        $('#preview-total-net').text('TZS ' + formatCurrency(totalNet));
        
        // Update detailed breakdown in preview tab
        updatePreviewBreakdown();
    }
    
    // Format currency helper (used in preview breakdown)
    function formatCurrencyPreview(amount) {
        if (isNaN(amount) || amount === null || amount === undefined) return 'TZS 0';
        return 'TZS ' + Math.round(amount).toLocaleString('en-TZ');
    }
    
    // Build comprehensive breakdown for preview tab
    function updatePreviewBreakdown() {
        const selectedEmployees = $('.employee-checkbox:checked');
        
        if (selectedEmployees.length === 0) {
            $('#payroll-summary-content').html(`
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>Select employees and enter details to see the detailed summary.
                </div>
            `);
            return;
        }
        
        let breakdownHtml = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th class="text-end">Basic Salary</th>
                            <th class="text-end">Overtime</th>
                            <th class="text-end">Bonus</th>
                            <th class="text-end">Allowance</th>
                            <th class="text-end">Gross Salary</th>
                            <th class="text-end text-danger">Deductions</th>
                            <th class="text-end text-success">Net Salary</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        let grandTotalBasic = 0;
        let grandTotalOvertime = 0;
        let grandTotalBonus = 0;
        let grandTotalAllowance = 0;
        let grandTotalGross = 0;
        let grandTotalDeductions = 0;
        let grandTotalNet = 0;
        
        selectedEmployees.each(function() {
            const employeeId = $(this).val();
            const row = $(`.employee-row[data-employee-id="${employeeId}"]`);
            
            // Get employee name
            const employeeName = row.find('h6').first().text() || 'N/A';
            const employeeCode = row.find('small').first().text() || '';
            
            // Get basic salary
            const basicSalary = parseFloat(row.find('.salary-input').val()) || 0;
            grandTotalBasic += basicSalary;
            
            // Get overtime
            const overtimeHours = parseFloat(row.find('.overtime-input').val()) || 0;
            const hourlyRate = basicSalary / (22 * 8);
            const overtimeAmount = overtimeHours * hourlyRate * 1.5;
            grandTotalOvertime += overtimeAmount;
            
            // Get bonus and allowance
            const bonus = parseFloat(row.find('.bonus-input').val()) || 0;
            const allowance = parseFloat(row.find('.allowance-input').val()) || 0;
            grandTotalBonus += bonus;
            grandTotalAllowance += allowance;
            
            // Calculate gross
            const gross = basicSalary + overtimeAmount + bonus + allowance;
            grandTotalGross += gross;
            
            // Get deductions
            const otherDeductions = parseFloat(row.find('.deduction-input').val()) || 0;
            const fixedDeductions = parseFloat(row.find('[data-fixed-deductions]').attr('data-fixed-deductions')) || 0;
            
            // Get deductions from stored breakdown data (if available) or parse from display
            let paye = 0, nssf = 0, nhif = 0, heslb = 0, wcf = 0, sdl = 0;
            let storedBreakdown = window.employeeBreakdownData && window.employeeBreakdownData[employeeId];
            
            if (storedBreakdown) {
                // Use stored calculated breakdown data
                paye = storedBreakdown.paye || 0;
                nssf = storedBreakdown.nssf || 0;
                nhif = storedBreakdown.nhif || 0;
                heslb = storedBreakdown.heslb || 0;
                wcf = storedBreakdown.wcf || 0;
                sdl = storedBreakdown.sdl || 0;
                fixedDeductions = storedBreakdown.fixedDeductionsTotal || fixedDeductions;
                otherDeductions = storedBreakdown.otherDeductions || otherDeductions;
            } else {
                // Fallback: Parse from displayed text
                const statutoryText = row.find('.statutory-deductions').text();
                
                // Extract statutory amounts
                const payeMatch = statutoryText.match(/PAYE[:\s]+([\d,]+)/i);
                const nssfMatch = statutoryText.match(/NSSF[:\s]+([\d,]+)/i);
                const nhifMatch = statutoryText.match(/NHIF[:\s]+([\d,]+)/i);
                const heslbMatch = statutoryText.match(/HESLB[:\s]+([\d,]+)/i);
                const wcfMatch = statutoryText.match(/WCF[:\s]+([\d,]+)/i);
                const sdlMatch = statutoryText.match(/SDL[:\s]+([\d,]+)/i);
                
                if (payeMatch) paye = parseFloat(payeMatch[1].replace(/,/g, '')) || 0;
                if (nssfMatch) nssf = parseFloat(nssfMatch[1].replace(/,/g, '')) || 0;
                if (nhifMatch) nhif = parseFloat(nhifMatch[1].replace(/,/g, '')) || 0;
                if (heslbMatch) heslb = parseFloat(heslbMatch[1].replace(/,/g, '')) || 0;
                if (wcfMatch) wcf = parseFloat(wcfMatch[1].replace(/,/g, '')) || 0;
                if (sdlMatch) sdl = parseFloat(sdlMatch[1].replace(/,/g, '')) || 0;
                
                // If no breakdown found, try stored amount
                if (paye === 0 && nssf === 0 && nhif === 0) {
                    const storedBadge = row.find('.badge.bg-label-success');
                    if (storedBadge.length) {
                        const storedText = storedBadge.text();
                        const storedMatch = storedText.match(/Stored[:\s]+([\d,]+)/i);
                        if (storedMatch) {
                            const storedTotal = parseFloat(storedMatch[1].replace(/,/g, '')) || 0;
                            // Distribute stored amount (approximation)
                            paye = storedTotal * 0.4;
                            nssf = storedTotal * 0.3;
                            nhif = storedTotal * 0.2;
                            heslb = storedTotal * 0.1;
                        }
                    }
                }
            }
            
            const totalStatutory = paye + nssf + nhif + heslb + wcf + sdl;
            const totalDeductions = totalStatutory + otherDeductions + fixedDeductions;
            grandTotalDeductions += totalDeductions;
            
            // Calculate net
            const net = gross - totalDeductions;
            grandTotalNet += net;
            
            // Build deductions breakdown tooltip
            const deductionsBreakdown = `
                <div class="text-start small">
                    <div class="mb-1"><strong>Statutory Deductions:</strong></div>
                    <div class="ms-2 mb-1">PAYE: ${formatCurrencyPreview(paye)}</div>
                    <div class="ms-2 mb-1">NSSF: ${formatCurrencyPreview(nssf)}</div>
                    <div class="ms-2 mb-1">NHIF: ${formatCurrencyPreview(nhif)}</div>
                    ${heslb > 0 ? `<div class="ms-2 mb-1">HESLB: ${formatCurrencyPreview(heslb)}</div>` : ''}
                    ${wcf > 0 ? `<div class="ms-2 mb-1">WCF: ${formatCurrencyPreview(wcf)}</div>` : ''}
                    ${sdl > 0 ? `<div class="ms-2 mb-1">SDL: ${formatCurrencyPreview(sdl)}</div>` : ''}
                    ${fixedDeductions > 0 ? `<div class="ms-2 mb-1 mt-2"><strong>Fixed Deductions: ${formatCurrencyPreview(fixedDeductions)}</strong></div>` : ''}
                    ${otherDeductions > 0 ? `<div class="ms-2 mb-1"><strong>Other Deductions: ${formatCurrencyPreview(otherDeductions)}</strong></div>` : ''}
                    <div class="ms-2 mt-2 pt-1 border-top"><strong>Total: ${formatCurrencyPreview(totalDeductions)}</strong></div>
                </div>
            `;
            
            breakdownHtml += `
                <tr>
                    <td>
                        <div>
                            <strong>${employeeName}</strong>
                            ${employeeCode ? `<br><small class="text-muted">${employeeCode}</small>` : ''}
                        </div>
                    </td>
                    <td class="text-end">${formatCurrencyPreview(basicSalary)}</td>
                    <td class="text-end">
                        ${overtimeHours > 0 ? `${overtimeHours} hrs<br><small class="text-muted">${formatCurrencyPreview(overtimeAmount)}</small>` : '-'}
                    </td>
                    <td class="text-end">${bonus > 0 ? formatCurrencyPreview(bonus) : '-'}</td>
                    <td class="text-end">${allowance > 0 ? formatCurrencyPreview(allowance) : '-'}</td>
                    <td class="text-end"><strong>${formatCurrencyPreview(gross)}</strong></td>
                    <td class="text-end">
                        <strong class="text-danger" data-bs-toggle="tooltip" data-bs-html="true" 
                                title="${deductionsBreakdown.replace(/"/g, '&quot;').replace(/\n/g, ' ')}">
                            ${formatCurrencyPreview(totalDeductions)}
                        </strong>
                        <br>
                        <small class="text-muted">
                            PAYE: ${formatCurrencyPreview(paye)}<br>
                            NSSF: ${formatCurrencyPreview(nssf)}<br>
                            NHIF: ${formatCurrencyPreview(nhif)}
                            ${heslb > 0 ? `<br>HESLB: ${formatCurrencyPreview(heslb)}` : ''}
                            ${fixedDeductions > 0 ? `<br>Fixed: ${formatCurrencyPreview(fixedDeductions)}` : ''}
                            ${otherDeductions > 0 ? `<br>Other: ${formatCurrencyPreview(otherDeductions)}` : ''}
                        </small>
                    </td>
                    <td class="text-end"><strong class="text-success">${formatCurrencyPreview(net)}</strong></td>
                </tr>
            `;
        });
        
        // Add totals row
        breakdownHtml += `
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <th>GRAND TOTAL</th>
                            <th class="text-end">${formatCurrencyPreview(grandTotalBasic)}</th>
                            <th class="text-end">${formatCurrencyPreview(grandTotalOvertime)}</th>
                            <th class="text-end">${formatCurrencyPreview(grandTotalBonus)}</th>
                            <th class="text-end">${formatCurrencyPreview(grandTotalAllowance)}</th>
                            <th class="text-end">${formatCurrencyPreview(grandTotalGross)}</th>
                            <th class="text-end text-danger">${formatCurrencyPreview(grandTotalDeductions)}</th>
                            <th class="text-end text-success">${formatCurrencyPreview(grandTotalNet)}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <!-- Summary Cards -->
            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <div class="card border-primary">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Gross Salary</h6>
                            <h4 class="text-primary mb-0">${formatCurrencyPreview(grandTotalGross)}</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-danger">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Deductions</h6>
                            <h4 class="text-danger mb-0">${formatCurrencyPreview(grandTotalDeductions)}</h4>
                            <small class="text-muted">
                                All deduction types included
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-success">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Net Salary</h6>
                            <h4 class="text-success mb-0">${formatCurrencyPreview(grandTotalNet)}</h4>
                            <small class="text-muted">
                                Average: ${formatCurrencyPreview(selectedEmployees.length > 0 ? grandTotalNet / selectedEmployees.length : 0)}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('#payroll-summary-content').html(breakdownHtml);
        
        // Build deduction summary by type
        let deductionSummaryHtml = '';
        let totalPAYE = 0, totalNSSF = 0, totalNHIF = 0, totalHESLB = 0, totalWCF = 0, totalSDL = 0, totalFixed = 0, totalOther = 0;
        
        selectedEmployees.each(function() {
            const employeeId = $(this).val();
            const storedBreakdown = window.employeeBreakdownData && window.employeeBreakdownData[employeeId];
            
            if (storedBreakdown) {
                totalPAYE += storedBreakdown.paye || 0;
                totalNSSF += storedBreakdown.nssf || 0;
                totalNHIF += storedBreakdown.nhif || 0;
                totalHESLB += storedBreakdown.heslb || 0;
                totalWCF += storedBreakdown.wcf || 0;
                totalSDL += storedBreakdown.sdl || 0;
                totalFixed += storedBreakdown.fixedDeductionsTotal || 0;
                totalOther += storedBreakdown.otherDeductions || 0;
            } else {
                // Fallback: parse from row
                const row = $(`.employee-row[data-employee-id="${employeeId}"]`);
                const statutoryText = row.find('.statutory-deductions').text();
                const fixedDeductions = parseFloat(row.find('[data-fixed-deductions]').attr('data-fixed-deductions')) || 0;
                const otherDeductions = parseFloat(row.find('.deduction-input').val()) || 0;
                
                const payeMatch = statutoryText.match(/PAYE[:\s]+([\d,]+)/i);
                const nssfMatch = statutoryText.match(/NSSF[:\s]+([\d,]+)/i);
                const nhifMatch = statutoryText.match(/NHIF[:\s]+([\d,]+)/i);
                const heslbMatch = statutoryText.match(/HESLB[:\s]+([\d,]+)/i);
                const wcfMatch = statutoryText.match(/WCF[:\s]+([\d,]+)/i);
                const sdlMatch = statutoryText.match(/SDL[:\s]+([\d,]+)/i);
                
                if (payeMatch) totalPAYE += parseFloat(payeMatch[1].replace(/,/g, '')) || 0;
                if (nssfMatch) totalNSSF += parseFloat(nssfMatch[1].replace(/,/g, '')) || 0;
                if (nhifMatch) totalNHIF += parseFloat(nhifMatch[1].replace(/,/g, '')) || 0;
                if (heslbMatch) totalHESLB += parseFloat(heslbMatch[1].replace(/,/g, '')) || 0;
                if (wcfMatch) totalWCF += parseFloat(wcfMatch[1].replace(/,/g, '')) || 0;
                if (sdlMatch) totalSDL += parseFloat(sdlMatch[1].replace(/,/g, '')) || 0;
                totalFixed += fixedDeductions;
                totalOther += otherDeductions;
            }
        });
        
        const totalStatutorySummary = totalPAYE + totalNSSF + totalNHIF + totalHESLB + totalWCF + totalSDL;
        const totalAllDeductions = totalStatutorySummary + totalFixed + totalOther;
        
        deductionSummaryHtml = `
            <div class="row g-3">
                <div class="col-md-6">
                    <h6 class="mb-3 text-primary"><i class="bx bx-shield me-2"></i>Statutory Deductions</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>PAYE</strong></td>
                                    <td class="text-end text-danger fw-bold">${formatCurrencyPreview(totalPAYE)}</td>
                                </tr>
                                <tr>
                                    <td><strong>NSSF (Employee)</strong></td>
                                    <td class="text-end text-warning fw-bold">${formatCurrencyPreview(totalNSSF)}</td>
                                </tr>
                                <tr>
                                    <td><strong>NHIF</strong></td>
                                    <td class="text-end text-info fw-bold">${formatCurrencyPreview(totalNHIF)}</td>
                                </tr>
                                ${totalHESLB > 0 ? `
                                <tr>
                                    <td><strong>HESLB</strong></td>
                                    <td class="text-end text-secondary fw-bold">${formatCurrencyPreview(totalHESLB)}</td>
                                </tr>
                                ` : ''}
                                ${totalWCF > 0 ? `
                                <tr>
                                    <td><strong>WCF</strong></td>
                                    <td class="text-end fw-bold">${formatCurrencyPreview(totalWCF)}</td>
                                </tr>
                                ` : ''}
                                ${totalSDL > 0 ? `
                                <tr>
                                    <td><strong>SDL</strong></td>
                                    <td class="text-end fw-bold">${formatCurrencyPreview(totalSDL)}</td>
                                </tr>
                                ` : ''}
                                <tr class="table-light">
                                    <td><strong>Total Statutory</strong></td>
                                    <td class="text-end text-danger fw-bold">${formatCurrencyPreview(totalStatutorySummary)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="mb-3 text-warning"><i class="bx bx-money-withdraw me-2"></i>Other Deductions</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <tbody>
                                <tr>
                                    <td><strong>Fixed Deductions</strong></td>
                                    <td class="text-end text-warning fw-bold">${formatCurrencyPreview(totalFixed)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Other Deductions</strong></td>
                                    <td class="text-end fw-bold">${formatCurrencyPreview(totalOther)}</td>
                                </tr>
                                <tr class="table-light">
                                    <td><strong>Total Other</strong></td>
                                    <td class="text-end text-warning fw-bold">${formatCurrencyPreview(totalFixed + totalOther)}</td>
                                </tr>
                                <tr class="table-danger">
                                    <td><strong>GRAND TOTAL DEDUCTIONS</strong></td>
                                    <td class="text-end text-danger fw-bold fs-5">${formatCurrencyPreview(totalAllDeductions)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;
        
        $('#deduction-summary-by-type').html(deductionSummaryHtml);
        
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
    }
    
    // Initialize payroll calculations
    function initializePayrollCalculations() {
        $('.employee-row').each(function() {
            const employeeId = $(this).data('employee-id');
            // Reset values
            $(`input[name="overtime_hours[${employeeId}]"]`).val(0);
            $(`input[name="bonus_amount[${employeeId}]"]`).val(0);
            $(`input[name="allowance_amount[${employeeId}]"]`).val(0);
            $(`input[name="deduction_amount[${employeeId}]"]`).val(0);
            
            // Clear validation
            clearEmployeeValidation(employeeId);
        });
    }
    
    // Validate employee input
    function validateEmployeeInput(employeeId) {
        const errors = [];
        const warnings = [];
        
        const basicSalary = parseFloat($(`input[name="basic_salary[${employeeId}]"]`).val()) || 0;
        const overtimeHours = parseFloat($(`input[name="overtime_hours[${employeeId}]"]`).val()) || 0;
        const bonusAmount = parseFloat($(`input[name="bonus_amount[${employeeId}]"]`).val()) || 0;
        const allowanceAmount = parseFloat($(`input[name="allowance_amount[${employeeId}]"]`).val()) || 0;
        const deductionAmount = parseFloat($(`input[name="deduction_amount[${employeeId}]"]`).val()) || 0;
        
        // Validate basic salary
        if (basicSalary <= 0) {
            errors.push('Basic salary is missing or invalid');
        }
        
        // Validate overtime hours (max 744 hours per month)
        if (overtimeHours < 0) {
            errors.push('Overtime hours cannot be negative');
        } else if (overtimeHours > 744) {
            errors.push('Overtime hours exceed maximum (744 hours/month)');
        }
        
        // Validate amounts are not negative
        if (bonusAmount < 0) {
            errors.push('Bonus amount cannot be negative');
        }
        if (allowanceAmount < 0) {
            errors.push('Allowance amount cannot be negative');
        }
        if (deductionAmount < 0) {
            errors.push('Deduction amount cannot be negative');
        }
        
        // Calculate estimated gross and check deductions
        const overtimeRate = basicSalary / (22 * 8);
        const overtimeAmount = overtimeHours * overtimeRate * 1.5;
        const estimatedGross = basicSalary + overtimeAmount + bonusAmount + allowanceAmount;
        
        if (deductionAmount > estimatedGross * 0.5) {
            warnings.push('Deduction exceeds 50% of gross salary');
        }
        
        // Display validation results
        displayEmployeeValidation(employeeId, errors, warnings);
        
        return errors.length === 0;
    }
    
    // Display validation results in the row
    function displayEmployeeValidation(employeeId, errors, warnings) {
        const container = $(`.employee-validation-errors[data-employee-id="${employeeId}"]`);
        const row = $(`.employee-row[data-employee-id="${employeeId}"]`);
        
        // Remove existing validation classes
        row.removeClass('table-danger table-warning');
        
        if (errors.length > 0) {
            row.addClass('table-danger');
            let html = '<div class="validation-errors">';
            errors.forEach(error => {
                html += `<small class="text-danger d-block"><i class="bx bx-error-circle me-1"></i>${error}</small>`;
            });
            if (warnings.length > 0) {
                warnings.forEach(warning => {
                    html += `<small class="text-warning d-block"><i class="bx bx-error me-1"></i>${warning}</small>`;
                });
            }
            html += '</div>';
            container.html(html);
        } else if (warnings.length > 0) {
            row.addClass('table-warning');
            let html = '<div class="validation-warnings">';
            warnings.forEach(warning => {
                html += `<small class="text-warning d-block"><i class="bx bx-error me-1"></i>${warning}</small>`;
            });
            html += '</div>';
            container.html(html);
        } else {
            container.html('<small class="text-success"><i class="bx bx-check-circle me-1"></i>Ready</small>');
        }
    }
    
    // Clear employee validation
    function clearEmployeeValidation(employeeId) {
        $(`.employee-validation-errors[data-employee-id="${employeeId}"]`).html('<small class="text-muted">-</small>');
        $(`.employee-row[data-employee-id="${employeeId}"]`).removeClass('table-danger table-warning');
    }
    
    // Queue employee calculation
    function queueEmployeeCalculation(employeeId) {
        if (!calculationQueue.includes(employeeId)) {
            calculationQueue.push(employeeId);
        }
        
        if (!isCalculating) {
            processCalculationQueue();
        }
    }
    
    // Process calculation queue
    function processCalculationQueue() {
        if (calculationQueue.length === 0) {
            isCalculating = false;
            return;
        }
        
        isCalculating = true;
        const employeeId = calculationQueue.shift();
        
        calculateEmployeeDeductions(employeeId)
            .finally(() => {
                setTimeout(() => {
                    processCalculationQueue();
                }, 200);
            });
    }
    
    // Calculate employee deductions with AJAX
    function calculateEmployeeDeductions(employeeId) {
        return new Promise((resolve) => {
            const basicSalary = parseFloat($(`input[name="basic_salary[${employeeId}]"]`).val()) || 0;
            const overtimeHours = parseFloat($(`input[name="overtime_hours[${employeeId}]"]`).val()) || 0;
            const bonusAmount = parseFloat($(`input[name="bonus_amount[${employeeId}]"]`).val()) || 0;
            const allowanceAmount = parseFloat($(`input[name="allowance_amount[${employeeId}]"]`).val()) || 0;
            const additionalDeductions = parseFloat($(`input[name="deduction_amount[${employeeId}]"]`).val()) || 0;
            const fixedDeductions = parseFloat($(`small[data-fixed-deductions][data-employee-id="${employeeId}"]`).attr('data-fixed-deductions')) || 0;
            
            if (basicSalary <= 0) {
                resolve();
                return;
            }
            
            // Show loading
            $(`.statutory-deductions[data-employee-id="${employeeId}"]`).html(`
                <div class="text-center">
                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                    <div class="small text-muted">Calculating...</div>
                </div>
            `);
            
            $.ajax({
                url: '{{ route("payroll.calculate-deductions") }}',
                type: 'POST',
                data: {
                    employee_id: employeeId,
                    basic_salary: basicSalary,
                    overtime_hours: overtimeHours,
                    bonus_amount: bonusAmount,
                    allowance_amount: allowanceAmount,
                    additional_deductions: additionalDeductions,
                    _token: csrfToken
                },
                success: function(response) {
                    if (response.success && response.breakdown) {
                        updateEmployeeDisplay(employeeId, response.breakdown);
                    } else {
                        showCalculationError(employeeId, response.message || 'Calculation failed');
                    }
                    resolve();
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showCalculationError(employeeId, response?.message || 'Server error');
                    resolve();
                }
            });
        });
    }
    
    // Store employee breakdown data for preview
    window.employeeBreakdownData = window.employeeBreakdownData || {};
    
    // Update employee display with breakdown
    function updateEmployeeDisplay(employeeId, breakdown) {
        const grossSalary = parseFloat(breakdown.gross_salary) || 0;
        const paye = parseFloat(breakdown.paye) || 0;
        const nssfEmployee = parseFloat(breakdown.nssf?.employee) || 0;
        const nssfEmployer = parseFloat(breakdown.nssf?.employer) || 0;
        const nhif = parseFloat(breakdown.nhif) || 0;
        const heslb = parseFloat(breakdown.heslb) || 0;
        const wcf = parseFloat(breakdown.wcf) || 0;
        const sdl = parseFloat(breakdown.sdl) || 0;
        const totalDeductions = parseFloat(breakdown.total_deductions) || 0;
        const netSalary = parseFloat(breakdown.net_salary) || 0;
        const totalEmployerCost = parseFloat(breakdown.total_employer_cost) || 0;
        const fixedDeductionsTotal = parseFloat(breakdown.fixed_deductions_total) || 0;
        const fixedDeductions = breakdown.fixed_deductions || [];
        const otherDeductions = parseFloat(breakdown.other_deductions) || 0;
        
        // Store breakdown data for preview tab
        window.employeeBreakdownData[employeeId] = {
            paye: paye,
            nssf: nssfEmployee,
            nhif: nhif,
            heslb: heslb,
            wcf: wcf,
            sdl: sdl,
            fixedDeductions: fixedDeductions,
            fixedDeductionsTotal: fixedDeductionsTotal,
            otherDeductions: otherDeductions,
            totalDeductions: totalDeductions,
            grossSalary: grossSalary,
            netSalary: netSalary
        };
        
        // Build fixed deductions display
        let fixedDeductionsHtml = '';
        if (fixedDeductions && fixedDeductions.length > 0) {
            fixedDeductionsHtml = '<div class="border-top pt-1 mt-1"><small class="text-muted d-block mb-1"><strong>Fixed Deductions:</strong></small>';
            fixedDeductions.forEach(ded => {
                fixedDeductionsHtml += `<div class="d-flex justify-content-between mb-1"><small>${ded.deduction_type || 'Other'}:</small><small class="text-warning">${formatCurrency(ded.amount || 0)}</small></div>`;
            });
            fixedDeductionsHtml += `<div class="d-flex justify-content-between mt-1"><small class="fw-bold">Fixed Total:</small><small class="fw-bold text-warning">${formatCurrency(fixedDeductionsTotal)}</small></div></div>`;
        }
        
        // Update statutory deductions
        $(`.statutory-deductions[data-employee-id="${employeeId}"]`).html(`
            <div class="small">
                <div class="d-flex justify-content-between mb-1">
                    <span>PAYE:</span>
                    <span class="text-danger fw-bold">${formatCurrency(paye)}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>NSSF:</span>
                    <span class="text-warning">${formatCurrency(nssfEmployee)}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>NHIF:</span>
                    <span class="text-info">${formatCurrency(nhif)}</span>
                </div>
                ${heslb > 0 ? `
                <div class="d-flex justify-content-between mb-1">
                    <span>HESLB:</span>
                    <span class="text-secondary">${formatCurrency(heslb)}</span>
                </div>
                ` : ''}
                ${fixedDeductionsHtml}
                <div class="border-top pt-1 mt-1">
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total:</span>
                        <span class="fw-bold text-danger">${formatCurrency(totalDeductions)}</span>
                    </div>
                </div>
            </div>
        `);
        
        // Update employer cost
        $(`.employer-cost[data-employee-id="${employeeId}"]`).html(`
            <div class="small">
                <div class="text-muted mb-1">Gross: ${formatCurrency(grossSalary)}</div>
                <div class="text-warning mb-1">NSSF: ${formatCurrency(nssfEmployer)}</div>
                <div class="text-info mb-1">WCF: ${formatCurrency(wcf)}</div>
                <div class="text-primary mb-1">SDL: ${formatCurrency(sdl)}</div>
                <div class="border-top pt-1 mt-1">
                    <div class="fw-bold text-info">${formatCurrency(totalEmployerCost)}</div>
                </div>
            </div>
        `);
        
        // Update net salary
        $(`.estimated-net[data-employee-id="${employeeId}"]`).html(`
            <div class="fw-bold text-success">${formatCurrency(netSalary)}</div>
            <small class="text-muted">${grossSalary > 0 ? ((netSalary / grossSalary) * 100).toFixed(1) : 0}% of gross</small>
        `);
        
        // Re-validate after calculation
        validateEmployeeInput(employeeId);
        
        // Update statistics dashboard
        updatePayrollStatistics();
        
        // Update preview breakdown
        updatePreviewBreakdown();
    }
    
    // Show calculation error
    function showCalculationError(employeeId, message) {
        $(`.statutory-deductions[data-employee-id="${employeeId}"]`).html(`
            <small class="text-danger"><i class="bx bx-error-circle me-1"></i>${message}</small>
        `);
        $(`.employer-cost[data-employee-id="${employeeId}"]`).html('<small class="text-danger">Error</small>');
        $(`.estimated-net[data-employee-id="${employeeId}"]`).html('<small class="text-danger">Error</small>');
    }
    
    // Format currency
    function formatCurrency(amount) {
        if (isNaN(amount) || amount === null || amount === undefined) return 'TZS 0';
        return 'TZS ' + Math.round(amount).toLocaleString('en-TZ');
    }
    
    // Validate before submit button
    $('#validate-before-submit').click(function() {
        const selectedEmployees = $('.employee-checkbox:checked').length;
        if (selectedEmployees === 0) {
            Swal.fire('Error', 'Please select at least one employee.', 'error');
            $('#employees-tab').tab('show');
            return;
        }
        
        // Validate all selected employees
        let hasErrors = false;
        let errorCount = 0;
        $('.employee-checkbox:checked').each(function() {
            const employeeId = $(this).val();
            validateEmployeeInput(employeeId);
            const errors = $(`.employee-validation-errors[data-employee-id="${employeeId}"] .validation-errors`).length;
            if (errors > 0) {
                hasErrors = true;
                errorCount++;
            }
        });
        
        if (hasErrors) {
            Swal.fire({
                title: 'Validation Errors Found',
                html: `Found validation errors for <strong>${errorCount}</strong> employee(s).<br>Please check the "Validation Issues" column and fix all errors before processing.`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            Swal.fire({
                title: 'Validation Passed!',
                html: `All <strong>${selectedEmployees}</strong> selected employees are valid and ready for processing.`,
                icon: 'success',
                confirmButtonText: 'OK'
            });
        }
        $('#employees-tab').tab('show');
    });
    
    // Direct button click handler as backup - ensure it works
    $(document).on('click', '#process-payroll-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        
        console.log('=== Process Payroll button clicked directly ===');
        console.log('Button ID:', $(this).attr('id'));
        console.log('Form exists:', $('#processPayrollForm').length);
        
        // Manually trigger validation and processing
        const selectedEmployees = $('.employee-checkbox:checked').length;
        console.log('Selected employees:', selectedEmployees);
        
        if (selectedEmployees === 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'No Employees Selected',
                    text: 'Please select at least one employee to process payroll.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#employees-tab').tab('show');
                });
            } else {
                alert('Please select at least one employee to process payroll.');
            }
            return false;
        }
        
        // Validate pay period and pay date
        const payPeriod = $('#pay_period').val();
        const payDate = $('#pay_date').val();
        
        if (!payPeriod || !payDate) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Missing Information',
                    text: 'Please fill in both Pay Period and Pay Date.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then(() => {
                    $('#basic-info-tab').tab('show');
                });
            } else {
                alert('Please fill in both Pay Period and Pay Date.');
            }
            return false;
        }
        
        // If validation passes, proceed with processing
        if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Process Payroll?',
                html: `This will process payroll for <strong>${selectedEmployees}</strong> employee(s).<br><br>Pay Period: <strong>${payPeriod}</strong><br>Pay Date: <strong>${payDate}</strong><br><br>Continue?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Process',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                    console.log('User confirmed - processing payroll...');
                processPayroll();
            }
        });
        } else {
            if (confirm(`Process payroll for ${selectedEmployees} employee(s)?`)) {
                processPayroll();
            }
        }
        
        return false;
    });
    
    // Process payroll form with enhanced validation
    $('#processPayrollForm').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Form submitted - starting validation...');
        
        const selectedEmployees = $('.employee-checkbox:checked').length;
        if (selectedEmployees === 0) {
            Swal.fire({
                title: 'No Employees Selected',
                text: 'Please select at least one employee to process payroll.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                $('#employees-tab').tab('show');
            });
            return false;
        }
        
        // Validate pay period and pay date
        const payPeriod = $('#pay_period').val();
        const payDate = $('#pay_date').val();
        
        if (!payPeriod || !payDate) {
            Swal.fire({
                title: 'Missing Information',
                text: 'Please fill in both Pay Period and Pay Date.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                $('#basic-info-tab').tab('show');
            });
            return false;
        }
        
        // Validate all selected employees before submission
        let hasErrors = false;
        let errorMessages = [];
        
        $('.employee-checkbox:checked').each(function() {
            const employeeId = $(this).val();
            if (!validateEmployeeInput(employeeId)) {
                hasErrors = true;
                const employeeName = $(`.employee-row[data-employee-id="${employeeId}"]`).find('h6').first().text();
                errorMessages.push(employeeName || `Employee ID: ${employeeId}`);
            }
        });
        
        if (hasErrors) {
            Swal.fire({
                title: 'Validation Errors Found',
                html: `Please fix validation errors for the following employees:<br><strong>${errorMessages.join(', ')}</strong><br><br>Check the "Validation Issues" column for details.`,
                icon: 'error',
                confirmButtonText: 'View Errors',
                showCancelButton: true,
                cancelButtonText: 'OK'
            }).then((result) => {
                $('#employees-tab').tab('show');
                if (result.isConfirmed) {
                    // Scroll to first error
                    const firstErrorRow = $('.table-danger').first();
                    if (firstErrorRow.length) {
                        $('html, body').animate({
                            scrollTop: firstErrorRow.offset().top - 200
                        }, 500);
                    }
                }
            });
            return false;
        }
        
        // Confirm before processing
        Swal.fire({
            title: 'Process Payroll?',
            html: `This will process payroll for <strong>${selectedEmployees}</strong> employee(s).<br><br>Pay Period: <strong>${payPeriod}</strong><br>Pay Date: <strong>${payDate}</strong><br><br>Continue?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Process',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                console.log('User confirmed - processing payroll...');
                processPayroll();
            } else {
                console.log('User cancelled payroll processing');
            }
        });
        
        return false;
    });
    
    // Enhanced process payroll function with error handling
    function processPayroll() {
        console.log('Starting payroll processing...');
        const formData = new FormData($('#processPayrollForm')[0]);
        
        // Log form data for debugging
        console.log('Form data:', {
            pay_period: $('#pay_period').val(),
            pay_date: $('#pay_date').val(),
            employee_ids: $('.employee-checkbox:checked').map(function() { return $(this).val(); }).get()
        });
        
        Swal.fire({
            title: 'Processing Payroll',
            html: '<div class="text-center"><div class="spinner-border text-primary mb-3" role="status"></div><p>Processing payroll for selected employees...</p><p class="text-muted small">Please wait, this may take a moment.</p></div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '{{ route("payroll.process") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 60000, // 60 second timeout
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                console.log('Payroll processing response:', response);
                Swal.close();
                
                if (response && response.success) {
                    // Display employee errors if any
                    if (response.has_errors && response.employee_errors && Object.keys(response.employee_errors).length > 0) {
                        displayEmployeeErrors(response.employee_errors);
                        
                        Swal.fire({
                            title: 'Payroll Processed with Warnings',
                            html: `<div class="text-start">
                                <p>${response.message}</p>
                                <hr>
                                <p><strong>Some employees had issues:</strong></p>
                                <ul class="text-start">
                                    ${Object.keys(response.employee_errors).map(empId => {
                                        const empName = $(`.employee-row[data-employee-id="${empId}"]`).find('h6').first().text() || `Employee ID: ${empId}`;
                                        return `<li>${empName}</li>`;
                                    }).join('')}
                                </ul>
                                <p class="small text-muted">${Object.keys(response.employee_errors).length} employee(s) with errors. Check the Validation Issues column for details.</p>
                            </div>`,
                            icon: 'warning',
                            confirmButtonText: 'View Details',
                            showCancelButton: true,
                            cancelButtonText: 'Close',
                            width: '600px'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#employees-tab').tab('show');
                                // Scroll to first error
                                setTimeout(() => {
                                    const firstErrorRow = $('.table-danger').first();
                                    if (firstErrorRow.length) {
                                        $('html, body').animate({
                                            scrollTop: firstErrorRow.offset().top - 200
                                        }, 500);
                                    }
                                }, 300);
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        // Complete success
                        Swal.fire({
                            title: 'Success!',
                            html: `<div class="text-center">
                                <i class="bx bx-check-circle text-success" style="font-size: 64px;"></i>
                                <p class="mt-3">${response.message}</p>
                                ${response.payroll_id ? `<p class="small text-muted">Payroll ID: ${response.payroll_id}</p>` : ''}
                                ${response.processed_count ? `<p class="small text-muted">Processed: ${response.processed_count} employee(s)</p>` : ''}
                            </div>`,
                            icon: 'success',
                            confirmButtonText: 'OK',
                            allowOutsideClick: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                } else {
                    Swal.fire({
                        title: 'Processing Failed',
                        text: response?.message || 'An unknown error occurred while processing payroll.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Payroll processing error:', {xhr, status, error});
                Swal.close();
                
                let errorMessage = 'An error occurred while processing payroll.';
                const response = xhr.responseJSON;
                
                if (xhr.status === 422) {
                    // Validation errors
                    errorMessage = 'Validation errors occurred. Please check your input.';
                    if (response && response.errors) {
                        const errorList = Object.values(response.errors).flat().join('<br>');
                        errorMessage += '<br><br>' + errorList;
                    }
                } else if (xhr.status === 500) {
                    errorMessage = response?.message || 'Server error occurred. Please try again or contact support.';
                } else if (xhr.status === 0 || status === 'timeout') {
                    errorMessage = 'Request timed out or network error. Please check your connection and try again.';
                } else if (response && response.message) {
                    errorMessage = response.message;
                }
                
                if (response && response.employee_errors && Object.keys(response.employee_errors).length > 0) {
                    displayEmployeeErrors(response.employee_errors);
                    Swal.fire({
                        title: 'Processing Failed',
                        html: `${errorMessage}<br><br><p>Check validation issues below.</p>`,
                        icon: 'error',
                        confirmButtonText: 'View Errors',
                        width: '600px'
                    }).then(() => {
                        $('#employees-tab').tab('show');
                        setTimeout(() => {
                            const firstErrorRow = $('.table-danger').first();
                            if (firstErrorRow.length) {
                                $('html, body').animate({
                                    scrollTop: firstErrorRow.offset().top - 200
                                }, 500);
                            }
                        }, 300);
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        html: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    }
    
    // Display employee errors from server response
    function displayEmployeeErrors(employeeErrors) {
        Object.keys(employeeErrors).forEach(employeeId => {
            const errors = employeeErrors[employeeId];
            const row = $(`.employee-row[data-employee-id="${employeeId}"]`);
            
            // Mark row as having errors
            row.addClass('table-danger');
            
            // Display errors in validation column
            let html = '<div class="validation-errors">';
            errors.forEach(error => {
                html += `<small class="text-danger d-block"><i class="bx bx-error-circle me-1"></i>${error}</small>`;
            });
            html += '</div>';
            
            $(`.employee-validation-errors[data-employee-id="${employeeId}"]`).html(html);
            
            // Scroll to error if visible
            if (row.is(':visible')) {
                row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
    
    // Review payroll form
    $('#reviewPayrollForm').submit(function(e) {
        e.preventDefault();
        
        const action = $('input[name="review_action"]:checked').val();
        const actionText = action === 'approve' ? 'approve' : 'reject';
        
        Swal.fire({
            title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} Payroll?`,
            text: `Are you sure you want to ${actionText} this payroll?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Yes, ${actionText.charAt(0).toUpperCase() + actionText.slice(1)}`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                reviewPayroll();
            }
        });
    });
    
    // Approve payroll form
    $('#approvePayrollForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Approve Payroll?',
            text: 'This will approve the payroll for payment processing.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                approvePayroll();
            }
        });
    });
    
    // Mark as paid form
    $('#markPaidForm').submit(function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Mark as Paid?',
            text: 'This will mark the payroll as paid and update all employee records.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Mark as Paid',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                markAsPaid();
            }
        });
    });
    
    // Export payroll Excel
    $(document).on('click', '#exportPayrollBtn', function() {
        const payrollId = $(this).data('payroll-id');
        if (payrollId) {
            const exportUrl = `{{ route('payroll.export', ['payroll' => ':id']) }}`.replace(':id', payrollId);
            console.log('Exporting payroll:', exportUrl);
            window.open(exportUrl, '_blank');
            
            Swal.fire({
                title: 'Export Started',
                text: 'Payroll export will download shortly...',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Error', 'Payroll ID not found.', 'error');
        }
    });
    
    // PDF Download handlers
    $(document).on('click', '#downloadPayrollReportPdf', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (href && href !== '#') {
            window.open(href, '_blank');
        } else {
            Swal.fire('Error', 'PDF download link not available.', 'error');
        }
    });
    
    $(document).on('click', '#downloadPayslipPdf', function(e) {
        e.preventDefault();
        const href = $(this).attr('href');
        if (href && href !== '#') {
            window.open(href, '_blank');
        } else {
            Swal.fire('Error', 'PDF download link not available.', 'error');
        }
    });
    
    // Print payslip
    $(document).on('click', '#printPayslipBtn', function() {
        window.print();
    });
});

function updateSelectAllCheckbox() {
    const totalCheckboxes = $('.employee-checkbox').length;
    const checkedCheckboxes = $('.employee-checkbox:checked').length;
    
    $('#selectAllEmployees').prop('checked', totalCheckboxes === checkedCheckboxes);
    $('#selectAllEmployees').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
}

function calculateNetSalary(employeeId) {
    const basicSalary = parseFloat($(`input[name="basic_salary[${employeeId}]"]`).val()) || 0;
    const overtimeHours = parseFloat($(`input[name="overtime_hours[${employeeId}]"]`).val()) || 0;
    const bonusAmount = parseFloat($(`input[name="bonus_amount[${employeeId}]"]`).val()) || 0;
    const allowanceAmount = parseFloat($(`input[name="allowance_amount[${employeeId}]"]`).val()) || 0;
    const deductionAmount = parseFloat($(`input[name="deduction_amount[${employeeId}]"]`).val()) || 0;
    
    // Calculate overtime amount (1.5x rate)
    const overtimeRate = basicSalary / (22 * 8); // Assuming 22 working days, 8 hours per day
    const overtimeAmount = overtimeHours * overtimeRate * 1.5;
    
    // Calculate gross salary
    const grossSalary = basicSalary + overtimeAmount + bonusAmount + allowanceAmount;
    
    // Calculate deductions (simplified calculation)
    const nssfEmployee = Math.min(grossSalary * 0.10, 270000); // 10% capped at 270,000
    const nhifAmount = Math.min(basicSalary * 0.06, 100000); // 6% capped at 100,000
    const taxableIncome = grossSalary - nssfEmployee - nhifAmount;
    
    // Calculate PAYE (simplified)
    let paye = 0;
    if (taxableIncome > 270000) {
        if (taxableIncome <= 520000) {
            paye = (taxableIncome - 270000) * 0.08;
        } else if (taxableIncome <= 760000) {
            paye = (250000 * 0.08) + ((taxableIncome - 520000) * 0.20);
        } else if (taxableIncome <= 1000000) {
            paye = (250000 * 0.08) + (240000 * 0.20) + ((taxableIncome - 760000) * 0.25);
        } else {
            paye = (250000 * 0.08) + (240000 * 0.20) + (240000 * 0.25) + ((taxableIncome - 1000000) * 0.30);
        }
    }
    
    const totalDeductions = nssfEmployee + nhifAmount + paye + deductionAmount;
    const netSalary = grossSalary - totalDeductions;
    
    $(`.net-salary-display[data-employee-id="${employeeId}"]`).text(netSalary.toFixed(2));
}

function updateNetSalaries() {
    $('.employee-checkbox:checked').each(function() {
        const employeeId = $(this).val();
        calculateNetSalary(employeeId);
    });
}

function processPayroll() {
    const formData = new FormData($('#processPayrollForm')[0]);
    
    $.ajax({
        url: '{{ route("payroll.process") }}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response.message || 'An error occurred while processing payroll.', 'error');
        }
    });
}

function reviewPayroll() {
    const payrollId = $('#reviewPayrollModal').data('payroll-id');
    
    if (!payrollId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Payroll ID not found.',
            toast: true,
            position: 'top-end',
            timer: 3000
        });
        return;
    }
    
    const formData = new FormData($('#reviewPayrollForm')[0]);
    const action = $('input[name="review_action"]:checked').val();
    const notes = $('#review_notes').val();
    
    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we process your review.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: `/payroll/${payrollId}/review`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('reviewPayrollModal'));
                if (modal) modal.hide();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message || 'Payroll reviewed successfully.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to review payroll.'
                });
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const errorMsg = response?.message || 'An error occurred while reviewing payroll.';
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg,
                footer: xhr.status === 403 ? 'You may not have permission to review this payroll.' : ''
            });
        }
    });
}

function approvePayroll() {
    const payrollId = $('#approvePayrollModal').data('payroll-id');
    
    if (!payrollId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Payroll ID not found.',
            toast: true,
            position: 'top-end',
            timer: 3000
        });
        return;
    }
    
    const formData = new FormData($('#approvePayrollForm')[0]);
    
    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we approve the payroll.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: `/payroll/${payrollId}/approve`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('approvePayrollModal'));
                if (modal) modal.hide();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message || 'Payroll approved successfully.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to approve payroll.'
                });
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const errorMsg = response?.message || 'An error occurred while approving payroll.';
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg,
                footer: xhr.status === 403 ? 'You may not have permission to approve this payroll.' : ''
            });
        }
    });
}

function markAsPaid() {
    const payrollId = $('#markPaidModal').data('payroll-id');
    
    if (!payrollId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Payroll ID not found.',
            toast: true,
            position: 'top-end',
            timer: 3000
        });
        return;
    }
    
    // Validate form
    const paymentMethod = $('#payment_method').val();
    const paymentDate = $('#payment_date').val();
    
    if (!paymentMethod) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Please select a payment method.'
        });
        return;
    }
    
    if (!paymentDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            text: 'Please select a payment date.'
        });
        return;
    }
    
    const formData = new FormData($('#markPaidForm')[0]);
    
    // Show loading
    Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we mark the payroll as paid.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: `/payroll/${payrollId}/mark-paid`,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('markPaidModal'));
                if (modal) modal.hide();
                
                // Build SMS results HTML
                let smsDetailsHtml = '';
                if (response.sms_results && response.sms_results.length > 0) {
                    smsDetailsHtml = '<div class="mt-3 text-start"><h6>SMS Notification Details:</h6><div class="table-responsive" style="max-height: 300px; overflow-y: auto;"><table class="table table-sm table-bordered"><thead><tr><th>Employee</th><th>Phone</th><th>Status</th></tr></thead><tbody>';
                    
                    response.sms_results.forEach(function(sms) {
                        const statusBadge = sms.sent ? 
                            '<span class="badge bg-success">Sent</span>' : 
                            '<span class="badge bg-danger">Failed</span>';
                        const phoneDisplay = sms.phone || 'N/A';
                        const errorDisplay = sms.error ? `<br><small class="text-danger">${sms.error}</small>` : '';
                        
                        smsDetailsHtml += `<tr>
                            <td>${sms.employee_name || 'N/A'}</td>
                            <td>${phoneDisplay}${errorDisplay}</td>
                            <td>${statusBadge}</td>
                        </tr>`;
                    });
                    
                    smsDetailsHtml += '</tbody></table></div>';
                    
                    if (response.sms_summary) {
                        smsDetailsHtml += `<div class="mt-2">
                            <strong>Summary:</strong> 
                            Total: ${response.sms_summary.total}, 
                            <span class="text-success">Success: ${response.sms_summary.success}</span>, 
                            <span class="text-danger">Failed: ${response.sms_summary.failed}</span>
                        </div>`;
                    }
                    smsDetailsHtml += '</div>';
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    html: '<div class="text-start">' + 
                          '<p>' + (response.message || 'Payroll marked as paid successfully.') + '</p>' +
                          smsDetailsHtml +
                          '</div>',
                    confirmButtonText: 'OK',
                    width: '700px',
                    customClass: {
                        popup: 'swal2-high-z-index'
                    }
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Failed to mark payroll as paid.'
                });
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const errorMsg = response?.message || 'An error occurred while marking payroll as paid.';
            
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMsg,
                footer: xhr.status === 403 ? 'You may not have permission to mark this payroll as paid.' : 
                       xhr.status === 400 ? 'Payroll must be approved before payment.' : ''
            });
        }
    });
}

function viewPayrollDetails(payrollId) {
    // Redirect to payroll view page instead of modal
    window.location.href = '{{ url("payroll") }}/' + payrollId + '/view';
    return;
    
    console.log('Loading payroll details for ID:', payrollId);
    
    // Show loading state
    $('#payrollDetailsContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading payroll details...</p>
        </div>
    `);
    
    // Show modal first
    const modal = new bootstrap.Modal(document.getElementById('payrollDetailsModal'));
    modal.show();
    
    // Ensure modal is on top
    $('#payrollDetailsModal').css('z-index', '10003');
    
    $.ajax({
        url: `/payroll/${payrollId}/details`,
        type: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('Payroll details response:', response);
            if (response && response.success) {
                if (response.html) {
                $('#payrollDetailsContent').html(response.html);
                } else {
                    $('#payrollDetailsContent').html('<div class="alert alert-warning">Payroll details loaded but HTML not available.</div>');
                }
                $('#exportPayrollBtn').data('payroll-id', payrollId);
                // Set PDF download link
                $('#downloadPayrollReportPdf').attr('href', `/payroll/${payrollId}/report/pdf`);
            } else {
                const errorMsg = response?.message || 'Failed to load payroll details.';
                $('#payrollDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i>${errorMsg}
                    </div>
                `);
                Swal.fire({
                    title: 'Error Loading Details',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#d33',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'swal2-high-z-index'
                    }
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Payroll details AJAX error:', {xhr, status, error});
            const response = xhr.responseJSON;
            const errorMsg = response?.message || `An error occurred while loading payroll details. (Status: ${xhr.status})`;
            
            $('#payrollDetailsContent').html(`
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i><strong>Error:</strong> ${errorMsg}
                    ${xhr.status === 403 ? '<br><small>You may not have permission to view this payroll.</small>' : ''}
                </div>
            `);
            
            Swal.fire({
                title: 'Error',
                html: `<div class="text-start">
                    <p><strong>Failed to load payroll details</strong></p>
                    <p>${errorMsg}</p>
                    ${xhr.status === 403 ? '<p class="small text-muted">You may not have the required permissions.</p>' : ''}
                    ${xhr.status === 404 ? '<p class="small text-muted">Payroll record not found.</p>' : ''}
                    ${xhr.status === 500 ? '<p class="small text-muted">Server error. Please try again later.</p>' : ''}
                </div>`,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33',
                width: '600px',
                allowOutsideClick: false,
                customClass: {
                    popup: 'swal2-high-z-index',
                    container: 'swal2-container-high-z'
                }
            });
        }
    });
}

function viewPayslip(payrollItemId) {
    // Redirect to payslip page instead of modal
    window.location.href = '{{ url("payroll/payslip") }}/' + payrollItemId + '/view';
    return;
    console.log('Loading payslip for ID:', payrollItemId);
    
    // Show loading state
    $('#payslipContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading payslip details... Please wait.</p>
        </div>
    `);
    
    // Show modal first
    const modal = new bootstrap.Modal(document.getElementById('payslipModal'));
    modal.show();
    
    // Ensure modal is on top
    $('#payslipModal').css('z-index', '10062');
    $('.modal-backdrop').css('z-index', '10061');
    
    // Re-apply z-index after modal is shown
    setTimeout(function() {
        $('#payslipModal').css('z-index', '10062');
        $('.modal-backdrop').css('z-index', '10061');
    }, 100);
    
    $.ajax({
        url: `/payroll/payslip/${payrollItemId}`,
        type: 'GET',
        dataType: 'json',
        cache: false,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        beforeSend: function() {
            console.log('Sending payslip request...');
        },
        success: function(response) {
            console.log('Payslip response:', response);
            try {
                if (response && response.success) {
                    if (response.html) {
                        $('#payslipContent').html(response.html);
                        // Set PDF download link
                        $('#downloadPayslipPdf').attr('href', `/payroll/payslip/${payrollItemId}/pdf`).show();
                        console.log('Payslip HTML rendered successfully');
                    } else {
                        $('#payslipContent').html('<div class="alert alert-warning">Payslip details loaded but HTML not available.</div>');
                    }
                    
                    // Ensure modal stays on top
                    $('#payslipModal').css('z-index', '10062');
                    $('.modal-backdrop').css('z-index', '10061');
                } else {
                    const errorMsg = response?.message || 'Failed to load payslip.';
                    $('#payslipContent').html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-error-circle me-2"></i><strong>Error:</strong> ${errorMsg}
                        </div>
                    `);
                    Swal.fire({
                        title: 'Error Loading Payslip',
                        text: errorMsg,
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#d33',
                        customClass: {
                            popup: 'swal2-high-z-index'
                        }
                    });
                }
            } catch (e) {
                console.error('Error processing payslip response:', e);
                $('#payslipContent').html(`
                    <div class="alert alert-danger">
                        <h6><i class="bx bx-error-circle me-2"></i><strong>Error Processing Response</strong></h6>
                        <p class="mb-2">${e.message || 'Failed to process payslip data'}</p>
                        <button class="btn btn-sm btn-primary mt-2" onclick="viewPayslip(${payrollItemId})">
                            <i class="bx bx-refresh me-1"></i>Try Again
                        </button>
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('Payslip AJAX error:', {xhr, status, error});
            const response = xhr.responseJSON;
            const errorMsg = response?.message || `An error occurred while loading payslip. (Status: ${xhr.status})`;
            
            $('#payslipContent').html(`
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i><strong>Error:</strong> ${errorMsg}
                    ${xhr.status === 403 ? '<br><small>You may not have permission to view this payslip.</small>' : ''}
                    ${xhr.status === 404 ? '<br><small>The payslip you are looking for does not exist.</small>' : ''}
                    <br><button class="btn btn-sm btn-primary mt-2" onclick="viewPayslip(${payrollItemId})">
                        <i class="bx bx-refresh me-1"></i>Try Again
                    </button>
                </div>
            `);
            
            Swal.fire({
                title: 'Error Loading Payslip',
                text: errorMsg,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33',
                customClass: {
                    popup: 'swal2-high-z-index'
                }
            });
        }
    });
}

function reviewPayrollAction(payrollId) {
    if (!payrollId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Payroll ID not found.',
                toast: true,
                position: 'top-end',
                timer: 3000
            });
        }
        return;
    }
    
    $('#reviewPayrollModal').data('payroll-id', payrollId);
    $('#review_notes').val(''); // Clear previous notes
    
    // Use Bootstrap 5 modal
    const modal = new bootstrap.Modal(document.getElementById('reviewPayrollModal'));
    modal.show();
}

function approvePayrollAction(payrollId) {
    if (!payrollId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Payroll ID not found.',
                toast: true,
                position: 'top-end',
                timer: 3000
            });
        }
        return;
    }
    
    $('#approvePayrollModal').data('payroll-id', payrollId);
    $('#approval_notes').val(''); // Clear previous notes
    
    // Use Bootstrap 5 modal
    const modal = new bootstrap.Modal(document.getElementById('approvePayrollModal'));
    modal.show();
}

function markPaidAction(payrollId) {
    if (!payrollId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Payroll ID not found.',
                toast: true,
                position: 'top-end',
                timer: 3000
            });
        }
        return;
    }
    
    $('#markPaidModal').data('payroll-id', payrollId);
    $('#payment_method').val(''); // Reset form
    $('#payment_date').val('');
    $('#transaction_ref').val('');
    $('#debit_account_id').val('');
    $('#credit_account_id').val('');
    $('#cash_box_id').val('');
    $('#transaction_details').val('');
    
    // Set default payment date to today
    const today = new Date().toISOString().split('T')[0];
    $('#payment_date').val(today);
    
    // Hide cashbox field initially
    $('#cashbox_field').hide();
    $('#cash_box_id').removeAttr('required');
    
    // Use Bootstrap 5 modal
    const modal = new bootstrap.Modal(document.getElementById('markPaidModal'));
    modal.show();
}

// Handle payment method change to show/hide cashbox field
$(document).on('change', '#payment_method', function() {
    const paymentMethod = $(this).val();
    const cashboxField = $('#cashbox_field');
    const cashboxSelect = $('#cash_box_id');
    
    if (paymentMethod === 'cash') {
        cashboxField.show();
        cashboxSelect.attr('required', 'required');
    } else {
        cashboxField.hide();
        cashboxSelect.removeAttr('required');
        cashboxSelect.val('');
    }
});

// Refresh payroll history
function refreshPayrollHistory() {
    location.reload();
}

// Show employee full details
function showEmployeeFullDetails(employeeId) {
    if (!employeeId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid employee ID.',
                customClass: {
                    popup: 'swal2-high-z-index'
                }
            });
        }
        return;
    }
    
    const modalElement = document.getElementById('employeeFullDetailsModal');
    if (!modalElement) {
        console.error('Employee Full Details Modal not found');
        return;
    }
    
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
    });
    const content = document.getElementById('employeeFullDetailsContent');
    
    // Ensure modal appears on top
    $(modalElement).css('z-index', '10090');
    $('.modal-backdrop').last().css('z-index', '10089');
    
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading employee details...</p></div>';
    modal.show();
    
    // Get employee data from PHP - we'll pass it via data attributes
    const employeeRow = $(`.employee-row[data-employee-id="${employeeId}"]`);
    if (!employeeRow.length) {
        content.innerHTML = '<div class="alert alert-danger">Employee not found</div>';
        return;
    }
    
    // Fetch employee details via AJAX - use the correct route
    const employeeRoute = `/modules/hr/employees/${employeeId}`;
    fetch(employeeRoute, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load employee details');
        }
        return response.json();
    })
    .then(data => {
        if (!data || !data.employee) {
            content.innerHTML = '<div class="alert alert-danger"><i class="bx bx-error-circle me-2"></i>Employee data not found. Please ensure the employee exists and you have permission to view their details.</div>';
            return;
        }
        
        const employee = data.employee;
        const emp = employee.employee || {};
        // Handle both camelCase and snake_case for relationships
        const bankAccounts = employee.bank_accounts || employee.bankAccounts || [];
        const salaryDeductions = employee.salary_deductions || employee.salaryDeductions || [];
        const employeeName = employee.name || 'N/A';
        const employeeCode = emp.employee_id || employeeId;
        const department = employee.primary_department?.name || employee.primaryDepartment?.name || 'N/A';
    
    let html = `
        <div class="row">
            <div class="col-md-12 mb-3">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="bx bx-user me-2"></i>Employee Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> ${escapeHtml(employee.name || 'N/A')}</p>
                                <p><strong>Employee ID:</strong> <code>${escapeHtml(emp.employee_id || employee.id || 'N/A')}</code></p>
                                <p><strong>Department:</strong> ${escapeHtml(employee.primary_department?.name || 'N/A')}</p>
                                <p><strong>Position:</strong> ${escapeHtml(emp.position || 'N/A')}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong> ${escapeHtml(employee.email || 'N/A')}</p>
                                <p><strong>Phone:</strong> ${escapeHtml(emp.phone || emp.mobile || 'N/A')}</p>
                                <p><strong>Basic Salary:</strong> <strong class="text-primary">TZS ${formatCurrency(emp.salary || 0)}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-credit-card me-2"></i>Banking Information</h6>
                    </div>
                    <div class="card-body">
    `;
    
    if (bankAccounts && bankAccounts.length > 0) {
        html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
        html += '<thead><tr><th>Bank Name</th><th>Account Number</th><th>Account Name</th><th>Branch</th><th>Type</th></tr></thead><tbody>';
        bankAccounts.forEach(account => {
            html += `
                <tr>
                    <td>${escapeHtml(account.bank_name || 'N/A')}</td>
                    <td><code>${escapeHtml(account.account_number || 'N/A')}</code></td>
                    <td>${escapeHtml(account.account_name || 'N/A')}</td>
                    <td>${escapeHtml(account.branch_name || 'N/A')}</td>
                    <td>${account.is_primary ? '<span class="badge bg-primary">Primary</span>' : '<span class="badge bg-secondary">Secondary</span>'}</td>
                </tr>
            `;
        });
        html += '</tbody></table></div>';
    } else {
        html += '<p class="text-muted mb-0"><i class="bx bx-info-circle me-1"></i>No banking information recorded</p>';
    }
    
    html += `
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-money-withdraw me-2"></i>Salary Deductions</h6>
                    </div>
                    <div class="card-body">
    `;
    
    if (salaryDeductions && salaryDeductions.length > 0) {
        const activeDeductions = salaryDeductions.filter(d => d.is_active && (!d.end_date || new Date(d.end_date) >= new Date()));
        const monthlyTotal = activeDeductions.filter(d => d.frequency === 'monthly').reduce((sum, d) => sum + parseFloat(d.amount || 0), 0);
        const oneTimeTotal = activeDeductions.filter(d => d.frequency === 'one-time').reduce((sum, d) => sum + parseFloat(d.amount || 0), 0);
        
        html += `<p class="mb-2"><strong>Active Deductions:</strong> ${activeDeductions.length}</p>`;
        html += `<p class="mb-2"><strong>Monthly Total:</strong> <span class="text-primary">TZS ${formatCurrency(monthlyTotal)}</span></p>`;
        html += `<p class="mb-3"><strong>One-Time Total:</strong> <span class="text-info">TZS ${formatCurrency(oneTimeTotal)}</span></p>`;
        
        html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
        html += '<thead><tr><th>Type</th><th>Description</th><th>Amount</th><th>Frequency</th><th>Status</th></tr></thead><tbody>';
        salaryDeductions.forEach(deduction => {
            html += `
                <tr class="${deduction.is_active ? '' : 'table-secondary'}">
                    <td><span class="badge bg-label-primary">${escapeHtml(deduction.deduction_type || 'N/A')}</span></td>
                    <td>${escapeHtml(deduction.description || 'N/A')}</td>
                    <td><strong>TZS ${formatCurrency(deduction.amount || 0)}</strong></td>
                    <td>${deduction.frequency === 'monthly' ? '<span class="badge bg-info">Monthly</span>' : '<span class="badge bg-warning">One-Time</span>'}</td>
                    <td>${deduction.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                </tr>
            `;
        });
        html += '</tbody></table></div>';
    } else {
        html += '<p class="text-muted mb-0"><i class="bx bx-info-circle me-1"></i>No salary deductions recorded</p>';
    }
    
    html += `
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-shield me-2"></i>Statutory Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>TIN Number:</strong> <code>${escapeHtml(emp.tin_number || 'N/A')}</code></p>
                                <p><strong>NSSF Number:</strong> <code>${escapeHtml(emp.nssf_number || 'N/A')}</code></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>NHIF Number:</strong> <code>${escapeHtml(emp.nhif_number || 'N/A')}</code></p>
                                <p><strong>HESLB Number:</strong> <code>${escapeHtml(emp.heslb_number || 'N/A')}</code></p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <p><strong>Has Student Loan:</strong> 
                                    ${emp.has_student_loan ? '<span class="badge bg-warning">Yes</span>' : '<span class="badge bg-success">No</span>'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
        
        content.innerHTML = html;
        modal.show();
    })
    .catch(error => {
        console.error('Error loading employee details:', error);
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="bx bx-error-circle me-2"></i>Failed to load employee details. Please try again.
            </div>
        `;
        modal.show();
    });
}

function formatCurrency(amount) {
    return (Number(amount) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Make functions globally available
window.reviewPayrollAction = reviewPayrollAction;
window.approvePayrollAction = approvePayrollAction;
window.markPaidAction = markPaidAction;
window.viewPayrollDetails = viewPayrollDetails;
window.viewPayslip = viewPayslip;
window.refreshPayrollHistory = refreshPayrollHistory;
window.reviewPayroll = reviewPayrollAction;
window.approvePayroll = approvePayrollAction;
window.markAsPaid = markPaidAction;
window.updatePreviewBreakdown = updatePreviewBreakdown;
</script>
@endpush