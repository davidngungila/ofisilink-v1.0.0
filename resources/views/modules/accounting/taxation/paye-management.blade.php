@extends('layouts.app')

@section('title', 'PAYE Management')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">PAYE Management</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="paye"] {
        border-left-color: #667eea !important;
    }

    .summary-card[data-type="employees"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="gross"] {
        border-left-color: #17a2b8 !important;
    }

    .summary-card[data-type="payrolls"] {
        border-left-color: #ffc107 !important;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }

    .paye-bracket-card {
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }

    .paye-bracket-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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
                        <i class="bx bx-user-check me-2"></i>PAYE (Pay As You Earn) Management
                    </h2>
                    <p class="mb-0 opacity-90">Manage and track payroll tax calculations</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <a href="{{ route('modules.accounting.taxation.index') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-cog me-1"></i>Tax Settings
                    </a>
                    <a href="{{ route('modules.accounting.taxation.reports') }}" class="btn btn-light btn-sm">
                        <i class="bx bx-bar-chart-alt-2 me-1"></i>Tax Reports
                    </a>
                    <button class="btn btn-light btn-sm" id="btn-refresh" title="Refresh">
                        <i class="bx bx-refresh"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold">
                <i class="bx bx-calendar me-2"></i>Report Period
            </h6>
            </div>
            <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-semibold">Start Date</label>
                    <input type="date" class="form-control form-control-sm" id="filterStartDate" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                        <div class="col-md-4">
                    <label class="form-label small text-muted fw-semibold">End Date</label>
                    <input type="date" class="form-control form-control-sm" id="filterEndDate" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                    <label class="form-label small text-muted fw-semibold">&nbsp;</label>
                    <button class="btn btn-primary btn-sm w-100" id="btn-generate">
                        <i class="bx bx-calculator me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4" id="summaryCards" style="display: none;">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="paye">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total PAYE</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumPaye">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-money fs-4 text-primary"></i>
                        </div>
                    </div>
                                    </div>
                                </div>
                            </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="employees">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Employees</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumEmployees">0</h3>
                            <small class="text-muted">Employees</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-user fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
                            </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="gross">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Gross Salary</h6>
                            <h3 class="mb-0 text-info fw-bold" id="sumGross">0.00</h3>
                            <small class="text-muted">TZS</small>
                                                    </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-wallet fs-4 text-info"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card fade-in" data-type="payrolls">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Payroll Periods</h6>
                            <h3 class="mb-0 text-warning fw-bold" id="sumPayrolls">0</h3>
                            <small class="text-muted">Periods</small>
                                </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-calendar fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYE Tax Brackets -->
    <div class="card border-0 shadow-sm mb-4" id="bracketsCard" style="display: none;">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold">
                <i class="bx bx-percent me-2"></i>Tanzania PAYE Tax Brackets
            </h6>
        </div>
        <div class="card-body">
            <div class="row" id="bracketsContainer">
                <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- PAYE Breakdown by Payroll -->
    <div class="card border-0 shadow-sm mb-4" id="breakdownCard" style="display: none;">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold">
                <i class="bx bx-calendar-alt me-2"></i>PAYE Breakdown by Payroll Period
            </h6>
                            </div>
        <div class="card-body p-0">
                                <div class="table-responsive">
                <table class="table table-hover mb-0" id="breakdownTable">
                    <thead class="table-light">
                                            <tr>
                                                <th>Pay Period</th>
                                                <th>Payroll ID</th>
                                                <th>Employees</th>
                            <th class="text-end">Gross Salary</th>
                            <th class="text-end">PAYE Amount</th>
                            <th class="text-end">Net Salary</th>
                                            </tr>
                                        </thead>
                    <tbody id="breakdownTableBody">
                        <!-- Will be populated by JavaScript -->
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3">Total</th>
                            <th class="text-end" id="footGross">0.00</th>
                            <th class="text-end" id="footPaye">0.00</th>
                            <th class="text-end" id="footNet">0.00</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

    <!-- Employee-Level PAYE Details -->
    <div class="card border-0 shadow-sm" id="employeeCard" style="display: none;">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-user me-2"></i>Employee-Level PAYE Details
                </h6>
                <div class="d-flex gap-2 align-items-center">
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
                <table class="table table-hover mb-0" id="employeeTable">
                    <thead class="table-light">
                                            <tr>
                                                <th>Employee</th>
                                                <th>Payrolls</th>
                            <th class="text-end">Total Gross</th>
                            <th class="text-end">Total PAYE</th>
                            <th class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                    <tbody id="employeeTableBody">
                        <!-- Will be populated by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 employees
                </div>
                <nav aria-label="Employee PAYE pagination">
                    <ul class="pagination pagination-sm mb-0" id="pagination">
                        <!-- Pagination will be generated by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Loading/Empty State -->
    <div id="emptyState" class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bx bx-user-check fs-1 text-muted"></i>
            <p class="text-muted mt-2 mb-0">Select date range and click "Generate Report" to view PAYE management data</p>
        </div>
    </div>
</div>

<!-- Employee PAYE Details Modal -->
<div class="modal fade" id="employeePayeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Employee PAYE Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="employeePayeModalBody">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
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
// Advanced PAYE Management Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.taxation.paye-management.data') }}';
    let page = 1, perPage = 20;
    let allEmployees = [];
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            start_date: document.getElementById('filterStartDate')?.value || '{{ now()->startOfMonth()->format('Y-m-d') }}',
            end_date: document.getElementById('filterEndDate')?.value || '{{ now()->endOfMonth()->format('Y-m-d') }}'
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const paye = summary?.total_paye || 0;
        const employees = summary?.total_employees || 0;
        const gross = summary?.total_gross_salary || 0;
        const payrolls = summary?.payroll_count || 0;
        
        animateValue('sumPaye', 0, paye, 800);
        animateValue('sumEmployees', 0, employees, 800);
        animateValue('sumGross', 0, gross, 800);
        animateValue('sumPayrolls', 0, payrolls, 800);
        
        if(document.getElementById('footGross')) document.getElementById('footGross').textContent = formatCurrency(gross);
        if(document.getElementById('footPaye')) document.getElementById('footPaye').textContent = formatCurrency(paye);
        if(document.getElementById('footNet')) document.getElementById('footNet').textContent = formatCurrency(summary?.total_net_salary || 0);
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
                if(id.includes('Employees') || id.includes('Payrolls')) {
                    element.textContent = Math.round(end).toLocaleString();
                } else {
                    element.textContent = formatCurrency(end);
                }
                clearInterval(timer);
            } else {
                if(id.includes('Employees') || id.includes('Payrolls')) {
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
    
    function renderBrackets(brackets){
        const container = document.getElementById('bracketsContainer');
        if(!container) return;
        
        if(!brackets || !brackets.length){
            container.innerHTML = '<div class="col-12"><p class="text-muted text-center">No tax brackets available</p></div>';
            return;
        }
        
        container.innerHTML = brackets.map((bracket, idx) => `
            <div class="col-md-6 mb-3 fade-in" style="animation-delay: ${idx * 0.05}s">
                <div class="card paye-bracket-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>
                                    ${formatCurrency(bracket.min || 0)} 
                                    ${bracket.max && bracket.max < 999999999 ? ' - ' + formatCurrency(bracket.max) : '+'}
                                </strong>
                            </div>
                            <div>
                                <span class="badge bg-primary">${((bracket.rate || 0) * 100).toFixed(0)}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    function renderBreakdown(breakdown){
        const tbody = document.getElementById('breakdownTableBody');
        if(!tbody) return;
        
        if(!breakdown || !breakdown.length){
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-3 text-muted">No PAYE breakdown available</td></tr>';
            return;
        }
        
        tbody.innerHTML = breakdown.map((b, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.02}s">
                <td>${escapeHtml(b.pay_period || '')}</td>
                <td><code class="text-primary">#${b.payroll_id || ''}</code></td>
                <td>${(b.employee_count || 0).toLocaleString()}</td>
                <td class="text-end">${formatCurrency(b.gross_salary || 0)}</td>
                <td class="text-end">
                    <strong class="text-success">${formatCurrency(b.paye_amount || 0)}</strong>
                </td>
                <td class="text-end">${formatCurrency(b.net_salary || 0)}</td>
            </tr>
        `).join('');
    }
    
    function renderEmployees(employees){
        const tbody = document.getElementById('employeeTableBody');
        if(!tbody) return;
        
        if(!employees || !employees.length){
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-5"><i class="bx bx-inbox fs-1 text-muted"></i><p class="text-muted mt-2 mb-0">No employee PAYE details found</p></td></tr>';
            return;
        }
        
        tbody.innerHTML = employees.map((emp, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.01}s">
                <td>
                    <div class="fw-medium">${escapeHtml(emp.employee_name || 'N/A')}</div>
                    ${emp.employee_email ? `<small class="text-muted">${escapeHtml(emp.employee_email)}</small>` : ''}
                </td>
                <td>${(emp.payroll_count || 0).toLocaleString()}</td>
                <td class="text-end">${formatCurrency(emp.total_gross || 0)}</td>
                <td class="text-end">
                    <strong class="text-success">${formatCurrency(emp.total_paye || 0)}</strong>
                </td>
                <td class="text-center">
                    <button class="btn btn-outline-info btn-sm" onclick="viewEmployeePayeDetails(${emp.employee_id || 0}, ${JSON.stringify(emp.items || []).replace(/"/g, '&quot;')})" title="View Details">
                        <i class="bx bx-show"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalEmployees){
        const totalPages = Math.ceil(totalEmployees / perPage);
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
                renderEmployees(allEmployees);
                renderPagination(page, allEmployees.length);
                if(document.getElementById('rowsInfo')) {
                    document.getElementById('rowsInfo').textContent = 
                        `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, allEmployees.length)} of ${allEmployees.length.toLocaleString()} employees`;
                }
            });
        });
    }
    
    function load(){
        showLoading(true);
        document.getElementById('emptyState').style.display = 'none';
        document.getElementById('summaryCards').style.display = 'none';
        document.getElementById('bracketsCard').style.display = 'none';
        document.getElementById('breakdownCard').style.display = 'none';
        document.getElementById('employeeCard').style.display = 'none';
        
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
                let msg = res.message || 'Failed to load PAYE management data';
                
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
                
                document.getElementById('emptyState').innerHTML = `
                    <div class="card-body text-danger text-center py-5">
                        <i class="bx bx-error-circle fs-1"></i>
                        <div class="mt-2">${msg.includes('<br>') ? msg : escapeHtml(msg)}</div>
                    </div>
                `;
                document.getElementById('emptyState').style.display = 'block';
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            document.getElementById('summaryCards').style.display = 'flex';
            document.getElementById('bracketsCard').style.display = 'block';
            document.getElementById('breakdownCard').style.display = 'block';
            document.getElementById('employeeCard').style.display = 'block';
            
            updateSummary(res.summary || {});
            renderBrackets(res.paye_brackets || []);
            renderBreakdown(res.paye_breakdown || []);
            
            allEmployees = res.employee_paye_details || [];
            const paginatedEmployees = allEmployees.slice((page - 1) * perPage, page * perPage);
            renderEmployees(paginatedEmployees);
            renderPagination(page, allEmployees.length);
            if(document.getElementById('rowsInfo')) {
                document.getElementById('rowsInfo').textContent = 
                    `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, allEmployees.length)} of ${allEmployees.length.toLocaleString()} employees`;
            }
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            document.getElementById('emptyState').innerHTML = `
                <div class="card-body text-danger text-center py-5">
                    <i class="bx bx-error-circle fs-1"></i>
                    <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                </div>
            `;
            document.getElementById('emptyState').style.display = 'block';
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
    
    if(document.getElementById('btn-generate')) {
        document.getElementById('btn-generate').addEventListener('click', () => { 
            page = 1;
            load(); 
        });
    }
    
    if(document.getElementById('perPageSelect')) {
        document.getElementById('perPageSelect').addEventListener('change', function(){
            perPage = parseInt(this.value);
            page = 1;
            const paginatedEmployees = allEmployees.slice((page - 1) * perPage, page * perPage);
            renderEmployees(paginatedEmployees);
            renderPagination(page, allEmployees.length);
            if(document.getElementById('rowsInfo')) {
                document.getElementById('rowsInfo').textContent = 
                    `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, allEmployees.length)} of ${allEmployees.length.toLocaleString()} employees`;
            }
        });
    }
    
    window.viewEmployeePayeDetails = function(employeeId, items){
        const modal = new bootstrap.Modal(document.getElementById('employeePayeModal'));
        const body = document.getElementById('employeePayeModalBody');
        
        if(!items || !items.length){
            body.innerHTML = '<p class="text-muted">No PAYE details available for this employee.</p>';
            modal.show();
            return;
        }
        
        let html = '<div class="table-responsive"><table class="table table-sm">';
        html += '<thead><tr><th>Pay Period</th><th class="text-end">Gross Salary</th><th class="text-end">PAYE</th><th class="text-end">Net Salary</th></tr></thead><tbody>';
        
        items.forEach(item => {
            html += `
                <tr>
                    <td>${escapeHtml(item.pay_period || '')}</td>
                    <td class="text-end">${formatCurrency(item.gross_salary || 0)}</td>
                    <td class="text-end"><strong class="text-success">${formatCurrency(item.paye_amount || 0)}</strong></td>
                    <td class="text-end">${formatCurrency(item.net_salary || 0)}</td>
                </tr>
            `;
        });
        
        const totalPaye = items.reduce((sum, item) => sum + (parseFloat(item.paye_amount) || 0), 0);
        const totalGross = items.reduce((sum, item) => sum + (parseFloat(item.gross_salary) || 0), 0);
        const totalNet = items.reduce((sum, item) => sum + (parseFloat(item.net_salary) || 0), 0);
        
        html += '</tbody><tfoot class="table-light"><tr>';
        html += `<th>Total</th>`;
        html += `<th class="text-end">${formatCurrency(totalGross)}</th>`;
        html += `<th class="text-end"><strong class="text-success">${formatCurrency(totalPaye)}</strong></th>`;
        html += `<th class="text-end">${formatCurrency(totalNet)}</th>`;
        html += '</tr></tfoot></table></div>';
        
        body.innerHTML = html;
        modal.show();
    };
    
    // Make load function globally accessible
    window.loadPayeManagement = function() {
        page = 1;
        load();
    };
    
    // Don't auto-load on page load - wait for user to click Generate
})();
</script>
@endpush
