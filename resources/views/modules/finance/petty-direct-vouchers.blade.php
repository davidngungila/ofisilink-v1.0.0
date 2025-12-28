@extends('layouts.app')

@section('title', 'Direct Vouchers Management - In-Office Expenses')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="card-title mb-0">
              <i class="bx bx-file-blank text-warning me-2"></i>Direct Vouchers Management
            </h4>
            <p class="text-muted mb-0">In-Office Expenses (Already Used) - HOD/Admin Approval Only</p>
          </div>
          <div>
            <a href="{{ route('petty-cash.index') }}" class="btn btn-outline-primary">
              <i class="bx bx-arrow-back"></i> Back to Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')
@php
    use Illuminate\Support\Str;
@endphp

<!-- Success/Error Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>Success!</strong> {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Error!</strong> {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="card border-start border-start-4 border-start-danger shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted text-uppercase small mb-1">Pending HOD</h6>
            <h3 class="mb-0 fw-bold text-danger">{{ $counts['direct_pending_hod'] ?? 0 }}</h3>
            <small class="text-muted">TZS {{ number_format($stats['direct_pending_hod_amount'] ?? 0, 2) }}</small>
          </div>
          <div class="bg-danger bg-opacity-10 rounded-circle p-3">
            <i class="bx bx-time text-danger" style="font-size: 1.75rem;"></i>
          </div>
        </div>
        <a href="{{ route('petty-cash.direct-vouchers.index', ['status' => 'pending_hod']) }}" 
           class="btn btn-sm btn-outline-danger w-100 mt-2 {{ $status === 'pending_hod' ? 'active' : '' }}">
          <i class="bx bx-show me-1"></i>View All
        </a>
      </div>
    </div>
  </div>
  
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="card border-start border-start-4 border-start-success shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted text-uppercase small mb-1">Paid (Complete)</h6>
            <h3 class="mb-0 fw-bold text-success">{{ $counts['direct_paid'] ?? 0 }}</h3>
            <small class="text-muted">TZS {{ number_format($stats['direct_paid_amount'] ?? 0, 2) }}</small>
          </div>
          <div class="bg-success bg-opacity-10 rounded-circle p-3">
            <i class="bx bx-check-circle text-success" style="font-size: 1.75rem;"></i>
          </div>
        </div>
        <a href="{{ route('petty-cash.direct-vouchers.index', ['status' => 'paid']) }}" 
           class="btn btn-sm btn-outline-success w-100 mt-2 {{ $status === 'paid' ? 'active' : '' }}">
          <i class="bx bx-show me-1"></i>View All
        </a>
      </div>
    </div>
  </div>
  
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="card border-start border-start-4 border-start-danger shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted text-uppercase small mb-1">Rejected</h6>
            <h3 class="mb-0 fw-bold text-danger">{{ $counts['direct_rejected'] ?? 0 }}</h3>
            <small class="text-muted">TZS {{ number_format($stats['direct_rejected_amount'] ?? 0, 2) }}</small>
          </div>
          <div class="bg-danger bg-opacity-10 rounded-circle p-3">
            <i class="bx bx-x-circle text-danger" style="font-size: 1.75rem;"></i>
          </div>
        </div>
        <a href="{{ route('petty-cash.direct-vouchers.index', ['status' => 'rejected']) }}" 
           class="btn btn-sm btn-outline-danger w-100 mt-2 {{ $status === 'rejected' ? 'active' : '' }}">
          <i class="bx bx-show me-1"></i>View All
        </a>
      </div>
    </div>
  </div>
  
  <div class="col-xl-3 col-md-6 mb-3">
    <div class="card border-start border-start-4 border-start-info shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted text-uppercase small mb-1">This Month</h6>
            <h3 class="mb-0 fw-bold text-info">{{ $stats['current_month_count'] ?? 0 }}</h3>
            <small class="text-muted">TZS {{ number_format($stats['current_month_total'] ?? 0, 2) }}</small>
          </div>
          <div class="bg-info bg-opacity-10 rounded-circle p-3">
            <i class="bx bx-calendar text-info" style="font-size: 1.75rem;"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Main Content Card -->
<div class="card shadow-sm border-warning">
  <div class="card-header bg-gradient-warning bg-opacity-10 border-bottom border-warning">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
      <div class="flex-grow-1">
        <div class="d-flex align-items-center gap-2 mb-2">
          <div class="bg-warning bg-opacity-20 rounded-circle p-2">
            <i class="bx bx-file-blank text-warning" style="font-size: 1.5rem;"></i>
          </div>
          <div>
            <h5 class="mb-0 fw-bold">
              <span class="text-warning">Direct Vouchers</span> - {{ ucwords(str_replace('_', ' ', $status)) }}
            </h5>
            <small class="text-muted">
              <i class="bx bx-info-circle"></i> In-Office Expenses (Already Used) - HOD/Admin Approval Only
            </small>
          </div>
        </div>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        @if($isAccountant)
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#directVoucherModal">
          <i class="bx bx-plus-circle me-1"></i>Create New Direct Voucher
        </button>
        @endif
      </div>
    </div>
  </div>
  
  <div class="card-body">
    <!-- Info Alert with Workflow -->
    <div class="alert alert-warning border-warning mb-4 d-flex align-items-start">
      <div class="me-3">
        <i class="bx bx-info-circle" style="font-size: 1.5rem;"></i>
      </div>
      <div class="flex-grow-1">
        <strong>Direct Voucher Workflow:</strong>
        <ul class="mb-0 mt-2 small">
          <li>These vouchers represent expenses <strong>already used in-office</strong></li>
          <li>Created by Accountant/System Admin with automatic verification</li>
          <li>Requires <strong>HOD or System Admin approval only</strong> (No CEO approval needed)</li>
          <li>Once approved, status automatically changes to <strong>Paid (Complete)</strong></li>
          <li>Ideal for immediate office expenses that need quick processing</li>
        </ul>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="card bg-light mb-4">
      <div class="card-body p-3">
        <form method="GET" action="{{ route('petty-cash.direct-vouchers.index') }}">
          <input type="hidden" name="status" value="{{ $status }}">
          <div class="row g-2">
            <div class="col-md-4">
              <label class="form-label small fw-bold mb-1">Quick Search</label>
              <input type="text" class="form-control form-control-sm" name="search" 
                     value="{{ request('search') }}" placeholder="Voucher No, Payee, Purpose...">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold mb-1">Date From</label>
              <input type="date" class="form-control form-control-sm" name="date_from" 
                     value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold mb-1">Date To</label>
              <input type="date" class="form-control form-control-sm" name="date_to" 
                     value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label small fw-bold mb-1">Min Amount</label>
              <input type="number" class="form-control form-control-sm" name="amount_min" 
                     value="{{ request('amount_min') }}" placeholder="0.00" step="0.01">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-warning btn-sm w-100">
                <i class="bx bx-search me-1"></i>Filter
              </button>
            </div>
          </div>
          @if(request()->hasAny(['search', 'date_from', 'date_to', 'amount_min', 'amount_max']))
          <div class="mt-2">
            <a href="{{ route('petty-cash.direct-vouchers.index', ['status' => $status]) }}" 
               class="btn btn-sm btn-outline-secondary">
              <i class="bx bx-x me-1"></i>Clear Filters
            </a>
          </div>
          @endif
        </form>
      </div>
    </div>

    <!-- Vouchers Table -->
    @if($vouchers->count() > 0)
    <div class="table-responsive">
      <table class="table table-striped table-hover table-bordered">
        <thead class="table-light">
          <tr>
            <th style="width: 120px;">Voucher No</th>
            <th style="min-width: 150px;">Created By</th>
            <th style="width: 120px;">Date</th>
            <th style="width: 130px;">Amount (TZS)</th>
            <th style="min-width: 200px;">Purpose</th>
            <th style="width: 120px;">Status</th>
            <th style="width: 150px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($vouchers as $voucher)
          <tr>
            <td>
              <div class="d-flex align-items-center flex-wrap">
                <span class="badge bg-primary mb-1">{{ $voucher->voucher_no }}</span>
                <span class="badge bg-warning text-dark ms-1 mb-1" title="Direct Voucher (In-Office)">
                  <i class="bx bx-file-blank"></i> Direct
                </span>
              </div>
            </td>
            <td>
              <div>
                <strong>{{ $voucher->creator->name ?? 'N/A' }}</strong>
                @if($voucher->creator)
                <br><small class="text-muted">{{ $voucher->creator->employee_id ?? '' }}</small>
                @endif
              </div>
            </td>
            <td>{{ $voucher->date->format('M d, Y') }}</td>
            <td class="text-end">
              <strong class="text-success">TZS {{ number_format($voucher->amount, 2) }}</strong>
            </td>
            <td>{{ Str::limit($voucher->purpose, 50) }}</td>
            <td>
              <span class="badge bg-{{ $voucher->status == 'pending_hod' ? 'warning' : ($voucher->status == 'paid' ? 'success' : 'danger') }}">
                {{ ucwords(str_replace('_', ' ', $voucher->status)) }}
              </span>
            </td>
            <td>
              <div class="btn-group" role="group">
                <a href="{{ route('petty-cash.show', $voucher) }}" class="btn btn-sm btn-info" title="View Details">
                  <i class="bx bx-show"></i>
                </a>
                @if($isHOD && $voucher->status == 'pending_hod')
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" 
                        data-bs-target="#approveModal{{ $voucher->id }}" title="Approve">
                  <i class="bx bx-check"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" 
                        data-bs-target="#rejectModal{{ $voucher->id }}" title="Reject">
                  <i class="bx bx-x"></i>
                </button>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
      <div>
        <small class="text-muted">
          Showing {{ $vouchers->firstItem() ?? 0 }}-{{ $vouchers->lastItem() ?? 0 }} of {{ $vouchers->total() }} vouchers
        </small>
      </div>
      <div>
        {{ $vouchers->links() }}
      </div>
    </div>
    @else
    <div class="alert alert-info">
      <i class="bx bx-info-circle"></i> No direct vouchers found for this status.
    </div>
    @endif
  </div>
</div>

<!-- Create Direct Voucher Modal (Accountant Only) -->
@if($isAccountant)
@include('modules.finance.partials.direct-voucher-modal', ['glAccounts' => $glAccounts, 'cashBoxes' => $cashBoxes])
@endif

<!-- Approval Modals (HOD Only) -->
@if($isHOD)
@foreach($vouchers as $voucher)
@if($voucher->status == 'pending_hod')
<!-- Approve Modal -->
<div class="modal fade" id="approveModal{{ $voucher->id }}" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <form id="approveForm{{ $voucher->id }}" action="{{ route('petty-cash.hod.approve', $voucher) }}" method="POST">
        @csrf
        <input type="hidden" name="action" value="approve">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="bx bx-check-circle me-2"></i>Approve Direct Voucher
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-success mb-3">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Direct Voucher (Already Used):</strong> This voucher was already used in-office. Approval will mark it as <strong>Paid (Complete)</strong>. No CEO approval required.
          </div>
          <p class="mb-3">
            Are you sure you want to approve voucher <strong class="text-primary">{{ $voucher->voucher_no }}</strong>?
          </p>
          <div class="mb-3">
            <div class="card bg-light">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-6">
                    <small class="text-muted d-block">Amount</small>
                    <strong class="text-success">TZS {{ number_format($voucher->amount, 2) }}</strong>
                  </div>
                  <div class="col-6">
                    <small class="text-muted d-block">Created By</small>
                    <strong>{{ $voucher->creator->name ?? 'N/A' }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">
              <i class="bx bx-comment me-1"></i>Comments (Optional)
            </label>
            <textarea name="comments" class="form-control" rows="3" placeholder="Add approval comments..."></textarea>
            <small class="text-muted">Any additional notes about this approval</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-success" id="approveBtn{{ $voucher->id }}">
            <i class="bx bx-check-circle me-1"></i>Approve & Mark as Paid
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal{{ $voucher->id }}" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <form id="rejectForm{{ $voucher->id }}" action="{{ route('petty-cash.hod.approve', $voucher) }}" method="POST">
        @csrf
        <input type="hidden" name="action" value="reject">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title">
            <i class="bx bx-x-circle me-2"></i>Reject Direct Voucher
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning mb-3">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Warning:</strong> Rejecting this voucher will mark it as rejected and notify the accountant.
          </div>
          <p class="mb-3">
            Are you sure you want to reject voucher <strong class="text-danger">{{ $voucher->voucher_no }}</strong>?
          </p>
          <div class="mb-3">
            <div class="card bg-light">
              <div class="card-body p-3">
                <div class="row">
                  <div class="col-6">
                    <small class="text-muted d-block">Amount</small>
                    <strong class="text-danger">TZS {{ number_format($voucher->amount, 2) }}</strong>
                  </div>
                  <div class="col-6">
                    <small class="text-muted d-block">Created By</small>
                    <strong>{{ $voucher->creator->name ?? 'N/A' }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">
              Rejection Reason <span class="text-danger">*</span>
            </label>
            <textarea name="comments" class="form-control" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
            <small class="text-muted">This reason will be sent to the accountant who created this voucher</small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-danger" id="rejectBtn{{ $voucher->id }}">
            <i class="bx bx-x-circle me-1"></i>Reject Voucher
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endforeach
@endif
@endsection

@push('styles')
<style>
  .border-start-4 {
    border-left-width: 4px !important;
  }
  
  #directVoucherModal {
    z-index: 1055 !important;
  }
  
  #directVoucherModal .modal-backdrop {
    z-index: 1054 !important;
  }
  
  #directVoucherModal .modal-content {
    position: relative;
    z-index: 1055 !important;
    pointer-events: auto !important;
  }
  
  #directVoucherModal .modal-body {
    pointer-events: auto !important;
  }
  
  #directVoucherModal input,
  #directVoucherModal select,
  #directVoucherModal textarea {
    pointer-events: auto !important;
  }
  
  .file-upload-area {
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
  }
  
  .file-upload-area:hover {
    background: #e9ecef;
    border-color: #0d6efd !important;
  }
  
  .voucher-line-item {
    border: 1px solid #dee2e6;
    border-radius: 8px;
  }
</style>
@endpush

@push('scripts')
<script>
let lineCounter = 1;
let directVoucherFiles = [];

// Ensure modal is properly initialized and form fields are enabled
$(document).ready(function() {
    // Initialize Bootstrap 5 modal
    const modalElement = document.getElementById('directVoucherModal');
    let directVoucherModal = null;
    if (modalElement) {
        directVoucherModal = new bootstrap.Modal(modalElement, {
            backdrop: true,
            keyboard: true,
            focus: true
        });
    }
    
    // When modal is shown, ensure all form fields are enabled
    $('#directVoucherModal').on('shown.bs.modal', function() {
        // Remove any disabled attributes from form and all inputs
        const $modal = $(this);
        const $form = $modal.find('#directVoucherForm');
        
        // Enable the form itself
        $form.prop('disabled', false);
        
        // Remove any disabled attributes from all form elements
        $modal.find('input, select, textarea, button').each(function() {
            const $el = $(this);
            // Skip readonly line-total fields
            if (!$el.hasClass('line-total')) {
                $el.prop('disabled', false).removeAttr('disabled');
                $el.prop('readonly', false).removeAttr('readonly');
            }
        });
        
        // Ensure pointer events are enabled on all interactive elements
        $modal.find('.modal-content, .modal-body, input, select, textarea, button').css({
            'pointer-events': 'auto',
            'opacity': '1'
        });
        
        // Remove any overlay that might be blocking
        $('.modal-backdrop').css('z-index', '1054');
        $modal.css('z-index', '1055');
        
        // Calculate initial totals
        calculateGrandTotal();
        
        // Focus on first input after a short delay to ensure modal is fully rendered
        setTimeout(function() {
            $modal.find('input:not([readonly]):not(.line-total):first').focus();
        }, 100);
    });
    
    // When modal is hidden, reset form
    $('#directVoucherModal').on('hidden.bs.modal', function() {
        // Ensure form is reset
        $('#directVoucherForm')[0].reset();
        // Reset line counter
        lineCounter = 1;
        // Reset file preview
        directVoucherFiles = [];
        $('#directVoucherFilePlaceholder').show();
        $('#directVoucherFilePreview').addClass('d-none');
    });
});

// Calculate line total
function calculateLineTotal(lineItem) {
    const qty = parseFloat($(lineItem).find('.line-qty').val()) || 0;
    const unitPrice = parseFloat($(lineItem).find('.line-unit-price').val()) || 0;
    const total = qty * unitPrice;
    $(lineItem).find('.line-total').val(total.toFixed(2));
    calculateGrandTotal();
}

// Calculate grand total
function calculateGrandTotal() {
    let grandTotal = 0;
    $('.voucher-line-item').each(function() {
        const total = parseFloat($(this).find('.line-total').val()) || 0;
        grandTotal += total;
    });
    $('#directVoucherTotal').text('TZS ' + grandTotal.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }));
}

// Add voucher line
$('#addVoucherLine').on('click', function() {
    lineCounter++;
    const newLine = `
        <div class="voucher-line-item card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Line ${lineCounter}</h6>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-line">
                        <i class="bx bx-trash"></i> Remove
                    </button>
                </div>
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label fw-bold">
                            Description <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="line_description[]" required placeholder="Item description">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">
                            Qty <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control line-qty" name="line_qty[]" step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">
                            Unit Price <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">TZS</span>
                            <input type="number" class="form-control line-unit-price" name="line_unit_price[]" step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">Total</label>
                        <input type="text" class="form-control line-total" readonly placeholder="0.00">
                    </div>
                </div>
            </div>
        </div>
    `;
    $('#voucherLinesContainer').append(newLine);
});

// Remove voucher line
$(document).on('click', '.remove-line', function() {
    if ($('.voucher-line-item').length > 1) {
        $(this).closest('.voucher-line-item').remove();
        calculateGrandTotal();
    } else {
        alert('You must have at least one voucher line.');
    }
});

// Calculate totals on input change
$(document).on('input', '.line-qty, .line-unit-price', function() {
    calculateLineTotal($(this).closest('.voucher-line-item'));
});

// File upload area click - prevent recursion
$(document).on('click', '#directVoucherFileArea', function(e) {
    // Don't trigger if clicking on:
    // - The file input itself
    // - Any button (like remove-file buttons)
    // - The file preview/list area
    const target = $(e.target);
    if (target.is('input[type="file"]') || 
        target.is('button') || 
        target.closest('button').length ||
        target.closest('#directVoucherFilePreview').length ||
        target.closest('#directVoucherFileList').length) {
        return;
    }
    
    // Only trigger on the placeholder area
    if (target.closest('#directVoucherFilePlaceholder').length || target.is('#directVoucherFilePlaceholder')) {
        e.preventDefault();
        e.stopPropagation();
        
        // Trigger file input click using native DOM to avoid jQuery recursion
        const fileInput = document.getElementById('directVoucherAttachments');
        if (fileInput) {
            fileInput.click();
        }
    }
});

// Prevent file input click from bubbling up
$(document).on('click', '#directVoucherAttachments', function(e) {
    e.stopPropagation();
});

// Preview files
function previewDirectVoucherFiles(input) {
    const files = input.files;
    if (!files || files.length === 0) {
        directVoucherFiles = [];
        $('#directVoucherFilePlaceholder').show();
        $('#directVoucherFilePreview').addClass('d-none');
        return;
    }
    
    directVoucherFiles = Array.from(files);
    $('#directVoucherFilePlaceholder').hide();
    $('#directVoucherFilePreview').removeClass('d-none');
    
    let fileList = '<div class="list-group">';
    directVoucherFiles.forEach((file, index) => {
        const size = (file.size / 1024 / 1024).toFixed(2);
        fileList += `
            <div class="list-group-item d-flex justify-content-between align-items-center">
                <div>
                    <i class="bx bx-file me-2"></i>
                    <strong>${file.name}</strong>
                    <small class="text-muted ms-2">(${size} MB)</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-file" data-index="${index}">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        `;
    });
    fileList += '</div>';
    $('#directVoucherFileList').html(fileList);
}

// Remove file
$(document).on('click', '.remove-file', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const index = $(this).data('index');
    directVoucherFiles.splice(index, 1);
    
    const dt = new DataTransfer();
    directVoucherFiles.forEach(file => dt.items.add(file));
    const fileInput = document.getElementById('directVoucherAttachments');
    if (fileInput) {
        fileInput.files = dt.files;
        previewDirectVoucherFiles(fileInput);
    }
});

// Form submission - Use event delegation to ensure it works even if form is loaded dynamically
$(document).on('submit', '#directVoucherForm', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    const form = $(this)[0];
    const submitBtn = $('#submitDirectVoucherBtn');
    const originalText = submitBtn.html();
    
    // Basic form validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }
    
    // Validate that at least one voucher line has valid data
    let hasValidLine = false;
    $('.voucher-line-item').each(function() {
        const qty = parseFloat($(this).find('.line-qty').val()) || 0;
        const unitPrice = parseFloat($(this).find('.line-unit-price').val()) || 0;
        if (qty > 0 && unitPrice > 0) {
            hasValidLine = true;
            return false; // break loop
        }
    });
    
    if (!hasValidLine) {
        alert('Please add at least one voucher line with valid quantity and unit price.');
        return false;
    }
    
    // Disable submit button and show loading state
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Creating...');

    // Create FormData from form
    const formData = new FormData(form);
    
    // Ensure CSRF token is included in FormData (some Laravel configs require it)
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    if (csrfToken && !formData.has('_token')) {
        formData.append('_token', csrfToken);
    }
    
    // Log for debugging (remove in production)
    console.log('Submitting direct voucher form...');
    
    $.ajax({
        url: '{{ route("petty-cash.accountant.direct-voucher") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        cache: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            console.log('Response received:', response);
            if (response && response.success) {
                const alert = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 100002;">' +
                    '<strong>Success!</strong> ' + (response.message || 'Direct voucher created successfully.') +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>');
                $('body').append(alert);
                setTimeout(() => alert.fadeOut(() => alert.remove()), 4000);

                // Use Bootstrap 5 API to hide modal
                const modalElement = document.getElementById('directVoucherModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    } else {
                        $(modalElement).modal('hide');
                    }
                }
                setTimeout(() => location.reload(), 1500);
            } else {
                const errorMsg = (response && response.message) ? response.message : 'Failed to create voucher';
                alert('Error: ' + errorMsg);
                submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error, xhr);
            let errorMsg = 'Error creating voucher. Please try again.';
            
            if (xhr.status === 422) {
                // Validation errors
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = 'Validation errors: ' + errors.join(', ');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
            } else if (xhr.status === 403) {
                errorMsg = 'You do not have permission to create direct vouchers.';
            } else if (xhr.status === 500) {
                errorMsg = 'Server error. Please contact support if the problem persists.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                // Try to parse HTML response for error message
                const parser = new DOMParser();
                const doc = parser.parseFromString(xhr.responseText, 'text/html');
                const errorElement = doc.querySelector('.error, .alert-danger');
                if (errorElement) {
                    errorMsg = errorElement.textContent.trim();
                }
            }
            
            // Show error alert
            const alert = $('<div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 100002;">' +
                '<strong>Error!</strong> ' + errorMsg +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');
            $('body').append(alert);
            setTimeout(() => alert.fadeOut(() => alert.remove()), 5000);
            
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
    
    return false;
});

// Handle approval form submissions with AJAX for better UX
@if($isHOD)
@foreach($vouchers as $voucher)
@if($voucher->status == 'pending_hod')
$('#approveForm{{ $voucher->id }}').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitBtn = $('#approveBtn{{ $voucher->id }}');
    const originalText = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Approving...');
    
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            // Handle both JSON and regular responses
            const message = (response && typeof response === 'object' && response.message) ? response.message : 'Direct voucher approved and marked as Paid (complete).';
            const alertType = (response && response.success === false) ? 'danger' : 'success';
            const alertTitle = (response && response.success === false) ? 'Error!' : 'Success!';
            
            const alert = $('<div class="alert alert-' + alertType + ' alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 100002;">' +
                '<strong>' + alertTitle + '</strong> ' + message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');
            $('body').append(alert);
            setTimeout(() => alert.fadeOut(() => alert.remove()), 4000);
            
            // Close modal
            const modalElement = document.getElementById('approveModal{{ $voucher->id }}');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }
            
            // Reload page after short delay if successful
            if (!response || response.success !== false) {
                setTimeout(() => location.reload(), 1500);
            } else {
                submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Failed to approve voucher';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMsg = errors.join(', ');
            }
            
            const alert = $('<div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 100002;">' +
                '<strong>Error!</strong> ' + errorMsg +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');
            $('body').append(alert);
            setTimeout(() => alert.fadeOut(() => alert.remove()), 5000);
            
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});

$('#rejectForm{{ $voucher->id }}').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const submitBtn = $('#rejectBtn{{ $voucher->id }}');
    const originalText = submitBtn.html();
    
    // Validate comments
    const comments = form.find('textarea[name="comments"]').val().trim();
    if (!comments) {
        alert('Please provide a rejection reason.');
        return;
    }
    
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Rejecting...');
    
    $.ajax({
        url: form.attr('action'),
        method: 'POST',
        data: form.serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            const alert = $('<div class="alert alert-warning alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 100002;">' +
                '<strong>Voucher Rejected!</strong> The accountant has been notified.' +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');
            $('body').append(alert);
            setTimeout(() => alert.fadeOut(() => alert.remove()), 4000);
            
            // Close modal
            const modalElement = document.getElementById('rejectModal{{ $voucher->id }}');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
            }
            
            // Reload page after short delay
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            let errorMsg = 'Failed to reject voucher';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMsg = errors.join(', ');
            }
            
            const alert = $('<div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 100002;">' +
                '<strong>Error!</strong> ' + errorMsg +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>');
            $('body').append(alert);
            setTimeout(() => alert.fadeOut(() => alert.remove()), 5000);
            
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});
@endif
@endforeach
@endif

// Reset form when modal is closed
$('#directVoucherModal').on('hidden.bs.modal', function() {
    $('#directVoucherForm')[0].reset();
    $('#voucherLinesContainer').html(`
        <div class="voucher-line-item card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label class="form-label fw-bold">
                            Description <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" name="line_description[]" required placeholder="Item description">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">
                            Qty <span class="text-danger">*</span>
                        </label>
                        <input type="number" class="form-control line-qty" name="line_qty[]" step="0.01" min="0.01" required placeholder="0.00">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label fw-bold">
                            Unit Price <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">TZS</span>
                            <input type="number" class="form-control line-unit-price" name="line_unit_price[]" step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label class="form-label fw-bold">Total</label>
                        <input type="text" class="form-control line-total" readonly placeholder="0.00">
                    </div>
                </div>
            </div>
        </div>
    `);
    $('#directVoucherTotal').text('TZS 0.00');
    directVoucherFiles = [];
    $('#directVoucherFilePlaceholder').show();
    $('#directVoucherFilePreview').addClass('d-none');
    lineCounter = 1;
});
</script>
@endpush

@include('modules.finance.petty-cash-partials.modals')
@include('modules.finance.petty-cash-partials.scripts')

