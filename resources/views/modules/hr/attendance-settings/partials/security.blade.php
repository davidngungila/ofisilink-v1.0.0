<div class="row">
    <div class="col-12">
        <h5 class="mb-4">
            <i class="bx bx-lock text-primary"></i> Security Settings
        </h5>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">API Security</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">API Authentication Method</label>
                    <select class="form-select" id="apiAuthMethod">
                        <option value="token">Token</option>
                        <option value="key">API Key</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">API Key</label>
                    <input type="text" class="form-control" id="apiKey" placeholder="Enter API key">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="enableApiLogging" checked>
                    <label class="form-check-label" for="enableApiLogging">Enable API logging</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="requireHttps">
                    <label class="form-check-label" for="requireHttps">Require HTTPS</label>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Access Control</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">Rate Limit (requests/minute)</label>
                    <input type="number" class="form-control" id="rateLimit" value="60" min="1" max="1000">
                </div>
                <div class="mb-3">
                    <label class="form-label">Allowed IPs (comma-separated)</label>
                    <textarea class="form-control" id="allowedIPs" rows="3" placeholder="192.168.1.1, 10.0.0.1"></textarea>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="enableAuditLog" checked>
                    <label class="form-check-label" for="enableAuditLog">Enable audit log</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <button type="button" class="btn btn-primary" onclick="saveSecuritySettings()">
            <i class="bx bx-save me-1"></i> Save Security Settings
        </button>
    </div>
</div>

<script>
function loadSecuritySettings() {
    // Load security settings
}

function saveSecuritySettings() {
    const settings = {
        api_auth_method: document.getElementById('apiAuthMethod').value,
        api_key: document.getElementById('apiKey').value,
        enable_api_logging: document.getElementById('enableApiLogging').checked,
        require_https: document.getElementById('requireHttps').checked,
        rate_limit: document.getElementById('rateLimit').value,
        allowed_ips: document.getElementById('allowedIPs').value.split(',').map(ip => ip.trim()),
        enable_audit_log: document.getElementById('enableAuditLog').checked,
    };

    fetch('{{ route("modules.hr.attendance.settings") }}/security', {
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
            Swal.fire('Success!', 'Security settings saved', 'success');
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    });
}
</script>









