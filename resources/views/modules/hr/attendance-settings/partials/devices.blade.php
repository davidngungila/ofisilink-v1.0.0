<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <i class="bx bx-devices text-primary"></i> Biometric Devices
            </h5>
            <div>
                <button type="button" class="btn btn-outline-info me-2" onclick="refreshDeviceStatus()">
                    <i class="bx bx-refresh me-1"></i> Refresh Status
                </button>
                <button type="button" class="btn btn-primary" onclick="openDeviceModal()">
                    <i class="bx bx-plus me-1"></i> Add Device
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="devicesTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Device ID</th>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Last Sync</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="devicesList">
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function loadDevices() {
    fetch('{{ route("modules.hr.attendance.settings") }}/devices', {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            displayDevices(data.devices);
        }
    });
}

function displayDevices(devices) {
    const tbody = document.getElementById('devicesList');
    if (devices.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">No devices found</td></tr>';
        return;
    }
    
    tbody.innerHTML = devices.map(device => `
        <tr>
            <td>${device.name}</td>
            <td><code>${device.device_id}</code></td>
            <td>${device.ip_address || 'N/A'}</td>
            <td>${device.location?.name || 'N/A'}</td>
            <td>
                ${device.is_online ? '<span class="badge bg-success">Online</span>' : '<span class="badge bg-danger">Offline</span>'}
                ${device.is_active ? '' : '<span class="badge bg-secondary ms-1">Inactive</span>'}
            </td>
            <td>${device.last_sync_at ? new Date(device.last_sync_at).toLocaleString() : 'Never'}</td>
            <td>
                <button class="btn btn-sm btn-outline-info" onclick="testDevice(${device.id})" title="Test Connection">
                    <i class="bx bx-check"></i>
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="editDevice(${device.id})">
                    <i class="bx bx-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteDevice(${device.id})">
                    <i class="bx bx-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function refreshDeviceStatus() {
    loadDevices();
}

function openDeviceModal() {
    alert('Device modal will be implemented');
}

function testDevice(id) {
    fetch(`{{ route("modules.hr.attendance.settings") }}/devices/${id}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success!', data.message, 'success');
            loadDevices();
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    });
}

function editDevice(id) {
    alert('Edit device: ' + id);
}

function deleteDevice(id) {
    Swal.fire({
        title: 'Delete Device?',
        text: 'This action cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ route("modules.hr.attendance.settings") }}/devices/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success');
                    loadDevices();
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            });
        }
    });
}
</script>









