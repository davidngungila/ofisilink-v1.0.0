<!-- Create Incident Modal -->
<div class="modal fade" id="createIncidentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="bx bx-plus-circle me-2"></i>Create New Incident
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="createIncidentForm" enctype="multipart/form-data">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="title" required placeholder="Enter incident title">
          </div>
          
          <div class="mb-3">
            <label class="form-label">Description <span class="text-danger">*</span></label>
            <textarea class="form-control" name="description" rows="4" required placeholder="Describe the incident in detail..."></textarea>
          </div>
          
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Priority <span class="text-danger">*</span></label>
              <select class="form-select" name="priority" required>
                <option value="Low">Low</option>
                <option value="Medium" selected>Medium</option>
                <option value="High">High</option>
                <option value="Critical">Critical</option>
              </select>
            </div>
            
            <div class="col-md-6 mb-3">
              <label class="form-label">Category <span class="text-danger">*</span></label>
              <select class="form-select" name="category" required>
                <option value="technical">Technical</option>
                <option value="hr">HR</option>
                <option value="facilities">Facilities</option>
                <option value="security">Security</option>
                <option value="other">Other</option>
              </select>
            </div>
          </div>
          
          <hr>
          
          <h6 class="mb-3"><i class="bx bx-user me-2"></i>Reporter Information</h6>
          
          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label">Reporter Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="reporter_name" required placeholder="Full name">
            </div>
            
            <div class="col-md-4 mb-3">
              <label class="form-label">Reporter Email</label>
              <input type="email" class="form-control" name="reporter_email" placeholder="email@example.com">
            </div>
            
            <div class="col-md-4 mb-3">
              <label class="form-label">Reporter Phone</label>
              <input type="tel" class="form-control" name="reporter_phone" placeholder="2556XXXXXXXX">
            </div>
          </div>

          <hr>
          
          <div class="mb-3">
            <label class="form-label">Assign To (Optional)</label>
            <select class="form-select" name="assigned_to">
              <option value="">Leave Unassigned</option>
              @if(isset($staff) && (is_countable($staff) ? count($staff) > 0 : !empty($staff)))
                @foreach($staff as $member)
                  @if(is_object($member) && isset($member->id))
                    <option value="{{ $member->id }}">{{ $member->name ?? 'N/A' }}</option>
                  @endif
                @endforeach
              @endif
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Attachments</label>
            <input type="file" class="form-control" name="attachments[]" multiple>
            <small class="text-muted">Multiple files allowed. Max 10MB per file.</small>
          </div>

          <div class="mb-3">
            <label class="form-label">Internal Notes</label>
            <textarea class="form-control" name="internal_notes" rows="3" placeholder="Internal notes (not visible to reporter)"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-1"></i>Create Incident
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('createIncidentForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creating...';
  
  try {
    const res = await fetch('{{ route("modules.incidents.store") }}', {
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
      bootstrap.Modal.getInstance(document.getElementById('createIncidentModal')).hide();
      this.reset();
      setTimeout(() => location.reload(), 1000);
    } else {
      showToast(data.message || 'Error creating incident', 'error');
    }
  } catch (error) {
    showToast('Network error', 'error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
  }
});
</script>


