<!-- Manager View - Payroll Dashboard Content -->

<!-- Payroll Dashboard -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0 text-white">
                            <i class="bx bx-calendar-check me-2"></i>Payroll Dashboard
                        </h5>
                        <small class="text-white-50">Manage and track all payroll records</small>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bx bx-filter me-2"></i>Filter
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item filter-btn" href="javascript:void(0);" onclick="filterPayrolls('all')">
                                <i class="bx bx-list-ul me-2"></i>All Payrolls
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item filter-btn" href="javascript:void(0);" onclick="filterPayrolls('processed')">
                                <i class="bx bx-time me-2 text-warning"></i>Processed
                            </a></li>
                            <li><a class="dropdown-item filter-btn" href="javascript:void(0);" onclick="filterPayrolls('reviewed')">
                                <i class="bx bx-check me-2 text-info"></i>Reviewed
                            </a></li>
                            <li><a class="dropdown-item filter-btn" href="javascript:void(0);" onclick="filterPayrolls('approved')">
                                <i class="bx bx-check-circle me-2 text-success"></i>Approved
                            </a></li>
                            <li><a class="dropdown-item filter-btn" href="javascript:void(0);" onclick="filterPayrolls('paid')">
                                <i class="bx bx-dollar me-2 text-primary"></i>Paid
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($payrolls as $payroll)
                @php
                    $empCount = $payroll->employee_count ?? $payroll->items_count ?? 0;
                    $totalAmount = $payroll->total_amount ?? $payroll->items_sum_net_salary ?? 0;
                    $totalEmployerCost = $payroll->total_employer_cost ?? 0;
                    $statusClass = match($payroll->status) {
                        'processed' => 'warning',
                        'reviewed' => 'info',
                        'approved' => 'success',
                        'paid' => 'primary',
                        'rejected' => 'danger',
                        'cancelled' => 'secondary',
                        default => 'secondary'
                    };
                    $statusIcon = match($payroll->status) {
                        'processed' => 'bx-time',
                        'reviewed' => 'bx-check',
                        'approved' => 'bx-check-circle',
                        'paid' => 'bx-dollar',
                        'rejected' => 'bx-x-circle',
                        'cancelled' => 'bx-x',
                        default => 'bx-info-circle'
                    };
                @endphp
                <div class="payroll-card border-bottom payroll-row" data-status="{{ $payroll->status }}">
                    <div class="p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-3 col-md-4 mb-3 mb-md-0">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-lg me-3" style="width: 60px; height: 60px;">
                                        <span class="avatar-initial rounded-circle bg-label-{{ $statusClass }}" style="font-size: 1.5rem; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                            <i class="bx {{ $statusIcon }}"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1 fw-bold">{{ date('F Y', strtotime($payroll->pay_period . '-01')) }}</h5>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-{{ $statusClass }}">{{ ucfirst($payroll->status) }}</span>
                                            @if($payroll->pay_date)
                                            <small class="text-muted">
                                                <i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::parse($payroll->pay_date)->format('M d, Y') }}
                                            </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                        <i class="bx bx-user text-primary fs-4 me-2"></i>
                                        <div>
                                            <h4 class="mb-0 fw-bold">{{ $empCount }}</h4>
                                            <small class="text-muted">Employees</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 mb-3 mb-md-0">
                                <div class="text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                        <i class="bx bx-money text-success fs-4 me-2"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-success">TZS {{ number_format($totalAmount, 0) }}</h6>
                                            <small class="text-muted">Net Pay</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 mb-3 mb-md-0">
                                <div class="text-center">
                                    <div class="d-flex align-items-center justify-content-center mb-1">
                                        <i class="bx bx-building text-info fs-4 me-2"></i>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-info">TZS {{ number_format($totalEmployerCost, 0) }}</h6>
                                            <small class="text-muted">Employer Cost</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-3 mb-3 mb-md-0">
                                @if($payroll->processor)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($payroll->processor->name ?? 'N', 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 small">{{ $payroll->processor->name ?? 'N/A' }}</h6>
                                        <small class="text-muted">
                                            <i class="bx bx-time me-1"></i>{{ $payroll->created_at ? \Carbon\Carbon::parse($payroll->created_at)->format('M d, Y') : 'N/A' }}
                                        </small>
                                    </div>
                                </div>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </div>
                            <div class="col-lg-1 col-md-12 text-end">
                                <div class="dropdown">
                                    <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i> Actions
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a class="dropdown-item" href="{{ route('payroll.view.page', $payroll->id) }}">
                                            <i class="bx bx-show me-2"></i> View Details
                                        </a>
                                        
                                        @if($payroll->status === 'processed' && $can_review_payroll)
                                        <a class="dropdown-item" href="{{ route('payroll.review.page', $payroll->id) }}">
                                            <i class="bx bx-check me-2"></i> Review
                                        </a>
                                        @endif
                                        
                                        @if($payroll->status === 'reviewed' && $can_approve_payroll)
                                        <a class="dropdown-item" href="{{ route('payroll.approve.page', $payroll->id) }}">
                                            <i class="bx bx-check-circle me-2"></i> Approve
                                        </a>
                                        @endif
                                        
                                        @if($payroll->status === 'approved' && $can_pay_payroll)
                                        <a class="dropdown-item" href="{{ route('payroll.pay.page', $payroll->id) }}">
                                            <i class="bx bx-dollar me-2"></i> Mark as Paid
                                        </a>
                                        @endif
                                        
                                        @if($payroll->status === 'paid' || $payroll->status === 'approved')
                                        <a class="dropdown-item" href="{{ route('payroll.report.pdf', $payroll->id) }}" target="_blank">
                                            <i class="bx bx-file me-2"></i> Download PDF
                                        </a>
                                        @endif
                                        @if($payroll->status === 'paid')
                                        <a class="dropdown-item" href="javascript:void(0);" onclick="exportPayroll({{ $payroll->id }})">
                                            <i class="bx bx-download me-2"></i> Export Excel
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bx bx-calculator text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-muted">No Payroll Records Found</h5>
                        <p class="text-muted">Start by processing your first payroll.</p>
                        @if($can_process_payroll)
                        <a href="{{ route('payroll.process.page') }}" class="btn btn-primary mt-2">
                            <i class="bx bx-plus me-2"></i>Process New Payroll
                        </a>
                        @endif
                    </div>
                </div>
                @endforelse
                
                @if($payrolls->hasPages())
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-center">
                        {{ $payrolls->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function filterPayrolls(status) {
    const payrollCards = document.querySelectorAll('.payroll-row');
    let visibleCount = 0;
    
    payrollCards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        if (status === 'all' || cardStatus === status.toLowerCase()) {
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show message if no results
    const container = document.querySelector('.card-body');
    let noResultsDiv = container.querySelector('.no-results-message');
    
    if (visibleCount === 0 && status !== 'all') {
        if (!noResultsDiv) {
            noResultsDiv = document.createElement('div');
            noResultsDiv.className = 'no-results-message text-center py-5';
            noResultsDiv.innerHTML = `
                <div class="d-flex flex-column align-items-center">
                    <i class="bx bx-search text-muted" style="font-size: 4rem;"></i>
                    <h5 class="mt-3 text-muted">No ${status} payrolls found</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="filterPayrolls('all')">
                        Show All Payrolls
                    </button>
                </div>
            `;
            container.appendChild(noResultsDiv);
        }
        noResultsDiv.style.display = 'block';
    } else if (noResultsDiv) {
        noResultsDiv.style.display = 'none';
    }
    
    // Update active filter button
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    const activeBtn = document.querySelector(`[onclick="filterPayrolls('${status}')"]`);
    if (activeBtn) {
        activeBtn.classList.add('active');
    }
}

function viewPendingReview() {
    filterPayrolls('processed');
    // Scroll to table
    document.getElementById('payrollTable')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Filtered',
            text: 'Showing payrolls pending review',
            icon: 'info',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
}

function viewPendingApproval() {
    filterPayrolls('reviewed');
    // Scroll to table
    document.getElementById('payrollTable')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Filtered',
            text: 'Showing payrolls pending approval',
            icon: 'info',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
}

function viewApprovedPayroll() {
    filterPayrolls('approved');
    // Scroll to table
    document.getElementById('payrollTable')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Filtered',
            text: 'Showing approved payrolls ready for payment',
            icon: 'info',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
}

function exportPayroll(payrollId) {
    if (!payrollId) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Payroll ID not found.',
                toast: true,
                position: 'top-end',
                timer: 3000
            });
        } else {
            alert('Payroll ID not found.');
        }
        return;
    }
    
    const exportUrl = `{{ route('payroll.export', ['payroll' => ':id']) }}`.replace(':id', payrollId);
    console.log('Exporting payroll:', exportUrl);
    
    // Show loading
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Exporting...',
            text: 'Preparing payroll export...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    // Open in new window for download
    const downloadWindow = window.open(exportUrl, '_blank');
    
    // Check if download started (basic check)
    setTimeout(() => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Export Started',
                text: 'Payroll export will download shortly...',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false
            });
        }
    }, 500);
}

// Make functions globally available
window.filterPayrolls = filterPayrolls;
window.viewPendingReview = viewPendingReview;
window.viewPendingApproval = viewPendingApproval;
window.viewApprovedPayroll = viewApprovedPayroll;
window.exportPayroll = exportPayroll;
</script>


