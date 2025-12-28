@extends('layouts.app')

@section('title', 'Sick Sheets')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="bx bx-first-aid"></i> Sick Sheets</h4>
            <p class="text-muted mb-0">Submit and track sick sheets with HR/HOD workflow</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSickSheetModal"><i class="bx bx-plus"></i> New Sick Sheet</button>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        @if($canSeeAllTabs ?? false)
        <!-- Multiple tabs for HR, HOD, Director, and Admin -->
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab-all" role="tab">
                All Requests
                <span class="badge bg-secondary ms-2">{{ ($allSheets ?? collect())->count() }}</span>
            </a>
        </li>
        @if($isHR || $isAdmin)
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-pending-hr" role="tab">
                Pending HR Review
                <span class="badge bg-warning ms-2">{{ $pendingHR ?? 0 }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-return-pending" role="tab">
                Return Pending
                <span class="badge bg-info ms-2">{{ $returnPending ?? 0 }}</span>
            </a>
        </li>
        @endif
        @if($isHOD || $isAdmin || ($isDirector ?? false))
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-pending-hod" role="tab">
                Pending HOD Approval
                <span class="badge bg-primary ms-2">{{ $pendingHOD ?? 0 }}</span>
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-my-action" role="tab">
                Awaiting My Action
                <span class="badge bg-danger ms-2">{{ ($awaitingMyAction ?? collect())->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#tab-my-requests" role="tab">
                My Requests
                <span class="badge bg-success ms-2">{{ ($myRequests ?? collect())->count() }}</span>
            </a>
        </li>
        @else
        <!-- Single tab for regular staff -->
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#tab-my-requests" role="tab">
                My Sick Sheets
                <span class="badge bg-success ms-2">{{ ($myRequests ?? collect())->count() }}</span>
            </a>
        </li>
        @endif
    </ul>

    <!-- Tab Content -->
    <div class="tab-content">
        @if($canSeeAllTabs ?? false)
        <!-- All Requests Tab -->
        <div class="tab-pane fade show active" id="tab-all" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Sheet #</th>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($allSheets ?? collect()) as $s)
                                <tr>
                                    <td><strong>{{ $s->sheet_number }}</strong></td>
                                    <td>{{ $s->employee->name ?? '—' }}</td>
                                    <td>{{ $s->start_date }} → {{ $s->end_date }}</td>
                                    <td>{{ $s->total_days }} day(s)</td>
                                    <td>
                                        <span class="badge bg-{{ $s->status==='approved'?'success':($s->status==='rejected'?'danger':($s->status==='pending_hr'?'warning':($s->status==='pending_hod'?'info':($s->status==='return_pending'?'info':($s->status==='completed'?'secondary':'secondary'))))) }}">
                                            {{ ucfirst(str_replace('_',' ', $s->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $s->created_at?->format('M d, Y') }}</td>
                                    <td>
                                        @if($s->medical_document_path)
                                        <a href="{{ asset('storage/' . $s->medical_document_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="bx bx-file"></i> View Doc
                                        </a>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No sick sheets found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending HR Review Tab -->
        @if($isHR || $isAdmin)
        <div class="tab-pane fade" id="tab-pending-hr" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Sheet #</th>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($allSheets ?? collect())->where('status', 'pending_hr') as $s)
                                <tr>
                                    <td><strong>{{ $s->sheet_number }}</strong></td>
                                    <td>{{ $s->employee->name ?? '—' }}</td>
                                    <td>{{ $s->start_date }} → {{ $s->end_date }}</td>
                                    <td>{{ $s->total_days }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="hrReview({{ $s->id }}, 'approve')">Approve</button>
                                        <button class="btn btn-sm btn-danger" onclick="hrReview({{ $s->id }}, 'reject')">Reject</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">No pending HR reviews.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Return Pending Tab -->
        <div class="tab-pane fade" id="tab-return-pending" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Sheet #</th>
                                    <th>Employee</th>
                                    <th>Return Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($allSheets ?? collect())->where('status', 'return_pending') as $s)
                                <tr>
                                    <td><strong>{{ $s->sheet_number }}</strong></td>
                                    <td>{{ $s->employee->name ?? '—' }}</td>
                                    <td>{{ Str::limit($s->return_remarks ?? '—', 50) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="hrVerify({{ $s->id }}, 'approve')">Complete</button>
                                        <button class="btn btn-sm btn-danger" onclick="hrVerify({{ $s->id }}, 'reject')">Reject</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted">No return confirmations pending.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
                                    </div>
                    @endif

        <!-- Pending HOD Approval Tab -->
        @if($isHOD || $isAdmin || ($isDirector ?? false))
        <div class="tab-pane fade" id="tab-pending-hod" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Sheet #</th>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Days</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($allSheets ?? collect())->where('status', 'pending_hod') as $s)
                                <tr>
                                    <td><strong>{{ $s->sheet_number }}</strong></td>
                                    <td>{{ $s->employee->name ?? '—' }}</td>
                                    <td>{{ $s->start_date }} → {{ $s->end_date }}</td>
                                    <td>{{ $s->total_days }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="hodApprove({{ $s->id }}, 'approve')">Approve</button>
                                        <button class="btn btn-sm btn-danger" onclick="hodApprove({{ $s->id }}, 'reject')">Reject</button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted">No pending HOD approvals.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Awaiting My Action Tab -->
        <div class="tab-pane fade" id="tab-my-action" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    @if(($awaitingMyAction ?? collect())->isEmpty())
                        <div class="text-center text-muted py-4">No items awaiting your action.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Sheet #</th>
                                        <th>Employee</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($awaitingMyAction as $s)
                                    <tr>
                                        <td><strong>{{ $s->sheet_number }}</strong></td>
                                        <td>{{ $s->employee->name ?? '—' }}</td>
                                        <td><span class="badge bg-warning">{{ ucfirst(str_replace('_',' ', $s->status)) }}</span></td>
                                        <td>
                                            @if($s->status === 'pending_hr' && ($isHR || $isAdmin))
                                            <button class="btn btn-sm btn-success" onclick="hrReview({{ $s->id }}, 'approve')">Review</button>
                                            @elseif($s->status === 'pending_hod' && ($isHOD || $isAdmin || ($isDirector ?? false)))
                                            <button class="btn btn-sm btn-success" onclick="hodApprove({{ $s->id }}, 'approve')">Approve</button>
                                            @elseif($s->status === 'return_pending' && ($isHR || $isAdmin))
                                            <button class="btn btn-sm btn-success" onclick="hrVerify({{ $s->id }}, 'approve')">Verify</button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- My Requests Tab - Visible to all users -->
        <div class="tab-pane fade {{ !($canSeeAllTabs ?? false) ? 'show active' : '' }}" id="tab-my-requests" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    @if(($myRequests ?? collect())->isEmpty())
                        <div class="text-center text-muted py-4">No requests found.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Sheet #</th>
                                        <th>Period</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myRequests as $s)
                                    <tr>
                                        <td><strong>{{ $s->sheet_number }}</strong></td>
                                        <td>{{ $s->start_date }} → {{ $s->end_date }}</td>
                                        <td>{{ $s->total_days }} day(s)</td>
                                        <td>
                                            <span class="badge bg-{{ $s->status==='approved'?'success':($s->status==='rejected'?'danger':($s->status==='pending_hr'?'warning':($s->status==='pending_hod'?'info':'secondary'))) }}">
                                                {{ ucfirst(str_replace('_',' ', $s->status)) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($s->status === 'approved')
                                            <button class="btn btn-sm btn-primary" onclick="confirmReturn({{ $s->id }})">Confirm Return</button>
                                            @endif
                                            @if($s->medical_document_path)
                                            <a href="{{ Storage::url($s->medical_document_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bx bx-file"></i> View Doc
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
<div class="modal fade" id="newSickSheetModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">New Sick Sheet</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="sickSheetForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Start Date *</label>
            <input type="date" name="start_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">End Date *</label>
            <input type="date" name="end_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Medical Document *</label>
            <input type="file" name="medical_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason *</label>
            <textarea name="reason" class="form-control" rows="3" required></textarea>
          </div>
          <div id="ssErrors" class="text-danger small"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('sickSheetForm');
  if(!form) return;
  
  const errorDiv = document.getElementById('ssErrors');
  const submitBtn = form.querySelector('button[type="submit"]');
  
  form.addEventListener('submit', async function(e){
    e.preventDefault();
    
    // Clear previous errors
    errorDiv.textContent = '';
    errorDiv.classList.add('d-none');
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';
    
    const fd = new FormData(form);
    
    try {
      const response = await fetch('{{ route('sick-sheets.store') }}', {
        method: 'POST',
        body: fd,
        headers: {
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      
      // Check if response is JSON
      const contentType = response.headers.get('content-type');
      if (!contentType || !contentType.includes('application/json')) {
        const text = await response.text();
        console.error('Non-JSON response received:', text.substring(0, 500));
        // Try to extract error message from HTML if possible
        const errorMatch = text.match(/<title>([^<]+)<\/title>/i) || text.match(/Error[:\s]+([^<\n]+)/i);
        const errorMsg = errorMatch ? errorMatch[1] : 'Server returned HTML instead of JSON. Check console for details.';
        throw new Error(errorMsg);
      }
      
      const data = await response.json();
      
      if (!response.ok || !data.success) {
        // Handle validation errors
        if (data.errors) {
          const errorMessages = Object.values(data.errors).flat().join(', ');
          throw new Error(errorMessages);
        }
        throw new Error(data.message || 'Failed to submit sick sheet');
      }
      
      // Success
      errorDiv.textContent = '';
      errorDiv.classList.add('d-none');
      
      // Show success message and reload
      if (window.Swal && typeof Swal.fire === 'function') {
        Swal.fire({
          icon: 'success',
          title: 'Success!',
          text: data.message || 'Sick sheet submitted successfully!',
          timer: 2000,
          showConfirmButton: false
        }).then(() => {
          location.reload();
        });
      } else {
        setTimeout(() => location.reload(), 500);
      }
      
    } catch(err) {
      console.error('Sick sheet submission error:', err);
      errorDiv.textContent = err.message || 'Failed to submit sick sheet. Please try again.';
      errorDiv.classList.remove('d-none');
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = 'Submit';
    }
  });
});

// HR Review function
function hrReview(id, decision) {
  if (window.Swal && typeof Swal.fire === 'function') {
    Swal.fire({
      title: decision === 'approve' ? 'Approve Sick Sheet?' : 'Reject Sick Sheet?',
      html: '<textarea id="review-comments" class="swal2-textarea" placeholder="Comments (required)" required></textarea>',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: decision === 'approve' ? 'Approve' : 'Reject',
      cancelButtonText: 'Cancel',
      preConfirm: () => {
        const comments = (document.getElementById('review-comments')||{}).value;
        if (!comments || !comments.trim()) {
          Swal.showValidationMessage('Comments are required');
          return false;
        }
        return { comments };
      }
    }).then(function(result){
      if (result.isConfirmed) {
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('decision', decision);
        fd.append('comments', result.value.comments);
        Swal.showLoading();
        fetch(`/sick-sheets/${id}/hr-review`, { method:'POST', body: fd })
          .then(async r => {
            const j = await r.json().catch(()=>({}));
            if(!r.ok || !j.success){ throw new Error(j.message||'Failed'); }
            return j;
          })
          .then(() => { Swal.fire('Success', 'Review completed.', 'success').then(()=>location.reload()); })
          .catch((err) => { Swal.fire('Error', err.message || 'Failed to process review.', 'error'); });
      }
    });
  } else {
    const comments = prompt('Enter comments:');
    if (!comments) return;
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('decision', decision);
    fd.append('comments', comments);
    fetch(`/sick-sheets/${id}/hr-review`, { method:'POST', body: fd })
      .then(() => location.reload())
      .catch(() => alert('Failed to process review'));
  }
}

// HOD Approval function
function hodApprove(id, decision) {
  if (window.Swal && typeof Swal.fire === 'function') {
    Swal.fire({
      title: decision === 'approve' ? 'Approve Sick Sheet?' : 'Reject Sick Sheet?',
      html: '<textarea id="hod-comments" class="swal2-textarea" placeholder="Comments (optional)"></textarea>',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: decision === 'approve' ? 'Approve' : 'Reject',
      cancelButtonText: 'Cancel',
      preConfirm: () => {
        const comments = (document.getElementById('hod-comments')||{}).value || '';
        return { comments };
      }
    }).then(function(result){
      if (result.isConfirmed) {
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('decision', decision);
        if (result.value.comments) fd.append('comments', result.value.comments);
        Swal.showLoading();
        fetch(`/sick-sheets/${id}/hod-approve`, { method:'POST', body: fd })
          .then(async r => {
            const j = await r.json().catch(()=>({}));
            if(!r.ok || !j.success){ throw new Error(j.message||'Failed'); }
            return j;
          })
          .then(() => { Swal.fire('Success', 'Decision submitted.', 'success').then(()=>location.reload()); })
          .catch((err) => { Swal.fire('Error', err.message || 'Failed to process.', 'error'); });
      }
    });
  } else {
    const comments = prompt('Enter comments (optional):');
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('decision', decision);
    if (comments) fd.append('comments', comments);
    fetch(`/sick-sheets/${id}/hod-approve`, { method:'POST', body: fd })
      .then(() => location.reload())
      .catch(() => alert('Failed to process'));
  }
}

// HR Final Verification function
function hrVerify(id, decision) {
  if (window.Swal && typeof Swal.fire === 'function') {
    Swal.fire({
      title: decision === 'approve' ? 'Complete Verification?' : 'Reject Return?',
      html: '<textarea id="verify-comments" class="swal2-textarea" placeholder="Comments (optional)"></textarea>',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: decision === 'approve' ? 'Complete' : 'Reject',
      cancelButtonText: 'Cancel',
      preConfirm: () => {
        const comments = (document.getElementById('verify-comments')||{}).value || '';
        return { comments };
      }
    }).then(function(result){
      if (result.isConfirmed) {
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        fd.append('decision', decision);
        if (result.value.comments) fd.append('comments', result.value.comments);
        Swal.showLoading();
        fetch(`/sick-sheets/${id}/hr-verification`, { method:'POST', body: fd })
          .then(async r => {
            const j = await r.json().catch(()=>({}));
            if(!r.ok || !j.success){ throw new Error(j.message||'Failed'); }
            return j;
          })
          .then(() => { Swal.fire('Success', 'Verification completed.', 'success').then(()=>location.reload()); })
          .catch((err) => { Swal.fire('Error', err.message || 'Failed to verify.', 'error'); });
      }
    });
  } else {
    const comments = prompt('Enter comments (optional):');
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    fd.append('decision', decision);
    if (comments) fd.append('comments', comments);
    fetch(`/sick-sheets/${id}/hr-verification`, { method:'POST', body: fd })
      .then(() => location.reload())
      .catch(() => alert('Failed to verify'));
  }
}

// Confirm Return function
function confirmReturn(id) {
  if (window.Swal && typeof Swal.fire === 'function') {
    Swal.fire({
      title: 'Confirm Return to Work?',
      html: '<textarea id="return-remarks" class="swal2-textarea" placeholder="Return remarks (optional)"></textarea>',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Confirm Return',
      cancelButtonText: 'Cancel',
      preConfirm: () => {
        const remarks = (document.getElementById('return-remarks')||{}).value || '';
        return { remarks };
      }
    }).then(function(result){
      if (result.isConfirmed) {
        const fd = new FormData();
        fd.append('_token', '{{ csrf_token() }}');
        if (result.value.remarks) fd.append('return_remarks', result.value.remarks);
        Swal.showLoading();
        fetch(`/sick-sheets/${id}/confirm-return`, { method:'POST', body: fd })
          .then(async r => {
            const j = await r.json().catch(()=>({}));
            if(!r.ok || !j.success){ throw new Error(j.message||'Failed'); }
            return j;
          })
          .then(() => { Swal.fire('Success', 'Return confirmed.', 'success').then(()=>location.reload()); })
          .catch((err) => { Swal.fire('Error', err.message || 'Failed to confirm return.', 'error'); });
      }
    });
  } else {
    const remarks = prompt('Enter return remarks (optional):');
    const fd = new FormData();
    fd.append('_token', '{{ csrf_token() }}');
    if (remarks) fd.append('return_remarks', remarks);
    fetch(`/sick-sheets/${id}/confirm-return`, { method:'POST', body: fd })
      .then(() => location.reload())
      .catch(() => alert('Failed to confirm return'));
  }
}
</script>
@endpush


