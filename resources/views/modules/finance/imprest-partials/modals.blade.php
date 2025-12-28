<!-- Assign Staff Modal -->
@php
    $staffMembers = $staffMembers ?? collect();
    $staffWithUnretiredImprests = $staffWithUnretiredImprests ?? [];
@endphp
@include('modules.finance.imprest-partials.assign-modal', ['staffMembers' => $staffMembers, 'staffWithUnretiredImprests' => $staffWithUnretiredImprests])

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
          <i class="bx bx-info-circle me-2"></i>Imprest Request Details
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

