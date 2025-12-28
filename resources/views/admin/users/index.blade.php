@extends('layouts.app')

@section('title', 'User Management - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Advanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-lg">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-lg me-3">
                                    <span class="avatar-initial rounded-circle bg-white text-primary">
                                        <i class="bx bx-user-circle fs-1"></i>
                                    </span>
                                </div>
                                <div>
                                    <h4 class="card-title text-white mb-1">User Management System</h4>
                                    <p class="card-text text-white-50 mb-0">
                                        Comprehensive user management with advanced analytics, role management, and access controls
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <div class="dropdown" style="position: relative; z-index: 1060;">
                                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" style="z-index: 1061;">
                                        <i class="bx bx-dots-vertical-rounded me-1"></i>Quick Actions
                                    </button>
                                    <ul class="dropdown-menu" style="z-index: 1062; position: absolute;">
                                        <li><a class="dropdown-item" href="{{ route('modules.hr.employees.register') }}">
                                            <i class="bx bx-user-plus me-2"></i>Register New Employee
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="#" onclick="exportUsers()">
                                            <i class="bx bx-download me-2"></i>Export All Users
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="bulkUserActions()">
                                            <i class="bx bx-check-square me-2"></i>Bulk Actions
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="generateUserReport()">
                                            <i class="bx bx-bar-chart me-2"></i>Generate Report
                                        </a></li>
                                    </ul>
                                </div>
                                <button class="btn btn-light" onclick="refreshUserData()">
                                    <i class="bx bx-refresh me-1"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Statistics Dashboard -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0 hover-lift">
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
                                    <h6 class="mb-1">Total Users</h6>
                                    <h4 class="mb-0 text-primary">{{ $stats['total'] ?? 0 }}</h4>
                                </div>
                                <div class="text-end">
                                    <small class="text-success">
                                        <i class="bx bx-trending-up me-1"></i>+{{ $stats['recent_registrations'] ?? 0 }}
                                    </small>
                                </div>
                            </div>
                            <small class="text-muted">All registered users</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0 hover-lift">
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
                                    <h6 class="mb-1">Active Users</h6>
                                    <h4 class="mb-0 text-success">{{ $stats['active'] ?? 0 }}</h4>
                                </div>
                                <div class="text-end">
                                    <small class="text-success">
                                        {{ $stats['total'] > 0 ? number_format(($stats['active'] / $stats['total']) * 100, 1) : 0 }}%
                                    </small>
                                </div>
                            </div>
                            <small class="text-muted">Currently active</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3">
                            <span class="avatar-initial rounded-circle bg-label-danger">
                                <i class="bx bx-x-circle fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">Inactive Users</h6>
                                    <h4 class="mb-0 text-danger">{{ $stats['inactive'] ?? 0 }}</h4>
                                </div>
                                <div class="text-end">
                                    <small class="text-danger">
                                        {{ $stats['total'] > 0 ? number_format(($stats['inactive'] / $stats['total']) * 100, 1) : 0 }}%
                                    </small>
                                </div>
                            </div>
                            <small class="text-muted">Deactivated accounts</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
            <div class="card h-100 shadow-sm border-0 hover-lift">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-calendar fs-4"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">New (30 days)</h6>
                                    <h4 class="mb-0 text-info">{{ $stats['recent_registrations'] ?? 0 }}</h4>
                                </div>
                                <div class="text-end">
                                    <small class="text-info">
                                        <i class="bx bx-user-plus me-1"></i>Recent
                                    </small>
                                </div>
                            </div>
                            <small class="text-muted">Last 30 days</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Panel -->
    <div class="row mb-4">
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('modules.hr.employees.register') }}" class="btn btn-danger btn-lg quick-action-btn" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; color: white; font-weight: 600; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3); transition: all 0.3s ease;">
                            <i class="bx bx-user-plus me-2"></i>Register New Employee
                        </a>
                        
                        <button type="button" class="btn btn-outline-success btn-lg quick-action-btn" onclick="exportUsers()" style="border-color: #28a745; color: #28a745; font-weight: 500; transition: all 0.3s ease;">
                            <i class="bx bx-download me-2"></i>Export User Data
                        </button>
                        
                        <button type="button" class="btn btn-outline-info btn-lg quick-action-btn" onclick="generateUserReport()" style="border-color: #17a2b8; color: #17a2b8; font-weight: 500; transition: all 0.3s ease;">
                            <i class="bx bx-bar-chart me-2"></i>Generate Report
                        </button>
                        
                        <button type="button" class="btn btn-outline-warning btn-lg quick-action-btn" onclick="bulkUserActions()" style="border-color: #ff9800; color: #ff9800; font-weight: 500; transition: all 0.3s ease;">
                            <i class="bx bx-check-square me-2"></i>Bulk Actions
                        </button>
                        
                        <button type="button" class="btn btn-outline-secondary btn-lg quick-action-btn" onclick="refreshUserData()" style="border-color: #6c757d; color: #6c757d; font-weight: 500; transition: all 0.3s ease;">
                            <i class="bx bx-refresh me-2"></i>Refresh Data
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics by Role and Department -->
        <div class="col-lg-8 mb-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-label-info">
                            <h6 class="card-title mb-0 text-white">
                                <i class="bx bx-shield me-2"></i>Users by Role
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @forelse($stats['by_role'] ?? [] as $roleStat)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <span class="badge bg-label-info me-2">{{ $roleStat->display_name ?? $roleStat->name }}</span>
                                    </div>
                                    <strong class="text-primary">{{ $roleStat->count }}</strong>
                                </div>
                                @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bx bx-info-circle fs-4 d-block mb-2"></i>
                                    No role statistics available
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-label-secondary">
                            <h6 class="card-title mb-0 text-white">
                                <i class="bx bx-building me-2"></i>Users by Department
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @forelse($stats['by_department'] ?? [] as $deptStat)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <span class="badge bg-label-secondary me-2">{{ $deptStat->primaryDepartment->name ?? 'Unassigned' }}</span>
                                    </div>
                                    <strong class="text-secondary">{{ $deptStat->count }}</strong>
                                </div>
                                @empty
                                <div class="text-center text-muted py-3">
                                    <i class="bx bx-info-circle fs-4 d-block mb-2"></i>
                                    No department statistics available
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filter and Search Panel -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="bx bx-filter-alt me-2"></i>Advanced Filters & Search
                        </h6>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                            <i class="bx bx-chevron-down me-1"></i>Toggle Filters
                        </button>
                    </div>
                </div>
                <div class="collapse show" id="filterPanel">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm" onsubmit="return false;">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Search User <span class="text-muted small">(Live Search)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" name="search" id="liveSearchInput" class="form-control" 
                                               placeholder="Name, email, ID, phone..." 
                                               value="{{ request('search') }}"
                                               autocomplete="off">
                                        <button type="button" class="btn btn-outline-secondary" id="clearSearchBtn" style="display: none;">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bx bx-info-circle"></i> Type to search instantly
                                    </small>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Role</label>
                                    <select name="role" class="form-select">
                                        <option value="">All Roles</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Department</label>
                                    <select name="department" class="form-select">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $department)
                                        <option value="{{ $department->id }}" {{ request('department') == $department->id ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Sort By</label>
                                    <select name="sort_by" class="form-select">
                                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                                        <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name</option>
                                        <option value="email" {{ request('sort_by') === 'email' ? 'selected' : '' }}>Email</option>
                                        <option value="employee_id" {{ request('sort_by') === 'employee_id' ? 'selected' : '' }}>Employee ID</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Order</label>
                                    <select name="sort_order" class="form-select">
                                        <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Desc</option>
                                        <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Asc</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" onclick="performLiveSearch()">
                                        <i class="bx bx-filter"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearAllFilters()">
                                        <i class="bx bx-x"></i> Clear Filters
                                    </button>
                                    <span class="text-muted ms-3" id="paginationInfoTop">
                                        Showing <strong>{{ $users->firstItem() ?? 0 }}</strong> to <strong>{{ $users->lastItem() ?? 0 }}</strong> of <strong>{{ $users->total() }}</strong> users
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">All Users</h5>
                        <p class="text-muted mb-0 small">Manage user accounts, roles, and permissions</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-success" id="bulkActionsBtn" disabled>
                            <i class="bx bx-check-square"></i> Bulk Actions (<span id="selectedCount">0</span>)
                        </button>
                        <a href="{{ route('admin.users.export') }}" class="btn btn-outline-primary" id="exportBtn">
                            <i class="bx bx-download"></i> Export CSV
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="usersTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Employee ID</th>
                                    <th>Roles</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersTableBody">
                                @forelse($users as $user)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                @if($user->photo)
                                                <img src="{{ route('storage.photos', ['filename' => $user->photo]) }}" alt="{{ $user->name }}" class="rounded-circle" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Crect fill=\'%23ddd\' width=\'100\' height=\'100\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'50\' dy=\'.3em\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3E{{ strtoupper(substr($user->name, 0, 2)) }}%3C/text%3E%3C/svg%3E';">
                                                @else
                                                <span class="avatar-initial rounded-circle bg-label-primary">
                                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                                </span>
                                                @endif
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $user->name }}</h6>
                                                <small class="text-muted">
                                                    @if($user->phone || $user->mobile)
                                                        {{ $user->phone ?? $user->mobile }}
                                                    @else
                                                        <span class="text-warning">
                                                            <i class="bx bx-info-circle me-1"></i>No phone number registered. Please contact administrator.
                                                        </span>
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary">{{ $user->employee_id ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        @foreach($user->roles as $role)
                                        <span class="badge bg-label-info me-1">{{ $role->display_name }}</span>
                                        @endforeach
                                        @if($user->roles->isEmpty())
                                        <span class="text-muted small">No roles</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary">{{ $user->primaryDepartment->name ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                            <i class="bx bx-{{ $user->is_active ? 'check-circle' : 'x-circle' }} me-1"></i>
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $user->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button class="dropdown-item btn-view-user" data-user-id="{{ $user->id }}">
                                                        <i class="bx bx-show me-2"></i>View Details
                                                    </button>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.users.edit', $user) }}">
                                                        <i class="bx bx-edit me-2"></i>Edit User
                                                    </a>
                                                    @if(auth()->user()->hasRole('System Admin'))
                                                    <button class="dropdown-item text-warning" onclick="refreshEmailVerification({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                                        <i class="bx bx-refresh me-2"></i>Refresh Email Verification
                                                    </button>
                                                    @endif
                                                </li>
                                                @if($user->employee_id)
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('employees.show', $user->id) }}">
                                                        <i class="bx bx-user me-2"></i>View Employee Details
                                                    </a>
                                                </li>
                                                @endif
                                                <li>
                                                    <button class="dropdown-item toggle-status-btn" 
                                                            data-user-id="{{ $user->id }}" 
                                                            data-status="{{ $user->is_active ? 'active' : 'inactive' }}">
                                                        <i class="bx bx-{{ $user->is_active ? 'user-x' : 'user-check' }} me-2"></i>
                                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </li>
                                                @if($user->id !== auth()->id() && !$user->hasRole('System Admin'))
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item text-danger btn-delete-user" 
                                                            data-user-id="{{ $user->id }}" 
                                                            data-user-name="{{ $user->name }}">
                                                        <i class="bx bx-trash me-2"></i>Delete User
                                                    </button>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bx bx-user-x fs-1 d-block mb-2"></i>
                                            <h5>No users found</h5>
                                            <p>No users match your search criteria. Try adjusting your filters.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($users->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div>
                            <span class="text-muted">
                                Showing <strong>{{ $users->firstItem() ?? 0 }}</strong> to <strong>{{ $users->lastItem() ?? 0 }}</strong> of <strong>{{ $users->total() }}</strong> users
                            </span>
                        </div>
                        <nav aria-label="User pagination">
                            <ul class="pagination mb-0">
                                {{-- Previous Page Link --}}
                                @if ($users->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-disabled="true">
                                            <i class="bx bx-chevron-left"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev" aria-label="Previous">
                                            <i class="bx bx-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @php
                                    $currentPage = $users->currentPage();
                                    $lastPage = $users->lastPage();
                                    $startPage = max(1, $currentPage - 2);
                                    $endPage = min($lastPage, $currentPage + 2);
                                @endphp
                                
                                @if($startPage > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->url(1) }}">1</a>
                                    </li>
                                    @if($startPage > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endif
                                
                                @for($page = $startPage; $page <= $endPage; $page++)
                                    @if ($page == $currentPage)
                                        <li class="page-item active" aria-current="page">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $users->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @endif
                                @endfor
                                
                                @if($endPage < $lastPage)
                                    @if($endPage < $lastPage - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->url($lastPage) }}">{{ $lastPage }}</a>
                                    </li>
                                @endif

                                {{-- Next Page Link --}}
                                @if ($users->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next" aria-label="Next">
                                            <i class="bx bx-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link" aria-disabled="true">
                                            <i class="bx bx-chevron-right"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                    @else
                    <div class="d-flex justify-content-center mt-4">
                        <span class="text-muted" id="paginationInfo">
                            Showing <strong>{{ $users->firstItem() ?? 0 }}</strong> to <strong>{{ $users->lastItem() ?? 0 }}</strong> of <strong>{{ $users->total() }}</strong> users
                        </span>
                    </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced View User Modal -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewUserModalLabel">
                    <i class="bx bx-user me-2"></i>User Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewUserContent">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading user details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Enhanced Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1" aria-labelledby="bulkActionsModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="bulkActionsModalLabel">
                    <i class="bx bx-check-square me-2"></i>Bulk Actions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bx bx-info-circle me-2"></i>
                    <strong id="bulkSelectedCount">0</strong> user(s) selected
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" id="bulkActivate">
                        <i class="bx bx-user-check me-2"></i>Activate Selected
                    </button>
                    <button type="button" class="btn btn-warning" id="bulkDeactivate">
                        <i class="bx bx-user-x me-2"></i>Deactivate Selected
                    </button>
                    @if(auth()->user()->hasRole('System Admin'))
                    <button type="button" class="btn btn-outline-warning" onclick="bulkRefreshEmailVerification()">
                        <i class="bx bx-refresh me-2"></i>Refresh Email Verification
                    </button>
                    @endif
                    <button type="button" class="btn btn-danger" id="bulkDelete">
                        <i class="bx bx-trash me-2"></i>Delete Selected
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.hover-lift {
    transition: all 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

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

.quick-action-btn.btn-outline-warning:hover {
    background-color: #ff9800 !important;
    color: white !important;
    border-color: #ff9800 !important;
    box-shadow: 0 6px 20px rgba(255, 152, 0, 0.3) !important;
}

.quick-action-btn.btn-outline-secondary:hover {
    background-color: #6c757d !important;
    color: white !important;
    border-color: #6c757d !important;
    box-shadow: 0 6px 20px rgba(108, 117, 125, 0.3) !important;
}

#usersTable {
    font-size: 0.9rem;
}

#usersTable thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 12px 10px;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

#usersTable tbody tr {
    transition: all 0.2s ease;
}

#usersTable tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
}

#usersTable tbody td {
    padding: 12px 10px;
    vertical-align: middle;
}

.avatar img {
    object-fit: cover;
}

/* Custom Pagination Styling */
.pagination {
    margin-bottom: 0;
}

.pagination .page-item .page-link {
    color: #940000;
    border-color: #dee2e6;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

.pagination .page-item .page-link:hover {
    background-color: #f8f9fa;
    border-color: #940000;
    color: #940000;
}

.pagination .page-item.active .page-link {
    background-color: #940000;
    border-color: #940000;
    color: white;
}

.pagination .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    cursor: not-allowed;
}
</style>
@endpush

@push('scripts')
<script>
let searchTimeout;
let currentUserId = {{ auth()->id() }};
let currentFilters = {
    search: '{{ request('search') }}',
    status: '{{ request('status') }}',
    role: '{{ request('role') }}',
    department: '{{ request('department') }}',
    sort_by: '{{ request('sort_by', 'created_at') }}',
    sort_order: '{{ request('sort_order', 'desc') }}',
    page: 1
};

$(document).ready(function() {
    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Live Search Implementation
    $('#liveSearchInput').on('input', function() {
        const searchValue = $(this).val().trim();
        currentFilters.search = searchValue;
        currentFilters.page = 1;
        
        // Show/hide clear button
        if (searchValue.length > 0) {
            $('#clearSearchBtn').show();
        } else {
            $('#clearSearchBtn').hide();
        }
        
        // Debounce search - wait 500ms after user stops typing
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performLiveSearch();
        }, 500);
    });

    // Clear search button
    $('#clearSearchBtn').on('click', function() {
        $('#liveSearchInput').val('');
        currentFilters.search = '';
        currentFilters.page = 1;
        $(this).hide();
        performLiveSearch();
    });

    // Update filters when dropdowns change
    $('select[name="status"], select[name="role"], select[name="department"], select[name="sort_by"], select[name="sort_order"]').on('change', function() {
        currentFilters.status = $('select[name="status"]').val();
        currentFilters.role = $('select[name="role"]').val();
        currentFilters.department = $('select[name="department"]').val();
        currentFilters.sort_by = $('select[name="sort_by"]').val();
        currentFilters.sort_order = $('select[name="sort_order"]').val();
        currentFilters.page = 1;
        performLiveSearch();
    });

    // Function to perform live search
    function performLiveSearch() {
        // Show loading indicator
        const tbody = $('#usersTableBody');
        tbody.html(`
            <tr>
                <td colspan="9" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Searching...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Searching users...</p>
                </td>
            </tr>
        `);

        // Build query string
        const queryParams = new URLSearchParams();
        if (currentFilters.search) queryParams.append('search', currentFilters.search);
        if (currentFilters.status) queryParams.append('status', currentFilters.status);
        if (currentFilters.role) queryParams.append('role', currentFilters.role);
        if (currentFilters.department) queryParams.append('department', currentFilters.department);
        if (currentFilters.sort_by) queryParams.append('sort_by', currentFilters.sort_by);
        if (currentFilters.sort_order) queryParams.append('sort_order', currentFilters.sort_order);
        if (currentFilters.page > 1) queryParams.append('page', currentFilters.page);

        // Make AJAX request
        $.ajax({
            url: '{{ route("admin.users.index") }}?' + queryParams.toString(),
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    renderUsersTable(response.users, response.pagination);
                    updatePaginationInfo(response.pagination);
                } else {
                    tbody.html(`
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bx bx-error-circle fs-1 d-block mb-2"></i>
                                    <p>Error loading users. Please try again.</p>
                                </div>
                            </td>
                        </tr>
                    `);
                }
            },
            error: function(xhr) {
                console.error('Search error:', xhr);
                tbody.html(`
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-danger">
                                <i class="bx bx-error-circle fs-1 d-block mb-2"></i>
                                <p>Error searching users. Please refresh the page.</p>
                            </div>
                        </td>
                    </tr>
                `);
            }
        });
    }

    // Function to render users table
    function renderUsersTable(users, pagination) {
        const tbody = $('#usersTableBody');
        
        if (!users || users.length === 0) {
            tbody.html(`
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="text-muted">
                            <i class="bx bx-user-x fs-1 d-block mb-2"></i>
                            <h5>No users found</h5>
                            <p>No users match your search criteria. Try adjusting your filters.</p>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        let html = '';
        users.forEach(function(user) {
            const userInitials = (user.name || 'U').substring(0, 2).toUpperCase();
            const photoUrl = user.photo ? `/storage/photos/${user.photo}` : '';
            const rolesHtml = (user.roles || []).map(role => 
                `<span class="badge bg-label-info me-1">${role.display_name || role.name}</span>`
            ).join('') || '<span class="text-muted small">No roles</span>';
            const departmentName = user.primary_department ? user.primary_department.name : 'N/A';
            const statusBadge = user.is_active ? 
                '<span class="badge bg-success"><i class="bx bx-check-circle me-1"></i>Active</span>' : 
                '<span class="badge bg-danger"><i class="bx bx-x-circle me-1"></i>Inactive</span>';
            const updatedAt = user.updated_at ? new Date(user.updated_at).toLocaleDateString() : 'N/A';
            
            html += `
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input user-checkbox" value="${user.id}">
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                ${photoUrl ? 
                                    `<img src="${photoUrl}" alt="${user.name}" class="rounded-circle" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\'%3E%3Crect fill=\'%23ddd\' width=\'100\' height=\'100\'/%3E%3Ctext fill=\'%23999\' font-family=\'sans-serif\' font-size=\'50\' dy=\'.3em\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\'%3E${userInitials}%3C/text%3E%3C/svg%3E';">` :
                                    `<span class="avatar-initial rounded-circle bg-label-primary">${userInitials}</span>`
                                }
                            </div>
                            <div>
                                <h6 class="mb-0">${user.name || 'N/A'}</h6>
                                <small class="text-muted">${user.phone || 'No phone'}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <a href="mailto:${user.email}" class="text-decoration-none">${user.email || 'N/A'}</a>
                    </td>
                    <td>
                        <span class="badge bg-label-secondary">${user.employee_id || 'N/A'}</span>
                    </td>
                    <td>${rolesHtml}</td>
                    <td>
                        <span class="badge bg-label-secondary">${departmentName}</span>
                    </td>
                    <td>${statusBadge}</td>
                    <td>
                        <small class="text-muted">${updatedAt}</small>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <button class="dropdown-item btn-view-user" data-user-id="${user.id}">
                                        <i class="bx bx-show me-2"></i>View Details
                                    </button>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/admin/users/${user.id}/edit">
                                        <i class="bx bx-edit me-2"></i>Edit User
                                    </a>
                                </li>
                                ${user.employee_id ? `
                                <li>
                                    <a class="dropdown-item" href="/employees/${user.id}">
                                        <i class="bx bx-user me-2"></i>View Employee Details
                                    </a>
                                </li>
                                ` : ''}
                                <li>
                                    <button class="dropdown-item toggle-status-btn" 
                                            data-user-id="${user.id}" 
                                            data-status="${user.is_active ? 'active' : 'inactive'}">
                                        <i class="bx bx-${user.is_active ? 'user-x' : 'user-check'} me-2"></i>
                                        ${user.is_active ? 'Deactivate' : 'Activate'}
                                    </button>
                                </li>
                                ${user.id !== currentUserId && !(user.roles || []).some(r => (r.name || '').toLowerCase() === 'system admin') ? `
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item text-danger btn-delete-user" 
                                            data-user-id="${user.id}" 
                                            data-user-name="${user.name || 'User'}">
                                        <i class="bx bx-trash me-2"></i>Delete User
                                    </button>
                                </li>
                                ` : ''}
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
        });
        
        tbody.html(html);
        
        // Re-initialize checkboxes
        updateBulkActionsButton();
    }

    // Function to update pagination info
    function updatePaginationInfo(pagination) {
        if (pagination) {
            const infoText = `Showing <strong>${pagination.from || 0}</strong> to <strong>${pagination.to || 0}</strong> of <strong>${pagination.total || 0}</strong> users`;
            $('#paginationInfo').html(infoText);
            $('#paginationInfoTop').html(infoText);
        }
    }

    // Clear all filters function
    function clearAllFilters() {
        $('#liveSearchInput').val('');
        $('select[name="status"]').val('');
        $('select[name="role"]').val('');
        $('select[name="department"]').val('');
        $('select[name="sort_by"]').val('created_at');
        $('select[name="sort_order"]').val('desc');
        $('#clearSearchBtn').hide();
        
        currentFilters = {
            search: '',
            status: '',
            role: '',
            department: '',
            sort_by: 'created_at',
            sort_order: 'desc',
            page: 1
        };
        
        performLiveSearch();
    }

    // View User (modal)
    $(document).on('click', '.btn-view-user', function() {
        const userId = $(this).data('user-id');
        $('#viewUserContent').html(`
            <div class="text-center py-4 text-muted">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading user details...</p>
            </div>
        `);
        const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
        modal.show();

        $.ajax({
            url: `{{ route("admin.users.show", ":id") }}`.replace(':id', userId),
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            success: function(resp) {
                if (resp.success && resp.user) {
                    const u = resp.user;
                    const roles = (u.roles || []).map(r => 
                        `<span class="badge bg-label-info me-1">${r.display_name || r.name}</span>`
                    ).join(' ');
                    const departments = (u.departments || []).map(d => 
                        `<span class="badge bg-label-secondary me-1">${d.name}</span>`
                    ).join(' ');
                    
                    const html = `
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Full Name</label>
                                <div class="form-control bg-light">${u.name || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Email Address</label>
                                <div class="form-control bg-light">${u.email || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Employee ID</label>
                                <div class="form-control bg-light">${u.employee_id || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Phone Number</label>
                                <div class="form-control bg-light">${u.phone || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Hire Date</label>
                                <div class="form-control bg-light">${u.hire_date || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Primary Department</label>
                                <div class="form-control bg-light">${u.primary_department?.name || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Status</label>
                                <div>
                                    ${u.is_active ? 
                                        '<span class="badge bg-success">Active</span>' : 
                                        '<span class="badge bg-danger">Inactive</span>'
                                    }
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Created At</label>
                                <div class="form-control bg-light">${u.created_at || 'N/A'}</div>
                            </div>
                            <div class="col-12"><hr></div>
                            <div class="col-12">
                                <label class="form-label text-muted">Assigned Roles</label>
                                <div>${roles || '<span class="text-muted">No roles assigned</span>'}</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted">Departments</label>
                                <div>${departments || '<span class="text-muted">No departments assigned</span>'}</div>
                            </div>
                        </div>`;
                    $('#viewUserContent').html(html);
                } else {
                    $('#viewUserContent').html('<div class="text-danger">Failed to load user details.</div>');
                }
            },
            error: function() {
                $('#viewUserContent').html('<div class="text-danger">Failed to load user details.</div>');
            }
        });
    });

    // Delete user (AJAX)
    $(document).on('click', '.btn-delete-user', function() {
        const userId = $(this).data('user-id');
        const userName = $(this).data('user-name');
        
            // Use SweetAlert for confirmation, then toast for result
            Swal.fire({
                title: 'Delete User?',
                text: `Are you sure you want to delete "${userName}"? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#dc3545'
            }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ route("admin.users.destroy", ":id") }}`.replace(':id', userId),
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
                    success: function(resp) {
                        if (resp.success) {
                            window.toastSuccess('Deleted!', resp.message || 'User deleted successfully', { duration: 3000 });
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            window.toastError('Error!', resp.message || 'Delete failed', { duration: 5000 });
                        }
                    },
                    error: function(xhr) {
                        const msg = xhr.responseJSON?.message || 'Failed to delete user';
                        window.toastError('Error!', msg, { duration: 5000 });
                    }
                });
            }
        });
    });

    // Toggle user status
    $(document).on('click', '.toggle-status-btn', function() {
        const userId = $(this).data('user-id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        
        Swal.fire({
            title: `${action.charAt(0).toUpperCase() + action.slice(1)} User?`,
            text: `Are you sure you want to ${action} this user?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Yes, ${action.charAt(0).toUpperCase() + action.slice(1)}`,
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `{{ route("admin.users.toggle-status", ":id") }}`.replace(':id', userId),
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if (response.success) {
                            window.toastSuccess('Success!', response.message, { duration: 3000 });
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            window.toastError('Error!', response.message || 'Error updating user status', { duration: 5000 });
                        }
                    },
                    error: function() {
                        window.toastError('Error!', 'Error updating user status', { duration: 5000 });
                    }
                });
            }
        });
    });

    // Select All functionality
    $('#selectAll').on('change', function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActionsButton();
    });

    // Individual checkbox change
    $(document).on('change', '.user-checkbox', function() {
        updateBulkActionsButton();
        const totalCheckboxes = $('.user-checkbox').length;
        const checkedCheckboxes = $('.user-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Bulk Actions Button
    $('#bulkActionsBtn').on('click', function() {
        const selectedCount = $('.user-checkbox:checked').length;
        $('#bulkSelectedCount').text(selectedCount);
        $('#bulkActionsModal').modal('show');
    });

    // Bulk Actions
    $('#bulkActivate').on('click', function() {
        const selectedIds = getSelectedUserIds();
        if (selectedIds.length === 0) {
            Swal.fire('Warning', 'Please select at least one user', 'warning');
            return;
        }
        performBulkAction(selectedIds, 'activate');
    });

    $('#bulkDeactivate').on('click', function() {
        const selectedIds = getSelectedUserIds();
        if (selectedIds.length === 0) {
            Swal.fire('Warning', 'Please select at least one user', 'warning');
            return;
        }
        performBulkAction(selectedIds, 'deactivate');
    });

    $('#bulkDelete').on('click', function() {
        const selectedIds = getSelectedUserIds();
        if (selectedIds.length === 0) {
            Swal.fire('Warning', 'Please select at least one user', 'warning');
            return;
        }
        Swal.fire({
            title: 'Delete Users?',
            text: `Are you sure you want to delete ${selectedIds.length} user(s)? This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                performBulkAction(selectedIds, 'delete');
            }
        });
    });

    // Helper Functions
    function updateBulkActionsButton() {
        const selectedCount = $('.user-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);
        $('#bulkActionsBtn').prop('disabled', selectedCount === 0);
    }

    function getSelectedUserIds() {
        return $('.user-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
    }

    function performBulkAction(userIds, action) {
        const routeMap = {
            'activate': '{{ route("admin.users.bulk-activate") }}',
            'deactivate': '{{ route("admin.users.bulk-deactivate") }}',
            'delete': '{{ route("admin.users.bulk-delete") }}'
        };
        
        Swal.fire({
            title: 'Processing...',
            text: `Please wait while we ${action} the selected users.`,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: routeMap[action],
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                user_ids: userIds
            },
            success: function(response) {
                if (response.success) {
                    $('#bulkActionsModal').modal('hide');
                    // Use advanced toast instead of SweetAlert
                    window.toastSuccess('Success!', response.message || 'Operation completed successfully', { duration: 3000 });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    window.toastError('Error!', response.message || 'Bulk action failed', { duration: 5000 });
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Bulk action failed';
                window.toastError('Error!', msg, { duration: 5000 });
            }
        });
    }
});

// Quick Actions Functions
function exportUsers() {
    console.log('exportUsers function called');
    try {
        @if(auth()->user()->hasRole('System Admin'))
        Swal.fire({
            title: 'Export Users with Advanced Options',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Export Format</label>
                        <select id="exportFormat" class="form-select">
                            <option value="csv">CSV (Comma Separated Values)</option>
                            <option value="excel">Excel (XLSX)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="includePasswords" value="1">
                            <label class="form-check-label" for="includePasswords">
                                <strong>Include Passwords</strong>
                                <br><small class="text-muted">Export hashed passwords and default password note (System Admin only)</small>
                            </label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Verification Filter</label>
                        <select id="emailVerified" class="form-select">
                            <option value="">All Users</option>
                            <option value="1">Verified Only</option>
                            <option value="0">Unverified Only</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Date Range (Optional)</label>
                        <div class="row">
                            <div class="col-6">
                                <input type="date" id="createdFrom" class="form-control" placeholder="From">
                            </div>
                            <div class="col-6">
                                <input type="date" id="createdTo" class="form-control" placeholder="To">
                            </div>
                        </div>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="bx bx-download me-1"></i>Export',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#696cff',
            width: '600px',
            preConfirm: () => {
                return {
                    format: document.getElementById('exportFormat').value,
                    include_passwords: document.getElementById('includePasswords').checked ? '1' : '0',
                    email_verified: document.getElementById('emailVerified').value,
                    created_from: document.getElementById('createdFrom').value,
                    created_to: document.getElementById('createdTo').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const currentUrl = new URL(window.location.href);
                const params = currentUrl.searchParams;
                
                // Add export options
                params.set('format', result.value.format);
                if (result.value.include_passwords === '1') {
                    params.set('include_passwords', '1');
                }
                if (result.value.email_verified) {
                    params.set('email_verified', result.value.email_verified);
                }
                if (result.value.created_from) {
                    params.set('created_from', result.value.created_from);
                }
                if (result.value.created_to) {
                    params.set('created_to', result.value.created_to);
                }
                
                const exportUrl = '{{ route("admin.users.export") }}?' + params.toString();
                
                Swal.fire({
                    icon: 'info',
                    title: 'Exporting Users...',
                    text: 'Please wait while we prepare your file.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                window.location.href = exportUrl;
                
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Export Started',
                        text: 'Your file will download shortly.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 1000);
            }
        });
        @else
        // Non-admin users get simple export
        const currentUrl = new URL(window.location.href);
        const params = currentUrl.searchParams;
        const exportUrl = '{{ route("admin.users.export") }}?' + params.toString();
        
        Swal.fire({
            icon: 'info',
            title: 'Exporting Users...',
            text: 'Please wait while we prepare your file.',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        window.location.href = exportUrl;
        
        setTimeout(() => {
            Swal.fire({
                icon: 'success',
                title: 'Export Started',
                text: 'Your CSV file will download shortly.',
                timer: 2000,
                showConfirmButton: false
            });
        }, 1000);
        @endif
    } catch (error) {
        console.error('exportUsers error:', error);
        Swal.fire('Error', 'Error exporting users. Please try again.', 'error');
    }
}

// Refresh email verification for a user
function refreshEmailVerification(userId, userName) {
    Swal.fire({
        title: 'Refresh Email Verification?',
        html: `This will clear the email verification for <strong>${userName}</strong>. They will need to verify their email again.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Refresh',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ff9800',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch(`{{ url('admin/users') }}/${userId}/refresh-email-verification`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to refresh email verification');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Request failed: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Email Verification Refreshed!',
                text: result.value.message || 'Email verification has been cleared. User will need to verify again.',
                timer: 3000
            }).then(() => {
                location.reload();
            });
        }
    });
}

// Bulk refresh email verification
function bulkRefreshEmailVerification() {
    const selected = $('.user-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    if (selected.length === 0) {
        Swal.fire('Warning', 'Please select users first.', 'warning');
        return;
    }
    
    Swal.fire({
        title: 'Refresh Email Verification?',
        html: `This will clear email verification for <strong>${selected.length}</strong> selected user(s). They will need to verify their emails again.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, Refresh ${selected.length} User(s)`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ff9800',
        showLoaderOnConfirm: true,
        preConfirm: () => {
            return fetch('{{ route("admin.users.bulk-refresh-email-verification") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    user_ids: selected
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to refresh email verification');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(`Request failed: ${error.message}`);
            });
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'success',
                title: 'Email Verification Refreshed!',
                text: result.value.message || `Email verification refreshed for ${result.value.count} user(s).`,
                timer: 3000
            }).then(() => {
                location.reload();
            });
        }
    });
}

function generateUserReport() {
    console.log('generateUserReport function called');
    try {
        Swal.fire({
            title: 'Generate User Report',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Report Type</label>
                        <select id="userReportType" class="form-select form-select-lg">
                            <option value="summary">Summary Report</option>
                            <option value="detailed">Detailed Report</option>
                            <option value="role">Role Distribution</option>
                            <option value="department">Department Distribution</option>
                        </select>
                        <small class="text-muted">Select the type of report you want to generate</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Export Format</label>
                        <select id="userReportFormat" class="form-select form-select-lg">
                            <option value="pdf">PDF Document</option>
                            <option value="excel">Excel Spreadsheet</option>
                        </select>
                        <small class="text-muted">Choose your preferred file format</small>
                    </div>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: '<i class="bx bx-bar-chart me-1"></i>Generate Report',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#17a2b8',
            width: '500px',
            preConfirm: () => {
                const type = document.getElementById('userReportType').value;
                const format = document.getElementById('userReportFormat').value;
                if (!type || !format) {
                    Swal.showValidationMessage('Please select both report type and format');
                    return false;
                }
                return { type, format };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    icon: 'info',
                    title: 'Generating Report...',
                    text: 'Please wait while we prepare your report.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Redirect to report generation (would need to implement this route)
                const url = '{{ route("admin.users.index") }}?report=' + result.value.type + '&format=' + result.value.format;
                window.open(url, '_blank');
                
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Report Generated',
                        text: 'Your report is opening in a new tab.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 1500);
            }
        }).catch(error => {
            console.error('Report generation error:', error);
            Swal.fire('Error', 'Error generating report. Please try again.', 'error');
        });
    } catch (error) {
        console.error('generateUserReport error:', error);
        Swal.fire('Error', 'Error: ' + error.message, 'error');
    }
}

function bulkUserActions() {
    console.log('bulkUserActions function called');
    try {
        const selectedCount = $('.user-checkbox:checked').length;
        
        if (selectedCount === 0) {
            window.toastWarning('Warning', 'Please select users first.', { duration: 4000 });
            return;
        }
        
        $('#bulkSelectedCount').text(selectedCount);
        $('#bulkActionsModal').modal('show');
    } catch (error) {
        console.error('bulkUserActions error:', error);
        Swal.fire('Error', 'Error: ' + error.message, 'error');
    }
}

function refreshUserData() {
    console.log('refreshUserData function called');
    try {
        Swal.fire({
            title: 'Refreshing Data...',
            html: `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mb-0">Please wait while we refresh the user data.</p>
                </div>
            `,
            icon: null,
            allowOutsideClick: false,
            showConfirmButton: false,
            showCancelButton: false,
            didOpen: () => {
                setTimeout(() => {
                    console.log('Reloading page...');
                    location.reload();
                }, 800);
            }
        });
    } catch (error) {
        console.error('refreshUserData error:', error);
        location.reload();
    }
}
</script>
@endpush
