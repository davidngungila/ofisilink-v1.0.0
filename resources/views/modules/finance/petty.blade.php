@extends('layouts.app')

@section('title', 'Petty Cash Management - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="card-title mb-0">My Petty Cash Requests</h4>
            <p class="text-muted mb-0">Create and manage your petty cash requests</p>
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
<!-- Success/Error Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;" role="alert">
  <strong>Success!</strong> {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;" role="alert">
  <strong>Error!</strong> {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Validation Errors:</strong>
  <ul class="mb-0">
    @foreach($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-start border-start-4 border-start-primary shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Requests</div>
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
        <div class="card border-start border-start-4 border-start-warning shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Approval</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ ($stats['pending_accountant'] ?? 0) + ($stats['pending_hod'] ?? 0) + ($stats['pending_ceo'] ?? 0) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-time text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
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
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Paid</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['paid'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-check-circle text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
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
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Retired</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['retired'] ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="bx bx-file-blank text-info" style="font-size: 2.5rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
  <!-- Create Request Section -->
  <div class="col-lg-12 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-0">My Petty Cash Requests</h5>
          <p class="text-muted mb-0">Track and manage your petty cash requests</p>
        </div>
        <div>
          @if($hasActiveRequest)
          <button type="button" class="btn btn-primary" disabled 
                  title="You have an active request. Please wait until it is retired or rejected.">
            <i class="bx bx-plus"></i> New Request
          </button>
          <small class="d-block text-danger mt-1">You have an active request in progress</small>
          @else
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRequestModal">
            <i class="bx bx-plus"></i> New Request
          </button>
          @endif
        </div>
      </div>
      <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="{{ route('petty-cash.my-requests') }}" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Status Filter</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Requests</option>
                    <option value="pending_accountant" {{ ($status ?? 'all') === 'pending_accountant' ? 'selected' : '' }}>Pending Accountant</option>
                    <option value="pending_hod" {{ ($status ?? 'all') === 'pending_hod' ? 'selected' : '' }}>Pending HOD</option>
                    <option value="pending_ceo" {{ ($status ?? 'all') === 'pending_ceo' ? 'selected' : '' }}>Pending CEO</option>
                    <option value="approved_for_payment" {{ ($status ?? 'all') === 'approved_for_payment' ? 'selected' : '' }}>Approved for Payment</option>
                    <option value="paid" {{ ($status ?? 'all') === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending_retirement_review" {{ ($status ?? 'all') === 'pending_retirement_review' ? 'selected' : '' }}>Pending Retirement</option>
                    <option value="retired" {{ ($status ?? 'all') === 'retired' ? 'selected' : '' }}>Retired</option>
                </select>
            </div>
            <div class="col-md-7">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Search by voucher number, purpose, or payee..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-search me-1"></i>Search
                </button>
            </div>
            @if(request()->hasAny(['status', 'search']))
            <div class="col-12">
                <a href="{{ route('petty-cash.my-requests') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-x me-1"></i>Clear Filters
                </a>
            </div>
            @endif
        </form>
        @if($vouchers->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Voucher No.</th>
                <th>Date</th>
                <th>Purpose</th>
                <th>Amount (TZS)</th>
                <th>Status</th>
                <th>Progress</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($vouchers as $voucher)
              <tr>
                <td><strong>{{ $voucher->voucher_no }}</strong></td>
                <td>{{ $voucher->date->format('M d, Y') }}</td>
                <td>{{ Str::limit($voucher->purpose, 50) }}</td>
                <td class="text-end">{{ number_format($voucher->amount, 2) }}</td>
                <td class="text-center">
                  <span class="badge bg-{{ $voucher->status_badge_class }}">
                    {{ ucwords(str_replace('_', ' ', $voucher->status)) }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="progress" style="height: 8px; width: 60px;">
                    <div class="progress-bar bg-{{ $voucher->progress_percentage == 100 ? 'success' : ($voucher->progress_percentage > 0 ? 'info' : 'danger') }}" 
                         style="width: {{ $voucher->progress_percentage }}%"></div>
                  </div>
                  <small class="text-muted">{{ $voucher->progress_percentage }}%</small>
                </td>
                <td class="text-center">
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-outline-info view-details-btn" 
                            data-id="{{ $voucher->id }}" title="Quick View">
                      <i class="bx bx-show"></i>
                    </button>
                    <a href="{{ route('petty-cash.show', $voucher) }}" 
                       class="btn btn-sm btn-outline-primary" title="View Details">
                      <i class="bx bx-file"></i>
                    </a>
                    @if($voucher->status === 'paid' && $voucher->created_by === auth()->id())
                    <button type="button" class="btn btn-sm btn-success btn-open-retire" 
                            data-id="{{ $voucher->id }}" data-voucher="{{ $voucher->voucher_no }}" title="Submit Retirement">
                      <i class="bx bx-receipt"></i>
                    </button>
                    @endif
                    @if($voucher->canBeDeleted())
                    <form action="{{ route('petty-cash.destroy', $voucher) }}" method="POST" 
                          style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this request?');">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bx bx-trash"></i>
                      </button>
                    </form>
                    @endif
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        
        <div class="d-flex justify-content-center mt-3">
          {{ $vouchers->links() }}
        </div>
        @else
        <div class="text-center p-4">
          <i class="bx bx-folder-plus" style="font-size: 48px; color: #ccc;"></i>
          <h4>No Requests Found</h4>
          <p>You haven't created any petty cash requests yet.</p>
          @if(!$hasActiveRequest)
          <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createRequestModal">
            <i class="bx bx-plus"></i> Create Your First Request
          </button>
          @endif
        </div>
        @endif
      </div>
    </div>
  </div>
  
  <!-- Reference Details Section -->
  @if(isset($glAccounts) && isset($cashBoxes))
  @include('components.reference-details', ['glAccounts' => $glAccounts, 'cashBoxes' => $cashBoxes])
  @endif
</div>

<!-- Create Request Modal -->
<div class="modal fade" id="createRequestModal" tabindex="-1" aria-labelledby="createRequestModalLabel">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="createPettyCashForm" action="{{ route('petty-cash.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="createRequestModalLabel">Create New Petty Cash Request</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Date <span class="text-danger">*</span></label>
              <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Payee <span class="text-danger">*</span></label>
              <input type="text" name="payee" class="form-control" value="{{ auth()->user()->name }}" readonly>
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label">Purpose <span class="text-danger">*</span></label>
              <textarea name="purpose" class="form-control" rows="2" required placeholder="Brief description of the request"></textarea>
            </div>
          </div>
          
          <h5 class="mt-4 mb-3 text-primary"><i class="bx bx-list-ul"></i> Expense Details</h5>
          <div class="table-responsive">
            <table class="table table-bordered table-sm" id="expense-lines-table">
              <thead class="table-light">
                <tr>
                  <th>Description <span class="text-danger">*</span></th>
                  <th style="width: 18%;">Qty</th>
                  <th style="width: 18%;">Unit Price</th>
                  <th style="width: 15%;">Total</th>
                  <th style="width: 5%;"></th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><input type="text" name="line_description[]" class="form-control form-control-sm" required placeholder="Item description"></td>
                  <td><input type="number" name="line_qty[]" class="form-control form-control-sm line-qty" step="0.01" value="1" required></td>
                  <td><input type="number" name="line_unit_price[]" class="form-control form-control-sm line-unit-price" step="0.01" value="0.00" required></td>
                  <td><input type="text" class="form-control form-control-sm line-total" readonly></td>
                  <td><button type="button" class="btn btn-danger btn-sm remove-line"><i class="bx bx-minus"></i></button></td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                  <td><input type="text" id="grand-total" class="form-control form-control-sm" readonly></td>
                  <td><button type="button" class="btn btn-primary btn-sm add-line"><i class="bx bx-plus"></i></button></td>
                </tr>
              </tfoot>
            </table>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Supporting Documents (Optional)</label>
            <input type="file" name="attachments[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png">
            <div class="form-text">You can attach multiple receipts (PDF/JPG/PNG). Maximum 10MB per file.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-check"></i> Submit Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('modals')
<!-- Retirement Modal -->
<div class="modal fade" id="retirementModal" tabindex="-1" aria-labelledby="retirementModalLabel">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="retirementModalLabel">Submit Retirement for <span id="retireVoucherNo"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="retirementForm" method="POST" action="{{ route('petty-cash.retirement.submit', ':id') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Receipts (PDF/JPG/PNG) *</label>
            <input type="file" name="retirement_receipts[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png" multiple required>
            <div class="form-text">Upload one or more receipt files.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea name="retirement_comments" class="form-control" rows="3" placeholder="Optional notes for accountant"></textarea>
          </div>
          <div class="text-danger small" id="retireError"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit Retirement</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const retireModalEl = document.getElementById('retirementModal');
  if (!retireModalEl) return;
  const retireModal = new bootstrap.Modal(retireModalEl);
  let retireId = null;

  document.querySelectorAll('.btn-open-retire').forEach(function(btn){
    btn.addEventListener('click', function(){
      retireId = this.getAttribute('data-id');
      const vno = this.getAttribute('data-voucher') || '';
      const label = document.getElementById('retireVoucherNo');
      if (label) label.textContent = vno;
      const form = document.getElementById('retirementForm');
      if (form && retireId) {
        const base = form.getAttribute('action');
        if (base && base.includes(':id')) form.setAttribute('action', base.replace(':id', retireId));
      }
      const err = document.getElementById('retireError');
      if (err) err.textContent = '';
      retireModal.show();
    });
  });

  const form = document.getElementById('retirementForm');
  if (form) {
    form.addEventListener('submit', function(){
      if (!retireId) return false;
    });
  }
});
</script>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate totals
    function calculateGrandTotal() {
        let total = 0;
        $('#expense-lines-table tbody tr').each(function() {
            const qty = parseFloat($(this).find('.line-qty').val()) || 0;
            const price = parseFloat($(this).find('.line-unit-price').val()) || 0;
            $(this).find('.line-total').val((qty * price).toFixed(2));
            total += qty * price;
        });
        $('#grand-total').val(total.toFixed(2));
    }

    // Add line
    $('.add-line').on('click', function() {
        const newRow = `
            <tr>
                <td><input type="text" name="line_description[]" class="form-control form-control-sm" required placeholder="Item description"></td>
                <td><input type="number" name="line_qty[]" class="form-control form-control-sm line-qty" step="0.01" value="1" required></td>
                <td><input type="number" name="line_unit_price[]" class="form-control form-control-sm line-unit-price" step="0.01" value="0.00" required></td>
                <td><input type="text" class="form-control form-control-sm line-total" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-line"><i class="bx bx-minus"></i></button></td>
            </tr>
        `;
        $('#expense-lines-table tbody').append(newRow);
    });

    // Remove line
    $(document).on('click', '.remove-line', function() {
        if ($('#expense-lines-table tbody tr').length > 1) {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        } else {
            alert("You must have at least one expense line.");
        }
    });

    // Calculate on input change
    $(document).on('input', '.line-qty, .line-unit-price', calculateGrandTotal);

    // Initial calculation
    calculateGrandTotal();

    // Handle form submission
    $('#createPettyCashForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');
        
        // Create FormData to handle file uploads
        const formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Show success message
                const toast = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;" role="alert">' +
                    '<strong>Success!</strong> Petty cash request submitted successfully.' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>');
                $('body').append(toast);
                
                // Hide modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('createRequestModal'));
                if (modal) modal.hide();
                
                // Reload page after short delay
                setTimeout(function() {
                    window.location.reload();
                }, 1500);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                
                let errorMessage = 'Failed to submit request.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        // Validation errors
                        let errorList = '<ul class="mb-0">';
                        Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                            xhr.responseJSON.errors[key].forEach(function(msg) {
                                errorList += '<li>' + msg + '</li>';
                            });
                        });
                        errorList += '</ul>';
                        errorMessage = '<strong>Validation Errors:</strong>' + errorList;
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) errorMessage = response.message;
                    } catch (e) {
                        errorMessage = 'An error occurred. Please try again.';
                    }
                }
                
                // Show error toast
                const toast = $('<div class="alert alert-danger alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;" role="alert">' +
                    '<strong>Error!</strong> ' + errorMessage +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>');
                $('body').append(toast);
                
                // Auto remove toast after 5 seconds
                setTimeout(function() {
                    toast.alert('close');
                }, 5000);
            }
        });
    });

    // Reset form when modal is closed
    $('#createRequestModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        $('#expense-lines-table tbody').empty();
        $('#expense-lines-table tbody').append(`
            <tr>
                <td><input type="text" name="line_description[]" class="form-control form-control-sm" required placeholder="Item description"></td>
                <td><input type="number" name="line_qty[]" class="form-control form-control-sm line-qty" step="0.01" value="1" required></td>
                <td><input type="number" name="line_unit_price[]" class="form-control form-control-sm line-unit-price" step="0.01" value="0.00" required></td>
                <td><input type="text" class="form-control form-control-sm line-total" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-line"><i class="bx bx-minus"></i></button></td>
            </tr>
        `);
        calculateGrandTotal();
    });

    // Quick view functionality - use the viewDetails function from scripts partial
    $('.view-details-btn').on('click', function() {
        const voucherId = $(this).data('id');
        if (typeof viewDetails === 'function') {
            viewDetails(voucherId);
        } else {
            // Fallback if viewDetails is not available
            const modal = new bootstrap.Modal(document.getElementById('viewDetailsModal'));
            const content = document.getElementById('viewDetailsContent');
            
            content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>';
            modal.show();
            
            fetch(`/petty-cash/${voucherId}/details-ajax`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error(`Network error: ${response.statusText}`);
                return response.json();
            })
            .then(data => {
                if (data.success && data.html) {
                    content.innerHTML = data.html;
                } else {
                    content.innerHTML = `<div class="alert alert-danger text-center">${data.message || 'Error loading details. Please try again.'}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                content.innerHTML = '<div class="alert alert-danger text-center">Error loading details. Please try again.</div>';
            });
        }
    });

    function escapeHtml(text) {
        if (text === null || typeof text === 'undefined') return '';
        var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
        return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
</script>
@endpush

@include('modules.finance.petty-cash-partials.modals')
@include('modules.finance.petty-cash-partials.scripts')