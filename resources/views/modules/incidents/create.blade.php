@extends('layouts.app')

@section('title', 'Create Incident')

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
                                <i class="bx bx-plus me-2"></i>Create Incident
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Report a new incident with detailed information and attachments
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.incidents.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Form -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-error-circle me-2"></i>Incident Information
                    </h5>
                </div>
                <div class="card-body">
                    <form id="create-incident-form" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Basic Information -->
                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title" required placeholder="Enter incident title...">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="description" name="description" rows="6" required placeholder="Provide detailed description of the incident..."></textarea>
                            </div>
                        </div>

                        <!-- Priority and Category -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Priority <span class="text-danger">*</span></label>
                                <select class="form-select" id="priority" name="priority" required>
                                    <option value="">-- Select Priority --</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium" selected>Medium</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">-- Select Category --</option>
                                    <option value="technical">Technical</option>
                                    <option value="hr">HR</option>
                                    <option value="facilities">Facilities</option>
                                    <option value="security">Security</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Reporter Information -->
                        <h6 class="mb-3 fw-bold">
                            <i class="bx bx-user me-2"></i>Reporter Information
                        </h6>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Reporter Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="reporter_name" name="reporter_name" required placeholder="Enter reporter name...">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reporter Email</label>
                                <input type="email" class="form-control" id="reporter_email" name="reporter_email" placeholder="reporter@example.com">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Reporter Phone</label>
                                <input type="tel" class="form-control" id="reporter_phone" name="reporter_phone" placeholder="2556XXXXXXXX">
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Assignment -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Assign To (Optional)</label>
                                <select class="form-select" id="assigned_to" name="assigned_to">
                                    <option value="">-- Leave Unassigned --</option>
                                    @foreach($staff as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Leave unassigned to review later</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Due Date (Optional)</label>
                                <input type="date" class="form-control" id="due_date" name="due_date">
                            </div>
                        </div>

                        <!-- Attachments -->
                        <div class="mb-4">
                            <label class="form-label">Attachments</label>
                            <div class="file-upload-area p-5 text-center" id="dropzone">
                                <i class="bx bx-cloud-upload fs-1 text-primary mb-3"></i>
                                <h5>Drag & Drop Files Here</h5>
                                <p class="text-muted">or click to browse</p>
                                <input type="file" id="file-input" name="attachments[]" multiple class="d-none">
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('file-input').click()">
                                    <i class="bx bx-folder-open me-2"></i>Select Files
                                </button>
                                <small class="d-block text-muted mt-2">Multiple files allowed. Max 10MB per file.</small>
                            </div>
                            
                            <!-- Selected Files Preview -->
                            <div id="files-preview" class="mt-3" style="display: none;">
                                <h6 class="mb-3">Selected Files:</h6>
                                <div id="files-list" class="row g-3"></div>
                            </div>
                        </div>

                        <!-- Internal Notes -->
                        <div class="mb-4">
                            <label class="form-label">Internal Notes</label>
                            <textarea class="form-control" id="internal_notes" name="internal_notes" rows="3" placeholder="Internal notes (not visible to reporter)..."></textarea>
                            <small class="text-muted">These notes are only visible to HR and administrators</small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('modules.incidents.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="bx bx-check me-2"></i>Create Incident
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.file-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    transition: all 0.3s;
    cursor: pointer;
}
.file-upload-area:hover,
.file-upload-area.dragover {
    border-color: #0d6efd;
    background-color: #f8f9ff;
}
.file-preview-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s;
}
.file-preview-item:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const dropzone = $('#dropzone');
    const fileInput = $('#file-input');
    const filesPreview = $('#files-preview');
    const filesList = $('#files-list');
    let selectedFiles = [];

    // Drag and drop handlers
    dropzone.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    dropzone.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    dropzone.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        const files = e.originalEvent.dataTransfer.files;
        handleFiles(files);
    });

    fileInput.on('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        selectedFiles = Array.from(files);
        displayFiles();
    }

    function displayFiles() {
        filesList.empty();
        if (selectedFiles.length > 0) {
            filesPreview.show();
            selectedFiles.forEach((file, index) => {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                const fileItem = `
                    <div class="col-md-4">
                        <div class="file-preview-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><i class="bx bx-file me-2"></i>${file.name}</h6>
                                    <small class="text-muted">${fileSize} MB</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-file" data-index="${index}">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                filesList.append(fileItem);
            });
        } else {
            filesPreview.hide();
        }
    }

    $(document).on('click', '.remove-file', function() {
        const index = $(this).data('index');
        selectedFiles.splice(index, 1);
        displayFiles();
    });

    // Form submission
    $('#create-incident-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Add selected files
        selectedFiles.forEach((file) => {
            formData.append('attachments[]', file);
        });

        $('#submit-btn').prop('disabled', true).html('<i class="bx bx-loader bx-spin me-2"></i>Creating...');

        $.ajax({
            url: '{{ route("modules.incidents.store") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'Incident created successfully!', 'success')
                        .then(() => {
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.href = '{{ route("modules.incidents.dashboard") }}';
                            }
                        });
                } else {
                    Swal.fire('Error', response.message || 'Failed to create incident.', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while creating the incident.';
                if (response && response.message) {
                    errorMessage = response.message;
                } else if (response && response.errors) {
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join('<br>');
                }
                Swal.fire('Error', errorMessage, 'error');
            },
            complete: function() {
                $('#submit-btn').prop('disabled', false).html('<i class="bx bx-check me-2"></i>Create Incident');
            }
        });
    });
});
</script>
@endpush
@endsection
