@extends('layouts.app')

@section('title', 'Petty Cash Retirement')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h4 class="card-title mb-0"><i class="bx bx-file-blank me-2"></i>Petty Cash Retirement</h4>
            <p class="text-muted mb-0">Submit and review retirement receipts</p>
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
@php
    $isAccountant = auth()->user()->hasRole('Accountant') || auth()->user()->hasRole('System Admin');
@endphp

<!-- Success/Error Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
  <strong>Success!</strong> {{ session('success') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
  <strong>Error!</strong> {{ session('error') }}
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
  <!-- My Retirements (Staff) -->
  @if(!$isAccountant || $myRetirements->count() > 0)
  <div class="col-lg-12 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="bx bx-receipt"></i> My Paid Requests (Awaiting Retirement)</h5>
      </div>
      <div class="card-body">
        @if($myRetirements->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Voucher No.</th>
                <th>Date</th>
                <th>Purpose</th>
                <th>Amount (TZS)</th>
                <th>Paid Date</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($myRetirements as $voucher)
              <tr>
                <td><strong>{{ $voucher->voucher_no }}</strong></td>
                <td>{{ $voucher->date->format('M d, Y') }}</td>
                <td>{{ Str::limit($voucher->purpose, 50) }}</td>
                <td class="text-end">{{ number_format($voucher->amount, 2) }}</td>
                <td>{{ $voucher->paid_at ? $voucher->paid_at->format('M d, Y') : 'N/A' }}</td>
                <td>
                  <span class="badge bg-{{ $voucher->status == 'paid' ? 'success' : 'warning' }}">
                    {{ ucwords(str_replace('_', ' ', $voucher->status)) }}
                  </span>
                </td>
                <td>
                  <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#retirementModal{{ $voucher->id }}">
                    <i class="bx bx-upload"></i> Submit Retirement
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="alert alert-info">
          <i class="bx bx-info-circle"></i> No paid requests awaiting retirement.
        </div>
        @endif
      </div>
    </div>
  </div>
  @endif

  <!-- Pending Retirement Reviews (Accountant) -->
  @if($isAccountant)
  <div class="col-lg-12 mb-4">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="bx bx-check-circle"></i> Pending Retirement Reviews</h5>
      </div>
      <div class="card-body">
        @if($pendingRetirements->count() > 0)
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Voucher No.</th>
                <th>Date</th>
                <th>Payee</th>
                <th>Purpose</th>
                <th>Amount (TZS)</th>
                <th>Paid Date</th>
                <th>Retirement Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pendingRetirements as $voucher)
              <tr>
                <td><strong>{{ $voucher->voucher_no }}</strong></td>
                <td>{{ $voucher->date->format('M d, Y') }}</td>
                <td>{{ $voucher->payee }}</td>
                <td>{{ Str::limit($voucher->purpose, 50) }}</td>
                <td class="text-end">{{ number_format($voucher->amount, 2) }}</td>
                <td>{{ $voucher->paid_at ? $voucher->paid_at->format('M d, Y') : 'N/A' }}</td>
                <td>{{ $voucher->updated_at->format('M d, Y') }}</td>
                <td>
                  <a href="{{ route('petty-cash.show', $voucher) }}" class="btn btn-sm btn-info">
                    <i class="bx bx-show"></i> Review
                  </a>
                  <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveRetirementModal{{ $voucher->id }}">
                    <i class="bx bx-check"></i> Approve
                  </button>
                  <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectRetirementModal{{ $voucher->id }}">
                    <i class="bx bx-x"></i> Reject
                  </button>
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="alert alert-info">
          <i class="bx bx-info-circle"></i> No pending retirement reviews.
        </div>
        @endif
      </div>
    </div>
  </div>
  @endif
</div>

<!-- Retirement Submission Modals -->
@foreach($myRetirements as $voucher)
<div class="modal fade" id="retirementModal{{ $voucher->id }}" tabindex="-1" aria-labelledby="retirementModalLabel{{ $voucher->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form action="{{ route('petty-cash.retirement.submit', $voucher) }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="retirementModalLabel{{ $voucher->id }}">Submit Retirement - {{ $voucher->voucher_no }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Voucher Details</label>
            <div class="card bg-light">
              <div class="card-body">
                <p class="mb-1"><strong>Amount:</strong> TZS {{ number_format($voucher->amount, 2) }}</p>
                <p class="mb-1"><strong>Purpose:</strong> {{ $voucher->purpose }}</p>
                <p class="mb-0"><strong>Paid Date:</strong> {{ $voucher->paid_at ? $voucher->paid_at->format('M d, Y') : 'N/A' }}</p>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Retirement Receipts <span class="text-danger">*</span></label>
            <input type="file" name="retirement_receipts[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png" required>
            <small class="text-muted">Upload receipts for expenses. Multiple files allowed (PDF, JPG, PNG, max 10MB each)</small>
          </div>
          
          <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea name="retirement_comments" class="form-control" rows="3" placeholder="Optional comments about the retirement..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-upload"></i> Submit Retirement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach

<!-- Approval/Rejection Modals for Pending Retirements -->
@if($isAccountant)
@foreach($pendingRetirements as $voucher)
<!-- Approve Retirement Modal -->
<div class="modal fade" id="approveRetirementModal{{ $voucher->id }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="bx bx-check-circle me-2"></i>Approve Retirement - {{ $voucher->voucher_no }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="approveRetirementForm{{ $voucher->id }}" method="POST" action="{{ route('petty-cash.retirement.approve', $voucher) }}">
        @csrf
        <input type="hidden" name="action" value="approve">
        <div class="modal-body">
          <div class="alert alert-success">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Note:</strong> Approving this retirement will mark the voucher as completed.
          </div>
          <div class="mb-3">
            <label class="form-label">Voucher Details</label>
            <div class="card bg-light">
              <div class="card-body">
                <p class="mb-1"><strong>Amount:</strong> TZS {{ number_format($voucher->amount, 2) }}</p>
                <p class="mb-1"><strong>Purpose:</strong> {{ $voucher->purpose }}</p>
                <p class="mb-0"><strong>Payee:</strong> {{ $voucher->payee }}</p>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Comments (Optional)</label>
            <textarea name="comments" class="form-control" rows="3" placeholder="Add any comments about this retirement approval..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="approveRetirementBtn{{ $voucher->id }}">
            <i class="bx bx-check-circle me-1"></i>Approve Retirement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Retirement Modal -->
<div class="modal fade" id="rejectRetirementModal{{ $voucher->id }}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="bx bx-x-circle me-2"></i>Reject Retirement - {{ $voucher->voucher_no }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="rejectRetirementForm{{ $voucher->id }}" method="POST" action="{{ route('petty-cash.retirement.approve', $voucher) }}">
        @csrf
        <input type="hidden" name="action" value="reject">
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Warning:</strong> This action will reject the retirement and notify the staff member.
          </div>
          <div class="mb-3">
            <label class="form-label">Voucher Details</label>
            <div class="card bg-light">
              <div class="card-body">
                <p class="mb-1"><strong>Amount:</strong> TZS {{ number_format($voucher->amount, 2) }}</p>
                <p class="mb-1"><strong>Purpose:</strong> {{ $voucher->purpose }}</p>
                <p class="mb-0"><strong>Payee:</strong> {{ $voucher->payee }}</p>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea name="comments" class="form-control" rows="4" required placeholder="Please provide a detailed reason for rejecting this retirement..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="rejectRetirementBtn{{ $voucher->id }}">
            <i class="bx bx-x-circle me-1"></i>Reject Retirement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endforeach
@endif
@endsection

@push('scripts')
<script>
  // Auto-dismiss alerts after 5 seconds
  setTimeout(function() {
    document.querySelectorAll('.alert').forEach(function(alert) {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    });
  }, 5000);
</script>
@endpush



