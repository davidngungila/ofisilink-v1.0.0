@extends('layouts.app')

@section('title', 'Incidents Dashboard')

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
                                <i class="bx bx-error-circle me-2"></i>Incidents Dashboard
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive incident management system with advanced tracking and analytics
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($canManageIncidents)
                                <a href="{{ route('modules.incidents.create') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-plus me-2"></i>Create Incident
                                </a>
                                <a href="{{ route('modules.incidents') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-list-ul me-2"></i>View All
                                </a>
                            @endif
                            <a href="{{ route('modules.incidents.analytics') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-bar-chart me-2"></i>Analytics
                            </a>
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
                            <i class="bx bx-error-circle fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Incidents</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_incidents'] ?? 0 }}</h3>
                            <small class="text-info">
                                <i class="bx bx-calendar me-1"></i>{{ $stats['incidents_this_month'] ?? 0 }} this month
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
                            <i class="bx bx-check-circle fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Resolved</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $stats['resolved_incidents'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="bx bx-check me-1"></i>{{ $stats['resolved_this_month'] ?? 0 }} this month
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
                            <i class="bx bx-time fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">In Progress</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $stats['in_progress_incidents'] ?? 0 }}</h3>
                            <small class="text-warning">
                                <i class="bx bx-loader me-1"></i>Active
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
                            <i class="bx bx-error fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Critical</h6>
                            <h3 class="mb-0 fw-bold text-danger">{{ $stats['critical_incidents'] ?? 0 }}</h3>
                            <small class="text-danger">
                                <i class="bx bx-alarm me-1"></i>Urgent
                            </small>
                        </div>
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
                        @if($canManageIncidents)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.incidents.create') }}" class="card border-primary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-primary">
                                        <i class="bx bx-plus fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Create Incident</h6>
                                    <small class="text-muted">Report new incident</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.incidents') }}" class="card border-info h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                        <i class="bx bx-list-ul fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">View All</h6>
                                    <small class="text-muted">Browse all incidents</small>
                                </div>
                            </a>
                        </div>
                        @endif
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.incidents.analytics') }}" class="card border-success h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                        <i class="bx bx-bar-chart fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Analytics</h6>
                                    <small class="text-muted">View statistics</small>
                                </div>
                            </a>
                        </div>
                        
                        @if($canManageIncidents)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.incidents.export') }}" class="card border-warning h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                        <i class="bx bx-export fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Export</h6>
                                    <small class="text-muted">Export incidents</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.incidents.email.config') }}" class="card border-secondary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-secondary">
                                        <i class="bx bx-envelope fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Email Config</h6>
                                    <small class="text-muted">Configure email sync</small>
                                </div>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Incidents Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-error-circle me-2"></i>All Incidents
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="incident-search-input" placeholder="Search incidents...">
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" id="filter-all">
                                    <i class="bx bx-list-ul"></i> All
                                </button>
                                <button class="btn btn-sm btn-outline-primary" id="filter-new">
                                    <i class="bx bx-plus-circle"></i> New
                                </button>
                                <button class="btn btn-sm btn-outline-primary" id="filter-assigned">
                                    <i class="bx bx-user-check"></i> Assigned
                                </button>
                                <button class="btn btn-sm btn-outline-primary" id="filter-in-progress">
                                    <i class="bx bx-loader"></i> In Progress
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($allIncidents->count() > 0)
                        <div id="incidents-container" class="incidents-list-view">
                            @foreach($allIncidents as $incident)
                                @include('modules.incidents.partials.incident-item', ['incident' => $incident, 'canManageIncidents' => $canManageIncidents])
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-error-circle fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No incidents found. Create your first incident to get started.</p>
                            @if($canManageIncidents)
                                <a href="{{ route('modules.incidents.create') }}" class="btn btn-primary">
                                    <i class="bx bx-plus me-2"></i>Create Incident
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if($myAssignedIncidents->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-user-check me-2"></i>My Assigned Incidents
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Incident #</th>
                                    <th>Title</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myAssignedIncidents as $incident)
                                <tr>
                                    <td>
                                        <strong>{{ $incident->incident_no ?? $incident->incident_code ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <a href="{{ route('modules.incidents.show', $incident->id) }}" class="text-decoration-none">
                                            {{ Str::limit($incident->subject ?? $incident->title ?? 'N/A', 50) }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ strtolower($incident->priority) === 'critical' ? 'danger' : (strtolower($incident->priority) === 'high' ? 'warning' : (strtolower($incident->priority) === 'medium' ? 'info' : 'secondary')) }}">
                                            {{ $incident->priority }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ strtolower($incident->status) === 'resolved' ? 'success' : (strtolower($incident->status) === 'in progress' ? 'warning' : (strtolower($incident->status) === 'assigned' ? 'info' : 'secondary')) }}">
                                            {{ $incident->status }}
                                        </span>
                                    </td>
                                    <td>{{ $incident->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('modules.incidents.show', $incident->id) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-show"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
.hover-lift {
    transition: all 0.3s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
.incidents-list-view .incident-item {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}
.incidents-list-view .incident-item:hover {
    background-color: #f8f9fa;
    border-color: #0d6efd;
    transform: translateX(5px);
}
.incidents-grid-view {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}
.incidents-grid-view .incident-item {
    padding: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
}
.incidents-grid-view .incident-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Live search for incidents
    let searchTimeout;
    $('#incident-search-input').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().toLowerCase();
        
        searchTimeout = setTimeout(() => {
            $('.incident-item').each(function() {
                const incidentText = $(this).text().toLowerCase();
                if (incidentText.includes(searchTerm) || searchTerm === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }, 300);
    });
    
    // Filter buttons
    $('#filter-all').click(function() {
        $('.incident-item').show();
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filter-new').click(function() {
        $('.incident-item').hide();
        $('.incident-item[data-status="New"]').show();
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filter-assigned').click(function() {
        $('.incident-item').hide();
        $('.incident-item[data-status="Assigned"]').show();
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
    });
    
    $('#filter-in-progress').click(function() {
        $('.incident-item').hide();
        $('.incident-item[data-status="In Progress"]').show();
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
    });
    
    // View incident
    $(document).on('click', '.view-incident-btn', function(e) {
        e.preventDefault();
        const incidentId = $(this).data('incident-id');
        window.location.href = '{{ route("modules.incidents.show", ":id") }}'.replace(':id', incidentId);
    });
    
    $(document).on('click', '.incident-link', function(e) {
        e.preventDefault();
        const incidentId = $(this).data('incident-id');
        if (incidentId) {
            window.location.href = '{{ route("modules.incidents.show", ":id") }}'.replace(':id', incidentId);
        }
    });
});
</script>
@endpush
@endsection
