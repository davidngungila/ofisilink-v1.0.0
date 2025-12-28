@extends('layouts.app')

@section('title', 'Add Physical Files')

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
                                <i class="bx bx-file-plus me-2"></i>Add Physical Files
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Add new physical files to rack folders with complete tracking information
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

    <!-- Add File Form -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-file me-2"></i>File Information
                    </h5>
                </div>
                <div class="card-body">
                    <form id="add-file-form">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Select Rack Folder <span class="text-danger">*</span></label>
                                <select class="form-select" id="folder_id" name="folder_id" required>
                                    <option value="">-- Select Rack Folder --</option>
                                    @foreach($rackFolders as $rack)
                                        <option value="{{ $rack->id }}">{{ $rack->name }} ({{ $rack->category->name ?? 'N/A' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">-- Select Category --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">File Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="file_name" name="file_name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">File Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="file_number" name="file_number" required placeholder="e.g., FILE-001">
                                <small class="text-muted">Must be unique</small>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">File Type <span class="text-danger">*</span></label>
                                <select class="form-select" id="file_type" name="file_type" required>
                                    <option value="">-- Select File Type --</option>
                                    <option value="general">General</option>
                                    <option value="contract">Contract</option>
                                    <option value="financial">Financial</option>
                                    <option value="legal">Legal</option>
                                    <option value="hr">HR</option>
                                    <option value="technical">Technical</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">File Date</label>
                                <input type="date" class="form-control" id="file_date" name="file_date">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Confidentiality Level <span class="text-danger">*</span></label>
                                <select class="form-select" id="confidential_level" name="confidential_level" required>
                                    <option value="normal" selected>Normal</option>
                                    <option value="confidential">Confidential</option>
                                    <option value="strictly_confidential">Strictly Confidential</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Retention Period (Years)</label>
                                <input type="number" class="form-control" id="retention_period" name="retention_period" min="1" placeholder="e.g., 5">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter file description..."></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Tags (comma-separated)</label>
                            <input type="text" class="form-control" id="tags" name="tags" placeholder="tag1, tag2, tag3">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes..."></textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('modules.files.physical.dashboard') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-2"></i>Add File
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

    $('#add-file-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'create_rack_file',
            folder_id: $('#folder_id').val(),
            file_name: $('#file_name').val(),
            file_number: $('#file_number').val(),
            file_type: $('#file_type').val(),
            file_date: $('#file_date').val(),
            confidential_level: $('#confidential_level').val(),
            retention_period: $('#retention_period').val(),
            description: $('#description').val(),
            tags: $('#tags').val(),
            notes: $('#notes').val(),
            _token: csrfToken
        };

        Swal.fire({
            title: 'Adding File...',
            text: 'Please wait while we add the file.',
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
                        text: response.message || 'File added successfully!',
                        confirmButtonText: 'Add Another',
                        showCancelButton: true,
                        cancelButtonText: 'Go to Dashboard'
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel) {
                            window.location.href = '{{ route("modules.files.physical.dashboard") }}';
                        } else {
                            $('#add-file-form')[0].reset();
                        }
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to add file', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while adding the file.';
                
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

