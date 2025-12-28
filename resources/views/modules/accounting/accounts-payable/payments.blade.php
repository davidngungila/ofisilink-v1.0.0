@extends('layouts.app')

@section('title', 'Bill Payments')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Accounts Payable - Bill Payments</h4>
</div>
@endsection

@push('styles')
<style>
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
</style>
@endpush

@section('content')
<!-- Stats -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="mb-0">Total Payments</h6>
                <h3 class="mb-0">{{ $payments->total() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="mb-0">Total Paid</h6>
                <h3 class="mb-0">TZS {{ number_format($payments->sum('amount'), 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="mb-0">Pending Bills</h6>
                <h3 class="mb-0">{{ $bills->count() }}</h3>
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
                <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="{{ request()->hasAny(['bill_id', 'date_from', 'date_to', 'payment_method', 'vendor_id', 'search']) ? 'true' : 'false' }}">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="collapse {{ request()->hasAny(['bill_id', 'date_from', 'date_to', 'payment_method', 'vendor_id', 'search']) ? 'show' : '' }}" id="filterCollapse">
                <div class="card-body">
                    <form method="GET" id="filterForm" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-search"></i> Search</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Payment No, Bill No, Vendor..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Vendor</label>
                            <select name="vendor_id" class="form-select form-select-sm">
                                <option value="">All Vendors</option>
                                @php
                                    $vendors = \App\Models\Vendor::where('is_active', true)->orderBy('name')->get();
                                @endphp
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bill</label>
                            <select name="bill_id" class="form-select form-select-sm">
                                <option value="">All Bills</option>
                                @foreach($bills as $bill)
                                <option value="{{ $bill->id }}" {{ request('bill_id') == $bill->id ? 'selected' : '' }}>
                                    {{ $bill->bill_no }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="">All Methods</option>
                                <option value="Cash" {{ request('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                                <option value="Bank Transfer" {{ request('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="Cheque" {{ request('payment_method') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="Mobile Money" {{ request('payment_method') == 'Mobile Money' ? 'selected' : '' }}>Mobile Money</option>
                                <option value="Credit Card" {{ request('payment_method') == 'Credit Card' ? 'selected' : '' }}>Credit Card</option>
                                <option value="Other" {{ request('payment_method') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <div class="input-group input-group-sm">
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" placeholder="From">
                                <span class="input-group-text">to</span>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" placeholder="To">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="btn-group" role="group">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="{{ route('modules.accounting.ap.payments') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="exportPaymentsPdf()">
                                    <i class="fas fa-file-pdf"></i> Export PDF
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="exportPaymentsExcel()">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payments List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Bill Payments</h5>
                <button class="btn btn-primary" onclick="openPaymentModal()">
                    <i class="fas fa-plus"></i> Record Payment
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="paymentsTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Payment No</th>
                                <th>Bill No</th>
                                <th>Vendor</th>
                                <th>Date</th>
                                <th class="text-end">Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                            <tr>
                                <td>
                                    <strong class="text-primary">{{ $payment->payment_no }}</strong>
                                </td>
                                <td>
                                    <a href="#" class="text-decoration-none" onclick="viewBillDetails({{ $payment->bill_id ?? 0 }}); return false;">
                                        {{ $payment->bill->bill_no ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $payment->bill->vendor->name ?? 'N/A' }}</strong>
                                        @if($payment->bill->vendor)
                                        <br><small class="text-muted">{{ $payment->bill->vendor->vendor_code ?? '' }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <i class="fas fa-calendar text-muted"></i> {{ $payment->payment_date->format('d M Y') }}
                                        <br><small class="text-muted">{{ $payment->payment_date->format('H:i') }}</small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <strong class="text-success">TZS {{ number_format($payment->amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $payment->payment_method == 'Cash' ? 'success' : ($payment->payment_method == 'Bank Transfer' ? 'primary' : 'info') }}">
                                        {{ $payment->payment_method }}
                                    </span>
                                </td>
                                <td>
                                    {{ $payment->reference_no ?? '-' }}
                                    @if($payment->bank_account)
                                    <br><small class="text-muted">{{ $payment->bank_account->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($payment->bill)
                                        @php
                                            $billStatus = $payment->bill->status;
                                            $statusClass = $billStatus == 'Paid' ? 'success' : ($billStatus == 'Partially Paid' ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge bg-{{ $statusClass }}">{{ $billStatus }}</span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-info btn-sm" onclick="viewPayment({{ $payment->id }})" title="View Details">
                                            View
                                        </button>
                                        <button class="btn btn-danger btn-sm" onclick="exportPaymentPdf({{ $payment->id }})" title="Download PDF">
                                            PDF
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No payments found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($payments->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-end">
                                    <strong class="text-success">TZS {{ number_format($payments->sum('amount'), 2) }}</strong>
                                </th>
                                <th colspan="4"></th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <small class="text-muted">
                            Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} payments
                        </small>
                    </div>
                    <div>
                        {{ $payments->links() }}
                    </div>
                </div>
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
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Bill <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentBill" name="bill_id" required onchange="loadBillDetails()">
                                <option value="">Select Bill</option>
                                @foreach($bills as $bill)
                                <option value="{{ $bill->id }}" data-balance="{{ $bill->balance }}" data-bill-no="{{ $bill->bill_no }}">
                                    {{ $bill->bill_no }} - {{ $bill->vendor->name ?? 'N/A' }} (Balance: TZS {{ number_format($bill->balance, 2) }})
                                </option>
                                @endforeach
                            </select>
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
                                @foreach($bankAccounts as $bank)
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
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Record Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Payment Modal -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Payment Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewPaymentContent">
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

function openPaymentModal() {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('paymentModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        
        document.getElementById('paymentForm').reset();
        document.getElementById('paymentBillBalance').value = '';
        
        modal.show();
        
        modalElement.addEventListener('shown.bs.modal', function() {
            $(this).css('z-index', 1050);
            $('.modal-backdrop').css('z-index', 1040);
            $(this).find('.modal-content').css('pointer-events', 'auto');
        }, { once: true });
    }, 100);
}

function loadBillDetails() {
    const select = document.getElementById('paymentBill');
    const option = select.options[select.selectedIndex];
    const balance = option.getAttribute('data-balance');
    
    if (balance) {
        document.getElementById('paymentBillBalance').value = `TZS ${parseFloat(balance).toLocaleString('en-US', {minimumFractionDigits: 2})}`;
        document.getElementById('paymentAmount').value = balance;
        document.getElementById('paymentAmount').max = balance;
    } else {
        document.getElementById('paymentBillBalance').value = '';
        document.getElementById('paymentAmount').value = '';
        document.getElementById('paymentAmount').max = '';
    }
}

async function viewPayment(id) {
    $('.modal').modal('hide');
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    
    setTimeout(() => {
        const modalElement = document.getElementById('viewPaymentModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        const content = document.getElementById('viewPaymentContent');
        
        content.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>';
        
        modal.show();
        
        modalElement.addEventListener('shown.bs.modal', function() {
            $(this).css('z-index', 1050);
            $('.modal-backdrop').css('z-index', 1040);
            $(this).find('.modal-content').css('pointer-events', 'auto');
            
            // Load payment data
            fetch(`{{ url('/modules/accounting/accounts-payable/payments') }}/${id}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(r => {
                    if (!r.ok) {
                        throw new Error(`HTTP error! status: ${r.status}`);
                    }
                    return r.json();
                })
                .then(data => {
                    if (data.success && data.payment) {
                        const p = data.payment;
                        const paymentDate = p.payment_date ? new Date(p.payment_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A';
                        const createdAt = p.created_at ? new Date(p.created_at).toLocaleString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';
                        
                        content.innerHTML = `
                            <div class="row mb-3">
                                <div class="col-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h5 class="mb-0">${p.payment_no || 'N/A'}</h5>
                                                    <small class="text-muted">Payment Details</small>
                                                </div>
                                                <div class="text-end">
                                                    <h4 class="mb-0 text-success">TZS ${parseFloat(p.amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</h4>
                                                    <small class="text-muted">Payment Amount</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2 mb-3">Payment Information</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="40%">Payment No:</th>
                                            <td><strong class="text-primary">${p.payment_no || 'N/A'}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Bill No:</th>
                                            <td>
                                                <a href="#" class="text-decoration-none">${p.bill?.bill_no || 'N/A'}</a>
                                                ${p.bill?.status ? `<span class="badge bg-${p.bill.status === 'Paid' ? 'success' : p.bill.status === 'Partially Paid' ? 'warning' : 'danger'} ms-2">${p.bill.status}</span>` : ''}
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Vendor:</th>
                                            <td><strong>${p.bill?.vendor?.name || 'N/A'}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Date:</th>
                                            <td><i class="fas fa-calendar text-muted"></i> ${paymentDate}</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Method:</th>
                                            <td>
                                                <span class="badge bg-info">${p.payment_method || 'N/A'}</span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="border-bottom pb-2 mb-3">Additional Details</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th width="40%">Bank Account:</th>
                                            <td>${p.bank_account?.name || p.bank_account_id ? 'Selected' : '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>Reference No:</th>
                                            <td>${p.reference_no || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th>Bill Balance:</th>
                                            <td>
                                                <strong class="text-${p.bill?.balance > 0 ? 'warning' : 'success'}">
                                                    TZS ${parseFloat(p.bill?.balance || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}
                                                </strong>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Bill Total:</th>
                                            <td>TZS ${parseFloat(p.bill?.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                        </tr>
                                        <tr>
                                            <th>Created By:</th>
                                            <td>${p.creator?.name || 'System'}</td>
                                        </tr>
                                        <tr>
                                            <th>Created At:</th>
                                            <td><i class="fas fa-clock text-muted"></i> ${createdAt}</td>
                                        </tr>
                                    </table>
                                </div>
                                ${p.notes ? `
                                <div class="col-12 mt-3">
                                    <h6 class="border-bottom pb-2 mb-2">Notes</h6>
                                    <div class="alert alert-light">
                                        <p class="mb-0">${p.notes}</p>
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        `;
                    } else {
                        throw new Error(data.message || 'Invalid response format');
                    }
                })
                .catch(err => {
                    console.error('Payment details error:', err);
                    content.innerHTML = `
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Error loading payment details</h6>
                            <p class="mb-0">${err.message || 'Unable to load payment information. Please try again.'}</p>
                        </div>
                    `;
                });
        }, { once: true });
    }, 100);
}

function exportPaymentsPdf() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'pdf');
    window.location.href = '{{ route("modules.accounting.ap.payments") }}?' + params.toString();
}

function exportPaymentsExcel() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = '{{ route("modules.accounting.ap.payments") }}?' + params.toString();
}

function exportPaymentPdf(id) {
    window.location.href = `/modules/accounting/accounts-payable/payments/${id}/pdf`;
}

function viewBillDetails(billId) {
    if (!billId || billId === 0) {
        alert('Bill information not available');
        return;
    }
    // You can implement a modal or redirect to bill details page
    window.open(`/modules/accounting/accounts-payable/bills/${billId}`, '_blank');
}

document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("modules.accounting.ap.payments.store") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        if (data.success) {
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
            if (modalInstance) {
                modalInstance.hide();
            }
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            location.reload();
        } else {
            alert(data.message || 'Error recording payment');
        }
    } catch (error) {
        alert('Error: ' + error.message);
    }
});

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
