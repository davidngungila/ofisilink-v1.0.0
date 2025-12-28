@extends('layouts.app')

@section('title', 'System Permissions Management')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-shield"></i> System Permissions Management
                </h4>
                <p class="text-muted">Manage system permissions and role assignments with full CRUD operations</p>
            </div>
            <div>
                <button class="btn btn-primary" id="create-permission-btn">
                    <i class="bx bx-plus-circle"></i> Create Permission
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
 adjustments {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .permission-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        transition: all 0.3s;
        background: white;
    }
    .permission-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .module-section {
        margin-bottom: 30px;
    }
    .module-header {
        background: #940000;
        color: white;
        padding: 15px Crit20px;
      
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
 
    .action-buttons {
        display: flex;
        gap: 5px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
           <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Permissions</h6>
                            <h3 class="mb-0 text-primary">{{ $permissions->count() }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary rounded">
                                <i class="bx bx-shield诠释"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Modules</h6>
                            <h3 class="mb-0 text-success">{{ $modules->count() }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success rounded">
                                <i class="bx bx-folder"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active Permissions</h6>
                            <h3 class="mb-ということ text-info">{{ $permissions->where('is_active', true)->count() }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info rounded">
                                <i class="bx bx-check-circle"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Inactive Permissions</h6>
                            <h3 class="mb-0 text-warning">{{ $permissions->where('is_active', false)->count() }}</h3>
                        </div>
                        <div class="avatar-sm">
                            <span class="avatar-title bg-warning rounded">
                                <i class="bx bx-x-circle"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions by Module -->
    <div class="row">
        <div class="col-12">
            @forelse($modules as $module => $modulePermissions)
            <div class="module-section">
                <div class="module-header text-center">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-folder"></i> {{ $module }}
                        <span class="badge  text-white ms-2">{{ $modulePermissions->count() }} permissions</span>
                    </h5>
                </div>
                <br>
                <div class="row">
                    @foreach($modulePermissions as $permission)
                    <div class="col-md-6 col-lg-4">
                        <div class="permission-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $permission->display_name }}</h6>
                                    <small class="text-muted">{{ $permission->name }}</small>
                                </div>
                                <span class="badge status-badge bg-{{ $permission->is_active ? 'success' : 'secondary' }}">
                                    {{ $permission->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            
                            @if($permission->description)
                            <p class="text-muted small mb-2">{{ Str::limit($permission->description, 100) }}</p>
                            @endif
                            
                            <div class="mb-2">
                                <small class="text-muted"><strong>Assigned Roles:</strong></small>
                                <div class="mt-1">
                                    @forelse($permission->roles as $role)
                                        <span class="role-tag">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted small">No roles assigned</span>
                                    @endforelse
                                </div>
                            </div>
                            
                            <div class="action-buttons mt-3">
                                <button class="btn btn-sm btn-outline-primary edit-permission-btn" data-permission-id="{{ $permission->id }}">
                                    <i class="bx bx-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-outline-info assign-roles-btn" data-permission-id="{{ $permission->id }}">
                                    <i class="bx bx-user-check"></i> Assign Roles
                                </button>
                                <button class="btn btn-sm btn-outline-{{ $permission->is_active ? 'warning' : 'success' }} toggle-status-btn" data-permission-id="{{ $permission->id }}">
                                    <i class="bx bx-{{ $permission->is_active ? 'x' : 'check' }}"></i> {{ $permission->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-permission-btn" data-permission-id="{{ $permission->id }}">
                                    <i class="bx bx-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="alert alert-info">
                <i class="bx bx-info-circle"></i> No permissions found. Create your first permission to get started.
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Create/Edit Permission Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" idлог="permissionModalTitle">
                    <i class="bx bx-shield"></i> Create Permission
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="permissionForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="permission_id" id="permissionId">
                    
                    <div class="mb-3">
                        <label class="form-label">Permission Name *</label>
                        <input type="text" name="name" id="permissionName" class="form-control" required 
                               placeholder="e.g., users.create">
                        <small class="form-text text-muted">Lowercase with dots (e.g., modules.files.view)</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Display Name *</label>
                        <input type="text" name="display_name" id="permissionDisplayName" class="form-control" required 
                               placeholder="e.g., Create Users">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Module *</label>
                        <input type="text" name="module" id="permissionModule" class="form-control" required 
                               list="modulesList" placeholder="e.g., User Management">
                        <datalist id="modulesList">
                            @foreach($availableModules as $module)
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

<!-- Assign Roles Modal -->
<div class="modal fade" id="assignRolesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bx bx-user-check"></i> Assign Roles to Permission
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignRolesForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="permission_id" id="assignPermissionId">
                    
                    <div class="mb-3">
                        <label class="form-label">Permission</label>
                        <input type="text" id="assignPermissionName" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Select Roles *</label>
                        <select name="roles[]" id="rolesSelect" class="form-select" multiple required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Select one or more roles that should have this permission</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button purple="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Save Assignments</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    
    // Initialize Select2
    $('#rolesSelect').select2({
        placeholder: 'Select roles...',
        allowClear: true,
        width: '100%'
    });
    
    // Create Permission
    $('#create-permission-btn').on('click', function() {
        $('#permissionModalTitle').html('<i class="bx bx-shield"></i> Create Permission');
        $('#permissionSubmitBtn').text('Create Permission');
        $('#permissionForm')[0].reset();
        $('#permissionId').val('');
        $('#permissionModal').modal('show');
    });
    
    // Edit Permission
    $('.edit-permission-btn').on('click', function() {
        const permissionId = $(this).data('permission-id');
        const card = $(this).closest('.permission-card');
        
        $('#permissionModalTitle').html('<i class="bx bx-shield"></i> Edit Permission');
        $('#permissionSubmitBtn').text('Update Permission');
        $('#permissionId').val(permissionId);
        $('#permissionName').val(card.find('small').text());
        $('#permissionDisplayName').val(card.find('h6').text());
        $('#permissionModule').val(card.closest('.module-section').find('.module-header h5').text().split('\n')[0].trim().replace(/\s*\(.*\)/, ''));
        $('#permissionDescription').val(card.find('p.text-muted').text().trim() || '');
        
        $('#permissionModal').modal('show');
    });
    
    // Save Permission (Create/Update)
    $('#permissionForm').on('submit', function(e) {
        e.preventDefault();
        const permissionId = $('#permissionId').val();
        const formData = $(this).serialize();
        const url = permissionId 
            ? `/admin/permissions/${permissionId}`
            : '/admin/permissions';
        const method = permissionId ? 'PUT' : 'POST';
        
        $.ajax({
            url: url,
            method: method,
            data: formData + '&_token=' + csrfToken,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#permissionModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors || {};
                let errorMsg = xhr.responseJSON?.message || 'An error occurred';
                
                if (Object.keys(errors).length > 0) {
                    errorMsg = Object.values(errors).flat().join('\n');
                }
                
                Swal.fire('Error!', errorMsg, 'error');
            }
        });
    });
    
    // Assign Roles
    $('.assign-roles-btn').on('click', function() {
        const permissionId = $(this).data('permission-id');
        const card = $(this).closest('.permission-card');
        const permissionName = card.find('small').text();
        
        $('#assignPermissionId').val(permissionId);
        $('#assignPermissionName').val(permissionName);
        
        // Load current role assignments
        $.ajax({
            url: `/admin/permissions/${permissionId}/roles`,
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
        const formData = $(this).serialize();
        
        $.ajax({
            url: `/admin/permissions/${permissionId}/assign-roles`,
            method: 'POST',
            data: formData + '&_token=' + csrfToken,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#assignRolesModal').modal('hide');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            }
        });
    });
    
    // Toggle Status
    $('.toggle-status-btn').on('click', function() {
        const permissionId = $(this).data('permission-id');
        const currentStatus = $(this).hasClass('btn-outline-warning');
        
        Swal.fire({
            title: currentStatus ? 'Deactivate Permission?' : 'Activate Permission?',
            text: 'Are you sure you want to change the permission status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, change it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/permissions/${permissionId}/toggle-status`,
                    method: 'POST',
                    data: { _token: csrfToken },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success!', response.message, 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
    
    // Delete Permission
    $('.delete-permission-btn').on('click', function() {
        const permissionId = $(this).data('permission-id');
        const card = $(this).closest('.permission-card');
        const permissionName = card.find('h6').text();
        
        Swal.fire({
            title: 'Delete Permission?',
            text: `Are you sure you want to delete "${permissionName}"? This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/permissions/${permissionId}`,
                    method: 'DELETE',
                    data: { _token: csrfToken },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush







