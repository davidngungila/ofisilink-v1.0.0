@extends('layouts.app')

@section('title', $rack->name)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('modules.files.physical.dashboard') }}">
                            <i class="bx bx-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ $rack->name }}</li>
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
                                <i class="bx bx-archive me-2"></i>{{ $rack->name }}
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                @if($rack->description)
                                    {{ $rack->description }}
                                @else
                                    Rack containing {{ $rack->files_count ?? 0 }} file(s)
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($canManageFiles)
                                <button class="btn btn-light btn-lg shadow-sm" onclick="showAddFileModal()">
                                    <i class="bx bx-file-plus me-2"></i>Add File
                                </button>
                                <button class="btn btn-light btn-lg shadow-sm" onclick="showBulkCreateRacksModal()">
                                    <i class="bx bx-file me-2"></i>Bulk Create Racks
                                </button>
                                <button class="btn btn-light btn-lg shadow-sm" onclick="showEditRackModal()">
                                    <i class="bx bx-edit me-2"></i>Edit Rack
                                </button>
                            @endif
                            <a href="{{ route('modules.files.physical.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rack Info Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-file fs-1 text-primary mb-2"></i>
                    <h4 class="mb-0">{{ $rack->files_count ?? 0 }}</h4>
                    <small class="text-muted">Total Files</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-package fs-1 text-warning mb-2"></i>
                    <h4 class="mb-0">{{ $files->where('status', 'issued')->count() }}</h4>
                    <small class="text-muted">Issued Files</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-category fs-1 text-info mb-2"></i>
                    <h4 class="mb-0">{{ $rack->category->name ?? 'N/A' }}</h4>
                    <small class="text-muted">Category</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-map fs-1 text-success mb-2"></i>
                    <h4 class="mb-0">{{ $rack->location ?? 'N/A' }}</h4>
                    <small class="text-muted">Location</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Rack Details -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Rack Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Rack Number:</strong></td>
                            <td>{{ $rack->rack_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Range:</strong></td>
                            <td>{{ $rack->rack_range_start ?? 'N/A' }} - {{ $rack->rack_range_end ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Department:</strong></td>
                            <td>{{ $rack->department->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Access Level:</strong></td>
                            <td>
                                <span class="badge bg-label-{{ $rack->access_level === 'public' ? 'success' : ($rack->access_level === 'department' ? 'info' : 'warning') }}">
                                    {{ ucfirst($rack->access_level) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created By:</strong></td>
                            <td>{{ $rack->creator->name ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $rack->created_at->format('M d, Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body">
                    @if($activities->count() > 0)
                        <div class="activity-timeline">
                            @foreach($activities->take(5) as $activity)
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $activity->user->name ?? 'System' }}</strong>
                                            <small class="text-muted d-block">{{ ucfirst(str_replace('_', ' ', $activity->activity_type ?? 'activity')) }}</small>
                                        </div>
                                        <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">No recent activity</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Files Section -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-file me-2"></i>Files ({{ $files->total() }})
                        </h5>
                        @if($canManageFiles)
                            <button class="btn btn-primary btn-sm" onclick="showAddFileModal()">
                                <i class="bx bx-file-plus me-2"></i>Add File
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($files->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>File Name</th>
                                        <th>File Number</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Current Holder</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($files as $file)
                                        <tr>
                                            <td>
                                                <i class="bx bx-file me-2 text-primary"></i>
                                                <strong>{{ $file->file_name }}</strong>
                                            </td>
                                            <td>{{ $file->file_number ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-label-info">{{ $file->file_type ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($file->status === 'issued')
                                                    <span class="badge bg-label-warning">Issued</span>
                                                @elseif($file->status === 'archived')
                                                    <span class="badge bg-label-secondary">Archived</span>
                                                @else
                                                    <span class="badge bg-label-success">Available</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($file->holder)
                                                    {{ $file->holder->name }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>{{ $file->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    {{-- View button - available to all users --}}
                                                    <button class="btn btn-sm btn-outline-primary view-file" data-file-id="{{ $file->id }}">
                                                        <i class="bx bx-show"></i> View
                                                    </button>
                                                    
                                                    {{-- Request button - only for staff on available files from public/department racks --}}
                                                    @if($file->status === 'available' && !$canManageFiles)
                                                        @if($rack->access_level === 'public' || ($rack->access_level === 'department' && $rack->department_id == ($user->department_id ?? null)))
                                                            <button class="btn btn-sm btn-outline-success request-file" data-file-id="{{ $file->id }}" data-file-name="{{ $file->file_name }}" data-file-number="{{ $file->file_number }}">
                                                                <i class="bx bx-clipboard"></i> Request
                                                            </button>
                                                        @endif
                                                    @endif
                                                    
                                                    {{-- Admin/HOD/HR buttons - full access --}}
                                                    @if($canManageFiles)
                                                        @if($file->status === 'available')
                                                            <button class="btn btn-sm btn-outline-warning assign-file-direct" data-file-id="{{ $file->id }}" data-file-name="{{ $file->file_name }}">
                                                                <i class="bx bx-user-plus"></i> Assign
                                                            </button>
                                                        @endif
                                                        <button class="btn btn-sm btn-outline-info edit-file" data-file-id="{{ $file->id }}">
                                                            <i class="bx bx-edit"></i> Edit
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
                            <p class="text-muted">No files in this rack yet.</p>
                            @if($canManageFiles)
                                <button class="btn btn-primary" onclick="showAddFileModal()">
                                    <i class="bx bx-file-plus me-2"></i>Add First File
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Ensure SweetAlert and modals appear in front of all elements */
    .swal2-container {
        z-index: 200000 !important;
    }
    .swal2-popup {
        z-index: 200001 !important;
    }
    .modal {
        z-index: 1055 !important;
    }
    .modal-backdrop {
        z-index: 1054 !important;
    }
    body.modal-open {
        overflow: hidden;
    }
    /* Ensure all popups are visible */
    .swal2-container.swal2-backdrop-show {
        background-color: rgba(0, 0, 0, 0.4) !important;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
const ajaxUrl = '{{ route("modules.files.physical.ajax") }}';
const csrfToken = '{{ csrf_token() }}';
const rackId = {{ $rack->id }};

// Add File Modal
function showAddFileModal() {
    Swal.fire({
        title: 'Add File to Rack',
        html: `
            <form id="add-file-form">
                <div class="mb-3">
                    <label class="form-label">File Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="file-name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">File Number</label>
                    <input type="text" class="form-control" id="file-number">
                </div>
                <div class="mb-3">
                    <label class="form-label">File Type</label>
                    <input type="text" class="form-control" id="file-type" placeholder="e.g., Document, Contract, etc.">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="file-description" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">File Date</label>
                    <input type="date" class="form-control" id="file-date">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confidentiality Level</label>
                    <select class="form-select" id="confidential-level">
                        <option value="normal">Normal</option>
                        <option value="confidential">Confidential</option>
                        <option value="restricted">Restricted</option>
                    </select>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add File',
        preConfirm: () => {
            return {
                file_name: $('#file-name').val(),
                file_number: $('#file-number').val(),
                file_type: $('#file-type').val(),
                description: $('#file-description').val(),
                file_date: $('#file-date').val(),
                confidential_level: $('#confidential-level').val()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            addFile(result.value);
        }
    });
}

function addFile(data) {
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'create_rack_file',
            folder_id: rackId,
            file_name: data.file_name,
            file_number: data.file_number,
            file_type: data.file_type,
            description: data.description,
            file_date: data.file_date,
            confidential_level: data.confidential_level,
            _token: csrfToken
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message || 'File added successfully!', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', response.message || 'Failed to add file', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'An error occurred', 'error');
        }
    });
}

// Edit Rack Modal
function showEditRackModal() {
    Swal.fire({
        title: 'Edit Rack',
        html: `
            <form id="edit-rack-form">
                <div class="mb-3">
                    <label class="form-label">Rack Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="edit-rack-name" value="{{ $rack->name }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" id="edit-rack-description" rows="3">{{ $rack->description ?? '' }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" class="form-control" id="edit-rack-location" value="{{ $rack->location ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Access Level</label>
                    <select class="form-select" id="edit-rack-access-level">
                        <option value="public" {{ $rack->access_level === 'public' ? 'selected' : '' }}>Public</option>
                        <option value="department" {{ $rack->access_level === 'department' ? 'selected' : '' }}>Department</option>
                        <option value="private" {{ $rack->access_level === 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Save Changes',
        width: '600px',
        preConfirm: () => {
            const name = $('#edit-rack-name').val();
            if (!name) {
                Swal.showValidationMessage('Rack name is required');
                return false;
            }
            return {
                name: name,
                description: $('#edit-rack-description').val(),
                location: $('#edit-rack-location').val(),
                access_level: $('#edit-rack-access-level').val()
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            updateRack(result.value);
        }
    });
}

function updateRack(data) {
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'update_rack_folder',
            folder_id: rackId,
            name: data.name,
            description: data.description,
            location: data.location,
            access_level: data.access_level,
            _token: csrfToken
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success', response.message || 'Rack updated successfully!', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', response.message || 'Failed to update rack', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            let errorMessage = 'An error occurred while updating the rack.';
            
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

// View File
$(document).on('click', '.view-file', function() {
    const fileId = $(this).data('file-id');
    
    Swal.fire({
        title: 'Loading...',
        text: 'Please wait while we fetch file details.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'get_rack_file_details',
            file_id: fileId,
            _token: csrfToken
        },
        success: function(response) {
            if (response.success && response.file) {
                const file = response.file;
                const activeRequest = response.active_request;
                const requests = response.requests || [];
                
                // Format file date
                const fileDate = file.file_date ? new Date(file.file_date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                }) : 'N/A';
                
                // Format created date
                const createdDate = file.created_at ? new Date(file.created_at).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                }) : 'N/A';
                
                // Format last returned date
                const lastReturned = file.last_returned ? new Date(file.last_returned).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : 'N/A';
                
                // Status badge
                let statusBadge = '';
                if (file.status === 'issued') {
                    statusBadge = '<span class="badge bg-label-warning">Issued</span>';
                } else if (file.status === 'archived') {
                    statusBadge = '<span class="badge bg-label-secondary">Archived</span>';
                } else {
                    statusBadge = '<span class="badge bg-label-success">Available</span>';
                }
                
                // Confidentiality badge
                let confBadge = '';
                if (file.confidential_level === 'strictly_confidential') {
                    confBadge = '<span class="badge bg-label-danger">Strictly Confidential</span>';
                } else if (file.confidential_level === 'confidential') {
                    confBadge = '<span class="badge bg-label-warning">Confidential</span>';
                } else {
                    confBadge = '<span class="badge bg-label-info">Normal</span>';
                }
                
                // Active request info
                let activeRequestHtml = '';
                if (activeRequest) {
                    const expectedReturn = activeRequest.expected_return_date ? 
                        new Date(activeRequest.expected_return_date).toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'long', 
                            day: 'numeric' 
                        }) : 'N/A';
                    activeRequestHtml = `
                        <div class="alert alert-info mb-3">
                            <h6 class="mb-2"><i class="bx bx-info-circle me-2"></i>Current Assignment</h6>
                            <p class="mb-1"><strong>Holder:</strong> ${activeRequest.requester ? activeRequest.requester.name : 'N/A'}</p>
                            <p class="mb-1"><strong>Expected Return:</strong> ${expectedReturn}</p>
                            <p class="mb-0"><strong>Purpose:</strong> ${activeRequest.purpose || 'N/A'}</p>
                        </div>
                    `;
                }
                
                // Requests history
                let requestsHtml = '';
                if (requests.length > 0) {
                    requestsHtml = '<div class="mt-3"><h6 class="mb-2">Request History</h6><div class="table-responsive" style="max-height: 200px; overflow-y: auto;"><table class="table table-sm table-bordered"><thead><tr><th>Requester</th><th>Status</th><th>Date</th></tr></thead><tbody>';
                    requests.slice(0, 5).forEach(req => {
                        const reqDate = req.created_at ? new Date(req.created_at).toLocaleDateString('en-US', { 
                            year: 'numeric', 
                            month: 'short', 
                            day: 'numeric' 
                        }) : 'N/A';
                        let reqStatus = '';
                        if (req.status === 'approved') {
                            reqStatus = '<span class="badge bg-label-success">Approved</span>';
                        } else if (req.status === 'rejected') {
                            reqStatus = '<span class="badge bg-label-danger">Rejected</span>';
                        } else {
                            reqStatus = '<span class="badge bg-label-warning">Pending</span>';
                        }
                        requestsHtml += `<tr><td>${req.requester ? req.requester.name : 'N/A'}</td><td>${reqStatus}</td><td>${reqDate}</td></tr>`;
                    });
                    requestsHtml += '</tbody></table></div></div>';
                }
                
                Swal.fire({
                    title: `<i class="bx bx-file me-2"></i>${file.file_name || 'File Details'}`,
                    html: `
                        <div class="text-start">
                            ${activeRequestHtml}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>File Number:</strong><br>
                                    <span class="text-muted">${file.file_number || 'N/A'}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Status:</strong><br>
                                    ${statusBadge}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>File Type:</strong><br>
                                    <span class="badge bg-label-info">${file.file_type || 'N/A'}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Confidentiality:</strong><br>
                                    ${confBadge}
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Rack Folder:</strong><br>
                                    <span class="text-muted">${file.folder ? file.folder.name : 'N/A'}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Category:</strong><br>
                                    <span class="text-muted">${file.folder && file.folder.category ? file.folder.category.name : 'N/A'}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Current Holder:</strong><br>
                                    <span class="text-muted">${file.holder ? file.holder.name : 'N/A'}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>File Date:</strong><br>
                                    <span class="text-muted">${fileDate}</span>
                                </div>
                            </div>
                            ${file.description ? `
                                <div class="mb-3">
                                    <strong>Description:</strong><br>
                                    <span class="text-muted">${file.description}</span>
                                </div>
                            ` : ''}
                            ${file.tags ? `
                                <div class="mb-3">
                                    <strong>Tags:</strong><br>
                                    <span class="text-muted">${file.tags}</span>
                                </div>
                            ` : ''}
                            ${file.notes ? `
                                <div class="mb-3">
                                    <strong>Notes:</strong><br>
                                    <span class="text-muted">${file.notes}</span>
                                </div>
                            ` : ''}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Retention Period:</strong><br>
                                    <span class="text-muted">${file.retention_period ? file.retention_period + ' years' : 'N/A'}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Last Returned:</strong><br>
                                    <span class="text-muted">${lastReturned}</span>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Created By:</strong><br>
                                    <span class="text-muted">${file.creator ? file.creator.name : 'System'}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Created:</strong><br>
                                    <span class="text-muted">${createdDate}</span>
                                </div>
                            </div>
                            ${requestsHtml}
                        </div>
                    `,
                    width: '700px',
                    confirmButtonText: 'Close',
                    customClass: {
                        popup: 'text-start'
                    }
                });
            } else {
                Swal.fire('Error', 'Failed to load file details', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'An error occurred while loading file details', 'error');
        }
    });
});

// Request File (for Staff)
$(document).on('click', '.request-file', function() {
    const fileId = $(this).data('file-id');
    const fileName = $(this).data('file-name') || 'this file';
    const fileNumber = $(this).data('file-number') || '';
    
    Swal.fire({
        title: 'Request Physical File',
        html: `
            <form id="request-file-form">
                <div class="mb-3">
                    <label class="form-label">File <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" value="${fileName}${fileNumber ? ' (' + fileNumber + ')' : ''}" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Purpose <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="request-purpose" rows="4" required minlength="10" maxlength="500" placeholder="Please provide a detailed purpose for requesting this file (minimum 10 characters)..."></textarea>
                    <small class="text-muted">Minimum 10 characters, maximum 500 characters</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="request-return-date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Urgency <span class="text-danger">*</span></label>
                    <select class="form-select" id="request-urgency" required>
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonText: 'Submit Request',
        cancelButtonText: 'Cancel',
        width: '600px',
        preConfirm: () => {
            const purpose = $('#request-purpose').val();
            const returnDate = $('#request-return-date').val();
            const urgency = $('#request-urgency').val();
            
            if (!purpose || purpose.length < 10) {
                Swal.showValidationMessage('Purpose must be at least 10 characters long');
                return false;
            }
            if (!returnDate) {
                Swal.showValidationMessage('Expected return date is required');
                return false;
            }
            if (!urgency) {
                Swal.showValidationMessage('Please select urgency level');
                return false;
            }
            
            return {
                file_id: fileId,
                purpose: purpose,
                expected_return_date: returnDate,
                urgency: urgency
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            submitFileRequest(result.value);
        }
    });
});

function submitFileRequest(data) {
    Swal.fire({
        title: 'Submitting Request...',
        text: 'Please wait while we process your request.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'request_physical_file',
            file_id: data.file_id,
            purpose: data.purpose,
            expected_return_date: data.expected_return_date,
            urgency: data.urgency,
            _token: csrfToken
        },
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Request Submitted!',
                    text: response.message || 'Your file request has been submitted and is pending approval. You will be notified once it\'s processed.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error', response.message || 'Failed to submit request', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            let errorMessage = 'An error occurred while submitting your request.';
            
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

// Assign File Directly (Admin/HOD/HR only)
$(document).on('click', '.assign-file-direct', function() {
    const fileId = $(this).data('file-id');
    const fileName = $(this).data('file-name') || 'this file';
    
    // Redirect to assign page with file pre-selected
    window.location.href = '{{ route("modules.files.physical.assign") }}?file_id=' + fileId;
});

// Edit File (Admin/HOD/HR only)
$(document).on('click', '.edit-file', function() {
    const fileId = $(this).data('file-id');
    
    Swal.fire({
        title: 'Loading...',
        text: 'Please wait while we fetch file details.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'get_rack_file_details',
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
                                <label class="form-label">File Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-file-name" value="${file.file_name || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">File Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-file-number" value="${file.file_number || ''}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">File Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit-file-type" required>
                                    <option value="general" ${file.file_type === 'general' ? 'selected' : ''}>General</option>
                                    <option value="contract" ${file.file_type === 'contract' ? 'selected' : ''}>Contract</option>
                                    <option value="financial" ${file.file_type === 'financial' ? 'selected' : ''}>Financial</option>
                                    <option value="legal" ${file.file_type === 'legal' ? 'selected' : ''}>Legal</option>
                                    <option value="hr" ${file.file_type === 'hr' ? 'selected' : ''}>HR</option>
                                    <option value="technical" ${file.file_type === 'technical' ? 'selected' : ''}>Technical</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confidentiality Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit-confidential-level" required>
                                    <option value="normal" ${file.confidential_level === 'normal' ? 'selected' : ''}>Normal</option>
                                    <option value="confidential" ${file.confidential_level === 'confidential' ? 'selected' : ''}>Confidential</option>
                                    <option value="strictly_confidential" ${file.confidential_level === 'strictly_confidential' ? 'selected' : ''}>Strictly Confidential</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">File Date</label>
                                <input type="date" class="form-control" id="edit-file-date" value="${file.file_date ? file.file_date.split('T')[0] : ''}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="edit-file-description" rows="3">${file.description || ''}</textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tags</label>
                                <input type="text" class="form-control" id="edit-file-tags" value="${file.tags || ''}" placeholder="tag1, tag2, tag3">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Retention Period (Years)</label>
                                <input type="number" class="form-control" id="edit-retention-period" value="${file.retention_period || ''}" min="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" id="edit-file-notes" rows="3">${file.notes || ''}</textarea>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Save Changes',
                    cancelButtonText: 'Cancel',
                    width: '700px',
                    preConfirm: () => {
                        const name = $('#edit-file-name').val();
                        const number = $('#edit-file-number').val();
                        const type = $('#edit-file-type').val();
                        const confLevel = $('#edit-confidential-level').val();
                        
                        if (!name) {
                            Swal.showValidationMessage('File name is required');
                            return false;
                        }
                        if (!number) {
                            Swal.showValidationMessage('File number is required');
                            return false;
                        }
                        if (!type) {
                            Swal.showValidationMessage('File type is required');
                            return false;
                        }
                        if (!confLevel) {
                            Swal.showValidationMessage('Confidentiality level is required');
                            return false;
                        }
                        
                        return {
                            file_id: fileId,
                            file_name: name,
                            file_number: number,
                            file_type: type,
                            confidential_level: confLevel,
                            file_date: $('#edit-file-date').val(),
                            description: $('#edit-file-description').val(),
                            tags: $('#edit-file-tags').val(),
                            retention_period: $('#edit-retention-period').val(),
                            notes: $('#edit-file-notes').val()
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        updateFile(result.value);
                    }
                });
            } else {
                Swal.fire('Error', 'Failed to load file details', 'error');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            Swal.fire('Error', response?.message || 'An error occurred while loading file details', 'error');
        }
    });
});

function updateFile(data) {
    Swal.fire({
        title: 'Updating File...',
        text: 'Please wait while we update the file.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: ajaxUrl,
        type: 'POST',
        data: {
            action: 'update_rack_file',
            ...data,
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
            let errorMessage = 'An error occurred while updating the file.';
            
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
</script>
@endpush
@endsection

