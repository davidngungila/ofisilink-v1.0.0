@extends('layouts.app')

@section('title', 'Positions Management - OfisiLink')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-briefcase me-2"></i>Positions / Job Titles Management
            </h1>
            <p class="text-muted mb-0">Manage job positions and titles for all staff</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" id="new-position-btn">
                <i class="bx bx-plus-circle me-1"></i>Add New Position
            </button>
        </div>
    </div>

    <!-- Positions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Positions</h6>
        </div>
        <div class="card-body">
            @if($positions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Department</th>
                                <th>Employment Type</th>
                                <th>Salary Range</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($positions as $position)
                            <tr>
                                <td>
                                    <strong><code>{{ $position->code ?? '—' }}</code></strong>
                                </td>
                                <td>
                                    <strong>{{ $position->title }}</strong>
                                    @if($position->description)
                                        <br><small class="text-muted">{{ Str::limit($position->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($position->department)
                                        <span class="badge bg-info">
                                            <i class="bx bx-building"></i> {{ $position->department->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($position->employment_type) }}</span>
                                </td>
                                <td>
                                    @if($position->min_salary || $position->max_salary)
                                        <strong>{{ $position->min_salary ? number_format($position->min_salary, 0) . ' TZS' : '—' }}</strong>
                                        @if($position->min_salary && $position->max_salary) - @endif
                                        <strong>{{ $position->max_salary ? number_format($position->max_salary, 0) . ' TZS' : '—' }}</strong>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($position->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-primary" onclick="viewPosition({{ $position->id }})" title="View Details">
                                            <i class="bx bx-show"></i> View
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info" onclick="editPosition({{ $position->id }})">
                                            <i class="bx bx-edit"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deletePosition({{ $position->id }})">
                                            <i class="bx bx-trash"></i> Delete
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
                    <div class="mb-3">
                        <i class="bx bx-briefcase text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-muted">No Positions Found</h5>
                    <p class="text-muted">Click "Add New Position" to create your first position.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add/Edit Position Modal -->
<div class="modal fade" id="positionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="positionModalTitle">Add New Position</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="positionForm">
                @csrf
                <input type="hidden" id="positionId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="title" class="form-label">Position Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required placeholder="e.g., Software Developer, Accountant, HR Manager">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="code" class="form-label">Position Code</label>
                            <input type="text" class="form-control" id="code" name="code" placeholder="e.g., DEV, ACC, HRM">
                            <small class="text-muted">Unique code (optional)</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="department_id" class="form-label">Department</label>
                            <select class="form-control" id="department_id" name="department_id">
                                <option value="">— Select Department —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optional: Associate with a department</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="employment_type" class="form-label">Employment Type <span class="text-danger">*</span></label>
                            <select class="form-control" id="employment_type" name="employment_type" required>
                                <option value="permanent">Permanent</option>
                                <option value="contract">Contract</option>
                                <option value="intern">Intern</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="min_salary" class="form-label">Minimum Salary (TZS)</label>
                            <input type="number" class="form-control" id="min_salary" name="min_salary" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="max_salary" class="form-label">Maximum Salary (TZS)</label>
                            <input type="number" class="form-control" id="max_salary" name="max_salary" min="0" step="0.01" placeholder="0.00">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Brief description of this position..."></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="requirements" class="form-label">Requirements</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="3" placeholder="Required qualifications, skills, experience..."></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="responsibilities" class="form-label">Responsibilities</label>
                            <textarea class="form-control" id="responsibilities" name="responsibilities" rows="3" placeholder="Key responsibilities and duties..."></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
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
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Position</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Position Details Modal -->
<div class="modal fade" id="viewPositionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Position Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="positionDetails">
                <!-- Details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPositionId = null;

function showToast(message, type){
    const cls = type==='success' ? 'alert-success' : (type==='error'?'alert-danger':'alert-info');
    const el = document.createElement('div');
    el.className = 'alert '+cls+' position-fixed top-0 end-0 m-3';
    el.style.zIndex = 9999;
    el.style.minWidth = '300px';
    el.innerHTML = '<strong>' + message + '</strong><button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    document.body.appendChild(el);
    setTimeout(()=>{ el.remove(); }, 4000);
}

$('#new-position-btn').on('click', function() {
    currentPositionId = null;
    $('#positionModalTitle').text('Add New Position');
    $('#submitBtn').text('Save Position');
    $('#positionForm')[0].reset();
    $('#positionId').val('');
    $('#is_active').prop('checked', true);
    $('#employment_type').val('permanent');
    $('#department_id').val('');
    $('#positionModal').modal('show');
});

function viewPosition(id) {
    fetch(`{{ route('positions.show', ':id') }}`.replace(':id', id), {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pos = data.position;
                let html = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Title:</strong> ${pos.title}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Code:</strong> ${pos.code || '—'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Department:</strong> ${pos.department ? pos.department.name : '—'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Employment Type:</strong> ${pos.employment_type.charAt(0).toUpperCase() + pos.employment_type.slice(1)}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Min Salary:</strong> ${pos.min_salary ? new Intl.NumberFormat('en-TZ', {style: 'currency', currency: 'TZS'}).format(pos.min_salary) : '—'}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Max Salary:</strong> ${pos.max_salary ? new Intl.NumberFormat('en-TZ', {style: 'currency', currency: 'TZS'}).format(pos.max_salary) : '—'}
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Description:</strong><br>
                            <p>${pos.description || '—'}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Requirements:</strong><br>
                            <p>${pos.requirements || '—'}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Responsibilities:</strong><br>
                            <p>${pos.responsibilities || '—'}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <strong>Status:</strong> 
                            <span class="badge ${pos.is_active ? 'bg-success' : 'bg-secondary'}">${pos.is_active ? 'Active' : 'Inactive'}</span>
                        </div>
                    </div>
                `;
                $('#positionDetails').html(html);
                $('#viewPositionModal').modal('show');
            } else {
                showToast('Failed to load position: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            showToast('Error loading position: ' + error.message, 'error');
        });
}

function editPosition(id) {
    currentPositionId = id;
    
    fetch(`{{ route('positions.show', ':id') }}`.replace(':id', id), {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pos = data.position;
                $('#positionModalTitle').text('Edit Position');
                $('#submitBtn').text('Update Position');
                $('#positionId').val(pos.id);
                $('#title').val(pos.title || '');
                $('#code').val(pos.code || '');
                $('#description').val(pos.description || '');
                $('#department_id').val(pos.department_id || '');
                $('#employment_type').val(pos.employment_type || 'permanent');
                $('#min_salary').val(pos.min_salary || '');
                $('#max_salary').val(pos.max_salary || '');
                $('#requirements').val(pos.requirements || '');
                $('#responsibilities').val(pos.responsibilities || '');
                $('#is_active').prop('checked', pos.is_active !== false);
                
                // Ensure required fields have values
                if (!$('#title').val()) {
                    $('#title').val(pos.title || '');
                }
                if (!$('#employment_type').val()) {
                    $('#employment_type').val('permanent');
                }
                
                $('#positionModal').modal('show');
            } else {
                showToast('Failed to load position: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            showToast('Error loading position: ' + error.message, 'error');
        });
}

function deletePosition(id) {
    if (!confirm('Are you sure you want to delete this position? This action cannot be undone. Positions with assigned employees cannot be deleted.')) {
        return;
    }

    fetch(`{{ route('positions.destroy', ':id') }}`.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Position deleted successfully!', 'success');
            location.reload();
        } else {
            showToast('Failed to delete: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        showToast('Error deleting position: ' + error.message, 'error');
    });
}

$('#positionForm').on('submit', function(e) {
    e.preventDefault();
    
    const id = $('#positionId').val();
    const isEdit = !!id;
    
    // Get all form values directly
    const title = $('#title').val() || '';
    const code = $('#code').val() || '';
    const description = $('#description').val() || '';
    const department_id = $('#department_id').val() || '';
    const min_salary = $('#min_salary').val() || '';
    const max_salary = $('#max_salary').val() || '';
    const employment_type = $('#employment_type').val() || 'permanent';
    const requirements = $('#requirements').val() || '';
    const responsibilities = $('#responsibilities').val() || '';
    const is_active = $('#is_active').is(':checked') ? 1 : 0;
    
    // Validate required fields
    if (!title || title.trim() === '') {
        showToast('Error: Title is required', 'error');
        $('#title').focus();
        return;
    }
    
    if (!employment_type || employment_type.trim() === '') {
        showToast('Error: Employment type is required', 'error');
        $('#employment_type').focus();
        return;
    }
    
    // Create FormData and explicitly set all fields
    const formData = new FormData();
    formData.append('title', title);
    if (code) formData.append('code', code);
    if (description) formData.append('description', description);
    if (department_id) formData.append('department_id', department_id);
    if (min_salary) formData.append('min_salary', min_salary);
    if (max_salary) formData.append('max_salary', max_salary);
    formData.append('employment_type', employment_type);
    if (requirements) formData.append('requirements', requirements);
    if (responsibilities) formData.append('responsibilities', responsibilities);
    formData.append('is_active', is_active);
    
    const url = isEdit ? `{{ route('positions.update', ':id') }}`.replace(':id', id) : '{{ route('positions.store') }}';
    
    // For PUT requests, add _method
    if (isEdit) {
        formData.append('_method', 'PUT');
    }

    fetch(url, {
        method: 'POST', // Always use POST, Laravel will handle PUT via _method
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(id ? 'Position updated successfully!' : 'Position created successfully!', 'success');
            $('#positionModal').modal('hide');
            location.reload();
        } else {
            const errors = data.errors || {};
            let errorMsg = data.message || 'Validation failed';
            if (Object.keys(errors).length > 0) {
                errorMsg = Object.values(errors).flat().join('\n');
            }
            showToast('Error: ' + errorMsg, 'error');
        }
    })
    .catch(error => {
        showToast('Error: ' + error.message, 'error');
    });
});
</script>
@endpush

