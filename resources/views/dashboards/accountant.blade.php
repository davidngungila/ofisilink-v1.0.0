@extends('layouts.app')

@section('title', 'Accountant Dashboard - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-lg bg-gradient-success" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
      <div class="card-body text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-3 mb-md-0">
            <h3 class="mb-2 text-white fw-bold">
              <i class="bx bx-calculator me-2"></i>Finance Dashboard
            </h3>
            <p class="mb-0 text-white-50 fs-6">
              Welcome back, {{ auth()->user()->name }}! Comprehensive financial overview and management
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('modules.finance.petty') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-money me-2"></i>Petty Cash
            </a>
            <a href="{{ route('imprest.index') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-wallet me-2"></i>Imprest
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
            <a href="{{ route('modules.finance.petty') }}" class="btn btn-primary w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-money fs-3 mb-2"></i>
              <span>Petty Cash</span>
              @if($pendingActions['petty_cash_pending_accountant'] > 0)
                <span class="badge bg-danger mt-1">{{ $pendingActions['petty_cash_pending_accountant'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('imprest.index') }}" class="btn btn-success w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-wallet fs-3 mb-2"></i>
              <span>Imprest</span>
              @if($pendingActions['imprest_pending_assignment'] > 0 || $pendingActions['imprest_pending_payment'] > 0 || $pendingActions['imprest_pending_verification'] > 0)
                <span class="badge bg-danger mt-1">{{ $pendingActions['imprest_pending_assignment'] + $pendingActions['imprest_pending_payment'] + $pendingActions['imprest_pending_verification'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-info w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-credit-card fs-3 mb-2"></i>
              <span>Payroll</span>
              @if($pendingActions['payroll_pending'] > 0)
                <span class="badge bg-danger mt-1">{{ $pendingActions['payroll_pending'] }}</span>
              @endif
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.finance.ledger') }}" class="btn btn-warning w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-book fs-3 mb-2"></i>
              <span>General Ledger</span>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('modules.accounting.index') }}" class="btn btn-danger w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-bar-chart fs-3 mb-2"></i>
              <span>Accounting</span>
            </a>
          </div>
          <div class="col-lg-2 col-md-4 col-sm-6">
            <a href="{{ route('finance.settings.index') }}" class="btn btn-dark w-100 d-flex flex-column align-items-center py-3 shadow-sm">
              <i class="bx bx-cog fs-3 mb-2"></i>
              <span>Settings</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Financial KPI Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-primary">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Total Petty Cash</h6>
            <h3 class="mb-0 text-primary fw-bold">{{ number_format($stats['total_petty_cash_amount'] ?? 0, 0) }}</h3>
            <small class="text-muted">{{ $stats['total_vouchers'] ?? 0 }} vouchers</small>
          </div>
          <div class="avatar bg-primary bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-money fs-2 text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-warning">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Pending My Action</h6>
            <h3 class="mb-0 text-warning fw-bold">{{ number_format($stats['pending_accountant_amount'] ?? 0, 0) }}</h3>
            <small class="text-muted">{{ $stats['pending_accountant_count'] ?? 0 }} requests</small>
          </div>
          <div class="avatar bg-warning bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-time fs-2 text-warning"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-success">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Total Imprest</h6>
            <h3 class="mb-0 text-success fw-bold">{{ number_format($imprestStats['total_imprest_amount'] ?? 0, 0) }}</h3>
            <small class="text-muted">{{ $imprestStats['pending_verification'] ?? 0 }} pending verification</small>
          </div>
          <div class="avatar bg-success bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-wallet fs-2 text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-info">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Total Payroll</h6>
            <h3 class="mb-0 text-info fw-bold">{{ number_format($payrollStats['current_month_total'] ?? 0, 0) }}</h3>
            <small class="text-muted">{{ $payrollStats['pending_payrolls'] ?? 0 }} pending</small>
          </div>
          <div class="avatar bg-info bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-credit-card fs-2 text-info"></i>
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
        <h5 class="card-title mb-0"><i class="bx bx-pie-chart me-2"></i>Petty Cash Status</h5>
      </div>
      <div class="card-body">
        <canvas id="pettyCashChart" height="250"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Petty Cash & Imprest Overview -->
<div class="row mb-4">
  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="bx bx-money me-2"></i>Petty Cash Overview</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6 mb-3">
            <div class="text-center p-3 border rounded">
              <h4 class="text-primary mb-1">{{ number_format($stats['total_vouchers'] ?? 0) }}</h4>
              <small class="text-muted">Total Vouchers</small>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="text-center p-3 border rounded">
              <h4 class="text-warning mb-1">{{ $stats['pending_accountant_count'] ?? 0 }}</h4>
              <small class="text-muted">Pending Review</small>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="text-center p-3 border rounded">
              <h4 class="text-success mb-1">{{ $stats['pending_payment_vouchers'] ?? 0 }}</h4>
              <small class="text-muted">Ready for Payment</small>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="text-center p-3 border rounded">
              <h4 class="text-info mb-1">{{ $stats['paid_vouchers'] ?? 0 }}</h4>
              <small class="text-muted">Paid Vouchers</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-success text-white">
        <h5 class="card-title mb-0"><i class="bx bx-wallet me-2"></i>Imprest Overview</h5>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-6 mb-3">
            <div class="text-center p-3 border rounded">
              <h4 class="text-success mb-1">{{ $imprestStats['pending_hod'] + $imprestStats['pending_ceo'] + $imprestStats['approved'] + $imprestStats['assigned'] + $imprestStats['paid'] + $imprestStats['pending_verification'] + $imprestStats['completed'] }}</h4>
              <small class="text-muted">Total Requests</small>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="text-center p-3 border rounded">
              <h4 class="text-warning mb-1">{{ $imprestStats['pending_hod'] + $imprestStats['pending_ceo'] }}</h4>
              <small class="text-muted">Pending Approval</small>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="text-center p-3 border rounded">
              <h4 class="text-primary mb-1">{{ $imprestStats['approved'] + $imprestStats['assigned'] }}</h4>
              <small class="text-muted">Ready for Action</small>
            </div>
          </div>
          <div class="col-6 mb-3">
            <div class="text-center p-3 border rounded">
              <h4 class="text-info mb-1">{{ $imprestStats['pending_verification'] }}</h4>
              <small class="text-muted">Pending Verification</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Activities -->
<div class="row mb-4">
  <div class="col-lg-6 mb-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0"><i class="bx bx-time me-2"></i>Recent Petty Cash Activities</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Voucher No</th>
                <th>Requester</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentActivities['petty_cash_vouchers'] ?? [] as $voucher)
              <tr>
                <td><span class="badge bg-primary">{{ $voucher->voucher_no ?? 'N/A' }}</span></td>
                <td>{{ $voucher->creator->name ?? 'Unknown' }}</td>
                <td><strong>TZS {{ number_format($voucher->amount ?? 0, 2) }}</strong></td>
                <td><span class="badge bg-{{ $voucher->status === 'paid' ? 'success' : ($voucher->status === 'approved_for_payment' ? 'primary' : ($voucher->status === 'pending_accountant' ? 'warning' : 'info')) }}">{{ ucfirst(str_replace('_', ' ', $voucher->status ?? 'unknown')) }}</span></td>
              </tr>
              @empty
              <tr><td colspan="4" class="text-center text-muted">No recent petty cash activities</td></tr>
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
        <h5 class="card-title mb-0"><i class="bx bx-wallet me-2"></i>Recent Imprest Requests</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Request No</th>
                <th>Purpose</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentActivities['imprest_requests'] ?? [] as $imprest)
              <tr>
                <td><span class="badge bg-success">{{ $imprest->request_no ?? 'N/A' }}</span></td>
                <td>{{ \Illuminate\Support\Str::limit($imprest->purpose ?? 'N/A', 30) }}</td>
                <td><strong>TZS {{ number_format($imprest->amount ?? 0, 2) }}</strong></td>
                <td><span class="badge bg-{{ $imprest->status === 'completed' ? 'success' : ($imprest->status === 'paid' ? 'primary' : ($imprest->status === 'pending_ceo' ? 'warning' : 'info')) }}">{{ ucfirst(str_replace('_', ' ', $imprest->status ?? 'unknown')) }}</span></td>
              </tr>
              @empty
              <tr><td colspan="4" class="text-center text-muted">No recent imprest requests</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Pending Actions Alert -->
@if($pendingActions['petty_cash_pending_accountant'] > 0 || $pendingActions['imprest_pending_assignment'] > 0 || $pendingActions['imprest_pending_payment'] > 0 || $pendingActions['imprest_pending_verification'] > 0 || $pendingActions['payroll_pending'] > 0)
<div class="row">
  <div class="col-12">
    <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">
      <h5 class="alert-heading"><i class="bx bx-bell me-2"></i>Action Required!</h5>
      <p class="mb-2">You have pending items that require your attention:</p>
      <ul class="mb-0">
        @if($pendingActions['petty_cash_pending_accountant'] > 0)
          <li><strong>{{ $pendingActions['petty_cash_pending_accountant'] }}</strong> petty cash voucher(s) pending your review</li>
        @endif
        @if($pendingActions['imprest_pending_assignment'] > 0)
          <li><strong>{{ $pendingActions['imprest_pending_assignment'] }}</strong> imprest request(s) need staff assignment</li>
        @endif
        @if($pendingActions['imprest_pending_payment'] > 0)
          <li><strong>{{ $pendingActions['imprest_pending_payment'] }}</strong> imprest request(s) ready for payment</li>
        @endif
        @if($pendingActions['imprest_pending_verification'] > 0)
          <li><strong>{{ $pendingActions['imprest_pending_verification'] }}</strong> imprest request(s) need receipt verification</li>
        @endif
        @if($pendingActions['payroll_pending'] > 0)
          <li><strong>{{ $pendingActions['payroll_pending'] }}</strong> payroll(s) pending processing</li>
        @endif
      </ul>
      <hr>
      <div class="d-flex gap-2">
        @if($pendingActions['petty_cash_pending_accountant'] > 0)
          <a href="{{ route('modules.finance.petty') }}" class="btn btn-sm btn-warning">Review Petty Cash</a>
        @endif
        @if($pendingActions['imprest_pending_assignment'] > 0 || $pendingActions['imprest_pending_payment'] > 0 || $pendingActions['imprest_pending_verification'] > 0)
          <a href="{{ route('imprest.index') }}" class="btn btn-sm btn-success">Manage Imprest</a>
        @endif
        @if($pendingActions['payroll_pending'] > 0)
          <a href="{{ route('modules.hr.payroll') }}" class="btn btn-sm btn-info">Process Payroll</a>
        @endif
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
</div>
@endif

@endsection

@push('styles')
<style>
  .bg-gradient-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important; }
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
        
        const pettyCashData = Array(12).fill(0);
        const imprestData = Array(12).fill(0);
        
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
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    // Petty Cash Status Chart
    const pettyCashCtx = document.getElementById('pettyCashChart');
    if (pettyCashCtx) {
        new Chart(pettyCashCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending Review', 'Ready for Payment', 'Paid'],
                datasets: [{
                    data: [
                        {{ $stats['pending_accountant_count'] ?? 0 }},
                        {{ $stats['pending_payment_vouchers'] ?? 0 }},
                        {{ $stats['paid_vouchers'] ?? 0 }}
                    ],
                    backgroundColor: [
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
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
