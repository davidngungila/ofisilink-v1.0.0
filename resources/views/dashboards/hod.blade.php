@extends('layouts.app')

@section('title', 'HOD Dashboard - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-lg bg-gradient-info" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);">
      <div class="card-body text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-3 mb-md-0">
            <h3 class="mb-2 text-white fw-bold">
              <i class="bx bx-building me-2"></i>Department Head Dashboard
            </h3>
            <p class="mb-0 text-white-50 fs-6">
              Welcome back, {{ auth()->user()->name }}! Department overview for <strong>{{ $user->primaryDepartment->name ?? 'Your Department' }}</strong>
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('modules.hr.employees') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-group me-2"></i>Staff
            </a>
            <a href="{{ route('modules.hr.leave') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-calendar me-2"></i>Leave Requests
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
            <a href="{{ route('modules.hr.leave') }}" class="btn btn-primary w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-calendar fs-3 mb-2"></i>
              <span>Leave Requests</span>
              @if($stats['pending_leave_requests'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_leave_requests'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.permissions') }}" class="btn btn-warning w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-time-five fs-3 mb-2"></i>
              <span>Permissions</span>
              @if($stats['pending_permissions'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_permissions'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.sick-sheets') }}" class="btn btn-danger w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-file fs-3 mb-2"></i>
              <span>Sick Sheets</span>
              @if($stats['pending_sick_sheets'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_sick_sheets'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.assessments') }}" class="btn btn-success w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-clipboard fs-3 mb-2"></i>
              <span>Assessments</span>
              @if($stats['pending_assessments'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_assessments'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.files.digital') }}" class="btn btn-info w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-folder fs-3 mb-2"></i>
              <span>Files</span>
              <small class="text-white-50">{{ $stats['department_files'] ?? 0 }} files</small>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.employees') }}" class="btn btn-dark w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-group fs-3 mb-2"></i>
              <span>Staff</span>
              <small class="text-white-50">{{ $stats['department_employees'] ?? 0 }} employees</small>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Department KPI Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Department Staff</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['department_employees'] ?? 0 }}</h2>
            <small class="text-white-50">Active employees</small>
          </div>
          <i class="bx bx-group fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-warning text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Pending Approvals</h6>
            <h2 class="mb-0 fw-bold">{{ ($stats['pending_leave_requests'] ?? 0) + ($stats['pending_permissions'] ?? 0) + ($stats['pending_sick_sheets'] ?? 0) + ($stats['pending_assessments'] ?? 0) }}</h2>
            <small class="text-white-50">Requires your review</small>
          </div>
          <i class="bx bx-time fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-info text-white" style="background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Department Files</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['department_files'] ?? 0 }}</h2>
            <small class="text-white-50">Digital files</small>
          </div>
          <i class="bx bx-folder fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-success text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">File Requests</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['pending_file_requests'] ?? 0 }}</h2>
            <small class="text-white-50">Pending access</small>
          </div>
          <i class="bx bx-file-blank fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0">
        <h5 class="card-title mb-0"><i class="bx bx-bar-chart me-2"></i>Pending Approvals Breakdown</h5>
      </div>
      <div class="card-body">
        <canvas id="approvalsChart" height="200"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0">
        <h5 class="card-title mb-0"><i class="bx bx-pie-chart me-2"></i>Department Activity</h5>
      </div>
      <div class="card-body">
        <canvas id="activityChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Pending Approvals Alert -->
@if(($stats['pending_leave_requests'] ?? 0) + ($stats['pending_permissions'] ?? 0) + ($stats['pending_sick_sheets'] ?? 0) + ($stats['pending_assessments'] ?? 0) > 0)
<div class="row mb-4">
  <div class="col-12">
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
      <h5 class="alert-heading"><i class="bx bx-bell me-2"></i>Pending Approvals Requiring Your Attention!</h5>
      <p class="mb-2">You have the following items pending your approval for your department:</p>
      <ul class="mb-0">
        @if($stats['pending_leave_requests'] > 0)
          <li><strong>{{ $stats['pending_leave_requests'] }}</strong> leave request(s)</li>
        @endif
        @if($stats['pending_permissions'] > 0)
          <li><strong>{{ $stats['pending_permissions'] }}</strong> permission request(s)</li>
        @endif
        @if($stats['pending_sick_sheets'] > 0)
          <li><strong>{{ $stats['pending_sick_sheets'] }}</strong> sick sheet(s)</li>
        @endif
        @if($stats['pending_assessments'] > 0)
          <li><strong>{{ $stats['pending_assessments'] }}</strong> assessment(s)</li>
        @endif
      </ul>
      <hr>
      <div class="d-flex gap-2">
        @if($stats['pending_leave_requests'] > 0)
          <a href="{{ route('modules.hr.leave') }}" class="btn btn-sm btn-primary">Review Leave</a>
        @endif
        @if($stats['pending_permissions'] > 0)
          <a href="{{ route('modules.hr.permissions') }}" class="btn btn-sm btn-warning">Review Permissions</a>
        @endif
        @if($stats['pending_sick_sheets'] > 0)
          <a href="{{ route('modules.hr.sick-sheets') }}" class="btn btn-sm btn-danger">Review Sick Sheets</a>
        @endif
        @if($stats['pending_assessments'] > 0)
          <a href="{{ route('modules.hr.assessments') }}" class="btn btn-sm btn-success">Review Assessments</a>
        @endif
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
</div>
@endif

<!-- Pending Approvals Lists -->
<div class="row mb-4">
  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="bx bx-calendar me-2"></i>Pending Leave Requests</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Type</th>
                <th>Days</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pendingApprovals['leave_requests'] ?? [] as $request)
              <tr>
                <td>{{ $request->user->name ?? 'Unknown' }}</td>
                <td>{{ $request->leave_type ?? 'Annual' }}</td>
                <td><span class="badge bg-info">{{ $request->days_requested ?? 1 }}</span></td>
                <td><a href="{{ route('leave.show', $request->id) }}" class="btn btn-sm btn-outline-primary">Review</a></td>
              </tr>
              @empty
              <tr><td colspan="4" class="text-center text-muted">No pending leave requests</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-warning text-white">
        <h5 class="card-title mb-0"><i class="bx bx-time-five me-2"></i>Pending Permission Requests</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Employee</th>
                <th>Type</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pendingApprovals['permission_requests'] ?? [] as $request)
              <tr>
                <td>{{ $request->user->name ?? 'Unknown' }}</td>
                <td>{{ $request->time_mode ?? 'Permission' }}</td>
                <td>{{ $request->created_at ? $request->created_at->format('M d, Y') : 'N/A' }}</td>
                <td><a href="{{ route('permissions.show', $request->id) }}" class="btn btn-sm btn-outline-warning">Review</a></td>
              </tr>
              @empty
              <tr><td colspan="4" class="text-center text-muted">No pending permission requests</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Department Employees -->
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0"><i class="bx bx-group me-2"></i>Department Employees</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($departmentEmployees ?? [] as $employee)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-2">
                      <div class="avatar-initial bg-primary rounded">{{ strtoupper(substr($employee->name ?? 'U', 0, 1)) }}</div>
                    </div>
                    <strong>{{ $employee->name ?? 'Unknown' }}</strong>
                  </div>
                </td>
                <td>{{ $employee->email ?? 'N/A' }}</td>
                <td>
                  @foreach($employee->roles->take(2) as $role)
                    <span class="badge bg-secondary">{{ $role->name }}</span>
                  @endforeach
                </td>
                <td>
                  <span class="badge bg-{{ $employee->is_active ? 'success' : 'danger' }}">
                    {{ $employee->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td><a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
              </tr>
              @empty
              <tr><td colspan="5" class="text-center text-muted">No employees in your department</td></tr>
              @endforelse
            </tbody>
          </table>
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
    // Pending Approvals Chart
    const approvalsCtx = document.getElementById('approvalsChart');
    if (approvalsCtx) {
        new Chart(approvalsCtx, {
            type: 'bar',
            data: {
                labels: ['Leave', 'Permissions', 'Sick Sheets', 'Assessments'],
                datasets: [{
                    label: 'Pending Approvals',
                    data: [
                        {{ $stats['pending_leave_requests'] ?? 0 }},
                        {{ $stats['pending_permissions'] ?? 0 }},
                        {{ $stats['pending_sick_sheets'] ?? 0 }},
                        {{ $stats['pending_assessments'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Activity Chart
    const activityCtx = document.getElementById('activityChart');
    if (activityCtx) {
        new Chart(activityCtx, {
            type: 'doughnut',
            data: {
                labels: ['Employees', 'Files', 'File Requests'],
                datasets: [{
                    data: [
                        {{ $stats['department_employees'] ?? 0 }},
                        {{ $stats['department_files'] ?? 0 }},
                        {{ $stats['pending_file_requests'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>
@endpush
