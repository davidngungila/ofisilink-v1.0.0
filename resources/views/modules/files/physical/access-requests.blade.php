@extends('layouts.app')

@section('title', 'Access Requests')

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
                                <i class="bx bx-clipboard me-2"></i>File Access Requests
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Manage physical file access requests and approvals
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
                        @if($canManageFiles)
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pending-tab">
                                <i class="bx bx-time me-2"></i>Pending Requests
                                @if($pendingRequests->count() > 0)
                                    <span class="badge bg-danger ms-2">{{ $pendingRequests->count() }}</span>
                                @endif
                            </button>
                        </li>
                        @endif
                        <li class="nav-item">
                            <button class="nav-link {{ !$canManageFiles ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#my-requests-tab">
                                <i class="bx bx-user me-2"></i>My Requests
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        @if($canManageFiles)
                        <!-- Pending Requests Tab -->
                        <div class="tab-pane fade show active" id="pending-tab">
                            @if($pendingRequests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>File</th>
                                                <th>Rack</th>
                                                <th>Purpose</th>
                                                <th>Expected Return</th>
                                                <th>Requested</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingRequests as $request)
                                            <tr class="{{ $request->status === 'return_pending' ? 'table-warning' : '' }}">
                                                <td>{{ $request->requester->name ?? 'N/A' }}</td>
                                                <td>
                                                    <i class="bx bx-file me-1"></i>{{ $request->file->file_name ?? 'N/A' }}
                                                    @if($request->status === 'return_pending')
                                                        <span class="badge bg-label-warning ms-2">Return</span>
                                                    @endif
                                                </td>
                                                <td>{{ $request->file->folder->name ?? 'N/A' }}</td>
                                                <td>
                                                    @if($request->status === 'return_pending')
                                                        <strong>Return Request:</strong><br>
                                                        <small class="text-muted">{{ $request->manager_notes ?? 'N/A' }}</small>
                                                    @else
                                                        {{ $request->purpose ?? 'N/A' }}
                                                    @endif
                                                </td>
                                                <td>{{ $request->expected_return_date ? \Carbon\Carbon::parse($request->expected_return_date)->format('M d, Y') : 'N/A' }}</td>
                                                <td>{{ $request->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-success approve-request" data-request-id="{{ $request->id }}" data-request-type="{{ $request->status }}">
                                                            <i class="bx bx-check"></i> {{ $request->status === 'return_pending' ? 'Approve Return' : 'Approve' }}
                                                        </button>
                                                        <button class="btn btn-sm btn-danger reject-request" data-request-id="{{ $request->id }}" data-request-type="{{ $request->status }}">
                                                            <i class="bx bx-x"></i> Reject
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bx bx-check-circle fs-1 text-success mb-3"></i>
                                    <p class="text-muted">No pending requests</p>
                                </div>
                            @endif
                        </div>
                        @endif

                        <!-- My Requests Tab -->
                        <div class="tab-pane fade {{ !$canManageFiles ? 'show active' : '' }}" id="my-requests-tab">
                            @if($myRequests->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>File</th>
                                                <th>Rack</th>
                                                <th>Purpose</th>
                                                <th>Status</th>
                                                <th>Expected Return</th>
                                                <th>Requested</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myRequests as $request)
                                            <tr>
                                                <td>
                                                    <i class="bx bx-file me-1"></i>{{ $request->file->file_name ?? 'N/A' }}
                                                </td>
                                                <td>{{ $request->file->folder->name ?? 'N/A' }}</td>
                                                <td>{{ $request->purpose ?? 'N/A' }}</td>
                                                <td>
                                                    @if($request->status === 'approved')
                                                        <span class="badge bg-label-success">Approved</span>
                                                    @elseif($request->status === 'rejected')
                                                        <span class="badge bg-label-danger">Rejected</span>
                                                    @elseif($request->status === 'return_pending')
                                                        <span class="badge bg-label-warning">Return Pending</span>
                                                    @elseif($request->status === 'return_approved')
                                                        <span class="badge bg-label-success">Return Approved</span>
                                                    @elseif($request->status === 'return_rejected')
                                                        <span class="badge bg-label-danger">Return Rejected</span>
                                                    @else
                                                        <span class="badge bg-label-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td>{{ $request->expected_return_date ? \Carbon\Carbon::parse($request->expected_return_date)->format('M d, Y') : 'N/A' }}</td>
                                                <td>{{ $request->created_at->diffForHumans() }}</td>
                                                <td>
                                                    @if($request->status === 'approved' && $request->file->status === 'issued')
                                                        @php
                                                            $hasPendingReturn = \App\Models\RackFileRequest::where('file_id', $request->file_id)
                                                                ->where('requested_by', $request->requested_by)
                                                                ->where('status', 'return_pending')
                                                                ->exists();
                                                        @endphp
                                                        @if(!$hasPendingReturn)
                                                            <button class="btn btn-sm btn-primary return-file" data-file-id="{{ $request->file_id }}">
                                                                <i class="bx bx-undo"></i> Return
                                                            </button>
                                                        @else
                                                            <span class="badge bg-label-warning">Return Pending</span>
                                                        @endif
                                                    @elseif($request->status === 'return_pending')
                                                        <span class="badge bg-label-warning">Awaiting Approval</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bx bx-clipboard fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">You have no file requests</p>
                                </div>
                            @endif
                        </div>
                    </div>
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
    
    // Approve Request
    $(document).on('click', '.approve-request', function() {
        const requestId = $(this).data('request-id');
        
        Swal.fire({
            title: 'Approve Request?',
            text: 'This will approve the file access request.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'process_rack_request',
                        request_id: requestId,
                        decision: 'approve',
                        _token: csrfToken
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', response.message || 'Request approved!', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message || 'Failed to approve request', 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response?.message || 'An error occurred', 'error');
                    }
                });
            }
        });
    });
    
    // Reject Request
    $(document).on('click', '.reject-request', function() {
        const requestId = $(this).data('request-id');
        
        Swal.fire({
            title: 'Reject Request?',
            text: 'This will reject the file access request.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reject it!',
            cancelButtonText: 'Cancel',
            input: 'text',
            inputPlaceholder: 'Rejection reason (optional)',
            inputValidator: (value) => {
                return null; // Allow empty
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'process_rack_request',
                        request_id: requestId,
                        decision: 'reject',
                        notes: result.value || '',
                        _token: csrfToken
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Success', response.message || 'Request rejected!', 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error', response.message || 'Failed to reject request', 'error');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire('Error', response?.message || 'An error occurred', 'error');
                    }
                });
            }
        });
    });
    
    // Return File
    $(document).on('click', '.return-file', function() {
        const fileId = $(this).data('file-id');
        
        Swal.fire({
            title: 'Return Physical File',
            html: `
                <form id="return-file-form">
                    <div class="mb-3">
                        <label class="form-label">Return Condition <span class="text-danger">*</span></label>
                        <select class="form-select" id="return-condition" required>
                            <option value="">-- Select Condition --</option>
                            <option value="excellent">Excellent</option>
                            <option value="good" selected>Good</option>
                            <option value="fair">Fair</option>
                            <option value="poor">Poor</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Return Notes</label>
                        <textarea class="form-control" id="return-notes" rows="3" placeholder="Any additional notes about the file condition or return..."></textarea>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Return File',
            cancelButtonText: 'Cancel',
            width: '600px',
            preConfirm: () => {
                const condition = $('#return-condition').val();
                const notes = $('#return-notes').val();
                
                if (!condition) {
                    Swal.showValidationMessage('Please select the return condition');
                    return false;
                }
                
                return {
                    file_id: fileId,
                    return_condition: condition,
                    return_notes: notes || ''
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitReturnFile(result.value);
            }
        });
    });
    
    function submitReturnFile(data) {
        Swal.fire({
            title: 'Returning File...',
            text: 'Please wait while we process the return.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'return_physical_file',
                file_id: data.file_id,
                return_condition: data.return_condition,
                return_notes: data.return_notes,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'File returned successfully!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message || 'Failed to return file', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'An error occurred while returning the file.';
                
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
});
</script>
@endpush
@endsection

