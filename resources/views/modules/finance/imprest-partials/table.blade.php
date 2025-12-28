<div class="card">
  <div class="card-body">
    @if(isset($requests) && $requests->count() > 0)
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>Request #</th>
              <th>Accountant</th>
              <th>Purpose</th>
              <th>Amount</th>
              <th>Staff Assigned</th>
              <th>Status</th>
              <th>Progress</th>
              <th>Created</th>
              @if($showActions ?? false)
              <th>Actions</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach($requests as $req)
            <tr>
              <td><strong>{{ $req->request_no }}</strong></td>
              <td>{{ $req->accountant->name ?? '—' }}</td>
              <td>{{ Str::limit($req->purpose, 40) }}</td>
              <td><strong>TZS {{ number_format($req->amount, 2) }}</strong></td>
              <td>
                @if($req->assignments->count() > 0)
                  <span class="badge bg-info">{{ $req->assignments->count() }} staff</span>
                @else
                  <span class="badge bg-secondary">Not assigned</span>
                @endif
              </td>
              <td>
                @php
                  $badgeClass = match($req->status) {
                    'pending_hod' => 'warning',
                    'pending_ceo' => 'info',
                    'approved' => 'success',
                    'assigned' => 'primary',
                    'paid' => 'success',
                    'pending_receipt_verification' => 'warning',
                    'completed' => 'dark',
                    default => 'secondary'
                  };
                @endphp
                <span class="badge bg-{{ $badgeClass }}">{{ ucwords(str_replace('_', ' ', $req->status)) }}</span>
              </td>
              <td>
                @php
                  $progress = match($req->status) {
                    'pending_hod' => 20,
                    'pending_ceo' => 40,
                    'approved' => 60,
                    'assigned' => 70,
                    'paid' => 80,
                    'pending_receipt_verification' => 90,
                    'completed' => 100,
                    default => 0
                  };
                @endphp
                <div class="progress" style="height: 20px;">
                  <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%">{{ $progress }}%</div>
                </div>
              </td>
              <td>{{ $req->created_at?->format('M d, Y') }}</td>
              @if($showActions ?? false)
              <td>
                @php
                  $isHOD = auth()->user()->hasAnyRole(['HOD', 'System Admin']);
                  $isCEO = auth()->user()->hasAnyRole(['CEO', 'Director', 'System Admin']);
                  $isAccountant = auth()->user()->hasAnyRole(['Accountant', 'System Admin']);
                  
                  $canHodApprove = $isHOD && ($actionType ?? '') === 'hod' && $req->status === 'pending_hod';
                  $canCeoApprove = $isCEO && ($actionType ?? '') === 'ceo' && $req->status === 'pending_ceo';
                  $canAssignStaff = $isAccountant && ($actionType ?? '') === 'assign' && $req->status === 'approved';
                  $canProcessPayment = $isAccountant && ($actionType ?? '') === 'payment' && $req->status === 'assigned';
                  $canVerifyReceipts = $isAccountant && ($actionType ?? '') === 'verify' && $req->status === 'pending_receipt_verification';
                @endphp
                <div class="btn-group btn-group-sm" role="group">
                  <a href="{{ route('imprest.show', $req->id) }}" class="btn btn-outline-primary" title="View Details">
                    <i class="bx bx-show"></i>
                  </a>
                  
                  @if($canHodApprove)
                  <button class="btn btn-outline-success" onclick="hodApprove({{ $req->id }})" title="Approve">
                    <i class="bx bx-check"></i>
                  </button>
                  @endif

                  @if($canCeoApprove)
                  <button class="btn btn-outline-success" onclick="ceoApprove({{ $req->id }})" title="Final Approval">
                    <i class="bx bx-check-double"></i>
                  </button>
                  @endif

                  @if($canAssignStaff)
                  <button class="btn btn-outline-info" onclick="openAssignStaff({{ $req->id }})" title="Assign Staff">
                    <i class="bx bx-user-plus"></i>
                  </button>
                  @endif

                  @if($canProcessPayment)
                  <button class="btn btn-outline-success" onclick="openPayment({{ $req->id }})" title="Process Payment">
                    <i class="bx bx-money"></i>
                  </button>
                  @endif

                  @if($canVerifyReceipts)
                  <button class="btn btn-outline-warning" onclick="viewReceiptsForVerification({{ $req->id }})" title="Verify Receipts">
                    <i class="bx bx-check-circle"></i>
                  </button>
                  @endif

                  <a href="{{ route('imprest.pdf', $req->id) }}" class="btn btn-outline-danger" target="_blank" title="Download PDF">
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
      @if(method_exists($requests, 'links'))
      <div class="d-flex justify-content-center mt-3">
        {{ $requests->links() }}
      </div>
      @endif
    @else
      <div class="text-center py-5 text-muted">
        <i class="bx bx-inbox" style="font-size: 4rem;"></i>
        <h5>No requests found</h5>
        <p>There are no imprest requests in this category.</p>
      </div>
    @endif
  </div>
</div>

