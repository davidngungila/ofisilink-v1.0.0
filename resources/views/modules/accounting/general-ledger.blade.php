@extends('layouts.app')

@section('title', 'General Ledger')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">General Ledger</h4>
</div>
@endsection

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section with Gradient Background -->
    <div class="card border-0 shadow-sm mb-4" style="background:#940000;">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-book-open me-2"></i>General Ledger
                    </h2>
                    <p class="mb-0 opacity-90">Complete accounting ledger with running balances and transaction history</p>
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

    <!-- Summary Cards with Animations -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="debit">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Debit</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumDebit">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-trending-up fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="credit">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Credit</h6>
                            <h3 class="mb-0 text-danger fw-bold" id="sumCredit">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-trending-down fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="balance">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Balance</h6>
                            <h3 class="mb-0 fw-bold" id="sumBalance">0.00</h3>
                            <small class="text-muted" id="balanceLabel">Dr - Cr</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-calculator fs-4 text-primary"></i>
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
                    <!-- Quick Date Presets -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label small text-muted fw-semibold">Quick Date Range</label>
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <button type="button" class="btn btn-outline-primary date-preset" data-days="0">Today</button>
                            <button type="button" class="btn btn-outline-primary date-preset" data-days="7">Last 7 Days</button>
                            <button type="button" class="btn btn-outline-primary date-preset" data-days="30">Last 30 Days</button>
                            <button type="button" class="btn btn-outline-primary date-preset" data-days="90">Last 3 Months</button>
                            <button type="button" class="btn btn-outline-primary date-preset" data-days="365">Last Year</button>
                            <button type="button" class="btn btn-outline-primary" id="clearDates">Clear</button>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">From Date</label>
                        <input type="date" class="form-control form-control-sm" id="filterFrom" value="{{ request('date_from', date('Y-m-01')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">To Date</label>
                        <input type="date" class="form-control form-control-sm" id="filterTo" value="{{ request('date_to', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Account</label>
                        <select class="form-select form-select-sm" id="filterAccount">
                            <option value="">All Accounts</option>
                            @foreach($accounts as $account)
                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->code }} - {{ $account->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Type</label>
                        <select class="form-select form-select-sm" id="filterType">
                            <option value="">All Types</option>
                            <option value="Debit" {{ request('type') == 'Debit' ? 'selected' : '' }}>Debit</option>
                            <option value="Credit" {{ request('type') == 'Credit' ? 'selected' : '' }}>Credit</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by description, reference, account code or name...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Clear search">
                                <i class="bx bx-x"></i>
                        </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="card border-0 shadow-sm mb-4" id="chartCard" style="display: none;">
        <div class="card-header bg-white border-bottom">
            <h6 class="mb-0 fw-semibold">
                <i class="bx bx-bar-chart-alt-2 me-2"></i>Transaction Trends
            </h6>
        </div>
        <div class="card-body">
            <canvas id="ledgerChart" height="80"></canvas>
    </div>
</div>

    <!-- Data Table Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-table me-2"></i>Ledger Entries
                    <span class="badge bg-primary ms-2" id="entryCount">0</span>
                </h6>
                <div class="d-flex gap-2 align-items-center mt-2 mt-md-0">
                    <label class="small text-muted me-2">Per page:</label>
                    <select class="form-select form-select-sm" style="width: auto;" id="perPageSelect">
                        <option value="10">10</option>
                        <option value="20" selected>20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <button class="btn btn-sm btn-outline-primary" id="toggleChart">
                        <i class="bx bx-show"></i> Chart
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
                <div class="table-responsive">
                <table class="table table-hover mb-0" id="ledgerTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="date">
                                Date <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="account">
                                Account <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="reference">
                                Reference <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="description">
                                Description <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="debit">
                                Debit (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="credit">
                                Credit (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="balance">
                                Running Balance <i class="bx bx-sort"></i>
                            </th>
                                <th>Source</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                    <tbody id="ledgerTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading ledger data...</p>
                                </td>
                            </tr>
                        </tbody>
                    <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Totals:</th>
                            <th class="text-end text-success" id="footDebit">0.00</th>
                            <th class="text-end text-danger" id="footCredit">0.00</th>
                                <th colspan="3"></th>
                            </tr>
                        </tfoot>
                    </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 entries
                </div>
                <nav aria-label="Ledger pagination">
                    <ul class="pagination pagination-sm mb-0" id="pagination">
                        <!-- Pagination will be generated by JavaScript -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Reference Details Section -->
    @include('components.reference-details', ['glAccounts' => $glAccounts, 'cashBoxes' => $cashBoxes])
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="display: none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<!-- Transaction Detail Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Transaction Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="transactionContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
.summary-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-left: 4px solid transparent !important;
}

.summary-card[data-type="debit"] {
    border-left-color: #28a745 !important;
}

.summary-card[data-type="credit"] {
    border-left-color: #dc3545 !important;
}

.summary-card[data-type="balance"] {
    border-left-color: #007bff !important;
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

.date-preset.active {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: white !important;
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

.badge {
    font-weight: 500;
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

.ledger-balance {
    font-weight: bold;
}
.ledger-debit {
    color: #28a745;
}
.ledger-credit {
    color: #dc3545;
}

@media (max-width: 768px) {
    .btn-group {
        flex-wrap: wrap;
    }
    
    .btn-group .btn {
        flex: 1 1 auto;
        min-width: 80px;
    }
}
</style>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<script>
(function(){
    const endpoint = '{{ route('modules.accounting.general-ledger.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.general-ledger') }}';
    let page = 1, perPage = 20;
    let sortColumn = 'date', sortDirection = 'desc';
    let allEntries = [];
    let chart = null;
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            date_from: document.getElementById('filterFrom').value || '',
            date_to: document.getElementById('filterTo').value || '',
            account_id: document.getElementById('filterAccount').value || '',
            type: document.getElementById('filterType').value || '',
            q: document.getElementById('filterQ').value || '',
            page, per_page: perPage
        };
    }
    
    function showLoading(show = true){
        document.getElementById('loadingOverlay').style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const debit = summary?.total_debit || 0;
        const credit = summary?.total_credit || 0;
        const balance = summary?.balance || 0;
        
        animateValue('sumDebit', 0, debit, 800);
        animateValue('sumCredit', 0, credit, 800);
        animateValue('sumBalance', 0, balance, 800);
        
        document.getElementById('footDebit').textContent = formatCurrency(debit);
        document.getElementById('footCredit').textContent = formatCurrency(credit);
        
        const balanceEl = document.getElementById('sumBalance');
        const balanceLabel = document.getElementById('balanceLabel');
        balanceEl.textContent = formatCurrency(Math.abs(balance));
        balanceEl.className = balance >= 0 ? 'mb-0 text-success fw-bold' : 'mb-0 text-danger fw-bold';
        balanceLabel.textContent = balance >= 0 ? 'Debit Balance' : 'Credit Balance';
    }
    
    function animateValue(id, start, end, duration){
        const element = document.getElementById(id);
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
    
    function getAccountBadge(accountType){
        const badges = {
            'Asset': 'bg-success',
            'Liability': 'bg-danger',
            'Equity': 'bg-info',
            'Revenue': 'bg-primary',
            'Expense': 'bg-warning'
        };
        return badges[accountType] || 'bg-secondary';
    }
    
    function getSourceIcon(source){
        const icons = {
            'petty_cash': 'bx-wallet',
            'imprest': 'bx-money',
            'payroll': 'bx-credit-card',
            'journal': 'bx-book',
            'invoice': 'bx-file',
            'bill': 'bx-receipt',
            'general': 'bx-file-blank'
        };
        return icons[source] || 'bx-file';
    }
    
    function renderTable(entries){
        const tbody = document.getElementById('ledgerTableBody');
        
        if(!entries || !entries.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No entries found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = entries.map((e, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.02}s">
                <td>
                    <span class="badge bg-light text-dark">${e.date_display || e.date || ''}</span>
                </td>
                <td>
                    <div class="fw-medium">
                        <span class="badge ${getAccountBadge(e.account_type)} me-1">${escapeHtml(e.account_code || '')}</span>
                        ${escapeHtml(e.account_name || '')}
                    </div>
                </td>
                <td><code class="text-primary">${escapeHtml(String(e.reference || '-'))}</code></td>
                <td>
                    <div class="fw-medium">${escapeHtml(e.description || '')}</div>
                    ${e.created_by ? `<small class="text-muted">By: ${escapeHtml(e.created_by)}</small>` : ''}
                </td>
                <td class="text-end">
                    ${e.debit > 0 ? `<span class="text-success fw-semibold ledger-debit">${formatCurrency(e.debit)}</span>` : '<span class="text-muted">—</span>'}
                </td>
                <td class="text-end">
                    ${e.credit > 0 ? `<span class="text-danger fw-semibold ledger-credit">${formatCurrency(e.credit)}</span>` : '<span class="text-muted">—</span>'}
                </td>
                <td class="text-end ledger-balance text-${e.balance >= 0 ? 'success' : 'danger'}">
                    ${formatCurrency(e.balance || 0)}
                </td>
                <td>
                    <span class="badge bg-secondary">${escapeHtml(e.source || 'general')}</span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info" onclick="viewTransaction(${e.id})" title="View Details">
                        <i class="bx bx-show"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalEntries){
        const totalPages = Math.ceil(totalEntries / perPage);
        const pagination = document.getElementById('pagination');
        
        if(totalPages <= 1){
            pagination.innerHTML = '';
            return;
        }
        
        let html = '';
        
        // Previous button
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
        </li>`;
        
        // Page numbers
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
        
        // Next button
        html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
        </li>`;
        
        pagination.innerHTML = html;
        
        // Attach click handlers
        pagination.querySelectorAll('a[data-page]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                page = parseInt(link.dataset.page);
                load();
            });
        });
    }
    
    function updateChart(entries){
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        if(!chart){
            const ctxEl = document.getElementById('ledgerChart');
            if (!ctxEl) {
                console.warn('Ledger chart canvas not found');
                return;
            }
            const ctx = ctxEl.getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Debit',
                        data: [],
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Credit',
                        data: [],
                        borderColor: 'rgb(220, 53, 69)',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        }
        
        // Group by date
        const grouped = {};
        entries.forEach(e => {
            const date = e.date || '';
            if(!grouped[date]){
                grouped[date] = { debit: 0, credit: 0 };
            }
            grouped[date].debit += e.debit || 0;
            grouped[date].credit += e.credit || 0;
        });
        
        const dates = Object.keys(grouped).sort();
        chart.data.labels = dates;
        chart.data.datasets[0].data = dates.map(d => grouped[d].debit);
        chart.data.datasets[1].data = dates.map(d => grouped[d].credit);
        chart.update();
    }
    
    function load(){
        showLoading(true);
        const body = document.getElementById('ledgerTableBody');
        body.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading ledger data...</p>
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
                const msg = res.message || 'Failed to load ledger data';
                body.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">${escapeHtml(msg)}</p>
                        </td>
                    </tr>
                `;
                updateSummary({ total_debit: 0, total_credit: 0, balance: 0 });
                document.getElementById('entryCount').textContent = '0';
                document.getElementById('rowsInfo').textContent = '0 entries';
                return; 
            }
            
            allEntries = res.entries || [];
            updateSummary(res.summary || {});
            document.getElementById('entryCount').textContent = (res.summary?.count || 0).toLocaleString();
            document.getElementById('rowsInfo').textContent = `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, res.summary?.count || 0)} of ${(res.summary?.count || 0).toLocaleString()} entries`;
            
            // Sort entries
            allEntries.sort((a, b) => {
                let aVal = a[sortColumn];
                let bVal = b[sortColumn];
                
                if(sortColumn === 'debit' || sortColumn === 'credit' || sortColumn === 'balance'){
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                }
                
                if(sortDirection === 'asc'){
                    return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                } else {
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                }
            });
            
            renderTable(allEntries);
            renderPagination(page, res.summary?.count || 0);
            
            if(chart && document.getElementById('chartCard').style.display !== 'none'){
                updateChart(allEntries);
            }
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            body.innerHTML = `
                <tr>
                    <td colspan="9" class="text-danger text-center py-5">
                        <i class="bx bx-error-circle fs-1"></i>
                        <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                    </td>
                </tr>
            `;
            updateSummary({ total_debit: 0, total_credit: 0, balance: 0 });
        })
        .finally(() => {
            showLoading(false);
        });
    }
    
    // Event Listeners
    document.getElementById('btn-refresh').addEventListener('click', () => { 
        page = 1; 
        load(); 
    });
    
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
    
    document.getElementById('btn-export-excel').addEventListener('click', () => {
        // Export to Excel functionality (can be implemented)
        alert('Excel export feature coming soon!');
    });
    
    // Date presets
    document.querySelectorAll('.date-preset').forEach(btn => {
        btn.addEventListener('click', function(){
            const days = parseInt(this.dataset.days);
            if(isNaN(days)) return;
            
            const today = new Date();
            const fromDate = new Date(today);
            
            if(days === 0){
                fromDate.setHours(0,0,0,0);
            } else {
                fromDate.setDate(today.getDate() - days);
            }
            
            document.getElementById('filterFrom').value = fromDate.toISOString().split('T')[0];
            document.getElementById('filterTo').value = today.toISOString().split('T')[0];
            
            document.querySelectorAll('.date-preset').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            page = 1;
            load();
        });
    });
    
    document.getElementById('clearDates').addEventListener('click', () => {
        document.getElementById('filterFrom').value = '';
        document.getElementById('filterTo').value = '';
        document.querySelectorAll('.date-preset').forEach(b => b.classList.remove('active'));
        page = 1;
        load();
    });
    
    document.getElementById('filterFrom').addEventListener('change', () => { 
        page = 1; 
        load(); 
    });
    document.getElementById('filterTo').addEventListener('change', () => { 
        page = 1; 
        load(); 
    });
    document.getElementById('filterAccount').addEventListener('change', () => { 
        page = 1; 
        load(); 
    });
    document.getElementById('filterType').addEventListener('change', () => { 
        page = 1; 
        load(); 
    });
    document.getElementById('filterQ').addEventListener('input', () => { 
        page = 1; 
        debounce(load, 300)(); 
    });
    document.getElementById('clearSearch').addEventListener('click', () => {
        document.getElementById('filterQ').value = '';
        page = 1;
        load();
    });
    document.getElementById('perPageSelect').addEventListener('change', function(){
        perPage = parseInt(this.value);
        page = 1;
        load();
    });
    
    document.getElementById('toggleChart').addEventListener('click', function(){
        const chartCard = document.getElementById('chartCard');
        if(chartCard.style.display === 'none'){
            chartCard.style.display = 'block';
            this.innerHTML = '<i class="bx bx-hide"></i> Hide Chart';
            if(allEntries.length > 0){
                updateChart(allEntries);
            }
        } else {
            chartCard.style.display = 'none';
            this.innerHTML = '<i class="bx bx-show"></i> Show Chart';
        }
    });
    
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
            
            // Update UI
            document.querySelectorAll('.sortable').forEach(t => {
                t.classList.remove('active');
                const icon = t.querySelector('i');
                icon.className = 'bx bx-sort';
            });
            
            this.classList.add('active');
            const icon = this.querySelector('i');
            icon.className = sortDirection === 'asc' ? 'bx bx-sort-up' : 'bx bx-sort-down';
            
            // Re-sort and render
            renderTable(allEntries);
        });
    });
    
    // Filter collapse toggle
    document.getElementById('toggleFilters').addEventListener('click', function(){
        const icon = document.getElementById('filterIcon');
        const isCollapsed = document.getElementById('filterCollapse').classList.contains('show');
        icon.className = isCollapsed ? 'bx bx-chevron-down' : 'bx bx-chevron-up';
    });
    
    let t = null;
    function debounce(fn, ms){ 
        return () => { 
            clearTimeout(t); 
            t = setTimeout(fn, ms); 
        }; 
    }
    
    // Transaction detail modal function
    window.viewTransaction = function(id) {
    // Clean up any existing modals
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('transactionModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const content = document.getElementById('transactionContent');
        
        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>';
        
        modal.show();
        
        // Ensure modal is responsive after show
        modalElement.addEventListener('shown.bs.modal', function() {
            $(this).css('z-index', 1050);
            $('.modal-backdrop').css('z-index', 1040);
            $(this).find('.modal-content').css('pointer-events', 'auto');
        }, { once: true });
    
    // In a real implementation, you would fetch transaction details
    setTimeout(() => {
                const entry = allEntries.find(e => e.id === id);
                if(entry){
                    content.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Date:</strong> ${entry.date_display || entry.date}
                            </div>
                            <div class="col-md-6">
                                <strong>Reference:</strong> ${escapeHtml(entry.reference || '-')}
                            </div>
                            <div class="col-md-12 mt-2">
                                <strong>Account:</strong> ${escapeHtml(entry.account_code || '')} - ${escapeHtml(entry.account_name || '')}
                            </div>
                            <div class="col-md-12 mt-2">
                                <strong>Description:</strong> ${escapeHtml(entry.description || '')}
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>Debit:</strong> <span class="text-success">${formatCurrency(entry.debit || 0)}</span>
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>Credit:</strong> <span class="text-danger">${formatCurrency(entry.credit || 0)}</span>
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>Balance:</strong> <span class="fw-bold">${formatCurrency(entry.balance || 0)}</span>
                            </div>
                            <div class="col-md-6 mt-2">
                                <strong>Source:</strong> ${escapeHtml(entry.source || 'general')}
                            </div>
                            <div class="col-md-12 mt-2">
                                <strong>Created By:</strong> ${escapeHtml(entry.created_by || 'System')}
                            </div>
                        </div>
                    `;
                } else {
                    content.innerHTML = '<div class="alert alert-info">Transaction details not available</div>';
                }
    }, 500);
        }, 100);
    };
    
    // Initialize
    load();
})();
</script>
@endpush
