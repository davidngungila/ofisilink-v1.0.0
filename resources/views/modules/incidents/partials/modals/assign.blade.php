<!-- Assign Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="bx bx-user-plus me-2"></i>Assign Incident
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="assignForm">
        <div class="modal-body">
          <input type="hidden" id="assignIncidentId">
          <div class="mb-3">
            <label class="form-label">Assign To <span class="text-danger">*</span></label>
            <select class="form-select" id="assignTo" required>
              <option value="">Select Staff...</option>
              @if(isset($staff) && count($staff) > 0)
                @foreach($staff as $member)
                  <option value="{{ $member->id }}">{{ $member->name }}</option>
                @endforeach
              @endif
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Internal Notes (Optional)</label>
            <textarea class="form-control" id="assignNotes" rows="3" placeholder="Add internal notes..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-check me-1"></i>Assign
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function assignIncident(id) {
  document.getElementById('assignIncidentId').value = id;
  const modal = new bootstrap.Modal(document.getElementById('assignModal'));
  modal.show();
}

document.getElementById('assignForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const incidentId = document.getElementById('assignIncidentId').value;
  const assignedTo = document.getElementById('assignTo').value;
  const notes = document.getElementById('assignNotes').value;
  
  const formData = new FormData();
  formData.append('assigned_to', assignedTo);
  formData.append('internal_notes', notes);
  
  try {
    const res = await fetch(`/modules/incidents/${incidentId}/assign`, {
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
      bootstrap.Modal.getInstance(document.getElementById('assignModal')).hide();
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast(data.message || 'Error assigning incident', 'error');
    }
  } catch (error) {
    showToast('Network error', 'error');
  }
});
</script>


