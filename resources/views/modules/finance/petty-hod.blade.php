@extends('layouts.app')

@section('title', 'HOD Petty Cash Approval - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="card-title mb-0"><i class="bx bx-money me-2"></i>HOD Approval Dashboard</h4>
            <p class="text-muted mb-0">Review and approve petty cash requests, invoices, and bills</p>
          </div>
          <div>
            <a href="{{ route('petty-cash.index') }}" class="btn btn-outline-primary">
              <i class="bx bx-arrow-back"></i> Back to Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')
<!-- Statistics Dashboard -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card h-100 border-start border-start-4 border-start-danger">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-lg me-3">
            <span class="avatar-initial rounded-circle bg-label-danger">
              <i class="bx bx-time fs-4"></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1">Pending My Action</h6>
            <h4 class="mb-0 text-danger">{{ $counts['pending_hod'] ?? 0 }}</h4>
            <small class="text-muted">Requires attention</small>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card h-100 border-start border-start-4 border-start-info">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-lg me-3">
            <span class="avatar-initial rounded-circle bg-label-info">
              <i class="bx bx-user-check fs-4"></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1">Pending CEO</h6>
            <h4 class="mb-0 text-info">{{ $counts['pending_ceo'] ?? 0 }}</h4>
            <small class="text-muted">Awaiting CEO approval</small>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card h-100 border-start border-start-4 border-start-success">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-lg me-3">
            <span class="avatar-initial rounded-circle bg-label-success">
              <i class="bx bx-check-circle fs-4"></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1">Approved</h6>
            <h4 class="mb-0 text-success">{{ $counts['approved'] ?? 0 }}</h4>
            <small class="text-muted">Ready for payment</small>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
    <div class="card h-100 border-start border-start-4 border-start-primary">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="avatar avatar-lg me-3">
            <span class="avatar-initial rounded-circle bg-label-primary">
              <i class="bx bx-money fs-4"></i>
            </span>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1">Paid</h6>
            <h4 class="mb-0 text-primary">{{ $counts['paid'] ?? 0 }}</h4>
            <small class="text-muted">Completed payments</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">
    <div class="card shadow-sm">
      <div class="card-header bg-white border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div>
            <h5 class="card-title mb-0">
              <i class="bx bx-money me-2 text-primary"></i>Petty Cash Requests
            </h5>
            <small class="text-muted">HOD & System Admin Approval Dashboard</small>
          </div>
          <div class="d-flex gap-2 mt-2 mt-md-0">
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleFilters()">
              <i class="bx bx-filter me-1"></i>Filters
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="exportData()">
              <i class="bx bx-export me-1"></i>Export
            </button>
          </div>
        </div>
      </div>
      
      <!-- Advanced Filters -->
      <div class="card-body border-bottom bg-light d-none" id="filtersSection">
        <form method="GET" action="{{ route('petty-cash.hod.index') }}" id="filterForm">
          <input type="hidden" name="type" value="{{ $type ?? 'regular' }}">
          <input type="hidden" name="status" value="{{ $status ?? 'pending_hod' }}">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label">Search</label>
              <input type="text" name="search" class="form-control" placeholder="Voucher No, Employee, Purpose..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">Date From</label>
              <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">Date To</label>
              <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">Min Amount</label>
              <input type="number" name="amount_min" class="form-control" placeholder="0.00" step="0.01" value="{{ request('amount_min') }}">
            </div>
            <div class="col-md-2">
              <label class="form-label">Max Amount</label>
              <input type="number" name="amount_max" class="form-control" placeholder="0.00" step="0.01" value="{{ request('amount_max') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">
                <i class="bx bx-search"></i>
              </button>
            </div>
          </div>
          @if(request()->hasAny(['search', 'date_from', 'date_to', 'amount_min', 'amount_max']))
          <div class="mt-2">
            <a href="{{ route('petty-cash.hod.index', ['type' => $type ?? 'regular', 'status' => $status ?? 'pending_hod']) }}" class="btn btn-sm btn-outline-secondary">
              <i class="bx bx-x me-1"></i>Clear Filters
            </a>
          </div>
          @endif
        </form>
      </div>
      
      <div class="card-body">
        <!-- Advanced Tabs Navigation -->
        <ul class="nav nav-pills nav-fill mb-4" role="tablist" id="hodTabs">
          <!-- Regular Vouchers -->
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'pending_hod' ? 'active' : '' }}" 
               href="{{ route('petty-cash.hod.index', ['type' => 'regular', 'status' => 'pending_hod']) }}">
              <i class="bx bx-time-five me-1"></i>
              <span class="d-none d-md-inline">Pending My Action</span>
              <span class="d-md-none">Pending</span>
              @if($counts['pending_hod'] > 0)
                <span class="badge bg-danger ms-2 rounded-pill">{{ $counts['pending_hod'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'pending_ceo' ? 'active' : '' }}" 
               href="{{ route('petty-cash.hod.index', ['type' => 'regular', 'status' => 'pending_ceo']) }}">
              <i class="bx bx-user-check me-1"></i>
              <span class="d-none d-md-inline">Pending CEO</span>
              <span class="d-md-none">CEO</span>
              @if($counts['pending_ceo'] > 0)
                <span class="badge bg-info ms-2 rounded-pill">{{ $counts['pending_ceo'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'approved' ? 'active' : '' }}" 
               href="{{ route('petty-cash.hod.index', ['type' => 'regular', 'status' => 'approved']) }}">
              <i class="bx bx-check-circle me-1"></i>
              <span class="d-none d-md-inline">Approved</span>
              <span class="d-md-none">Approved</span>
              @if($counts['approved'] > 0)
                <span class="badge bg-success ms-2 rounded-pill">{{ $counts['approved'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'paid' ? 'active' : '' }}" 
               href="{{ route('petty-cash.hod.index', ['type' => 'regular', 'status' => 'paid']) }}">
              <i class="bx bx-money me-1"></i>
              <span class="d-none d-md-inline">Paid</span>
              <span class="d-md-none">Paid</span>
              @if($counts['paid'] > 0)
                <span class="badge bg-primary ms-2 rounded-pill">{{ $counts['paid'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'rejected' ? 'active' : '' }}" 
               href="{{ route('petty-cash.hod.index', ['type' => 'regular', 'status' => 'rejected']) }}">
              <i class="bx bx-x-circle me-1"></i>
              <span class="d-none d-md-inline">Rejected</span>
              <span class="d-md-none">Rejected</span>
              @if($counts['rejected'] > 0)
                <span class="badge bg-danger ms-2 rounded-pill">{{ $counts['rejected'] }}</span>
              @endif
            </a>
          </li>
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'regular' && $status === 'retired' ? 'active' : '' }}" 
               href="{{ route('petty-cash.hod.index', ['type' => 'regular', 'status' => 'retired']) }}">
              <i class="bx bx-archive me-1"></i>
              <span class="d-none d-md-inline">Retired</span>
              <span class="d-md-none">Retired</span>
              @if($counts['retired'] > 0)
                <span class="badge bg-secondary ms-2 rounded-pill">{{ $counts['retired'] }}</span>
              @endif
            </a>
          </li>
          
          <!-- Direct Vouchers Tab - Link to dedicated page -->
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ request()->routeIs('petty-cash.direct-vouchers.index') ? 'active' : '' }}" 
               href="{{ route('petty-cash.direct-vouchers.index', ['status' => 'pending_hod']) }}">
              <i class="bx bx-file-blank me-1"></i>
              <span class="d-none d-md-inline">Direct Vouchers</span>
              <span class="d-md-none">Direct</span>
              @php
                $directTotal = ($counts['direct_pending_hod'] ?? 0) + ($counts['direct_paid'] ?? 0) + ($counts['direct_rejected'] ?? 0);
              @endphp
              @if($directTotal > 0)
                <span class="badge bg-warning ms-2 rounded-pill">{{ $directTotal }}</span>
              @endif
            </a>
          </li>
          
          <!-- Invoices Tab -->
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'invoices' ? 'active' : '' }}" 
               href="{{ route('petty-cash.hod.index', ['type' => 'invoices', 'status' => 'pending_hod']) }}">
              <i class="bx bx-file-blank me-1"></i>
              <span class="d-none d-md-inline">Invoices</span>
              <span class="d-md-none">Invoices</span>
              @if(($counts['pending_invoices'] ?? 0) > 0)
                <span class="badge bg-warning ms-2 rounded-pill">{{ $counts['pending_invoices'] }}</span>
              @endif
            </a>
          </li>
          
          <!-- Bills Tab -->
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'bills' ? 'active' : '' }}" 
               href="{{ route('petty-cash.hod.index', ['type' => 'bills', 'status' => 'pending_hod']) }}">
              <i class="bx bx-receipt me-1"></i>
              <span class="d-none d-md-inline">Bills</span>
              <span class="d-md-none">Bills</span>
              @if(($counts['pending_bills'] ?? 0) > 0)
                <span class="badge bg-info ms-2 rounded-pill">{{ $counts['pending_bills'] }}</span>
              @endif
            </a>
          </li>
          
          <!-- Credit Memos Tab -->
          <li class="nav-item" role="presentation">
            <a class="nav-link {{ ($type ?? 'regular') === 'credit_memos' ? 'active' : '' }}" 
               href="{{ route('petty-cash.hod.index', ['type' => 'credit_memos', 'status' => 'pending_hod']) }}">
              <i class="bx bx-file-blank me-1"></i>
              <span class="d-none d-md-inline">Credit Memos</span>
              <span class="d-md-none">Credit Memos</span>
              @if(($counts['pending_credit_memos'] ?? 0) > 0)
                <span class="badge bg-warning ms-2 rounded-pill">{{ $counts['pending_credit_memos'] }}</span>
              @endif
            </a>
          </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
          
          <!-- Invoices Section -->
          @if(($type ?? 'regular') === 'invoices')
          <div class="tab-pane fade show active">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Invoice No</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingInvoices ?? [] as $invoice)
                  <tr>
                    <td><code class="text-primary">{{ $invoice->invoice_no }}</code></td>
                    <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                    <td>{{ $invoice->invoice_date->format('d M Y') }}</td>
                    <td>{{ $invoice->due_date->format('d M Y') }}</td>
                    <td class="text-end"><strong>TZS {{ number_format($invoice->total_amount, 2) }}</strong></td>
                    <td><span class="badge bg-warning">{{ $invoice->status }}</span></td>
                    <td class="text-center">
                      <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info" onclick="viewInvoice({{ $invoice->id }})" title="View">
                          <i class="bx bx-show"></i>
                        </button>
                        <button class="btn btn-sm btn-success" onclick="approveInvoice({{ $invoice->id }})" title="Approve">
                          <i class="bx bx-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="rejectInvoice({{ $invoice->id }})" title="Reject">
                          <i class="bx bx-x"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  @if($invoicePayments->where('invoice_id', $invoice->id)->count() > 0)
                  <tr class="table-info">
                    <td colspan="7" class="p-2">
                      <small><strong>Payments:</strong></small>
                      @foreach($invoicePayments->where('invoice_id', $invoice->id) as $payment)
                      <span class="badge bg-success ms-2">
                        {{ $payment->payment_no }} - TZS {{ number_format($payment->amount, 2) }} ({{ $payment->payment_date->format('d M Y') }})
                      </span>
                      @endforeach
                    </td>
                  </tr>
                  @endif
                  @empty
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <i class="bx bx-inbox fs-1 text-muted"></i>
                      <p class="text-muted mt-2 mb-0">No invoices pending approval</p>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          @endif
          
          <!-- Bills Section -->
          @if(($type ?? 'regular') === 'bills')
          <div class="tab-pane fade show active">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Bill No</th>
                    <th>Vendor</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingBills ?? [] as $bill)
                  <tr>
                    <td><code class="text-primary">{{ $bill->bill_no }}</code></td>
                    <td>{{ $bill->vendor->name ?? 'N/A' }}</td>
                    <td>{{ $bill->bill_date->format('d M Y') }}</td>
                    <td>{{ $bill->due_date->format('d M Y') }}</td>
                    <td class="text-end"><strong>TZS {{ number_format($bill->total_amount, 2) }}</strong></td>
                    <td><span class="badge bg-warning">{{ $bill->status }}</span></td>
                    <td class="text-center">
                      <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info" onclick="viewBill({{ $bill->id }})" title="View">
                          <i class="bx bx-show"></i>
                        </button>
                        <button class="btn btn-sm btn-success" onclick="approveBill({{ $bill->id }})" title="Approve">
                          <i class="bx bx-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="rejectBill({{ $bill->id }})" title="Reject">
                          <i class="bx bx-x"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  @if($billPayments->where('bill_id', $bill->id)->count() > 0)
                  <tr class="table-info">
                    <td colspan="7" class="p-2">
                      <small><strong>Payments:</strong></small>
                      @foreach($billPayments->where('bill_id', $bill->id) as $payment)
                      <span class="badge bg-success ms-2">
                        {{ $payment->payment_no }} - TZS {{ number_format($payment->amount, 2) }} ({{ $payment->payment_date->format('d M Y') }})
                      </span>
                      @endforeach
                    </td>
                  </tr>
                  @endif
                  @empty
                  <tr>
                    <td colspan="7" class="text-center py-4">
                      <i class="bx bx-inbox fs-1 text-muted"></i>
                      <p class="text-muted mt-2 mb-0">No bills pending approval</p>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          @endif
          
          <!-- Credit Memos Section -->
          @if(($type ?? 'regular') === 'credit_memos')
          <div class="tab-pane fade show active">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-light">
                  <tr>
                    <th>Memo No</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-end">Amount</th>
                    <th>Invoice</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($pendingCreditMemos ?? [] as $creditMemo)
                  <tr>
                    <td><code class="text-primary">{{ $creditMemo->memo_no }}</code></td>
                    <td>{{ $creditMemo->customer->name ?? 'N/A' }}</td>
                    <td>{{ $creditMemo->memo_date->format('d M Y') }}</td>
                    <td><span class="badge bg-info">{{ $creditMemo->type }}</span></td>
                    <td class="text-end"><strong>TZS {{ number_format($creditMemo->amount, 2) }}</strong></td>
                    <td>
                      @if($creditMemo->invoice)
                        <code class="text-info">{{ $creditMemo->invoice->invoice_no }}</code>
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td><span class="badge bg-warning">{{ $creditMemo->status }}</span></td>
                    <td class="text-center">
                      <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-info" onclick="viewCreditMemo({{ $creditMemo->id }})" title="View">
                          <i class="bx bx-show"></i>
                        </button>
                        <button class="btn btn-sm btn-success" onclick="approveCreditMemo({{ $creditMemo->id }})" title="Approve">
                          <i class="bx bx-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="rejectCreditMemo({{ $creditMemo->id }})" title="Reject">
                          <i class="bx bx-x"></i>
                        </button>
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="8" class="text-center py-4">
                      <i class="bx bx-inbox fs-1 text-muted"></i>
                      <p class="text-muted mt-2 mb-0">No credit memos pending approval</p>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
          @endif
          
          <!-- Petty Cash Section -->
          @if(($type ?? 'regular') !== 'invoices' && ($type ?? 'regular') !== 'bills' && ($type ?? 'regular') !== 'credit_memos')
          
          <!-- Page Header for Current Tab -->
          <div class="card-header bg-white border-bottom mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
              <div>
                <h5 class="mb-1">
                  @if(($type ?? 'regular') === 'regular' && $status === 'pending_hod')
                    <i class="bx bx-time-five text-danger me-2"></i>Pending My Action
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'pending_ceo')
                    <i class="bx bx-user-check text-info me-2"></i>Pending CEO Approval
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'approved')
                    <i class="bx bx-check-circle text-success me-2"></i>Approved for Payment
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'paid')
                    <i class="bx bx-money text-primary me-2"></i>Paid Vouchers
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'rejected')
                    <i class="bx bx-x-circle text-danger me-2"></i>Rejected Vouchers
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'retired')
                    <i class="bx bx-archive text-secondary me-2"></i>Retired Vouchers
                  @endif
                </h5>
                <p class="text-muted mb-0 small">
                  @if(($type ?? 'regular') === 'regular' && $status === 'pending_hod')
                    Regular vouchers requiring your approval. After approval, they will be forwarded to CEO for final approval.
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'pending_ceo')
                    Vouchers you've approved, now awaiting CEO's final approval before payment.
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'approved')
                    CEO-approved vouchers ready for accountant to process payment.
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'paid')
                    Vouchers that have been paid and are awaiting retirement submission.
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'rejected')
                    Vouchers you have rejected.
                  @elseif(($type ?? 'regular') === 'regular' && $status === 'retired')
                    Completed vouchers with retirement approved.
                  @endif
                </p>
              </div>
              <div class="mt-2 mt-md-0">
                <span class="badge bg-{{ ($type ?? 'regular') === 'regular' && $status === 'pending_hod' ? 'danger' : (($type ?? 'regular') === 'regular' && $status === 'pending_ceo' ? 'info' : (($type ?? 'regular') === 'regular' && $status === 'approved' ? 'success' : 'primary')) }} fs-6 px-3 py-2">
                  {{ $vouchers->total() }} {{ Str::plural('Voucher', $vouchers->total()) }}
                </span>
              </div>
            </div>
          </div>
          
          @if($vouchers->count() > 0)
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead class="table-light">
                  <tr>
                    <th class="d-none d-md-table-cell">Voucher No</th>
                    <th>Employee</th>
                    <th class="d-none d-lg-table-cell">Department</th>
                    <th>Amount</th>
                    <th class="d-none d-xl-table-cell">Purpose</th>
                    <th>Status</th>
                    <th class="d-none d-md-table-cell">Submitted</th>
                    <th class="text-nowrap">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($vouchers as $voucher)
                  @php
                    $isDirect = $voucher->created_by === $voucher->accountant_id && $voucher->accountant_id !== null;
                  @endphp
                  <tr class="{{ $isDirect ? 'table-warning' : '' }}">
                    <td class="d-none d-md-table-cell">
                      <div class="d-flex align-items-center flex-wrap">
                        <span class="badge bg-primary mb-1">{{ $voucher->voucher_no }}</span>
                        @if($isDirect)
                          <span class="badge bg-warning text-dark ms-1 mb-1" title="Direct Voucher - HOD/Admin Approval">
                            <i class="bx bx-file-blank"></i> Direct
                          </span>
                        @endif
                      </div>
                    </td>
                    <td>
                      <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-light rounded-circle me-2 flex-shrink-0">
                          <span class="avatar-title {{ $isDirect ? 'text-warning' : 'text-primary' }}">{{ substr($voucher->creator->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                          <h6 class="mb-0 text-truncate" style="max-width: 150px;" title="{{ $voucher->creator->name }}">{{ $voucher->creator->name }}</h6>
                          <small class="text-muted d-block">{{ $voucher->creator->employee_id ?? 'N/A' }}</small>
                          <div class="d-md-none">
                            <span class="badge bg-primary">{{ $voucher->voucher_no }}</span>
                            @if($isDirect)
                              <span class="badge bg-warning text-dark ms-1">Direct</span>
                            @endif
                          </div>
                          @if($isDirect)
                            <small class="text-info d-block"><i class="bx bx-user-check"></i> Accountant</small>
                          @endif
                        </div>
                      </div>
                    </td>
                    <td class="d-none d-lg-table-cell">
                      <span class="badge bg-info">{{ $voucher->creator->primaryDepartment->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                      <strong class="text-success">TZS {{ number_format($voucher->amount ?? 0, 2) }}</strong>
                      <div class="d-lg-none mt-1">
                        <small class="text-muted">{{ $voucher->creator->primaryDepartment->name ?? 'N/A' }}</small>
                      </div>
                    </td>
                    <td class="d-none d-xl-table-cell">
                      <div class="text-truncate" style="max-width: 220px;" title="{{ $voucher->purpose }}">
                        {{ $voucher->purpose }}
                      </div>
                    </td>
                    <td>
                      <span class="badge bg-{{ $voucher->status_badge_class }}">{{ ucfirst(str_replace('_', ' ', $voucher->status)) }}</span>
                      @if($isDirect && $voucher->status === 'pending_hod')
                        <br><small class="text-warning d-block"><i class="bx bx-info-circle"></i> <strong>HOD/Admin</strong></small>
                        <small class="text-muted d-block">(No CEO)</small>
                      @endif
                    </td>
                    <td class="d-none d-md-table-cell">
                      <small class="text-muted">{{ $voucher->created_at->format('M d, Y') }}</small>
                      <br><small class="text-muted">{{ $voucher->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                      <div class="btn-group btn-group-sm flex-wrap" role="group">
                        <button type="button" class="btn btn-info" onclick="viewDetails({{ $voucher->id }})" title="View Details">
                          <i class="bx bx-show"></i>
                        </button>
                        <a class="btn btn-secondary" href="{{ route('petty-cash.pdf', $voucher->id) }}" target="_blank" title="View PDF">
                          <i class="bx bxs-file-pdf"></i>
                        </a>
                        <a class="btn btn-outline-primary d-none d-md-inline-flex" href="{{ route('petty-cash.show', $voucher->id) }}" title="Full Details">
                          <i class="bx bx-detail"></i>
                        </a>
                        @if($voucher->status === 'pending_hod')
                          <button type="button" class="btn btn-success" onclick="openHodApprove({{ $voucher->id }})" title="Approve">
                            <i class="bx bx-check"></i><span class="d-none d-sm-inline ms-1">Approve</span>
                          </button>
                          <button type="button" class="btn btn-danger" onclick="openHodReject({{ $voucher->id }})" title="Reject">
                            <i class="bx bx-x"></i>
                          </button>
                        @endif
                      </div>
                      <div class="d-md-none mt-2">
                        <small class="text-muted">{{ $voucher->created_at->format('M d, Y H:i') }}</small>
                      </div>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 px-3 py-2 border-top bg-light flex-wrap gap-2">
              <div class="text-muted">
                <small>Page <strong>{{ $vouchers->currentPage() }}</strong> of <strong>{{ $vouchers->lastPage() }}</strong></small>
              </div>
              <div>
                {{ $vouchers->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }}
              </div>
              <div class="text-muted">
                <small><strong>{{ $vouchers->total() }}</strong> total vouchers</small>
              </div>
            </div>
          @else
            <div class="text-center py-5">
              <div class="mb-3">
                <i class="bx bx-inbox text-muted" style="font-size: 4rem;"></i>
              </div>
              <h5 class="text-muted">No Requests Found</h5>
              <p class="text-muted">
                @if($status === 'pending_hod')
                  No requests are pending your action.
                @else
                  No requests found for this status.
                @endif
              </p>
            </div>
          @endif
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<!-- HOD Approve Modal -->
<div class="modal fade" id="hodApproveModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-success text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-check-circle me-2"></i>Approve Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="hodApproveForm" method="POST">
        @csrf
        <div class="modal-body" style="max-height: calc(90vh - 200px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
          <div id="hodApproveModalInfo" class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Regular Voucher Workflow:</strong> This request will be forwarded to <strong>CEO for final approval</strong> after your approval. The workflow is: <strong>Accountant → HOD → CEO → Payment</strong>
          </div>
          <div class="mb-3">
            <label for="hod_comments" class="form-label fw-bold">
              <i class="bx bx-comment me-1"></i>Comments (Optional)
            </label>
            <textarea class="form-control" id="hod_comments" name="comments" rows="4" placeholder="Add any approval comments..."></textarea>
            <small class="text-muted">
              <i class="bx bx-info-circle"></i> Any additional notes about this approval
            </small>
          </div>
        </div>
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-success btn-lg text-white">
            <i class="bx bx-check-circle me-1"></i>Approve & Forward
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
#hodApproveModal {
    z-index: 100000 !important;
    position: fixed !important;
}

#hodApproveModal.show {
    z-index: 100000 !important;
    display: block !important;
    position: fixed !important;
}

/* Ensure only one backdrop exists */
body.modal-open .modal-backdrop {
    z-index: 99999 !important;
}

body.modal-open .modal-backdrop:not(:last-of-type) {
    display: none !important;
}

/* Ensure modal dialog is on top */
#hodApproveModal .modal-dialog {
    z-index: 100001 !important;
    position: relative;
}

/* Ensure modal content is clickable */
#hodApproveModal .modal-content {
    position: relative;
    z-index: 1;
}

#hodApproveModal .modal-body * {
    pointer-events: auto !important;
    position: relative;
    z-index: 1;
}

#hodApproveModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#hodApproveModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#hodApproveModal .modal-header,
#hodApproveModal .modal-footer {
    flex-shrink: 0;
}

#hodApproveModal .form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
}

@media (max-width: 991.98px) {
    #hodApproveModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #hodApproveModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>

<!-- HOD Reject Modal -->
<div class="modal fade" id="hodRejectModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-danger text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-x-circle me-2"></i>Reject Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="hodRejectForm" method="POST">
        @csrf
        <div class="modal-body" style="max-height: calc(90vh - 200px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Warning:</strong> This action will reject the request and notify the creator.
          </div>
          <div class="mb-3">
            <label for="hod_reject_comments" class="form-label fw-bold">
              <i class="bx bx-comment-detail me-1"></i>Reason for Rejection <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="hod_reject_comments" name="comments" rows="4" required placeholder="Please provide a detailed reason for rejecting this request..."></textarea>
            <small class="text-muted">
              <i class="bx bx-info-circle"></i> This reason will be sent to the creator
            </small>
          </div>
        </div>
        <div class="modal-footer bg-light sticky-bottom">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
            <i class="bx bx-x me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-danger btn-lg text-white">
            <i class="bx bx-x-circle me-1"></i>Reject Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
#hodRejectModal {
    z-index: 100000 !important;
    position: fixed !important;
}

#hodRejectModal.show {
    z-index: 100000 !important;
    display: block !important;
    position: fixed !important;
}

/* Ensure only one backdrop exists */
body.modal-open .modal-backdrop {
    z-index: 99999 !important;
}

body.modal-open .modal-backdrop:not(:last-of-type) {
    display: none !important;
}

/* Ensure modal dialog is on top */
#hodRejectModal .modal-dialog {
    z-index: 100001 !important;
    position: relative;
}

/* Ensure modal content is clickable */
#hodRejectModal .modal-content {
    position: relative;
    z-index: 1;
}

#hodRejectModal .modal-body * {
    pointer-events: auto !important;
    position: relative;
    z-index: 1;
}

#hodRejectModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#hodRejectModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#hodRejectModal .modal-header,
#hodRejectModal .modal-footer {
    flex-shrink: 0;
}

#hodRejectModal .form-control:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25);
}

@media (max-width: 991.98px) {
    #hodRejectModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #hodRejectModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-header bg-primary text-white sticky-top">
        <h5 class="modal-title text-white">
          <i class="bx bx-detail me-2"></i>Request Details
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="detailsContent" style="max-height: calc(90vh - 150px); overflow-y: auto; overflow-x: hidden; padding: 1.5rem;">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading details...</p>
        </div>
      </div>
      <div class="modal-footer bg-light sticky-bottom">
        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
          <i class="bx bx-x me-1"></i>Close
        </button>
      </div>
    </div>
  </div>
</div>

<style>
#detailsModal {
    z-index: 100000 !important;
    position: fixed !important;
}

#detailsModal.show {
    z-index: 100000 !important;
    display: block !important;
    position: fixed !important;
}

/* Ensure only one backdrop exists */
body.modal-open .modal-backdrop {
    z-index: 99999 !important;
}

body.modal-open .modal-backdrop:not(:last-of-type) {
    display: none !important;
}

/* Ensure modal dialog is on top */
#detailsModal .modal-dialog {
    z-index: 100001 !important;
    position: relative;
}

/* Ensure modal content is clickable */
#detailsModal .modal-content {
    position: relative;
    z-index: 1;
}

#detailsModal .modal-body * {
    pointer-events: auto !important;
    position: relative;
    z-index: 1;
}

#detailsModal .modal-body button,
#detailsModal .modal-body a,
#detailsModal .modal-body input,
#detailsModal .modal-body select,
#detailsModal .modal-body textarea {
    pointer-events: auto !important;
    z-index: 10 !important;
    position: relative;
}

#detailsModal .modal-content {
    border-radius: 15px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

#detailsModal .modal-body {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
}

#detailsModal .modal-header,
#detailsModal .modal-footer {
    flex-shrink: 0;
}

@media (max-width: 991.98px) {
    #detailsModal .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    #detailsModal .modal-body {
        max-height: calc(85vh - 200px);
    }
}
</style>
@endsection

@push('scripts')
<script>
let currentVoucherId = null;

function viewDetails(voucherId) {
  currentVoucherId = voucherId;
  $('#detailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>');
  
  // Show modal with proper z-index and prevent stacking
  var modalElement = document.getElementById('detailsModal');
  if (modalElement) {
    // Remove any existing backdrops first
    $('.modal-backdrop').remove();
    
    // Move modal to end of body
    $('body').append($(modalElement));
    
    // Hide any other open modals temporarily
    $('.modal.show').not(modalElement).each(function() {
      $(this).css('z-index', 9999);
    });
    
    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
    
    // Multiple setTimeout checks to ensure z-index is maintained
    setTimeout(function() {
      $(modalElement).css({
        'z-index': '100000',
        'position': 'fixed',
        'display': 'block'
      }).addClass('show');
      
      // Ensure only one backdrop exists
      $('.modal-backdrop').not(':last').remove();
      
      // Set backdrop z-index
      if ($('.modal-backdrop').length > 0) {
        $('.modal-backdrop').last().css('z-index', 99999);
      } else {
        $('body').append('<div class="modal-backdrop fade show"></div>');
        $('.modal-backdrop').last().css('z-index', 99999);
      }
      
      // Force modal dialog to front
      $(modalElement).find('.modal-dialog').css({
        'z-index': '100001',
        'position': 'relative'
      });
      
      // Ensure interactive elements are clickable
      $(modalElement).find('.modal-body *').css({
        'pointer-events': 'auto',
        'position': 'relative',
        'z-index': '1'
      });
    }, 50);
    
    setTimeout(function() {
      $(modalElement).css('z-index', '100000');
      $(modalElement).find('.modal-dialog').css('z-index', '100001');
      if ($('.modal-backdrop').length > 0) {
        $('.modal-backdrop').last().css('z-index', 99999);
      }
    }, 300);
    
    setTimeout(function() {
      $(modalElement).css('z-index', '100000');
      $(modalElement).find('.modal-dialog').css('z-index', '100001');
    }, 500);
  }

  fetch(`/petty-cash/${voucherId}/details`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        $('#detailsContent').html(data.html);
      } else {
        $('#detailsContent').html('<div class="alert alert-danger"><i class="bx bx-error-circle me-2"></i>Failed to load details: ' + data.message + '</div>');
      }
    })
    .catch(err => {
      $('#detailsContent').html('<div class="alert alert-danger"><i class="bx bx-error-circle me-2"></i>Error loading details: ' + err.message + '</div>');
    });
}

function openHodApprove(voucherId) {
  currentVoucherId = voucherId;
  
  // Check if this is a direct voucher by fetching details
  fetch(`/petty-cash/${voucherId}/details`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Check if voucher is direct (created by accountant)
        const isDirect = data.html.includes('Direct Voucher') || data.html.includes('accountant_id');
        
        // Update modal info based on voucher type
        const infoHtml = isDirect 
          ? '<div class="alert alert-success"><i class="bx bx-check-circle me-2"></i><strong>Direct Voucher (Already Used):</strong> This voucher was already used in-office. Approval will mark it as <strong>Paid (Complete)</strong>. No CEO approval required. HOD/Admin can approve directly.</div>'
          : '<div class="alert alert-info"><i class="bx bx-info-circle me-2"></i><strong>Regular Voucher Workflow:</strong> This request will be forwarded to <strong>CEO for final approval</strong> after your approval. The workflow is: <strong>Accountant → HOD → CEO → Payment</strong></div>';
        $('#hodApproveModalInfo').html(infoHtml);
      }
    })
    .catch(() => {
      // Default message if fetch fails (assume regular voucher)
      $('#hodApproveModalInfo').html('<div class="alert alert-info"><i class="bx bx-info-circle me-2"></i><strong>Regular Voucher Workflow:</strong> This request will be forwarded to <strong>CEO for final approval</strong> after your approval. The workflow is: <strong>Accountant → HOD → CEO → Payment</strong></div>');
    });
  
  $('#hodApproveForm').attr('action', `/petty-cash/${voucherId}/hod-approve`);
  if (!$('#hodApproveForm input[name="action"]').length) {
    $('#hodApproveForm').append('<input type="hidden" name="action" value="approve">');
  } else {
    $('#hodApproveForm input[name="action"]').val('approve');
  }
  
  // Show modal with proper z-index and prevent stacking
  var modalElement = document.getElementById('hodApproveModal');
  if (modalElement) {
    // Remove any existing backdrops first
    $('.modal-backdrop').remove();
    
    // Move modal to end of body
    $('body').append($(modalElement));
    
    // Hide any other open modals temporarily
    $('.modal.show').not(modalElement).each(function() {
      $(this).css('z-index', 9999);
    });
    
    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
    
    // Multiple setTimeout checks to ensure z-index is maintained
    setTimeout(function() {
      $(modalElement).css({
        'z-index': '100000',
        'position': 'fixed',
        'display': 'block'
      }).addClass('show');
      
      // Ensure only one backdrop exists
      $('.modal-backdrop').not(':last').remove();
      
      // Set backdrop z-index
      if ($('.modal-backdrop').length > 0) {
        $('.modal-backdrop').last().css('z-index', 99999);
      } else {
        $('body').append('<div class="modal-backdrop fade show"></div>');
        $('.modal-backdrop').last().css('z-index', 99999);
      }
      
      // Force modal dialog to front
      $(modalElement).find('.modal-dialog').css({
        'z-index': '100001',
        'position': 'relative'
      });
    }, 50);
    
    setTimeout(function() {
      $(modalElement).css('z-index', '100000');
      $(modalElement).find('.modal-dialog').css('z-index', '100001');
      if ($('.modal-backdrop').length > 0) {
        $('.modal-backdrop').last().css('z-index', 99999);
      }
    }, 300);
    
    setTimeout(function() {
      $(modalElement).css('z-index', '100000');
      $(modalElement).find('.modal-dialog').css('z-index', '100001');
    }, 500);
  }
}

function openHodReject(voucherId) {
  currentVoucherId = voucherId;
  $('#hodRejectForm').attr('action', `/petty-cash/${voucherId}/hod-approve`);
  if (!$('#hodRejectForm input[name="action"]').length) {
    $('#hodRejectForm').append('<input type="hidden" name="action" value="reject">');
  }
  
  // Show modal with proper z-index and prevent stacking
  var modalElement = document.getElementById('hodRejectModal');
  if (modalElement) {
    // Remove any existing backdrops first
    $('.modal-backdrop').remove();
    
    // Move modal to end of body
    $('body').append($(modalElement));
    
    // Hide any other open modals temporarily
    $('.modal.show').not(modalElement).each(function() {
      $(this).css('z-index', 9999);
    });
    
    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    modal.show();
    
    // Multiple setTimeout checks to ensure z-index is maintained
    setTimeout(function() {
      $(modalElement).css({
        'z-index': '100000',
        'position': 'fixed',
        'display': 'block'
      }).addClass('show');
      
      // Ensure only one backdrop exists
      $('.modal-backdrop').not(':last').remove();
      
      // Set backdrop z-index
      if ($('.modal-backdrop').length > 0) {
        $('.modal-backdrop').last().css('z-index', 99999);
      } else {
        $('body').append('<div class="modal-backdrop fade show"></div>');
        $('.modal-backdrop').last().css('z-index', 99999);
      }
      
      // Force modal dialog to front
      $(modalElement).find('.modal-dialog').css({
        'z-index': '100001',
        'position': 'relative'
      });
    }, 50);
    
    setTimeout(function() {
      $(modalElement).css('z-index', '100000');
      $(modalElement).find('.modal-dialog').css('z-index', '100001');
      if ($('.modal-backdrop').length > 0) {
        $('.modal-backdrop').last().css('z-index', 99999);
      }
    }, 300);
    
    setTimeout(function() {
      $(modalElement).css('z-index', '100000');
      $(modalElement).find('.modal-dialog').css('z-index', '100001');
    }, 500);
  }
}

$('#hodApproveModal').on('hidden.bs.modal', function () {
  $('#hodApproveForm')[0].reset();
  // Clean up any extra backdrops
  $('.modal-backdrop').remove();
  // Remove body class if no other modals are open
  if ($('.modal.show').length === 0) {
    $('body').removeClass('modal-open');
    $('body').css('overflow', '');
    $('body').css('padding-right', '');
  }
});

$('#hodRejectModal').on('hidden.bs.modal', function () {
  $('#hodRejectForm')[0].reset();
  $('#hodRejectForm input[name="action"]').remove();
  // Clean up any extra backdrops
  $('.modal-backdrop').remove();
  // Remove body class if no other modals are open
  if ($('.modal.show').length === 0) {
    $('body').removeClass('modal-open');
    $('body').css('overflow', '');
    $('body').css('padding-right', '');
  }
});

$('#detailsModal').on('hidden.bs.modal', function () {
  // Clean up any extra backdrops
  $('.modal-backdrop').remove();
  // Remove body class if no other modals are open
  if ($('.modal.show').length === 0) {
    $('body').removeClass('modal-open');
    $('body').css('overflow', '');
    $('body').css('padding-right', '');
  }
});

// Toggle filters section
function toggleFilters() {
  $('#filtersSection').toggleClass('d-none');
  const btn = event.target.closest('button');
  if ($('#filtersSection').hasClass('d-none')) {
    $(btn).removeClass('btn-primary').addClass('btn-outline-secondary');
  } else {
    $(btn).removeClass('btn-outline-secondary').addClass('btn-primary');
  }
}

// Export data
function exportData() {
  const params = new URLSearchParams(window.location.search);
  params.set('export', 'excel');
  window.location.href = '{{ route("petty-cash.hod.index") }}?' + params.toString();
}

// Auto-submit filter form on Enter key
$('#filterForm input').on('keypress', function(e) {
  if (e.which === 13) {
    e.preventDefault();
    $('#filterForm').submit();
  }
});

// Invoice Approval Functions
async function viewInvoice(id) {
    try {
        const response = await fetch(`{{ route('modules.accounting.ar.invoices.show', ':id') }}`.replace(':id', id), {
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
                itemsHtml = invoice.items.map(item => {
                    const lineTotal = (item.line_total || ((item.quantity || 0) * (item.unit_price || 0)));
                    return `
                    <tr>
                        <td>${item.description || '-'}</td>
                        <td class="text-end">${(item.quantity || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                        <td class="text-end">TZS ${(item.unit_price || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                        <td class="text-end">${(item.tax_rate || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}%</td>
                        <td class="text-end">TZS ${lineTotal.toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</td>
                    </tr>
                `;
                }).join('');
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
                                        <h6 class="text-muted mb-2">Customer Information</h6>
                                        <p class="mb-1"><strong>Name:</strong> ${invoice.customer?.name || 'N/A'}</p>
                                        <p class="mb-1"><strong>Address:</strong> ${invoice.customer?.address || 'N/A'}</p>
                                        <p class="mb-1"><strong>Phone:</strong> ${invoice.customer?.phone || 'N/A'}</p>
                                        <p class="mb-1"><strong>Email:</strong> ${invoice.customer?.email || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-2">Invoice Details</h6>
                                        <p class="mb-1"><strong>Invoice No:</strong> ${invoice.invoice_no || 'N/A'}</p>
                                        <p class="mb-1"><strong>Reference:</strong> ${invoice.reference_no || '-'}</p>
                                        <p class="mb-1"><strong>Invoice Date:</strong> ${invoice.invoice_date ? new Date(invoice.invoice_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'}) : 'N/A'}</p>
                                        <p class="mb-1"><strong>Due Date:</strong> ${invoice.due_date ? new Date(invoice.due_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'}) : 'N/A'}</p>
                                        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-warning">${invoice.status || 'N/A'}</span></p>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="row text-center">
                                                    <div class="col-md-3">
                                                        <small class="text-muted d-block">Subtotal</small>
                                                        <strong>TZS ${(invoice.subtotal || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted d-block">Tax Amount</small>
                                                        <strong>TZS ${(invoice.tax_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted d-block">Total Amount</small>
                                                        <strong class="text-primary">TZS ${(invoice.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted d-block">Balance</small>
                                                        <strong class="text-danger">TZS ${(invoice.balance || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                                ${invoice.notes ? `<div class="mt-3"><strong>Notes:</strong><p class="text-muted">${invoice.notes}</p></div>` : ''}
                                ${invoice.terms ? `<div class="mt-2"><strong>Terms & Conditions:</strong><p class="text-muted">${invoice.terms}</p></div>` : ''}
                            </div>
                            <div class="modal-footer">
                                ${invoice.status === 'Pending for Approval' ? `
                                    <button type="button" class="btn btn-success" onclick="approveInvoice(${invoice.id}); bootstrap.Modal.getInstance(document.getElementById('viewInvoiceModal')).hide();">
                                        <i class="bx bx-check me-1"></i>Approve
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="rejectInvoice(${invoice.id}); bootstrap.Modal.getInstance(document.getElementById('viewInvoiceModal')).hide();">
                                        <i class="bx bx-x me-1"></i>Reject
                                    </button>
                                ` : ''}
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existingModal = document.getElementById('viewInvoiceModal');
            if (existingModal) existingModal.remove();
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modal = new bootstrap.Modal(document.getElementById('viewInvoiceModal'));
            modal.show();
            
            document.getElementById('viewInvoiceModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error loading invoice: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error loading invoice: ' + error.message);
        }
    }
}

async function approveInvoice(id) {
    if (!confirm('Are you sure you want to approve this invoice?')) return;
    
    try {
        const response = await fetch(`{{ route('modules.accounting.ar.invoices.approve', ':id') }}`.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', data.message || 'Invoice approved successfully', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Invoice approved successfully');
            }
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to approve invoice', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to approve invoice');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error: ' + error.message);
        }
    }
}

async function rejectInvoice(id) {
    const reason = prompt('Please provide a reason for rejection:');
    if (!reason) return;
    
    try {
        const response = await fetch(`{{ route('modules.accounting.ar.invoices.reject', ':id') }}`.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ rejection_reason: reason })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', data.message || 'Invoice rejected successfully', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Invoice rejected successfully');
            }
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to reject invoice', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to reject invoice');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error: ' + error.message);
        }
    }
}

// Credit Memo Approval Functions
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
                                        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-warning">${memo.status || 'N/A'}</span></p>
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
                                ${memo.status === 'Pending for Approval' ? `
                                    <button type="button" class="btn btn-success" onclick="approveCreditMemo(${memo.id}); bootstrap.Modal.getInstance(document.getElementById('viewCreditMemoModal')).hide();">
                                        <i class="bx bx-check me-1"></i>Approve
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="rejectCreditMemo(${memo.id}); bootstrap.Modal.getInstance(document.getElementById('viewCreditMemoModal')).hide();">
                                        <i class="bx bx-x me-1"></i>Reject
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

async function approveCreditMemo(id) {
    if (!confirm('Are you sure you want to approve this credit memo?')) return;
    
    try {
        const response = await fetch(`{{ route('modules.accounting.ar.credit-memos.approve', ':id') }}`.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', data.message || 'Credit memo approved successfully', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Credit memo approved successfully');
            }
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to approve credit memo', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to approve credit memo');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error: ' + error.message);
        }
    }
}

async function rejectCreditMemo(id) {
    const reason = prompt('Please provide a reason for rejection:');
    if (!reason) return;
    
    try {
        const response = await fetch(`{{ route('modules.accounting.ar.credit-memos.reject', ':id') }}`.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ rejection_reason: reason })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', data.message || 'Credit memo rejected successfully', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Credit memo rejected successfully');
            }
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to reject credit memo', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to reject credit memo');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error: ' + error.message);
        }
    }
}

// Bill Approval Functions
async function viewBill(id) {
    try {
        const response = await fetch(`{{ route('modules.accounting.ap.bills.show', ':id') }}`.replace(':id', id), {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.bill) {
            const bill = data.bill;
            let itemsHtml = '';
            if (bill.items && bill.items.length > 0) {
                itemsHtml = bill.items.map(item => `
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
                <div class="modal fade" id="viewBillModal" tabindex="-1">
                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">Bill Details - ${bill.bill_no || 'N/A'}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Vendor:</strong> ${bill.vendor?.name || 'N/A'}<br>
                                        <strong>Bill Date:</strong> ${bill.bill_date ? new Date(bill.bill_date).toLocaleDateString() : 'N/A'}<br>
                                        <strong>Due Date:</strong> ${bill.due_date ? new Date(bill.due_date).toLocaleDateString() : 'N/A'}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Status:</strong> <span class="badge bg-warning">${bill.status || 'N/A'}</span><br>
                                        <strong>Total Amount:</strong> TZS ${(bill.total_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}<br>
                                        <strong>Paid Amount:</strong> TZS ${(bill.paid_amount || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}<br>
                                        <strong>Balance:</strong> TZS ${(bill.balance || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})}
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
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existingModal = document.getElementById('viewBillModal');
            if (existingModal) existingModal.remove();
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modal = new bootstrap.Modal(document.getElementById('viewBillModal'));
            modal.show();
            
            document.getElementById('viewBillModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error loading bill: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error loading bill: ' + error.message);
        }
    }
}

async function approveBill(id) {
    if (!confirm('Are you sure you want to approve this bill?')) return;
    
    try {
        // Update bill status to Pending (approved)
        const response = await fetch(`{{ route('modules.accounting.ap.bills.update', ':id') }}`.replace(':id', id), {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: 'Pending' })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', 'Bill approved successfully', { duration: 5000, sound: true });
            } else {
                alert('Bill approved successfully');
            }
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to approve bill', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to approve bill');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error: ' + error.message);
        }
    }
}

async function rejectBill(id) {
    const reason = prompt('Please provide a reason for rejection:');
    if (!reason) return;
    
    try {
        // Update bill status to Cancelled (rejected)
        const response = await fetch(`{{ route('modules.accounting.ap.bills.update', ':id') }}`.replace(':id', id), {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status: 'Cancelled', notes: reason })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.success('Success', 'Bill rejected successfully', { duration: 5000, sound: true });
            } else {
                alert('Bill rejected successfully');
            }
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof window.AdvancedToast !== 'undefined') {
                window.AdvancedToast.error('Error', data.message || 'Failed to reject bill', { duration: 5000, sound: true });
            } else {
                alert(data.message || 'Failed to reject bill');
            }
        }
    } catch (error) {
        if (typeof window.AdvancedToast !== 'undefined') {
            window.AdvancedToast.error('Error', 'Error: ' + error.message, { duration: 5000, sound: true });
        } else {
            alert('Error: ' + error.message);
        }
    }
}
</script>

@push('styles')
<style>
.nav-pills .nav-link {
  border-radius: 0.5rem;
  transition: all 0.3s ease;
  font-weight: 500;
  padding: 0.75rem 1rem;
}

.nav-pills .nav-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.nav-pills .nav-link.active {
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.card {
  transition: all 0.3s ease;
}

.card:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.border-start-4 {
  border-left-width: 4px !important;
}

.table-hover tbody tr {
  transition: all 0.2s ease;
}

.table-hover tbody tr:hover {
  background-color: rgba(0,123,255,0.05);
  transform: scale(1.01);
}

#filtersSection {
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.avatar-initial {
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 768px) {
  .nav-pills {
    flex-wrap: wrap;
  }
  
  .nav-pills .nav-link {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
  }
  
  .card-header h5 {
    font-size: 1rem;
  }
}
</style>
@endpush
