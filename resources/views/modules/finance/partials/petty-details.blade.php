<div class="container-fluid">
  <div class="row mb-3">
    <div class="col-md-8">
      <h5 class="mb-1">Petty Cash Request #{{ $voucher->voucher_no }}</h5>
      <div class="text-muted">
        Submitted {{ $voucher->created_at->format('M d, Y H:i') }} by {{ $voucher->creator->name }}
      </div>
    </div>
    <div class="col-md-4 text-md-end mt-2 mt-md-0">
      <a href="{{ route('petty-cash.pdf', $voucher->id) }}" class="btn btn-sm btn-secondary" target="_blank">
        <i class="bx bxs-file-pdf"></i> Export PDF
      </a>
      <a href="{{ route('petty-cash.show', $voucher->id) }}" class="btn btn-sm btn-outline-primary">
        <i class="bx bx-detail"></i> Full Details
      </a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <div class="d-flex align-items-center mb-2">
            <span class="badge bg-{{ $voucher->status_badge_class }} me-2">{{ strtoupper(str_replace('_',' ', $voucher->status)) }}</span>
            <div class="flex-grow-1">
              <div class="progress" style="height:6px">
                <div class="progress-bar" role="progressbar" style="width: {{ $voucher->progress_percentage }}%"></div>
              </div>
            </div>
            <span class="ms-2 small">{{ (int)$voucher->progress_percentage }}%</span>
          </div>

          <dl class="row mb-0">
            <dt class="col-5">Date</dt>
            <dd class="col-7">{{ optional($voucher->date)->format('Y-m-d') }}</dd>
            <dt class="col-5">Payee</dt>
            <dd class="col-7">{{ $voucher->payee }}</dd>
            <dt class="col-5">Department</dt>
            <dd class="col-7">{{ $voucher->creator->primaryDepartment->name ?? 'N/A' }}</dd>
            <dt class="col-5">Amount</dt>
            <dd class="col-7"><strong>TZS {{ number_format($voucher->amount ?? 0, 2) }}</strong></dd>
            <dt class="col-5">Purpose</dt>
            <dd class="col-7">{{ $voucher->purpose }}</dd>
          </dl>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-3">Approval Chain</h6>
          <ul class="list-unstyled mb-0">
            <li class="mb-2">
              <i class="bx bx-user me-1"></i> Accountant:
              <strong>{{ $voucher->accountant->name ?? '—' }}</strong>
              @if($voucher->accountant_verified_at)
                <span class="text-muted">({{ $voucher->accountant_verified_at->format('M d, Y H:i') }})</span>
              @endif
            </li>
            <li class="mb-2">
              <i class="bx bx-user me-1"></i> HOD:
              <strong>{{ $voucher->hod->name ?? '—' }}</strong>
              @if($voucher->hod_approved_at)
                <span class="text-muted">({{ $voucher->hod_approved_at->format('M d, Y H:i') }})</span>
              @endif
            </li>
            <li class="mb-2">
              <i class="bx bx-user me-1"></i> CEO:
              <strong>{{ $voucher->ceo->name ?? '—' }}</strong>
              @if($voucher->ceo_approved_at)
                <span class="text-muted">({{ $voucher->ceo_approved_at->format('M d, Y H:i') }})</span>
              @endif
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <h6 class="mb-3">Expense Breakdown</h6>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Description</th>
              <th class="text-end">Qty</th>
              <th class="text-end">Unit Price</th>
              <th class="text-end">Total</th>
            </tr>
          </thead>
          <tbody>
            @forelse($voucher->lines as $line)
              <tr>
                <td>{{ $line->description }}</td>
                <td class="text-end">{{ number_format($line->qty, 2) }}</td>
                <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                <td class="text-end">{{ number_format($line->total, 2) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="text-center text-muted">No lines found</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>








