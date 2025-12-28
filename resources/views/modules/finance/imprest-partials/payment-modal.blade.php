<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-money me-2"></i>Process Payment
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="paymentForm">
        @csrf
        <input type="hidden" id="paymentImprestId" name="imprest_id">
        <div class="modal-body" style="max-height: calc(90vh - 200px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">
                Payment Method <span class="text-danger">*</span>
              </label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">
                  <i class="bx bx-credit-card text-primary"></i>
                </span>
                <select class="form-select" name="payment_method" id="payment_method" required>
                  <option value="">Select Method</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="mobile_money">Mobile Money</option>
                  <option value="cash">Cash</option>
                </select>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">
                Payment Date <span class="text-danger">*</span>
              </label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">
                  <i class="bx bx-calendar text-primary"></i>
                </span>
                <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
              </div>
            </div>
          </div>
          <div class="row" id="bankFields" style="display: none;">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">
                Bank Name <span class="text-danger">*</span>
              </label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">
                  <i class="bx bx-building text-primary"></i>
                </span>
                <input type="text" class="form-control" name="bank_name" placeholder="Enter bank name">
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">
                Account Number <span class="text-danger">*</span>
              </label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">
                  <i class="bx bx-hash text-primary"></i>
                </span>
                <input type="text" class="form-control" name="account_number" placeholder="Enter account number">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Payment Reference</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-light">
                <i class="bx bx-barcode text-primary"></i>
              </span>
              <input type="text" class="form-control" name="payment_reference" placeholder="Optional payment reference number">
            </div>
            <small class="text-muted">
              <i class="bx bx-info-circle"></i> Transaction reference or receipt number
            </small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Payment Notes</label>
            <textarea class="form-control" name="payment_notes" rows="3" placeholder="Additional notes about this payment..."></textarea>
            <small class="text-muted">
              <i class="bx bx-info-circle"></i> Any additional information about this payment
            </small>
          </div>
        </div>
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-success btn-lg text-white">
            <i class="bx bx-check-circle me-1"></i>Process Payment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
#paymentModal {
    z-index: 99999 !important;
}

#paymentModal.show {
    z-index: 99999 !important;
    display: block !important;
}

#paymentModal + .modal-backdrop,
body.modal-open .modal-backdrop:last-of-type {
    z-index: 99998 !important;
}

#paymentModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#paymentModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#paymentModal .modal-header,
#paymentModal .modal-footer {
    flex-shrink: 0;
}

#paymentModal .form-control:focus,
#paymentModal .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

@media (max-width: 991.98px) {
    #paymentModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #paymentModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.getElementById('payment_method');
    const bankFields = document.getElementById('bankFields');
    
    if (paymentMethod && bankFields) {
        paymentMethod.addEventListener('change', function() {
            if (this.value === 'bank_transfer') {
                bankFields.style.display = 'block';
                bankFields.querySelectorAll('input').forEach(i => i.required = true);
            } else {
                bankFields.style.display = 'none';
                bankFields.querySelectorAll('input').forEach(i => {
                    i.required = false;
                    i.value = '';
                });
            }
        });
    }
});
</script>

