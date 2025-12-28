@extends('layouts.app')

@section('title', 'Bills Management')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Accounts Payable - Bills</h4>
</div>
@endsection

@push('styles')
<style>
    .bill-item-row {
        border-bottom: 1px solid #dee2e6;
        padding: 10px 0;
    }
    
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
    }
    
    .modal-content {
        pointer-events: auto !important;
    }
    
    body.modal-open {
        overflow: hidden !important;
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
                        <i class="bx bx-file me-2"></i>Bills Management
                    </h2>
                    <p class="mb-0 opacity-90">Manage vendor bills, payments, and accounts payable</p>
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
                    <button class="btn btn-light btn-sm" onclick="openBillModal()" title="New Bill">
                        <i class="bx bx-plus"></i> New Bill
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
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Bills</h6>
                            <h3 class="mb-0 fw-bold" id="sumTotal">0</h3>
                            <small class="text-muted">Bills</small>
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
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Pending Amount</h6>
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
                        <label class="form-label small text-muted fw-semibold">Vendor</label>
                        <select class="form-select form-select-sm" id="filterVendor">
                            <option value="">All Vendors</option>
                            @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All Status</option>
                            <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Partially Paid" {{ request('status') == 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                            <option value="Paid" {{ request('status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="Overdue" {{ request('status') == 'Overdue' ? 'selected' : '' }}>Overdue</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by bill no, vendor, or reference...">
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
                <i class="bx bx-bar-chart-alt-2 me-2"></i>Bills Trends
            </h6>
        </div>
        <div class="card-body">
            <canvas id="billsChart" height="80"></canvas>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h6 class="mb-0 fw-semibold">
                    <i class="bx bx-table me-2"></i>Bills List
                    <span class="badge bg-primary ms-2" id="billCount">0</span>
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
                <table class="table table-hover mb-0" id="billsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="bill_date">
                                Date <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="bill_no">
                                Bill No <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="vendor_name">
                                Vendor <i class="bx bx-sort"></i>
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
                    <tbody id="billsTableBody">
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading bills...</p>
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
                    Showing 0 of 0 bills
                </div>
                <nav aria-label="Bills pagination">
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

<!-- Bill Modal -->
<div class="modal fade" id="billModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="billModalTitle">New Bill</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="billForm">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <input type="hidden" id="billId" name="id">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Vendor <span class="text-danger">*</span></label>
                            <select class="form-select" id="billVendor" name="vendor_id" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Bill Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="billDate" name="bill_date" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="billDueDate" name="due_date" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference No</label>
                            <input type="text" class="form-control" id="billReference" name="reference_no">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Amount</label>
                            <input type="number" step="0.01" class="form-control" id="billDiscount" name="discount_amount" value="0" onchange="calculateBillTotal()">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="billNotes" name="notes" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea class="form-control" id="billTerms" name="terms" rows="2"></textarea>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Bill Items</h6>
                        <button type="button" class="btn btn-sm btn-success" onclick="addBillItem()">
                            <i class="fas fa-plus"></i> Add Item
                        </button>
                    </div>

                    <div id="billItems">
                        <!-- Items will be added here -->
                    </div>

                    <div class="mt-3 p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Subtotal:</strong>
                                    <span id="billSubtotal">TZS 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Tax:</strong>
                                    <span id="billTax">TZS 0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Discount:</strong>
                                    <span id="billDiscountDisplay">TZS 0.00</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong class="fs-5">Total Amount:</strong>
                                    <strong class="fs-5 text-primary" id="billTotal">TZS 0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Bill Modal -->
<div class="modal fade" id="viewBillModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Bill Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewBillContent" style="max-height: 70vh; overflow-y: auto;">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" onclick="exportBillPdfFromView()">Download PDF</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Record Bill Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm">
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <input type="hidden" id="paymentBillId" name="bill_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bill Number</label>
                            <input type="text" class="form-control" id="paymentBillNo" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bill Balance</label>
                            <input type="text" class="form-control" id="paymentBillBalance" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="paymentDate" name="payment_date" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
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
                            <label class="form-label">Bank Account</label>
                            <select class="form-select" id="paymentBankAccount" name="bank_account_id">
                                <option value="">Select Bank Account</option>
                                @foreach(\App\Models\BankAccount::all() as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference No</label>
                            <input type="text" class="form-control" id="paymentReference" name="reference_no">
                        </div>
                        <div class="col-12">
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

@push('scripts')
<script>
// Load Chart.js with fallback
(function() {
    const chartScript = document.createElement('script');
    chartScript.src = '{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}';
    chartScript.onerror = function() {
        const fallbackScript = document.createElement('script');
        fallbackScript.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
        document.head.appendChild(fallbackScript);
    };
    document.head.appendChild(chartScript);
})();
</script>
<script>
const token = '{{ csrf_token() }}';
let billItemCount = 0;
const accounts = @json($accounts->map(fn($a) => ['id' => $a->id, 'code' => $a->code, 'name' => $a->name]));

// Advanced Bills Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.ap.bills.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.ap.bills') }}';
    let page = 1, perPage = 20;
    let sortColumn = 'bill_date', sortDirection = 'desc';
    let allBills = [];
    let chart = null;
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            vendor_id: document.getElementById('filterVendor')?.value || '',
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
        const total = summary?.total_bills || 0;
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
        return 'bg-warning';
    }
    
    function renderTable(bills){
        const tbody = document.getElementById('billsTableBody');
        if(!tbody) return;
        
        if(!bills || !bills.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No bills found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = bills.map((b, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.02}s">
                <td>
                    <span class="badge bg-light text-dark">${b.bill_date_display || b.bill_date || ''}</span>
                </td>
                <td><code class="text-primary fw-bold">${escapeHtml(b.bill_no || '')}</code></td>
                <td>
                    <div class="fw-medium">${escapeHtml(b.vendor_name || 'N/A')}</div>
                </td>
                <td>
                    <span class="badge bg-light text-dark">${b.due_date_display || b.due_date || ''}</span>
                    ${b.is_overdue ? '<span class="badge bg-danger ms-1">Overdue</span>' : ''}
                </td>
                <td class="text-end">
                    <span class="text-primary fw-semibold">${formatCurrency(b.total_amount)}</span>
                </td>
                <td class="text-end">
                    <span class="text-success fw-semibold">${formatCurrency(b.paid_amount)}</span>
                </td>
                <td class="text-end">
                    ${b.balance > 0 ? `<span class="text-warning fw-semibold">${formatCurrency(b.balance)}</span>` : '<span class="text-success">0.00</span>'}
                </td>
                <td>
                    <span class="badge ${getStatusBadge(b.status, b.is_overdue)}">${escapeHtml(b.status || '')}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info" onclick="viewBill(${b.id})" title="View">View</button>
                        ${b.status !== 'Paid' ? `<button class="btn btn-sm btn-warning" onclick="editBill(${b.id})" title="Edit">Edit</button>` : ''}
                        <button class="btn btn-sm btn-success" onclick="recordPayment(${b.id})" title="Record Payment">Payment</button>
                        <button class="btn btn-sm btn-danger" onclick="exportBillPdf(${b.id})" title="PDF">PDF</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    function renderPagination(currentPage, totalBills){
        const totalPages = Math.ceil(totalBills / perPage);
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
    
    function updateChart(bills){
        if (typeof Chart === 'undefined') return;
        
        if(!chart){
            const ctxEl = document.getElementById('billsChart');
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
        bills.forEach(b => {
            const date = b.bill_date || '';
            if(!grouped[date]){
                grouped[date] = { total: 0, paid: 0 };
            }
            grouped[date].total += b.total_amount || 0;
            grouped[date].paid += b.paid_amount || 0;
        });
        
        const dates = Object.keys(grouped).sort();
        chart.data.labels = dates;
        chart.data.datasets[0].data = dates.map(d => grouped[d].total);
        chart.data.datasets[1].data = dates.map(d => grouped[d].paid);
        chart.update();
    }
    
    function load(){
        showLoading(true);
        const body = document.getElementById('billsTableBody');
        if(!body) return;
        
        body.innerHTML = `
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading bills...</p>
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
                const msg = res.message || 'Failed to load bills';
                body.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">${escapeHtml(msg)}</p>
                        </td>
                    </tr>
                `;
                updateSummary({ total_bills: 0, total_amount: 0, total_paid: 0, total_balance: 0, total_overdue: 0 });
                if(document.getElementById('billCount')) document.getElementById('billCount').textContent = '0';
                if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = '0 bills';
                return; 
            }
            
            allBills = res.bills || [];
            updateSummary(res.summary || {});
            if(document.getElementById('billCount')) document.getElementById('billCount').textContent = (res.summary?.count || 0).toLocaleString();
            if(document.getElementById('rowsInfo')) document.getElementById('rowsInfo').textContent = `Showing ${((page - 1) * perPage) + 1} - ${Math.min(page * perPage, res.summary?.count || 0)} of ${(res.summary?.count || 0).toLocaleString()} bills`;
            
            allBills.sort((a, b) => {
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
            
            renderTable(allBills);
            renderPagination(page, res.summary?.count || 0);
            
            if(chart && document.getElementById('chartCard') && document.getElementById('chartCard').style.display !== 'none'){
                updateChart(allBills);
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
            updateSummary({ total_bills: 0, total_amount: 0, total_paid: 0, total_balance: 0, total_overdue: 0 });
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
    
    ['filterFrom', 'filterTo', 'filterVendor', 'filterStatus'].forEach(id => {
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
                if(allBills.length > 0){
                    updateChart(allBills);
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
            
            renderTable(allBills);
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
})();

function openBillModal(id = null) {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('billModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const form = document.getElementById('billForm');
        const title = document.getElementById('billModalTitle');
        
        form.reset();
        document.getElementById('billId').value = '';
        document.getElementById('billItems').innerHTML = '';
        billItemCount = 0;
        
        addBillItem();
        
        if (id) {
            title.textContent = 'Edit Bill';
            loadBillData(id);
        } else {
            title.textContent = 'New Bill';
            // Set due date to 30 days from now
            const dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 30);
            document.getElementById('billDueDate').value = dueDate.toISOString().split('T')[0];
        }
        
        modal.show();
        
        modalElement.addEventListener('shown.bs.modal', function() {
            $(this).css('z-index', 1050);
            $('.modal-backdrop').css('z-index', 1040);
            $(this).find('.modal-content').css('pointer-events', 'auto');
        }, { once: true });
    }, 100);
}

function addBillItem() {
    billItemCount++;
    const itemHtml = `
        <div class="bill-item-row row g-2" data-item-id="${billItemCount}">
            <div class="col-md-4">
                <label class="form-label small">Description <span class="text-danger">*</span></label>
                <input type="text" class="form-control form-control-sm bill-item-desc" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Quantity <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control form-control-sm bill-item-qty" required value="1" onchange="calculateBillItem(${billItemCount})">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Unit Price <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control form-control-sm bill-item-price" required onchange="calculateBillItem(${billItemCount})">
            </div>
            <div class="col-md-1">
                <label class="form-label small">Tax %</label>
                <input type="number" step="0.01" class="form-control form-control-sm bill-item-tax" value="0" onchange="calculateBillItem(${billItemCount})">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Account</label>
                <select class="form-select form-select-sm bill-item-account">
                    <option value="">Select Account</option>
                    ${accounts.map(acc => `<option value="${acc.id}">${acc.code} - ${acc.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeBillItem(${billItemCount})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('billItems').insertAdjacentHTML('beforeend', itemHtml);
    calculateBillTotal();
}

function removeBillItem(itemId) {
    document.querySelector(`[data-item-id="${itemId}"]`).remove();
    calculateBillTotal();
}

function calculateBillItem(itemId) {
    const item = document.querySelector(`[data-item-id="${itemId}"]`);
    const qty = parseFloat(item.querySelector('.bill-item-qty').value) || 0;
    const price = parseFloat(item.querySelector('.bill-item-price').value) || 0;
    const taxRate = parseFloat(item.querySelector('.bill-item-tax').value) || 0;
    
    const subtotal = qty * price;
    const tax = (subtotal * taxRate) / 100;
    
    calculateBillTotal();
}

function calculateBillTotal() {
    let subtotal = 0;
    let taxTotal = 0;
    
    document.querySelectorAll('.bill-item-row').forEach(item => {
        const qty = parseFloat(item.querySelector('.bill-item-qty').value) || 0;
        const price = parseFloat(item.querySelector('.bill-item-price').value) || 0;
        const taxRate = parseFloat(item.querySelector('.bill-item-tax').value) || 0;
        
        const lineSubtotal = qty * price;
        const lineTax = (lineSubtotal * taxRate) / 100;
        
        subtotal += lineSubtotal;
        taxTotal += lineTax;
    });
    
    const discount = parseFloat(document.getElementById('billDiscount').value) || 0;
    const total = subtotal + taxTotal - discount;
    
    document.getElementById('billSubtotal').textContent = `TZS ${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById('billTax').textContent = `TZS ${taxTotal.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById('billDiscountDisplay').textContent = `TZS ${discount.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
    document.getElementById('billTotal').textContent = `TZS ${total.toLocaleString('en-US', {minimumFractionDigits: 2})}`;
}

async function viewBill(id) {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('viewBillModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const content = document.getElementById('viewBillContent');
        
        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>';
        
        modal.show();
        
        modalElement.addEventListener('shown.bs.modal', function() {
            $(this).css('z-index', 1050);
            $('.modal-backdrop').css('z-index', 1040);
            $(this).find('.modal-content').css('pointer-events', 'auto');
            loadBillViewData(id, content);
        }, { once: true });
    }, 100);
}

async function loadBillViewData(id, content) {
    try {
        const response = await fetch(`/modules/accounting/accounts-payable/bills/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const bill = data.bill;
            content.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Bill No:</th><td><strong>${bill.bill_no}</strong></td></tr>
                            <tr><th>Vendor:</th><td>${bill.vendor?.name || 'N/A'}</td></tr>
                            <tr><th>Bill Date:</th><td>${new Date(bill.bill_date).toLocaleDateString()}</td></tr>
                            <tr><th>Due Date:</th><td>${new Date(bill.due_date).toLocaleDateString()}</td></tr>
                            <tr><th>Reference:</th><td>${bill.reference_no || '-'}</td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-${bill.status === 'Paid' ? 'success' : 'warning'}">${bill.status}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Subtotal:</th><td class="text-end">TZS ${parseFloat(bill.subtotal).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                            <tr><th>Tax:</th><td class="text-end">TZS ${parseFloat(bill.tax_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                            <tr><th>Discount:</th><td class="text-end">TZS ${parseFloat(bill.discount_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                            <tr><th>Total Amount:</th><td class="text-end"><strong>TZS ${parseFloat(bill.total_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></td></tr>
                            <tr><th>Paid Amount:</th><td class="text-end text-success">TZS ${parseFloat(bill.paid_amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                            <tr><th>Balance:</th><td class="text-end"><strong class="text-${bill.balance > 0 ? 'warning' : 'success'}">TZS ${parseFloat(bill.balance).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></td></tr>
                        </table>
                    </div>
                    ${bill.notes ? `<div class="col-12"><strong>Notes:</strong><p>${bill.notes}</p></div>` : ''}
                    ${bill.terms ? `<div class="col-12"><strong>Terms:</strong><p>${bill.terms}</p></div>` : ''}
                </div>
                <h6>Bill Items</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Quantity</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Tax %</th>
                                <th class="text-end">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${bill.items ? bill.items.map(item => `
                                <tr>
                                    <td>${item.description}</td>
                                    <td class="text-end">${item.quantity}</td>
                                    <td class="text-end">TZS ${parseFloat(item.unit_price).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                    <td class="text-end">${item.tax_rate || 0}%</td>
                                    <td class="text-end">TZS ${parseFloat(item.line_total).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
                                </tr>
                            `).join('') : '<tr><td colspan="5" class="text-center">No items</td></tr>'}
                        </tbody>
                    </table>
                </div>
            `;
        }
    } catch (error) {
        content.innerHTML = '<div class="alert alert-danger">Error loading bill details</div>';
    }
}

function editBill(id) {
    openBillModal(id);
}

function recordPayment(id) {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        fetch(`/modules/accounting/accounts-payable/bills/${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const bill = data.bill;
                    document.getElementById('paymentBillId').value = bill.id;
                    document.getElementById('paymentBillNo').value = bill.bill_no;
                    document.getElementById('paymentBillBalance').value = `TZS ${parseFloat(bill.balance).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
                    document.getElementById('paymentAmount').value = bill.balance;
                    document.getElementById('paymentAmount').max = bill.balance;
                    
                    const modalElement = document.getElementById('paymentModal');
                    const modal = new bootstrap.Modal(modalElement, {
                        backdrop: 'static',
                        keyboard: false
                    });
                    
                    modal.show();
                    
                    modalElement.addEventListener('shown.bs.modal', function() {
                        $(this).css('z-index', 1050);
                        $('.modal-backdrop').css('z-index', 1040);
                        $(this).find('.modal-content').css('pointer-events', 'auto');
                    }, { once: true });
                }
            });
    }, 100);
}

async function loadBillData(id) {
    try {
        const response = await fetch(`/modules/accounting/accounts-payable/bills/${id}`);
        const data = await response.json();
        
        if (data.success) {
            const bill = data.bill;
            document.getElementById('billId').value = bill.id;
            document.getElementById('billVendor').value = bill.vendor_id;
            document.getElementById('billDate').value = bill.bill_date;
            document.getElementById('billDueDate').value = bill.due_date;
            document.getElementById('billReference').value = bill.reference_no || '';
            document.getElementById('billDiscount').value = bill.discount_amount || 0;
            document.getElementById('billNotes').value = bill.notes || '';
            document.getElementById('billTerms').value = bill.terms || '';
            
            document.getElementById('billItems').innerHTML = '';
            billItemCount = 0;
            
            if (bill.items && bill.items.length > 0) {
                bill.items.forEach(item => {
                    addBillItem();
                    const lastItem = document.querySelectorAll('.bill-item-row').lastElementChild;
                    lastItem.querySelector('.bill-item-desc').value = item.description;
                    lastItem.querySelector('.bill-item-qty').value = item.quantity;
                    lastItem.querySelector('.bill-item-price').value = item.unit_price;
                    lastItem.querySelector('.bill-item-tax').value = item.tax_rate || 0;
                    lastItem.querySelector('.bill-item-account').value = item.account_id || '';
                });
            }
            
            calculateBillTotal();
        }
    } catch (error) {
        console.error('Error loading bill:', error);
        alert('Error loading bill data');
    }
}

document.getElementById('billForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validate required fields
    const vendorId = document.getElementById('billVendor').value;
    const billDate = document.getElementById('billDate').value;
    const dueDate = document.getElementById('billDueDate').value;
    
    if (!vendorId || vendorId === '') {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Validation Error', 'Please select a vendor', { duration: 5000, sound: true });
        } else {
            alert('Please select a vendor');
        }
        return;
    }
    
    if (!billDate) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Validation Error', 'Please select a bill date', { duration: 5000, sound: true });
        } else {
            alert('Please select a bill date');
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
    
    const items = [];
    let isValid = true;
    let invalidItems = [];
    
    document.querySelectorAll('.bill-item-row').forEach((item, index) => {
        const desc = item.querySelector('.bill-item-desc')?.value?.trim();
        const qty = item.querySelector('.bill-item-qty')?.value;
        const price = item.querySelector('.bill-item-price')?.value;
        const tax = item.querySelector('.bill-item-tax')?.value || 0;
        const account = item.querySelector('.bill-item-account')?.value;
        
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
            ? 'Please add at least one bill item' 
            : `Please fill in all required fields for items: ${invalidItems.join(', ')}`;
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Validation Error', msg, { duration: 5000, sound: true });
        } else {
            alert(msg);
        }
        return;
    }
    
    const billId = document.getElementById('billId').value;
    const payload = {
        vendor_id: vendorId,
        bill_date: billDate,
        due_date: dueDate,
        reference_no: document.getElementById('billReference')?.value || '',
        discount_amount: parseFloat(document.getElementById('billDiscount')?.value) || 0,
        notes: document.getElementById('billNotes')?.value || '',
        terms: document.getElementById('billTerms')?.value || '',
        items: items
    };
    
    try {
        const url = billId 
            ? `/modules/accounting/accounts-payable/bills/${billId}`
            : '{{ route("modules.accounting.ap.bills.store") }}';
        const method = billId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        if (data.success) {
            // Show success toast
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', data.message || 'Bill saved successfully', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Bill saved successfully');
            }
            
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('billModal'));
            if (modalInstance) {
                modalInstance.hide();
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Reset form
            document.getElementById('billForm').reset();
            document.getElementById('billId').value = '';
            document.getElementById('billItems').innerHTML = '';
            billItemCount = 0;
            
            // Reload bills list after a short delay to show the toast
            setTimeout(() => {
                if (typeof load === 'function') {
                    page = 1;
                    load();
                } else {
                    location.reload();
                }
            }, 500);
        } else {
            // Handle validation errors
            let errorMessage = data.message || 'Error saving bill';
            
            if (data.errors) {
                const errorMessages = [];
                Object.keys(data.errors).forEach(key => {
                    if (Array.isArray(data.errors[key])) {
                        errorMessages.push(...data.errors[key]);
                    } else {
                        errorMessages.push(data.errors[key]);
                    }
                });
                if (errorMessages.length > 0) {
                    errorMessage = errorMessages.join('<br>');
                }
            }
            
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', errorMessage, { duration: 7000, sound: true });
            } else {
                alert(errorMessage.replace(/<br>/g, '\n'));
            }
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});

document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Get form values
    const billId = document.getElementById('paymentBill')?.value;
    const paymentDate = document.getElementById('paymentDate')?.value;
    const amount = document.getElementById('paymentAmount')?.value;
    const paymentMethod = document.getElementById('paymentMethod')?.value;
    const referenceNo = document.getElementById('paymentReference')?.value || '';
    const bankAccountId = document.getElementById('paymentBankAccount')?.value || '';
    const notes = document.getElementById('paymentNotes')?.value || '';
    
    // Validate required fields
    if (!billId || billId === '') {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Validation Error', 'Please select a bill', { duration: 5000, sound: true });
        } else {
            alert('Please select a bill');
        }
        return;
    }
    
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
        bill_id: billId,
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
        const response = await fetch('{{ route("modules.accounting.ap.payments.store") }}', {
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
            // Show success toast
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
            
            // Reload bills list after a short delay to show the toast
            setTimeout(() => {
                if (typeof load === 'function') {
                    page = 1;
                    load();
                } else {
                    location.reload();
                }
            }, 500);
        } else {
            // Handle validation errors
            let errorMessage = data.message || 'Error recording payment';
            
            if (data.errors) {
                const errorMessages = [];
                Object.keys(data.errors).forEach(key => {
                    if (Array.isArray(data.errors[key])) {
                        errorMessages.push(...data.errors[key]);
                    } else {
                        errorMessages.push(data.errors[key]);
                    }
                });
                if (errorMessages.length > 0) {
                    errorMessage = errorMessages.join('<br>');
                }
            }
            
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', errorMessage, { duration: 7000, sound: true });
            } else {
                alert(errorMessage.replace(/<br>/g, '\n'));
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

function exportBillsPdf() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'pdf');
    window.location.href = '{{ route("modules.accounting.ap.bills") }}?' + params.toString();
}

function exportBillPdf(id) {
    window.location.href = `/modules/accounting/accounts-payable/bills/${id}/pdf`;
}

function exportBillPdfFromView() {
    const billId = document.getElementById('paymentBillId').value || 
                   document.querySelector('[data-bill-id]')?.getAttribute('data-bill-id');
    if (billId) {
        exportBillPdf(billId);
    }
}

// Modal cleanup
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
@endsection
