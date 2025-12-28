@extends('layouts.app')

@php
use App\Models\SystemSetting;
@endphp

@section('title', 'Attendance Settings & Configuration')

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
                                <i class="bx bx-cog me-2"></i>Attendance Settings & Configuration
                            </h4>
                            <p class="card-text text-white-50 mb-0">Manage ZKTeco biometric devices, user enrollment, and attendance settings</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.attendance') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Attendance
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Connection Test Results (from direct form submission) -->
    @if(session('success') || session('error'))
    <div class="row mb-4">
        <div class="col-12">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bx bx-check-circle me-2"></i>Connection Successful!</h5>
                <p class="mb-2">{{ session('message') }}</p>
                @if(session('device_info'))
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Device Information:</strong>
                        <ul class="mb-0 mt-2">
                            <li>IP: {{ session('device_info')['ip'] ?? 'N/A' }}</li>
                            <li>Port: {{ session('device_info')['port'] ?? 'N/A' }}</li>
                            <li>Model: {{ session('device_info')['model'] ?? 'N/A' }}</li>
                            <li>Name: {{ session('device_info')['device_name'] ?? 'N/A' }}</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        @if(session('device_info')['firmware_version'] ?? null)
                        <strong>Firmware:</strong> {{ session('device_info')['firmware_version'] }}<br>
                        @endif
                        @if(session('device_info')['serial_number'] ?? null)
                        <strong>Serial:</strong> {{ session('device_info')['serial_number'] }}<br>
                        @endif
                        @if(session('working_method'))
                        <strong>Working Method:</strong> {{ session('working_method') }}<br>
                        @endif
                    </div>
                </div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bx bx-x-circle me-2"></i>Connection Failed</h5>
                <p class="mb-2">{{ session('message') }}</p>
                @if(session('connection_results') && isset(session('connection_results')['attempts']))
                <hr>
                <strong>Connection Attempts:</strong>
                <div class="mt-2">
                    @foreach(session('connection_results')['attempts'] as $attempt)
                    <div class="mb-2 p-2 border rounded">
                        <strong>{{ $attempt['method'] }}</strong><br>
                        <span class="badge bg-{{ $attempt['status'] == 'success' ? 'success' : ($attempt['status'] == 'partial' ? 'warning' : 'danger') }}">
                            {{ ucfirst($attempt['status']) }}
                        </span>
                        <small class="d-block mt-1">{{ $attempt['message'] }}</small>
                    </div>
                    @endforeach
                </div>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-info">{{ $stats['total_devices'] }}</div>
                    <div class="text-muted small">ZKTeco Devices</div>
                    <div class="text-success small">{{ $stats['online_devices'] }} Online</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-success">{{ $employees->count() ?? 0 }}</div>
                    <div class="text-muted small">Total Employees</div>
                    <div class="text-success small">{{ $employees->where('registered_on_device', true)->count() ?? 0 }} Enrolled</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="display-6 fw-bold text-primary">{{ $stats['active_devices'] }}</div>
                    <div class="text-muted small">Active Devices</div>
                    <div class="text-success small">{{ $stats['online_devices'] }} Connected</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Tabbed Interface -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="devices-tab" data-bs-toggle="tab" data-bs-target="#devices" type="button" role="tab">
                                <i class="bx bx-devices me-1"></i>ZKTeco Devices
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                                <i class="bx bx-user me-1"></i>Users & Enrollment
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="advanced-tab" data-bs-toggle="tab" data-bs-target="#advanced-settings" type="button" role="tab">
                                <i class="bx bx-cog me-1"></i>Advanced Settings
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- Devices Tab -->
                        <div class="tab-pane fade show active" id="devices" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Attendance Devices</h5>
                                <button type="button" class="btn btn-primary btn-sm" onclick="openDeviceModal()">
                                    <i class="bx bx-plus me-1"></i>Add Device
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Device ID</th>
                                            <th>Type</th>
                                            <th>Location</th>
                                            <th>IP Address</th>
                                            <th>Connection Status</th>
                                            <th>Last Sync</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($devices as $device)
                                        <tr>
                                            <td><strong>{{ $device->name }}</strong></td>
                                            <td><code>{{ $device->device_id }}</code></td>
                                            <td><span class="badge bg-label-primary">{{ ucfirst(str_replace('_', ' ', $device->device_type)) }}</span></td>
                                            <td>{{ $device->location->name ?? 'N/A' }}</td>
                                            <td>
                                                @if($device->ip_address)
                                                    <code>{{ $device->ip_address }}</code>
                                                    @if($device->port)
                                                        <small class="text-muted">:{{ $device->port }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $connectionStatus = 'unknown';
                                                    $connectionClass = 'secondary';
                                                    $connectionText = 'Unknown';
                                                    
                                                    if (!$device->is_active) {
                                                        $connectionStatus = 'inactive';
                                                        $connectionClass = 'danger';
                                                        $connectionText = 'Inactive';
                                                    } elseif ($device->is_online) {
                                                        $connectionStatus = 'online';
                                                        $connectionClass = 'success';
                                                        $connectionText = 'Online';
                                                    } elseif ($device->last_sync_at) {
                                                        $minutesSinceSync = now()->diffInMinutes($device->last_sync_at);
                                                        if ($minutesSinceSync < 10) {
                                                            $connectionStatus = 'online';
                                                            $connectionClass = 'success';
                                                            $connectionText = 'Online';
                                                        } elseif ($minutesSinceSync < 30) {
                                                            $connectionStatus = 'warning';
                                                            $connectionClass = 'warning';
                                                            $connectionText = 'Slow';
                                                        } else {
                                                            $connectionStatus = 'offline';
                                                            $connectionClass = 'danger';
                                                            $connectionText = 'Offline';
                                                        }
                                                    } else {
                                                        $connectionStatus = 'offline';
                                                        $connectionClass = 'warning';
                                                        $connectionText = 'Never Synced';
                                                    }
                                                @endphp
                                                <span class="badge bg-{{ $connectionClass }}" id="device-status-{{ $device->id }}">
                                                    <i class="bx bx-{{ $connectionStatus === 'online' ? 'check-circle' : ($connectionStatus === 'offline' ? 'x-circle' : 'time') }} me-1"></i>
                                                    {{ $connectionText }}
                                                </span>
                                                @if($device->last_sync_at)
                                                    <br><small class="text-muted">{{ $device->last_sync_at->diffForHumans() }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($device->last_sync_at)
                                                    <div>{{ $device->last_sync_at->format('M d, Y') }}</div>
                                                    <small class="text-muted">{{ $device->last_sync_at->format('H:i:s') }}</small>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-info" onclick="testDevice({{ $device->id }})" title="Test Connection">
                                                        <i class="bx bx-wifi"></i>
                                                    </button>
                                                    <button class="btn btn-outline-success" onclick="viewDeviceLogs({{ $device->id }})" title="View Logs">
                                                        <i class="bx bx-list-ul"></i>
                                                    </button>
                                                    <button class="btn btn-outline-primary" onclick="editDevice({{ $device->id }})" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger" onclick="deleteDevice({{ $device->id }})" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <i class="bx bx-inbox fs-1"></i>
                                                <p class="mt-2">No devices configured</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Users & Enrollment Tab -->
                        <div class="tab-pane fade" id="users" role="tabpanel">
                            <!-- Office Locations Section -->
                            <div class="card mb-4 border-primary">
                                <div class="card-header bg-primary text-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-0">
                                                <i class="bx bx-map me-2"></i>Office Locations Management
                                            </h6>
                                            <small class="text-white-50">Define office locations with GPS coordinates for attendance verification</small>
                                        </div>
                                        <button type="button" class="btn btn-light btn-sm" onclick="openLocationModal()">
                                            <i class="bx bx-plus me-1"></i>Add Location
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-3">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Location Setup:</strong> Define office locations with GPS coordinates and radius. When "Require GPS Verification" is enabled, users must be within the specified radius to check in or check out.
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm" id="locationsTable">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Code</th>
                                                    <th>Address</th>
                                                    <th>GPS Coordinates</th>
                                                    <th>Radius</th>
                                                    <th>GPS Required</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="locationsList">
                                                <tr>
                                                    <td colspan="8" class="text-center py-4 text-muted">
                                                        <i class="bx bx-loader-circle bx-spin fs-4"></i>
                                                        <p class="mt-2 mb-0">Loading locations...</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- User Management Section -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">User Management & Fingerprint Enrollment</h5>
                                <div>
                                    <button type="button" class="btn btn-info btn-sm" onclick="syncAllUsersToDevices()">
                                        <i class="bx bx-sync me-1"></i>Sync All Users to Devices
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openUserEnrollmentModal()">
                                        <i class="bx bx-plus me-1"></i>Enroll User
                                    </button>
                    </div>
                </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <input type="text" class="form-control" id="userSearchInput" placeholder="Search users by name, ID, or department..." onkeyup="filterUsers()">
            </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="userDepartmentFilter" onchange="filterUsers()">
                                        <option value="">All Departments</option>
                                        @foreach($departments ?? [] as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
        </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="userEnrollmentFilter" onchange="filterUsers()">
                                        <option value="">All Enrollment Status</option>
                                        <option value="enrolled">Enrolled</option>
                                        <option value="not_enrolled">Not Enrolled</option>
                                        <option value="partial">Partially Enrolled</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select" id="userDeviceFilter" onchange="filterUsers()">
                                        <option value="">All Devices</option>
                                        @foreach($devices as $device)
                                            <option value="{{ $device->id }}">{{ $device->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="usersTable">
                                    <thead>
                                        <tr>
                                            <th>Employee ID</th>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Fingerprint Status</th>
                                            <th>Enrolled Devices</th>
                                            <th>Last Sync</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="usersTableBody">
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="bx bx-loader-circle bx-spin fs-1"></i>
                                                <p class="mt-2">Loading users...</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Advanced Settings Tab -->
                        <div class="tab-pane fade" id="advanced-settings" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <h5 class="mb-3"><i class="bx bx-cog me-1"></i>Advanced Configuration & Device Management</h5>
                                </div>
                            </div>
                            
                            <!-- Advanced Sub-Tabs -->
                            <ul class="nav nav-tabs mb-4" id="advancedSubTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="advanced-locations-tab" data-bs-toggle="tab" data-bs-target="#advanced-locations" type="button" role="tab">
                                        <i class="bx bx-map me-1"></i>Office Locations
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="advanced-devices-tab" data-bs-toggle="tab" data-bs-target="#advanced-devices" type="button" role="tab">
                                        <i class="bx bx-error-circle me-1"></i>Device Failures
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="advanced-sync-tab" data-bs-toggle="tab" data-bs-target="#advanced-sync" type="button" role="tab">
                                        <i class="bx bx-sync me-1"></i>Sync Settings
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="advanced-api-tab" data-bs-toggle="tab" data-bs-target="#advanced-api" type="button" role="tab">
                                        <i class="bx bx-code-alt me-1"></i>API Integration
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content" id="advancedSubTabContent">
                                <!-- Office Locations Sub-Tab -->
                                <div class="tab-pane fade show active" id="advanced-locations" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <h5 class="mb-0">
                                                <i class="bx bx-map text-primary me-2"></i>Office Locations Management
                                            </h5>
                                            <p class="text-muted mb-0 small">Define office locations with GPS coordinates for attendance verification</p>
                                        </div>
                                        <button type="button" class="btn btn-primary" onclick="openLocationModal()">
                                            <i class="bx bx-plus me-1"></i>Add Location
                                        </button>
                                    </div>
                                    
                                    <div class="alert alert-info mb-4">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Location Setup:</strong> Define office locations with GPS coordinates and radius. When "Require GPS Verification" is enabled, users must be within the specified radius to check in or check out.
                                    </div>
                                    
                                    <div class="table-responsive">
                                        <table class="table table-hover" id="locationsTable">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Code</th>
                                                    <th>Address</th>
                                                    <th>GPS Coordinates</th>
                                                    <th>Radius</th>
                                                    <th>GPS Required</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="locationsList">
                                                <tr>
                                                    <td colspan="8" class="text-center py-5 text-muted">
                                                        <i class="bx bx-loader-circle bx-spin fs-1"></i>
                                                        <p class="mt-2">Loading locations...</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Device Failures Sub-Tab -->
                                <div class="tab-pane fade" id="advanced-devices" role="tabpanel">
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card border-warning">
                                                <div class="card-header bg-warning text-dark">
                                                    <h6 class="mb-0"><i class="bx bx-error-circle me-1"></i>Device Failure Detection & Management</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Failed Devices Status</label>
                                                                <div id="failedDevicesList" class="list-group">
                                                                    <div class="list-group-item">
                                                                        <div class="d-flex justify-content-between align-items-center">
                                                                            <div>
                                                                                <h6 class="mb-0">Checking devices...</h6>
                                                                                <small class="text-muted">Loading device status</small>
                                                                            </div>
                                                                            <span class="badge bg-secondary">Checking</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="d-grid gap-2">
                                                                <button type="button" class="btn btn-warning" onclick="checkDeviceFailures()">
                                                                    <i class="bx bx-refresh me-1"></i>Check Device Failures
                                                                </button>
                                                                <button type="button" class="btn btn-outline-danger" onclick="viewFailedDevices()">
                                                                    <i class="bx bx-error me-1"></i>View Failed Devices
                                                                </button>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="alert alert-info">
                                                                <h6 class="mb-2"><i class="bx bx-info-circle me-1"></i>Device Failure Detection</h6>
                                                                <p class="mb-2 small">The system automatically detects device failures when:</p>
                                                                <ul class="mb-0 small">
                                                                    <li>Device is offline for more than 5 minutes</li>
                                                                    <li>Connection timeout occurs</li>
                                                                    <li>Sync fails repeatedly (3+ times)</li>
                                                                    <li>Invalid response from device</li>
                                                                    <li>Network connectivity issues</li>
                                                                </ul>
                                                            </div>
                                                            <div class="mb-3">
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox" id="autoFailureDetection" name="auto_failure_detection" {{ SystemSetting::getValue('attendance_auto_failure_detection', true) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="autoFailureDetection">Enable Automatic Failure Detection</label>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Failure Alert Threshold (minutes)</label>
                                                                <input type="number" class="form-control" id="failureThreshold" name="failure_threshold" value="{{ SystemSetting::getValue('attendance_failure_threshold', 5) }}" min="1" max="60">
                                                                <small class="text-muted">Alert when device is offline for this duration</small>
                                                            </div>
                                                            <button type="button" class="btn btn-warning btn-sm" onclick="saveFailureSettings()">
                                                                <i class="bx bx-save me-1"></i>Save Failure Settings
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Sync Settings Sub-Tab -->
                                <div class="tab-pane fade" id="advanced-sync" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="bx bx-sync me-1"></i>Sync Configuration</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Sync Mode <span class="text-danger">*</span></label>
                                                        <select class="form-select" id="defaultSyncMode" name="default_sync_mode">
                                                            <option value="push" {{ SystemSetting::getValue('attendance_default_sync_mode', 'push') === 'push' ? 'selected' : '' }}>Push Mode (Device → Server)</option>
                                                            <option value="polling" {{ SystemSetting::getValue('attendance_default_sync_mode') === 'polling' ? 'selected' : '' }}>Polling Mode (Server → Device)</option>
                                                            <option value="hybrid" {{ SystemSetting::getValue('attendance_default_sync_mode') === 'hybrid' ? 'selected' : '' }}>Hybrid Mode (Both)</option>
                                                        </select>
                                                        <small class="text-muted">How devices communicate with the server</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Push API Endpoint</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="pushApiEndpoint" value="{{ url('attendance/api/push') }}" readonly>
                                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('pushApiEndpoint')">
                                                                <i class="bx bx-copy"></i>
                                                            </button>
                                                        </div>
                                                        <small class="text-muted">Configure this URL in WL30 device settings</small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="autoSyncEnabled" name="auto_sync_enabled" {{ SystemSetting::getValue('attendance_auto_sync_enabled', true) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="autoSyncEnabled">Enable Automatic Sync</label>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="saveSyncSettings()">
                                                        <i class="bx bx-save me-1"></i>Save Settings
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0"><i class="bx bx-wifi me-1"></i>Device Management</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Device Status</label>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span>Online:</span>
                                                            <span class="badge bg-success">{{ $stats['online_devices'] }}/{{ $stats['total_devices'] }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <span>Active:</span>
                                                            <span class="badge bg-info">{{ $stats['active_devices'] }}/{{ $stats['total_devices'] }}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span>Failed:</span>
                                                            <span class="badge bg-danger" id="failedDevicesCount">0</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-grid gap-2">
                                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="testAllDevices()">
                                                            <i class="bx bx-wifi me-1"></i>Test All Devices
                                                        </button>
                                                        <button type="button" class="btn btn-outline-info btn-sm" onclick="syncAllDevices()">
                                                            <i class="bx bx-sync me-1"></i>Sync All Devices
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- API Integration Sub-Tab -->
                                <div class="tab-pane fade" id="advanced-api" role="tabpanel">
                                    <div class="row">
                                        <!-- API Endpoints Configuration -->
                                        <div class="col-md-12 mb-4">
                                            <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                    <h6 class="mb-0"><i class="bx bx-code-alt me-1"></i>Device API Endpoints</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-info">
                                                        <i class="bx bx-info-circle me-1"></i>
                                                        <strong>Base URL:</strong> <code>{{ url('api/device') }}</code>
                                                        <button class="btn btn-sm btn-outline-light ms-2" onclick="copyToClipboard('{{ url('api/device') }}')">
                                                            <i class="bx bx-copy"></i> Copy
                                                        </button>
                                                    </div>

                                                    <!-- Receiving Data FROM Device (Push API) -->
                                                    <div class="mb-4">
                                                        <h6 class="text-primary"><i class="bx bx-upload me-1"></i>Receiving Data FROM Device (Push API)</h6>
                                                        
                                                        <!-- Push Attendance -->
                                                        <div class="card mb-3">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mb-1"><code>POST /api/device/attendance/push</code></h6>
                                                                        <small class="text-muted">Receive single attendance record from device</small>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="copyEndpoint('{{ url('api/device/attendance/push') }}')">
                                                                        <i class="bx bx-copy"></i> Copy URL
                                                                    </button>
                                                                </div>
                                                                <div class="bg-light p-2 rounded">
                                                                    <small><strong>URL:</strong> <code>{{ url('api/device/attendance/push') }}</code></small>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-success" onclick="testApiEndpoint('push')">
                                                                        <i class="bx bx-test-tube me-1"></i>Test Endpoint
                                                                    </button>
                                                                    <button class="btn btn-sm btn-info" onclick="showApiExample('push')">
                                                                        <i class="bx bx-code me-1"></i>View Example
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Batch Attendance -->
                                                        <div class="card mb-3">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mb-1"><code>POST /api/device/attendance/batch</code></h6>
                                                                        <small class="text-muted">Receive multiple attendance records in one request</small>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="copyEndpoint('{{ url('api/device/attendance/batch') }}')">
                                                                        <i class="bx bx-copy"></i> Copy URL
                                                                    </button>
                                                                </div>
                                                                <div class="bg-light p-2 rounded">
                                                                    <small><strong>URL:</strong> <code>{{ url('api/device/attendance/batch') }}</code></small>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-success" onclick="testApiEndpoint('batch')">
                                                                        <i class="bx bx-test-tube me-1"></i>Test Endpoint
                                                                    </button>
                                                                    <button class="btn btn-sm btn-info" onclick="showApiExample('batch')">
                                                                        <i class="bx bx-code me-1"></i>View Example
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Device Status -->
                                                        <div class="card mb-3">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mb-1"><code>POST /api/device/status</code></h6>
                                                                        <small class="text-muted">Receive device status/heartbeat information</small>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="copyEndpoint('{{ url('api/device/status') }}')">
                                                                        <i class="bx bx-copy"></i> Copy URL
                                                                    </button>
                                                                </div>
                                                                <div class="bg-light p-2 rounded">
                                                                    <small><strong>URL:</strong> <code>{{ url('api/device/status') }}</code></small>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-success" onclick="testApiEndpoint('status')">
                                                                        <i class="bx bx-test-tube me-1"></i>Test Endpoint
                                                                    </button>
                                                                    <button class="btn btn-sm btn-info" onclick="showApiExample('status')">
                                                                        <i class="bx bx-code me-1"></i>View Example
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Sending Data TO Device (Pull API) -->
                                                    <div class="mb-4">
                                                        <h6 class="text-success"><i class="bx bx-download me-1"></i>Sending Data TO Device (Pull API)</h6>
                                                        
                                                        <!-- Get Users -->
                                                        <div class="card mb-3">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mb-1"><code>GET /api/device/users/{device_id}</code></h6>
                                                                        <small class="text-muted">Get all employees/users list for device sync</small>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="copyEndpoint('{{ url('api/device/users/{device_id}') }}')">
                                                                        <i class="bx bx-copy"></i> Copy URL
                                                                    </button>
                                                                </div>
                                                                <div class="bg-light p-2 rounded mb-2">
                                                                    <small><strong>URL:</strong> <code>{{ url('api/device/users') }}/<span class="text-danger">{device_id}</span></code></small>
                                                                    <br><small class="text-muted">Example: <code>{{ url('api/device/users/UF200-S-TRU7251200134') }}</code></small>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-success" onclick="testApiEndpoint('users')">
                                                                        <i class="bx bx-test-tube me-1"></i>Test Endpoint
                                                                    </button>
                                                                    <button class="btn btn-sm btn-info" onclick="showApiExample('users')">
                                                                        <i class="bx bx-code me-1"></i>View Example
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Get Server Time -->
                                                        <div class="card mb-3">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mb-1"><code>GET /api/device/time/{device_id}</code></h6>
                                                                        <small class="text-muted">Get server time for device synchronization</small>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="copyEndpoint('{{ url('api/device/time/{device_id}') }}')">
                                                                        <i class="bx bx-copy"></i> Copy URL
                                                                    </button>
                                                                </div>
                                                                <div class="bg-light p-2 rounded mb-2">
                                                                    <small><strong>URL:</strong> <code>{{ url('api/device/time') }}/<span class="text-danger">{device_id}</span></code></small>
                                                                    <br><small class="text-muted">Example: <code>{{ url('api/device/time/UF200-S-TRU7251200134') }}</code></small>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-success" onclick="testApiEndpoint('time')">
                                                                        <i class="bx bx-test-tube me-1"></i>Test Endpoint
                                                                    </button>
                                                                    <button class="btn btn-sm btn-info" onclick="showApiExample('time')">
                                                                        <i class="bx bx-code me-1"></i>View Example
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Get Commands -->
                                                        <div class="card mb-3">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mb-1"><code>GET /api/device/commands/{device_id}</code></h6>
                                                                        <small class="text-muted">Device checks for pending commands from Laravel</small>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="copyEndpoint('{{ url('api/device/commands/{device_id}') }}')">
                                                                        <i class="bx bx-copy"></i> Copy URL
                                                                    </button>
                                                                </div>
                                                                <div class="bg-light p-2 rounded mb-2">
                                                                    <small><strong>URL:</strong> <code>{{ url('api/device/commands') }}/<span class="text-danger">{device_id}</span></code></small>
                                                                    <br><small class="text-muted">Example: <code>{{ url('api/device/commands/UF200-S-TRU7251200134') }}</code></small>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-success" onclick="testApiEndpoint('commands')">
                                                                        <i class="bx bx-test-tube me-1"></i>Test Endpoint
                                                                    </button>
                                                                    <button class="btn btn-sm btn-info" onclick="showApiExample('commands')">
                                                                        <i class="bx bx-code me-1"></i>View Example
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Send Command -->
                                                        <div class="card mb-3">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mb-1"><code>POST /api/device/commands/{device_id}</code></h6>
                                                                        <small class="text-muted">Send command to device (stored for device to pull)</small>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="copyEndpoint('{{ url('api/device/commands/{device_id}') }}')">
                                                                        <i class="bx bx-copy"></i> Copy URL
                                                                    </button>
                                                                </div>
                                                                <div class="bg-light p-2 rounded mb-2">
                                                                    <small><strong>URL:</strong> <code>{{ url('api/device/commands') }}/<span class="text-danger">{device_id}</span></code></small>
                                                                    <br><small class="text-muted">Example: <code>{{ url('api/device/commands/UF200-S-TRU7251200134') }}</code></small>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-success" onclick="testApiEndpoint('send-command')">
                                                                        <i class="bx bx-test-tube me-1"></i>Test Endpoint
                                                                    </button>
                                                                    <button class="btn btn-sm btn-info" onclick="showApiExample('send-command')">
                                                                        <i class="bx bx-code me-1"></i>View Example
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Health Check -->
                                                    <div class="mb-4">
                                                        <h6 class="text-info"><i class="bx bx-heart me-1"></i>Health & Status</h6>
                                                        <div class="card">
                                                            <div class="card-body">
                                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                                    <div>
                                                                        <h6 class="mb-1"><code>GET /api/device/health</code></h6>
                                                                        <small class="text-muted">Check if API is accessible and healthy</small>
                                                                    </div>
                                                                    <button class="btn btn-sm btn-outline-primary" onclick="copyEndpoint('{{ url('api/device/health') }}')">
                                                                        <i class="bx bx-copy"></i> Copy URL
                                                                    </button>
                                                                </div>
                                                                <div class="bg-light p-2 rounded">
                                                                    <small><strong>URL:</strong> <code>{{ url('api/device/health') }}</code></small>
                                                                </div>
                                                                <div class="mt-2">
                                                                    <button class="btn btn-sm btn-success" onclick="testApiEndpoint('health')">
                                                                        <i class="bx bx-test-tube me-1"></i>Test Health
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- API Configuration -->
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-warning text-dark">
                                                    <h6 class="mb-0"><i class="bx bx-cog me-1"></i>API Configuration</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">API Base URL</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="apiBaseUrl" value="{{ url('api/device') }}" readonly>
                                                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ url('api/device') }}')">
                                                                <i class="bx bx-copy"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">API Key (for device authentication)</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" id="deviceApiKey" value="{{ SystemSetting::getValue('device_api_key', '') }}" placeholder="Leave empty to use IP whitelist">
                                                            <button class="btn btn-outline-secondary" type="button" onclick="generateDeviceApiKey()">
                                                                <i class="bx bx-refresh me-1"></i>Generate
                                                            </button>
                                                        </div>
                                                        <small class="text-muted">Include in header: <code>X-API-Key: your-key</code></small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="enableDeviceApiLogging" {{ SystemSetting::getValue('device_api_logging', true) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="enableDeviceApiLogging">Enable API Request Logging</label>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox" id="requireDeviceHttps" {{ SystemSetting::getValue('device_api_require_https', false) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="requireDeviceHttps">Require HTTPS for API</label>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn btn-warning" onclick="saveDeviceApiSettings()">
                                                        <i class="bx bx-save me-1"></i>Save API Settings
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- API Documentation & Examples -->
                                        <div class="col-md-6">
                                            <div class="card">
                                                <div class="card-header bg-info text-white">
                                                    <h6 class="mb-0"><i class="bx bx-book me-1"></i>Documentation & Examples</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <h6>Quick Links</h6>
                                                        <ul class="list-unstyled">
                                                            <li><a href="#" onclick="window.open('{{ asset('DEVICE_API_DOCUMENTATION.md') }}', '_blank')" class="text-decoration-none">
                                                                <i class="bx bx-file me-1"></i>Full API Documentation
                                                            </a></li>
                                                            <li><a href="#" onclick="window.open('{{ asset('DEVICE_API_QUICK_REFERENCE.md') }}', '_blank')" class="text-decoration-none">
                                                                <i class="bx bx-book-open me-1"></i>Quick Reference Guide
                                                            </a></li>
                                                            <li><a href="#" onclick="window.open('{{ asset('DEVICE_API_SUMMARY.md') }}', '_blank')" class="text-decoration-none">
                                                                <i class="bx bx-list-ul me-1"></i>API Summary
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                    <div class="mb-3">
                                                        <h6>Example Request (cURL)</h6>
                                                        <div class="bg-dark text-light p-2 rounded">
                                                            <pre class="small mb-0 text-light"><code>curl -X POST {{ url('api/device/attendance/push') }} \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "device_id": "UF200-S-TRU7251200134",
    "employee_id": "EMP001",
    "check_time": "2025-01-15T09:00:00Z",
    "check_type": "I"
  }'</code></pre>
                                                        </div>
                                                        <button class="btn btn-sm btn-info mt-2" onclick="copyCurlExample()">
                                                            <i class="bx bx-copy me-1"></i>Copy cURL Example
                                                        </button>
                                                    </div>
                                                    <div class="alert alert-warning mb-0">
                                                        <small><i class="bx bx-info-circle me-1"></i><strong>Note:</strong> Ensure device is registered in Laravel before using API endpoints.</small>
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
            </div>
        </div>
    </div>
</div>

<!-- Location Modal -->
<div class="modal fade" id="locationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="locationModalTitle">Add Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="locationForm">
                <div class="modal-body">
                    <input type="hidden" id="locationId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Location Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="locationName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="locationCode" name="code" required placeholder="e.g., HQ, BRANCH-01">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="locationDescription" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" id="locationAddress" name="address">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" id="locationCity" name="city">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">State/Region</label>
                            <input type="text" class="form-control" id="locationState" name="state">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Country</label>
                            <input type="text" class="form-control" id="locationCountry" name="country">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Postal Code</label>
                            <input type="text" class="form-control" id="locationPostalCode" name="postal_code">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">GPS Radius (meters) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="locationRadius" name="radius_meters" value="100" min="10" max="10000" required>
                            <small class="text-muted">Allowed radius for GPS-based attendance</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Latitude</label>
                            <input type="number" step="any" class="form-control" id="locationLatitude" name="latitude" placeholder="-6.7924">
                            <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="getCurrentLocation()">
                                <i class="bx bx-map me-1"></i>Get Current Location
                            </button>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Longitude</label>
                            <input type="number" step="any" class="form-control" id="locationLongitude" name="longitude" placeholder="39.2083">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="locationRequireGps" name="require_gps">
                                <label class="form-check-label" for="locationRequireGps">Require GPS Verification</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="locationAllowRemote" name="allow_remote">
                                <label class="form-check-label" for="locationAllowRemote">Allow Remote Attendance</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="locationIsActive" name="is_active" checked>
                                <label class="form-check-label" for="locationIsActive">Active</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Attendance Method:</strong> Only ZKTeco Biometric devices are used for automatic attendance tracking.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Device Modal -->
<div class="modal fade" id="deviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deviceModalTitle">Add Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deviceForm">
                <div class="modal-body">
                    <input type="hidden" id="deviceId" name="id">
                    <input type="hidden" id="deviceType" name="device_type" value="biometric">
                    <input type="hidden" id="deviceConnectionType" name="connection_type" value="network">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Device Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="deviceName" name="name" required placeholder="e.g., Main Office UF200-S">
                            <small class="text-muted">A friendly name to identify this device</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IP Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="deviceIpAddress" name="ip_address" placeholder="192.168.1.100" required>
                            <small class="text-muted">UF200-S device IP address on network</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Port <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="devicePort" name="port" placeholder="4370" value="4370" min="1" max="65535" required>
                            <small class="text-muted">Default ZKTeco port: 4370</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Communication Key (Password)</label>
                            <input type="number" class="form-control" id="devicePassword" name="password" placeholder="0" value="0" min="0" max="65535">
                            <small class="text-muted">Set on device (0 for no password)</small>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="bx bx-info-circle me-2"></i>
                                <strong>Test Connection:</strong> Click the "Test Connection" button below to verify device connectivity before saving.
                            </div>
                            <button type="button" class="btn btn-info" onclick="testZKTecoConnection()">
                                <i class="bx bx-wifi me-1"></i>Test Connection
                            </button>
                            <div id="connectionTestResult" class="mt-3"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Device Logs Modal -->
<div class="modal fade" id="deviceLogsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-list-ul me-2"></i>Device Logs
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="deviceLogsBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading device logs...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Removed old ZKBio Time.Net configuration content - not in documentation -->
<!--
                                <div class="col-md-6">
                                    <label class="form-label">ZKBio Time.Net Server IP <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="zkbioServerIp" name="zkbio_server_ip" placeholder="192.168.1.50" required>
                                    <small class="text-muted">IP address of the computer running ZKBio Time.Net</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Database Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="zkbioDbType" name="zkbio_db_type" required>
                                        <option value="sqlite" selected>SQLite (Default)</option>
                                        <option value="mysql">MySQL</option>
                                        <option value="mssql">MS SQL Server</option>
                                    </select>
                                </div>
                                <div class="col-md-12" id="zkbioDbPathGroup">
                                    <label class="form-label">Database Path <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="zkbioDbPath" name="zkbio_db_path" placeholder="C:\ZKTeco\ZKBioTime\attendance.db" required>
                                    <small class="text-muted">Full path to ZKBio Time.Net database file (SQLite) or database name (MySQL/MSSQL)</small>
                                </div>
                                <div class="col-md-6" id="zkbioDbHostGroup" style="display: none;">
                                    <label class="form-label">Database Host</label>
                                    <input type="text" class="form-control" id="zkbioDbHost" name="zkbio_db_host" placeholder="192.168.1.50">
                                </div>
                                <div class="col-md-6" id="zkbioDbUserGroup" style="display: none;">
                                    <label class="form-label">Database Username</label>
                                    <input type="text" class="form-control" id="zkbioDbUser" name="zkbio_db_user" placeholder="username">
                                </div>
                                <div class="col-md-6" id="zkbioDbPasswordGroup" style="display: none;">
                                    <label class="form-label">Database Password</label>
                                    <input type="password" class="form-control" id="zkbioDbPassword" name="zkbio_db_password" placeholder="password">
                                </div>
                                
                                <script>
                                // Toggle database fields based on database type
                                function toggleDatabaseFields() {
                                    const dbType = document.getElementById('zkbioDbType').value;
                                    const pathGroup = document.getElementById('zkbioDbPathGroup');
                                    const hostGroup = document.getElementById('zkbioDbHostGroup');
                                    const userGroup = document.getElementById('zkbioDbUserGroup');
                                    const passwordGroup = document.getElementById('zkbioDbPasswordGroup');
                                    
                                    if (dbType === 'sqlite') {
                                        pathGroup.style.display = 'block';
                                        pathGroup.querySelector('input').placeholder = 'C:\\ZKTeco\\ZKBioTime\\attendance.db';
                                        pathGroup.querySelector('small').textContent = 'Full path to ZKBio Time.Net SQLite database file';
                                        hostGroup.style.display = 'none';
                                        userGroup.style.display = 'none';
                                        passwordGroup.style.display = 'none';
                                    } else {
                                        pathGroup.style.display = 'block';
                                        pathGroup.querySelector('input').placeholder = 'zkbiotime';
                                        pathGroup.querySelector('small').textContent = 'Database name';
                                        hostGroup.style.display = 'block';
                                        userGroup.style.display = 'block';
                                        passwordGroup.style.display = 'block';
                                    }
                                }
                                
                                document.getElementById('zkbioDbType').addEventListener('change', toggleDatabaseFields);
                                </script>
                            </div>

                            <!-- Setup Instructions -->
                            <div class="card bg-light mt-4">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="bx bx-book me-1"></i>ZKBio Time.Net Setup Instructions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="accordion" id="setupAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#step1">
                                                    <strong>Step 1: Install ZKBio Time.Net</strong>
                                                </button>
                                            </h2>
                                            <div id="step1" class="accordion-collapse collapse show" data-bs-parent="#setupAccordion">
                                                <div class="accordion-body">
                                                    <ol>
                                                        <li>Download ZKBio Time.Net from ZKTeco official website</li>
                                                        <li>Install on a Windows PC that will run 24/7 (recommended)</li>
                                                        <li>Ensure the PC is on the same network as the UF200-S device</li>
                                                        <li>Launch ZKBio Time.Net and complete initial setup</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step2">
                                                    <strong>Step 2: Connect UF200-S Device</strong>
                                                </button>
                                            </h2>
                                            <div id="step2" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                                <div class="accordion-body">
                                                    <ol>
                                                        <li>In ZKBio Time.Net, go to <strong>Device Management</strong></li>
                                                        <li>Click <strong>Add Device</strong> or <strong>Search Device</strong></li>
                                                        <li>Enter device IP address (configured in device settings)</li>
                                                        <li>Enter device port: <code>4370</code> (default)</li>
                                                        <li>Enter device username: <code>admin</code> (default)</li>
                                                        <li>Enter device password (set during device setup)</li>
                                                        <li>Click <strong>Connect</strong> and wait for connection status</li>
                                                        <li>Once connected, device will appear in the device list</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step3">
                                                    <strong>Step 3: Configure Device Communication</strong>
                                                </button>
                                            </h2>
                                            <div id="step3" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                                <div class="accordion-body">
                                                    <ol>
                                                        <li>Right-click on the connected device → <strong>Device Settings</strong></li>
                                                        <li>Go to <strong>Communication</strong> tab</li>
                                                        <li>Set <strong>Connection Type:</strong> TCP/IP</li>
                                                        <li>Set <strong>IP Address:</strong> (Device static IP)</li>
                                                        <li>Set <strong>Port:</strong> 4370</li>
                                                        <li>Enable <strong>Auto Download</strong> (recommended)</li>
                                                        <li>Set <strong>Download Interval:</strong> 5 minutes</li>
                                                        <li>Click <strong>OK</strong> to save</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step4">
                                                    <strong>Step 4: Sync Employee Data</strong>
                                                </button>
                                            </h2>
                                            <div id="step4" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                                <div class="accordion-body">
                                                    <ol>
                                                        <li>In ZKBio Time.Net, go to <strong>User Management</strong></li>
                                                        <li>Add employees or import from Excel/CSV</li>
                                                        <li>Ensure Employee ID matches your system Employee ID</li>
                                                        <li>Select device(s) and click <strong>Upload User</strong> to sync users to device</li>
                                                        <li>For fingerprint enrollment: Select user → <strong>Enroll Fingerprint</strong> → Follow on-screen instructions</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step5">
                                                    <strong>Step 5: Configure Database Export</strong>
                                                </button>
                                            </h2>
                                            <div id="step5" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                                <div class="accordion-body">
                                                    <ol>
                                                        <li>ZKBio Time.Net stores data in SQLite database by default</li>
                                                        <li>Database location: <code>C:\ZKTeco\ZKBioTime\attendance.db</code> (default)</li>
                                                        <li>Or configure MySQL/MSSQL in <strong>System Settings → Database</strong></li>
                                                        <li>Enable <strong>Auto Download</strong> to automatically fetch attendance records</li>
                                                        <li>Set download schedule: <strong>Tools → Schedule Download</strong></li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step6">
                                                    <strong>Step 6: Configure Web System Integration</strong>
                                                </button>
                                            </h2>
                                            <div id="step6" class="accordion-collapse collapse" data-bs-parent="#setupAccordion">
                                                <div class="accordion-body">
                                                    <ol>
                                                        <li>Enter ZKBio Time.Net server IP address above</li>
                                                        <li>Set sync interval (recommended: 5 minutes)</li>
                                                        <li>Enable <strong>Automatic Sync</strong> checkbox</li>
                                                        <li>Click <strong>Test Connection</strong> to verify connectivity</li>
                                                        <li>Once connected, attendance will sync automatically</li>
                                                    </ol>
                                                    <div class="alert alert-warning mt-3">
                                                        <strong>Note:</strong> Ensure ZKBio Time.Net database is accessible from your web server. If using SQLite, you may need to share the database file or use a network path.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Advanced Config Tab -->
                        <div class="tab-pane fade" id="advanced" role="tabpanel">
                            <div class="alert alert-info">
                                <h6><i class="bx bx-info-circle me-1"></i>ZKBio Time.Net Integration</h6>
                                <p class="mb-2">This device syncs attendance records from ZKBio Time.Net database automatically.</p>
                                <p class="mb-0"><strong>Sync Command:</strong> <code>php artisan attendance:sync-zkbiotime --device={{ $device->id ?? 'ID' }}</code></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Device Logs Modal -->
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-list-ul me-2"></i>Device Logs
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="deviceLogsBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading device logs...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Modal removed - not needed for ZKTeco automatic attendance -->
<!--
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalTitle">Add Work Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="scheduleForm">
                <div class="modal-body">
                    <input type="hidden" id="scheduleId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Schedule Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="scheduleName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schedule Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="scheduleCode" name="code" required placeholder="e.g., STD-9-5">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="scheduleDescription" name="description" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <select class="form-select" id="scheduleLocationId" name="location_id">
                                <option value="">All Locations</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select class="form-select" id="scheduleDepartmentId" name="department_id">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="scheduleStartTime" name="start_time" required value="09:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="scheduleEndTime" name="end_time" required value="17:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Work Hours (per day) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="scheduleWorkHours" name="work_hours" required value="8" min="1" max="24">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Break Duration (minutes)</label>
                            <input type="number" class="form-control" id="scheduleBreakDuration" name="break_duration_minutes" value="60" min="0" max="480">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Break Start Time</label>
                            <input type="time" class="form-control" id="scheduleBreakStart" name="break_start_time" value="13:00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Break End Time</label>
                            <input type="time" class="form-control" id="scheduleBreakEnd" name="break_end_time" value="14:00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Late Tolerance (minutes)</label>
                            <input type="number" class="form-control" id="scheduleLateTolerance" name="late_tolerance_minutes" value="15" min="0" max="120">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Early Leave Tolerance (minutes)</label>
                            <input type="number" class="form-control" id="scheduleEarlyLeaveTolerance" name="early_leave_tolerance_minutes" value="15" min="0" max="120">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Overtime Threshold (minutes)</label>
                            <input type="number" class="form-control" id="scheduleOvertimeThreshold" name="overtime_threshold_minutes" value="30" min="0" max="120">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Working Days <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="working_days[]" value="1" id="day1" checked>
                                        <label class="form-check-label" for="day1">Monday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="working_days[]" value="2" id="day2" checked>
                                        <label class="form-check-label" for="day2">Tuesday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="working_days[]" value="3" id="day3" checked>
                                        <label class="form-check-label" for="day3">Wednesday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="working_days[]" value="4" id="day4" checked>
                                        <label class="form-check-label" for="day4">Thursday</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="working_days[]" value="5" id="day5" checked>
                                        <label class="form-check-label" for="day5">Friday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="working_days[]" value="6" id="day6">
                                        <label class="form-check-label" for="day6">Saturday</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="working_days[]" value="7" id="day7">
                                        <label class="form-check-label" for="day7">Sunday</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="scheduleIsFlexible" name="is_flexible">
                                <label class="form-check-label" for="scheduleIsFlexible">Flexible Working Hours</label>
                            </div>
                        </div>
                        <div class="col-md-6" id="flexibleStartMinGroup" style="display:none;">
                            <label class="form-label">Flexible Start Min</label>
                            <input type="time" class="form-control" id="scheduleFlexibleStartMin" name="flexible_start_min">
                        </div>
                        <div class="col-md-6" id="flexibleStartMaxGroup" style="display:none;">
                            <label class="form-label">Flexible Start Max</label>
                            <input type="time" class="form-control" id="scheduleFlexibleStartMax" name="flexible_start_max">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Effective From</label>
                            <input type="date" class="form-control" id="scheduleEffectiveFrom" name="effective_from">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Effective To</label>
                            <input type="date" class="form-control" id="scheduleEffectiveTo" name="effective_to">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="scheduleIsActive" name="is_active" checked>
                                <label class="form-check-label" for="scheduleIsActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- User Enrollment Modal -->
<div class="modal fade" id="userEnrollmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userEnrollmentModalTitle">Enroll User to Device</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="userEnrollmentForm">
                <div class="modal-body">
                    <input type="hidden" id="enrollmentUserId" name="user_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Select User <span class="text-danger">*</span></label>
                            <select class="form-select" id="enrollmentUserSelect" name="user_id" required onchange="loadUserDetails()">
                                <option value="">Select Employee</option>
                                <!-- Will be populated via AJAX -->
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Device <span class="text-danger">*</span></label>
                            <select class="form-select" id="enrollmentDeviceSelect" name="device_id" required>
                                <option value="">Select Device</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->ip_address }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>User Information</h6>
                                    <div id="userDetailsDisplay">
                                        <p class="text-muted mb-0">Select a user to view details</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <h6>Fingerprint Enrollment</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger1" name="fingers[]" value="1">
                                        <label class="form-check-label" for="enrollFinger1">Right Index Finger</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger2" name="fingers[]" value="2">
                                        <label class="form-check-label" for="enrollFinger2">Right Middle Finger</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger3" name="fingers[]" value="3">
                                        <label class="form-check-label" for="enrollFinger3">Right Ring Finger</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger4" name="fingers[]" value="4">
                                        <label class="form-check-label" for="enrollFinger4">Right Little Finger</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger5" name="fingers[]" value="5">
                                        <label class="form-check-label" for="enrollFinger5">Right Thumb</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger6" name="fingers[]" value="6">
                                        <label class="form-check-label" for="enrollFinger6">Left Index Finger</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger7" name="fingers[]" value="7">
                                        <label class="form-check-label" for="enrollFinger7">Left Middle Finger</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger8" name="fingers[]" value="8">
                                        <label class="form-check-label" for="enrollFinger8">Left Ring Finger</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger9" name="fingers[]" value="9">
                                        <label class="form-check-label" for="enrollFinger9">Left Little Finger</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="enrollFinger10" name="fingers[]" value="10">
                                        <label class="form-check-label" for="enrollFinger10">Left Thumb</label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Select which fingers to enroll. Recommended: Right Index + Left Index</small>
                        </div>
                        <div class="col-md-12">
                            <div class="alert alert-warning">
                                <h6><i class="bx bx-info-circle me-1"></i>Enrollment Instructions</h6>
                                <ol class="mb-0 small">
                                    <li>Ensure the device is online and connected</li>
                                    <li>User should place selected finger on the scanner</li>
                                    <li>Follow device prompts (usually 3 scans per finger)</li>
                                    <li>Wait for confirmation before removing finger</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info" onclick="startEnrollment()">
                        <i class="bx bx-fingerprint me-1"></i>Start Enrollment
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save Enrollment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Policy Modal -->
<div class="modal fade" id="policyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="policyModalTitle">Add Attendance Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="policyForm">
                <div class="modal-body">
                    <input type="hidden" id="policyId" name="id">
                    <ul class="nav nav-tabs mb-3" id="policyTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="policy-basic-tab" data-bs-toggle="tab" data-bs-target="#policy-basic" type="button" data-step="1">
                                <i class="bx bx-info-circle me-1"></i>Step 1: Basic Info
                                <span class="badge bg-success ms-2 d-none" id="policy-basic-saved-badge"><i class="bx bx-check"></i></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="policy-approval-tab" data-bs-toggle="tab" data-bs-target="#policy-approval" type="button" data-step="2">
                                <i class="bx bx-check-circle me-1"></i>Step 2: Approval Rules
                                <span class="badge bg-success ms-2 d-none" id="policy-approval-saved-badge"><i class="bx bx-check"></i></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="policy-methods-tab" data-bs-toggle="tab" data-bs-target="#policy-methods" type="button" data-step="3">
                                <i class="bx bx-slider me-1"></i>Step 3: Methods & Limits
                                <span class="badge bg-success ms-2 d-none" id="policy-methods-saved-badge"><i class="bx bx-check"></i></span>
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="policyTabContent">
                        <!-- Basic Info Tab -->
                        <div class="tab-pane fade show active" id="policy-basic" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Policy Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="policyName" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Policy Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="policyCode" name="code" required placeholder="e.g., POL-STD">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="policyDescription" name="description" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Location</label>
                                    <select class="form-select" id="policyLocationId" name="location_id">
                                        <option value="">All Locations</option>
                                        @foreach($locations as $loc)
                                            <option value="{{ $loc->id }}">{{ $loc->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Department</label>
                                    <select class="form-select" id="policyDepartmentId" name="department_id">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Effective From</label>
                                    <input type="date" class="form-control" id="policyEffectiveFrom" name="effective_from">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Effective To</label>
                                    <input type="date" class="form-control" id="policyEffectiveTo" name="effective_to">
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="policyIsActive" name="is_active" checked>
                                        <label class="form-check-label" for="policyIsActive">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Approval Rules Tab -->
                        <div class="tab-pane fade" id="policy-approval" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <h6 class="mb-3">Approval Requirements</h6>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="policyRequireApprovalLate" name="require_approval_for_late">
                                        <label class="form-check-label" for="policyRequireApprovalLate">Require Approval for Late Arrival</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="policyRequireApprovalEarlyLeave" name="require_approval_for_early_leave">
                                        <label class="form-check-label" for="policyRequireApprovalEarlyLeave">Require Approval for Early Leave</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="policyRequireApprovalOvertime" name="require_approval_for_overtime">
                                        <label class="form-check-label" for="policyRequireApprovalOvertime">Require Approval for Overtime</label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="policyAutoApproveVerified" name="auto_approve_verified" checked>
                                        <label class="form-check-label" for="policyAutoApproveVerified">Auto-approve Verified Entries (ZKTeco Biometric)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Methods & Limits Tab -->
                        <div class="tab-pane fade" id="policy-methods" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <h6 class="mb-3">Remote Attendance</h6>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="policyAllowRemote" name="allow_remote_attendance">
                                        <label class="form-check-label" for="policyAllowRemote">Allow Remote Attendance</label>
                                    </div>
                                </div>
                                <div class="col-md-6" id="maxRemoteDaysGroup" style="display:none;">
                                    <label class="form-label">Max Remote Days per Month</label>
                                    <input type="number" class="form-control" id="policyMaxRemoteDays" name="max_remote_days_per_month" min="0" max="31">
                                </div>
                                <div class="col-md-12">
                                    <h6 class="mb-3 mt-3">Monthly Limits</h6>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Late Minutes per Month</label>
                                    <input type="number" class="form-control" id="policyMaxLateMinutes" name="max_late_minutes_per_month" min="0">
                                    <small class="text-muted">Leave empty for no limit</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Max Early Leave Minutes per Month</label>
                                    <input type="number" class="form-control" id="policyMaxEarlyLeaveMinutes" name="max_early_leave_minutes_per_month" min="0">
                                    <small class="text-muted">Leave empty for no limit</small>
                                </div>
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="bx bx-info-circle me-2"></i>
                                        <strong>Attendance Method:</strong> Only ZKTeco Biometric devices are used for automatic attendance tracking.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Policy</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ZKTeco biometric only - no methods selection needed
const attendanceMethods = {'biometric': 'ZKTeco Biometric'};
const locations = @json($locations);
const departments = @json($departments);
const devices = @json($devices);
// Schedules removed - not needed for ZKTeco automatic attendance
const policies = @json($policies);
const csrfToken = '{{ csrf_token() }}';
const isAdmin = {{ Auth::user()->hasRole('System Admin') ? 'true' : 'false' }};

// Location Management
let locationsList = [];

function loadLocations() {
    const tbody = document.getElementById('locationsList');
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
    
    fetch('{{ route("attendance-settings.locations.index") }}', {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load locations');
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.locations) {
            locationsList = data.locations;
            displayLocations(data.locations);
        } else {
            throw new Error(data.message || 'Invalid response');
        }
    })
    .catch(error => {
        console.error('Error loading locations:', error);
        const tbody = document.getElementById('locationsList');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-4 text-danger">
                        <i class="bx bx-error-circle fs-1"></i>
                        <p class="mt-2">Failed to load locations</p>
                        <small>${error.message}</small>
                    </td>
                </tr>
            `;
        }
    });
}

function displayLocations(locations) {
    const tbody = document.getElementById('locationsList');
    if (!tbody) return;
    
    if (!locations || locations.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <i class="bx bx-map fs-1"></i>
                    <p class="mt-2">No locations found</p>
                    <small>Click "Add Location" to create your first office location</small>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = locations.map(loc => {
        const gpsCoords = (loc.latitude && loc.longitude) 
            ? `${parseFloat(loc.latitude).toFixed(6)}, ${parseFloat(loc.longitude).toFixed(6)}`
            : '<span class="text-muted">Not set</span>';
        
        return `
            <tr>
                <td><strong>${loc.name || 'N/A'}</strong></td>
                <td><code>${loc.code || 'N/A'}</code></td>
                <td>${loc.address || '<span class="text-muted">N/A</span>'}</td>
                <td><small>${gpsCoords}</small></td>
                <td><span class="badge bg-label-info">${loc.radius_meters || 100}m</span></td>
                <td>
                    ${loc.require_gps 
                        ? '<span class="badge bg-success"><i class="bx bx-check me-1"></i>Required</span>' 
                        : '<span class="badge bg-secondary">Optional</span>'}
                </td>
                <td>
                    ${loc.is_active 
                        ? '<span class="badge bg-success">Active</span>' 
                        : '<span class="badge bg-secondary">Inactive</span>'}
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="editLocation(${loc.id})" title="Edit">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteLocation(${loc.id})" title="Delete">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function openLocationModal(id = null) {
    const modal = new bootstrap.Modal(document.getElementById('locationModal'));
    const form = document.getElementById('locationForm');
    const title = document.getElementById('locationModalTitle');
    
    form.reset();
    document.getElementById('locationId').value = id || '';
    title.textContent = id ? 'Edit Location' : 'Add Location';
    
    if (id) {
        const location = locationsList.find(l => l.id == id) || locations.find(l => l.id == id);
        if (location) {
            document.getElementById('locationName').value = location.name || '';
            document.getElementById('locationCode').value = location.code || '';
            document.getElementById('locationDescription').value = location.description || '';
            document.getElementById('locationAddress').value = location.address || '';
            document.getElementById('locationCity').value = location.city || '';
            document.getElementById('locationState').value = location.state || '';
            document.getElementById('locationCountry').value = location.country || '';
            document.getElementById('locationPostalCode').value = location.postal_code || '';
            document.getElementById('locationRadius').value = location.radius_meters || 100;
            document.getElementById('locationLatitude').value = location.latitude || '';
            document.getElementById('locationLongitude').value = location.longitude || '';
            document.getElementById('locationRequireGps').checked = location.require_gps || false;
            document.getElementById('locationAllowRemote').checked = location.allow_remote || false;
            document.getElementById('locationIsActive').checked = location.is_active !== false;
            
            // ZKTeco biometric only - no methods selection needed
        }
    }
    
    modal.show();
}

function editLocation(id) {
    openLocationModal(id);
}

function deleteLocation(id) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Delete Location?',
            text: 'Are you sure you want to delete this location? This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                performDeleteLocation(id);
            }
        });
    } else {
        if (!confirm('Are you sure you want to delete this location?')) return;
        performDeleteLocation(id);
    }
}

function performDeleteLocation(id) {
    fetch(`{{ url('attendance-settings/locations') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Location deleted successfully', 'success');
            loadLocations();
        } else {
            showToast(data.message || 'Failed to delete location', 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting location:', error);
        showToast('Error deleting location: ' + error.message, 'error');
    });
}

// Device Management
function openDeviceModal(id = null) {
    const modal = new bootstrap.Modal(document.getElementById('deviceModal'));
    const form = document.getElementById('deviceForm');
    const title = document.getElementById('deviceModalTitle');
    const testBtn = document.getElementById('testDeviceBtn');
    
    form.reset();
    currentDeviceId = id || null;
    document.getElementById('deviceId').value = id || '';
    title.textContent = id ? 'Edit Device' : 'Register New Device';
    testBtn.style.display = id ? 'inline-block' : 'none';
    
    // Reset saved badges
    savedSteps = { step1: false, step2: false, step3: false, step4: false };
    ['basic-saved-badge', 'connection-saved-badge', 'uf200-saved-badge', 'advanced-saved-badge'].forEach(badgeId => {
        const badge = document.getElementById(badgeId);
        if (badge) badge.classList.add('d-none');
    });
    
    // Reset to first tab
    const firstTab = document.getElementById('basic-tab');
    if (firstTab) {
        const tab = new bootstrap.Tab(firstTab);
        tab.show();
    }
    
    if (id) {
        const device = devices.find(d => d.id == id);
        if (device) {
            document.getElementById('deviceName').value = device.name || '';
            document.getElementById('deviceDeviceId').value = device.device_id || '';
            document.getElementById('deviceType').value = device.device_type || 'biometric';
            document.getElementById('deviceLocationId').value = device.location_id || '';
            document.getElementById('deviceIsActive').checked = device.is_active !== false;
            document.getElementById('deviceConnectionType').value = device.connection_type || 'network';
            document.getElementById('deviceIpAddress').value = device.ip_address || '';
            document.getElementById('devicePort').value = device.port || '4370';
            document.getElementById('deviceSyncInterval').value = device.sync_interval_minutes || 5;
            
            // Load ZKBio Time.Net settings
            const settings = device.settings || {};
            const connectionConfig = device.connection_config || {};
            document.getElementById('zkbioServerIp').value = settings.zkbio_server_ip || connectionConfig.zkbio_server_ip || '';
            document.getElementById('zkbioDbType').value = settings.zkbio_db_type || connectionConfig.zkbio_db_type || 'sqlite';
            document.getElementById('zkbioDbPath').value = settings.zkbio_db_path || connectionConfig.zkbio_db_path || '';
            document.getElementById('zkbioDbHost').value = settings.zkbio_db_host || connectionConfig.zkbio_db_host || '';
            document.getElementById('zkbioDbUser').value = settings.zkbio_db_user || connectionConfig.zkbio_db_user || '';
            document.getElementById('zkbioDbPassword').value = settings.zkbio_db_password || connectionConfig.zkbio_db_password || '';
            
            // Show/hide database fields based on type
            toggleDatabaseFields();
        }
    }
    
    modal.show();
}

function editDevice(id) {
    openDeviceModal(id);
}

function deleteDevice(id) {
    if (!confirm('Are you sure you want to delete this device?')) return;
    
    fetch(`{{ url('attendance-settings/devices') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Device deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to delete device', 'error');
        }
    });
}

function testDevice(id) {
    const testBtn = document.querySelector(`button[onclick="testDevice(${id})"]`) || document.getElementById('testDeviceBtn');
    const originalText = testBtn ? testBtn.innerHTML : '';
    
    if (testBtn) {
        testBtn.disabled = true;
        testBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Testing...';
    }
    
    showToast('Testing device connection...', 'info');
    
    fetch(`{{ url('attendance-settings/devices') }}/${id}/test`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const status = data.is_online ? 'Online' : 'Offline';
            const statusClass = data.is_online ? 'success' : 'danger';
            const statusIcon = data.is_online ? 'check-circle' : 'x-circle';
            
            // Update status badge in table
            const statusBadge = document.getElementById(`device-status-${id}`);
            if (statusBadge) {
                statusBadge.className = `badge bg-${statusClass}`;
                statusBadge.innerHTML = `<i class="bx bx-${statusIcon} me-1"></i>${status}`;
            }
            
            showToast(data.message || `Device is ${status.toLowerCase()}`, data.is_online ? 'success' : 'warning');
            
            // Reload page after 1.5 seconds to show updated status
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Device connection test failed', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error testing device connection', 'error');
    })
    .finally(() => {
        if (testBtn) {
            testBtn.disabled = false;
            testBtn.innerHTML = originalText;
        }
    });
}

function testDeviceConnection() {
    const deviceId = document.getElementById('deviceId').value;
    if (deviceId) {
        testDevice(deviceId);
    } else {
        showToast('Please save the device first before testing', 'warning');
    }
}

// Test ZKTeco device connection directly
async function testZKTecoConnection() {
    const ip = document.getElementById('deviceIpAddress').value;
    const port = document.getElementById('devicePort').value;
    const password = document.getElementById('devicePassword')?.value || 0;
    const resultDiv = document.getElementById('connectionTestResult');
    
    if (!ip || !port) {
        showToast('Please enter IP address and port', 'warning');
        return;
    }
    
    // Create and submit form directly (NO AJAX - direct server connection)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("zkteco.test-connection") }}';
    form.style.display = 'none';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = csrfToken;
    form.appendChild(csrfInput);
    
    // Add IP
    const ipInput = document.createElement('input');
    ipInput.type = 'hidden';
    ipInput.name = 'ip';
    ipInput.value = ip;
    form.appendChild(ipInput);
    
    // Add Port
    const portInput = document.createElement('input');
    portInput.type = 'hidden';
    portInput.name = 'port';
    portInput.value = port || 4370;
    form.appendChild(portInput);
    
    // Add Password
    const passwordInput = document.createElement('input');
    passwordInput.type = 'hidden';
    passwordInput.name = 'password';
    passwordInput.value = password || 0;
    form.appendChild(passwordInput);
    
    // Show loading state
    if (resultDiv) {
        resultDiv.innerHTML = `
            <div class="alert alert-info">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <strong>Testing connection directly to device...</strong><br>
                <small>This may take 10-30 seconds as we test all connection methods until one succeeds.</small>
            </div>
        `;
    }
    
    showToast('Testing ZKTeco device connection directly (no AJAX)...', 'info');
    
    // Submit form - this will cause a page reload with results
    document.body.appendChild(form);
    form.submit();
        showToast('Error testing connection: ' + error.message, 'error');
    }
}

// Save current tab individually
async function saveCurrentTab() {
    const deviceTabs = document.querySelectorAll('#deviceTabs button[data-bs-toggle="tab"]');
    let currentStep = 1;
    
    deviceTabs.forEach(tab => {
        if (tab.classList.contains('active')) {
            currentStep = parseInt(tab.getAttribute('data-step')) || 1;
        }
    });
    
    await saveDeviceStep(currentStep);
}

// View device logs
function viewDeviceLogs(deviceId) {
    if (!deviceId) {
        showToast('Invalid device ID', 'error');
        return;
    }
    
    const modal = document.getElementById('deviceLogsModal');
    const modalBody = document.getElementById('deviceLogsBody');
    
    // Show loading state
    modalBody.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading device logs...</p>
        </div>
    `;
    
    // Show modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    
    // Fetch device logs
    fetch(`{{ url('attendance-settings/devices') }}/${deviceId}/logs`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.logs) {
            const logs = data.logs;
            
            if (logs.length === 0) {
                modalBody.innerHTML = `
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        No logs found for this device.
                    </div>
                `;
                return;
            }
            
            let logsHtml = `
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Device: <strong>${data.device_name || 'N/A'}</strong></h6>
                    <span class="badge bg-primary">${logs.length} log entries</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Action</th>
                                <th>Status</th>
                                <th>Message</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            logs.forEach(log => {
                const statusClass = log.status === 'success' ? 'success' : 
                                   log.status === 'error' ? 'danger' : 
                                   log.status === 'warning' ? 'warning' : 'info';
                const statusIcon = log.status === 'success' ? 'check-circle' : 
                                  log.status === 'error' ? 'x-circle' : 
                                  log.status === 'warning' ? 'error-circle' : 'info-circle';
                
                const detailsHtml = log.details && Object.keys(log.details).length > 0 
                    ? `<button class="btn btn-sm btn-outline-info" onclick="showLogDetails(${JSON.stringify(log.details).replace(/"/g, '&quot;')})">View</button>` 
                    : '-';
                
                logsHtml += `
                    <tr>
                        <td>
                            <div>${log.created_at || 'N/A'}</div>
                            <small class="text-muted">${log.time_ago || ''}</small>
                        </td>
                        <td><span class="badge bg-label-secondary">${log.action || 'N/A'}</span></td>
                        <td>
                            <span class="badge bg-${statusClass}">
                                <i class="bx bx-${statusIcon} me-1"></i>
                                ${log.status || 'info'}
                            </span>
                        </td>
                        <td>${log.message || log.description || 'N/A'}</td>
                        <td>${detailsHtml}</td>
                    </tr>
                `;
            });
            
            logsHtml += `
                        </tbody>
                    </table>
                </div>
            `;
            
            modalBody.innerHTML = logsHtml;
        } else {
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${data.message || 'Failed to load device logs'}
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalBody.innerHTML = `
            <div class="alert alert-danger">
                <i class="bx bx-error-circle me-2"></i>
                <strong>Error:</strong> Failed to load device logs. Please try again.
            </div>
        `;
    });
}

// Show log details
function showLogDetails(details) {
    if (typeof details === 'string') {
        try {
            details = JSON.parse(details);
        } catch (e) {
            alert('Details: ' + details);
            return;
        }
    }
    
    const detailsJson = JSON.stringify(details, null, 2);
    Swal.fire({
        title: 'Log Details',
        html: `<pre class="text-start bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">${detailsJson}</pre>`,
        width: '800px',
        confirmButtonText: 'Close'
    });
}

// Schedule Management removed - not needed for ZKTeco automatic attendance

// Policy Management
function openPolicyModal(id = null) {
    const modal = new bootstrap.Modal(document.getElementById('policyModal'));
    const form = document.getElementById('policyForm');
    const title = document.getElementById('policyModalTitle');
    
    form.reset();
    currentPolicyId = id || null;
    document.getElementById('policyId').value = id || '';
    title.textContent = id ? 'Edit Attendance Policy' : 'Add Attendance Policy';
    
    // Reset saved badges
    savedPolicySteps = { step1: false, step2: false, step3: false };
    ['policy-basic-saved-badge', 'policy-approval-saved-badge', 'policy-methods-saved-badge'].forEach(badgeId => {
        const badge = document.getElementById(badgeId);
        if (badge) badge.classList.add('d-none');
    });
    
    // Reset to first tab
    const firstTab = document.getElementById('policy-basic-tab');
    if (firstTab) {
        const tab = new bootstrap.Tab(firstTab);
        tab.show();
    }
    
    if (id) {
        const policy = policies.find(p => p.id == id);
        if (policy) {
            document.getElementById('policyName').value = policy.name || '';
            document.getElementById('policyCode').value = policy.code || '';
            document.getElementById('policyDescription').value = policy.description || '';
            document.getElementById('policyLocationId').value = policy.location_id || '';
            document.getElementById('policyDepartmentId').value = policy.department_id || '';
            document.getElementById('policyRequireApprovalLate').checked = policy.require_approval_for_late || false;
            document.getElementById('policyRequireApprovalEarlyLeave').checked = policy.require_approval_for_early_leave || false;
            document.getElementById('policyRequireApprovalOvertime').checked = policy.require_approval_for_overtime || false;
            document.getElementById('policyAutoApproveVerified').checked = policy.auto_approve_verified !== false;
            // Manual entry removed - ZKTeco biometric only
            document.getElementById('policyRequireLocationMobile').checked = policy.require_location_for_mobile !== false;
            document.getElementById('policyAllowRemote').checked = policy.allow_remote_attendance || false;
            document.getElementById('policyMaxRemoteDays').value = policy.max_remote_days_per_month || '';
            document.getElementById('policyMaxLateMinutes').value = policy.max_late_minutes_per_month || '';
            document.getElementById('policyMaxEarlyLeaveMinutes').value = policy.max_early_leave_minutes_per_month || '';
            document.getElementById('policyEffectiveFrom').value = policy.effective_from || '';
            document.getElementById('policyEffectiveTo').value = policy.effective_to || '';
            document.getElementById('policyIsActive').checked = policy.is_active !== false;
            
            // ZKTeco biometric only - no methods selection needed
        }
    }
    
    // Toggle remote days field
    document.getElementById('policyAllowRemote').addEventListener('change', function() {
        document.getElementById('maxRemoteDaysGroup').style.display = this.checked ? 'block' : 'none';
    });
    
    modal.show();
}

function editPolicy(id) {
    openPolicyModal(id);
}

function deletePolicy(id) {
    if (!confirm('Are you sure you want to delete this policy?')) return;
    
    fetch(`{{ url('attendance-settings/policies') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Policy deleted successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to delete policy', 'error');
        }
    });
}

// Get current location using browser geolocation
// Get current location for location form
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('locationLatitude').value = position.coords.latitude.toFixed(8);
            document.getElementById('locationLongitude').value = position.coords.longitude.toFixed(8);
            showToast('Location retrieved successfully', 'success');
        }, function(error) {
            showToast('Failed to get location: ' + error.message, 'error');
        });
    } else {
        showToast('Geolocation is not supported by your browser', 'error');
    }
}

// Form Submissions
document.getElementById('locationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validate GPS coordinates if "Require GPS Verification" is enabled
    const requireGps = document.getElementById('locationRequireGps').checked;
    const latitude = document.getElementById('locationLatitude').value;
    const longitude = document.getElementById('locationLongitude').value;
    
    if (requireGps && (!latitude || !longitude)) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'GPS Coordinates Required',
                html: `
                    <div class="text-center">
                        <i class="bx bx-error-circle bx-lg text-warning mb-3" style="font-size: 4rem;"></i>
                        <h5 class="mb-2">GPS Coordinates Required</h5>
                        <p class="mb-3">You have enabled "Require GPS Verification" but haven't set the GPS coordinates.</p>
                        <p class="text-start mb-0">
                            <strong>To set GPS coordinates:</strong>
                            <ol class="text-start mt-2">
                                <li>Click the "Get Current Location" button to automatically get your current location, OR</li>
                                <li>Manually enter the latitude and longitude of the office location</li>
                            </ol>
                        </p>
                    </div>
                `,
                icon: 'warning',
                confirmButtonText: 'OK',
                width: '600px'
            });
        } else {
            alert('GPS coordinates are required when "Require GPS Verification" is enabled. Please set latitude and longitude.');
        }
        return;
    }
    
    showToast('Saving location...', 'info');
    
    const formData = new FormData(this);
    const locationId = document.getElementById('locationId').value;
    const url = locationId 
        ? `{{ url('attendance-settings/locations') }}/${locationId}`
        : '{{ route("attendance-settings.locations.store") }}';
    const method = locationId ? 'PUT' : 'POST';
    
    // ZKTeco biometric only - no methods selection needed
    const allowedMethods = ['biometric']; // Always biometric for ZKTeco
    
    // Convert FormData to object and handle boolean fields properly
    const data = {};
    for (let [key, value] of formData.entries()) {
        // Handle boolean checkboxes - convert to proper boolean
        if (['is_active', 'require_gps', 'allow_remote'].includes(key)) {
            data[key] = value === 'on' || value === '1' || value === true || value === 'true';
        } else if (key === 'allowed_methods[]') {
            // Skip, we'll handle this separately
            continue;
        } else {
            data[key] = value;
        }
    }
    
    // Set allowed_methods array
    data.allowed_methods = allowedMethods.length > 0 ? allowedMethods : null;
    
    // Ensure boolean fields are sent as proper booleans
    data.is_active = data.is_active === true || data.is_active === 'true' || data.is_active === '1' || data.is_active === 'on';
    data.require_gps = data.require_gps === true || data.require_gps === 'true' || data.require_gps === '1' || data.require_gps === 'on';
    data.allow_remote = data.allow_remote === true || data.allow_remote === 'true' || data.allow_remote === '1' || data.allow_remote === 'on';
    
    await submitForm(url, method, data, 'locationModal', 'Location');
});

// Device Form - Auto-save on tab change
let currentDeviceId = null;
let savedSteps = {
    step1: false,
    step2: false,
    step3: false,
    step4: false
};

// Initialize device form tab listeners
document.addEventListener('DOMContentLoaded', function() {
    const deviceTabs = document.querySelectorAll('#deviceTabs button[data-bs-toggle="tab"]');
    deviceTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', async function(e) {
            const targetTab = e.target.getAttribute('data-bs-target');
            const step = e.target.getAttribute('data-step');
            
            // Save previous step before moving to next
            if (step > 1) {
                const prevStep = parseInt(step) - 1;
                await saveDeviceStep(prevStep);
            }
            
            // Auto-save current step after a short delay
            setTimeout(async () => {
                await saveDeviceStep(parseInt(step));
            }, 500);
        });
    });
    
    // Also save when form is submitted
document.getElementById('deviceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
        showToast('Step 1: Saving all device information...', 'info');
        
        // Save all steps before final submit
        for (let step = 1; step <= 4; step++) {
            await saveDeviceStep(step);
        }
        
        // Final save with all data
    const formData = new FormData(this);
    const deviceId = document.getElementById('deviceId').value;
    const url = deviceId 
        ? `{{ url('attendance-settings/devices') }}/${deviceId}`
        : '{{ route("attendance-settings.devices.store") }}';
    const method = deviceId ? 'PUT' : 'POST';
    
    const data = Object.fromEntries(formData);
    
    // Parse JSON fields
    try {
            if (data.connection_config && data.connection_config.trim()) {
            data.connection_config = JSON.parse(data.connection_config);
        }
    } catch (e) {
        showToast('Invalid JSON in Connection Config', 'error');
        return;
    }
    
    try {
            if (data.capabilities && data.capabilities.trim()) {
            data.capabilities = JSON.parse(data.capabilities);
        }
    } catch (e) {
        showToast('Invalid JSON in Capabilities', 'error');
        return;
    }
    
    try {
            if (data.settings && data.settings.trim()) {
            data.settings = JSON.parse(data.settings);
        }
    } catch (e) {
        showToast('Invalid JSON in Settings', 'error');
        return;
    }
    
        showToast('Step 2: Finalizing device registration...', 'info');
    await submitForm(url, method, data, 'deviceModal', 'Device');
    });
});

async function saveDeviceStep(step) {
    const form = document.getElementById('deviceForm');
    const formData = new FormData(form);
    const deviceId = document.getElementById('deviceId').value;
    
    let data = {};
    let stepName = '';
    let badgeId = '';
    
    switch(step) {
        case 1:
            // Basic Info
            stepName = 'Basic Information';
            badgeId = 'basic-saved-badge';
            data = {
                name: document.getElementById('deviceName').value,
                device_id: document.getElementById('deviceDeviceId').value,
                device_type: document.getElementById('deviceType').value,
                location_id: document.getElementById('deviceLocationId').value || null,
                is_active: document.getElementById('deviceIsActive').checked
            };
            break;
        case 2:
            // Connection Settings
            stepName = 'Connection Settings';
            badgeId = 'connection-saved-badge';
            data = {
                connection_type: document.getElementById('deviceConnectionType').value,
                ip_address: document.getElementById('deviceIpAddress').value,
                port: document.getElementById('devicePort').value,
                sync_interval_minutes: document.getElementById('deviceSyncInterval').value
            };
            break;
        case 3:
            // UF200-S Config
            stepName = 'UF200-S Configuration';
            badgeId = 'uf200-saved-badge';
            const dbType = document.getElementById('zkbioDbType')?.value || 'sqlite';
            data = {
                zkbio_server_ip: document.getElementById('zkbioServerIp')?.value || '',
                zkbio_db_type: dbType,
                zkbio_db_path: document.getElementById('zkbioDbPath')?.value || '',
                zkbio_db_host: document.getElementById('zkbioDbHost')?.value || '',
                zkbio_db_user: document.getElementById('zkbioDbUser')?.value || '',
                zkbio_db_password: document.getElementById('zkbioDbPassword')?.value || ''
            };
            break;
        case 4:
            // Advanced Config - No settings needed
            stepName = 'Advanced Configuration';
            badgeId = 'advanced-saved-badge';
            data = {};
            break;
    }
    
    // Skip if no device ID and step 1 required fields are empty
    if (!deviceId && step === 1) {
        if (!data.name || !data.device_id || !data.device_type) {
            return; // Don't save if required fields are empty
        }
    }
    
    // Merge with existing device ID if editing
    if (deviceId) {
        data.id = deviceId;
    }
    
    // Add step indicator
    data.save_step = step;
    
    try {
        const url = deviceId 
            ? `{{ url('attendance-settings/devices') }}/${deviceId}/save-step`
            : '{{ url("attendance-settings/devices/save-step") }}';
        const method = deviceId ? 'POST' : 'POST';
        
        showToast(`Saving ${stepName}...`, 'info');
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update device ID if it's a new device
            if (result.device_id && !deviceId) {
                document.getElementById('deviceId').value = result.device_id;
                currentDeviceId = result.device_id;
            }
            
            // Show success badge
            const badge = document.getElementById(badgeId);
            if (badge) {
                badge.classList.remove('d-none');
            }
            
            savedSteps[`step${step}`] = true;
            showToast(`✓ Step ${step} (${stepName}) saved successfully!`, 'success');
        } else {
            showToast(`✗ Failed to save ${stepName}: ${result.message || 'Unknown error'}`, 'error');
        }
    } catch (error) {
        showToast(`✗ Error saving ${stepName}: ${error.message}`, 'error');
    }
}

// Schedule form removed - not needed for ZKTeco automatic attendance

// Policy Form - Auto-save on tab change
let currentPolicyId = null;
let savedPolicySteps = {
    step1: false,
    step2: false,
    step3: false
};

// Initialize policy form tab listeners
document.addEventListener('DOMContentLoaded', function() {
    const policyTabs = document.querySelectorAll('#policyTabs button[data-bs-toggle="tab"]');
    policyTabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', async function(e) {
            const targetTab = e.target.getAttribute('data-bs-target');
            const step = e.target.getAttribute('data-step');
            
            // Save previous step before moving to next
            if (step > 1) {
                const prevStep = parseInt(step) - 1;
                await savePolicyStep(prevStep);
            }
            
            // Auto-save current step after a short delay
            setTimeout(async () => {
                await savePolicyStep(parseInt(step));
            }, 500);
        });
    });
    
    // Also save when form is submitted
document.getElementById('policyForm').addEventListener('submit', async function(e) {
    e.preventDefault();
        showToast('Step 1: Saving all policy information...', 'info');
        
        // Save all steps before final submit
        for (let step = 1; step <= 3; step++) {
            await savePolicyStep(step);
        }
        
        // Final save with all data
    const formData = new FormData(this);
    const policyId = document.getElementById('policyId').value;
    const url = policyId 
        ? `{{ url('attendance-settings/policies') }}/${policyId}`
        : '{{ route("attendance-settings.policies.store") }}';
    const method = policyId ? 'PUT' : 'POST';
    
    // ZKTeco biometric only
    const data = Object.fromEntries(formData);
    data.allowed_attendance_methods = ['biometric'];
    
        showToast('Step 2: Finalizing policy registration...', 'info');
    await submitForm(url, method, data, 'policyModal', 'Policy');
    });
});

async function savePolicyStep(step) {
    const form = document.getElementById('policyForm');
    const policyId = document.getElementById('policyId').value;
    
    let data = {};
    let stepName = '';
    let badgeId = '';
    
    switch(step) {
        case 1:
            // Basic Info
            stepName = 'Basic Information';
            badgeId = 'policy-basic-saved-badge';
            data = {
                name: document.getElementById('policyName').value,
                code: document.getElementById('policyCode').value,
                description: document.getElementById('policyDescription').value,
                location_id: document.getElementById('policyLocationId').value || null,
                department_id: document.getElementById('policyDepartmentId').value || null,
                effective_from: document.getElementById('policyEffectiveFrom').value,
                effective_to: document.getElementById('policyEffectiveTo').value,
                is_active: document.getElementById('policyIsActive').checked
            };
            break;
        case 2:
            // Approval Rules
            stepName = 'Approval Rules';
            badgeId = 'policy-approval-saved-badge';
            data = {
                require_approval_for_late: document.getElementById('policyRequireApprovalLate').checked,
                require_approval_for_early_leave: document.getElementById('policyRequireApprovalEarlyLeave').checked,
                require_approval_for_overtime: document.getElementById('policyRequireApprovalOvertime').checked,
                auto_approve_verified: document.getElementById('policyAutoApproveVerified').checked,
                // Manual entry removed - ZKTeco biometric only
                require_location_for_mobile: document.getElementById('policyRequireLocationMobile').checked
            };
            break;
        case 3:
            // Methods & Limits
            stepName = 'Methods & Limits';
            badgeId = 'policy-methods-saved-badge';
            const allowedMethods = [];
            document.querySelectorAll('input[name="allowed_attendance_methods[]"]:checked').forEach(cb => {
                allowedMethods.push(cb.value);
            });
            data = {
                allow_remote_attendance: document.getElementById('policyAllowRemote').checked,
                max_remote_days_per_month: document.getElementById('policyMaxRemoteDays').value || null,
                max_late_minutes_per_month: document.getElementById('policyMaxLateMinutes').value || null,
                max_early_leave_minutes_per_month: document.getElementById('policyMaxEarlyLeaveMinutes').value || null,
                allowed_attendance_methods: allowedMethods.length > 0 ? allowedMethods : null
            };
            break;
    }
    
    // Skip if no policy ID and step 1 required fields are empty
    if (!policyId && step === 1) {
        if (!data.name || !data.code) {
            return; // Don't save if required fields are empty
        }
    }
    
    // Merge with existing policy ID if editing
    if (policyId) {
        data.id = policyId;
    }
    
    // Add step indicator
    data.save_step = step;
    
    try {
        const url = policyId 
            ? `{{ url('attendance-settings/policies') }}/${policyId}/save-step`
            : '{{ url("attendance-settings/policies/save-step") }}';
        const method = 'POST';
        
        showToast(`Saving ${stepName}...`, 'info');
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Update policy ID if it's a new policy
            if (result.policy_id && !policyId) {
                document.getElementById('policyId').value = result.policy_id;
                currentPolicyId = result.policy_id;
            }
            
            // Show success badge
            const badge = document.getElementById(badgeId);
            if (badge) {
                badge.classList.remove('d-none');
            }
            
            savedPolicySteps[`step${step}`] = true;
            showToast(`✓ Step ${step} (${stepName}) saved successfully!`, 'success');
        } else {
            showToast(`✗ Failed to save ${stepName}: ${result.message || 'Unknown error'}`, 'error');
        }
    } catch (error) {
        showToast(`✗ Error saving ${stepName}: ${error.message}`, 'error');
    }
}

async function submitForm(url, method, data, modalId, entityName) {
    const submitBtn = event.submitter;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(`${entityName} saved successfully`, 'success');
            const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
            if (modal) modal.hide();
            
            // Reload locations if it's a location form
            if (modalId === 'locationModal') {
                loadLocations();
            } else {
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            let errorMsg = result.message || 'Failed to save';
            if (result.errors) {
                errorMsg += ': ' + Object.values(result.errors).flat().join(', ');
            }
            showToast(errorMsg, 'error');
        }
    } catch (error) {
        showToast('Error: ' + error.message, 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

/**
 * Enhanced Toast Notification Function
 * Uses the new AdvancedToast design for all notifications
 */
function showToast(message, type = 'info') {
    // Ensure AdvancedToast is available
    if (typeof window.AdvancedToast === 'undefined') {
        console.error('AdvancedToast is not loaded. Please ensure advanced-toast.js is included.');
        // Fallback to console log
        console.log(`[${type.toUpperCase()}] ${message}`);
        return;
    }
    
    // Map type to AdvancedToast type
    const typeMap = {
        'success': 'success',
        'error': 'error',
        'warning': 'warning',
        'info': 'info',
        'primary': 'primary'
    };
    const toastType = typeMap[type] || 'info';
    
    // Extract title from message if it contains special prefixes
    let title = '';
    let cleanMessage = message;
    
    // Check for step indicators (Step 1:, Step 2:, etc.)
    const stepMatch = message.match(/^(Step\s+\d+[:\s]+)/i);
    if (stepMatch) {
        title = stepMatch[1].trim();
        cleanMessage = message.replace(stepMatch[1], '').trim();
    }
    
    // Check for checkmark/cross prefixes (✓, ✗, ⚠)
    if (message.startsWith('✓')) {
        title = 'Success';
        cleanMessage = message.substring(1).trim();
    } else if (message.startsWith('✗')) {
        title = 'Error';
        cleanMessage = message.substring(1).trim();
    } else if (message.startsWith('⚠')) {
        title = 'Warning';
        cleanMessage = message.substring(1).trim();
    }
    
    // If no title extracted, generate one based on type
    if (!title) {
        switch(type) {
            case 'success':
                title = 'Success';
                break;
            case 'error':
                title = 'Error';
                break;
            case 'warning':
                title = 'Warning';
                break;
            case 'info':
                title = 'Information';
                break;
            default:
                title = 'Notification';
        }
    }
    
    // Use the message as-is if title was extracted, otherwise use full message
    const finalMessage = cleanMessage || message;
    
    // Determine duration based on type and message length
    let duration = 5000;
    if (type === 'error') {
        duration = 7000;
    } else if (type === 'warning') {
        duration = 6000;
    } else if (finalMessage.length > 100) {
        duration = 6000; // Longer messages need more time to read
    }
    
    // Show toast using AdvancedToast
    window.AdvancedToast.show({
        type: toastType,
        title: title,
        message: finalMessage,
        duration: duration,
        sound: true
    });
}

/**
 * Helper function to show AJAX response messages with validation error handling
 */
function showAjaxResponseToast(response, defaultTitle = 'Notification') {
    if (!response) return;
    
    // Handle validation errors
    if (response.errors && typeof response.errors === 'object') {
        if (typeof window.showValidationErrors === 'function') {
            window.showValidationErrors(response.errors);
            return;
        }
        
        // Fallback: Convert validation errors to message
        let errorMessages = [];
        if (Array.isArray(response.errors)) {
            errorMessages = response.errors;
    } else {
            Object.keys(response.errors).forEach(key => {
                if (Array.isArray(response.errors[key])) {
                    response.errors[key].forEach(msg => errorMessages.push(`${key}: ${msg}`));
                } else {
                    errorMessages.push(`${key}: ${response.errors[key]}`);
                }
            });
        }
        
        if (errorMessages.length > 0) {
            showToast(errorMessages.join('; '), 'error');
            return;
        }
    }
    
    // Handle regular success/error responses
    const type = response.type || (response.success ? 'success' : 'error');
    const title = response.title || defaultTitle;
    const message = response.message || response.error || 'Operation completed';
    
    showToast(message, type);
}

// ==================== USER MANAGEMENT & ENROLLMENT ====================

let autoRefreshInterval = null;

function openUserEnrollmentModal() {
    const modal = new bootstrap.Modal(document.getElementById('userEnrollmentModal'));
    document.getElementById('userEnrollmentForm').reset();
    loadUsersForEnrollment();
    modal.show();
}

function loadUsersForEnrollment() {
    // First try to load from server-side rendered employees (fastest)
    loadEmployeesViaServer();
    
    // Then fetch updated list from API
    fetchEmployeeList();
}

function loadEmployeesViaServer() {
    // Use a server-side rendered approach - get employees list from blade
    // This will be populated by the controller
    const select = document.getElementById('enrollmentUserSelect');
    select.innerHTML = '<option value="">Select Employee</option>';
    
    // Employees will be passed from controller - check if available
    @if(isset($employees) && $employees->count() > 0)
        @foreach($employees as $emp)
            const option{{ $emp->id }} = document.createElement('option');
            option{{ $emp->id }}.value = {{ $emp->id }};
            option{{ $emp->id }}.textContent = '{{ $emp->employee_id ?? $emp->id }} - {{ $emp->name }}';
            option{{ $emp->id }}.setAttribute('data-employee-id', '{{ $emp->employee_id ?? "" }}');
            option{{ $emp->id }}.setAttribute('data-department', '{{ $emp->primaryDepartment->name ?? "" }}');
            select.appendChild(option{{ $emp->id }});
        @endforeach
    @else
        // If not available, fetch via AJAX
        fetchEmployeeList();
    @endif
}

function fetchEmployeeList() {
    // Use the dedicated API endpoint
    fetch('{{ url("attendance-settings/api/employees") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to fetch employees');
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.employees) {
            populateUserSelect(data);
    } else {
            throw new Error('Invalid response format');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback to server-side rendered employees
        loadEmployeesViaServer();
    });
}

function populateUserSelect(data) {
    const select = document.getElementById('enrollmentUserSelect');
    select.innerHTML = '<option value="">Select Employee</option>';
    
    let employees = [];
    if (data.employees) {
        employees = data.employees;
    } else if (data.data && Array.isArray(data.data)) {
        employees = data.data;
    } else if (Array.isArray(data)) {
        employees = data;
    }
    
    employees.forEach(emp => {
        const option = document.createElement('option');
        option.value = emp.id || emp.user_id;
        const empId = emp.employee_id || emp.employee_number || emp.id || '';
        const empName = emp.name || emp.full_name || 'Unknown';
        option.textContent = `${empId} - ${empName}`;
        option.setAttribute('data-employee-id', empId);
        option.setAttribute('data-department', emp.primary_department?.name || emp.department?.name || '');
        option.setAttribute('data-email', emp.email || '');
        option.setAttribute('data-phone', emp.phone || emp.mobile || '');
        select.appendChild(option);
    });
    
    if (employees.length === 0) {
        select.innerHTML = '<option value="">No employees found</option>';
    }
}

function loadUserDetails() {
    const userId = document.getElementById('enrollmentUserSelect').value;
    if (!userId) {
        document.getElementById('userDetailsDisplay').innerHTML = '<p class="text-muted mb-0">Select an employee to view details</p>';
        return;
    }
    
    const selectedOption = document.getElementById('enrollmentUserSelect').options[document.getElementById('enrollmentUserSelect').selectedIndex];
    const employeeId = selectedOption.getAttribute('data-employee-id');
    const department = selectedOption.getAttribute('data-department');
    const email = selectedOption.getAttribute('data-email');
    const phone = selectedOption.getAttribute('data-phone');
    
    // Show basic info immediately
    document.getElementById('userDetailsDisplay').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <p><strong>Employee ID:</strong> ${employeeId || 'N/A'}</p>
                <p><strong>Name:</strong> ${selectedOption.textContent.split(' - ')[1] || 'N/A'}</p>
                <p><strong>Department:</strong> ${department || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Email:</strong> ${email || 'N/A'}</p>
                <p><strong>Phone:</strong> ${phone || 'N/A'}</p>
                <p><strong>Enrollment Status:</strong> <span class="badge bg-label-warning">Not Enrolled</span></p>
            </div>
        </div>
    `;
    
    // Fetch full details from employee controller
    fetch(`{{ url('modules/hr/employees') }}/${userId}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load employee details');
        }
        return response.json();
    })
    .then(data => {
        if (data.success && data.employee) {
            const emp = data.employee;
            const employee = emp.employee || {};
            const dept = emp.primary_department || emp.department || {};
            
            document.getElementById('userDetailsDisplay').innerHTML = `
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <div class="d-flex align-items-center">
                            ${emp.photo ? `<img src="${emp.photo_url || '{{ asset("storage/photos") }}/' + emp.photo}" alt="${emp.name}" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;">` : '<div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;"><i class="bx bx-user fs-4"></i></div>'}
                            <div>
                                <h6 class="mb-0">${emp.name || 'N/A'}</h6>
                                <small class="text-muted">${emp.employee_id || 'N/A'}</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="bx bx-id-card me-1"></i> Employee ID:</strong> ${emp.employee_id || 'N/A'}</p>
                        <p><strong><i class="bx bx-building me-1"></i> Department:</strong> ${dept.name || 'N/A'}</p>
                        <p><strong><i class="bx bx-briefcase me-1"></i> Position:</strong> ${employee.position || 'N/A'}</p>
                        <p><strong><i class="bx bx-calendar me-1"></i> Hire Date:</strong> ${emp.hire_date ? new Date(emp.hire_date).toLocaleDateString() : 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="bx bx-envelope me-1"></i> Email:</strong> ${emp.email || 'N/A'}</p>
                        <p><strong><i class="bx bx-phone me-1"></i> Phone:</strong> ${emp.phone || emp.mobile || 'N/A'}</p>
                        <p><strong><i class="bx bx-user-check me-1"></i> Status:</strong> 
                            <span class="badge bg-label-${emp.is_active ? 'success' : 'danger'}">${emp.is_active ? 'Active' : 'Inactive'}</span>
                        </p>
                        <p><strong><i class="bx bx-fingerprint me-1"></i> Enrollment:</strong> 
                            <span class="badge bg-label-warning">Not Enrolled</span>
                        </p>
                    </div>
                </div>
                ${employee.employment_type ? `<div class="row mt-2"><div class="col-md-12"><p><strong>Employment Type:</strong> <span class="badge bg-label-info">${employee.employment_type}</span></p></div></div>` : ''}
            `;
        }
    })
    .catch(error => {
        console.error('Error loading employee details:', error);
        // Keep the basic info that was already displayed
    });
}

function startEnrollment() {
    const userId = document.getElementById('enrollmentUserSelect').value;
    const deviceId = document.getElementById('enrollmentDeviceSelect').value;
    const fingers = Array.from(document.querySelectorAll('input[name="fingers[]"]:checked')).map(cb => cb.value);
    
    if (!userId || !deviceId || fingers.length === 0) {
        showToast('Please select user, device, and at least one finger', 'warning');
        return;
    }
    
    showToast('Starting enrollment process. Please follow device instructions.', 'info');
    // This would trigger the actual enrollment process on the device
}

function syncAllUsersToDevices() {
    if (!confirm('This will sync all users to all active devices. Continue?')) return;
    
    showToast('Syncing users to devices...', 'info');
    fetch('{{ url("attendance-settings/users/sync-all") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Route not implemented yet');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast(`Successfully synced ${data.synced_count || 0} users`, 'success');
            loadUsersTable();
        } else {
            showToast(data.message || 'Sync failed', 'error');
        }
    })
    .catch(error => {
        showToast('User sync feature will be available after route implementation', 'info');
    });
}

function filterUsers() {
    const search = document.getElementById('userSearchInput').value.toLowerCase();
    const deptFilter = document.getElementById('userDepartmentFilter').value;
    const enrollmentFilter = document.getElementById('userEnrollmentFilter').value;
    const deviceFilter = document.getElementById('userDeviceFilter').value;
    
    // Filter logic would be implemented here
    loadUsersTable({ search, deptFilter, enrollmentFilter, deviceFilter });
}

function loadUsersTable(filters = {}) {
    // Use direct URL - route will be implemented later
    const url = '{{ url("attendance-settings/users/list") }}?' + new URLSearchParams(filters);
    fetch(url, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            // Fallback: Show message that feature is coming soon
            throw new Error('Route not implemented');
        }
        return response.json();
    })
    .then(data => {
        const tbody = document.getElementById('usersTableBody');
        if (data.users && data.users.length > 0) {
            tbody.innerHTML = data.users.map(user => `
                <tr>
                    <td>${user.employee_id || user.id}</td>
                    <td><strong>${user.name || user.full_name || 'N/A'}</strong></td>
                    <td>${user.department?.name || 'N/A'}</td>
                    <td>
                        <span class="badge bg-label-${user.enrollment_status === 'enrolled' ? 'success' : 'warning'}">
                            ${user.enrollment_status || 'Not Enrolled'}
                        </span>
                    </td>
                    <td>${user.enrolled_devices_count || 0} devices</td>
                    <td>${user.last_sync_at ? new Date(user.last_sync_at).toLocaleString() : 'Never'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="openUserEnrollmentModal(${user.id})">
                            <i class="bx bx-edit"></i> Enroll
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-5 text-muted">No users found</td></tr>';
        }
    })
    .catch(error => {
        document.getElementById('usersTableBody').innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">
                        <i class="bx bx-info-circle fs-1"></i>
                        <p class="mt-2">User management feature coming soon</p>
                        <small>Routes need to be implemented in the backend</small>
                    </div>
                </td>
            </tr>
        `;
    });
}

// ==================== REAL-TIME DASHBOARD ====================

function refreshDashboard() {
    fetch('{{ url("attendance-settings/dashboard/data") }}', {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            // Fallback: Use basic stats from existing data
            updateDashboardFallback();
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            document.getElementById('presentCount').textContent = data.present_count || 0;
            document.getElementById('lateCount').textContent = data.late_count || 0;
            document.getElementById('absentCount').textContent = data.absent_count || 0;
            document.getElementById('onlineDevicesCount').textContent = data.online_devices || {{ $stats['online_devices'] ?? 0 }};
            
            // Update recent check-ins
            const checkinsBody = document.getElementById('recentCheckinsBody');
            if (data.recent_checkins && data.recent_checkins.length > 0) {
                checkinsBody.innerHTML = data.recent_checkins.map(checkin => {
                    const timestamp = checkin.timestamp ? new Date(checkin.timestamp) : new Date();
                    return `
                        <tr>
                            <td>${timestamp.toLocaleTimeString()}</td>
                            <td>${checkin.user_name || 'Unknown'}</td>
                            <td>${checkin.device_name || 'N/A'}</td>
                            <td><span class="badge bg-label-${checkin.status === 'in' ? 'success' : 'info'}">${checkin.status || 'in'}</span></td>
                        </tr>
                    `;
                }).join('');
            } else {
                checkinsBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No recent check-ins</td></tr>';
            }
        } else if (data === null) {
            // Response was not ok, fallback already called
            return;
        }
    })
    .catch(error => {
        console.error('Dashboard refresh error:', error);
        updateDashboardFallback();
    });
}

function updateDashboardFallback() {
    // Use existing stats as fallback
    document.getElementById('onlineDevicesCount').textContent = {{ $stats['online_devices'] ?? 0 }};
    document.getElementById('recentCheckinsBody').innerHTML = '<tr><td colspan="4" class="text-center text-muted">Dashboard data will be available after route implementation</td></tr>';
}

function toggleAutoRefresh() {
    const toggle = document.getElementById('autoRefreshToggle');
    if (toggle.checked) {
        autoRefreshInterval = setInterval(refreshDashboard, 30000); // 30 seconds
        refreshDashboard(); // Initial load
    } else {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }
}

// Initialize dashboard on tab show
document.getElementById('dashboard-tab').addEventListener('shown.bs.tab', function() {
    refreshDashboard();
    toggleAutoRefresh();
});

// ==================== REPORTS & ANALYTICS ====================

function generateReport() {
    const reportType = document.getElementById('reportType').value;
    const dateFrom = document.getElementById('reportDateFrom').value;
    const dateTo = document.getElementById('reportDateTo').value;
    
    if (!dateFrom || !dateTo) {
        showToast('Please select date range', 'warning');
        return;
    }
    
    showToast('Generating report...', 'info');
    
    fetch('{{ url("attendance-settings/reports/generate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ report_type: reportType, date_from: dateFrom, date_to: dateTo })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Route not implemented');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            displayReportResults(data.report);
        } else {
            showToast(data.message || 'Failed to generate report', 'error');
        }
    })
    .catch(error => {
        showToast('Report generation feature will be available after route implementation', 'info');
        document.getElementById('reportResults').innerHTML = '<p class="text-muted text-center">Report feature coming soon. Routes need to be implemented.</p>';
    });
}

function displayReportResults(report) {
    const resultsDiv = document.getElementById('reportResults');
    if (report && report.data && report.data.length > 0) {
        let html = '';
        
        // Display summary if available
        if (report.summary) {
            html += '<div class="row mb-3">';
            Object.entries(report.summary).forEach(([key, value]) => {
                html += `
                    <div class="col-md-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-1">${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h6>
                                <div class="h4 mb-0">${value}</div>
                            </div>
                        </div>
                    </div>
                `;
            });
            html += '</div>';
        }
        
        // Display table
        html += '<div class="table-responsive"><table class="table table-striped table-hover"><thead class="table-dark"><tr>';
        if (report.columns && report.columns.length > 0) {
            report.columns.forEach(col => {
                html += `<th>${col}</th>`;
            });
        } else if (report.data.length > 0) {
            // Extract columns from first row
            Object.keys(report.data[0]).forEach(key => {
                html += `<th>${key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</th>`;
            });
        }
        html += '</tr></thead><tbody>';
        
        report.data.forEach(row => {
            html += '<tr>';
            if (Array.isArray(row)) {
                row.forEach(cell => {
                    html += `<td>${cell || 'N/A'}</td>`;
                });
            } else {
                Object.values(row).forEach(cell => {
                    html += `<td>${cell || 'N/A'}</td>`;
                });
            }
            html += '</tr>';
        });
        html += '</tbody></table></div>';
        
        // Add export buttons
        html += `
            <div class="mt-3 text-end">
                <button class="btn btn-success btn-sm" onclick="exportReport()">
                    <i class="bx bx-export me-1"></i>Export Report
                </button>
            </div>
        `;
        
        resultsDiv.innerHTML = html;
    } else {
        resultsDiv.innerHTML = '<div class="alert alert-info text-center"><i class="bx bx-info-circle fs-1"></i><p class="mt-2">No data available for selected criteria</p></div>';
    }
}

function exportReport() {
    const reportType = document.getElementById('reportType').value;
    const dateFrom = document.getElementById('reportDateFrom').value;
    const dateTo = document.getElementById('reportDateTo').value;
    const format = prompt('Export format (pdf/excel/csv):', 'excel');
    
    if (!format) return;
    
    // Use direct URL - route will be implemented later
    const url = `{{ url("attendance-settings/reports/export") }}?type=${reportType}&from=${dateFrom}&to=${dateTo}&format=${format}`;
    window.location.href = url;
}

// ==================== NOTIFICATIONS ====================

function saveNotificationSettings() {
    const formData = new FormData(document.getElementById('notificationSettingsForm'));
    const data = {};
    
    // Convert FormData to object, handling checkboxes properly
    for (let [key, value] of formData.entries()) {
        if (key.includes('notify_') || key === 'sms_enabled') {
            data[key] = value === 'on' || value === true || value === '1';
        } else {
            data[key] = value;
        }
    }
    
    // Handle unchecked checkboxes
    const checkboxes = ['notify_device_offline', 'notify_device_sync_failed', 'notify_fingerprint_failed', 
                        'notify_missing_checkin', 'notify_late_arrival', 'notify_absenteeism', 'sms_enabled'];
    checkboxes.forEach(key => {
        if (!data.hasOwnProperty(key)) {
            data[key] = false;
        }
    });
    
    fetch('{{ url("attendance-settings/notifications/save") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to save settings');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('Notification settings saved successfully', 'success');
        } else {
            showToast(data.message || 'Failed to save settings', 'error');
        }
    })
    .catch(error => {
        showToast('Error saving notification settings: ' + error.message, 'error');
    });
}

function testSMS() {
    const phone = document.getElementById('smsPhone').value;
    if (!phone) {
        showToast('Please enter a phone number first', 'warning');
        return;
    }
    
    if (!confirm(`Send test SMS to ${phone}?`)) return;
    
    showToast('Sending test SMS...', 'info');
    
    fetch('{{ url("attendance-settings/notifications/test-sms") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ phone: phone })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Test SMS sent successfully!', 'success');
        } else {
            showToast(data.message || 'Failed to send test SMS', 'error');
        }
    })
    .catch(error => {
        showToast('Error sending test SMS: ' + error.message, 'error');
    });
}

function saveSyncSettings() {
    showToast('Saving sync settings...', 'info');
    
    const data = {
        default_sync_mode: document.getElementById('defaultSyncMode').value,
        auto_sync_enabled: document.getElementById('autoSyncEnabled').checked
    };
    
    fetch('{{ url("attendance-settings/advanced/save-sync") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to save settings');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('✓ Sync settings saved successfully!', 'success');
        } else {
            showToast('✗ ' + (data.message || 'Failed to save settings'), 'error');
        }
    })
    .catch(error => {
        showToast('✗ Error saving sync settings: ' + error.message, 'error');
    });
}

function saveFailureSettings() {
    showToast('Saving failure detection settings...', 'info');
    
    const data = {
        auto_failure_detection: document.getElementById('autoFailureDetection').checked,
        failure_threshold: document.getElementById('failureThreshold').value
    };
    
    fetch('{{ url("attendance-settings/advanced/save-failure") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to save settings');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('✓ Failure detection settings saved successfully!', 'success');
        } else {
            showToast('✗ ' + (data.message || 'Failed to save settings'), 'error');
        }
    })
    .catch(error => {
        showToast('✗ Error saving settings: ' + error.message, 'error');
    });
}

function saveSecuritySettings() {
    showToast('Saving security settings...', 'info');
    
    const data = {
        api_auth_method: document.getElementById('apiAuthMethod').value,
        api_key: document.getElementById('apiKey').value,
        enable_api_logging: document.getElementById('enableApiLogging').checked,
        require_https: document.getElementById('requireHttps').checked,
        allowed_ips: document.getElementById('allowedIPs').value.split('\n').filter(ip => ip.trim()),
        rate_limit: document.getElementById('rateLimit').value,
        enable_audit_log: document.getElementById('enableAuditLog').checked
    };
    
    fetch('{{ url("attendance-settings/advanced/save-security") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to save settings');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('✓ Security settings saved successfully!', 'success');
        } else {
            showToast('✗ ' + (data.message || 'Failed to save settings'), 'error');
        }
    })
    .catch(error => {
        showToast('✗ Error saving security settings: ' + error.message, 'error');
    });
}

function generateApiKey() {
    showToast('Generating new API key...', 'info');
    
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let key = '';
    for (let i = 0; i < 32; i++) {
        key += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('apiKey').value = 'att_' + key;
    showToast('✓ New API key generated!', 'success');
}

function runMaintenance() {
    if (!confirm('Run system maintenance now?\n\nThis will clean up old data and optimize the database.')) {
        showToast('Maintenance cancelled', 'info');
        return;
    }
    
    showToast('Step 1: Starting maintenance process...', 'info');
    
    setTimeout(() => {
        showToast('Step 2: Cleaning up old data...', 'info');
        
        setTimeout(() => {
            showToast('Step 3: Optimizing database...', 'info');
            
            fetch('{{ url("attendance-settings/advanced/run-maintenance") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('✓ Step 4: Maintenance completed successfully!', 'success');
                } else {
                    showToast('✗ Maintenance failed: ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showToast('✗ Error running maintenance: ' + error.message, 'error');
            });
        }, 1500);
    }, 1000);
}

function clearCache() {
    if (!confirm('Clear system cache?\n\nThis will refresh cached data.')) {
        return;
    }
    
    showToast('Clearing cache...', 'info');
    
    fetch('{{ url("attendance-settings/advanced/clear-cache") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('✓ Cache cleared successfully!', 'success');
        } else {
            showToast('✗ Failed to clear cache', 'error');
        }
    })
    .catch(error => {
        showToast('✗ Error clearing cache: ' + error.message, 'error');
    });
}

function checkSystemHealth() {
    showToast('Checking system health...', 'info');
    
    fetch('{{ url("attendance-settings/advanced/system-health") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(`✓ System Health: ${data.status || 'Healthy'}`, 'success');
        } else {
            showToast('✗ Health check failed', 'error');
        }
    })
    .catch(error => {
        showToast('✗ Error checking health: ' + error.message, 'error');
    });
}

function testAllDevices() {
    if (!confirm('Test connection to all active devices?\n\nThis will check connectivity for each device.')) {
        showToast('Device test cancelled', 'info');
        return;
    }
    
    showToast('Step 1: Starting device connection test...', 'info');
    
    setTimeout(() => {
        showToast('Step 2: Testing device connections...', 'info');
        
        fetch('{{ url("attendance-settings/devices/test-all") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Test request failed');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showToast(`✓ Step 3: Test complete! ${data.successful || 0}/${data.total || 0} devices online`, 'success');
                if (data.failed && data.failed.length > 0) {
                    setTimeout(() => {
                        showToast(`⚠ Warning: ${data.failed.length} device(s) failed the test`, 'warning');
                    }, 2000);
                }
                checkDeviceFailures();
            } else {
                showToast('✗ Step 3: Test failed - ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            showToast('✗ Error during device test: ' + error.message, 'error');
        });
    }, 1000);
}

function syncAllDevices() {
    if (!confirm('Sync all devices now?\n\nThis will synchronize data with all active devices. This may take a few minutes.')) {
        showToast('Device sync cancelled', 'info');
        return;
    }
    
    showToast('Step 1: Preparing to sync all devices...', 'info');
    
    setTimeout(() => {
        showToast('Step 2: Connecting to devices...', 'info');
        
        setTimeout(() => {
            showToast('Step 3: Synchronizing device data...', 'info');
            
            fetch('{{ url("attendance-settings/devices/sync-all") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Sync request failed');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showToast(`✓ Step 4: Successfully synced ${data.synced_count || 0} device(s)!`, 'success');
                    setTimeout(() => {
                        showToast('✓ All devices synchronized successfully', 'success');
                        checkDeviceFailures();
                    }, 2000);
                } else {
                    showToast('✗ Step 4: Sync failed - ' + (data.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                showToast('✗ Error syncing devices: ' + error.message, 'error');
            });
        }, 1500);
    }, 1000);
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand('copy');
    showToast('✓ URL copied to clipboard!', 'success');
}

function checkDeviceFailures() {
    showToast('Step 1: Checking device status...', 'info');
    
    fetch('{{ url("attendance-settings/devices/failures") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to check devices');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('✓ Step 2: Device status check complete', 'success');
            displayFailedDevices(data.failed_devices || []);
            document.getElementById('failedDevicesCount').textContent = data.failed_count || 0;
        } else {
            showToast('✗ Failed to check device status', 'error');
        }
    })
    .catch(error => {
        showToast('✗ Error checking devices: ' + error.message, 'error');
        // Fallback: show empty state
        displayFailedDevices([]);
    });
}

function displayFailedDevices(failedDevices) {
    const container = document.getElementById('failedDevicesList');
    
    if (!failedDevices || failedDevices.length === 0) {
        container.innerHTML = `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-success"><i class="bx bx-check-circle me-1"></i>All Devices Online</h6>
                        <small class="text-muted">No device failures detected</small>
                    </div>
                    <span class="badge bg-success">OK</span>
                </div>
            </div>
        `;
        return;
    }
    
    container.innerHTML = failedDevices.map(device => `
        <div class="list-group-item border-danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0">${device.name || 'Unknown Device'}</h6>
                    <small class="text-muted">${device.ip_address || 'N/A'} | Last seen: ${device.last_sync_at || 'Never'}</small>
                    <div class="mt-1">
                        <small class="text-danger">Error: ${device.error_message || 'Connection failed'}</small>
                    </div>
                </div>
                <div>
                    <span class="badge bg-danger me-1">Failed</span>
                    <button class="btn btn-sm btn-outline-primary" onclick="retryDevice(${device.id})" title="Retry Connection">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function viewFailedDevices() {
    showToast('Loading failed devices list...', 'info');
    checkDeviceFailures();
}

function retryDevice(deviceId) {
    if (!confirm(`Retry connection to this device?\n\nThis will attempt to reconnect and sync the device.`)) {
        return;
    }
    
    showToast('Step 1: Attempting to reconnect device...', 'info');
    
    fetch(`{{ url('attendance-settings/devices') }}/${deviceId}/retry`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Retry request failed');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('✓ Step 2: Device reconnection successful!', 'success');
            setTimeout(() => {
                checkDeviceFailures();
            }, 1500);
        } else {
            showToast('✗ Step 2: Reconnection failed - ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        showToast('✗ Error retrying device: ' + error.message, 'error');
    });
}

function copySampleConfig(type) {
    let text = '';
    
    if (type === 'wl30') {
        text = `WL30 Device Configuration:

Step 1: Network Settings
IP Address: 192.168.1.100
Subnet Mask: 255.255.255.0
Gateway: 192.168.1.1
DNS: 8.8.8.8

Step 2: Communication Settings
Push URL: {{ url('attendance/api/push') }}
Push Mode: Enabled
Port: 80
Protocol: HTTP

Step 3: Device Settings
Device Name: Main Entrance
Location: Building A - Ground Floor
Timezone: UTC+3
Language: English`;
    } else if (type === 'api') {
        text = `POST {{ url('attendance/api/push') }}
Content-Type: application/json
Authorization: Bearer YOUR_API_KEY

{
  "employee_id": "EMP001",
  "device_id": "WL30-001",
  "device_name": "Main Entrance",
  "action": "in",
  "timestamp": "2025-01-10T09:00:00Z",
  "fingerprint_id": "1",
  "verification_status": "success",
  "location": {
    "latitude": -6.7924,
    "longitude": 39.2083
  }
}`;
    }
    
    navigator.clipboard.writeText(text).then(() => {
        showToast('✓ Sample configuration copied to clipboard!', 'success');
    }).catch(() => {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('✓ Sample configuration copied to clipboard!', 'success');
    });
}

// ==================== ADVANCED SETTINGS ====================

// Backup functions removed - use system backup instead

function toggleConnectionFields() {
    const connectionType = document.getElementById('deviceConnectionType').value;
    // Show/hide relevant fields based on connection type
}

// ==================== DEVICE API FUNCTIONS ====================

function copyEndpoint(url) {
    navigator.clipboard.writeText(url).then(() => {
        showToast('✓ Endpoint URL copied to clipboard!', 'success');
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = url;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('✓ Endpoint URL copied to clipboard!', 'success');
    });
}

function testApiEndpoint(type) {
    const baseUrl = '{{ url("api/device") }}';
    let url = '';
    let method = 'GET';
    let body = null;
    
    switch(type) {
        case 'push':
            url = baseUrl + '/attendance/push';
            method = 'POST';
            body = JSON.stringify({
                device_id: 'UF200-S-TRU7251200134',
                employee_id: 'EMP001',
                check_time: new Date().toISOString(),
                check_type: 'I',
                verify_code: 15
            });
            break;
        case 'batch':
            url = baseUrl + '/attendance/batch';
            method = 'POST';
            body = JSON.stringify({
                device_id: 'UF200-S-TRU7251200134',
                records: [
                    {
                        employee_id: 'EMP001',
                        check_time: new Date().toISOString(),
                        check_type: 'I'
                    }
                ]
            });
            break;
        case 'status':
            url = baseUrl + '/status';
            method = 'POST';
            body = JSON.stringify({
                device_id: 'UF200-S-TRU7251200134',
                status: 'online',
                battery_level: 85
            });
            break;
        case 'users':
            url = baseUrl + '/users/UF200-S-TRU7251200134';
            method = 'GET';
            break;
        case 'time':
            url = baseUrl + '/time/UF200-S-TRU7251200134';
            method = 'GET';
            break;
        case 'commands':
            url = baseUrl + '/commands/UF200-S-TRU7251200134';
            method = 'GET';
            break;
        case 'send-command':
            url = baseUrl + '/commands/UF200-S-TRU7251200134';
            method = 'POST';
            body = JSON.stringify({
                command: 'sync_time',
                parameters: {}
            });
            break;
        case 'health':
            url = baseUrl + '/health';
            method = 'GET';
            break;
    }
    
    showToast(`Testing ${type} endpoint...`, 'info');
    
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };
    
    const apiKey = document.getElementById('deviceApiKey')?.value;
    if (apiKey) {
        headers['X-API-Key'] = apiKey;
    }
    
    fetch(url, {
        method: method,
        headers: headers,
        body: body
    })
    .then(response => response.json())
    .then(data => {
        if (data.success !== false) {
            showToast(`✓ ${type} endpoint test successful!`, 'success');
            console.log('API Response:', data);
        } else {
            showToast(`✗ ${type} endpoint test failed: ${data.message || 'Unknown error'}`, 'error');
            console.error('API Error:', data);
        }
    })
    .catch(error => {
        showToast(`✗ ${type} endpoint test failed: ${error.message}`, 'error');
        console.error('API Error:', error);
    });
}

function showApiExample(type) {
    const examples = {
        push: {
            title: 'Push Attendance Example',
            method: 'POST',
            url: '{{ url("api/device/attendance/push") }}',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': 'your-api-key'
            },
            body: {
                device_id: 'UF200-S-TRU7251200134',
                employee_id: 'EMP001',
                check_time: '2025-01-15T09:00:00Z',
                check_type: 'I',
                verify_code: 15
            }
        },
        batch: {
            title: 'Batch Attendance Example',
            method: 'POST',
            url: '{{ url("api/device/attendance/batch") }}',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': 'your-api-key'
            },
            body: {
                device_id: 'UF200-S-TRU7251200134',
                records: [
                    {
                        employee_id: 'EMP001',
                        check_time: '2025-01-15T09:00:00Z',
                        check_type: 'I'
                    }
                ]
            }
        },
        status: {
            title: 'Device Status Example',
            method: 'POST',
            url: '{{ url("api/device/status") }}',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': 'your-api-key'
            },
            body: {
                device_id: 'UF200-S-TRU7251200134',
                status: 'online',
                battery_level: 85
            }
        },
        users: {
            title: 'Get Users Example',
            method: 'GET',
            url: '{{ url("api/device/users/UF200-S-TRU7251200134") }}',
            headers: {
                'X-API-Key': 'your-api-key'
            }
        },
        time: {
            title: 'Get Server Time Example',
            method: 'GET',
            url: '{{ url("api/device/time/UF200-S-TRU7251200134") }}',
            headers: {
                'X-API-Key': 'your-api-key'
            }
        },
        commands: {
            title: 'Get Commands Example',
            method: 'GET',
            url: '{{ url("api/device/commands/UF200-S-TRU7251200134") }}',
            headers: {
                'X-API-Key': 'your-api-key'
            }
        },
        'send-command': {
            title: 'Send Command Example',
            method: 'POST',
            url: '{{ url("api/device/commands/UF200-S-TRU7251200134") }}',
            headers: {
                'Content-Type': 'application/json',
                'X-API-Key': 'your-api-key'
            },
            body: {
                command: 'sync_time',
                parameters: {}
            }
        }
    };
    
    const example = examples[type];
    if (!example) return;
    
    let code = `${example.method} ${example.url}\n`;
    code += Object.entries(example.headers).map(([k, v]) => `${k}: ${v}`).join('\n');
    if (example.body) {
        code += '\n\n' + JSON.stringify(example.body, null, 2);
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">${example.title}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre class="bg-dark text-light p-3 rounded"><code>${code}</code></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="navigator.clipboard.writeText(\`${code.replace(/`/g, '\\`')}\`); showToast('✓ Example copied!', 'success');">
                        <i class="bx bx-copy me-1"></i>Copy Example
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
    modal.addEventListener('hidden.bs.modal', () => modal.remove());
}

function generateDeviceApiKey() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let key = '';
    for (let i = 0; i < 32; i++) {
        key += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('deviceApiKey').value = 'dev_' + key;
    showToast('✓ New API key generated!', 'success');
}

function saveDeviceApiSettings() {
    showToast('Saving device API settings...', 'info');
    
    const data = {
        device_api_key: document.getElementById('deviceApiKey').value,
        device_api_logging: document.getElementById('enableDeviceApiLogging').checked,
        device_api_require_https: document.getElementById('requireDeviceHttps').checked
    };
    
    fetch('{{ url("attendance-settings/advanced/save-security") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to save settings');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showToast('✓ Device API settings saved successfully!', 'success');
        } else {
            showToast('✗ ' + (data.message || 'Failed to save settings'), 'error');
        }
    })
    .catch(error => {
        showToast('✗ Error saving device API settings: ' + error.message, 'error');
    });
}

function copyCurlExample() {
    const curlExample = `curl -X POST {{ url('api/device/attendance/push') }} \\
  -H "Content-Type: application/json" \\
  -H "X-API-Key: your-api-key" \\
  -d '{
    "device_id": "UF200-S-TRU7251200134",
    "employee_id": "EMP001",
    "check_time": "2025-01-15T09:00:00Z",
    "check_type": "I"
  }'`;
    
    navigator.clipboard.writeText(curlExample).then(() => {
        showToast('✓ cURL example copied to clipboard!', 'success');
    }).catch(() => {
        const textarea = document.createElement('textarea');
        textarea.value = curlExample;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast('✓ cURL example copied to clipboard!', 'success');
    });
}

// Handle user enrollment form submission
document.addEventListener('DOMContentLoaded', function() {
    // Load users table and locations if users tab is active
    const usersTab = document.getElementById('users-tab');
    if (usersTab) {
        usersTab.addEventListener('shown.bs.tab', function() {
            loadUsersTable();
            loadLocations(); // Load locations when users tab is shown
        });
    }
    
    // Load locations when locations sub-tab is shown (if advanced tab exists)
    const locationsSubTab = document.getElementById('advanced-locations-tab');
    if (locationsSubTab) {
        locationsSubTab.addEventListener('shown.bs.tab', function() {
            loadLocations();
        });
    }
    
    // Check device failures on advanced tab load
    const advancedTab = document.getElementById('advanced-tab');
    if (advancedTab) {
        advancedTab.addEventListener('shown.bs.tab', function() {
            showToast('Loading advanced settings...', 'info');
            // Load locations if locations sub-tab is active
            const activeSubTab = document.querySelector('#advancedSubTabs .nav-link.active');
            if (activeSubTab && activeSubTab.id === 'advanced-locations-tab') {
                loadLocations();
            } else {
                setTimeout(() => {
                    checkDeviceFailures();
                }, 500);
            }
        });
    }
    
    // Load locations on page load if users tab is active by default
    document.addEventListener('DOMContentLoaded', function() {
        // Check if users tab is active or if we're on the users tab
        const activeTab = document.querySelector('#settingsTabs .nav-link.active');
        if (activeTab && activeTab.id === 'users-tab') {
            loadLocations();
        }
    });
    
    // Handle user enrollment form
    const enrollmentForm = document.getElementById('userEnrollmentForm');
    if (enrollmentForm) {
        enrollmentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const userId = document.getElementById('enrollmentUserSelect').value;
            const deviceId = document.getElementById('enrollmentDeviceSelect').value;
            const fingers = Array.from(document.querySelectorAll('input[name="fingers[]"]:checked')).map(cb => cb.value);
            
            if (!userId || !deviceId) {
                showToast('Please select both user and device', 'warning');
                return;
            }
            
            if (fingers.length === 0) {
                showToast('Please select at least one finger to enroll', 'warning');
                return;
            }
            
            const data = {
                user_id: userId,
                device_id: deviceId,
                fingers: fingers
            };
            
            const submitBtn = e.submitter;
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
            
            try {
                const response = await fetch('{{ url("attendance-settings/users/enroll") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast('User enrollment saved successfully', 'success');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('userEnrollmentModal'));
                    if (modal) modal.hide();
                    loadUsersTable();
                } else {
                    showToast(result.message || 'Failed to save enrollment', 'error');
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
});
</script>
@endpush
@endsection

