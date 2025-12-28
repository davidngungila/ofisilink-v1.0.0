@extends('layouts.app')

@section('title', 'Manage Files & Folders')

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
                                <i class="bx bx-cog me-2"></i>Manage Files & Folders
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Organize, edit, and manage your digital files and folder structure
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.files.digital.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#folders-tab">
                                <i class="bx bx-folder me-2"></i>Folders
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#files-tab">
                                <i class="bx bx-file me-2"></i>Files
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Folders Tab -->
                        <div class="tab-pane fade show active" id="folders-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <h5 class="mb-0">All Folders</h5>
                                <div class="d-flex gap-2">
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" class="form-control" id="folder-search-manage" placeholder="Search folders...">
                                    </div>
                                    <button class="btn btn-success" id="bulk-create-folders-btn">
                                        <i class="bx bx-file me-2"></i>Bulk Create (Excel)
                                    </button>
                                    <button class="btn btn-primary" id="create-folder-btn">
                                        <i class="bx bx-folder-plus me-2"></i>Create Folder
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Access Level</th>
                                            <th>Files</th>
                                            <th>Subfolders</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="folders-tbody">
                                        @foreach($folders as $folder)
                                        <tr class="folder-row" data-folder-name="{{ strtolower($folder->name) }}">
                                            <td>
                                                <i class="bx bx-folder text-primary me-2"></i>
                                                <strong>{{ $folder->name }}</strong>
                                            </td>
                                            <td>{{ $folder->department->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-label-{{ $folder->access_level === 'public' ? 'success' : ($folder->access_level === 'department' ? 'info' : 'warning') }}">
                                                    {{ ucfirst($folder->access_level) }}
                                                </span>
                                            </td>
                                            <td>{{ $folder->files_count ?? 0 }}</td>
                                            <td>{{ $folder->subfolders_count ?? 0 }}</td>
                                            <td>{{ $folder->creator->name ?? 'System' }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary view-folder" data-folder-id="{{ $folder->id }}">
                                                        <i class="bx bx-show"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info edit-folder" data-folder-id="{{ $folder->id }}">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-folder" data-folder-id="{{ $folder->id }}">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Files Tab -->
                        <div class="tab-pane fade" id="files-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <h5 class="mb-0">All Files</h5>
                                <div class="d-flex gap-2">
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" class="form-control" id="file-search-manage" placeholder="Search files...">
                                    </div>
                                    <a href="{{ route('modules.files.digital.upload') }}" class="btn btn-primary">
                                        <i class="bx bx-upload me-2"></i>Upload Files
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Folder</th>
                                            <th>Size</th>
                                            <th>Type</th>
                                            <th>Uploaded By</th>
                                            <th>Uploaded At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="files-tbody">
                                        @foreach($files as $file)
                                        <tr class="file-row" data-file-name="{{ strtolower($file->original_name) }}">
                                            <td>
                                                <i class="bx bx-file text-info me-2"></i>
                                                <strong>{{ $file->original_name }}</strong>
                                            </td>
                                            <td>{{ $file->folder->name ?? 'N/A' }}</td>
                                            <td>{{ number_format($file->file_size / 1024 / 1024, 2) }} MB</td>
                                            <td>{{ $file->file_type ?? 'N/A' }}</td>
                                            <td>{{ $file->uploader->name ?? 'System' }}</td>
                                            <td>{{ $file->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ Storage::url($file->file_path) }}" class="btn btn-sm btn-outline-primary" target="_blank" title="Download">
                                                        <i class="bx bx-download"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-success assign-file-btn" data-file-id="{{ $file->id }}" title="Assign to Staff">
                                                        <i class="bx bx-user-plus"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info edit-file" data-file-id="{{ $file->id }}" title="Edit">
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-file" data-file-id="{{ $file->id }}" title="Delete">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    {{ $files->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Ensure SweetAlert2 popup appears in front of all elements */
    .swal2-container {
        z-index: 99999 !important;
    }
    .swal2-popup {
        z-index: 99999 !important;
    }
    .swal2-backdrop-show {
        z-index: 99998 !important;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    const ajaxUrl = '{{ route("modules.files.digital.ajax") }}';
    
    // Create Folder
    $('#create-folder-btn').click(function() {
        Swal.fire({
            title: 'Create New Folder',
            html: `
                <form id="create-folder-form">
                    <div class="mb-3">
                        <label class="form-label">Folder Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="folder-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="folder-description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Access Level</label>
                        <select class="form-select" id="folder-access-level">
                            <option value="public">Public</option>
                            <option value="department">Department</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Create',
            preConfirm: () => {
                const name = $('#folder-name').val();
                if (!name) {
                    Swal.showValidationMessage('Folder name is required');
                    return false;
                }
                return {
                    name: name,
                    description: $('#folder-description').val(),
                    access_level: $('#folder-access-level').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                createFolder(result.value);
            }
        });
    });
    
    function createFolder(data) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'create_folder',
                folder_name: data.name,
                description: data.description,
                access_level: data.access_level,
                parent_id: 0,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'Folder created successfully!', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Failed to create folder', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = response?.message || 'An error occurred';
                
                // Handle validation errors
                if (response?.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
    }
    
    // Edit Folder
    $(document).on('click', '.edit-folder', function() {
        const folderId = $(this).data('folder-id');
        editFolder(folderId);
    });
    
    function editFolder(folderId) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_folder_details',
                folder_id: folderId,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success && response.folder) {
                    const folder = response.folder;
                    Swal.fire({
                        title: 'Edit Folder',
                        html: `
                            <form id="edit-folder-form">
                                <div class="mb-3">
                                    <label class="form-label">Folder Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit-folder-name" value="${escapeHtml(folder.name)}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="edit-folder-description" rows="3">${escapeHtml(folder.description || '')}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Access Level</label>
                                    <select class="form-select" id="edit-folder-access-level">
                                        <option value="public" ${folder.access_level === 'public' ? 'selected' : ''}>Public</option>
                                        <option value="department" ${folder.access_level === 'department' ? 'selected' : ''}>Department</option>
                                        <option value="private" ${folder.access_level === 'private' ? 'selected' : ''}>Private</option>
                                    </select>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Update',
                        preConfirm: () => {
                            const name = $('#edit-folder-name').val();
                            if (!name) {
                                Swal.showValidationMessage('Folder name is required');
                                return false;
                            }
                            return {
                                name: name,
                                description: $('#edit-folder-description').val(),
                                access_level: $('#edit-folder-access-level').val()
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            updateFolder(folderId, result.value);
                        }
                    });
                }
            }
        });
    }
    
    function updateFolder(folderId, data) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'update_folder',
                folder_id: folderId,
                folder_name: data.name,
                description: data.description,
                access_level: data.access_level,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'Folder updated successfully!', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Failed to update folder', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = response?.message || 'An error occurred';
                
                // Handle validation errors
                if (response?.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
    }
    
    // Delete Folder
    $(document).on('click', '.delete-folder', function() {
        const folderId = $(this).data('folder-id');
        Swal.fire({
            title: 'Delete Folder?',
            text: 'This will delete the folder and all its contents. This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteFolder(folderId);
            }
        });
    });
    
    function deleteFolder(folderId) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'delete_folder',
                folder_id: folderId,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'Folder deleted successfully!', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Failed to delete folder', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = response?.message || 'An error occurred';
                
                // Handle validation errors
                if (response?.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
    }
    
    // View Folder
    $(document).on('click', '.view-folder', function() {
        const folderId = $(this).data('folder-id');
        if (folderId) {
            window.location.href = '/modules/files/digital/folder/' + folderId;
        }
    });
    
    // Edit File
    $(document).on('click', '.edit-file', function() {
        const fileId = $(this).data('file-id');
        editFile(fileId);
    });
    
    function editFile(fileId) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_file_details',
                file_id: fileId,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success && response.file) {
                    const file = response.file;
                    Swal.fire({
                        title: 'Edit File',
                        html: `
                            <form id="edit-file-form">
                                <div class="mb-3">
                                    <label class="form-label">File Name</label>
                                    <input type="text" class="form-control" id="edit-file-name" value="${escapeHtml(file.original_name || '')}" readonly>
                                    <small class="text-muted">File name cannot be changed</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="edit-file-description" rows="3">${escapeHtml(file.description || '')}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Access Level</label>
                                    <select class="form-select" id="edit-file-access-level">
                                        <option value="public" ${file.access_level === 'public' ? 'selected' : ''}>Public</option>
                                        <option value="department" ${file.access_level === 'department' ? 'selected' : ''}>Department</option>
                                        <option value="private" ${file.access_level === 'private' ? 'selected' : ''}>Private</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Confidentiality Level</label>
                                    <select class="form-select" id="edit-file-confidentiality">
                                        <option value="normal" ${file.confidential_level === 'normal' ? 'selected' : ''}>Normal</option>
                                        <option value="confidential" ${file.confidential_level === 'confidential' ? 'selected' : ''}>Confidential</option>
                                        <option value="strictly_confidential" ${file.confidential_level === 'strictly_confidential' ? 'selected' : ''}>Strictly Confidential</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tags (comma-separated)</label>
                                    <input type="text" class="form-control" id="edit-file-tags" value="${escapeHtml(file.tags || '')}" placeholder="tag1, tag2, tag3">
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Save Changes',
                        cancelButtonText: 'Cancel',
                        preConfirm: () => {
                            return {
                                description: $('#edit-file-description').val(),
                                access_level: $('#edit-file-access-level').val(),
                                confidential_level: $('#edit-file-confidentiality').val(),
                                tags: $('#edit-file-tags').val()
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            updateFile(fileId, result.value);
                        }
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to load file details', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = response?.message || 'An error occurred';
                
                // Handle validation errors
                if (response?.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
    }
    
    function updateFile(fileId, data) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'update_file',
                file_id: fileId,
                description: data.description,
                access_level: data.access_level,
                confidential_level: data.confidential_level,
                tags: data.tags,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'File updated successfully!', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Failed to update file', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = response?.message || 'An error occurred';
                
                // Handle validation errors
                if (response?.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
    }
    
    // Delete File
    $(document).on('click', '.delete-file', function() {
        const fileId = $(this).data('file-id');
        Swal.fire({
            title: 'Delete File?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteFile(fileId);
            }
        });
    });
    
    function deleteFile(fileId) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'delete_file',
                file_id: fileId,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'File deleted successfully!', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Failed to delete file', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = response?.message || 'An error occurred';
                
                // Handle validation errors
                if (response?.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Live search for folders
    let folderSearchTimeout;
    $('#folder-search-manage').on('input', function() {
        clearTimeout(folderSearchTimeout);
        const searchTerm = $(this).val().toLowerCase();
        
        folderSearchTimeout = setTimeout(() => {
            $('.folder-row').each(function() {
                const folderName = $(this).data('folder-name') || $(this).find('td').first().text().toLowerCase();
                if (folderName.includes(searchTerm) || searchTerm === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }, 300);
    });
    
    // Live search for files
    let fileSearchTimeout;
    $('#file-search-manage').on('input', function() {
        clearTimeout(fileSearchTimeout);
        const searchTerm = $(this).val().toLowerCase();
        
        fileSearchTimeout = setTimeout(() => {
            $('.file-row').each(function() {
                const fileName = $(this).data('file-name') || $(this).find('td').first().text().toLowerCase();
                if (fileName.includes(searchTerm) || searchTerm === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }, 300);
    });
    
    // Bulk Create Folders from Excel
    $('#bulk-create-folders-btn').on('click', function() {
        Swal.fire({
            title: 'Bulk Create Folders from Excel',
            html: `
                <div class="text-start">
                    <p class="mb-3">Upload a CSV or Excel file (.csv, .xlsx, .xls) to create multiple folders and subfolders at once. <strong>CSV format is recommended</strong> for best compatibility.</p>
                    <div class="alert alert-info mb-3">
                        <strong>Excel Format:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Column A:</strong> Folder Name (Required)</li>
                            <li><strong>Column B:</strong> Parent Folder Path (Optional) - Use forward slashes for nested paths, e.g., "Parent/Subfolder". Leave empty for root folders.</li>
                            <li><strong>Column C:</strong> Description (Optional)</li>
                            <li><strong>Column D:</strong> Access Level (Optional) - public, department, or private (default: public)</li>
                            <li><strong>Column E:</strong> Department Name (Optional)</li>
                        </ul>
                        <p class="mb-0 mt-2"><strong>Note:</strong> Parent folders will be created automatically before their children. You can create nested folder structures in a single import!</p>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0">Select File <span class="text-danger">*</span></label>
                            <a href="{{ route('modules.files.digital.download-folder-template') }}" class="btn btn-sm btn-outline-primary" download>
                                <i class="bx bx-download me-1"></i>Download Template
                            </a>
                        </div>
                        <input type="file" class="form-control" id="excel-file-input" accept=".csv,.xlsx,.xls" required>
                        <small class="text-muted">
                            <strong>Supported formats:</strong> CSV (recommended), Excel (.xlsx, .xls) | Maximum file size: 5MB
                            <br>
                            <span class="text-info"><i class="bx bx-info-circle"></i> CSV format works without additional packages. Excel files require Laravel Excel package.</span>
                        </small>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Upload & Create',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const fileInput = document.getElementById('excel-file-input');
                if (!fileInput.files || !fileInput.files[0]) {
                    Swal.showValidationMessage('Please select an Excel file');
                    return false;
                }
                return fileInput.files[0];
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                uploadBulkFoldersExcel(result.value);
            }
        });
    });
    
    function uploadBulkFoldersExcel(file) {
        const formData = new FormData();
        formData.append('action', 'bulk_create_folders_excel');
        formData.append('excel_file', file);
        formData.append('_token', csrfToken);
        
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we create folders from Excel',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    let message = `Successfully created ${response.created_count || 0} folder(s) from Excel.`;
                    if (response.errors && response.errors.length > 0) {
                        message += `\n\n${response.errors.length} error(s) occurred:\n`;
                        message += response.errors.slice(0, 10).join('\n');
                        if (response.errors.length > 10) {
                            message += `\n... and ${response.errors.length - 10} more errors.`;
                        }
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        html: message.replace(/\n/g, '<br>'),
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to create folders from Excel', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while processing the Excel file.';
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
    }
    
    // Handle assign individual file
    $(document).on('click', '.assign-file-btn', function() {
        const fileId = $(this).data('file-id');
        const fileName = $(this).closest('tr').find('td').first().text().trim() || 'File';
        
        // Get users list
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_users_for_assignment',
                _token: csrfToken
            },
            success: function(response) {
                if (response.success && response.users) {
                    showAssignFileModal(fileId, fileName, response.users);
                } else {
                    Swal.fire('Error', 'Failed to load users', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred', 'error');
            }
        });
    });
    
    function showAssignFileModal(fileId, fileName, users) {
        let usersOptions = '';
        users.forEach(function(user) {
            usersOptions += `<option value="${user.id}">${escapeHtml(user.name)} (${escapeHtml(user.email)})</option>`;
        });
        
        Swal.fire({
            title: 'Assign File to Staff',
            html: `
                <form id="assign-file-form">
                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <input type="text" class="form-control" value="${escapeHtml(fileName)}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Users <span class="text-danger">*</span></label>
                        <select class="form-select" name="user_ids[]" id="assign-file-user-ids" multiple required style="height: 200px;">
                            ${usersOptions}
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple users</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Permission Level</label>
                        <select class="form-select" name="permission_level" id="assign-file-permission">
                            <option value="view">View Only</option>
                            <option value="download">View & Download</option>
                            <option value="edit">View, Download & Edit</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry Duration</label>
                        <select class="form-select" name="expiry_duration" id="assign-file-expiry">
                            <option value="never">Never Expire</option>
                            <option value="1week">1 Week</option>
                            <option value="2weeks">2 Weeks</option>
                            <option value="4weeks">4 Weeks</option>
                            <option value="1month">1 Month</option>
                            <option value="3months">3 Months</option>
                            <option value="6months">6 Months</option>
                            <option value="1year">1 Year</option>
                            <option value="custom">Custom Date</option>
                        </select>
                    </div>
                    <div class="mb-3" id="assign-file-custom-expiry" style="display: none;">
                        <label class="form-label">Custom Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" min="${new Date(Date.now() + 86400000).toISOString().split('T')[0]}">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Assign',
            cancelButtonText: 'Cancel',
            didOpen: () => {
                $('#assign-file-expiry').on('change', function() {
                    if ($(this).val() === 'custom') {
                        $('#assign-file-custom-expiry').show();
                    } else {
                        $('#assign-file-custom-expiry').hide();
                    }
                });
            },
            preConfirm: () => {
                const userIds = $('#assign-file-user-ids').val();
                if (!userIds || userIds.length === 0) {
                    Swal.showValidationMessage('Please select at least one user');
                    return false;
                }
                return {
                    user_ids: userIds,
                    permission_level: $('#assign-file-permission').val(),
                    expiry_duration: $('#assign-file-expiry').val(),
                    expiry_date: $('#assign-file-expiry').val() === 'custom' ? $('input[name="expiry_date"]').val() : null
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                assignFileToUsers(fileId, result.value);
            }
        });
    }
    
    function assignFileToUsers(fileId, data) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'assign_file_folder',
                type: 'file',
                item_id: fileId,
                user_ids: data.user_ids,
                permission_level: data.permission_level,
                expiry_duration: data.expiry_duration,
                expiry_date: data.expiry_date,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'File assigned successfully!', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Failed to assign file', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = response?.message || 'An error occurred';
                
                // Handle validation errors
                if (response?.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    html: errorMessage
                });
            }
        });
    }
});
</script>
@endpush
@endsection

