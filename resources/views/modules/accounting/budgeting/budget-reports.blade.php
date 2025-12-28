@extends('layouts.app')

@section('title', 'Budget Reports - Actual vs Budgeted')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Budgeting & Forecasting - Budget Reports</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="budgeted"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="actual"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="variance"] {
        border-left-color: #ffc107 !important;
    }

    .summary-card[data-type="percentage"] {
        border-left-color: #17a2b8 !important;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
    }

    .variance-favorable { 
        color: #28a745; 
        background-color: #d4edda; 
        font-weight: bold;
    }

    .variance-unfavorable { 
        color: #dc3545; 
        background-color: #f8d7da; 
        font-weight: bold;
    }

    .variance-neutral { 
        color: #6c757d; 
        background-color: #e9ecef; 
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
                        <i class="bx bx-bar-chart-alt-2 me-2"></i>Budget Reports - Actual vs Budgeted Analysis
                    </h2>
                    <p class="mb-0 opacity-90">Compare actual performance against budgeted amounts</p>
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
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="budgeted">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Budgeted</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumBudgeted">0.00</h3>
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
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="percentage">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Variance %</h6>
                            <h3 class="mb-0 text-info fw-bold" id="sumVariancePercent">0.00%</h3>
                            <small class="text-muted">Percentage</small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-percent fs-4 text-info"></i>
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
                    <i class="bx bx-filter-alt me-2"></i>Filters & Options
                </h6>
                <button class="btn btn-sm btn-link text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" id="toggleFilters">
                    <i class="bx bx-chevron-up" id="filterIcon"></i>
                </button>
            </div>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-semibold">Budget</label>
                        <select class="form-select form-select-sm" id="filterBudget">
                            <option value="">All Budgets</option>
                            @foreach($allBudgets as $budget)
                            <option value="{{ $budget->id }}">
                                {{ $budget->budget_name }} ({{ $budget->fiscal_year }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Fiscal Year</label>
                        <select class="form-select form-select-sm" id="filterFiscalYear">
                            @foreach($fiscalYears as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Period</label>
                        <select class="form-select form-select-sm" id="filterPeriod">
                            <option value="monthly" selected>Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-semibold">&nbsp;</label>
                        <button class="btn btn-primary btn-sm w-100" id="btn-apply-filters">
                            <i class="bx bx-filter me-1"></i>Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reports Section -->
    <div id="reportsContainer">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2 mb-0">Loading budget reports...</p>
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
// Advanced Budget Reports Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.budgeting.budget-reports.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.budgeting.budget-reports') }}';
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            budget_id: document.getElementById('filterBudget')?.value || '',
            fiscal_year: document.getElementById('filterFiscalYear')?.value || '{{ date('Y') }}',
            period: document.getElementById('filterPeriod')?.value || 'monthly'
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const budgeted = summary?.total_budgeted || 0;
        const actual = summary?.total_actual || 0;
        const variance = summary?.total_variance || 0;
        const variancePercent = summary?.variance_percentage || 0;
        
        animateValue('sumBudgeted', 0, budgeted, 800);
        animateValue('sumActual', 0, actual, 800);
        animateValue('sumVariance', 0, Math.abs(variance), 800);
        
        if(document.getElementById('sumVariancePercent')) {
            document.getElementById('sumVariancePercent').textContent = variancePercent.toFixed(2) + '%';
        }
        
        const varianceEl = document.getElementById('sumVariance');
        const varianceText = document.getElementById('sumVarianceText');
        if(varianceEl && varianceText) {
            if(variance < 0) {
                varianceEl.className = 'mb-0 text-success fw-bold';
                varianceText.textContent = 'Favorable';
            } else if(variance > 0) {
                varianceEl.className = 'mb-0 text-danger fw-bold';
                varianceText.textContent = 'Unfavorable';
            } else {
                varianceEl.className = 'mb-0 text-secondary fw-bold';
                varianceText.textContent = 'On Target';
            }
        }
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
                element.textContent = formatCurrency(end);
                clearInterval(timer);
            } else {
                element.textContent = formatCurrency(current);
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
        if(Math.abs(variance) < 0.01) return 'variance-neutral';
        return variance < 0 ? 'variance-favorable' : 'variance-unfavorable';
    }
    
    function renderReports(reports){
        const container = document.getElementById('reportsContainer');
        if(!container) return;
        
        if(!reports || !reports.length){
            container.innerHTML = `
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No budget reports found. Try adjusting your filters.</p>
                    </div>
                </div>
            `;
            return;
        }
        
        let html = '';
        reports.forEach((report, idx) => {
            html += `
                <div class="card border-0 shadow-sm mb-4 fade-in" style="animation-delay: ${idx * 0.1}s">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bx bx-file-blank me-2"></i>
                                ${escapeHtml(report.budget_name)} - ${escapeHtml(report.fiscal_year)}
                            </h5>
                            <div>
                                <span class="badge bg-info me-2">${escapeHtml(report.department_name)}</span>
                                <span class="badge ${report.total_variance < 0 ? 'bg-success' : (report.total_variance > 0 ? 'bg-danger' : 'bg-secondary')}">
                                    ${report.total_variance < 0 ? 'Under Budget' : (report.total_variance > 0 ? 'Over Budget' : 'On Budget')}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>Budgeted:</strong> ${formatCurrency(report.total_budgeted)}
                            </div>
                            <div class="col-md-3">
                                <strong>Actual:</strong> ${formatCurrency(report.total_actual)}
                            </div>
                            <div class="col-md-3">
                                <strong>Variance:</strong> 
                                <span class="${getVarianceClass(report.total_variance)}">
                                    ${report.total_variance >= 0 ? '' : '-'}${formatCurrency(Math.abs(report.total_variance))}
                                </span>
                            </div>
                            <div class="col-md-3">
                                <strong>Variance %:</strong> 
                                <span class="${getVarianceClass(report.total_variance)}">
                                    ${report.variance_percentage >= 0 ? '' : '-'}${Math.abs(report.variance_percentage).toFixed(2)}%
                                </span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Account</th>
                                        <th class="text-end">Budgeted</th>
                                        <th class="text-end">Actual</th>
                                        <th class="text-end">Variance</th>
                                        <th class="text-end">Variance %</th>
                                    </tr>
                                </thead>
                                <tbody>
            `;
            
            report.items.forEach(item => {
                html += `
                    <tr>
                        <td>
                            <strong>${escapeHtml(item.account)}</strong>
                            <br><small class="text-muted">${escapeHtml(item.account_code)}</small>
                        </td>
                        <td class="text-end">${formatCurrency(item.budgeted)}</td>
                        <td class="text-end">${formatCurrency(item.actual)}</td>
                        <td class="text-end ${getVarianceClass(item.variance)}">
                            ${item.variance >= 0 ? '' : '-'}${formatCurrency(Math.abs(item.variance))}
                        </td>
                        <td class="text-end">
                            ${item.variance_percentage >= 0 ? '' : '-'}${Math.abs(item.variance_percentage).toFixed(2)}%
                        </td>
                    </tr>
                `;
            });
            
            html += `
                                </tbody>
                                <tfoot class="table-primary">
                                    <tr>
                                        <th>Total</th>
                                        <th class="text-end">${formatCurrency(report.total_budgeted)}</th>
                                        <th class="text-end">${formatCurrency(report.total_actual)}</th>
                                        <th class="text-end ${getVarianceClass(report.total_variance)}">
                                            ${report.total_variance >= 0 ? '' : '-'}${formatCurrency(Math.abs(report.total_variance))}
                                        </th>
                                        <th class="text-end">
                                            ${report.variance_percentage >= 0 ? '' : '-'}${Math.abs(report.variance_percentage).toFixed(2)}%
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function load(){
        showLoading(true);
        const container = document.getElementById('reportsContainer');
        if(!container) return;
        
        container.innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2 mb-0">Loading budget reports...</p>
            </div>
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
                let msg = res.message || 'Failed to load budget reports';
                
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
                container.innerHTML = `
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <div class="mt-2">${errorHtml}</div>
                        </div>
                    </div>
                `;
                updateSummary({ 
                    total_budgeted: 0, total_actual: 0, total_variance: 0, variance_percentage: 0 
                });
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            updateSummary(res.summary || {});
            renderReports(res.reports || []);
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            if(container) {
                container.innerHTML = `
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                        </div>
                    </div>
                `;
            }
            updateSummary({ 
                total_budgeted: 0, total_actual: 0, total_variance: 0, variance_percentage: 0 
            });
        })
        .finally(() => {
            showLoading(false);
        });
    }
    
    // Event Listeners
    if(document.getElementById('btn-refresh')) {
        document.getElementById('btn-refresh').addEventListener('click', () => { load(); });
    }
    
    if(document.getElementById('btn-apply-filters')) {
        document.getElementById('btn-apply-filters').addEventListener('click', () => { load(); });
    }
    
    if(document.getElementById('btn-export')) {
        document.getElementById('btn-export').addEventListener('click', (e) => {
            e.preventDefault();
            const params = qs();
            const p = new URLSearchParams();
            Object.keys(params).forEach(key => {
                if(params[key]) {
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
                if(params[key]) {
                    p.append(key, params[key]);
                }
            });
            p.append('export', 'excel');
            window.location.href = pdfEndpoint + '?' + p.toString();
        });
    }
    
    ['filterBudget', 'filterFiscalYear', 'filterPeriod'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('change', () => { load(); });
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
    
    // Make load function globally accessible
    window.loadBudgetReports = function() {
        load();
    };
    
    // Initialize
    load();
})();
</script>
@endpush
