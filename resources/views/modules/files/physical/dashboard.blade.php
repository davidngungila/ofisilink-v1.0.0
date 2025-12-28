@extends('layouts.app')

@section('title', 'Physical Files Dashboard')

@push('styles')
<style>
    /* Ensure SweetAlert and modals appear in front of all elements */
    .swal2-container {
        z-index: 200000 !important;
    }
    .swal2-popup {
        z-index: 200001 !important;
    }
    .modal {
        z-index: 1055 !important;
    }
    .modal-backdrop {
        z-index: 1054 !important;
    }
    body.modal-open {
        overflow: hidden;
    }
    /* Ensure all popups are visible */
    .swal2-container.swal2-backdrop-show {
        background-color: rgba(0, 0, 0, 0.4) !important;
    }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-archive me-2"></i>Physical Files Dashboard
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive physical rack management system with tracking and request workflow
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($canManageFiles)
                                <a href="{{ route('modules.files.physical.upload') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-file-plus me-2"></i>Add Files
                                </a>
                                <a href="{{ route('modules.files.physical.manage') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-cog me-2"></i>Manage
                                </a>
                                <a href="{{ route('modules.files.physical.assign') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-user-plus me-2"></i>Assign Files
                                </a>
                            @endif
                            <a href="{{ route('modules.files.physical.search') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-search me-2"></i>Search
                            </a>
                            @if($isStaff || !$canManageFiles)
                                <a href="{{ route('modules.files.physical.access-requests') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-clipboard me-2"></i>My Requests
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-archive fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Racks</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_folders'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="bx bx-trending-up me-1"></i>All Racks
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="bx bx-file fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Files</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $stats['total_files'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="bx bx-check-circle me-1"></i>All Files
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="bx bx-package fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Issued Files</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $stats['issued_files'] ?? 0 }}</h3>
                            <small class="text-warning">
                                <i class="bx bx-time me-1"></i>Currently Out
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #ef4444 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                            <i class="bx bx-clipboard fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Pending Requests</h6>
                            <h3 class="mb-0 fw-bold text-danger">{{ $stats['pending_requests'] ?? 0 }}</h3>
                            <small class="text-danger">
                                <i class="bx bx-bell me-1"></i>Awaiting Approval
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-grid-alt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($canManageFiles)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.physical.upload') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-file-plus fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Add Files</h6>
                                    <small class="text-muted">Create new files</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.physical.manage') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-cog fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Manage</h6>
                                    <small class="text-muted">Organize racks</small>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.physical.assign') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-user-plus fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Assign</h6>
                                    <small class="text-muted">Assign files</small>
                                </div>
                            </a>
                        </div>
                        @endif
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.physical.search') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-search fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Search</h6>
                                    <small class="text-muted">Find files</small>
                                </div>
                            </a>
                        </div>
                        
                        @if($canManageFiles)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.physical.analytics') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-bar-chart-alt-2 fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Analytics</h6>
                                    <small class="text-muted">View statistics</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.physical.access-requests') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-clipboard fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Access Requests</h6>
                                    <small class="text-muted">Manage requests</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.physical.activity-log') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-history fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Activity Log</h6>
                                    <small class="text-muted">View history</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.physical.settings') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-cog fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Settings</h6>
                                    <small class="text-muted">Configure system</small>
                                </div>
                            </a>
                        </div>
                        @else
                        {{-- Staff/Regular users can view their own requests --}}
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.physical.access-requests') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-clipboard fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">My Requests</h6>
                                    <small class="text-muted">View my requests</small>
                                </div>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Racks Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-archive me-2"></i>All Racks
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="rack-search-input" placeholder="Search racks...">
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" id="view-grid">
                                    <i class="bx bx-grid-alt"></i> Grid
                                </button>
                                <button class="btn btn-sm btn-outline-primary active" id="view-list">
                                    <i class="bx bx-list-ul"></i> List
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($rootFolders->count() > 0)
                        <div id="racks-container" class="racks-list-view">
                            @foreach($rootFolders as $rack)
                                <div class="rack-item mb-3 p-3 border rounded" data-rack-name="{{ strtolower($rack->name) }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2">
                                                <i class="bx bx-archive text-primary me-2"></i>
                                                <a href="{{ route('modules.files.physical.rack.detail', $rack->id) }}" class="text-decoration-none">
                                                    {{ $rack->name }}
                                                </a>
                                            </h6>
                                            <div class="d-flex gap-3 flex-wrap mb-2">
                                                <small class="text-muted">
                                                    <i class="bx bx-file me-1"></i>{{ $rack->files_count ?? 0 }} files
                                                </small>
                                                @if($rack->category)
                                                <small class="text-muted">
                                                    <i class="bx bx-category me-1"></i>{{ $rack->category->name }}
                                                </small>
                                                @endif
                                                @if($rack->department)
                                                <small class="text-muted">
                                                    <i class="bx bx-building me-1"></i>{{ $rack->department->name }}
                                                </small>
                                                @endif
                                                <small class="text-muted">
                                                    <i class="bx bx-lock-alt me-1"></i>{{ ucfirst($rack->access_level) }}
                                                </small>
                                            </div>
                                            @if($rack->description)
                                            <p class="text-muted mb-0 small">{{ Str::limit($rack->description, 100) }}</p>
                                            @endif
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('modules.files.physical.rack.detail', $rack->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-show"></i> View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-archive-open fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No racks found. Create your first rack to get started.</p>
                            @if($canManageFiles)
                                <a href="{{ route('modules.files.physical.manage') }}" class="btn btn-primary">
                                    <i class="bx bx-folder-plus me-2"></i>Create Rack
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    let searchTimeout;
    
    // Ensure SweetAlert appears on top
    if (typeof Swal !== 'undefined') {
        Swal.mixin({
            customClass: {
                container: 'swal2-container-custom'
            }
        });
    }
    
    // Live search for racks
    $('#rack-search-input').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().toLowerCase();
        
        searchTimeout = setTimeout(() => {
            $('.rack-item').each(function() {
                const rackName = $(this).data('rack-name') || $(this).find('h6').text().toLowerCase();
                if (rackName.includes(searchTerm) || searchTerm === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }, 300);
    });
    
    // View toggle
    $('#view-grid').on('click', function() {
        $('#racks-container').removeClass('racks-list-view').addClass('racks-grid-view');
        $(this).addClass('active');
        $('#view-list').removeClass('active');
    });
    
    $('#view-list').on('click', function() {
        $('#racks-container').removeClass('racks-grid-view').addClass('racks-list-view');
        $(this).addClass('active');
        $('#view-grid').removeClass('active');
    });
});
</script>
@endpush
@endsection

