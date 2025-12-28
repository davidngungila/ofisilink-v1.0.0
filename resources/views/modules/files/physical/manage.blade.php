@extends('layouts.app')

@section('title', 'Manage Physical Racks & Files')

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
                                <i class="bx bx-cog me-2"></i>Manage Physical Racks & Files
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Organize, edit, and manage your physical rack folders and files
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

    <!-- Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#racks-tab">
                                <i class="bx bx-archive me-2"></i>Racks
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
                        <!-- Racks Tab -->
                        <div class="tab-pane fade show active" id="racks-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <h5 class="mb-0">All Racks</h5>
                                <div class="d-flex gap-2">
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" class="form-control" id="rack-search-manage" placeholder="Search racks...">
                                    </div>
                                    <button class="btn btn-success" id="bulk-create-racks-btn">
                                        <i class="bx bx-file me-2"></i>Bulk Create (Excel)
                                    </button>
                                    <button class="btn btn-primary" id="create-rack-btn">
                                        <i class="bx bx-folder-plus me-2"></i>Create Rack
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="rackTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Department</th>
                                            <th>Location</th>
                                            <th>Files</th>
                                            <th>Created By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="racks-tbody">
                                        @foreach($rackFolders as $rack)
                                        <tr class="rack-row" data-rack-name="{{ strtolower($rack->name) }}">
                                            <td>
                                                <i class="bx bx-archive text-primary me-2"></i>
                                                <strong>{{ $rack->name }}</strong>
                                            </td>
                                            <td>{{ $rack->category->name ?? 'N/A' }}</td>
                                            <td>{{ $rack->department->name ?? 'N/A' }}</td>
                                            <td>{{ $rack->location ?? 'N/A' }}</td>
                                            <td>{{ $rack->files_count ?? 0 }}</td>
                                            <td>{{ $rack->creator->name ?? 'System' }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('modules.files.physical.rack.detail', $rack->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-show"></i> View
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-info edit-rack" data-rack-id="{{ $rack->id }}">
                                                        <i class="bx bx-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-rack" data-rack-id="{{ $rack->id }}">
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

                        <!-- Files Tab -->
                        <div class="tab-pane fade" id="files-tab">
                            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                <h5 class="mb-0">All Files</h5>
                                <div class="d-flex gap-2">
                                    <div class="input-group" style="max-width: 300px;">
                                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                                        <input type="text" class="form-control" id="file-search-manage" placeholder="Search files...">
                                    </div>
                                    <a href="{{ route('modules.files.physical.upload') }}" class="btn btn-primary">
                                        <i class="bx bx-file-plus me-2"></i>Add File
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="fileTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Rack</th>
                                            <th>File Number</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="files-tbody">
                                        @foreach($files as $file)
                                        <tr class="file-row" data-file-name="{{ strtolower($file->file_name) }}">
                                            <td>
                                                <i class="bx bx-file text-info me-2"></i>
                                                <strong>{{ $file->file_name }}</strong>
                                            </td>
                                            <td>{{ $file->folder->name ?? 'N/A' }}</td>
                                            <td>{{ $file->file_number ?? 'N/A' }}</td>
                                            <td>{{ $file->file_type ?? 'N/A' }}</td>
                                            <td>
                                                @if($file->status === 'issued')
                                                    <span class="badge bg-label-warning">Issued</span>
                                                @elseif($file->status === 'archived')
                                                    <span class="badge bg-label-secondary">Archived</span>
                                                @else
                                                    <span class="badge bg-label-success">Available</span>
                                                @endif
                                            </td>
                                            <td>{{ $file->creator->name ?? 'System' }}</td>
                                            <td>{{ $file->created_at->format('M d, Y') }}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-primary view-file" data-file-id="{{ $file->id }}">
                                                        <i class="bx bx-show"></i> View
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info edit-file" data-file-id="{{ $file->id }}">
                                                        <i class="bx bx-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger delete-file" data-file-id="{{ $file->id }}">
                                                        <i class="bx bx-trash"></i> Delete
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
$(document).ready(function() {
    const csrfToken = '{{ csrf_token() }}';
    const ajaxUrl = '{{ route("modules.files.physical.ajax") }}';
    
    // Live search for racks
    $('#rack-search-manage').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.rack-row').each(function() {
            const rackName = $(this).data('rack-name') || '';
            if (rackName.includes(searchTerm) || searchTerm === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Live search for files
    $('#file-search-manage').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.file-row').each(function() {
            const fileName = $(this).data('file-name') || '';
            if (fileName.includes(searchTerm) || searchTerm === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Bulk Create Racks from Excel
    $('#bulk-create-racks-btn').click(function() {
        Swal.fire({
            title: 'Bulk Create Racks from Excel',
            html: `
                <div class="text-start">
                    <p class="mb-3">Upload a CSV or Excel file (.csv, .xlsx, .xls) to create multiple racks at once. CSV format is recommended for best compatibility.</p>
                    <div class="mb-3">
                        <a href="{{ route('modules.files.physical.download-rack-template') }}" class="btn btn-sm btn-outline-primary" target="_blank">
                            <i class="bx bx-download me-2"></i>Download Template
                        </a>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Excel/CSV File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="bulk-racks-file" accept=".csv,.xlsx,.xls" required>
                        <small class="text-muted">Maximum file size: 10MB</small>
                    </div>
                    <div class="alert alert-info">
                        <strong>CSV Format:</strong><br>
                        Required columns: <code>Rack Name</code>, <code>Category ID</code>, <code>Rack Range Start</code>, <code>Rack Range End</code>, <code>Access Level</code><br>
                        Optional columns: <code>Department ID</code>, <code>Location</code>, <code>Description</code>, <code>Notes</code>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Upload & Create',
            cancelButtonText: 'Cancel',
            width: '700px',
            preConfirm: () => {
                const fileInput = document.getElementById('bulk-racks-file');
                if (!fileInput.files || !fileInput.files[0]) {
                    Swal.showValidationMessage('Please select a file');
                    return false;
                }
                return fileInput.files[0];
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                uploadBulkRacks(result.value);
            }
        });
    });
    
    function uploadBulkRacks(file) {
        const formData = new FormData();
        formData.append('excel_file', file);
        formData.append('action', 'bulk_create_racks_excel');
        formData.append('_token', csrfToken);
        
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait while we create the racks.',
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
                    let message = response.message || 'Racks created successfully!';
                    if (response.errors && response.errors.length > 0) {
                        message += '<br><br><strong>Errors:</strong><ul style="text-align: left; max-height: 200px; overflow-y: auto;">';
                        response.errors.forEach(error => {
                            message += '<li>' + error + '</li>';
                        });
                        message += '</ul>';
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        html: message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to create racks', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while processing the file.';
                
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
    
    // Create Rack
    $('#create-rack-btn').click(function() {
        Swal.fire({
            title: 'Create New Rack',
            html: `
                <form id="create-rack-form">
                    <div class="mb-3">
                        <label class="form-label">Rack Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="rack-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="rack-category" required>
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Department</label>
                        <select class="form-select" id="rack-department">
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Access Level <span class="text-danger">*</span></label>
                        <select class="form-select" id="rack-access-level" required>
                            <option value="public" selected>Public</option>
                            <option value="department">Department</option>
                            <option value="private">Private</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rack Range Start <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="rack-range-start" min="1" value="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rack Range End <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="rack-range-end" min="1" value="100" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" id="rack-location">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="rack-description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="rack-notes" rows="2"></textarea>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Create',
            width: '600px',
            preConfirm: () => {
                const name = $('#rack-name').val();
                const categoryId = $('#rack-category').val();
                const rangeStart = parseInt($('#rack-range-start').val());
                const rangeEnd = parseInt($('#rack-range-end').val());
                const accessLevel = $('#rack-access-level').val();
                
                if (!name) {
                    Swal.showValidationMessage('Rack name is required');
                    return false;
                }
                if (!categoryId) {
                    Swal.showValidationMessage('Category is required');
                    return false;
                }
                if (!rangeStart || rangeStart < 1) {
                    Swal.showValidationMessage('Rack range start must be at least 1');
                    return false;
                }
                if (!rangeEnd || rangeEnd < 1) {
                    Swal.showValidationMessage('Rack range end must be at least 1');
                    return false;
                }
                if (rangeStart > rangeEnd) {
                    Swal.showValidationMessage('Rack range start cannot be greater than range end');
                    return false;
                }
                if (!accessLevel) {
                    Swal.showValidationMessage('Access level is required');
                    return false;
                }
                
                return {
                    folder_name: name,
                    category_id: categoryId,
                    department_id: $('#rack-department').val() || null,
                    access_level: accessLevel,
                    rack_range_start: rangeStart,
                    rack_range_end: rangeEnd,
                    location: $('#rack-location').val() || null,
                    description: $('#rack-description').val() || null,
                    notes: $('#rack-notes').val() || null
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                createRack(result.value);
            }
        });
    });
    
    function createRack(data) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'create_rack_folder',
                folder_name: data.folder_name,
                category_id: data.category_id,
                department_id: data.department_id,
                access_level: data.access_level,
                rack_range_start: data.rack_range_start,
                rack_range_end: data.rack_range_end,
                location: data.location,
                description: data.description,
                notes: data.notes,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'Rack created successfully!', 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', response.message || 'Failed to create rack', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while creating the rack.';
                
                if (response && response.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                } else if (response && response.message) {
                    errorMessage = response.message;
                } else if (xhr.status === 422) {
                    errorMessage = 'Validation failed. Please check all required fields.';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    html: errorMessage
                });
            }
        });
    }
    
    // Edit Rack
    $(document).on('click', '.edit-rack', function() {
        const rackId = $(this).data('rack-id');
        Swal.fire('Info', 'Edit rack functionality will be implemented', 'info');
    });
    
    // Delete Rack
    $(document).on('click', '.delete-rack', function() {
        const rackId = $(this).data('rack-id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the rack and all its files!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Implement delete functionality
                Swal.fire('Info', 'Delete rack functionality will be implemented', 'info');
            }
        });
    });
    
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
    
    // Edit File
    $(document).on('click', '.edit-file', function() {
        const fileId = $(this).data('file-id');
        
        // First, get file details
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
                    const fileDate = file.file_date ? file.file_date.split('T')[0] : '';
                    
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
                                    <input type="date" class="form-control" id="edit-file-date" value="${fileDate}">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" id="edit-file-description" rows="3">${file.description || ''}</textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tags</label>
                                    <input type="text" class="form-control" id="edit-file-tags" value="${file.tags || ''}" placeholder="Comma-separated tags">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Retention Period (years)</label>
                                    <input type="number" class="form-control" id="edit-retention-period" value="${file.retention_period || ''}" min="1">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" id="edit-file-notes" rows="2">${file.notes || ''}</textarea>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Save Changes',
                        cancelButtonText: 'Cancel',
                        width: '700px',
                        preConfirm: () => {
                            const fileName = $('#edit-file-name').val();
                            const fileNumber = $('#edit-file-number').val();
                            const fileType = $('#edit-file-type').val();
                            const confidentialLevel = $('#edit-confidential-level').val();
                            
                            if (!fileName) {
                                Swal.showValidationMessage('File name is required');
                                return false;
                            }
                            if (!fileNumber) {
                                Swal.showValidationMessage('File number is required');
                                return false;
                            }
                            if (!fileType) {
                                Swal.showValidationMessage('File type is required');
                                return false;
                            }
                            if (!confidentialLevel) {
                                Swal.showValidationMessage('Confidentiality level is required');
                                return false;
                            }
                            
                            return {
                                file_id: fileId,
                                file_name: fileName,
                                file_number: fileNumber,
                                file_type: fileType,
                                confidential_level: confidentialLevel,
                                file_date: $('#edit-file-date').val() || null,
                                description: $('#edit-file-description').val() || null,
                                tags: $('#edit-file-tags').val() || null,
                                retention_period: $('#edit-retention-period').val() || null,
                                notes: $('#edit-file-notes').val() || null
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
            title: 'Updating...',
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
                file_id: data.file_id,
                file_name: data.file_name,
                file_number: data.file_number,
                file_type: data.file_type,
                confidential_level: data.confidential_level,
                file_date: data.file_date,
                description: data.description,
                tags: data.tags,
                retention_period: data.retention_period,
                notes: data.notes,
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
    
    // Delete File
    $(document).on('click', '.delete-file', function() {
        const fileId = $(this).data('file-id');
        Swal.fire({
            title: 'Are you sure?',
            text: 'This will delete the file permanently!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Implement delete functionality
                Swal.fire('Info', 'Delete file functionality will be implemented', 'info');
            }
        });
    });
});
</script>
@endpush
@endsection

