@extends('layouts.app')

@section('title', 'Pending HOD Approval')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Pending HOD Approval</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('petty-cash.index') }}">Petty Cash Dashboard</a></li>
            <li class="breadcrumb-item active">Pending HOD Approval</li>
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
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 page-header-card bg-info">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-time-five me-2"></i>Pending HOD Approval
                    </h2>
                    <p class="mb-0 opacity-90">Awaiting your approval - Review and approve petty cash vouchers with advanced filtering</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <span class="count-badge">{{ $count }}</span>
                    <p class="mb-0 small">Pending Vouchers</p>
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
                <form method="GET" action="{{ route('petty-cash.pending-hod') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Voucher #, Purpose, Payee..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created From</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created To</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
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
                            @foreach($creators ?? [] as $creator)
                                <option value="{{ $creator->id }}" {{ request('creator_id') == $creator->id ? 'selected' : '' }}>
                                    {{ $creator->name }} ({{ $creator->employee_id ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Voucher Type</label>
                        <select name="voucher_type" class="form-select">
                            <option value="">All Types</option>
                            <option value="regular" {{ request('voucher_type') === 'regular' ? 'selected' : '' }}>Regular Vouchers</option>
                            <option value="direct" {{ request('voucher_type') === 'direct' ? 'selected' : '' }}>Direct Vouchers</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Order By</label>
                        <select name="order_by" class="form-select">
                            <option value="created_at" {{ request('order_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                            <option value="date" {{ request('order_by') === 'date' ? 'selected' : '' }}>Voucher Date</option>
                            <option value="total_amount" {{ request('order_by') === 'total_amount' ? 'selected' : '' }}>Amount</option>
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
                        <a href="{{ route('petty-cash.pending-hod') }}" class="btn btn-secondary">
                            <i class="bx bx-refresh me-1"></i>Reset
                        </a>
                        @if(request()->hasAny(['search', 'from_date', 'to_date', 'amount_min', 'amount_max', 'creator_id', 'voucher_type']))
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
                'showActions' => true,
                'actionType' => 'hod'
            ])
        </div>
    </div>
</div>

@include('modules.finance.petty-cash-partials.modals')
@include('modules.finance.petty-cash-partials.scripts')
@endsection

