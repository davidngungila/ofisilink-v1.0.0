@extends('layouts.app')

@section('title', 'Deduction Management - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Enhanced Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-money-withdraw me-2"></i>Deduction Management
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Manage employee salary deductions including PAYE, NSSF, NHIF, HESLB, WCF, SDL, and custom deductions
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

    <!-- Statistics Dashboard -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-group fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Employees</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $totalEmployees ?? 0 }}</h3>
                            <small class="text-muted">Active Staff</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-success" style="border-left: 4px solid #10b981 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success">
                            <i class="bx bx-check-circle fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">With Deductions</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $employeesWithDeductions ?? 0 }}</h3>
                            <small class="text-muted">Employees</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-info" style="border-left: 4px solid #3b82f6 !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-info">
                            <i class="bx bx-calendar fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Monthly Total</h6>
                            <h3 class="mb-0 fw-bold text-info">TZS {{ number_format($totalMonthlyDeductions ?? 0, 0) }}</h3>
                            <small class="text-muted">Per Month</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-warning" style="border-left: 4px solid #f59e0b !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-warning">
                            <i class="bx bx-time fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">One-Time Total</h6>
                            <h3 class="mb-0 fw-bold text-warning">TZS {{ number_format($totalOneTimeDeductions ?? 0, 0) }}</h3>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Deduction Types Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="bx bx-list-ul me-2"></i>Deduction Types Overview
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @foreach($deductionTypes ?? [] as $type)
                        <div class="col-md-3 col-6">
                            <div class="card border-0 shadow-sm h-100 hover-lift" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                <div class="card-body text-center p-3">
                                    <div class="avatar avatar-lg mx-auto mb-2 bg-primary">
                                        <i class="bx bx-money fs-3 text-white"></i>
                                    </div>
                                    <h6 class="mb-1 fw-bold">{{ $type->deduction_type }}</h6>
                                    <div class="fw-bold text-primary fs-4">{{ $type->count }}</div>
                                    <small class="text-muted d-block">Active Deductions</small>
                                    <div class="mt-2">
                                        <strong class="text-success">TZS {{ number_format($type->total, 0) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced Action Buttons -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="mb-0 text-white fw-bold">
                        <i class="bx bx-cog me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <p class="text-muted mb-0">Manage deductions efficiently with bulk operations</p>
                        </div>
                        <div class="btn-group btn-group-lg">
                            <button type="button" class="btn btn-primary shadow-sm" onclick="showAddDeductionModal()">
                                <i class="bx bx-plus me-1"></i>Add Deduction
                            </button>
                            <button type="button" class="btn btn-success shadow-sm" onclick="showBulkDeductionModal()">
                                <i class="bx bx-layer me-1"></i>Bulk Statutory Deductions
                            </button>
                            <button type="button" class="btn btn-info shadow-sm" onclick="loadDeductionsSummary()">
                                <i class="bx bx-refresh me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
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

    <!-- Enhanced Employee Deductions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <h5 class="mb-0 text-white fw-bold">
                            <i class="bx bx-table me-2"></i>Employee Deductions
                        </h5>
                        <div class="d-flex gap-2 mt-2 mt-md-0">
                            <div class="input-group" style="width: 250px;">
                                <span class="input-group-text bg-white">
                                    <i class="bx bx-search"></i>
                                </span>
                                <input type="text" class="form-control" id="searchEmployees" placeholder="Search employees...">
                            </div>
                            <select class="form-select" id="filterDepartment" style="width: 180px;">
                                <option value="">All Departments</option>
                                @foreach($employees->pluck('primaryDepartment.name')->unique()->filter() as $dept)
                                    <option value="{{ $dept }}">{{ $dept }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="deductionsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Department</th>
                                    <th class="text-end">Monthly Deductions</th>
                                    <th class="text-end">One-Time Deductions</th>
                                    <th class="text-end">Total Deductions</th>
                                    <th>Deductions Count</th>
                                    <th>Deduction Types</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="deductionsTableBody">
                                <tr>
                                    <td colspan="8" class="text-center py-4">
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
        </div>
    </div>
</div>

<!-- Include Modals and JavaScript from Partial -->
<!-- Include Modals and JavaScript from Partial -->
<!-- Add/Edit Deduction Modal -->
<div class="modal fade" id="deductionModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 10090 !important;">
    <div class="modal-dialog modal-lg" role="document" style="z-index: 10091 !important;">
        <div class="modal-content" style="z-index: 10092 !important;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="deductionModalTitle">
                    <i class="bx bx-plus me-2"></i>Add Deduction
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-list-ul me-2"></i>Employee Deductions
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="employeeDeductionsModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.hover-lift {
    transition: all 0.3s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

/* Ensure SweetAlert2 appears above all modals */
.swal2-container {
    z-index: 10100 !important;
}
.swal2-popup {
    z-index: 10101 !important;
}
.swal2-backdrop-show {
    z-index: 10099 !important;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
let deductionsData = [];
let filteredData = [];

// Load deductions summary on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDeductionsSummary();
    
    // Search functionality
    document.getElementById('searchEmployees')?.addEventListener('input', function() {
        filterTable();
    });
    
    // Department filter
    document.getElementById('filterDepartment')?.addEventListener('change', function() {
        filterTable();
    });
});

function filterTable() {
    const searchTerm = document.getElementById('searchEmployees')?.value.toLowerCase() || '';
    const departmentFilter = document.getElementById('filterDepartment')?.value || '';
    
    filteredData = deductionsData.filter(emp => {
        const matchesSearch = emp.name.toLowerCase().includes(searchTerm) || 
                            (emp.employee_id && emp.employee_id.toLowerCase().includes(searchTerm));
        const matchesDepartment = !departmentFilter || emp.department === departmentFilter;
        return matchesSearch && matchesDepartment;
    });
    
    renderDeductionsTable(filteredData);
}

function loadDeductionsSummary() {
    const tbody = document.getElementById('deductionsTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';
    
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
            filteredData = deductionsData;
            renderDeductionsTable(filteredData);
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Failed to load deductions</td></tr>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-danger">Error loading deductions</td></tr>';
    });
}

function renderDeductionsTable(employees) {
    const tbody = document.getElementById('deductionsTableBody');
    if (!tbody) return;
    
    if (!employees || employees.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><i class="bx bx-inbox fs-1 text-muted"></i><p class="text-muted mt-2 mb-0">No deductions found</p></td></tr>';
        return;
    }
    
    tbody.innerHTML = employees.map(emp => {
        const deductionTypes = emp.deductions ? [...new Set(emp.deductions.map(d => d.deduction_type))] : [];
        const typesBadges = deductionTypes.map(type => {
            const colorClass = ['PAYE', 'NSSF', 'NHIF', 'HESLB', 'WCF', 'SDL'].includes(type) 
                ? 'bg-label-primary' 
                : 'bg-label-info';
            return `<span class="badge ${colorClass} me-1 mb-1">${escapeHtml(type)}</span>`;
        }).join('');
        
        const monthlyTotal = parseFloat(emp.total_monthly_deductions) || 0;
        const oneTimeTotal = parseFloat(emp.total_one_time_deductions) || 0;
        const totalDeductions = monthlyTotal + oneTimeTotal;
        
        return `
        <tr class="employee-row">
            <td>
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-sm me-2">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            ${escapeHtml(emp.name ? emp.name.charAt(0).toUpperCase() : 'N')}
                        </span>
                    </div>
                    <div>
                        <strong>${escapeHtml(emp.name)}</strong><br>
                        <small class="text-muted">ID: ${escapeHtml(emp.employee_id || 'N/A')}</small>
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-label-secondary">${escapeHtml(emp.department || 'N/A')}</span>
            </td>
            <td class="text-end">
                <strong class="text-primary">TZS ${formatCurrency(monthlyTotal)}</strong>
            </td>
            <td class="text-end">
                <strong class="text-info">TZS ${formatCurrency(oneTimeTotal)}</strong>
            </td>
            <td class="text-end">
                <strong class="text-success fs-6">TZS ${formatCurrency(totalDeductions)}</strong>
            </td>
            <td class="text-center">
                <span class="badge bg-primary">${emp.deductions_count || 0}</span>
            </td>
            <td>
                <div class="d-flex flex-wrap gap-1">
                    ${typesBadges || '<span class="text-muted">-</span>'}
                </div>
            </td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-primary" onclick="viewEmployeeDeductions(${emp.id}, '${escapeHtml(emp.name)}')" title="View All Deductions">
                        <i class="bx bx-show"></i>
                    </button>
                    <button type="button" class="btn btn-outline-success" onclick="showAddDeductionModal(${emp.id})" title="Add Deduction">
                        <i class="bx bx-plus"></i>
                    </button>
                </div>
            </td>
        </tr>
    `;
    }).join('');
}

function formatCurrency(amount) {
    return (Number(amount) || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Deduction Management Functions
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
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Deduction deleted successfully',
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            });
            loadDeductionsSummary();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to delete deduction',
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            });
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
    
    if (calculationMethod === 'fixed') {
        amount = parseFloat(formData.get('amount')) || 0;
    } else if (calculationMethod === 'percentage') {
        const percentage = parseFloat(formData.get('percentage')) || 0;
        amount = percentage;
    } else if (calculationMethod === 'statutory') {
        amount = 0;
    }
    
    if (amount <= 0 && calculationMethod !== 'statutory') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please enter a valid amount or percentage',
            customClass: {
                container: 'swal2-container',
                popup: 'swal2-popup'
            }
        });
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
            
            // Wait for modal to fully close and backdrop to be removed
            setTimeout(() => {
                // Remove any remaining backdrop
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: result.message || 'Deduction saved successfully',
                    customClass: {
                        container: 'swal2-container',
                        popup: 'swal2-popup'
                    }
                });
            }, 300);
            
            loadDeductionsSummary();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to save deduction',
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while saving the deduction',
            customClass: {
                container: 'swal2-container',
                popup: 'swal2-popup'
            }
        });
    });
});

// Bulk Deduction Operations
let allEmployeesForBulk = [];
let selectedEmployeesForBulk = [];

function showBulkDeductionModal() {
    const modal = new bootstrap.Modal(document.getElementById('bulkDeductionModal'));
    const form = document.getElementById('bulkDeductionForm');
    
    form.reset();
    selectedEmployeesForBulk = [];
    document.getElementById('bulk-selected-count').textContent = '0 selected';
    
    loadEmployeesForBulk();
    
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

document.getElementById('bulk_deduction_type')?.addEventListener('change', function() {
    calculateBulkPreview();
});

document.getElementById('bulkDeductionForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (selectedEmployeesForBulk.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please select at least one employee',
            customClass: {
                container: 'swal2-container',
                popup: 'swal2-popup'
            }
        });
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
    
    Swal.fire({
        title: 'Processing',
        text: 'Creating bulk deductions...',
        allowOutsideClick: false,
        customClass: {
            container: 'swal2-container',
            popup: 'swal2-popup'
        },
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
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
            
            // Wait for modal to fully close and backdrop to be removed
            setTimeout(() => {
                // Remove any remaining backdrop
                document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
                
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: message,
                    customClass: {
                        container: 'swal2-container',
                        popup: 'swal2-popup'
                    }
                });
            }, 300);
            
            loadDeductionsSummary();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: result.message || 'Failed to create bulk deductions',
                customClass: {
                    container: 'swal2-container',
                    popup: 'swal2-popup'
                }
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred while creating bulk deductions',
            customClass: {
                container: 'swal2-container',
                popup: 'swal2-popup'
            }
        });
    });
});

function toggleDeductionCalculationMethod() {
    const method = document.getElementById('deduction_calculation_method').value;
    const fixedAmountDiv = document.getElementById('deduction-fixed-amount');
    const percentageDiv = document.getElementById('deduction-percentage-amount');
    const statutoryInfo = document.getElementById('deduction-statutory-info');
    
    if (method === 'fixed') {
        fixedAmountDiv.style.display = 'block';
        percentageDiv.style.display = 'none';
        statutoryInfo.style.display = 'none';
    } else if (method === 'percentage') {
        fixedAmountDiv.style.display = 'none';
        percentageDiv.style.display = 'block';
        statutoryInfo.style.display = 'none';
    } else if (method === 'statutory') {
        fixedAmountDiv.style.display = 'none';
        percentageDiv.style.display = 'none';
        statutoryInfo.style.display = 'block';
    }
}
</script>
@endpush
@endsection

