@extends('layouts.app')

@section('title', 'Role Management - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0">Role Management</h4>
        <p class="text-muted">Manage system roles and their permissions</p>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')
<div class="row">
  <!-- Roles List -->
  <div class="col-lg-8 mb-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h5 class="card-title mb-0">System Roles</h5>
          <p class="text-muted mb-0">Manage roles and assign permissions</p>
        </div>
        <div>
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">
            <i class="bx bx-plus"></i> Add New Role
          </button>
        </div>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover" id="rolesTable">
            <thead class="table-light">
              <tr>
                <th>Role Name</th>
                <th>Display Name</th>
                <th>Users</th>
                <th>Permissions</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($roles as $role)
              <tr>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-2">
                      <span class="avatar-initial rounded-circle bg-label-primary">
                        {{ strtoupper(substr($role->display_name, 0, 2)) }}
                      </span>
                    </div>
                    <div>
                      <h6 class="mb-0">{{ $role->name }}</h6>
                      <small class="text-muted">{{ $role->description ?? 'No description' }}</small>
                    </div>
                  </div>
                </td>
                <td>{{ $role->display_name }}</td>
                <td>
                  <span class="badge bg-label-info">{{ $role->users->count() }} users</span>
                </td>
                <td>
                  <span class="badge bg-label-success">{{ $role->permissions->count() }} permissions</span>
                </td>
                <td>
                  <span class="badge bg-{{ $role->is_active ? 'success' : 'danger' }}">
                    {{ $role->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      Actions
                    </button>
                    <ul class="dropdown-menu">
                      <li>
                        <button class="dropdown-item edit-role-btn" 
                                data-role-id="{{ $role->id }}"
                                data-role-name="{{ $role->name }}"
                                data-role-display-name="{{ $role->display_name }}"
                                data-role-description="{{ $role->description ?? '' }}"
                                data-role-status="{{ $role->is_active ? '1' : '0' }}"
                                data-role-permissions="{{ $role->permissions->pluck('id')->implode(',') }}">
                          <i class="bx bx-edit me-2"></i>Edit Role
                        </button>
                      </li>
                      <li>
                        <button class="dropdown-item view-role-btn" 
                                data-role-id="{{ $role->id }}"
                                data-role-name="{{ $role->name }}"
                                data-role-display-name="{{ $role->display_name }}"
                                data-role-description="{{ $role->description ?? '' }}"
                                data-role-status="{{ $role->is_active ? 'active' : 'inactive' }}"
                                data-role-permissions="{{ $role->permissions->pluck('id')->implode(',') }}">
                          <i class="bx bx-show me-2"></i>View Role
                        </button>
                      </li>
                      <li>
                        <button class="dropdown-item toggle-status-btn" data-role-id="{{ $role->id }}" data-status="{{ $role->is_active ? 'active' : 'inactive' }}">
                          <i class="bx bx-{{ $role->is_active ? 'user-x' : 'user-check' }} me-2"></i>
                          {{ $role->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                      </li>
                      @if($role->name !== 'System Admin')
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <button class="dropdown-item text-danger delete-role-btn" data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}">
                          <i class="bx bx-trash me-2"></i>Delete Role
                        </button>
                      </li>
                      @endif
                    </ul>
                  </div>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Permissions Overview -->
  <div class="col-lg-4 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0">Permissions Overview</h5>
      </div>
      <div class="card-body">
        @foreach($permissions as $module => $modulePermissions)
        <div class="mb-3">
          <h6 class="text-primary">{{ ucfirst($module) }}</h6>
          <div class="d-flex flex-wrap gap-1">
            @foreach($modulePermissions as $permission)
            <span class="badge bg-label-secondary">{{ $permission->display_name }}</span>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<!-- Create Role Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="createRoleForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="createRoleModalLabel">Create New Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Role Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required placeholder="e.g., manager">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Display Name <span class="text-danger">*</span></label>
              <input type="text" name="display_name" class="form-control" required placeholder="e.g., Manager">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Role description"></textarea>
          </div>
          
          <h6 class="text-primary mb-3">Assign Permissions</h6>
          @foreach($permissions as $module => $modulePermissions)
          <div class="mb-3">
            <h6 class="text-muted">{{ ucfirst($module) }}</h6>
            <div class="row">
              @foreach($modulePermissions as $permission)
              <div class="col-md-6 mb-2">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="perm_{{ $permission->id }}">
                  <label class="form-check-label" for="perm_{{ $permission->id }}">
                    {{ $permission->display_name }}
                  </label>
                </div>
              </div>
              @endforeach
            </div>
          </div>
          @endforeach
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save"></i> Create Role
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Role Modal -->
<div class="modal fade" id="viewRoleModal" tabindex="-1" aria-labelledby="viewRoleModalLabel">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewRoleModalLabel">Role Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label text-muted">System Name</label>
            <div class="form-control bg-light" id="viewRoleName" readonly></div>
          </div>
          <div class="col-md-6">
            <label class="form-label text-muted">Display Name</label>
            <div class="form-control bg-light" id="viewRoleDisplayName" readonly></div>
          </div>
          <div class="col-12">
            <label class="form-label text-muted">Description</label>
            <div class="form-control bg-light" id="viewRoleDescription" style="min-height: 80px;" readonly></div>
          </div>
          <div class="col-12">
            <label class="form-label text-muted me-2">Status</label>
            <span id="viewRoleStatus" class="badge"></span>
          </div>
          <div class="col-12">
            <label class="form-label text-muted">Permissions</label>
            <div id="viewRolePermissions" class="d-flex flex-wrap gap-1"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
  </div>

<!-- Edit Role Modal -->
<div class="modal fade" id="editRoleModal" tabindex="-1" aria-labelledby="editRoleModalLabel">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="editRoleForm">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="editRoleModalLabel">Edit Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Role Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Display Name <span class="text-danger">*</span></label>
              <input type="text" name="display_name" id="edit_display_name" class="form-control" required>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
          </div>
          
          <h6 class="text-primary mb-3">Assign Permissions</h6>
          <div id="editPermissionsContainer">
            @foreach($permissions as $module => $modulePermissions)
            <div class="mb-3">
              <h6 class="text-muted">{{ ucfirst($module) }}</h6>
              <div class="row">
                @foreach($modulePermissions as $permission)
                <div class="col-md-6 mb-2">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" id="edit_perm_{{ $permission->id }}">
                    <label class="form-check-label" for="edit_perm_{{ $permission->id }}">
                      {{ $permission->display_name }}
                    </label>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            @endforeach
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save"></i> Update Role
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- View Role Modal - Advanced -->
<div class="modal fade" id="viewRoleModal" tabindex="-1" aria-labelledby="viewRoleModalLabel">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="viewRoleModalLabel">Role Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-bold text-muted">System Name</label>
            <div class="form-control bg-light border-0" id="viewRoleName" readonly></div>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold text-muted">Display Name</label>
            <div class="form-control bg-light border-0" id="viewRoleDisplayName" readonly></div>
          </div>
          <div class="col-12">
            <label class="form-label fw-bold text-muted">Description</label>
            <div class="form-control bg-light border-0" id="viewRoleDescription" style="min-height: 80px;" readonly></div>
          </div>
          <div class="col-12">
            <label class="form-label fw-bold text-muted me-2">Status:</label>
            <span id="viewRoleStatus" class="badge"></span>
          </div>
          <div class="col-12">
            <hr>
            <label class="form-label fw-bold text-muted mb-3 d-block">Assigned Permissions</label>
            <div id="viewRolePermissions" class="d-flex flex-wrap gap-2 p-3 bg-light rounded"></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bx bx-x"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentRoleId = null;

    // Create Role Form
    $('#createRoleForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("roles.store") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const toast = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;" role="alert">' +
                        '<strong>Success!</strong> Role created successfully.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('body').append(toast);
                    
                    // Hide modal (Bootstrap 5)
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createRoleModal'));
                    if (modal) modal.hide();
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Failed to create role: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                // Handle validation errors
                console.error('Validation errors:', errors);
            }
        });
    });

    // View Role Button - Advanced Popup with AJAX
    $('.view-role-btn').on('click', function() {
        const roleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');
        const roleDisplayName = $(this).data('role-display-name');
        const roleDescription = $(this).data('role-description') || 'No description provided';
        const roleStatus = $(this).data('role-status');
        const permissionIds = $(this).data('role-permissions') ? $(this).data('role-permissions').toString().split(',').filter(id => id.trim()) : [];

        // Populate basic info immediately
        $('#viewRoleModalLabel').text('Role Details: ' + roleDisplayName);
        $('#viewRoleName').text(roleName);
        $('#viewRoleDisplayName').text(roleDisplayName);
        $('#viewRoleDescription').text(roleDescription || 'No description provided');
        
        // Status badge
        const statusBadge = $('#viewRoleStatus');
        statusBadge.removeClass('badge bg-success badge bg-danger bg-success bg-danger');
        if (roleStatus === 'active') {
            statusBadge.addClass('badge bg-success').text('Active');
        } else {
            statusBadge.addClass('badge bg-danger').text('Inactive');
        }

        // Show modal first
        const modal = new bootstrap.Modal(document.getElementById('viewRoleModal'));
        modal.show();

        // Fetch role details with permissions via AJAX
        $.ajax({
            url: `/admin/roles/${roleId}`,
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.role && response.role.permissions) {
                    const permissions = response.role.permissions;
                    const permsContainer = $('#viewRolePermissions');
                    permsContainer.empty();
                    
                    if (permissions.length > 0) {
                        permissions.forEach(function(perm) {
                            const badge = $('<span/>', {
                                class: 'badge bg-label-primary me-1 mb-1',
                                text: perm.display_name || perm.name
                            }).append(
                                $('<small/>', {
                                    class: 'text-muted ms-1',
                                    text: '(' + (perm.module || 'N/A') + ')'
                                })
                            );
                            permsContainer.append(badge);
                        });
                    } else {
                        permsContainer.html('<span class="text-muted fst-italic">No permissions assigned to this role</span>');
                    }
                } else {
                    // Fallback: try to find permissions from page context
                    const permsContainer = $('#viewRolePermissions');
                    permsContainer.empty();
                    if (permissionIds.length > 0) {
                        permissionIds.forEach(function(permId) {
                            const permCheckbox = $('#edit_perm_' + permId);
                            if (permCheckbox.length) {
                                const permLabel = permCheckbox.next('label');
                                const badge = $('<span/>', {
                                    class: 'badge bg-label-primary me-1 mb-1',
                                    text: permLabel.text()
                                });
                                permsContainer.append(badge);
                            }
                        });
                        if (permsContainer.children().length === 0) {
                            permsContainer.html('<span class="text-muted">' + permissionIds.length + ' permission(s) assigned</span>');
                        }
                    } else {
                        permsContainer.html('<span class="text-muted fst-italic">No permissions assigned to this role</span>');
                    }
                }
            },
            error: function(xhr) {
                // Fallback: use data from button attributes
                const permsContainer = $('#viewRolePermissions');
                permsContainer.empty();
                if (permissionIds.length > 0) {
                    permissionIds.forEach(function(permId) {
                        const permCheckbox = $('#edit_perm_' + permId.trim());
                        if (permCheckbox.length) {
                            const permLabel = permCheckbox.next('label');
                            const badge = $('<span/>', {
                                class: 'badge bg-label-primary me-1 mb-1',
                                text: permLabel.text()
                            });
                            permsContainer.append(badge);
                        }
                    });
                    if (permsContainer.children().length === 0) {
                        permsContainer.html('<span class="text-muted">' + permissionIds.length + ' permission(s) assigned</span>');
                    }
                } else {
                    permsContainer.html('<span class="text-muted fst-italic">No permissions assigned to this role</span>');
                }
            }
        });
    });

    // Edit Role Button - Advanced with proper data handling
    $('.edit-role-btn').on('click', function() {
        const roleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');
        const roleDisplayName = $(this).data('role-display-name');
        const roleDescription = $(this).data('role-description') || '';
        const roleStatus = $(this).data('role-status');
        const permissionIds = $(this).data('role-permissions') ? $(this).data('role-permissions').toString().split(',') : [];

        currentRoleId = roleId;
        $('#edit_name').val(roleName);
        $('#edit_display_name').val(roleDisplayName);
        $('#edit_description').val(roleDescription);
        // Note: is_active is managed separately via toggle-status action

        // Uncheck all permissions first
        $('#editPermissionsContainer input[type="checkbox"]').prop('checked', false);

        // Check assigned permissions
        permissionIds.forEach(function(permissionId) {
            const permId = permissionId.toString().trim();
            if (permId) {
                $('#edit_perm_' + permId).prop('checked', true);
            }
        });

        // Show modal (Bootstrap 5)
        const modalElement = document.getElementById('editRoleModal');
        if (modalElement) {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
    });

    // Edit Role Form
    $('#editRoleForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: `/admin/roles/${currentRoleId}`,
            method: 'PUT',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const toast = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;" role="alert">' +
                        '<strong>Success!</strong> Role updated successfully.' +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('body').append(toast);
                    
                    // Hide modal (Bootstrap 5)
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editRoleModal'));
                    if (modal) modal.hide();
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Failed to update role: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMsg = 'Validation errors:\n';
                    Object.keys(errors).forEach(function(key) {
                        errorMsg += '- ' + errors[key][0] + '\n';
                    });
                    alert(errorMsg);
                } else {
                    alert('Failed to update role: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
                console.error('Update errors:', xhr);
            }
        });
    });

    // Toggle Status - Advanced with confirmation
    $('.toggle-status-btn').on('click', function() {
        const roleId = $(this).data('role-id');
        const currentStatus = $(this).data('status');
        const action = currentStatus === 'active' ? 'deactivate' : 'activate';
        
        if (confirm(`Are you sure you want to ${action} this role?`)) {
            $.ajax({
                url: `/admin/roles/${roleId}/toggle-status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        const toast = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;" role="alert">' +
                            '<strong>Success!</strong> Role ' + action + 'd successfully.' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                            '</div>');
                        $('body').append(toast);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('Failed to ' + action + ' role: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to ' + action + ' role';
                    alert('Error: ' + message);
                }
            });
        }
    });

    // Delete Role - Advanced with confirmation
    $('.delete-role-btn').on('click', function() {
        const roleId = $(this).data('role-id');
        const roleName = $(this).data('role-name');
        
        if (confirm(`Are you sure you want to delete the role "${roleName}"? This action cannot be undone.`)) {
            $.ajax({
                url: `/admin/roles/${roleId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        const toast = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" style="z-index: 9999;" role="alert">' +
                            '<strong>Success!</strong> Role deleted successfully.' +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                            '</div>');
                        $('body').append(toast);
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        alert('Failed to delete role: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Failed to delete role';
                    alert('Error: ' + message);
                }
            });
        }
    });
});
</script>
@endpush