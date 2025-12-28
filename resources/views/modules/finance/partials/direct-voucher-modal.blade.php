<!-- Direct Voucher Modal -->
<div class="modal fade" id="directVoucherModal" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-height: 95vh;">
    <div class="modal-content shadow-lg border-0" style="max-height: 95vh; display: flex; flex-direction: column;">
      <div class="modal-header bg-primary text-white" style="flex-shrink: 0;">
        <h5 class="modal-title text-white">
          <i class="bx bx-plus-circle me-2"></i>Create Direct Voucher (In-Office)
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="directVoucherForm" enctype="multipart/form-data" style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
        @csrf
        <div class="modal-body" style="overflow-y: auto; overflow-x: hidden; padding: 1.5rem; flex: 1; min-height: 0;">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Direct Voucher:</strong> This voucher is created in-office. It will be automatically verified and forwarded to HOD for approval.
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">
                Date <span class="text-danger">*</span>
              </label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">
                  <i class="bx bx-calendar text-primary"></i>
                </span>
                <input type="date" class="form-control" name="date" id="directVoucherDate" value="{{ date('Y-m-d') }}" required>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">
                Payee <span class="text-danger">*</span>
              </label>
              <div class="input-group input-group-lg">
                <span class="input-group-text bg-light">
                  <i class="bx bx-user text-primary"></i>
                </span>
                <input type="text" class="form-control" name="payee" id="directVoucherPayee" required placeholder="Enter payee name">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">
              Purpose <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" name="purpose" id="directVoucherPurpose" rows="3" required placeholder="Enter purpose of this voucher..."></textarea>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">
                GL Account <span class="text-danger">*</span>
              </label>
              <select class="form-select form-select-lg" name="gl_account_id" id="directVoucherGlAccount" required>
                <option value="" selected disabled>-- Select GL Account --</option>
                @foreach(($glAccounts ?? []) as $gl)
                  <option value="{{ $gl->id }}">{{ $gl->code }} — {{ $gl->name }}</option>
                @endforeach
              </select>
              <small class="text-muted">
                <i class="bx bx-info-circle"></i> General Ledger account to be affected
              </small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">
                Cash Box <span class="text-danger">*</span>
              </label>
              <select class="form-select form-select-lg" name="cash_box_id" id="directVoucherCashBox" required>
                <option value="" selected disabled>-- Select Cash Box --</option>
                @foreach(($cashBoxes ?? []) as $cb)
                  <option value="{{ $cb->id }}">{{ $cb->name }} ({{ $cb->currency ?? 'TZS' }})</option>
                @endforeach
              </select>
              <small class="text-muted">
                <i class="bx bx-info-circle"></i> Source of payment
              </small>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Notes (Optional)</label>
            <textarea class="form-control" name="notes" id="directVoucherNotes" rows="2" placeholder="Additional notes about this voucher..."></textarea>
          </div>

          <hr class="my-4">
          <h6 class="fw-bold mb-3">
            <i class="bx bx-list-ul me-2"></i>Voucher Lines
          </h6>

          <div id="voucherLinesContainer">
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
          </div>

          <button type="button" class="btn btn-outline-primary btn-sm" id="addVoucherLine">
            <i class="bx bx-plus me-1"></i>Add Line
          </button>

          <hr class="my-4">
          <div class="row">
            <div class="col-md-6">
              <h5 class="text-success">
                <strong>Total Amount: <span id="directVoucherTotal">TZS 0.00</span></strong>
              </h5>
            </div>
          </div>

          <hr class="my-4">
          <h6 class="fw-bold mb-3">
            <i class="bx bx-paperclip me-2"></i>Attachments (Optional)
          </h6>

          <div class="file-upload-area border-2 border-dashed rounded p-4 text-center mb-3" id="directVoucherFileArea" style="cursor: pointer;">
            <input type="file" class="d-none" name="attachments[]" id="directVoucherAttachments" multiple accept=".pdf,.jpg,.jpeg,.png" onchange="previewDirectVoucherFiles(this)" onclick="event.stopPropagation();">
            <div id="directVoucherFilePlaceholder">
              <i class="bx bx-cloud-upload" style="font-size: 3rem; color: #6c757d;"></i>
              <p class="mb-2 mt-2">
                <strong>Click to upload</strong> or drag and drop
              </p>
              <p class="text-muted small mb-0">
                PDF, JPG, PNG (Max 10MB per file)
              </p>
            </div>
            <div id="directVoucherFilePreview" class="d-none mt-3">
              <div id="directVoucherFileList"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light" style="flex-shrink: 0; border-top: 1px solid #dee2e6;">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary btn-lg text-white" id="submitDirectVoucherBtn">
            <i class="bx bx-check-circle me-1"></i>Create Voucher
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
  /* Smooth scrolling for modal body */
  #directVoucherModal .modal-body {
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
  }
  
  /* Custom scrollbar styling for better visibility */
  #directVoucherModal .modal-body::-webkit-scrollbar {
    width: 8px;
  }
  
  #directVoucherModal .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
  }
  
  #directVoucherModal .modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
  }
  
  #directVoucherModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
  
  /* Ensure modal content doesn't overflow */
  #directVoucherModal .modal-content {
    overflow: hidden;
  }
</style>
