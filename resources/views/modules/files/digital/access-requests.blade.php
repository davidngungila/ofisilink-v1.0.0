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
                                <i class="bx bx-user-check me-2"></i>Access Requests
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Manage file access requests and permissions
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
                                                <th>File/Folder</th>
                                                <th>Reason</th>
                                                <th>Requested</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pendingRequests as $request)
                                            <tr>
                                                <td>{{ $request->user->name }}</td>
                                                <td>
                                                    @if($request->file)
                                                        <i class="bx bx-file me-1"></i>{{ $request->file->original_name }}
                                                    @elseif($request->folder)
                                                        <i class="bx bx-folder me-1"></i>{{ $request->folder->name }}
                                                    @endif
                                                </td>
                                                <td>{{ $request->reason ?? 'N/A' }}</td>
                                                <td>{{ $request->created_at->diffForHumans() }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button class="btn btn-sm btn-success approve-request" data-request-id="{{ $request->id }}">
                                                            <i class="bx bx-check"></i> Approve
                                                        </button>
                                                        <button class="btn btn-sm btn-danger reject-request" data-request-id="{{ $request->id }}">
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
                                    <p class="text-muted">No pending access requests</p>
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
                                                <th>File/Folder</th>
                                                <th>Status</th>
                                                <th>Reason</th>
                                                <th>Requested</th>
                                                <th>Response</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($myRequests as $request)
                                            <tr>
                                                <td>
                                                    @if($request->file)
                                                        <i class="bx bx-file me-1"></i>{{ $request->file->original_name }}
                                                    @elseif($request->folder)
                                                        <i class="bx bx-folder me-1"></i>{{ $request->folder->name }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-label-{{ $request->status === 'approved' ? 'success' : ($request->status === 'rejected' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($request->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $request->reason ?? 'N/A' }}</td>
                                                <td>{{ $request->created_at->diffForHumans() }}</td>
                                                <td>{{ $request->response ?? 'N/A' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="bx bx-file fs-1 text-muted mb-3"></i>
                                    <p class="text-muted">You haven't made any access requests yet</p>
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
    const csrfToken = '{{ csrf_token() }}';
    const ajaxUrl = '{{ route("modules.files.digital.ajax") }}';
    
    // Approve Request
    $(document).on('click', '.approve-request', function() {
        const requestId = $(this).data('request-id');
        Swal.fire({
            title: 'Approve Request?',
            text: 'This will grant access to the requested file/folder.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Approve',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                approveRequest(requestId);
            }
        });
    });
    
    // Reject Request
    $(document).on('click', '.reject-request', function() {
        const requestId = $(this).data('request-id');
        Swal.fire({
            title: 'Reject Request?',
            text: 'Please provide a reason for rejection:',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason...',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please provide a reason for rejection';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                rejectRequest(requestId, result.value);
            }
        });
    });
    
    function approveRequest(requestId) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'approve_request',
                request_id: requestId,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'Request approved successfully!', 'success')
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
    
    function rejectRequest(requestId, reason) {
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'reject_request',
                request_id: requestId,
                response: reason,
                _token: csrfToken
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message || 'Request rejected successfully!', 'success')
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
</script>
@endpush
@endsection

