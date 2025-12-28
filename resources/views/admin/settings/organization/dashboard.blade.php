@extends('layouts.app')

@section('title', 'Organization Settings Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .settings-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
    }
    .settings-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        border-color: #007bff;
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
    .quick-action-card {
        border: 2px solid transparent;
        transition: all 0.3s;
        cursor: pointer;
    }
    .quick-action-card:hover {
        border-color: #007bff;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0,123,255,0.2);
    }
    .avatar {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }
    .hover-lift {
        transition: all 0.3s ease;
    }
    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-cog me-2"></i>Organization Settings Dashboard
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive organization management system with advanced configuration options
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('admin.settings.organization.info') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-building me-2"></i>Organization Info
                            </a>
                            <a href="{{ route('admin.settings') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-list-ul me-2"></i>All Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Financial Year</h6>
                        <h3 class="mb-0 text-primary">{{ $orgSettings->current_financial_year ?? 'N/A' }}</h3>
                        <small class="text-success">
                            <i class="bx bx-calendar-check"></i> {{ $orgSettings->financial_year_locked ? 'Locked' : 'Active' }}
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary rounded">
                            <i class="bx bx-calendar"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Currency</h6>
                        <h3 class="mb-0 text-success">{{ $orgSettings->currency ?? 'TZS' }}</h3>
                        <small class="text-info">
                            <i class="bx bx-dollar"></i> {{ $orgSettings->currency_symbol ?? 'TSh' }}
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-success rounded">
                            <i class="bx bx-dollar"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Timezone</h6>
                        <h3 class="mb-0 text-warning">{{ $orgSettings->timezone ?? 'UTC' }}</h3>
                        <small class="text-muted">
                            <i class="bx bx-time"></i> {{ $orgSettings->locale ?? 'en' }}
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-warning rounded">
                            <i class="bx bx-time-five"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">System Status</h6>
                        <h3 class="mb-0 text-danger">Active</h3>
                        <small class="text-warning">
                            <i class="bx bx-check-circle"></i> All systems operational
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-danger rounded">
                            <i class="bx bx-server"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white border-bottom">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bx bx-bolt-circle me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('admin.settings.organization.info') }}" class="card border-primary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-primary">
                                        <i class="bx bx-building fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Organization Info</h6>
                                    <small class="text-muted">Company details & branding</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('admin.settings.organization.financial-year') }}" class="card border-info h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                        <i class="bx bx-calendar fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Financial Year</h6>
                                    <small class="text-muted">Configure fiscal periods</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('admin.settings.organization.currency') }}" class="card border-success h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                        <i class="bx bx-dollar fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Currency & Regional</h6>
                                    <small class="text-muted">Money & locale settings</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('admin.settings.communication') }}" class="card border-warning h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                        <i class="bx bx-envelope fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Communication</h6>
                                    <small class="text-muted">Email & SMS config</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('admin.settings.system') }}" class="card border-secondary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-secondary">
                                        <i class="bx bx-cog fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">System Settings</h6>
                                    <small class="text-muted">System configuration</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('admin.settings') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-list-ul fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">All Settings</h6>
                                    <small class="text-muted">View all settings</small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity / Quick Info -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-info-circle me-2"></i>Organization Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label fw-bold text-muted small mb-1">Company Name</label>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-building text-primary me-2"></i>
                                    <strong>{{ $organizationSettings['company_name']->value ?? 'Not Set' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label fw-bold text-muted small mb-1">Email</label>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-envelope text-danger me-2"></i>
                                    <strong>{{ $organizationSettings['email']->value ?? 'Not Set' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label fw-bold text-muted small mb-1">Phone</label>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-phone text-success me-2"></i>
                                    <strong>{{ $organizationSettings['phone']->value ?? 'Not Set' }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded">
                                <label class="form-label fw-bold text-muted small mb-1">Website</label>
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-globe text-info me-2"></i>
                                    <strong>{{ $organizationSettings['website']->value ?? 'Not Set' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-time me-2"></i>Financial Year Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h2 class="text-primary fw-bold">{{ $orgSettings->current_financial_year ?? 'N/A' }}</h2>
                        <p class="text-muted mb-0">Current Financial Year</p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span><i class="bx bx-calendar text-primary"></i> Start Date</span>
                        <strong>{{ $orgSettings->financial_year_start_date ? \Carbon\Carbon::parse($orgSettings->financial_year_start_date)->format('M d, Y') : 'Not Set' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span><i class="bx bx-calendar-check text-success"></i> End Date</span>
                        <strong>{{ $orgSettings->financial_year_end_date ? \Carbon\Carbon::parse($orgSettings->financial_year_end_date)->format('M d, Y') : 'Not Set' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="bx bx-{{ $orgSettings->financial_year_locked ? 'lock' : 'lock-open' }} text-{{ $orgSettings->financial_year_locked ? 'danger' : 'success' }}"></i> Status</span>
                        <span class="badge bg-{{ $orgSettings->financial_year_locked ? 'danger' : 'success' }}">
                            {{ $orgSettings->financial_year_locked ? 'Locked' : 'Active' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush










