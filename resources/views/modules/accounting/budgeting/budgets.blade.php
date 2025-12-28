@extends('layouts.app')

@section('title', 'Budgets Management')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Budgeting & Forecasting - Budgets</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="total"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="budgeted"] {
        border-left-color: #17a2b8 !important;
    }

    .summary-card[data-type="actual"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="variance"] {
        border-left-color: #ffc107 !important;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }

    .sortable {
        cursor: pointer;
        user-select: none;
    }

    .sortable:hover {
        background-color: #f8f9fa;
    }

    .sortable i {
        opacity: 0.3;
        transition: opacity 0.2s;
    }

    .sortable:hover i {
        opacity: 1;
    }

    .sortable.active i {
        opacity: 1;
        color: #007bff;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .variance-positive { color: #28a745; font-weight: bold; }
    .variance-negative { color: #dc3545; font-weight: bold; }
    .variance-zero { color: #6c757d; }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in {
        animation: fadeIn 0.3s ease;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm mb-4" style="background:#940000;">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-calculator me-2"></i>Budgeting & Forecasting - Budgets
                    </h2>
                    <p class="mb-0 opacity-90">Manage budgets, track actuals, and analyze variances</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button class="btn btn-light btn-sm" id="btn-export-excel" title="Export Excel">
                        <i class="bx bxs-file-excel me-1"></i>Excel
                    </button>
                    <a class="btn btn-secondary btn-sm" id="btn-export" target="_blank" title="Export PDF">
                        <i class="bx bxs-file-pdf me-1"></i>PDF
                    </a>
                    <button class="btn btn-light btn-sm" id="btn-refresh" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                    <button class="btn btn-light btn-sm" onclick="openBudgetModal()" title="New Budget">
                        <i class="bx bx-plus me-1"></i>New Budget
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="total">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Budgets</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumTotal">0</h3>
                            <small class="text-muted" id="sumApproved">0 Approved</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-list-ul fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="budgeted">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Budgeted</h6>
                            <h3 class="mb-0 text-info fw-bold" id="sumBudgeted">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-money fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="actual">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Actual</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumActual">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-trending-up fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="variance">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Variance</h6>
                            <h3 class="mb-0 fw-bold" id="sumVariance">0.00</h3>
                            <small class="text-muted" id="sumVarianceText">-</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-equalizer fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-filter-alt me-2"></i>Filters & Search
                </h6>
                <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" id="toggleFilters">
                    <i class="bx bx-chevron-up" id="filterIcon"></i>
                </button>
            </div>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterSearch" placeholder="Budget name, fiscal year...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Clear search">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-semibold">Fiscal Year</label>
                        <select class="form-select form-select-sm" id="filterFiscalYear">
                            <option value="">All Years</option>
                            @foreach($fiscalYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-semibold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="Draft">Draft</option>
                            <option value="Approved">Approved</option>
                            <option value="Active">Active</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-semibold">Budget Type</label>
                        <select class="form-select form-select-sm" id="filterBudgetType">
                            <option value="">All Types</option>
                            <option value="Annual">Annual</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Monthly">Monthly</option>
                            <option value="Custom">Custom</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Department</label>
                        <select class="form-select form-select-sm" id="filterDepartment">
                            <option value="">All Departments</option>
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-table me-2"></i>Budgets
                    <span class="badge bg-primary ms-2" id="budgetCount">0</span>
                </h6>
                <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
                    <label class="small text-muted me-2">Per page:</label>
                    <select class="form-select form-select-sm" style="width: auto;" id="perPageSelect">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="budgetsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="budget_name">
                                Budget Name <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="budget_type">
                                Type <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="fiscal_year">
                                Fiscal Year <i class="bx bx-sort"></i>
                            </th>
                            <th>Period</th>
                            <th class="text-end sortable" data-sort="total_budgeted">
                                Budgeted (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="total_actual">
                                Actual (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="total_variance">
                                Variance (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="budgetsTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading budgets...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 budgets
                </div>
                <nav aria-label="Budgets pagination">
                    <ul class="pagination pagination-sm mb-0" id="pagination">
                        <!-- Pagination will be generated by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Budget Modal (Preserved from original) -->
<div class="modal fade" id="budgetModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="budgetModalTitle">New Budget</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="budgetForm">
                <input type="hidden" id="budgetId" name="id">
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Budget Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="budgetName" name="budget_name" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Budget Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="budgetType" name="budget_type" required onchange="updateDateRange()">
                                <option value="">Select Type</option>
                                <option value="Annual">Annual</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fiscal Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="fiscalYear" name="fiscal_year" value="{{ date('Y') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="startDate" name="start_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="endDate" name="end_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Department</label>
                            <select class="form-select" id="departmentId" name="department_id">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Budget Items</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addBudgetItem()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>
                    <div id="budgetItems">
                        <!-- Items will be added here -->
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="budgetNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Budget
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Advanced Budgets Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.budgeting.budgets.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.budgeting.budgets') }}';
    let page = 1, perPage = 20;
    let sortColumn = 'fiscal_year', sortDirection = 'desc';
    let allBudgets = [];
    const token = '{{ csrf_token() }}';
    let itemCount = 0;
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            search: document.getElementById('filterSearch')?.value || '',
            fiscal_year: document.getElementById('filterFiscalYear')?.value || '',
            status: document.getElementById('filterStatus')?.value || '',
            budget_type: document.getElementById('filterBudgetType')?.value || '',
            department_id: document.getElementById('filterDepartment')?.value || '',
            page, per_page: perPage
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const total = summary?.total_budgets || 0;
        const approved = summary?.approved_budgets || 0;
        const budgeted = summary?.total_budgeted || 0;
        const actual = summary?.total_actual || 0;
        const variance = summary?.total_variance || 0;
        
        animateValue('sumTotal', 0, total, 800);
        animateValue('sumBudgeted', 0, budgeted, 800);
        animateValue('sumActual', 0, actual, 800);
        animateValue('sumVariance', 0, Math.abs(variance), 800);
        
        if(document.getElementById('sumApproved')) document.getElementById('sumApproved').textContent = `${approved} Approved`;
        if(document.getElementById('sumVarianceText')) {
            const varianceText = document.getElementById('sumVarianceText');
            const varianceEl = document.getElementById('sumVariance');
            if(variance < 0) {
                varianceEl.className = 'mb-0 text-success fw-bold';
                varianceText.textContent = 'Under Budget';
            } else if(variance > 0) {
                varianceEl.className = 'mb-0 text-danger fw-bold';
                varianceText.textContent = 'Over Budget';
            } else {
                varianceEl.className = 'mb-0 text-secondary fw-bold';
                varianceText.textContent = 'On Budget';
            }
        }
        if(document.getElementById('budgetCount')) document.getElementById('budgetCount').textContent = total.toLocaleString();
    }
    
    function animateValue(id, start, end, duration){
        const element = document.getElementById(id);
        if(!element) return;
        const startVal = parseFloat(element.textContent.replace(/[^0-9.-]/g, '')) || start;
        let current = startVal;
        const range = end - startVal;
        const increment = range / (duration / 16);
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                if(id.includes('Total') && !id.includes('Budgeted') && !id.includes('Actual') && !id.includes('Variance')) {
                    element.textContent = Math.round(end).toLocaleString();
                } else {
                    element.textContent = formatCurrency(end);
                }
                clearInterval(timer);
            } else {
                if(id.includes('Total') && !id.includes('Budgeted') && !id.includes('Actual') && !id.includes('Variance')) {
                    element.textContent = Math.round(current).toLocaleString();
                } else {
                    element.textContent = formatCurrency(current);
                }
            }
        }, 16);
    }
    
    function escapeHtml(s){ 
        return (s||'').replace(/[&<>"']/g, m => ({
            '&':'&amp;',
            '<':'&lt;',
            '>':'&gt;',
            '"':'&quot;',
            '\'':'&#39;'
        }[m])); 
    }
    
    function getVarianceClass(variance){
        if(Math.abs(variance) < 0.01) return 'variance-zero';
        return variance < 0 ? 'variance-positive' : 'variance-negative';
    }
    
    function getStatusBadge(status){
        const badges = {
            'Approved': 'bg-success',
            'Active': 'bg-primary',
            'Draft': 'bg-secondary'
        };
        return badges[status] || 'bg-secondary';
    }
    
    function renderTable(budgets){
        const tbody = document.getElementById('budgetsTableBody');
        if(!tbody) return;
        
        if(!budgets || !budgets.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No budgets found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = budgets.map((b, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.01}s">
                <td>
                    <div class="fw-medium">${escapeHtml(b.budget_name || 'N/A')}</div>
                    ${b.department_name ? `<small class="text-muted">${escapeHtml(b.department_name)}</small>` : ''}
                </td>
                <td><span class="badge bg-info">${escapeHtml(b.budget_type || '')}</span></td>
                <td><strong>${escapeHtml(b.fiscal_year || '')}</strong></td>
                <td>
                    <small>
                        ${escapeHtml(b.start_date_display || '')}<br>
                        to ${escapeHtml(b.end_date_display || '')}
                    </small>
                </td>
                <td class="text-end">
                    <strong>${formatCurrency(b.total_budgeted)}</strong>
                </td>
                <td class="text-end">
                    <strong class="text-success">${formatCurrency(b.total_actual)}</strong>
                </td>
                <td class="text-end">
                    <strong class="${getVarianceClass(b.total_variance)}">
                        ${b.total_variance >= 0 ? '' : '-'}${formatCurrency(Math.abs(b.total_variance))}
                    </strong>
                    <br><small class="text-muted">${b.variance_percent >= 0 ? '' : '-'}${Math.abs(b.variance_percent)}%</small>
                </td>
                <td>
                    <span class="badge ${getStatusBadge(b.status)}">${escapeHtml(b.status || '')}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" onclick="viewBudget(${b.id})" title="View">
                            <i class="bx bx-show"></i>
                        </button>
                        ${b.status !== 'Approved' ? `
                        <button class="btn btn-outline-warning" onclick="editBudget(${b.id})" title="Edit">
                            <i class="bx bx-edit"></i>
                        </button>
                        ` : ''}
                        ${b.status === 'Draft' ? `
                        <button class="btn btn-outline-success" onclick="approveBudget(${b.id})" title="Approve">
                            <i class="bx bx-check"></i>
                        </button>
                        ` : ''}
                        <button class="btn btn-outline-primary" onclick="updateActuals(${b.id})" title="Update Actuals">
                            <i class="bx bx-refresh"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalBudgets){
        const totalPages = Math.ceil(totalBudgets / perPage);
        const pagination = document.getElementById('pagination');
        if(!pagination) return;
        
        if(totalPages <= 1){
            pagination.innerHTML = '';
            return;
        }
        
        let html = '';
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>`;
        
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);
        
        if(startPage > 1){
            html += `<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>`;
            if(startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        
        for(let i = startPage; i <= endPage; i++){
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        }
        
        if(endPage < totalPages){
            if(endPage < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a></li>`;
        }
        
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>`;
        
        pagination.innerHTML = html;
        
        pagination.querySelectorAll('a[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                page = parseInt(link.dataset.page);
                load();
            });
        });
    }
    
    function load(){
        showLoading(true);
        const body = document.getElementById('budgetsTableBody');
        if(!body) return;
        
        body.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading budgets...</p>
                </td>
            </tr>
        `;
        
        fetch(endpoint, {
            method:'POST', 
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}',
                'Accept':'application/json'
            }, 
            body: JSON.stringify(qs())
        })
        .then(async r=>{
            const text = await r.text();
            let res;
            try { 
                res = JSON.parse(text); 
            } catch(e) { 
                console.error('JSON parse error:', text); 
                throw new Error('Invalid response from server'); 
            }
            
            if(!r.ok || !res.success){ 
                let msg = res.message || 'Failed to load budgets';
                
                if (res.errors) {
                    const errorMessages = [];
                    Object.keys(res.errors).forEach(key => {
                        if (Array.isArray(res.errors[key])) {
                            res.errors[key].forEach(err => {
                                errorMessages.push(`${key}: ${err}`);
                            });
                        } else {
                            errorMessages.push(`${key}: ${res.errors[key]}`);
                        }
                    });
                    if (errorMessages.length > 0) {
                        msg = 'Validation Errors:<br>' + errorMessages.join('<br>');
                    }
                }
                
                const errorHtml = msg.includes('<br>') ? msg : escapeHtml(msg);
                body.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <div class="mt-2">${errorHtml}</div>
                        </td>
                    </tr>
                `;
                updateSummary({ 
                    total_budgets: 0, approved_budgets: 0, total_budgeted: 0, 
                    total_actual: 0, total_variance: 0 
                });
                if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = '0 budgets';
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            allBudgets = res.budgets || [];
            updateSummary(res.summary || {});
            if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, res.summary?.count || 0)} of ${(res.summary?.count || 0).toLocaleString()} budgets`;
            
            // Client-side sorting
            allBudgets.sort((a, b) => {
                let aVal = a[sortColumn];
                let bVal = b[sortColumn];
                
                if(['total_budgeted', 'total_actual', 'total_variance'].includes(sortColumn)){
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                }
                
                if(sortColumn === 'fiscal_year'){
                    aVal = parseInt(aVal) || 0;
                    bVal = parseInt(bVal) || 0;
                }
                
                if(sortDirection === 'asc'){
                    return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                } else {
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                }
            });
            
            renderTable(allBudgets);
            renderPagination(page, res.summary?.count || 0);
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            if(body) {
                body.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                        </td>
                    </tr>
                `;
            }
            updateSummary({ 
                total_budgets: 0, approved_budgets: 0, total_budgeted: 0, 
                total_actual: 0, total_variance: 0 
            });
        })
        .finally(() => {
            showLoading(false);
        });
    }
    
    // Event Listeners
    if(document.getElementById('btn-refresh')) {
        document.getElementById('btn-refresh').addEventListener('click', () => { 
            page = 1; 
            load(); 
        });
    }
    
    if(document.getElementById('btn-export')) {
        document.getElementById('btn-export').addEventListener('click', (e) => {
            e.preventDefault();
            const params = qs();
            const p = new URLSearchParams();
            Object.keys(params).forEach(key => {
                if(params[key] && key !== 'page' && key !== 'per_page') {
                    p.append(key, params[key]);
                }
            });
            p.append('export', 'pdf');
            window.open(pdfEndpoint + '?' + p.toString(), '_blank');
        });
    }
    
    if(document.getElementById('btn-export-excel')) {
        document.getElementById('btn-export-excel').addEventListener('click', () => {
            const params = qs();
            const p = new URLSearchParams();
            Object.keys(params).forEach(key => {
                if(params[key] && key !== 'page' && key !== 'per_page') {
                    p.append(key, params[key]);
                }
            });
            p.append('export', 'excel');
            window.location.href = pdfEndpoint + '?' + p.toString();
        });
    }
    
    ['filterSearch', 'filterFiscalYear', 'filterStatus', 'filterBudgetType', 'filterDepartment'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('change', () => { page = 1; load(); });
    });
    
    if(document.getElementById('filterSearch')) {
        document.getElementById('filterSearch').addEventListener('input', () => { 
            page = 1; 
            debounce(load, 300)(); 
        });
    }
    
    if(document.getElementById('clearSearch')) {
        document.getElementById('clearSearch').addEventListener('click', () => {
            if(document.getElementById('filterSearch')) document.getElementById('filterSearch').value = '';
            page = 1;
            load();
        });
    }
    
    if(document.getElementById('perPageSelect')) {
        document.getElementById('perPageSelect').addEventListener('change', function(){
            perPage = parseInt(this.value);
            page = 1;
            load();
        });
    }
    
    // Sorting
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function(){
            const col = this.dataset.sort;
            
            if(sortColumn === col){
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn = col;
                sortDirection = 'desc';
            }
            
            document.querySelectorAll('.sortable').forEach(t => {
                t.classList.remove('active');
                const icon = t.querySelector('i');
                if(icon) icon.className = 'bx bx-sort';
            });
            
            this.classList.add('active');
            const icon = this.querySelector('i');
            if(icon) icon.className = sortDirection === 'asc' ? 'bx bx-sort-up' : 'bx bx-sort-down';
            
            renderTable(allBudgets);
        });
    });
    
    // Filter collapse toggle
    if(document.getElementById('toggleFilters')) {
        document.getElementById('toggleFilters').addEventListener('click', function(){
            const icon = document.getElementById('filterIcon');
            if(icon) {
                const isCollapsed = document.getElementById('filterCollapse')?.classList.contains('show');
                icon.className = isCollapsed ? 'bx bx-chevron-down' : 'bx bx-chevron-up';
            }
        });
    }
    
    let t = null;
    function debounce(fn, ms){ 
        return () => { 
            clearTimeout(t); 
            t = setTimeout(fn, ms); 
        }; 
    }
    
    // Budget Modal Functions (Preserved from original)
    window.openBudgetModal = function() {
        const form = document.getElementById('budgetForm');
        if (form) {
            form.reset();
            document.getElementById('budgetId').value = '';
            document.getElementById('budgetModalTitle').textContent = 'New Budget';
            document.getElementById('fiscalYear').value = new Date().getFullYear();
            document.getElementById('budgetItems').innerHTML = '';
            itemCount = 0;
            addBudgetItem();
        }
        
        const modal = new bootstrap.Modal(document.getElementById('budgetModal'));
        modal.show();
    };
    
    window.updateDateRange = function() {
        const type = document.getElementById('budgetType').value;
        const year = parseInt(document.getElementById('fiscalYear').value) || new Date().getFullYear();
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        
        if (type === 'Annual') {
            startDate.value = year + '-01-01';
            endDate.value = year + '-12-31';
        } else if (type === 'Quarterly') {
            const quarter = Math.floor((new Date().getMonth()) / 3);
            const startMonth = quarter * 3;
            startDate.value = year + '-' + String(startMonth + 1).padStart(2, '0') + '-01';
            const endMonth = startMonth + 3;
            const lastDay = new Date(year, endMonth, 0).getDate();
            endDate.value = year + '-' + String(endMonth).padStart(2, '0') + '-' + String(lastDay).padStart(2, '0');
        } else if (type === 'Monthly') {
            const month = new Date().getMonth() + 1;
            startDate.value = year + '-' + String(month).padStart(2, '0') + '-01';
            const lastDay = new Date(year, month, 0).getDate();
            endDate.value = year + '-' + String(month).padStart(2, '0') + '-' + String(lastDay).padStart(2, '0');
        }
    };
    
    window.addBudgetItem = function() {
        const container = document.getElementById('budgetItems');
        const itemHtml = `
            <div class="budget-item border rounded p-3 mb-3" data-index="${itemCount}">
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label small">Account <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" name="items[${itemCount}][account_id]" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Budgeted Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemCount}][budgeted_amount]" value="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeBudgetItem(this)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', itemHtml);
        itemCount++;
    };
    
    window.removeBudgetItem = function(btn) {
        const item = btn.closest('.budget-item');
        if (item && document.getElementById('budgetItems').children.length > 1) {
            item.remove();
        }
    };
    
    window.viewBudget = function(id) {
        window.location.href = '{{ route("modules.accounting.budgeting.budgets") }}?budget_id=' + id;
    };
    
    window.editBudget = function(id) {
        fetch(`{{ url('/modules/accounting/budgeting/budgets') }}/${id}`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.budget) {
                const b = data.budget;
                document.getElementById('budgetId').value = b.id;
                document.getElementById('budgetName').value = b.budget_name || '';
                document.getElementById('budgetType').value = b.budget_type || '';
                document.getElementById('fiscalYear').value = b.fiscal_year || '';
                document.getElementById('startDate').value = b.start_date || '';
                document.getElementById('endDate').value = b.end_date || '';
                document.getElementById('departmentId').value = b.department_id || '';
                document.getElementById('budgetNotes').value = b.notes || '';
                document.getElementById('budgetModalTitle').textContent = 'Edit Budget';
                
                document.getElementById('budgetItems').innerHTML = '';
                itemCount = 0;
                if (b.items) {
                    b.items.forEach(item => {
                        addBudgetItem();
                        const lastItem = document.getElementById('budgetItems').lastElementChild;
                        lastItem.querySelector('select[name*="[account_id]"]').value = item.account_id || '';
                        lastItem.querySelector('input[name*="[budgeted_amount]"]').value = item.budgeted_amount || 0;
                    });
                }
                
                const modal = new bootstrap.Modal(document.getElementById('budgetModal'));
                modal.show();
            }
        });
    };
    
    window.approveBudget = function(id) {
        if (!confirm('Are you sure you want to approve this budget?')) return;
        
        fetch(`{{ url('/modules/accounting/budgeting/budgets') }}/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', data.message || 'Budget approved successfully');
                } else {
                    alert(data.message || 'Budget approved successfully');
                }
                setTimeout(() => load(), 1500);
            } else {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', data.message || 'Error approving budget');
                } else {
                    alert(data.message || 'Error approving budget');
                }
            }
        });
    };
    
    window.updateActuals = function(id) {
        fetch(`{{ url('/modules/accounting/budgeting/budgets') }}/${id}/update-actuals`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', data.message || 'Actuals updated successfully');
                } else {
                    alert(data.message || 'Actuals updated successfully');
                }
                setTimeout(() => load(), 1500);
            } else {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', data.message || 'Error updating actuals');
                } else {
                    alert(data.message || 'Error updating actuals');
                }
            }
        });
    };
    
    window.exportBudgetsPdf = function() {
        const params = qs();
        const p = new URLSearchParams();
        Object.keys(params).forEach(key => {
            if(params[key] && key !== 'page' && key !== 'per_page') {
                p.append(key, params[key]);
            }
        });
        p.append('export', 'pdf');
        window.open(pdfEndpoint + '?' + p.toString(), '_blank');
    };
    
    window.exportBudgetsExcel = function() {
        const params = qs();
        const p = new URLSearchParams();
        Object.keys(params).forEach(key => {
            if(params[key] && key !== 'page' && key !== 'per_page') {
                p.append(key, params[key]);
            }
        });
        p.append('export', 'excel');
        window.location.href = pdfEndpoint + '?' + p.toString();
    };
    
    // Budget Form Submission
    if(document.getElementById('budgetForm')) {
        document.getElementById('budgetForm').addEventListener('submit', function(e){
            e.preventDefault();
            const formData = new FormData(this);
            const budgetId = document.getElementById('budgetId').value;
            const url = budgetId 
                ? `{{ url('/modules/accounting/budgeting/budgets') }}/${budgetId}`
                : '{{ route("modules.accounting.budgeting.budgets.store") }}';
            const method = budgetId ? 'PUT' : 'POST';
            
            fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.success('Success', data.message || 'Budget saved successfully');
                    } else {
                        alert(data.message || 'Budget saved successfully');
                    }
                    bootstrap.Modal.getInstance(document.getElementById('budgetModal')).hide();
                    setTimeout(() => load(), 1500);
                } else {
                    let msg = data.message || 'Error saving budget';
                    if (data.errors) {
                        const errors = [];
                        Object.keys(data.errors).forEach(key => {
                            if (Array.isArray(data.errors[key])) {
                                data.errors[key].forEach(err => errors.push(`${key}: ${err}`));
                            } else {
                                errors.push(`${key}: ${data.errors[key]}`);
                            }
                        });
                        if (errors.length > 0) msg = errors.join(' | ');
                    }
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.error('Error', msg);
                    } else {
                        alert(msg);
                    }
                }
            })
            .catch(err => {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', err.message || 'Network error');
                } else {
                    alert(err.message || 'Network error');
                }
            });
        });
    }
    
    // Make load function globally accessible
    window.loadBudgets = function(resetPage = false) {
        if (resetPage) {
            page = 1;
        }
        load();
    };
    
    // Initialize
    load();
})();
</script>
@endpush
