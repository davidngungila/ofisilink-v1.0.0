@extends('layouts.app')

@section('title', 'Imprest Management System - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0"> Imprest Management System</h4>
        <p class="text-muted">Complete workflow: Request → HOD Approval → CEO Approval → Staff Assignment → Payment → Receipt Submission → Verification → Completion</p>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')

@php
$isAccountant = auth()->user()->hasRole('Accountant') || auth()->user()->hasRole('System Admin');
$isHOD = auth()->user()->hasRole('HOD') || auth()->user()->hasRole('System Admin');
$isCEO = auth()->user()->hasRole('CEO') || auth()->user()->hasRole('Director') || auth()->user()->hasRole('System Admin');
$isStaff = auth()->user()->hasRole('Staff') || auth()->user()->hasRole('Employee');
@endphp

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-md-3">
    <div class="card bg-primary text-white">
      <div class="card-body">
        <h3 class="mb-0">{{ $stats['all'] ?? 0 }}</h3>
        <p class="mb-0">All Requests</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-warning text-white">
      <div class="card-body">
        <h3 class="mb-0">{{ $stats['pending_hod'] ?? 0 }}</h3>
        <p class="mb-0">Pending HOD</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-info text-white">
      <div class="card-body">
        <h3 class="mb-0">{{ $stats['pending_ceo'] ?? 0 }}</h3>
        <p class="mb-0">Pending CEO</p>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card bg-success text-white">
      <div class="card-body">
        <h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3>
        <p class="mb-0">Completed</p>
      </div>
    </div>
  </div>
</div>

<!-- Action Buttons -->
<div class="row mb-3">
  <div class="col-12">
    @if($isAccountant)
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newImprestModal">
      <i class="bx bx-plus-circle"></i> New Imprest Request
    </button>
    <a href="{{ route('imprest.export-pdf') }}" class="btn btn-info" target="_blank">
      <i class="bx bx-file-blank"></i> Export PDF Report
    </a>
    @endif
  </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-tabs mb-3" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" data-bs-toggle="tab" href="#tab-all" role="tab">
      All Requests <span class="badge bg-secondary ms-2">{{ $stats['all'] ?? 0 }}</span>
    </a>
  </li>
  @if($isHOD)
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-pending-hod" role="tab">
      Pending HOD <span class="badge bg-warning ms-2">{{ $stats['pending_hod'] ?? 0 }}</span>
    </a>
  </li>
  @endif
  @if($isCEO)
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-pending-ceo" role="tab">
      Pending CEO <span class="badge bg-info ms-2">{{ $stats['pending_ceo'] ?? 0 }}</span>
    </a>
  </li>
  @endif
  @if($isAccountant)
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-approved" role="tab">
      Approved (Assign Staff) <span class="badge bg-success ms-2">{{ $stats['approved'] ?? 0 }}</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-assigned" role="tab">
      Assigned (Payment) <span class="badge bg-primary ms-2">{{ $stats['assigned'] ?? 0 }}</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-paid" role="tab">
      Paid (Awaiting Receipts) <span class="badge bg-warning ms-2">{{ $stats['paid'] ?? 0 }}</span>
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-verification" role="tab">
      Pending Verification <span class="badge bg-info ms-2">{{ $stats['pending_receipt_verification'] ?? 0 }}</span>
    </a>
  </li>
  @endif
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-completed" role="tab">
      Completed <span class="badge bg-success ms-2">{{ $stats['completed'] ?? 0 }}</span>
    </a>
  </li>
  @if($isStaff)
  @php
    $myAssignmentsCount = \App\Models\ImprestAssignment::where('staff_id', auth()->id())
      ->whereHas('imprestRequest', function($q) {
        $q->where('status', 'paid');
      })
      ->where('receipt_submitted', false)
      ->count();
  @endphp
  <li class="nav-item">
    <a class="nav-link" data-bs-toggle="tab" href="#tab-my-assignments" role="tab">
      <i class="bx bx-clipboard"></i> My Assignments 
      @if($myAssignmentsCount > 0)
        <span class="badge bg-danger ms-2">{{ $myAssignmentsCount }} Pending</span>
      @else
        <span class="badge bg-secondary ms-2">0</span>
      @endif
    </a>
  </li>
  @endif
</ul>

<div class="tab-content">
  <!-- All Requests Tab -->
  <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
    @include('modules.finance.imprest-partials.table', [
      'requests' => $imprestRequests,
      'showActions' => true
    ])
  </div>

  <!-- Pending HOD Tab -->
  @if($isHOD)
  <div class="tab-pane fade" id="tab-pending-hod" role="tabpanel">
    @include('modules.finance.imprest-partials.table', [
      'requests' => $pendingHod ?? collect(),
      'showActions' => true,
      'actionType' => 'hod'
    ])
  </div>
  @endif

  <!-- Pending CEO Tab -->
  @if($isCEO)
  <div class="tab-pane fade" id="tab-pending-ceo" role="tabpanel">
    @include('modules.finance.imprest-partials.table', [
      'requests' => $pendingCEO ?? collect(),
      'showActions' => true,
      'actionType' => 'ceo'
    ])
  </div>
  @endif

  <!-- Approved (Assign Staff) Tab -->
  @if($isAccountant)
  <div class="tab-pane fade" id="tab-approved" role="tabpanel">
    @include('modules.finance.imprest-partials.table', [
      'requests' => $approved ?? collect(),
      'showActions' => true,
      'actionType' => 'assign'
    ])
  </div>

  <!-- Assigned (Payment) Tab -->
  <div class="tab-pane fade" id="tab-assigned" role="tabpanel">
    @include('modules.finance.imprest-partials.table', [
      'requests' => $assigned ?? collect(),
      'showActions' => true,
      'actionType' => 'payment'
    ])
  </div>

  <!-- Paid (Awaiting Receipts) Tab -->
  <div class="tab-pane fade" id="tab-paid" role="tabpanel">
    @include('modules.finance.imprest-partials.table', [
      'requests' => $paid ?? collect(),
      'showActions' => true,
      'actionType' => 'receipts'
    ])
  </div>

  <!-- Pending Verification Tab -->
  <div class="tab-pane fade" id="tab-verification" role="tabpanel">
    @include('modules.finance.imprest-partials.table', [
      'requests' => $pendingVerification ?? collect(),
      'showActions' => true,
      'actionType' => 'verify'
    ])
  </div>
  @endif

  <!-- Completed Tab -->
  <div class="tab-pane fade" id="tab-completed" role="tabpanel">
    @include('modules.finance.imprest-partials.table', [
      'requests' => $completed ?? collect(),
      'showActions' => false
    ])
  </div>

  <!-- My Assignments Tab (Staff) -->
  @if($isStaff)
  <div class="tab-pane fade" id="tab-my-assignments" role="tabpanel">
    @php
    $myAssignments = \App\Models\ImprestAssignment::where('staff_id', auth()->id())
      ->with([
        'imprestRequest.accountant', 
        'receipts.submittedBy',
        'receipts.verifiedBy'
      ])
      ->orderBy('created_at', 'desc')
      ->get();
    
    $pendingReceipts = $myAssignments->filter(function($a) {
      $hasReceipts = $a->receipts->count() > 0;
      $isSubmitted = $a->receipt_submitted || $hasReceipts;
      return $a->imprestRequest->status === 'paid' && !$isSubmitted;
    });
    
    $submittedReceipts = $myAssignments->filter(function($a) {
      return $a->receipt_submitted;
    });
    @endphp
    
    <!-- Information Alert -->
    @if($pendingReceipts->count() > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <h5 class="alert-heading"><i class="bx bx-info-circle"></i> Action Required</h5>
      <p class="mb-0">You have <strong>{{ $pendingReceipts->count() }}</strong> imprest assignment(s) that require receipt submission. Please submit your receipts as soon as possible after payment.</p>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bx bx-clipboard"></i> My Imprest Assignments</h5>
      </div>
      <div class="card-body">
        @if($myAssignments->count() > 0)
        
        <!-- Pending Receipt Submission Section -->
        @if($pendingReceipts->count() > 0)
        <div class="mb-4">
          <h6 class="text-danger mb-3"><i class="bx bx-exclamation-triangle"></i> Pending Receipt Submission ({{ $pendingReceipts->count() }})</h6>
          <div class="table-responsive">
            <table class="table table-hover table-bordered">
              <thead class="table-danger">
                <tr>
                  <th>Request #</th>
                  <th>Amount Assigned</th>
                  <th>Purpose</th>
                  <th>Assigned Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach($pendingReceipts as $assignment)
                <tr>
                  <td><strong class="text-primary">{{ $assignment->imprestRequest->request_no }}</strong></td>
                  <td><strong class="text-success">TZS {{ number_format($assignment->assigned_amount, 2) }}</strong></td>
                  <td>{{ Str::limit($assignment->imprestRequest->purpose, 50) }}</td>
                  <td>{{ $assignment->assigned_at ? $assignment->assigned_at->format('M d, Y') : '-' }}</td>
                  <td>
                    <span class="badge bg-success">Paid - Ready for Receipt</span>
                  </td>
                  <td>
                    <button class="btn btn-primary btn-sm" onclick="openSubmitReceipt({{ $assignment->id }})">
                      <i class="bx bx-upload"></i> Submit Receipt Now
                    </button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
        <hr>
        @endif
        
        <!-- All Assignments Section -->
        <h6 class="mb-3"><i class="bx bx-list-ul"></i> All My Assignments ({{ $myAssignments->count() }})</h6>
        <div class="table-responsive">
          <table class="table table-striped table-hover">
            <thead class="table-light">
              <tr>
                <th>Request #</th>
                <th>Amount Assigned</th>
                <th>Purpose</th>
                <th>Imprest Status</th>
                <th>Receipt Submitted</th>
                <th>Receipt Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($myAssignments as $assignment)
              @php
                $receipt = $assignment->receipts->first();
                $allReceipts = $assignment->receipts;
              @endphp
              @php
                $hasReceipts = $allReceipts->count() > 0;
                $isSubmitted = $assignment->receipt_submitted || $hasReceipts;
              @endphp
              <tr class="{{ $assignment->imprestRequest->status === 'paid' && !$isSubmitted ? 'table-warning' : '' }}">
                <td><strong>{{ $assignment->imprestRequest->request_no }}</strong></td>
                <td><strong>TZS {{ number_format($assignment->assigned_amount, 2) }}</strong></td>
                <td>{{ Str::limit($assignment->imprestRequest->purpose, 40) }}</td>
                <td>
                  <span class="badge bg-{{ $assignment->imprestRequest->status === 'paid' ? 'success' : ($assignment->imprestRequest->status === 'completed' ? 'info' : 'secondary') }}">
                    {{ ucwords(str_replace('_', ' ', $assignment->imprestRequest->status)) }}
                  </span>
                </td>
                <td>
                  @php
                    // Check both flag and if receipts actually exist
                    $hasReceipts = $allReceipts->count() > 0;
                    $isSubmitted = $assignment->receipt_submitted || $hasReceipts;
                  @endphp
                  @if($isSubmitted)
                    <span class="badge bg-success">
                      <i class="bx bx-check-circle"></i> Yes
                    </span>
                    @if($assignment->receipt_submitted_at)
                      <br><small class="text-muted">{{ $assignment->receipt_submitted_at->format('M d, Y') }}</small>
                    @elseif($hasReceipts && $allReceipts->first()->submitted_at)
                      <br><small class="text-muted">{{ $allReceipts->first()->submitted_at->format('M d, Y') }}</small>
                    @endif
                  @else
                    <span class="badge bg-warning">
                      <i class="bx bx-time"></i> No
                    </span>
                  @endif
                </td>
                <td>
                  @if($allReceipts->count() > 0)
                    @if($allReceipts->where('is_verified', true)->count() === $allReceipts->count())
                      <span class="badge bg-success">
                        <i class="bx bx-check-double"></i> All Verified
                      </span>
                    @elseif($allReceipts->where('is_verified', false)->count() > 0)
                      <span class="badge bg-warning">
                        <i class="bx bx-time-five"></i> Pending Verification
                      </span>
                    @else
                      <span class="badge bg-info">
                        <i class="bx bx-file"></i> {{ $allReceipts->count() }} Receipt(s)
                      </span>
                    @endif
                  @else
                    <span class="badge bg-secondary">—</span>
                  @endif
                </td>
                <td>
                  @php
                    $hasReceipts = $allReceipts->count() > 0;
                    $isSubmitted = $assignment->receipt_submitted || $hasReceipts;
                  @endphp
                  @if($assignment->imprestRequest->status === 'paid' && !$isSubmitted)
                    <button class="btn btn-sm btn-primary" onclick="openSubmitReceipt({{ $assignment->id }})" title="Submit your receipt for this assignment">
                      <i class="bx bx-upload"></i> Submit Receipt
                    </button>
                  @elseif($isSubmitted && $allReceipts->count() > 0)
                    <button class="btn btn-sm btn-info" onclick="viewReceiptDetails({{ $assignment->id }})" title="View receipt details">
                      <i class="bx bx-show"></i> View Receipts
                    </button>
                  @else
                    <span class="text-muted small">Waiting for payment</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="text-center py-5">
          <i class="bx bx-clipboard" style="font-size: 4rem; color: #d0d0d0;"></i>
          <h5 class="text-muted mt-3">No assignments found</h5>
          <p class="text-muted">You haven't been assigned to any imprest requests yet.</p>
        </div>
        @endif
      </div>
    </div>
  </div>
  @endif
</div>

<!-- New Imprest Request Modal -->
@include('modules.finance.imprest-partials.new-modal', ['staffMembers' => $staffMembers ?? collect()])

<!-- Assign Staff Modal -->
@include('modules.finance.imprest-partials.assign-modal', ['staffMembers' => $staffMembers ?? collect()])

<!-- Payment Modal -->
@include('modules.finance.imprest-partials.payment-modal')

<!-- Submit Receipt Modal -->
@include('modules.finance.imprest-partials.receipt-modal')

<!-- Verify Receipt Modal -->
@include('modules.finance.imprest-partials.verify-modal')

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-fullscreen-lg-down modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw; width: 95vw;">
    <div class="modal-content shadow-lg border-0" style="max-height: 95vh;">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-detail me-2"></i>Imprest Request Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="viewDetailsContent" style="max-height: calc(95vh - 150px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading details...</p>
        </div>
      </div>
      <div class="modal-footer sticky-bottom bg-light">
        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>

<style>
/* View Details Modal specific styles */
#viewDetailsModal {
    z-index: 99999 !important;
}

#viewDetailsModal.show {
    z-index: 99999 !important;
    display: block !important;
}

#viewDetailsModal + .modal-backdrop,
body.modal-open .modal-backdrop:last-of-type {
    z-index: 99998 !important;
}

#viewDetailsModal .modal-content {
    max-height: 95vh;
    display: flex;
    flex-direction: column;
    border-radius: 15px;
}

#viewDetailsModal .modal-body {
    overflow-y: auto;
    overflow-x: hidden;
    flex: 1 1 auto;
    -webkit-overflow-scrolling: touch;
}

#viewDetailsModal .modal-header,
#viewDetailsModal .modal-footer {
    flex-shrink: 0;
}

/* Ensure modal is scrollable and large */
#viewDetailsModal .modal-dialog {
    max-width: 95vw !important;
    width: 95vw !important;
    margin: 2.5vh auto;
}

#viewDetailsModal .modal-dialog-scrollable .modal-body {
    overflow-y: auto;
    overflow-x: hidden;
    max-height: calc(95vh - 150px) !important;
    padding: 1.5rem;
}

@media (max-width: 991.98px) {
    #viewDetailsModal .modal-dialog {
        max-width: 100vw !important;
        width: 100vw !important;
        margin: 0;
        height: 100vh;
    }
    
    #viewDetailsModal .modal-content {
        max-height: 100vh !important;
        height: 100vh;
        border-radius: 0;
    }
    
    #viewDetailsModal .modal-dialog-scrollable .modal-body {
        max-height: calc(100vh - 150px) !important;
    }
}
</style>

<script>
// Fix modal z-index and scrolling when shown
document.addEventListener('DOMContentLoaded', function() {
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    if (viewDetailsModal) {
        viewDetailsModal.addEventListener('show.bs.modal', function() {
            // Ensure proper z-index
            $(this).css('z-index', 9999);
            
            // Remove any duplicate backdrops
            $('.modal-backdrop').not(':last').remove();
            
            // Ensure body scrolling is handled
            $('body').addClass('modal-open');
        });
        
        viewDetailsModal.addEventListener('shown.bs.modal', function() {
            // Final z-index check
            $(this).css('z-index', 9999);
            $('.modal-backdrop').last().css('z-index', 9998);
            
            // Ensure modal is scrollable and large
            const modalBody = $(this).find('.modal-body');
            const modalDialog = $(this).find('.modal-dialog');
            if (modalBody.length) {
                modalBody.css({
                    'max-height': 'calc(95vh - 150px)',
                    'overflow-y': 'auto',
                    'overflow-x': 'hidden',
                    'padding': '1.5rem'
                });
            }
            if (modalDialog.length && window.innerWidth > 992) {
                modalDialog.css({
                    'max-width': '95vw',
                    'width': '95vw'
                });
            }
        });
        
        viewDetailsModal.addEventListener('hidden.bs.modal', function() {
            // Clean up - remove all backdrops
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({
                'overflow': '',
                'overflow-x': '',
                'overflow-y': '',
                'position': '',
                'width': '',
                'height': '',
                'padding-right': '',
                'top': '',
                'left': ''
            });
            $('html').css({
                'overflow': '',
                'overflow-x': '',
                'overflow-y': '',
                'position': '',
                'height': ''
            });
            $('#viewDetailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading...</p></div>');
        });
    }
});
</script>

<!-- Reference Details Section -->
@if(isset($glAccounts) && isset($cashBoxes))
<div class="container-fluid px-4 py-3">
    @include('components.reference-details', ['glAccounts' => $glAccounts, 'cashBoxes' => $cashBoxes])
</div>
@endif

@endsection

@push('styles')
<style>
/* Ensure all imprest modals appear in front of all content */
#newImprestModal,
#assignStaffModal,
#paymentModal,
#submitReceiptModal,
#viewDetailsModal {
    z-index: 99999 !important;
}

/* Verify Receipt Modal - HIGHEST PRIORITY */
#verifyReceiptModal {
    z-index: 100000 !important;
    position: fixed !important;
}

#newImprestModal.show,
#assignStaffModal.show,
#paymentModal.show,
#submitReceiptModal.show,
#viewDetailsModal.show {
    z-index: 99999 !important;
    display: block !important;
}

#verifyReceiptModal.show {
    z-index: 100000 !important;
    display: block !important;
    position: fixed !important;
}

/* Ensure backdrop is behind modal but above other content */
body.modal-open .modal-backdrop:last-of-type {
    z-index: 99998 !important;
    background-color: rgba(0, 0, 0, 0.5);
    opacity: 0.5;
}

/* Verify Receipt Modal backdrop - higher z-index */
body.modal-open #verifyReceiptModal + .modal-backdrop,
body.modal-open .modal-backdrop:has(+ #verifyReceiptModal) {
    z-index: 99999 !important;
}

/* Ensure all modal content is properly styled */
#newImprestModal .modal-content,
#assignStaffModal .modal-content,
#paymentModal .modal-content,
#submitReceiptModal .modal-content,
#verifyReceiptModal .modal-content,
#viewDetailsModal .modal-content {
    border-radius: 15px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Ensure modal bodies are scrollable */
.modal-dialog-scrollable .modal-body {
    overflow-y: auto;
    overflow-x: hidden;
    -webkit-overflow-scrolling: touch;
}

/* Fix body scroll when modal is open - allow modal body to scroll instead */
body.modal-open {
    overflow: hidden !important;
    padding-right: 0 !important;
    position: relative !important; /* Changed from fixed to relative */
}

/* Ensure body is scrollable when no modal is open */
body:not(.modal-open) {
    overflow: visible !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    position: static !important;
    height: auto !important;
    width: 100% !important;
}

/* Ensure SweetAlert2 appears above modals when needed */
.swal2-container {
    z-index: 100000 !important;
}

.swal2-popup {
    z-index: 100001 !important;
}

/* Ensure toast notifications appear above everything */
.alert.position-fixed {
    z-index: 100002 !important;
}

/* Ensure all action buttons are accessible */
.modal-footer button,
.modal-header button {
    position: relative;
    z-index: 1;
}

/* Prevent body scroll lock issues */
#viewDetailsModal {
    pointer-events: auto !important;
}

#viewDetailsModal .modal-content {
    pointer-events: auto !important;
}

/* Ensure page is scrollable when no modal is open - REMOVED DUPLICATE */

/* Clean up any stuck backdrops */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1040;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
}

/* Remove duplicate backdrops */
.modal-backdrop ~ .modal-backdrop {
    display: none !important;
}

/* Ensure primary buttons have white text */
.btn-primary,
.btn-success,
.btn-danger {
    color: white !important;
}

.btn-primary:hover,
.btn-success:hover,
.btn-danger:hover {
    color: white !important;
}

/* Enhance form controls focus states */
.modal .form-control:focus,
.modal .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Responsive modal adjustments */
@media (max-width: 991.98px) {
    .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
@include('modules.finance.imprest-partials.scripts')
<script>
// Global cleanup function to fix stuck modals/backdrops
window.cleanupStuckModals = function() {
    // Remove all backdrops if no modals are showing
    if ($('.modal.show').length === 0) {
        $('.modal-backdrop').remove();
        // Force remove modal-open class and restore body styles
        $('body').removeClass('modal-open').css({
            'overflow': '',
            'overflow-x': '',
            'overflow-y': '',
            'position': '',
            'width': '',
            'height': '',
            'padding-right': '',
            'top': '',
            'left': ''
        });
        // Also ensure html element is not locked
        $('html').css({
            'overflow': '',
            'overflow-x': '',
            'overflow-y': '',
            'position': '',
            'height': ''
        });
    } else {
        // Even if modals are open, remove duplicate backdrops
        const backdrops = $('.modal-backdrop');
        if (backdrops.length > 1) {
            backdrops.slice(0, -1).remove();
        }
    }
};

// Run cleanup on page load
$(document).ready(function() {
    // Clean up any stuck modals/backdrops from previous page loads
    window.cleanupStuckModals();
    
    // Also run cleanup after a short delay to catch any async issues
    setTimeout(window.cleanupStuckModals, 500);
    
    // Run cleanup periodically to catch any stuck states
    setInterval(window.cleanupStuckModals, 2000);
    
    // Clean up on page unload
    $(window).on('beforeunload', function() {
        window.cleanupStuckModals();
    });
    
    // Ensure all imprest modals have proper z-index when shown
    const imprestModalIds = [
        'newImprestModal',
        'assignStaffModal',
        'paymentModal',
        'submitReceiptModal',
        'viewDetailsModal'
    ];
    
    // When any imprest modal is shown (except verifyReceiptModal which has special handling)
    imprestModalIds.forEach(function(modalId) {
        const $modal = $('#' + modalId);
        if ($modal.length) {
            $modal.on('show.bs.modal', function() {
                // If verifyReceiptModal is open, lower this modal's z-index
                if ($('#verifyReceiptModal').hasClass('show')) {
                    $(this).css('z-index', 9999);
                } else {
                    $(this).css('z-index', 99999);
                }
                
                // Remove duplicate backdrops
                $('.modal-backdrop').not(':last').remove();
                
                // Ensure backdrop is behind modal
                setTimeout(function() {
                    $('.modal-backdrop').last().css('z-index', 99998);
                }, 10);
            });
            
            $modal.on('shown.bs.modal', function() {
                // Final z-index check
                if ($('#verifyReceiptModal').hasClass('show')) {
                    $(this).css('z-index', 9999);
                } else {
                    $(this).css('z-index', 99999);
                }
                $('.modal-backdrop').last().css('z-index', 99998);
                
                // Ensure modal body is scrollable
                const $modalBody = $(this).find('.modal-body');
                if ($modalBody.length) {
                    $modalBody.css({
                        'overflow-y': 'auto',
                        'overflow-x': 'hidden'
                    });
                }
            });
            
            $modal.on('hidden.bs.modal', function() {
                // Clean up - remove all backdrops and restore body
                setTimeout(function() {
                    // Remove duplicate backdrops
                    const backdrops = $('.modal-backdrop');
                    if (backdrops.length > 1) {
                        backdrops.slice(0, -1).remove();
                    }
                    
                    // Only remove modal-open if no other modals are open
                    if ($('.modal.show').length === 0) {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css({
                            'overflow': '',
                            'overflow-x': '',
                            'overflow-y': '',
                            'position': '',
                            'width': '',
                            'height': '',
                            'padding-right': '',
                            'top': '',
                            'left': ''
                        });
                        $('html').css({
                            'overflow': '',
                            'overflow-x': '',
                            'overflow-y': '',
                            'position': '',
                            'height': ''
                        });
                    }
                }, 100);
            });
        }
    });
    
    // Special handling for verifyReceiptModal - HIGHEST PRIORITY
    const $verifyModal = $('#verifyReceiptModal');
    if ($verifyModal.length) {
        // Move to end of body on page load
        $('body').append($verifyModal);
        
        $verifyModal.on('show.bs.modal', function() {
            // Lower z-index of all other modals
            $('.modal.show').not(this).each(function() {
                $(this).css('z-index', 9999);
            });
            
            // Set highest z-index for verify modal
            $(this).css({
                'z-index': '100000',
                'position': 'fixed'
            });
            
            // Remove duplicate backdrops
            $('.modal-backdrop').not(':last').remove();
            
            // Ensure backdrop is behind modal
            setTimeout(function() {
                if ($('.modal-backdrop').length === 0) {
                    $('body').append('<div class="modal-backdrop fade show" style="z-index: 99999;"></div>');
                } else {
                    $('.modal-backdrop').last().css('z-index', 99999);
                }
            }, 10);
        });
        
        $verifyModal.on('shown.bs.modal', function() {
            // Force to front
            $(this).css({
                'z-index': '100000',
                'position': 'fixed',
                'display': 'block'
            });
            
            // Ensure modal dialog is also high
            $(this).find('.modal-dialog').css('z-index', '100001');
            
            // Ensure backdrop
            $('.modal-backdrop').last().css('z-index', 99999);
            
            // Move to end of body to ensure it's on top
            $('body').append($(this));
        });
        
        $verifyModal.on('hidden.bs.modal', function() {
            // Clean up
            $('.modal-backdrop').remove();
        });
    }
});
</script>
@endpush


