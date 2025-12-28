@extends('layouts.app')

@section('title', 'Petty Cash Management Dashboard')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Petty Cash Management Dashboard</h4>
</div>
@endsection

@push('styles')
<style>
    .petty-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        height: 100%;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .petty-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        transition: width 0.3s ease;
    }
    
    .petty-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .petty-card:hover::before {
        width: 100%;
        opacity: 0.1;
    }
    
    .petty-card.card-primary::before { background: #007bff; }
    .petty-card.card-warning::before { background: #ffc107; }
    .petty-card.card-info::before { background: #17a2b8; }
    .petty-card.card-success::before { background: #28a745; }
    .petty-card.card-danger::before { background: #dc3545; }
    .petty-card.card-secondary::before { background: #6c757d; }
    .petty-card.card-purple::before { background: #6f42c1; }
    .petty-card.card-orange::before { background: #fd7e14; }
    
    .petty-card .card-body {
        padding: 2rem;
    }
    
    .petty-card .icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 2rem;
    }
    
    .petty-card .count {
        font-size: 3rem;
        font-weight: 700;
        line-height: 1;
        margin: 1rem 0;
    }
    
    .petty-card .title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .petty-card .badge {
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
    }
    
    .create-card {
        background: var(--bs-primary);
        color: white;
        border: none;
    }
    
    .create-card:hover {
        background: var(--bs-primary);
        opacity: 0.9;
    }
    
    .stats-summary {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 bg-primary">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-money me-2"></i>Petty Cash Management
                    </h2>
                    <p class="mb-0 opacity-90">Complete workflow: Request → Accountant Review → HOD Approval → CEO Approval → Payment → Retirement</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="row g-4">
        @php
            $isAccountant = auth()->user()->hasRole('Accountant') || auth()->user()->hasRole('System Admin');
            $user = auth()->user();
            $myRequestsCount = \App\Models\PettyCashVoucher::where('created_by', $user->id)->count();
        @endphp

        <!-- My Petty Cash Requests -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('petty-cash.my-requests') }}" class="text-decoration-none">
                <div class="card petty-card card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-primary bg-opacity-10 text-primary">
                                <i class="bx bx-clipboard"></i>
                            </div>
                            @if($myRequestsCount > 0)
                            <span class="badge bg-primary">{{ $myRequestsCount }}</span>
                            @endif
                        </div>
                        <div class="count text-primary">{{ $myRequestsCount }}</div>
                        <div class="title">My Petty Cash Requests</div>
                        <p class="text-muted mb-0 small">Create and manage your petty cash requests</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- All Petty Cash Vouchers -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('petty-cash.all') }}" class="text-decoration-none">
                <div class="card petty-card card-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-info bg-opacity-10 text-info">
                                <i class="bx bx-list-ul"></i>
                            </div>
                        </div>
                        <div class="count text-info">{{ $stats['all'] ?? 0 }}</div>
                        <div class="title">All Petty Cash Vouchers</div>
                        <p class="text-muted mb-0 small">View and filter all petty cash vouchers with advanced search options</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Direct Vouchers (Accountant Only) -->
        @if($isAccountant)
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('petty-cash.direct-vouchers.index') }}" class="text-decoration-none">
                <div class="card petty-card card-secondary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-secondary bg-opacity-10 text-secondary">
                                <i class="bx bx-file-blank"></i>
                            </div>
                            @php
                                $directPendingCount = \App\Models\PettyCashVoucher::where('status', 'pending_hod')
                                    ->whereRaw('created_by = accountant_id')
                                    ->whereNotNull('accountant_id')
                                    ->whereNotNull('accountant_verified_at')
                                    ->count();
                            @endphp
                            @if($directPendingCount > 0)
                            <span class="badge bg-secondary">{{ $directPendingCount }}</span>
                            @endif
                        </div>
                        <div class="count text-secondary">{{ $directPendingCount }}</div>
                        <div class="title">Direct Vouchers</div>
                        <p class="text-muted mb-0 small">In-Office Expenses Management</p>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

