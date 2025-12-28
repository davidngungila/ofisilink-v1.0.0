@extends('layouts.app')

@section('title', 'View Receipts for Verification')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">View Receipts for Verification</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('imprest.index') }}">Imprest Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('imprest.show', $assignment->imprestRequest->id) }}">{{ $assignment->imprestRequest->request_no }}</a></li>
            <li class="breadcrumb-item active">View Receipts</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .receipt-card {
        border-left: 4px solid #0d6efd;
        transition: all 0.3s ease;
    }
    
    .receipt-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .receipt-card.verified {
        border-left-color: #28a745;
    }
    
    .receipt-card.pending {
        border-left-color: #ffc107;
    }
    
    .info-card {
        border-left: 4px solid #0d6efd;
        background: #f8f9fa;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Action Buttons -->
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="{{ route('imprest.show', $assignment->imprestRequest->id) }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i>Back to Details
        </a>
    </div>

    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 bg-primary">
        <div class="card-body text-white">
            <h2 class="fw-bold mb-2 text-white">
                <i class="bx bx-receipt me-2"></i>View Receipts for Verification
            </h2>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Request Number:</strong> {{ $assignment->imprestRequest->request_no }}</p>
                    <p class="mb-1"><strong>Purpose:</strong> {{ $assignment->imprestRequest->purpose }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Assigned Amount:</strong> <strong>TZS {{ number_format($assignment->assigned_amount, 2) }}</strong></p>
                    <p class="mb-1"><strong>Staff Member:</strong> {{ $assignment->staff->name ?? 'Unknown' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Info -->
    <div class="card border-0 shadow-sm mb-4 info-card">
        <div class="card-body">
            <h5 class="mb-3"><i class="bx bx-info-circle me-2"></i>Assignment Information</h5>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Staff Member:</strong> {{ $assignment->staff->name ?? 'Unknown' }}</p>
                    <p class="mb-2"><strong>Assigned Amount:</strong> <span class="text-primary">TZS {{ number_format($assignment->assigned_amount, 2) }}</span></p>
                    <p class="mb-2"><strong>Assigned Date:</strong> {{ $assignment->assigned_at ? $assignment->assigned_at->format('d M Y') : 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Request Number:</strong> {{ $assignment->imprestRequest->request_no }}</p>
                    <p class="mb-2"><strong>Status:</strong> <span class="badge bg-{{ $assignment->imprestRequest->status === 'completed' ? 'success' : ($assignment->imprestRequest->status === 'pending_receipt_verification' ? 'warning' : 'info') }}">{{ ucwords(str_replace('_', ' ', $assignment->imprestRequest->status)) }}</span></p>
                    <p class="mb-2"><strong>Receipt Submitted:</strong> 
                        @if($assignment->receipt_submitted)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-warning">No</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipts List -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0 text-white">
                <i class="bx bx-receipt me-2"></i>Submitted Receipts ({{ $assignment->receipts->count() }})
            </h5>
        </div>
        <div class="card-body">
            @if($assignment->receipts->count() > 0)
                <div class="row">
                    @foreach($assignment->receipts as $receipt)
                    <div class="col-md-6 mb-4">
                        <div class="card receipt-card {{ $receipt->is_verified ? 'verified' : 'pending' }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="bx bx-file-alt me-2"></i>{{ $receipt->receipt_description }}
                                        </h6>
                                        <p class="text-muted mb-1 small">
                                            Amount: <strong class="text-primary">TZS {{ number_format($receipt->receipt_amount, 2) }}</strong>
                                        </p>
                                        <p class="text-muted mb-1 small">
                                            Submitted: {{ $receipt->submitted_at ? $receipt->submitted_at->format('d M Y, H:i') : 'N/A' }}
                                        </p>
                                        @if($receipt->submittedBy)
                                        <p class="text-muted mb-1 small">
                                            By: {{ $receipt->submittedBy->name }}
                                        </p>
                                        @endif
                                    </div>
                                    <div>
                                        @if($receipt->is_verified)
                                            <span class="badge bg-success">
                                                <i class="bx bx-check-circle"></i> Verified
                                            </span>
                                            @if($receipt->verified_at)
                                                <br><small class="text-muted">{{ $receipt->verified_at->format('d M Y') }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="bx bx-time"></i> Pending Verification
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($receipt->is_verified && $receipt->verifiedBy)
                                <div class="mb-2">
                                    <p class="text-success mb-1 small">
                                        <i class="bx bx-user-check"></i> Verified by {{ $receipt->verifiedBy->name }}
                                        @if($receipt->verified_at)
                                            on {{ $receipt->verified_at->format('d M Y, H:i') }}
                                        @endif
                                    </p>
                                    @if($receipt->verification_notes)
                                    <p class="text-info mb-1 small">
                                        <i class="bx bx-comment"></i> Notes: {{ $receipt->verification_notes }}
                                    </p>
                                    @endif
                                </div>
                                @endif
                                
                                <div class="d-flex gap-2 mt-3">
                                    @if($receipt->receipt_file_path)
                                    <a href="{{ asset('storage/' . $receipt->receipt_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bx bx-download"></i> View Receipt
                                    </a>
                                    @endif
                                    @if(!$receipt->is_verified && in_array($assignment->imprestRequest->status, ['pending_receipt_verification', 'paid']))
                                    <button class="btn btn-sm btn-success" onclick="openVerifyReceipt({{ $receipt->id }})">
                                        <i class="bx bx-check-circle"></i> Verify Receipt
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Summary -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="mb-0 text-primary">{{ $assignment->receipts->count() }}</h4>
                                <small class="text-muted">Total Receipts</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="mb-0 text-success">{{ $assignment->receipts->where('is_verified', true)->count() }}</h4>
                                <small class="text-muted">Verified</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <h4 class="mb-0 text-warning">{{ $assignment->receipts->where('is_verified', false)->count() }}</h4>
                                <small class="text-muted">Pending Verification</small>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <strong>Total Receipt Amount:</strong> 
                        <span class="text-primary">TZS {{ number_format($assignment->receipts->sum('receipt_amount'), 2) }}</span>
                        <small class="text-muted">/ TZS {{ number_format($assignment->assigned_amount, 2) }}</small>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bx bx-inbox" style="font-size: 4rem; color: #6c757d;"></i>
                    <h5 class="mt-3">No Receipts Submitted</h5>
                    <p class="text-muted">No receipts have been submitted for this assignment yet.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@include('modules.finance.imprest-partials.modals')
@include('modules.finance.imprest-partials.scripts')
@endsection







