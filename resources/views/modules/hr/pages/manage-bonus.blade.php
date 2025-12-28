@extends('layouts.app')

@section('title', 'Bonus Management - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-success" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-gift me-2"></i>Bonus Management
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Manage monthly bonus payments for employees
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('modules.hr.payroll') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-1"></i>Back to Payroll
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-success" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success">
                            <i class="bx bx-money fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Bonus</h6>
                            <h3 class="mb-0 fw-bold text-success">TZS {{ number_format($totalAmount ?? 0, 0) }}</h3>
                            <small class="text-muted">This Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-info" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-info">
                            <i class="bx bx-group fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Employees</h6>
                            <h3 class="mb-0 fw-bold text-info">{{ $employeeCount ?? 0 }}</h3>
                            <small class="text-muted">With Bonus</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-warning" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-warning">
                            <i class="bx bx-bar-chart fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Avg Bonus</h6>
                            <h3 class="mb-0 fw-bold text-warning">TZS {{ number_format($avgAmount ?? 0, 0) }}</h3>
                            <small class="text-muted">Per Employee</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Month Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-body">
                    <form method="GET" action="{{ route('payroll.bonus.index') }}" class="d-flex gap-3 align-items-end">
                        <div class="flex-grow-1">
                            <label class="form-label">Select Month</label>
                            <input type="month" name="month" class="form-control" value="{{ $selectedMonth }}" required>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-search me-1"></i>Load Month
                        </button>
                        <button type="button" class="btn btn-primary" onclick="showAddBonusModal()">
                            <i class="bx bx-plus me-1"></i>Add Bonus
                        </button>
                        <button type="button" class="btn btn-info" onclick="showBulkBonusModal()">
                            <i class="bx bx-layer me-1"></i>Bulk Create
                        </button>
                        <button type="button" class="btn btn-warning" onclick="downloadBonusTemplate()">
                            <i class="bx bx-download me-1"></i>Download Template
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bonus Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="bx bx-table me-2"></i>Bonus Records - {{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th class="text-end">Amount</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    @php
                                        $bonus = $bonuses[$employee->id] ?? null;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded-circle bg-label-success">{{ substr($employee->name, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $employee->name }}</div>
                                                    <small class="text-muted">{{ $employee->employee->employee_id ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->primaryDepartment->name ?? 'N/A' }}</td>
                                        <td class="text-end">
                                            @if($bonus)
                                                <span class="fw-bold text-success">TZS {{ number_format($bonus->amount, 0) }}</span>
                                            @else
                                                <span class="text-muted">TZS 0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($bonus && $bonus->bonus_type)
                                                <span class="badge bg-label-info">{{ $bonus->bonus_type }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($bonus)
                                                <small class="text-muted">{{ Str::limit($bonus->description ?? 'N/A', 30) }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($bonus)
                                                <button class="btn btn-sm btn-success" onclick="editBonus({{ $bonus->id }}, {{ $employee->id }}, '{{ $employee->name }}', {{ $bonus->amount }}, '{{ $bonus->bonus_type ?? '' }}', '{{ $bonus->description ?? '' }}')">
                                                    <i class="bx bx-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="deleteBonus({{ $bonus->id }})">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-primary" onclick="addBonus({{ $employee->id }}, '{{ $employee->name }}')">
                                                    <i class="bx bx-plus"></i> Add
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No employees found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Bonus Modal -->
<div class="modal fade" id="bonusModal" tabindex="-1" style="z-index: 10090 !important;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="bonusModalTitle">Add Bonus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bonusForm">
                <div class="modal-body">
                    <input type="hidden" id="bonus_id" name="bonus_id">
                    <input type="hidden" id="employee_id" name="employee_id">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <input type="text" class="form-control" id="employee_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="amount" class="form-control" step="1000" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bonus Type</label>
                        <select name="bonus_type" id="bonus_type" class="form-select">
                            <option value="">Select Type</option>
                            <option value="Performance">Performance</option>
                            <option value="Annual">Annual</option>
                            <option value="Special">Special</option>
                            <option value="Project">Project</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Create Bonus Modal -->
<div class="modal fade" id="bulkBonusModal" tabindex="-1" style="z-index: 10090 !important;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Bulk Create Bonus</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkBonusForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Bulk Create Bonus</strong><br>
                        Upload a CSV or Excel file to create multiple bonus records at once. CSV format is recommended.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Upload File <span class="text-danger">*</span></label>
                        <input type="file" name="file" id="bulk_bonus_file" class="form-control" accept=".csv,.xlsx,.xls" required>
                        <small class="text-muted">Accepted formats: CSV, XLSX, XLS</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">File Format</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <small class="text-muted">
                                    <strong>Template includes all active employees with their details:</strong><br>
                                    - employee_code: Employee Code like EMP006 (pre-filled, primary identifier)<br>
                                    - employee_id: System Employee ID (pre-filled, for reference)<br>
                                    - employee_name: Full Name (pre-filled)<br>
                                    - department: Department Name (pre-filled)<br>
                                    - basic_salary: Current Basic Salary (pre-filled)<br>
                                    <br>
                                    <strong>You only need to fill:</strong><br>
                                    - amount: Bonus amount (numeric, required)<br>
                                    - bonus_type (optional): Performance, Annual, Special, Project, Other<br>
                                    - description (optional): Description of bonus
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i class="bx bx-upload me-1"></i>Upload & Process
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Ensure modals appear in front of all elements */
    .modal {
        z-index: 10090 !important;
    }
    .modal-backdrop {
        z-index: 10089 !important;
    }
    .modal.show {
        z-index: 10090 !important;
    }
    
    /* SweetAlert2 z-index */
    .swal2-container {
        z-index: 100000 !important;
    }
    .swal2-popup {
        z-index: 100001 !important;
    }
    .swal2-backdrop-show {
        z-index: 99999 !important;
    }
</style>
@endpush

@push('scripts')
<script>
let bonusModal = new bootstrap.Modal(document.getElementById('bonusModal'));
let bulkBonusModal = new bootstrap.Modal(document.getElementById('bulkBonusModal'));

function showBulkBonusModal() {
    document.getElementById('bulkBonusForm').reset();
    bulkBonusModal.show();
}

function downloadBonusTemplate() {
    window.location.href = '/payroll/bonus/template';
}

function showAddBonusModal() {
    document.getElementById('bonusModalTitle').textContent = 'Add Bonus';
    document.getElementById('bonusForm').reset();
    document.getElementById('bonus_id').value = '';
    document.getElementById('employee_id').value = '';
    document.getElementById('employee_name').value = '';
    bonusModal.show();
}

function addBonus(employeeId, employeeName) {
    document.getElementById('bonusModalTitle').textContent = 'Add Bonus';
    document.getElementById('bonusForm').reset();
    document.getElementById('bonus_id').value = '';
    document.getElementById('employee_id').value = employeeId;
    document.getElementById('employee_name').value = employeeName;
    bonusModal.show();
}

function editBonus(id, employeeId, employeeName, amount, bonusType, description) {
    document.getElementById('bonusModalTitle').textContent = 'Edit Bonus';
    document.getElementById('bonus_id').value = id;
    document.getElementById('employee_id').value = employeeId;
    document.getElementById('employee_name').value = employeeName;
    document.getElementById('amount').value = amount;
    document.getElementById('bonus_type').value = bonusType || '';
    document.getElementById('description').value = description || '';
    bonusModal.show();
}

function deleteBonus(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will deactivate the bonus record.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, deactivate it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/payroll/bonus/${id}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deactivated!', response.message, 'success').then(() => location.reload());
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to deactivate', 'error');
                }
            });
        }
    });
}

$('#bonusForm').submit(function(e) {
    e.preventDefault();
    const formData = $(this).serialize();
    const bonusId = $('#bonus_id').val();
    const url = bonusId ? `/payroll/bonus/${bonusId}` : '/payroll/bonus';
    const method = bonusId ? 'PUT' : 'POST';
    
    $.ajax({
        url: url,
        method: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success').then(() => location.reload());
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to save', 'error');
        }
    });
});

$('#bulkBonusForm').submit(function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    Swal.fire({
        title: 'Processing...',
        text: 'Uploading and processing file...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '/payroll/bonus/bulk',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                let message = `Successfully processed ${response.created || 0} new and ${response.updated || 0} updated records.`;
                if (response.errors && response.errors.length > 0) {
                    message += '\n\nErrors:\n' + response.errors.join('\n');
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Bulk Upload Complete',
                    html: message.replace(/\n/g, '<br>'),
                    width: '600px'
                }).then(() => location.reload());
            }
        },
        error: function(xhr) {
            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to process bulk upload', 'error');
        }
    });
});
</script>
@endpush
@endsection

