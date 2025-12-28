@extends('layouts.app')

@section('title', 'Imprest Receipt Verification - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0"><i class="bx bx-check-circle me-2"></i>Imprest Receipt Verification</h4>
        <p class="text-muted">Verify and approve submitted receipts for imprest requests</p>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')

<!-- Statistics Cards -->
<div class="row mb-4">
  <div class="col-md-4">
    <div class="card bg-warning text-white">
      <div class="card-body">
        <h3 class="mb-0">{{ $stats['pending_verification'] ?? 0 }}</h3>
        <p class="mb-0">Pending Verification</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-danger text-white">
      <div class="card-body">
        <h3 class="mb-0">{{ $stats['unverified_receipts'] ?? 0 }}</h3>
        <p class="mb-0">Unverified Receipts</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-success text-white">
      <div class="card-body">
        <h3 class="mb-0">{{ $stats['verified_today'] ?? 0 }}</h3>
        <p class="mb-0">Verified Today</p>
      </div>
    </div>
  </div>
</div>

<!-- Unverified Receipts Quick Access -->
@if($unverifiedReceipts->count() > 0)
<div class="row mb-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="bx bx-time me-2"></i>Unverified Receipts (Quick Access)</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Request No</th>
                <th>Staff Member</th>
                <th>Amount</th>
                <th>Submitted Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($unverifiedReceipts as $receipt)
              <tr>
                <td><strong>{{ $receipt->assignment->imprestRequest->request_no ?? 'N/A' }}</strong></td>
                <td>{{ $receipt->assignment->staff->name ?? 'N/A' }}</td>
                <td>{{ number_format($receipt->receipt_amount, 2) }}</td>
                <td>{{ $receipt->submitted_at ? $receipt->submitted_at->format('d M Y, H:i') : 'N/A' }}</td>
                <td>
                  <button class="btn btn-sm btn-primary" onclick="viewReceiptDetails({{ $receipt->id }})">
                    <i class="bx bx-detail"></i> View Details
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endif

<!-- Imprest Requests with Pending Verification -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="bx bx-list-ul me-2"></i>Imprest Requests Pending Verification</h5>
      </div>
      <div class="card-body">
        @if($imprestRequests->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Request No</th>
                <th>Purpose</th>
                <th>Amount</th>
                <th>Assigned Staff</th>
                <th>Receipts</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($imprestRequests as $request)
              <tr>
                <td><strong>{{ $request->request_no }}</strong></td>
                <td>{{ Str::limit($request->purpose, 50) }}</td>
                <td>{{ number_format($request->amount, 2) }}</td>
                <td>{{ $request->assignments->count() }} staff</td>
                <td>
                  <span class="badge bg-info">{{ $request->receipts->count() }} total</span>
                  <span class="badge bg-success">{{ $request->receipts->where('is_verified', true)->count() }} verified</span>
                  <span class="badge bg-warning">{{ $request->receipts->where('is_verified', false)->count() }} pending</span>
                </td>
                <td>
                  <span class="badge bg-warning">{{ ucwords(str_replace('_', ' ', $request->status)) }}</span>
                </td>
                <td>
                  <button class="btn btn-sm btn-primary" onclick="viewImprestDetails({{ $request->id }})">
                    <i class="bx bx-detail"></i> View & Verify
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="mt-3">
          {{ $imprestRequests->links() }}
        </div>
        @else
        <div class="alert alert-info">
          <i class="bx bx-info-circle me-2"></i>No imprest requests pending verification at this time.
        </div>
        @endif
      </div>
    </div>
  </div>
</div>

<!-- Receipt Details Modal -->
@include('modules.finance.imprest-partials.verify-modal')

<!-- Imprest Details Modal -->
<div class="modal fade" id="imprestDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bx bx-detail me-2"></i>Imprest Request Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="imprestDetailsContent">
        <div class="text-center py-4">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-2">Loading details...</p>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
function viewReceiptDetails(receiptId) {
  fetch(`{{ url('imprest/receipts') }}/${receiptId}/details`)
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Use the existing verify receipt modal
        openVerifyReceipt(receiptId);
      } else {
        showToast(data.message || 'Error loading receipt details', 'error');
      }
    })
    .catch(err => {
      showToast('Error loading receipt details: ' + err.message, 'error');
    });
}

function viewImprestDetails(imprestId) {
  $('#imprestDetailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading details...</p></div>');
  $('#imprestDetailsModal').modal('show');

  fetch(`{{ url('imprest') }}/${imprestId}`)
    .then(r => r.json())
    .then(data => {
      if (data.html) {
        $('#imprestDetailsContent').html(data.html);
      } else {
        $('#imprestDetailsContent').html('<div class="alert alert-danger">Failed to load details</div>');
      }
    })
    .catch(err => {
      $('#imprestDetailsContent').html('<div class="alert alert-danger">Error loading details: ' + err.message + '</div>');
    });
}

// Include verification functions from main imprest scripts
@include('modules.finance.imprest-partials.scripts')
</script>
@endpush

