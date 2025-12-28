@extends('layouts.app')

@section('title', 'Accountant Petty Cash Verification - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h4 class="fw-bold py-3 mb-2">
              <i class="bx bx-money me-2"></i>Petty Cash Management
            </h4>
            <p class="text-muted">Enterprise-grade petty cash verification and management system</p>
          </div>
          <div class="btn-group" role="group">
            <a href="{{ route('petty-cash.index') }}" class="btn btn-outline-primary">
              <i class="bx bx-arrow-back"></i> Dashboard
            </a>
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#searchFilters">
              <i class="bx bx-search"></i> Search
            </button>
            @if(($type ?? 'regular') === 'direct')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#directVoucherModal">
              <i class="bx bx-plus"></i> New Direct Voucher
            </button>
            @endif
            <button type="button" class="btn btn-outline-info" onclick="exportTable()">
              <i class="bx bx-export"></i> Export
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .stat-card { 
        transition: all 0.3s ease; 
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .stat-card:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 8px 25px rgba(0,0,0,0.15); 
        border-color: #007bff;
    }
    .dashboard-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .voucher-card {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s;
        border-left: 4px solid #007bff;
    }
    .voucher-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .voucher-card.direct {
        border-left-color: #ffc107;
    }
    .nav-pills .nav-link {
        border-radius: 8px;
        margin-right: 5px;
        transition: all 0.3s;
    }
    .nav-pills .nav-link:hover {
        background-color: #f8f9fa;
    }
    .nav-pills .nav-link.active {
        background-color: #007bff;
        color: white;
    }
    .search-filters {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .avatar-sm {
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }
    .avatar-title {
        font-weight: 600;
        font-size: 1rem;
    }
    .badge-modern {
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 500;
    }
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 15px;
        }
    }
    /* Ensure SweetAlert is above Bootstrap modals */
    .swal2-container { z-index: 200000 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Dashboard Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center p-3">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Pending Action</h6>
                        <h3 class="mb-0 text-danger">{{ $counts['pending_accountant'] ?? 0 }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-money"></i> TZS {{ number_format($stats['pending_accountant_amount'] ?? 0, 0) }}
                        </small>
                    </div>
                    <div class="avatar-sm bg-danger bg-opacity-10">
                        <span class="avatar-title text-danger">
                            <i class="bx bx-time" style="font-size: 1.5rem;"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center p-3">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Ready to Pay</h6>
                        <h3 class="mb-0 text-success">{{ $counts['approved'] ?? 0 }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-money"></i> TZS {{ number_format($stats['approved_amount'] ?? 0, 0) }}
                        </small>
                    </div>
                    <div class="avatar-sm bg-success bg-opacity-10">
                        <span class="avatar-title text-success">
                            <i class="bx bx-check-circle" style="font-size: 1.5rem;"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center p-3">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">This Month</h6>
                        <h3 class="mb-0 text-primary">{{ $stats['current_month_count'] ?? 0 }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-money"></i> TZS {{ number_format($stats['current_month_total'] ?? 0, 0) }}
                        </small>
                    </div>
                    <div class="avatar-sm bg-primary bg-opacity-10">
                        <span class="avatar-title text-primary">
                            <i class="bx bx-calendar" style="font-size: 1.5rem;"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center p-3">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Vouchers</h6>
                        <h3 class="mb-0 text-info">{{ number_format(($counts['pending_accountant'] ?? 0) + ($counts['pending_hod'] ?? 0) + ($counts['pending_ceo'] ?? 0) + ($counts['approved'] ?? 0) + ($counts['paid'] ?? 0) + ($counts['pending_retirement'] ?? 0) + ($counts['retired'] ?? 0) + ($counts['rejected'] ?? 0) + ($counts['direct_pending_hod'] ?? 0) + ($counts['direct_paid'] ?? 0) + ($counts['direct_rejected'] ?? 0), 0) }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-money"></i> TZS {{ number_format($stats['total_amount'] ?? 0, 0) }}
                        </small>
                    </div>
                    <div class="avatar-sm bg-info bg-opacity-10">
                        <span class="avatar-title text-info">
                            <i class="bx bx-money" style="font-size: 1.5rem;"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Main Content -->
        <div class="col-12">
            <!-- Search Filters -->
            <div class="collapse border-bottom bg-light mb-3" id="searchFilters">
                <div class="card-body">
                    <form method="GET" action="{{ route('petty-cash.accountant.index') }}" id="filterForm">
                        <input type="hidden" name="type" value="{{ $type ?? 'regular' }}">
                        <input type="hidden" name="status" value="{{ $status ?? 'pending_accountant' }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Voucher, payee, purpose...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Min Amount</label>
                                <input type="number" class="form-control" name="amount_min" value="{{ request('amount_min') }}" placeholder="Min" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-search"></i> Filter
                                </button>
                            </div>
                        </div>
                        @if(request()->hasAny(['search', 'date_from', 'date_to', 'amount_min', 'amount_max']))
                        <div class="mt-3">
                            <a href="{{ route('petty-cash.accountant.index', ['type' => $type ?? 'regular', 'status' => $status ?? 'pending_accountant']) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-x me-1"></i>Clear Filters
                            </a>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Main Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                        <div>
                            <h5 class="mb-1"><i class="bx bx-list-ul me-2 text-primary"></i>Voucher Management</h5>
                            <p class="text-muted mb-0 small">View and manage all petty cash vouchers</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if(($status ?? 'pending_accountant') === 'all')
                        <!-- Dashboard View with Buttons (status=all) -->
                        <div class="p-4">
                            <div class="row g-3 mb-4">
                                <div class="col-12">
                                    <h5 class="mb-3"><i class="bx bx-dashboard me-2"></i>Petty Cash Dashboard</h5>
                                    <p class="text-muted">Click on any section below to view and manage vouchers</p>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <!-- My Action -->
                                <div class="col-md-6 col-lg-4">
                                    <a href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'pending_accountant']) }}" 
                                       class="btn btn-outline-danger w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-decoration-none" 
                                       style="min-height: 150px; border-width: 2px;">
                                        <i class="bx bx-time fs-1 mb-2"></i>
                                        <h6 class="mb-1">My Action</h6>
                                        <span class="badge bg-danger fs-6 px-3 py-2">{{ $counts['pending_accountant'] ?? 0 }}</span>
                                        <small class="text-muted mt-2">Pending Verification</small>
                                    </a>
                                </div>
                                
                                <!-- Pending HOD -->
                                <div class="col-md-6 col-lg-4">
                                    <a href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'pending_hod']) }}" 
                                       class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-decoration-none" 
                                       style="min-height: 150px; border-width: 2px;">
                                        <i class="bx bx-user fs-1 mb-2"></i>
                                        <h6 class="mb-1">Pending HOD</h6>
                                        <span class="badge bg-info fs-6 px-3 py-2">{{ $counts['pending_hod'] ?? 0 }}</span>
                                        <small class="text-muted mt-2">Awaiting HOD Approval</small>
                                    </a>
                                </div>
                                
                                <!-- Pending CEO -->
                                <div class="col-md-6 col-lg-4">
                                    <a href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'pending_ceo']) }}" 
                                       class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-decoration-none" 
                                       style="min-height: 150px; border-width: 2px;">
                                        <i class="bx bx-crown fs-1 mb-2"></i>
                                        <h6 class="mb-1">Pending CEO</h6>
                                        <span class="badge bg-primary fs-6 px-3 py-2">{{ $counts['pending_ceo'] ?? 0 }}</span>
                                        <small class="text-muted mt-2">Awaiting CEO Approval</small>
                                    </a>
                                </div>
                                
                                <!-- Approved -->
                                <div class="col-md-6 col-lg-4">
                                    <a href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'approved']) }}" 
                                       class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-decoration-none" 
                                       style="min-height: 150px; border-width: 2px;">
                                        <i class="bx bx-check-circle fs-1 mb-2"></i>
                                        <h6 class="mb-1">Approved</h6>
                                        <span class="badge bg-success fs-6 px-3 py-2">{{ $counts['approved'] ?? 0 }}</span>
                                        <small class="text-muted mt-2">Ready for Payment</small>
                                    </a>
                                </div>
                                
                                <!-- Paid -->
                                <div class="col-md-6 col-lg-4">
                                    <a href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'paid']) }}" 
                                       class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-decoration-none" 
                                       style="min-height: 150px; border-width: 2px;">
                                        <i class="bx bx-money fs-1 mb-2"></i>
                                        <h6 class="mb-1">Paid</h6>
                                        <span class="badge bg-primary fs-6 px-3 py-2">{{ $counts['paid'] ?? 0 }}</span>
                                        <small class="text-muted mt-2">Payment Completed</small>
                                    </a>
                                </div>
                                
                                <!-- Retirement -->
                                <div class="col-md-6 col-lg-4">
                                    <a href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'pending_retirement']) }}" 
                                       class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-decoration-none" 
                                       style="min-height: 150px; border-width: 2px;">
                                        <i class="bx bx-receipt fs-1 mb-2"></i>
                                        <h6 class="mb-1">Retirement</h6>
                                        <span class="badge bg-warning fs-6 px-3 py-2">{{ $counts['pending_retirement'] ?? 0 }}</span>
                                        <small class="text-muted mt-2">Pending Review</small>
                                    </a>
                                </div>
                                
                                <!-- Direct Vouchers -->
                                <div class="col-md-6 col-lg-4">
                                    <a href="{{ route('petty-cash.direct-vouchers.index', ['status' => 'pending_hod']) }}" 
                                       class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center p-4 text-decoration-none" 
                                       style="min-height: 150px; border-width: 2px;">
                                        <i class="bx bx-file-blank fs-1 mb-2"></i>
                                        <h6 class="mb-1">Direct Vouchers</h6>
                                        @php
                                            $directTotal = ($counts['direct_pending_hod'] ?? 0) + ($counts['direct_paid'] ?? 0) + ($counts['direct_rejected'] ?? 0);
                                        @endphp
                                        <span class="badge bg-warning fs-6 px-3 py-2">{{ $directTotal }}</span>
                                        <small class="text-muted mt-2">In-Office Expenses</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Tabs View (when status is not 'all') -->
                        <ul class="nav nav-pills nav-fill border-bottom bg-light px-3 pt-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'pending_accountant' ? 'active' : '' }}" 
                                   href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'pending_accountant']) }}">
                                    <i class="bx bx-time me-1"></i>My Action
                                    @if($counts['pending_accountant'] > 0)
                                        <span class="badge bg-danger ms-1">{{ $counts['pending_accountant'] }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'pending_hod' ? 'active' : '' }}" 
                                   href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'pending_hod']) }}">
                                    <i class="bx bx-user me-1"></i>Pending HOD
                                    @if($counts['pending_hod'] > 0)
                                        <span class="badge bg-info ms-1">{{ $counts['pending_hod'] }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'pending_ceo' ? 'active' : '' }}" 
                                   href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'pending_ceo']) }}">
                                    <i class="bx bx-crown me-1"></i>Pending CEO
                                    @if($counts['pending_ceo'] > 0)
                                        <span class="badge bg-primary ms-1">{{ $counts['pending_ceo'] }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'approved' ? 'active' : '' }}" 
                                   href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'approved']) }}">
                                    <i class="bx bx-check-circle me-1"></i>Approved
                                    @if($counts['approved'] > 0)
                                        <span class="badge bg-success ms-1">{{ $counts['approved'] }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'paid' ? 'active' : '' }}" 
                                   href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'paid']) }}">
                                    <i class="bx bx-money me-1"></i>Paid
                                    @if($counts['paid'] > 0)
                                        <span class="badge bg-primary ms-1">{{ $counts['paid'] }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'pending_retirement' ? 'active' : '' }}" 
                                   href="{{ route('petty-cash.accountant.index', ['type' => 'regular', 'status' => 'pending_retirement']) }}">
                                    <i class="bx bx-receipt me-1"></i>Retirement
                                    @if($counts['pending_retirement'] > 0)
                                        <span class="badge bg-warning ms-1">{{ $counts['pending_retirement'] }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('petty-cash.direct-vouchers.index') ? 'active' : '' }}" 
                                   href="{{ route('petty-cash.direct-vouchers.index', ['status' => 'pending_hod']) }}">
                                    <i class="bx bx-file-blank me-1"></i>Direct
                                    @php
                                        $directTotal = ($counts['direct_pending_hod'] ?? 0) + ($counts['direct_paid'] ?? 0) + ($counts['direct_rejected'] ?? 0);
                                    @endphp
                                    @if($directTotal > 0)
                                        <span class="badge bg-warning ms-1">{{ $directTotal }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>
                    @endif

                    <!-- Page Header for Current Tab -->
                    @if(($type ?? 'regular') !== 'direct' && ($status ?? 'pending_accountant') !== 'all')
                    <div class="card-header bg-white border-bottom mb-3 mx-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h5 class="mb-1">
                                    @if($status === 'pending_accountant')
                                        <i class="bx bx-time text-danger me-2"></i>My Action - Pending Verification
                                    @elseif($status === 'pending_hod')
                                        <i class="bx bx-user text-info me-2"></i>Pending HOD Approval
                                    @elseif($status === 'pending_ceo')
                                        <i class="bx bx-crown text-primary me-2"></i>Pending CEO Approval
                                    @elseif($status === 'approved')
                                        <i class="bx bx-check-circle text-success me-2"></i>Approved for Payment
                                    @elseif($status === 'paid')
                                        <i class="bx bx-money text-primary me-2"></i>Paid Vouchers
                                    @elseif($status === 'pending_retirement')
                                        <i class="bx bx-receipt text-warning me-2"></i>Pending Retirement Review
                                    @endif
                                </h5>
                                <p class="text-muted mb-0 small">
                                    @if($status === 'pending_accountant')
                                        Regular vouchers requiring your verification. After verification, they will be forwarded to HOD.
                                    @elseif($status === 'pending_hod')
                                        Vouchers you've verified, now awaiting HOD approval. After HOD approval, they will go to CEO.
                                    @elseif($status === 'pending_ceo')
                                        HOD-approved vouchers awaiting CEO's final approval before payment.
                                    @elseif($status === 'approved')
                                        CEO-approved vouchers ready for you to process payment.
                                    @elseif($status === 'paid')
                                        Vouchers that have been paid and are awaiting retirement submission.
                                    @elseif($status === 'pending_retirement')
                                        Retirements submitted by staff, awaiting your review and approval.
                                    @endif
                                </p>
                                </div>
                            <div class="mt-2 mt-md-0">
                                <span class="badge bg-{{ $status === 'pending_accountant' ? 'danger' : ($status === 'pending_hod' ? 'info' : ($status === 'pending_ceo' ? 'primary' : ($status === 'approved' ? 'success' : ($status === 'paid' ? 'primary' : 'warning')))) }} fs-6 px-3 py-2">
                                    {{ $vouchers->total() }} {{ Str::plural('Voucher', $vouchers->total()) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Summary Bar -->
                    @if(($status ?? 'pending_accountant') !== 'all' && isset($vouchers) && $vouchers->count() > 0)
                    <div class="alert alert-light border-0 rounded-0 mb-0 mx-3 mt-3 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <div>
                            <strong>Showing {{ $vouchers->firstItem() ?? 0 }}-{{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() }}</strong>
                            @if(request()->hasAny(['search', 'date_from', 'date_to', 'amount_min', 'amount_max']))
                                <span class="badge bg-info ms-2">Filtered</span>
                            @endif
                        </div>
                        <div class="mt-2 mt-md-0">
                            <strong class="text-success">Total: TZS {{ number_format($vouchers->sum('amount'), 2) }}</strong>
                        </div>
                    </div>
                    @endif

                    <!-- Vouchers Table -->
                    @if(($status ?? 'pending_accountant') !== 'all' && isset($vouchers) && $vouchers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0" id="vouchersTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Voucher No</th>
                                    <th>Employee</th>
                                    <th class="d-none d-md-table-cell">Department</th>
                                    <th style="width: 130px;">Amount</th>
                                    <th class="d-none d-lg-table-cell">Purpose</th>
                                    <th style="width: 120px;">Status</th>
                                    @if($status === 'pending_retirement' || $status === 'retired')
                                        <th class="d-none d-md-table-cell" style="width: 100px;">Receipts</th>
                                    @endif
                                    <th class="d-none d-md-table-cell" style="width: 130px;">Date</th>
                                    <th style="width: 180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vouchers as $voucher)
                                @php
                                    $isDirect = $voucher->created_by === $voucher->accountant_id && $voucher->accountant_id !== null;
                                @endphp
                                <tr class="{{ $isDirect ? 'table-warning' : '' }}">
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="badge bg-primary">{{ $voucher->voucher_no }}</span>
                                            @if($isDirect)
                                                <span class="badge bg-warning text-dark small">Direct</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-light rounded-circle me-2">
                                                <span class="avatar-title text-primary">{{ substr($voucher->creator->name, 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $voucher->creator->name }}</div>
                                                <small class="text-muted">{{ $voucher->creator->employee_id ?? 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="badge bg-info">{{ $voucher->creator->primaryDepartment->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">TZS {{ number_format($voucher->amount ?? 0, 2) }}</strong>
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        <div class="text-truncate" style="max-width: 220px;" title="{{ $voucher->purpose }}">
                                            {{ $voucher->purpose }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $voucher->status_badge_class }}">{{ ucfirst(str_replace('_', ' ', $voucher->status)) }}</span>
                                    </td>
                                    @if($status === 'pending_retirement' || $status === 'retired')
                                        <td class="d-none d-md-table-cell">
                                            @if(is_array($voucher->retirement_receipts) && count($voucher->retirement_receipts))
                                                <span class="badge bg-info">{{ count($voucher->retirement_receipts) }} file(s)</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="d-none d-md-table-cell">
                                        <small class="text-muted">{{ $voucher->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info" onclick="viewDetails({{ $voucher->id }})" title="View">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            <a class="btn btn-secondary" href="{{ route('petty-cash.pdf', $voucher->id) }}" target="_blank" title="PDF">
                                                <i class="bx bxs-file-pdf"></i>
                                            </a>
                                            @if($voucher->status === 'pending_accountant')
                                                <button type="button" class="btn btn-success" onclick="openVerify({{ $voucher->id }})" title="Verify">
                                                    <i class="bx bx-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="openReject({{ $voucher->id }})" title="Reject">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                            @elseif($voucher->status === 'pending_retirement_review')
                                                <button type="button" class="btn btn-success btn-complete-retire" data-id="{{ $voucher->id }}" title="Complete">
                                                    <i class="bx bx-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="openRejectRetire({{ $voucher->id }})" title="Reject">
                                                    <i class="bx bx-x"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center p-3">
                        {{ $vouchers->appends(request()->query())->links() }}
                    </div>
                    @elseif(($status ?? 'pending_accountant') !== 'all')
                    <div class="text-center py-5">
                        <i class="bx bx-inbox text-muted" style="font-size: 4rem;"></i>
                        <h5 class="text-muted mt-3">No Vouchers Found</h5>
                        <p class="text-muted">
                            @if($status === 'pending_accountant')
                                No requests pending your verification.
                            @elseif($status === 'pending_retirement')
                                No retirements pending review.
                            @else
                                No vouchers found for this status.
                            @endif
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Verify Modal -->
<div class="modal fade" id="verifyModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="bx bx-check-circle me-2"></i>Verify Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="verifyForm" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>Select GL Account and Cash Box to verify this request.
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">GL Account <span class="text-danger">*</span></label>
              <select class="form-select" id="gl_account_id" name="gl_account_id" required>
                <option value="">-- Select GL Account --</option>
                @foreach(($glAccounts ?? []) as $gl)
                  <option value="{{ $gl->id }}">{{ $gl->code }} — {{ $gl->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cash Box <span class="text-danger">*</span></label>
              <select class="form-select" id="cash_box_id" name="cash_box_id" required>
                <option value="">-- Select Cash Box --</option>
                @foreach(($cashBoxes ?? []) as $cb)
                  <option value="{{ $cb->id }}">{{ $cb->name }} ({{ $cb->currency ?? 'TZS' }})</option>
                @endforeach
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Comments (Optional)</label>
              <textarea class="form-control" id="verify_comments" name="comments" rows="3" placeholder="Add verification comments..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Verify & Forward</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bx bx-x-circle me-2"></i>Reject Request</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="rejectForm" method="POST">
        @csrf
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>This will reject the request and notify the staff member.
          </div>
          <div class="mb-3">
            <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea class="form-control" id="reject_comments" name="comments" rows="4" required placeholder="Provide detailed reason..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Reject Request</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bx bx-detail me-2"></i>Voucher Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailsContent">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2">Loading details...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Direct Voucher Modal -->
<div class="modal fade" id="directVoucherModal" tabindex="-1" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bx bx-plus-circle me-2"></i>Create Direct Voucher</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="directVoucherForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>Direct vouchers are for in-office expenses. Auto-verified and forwarded to HOD.
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Date <span class="text-danger">*</span></label>
              <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Payee <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="payee" required placeholder="Enter payee name">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Purpose <span class="text-danger">*</span></label>
            <textarea class="form-control" name="purpose" rows="3" required placeholder="Enter purpose..."></textarea>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">GL Account <span class="text-danger">*</span></label>
              <select class="form-select" name="gl_account_id" required>
                <option value="">-- Select GL Account --</option>
                @foreach(($glAccounts ?? []) as $gl)
                  <option value="{{ $gl->id }}">{{ $gl->code }} — {{ $gl->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Cash Box <span class="text-danger">*</span></label>
              <select class="form-select" name="cash_box_id" required>
                <option value="">-- Select Cash Box --</option>
                @foreach(($cashBoxes ?? []) as $cb)
                  <option value="{{ $cb->id }}">{{ $cb->name }} ({{ $cb->currency ?? 'TZS' }})</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Notes (Optional)</label>
            <textarea class="form-control" name="notes" rows="2" placeholder="Additional notes..."></textarea>
          </div>

          <hr>
          <h6 class="mb-3"><i class="bx bx-list-ul me-2"></i>Voucher Lines</h6>

          <div id="voucherLinesContainer">
            <div class="card mb-3 voucher-line-item">
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-md-5">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="line_description[]" required>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Qty <span class="text-danger">*</span></label>
                    <input type="number" class="form-control line-qty" name="line_qty[]" step="0.01" min="0.01" required>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text">TZS</span>
                      <input type="number" class="form-control line-unit-price" name="line_unit_price[]" step="0.01" min="0.01" required>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <label class="form-label">Total</label>
                    <input type="text" class="form-control line-total" readonly>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button type="button" class="btn btn-outline-primary btn-sm" id="addVoucherLine">
            <i class="bx bx-plus me-1"></i>Add Line
          </button>

          <hr>
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="text-success mb-0">Total: <span id="directVoucherTotal">TZS 0.00</span></h5>
          </div>

          <hr>
          <h6 class="mb-3"><i class="bx bx-paperclip me-2"></i>Attachments (Optional)</h6>
          <div class="border border-dashed rounded p-4 text-center" id="directVoucherFileArea" style="cursor: pointer;">
            <input type="file" class="d-none" name="attachments[]" id="directVoucherAttachments" multiple accept=".pdf,.jpg,.jpeg,.png" onchange="previewDirectVoucherFiles(this)">
            <div id="directVoucherFilePlaceholder">
              <i class="bx bx-cloud-upload text-muted" style="font-size: 3rem;"></i>
              <p class="mb-0 mt-2"><strong>Click to upload</strong> or drag and drop</p>
              <p class="text-muted small mb-0">PDF, JPG, PNG (Max 10MB per file)</p>
            </div>
            <div id="directVoucherFilePreview" class="d-none mt-3">
              <div id="directVoucherFileList"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submitDirectVoucherBtn">Create Voucher</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
let currentVoucherId = null;
let lineCounter = 1;

function viewDetails(voucherId) {
  currentVoucherId = voucherId;
  $('#detailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>');
  
  var modal = new bootstrap.Modal(document.getElementById('detailsModal'));
  modal.show();

  fetch(`/petty-cash/${voucherId}/details`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        $('#detailsContent').html(data.html);
      } else {
        $('#detailsContent').html('<div class="alert alert-danger">' + data.message + '</div>');
      }
    })
    .catch(err => {
      $('#detailsContent').html('<div class="alert alert-danger">Error: ' + err.message + '</div>');
    });
}

function openVerify(voucherId) {
  currentVoucherId = voucherId;
  $('#verifyForm').attr('action', `/petty-cash/${voucherId}/accountant-verify`);
  if (!$('#verifyForm input[name="action"]').length) {
    $('#verifyForm').append('<input type="hidden" name="action" value="approve">');
  }
  new bootstrap.Modal(document.getElementById('verifyModal')).show();
}

function openReject(voucherId) {
  currentVoucherId = voucherId;
  $('#rejectForm').attr('action', `/petty-cash/${voucherId}/accountant-verify`);
  if (!$('#rejectForm input[name="action"]').length) {
    $('#rejectForm').append('<input type="hidden" name="action" value="reject">');
  }
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function openRejectRetire(voucherId) {
  currentVoucherId = voucherId;
  $('#rejectForm').attr('action', `/petty-cash/${voucherId}/approve-retirement`);
  if (!$('#rejectForm input[name="action"]').length) {
    $('#rejectForm').append('<input type="hidden" name="action" value="reject">');
  } else {
    $('#rejectForm input[name="action"]').val('reject');
  }
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

// Direct Voucher Functions
$(document).ready(function() {
  function calculateLineTotal(lineItem) {
    const qty = parseFloat($(lineItem).find('.line-qty').val()) || 0;
    const unitPrice = parseFloat($(lineItem).find('.line-unit-price').val()) || 0;
    const total = qty * unitPrice;
    $(lineItem).find('.line-total').val(total.toFixed(2));
    calculateGrandTotal();
  }

  function calculateGrandTotal() {
    let grandTotal = 0;
    $('.voucher-line-item').each(function() {
      const total = parseFloat($(this).find('.line-total').val()) || 0;
      grandTotal += total;
    });
    $('#directVoucherTotal').text('TZS ' + grandTotal.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    }));
  }

  $('#addVoucherLine').on('click', function() {
    lineCounter++;
    const newLine = `
      <div class="card mb-3 voucher-line-item">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Line ${lineCounter}</h6>
            <button type="button" class="btn btn-sm btn-outline-danger remove-line">
              <i class="bx bx-trash"></i> Remove
            </button>
          </div>
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label">Description <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="line_description[]" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Qty <span class="text-danger">*</span></label>
              <input type="number" class="form-control line-qty" name="line_qty[]" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Unit Price <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">TZS</span>
                <input type="number" class="form-control line-unit-price" name="line_unit_price[]" step="0.01" min="0.01" required>
              </div>
            </div>
            <div class="col-md-2">
              <label class="form-label">Total</label>
              <input type="text" class="form-control line-total" readonly>
            </div>
          </div>
        </div>
      </div>
    `;
    $('#voucherLinesContainer').append(newLine);
  });

  $(document).on('click', '.remove-line', function() {
    if ($('.voucher-line-item').length > 1) {
      $(this).closest('.voucher-line-item').remove();
      calculateGrandTotal();
    } else {
      alert('You must have at least one voucher line.');
    }
  });

  $(document).on('input', '.line-qty, .line-unit-price', function() {
    calculateLineTotal($(this).closest('.voucher-line-item'));
  });

  $('#directVoucherFileArea').on('click', function() {
    $('#directVoucherAttachments').click();
  });

  $('#directVoucherForm').on('submit', function(e) {
    e.preventDefault();
    const submitBtn = $('#submitDirectVoucherBtn');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

    const formData = new FormData(this);
    $.ajax({
      url: '{{ route("petty-cash.accountant.direct-voucher") }}',
      method: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function(response) {
        if (response.success) {
          if (typeof Swal !== 'undefined') {
            Swal.fire('Success!', response.message || 'Voucher created successfully', 'success').then(() => {
              location.reload();
            });
          } else {
            alert(response.message || 'Voucher created successfully');
            location.reload();
          }
        } else {
          if (typeof Swal !== 'undefined') {
            Swal.fire('Error!', response.message || 'Failed to create voucher', 'error');
          } else {
            alert('Error: ' + (response.message || 'Failed to create voucher'));
          }
          submitBtn.prop('disabled', false).html(originalText);
        }
      },
      error: function(xhr) {
        let errorMsg = 'Error creating voucher';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMsg = xhr.responseJSON.message;
        }
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error!', errorMsg, 'error');
        } else {
          alert(errorMsg);
        }
        submitBtn.prop('disabled', false).html(originalText);
      }
    });
  });

  $('#directVoucherModal').on('hidden.bs.modal', function() {
    $('#directVoucherForm')[0].reset();
    $('#voucherLinesContainer').html(`
      <div class="card mb-3 voucher-line-item">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label">Description <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="line_description[]" required>
            </div>
            <div class="col-md-2">
              <label class="form-label">Qty <span class="text-danger">*</span></label>
              <input type="number" class="form-control line-qty" name="line_qty[]" step="0.01" min="0.01" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Unit Price <span class="text-danger">*</span></label>
              <div class="input-group">
                <span class="input-group-text">TZS</span>
                <input type="number" class="form-control line-unit-price" name="line_unit_price[]" step="0.01" min="0.01" required>
              </div>
            </div>
            <div class="col-md-2">
              <label class="form-label">Total</label>
              <input type="text" class="form-control line-total" readonly>
            </div>
          </div>
        </div>
      </div>
    `);
    $('#directVoucherTotal').text('TZS 0.00');
    directVoucherFiles = [];
    updateDirectVoucherFilePreview();
    lineCounter = 1;
  });
});

// Retirement completion
document.addEventListener('DOMContentLoaded', function(){
  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  document.querySelectorAll('.btn-complete-retire').forEach(function(btn){
    btn.addEventListener('click', function(){
      const id = this.getAttribute('data-id');
      if (!id) return;
      
      if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
        Swal.fire({
          title: 'Complete Retirement?',
          html: '<p class="mb-3">Mark receipts as verified and complete the request.</p>' +
                '<textarea id="retire-comments" class="swal2-textarea form-control" placeholder="Comments (optional)"></textarea>',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Complete',
          cancelButtonText: 'Cancel',
          preConfirm: () => {
            const comments = (document.getElementById('retire-comments')||{}).value || '';
            return { comments };
          }
        }).then(function(result){
          if (result.isConfirmed) {
            const fd = new FormData();
            fd.append('_token', csrf);
            fd.append('action', 'approve');
            if (result.value && result.value.comments) fd.append('comments', result.value.comments);
            
            Swal.showLoading();
            fetch(`/petty-cash/${id}/approve-retirement`, { 
              method:'POST', 
              headers: { 'Accept': 'application/json' }, 
              body: fd 
            })
              .then(async r => {
                let j = {};
                try { j = await r.json(); } catch(e) {}
                if (!r.ok || (j && j.success === false)) {
                  throw new Error(j.message || 'Request failed');
                }
                return j;
              })
              .then(() => { 
                Swal.fire('Completed', 'Retirement completed successfully.', 'success').then(()=>location.reload()); 
              })
              .catch((err) => { 
                Swal.fire('Error', err.message || 'Failed to complete retirement.', 'error'); 
              });
          }
        });
      } else {
        if (confirm('Mark retirement as completed?')) {
          const fd = new FormData();
          fd.append('_token', csrf);
          fd.append('action', 'approve');
          fetch(`/petty-cash/${id}/approve-retirement`, { 
            method:'POST', 
            headers: { 'Accept': 'application/json' }, 
            body: fd 
          })
            .then(r=>{ if(!r.ok) throw new Error('Request failed'); })
            .then(() => location.reload())
            .catch(() => alert('Failed to complete retirement'));
        }
      }
    });
  });
});

// File handling
let directVoucherFiles = [];

function previewDirectVoucherFiles(input) {
  const files = input.files;
  if (!files || files.length === 0) {
    directVoucherFiles = [];
    updateDirectVoucherFilePreview();
    return;
  }

  Array.from(files).forEach(file => {
    const exists = directVoucherFiles.some(f => f.name === file.name && f.size === file.size);
    if (!exists && file.size <= 10 * 1024 * 1024) {
      directVoucherFiles.push(file);
    }
  });

  updateDirectVoucherFilePreview();
}

function updateDirectVoucherFilePreview() {
  if (directVoucherFiles.length === 0) {
    $('#directVoucherFilePlaceholder').removeClass('d-none');
    $('#directVoucherFilePreview').addClass('d-none');
    $('#directVoucherFileList').empty();
    return;
  }

  $('#directVoucherFilePlaceholder').addClass('d-none');
  $('#directVoucherFilePreview').removeClass('d-none');
  $('#directVoucherFileList').empty();

  directVoucherFiles.forEach((file, index) => {
    const fileSize = (file.size / 1024 / 1024).toFixed(2);
    const fileItem = `
      <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded mb-2">
        <div class="d-flex align-items-center">
          <i class="bx bx-file me-2 text-primary"></i>
          <div>
            <strong>${file.name}</strong>
            <small class="text-muted d-block">${fileSize} MB</small>
          </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDirectVoucherFile(${index})">
          <i class="bx bx-x"></i>
        </button>
      </div>
    `;
    $('#directVoucherFileList').append(fileItem);
  });

  const dataTransfer = new DataTransfer();
  directVoucherFiles.forEach(file => dataTransfer.items.add(file));
  $('#directVoucherAttachments')[0].files = dataTransfer.files;
}

function removeDirectVoucherFile(index) {
  if (index >= 0 && index < directVoucherFiles.length) {
    directVoucherFiles.splice(index, 1);
    updateDirectVoucherFilePreview();
  }
}

// Export function
function exportTable() {
  const table = document.getElementById('vouchersTable');
  if (!table) {
    if (typeof Swal !== 'undefined') {
      Swal.fire('Warning!', 'No data to export', 'warning');
    } else {
      alert('No data to export');
    }
    return;
  }
  
  let csv = [];
  const rows = table.querySelectorAll('tr');
  
  for (let i = 0; i < rows.length; i++) {
    const row = [];
    const cols = rows[i].querySelectorAll('td, th');
    
    for (let j = 0; j < cols.length; j++) {
      if (cols[j].querySelector('.btn-group')) continue;
      let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/,/g, ';');
      row.push('"' + data + '"');
    }
    
    if (row.length > 0) csv.push(row.join(','));
  }
  
  const blob = new Blob([csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = 'petty_cash_' + new Date().toISOString().split('T')[0] + '.csv';
  link.click();
}

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
  document.querySelectorAll('.alert').forEach(function(alert) {
    const bsAlert = new bootstrap.Alert(alert);
    bsAlert.close();
  });
}, 5000);
</script>
@endpush
