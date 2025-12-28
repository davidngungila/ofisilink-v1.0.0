@extends('layouts.app')

@section('title', 'Chart of Accounts - Advanced')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title"><i class="bx bx-list-ul"></i> Chart of Accounts</h4>
</div>
@endsection

@push('styles')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/select/1.7.0/css/select.bootstrap5.min.css" rel="stylesheet">
<style>
    .account-type-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: bold;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .account-type-header .badge {
        background: rgba(255,255,255,0.3);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
    }
    .account-row {
        cursor: pointer;
        transition: all 0.2s;
    }
    .account-row:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
    }
    .account-child {
        background-color: #f8f9fa;
    }
    .account-child td:first-child {
        padding-left: 40px !important;
    }
    .tree-toggle {
        cursor: pointer;
        margin-right: 8px;
        color: #667eea;
    }
    .tree-toggle:hover {
        color: #764ba2;
    }
    .stats-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .stats-card.asset { border-left-color: #28a745; }
    .stats-card.liability { border-left-color: #dc3545; }
    .stats-card.equity { border-left-color: #ffc107; }
    .stats-card.income { border-left-color: #17a2b8; }
    .stats-card.expense { border-left-color: #6f42c1; }
    .balance-positive { color: #28a745; font-weight: bold; }
    .balance-negative { color: #dc3545; font-weight: bold; }
    .balance-zero { color: #6c757d; }
    .advanced-filter-panel {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .view-mode-buttons .btn {
        border-radius: 0;
    }
    .view-mode-buttons .btn:first-child {
        border-top-left-radius: 0.375rem;
        border-bottom-left-radius: 0.375rem;
    }
    .view-mode-buttons .btn:last-child {
        border-top-right-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }
    #transactionsTable_wrapper {
        margin-top: 20px;
    }
    .transaction-debit { color: #28a745; font-weight: bold; }
    .transaction-credit { color: #dc3545; font-weight: bold; }
    .bulk-actions-bar {
        background: #e9ecef;
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 15px;
        display: none;
    }
    .bulk-actions-bar.show {
        display: block;
    }
    
    /* Modal Scrolling Styles */
    .modal-dialog-scrollable .modal-content {
        max-height: 90vh;
        display: flex;
        flex-direction: column;
    }
    
    .modal-dialog-scrollable .modal-header {
        flex-shrink: 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .modal-dialog-scrollable .modal-body {
        overflow-y: auto;
        flex: 1 1 auto;
        max-height: calc(90vh - 120px);
        padding: 1.5rem;
    }
    
    .modal-dialog-scrollable .modal-footer {
        flex-shrink: 0;
        border-top: 1px solid #dee2e6;
    }
    
    /* Account Modal Specific Styles */
    #accountModal .modal-body {
        max-height: calc(90vh - 160px);
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    #accountModal .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    
    #accountModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    #accountModal .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    #accountModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* View Account Modal Specific Styles */
    #viewAccountModal .modal-body {
        max-height: calc(90vh - 160px);
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    #viewAccountModal .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    
    #viewAccountModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    #viewAccountModal .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    #viewAccountModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Transactions Modal Specific Styles */
    #transactionsModal .modal-body {
        max-height: calc(90vh - 160px);
        overflow-y: auto;
        overflow-x: hidden;
    }
    
    #transactionsModal .modal-body::-webkit-scrollbar {
        width: 8px;
    }
    
    #transactionsModal .modal-body::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    #transactionsModal .modal-body::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    #transactionsModal .modal-body::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Ensure form elements don't overflow */
    .modal-body .row {
        margin-left: 0;
        margin-right: 0;
    }
    
    .modal-body .form-control,
    .modal-body .form-select {
        max-width: 100%;
    }
    
    /* Responsive modal adjustments */
    @media (max-width: 768px) {
        .modal-dialog-scrollable .modal-body {
            max-height: calc(85vh - 120px);
        }
        
        #accountModal .modal-body,
        #viewAccountModal .modal-body,
        #transactionsModal .modal-body {
            max-height: calc(85vh - 160px);
        }
    }
</style>
@endpush

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-2">
        <div class="card stats-card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Accounts</h6>
                <h3 class="mb-0">{{ $stats['total'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card stats-card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Active</h6>
                <h3 class="mb-0 text-success">{{ $stats['active'] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    @foreach(['Asset', 'Liability', 'Equity', 'Income', 'Expense'] as $type)
    <div class="col-md-2">
        <div class="card stats-card {{ strtolower($type) }}">
            <div class="card-body">
                <h6 class="text-muted mb-1">{{ $type }}s</h6>
                <h3 class="mb-0">{{ $stats['by_type'][$type] ?? 0 }}</h3>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Advanced Filters Panel -->
<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bx bx-filter"></i> Advanced Filters</h6>
        <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
            <i class="bx bx-chevron-down"></i>
        </button>
    </div>
    <div class="collapse {{ request()->hasAny(['type', 'category', 'search', 'status', 'parent_id']) ? 'show' : '' }}" id="filterPanel">
        <div class="card-body">
            <form id="filterForm" method="GET" action="{{ route('modules.accounting.chart-of-accounts') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Account Type</label>
                        <select class="form-select form-select-sm" name="type" id="filterType">
                            <option value="">All Types</option>
                            <option value="Asset" {{ request('type') == 'Asset' ? 'selected' : '' }}>Asset</option>
                            <option value="Liability" {{ request('type') == 'Liability' ? 'selected' : '' }}>Liability</option>
                            <option value="Equity" {{ request('type') == 'Equity' ? 'selected' : '' }}>Equity</option>
                            <option value="Income" {{ request('type') == 'Income' ? 'selected' : '' }}>Income</option>
                            <option value="Expense" {{ request('type') == 'Expense' ? 'selected' : '' }}>Expense</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select class="form-select form-select-sm" name="category" id="filterCategory">
                            <option value="">All Categories</option>
                            @php
                                $categories = \App\Models\ChartOfAccount::whereNotNull('category')
                                    ->distinct()
                                    ->pluck('category')
                                    ->filter()
                                    ->sort()
                                    ->values();
                            @endphp
                            @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select class="form-select form-select-sm" name="status" id="filterStatus">
                            <option value="">All</option>
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Parent Account</label>
                        <select class="form-select form-select-sm" name="parent_id" id="filterParent">
                            <option value="">All</option>
                            <option value="null" {{ request('parent_id') == 'null' ? 'selected' : '' }}>Top Level Only</option>
                            @foreach($allAccounts->whereNull('parent_id') as $acc)
                            <option value="{{ $acc->id }}" {{ request('parent_id') == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Search</label>
                        <input type="text" class="form-control form-control-sm" name="search" id="searchAccounts" 
                               value="{{ request('search') }}" placeholder="Code, Name, or Description">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">View Mode</label>
                        <div class="btn-group view-mode-buttons w-100" role="group">
                            <input type="radio" class="btn-check" name="view" id="viewList" value="list" 
                                   {{ ($viewMode ?? 'list') == 'list' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="viewList"><i class="bx bx-list-ul"></i> List</label>
                            
                            <input type="radio" class="btn-check" name="view" id="viewTree" value="tree" 
                                   {{ ($viewMode ?? 'list') == 'tree' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-primary btn-sm" for="viewTree"><i class="bx bx-git-branch"></i> Tree</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill">
                            <i class="bx bx-filter"></i> Apply Filters
                        </button>
                        <a href="{{ route('modules.accounting.chart-of-accounts') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bx bx-refresh"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Actions Bar -->
<div class="bulk-actions-bar" id="bulkActionsBar">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <strong><span id="selectedCount">0</span> account(s) selected</strong>
            </div>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-success" onclick="bulkAction('activate')">
                <i class="bx bx-check"></i> Activate
            </button>
            <button class="btn btn-warning" onclick="bulkAction('deactivate')">
                <i class="bx bx-x"></i> Deactivate
            </button>
            <button class="btn btn-danger" onclick="bulkAction('delete')">
                <i class="bx bx-trash"></i> Delete
            </button>
            <button class="btn btn-secondary" onclick="clearSelection()">
                <i class="bx bx-x-circle"></i> Clear
            </button>
        </div>
    </div>
</div>

<!-- Accounts List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bx bx-list-ul"></i> Chart of Accounts</h5>
        <div class="btn-group btn-group-sm">
            <button class="btn btn-success" onclick="exportAccounts('csv')" title="Export CSV">
                <i class="bx bx-export"></i> CSV
                    </button>
            <button class="btn btn-danger" onclick="exportAccounts('pdf')" title="Export PDF">
                <i class="bx bx-file"></i> PDF
                    </button>
            <button class="btn btn-primary" onclick="openAccountModal()">
                <i class="bx bx-plus"></i> New Account
                    </button>
                </div>
            </div>
            <div class="card-body">
        @if(($viewMode ?? 'list') == 'tree')
            <!-- Tree View -->
            @foreach(['Asset', 'Liability', 'Equity', 'Income', 'Expense'] as $type)
                @php 
                    $typeAccounts = $groupedAccounts->get($type, collect([]));
                    $typeCount = is_countable($typeAccounts) ? count($typeAccounts) : ($typeAccounts instanceof \Illuminate\Support\Collection ? $typeAccounts->count() : 0);
                @endphp
                @if($typeCount > 0)
                <div class="mb-4">
                    <div class="account-type-header">
                        <div>
                            <i class="bx bx-{{ $type === 'Asset' ? 'building' : ($type === 'Liability' ? 'file-invoice-dollar' : ($type === 'Equity' ? 'pie-chart' : ($type === 'Income' ? 'trending-up' : 'trending-down'))) }}"></i>
                            {{ $type }}s
                        </div>
                        <span class="badge">{{ $typeCount }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th width="30"><input type="checkbox" class="form-check-input" onchange="toggleAllSelection(this)"></th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th class="text-end">Opening Balance</th>
                                    <th class="text-end">Current Balance</th>
                                    <th>Status</th>
                                    <th class="text-center" width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($typeAccounts->whereNull('parent_id') as $account)
                                    @include('modules.accounting.partials.account-tree-row', ['account' => $account, 'level' => 0])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endforeach
        @else
            <!-- List View -->
                @foreach(['Asset', 'Liability', 'Equity', 'Income', 'Expense'] as $type)
                @php 
                    $typeAccounts = $groupedAccounts->get($type, collect([]));
                    $typeCount = is_countable($typeAccounts) ? count($typeAccounts) : ($typeAccounts instanceof \Illuminate\Support\Collection ? $typeAccounts->count() : 0);
                @endphp
                @if($typeCount > 0)
                <div class="mb-4">
                    <div class="account-type-header">
                        <div>
                            <i class="bx bx-{{ $type === 'Asset' ? 'building' : ($type === 'Liability' ? 'file-invoice-dollar' : ($type === 'Equity' ? 'pie-chart' : ($type === 'Income' ? 'trending-up' : 'trending-down'))) }}"></i>
                        {{ $type }}s
                        </div>
                        <span class="badge">{{ $typeCount }}</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" id="accountsTable{{ $type }}">
                            <thead>
                                <tr>
                                    <th width="30"><input type="checkbox" class="form-check-input" onchange="toggleAllSelection(this)"></th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Parent Account</th>
                                    <th class="text-end">Opening Balance</th>
                                    <th class="text-end">Current Balance</th>
                                    <th>Status</th>
                                    <th class="text-center" width="250">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($typeAccounts as $account)
                                <tr class="account-row" data-id="{{ $account->id }}" data-parent="{{ $account->parent_id }}">
                                    <td><input type="checkbox" class="form-check-input account-checkbox" value="{{ $account->id }}"></td>
                                    <td><strong>{{ $account->code }}</strong></td>
                                    <td>{{ $account->name }}</td>
                                    <td><span class="badge bg-info">{{ $account->category ?? 'N/A' }}</span></td>
                                    <td>{{ $account->parent ? $account->parent->code . ' - ' . $account->parent->name : '-' }}</td>
                                    <td class="text-end">TZS {{ number_format($account->opening_balance, 2) }}</td>
                                    <td class="text-end">
                                        <strong class="{{ $account->current_balance > 0 ? 'balance-positive' : ($account->current_balance < 0 ? 'balance-negative' : 'balance-zero') }}">
                                            TZS {{ number_format($account->current_balance, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $account->is_active ? 'success' : 'secondary' }}">
                                            {{ $account->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-info" onclick="viewAccount({{ $account->id }})" title="View Details">
                                                <i class="bx bx-show"></i>
                                        </button>
                                            <button class="btn btn-primary" onclick="viewTransactions({{ $account->id }})" title="Transactions">
                                                <i class="bx bx-history"></i>
                                            </button>
                                            <button class="btn btn-warning" onclick="editAccount({{ $account->id }})" title="Edit">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            @if($account->canBeDeleted())
                                            <button class="btn btn-danger" onclick="deleteAccount({{ $account->id }})" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                            @endif
                                        </div>
                                        </td>
                                    </tr>
                                    @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @endforeach
        @endif
    </div>
</div>

<!-- Account Modal (Same as before, but enhanced) -->
@include('modules.accounting.partials.account-modal')

<!-- View Account Modal -->
@include('modules.accounting.partials.view-account-modal')

<!-- Transactions Modal -->
@include('modules.accounting.partials.transactions-modal')

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/select/1.7.0/js/dataTables.select.min.js"></script>
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<script>
const token = '{{ csrf_token() }}';
const baseUrl = '{{ route("modules.accounting.chart-of-accounts") }}';
let selectedAccounts = [];

// Initialize DataTables for each account type
@foreach(['Asset', 'Liability', 'Equity', 'Income', 'Expense'] as $type)
@if($groupedAccounts->has($type))
$('#accountsTable{{ $type }}').DataTable({
    pageLength: 25,
    order: [[1, 'asc']],
    columnDefs: [
        { orderable: false, targets: [0, 8] }
    ],
    dom: 'Bfrtip',
    buttons: []
});
@endif
@endforeach

// Account selection handling
function toggleAllSelection(checkbox) {
    const isChecked = checkbox.checked;
    document.querySelectorAll('.account-checkbox').forEach(cb => {
        cb.checked = isChecked;
        if (isChecked) {
            if (!selectedAccounts.includes(parseInt(cb.value))) {
                selectedAccounts.push(parseInt(cb.value));
            }
        } else {
            selectedAccounts = selectedAccounts.filter(id => id !== parseInt(cb.value));
        }
    });
    updateBulkActionsBar();
}

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('account-checkbox')) {
        const accountId = parseInt(e.target.value);
        if (e.target.checked) {
            if (!selectedAccounts.includes(accountId)) {
                selectedAccounts.push(accountId);
            }
        } else {
            selectedAccounts = selectedAccounts.filter(id => id !== accountId);
        }
        updateBulkActionsBar();
    }
});

function updateBulkActionsBar() {
    const bar = document.getElementById('bulkActionsBar');
    const count = document.getElementById('selectedCount');
    if (selectedAccounts.length > 0) {
        bar.classList.add('show');
        count.textContent = selectedAccounts.length;
    } else {
        bar.classList.remove('show');
    }
}

function clearSelection() {
    selectedAccounts = [];
    document.querySelectorAll('.account-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('input[type="checkbox"][onchange*="toggleAllSelection"]').forEach(cb => cb.checked = false);
    updateBulkActionsBar();
}

async function bulkAction(action) {
    if (selectedAccounts.length === 0) {
        alert('Please select at least one account');
        return;
    }

    if (action === 'delete' && !confirm(`Are you sure you want to delete ${selectedAccounts.length} account(s)? This action cannot be undone.`)) {
        return;
    }

    try {
        const response = await fetch('{{ route("modules.accounting.accounts.bulk-operations") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                account_ids: selectedAccounts
            })
        });

        const data = await response.json();
        if (data.success) {
            alert(data.message);
            clearSelection();
            location.reload();
        } else {
            alert(data.message || 'Error performing bulk operation');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
}

// Export functions
function exportAccounts(format) {
    const params = new URLSearchParams(window.location.search);
    params.set('export', format);
    window.location.href = baseUrl + '?' + params.toString();
}

// View transactions
async function viewTransactions(accountId) {
    const modal = new bootstrap.Modal(document.getElementById('transactionsModal'));
    const content = document.getElementById('transactionsContent');
    
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border"></div><p class="mt-2">Loading transactions...</p></div>';
    modal.show();

    try {
        const response = await fetch(`{{ route("modules.accounting.accounts.transactions", ["id" => ":id"]) }}`.replace(':id', accountId));
        const data = await response.json();
        
        if (data.success) {
            renderTransactions(data);
        } else {
            content.innerHTML = '<div class="alert alert-danger">Error loading transactions</div>';
        }
    } catch (error) {
        content.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
    }
}

function renderTransactions(data) {
    const content = document.getElementById('transactionsContent');
    const account = data.account;
    const transactions = data.transactions.data || [];
    
    let html = `
        <div class="mb-3">
            <h6>Account: ${account.code} - ${account.name}</h6>
            <div class="row">
                <div class="col-md-4">
                    <strong>Opening Balance:</strong> TZS ${parseFloat(account.opening_balance).toLocaleString('en-US', {minimumFractionDigits: 2})}
            </div>
                <div class="col-md-4">
                    <strong>Current Balance:</strong> TZS ${parseFloat(account.current_balance).toLocaleString('en-US', {minimumFractionDigits: 2})}
                </div>
            </div>
            </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Description</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    transactions.forEach(t => {
        html += `
            <tr>
                <td>${new Date(t.transaction_date).toLocaleDateString()}</td>
                <td>${t.reference_no || '-'}</td>
                <td>${t.description || '-'}</td>
                <td class="text-end ${t.type === 'Debit' ? 'transaction-debit' : ''}">${t.type === 'Debit' ? parseFloat(t.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-'}</td>
                <td class="text-end ${t.type === 'Credit' ? 'transaction-credit' : ''}">${t.type === 'Credit' ? parseFloat(t.amount).toLocaleString('en-US', {minimumFractionDigits: 2}) : '-'}</td>
                <td class="text-end">${parseFloat(t.running_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;

    content.innerHTML = html;
}

// Include existing functions from original file
function openAccountModal(id = null) {
    // Clean up any existing modals
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('accountModal');
        const modal = new bootstrap.Modal(modalElement);
        const form = document.getElementById('accountForm');
        const title = document.getElementById('accountModalTitle');
        
        form.reset();
        document.getElementById('accountId').value = '';
        document.getElementById('accountCode').readOnly = false;
        
        if (id) {
            title.textContent = 'Edit Account';
            loadAccountData(id);
        } else {
            title.textContent = 'New Account';
        }
        
        modal.show();
    }, 100);
}

async function loadAccountData(id) {
    try {
        const response = await fetch(`{{ route("modules.accounting.accounts.show", ["id" => ":id"]) }}`.replace(':id', id));
        const data = await response.json();
        
        if (data.success) {
            const account = data.account;
            document.getElementById('accountId').value = account.id;
            document.getElementById('accountCode').value = account.code;
            document.getElementById('accountCode').readOnly = true;
            document.getElementById('accountName').value = account.name;
            document.getElementById('accountType').value = account.type;
            document.getElementById('accountCategory').value = account.category || '';
            document.getElementById('accountParent').value = account.parent_id || '';
            document.getElementById('accountOpeningBalance').value = account.opening_balance || 0;
            document.getElementById('accountOpeningDate').value = account.opening_balance_date || '';
            document.getElementById('accountSortOrder').value = account.sort_order || 0;
            document.getElementById('accountIsActive').checked = account.is_active;
            document.getElementById('accountDescription').value = account.description || '';
        }
    } catch (error) {
        console.error('Error loading account:', error);
        alert('Error loading account data');
    }
}

async function viewAccount(id) {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('viewAccountModal');
        const modal = new bootstrap.Modal(modalElement);
        const content = document.getElementById('viewAccountContent');
        
        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border"></div><p class="mt-2">Loading...</p></div>';
        modal.show();
        
        loadAccountViewData(id, content);
    }, 100);
}

async function loadAccountViewData(id, content) {
    try {
        const response = await fetch(`{{ route("modules.accounting.accounts.show", ["id" => ":id"]) }}`.replace(':id', id));
        const data = await response.json();
        
        if (data.success) {
            const acc = data.account;
            const balance = acc.current_balance || 0;
            
            content.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Code:</th><td>${acc.code}</td></tr>
                            <tr><th>Name:</th><td>${acc.name}</td></tr>
                            <tr><th>Type:</th><td><span class="badge bg-primary">${acc.type}</span></td></tr>
                            <tr><th>Category:</th><td>${acc.category || 'N/A'}</td></tr>
                            <tr><th>Parent:</th><td>${acc.parent ? acc.parent.code + ' - ' + acc.parent.name : 'None'}</td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-${acc.is_active ? 'success' : 'secondary'}">${acc.is_active ? 'Active' : 'Inactive'}</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr><th>Opening Balance:</th><td class="text-end">TZS ${parseFloat(acc.opening_balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                            <tr><th>Current Balance:</th><td class="text-end"><strong class="text-${balance >= 0 ? 'success' : 'danger'}">TZS ${parseFloat(balance).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></td></tr>
                            <tr><th>Opening Date:</th><td>${acc.opening_balance_date || 'N/A'}</td></tr>
                            <tr><th>Sort Order:</th><td>${acc.sort_order || 0}</td></tr>
                            <tr><th>Created:</th><td>${new Date(acc.created_at).toLocaleString()}</td></tr>
                        </table>
                    </div>
                    <div class="col-12">
                        <strong>Description:</strong>
                        <p>${acc.description || 'No description'}</p>
                    </div>
                    ${acc.children && acc.children.length > 0 ? `
                    <div class="col-12">
                        <strong>Child Accounts (${acc.children.length}):</strong>
                        <ul class="list-group mt-2">
                            ${acc.children.map(child => `
                                <li class="list-group-item d-flex justify-content-between">
                                    <span>${child.code} - ${child.name}</span>
                                    <span class="badge bg-info">${child.is_active ? 'Active' : 'Inactive'}</span>
                                </li>
                            `).join('')}
                        </ul>
                    </div>
                    ` : ''}
                </div>
            `;
        }
    } catch (error) {
        content.innerHTML = '<div class="alert alert-danger">Error loading account details</div>';
    }
}

function editAccount(id) {
    openAccountModal(id);
}

async function deleteAccount(id) {
    if (!confirm('Are you sure you want to delete this account? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route("modules.accounting.accounts.delete", ["id" => ":id"]) }}`.replace(':id', id), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error deleting account');
        }
    } catch (error) {
        alert('Error deleting account: ' + error.message);
    }
}

document.getElementById('accountForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const accountId = document.getElementById('accountId').value;
    const url = accountId 
        ? `{{ route("modules.accounting.accounts.update", ["id" => ":id"]) }}`.replace(':id', accountId)
        : '{{ route("modules.accounting.accounts.store") }}';
    const method = accountId ? 'PUT' : 'POST';
    
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
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('accountModal'));
            if (modalInstance) {
                modalInstance.hide();
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            location.reload();
        } else {
            alert(data.message || 'Error saving account');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});
</script>
@endpush
@endsection
