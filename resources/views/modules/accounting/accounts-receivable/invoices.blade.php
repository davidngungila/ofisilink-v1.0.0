@extends('layouts.app')

@section('title', 'Invoices Management')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Accounts Receivable - Invoices</h4>
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

    .summary-card[data-type="pending"] {
        border-left-color: #ffc107 !important;
    }

    .summary-card[data-type="overdue"] {
        border-left-color: #dc3545 !important;
    }

    .summary-card[data-type="paid"] {
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

    /* Invoice Modal Scrolling */
    #invoiceModal .modal-dialog {
        max-height: 90vh;
    }

    #invoiceModal .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }

    #invoiceModal .modal-body {
        max-height: calc(90vh - 120px);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 1.5rem;
    }

    #invoiceModal .modal-header {
        flex-shrink: 0;
    }

    #invoiceModal .modal-footer {
        flex-shrink: 0;
    }

    #invoiceModal #invoiceItems {
        max-height: 400px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 10px;
    }

    /* Custom scrollbar for modal */
    #invoiceModal .modal-body::-webkit-scrollbar,
    #invoiceModal #invoiceItems::-webkit-scrollbar {
        width: 8px;
    }

    #invoiceModal .modal-body::-webkit-scrollbar-track,
    #invoiceModal #invoiceItems::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    #invoiceModal .modal-body::-webkit-scrollbar-thumb,
    #invoiceModal #invoiceItems::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    #invoiceModal .modal-body::-webkit-scrollbar-thumb:hover,
    #invoiceModal #invoiceItems::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Responsive adjustments for mobile */
    @media (max-width: 768px) {
        #invoiceModal .modal-dialog {
            max-height: 95vh;
            margin: 0.5rem;
        }

        #invoiceModal .modal-content {
            max-height: 95vh;
        }

        #invoiceModal .modal-body {
            max-height: calc(95vh - 140px);
            padding: 1rem;
        }

        #invoiceModal #invoiceItems {
            max-height: 300px;
        }
    }

    @media (max-width: 576px) {
        #invoiceModal .modal-dialog {
            max-height: 98vh;
            margin: 0.25rem;
        }

        #invoiceModal .modal-body {
            max-height: calc(98vh - 160px);
            padding: 0.75rem;
        }

        #invoiceModal #invoiceItems {
            max-height: 250px;
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
                        <i class="bx bx-file me-2"></i>Invoices Management
                    </h2>
                    <p class="mb-0 opacity-90">Manage customer invoices, payments, and accounts receivable</p>
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
                    <button class="btn btn-light btn-sm" onclick="openInvoiceModal()" title="New Invoice">
                        <i class="bx bx-plus"></i> New Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards with Animations -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="total">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Invoices</h6>
                            <h3 class="mb-0 fw-bold" id="sumTotal">0</h3>
                            <small class="text-muted">Invoices</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-file fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="pending">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Outstanding</h6>
                            <h3 class="mb-0 text-warning fw-bold" id="sumPending">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-time fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="overdue">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Overdue Amount</h6>
                            <h3 class="mb-0 text-danger fw-bold" id="sumOverdue">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-danger bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-error-circle fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="paid">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Paid</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumPaid">0.00</h3>
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
                            <option value="Sent" {{ request('status') == 'Sent' ? 'selected' : '' }}>Sent</option>
                            <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by invoice no, customer, or reference...">
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
                <i class="bx bx-bar-chart-alt-2 me-2"></i>Invoices Trends
            </h6>
        </div>
        <div class="card-body">
            <canvas id="invoicesChart" height="80"></canvas>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-table me-2"></i>Invoices List
                    <span class="badge bg-primary ms-2" id="invoiceCount">0</span>
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
                <table class="table table-hover mb-0" id="invoicesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="invoice_date">
                                Date <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="invoice_no">
                                Invoice No <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="customer_name">
                                Customer <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="due_date">
                                Due Date <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="total_amount">
                                Total (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="paid_amount">
                                Paid (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="balance">
                                Balance (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="status">
                                Status <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="invoicesTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading invoices...</p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Totals:</th>
                            <th class="text-end text-primary" id="footTotal">0.00</th>
                            <th class="text-end text-success" id="footPaid">0.00</th>
                            <th class="text-end text-warning" id="footBalance">0.00</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 invoices
                </div>
                <nav aria-label="Invoices pagination">
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

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="invoiceModalTitle">New Invoice</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="invoiceForm">
                <input type="hidden" id="invoiceId" name="id">
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                            <select class="form-select" id="invoiceCustomer" name="customer_id" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->customer_code }})</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Please select a customer.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="invoiceDate" name="invoice_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="dueDate" name="due_date" value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference No</label>
                            <input type="text" class="form-control" id="referenceNo" name="reference_no">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Amount</label>
                            <input type="number" step="0.01" class="form-control" id="discountAmount" name="discount_amount" value="0">
                        </div>
                    </div>
                    
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Invoice Items</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addInvoiceItem()">
                            <i class="bx bx-plus"></i> Add Item
                        </button>
                    </div>
                    <div id="invoiceItems">
                        <!-- Items will be added here -->
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="invoiceNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Save Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<script>
// Advanced Invoices Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.ar.invoices.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.ar.invoices') }}';
    let page = 1, perPage = 20;
    let sortColumn = 'invoice_date', sortDirection = 'desc';
    let allInvoices = [];
    let chart = null;
    
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
        const total = summary?.total_invoices || 0;
        const pending = summary?.total_balance || 0;
        const overdue = summary?.total_overdue || 0;
        const paid = summary?.total_paid || 0;
        
        animateValue('sumTotal', 0, total, 800);
        animateValue('sumPending', 0, pending, 800);
        animateValue('sumOverdue', 0, overdue, 800);
        animateValue('sumPaid', 0, paid, 800);
        
        if(document.getElementById('footTotal')) document.getElementById('footTotal').textContent = formatCurrency(summary?.total_amount || 0);
        if(document.getElementById('footPaid')) document.getElementById('footPaid').textContent = formatCurrency(paid);
        if(document.getElementById('footBalance')) document.getElementById('footBalance').textContent = formatCurrency(pending);
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
    
    function getStatusBadge(status, isOverdue){
        if(status === 'Paid') return 'bg-success';
        if(isOverdue || status === 'Overdue') return 'bg-danger';
        if(status === 'Partially Paid') return 'bg-info';
        return 'bg-primary';
    }
    
    function renderTable(invoices){
        const tbody = document.getElementById('invoicesTableBody');
        if(!tbody) return;
        
        if(!invoices || !invoices.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No invoices found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = invoices.map((inv, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.02}s">
                <td>
                    <span class="badge bg-light text-dark">${inv.invoice_date_display || inv.invoice_date || ''}</span>
                </td>
                <td><code class="text-primary fw-bold">${escapeHtml(inv.invoice_no || '')}</code></td>
                <td>
                    <div class="fw-medium">${escapeHtml(inv.customer_name || 'N/A')}</div>
                </td>
                <td>
                    <span class="badge bg-light text-dark">${inv.due_date_display || inv.due_date || ''}</span>
                    ${inv.is_overdue ? '<span class="badge bg-danger ms-1">Overdue</span>' : ''}
                </td>
                <td class="text-end">
                    <span class="text-primary fw-semibold">${formatCurrency(inv.total_amount)}</span>
                </td>
                <td class="text-end">
                    <span class="text-success fw-semibold">${formatCurrency(inv.paid_amount)}</span>
                </td>
                <td class="text-end">
                    ${inv.balance > 0 ? `<span class="text-warning fw-semibold">${formatCurrency(inv.balance)}</span>` : '<span class="text-success">0.00</span>'}
                </td>
                <td>
                    <span class="badge ${getStatusBadge(inv.status, inv.is_overdue)}">${escapeHtml(inv.status || '')}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info" onclick="viewInvoice(${inv.id})" title="View">View</button>
                        ${inv.status !== 'Paid' ? `<button class="btn btn-sm btn-warning" onclick="editInvoice(${inv.id})" title="Edit">Edit</button>` : ''}
                        <button class="btn btn-sm btn-success" onclick="recordPayment(${inv.id})" title="Record Payment">Payment</button>
                        <button class="btn btn-sm btn-danger" onclick="exportInvoicePdf(${inv.id})" title="PDF">PDF</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalInvoices){
        const totalPages = Math.ceil(totalInvoices / perPage);
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
    
    function updateChart(invoices){
        if (typeof Chart === 'undefined') return;
        
        if(!chart){
            const ctxEl = document.getElementById('invoicesChart');
            if (!ctxEl) return;
            const ctx = ctxEl.getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Total Amount',
                        data: [],
                        borderColor: 'rgb(0, 123, 255)',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Paid Amount',
                        data: [],
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
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
        
        const grouped = {};
        invoices.forEach(inv => {
            const date = inv.invoice_date || '';
            if(!grouped[date]){
                grouped[date] = { total: 0, paid: 0 };
            }
            grouped[date].total += inv.total_amount || 0;
            grouped[date].paid += inv.paid_amount || 0;
        });
        
        const dates = Object.keys(grouped).sort();
        chart.data.labels = dates;
        chart.data.datasets[0].data = dates.map(d => grouped[d].total);
        chart.data.datasets[1].data = dates.map(d => grouped[d].paid);
        chart.update();
    }
    
    function load(){
        showLoading(true);
        const body = document.getElementById('invoicesTableBody');
        if(!body) return;
        
        body.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading invoices...</p>
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
                let msg = res.message || 'Failed to load invoices';
                
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
                        <td colspan="9" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <div class="mt-2">${errorHtml}</div>
                        </td>
                    </tr>
                `;
                updateSummary({ total_invoices: 0, total_amount: 0, total_paid: 0, total_balance: 0, total_overdue: 0 });
                if(document.getElementById('invoiceCount')) document.getElementById('invoiceCount').textContent = '0';
                if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = '0 invoices';
                
                // Show toast notification
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Validation Error', msg.replace(/<br>/g, ' | '), { duration: 10000, sound: true });
                }
                return; 
            }
            
            allInvoices = res.invoices || [];
            updateSummary(res.summary || {});
            if(document.getElementById('invoiceCount')) document.getElementById('invoiceCount').textContent = (res.summary?.count || 0).toLocaleString();
            if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, res.summary?.count || 0)} of ${(res.summary?.count || 0).toLocaleString()} invoices`;
            
            allInvoices.sort((a, b) => {
                let aVal = a[sortColumn];
                let bVal = b[sortColumn];
                
                if(sortColumn.includes('amount') || sortColumn === 'balance' || sortColumn === 'paid_amount'){
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                }
                
                if(sortDirection === 'asc'){
                    return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                } else {
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                }
            });
            
            renderTable(allInvoices);
            renderPagination(page, res.summary?.count || 0);
            
            if(chart && document.getElementById('chartCard') && document.getElementById('chartCard').style.display !== 'none'){
                updateChart(allInvoices);
            }
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
            updateSummary({ total_invoices: 0, total_amount: 0, total_paid: 0, total_balance: 0, total_overdue: 0 });
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
    
    ['filterFrom', 'filterTo', 'filterCustomer', 'filterStatus'].forEach(id => {
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
    
    if(document.getElementById('toggleChart')) {
        document.getElementById('toggleChart').addEventListener('click', function(){
            const chartCard = document.getElementById('chartCard');
            if(chartCard && chartCard.style.display === 'none'){
                chartCard.style.display = 'block';
                this.innerHTML = '<i class="bx bx-hide"></i> Hide Chart';
                if(allInvoices.length > 0){
                    updateChart(allInvoices);
                }
            } else if(chartCard) {
                chartCard.style.display = 'none';
                this.innerHTML = '<i class="bx bx-show"></i> Show Chart';
            }
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
            
            renderTable(allInvoices);
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
    
    // Initialize
    load();
    
    // Initialize invoice modal with first item
    if(document.getElementById('invoiceItems')) {
        addInvoiceItem();
    }
})();

// Invoice Form Submission
if(document.getElementById('invoiceForm')) {
    document.getElementById('invoiceForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate required fields
        const customerId = document.getElementById('invoiceCustomer')?.value;
        const invoiceDate = document.getElementById('invoiceDate')?.value;
        const dueDate = document.getElementById('dueDate')?.value;
        
        if (!customerId || customerId === '') {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Validation Error', 'Please select a customer', { duration: 5000, sound: true });
            } else {
                alert('Please select a customer');
            }
            document.getElementById('invoiceCustomer')?.classList.add('is-invalid');
            return;
        }
        document.getElementById('invoiceCustomer')?.classList.remove('is-invalid');
        
        if (!invoiceDate) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Validation Error', 'Please select an invoice date', { duration: 5000, sound: true });
            } else {
                alert('Please select an invoice date');
            }
            return;
        }
        
        if (!dueDate) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Validation Error', 'Please select a due date', { duration: 5000, sound: true });
            } else {
                alert('Please select a due date');
            }
            return;
        }
        
        // Collect items
        const items = [];
        let isValid = true;
        let invalidItems = [];
        
        document.querySelectorAll('.invoice-item').forEach((item, index) => {
            const desc = item.querySelector('input[name*="[description]"]')?.value?.trim();
            const qty = item.querySelector('input[name*="[quantity]"]')?.value;
            const price = item.querySelector('input[name*="[unit_price]"]')?.value;
            const tax = item.querySelector('input[name*="[tax_rate]"]')?.value || 0;
            const account = item.querySelector('select[name*="[account_id]"]')?.value;
            
            if (!desc || !qty || !price) {
                isValid = false;
                invalidItems.push(index + 1);
                return;
            }
            
            if (parseFloat(qty) <= 0 || parseFloat(price) < 0) {
                isValid = false;
                invalidItems.push(index + 1);
                return;
            }
            
            items.push({
                description: desc,
                quantity: parseFloat(qty),
                unit_price: parseFloat(price),
                tax_rate: parseFloat(tax) || 0,
                account_id: account || null
            });
        });
        
        if (!isValid || items.length === 0) {
            const msg = items.length === 0 
                ? 'Please add at least one invoice item' 
                : `Please fill in all required fields for items: ${invalidItems.join(', ')}`;
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Validation Error', msg, { duration: 5000, sound: true });
            } else {
                alert(msg);
            }
            return;
        }
        
        const payload = {
            customer_id: customerId,
            invoice_date: invoiceDate,
            due_date: dueDate,
            reference_no: document.getElementById('referenceNo')?.value || '',
            discount_amount: parseFloat(document.getElementById('discountAmount')?.value) || 0,
            notes: document.getElementById('invoiceNotes')?.value || '',
            items: items
        };
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';
        
        try {
            const invoiceId = document.getElementById('invoiceId')?.value;
            const url = invoiceId 
                ? '{{ route("modules.accounting.ar.invoices.update", ":id") }}'.replace(':id', invoiceId)
                : '{{ route("modules.accounting.ar.invoices.store") }}';
            const method = invoiceId ? 'PUT' : 'POST';
            
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
                // Show success toast
                const successMsg = invoiceId 
                    ? (data.message || 'Invoice updated successfully')
                    : (data.message || 'Invoice created successfully');
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', successMsg, { duration: 5000, sound: true });
                } else {
                    alert(successMsg);
                }
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('invoiceModal'));
                if (modal) modal.hide();
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                
                // Reset form
                document.getElementById('invoiceForm').reset();
                document.getElementById('invoiceId').value = '';
                document.getElementById('invoiceItems').innerHTML = '';
                if (typeof invoiceItemCount !== 'undefined') invoiceItemCount = 0;
                document.getElementById('invoiceModalTitle').textContent = 'New Invoice';
                addInvoiceItem();
                
                // Reload invoices
                setTimeout(() => {
                    page = 1;
                    load();
                }, 500);
            } else {
                // Handle validation errors - display exact errors
                let errorMessage = data.message || 'Error creating invoice';
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

function openInvoiceModal() {
    // Reset form
    const form = document.getElementById('invoiceForm');
    if (form) {
        form.reset();
        document.getElementById('invoiceId').value = '';
        document.getElementById('invoiceModalTitle').textContent = 'New Invoice';
        // Clear items
        const itemsContainer = document.getElementById('invoiceItems');
        if (itemsContainer) {
            itemsContainer.innerHTML = '';
            addInvoiceItem();
        }
    }
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
    modal.show();
}

function addInvoiceItem(description = '', quantity = 1, unitPrice = 0, taxRate = 0, accountId = '') {
    const container = document.getElementById('invoiceItems');
    if (!container) return;
    
    const itemCount = container.children.length;
    const itemHtml = `
        <div class="invoice-item border rounded p-3 mb-3" data-index="${itemCount}">
            <div class="row g-2">
                <div class="col-md-5">
                    <label class="form-label small">Description <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="items[${itemCount}][description]" value="${description}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Quantity <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemCount}][quantity]" value="${quantity}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Unit Price <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemCount}][unit_price]" value="${unitPrice}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Tax Rate %</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" name="items[${itemCount}][tax_rate]" value="${taxRate}">
                </div>
                <div class="col-md-1">
                    <label class="form-label small">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeInvoiceItem(this)">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', itemHtml);
}

function removeInvoiceItem(btn) {
    const item = btn.closest('.invoice-item');
    if (item && document.getElementById('invoiceItems').children.length > 1) {
        item.remove();
        recalculateInvoiceTotal();
    }
}

function recalculateInvoiceTotal() {
    // This would calculate totals, but for now just a placeholder
}

async function viewInvoice(id) {
    try {
        const response = await fetch('{{ route("modules.accounting.ar.invoices.show", ":id") }}'.replace(':id', id), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.invoice) {
            const invoice = data.invoice;
            let itemsHtml = '';
            if (invoice.items && invoice.items.length > 0) {
                itemsHtml = invoice.items.map(item => `
                    <tr>
                        <td>${item.description || '-'}</td>
                        <td class="text-end">${(item.quantity || 0).toLocaleString()}</td>
                        <td class="text-end">TZS ${(item.unit_price || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                        <td class="text-end">${(item.tax_rate || 0)}%</td>
                        <td class="text-end">TZS ${((item.quantity || 0) * (item.unit_price || 0) * (1 + (item.tax_rate || 0) / 100)).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    </tr>
                `).join('');
            }
            
            const modalHtml = `
                <div class="modal fade" id="viewInvoiceModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">Invoice Details - ${invoice.invoice_no || 'N/A'}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Customer:</strong> ${invoice.customer?.name || 'N/A'}<br>
                                        <strong>Invoice Date:</strong> ${invoice.invoice_date ? new Date(invoice.invoice_date).toLocaleDateString() : 'N/A'}<br>
                                        <strong>Due Date:</strong> ${invoice.due_date ? new Date(invoice.due_date).toLocaleDateString() : 'N/A'}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Status:</strong> <span class="badge bg-${invoice.status === 'Paid' ? 'success' : invoice.status === 'Overdue' ? 'danger' : 'warning'}">${invoice.status || 'N/A'}</span><br>
                                        <strong>Total Amount:</strong> TZS ${(invoice.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}<br>
                                        <strong>Paid Amount:</strong> TZS ${(invoice.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}<br>
                                        <strong>Balance:</strong> TZS ${(invoice.balance || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}
                                    </div>
                                </div>
                                <h6>Items:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Description</th>
                                                <th class="text-end">Qty</th>
                                                <th class="text-end">Unit Price</th>
                                                <th class="text-end">Tax</th>
                                                <th class="text-end">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${itemsHtml || '<tr><td colspan="5" class="text-center">No items</td></tr>'}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger" onclick="exportInvoicePdf(${id})">
                                    <i class="bx bxs-file-pdf"></i> Export PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            const existingModal = document.getElementById('viewInvoiceModal');
            if (existingModal) existingModal.remove();
            
            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewInvoiceModal'));
            modal.show();
            
            // Clean up on hide
            document.getElementById('viewInvoiceModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to load invoice details', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to load invoice details');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error loading invoice: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error loading invoice: ' + error.message);
        }
    }
}

async function editInvoice(id) {
    try {
        const response = await fetch('{{ route("modules.accounting.ar.invoices.show", ":id") }}'.replace(':id', id), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.invoice) {
            const invoice = data.invoice;
            
            // Check if elements exist before setting values
            const invoiceIdEl = document.getElementById('invoiceId');
            const invoiceCustomerEl = document.getElementById('invoiceCustomer');
            const invoiceDateEl = document.getElementById('invoiceDate');
            const dueDateEl = document.getElementById('dueDate');
            const invoiceItemsEl = document.getElementById('invoiceItems');
            const invoiceModalTitleEl = document.getElementById('invoiceModalTitle');
            
            if (!invoiceIdEl || !invoiceCustomerEl || !invoiceDateEl || !dueDateEl || !invoiceItemsEl) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', 'Invoice form elements not found. Please refresh the page.', { duration: 5000, sound: true });
                } else {
                    alert('Invoice form elements not found. Please refresh the page.');
                }
                return;
            }
            
            // Populate form
            invoiceIdEl.value = invoice.id;
            invoiceCustomerEl.value = invoice.customer_id;
            invoiceDateEl.value = invoice.invoice_date ? invoice.invoice_date.split('T')[0] : '';
            dueDateEl.value = invoice.due_date ? invoice.due_date.split('T')[0] : '';
            
            // Clear existing items
            invoiceItemsEl.innerHTML = '';
            if (typeof invoiceItemCount !== 'undefined') invoiceItemCount = 0;
            
            // Add items
            if (invoice.items && invoice.items.length > 0) {
                invoice.items.forEach(item => {
                    addInvoiceItem(item.description, item.quantity, item.unit_price, item.tax_rate || 0, item.account_id || '');
                });
            } else {
                addInvoiceItem();
            }
            
            // Update modal title
            if (invoiceModalTitleEl) {
                invoiceModalTitleEl.textContent = 'Edit Invoice - ' + invoice.invoice_no;
            }
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('invoiceModal'));
            modal.show();
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to load invoice', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to load invoice');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error loading invoice: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error loading invoice: ' + error.message);
        }
    }
}

function recordPayment(id) {
    // Redirect to payments page with invoice_id parameter
    // The payments page will auto-open the payment modal
    window.location.href = '{{ route("modules.accounting.ar.payments") }}?invoice_id=' + id;
}

function exportInvoicePdf(id) {
    window.open('{{ route("modules.accounting.ar.invoices.pdf", ":id") }}'.replace(':id', id), '_blank');
}
</script>
@endpush
