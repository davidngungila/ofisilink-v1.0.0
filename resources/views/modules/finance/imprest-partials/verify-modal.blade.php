<!-- Verify Receipt Modal -->
<div class="modal fade" id="verifyReceiptModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-check-circle me-2"></i>Verify Receipt
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="verifyReceiptForm">
        @csrf
        <input type="hidden" name="receipt_id" id="verifyReceiptId">
        <div class="modal-body" id="verifyReceiptContent" style="max-height: calc(90vh - 200px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading receipt details...</p>
          </div>
        </div>
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="button" class="btn btn-danger btn-lg text-white" onclick="verifyReceiptAction('reject')">
            <i class="bx bx-x-circle me-1"></i>Reject
          </button>
          <button type="button" class="btn btn-success btn-lg text-white" onclick="verifyReceiptAction('approve')">
            <i class="bx bx-check me-1"></i>Approve
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* Verify Receipt Modal - HIGHEST PRIORITY */
#verifyReceiptModal {
    z-index: 100000 !important;
    position: fixed !important;
}

#verifyReceiptModal.show {
    z-index: 100000 !important;
    display: block !important;
    position: fixed !important;
}

#verifyReceiptModal .modal-dialog {
    z-index: 100001 !important;
    position: relative !important;
}

#verifyReceiptModal + .modal-backdrop,
body.modal-open .modal-backdrop:last-of-type {
    z-index: 99999 !important;
}

#verifyReceiptModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#verifyReceiptModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#verifyReceiptModal .modal-header,
#verifyReceiptModal .modal-footer {
    flex-shrink: 0;
}

#verifyReceiptModal .form-control:focus,
#verifyReceiptModal .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

@media (max-width: 991.98px) {
    #verifyReceiptModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #verifyReceiptModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>

<script>
// Fix z-index when modal is shown - FORCE TO FRONT
document.addEventListener('DOMContentLoaded', function() {
    const verifyModal = document.getElementById('verifyReceiptModal');
    if (verifyModal) {
        // Move modal to end of body when page loads to ensure it can be on top
        $('body').append($(verifyModal));
        
        verifyModal.addEventListener('show.bs.modal', function() {
            // Hide or lower z-index of other modals
            $('.modal.show').not(this).each(function() {
                $(this).css('z-index', 9999);
            });
            
            // Remove all other backdrops
            $('.modal-backdrop').remove();
            
            // Set highest z-index
            $(this).css({
                'z-index': '100000',
                'position': 'fixed',
                'display': 'block'
            });
            
            // Create backdrop with proper z-index
            setTimeout(function() {
                if ($('.modal-backdrop').length === 0) {
                    $('body').append('<div class="modal-backdrop fade show" style="z-index: 99999;"></div>');
                }
            }, 10);
            
            // Ensure body has proper class
            $('body').addClass('modal-open');
        });
        
        verifyModal.addEventListener('shown.bs.modal', function() {
            // Final check to ensure z-index is correct and modal is on top
            $(this).css({
                'z-index': '100000',
                'position': 'fixed',
                'display': 'block'
            });
            
            // Ensure modal dialog is also high
            $(this).find('.modal-dialog').css('z-index', '100001');
            
            // Ensure backdrop is correct
            $('.modal-backdrop').last().css('z-index', 99999);
            
            // Force modal to front by appending to body again
            $('body').append($(this));
        });
        
        verifyModal.addEventListener('hidden.bs.modal', function() {
            // Clean up backdrops
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
        });
    }
});
</script>

