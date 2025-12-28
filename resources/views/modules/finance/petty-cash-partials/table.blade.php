<div class="card">
  <div class="card-body">
    @if(isset($vouchers) && $vouchers->count() > 0)
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>Voucher #</th>
              <th>Creator</th>
              <th>Payee</th>
              <th>Purpose</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Progress</th>
              <th>Created</th>
              @if($showActions ?? false)
              <th>Actions</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach($vouchers as $voucher)
            <tr>
              <td><strong>{{ $voucher->voucher_no }}</strong></td>
              <td>{{ $voucher->creator->name ?? '—' }}</td>
              <td>{{ $voucher->payee }}</td>
              <td>{{ Str::limit($voucher->purpose, 40) }}</td>
              <td><strong>TZS {{ number_format($voucher->amount, 2) }}</strong></td>
              <td>
                @php
                  $badgeClass = match($voucher->status) {
                    'pending_accountant' => 'warning',
                    'pending_hod' => 'info',
                    'pending_ceo' => 'primary',
                    'approved_for_payment' => 'success',
                    'paid' => 'success',
                    'pending_retirement_review' => 'warning',
                    'retired' => 'dark',
                    'rejected' => 'danger',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">{{ ucwords(str_replace('_', ' ', $voucher->status)) }}</span>
              </td>
              <td>
                @php
                  $progress = match($voucher->status) {
                    'pending_accountant' => 15,
                    'pending_hod' => 30,
                    'pending_ceo' => 50,
                    'approved_for_payment' => 70,
                    'paid' => 85,
                    'pending_retirement_review' => 95,
                    'retired' => 100,
                    default => 0
                  };
                @endphp
                <div class="progress" style="height: 20px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">{{ $progress }}%</div>
                </div>
              </td>
              <td>{{ $voucher->created_at?->format('M d, Y') }}</td>
              @if($showActions ?? false)
              <td>
                <div class="btn-group btn-group-sm" role="group">
                  <button class="btn btn-outline-primary" onclick="viewDetails({{ $voucher->id }})" title="View Details">
                    <i class="bx bx-show"></i>
                  </button>
                  <a href="{{ route('petty-cash.show', $voucher->id) }}" class="btn btn-outline-info" title="Full Page" target="_blank">
                    <i class="bx bx-link-external"></i>
                  </a>
                  <a href="{{ route('petty-cash.pdf', $voucher->id) }}" class="btn btn-outline-danger" target="_blank" title="Download PDF">
                    <i class="bx bx-file-blank"></i>
                  </a>
                </div>
              </td>
              @endif
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      @if(method_exists($vouchers, 'links'))
      <div class="d-flex justify-content-center mt-3">
        {{ $vouchers->links() }}
      </div>
      @endif
    @else
      <div class="text-center py-5 text-muted">
        <i class="bx bx-inbox" style="font-size: 4rem;"></i>
        <h5>No vouchers found</h5>
        <p>There are no petty cash vouchers in this category.</p>
      </div>
    @endif
  </div>
</div>

