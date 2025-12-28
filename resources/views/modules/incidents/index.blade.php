@extends('layouts.app')

@section('title', 'Incident Management')

@php
$user = auth()->user();
$isHR = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
$isHOD = $user->hasRole('HOD') || $user->hasRole('System Admin');
$isManager = $isHR || $isHOD;
$activeTab = request()->get('tab', 'all');
@endphp

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-error-circle me-2"></i>Incident Management
            </h1>
            <p class="text-muted mb-0">Capture, triage, and resolve incidents efficiently</p>
        </div>
        <div>
            <a href="{{ route('modules.incidents.dashboard') }}" class="btn btn-info shadow-sm me-2">
                <i class="bx bx-dashboard me-1"></i>Dashboard
            </a>
            @if($isManager)
            <a href="{{ route('modules.incidents.create') }}" class="btn btn-primary shadow-sm">
                <i class="bx bx-plus-circle me-1"></i>Create Incident
            </a>
            @endif
        </div>
    </div>

    @if($isManager)
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Incidents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-list-ul fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">New</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['new'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Progress</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['in_progress'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-loader-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Critical</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['critical'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-error-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Tabs Interface -->
    <div class="card shadow mb-4">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-tabs card-header-tabs" id="incidentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'all' ? 'active' : '' }}" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" onclick="switchTab('all')">
                        <i class="bx bx-list-ul me-1"></i>All Incidents
                        <span class="badge bg-secondary ms-2">{{ $allIncidents->count() }}</span>
                    </button>
                </li>
                @if($newIncidents->count() > 0 || $isManager)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'new' ? 'active' : '' }}" id="new-tab" data-bs-toggle="tab" data-bs-target="#new" type="button" role="tab" onclick="switchTab('new')">
                        <i class="bx bx-bell me-1"></i>New
                        <span class="badge bg-warning ms-2">{{ $newIncidents->count() }}</span>
                    </button>
                </li>
                @endif
                @if($assignedIncidents->count() > 0 || $isManager)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'assigned' ? 'active' : '' }}" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned" type="button" role="tab" onclick="switchTab('assigned')">
                        <i class="bx bx-user-check me-1"></i>Assigned
                        <span class="badge bg-info ms-2">{{ $assignedIncidents->count() }}</span>
                    </button>
                </li>
                @endif
                @if($inProgressIncidents->count() > 0 || $isManager)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'in_progress' ? 'active' : '' }}" id="in-progress-tab" data-bs-toggle="tab" data-bs-target="#in-progress" type="button" role="tab" onclick="switchTab('in_progress')">
                        <i class="bx bx-loader-circle me-1"></i>In Progress
                        <span class="badge bg-warning ms-2">{{ $inProgressIncidents->count() }}</span>
                    </button>
                </li>
                @endif
                @if($resolvedIncidents->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'resolved' ? 'active' : '' }}" id="resolved-tab" data-bs-toggle="tab" data-bs-target="#resolved" type="button" role="tab" onclick="switchTab('resolved')">
                        <i class="bx bx-check-circle me-1"></i>Resolved
                        <span class="badge bg-success ms-2">{{ $resolvedIncidents->count() }}</span>
                    </button>
                </li>
                @endif
                @if($myIncidents->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'my_incidents' ? 'active' : '' }}" id="my-incidents-tab" data-bs-toggle="tab" data-bs-target="#my-incidents" type="button" role="tab" onclick="switchTab('my_incidents')">
                        <i class="bx bx-user me-1"></i>My Incidents
                        <span class="badge bg-primary ms-2">{{ $myIncidents->count() }}</span>
                    </button>
                </li>
                @endif
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="incidentTabsContent">
                <!-- All Incidents Tab -->
                <div class="tab-pane fade {{ $activeTab === 'all' ? 'show active' : '' }}" id="all" role="tabpanel">
                    @include('modules.incidents.partials.incidents-table', ['incidents' => $allIncidents, 'isManager' => $isManager])
                </div>

                <!-- New Incidents Tab -->
                @if($newIncidents->count() > 0 || $isManager)
                <div class="tab-pane fade {{ $activeTab === 'new' ? 'show active' : '' }}" id="new" role="tabpanel">
                    @include('modules.incidents.partials.incidents-table', ['incidents' => $newIncidents, 'isManager' => $isManager])
                </div>
                @endif

                <!-- Assigned Incidents Tab -->
                @if($assignedIncidents->count() > 0 || $isManager)
                <div class="tab-pane fade {{ $activeTab === 'assigned' ? 'show active' : '' }}" id="assigned" role="tabpanel">
                    @include('modules.incidents.partials.incidents-table', ['incidents' => $assignedIncidents, 'isManager' => $isManager])
                </div>
                @endif

                <!-- In Progress Incidents Tab -->
                @if($inProgressIncidents->count() > 0 || $isManager)
                <div class="tab-pane fade {{ $activeTab === 'in_progress' ? 'show active' : '' }}" id="in-progress" role="tabpanel">
                    @include('modules.incidents.partials.incidents-table', ['incidents' => $inProgressIncidents, 'isManager' => $isManager])
                </div>
                @endif

                <!-- Resolved Incidents Tab -->
                @if($resolvedIncidents->count() > 0)
                <div class="tab-pane fade {{ $activeTab === 'resolved' ? 'show active' : '' }}" id="resolved" role="tabpanel">
                    @include('modules.incidents.partials.incidents-table', ['incidents' => $resolvedIncidents, 'isManager' => $isManager])
                </div>
                @endif

                <!-- My Incidents Tab -->
                @if($myIncidents->count() > 0)
                <div class="tab-pane fade {{ $activeTab === 'my_incidents' ? 'show active' : '' }}" id="my-incidents" role="tabpanel">
                    @include('modules.incidents.partials.incidents-table', ['incidents' => $myIncidents, 'isManager' => $isManager])
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function switchTab(tab) {
    window.location.href = '{{ route("modules.incidents") }}?tab=' + tab;
}
</script>
@endpush
@endsection
