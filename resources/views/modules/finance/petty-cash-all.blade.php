@extends('layouts.app')

@section('title', 'All Petty Cash Vouchers')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">All Petty Cash Vouchers</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('petty-cash.index') }}">Petty Cash Dashboard</a></li>
            <li class="breadcrumb-item active">All Vouchers</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .page-header-card {
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        border: none;
    }
    
    .count-badge {
        font-size: 2.5rem;
        font-weight: 700;
    }
    
    .filter-card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .stats-badge {
        font-size: 0.85rem;
        padding: 0.25rem 0.75rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 page-header-card bg-primary">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-list-ul me-2"></i>All Petty Cash Vouchers
                    </h2>
                    <p class="mb-0 opacity-90">View and filter all petty cash vouchers with advanced search options</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <span class="count-badge">{{ $count }}</span>
                    <p class="mb-0 small">Total Results</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Summary -->
    <div class="row mb-4">
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-start border-start-4 border-start-primary">
                <div class="card-body p-2">
                    <div class="text-xs text-muted">All</div>
                    <div class="h6 mb-0">{{ $stats['all'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-start border-start-4 border-start-warning">
                <div class="card-body p-2">
                    <div class="text-xs text-muted">Pending</div>
                    <div class="h6 mb-0">{{ ($stats['pending_accountant'] ?? 0) + ($stats['pending_hod'] ?? 0) + ($stats['pending_ceo'] ?? 0) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-start border-start-4 border-start-success">
                <div class="card-body p-2">
                    <div class="text-xs text-muted">Approved</div>
                    <div class="h6 mb-0">{{ $stats['approved_for_payment'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-start border-start-4 border-start-info">
                <div class="card-body p-2">
                    <div class="text-xs text-muted">Paid</div>
                    <div class="h6 mb-0">{{ $stats['paid'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-start border-start-4 border-start-warning">
                <div class="card-body p-2">
                    <div class="text-xs text-muted">Retirement</div>
                    <div class="h6 mb-0">{{ $stats['pending_retirement_review'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card border-start border-start-4 border-start-dark">
                <div class="card-body p-2">
                    <div class="text-xs text-muted">Retired</div>
                    <div class="h6 mb-0">{{ $stats['retired'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="card border-0 shadow-sm mb-4 filter-card">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bx bx-filter me-2"></i>Advanced Filters
                <button class="btn btn-sm btn-outline-secondary float-end" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true">
                    <i class="bx bx-chevron-down"></i> Toggle Filters
                </button>
            </h5>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body">
                <form method="GET" action="{{ route('petty-cash.all') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                            <option value="pending_accountant" {{ request('status') === 'pending_accountant' ? 'selected' : '' }}>Pending Accountant</option>
                            <option value="pending_hod" {{ request('status') === 'pending_hod' ? 'selected' : '' }}>Pending HOD</option>
                            <option value="pending_ceo" {{ request('status') === 'pending_ceo' ? 'selected' : '' }}>Pending CEO</option>
                            <option value="approved_for_payment" {{ request('status') === 'approved_for_payment' ? 'selected' : '' }}>Approved for Payment</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="pending_retirement_review" {{ request('status') === 'pending_retirement_review' ? 'selected' : '' }}>Pending Retirement</option>
                            <option value="retired" {{ request('status') === 'retired' ? 'selected' : '' }}>Retired</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Voucher #, Purpose, Payee..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Voucher Date From</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Voucher Date To</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created From</label>
                        <input type="date" name="created_from" class="form-control" value="{{ request('created_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created To</label>
                        <input type="date" name="created_to" class="form-control" value="{{ request('created_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount Min</label>
                        <input type="number" name="amount_min" class="form-control" step="0.01" placeholder="0.00" value="{{ request('amount_min') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Amount Max</label>
                        <input type="number" name="amount_max" class="form-control" step="0.01" placeholder="0.00" value="{{ request('amount_max') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Creator</label>
                        <select name="creator_id" class="form-select">
                            <option value="">All Creators</option>
                            @foreach($creators as $creator)
                                <option value="{{ $creator->id }}" {{ request('creator_id') == $creator->id ? 'selected' : '' }}>
                                    {{ $creator->name }} ({{ $creator->employee_id ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Accountant</label>
                        <select name="accountant_id" class="form-select">
                            <option value="">All Accountants</option>
                            @foreach($accountants as $accountant)
                                <option value="{{ $accountant->id }}" {{ request('accountant_id') == $accountant->id ? 'selected' : '' }}>
                                    {{ $accountant->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Voucher Type</label>
                        <select name="is_direct" class="form-select">
                            <option value="">All Types</option>
                            <option value="yes" {{ request('is_direct') === 'yes' ? 'selected' : '' }}>Direct Vouchers</option>
                            <option value="no" {{ request('is_direct') === 'no' ? 'selected' : '' }}>Regular Vouchers</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Order By</label>
                        <select name="order_by" class="form-select">
                            <option value="created_at" {{ request('order_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                            <option value="date" {{ request('order_by') === 'date' ? 'selected' : '' }}>Voucher Date</option>
                            <option value="amount" {{ request('order_by') === 'amount' ? 'selected' : '' }}>Amount</option>
                            <option value="voucher_no" {{ request('order_by') === 'voucher_no' ? 'selected' : '' }}>Voucher Number</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Order Direction</label>
                        <select name="order_dir" class="form-select">
                            <option value="desc" {{ request('order_dir') === 'desc' ? 'selected' : '' }}>Descending</option>
                            <option value="asc" {{ request('order_dir') === 'asc' ? 'selected' : '' }}>Ascending</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Per Page</label>
                        <select name="per_page" class="form-select">
                            <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-filter me-1"></i>Apply Filters
                        </button>
                        <a href="{{ route('petty-cash.all') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh me-1"></i>Reset
                        </a>
                        @if(request()->hasAny(['status', 'search', 'date_from', 'date_to', 'created_from', 'created_to', 'amount_min', 'amount_max', 'creator_id', 'accountant_id', 'is_direct']))
                        <span class="badge bg-info ms-2">
                            <i class="bx bx-info-circle"></i> Filters Active
                        </span>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Vouchers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @include('modules.finance.petty-cash-partials.table', [
                'vouchers' => $vouchers,
                'showActions' => true
            ])
        </div>
    </div>
</div>

@include('modules.finance.petty-cash-partials.modals')
@include('modules.finance.petty-cash-partials.scripts')
@endsection







