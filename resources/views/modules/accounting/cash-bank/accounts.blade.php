@extends('layouts.app')

@section('title', 'Bank Accounts')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Bank Accounts</h4>
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

    .summary-card[data-type="balance"] {
        border-left-color: #28a745 !important;
    }

    .summary-card[data-type="active"] {
        border-left-color: #17a2b8 !important;
    }

    .summary-card[data-type="primary"] {
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
                        <i class="bx bx-credit-card me-2"></i>Bank Accounts Management
                    </h2>
                    <p class="mb-0 opacity-90">Manage and monitor all bank accounts and their balances</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <button class="btn btn-success btn-sm" id="btn-add-account" title="Add New Account">
                        <i class="bx bx-plus me-1"></i>Add New Account
                    </button>
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
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="total">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Accounts</h6>
                            <h3 class="mb-0 text-primary fw-bold" id="sumTotal">0</h3>
                            <small class="text-muted">Bank accounts</small>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-credit-card fs-4 text-primary"></i>
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
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Total Balance</h6>
                            <h3 class="mb-0 text-success fw-bold" id="sumBalance">0.00</h3>
                            <small class="text-muted">TZS</small>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-money fs-4 text-success"></i>
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
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Active Accounts</h6>
                            <h3 class="mb-0 text-info fw-bold" id="sumActive">0</h3>
                            <small class="text-muted">Currently active</small>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-check-circle fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 summary-card" data-type="primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2 text-uppercase small fw-semibold">Primary Accounts</h6>
                            <h3 class="mb-0 text-warning fw-bold" id="sumPrimary">0</h3>
                            <small class="text-muted">Marked as primary</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bx bx-star fs-4 text-warning"></i>
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
                        <label class="form-label small text-muted fw-semibold">Bank Name</label>
                        <input type="text" class="form-control form-control-sm" id="filterBankName" placeholder="Filter by bank name...">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted fw-semibold">Status</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted fw-semibold">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                            <input type="text" class="form-control" id="filterQ" placeholder="Search by account name, number, branch...">
                            <button class="btn btn-outline-secondary" type="button" id="clearSearch" title="Clear search">
                                <i class="bx bx-x"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted fw-semibold">&nbsp;</label>
                        <button class="btn btn-primary btn-sm w-100" id="btn-apply-filters">
                            <i class="bx bx-filter me-1"></i>Apply Filters
                        </button>
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
                    <i class="bx bx-table me-2"></i>Bank Accounts
                    <span class="badge bg-primary ms-2" id="accountCount">0</span>
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
                <table class="table table-hover mb-0" id="bankAccountsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="name">
                                Account Name <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="bank_name">
                                Bank <i class="bx bx-sort"></i>
                            </th>
                            <th class="sortable" data-sort="account_number">
                                Account Number <i class="bx bx-sort"></i>
                            </th>
                            <th>Branch</th>
                            <th class="text-end sortable" data-sort="balance">
                                Balance (TZS) <i class="bx bx-sort"></i>
                            </th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bankAccountsTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2 mb-0">Loading bank accounts...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-muted small" id="rowsInfo">
                    Showing 0 of 0 accounts
                </div>
                <nav aria-label="Bank accounts pagination">
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

<!-- Add Bank Account Modal -->
<div class="modal fade" id="addBankAccountModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bx bx-plus me-2"></i>Add New Bank Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="addBankAccountForm">
                <div class="modal-body" style="max-height: calc(90vh - 160px); overflow-y: auto;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bankAccountName" name="name" required>
                            <small class="text-muted">Name for this bank account</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="bankName" name="bank_name" required>
                            <small class="text-muted">Name of the bank</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="accountNumber" name="account_number" required>
                            <small class="text-muted">Bank account number</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Holder Name</label>
                            <input type="text" class="form-control" id="accountHolderName" name="account_name">
                            <small class="text-muted">Name on the account</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch Name</label>
                            <input type="text" class="form-control" id="branchName" name="branch_name">
                            <small class="text-muted">Bank branch location</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SWIFT Code</label>
                            <input type="text" class="form-control" id="swiftCode" name="swift_code" maxlength="11">
                            <small class="text-muted">Bank SWIFT/BIC code</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Initial Balance</label>
                            <input type="number" step="0.01" class="form-control" id="initialBalance" name="balance" value="0" min="0">
                            <small class="text-muted">Starting balance for this account</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chart of Account</label>
                            <select class="form-select" id="chartOfAccount" name="account_id">
                                <option value="">Select Chart of Account (Optional)</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Link to chart of accounts</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isPrimary" name="is_primary" value="1">
                                <label class="form-check-label" for="isPrimary">
                                    Set as Primary Account
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="isActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="isActive">
                                    Active Account
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="bankAccountNotes" name="notes" rows="3" placeholder="Additional notes or comments..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-save me-1"></i>Save Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Bank Account Modal -->
<div class="modal fade" id="editBankAccountModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bx bx-edit me-2"></i>Edit Bank Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editBankAccountForm">
                <input type="hidden" id="editAccountId" name="id">
                <div class="modal-body" style="max-height: calc(90vh - 160px); overflow-y: auto;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editBankAccountName" name="name" required>
                            <small class="text-muted">Name for this bank account</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editBankName" name="bank_name" required>
                            <small class="text-muted">Name of the bank</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editAccountNumber" name="account_number" required>
                            <small class="text-muted">Bank account number</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account Holder Name</label>
                            <input type="text" class="form-control" id="editAccountHolderName" name="account_name">
                            <small class="text-muted">Name on the account</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch Name</label>
                            <input type="text" class="form-control" id="editBranchName" name="branch_name">
                            <small class="text-muted">Bank branch location</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SWIFT Code</label>
                            <input type="text" class="form-control" id="editSwiftCode" name="swift_code" maxlength="11">
                            <small class="text-muted">Bank SWIFT/BIC code</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Balance</label>
                            <input type="number" step="0.01" class="form-control" id="editInitialBalance" name="balance" min="0">
                            <small class="text-muted">Current balance for this account</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Chart of Account</label>
                            <select class="form-select" id="editChartOfAccount" name="account_id">
                                <option value="">Select Chart of Account (Optional)</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Link to chart of accounts</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="editIsPrimary" name="is_primary" value="1">
                                <label class="form-check-label" for="editIsPrimary">
                                    Set as Primary Account
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="editIsActive" name="is_active" value="1">
                                <label class="form-check-label" for="editIsActive">
                                    Active Account
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="editBankAccountNotes" name="notes" rows="3" placeholder="Additional notes or comments..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Update Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Advanced Bank Accounts Data Loading
(function(){
    const endpoint = '{{ route('modules.accounting.cash-bank.accounts.data') }}';
    const pdfEndpoint = '{{ route('modules.accounting.cash-bank.accounts') }}';
    let page = 1, perPage = 20;
    let sortColumn = 'bank_name', sortDirection = 'asc';
    let allAccounts = [];
    
    function number(n){
        return (Number(n)||0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
    
    function formatCurrency(n){
        return 'TZS ' + number(n);
    }
    
    function qs(){
        return {
            bank_name: document.getElementById('filterBankName')?.value || '',
            is_active: document.getElementById('filterStatus')?.value || '',
            q: document.getElementById('filterQ')?.value || '',
            page, per_page: perPage
        };
    }
    
    function showLoading(show = true){
        const overlay = document.getElementById('loadingOverlay');
        if(overlay) overlay.style.display = show ? 'flex' : 'none';
    }
    
    function updateSummary(summary){
        const total = summary?.total_accounts || 0;
        const balance = summary?.total_balance || 0;
        const active = summary?.active_accounts || 0;
        const primary = summary?.primary_accounts || 0;
        
        if(document.getElementById('sumTotal')) document.getElementById('sumTotal').textContent = total.toLocaleString();
        if(document.getElementById('sumBalance')) document.getElementById('sumBalance').textContent = formatCurrency(balance);
        if(document.getElementById('sumActive')) document.getElementById('sumActive').textContent = active.toLocaleString();
        if(document.getElementById('sumPrimary')) document.getElementById('sumPrimary').textContent = primary.toLocaleString();
        if(document.getElementById('accountCount')) document.getElementById('accountCount').textContent = total.toLocaleString();
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
    
    function renderTable(accounts){
        const tbody = document.getElementById('bankAccountsTableBody');
        if(!tbody) return;
        
        if(!accounts || !accounts.length){
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="bx bx-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">No bank accounts found. Try adjusting your filters.</p>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = accounts.map((a, idx) => `
            <tr class="fade-in" style="animation-delay: ${idx * 0.01}s">
                <td>
                    <div class="fw-medium">${escapeHtml(a.name || 'N/A')}</div>
                    ${a.account_name && a.account_name !== a.name ? `<small class="text-muted">${escapeHtml(a.account_name)}</small>` : ''}
                </td>
                <td>${escapeHtml(a.bank_name || 'N/A')}</td>
                <td><code class="text-primary">${escapeHtml(a.account_number || 'N/A')}</code></td>
                <td>${escapeHtml(a.branch_name || '-')}</td>
                <td class="text-end">
                    <span class="fw-bold ${a.balance >= 0 ? 'text-success' : 'text-danger'}">
                        ${formatCurrency(a.balance)}
                    </span>
                </td>
                <td>
                    <span class="badge ${a.is_active ? 'bg-success' : 'bg-secondary'}">
                        ${a.is_active ? 'Active' : 'Inactive'}
                    </span>
                    ${a.is_primary ? '<span class="badge bg-warning ms-1">Primary</span>' : ''}
                </td>
                <td>${escapeHtml(a.user_name || 'N/A')}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" onclick="viewAccount(${a.id})" title="View Details">
                            <i class="bx bx-show"></i>
                        </button>
                        <button class="btn btn-outline-primary" onclick="editAccount(${a.id})" title="Edit Account">
                            <i class="bx bx-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteAccount(${a.id}, '${escapeHtml(a.bank_name || 'N/A')}', '${escapeHtml(a.account_number || 'N/A')}')" title="Delete Account">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
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
        const body = document.getElementById('bankAccountsTableBody');
        if(!body) return;
        
        body.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 mb-0">Loading bank accounts...</p>
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
                let msg = res.message || 'Failed to load bank accounts';
                
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
                        <td colspan="8" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <div class="mt-2">${errorHtml}</div>
                        </td>
                    </tr>
                `;
                updateSummary({ total_accounts: 0, total_balance: 0, active_accounts: 0, primary_accounts: 0 });
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
                
                if(sortColumn === 'balance'){
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
                        <td colspan="8" class="text-danger text-center py-5">
                            <i class="bx bx-error-circle fs-1"></i>
                            <p class="mt-2">Error loading: ${escapeHtml(err.message || 'Network or server error')}</p>
                        </td>
                    </tr>
                `;
            }
            updateSummary({ total_accounts: 0, total_balance: 0, active_accounts: 0, primary_accounts: 0 });
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
    
    if(document.getElementById('btn-apply-filters')) {
        document.getElementById('btn-apply-filters').addEventListener('click', () => { 
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
    
    ['filterBankName', 'filterStatus'].forEach(id => {
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
    window.loadBankAccounts = function(resetPage = false) {
        if (resetPage) {
            page = 1;
        }
        load();
    };
    
    // Add Bank Account Modal Handler
    const addBankAccountModal = new bootstrap.Modal(document.getElementById('addBankAccountModal'));
    const addBankAccountForm = document.getElementById('addBankAccountForm');
    
    if(document.getElementById('btn-add-account')) {
        document.getElementById('btn-add-account').addEventListener('click', function() {
            addBankAccountForm.reset();
            // Reset checkboxes
            document.getElementById('isPrimary').checked = false;
            document.getElementById('isActive').checked = true;
            document.getElementById('initialBalance').value = '0';
            addBankAccountModal.show();
        });
    }
    
    if(addBankAccountForm) {
        addBankAccountForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
            
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                if(key === 'is_primary' || key === 'is_active') {
                    data[key] = value === '1' || value === true;
                } else if(key === 'balance') {
                    data[key] = parseFloat(value) || 0;
                } else if(key === 'account_id') {
                    data[key] = value || null;
                } else {
                    data[key] = value || null;
                }
            });
            
            // Convert checkbox values properly
            if(!formData.has('is_primary')) {
                data.is_primary = false;
            }
            if(!formData.has('is_active')) {
                data.is_active = true;
            }
            
            fetch('{{ route("modules.accounting.cash-bank.accounts.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(async response => {
                const result = await response.json();
                
                if(response.ok && result.success) {
                    // Show success message
                    if(typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.success('Success', 'Bank account created successfully!', { duration: 5000 });
                    } else {
                        alert('Bank account created successfully!');
                    }
                    
                    // Close modal
                    addBankAccountModal.hide();
                    
                    // Reset form
                    addBankAccountForm.reset();
                    document.getElementById('isPrimary').checked = false;
                    document.getElementById('isActive').checked = true;
                    document.getElementById('initialBalance').value = '0';
                    
                    // Reload accounts
                    page = 1;
                    load();
                } else {
                    // Show error message
                    let errorMsg = result.message || 'Failed to create bank account';
                    
                    if(result.errors) {
                        const errorMessages = [];
                        Object.keys(result.errors).forEach(key => {
                            if(Array.isArray(result.errors[key])) {
                                result.errors[key].forEach(err => {
                                    errorMessages.push(`${key}: ${err}`);
                                });
                            } else {
                                errorMessages.push(`${key}: ${result.errors[key]}`);
                            }
                        });
                        if(errorMessages.length > 0) {
                            errorMsg = errorMessages.join('\n');
                        }
                    }
                    
                    if(typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMsg = error.message || 'Network error occurred';
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // View Account Details
    window.viewAccount = function(id) {
        const account = allAccounts.find(a => a.id === id);
        if (!account) {
            if(typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', 'Account not found', { duration: 5000 });
            } else {
                alert('Account not found');
            }
            return;
        }
        
        let details = `
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Bank Account Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Account Name:</strong></div>
                        <div class="col-md-6">${escapeHtml(account.name || 'N/A')}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Bank Name:</strong></div>
                        <div class="col-md-6">${escapeHtml(account.bank_name || 'N/A')}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Account Number:</strong></div>
                        <div class="col-md-6"><code>${escapeHtml(account.account_number || 'N/A')}</code></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Account Holder:</strong></div>
                        <div class="col-md-6">${escapeHtml(account.account_name || 'N/A')}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Branch:</strong></div>
                        <div class="col-md-6">${escapeHtml(account.branch_name || 'N/A')}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>SWIFT Code:</strong></div>
                        <div class="col-md-6">${escapeHtml(account.swift_code || 'N/A')}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Balance:</strong></div>
                        <div class="col-md-6"><span class="fw-bold ${account.balance >= 0 ? 'text-success' : 'text-danger'}">${formatCurrency(account.balance)}</span></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>Status:</strong></div>
                        <div class="col-md-6">
                            <span class="badge ${account.is_active ? 'bg-success' : 'bg-secondary'}">${account.is_active ? 'Active' : 'Inactive'}</span>
                            ${account.is_primary ? '<span class="badge bg-warning ms-1">Primary</span>' : ''}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6"><strong>User:</strong></div>
                        <div class="col-md-6">${escapeHtml(account.user_name || 'Organization Account')}</div>
                    </div>
                    ${account.notes ? `<div class="row mb-3"><div class="col-12"><strong>Notes:</strong><br>${escapeHtml(account.notes)}</div></div>` : ''}
                </div>
            </div>
        `;
        
        if(typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.info('Account Details', details, { duration: 0, html: true });
        } else {
            alert('Account: ' + account.bank_name + ' - ' + account.account_number);
        }
    };
    
    // Edit Account
    window.editAccount = function(id) {
        const account = allAccounts.find(a => a.id === id);
        if (!account) {
            if(typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', 'Account not found', { duration: 5000 });
            } else {
                alert('Account not found');
            }
            return;
        }
        
        // Populate form
        document.getElementById('editAccountId').value = account.id;
        document.getElementById('editBankAccountName').value = account.name || '';
        document.getElementById('editBankName').value = account.bank_name || '';
        document.getElementById('editAccountNumber').value = account.account_number || '';
        document.getElementById('editAccountHolderName').value = account.account_name || '';
        document.getElementById('editBranchName').value = account.branch_name || '';
        document.getElementById('editSwiftCode').value = account.swift_code || '';
        document.getElementById('editInitialBalance').value = account.balance || 0;
        const chartSelect = document.getElementById('editChartOfAccount');
        if (chartSelect) {
            chartSelect.value = account.account_code ? Array.from(chartSelect.options).find(opt => opt.text.includes(account.account_code))?.value || '' : '';
        }
        document.getElementById('editIsPrimary').checked = account.is_primary || false;
        document.getElementById('editIsActive').checked = account.is_active !== false;
        document.getElementById('editBankAccountNotes').value = account.notes || '';
        
        // Show modal
        const editModal = new bootstrap.Modal(document.getElementById('editBankAccountModal'));
        editModal.show();
    };
    
    // Delete Account
    window.deleteAccount = function(id, bankName, accountNumber) {
        if (!confirm(`Are you sure you want to delete this bank account?\n\nBank: ${bankName}\nAccount: ${accountNumber}\n\nThis action cannot be undone!`)) {
            return;
        }
        
        fetch(`{{ route('modules.accounting.cash-bank.accounts.destroy', ['id' => ':id']) }}`.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            const result = await response.json();
            
            if(response.ok && result.success) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', 'Bank account deleted successfully!', { duration: 5000 });
                } else {
                    alert('Bank account deleted successfully!');
                }
                
                // Reload accounts
                page = 1;
                load();
            } else {
                const errorMsg = result.message || 'Failed to delete bank account';
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.message || 'Network error occurred';
            if(typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
            } else {
                alert('Error: ' + errorMsg);
            }
        });
    };
    
    // Edit Bank Account Form Handler
    const editBankAccountModal = new bootstrap.Modal(document.getElementById('editBankAccountModal'));
    const editBankAccountForm = document.getElementById('editBankAccountForm');
    
    if(editBankAccountForm) {
        editBankAccountForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
            
            const accountId = document.getElementById('editAccountId').value;
            const formData = new FormData(this);
            const data = {};
            formData.forEach((value, key) => {
                if(key === 'is_primary' || key === 'is_active') {
                    data[key] = value === '1' || value === true;
                } else if(key === 'balance') {
                    data[key] = parseFloat(value) || 0;
                } else if(key === 'account_id') {
                    data[key] = value || null;
                } else {
                    data[key] = value || null;
                }
            });
            
            // Convert checkbox values properly
            if(!formData.has('is_primary')) {
                data.is_primary = false;
            }
            if(!formData.has('is_active')) {
                data.is_active = true;
            }
            
            fetch(`{{ route('modules.accounting.cash-bank.accounts.update', ['id' => ':id']) }}`.replace(':id', accountId), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(async response => {
                const result = await response.json();
                
                if(response.ok && result.success) {
                    if(typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.success('Success', 'Bank account updated successfully!', { duration: 5000 });
                    } else {
                        alert('Bank account updated successfully!');
                    }
                    
                    // Close modal
                    editBankAccountModal.hide();
                    
                    // Reset form
                    editBankAccountForm.reset();
                    
                    // Reload accounts
                    page = 1;
                    load();
                } else {
                    let errorMsg = result.message || 'Failed to update bank account';
                    
                    if(result.errors) {
                        const errorMessages = [];
                        Object.keys(result.errors).forEach(key => {
                            if(Array.isArray(result.errors[key])) {
                                result.errors[key].forEach(err => {
                                    errorMessages.push(`${key}: ${err}`);
                                });
                            } else {
                                errorMessages.push(`${key}: ${result.errors[key]}`);
                            }
                        });
                        if(errorMessages.length > 0) {
                            errorMsg = errorMessages.join('\n');
                        }
                    }
                    
                    if(typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                    } else {
                        alert('Error: ' + errorMsg);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMsg = error.message || 'Network error occurred';
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
    
    // Initialize
    load();
})();
</script>
@endpush
