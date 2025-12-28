@extends('layouts.app')

@section('title', 'Approved - Assign Staff')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Approved - Assign Staff</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('imprest.index') }}">Imprest Dashboard</a></li>
            <li class="breadcrumb-item active">Approved</li>
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
    <div class="card border-0 shadow-sm mb-4 page-header-card bg-primary">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-user-check me-2"></i>Approved - Assign Staff
                    </h2>
                    <p class="mb-0 opacity-90">Assign staff members to approved imprest requests</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <span class="count-badge">{{ $count }}</span>
                    <p class="mb-0 small">Approved Requests</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('imprest.approved') }}" class="row g-3">
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
                    <a href="{{ route('imprest.approved') }}" class="btn btn-secondary">
                        <i class="bx bx-refresh me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @include('modules.finance.imprest-partials.table', [
                'requests' => $requests,
                'showActions' => true,
                'actionType' => 'assign'
            ])
        </div>
    </div>
</div>

@include('modules.finance.imprest-partials.modals')
@include('modules.finance.imprest-partials.scripts')
@endsection

