@extends('layouts.app')

@section('title', 'Petty Cash - Pending Payments')

@section('content')
<div class="container-fluid">
	<div class="d-flex justify-content-between align-items-center mb-4">
		<div>
			<h4 class="fw-bold mb-0">
				<i class="bx bx-credit-card"></i> Petty Cash - Pending Payments
			</h4>
			<p class="text-muted mb-0">Vouchers approved for payment and awaiting processing</p>
		</div>
		<div>
			<button type="button" class="btn btn-info" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
				<i class="bx bx-filter"></i> Filters
			</button>
			<a href="{{ route('petty-cash.index') }}" class="btn btn-outline-primary">
				<i class="bx bx-arrow-back"></i> Back to Dashboard
			</a>
		</div>
	</div>

	@if(isset($stats) && $stats)
	<!-- Statistics Dashboard -->
	<div class="row mb-4">
		<div class="col-md-3">
			<div class="card border-primary">
				<div class="card-body text-center">
					<div class="display-6 fw-bold text-primary">{{ $stats->total_count ?? 0 }}</div>
					<div class="text-muted small">Total Pending</div>
					<div class="mt-2">
						<small class="text-muted">Today: {{ $stats->today_count ?? 0 }}</small>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card border-success">
				<div class="card-body text-center">
					<div class="display-6 fw-bold text-success">TZS {{ number_format($stats->total_amount ?? 0, 2) }}</div>
					<div class="text-muted small">Total Amount</div>
					<div class="mt-2">
						<small class="text-muted">Today: TZS {{ number_format($stats->today_amount ?? 0, 2) }}</small>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card border-info">
				<div class="card-body text-center">
					<div class="display-6 fw-bold text-info">TZS {{ number_format($stats->avg_amount ?? 0, 2) }}</div>
					<div class="text-muted small">Average Amount</div>
					<div class="mt-2">
						<small class="text-muted">Range: {{ number_format($stats->min_amount ?? 0, 2) }} - {{ number_format($stats->max_amount ?? 0, 2) }}</small>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-3">
			<div class="card border-warning">
				<div class="card-body text-center">
					<div class="display-6 fw-bold text-warning">{{ $vouchers->total() ?? 0 }}</div>
					<div class="text-muted small">Showing</div>
					<div class="mt-2">
						<small class="text-muted">Page {{ $vouchers->currentPage() }} of {{ $vouchers->lastPage() }}</small>
					</div>
				</div>
			</div>
		</div>
	</div>
	@endif

	@if(isset($departmentStats) && $departmentStats->count() > 0)
	<!-- Department Statistics -->
	<div class="card mb-4">
		<div class="card-header bg-light">
			<h6 class="mb-0"><i class="bx bx-bar-chart-alt-2"></i> Top Departments by Amount</h6>
		</div>
		<div class="card-body">
			<div class="row">
				@foreach($departmentStats as $dept)
				<div class="col-md-3 mb-2">
					<div class="d-flex justify-content-between align-items-center p-2 border rounded">
						<div>
							<strong>{{ $dept->department_name ?? 'N/A' }}</strong>
							<br><small class="text-muted">{{ $dept->count }} vouchers</small>
						</div>
						<div class="text-end">
							<strong class="text-success">TZS {{ number_format($dept->total_amount, 2) }}</strong>
						</div>
					</div>
				</div>
				@endforeach
			</div>
		</div>
	</div>
	@endif

	<!-- Filters Collapse -->
	<div class="collapse mb-4" id="filtersCollapse">
		<div class="card">
			<div class="card-body">
				<form method="GET" action="{{ route('petty-cash.payment.index') }}" id="filterForm">
					<div class="row g-3">
						<div class="col-md-3">
							<label class="form-label">Search</label>
							<input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Voucher No, Payee, Purpose...">
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
							<input type="number" step="0.01" name="amount_min" class="form-control" value="{{ request('amount_min') }}" placeholder="0.00">
						</div>
						<div class="col-md-2">
							<label class="form-label">Max Amount</label>
							<input type="number" step="0.01" name="amount_max" class="form-control" value="{{ request('amount_max') }}" placeholder="0.00">
						</div>
						@if(isset($departments) && $departments->count() > 0)
						<div class="col-md-3">
							<label class="form-label">Department</label>
							<select name="department_id" class="form-select">
								<option value="">All Departments</option>
								@foreach($departments as $dept)
								<option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
									{{ $dept->name }}
								</option>
								@endforeach
							</select>
						</div>
						@endif
						<div class="col-md-12">
							<button type="submit" class="btn btn-primary">
								<i class="bx bx-search"></i> Apply Filters
							</button>
							<a href="{{ route('petty-cash.payment.index') }}" class="btn btn-outline-secondary">
								<i class="bx bx-refresh"></i> Reset
							</a>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			@if(session('success'))
				<div class="alert alert-success alert-dismissible fade show">
					{{ session('success') }}
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			@endif
			@if(session('error'))
				<div class="alert alert-danger alert-dismissible fade show">
					{{ session('error') }}
					<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
				</div>
			@endif
			@if ($errors->any())
				<div class="alert alert-danger">
					<ul class="mb-0">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif
			@if($vouchers->count() === 0)
				<div class="text-center py-5 text-muted">
					<i class="bx bx-inbox bx-lg"></i>
					<p class="mt-2 mb-0">No vouchers pending payment.</p>
					@if(request()->hasAny(['search', 'date_from', 'date_to', 'amount_min', 'amount_max', 'department_id']))
					<p class="small mt-2">
						<a href="{{ route('petty-cash.payment.index') }}" class="text-primary">Clear filters to see all vouchers</a>
					</p>
					@endif
				</div>
			@else
				<div class="table-responsive">
					<table class="table table-striped table-hover align-middle">
						<thead class="table-light">
							<tr>
								<th width="50">#</th>
								<th>Voucher No.</th>
								<th>Requested By</th>
								<th>Department</th>
								<th>Payee</th>
								<th>Purpose</th>
								<th class="text-end">Total Amount</th>
								<th>Created</th>
								<th class="text-end" width="200">Actions</th>
							</tr>
						</thead>
						<tbody>
							@foreach($vouchers as $index => $voucher)
								<tr>
									<td>{{ $vouchers->firstItem() + $index }}</td>
									<td>
										<span class="badge bg-primary">{{ $voucher->voucher_no ?? ('VC-' . str_pad($voucher->id, 5, '0', STR_PAD_LEFT)) }}</span>
									</td>
									<td>
										<strong>{{ optional($voucher->creator)->name ?? '—' }}</strong>
										@if(optional($voucher->creator)->email)
										<br><small class="text-muted">{{ $voucher->creator->email }}</small>
										@endif
									</td>
									<td>
										@if(optional($voucher->creator)->primaryDepartment)
										<span class="badge bg-info">{{ $voucher->creator->primaryDepartment->name }}</span>
										@else
										<span class="text-muted">—</span>
										@endif
									</td>
									<td>
										<small>{{ Str::limit($voucher->payee ?? '—', 30) }}</small>
									</td>
									<td>
										<small>{{ Str::limit($voucher->purpose ?? '—', 40) }}</small>
									</td>
									<td class="text-end">
										<strong class="text-success">TZS {{ number_format($voucher->amount ?? 0, 2) }}</strong>
									</td>
									<td>
										<div>{{ $voucher->created_at?->format('M d, Y') }}</div>
										<small class="text-muted">{{ $voucher->created_at?->format('H:i') }}</small>
									</td>
									<td class="text-end">
										<div class="btn-group btn-group-sm" role="group">
											<a href="{{ route('petty-cash.show', $voucher->id) }}" class="btn btn-outline-primary" title="View Details">
												<i class="bx bx-show"></i>
											</a>
											<button type="button" class="btn btn-success btn-open-pay" 
												data-voucher='@json(["id"=>$voucher->id, "voucher_no"=>$voucher->voucher_no, "amount"=>$voucher->amount])'
												title="Record Payment">
												<i class="bx bx-money"></i> Pay
											</button>
										</div>
									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
				<div class="mt-3 d-flex justify-content-between align-items-center">
					<div>
						<small class="text-muted">
							Showing {{ $vouchers->firstItem() }} to {{ $vouchers->lastItem() }} of {{ $vouchers->total() }} vouchers
						</small>
					</div>
					<div>
						{{ $vouchers->links() }}
					</div>
				</div>
			@endif
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const payModalEl = document.getElementById('paymentModal');
    if (!payModalEl) return;
    const payModal = new bootstrap.Modal(payModalEl);
    let currentId = null;

    document.querySelectorAll('.btn-open-pay').forEach(btn => {
        btn.addEventListener('click', function(){
            const vAttr = this.getAttribute('data-voucher');
            let v = {};
            try { v = JSON.parse(vAttr); } catch(e) { v = {}; }
            currentId = v.id;
            const noEl = document.getElementById('payVoucherNo');
            const amtEl = document.getElementById('paid_amount');
            if (noEl) noEl.textContent = v.voucher_no || ('PCV-' + (currentId ?? ''));
            if (amtEl) amtEl.value = (v.amount ?? '').toString();
            // Reset errors
            var err = document.getElementById('paymentError');
            if (err) err.textContent = '';
            // Reset method-dependent fields
            const methodEl = document.querySelector('select[name="payment_method"]');
            if (methodEl) { methodEl.value = methodEl.value || 'cash'; toggleMethodFields(methodEl.value); }
            // Ensure form action has ID substituted before submit
            const formEl = document.getElementById('paymentForm');
            if (formEl && currentId) {
                const base = formEl.getAttribute('action') || '';
                if (base.includes(':id')) {
                    formEl.setAttribute('action', base.replace(':id', currentId));
                }
            }
            payModal.show();
        });
    });

    const form = document.getElementById('paymentForm');
    if (form) {
        form.addEventListener('submit', function(e){
            if(!currentId){ e.preventDefault(); return false; }
            const url = this.getAttribute('action');
            if (url && url.includes(':id')) {
                this.setAttribute('action', url.replace(':id', currentId));
            }
        });
        // Toggle required fields based on method
        const methodEl = document.querySelector('select[name="payment_method"]');
        if (methodEl) {
            methodEl.addEventListener('change', function(){ toggleMethodFields(this.value); });
        }
    }

    function toggleMethodFields(method){
        const bankGroup = document.getElementById('bankFields');
        const accountInput = document.querySelector('input[name="account_number"]');
        const bankNameInput = document.querySelector('input[name="bank_name"]');
        const refInput = document.querySelector('input[name="payment_reference"]');
        // Bank transfer and cheque typically need bank/account/ref; mobile money needs ref
        const isBank = (method === 'bank_transfer' || method === 'cheque');
        const isMobile = (method === 'mobile_money');

        if (bankGroup) bankGroup.style.display = isBank ? '' : 'none';
        if (bankNameInput) bankNameInput.required = isBank;
        if (accountInput) accountInput.required = isBank;
        if (refInput) refInput.required = (isBank || isMobile);
    }
});
</script>
@endpush

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title text-white">
            <i class="bx bx-money"></i> Record Payment for <span id="payVoucherNo"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="paymentForm" method="POST" action="{{ route('petty-cash.payment.mark-paid', ':id') }}" enctype="multipart/form-data">
          @csrf
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Payment Method <span class="text-danger">*</span></label>
              <select name="payment_method" class="form-select" required>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="mobile_money">Mobile Money</option>
                <option value="cheque">Cheque</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Paid Amount <span class="text-danger">*</span></label>
              <input type="number" step="0.01" name="paid_amount" id="paid_amount" class="form-control" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">Currency</label>
              <input type="text" name="payment_currency" class="form-control" placeholder="TZS" value="TZS">
            </div>
            <div class="col-12" id="bankFields" style="display: none;">
              <div class="row g-3">
                <div class="col-md-4">
                  <label class="form-label">Bank Name</label>
                  <input type="text" name="bank_name" class="form-control" placeholder="e.g., CRDB">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Account Number</label>
                  <input type="text" name="account_number" class="form-control" placeholder="e.g., 0123456789">
                </div>
                <div class="col-md-4">
                  <label class="form-label">Payment Reference</label>
                  <input type="text" name="payment_reference" class="form-control" placeholder="Txn/Ref No">
                </div>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Payment Notes</label>
              <textarea name="payment_notes" class="form-control" rows="2" placeholder="Optional notes"></textarea>
            </div>
            <div class="col-12">
              <label class="form-label">Attachment (Slip/Proof)</label>
              <input type="file" name="payment_attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
              <small class="text-muted">Upload payment slip or proof of payment (PDF, JPG, PNG - Max 10MB)</small>
            </div>
            <div class="col-12 text-danger small" id="paymentError"></div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="paymentForm" class="btn btn-success">
            <i class="bx bx-check"></i> Record Payment
        </button>
      </div>
    </div>
  </div>
</div>
