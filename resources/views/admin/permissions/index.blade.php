@extends('layouts.app')

@section('title', 'Permissions Management - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0">Permissions Management</h4>
        <p class="text-muted">Comprehensive permission management system with advanced analytics and role assignments</p>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-label-primary rounded-pill p-2 mb-2">
                                <i class="bx bx-shield fs-4"></i>
                            </span>
                            <h5 class="card-title mb-0">{{ $stats['total'] ?? 0 }}</h5>
                            <small class="text-muted">Total Permissions</small>
                        </div>
                        <div class="avatar avatar-md">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-shield fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-label-success rounded-pill p-2 mb-2">
                                <i class="bx bx-check-circle fs-4"></i>
                            </span>
                            <h5 class="card-title mb-0">{{ $stats['active'] ?? 0 }}</h5>
                            <small class="text-muted">Active Permissions</small>
                        </div>
                        <div class="avatar avatar-md">
                            <span class="avatar-initial rounded-circle bg-label-success">
                                <i class="bx bx-check-circle fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-label-info rounded-pill p-2 mb-2">
                                <i class="bx bx-folder fs-4"></i>
                            </span>
                            <h5 class="card-title mb-0">{{ $stats['modules_count'] ?? 0 }}</h5>
                            <small class="text-muted">Modules</small>
                        </div>
                        <div class="avatar avatar-md">
                            <span class="avatar-initial rounded-circle bg-label-info">
                                <i class="bx bx-folder fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-label-warning rounded-pill p-2 mb-2">
                                <i class="bx bx-x-circle fs-4"></i>
                            </span>
                            <h5 class="card-title mb-0">{{ $stats['unassigned'] ?? 0 }}</h5>
                            <small class="text-muted">Unassigned</small>
                        </div>
                        <div class="avatar avatar-md">
                            <span class="avatar-initial rounded-circle bg-label-warning">
                                <i class="bx bx-x-circle fs-4"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">All Permissions</h5>
                        <p class="text-muted mb-0">Manage system permissions, modules, and role assignments</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-primary" id="createPermissionBtn">
                            <i class="bx bx-plus-circle"></i> Create Permission
                        </button>
                        <button type="button" class="btn btn-outline-success" id="bulkActionsBtn" disabled>
                            <i class="bx bx-check-square"></i> Bulk Actions (<span id="selectedCount">0</span>)
                        </button>
                        <a href="{{ route('admin.permissions.export', request()->query()) }}" class="btn btn-outline-primary" id="exportBtn">
                            <i class="bx bx-download"></i> Export CSV
                        </a>
                        <button type="button" class="btn btn-outline-secondary" onclick="refreshData()">
                            <i class="bx bx-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Advanced Search and Filter Panel -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="bx bx-filter-alt me-2"></i>Advanced Filters & Search
                                        </h6>
                                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                                            <i class="bx bx-chevron-down me-1"></i>Toggle Filters
                                        </button>
                                    </div>
                                </div>
                                <div class="collapse show" id="filterPanel">
                                    <div class="card-body">
                                        <form method="GET" action="{{ route('admin.permissions.index') }}" id="filterForm">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label">Search</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                                        <input type="text" name="search" class="form-control" 
                                                               placeholder="Name, display name, module..." 
                                                               value="{{ request('search') }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label">Module</label>
                                                    <select name="module" class="form-select">
                                                        <option value="">All Modules</option>
                                                        @foreach($modules as $module)
                                                        <option value="{{ $module }}" {{ request('module') === $module ? 'selected' : '' }}>
                                                            {{ $module }}
                                                        </option>
                                                        @endforeach
                                                    </select>
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
                                                    <label class="form-label">Sort By</label>
                                                    <select name="sort_by" class="form-select">
                                                        <option value="module" {{ request('sort_by') === 'module' ? 'selected' : '' }}>Module</option>
                                                        <option value="display_name" {{ request('sort_by') === 'display_name' ? 'selected' : '' }}>Display Name</option>
                                                        <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name</option>
                                                        <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-label">Order</label>
                                                    <select name="sort_order" class="form-select">
                                                        <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>Asc</option>
                                                        <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>Desc</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bx bx-filter"></i> Apply Filters
                                                    </button>
                                                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">
                                                        <i class="bx bx-x"></i> Clear Filters
                                                    </a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions Table -->
                    <div class="table-responsive">
                        <table class="table table-hover" id="permissionsTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Permission</th>
                                    <th>Module</th>
                                    <th>Roles</th>
                                    <th>Status</th>
                                    <th>Users</th>
                                    <th>Created</th>
                                    <th width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permission)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input permission-checkbox" value="{{ $permission->id }}">
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-0">{{ $permission->display_name }}</h6>
                                            <small class="text-muted">{{ $permission->name }}</small>
                                            @if($permission->description)
                                            <br><small class="text-muted">{{ Str::limit($permission->description, 60) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ $permission->module }}</span>
                                    </td>
                                    <td>
                                        @foreach($permission->roles->take(3) as $role)
                                        <span class="badge bg-label-primary me-1">{{ $role->display_name }}</span>
                                        @endforeach
                                        @if($permission->roles->count() > 3)
                                        <span class="badge bg-label-secondary">+{{ $permission->roles->count() - 3 }} more</span>
                                        @endif
                                        @if($permission->roles->isEmpty())
                                        <span class="text-muted small">No roles</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $permission->is_active ? 'success' : 'danger' }}">
                                            {{ $permission->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-secondary">{{ $permission->users()->count() }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $permission->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button class="dropdown-item btn-view-permission" data-permission-id="{{ $permission->id }}">
                                                        <i class="bx bx-show me-2"></i>View Details
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item btn-edit-permission" data-permission-id="{{ $permission->id }}">
                                                        <i class="bx bx-edit me-2"></i>Edit Permission
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item btn-assign-roles" data-permission-id="{{ $permission->id }}">
                                                        <i class="bx bx-user-check me-2"></i>Assign Roles
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item toggle-status-btn" 
                                                            data-permission-id="{{ $permission->id }}" 
                                                            data-status="{{ $permission->is_active ? 'active' : 'inactive' }}">
                                                        <i class="bx bx-{{ $permission->is_active ? 'x' : 'check' }} me-2"></i>
                                                        {{ $permission->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button class="dropdown-item text-danger btn-delete-permission" 
                                                            data-permission-id="{{ $permission->id }}" 
                                                            data-permission-name="{{ $permission->display_name }}">
                                                        <i class="bx bx-trash me-2"></i>Delete Permission
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="bx bx-shield-x fs-1 d-block mb-2"></i>
                                            No permissions found matching your criteria.
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Permission Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="permissionModalTitle">
                    <i class="bx bx-shield"></i> Create Permission
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="permissionForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="permission_id" id="permissionId">
                    
                    <div class="mb-3">
                        <label class="form-label">Permission Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="permissionName" class="form-control" required 
                               placeholder="e.g., users.create">
                        <small class="form-text text-muted">Lowercase with dots (e.g., modules.files.view)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Display Name <span class="text-danger">*</span></label>
                        <input type="text" name="display_name" id="permissionDisplayName" class="form-control" required 
                               placeholder="e.g., Create Users">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Module <span class="text-danger">*</span></label>
                        <input type="text" name="module" id="permissionModule" class="form-control" required 
                               list="modulesList" placeholder="e.g., User Management">
                        <datalist id="modulesList">
                            @foreach($modules as $module)
                                <option value="{{ $module }}">
                            @endforeach
                        </datalist>
                        <small class="form-text text-muted">Enter a new module name or select from existing</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="permissionDescription" class="form-control" rows="3" 
                                  placeholder="Describe what this permission allows..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="permissionSubmitBtn">Create Permission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Permission Modal -->
<div class="modal fade" id="viewPermissionModal" tabindex="-1" aria-labelledby="viewPermissionModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="viewPermissionModalLabel">
                    <i class="bx bx-shield me-2"></i>Permission Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewPermissionContent">
                <div class="text-center py-4 text-muted">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading permission details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Assign Roles Modal -->
<div class="modal fade" id="assignRolesModal" tabindex="-1" aria-labelledby="assignRolesModalLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bx bx-user-check me-2"></i>Assign Roles to Permission
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignRolesForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="permission_id" id="assignPermissionId">
                    
                    <div class="mb-3">
                        <label class="form-label">Permission</label>
                        <input type="text" id="assignPermissionName" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Roles <span class="text-danger">*</span></label>
                        <select name="roles[]" id="rolesSelect" class="form-select" multiple required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select one or more roles that should have this permission</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Save Assignments</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1" aria-labelledby="bulkActionsModalLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionsModalLabel">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Selected Permissions: <strong id="bulkSelectedCount">0</strong></label>
                </div>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success" id="bulkActivate">
                        <i class="bx bx-check-circle"></i> Activate Selected
                    </button>
                    <button type="button" class="btn btn-warning" id="bulkDeactivate">
                        <i class="bx bx-x-circle"></i> Deactivate Selected
                    </button>
                    <button type="button" class="btn btn-danger" id="bulkDelete">
                        <i class="bx bx-trash"></i> Delete Selected
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<script>
$(document).ready(function() {
    // CSRF Token setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Initialize Select2
    $('#rolesSelect').select2({
        placeholder: 'Select roles...',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#assignRolesModal')
    });

    // Create Permission
    $('#createPermissionBtn').on('click', function() {
        $('#permissionModalTitle').html('<i class="bx bx-shield"></i> Create Permission');
        $('#permissionSubmitBtn').text('Create Permission');
        $('#permissionForm')[0].reset();
        $('#permissionId').val('');
        $('#permissionModal').modal('show');
    });

    // Edit Permission
    $(document).on('click', '.btn-edit-permission', function() {
        const permissionId = $(this).data('permission-id');
        const row = $(this).closest('tr');
        
        $('#permissionModalTitle').html('<i class="bx bx-shield"></i> Edit Permission');
        $('#permissionSubmitBtn').text('Update Permission');
        $('#permissionId').val(permissionId);
        $('#permissionName').val(row.find('small.text-muted').first().text());
        $('#permissionDisplayName').val(row.find('h6').text());
        $('#permissionModule').val(row.find('.badge.bg-label-info').text());
        $('#permissionDescription').val(row.find('small.text-muted').last().text() || '');
        
        $('#permissionModal').modal('show');
    });

    // Save Permission (Create/Update)
    $('#permissionForm').on('submit', function(e) {
        e.preventDefault();
        const permissionId = $('#permissionId').val();
        const url = permissionId 
            ? `{{ route("admin.permissions.update", ":id") }}`.replace(':id', permissionId)
            : '{{ route("admin.permissions.store") }}';
        const method = permissionId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    $('#permissionModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', response.message || 'Operation failed');
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                let errorMsg = xhr.responseJSON?.message || 'An error occurred';
                
                if (Object.keys(errors).length > 0) {
                    errorMsg = Object.values(errors).flat().join('\n');
                }
                
                showToast('error', errorMsg);
            }
        });
    });

    // View Permission
    $(document).on('click', '.btn-view-permission', function() {
        const permissionId = $(this).data('permission-id');
        $('#viewPermissionContent').html(`
            <div class="text-center py-4 text-muted">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading permission details...</p>
            </div>
        `);
        const modal = new bootstrap.Modal(document.getElementById('viewPermissionModal'));
        modal.show();

        $.ajax({
            url: `{{ route("admin.permissions.show", ":id") }}`.replace(':id', permissionId),
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            success: function(resp) {
                if (resp.success && resp.permission) {
                    const p = resp.permission;
                    const roles = (p.roles || []).map(r => 
                        `<span class="badge bg-label-info me-1">${r.display_name || r.name}</span>`
                    ).join(' ');
                    
                    const html = `
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Permission Name</label>
                                <div class="form-control bg-light">${p.name || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Display Name</label>
                                <div class="form-control bg-light">${p.display_name || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Module</label>
                                <div class="form-control bg-light">${p.module || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Status</label>
                                <div>
                                    ${p.is_active ? 
                                        '<span class="badge bg-success">Active</span>' : 
                                        '<span class="badge bg-danger">Inactive</span>'
                                    }
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted">Description</label>
                                <div class="form-control bg-light">${p.description || 'N/A'}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Users Count</label>
                                <div class="form-control bg-light">${p.users_count || 0}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Created At</label>
                                <div class="form-control bg-light">${p.created_at || 'N/A'}</div>
                            </div>
                            <div class="col-12"><hr></div>
                            <div class="col-12">
                                <label class="form-label text-muted">Assigned Roles</label>
                                <div>${roles || '<span class="text-muted">No roles assigned</span>'}</div>
                            </div>
                        </div>`;
                    $('#viewPermissionContent').html(html);
                } else {
                    $('#viewPermissionContent').html('<div class="text-danger">Failed to load permission details.</div>');
                }
            },
            error: function() {
                $('#viewPermissionContent').html('<div class="text-danger">Failed to load permission details.</div>');
            }
        });
    });

    // Assign Roles
    $(document).on('click', '.btn-assign-roles', function() {
        const permissionId = $(this).data('permission-id');
        const row = $(this).closest('tr');
        const permissionName = row.find('h6').text();
        
        $('#assignPermissionId').val(permissionId);
        $('#assignPermissionName').val(permissionName);
        
        // Load current role assignments
        $.ajax({
            url: `{{ route("admin.permissions.get-roles", ":id") }}`.replace(':id', permissionId),
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#rolesSelect').val(response.roles).trigger('change');
                }
            }
        });
        
        $('#assignRolesModal').modal('show');
    });

    // Save Role Assignments
    $('#assignRolesForm').on('submit', function(e) {
        e.preventDefault();
        const permissionId = $('#assignPermissionId').val();
        
        $.ajax({
            url: `{{ route("admin.permissions.assign-roles", ":id") }}`.replace(':id', permissionId),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    $('#assignRolesModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', response.message || 'Operation failed');
                }
            },
            error: function(xhr) {
                showToast('error', xhr.responseJSON?.message || 'Failed to assign roles');
            }
        });
    });

    // Toggle Status
    $(document).on('click', '.toggle-status-btn', function() {
        const permissionId = $(this).data('permission-id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        const action = newStatus === 'active' ? 'activate' : 'deactivate';
        
        if (!confirm(`Are you sure you want to ${action} this permission?`)) return;
        
        $.ajax({
            url: `{{ route("admin.permissions.toggle-status", ":id") }}`.replace(':id', permissionId),
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', response.message || 'Error updating permission status');
                }
            },
            error: function() {
                showToast('error', 'Error updating permission status');
            }
        });
    });

    // Delete Permission
    $(document).on('click', '.btn-delete-permission', function() {
        const permissionId = $(this).data('permission-id');
        const permissionName = $(this).data('permission-name');
        
        if (!confirm(`Are you sure you want to delete permission "${permissionName}"? This action cannot be undone!`)) return;
        
        $.ajax({
            url: `{{ route("admin.permissions.destroy", ":id") }}`.replace(':id', permissionId),
            method: 'POST',
            data: { _token: '{{ csrf_token() }}', _method: 'DELETE' },
            success: function(resp) {
                if (resp.success) {
                    showToast('success', resp.message || 'Permission deleted successfully');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', resp.message || 'Delete failed');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to delete permission';
                showToast('error', msg);
            }
        });
    });

    // Select All functionality
    $('#selectAll').on('change', function() {
        $('.permission-checkbox').prop('checked', $(this).prop('checked'));
        updateBulkActionsButton();
    });

    // Individual checkbox change
    $(document).on('change', '.permission-checkbox', function() {
        updateBulkActionsButton();
        const totalCheckboxes = $('.permission-checkbox').length;
        const checkedCheckboxes = $('.permission-checkbox:checked').length;
        $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
    });

    // Bulk Actions Button
    $('#bulkActionsBtn').on('click', function() {
        $('#bulkSelectedCount').text($('.permission-checkbox:checked').length);
        $('#bulkActionsModal').modal('show');
    });

    // Bulk Actions
    $('#bulkActivate').on('click', function() {
        const selectedIds = getSelectedPermissionIds();
        if (selectedIds.length === 0) {
            showToast('warning', 'Please select at least one permission');
            return;
        }
        performBulkAction(selectedIds, 'activate');
    });

    $('#bulkDeactivate').on('click', function() {
        const selectedIds = getSelectedPermissionIds();
        if (selectedIds.length === 0) {
            showToast('warning', 'Please select at least one permission');
            return;
        }
        performBulkAction(selectedIds, 'deactivate');
    });

    $('#bulkDelete').on('click', function() {
        const selectedIds = getSelectedPermissionIds();
        if (selectedIds.length === 0) {
            showToast('warning', 'Please select at least one permission');
            return;
        }
        if (!confirm(`Are you sure you want to delete ${selectedIds.length} permission(s)? This action cannot be undone.`)) {
            return;
        }
        performBulkAction(selectedIds, 'delete');
    });

    // Helper Functions
    function updateBulkActionsButton() {
        const selectedCount = $('.permission-checkbox:checked').length;
        $('#selectedCount').text(selectedCount);
        $('#bulkActionsBtn').prop('disabled', selectedCount === 0);
    }

    function getSelectedPermissionIds() {
        return $('.permission-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
    }

    function performBulkAction(permissionIds, action) {
        const routeMap = {
            'activate': '{{ route("admin.permissions.bulk-activate") }}',
            'deactivate': '{{ route("admin.permissions.bulk-deactivate") }}',
            'delete': '{{ route("admin.permissions.bulk-delete") }}'
        };
        
        $.ajax({
            url: routeMap[action],
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                permission_ids: permissionIds
            },
            success: function(response) {
                if (response.success) {
                    $('#bulkActionsModal').modal('hide');
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('error', response.message || 'Bulk action failed');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Bulk action failed';
                showToast('error', msg);
            }
        });
    }

    function showToast(type, message) {
        const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : 'bg-warning';
        const toast = $(`
            <div class="alert ${bgClass} alert-dismissible fade show position-fixed top-0 end-0 m-3 text-white" 
                 style="z-index: 9999; min-width: 300px;" role="alert">
                ${message}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        `);
        $('body').append(toast);
        setTimeout(() => toast.alert('close'), 5000);
    }

    function refreshData() {
        location.reload();
    }

    // Export button - update URL with current filters
    $('#exportBtn').on('click', function(e) {
        const currentUrl = new URL(window.location.href);
        const params = currentUrl.searchParams;
        const exportUrl = '{{ route("admin.permissions.export") }}?' + params.toString();
        window.location.href = exportUrl;
    });
});
</script>
@endpush
