<!-- Sync Emails Modal -->
<div class="modal fade" id="syncEmailsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="bx bx-sync me-2"></i>Sync Emails
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="syncEmailsForm">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            Sync will run in the background to prevent system slowdown. You'll be notified when complete.
          </div>

          <div class="mb-3">
            <label class="form-label">Sync Mode <span class="text-danger">*</span></label>
            <select class="form-select" id="syncMode" name="sync_mode" required onchange="toggleSyncOptions()">
              <option value="all">Sync All Unseen Emails</option>
              <option value="range">Sync by Date Range</option>
              <option value="live">Live Mode (Only New Emails)</option>
            </select>
            <small class="text-muted">
              <div id="modeAllDesc" class="mt-2">Sync all unread emails from all configured accounts.</div>
              <div id="modeRangeDesc" class="mt-2" style="display:none;">Sync emails within the specified date range.</div>
              <div id="modeLiveDesc" class="mt-2" style="display:none;">Only sync emails received after the last sync. Enables automatic background syncing.</div>
            </small>
          </div>

          <div id="dateRangeFields" style="display: none;">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Date From <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="syncDateFrom" name="date_from">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Date To <span class="text-danger">*</span></label>
                <input type="date" class="form-control" id="syncDateTo" name="date_to">
              </div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Email Account (Optional)</label>
            <select class="form-select" id="syncConfigId" name="config_id">
              <option value="">All Active Accounts</option>
            </select>
            <small class="text-muted">Leave blank to sync all active email accounts.</small>
          </div>

          <div class="alert alert-warning" id="liveModeWarning" style="display: none;">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Live Mode:</strong> This will enable automatic background syncing. New emails will be automatically imported every 5 minutes.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-sync me-1"></i>Start Sync
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleSyncOptions() {
  const mode = document.getElementById('syncMode').value;
  const dateRangeFields = document.getElementById('dateRangeFields');
  const liveWarning = document.getElementById('liveModeWarning');
  
  // Hide all descriptions
  document.getElementById('modeAllDesc').style.display = 'none';
  document.getElementById('modeRangeDesc').style.display = 'none';
  document.getElementById('modeLiveDesc').style.display = 'none';
  
  if (mode === 'range') {
    dateRangeFields.style.display = 'block';
    liveWarning.style.display = 'none';
    document.getElementById('modeRangeDesc').style.display = 'block';
    document.getElementById('syncDateFrom').required = true;
    document.getElementById('syncDateTo').required = true;
  } else if (mode === 'live') {
    dateRangeFields.style.display = 'none';
    liveWarning.style.display = 'block';
    document.getElementById('modeLiveDesc').style.display = 'block';
    document.getElementById('syncDateFrom').required = false;
    document.getElementById('syncDateTo').required = false;
  } else {
    dateRangeFields.style.display = 'none';
    liveWarning.style.display = 'none';
    document.getElementById('modeAllDesc').style.display = 'block';
    document.getElementById('syncDateFrom').required = false;
    document.getElementById('syncDateTo').required = false;
  }
}

function showSyncEmailsModal() {
  // Load email configs
  loadEmailConfigs();
  
  const modal = new bootstrap.Modal(document.getElementById('syncEmailsModal'));
  modal.show();
}

function loadEmailConfigs() {
  fetch('{{ route("modules.incidents.email.configs") }}', {
    headers: {
      'Accept': 'application/json'
    }
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      const select = document.getElementById('syncConfigId');
      select.innerHTML = '<option value="">All Active Accounts</option>';
      data.configs.forEach(config => {
        const option = document.createElement('option');
        option.value = config.id;
        option.textContent = config.email_address;
        select.appendChild(option);
      });
    }
  })
  .catch(err => {
    console.error('Error loading email configs:', err);
  });
}

document.getElementById('syncEmailsForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalText = submitBtn.innerHTML;
  
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Starting...';
  
  try {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const res = await fetch('{{ route("modules.incidents.sync.emails") }}', {
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
      bootstrap.Modal.getInstance(document.getElementById('syncEmailsModal')).hide();
      
      // If live mode, show notification about automatic syncing
      if (data.sync_mode === 'live') {
        showToast('Live mode enabled. New emails will be synced automatically every 5 minutes.', 'info');
      }
    } else {
      showToast(data.message || 'Error starting sync', 'error');
    }
  } catch (error) {
    showToast('Network error', 'error');
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
  }
});
</script>

