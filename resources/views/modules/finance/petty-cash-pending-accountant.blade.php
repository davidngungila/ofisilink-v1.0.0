@extends('layouts.app')

@section('title', 'Pending Accountant Verification')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Pending Accountant Verification</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('petty-cash.index') }}">Petty Cash Dashboard</a></li>
            <li class="breadcrumb-item active">Pending Accountant Verification</li>
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
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 page-header-card bg-warning">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-time-five me-2"></i>Pending Accountant Verification
                    </h2>
                    <p class="mb-0 opacity-90">Review and verify petty cash vouchers pending your verification</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <span class="count-badge">{{ $count }}</span>
                    <p class="mb-0 small">Pending Vouchers</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('petty-cash.pending-accountant') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bx bx-filter me-1"></i>Filter
                    </button>
                    <a href="{{ route('petty-cash.pending-accountant') }}" class="btn btn-secondary">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Vouchers Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @include('modules.finance.petty-cash-partials.table', [
                'vouchers' => $vouchers,
                'showActions' => true,
                'actionType' => 'accountant',
                'glAccounts' => $glAccounts ?? collect(),
                'cashBoxes' => $cashBoxes ?? collect()
            ])
        </div>
    </div>
</div>

@include('modules.finance.petty-cash-partials.modals')
@include('modules.finance.petty-cash-partials.scripts')
@endsection







