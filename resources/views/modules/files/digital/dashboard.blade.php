@extends('layouts.app')

@section('title', 'Digital Files Dashboard')

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
                                <i class="bx bx-folder me-2"></i>Digital Files Dashboard
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Comprehensive file management system with advanced folder organization and analytics
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($canManageFiles)
                                <a href="{{ route('modules.files.digital.upload') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-upload me-2"></i>Upload Files
                                </a>
                                <a href="{{ route('modules.files.digital.manage') }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-cog me-2"></i>Manage
                                </a>
                            @endif
                            <a href="{{ route('modules.files.digital.search') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-search me-2"></i>Search
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-folder fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Folders</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $stats['total_folders'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="bx bx-trending-up me-1"></i>All Folders
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="bx bx-file fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Files</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $stats['total_files'] ?? 0 }}</h3>
                            <small class="text-success">
                                <i class="bx bx-check-circle me-1"></i>All Files
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                            <i class="bx bx-hdd fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Storage Used</h6>
                            <h3 class="mb-0 fw-bold text-info">{{ number_format(($stats['total_storage'] ?? 0) / 1024 / 1024, 2) }} MB</h3>
                            <small class="text-info">
                                <i class="bx bx-data me-1"></i>Total Size
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="bx bx-user-check fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Pending Requests</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $stats['pending_requests'] ?? 0 }}</h3>
                            <small class="text-warning">
                                <i class="bx bx-time me-1"></i>Awaiting Review
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white border-bottom">
                    <h5 class="mb-0 fw-bold text-white">
                        <i class="bx bx-bolt-circle me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if($canManageFiles)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.digital.upload') }}" class="card border-primary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-primary">
                                        <i class="bx bx-upload fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Upload Files</h6>
                                    <small class="text-muted">Upload new files</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.digital.manage') }}" class="card border-info h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                                        <i class="bx bx-cog fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Manage Files</h6>
                                    <small class="text-muted">Organize & manage</small>
                                </div>
                            </a>
                        </div>
                        @endif
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.digital.search') }}" class="card border-success h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                                        <i class="bx bx-search fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Search Files</h6>
                                    <small class="text-muted">Find files quickly</small>
                                </div>
                            </a>
                        </div>
                        
                        @if($canManageFiles)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.digital.analytics') }}" class="card border-warning h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                                        <i class="bx bx-bar-chart fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Analytics</h6>
                                    <small class="text-muted">View statistics</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.digital.access-requests') }}" class="card border-danger h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                                        <i class="bx bx-user-check fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Access Requests</h6>
                                    <small class="text-muted">{{ $stats['pending_requests'] ?? 0 }} pending</small>
                                </div>
                            </a>
                        </div>
                        @endif
                        
                        @if($canManageFiles)
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.digital.activity-log') }}" class="card border-secondary h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-secondary">
                                        <i class="bx bx-history fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Activity Log</h6>
                                    <small class="text-muted">View all activities</small>
                                </div>
                            </a>
                        </div>
                        
                        <div class="col-lg-3 col-md-6">
                            <a href="{{ route('modules.files.digital.settings') }}" class="card border-dark h-100 text-decoration-none hover-lift">
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl mx-auto mb-3 bg-dark">
                                        <i class="bx bx-cog fs-1 text-white"></i>
                                    </div>
                                    <h6 class="fw-bold mb-1">Settings</h6>
                                    <small class="text-muted">Configure system</small>
                                </div>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Folders Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-folder me-2"></i>All Folders
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="input-group" style="max-width: 300px;">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="folder-search-input" placeholder="Search folders...">
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" id="view-grid">
                                    <i class="bx bx-grid-alt"></i> Grid
                                </button>
                                <button class="btn btn-sm btn-outline-primary active" id="view-list">
                                    <i class="bx bx-list-ul"></i> List
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($rootFolders->count() > 0)
                        <div id="folders-container" class="folders-list-view">
                            @foreach($rootFolders as $folder)
                                @include('modules.files.digital.partials.folder-item', ['folder' => $folder, 'level' => 0, 'foldersByParent' => $foldersByParent])
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-folder-open fs-1 text-muted mb-3"></i>
                            <p class="text-muted">No folders found. Create your first folder to get started.</p>
                            @if($canManageFiles)
                                <button class="btn btn-primary" id="create-folder-btn">
                                    <i class="bx bx-folder-plus me-2"></i>Create Folder
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if($recentActivity->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-history me-2"></i>Recent Activity
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Action</th>
                                    <th>User</th>
                                    <th>File/Folder</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentActivity as $activity)
                                <tr>
                                    <td>
                                        <span class="badge bg-label-{{ $activity->action_type === 'upload' ? 'success' : ($activity->action_type === 'delete' ? 'danger' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $activity->action_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $activity->user->name ?? 'System' }}</td>
                                    <td>
                                        @if($activity->file)
                                            <i class="bx bx-file me-1"></i>{{ $activity->file->original_name }}
                                        @elseif($activity->folder)
                                            <i class="bx bx-folder me-1"></i>{{ $activity->folder->name }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $activity->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
.hover-lift {
    transition: all 0.3s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
}
.folders-list-view .folder-item {
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}
.folders-list-view .folder-item:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
    transform: translateX(5px);
}
.folders-grid-view {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}
.folders-grid-view .folder-item {
    padding: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    text-align: center;
    transition: all 0.3s ease;
}
.folders-grid-view .folder-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    const ajaxUrl = '{{ route("modules.files.digital.ajax") }}';
    
    // View toggle
    $('#view-grid').click(function() {
        $('#folders-container').removeClass('folders-list-view').addClass('folders-grid-view');
        $(this).addClass('active');
        $('#view-list').removeClass('active');
    });
    
    $('#view-list').click(function() {
        $('#folders-container').removeClass('folders-grid-view').addClass('folders-list-view');
        $(this).addClass('active');
        $('#view-grid').removeClass('active');
    });
    
    // Live search for folders
    let searchTimeout;
    $('#folder-search-input').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().toLowerCase();
        
        searchTimeout = setTimeout(() => {
            $('.folder-item').each(function() {
                const folderName = $(this).find('.folder-link').text().toLowerCase();
                if (folderName.includes(searchTerm) || searchTerm === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }, 300);
    });
    
    // View folder
    $(document).on('click', '.view-folder-btn', function(e) {
        e.preventDefault();
        const folderId = $(this).data('folder-id');
        window.location.href = '{{ route("modules.files.digital.folder.detail", ":id") }}'.replace(':id', folderId);
    });
    
    $(document).on('click', '.folder-link', function(e) {
        e.preventDefault();
        const folderId = $(this).data('folder-id');
        if (folderId) {
            window.location.href = '{{ route("modules.files.digital.folder.detail", ":id") }}'.replace(':id', folderId);
        }
    });
    
    // Edit folder
    $(document).on('click', '.edit-folder-btn', function() {
        const folderId = $(this).data('folder-id');
        editFolder(folderId);
    });
    
    // Create folder
    $('#create-folder-btn').click(function() {
        createFolder();
    });
    
    function viewFolderContents(folderId) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'get_folder_contents',
                folder_id: folderId,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    // Display folder contents in a modal or navigate
                    Swal.fire({
                        title: 'Folder Contents',
                        html: buildFolderContentsHtml(response.files, response.subfolders),
                        width: '800px',
                        showCloseButton: true,
                        showConfirmButton: false
                    });
                }
            }
        });
    }
    
    function buildFolderContentsHtml(files, subfolders) {
        let html = '<div class="text-start">';
        
        if (subfolders && subfolders.length > 0) {
            html += '<h6>Subfolders:</h6><ul>';
            subfolders.forEach(folder => {
                html += `<li><i class="bx bx-folder me-1"></i>${escapeHtml(folder.name)}</li>`;
            });
            html += '</ul>';
        }
        
        if (files && files.length > 0) {
            html += '<h6 class="mt-3">Files:</h6><ul>';
            files.forEach(file => {
                html += `<li><i class="bx bx-file me-1"></i>${escapeHtml(file.original_name)}</li>`;
            });
            html += '</ul>';
        }
        
        if ((!files || files.length === 0) && (!subfolders || subfolders.length === 0)) {
            html += '<p class="text-muted">This folder is empty</p>';
        }
        
        html += '</div>';
        return html;
    }
    
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
                                description: $('#edit-folder-description').val()
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
                name: data.name,
                description: data.description,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', 'Folder updated successfully!', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Failed to update folder', 'error');
                }
            }
        });
    }
    
    function createFolder() {
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
                    description: $('#folder-description').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'create_folder',
                        name: result.value.name,
                        description: result.value.description,
                        _token: csrfToken
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', 'Folder created successfully!', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message || 'Failed to create folder', 'error');
                        }
                    }
                });
            }
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>
@endpush
@endsection

