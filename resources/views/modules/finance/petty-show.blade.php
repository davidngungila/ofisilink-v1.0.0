@extends('layouts.app')

@section('title', 'Petty Cash Request Details - ' . $pettyCash->voucher_no)

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bx bx-home"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('petty-cash.index') }}">Petty Cash Management</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $pettyCash->voucher_no }}</li>
              </ol>
            </nav>
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
<!-- Actions Card - At Top for All Screen Sizes -->
<div class="card mb-4">
  <div class="card-header bg-primary text-white">
    <h5 class="card-title mb-0 text-white"><i class="bx bx-cog"></i> Actions</h5>
  </div>
  <div class="card-body">
    <div class="d-grid gap-2">
      
      <!-- Current Action Required (Based on Status and Role) -->
      @php
        $hasAction = false;
        $actionTitle = '';
        if ($isAccountant && $pettyCash->status == 'pending_accountant') {
          $hasAction = true;
          $actionTitle = 'Pending Accountant Verification';
        } elseif ($isHOD && $pettyCash->status == 'pending_hod') {
          $hasAction = true;
          $actionTitle = 'Pending HOD Approval';
        } elseif ($isCEO && $pettyCash->status == 'pending_ceo') {
          $hasAction = true;
          $actionTitle = 'Pending CEO Approval';
        } elseif ($isAccountant && $pettyCash->status == 'approved_for_payment') {
          $hasAction = true;
          $actionTitle = 'Ready for Payment Processing';
        } elseif ($isCreator && $pettyCash->status == 'paid') {
          $hasAction = true;
          $actionTitle = 'Awaiting Retirement Submission';
        } elseif ($isAccountant && $pettyCash->status == 'pending_retirement_review') {
          $hasAction = true;
          $actionTitle = 'Pending Retirement Review';
        }
      @endphp

      @if($hasAction)
      <div class="alert alert-info mb-3">
        <i class="bx bx-info-circle me-2"></i>
        <strong>Action Required:</strong> {{ $actionTitle }}
      </div>
      @endif

      <div class="row g-2">
        <!-- Accountant Verification Actions -->
        @if($isAccountant && $pettyCash->status == 'pending_accountant')
        <div class="col-12 col-sm-6 col-md-4">
          <small class="text-muted d-block mb-1"><i class="bx bx-user-circle"></i> Accountant Action</small>
          <button type="button" class="btn btn-info w-100" data-bs-toggle="modal" data-bs-target="#accountantVerifyModal">
            <i class="bx bx-check-circle"></i> Verify Request
          </button>
        </div>
        @endif

        <!-- HOD Approval Actions -->
        @if($isHOD && $pettyCash->status == 'pending_hod')
        <div class="col-12 col-sm-6 col-md-4">
          <small class="text-muted d-block mb-1"><i class="bx bx-user-circle"></i> HOD Action</small>
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#hodApproveModal">
              <i class="bx bx-check"></i> Approve Request
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#hodRejectModal">
              <i class="bx bx-x"></i> Reject Request
            </button>
          </div>
        </div>
        @endif

        <!-- CEO Approval Actions -->
        @if($isCEO && $pettyCash->status == 'pending_ceo')
        <div class="col-12 col-sm-6 col-md-4">
          <small class="text-muted d-block mb-1"><i class="bx bx-user-circle"></i> CEO Action</small>
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#ceoApproveModal">
              <i class="bx bx-check"></i> Approve Request
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#ceoRejectModal">
              <i class="bx bx-x"></i> Reject Request
            </button>
          </div>
        </div>
        @endif

        <!-- Payment Processing Actions -->
        @if($isAccountant && $pettyCash->status == 'approved_for_payment')
        <div class="col-12 col-sm-6 col-md-4">
          <small class="text-muted d-block mb-1"><i class="bx bx-user-circle"></i> Accountant Action</small>
          <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#markPaidModal">
            <i class="bx bx-money"></i> Mark as Paid
          </button>
        </div>
        @endif

        <!-- Retirement Submission Actions -->
        @if($isCreator && $pettyCash->status == 'paid')
        <div class="col-12 col-sm-6 col-md-4">
          <small class="text-muted d-block mb-1"><i class="bx bx-user-circle"></i> Your Action</small>
          <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#submitRetirementModal">
            <i class="bx bx-receipt"></i> Submit Retirement
          </button>
        </div>
        @endif

        <!-- Retirement Review Actions -->
        @if($isAccountant && $pettyCash->status == 'pending_retirement_review')
        <div class="col-12 col-sm-6 col-md-4">
          <small class="text-muted d-block mb-1"><i class="bx bx-user-circle"></i> Accountant Action</small>
          <div class="d-grid gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveRetirementModal">
              <i class="bx bx-check-circle"></i> Approve Retirement
            </button>
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectRetirementModal">
              <i class="bx bx-x-circle"></i> Reject Retirement
            </button>
          </div>
        </div>
        @endif
      </div>

      @if(!$hasAction)
      <div class="alert alert-secondary mb-0 mt-3">
        <i class="bx bx-info-circle me-2"></i>
        <small>No action required from you at this time.</small>
      </div>
      @endif

      <hr class="my-3">

      <!-- General Actions -->
      <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4">
          <small class="text-muted d-block mb-1"><i class="bx bx-menu"></i> General</small>
          <div class="d-grid gap-2">
            <a href="{{ route('petty-cash.index') }}" class="btn btn-outline-secondary">
              <i class="bx bx-arrow-back"></i> Back to Dashboard
            </a>
            
            <a href="{{ route('petty-cash.pdf', $pettyCash) }}" target="_blank" class="btn btn-outline-primary">
              <i class="bx bx-file-blank"></i> Generate PDF
            </a>
            
            <button onclick="window.print()" class="btn btn-outline-secondary">
              <i class="bx bx-printer"></i> Print Page
            </button>
          </div>
        </div>
      </div>

      <!-- Delete Action -->
      @if($pettyCash->canBeDeleted() && $isCreator)
      <hr class="my-3">
      <div class="row g-2">
        <div class="col-12 col-sm-6 col-md-4">
          <small class="text-muted d-block mb-1"><i class="bx bx-trash"></i> Danger Zone</small>
          <form action="{{ route('petty-cash.destroy', $pettyCash) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this request? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger w-100">
              <i class="bx bx-trash"></i> Delete Request
            </button>
          </form>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

<div class="row">
  <!-- Left Column: Main Details -->
  <div class="col-lg-8">
    <!-- Header Card with Status -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div>
          <h4 class="card-title mb-0">
            <i class="bx bx-receipt text-primary"></i> {{ $pettyCash->voucher_no }}
          </h4>
          <small class="text-muted">Request Details</small>
        </div>
        <div>
          <span class="badge bg-{{ $pettyCash->status_badge_class }} fs-6 px-3 py-2">
            {{ ucwords(str_replace('_', ' ', $pettyCash->status)) }}
          </span>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6">
            <div class="info-item mb-3">
              <label class="text-muted small mb-1">Request Date</label>
              <div class="fw-bold">
                <i class="bx bx-calendar"></i> {{ $pettyCash->date->format('l, F d, Y') }}
              </div>
            </div>
            <div class="info-item mb-3">
              <label class="text-muted small mb-1">Payee</label>
              <div class="fw-bold">
                <i class="bx bx-user"></i> {{ $pettyCash->payee }}
              </div>
            </div>
            <div class="info-item mb-3">
              <label class="text-muted small mb-1">Requested By</label>
              <div class="fw-bold">
                <i class="bx bx-user-circle"></i> {{ $pettyCash->creator->name }}
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="info-item mb-3">
              <label class="text-muted small mb-1">Total Amount</label>
              <div class="fw-bold text-primary fs-4">
                <i class="bx bx-money"></i> {{ number_format($pettyCash->amount, 2) }} TZS
              </div>
            </div>
            <div class="info-item mb-3">
              <label class="text-muted small mb-1">Created On</label>
              <div class="fw-bold">
                <i class="bx bx-time"></i> {{ $pettyCash->created_at->format('M d, Y h:i A') }}
              </div>
            </div>
            @if($pettyCash->paid_at)
            <div class="info-item mb-3">
              <label class="text-muted small mb-1">Paid On</label>
              <div class="fw-bold text-success">
                <i class="bx bx-check-circle"></i> {{ $pettyCash->paid_at->format('M d, Y h:i A') }}
              </div>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>

    <!-- Purpose Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="bx bx-file-blank"></i> Purpose & Description</h5>
      </div>
      <div class="card-body">
        <p class="mb-0">{{ $pettyCash->purpose }}</p>
      </div>
    </div>

    <!-- Expense Lines Card -->
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="bx bx-list-ul"></i> Expense Breakdown</h5>
        <span class="badge bg-info">{{ $pettyCash->lines->count() }} Item(s)</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover table-bordered">
            <thead class="table-light">
              <tr>
                <th width="50">#</th>
                <th>Description</th>
                <th class="text-end" width="120">Quantity</th>
                <th class="text-end" width="150">Unit Price (TZS)</th>
                <th class="text-end" width="150">Total (TZS)</th>
              </tr>
            </thead>
            <tbody>
              @foreach($pettyCash->lines as $index => $line)
              <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $line->description }}</td>
                <td class="text-end">{{ number_format($line->qty, 2) }}</td>
                <td class="text-end">{{ number_format($line->unit_price, 2) }}</td>
                <td class="text-end fw-bold">{{ number_format($line->total, 2) }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot class="table-secondary">
              <tr>
                <th colspan="4" class="text-end">Grand Total:</th>
                <th class="text-end fs-5 text-primary">{{ number_format($pettyCash->amount, 2) }} TZS</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- Documents Card -->
    @if(($pettyCash->attachments && count($pettyCash->attachments) > 0) || ($pettyCash->retirement_receipts && count($pettyCash->retirement_receipts) > 0))
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="bx bx-folder"></i> Documents & Attachments</h5>
      </div>
      <div class="card-body">
        @if($pettyCash->attachments && count($pettyCash->attachments) > 0)
        <div class="mb-4">
          <h6 class="mb-3"><i class="bx bx-paperclip"></i> Supporting Documents</h6>
          <div class="row g-2">
            @foreach($pettyCash->attachments as $attachment)
            <div class="col-md-3 col-sm-4 col-6">
              <div class="document-card p-2 border rounded text-center hover-shadow">
                <i class="bx bx-file fs-1 text-primary"></i>
                <div class="small mt-1">Document {{ $loop->iteration }}</div>
                <a href="{{ asset('storage/' . $attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 w-100">
                  <i class="bx bx-show"></i> View
                </a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif

        @if($pettyCash->retirement_receipts && count($pettyCash->retirement_receipts) > 0)
        <div>
          <h6 class="mb-3"><i class="bx bx-receipt"></i> Retirement Receipts</h6>
          <div class="row g-2">
            @foreach($pettyCash->retirement_receipts as $receipt)
            <div class="col-md-3 col-sm-4 col-6">
              <div class="document-card p-2 border rounded text-center hover-shadow">
                <i class="bx bx-receipt fs-1 text-success"></i>
                <div class="small mt-1">Receipt {{ $loop->iteration }}</div>
                <a href="{{ asset('storage/' . $receipt) }}" target="_blank" class="btn btn-sm btn-outline-success mt-2 w-100">
                  <i class="bx bx-show"></i> View
                </a>
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endif
      </div>
    </div>
    @endif

    <!-- Comments & Notes -->
    @if($pettyCash->accountant_comments || $pettyCash->hod_comments || $pettyCash->ceo_comments || $pettyCash->retirement_comments)
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="bx bx-message-dots"></i> Comments & Notes</h5>
      </div>
      <div class="card-body">
        @if($pettyCash->accountant_comments)
        <div class="comment-box mb-3 p-3 border-start border-3 border-info bg-light">
          <div class="d-flex justify-content-between mb-2">
            <strong><i class="bx bx-user-circle text-info"></i> Accountant</strong>
            @if($pettyCash->accountant_verified_at)
            <small class="text-muted">{{ $pettyCash->accountant_verified_at->format('M d, Y h:i A') }}</small>
            @endif
          </div>
          <p class="mb-0">{{ $pettyCash->accountant_comments }}</p>
        </div>
        @endif

        @if($pettyCash->hod_comments)
        <div class="comment-box mb-3 p-3 border-start border-3 border-primary bg-light">
          <div class="d-flex justify-content-between mb-2">
            <strong><i class="bx bx-user-circle text-primary"></i> HOD</strong>
            @if($pettyCash->hod_approved_at)
            <small class="text-muted">{{ $pettyCash->hod_approved_at->format('M d, Y h:i A') }}</small>
            @endif
          </div>
          <p class="mb-0">{{ $pettyCash->hod_comments }}</p>
        </div>
        @endif

        @if($pettyCash->ceo_comments)
        <div class="comment-box mb-3 p-3 border-start border-3 border-warning bg-light">
          <div class="d-flex justify-content-between mb-2">
            <strong><i class="bx bx-user-circle text-warning"></i> CEO</strong>
            @if($pettyCash->ceo_approved_at)
            <small class="text-muted">{{ $pettyCash->ceo_approved_at->format('M d, Y h:i A') }}</small>
            @endif
          </div>
          <p class="mb-0">{{ $pettyCash->ceo_comments }}</p>
        </div>
        @endif

        @if($pettyCash->retirement_comments)
        <div class="comment-box mb-3 p-3 border-start border-3 border-success bg-light">
          <div class="d-flex justify-content-between mb-2">
            <strong><i class="bx bx-check-circle text-success"></i> Retirement Notes</strong>
            @if($pettyCash->retired_at)
            <small class="text-muted">{{ $pettyCash->retired_at->format('M d, Y h:i A') }}</small>
            @endif
          </div>
          <p class="mb-0">{{ $pettyCash->retirement_comments }}</p>
        </div>
        @endif
      </div>
    </div>
    @endif
  </div>

  <!-- Right Column: Workflow & Actions -->
  <div class="col-lg-4">
    <!-- Workflow Timeline Card -->
    <div class="card mb-4 sticky-top" style="top: 20px;">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="bx bx-time-five"></i> Workflow Progress</h5>
      </div>
      <div class="card-body">
        <!-- Progress Bar -->
        <div class="mb-4">
          <div class="d-flex justify-content-between mb-2">
            <span class="small text-muted">Progress</span>
            <span class="small fw-bold">{{ $pettyCash->progress_percentage }}%</span>
          </div>
          <div class="progress" style="height: 25px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $pettyCash->progress_percentage == 100 ? 'success' : ($pettyCash->progress_percentage > 0 ? 'primary' : 'danger') }}" 
                 role="progressbar" style="width: {{ $pettyCash->progress_percentage }}%">
              {{ $pettyCash->progress_percentage }}%
            </div>
          </div>
        </div>

        <!-- Timeline -->
        <div class="timeline-advanced">
          <!-- Step 1: Request Submitted -->
          <div class="timeline-step {{ $pettyCash->status != 'pending_accountant' || $pettyCash->accountant_verified_at ? 'completed' : ($pettyCash->status == 'pending_accountant' ? 'active' : 'pending') }}">
            <div class="timeline-marker">
              <i class="bx bx-check"></i>
            </div>
            <div class="timeline-content">
              <h6>Request Submitted</h6>
              <small class="text-muted">
                <i class="bx bx-user"></i> {{ $pettyCash->creator->name }}<br>
                <i class="bx bx-time"></i> {{ $pettyCash->created_at->format('M d, Y h:i A') }}
              </small>
            </div>
          </div>

          <!-- Step 2: Accountant Verification -->
          <div class="timeline-step {{ $pettyCash->accountant_verified_at ? 'completed' : ($pettyCash->status == 'pending_accountant' ? 'active' : 'pending') }}">
            <div class="timeline-marker">
              <i class="bx {{ $pettyCash->accountant_verified_at ? 'bx-check' : 'bx-time' }}"></i>
            </div>
            <div class="timeline-content">
              <h6>Accountant Verification</h6>
              @if($pettyCash->accountant_verified_at)
              <small class="text-muted">
                <i class="bx bx-user"></i> {{ $pettyCash->accountant->name ?? 'N/A' }}<br>
                <i class="bx bx-time"></i> {{ $pettyCash->accountant_verified_at->format('M d, Y h:i A') }}
              </small>
              @else
              <small class="text-muted">Pending verification</small>
              @endif
            </div>
          </div>

          <!-- Step 3: HOD Approval -->
          <div class="timeline-step {{ $pettyCash->hod_approved_at ? 'completed' : (in_array($pettyCash->status, ['pending_hod', 'pending_ceo', 'approved_for_payment', 'paid', 'pending_retirement_review', 'retired']) ? 'active' : 'pending') }}">
            <div class="timeline-marker">
              <i class="bx {{ $pettyCash->hod_approved_at ? 'bx-check' : 'bx-time' }}"></i>
            </div>
            <div class="timeline-content">
              <h6>HOD Approval</h6>
              @if($pettyCash->hod_approved_at)
              <small class="text-muted">
                <i class="bx bx-user"></i> {{ $pettyCash->hod->name ?? 'N/A' }}<br>
                <i class="bx bx-time"></i> {{ $pettyCash->hod_approved_at->format('M d, Y h:i A') }}
              </small>
              @else
              <small class="text-muted">Pending approval</small>
              @endif
            </div>
          </div>

          <!-- Step 4: CEO Approval -->
          <div class="timeline-step {{ $pettyCash->ceo_approved_at ? 'completed' : (in_array($pettyCash->status, ['pending_ceo', 'approved_for_payment', 'paid', 'pending_retirement_review', 'retired']) ? 'active' : 'pending') }}">
            <div class="timeline-marker">
              <i class="bx {{ $pettyCash->ceo_approved_at ? 'bx-check' : 'bx-time' }}"></i>
            </div>
            <div class="timeline-content">
              <h6>CEO Approval</h6>
              @if($pettyCash->ceo_approved_at)
              <small class="text-muted">
                <i class="bx bx-user"></i> {{ $pettyCash->ceo->name ?? 'N/A' }}<br>
                <i class="bx bx-time"></i> {{ $pettyCash->ceo_approved_at->format('M d, Y h:i A') }}
              </small>
              @else
              <small class="text-muted">Pending approval</small>
              @endif
            </div>
          </div>

          <!-- Step 5: Payment -->
          <div class="timeline-step {{ $pettyCash->paid_at ? 'completed' : (in_array($pettyCash->status, ['approved_for_payment', 'paid', 'pending_retirement_review', 'retired']) ? 'active' : 'pending') }}">
            <div class="timeline-marker">
              <i class="bx {{ $pettyCash->paid_at ? 'bx-check' : 'bx-time' }}"></i>
            </div>
            <div class="timeline-content">
              <h6>Payment Processed</h6>
              @if($pettyCash->paid_at)
              <small class="text-muted">
                <i class="bx bx-user"></i> {{ $pettyCash->paidBy->name ?? 'N/A' }}<br>
                <i class="bx bx-time"></i> {{ $pettyCash->paid_at->format('M d, Y h:i A') }}
              </small>
              @else
              <small class="text-muted">Pending payment</small>
              @endif
            </div>
          </div>

          <!-- Step 6: Retirement -->
          <div class="timeline-step {{ $pettyCash->retired_at ? 'completed' : (in_array($pettyCash->status, ['pending_retirement_review', 'retired']) ? 'active' : 'pending') }}">
            <div class="timeline-marker">
              <i class="bx {{ $pettyCash->retired_at ? 'bx-check' : 'bx-time' }}"></i>
            </div>
            <div class="timeline-content">
              <h6>Retirement Completed</h6>
              @if($pettyCash->retired_at)
              <small class="text-muted">
                <i class="bx bx-time"></i> {{ $pettyCash->retired_at->format('M d, Y h:i A') }}
              </small>
              @else
              <small class="text-muted">Pending retirement</small>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Approvers Info Card -->
    <div class="card mb-4">
      <div class="card-header">
        <h5 class="card-title mb-0"><i class="bx bx-user-check"></i> Approval Chain</h5>
      </div>
      <div class="card-body">
        <div class="approver-list">
          @if($pettyCash->accountant)
          <div class="approver-item mb-3 p-2 bg-light rounded">
            <div class="d-flex align-items-center">
              <div class="approver-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bx bx-user"></i>
              </div>
              <div class="ms-3">
                <div class="fw-bold">{{ $pettyCash->accountant->name }}</div>
                <small class="text-muted">Accountant</small>
                @if($pettyCash->accountant_verified_at)
                <div class="small text-success">
                  <i class="bx bx-check"></i> Verified {{ $pettyCash->accountant_verified_at->diffForHumans() }}
                </div>
                @endif
              </div>
            </div>
          </div>
          @endif

          @if($pettyCash->hod)
          <div class="approver-item mb-3 p-2 bg-light rounded">
            <div class="d-flex align-items-center">
              <div class="approver-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bx bx-user"></i>
              </div>
              <div class="ms-3">
                <div class="fw-bold">{{ $pettyCash->hod->name }}</div>
                <small class="text-muted">Head of Department</small>
                @if($pettyCash->hod_approved_at)
                <div class="small text-success">
                  <i class="bx bx-check"></i> Approved {{ $pettyCash->hod_approved_at->diffForHumans() }}
                </div>
                @endif
              </div>
            </div>
          </div>
          @endif

          @if($pettyCash->ceo)
          <div class="approver-item mb-3 p-2 bg-light rounded">
            <div class="d-flex align-items-center">
              <div class="approver-icon bg-warning text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bx bx-user"></i>
              </div>
              <div class="ms-3">
                <div class="fw-bold">{{ $pettyCash->ceo->name }}</div>
                <small class="text-muted">CEO</small>
                @if($pettyCash->ceo_approved_at)
                <div class="small text-success">
                  <i class="bx bx-check"></i> Approved {{ $pettyCash->ceo_approved_at->diffForHumans() }}
                </div>
                @endif
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>
@endsection

@push('styles')
<style>
  .hover-shadow {
    transition: all 0.3s ease;
  }
  .hover-shadow:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
  }

  .document-card {
    min-height: 120px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .timeline-advanced {
    position: relative;
    padding-left: 40px;
  }

  .timeline-advanced::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
  }

  .timeline-step {
    position: relative;
    margin-bottom: 25px;
  }

  .timeline-step:last-child {
    margin-bottom: 0;
  }

  .timeline-marker {
    position: absolute;
    left: -25px;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e0e0e0;
    color: #999;
    font-size: 14px;
    z-index: 1;
    border: 3px solid #fff;
  }

  .timeline-step.completed .timeline-marker {
    background: #28a745;
    color: white;
    box-shadow: 0 0 0 4px rgba(40, 167, 69, 0.2);
  }

  .timeline-step.active .timeline-marker {
    background: #0d6efd;
    color: white;
    animation: pulse 2s infinite;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
  }

  .timeline-step.pending .timeline-marker {
    background: #e0e0e0;
    color: #999;
  }

  .timeline-content h6 {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
  }

  .timeline-content small {
    font-size: 12px;
    display: block;
    line-height: 1.6;
  }

  .comment-box {
    border-radius: 8px;
    transition: all 0.3s ease;
  }

  .comment-box:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }

  .info-item label {
    display: block;
    margin-bottom: 4px;
  }

  @keyframes pulse {
    0%, 100% {
      box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.2);
    }
    50% {
      box-shadow: 0 0 0 8px rgba(13, 110, 253, 0.1);
    }
  }

  @media print {
    .card-header, .btn, nav, .breadcrumb {
      display: none !important;
    }
    .card {
      border: 1px solid #ddd;
      page-break-inside: avoid;
    }
  }
</style>
@endpush

<!-- Accountant Verify Modal -->
@if($isAccountant && $pettyCash->status == 'pending_accountant')
<div class="modal fade" id="accountantVerifyModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title">
          <i class="bx bx-check-circle me-2"></i>Verify Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="accountantVerifyForm" method="POST" action="{{ route('petty-cash.accountant.verify', $pettyCash) }}">
        @csrf
        <input type="hidden" name="action" value="approve">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Note:</strong> You must select a GL Account and Cash Box to verify this request.
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label for="verify_gl_account_id" class="form-label">
                <i class="bx bx-book me-1"></i>GL Account <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="verify_gl_account_id" name="gl_account_id" required>
                <option value="" selected disabled>-- Select GL Account --</option>
                @foreach($glAccounts as $gl)
                  <option value="{{ $gl->id }}">{{ $gl->code }} — {{ $gl->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label for="verify_cash_box_id" class="form-label">
                <i class="bx bx-money me-1"></i>Cash Box <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="verify_cash_box_id" name="cash_box_id" required>
                <option value="" selected disabled>-- Select Cash Box --</option>
                @foreach($cashBoxes as $cb)
                  <option value="{{ $cb->id }}">{{ $cb->name }} ({{ $cb->currency ?? 'TZS' }})</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="mb-3 mt-3">
            <label for="verify_comments" class="form-label">
              <i class="bx bx-comment me-1"></i>Comments (Optional)
            </label>
            <textarea class="form-control" id="verify_comments" name="comments" rows="3" placeholder="Add any verification comments..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-info" id="verifySubmitBtn">
            <i class="bx bx-check me-1"></i>Verify & Forward
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

<!-- HOD Approve Modal -->
@if($isHOD && $pettyCash->status == 'pending_hod')
<div class="modal fade" id="hodApproveModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="bx bx-check-circle me-2"></i>Approve Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="hodApproveForm" method="POST" action="{{ route('petty-cash.hod.approve', $pettyCash) }}">
        @csrf
        <input type="hidden" name="action" value="approve">
        <div class="modal-body">
          <div id="hodApproveModalInfo" class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            @if($isDirectVoucher)
            <strong>Direct Voucher (Already Used):</strong> This voucher was already used in-office. Approval will mark it as <strong>Paid (Complete)</strong>. No CEO approval required.
            @else
            <strong>Regular Voucher Workflow:</strong> This request will be forwarded to <strong>CEO for final approval</strong> after your approval. The workflow is: <strong>Accountant → HOD → CEO → Payment</strong>
            @endif
          </div>
          <div class="mb-3">
            <label for="hod_comments" class="form-label">
              <i class="bx bx-comment me-1"></i>Comments (Optional)
            </label>
            <textarea class="form-control" id="hod_comments" name="comments" rows="4" placeholder="Add any approval comments..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="hodApproveSubmitBtn">
            <i class="bx bx-check-circle me-1"></i>
            @if($isDirectVoucher)
            Approve & Mark as Paid
            @else
            Approve & Forward to CEO
            @endif
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- HOD Reject Modal -->
<div class="modal fade" id="hodRejectModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="bx bx-x-circle me-2"></i>Reject Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="hodRejectForm" method="POST" action="{{ route('petty-cash.hod.approve', $pettyCash) }}">
        @csrf
        <input type="hidden" name="action" value="reject">
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Warning:</strong> This action will reject the request and notify the staff member.
          </div>
          <div class="mb-3">
            <label for="hod_reject_comments" class="form-label">
              <i class="bx bx-comment-detail me-1"></i>Reason for Rejection <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="hod_reject_comments" name="comments" rows="4" required placeholder="Please provide a detailed reason for rejecting this request..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="hodRejectSubmitBtn">
            <i class="bx bx-x-circle me-1"></i>Reject Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

<!-- CEO Approve Modal -->
@if($isCEO && $pettyCash->status == 'pending_ceo')
<div class="modal fade" id="ceoApproveModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title">
          <i class="bx bx-check-circle me-2"></i>Approve Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="ceoApproveForm" method="POST" action="{{ route('petty-cash.ceo.approve', $pettyCash) }}">
        @csrf
        <input type="hidden" name="action" value="approve">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Note:</strong> This request will be approved for payment processing.
          </div>
          <div class="mb-3">
            <label for="ceo_comments" class="form-label">
              <i class="bx bx-comment me-1"></i>Comments (Optional)
            </label>
            <textarea class="form-control" id="ceo_comments" name="comments" rows="4" placeholder="Add any approval comments..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning" id="ceoApproveSubmitBtn">
            <i class="bx bx-check-circle me-1"></i>Approve for Payment
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- CEO Reject Modal -->
<div class="modal fade" id="ceoRejectModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="bx bx-x-circle me-2"></i>Reject Request
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="ceoRejectForm" method="POST" action="{{ route('petty-cash.ceo.approve', $pettyCash) }}">
        @csrf
        <input type="hidden" name="action" value="reject">
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Warning:</strong> This action will reject the request and notify the staff member.
          </div>
          <div class="mb-3">
            <label for="ceo_reject_comments" class="form-label">
              <i class="bx bx-comment-detail me-1"></i>Reason for Rejection <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" id="ceo_reject_comments" name="comments" rows="4" required placeholder="Please provide a detailed reason for rejecting this request..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="ceoRejectSubmitBtn">
            <i class="bx bx-x-circle me-1"></i>Reject Request
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

<!-- Submit Retirement Modal -->
@if($isCreator && $pettyCash->status == 'paid')
<div class="modal fade" id="submitRetirementModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="bx bx-receipt me-2"></i>Submit Retirement - {{ $pettyCash->voucher_no }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="submitRetirementForm" method="POST" action="{{ route('petty-cash.retirement.submit', $pettyCash) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Voucher Details</label>
            <div class="card bg-light">
              <div class="card-body">
                <p class="mb-1"><strong>Amount:</strong> TZS {{ number_format($pettyCash->amount, 2) }}</p>
                <p class="mb-1"><strong>Purpose:</strong> {{ $pettyCash->purpose }}</p>
                <p class="mb-0"><strong>Paid Date:</strong> {{ $pettyCash->paid_at ? $pettyCash->paid_at->format('M d, Y') : 'N/A' }}</p>
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
          <button type="submit" class="btn btn-success" id="submitRetirementBtn">
            <i class="bx bx-upload me-1"></i>Submit Retirement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

<!-- Approve Retirement Modal -->
@if($isAccountant && $pettyCash->status == 'pending_retirement_review')
<div class="modal fade" id="approveRetirementModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="bx bx-check-circle me-2"></i>Approve Retirement
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="approveRetirementForm" method="POST" action="{{ route('petty-cash.retirement.approve', $pettyCash) }}">
        @csrf
        <input type="hidden" name="action" value="approve">
        <div class="modal-body">
          <div class="alert alert-success">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Note:</strong> Approving this retirement will mark the voucher as completed.
          </div>
          <div class="mb-3">
            <label class="form-label">Comments (Optional)</label>
            <textarea name="comments" class="form-control" rows="3" placeholder="Add any comments about this retirement approval..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="approveRetirementBtn">
            <i class="bx bx-check-circle me-1"></i>Approve Retirement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Retirement Modal -->
<div class="modal fade" id="rejectRetirementModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="bx bx-x-circle me-2"></i>Reject Retirement
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="rejectRetirementForm" method="POST" action="{{ route('petty-cash.retirement.approve', $pettyCash) }}">
        @csrf
        <input type="hidden" name="action" value="reject">
        <div class="modal-body">
          <div class="alert alert-warning">
            <i class="bx bx-error-circle me-2"></i>
            <strong>Warning:</strong> This action will reject the retirement and notify the staff member.
          </div>
          <div class="mb-3">
            <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea name="comments" class="form-control" rows="4" required placeholder="Please provide a detailed reason for rejecting this retirement..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger" id="rejectRetirementBtn">
            <i class="bx bx-x-circle me-1"></i>Reject Retirement
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

<!-- Mark as Paid Modal -->
@if($isAccountant && $pettyCash->status == 'approved_for_payment')
<div class="modal fade" id="markPaidModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="bx bx-money me-2"></i>Mark as Paid - {{ $pettyCash->voucher_no }}
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="markPaidForm" method="POST" action="{{ route('petty-cash.mark-paid', $pettyCash) }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Note:</strong> This will create General Ledger entries and update the cash box balance if applicable.
          </div>
          
          <div class="row g-3">
            <div class="col-md-6">
              <label for="payment_method" class="form-label">
                <i class="bx bx-credit-card me-1"></i>Payment Method <span class="text-danger">*</span>
              </label>
              <select class="form-select" id="payment_method" name="payment_method" required>
                <option value="">-- Select Payment Method --</option>
                <option value="cash">Cash</option>
                <option value="bank_transfer">Bank Transfer</option>
                <option value="mobile_money">Mobile Money</option>
                <option value="cheque">Cheque</option>
                <option value="other">Other</option>
              </select>
            </div>
            
            <div class="col-md-6">
              <label for="paid_amount" class="form-label">
                <i class="bx bx-money me-1"></i>Paid Amount (TZS) <span class="text-danger">*</span>
              </label>
              <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount" value="{{ $pettyCash->amount }}" required>
              <small class="text-muted">Voucher amount: {{ number_format($pettyCash->amount, 2) }} TZS</small>
            </div>
            
            <div class="col-md-6">
              <label for="payment_currency" class="form-label">
                <i class="bx bx-dollar me-1"></i>Currency
              </label>
              <input type="text" class="form-control" id="payment_currency" name="payment_currency" value="TZS" placeholder="TZS">
            </div>
            
            <div class="col-md-6" id="bankFields" style="display: none;">
              <label for="bank_name" class="form-label">
                <i class="bx bx-building me-1"></i>Bank Name
              </label>
              <input type="text" class="form-control" id="bank_name" name="bank_name" placeholder="Bank name">
            </div>
            
            <div class="col-md-6" id="accountFields" style="display: none;">
              <label for="account_number" class="form-label">
                <i class="bx bx-hash me-1"></i>Account Number
              </label>
              <input type="text" class="form-control" id="account_number" name="account_number" placeholder="Account number">
            </div>
            
            <div class="col-md-6">
              <label for="payment_reference" class="form-label">
                <i class="bx bx-barcode me-1"></i>Payment Reference
              </label>
              <input type="text" class="form-control" id="payment_reference" name="payment_reference" placeholder="Transaction reference, cheque number, etc.">
            </div>
            
            <div class="col-12">
              <label for="payment_notes" class="form-label">
                <i class="bx bx-note me-1"></i>Payment Notes
              </label>
              <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3" placeholder="Additional payment notes..."></textarea>
            </div>
            
            <div class="col-12">
              <label for="payment_attachment" class="form-label">
                <i class="bx bx-paperclip me-1"></i>Payment Attachment (Optional)
              </label>
              <input type="file" class="form-control" id="payment_attachment" name="payment_attachment" accept=".pdf,.jpg,.jpeg,.png">
              <small class="text-muted">Upload payment receipt or proof (PDF, JPG, PNG, max 10MB)</small>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success" id="markPaidSubmitBtn">
            <i class="bx bx-check-circle me-1"></i>Mark as Paid
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif

@push('scripts')
<script>
// Handle form submissions with AJAX for better UX
$(document).ready(function() {
    // Accountant Verify Form
    @if($isAccountant && $pettyCash->status == 'pending_accountant')
    $('#accountantVerifyForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#verifySubmitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Verifying...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', 'Request verified and forwarded to HOD!', { duration: 5000 });
                } else {
                    alert('Request verified successfully!');
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to verify request';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join(', ');
                }
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    @endif

    // HOD Approve Form
    @if($isHOD && $pettyCash->status == 'pending_hod')
    $('#hodApproveForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#hodApproveSubmitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Approving...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                @if($isDirectVoucher)
                const message = 'Direct voucher approved and marked as Paid!';
                @else
                const message = 'Request approved and forwarded to CEO!';
                @endif
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', message, { duration: 5000 });
                } else {
                    alert(message);
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to approve request';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#hodRejectForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#hodRejectSubmitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.warning('Request Rejected', 'The request has been rejected and the staff member has been notified.', { duration: 5000 });
                } else {
                    alert('Request rejected successfully!');
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to reject request';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    @endif

    // CEO Approve Form
    @if($isCEO && $pettyCash->status == 'pending_ceo')
    $('#ceoApproveForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#ceoApproveSubmitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Approving...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', 'Request approved for payment!', { duration: 5000 });
                } else {
                    alert('Request approved successfully!');
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to approve request';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#ceoRejectForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#ceoRejectSubmitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.warning('Request Rejected', 'The request has been rejected and the staff member has been notified.', { duration: 5000 });
                } else {
                    alert('Request rejected successfully!');
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to reject request';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    @endif

    // Submit Retirement Form
    @if($isCreator && $pettyCash->status == 'paid')
    $('#submitRetirementForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#submitRetirementBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting...');
        
        const formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', 'Retirement submitted successfully!', { duration: 5000 });
                } else {
                    alert('Retirement submitted successfully!');
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to submit retirement';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    @endif

    // Approve/Reject Retirement Forms
    @if($isAccountant && $pettyCash->status == 'pending_retirement_review')
    $('#approveRetirementForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#approveRetirementBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Approving...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', 'Retirement approved! Voucher marked as completed.', { duration: 5000 });
                } else {
                    alert('Retirement approved successfully!');
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to approve retirement';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    $('#rejectRetirementForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#rejectRetirementBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Rejecting...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.warning('Retirement Rejected', 'The retirement has been rejected and the staff member has been notified.', { duration: 5000 });
                } else {
                    alert('Retirement rejected successfully!');
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to reject retirement';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    @endif

    // Mark as Paid Form
    @if($isAccountant && $pettyCash->status == 'approved_for_payment')
    // Show/hide bank fields based on payment method
    $('#payment_method').on('change', function() {
        const method = $(this).val();
        if (method === 'bank_transfer' || method === 'cheque') {
            $('#bankFields, #accountFields').show();
        } else {
            $('#bankFields, #accountFields').hide();
        }
    });

    $('#markPaidForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = $('#markPaidSubmitBtn');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');
        
        const formData = new FormData(this);
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.success('Success', 'Payment recorded successfully!', { duration: 5000 });
                } else {
                    alert('Payment recorded successfully!');
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to record payment';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join(', ');
                }
                if(typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    @endif
});
</script>
@endpush
