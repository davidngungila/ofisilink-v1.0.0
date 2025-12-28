@extends('layouts.app')

@section('title', 'Customers Management')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Accounts Receivable - Customers</h4>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
@php
    $totalCustomers = $customers->count();
    $activeCustomers = $customers->where('is_active', true)->count();
    $totalReceivables = $customers->sum('total_receivable');
    $totalOverdue = 0;
    foreach ($customers as $customer) {
        $overdueInvoices = $customer->invoices()
            ->whereIn('status', ['Sent', 'Partially Paid'])
            ->where('due_date', '<', now())
            ->get();
        $totalOverdue += $overdueInvoices->sum('balance');
    }
@endphp

<!-- Summary Cards -->
<div class="row mb-3">
    <div class="col-md-3">
        <div class="card summary-card border-left-primary">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Customers</h6>
                <h4 class="mb-0 text-primary">{{ $totalCustomers }}</h4>
                <small class="text-muted">{{ $activeCustomers }} Active</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card border-left-success">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Receivables</h6>
                <h4 class="mb-0 text-success">TZS {{ number_format($totalReceivables, 2) }}</h4>
                <small class="text-muted">Outstanding Amount</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card border-left-danger">
            <div class="card-body">
                <h6 class="text-muted mb-1">Overdue Amount</h6>
                <h4 class="mb-0 text-danger">TZS {{ number_format($totalOverdue, 2) }}</h4>
                <small class="text-muted">Past Due Date</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card border-left-info">
            <div class="card-body">
                <h6 class="text-muted mb-1">Active Customers</h6>
                <h4 class="mb-0 text-info">{{ $activeCustomers }}</h4>
                <small class="text-muted">{{ $totalCustomers > 0 ? number_format(($activeCustomers / $totalCustomers) * 100, 1) : 0 }}% of total</small>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filters -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-filter"></i> Advanced Filters</h6>
                <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="{{ request()->hasAny(['search', 'status', 'receivables', 'payment_terms']) ? 'true' : 'false' }}">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="collapse {{ request()->hasAny(['search', 'status', 'receivables', 'payment_terms']) ? 'show' : '' }}" id="filterCollapse">
                <div class="card-body">
                    <form method="GET" id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-search"></i> Search</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Name, Code, Email, Phone..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Receivables</label>
                            <select name="receivables" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="zero" {{ request('receivables') == 'zero' ? 'selected' : '' }}>Zero</option>
                                <option value="low" {{ request('receivables') == 'low' ? 'selected' : '' }}>Low (0-100K)</option>
                                <option value="medium" {{ request('receivables') == 'medium' ? 'selected' : '' }}>Medium (100K-1M)</option>
                                <option value="high" {{ request('receivables') == 'high' ? 'selected' : '' }}>High (1M+)</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Payment Terms</label>
                            <select name="payment_terms" class="form-select form-select-sm">
                                <option value="">All Terms</option>
                                <option value="0-15" {{ request('payment_terms') == '0-15' ? 'selected' : '' }}>0-15 Days</option>
                                <option value="16-30" {{ request('payment_terms') == '16-30' ? 'selected' : '' }}>16-30 Days</option>
                                <option value="31-60" {{ request('payment_terms') == '31-60' ? 'selected' : '' }}>31-60 Days</option>
                                <option value="60+" {{ request('payment_terms') == '60+' ? 'selected' : '' }}>60+ Days</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="{{ route('modules.accounting.ar.customers') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="exportCustomersPdf()">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="exportCustomersExcel()">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customers List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Customers</h5>
                <button class="btn btn-primary" onclick="openCustomerModal()">
                    <i class="fas fa-plus"></i> New Customer
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="customersTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Credit Limit</th>
                                <th class="text-end">Receivables</th>
                                <th>Payment Terms</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($paginatedCustomers as $customer)
                            @php
                                $overdueInvoices = $customer->invoices()
                                    ->whereIn('status', ['Sent', 'Partially Paid'])
                                    ->where('due_date', '<', now())
                                    ->get();
                                $overdueAmount = $overdueInvoices->sum('balance');
                            @endphp
                            <tr>
                                <td><strong class="text-primary">{{ $customer->customer_code }}</strong></td>
                                <td>
                                    <div>
                                        <strong>{{ $customer->name }}</strong>
                                        @if($customer->contact_person)
                                        <br><small class="text-muted">{{ $customer->contact_person }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-phone text-muted"></i> {{ $customer->phone ?? $customer->mobile ?? '-' }}
                                        @if($customer->phone && $customer->mobile)
                                        <br><small class="text-muted">{{ $customer->mobile }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($customer->email)
                                    <i class="fas fa-envelope text-muted"></i> 
                                    <a href="mailto:{{ $customer->email }}" class="text-decoration-none">{{ $customer->email }}</a>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>TZS {{ number_format($customer->credit_limit ?? 0, 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    <div>
                                        <strong class="text-{{ ($customer->total_receivable ?? 0) > 0 ? 'warning' : 'success' }}">
                                            TZS {{ number_format($customer->total_receivable ?? 0, 2) }}
                                        </strong>
                                        @if($overdueAmount > 0)
                                        <br><small class="text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> Overdue: TZS {{ number_format($overdueAmount, 2) }}
                                        </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $customer->payment_terms_days ?? 30 }} days</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $customer->is_active ? 'success' : 'secondary' }}">
                                        {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-info btn-sm" onclick="viewCustomer({{ $customer->id }})" title="View Details">
                                            View
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="editCustomer({{ $customer->id }})" title="Edit">
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No customers found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            Showing {{ $paginatedCustomers->firstItem() ?? 0 }} to {{ $paginatedCustomers->lastItem() ?? 0 }} of {{ $paginatedCustomers->total() }} customers
                        </small>
                    </div>
                    <div>
                        {{ $paginatedCustomers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="customerModalTitle">New Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="customerForm">
                <input type="hidden" id="customerId" name="id">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Customer Code</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="customerCode" name="customer_code" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateCustomerCode()">
                                    <i class="fas fa-sync"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerName" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Person</label>
                            <input type="text" class="form-control" id="contactPerson" name="contact_person">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="customerEmail" name="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" id="customerPhone" name="phone">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile</label>
                            <input type="text" class="form-control" id="customerMobile" name="mobile">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" id="customerAddress" name="address">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">City</label>
                            <input type="text" class="form-control" id="customerCity" name="city">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tax ID</label>
                            <input type="text" class="form-control" id="taxId" name="tax_id">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Account</label>
                            <select class="form-select" id="customerAccount" name="account_id">
                                <option value="">Select Account</option>
                                @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Credit Limit</label>
                            <input type="number" step="0.01" class="form-control" id="creditLimit" name="credit_limit" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Terms</label>
                            <select class="form-select" id="paymentTerms" name="payment_terms">
                                <option value="Net 15">Net 15</option>
                                <option value="Net 30" selected>Net 30</option>
                                <option value="Net 45">Net 45</option>
                                <option value="Net 60">Net 60</option>
                                <option value="Custom">Custom</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Terms (Days)</label>
                            <input type="number" class="form-control" id="paymentTermsDays" name="payment_terms_days" value="30">
                        </div>
                        <div class="col-md-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="isActive" name="is_active" checked>
                                <label class="form-check-label" for="isActive">
                                    Active
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" id="customerNotes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Customer Modal -->
<div class="modal fade" id="viewCustomerModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Customer Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewCustomerContent">
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

@push('scripts')
<script>
const token = '{{ csrf_token() }}';

function openCustomerModal() {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('customerModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        
        document.getElementById('customerForm').reset();
        document.getElementById('customerId').value = '';
        document.getElementById('customerModalTitle').textContent = 'New Customer';
        generateCustomerCode();
        
        modal.show();
    }, 100);
}

function generateCustomerCode() {
    const formData = new FormData();
    formData.append('generate_code', '1');
    
    fetch('{{ route("modules.accounting.ar.customers.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.code) {
            document.getElementById('customerCode').value = data.code;
        }
    })
    .catch(err => {
        console.error('Error generating code:', err);
        // Fallback to manual generation
        document.getElementById('customerCode').value = 'CUS' + Date.now().toString().slice(-5);
    });
}

async function viewCustomer(id) {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('viewCustomerModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const content = document.getElementById('viewCustomerContent');
        
        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>';
        modal.show();
        
        fetch(`{{ url('/modules/accounting/accounts-receivable/customers') }}/${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => {
            if (!r.ok) throw new Error(`HTTP error! status: ${r.status}`);
            return r.json();
        })
        .then(data => {
            if (data.success && data.customer) {
                const c = data.customer;
                content.innerHTML = `
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0">${c.name || 'N/A'}</h5>
                                            <small class="text-muted">${c.customer_code || ''}</small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-${c.is_active ? 'success' : 'secondary'}">${c.is_active ? 'Active' : 'Inactive'}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Contact Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr><th width="40%">Contact Person:</th><td>${c.contact_person || '-'}</td></tr>
                                <tr><th>Email:</th><td>${c.email || '-'}</td></tr>
                                <tr><th>Phone:</th><td>${c.phone || '-'}</td></tr>
                                <tr><th>Mobile:</th><td>${c.mobile || '-'}</td></tr>
                                <tr><th>Address:</th><td>${c.address || '-'}</td></tr>
                                <tr><th>City:</th><td>${c.city || '-'}</td></tr>
                                <tr><th>Tax ID:</th><td>${c.tax_id || '-'}</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Financial Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr><th width="40%">Credit Limit:</th><td>TZS ${parseFloat(c.credit_limit || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</td></tr>
                                <tr><th>Payment Terms:</th><td>${c.payment_terms || 'Net 30'} (${c.payment_terms_days || 30} days)</td></tr>
                                <tr><th>Receivables:</th><td><strong class="text-warning">TZS ${parseFloat(c.total_receivable || 0).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong></td></tr>
                            </table>
                        </div>
                        ${c.notes ? `<div class="col-12 mt-3"><h6 class="border-bottom pb-2 mb-2">Notes</h6><div class="alert alert-light"><p class="mb-0">${c.notes}</p></div></div>` : ''}
                    </div>
                `;
            } else {
                throw new Error(data.message || 'Invalid response');
            }
        })
        .catch(err => {
            console.error('Customer details error:', err);
            content.innerHTML = `<div class="alert alert-danger"><h6><i class="fas fa-exclamation-triangle"></i> Error loading customer details</h6><p class="mb-0">${err.message || 'Unable to load customer information.'}</p></div>`;
        });
    }, 100);
}

function editCustomer(id) {
    viewCustomer(id);
    // After loading, populate edit form
    setTimeout(() => {
        fetch(`{{ url('/modules/accounting/accounts-receivable/customers') }}/${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.customer) {
                const c = data.customer;
                document.getElementById('customerId').value = c.id;
                document.getElementById('customerCode').value = c.customer_code || '';
                document.getElementById('customerName').value = c.name || '';
                document.getElementById('contactPerson').value = c.contact_person || '';
                document.getElementById('customerEmail').value = c.email || '';
                document.getElementById('customerPhone').value = c.phone || '';
                document.getElementById('customerMobile').value = c.mobile || '';
                document.getElementById('customerAddress').value = c.address || '';
                document.getElementById('customerCity').value = c.city || '';
                document.getElementById('taxId').value = c.tax_id || '';
                document.getElementById('customerAccount').value = c.account_id || '';
                document.getElementById('creditLimit').value = c.credit_limit || 0;
                document.getElementById('paymentTerms').value = c.payment_terms || 'Net 30';
                document.getElementById('paymentTermsDays').value = c.payment_terms_days || 30;
                document.getElementById('isActive').checked = c.is_active || false;
                document.getElementById('customerNotes').value = c.notes || '';
                document.getElementById('customerModalTitle').textContent = 'Edit Customer';
                
                bootstrap.Modal.getInstance(document.getElementById('viewCustomerModal'))?.hide();
                const modal = new bootstrap.Modal(document.getElementById('customerModal'));
                modal.show();
            }
        });
    }, 500);
}

function exportCustomersPdf() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'pdf');
    window.location.href = '{{ route("modules.accounting.ar.customers") }}?' + params.toString();
}

function exportCustomersExcel() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = '{{ route("modules.accounting.ar.customers") }}?' + params.toString();
}

document.getElementById('customerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const customerId = document.getElementById('customerId').value;
    let url, method;
    
    if (customerId) {
        // For update, use POST with _method override
        url = `{{ url('/modules/accounting/accounts-receivable/customers') }}/${customerId}`;
        method = 'POST';
        formData.append('_method', 'PUT');
    } else {
        // For create, use POST
        url = '{{ route("modules.accounting.ar.customers.store") }}';
        method = 'POST';
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Show success popup
            showSuccessMessage(data.message || (customerId ? 'Customer updated successfully!' : 'Customer created successfully!'));
            
            // Close modal
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('customerModal'));
            if (modalInstance) {
                modalInstance.hide();
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            
            // Reload page after a short delay
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showErrorMessage(data.message || 'Error saving customer');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        showErrorMessage('Error: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Success message function
function showSuccessMessage(message) {
    // Create prominent success alert at top of page
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    alertDiv.style.zIndex = '10000';
    alertDiv.style.minWidth = '400px';
    alertDiv.style.maxWidth = '90%';
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle fa-2x me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">Success!</h5>
                <p class="mb-0">${message}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Insert at the beginning of body
    document.body.insertBefore(alertDiv, document.body.firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 5000);
    
    // Create toast notification as well
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-success border-0';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-check-circle me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    // Get or create toast container
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
    bsToast.show();
    
    // Remove toast element after it's hidden
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
    
    // Request notification permission and show browser notification
    if ('Notification' in window) {
        if (Notification.permission === 'granted') {
            new Notification('Success - Customer Management', {
                body: message,
                icon: '/favicon.ico',
                badge: '/favicon.ico'
            });
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    new Notification('Success - Customer Management', {
                        body: message,
                        icon: '/favicon.ico',
                        badge: '/favicon.ico'
                    });
                }
            });
        }
    }
}

// Error message function
function showErrorMessage(message) {
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white bg-danger border-0';
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-exclamation-circle me-2"></i>${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', function() {
        toast.remove();
    });
}
</script>
@endpush
@endsection
