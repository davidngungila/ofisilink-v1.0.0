@extends('layouts.app')

@section('title', 'Imprest Management Dashboard')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Imprest Management Dashboard</h4>
</div>
@endsection

@push('styles')
<style>
    .imprest-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        height: 100%;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    
    .imprest-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        transition: width 0.3s ease;
    }
    
    .imprest-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .imprest-card:hover::before {
        width: 100%;
        opacity: 0.1;
    }
    
    .imprest-card.card-primary::before { background: #007bff; }
    .imprest-card.card-warning::before { background: #ffc107; }
    .imprest-card.card-info::before { background: #17a2b8; }
    .imprest-card.card-success::before { background: #28a745; }
    .imprest-card.card-danger::before { background: #dc3545; }
    .imprest-card.card-secondary::before { background: #6c757d; }
    .imprest-card.card-purple::before { background: #6f42c1; }
    .imprest-card.card-orange::before { background: #fd7e14; }
    
    .imprest-card .card-body {
        padding: 2rem;
    }
    
    .imprest-card .icon-wrapper {
        width: 70px;
        height: 70px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        font-size: 2rem;
    }
    
    .imprest-card .count {
        font-size: 3rem;
        font-weight: 700;
        line-height: 1;
        margin: 1rem 0;
    }
    
    .imprest-card .title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .imprest-card .badge {
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
                        <i class="bx bx-wallet me-2"></i>Imprest Management
                    </h2>
                    <p class="mb-0 opacity-90">Complete workflow: Request → Approval → Assignment → Payment → Receipts → Verification</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('imprest.export-pdf') }}" class="btn btn-light btn-sm" target="_blank">
                        <i class="bx bx-file-blank me-1"></i>Export Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Summary -->
    <div class="stats-summary mb-4">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-3 mb-md-0">
                <h3 class="mb-0 text-primary">{{ $stats['all'] ?? 0 }}</h3>
                <small class="text-muted">Total Requests</small>
            </div>
            <div class="col-md-3 col-6 mb-3 mb-md-0">
                <h3 class="mb-0 text-warning">{{ ($stats['pending_hod'] ?? 0) + ($stats['pending_ceo'] ?? 0) }}</h3>
                <small class="text-muted">Pending Approvals</small>
            </div>
            <div class="col-md-3 col-6">
                <h3 class="mb-0 text-info">{{ $stats['assigned'] ?? 0 }}</h3>
                <small class="text-muted">Awaiting Payment</small>
            </div>
            <div class="col-md-3 col-6">
                <h3 class="mb-0 text-success">{{ $stats['completed'] ?? 0 }}</h3>
                <small class="text-muted">Completed</small>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="row g-4">
        @php
            $isAccountant = auth()->user()->hasRole('Accountant') || auth()->user()->hasRole('System Admin');
            $isHOD = auth()->user()->hasRole('HOD') || auth()->user()->hasRole('System Admin');
            $isCEO = auth()->user()->hasRole('CEO') || auth()->user()->hasRole('Director') || auth()->user()->hasRole('System Admin');
            $isStaff = auth()->user()->hasRole('Staff') || auth()->user()->hasRole('Employee');
        @endphp

        <!-- All Imprest Requests -->
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.index') }}" class="text-decoration-none">
                <div class="card imprest-card card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-primary bg-opacity-10 text-primary">
                                <i class="bx bx-list-ul"></i>
                            </div>
                        </div>
                        <div class="count text-primary">{{ $stats['all'] ?? 0 }}</div>
                        <div class="title">All Imprest Requests</div>
                        <p class="text-muted mb-0 small">View all imprest requests</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Create New Request -->
        @if($isAccountant)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.create') }}" class="text-decoration-none">
                <div class="card imprest-card create-card">
                    <div class="card-body text-center">
                        <div class="icon-wrapper mx-auto bg-white bg-opacity-20">
                            <i class="bx bx-plus-circle text-white"></i>
                        </div>
                        <div class="title text-white">Create New Request</div>
                        <p class="text-white-50 mb-0 small">Start a new imprest request</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <!-- Pending HOD Approval -->
        @if($isHOD)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.pending-hod') }}" class="text-decoration-none">
                <div class="card imprest-card card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-warning bg-opacity-10 text-warning">
                                <i class="bx bx-time-five"></i>
                            </div>
                            @if(($stats['pending_hod'] ?? 0) > 0)
                            <span class="badge bg-warning">{{ $stats['pending_hod'] ?? 0 }}</span>
                            @endif
                        </div>
                        <div class="count text-warning">{{ $stats['pending_hod'] ?? 0 }}</div>
                        <div class="title">Pending HOD Approval</div>
                        <p class="text-muted mb-0 small">Awaiting your review</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <!-- Pending CEO Approval -->
        @if($isCEO)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.pending-ceo') }}" class="text-decoration-none">
                <div class="card imprest-card card-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-info bg-opacity-10 text-info">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            @if(($stats['pending_ceo'] ?? 0) > 0)
                            <span class="badge bg-info">{{ $stats['pending_ceo'] ?? 0 }}</span>
                            @endif
                        </div>
                        <div class="count text-info">{{ $stats['pending_ceo'] ?? 0 }}</div>
                        <div class="title">Pending CEO Approval</div>
                        <p class="text-muted mb-0 small">Awaiting final approval</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <!-- Approved (Assign Staff) -->
        @if($isAccountant)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.approved') }}" class="text-decoration-none">
                <div class="card imprest-card card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-success bg-opacity-10 text-success">
                                <i class="bx bx-user-check"></i>
                            </div>
                            @if(($stats['approved'] ?? 0) > 0)
                            <span class="badge bg-success">{{ $stats['approved'] ?? 0 }}</span>
                            @endif
                        </div>
                        <div class="count text-success">{{ $stats['approved'] ?? 0 }}</div>
                        <div class="title">Approved (Assign Staff)</div>
                        <p class="text-muted mb-0 small">Ready for staff assignment</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <!-- Assigned (Payment) -->
        @if($isAccountant)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.assigned') }}" class="text-decoration-none">
                <div class="card imprest-card card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-primary bg-opacity-10 text-primary">
                                <i class="bx bx-credit-card"></i>
                            </div>
                            @if(($stats['assigned'] ?? 0) > 0)
                            <span class="badge bg-primary">{{ $stats['assigned'] ?? 0 }}</span>
                            @endif
                        </div>
                        <div class="count text-primary">{{ $stats['assigned'] ?? 0 }}</div>
                        <div class="title">Assigned (Payment)</div>
                        <p class="text-muted mb-0 small">Ready for payment processing</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <!-- Paid (Awaiting Receipts) -->
        @if($isAccountant)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.paid') }}" class="text-decoration-none">
                <div class="card imprest-card card-orange">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper" style="background: rgba(253, 126, 20, 0.1); color: #fd7e14;">
                                <i class="bx bx-receipt"></i>
                            </div>
                            @if(($stats['paid'] ?? 0) > 0)
                            <span class="badge" style="background: #fd7e14;">{{ $stats['paid'] ?? 0 }}</span>
                            @endif
                        </div>
                        <div class="count" style="color: #fd7e14;">{{ $stats['paid'] ?? 0 }}</div>
                        <div class="title">Paid (Awaiting Receipts)</div>
                        <p class="text-muted mb-0 small">Waiting for receipt submission</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <!-- Pending Verification -->
        @if($isAccountant)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.pending-verification') }}" class="text-decoration-none">
                <div class="card imprest-card card-purple">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper" style="background: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                                <i class="bx bx-check-double"></i>
                            </div>
                            @if(($stats['pending_receipt_verification'] ?? 0) > 0)
                            <span class="badge" style="background: #6f42c1;">{{ $stats['pending_receipt_verification'] ?? 0 }}</span>
                            @endif
                        </div>
                        <div class="count" style="color: #6f42c1;">{{ $stats['pending_receipt_verification'] ?? 0 }}</div>
                        <div class="title">Pending Verification</div>
                        <p class="text-muted mb-0 small">Receipts awaiting verification</p>
                    </div>
                </div>
            </a>
        </div>
        @endif

        <!-- Completed -->
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.completed') }}" class="text-decoration-none">
                <div class="card imprest-card card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-success bg-opacity-10 text-success">
                                <i class="bx bx-check-square"></i>
                            </div>
                            @if(($stats['completed'] ?? 0) > 0)
                            <span class="badge bg-success">{{ $stats['completed'] ?? 0 }}</span>
                            @endif
                        </div>
                        <div class="count text-success">{{ $stats['completed'] ?? 0 }}</div>
                        <div class="title">Completed</div>
                        <p class="text-muted mb-0 small">All completed requests</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- My Assignments (Staff Only) -->
        @if($isStaff)
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="{{ route('imprest.my-assignments') }}" class="text-decoration-none">
                <div class="card imprest-card card-danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="icon-wrapper bg-danger bg-opacity-10 text-danger">
                                <i class="bx bx-clipboard"></i>
                            </div>
                            @if($myAssignmentsCount > 0)
                            <span class="badge bg-danger">{{ $myAssignmentsCount }}</span>
                            @endif
                        </div>
                        <div class="count text-danger">{{ $myAssignmentsCount }}</div>
                        <div class="title">My Assignments</div>
                        <p class="text-muted mb-0 small">All my imprest assignments</p>
                    </div>
                </div>
            </a>
        </div>
        @endif
    </div>
</div>
@endsection

