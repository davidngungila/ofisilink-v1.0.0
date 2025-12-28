@extends('layouts.app')

@section('title', 'Physical Files Settings')

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
                                <i class="bx bx-cog me-2"></i>Physical Files Settings
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Configure physical file management system settings and preferences
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.files.physical.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Management -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Rack Categories</h5>
                        <button class="btn btn-primary btn-sm" id="create-category-btn">
                            <i class="bx bx-plus me-2"></i>Add Category
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Prefix</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr>
                                    <td><strong>{{ $category->name }}</strong></td>
                                    <td><span class="badge bg-label-info">{{ $category->prefix }}</span></td>
                                    <td>{{ $category->description ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-label-{{ $category->status === 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($category->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-outline-info edit-category" data-category-id="{{ $category->id }}" data-category-name="{{ $category->name }}" data-category-prefix="{{ $category->prefix }}" data-category-description="{{ $category->description ?? '' }}" data-category-status="{{ $category->status }}">
                                                <i class="bx bx-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-category" data-category-id="{{ $category->id }}">
                                                <i class="bx bx-trash"></i> Delete
                                            </button>
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
    </div>

    <!-- System Settings -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">System Settings</h5>
                </div>
                <div class="card-body">
                    <form id="settings-form">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Default Retention Period (Years)</label>
                                <input type="number" class="form-control" name="default_retention" value="{{ $settings['default_retention'] ?? 5 }}" min="1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Auto-archive After (Days)</label>
                                <input type="number" class="form-control" name="auto_archive_days" value="{{ $settings['auto_archive_days'] ?? 365 }}" min="1">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Enable Activity Logging</label>
                                <select class="form-select" name="enable_logging">
                                    <option value="1" {{ ($settings['enable_logging'] ?? true) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ !($settings['enable_logging'] ?? true) ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Default Access Level</label>
                                <select class="form-select" name="default_access_level">
                                    <option value="public" {{ ($settings['default_access_level'] ?? 'public') === 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="department" {{ ($settings['default_access_level'] ?? 'public') === 'department' ? 'selected' : '' }}>Department</option>
                                    <option value="private" {{ ($settings['default_access_level'] ?? 'public') === 'private' ? 'selected' : '' }}>Private</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-2"></i>Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const ajaxUrl = '{{ route("modules.files.physical.ajax") }}';
    const csrfToken = '{{ csrf_token() }}';
    
    // Create Category
    $('#create-category-btn').click(function() {
        Swal.fire({
            title: 'Create Category',
            html: `
                <form id="create-category-form">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prefix <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="category-prefix" maxlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="category-description" rows="3"></textarea>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Create',
            preConfirm: () => {
                const name = $('#category-name').val();
                const prefix = $('#category-prefix').val();
                if (!name || !prefix) {
                    Swal.showValidationMessage('Name and prefix are required');
                    return false;
                }
                return {
                    name: name,
                    prefix: prefix,
                    description: $('#category-description').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'create_category',
                        name: result.value.name,
                        prefix: result.value.prefix,
                        description: result.value.description,
                        _token: csrfToken
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', response.message || 'Category created!', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message || 'Failed to create category', 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response?.message || 'An error occurred', 'error');
                    }
                });
            }
        });
    });
    
    // Edit Category
    $(document).on('click', '.edit-category', function() {
        const categoryId = $(this).data('category-id');
        const categoryName = $(this).data('category-name');
        const categoryPrefix = $(this).data('category-prefix');
        const categoryDescription = $(this).data('category-description');
        const categoryStatus = $(this).data('category-status');
        
        Swal.fire({
            title: 'Edit Category',
            html: `
                <form id="edit-category-form">
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-category-name" value="${categoryName}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prefix <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit-category-prefix" value="${categoryPrefix}" maxlength="10" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="edit-category-description" rows="3">${categoryDescription || ''}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="edit-category-status">
                            <option value="active" ${categoryStatus === 'active' ? 'selected' : ''}>Active</option>
                            <option value="inactive" ${categoryStatus === 'inactive' ? 'selected' : ''}>Inactive</option>
                        </select>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            preConfirm: () => {
                return {
                    name: $('#edit-category-name').val(),
                    prefix: $('#edit-category-prefix').val(),
                    description: $('#edit-category-description').val(),
                    status: $('#edit-category-status').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'update_category',
                        category_id: categoryId,
                        name: result.value.name,
                        prefix: result.value.prefix,
                        description: result.value.description,
                        status: result.value.status,
                        _token: csrfToken
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', response.message || 'Category updated!', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message || 'Failed to update category', 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response?.message || 'An error occurred', 'error');
                    }
                });
            }
        });
    });
    
    // Delete Category
    $(document).on('click', '.delete-category', function() {
        const categoryId = $(this).data('category-id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the category permanently!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'delete_category',
                        category_id: categoryId,
                        _token: csrfToken
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', response.message || 'Category deleted!', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message || 'Failed to delete category', 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response?.message || 'An error occurred', 'error');
                    }
                });
            }
        });
    });
    
    // Save Settings
    $('#settings-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'save_settings',
            default_retention: $('input[name="default_retention"]').val(),
            auto_archive_days: $('input[name="auto_archive_days"]').val(),
            enable_logging: $('select[name="enable_logging"]').val(),
            default_access_level: $('select[name="default_access_level"]').val(),
            _token: csrfToken
        };
        
        Swal.fire({
            title: 'Saving Settings...',
            text: 'Please wait while we save your settings.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Settings saved successfully!',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to save settings', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while saving settings.';
                
                if (response && response.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                } else if (response && response.message) {
                    errorMessage = response.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
    });
});
</script>
@endpush
@endsection

