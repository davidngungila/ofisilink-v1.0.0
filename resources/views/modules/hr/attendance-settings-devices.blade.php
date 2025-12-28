@extends('layouts.app')

@section('title', 'Attendance Devices')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-devices"></i> Biometric Devices
                </h4>
                <p class="text-muted">Manage and configure biometric attendance devices</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary me-2">
                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                </a>
                <button type="button" class="btn btn-primary" onclick="openDeviceModal()">
                    <i class="bx bx-plus me-1"></i> Add Device
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .device-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 8px;
    }
    .device-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .status-online {
        color: #28a745;
    }
    .status-offline {
        color: #dc3545;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Devices</h6>
                            <h3 class="mb-0" id="statTotalDevices">{{ $stats['total_devices'] ?? 0 }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bx bx-devices fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Online Devices</h6>
                            <h3 class="mb-0 text-success" id="statOnlineDevices">{{ $stats['online_devices'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-check-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Offline Devices</h6>
                            <h3 class="mb-0 text-warning" id="statOfflineDevices">{{ ($stats['total_devices'] ?? 0) - ($stats['online_devices'] ?? 0) }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bx bx-x-circle fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="bx bx-list-ul me-1"></i> Devices List
                    </h6>
                    <div>
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="refreshDeviceStatus()">
                            <i class="bx bx-refresh me-1"></i> Refresh Status
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="testAllDevices()">
                            <i class="bx bx-check-double me-1"></i> Test All
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="devicesTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Device ID</th>
                                    <th>IP Address</th>
                                    <th>Port</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Last Sync</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="devicesList">
                                <tr>
                                    <td colspan="8" class="text-center py-4">
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
</div>

<!-- Device Modal -->
@include('modules.hr.attendance-settings.modals.device-modal')

<!-- View Device Details Modal -->
@include('modules.hr.attendance-settings.modals.view-device-modal')

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const csrfToken = '{{ csrf_token() }}';
const devicesData = @json($devices ?? []);
const locations = @json($locations ?? []);
let currentDeviceId = null;

document.addEventListener('DOMContentLoaded', function() {
    loadDevices();
    setupDeviceForm();
});

function loadDevices() {
    const tbody = document.getElementById('devicesList');
    if (!tbody) return;

    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </td>
        </tr>
    `;

    fetch('/attendance-settings/devices', {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.devices) {
            if (data.devices.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bx bx-inbox fs-1"></i><p class="mt-2">No devices found</p></td></tr>';
                return;
            }

            let html = '';
            data.devices.forEach(device => {
                const statusClass = device.is_online ? 'status-online' : 'status-offline';
                const statusIcon = device.is_online ? 'bx-check-circle' : 'bx-x-circle';
                const statusText = device.is_online ? 'Online' : 'Offline';
                const lastSync = device.last_sync_at ? new Date(device.last_sync_at).toLocaleString() : 'Never';
                
                html += '<tr>';
                html += '<td><strong>' + (device.name || 'N/A') + '</strong></td>';
                html += '<td><code>' + (device.device_id || 'N/A') + '</code></td>';
                html += '<td>' + (device.ip_address || 'N/A') + '</td>';
                html += '<td>' + (device.port || '4370') + '</td>';
                html += '<td>' + (device.location?.name || 'N/A') + '</td>';
                html += '<td><span class="' + statusClass + '"><i class="bx ' + statusIcon + ' me-1"></i>' + statusText + '</span></td>';
                html += '<td><small class="text-muted">' + lastSync + '</small></td>';
                html += '<td>';
                html += '<div class="btn-group" role="group">';
                html += '<button class="btn btn-sm btn-outline-info" onclick="viewDeviceDetails(' + device.id + ')" title="View More"><i class="bx bx-show"></i></button> ';
                html += '<button class="btn btn-sm btn-outline-primary" onclick="editDevice(' + device.id + ')" title="Edit"><i class="bx bx-edit"></i></button> ';
                html += '<button class="btn btn-sm btn-outline-warning" onclick="testDevice(' + device.id + ')" title="Test Connection"><i class="bx bx-wifi"></i></button> ';
                html += '<button class="btn btn-sm btn-outline-danger" onclick="deleteDevice(' + device.id + ', \'' + (device.name || '').replace(/'/g, "\\'") + '\')" title="Delete"><i class="bx bx-trash"></i></button>';
                html += '</div>';
                html += '</td>';
                html += '</tr>';
            });

            tbody.innerHTML = html;
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger"><i class="bx bx-error-circle fs-1"></i><p class="mt-2">Failed to load devices</p></td></tr>';
        }
    })
    .catch(error => {
        console.error('Error loading devices:', error);
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-5 text-danger"><i class="bx bx-error-circle fs-1"></i><p class="mt-2">Failed to load devices</p></td></tr>';
    });
}

function refreshDeviceStatus() {
    // Refresh device status logic
    loadDevices();
}

function testDevice(deviceId) {
    // Test device connection logic
    Swal.fire({
        title: 'Testing Connection...',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    fetch('/attendance-settings/devices/' + deviceId + '/test', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        if (data.success) {
            Swal.fire('Success!', data.message, 'success');
            loadDevices();
        } else {
            Swal.fire('Error!', data.message, 'error');
        }
    })
    .catch(error => {
        Swal.close();
        Swal.fire('Error!', 'Failed to test device connection', 'error');
    });
}

function testAllDevices() {
    // Test all devices logic
    Swal.fire('Info', 'Testing all devices...', 'info');
}

function editDevice(deviceId) {
    // Edit device logic
    openDeviceModal(deviceId);
}

function deleteDevice(deviceId, deviceName) {
    Swal.fire({
        title: 'Delete Device',
        html: 'Are you sure you want to delete <strong>' + deviceName + '</strong>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/attendance-settings/devices/' + deviceId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', data.message, 'success');
                    loadDevices();
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            })
            .catch(error => {
                Swal.fire('Error!', 'Failed to delete device', 'error');
            });
        }
    });
}

function openDeviceModal(deviceId = null) {
    const modal = new bootstrap.Modal(document.getElementById('deviceModal'));
    const form = document.getElementById('deviceForm');
    const modalTitle = document.getElementById('deviceModalTitle');
    
    // Reset form
    form.reset();
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    if (deviceId) {
        // Edit mode
        modalTitle.innerHTML = '<i class="bx bx-edit me-2"></i>Edit Device';
        currentDeviceId = deviceId;
        
        // Load device data
        fetch(`/attendance-settings/devices/${deviceId}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.device) {
                const device = data.device;
                document.getElementById('deviceId').value = device.id;
                document.getElementById('deviceName').value = device.name || '';
                document.getElementById('deviceDeviceId').value = device.device_id || '';
                document.getElementById('deviceType').value = device.device_type || 'biometric';
                document.getElementById('deviceLocation').value = device.location_id || '';
                document.getElementById('deviceManufacturer').value = device.manufacturer || '';
                document.getElementById('deviceModel').value = device.model || '';
                document.getElementById('deviceSerialNumber').value = device.serial_number || '';
                document.getElementById('deviceIpAddress').value = device.ip_address || '';
                document.getElementById('devicePort').value = device.port || '';
                document.getElementById('deviceMacAddress').value = device.mac_address || '';
                document.getElementById('deviceConnectionType').value = device.connection_type || 'network';
                document.getElementById('deviceSyncInterval').value = device.sync_interval_minutes || 5;
                document.getElementById('deviceConnectionConfig').value = device.connection_config ? JSON.stringify(device.connection_config, null, 2) : '';
                document.getElementById('deviceCapabilities').value = device.capabilities ? JSON.stringify(device.capabilities, null, 2) : '';
                document.getElementById('deviceSettings').value = device.settings ? JSON.stringify(device.settings, null, 2) : '';
                document.getElementById('deviceNotes').value = device.notes || '';
                document.getElementById('deviceIsActive').checked = device.is_active !== false;
            }
        })
        .catch(error => {
            console.error('Error loading device:', error);
            Swal.fire('Error!', 'Failed to load device data', 'error');
        });
    } else {
        // Create mode
        modalTitle.innerHTML = '<i class="bx bx-plus me-2"></i>Add Device';
        currentDeviceId = null;
        document.getElementById('deviceId').value = '';
    }
    
    modal.show();
}

function setupDeviceForm() {
    const form = document.getElementById('deviceForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        const deviceId = document.getElementById('deviceId').value;
        
        // Parse JSON fields
        try {
            if (data.connection_config && data.connection_config.trim()) {
                data.connection_config = JSON.parse(data.connection_config);
            }
        } catch (e) {
            Swal.fire('Error!', 'Invalid JSON in Connection Config', 'error');
            return;
        }
        
        try {
            if (data.capabilities && data.capabilities.trim()) {
                data.capabilities = JSON.parse(data.capabilities);
            }
        } catch (e) {
            Swal.fire('Error!', 'Invalid JSON in Capabilities', 'error');
            return;
        }
        
        try {
            if (data.settings && data.settings.trim()) {
                data.settings = JSON.parse(data.settings);
            }
        } catch (e) {
            Swal.fire('Error!', 'Invalid JSON in Settings', 'error');
            return;
        }
        
        // Convert checkbox to boolean
        data.is_active = document.getElementById('deviceIsActive').checked;
        
        const url = deviceId 
            ? `/attendance-settings/devices/${deviceId}`
            : '/attendance-settings/devices';
        const method = deviceId ? 'PUT' : 'POST';
        
        Swal.fire({
            title: deviceId ? 'Updating Device...' : 'Creating Device...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                Swal.fire('Success!', data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('deviceModal')).hide();
                form.reset();
                // Reload devices
                loadDevices();
            } else {
                let errorMsg = data.message || 'An error occurred';
                if (data.errors) {
                    errorMsg += '<br><ul>';
                    Object.keys(data.errors).forEach(key => {
                        errorMsg += '<li>' + data.errors[key][0] + '</li>';
                    });
                    errorMsg += '</ul>';
                }
                Swal.fire('Error!', errorMsg, 'error');
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire('Error!', 'Failed to save device', 'error');
        });
    });
}

function viewDeviceDetails(deviceId) {
    const modal = new bootstrap.Modal(document.getElementById('viewDeviceModal'));
    const content = document.getElementById('viewDeviceContent');
    
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading device details...</p>
        </div>
    `;
    
    currentDeviceId = deviceId;
    document.getElementById('editDeviceFromViewBtn').setAttribute('data-device-id', deviceId);
    
    fetch(`/attendance-settings/devices/${deviceId}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.device) {
            const device = data.device;
            const statusClass = device.is_online ? 'text-success' : 'text-danger';
            const statusIcon = device.is_online ? 'bx-check-circle' : 'bx-x-circle';
            const statusText = device.is_online ? 'Online' : 'Offline';
            const lastSync = device.last_sync_at ? new Date(device.last_sync_at).toLocaleString() : 'Never';
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Name:</th>
                                        <td><strong>${device.name || 'N/A'}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Device ID:</th>
                                        <td><code>${device.device_id || 'N/A'}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Device Type:</th>
                                        <td><span class="badge bg-info">${device.device_type || 'N/A'}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Location:</th>
                                        <td>${device.location?.name || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td><span class="${statusClass}"><i class="bx ${statusIcon} me-1"></i>${statusText}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Active:</th>
                                        <td>${device.is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="bx bx-network-chart me-2"></i>Connection Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">IP Address:</th>
                                        <td>${device.ip_address || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Port:</th>
                                        <td>${device.port || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>MAC Address:</th>
                                        <td>${device.mac_address || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Connection Type:</th>
                                        <td><span class="badge bg-secondary">${device.connection_type || 'N/A'}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Last Sync:</th>
                                        <td><small class="text-muted">${lastSync}</small></td>
                                    </tr>
                                    <tr>
                                        <th>Sync Interval:</th>
                                        <td>${device.sync_interval_minutes || 5} minutes</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bx bx-chip me-2"></i>Hardware Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Manufacturer:</th>
                                        <td>${device.manufacturer || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Model:</th>
                                        <td>${device.model || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Serial Number:</th>
                                        <td>${device.serial_number || 'N/A'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Additional Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Created By:</th>
                                        <td>${device.creator?.name || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated By:</th>
                                        <td>${device.updater?.name || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Created At:</th>
                                        <td><small>${new Date(device.created_at).toLocaleString()}</small></td>
                                    </tr>
                                    <tr>
                                        <th>Updated At:</th>
                                        <td><small>${new Date(device.updated_at).toLocaleString()}</small></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                ${device.connection_config ? `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-code-alt me-2"></i>Connection Config</h6>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>${JSON.stringify(device.connection_config, null, 2)}</code></pre>
                    </div>
                </div>
                ` : ''}
                ${device.capabilities ? `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-list-check me-2"></i>Capabilities</h6>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>${JSON.stringify(device.capabilities, null, 2)}</code></pre>
                    </div>
                </div>
                ` : ''}
                ${device.settings ? `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-slider me-2"></i>Settings</h6>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>${JSON.stringify(device.settings, null, 2)}</code></pre>
                    </div>
                </div>
                ` : ''}
                ${device.notes ? `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bx bx-note me-2"></i>Notes</h6>
                    </div>
                    <div class="card-body">
                        <p>${device.notes}</p>
                    </div>
                </div>
                ` : ''}
            `;
        } else {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${data.message || 'Failed to load device details'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="alert alert-danger">
                <i class="bx bx-error-circle me-2"></i>
                <strong>Error:</strong> Failed to load device details
            </div>
        `;
    });
    
    modal.show();
}

function editDeviceFromView() {
    const deviceId = document.getElementById('editDeviceFromViewBtn').getAttribute('data-device-id');
    if (deviceId) {
        bootstrap.Modal.getInstance(document.getElementById('viewDeviceModal')).hide();
        setTimeout(() => {
            openDeviceModal(deviceId);
        }, 300);
    }
}
</script>
@endpush









