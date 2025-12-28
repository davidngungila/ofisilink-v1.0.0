<div class="row">
    <div class="col-12">
        <h5 class="mb-4">
            <i class="bx bx-sync text-primary"></i> Sync Settings
        </h5>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Sync Configuration</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Default Sync Mode</label>
                    <select class="form-select" id="defaultSyncMode">
                        <option value="push">Push (Device → System)</option>
                        <option value="pull">Pull (System → Device)</option>
                        <option value="bidirectional">Bidirectional</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Polling Interval (minutes)</label>
                    <input type="number" class="form-control" id="pollingInterval" value="5" min="1" max="60">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="autoSyncEnabled" checked>
                    <label class="form-check-label" for="autoSyncEnabled">Enable auto-sync</label>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Failure Detection</h6>
            </div>
            <div class="card-body">
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="autoFailureDetection" checked>
                    <label class="form-check-label" for="autoFailureDetection">Auto-detect failures</label>
                </div>
                <div class="mb-3">
                    <label class="form-label">Failure Threshold (minutes)</label>
                    <input type="number" class="form-control" id="failureThreshold" value="5" min="1" max="60">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <button type="button" class="btn btn-primary" onclick="saveSyncSettings()">
            <i class="bx bx-save me-1"></i> Save Sync Settings
        </button>
    </div>
</div>

<script>
function loadSyncSettings() {
    // Load sync settings
}

function saveSyncSettings() {
    const settings = {
        default_sync_mode: document.getElementById('defaultSyncMode').value,
        polling_interval: document.getElementById('pollingInterval').value,
        auto_sync_enabled: document.getElementById('autoSyncEnabled').checked,
        auto_failure_detection: document.getElementById('autoFailureDetection').checked,
        failure_threshold: document.getElementById('failureThreshold').value,
    };

    fetch('{{ route("modules.hr.attendance.settings") }}/sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(settings)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', 'Sync settings saved', 'success');
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    });
}
</script>









