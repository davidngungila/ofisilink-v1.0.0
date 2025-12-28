@extends('layouts.app')

@section('title', 'Advanced Task Management')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-task"></i>Task Management
                </h4>
                <p class="text-muted">Enterprise-grade task management with advanced analytics, progress tracking, and comprehensive reporting</p>
            </div>
            <div class="btn-group" role="group">
                @if($isManager)
                    <button class="btn btn-primary" id="create-task-btn">
                        <i class="bx bx-plus"></i> Create Task
                    </button>
                    <button class="btn btn-success" id="bulk-create-tasks-btn">
                        <i class="bx bx-list-plus"></i> Bulk Create
                    </button>
                @endif
                <button class="btn btn-primary" id="live-search-btn">
                    <i class="bx bx-search-alt"></i> Live Search
                </button>
                <button class="btn btn-secondary" id="analytics-btn">
                    <i class="bx bx-bar-chart"></i> Analytics
                </button>
                <button class="btn btn-outline-dark" id="refresh-btn">
                    <i class="bx bx-refresh"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<style>
    .task-card { 
        transition: all 0.3s ease; 
        border: 1px solid #e9ecef;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 15px;
    }
    .task-card:hover { 
        transform: translateY(-3px); 
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
    .task-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        padding: 15px 0;
    }
    .task-item {
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s;
        cursor: pointer;
    }
    .task-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .priority-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    .progress-bar-custom {
        height: 8px;
        border-radius: 4px;
    }
    .task-meta {
        font-size: 0.85rem;
        color: #6c757d;
    }
    .filter-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    /* Ensure SweetAlert is above Bootstrap modals */
    .swal2-container { z-index: 200000 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Dashboard Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Tasks</h6>
                        <h3 class="mb-0 text-primary">{{ $dashboardStats['total'] ?? ($dashboardStats['total_tasks'] ?? 0) }}</h3>
                        <small class="text-success">
                            <i class="bx bx-trending-up"></i> Active projects
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary rounded">
                            <i class="bx bx-task"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">In Progress</h6>
                        <h3 class="mb-0 text-info">{{ $dashboardStats['in_progress'] ?? ($dashboardStats['pending'] ?? 0) }}</h3>
                        <small class="text-info">
                            <i class="bx bx-time"></i> Currently active
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
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Completed</h6>
                        <h3 class="mb-0 text-success">{{ $dashboardStats['completed'] ?? 0 }}</h3>
                        <small class="text-success">
                            <i class="bx bx-check-circle"></i> Finished
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-success rounded">
                            <i class="bx bx-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Overdue</h6>
                        <h3 class="mb-0 text-danger">{{ $dashboardStats['overdue'] ?? 0 }}</h3>
                        <small class="text-warning">
                            <i class="bx bx-error-circle"></i> Needs attention
                        </small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-danger rounded">
                            <i class="bx bx-error"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="filter-section">
        <form id="filterForm" method="GET" action="{{ route('modules.tasks') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="not_started" {{ request('status') == 'not_started' ? 'selected' : '' }}>Not Started</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priorities</option>
                    <option value="Low" {{ request('priority') == 'Low' ? 'selected' : '' }}>Low</option>
                    <option value="Normal" {{ request('priority') == 'Normal' ? 'selected' : '' }}>Normal</option>
                    <option value="High" {{ request('priority') == 'High' ? 'selected' : '' }}>High</option>
                    <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            @if($isManager)
            <div class="col-md-3">
                <label class="form-label">Team Leader</label>
                <select name="leader" class="form-select">
                    <option value="">All Leaders</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('leader') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search tasks..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-search"></i>
                    </button>
                    <a href="{{ route('modules.tasks') }}" class="btn btn-secondary">
                        <i class="bx bx-x"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tasks Grid -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="bx bx-list-ul"></i> All Tasks
            </h5>
        </div>
        <div class="card-body">
            <div class="task-grid" id="tasksGrid">
                @forelse($mainTasks as $task)
                <div class="task-item" onclick="window.location.href='{{ route('modules.tasks.show', $task->id) }}'">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">{{ $task->name }}</h6>
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge priority-badge bg-{{ $task->priority == 'Urgent' ? 'danger' : ($task->priority == 'High' ? 'warning' : ($task->priority == 'Normal' ? 'info' : 'secondary')) }}">
                                    {{ $task->priority }}
                                </span>
                                <span class="badge status-badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'info' : ($task->status == 'on_hold' ? 'warning' : 'secondary')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                </span>
                            </div>
                        </div>
                        <span class="badge bg-light text-dark">#{{ $task->id }}</span>
                    </div>
                    
                    @if($task->description)
                    <p class="task-meta mb-2" style="font-size: 0.85rem; color: #6c757d;">
                        {{ Str::limit($task->description, 100) }}
                    </p>
                    @endif
                    
                    <div class="mb-2">
                        <small class="text-muted d-block">
                            <i class="bx bx-user"></i> Leader: {{ $task->teamLeader->name ?? 'Unassigned' }}
                        </small>
                        @if($task->start_date)
                        <small class="text-muted d-block">
                            <i class="bx bx-calendar"></i> Start: {{ \Carbon\Carbon::parse($task->start_date)->format('M d, Y') }}
                        </small>
                        @endif
                        @if($task->end_date)
                        <small class="text-muted d-block">
                            <i class="bx bx-calendar-check"></i> Due: {{ \Carbon\Carbon::parse($task->end_date)->format('M d, Y') }}
                        </small>
                        @endif
                    </div>
                    
                    @if($task->activities && $task->activities->count() > 0)
                    <div class="mb-2">
                        <small class="text-muted">
                            <i class="bx bx-list-check"></i> {{ $task->activities->count() }} Activities
                        </small>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <a href="{{ route('modules.tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-show"></i> View Details
                        </a>
                        <small class="text-muted">
                            {{ $task->created_at->diffForHumans() }}
                        </small>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="bx bx-info-circle"></i> No tasks found. 
                        @if($isManager)
                        <a href="#" id="create-task-btn-link" class="alert-link">Create your first task</a>
                        @endif
                    </div>
                </div>
                @endforelse
            </div>
            
            @if($mainTasks->hasPages())
            <div class="mt-4">
                {{ $mainTasks->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Create Task Button
    $('#create-task-btn, #create-task-btn-link').on('click', function(e) {
        e.preventDefault();
        // Redirect to create page or show modal
        window.location.href = '{{ route("modules.tasks") }}?action=create';
    });
    
    // Refresh Button
    $('#refresh-btn').on('click', function() {
        location.reload();
    });
    
    // Live Search
    $('#live-search-btn').on('click', function() {
        $('input[name="search"]').focus();
    });
});
</script>
@endpush

