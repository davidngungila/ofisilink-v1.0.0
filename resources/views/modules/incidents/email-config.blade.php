@extends('layouts.app')

@section('title', 'Email Configuration - Incident Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .stat-card {
        border-radius: 12px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }
    .feature-card {
        border-radius: 12px;
        transition: all 0.3s;
        border: none;
        height: 100%;
    }
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    /* Ensure modals and popups appear in front */
    .modal {
        z-index: 100000 !important;
    }
    .modal-backdrop {
        z-index: 99999 !important;
    }
    .modal-dialog {
        z-index: 100001 !important;
    }
    .swal2-container {
        z-index: 1000000 !important;
    }
    .swal2-popup {
        z-index: 1000001 !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-danger" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-envelope me-2"></i>Email Configuration
                            </h3>
                            <p class="mb-0 text-white-50">Configure email accounts for automatic incident synchronization</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <a href="{{ route('modules.incidents.email.accounts') }}" class="btn btn-light">
                                <i class="bx bx-cog me-1"></i>Manage Accounts
                            </a>
                            <a href="{{ route('modules.incidents.dashboard') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-Sync Status Indicator -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info d-flex justify-content-between align-items-center shadow-sm" style="border-radius: 12px;">
                <div>
                    <i class="bx bx-sync bx-spin me-2"></i>
                    <strong>Automatic Email Sync:</strong> Active - Syncing every <strong>1 minute</strong> for all active email configurations with live mode enabled
                </div>
                <div>
                    <small class="text-muted">Last refresh: <span id="lastRefreshTime">{{ now()->format('H:i:s') }}</span></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Acceptance Info -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-success shadow-sm" style="border-radius: 12px;">
                <div class="d-flex align-items-start">
                    <i class="bx bx-info-circle me-2 mt-1" style="font-size: 1.2rem;"></i>
                    <div>
                        <strong><i class="bx bx-envelope me-1"></i> How Email Acceptance Works:</strong>
                        <ul class="mb-0 mt-2">
                            <li>The system accepts emails sent <strong>TO</strong> the configured email address(es) listed below.</li>
                            <li>When an email is received at any configured email account, it is automatically processed and converted into an incident.</li>
                            <li>All emails sent to the configured address will be accepted and processed, regardless of the sender.</li>
                            <li>Make sure the email account is properly configured with correct IMAP/POP3 settings and credentials.</li>
                            <li>Test the connection after configuration to ensure emails can be received.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100" onclick="window.location.href='{{ route('modules.incidents.email.accounts') }}'">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="bx bx-envelope fs-1 text-danger"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->count() }}</h3>
                    <small class="text-muted fw-semibold">Total Configurations</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100" onclick="window.location.href='{{ route('modules.incidents.email.connection.test') }}'">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bx bx-check-circle fs-1 text-success"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->where('connection_status', 'connected')->count() }}</h3>
                    <small class="text-muted fw-semibold">Connected</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100" onclick="window.location.href='{{ route('modules.incidents.email.connection.test') }}'">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-danger bg-opacity-10">
                        <i class="bx bx-error-circle fs-1 text-danger"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->where('connection_status', 'failed')->count() }}</h3>
                    <small class="text-muted fw-semibold">Failed</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm stat-card h-100" onclick="window.location.href='{{ route('modules.incidents.email.accounts') }}'">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bx bx-power-off fs-1 text-info"></i>
                    </div>
                    <h3 class="mb-1 fw-bold">{{ $configs->where('is_active', true)->count() }}</h3>
                    <small class="text-muted fw-semibold">Active</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Feature Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm feature-card" onclick="window.location.href='{{ route('modules.incidents.email.accounts') }}'">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-warning bg-opacity-10">
                        <i class="bx bx-envelope fs-1 text-warning"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Configured Email Accounts</h5>
                    <p class="text-muted small mb-3">Manage and configure email accounts for incident synchronization</p>
                    <a href="{{ route('modules.incidents.email.accounts') }}" class="btn btn-warning btn-sm">
                        <i class="bx bx-arrow-right me-1"></i>Manage Accounts
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm feature-card" onclick="window.location.href='{{ route('modules.incidents.email.connection.test') }}'">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-primary bg-opacity-10">
                        <i class="bx bx-network-chart fs-1 text-primary"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Connection Testing</h5>
                    <p class="text-muted small mb-3">Test and verify email account connections</p>
                    <a href="{{ route('modules.incidents.email.connection.test') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-arrow-right me-1"></i>Test Connections
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm feature-card" onclick="window.location.href='{{ route('modules.incidents.email.retrieve') }}'">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-info bg-opacity-10">
                        <i class="bx bx-envelope-open fs-1 text-info"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Email Retrieval</h5>
                    <p class="text-muted small mb-3">Retrieve and view emails from configured accounts</p>
                    <a href="{{ route('modules.incidents.email.retrieve') }}" class="btn btn-info btn-sm">
                        <i class="bx bx-arrow-right me-1"></i>Retrieve Emails
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm feature-card" onclick="window.location.href='{{ route('modules.incidents.email.transfer') }}'">
                <div class="card-body text-center p-4">
                    <div class="stat-icon bg-success bg-opacity-10">
                        <i class="bx bx-transfer fs-1 text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Transfer to Incidents</h5>
                    <p class="text-muted small mb-3">Select and transfer emails to create incidents</p>
                    <a href="{{ route('modules.incidents.email.transfer') }}" class="btn btn-success btn-sm">
                        <i class="bx bx-arrow-right me-1"></i>Transfer Emails
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Ensure all modals and popups appear in front
(function() {
    document.addEventListener('show.bs.modal', function(e) {
        e.target.style.zIndex = '100000';
    });
    document.addEventListener('shown.bs.modal', function(e) {
        e.target.style.zIndex = '100000';
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) backdrop.style.zIndex = '99999';
    });
    if (typeof Swal !== 'undefined') {
        const originalFire = Swal.fire;
        Swal.fire = function(...args) {
            const result = originalFire.apply(this, args);
            setTimeout(() => {
                const swalContainer = document.querySelector('.swal2-container');
                if (swalContainer) swalContainer.style.zIndex = '1000000';
            }, 10);
            return result;
        };
    }
})();
</script>
@endpush
