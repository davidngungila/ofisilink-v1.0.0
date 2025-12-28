@extends('layouts.app')

@section('title', 'Retrieve Data from Device')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-download"></i> Retrieve Data from Device
                </h4>
                <p class="text-muted">Retrieve users and attendance records from ZKTeco device</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Settings
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .table-responsive {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    .card-header h6 {
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bx bx-download me-2"></i>Retrieve Data from Device</h5>
                </div>
                <div class="card-body">
                    <form id="retrieveDataForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Device IP Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="ip_address" name="ip_address" 
                                       value="{{ old('ip_address', '192.168.100.108') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Port</label>
                                <input type="number" class="form-control" id="port" name="port" 
                                       value="{{ old('port', 4370) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Comm Key</label>
                                <input type="number" class="form-control" id="comm_key" name="comm_key" 
                                       value="{{ old('comm_key', 0) }}">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Data Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="data_type" name="data_type" required>
                                    <option value="all">All Data (Users + Attendances)</option>
                                    <option value="users">Users Only</option>
                                    <option value="attendances">Attendances Only</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="btn-group" role="group">
                                <button type="submit" class="btn btn-info btn-lg" id="retrieveBtn">
                                    <i class="bx bx-download me-2"></i>Retrieve Data
                                </button>
                                <button type="button" class="btn btn-success btn-lg" id="autoCaptureBtn" onclick="startAutoCapture()">
                                    <i class="bx bx-play-circle me-2"></i>Start Live Capture
                                </button>
                                <button type="button" class="btn btn-danger btn-lg" id="stopAutoCaptureBtn" onclick="stopAutoCapture()" style="display: none;">
                                    <i class="bx bx-stop-circle me-2"></i>Stop Live Capture
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="mt-3">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Live Capture Mode:</strong> When enabled, the system will automatically capture users and attendance from the device every minute and save directly to the database. 
                            This is similar to the enrollment page operations.
                        </div>
                    </div>

                    <div id="retrieveResult" class="mt-4" style="display: none;"></div>
                    
                    <!-- Live Capture Status -->
                    <div id="liveCaptureStatus" class="mt-4" style="display: none;">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-pulse me-2"></i>Live Capture Active
                                    <span class="badge bg-light text-dark ms-2" id="captureStatusBadge">Running...</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Last Sync:</strong> <span id="lastSyncTime">-</span></p>
                                        <p class="mb-1"><strong>Users Captured:</strong> <span id="usersCapturedCount">0</span></p>
                                        <p class="mb-1"><strong>Attendance Synced:</strong> <span id="attendanceSyncedCount">0</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Next Sync:</strong> <span id="nextSyncTime">-</span></p>
                                        <p class="mb-1"><strong>Total Syncs:</strong> <span id="totalSyncsCount">0</span></p>
                                        <p class="mb-1"><strong>Status:</strong> <span id="captureStatusText" class="badge bg-success">Active</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
document.getElementById('retrieveDataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('retrieveBtn');
    const resultDiv = document.getElementById('retrieveResult');
    const originalBtnText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Retrieving...';
    resultDiv.style.display = 'none';
    resultDiv.innerHTML = '';
    
    const formData = {
        ip_address: document.getElementById('ip_address').value,
        port: parseInt(document.getElementById('port').value) || 4370,
        comm_key: parseInt(document.getElementById('comm_key').value) || 0,
        data_type: document.getElementById('data_type').value
    };
    
    fetch('{{ route("zkteco.retrieve.api") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        resultDiv.style.display = 'block';
        
        if (data.success) {
            let html = '<div class="alert alert-success mb-4">';
            html += '<h5><i class="bx bx-check-circle me-2"></i>Data Retrieved Successfully!</h5>';
            html += '<hr>';
            
            // Summary section
            html += '<div class="row mb-4">';
            html += '<div class="col-md-4">';
            html += '<div class="card border-primary">';
            html += '<div class="card-body text-center">';
            html += '<h3 class="text-primary mb-0">' + (data.data.users_count || 0) + '</h3>';
            html += '<p class="text-muted mb-0">Total Users</p>';
            html += '</div></div></div>';
            html += '<div class="col-md-4">';
            html += '<div class="card border-warning">';
            html += '<div class="card-body text-center">';
            html += '<h3 class="text-warning mb-0">' + (data.data.attendances_count || 0) + '</h3>';
            html += '<p class="text-muted mb-0">Total Attendance Records</p>';
            html += '</div></div></div>';
            html += '<div class="col-md-4">';
            html += '<div class="card border-info">';
            html += '<div class="card-body text-center">';
            html += '<h3 class="text-info mb-0">' + (data.data.device_info ? 'Connected' : 'N/A') + '</h3>';
            html += '<p class="text-muted mb-0">Device Status</p>';
            html += '</div></div></div>';
            html += '</div>';
            
            if (data.data.device_info) {
                html += '<div class="card mb-4">';
                html += '<div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Device Information</h6></div>';
                html += '<div class="card-body">';
                html += '<div class="row">';
                if (data.data.device_info.device_name) {
                    html += '<div class="col-md-6 mb-2"><strong>Device Name:</strong> ' + data.data.device_info.device_name + '</div>';
                }
                if (data.data.device_info.serial_number) {
                    html += '<div class="col-md-6 mb-2"><strong>Serial Number:</strong> ' + data.data.device_info.serial_number + '</div>';
                }
                if (data.data.device_info.version) {
                    html += '<div class="col-md-6 mb-2"><strong>Firmware Version:</strong> ' + data.data.device_info.version + '</div>';
                }
                if (data.data.device_info.platform) {
                    html += '<div class="col-md-6 mb-2"><strong>Platform:</strong> ' + data.data.device_info.platform + '</div>';
                }
                if (data.data.device_info.device_id) {
                    html += '<div class="col-md-6 mb-2"><strong>Device ID:</strong> ' + data.data.device_info.device_id + '</div>';
                }
                if (data.data.device_info.ip) {
                    html += '<div class="col-md-6 mb-2"><strong>IP Address:</strong> ' + data.data.device_info.ip + '</div>';
                }
                if (data.data.device_info.port) {
                    html += '<div class="col-md-6 mb-2"><strong>Port:</strong> ' + data.data.device_info.port + '</div>';
                }
                html += '</div></div></div>';
            }
            
            if (data.data.users_count !== undefined) {
                html += '<div class="card mb-4">';
                html += '<div class="card-header bg-success text-white">';
                html += '<h6 class="mb-0"><i class="bx bx-user me-2"></i>Users - Total: <strong>' + data.data.users_count + '</strong></h6>';
                html += '</div>';
                html += '<div class="card-body">';
                if (data.data.users && data.data.users.length > 0) {
                    html += '<div class="table-responsive" style="max-height: 600px; overflow-y: auto;">';
                    html += '<table class="table table-sm table-bordered table-striped table-hover">';
                    html += '<thead class="table-light sticky-top"><tr>';
                    html += '<th>#</th><th>UID</th><th>User ID</th><th>Name</th>';
                    // Check if additional fields exist in first user
                    if (data.data.users[0].password !== undefined) {
                        html += '<th>Password</th>';
                    }
                    if (data.data.users[0].card !== undefined) {
                        html += '<th>Card</th>';
                    }
                    if (data.data.users[0].role !== undefined) {
                        html += '<th>Role</th>';
                    }
                    html += '</tr></thead><tbody>';
                    data.data.users.forEach(function(user, index) {
                        html += '<tr>';
                        html += '<td>' + (index + 1) + '</td>';
                        html += '<td>' + (user.uid || 'N/A') + '</td>';
                        html += '<td>' + (user.user_id || user.id || 'N/A') + '</td>';
                        html += '<td>' + (user.name || 'N/A') + '</td>';
                        if (user.password !== undefined) {
                            html += '<td>' + (user.password || 'N/A') + '</td>';
                        }
                        if (user.card !== undefined) {
                            html += '<td>' + (user.card || 'N/A') + '</td>';
                        }
                        if (user.role !== undefined) {
                            html += '<td>' + (user.role || 'N/A') + '</td>';
                        }
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += '<p class="text-muted mb-0">No users found on device.</p>';
                }
                html += '</div></div>';
            }
            
            if (data.data.attendances_count !== undefined) {
                html += '<div class="card mb-4">';
                html += '<div class="card-header bg-warning text-dark">';
                html += '<h6 class="mb-0"><i class="bx bx-time me-2"></i>Attendance Records - Total: <strong>' + data.data.attendances_count + '</strong></h6>';
                html += '</div>';
                html += '<div class="card-body">';
                if (data.data.attendances && data.data.attendances.length > 0) {
                    html += '<div class="table-responsive" style="max-height: 600px; overflow-y: auto;">';
                    html += '<table class="table table-sm table-bordered table-striped table-hover">';
                    html += '<thead class="table-light sticky-top"><tr>';
                    html += '<th>#</th><th>UID</th><th>User ID</th><th>Date</th><th>Time</th><th>Status</th><th>Type</th>';
                    // Check if additional fields exist in first attendance
                    if (data.data.attendances[0].punch !== undefined && data.data.attendances[0].punch !== 'N/A') {
                        html += '<th>Punch</th>';
                    }
                    if (data.data.attendances[0].device_ip !== undefined) {
                        html += '<th>Device IP</th>';
                    }
                    html += '</tr></thead><tbody>';
                    data.data.attendances.forEach(function(att, index) {
                        html += '<tr>';
                        html += '<td>' + (index + 1) + '</td>';
                        html += '<td><strong>' + (att.uid || 'N/A') + '</strong></td>';
                        html += '<td><strong>' + (att.user_id || 'N/A') + '</strong></td>';
                        html += '<td>' + (att.date || 'N/A') + '</td>';
                        html += '<td>' + (att.time || 'N/A') + '</td>';
                        // Status with color coding
                        let statusClass = '';
                        if (att.status === 'Check In') {
                            statusClass = 'badge bg-success';
                        } else if (att.status === 'Check Out') {
                            statusClass = 'badge bg-warning text-dark';
                        } else {
                            statusClass = 'badge bg-secondary';
                        }
                        html += '<td><span class="' + statusClass + '">' + (att.status || 'N/A') + '</span></td>';
                        html += '<td>' + (att.type || 'N/A') + '</td>';
                        if (att.punch !== undefined && att.punch !== 'N/A') {
                            html += '<td>' + (att.punch || 'N/A') + '</td>';
                        }
                        if (att.device_ip !== undefined) {
                            html += '<td><small>' + (att.device_ip || 'N/A') + '</small></td>';
                        }
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                    html += '<div class="mt-2">';
                    html += '<small class="text-muted">';
                    html += '<i class="bx bx-info-circle me-1"></i>';
                    html += 'UID = Sequential record number | User ID = Actual enroll ID from device';
                    html += '</small>';
                    html += '</div>';
                } else {
                    html += '<p class="text-muted mb-0">No attendance records found on device.</p>';
                }
                html += '</div></div>';
            }
            
            // Add export buttons
            html += '<div class="card mb-4">';
            html += '<div class="card-header bg-secondary text-white">';
            html += '<h6 class="mb-0"><i class="bx bx-download me-2"></i>Export Data</h6>';
            html += '</div>';
            html += '<div class="card-body">';
            html += '<div class="row g-2">';
            if (data.data.users && data.data.users.length > 0) {
                html += '<div class="col-md-6">';
                html += '<button class="btn btn-success w-100" onclick="exportToCSV(window.retrievedData.users, \'users\')">';
                html += '<i class="bx bx-file me-2"></i>Export Users (CSV)';
                html += '</button>';
                html += '</div>';
                html += '<div class="col-md-6">';
                html += '<button class="btn btn-outline-success w-100" onclick="exportToJSON(window.retrievedData.users, \'users\')">';
                html += '<i class="bx bx-file me-2"></i>Export Users (JSON)';
                html += '</button>';
                html += '</div>';
            }
            if (data.data.attendances && data.data.attendances.length > 0) {
                html += '<div class="col-md-6">';
                html += '<button class="btn btn-warning w-100" onclick="exportToCSV(window.retrievedData.attendances, \'attendances\')">';
                html += '<i class="bx bx-file me-2"></i>Export Attendances (CSV)';
                html += '</button>';
                html += '</div>';
                html += '<div class="col-md-6">';
                html += '<button class="btn btn-outline-warning w-100" onclick="exportToJSON(window.retrievedData.attendances, \'attendances\')">';
                html += '<i class="bx bx-file me-2"></i>Export Attendances (JSON)';
                html += '</button>';
                html += '</div>';
            }
            html += '</div></div></div>';
            
            // Add debug section to show raw data structure (collapsible)
            if (data.data.attendances && data.data.attendances.length > 0) {
                html += '<div class="card mb-4">';
                html += '<div class="card-header bg-dark text-white">';
                html += '<h6 class="mb-0">';
                html += '<button class="btn btn-link text-white text-decoration-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#rawDataCollapse" aria-expanded="false">';
                html += '<i class="bx bx-code me-2"></i>Raw Data Structure (Debug)';
                html += '</button>';
                html += '</h6>';
                html += '</div>';
                html += '<div class="collapse" id="rawDataCollapse">';
                html += '<div class="card-body">';
                html += '<p class="text-muted"><small>First attendance record raw structure:</small></p>';
                html += '<pre class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;"><code>' + JSON.stringify(data.data.attendances[0].raw || data.data.attendances[0], null, 2) + '</code></pre>';
                html += '</div></div></div>';
            }
            
            html += '</div>';
            resultDiv.innerHTML = html;
            
            // Store data globally for export functions
            window.retrievedData = data.data;
            
            Swal.fire('Success', 'Data retrieved successfully!', 'success');
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">' +
                '<h5><i class="bx bx-x-circle me-2"></i>Retrieval Failed</h5>' +
                '<p class="mb-0">' + (data.message || 'Unknown error') + '</p>' +
                '</div>';
            
            Swal.fire('Error', data.message || 'Failed to retrieve data', 'error');
        }
    })
    .catch(error => {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert alert-danger">' +
            '<h5><i class="bx bx-x-circle me-2"></i>Error</h5>' +
            '<p class="mb-0">' + error.message + '</p>' +
            '</div>';
        
        Swal.fire('Error', error.message, 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalBtnText;
    });
});

// Export functions
function exportToCSV(data, type) {
    if (!data || data.length === 0) {
        Swal.fire('Error', 'No data to export', 'error');
        return;
    }
    
    // Get headers from first object
    const headers = Object.keys(data[0]);
    let csv = headers.join(',') + '\n';
    
    // Add data rows
    data.forEach(function(row) {
        const values = headers.map(function(header) {
            const value = row[header] || '';
            // Escape commas and quotes in CSV
            if (typeof value === 'string' && (value.includes(',') || value.includes('"'))) {
                return '"' + value.replace(/"/g, '""') + '"';
            }
            return value;
        });
        csv += values.join(',') + '\n';
    });
    
    // Create download link
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', type + '_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire('Success', 'Data exported to CSV successfully!', 'success');
}

function exportToJSON(data, type) {
    if (!data || data.length === 0) {
        Swal.fire('Error', 'No data to export', 'error');
        return;
    }
    
    const json = JSON.stringify(data, null, 2);
    const blob = new Blob([json], { type: 'application/json;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', type + '_' + new Date().toISOString().split('T')[0] + '.json');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    Swal.fire('Success', 'Data exported to JSON successfully!', 'success');
}

// Live Capture Variables
let autoCaptureInterval = null;
let autoCaptureRunning = false;
let syncCount = 0;
let lastSyncTime = null;

// Start automatic live capture
function startAutoCapture() {
    const ip = document.getElementById('ip_address').value.trim();
    const port = parseInt(document.getElementById('port').value) || 4370;
    const commKey = parseInt(document.getElementById('comm_key').value) || 0;
    const dataType = document.getElementById('data_type').value;
    
    if (!ip) {
        Swal.fire('Error', 'Please enter device IP address', 'error');
        return;
    }
    
    autoCaptureRunning = true;
    document.getElementById('autoCaptureBtn').style.display = 'none';
    document.getElementById('stopAutoCaptureBtn').style.display = 'inline-block';
    document.getElementById('liveCaptureStatus').style.display = 'block';
    document.getElementById('retrieveBtn').disabled = true;
    
    // Update status
    updateCaptureStatus('Starting...', 'warning');
    
    // Perform initial capture
    performLiveCapture(ip, port, commKey, dataType);
    
    // Set up interval for automatic capture every minute
    autoCaptureInterval = setInterval(function() {
        if (autoCaptureRunning) {
            performLiveCapture(ip, port, commKey, dataType);
        }
    }, 60000); // Every 60 seconds
    
    Swal.fire({
        title: 'Live Capture Started!',
        html: 'The system will automatically capture data from the device every minute and save to database.',
        icon: 'success',
        timer: 3000,
        showConfirmButton: false
    });
}

// Stop automatic live capture
function stopAutoCapture() {
    autoCaptureRunning = false;
    if (autoCaptureInterval) {
        clearInterval(autoCaptureInterval);
        autoCaptureInterval = null;
    }
    
    document.getElementById('autoCaptureBtn').style.display = 'inline-block';
    document.getElementById('stopAutoCaptureBtn').style.display = 'none';
    document.getElementById('retrieveBtn').disabled = false;
    document.getElementById('liveCaptureStatus').style.display = 'none';
    
    updateCaptureStatus('Stopped', 'secondary');
    
    Swal.fire('Live Capture Stopped', 'Automatic capture has been stopped.', 'info');
}

// Perform live capture operation
function performLiveCapture(ip, port, commKey, dataType) {
    updateCaptureStatus('Syncing...', 'info');
    const startTime = new Date();
    
    // Step 1: Capture users from device (like enrollment page)
    captureUsersFromDevice(ip, port, commKey)
        .then(usersResult => {
            // Step 2: Sync attendance from device to database
            return syncAttendanceFromDevice(ip, port, commKey)
                .then(attendanceResult => {
                    return { users: usersResult, attendance: attendanceResult };
                });
        })
        .then(results => {
            syncCount++;
            lastSyncTime = new Date();
            
            // Update status
            document.getElementById('usersCapturedCount').textContent = results.users.total || 0;
            document.getElementById('attendanceSyncedCount').textContent = results.attendance.synced || 0;
            document.getElementById('totalSyncsCount').textContent = syncCount;
            document.getElementById('lastSyncTime').textContent = lastSyncTime.toLocaleTimeString();
            
            // Calculate next sync time
            const nextSync = new Date(lastSyncTime.getTime() + 60000);
            document.getElementById('nextSyncTime').textContent = nextSync.toLocaleTimeString();
            
            updateCaptureStatus('Synced Successfully', 'success');
            
            // Show notification
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Live Capture Complete',
                    html: `
                        <p><strong>Users Captured:</strong> ${results.users.total || 0}</p>
                        <p><strong>Attendance Synced:</strong> ${results.attendance.synced || 0}</p>
                        <p class="text-muted small">Next sync in 1 minute</p>
                    `,
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        })
        .catch(error => {
            console.error('Live capture error:', error);
            updateCaptureStatus('Error: ' + error.message, 'danger');
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Live Capture Error',
                    text: error.message || 'Failed to capture data',
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
}

// Capture users from device (similar to enrollment page)
function captureUsersFromDevice(ip, port, password) {
    return fetch('/zkteco/users/capture-from-device', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ip: ip,
            port: port,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return { total: data.total || 0, users: data.users || [] };
        } else {
            throw new Error(data.message || 'Failed to capture users');
        }
    });
}

// Sync attendance from device to database (using direct device connection)
function syncAttendanceFromDevice(ip, port, password) {
    // Use the sync attendance endpoint that connects directly to device
    return fetch('/zkteco/attendance/sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ip: ip,
            port: port,
            password: password
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return { synced: data.synced || 0, skipped: data.skipped || 0 };
        } else {
            throw new Error(data.message || 'Failed to sync attendance');
        }
    });
}

// Update capture status display
function updateCaptureStatus(status, type) {
    const badge = document.getElementById('captureStatusBadge');
    const text = document.getElementById('captureStatusText');
    
    if (badge) {
        badge.textContent = status;
        badge.className = 'badge bg-' + type + ' text-white ms-2';
    }
    
    if (text) {
        text.textContent = status;
        text.className = 'badge bg-' + type;
    }
}

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (autoCaptureRunning) {
        stopAutoCapture();
    }
});
</script>
@endpush

