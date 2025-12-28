@extends('layouts.app')

@section('title', 'Attendance Settings & Configuration')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-1">
                        <i class="bx bx-cog text-primary"></i> Attendance Settings & Configuration
                    </h4>
                    <p class="text-muted mb-0">Manage attendance policies, devices, locations, schedules, and system settings</p>
                </div>
                <div>
                    <a href="{{ route('modules.hr.attendance') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Attendance
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Locations</h6>
                            <h3 class="mb-0" id="statTotalLocations">{{ $stats['total_locations'] ?? 0 }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bx bx-map fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Active Devices</h6>
                            <h3 class="mb-0" id="statActiveDevices">{{ $stats['active_devices'] ?? 0 }}</h3>
                            <small class="text-success" id="statOnlineDevices">{{ $stats['online_devices'] ?? 0 }} Online</small>
                        </div>
                        <div class="text-success">
                            <i class="bx bx-devices fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Work Schedules</h6>
                            <h3 class="mb-0" id="statTotalSchedules">{{ $stats['total_schedules'] ?? 0 }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="bx bx-time-five fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Policies</h6>
                            <h3 class="mb-0" id="statTotalPolicies">{{ $stats['total_policies'] ?? 0 }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bx bx-shield fs-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Settings Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist" id="settingsTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#devices" role="tab">
                                <i class="bx bx-devices me-1"></i> Devices
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#enrollment" role="tab">
                                <i class="bx bx-user-plus me-1"></i> User Enrollment to Device
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#schedules" role="tab">
                                <i class="bx bx-time-five me-1"></i> Work Schedules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#policies" role="tab">
                                <i class="bx bx-shield me-1"></i> Policies
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="settingsTabContent">
                        <!-- Devices Tab -->
                        <div class="tab-pane fade show active" id="devices" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.devices')
                        </div>
                        <!-- User Enrollment Tab -->
                        <div class="tab-pane fade" id="enrollment" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.enrollment')
                        </div>
                        <!-- Work Schedules Tab -->
                        <div class="tab-pane fade" id="schedules" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.schedules')
                        </div>
                        <!-- Policies Tab -->
                        <div class="tab-pane fade" id="policies" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.policies')
                        </div>

                        <!-- Locations Tab -->
                        <div class="tab-pane fade" id="locations" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.locations')
                        </div>

                        <!-- Devices Tab -->
                        <div class="tab-pane fade" id="devices" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.devices')
                        </div>

                        <!-- Work Schedules Tab -->
                        <div class="tab-pane fade" id="schedules" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.schedules')
                        </div>

                        <!-- Policies Tab -->
                        <div class="tab-pane fade" id="policies" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.policies')
                        </div>

                        <!-- Notifications Tab -->
                        <div class="tab-pane fade" id="notifications" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.notifications')
                        </div>

                        <!-- Sync Settings Tab -->
                        <div class="tab-pane fade" id="sync" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.sync')
                        </div>

                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.security')
                        </div>

                        <!-- Maintenance Tab -->
                        <div class="tab-pane fade" id="maintenance" role="tabpanel">
                            @include('modules.hr.attendance-settings.partials.maintenance')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('modules.hr.attendance-settings.modals.location-modal')
@include('modules.hr.attendance-settings.modals.device-modal')
@include('modules.hr.attendance-settings.modals.schedule-modal')
@include('modules.hr.attendance-settings.modals.policy-modal')

@endsection

@push('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #0d6efd;
    }
    .border-left-success {
        border-left: 4px solid #198754;
    }
    .border-left-info {
        border-left: 4px solid #0dcaf0;
    }
    .border-left-warning {
        border-left: 4px solid #ffc107;
    }
    .nav-tabs .nav-link {
        color: #6c757d;
        border: none;
        border-bottom: 2px solid transparent;
    }
    .nav-tabs .nav-link:hover {
        border-color: transparent;
        border-bottom-color: #dee2e6;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        border-bottom-color: #0d6efd;
        background: transparent;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Global settings data
const settingsData = {
    locations: @json($locations ?? []),
    devices: @json($devices ?? []),
    schedules: @json($schedules ?? []),
    policies: @json($policies ?? []),
    departments: @json($departments ?? []),
    employees: @json($employees ?? [])
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data for each tab
    loadGeneralSettings();
    loadLocations();
    loadDevices();
    loadSchedules();
    loadPolicies();
    
    // Auto-refresh device status every 30 seconds
    setInterval(() => {
        refreshDeviceStatus();
    }, 30000);
});

// Tab switching handlers
document.querySelectorAll('#settingsTabs a[data-bs-toggle="tab"]').forEach(tab => {
    tab.addEventListener('shown.bs.tab', function (e) {
        const targetTab = e.target.getAttribute('href');
        
        // Load data when tab is shown
        switch(targetTab) {
            case '#general':
                loadGeneralSettings();
                break;
            case '#locations':
                loadLocations();
                break;
            case '#devices':
                loadDevices();
                refreshDeviceStatus();
                break;
            case '#schedules':
                loadSchedules();
                break;
            case '#policies':
                loadPolicies();
                break;
            case '#notifications':
                loadNotificationSettings();
                break;
            case '#sync':
                loadSyncSettings();
                break;
            case '#security':
                loadSecuritySettings();
                break;
            case '#maintenance':
                loadMaintenanceInfo();
                break;
        }
    });
});
</script>
@endpush

