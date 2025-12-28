@extends('layouts.app')

@section('title', 'HR Dashboard - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-lg bg-gradient-primary" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-3 mb-md-0">
            <h3 class="mb-2 text-white fw-bold">
              <i class="bx bx-user-circle me-2"></i>HR Dashboard
            </h3>
            <p class="mb-0 text-white-50 fs-6">
              Welcome back, {{ auth()->user()->name }}! Comprehensive HR overview and employee management
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('modules.hr.employees') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-group me-2"></i>Employees
            </a>
            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-credit-card me-2"></i>Payroll
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
            <a href="{{ route('modules.hr.employees') }}" class="btn btn-primary w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-group fs-3 mb-2"></i>
              <span>Employees</span>
              <small class="text-white-50">{{ $stats['total_employees'] ?? 0 }} total</small>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.leave') }}" class="btn btn-warning w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-calendar fs-3 mb-2"></i>
              <span>Leave</span>
              @if($stats['pending_leave_requests'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_leave_requests'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-success w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-credit-card fs-3 mb-2"></i>
              <span>Payroll</span>
              @if($stats['pending_payroll'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_payroll'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.permissions') }}" class="btn btn-info w-100 d-flex flex-column align-items-center py-3 shadow-sm">
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
            <a href="{{ route('modules.hr.departments') }}" class="btn btn-dark w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-building fs-3 mb-2"></i>
              <span>Departments</span>
              <small class="text-white-50">{{ $stats['total_departments'] ?? 0 }} total</small>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- HR KPI Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Total Employees</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['total_employees'] ?? 0 }}</h2>
            <small class="text-white-50">{{ $stats['total_departments'] ?? 0 }} departments</small>
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
            <h6 class="text-white-50 mb-2">Pending Leave</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['pending_leave_requests'] ?? 0 }}</h2>
            <small class="text-white-50">Awaiting approval</small>
          </div>
          <i class="bx bx-calendar fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-success text-white" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Total Payroll</h6>
            <h2 class="mb-0 fw-bold">{{ number_format($stats['total_payroll_amount'] ?? 0, 0) }}</h2>
            <small class="text-white-50">{{ number_format($stats['current_month_payroll'] ?? 0, 0) }} this month</small>
          </div>
          <i class="bx bx-credit-card fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-info text-white" style="background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Pending Actions</h6>
            <h2 class="mb-0 fw-bold">{{ ($stats['pending_permissions'] ?? 0) + ($stats['pending_sick_sheets'] ?? 0) + ($stats['pending_leave_requests'] ?? 0) }}</h2>
            <small class="text-white-50">Requires attention</small>
          </div>
          <i class="bx bx-time fs-1 opacity-50"></i>
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
        <h5 class="card-title mb-0"><i class="bx bx-line-chart me-2"></i>Leave Requests Trend - {{ now()->year }}</h5>
      </div>
      <div class="card-body">
        <canvas id="leaveTrendsChart" height="100"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-4 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0">
        <h5 class="card-title mb-0"><i class="bx bx-pie-chart me-2"></i>Department Distribution</h5>
      </div>
      <div class="card-body">
        <canvas id="departmentChart" height="250"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Pending Actions Alert -->
@if($stats['pending_leave_requests'] > 0 || $stats['pending_permissions'] > 0 || $stats['pending_sick_sheets'] > 0 || $stats['pending_payroll'] > 0)
<div class="row mb-4">
  <div class="col-12">
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
      <h5 class="alert-heading"><i class="bx bx-bell me-2"></i>Action Required!</h5>
      <p class="mb-2">You have pending items that require your attention:</p>
      <ul class="mb-0">
        @if($stats['pending_leave_requests'] > 0)
          <li><strong>{{ $stats['pending_leave_requests'] }}</strong> leave request(s) pending approval</li>
        @endif
        @if($stats['pending_permissions'] > 0)
          <li><strong>{{ $stats['pending_permissions'] }}</strong> permission request(s) pending review</li>
        @endif
        @if($stats['pending_sick_sheets'] > 0)
          <li><strong>{{ $stats['pending_sick_sheets'] }}</strong> sick sheet(s) pending review</li>
        @endif
        @if($stats['pending_payroll'] > 0)
          <li><strong>{{ $stats['pending_payroll'] }}</strong> payroll(s) pending processing</li>
        @endif
      </ul>
      <hr>
      <div class="d-flex gap-2">
        @if($stats['pending_leave_requests'] > 0)
          <a href="{{ route('modules.hr.leave') }}" class="btn btn-sm btn-warning">Review Leave</a>
        @endif
        @if($stats['pending_permissions'] > 0)
          <a href="{{ route('modules.hr.permissions') }}" class="btn btn-sm btn-info">Review Permissions</a>
        @endif
        @if($stats['pending_sick_sheets'] > 0)
          <a href="{{ route('modules.hr.sick-sheets') }}" class="btn btn-sm btn-danger">Review Sick Sheets</a>
        @endif
        @if($stats['pending_payroll'] > 0)
          <a href="{{ route('modules.hr.payroll') }}" class="btn btn-sm btn-success">Process Payroll</a>
        @endif
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
</div>
@endif

<!-- Recent Activities & Department Overview -->
<div class="row mb-4">
  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="bx bx-time me-2"></i>Recent HR Activities</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Type</th>
                <th>Employee</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @php
                $allActivities = collect($recentActivities['permission_requests'] ?? [])
                  ->merge($recentActivities['sick_sheets'] ?? [])
                  ->merge($recentActivities['assessments'] ?? [])
                  ->sortByDesc('created_at')
                  ->take(10);
              @endphp
              @forelse($allActivities as $activity)
              <tr>
                <td>
                  <span class="badge bg-{{ $activity instanceof \App\Models\PermissionRequest ? 'info' : ($activity instanceof \App\Models\SickSheet ? 'danger' : 'success') }}">
                    {{ $activity instanceof \App\Models\PermissionRequest ? 'Permission' : ($activity instanceof \App\Models\SickSheet ? 'Sick Sheet' : 'Assessment') }}
                  </span>
                </td>
                <td>
                  @if($activity instanceof \App\Models\PermissionRequest)
                    {{ $activity->user->name ?? 'Unknown' }}
                  @elseif($activity instanceof \App\Models\SickSheet)
                    {{ $activity->employee->name ?? 'Unknown' }}
                  @else
                    {{ $activity->employee->name ?? 'Unknown' }}
                  @endif
                </td>
                <td>
                  <span class="badge bg-{{ $activity->status === 'approved' ? 'success' : (str_contains($activity->status ?? '', 'pending') ? 'warning' : 'info') }}">
                    {{ ucfirst(str_replace('_', ' ', $activity->status ?? 'unknown')) }}
                  </span>
                </td>
                <td>{{ $activity->created_at ? $activity->created_at->format('M d, Y') : 'N/A' }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No recent HR activities</td>
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
        <h5 class="card-title mb-0"><i class="bx bx-building me-2"></i>Department Overview</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Department</th>
                <th>Employees</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($employeeStats ?? [] as $stat)
              <tr>
                <td><strong>{{ $stat->primaryDepartment->name ?? 'Unknown' }}</strong></td>
                <td><span class="badge bg-primary">{{ $stat->count ?? 0 }}</span></td>
                <td><a href="{{ route('modules.hr.employees') }}?department={{ $stat->primary_department_id }}" class="btn btn-sm btn-outline-primary">View</a></td>
              </tr>
              @empty
              <tr>
                <td colspan="3" class="text-center text-muted">No department data available</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Payroll Summary -->
@if($stats['total_payroll_amount'] > 0)
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0"><i class="bx bx-credit-card me-2"></i>Payroll Summary</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-4 text-center mb-3">
            <h4 class="text-primary">{{ number_format($stats['total_payroll_amount'] ?? 0, 2) }}</h4>
            <p class="text-muted mb-0">Total Payroll Amount</p>
          </div>
          <div class="col-md-4 text-center mb-3">
            <h4 class="text-success">{{ number_format($stats['current_month_payroll'] ?? 0, 2) }}</h4>
            <p class="text-muted mb-0">Current Month Payroll</p>
          </div>
          <div class="col-md-4 text-center mb-3">
            <h4 class="text-warning">{{ $stats['pending_payroll'] ?? 0 }}</h4>
            <p class="text-muted mb-0">Pending Payrolls</p>
          </div>
        </div>
        <div class="text-center mt-3">
          <a href="{{ route('modules.hr.payroll') }}" class="btn btn-success">
            <i class="bx bx-credit-card me-2"></i>View Payroll Management
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

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
    // Leave Trends Chart
    const leaveTrendsCtx = document.getElementById('leaveTrendsChart');
    if (leaveTrendsCtx) {
        const leaveTrends = @json($leaveTrends ?? []);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const data = Array(12).fill(0);
        
        leaveTrends.forEach(item => {
            if (item.month) data[item.month - 1] = parseInt(item.count || 0);
        });
        
        new Chart(leaveTrendsCtx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Leave Requests',
                    data: data,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Department Distribution Chart
    const departmentCtx = document.getElementById('departmentChart');
    if (departmentCtx) {
        const deptData = @json($employeeStats ?? []);
        const labels = deptData.map(d => d.primaryDepartment?.name || 'Unknown').slice(0, 6);
        const counts = deptData.map(d => d.count || 0).slice(0, 6);
        
        new Chart(departmentCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
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
