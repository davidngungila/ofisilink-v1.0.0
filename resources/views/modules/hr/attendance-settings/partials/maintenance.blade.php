<div class="row">
    <div class="col-12">
        <h5 class="mb-4">
            <i class="bx bx-wrench text-primary"></i> System Maintenance
        </h5>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">System Health</h6>
            </div>
            <div class="card-body">
                <div id="systemHealth">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-primary w-100" onclick="checkSystemHealth()">
                    <i class="bx bx-refresh me-1"></i> Check System Health
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Device Status</h6>
            </div>
            <div class="card-body">
                <div id="deviceFailures">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-outline-info w-100" onclick="checkDeviceFailures()">
                    <i class="bx bx-error-circle me-1"></i> Check Device Failures
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Cache Management</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Clear system cache to refresh data and improve performance.</p>
                <button type="button" class="btn btn-warning w-100" onclick="clearCache()">
                    <i class="bx bx-trash me-1"></i> Clear Cache
                </button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">Data Maintenance</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Clean up old attendance records based on retention policy.</p>
                <button type="button" class="btn btn-danger w-100" onclick="runMaintenance()">
                    <i class="bx bx-cog me-1"></i> Run Maintenance
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function loadMaintenanceInfo() {
    checkSystemHealth();
    checkDeviceFailures();
}

function checkSystemHealth() {
    fetch('{{ route("modules.hr.attendance.settings") }}/health', {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const health = data.health;
            document.getElementById('systemHealth').innerHTML = `
                <div class="mb-2">
                    <strong>Status:</strong> 
                    <span class="badge bg-${health.status === 'healthy' ? 'success' : 'danger'}">${health.status}</span>
                </div>
                <div class="mb-2">
                    <strong>Database:</strong> 
                    <span class="badge bg-success">${health.database}</span>
                </div>
                <div class="mb-2">
                    <strong>Devices Online:</strong> ${health.devices_online} / ${health.total_devices}
                </div>
                <div>
                    <strong>Recent Errors:</strong> ${health.recent_errors}
                </div>
            `;
        }
    });
}

function checkDeviceFailures() {
    fetch('{{ route("modules.hr.attendance.settings") }}/device-failures', {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const failures = data.failed_devices || [];
            if (failures.length === 0) {
                document.getElementById('deviceFailures').innerHTML = `
                    <div class="alert alert-success">
                        <i class="bx bx-check-circle me-2"></i> All devices are online
                    </div>
                `;
            } else {
                document.getElementById('deviceFailures').innerHTML = `
                    <div class="alert alert-warning">
                        <strong>${failures.length} device(s) have issues:</strong>
                        <ul class="mb-0 mt-2">
                            ${failures.map(f => `<li>${f.name}: ${f.error_message}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
        }
    });
}

function clearCache() {
    Swal.fire({
        title: 'Clear Cache?',
        text: 'This will clear all cached data',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, clear',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("modules.hr.attendance.settings") }}/clear-cache', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', 'Cache cleared successfully', 'success');
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    });
}

function runMaintenance() {
    Swal.fire({
        title: 'Run Maintenance?',
        text: 'This will clean up old attendance records',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, run',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('{{ route("modules.hr.attendance.settings") }}/maintenance', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success!', `Maintenance completed. ${data.deleted_records || 0} records deleted.`, 'success');
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    });
}
</script>









