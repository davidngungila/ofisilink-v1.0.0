@extends('layouts.app')

@section('title', 'Trial Balance')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Trial Balance</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="debits"] {
        border-left-color: #dc3545 !important;
    }

    .summary-card[data-type="credits"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="balance"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="accounts"] {
        border-left-color: #17a2b8 !important;
    }

    .summary-card[data-type="status"] {
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

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        transition: background-color 0.15s ease;
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

    .balance-zero {
        color: #6c757d;
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
                        <i class="bx bx-balance me-2"></i>Trial Balance
                    </h2>
                    <p class="mb-0 opacity-90">Financial statement showing all account balances as of a specific date</p>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="debits">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Debits</h6>
                            <h3 class="mb-0 text-danger fw-bold" id="sumDebits">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-down-arrow-alt fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="credits">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Credits</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumCredits">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-up-arrow-alt fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="balance">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Balance</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumBalance">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-equalizer fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="status">
            <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Status</h6>
                            <h3 class="mb-0 fw-bold" id="sumStatus">
                                <span class="badge bg-secondary">Loading...</span>
                            </h3>
                            <small class="text-muted" id="sumStatusText">-</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-check-circle fs-4 text-warning"></i>
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
                        <label class="form-label small text-muted fw-semibold">As of Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="filterDate" value="{{ $date }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Account Type</label>
                        <select class="form-select form-select-sm" id="filterAccountType">
                            <option value="">All Types</option>
                            <option value="Asset">Asset</option>
                            <option value="Liability">Liability</option>
                            <option value="Equity">Equity</option>
                            <option value="Revenue">Revenue</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by account code or name...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Clear search">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-semibold">Options</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showZeroBalance">
                            <label class="form-check-label small" for="showZeroBalance">Show Zero Balance</label>
                        </div>
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
                    <i class="bx bx-table me-2"></i>Trial Balance
                    <span class="badge bg-primary ms-2" id="accountCount">0</span>
                </h6>
                <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
                    <label class="small text-muted me-2">Per page:</label>
                    <select class="form-select form-select-sm" style="width: auto;" id="perPageSelect">
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                        <option value="200">200</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0" id="trialBalanceTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="code">
                                Account Code <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="name">
                                Account Name <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="type">
                                Type <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="debits">
                                Debits (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="credits">
                                Credits (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="balance">
                                Balance (TZS) <i class="bx bx-sort"></i>
                            </th>
                            </tr>
                        </thead>
                    <tbody id="trialBalanceTableBody">
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading trial balance...</p>
                            </td>
                            </tr>
                        </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Totals:</th>
                            <th class="text-end text-danger" id="footDebits">0.00</th>
                            <th class="text-end text-success" id="footCredits">0.00</th>
                            <th class="text-end text-primary" id="footBalance">0.00</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 accounts
                </div>
                <nav aria-label="Trial balance pagination">
                    <ul class="pagination pagination-sm mb-0" id="pagination">
                        <!-- Pagination will be generated by JavaScript -->
                    </ul>
                </nav>
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
// Advanced Trial Balance Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.trial-balance.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.trial-balance') }}';
    let page = 1, perPage = 50;
    let sortColumn = 'code', sortDirection = 'asc';
    let allAccounts = [];
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            date: document.getElementById('filterDate')?.value || '{{ $date }}',
            account_type: document.getElementById('filterAccountType')?.value || '',
            q: document.getElementById('filterQ')?.value || '',
            show_zero_balance: document.getElementById('showZeroBalance')?.checked || false,
            page, per_page: perPage
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const debits = summary?.total_debits || 0;
        const credits = summary?.total_credits || 0;
        const balance = summary?.total_balance || 0;
        const count = summary?.account_count || 0;
        const isBalanced = summary?.is_balanced || false;
        
        animateValue('sumDebits', 0, debits, 800);
        animateValue('sumCredits', 0, credits, 800);
        animateValue('sumBalance', 0, Math.abs(balance), 800);
        
        if(document.getElementById('footDebits')) document.getElementById('footDebits').textContent = formatCurrency(debits);
        if(document.getElementById('footCredits')) document.getElementById('footCredits').textContent = formatCurrency(credits);
        if(document.getElementById('footBalance')) document.getElementById('footBalance').textContent = formatCurrency(Math.abs(balance));
        
        const statusEl = document.getElementById('sumStatus');
        const statusTextEl = document.getElementById('sumStatusText');
        if(statusEl && statusTextEl) {
            if(isBalanced) {
                statusEl.innerHTML = '<span class="badge bg-success">BALANCED</span>';
                statusTextEl.textContent = '✓ All accounts balanced';
            } else {
                statusEl.innerHTML = '<span class="badge bg-danger">UNBALANCED</span>';
                statusTextEl.textContent = `Difference: ${formatCurrency(Math.abs(balance))}`;
            }
        }
        
        if(document.getElementById('accountCount')) {
            document.getElementById('accountCount').textContent = count.toLocaleString();
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
    
    function getBalanceClass(balance){
        if(Math.abs(balance) < 0.01) return 'balance-zero';
        return balance >= 0 ? 'balance-positive' : 'balance-negative';
    }
    
    function getTypeBadge(type){
        const badges = {
            'Asset': 'bg-primary',
            'Liability': 'bg-danger',
            'Equity': 'bg-info',
            'Revenue': 'bg-success',
            'Expense': 'bg-warning'
        };
        return badges[type] || 'bg-secondary';
    }
    
    function renderTable(accounts){
        const tbody = document.getElementById('trialBalanceTableBody');
        if(!tbody) return;
        
        if(!accounts || !accounts.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No accounts found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = accounts.map((a, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.01}s">
                <td><code class="text-primary fw-bold">${escapeHtml(a.code || '')}</code></td>
                <td>
                    <div class="fw-medium">${escapeHtml(a.name || 'N/A')}</div>
                </td>
                <td>
                    <span class="badge ${getTypeBadge(a.type)}">${escapeHtml(a.type || '')}</span>
                </td>
                <td class="text-end">
                    ${a.debits > 0 ? `<span class="text-danger">${formatCurrency(a.debits)}</span>` : '<span class="text-muted">-</span>'}
                </td>
                <td class="text-end">
                    ${a.credits > 0 ? `<span class="text-success">${formatCurrency(a.credits)}</span>` : '<span class="text-muted">-</span>'}
                </td>
                <td class="text-end">
                    <span class="${getBalanceClass(a.balance)}">
                        ${a.balance >= 0 ? '' : '-'}${formatCurrency(Math.abs(a.balance))}
                    </span>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalAccounts){
        const totalPages = Math.ceil(totalAccounts / perPage);
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
        const body = document.getElementById('trialBalanceTableBody');
        if(!body) return;
        
        body.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading trial balance...</p>
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
                let msg = res.message || 'Failed to load trial balance';
                
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
                        <td colspan="6" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <div class="mt-2">${errorHtml}</div>
                        </td>
                    </tr>
                `;
                updateSummary({ total_debits: 0, total_credits: 0, total_balance: 0, account_count: 0, is_balanced: false });
                if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = '0 accounts';
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            allAccounts = res.accounts || [];
            updateSummary(res.summary || {});
            if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, res.summary?.count || 0)} of ${(res.summary?.count || 0).toLocaleString()} accounts`;
            
            // Client-side sorting
            allAccounts.sort((a, b) => {
                let aVal = a[sortColumn];
                let bVal = b[sortColumn];
                
                if(['debits', 'credits', 'balance'].includes(sortColumn)){
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                }
                
                if(sortDirection === 'asc'){
                    return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                } else {
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                }
            });
            
            renderTable(allAccounts);
            renderPagination(page, res.summary?.count || 0);
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            if(body) {
                body.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                        </td>
                    </tr>
                `;
            }
            updateSummary({ total_debits: 0, total_credits: 0, total_balance: 0, account_count: 0, is_balanced: false });
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
    
    ['filterDate', 'filterAccountType', 'showZeroBalance'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('change', () => { page = 1; load(); });
    });
    
    if(document.getElementById('filterQ')) {
        document.getElementById('filterQ').addEventListener('input', () => { 
            page = 1; 
            debounce(load, 300)(); 
        });
    }
    
    if(document.getElementById('clearSearch')) {
        document.getElementById('clearSearch').addEventListener('click', () => {
            if(document.getElementById('filterQ')) document.getElementById('filterQ').value = '';
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
                sortDirection = 'asc';
            }
            
            document.querySelectorAll('.sortable').forEach(t => {
                t.classList.remove('active');
                const icon = t.querySelector('i');
                if(icon) icon.className = 'bx bx-sort';
            });
            
            this.classList.add('active');
            const icon = this.querySelector('i');
            if(icon) icon.className = sortDirection === 'asc' ? 'bx bx-sort-up' : 'bx bx-sort-down';
            
            renderTable(allAccounts);
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
    
    // Make load function globally accessible
    window.loadTrialBalance = function(resetPage = false) {
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
