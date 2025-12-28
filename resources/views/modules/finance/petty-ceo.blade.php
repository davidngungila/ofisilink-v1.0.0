@extends('layouts.app')

@section('title', 'CEO Petty Cash Approval - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="card-title mb-0"><i class="bx bx-money me-2"></i>CEO Petty Cash Approval</h4>
            <p class="text-muted mb-0">Review and approve petty cash requests</p>
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
<div class="row">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Petty Cash Requests</h5>
      </div>
      <div class="card-body">
        <!-- Advanced Tabs Navigation -->
        <ul class="nav nav-pills nav-fill mb-4" role="tablist" id="ceoTabs">
          <!-- All Requests Tab -->
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'all' ? 'active' : '' }}" 
               href="{{ route('petty-cash.ceo.index', ['type' => 'all', 'status' => 'all']) }}">
              <i class="bx bx-list-ul me-1"></i>
              <span class="d-none d-md-inline">All Requests</span>
              <span class="d-md-none">All</span>
              @php
                $allTotal = ($counts['pending_ceo'] ?? 0) + ($counts['approved'] ?? 0) + ($counts['paid'] ?? 0) + ($counts['rejected'] ?? 0) + ($counts['retired'] ?? 0) + ($counts['direct_pending_hod'] ?? 0) + ($counts['direct_paid'] ?? 0) + ($counts['direct_rejected'] ?? 0);
              @endphp
              @if($allTotal > 0)
                <span class="badge bg-info ms-2 rounded-pill">{{ $allTotal }}</span>
              @endif
            </a>
          </li>
          
          <!-- Regular Vouchers -->
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'pending_ceo' ? 'active' : '' }}" 
               href="{{ route('petty-cash.ceo.index', ['type' => 'regular', 'status' => 'pending_ceo']) }}">
              <i class="bx bx-time-five me-1"></i>
              <span class="d-none d-md-inline">Pending My Action</span>
              <span class="d-md-none">Pending</span>
              @if($counts['pending_ceo'] > 0)
                <span class="badge bg-danger ms-2 rounded-pill">{{ $counts['pending_ceo'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'approved' ? 'active' : '' }}" 
               href="{{ route('petty-cash.ceo.index', ['type' => 'regular', 'status' => 'approved']) }}">
              <i class="bx bx-check-circle me-1"></i>
              <span class="d-none d-md-inline">Approved for Payment</span>
              <span class="d-md-none">Approved</span>
              @if($counts['approved'] > 0)
                <span class="badge bg-success ms-2 rounded-pill">{{ $counts['approved'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'paid' ? 'active' : '' }}" 
               href="{{ route('petty-cash.ceo.index', ['type' => 'regular', 'status' => 'paid']) }}">
              <i class="bx bx-money me-1"></i>
              <span class="d-none d-md-inline">Paid</span>
              <span class="d-md-none">Paid</span>
              @if($counts['paid'] > 0)
                <span class="badge bg-primary ms-2 rounded-pill">{{ $counts['paid'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'rejected' ? 'active' : '' }}" 
               href="{{ route('petty-cash.ceo.index', ['type' => 'regular', 'status' => 'rejected']) }}">
              <i class="bx bx-x-circle me-1"></i>
              <span class="d-none d-md-inline">Rejected</span>
              <span class="d-md-none">Rejected</span>
              @if($counts['rejected'] > 0)
                <span class="badge bg-danger ms-2 rounded-pill">{{ $counts['rejected'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'retired' ? 'active' : '' }}" 
               href="{{ route('petty-cash.ceo.index', ['type' => 'regular', 'status' => 'retired']) }}">
              <i class="bx bx-archive me-1"></i>
              <span class="d-none d-md-inline">Retired</span>
              <span class="d-md-none">Retired</span>
              @if($counts['retired'] > 0)
                <span class="badge bg-secondary ms-2 rounded-pill">{{ $counts['retired'] }}</span>
              @endif
            </a>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
          @if($vouchers->count() > 0)
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Voucher No</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Amount</th>
                    <th>Purpose</th>
                    <th>HOD Comments</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($vouchers as $voucher)
                  <tr>
                    <td>
                      <span class="badge bg-primary">{{ $voucher->voucher_no }}</span>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-light rounded-circle me-2">
                          <span class="avatar-title text-primary">{{ substr($voucher->creator->name, 0, 1) }}</span>
                        </div>
                        <div>
                          <h6 class="mb-0">{{ $voucher->creator->name }}</h6>
                          <small class="text-muted">{{ $voucher->creator->employee_id ?? 'N/A' }}</small>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-info">{{ $voucher->creator->primaryDepartment->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                      <strong class="text-success">TZS {{ number_format($voucher->amount ?? 0, 2) }}</strong>
                    </td>
                    <td>
                      <div class="text-truncate" style="max-width: 200px;" title="{{ $voucher->purpose }}">
                        {{ $voucher->purpose }}
                      </div>
                    </td>
                    <td>
                      @if($voucher->hod_comments)
                        <div class="text-truncate" style="max-width: 150px;" title="{{ $voucher->hod_comments }}">
                          {{ $voucher->hod_comments }}
                        </div>
                      @else
                        <span class="text-muted">No comments</span>
                      @endif
                    </td>
                    <td>
                      <span class="badge bg-{{ $voucher->status_badge_class }}">{{ ucfirst(str_replace('_', ' ', $voucher->status)) }}</span>
                    </td>
                    <td>
                      <small class="text-muted">{{ $voucher->created_at->format('M d, Y H:i') }}</small>
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-info" onclick="viewDetails({{ $voucher->id }})" title="View Details">
                          <i class="bx bx-show"></i>
                        </button>
                        <a class="btn btn-sm btn-secondary" href="{{ route('petty-cash.pdf', $voucher->id) }}" target="_blank" title="View PDF">
                          <i class="bx bxs-file-pdf"></i>
                        </a>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('petty-cash.show', $voucher->id) }}" title="Full Details">
                          <i class="bx bx-detail"></i>
                        </a>
                        @if($voucher->status === 'pending_ceo')
                          <button type="button" class="btn btn-sm btn-success" onclick="approveRequest({{ $voucher->id }})" title="Approve">
                            <i class="bx bx-check"></i>
                          </button>
                          <button type="button" class="btn btn-sm btn-danger" onclick="rejectRequest({{ $voucher->id }})" title="Reject">
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
            
            <div class="d-flex justify-content-between align-items-center mt-3 px-3 py-2 border-top bg-light flex-wrap gap-2">
              <div class="text-muted">
                <small>Page <strong>{{ $vouchers->currentPage() }}</strong> of <strong>{{ $vouchers->lastPage() }}</strong></small>
              </div>
              <div>
                {{ $vouchers->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }}
              </div>
              <div class="text-muted">
                <small><strong>{{ $vouchers->total() }}</strong> total vouchers</small>
              </div>
            </div>
          @else
            <div class="text-center py-5">
              <div class="mb-3">
                <i class="bx bx-inbox text-muted" style="font-size: 4rem;"></i>
              </div>
              <h5 class="text-muted">No Requests Found</h5>
              <p class="text-muted">
                @if($status === 'pending_ceo')
                  No requests are pending your action.
                @else
                  No requests found for this status.
                @endif
              </p>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-success text-white sticky-top">
        <h5 class="modal-title text-white" id="approvalModalTitle">
          <i class="bx bx-check-circle me-2"></i>Approve Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="approvalForm" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="comments" class="form-label">Comments (Optional)</label>
            <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Add any comments about this approval..."></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-success btn-lg text-white" id="approvalSubmitBtn">
            <i class="bx bx-check-circle me-1"></i>Approve Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-danger text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-x-circle me-2"></i>Reject Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="rejectionForm" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="rejectionComments" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea class="form-control" id="rejectionComments" name="comments" rows="3" placeholder="Please provide a reason for rejecting this request..." required></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-danger btn-lg text-white">
            <i class="bx bx-x-circle me-1"></i>Reject Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-detail me-2"></i>Request Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detailsContent" style="max-height: calc(90vh - 150px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
      </div>
      <div class="modal-footer bg-light sticky-bottom">
        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('styles')
<style>
/* CEO Modals Anti-Stacking Styles */
#approvalModal, #rejectionModal, #detailsModal {
    z-index: 100000 !important;
    position: fixed !important;
}

#approvalModal.show, #rejectionModal.show, #detailsModal.show {
    z-index: 100000 !important;
    display: block !important;
    position: fixed !important;
}

/* Ensure only one backdrop exists */
body.modal-open .modal-backdrop {
    z-index: 99999 !important;
}

body.modal-open .modal-backdrop:not(:last-of-type) {
    display: none !important;
}

/* Ensure modal dialogs are on top */
#approvalModal .modal-dialog,
#rejectionModal .modal-dialog,
#detailsModal .modal-dialog {
    z-index: 100001 !important;
    position: relative;
}

/* Ensure modal content is clickable */
#approvalModal .modal-content,
#rejectionModal .modal-content,
#detailsModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
}

#approvalModal .modal-body,
#rejectionModal .modal-body,
#detailsModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#approvalModal .modal-body *,
#rejectionModal .modal-body *,
#detailsModal .modal-body * {
    pointer-events: auto !important;
    position: relative;
    z-index: 1;
}

#approvalModal .modal-body button,
#approvalModal .modal-body a,
#approvalModal .modal-body input,
#approvalModal .modal-body select,
#approvalModal .modal-body textarea,
#rejectionModal .modal-body button,
#rejectionModal .modal-body a,
#rejectionModal .modal-body input,
#rejectionModal .modal-body select,
#rejectionModal .modal-body textarea,
#detailsModal .modal-body button,
#detailsModal .modal-body a,
#detailsModal .modal-body input,
#detailsModal .modal-body select,
#detailsModal .modal-body textarea {
    pointer-events: auto !important;
    z-index: 10 !important;
    position: relative;
}

@media (max-width: 991.98px) {
    #approvalModal .modal-dialog,
    #rejectionModal .modal-dialog,
    #detailsModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #approvalModal .modal-body,
    #rejectionModal .modal-body,
    #detailsModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>
@endpush

@push('scripts')
<script>
let currentVoucherId = null;

// Enhanced modal show function to prevent stacking
function showModalSafely(modalId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) return;
    
    // Remove any existing backdrops first
    $('.modal-backdrop').remove();
    
    // Move modal to end of body
    $('body').append($(modalElement));
    
    // Hide any other open modals temporarily
    $('.modal.show').not(modalElement).each(function() {
        $(this).css('z-index', 9999);
    });
    
    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
    
    // Multiple setTimeout checks to ensure z-index is maintained
    setTimeout(function() {
        $(modalElement).css({
            'z-index': '100000',
            'position': 'fixed',
            'display': 'block'
        }).addClass('show');
        
        // Ensure only one backdrop exists
        $('.modal-backdrop').not(':last').remove();
        
        // Set backdrop z-index
        if ($('.modal-backdrop').length > 0) {
            $('.modal-backdrop').last().css('z-index', 99999);
        } else {
            $('body').append('<div class="modal-backdrop fade show"></div>');
            $('.modal-backdrop').last().css('z-index', 99999);
        }
        
        // Force modal dialog to front
        $(modalElement).find('.modal-dialog').css({
            'z-index': '100001',
            'position': 'relative'
        });
        
        // Ensure interactive elements are clickable
        $(modalElement).find('.modal-body *').css({
            'pointer-events': 'auto',
            'position': 'relative',
            'z-index': '1'
        });
    }, 50);
    
    setTimeout(function() {
        $(modalElement).css('z-index', '100000');
        $(modalElement).find('.modal-dialog').css('z-index', '100001');
        if ($('.modal-backdrop').length > 0) {
            $('.modal-backdrop').last().css('z-index', 99999);
        }
    }, 300);
    
    setTimeout(function() {
        $(modalElement).css('z-index', '100000');
        $(modalElement).find('.modal-dialog').css('z-index', '100001');
    }, 500);
}

function viewDetails(voucherId) {
    currentVoucherId = voucherId;
    
    $('#detailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>');
    showModalSafely('detailsModal');
    
    fetch(`/petty-cash/${voucherId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#detailsContent').html(data.html);
            } else {
                $('#detailsContent').html('<div class="alert alert-danger">Failed to load details: ' + data.message + '</div>');
            }
        })
        .catch(error => {
            $('#detailsContent').html('<div class="alert alert-danger">Error loading details: ' + error.message + '</div>');
        });
}

function approveRequest(voucherId) {
    currentVoucherId = voucherId;
    $('#approvalModalTitle').html('<i class="bx bx-check-circle me-2"></i>Approve Request');
    $('#approvalSubmitBtn').html('<i class="bx bx-check-circle me-1"></i>Approve Request').removeClass('btn-danger').addClass('btn-success');
    $('#approvalForm').attr('action', `/petty-cash/${voucherId}/ceo-approve`);
    if (!$('#approvalForm input[name="action"]').length) {
        $('#approvalForm').append('<input type="hidden" name="action" value="approve">');
    } else {
        $('#approvalForm input[name="action"]').val('approve');
    }
    showModalSafely('approvalModal');
}

function rejectRequest(voucherId) {
    currentVoucherId = voucherId;
    $('#rejectionForm').attr('action', `/petty-cash/${voucherId}/ceo-approve`);
    if (!$('#rejectionForm input[name="action"]').length) {
        $('#rejectionForm').append('<input type="hidden" name="action" value="reject">');
    } else {
        $('#rejectionForm input[name="action"]').val('reject');
    }
    showModalSafely('rejectionModal');
}

// Cleanup on modal close
$('#approvalModal').on('hidden.bs.modal', function() {
    $('#approvalForm')[0].reset();
    $('#approvalForm input[name="action"]').remove();
    // Clean up any extra backdrops
    $('.modal-backdrop').remove();
    // Remove body class if no other modals are open
    if ($('.modal.show').length === 0) {
        $('body').removeClass('modal-open');
        $('body').css('overflow', '');
        $('body').css('padding-right', '');
    }
});

$('#rejectionModal').on('hidden.bs.modal', function() {
    $('#rejectionForm')[0].reset();
    $('#rejectionForm input[name="action"]').remove();
    // Clean up any extra backdrops
    $('.modal-backdrop').remove();
    // Remove body class if no other modals are open
    if ($('.modal.show').length === 0) {
        $('body').removeClass('modal-open');
        $('body').css('overflow', '');
        $('body').css('padding-right', '');
    }
});

$('#detailsModal').on('hidden.bs.modal', function() {
    // Clean up any extra backdrops
    $('.modal-backdrop').remove();
    // Remove body class if no other modals are open
    if ($('.modal.show').length === 0) {
        $('body').removeClass('modal-open');
        $('body').css('overflow', '');
        $('body').css('padding-right', '');
    }
});
</script>

@push('styles')
<style>
.nav-pills .nav-link {
  border-radius: 0.5rem;
  transition: all 0.3s ease;
  font-weight: 500;
  padding: 0.75rem 1rem;
}

.nav-pills .nav-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.nav-pills .nav-link.active {
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.table-hover tbody tr {
  transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
  background-color: rgba(0,123,255,0.05);
  transform: scale(1.01);
}

@media (max-width: 768px) {
  .nav-pills {
    flex-wrap: wrap;
  }
  
  .nav-pills .nav-link {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
  }
}
</style>
@endpush
