@extends('layouts.app')

@section('title', 'Petty Cash Management - Unified Dashboard')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-money"></i> Petty Cash Management
                </h4>
                <p class="text-muted">Enterprise-grade petty cash verification and management system</p>
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('petty-cash.index') }}" class="btn btn-outline-primary">
                    <i class="bx bx-arrow-back"></i> Dashboard
                </a>
                <button class="btn btn-primary" id="create-voucher-btn">
                    <i class="bx bx-plus"></i> New Request
                </button>
                @if(Auth::user()->hasAnyRole(['Accountant', 'System Admin']))
                <a href="{{ route('petty-cash.direct-vouchers.index') }}" class="btn btn-outline-warning">
                    <i class="bx bx-file-blank"></i> Direct Vouchers
                </a>
                @endif
                <button class="btn btn-outline-info" id="refresh-btn">
                    <i class="bx bx-refresh"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .stat-card { 
        transition: all 0.3s ease; 
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        background: white;
    }
    .stat-card:hover { 
        transform: translateY(-2px); 
        box-shadow: 0 8px 15px rgba(0,0,0,0.15); 
    }
    .voucher-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s;
        border-left: 4px solid #007bff;
        cursor: pointer;
    }
    .voucher-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .voucher-card.direct {
        border-left-color: #ffc107;
    }
    .voucher-card.pending {
        border-left-color: #ffc107;
    }
    .voucher-card.approved {
        border-left-color: #28a745;
    }
    .voucher-card.paid {
        border-left-color: #17a2b8;
    }
    .voucher-card.rejected {
        border-left-color: #dc3545;
    }
    .filter-sidebar {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        max-height: 600px;
        overflow-y: auto;
    }
    .filter-btn {
        width: 100%;
        margin-bottom: 10px;
        text-align: left;
        padding: 12px 15px;
        border-radius: 8px;
        transition: all 0.3s;
    }
    .filter-btn.active {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }
    .filter-btn:hover:not(.active) {
        background: #e9ecef;
    }
    .badge-custom {
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .progress-mini {
        height: 4px;
        border-radius: 2px;
    }
    .swal2-container { z-index: 200000 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Dashboard Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Pending My Action</h6>
                        <h3 class="mb-0 text-danger" id="pendingActionCount">{{ $counts['pending_action'] ?? 0 }}</h3>
                        <small class="text-muted">Requires immediate attention</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-danger rounded">
                            <i class="bx bx-time"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Pending HOD</h6>
                        <h3 class="mb-0 text-warning" id="pendingHodCount">
                            @php
                                // For HOD: show all (regular + direct), for others: show regular only
                                $pendingHodDisplay = ($isHOD ?? false) ? 
                                    (($counts['pending_hod_regular'] ?? 0) + ($counts['direct_pending'] ?? 0)) : 
                                    ($counts['pending_hod_regular'] ?? 0);
                            @endphp
                            {{ $pendingHodDisplay }}
                        </h3>
                        <small class="text-muted">Awaiting HOD approval</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-warning rounded">
                            <i class="bx bx-user-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Pending CEO</h6>
                        <h3 class="mb-0 text-info" id="pendingCeoCount">{{ $counts['pending_ceo'] ?? 0 }}</h3>
                        <small class="text-muted">Awaiting CEO approval</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-info rounded">
                            <i class="bx bx-user-circle"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Amount</h6>
                        <h3 class="mb-0 text-success" id="totalAmount">TZS {{ number_format($counts['total_amount'] ?? 0, 2) }}</h3>
                        <small class="text-muted">All pending vouchers</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-success rounded">
                            <i class="bx bx-money"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Sidebar - Filters -->
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-filter"></i> Filters & Navigation
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="filter-sidebar">
                        @php
                            $user = Auth::user();
                            $isAccountant = $user->hasAnyRole(['Accountant', 'System Admin']);
                            $isHOD = $user->hasAnyRole(['HOD', 'System Admin']);
                            $isCEO = $user->hasAnyRole(['CEO', 'System Admin']);
                            $currentFilter = request('filter', 'my_action');
                        @endphp

                        @if($isAccountant)
                        <button class="btn filter-btn {{ $currentFilter === 'my_action' ? 'active' : '' }}" 
                                onclick="loadVouchers('my_action')">
                            <i class="bx bx-time me-2"></i>My Action
                            <span class="badge bg-danger float-end" id="myActionBadge">{{ $counts['pending_accountant'] ?? 0 }}</span>
                        </button>
                        @endif

                        @if($isHOD || $isAccountant)
                        <button class="btn filter-btn {{ $currentFilter === 'pending_hod' ? 'active' : '' }}" 
                                onclick="loadVouchers('pending_hod')">
                            <i class="bx bx-user-check me-2"></i>Pending HOD
                            @if($isHOD)
                                <span class="badge bg-warning float-end" id="pendingHodBadge">{{ ($counts['pending_hod_regular'] ?? 0) + ($counts['direct_pending'] ?? 0) }}</span>
                            @else
                                <span class="badge bg-warning float-end" id="pendingHodBadge">{{ $counts['pending_hod_regular'] ?? 0 }}</span>
                            @endif
                        </button>
                        @endif

                        @if($isCEO || $isAccountant || $isHOD)
                        <button class="btn filter-btn {{ $currentFilter === 'pending_ceo' ? 'active' : '' }}" 
                                onclick="loadVouchers('pending_ceo')">
                            <i class="bx bx-user-circle me-2"></i>Pending CEO
                            <span class="badge bg-info float-end" id="pendingCeoBadge">{{ $counts['pending_ceo'] ?? 0 }}</span>
                        </button>
                        @endif

                        <button class="btn filter-btn {{ $currentFilter === 'approved' ? 'active' : '' }}" 
                                onclick="loadVouchers('approved')">
                            <i class="bx bx-check-circle me-2"></i>Approved
                            <span class="badge bg-success float-end" id="approvedBadge">{{ $counts['approved'] ?? 0 }}</span>
                        </button>

                        <button class="btn filter-btn {{ $currentFilter === 'paid' ? 'active' : '' }}" 
                                onclick="loadVouchers('paid')">
                            <i class="bx bx-money me-2"></i>Paid
                            <span class="badge bg-primary float-end" id="paidBadge">{{ $counts['paid'] ?? 0 }}</span>
                        </button>

                        @if($isAccountant)
                        <button class="btn filter-btn {{ $currentFilter === 'retirement' ? 'active' : '' }}" 
                                onclick="loadVouchers('retirement')">
                            <i class="bx bx-receipt me-2"></i>Retirement
                            <span class="badge bg-warning float-end" id="retirementBadge">{{ $counts['pending_retirement'] ?? 0 }}</span>
                        </button>
                        @endif

                        <hr>

                        <button class="btn filter-btn {{ $currentFilter === 'all' ? 'active' : '' }}" 
                                onclick="loadVouchers('all')">
                            <i class="bx bx-list-ul me-2"></i>All Vouchers
                        </button>

                        @if($isAccountant || $isHOD)
                        <button class="btn filter-btn {{ $currentFilter === 'direct' ? 'active' : '' }}" 
                                onclick="loadVouchers('direct')">
                            <i class="bx bx-file-blank me-2"></i>Direct Vouchers
                            <span class="badge bg-warning float-end" id="directBadge">{{ $counts['direct_pending'] ?? 0 }}</span>
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bx bx-zap"></i> Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="window.location.href='{{ route('petty-cash.index') }}'">
                            <i class="bx bx-home"></i> Main Dashboard
                        </button>
                        @if($isAccountant)
                        <button class="btn btn-outline-success btn-sm" onclick="window.location.href='{{ route('petty-cash.direct-vouchers.index') }}'">
                            <i class="bx bx-file-blank"></i> Direct Vouchers
                        </button>
                        @endif
                        <button class="btn btn-outline-info btn-sm" onclick="exportTable()">
                            <i class="bx bx-export"></i> Export Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-xl-9 col-lg-8">
            <!-- Search and Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchInput" placeholder="Search by voucher no, payee, purpose...">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Status</option>
                                <option value="pending_accountant">Pending Accountant</option>
                                <option value="pending_hod">Pending HOD</option>
                                <option value="pending_ceo">Pending CEO</option>
                                <option value="approved_for_payment">Approved</option>
                                <option value="paid">Paid</option>
                                <option value="pending_retirement_review">Retirement</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" class="form-control" id="dateFilter" placeholder="Filter by date">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" onclick="applyFilters()">
                                <i class="bx bx-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vouchers List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0" id="pageTitle">
                        @php
                            $titles = [
                                'my_action' => '<i class="bx bx-time"></i> My Action Items',
                                'pending_hod' => '<i class="bx bx-user-check"></i> Pending HOD Approval',
                                'pending_ceo' => '<i class="bx bx-user-circle"></i> Pending CEO Approval',
                                'approved' => '<i class="bx bx-check-circle"></i> Approved Vouchers',
                                'paid' => '<i class="bx bx-money"></i> Paid Vouchers',
                                'retirement' => '<i class="bx bx-receipt"></i> Retirement Review',
                                'all' => '<i class="bx bx-list-ul"></i> All Vouchers',
                                'direct' => '<i class="bx bx-file-blank"></i> Direct Vouchers'
                            ];
                            $currentTitle = $titles[$filter ?? 'my_action'] ?? '<i class="bx bx-list-ul"></i> Vouchers';
                        @endphp
                        {!! $currentTitle !!}
                    </h5>
                </div>
                <div class="card-body">
                    <div id="vouchersContainer">
                        <!-- Vouchers will be loaded here -->
                        @include('modules.finance.partials.petty-vouchers-list', ['vouchers' => $vouchers ?? collect()])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Voucher Details Modal -->
<div class="modal fade" id="voucherDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-file"></i> Voucher Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="voucherDetailsContent" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading voucher details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="exportPdfBtn" target="_blank">
                    <i class="bx bxs-file-pdf"></i> Export PDF
                </a>
                <a href="#" class="btn btn-outline-primary" id="fullDetailsBtn" target="_blank">
                    <i class="bx bx-detail"></i> Full Details
                </a>
            </div>
        </div>
    </div>
</div>

@include('modules.finance.partials.direct-voucher-modal')
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    let currentFilter = '{{ request("filter", "my_action") }}';
    let currentVoucherId = null;
    const isHOD = {{ ($isHOD ?? false) ? 'true' : 'false' }};
    const isAccountant = {{ ($isAccountant ?? false) ? 'true' : 'false' }};

    // Load vouchers based on filter
    window.loadVouchers = function(filter) {
        currentFilter = filter;
        updateActiveFilter(filter);
        updatePageTitle(filter);
        
        $.ajax({
            url: '{{ route("petty-cash.unified") }}',
            method: 'GET',
            data: {
                filter: filter,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    $('#vouchersContainer').html(response.html);
                    updateCounts(response.counts);
                } else {
                    Swal.fire('Error!', response.message || 'Failed to load vouchers', 'error');
                }
            },
            error: function() {
                Swal.fire('Error!', 'Failed to load vouchers', 'error');
            }
        });
    };

    // Update active filter button
    function updateActiveFilter(filter) {
        $('.filter-btn').removeClass('active');
        $(`.filter-btn[onclick*="${filter}"]`).addClass('active');
    }

    // Update page title
    function updatePageTitle(filter) {
        const titles = {
            'my_action': '<i class="bx bx-time"></i> My Action Items',
            'pending_hod': '<i class="bx bx-user-check"></i> Pending HOD Approval',
            'pending_ceo': '<i class="bx bx-user-circle"></i> Pending CEO Approval',
            'approved': '<i class="bx bx-check-circle"></i> Approved Vouchers',
            'paid': '<i class="bx bx-money"></i> Paid Vouchers',
            'retirement': '<i class="bx bx-receipt"></i> Retirement Review',
            'all': '<i class="bx bx-list-ul"></i> All Vouchers',
            'direct': '<i class="bx bx-file-blank"></i> Direct Vouchers'
        };
        $('#pageTitle').html(titles[filter] || '<i class="bx bx-list-ul"></i> Vouchers');
    }

    // Update counts
    function updateCounts(counts) {
        if (counts) {
            // Update dashboard stat cards
            $('#pendingActionCount').text(counts.pending_action || 0);
            
            // For HOD: show all pending HOD (regular + direct), for others: show regular only
            const pendingHodDisplay = isHOD ? 
                ((counts.pending_hod_regular || 0) + (counts.direct_pending || 0)) : 
                (counts.pending_hod_regular || 0);
            $('#pendingHodCount').text(pendingHodDisplay);
            
            // Update Pending HOD badge
            if (isHOD) {
                $('#pendingHodBadge').text((counts.pending_hod_regular || 0) + (counts.direct_pending || 0));
            } else {
                $('#pendingHodBadge').text(counts.pending_hod_regular || 0);
            }
            
            $('#pendingCeoCount').text(counts.pending_ceo || 0);
            
            // Update sidebar badges with exact counts
            if (counts.pending_accountant !== undefined) {
                $('#myActionBadge').text(counts.pending_accountant || 0);
            }
            
            // Pending HOD badge already updated above
            
            if (counts.pending_ceo !== undefined) {
                $('#pendingCeoBadge').text(counts.pending_ceo || 0);
            }
            if (counts.approved !== undefined) {
                $('#approvedBadge').text(counts.approved || 0);
            }
            if (counts.paid !== undefined) {
                $('#paidBadge').text(counts.paid || 0);
            }
            if (counts.pending_retirement !== undefined) {
                $('#retirementBadge').text(counts.pending_retirement || 0);
            }
            if (counts.direct_pending !== undefined) {
                $('#directBadge').text(counts.direct_pending || 0);
            }
            
            // Update total amount
            $('#totalAmount').text('TZS ' + (counts.total_amount ? parseFloat(counts.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'));
        }
    }

    // View voucher details
    window.viewVoucherDetails = function(voucherId) {
        currentVoucherId = voucherId;
        
        // Show loading
        $('#voucherDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading voucher details...</p>
            </div>
        `);
        $('#voucherDetailsModal').modal('show');
        
        // Load details via AJAX
        $.ajax({
            url: '{{ route("petty-cash.show", ":id") }}'.replace(':id', voucherId),
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                // If response contains HTML, extract it
                if (response.html) {
                    $('#voucherDetailsContent').html(response.html);
                } else if (response.view) {
                    // If it's a view path, load it
                    loadVoucherDetailsView(voucherId);
                } else {
                    // Try to get the details partial
                    loadVoucherDetailsPartial(voucherId);
                }
                
                // Set PDF export and full details links
                $('#exportPdfBtn').attr('href', '{{ route("petty-cash.pdf", ":id") }}'.replace(':id', voucherId));
                $('#fullDetailsBtn').attr('href', '{{ route("petty-cash.show", ":id") }}'.replace(':id', voucherId));
            },
            error: function() {
                // Fallback: load details partial directly
                loadVoucherDetailsPartial(voucherId);
            }
        });
    };

    // Load voucher details partial
    function loadVoucherDetailsPartial(voucherId) {
        $.ajax({
            url: '{{ url("petty-cash") }}/' + voucherId + '/details-ajax',
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success && response.html) {
                    $('#voucherDetailsContent').html(response.html);
                } else {
                    // Try to load from show route with AJAX
                    $.ajax({
                        url: '{{ route("petty-cash.show", ":id") }}'.replace(':id', voucherId),
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(html) {
                            // Extract the content from the full page
                            const $temp = $('<div>').html(html);
                            const content = $temp.find('.container-fluid, .card, .row').first().html();
                            $('#voucherDetailsContent').html(content || html);
                        },
                        error: function() {
                            $('#voucherDetailsContent').html('<div class="alert alert-danger">Failed to load voucher details. <a href="{{ route("petty-cash.show", ":id") }}" target="_blank">View full page</a></div>'.replace(':id', voucherId));
                        }
                    });
                }
            },
            error: function() {
                // Try show route as fallback
                $.ajax({
                    url: '{{ route("petty-cash.show", ":id") }}'.replace(':id', voucherId),
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(html) {
                        const $temp = $('<div>').html(html);
                        const content = $temp.find('.container-fluid, .card-body').first().html();
                        $('#voucherDetailsContent').html(content || html);
                    },
                    error: function() {
                        $('#voucherDetailsContent').html('<div class="alert alert-danger">Failed to load voucher details. <a href="{{ route("petty-cash.show", ":id") }}" target="_blank">View full page</a></div>'.replace(':id', voucherId));
                    }
                });
            }
        });
    }

    // Accountant verify
    window.accountantVerify = function(voucherId) {
        window.location.href = '{{ route("petty-cash.accountant.verify", ":id") }}'.replace(':id', voucherId);
    };

    // HOD approve
    window.hodApprove = function(voucherId) {
        Swal.fire({
            title: 'Approve Voucher',
            text: 'Are you sure you want to approve this voucher?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("petty-cash.hod.approve", ":id") }}'.replace(':id', voucherId);
            }
        });
    };

    // CEO approve
    window.ceoApprove = function(voucherId) {
        Swal.fire({
            title: 'Approve Voucher',
            text: 'Are you sure you want to approve this voucher?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '{{ route("petty-cash.ceo.approve", ":id") }}'.replace(':id', voucherId);
            }
        });
    };

    // Apply filters
    window.applyFilters = function() {
        const search = $('#searchInput').val();
        const status = $('#statusFilter').val();
        const date = $('#dateFilter').val();
        
        $.ajax({
            url: '{{ route("petty-cash.unified") }}',
            method: 'GET',
            data: {
                filter: currentFilter,
                search: search,
                status: status,
                date: date,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    $('#vouchersContainer').html(response.html);
                }
            }
        });
    };

    // Export table
    window.exportTable = function() {
        window.location.href = '{{ route("petty-cash.unified") }}?filter=' + currentFilter + '&export=1';
    };

    // Refresh button
    $('#refresh-btn').on('click', function() {
        loadVouchers(currentFilter);
    });

    // New Request button
    $('#create-voucher-btn').on('click', function() {
        window.location.href = '{{ route("petty-cash.my-requests") }}';
    });

    // Search on enter
    $('#searchInput').on('keypress', function(e) {
        if (e.which === 13) {
            applyFilters();
        }
    });

    // Initialize with current filter
    loadVouchers(currentFilter);
});
</script>
@endpush

