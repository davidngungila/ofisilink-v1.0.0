@extends('layouts.app')

@section('title', 'Assign Physical Files')

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
                                <i class="bx bx-user-plus me-2"></i>Assign Physical Files
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Assign physical files to staff members for tracking and management
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

    <!-- Assignment Form -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-share-alt me-2"></i>New Assignment
                    </h5>
                </div>
                <div class="card-body">
                    <form id="assignForm">
                        @csrf
                        
                        <!-- File Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Select File <span class="text-danger">*</span></label>
                                <select class="form-select" name="file_id" id="file_id" required>
                                    <option value="">-- Select File --</option>
                                    @foreach($files as $file)
                                        <option value="{{ $file->id }}">
                                            {{ $file->file_name }} 
                                            @if($file->folder)
                                                ({{ $file->folder->name }})
                                            @endif
                                            @if($file->status === 'issued')
                                                - Currently Issued
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- User Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Select User <span class="text-danger">*</span></label>
                                <select class="form-select" name="user_id" id="user_id" required>
                                    <option value="">-- Select User --</option>
                                    @foreach($users as $u)
                                        <option value="{{ $u->id }}">
                                            {{ $u->name }} 
                                            @if($u->email)
                                                ({{ $u->email }})
                                            @endif
                                            @if($u->department)
                                                - {{ $u->department->name }}
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Purpose -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Purpose <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="purpose" id="purpose" rows="3" required minlength="10" maxlength="500" placeholder="Enter the purpose for assigning this file (minimum 10 characters)..."></textarea>
                                <small class="text-muted">Minimum 10 characters, maximum 500 characters</small>
                            </div>
                        </div>

                        <!-- Expected Return Date -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="expected_return_date" id="expected_return_date" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Urgency <span class="text-danger">*</span></label>
                                <select class="form-select" name="urgency" id="urgency" required>
                                    <option value="low">Low</option>
                                    <option value="normal" selected>Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('modules.files.physical.dashboard') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-send me-2"></i>Assign File
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
    
    $('#assignForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'assign_physical_file',
            file_id: $('#file_id').val(),
            user_id: $('#user_id').val(),
            purpose: $('#purpose').val(),
            expected_return_date: $('#expected_return_date').val(),
            urgency: $('#urgency').val(),
            _token: csrfToken
        };
        
        Swal.fire({
            title: 'Assigning File...',
            text: 'Please wait while we process your request.',
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
                        text: response.message || 'File assigned successfully!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '{{ route("modules.files.physical.dashboard") }}';
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to assign file', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while assigning the file.';
                
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

