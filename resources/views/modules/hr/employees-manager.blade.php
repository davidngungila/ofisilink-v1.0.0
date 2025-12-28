<!-- Advanced Manager View -->
<div class="row">
    <!-- Enhanced Statistics Dashboard -->
    <div class="col-12 mb-4">
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="bx bx-user fs-4"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Total Employees</h6>
                                        <h4 class="mb-0 text-primary">{{ $employees->total() }}</h4>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-success">
                                            <i class="bx bx-trending-up me-1"></i>+12%
                                        </small>
                                    </div>
                                </div>
                                <small class="text-muted">Active workforce</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-initial rounded-circle bg-label-success">
                                    <i class="bx bx-check-circle fs-4"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Active Staff</h6>
                                        <h4 class="mb-0 text-success">{{ $employees->where('is_active', true)->count() }}</h4>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-success">
                                            <i class="bx bx-trending-up me-1"></i>+5%
                                        </small>
                                    </div>
                                </div>
                                <small class="text-muted">Currently working</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-initial rounded-circle bg-label-info">
                                    <i class="bx bx-building fs-4"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Departments</h6>
                                        <h4 class="mb-0 text-info">{{ $departments->count() }}</h4>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-info">
                                            <i class="bx bx-trending-up me-1"></i>+2
                                        </small>
                                    </div>
                                </div>
                                <small class="text-muted">Active departments</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-lg me-3">
                                <span class="avatar-initial rounded-circle bg-label-warning">
                                    <i class="bx bx-dollar fs-4"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Monthly Cost</h6>
                                        <h4 class="mb-0 text-warning">{{ number_format($employees->sum(function($emp) { return $emp->employee ? $emp->employee->salary : 0; }) / 1000000, 1) }}M</h4>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-warning">
                                            <i class="bx bx-trending-up me-1"></i>+8%
                                        </small>
                                    </div>
                                </div>
                                <small class="text-muted">Total salary cost</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities Section -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="card-title mb-0">
                    <i class="bx bx-history me-2"></i>Recent Activities
                </h6>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshActivities()">
                    <i class="bx bx-refresh me-1"></i>Refresh
                </button>
            </div>
            <div class="card-body">
                @if($recentActivities && $recentActivities->count() > 0)
                    <div class="timeline" id="activitiesTimeline">
                        @php
                            $totalActivities = $recentActivities->count();
                            $initialDisplay = 5;
                            $activityIndex = 0;
                        @endphp
                        @foreach($recentActivities as $activity)
                            @php
                                $action = strtolower($activity->action ?? '');
                                $color = 'primary';
                                if (str_contains($action, 'create') || str_contains($action, 'add')) {
                                    $color = 'success';
                                } elseif (str_contains($action, 'update') || str_contains($action, 'edit')) {
                                    $color = 'info';
                                } elseif (str_contains($action, 'delete') || str_contains($action, 'remove')) {
                                    $color = 'danger';
                                } elseif (str_contains($action, 'deactivate') || str_contains($action, 'inactive')) {
                                    $color = 'warning';
                                }
                                $activityIndex++;
                                $isHidden = $activityIndex > $initialDisplay ? 'd-none' : '';
                            @endphp
                            <div class="timeline-item activity-item {{ $isHidden }}" data-activity-index="{{ $activityIndex }}">
                            <div class="timeline-marker bg-{{ $color }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">
                                    {{ ucfirst($activity->action ?? 'Activity') }}
                                    @if($activity->user)
                                        <span class="text-muted">by {{ $activity->user->name }}</span>
                                    @endif
                                </h6>
                                <p class="timeline-text">
                                    {{ $activity->description ?? ($activity->action ?? 'No description available') }}
                                </p>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if($totalActivities > $initialDisplay)
                        <div class="text-center mt-3 pt-3 border-top" id="activitiesActions">
                            <button class="btn btn-outline-primary btn-sm" onclick="showAllActivities()" id="showAllBtn">
                                <i class="bx bx-show me-1"></i>Show All Activities ({{ $totalActivities - $initialDisplay }} more)
                            </button>
                            <button class="btn btn-outline-secondary btn-sm d-none" onclick="showLessActivities()" id="showLessBtn">
                                <i class="bx bx-hide me-1"></i>Show Less
                            </button>
                        </div>
                    @endif
                @else
                    <div class="text-center py-4">
                        <i class="bx bx-history text-muted" style="font-size: 3rem;"></i>
                        <h6 class="mt-3 text-muted">No recent activities</h6>
                        <p class="text-muted">Activity logs will appear here as employees are managed.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bx bx-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($canEditAll)
                    <a href="{{ route('modules.hr.employees.register') }}" class="btn btn-danger btn-lg quick-action-btn" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; color: white; font-weight: 600; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); transition: all 0.3s ease;">
                        <i class="bx bx-user-plus me-2"></i>Add New Employee
                    </a>
                    
                    <button type="button" class="btn btn-outline-warning btn-lg quick-action-btn" onclick="syncAllEmployees()" style="border-color: #ff9800; color: #ff9800; font-weight: 500; transition: all 0.3s ease;">
                        <i class="bx bx-sync me-2"></i>Sync All Users
                    </button>
                    @endif
                    
                    <button type="button" class="btn btn-outline-success btn-lg quick-action-btn" onclick="exportEmployees()" style="border-color: #28a745; color: #28a745; font-weight: 500; transition: all 0.3s ease;">
                        <i class="bx bx-download me-2"></i>Export Employee Data
                    </button>
                    
                    <button type="button" class="btn btn-outline-info btn-lg quick-action-btn" onclick="generateReport()" style="border-color: #17a2b8; color: #17a2b8; font-weight: 500; transition: all 0.3s ease;">
                        <i class="bx bx-bar-chart me-2"></i>Generate Report
                    </button>
                    
                    <button type="button" class="btn btn-outline-warning btn-lg quick-action-btn" onclick="bulkActions()" style="border-color: #ff9800; color: #ff9800; font-weight: 500; transition: all 0.3s ease;">
                        <i class="bx bx-check-square me-2"></i>Bulk Actions
                    </button>
                    
                    <button type="button" class="btn btn-outline-secondary btn-lg quick-action-btn" onclick="refreshData()" style="border-color: #6c757d; color: #6c757d; font-weight: 500; transition: all 0.3s ease;">
                        <i class="bx bx-refresh me-2"></i>Refresh Data
                    </button>
                </div>
                
            </div>
        </div>
    </div>

</div>


<!-- Enhanced Employee Directory -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            <i class="bx bx-list-ul me-2"></i>Employee Directory
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end gap-2">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary active" id="listViewBtn" onclick="switchView('list')">
                                    <i class="bx bx-list-ul me-1"></i>List
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="cardViewBtn" onclick="switchView('card')">
                                    <i class="bx bx-grid-alt me-1"></i>Cards
                                </button>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bx bx-sort me-1"></i>Sort By
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="sortEmployees('name')">Name A-Z</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortEmployees('department')">Department</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortEmployees('salary')">Salary</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="sortEmployees('hire_date')">Hire Date</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- List View -->
                <div id="listView" class="table-responsive">
                    <table class="table table-hover table-sm mb-0" id="employeeTable">
                        <thead class="table-light">
                            <tr>
                                <th width="40" class="text-center">
                                    <input type="checkbox" id="selectAllEmployees" class="form-check-input">
                                </th>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Salary</th>
                                <th>Completion</th>
                                <th>Status</th>
                                <th width="80" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employees as $employee)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input employee-checkbox" value="{{ $employee->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            @if($employee->photo)
                                                <img src="{{ Storage::url('photos/' . $employee->photo) }}" alt="Employee Photo" class="rounded-circle">
                                            @else
                                                <span class="avatar-initial rounded-circle bg-label-primary">{{ substr($employee->name, 0, 1) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fs-6">{{ $employee->name }}</h6>
                                            <small class="text-muted">{{ $employee->employee_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">{{ $employee->primaryDepartment->name ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $employee->employee->position ?? 'N/A' }}</td>
                                <td>
                                    @if($employee->employee && $employee->employee->salary)
                                        <strong class="text-success">{{ number_format($employee->employee->salary, 0) }} TZS</strong>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if(isset($employee->completion_percentage))
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 20px; width: 80px;">
                                            <div class="progress-bar {{ $employee->completion_percentage >= 90 ? 'bg-success' : ($employee->completion_percentage >= 70 ? 'bg-primary' : ($employee->completion_percentage >= 50 ? 'bg-warning' : 'bg-danger')) }}" 
                                                 style="width: {{ $employee->completion_percentage }}%"
                                                 role="progressbar"
                                                 aria-valuenow="{{ $employee->completion_percentage }}"
                                                 aria-valuemin="0"
                                                 aria-valuemax="100">
                                                {{ number_format($employee->completion_percentage, 0) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted fw-bold">{{ number_format($employee->completion_percentage, 0) }}%</small>
                                    </div>
                                    @else
                                    <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $employee->is_active ? 'bg-label-success' : 'bg-label-danger' }}">
                                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="bx bx-show"></i>
                                        </a>
                                        @if($canEditAll)
                                        <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-warning" title="Edit Employee">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" title="More Actions">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('modules.hr.employees.registration-pdf', $employee->id) }}" target="_blank">
                                                    <i class="bx bx-file-blank me-2"></i> Generate PDF
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="openUploadPhotoModal({{ $employee->id }})">
                                                    <i class="bx bx-camera me-2"></i> Upload Photo
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="sendEmployeeSMS({{ $employee->id }})">
                                                    <i class="bx bx-message me-2"></i> Send SMS
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="toggleEmployeeStatus({{ $employee->id }})">
                                                    <i class="bx bx-{{ $employee->is_active ? 'user-x' : 'user-check' }} me-2"></i> 
                                                    {{ $employee->is_active ? 'Deactivate' : 'Activate' }}
                                                </a>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bx bx-user text-muted" style="font-size: 3rem;"></i>
                                        <h6 class="mt-2 text-muted">No employees found</h6>
                                        <p class="text-muted">No employees match your current filters.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($employees->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3 px-3 py-2 border-top bg-light">
                    <div class="text-muted">
                        <small>Page <strong>{{ $employees->currentPage() }}</strong> of <strong>{{ $employees->lastPage() }}</strong></small>
                    </div>
                    <div class="pagination-wrapper">
                        {{ $employees->onEachSide(1)->links('pagination::bootstrap-4') }}
                    </div>
                    <div class="text-muted">
                        <small><strong>{{ $employees->total() }}</strong> employees</small>
                    </div>
                </div>
                @else
                <div class="d-flex justify-content-end align-items-center mt-3 px-3 py-2 border-top bg-light">
                    <div class="text-muted">
                        <small><strong>{{ $employees->total() }}</strong> employees</small>
                    </div>
                </div>
                @endif
                
                <!-- Advanced Card View -->
                <div id="cardView" class="d-none p-4">
                    <div class="row g-4">
                        @forelse($employees as $employee)
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                            <div class="card employee-card h-100 shadow-sm border-0 hover-lift">
                                <div class="card-body p-3">
                                    <!-- Header with Photo and Status -->
                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-lg me-3">
                                                @if($employee->photo)
                                                    <img src="{{ Storage::url('photos/' . $employee->photo) }}" alt="Employee Photo" class="rounded-circle" style="width: 60px; height: 60px; object-fit: cover;">
                                                @else
                                                    <span class="avatar-initial rounded-circle bg-label-primary d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                        {{ substr($employee->name, 0, 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $employee->name }}</h6>
                                                <small class="text-muted">{{ $employee->employee_id }}</small>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button type="button" class="btn btn-sm btn-icon p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="{{ route('employees.show', $employee->id) }}" title="View Full Details">
                                                    <i class="bx bx-show me-2"></i> View Full Details
                                                </a>
                                                @if($canEditAll)
                                                <a class="dropdown-item" href="{{ route('employees.edit', $employee->id) }}" title="Edit Employee">
                                                    <i class="bx bx-edit me-2"></i> Edit Employee
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ route('modules.hr.employees.registration-pdf', $employee->id) }}" target="_blank" title="Generate PDF">
                                                    <i class="bx bx-file-blank me-2"></i> Generate PDF
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="openUploadPhotoModal({{ $employee->id }})" title="Upload Photo">
                                                    <i class="bx bx-camera me-2"></i> Upload Photo
                                                </a>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="sendEmployeeSMS({{ $employee->id }})" title="Send SMS">
                                                    <i class="bx bx-message me-2"></i> Send SMS
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="toggleEmployeeStatus({{ $employee->id }})" title="{{ $employee->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="bx bx-{{ $employee->is_active ? 'user-x' : 'user-check' }} me-2"></i> 
                                                    {{ $employee->is_active ? 'Deactivate' : 'Activate' }}
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Department & Position -->
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bx bx-building text-primary me-2"></i>
                                            <span class="badge bg-label-info">{{ $employee->primaryDepartment->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="bx bx-briefcase text-warning me-2"></i>
                                            <small class="text-muted">{{ $employee->employee->position ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                    
                                    <!-- Salary -->
                                    @if($employee->employee && $employee->employee->salary)
                                    <div class="mb-3 p-2 bg-light rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Salary</small>
                                            <strong class="text-success">{{ number_format($employee->employee->salary, 0) }} TZS</strong>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <!-- Profile Completion -->
                                    @if(isset($employee->completion_percentage))
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">Profile Completion</small>
                                            <small class="fw-bold {{ $employee->completion_percentage >= 90 ? 'text-success' : ($employee->completion_percentage >= 70 ? 'text-primary' : ($employee->completion_percentage >= 50 ? 'text-warning' : 'text-danger')) }}">
                                                {{ number_format($employee->completion_percentage, 0) }}%
                                            </small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar {{ $employee->completion_percentage >= 90 ? 'bg-success' : ($employee->completion_percentage >= 70 ? 'bg-primary' : ($employee->completion_percentage >= 50 ? 'bg-warning' : 'bg-danger')) }}" 
                                                 style="width: {{ $employee->completion_percentage }}%"
                                                 role="progressbar">
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                    
                                    <!-- Status & Quick Actions -->
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <span class="badge {{ $employee->is_active ? 'bg-label-success' : 'bg-label-danger' }}">
                                            <i class="bx bx-{{ $employee->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                                            {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-primary" title="View Details">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            @if($canEditAll)
                                            <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-outline-warning" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="bx bx-user text-muted" style="font-size: 4rem;"></i>
                                <h6 class="mt-3 text-muted">No employees found</h6>
                                <p class="text-muted">No employees match your current filters.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                    
                    @if($employees->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3 px-3 py-2 border-top bg-light">
                        <div class="text-muted">
                            <small>Page <strong>{{ $employees->currentPage() }}</strong> of <strong>{{ $employees->lastPage() }}</strong></small>
                        </div>
                        <div class="pagination-wrapper">
                            {{ $employees->onEachSide(1)->links('pagination::bootstrap-4') }}
                        </div>
                        <div class="text-muted">
                            <small><strong>{{ $employees->total() }}</strong> employees</small>
                        </div>
                    </div>
                    @else
                    <div class="d-flex justify-content-end align-items-center mt-3 px-3 py-2 border-top bg-light">
                        <div class="text-muted">
                            <small><strong>{{ $employees->total() }}</strong> employees</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Ensure jQuery and SweetAlert2 are available
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded!');
}
if (typeof Swal === 'undefined') {
    console.error('SweetAlert2 is not loaded!');
}
// Switch between List and Card View
function switchView(viewType) {
    const listView = document.getElementById('listView');
    const cardView = document.getElementById('cardView');
    const listBtn = document.getElementById('listViewBtn');
    const cardBtn = document.getElementById('cardViewBtn');
    
    if (viewType === 'list') {
        listView.classList.remove('d-none');
        cardView.classList.add('d-none');
        listBtn.classList.add('active');
        cardBtn.classList.remove('active');
        localStorage.setItem('employeeViewType', 'list');
    } else {
        listView.classList.add('d-none');
        cardView.classList.remove('d-none');
        cardBtn.classList.add('active');
        listBtn.classList.remove('active');
        localStorage.setItem('employeeViewType', 'card');
    }
}

// Restore view preference on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('employeeViewType') || 'list';
    if (savedView === 'card') {
        switchView('card');
    }
});

// Toast Notification Helper Function
function showToast(message, type = 'success', duration = 4000) {
    const iconMap = {
        'success': 'bx-check-circle',
        'error': 'bx-error-circle',
        'warning': 'bx-error',
        'info': 'bx-info-circle'
    };
    
    const bgMap = {
        'success': 'success',
        'error': 'danger',
        'warning': 'warning',
        'info': 'info'
    };
    
    const icon = iconMap[type] || 'bx-info-circle';
    const bgClass = bgMap[type] || 'info';
    
    Swal.fire({
        icon: type,
        title: type === 'success' ? '✓ Success' : type === 'error' ? '✗ Error' : type === 'warning' ? '⚠ Warning' : 'ℹ Info',
        html: `<div class="text-start"><p class="mb-0">${message}</p></div>`,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: duration,
        timerProgressBar: true,
        background: '#f8f9fa',
        customClass: {
            popup: `swal2-toast-border-${bgClass}`,
            title: `text-${bgClass} fw-bold`,
            htmlContainer: 'text-dark'
        },
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
            const borderColor = type === 'success' ? '#198754' : type === 'error' ? '#dc3545' : type === 'warning' ? '#ffc107' : '#0dcaf0';
            toast.style.borderLeft = `4px solid ${borderColor}`;
        }
    });
}

$(document).ready(function() {
    // Initialize charts if function exists
    if (typeof initializeCharts === 'function') {
        initializeCharts();
    }
    
    // Advanced search functionality
    $('#advancedSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        filterEmployeesBySearch(searchTerm);
    });
    
    // Filter change handlers
    $('#departmentFilter, #employmentTypeFilter, #statusFilter, #salaryRangeFilter').on('change', function() {
        applyFilters();
    });
    
    // Ensure list view is visible on page load
    $('#listView').show();
    $('#gridView').remove(); // Remove grid view completely if it exists
    
    // Select all functionality
    $('#selectAllEmployees').change(function() {
        $('.employee-checkbox').prop('checked', this.checked);
        updateBulkActionsVisibility();
    });
    
    $('.employee-checkbox').change(function() {
        updateBulkActionsVisibility();
        updateSelectAllCheckbox();
    });
});

function showAllActivities() {
    // Show all hidden activities
    $('.activity-item.d-none').removeClass('d-none').hide().fadeIn(300);
    
    // Toggle buttons
    $('#showAllBtn').addClass('d-none');
    $('#showLessBtn').removeClass('d-none');
    
    // Smooth scroll to the activities section
    $('html, body').animate({
        scrollTop: $('#activitiesTimeline').offset().top - 100
    }, 500);
}

function showLessActivities() {
    // Hide activities beyond the first 5
    $('.activity-item').each(function(index) {
        const activityIndex = parseInt($(this).data('activity-index'));
        if (activityIndex > 5) {
            $(this).fadeOut(300, function() {
                $(this).addClass('d-none').show();
            });
        }
    });
    
    // Toggle buttons
    $('#showLessBtn').addClass('d-none');
    $('#showAllBtn').removeClass('d-none');
    
    // Scroll to top of activities section
    $('html, body').animate({
        scrollTop: $('#activitiesTimeline').offset().top - 100
    }, 500);
}

function refreshActivities() {
    location.reload();
}

// Removed setViewMode function - only list view is used now

function sortEmployees(sortBy) {
    // This would typically require server-side sorting
    // For now, we'll implement client-side sorting
    const rows = Array.from($('#employeeTable tbody tr'));
    
    rows.sort((a, b) => {
        let aVal, bVal;
        
        switch(sortBy) {
            case 'name':
                aVal = $(a).find('h6').text().toLowerCase();
                bVal = $(b).find('h6').text().toLowerCase();
                break;
            case 'department':
                aVal = $(a).find('.badge').text().toLowerCase();
                bVal = $(b).find('.badge').text().toLowerCase();
                break;
            case 'salary':
                aVal = parseFloat($(a).find('strong').text().replace(/[^\d]/g, '')) || 0;
                bVal = parseFloat($(b).find('strong').text().replace(/[^\d]/g, '')) || 0;
                break;
            default:
                return 0;
        }
        
        return aVal > bVal ? 1 : -1;
    });
    
    $('#employeeTable tbody').empty().append(rows);
}

function applyFilters() {
    const searchTerm = $('#advancedSearch').val().toLowerCase();
    const department = $('#departmentFilter').val();
    const employmentType = $('#employmentTypeFilter').val();
    const status = $('#statusFilter').val();
    const salaryRange = $('#salaryRangeFilter').val();
    
    // Only filter table rows (list view)
    $('#employeeTable tbody tr').each(function() {
        let show = true;
        
        // Search filter
        if (searchTerm) {
            const text = $(this).text().toLowerCase();
            if (!text.includes(searchTerm)) {
                show = false;
            }
        }
        
        // Department filter
        if (department && show) {
            const deptBadge = $(this).find('.badge').text().toLowerCase();
            const deptOption = $('#departmentFilter option[value="' + department + '"]').text().toLowerCase();
            if (!deptBadge.includes(deptOption)) {
                show = false;
            }
        }
        
        // Status filter
        if (status && show) {
            const statusBadge = $(this).find('.badge');
            const isActive = statusBadge.hasClass('bg-label-success');
            if ((status === 'active' && !isActive) || (status === 'inactive' && isActive)) {
                show = false;
            }
        }
        
        // Salary range filter
        if (salaryRange && show) {
            const salaryText = $(this).find('strong').text();
            const salary = parseFloat(salaryText.replace(/[^\d]/g, '')) || 0;
            
            switch(salaryRange) {
                case '0-500000':
                    if (salary > 500000) show = false;
                    break;
                case '500000-1000000':
                    if (salary < 500000 || salary > 1000000) show = false;
                    break;
                case '1000000-2000000':
                    if (salary < 1000000 || salary > 2000000) show = false;
                    break;
                case '2000000+':
                    if (salary < 2000000) show = false;
                    break;
            }
        }
        
        if (show) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function filterEmployeesBySearch(searchTerm) {
    applyFilters();
}

function updateSelectAllCheckbox() {
    const totalCheckboxes = $('.employee-checkbox').length;
    const checkedCheckboxes = $('.employee-checkbox:checked').length;
    
    $('#selectAllEmployees').prop('checked', totalCheckboxes === checkedCheckboxes);
    $('#selectAllEmployees').prop('indeterminate', checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes);
}

function updateBulkActionsVisibility() {
    const checkedCount = $('.employee-checkbox:checked').length;
    if (checkedCount > 0) {
        $('.bulk-actions').show();
        $('.bulk-count').text(checkedCount);
    } else {
        $('.bulk-actions').hide();
    }
}

function addNewEmployee() {
    // Open add employee modal
    openAddEmployeeModal();
}

function openAddEmployeeModal() {
    // Function is defined in employees.blade.php
    if (typeof window.openAddEmployeeModal === 'undefined') {
        // Check if modal exists
        if ($('#addEmployeeModal').length === 0) {
            showToast('Add employee modal not found. Please ensure the main employees page is loaded.', 'error');
            return;
        }
        
        $('#addEmployeeModal').data('action', 'create');
        if ($('#addEmployeeModalTitle').length > 0) {
            $('#addEmployeeModalTitle').html('<i class="bx bx-user-plus me-2"></i>Add New Employee');
        }
        if ($('#addEmployeeForm').length > 0) {
            $('#addEmployeeForm')[0].reset();
        }
        if ($('#add_user_id').length > 0) {
            $('#add_user_id').val('');
        }
        if (typeof currentStageIndex !== 'undefined') {
            currentStageIndex = 0;
            if (typeof switchTab === 'function') {
                switchTab('personal');
            }
            if ($('#personal-tab-btn').length > 0) {
                $('#personal-tab-btn').tab('show');
            }
        }
        $('#addEmployeeModal').modal('show');
    } else {
        window.openAddEmployeeModal();
    }
}

// Ensure viewEmployeeDetails is accessible
if (typeof window.viewEmployeeDetails === 'undefined') {
    window.viewEmployeeDetails = function(employeeId) {
        if (!employeeId) {
            showToast('Invalid employee ID.', 'error');
            return;
        }
        // This function should be defined in employees.blade.php
        // If not available, show error
        showToast('Employee details function not available. Please refresh the page.', 'error');
    };
}

// Ensure openEditEmployeeModal is accessible
if (typeof window.openEditEmployeeModal === 'undefined') {
    window.openEditEmployeeModal = function(employeeId) {
        if (!employeeId) {
            showToast('Invalid employee ID.', 'error');
            return;
        }
        // This function should be defined in employees.blade.php
        // If not available, show error
        showToast('Edit employee function not available. Please refresh the page.', 'error');
    };
}

// Ensure openUploadPhotoModal is accessible
if (typeof window.openUploadPhotoModal === 'undefined') {
    window.openUploadPhotoModal = function(employeeId) {
        if (!employeeId) {
            showToast('Invalid employee ID.', 'error');
            return;
        }
        // This function should be defined in employees.blade.php
        // If not available, show error
        showToast('Upload photo function not available. Please refresh the page.', 'error');
    };
}

// Ensure toggleEmployeeStatus is accessible and uses toast notifications
if (typeof window.toggleEmployeeStatus === 'undefined') {
    window.toggleEmployeeStatus = function(employeeId) {
        if (!employeeId) {
            showToast('Invalid employee ID.', 'error');
            return;
        }
        
        Swal.fire({
            title: 'Change Employee Status?',
            text: 'Are you sure you want to change this employee\'s status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Change',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#696cff'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("employees.toggle-status", ":id") }}'.replace(':id', employeeId),
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast(response.message || 'Employee status updated successfully.', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast(response.message || 'Failed to update employee status.', 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        const errorMsg = response && response.message ? response.message : 'An error occurred while changing status.';
                        showToast(errorMsg, 'error');
                    }
                });
            }
        });
    };
}

function exportEmployees() {
    console.log('exportEmployees function called');
    try {
        Swal.fire({
            title: 'Export Employees',
            text: 'Choose export format',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Excel',
            cancelButtonText: 'CSV',
            showDenyButton: true,
            denyButtonText: 'PDF',
            confirmButtonColor: '#696cff'
        }).then((result) => {
            const exportUrl = '{{ route("modules.hr.employees") }}';
            console.log('Export result:', result);
            
            if (result.isConfirmed) {
                showToast('Preparing Excel export...', 'info', 2000);
                const url = exportUrl + '?export=excel';
                console.log('Opening Excel export:', url);
                window.open(url, '_blank');
            } else if (result.isDenied) {
                showToast('Preparing PDF export...', 'info', 2000);
                const url = exportUrl + '?export=pdf';
                console.log('Opening PDF export:', url);
                window.open(url, '_blank');
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                showToast('Preparing CSV export...', 'info', 2000);
                const url = exportUrl + '?export=excel';
                console.log('Opening CSV export:', url);
                window.open(url, '_blank');
            }
        }).catch(error => {
            console.error('Export error:', error);
            showToast('Error opening export. Please try again.', 'error');
        });
    } catch (error) {
        console.error('exportEmployees error:', error);
        showToast('Error: ' + error.message, 'error');
    }
}

function generateReport() {
    console.log('generateReport function called');
    try {
        const reportUrl = '{{ route("modules.hr.employees.report") }}';
        console.log('Report URL:', reportUrl);
        
        Swal.fire({
            title: 'Generate Report',
            text: 'Select report type',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Employee Summary',
            cancelButtonText: 'Department Report',
            showDenyButton: true,
            denyButtonText: 'Salary Analysis',
            confirmButtonColor: '#696cff'
        }).then((result) => {
            console.log('Report result:', result);
            
            if (result.isConfirmed) {
                showToast('Generating employee summary report...', 'info', 2000);
                const url = reportUrl + '?type=summary&format=pdf';
                console.log('Opening summary report:', url);
                window.open(url, '_blank');
            } else if (result.isDenied) {
                showToast('Generating salary analysis report...', 'info', 2000);
                const url = reportUrl + '?type=salary&format=pdf';
                console.log('Opening salary report:', url);
                window.open(url, '_blank');
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                showToast('Generating department report...', 'info', 2000);
                const url = reportUrl + '?type=department&format=pdf';
                console.log('Opening department report:', url);
                window.open(url, '_blank');
            }
        }).catch(error => {
            console.error('Report generation error:', error);
            showToast('Error generating report. Please try again.', 'error');
        });
    } catch (error) {
        console.error('generateReport error:', error);
        showToast('Error: ' + error.message, 'error');
    }
}

function bulkActions() {
    console.log('bulkActions function called');
    try {
        const checkedCount = $('.employee-checkbox:checked').length;
        console.log('Checked employees:', checkedCount);
        
        if (checkedCount === 0) {
            showToast('Please select employees first.', 'warning');
            return;
        }
    
        Swal.fire({
            title: 'Bulk Actions',
            text: `${checkedCount} employee(s) selected`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Activate All',
            cancelButtonText: 'Deactivate All',
            showDenyButton: true,
            denyButtonText: 'Export Selected',
            confirmButtonColor: '#696cff'
        }).then((result) => {
            if (result.isConfirmed) {
                bulkActivateEmployees();
            } else if (result.isDenied) {
                exportSelectedEmployees();
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                bulkDeactivateEmployees();
            }
        }).catch(error => {
            console.error('Bulk actions error:', error);
            showToast('Error performing bulk action. Please try again.', 'error');
        });
    } catch (error) {
        console.error('bulkActions error:', error);
        showToast('Error: ' + error.message, 'error');
    }
}

function bulkActivateEmployees() {
    const selectedIds = $('.employee-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        showToast('Please select at least one employee.', 'warning');
        return;
    }
    
    $.ajax({
        url: '{{ route("modules.hr.employees.bulk-action") }}',
        type: 'POST',
        data: { action: 'activate', employee_ids: selectedIds },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                showToast(response.message || `${selectedIds.length} employee(s) activated successfully.`, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message || 'Failed to activate employees.', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const errorMsg = response && response.message ? response.message : 'An error occurred during bulk activation.';
            showToast(errorMsg, 'error');
        }
    });
}

function bulkDeactivateEmployees() {
    const selectedIds = $('.employee-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        showToast('Please select at least one employee.', 'warning');
        return;
    }
    
    $.ajax({
        url: '{{ route("modules.hr.employees.bulk-action") }}',
        type: 'POST',
        data: { action: 'deactivate', employee_ids: selectedIds },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                showToast(response.message || `${selectedIds.length} employee(s) deactivated successfully.`, 'success');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showToast(response.message || 'Failed to deactivate employees.', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const errorMsg = response && response.message ? response.message : 'An error occurred during bulk deactivation.';
            showToast(errorMsg, 'error');
        }
    });
}

function exportSelectedEmployees() {
    const selectedIds = $('.employee-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selectedIds.length === 0) {
        showToast('Please select at least one employee to export.', 'warning');
        return;
    }
    
    const params = new URLSearchParams();
    selectedIds.forEach(id => params.append('employee_ids[]', id));
    
    showToast(`Exporting ${selectedIds.length} selected employee(s)...`, 'info', 2000);
    window.open(`/employees/export/selected?${params.toString()}`, '_blank');
}

function refreshData() {
    console.log('refreshData function called');
    try {
        Swal.fire({
            title: 'Refresh Data',
            text: 'This will reload all employee data',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Refresh',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#696cff'
        }).then((result) => {
            console.log('Refresh result:', result);
            if (result.isConfirmed) {
                showToast('Refreshing employee data...', 'info', 1000);
                setTimeout(() => {
                    console.log('Reloading page...');
                    location.reload();
                }, 500);
            }
        }).catch(error => {
            console.error('Refresh error:', error);
            showToast('Error refreshing data. Please try again.', 'error');
        });
    } catch (error) {
        console.error('refreshData error:', error);
        showToast('Error: ' + error.message, 'error');
    }
}

function syncAllEmployees() {
    console.log('syncAllEmployees function called');
    
    Swal.fire({
        title: 'Sync All Users',
        html: `
            <div class="text-start">
                <p class="mb-3">This will create employee records for all users that don't have one.</p>
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong>Note:</strong> This process may take a few moments depending on the number of users.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bx bx-sync me-1"></i>Yes, Sync All',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ff9800',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const syncUrl = '{{ route("employees.sync-all") }}';
            console.log('Syncing employees via:', syncUrl);
            
            return $.ajax({
                url: syncUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                timeout: 60000, // Increased to 60 seconds
                dataType: 'json'
            }).then(response => {
                console.log('Sync response received:', response);
                if (response && response.success) {
                    return response;
                } else {
                    const errorMsg = response?.message || 'Sync failed without error message';
                    console.error('Sync failed:', errorMsg);
                    throw new Error(errorMsg);
                }
            }).catch(error => {
                console.error('Sync AJAX error:', error);
                let errorMessage = 'Error syncing employees. Please try again.';
                
                if (error.responseJSON && error.responseJSON.message) {
                    errorMessage = error.responseJSON.message;
                } else if (error.message) {
                    errorMessage = error.message;
                } else if (error.statusText) {
                    errorMessage = `Request failed: ${error.statusText}`;
                }
                
                Swal.showValidationMessage(errorMessage);
                return false;
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        console.log('Swal result:', result);
        
        if (result.isConfirmed && result.value && result.value.success) {
            Swal.fire({
                icon: 'success',
                title: 'Sync Completed!',
                html: `
                    <p>${result.value.message || 'Employees synced successfully.'}</p>
                    <p class="text-muted mb-0">${result.value.count || 0} employee record(s) created/updated.</p>
                `,
                confirmButtonText: 'OK',
                confirmButtonColor: '#ff9800'
            }).then(() => {
                location.reload();
            });
        } else if (result.isConfirmed && result.value && !result.value.success) {
            Swal.fire({
                icon: 'error',
                title: 'Sync Failed',
                text: result.value.message || 'Failed to sync employees. Please try again.',
                confirmButtonText: 'OK'
            });
        } else if (result.isDismissed) {
            console.log('User cancelled sync');
        } else {
            console.error('Unexpected result:', result);
        }
    }).catch(error => {
        console.error('Swal error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An unexpected error occurred. Please check the console for details.',
            confirmButtonText: 'OK'
        });
    });
}
</script>
@endpush

<style>
/* Enhanced Quick Actions Buttons */
.quick-action-btn {
    padding: 0.75rem 1.25rem;
    border-radius: 0.5rem;
    font-size: 0.95rem;
    position: relative;
    overflow: hidden;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15) !important;
}

.quick-action-btn.btn-danger:hover {
    background: linear-gradient(135deg, #c82333 0%, #bd2130 100%) !important;
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4) !important;
}

.quick-action-btn.btn-outline-warning:hover {
    background-color: #ff9800 !important;
    color: white !important;
    border-color: #ff9800 !important;
    box-shadow: 0 6px 20px rgba(255, 152, 0, 0.3) !important;
}

.quick-action-btn.btn-outline-success:hover {
    background-color: #28a745 !important;
    color: white !important;
    border-color: #28a745 !important;
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3) !important;
}

.quick-action-btn.btn-outline-info:hover {
    background-color: #17a2b8 !important;
    color: white !important;
    border-color: #17a2b8 !important;
    box-shadow: 0 6px 20px rgba(23, 162, 184, 0.3) !important;
}

.quick-action-btn.btn-outline-secondary:hover {
    background-color: #6c757d !important;
    color: white !important;
    border-color: #6c757d !important;
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3) !important;
}

.quick-action-btn:active {
    transform: translateY(0);
}

.quick-action-btn i {
    font-size: 1.1rem;
    vertical-align: middle;
}

/* Optimized table styles for better screen fit */
#employeeTable {
    font-size: 0.875rem;
    width: 100%;
}

#employeeTable thead th {
    white-space: nowrap;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.75rem 0.5rem;
}

#employeeTable tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
}

#employeeTable .avatar {
    width: 32px;
    height: 32px;
    font-size: 0.75rem;
}

#employeeTable .progress {
    min-width: 60px;
}

#listView {
    max-height: calc(100vh - 300px);
    overflow-y: auto;
}

/* Ensure table fits screen width */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

/* Compact badge styles */
.badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

.timeline {
    position: relative;
    padding-left: 30px;
    max-height: 600px;
    overflow-y: auto;
    overflow-x: hidden;
}

.timeline::-webkit-scrollbar {
    width: 6px;
}

.timeline::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.timeline::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.timeline::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.activity-item {
    transition: all 0.3s ease;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-content {
    padding-left: 15px;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
    font-size: 13px;
    color: #6c757d;
}

.bulk-actions {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 1000;
    display: none;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #696cff 0%, #8592a3 100%);
}

/* Fix dropdown z-index issues - ensure dropdowns appear above other elements */
.dropdown-menu {
    z-index: 1052 !important;
}

.employee-card .dropdown,
#employeeTable .dropdown {
    position: relative;
}

.employee-card .dropdown-menu,
#employeeTable .dropdown-menu {
    position: absolute !important;
    z-index: 1052 !important;
    margin-top: 0.125rem !important;
}

/* Advanced Pagination Styling - Fix large icons */
.pagination-wrapper {
    padding: 1rem 0;
}

.pagination-wrapper .pagination {
    margin-bottom: 0;
    font-size: 0.875rem;
}

.pagination-wrapper .pagination .page-link {
    padding: 0.375rem 0.625rem;
    font-size: 0.875rem;
    line-height: 1.4;
    min-width: 36px;
    height: 36px;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.pagination-wrapper .pagination .page-link:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.pagination-wrapper .pagination .page-link i,
.pagination-wrapper .pagination .page-link svg,
.pagination-wrapper .pagination .page-link .bx {
    font-size: 0.75rem !important;
    width: 0.75rem !important;
    height: 0.75rem !important;
    line-height: 1 !important;
    vertical-align: middle;
    display: inline-block;
}

.pagination-wrapper .pagination .page-item:first-child .page-link,
.pagination-wrapper .pagination .page-item:last-child .page-link {
    padding: 0.375rem 0.5rem;
    min-width: 36px;
}

.pagination-wrapper .pagination .page-item:first-child .page-link i,
.pagination-wrapper .pagination .page-item:last-child .page-link i,
.pagination-wrapper .pagination .page-item:first-child .page-link svg,
.pagination-wrapper .pagination .page-item:last-child .page-link svg {
    font-size: 0.75rem !important;
    width: 0.75rem !important;
    height: 0.75rem !important;
}

.pagination-wrapper .pagination .page-item.disabled .page-link {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

.pagination-wrapper .pagination .page-item.active .page-link {
    z-index: 3;
    color: #fff;
    background-color: #696cff;
    border-color: #696cff;
    font-weight: 600;
}

.pagination-wrapper .pagination .page-item .page-link {
    color: #696cff;
    border-color: #d9dee3;
}

.pagination-wrapper .pagination .page-item .page-link:hover {
    color: #5a5fd8;
    background-color: #f5f5f9;
    border-color: #696cff;
}

/* Ensure pagination spans don't have large icons */
.pagination-wrapper .pagination .page-link span {
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Fix any icon fonts that might be rendering large */
.pagination-wrapper .pagination .page-link * {
    max-width: 0.75rem;
    max-height: 0.75rem;
}

/* Override any large icon sizes with !important */
.pagination-wrapper .pagination .page-link i.bx,
.pagination-wrapper .pagination .page-link svg.bx,
.pagination-wrapper .pagination .page-link .bx {
    font-size: 0.75rem !important;
    width: 0.75rem !important;
    height: 0.75rem !important;
    line-height: 0.75rem !important;
}

/* Target Laravel pagination specific elements */
.pagination-wrapper .pagination li a span,
.pagination-wrapper .pagination li a i,
.pagination-wrapper .pagination li a svg {
    font-size: 0.75rem !important;
    width: 0.75rem !important;
    height: 0.75rem !important;
    display: inline-block;
    vertical-align: middle;
}

/* Ensure previous/next buttons have small icons */
.pagination-wrapper .pagination .page-item:first-child a,
.pagination-wrapper .pagination .page-item:last-child a {
    font-size: 0.75rem !important;
}

.pagination-wrapper .pagination .page-item:first-child a i,
.pagination-wrapper .pagination .page-item:first-child a svg,
.pagination-wrapper .pagination .page-item:last-child a i,
.pagination-wrapper .pagination .page-item:last-child a svg {
    font-size: 0.75rem !important;
    width: 0.75rem !important;
    height: 0.75rem !important;
    margin: 0 !important;
}

/* Fix any nested elements */
.pagination-wrapper .pagination .page-link > * {
    font-size: 0.75rem !important;
    max-width: 0.75rem !important;
    max-height: 0.75rem !important;
}
</style>
