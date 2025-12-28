<!-- New Imprest Request Modal -->
<div class="modal fade" id="newImprestModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-plus-circle me-2"></i>Create New Imprest Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="newImprestForm">
        @csrf
        <div class="modal-body" style="max-height: calc(90vh - 200px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
          <div class="mb-3">
            <label class="form-label fw-bold">
              Purpose <span class="text-danger">*</span>
            </label>
            <input type="text" class="form-control form-control-lg" name="purpose" id="purpose" required placeholder="e.g., Training, Field Work, Conference">
            <small class="text-muted">
              <i class="bx bx-info-circle"></i> Provide a clear purpose for this imprest request
            </small>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">
                Amount (TZS) <span class="text-danger">*</span>
              </label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">
                  <i class="bx bx-money text-primary"></i>
                </span>
                <input type="number" class="form-control" name="amount" id="amount" step="0.01" min="1" required placeholder="Enter amount">
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">
                Priority <span class="text-danger">*</span>
              </label>
              <select class="form-select form-select-lg" name="priority" id="priority" required>
                <option value="">Select Priority</option>
                <option value="normal" selected>Normal</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Expected Return Date</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-light">
                <i class="bx bx-calendar text-primary"></i>
              </span>
              <input type="date" class="form-control" name="expected_return_date" id="expected_return_date" min="{{ date('Y-m-d') }}">
            </div>
            <small class="text-muted">
              <i class="bx bx-info-circle"></i> Optional: When do you expect to return/receive this amount?
            </small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Description</label>
            <textarea class="form-control" name="description" id="description" rows="4" placeholder="Additional details about this imprest request..."></textarea>
            <small class="text-muted">
              <i class="bx bx-info-circle"></i> Provide any additional information that might be helpful
            </small>
          </div>
        </div>
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary btn-lg text-white" id="submitImprestBtn">
            <i class="bx bx-check-circle me-1"></i>Submit Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
#newImprestModal {
    z-index: 99999 !important;
}

#newImprestModal.show {
    z-index: 99999 !important;
    display: block !important;
}

#newImprestModal + .modal-backdrop,
body.modal-open .modal-backdrop:last-of-type {
    z-index: 99998 !important;
}

#newImprestModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#newImprestModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#newImprestModal .modal-header,
#newImprestModal .modal-footer {
    flex-shrink: 0;
}

#newImprestModal .form-control:focus,
#newImprestModal .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

@media (max-width: 991.98px) {
    #newImprestModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #newImprestModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>

<script>
// Reset form when modal is closed
document.addEventListener('DOMContentLoaded', function() {
  const modal = document.getElementById('newImprestModal');
  if (modal) {
    modal.addEventListener('hidden.bs.modal', function() {
      const form = document.getElementById('newImprestForm');
      if (form) {
        form.reset();
        const submitBtn = form.querySelector('#submitImprestBtn');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = '<i class="bx bx-check-circle me-1"></i>Submit Request';
        }
      }
    });
  }
});
</script>

