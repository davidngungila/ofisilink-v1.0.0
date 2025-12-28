@extends('layouts.app')

@section('title', 'Cash Flow Statement')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Cash Flow Statement</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="operating"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="investing"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="financing"] {
        border-left-color: #ffc107 !important;
    }

    .summary-card[data-type="net"] {
        border-left-color: #17a2b8 !important;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
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

    .operating-section {
        border: 2px solid #28a745;
        border-radius: 8px;
        overflow: hidden;
    }

    .investing-section {
        border: 2px solid #007bff;
        border-radius: 8px;
        overflow: hidden;
    }

    .financing-section {
        border: 2px solid #ffc107;
        border-radius: 8px;
        overflow: hidden;
    }

    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 8px 8px 0 0;
        font-weight: bold;
        margin-bottom: 0;
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

    .balance-positive {
        color: #28a745;
        font-weight: bold;
    }

    .balance-negative {
        color: #dc3545;
        font-weight: bold;
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
                        <i class="bx bx-money me-2"></i>Cash Flow Statement
                    </h2>
                    <p class="mb-0 opacity-90">Financial statement showing cash inflows and outflows from operating, investing, and financing activities</p>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="operating">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Operating</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumOperating">0.00</h3>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="investing">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Investing</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumInvesting">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-building fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="financing">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Financing</h6>
                            <h3 class="mb-0 text-warning fw-bold" id="sumFinancing">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-wallet fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="net">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Net Cash Flow</h6>
                            <h3 class="mb-0 text-info fw-bold" id="sumNet">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-equalizer fs-4 text-info"></i>
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
                    <i class="bx bx-filter-alt me-2"></i>Date Range
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
                        <label class="form-label small text-muted fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="filterStartDate" value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="filterEndDate" value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-semibold">&nbsp;</label>
                        <button class="btn btn-primary btn-sm w-100" id="btn-generate">
                            <i class="bx bx-refresh me-1"></i>Generate Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Flow Sections -->
    <div class="row g-4">
        <!-- Operating Activities -->
        <div class="col-md-4">
            <div class="operating-section">
                <div class="section-header">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-trending-up me-2"></i>OPERATING ACTIVITIES
                    </h5>
                </div>
                <div class="card border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover mb-0" id="operatingTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-end">Amount (TZS)</th>
                                    </tr>
                                </thead>
                                <tbody id="operatingTableBody">
                                    <tr>
                                        <td colspan="2" class="text-center py-5">
                                            <div class="spinner-border text-success" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-success sticky-bottom">
                                    <tr>
                                        <th class="text-end">TOTAL OPERATING:</th>
                                        <th class="text-end text-success" id="operatingTotal">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Investing Activities -->
        <div class="col-md-4">
            <div class="investing-section">
                <div class="section-header">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-building me-2"></i>INVESTING ACTIVITIES
                    </h5>
                </div>
                <div class="card border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover mb-0" id="investingTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-end">Amount (TZS)</th>
                                    </tr>
                                </thead>
                                <tbody id="investingTableBody">
                                    <tr>
                                        <td colspan="2" class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-primary sticky-bottom">
                                    <tr>
                                        <th class="text-end">TOTAL INVESTING:</th>
                                        <th class="text-end text-primary" id="investingTotal">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financing Activities -->
        <div class="col-md-4">
            <div class="financing-section">
                <div class="section-header">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-wallet me-2"></i>FINANCING ACTIVITIES
                    </h5>
                </div>
                <div class="card border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover mb-0" id="financingTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-end">Amount (TZS)</th>
                                    </tr>
                                </thead>
                                <tbody id="financingTableBody">
                                    <tr>
                                        <td colspan="2" class="text-center py-5">
                                            <div class="spinner-border text-warning" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-warning sticky-bottom">
                                    <tr>
                                        <th class="text-end">TOTAL FINANCING:</th>
                                        <th class="text-end text-warning" id="financingTotal">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Net Cash Flow Summary -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">NET CASH FLOW:</h5>
                <h4 class="mb-0 fw-bold" id="netCashFlowTotal">
                    <span class="text-info">0.00</span>
                </h4>
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
// Advanced Cash Flow Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.cash-bank.cash-flow.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.cash-bank.cash-flow') }}';
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            start_date: document.getElementById('filterStartDate')?.value || '{{ $startDate }}',
            end_date: document.getElementById('filterEndDate')?.value || '{{ $endDate }}',
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const operating = summary?.operating_cash || 0;
        const investing = summary?.investing_cash || 0;
        const financing = summary?.financing_cash || 0;
        const net = summary?.net_cash_flow || 0;
        
        animateValue('sumOperating', 0, operating, 800);
        animateValue('sumInvesting', 0, investing, 800);
        animateValue('sumFinancing', 0, financing, 800);
        animateValue('sumNet', 0, Math.abs(net), 800);
        
        if(document.getElementById('operatingTotal')) document.getElementById('operatingTotal').textContent = formatCurrency(operating);
        if(document.getElementById('investingTotal')) document.getElementById('investingTotal').textContent = formatCurrency(investing);
        if(document.getElementById('financingTotal')) document.getElementById('financingTotal').textContent = formatCurrency(financing);
        if(document.getElementById('netCashFlowTotal')) {
            const netEl = document.getElementById('netCashFlowTotal');
            const netSpan = netEl.querySelector('span');
            if(netSpan) {
                netSpan.textContent = formatCurrency(Math.abs(net));
                netSpan.className = net >= 0 ? 'text-success' : 'text-danger';
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
    
    function getAmountClass(amount){
        return amount >= 0 ? 'balance-positive' : 'balance-negative';
    }
    
    function renderTable(section, items){
        const tbody = document.getElementById(section + 'TableBody');
        if(!tbody) return;
        
        if(!items || !items.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="2" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No ${section} activities found</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = items.map((item, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.01}s">
                <td>
                    <div class="fw-medium">${escapeHtml(item.description || 'N/A')}</div>
                    ${item.code ? `<small class="text-muted"><code>${escapeHtml(item.code)}</code></small>` : ''}
                </td>
                <td class="text-end">
                    <span class="${getAmountClass(item.amount)}">
                        ${item.amount >= 0 ? '' : '-'}${formatCurrency(Math.abs(item.amount))}
                    </span>
                </td>
            </tr>
        `).join('');
    }
    
    function load(){
        showLoading(true);
        
        const operatingBody = document.getElementById('operatingTableBody');
        const investingBody = document.getElementById('investingTableBody');
        const financingBody = document.getElementById('financingTableBody');
        
        if(operatingBody) operatingBody.innerHTML = '<tr><td colspan="2" class="text-center py-5"><div class="spinner-border text-success"></div></td></tr>';
        if(investingBody) investingBody.innerHTML = '<tr><td colspan="2" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';
        if(financingBody) financingBody.innerHTML = '<tr><td colspan="2" class="text-center py-5"><div class="spinner-border text-warning"></div></td></tr>';
        
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
                let msg = res.message || 'Failed to load cash flow';
                
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
                if(operatingBody) operatingBody.innerHTML = `<tr><td colspan="2" class="text-danger text-center py-5">${errorHtml}</td></tr>`;
                if(investingBody) investingBody.innerHTML = `<tr><td colspan="2" class="text-danger text-center py-5">${errorHtml}</td></tr>`;
                if(financingBody) financingBody.innerHTML = `<tr><td colspan="2" class="text-danger text-center py-5">${errorHtml}</td></tr>`;
                
                updateSummary({ 
                    operating_cash: 0, investing_cash: 0, financing_cash: 0, net_cash_flow: 0
                });
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            updateSummary(res.summary || {});
            
            renderTable('operating', res.operating?.items || []);
            renderTable('investing', res.investing?.items || []);
            renderTable('financing', res.financing?.items || []);
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            const errorMsg = escapeHtml(err.message || 'Network or server error');
            if(operatingBody) operatingBody.innerHTML = `<tr><td colspan="2" class="text-danger text-center py-5">${errorMsg}</td></tr>`;
            if(investingBody) investingBody.innerHTML = `<tr><td colspan="2" class="text-danger text-center py-5">${errorMsg}</td></tr>`;
            if(financingBody) financingBody.innerHTML = `<tr><td colspan="2" class="text-danger text-center py-5">${errorMsg}</td></tr>`;
            updateSummary({ 
                operating_cash: 0, investing_cash: 0, financing_cash: 0, net_cash_flow: 0
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
    
    if(document.getElementById('btn-generate')) {
        document.getElementById('btn-generate').addEventListener('click', () => { load(); });
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
    
    ['filterStartDate', 'filterEndDate'].forEach(id => {
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
    window.loadCashFlow = function() {
        load();
    };
    
    // Initialize
    load();
})();
</script>
@endpush
