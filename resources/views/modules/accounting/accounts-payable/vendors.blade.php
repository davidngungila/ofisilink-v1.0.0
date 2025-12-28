@extends('layouts.app')

@section('title', 'Vendors Management')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Accounts Payable - Vendors</h4>
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
                        <i class="bx bx-store me-2"></i>Vendors Management
                    </h2>
                    <p class="mb-0 opacity-90">Manage supplier profiles, contacts, and payment tracking</p>
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
                    <button class="btn btn-light btn-sm" onclick="openVendorModal()" title="New Vendor">
                        <i class="bx bx-plus"></i> New Vendor
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
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Vendors</h6>
                            <h3 class="mb-0 fw-bold" id="sumTotal">0</h3>
                            <small class="text-muted">Vendors</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-store fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="active">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Active Vendors</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumActive">0</h3>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="outstanding">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Outstanding</h6>
                            <h3 class="mb-0 text-warning fw-bold" id="sumOutstanding">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-money fs-4 text-warning"></i>
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
                    <!-- Quick Filters -->
                    <div class="col-md-12 mb-3">
                        <label class="form-label small text-muted fw-semibold">Quick Filters</label>
                        <div class="btn-group btn-group-sm w-100" role="group">
                            <button type="button" class="btn btn-outline-primary quick-filter" data-filter="all">All Vendors</button>
                            <button type="button" class="btn btn-outline-primary quick-filter" data-filter="active">Active Only</button>
                            <button type="button" class="btn btn-outline-primary quick-filter" data-filter="with-outstanding">With Outstanding</button>
                            <button type="button" class="btn btn-outline-primary quick-filter" data-filter="overdue">Overdue</button>
                            <button type="button" class="btn btn-outline-primary" id="clearFilters">Clear All</button>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <!-- Currency Filter -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Currency</label>
                        <select class="form-select form-select-sm" id="filterCurrency">
                            <option value="">All Currencies</option>
                            <option value="TZS">TZS</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                        </select>
                    </div>

                    <!-- Outstanding Range -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Outstanding Range</label>
                        <select class="form-select form-select-sm" id="filterOutstanding">
                            <option value="">All</option>
                            <option value="zero">Zero Balance</option>
                            <option value="low">Low (0 - 100,000)</option>
                            <option value="medium">Medium (100,000 - 1,000,000)</option>
                            <option value="high">High (1,000,000+)</option>
                        </select>
                    </div>

                    <!-- Payment Terms -->
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Payment Terms</label>
                        <select class="form-select form-select-sm" id="filterPaymentTerms">
                            <option value="">All</option>
                            <option value="0-15">0-15 Days</option>
                            <option value="16-30">16-30 Days</option>
                            <option value="31-60">31-60 Days</option>
                            <option value="60+">60+ Days</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="col-md-12">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by name, code, email, phone, tax ID, or address...">
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
                <i class="bx bx-bar-chart-alt-2 me-2"></i>Vendors Overview
            </h6>
        </div>
        <div class="card-body">
            <canvas id="vendorsChart" height="80"></canvas>
        </div>
    </div>

    <!-- Data Table Section with Scrollable Container -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-table me-2"></i>Vendors List
                    <span class="badge bg-primary ms-2" id="vendorCount">0</span>
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
            <!-- Scrollable Table Container -->
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-hover mb-0" id="vendorsTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="sortable" data-sort="vendor_code">
                                Code <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="name">
                                Name <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="contact_person">
                                Contact Person <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="email">
                                Email <i class="bx bx-sort"></i>
                            </th>
                            <th>Phone</th>
                            <th class="text-end sortable" data-sort="outstanding">
                                Outstanding (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-end sortable" data-sort="overdue">
                                Overdue (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="is_active">
                                Status <i class="bx bx-sort"></i>
                            </th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="vendorsTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading vendors...</p>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light sticky-bottom">
                        <tr>
                            <th colspan="5" class="text-end">Totals:</th>
                            <th class="text-end text-warning" id="footOutstanding">0.00</th>
                            <th class="text-end text-danger" id="footOverdue">0.00</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 vendors
                </div>
                <nav aria-label="Vendors pagination">
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

<!-- Vendor Modal -->
<div class="modal fade" id="vendorModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="vendorModalTitle">New Vendor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="vendorForm">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <input type="hidden" id="vendorId" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Vendor Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="vendorName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vendor Code</label>
                            <input type="text" class="form-control" id="vendorCode" name="vendor_code" readonly>
                            <small class="text-muted">Auto-generated</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="vendorContactPerson" name="contact_person">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="vendorEmail" name="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" id="vendorPhone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" class="form-control" id="vendorMobile" name="mobile">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax ID</label>
                            <input type="text" class="form-control" id="vendorTaxId" name="tax_id">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Credit Limit</label>
                            <input type="number" step="0.01" class="form-control" id="vendorCreditLimit" name="credit_limit" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Terms (Days)</label>
                            <input type="number" class="form-control" id="vendorPaymentTerms" name="payment_terms_days" value="30">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Currency</label>
                            <select class="form-select" id="vendorCurrency" name="currency">
                                <option value="TZS">TZS</option>
                                <option value="USD">USD</option>
                                <option value="EUR">EUR</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" id="vendorAddress" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="vendorNotes" name="notes" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="vendorIsActive" name="is_active" checked>
                                <label class="form-check-label" for="vendorIsActive">
                                    Active Vendor
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Vendor
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Vendor Modal -->
<div class="modal fade" id="viewVendorModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Vendor Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewVendorContent" style="max-height: 70vh; overflow-y: auto;">
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

.summary-card[data-type="total"] {
    border-left-color: #007bff !important;
}

.summary-card[data-type="active"] {
    border-left-color: #28a745 !important;
}

.summary-card[data-type="outstanding"] {
    border-left-color: #ffc107 !important;
}

.summary-card[data-type="overdue"] {
    border-left-color: #dc3545 !important;
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

.table-responsive {
    scrollbar-width: thin;
    scrollbar-color: #940000 #f1f1f1;
}

.table-responsive::-webkit-scrollbar {
    width: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #940000;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #7a0000;
}

.table-hover tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.15s ease;
}

.sticky-top {
    position: sticky;
    top: 0;
    z-index: 10;
    background: white;
}

.sticky-bottom {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: white;
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

.quick-filter.active {
    background-color: #007bff !important;
    border-color: #007bff !important;
    color: white !important;
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

/* Fix modal stacking */
.modal {
    z-index: 1050 !important;
}

.modal.show {
    z-index: 1050 !important;
    display: block !important;
    pointer-events: auto !important;
}

.modal-backdrop {
    z-index: 1040 !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
}

.modal-content {
    pointer-events: auto !important;
    position: relative;
    z-index: 1 !important;
}

body.modal-open {
    overflow: hidden !important;
    padding-right: 0 !important;
}
</style>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<script>
const token = '{{ csrf_token() }}';

// Advanced Vendors Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.ap.vendors.data') }}';
    let page = 1, perPage = 20;
    let sortColumn = 'name', sortDirection = 'asc';
    let allVendors = [];
    let chart = null;
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            status: document.getElementById('filterStatus').value || '',
            currency: document.getElementById('filterCurrency').value || '',
            outstanding: document.getElementById('filterOutstanding').value || '',
            payment_terms: document.getElementById('filterPaymentTerms').value || '',
            q: document.getElementById('filterQ').value || '',
            page, per_page: perPage
        };
    }
    
    function showLoading(show = true){
        document.getElementById('loadingOverlay').style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const total = summary?.total_vendors || 0;
        const active = summary?.active_vendors || 0;
        const outstanding = summary?.total_outstanding || 0;
        const overdue = summary?.total_overdue || 0;
        
        animateValue('sumTotal', 0, total, 800);
        animateValue('sumActive', 0, active, 800);
        animateValue('sumOutstanding', 0, outstanding, 800);
        animateValue('sumOverdue', 0, overdue, 800);
        
        document.getElementById('footOutstanding').textContent = formatCurrency(outstanding);
        document.getElementById('footOverdue').textContent = formatCurrency(overdue);
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
                if(id === 'sumTotal' || id === 'sumActive') {
                    element.textContent = Math.round(end).toLocaleString();
                } else {
                    element.textContent = formatCurrency(end);
                }
                clearInterval(timer);
            } else {
                if(id === 'sumTotal' || id === 'sumActive') {
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
    
    function renderTable(vendors){
        const tbody = document.getElementById('vendorsTableBody');
        
        if(!vendors || !vendors.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No vendors found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = vendors.map((v, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.02}s">
                <td><code class="text-primary fw-bold">${escapeHtml(v.vendor_code || '')}</code></td>
                <td>
                    <div class="fw-medium">${escapeHtml(v.name || '')}</div>
                </td>
                <td>${escapeHtml(v.contact_person || '-')}</td>
                <td>${escapeHtml(v.email || '-')}</td>
                <td>${escapeHtml(v.phone || '-')}</td>
                <td class="text-end">
                    ${v.outstanding > 0 ? `<span class="text-warning fw-semibold">${formatCurrency(v.outstanding)}</span>` : '<span class="text-success">0.00</span>'}
                </td>
                <td class="text-end">
                    ${v.overdue > 0 ? `<span class="text-danger fw-semibold">${formatCurrency(v.overdue)}</span>` : '<span class="text-muted">—</span>'}
                </td>
                <td>
                    <span class="badge bg-${v.is_active ? 'success' : 'secondary'}">${v.is_active ? 'Active' : 'Inactive'}</span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info" onclick="viewVendor(${v.id})" title="View Details">
                        <i class="bx bx-show"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editVendor(${v.id})" title="Edit">
                        <i class="bx bx-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="viewBills(${v.id})" title="View Bills">
                        <i class="bx bx-file"></i>
                    </button>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalVendors){
        const totalPages = Math.ceil(totalVendors / perPage);
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
    
    function updateChart(vendors){
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        if(!chart){
            const ctxEl = document.getElementById('vendorsChart');
            if (!ctxEl) {
                console.warn('Vendors chart canvas not found');
                return;
            }
            const ctx = ctxEl.getContext('2d');
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Outstanding',
                        data: [],
                        backgroundColor: 'rgba(255, 193, 7, 0.6)',
                        borderColor: 'rgb(255, 193, 7)',
                        borderWidth: 1
                    }, {
                        label: 'Overdue',
                        data: [],
                        backgroundColor: 'rgba(220, 53, 69, 0.6)',
                        borderColor: 'rgb(220, 53, 69)',
                        borderWidth: 1
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
        
        // Top 10 vendors by outstanding
        const topVendors = vendors
            .sort((a, b) => (b.outstanding || 0) - (a.outstanding || 0))
            .slice(0, 10);
        
        chart.data.labels = topVendors.map(v => v.name);
        chart.data.datasets[0].data = topVendors.map(v => v.outstanding || 0);
        chart.data.datasets[1].data = topVendors.map(v => v.overdue || 0);
        chart.update();
    }
    
    function load(){
        showLoading(true);
        const body = document.getElementById('vendorsTableBody');
        body.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading vendors...</p>
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
                const msg = res.message || 'Failed to load vendors';
                body.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">${escapeHtml(msg)}</p>
                        </td>
                    </tr>
                `;
                updateSummary({ total_vendors: 0, active_vendors: 0, total_outstanding: 0, total_overdue: 0 });
                document.getElementById('vendorCount').textContent = '0';
                document.getElementById('rowsInfo').textContent = '0 vendors';
                return; 
            }
            
            allVendors = res.vendors || [];
            updateSummary(res.summary || {});
            document.getElementById('vendorCount').textContent = (res.summary?.count || 0).toLocaleString();
            document.getElementById('rowsInfo').textContent = `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, res.summary?.count || 0)} of ${(res.summary?.count || 0).toLocaleString()} vendors`;
            
            // Sort vendors
            allVendors.sort((a, b) => {
                let aVal = a[sortColumn];
                let bVal = b[sortColumn];
                
                if(sortColumn === 'outstanding' || sortColumn === 'overdue'){
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                }
                
                if(sortDirection === 'asc'){
                    return aVal > bVal ? 1 : aVal < bVal ? -1 : 0;
                } else {
                    return aVal < bVal ? 1 : aVal > bVal ? -1 : 0;
                }
            });
            
            renderTable(allVendors);
            renderPagination(page, res.summary?.count || 0);
            
            if(chart && document.getElementById('chartCard').style.display !== 'none'){
                updateChart(allVendors);
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
            updateSummary({ total_vendors: 0, active_vendors: 0, total_outstanding: 0, total_overdue: 0 });
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
        window.open('{{ route("modules.accounting.ap.vendors") }}?' + p.toString(), '_blank');
    });
    
    document.getElementById('btn-export-excel').addEventListener('click', () => {
        const params = qs();
        const p = new URLSearchParams();
        Object.keys(params).forEach(key => {
            if(params[key] && key !== 'page' && key !== 'per_page') {
                p.append(key, params[key]);
            }
        });
        p.append('export', 'excel');
        window.location.href = '{{ route("modules.accounting.ap.vendors") }}?' + p.toString();
    });
    
    // Quick filter buttons
    document.querySelectorAll('.quick-filter').forEach(btn => {
        btn.addEventListener('click', function(){
            const filter = this.dataset.filter;
            
            // Reset all filters first
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterCurrency').value = '';
            document.getElementById('filterOutstanding').value = '';
            document.getElementById('filterPaymentTerms').value = '';
            document.getElementById('filterQ').value = '';
            
            // Apply quick filter
            if(filter === 'active'){
                document.getElementById('filterStatus').value = 'active';
            } else if(filter === 'with-outstanding'){
                document.getElementById('filterOutstanding').value = 'low';
            } else if(filter === 'overdue'){
                // This would need backend support
                document.getElementById('filterOutstanding').value = 'high';
            }
            
            // Update button states
            document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            page = 1;
            load();
        });
    });
    
    document.getElementById('clearFilters').addEventListener('click', () => {
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterCurrency').value = '';
        document.getElementById('filterOutstanding').value = '';
        document.getElementById('filterPaymentTerms').value = '';
        document.getElementById('filterQ').value = '';
        document.querySelectorAll('.quick-filter').forEach(b => b.classList.remove('active'));
        page = 1;
        load();
    });
    
    document.getElementById('filterStatus').addEventListener('change', () => { 
        page = 1; 
        load(); 
    });
    document.getElementById('filterCurrency').addEventListener('change', () => { 
        page = 1; 
        load(); 
    });
    document.getElementById('filterOutstanding').addEventListener('change', () => { 
        page = 1; 
        load(); 
    });
    document.getElementById('filterPaymentTerms').addEventListener('change', () => { 
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
            if(allVendors.length > 0){
                updateChart(allVendors);
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
                sortDirection = 'asc';
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
            renderTable(allVendors);
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
    
    // Initialize
    load();
})();

// Vendor Modal Functions
function openVendorModal(id = null) {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('vendorModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const form = document.getElementById('vendorForm');
        const title = document.getElementById('vendorModalTitle');
        
        form.reset();
        document.getElementById('vendorId').value = '';
        
        if (id) {
            title.textContent = 'Edit Vendor';
            loadVendorData(id);
        } else {
            title.textContent = 'New Vendor';
            fetch('{{ route("modules.accounting.ap.vendors.store") }}?generate_code=1')
                .then(r => r.json())
                .then(data => {
                    if(data.code) {
                        document.getElementById('vendorCode').value = data.code;
                    }
                });
        }
        
        modal.show();
        
        modalElement.addEventListener('shown.bs.modal', function() {
            $(this).css('z-index', 1050);
            $('.modal-backdrop').css('z-index', 1040);
            $(this).find('.modal-content').css('pointer-events', 'auto');
        }, { once: true });
    }, 100);
}

async function loadVendorData(id) {
    try {
        const response = await fetch(`/modules/accounting/accounts-payable/vendors/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const vendor = data.vendor;
            document.getElementById('vendorId').value = vendor.id;
            document.getElementById('vendorName').value = vendor.name;
            document.getElementById('vendorCode').value = vendor.vendor_code;
            document.getElementById('vendorContactPerson').value = vendor.contact_person || '';
            document.getElementById('vendorEmail').value = vendor.email || '';
            document.getElementById('vendorPhone').value = vendor.phone || '';
            document.getElementById('vendorMobile').value = vendor.mobile || '';
            document.getElementById('vendorTaxId').value = vendor.tax_id || '';
            document.getElementById('vendorCreditLimit').value = vendor.credit_limit || 0;
            document.getElementById('vendorPaymentTerms').value = vendor.payment_terms_days || 30;
            document.getElementById('vendorCurrency').value = vendor.currency || 'TZS';
            document.getElementById('vendorAddress').value = vendor.address || '';
            document.getElementById('vendorNotes').value = vendor.notes || '';
            document.getElementById('vendorIsActive').checked = vendor.is_active;
        }
    } catch (error) {
        console.error('Error loading vendor:', error);
        alert('Error loading vendor data');
    }
}

async function viewVendor(id) {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('viewVendorModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const content = document.getElementById('viewVendorContent');
        
        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>';
        
        modal.show();
        
        modalElement.addEventListener('shown.bs.modal', function() {
            $(this).css('z-index', 1050);
            $('.modal-backdrop').css('z-index', 1040);
            $(this).find('.modal-content').css('pointer-events', 'auto');
        }, { once: true });
    }, 100);
    
    try {
        const response = await fetch(`/modules/accounting/accounts-payable/vendors/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const v = data.vendor;
            document.getElementById('viewVendorContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Code:</th><td>${v.vendor_code}</td></tr>
                            <tr><th>Name:</th><td>${v.name}</td></tr>
                            <tr><th>Contact Person:</th><td>${v.contact_person || '-'}</td></tr>
                            <tr><th>Email:</th><td>${v.email || '-'}</td></tr>
                            <tr><th>Phone:</th><td>${v.phone || v.mobile || '-'}</td></tr>
                            <tr><th>Tax ID:</th><td>${v.tax_id || '-'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Credit Limit:</th><td class="text-end">${v.currency || 'TZS'} ${parseFloat(v.credit_limit || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                            <tr><th>Payment Terms:</th><td>${v.payment_terms || 30} days</td></tr>
                            <tr><th>Outstanding:</th><td class="text-end"><strong class="text-warning">TZS ${parseFloat(v.total_outstanding || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></td></tr>
                            <tr><th>Overdue:</th><td class="text-end"><strong class="text-danger">TZS ${parseFloat(v.overdue_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-${v.is_active ? 'success' : 'secondary'}">${v.is_active ? 'Active' : 'Inactive'}</span></td></tr>
                            <tr><th>Created:</th><td>${new Date(v.created_at).toLocaleString()}</td></tr>
                        </table>
                    </div>
                    <div class="col-12">
                        <strong>Address:</strong>
                        <p>${v.address || 'Not provided'}</p>
                        ${v.notes ? `<strong>Notes:</strong><p>${v.notes}</p>` : ''}
                    </div>
                </div>
            `;
        }
    } catch (error) {
        document.getElementById('viewVendorContent').innerHTML = '<div class="alert alert-danger">Error loading vendor details</div>';
    }
}

function editVendor(id) {
    openVendorModal(id);
}

function viewBills(id) {
    window.location.href = '{{ route("modules.accounting.ap.bills") }}?vendor_id=' + id;
}

document.getElementById('vendorForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const vendorId = document.getElementById('vendorId').value;
    const url = vendorId 
        ? `/modules/accounting/accounts-payable/vendors/${vendorId}`
        : '{{ route("modules.accounting.ap.vendors.store") }}';
    const method = vendorId ? 'PUT' : 'POST';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        if (data.success) {
            // Show success toast
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', data.message || 'Vendor saved successfully', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Vendor saved successfully');
            }
            
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('vendorModal'));
            if (modalInstance) {
                modalInstance.hide();
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Reload after a short delay to show the toast
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Error saving vendor', { duration: 7000, sound: true });
            } else {
                alert(data.message || 'Error saving vendor');
            }
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});

// Global modal cleanup
$(document).ready(function() {
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    $(document).on('show.bs.modal', '.modal', function() {
        $('.modal-backdrop').not(':last').remove();
        $(this).css('z-index', 1050);
    });
    
    $(document).on('shown.bs.modal', '.modal', function() {
        $(this).css('z-index', 1050);
        $('.modal-backdrop').last().css('z-index', 1040);
        $(this).find('.modal-content, .modal-body, .modal-footer, .modal-header').css('pointer-events', 'auto');
    });
    
    $(document).on('hidden.bs.modal', '.modal', function() {
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    });
});
</script>
@endpush
