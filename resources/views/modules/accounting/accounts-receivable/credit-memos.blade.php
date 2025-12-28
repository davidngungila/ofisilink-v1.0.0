@extends('layouts.app')

@section('title', 'Credit Memos Management')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Accounts Receivable - Credit Memos</h4>
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

    .summary-card[data-type="draft"] {
        border-left-color: #ffc107 !important;
    }

    .summary-card[data-type="posted"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="amount"] {
        border-left-color: #17a2b8 !important;
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
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm mb-4" style="background:#940000;">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-file-blank me-2"></i>Credit Memos Management
                    </h2>
                    <p class="mb-0 opacity-90">Manage customer credit memos, returns, and adjustments</p>
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
                    <button class="btn btn-light btn-sm" onclick="openCreditMemoModal()" title="New Credit Memo">
                        <i class="bx bx-plus"></i> New Credit Memo
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
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Memos</h6>
                            <h3 class="mb-0 fw-bold" id="sumTotal">0</h3>
                            <small class="text-muted">Memos</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-file-blank fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="amount">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Amount</h6>
                            <h3 class="mb-0 text-info fw-bold" id="sumAmount">0.00</h3>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="draft">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Draft Amount</h6>
                            <h3 class="mb-0 text-warning fw-bold" id="sumDraft">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-edit fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="posted">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Posted Amount</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumPosted">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-check-circle fs-4 text-success"></i>
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
                        <label class="form-label small text-muted fw-semibold">Customer</label>
                        <select class="form-select form-select-sm" id="filterCustomer">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                            <option value="Posted" {{ request('status') == 'Posted' ? 'selected' : '' }}>Posted</option>
                            <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Type</label>
                        <select class="form-select form-select-sm" id="filterType">
                            <option value="">All Types</option>
                            <option value="Return" {{ request('type') == 'Return' ? 'selected' : '' }}>Return</option>
                            <option value="Discount" {{ request('type') == 'Discount' ? 'selected' : '' }}>Discount</option>
                            <option value="Adjustment" {{ request('type') == 'Adjustment' ? 'selected' : '' }}>Adjustment</option>
                            <option value="Write-off" {{ request('type') == 'Write-off' ? 'selected' : '' }}>Write-off</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by memo no, customer, invoice, or reason...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Clear search">
                                <i class="bx bx-x"></i>
                            </button>
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
                    <i class="bx bx-table me-2"></i>Credit Memos List
                    <span class="badge bg-primary ms-2" id="memoCount">0</span>
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
                <table class="table table-hover mb-0" id="creditMemosTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="memo_date">
                                Date <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="memo_no">
                                Memo No <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="customer_name">
                                Customer <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="invoice_no">
                                Invoice No <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="type">
                                Type <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="amount">
                                Amount (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="status">
                                Status <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="creditMemosTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading credit memos...</p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Totals:</th>
                            <th class="text-end text-primary" id="footAmount">0.00</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 credit memos
                </div>
                <nav aria-label="Credit memos pagination">
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

<!-- Credit Memo Modal -->
<div class="modal fade" id="creditMemoModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="creditMemoModalTitle">New Credit Memo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="creditMemoForm">
                <input type="hidden" id="creditMemoId" name="id">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select class="form-select" id="creditMemoCustomer" name="customer_id" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->customer_code }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a customer.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Invoice (Optional)</label>
                            <select class="form-select" id="creditMemoInvoice" name="invoice_id">
                                <option value="">Select Invoice (Optional)</option>
                                @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}" data-customer="{{ $invoice->customer_id }}">
                                    {{ $invoice->invoice_no }} - {{ $invoice->customer->name ?? 'N/A' }} (Balance: TZS {{ number_format($invoice->balance, 2) }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Memo Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="creditMemoDate" name="memo_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="creditMemoType" name="type" required>
                                <option value="">Select Type</option>
                                <option value="Return">Return</option>
                                <option value="Discount">Discount</option>
                                <option value="Adjustment">Adjustment</option>
                                <option value="Write-off">Write-off</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="creditMemoAmount" name="amount" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Reason</label>
                            <textarea class="form-control" id="creditMemoReason" name="reason" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Save Credit Memo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Advanced Credit Memos Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.ar.credit-memos.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.ar.credit-memos') }}';
    let page = 1, perPage = 20;
    let sortColumn = 'memo_date', sortDirection = 'desc';
    let allCreditMemos = [];
    
    // Store page reference for external access
    window.creditMemosPage = { value: 1 };
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            customer_id: document.getElementById('filterCustomer')?.value || '',
            status: document.getElementById('filterStatus')?.value || '',
            type: document.getElementById('filterType')?.value || '',
            date_from: document.getElementById('filterFrom')?.value || '',
            date_to: document.getElementById('filterTo')?.value || '',
            q: document.getElementById('filterQ')?.value || '',
            page, per_page: perPage
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const total = summary?.total_memos || 0;
        const amount = summary?.total_amount || 0;
        const draft = summary?.draft_amount || 0;
        const posted = summary?.posted_amount || 0;
        
        animateValue('sumTotal', 0, total, 800);
        animateValue('sumAmount', 0, amount, 800);
        animateValue('sumDraft', 0, draft, 800);
        animateValue('sumPosted', 0, posted, 800);
        
        if(document.getElementById('footAmount')) document.getElementById('footAmount').textContent = formatCurrency(amount);
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
                if(id === 'sumTotal') {
                    element.textContent = Math.round(end).toLocaleString();
                } else {
                    element.textContent = formatCurrency(end);
                }
                clearInterval(timer);
            } else {
                if(id === 'sumTotal') {
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
    
    function getStatusBadge(status){
        if(status === 'Posted') return 'bg-success';
        if(status === 'Cancelled') return 'bg-danger';
        return 'bg-warning';
    }
    
    function getTypeBadge(type){
        const badges = {
            'Return': 'bg-info',
            'Discount': 'bg-primary',
            'Adjustment': 'bg-secondary',
            'Write-off': 'bg-dark'
        };
        return badges[type] || 'bg-secondary';
    }
    
    function renderTable(memos){
        const tbody = document.getElementById('creditMemosTableBody');
        if(!tbody) return;
        
        if(!memos || !memos.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No credit memos found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = memos.map((m, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.02}s">
                <td>
                    <span class="badge bg-light text-dark">${m.memo_date_display || m.memo_date || ''}</span>
                </td>
                <td><code class="text-primary fw-bold">${escapeHtml(m.memo_no || '')}</code></td>
                <td>
                    <div class="fw-medium">${escapeHtml(m.customer_name || 'N/A')}</div>
                </td>
                <td><code class="text-info">${escapeHtml(m.invoice_no || '-')}</code></td>
                <td>
                    <span class="badge ${getTypeBadge(m.type)}">${escapeHtml(m.type || '')}</span>
                </td>
                <td class="text-end">
                    <span class="text-primary fw-semibold">${formatCurrency(m.amount)}</span>
                </td>
                <td>
                    <span class="badge ${getStatusBadge(m.status)}">${escapeHtml(m.status || '')}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info" onclick="viewCreditMemo(${m.id})" title="View">
                            <i class="bx bx-show"></i>
                        </button>
                        ${m.status === 'Draft' ? `<button class="btn btn-sm btn-warning" onclick="editCreditMemo(${m.id})" title="Edit">
                            <i class="bx bx-edit"></i>
                        </button>` : ''}
                        <button class="btn btn-sm btn-danger" onclick="exportCreditMemoPdf(${m.id})" title="PDF">
                            <i class="bx bxs-file-pdf"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalMemos){
        const totalPages = Math.ceil(totalMemos / perPage);
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
        const body = document.getElementById('creditMemosTableBody');
        if(!body) return;
        
        body.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading credit memos...</p>
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
                let msg = res.message || 'Failed to load credit memos';
                
                // Display validation errors if present
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
                
                // Create error display with proper HTML rendering
                const errorHtml = msg.includes('<br>') ? msg : escapeHtml(msg);
                body.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <div class="mt-2">${errorHtml}</div>
                        </td>
                    </tr>
                `;
                updateSummary({ total_memos: 0, total_amount: 0, draft_amount: 0, posted_amount: 0 });
                if(document.getElementById('memoCount')) document.getElementById('memoCount').textContent = '0';
                if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = '0 credit memos';
                
                // Show toast notification
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            allCreditMemos = res.credit_memos || [];
            updateSummary(res.summary || {});
            if(document.getElementById('memoCount')) document.getElementById('memoCount').textContent = (res.summary?.count || 0).toLocaleString();
            if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, res.summary?.count || 0)} of ${(res.summary?.count || 0).toLocaleString()} credit memos`;
            
            allCreditMemos.sort((a, b) => {
                let aVal = a[sortColumn];
                let bVal = b[sortColumn];
                
                if(sortColumn === 'amount'){
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                }
                
                if(sortDirection === 'asc'){
                    return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                } else {
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                }
            });
            
            renderTable(allCreditMemos);
            renderPagination(page, res.summary?.count || 0);
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            if(body) {
                body.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                        </td>
                    </tr>
                `;
            }
            updateSummary({ total_memos: 0, total_amount: 0, draft_amount: 0, posted_amount: 0 });
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
            
            if(document.getElementById('filterFrom')) document.getElementById('filterFrom').value = fromDate.toISOString().split('T')[0];
            if(document.getElementById('filterTo')) document.getElementById('filterTo').value = today.toISOString().split('T')[0];
            
            document.querySelectorAll('.date-preset').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            page = 1;
            load();
        });
    });
    
    if(document.getElementById('clearDates')) {
        document.getElementById('clearDates').addEventListener('click', () => {
            if(document.getElementById('filterFrom')) document.getElementById('filterFrom').value = '';
            if(document.getElementById('filterTo')) document.getElementById('filterTo').value = '';
            document.querySelectorAll('.date-preset').forEach(b => b.classList.remove('active'));
            page = 1;
            load();
        });
    }
    
    ['filterFrom', 'filterTo', 'filterCustomer', 'filterStatus', 'filterType'].forEach(id => {
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
            
            renderTable(allCreditMemos);
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
    
    // Make load function globally accessible with page reset capability
    window.loadCreditMemos = function(resetPage = false) {
        if (resetPage) {
            page = 1;
            window.creditMemosPage.value = 1;
        }
        load();
    };
    
    // Initialize
    load();
})();

// Credit Memo Form Submission
if(document.getElementById('creditMemoForm')) {
    document.getElementById('creditMemoForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const creditMemoId = document.getElementById('creditMemoId')?.value;
        const customerId = document.getElementById('creditMemoCustomer')?.value;
        const memoDate = document.getElementById('creditMemoDate')?.value;
        const type = document.getElementById('creditMemoType')?.value;
        const amount = document.getElementById('creditMemoAmount')?.value;
        
        if (!customerId || customerId === '') {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Validation Error', 'Please select a customer', { duration: 5000, sound: true });
            } else {
                alert('Please select a customer');
            }
            return;
        }
        
        if (!memoDate) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Validation Error', 'Please select a memo date', { duration: 5000, sound: true });
            } else {
                alert('Please select a memo date');
            }
            return;
        }
        
        if (!type || type === '') {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Validation Error', 'Please select a type', { duration: 5000, sound: true });
            } else {
                alert('Please select a type');
            }
            return;
        }
        
        if (!amount || parseFloat(amount) <= 0) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Validation Error', 'Please enter a valid amount', { duration: 5000, sound: true });
            } else {
                alert('Please enter a valid amount');
            }
            return;
        }
        
        const payload = {
            customer_id: customerId,
            invoice_id: document.getElementById('creditMemoInvoice')?.value || null,
            memo_date: memoDate,
            type: type,
            amount: parseFloat(amount),
            reason: document.getElementById('creditMemoReason')?.value || ''
        };
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';
        
        try {
            const url = creditMemoId 
                ? '{{ route("modules.accounting.ar.credit-memos.update", ":id") }}'.replace(':id', creditMemoId)
                : '{{ route("modules.accounting.ar.credit-memos.store") }}';
            const method = creditMemoId ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            
            const data = await response.json();
            
            if (data.success) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', data.message || (creditMemoId ? 'Credit memo updated successfully' : 'Credit memo created successfully'), { duration: 5000, sound: true });
                } else {
                    alert(data.message || (creditMemoId ? 'Credit memo updated successfully' : 'Credit memo created successfully'));
                }
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('creditMemoModal'));
                if (modal) modal.hide();
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                
                document.getElementById('creditMemoForm').reset();
                document.getElementById('creditMemoId').value = '';
                document.getElementById('creditMemoModalTitle').textContent = 'New Credit Memo';
                
                setTimeout(() => {
                    if (typeof window.loadCreditMemos === 'function') {
                        window.loadCreditMemos(true);
                    } else {
                        location.reload();
                    }
                }, 500);
            } else {
                // Handle validation errors - display exact errors
                let errorMessage = data.message || 'Error creating credit memo';
                let errorTitle = 'Validation Error';
                
                if (data.errors) {
                    const errorMessages = [];
                    Object.keys(data.errors).forEach(key => {
                        const fieldName = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                        if (Array.isArray(data.errors[key])) {
                            data.errors[key].forEach(err => {
                                errorMessages.push(`${fieldName}: ${err}`);
                            });
                        } else {
                            errorMessages.push(`${fieldName}: ${data.errors[key]}`);
                        }
                    });
                    if (errorMessages.length > 0) {
                        errorTitle = 'Validation Failed';
                        errorMessage = errorMessages.join('<br>');
                    }
                }
                
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error(errorTitle, errorMessage, { duration: 10000, sound: true });
                } else {
                    alert(errorTitle + '\n\n' + errorMessage.replace(/<br>/g, '\n'));
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', 'Error: ' + error.message, { duration: 7000, sound: true });
            } else {
                alert('Error: ' + error.message);
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

// Filter invoices by customer
if(document.getElementById('creditMemoCustomer')) {
    document.getElementById('creditMemoCustomer').addEventListener('change', function(){
        const customerId = this.value;
        const invoiceSelect = document.getElementById('creditMemoInvoice');
        if(!invoiceSelect) return;
        
        Array.from(invoiceSelect.options).forEach(option => {
            if(option.value === '') return;
            const optionCustomerId = option.dataset.customer;
            option.style.display = (!customerId || optionCustomerId === customerId) ? '' : 'none';
        });
    });
}

function openCreditMemoModal() {
    const form = document.getElementById('creditMemoForm');
    if (form) {
        form.reset();
        document.getElementById('creditMemoId').value = '';
        document.getElementById('creditMemoModalTitle').textContent = 'New Credit Memo';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('creditMemoModal'));
    modal.show();
}

async function viewCreditMemo(id) {
    try {
        const response = await fetch(`{{ route('modules.accounting.ar.credit-memos.show', ':id') }}`.replace(':id', id), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.creditMemo) {
            const memo = data.creditMemo;
            const invoice = memo.invoice || {};
            const customer = memo.customer || {};
            
            const modalHtml = `
                <div class="modal fade" id="viewCreditMemoModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Credit Memo Details - ${memo.memo_no || 'N/A'}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Credit Memo Information</h6>
                                        <p class="mb-1"><strong>Memo No:</strong> <code class="text-primary">${memo.memo_no || 'N/A'}</code></p>
                                        <p class="mb-1"><strong>Memo Date:</strong> ${memo.memo_date ? new Date(memo.memo_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'}) : 'N/A'}</p>
                                        <p class="mb-1"><strong>Type:</strong> <span class="badge bg-info">${memo.type || 'N/A'}</span></p>
                                        <p class="mb-1"><strong>Amount:</strong> <span class="text-primary fw-bold">TZS ${(memo.amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</span></p>
                                        <p class="mb-1"><strong>Status:</strong> <span class="badge ${memo.status === 'Posted' ? 'bg-success' : memo.status === 'Cancelled' ? 'bg-danger' : 'bg-warning'}">${memo.status || 'N/A'}</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Customer Information</h6>
                                        <p class="mb-1"><strong>Name:</strong> ${customer.name || 'N/A'}</p>
                                        ${customer.address ? `<p class="mb-1"><strong>Address:</strong> ${customer.address}</p>` : ''}
                                        ${customer.phone ? `<p class="mb-1"><strong>Phone:</strong> ${customer.phone}</p>` : ''}
                                        ${customer.email ? `<p class="mb-1"><strong>Email:</strong> ${customer.email}</p>` : ''}
                                        ${invoice.invoice_no ? `
                                            <h6 class="text-muted mb-2 mt-3">Invoice Information</h6>
                                            <p class="mb-1"><strong>Invoice No:</strong> <code class="text-info">${invoice.invoice_no || 'N/A'}</code></p>
                                            <p class="mb-1"><strong>Invoice Date:</strong> ${invoice.invoice_date ? new Date(invoice.invoice_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'}) : 'N/A'}</p>
                                            <p class="mb-1"><strong>Invoice Total:</strong> TZS ${(invoice.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</p>
                                            <p class="mb-1"><strong>Balance:</strong> TZS ${(invoice.balance || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</p>
                                        ` : ''}
                                    </div>
                                </div>
                                ${memo.reason ? `<div class="mb-3"><strong>Reason:</strong><p class="text-muted">${memo.reason}</p></div>` : ''}
                                <div class="text-muted small">
                                    <p class="mb-0"><strong>Created by:</strong> ${memo.created_by || 'N/A'}</p>
                                    <p class="mb-0"><strong>Created on:</strong> ${memo.created_at ? new Date(memo.created_at).toLocaleString('en-US') : 'N/A'}</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" onclick="exportCreditMemoPdf(${memo.id}); bootstrap.Modal.getInstance(document.getElementById('viewCreditMemoModal')).hide();">
                                    <i class="bx bxs-file-pdf me-1"></i>Export PDF
                                </button>
                                ${memo.status === 'Draft' ? `
                                    <button type="button" class="btn btn-warning" onclick="editCreditMemo(${memo.id}); bootstrap.Modal.getInstance(document.getElementById('viewCreditMemoModal')).hide();">
                                        <i class="bx bx-edit me-1"></i>Edit
                                    </button>
                                ` : ''}
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existingModal = document.getElementById('viewCreditMemoModal');
            if (existingModal) existingModal.remove();
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modal = new bootstrap.Modal(document.getElementById('viewCreditMemoModal'));
            modal.show();
            
            document.getElementById('viewCreditMemoModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to load credit memo', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to load credit memo');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error loading credit memo: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error loading credit memo: ' + error.message);
        }
    }
}

async function editCreditMemo(id) {
    try {
        const response = await fetch(`{{ route('modules.accounting.ar.credit-memos.show', ':id') }}`.replace(':id', id), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.creditMemo) {
            const memo = data.creditMemo;
            
            const creditMemoIdEl = document.getElementById('creditMemoId');
            const creditMemoCustomerEl = document.getElementById('creditMemoCustomer');
            const creditMemoInvoiceEl = document.getElementById('creditMemoInvoice');
            const creditMemoDateEl = document.getElementById('creditMemoDate');
            const creditMemoTypeEl = document.getElementById('creditMemoType');
            const creditMemoAmountEl = document.getElementById('creditMemoAmount');
            const creditMemoReasonEl = document.getElementById('creditMemoReason');
            const creditMemoModalTitleEl = document.getElementById('creditMemoModalTitle');
            
            if (!creditMemoIdEl || !creditMemoCustomerEl || !creditMemoDateEl || !creditMemoTypeEl || !creditMemoAmountEl) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', 'Form elements not found', { duration: 5000, sound: true });
                } else {
                    alert('Form elements not found');
                }
                return;
            }
            
            creditMemoIdEl.value = memo.id;
            creditMemoCustomerEl.value = memo.customer_id || '';
            if (creditMemoInvoiceEl) creditMemoInvoiceEl.value = memo.invoice?.id || '';
            creditMemoDateEl.value = memo.memo_date || '';
            creditMemoTypeEl.value = memo.type || '';
            creditMemoAmountEl.value = memo.amount || '';
            if (creditMemoReasonEl) creditMemoReasonEl.value = memo.reason || '';
            
            if (creditMemoModalTitleEl) {
                creditMemoModalTitleEl.textContent = 'Edit Credit Memo - ' + memo.memo_no;
            }
            
            // Trigger customer change to filter invoices
            if (creditMemoCustomerEl && creditMemoInvoiceEl) {
                creditMemoCustomerEl.dispatchEvent(new Event('change'));
            }
            
            const modal = new bootstrap.Modal(document.getElementById('creditMemoModal'));
            modal.show();
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to load credit memo', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to load credit memo');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error loading credit memo: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error loading credit memo: ' + error.message);
        }
    }
}

function exportCreditMemoPdf(id) {
    window.open(`{{ route('modules.accounting.ar.credit-memos.pdf', ':id') }}`.replace(':id', id), '_blank');
}
</script>
@endpush
