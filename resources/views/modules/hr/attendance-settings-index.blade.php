@extends('layouts.app')

@section('title', 'Attendance Settings')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-cog"></i> Attendance Settings & Configuration
                </h4>
                <p class="text-muted">Manage attendance devices, user enrollment, work schedules, and policies</p>
            </div>
            <div>
                <a href="{{ route('modules.hr.attendance') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Attendance
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .settings-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        height: 100%;
    }
    .settings-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border-color: #007bff;
    }
    .settings-icon {
        font-size: 3rem;
        margin-bottom: 15px;
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card border-left-primary">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Devices</h6>
                        <h3 class="mb-0 text-primary">{{ $stats['total_devices'] ?? 0 }}</h3>
                        <small class="text-success">
                            <i class="bx bx-check-circle"></i> {{ $stats['online_devices'] ?? 0 }} Online
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary rounded">
                            <i class="bx bx-devices"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card border-left-success">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Enrolled Employees</h6>
                        <h3 class="mb-0 text-success">{{ $stats['enrolled_employees'] ?? 0 }}</h3>
                        <small class="text-info">
                            <i class="bx bx-user"></i> {{ $stats['total_employees'] ?? 0 }} Total
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-success rounded">
                            <i class="bx bx-user-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card border-left-info">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Work Schedules</h6>
                        <h3 class="mb-0 text-info">{{ $stats['total_schedules'] ?? 0 }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-time"></i> {{ $stats['active_schedules'] ?? 0 }} Active
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-info rounded">
                            <i class="bx bx-time-five"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card border-left-warning">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Policies</h6>
                        <h3 class="mb-0 text-warning">{{ $stats['total_policies'] ?? 0 }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-shield"></i> {{ $stats['active_policies'] ?? 0 }} Active
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-warning rounded">
                            <i class="bx bx-shield"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('modules.hr.attendance.settings.devices') }}" class="text-decoration-none">
                <div class="card settings-card">
                    <div class="card-body text-center">
                        <div class="settings-icon text-primary">
                            <i class="bx bx-devices"></i>
                        </div>
                        <h5 class="card-title">Devices</h5>
                        <p class="text-muted">Manage biometric devices, connection settings, and device status</p>
                        <div class="mt-3">
                            <span class="badge bg-primary">{{ $stats['total_devices'] ?? 0 }} Devices</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('modules.hr.attendance.settings.enrollment') }}" class="text-decoration-none">
                <div class="card settings-card">
                    <div class="card-body text-center">
                        <div class="settings-icon text-success">
                            <i class="bx bx-user-plus"></i>
                        </div>
                        <h5 class="card-title">User Enrollment</h5>
                        <p class="text-muted">Register employees to biometric devices and manage enrollment status</p>
                        <div class="mt-3">
                            <span class="badge bg-success">{{ $stats['enrolled_employees'] ?? 0 }} Enrolled</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('modules.hr.attendance.settings.schedules') }}" class="text-decoration-none">
                <div class="card settings-card">
                    <div class="card-body text-center">
                        <div class="settings-icon text-info">
                            <i class="bx bx-time-five"></i>
                        </div>
                        <h5 class="card-title">Work Schedules</h5>
                        <p class="text-muted">Configure work schedules, shifts, and time policies</p>
                        <div class="mt-3">
                            <span class="badge bg-info">{{ $stats['total_schedules'] ?? 0 }} Schedules</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <a href="{{ route('modules.hr.attendance.settings.policies') }}" class="text-decoration-none">
                <div class="card settings-card">
                    <div class="card-body text-center">
                        <div class="settings-icon text-warning">
                            <i class="bx bx-shield"></i>
                        </div>
                        <h5 class="card-title">Policies</h5>
                        <p class="text-muted">Set attendance policies, rules, and approval workflows</p>
                        <div class="mt-3">
                            <span class="badge bg-warning">{{ $stats['total_policies'] ?? 0 }} Policies</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

@push('scripts')
@endpush
@endsection







