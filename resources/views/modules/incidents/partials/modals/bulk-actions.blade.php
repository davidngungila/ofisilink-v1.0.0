<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title">
          <i class="bx bx-layer me-2"></i>Bulk Actions
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="bulkActionsForm">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            Select incidents from the table using checkboxes, then choose an action below.
          </div>
          <div class="mb-3">
            <label class="form-label">Action <span class="text-danger">*</span></label>
            <select class="form-select" id="bulkAction" required onchange="toggleBulkActionFields()">
              <option value="">Select Action...</option>
              <option value="assign">Assign to Staff</option>
              <option value="update_status">Update Status</option>
              <option value="delete">Delete Selected</option>
            </select>
          </div>
          <div id="assignFields" style="display: none;">
            <div class="mb-3">
              <label class="form-label">Assign To <span class="text-danger">*</span></label>
              <select class="form-select" id="bulkAssignTo">
                <option value="">Select Staff...</option>
                @if(isset($staff) && count($staff) > 0)
                  @foreach($staff as $member)
                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                  @endforeach
                @endif
              </select>
            </div>
          </div>
          <div id="statusFields" style="display: none;">
            <div class="mb-3">
              <label class="form-label">New Status <span class="text-danger">*</span></label>
              <select class="form-select" id="bulkStatus">
                <option value="">Select Status...</option>
                <option value="New">New</option>
                <option value="Assigned">Assigned</option>
                <option value="In Progress">In Progress</option>
                <option value="Resolved">Resolved</option>
                <option value="Closed">Closed</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Selected Incidents</label>
            <div id="selectedIncidentsList" class="border rounded p-2" style="min-height: 50px; max-height: 200px; overflow-y: auto;">
              <span class="text-muted">No incidents selected. Use checkboxes in the table.</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">
            <i class="bx bx-check me-1"></i>Execute Action
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleBulkActionFields() {
  const action = document.getElementById('bulkAction').value;
  document.getElementById('assignFields').style.display = action === 'assign' ? 'block' : 'none';
  document.getElementById('statusFields').style.display = action === 'update_status' ? 'block' : 'none';
}

function updateSelectedIncidents() {
  const checkboxes = document.querySelectorAll('input[type="checkbox"][name="incident_ids[]"]:checked');
  const list = document.getElementById('selectedIncidentsList');
  selectedIncidents = Array.from(checkboxes).map(cb => cb.value);
  
  if (selectedIncidents.length === 0) {
    list.innerHTML = '<span class="text-muted">No incidents selected. Use checkboxes in the table.</span>';
  } else {
    list.innerHTML = `<strong>${selectedIncidents.length}</strong> incident(s) selected`;
  }
}

document.getElementById('bulkActionsForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const action = document.getElementById('bulkAction').value;
  
  if (selectedIncidents.length === 0) {
    showToast('Please select at least one incident', 'error');
    return;
  }
  
  const formData = new FormData();
  formData.append('action', action);
  
  // Append incident IDs as array
  selectedIncidents.forEach(id => {
    formData.append('incident_ids[]', id);
  });
  
  if (action === 'assign') {
    const assignedTo = document.getElementById('bulkAssignTo').value;
    if (!assignedTo) {
      showToast('Please select a staff member to assign', 'error');
      return;
    }
    formData.append('assigned_to', assignedTo);
  } else if (action === 'update_status') {
    const status = document.getElementById('bulkStatus').value;
    if (!status) {
      showToast('Please select a status', 'error');
      return;
    }
    formData.append('status', status);
  }
  
  try {
    const res = await fetch('{{ route("modules.incidents.bulk.action") }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        'Accept': 'application/json'
      },
      body: formData
    });
    
    const data = await res.json();
    
    if (data.success) {
      showToast(data.message, 'success');
      bootstrap.Modal.getInstance(document.getElementById('bulkActionsModal')).hide();
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast(data.message || 'Error performing bulk action', 'error');
    }
  } catch (error) {
    showToast('Network error', 'error');
  }
});
</script>

