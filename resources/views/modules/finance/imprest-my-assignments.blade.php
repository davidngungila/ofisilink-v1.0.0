@extends('layouts.app')

@section('title', 'My Imprest Assignments')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">My Imprest Assignments</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('imprest.index') }}">Imprest Dashboard</a></li>
            <li class="breadcrumb-item active">My Assignments</li>
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
                        <i class="bx bx-clipboard me-2"></i>My Imprest Assignments
                    </h2>
                    <p class="mb-0 opacity-90">View and manage all your imprest assignments</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ route('imprest.index') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back me-1"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-start border-start-4 border-start-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assignments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['all'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-list-ul text-primary" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-start border-start-4 border-start-info shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Assigned (Awaiting Payment)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['assigned'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-time text-info" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-start border-start-4 border-start-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Paid (Submit Receipts)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['paid'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-money text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-start border-start-4 border-start-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Receipt Submission</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_receipt'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-receipt text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert for Pending Receipts -->
    @if(($stats['pending_receipt'] ?? 0) > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="bx bx-info-circle me-2"></i>Action Required</h5>
        <p class="mb-0">You have <strong>{{ $stats['pending_receipt'] ?? 0 }}</strong> imprest assignment(s) that require receipt submission. Please submit your receipts as soon as possible after payment.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('imprest.my-assignments') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status Filter</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Assignments</option>
                        <option value="assigned" {{ ($status ?? 'all') === 'assigned' ? 'selected' : '' }}>Assigned (Awaiting Payment)</option>
                        <option value="paid" {{ ($status ?? 'all') === 'paid' ? 'selected' : '' }}>Paid (Submit Receipts)</option>
                        <option value="completed" {{ ($status ?? 'all') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by request number or purpose..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-search me-1"></i>Search
                    </button>
                </div>
                @if(request()->hasAny(['status', 'search']))
                <div class="col-12">
                    <a href="{{ route('imprest.my-assignments') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-x me-1"></i>Clear Filters
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Assignments Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($assignments->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Request #</th>
                            <th>Amount Assigned</th>
                            <th>Amount Paid</th>
                            <th>Purpose</th>
                            <th>Assignment Date</th>
                            <th>Payment Status</th>
                            <th>Payment Date</th>
                            <th>Imprest Status</th>
                            <th>Receipt Status</th>
                            <th>Receipts Count</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                        @php
                            $hasReceipts = $assignment->receipts && $assignment->receipts->count() > 0;
                            $isSubmitted = $assignment->receipt_submitted || $hasReceipts;
                            $isPaid = $assignment->paid_at && $assignment->paid_amount > 0;
                        @endphp
                        <tr class="{{ $assignment->imprestRequest->status === 'paid' && !$isSubmitted ? 'table-warning' : '' }}">
                            <td>
                                <strong class="text-primary">{{ $assignment->imprestRequest->request_no }}</strong>
                            </td>
                            <td>
                                <strong class="text-info">TZS {{ number_format($assignment->assigned_amount ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                @if($isPaid)
                                    <strong class="text-success">TZS {{ number_format($assignment->paid_amount ?? 0, 2) }}</strong>
                                @else
                                    <span class="text-muted">Not Paid</span>
                                @endif
                            </td>
                            <td>
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $assignment->imprestRequest->purpose }}">
                                    {{ $assignment->imprestRequest->purpose }}
                                </div>
                            </td>
                            <td>
                                <small>
                                    <i class="bx bx-calendar"></i> 
                                    {{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : 'N/A' }}
                                </small>
                            </td>
                            <td>
                                @if($isPaid)
                                    <span class="badge bg-success">
                                        <i class="bx bx-check-circle"></i> Paid
                                    </span>
                                    @if($assignment->payment_method)
                                        <br><small class="text-muted">{{ ucwords(str_replace('_', ' ', $assignment->payment_method)) }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bx bx-time"></i> Not Paid
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($assignment->payment_date)
                                    <small>{{ \Carbon\Carbon::parse($assignment->payment_date)->format('M d, Y') }}</small>
                                @elseif($assignment->paid_at)
                                    <small>{{ $assignment->paid_at->format('M d, Y') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $assignment->imprestRequest->status === 'paid' ? 'success' : ($assignment->imprestRequest->status === 'completed' ? 'info' : ($assignment->imprestRequest->status === 'assigned' ? 'primary' : 'secondary')) }}">
                                    {{ ucwords(str_replace('_', ' ', $assignment->imprestRequest->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($isSubmitted)
                                    <span class="badge bg-success">
                                        <i class="bx bx-check-circle"></i> Submitted
                                    </span>
                                    @if($assignment->receipt_submitted_at)
                                        <br><small class="text-muted">{{ $assignment->receipt_submitted_at->format('M d, Y') }}</small>
                                    @elseif($hasReceipts && $assignment->receipts->first()->submitted_at)
                                        <br><small class="text-muted">{{ $assignment->receipts->first()->submitted_at->format('M d, Y') }}</small>
                                    @endif
                                    @if($hasReceipts)
                                        @php
                                            $verifiedCount = $assignment->receipts->where('is_verified', true)->count();
                                            $totalCount = $assignment->receipts->count();
                                        @endphp
                                        @if($verifiedCount === $totalCount)
                                            <br><small class="text-success"><i class="bx bx-check-double"></i> All Verified</small>
                                        @else
                                            <br><small class="text-warning"><i class="bx bx-time-five"></i> {{ $verifiedCount }}/{{ $totalCount }} Verified</small>
                                        @endif
                                    @endif
                                @else
                                    <span class="badge bg-warning">
                                        <i class="bx bx-time"></i> Not Submitted
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($hasReceipts)
                                    <span class="badge bg-info">{{ $assignment->receipts->count() }} receipt(s)</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm flex-wrap" role="group">
                                    <a href="{{ route('imprest.show', $assignment->imprestRequest->id) }}" class="btn btn-outline-primary" title="View Full Details">
                                        <i class="bx bx-show"></i> View
                                    </a>
                                    @if($isPaid && $assignment->imprestRequest->status === 'paid' && !$isSubmitted)
                                    <a href="{{ route('imprest.submit-receipt.page', ['assignmentId' => $assignment->id]) }}" class="btn btn-success" title="Submit Receipt">
                                        <i class="bx bx-upload"></i> Submit Receipt
                                    </a>
                                    @endif
                                    @if($hasReceipts || $isSubmitted)
                                    <a href="{{ route('imprest.my-receipts.page', ['assignmentId' => $assignment->id]) }}" class="btn btn-outline-info" title="View My Receipts">
                                        <i class="bx bx-receipt"></i> Receipts{{ $hasReceipts ? ' (' . $assignment->receipts->count() . ')' : '' }}
                                    </a>
                                    @endif
                                    <a href="{{ route('imprest.pdf', $assignment->imprestRequest->id) }}" class="btn btn-outline-secondary" target="_blank" title="Download PDF">
                                        <i class="bx bx-file-blank"></i> PDF
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer bg-white border-top">
                {{ $assignments->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="bx bx-inbox fs-1 text-muted"></i>
                <p class="text-muted mt-2 mb-0">No assignments found</p>
            </div>
            @endif
        </div>
    </div>
</div>

@include('modules.finance.imprest-partials.modals')
@include('modules.finance.imprest-partials.scripts')
@endsection

