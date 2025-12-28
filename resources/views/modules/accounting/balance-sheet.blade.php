@extends('layouts.app')

@section('title', 'Balance Sheet')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Balance Sheet</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border-left: 4px solid transparent !important;
    }

    .summary-card[data-type="assets"] {
        border-left-color: #007bff !important;
    }

    .summary-card[data-type="liabilities"] {
        border-left-color: #dc3545 !important;
    }

    .summary-card[data-type="equity"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="total"] {
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

    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 8px 8px 0 0;
        font-weight: bold;
        margin-bottom: 0;
    }

    .assets-section {
        border: 2px solid #007bff;
        border-radius: 8px;
        overflow: hidden;
    }

    .liabilities-section {
        border: 2px solid #dc3545;
        border-radius: 8px;
        overflow: hidden;
    }

    .equity-section {
        border: 2px solid #28a745;
        border-radius: 8px;
        overflow: hidden;
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
                        <i class="bx bx-bar-chart-alt-2 me-2"></i>Balance Sheet
                    </h2>
                    <p class="mb-0 opacity-90">Financial statement showing assets, liabilities, and equity as of a specific date</p>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="assets">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Assets</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumAssets">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-trending-up fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="liabilities">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Liabilities</h6>
                            <h3 class="mb-0 text-danger fw-bold" id="sumLiabilities">0.00</h3>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="equity">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Equity</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumEquity">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-wallet fs-4 text-success"></i>
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

    <!-- Balance Sheet Sections -->
    <div class="row g-4">
        <!-- Assets Section -->
        <div class="col-md-6">
            <div class="assets-section">
                <div class="section-header">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-trending-up me-2"></i>ASSETS
                        <span class="badge bg-light text-primary ms-2" id="assetsCount">0</span>
                    </h5>
                </div>
                <div class="card border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="assetsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable" data-sort="code" data-section="assets">
                                            Code <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="sortable" data-sort="name" data-section="assets">
                                            Account Name <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="text-end sortable" data-sort="balance" data-section="assets">
                                            Balance (TZS) <i class="bx bx-sort"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="assetsTableBody">
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-primary">
                                    <tr>
                                        <th colspan="2" class="text-end">TOTAL ASSETS:</th>
                                        <th class="text-end text-primary" id="assetsTotal">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liabilities & Equity Section -->
        <div class="col-md-6">
            <!-- Liabilities -->
            <div class="liabilities-section mb-4">
                <div class="section-header">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-trending-down me-2"></i>LIABILITIES
                        <span class="badge bg-light text-danger ms-2" id="liabilitiesCount">0</span>
                    </h5>
                </div>
                <div class="card border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="liabilitiesTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable" data-sort="code" data-section="liabilities">
                                            Code <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="sortable" data-sort="name" data-section="liabilities">
                                            Account Name <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="text-end sortable" data-sort="balance" data-section="liabilities">
                                            Balance (TZS) <i class="bx bx-sort"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="liabilitiesTableBody">
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
                                        <th colspan="2" class="text-end">TOTAL LIABILITIES:</th>
                                        <th class="text-end text-danger" id="liabilitiesTotal">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Equity -->
            <div class="equity-section">
                <div class="section-header">
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-wallet me-2"></i>EQUITY
                        <span class="badge bg-light text-success ms-2" id="equityCount">0</span>
                    </h5>
                </div>
                <div class="card border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0" id="equityTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="sortable" data-sort="code" data-section="equity">
                                            Code <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="sortable" data-sort="name" data-section="equity">
                                            Account Name <i class="bx bx-sort"></i>
                                        </th>
                                        <th class="text-end sortable" data-sort="balance" data-section="equity">
                                            Balance (TZS) <i class="bx bx-sort"></i>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="equityTableBody">
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
                                        <th colspan="2" class="text-end">TOTAL EQUITY:</th>
                                        <th class="text-end text-success" id="equityTotal">0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Liabilities & Equity -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">TOTAL LIABILITIES & EQUITY:</h5>
                        <h4 class="mb-0 text-info fw-bold" id="totalLiabilitiesEquity">0.00</h4>
                    </div>
                </div>
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
                <div class="col-md-4">
                    <h6 class="text-primary mb-3">Assets Details</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="assetsDetailsTable">
                            <thead class="table-primary">
                                <tr>
                                    <th>Account</th>
                                    <th class="text-end">Opening</th>
                                    <th class="text-end">Debits</th>
                                    <th class="text-end">Credits</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody id="assetsDetailsBody">
                                <tr><td colspan="5" class="text-center text-muted">No data</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-danger mb-3">Liabilities Details</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="liabilitiesDetailsTable">
                            <thead class="table-danger">
                                <tr>
                                    <th>Account</th>
                                    <th class="text-end">Opening</th>
                                    <th class="text-end">Debits</th>
                                    <th class="text-end">Credits</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody id="liabilitiesDetailsBody">
                                <tr><td colspan="5" class="text-center text-muted">No data</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-4">
                    <h6 class="text-success mb-3">Equity Details</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="equityDetailsTable">
                            <thead class="table-success">
                                <tr>
                                    <th>Account</th>
                                    <th class="text-end">Opening</th>
                                    <th class="text-end">Debits</th>
                                    <th class="text-end">Credits</th>
                                    <th class="text-end">Balance</th>
                                </tr>
                            </thead>
                            <tbody id="equityDetailsBody">
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
// Advanced Balance Sheet Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.balance-sheet.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.balance-sheet') }}';
    let sortColumn = {};
    let sortDirection = {};
    let allAssets = [];
    let allLiabilities = [];
    let allEquity = [];
    let showDetails = false;
    
    // Initialize sort state for each section
    sortColumn.assets = 'code';
    sortColumn.liabilities = 'code';
    sortColumn.equity = 'code';
    sortDirection.assets = 'asc';
    sortDirection.liabilities = 'asc';
    sortDirection.equity = 'asc';
    
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
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const assets = summary?.total_assets || 0;
        const liabilities = summary?.total_liabilities || 0;
        const equity = summary?.total_equity || 0;
        const totalLiabEquity = summary?.total_liabilities_equity || 0;
        const difference = summary?.difference || 0;
        const isBalanced = summary?.is_balanced || false;
        
        animateValue('sumAssets', 0, assets, 800);
        animateValue('sumLiabilities', 0, liabilities, 800);
        animateValue('sumEquity', 0, equity, 800);
        
        if(document.getElementById('assetsTotal')) document.getElementById('assetsTotal').textContent = formatCurrency(assets);
        if(document.getElementById('liabilitiesTotal')) document.getElementById('liabilitiesTotal').textContent = formatCurrency(liabilities);
        if(document.getElementById('equityTotal')) document.getElementById('equityTotal').textContent = formatCurrency(equity);
        if(document.getElementById('totalLiabilitiesEquity')) document.getElementById('totalLiabilitiesEquity').textContent = formatCurrency(totalLiabEquity);
        
        if(document.getElementById('assetsCount')) document.getElementById('assetsCount').textContent = (summary?.assets_count || 0).toLocaleString();
        if(document.getElementById('liabilitiesCount')) document.getElementById('liabilitiesCount').textContent = (summary?.liabilities_count || 0).toLocaleString();
        if(document.getElementById('equityCount')) document.getElementById('equityCount').textContent = (summary?.equity_count || 0).toLocaleString();
        
        const statusEl = document.getElementById('sumStatus');
        const statusTextEl = document.getElementById('sumStatusText');
        if(statusEl && statusTextEl) {
            if(isBalanced) {
                statusEl.innerHTML = '<span class="badge bg-success">BALANCED</span>';
                statusTextEl.textContent = '✓ Assets = Liabilities + Equity';
            } else {
                statusEl.innerHTML = '<span class="badge bg-danger">UNBALANCED</span>';
                statusTextEl.textContent = `Difference: ${formatCurrency(difference)}`;
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
        
        const assetsBody = document.getElementById('assetsTableBody');
        const liabilitiesBody = document.getElementById('liabilitiesTableBody');
        const equityBody = document.getElementById('equityTableBody');
        
        if(assetsBody) assetsBody.innerHTML = '<tr><td colspan="3" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>';
        if(liabilitiesBody) liabilitiesBody.innerHTML = '<tr><td colspan="3" class="text-center py-5"><div class="spinner-border text-danger"></div></td></tr>';
        if(equityBody) equityBody.innerHTML = '<tr><td colspan="3" class="text-center py-5"><div class="spinner-border text-success"></div></td></tr>';
        
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
                let msg = res.message || 'Failed to load balance sheet';
                
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
                if(assetsBody) assetsBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorHtml}</td></tr>`;
                if(liabilitiesBody) liabilitiesBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorHtml}</td></tr>`;
                if(equityBody) equityBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorHtml}</td></tr>`;
                
                updateSummary({ 
                    total_assets: 0, total_liabilities: 0, total_equity: 0, 
                    total_liabilities_equity: 0, difference: 0, is_balanced: false,
                    assets_count: 0, liabilities_count: 0, equity_count: 0
                });
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            allAssets = res.assets || [];
            allLiabilities = res.liabilities || [];
            allEquity = res.equity || [];
            
            updateSummary(res.summary || {});
            
            renderTable('assets', allAssets);
            renderTable('liabilities', allLiabilities);
            renderTable('equity', allEquity);
            
            if(showDetails) {
                renderDetailsTable('assets', allAssets);
                renderDetailsTable('liabilities', allLiabilities);
                renderDetailsTable('equity', allEquity);
            }
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            const errorMsg = escapeHtml(err.message || 'Network or server error');
            if(assetsBody) assetsBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorMsg}</td></tr>`;
            if(liabilitiesBody) liabilitiesBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorMsg}</td></tr>`;
            if(equityBody) equityBody.innerHTML = `<tr><td colspan="3" class="text-danger text-center py-5">${errorMsg}</td></tr>`;
            updateSummary({ 
                total_assets: 0, total_liabilities: 0, total_equity: 0, 
                total_liabilities_equity: 0, difference: 0, is_balanced: false,
                assets_count: 0, liabilities_count: 0, equity_count: 0
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
    
    ['filterDate', 'filterAccountType', 'showZeroBalance'].forEach(id => {
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
                    renderDetailsTable('assets', allAssets);
                    renderDetailsTable('liabilities', allLiabilities);
                    renderDetailsTable('equity', allEquity);
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
    window.loadBalanceSheet = function() {
        load();
    };
    
    // Initialize
    load();
})();
</script>
@endpush
