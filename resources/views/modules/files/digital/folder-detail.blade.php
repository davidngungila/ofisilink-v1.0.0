@extends('layouts.app')

@section('title', $folder->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('modules.files.digital.dashboard') }}">
                            <i class="bx bx-home"></i> Dashboard
                        </a>
                    </li>
                    @foreach($breadcrumbs as $crumb)
                        @if(!$loop->last)
                            <li class="breadcrumb-item">
                                <a href="{{ route('modules.files.digital.folder.detail', $crumb->id) }}">{{ $crumb->name }}</a>
                            </li>
                        @else
                            <li class="breadcrumb-item active">{{ $crumb->name }}</li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-folder me-2"></i>{{ $folder->name }}
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                @if($folder->description)
                                    {{ $folder->description }}
                                @else
                                    Folder containing {{ $folder->files_count }} file(s) and {{ $folder->subfolders_count }} subfolder(s)
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($canManageFiles)
                                <button class="btn btn-light btn-lg shadow-sm" onclick="showCreateSubfolderModal()">
                                    <i class="bx bx-folder-plus me-2"></i>Create Subfolder
                                </button>
                                <button class="btn btn-light btn-lg shadow-sm" onclick="showAssignModal()">
                                    <i class="bx bx-user-plus me-2"></i>Assign to Staff
                                </button>
                                <a href="{{ route('modules.files.digital.upload') }}?folder_id={{ $folder->id }}" class="btn btn-light btn-lg shadow-sm">
                                    <i class="bx bx-upload me-2"></i>Upload Files
                                </a>
                            @endif
                            <a href="{{ route('modules.files.digital.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Folder Info Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-folder fs-1 text-primary mb-2"></i>
                    <h4 class="mb-0">{{ $folder->subfolders_count }}</h4>
                    <small class="text-muted">Subfolders</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-file fs-1 text-success mb-2"></i>
                    <h4 class="mb-0">{{ $folder->files_count }}</h4>
                    <small class="text-muted">Files</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-lock-alt fs-1 text-warning mb-2"></i>
                    <h4 class="mb-0 text-capitalize">{{ $folder->access_level }}</h4>
                    <small class="text-muted">Access Level</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-user fs-1 text-info mb-2"></i>
                    <h4 class="mb-0">{{ $folderAssignments->count() }}</h4>
                    <small class="text-muted">Assigned Users</small>
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
                            <a class="nav-link active" data-bs-toggle="tab" href="#subfolders">
                                <i class="bx bx-folder me-2"></i>Subfolders ({{ $subfolders->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#files">
                                <i class="bx bx-file me-2"></i>Files ({{ $files->total() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#assignments">
                                <i class="bx bx-user-check me-2"></i>Assignments ({{ $folderAssignments->count() }})
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Subfolders Tab -->
                        <div class="tab-pane fade show active" id="subfolders">
                            @if($canManageFiles)
                                <div class="mb-3">
                                    <button class="btn btn-primary" onclick="showCreateSubfolderModal()">
                                        <i class="bx bx-folder-plus me-2"></i>Create New Subfolder
                                    </button>
                                </div>
                            @endif
                            
                            @if($subfolders->count() > 0)
                                <div class="row g-3">
                                    @foreach($subfolders as $subfolder)
                                        <div class="col-lg-3 col-md-4 col-sm-6">
                                            <div class="card border-0 shadow-sm h-100 folder-card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-start mb-2">
                                                        <i class="bx bx-folder fs-1 text-primary me-2"></i>
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1">
                                                                <a href="{{ route('modules.files.digital.folder.detail', $subfolder->id) }}" class="text-decoration-none">
                                                                    {{ $subfolder->name }}
                                                                </a>
                                                            </h6>
                                                            <small class="text-muted">
                                                                {{ $subfolder->files_count }} files, {{ $subfolder->subfolders_count }} folders
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <span class="badge bg-{{ $subfolder->access_level === 'public' ? 'success' : ($subfolder->access_level === 'department' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($subfolder->access_level) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bx bx-folder-open fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">No subfolders in this folder</p>
                                    @if($canManageFiles)
                                        <button class="btn btn-primary" onclick="showCreateSubfolderModal()">
                                            <i class="bx bx-folder-plus me-2"></i>Create First Subfolder
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Files Tab -->
                        <div class="tab-pane fade" id="files">
                            @if($files->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>File Name</th>
                                                <th>Size</th>
                                                <th>Uploaded By</th>
                                                <th>Upload Date</th>
                                                <th>Access Level</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($files as $file)
                                                <tr>
                                                    <td>
                                                        <i class="bx bx-file me-2"></i>
                                                        <strong>{{ $file->original_name }}</strong>
                                                    </td>
                                                    <td>{{ $file->formatted_size }}</td>
                                                    <td>{{ $file->uploader->name ?? 'N/A' }}</td>
                                                    <td>{{ $file->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ $file->access_level === 'public' ? 'success' : ($file->access_level === 'department' ? 'warning' : 'danger') }}">
                                                            {{ ucfirst($file->access_level) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <button class="btn btn-sm btn-primary download-file-btn" data-file-id="{{ $file->id }}" title="Download">
                                                                <i class="bx bx-download"></i>
                                                            </button>
                                                            @if($canManageFiles)
                                                                <button class="btn btn-sm btn-success assign-file-btn" data-file-id="{{ $file->id }}" data-file-name="{{ $file->original_name }}" title="Assign to Staff">
                                                                    <i class="bx bx-user-plus"></i>
                                                                </button>
                                                                <button class="btn btn-sm btn-info edit-file-btn" data-file-id="{{ $file->id }}" title="Edit">
                                                                    <i class="bx bx-edit"></i>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    {{ $files->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bx bx-file fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">No files in this folder</p>
                                    @if($canManageFiles)
                                        <a href="{{ route('modules.files.digital.upload') }}?folder_id={{ $folder->id }}" class="btn btn-primary">
                                            <i class="bx bx-upload me-2"></i>Upload Files
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Assignments Tab -->
                        <div class="tab-pane fade" id="assignments">
                            @if($canManageFiles)
                                <div class="mb-3">
                                    <button class="btn btn-primary" onclick="showAssignModal()">
                                        <i class="bx bx-user-plus me-2"></i>Assign to Staff
                                    </button>
                                </div>
                            @endif
                            
                            @if($folderAssignments->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Permission Level</th>
                                                <th>Assigned Date</th>
                                                <th>Expiry Date</th>
                                                <th>Status</th>
                                                @if($canManageFiles)
                                                    <th>Actions</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($folderAssignments as $assignment)
                                                <tr>
                                                    <td>{{ $assignment->user->name ?? 'N/A' }}</td>
                                                    <td>
                                                        <span class="badge bg-info">{{ ucfirst($assignment->permission_level) }}</span>
                                                    </td>
                                                    <td>{{ $assignment->created_at->format('M d, Y') }}</td>
                                                    <td>
                                                        @if($assignment->expiry_date)
                                                            {{ \Carbon\Carbon::parse($assignment->expiry_date)->format('M d, Y') }}
                                                        @else
                                                            <span class="text-muted">Never</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($assignment->expiry_date && \Carbon\Carbon::parse($assignment->expiry_date)->isPast())
                                                            <span class="badge bg-danger">Expired</span>
                                                        @else
                                                            <span class="badge bg-success">Active</span>
                                                        @endif
                                                    </td>
                                                    @if($canManageFiles)
                                                        <td>
                                                            <button class="btn btn-sm btn-danger" onclick="removeAssignment({{ $assignment->id }})">
                                                                <i class="bx bx-trash"></i>
                                                            </button>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bx bx-user-x fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">No user assignments for this folder</p>
                                    @if($canManageFiles)
                                        <button class="btn btn-primary" onclick="showAssignModal()">
                                            <i class="bx bx-user-plus me-2"></i>Assign to Staff
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Subfolder Modal -->
<div class="modal fade" id="createSubfolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Subfolder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createSubfolderForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="create_nested_folder">
                    <input type="hidden" name="parent_id" value="{{ $folder->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Folder Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Access Level <span class="text-danger">*</span></label>
                        <select class="form-select" name="access_level" required>
                            <option value="public">Public</option>
                            <option value="department">Department</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Department (if Department access)</label>
                        <select class="form-select" name="department_id">
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Folder</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Folder Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Folder to Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="assignForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" value="assign_file_folder">
                    <input type="hidden" name="type" value="folder">
                    <input type="hidden" name="item_id" value="{{ $folder->id }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Select Users <span class="text-danger">*</span></label>
                        <select class="form-select" name="user_ids[]" id="user_ids" multiple required>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple users</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permission Level</label>
                        <select class="form-select" name="permission_level">
                            <option value="view">View Only</option>
                            <option value="download">Download</option>
                            <option value="edit">Edit</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Expiry Duration</label>
                        <select class="form-select" name="expiry_duration" id="expiry_duration">
                            <option value="never">Never Expire</option>
                            <option value="1week">1 Week</option>
                            <option value="2weeks">2 Weeks</option>
                            <option value="4weeks">4 Weeks</option>
                            <option value="1month">1 Month</option>
                            <option value="3months">3 Months</option>
                            <option value="6months">6 Months</option>
                            <option value="1year">1 Year</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="custom_expiry" style="display: none;">
                        <label class="form-label">Custom Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showCreateSubfolderModal() {
    new bootstrap.Modal(document.getElementById('createSubfolderModal')).show();
}

function showAssignModal() {
    new bootstrap.Modal(document.getElementById('assignModal')).show();
}

$('#expiry_duration').on('change', function() {
    if ($(this).val() === 'never') {
        $('#custom_expiry').hide();
    } else {
        $('#custom_expiry').show();
    }
});

$('#createSubfolderForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'An error occurred', 'error');
        }
    });
});

$('#assignForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'An error occurred', 'error');
        }
    });
});

function removeAssignment(assignmentId) {
    Swal.fire({
        title: 'Remove Assignment?',
        text: 'Are you sure you want to remove this assignment?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Remove',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("modules.files.digital.ajax") }}',
                type: 'POST',
                data: {
                    action: 'remove_user_assignment',
                    assignment_id: assignmentId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }
            });
        }
    });
}

// Handle file download
$(document).on('click', '.download-file-btn', function() {
    const fileId = $(this).data('file-id');
    
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: {
            action: 'download_file',
            file_id: fileId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success && response.download_url) {
                window.open(response.download_url, '_blank');
            } else {
                Swal.fire('Error', response.message || 'Failed to download file', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'An error occurred while downloading the file', 'error');
        }
    });
});

// Handle assign individual file
$(document).on('click', '.assign-file-btn', function() {
    const fileId = $(this).data('file-id');
    const fileName = $(this).data('file-name') || 'File';
    
    // Get users list
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: {
            action: 'get_users_for_assignment',
            _token: '{{ csrf_token() }}'
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
                    <input type="date" class="form-control" name="expiry_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
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
            assignFile(fileId, result.value);
        }
    });
}

function assignFile(fileId, data) {
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: {
            action: 'assign_file_folder',
            type: 'file',
            item_id: fileId,
            user_ids: data.user_ids,
            permission_level: data.permission_level,
            expiry_duration: data.expiry_duration,
            expiry_date: data.expiry_date,
            _token: '{{ csrf_token() }}'
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
            Swal.fire('Error', response?.message || 'An error occurred', 'error');
        }
    });
}

// Handle edit file
$(document).on('click', '.edit-file-btn', function() {
    const fileId = $(this).data('file-id');
    editFile(fileId);
});

function editFile(fileId) {
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: {
            action: 'get_file_details',
            file_id: fileId,
            _token: '{{ csrf_token() }}'
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
                                <input type="text" class="form-control" value="${escapeHtml(file.original_name || '')}" readonly>
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
            Swal.fire('Error', response?.message || 'An error occurred', 'error');
        }
    });
}

function updateFile(fileId, data) {
    $.ajax({
        url: '{{ route("modules.files.digital.ajax") }}',
        type: 'POST',
        data: {
            action: 'update_file',
            file_id: fileId,
            description: data.description,
            access_level: data.access_level,
            confidential_level: data.confidential_level,
            tags: data.tags,
            _token: '{{ csrf_token() }}'
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
            Swal.fire('Error', response?.message || 'An error occurred', 'error');
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
@endpush
@endsection

