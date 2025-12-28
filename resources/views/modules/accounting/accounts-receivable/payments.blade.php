@extends('layouts.app')

@section('title', 'Invoice Payments')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Accounts Receivable - Invoice Payments</h4>
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

    .summary-card[data-type="amount"] {
        border-left-color: #28a745 !important;
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
    <!-- Header Section with Gradient Background -->
    <div class="card border-0 shadow-sm mb-4" style="background:#940000;">
        <div class="card-body text-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="fw-bold mb-2 text-white">
                        <i class="bx bx-money me-2"></i>Invoice Payments
                    </h2>
                    <p class="mb-0 opacity-90">Record and manage customer invoice payments</p>
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
                    <button class="btn btn-light btn-sm" onclick="openPaymentModal()" title="Record Payment">
                        <i class="bx bx-plus"></i> Record Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="total">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Payments</h6>
                            <h3 class="mb-0 fw-bold" id="sumTotal">0</h3>
                            <small class="text-muted">Payments</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-money fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="amount">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Amount</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumAmount">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="amount">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">This Month</h6>
                            <h3 class="mb-0 text-info fw-bold" id="sumMonth">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-calendar fs-4 text-info"></i>
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
                        <label class="form-label small text-muted fw-semibold">Invoice</label>
                        <select class="form-select form-select-sm" id="filterInvoice">
                            <option value="">All Invoices</option>
                            @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}" {{ request('invoice_id') == $invoice->id ? 'selected' : '' }}>
                                {{ $invoice->invoice_no }} - {{ $invoice->customer->name ?? 'N/A' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Payment Method</label>
                        <select class="form-select form-select-sm" id="filterMethod">
                            <option value="">All Methods</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Credit Card">Credit Card</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by payment no, invoice no, or customer...">
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
                    <i class="bx bx-table me-2"></i>Payments List
                    <span class="badge bg-primary ms-2" id="paymentCount">0</span>
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
                <table class="table table-hover mb-0" id="paymentsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="payment_date">
                                Date <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="payment_no">
                                Payment No <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="invoice_no">
                                Invoice No <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="customer_name">
                                Customer <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="amount">
                                Amount (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="payment_method">
                                Method <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                    <tbody id="paymentsTableBody">
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading payments...</p>
                                </td>
                            </tr>
                    </tbody>
                    <tfoot class="table-light">
                            <tr>
                            <th colspan="4" class="text-end">Totals:</th>
                            <th class="text-end text-success" id="footAmount">0.00</th>
                            <th colspan="2"></th>
                            </tr>
                    </tfoot>
                    </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 payments
                </div>
                <nav aria-label="Payments pagination">
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

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Record Invoice Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Invoice <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentInvoice" name="invoice_id" required>
                                <option value="">Select Invoice</option>
                                @foreach($invoices as $invoice)
                                <option value="{{ $invoice->id }}" data-balance="{{ $invoice->balance }}">
                                    {{ $invoice->invoice_no }} - {{ $invoice->customer->name ?? 'N/A' }} (Balance: TZS {{ number_format($invoice->balance, 2) }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="paymentDate" name="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="paymentAmount" name="amount" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentMethod" name="payment_method" required>
                                <option value="">Select Method</option>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Mobile Money">Mobile Money</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference No</label>
                            <input type="text" class="form-control" id="paymentReference" name="reference_no">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Account</label>
                            <select class="form-select" id="paymentBankAccount" name="bank_account_id">
                                <option value="">Select Bank Account</option>
                                @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }} - {{ $bank->account_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="paymentNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const token = '{{ csrf_token() }}';

// Advanced Payments Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.ar.payments.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.ar.payments') }}';
    let page = 1, perPage = 20;
    let sortColumn = 'payment_date', sortDirection = 'desc';
    let allPayments = [];
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            invoice_id: document.getElementById('filterInvoice')?.value || '',
            payment_method: document.getElementById('filterMethod')?.value || '',
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
        const total = summary?.total_payments || 0;
        const amount = summary?.total_amount || 0;
        const month = summary?.month_amount || 0;
        
        animateValue('sumTotal', 0, total, 800);
        animateValue('sumAmount', 0, amount, 800);
        animateValue('sumMonth', 0, month, 800);
        
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
    
    function renderTable(payments){
        const tbody = document.getElementById('paymentsTableBody');
        if(!tbody) return;
        
        if(!payments || !payments.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No payments found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = payments.map((p, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.02}s">
                <td>
                    <span class="badge bg-light text-dark">${p.payment_date_display || p.payment_date || ''}</span>
                </td>
                <td><code class="text-primary fw-bold">${escapeHtml(p.payment_no || '')}</code></td>
                <td><code class="text-info fw-bold">${escapeHtml(p.invoice_no || 'N/A')}</code></td>
                <td>
                    <div class="fw-medium">${escapeHtml(p.customer_name || 'N/A')}</div>
                </td>
                <td class="text-end">
                    <span class="text-success fw-semibold">${formatCurrency(p.amount)}</span>
                </td>
                <td>
                    <span class="badge bg-info">${escapeHtml(p.payment_method || '')}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info" onclick="viewPayment(${p.id})" title="View">View</button>
                        <button class="btn btn-sm btn-danger" onclick="exportPaymentPdf(${p.id})" title="PDF">PDF</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalPayments){
        const totalPages = Math.ceil(totalPayments / perPage);
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
        const body = document.getElementById('paymentsTableBody');
        if(!body) return;
        
        body.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading payments...</p>
                </td>
            </tr>
        `;
        
        fetch(endpoint, {
            method:'POST', 
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':token,
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
                let msg = res.message || 'Failed to load payments';
                
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
                        <td colspan="7" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <div class="mt-2">${errorHtml}</div>
                        </td>
                    </tr>
                `;
                updateSummary({ total_payments: 0, total_amount: 0, month_amount: 0 });
                if(document.getElementById('paymentCount')) document.getElementById('paymentCount').textContent = '0';
                if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = '0 payments';
                
                // Show toast notification
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            allPayments = res.payments || [];
            updateSummary(res.summary || {});
            if(document.getElementById('paymentCount')) document.getElementById('paymentCount').textContent = (res.summary?.count || 0).toLocaleString();
            if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, res.summary?.count || 0)} of ${(res.summary?.count || 0).toLocaleString()} payments`;
            
            allPayments.sort((a, b) => {
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
            
            renderTable(allPayments);
            renderPagination(page, res.summary?.count || 0);
        })
        .catch((err)=>{
            console.error('Fetch error:', err);
            if(body) {
                body.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                        </td>
                    </tr>
                `;
            }
            updateSummary({ total_payments: 0, total_amount: 0, month_amount: 0 });
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
    
    ['filterFrom', 'filterTo', 'filterInvoice', 'filterMethod'].forEach(id => {
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
            
            renderTable(allPayments);
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
    
    // Payment form
    if(document.getElementById('paymentForm')) {
        document.getElementById('paymentForm').addEventListener('submit', async function(e){
            e.preventDefault();
            
            // Get form values
            const invoiceId = document.getElementById('paymentInvoice')?.value;
            const paymentDate = document.getElementById('paymentDate')?.value;
            const amount = document.getElementById('paymentAmount')?.value;
            const paymentMethod = document.getElementById('paymentMethod')?.value;
            const referenceNo = document.getElementById('paymentReference')?.value || '';
            const bankAccountId = document.getElementById('paymentBankAccount')?.value || '';
            const notes = document.getElementById('paymentNotes')?.value || '';
            
            // Validate required fields
            if (!invoiceId || invoiceId === '') {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', 'Please select an invoice', { duration: 5000, sound: true });
                } else {
                    alert('Please select an invoice');
                }
                document.getElementById('paymentInvoice')?.classList.add('is-invalid');
                return;
            }
            document.getElementById('paymentInvoice')?.classList.remove('is-invalid');
            
            if (!paymentDate) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', 'Please select a payment date', { duration: 5000, sound: true });
                } else {
                    alert('Please select a payment date');
                }
                return;
            }
            
            if (!amount || parseFloat(amount) <= 0) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', 'Please enter a valid payment amount', { duration: 5000, sound: true });
                } else {
                    alert('Please enter a valid payment amount');
                }
                return;
            }
            
            if (!paymentMethod || paymentMethod === '') {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', 'Please select a payment method', { duration: 5000, sound: true });
                } else {
                    alert('Please select a payment method');
                }
                return;
            }
            
            const payload = {
                invoice_id: invoiceId,
                payment_date: paymentDate,
                amount: parseFloat(amount),
                payment_method: paymentMethod,
                reference_no: referenceNo,
                bank_account_id: bankAccountId || null,
                notes: notes
            };
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';
            
            try {
                const response = await fetch('{{ route("modules.accounting.ar.payments.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.success('Success', data.message || 'Payment recorded successfully', { duration: 5000, sound: true });
                    } else {
                        alert(data.message || 'Payment recorded successfully');
                    }
                    
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    
                    // Reset form
                    document.getElementById('paymentForm').reset();
                    
                    setTimeout(() => {
                        page = 1;
                        load();
                    }, 500);
                } else {
                    // Handle validation errors - display exact errors
                    let errorMessage = data.message || 'Error recording payment';
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
    
    // Set max amount when invoice selected
    // Function to update payment amount based on selected invoice
    function updatePaymentAmountFromInvoice() {
        const paymentInvoiceSelect = document.getElementById('paymentInvoice');
        const paymentAmountInput = document.getElementById('paymentAmount');
        
        if (paymentInvoiceSelect && paymentAmountInput) {
            const selectedOption = paymentInvoiceSelect.options[paymentInvoiceSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                const balance = parseFloat(selectedOption.dataset.balance) || 0;
                paymentAmountInput.max = balance;
                paymentAmountInput.value = balance > 0 ? balance : '';
            } else {
                paymentAmountInput.max = '';
                paymentAmountInput.value = '';
            }
        }
    }
    
    if(document.getElementById('paymentInvoice')) {
        document.getElementById('paymentInvoice').addEventListener('change', updatePaymentAmountFromInvoice);
    }
    
    let t = null;
    function debounce(fn, ms){ 
        return () => { 
            clearTimeout(t); 
            t = setTimeout(fn, ms); 
        }; 
    }
    
    // Initialize
    load();
    
    // Check if invoice_id is in URL and open payment modal
    const urlParams = new URLSearchParams(window.location.search);
    const invoiceId = urlParams.get('invoice_id');
    if (invoiceId) {
        // Set the invoice filter
        const filterInvoiceSelect = document.getElementById('filterInvoice');
        if (filterInvoiceSelect) {
            filterInvoiceSelect.value = invoiceId;
            // Reload data with filter applied
            page = 1;
            load();
        }
        
        // Wait for page to fully load and data to be loaded
        setTimeout(() => {
            const paymentInvoiceSelect = document.getElementById('paymentInvoice');
            if (paymentInvoiceSelect) {
                // Check if invoice exists in the select options
                const invoiceOption = Array.from(paymentInvoiceSelect.options).find(opt => opt.value === invoiceId);
                
                if (invoiceOption) {
                    paymentInvoiceSelect.value = invoiceId;
                    // Update payment amount based on invoice balance
                    updatePaymentAmountFromInvoice();
                    
                    // Open payment modal
                    const paymentModal = document.getElementById('paymentModal');
                    if (paymentModal) {
                        const modal = new bootstrap.Modal(paymentModal, {
                            backdrop: 'static',
                            keyboard: false
                        });
                        modal.show();
                        
                        // Clean up URL after opening modal (keep filter)
                        const newUrl = window.location.pathname;
                        window.history.replaceState({}, '', newUrl);
                    }
                } else {
                    // Invoice not found in options, show error
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.warning('Warning', 'Invoice not found or already paid. Please select an invoice manually.', { duration: 5000, sound: true });
                    }
                    // Clean up URL
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, '', newUrl);
                }
            }
        }, 1500); // Increased timeout to ensure page and data are fully loaded
    }
})();

function openPaymentModal() {
    const modalElement = document.getElementById('paymentModal');
    const modal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',
        keyboard: false
    });
    document.getElementById('paymentForm').reset();
    modal.show();
}

async function viewPayment(id) {
    try {
        const response = await fetch(`{{ route('modules.accounting.ar.payments.show', ':id') }}`.replace(':id', id), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.payment) {
            const payment = data.payment;
            const invoice = payment.invoice || {};
            const customer = invoice.customer || {};
            
            const modalHtml = `
                <div class="modal fade" id="viewPaymentModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Payment Details - ${payment.payment_no || 'N/A'}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Payment Information</h6>
                                        <p class="mb-1"><strong>Payment No:</strong> ${payment.payment_no || 'N/A'}</p>
                                        <p class="mb-1"><strong>Payment Date:</strong> ${payment.payment_date ? new Date(payment.payment_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'}) : 'N/A'}</p>
                                        <p class="mb-1"><strong>Amount:</strong> <span class="text-success fw-bold">TZS ${(payment.amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</span></p>
                                        <p class="mb-1"><strong>Payment Method:</strong> <span class="badge bg-info">${payment.payment_method || 'N/A'}</span></p>
                                        <p class="mb-1"><strong>Reference No:</strong> ${payment.reference_no || '-'}</p>
                                        ${payment.bank_account ? `<p class="mb-1"><strong>Bank Account:</strong> ${payment.bank_account.name || 'N/A'}</p>` : ''}
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Invoice Information</h6>
                                        <p class="mb-1"><strong>Invoice No:</strong> <code class="text-primary">${invoice.invoice_no || 'N/A'}</code></p>
                                        <p class="mb-1"><strong>Invoice Date:</strong> ${invoice.invoice_date ? new Date(invoice.invoice_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'}) : 'N/A'}</p>
                                        <p class="mb-1"><strong>Due Date:</strong> ${invoice.due_date ? new Date(invoice.due_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'}) : 'N/A'}</p>
                                        <p class="mb-1"><strong>Invoice Total:</strong> TZS ${(invoice.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</p>
                                        <p class="mb-1"><strong>Paid Amount:</strong> TZS ${(invoice.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</p>
                                        <p class="mb-1"><strong>Balance:</strong> <span class="text-danger fw-bold">TZS ${(invoice.balance || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</span></p>
                                        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-warning">${invoice.status || 'N/A'}</span></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <h6 class="text-muted mb-2">Customer Information</h6>
                                        <p class="mb-1"><strong>Name:</strong> ${customer.name || 'N/A'}</p>
                                        ${customer.address ? `<p class="mb-1"><strong>Address:</strong> ${customer.address}</p>` : ''}
                                        ${customer.phone ? `<p class="mb-1"><strong>Phone:</strong> ${customer.phone}</p>` : ''}
                                        ${customer.email ? `<p class="mb-1"><strong>Email:</strong> ${customer.email}</p>` : ''}
                                    </div>
                                </div>
                                ${payment.notes ? `<div class="mb-3"><strong>Notes:</strong><p class="text-muted">${payment.notes}</p></div>` : ''}
                                <div class="text-muted small">
                                    <p class="mb-0"><strong>Recorded by:</strong> ${payment.created_by || 'N/A'}</p>
                                    <p class="mb-0"><strong>Recorded on:</strong> ${payment.created_at ? new Date(payment.created_at).toLocaleString('en-US') : 'N/A'}</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-success" onclick="exportPaymentPdf(${payment.id}); bootstrap.Modal.getInstance(document.getElementById('viewPaymentModal')).hide();">
                                    <i class="bx bxs-file-pdf me-1"></i>Export PDF
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existingModal = document.getElementById('viewPaymentModal');
            if (existingModal) existingModal.remove();
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modal = new bootstrap.Modal(document.getElementById('viewPaymentModal'));
            modal.show();
            
            document.getElementById('viewPaymentModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to load payment', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to load payment');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error loading payment: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error loading payment: ' + error.message);
        }
    }
}

function exportPaymentPdf(id) {
    window.open(`{{ route('modules.accounting.ar.payments.pdf', ':id') }}`.replace(':id', id), '_blank');
}
</script>
@endpush
