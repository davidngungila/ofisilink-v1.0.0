@extends('layouts.app')

@section('title', 'Departments Management - OfisiLink')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-buildings me-2"></i>Departments Management
            </h1>
            <p class="text-muted mb-0">Manage organizational departments and assign department heads</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" id="new-department-btn">
                <i class="bx bx-plus-circle me-1"></i>Add New Department
            </button>
        </div>
    </div>

    <!-- Departments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Departments</h6>
        </div>
        <div class="card-body">
            @if($departments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Department Head</th>
                                <th class="text-center">Employees</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departments as $department)
                            <tr>
                                <td>
                                    <strong><code>{{ $department->code ?? '—' }}</code></strong>
                                </td>
                                <td>
                                    <strong>{{ $department->name }}</strong>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;" title="{{ $department->description ?? 'No description' }}">
                                        {{ $department->description ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    @if($department->head)
                                        <span class="badge bg-info">
                                            <i class="bx bx-user"></i> {{ $department->head->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary">{{ $department->primaryUsers->count() ?? 0 }} employees</span>
                                </td>
                                <td class="text-center">
                                    @if($department->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('departments.show', $department->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                            <i class="bx bx-show"></i> View
                                        </a>
                                        <button type="button" class="btn btn-sm btn-info" onclick="editDepartment({{ $department->id }})">
                                            <i class="bx bx-edit"></i> Edit
                                        </button>
                                        @if($department->primaryUsers->count() == 0)
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteDepartment({{ $department->id }})">
                                                <i class="bx bx-trash"></i> Delete
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-secondary" disabled title="Cannot delete - has {{ $department->primaryUsers->count() }} employee(s)">
                                                <i class="bx bx-lock"></i> Locked
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
                        <i class="bx bx-buildings text-muted" style="font-size: 4rem;"></i>
                    </div>
                    <h5 class="text-muted">No Departments Found</h5>
                    <p class="text-muted">Click "Add New Department" to create your first department.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Add/Edit Department Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentModalTitle">Add New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="departmentForm">
                @csrf
                <input type="hidden" id="departmentId" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g., Human Resources, Finance, IT">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="code" class="form-label">Department Code</label>
                            <input type="text" class="form-control" id="code" name="code" placeholder="e.g., HR, FIN, IT">
                            <small class="text-muted">Unique code for this department (optional)</small>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Brief description of this department..."></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="head_id" class="form-label">Department Head</label>
                            <select class="form-control" id="head_id" name="head_id">
                                <option value="">— Select Department Head —</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Optional: Assign a department head</small>
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
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentDepartmentId = null;

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

$('#new-department-btn').on('click', function() {
    currentDepartmentId = null;
    $('#departmentModalTitle').text('Add New Department');
    $('#submitBtn').text('Save Department');
    $('#departmentForm')[0].reset();
    $('#departmentId').val('');
    $('#is_active').prop('checked', true);
    $('#head_id').val('');
    $('#departmentModal').modal('show');
});

function editDepartment(id) {
    currentDepartmentId = id;
    
    // Fetch department data
    fetch(`{{ route('departments.show', ':id') }}`.replace(':id', id), {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const dept = data.department;
                $('#departmentModalTitle').text('Edit Department');
                $('#submitBtn').text('Update Department');
                $('#departmentId').val(dept.id);
                $('#name').val(dept.name);
                $('#code').val(dept.code || '');
                $('#description').val(dept.description || '');
                $('#head_id').val(dept.head_id || '');
                $('#is_active').prop('checked', dept.is_active !== false);
                $('#departmentModal').modal('show');
            } else {
                showToast('Failed to load department: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            showToast('Error loading department: ' + error.message, 'error');
        });
}

function deleteDepartment(id) {
    if (!confirm('Are you sure you want to delete this department? This action cannot be undone. Departments with assigned employees cannot be deleted.')) {
        return;
    }

    fetch(`{{ route('departments.destroy', ':id') }}`.replace(':id', id), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Department deleted successfully!', 'success');
            location.reload();
        } else {
            showToast('Failed to delete: ' + (data.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        showToast('Error deleting department: ' + error.message, 'error');
    });
}

$('#departmentForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = $('#departmentId').val();
    const url = id ? `{{ route('departments.update', ':id') }}`.replace(':id', id) : '{{ route('departments.store') }}';
    const method = id ? 'PUT' : 'POST';
    
    // Add checkbox value
    formData.append('is_active', $('#is_active').is(':checked') ? 1 : 0);
    
    // Remove _method if PUT
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(id ? 'Department updated successfully!' : 'Department created successfully!', 'success');
            $('#departmentModal').modal('hide');
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

