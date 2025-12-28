@extends('layouts.app')

@section('title', 'Leave Types Management - OfisiLink')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-calendar me-2"></i>Leave Types Management
            </h1>
            <p class="text-muted mb-0">Define and manage all leave types with their allocated days</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" id="new-leave-type-btn">
                <i class="bx bx-plus-circle me-1"></i>Add New Leave Type
            </button>
        </div>
    </div>

    <!-- Leave Types Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Leave Types</h6>
        </div>
        <div class="card-body">
            @if($leaveTypes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Name</th>
                                <th width="25%">Description</th>
                                <th class="text-center" width="10%">Max Days/Year</th>
                                <th class="text-center" width="10%">Requires Approval</th>
                                <th class="text-center" width="10%">Paid Leave</th>
                                <th class="text-center" width="10%">Status</th>
                                <th class="text-center" width="10%">Requests</th>
                                <th class="text-center" width="15%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveTypes as $index => $leaveType)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <strong><i class="bx bx-calendar-check text-primary me-1"></i>{{ $leaveType->name }}</strong>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" title="{{ $leaveType->description ?? 'No description' }}">
                                        {{ $leaveType->description ?? '—' }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info fs-6">
                                        <i class="bx bx-time-five me-1"></i>{{ $leaveType->max_days_per_year ?? $leaveType->max_days ?? 0 }} days
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($leaveType->requires_approval ?? true)
                                        <span class="badge bg-warning"><i class="bx bx-check-circle"></i> Yes</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bx bx-x-circle"></i> No</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($leaveType->is_paid ?? true)
                                        <span class="badge bg-success"><i class="bx bx-dollar-circle"></i> Paid</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bx bx-x-circle"></i> Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($leaveType->is_active)
                                        <span class="badge bg-success"><i class="bx bx-check-circle"></i> Active</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bx bx-x-circle"></i> Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">
                                        <i class="bx bx-file me-1"></i>{{ $leaveType->leave_requests_count ?? $leaveType->leaveRequests()->count() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-info" onclick="viewLeaveType({{ $leaveType->id }})" title="View Full Details">
                                            <i class="bx bx-show"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning" onclick="editLeaveType({{ $leaveType->id }})" title="Edit Leave Type">
                                            <i class="bx bx-edit"></i>
                                        </button>
                                        @if($leaveType->leaveRequests()->count() == 0)
                                            <button type="button" class="btn btn-danger" onclick="deleteLeaveType({{ $leaveType->id }})" title="Delete Leave Type">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-secondary" disabled title="Cannot delete - has {{ $leaveType->leaveRequests()->count() }} request(s)">
                                                <i class="bx bx-lock"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bx bx-calendar text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-muted">No Leave Types Found</h5>
                    <p class="text-muted">Click "Add New Leave Type" to create your first leave type.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- View Leave Type Details Modal -->
<div class="modal fade" id="viewLeaveTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-info-circle me-2"></i>Leave Type Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewLeaveTypeContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-muted">Loading leave type details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="editFromViewBtn" style="display: none;">
                    <i class="bx bx-edit me-1"></i>Edit Leave Type
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Leave Type Modal -->
<div class="modal fade" id="leaveTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="leaveTypeModalTitle">Add New Leave Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="leaveTypeForm">
                @csrf
                <input type="hidden" id="leaveTypeId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">Leave Type Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., Annual Leave, Sick Leave, Maternity Leave">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of this leave type..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="max_days_per_year" class="form-label">Maximum Days Per Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="max_days_per_year" name="max_days_per_year" required min="0" max="365" placeholder="e.g., 28">
                            <small class="text-muted">Total number of days employees can take per year for this leave type</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label d-block">Settings</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="requires_approval" name="requires_approval" checked>
                                <label class="form-check-label" for="requires_approval">
                                    Requires Approval
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_paid" name="is_paid" checked>
                                <label class="form-check-label" for="is_paid">
                                    Paid Leave
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .leave-type-detail-card {
        border-left: 4px solid #4e73df;
        margin-bottom: 1rem;
    }
    .detail-item {
        padding: 0.75rem;
        border-bottom: 1px solid #e3e6f0;
    }
    .detail-item:last-child {
        border-bottom: none;
    }
    .detail-label {
        font-weight: 600;
        color: #5a5c69;
        margin-bottom: 0.25rem;
    }
    .detail-value {
        color: #858796;
    }
    .stat-badge {
        font-size: 1.1rem;
        padding: 0.5rem 1rem;
    }
</style>
@endpush

@push('scripts')
<script>
let currentLeaveTypeId = null;

function showToast(message, type){
    const cls = type==='success' ? 'alert-success' : (type==='error'?'alert-danger':'alert-info');
    const el = document.createElement('div');
    el.className = 'alert '+cls+' position-fixed top-0 end-0 m-3';
    el.style.zIndex = 9999;
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(()=>{ el.remove(); }, 3000);
}

function viewLeaveType(id) {
    currentLeaveTypeId = id;
    
    // Show modal with loading state
    $('#viewLeaveTypeModal').modal('show');
    $('#viewLeaveTypeContent').html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading leave type details...</p>
        </div>
    `);
    $('#editFromViewBtn').hide();
    
    // Fetch leave type data
    fetch(`{{ route('leave.hr.leave-types.show', ':id') }}`.replace(':id', id), {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const lt = data.leaveType;
                const requestCount = lt.leave_requests_count || 0;
                
                // Format the details HTML
                let detailsHtml = `
                    <div class="card leave-type-detail-card shadow-sm">
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-12 text-center">
                                    <h4 class="mb-2">
                                        <i class="bx bx-calendar-check text-primary me-2"></i>${lt.name}
                                    </h4>
                                    <p class="text-muted mb-0">Leave Type Information</p>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="bx bx-info-circle text-primary me-1"></i>Description
                                        </div>
                                        <div class="detail-value">
                                            ${lt.description ? lt.description : '<em class="text-muted">No description provided</em>'}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="bx bx-time-five text-info me-1"></i>Maximum Days Per Year
                                        </div>
                                        <div class="detail-value">
                                            <span class="badge bg-info stat-badge">${lt.max_days_per_year || lt.max_days || 0} days</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="bx bx-check-circle text-warning me-1"></i>Requires Approval
                                        </div>
                                        <div class="detail-value">
                                            ${lt.requires_approval !== false ? 
                                                '<span class="badge bg-warning"><i class="bx bx-check-circle"></i> Yes - Approval Required</span>' : 
                                                '<span class="badge bg-secondary"><i class="bx bx-x-circle"></i> No - Auto Approved</span>'}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="bx bx-dollar-circle text-success me-1"></i>Payment Status
                                        </div>
                                        <div class="detail-value">
                                            ${lt.is_paid !== false ? 
                                                '<span class="badge bg-success"><i class="bx bx-dollar-circle"></i> Paid Leave</span>' : 
                                                '<span class="badge bg-danger"><i class="bx bx-x-circle"></i> Unpaid Leave</span>'}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="detail-item">
                                        <div class="detail-label">
                                            <i class="bx bx-toggle-right text-primary me-1"></i>Status
                                        </div>
                                        <div class="detail-value">
                                            ${lt.is_active ? 
                                                '<span class="badge bg-success"><i class="bx bx-check-circle"></i> Active</span>' : 
                                                '<span class="badge bg-secondary"><i class="bx bx-x-circle"></i> Inactive</span>'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title mb-3">
                                                <i class="bx bx-bar-chart-alt-2 text-primary me-2"></i>Statistics
                                            </h6>
                                            <div class="row text-center">
                                                <div class="col-md-6">
                                                    <div class="mb-2">
                                                        <div class="detail-label">Total Leave Requests</div>
                                                        <div>
                                                            <span class="badge bg-primary stat-badge">
                                                                <i class="bx bx-file me-1"></i>${requestCount} request${requestCount !== 1 ? 's' : ''}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-2">
                                                        <div class="detail-label">Created</div>
                                                        <div>
                                                            <span class="text-muted">
                                                                <i class="bx bx-calendar me-1"></i>${lt.created_at ? new Date(lt.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A'}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            ${lt.updated_at && lt.updated_at !== lt.created_at ? `
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <small class="text-muted">
                                        <i class="bx bx-time me-1"></i>Last updated: ${new Date(lt.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })}
                                    </small>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                $('#viewLeaveTypeContent').html(detailsHtml);
                $('#editFromViewBtn').show();
            } else {
                $('#viewLeaveTypeContent').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>Error:</strong> Failed to load leave type: ${data.message || 'Unknown error'}
                    </div>
                `);
            }
        })
        .catch(error => {
            $('#viewLeaveTypeContent').html(`
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${error.message}
                </div>
            `);
        });
}

// Edit from view modal
$('#editFromViewBtn').on('click', function() {
    $('#viewLeaveTypeModal').modal('hide');
    if (currentLeaveTypeId) {
        editLeaveType(currentLeaveTypeId);
    }
});

$('#new-leave-type-btn').on('click', function() {
    currentLeaveTypeId = null;
    $('#leaveTypeModalTitle').text('Add New Leave Type');
    $('#submitBtn').text('Save Leave Type');
    $('#leaveTypeForm')[0].reset();
    $('#leaveTypeId').val('');
    $('#leaveTypeModal').modal('show');
});

function editLeaveType(id) {
    currentLeaveTypeId = id;
    
    // Fetch leave type data
    fetch(`{{ route('leave.hr.leave-types.show', ':id') }}`.replace(':id', id), {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const lt = data.leaveType;
                $('#leaveTypeModalTitle').text('Edit Leave Type');
                $('#submitBtn').text('Update Leave Type');
                $('#leaveTypeId').val(lt.id);
                $('#name').val(lt.name);
                $('#description').val(lt.description || '');
                $('#max_days_per_year').val(lt.max_days_per_year || lt.max_days || 0);
                $('#requires_approval').prop('checked', lt.requires_approval !== false);
                $('#is_paid').prop('checked', lt.is_paid !== false);
                $('#is_active').prop('checked', lt.is_active !== false);
                $('#leaveTypeModal').modal('show');
            } else {
                showToast('Failed to load leave type: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
        showToast('Error loading leave type: ' + error.message, 'error');
        });
}

function deleteLeaveType(id) {
    if (!confirm('Are you sure you want to delete this leave type? This action cannot be undone.')) {
        return;
    }

    fetch(`{{ route('leave.hr.leave-types.destroy', ':id') }}`.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Leave type deleted successfully!', 'success');
            location.reload();
        } else {
            showToast('Failed to delete: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        showToast('Error deleting leave type: ' + error.message, 'error');
    });
}

$('#leaveTypeForm').on('submit', function(e) {
    e.preventDefault();
    
    // Get form values
    const id = $('#leaveTypeId').val();
    const name = $('#name').val().trim();
    const description = $('#description').val().trim();
    const maxDaysPerYear = $('#max_days_per_year').val();
    
    // Validate required fields
    if (!name) {
        showToast('Leave type name is required.', 'error');
        $('#name').focus();
        return;
    }
    
    if (!maxDaysPerYear || maxDaysPerYear === '') {
        showToast('Maximum days per year is required.', 'error');
        $('#max_days_per_year').focus();
        return;
    }
    
    // Build form data manually to ensure all fields are included
    const formData = new FormData();
    formData.append('name', name);
    formData.append('description', description);
    formData.append('max_days_per_year', maxDaysPerYear);
    formData.append('requires_approval', $('#requires_approval').is(':checked') ? 1 : 0);
    formData.append('is_paid', $('#is_paid').is(':checked') ? 1 : 0);
    formData.append('is_active', $('#is_active').is(':checked') ? 1 : 0);
    
    const url = id ? `{{ route('leave.hr.leave-types.update', ':id') }}`.replace(':id', id) : '{{ route('leave.hr.leave-types.store') }}';
    const method = id ? 'PUT' : 'POST';
    
    // Add _method for PUT requests
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }

    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json();
        
        if (!response.ok) {
            // Handle validation errors
            if (response.status === 422) {
                const errors = data.errors || {};
                let errorMsg = data.message || 'Validation failed';
                
                if (Object.keys(errors).length > 0) {
                    // Build detailed error message
                    const errorMessages = [];
                    Object.keys(errors).forEach(field => {
                        const fieldErrors = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
                        fieldErrors.forEach(err => {
                            errorMessages.push(err);
                        });
                    });
                    errorMsg = errorMessages.join('\n');
                }
                
                showToast('Validation Error:\n' + errorMsg, 'error');
                return;
            }
            
            // Handle other errors
            throw new Error(data.message || 'Request failed');
        }
        
        if (data.success) {
            showToast(id ? 'Leave type updated successfully!' : 'Leave type created successfully!', 'success');
            $('#leaveTypeModal').modal('hide');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            const errors = data.errors || {};
            let errorMsg = data.message || 'Operation failed';
            if (Object.keys(errors).length > 0) {
                const errorMessages = Object.values(errors).flat();
                errorMsg = errorMessages.join('\n');
            }
            showToast('Error: ' + errorMsg, 'error');
        }
    })
    .catch(error => {
        let errorMsg = error.message || 'An error occurred';
        showToast('Error: ' + errorMsg, 'error');
        console.error('Leave type form error:', error);
    });
});
</script>
@endpush

