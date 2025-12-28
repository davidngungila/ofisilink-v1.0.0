<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1"><i class="bx bx-money-withdraw me-2"></i>Deduction Management</h5>
                <p class="text-muted mb-0">Manage employee salary deductions including PAYE, NSSF, NHIF, HESLB, WCF, SDL, and custom deductions</p>
            </div>
            <div class="btn-group">
                <button type="button" class="btn btn-primary" onclick="showAddDeductionModal()">
                    <i class="bx bx-plus me-1"></i>Add Deduction
                </button>
                <button type="button" class="btn btn-success" onclick="showBulkDeductionModal()">
                    <i class="bx bx-layer me-1"></i>Bulk Statutory Deductions
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Deduction Types Info Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-primary">
            <div class="card-body text-center">
                <i class="bx bx-money fs-1 text-primary mb-2"></i>
                <h6 class="mb-1">PAYE</h6>
                <small class="text-muted">Pay As You Earn Tax</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body text-center">
                <i class="bx bx-shield fs-1 text-info mb-2"></i>
                <h6 class="mb-1">NSSF</h6>
                <small class="text-muted">National Social Security Fund</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-success">
            <div class="card-body text-center">
                <i class="bx bx-heart fs-1 text-success mb-2"></i>
                <h6 class="mb-1">NHIF</h6>
                <small class="text-muted">National Health Insurance Fund</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body text-center">
                <i class="bx bx-graduation fs-1 text-warning mb-2"></i>
                <h6 class="mb-1">HESLB</h6>
                <small class="text-muted">Higher Education Student Loans Board</small>
            </div>
        </div>
    </div>
</div>

<!-- Employee Deductions Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Employee Deductions</h6>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="loadDeductionsSummary()">
                <i class="bx bx-refresh me-1"></i>Refresh
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="deductionsTable">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th class="text-end">Monthly Deductions</th>
                        <th class="text-end">One-Time Deductions</th>
                        <th>Deductions Count</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="deductionsTableBody">
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Deduction Modal -->
<div class="modal fade" id="deductionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 10090 !important;">
    <div class="modal-dialog modal-lg" role="document" style="z-index: 10091 !important;">
        <div class="modal-content" style="z-index: 10092 !important;">
            <div class="modal-header">
                <h5 class="modal-title" id="deductionModalTitle">
                    <i class="bx bx-plus me-2"></i>Add Deduction
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="deductionForm">
                <div class="modal-body">
                    <input type="hidden" id="deduction_id" name="deduction_id">
                    <input type="hidden" id="deduction_employee_id" name="employee_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select" id="deduction_employee_select" name="employee_id" required>
                                <option value="">Select Employee</option>
                                @if(isset($employees) && $employees->count() > 0)
                                    @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->employee->employee_id ?? $employee->id }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Deduction Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="deduction_type" name="deduction_type" required>
                                <option value="">Select Type</option>
                                <option value="PAYE">PAYE (Pay As You Earn)</option>
                                <option value="NSSF">NSSF (National Social Security Fund)</option>
                                <option value="NHIF">NHIF (National Health Insurance Fund)</option>
                                <option value="HESLB">HESLB (Higher Education Student Loans Board)</option>
                                <option value="WCF">WCF (Workers Compensation Fund)</option>
                                <option value="SDL">SDL (Skills Development Levy)</option>
                                <option value="Loan">Loan</option>
                                <option value="Advance">Advance</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Calculation Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="deduction_calculation_method" name="calculation_method" required onchange="toggleDeductionCalculationMethod()">
                                <option value="fixed">Fixed Amount</option>
                                <option value="percentage">Percentage of Salary</option>
                                <option value="statutory">Statutory Formula (Auto)</option>
                            </select>
                            <small class="text-muted">Choose how the deduction amount is calculated</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" id="deduction_frequency" name="frequency" required>
                                <option value="monthly">Monthly</option>
                                <option value="one-time">One-Time</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row" id="deduction-amount-row">
                        <div class="col-md-6 mb-3" id="deduction-fixed-amount">
                            <label class="form-label">Amount (TZS) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="deduction_amount" name="amount" step="0.01" min="0">
                        </div>
                        <div class="col-md-6 mb-3" id="deduction-percentage-amount" style="display: none;">
                            <label class="form-label">Percentage (%) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="deduction_percentage" name="percentage" step="0.01" min="0" max="100" placeholder="e.g., 5 for 5%">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Calculated based on employee's basic salary</small>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" id="deduction-statutory-info" style="display: none;">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Statutory Calculation:</strong> The amount will be calculated automatically using Tanzania statutory rates based on the deduction type and employee's salary.
                        <div id="deduction-statutory-details" class="mt-2"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="deduction_start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="deduction_end_date" name="end_date">
                            <small class="text-muted">Leave empty for ongoing deductions</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="deduction_description" name="description" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="deduction_notes" name="notes" rows="2"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="deduction_is_active" name="is_active" checked>
                            <label class="form-check-label" for="deduction_is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Save Deduction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Statutory Deductions Modal -->
<div class="modal fade" id="bulkDeductionModal" tabindex="-1" aria-hidden="true" style="z-index: 10079;">
    <div class="modal-dialog modal-xl" style="z-index: 10080;">
        <div class="modal-content" style="z-index: 10081;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-layer me-2"></i>Bulk Statutory Deductions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkDeductionForm">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Bulk Operations:</strong> Create statutory deductions for multiple employees at once. 
                        Deductions will be calculated automatically based on each employee's salary using Tanzania statutory rates.
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Deduction Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="bulk_deduction_type" name="deduction_type" required>
                                <option value="">Select Statutory Deduction Type</option>
                                <option value="PAYE">PAYE (Pay As You Earn) - Progressive Tax Brackets</option>
                                <option value="NSSF">NSSF (National Social Security Fund) - 5% of Gross (capped at TZS 2,000,000)</option>
                                <option value="NHIF">NHIF (National Health Insurance Fund) - 3% of Gross (capped at TZS 1,000,000)</option>
                                <option value="HESLB">HESLB (Higher Education Student Loans Board) - 5% of Gross (capped at TZS 5,000,000, only for employees with student loans)</option>
                                <option value="WCF">WCF (Workers Compensation Fund) - 1% of Gross</option>
                                <option value="SDL">SDL (Skills Development Levy) - 3.5% of Gross</option>
                            </select>
                            <small class="text-muted">Select the statutory deduction type to apply to selected employees</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Frequency <span class="text-danger">*</span></label>
                            <select class="form-select" id="bulk_deduction_frequency" name="frequency" required>
                                <option value="monthly" selected>Monthly</option>
                                <option value="one-time">One-Time</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="bulk_deduction_start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" id="bulk_deduction_end_date" name="end_date">
                            <small class="text-muted">Leave empty for ongoing deductions</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="bulk_deduction_description" name="description" rows="2" placeholder="Optional description for the deduction"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" id="bulk_deduction_notes" name="notes" rows="2" placeholder="Optional notes"></textarea>
                    </div>
                    
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bx bx-user me-2"></i>Select Employees
                                <span class="badge bg-light text-primary ms-2" id="bulk-selected-count">0 selected</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllEmployeesForBulk()">
                                    <i class="bx bx-check-square me-1"></i>Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAllEmployeesForBulk()">
                                    <i class="bx bx-x me-1"></i>Deselect All
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="filterEmployeesForBulk()">
                                    <i class="bx bx-filter me-1"></i>Filter
                                </button>
                            </div>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" id="bulk-select-all-employees" class="form-check-input">
                                            </th>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th class="text-end">Basic Salary</th>
                                            <th class="text-end">Estimated Deduction</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bulkEmployeesTableBody">
                                        <!-- Will be populated by JavaScript -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning" id="bulk-calculation-info" style="display: none;">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Calculation Info:</strong> <span id="bulk-calculation-details"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-layer me-1"></i>Create Bulk Deductions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Employee Deductions Detail Modal -->
<div class="modal fade" id="employeeDeductionsModal" tabindex="-1" aria-hidden="true" style="z-index: 10080;">
    <div class="modal-dialog modal-xl" style="z-index: 10081;">
        <div class="modal-content" style="z-index: 10082;">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-list-ul me-2"></i>Employee Deductions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="employeeDeductionsModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let deductionsData = [];

// Load deductions summary on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('deductions-tab')) {
        document.getElementById('deductions-tab').addEventListener('shown.bs.tab', function() {
            loadDeductionsSummary();
        });
    }
});

function loadDeductionsSummary() {
    const tbody = document.getElementById('deductionsTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    
    fetch('{{ route("payroll.deductions.summary") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            deductionsData = data.employees || [];
            renderDeductionsTable(deductionsData);
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load deductions</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Error loading deductions</td></tr>';
    });
}

function renderDeductionsTable(employees) {
    const tbody = document.getElementById('deductionsTableBody');
    if (!tbody) return;
    
    if (!employees || employees.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><i class="bx bx-inbox fs-1 text-muted"></i><p class="text-muted mt-2 mb-0">No deductions found</p></td></tr>';
        return;
    }
    
    tbody.innerHTML = employees.map(emp => `
        <tr>
            <td>
                <strong>${escapeHtml(emp.name)}</strong><br>
                <small class="text-muted">ID: ${escapeHtml(emp.employee_id)}</small>
            </td>
            <td>${escapeHtml(emp.department)}</td>
            <td class="text-end">
                <strong class="text-primary">TZS ${formatCurrency(emp.total_monthly_deductions)}</strong>
            </td>
            <td class="text-end">
                <strong class="text-info">TZS ${formatCurrency(emp.total_one_time_deductions)}</strong>
            </td>
            <td>
                <span class="badge bg-label-primary">${emp.deductions_count}</span>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" onclick="viewEmployeeDeductions(${emp.id}, '${escapeHtml(emp.name)}')" title="View Details">
                        <i class="bx bx-show"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="showAddDeductionModal(${emp.id})" title="Add Deduction">
                        <i class="bx bx-plus"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function showAddDeductionModal(employeeId = null) {
    const modal = new bootstrap.Modal(document.getElementById('deductionModal'));
    const form = document.getElementById('deductionForm');
    const title = document.getElementById('deductionModalTitle');
    
    form.reset();
    document.getElementById('deduction_id').value = '';
    
    if (employeeId) {
        document.getElementById('deduction_employee_id').value = employeeId;
        document.getElementById('deduction_employee_select').value = employeeId;
        document.getElementById('deduction_employee_select').disabled = true;
    } else {
        document.getElementById('deduction_employee_select').disabled = false;
    }
    
    title.innerHTML = '<i class="bx bx-plus me-2"></i>Add Deduction';
    modal.show();
}

function editDeduction(deductionId, employeeId) {
    fetch(`{{ url('payroll/deductions/employee') }}/${employeeId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const deduction = data.deductions.find(d => d.id == deductionId);
            if (deduction) {
                const modal = new bootstrap.Modal(document.getElementById('deductionModal'));
                const form = document.getElementById('deductionForm');
                const title = document.getElementById('deductionModalTitle');
                
                document.getElementById('deduction_id').value = deduction.id;
                document.getElementById('deduction_employee_id').value = deduction.employee_id;
                document.getElementById('deduction_employee_select').value = deduction.employee_id;
                document.getElementById('deduction_employee_select').disabled = true;
                document.getElementById('deduction_type').value = deduction.deduction_type;
                document.getElementById('deduction_amount').value = deduction.amount;
                document.getElementById('deduction_frequency').value = deduction.frequency;
                document.getElementById('deduction_start_date').value = deduction.start_date;
                document.getElementById('deduction_end_date').value = deduction.end_date || '';
                document.getElementById('deduction_description').value = deduction.description || '';
                document.getElementById('deduction_notes').value = deduction.notes || '';
                document.getElementById('deduction_is_active').checked = deduction.is_active;
                
                title.innerHTML = '<i class="bx bx-edit me-2"></i>Edit Deduction';
                modal.show();
            }
        }
    });
}

function deleteDeduction(deductionId) {
    if (!confirm('Are you sure you want to delete this deduction?')) return;
    
    fetch(`{{ url('payroll/deductions') }}/${deductionId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', 'Deduction deleted successfully');
            }
            loadDeductionsSummary();
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to delete deduction');
            }
        }
    });
}

function viewEmployeeDeductions(employeeId, employeeName) {
    fetch(`{{ url('payroll/deductions/employee') }}/${employeeId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = new bootstrap.Modal(document.getElementById('employeeDeductionsModal'));
            const body = document.getElementById('employeeDeductionsModalBody');
            
            const deductions = data.deductions || [];
            
            let html = `
                <h6 class="mb-3">Deductions for: <strong>${escapeHtml(employeeName)}</strong></h6>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Amount</th>
                                <th>Frequency</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            if (deductions.length === 0) {
                html += '<tr><td colspan="8" class="text-center py-4 text-muted">No deductions found</td></tr>';
            } else {
                deductions.forEach(deduction => {
                    html += `
                        <tr>
                            <td><span class="badge bg-label-primary">${escapeHtml(deduction.deduction_type)}</span></td>
                            <td>${escapeHtml(deduction.description || 'N/A')}</td>
                            <td class="text-end"><strong>TZS ${formatCurrency(deduction.amount)}</strong></td>
                            <td>${deduction.frequency === 'monthly' ? '<span class="badge bg-label-info">Monthly</span>' : '<span class="badge bg-label-warning">One-Time</span>'}</td>
                            <td>${deduction.start_date}</td>
                            <td>${deduction.end_date || '<span class="text-muted">Ongoing</span>'}</td>
                            <td>${deduction.is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary" onclick="editDeduction(${deduction.id}, ${deduction.employee_id})" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteDeduction(${deduction.id})" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }
            
            html += `
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary" onclick="showAddDeductionModal(${employeeId})">
                        <i class="bx bx-plus me-1"></i>Add New Deduction
                    </button>
                </div>
            `;
            
            body.innerHTML = html;
            modal.show();
        }
    });
}

// Handle deduction form submission
document.getElementById('deductionForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const deductionId = document.getElementById('deduction_id').value;
    const calculationMethod = document.getElementById('deduction_calculation_method').value;
    const employeeId = formData.get('employee_id');
    const deductionType = formData.get('deduction_type');
    
    let amount = 0;
    
    // Calculate amount based on method
    if (calculationMethod === 'fixed') {
        amount = parseFloat(formData.get('amount')) || 0;
    } else if (calculationMethod === 'percentage') {
        const percentage = parseFloat(formData.get('percentage')) || 0;
        // We need to get employee salary - for now, we'll calculate on backend
        // Store percentage for backend calculation
        amount = percentage; // Will be recalculated on backend
    } else if (calculationMethod === 'statutory') {
        // Will be calculated on backend using statutory calculator
        amount = 0; // Placeholder, will be calculated
    }
    
    if (amount <= 0 && calculationMethod !== 'statutory') {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Please enter a valid amount or percentage');
        }
        return;
    }
    
    const url = deductionId 
        ? `{{ url('payroll/deductions') }}/${deductionId}`
        : '{{ route("payroll.deductions.store") }}';
    const method = deductionId ? 'PUT' : 'POST';
    
    const data = {
        employee_id: employeeId,
        deduction_type: deductionType,
        description: formData.get('description'),
        amount: amount,
        calculation_method: calculationMethod,
        percentage: calculationMethod === 'percentage' ? parseFloat(formData.get('percentage')) : null,
        frequency: formData.get('frequency'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date') || null,
        is_active: document.getElementById('deduction_is_active').checked,
        notes: formData.get('notes')
    };
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('deductionModal'));
            modal.hide();
            
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', result.message || 'Deduction saved successfully');
            }
            
            loadDeductionsSummary();
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', result.message || 'Failed to save deduction');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'An error occurred while saving the deduction');
        }
    });
});

function formatCurrency(amount) {
    return (Number(amount) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Bulk Deduction Operations
let allEmployeesForBulk = [];
let selectedEmployeesForBulk = [];

function showBulkDeductionModal() {
    const modal = new bootstrap.Modal(document.getElementById('bulkDeductionModal'));
    const form = document.getElementById('bulkDeductionForm');
    
    form.reset();
    selectedEmployeesForBulk = [];
    document.getElementById('bulk-selected-count').textContent = '0 selected';
    
    // Load employees
    loadEmployeesForBulk();
    
    // Set default start date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('bulk_deduction_start_date').value = today;
    
    modal.show();
}

function loadEmployeesForBulk() {
    fetch('{{ route("payroll.deductions.summary") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            allEmployeesForBulk = data.employees || [];
            renderBulkEmployeesTable(allEmployeesForBulk);
        }
    })
    .catch(error => {
        console.error('Error loading employees:', error);
    });
}

function renderBulkEmployeesTable(employees) {
    const tbody = document.getElementById('bulkEmployeesTableBody');
    if (!tbody) return;
    
    if (!employees || employees.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">No employees found</td></tr>';
        return;
    }
    
    tbody.innerHTML = employees.map(emp => {
        const isSelected = selectedEmployeesForBulk.includes(emp.id);
        return `
            <tr>
                <td>
                    <input type="checkbox" class="form-check-input bulk-employee-checkbox" 
                           value="${emp.id}" 
                           ${isSelected ? 'checked' : ''}
                           onchange="toggleEmployeeForBulk(${emp.id})">
                </td>
                <td>
                    <strong>${escapeHtml(emp.name)}</strong><br>
                    <small class="text-muted">ID: ${escapeHtml(emp.employee_id)}</small>
                </td>
                <td>${escapeHtml(emp.department)}</td>
                <td class="text-end">
                    <strong>TZS ${formatCurrency(emp.basic_salary || 0)}</strong>
                </td>
                <td class="text-end">
                    <span class="estimated-deduction-${emp.id} text-muted">-</span>
                </td>
                <td>
                    <span class="status-badge-${emp.id}">-</span>
                </td>
            </tr>
        `;
    }).join('');
}

function toggleEmployeeForBulk(employeeId) {
    const index = selectedEmployeesForBulk.indexOf(employeeId);
    if (index > -1) {
        selectedEmployeesForBulk.splice(index, 1);
    } else {
        selectedEmployeesForBulk.push(employeeId);
    }
    updateBulkSelectedCount();
    calculateBulkPreview();
}

function selectAllEmployeesForBulk() {
    selectedEmployeesForBulk = allEmployeesForBulk.map(emp => emp.id);
    document.querySelectorAll('.bulk-employee-checkbox').forEach(cb => cb.checked = true);
    updateBulkSelectedCount();
    calculateBulkPreview();
}

function deselectAllEmployeesForBulk() {
    selectedEmployeesForBulk = [];
    document.querySelectorAll('.bulk-employee-checkbox').forEach(cb => cb.checked = false);
    updateBulkSelectedCount();
    document.getElementById('bulkEmployeesTableBody').querySelectorAll('[class*="estimated-deduction"], [class*="status-badge"]').forEach(el => {
        el.textContent = '-';
    });
}

function updateBulkSelectedCount() {
    document.getElementById('bulk-selected-count').textContent = `${selectedEmployeesForBulk.length} selected`;
}

function calculateBulkPreview() {
    const deductionType = document.getElementById('bulk_deduction_type').value;
    if (!deductionType || selectedEmployeesForBulk.length === 0) {
        return;
    }
    
    fetch('{{ route("payroll.deductions.bulk.preview") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            deduction_type: deductionType,
            employee_ids: selectedEmployeesForBulk
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            data.previews.forEach(preview => {
                const estEl = document.querySelector(`.estimated-deduction-${preview.employee_id}`);
                const statusEl = document.querySelector(`.status-badge-${preview.employee_id}`);
                
                if (estEl) {
                    if (preview.applicable) {
                        estEl.textContent = `TZS ${formatCurrency(preview.deduction_amount)}`;
                        estEl.className = `estimated-deduction-${preview.employee_id} text-success fw-bold`;
                    } else {
                        estEl.textContent = 'N/A';
                        estEl.className = `estimated-deduction-${preview.employee_id} text-muted`;
                    }
                }
                
                if (statusEl) {
                    if (preview.applicable) {
                        statusEl.innerHTML = '<span class="badge bg-success">Applicable</span>';
                    } else {
                        statusEl.innerHTML = `<span class="badge bg-warning" title="${escapeHtml(preview.reason)}">Not Applicable</span>`;
                    }
                }
            });
            
            // Show calculation info
            const applicableCount = data.previews.filter(p => p.applicable).length;
            const totalAmount = data.previews.filter(p => p.applicable).reduce((sum, p) => sum + p.deduction_amount, 0);
            
            const infoEl = document.getElementById('bulk-calculation-info');
            const detailsEl = document.getElementById('bulk-calculation-details');
            
            if (infoEl && detailsEl) {
                detailsEl.innerHTML = `
                    ${applicableCount} of ${data.previews.length} employees are applicable. 
                    Total estimated deduction: <strong>TZS ${formatCurrency(totalAmount)}</strong>
                `;
                infoEl.style.display = 'block';
            }
        }
    })
    .catch(error => {
        console.error('Error calculating preview:', error);
    });
}

// Handle deduction type change to recalculate preview
document.getElementById('bulk_deduction_type')?.addEventListener('change', function() {
    calculateBulkPreview();
});

// Handle bulk deduction form submission
document.getElementById('bulkDeductionForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (selectedEmployeesForBulk.length === 0) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Please select at least one employee');
        }
        return;
    }
    
    const formData = new FormData(this);
    const data = {
        deduction_type: formData.get('deduction_type'),
        frequency: formData.get('frequency'),
        start_date: formData.get('start_date'),
        end_date: formData.get('end_date') || null,
        description: formData.get('description'),
        notes: formData.get('notes'),
        employee_ids: selectedEmployeesForBulk
    };
    
    if (typeof window.AdvancedToast !== 'undefined') {
        window.AdvancedToast.info('Processing', 'Creating bulk deductions...', { duration: 5000 });
    }
    
    fetch('{{ route("payroll.deductions.bulk") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('bulkDeductionModal'));
            modal.hide();
            
            let message = `Successfully created ${result.created_count} deduction(s)`;
            if (result.skipped_count > 0) {
                message += `. ${result.skipped_count} skipped.`;
            }
            
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', message);
            }
            
            if (result.errors && result.errors.length > 0) {
                console.log('Skipped employees:', result.errors);
            }
            
            loadDeductionsSummary();
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', result.message || 'Failed to create bulk deductions');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'An error occurred while creating bulk deductions');
        }
    });
});
</script>
@endpush

