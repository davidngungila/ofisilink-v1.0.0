@extends('layouts.app')

@section('title', 'Department Details - ' . $department->name . ' - OfisiLink')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb" class="mb-2">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('modules.hr.departments') }}">Departments</a></li>
                    <li class="breadcrumb-item active">{{ $department->name }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-buildings me-2"></i>{{ $department->name }}
                @if($department->code)
                    <small class="text-muted">({{ $department->code }})</small>
                @endif
            </h1>
            <p class="text-muted mb-0">Department Details & Information</p>
        </div>
        <div>
            <a href="{{ route('modules.hr.departments') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to Departments
            </a>
            <button class="btn btn-primary" onclick="editDepartment({{ $department->id }})">
                <i class="bx bx-edit me-1"></i>Edit Department
            </button>
        </div>
    </div>

    <!-- Status Badge -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-{{ $department->is_active ? 'success' : 'secondary' }} d-flex justify-content-between align-items-center" role="alert">
                <div>
                    <i class="bx bx-{{ $department->is_active ? 'check-circle' : 'x-circle' }} me-2"></i>
                    <strong>Status:</strong> {{ $department->is_active ? 'Active' : 'Inactive' }}
                </div>
                <div>
                    <small class="text-muted">
                        Created: {{ \Carbon\Carbon::parse($department->created_at)->format('M d, Y') }} | 
                        Updated: {{ \Carbon\Carbon::parse($department->updated_at)->format('M d, Y') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Main Information -->
        <div class="col-lg-8">
            <!-- Department Information Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-info-circle me-2"></i>Department Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bx bx-tag me-2 text-primary"></i>Department Code:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($department->code)
                                <code class="bg-light px-2 py-1 rounded">{{ $department->code }}</code>
                            @else
                                <span class="text-muted">Not assigned</span>
                            @endif
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bx bx-building me-2 text-primary"></i>Department Name:</strong>
                        </div>
                        <div class="col-md-8">
                            <span class="h5 mb-0">{{ $department->name }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <strong><i class="bx bx-file-blank me-2 text-primary"></i>Description:</strong>
                        </div>
                        <div class="col-md-8">
                            @if($department->description)
                                <p class="mb-0">{{ $department->description }}</p>
                            @else
                                <span class="text-muted">No description provided</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Department Head Card -->
            @if($department->head)
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-info text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-user-check me-2"></i>Department Head
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-circle me-3" style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: bold;">
                            {{ substr($department->head->name, 0, 1) }}
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1">{{ $department->head->name }}</h5>
                            <p class="text-muted mb-1">
                                <i class="bx bx-envelope me-1"></i>{{ $department->head->email }}
                            </p>
                            @if($department->head->phone)
                            <p class="text-muted mb-0">
                                <i class="bx bx-phone me-1"></i>{{ $department->head->phone }}
                            </p>
                            @endif
                            @if($department->head->employee_id)
                            <p class="text-muted mb-0">
                                <i class="bx bx-id-card me-1"></i>Employee ID: {{ $department->head->employee_id }}
                            </p>
                            @endif
                        </div>
                        <div class="text-end">
                            <span class="badge bg-{{ $department->head->is_active ? 'success' : 'secondary' }}">
                                {{ $department->head->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            @else
            <div class="card shadow mb-4 border-warning">
                <div class="card-header py-3 bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-user-x me-2"></i>Department Head
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-3">
                        <i class="bx bx-user-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No department head assigned</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Statistics & Quick Info -->
        <div class="col-lg-4">
            <!-- Statistics Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-success text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-stats me-2"></i>Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bx bx-group me-2 text-primary"></i>Total Employees:</span>
                            <strong class="h5 mb-0">{{ $department->primaryUsers->count() }}</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ min(100, ($department->primaryUsers->count() / 50) * 100) }}%"></div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bx bx-check-circle me-2 text-success"></i>Active Employees:</span>
                            <strong class="h5 mb-0">{{ $department->primaryUsers->where('is_active', true)->count() }}</strong>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span><i class="bx bx-x-circle me-2 text-secondary"></i>Inactive Employees:</span>
                            <strong class="h5 mb-0">{{ $department->primaryUsers->where('is_active', false)->count() }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-secondary text-white">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-cog me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="editDepartment({{ $department->id }})">
                            <i class="bx bx-edit me-2"></i>Edit Department
                        </button>
                        @if($department->primaryUsers->count() == 0)
                            <button class="btn btn-outline-danger" onclick="deleteDepartment({{ $department->id }})">
                                <i class="bx bx-trash me-2"></i>Delete Department
                            </button>
                        @else
                            <button class="btn btn-outline-secondary" disabled title="Cannot delete - has {{ $department->primaryUsers->count() }} employee(s)">
                                <i class="bx bx-lock me-2"></i>Delete (Locked)
                            </button>
                        @endif
                        <a href="{{ route('modules.hr.employees') }}?department={{ $department->id }}" class="btn btn-outline-info">
                            <i class="bx bx-group me-2"></i>View All Employees
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Employees List Card -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-group me-2"></i>Department Employees ({{ $department->primaryUsers->count() }})
                    </h6>
                    @if($department->primaryUsers->count() > 0)
                    <a href="{{ route('modules.hr.employees') }}?department={{ $department->id }}" class="btn btn-sm btn-light">
                        <i class="bx bx-link-external me-1"></i>View All
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    @if($department->primaryUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Hire Date</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($department->primaryUsers as $employee)
                                    <tr>
                                        <td>
                                            <code>{{ $employee->employee_id ?? '—' }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $employee->name }}</strong>
                                        </td>
                                        <td>
                                            <a href="mailto:{{ $employee->email }}" class="text-decoration-none">
                                                <i class="bx bx-envelope me-1"></i>{{ $employee->email }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($employee->phone)
                                                <a href="tel:{{ $employee->phone }}" class="text-decoration-none">
                                                    <i class="bx bx-phone me-1"></i>{{ $employee->phone }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($employee->hire_date)
                                                {{ \Carbon\Carbon::parse($employee->hire_date)->format('M d, Y') }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-{{ $employee->is_active ? 'success' : 'secondary' }}">
                                                {{ $employee->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-info" title="View Details">
                                                <i class="bx bx-show"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bx bx-group text-muted" style="font-size: 4rem;"></i>
                            <h5 class="text-muted mt-3">No Employees in This Department</h5>
                            <p class="text-muted">Employees will appear here once they are assigned to this department.</p>
                            <a href="{{ route('modules.hr.employees') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i>Add Employee
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Department Modal (will be loaded from the index page script) -->
<div class="modal fade" id="departmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="departmentModalTitle">Edit Department</h5>
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
                                @foreach(\App\Models\User::where('is_active', true)->orderBy('name')->get() as $user)
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
                    <button type="submit" class="btn btn-primary" id="submitBtn">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
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

function editDepartment(id) {
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
            setTimeout(() => {
                window.location.href = '{{ route('modules.hr.departments') }}';
            }, 1000);
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
    const url = `{{ route('departments.update', ':id') }}`.replace(':id', id);
    
    // Add checkbox value
    formData.append('is_active', $('#is_active').is(':checked') ? 1 : 0);
    formData.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Department updated successfully!', 'success');
            $('#departmentModal').modal('hide');
            setTimeout(() => {
                location.reload();
            }, 1000);
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

@push('styles')
<style>
    .avatar-circle {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .card-header {
        border-bottom: none;
    }
    .progress {
        border-radius: 10px;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,0,0,.02);
    }
</style>
@endpush

