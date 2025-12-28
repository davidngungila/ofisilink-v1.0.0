@extends('layouts.app')

@section('title', 'Admin Dashboard - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
      <div class="card-body text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-3 mb-md-0">
            <h3 class="mb-2 text-white fw-bold">
              <i class="bx bx-shield-quarter me-2"></i>System Administration Dashboard
            </h3>
            <p class="mb-0 text-white-50 fs-6">
              Welcome back, {{ auth()->user()->name }}! Comprehensive system overview and analytics
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('admin.system') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-server me-2"></i>System Health
            </a>
            <a href="{{ route('admin.settings') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-cog me-2"></i>Settings
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')

<!-- Quick Action Buttons -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5 class="card-title mb-3"><i class="bx bx-link-external me-2"></i>Quick Actions</h5>
        <div class="row g-3">
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-user fs-3 mb-2"></i>
              <span>Users</span>
              <small class="text-white-50">{{ $stats['total_users'] ?? 0 }} total</small>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('admin.roles') }}" class="btn btn-warning w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-shield fs-3 mb-2"></i>
              <span>Roles</span>
              <small class="text-white-50">{{ $stats['total_roles'] ?? 0 }} roles</small>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.finance.petty') }}" class="btn btn-success w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-money fs-3 mb-2"></i>
              <span>Finance</span>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.employees') }}" class="btn btn-info w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-group fs-3 mb-2"></i>
              <span>HR</span>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.files.digital') }}" class="btn btn-danger w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-folder fs-3 mb-2"></i>
              <span>Files</span>
              <small class="text-white-50">{{ $stats['total_digital_files'] ?? 0 }} files</small>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('admin.system') }}" class="btn btn-dark w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-server fs-3 mb-2"></i>
              <span>System</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Key Performance Indicators -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Total Users</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['total_users'] ?? 0 }}</h2>
            <small class="text-white-50">{{ $stats['active_users'] ?? 0 }} active, {{ $stats['inactive_users'] ?? 0 }} inactive</small>
          </div>
          <i class="bx bx-user fs-1 opacity-50"></i>
        </div>
        @php
          $activeRate = $stats['total_users'] > 0 ? round(($stats['active_users'] / $stats['total_users']) * 100) : 0;
        @endphp
        <div class="mt-3">
          <div class="progress" style="height: 6px; background: rgba(255,255,255,0.2);">
            <div class="progress-bar bg-white" role="progressbar" style="width: {{ $activeRate }}%"></div>
          </div>
          <small class="text-white-50">{{ $activeRate }}% active rate</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-success text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Departments</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['total_departments'] ?? 0 }}</h2>
            <small class="text-white-50">{{ $stats['total_roles'] ?? 0 }} roles configured</small>
          </div>
          <i class="bx bx-building fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-info text-white" style="background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">System Storage</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['total_storage_used'] ?? '0 B' }}</h2>
            <small class="text-white-50">{{ $stats['total_digital_files'] ?? 0 }} + {{ $stats['total_physical_files'] ?? 0 }} files</small>
          </div>
          <i class="bx bx-server fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-warning text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Pending Actions</h6>
            <h2 class="mb-0 fw-bold">{{ ($stats['pending_leave_requests'] ?? 0) + ($stats['pending_file_requests'] ?? 0) + ($stats['pending_payrolls'] ?? 0) + ($stats['pending_permissions'] ?? 0) }}</h2>
            <small class="text-white-50">Requires attention</small>
          </div>
          <i class="bx bx-time fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Financial Overview -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="text-muted mb-1">Total Payroll</h6>
            <h3 class="mb-0 text-primary fw-bold">{{ number_format($stats['total_payroll'] ?? 0, 0) }}</h3>
            <small class="text-success">
              <i class="bx bx-up-arrow-alt"></i> {{ number_format($stats['current_month_payroll'] ?? 0, 0) }} this month
            </small>
          </div>
          <div class="avatar bg-primary bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-money fs-2 text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="text-muted mb-1">Total Petty Cash</h6>
            <h3 class="mb-0 text-danger fw-bold">{{ number_format($stats['total_petty_cash'] ?? 0, 0) }}</h3>
            <small class="text-success">
              <i class="bx bx-up-arrow-alt"></i> {{ number_format($stats['current_month_petty_cash'] ?? 0, 0) }} this month
            </small>
          </div>
          <div class="avatar bg-danger bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-wallet fs-2 text-danger"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="text-muted mb-1">Total Imprest</h6>
            <h3 class="mb-0 text-success fw-bold">{{ number_format($stats['total_imprest'] ?? 0, 0) }}</h3>
            <small class="text-success">
              <i class="bx bx-up-arrow-alt"></i> {{ number_format($stats['current_month_imprest'] ?? 0, 0) }} this month
            </small>
          </div>
          <div class="avatar bg-success bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-credit-card fs-2 text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h6 class="text-muted mb-1">System Health</h6>
            <h3 class="mb-0 text-success fw-bold">
              <span class="badge bg-success">{{ $systemHealth['database_status'] ?? 'connected' }}</span>
            </h3>
            <small class="text-muted">Database & Cache Active</small>
          </div>
          <div class="avatar bg-success bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-check-circle fs-2 text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
  <div class="col-lg-8 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0">
        <h5 class="card-title mb-0"><i class="bx bx-line-chart me-2"></i>Monthly Trends - {{ now()->year }}</h5>
      </div>
      <div class="card-body">
        <canvas id="monthlyTrendsChart" height="100"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-4 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0">
        <h5 class="card-title mb-0"><i class="bx bx-pie-chart me-2"></i>Role Distribution</h5>
      </div>
      <div class="card-body">
        <canvas id="roleDistributionChart" height="250"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- HR & Operations Statistics -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-primary">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Leave Requests</h6>
            <h4 class="mb-0">{{ $stats['pending_leave_requests'] ?? 0 }}</h4>
            <small class="text-muted">Pending approval</small>
          </div>
          <i class="bx bx-calendar fs-1 text-primary opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-warning">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Permission Requests</h6>
            <h4 class="mb-0">{{ $stats['pending_permissions'] ?? 0 }}</h4>
            <small class="text-muted">Awaiting review</small>
          </div>
          <i class="bx bx-time fs-1 text-warning opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-danger">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Sick Sheets</h6>
            <h4 class="mb-0">{{ $stats['pending_sick_sheets'] ?? 0 }}</h4>
            <small class="text-muted">Pending verification</small>
          </div>
          <i class="bx bx-file fs-1 text-danger opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-info">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Assessments</h6>
            <h4 class="mb-0">{{ $stats['pending_assessments'] ?? 0 }}</h4>
            <small class="text-muted">Pending HOD review</small>
          </div>
          <i class="bx bx-target-lock fs-1 text-info opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Department Breakdown & Recent Activities -->
<div class="row mb-4">
  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="bx bx-building me-2"></i>Department Breakdown</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Department</th>
                <th>Employees</th>
                <th>Files</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($departmentStats ?? [] as $dept)
              <tr>
                <td><strong>{{ $dept->name ?? 'Unknown' }}</strong></td>
                <td><span class="badge bg-primary">{{ $dept->primary_users_count ?? 0 }}</span></td>
                <td><span class="badge bg-info">{{ $dept->file_folders_count ?? 0 }}</span></td>
                <td><a href="{{ route('departments.show', $dept->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No departments found</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0"><i class="bx bx-time me-2"></i>Recent System Activities</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
          <table class="table table-sm">
            <thead>
              <tr>
                <th>Action</th>
                <th>User</th>
                <th>Time</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentActivities ?? [] as $activity)
              <tr>
                <td>
                  <span class="badge bg-{{ $activity->type === 'create' ? 'success' : ($activity->type === 'update' ? 'warning' : ($activity->type === 'delete' ? 'danger' : 'info')) }}">
                    {{ $activity->action ?? 'Activity' }}
                  </span>
                </td>
                <td>{{ $activity->user->name ?? 'System' }}</td>
                <td><small>{{ $activity->created_at ? $activity->created_at->diffForHumans() : 'Recently' }}</small></td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center text-muted">No recent activities</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Pending Approvals -->
<div class="row mb-4">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-warning text-white">
        <h5 class="card-title mb-0"><i class="bx bx-check-circle me-2"></i>Pending Approvals Requiring Attention</h5>
      </div>
      <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#leave-requests">Leave Requests ({{ $pendingApprovals['leave_requests']->count() }})</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#permissions">Permissions ({{ $pendingApprovals['permission_requests']->count() }})</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#sick-sheets">Sick Sheets ({{ $pendingApprovals['sick_sheets']->count() }})</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#petty-cash">Petty Cash ({{ $pendingApprovals['petty_cash']->count() }})</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#imprest">Imprest ({{ $pendingApprovals['imprest_requests']->count() }})</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#assessments">Assessments ({{ $pendingApprovals['assessments']->count() }})</a>
          </li>
        </ul>
        <div class="tab-content mt-3">
          <div class="tab-pane fade show active" id="leave-requests">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingApprovals['leave_requests'] as $request)
                  <tr>
                    <td>{{ $request->user->name ?? 'N/A' }}</td>
                    <td>{{ $request->leave_type ?? 'N/A' }}</td>
                    <td>{{ $request->start_date ?? 'N/A' }} to {{ $request->end_date ?? 'N/A' }}</td>
                    <td><span class="badge bg-warning">{{ $request->status ?? 'Pending' }}</span></td>
                    <td><a href="{{ route('leaves.show', $request->id) }}" class="btn btn-sm btn-primary">Review</a></td>
                  </tr>
                  @empty
                  <tr><td colspan="5" class="text-center text-muted">No pending leave requests</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="permissions">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingApprovals['permission_requests'] as $request)
                  <tr>
                    <td>{{ $request->user->name ?? 'N/A' }}</td>
                    <td>{{ $request->time_mode ?? 'N/A' }}</td>
                    <td>{{ $request->start_datetime ?? 'N/A' }} to {{ $request->end_datetime ?? 'N/A' }}</td>
                    <td><span class="badge bg-warning">{{ $request->status ?? 'Pending' }}</span></td>
                    <td><a href="{{ route('permissions.show', $request->id) }}" class="btn btn-sm btn-primary">Review</a></td>
                  </tr>
                  @empty
                  <tr><td colspan="5" class="text-center text-muted">No pending permission requests</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="sick-sheets">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingApprovals['sick_sheets'] as $sheet)
                  <tr>
                    <td>{{ $sheet->employee->name ?? 'N/A' }}</td>
                    <td>{{ $sheet->sick_date ?? 'N/A' }}</td>
                    <td><span class="badge bg-warning">{{ $sheet->status ?? 'Pending' }}</span></td>
                    <td><a href="{{ route('sick-sheets.show', $sheet->id) }}" class="btn btn-sm btn-primary">Review</a></td>
                  </tr>
                  @empty
                  <tr><td colspan="4" class="text-center text-muted">No pending sick sheets</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="petty-cash">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Requestor</th>
                    <th>Amount</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingApprovals['petty_cash'] as $voucher)
                  <tr>
                    <td>{{ $voucher->user->name ?? 'N/A' }}</td>
                    <td>{{ number_format($voucher->amount ?? 0, 0) }} TZS</td>
                    <td>{{ \Illuminate\Support\Str::limit($voucher->purpose ?? 'N/A', 30) }}</td>
                    <td><span class="badge bg-warning">{{ $voucher->status ?? 'Pending' }}</span></td>
                    <td><a href="{{ route('petty-cash.show', $voucher->id) }}" class="btn btn-sm btn-primary">Review</a></td>
                  </tr>
                  @empty
                  <tr><td colspan="5" class="text-center text-muted">No pending petty cash requests</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="imprest">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Accountant</th>
                    <th>Amount</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingApprovals['imprest_requests'] as $request)
                  <tr>
                    <td>{{ $request->accountant->name ?? 'N/A' }}</td>
                    <td>{{ number_format($request->amount ?? 0, 0) }} TZS</td>
                    <td>{{ \Illuminate\Support\Str::limit($request->purpose ?? 'N/A', 30) }}</td>
                    <td><span class="badge bg-warning">{{ $request->status ?? 'Pending' }}</span></td>
                    <td><a href="{{ route('imprest.show', $request->id) }}" class="btn btn-sm btn-primary">Review</a></td>
                  </tr>
                  @empty
                  <tr><td colspan="5" class="text-center text-muted">No pending imprest requests</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          <div class="tab-pane fade" id="assessments">
            <div class="table-responsive">
              <table class="table table-hover">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Responsibility</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingApprovals['assessments'] as $assessment)
                  <tr>
                    <td>{{ $assessment->employee->name ?? 'N/A' }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($assessment->main_responsibility ?? 'N/A', 40) }}</td>
                    <td><span class="badge bg-warning">{{ $assessment->status ?? 'Pending' }}</span></td>
                    <td><a href="{{ route('assessments.show', $assessment->id) }}" class="btn btn-sm btn-primary">Review</a></td>
                  </tr>
                  @empty
                  <tr><td colspan="4" class="text-center text-muted">No pending assessments</td></tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
  .bg-gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important; }
  .bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important; }
  .bg-gradient-info { background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%) !important; }
  .bg-gradient-warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    // Monthly Trends Chart
    const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart');
    if (monthlyTrendsCtx) {
        const monthlyData = @json($monthlyTrends ?? []);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        // Prepare data
        const payrollData = Array(12).fill(0);
        const pettyCashData = Array(12).fill(0);
        const imprestData = Array(12).fill(0);
        const filesData = Array(12).fill(0);
        
        monthlyData.payroll?.forEach(item => {
            if (item.month) payrollData[item.month - 1] = parseFloat(item.total || 0) / 1000000; // Convert to millions
        });
        monthlyData.petty_cash?.forEach(item => {
            if (item.month) pettyCashData[item.month - 1] = parseFloat(item.total || 0) / 1000000;
        });
        monthlyData.imprest?.forEach(item => {
            if (item.month) imprestData[item.month - 1] = parseFloat(item.total || 0) / 1000000;
        });
        monthlyData.files?.forEach(item => {
            if (item.month) filesData[item.month - 1] = parseInt(item.count || 0);
        });
        
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [
                    {
                        label: 'Payroll (Millions TZS)',
                        data: payrollData,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Petty Cash (Millions TZS)',
                        data: pettyCashData,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Imprest (Millions TZS)',
                        data: imprestData,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Role Distribution Chart
    const roleDistributionCtx = document.getElementById('roleDistributionChart');
    if (roleDistributionCtx) {
        const roleData = @json($roleDistribution ?? []);
        const labels = roleData.map(r => r.display_name || r.name);
        const data = roleData.map(r => r.users_count || 0);
        
        new Chart(roleDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(199, 199, 199, 0.8)',
                        'rgba(83, 102, 255, 0.8)',
                        'rgba(255, 99, 255, 0.8)',
                        'rgba(99, 255, 132, 0.8)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    }
});
</script>
@endpush
