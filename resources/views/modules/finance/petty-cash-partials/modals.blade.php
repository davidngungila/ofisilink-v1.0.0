<!-- Accountant Verify Modal -->
<div class="modal fade" id="accountantVerifyModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="accountantVerifyForm" method="POST">
        @csrf
        <input type="hidden" name="action" value="approve">
        <div class="modal-header">
          <h5 class="modal-title">Verify Petty Cash Voucher</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">GL Account <span class="text-danger">*</span></label>
            <select name="gl_account_id" class="form-select" required>
              <option value="">Select GL Account</option>
              @if(isset($glAccounts))
                @foreach($glAccounts as $glAccount)
                  <option value="{{ $glAccount->id }}">{{ $glAccount->code }} - {{ $glAccount->name }}</option>
                @endforeach
              @endif
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Cash Box <span class="text-danger">*</span></label>
            <select name="cash_box_id" class="form-select" required>
              <option value="">Select Cash Box</option>
              @if(isset($cashBoxes))
                @foreach($cashBoxes as $cashBox)
                  <option value="{{ $cashBox->id }}">{{ $cashBox->name }}</option>
                @endforeach
              @endif
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea name="comments" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Verify</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- HOD Approve Modal -->
<div class="modal fade" id="hodApproveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="hodApproveForm" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Approve Petty Cash Voucher</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea name="hod_comments" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Approve</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CEO Approve Modal -->
<div class="modal fade" id="ceoApproveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="ceoApproveForm" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Final Approval - Petty Cash Voucher</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea name="ceo_comments" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Approve</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="paymentForm" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Process Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Payment Method <span class="text-danger">*</span></label>
              <select name="payment_method" class="form-select" required>
                <option value="">Select Method</option>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cheque">Cheque</option>
                <option value="mobile_money">Mobile Money</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Payment Reference</label>
              <input type="text" name="payment_reference" class="form-control">
            </div>
            <div class="col-md-12 mb-3">
              <label class="form-label">Payment Notes</label>
              <textarea name="payment_notes" class="form-control" rows="3"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Mark as Paid</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Retirement Approval Modal -->
<div class="modal fade" id="retirementApproveModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="retirementApproveForm" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Approve Retirement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea name="retirement_comments" class="form-control" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Approve Retirement</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" data-bs-backdrop="true" data-bs-keyboard="true">
  <div class="modal-dialog modal-fullscreen-lg-down modal-dialog-centered modal-dialog-scrollable" style="max-width: 95vw; width: 95vw;">
    <div class="modal-content shadow-lg border-0" style="max-height: 95vh;">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-detail me-2"></i>Petty Cash Voucher Details
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

