<!-- Assign Staff Modal -->
<div class="modal fade" id="assignStaffModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-user-plus me-2"></i>Assign Staff to Imprest Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="assignStaffForm">
        @csrf
        <input type="hidden" name="imprest_id" id="assignImprestId">
        <div class="modal-body" style="max-height: calc(90vh - 200px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
          <div class="mb-3">
            <label class="form-label fw-bold">
              Select Staff Members <span class="text-danger">*</span>
            </label>
            @php
              $staffWithUnretired = $staffWithUnretiredImprests ?? [];
            @endphp
            @if(count($staffWithUnretired) > 0)
            <div class="alert alert-warning mb-3">
              <i class="bx bx-info-circle"></i> <strong>Note:</strong> Some staff members have unretired imprests and cannot be assigned to new imprests until their previous assignments are completed.
            </div>
            @endif
            <div class="input-group mb-2">
              <span class="input-group-text bg-light">
                <i class="bx bx-search text-primary"></i>
              </span>
              <input type="text" class="form-control" id="staffSearchInput" placeholder="Search staff members...">
            </div>
            <select class="form-select" name="staff_ids[]" id="staffSelect" multiple size="8" required style="min-height: 200px; max-height: 300px; overflow-y: auto;">
              @foreach($staffMembers as $staff)
                @php
                  $hasUnretired = isset($staffWithUnretired[$staff->id]) && count($staffWithUnretired[$staff->id]) > 0;
                  $unretiredList = $hasUnretired ? collect($staffWithUnretired[$staff->id])->pluck('request_no')->implode(', ') : '';
                @endphp
                <option value="{{ $staff->id }}" 
                  @if($hasUnretired) 
                    disabled 
                    data-unretired="true" 
                    data-unretired-list="{{ $unretiredList }}"
                    style="color: #dc3545; font-style: italic;"
                  @endif
                  data-staff-name="{{ strtolower($staff->name) }}"
                  data-staff-dept="{{ strtolower(optional($staff->primaryDepartment)->name ?? 'no department') }}">
                  {{ $staff->name }} - {{ optional($staff->primaryDepartment)->name ?? 'No Department' }}
                  @if($hasUnretired)
                    (⚠️ Has unretired imprest: {{ $unretiredList }})
                  @endif
                </option>
              @endforeach
            </select>
            <small class="text-muted d-block mt-2">
              <i class="bx bx-info-circle"></i> Hold Ctrl/Cmd to select multiple staff. 
              @if(count($staffWithUnretired) > 0)
                <span class="text-danger">Staff with unretired imprests are disabled and shown in red.</span>
              @endif
            </small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Assignment Notes</label>
            <textarea class="form-control" name="assignment_notes" rows="3" placeholder="Optional notes about this assignment..."></textarea>
            <small class="text-muted">
              <i class="bx bx-info-circle"></i> Add any additional information about this assignment
            </small>
          </div>
        </div>
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-primary btn-lg text-white">
            <i class="bx bx-check-circle me-1"></i>Assign Staff
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
#assignStaffModal {
    z-index: 99999 !important;
}

#assignStaffModal.show {
    z-index: 99999 !important;
    display: block !important;
}

#assignStaffModal + .modal-backdrop,
body.modal-open .modal-backdrop:last-of-type {
    z-index: 99998 !important;
}

#assignStaffModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#assignStaffModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#assignStaffModal .modal-header,
#assignStaffModal .modal-footer {
    flex-shrink: 0;
}

#assignStaffModal #staffSelect {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
}

#assignStaffModal #staffSelect:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

#assignStaffModal #staffSelect option:checked {
    background-color: #0d6efd;
    color: white;
}

@media (max-width: 991.98px) {
    #assignStaffModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #assignStaffModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('staffSearchInput');
    const staffSelect = document.getElementById('staffSelect');
    
    if (searchInput && staffSelect) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const options = staffSelect.querySelectorAll('option');
            
            options.forEach(option => {
                const staffName = option.getAttribute('data-staff-name') || '';
                const staffDept = option.getAttribute('data-staff-dept') || '';
                
                if (staffName.includes(searchTerm) || staffDept.includes(searchTerm)) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            });
        });
    }
});
</script>

