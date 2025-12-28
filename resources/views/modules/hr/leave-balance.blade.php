@extends('layouts.app')

@section('title', 'Leave Balance Management - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-calculator me-2"></i>Leave Balance Management
                            </h4>
                            <p class="card-text text-white-50 mb-0">Manage employee leave balances</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.leave') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Leave Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Balance Management Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Employee Leave Balance Management</h6>
                        <button class="btn btn-sm btn-primary" onclick="showBalanceModal()">
                            <i class="bx bx-plus"></i> Manage Balance
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Year</label>
                            <select class="form-select" id="balance-year-filter" onchange="loadBalanceManagement()">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search Employee</label>
                            <input type="text" class="form-control" id="balance-search" placeholder="Search by name..." onkeyup="filterBalanceTable()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter by Department</label>
                            <select class="form-select" id="balance-dept-filter" onchange="loadBalanceManagement()">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Balance Table -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="selectAllBalances()">
                                <i class="bx bx-check-square"></i> Select All
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="deselectAllBalances()">
                                <i class="bx bx-square"></i> Deselect All
                            </button>
                            <span class="ms-2 text-muted" id="selected-balances-count">0 selected</span>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-check-double"></i> Bulk Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="bulkUpdateBalance(); return false;"><i class="bx bx-edit text-primary"></i> Update Selected Balances</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkUpdateAllStaff(); return false;"><i class="bx bx-check-double text-success"></i> Update All Staff</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkResetBalance(); return false;"><i class="bx bx-reset text-warning"></i> Reset Selected</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkResetAllStaff(); return false;"><i class="bx bx-refresh text-warning"></i> Reset All Staff</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkExportBalance(); return false;"><i class="bx bx-download text-info"></i> Export Selected</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="balance-table">
                            <thead class="table-light">
                                <tr>
                                    <th width="40">
                                        <input type="checkbox" id="select-all-balances" onchange="toggleAllBalances(this)">
                                    </th>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th>Total Days</th>
                                    <th>Days Taken</th>
                                    <th>Remaining</th>
                                    <th>Carry Forward</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="balance-table-body">
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Loading balances...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HR Balance Management Modal -->
<div class="modal fade" id="balanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="balanceForm">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Employee Leave Balance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="action" value="hr_manage_leave_balance">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee *</label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">-- Select Employee --</option>
                                @foreach(\App\Models\User::where('is_active', true)->orderBy('name')->get() as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->name }}@if($emp->employee && $emp->employee->position) ({{ $emp->employee->position }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Financial Year *</label>
                            <select name="financial_year" class="form-select" required>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Days Allotted *</label>
                            <input type="number" name="total_days_allotted" class="form-control" value="28" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Carry Forward Days</label>
                            <input type="number" name="carry_forward_days" class="form-control" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Balance</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Global functions for balance management page - must be defined before document.ready
var csrfToken = '';

// Fallback if SweetAlert2 isn't loaded
if (typeof window.Swal === 'undefined') {
    window.Swal = {
        fire: function(optsOrTitle, text, icon) {
            if (typeof optsOrTitle === 'object') {
                const title = optsOrTitle.title || '';
                const html = optsOrTitle.html || optsOrTitle.text || '';
                alert(title + (html ? '\n\n' + html : ''));
                return Promise.resolve({ isConfirmed: true });
            } else {
                alert(optsOrTitle + (text ? '\n\n' + text : ''));
                return Promise.resolve({ isConfirmed: true });
            }
        },
        close: function() {},
        showLoading: function() {}
    };
}

// Show balance modal
function showBalanceModal() {
    if (typeof $ !== 'undefined' && $('#balanceModal').length) {
        $('#balanceForm')[0].reset();
        $('#balanceModal').modal('show');
    }
}

// Load balance management data
function loadBalanceManagement() {
    const year = $('#balance-year-filter').val();
    const deptId = $('#balance-dept-filter').val();
    const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
    
    $('#balance-table-body').html('<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></td></tr>');
    
    $.post('{{ route("leave.hr.balance-data") }}', {
        _token: token,
        financial_year: year,
        department_id: deptId
    }, function(response) {
        if (response.success) {
            let html = '';
            // The API returns 'data' not 'balances'
            const balances = response.data || response.balances || [];
            if (balances.length > 0) {
                balances.forEach(function(balance) {
                    // Use remaining_days from response if available, otherwise calculate
                    const remaining = balance.remaining_days !== undefined 
                        ? balance.remaining_days 
                        : ((balance.total_days_allotted || 0) - (balance.days_taken || 0));
                    html += `
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input balance-checkbox" value="${balance.employee_id}" data-year="${year}" id="balance-${balance.employee_id}-${year}">
                            </td>
                            <td>${balance.employee_name || 'N/A'}</td>
                            <td>${balance.department_name || 'N/A'}</td>
                            <td><span class="badge bg-primary">${balance.total_days_allotted || 0}</span></td>
                            <td><span class="badge bg-warning">${balance.days_taken || 0}</span></td>
                            <td><span class="badge bg-success">${remaining}</span></td>
                            <td><span class="badge bg-info">${balance.carry_forward_days || 0}</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="editBalance(${balance.employee_id}, ${year})" title="Edit Balance">
                                    <i class="bx bx-edit"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="8" class="text-center py-4 text-muted"><i class="bx bx-info-circle me-2"></i>No balance data found for the selected filters.</td></tr>';
            }
            $('#balance-table-body').html(html);
        } else {
            $('#balance-table-body').html('<tr><td colspan="8" class="text-center py-4 text-danger">Error loading balance data. Please try again.</td></tr>');
        }
    }).fail(function() {
        $('#balance-table-body').html('<tr><td colspan="8" class="text-center py-4 text-danger">Failed to load balance data. Please refresh the page.</td></tr>');
    });
}

// Filter balance table
function filterBalanceTable() {
    const search = $('#balance-search').val().toLowerCase();
    $('#balance-table tbody tr').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.includes(search));
    });
}

// Select/Deselect all balances
function selectAllBalances() {
    $('.balance-checkbox').prop('checked', true);
    updateSelectedBalancesCount();
}

function deselectAllBalances() {
    $('.balance-checkbox').prop('checked', false);
    updateSelectedBalancesCount();
}

function toggleAllBalances(checkbox) {
    $('.balance-checkbox').prop('checked', checkbox.checked);
    updateSelectedBalancesCount();
}

function updateSelectedBalancesCount() {
    const count = $('.balance-checkbox:checked').length;
    $('#selected-balances-count').text(count + ' selected');
}

// Edit balance
function editBalance(employeeId, year) {
    // This would open the modal with pre-filled data
    // For now, just show the modal
    showBalanceModal();
}

// Bulk operations
function bulkUpdateBalance() {
    const selected = $('.balance-checkbox:checked');
    
    if (selected.length === 0) {
        Swal.fire('No Selection', 'Please select at least one employee balance to update.', 'warning');
        return;
    }
    
    const selectedCount = selected.length;
    
    Swal.fire({
        title: 'Bulk Update Leave Balance',
        html: `
            <div class="text-start">
                <p class="mb-3">You are about to update <strong>${selectedCount}</strong> employee balance(s).</p>
                <div class="mb-3">
                    <label class="form-label">Total Days Allotted *</label>
                    <input type="number" id="bulk-total-days" class="form-control" value="28" min="0" required>
                    <small class="form-text text-muted">This will be applied to all selected employees</small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Carry Forward Days</label>
                    <input type="number" id="bulk-carry-forward" class="form-control" value="0" min="0">
                    <small class="form-text text-muted">Days to carry forward from previous year</small>
                </div>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Update All Selected',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        preConfirm: () => {
            const totalDays = parseInt(document.getElementById('bulk-total-days').value);
            const carryForward = parseInt(document.getElementById('bulk-carry-forward').value) || 0;
            
            if (!totalDays || totalDays < 0) {
                Swal.showValidationMessage('Please enter a valid total days allotted (minimum 0)');
                return false;
            }
            
            return {
                totalDays: totalDays,
                carryForward: carryForward
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const balances = [];
            selected.each(function() {
                balances.push({
                    employee_id: $(this).val(),
                    year: $(this).data('year')
                });
            });
            
            const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
            
            Swal.fire({
                title: 'Updating...',
                text: 'Please wait while we update the balances.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route("leave.hr.bulk-operations") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    _token: token,
                    action: 'bulk_update_balance',
                    balances: balances,
                    total_days_allotted: result.value.totalDays,
                    carry_forward_days: result.value.carryForward
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            loadBalanceManagement();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to update balances. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', errorMessage, 'error');
                }
            });
        }
    });
}

function bulkResetBalance() {
    const selected = $('.balance-checkbox:checked');
    
    if (selected.length === 0) {
        Swal.fire('No Selection', 'Please select at least one employee balance to reset.', 'warning');
        return;
    }
    
    const selectedCount = selected.length;
    
    Swal.fire({
        title: 'Reset Leave Balance?',
        html: `
            <p>You are about to reset <strong>${selectedCount}</strong> employee balance(s).</p>
            <p class="text-warning"><strong>Warning:</strong> This will set "Days Taken" to 0 for all selected employees. This action cannot be undone.</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Reset Selected',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            const balances = [];
            selected.each(function() {
                balances.push({
                    employee_id: $(this).val(),
                    year: $(this).data('year')
                });
            });
            
            const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
            
            Swal.fire({
                title: 'Resetting...',
                text: 'Please wait while we reset the balances.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route("leave.hr.bulk-operations") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    _token: token,
                    action: 'bulk_reset_balance',
                    balances: balances
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            loadBalanceManagement();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to reset balances. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', errorMessage, 'error');
                }
            });
        }
    });
}

function bulkUpdateAllStaff() {
    // Select all visible balances
    $('.balance-checkbox').prop('checked', true);
    updateSelectedBalancesCount();
    
    // Then call bulk update
    bulkUpdateBalance();
}

function bulkResetAllStaff() {
    // Select all visible balances
    $('.balance-checkbox').prop('checked', true);
    updateSelectedBalancesCount();
    
    // Then call bulk reset
    bulkResetBalance();
}

function bulkExportBalance() {
    const selected = $('.balance-checkbox:checked');
    
    if (selected.length === 0) {
        Swal.fire('No Selection', 'Please select at least one employee balance to export.', 'warning');
        return;
    }
    
    Swal.fire('Info', 'Export functionality will be implemented soon. This will export the selected balances to Excel/CSV.', 'info');
}

// Balance form submission
$(document).ready(function() {
    // Get CSRF token
    csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    $('#balanceForm').on('submit', function(e) {
        e.preventDefault();
        $.post('{{ route("leave.hr.manage-balance") }}', $(this).serialize(), function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#balanceModal').modal('hide');
                loadBalanceManagement();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        }).fail(function() {
            Swal.fire('Error!', 'Failed to update balance. Please try again.', 'error');
        });
    });
    
    // Load balance data on page load
    loadBalanceManagement();
});
</script>
@endpush

