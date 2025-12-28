@extends('layouts.app')

@section('title', 'Staff Dashboard - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-lg bg-gradient-primary" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-3 mb-md-0">
            <h3 class="mb-2 text-white fw-bold">
              <i class="bx bx-user me-2"></i>My Dashboard
            </h3>
            <p class="mb-0 text-white-50 fs-6">
              Welcome back, {{ auth()->user()->name }}! Your personal overview and quick access
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('modules.hr.leave') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-calendar me-2"></i>Request Leave
            </a>
            <a href="{{ route('modules.files.digital') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-folder me-2"></i>My Files
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
              <span>Request Leave</span>
              @if($stats['pending_leave_requests'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_leave_requests'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('petty-cash.index') }}" class="btn btn-success w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-money fs-3 mb-2"></i>
              <span>Petty Cash</span>
              @if($stats['pending_petty_cash_requests'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_petty_cash_requests'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('imprest.index') }}" class="btn btn-info w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-wallet fs-3 mb-2"></i>
              <span>Imprest</span>
              @if($stats['pending_imprest_receipts'] > 0)
                <span class="badge bg-danger mt-1">{{ $stats['pending_imprest_receipts'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.files.digital') }}" class="btn btn-warning w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-folder fs-3 mb-2"></i>
              <span>Files</span>
              <small class="text-white-50">{{ $stats['my_files'] }} assigned</small>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.permissions') }}" class="btn btn-danger w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-time-five fs-3 mb-2"></i>
              <span>Permission</span>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-dark w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-credit-card fs-3 mb-2"></i>
              <span>Payroll</span>
              <small class="text-white-50">{{ $stats['my_payroll_items'] }} records</small>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Personal KPI Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">My Files</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['my_files'] ?? 0 }}</h2>
            <small class="text-white-50">Assigned files</small>
          </div>
          <i class="bx bx-folder fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-warning text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Leave Requests</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['my_leave_requests'] ?? 0 }}</h2>
            <small class="text-white-50">{{ $stats['pending_leave_requests'] ?? 0 }} pending</small>
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
            <h6 class="text-white-50 mb-2">Imprest Assignments</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['my_imprest_assignments'] ?? 0 }}</h2>
            <small class="text-white-50">{{ $stats['pending_imprest_receipts'] ?? 0 }} need receipts</small>
          </div>
          <i class="bx bx-wallet fs-1 opacity-50"></i>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm bg-gradient-info text-white" style="background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-white-50 mb-2">Payroll Records</h6>
            <h2 class="mb-0 fw-bold">{{ $stats['my_payroll_items'] ?? 0 }}</h2>
            <small class="text-white-50">Total payslips</small>
          </div>
          <i class="bx bx-credit-card fs-1 opacity-50"></i>
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
        <h5 class="card-title mb-0"><i class="bx bx-bar-chart me-2"></i>My Activity Overview</h5>
      </div>
      <div class="card-body">
        <canvas id="activityChart" height="200"></canvas>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-0">
        <h5 class="card-title mb-0"><i class="bx bx-pie-chart me-2"></i>Request Status</h5>
      </div>
      <div class="card-body">
        <canvas id="statusChart" height="200"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Pending Actions Alert -->
@if($stats['pending_imprest_receipts'] > 0 || $stats['pending_leave_requests'] > 0 || $stats['pending_petty_cash_requests'] > 0)
<div class="row mb-4">
  <div class="col-12">
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
      <h5 class="alert-heading"><i class="bx bx-bell me-2"></i>Action Required!</h5>
      <p class="mb-2">You have pending items that require your attention:</p>
      <ul class="mb-0">
        @if($stats['pending_imprest_receipts'] > 0)
          <li><strong>{{ $stats['pending_imprest_receipts'] }}</strong> imprest receipt(s) need to be submitted</li>
        @endif
        @if($stats['pending_leave_requests'] > 0)
          <li><strong>{{ $stats['pending_leave_requests'] }}</strong> leave request(s) pending approval</li>
        @endif
        @if($stats['pending_petty_cash_requests'] > 0)
          <li><strong>{{ $stats['pending_petty_cash_requests'] }}</strong> petty cash request(s) pending</li>
        @endif
      </ul>
      <hr>
      <div class="d-flex gap-2">
        @if($stats['pending_imprest_receipts'] > 0)
          <a href="{{ route('imprest.index') }}" class="btn btn-sm btn-warning">Submit Receipts</a>
        @endif
        @if($stats['pending_leave_requests'] > 0)
          <a href="{{ route('modules.hr.leave') }}" class="btn btn-sm btn-primary">View Leave Requests</a>
        @endif
        @if($stats['pending_petty_cash_requests'] > 0)
          <a href="{{ route('petty-cash.index') }}" class="btn btn-sm btn-success">View Petty Cash</a>
        @endif
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
</div>
@endif

<!-- Recent Activities -->
<div class="row mb-4">
  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="bx bx-wallet me-2"></i>My Imprest Assignments</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Request No</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              @forelse($myActivities['imprest_assignments'] ?? [] as $assignment)
              <tr>
                <td><span class="badge bg-success">{{ $assignment->imprestRequest->request_no ?? 'N/A' }}</span></td>
                <td><strong>TZS {{ number_format($assignment->assigned_amount ?? 0, 2) }}</strong></td>
                <td>
                  <span class="badge bg-{{ $assignment->imprestRequest->status === 'paid' ? 'success' : ($assignment->imprestRequest->status === 'completed' ? 'info' : 'warning') }}">
                    {{ ucwords(str_replace('_', ' ', $assignment->imprestRequest->status ?? 'unknown')) }}
                  </span>
                </td>
                <td>
                  @if($assignment->imprestRequest->status === 'paid' && !$assignment->receipt_submitted)
                    <a href="{{ route('imprest.index') }}" class="btn btn-sm btn-warning">Submit Receipt</a>
                  @else
                    <a href="{{ route('imprest.index') }}" class="btn btn-sm btn-info">View</a>
                  @endif
                </td>
              </tr>
              @empty
              <tr><td colspan="4" class="text-center text-muted">No imprest assignments</td></tr>
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
        <h5 class="card-title mb-0"><i class="bx bx-calendar me-2"></i>Recent Leave Requests</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Type</th>
                <th>Days</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              @forelse($myActivities['leave_requests'] ?? [] as $request)
              <tr>
                <td>{{ $request->leave_type ?? 'Annual' }}</td>
                <td>{{ $request->days_requested ?? 1 }}</td>
                <td>
                  <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'pending' ? 'warning' : 'danger') }}">
                    {{ ucfirst($request->status ?? 'unknown') }}
                  </span>
                </td>
                <td>{{ $request->created_at ? $request->created_at->format('M d, Y') : 'N/A' }}</td>
              </tr>
              @empty
              <tr><td colspan="4" class="text-center text-muted">No leave requests</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Additional Activities -->
<div class="row">
  <div class="col-lg-4 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0"><i class="bx bx-money me-2"></i>Petty Cash Requests</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          @forelse($myActivities['petty_cash_requests'] ?? [] as $request)
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>TZS {{ number_format($request->amount ?? 0, 2) }}</strong>
                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($request->purpose ?? 'N/A', 30) }}</small>
              </div>
              <span class="badge bg-{{ $request->status === 'paid' ? 'success' : ($request->status === 'pending' ? 'warning' : 'info') }}">
                {{ ucfirst(str_replace('_', ' ', $request->status ?? 'unknown')) }}
              </span>
            </div>
          </div>
          @empty
          <div class="list-group-item text-center text-muted">No petty cash requests</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0"><i class="bx bx-file me-2"></i>File Access Requests</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          @forelse($myActivities['file_access_requests'] ?? [] as $request)
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>{{ \Illuminate\Support\Str::limit($request->file->name ?? 'Unknown', 25) }}</strong>
                <br><small class="text-muted">{{ $request->created_at ? $request->created_at->format('M d, Y') : 'N/A' }}</small>
              </div>
              <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'pending' ? 'warning' : 'danger') }}">
                {{ ucfirst($request->status ?? 'unknown') }}
              </span>
            </div>
          </div>
          @empty
          <div class="list-group-item text-center text-muted">No file access requests</div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-danger text-white">
        <h5 class="card-title mb-0"><i class="bx bx-time-five me-2"></i>Permission Requests</h5>
      </div>
      <div class="card-body">
        <div class="list-group list-group-flush">
          @forelse($myActivities['permission_requests'] ?? [] as $request)
          <div class="list-group-item">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <strong>{{ $request->created_at ? $request->created_at->format('M d, Y') : 'N/A' }}</strong>
                <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($request->reason ?? 'N/A', 25) }}</small>
              </div>
              <span class="badge bg-{{ $request->status === 'approved' ? 'success' : ($request->status === 'pending' ? 'warning' : 'danger') }}">
                {{ ucfirst(str_replace('_', ' ', $request->status ?? 'unknown')) }}
              </span>
            </div>
          </div>
          @empty
          <div class="list-group-item text-center text-muted">No permission requests</div>
          @endforelse
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
    // Activity Chart
    const activityCtx = document.getElementById('activityChart');
    if (activityCtx) {
        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: ['Files', 'Leave Requests', 'Imprest', 'Payroll'],
                datasets: [{
                    label: 'My Activities',
                    data: [
                        {{ $stats['my_files'] ?? 0 }},
                        {{ $stats['my_leave_requests'] ?? 0 }},
                        {{ $stats['my_imprest_assignments'] ?? 0 }},
                        {{ $stats['my_payroll_items'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
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

    // Status Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const pending = {{ ($stats['pending_leave_requests'] ?? 0) + ($stats['pending_petty_cash_requests'] ?? 0) + ($stats['pending_imprest_receipts'] ?? 0) }};
        const approved = {{ ($stats['approved_leave_requests'] ?? 0) }};
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Approved'],
                datasets: [{
                    data: [pending, approved],
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
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
