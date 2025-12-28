@extends('layouts.app')

@section('title', 'Assign Files & Folders')

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
                                <i class="bx bx-user-plus me-2"></i>Assign Files & Folders
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Assign files and folders to staff members with customizable access permissions and expiry dates
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
                        <input type="hidden" name="action" value="assign_file_folder">
                        
                        <!-- Type Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Assignment Type <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="type" id="type_folder" value="folder" checked>
                                    <label class="btn btn-outline-primary" for="type_folder">
                                        <i class="bx bx-folder me-2"></i>Folder
                                    </label>
                                    
                                    <input type="radio" class="btn-check" name="type" id="type_file" value="file">
                                    <label class="btn btn-outline-primary" for="type_file">
                                        <i class="bx bx-file me-2"></i>File
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Folder/File Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label" id="item-label">Select Folder <span class="text-danger">*</span></label>
                                <select class="form-select" name="item_id" id="item_id" required>
                                    <option value="">-- Select Folder --</option>
                                    @foreach($folders as $folder)
                                        <option value="{{ $folder->id }}">{{ $folder->name }} 
                                            @if($folder->department)
                                                ({{ $folder->department->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Users Selection -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Select Users <span class="text-danger">*</span></label>
                                <select class="form-select" name="user_ids[]" id="user_ids" multiple required style="height: 200px;">
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
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple users. You can also search by typing.</small>
                            </div>
                        </div>

                        <!-- Permission Level -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Permission Level</label>
                                <select class="form-select" name="permission_level">
                                    <option value="view">View Only</option>
                                    <option value="download">View & Download</option>
                                    <option value="edit">View, Download & Edit</option>
                                </select>
                            </div>
                            <div class="col-md-6">
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
                                    <option value="custom">Custom Date</option>
                                </select>
                            </div>
                        </div>

                        <!-- Custom Expiry Date -->
                        <div class="row mb-4" id="custom_expiry" style="display: none;">
                            <div class="col-md-12">
                                <label class="form-label">Custom Expiry Date</label>
                                <input type="date" class="form-control" name="expiry_date" min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('modules.files.digital.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="assign-btn">
                                <i class="bx bx-check me-2"></i>Assign
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mt-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-folder fs-1 text-primary mb-2"></i>
                    <h4 class="mb-0">{{ $folders->count() }}</h4>
                    <small class="text-muted">Total Folders</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-file fs-1 text-success mb-2"></i>
                    <h4 class="mb-0">{{ $files->count() }}</h4>
                    <small class="text-muted">Total Files</small>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-user fs-1 text-info mb-2"></i>
                    <h4 class="mb-0">{{ $users->count() }}</h4>
                    <small class="text-muted">Active Users</small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle between folder and file selection
    $('input[name="type"]').on('change', function() {
        const type = $(this).val();
        const itemSelect = $('#item_id');
        const itemLabel = $('#item-label');
        
        if (type === 'file') {
            itemLabel.text('Select File *');
            itemSelect.empty().append('<option value="">-- Select File --</option>');
            @foreach($files as $file)
                itemSelect.append('<option value="{{ $file->id }}">{{ $file->original_name }} @if($file->folder)({{ $file->folder->name }})@endif</option>');
            @endforeach
        } else {
            itemLabel.text('Select Folder *');
            itemSelect.empty().append('<option value="">-- Select Folder --</option>');
            @foreach($folders as $folder)
                itemSelect.append('<option value="{{ $folder->id }}">{{ $folder->name }} @if($folder->department)({{ $folder->department->name }})@endif</option>');
            @endforeach
        }
    });

    // Show/hide custom expiry date
    $('#expiry_duration').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#custom_expiry').show();
        } else {
            $('#custom_expiry').hide();
        }
    });

    // Form submission
    $('#assignForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $('#assign-btn').prop('disabled', true).html('<i class="bx bx-loader bx-spin me-2"></i>Assigning...');
        
        $.ajax({
            url: '{{ route("modules.files.digital.ajax") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Assignment created successfully!',
                        showConfirmButton: true
                    }).then(() => {
                        $('#assignForm')[0].reset();
                        $('#user_ids').val(null).trigger('change');
                        $('#custom_expiry').hide();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to create assignment.', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while creating assignment.';
                
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
            },
            complete: function() {
                $('#assign-btn').prop('disabled', false).html('<i class="bx bx-check me-2"></i>Assign');
            }
        });
    });
});
</script>
@endpush
@endsection


