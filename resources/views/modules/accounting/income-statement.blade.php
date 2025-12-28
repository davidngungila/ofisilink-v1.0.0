@extends('layouts.app')

@section('title', 'Income Statement')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Income Statement (Profit & Loss)</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="income"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="expenses"] {
        border-left-color: #dc3545 !important;
    }

    .summary-card[data-type="net"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="margin"] {
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

    .income-section {
        border: 2px solid #28a745;
        border-radius: 8px;
        overflow: hidden;
    }

    .expenses-section {
        border: 2px solid #dc3545;
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
                        <i class="bx bx-line-chart me-2"></i>Income Statement (Profit & Loss)
                    </h2>
                    <p class="mb-0 opacity-90">Financial statement showing income, expenses, and net profit/loss for a period</p>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="income">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Income</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumIncome">0.00</h3>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="expenses">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Expenses</h6>
                            <h3 class="mb-0 text-danger fw-bold" id="sumExpenses">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-trending-down fs-4 text-danger"></i>
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
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Net Income</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumNetIncome">0.00</h3>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="margin">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Profit Margin</h6>
                            <h3 class="mb-0 text-warning fw-bold" id="sumMargin">0.00%</h3>
                            <small class="text-muted" id="sumMarginText">-</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-percent fs-4 text-warning"></i>
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
                        <label class="form-label small text-muted fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="filterStartDate" value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm" id="filterEndDate" value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Account Type</label>
                        <select class="form-select form-select-sm" id="filterAccountType">
                            <option value="">All Types</option>
                            <option value="Income">Income</option>
                            <option value="Expense">Expense</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by account code or name...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Clear search">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Income Statement Sections -->
    <div class="row g-4">
        <!-- Income Section -->
        <div class="col-md-6">
            <div class="income-section">
                <div class="section-header">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-trending-up me-2"></i>INCOME
                        <span class="badge bg-light text-success ms-2" id="incomeCount">0</span>
                    </h5>
                </div>
                <div class="card border-0">
                    <div class="card-body p-0">
                <div class="table-responsive">
                            <table class="table table-hover mb-0" id="incomeTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable" data-sort="code" data-section="income">
                                            Code <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="sortable" data-sort="name" data-section="income">
                                            Account Name <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="text-end sortable" data-sort="balance" data-section="income">
                                            Amount (TZS) <i class="bx bx-sort"></i>
                                        </th>
                            </tr>
                        </thead>
                                <tbody id="incomeTableBody">
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="spinner-border text-success" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                            </tr>
                                </tbody>
                                <tfoot class="table-success">
                            <tr>
                                        <th colspan="2" class="text-end">TOTAL INCOME:</th>
                                        <th class="text-end text-success" id="incomeTotal">0.00</th>
                            </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Section -->
        <div class="col-md-6">
            <div class="expenses-section">
                <div class="section-header">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-trending-down me-2"></i>EXPENSES
                        <span class="badge bg-light text-danger ms-2" id="expensesCount">0</span>
                    </h5>
                </div>
                <div class="card border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="expensesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable" data-sort="code" data-section="expenses">
                                            Code <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="sortable" data-sort="name" data-section="expenses">
                                            Account Name <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="text-end sortable" data-sort="balance" data-section="expenses">
                                            Amount (TZS) <i class="bx bx-sort"></i>
                                        </th>
                            </tr>
                                </thead>
                                <tbody id="expensesTableBody">
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="spinner-border text-danger" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                            </tr>
                                </tbody>
                                <tfoot class="table-danger">
                                    <tr>
                                        <th colspan="2" class="text-end">TOTAL EXPENSES:</th>
                                        <th class="text-end text-danger" id="expensesTotal">0.00</th>
                            </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Net Income Summary -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-body bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">NET INCOME (PROFIT/LOSS):</h5>
                <h4 class="mb-0 fw-bold" id="netIncomeTotal">
                    <span class="text-primary">0.00</span>
                </h4>
            </div>
        </div>
    </div>

    <!-- Detailed View Toggle -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-detail me-2"></i>Detailed Account Information
                </h6>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="showDetails">
                    <label class="form-check-label small" for="showDetails">Show Details (Debits, Credits, Opening Balance)</label>
                </div>
            </div>
        </div>
        <div class="card-body" id="detailedView" style="display: none;">
            <div class="row g-4">
                <div class="col-md-6">
                    <h6 class="text-success mb-3">Income Details</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="incomeDetailsTable">
                            <thead class="table-success">
                                <tr>
                                    <th>Account</th>
                                    <th class="text-end">Opening</th>
                                    <th class="text-end">Debits</th>
                                    <th class="text-end">Credits</th>
                                    <th class="text-end">Balance</th>
                            </tr>
                            </thead>
                            <tbody id="incomeDetailsBody">
                                <tr><td colspan="5" class="text-center text-muted">No data</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6 class="text-danger mb-3">Expenses Details</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="expensesDetailsTable">
                            <thead class="table-danger">
                                <tr>
                                    <th>Account</th>
                                    <th class="text-end">Opening</th>
                                    <th class="text-end">Debits</th>
                                    <th class="text-end">Credits</th>
                                    <th class="text-end">Balance</th>
                            </tr>
                            </thead>
                            <tbody id="expensesDetailsBody">
                                <tr><td colspan="5" class="text-center text-muted">No data</td></tr>
                        </tbody>
                    </table>
                    </div>
                </div>
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
// Advanced Income Statement Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.income-statement.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.income-statement') }}';
    let sortColumn = {};
    let sortDirection = {};
    let allIncome = [];
    let allExpenses = [];
    let showDetails = false;
    
    // Initialize sort state for each section
    sortColumn.income = 'code';
    sortColumn.expenses = 'code';
    sortDirection.income = 'asc';
    sortDirection.expenses = 'asc';
    
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
            account_type: document.getElementById('filterAccountType')?.value || '',
            q: document.getElementById('filterQ')?.value || '',
            show_zero_balance: document.getElementById('showZeroBalance')?.checked || false,
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const income = summary?.total_income || 0;
        const expenses = summary?.total_expenses || 0;
        const netIncome = summary?.net_income || 0;
        const margin = summary?.profit_margin || 0;
        
        animateValue('sumIncome', 0, income, 800);
        animateValue('sumExpenses', 0, expenses, 800);
        animateValue('sumNetIncome', 0, Math.abs(netIncome), 800);
        
        if(document.getElementById('incomeTotal')) document.getElementById('incomeTotal').textContent = formatCurrency(income);
        if(document.getElementById('expensesTotal')) document.getElementById('expensesTotal').textContent = formatCurrency(expenses);
        if(document.getElementById('netIncomeTotal')) {
            const netEl = document.getElementById('netIncomeTotal');
            const netSpan = netEl.querySelector('span');
            if(netSpan) {
                netSpan.textContent = formatCurrency(Math.abs(netIncome));
                netSpan.className = netIncome >= 0 ? 'text-success' : 'text-danger';
            }
        }
        
        if(document.getElementById('sumMargin')) {
            document.getElementById('sumMargin').textContent = number(margin) + '%';
        }
        if(document.getElementById('sumMarginText')) {
            document.getElementById('sumMarginText').textContent = margin >= 0 ? 'Profit' : 'Loss';
        }
        
        if(document.getElementById('incomeCount')) document.getElementById('incomeCount').textContent = (summary?.income_count || 0).toLocaleString();
        if(document.getElementById('expensesCount')) document.getElementById('expensesCount').textContent = (summary?.expenses_count || 0).toLocaleString();
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
    
    function renderTable(section, accounts){
        const tbody = document.getElementById(section + 'TableBody');
        if(!tbody) return;
        
        if(!accounts || !accounts.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No ${section} accounts found</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        // Sort accounts
        const sorted = [...accounts].sort((a, b) => {
            let aVal = a[sortColumn[section]];
            let bVal = b[sortColumn[section]];
            
            if(sortColumn[section] === 'balance'){
                aVal = parseFloat(aVal) || 0;
                bVal = parseFloat(bVal) || 0;
            }
            
            if(sortDirection[section] === 'asc'){
                return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
            } else {
                return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
            }
        });
        
        tbody.innerHTML = sorted.map((a, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.01}s">
                <td><code class="text-primary fw-bold">${escapeHtml(a.code || '')}</code></td>
                <td>
                    <div class="fw-medium">${escapeHtml(a.name || 'N/A')}</div>
                    ${a.category ? `<small class="text-muted">${escapeHtml(a.category)}</small>` : ''}
                </td>
                <td class="text-end">
                    <span class="${getBalanceClass(a.balance)}">
                        ${a.balance >= 0 ? '' : '-'}${formatCurrency(Math.abs(a.balance))}
                    </span>
                </td>
            </tr>
        `).join('');
    }
    
    function renderDetailsTable(section, accounts){
        const tbody = document.getElementById(section + 'DetailsBody');
        if(!tbody) return;
        
        if(!accounts || !accounts.length){
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No accounts</td></tr>';
            return;
        }
        
        tbody.innerHTML = accounts.map(a => `
            <tr>
                <td>
                    <div><code class="text-primary">${escapeHtml(a.code || '')}</code></div>
                    <small>${escapeHtml(a.name || 'N/A')}</small>
                </td>
                <td class="text-end">${formatCurrency(a.opening_balance || 0)}</td>
                <td class="text-end text-danger">${a.debits > 0 ? formatCurrency(a.debits) : '-'}</td>
                <td class="text-end text-success">${a.credits > 0 ? formatCurrency(a.credits) : '-'}</td>
                <td class="text-end">
                    <span class="${getBalanceClass(a.balance)}">
                        ${a.balance >= 0 ? '' : '-'}${formatCurrency(Math.abs(a.balance))}
                    </span>
                </td>
            </tr>
        `).join('');
    }
    
    function load(){
        showLoading(true);
        
        const incomeBody = document.getElementById('incomeTableBody');
        const expensesBody = document.getElementById('expensesTableBody');
        
        if(incomeBody) incomeBody.innerHTML = '<tr><td colspan="3" class="text-center py-5"><div class="spinner-border text-success"></div></td></tr>';
        if(expensesBody) expensesBody.innerHTML = '<tr><td colspan="3" class="text-center py-5"><div class="spinner-border text-danger"></div></td></tr>';
        
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
                let msg = res.message || 'Failed to load income statement';
                
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
                if(incomeBody) incomeBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorHtml}</td></tr>`;
                if(expensesBody) expensesBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorHtml}</td></tr>`;
                
                updateSummary({ 
                    total_income: 0, total_expenses: 0, net_income: 0, profit_margin: 0,
                    income_count: 0, expenses_count: 0
                });
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            allIncome = res.income || [];
            allExpenses = res.expenses || [];
            
            updateSummary(res.summary || {});
            
            renderTable('income', allIncome);
            renderTable('expenses', allExpenses);
            
            if(showDetails) {
                renderDetailsTable('income', allIncome);
                renderDetailsTable('expenses', allExpenses);
            }
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            const errorMsg = escapeHtml(err.message || 'Network or server error');
            if(incomeBody) incomeBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorMsg}</td></tr>`;
            if(expensesBody) expensesBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorMsg}</td></tr>`;
            updateSummary({ 
                total_income: 0, total_expenses: 0, net_income: 0, profit_margin: 0,
                income_count: 0, expenses_count: 0
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
    
    if(document.getElementById('btn-export')) {
        document.getElementById('btn-export').addEventListener('click', (e) => {
            e.preventDefault();
            const params = qs();
            const p = new URLSearchParams();
            Object.keys(params).forEach(key => {
                if(params[key] && key !== 'show_zero_balance') {
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
                if(params[key] && key !== 'show_zero_balance') {
                    p.append(key, params[key]);
                }
            });
            p.append('export', 'excel');
            window.location.href = pdfEndpoint + '?' + p.toString();
        });
    }
    
    ['filterStartDate', 'filterEndDate', 'filterAccountType'].forEach(id => {
        const el = document.getElementById(id);
        if(el) el.addEventListener('change', () => { load(); });
    });
    
    if(document.getElementById('filterQ')) {
        document.getElementById('filterQ').addEventListener('input', () => { 
            debounce(load, 300)(); 
        });
    }
    
    if(document.getElementById('clearSearch')) {
        document.getElementById('clearSearch').addEventListener('click', () => {
            if(document.getElementById('filterQ')) document.getElementById('filterQ').value = '';
            load();
        });
    }
    
    if(document.getElementById('showDetails')) {
        document.getElementById('showDetails').addEventListener('change', function(){
            showDetails = this.checked;
            const detailedView = document.getElementById('detailedView');
            if(detailedView) {
                detailedView.style.display = showDetails ? 'block' : 'none';
                if(showDetails) {
                    renderDetailsTable('income', allIncome);
                    renderDetailsTable('expenses', allExpenses);
                }
            }
        });
    }
    
    // Sorting
    document.querySelectorAll('.sortable').forEach(th => {
        th.addEventListener('click', function(){
            const section = this.dataset.section;
            const col = this.dataset.sort;
            
            if(sortColumn[section] === col){
                sortDirection[section] = sortDirection[section] === 'asc' ? 'desc' : 'asc';
            } else {
                sortColumn[section] = col;
                sortDirection[section] = 'asc';
            }
            
            // Update icons for this section only
            document.querySelectorAll(`.sortable[data-section="${section}"]`).forEach(t => {
                t.classList.remove('active');
                const icon = t.querySelector('i');
                if(icon) icon.className = 'bx bx-sort';
            });
            
            this.classList.add('active');
            const icon = this.querySelector('i');
            if(icon) icon.className = sortDirection[section] === 'asc' ? 'bx bx-sort-up' : 'bx bx-sort-down';
            
            renderTable(section, window['all' + section.charAt(0).toUpperCase() + section.slice(1)]);
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
    window.loadIncomeStatement = function() {
        load();
    };
    
    // Initialize
    load();
})();
</script>
@endpush
