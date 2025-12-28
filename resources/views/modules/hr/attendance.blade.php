@extends('layouts.app')

@section('title', 'Attendance Management')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-time me-2"></i>Attendance Management
                            </h4>
                            <p class="card-text text-white-50 mb-0">Track employee attendance automatically via ZKTeco biometric devices</p>
                        </div>
                        <div>
                            @if($canManage)
                            <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-light me-2">
                                <i class="bx bx-cog me-1"></i>Settings
                            </a>
                            <button type="button" class="btn btn-light btn-danger me-2" onclick="deleteAllAttendance()" title="Delete All Attendance Records">
                                <i class="bx bx-trash me-1"></i>Delete All
                            </button>
                            @endif
                            <div class="btn-group">
                                <button type="button" class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-download me-1"></i>Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('attendance.export', array_merge(request()->all(), ['format' => 'excel'])) }}">
                                        <i class="bx bx-file me-2"></i>Export to Excel
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('attendance.export', array_merge(request()->all(), ['format' => 'pdf'])) }}">
                                        <i class="bx bx-file me-2"></i>Export to PDF
                                    </a></li>
                                    <li><a class="dropdown-item" href="{{ route('attendance.export', array_merge(request()->all(), ['format' => 'csv'])) }}">
                                        <i class="bx bx-file me-2"></i>Export to CSV
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- ZKTeco Sync Section -->
    @if($canManage)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-sync me-2"></i>ZKTeco Device Sync
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Device IP</label>
                            <input type="text" class="form-control" id="deviceIp" value="{{ $deviceIp ?? config('zkteco.ip', '192.168.100.108') }}" placeholder="192.168.100.108">
                            </div>
                        <div class="col-md-2">
                            <label class="form-label">Port</label>
                            <input type="number" class="form-control" id="devicePort" value="{{ $devicePort ?? config('zkteco.port', 4370) }}" placeholder="4370">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Comm Key</label>
                            <input type="number" class="form-control" id="devicePassword" value="{{ $deviceCommKey ?? config('zkteco.password', 0) }}" placeholder="0">
                            </div>
                        <div class="col-md-5">
                            <label class="form-label">&nbsp;</label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-primary" onclick="testZKTecoConnection()">
                                    <i class="bx bx-wifi me-1"></i>Test Connection
                                </button>
                                <button type="button" class="btn btn-success" onclick="syncZKTecoAttendance()">
                                    <i class="bx bx-sync me-1"></i>Sync Now
                                </button>
                                <a href="{{ route('modules.hr.attendance.settings') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-cog me-1"></i>Settings
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="alert alert-success mb-2">
                            <i class="bx bx-check-circle me-2"></i>
                            <strong>Real-Time Auto Capture:</strong> The system automatically syncs users and attendance from ZKTeco devices every second. 
                            All new attendance attempts are captured instantly and saved to the database in real-time. No manual action required.
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>Data Source:</strong> All attendance data is captured directly from ZKTeco biometric devices using direct device connection. 
                            The system automatically saves attendance records for users that exist in your local system. 
                            Manual check-in/check-out is disabled - all attendance must be recorded via the biometric device.
                        </div>
                    </div>
                    
                    <!-- Live Capture Status -->
                    <div id="liveCaptureStatus" class="mt-3">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="bx bx-pulse me-2"></i>Real-Time Auto Capture Active
                                    <span class="badge bg-light text-dark ms-2" id="captureStatusBadge">Running...</span>
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Last Sync:</strong> <span id="lastSyncTime">-</span></p>
                                        <p class="mb-1"><strong>Users Captured:</strong> <span id="usersCapturedCount">0</span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Attendance Synced:</strong> <span id="attendanceSyncedCount">0</span></p>
                                        <p class="mb-1"><strong>Total Syncs:</strong> <span id="totalSyncsCount">0</span></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>Next Sync:</strong> <span id="nextSyncTime">-</span></p>
                                        <p class="mb-1"><strong>Status:</strong> <span id="captureStatusText" class="badge bg-success" title="" data-bs-toggle="tooltip" data-bs-placement="top">Real-Time (1 sec)</span></p>
                                        <p class="mb-0 small text-muted" id="errorDetails" style="display: none;"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-filter me-2"></i>Filters
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('modules.hr.attendance') }}" id="filterForm">
                        <div class="row g-3">
                            @if($canViewAll)
                            <div class="col-md-3">
                                <label class="form-label">Employee</label>
                                <select name="employee_id" class="form-select">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                                            {{ $emp->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select name="department_id" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Schedule</label>
                                <select name="schedule_id" class="form-select">
                                    <option value="">All Schedules</option>
                                    @foreach($schedules as $schedule)
                                        <option value="{{ $schedule->id }}" {{ request('schedule_id') == $schedule->id ? 'selected' : '' }}>
                                            {{ $schedule->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Policy</label>
                                <select name="policy_id" class="form-select">
                                    <option value="">All Policies</option>
                                    @foreach($policies as $policy)
                                        <option value="{{ $policy->id }}" {{ request('policy_id') == $policy->id ? 'selected' : '' }}>
                                            {{ $policy->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <i class="bx bx-info-circle me-2"></i>
                                    <strong>Your Attendance:</strong> You can only view your own attendance records.
                                </div>
                            </div>
                            @endif
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                                    <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late</option>
                                    <option value="early_leave" {{ request('status') == 'early_leave' ? 'selected' : '' }}>Early Leave</option>
                                </select>
                            </div>
                            @if($canViewAll)
                            <div class="col-md-3">
                                <label class="form-label">Location</label>
                                <select name="location_id" class="form-select">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search me-1"></i>Apply Filters
                                </button>
                                <a href="{{ route('modules.hr.attendance') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-refresh me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Table Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-list-ul me-2"></i>Attendance Records (Captured from Device)
                    </h6>
                    @if(!$canViewAll)
                    <span class="badge bg-info">
                        <i class="bx bx-info-circle me-1"></i>Showing only your attendance records
                    </span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Status</th>
                                    <th>Verify Mode</th>
                                    <th width="80">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $attendance)
                                <tr>
                                    <td>{{ $attendance->id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $attendance->user->name ?? 'N/A' }}</div>
                                        @if($attendance->user && $attendance->user->employee)
                                            <small class="text-muted">{{ $attendance->user->employee->employee_id ?? '' }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $attendance->attendance_date ? $attendance->attendance_date->format('Y-m-d') : ($attendance->check_in_time ? $attendance->check_in_time->format('Y-m-d') : 'N/A') }}</td>
                                    <td>
                                        @if($attendance->check_in_time)
                                            <span class="badge bg-success">{{ $attendance->check_in_time->format('H:i:s') }}</span>
                                        @elseif($attendance->time_in)
                                            <span class="badge bg-success">{{ is_string($attendance->time_in) ? $attendance->time_in : $attendance->time_in->format('H:i:s') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attendance->check_out_time)
                                            <span class="badge bg-warning text-dark">{{ $attendance->check_out_time->format('H:i:s') }}</span>
                                        @elseif($attendance->time_out)
                                            <span class="badge bg-warning text-dark">{{ is_string($attendance->time_out) ? $attendance->time_out : $attendance->time_out->format('H:i:s') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'present' => 'success',
                                                'absent' => 'danger',
                                                'late' => 'warning',
                                                'early_leave' => 'warning',
                                                'half_day' => 'info',
                                                'on_leave' => 'secondary',
                                            ];
                                            $color = $statusColors[$attendance->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $attendance->status ?? 'N/A')) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $attendance->verify_mode ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="viewAttendance({{ $attendance->id }})" title="View More Details">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            @if($canManage)
                                            <button type="button" class="btn btn-outline-danger" onclick="deleteAttendance({{ $attendance->id }})" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bx bx-inbox fs-1"></i>
                                            <p class="mt-2">No attendance records found. Click "Start Live Capture" to sync from device.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Showing {{ $attendances->firstItem() ?? 0 }} to {{ $attendances->lastItem() ?? 0 }} of {{ $attendances->total() }} records
                        </div>
                        <div>
                            {{ $attendances->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- View Attendance Details Modal -->
<div class="modal fade" id="viewAttendanceModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-info-circle me-2"></i>Attendance Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewAttendanceBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading attendance details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="downloadAttendancePDF()" id="downloadPdfBtn" style="display:none;">
                    <i class="bx bx-download me-1"></i>Download PDF
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    /* Ensure SweetAlert appears above Bootstrap modals */
    .swal2-container { z-index: 200000 !important; }
</style>
@endpush

@push('scripts')
<script>
// ZKTeco Sync Functions - Define FIRST to ensure they're always available
window.testZKTecoConnection = function() {
    const ip = document.getElementById('deviceIp')?.value;
    const port = document.getElementById('devicePort')?.value;
    const password = document.getElementById('devicePassword')?.value;
    
    if (!ip) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: 'Please enter device IP address',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Please enter device IP address');
        }
        return;
    }
    
    // Show progress modal
    const steps = [
        { step: 1, name: 'Checking PHP Sockets Extension', status: 'pending', message: 'Waiting...', progress: 0 },
        { step: 2, name: 'Validating Connection Parameters', status: 'pending', message: 'Waiting...', progress: 0 },
        { step: 3, name: 'Initializing ZKTeco Service', status: 'pending', message: 'Waiting...', progress: 0 },
        { step: 4, name: 'Creating Socket Connection', status: 'pending', message: 'Waiting...', progress: 0 },
        { step: 5, name: 'Authenticating with Device', status: 'pending', message: 'Waiting...', progress: 0 },
        { step: 6, name: 'Retrieving Device Information', status: 'pending', message: 'Waiting...', progress: 0 }
    ];
    
    let progressHtml = '<div class="connection-progress-container" style="text-align: left; max-width: 500px; margin: 0 auto;">';
    progressHtml += '<div class="progress mb-4" style="height: 30px;">';
    progressHtml += '<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" id="overallProgress" style="width: 0%">0%</div>';
    progressHtml += '</div>';
    progressHtml += '<div class="steps-list">';
    steps.forEach((step, index) => {
        progressHtml += `
            <div class="step-item mb-3 p-3 border rounded" id="step-${step.step}" style="background: #f8f9fa;">
                <div class="d-flex align-items-center mb-2">
                    <div class="step-icon me-3" id="step-icon-${step.step}">
                        <i class="bx bx-loader-circle bx-spin" style="font-size: 1.5rem; color: #0d6efd;"></i>
                    </div>
                    <div class="flex-grow-1">
                        <strong class="step-name" id="step-name-${step.step}">${step.name}</strong>
                        <div class="step-message text-muted small" id="step-message-${step.step}">${step.message}</div>
                    </div>
                </div>
            </div>
        `;
    });
    progressHtml += '</div></div>';
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Testing Connection...',
            html: progressHtml,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                // Start the connection test
                fetch('{{ route("zkteco.test-connection") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ip: ip,
                        port: port || 4370,
                        password: password || 0
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Update steps based on response
                    if (data.steps && Array.isArray(data.steps)) {
                        data.steps.forEach(step => {
                            updateStepStatus(step.step, step.status, step.message, step.progress);
                        });
                    }
                    
                    // Update overall progress
                    const overallProgress = data.steps ? Math.max(...data.steps.map(s => s.progress)) : 100;
                    updateOverallProgress(overallProgress);
                    
                    // Show final result
                    setTimeout(() => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                html: `
                                    <div class="text-center">
                                        <i class="bx bx-check-circle bx-lg text-success mb-3" style="font-size: 4rem;"></i>
                                        <h5 class="mb-2">Device Connected</h5>
                                        <p class="mb-0">Device is online and accessible</p>
                                        ${data.device_info ? `
                                            <div class="mt-3 text-start" style="max-width: 400px; margin: 0 auto;">
                                                <p class="mb-1"><strong>IP:</strong> ${data.device_info.ip || ip}</p>
                                                <p class="mb-1"><strong>Port:</strong> ${data.device_info.port || port || 4370}</p>
                                                <p class="mb-1"><strong>Model:</strong> ${data.device_info.model || 'UF200-S'}</p>
                                                <p class="mb-0"><strong>Firmware:</strong> ${data.device_info.firmware_version || 'N/A'}</p>
                                            </div>
                                        ` : ''}
                                    </div>
                                `,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                title: 'Connection Failed!',
                                html: `
                                    <div class="text-center">
                                        <i class="bx bx-x-circle bx-lg text-danger mb-3" style="font-size: 4rem;"></i>
                                        <h5 class="mb-2">Connection Failed</h5>
                                        <p class="mb-0">${data.message || 'Failed to connect to device'}</p>
                                    </div>
                                `,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    }, 500);
                })
                .catch(error => {
                    updateOverallProgress(0);
                    Swal.fire({
                        title: 'Error!',
                        text: error.message || 'An error occurred',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    } else {
        // Fallback without SweetAlert
        fetch('{{ route("zkteco.test-connection") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ip: ip,
                port: port || 4370,
                password: password || 0
            })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.success ? 'Device connected successfully!' : (data.message || 'Connection failed'));
        })
        .catch(error => {
            alert('Error: ' + (error.message || 'An error occurred'));
        });
    }
    
    function updateStepStatus(stepNum, status, message, progress) {
        const stepEl = document.getElementById(`step-${stepNum}`);
        const iconEl = document.getElementById(`step-icon-${stepNum}`);
        const messageEl = document.getElementById(`step-message-${stepNum}`);
        
        if (!stepEl || !iconEl || !messageEl) return;
        
        // Update message
        messageEl.textContent = message;
        
        // Update icon and styling based on status
        let iconClass = '';
        let iconColor = '';
        let bgColor = '';
        
        if (status === 'success') {
            iconClass = 'bx-check-circle';
            iconColor = '#198754';
            bgColor = '#d1e7dd';
        } else if (status === 'failed') {
            iconClass = 'bx-x-circle';
            iconColor = '#dc3545';
            bgColor = '#f8d7da';
        } else if (status === 'processing') {
            iconClass = 'bx-loader-circle bx-spin';
            iconColor = '#0d6efd';
            bgColor = '#cfe2ff';
        } else {
            iconClass = 'bx-time';
            iconColor = '#6c757d';
            bgColor = '#f8f9fa';
        }
        
        iconEl.innerHTML = `<i class="bx ${iconClass}" style="font-size: 1.5rem; color: ${iconColor};"></i>`;
        stepEl.style.background = bgColor;
    }
    
    function updateOverallProgress(progress) {
        const progressBar = document.getElementById('overallProgress');
        if (progressBar) {
            progressBar.style.width = progress + '%';
            progressBar.textContent = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
        }
    }
};

// Sync attendance from external API (only for users in local system)
window.syncAttendanceFromApi = function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Syncing from API...',
            html: 'Fetching attendance from external API and saving for your users...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    fetch('{{ route("zkteco.attendance.sync-from-api") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (typeof Swal !== 'undefined') {
            if (data.success) {
                Swal.fire({
                    title: 'Sync Complete!',
                    html: `
                        <div class="text-center">
                            <i class="bx bx-check-circle bx-lg text-success mb-3" style="font-size: 4rem;"></i>
                            <h5 class="mb-2">Attendance Synced from API</h5>
                            <p class="mb-1"><strong>Synced:</strong> ${data.synced || 0} records</p>
                            <p class="mb-1"><strong>Skipped:</strong> ${data.skipped || 0} records (users not in your system)</p>
                            ${data.errors > 0 ? `<p class="text-warning mb-0"><strong>Errors:</strong> ${data.errors}</p>` : ''}
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Sync Failed!',
                    text: data.message || 'Failed to sync attendance from API',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        } else {
            alert(data.success ? 'Attendance synced successfully!' : (data.message || 'Sync failed'));
            if (data.success) location.reload();
        }
    })
    .catch(error => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: error.message || 'An error occurred',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Error: ' + (error.message || 'An error occurred'));
        }
    });
};

window.syncZKTecoAttendance = function() {
    const ip = document.getElementById('deviceIp')?.value?.trim();
    const port = parseInt(document.getElementById('devicePort')?.value) || 4370;
    const password = parseInt(document.getElementById('devicePassword')?.value) || 0;
    
    if (!ip) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Error', 'Please enter device IP address', 'error');
        } else {
            alert('Please enter device IP address');
        }
        return;
    }
    
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Syncing Attendance...',
            html: 'Fetching attendance records directly from device and saving to database...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }
    
    // Use direct device connection (like enrollment page operations)
    fetch('{{ route("zkteco.attendance.sync") }}', {
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
        if (typeof Swal !== 'undefined') {
            if (data.success) {
                Swal.fire({
                    title: 'Sync Complete!',
                    html: `
                        <div class="text-center">
                            <i class="bx bx-check-circle bx-lg text-success mb-3" style="font-size: 4rem;"></i>
                            <h5 class="mb-2">Attendance Synced from Device</h5>
                            <p class="mb-1"><strong>Synced:</strong> ${data.synced || 0} records</p>
                            <p class="mb-1"><strong>Skipped:</strong> ${data.skipped || 0} records</p>
                            <p class="text-muted small mt-2">Data source: Direct device connection</p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Sync Failed!',
                    text: data.message || 'Failed to sync attendance from device',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        } else {
            alert(data.success ? 'Attendance synced successfully!' : (data.message || 'Sync failed'));
            if (data.success) location.reload();
        }
    })
    .catch(error => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: error.message || 'An error occurred',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Error: ' + (error.message || 'An error occurred'));
        }
    });
};

// Live Capture Variables
let autoCaptureInterval = null;
let autoCaptureRunning = true; // Always running
let syncCount = 0;
let lastSyncTime = null;
let isCapturing = false; // Prevent overlapping captures
let lastErrorMessage = null; // Store last error message for debugging

// Auto-start live capture when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Start automatic capture immediately
    startAutoCapture();
});

// Start automatic live capture (runs automatically)
function startAutoCapture() {
    const ip = document.getElementById('deviceIp')?.value?.trim();
    const port = parseInt(document.getElementById('devicePort')?.value) || 4370;
    const password = parseInt(document.getElementById('devicePassword')?.value) || 0;
    
    if (!ip) {
        updateCaptureStatus('Waiting for device IP...', 'warning');
        // Retry after 5 seconds if IP not set
        setTimeout(startAutoCapture, 5000);
        return;
    }
    
    autoCaptureRunning = true;
    const liveCaptureStatusEl = document.getElementById('liveCaptureStatus');
    if (liveCaptureStatusEl) {
        liveCaptureStatusEl.style.display = 'block';
    }
    
    // Initialize counters if not set
    if (document.getElementById('totalSyncsCount') && document.getElementById('totalSyncsCount').textContent === '0') {
        document.getElementById('totalSyncsCount').textContent = '0';
    }
    
    // Update status
    updateCaptureStatus('Initializing...', 'info');
    
    // Perform initial capture immediately
    setTimeout(() => {
        performLiveCapture(ip, port, password);
    }, 500); // Small delay to ensure UI is ready
    
    // Set up interval for automatic capture every SECOND (real-time)
    autoCaptureInterval = setInterval(function() {
        if (autoCaptureRunning && !isCapturing) {
            performLiveCapture(ip, port, password);
        }
    }, 1000); // Every 1 second for real-time capture
}

// Perform live capture operation (real-time every second)
function performLiveCapture(ip, port, password) {
    // Prevent overlapping captures
    if (isCapturing) {
        return;
    }
    
    isCapturing = true;
    updateCaptureStatus('Syncing...', 'info');
    const startTime = new Date();
    
    // Safety timeout: reset isCapturing flag after 30 seconds if still stuck
    const safetyTimeout = setTimeout(() => {
        if (isCapturing) {
            console.warn('Live capture timeout - resetting flag');
            isCapturing = false;
            updateCaptureStatus('Timeout - Retrying...', 'warning');
        }
    }, 30000);
    
    // Step 1: Capture users from device (like enrollment page)
    captureUsersFromDevice(ip, port, password)
        .then(usersResult => {
            // Step 2: Sync attendance from device to database (this captures new attempts)
            return syncAttendanceFromDevice(ip, port, password)
                .then(attendanceResult => {
                    return { users: usersResult, attendance: attendanceResult };
                });
        })
        .then(results => {
            clearTimeout(safetyTimeout);
            syncCount++;
            lastSyncTime = new Date();
            
            // Update status
            const usersCount = parseInt(document.getElementById('usersCapturedCount').textContent) || 0;
            const attendanceCount = parseInt(document.getElementById('attendanceSyncedCount').textContent) || 0;
            
            document.getElementById('usersCapturedCount').textContent = results.users?.total || usersCount;
            document.getElementById('attendanceSyncedCount').textContent = (attendanceCount + (results.attendance?.synced || 0));
            document.getElementById('totalSyncsCount').textContent = syncCount;
            document.getElementById('lastSyncTime').textContent = lastSyncTime.toLocaleTimeString();
            
            // Calculate next sync time (1 second)
            const nextSync = new Date(lastSyncTime.getTime() + 1000);
            document.getElementById('nextSyncTime').textContent = nextSync.toLocaleTimeString();
            
            updateCaptureStatus('Synced - ' + lastSyncTime.toLocaleTimeString(), 'success');
            
            // Only show notification if new records were synced
            if (results.attendance?.synced > 0 && typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'New Records Captured!',
                    html: `
                        <p><strong>New Attendance:</strong> ${results.attendance.synced} record(s)</p>
                        <p class="text-muted small">Captured at ${lastSyncTime.toLocaleTimeString()}</p>
                    `,
                    icon: 'success',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                
                // Reload table to show new records
                setTimeout(function() {
                    location.reload();
                }, 2000);
            }
        })
        .catch(async error => {
            clearTimeout(safetyTimeout);
            console.error('Live capture error:', error);
            
            // Extract detailed error message
            let errorMsg = 'Connection issue';
            if (error.message) {
                errorMsg = error.message;
            }
            
            // Still update sync count and time to show activity
            syncCount++;
            const errorTime = new Date();
            lastSyncTime = errorTime;
            
            // Update UI to show attempt was made
            const totalSyncsEl = document.getElementById('totalSyncsCount');
            if (totalSyncsEl) {
                totalSyncsEl.textContent = syncCount;
            }
            
            const lastSyncEl = document.getElementById('lastSyncTime');
            if (lastSyncEl) {
                lastSyncEl.textContent = errorTime.toLocaleTimeString();
            }
            
            const nextSyncEl = document.getElementById('nextSyncTime');
            if (nextSyncEl) {
                const nextSync = new Date(errorTime.getTime() + 1000);
                nextSyncEl.textContent = nextSync.toLocaleTimeString();
            }
            
            // Show error status with more detail (truncate if too long)
            const shortError = errorMsg.length > 40 ? errorMsg.substring(0, 37) + '...' : errorMsg;
            updateCaptureStatus('Error: ' + shortError, 'danger', errorMsg);
            // Don't show error notifications for every failed attempt to avoid spam
        })
        .finally(() => {
            clearTimeout(safetyTimeout);
            isCapturing = false;
        });
}

// Capture users from device (similar to enrollment page)
function captureUsersFromDevice(ip, port, password) {
    // Create abort controller for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 15000); // 15 second timeout
    
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
        }),
        signal: controller.signal
    })
    .then(async response => {
        clearTimeout(timeoutId);
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
        }
        if (data.success) {
            return { total: data.total || 0, users: data.users || [] };
        } else {
            throw new Error(data.message || 'Failed to capture users');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        if (error.name === 'AbortError') {
            throw new Error('Request timeout - device may be unreachable');
        }
        throw error;
    });
}

// Sync attendance from device to database (using direct device connection)
function syncAttendanceFromDevice(ip, port, password) {
    // Create abort controller for timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 15000); // 15 second timeout
    
    return fetch('{{ route("zkteco.attendance.sync") }}', {
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
        }),
        signal: controller.signal
    })
    .then(async response => {
        clearTimeout(timeoutId);
        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
        }
        if (data.success) {
            return { synced: data.synced || 0, skipped: data.skipped || 0 };
        } else {
            throw new Error(data.message || 'Failed to sync attendance');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        if (error.name === 'AbortError') {
            throw new Error('Request timeout - device may be unreachable');
        }
        throw error;
    });
}

// Update capture status display
function updateCaptureStatus(status, type, errorDetails = null) {
    const badge = document.getElementById('captureStatusBadge');
    const text = document.getElementById('captureStatusText');
    const errorDetailsEl = document.getElementById('errorDetails');
    
    if (badge) {
        badge.textContent = status;
        badge.className = 'badge bg-' + type + ' text-white ms-2';
        if (errorDetails) {
            badge.setAttribute('title', errorDetails);
            badge.setAttribute('data-bs-toggle', 'tooltip');
        }
    }
    
    if (text) {
        text.textContent = status;
        text.className = 'badge bg-' + type;
        if (errorDetails) {
            text.setAttribute('title', errorDetails);
            text.setAttribute('data-bs-toggle', 'tooltip');
            // Initialize tooltip if Bootstrap is available
            if (typeof bootstrap !== 'undefined') {
                const tooltip = bootstrap.Tooltip.getInstance(text);
                if (tooltip) {
                    tooltip.dispose();
                }
                new bootstrap.Tooltip(text);
            }
        }
    }
    
    // Show error details below status if available
    if (errorDetailsEl) {
        if (errorDetails && type === 'danger') {
            errorDetailsEl.textContent = 'Last error: ' + errorDetails;
            errorDetailsEl.style.display = 'block';
            errorDetailsEl.className = 'mb-0 small text-danger';
        } else {
            errorDetailsEl.style.display = 'none';
        }
    }
    
    lastErrorMessage = errorDetails;
}

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (autoCaptureInterval) {
        clearInterval(autoCaptureInterval);
    }
});

// Load SweetAlert2 first
(function() {
    const script = document.createElement('script');
    script.src = '{{ asset("assets/vendor/libs/sweetalert2/sweetalert2.min.js") }}';
    script.onload = function() {
        console.log('SweetAlert2 loaded successfully');
        // Initialize attendance functions after Swal is loaded
        if (typeof initAttendanceFunctions === 'function') {
            initAttendanceFunctions();
        }
    };
    script.onerror = function() {
        console.error('Failed to load SweetAlert2, using fallback');
        createSwalFallback();
    };
    document.head.appendChild(script);
})();

// Create fallback if SweetAlert2 fails to load
function createSwalFallback() {
    window.Swal = {
        fire: function(options) {
            if (typeof options === 'string') {
                alert(options);
                return Promise.resolve({ isConfirmed: true });
            }
            
            const title = options.title || '';
            const html = options.html || options.text || '';
            const icon = options.icon || 'info';
            const confirmButtonText = options.confirmButtonText || 'OK';
            const cancelButtonText = options.cancelButtonText || 'Cancel';
            const showCancelButton = options.showCancelButton || false;
            const timer = options.timer || null;
            const preConfirm = options.preConfirm;
            const showLoaderOnConfirm = options.showLoaderOnConfirm || false;
            
            return new Promise((resolve) => {
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    z-index: 200000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                `;
                
                const dialog = document.createElement('div');
                dialog.style.cssText = `
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    max-width: 500px;
                    width: 90%;
                    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                `;
                
                const iconMap = {
                    'success': '✓',
                    'error': '✗',
                    'warning': '⚠',
                    'info': 'ℹ',
                    'question': '?'
                };
                
                const iconColor = icon === 'success' ? '#28a745' : icon === 'error' ? '#dc3545' : icon === 'warning' ? '#ffc107' : '#17a2b8';
                
                dialog.innerHTML = `
                    <div style="text-align: center; margin-bottom: 20px;">
                        <div style="font-size: 60px; color: ${iconColor};">
                            ${iconMap[icon] || 'ℹ'}
                        </div>
                    </div>
                    <h3 style="margin-bottom: 15px; text-align: center;">${title}</h3>
                    <div style="margin-bottom: 20px;">${html}</div>
                    <div id="swal-loader" style="text-align: center; margin-bottom: 15px; display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div style="text-align: center;">
                        ${showCancelButton ? `<button class="btn btn-secondary" id="swal-cancel" style="margin-right: 10px;">${cancelButtonText}</button>` : ''}
                        <button class="btn btn-primary" id="swal-confirm">${confirmButtonText}</button>
                    </div>
                `;
                
                modal.appendChild(dialog);
                document.body.appendChild(modal);
                
                const confirmBtn = dialog.querySelector('#swal-confirm');
                const cancelBtn = dialog.querySelector('#swal-cancel');
                const loader = dialog.querySelector('#swal-loader');
                
                const closeModal = (result) => {
                    if (modal.parentNode) {
                        modal.remove();
                    }
                    resolve(result);
                };
                
                confirmBtn.addEventListener('click', async function() {
                    if (preConfirm) {
                        if (showLoaderOnConfirm) {
                            loader.style.display = 'block';
                            confirmBtn.disabled = true;
                            if (cancelBtn) cancelBtn.disabled = true;
                        }
                        try {
                            const preResult = await preConfirm();
                            if (preResult !== false) {
                                closeModal({ isConfirmed: true, value: preResult });
                            } else {
                                loader.style.display = 'none';
                                confirmBtn.disabled = false;
                                if (cancelBtn) cancelBtn.disabled = false;
                            }
                        } catch (error) {
                            loader.style.display = 'none';
                            confirmBtn.disabled = false;
                            if (cancelBtn) cancelBtn.disabled = false;
                            console.error('Pre-confirm error:', error);
                        }
                    } else {
                        closeModal({ isConfirmed: true });
                    }
                });
                
                if (cancelBtn) {
                    cancelBtn.addEventListener('click', function() {
                        closeModal({ isConfirmed: false, isDismissed: true });
                    });
                }
                
                if (timer) {
                    setTimeout(() => {
                        if (modal.parentNode) {
                            closeModal({ isConfirmed: true, isDismissed: true });
                        }
                    }, timer);
                }
            });
        },
        close: function() {
            const modals = document.querySelectorAll('[style*="z-index: 200000"]');
            modals.forEach(m => m.remove());
        }
    };
}

let currentAttendance = null;

// Initialize attendance functions
function initAttendanceFunctions() {
    // ZKTeco automatic attendance - no manual clocking needed
}

// Wait for DOM and Swal to be ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if Swal is already loaded
    if (typeof Swal !== 'undefined') {
        initAttendanceFunctions();
    } else {
        // Wait a bit for Swal to load
        setTimeout(function() {
            if (typeof Swal === 'undefined') {
                console.warn('Swal still not loaded, creating fallback');
                createSwalFallback();
            }
            initAttendanceFunctions();
        }, 100);
    }
});

// Helper function to extract time string from datetime or time value
function extractTimeString(timeValue) {
    if (!timeValue) return null;
    
    // If it's already a time string (HH:MM:SS or HH:MM)
    if (typeof timeValue === 'string' && /^\d{1,2}:\d{2}(:\d{2})?$/.test(timeValue)) {
        return timeValue;
    }
    
    // If it's a datetime string, extract time part
    if (typeof timeValue === 'string' && timeValue.includes(' ')) {
        const parts = timeValue.split(' ');
        if (parts.length >= 2) {
            return parts[1].substring(0, 8); // Get HH:MM:SS
        }
    }
    
    // Try to parse as date and extract time
    try {
        const date = new Date(timeValue);
        if (!isNaN(date.getTime())) {
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            return `${hours}:${minutes}:${seconds}`;
        }
    } catch (e) {
        console.warn('Could not parse time value:', timeValue);
    }
    
    return null;
}

// Helper function to format time for display
function formatTime(timeValue) {
    const timeStr = extractTimeString(timeValue);
    if (!timeStr) return 'N/A';
    
    try {
        const [hours, minutes] = timeStr.split(':');
        const hour12 = parseInt(hours) % 12 || 12;
        const ampm = parseInt(hours) >= 12 ? 'PM' : 'AM';
        return `${hour12}:${minutes} ${ampm}`;
    } catch (e) {
        return timeStr; // Return as-is if formatting fails
    }
}

// Helper function to calculate time difference in hours and minutes
function calculateTimeDiff(timeInValue, timeOutValue) {
    const timeInStr = extractTimeString(timeInValue);
    const timeOutStr = extractTimeString(timeOutValue);
    
    if (!timeInStr || !timeOutStr) return { hours: 0, minutes: 0 };
    
    try {
        const [inHours, inMinutes, inSeconds = 0] = timeInStr.split(':').map(Number);
        const [outHours, outMinutes, outSeconds = 0] = timeOutStr.split(':').map(Number);
        
        const inTotalSeconds = inHours * 3600 + inMinutes * 60 + inSeconds;
        const outTotalSeconds = outHours * 3600 + outMinutes * 60 + outSeconds;
        
        let diffSeconds = outTotalSeconds - inTotalSeconds;
        
        // Handle overnight (time out is next day)
        if (diffSeconds < 0) {
            diffSeconds += 24 * 3600;
        }
        
        const hours = Math.floor(diffSeconds / 3600);
        const minutes = Math.floor((diffSeconds % 3600) / 60);
        
        return { hours, minutes };
    } catch (e) {
        console.warn('Error calculating time difference:', e);
        return { hours: 0, minutes: 0 };
    }
}

// Manual clock functions removed - using ZKTeco automatic attendance only
// All attendance is now automatic via ZKTeco biometric devices
// ZKTeco functions (testZKTecoConnection, syncZKTecoAttendance) are defined at the top of this script

@if($canManage)
// Attendance form handling
document.getElementById('attendanceForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    const isEdit = document.getElementById('attendanceId').value;
    
    const url = isEdit 
        ? `{{ url('attendance') }}/${isEdit}`
        : '{{ route("attendance.store") }}';
    const method = isEdit ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const data = await response.json();
        
        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: data.message || 'Attendance record saved successfully',
                icon: 'success',
                confirmButtonText: 'OK',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
            location.reload();
            });
        } else {
            let errorMessage = data.message || 'Failed to save attendance record';
            if (data.errors) {
                const errorList = Object.values(data.errors).flat();
                errorMessage = errorList.join('<br>');
            }
            
            Swal.fire({
                title: 'Error!',
                html: `<p class="text-danger">${errorMessage}</p>`,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'An error occurred. Please try again.',
            icon: 'error',
            confirmButtonText: 'OK'
        });
    });
});

window.deleteAttendance = function(id) {
    if (!id) {
        Swal.fire({
            title: 'Error!',
            text: 'Invalid attendance ID',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    Swal.fire({
        title: 'Delete Attendance Record?',
        html: `
            <div class="text-center">
                <i class="bx bx-trash bx-lg text-danger mb-3" style="font-size: 4rem;"></i>
                <p class="mb-2">Are you sure you want to delete this attendance record?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-check me-1"></i>Yes, Delete',
        cancelButtonText: '<i class="bx bx-x me-1"></i>Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
    
    fetch(`{{ url('attendance') }}/${id}`, {
        method: 'DELETE',
        headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: data.message || 'Attendance record deleted successfully',
                        icon: 'success',
                        confirmButtonText: 'OK',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
            location.reload();
                    });
        } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Failed to delete attendance record',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'An error occurred while deleting. Please try again.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }
    });
};

@endif

// View attendance function - available to all users
function viewAttendance(id) {
    if (!id) {
        Swal.fire({
            title: 'Error!',
            text: 'Invalid attendance ID',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const modal = document.getElementById('viewAttendanceModal');
    const modalBody = document.getElementById('viewAttendanceBody');
    
    // Show loading state
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading attendance details...</p>
        </div>
    `;
    
    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Fetch attendance data
    fetch(`{{ url('attendance') }}/${id}`, {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.attendance) {
            const att = data.attendance;
            const user = att.user || {};
            const employee = att.employee || {};
            const department = employee.department || {};
            const approver = att.approver || {};
            const schedule = att.work_schedule || {};
            const policy = data.policy || {};
            const lateDetails = data.late_details || null;
            const earlyLeaveDetails = data.early_leave_details || null;
            const timeSpentDetails = data.time_spent_details || null;
            
            // Store attendance ID for PDF download
            window.currentAttendanceId = id;
            document.getElementById('downloadPdfBtn').style.display = 'inline-block';
            
            // Format dates and times - prioritize check_in_time/check_out_time
            const attendanceDate = new Date(att.attendance_date).toLocaleDateString('en-US', { 
                year: 'numeric', month: 'long', day: 'numeric' 
            });
            const timeIn = formatTime(att.check_in_time || att.time_in);
            const timeOut = formatTime(att.check_out_time || att.time_out);
            
            // Calculate total hours from check_in_time and check_out_time
            let totalHours = 'N/A';
            if (att.check_in_time && att.check_out_time) {
                try {
                    const checkIn = new Date(att.check_in_time);
                    const checkOut = new Date(att.check_out_time);
                    const diffMs = checkOut - checkIn;
                    const diffMinutes = Math.floor(diffMs / (1000 * 60));
                    const hours = Math.floor(diffMinutes / 60);
                    const minutes = diffMinutes % 60;
                    totalHours = hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
                } catch (e) {
                    // Fallback to total_hours if calculation fails
                    if (att.total_hours) {
                        const hours = Math.floor(att.total_hours / 60);
                        const minutes = att.total_hours % 60;
                        totalHours = hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
                    }
                }
            } else if (att.total_hours) {
                const hours = Math.floor(att.total_hours / 60);
                const minutes = att.total_hours % 60;
                totalHours = hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
            } else if (timeIn !== 'N/A' && timeOut !== 'N/A') {
                // Try to calculate from formatted time strings
                try {
                    const inTimeStr = att.check_in_time_formatted || (att.check_in_time ? new Date(att.check_in_time).toTimeString().substring(0, 8) : null);
                    const outTimeStr = att.check_out_time_formatted || (att.check_out_time ? new Date(att.check_out_time).toTimeString().substring(0, 8) : null);
                    
                    if (inTimeStr && outTimeStr) {
                        const [inHours, inMins] = inTimeStr.split(':').map(Number);
                        const [outHours, outMins] = outTimeStr.split(':').map(Number);
                        const inTotalMins = inHours * 60 + inMins;
                        const outTotalMins = outHours * 60 + outMins;
                        let diffMins = outTotalMins - inTotalMins;
                        if (diffMins < 0) diffMins += 24 * 60; // Handle overnight
                        const hours = Math.floor(diffMins / 60);
                        const minutes = diffMins % 60;
                        totalHours = hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
                    }
                } catch (e) {
                    totalHours = 'N/A';
                }
            }
            
            // Status badges - handle status_code if status is numeric
            let statusValue = att.status;
            if (typeof statusValue === 'number' || (typeof statusValue === 'string' && /^\d+$/.test(statusValue))) {
                // Convert status_code to status text
                const statusMap = {
                    0: 'present',
                    1: 'absent',
                    2: 'late',
                    3: 'early_leave',
                    4: 'half_day',
                    5: 'on_leave'
                };
                statusValue = statusMap[statusValue] || 'present';
            }
            
            const statusColors = {
                'present': 'success',
                'absent': 'danger',
                'late': 'warning',
                'early_leave': 'warning',
                'half_day': 'info',
                'on_leave': 'secondary'
            };
            const statusColor = statusColors[statusValue] || 'secondary';
            const statusText = statusValue ? statusValue.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'N/A';
            
            // Verification status - auto-verify biometric records
            let verificationStatus = att.verification_status;
            if (!verificationStatus || verificationStatus === 'pending') {
                // Auto-verify if it's a biometric method
                const biometricMethods = ['biometric', 'fingerprint', 'face_recognition', 'rfid'];
                if (att.attendance_method && biometricMethods.includes(att.attendance_method.toLowerCase())) {
                    verificationStatus = 'verified';
                }
            }
            
            const verificationColors = {
                'pending': 'warning',
                'verified': 'success',
                'rejected': 'danger'
            };
            const verificationColor = verificationColors[verificationStatus] || 'secondary';
            const verificationText = verificationStatus ? verificationStatus.charAt(0).toUpperCase() + verificationStatus.slice(1) : 'N/A';
            
            // Method display - show proper method name
            let methodText = att.attendance_method || 'N/A';
            if (methodText && methodText !== 'N/A') {
                const methodMap = {
                    'biometric': 'Biometric',
                    'fingerprint': 'Fingerprint',
                    'face_recognition': 'Face Recognition',
                    'rfid': 'RFID',
                    'manual': 'Manual',
                    'mobile_app': 'Mobile App',
                    'card_swipe': 'Card Swipe'
                };
                methodText = methodMap[methodText.toLowerCase()] || methodText.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            }
            
            // Flags
            const flags = [];
            if (att.is_late) flags.push('<span class="badge bg-warning">Late</span>');
            if (att.is_early_leave) flags.push('<span class="badge bg-warning">Early Leave</span>');
            if (att.is_overtime) flags.push('<span class="badge bg-info">Overtime</span>');
            
            // Location info
            let locationInfo = '';
            if (att.location_name || att.location || (att.latitude && att.longitude)) {
                const locationText = att.location_name || att.location || `${att.latitude}, ${att.longitude}`;
                const locationDetails = att.attendance_location ? `<br><small class="text-muted">Office: ${att.attendance_location.name}</small>` : '';
                locationInfo = `
                    <tr>
                        <th>Location:</th>
                        <td>${locationText}${locationDetails}</td>
                    </tr>
                `;
            }
            
            // Device info
            let deviceInfo = '';
            if (att.device_type || att.device_id || att.device_ip) {
                deviceInfo = `
                    <tr>
                        <th>Device Type:</th>
                        <td>${att.device_type || 'N/A'}</td>
                    </tr>
                    ${att.device_id ? `
                    <tr>
                        <th>Device ID:</th>
                        <td>${att.device_id}</td>
                    </tr>
                    ` : ''}
                    ${att.device_ip ? `
                    <tr>
                        <th>Device IP:</th>
                        <td><code>${att.device_ip}</code></td>
                    </tr>
                    ` : ''}
                `;
            }
            
            // IP Address
            let ipInfo = '';
            if (att.ip_address) {
                ipInfo = `
                    <tr>
                        <th>IP Address:</th>
                        <td><code>${att.ip_address}</code></td>
                    </tr>
                `;
            }
            
            // Approved by
            let approverInfo = '';
            if (att.approved_by && approver.name) {
                approverInfo = `
                    <tr>
                        <th>Approved By:</th>
                        <td>${approver.name} ${att.approved_at ? '(on ' + new Date(att.approved_at).toLocaleDateString() + ')' : ''}</td>
                    </tr>
                `;
            }
            
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0 text-white"><i class="bx bx-user me-2"></i>Employee Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Name:</th>
                                        <td><strong>${user.name || 'N/A'}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Employee ID:</th>
                                        <td>${user.employee_id || employee.employee_id || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Enroll ID:</th>
                                        <td><code>${att.enroll_id || 'N/A'}</code></td>
                                    </tr>
                                    <tr>
                                        <th>Department:</th>
                                        <td>${department.name || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Position:</th>
                                        <td>${employee.position || 'N/A'}</td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td>${user.email || 'N/A'}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0 text-white"><i class="bx bx-time me-2"></i>Attendance Details</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Date:</th>
                                        <td><strong>${attendanceDate}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td><span class="badge bg-${statusColor}">${statusText}</span> ${flags.join(' ')}</td>
                                    </tr>
                                    <tr>
                                        <th>Verification:</th>
                                        <td><span class="badge bg-${verificationColor}">${verificationText}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Method:</th>
                                        <td>${methodText}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0 text-white"><i class="bx bx-time-five me-2"></i>Time Records</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <th width="40%">Check In:</th>
                                        <td><strong>${timeIn}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Check Out:</th>
                                        <td><strong>${timeOut}</strong></td>
                                    </tr>
                                    <tr>
                                        <th>Total Hours:</th>
                                        <td><strong>${totalHours}</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0 text-white"><i class="bx bx-info-circle me-2"></i>Additional Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm table-borderless mb-0">
                                    ${locationInfo}
                                    ${deviceInfo}
                                    ${ipInfo}
                                    ${approverInfo}
                                    ${att.notes ? `
                                    <tr>
                                        <th>Notes:</th>
                                        <td>${att.notes}</td>
                                    </tr>
                                    ` : ''}
                                    <tr>
                                        <th>Created At:</th>
                                        <td>${new Date(att.created_at).toLocaleString()}</td>
                                    </tr>
                                    <tr>
                                        <th>Updated At:</th>
                                        <td>${new Date(att.updated_at).toLocaleString()}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
    } else {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${data.message || 'Failed to load attendance details'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = `
            <div class="alert alert-danger">
                <i class="bx bx-error-circle me-2"></i>
                <strong>Error:</strong> Failed to load attendance details. Please try again.
            </div>
        `;
    });
}

// Download PDF for attendance
function downloadAttendancePDF(id) {
    if (!id) {
        id = window.currentAttendanceId;
    }
    if (!id) {
        Swal.fire({
            title: 'Error!',
            text: 'Invalid attendance ID',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    window.location.href = `{{ url('attendance') }}/${id}/download-pdf`;
}

// Delete attendance function
@if($canManage)
function deleteAttendance(id) {
    if (!id) {
        Swal.fire({
            title: 'Error!',
            text: 'Invalid attendance ID',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    Swal.fire({
        title: 'Delete Attendance Record?',
        text: 'Are you sure you want to delete this attendance record? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ url('attendance') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Attendance record has been deleted.', 'success');
                    // Reload the page to refresh the table
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Error!', data.message || 'Failed to delete attendance record', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to delete attendance record', 'error');
            });
        }
    });
}

// Delete all attendance records
function deleteAllAttendance() {
    Swal.fire({
        title: 'Delete ALL Attendance Records?',
        html: `
            <div class="text-center">
                <i class="bx bx-error-circle bx-lg text-danger mb-3" style="font-size: 4rem;"></i>
                <h5 class="mb-2">Warning: This will delete ALL attendance records!</h5>
                <p class="mb-3 text-danger"><strong>This action cannot be undone!</strong></p>
                <p class="text-start mb-0">
                    <strong>Are you absolutely sure?</strong><br>
                    All attendance data will be permanently deleted from the database.
                </p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete ALL!',
        cancelButtonText: 'Cancel',
        input: 'text',
        inputPlaceholder: 'Type "DELETE ALL" to confirm',
        inputValidator: (value) => {
            if (value !== 'DELETE ALL') {
                return 'You must type "DELETE ALL" to confirm';
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete all attendance records.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`{{ url('attendance') }}/delete-all`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', `Successfully deleted ${data.deleted_count || 0} attendance record(s).`, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    Swal.fire('Error!', data.message || 'Failed to delete attendance records', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error!', 'Failed to delete attendance records', 'error');
            });
        }
    });
}
@endif

// Verify attendance function (HR and System Admin only)
@if($canManage)
function verifyAttendance(id, status) {
    if (!id || !status) {
        Swal.fire({
            title: 'Error!',
            text: 'Invalid parameters',
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    const statusText = status === 'verified' ? 'Verify' : 'Reject';
    const statusIcon = status === 'verified' ? 'success' : 'warning';
    const statusColor = status === 'verified' ? '#28a745' : '#dc3545';
    
    Swal.fire({
        title: `${statusText} Attendance?`,
        html: `
            <div class="text-center">
                <i class="bx bx-${status === 'verified' ? 'check-circle' : 'x-circle'} bx-lg text-${statusIcon} mb-3" style="font-size: 4rem;"></i>
                <p class="mb-2">Are you sure you want to ${statusText.toLowerCase()} this attendance record?</p>
                <div class="form-group mt-3">
                    <label class="form-label">Notes (Optional):</label>
                    <textarea id="verificationNotes" class="form-control" rows="3" placeholder="Add verification notes..."></textarea>
                </div>
            </div>
        `,
        icon: statusIcon,
        showCancelButton: true,
        confirmButtonText: `<i class="bx bx-check me-1"></i>Yes, ${statusText}`,
        cancelButtonText: '<i class="bx bx-x me-1"></i>Cancel',
        confirmButtonColor: statusColor,
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const notes = document.getElementById('verificationNotes').value;
            const verifyUrl = `{{ url('attendance') }}/${id}/verify`;
            return fetch(verifyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    verification_status: status,
                    notes: notes || null,
                    _token: '{{ csrf_token() }}'
                })
            })
            .then(async response => {
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to verify attendance');
                }
                return data;
            });
        }
    })
    .then((result) => {
        if (result.isConfirmed && result.value) {
            Swal.fire({
                title: 'Success!',
                text: result.value.message || `Attendance ${statusText.toLowerCase()}ed successfully`,
                icon: 'success',
                confirmButtonText: '<i class="bx bx-check me-1"></i>OK',
                confirmButtonColor: '#28a745',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                location.reload();
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            html: `
                <div class="text-center">
                    <i class="bx bx-error-circle bx-lg text-danger mb-3" style="font-size: 4rem;"></i>
                    <h5 class="mb-2">Failed to ${statusText} Attendance</h5>
                    <p class="text-danger">${error.message || 'An error occurred. Please try again.'}</p>
                </div>
            `,
            icon: 'error',
            confirmButtonText: '<i class="bx bx-check me-1"></i>OK',
            confirmButtonColor: '#dc3545'
        });
    });
}
@endif

// Load device attendance from API via Laravel backend (to avoid CORS)
window.loadDeviceAttendance = function(page = 1) {
    const loadingEl = document.getElementById('deviceAttendanceLoading');
    const errorEl = document.getElementById('deviceAttendanceError');
    const errorMessageEl = document.getElementById('deviceAttendanceErrorMessage');
    const tableContainer = document.getElementById('deviceAttendanceTableContainer');
    const tableBody = document.getElementById('deviceAttendanceTableBody');
    const countEl = document.getElementById('deviceAttendanceCount');
    const paginationEl = document.getElementById('deviceAttendancePagination');
    
    // Show loading, hide error and table
    if (loadingEl) loadingEl.style.display = 'block';
    if (errorEl) errorEl.style.display = 'none';
    if (tableContainer) tableContainer.style.display = 'none';
    if (tableBody) tableBody.innerHTML = '';
    
    // Fetch attendance from Laravel backend (which proxies to device API)
    const url = '{{ route("zkteco.attendance.device") }}' + '?page=' + page + '&per_page=50';
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(async response => {
        const responseData = await response.json();
        
        // Check if response indicates an error
        if (!response.ok || !responseData.success) {
            let errorMessage = responseData.message || `HTTP error! status: ${response.status}`;
            
            // Add details if available
            if (responseData.details && Array.isArray(responseData.details)) {
                errorMessage += '\n\n' + responseData.details.join('\n');
            }
            
            throw new Error(errorMessage);
        }
        
        return responseData;
    })
    .then(data => {
        if (loadingEl) loadingEl.style.display = 'none';
        
        if (data.success && data.data && Array.isArray(data.data)) {
            const attendances = data.data;
            const pagination = data.pagination || {};
            
            // Display records
            if (tableBody) {
                tableBody.innerHTML = '';
                
                if (attendances.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bx bx-inbox fs-1"></i>
                                    <p class="mt-2">No attendance records found on device</p>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    attendances.forEach(att => {
                        const user = att.user || {};
                        const checkIn = att.check_in_time ? new Date(att.check_in_time).toLocaleString() : '-';
                        const checkOut = att.check_out_time ? new Date(att.check_out_time).toLocaleString() : '-';
                        const date = att.attendance_date ? new Date(att.attendance_date).toLocaleDateString() : '-';
                        
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${att.id || '-'}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            ${(user.name || 'N/A').substring(0, 2).toUpperCase()}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">${user.name || 'N/A'}</div>
                                        <small class="text-muted">ID: ${user.id || '-'}</small>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-label-info">${user.enroll_id || '-'}</span></td>
                            <td>${date}</td>
                            <td>
                                ${checkIn !== '-' ? `<span class="badge bg-label-success">${checkIn}</span>` : '<span class="text-muted">-</span>'}
                            </td>
                            <td>
                                ${checkOut !== '-' ? `<span class="badge bg-label-danger">${checkOut}</span>` : '<span class="text-muted">-</span>'}
                            </td>
                            <td>
                                <span class="badge bg-label-${att.status === '1' ? 'success' : 'warning'}">
                                    ${att.status === '1' ? 'Present' : 'Pending'}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">${att.verify_mode || 'Fingerprint'}</span>
                            </td>
                            <td>
                                <small class="text-muted">${att.device_ip || '-'}</small>
                            </td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
            }
            
            // Update count
            if (countEl) {
                countEl.textContent = pagination.total || attendances.length;
            }
            
            // Update pagination
            if (paginationEl && pagination.last_page > 1) {
                let paginationHtml = '<nav><ul class="pagination pagination-sm mb-0">';
                
                // Previous button
                if (pagination.current_page > 1) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadDeviceAttendancePage(${pagination.current_page - 1}); return false;">Previous</a></li>`;
                }
                
                // Page numbers
                for (let i = 1; i <= pagination.last_page; i++) {
                    if (i === pagination.current_page) {
                        paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                    } else {
                        paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadDeviceAttendancePage(${i}); return false;">${i}</a></li>`;
                    }
                }
                
                // Next button
                if (pagination.current_page < pagination.last_page) {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadDeviceAttendancePage(${pagination.current_page + 1}); return false;">Next</a></li>`;
                }
                
                paginationHtml += '</ul></nav>';
                paginationEl.innerHTML = paginationHtml;
            } else {
                paginationEl.innerHTML = '';
            }
            
            // Show table
            if (tableContainer) tableContainer.style.display = 'block';
            
        } else {
            throw new Error(data.message || 'Invalid response format');
        }
    })
    .catch(error => {
        if (loadingEl) loadingEl.style.display = 'none';
        if (errorEl) {
            errorEl.style.display = 'block';
            if (errorMessageEl) {
                // Format error message - handle multiline messages
                const errorText = error.message || 'Unknown error occurred';
                errorMessageEl.innerHTML = errorText.replace(/\n/g, '<br>');
            }
            
            // Show additional troubleshooting info
            const errorDetailsEl = document.getElementById('deviceAttendanceErrorDetails');
            if (errorDetailsEl) {
                errorDetailsEl.innerHTML = `
                    <hr>
                    <strong>Troubleshooting:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Verify the device API is running at <code>http://192.168.100.100:8000</code></li>
                        <li>Check if the server can reach the device API (network connectivity)</li>
                        <li>Verify the API endpoint <code>/api/v1/attendances</code> is accessible</li>
                        <li>Check server logs for detailed error information</li>
                    </ul>
                `;
            }
        }
        console.error('Error loading device attendance:', error);
    });
};

// Load device attendance with pagination (simplified - just calls main function)
window.loadDeviceAttendancePage = function(page) {
    loadDeviceAttendance(page);
};

// Test device API connection with detailed diagnostics
window.testDeviceApiConnection = function() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Testing API Connection...',
            html: '<div class="text-center"><div class="spinner-border text-primary mb-2"></div><p>Running diagnostic tests...</p></div>',
            allowOutsideClick: false,
            showConfirmButton: false
        });
    }
    
    const url = '{{ route("zkteco.attendance.device.test") }}';
    
    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(async response => {
        const data = await response.json();
        
        if (typeof Swal !== 'undefined') {
            // Build detailed results HTML
            let resultsHtml = '<div class="text-start">';
            resultsHtml += `<p class="mb-3"><strong>API URL:</strong> <code>${data.api_url || 'N/A'}</code></p>`;
            
            if (data.tests) {
                resultsHtml += '<div class="mb-3">';
                resultsHtml += '<h6 class="mb-2">Test Results:</h6>';
                
                Object.keys(data.tests).forEach(testName => {
                    const test = data.tests[testName];
                    const icon = test.status === 'success' ? 'bx-check-circle text-success' : 'bx-x-circle text-danger';
                    const badge = test.status === 'success' ? 'badge bg-success' : 'badge bg-danger';
                    
                    resultsHtml += `<div class="mb-2 p-2 border rounded">`;
                    resultsHtml += `<div class="d-flex align-items-center mb-1">`;
                    resultsHtml += `<i class="bx ${icon} me-2"></i>`;
                    resultsHtml += `<strong>${testName.charAt(0).toUpperCase() + testName.slice(1)}:</strong> `;
                    resultsHtml += `<span class="${badge} ms-2">${test.status}</span>`;
                    resultsHtml += `</div>`;
                    resultsHtml += `<small class="text-muted">${test.message || ''}</small>`;
                    if (test.response_time_ms) {
                        resultsHtml += `<br><small class="text-muted">Response time: ${test.response_time_ms}ms</small>`;
                    }
                    resultsHtml += `</div>`;
                });
                
                resultsHtml += '</div>';
            }
            
            resultsHtml += '</div>';
            
            if (data.success) {
                Swal.fire({
                    title: 'Connection Successful!',
                    html: `
                        <div class="text-center mb-3">
                            <i class="bx bx-check-circle bx-lg text-success mb-3" style="font-size: 4rem;"></i>
                            <h5 class="mb-2">API is Accessible</h5>
                        </div>
                        ${resultsHtml}
                    `,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    width: '600px'
                });
            } else {
                Swal.fire({
                    title: 'Connection Failed!',
                    html: `
                        <div class="text-center mb-3">
                            <i class="bx bx-x-circle bx-lg text-danger mb-3" style="font-size: 4rem;"></i>
                            <h5 class="mb-2">Cannot Connect to API</h5>
                        </div>
                        ${resultsHtml}
                        <div class="alert alert-warning mt-3">
                            <strong>Possible Solutions:</strong>
                            <ul class="mb-0 text-start">
                                <li>Verify the API server is running at <code>192.168.100.100:8000</code></li>
                                <li>Check network connectivity from server to API</li>
                                <li>Verify firewall allows connections on port 8000</li>
                                <li>Test from server command line: <code>curl http://192.168.100.100:8000/api/v1/attendances</code></li>
                            </ul>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    width: '700px'
                });
            }
        } else {
            alert(data.success ? 'API connection successful!' : 'Connection failed. Check console for details.');
        }
        
        console.log('API Connection Test Results:', data);
    })
    .catch(error => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Test Error!',
                html: `
                    <div class="text-center">
                        <i class="bx bx-error-circle bx-lg text-danger mb-3" style="font-size: 4rem;"></i>
                        <h5 class="mb-2">Network Error</h5>
                        <p class="text-danger">${error.message || 'Failed to run diagnostic tests'}</p>
                    </div>
                `,
                icon: 'error',
                confirmButtonText: 'OK'
            });
        } else {
            alert('Error: ' + (error.message || 'Connection failed'));
        }
        console.error('API connection test error:', error);
    });
};

// Toggle custom date range visibility
function toggleCustomDateRange() {
    const reportPeriod = document.getElementById('reportPeriod').value;
    const startDateContainer = document.getElementById('startDateContainer');
    const endDateContainer = document.getElementById('endDateContainer');
    
    if (reportPeriod === 'custom') {
        startDateContainer.style.display = 'block';
        endDateContainer.style.display = 'block';
    } else {
        startDateContainer.style.display = 'none';
        endDateContainer.style.display = 'none';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleCustomDateRange();
    checkTodayAttendanceStatus();
});

// Check today's attendance status
function checkTodayAttendanceStatus() {
    const statusText = document.getElementById('statusText');
    const statusLoading = document.getElementById('statusLoading');
    const btnCheckIn = document.getElementById('btnCheckIn');
    const btnCheckOut = document.getElementById('btnCheckOut');
    
    if (!statusText) return;
    
    statusLoading.style.display = 'inline-block';
    statusText.textContent = 'Checking...';
    
    fetch('{{ route("attendance.today") }}', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        statusLoading.style.display = 'none';
        
        if (data.attendance) {
            const att = data.attendance;
            if (att.time_in && !att.time_out) {
                statusText.innerHTML = '<span class="badge bg-success">Checked In</span><br><small class="text-muted">Time: ' + (att.time_in || 'N/A') + '</small>';
                btnCheckIn.disabled = true;
                btnCheckOut.disabled = false;
            } else if (att.time_in && att.time_out) {
                statusText.innerHTML = '<span class="badge bg-danger">Checked Out</span><br><small class="text-muted">In: ' + (att.time_in || 'N/A') + ' | Out: ' + (att.time_out || 'N/A') + '</small>';
                btnCheckIn.disabled = true;
                btnCheckOut.disabled = true;
            } else {
                statusText.innerHTML = '<span class="badge bg-secondary">Not Checked In</span>';
                btnCheckIn.disabled = false;
                btnCheckOut.disabled = true;
            }
        } else {
            statusText.innerHTML = '<span class="badge bg-secondary">Not Checked In</span>';
            btnCheckIn.disabled = false;
            btnCheckOut.disabled = true;
        }
    })
    .catch(error => {
        statusLoading.style.display = 'none';
        statusText.innerHTML = '<span class="text-danger">Error checking status</span>';
        console.error('Error checking attendance status:', error);
    });
}

// Manual check-in/check-out functions removed - attendance is now only captured via ZKTeco devices

// showToast function removed - using SweetAlert2 directly in all functions
</script>
@endpush
@endsection


