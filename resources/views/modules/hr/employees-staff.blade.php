<!-- Advanced Staff View with Full Details -->
@php
    $payrollHistory = $payrollHistory ?? collect();
    $leaveBalances = $leaveBalances ?? collect();
    $recentLeaves = $recentLeaves ?? collect();
    $attendanceSummary = $attendanceSummary ?? collect();
    $performanceReviews = $performanceReviews ?? collect();
    $recentActivities = $recentActivities ?? collect();
    $presentDays = $presentDays ?? 0;
    $absentDays = $absentDays ?? 0;
    $attendanceRate = $attendanceRate ?? 0;
@endphp

<!-- Statistics Dashboard -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-label-primary me-3">
                        <i class="bx bx-dollar fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Monthly Salary</h6>
                        <h4 class="mb-0 text-primary">
                            @if($employee && $employee->employee && $employee->employee->salary)
                                {{ number_format($employee->employee->salary, 0) }}
                            @else
                                0
                            @endif
                        </h4>
                        <small class="text-muted">TZS</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-label-success me-3">
                        <i class="bx bx-check-circle fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Attendance Rate</h6>
                        <h4 class="mb-0 text-success">{{ number_format($attendanceRate, 1) }}%</h4>
                        <small class="text-muted">{{ $presentDays }}/30 days</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-label-info me-3">
                        <i class="bx bx-calendar fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Leave Balance</h6>
                        <h4 class="mb-0 text-info">
                            {{ $leaveBalances->sum('balance') ?? 0 }}
                        </h4>
                        <small class="text-muted">Days Available</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-lg bg-label-warning me-3">
                        <i class="bx bx-time fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1 text-muted">Years of Service</h6>
                        <h4 class="mb-0 text-warning">
                            @if($employee && $employee->hire_date)
                                {{ \Carbon\Carbon::parse($employee->hire_date)->diffInYears(\Carbon\Carbon::now()) }}
                            @else
                                0
                            @endif
                        </h4>
                        <small class="text-muted">Years</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Profile Section -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 position-relative">
                            @if($employee && $employee->photo)
                                <img src="{{ Storage::url('photos/' . $employee->photo) }}" alt="Employee Photo" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                            @else
                                <span class="avatar-initial rounded-circle bg-white text-primary" style="font-size: 2rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">{{ $employee ? substr($employee->name, 0, 1) : 'U' }}</span>
                            @endif
                            @if($employee)
                            <button class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" style="width: 28px; height: 28px; padding: 0; font-size: 12px;" onclick="openUploadPhotoModal({{ $employee->id }})" title="Change Photo">
                                <i class="bx bx-camera"></i>
                            </button>
                            @endif
                        </div>
                        <div>
                            <h4 class="card-title text-white mb-1">{{ $employee->name ?? 'User' }}</h4>
                            <p class="text-white-50 mb-0">
                                <i class="bx bx-id-card me-1"></i>{{ $employee->employee_id ?? 'N/A' }} 
                                <span class="mx-2">•</span>
                                <i class="bx bx-building me-1"></i>{{ $employee->primaryDepartment->name ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="badge bg-white text-primary px-3 py-2">
                            <i class="bx bx-check-circle me-1"></i>Profile {{ $employee->completion_percentage ?? 0 }}% Complete
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Enhanced Profile Tabs -->
                <ul class="nav nav-tabs nav-tabs-custom px-3 pt-3" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button" role="tab">
                            <i class="bx bx-home me-2"></i>Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button" role="tab">
                            <i class="bx bx-user me-2"></i>Personal
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="employment-tab" data-bs-toggle="tab" data-bs-target="#employment" type="button" role="tab">
                            <i class="bx bx-briefcase me-2"></i>Employment
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="payroll-tab" data-bs-toggle="tab" data-bs-target="#payroll" type="button" role="tab">
                            <i class="bx bx-receipt me-2"></i>Payroll
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="leave-tab" data-bs-toggle="tab" data-bs-target="#leave" type="button" role="tab">
                            <i class="bx bx-calendar me-2"></i>Leave
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">
                            <i class="bx bx-time me-2"></i>Attendance
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button" role="tab">
                            <i class="bx bx-file me-2"></i>Documents
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-4" id="profileTabsContent">
                    @if($employee)
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="row g-4">
                            <!-- Quick Stats -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-stats me-2 text-primary"></i>Quick Statistics
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-6">
                                                <div class="text-center p-3 bg-white rounded">
                                                    <h5 class="mb-1 text-primary">{{ $payrollHistory->count() }}</h5>
                                                    <small class="text-muted">Payslips</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center p-3 bg-white rounded">
                                                    <h5 class="mb-1 text-success">{{ $recentLeaves->count() }}</h5>
                                                    <small class="text-muted">Leave Requests</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center p-3 bg-white rounded">
                                                    <h5 class="mb-1 text-info">{{ $performanceReviews->count() }}</h5>
                                                    <small class="text-muted">Reviews</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center p-3 bg-white rounded">
                                                    <h5 class="mb-1 text-warning">{{ $employee->educations->count() ?? 0 }}</h5>
                                                    <small class="text-muted">Education</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Payslip -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-receipt me-2 text-success"></i>Latest Payslip
                                        </h6>
                                        @if($payrollHistory->isNotEmpty())
                                            @php $latestPayroll = $payrollHistory->first(); @endphp
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Period:</span>
                                                <span class="fw-semibold">{{ $latestPayroll->payroll->pay_period ?? 'N/A' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="text-muted">Net Pay:</span>
                                                <span class="fw-bold text-success">TZS {{ number_format($latestPayroll->net_salary ?? 0, 0) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted">Status:</span>
                                                <span class="badge bg-label-{{ $latestPayroll->status === 'paid' ? 'success' : ($latestPayroll->status === 'approved' ? 'info' : 'warning') }}">
                                                    {{ ucfirst($latestPayroll->status ?? 'N/A') }}
                                                </span>
                                            </div>
                                            <a href="{{ route('payroll.payslip.page', $latestPayroll->id) }}" class="btn btn-sm btn-primary w-100">
                                                <i class="bx bx-show me-1"></i>View Details
                                            </a>
                                        @else
                                            <p class="text-muted text-center py-3">No payslips available</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Leave Balance Summary -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-calendar-check me-2 text-info"></i>Leave Balance
                                        </h6>
                                        @if($leaveBalances->isNotEmpty())
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Type</th>
                                                            <th class="text-end">Balance</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($leaveBalances as $balance)
                                                        <tr>
                                                            <td>{{ $balance->leaveType->name ?? 'N/A' }}</td>
                                                            <td class="text-end fw-semibold">{{ $balance->balance ?? 0 }} days</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted text-center py-3">No leave balance data</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Performance Summary -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light h-100">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-trending-up me-2 text-warning"></i>Performance
                                        </h6>
                                        @if($performanceReviews->isNotEmpty())
                                            @php 
                                                $latestReview = $performanceReviews->first(); 
                                                $rating = $latestReview->overall_rating ?? 0;
                                                // Convert percentage to 5-point scale (0-100% to 0-5)
                                                $ratingOutOf5 = ($rating / 100) * 5;
                                            @endphp
                                            <div class="mb-2">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="text-muted">Contribution:</span>
                                                    <span class="fw-bold">{{ number_format($rating, 1) }}%</span>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $rating }}%"></div>
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                Last reviewed: {{ $latestReview->review_date ? \Carbon\Carbon::parse($latestReview->review_date)->format('M d, Y') : 'N/A' }}
                                                @if(isset($latestReview->reviewer))
                                                    <br>by {{ $latestReview->reviewer }}
                                                @endif
                                            </small>
                                        @else
                                            <p class="text-muted text-center py-3">No performance reviews</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Personal Information Tab -->
                    <div class="tab-pane fade" id="personal" role="tabpanel">
                        <div class="row g-4">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-user-circle me-2 text-primary"></i>Basic Information
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Full Name</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->name }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small">Email</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->email }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small">Phone</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->phone ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small">Employee ID</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->employee_id ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small">Hire Date</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Department & Status -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-building me-2 text-info"></i>Department & Status
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Department</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->primaryDepartment->name ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small">Position</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->employee->position ?? 'N/A' }}</p>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label text-muted small">Status</label>
                                                <span class="badge {{ $employee->is_active ? 'bg-label-success' : 'bg-label-danger' }}">
                                                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Roles</label>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @if($employee->roles && $employee->roles->count() > 0)
                                                        @foreach($employee->roles as $role)
                                                            <span class="badge bg-label-primary">{{ $role->display_name }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted">No roles assigned</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Emergency Contacts -->
                            @if($employee->nextOfKin && $employee->nextOfKin->count() > 0)
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-phone me-2 text-danger"></i>Emergency Contacts
                                        </h6>
                                        @foreach($employee->nextOfKin->take(2) as $nok)
                                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                            <p class="mb-1 fw-semibold">{{ $nok->name ?? 'N/A' }}</p>
                                            <p class="mb-1 small text-muted">{{ $nok->relationship ?? 'N/A' }}</p>
                                            <p class="mb-0 small text-muted">
                                                <i class="bx bx-phone me-1"></i>{{ $nok->phone ?? 'N/A' }}
                                            </p>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                            
                            <!-- Education -->
                            @if($employee->educations && $employee->educations->count() > 0)
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-book me-2 text-success"></i>Education
                                        </h6>
                                        @foreach($employee->educations->take(2) as $edu)
                                        <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                            <p class="mb-1 fw-semibold">{{ $edu->institution ?? 'N/A' }}</p>
                                            <p class="mb-1 small text-muted">{{ $edu->degree ?? 'N/A' }}</p>
                                            <p class="mb-0 small text-muted">{{ $edu->graduation_year ?? 'N/A' }}</p>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Employment Information Tab -->
                    <div class="tab-pane fade" id="employment" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-briefcase me-2 text-primary"></i>Employment Details
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Employment Type</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->employee && $employee->employee->employment_type ? ucfirst($employee->employee->employment_type) : 'N/A' }}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Position</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->employee ? $employee->employee->position : 'N/A' }}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Hire Date</label>
                                                <p class="mb-0 fw-semibold">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Years of Service</label>
                                                <p class="mb-0 fw-semibold">
                                                    @if($employee->hire_date)
                                                        {{ \Carbon\Carbon::parse($employee->hire_date)->diffInYears(\Carbon\Carbon::now()) }} years
                                                        ({{ \Carbon\Carbon::parse($employee->hire_date)->diffInMonths(\Carbon\Carbon::now()) }} months)
                                                    @else
                                                        N/A
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-dollar me-2 text-success"></i>Compensation
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Basic Salary</label>
                                                <p class="mb-0 fw-semibold text-success fs-5">
                                                    @if($employee->employee && $employee->employee->salary)
                                                        TZS {{ number_format($employee->employee->salary, 0) }}
                                                    @else
                                                        N/A
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label text-muted small">Employment Status</label>
                                                <p class="mb-0">
                                                    <span class="badge {{ $employee->employee && $employee->employee->employment_status === 'active' ? 'bg-label-success' : 'bg-label-warning' }}">
                                                        {{ $employee->employee && $employee->employee->employment_status ? ucfirst($employee->employee->employment_status) : 'N/A' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payroll Tab -->
                    <div class="tab-pane fade" id="payroll" role="tabpanel">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-title mb-0">
                                        <i class="bx bx-receipt me-2 text-primary"></i>Payroll History
                                    </h6>
                                    <a href="{{ route('modules.hr.payroll') }}" class="btn btn-sm btn-primary">
                                        <i class="bx bx-show me-1"></i>View All
                                    </a>
                                </div>
                                @if($payrollHistory->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Period</th>
                                                    <th>Gross</th>
                                                    <th>Deductions</th>
                                                    <th>Net Pay</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($payrollHistory->take(10) as $payroll)
                                                <tr>
                                                    <td>{{ $payroll->payroll->pay_period ?? 'N/A' }}</td>
                                                    <td>TZS {{ number_format($payroll->gross_salary ?? 0, 0) }}</td>
                                                    <td>TZS {{ number_format($payroll->total_deductions ?? 0, 0) }}</td>
                                                    <td class="fw-bold text-success">TZS {{ number_format($payroll->net_salary ?? 0, 0) }}</td>
                                                    <td>
                                                        <span class="badge bg-label-{{ $payroll->status === 'paid' ? 'success' : ($payroll->status === 'approved' ? 'info' : 'warning') }}">
                                                            {{ ucfirst($payroll->status ?? 'N/A') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('payroll.payslip.page', $payroll->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="bx bx-show"></i>
                                                        </a>
                                                        <a href="{{ route('payroll.payslip.pdf', $payroll->id) }}" class="btn btn-sm btn-outline-danger" target="_blank">
                                                            <i class="bx bx-download"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3">No payroll history available</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Leave Tab -->
                    <div class="tab-pane fade" id="leave" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">
                                            <i class="bx bx-calendar-check me-2 text-info"></i>Leave Balance
                                        </h6>
                                        @if($leaveBalances->isNotEmpty())
                                            <div class="table-responsive">
                                                <table class="table table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Type</th>
                                                            <th class="text-end">Balance</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($leaveBalances as $balance)
                                                        <tr>
                                                            <td>{{ $balance->leaveType->name ?? 'N/A' }}</td>
                                                            <td class="text-end fw-semibold">{{ $balance->balance ?? 0 }} days</td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <p class="text-muted text-center py-3">No leave balance data</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-calendar me-2 text-warning"></i>Recent Requests
                                            </h6>
                                            <a href="{{ route('modules.hr.leave') }}" class="btn btn-sm btn-primary">
                                                <i class="bx bx-plus me-1"></i>Request Leave
                                            </a>
                                        </div>
                                        @if($recentLeaves->isNotEmpty())
                                            <div class="list-group list-group-flush">
                                                @foreach($recentLeaves->take(5) as $leave)
                                                <div class="list-group-item px-0">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <p class="mb-1 fw-semibold">{{ $leave->leaveType->name ?? 'N/A' }}</p>
                                                            <small class="text-muted">
                                                                {{ $leave->start_date ? \Carbon\Carbon::parse($leave->start_date)->format('M d') : 'N/A' }} - 
                                                                {{ $leave->end_date ? \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') : 'N/A' }}
                                                            </small>
                                                        </div>
                                                        <span class="badge bg-label-{{ $leave->status === 'approved' ? 'success' : ($leave->status === 'pending' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($leave->status ?? 'N/A') }}
                                                        </span>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted text-center py-3">No leave requests</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Attendance Tab -->
                    <div class="tab-pane fade" id="attendance" role="tabpanel">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title mb-3">
                                    <i class="bx bx-time me-2 text-success"></i>Attendance Summary (Last 30 Days)
                                </h6>
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-white rounded">
                                            <h4 class="mb-1 text-success">{{ $presentDays }}</h4>
                                            <small class="text-muted">Present Days</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-white rounded">
                                            <h4 class="mb-1 text-danger">{{ $absentDays }}</h4>
                                            <small class="text-muted">Absent Days</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center p-3 bg-white rounded">
                                            <h4 class="mb-1 text-primary">{{ number_format($attendanceRate, 1) }}%</h4>
                                            <small class="text-muted">Attendance Rate</small>
                                        </div>
                                    </div>
                                </div>
                                @if($attendanceSummary->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Check In</th>
                                                    <th>Check Out</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($attendanceSummary->take(15) as $attendance)
                                                <tr>
                                                    <td>{{ $attendance->attendance_date ? \Carbon\Carbon::parse($attendance->attendance_date)->format('M d, Y') : 'N/A' }}</td>
                                                    <td>{{ $attendance->check_in_time ?? 'N/A' }}</td>
                                                    <td>{{ $attendance->check_out_time ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-label-{{ $attendance->status === 'present' ? 'success' : 'danger' }}">
                                                            {{ ucfirst($attendance->status ?? 'N/A') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="text-muted text-center py-3">No attendance records available</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Documents Tab -->
                    <div class="tab-pane fade" id="documents" role="tabpanel">
                        <div class="row g-4">
                            @if($employee->employeeDocuments && $employee->employeeDocuments->count() > 0)
                                @foreach($employee->employeeDocuments as $doc)
                                <div class="col-md-4">
                                    <div class="card border-0 bg-light h-100">
                                        <div class="card-body text-center">
                                            <i class="bx bx-file-pdf text-danger" style="font-size: 3rem;"></i>
                                            <h6 class="mt-3 mb-1">{{ $doc->document_type ?? 'Document' }}</h6>
                                            <small class="text-muted">{{ $doc->document_name ?? 'N/A' }}</small>
                                            <div class="mt-3">
                                                @if($doc->file_path)
                                                    <a href="{{ Storage::url($doc->file_path) }}" class="btn btn-sm btn-primary" target="_blank">
                                                        <i class="bx bx-download me-1"></i>Download
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="col-12">
                                    <div class="text-center py-5">
                                        <i class="bx bx-file text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-3">No documents available</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <!-- No Employee Data -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="text-center py-5">
                            <i class="bx bx-user text-muted" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 text-muted">No Employee Profile Found</h5>
                            <p class="text-muted">Please contact HR to set up your employee profile.</p>
                            <button type="button" class="btn btn-primary" onclick="contactHR()">
                                <i class="bx bx-envelope me-2"></i>Contact HR
                            </button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h6 class="card-title mb-0 text-white">
                    <i class="bx bx-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($employee)
                    <button type="button" class="btn btn-primary" onclick="viewEmployeeDetails({{ $employee->id }})">
                        <i class="bx bx-show me-2"></i>View Full Profile
                    </button>
                    <a href="{{ route('modules.hr.payroll') }}" class="btn btn-outline-info">
                        <i class="bx bx-receipt me-2"></i>View Payslips
                    </a>
                    <a href="{{ route('modules.hr.leave') }}" class="btn btn-outline-warning">
                        <i class="bx bx-calendar me-2"></i>Request Leave
                    </a>
                    <a href="{{ route('petty-cash.index') }}" class="btn btn-outline-success">
                        <i class="bx bx-receipt me-2"></i>Petty Cash
                    </a>
                    @else
                    <button type="button" class="btn btn-primary" onclick="contactHR()">
                        <i class="bx bx-envelope me-2"></i>Contact HR
                    </button>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bx bx-time me-2"></i>Recent Activity
                </h6>
            </div>
            <div class="card-body">
                @if($recentActivities && $recentActivities->count() > 0)
                    <div class="timeline">
                        @foreach($recentActivities->take(10) as $activity)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ ($activity->action ?? '') === 'created' ? 'primary' : (($activity->action ?? '') === 'updated' ? 'info' : 'success') }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ $activity->description ?? ($activity->action ?? 'Activity') }}</h6>
                                <small class="text-muted">{{ $activity->created_at ? $activity->created_at->diffForHumans() : 'N/A' }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center py-3">No recent activity</p>
                @endif
            </div>
        </div>
        
        <!-- Performance Summary -->
        <div class="card border-0 shadow-sm">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bx bx-trending-up me-2"></i>Performance
                </h6>
            </div>
            <div class="card-body">
                @if($performanceReviews->isNotEmpty())
                    @php 
                        $avgRating = $performanceReviews->avg('overall_rating'); 
                        // Average contribution percentage
                    @endphp
                    <div class="text-center mb-3">
                        <h2 class="mb-0 text-primary">{{ number_format($avgRating, 1) }}%</h2>
                        <small class="text-muted">Avg Contribution</small>
                    </div>
                    <div class="progress mb-3" style="height: 10px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $avgRating }}%"></div>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('assessments.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-bar-chart me-1"></i>View Assessments
                        </a>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No performance data</p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function viewEmployeeDetails(employeeId) {
    if (!employeeId) {
        Swal.fire('Error', 'Invalid employee ID.', 'error');
        return;
    }
    // Redirect to employee show page (employee-show.blade.php)
    window.location.href = '/employees/' + employeeId;
}

function viewPayslips() {
    window.location.href = '{{ route("modules.hr.payroll") }}';
}

function requestLeave() {
    window.location.href = '{{ route("modules.hr.leave") }}';
}

function viewPettyCash() {
    window.location.href = '{{ route("petty-cash.index") }}';
}

function viewTasks() {
    window.location.href = '{{ route("modules.tasks") ?? "#" }}';
}

function viewPerformanceDetails() {
    Swal.fire({
        title: 'Performance Report',
        text: 'Detailed performance report will be available soon.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}

function contactHR() {
    Swal.fire({
        title: 'Contact HR Department',
        text: 'Please contact the HR department to set up your employee profile.',
        icon: 'info',
        confirmButtonText: 'OK'
    });
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content {
    padding-left: 15px;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #696cff 0%, #8592a3 100%);
}

.nav-tabs-custom .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #6c757d;
    padding: 0.75rem 1rem;
}

.nav-tabs-custom .nav-link.active {
    border-bottom-color: #696cff;
    color: #696cff;
    background: none;
}

.nav-tabs-custom .nav-link:hover {
    border-bottom-color: #696cff;
    color: #696cff;
}
</style>
