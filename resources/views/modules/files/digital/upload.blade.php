@extends('layouts.app')

@section('title', 'Upload Files')

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
                                <i class="bx bx-upload me-2"></i>Upload Files
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Upload and organize your digital files with advanced security and access control
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

    <!-- Upload Form -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-cloud-upload me-2"></i>File Upload
                    </h5>
                </div>
                <div class="card-body">
                    <form id="upload-form" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Folder Selection -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Select Folder <span class="text-danger">*</span></label>
                                <select class="form-select" id="folder_id" name="folder_id" required>
                                    <option value="">-- Select Folder --</option>
                                    @foreach($folders as $folder)
                                        <option value="{{ $folder->id }}">{{ $folder->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Access Level</label>
                                <select class="form-select" id="access_level" name="access_level">
                                    <option value="public">Public</option>
                                    <option value="department">Department</option>
                                    <option value="private">Private</option>
                                </select>
                            </div>
                        </div>

                        <!-- File Upload Area -->
                        <div class="file-upload-area p-5 text-center mb-4" id="dropzone">
                            <i class="bx bx-cloud-upload fs-1 text-primary mb-3"></i>
                            <h5>Drag & Drop Files Here</h5>
                            <p class="text-muted">or click to browse</p>
                            <input type="file" id="file-input" name="files[]" multiple class="d-none">
                            <button type="button" class="btn btn-primary" onclick="document.getElementById('file-input').click()">
                                <i class="bx bx-folder-open me-2"></i>Select Files
                            </button>
                        </div>

                        <!-- Selected Files Preview -->
                        <div id="files-preview" class="mb-4" style="display: none;">
                            <h6 class="mb-3">Selected Files:</h6>
                            <div id="files-list" class="row g-3"></div>
                        </div>

                        <!-- Additional Options -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Confidentiality Level</label>
                                <select class="form-select" id="confidentiality_level" name="confidentiality_level">
                                    <option value="normal">Normal</option>
                                    <option value="confidential">Confidential</option>
                                    <option value="restricted">Restricted</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Department (if Department access)</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">-- Select Department --</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter file description..."></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('modules.files.digital.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="upload-btn">
                                <i class="bx bx-upload me-2"></i>Upload Files
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
    border-color: #007bff;
    background-color: #f8f9ff;
}
.file-preview-item {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    transition: all 0.3s;
}
.file-preview-item:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}
</style>
@endpush

@push('scripts')
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
    $('#upload-form').on('submit', function(e) {
        e.preventDefault();
        
        if (selectedFiles.length === 0) {
            Swal.fire('Error', 'Please select at least one file to upload.', 'error');
            return;
        }

        const formData = new FormData();
        // Use bulk upload for multiple files, single upload for one file
        formData.append('action', selectedFiles.length > 1 ? 'bulk_upload_files' : 'upload_file');
        formData.append('folder_id', $('#folder_id').val());
        formData.append('access_level', $('#access_level').val());
        formData.append('confidential_level', $('#confidentiality_level').val());
        formData.append('department_id', $('#department_id').val());
        formData.append('description', $('#description').val());
        formData.append('_token', '{{ csrf_token() }}');

        // For single file, use 'file', for multiple use 'files[]'
        if (selectedFiles.length === 1) {
            formData.append('file', selectedFiles[0]);
        } else {
            selectedFiles.forEach((file) => {
                formData.append('files[]', file);
            });
        }

        $('#upload-btn').prop('disabled', true).html('<i class="bx bx-loader bx-spin me-2"></i>Uploading...');

        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'Files uploaded successfully!', 'success')
                        .then(() => {
                            window.location.href = '{{ route("modules.files.digital.dashboard") }}';
                        });
                } else {
                    Swal.fire('Error', response.message || 'Failed to upload files.', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Swal.fire('Error', response?.message || 'An error occurred while uploading files.', 'error');
            },
            complete: function() {
                $('#upload-btn').prop('disabled', false).html('<i class="bx bx-upload me-2"></i>Upload Files');
            }
        });
    });
});
</script>
@endpush
@endsection

