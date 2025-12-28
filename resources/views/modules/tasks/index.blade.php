@extends('layouts.app')

@section('title', 'Task Management')

@section('breadcrumb')
    <div class="db-breadcrumb d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="breadcrumb-title mb-1">Task Management</h4>
            <small class="text-muted">Plan, assign, and track activities with advanced features</small>
        </div>
        @if($isManager)
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMainTaskModal">
            Create Task
        </button>
        @endif
    </div>
@endsection

@section('content')
<div class="container-fluid" id="taskApp">
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        @if($isManager)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['total'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-tasks fa-2x text-gray-300"></i></div>
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
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['in_progress'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-spinner fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['completed'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['overdue'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">My Tasks</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['total_tasks'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-user-tasks fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['pending'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['overdue'] }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-exclamation-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Advanced View Tabs -->
    <ul class="nav nav-tabs nav-pills mb-4" id="viewTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-view" type="button" role="tab" aria-controls="dashboard-view" aria-selected="true">
                <i class="fas fa-chart-line me-2"></i>Dashboard
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-view" type="button" role="tab" aria-controls="list-view" aria-selected="false">
                <i class="fas fa-list me-2"></i>List View
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button" role="tab" aria-controls="calendar-view" aria-selected="false">
                <i class="fas fa-calendar-alt me-2"></i>Calendar
            </button>
        </li>
        @if($isManager)
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics-view" type="button" role="tab" aria-controls="analytics-view" aria-selected="false">
                <i class="fas fa-chart-bar me-2"></i>Analytics
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports-view" type="button" role="tab" aria-controls="reports-view" aria-selected="false">
                <i class="fas fa-file-alt me-2"></i>Reports
            </button>
        </li>
        @endif
    </ul>

    <!-- Advanced Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="planning" {{ $filters['status'] == 'planning' ? 'selected' : '' }}>Planning</option>
                        <option value="in_progress" {{ $filters['status'] == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $filters['status'] == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="delayed" {{ $filters['status'] == 'delayed' ? 'selected' : '' }}>Delayed</option>
                    </select>
                </div>
                @if($isManager)
                <div class="col-md-2">
                    <label class="form-label small">Priority</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">All Priorities</option>
                        <option value="Low">Low</option>
                        <option value="Normal">Normal</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Team Leader</label>
                    <select name="leader" class="form-select form-select-sm">
                        <option value="">All Leaders</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $filters['leader'] == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="col-md-3">
                    <label class="form-label small">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search tasks..." value="{{ request('search', '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Actions</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                        <a href="{{ route('modules.tasks') }}" class="btn btn-secondary btn-sm">Clear</a>
                        @if($isManager)
                        <button type="button" class="btn btn-success btn-sm" onclick="exportTasks()">Export</button>
                        @endif
                    </div>
                </div>
                    </form>
                </div>
        </div>

    <!-- Tab Content -->
    <div class="tab-content" id="viewTabsContent">
        <!-- Dashboard View -->
        <div class="tab-pane fade show active" id="dashboard-view" role="tabpanel" aria-labelledby="dashboard-tab">
            <div class="row">
                <!-- Quick Stats Cards -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Tasks</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['total'] ?? 0 }}</div>
                                    <small class="text-muted">All time</small>
                                </div>
                                <div class="text-primary">
                                    <i class="fas fa-tasks fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">In Progress</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['in_progress'] ?? 0 }}</div>
                                    <small class="text-muted">Active now</small>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-spinner fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['completed'] ?? 0 }}</div>
                                    <small class="text-muted">This period</small>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card border-left-danger shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Overdue</div>
                                    <div class="h3 mb-0 font-weight-bold text-gray-800">{{ $dashboardStats['overdue'] ?? 0 }}</div>
                                    <small class="text-muted">Requires attention</small>
                                </div>
                                <div class="text-danger">
                                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-area me-2"></i>Task Progress Overview</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="taskProgressChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie me-2"></i>Status Distribution</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="statusDistributionChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Priority Tasks -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-fire me-2"></i>Priority Tasks</h6>
                            <a href="#" onclick="document.getElementById('list-tab').click(); return false;" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @php
                                    $priorityTasks = $mainTasks->whereIn('priority', ['High', 'Critical'])->sortByDesc('priority')->take(5);
                                @endphp
                                @forelse($priorityTasks as $task)
                                <div class="list-group-item px-0 border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="#" onclick="viewTaskDetails({{ $task->id }}); return false;" class="text-decoration-none">{{ $task->name }}</a>
                                                <span class="badge bg-{{ $task->priority == 'Critical' ? 'danger' : 'warning' }} ms-2">{{ $task->priority }}</span>
                                            </h6>
                                            <p class="mb-1 small text-muted">{{ \Illuminate\Support\Str::limit($task->description ?? '', 60) }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>{{ $task->teamLeader->name ?? 'N/A' }}
                                                <span class="ms-2"><i class="fas fa-calendar me-1"></i>{{ $task->end_date ? $task->end_date->format('M j, Y') : 'N/A' }}</span>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <div class="progress mb-2" style="width: 60px; height: 6px;">
                                                <div class="progress-bar bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'info' : 'warning') }}" 
                                                     style="width: {{ $task->progress_percentage }}%"></div>
                                            </div>
                                            <small class="text-muted">{{ $task->progress_percentage }}%</small>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No priority tasks</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clock me-2"></i>Upcoming Deadlines</h6>
                            <a href="#" onclick="document.getElementById('calendar-tab').click(); return false;" class="btn btn-sm btn-outline-primary">View Calendar</a>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @php
                                    $upcomingTasks = $mainTasks->filter(function($task) {
                                        return $task->end_date && $task->end_date >= now() && $task->status != 'completed';
                                    })->sortBy('end_date')->take(5);
                                @endphp
                                @forelse($upcomingTasks as $task)
                                <div class="list-group-item px-0 border-0 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <a href="#" onclick="viewTaskDetails({{ $task->id }}); return false;" class="text-decoration-none">{{ $task->name }}</a>
                                            </h6>
                                            <p class="mb-1 small text-muted">{{ \Illuminate\Support\Str::limit($task->description ?? '', 60) }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>{{ $task->teamLeader->name ?? 'N/A' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <div class="badge bg-{{ $task->end_date->diffInDays(now()) <= 3 ? 'danger' : ($task->end_date->diffInDays(now()) <= 7 ? 'warning' : 'info') }}">
                                                {{ $task->end_date->diffForHumans() }}
                                            </div>
                                            <div class="progress mt-2" style="width: 60px; height: 6px;">
                                                <div class="progress-bar bg-info" style="width: {{ $task->progress_percentage }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-calendar-check fa-2x mb-2"></i>
                                    <p class="mb-0">No upcoming deadlines</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- List View -->
        <div class="tab-pane fade" id="list-view" role="tabpanel" aria-labelledby="list-tab">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Tasks ({{ $mainTasks->count() }})</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" onclick="refreshTasks()">Refresh</button>
            </div>
        </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="tasksTable">
                            <thead>
                                <tr>
                                    <th width="50"><input type="checkbox" id="selectAllTasks"></th>
                                    <th>Task</th>
                                   
                                    <th>Priority</th>
                                    <th>Status</th>

                                    <th>Team Leader</th>
                                    <th>Progress</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($mainTasks as $task)
                                <tr data-task-id="{{ $task->id }}">
                                    <td><input type="checkbox" class="task-checkbox" value="{{ $task->id }}"></td>
                                    <td>
                                        <div>
                                            <strong class="text-primary cursor-pointer" onclick="viewTaskDetails({{ $task->id }})">{{ $task->name }}</strong>
                                            @if($task->tags)
                                            <div class="mt-1">
                                                @php
                                                    $tagsArray = is_array($task->tags) ? $task->tags : (is_string($task->tags) ? json_decode($task->tags, true) : []);
                                                    $tagsArray = $tagsArray ?: [];
                                                @endphp
                                                @foreach($tagsArray as $tag)
                                                @if(!empty($tag))
                                                <span class="badge badge-secondary badge-sm me-1">{{ $tag }}</span>
                                                @endif
                                                @endforeach
                                            </div>
                                            @endif
                                        </div>
                                            <small class="text-muted">{{ \Illuminate\Support\Str::limit($task->description ?? '', 60) }}</small>
                                    </td>
                                    
                                    <td>
                                        @php
                                            $priorityClass = match($task->priority ?? 'Normal') {
                                                'Critical' => 'danger',
                                                'High' => 'warning',
                                                'Normal' => 'info',
                                                'Low' => 'secondary',
                                                default => 'info'
                                            };
                                        @endphp
                                        <span class="badge btn-{{ $priorityClass }}">{{ $task->priority ?? 'Normal' }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($task->status) {
                                                'completed' => 'success',
                                                'in_progress' => 'info',
                                                'delayed' => 'danger',
                                                'planning' => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge btn-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $task->status)) }}</span>
                                    </td>
                                    <td>{{ $task->teamLeader->name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                <div class="progress-bar bg-{{ $statusClass }}" role="progressbar" style="width: {{ $task->progress_percentage }}%"></div>
                                            </div>
                                            <small>{{ $task->progress_percentage }}%</small>
                                        </div>
                                    </td>
                                    <td>
                                        {{ $task->end_date ? $task->end_date->format('M j, Y') : 'N/A' }}
                                        @if($task->end_date && $task->end_date < now() && $task->status !== 'completed')
                                        <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Overdue</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-sm btn-primary" onclick="viewTaskDetails({{ $task->id }})" title="View Details">
                                                View
                                            </button>
                                            <a href="/modules/tasks/pdf?task_id={{ $task->id }}" class="btn btn-sm btn-danger" target="_blank" title="Download PDF">
                                                PDF
                                            </a>
                                            @if($isManager)
                                            <button class="btn btn-sm btn-outline-secondary" onclick="editTask({{ $task->id }})" title="Edit">
                                                Edit
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="9" class="text-center py-4 text-muted">No tasks found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar View -->
        <div class="tab-pane fade" id="calendar-view" role="tabpanel" aria-labelledby="calendar-tab">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-3">
                        <h6 class="m-0 font-weight-bold text-primary mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Advanced Task Calendar - Tanzania
                            <small class="text-muted ms-2">
                                <i class="fas fa-clock me-1"></i>
                                <span id="tz-display">Africa/Dar_es_Salaam (GMT+3)</span>
                            </small>
                            </h6>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            @if($isManager)
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createMainTaskModal">
                                <i class="fas fa-plus me-1"></i>Add New Task
                            </button>
                            @endif
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download me-1"></i>Export
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="exportCalendar('pdf'); return false;"><i class="fas fa-file-pdf me-2"></i>Export as PDF</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportCalendar('csv'); return false;"><i class="fas fa-file-csv me-2"></i>Export as CSV</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="exportCalendar('excel'); return false;"><i class="fas fa-file-excel me-2"></i>Export as Excel</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Calendar Controls -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-sm btn-outline-primary active" onclick="setCalendarView('month')" id="view-month-btn">
                                    <i class="fas fa-calendar me-1"></i>Month
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="setCalendarView('week')" id="view-week-btn">
                                    <i class="fas fa-calendar-week me-1"></i>Week
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="setCalendarView('day')" id="view-day-btn">
                                    <i class="fas fa-calendar-day me-1"></i>Day
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="d-flex align-items-center justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="navigateCalendar(-1)" title="Previous">
                                    Previous
                            </button>
                            <button type="button" class="btn btn-sm btn-primary" onclick="goToToday()" title="Today">
                                    <i class="fas fa-calendar-check me-1"></i>Today
                            </button>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="navigateCalendar(1)" title="Next">
                                    Next
                            </button>
                        </div>
                            <h5 class="mb-0 mt-2" id="calendar-month-year"></h5>
                    </div>
                        <div class="col-md-4">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" id="calendar-search" placeholder="Search tasks..." onkeyup="filterCalendarTasks(this.value)">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Calendar Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <span class="text-muted small"><i class="fas fa-filter me-1"></i>Filters:</span>
                                <select class="form-select form-select-sm" style="width: auto;" id="filter-status" onchange="applyCalendarFilters()">
                                    <option value="">All Status</option>
                                    <option value="planning">Planning</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="delayed">Delayed</option>
                                </select>
                                <select class="form-select form-select-sm" style="width: auto;" id="filter-priority" onchange="applyCalendarFilters()">
                                    <option value="">All Priority</option>
                                    <option value="Low">Low</option>
                                    <option value="Normal">Normal</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                                <select class="form-select form-select-sm" style="width: auto;" id="filter-category" onchange="applyCalendarFilters()">
                                    <option value="">All Categories</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetCalendarFilters()">
                                    <i class="fas fa-redo me-1"></i>Reset
                                </button>
                                <div class="form-check ms-auto">
                                    <input class="form-check-input" type="checkbox" id="show-holidays" checked onchange="toggleHolidays()">
                                    <label class="form-check-label small" for="show-holidays">
                                        Show Tanzania Holidays
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="task-calendar" class="advanced-calendar"></div>
                </div>
            </div>
                        </div>

        <!-- Analytics View -->
        @if($isManager)
        <div class="tab-pane fade" id="analytics-view" role="tabpanel" aria-labelledby="analytics-tab">
            <!-- Date Filters -->
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-calendar-alt me-1"></i>Date From</label>
                            <input type="date" class="form-control" id="analyticsDateFrom" value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-calendar-alt me-1"></i>Date To</label>
                            <input type="date" class="form-control" id="analyticsDateTo" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-filter me-1"></i>Status Filter</label>
                            <select class="form-select" id="analyticsStatusFilter">
                                <option value="">All Statuses</option>
                                <option value="planning">Planning</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="delayed">Delayed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100" onclick="refreshTaskAnalytics()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh Analytics
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics Cards -->
            <div class="row mb-4" id="taskStatsCards">
                @php
                    $totalTasks = $mainTasks->count();
                    $completedTasks = $mainTasks->where('status', 'completed')->count();
                    $inProgressTasks = $mainTasks->where('status', 'in_progress')->count();
                    $planningTasks = $mainTasks->where('status', 'planning')->count();
                    $delayedTasks = $mainTasks->where('status', 'delayed')->count();
                    $avgProgress = round($mainTasks->avg('progress_percentage') ?? 0);
                    $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                @endphp
                <div class="col-md-3 mb-3">
                    <div class="card stat-card primary shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Tasks</h6>
                                    <h3 class="mb-0">{{ $totalTasks }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-tasks fa-2x text-primary" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card success shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Completed</h6>
                                    <h3 class="mb-0">{{ $completedTasks }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-check-circle fa-2x text-success" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card info shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">In Progress</h6>
                                    <h3 class="mb-0">{{ $inProgressTasks }}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-spinner fa-2x text-info" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card warning shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Completion Rate</h6>
                                    <h3 class="mb-0">{{ $completionRate }}%</h3>
                                </div>
                                <div>
                                    <i class="fas fa-chart-line fa-2x text-warning" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Priority Breakdown Cards -->
            <div class="row mb-4">
                @php
                    $lowPriority = $mainTasks->where('priority', 'Low')->count();
                    $normalPriority = $mainTasks->filter(function($task) {
                        return $task->priority === 'Normal' || $task->priority === null;
                    })->count();
                    $highPriority = $mainTasks->where('priority', 'High')->count();
                    $criticalPriority = $mainTasks->where('priority', 'Critical')->count();
                @endphp
                <div class="col-md-3 mb-3">
                    <div class="card stat-card info shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2"><i class="fas fa-info-circle me-1"></i>Low Priority</h6>
                            <h3 class="mb-0">{{ $lowPriority }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card secondary shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2"><i class="fas fa-circle me-1"></i>Normal Priority</h6>
                            <h3 class="mb-0">{{ $normalPriority }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card warning shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2"><i class="fas fa-exclamation-circle me-1"></i>High Priority</h6>
                            <h3 class="mb-0">{{ $highPriority }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card danger shadow-sm">
                        <div class="card-body text-center">
                            <h6 class="text-muted mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Critical</h6>
                            <h3 class="mb-0">{{ $criticalPriority }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1: Priority & Team Leader -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card analytics-card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-chart-bar me-1"></i>Tasks by Priority</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="priorityChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card analytics-card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-users me-1"></i>Tasks by Team Leader</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="leaderChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2: Completion Trend & Status Distribution -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card analytics-card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-chart-line me-1"></i>Task Completion Trend</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="completionTrendChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card analytics-card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-chart-pie me-1"></i>Status Distribution</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="statusDistributionChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 3: Category & Progress Distribution -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card analytics-card shadow-sm">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-tags me-1"></i>Tasks by Category</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="categoryChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card analytics-card shadow-sm">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-tasks me-1"></i>Progress Distribution</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="progressDistributionChart" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 4: Timeline & Performance -->
            <div class="row mb-4">
                <div class="col-lg-12 mb-4">
                    <div class="card analytics-card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-chart-area me-1"></i>Task Timeline (Created vs Completed)</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="timelineChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Metrics & Category Table -->
            <div class="row mb-4">
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-clock me-2"></i>Performance Metrics</h6>
                        </div>
                        <div class="card-body">
                            @php
                                $onTimeTasks = $mainTasks->filter(function($task) {
                                    return $task->status == 'completed' && $task->end_date && $task->end_date >= now();
                                })->count();
                                $avgProgress = round($mainTasks->avg('progress_percentage') ?? 0);
                            @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Completion Rate</span>
                                    <strong>{{ $completionRate }}%</strong>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-success" style="width: {{ $completionRate }}%">
                                        {{ $completedTasks }}/{{ $totalTasks }}
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>On-Time Delivery</span>
                                    <strong>{{ $completedTasks > 0 ? round(($onTimeTasks / $completedTasks) * 100) : 0 }}%</strong>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-info" style="width: {{ $completedTasks > 0 ? ($onTimeTasks / $completedTasks) * 100 : 0 }}%">
                                        {{ $onTimeTasks }}/{{ $completedTasks }}
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Average Progress</span>
                                    <strong>{{ $avgProgress }}%</strong>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $avgProgress }}%">
                                        {{ $avgProgress }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-tags me-2"></i>Tasks by Category</h6>
                            <button class="btn btn-sm btn-success" onclick="exportTaskAnalytics('pdf')">
                                <i class="fas fa-file-pdf me-1"></i>Export PDF
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>Category</th>
                                            <th>Count</th>
                                            <th>Completed</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categoryTableBody">
                                        @php
                                            $categoryStats = $mainTasks->groupBy('category')->map(function($tasks) {
                                                return [
                                                    'count' => $tasks->count(),
                                                    'completed' => $tasks->where('status', 'completed')->count(),
                                                    'avg_progress' => round($tasks->avg('progress_percentage') ?? 0)
                                                ];
                                            });
                                        @endphp
                                        @forelse($categoryStats as $category => $stats)
                                        <tr>
                                            <td><strong>{{ $category ?: 'Uncategorized' }}</strong></td>
                                            <td><span class="badge bg-primary">{{ $stats['count'] }}</span></td>
                                            <td><span class="badge bg-success">{{ $stats['completed'] }}</span></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" style="width: {{ $stats['avg_progress'] }}%">{{ $stats['avg_progress'] }}%</div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="4" class="text-center text-muted">No category data</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Reports View -->
        @if($isManager)
        <div class="tab-pane fade" id="reports-view" role="tabpanel" aria-labelledby="reports-tab">
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-alt me-2"></i>Task Reports</h6>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary" onclick="generateReport('summary')">
                                    Summary Report
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="generateReport('detailed')">
                                    Detailed Report
                                </button>
                                    </div>
                                </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Task Name</th>
                                            <th>Team Leader</th>
                                            <th>Status</th>
                                            <th>Progress</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($mainTasks as $task)
                                        <tr>
                                            <td><strong>{{ $task->name }}</strong></td>
                                            <td>{{ $task->teamLeader->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $task->status == 'completed' ? 'success' : ($task->status == 'in_progress' ? 'info' : ($task->status == 'delayed' ? 'danger' : ($task->status == 'planning' ? 'warning' : 'secondary'))) }}">{{ ucwords(str_replace('_', ' ', $task->status)) }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 100px; height: 8px;">
                                    <div class="progress-bar" style="width: {{ $task->progress_percentage }}%"></div>
                                </div>
                                                    <span>{{ $task->progress_percentage }}%</span>
                            </div>
                                            </td>
                                            <td>{{ $task->start_date ? $task->start_date->format('M j, Y') : 'N/A' }}</td>
                                            <td>{{ $task->end_date ? $task->end_date->format('M j, Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="/modules/tasks/pdf?task_id={{ $task->id }}" class="btn btn-sm btn-danger" target="_blank">
                                                    PDF
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr><td colspan="7" class="text-center text-muted py-4">No tasks available for reporting</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Create Task Modal (Advanced) -->
@if($isManager)
<div class="modal fade" id="createMainTaskModal" tabindex="-1" aria-labelledby="createMainTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="createMainTaskModalLabel"><i class="fas fa-plus-circle me-2"></i>New Task</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="createMainTaskForm" data-ajax-form="true">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" value="task_create_main">
                    
                    <!-- Basic Information -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Task Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="Enter task name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g., Development, Marketing">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe the task in detail"></textarea>
                        </div>
                    </div>

                    <!-- Priority & Status -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="Normal">Normal</option>
                                <option value="Low">Low</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Initial Status</label>
                            <select name="status" class="form-select">
                                <option value="planning">Planning</option>
                                <option value="in_progress" selected>In Progress</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Budget (Optional)</label>
                            <input type="number" name="budget" class="form-control" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tags (comma separated)</label>
                            <input type="text" name="tags" class="form-control" placeholder="urgent, q1, client">
                        </div>
                    </div>

                    <!-- Dates & Timeframe -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="start_date" id="create-start-date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" name="end_date" id="create-end-date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Timeframe</label>
                            <input type="text" name="timeframe" id="create-timeframe" class="form-control" readonly placeholder="Auto-calculated">
                        </div>
                    </div>

                    <!-- Team Assignment -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Team Leader <span class="text-danger">*</span></label>
                            <select name="team_leader_id" class="form-select" required>
                                <option value="">-- Select Team Leader --</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Initial Activities -->
                    <h6 class="mb-3"><i class="fas fa-tasks me-2"></i>Initial Activities</h6>
                    <div id="initial-activities-container"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-activity-input">
                        Add Activity
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- View Task Details Modal (Advanced) -->
<div class="modal fade" id="viewTaskModal" tabindex="-1" aria-labelledby="viewTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="view-task-content">
            <div class="modal-header">
                <h5 class="modal-title">Loading...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Loading task details...</p>
            </div>
        </div>
    </div>
</div>

<!-- Edit Task Modal -->
<div class="modal fade" id="editTaskModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="edit-task-content"></div>
    </div>
</div>

<!-- Manage Activity Modal -->
<div class="modal fade" id="manageActivityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="manageActivityForm" data-ajax-form="true">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" value="task_update_activity">
                    <input type="hidden" name="activity_id" id="manage-activity-id">
                    
                    <div class="form-group mb-3">
                        <label>Activity Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="manage-activity-name" class="form-control" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" name="start_date" id="manage-start-date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" name="end_date" id="manage-end-date" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>Priority</label>
                            <select name="priority" id="manage-activity-priority" class="form-select">
                                <option value="Normal">Normal</option>
                                <option value="Low">Low</option>
                                <option value="High">High</option>
                                <option value="Critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Estimated Hours</label>
                            <input type="number" name="estimated_hours" id="manage-estimated-hours" class="form-control" min="0">
                        </div>
                        <div class="col-md-6">
                            <label>Timeframe</label>
                            <input type="text" name="timeframe" id="manage-timeframe" class="form-control" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label>Assign Staff Members</label>
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                            @foreach($users as $user)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="manage-user-{{ $user->id }}">
                                <label class="form-check-label" for="manage-user-{{ $user->id }}">{{ $user->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Activity Details Modal -->
<div class="modal fade" id="activityDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white" id="activity-details-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="activity-details-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<!-- Chart.js for Analytics -->
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<script>
const allUsersData = @json($users);
const mainTasksData = @json($mainTasks);
const CSRF = '{{ csrf_token() }}';
const AppConfig = { 
    userId: {{ Auth::id() }}, 
    userRole: '{{ Auth::user()->roles()->first()->name ?? 'Staff' }}', 
    isManager: {{ $isManager ? 'true' : 'false' }},
    actionUrl: '{{ route("modules.tasks.action") }}'
};

$(document).ready(() => {
    const TaskManager = {
        currentMainTaskId: null,
        currentLeaderId: null,
        activitiesData: [],
        currentActivityId: null,

        init() {
            this.bindEvents();
        },

        apiCall(formData) {
            // Ensure CSRF token present even if meta tag is missing
            if (!formData.has('_token')) {
                const tokenMeta = $('meta[name="csrf-token"]').attr('content');
                formData.append('_token', tokenMeta || CSRF);
            }
            return $.ajax({
                type: 'POST',
                url: AppConfig.actionUrl,
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                headers: (function(){
                    const token = $('meta[name="csrf-token"]').attr('content') || CSRF;
                    return token ? { 'X-CSRF-TOKEN': token } : {};
                })()
            });
        },

        bindEvents() {
            $(document).on('submit', 'form[data-ajax-form]', (e) => this.handleFormSubmit(e));
            $('#create-start-date, #create-end-date').on('change', () => this.calculateTimeframe('#create-start-date', '#create-end-date', '#create-timeframe'));
            $('#manage-start-date, #manage-end-date').on('change', () => this.calculateTimeframe('#manage-start-date', '#manage-end-date', '#manage-timeframe'));
            $('#add-activity-input').on('click', () => this.addInitialActivityRow());
            $(document).on('click', '.remove-activity-btn', (e) => $(e.currentTarget).closest('.activity-row').remove());
            $('#createMainTaskModal').on('shown.bs.modal', () => {
                if ($('#initial-activities-container .activity-row').length === 0) {
                    this.addInitialActivityRow();
                }
            });
            // Kanban drag and drop
            if (AppConfig.isManager) {
                $(".kanban-column").sortable({
                    connectWith: ".kanban-column",
                    placeholder: "ui-sortable-placeholder",
                    receive: function(event, ui) {
                        const taskId = $(ui.item).data('task-id');
                        const newStatus = $(this).data('status');
                        if (taskId && newStatus) {
                            TaskManager.updateTaskStatus(taskId, newStatus);
                        }
                    }
                }).disableSelection();
            }
        },

        handleFormSubmit(e) {
            e.preventDefault();
            const form = e.currentTarget;
            const formData = new FormData(form);
            const action = formData.get('action');

            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.data('original-text', originalText);
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

            this.apiCall(formData).done(response => {
                if (response && response.success) {
                    // Show toast notification
                    const successMsg = response.message || 'Operation completed successfully';
                    const description = response.description || 'Your request has been processed successfully.';
                    this.showToast('success', successMsg, description);
                    
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Success',
                            text: response.message || 'Operation completed successfully',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            if (['task_create_main', 'task_edit_main'].includes(action)) {
                                window.location.reload();
                            } else {
                                $(form).closest('.modal').modal('hide');
                                TaskManager.loadActivitiesForCurrentTask();
                                // Refresh activity details if modal is open
                                const activityModal = $('#activityDetailsModal');
                                if (action === 'task_update_activity' && activityModal.is(':visible')) {
                                    const activityId = formData.get('activity_id');
                                    if (activityId) {
                                        setTimeout(() => viewActivityDetails(activityId), 500);
                                    }
                                }
                            }
                        });
                    } else {
                        if (['task_create_main', 'task_edit_main'].includes(action)) {
                            window.location.reload();
                        } else {
                            $(form).closest('.modal').modal('hide');
                            TaskManager.loadActivitiesForCurrentTask();
                        }
                    }
                } else {
                    const errorMsg = (response && response.message) ? response.message : 'An error occurred.';
                    const errorDesc = response.description || 'Please check your input and try again.';
                    this.showToast('error', errorMsg, errorDesc);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', errorMsg, 'error');
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            }).fail((xhr) => {
                console.error('Form submission error:', xhr);
                
                // Extract error message from response
                let errorMsg = 'A network or server error occurred.';
                
                if (xhr.responseJSON) {
                    // Laravel validation errors
                    if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        errorMsg = Object.keys(errors).map(key => errors[key].join(', ')).join('; ');
                    } else if (xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                } else if (xhr.responseText) {
                    try {
                        const parsed = JSON.parse(xhr.responseText);
                        if (parsed.message) errorMsg = parsed.message;
                        else if (parsed.errors) {
                            errorMsg = Object.keys(parsed.errors).map(key => parsed.errors[key].join(', ')).join('; ');
                        }
                    } catch (e) {
                        // Not JSON, try to extract from HTML
                        if (xhr.status === 419) {
                            errorMsg = 'Session expired. Please refresh the page and try again.';
                        } else if (xhr.status === 403) {
                            errorMsg = 'You do not have permission to perform this action.';
                        } else if (xhr.status === 404) {
                            errorMsg = 'The requested resource was not found.';
                        } else if (xhr.status === 422) {
                            errorMsg = 'Validation error. Please check your input.';
                        } else if (xhr.status === 500) {
                            errorMsg = 'Server error. Please try again later.';
                        }
                    }
                } else if (xhr.status) {
                    if (xhr.status === 419) {
                        errorMsg = 'Session expired. Please refresh the page and try again.';
                    } else if (xhr.status === 403) {
                        errorMsg = 'You do not have permission to perform this action.';
                    } else if (xhr.status === 404) {
                        errorMsg = 'The requested resource was not found.';
                    } else if (xhr.status === 422) {
                        errorMsg = 'Validation error. Please check your input.';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Server error. Please try again later.';
                    } else if (xhr.status === 0) {
                        errorMsg = 'Network connection failed. Please check your internet connection.';
                    }
                }
                
                const errorDesc = 'An unexpected error occurred. Please refresh the page and try again.';
                this.showToast('error', errorMsg, errorDesc);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', errorMsg, 'error');
                } else {
                    alert(errorMsg);
                }
            }).always(() => {
                submitBtn.prop('disabled', false).html(submitBtn.data('original-text') || 'Submit');
            });
        },

        showToast(type, message, description = '') {
            // Remove existing toasts
            $('#toast-container').remove();
            
            // Create toast container with increased size and description support
            const toastHtml = `
                <div id="toast-container" class="position-fixed top-0 end-0 p-3" style="z-index: 9999; margin-top: 70px;">
                    <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" 
                         role="alert" aria-live="assertive" aria-atomic="true" 
                         style="min-width: 350px; max-width: 450px;">
                        <div class="d-flex flex-column">
                            <div class="d-flex align-items-start p-3">
                                <div class="toast-body flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2 fs-5"></i>
                                        <strong class="fs-6">${message}</strong>
                                    </div>
                                    ${description ? `<div class="mt-2 ps-4 text-white-50" style="font-size: 0.875rem; line-height: 1.4;">${description}</div>` : ''}
                                </div>
                                <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(toastHtml);
            const toastElement = $('#toast-container .toast');
            const toast = new bootstrap.Toast(toastElement[0], {
                autohide: true,
                delay: 5000
            });
            toast.show();
            
            // Remove container after toast is hidden
            toastElement.on('hidden.bs.toast', function() {
                $('#toast-container').remove();
            });
        },

        calculateTimeframe(startId, endId, targetId) {
            const startDateStr = $(startId).val();
            const endDateStr = $(endId).val();

            if (startDateStr && endDateStr) {
                const start = new Date(startDateStr);
                const end = new Date(endDateStr);

                if (end < start) {
                    $(targetId).val('End date must be after start date.');
                    return;
                }

                let years = end.getFullYear() - start.getFullYear();
                let months = end.getMonth() - start.getMonth();
                let days = end.getDate() - start.getDate();

                if (days < 0) {
                    months--;
                    days += new Date(end.getFullYear(), end.getMonth(), 0).getDate();
                }

                if (months < 0) {
                    years--;
                    months += 12;
                }

                let result = [];
                if (years > 0) result.push(`${years} Year(s)`);
                if (months > 0) result.push(`${months} Month(s)`);
                if (days > 0) result.push(`${days} Day(s)`);

                $(targetId).val(result.join(', ') || 'Same Day');
            } else {
                $(targetId).val('');
            }
        },

        addInitialActivityRow() {
            const index = $('#initial-activities-container .activity-row').length;
            const userOptions = allUsersData.map(user => 
                `<option value="${user.id}">${user.name}</option>`
            ).join('');

            const newRow = `<div class="p-3 mb-2 border rounded activity-row">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="form-label small">Activity Name</label>
                        <input type="text" name="activities[${index}][name]" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="form-label small">Start Date</label>
                        <input type="date" name="activities[${index}][start_date]" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 text-end">
                        <button class="btn btn-danger btn-sm remove-activity-btn" type="button">
                            Delete
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <label class="form-label small">Assign Staff</label>
                        <select name="activities[${index}][users][]" class="form-select form-select-sm" multiple style="min-height: 80px;">
                            ${userOptions}
                        </select>
                    </div>
                </div>
            </div>`;

            $('#initial-activities-container').append(newRow);
        },

        updateTaskStatus(taskId, newStatus) {
            const formData = new FormData();
            formData.append('action', 'update_task_status');
            formData.append('task_id', taskId);
            formData.append('status', newStatus);

            this.apiCall(formData).done(response => {
                if (!response.success) {
                    location.reload();
                }
            });
        },

        loadActivitiesForCurrentTask() {
            if (!this.currentMainTaskId) return;

            const formData = new FormData();
            formData.append('action', 'task_get_details');
            formData.append('main_task_id', this.currentMainTaskId);

            this.apiCall(formData).done(response => {
                if (response.success) {
                    this.activitiesData = response.activities;
                    this.renderActivities(this.activitiesData);
                }
            });
        },

        renderActivities(activities) {
            // Activity rendering will be handled in the view task modal
        }
    };

    TaskManager.init();

    // Global functions
    window.viewTaskDetails = function(taskId) {
        $('#viewTaskModal').modal('show');
        $('#view-task-content').html(`
            <div class="modal-header">
                <h5 class="modal-title">Loading Task...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Loading task details...</p>
            </div>
        `);
        
        const formData = new FormData();
        formData.append('action', 'get_task_full_details');
        formData.append('task_id', taskId);

        TaskManager.apiCall(formData).done(response => {
            if (response.success) {
                renderTaskDetailsModal(response.task);
            } else {
                $('#view-task-content').html(`
                    <div class="modal-header">
                        <h5 class="modal-title">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">${response.message || 'Failed to load task details'}</div>
                    </div>
                `);
            }
        }).fail((xhr) => {
            let msg = 'Network or server error.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
            $('#view-task-content').html(`
                <div class="modal-header">
                    <h5 class="modal-title">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger">${msg}</div>
                </div>
            `);
        });
    };

    window.editTask = function(taskId) {
        const task = mainTasksData.find(t => t.id == taskId);
        if (!task) return;

        $('#editTaskModal').modal('show');
        $('#edit-task-content').html(`
            <div class="modal-header">
                <h5 class="modal-title">Edit Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTaskForm" data-ajax-form="true">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" value="task_edit_main">
                    <input type="hidden" name="main_task_id" value="${task.id}">
                    <div class="form-group mb-3">
                        <label>Task Name</label>
                        <input type="text" name="name" class="form-control" value="${task.name}" required>
                    </div>
                    <div class="form-group mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3">${task.description || ''}</textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="${task.start_date}" required>
                        </div>
                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="${task.end_date}" required>
                        </div>
                        <div class="col-md-4">
                            <label>Priority</label>
                            <select name="priority" class="form-select">
                                <option value="Low" ${task.priority === 'Low' ? 'selected' : ''}>Low</option>
                                <option value="Normal" ${task.priority === 'Normal' || !task.priority ? 'selected' : ''}>Normal</option>
                                <option value="High" ${task.priority === 'High' ? 'selected' : ''}>High</option>
                                <option value="Critical" ${task.priority === 'Critical' ? 'selected' : ''}>Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label>Status</label>
                            <select name="status" class="form-select">
                                <option value="planning" ${task.status === 'planning' ? 'selected' : ''}>Planning</option>
                                <option value="in_progress" ${task.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                <option value="completed" ${task.status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="delayed" ${task.status === 'delayed' ? 'selected' : ''}>Delayed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Team Leader</label>
                            <select name="team_leader_id" class="form-select" required>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" ${task.team_leader_id == {{ $user->id }} ? 'selected' : ''}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- New Activities Section -->
                <div class="border-top p-3 mt-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Activities</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addNewActivityBtn">
                            Add Activity
                        </button>
                    </div>
                    <div id="newActivitiesContainer" class="mb-3"></div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
            <div class="border-top p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0"><i class="fas fa-users me-2"></i>Assign Staff to Existing Activities</h6>
                    <button type="button" id="saveAssignmentsBtn" class="btn btn-sm btn-success">Save Assignments</button>
                </div>
                <div id="activityAssignments" class="small text-muted">Loading activities...</div>
            </div>
        `);

        // Load activities and render assignment checklists
        const fd = new FormData();
        fd.append('action','task_get_details');
        fd.append('main_task_id', taskId);
        TaskManager.apiCall(fd).done(resp => {
            if (!resp.success) { $('#activityAssignments').html('<div class="alert alert-danger">Failed to load activities.</div>'); return; }
            const activities = resp.activities || [];
            if (activities.length === 0) { $('#activityAssignments').html('<div class="text-muted">No activities to assign.</div>'); return; }
            const html = activities.map(act => {
                const assignedIds = (act.assigned_users||[]).map(u=>u.id);
                const userChecks = allUsersData.map(u => {
                    const checked = assignedIds.includes(u.id) ? 'checked' : '';
                    return `<div class=\"form-check form-check-inline\"><input class=\"form-check-input\" type=\"checkbox\" name=\"user_ids[]\" value=\"${u.id}\" ${checked} id=\"assign-${act.id}-${u.id}\"><label class=\"form-check-label\" for=\"assign-${act.id}-${u.id}\">${u.name}</label></div>`;
                }).join('');
                return `<div class=\"border rounded p-2 mb-2 activity-assignment\" data-activity-id=\"${act.id}\">`
                    + `<div class=\"d-flex justify-content-between align-items-center mb-1\">`
                    + `<strong>${act.name}</strong>`
                    + `<span class=\"badge bg-${act.status==='Completed'?'success':(act.status==='In Progress'?'info':(act.status==='Delayed'?'danger':'secondary'))}\">${act.status}</span>`
                    + `</div>`
                    + `<div class=\"text-muted mb-2\">${act.start_date || 'N/A'} ${act.end_date? ' - '+act.end_date: ''}</div>`
                    + `<input type=\"hidden\" name=\"name\" value=\"${act.name.replace(/\"/g,'&quot;')}\">`
                    + `<input type=\"hidden\" name=\"start_date\" value=\"${act.start_date || ''}\">`
                    + `<input type=\"hidden\" name=\"end_date\" value=\"${act.end_date || ''}\">`
                    + `<input type=\"hidden\" name=\"timeframe\" value=\"${act.timeframe || ''}\">`
                    + `<div>${userChecks}</div>`
                    + `</div>`;
            }).join('');
            $('#activityAssignments').html(html);
        }).fail(() => {
            $('#activityAssignments').html('<div class="alert alert-danger">Failed to load activities.</div>');
        });

        // Save assignments handler
        $(document).off('click.saveAssignments').on('click.saveAssignments','#saveAssignmentsBtn', function(){
            const blocks = $('#activityAssignments .activity-assignment');
            if (blocks.length === 0) return;
            let requests = [];
            blocks.each(function(){
                const $b = $(this);
                const aid = $b.data('activity-id');
                const userIds = [];
                $b.find('input[name="user_ids[]"]:checked').each(function(){ userIds.push($(this).val()); });
                const fd2 = new FormData();
                fd2.append('action','task_update_activity');
                fd2.append('activity_id', aid);
                fd2.append('name', $b.find('input[name="name"]').val());
                fd2.append('start_date', $b.find('input[name="start_date"]').val());
                fd2.append('end_date', $b.find('input[name="end_date"]').val());
                fd2.append('timeframe', $b.find('input[name="timeframe"]').val());
                userIds.forEach(id => fd2.append('user_ids[]', id));
                requests.push(TaskManager.apiCall(fd2));
            });
            if (requests.length === 0) return;
            Promise.all(requests).then(results => {
                const ok = results.every(r => r && r.success);
                if (typeof Swal !== 'undefined') {
                    Swal.fire(ok? 'Success':'Info', ok? 'Assignments saved.':'Some assignments may have failed.', ok? 'success':'warning');
                }
            }).catch(() => {
                if (typeof Swal !== 'undefined') Swal.fire('Error','Failed to save assignments','error');
            });
        });

        // Add new activity row handler
        $(document).off('click.addNewActivity').on('click.addNewActivity', '#addNewActivityBtn', function() {
            addNewActivityRow();
        });

        // Remove activity row handler
        $(document).off('click.removeNewActivity').on('click.removeNewActivity', '.remove-new-activity-btn', function() {
            $(this).closest('.new-activity-row').remove();
        });

        // Calculate timeframe when dates change
        $(document).off('change.calcTimeframe').on('change.calcTimeframe', '.new-activity-start-date, .new-activity-end-date', function() {
            const $row = $(this).closest('.new-activity-row');
            const startDate = $row.find('.new-activity-start-date').val();
            const endDate = $row.find('.new-activity-end-date').val();
            
            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                $row.find('.new-activity-timeframe').val(diffDays + ' Day(s)');
            } else {
                $row.find('.new-activity-timeframe').val('');
            }
        });
    };

    // Function to add new activity row in Edit Task modal
    function addNewActivityRow() {
        const index = $('#newActivitiesContainer .new-activity-row').length;
        const userOptions = allUsersData.map(user => 
            `<option value="${user.id}">${user.name}</option>`
        ).join('');

        const newRow = `
            <div class="border rounded p-3 mb-3 new-activity-row" data-index="${index}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>New Activity ${index + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger remove-new-activity-btn">
                        Remove
                    </button>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Activity Name <span class="text-danger">*</span></label>
                        <input type="text" name="new_activities[${index}][name]" class="form-control" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Activity Details / Description</label>
                        <textarea name="new_activities[${index}][description]" class="form-control" rows="3" placeholder="Enter activity description..."></textarea>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="new_activities[${index}][status]" class="form-select" required>
                            <option value="Not Started">Not Started</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Delayed">Delayed</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="new_activities[${index}][start_date]" class="form-control new-activity-start-date" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="new_activities[${index}][end_date]" class="form-control new-activity-end-date" required>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Timeframe</label>
                        <input type="text" name="new_activities[${index}][timeframe]" class="form-control new-activity-timeframe" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Priority</label>
                        <select name="new_activities[${index}][priority]" class="form-select">
                            <option value="Normal">Normal</option>
                            <option value="Low">Low</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Estimated Hours</label>
                        <input type="number" name="new_activities[${index}][estimated_hours]" class="form-control" min="0">
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label">Assigned Team</label>
                        <select name="new_activities[${index}][user_ids][]" class="form-select" multiple style="min-height: 120px;">
                            ${userOptions}
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple team members</small>
                    </div>
                </div>
            </div>
        `;

        $('#newActivitiesContainer').append(newRow);
    }

    window.exportTasks = function() {
        let csv = ['Task Name,Status,Priority,Team Leader,Progress,Due Date'];
        $('#tasksTable tbody tr').each(function() {
            const cells = $(this).find('td');
            if (cells.length > 1) {
                const row = [
                    $(cells[1]).find('strong').text(),
                    $(cells[4]).text().trim(),
                    $(cells[3]).text().trim(),
                    $(cells[5]).text().trim(),
                    $(cells[6]).find('small').text().trim(),
                    $(cells[7]).text().trim()
                ];
                csv.push(row.join(','));
            }
        });
        const content = 'data:text/csv;charset=utf-8,' + csv.join('\n');
        const a = document.createElement('a');
        a.href = encodeURI(content);
        a.download = 'tasks_export_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    };

    window.refreshTasks = function() {
        window.location.reload();
    };

    function renderTaskDetailsModal(task) {
        // Calculate statistics
        const totalActivities = task.activities?.length || 0;
        const completedActivities = task.activities?.filter(a => a.status === 'Completed').length || 0;
        const inProgressActivities = task.activities?.filter(a => a.status === 'In Progress').length || 0;
        const notStartedActivities = task.activities?.filter(a => a.status === 'Not Started').length || 0;
        const delayedActivities = task.activities?.filter(a => a.status === 'Delayed').length || 0;
        
        // Count reports properly - check both reports and progress_reports arrays
        const totalReports = task.activities?.reduce((sum, act) => {
            const reports = act.reports || act.progress_reports || [];
            return sum + (Array.isArray(reports) ? reports.length : 0);
        }, 0) || 0;
        const approvedReports = task.activities?.reduce((sum, act) => {
            const reports = act.reports || act.progress_reports || [];
            if (!Array.isArray(reports)) return sum;
            return sum + reports.filter(r => r && (r.status === 'Approved' || r.status === 'approved')).length;
        }, 0) || 0;
        const pendingReports = task.activities?.reduce((sum, act) => {
            const reports = act.reports || act.progress_reports || [];
            if (!Array.isArray(reports)) return sum;
            return sum + reports.filter(r => r && (r.status === 'Pending' || r.status === 'pending' || r.status === 'pending_approval')).length;
        }, 0) || 0;
        
        const totalComments = (task.comments?.length || 0) + (task.activities?.reduce((sum, act) => sum + (act.comments?.length || 0), 0) || 0);
        const totalAttachments = (task.attachments?.length || 0) + (task.activities?.reduce((sum, act) => sum + (act.attachments?.length || 0), 0) || 0);
        
        // Collect all issues and delays
        const allDelays = [];
        const allIssues = [];
        task.activities?.forEach(activity => {
            const reports = activity.reports || activity.progress_reports || [];
            const reportsArray = Array.isArray(reports) ? reports : [];
            reportsArray.forEach(report => {
                if (report.reason_if_delayed) {
                    allDelays.push({
                        activity: activity.name,
                        reporter: report.user?.name || 'Unknown',
                        date: report.report_date,
                        reason: report.reason_if_delayed,
                        status: report.status
                    });
                }
                if (report.completion_status === 'Delayed' || report.completion_status === 'Behind Schedule') {
                    allIssues.push({
                        activity: activity.name,
                        reporter: report.user?.name || 'Unknown',
                        date: report.report_date,
                        issue: report.work_description,
                        status: report.completion_status
                    });
                }
            });
        });

        // Overview Tab
        const overviewHtml = `
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-left-primary">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Progress</div>
                            <div class="h5 mb-0 font-weight-bold">${task.progress_percentage || 0}%</div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: ${task.progress_percentage || 0}%"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-info">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Activities</div>
                            <div class="h5 mb-0 font-weight-bold">${totalActivities}</div>
                            <small class="text-muted">${completedActivities} completed</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-success">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Reports</div>
                            <div class="h5 mb-0 font-weight-bold">${totalReports}</div>
                            <small class="text-muted">${approvedReports} approved</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-left-warning">
                        <div class="card-body">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Comments</div>
                            <div class="h5 mb-0 font-weight-bold">${totalComments}</div>
                            <small class="text-muted">${totalAttachments} attachments</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Task Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td width="40%"><strong>Status:</strong></td>
                                    <td><span class="badge bg-${task.status === 'completed' ? 'success' : task.status === 'in_progress' ? 'info' : task.status === 'delayed' ? 'danger' : 'warning'}">${task.status ? task.status.replace('_', ' ').toUpperCase() : 'N/A'}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Priority:</strong></td>
                                    <td><span class="badge bg-${task.priority === 'Critical' ? 'danger' : task.priority === 'High' ? 'warning' : task.priority === 'Low' ? 'secondary' : 'info'}">${task.priority || 'Normal'}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Category:</strong></td>
                                    <td>${task.category || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Team Leader:</strong></td>
                                    <td>${task.team_leader?.name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Created By:</strong></td>
                                    <td>${task.creator?.name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Start Date:</strong></td>
                                    <td>${task.start_date ? new Date(task.start_date).toLocaleDateString() : 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>End Date:</strong></td>
                                    <td>${task.end_date ? new Date(task.end_date).toLocaleDateString() : 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Timeframe:</strong></td>
                                    <td>${task.timeframe || 'N/A'}</td>
                                </tr>
                                ${task.budget ? `<tr><td><strong>Budget:</strong></td><td>${parseFloat(task.budget).toLocaleString()} TZS</td></tr>` : ''}
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Description</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">${task.description ? escapeHtml(task.description).replace(/\\n/g, '<br>') : '<em class="text-muted">No description provided</em>'}</p>
                            ${task.tags && task.tags.length > 0 ? `
                                <div class="mt-3">
                                    <strong>Tags:</strong><br>
                                    ${task.tags.map(tag => `<span class="badge bg-secondary me-1">${escapeHtml(tag)}</span>`).join('')}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Activities Tab
        const activitiesHtml = task.activities && task.activities.length > 0 
            ? task.activities.map((act, idx) => {
                const assignedUsers = (act.assigned_users || act.assignedUsers || []).map(u => u.name).join(', ') || 'Not assigned';
                const reports = act.reports || act.progress_reports || [];
                const reportsCount = Array.isArray(reports) ? reports.length : 0;
                const statusColor = act.status === 'Completed' ? 'success' : act.status === 'In Progress' ? 'info' : act.status === 'Delayed' ? 'danger' : 'warning';
                return `
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>${escapeHtml(act.name)}</h6>
                                <small class="text-muted">Activity #${idx + 1}</small>
                            </div>
                            <span class="badge bg-${statusColor}">${act.status || 'Not Started'}</span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <small class="text-muted"><strong>Duration:</strong> ${act.start_date ? new Date(act.start_date).toLocaleDateString() : 'N/A'} - ${act.end_date ? new Date(act.end_date).toLocaleDateString() : 'N/A'}</small><br>
                                    <small class="text-muted"><strong>Timeframe:</strong> ${act.timeframe || 'N/A'}</small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted"><strong>Priority:</strong> <span class="badge bg-${act.priority === 'Critical' ? 'danger' : act.priority === 'High' ? 'warning' : 'secondary'}">${act.priority || 'Normal'}</span></small><br>
                                    <small class="text-muted"><strong>Reports:</strong> ${reportsCount} submitted</small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted"><strong>Assigned Team:</strong> ${assignedUsers}</small>
                            </div>
                            <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-primary" onclick="viewActivityDetails(${act.id})">
                                    <i class="fas fa-eye me-1"></i>View Details
                            </button>
                                ${act.estimated_hours ? `<span class="badge bg-info align-self-center"><i class="fas fa-clock me-1"></i>${act.estimated_hours} hrs</span>` : ''}
                        </div>
                    </div>
                </div>
                `;
            }).join('')
            : '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No activities have been created for this task yet.</div>';

        // Reports Tab
        const reportsHtml = task.activities && task.activities.length > 0
            ? task.activities.map(act => {
                const reports = act.reports || act.progress_reports || [];
                const reportsArray = Array.isArray(reports) ? reports : [];
                if (reportsArray.length === 0) return '';
                
                return `
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>${escapeHtml(act.name)}</h6>
            </div>
                        <div class="card-body">
                            ${reportsArray.map(report => {
                                const statusColor = report.status === 'Approved' ? 'success' : report.status === 'Rejected' ? 'danger' : 'warning';
                                return `
                                    <div class="border rounded p-3 mb-2">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong>${report.user?.name || 'Unknown'}</strong>
                                                <small class="text-muted ms-2">${report.report_date ? new Date(report.report_date).toLocaleDateString() : 'N/A'}</small>
                                            </div>
                                            <span class="badge bg-${statusColor}">${report.status}</span>
                                        </div>
                                        <div class="mb-2">
                                            <strong>Work Done:</strong>
                                            <p class="mb-1">${escapeHtml(report.work_description || 'N/A').replace(/\\n/g, '<br>')}</p>
                                        </div>
                                        ${report.next_activities ? `
                                            <div class="mb-2">
                                                <strong>Next Activities:</strong>
                                                <p class="mb-1">${escapeHtml(report.next_activities).replace(/\\n/g, '<br>')}</p>
                                            </div>
                                        ` : ''}
                                        ${report.completion_status ? `
                                            <div class="mb-2">
                                                <strong>Completion Status:</strong> <span class="badge bg-info">${report.completion_status}</span>
                                            </div>
                                        ` : ''}
                                        ${report.reason_if_delayed ? `
                                            <div class="mb-2">
                                                <strong>Reason for Delay:</strong>
                                                <p class="mb-1 text-danger">${escapeHtml(report.reason_if_delayed).replace(/\\n/g, '<br>')}</p>
                                            </div>
                                        ` : ''}
                                        ${report.approver_comments ? `
                                            <div class="mb-2">
                                                <strong>Approver Comments:</strong>
                                                <p class="mb-1">${escapeHtml(report.approver_comments).replace(/\\n/g, '<br>')}</p>
                                            </div>
                                        ` : ''}
                                        ${report.approver ? `
                                            <div>
                                                <small class="text-muted">Approved by: ${report.approver.name} on ${report.approved_at ? new Date(report.approved_at).toLocaleDateString() : 'N/A'}</small>
                                            </div>
                                        ` : ''}
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            }).filter(html => html).join('') || '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No progress reports have been submitted yet.</div>'
            : '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No activities available.</div>';

        // Comments Tab
        const commentsHtml = `
            ${task.comments && task.comments.length > 0 ? `
                <h6 class="mb-3"><i class="fas fa-comments me-2"></i>Task Comments (${task.comments.length})</h6>
                ${task.comments.map(comment => `
                    <div class="card mb-2">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>${comment.user?.name || 'Unknown'}</strong>
                                    ${comment.is_internal ? '<span class="badge bg-warning ms-2">Internal</span>' : ''}
                                </div>
                                <small class="text-muted">${comment.created_at ? new Date(comment.created_at).toLocaleString() : 'N/A'}</small>
                            </div>
                            <p class="mb-0">${escapeHtml(comment.comment || '').replace(/\\n/g, '<br>')}</p>
                        </div>
                    </div>
                `).join('')}
            ` : ''}
            ${task.activities?.map(act => {
                if (!act.comments || act.comments.length === 0) return '';
                return `
                    <h6 class="mb-3 mt-4"><i class="fas fa-comments me-2"></i>${escapeHtml(act.name)} - Comments (${act.comments.length})</h6>
                    ${act.comments.map(comment => `
                        <div class="card mb-2">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>${comment.user?.name || 'Unknown'}</strong>
                                        ${comment.is_internal ? '<span class="badge bg-warning ms-2">Internal</span>' : ''}
                                    </div>
                                    <small class="text-muted">${comment.created_at ? new Date(comment.created_at).toLocaleString() : 'N/A'}</small>
                                </div>
                                <p class="mb-0">${escapeHtml(comment.comment || '').replace(/\\n/g, '<br>')}</p>
                            </div>
                        </div>
                    `).join('')}
                `;
            }).filter(html => html).join('') || ''}
            ${(!task.comments || task.comments.length === 0) && (!task.activities || task.activities.every(a => !a.comments || a.comments.length === 0)) 
                ? '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No comments have been added yet.</div>' 
                : ''}
        `;

        // Attachments Tab
        const attachmentsHtml = `
            ${task.attachments && task.attachments.length > 0 ? `
                <h6 class="mb-3"><i class="fas fa-paperclip me-2"></i>Task Attachments (${task.attachments.length})</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${task.attachments.map(att => `
                                <tr>
                                    <td><i class="fas fa-file me-2"></i>${escapeHtml(att.file_name || 'Unknown')}</td>
                                    <td>${att.user?.name || 'Unknown'}</td>
                                    <td>${att.created_at ? new Date(att.created_at).toLocaleDateString() : 'N/A'}</td>
                                    <td>${att.file_size ? (att.file_size / 1024).toFixed(2) + ' KB' : 'N/A'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : ''}
            ${task.activities?.map(act => {
                if (!act.attachments || act.attachments.length === 0) return '';
                return `
                    <h6 class="mb-3 mt-4"><i class="fas fa-paperclip me-2"></i>${escapeHtml(act.name)} - Attachments (${act.attachments.length})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>File Name</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th>Size</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${act.attachments.map(att => `
                                    <tr>
                                        <td><i class="fas fa-file me-2"></i>${escapeHtml(att.file_name || 'Unknown')}</td>
                                        <td>${att.user?.name || 'Unknown'}</td>
                                        <td>${att.created_at ? new Date(att.created_at).toLocaleDateString() : 'N/A'}</td>
                                        <td>${att.file_size ? (att.file_size / 1024).toFixed(2) + ' KB' : 'N/A'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }).filter(html => html).join('') || ''}
            ${(!task.attachments || task.attachments.length === 0) && (!task.activities || task.activities.every(a => !a.attachments || a.attachments.length === 0))
                ? '<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>No attachments have been uploaded yet.</div>'
                : ''}
        `;

        // Issues & Delays Tab
        const issuesDelaysHtml = `
            ${allDelays.length > 0 ? `
                <h6 class="mb-3"><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Reported Delays (${allDelays.length})</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Activity</th>
                                <th>Reported By</th>
                                <th>Date</th>
                                <th>Reason for Delay</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${allDelays.map(delay => `
                                <tr>
                                    <td>${escapeHtml(delay.activity)}</td>
                                    <td>${escapeHtml(delay.reporter)}</td>
                                    <td>${delay.date ? new Date(delay.date).toLocaleDateString() : 'N/A'}</td>
                                    <td>${escapeHtml(delay.reason).replace(/\\n/g, '<br>')}</td>
                                    <td><span class="badge bg-${delay.status === 'Approved' ? 'success' : delay.status === 'Rejected' ? 'danger' : 'warning'}">${delay.status}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : ''}
            ${allIssues.length > 0 ? `
                <h6 class="mb-3"><i class="fas fa-bug me-2 text-warning"></i>Issues & Problems (${allIssues.length})</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Activity</th>
                                <th>Reported By</th>
                                <th>Date</th>
                                <th>Issue Description</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${allIssues.map(issue => `
                                <tr>
                                    <td>${escapeHtml(issue.activity)}</td>
                                    <td>${escapeHtml(issue.reporter)}</td>
                                    <td>${issue.date ? new Date(issue.date).toLocaleDateString() : 'N/A'}</td>
                                    <td>${escapeHtml(issue.issue).substring(0, 100)}${issue.issue.length > 100 ? '...' : ''}</td>
                                    <td><span class="badge bg-info">${escapeHtml(issue.status)}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            ` : ''}
            ${allDelays.length === 0 && allIssues.length === 0
                ? '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>No issues or delays reported for this task.</div>'
                : ''}
        `;

        // Analytics Tab
        const analyticsHtml = `
            <div class="row mb-4">
                    <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Activity Status Distribution</h6>
                        </div>
                        <div class="card-body">
                            <canvas id="activityStatusChart" height="200"></canvas>
                            <div class="mt-3">
                                <small><span class="badge bg-success me-2">Completed: ${completedActivities}</span></small>
                                <small><span class="badge bg-info me-2">In Progress: ${inProgressActivities}</span></small>
                                <small><span class="badge bg-warning me-2">Not Started: ${notStartedActivities}</span></small>
                                <small><span class="badge bg-danger">Delayed: ${delayedActivities}</span></small>
                            </div>
                        </div>
                    </div>
                    </div>
                    <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Report Status</h6>
                    </div>
                        <div class="card-body">
                            <canvas id="reportStatusChart" height="200"></canvas>
                            <div class="mt-3">
                                <small><span class="badge bg-success me-2">Approved: ${approvedReports}</span></small>
                                <small><span class="badge bg-warning me-2">Pending: ${pendingReports}</span></small>
                                <small><span class="badge bg-danger">Rejected: ${totalReports - approvedReports - pendingReports}</span></small>
                </div>
                </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Task Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 text-center">
                                    <div class="h4 mb-0 text-primary">${totalActivities}</div>
                                    <small class="text-muted">Total Activities</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="h4 mb-0 text-success">${completedActivities}</div>
                                    <small class="text-muted">Completed</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="h4 mb-0 text-info">${totalReports}</div>
                                    <small class="text-muted">Total Reports</small>
                                </div>
                                <div class="col-md-3 text-center">
                                    <div class="h4 mb-0 text-warning">${totalComments}</div>
                                    <small class="text-muted">Total Comments</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#view-task-content').html(`
            <div class="modal-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <div>
                        <h5 class="modal-title mb-0"><i class="fas fa-tasks me-2"></i>${escapeHtml(task.name)}</h5>
                        <small class="text-white-50">Task ID: ${task.id}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="/modules/tasks/pdf?task_id=${task.id}" class="btn btn-light btn-sm" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>Download PDF
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview-tab" type="button">
                            <i class="fas fa-home me-1"></i>Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activities-tab" type="button">
                            <i class="fas fa-tasks me-1"></i>Activities <span class="badge bg-secondary">${totalActivities}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reports-tab" type="button">
                            <i class="fas fa-file-alt me-1"></i>Progress Reports <span class="badge bg-secondary">${totalReports}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#comments-tab" type="button">
                            <i class="fas fa-comments me-1"></i>Comments <span class="badge bg-secondary">${totalComments}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#attachments-tab" type="button">
                            <i class="fas fa-paperclip me-1"></i>Attachments <span class="badge bg-secondary">${totalAttachments}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#issues-tab" type="button">
                            <i class="fas fa-exclamation-triangle me-1"></i>Issues & Delays <span class="badge bg-danger">${allDelays.length + allIssues.length}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#analytics-tab" type="button">
                            <i class="fas fa-chart-line me-1"></i>Analytics
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="overview-tab" role="tabpanel">
                        ${overviewHtml}
                    </div>
                    <div class="tab-pane fade" id="activities-tab" role="tabpanel">
                    ${activitiesHtml}
                    </div>
                    <div class="tab-pane fade" id="reports-tab" role="tabpanel">
                        ${reportsHtml}
                    </div>
                    <div class="tab-pane fade" id="comments-tab" role="tabpanel">
                        ${commentsHtml}
                    </div>
                    <div class="tab-pane fade" id="attachments-tab" role="tabpanel">
                        ${attachmentsHtml}
                    </div>
                    <div class="tab-pane fade" id="issues-tab" role="tabpanel">
                        ${issuesDelaysHtml}
                    </div>
                    <div class="tab-pane fade" id="analytics-tab" role="tabpanel">
                        ${analyticsHtml}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                ${AppConfig.isManager ? `<button type="button" class="btn btn-primary" onclick="editTask(${task.id})">Edit Task</button>` : ''}
            </div>
        `);
        
        // Function to initialize task analytics charts
        function initTaskAnalyticsCharts() {
            console.log('Initializing task analytics charts...');
            console.log('Chart.js available:', typeof Chart !== 'undefined');
            
            if (typeof Chart === 'undefined') {
                console.error('Chart.js is not loaded! Attempting to load from CDN...');
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
                script.onload = function() {
                    console.log('Chart.js loaded from CDN, initializing charts...');
                    initTaskAnalyticsCharts();
                };
                script.onerror = function() {
                    console.error('Failed to load Chart.js from CDN');
                };
                document.head.appendChild(script);
                return;
            }
            
            // Destroy existing chart instances if they exist
            if (window.taskActivityStatusChart) {
                window.taskActivityStatusChart.destroy();
                window.taskActivityStatusChart = null;
            }
            if (window.taskReportStatusChart) {
                window.taskReportStatusChart.destroy();
                window.taskReportStatusChart = null;
            }
            
            // Activity Status Chart
            const ctx1 = document.getElementById('activityStatusChart');
            if (ctx1) {
                console.log('Rendering Activity Status Chart...');
                const completed = completedActivities || 0;
                const inProgress = inProgressActivities || 0;
                const notStarted = notStartedActivities || 0;
                const delayed = delayedActivities || 0;
                
                const activityData = [
                    isNaN(completed) || !isFinite(completed) ? 0 : completed,
                    isNaN(inProgress) || !isFinite(inProgress) ? 0 : inProgress,
                    isNaN(notStarted) || !isFinite(notStarted) ? 0 : notStarted,
                    isNaN(delayed) || !isFinite(delayed) ? 0 : delayed
                ];
                
                const hasData = activityData.some(val => val > 0);
                
                if (hasData) {
                    try {
                        window.taskActivityStatusChart = new Chart(ctx1, {
                            type: 'doughnut',
                            data: {
                                labels: ['Completed', 'In Progress', 'Not Started', 'Delayed'],
                                datasets: [{
                                    data: activityData,
                                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545']
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: true,
                                        position: 'bottom'
                                    },
                                    tooltip: {
                                        enabled: true
                                    }
                                }
                            }
                        });
                        console.log('✅ Activity Status Chart rendered successfully');
                    } catch (error) {
                        console.error('Error rendering Activity Status Chart:', error);
                        ctx1.parentElement.innerHTML = '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle me-2"></i>Error rendering chart: ' + error.message + '</div>';
                    }
                } else {
                    ctx1.parentElement.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle me-2"></i>No activities available to display chart.</div>';
                    console.log('No activity data to display');
                }
            } else {
                console.warn('Activity Status Chart canvas not found');
            }
            
            // Report Status Chart
            const ctx2 = document.getElementById('reportStatusChart');
            if (ctx2) {
                console.log('Rendering Report Status Chart...');
                const approved = approvedReports || 0;
                const pending = pendingReports || 0;
                const rejected = Math.max(0, (totalReports || 0) - approved - pending);
                
                const reportData = [
                    isNaN(approved) || !isFinite(approved) ? 0 : approved,
                    isNaN(pending) || !isFinite(pending) ? 0 : pending,
                    isNaN(rejected) || !isFinite(rejected) ? 0 : rejected
                ];
                
                const hasReportData = reportData.some(val => val > 0);
                
                if (hasReportData) {
                    try {
                        window.taskReportStatusChart = new Chart(ctx2, {
                            type: 'bar',
                            data: {
                                labels: ['Approved', 'Pending', 'Rejected'],
                                datasets: [{
                                    label: 'Reports',
                                    data: reportData,
                                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: { 
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        enabled: true
                                    }
                                }
                            }
                        });
                        console.log('✅ Report Status Chart rendered successfully');
                    } catch (error) {
                        console.error('Error rendering Report Status Chart:', error);
                        ctx2.parentElement.innerHTML = '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle me-2"></i>Error rendering chart: ' + error.message + '</div>';
                    }
                } else {
                    ctx2.parentElement.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle me-2"></i>No reports available to display chart.</div>';
                    console.log('No report data to display');
                }
            } else {
                console.warn('Report Status Chart canvas not found');
            }
            
            console.log('=== Task Analytics Charts Initialization Complete ===');
        }
        
        // Initialize charts when analytics tab is shown in the modal
        $(document).off('shown.bs.tab', '#viewTaskModal button[data-bs-target="#analytics-tab"]');
        $(document).on('shown.bs.tab', '#viewTaskModal button[data-bs-target="#analytics-tab"]', function() {
            console.log('Analytics tab shown in task modal, initializing charts...');
            setTimeout(initTaskAnalyticsCharts, 300);
        });
        
        // Also initialize when modal is shown and analytics tab is already active
        $('#viewTaskModal').off('shown.bs.modal');
        $('#viewTaskModal').on('shown.bs.modal', function() {
            setTimeout(() => {
                const analyticsTab = $('#viewTaskModal #analytics-tab');
                if (analyticsTab.length && (analyticsTab.hasClass('active') || analyticsTab.hasClass('show'))) {
                    console.log('Analytics tab already active in modal, initializing charts...');
                    setTimeout(initTaskAnalyticsCharts, 500);
                }
            }, 100);
        });
        
        // Initialize charts immediately if Chart.js is available and analytics tab is visible
        if (typeof Chart !== 'undefined') {
            setTimeout(() => {
                const analyticsTab = $('#viewTaskModal #analytics-tab');
                if (analyticsTab.length && (analyticsTab.hasClass('active') || analyticsTab.hasClass('show'))) {
                    initTaskAnalyticsCharts();
                }
            }, 500);
        }
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, m => map[m]);
    }

    window.viewActivityDetails = function(activityId) {
        TaskManager.currentActivityId = activityId; // Store current activity ID
        $('#activityDetailsModal').modal('show');
        $('#activity-details-title').text('Loading Activity...');
        $('#activity-details-body').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2">Loading activity details...</p></div>');

        const formData = new FormData();
        formData.append('action', 'task_get_report_details');
        formData.append('activity_id', activityId);

        TaskManager.apiCall(formData).done(response => {
            if (!response.success) {
                $('#activity-details-title').text('Error');
                $('#activity-details-body').html('<div class="alert alert-danger">'+(response.message || 'Failed to load activity')+'</div>');
                return;
            }

            const { activity, reports, is_assigned, has_pending_report } = response;
            $('#activity-details-title').text('Activity Details: ' + activity.name);

            // Performance
            let performanceHtml = '';
            if (activity.status === 'Completed' && activity.actual_end_date && activity.end_date) {
                const plannedEnd = new Date(activity.end_date);
                const actualEnd = new Date(activity.actual_end_date);
                plannedEnd.setHours(0,0,0,0); actualEnd.setHours(0,0,0,0);
                const diffDays = Math.ceil((actualEnd.getTime()-plannedEnd.getTime())/(1000*60*60*24));
                if (diffDays <= 0) {
                    performanceHtml = `<dt class="col-sm-4">Performance</dt><dd class="col-sm-8 text-success"><strong>Completed on time${diffDays<0? ' ('+Math.abs(diffDays)+' day(s) early)':''}</strong></dd>`;
                } else {
                    performanceHtml = `<dt class="col-sm-4">Performance</dt><dd class="col-sm-8 text-danger"><strong>Completed ${diffDays} day(s) late</strong></dd>`;
                }
            }

            // Assigned Users
            const assignedUsersHtml = (activity.assigned_users?.length)
                ? activity.assigned_users.map(u => `<li class="list-group-item p-1 border-0">${u.name}</li>`).join('')
                : '<li class="list-group-item p-1 border-0 text-muted">No staff assigned</li>';

            // Reports
            let reportsHtml = (reports && reports.length) ? reports.map(r => {
                let statusBadge = 'warning', statusText = 'Pending';
                if (r.status === 'Approved') { statusBadge = 'success'; statusText = 'Approved'; }
                if (r.status === 'Rejected') { statusBadge = 'danger'; statusText = 'Rejected'; }
                let actions = '';
                if (AppConfig.isManager && r.status === 'Pending') {
                    actions = `<div class="btn-group btn-group-sm">
                        <button class="btn btn-success btn-sm" onclick="approveReport(${r.id})" title="Approve">Approve</button>
                        <button class="btn btn-danger btn-sm" onclick="rejectReport(${r.id})" title="Reject">Reject</button>
                    </div>`;
                }
                const feedback = (r.approver_comments && (r.status==='Approved'||r.status==='Rejected'))
                    ? `<div class="mt-2 p-2 bg-light border rounded"><strong class="text-sm">Feedback:</strong><p class="mb-0 small">${(r.approver_comments||'').replace(/\n/g,'<br>')}</p></div>`
                    : '';
                const att = r.attachment_path ? `<p class="mb-1"><strong>Attachment:</strong> <a href="/storage/${r.attachment_path}" target="_blank">View File</a></p>` : '';
                return `<div class="card mb-2 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center p-2 bg-light">
                        <strong class="text-sm">Report by ${r.user?.name || ''} on ${new Date(r.report_date).toLocaleDateString()}</strong>
                        <div><span class="badge bg-${statusBadge} me-2">${statusText}</span>${actions}</div>
                    </div>
                    <div class="card-body p-2">
                        <strong>Work Done:</strong>
                        <p class="p-2 bg-white rounded border">${(r.work_description||'').replace(/\n/g,'<br>')}</p>
                        <strong>Next Plan:</strong>
                        <p>${r.next_activities || 'N/A'}</p>
                        ${att}
                        ${feedback}
                    </div>
                </div>`;
            }).join('') : '<p class="text-muted">No reports submitted yet.</p>';

            // Submit report form
            let reportFormHtml = '';
            if (is_assigned) {
                if (has_pending_report) {
                    reportFormHtml = `<div class="alert alert-warning mt-3"><i class="fas fa-exclamation-triangle"></i> You cannot submit a new report until your previous submission has been reviewed.</div>`;
                } else {
                    const today = new Date().toISOString().slice(0,10);
                    reportFormHtml = `<form id="submitReportForm" data-ajax-form="true" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="task_submit_report">
                        <input type="hidden" name="activity_id" value="${activity.id}">
                        <div class="mb-2">
                            <label class="form-label">Report Date</label>
                            <input type="date" name="report_date" class="form-control" value="${today}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Work Done</label>
                            <textarea name="work_description" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Next Plan</label>
                            <textarea name="next_activities" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Attachment</label>
                            <input type="file" name="attachment" class="form-control">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Status</label>
                            <select name="completion_status" id="completion_status" class="form-select" required>
                                <option value="In Progress">In Progress</option>
                                <option value="Completed">Completed</option>
                                <option value="Delayed">Delayed</option>
                            </select>
                        </div>
                        <div class="mb-2 d-none" id="reason-if-delayed-group">
                            <label class="form-label text-danger">Reason for Delay</label>
                            <textarea name="reason_if_delayed" class="form-control"></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Submit Report</button>
                        </div>
                    </form>`;
                }
            } else {
                reportFormHtml = `<div class="alert alert-info mt-3">You can only submit reports for activities to which you are assigned.</div>`;
            }

            const modalBodyHtml = `<div class="row">
                <div class="col-md-4">
                    <h6>Activity Details</h6>
                    <dl class="row mb-3">
                        <dt class="col-sm-5">Status</dt><dd class="col-sm-7"><span class="badge bg-info">${activity.status || 'Not Set'}</span></dd>
                        <dt class="col-sm-5">Start Date</dt><dd class="col-sm-7">${activity.start_date || 'N/A'}</dd>
                        <dt class="col-sm-5">End Date</dt><dd class="col-sm-7">${activity.end_date || 'N/A'}</dd>
                        <dt class="col-sm-5">Timeframe</dt><dd class="col-sm-7">${activity.timeframe || 'N/A'}</dd>
                        ${performanceHtml}
                    </dl>
                    <h6>Assigned Team</h6>
                    <ul class="list-group list-group-flush">${assignedUsersHtml}</ul>
                </div>
                <div class="col-md-8">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#history-pane" role="tab">Report History</a></li>
                        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#submit-pane" role="tab">Submit Report</a></li>
                        ${AppConfig.isManager ? '<li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#manage-pane" role="tab">Manage Assignment</a></li>' : ''}
                    </ul>
                    <div class="tab-content pt-3" style="max-height:60vh; overflow-y:auto;">
                        <div id="history-pane" class="tab-pane fade show active" role="tabpanel">${reportsHtml}</div>
                        <div id="submit-pane" class="tab-pane fade" role="tabpanel">${reportFormHtml}</div>
                        ${AppConfig.isManager ? `
                        <div id="manage-pane" class="tab-pane fade" role="tabpanel">
                            <form id="manageAssignForm" data-ajax-form="true">
                                <input type="hidden" name="action" value="task_update_activity">
                                <input type="hidden" name="activity_id" value="${activity.id}">
                                <input type="hidden" name="name" value="${(activity.name||'').replace(/"/g,'&quot;')}">
                                <div class="row g-2 mb-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" id="ma-start-date" value="${(activity.start_date||'').toString().slice(0,10)}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date" id="ma-end-date" value="${(activity.end_date||'').toString().slice(0,10)}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Timeframe</label>
                                        <input type="text" class="form-control" name="timeframe" id="ma-timeframe" value="${activity.timeframe||''}" readonly>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Assign Staff</label>
                                    <div class="border rounded p-2" style="max-height: 220px; overflow-y:auto;">
                                        ${allUsersData.map(u=>{
                                            const checked = (activity.assigned_users||[]).some(x=>x.id===u.id) ? 'checked' : '';
                                            return `<div class=\"form-check form-check-inline\">`+
                                                `<input class=\"form-check-input\" type=\"checkbox\" name=\"user_ids[]\" value=\"${u.id}\" id=\"ma-user-${u.id}\" ${checked}>`+
                                                `<label class=\"form-check-label\" for=\"ma-user-${u.id}\">${u.name}</label>`+
                                            `</div>`;
                                        }).join('')}
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Save Assignment</button>
                                </div>
                            </form>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>`;

            $('#activity-details-body').html(modalBodyHtml);

            // Toggle reason-if-delayed input
            $(document).off('change.activityStatus').on('change.activityStatus', '#completion_status', function(){
                const val = $(this).val();
                $('#reason-if-delayed-group').toggleClass('d-none', val !== 'Delayed');
            });

            // Manage assignment timeframe auto-calc
            function calcTF(startSel, endSel, targetSel) {
                const s = $(startSel).val();
                const e = $(endSel).val();
                if (!s || !e) { $(targetSel).val(''); return; }
                const start = new Date(s); const end = new Date(e);
                if (end < start) { $(targetSel).val('End date must be after start date.'); return; }
                let years = end.getFullYear()-start.getFullYear();
                let months = end.getMonth()-start.getMonth();
                let days = end.getDate()-start.getDate();
                if (days < 0) { months--; days += new Date(end.getFullYear(), end.getMonth(), 0).getDate(); }
                if (months < 0) { years--; months += 12; }
                const parts = [];
                if (years>0) parts.push(`${years} Year(s)`);
                if (months>0) parts.push(`${months} Month(s)`);
                if (days>0) parts.push(`${days} Day(s)`);
                $(targetSel).val(parts.join(', ') || 'Same Day');
            }
            $(document).off('change.maDates').on('change.maDates', '#ma-start-date,#ma-end-date', function(){ calcTF('#ma-start-date','#ma-end-date','#ma-timeframe'); });

            // Save assignment inline
            $(document).off('submit.manageAssignForm').on('submit.manageAssignForm', '#manageAssignForm', function(ev){
                ev.preventDefault();
                const form = this;
                const fd = new FormData(form);
                TaskManager.apiCall(fd).done(r => {
                    if (r && r.success) {
                        if (typeof Swal !== 'undefined') Swal.fire('Success','Assignment saved','success');
                        // refresh modal data
                        viewActivityDetails(activity.id);
                    } else {
                        if (typeof Swal !== 'undefined') Swal.fire('Error', (r&&r.message)||'Failed', 'error');
                    }
                }).fail(() => { if (typeof Swal !== 'undefined') Swal.fire('Error','Network error','error'); });
            });
        }).fail(() => {
            $('#activity-details-title').text('Error');
            $('#activity-details-body').html('<div class="alert alert-danger">Failed to load activity details.</div>');
        });
    };

    window.approveReport = function(reportId){
        console.log('approveReport called with ID:', reportId);
        
        if (!reportId) {
            alert('Error: Report ID is missing');
            return;
        }

        const handleApproval = (comments) => {
            console.log('Submitting approval for report:', reportId);
            const fd = new FormData(); 
            fd.append('action','task_approve_report'); 
            fd.append('report_id', reportId); 
            fd.append('comments', comments || '');
            
            return TaskManager.apiCall(fd)
                .done(r => {
                    console.log('Approval response:', r);
                    if(r && r.success){
                        alert(r.message || 'Report approved successfully!');
                        // Reload activity details if modal is open
                        if (TaskManager.currentActivityId) {
                            TaskManager.loadActivityDetails(TaskManager.currentActivityId);
                        } else {
                            location.reload();
                        }
                    } else {
                        alert(r.message || 'Failed to approve report');
                    }
                })
                .fail(err => {
                    console.error('Approval error:', err);
                    const errorMsg = err.responseJSON?.message || err.message || 'Request failed. Please try again.';
                    alert('Error: ' + errorMsg);
                });
        };

        if (typeof Swal !== 'undefined' && Swal && Swal.fire) {
            Swal.fire({
                title: 'Approve Report', 
                input: 'textarea', 
                inputLabel: 'Feedback / Comments',
                inputPlaceholder: 'Optional comments...', 
                showCancelButton: true, 
                confirmButtonText: 'Approve',
                confirmButtonColor: '#28a745', 
                showLoaderOnConfirm: true,
                preConfirm: (comments) => {
                    const fd = new FormData();
                    fd.append('action','task_approve_report'); 
                    fd.append('report_id', reportId); 
                    fd.append('comments', comments||'');
                    return TaskManager.apiCall(fd)
                        .then(r=>{ 
                            if(!r || !r.success) throw new Error(r?.message||'Failed to approve report'); 
                            return r; 
                        })
                        .catch(err=>{ 
                            const errorMsg = err.responseJSON?.message || err.message || 'Request failed';
                            Swal.showValidationMessage(errorMsg);
                            return false;
                        });
                }, 
                allowOutsideClick: () => !Swal.isLoading()
            }).then((res)=>{ 
                if(res.isConfirmed && res.value){ 
                    Swal.fire('Success!', res.value.message || 'Report approved successfully.', 'success').then(() => {
                        if (TaskManager.currentActivityId) {
                            TaskManager.loadActivityDetails(TaskManager.currentActivityId);
                        } else {
                            location.reload();
                        }
                    });
                }
            });
        } else {
            // Fallback: use regular prompt and confirm
            const comments = prompt('Enter optional feedback/comments (or leave empty):');
            if (comments !== null) {
                handleApproval(comments);
            }
        }
    };

    window.rejectReport = function(reportId){
        if (typeof Swal === 'undefined') return;
        Swal.fire({
            title: 'Reject Report', input: 'textarea', inputLabel: 'Reason (required)', inputPlaceholder: 'Reason for rejection...',
            showCancelButton: true, confirmButtonText: 'Reject', confirmButtonColor: '#dc3545', showLoaderOnConfirm: true,
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'A comment is required for rejection.';
                }
            },
            preConfirm: (comments) => {
                if (!comments || !comments.trim()) { 
                    Swal.showValidationMessage('A comment is required for rejection.'); 
                    return false; 
                }
                const fd = new FormData(); 
                fd.append('action','task_reject_report'); 
                fd.append('report_id', reportId); 
                fd.append('comments', comments);
                return TaskManager.apiCall(fd)
                    .then(r=>{ 
                        if(!r.success) throw new Error(r.message||'Failed to reject report'); 
                        return r; 
                    })
                    .catch(err=>{ 
                        const errorMsg = err.responseJSON?.message || err.message || 'Request failed';
                        Swal.showValidationMessage(errorMsg);
                        return false;
                    });
            }, 
            allowOutsideClick: () => !Swal.isLoading()
        }).then((res)=>{ 
            if(res.isConfirmed && res.value){ 
                Swal.fire('Success!', res.value.message || 'Report rejected successfully.', 'success').then(() => {
                    // Reload activity details if modal is open
                    if (TaskManager.currentActivityId) {
                        TaskManager.loadActivityDetails(TaskManager.currentActivityId);
                    } else {
                        location.reload();
                    }
                });
            }
        });
    };

    // Initialize Dashboard Charts
    function initDashboardCharts() {
        console.log('=== Initializing Dashboard Charts ===');
        console.log('Chart.js available:', typeof Chart !== 'undefined');
        
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded! Attempting to load from CDN...');
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            script.onload = function() {
                console.log('Chart.js loaded from CDN, initializing dashboard charts...');
                initDashboardCharts();
            };
            script.onerror = function() {
                console.error('Failed to load Chart.js from CDN');
            };
            document.head.appendChild(script);
            return;
        }

        // Destroy existing chart instances if they exist
        if (window.taskProgressChartInstance) {
            window.taskProgressChartInstance.destroy();
            window.taskProgressChartInstance = null;
        }
        if (window.statusDistributionChartInstance) {
            window.statusDistributionChartInstance.destroy();
            window.statusDistributionChartInstance = null;
        }

        // Task Progress Chart
        const progressCtx = document.getElementById('taskProgressChart');
        if (progressCtx) {
            console.log('Rendering Task Progress Chart...');
            const tasks = mainTasksData || [];
            const labels = tasks.map(t => t.name).slice(0, 10);
            const progressData = tasks.map(t => t.progress_percentage || 0).slice(0, 10);
            
            if (labels.length > 0 && progressData.length > 0) {
                try {
                    window.taskProgressChartInstance = new Chart(progressCtx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Progress %',
                                data: progressData,
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.1,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: true, position: 'top' },
                                tooltip: { enabled: true }
                            },
                            scales: {
                                y: { 
                                    beginAtZero: true, 
                                    max: 100,
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                    console.log('✅ Task Progress Chart rendered successfully');
                } catch (error) {
                    console.error('Error rendering Task Progress Chart:', error);
                    progressCtx.parentElement.innerHTML = '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle me-2"></i>Error rendering chart: ' + error.message + '</div>';
                }
            } else {
                progressCtx.parentElement.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle me-2"></i>No task data available to display chart.</div>';
                console.log('No task data to display for progress chart');
            }
        } else {
            console.warn('Task Progress Chart canvas not found');
        }

        // Status Distribution Chart
        const statusCtx = document.getElementById('statusDistributionChart');
        if (statusCtx) {
            console.log('Rendering Status Distribution Chart...');
            const tasks = mainTasksData || [];
            const statusCounts = {
                'planning': tasks.filter(t => t.status === 'planning').length,
                'in_progress': tasks.filter(t => t.status === 'in_progress').length,
                'completed': tasks.filter(t => t.status === 'completed').length,
                'delayed': tasks.filter(t => t.status === 'delayed').length
            };
            
            const statusData = [statusCounts.planning, statusCounts.in_progress, statusCounts.completed, statusCounts.delayed];
            const hasData = statusData.some(val => val > 0);
            
            if (hasData) {
                try {
                    window.statusDistributionChartInstance = new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Planning', 'In Progress', 'Completed', 'Delayed'],
                            datasets: [{
                                data: statusData,
                                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { 
                                    position: 'bottom',
                                    display: true
                                },
                                tooltip: { enabled: true }
                            }
                        }
                    });
                    console.log('✅ Status Distribution Chart rendered successfully');
                } catch (error) {
                    console.error('Error rendering Status Distribution Chart:', error);
                    statusCtx.parentElement.innerHTML = '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-triangle me-2"></i>Error rendering chart: ' + error.message + '</div>';
                }
            } else {
                statusCtx.parentElement.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle me-2"></i>No task data available to display chart.</div>';
                console.log('No task data to display for status chart');
            }
        } else {
            console.warn('Status Distribution Chart canvas not found');
        }
        
        console.log('=== Dashboard Charts Initialization Complete ===');
    }

    // Initialize Analytics Charts
    function initAnalyticsCharts() {
        console.log('=== Initializing Analytics Charts ===');
        console.log('Chart.js available:', typeof Chart !== 'undefined');
        console.log('Tasks data available:', typeof mainTasksData !== 'undefined', mainTasksData?.length || 0);
        
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded! Attempting to load from CDN...');
            // Try loading Chart.js from CDN
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
            script.onload = function() {
                console.log('Chart.js loaded from CDN, initializing charts...');
                initAnalyticsCharts();
            };
            script.onerror = function() {
                console.error('Failed to load Chart.js from CDN');
                const errorMsg = document.createElement('div');
                errorMsg.className = 'alert alert-danger';
                errorMsg.innerHTML = '<strong>Error:</strong> Chart library (Chart.js) could not be loaded. Please refresh the page.';
                const analyticsView = document.getElementById('analytics-view');
                if (analyticsView) {
                    analyticsView.insertBefore(errorMsg, analyticsView.firstChild);
                }
            };
            document.head.appendChild(script);
            return;
        }
        
        const tasks = mainTasksData || [];
        console.log('Processing', tasks.length, 'tasks for charts');

        // Priority Chart
        const priorityCtx = document.getElementById('priorityChart');
        if (priorityCtx) {
            console.log('Rendering Priority Chart...');
            // Destroy existing chart if any
            if (window.priorityChartInstance) {
                window.priorityChartInstance.destroy();
            }
            
            const priorityCounts = {
                'Low': tasks.filter(t => t.priority === 'Low').length,
                'Normal': tasks.filter(t => t.priority === 'Normal' || !t.priority).length,
                'High': tasks.filter(t => t.priority === 'High').length,
                'Critical': tasks.filter(t => t.priority === 'Critical').length
            };
            
            console.log('Priority counts:', priorityCounts);
            
            window.priorityChartInstance = new Chart(priorityCtx, {
                type: 'bar',
                data: {
                    labels: ['Low', 'Normal', 'High', 'Critical'],
                    datasets: [{
                        label: 'Tasks',
                        data: [priorityCounts.Low, priorityCounts.Normal, priorityCounts.High, priorityCounts.Critical],
                        backgroundColor: ['#6c757d', '#17a2b8', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Leader Chart
        const leaderCtx = document.getElementById('leaderChart');
        if (leaderCtx) {
            console.log('Rendering Leader Chart...');
            // Destroy existing chart if any
            if (window.leaderChartInstance) {
                window.leaderChartInstance.destroy();
            }
            
            const leaderCounts = {};
            tasks.forEach(t => {
                const leaderName = t.team_leader?.name || t.team_leader_name || 'Unassigned';
                leaderCounts[leaderName] = (leaderCounts[leaderName] || 0) + 1;
            });
            
            console.log('Leader counts:', leaderCounts);
            
            window.leaderChartInstance = new Chart(leaderCtx, {
                type: 'bar',
                data: {
                    labels: Object.keys(leaderCounts),
                    datasets: [{
                        label: 'Tasks',
                        data: Object.values(leaderCounts),
                        backgroundColor: '#007bff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        // Completion Trend Chart
        const trendCtx = document.getElementById('completionTrendChart');
        if (trendCtx) {
            console.log('Rendering Completion Trend Chart...');
            // Destroy existing chart if any
            if (window.trendChartInstance) {
                window.trendChartInstance.destroy();
            }
            
            // Group by month - use completed_at if available, otherwise created_at
            const monthlyData = {};
            tasks.forEach(t => {
                const dateStr = t.completed_at || t.created_at;
                if (dateStr) {
                    try {
                        const date = new Date(dateStr);
                        const month = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    monthlyData[month] = (monthlyData[month] || 0) + 1;
                    } catch (e) {
                        console.warn('Invalid date:', dateStr);
                    }
                }
            });
            
            // Sort months chronologically
            const sortedMonths = Object.keys(monthlyData).sort((a, b) => {
                return new Date(a) - new Date(b);
            });
            
            console.log('Monthly trend data:', monthlyData);
            
            window.trendChartInstance = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: sortedMonths.length > 0 ? sortedMonths : ['No Data'],
                    datasets: [{
                        label: 'Tasks Completed',
                        data: sortedMonths.length > 0 ? sortedMonths.map(m => monthlyData[m]) : [0],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: true }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
            console.log('Completion Trend Chart rendered');
        } else {
            console.warn('Completion Trend Chart element not found');
        }
        
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            console.log('Rendering Category Chart...');
            if (window.categoryChartInstance) {
                window.categoryChartInstance.destroy();
            }
            
            const tasks = mainTasksData || [];
            const categoryCounts = {};
            tasks.forEach(t => {
                const cat = t.category || 'Uncategorized';
                categoryCounts[cat] = (categoryCounts[cat] || 0) + 1;
            });
            
            const categoryLabels = Object.keys(categoryCounts);
            const categoryValues = Object.values(categoryCounts);
            
            console.log('Category counts:', categoryCounts);
            
            window.categoryChartInstance = new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: categoryLabels.length > 0 ? categoryLabels : ['No Categories'],
                    datasets: [{
                        label: 'Tasks',
                        data: categoryValues.length > 0 ? categoryValues : [0],
                        backgroundColor: '#6c757d'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
            console.log('✅ Category Chart rendered');
        }

        // Progress Distribution Chart
        const progressDistCtx = document.getElementById('progressDistributionChart');
        if (progressDistCtx) {
            console.log('Rendering Progress Distribution Chart...');
            if (window.progressDistChartInstance) {
                window.progressDistChartInstance.destroy();
            }
            
            const tasks = mainTasksData || [];
            const progressBuckets = {
                '0-25%': tasks.filter(t => (t.progress_percentage || 0) >= 0 && (t.progress_percentage || 0) < 25).length,
                '25-50%': tasks.filter(t => (t.progress_percentage || 0) >= 25 && (t.progress_percentage || 0) < 50).length,
                '50-75%': tasks.filter(t => (t.progress_percentage || 0) >= 50 && (t.progress_percentage || 0) < 75).length,
                '75-100%': tasks.filter(t => (t.progress_percentage || 0) >= 75 && (t.progress_percentage || 0) <= 100).length
            };
            
            window.progressDistChartInstance = new Chart(progressDistCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(progressBuckets),
                    datasets: [{
                        data: Object.values(progressBuckets),
                        backgroundColor: ['#dc3545', '#ffc107', '#0dcaf0', '#198754']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
            console.log('✅ Progress Distribution Chart rendered');
        }

        // Timeline Chart (Created vs Completed)
        const timelineCtx = document.getElementById('timelineChart');
        if (timelineCtx) {
            console.log('Rendering Timeline Chart...');
            if (window.timelineChartInstance) {
                window.timelineChartInstance.destroy();
            }
            
            const tasks = mainTasksData || [];
            const monthlyData = {};
            tasks.forEach(t => {
                if (t.created_at) {
                    const month = new Date(t.created_at).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    if (!monthlyData[month]) {
                        monthlyData[month] = { created: 0, completed: 0 };
                    }
                    monthlyData[month].created++;
                }
                if (t.status === 'completed' && t.end_date) {
                    const month = new Date(t.end_date).toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                    if (!monthlyData[month]) {
                        monthlyData[month] = { created: 0, completed: 0 };
                    }
                    monthlyData[month].completed++;
                }
            });
            
            const sortedMonths = Object.keys(monthlyData).sort((a, b) => new Date(a) - new Date(b));
            const createdData = sortedMonths.map(m => monthlyData[m].created);
            const completedData = sortedMonths.map(m => monthlyData[m].completed);
            
            window.timelineChartInstance = new Chart(timelineCtx, {
                type: 'line',
                data: {
                    labels: sortedMonths.length > 0 ? sortedMonths : ['No Data'],
                    datasets: [{
                        label: 'Created',
                        data: createdData.length > 0 ? createdData : [0],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Completed',
                        data: completedData.length > 0 ? completedData : [0],
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
            console.log('✅ Timeline Chart rendered');
        }
        
        console.log('=== Analytics Charts Initialization Complete ===');
        console.log('Priority Chart:', window.priorityChartInstance ? 'Rendered' : 'Failed');
        console.log('Leader Chart:', window.leaderChartInstance ? 'Rendered' : 'Failed');
        console.log('Trend Chart:', window.trendChartInstance ? 'Rendered' : 'Failed');
        console.log('Category Chart:', window.categoryChartInstance ? 'Rendered' : 'Failed');
        console.log('Progress Distribution Chart:', window.progressDistChartInstance ? 'Rendered' : 'Failed');
        console.log('Timeline Chart:', window.timelineChartInstance ? 'Rendered' : 'Failed');
    }

    // Refresh Task Analytics
    function refreshTaskAnalytics() {
        console.log('Refreshing task analytics...');
        const dateFrom = document.getElementById('analyticsDateFrom')?.value;
        const dateTo = document.getElementById('analyticsDateTo')?.value;
        const statusFilter = document.getElementById('analyticsStatusFilter')?.value;
        
        // Filter tasks based on date and status
        let filteredTasks = mainTasksData || [];
        
        if (dateFrom) {
            filteredTasks = filteredTasks.filter(t => {
                if (!t.created_at) return false;
                const taskDate = new Date(t.created_at);
                return taskDate >= new Date(dateFrom);
            });
        }
        
        if (dateTo) {
            filteredTasks = filteredTasks.filter(t => {
                if (!t.created_at) return false;
                const taskDate = new Date(t.created_at);
                return taskDate <= new Date(dateTo);
            });
        }
        
        if (statusFilter) {
            filteredTasks = filteredTasks.filter(t => t.status === statusFilter);
        }
        
        console.log('Filtered tasks:', filteredTasks.length);
        
        // Update statistics cards
        const totalTasks = filteredTasks.length;
        const completedTasks = filteredTasks.filter(t => t.status === 'completed').length;
        const inProgressTasks = filteredTasks.filter(t => t.status === 'in_progress').length;
        const completionRate = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;
        
        // Update stat cards
        const statsCards = document.getElementById('taskStatsCards');
        if (statsCards) {
            statsCards.innerHTML = `
                <div class="col-md-3 mb-3">
                    <div class="card stat-card primary shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Tasks</h6>
                                    <h3 class="mb-0">${totalTasks}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-tasks fa-2x text-primary" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card success shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Completed</h6>
                                    <h3 class="mb-0">${completedTasks}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-check-circle fa-2x text-success" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card info shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">In Progress</h6>
                                    <h3 class="mb-0">${inProgressTasks}</h3>
                                </div>
                                <div>
                                    <i class="fas fa-spinner fa-2x text-info" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card warning shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Completion Rate</h6>
                                    <h3 class="mb-0">${completionRate}%</h3>
                                </div>
                                <div>
                                    <i class="fas fa-chart-line fa-2x text-warning" style="opacity: 0.3;"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Re-render charts with filtered data
        setTimeout(() => {
            initAnalyticsCharts();
        }, 300);
    }

    // Export Task Analytics to PDF
    function exportTaskAnalytics(format) {
        console.log('Exporting task analytics to', format);
        const dateFrom = document.getElementById('analyticsDateFrom')?.value || '';
        const dateTo = document.getElementById('analyticsDateTo')?.value || '';
        const statusFilter = document.getElementById('analyticsStatusFilter')?.value || '';
        
        if (format === 'pdf') {
            const url = `/modules/tasks/analytics-pdf?date_from=${dateFrom}&date_to=${dateTo}&status=${statusFilter}`;
            window.open(url, '_blank');
        } else {
            console.warn('Export format not supported:', format);
        }
    }

    // Advanced Calendar View Functions - Tanzania Timezone Support
    let currentCalendarDate = new Date();
    let currentCalendarView = 'month'; // 'month', 'week', 'day'
    let calendarFilters = {
        status: '',
        priority: '',
        category: '',
        search: ''
    };
    let showHolidays = true;
    
    // Tanzania Public Holidays (Fixed dates and calculated dates)
    const tanzaniaHolidays = {
        // Fixed dates
        fixed: [
            { month: 1, day: 1, name: 'New Year\'s Day' },
            { month: 1, day: 12, name: 'Zanzibar Revolution Day' },
            { month: 2, day: 5, name: 'Chama cha Mapinduzi Day' },
            { month: 3, day: 3, name: 'Karume Day' },
            { month: 4, day: 7, name: 'Sheikh Abeid Amani Karume Day' },
            { month: 4, day: 26, name: 'Union Day' },
            { month: 5, day: 1, name: 'Workers\' Day' },
            { month: 7, day: 7, name: 'Saba Saba Day' },
            { month: 8, day: 8, name: 'Nane Nane Day (Farmers\' Day)' },
            { month: 9, day: 14, name: 'Mwalimu Nyerere Day' },
            { month: 10, day: 14, name: 'Nyerere Day' },
            { month: 12, day: 9, name: 'Independence Day' },
            { month: 12, day: 25, name: 'Christmas Day' },
            { month: 12, day: 26, name: 'Boxing Day' }
        ],
        // Calculated dates (Easter-based holidays)
        calculated: function(year) {
            // Good Friday and Easter Monday (approximate calculation)
            const easter = calculateEaster(year);
            const goodFriday = new Date(easter);
            goodFriday.setDate(easter.getDate() - 2);
            const easterMonday = new Date(easter);
            easterMonday.setDate(easter.getDate() + 1);
            
            return [
                { date: goodFriday, name: 'Good Friday' },
                { date: easterMonday, name: 'Easter Monday' }
            ];
        }
    };
    
    // Calculate Easter (simplified algorithm)
    function calculateEaster(year) {
        const a = year % 19;
        const b = Math.floor(year / 100);
        const c = year % 100;
        const d = Math.floor(b / 4);
        const e = b % 4;
        const f = Math.floor((b + 8) / 25);
        const g = Math.floor((b - f + 1) / 3);
        const h = (19 * a + b - d - g + 15) % 30;
        const i = Math.floor(c / 4);
        const k = c % 4;
        const l = (32 + 2 * e + 2 * i - h - k) % 7;
        const m = Math.floor((a + 11 * h + 22 * l) / 451);
        const month = Math.floor((h + l - 7 * m + 114) / 31);
        const day = ((h + l - 7 * m + 114) % 31) + 1;
        return new Date(year, month - 1, day);
    }
    
    // Get Tanzania holidays for a specific date
    function getTanzaniaHoliday(date) {
        if (!showHolidays) return null;
        
        const year = date.getFullYear();
        const month = date.getMonth() + 1;
        const day = date.getDate();
        
        // Check fixed holidays
        const fixedHoliday = tanzaniaHolidays.fixed.find(h => h.month === month && h.day === day);
        if (fixedHoliday) {
            return fixedHoliday.name;
        }
        
        // Check calculated holidays
        const calculatedHolidays = tanzaniaHolidays.calculated(year);
        for (let holiday of calculatedHolidays) {
            if (holiday.date.getFullYear() === year &&
                holiday.date.getMonth() === date.getMonth() &&
                holiday.date.getDate() === date.getDate()) {
                return holiday.name;
            }
        }
        
        return null;
    }
    
    // Convert date to Tanzania timezone
    function toTanzaniaDate(date) {
        // Create date in Tanzania timezone (GMT+3)
        const tzDate = new Date(date.toLocaleString('en-US', { timeZone: 'Africa/Dar_es_Salaam' }));
        return tzDate;
    }
    
    // Format date for Tanzania locale
    function formatTanzaniaDate(date, options = {}) {
        return date.toLocaleDateString('en-TZ', {
            timeZone: 'Africa/Dar_es_Salaam',
            ...options
        });
    }
    
    // Filter tasks based on current filters
    function getFilteredTasks() {
        let tasks = mainTasksData || [];
        
        if (calendarFilters.status) {
            tasks = tasks.filter(t => t.status === calendarFilters.status);
        }
        
        if (calendarFilters.priority) {
            tasks = tasks.filter(t => t.priority === calendarFilters.priority);
        }
        
        if (calendarFilters.category) {
            tasks = tasks.filter(t => (t.category || '') === calendarFilters.category);
        }
        
        if (calendarFilters.search) {
            const searchLower = calendarFilters.search.toLowerCase();
            tasks = tasks.filter(t => 
                (t.name || '').toLowerCase().includes(searchLower) ||
                (t.description || '').toLowerCase().includes(searchLower) ||
                (t.category || '').toLowerCase().includes(searchLower)
            );
        }
        
        return tasks;
    }
    
    // Render Month View
    function renderMonthView() {
        const calendarEl = document.getElementById('task-calendar');
        const monthYearEl = document.getElementById('calendar-month-year');
        if (!calendarEl) return;

        const tasks = getFilteredTasks();
        const year = currentCalendarDate.getFullYear();
        const month = currentCalendarDate.getMonth();
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Update month/year display with Tanzania formatting
        if (monthYearEl) {
            monthYearEl.textContent = formatTanzaniaDate(currentCalendarDate, { month: 'long', year: 'numeric' });
        }
        
        // Get first day of month and days in month
        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        
        let calendarHTML = '<div class="calendar-weekdays">';
        const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        weekdays.forEach(day => {
            calendarHTML += '<div class="calendar-weekday">' + day + '</div>';
        });
        calendarHTML += '</div><div class="calendar-days">';
        
        // Empty cells for days before month starts
        for (let i = 0; i < firstDay; i++) {
            calendarHTML += '<div class="calendar-day empty"></div>';
        }
        
            // Days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(year, month, day);
            date.setHours(0, 0, 0, 0);
            const isToday = date.getTime() === today.getTime();
            const holiday = getTanzaniaHoliday(date);
                
                // Find tasks for this day
                const dayTasks = tasks.filter(t => {
                    if (!t.start_date || !t.end_date) return false;
                    const start = new Date(t.start_date);
                start.setHours(0, 0, 0, 0);
                    const end = new Date(t.end_date);
                end.setHours(23, 59, 59, 999);
                    return date >= start && date <= end;
                });
                
                const dateStr = date.toISOString().split('T')[0];
            const dayClasses = ['calendar-day', 'clickable-day'];
            if (isToday) dayClasses.push('today');
            if (holiday) dayClasses.push('holiday');
            
            calendarHTML += `<div class="${dayClasses.join(' ')}" onclick="selectDateForPlanning('${dateStr}')" data-date="${dateStr}">`;
            calendarHTML += '<div class="day-header">';
                calendarHTML += '<div class="day-number">' + day + '</div>';
            if (holiday) {
                calendarHTML += '<div class="holiday-badge" title="' + holiday + '"><i class="fas fa-star"></i></div>';
            }
            calendarHTML += '</div>';
                
                if (dayTasks.length > 0) {
                    calendarHTML += '<div class="day-tasks" onclick="event.stopPropagation();">';
                dayTasks.slice(0, 3).forEach(task => {
                    const statusColor = task.status === 'completed' ? 'success' : 
                                      (task.status === 'in_progress' ? 'info' : 
                                      (task.status === 'delayed' ? 'danger' : 'warning'));
                    const priorityClass = task.priority === 'Critical' ? 'critical' : 
                                        (task.priority === 'High' ? 'high' : '');
                    const taskName = task.name.length > 15 ? task.name.substring(0, 15) + '...' : task.name;
                    calendarHTML += `<div class="task-badge status-${statusColor} ${priorityClass}" onclick="viewTaskDetails(${task.id})" title="${escapeHtml(task.name)}">`;
                    calendarHTML += '<span class="task-name">' + escapeHtml(taskName) + '</span>';
                    if (task.priority === 'Critical' || task.priority === 'High') {
                        calendarHTML += '<i class="fas fa-exclamation-circle ms-1"></i>';
                    }
                    calendarHTML += '</div>';
                });
                if (dayTasks.length > 3) {
                    calendarHTML += '<div class="task-badge more" title="' + dayTasks.length + ' tasks">+' + (dayTasks.length - 3) + '</div>';
                    }
                    calendarHTML += '</div>';
                }
                
            // Add "Add" button
                calendarHTML += '<div class="add-activity-section" onclick="event.stopPropagation();">';
                calendarHTML += '<button type="button" class="btn-add-activity" onclick="planTaskForDate(\'' + dateStr + '\')" title="Plan Task/Activity">';
            calendarHTML += (isToday ? '<i class="fas fa-plus me-1"></i>Add' : '<i class="fas fa-plus"></i>');
                calendarHTML += '</button>';
                calendarHTML += '</div>';
                
                calendarHTML += '</div>';
            }
        
        calendarHTML += '</div>';
        calendarEl.innerHTML = calendarHTML;
    }
    
    // Render Week View
    function renderWeekView() {
        const calendarEl = document.getElementById('task-calendar');
        const monthYearEl = document.getElementById('calendar-month-year');
        if (!calendarEl) return;

        const tasks = getFilteredTasks();
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Get start of week (Sunday)
        const weekStart = new Date(currentCalendarDate);
        weekStart.setDate(currentCalendarDate.getDate() - currentCalendarDate.getDay());
        weekStart.setHours(0, 0, 0, 0);
        
        // Update display
        if (monthYearEl) {
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            monthYearEl.textContent = formatTanzaniaDate(weekStart, { month: 'short', day: 'numeric' }) + 
                                     ' - ' + formatTanzaniaDate(weekEnd, { month: 'short', day: 'numeric', year: 'numeric' });
        }
        
        let calendarHTML = '<div class="calendar-week-view">';
        calendarHTML += '<div class="week-header"><div class="time-column"></div>';
        
        const weekdays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        for (let i = 0; i < 7; i++) {
            const date = new Date(weekStart);
            date.setDate(weekStart.getDate() + i);
            const isToday = date.getTime() === today.getTime();
            const holiday = getTanzaniaHoliday(date);
            const dateStr = date.toISOString().split('T')[0];
            
            calendarHTML += `<div class="week-day-header ${isToday ? 'today' : ''} ${holiday ? 'holiday' : ''}" onclick="selectDateForPlanning('${dateStr}')">`;
            calendarHTML += '<div class="week-day-name">' + weekdays[i].substring(0, 3) + '</div>';
            calendarHTML += '<div class="week-day-number">' + date.getDate() + '</div>';
            if (holiday) {
                calendarHTML += '<div class="holiday-label">' + holiday + '</div>';
            }
            calendarHTML += '</div>';
        }
        calendarHTML += '</div>';
        
        // Time slots (8 AM to 6 PM)
        const hours = Array.from({length: 11}, (_, i) => i + 8);
        calendarHTML += '<div class="week-body">';
        
        hours.forEach(hour => {
            calendarHTML += '<div class="week-time-row">';
            calendarHTML += '<div class="time-slot">' + (hour < 10 ? '0' : '') + hour + ':00</div>';
            
            for (let i = 0; i < 7; i++) {
                const date = new Date(weekStart);
                date.setDate(weekStart.getDate() + i);
                date.setHours(hour, 0, 0, 0);
                const dateStr = date.toISOString().split('T')[0];
                
                // Find tasks for this day
                const dayTasks = tasks.filter(t => {
                    if (!t.start_date || !t.end_date) return false;
                    const start = new Date(t.start_date);
                    const end = new Date(t.end_date);
                    return date >= start && date <= end;
                });
                
                calendarHTML += '<div class="week-time-cell" onclick="selectDateForPlanning(\'' + dateStr + '\')">';
                if (dayTasks.length > 0 && hour === 8) {
                    dayTasks.slice(0, 2).forEach(task => {
                        const statusColor = task.status === 'completed' ? 'success' : 
                                          (task.status === 'in_progress' ? 'info' : 
                                          (task.status === 'delayed' ? 'danger' : 'warning'));
                        calendarHTML += `<div class="week-task-item status-${statusColor}" onclick="event.stopPropagation(); viewTaskDetails(${task.id})" title="${escapeHtml(task.name)}">`;
                        calendarHTML += escapeHtml(task.name.length > 20 ? task.name.substring(0, 20) + '...' : task.name);
                        calendarHTML += '</div>';
                    });
                }
                calendarHTML += '</div>';
            }
            
            calendarHTML += '</div>';
        });
        
        calendarHTML += '</div></div>';
        calendarEl.innerHTML = calendarHTML;
    }
    
    // Render Day View
    function renderDayView() {
        const calendarEl = document.getElementById('task-calendar');
        const monthYearEl = document.getElementById('calendar-month-year');
        if (!calendarEl) return;

        const tasks = getFilteredTasks();
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const isToday = currentCalendarDate.getTime() === today.getTime();
        const holiday = getTanzaniaHoliday(currentCalendarDate);
        
        // Update display
        if (monthYearEl) {
            monthYearEl.textContent = formatTanzaniaDate(currentCalendarDate, { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
        
        // Find tasks for this day
        const dayTasks = tasks.filter(t => {
            if (!t.start_date || !t.end_date) return false;
            const start = new Date(t.start_date);
            start.setHours(0, 0, 0, 0);
            const end = new Date(t.end_date);
            end.setHours(23, 59, 59, 999);
            return currentCalendarDate >= start && currentCalendarDate <= end;
        });
        
        const dateStr = currentCalendarDate.toISOString().split('T')[0];
        
        let calendarHTML = '<div class="calendar-day-view">';
        
        if (holiday) {
            calendarHTML += '<div class="day-holiday-banner"><i class="fas fa-star me-2"></i>' + holiday + '</div>';
        }
        
        // Time slots
        const hours = Array.from({length: 24}, (_, i) => i);
        calendarHTML += '<div class="day-time-slots">';
        
        hours.forEach(hour => {
            const hourTasks = dayTasks.filter(t => {
                // Simple check - can be enhanced with actual time
                return true;
            });
            
            calendarHTML += '<div class="day-time-slot">';
            calendarHTML += '<div class="day-time-label">' + (hour < 10 ? '0' : '') + hour + ':00</div>';
            calendarHTML += '<div class="day-time-content">';
            
            if (hour === 9 && dayTasks.length > 0) {
                dayTasks.forEach(task => {
                    const statusColor = task.status === 'completed' ? 'success' : 
                                      (task.status === 'in_progress' ? 'info' : 
                                      (task.status === 'delayed' ? 'danger' : 'warning'));
                    calendarHTML += `<div class="day-task-card status-${statusColor}" onclick="viewTaskDetails(${task.id})">`;
                    calendarHTML += '<div class="task-card-header">';
                    calendarHTML += '<strong>' + escapeHtml(task.name) + '</strong>';
                    calendarHTML += '<span class="badge bg-' + statusColor + '">' + task.status.replace('_', ' ') + '</span>';
                    calendarHTML += '</div>';
                    calendarHTML += '<div class="task-card-body">';
                    calendarHTML += '<small class="text-muted">' + (task.category || 'Uncategorized') + '</small>';
                    calendarHTML += '<div class="mt-1">';
                    calendarHTML += '<span class="badge bg-secondary">' + (task.priority || 'Normal') + '</span>';
                    calendarHTML += '<span class="ms-2">Progress: ' + (task.progress_percentage || 0) + '%</span>';
                    calendarHTML += '</div>';
                    calendarHTML += '</div>';
                    calendarHTML += '</div>';
                });
            }
            
            calendarHTML += '</div>';
            calendarHTML += '</div>';
        });
        
        calendarHTML += '</div>';
        calendarHTML += '<div class="day-actions mt-3 text-center">';
        calendarHTML += '<button type="button" class="btn btn-primary" onclick="planTaskForDate(\'' + dateStr + '\')">';
        calendarHTML += '<i class="fas fa-plus me-2"></i>Add Task for This Day';
        calendarHTML += '</button>';
        calendarHTML += '</div>';
        calendarHTML += '</div>';
        
        calendarEl.innerHTML = calendarHTML;
    }
    
    // Main render function
    window.renderCalendar = function() {
        if (currentCalendarView === 'month') {
            renderMonthView();
        } else if (currentCalendarView === 'week') {
            renderWeekView();
        } else if (currentCalendarView === 'day') {
            renderDayView();
        }
        
        // Populate category filter
        populateCategoryFilter();
    };
    
    // Populate category filter
    function populateCategoryFilter() {
        const categorySelect = document.getElementById('filter-category');
        if (!categorySelect) return;
        
        const categories = new Set();
        (mainTasksData || []).forEach(task => {
            if (task.category) {
                categories.add(task.category);
            }
        });
        
        // Keep "All Categories" option
        const currentValue = categorySelect.value;
        categorySelect.innerHTML = '<option value="">All Categories</option>';
        Array.from(categories).sort().forEach(cat => {
            const option = document.createElement('option');
            option.value = cat;
            option.textContent = cat;
            if (cat === currentValue) {
                option.selected = true;
            }
            categorySelect.appendChild(option);
        });
    }
    
    // Set calendar view
    window.setCalendarView = function(view) {
        currentCalendarView = view;
        
        // Update button states
        document.querySelectorAll('[id^="view-"]').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById('view-' + view + '-btn').classList.add('active');
        
        renderCalendar();
    };
    
    // Navigate calendar
    window.navigateCalendar = function(direction) {
        if (currentCalendarView === 'month') {
        currentCalendarDate.setMonth(currentCalendarDate.getMonth() + direction);
        } else if (currentCalendarView === 'week') {
            currentCalendarDate.setDate(currentCalendarDate.getDate() + (direction * 7));
        } else if (currentCalendarView === 'day') {
            currentCalendarDate.setDate(currentCalendarDate.getDate() + direction);
        }
        renderCalendar();
    };
    
    window.goToToday = function() {
        currentCalendarDate = new Date();
        renderCalendar();
    };
    
    // Apply calendar filters
    window.applyCalendarFilters = function() {
        calendarFilters.status = document.getElementById('filter-status')?.value || '';
        calendarFilters.priority = document.getElementById('filter-priority')?.value || '';
        calendarFilters.category = document.getElementById('filter-category')?.value || '';
        renderCalendar();
    };
    
    // Reset calendar filters
    window.resetCalendarFilters = function() {
        calendarFilters = { status: '', priority: '', category: '', search: '' };
        document.getElementById('filter-status').value = '';
        document.getElementById('filter-priority').value = '';
        document.getElementById('filter-category').value = '';
        document.getElementById('calendar-search').value = '';
        renderCalendar();
    };
    
    // Filter calendar tasks by search
    window.filterCalendarTasks = function(searchTerm) {
        calendarFilters.search = searchTerm;
        renderCalendar();
    };
    
    // Toggle holidays
    window.toggleHolidays = function() {
        showHolidays = document.getElementById('show-holidays').checked;
        renderCalendar();
    };
    
    // Initialize calendar
    window.calendarView = function() {
        // Set default view to month
        currentCalendarView = 'month';
        // Update button states
        document.querySelectorAll('[id^="view-"]').forEach(btn => {
            btn.classList.remove('active');
        });
        const monthBtn = document.getElementById('view-month-btn');
        if (monthBtn) {
            monthBtn.classList.add('active');
        }
        renderCalendar();
    };
    
    // Select date for planning - Show activities for that date
    window.selectDateForPlanning = function(dateString) {
        showDateActivities(dateString);
    };
    
    // Show activities for a specific date
    window.showDateActivities = function(dateString) {
        const tasks = mainTasksData || [];
        const selectedDate = new Date(dateString);
        
        // Find all tasks that include this date
        const dayTasks = tasks.filter(t => {
            if (!t.start_date || !t.end_date) return false;
            const start = new Date(t.start_date);
            const end = new Date(t.end_date);
            return selectedDate >= start && selectedDate <= end;
        });
        
        // Get activities for tasks on this date
        const formData = new FormData();
        formData.append('action', 'get_activities_for_date');
        formData.append('date', dateString);
        
        TaskManager.apiCall(formData).done(response => {
            if (response.success) {
                displayDateActivitiesModal(dateString, dayTasks, response.activities || []);
            } else {
                // Fallback: show tasks only
                displayDateActivitiesModal(dateString, dayTasks, []);
            }
        }).fail(() => {
            // Fallback: show tasks only
            displayDateActivitiesModal(dateString, dayTasks, []);
        });
    };
    
    // Display modal with activities for a date
    window.displayDateActivitiesModal = function(dateString, tasks, activities) {
        const date = new Date(dateString);
        const dateFormatted = date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        
        // Remove existing modal
        $('#dateActivitiesModal').remove();
        
        // Build tasks HTML
        let tasksHtml = '';
        if (tasks.length > 0) {
            tasks.forEach(task => {
                const statusColor = task.status === 'completed' ? 'success' : 
                                  (task.status === 'in_progress' ? 'info' : 
                                  (task.status === 'delayed' ? 'danger' : 'warning'));
                tasksHtml += `
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="#" onclick="viewTaskDetails(${task.id}); $('#dateActivitiesModal').modal('hide'); return false;" class="text-primary">
                                            ${escapeHtml(task.name)}
                                        </a>
                                    </h6>
                                    <small class="text-muted">${task.category || 'Uncategorized'}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-${statusColor}">${task.status.replace('_', ' ')}</span>
                                        <span class="badge bg-secondary">${task.priority || 'Normal'}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">${task.progress_percentage || 0}%</small>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            tasksHtml = '<p class="text-muted text-center py-3">No tasks scheduled for this date</p>';
        }
        
        // Build activities HTML
        let activitiesHtml = '';
        if (activities.length > 0) {
            activities.forEach(activity => {
                const activityStatusColor = activity.status === 'Completed' ? 'success' : 
                                          (activity.status === 'In Progress' ? 'info' : 'secondary');
                activitiesHtml += `
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${escapeHtml(activity.name)}</h6>
                                    <small class="text-muted">Task: ${escapeHtml(activity.task_name || 'N/A')}</small>
                                    <div class="mt-1">
                                        <span class="badge bg-${activityStatusColor}">${activity.status}</span>
                                        <span class="badge bg-secondary">${activity.priority || 'Normal'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
        } else {
            activitiesHtml = '<p class="text-muted text-center py-3">No activities scheduled for this date</p>';
        }
        
        const modalHTML = `
            <div class="modal fade" id="dateActivitiesModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">Activities for ${dateFormatted}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <ul class="nav nav-tabs mb-3" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tasks-tab" type="button">
                                        Tasks (${tasks.length})
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#activities-tab" type="button">
                                        Activities (${activities.length})
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="tasks-tab" role="tabpanel">
                                    <div style="max-height: 400px; overflow-y: auto;">
                                        ${tasksHtml}
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="activities-tab" role="tabpanel">
                                    <div style="max-height: 400px; overflow-y: auto;">
                                        ${activitiesHtml}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            ${AppConfig.isManager ? `
                            <button type="button" class="btn btn-primary" onclick="planTaskForDate('${dateString}'); $('#dateActivitiesModal').modal('hide');">
                                Add New Activity
                            </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('dateActivitiesModal'));
        modal.show();
        
        // Clean up when modal is hidden
        $('#dateActivitiesModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    };
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return (text || '').replace(/[&<>"']/g, m => map[m]);
    }
    
    // Plan task for specific date - Advanced Modal
    window.planTaskForDate = function(startDate) {
        if (!AppConfig.isManager) {
            alert('Only managers can create tasks.');
            return;
        }
        
        // Open advanced task planning modal
        $('#planTaskModal').remove();
        
        const modalHTML = `
            <div class="modal fade" id="planTaskModal" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-calendar-plus me-2"></i>Plan New Task & Activities</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <form id="planTaskForm" data-ajax-form="true">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" name="action" value="task_create_main">
                                
                                <!-- Task Information -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Task Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-8">
                                                <label class="form-label">Task Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" required placeholder="Enter task name">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Category</label>
                                                <input type="text" name="category" class="form-control" placeholder="e.g., Development, Marketing">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Description</label>
                                            <textarea name="description" class="form-control" rows="2" placeholder="Describe the task"></textarea>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-3">
                                                <label class="form-label">Priority</label>
                                                <select name="priority" class="form-select">
                                                    <option value="Normal">Normal</option>
                                                    <option value="Low">Low</option>
                                                    <option value="High">High</option>
                                                    <option value="Critical">Critical</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Status</label>
                                                <select name="status" class="form-select">
                                                    <option value="planning">Planning</option>
                                                    <option value="in_progress">In Progress</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Budget (Optional)</label>
                                                <input type="number" name="budget" class="form-control" step="0.01" placeholder="0.00">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Tags</label>
                                                <input type="text" name="tags" class="form-control" placeholder="urgent, q1, client">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Date Range Selection -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Schedule</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                                <input type="date" name="start_date" id="plan-start-date" class="form-control" value="` + startDate + `" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                                <input type="date" name="end_date" id="plan-end-date" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Timeframe</label>
                                                <input type="text" name="timeframe" id="plan-timeframe" class="form-control" readonly placeholder="Auto-calculated">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Team Assignment -->
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Team Assignment</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="form-label">Team Leader <span class="text-danger">*</span></label>
                                                <select name="team_leader_id" class="form-select" required>
                                                    <option value="">-- Select Team Leader --</option>
                                                    ` + allUsersData.map(u => `<option value="${u.id}">${u.name}</option>`).join('') + `
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Activities Planning -->
                                <div class="card">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><i class="fas fa-tasks me-2"></i>Plan Activities</h6>
                                        <button type="button" class="btn btn-sm btn-primary" onclick="addActivityRow()">
                                            Add Activity
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <div id="activities-planning-container">
                                            <div class="activity-plan-row border rounded p-3 mb-3">
                                                <div class="row align-items-center">
                                                    <div class="col-md-4 mb-2 mb-md-0">
                                                        <label class="form-label small">Activity Name</label>
                                                        <input type="text" name="activities[0][name]" class="form-control form-control-sm" placeholder="Activity name">
                                                    </div>
                                                    <div class="col-md-2 mb-2 mb-md-0">
                                                        <label class="form-label small">Start Date</label>
                                                        <input type="date" name="activities[0][start_date]" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-2 mb-2 mb-md-0">
                                                        <label class="form-label small">End Date</label>
                                                        <input type="date" name="activities[0][end_date]" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-2 mb-2 mb-md-0">
                                                        <label class="form-label small">Priority</label>
                                                        <select name="activities[0][priority]" class="form-select form-select-sm">
                                                            <option value="Normal">Normal</option>
                                                            <option value="Low">Low</option>
                                                            <option value="High">High</option>
                                                            <option value="Critical">Critical</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 text-end">
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="removeActivityRow(this)">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-12">
                                                        <label class="form-label small">Assign Staff</label>
                                                        <select name="activities[0][users][]" class="form-select form-select-sm" multiple style="min-height: 60px;">
                                                            ` + allUsersData.map(u => `<option value="${u.id}">${u.name}</option>`).join('') + `
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i>Create Task & Activities
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('planTaskModal'));
        modal.show();
        
        // Calculate timeframe when dates change
        $('#plan-start-date, #plan-end-date').on('change', function() {
            TaskManager.calculateTimeframe('#plan-start-date', '#plan-end-date', '#plan-timeframe');
        });
        
        // Set end date to start date if not set
        if (!$('#plan-end-date').val()) {
            $('#plan-end-date').val(startDate);
        }
    };
    
    // Add activity row in planning modal
    window.addActivityRow = function() {
        const container = document.getElementById('activities-planning-container');
        const index = container.children.length;
        const newRow = document.createElement('div');
        newRow.className = 'activity-plan-row border rounded p-3 mb-3';
        newRow.innerHTML = `
            <div class="row align-items-center">
                <div class="col-md-4 mb-2 mb-md-0">
                    <label class="form-label small">Activity Name</label>
                    <input type="text" name="activities[${index}][name]" class="form-control form-control-sm" placeholder="Activity name">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label small">Start Date</label>
                    <input type="date" name="activities[${index}][start_date]" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label small">End Date</label>
                    <input type="date" name="activities[${index}][end_date]" class="form-control form-control-sm">
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="form-label small">Priority</label>
                    <select name="activities[${index}][priority]" class="form-select form-select-sm">
                        <option value="Normal">Normal</option>
                        <option value="Low">Low</option>
                        <option value="High">High</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeActivityRow(this)">
                        Delete
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-12">
                    <label class="form-label small">Assign Staff</label>
                    <select name="activities[${index}][users][]" class="form-select form-select-sm" multiple style="min-height: 60px;">
                        ` + allUsersData.map(u => `<option value="${u.id}">${u.name}</option>`).join('') + `
                    </select>
                </div>
            </div>
        `;
        container.appendChild(newRow);
    };
    
    // Remove activity row
    window.removeActivityRow = function(btn) {
        $(btn).closest('.activity-plan-row').remove();
    };

    // Report Generation
    window.generateReport = function(type) {
        if (type === 'summary') {
            window.location.href = '/modules/tasks/pdf?type=summary';
        } else {
            window.location.href = '/modules/tasks/pdf?type=detailed';
        }
    };

    // Export Calendar Function
    window.exportCalendar = function(format) {
        const tasks = mainTasksData || [];
        const year = currentCalendarDate.getFullYear();
        const month = currentCalendarDate.getMonth();
        const monthName = currentCalendarDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        
        // Get all tasks for the current month
        const monthTasks = tasks.filter(t => {
            if (!t.start_date || !t.end_date) return false;
            const start = new Date(t.start_date);
            const end = new Date(t.end_date);
            const monthStart = new Date(year, month, 1);
            const monthEnd = new Date(year, month + 1, 0);
            return (start <= monthEnd && end >= monthStart);
        });

        if (format === 'pdf') {
            // Export as PDF via server
            const params = new URLSearchParams({
                type: 'calendar',
                month: month + 1,
                year: year,
                format: 'pdf'
            });
            window.location.href = '/modules/tasks/pdf?' + params.toString();
        } else if (format === 'csv') {
            // Export as CSV
            let csv = ['Date,Task Name,Project/Category,Priority,Status,Team Leader,Start Date,End Date,Progress'];
            
            monthTasks.forEach(task => {
                const startDate = new Date(task.start_date);
                const endDate = new Date(task.end_date);
                const taskStart = new Date(Math.max(startDate, new Date(year, month, 1)));
                const taskEnd = new Date(Math.min(endDate, new Date(year, month + 1, 0)));
                
                // Create entries for each day the task spans in this month
                for (let d = new Date(taskStart); d <= taskEnd; d.setDate(d.getDate() + 1)) {
                    const dateStr = d.toISOString().split('T')[0];
                    const row = [
                        dateStr,
                        `"${(task.name || '').replace(/"/g, '""')}"`,
                        `"${(task.category || 'N/A').replace(/"/g, '""')}"`,
                        task.priority || 'Normal',
                        task.status || 'planning',
                        `"${((task.team_leader && task.team_leader.name) || (task.teamLeader && task.teamLeader.name) || 'N/A').replace(/"/g, '""')}"`,
                        task.start_date || '',
                        task.end_date || '',
                        (task.progress_percentage || 0) + '%'
                    ];
                    csv.push(row.join(','));
                }
            });
            
            const content = 'data:text/csv;charset=utf-8,' + csv.join('\n');
            const a = document.createElement('a');
            a.href = encodeURI(content);
            a.download = `calendar_export_${monthName.replace(/\s+/g, '_')}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        } else if (format === 'excel') {
            // Export as Excel (CSV format with .xls extension for Excel compatibility)
            let csv = ['Date,Task Name,Project/Category,Priority,Status,Team Leader,Start Date,End Date,Progress'];
            
            monthTasks.forEach(task => {
                const startDate = new Date(task.start_date);
                const endDate = new Date(task.end_date);
                const taskStart = new Date(Math.max(startDate, new Date(year, month, 1)));
                const taskEnd = new Date(Math.min(endDate, new Date(year, month + 1, 0)));
                
                // Create entries for each day the task spans in this month
                for (let d = new Date(taskStart); d <= taskEnd; d.setDate(d.getDate() + 1)) {
                    const dateStr = d.toISOString().split('T')[0];
                    const row = [
                        dateStr,
                        `"${(task.name || '').replace(/"/g, '""')}"`,
                        `"${(task.category || 'N/A').replace(/"/g, '""')}"`,
                        task.priority || 'Normal',
                        task.status || 'planning',
                        `"${((task.team_leader && task.team_leader.name) || (task.teamLeader && task.teamLeader.name) || 'N/A').replace(/"/g, '""')}"`,
                        task.start_date || '',
                        task.end_date || '',
                        (task.progress_percentage || 0) + '%'
                    ];
                    csv.push(row.join(','));
                }
            });
            
            // Create Excel-compatible content with BOM for UTF-8
            const BOM = '\uFEFF';
            const content = 'data:text/csv;charset=utf-8,' + BOM + csv.join('\n');
            const a = document.createElement('a');
            a.href = encodeURI(content);
            a.download = `calendar_export_${monthName.replace(/\s+/g, '_')}.xls`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    };

    // Initialize charts when dashboard tab is shown
    $('#dashboard-tab').on('shown.bs.tab', function() {
        setTimeout(initDashboardCharts, 100);
    });

    // Initialize analytics charts when analytics tab is shown
    $('#analytics-tab').on('shown.bs.tab', function() {
        console.log('Analytics tab shown, initializing charts...');
        setTimeout(initAnalyticsCharts, 300);
    });
    
    // Also initialize if analytics view is already visible
    if ($('#analytics-view').hasClass('active') || $('#analytics-view').hasClass('show')) {
        console.log('Analytics view already active, initializing charts...');
        setTimeout(initAnalyticsCharts, 500);
    }
    
    // Fallback: Initialize when analytics-view becomes visible
    const analyticsObserver = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const target = mutation.target;
                if (target.id === 'analytics-view' && (target.classList.contains('active') || target.classList.contains('show'))) {
                    console.log('Analytics view became visible, initializing charts...');
                    setTimeout(initAnalyticsCharts, 300);
                }
            }
        });
    });
    
    const analyticsView = document.getElementById('analytics-view');
    if (analyticsView) {
        analyticsObserver.observe(analyticsView, { attributes: true, attributeFilter: ['class'] });
    }

    // Initialize calendar when calendar tab is shown
    $('#calendar-tab').on('shown.bs.tab', function() {
        setTimeout(() => calendarView(), 100);
    });

    // Initialize dashboard charts on page load if dashboard is active
    if ($('#dashboard-tab').hasClass('active')) {
        setTimeout(initDashboardCharts, 500);
    }
});
</script>

<style>
/* Advanced Calendar Styles - Tanzania Timezone Support */
.advanced-calendar {
    width: 100%;
    overflow-x: auto;
}

.responsive-calendar {
    width: 100%;
    overflow-x: auto;
}

.calendar-weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
    font-weight: bold;
    text-align: center;
    margin-bottom: 0.5rem;
    padding: 0.5rem 0;
    border-bottom: 2px solid #dee2e6;
}

.calendar-weekday {
    padding: 0.5rem;
    font-size: 0.875rem;
    color: #6c757d;
}

.calendar-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
}

.calendar-day {
    min-height: 80px;
    border: 1px solid #e9ecef;
    padding: 0.5rem;
    border-radius: 4px;
    background: #fff;
    transition: all 0.2s;
    position: relative;
}

.calendar-day.clickable-day {
    cursor: pointer;
}

.calendar-day.clickable-day:hover {
    background: #f8f9fa;
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.calendar-day.clickable-day:active {
    transform: translateY(0);
}

.calendar-day.today {
    background: linear-gradient(135deg, #e7f3ff 0%, #cfe2ff 100%);
    border-color: #007bff;
    border-width: 2px;
    min-height: 130px;
    box-shadow: 0 2px 8px rgba(0,123,255,0.2);
}

.calendar-day.holiday {
    background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
    border-color: #ffc107;
}

.calendar-day.holiday.today {
    background: linear-gradient(135deg, #fff3cd 0%, #e7f3ff 50%, #cfe2ff 100%);
}

.day-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.holiday-badge {
    color: #ffc107;
    font-size: 0.75rem;
    cursor: help;
}

.holiday-badge i {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}

.add-activity-section {
    margin-top: 0.5rem;
    padding-top: 0.5rem;
    border-top: 1px dashed #007bff;
}

.btn-add-activity {
    width: 100%;
    background: #28a745;
    color: white;
    border: none;
    padding: 0.3rem 0.5rem;
    border-radius: 3px;
    font-size: 0.7rem;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    font-weight: 500;
}

.btn-add-activity:hover {
    background: #218838;
    transform: scale(1.02);
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.btn-add-activity i {
    font-size: 0.65rem;
}

.activity-plan-row {
    background: #f8f9fa;
    transition: all 0.2s;
}

.activity-plan-row:hover {
    background: #e9ecef;
    border-color: #007bff !important;
}

.calendar-day.empty {
    background: #f8f9fa;
    border: none;
    min-height: 0;
    padding: 0;
}

.day-number {
    font-weight: bold;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    color: #495057;
}

.day-tasks {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    margin-top: 0.25rem;
}

.task-badge {
    background: #007bff;
    color: white;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    font-size: 0.7rem;
    cursor: pointer;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    transition: background 0.2s;
}

.task-badge:hover {
    background: #0056b3;
    transform: scale(1.02);
}

.task-badge.more {
    background: #6c757d;
    text-align: center;
    font-weight: bold;
}

.task-badge.status-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.task-badge.status-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.task-badge.status-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #212529;
}

.task-badge.status-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.task-badge.critical {
    border-left: 3px solid #dc3545;
    font-weight: bold;
}

.task-badge.high {
    border-left: 3px solid #ff9800;
}

.task-name {
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Analytics Cards Styling */
.analytics-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    border-radius: 8px;
}
.analytics-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
}
.stat-card {
    border-left: 4px solid;
    border-radius: 8px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.stat-card.primary { border-left-color: #0d6efd; }
.stat-card.success { border-left-color: #198754; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.danger { border-left-color: #dc3545; }
.stat-card.info { border-left-color: #0dcaf0; }
.stat-card.secondary { border-left-color: #6c757d; }

/* Week View Styles */
.calendar-week-view {
    width: 100%;
    overflow-x: auto;
}

.week-header {
    display: grid;
    grid-template-columns: 80px repeat(7, 1fr);
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
}

.time-column {
    font-weight: bold;
    color: #6c757d;
}

.week-day-header {
    text-align: center;
    padding: 0.75rem;
    border-radius: 6px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.2s;
}

.week-day-header:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.week-day-header.today {
    background: linear-gradient(135deg, #e7f3ff 0%, #cfe2ff 100%);
    border: 2px solid #007bff;
    font-weight: bold;
}

.week-day-header.holiday {
    background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
    border: 1px solid #ffc107;
}

.week-day-name {
    font-size: 0.75rem;
    text-transform: uppercase;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.week-day-number {
    font-size: 1.25rem;
    font-weight: bold;
    color: #212529;
}

.holiday-label {
    font-size: 0.65rem;
    color: #856404;
    margin-top: 0.25rem;
    font-weight: 600;
}

.week-body {
    display: flex;
    flex-direction: column;
}

.week-time-row {
    display: grid;
    grid-template-columns: 80px repeat(7, 1fr);
    gap: 0.5rem;
    border-bottom: 1px solid #e9ecef;
    min-height: 60px;
}

.time-slot {
    padding: 0.5rem;
    font-size: 0.75rem;
    color: #6c757d;
    text-align: center;
    border-right: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
}

.week-time-cell {
    padding: 0.5rem;
    cursor: pointer;
    transition: background 0.2s;
    border-right: 1px solid #f0f0f0;
}

.week-time-cell:hover {
    background: #f8f9fa;
}

.week-task-item {
    padding: 0.3rem 0.5rem;
    border-radius: 4px;
    margin-bottom: 0.25rem;
    font-size: 0.75rem;
    cursor: pointer;
    transition: all 0.2s;
    color: white;
}

.week-task-item:hover {
    transform: translateX(5px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.week-task-item.status-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.week-task-item.status-info {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
}

.week-task-item.status-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #212529;
}

.week-task-item.status-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

/* Day View Styles */
.calendar-day-view {
    width: 100%;
}

.day-holiday-banner {
    background: linear-gradient(135deg, #fff3cd 0%, #ffe69c 100%);
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    text-align: center;
    font-weight: bold;
    color: #856404;
    border: 2px solid #ffc107;
}

.day-time-slots {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-height: 600px;
    overflow-y: auto;
}

.day-time-slot {
    display: grid;
    grid-template-columns: 80px 1fr;
    gap: 1rem;
    border-bottom: 1px solid #e9ecef;
    padding: 0.75rem 0;
}

.day-time-label {
    font-weight: bold;
    color: #6c757d;
    text-align: center;
    padding: 0.5rem;
    border-right: 2px solid #e9ecef;
}

.day-time-content {
    padding: 0.5rem;
}

.day-task-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.day-task-card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.day-task-card.status-success {
    border-left: 4px solid #28a745;
}

.day-task-card.status-info {
    border-left: 4px solid #17a2b8;
}

.day-task-card.status-warning {
    border-left: 4px solid #ffc107;
}

.day-task-card.status-danger {
    border-left: 4px solid #dc3545;
}

.task-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.task-card-body {
    font-size: 0.875rem;
}

.day-actions {
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 2px solid #e9ecef;
}

/* View Button Active State */
.btn-group .btn.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

/* Responsive Design */
@media (max-width: 768px) {
    .calendar-day {
        min-height: 60px;
        padding: 0.25rem;
    }
    
    .calendar-day.today {
        min-height: 100px;
    }
    
    .day-number {
        font-size: 0.75rem;
    }
    
    .task-badge {
        font-size: 0.65rem;
        padding: 0.15rem 0.3rem;
    }
    
    .calendar-weekday {
        font-size: 0.75rem;
        padding: 0.25rem;
    }
    
    .btn-add-activity {
        font-size: 0.65rem;
        padding: 0.25rem 0.4rem;
    }
}

@media (max-width: 576px) {
    .calendar-day {
        min-height: 50px;
        padding: 0.2rem;
    }
    
    .calendar-day.today {
        min-height: 90px;
    }
    
    .day-tasks {
        gap: 0.1rem;
    }
    
    .task-badge {
        font-size: 0.6rem;
        padding: 0.1rem 0.2rem;
    }
    
    .btn-add-activity {
        font-size: 0.6rem;
        padding: 0.2rem 0.3rem;
    }
    
    .add-activity-section {
        margin-top: 0.25rem;
        padding-top: 0.25rem;
    }
    
    .week-header {
        grid-template-columns: 50px repeat(7, 1fr);
        gap: 0.25rem;
    }
    
    .week-time-row {
        grid-template-columns: 50px repeat(7, 1fr);
        gap: 0.25rem;
    }
    
    .day-time-slot {
        grid-template-columns: 60px 1fr;
    }
}

/* Modal Scrolling Styles */
.modal-dialog-scrollable .modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.modal-dialog-scrollable .modal-header {
    flex-shrink: 0;
    border-bottom: 1px solid #dee2e6;
}

.modal-dialog-scrollable .modal-body {
    overflow-y: auto;
    flex: 1 1 auto;
    max-height: calc(90vh - 120px);
    padding: 1.5rem;
}

.modal-dialog-scrollable .modal-footer {
    flex-shrink: 0;
    border-top: 1px solid #dee2e6;
}

/* Task Modal Specific Styles */
#createMainTaskModal .modal-body,
#viewTaskModal .modal-body {
    max-height: calc(90vh - 160px);
    overflow-y: auto;
    overflow-x: hidden;
}

#createMainTaskModal .modal-body::-webkit-scrollbar,
#viewTaskModal .modal-body::-webkit-scrollbar {
    width: 8px;
}

#createMainTaskModal .modal-body::-webkit-scrollbar-track,
#viewTaskModal .modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

#createMainTaskModal .modal-body::-webkit-scrollbar-thumb,
#viewTaskModal .modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

#createMainTaskModal .modal-body::-webkit-scrollbar-thumb:hover,
#viewTaskModal .modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Ensure form elements don't overflow */
.modal-body .row {
    margin-left: 0;
    margin-right: 0;
}

.modal-body .form-control,
.modal-body .form-select {
    max-width: 100%;
}

/* Responsive modal adjustments */
@media (max-width: 768px) {
    .modal-dialog-scrollable .modal-body {
        max-height: calc(85vh - 120px);
    }
    
    #createMainTaskModal .modal-body,
    #viewTaskModal .modal-body {
        max-height: calc(85vh - 160px);
    }
}
</style>
@endpush