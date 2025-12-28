@extends('layouts.app')

@section('title', 'CEO Dashboard - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-lg bg-gradient-primary" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-3 mb-md-0">
            <h3 class="mb-2 text-white fw-bold">
              <i class="bx bx-bar-chart-square me-2"></i>Executive Dashboard
            </h3>
            <p class="mb-0 text-white-50 fs-6">
              Welcome back, {{ auth()->user()->name }}! Comprehensive executive overview and strategic insights
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('modules.finance.petty') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-money me-2"></i>Financial Review
            </a>
            <a href="{{ route('modules.hr.employees') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-group me-2"></i>Employees
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
            <a href="{{ route('modules.finance.petty') }}" class="btn btn-warning w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-money fs-3 mb-2"></i>
              <span>Petty Cash</span>
              @if($stats['pending_petty_cash_count'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_petty_cash_count'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('imprest.index') }}" class="btn btn-success w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-wallet fs-3 mb-2"></i>
              <span>Imprest</span>
              @if($stats['pending_imprest_count'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_imprest_count'] }}</span>
              @endif
            </a>
          </div>
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
            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-info w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-credit-card fs-3 mb-2"></i>
              <span>Payroll</span>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.employees') }}" class="btn btn-danger w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-group fs-3 mb-2"></i>
              <span>Employees</span>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.files.digital') }}" class="btn btn-dark w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-folder fs-3 mb-2"></i>
              <span>Files</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Executive KPI Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Total Employees</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['total_employees'] ?? 0 }}</h2>
            <small class="text-white-50">{{ $stats['departments_count'] ?? 0 }} departments</small>
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
            <h2 class="mb-0 fw-bold">{{ $stats['pending_petty_cash_count'] + $stats['pending_imprest_count'] + $stats['pending_leave_requests'] }}</h2>
            <small class="text-white-50">Requires your attention</small>
          </div>
          <i class="bx bx-time fs-1 opacity-50"></i>
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
            <h6 class="text-white-50 mb-2">Pending Financial</h6>
            <h2 class="mb-0 fw-bold">{{ number_format(($stats['pending_petty_cash'] ?? 0) + ($stats['pending_imprest'] ?? 0), 0) }}</h2>
            <small class="text-white-50">Petty Cash + Imprest</small>
          </div>
          <i class="bx bx-dollar fs-1 opacity-50"></i>
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
        <h5 class="card-title mb-0"><i class="bx bx-line-chart me-2"></i>Financial Trends - {{ now()->year }}</h5>
      </div>
      <div class="card-body">
        <canvas id="financialTrendsChart" height="100"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-4 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0">
        <h5 class="card-title mb-0"><i class="bx bx-pie-chart me-2"></i>Department Performance</h5>
      </div>
      <div class="card-body">
        <canvas id="departmentChart" height="250"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Pending Approvals Alert -->
@if($stats['pending_petty_cash_count'] > 0 || $stats['pending_imprest_count'] > 0 || $stats['pending_leave_requests'] > 0)
<div class="row mb-4">
  <div class="col-12">
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
      <h5 class="alert-heading"><i class="bx bx-bell me-2"></i>Pending Approvals Requiring Your Attention!</h5>
      <p class="mb-2">You have the following items pending your approval:</p>
      <ul class="mb-0">
        @if($stats['pending_petty_cash_count'] > 0)
          <li><strong>{{ $stats['pending_petty_cash_count'] }}</strong> petty cash voucher(s) - TZS {{ number_format($stats['pending_petty_cash'] ?? 0, 2) }}</li>
        @endif
        @if($stats['pending_imprest_count'] > 0)
          <li><strong>{{ $stats['pending_imprest_count'] }}</strong> imprest request(s) - TZS {{ number_format($stats['pending_imprest'] ?? 0, 2) }}</li>
        @endif
        @if($stats['pending_leave_requests'] > 0)
          <li><strong>{{ $stats['pending_leave_requests'] }}</strong> leave request(s)</li>
        @endif
      </ul>
      <hr>
      <div class="d-flex gap-2">
        @if($stats['pending_petty_cash_count'] > 0)
          <a href="{{ route('modules.finance.petty') }}" class="btn btn-sm btn-warning">Review Petty Cash</a>
        @endif
        @if($stats['pending_imprest_count'] > 0)
          <a href="{{ route('imprest.index') }}" class="btn btn-sm btn-success">Review Imprest</a>
        @endif
        @if($stats['pending_leave_requests'] > 0)
          <a href="{{ route('modules.hr.leave') }}" class="btn btn-sm btn-primary">Review Leave</a>
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
      <div class="card-header bg-warning text-white">
        <h5 class="card-title mb-0"><i class="bx bx-money me-2"></i>Pending Petty Cash Approvals</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Voucher No</th>
                <th>Requester</th>
                <th>Amount</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pendingApprovals['petty_cash'] ?? [] as $voucher)
              <tr>
                <td><span class="badge bg-primary">{{ $voucher->voucher_no ?? 'N/A' }}</span></td>
                <td>{{ $voucher->user->name ?? 'Unknown' }}</td>
                <td><strong>TZS {{ number_format($voucher->amount ?? 0, 2) }}</strong></td>
                <td><a href="{{ route('modules.finance.petty') }}?voucher={{ $voucher->id }}" class="btn btn-sm btn-outline-warning">Review</a></td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No pending petty cash approvals</td>
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
      <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0"><i class="bx bx-wallet me-2"></i>Pending Imprest Approvals</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Request No</th>
                <th>Purpose</th>
                <th>Amount</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($pendingApprovals['imprest_requests'] ?? [] as $imprest)
              <tr>
                <td><span class="badge bg-success">{{ $imprest->request_no ?? 'N/A' }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($imprest->purpose ?? 'N/A', 30) }}</td>
                <td><strong>TZS {{ number_format($imprest->amount ?? 0, 2) }}</strong></td>
                <td><a href="{{ route('imprest.index') }}" class="btn btn-sm btn-outline-success">Review</a></td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No pending imprest approvals</td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Department Performance -->
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="bx bx-building me-2"></i>Department Performance Overview</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Department</th>
                <th>Employees</th>
                <th>Leave Requests</th>
                <th>Files</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($departmentPerformance ?? [] as $dept)
              <tr>
                <td><strong>{{ $dept->name ?? 'Unknown' }}</strong></td>
                <td><span class="badge bg-primary">{{ $dept->primary_users_count ?? 0 }}</span></td>
                <td><span class="badge bg-warning">{{ $dept->leave_requests_count ?? 0 }}</span></td>
                <td><span class="badge bg-info">{{ $dept->file_folders_count ?? 0 }}</span></td>
                <td><a href="{{ route('departments.show', $dept->id) }}" class="btn btn-sm btn-outline-primary">View</a></td>
              </tr>
              @empty
              <tr>
                <td colspan="5" class="text-center text-muted">No department data available</td>
              </tr>
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
    // Financial Trends Chart
    const financialTrendsCtx = document.getElementById('financialTrendsChart');
    if (financialTrendsCtx) {
        const monthlyTrends = @json($monthlyTrends ?? []);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        const payrollData = Array(12).fill(0);
        const pettyCashData = Array(12).fill(0);
        const imprestData = Array(12).fill(0);
        
        monthlyTrends.payroll?.forEach(item => {
            if (item.month) payrollData[item.month - 1] = parseFloat(item.total || 0) / 1000000;
        });
        monthlyTrends.petty_cash?.forEach(item => {
            if (item.month) pettyCashData[item.month - 1] = parseFloat(item.total || 0) / 1000000;
        });
        monthlyTrends.imprest?.forEach(item => {
            if (item.month) imprestData[item.month - 1] = parseFloat(item.total || 0) / 1000000;
        });
        
        new Chart(financialTrendsCtx, {
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
                    legend: { position: 'top' }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Department Performance Chart
    const departmentCtx = document.getElementById('departmentChart');
    if (departmentCtx) {
        const deptData = @json($departmentPerformance ?? []);
        const labels = deptData.map(d => d.name || 'Unknown').slice(0, 6);
        const employees = deptData.map(d => d.primary_users_count || 0).slice(0, 6);
        
        new Chart(departmentCtx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: employees,
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
