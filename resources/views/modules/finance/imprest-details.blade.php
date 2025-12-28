<div class="imprest-details-container">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1">
                                <i class="fa fa-file-invoice-dollar text-primary"></i> 
                                {{ $imprestRequest->request_no }}
                            </h3>
                            <p class="text-muted mb-0">
                                <i class="fa fa-user"></i> Created by {{ $imprestRequest->accountant->name ?? ($imprestRequest->accountant_id ? 'User #' . $imprestRequest->accountant_id : 'N/A') }}
                                <span class="mx-2">|</span>
                                <i class="fa fa-calendar"></i> {{ $imprestRequest->created_at ? $imprestRequest->created_at->format('d M Y, H:i') : 'N/A' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="badge badge-lg px-4 py-2 
                                @if($imprestRequest->status == 'completed') btn-success
                                @elseif($imprestRequest->status == 'paid') btn-primary
                                @elseif($imprestRequest->status == 'approved') btn-info
                                @elseif(in_array($imprestRequest->status, ['pending_hod', 'pending_ceo'])) btn-warning
                                @else btn-secondary
                                @endif">
                                {{ ucwords(str_replace('_', ' ', $imprestRequest->status)) }}
                            </span>
                            @if($imprestRequest->priority == 'urgent')
                                <span class="badge btn-danger badge-lg px-3 py-2 mt-2 d-block">
                                    <i class="fa fa-exclamation-triangle"></i> URGENT
                                </span>
                            @elseif($imprestRequest->priority == 'high')
                                <span class="badge btn-warning badge-lg px-3 py-2 mt-2 d-block">
                                    <i class="fa fa-arrow-up"></i> HIGH PRIORITY
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0 text-white">{{ number_format($imprestRequest->amount, 2) }}</h2>
                    <p class="mb-0"><small>Total Amount (TZS)</small></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $imprestRequest->assignments ? $imprestRequest->assignments->count() : 0 }}</h2>
                    <p class="mb-0"><small>Staff Assigned</small></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body text-center">
                    <h2 class="mb-0">{{ $imprestRequest->receipts ? $imprestRequest->receipts->count() : 0 }}</h2>
                    <p class="mb-0"><small>Receipts Submitted</small></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="progress mb-2" style="height: 30px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated 
                            @if($imprestRequest->progress_percentage == 100) bg-success
                            @elseif($imprestRequest->progress_percentage >= 50) bg-info
                            @else bg-warning
                            @endif" 
                            role="progressbar" 
                            style="width: {{ $imprestRequest->progress_percentage }}%">
                            {{ $imprestRequest->progress_percentage }}%
                        </div>
                    </div>
                    <p class="mb-0"><small>Progress</small></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Left Column - Request Details -->
        <div class="col-lg-8">
            <!-- Request Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white"><i class="fa fa-info-circle"></i> Request Information</h5>
                </div>
                <br>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Purpose</label>
                            <p class="mb-0 font-weight-bold">{{ $imprestRequest->purpose }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Amount</label>
                            <p class="mb-0 font-weight-bold text-primary">
                                <i class="fa fa-money-bill-wave"></i> TZS {{ number_format($imprestRequest->amount, 2) }}
                            </p>
                        </div>
                        @if($imprestRequest->expected_return_date)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Expected Return Date</label>
                            <p class="mb-0">
                                <i class="fa fa-calendar-check"></i> 
                                {{ $imprestRequest->expected_return_date->format('d M Y') }}
                            </p>
                        </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Priority</label>
                            <p class="mb-0">
                                <span class="badge 
                                    @if($imprestRequest->priority == 'urgent') btn-danger
                                    @elseif($imprestRequest->priority == 'high') btn-warning
                                    @else btn-info
                                    @endif">
                                    {{ ucfirst($imprestRequest->priority) }}
                                </span>
                            </p>
                        </div>
                        @if($imprestRequest->description)
                        <div class="col-12 mb-3">
                            <label class="text-muted small">Description</label>
                            <p class="mb-0">{{ $imprestRequest->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Staff Assignments -->
            @if($imprestRequest->assignments && $imprestRequest->assignments->count() > 0)
            @php
                $user = auth()->user();
                $isStaff = $user->hasAnyRole(['Staff', 'Employee']) && !$user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO', 'Director']);
                $isApprover = $user->hasAnyRole(['System Admin', 'Accountant', 'HOD', 'CEO', 'Director']);
                $currentUserId = auth()->id();
            @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fa fa-users"></i> 
                        @if($isStaff)
                            My Assignment
                        @else
                            Staff Assignments ({{ $imprestRequest->assignments->count() }})
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    @if(!$isStaff)
                                    <th>Staff Member</th>
                                    <th>Department</th>
                                    <th>Employee ID</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    @endif
                                    <th class="text-right">Assigned Amount</th>
                                    <th>Assignment Date</th>
                                    @if($isApprover)
                                    <th>Payment Status</th>
                                    <th>Payment Method</th>
                                    <th>Payment Date</th>
                                    <th>Bank Details</th>
                                    @endif
                                    <th>Receipt Status</th>
                                    <th>Receipts</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($imprestRequest->assignments as $assignment)
                                @if($assignment && (!$isStaff || $assignment->staff_id == $currentUserId))
                                <tr>
                                    @if(!$isStaff)
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm mr-2 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                                {{ strtoupper(substr($assignment->staff->name ?? 'N/A', 0, 2)) }}
                                            </div>
                                            <div>
                                                <strong>{{ $assignment->staff->name ?? 'N/A' }}</strong>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge btn-secondary">
                                            {{ $assignment->staff && $assignment->staff->primaryDepartment ? $assignment->staff->primaryDepartment->name : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $assignment->staff->employee_id ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $assignment->staff->email ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $assignment->staff->phone ?? ($assignment->staff->mobile ?? 'N/A') }}</small>
                                    </td>
                                    @endif
                                    <td class="text-right">
                                        <strong class="text-success">
                                            TZS {{ number_format($assignment->assigned_amount ?? 0, 2) }}
                                        </strong>
                                    </td>
                                    <td>
                                        <small>
                                            <i class="fa fa-calendar"></i> 
                                            {{ $assignment->assigned_at ? $assignment->assigned_at->format('d M Y') : 'N/A' }}
                                        </small>
                                    </td>
                                    @if($isApprover)
                                    <td>
                                        @php
                                            $isPaid = $assignment->paid_at && $assignment->paid_amount && $assignment->paid_amount > 0;
                                        @endphp
                                        @if($isPaid)
                                            <span class="badge btn-success">
                                                <i class="fa fa-check"></i> Paid
                                            </span>
                                            @if($assignment->paid_amount)
                                                <br><small class="text-success mt-1 d-block">
                                                    TZS {{ number_format($assignment->paid_amount, 2) }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="badge btn-warning">
                                                <i class="fa fa-clock"></i> Not Paid
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->payment_method)
                                            <span class="badge btn-info">
                                                {{ ucwords(str_replace('_', ' ', $assignment->payment_method)) }}
                                            </span>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->payment_date)
                                            <small>{{ \Carbon\Carbon::parse($assignment->payment_date)->format('d M Y') }}</small>
                                        @elseif($assignment->paid_at)
                                            <small>{{ $assignment->paid_at->format('d M Y') }}</small>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->bank_name && $assignment->account_number)
                                            <small>
                                                <strong>{{ $assignment->bank_name }}</strong><br>
                                                <span class="text-muted">{{ $assignment->account_number }}</span>
                                            </small>
                                        @elseif($assignment->staff && $assignment->staff->primaryBankAccount)
                                            <small>
                                                <strong>{{ $assignment->staff->primaryBankAccount->bank_name ?? 'N/A' }}</strong><br>
                                                <span class="text-muted">{{ $assignment->staff->primaryBankAccount->account_number ?? 'N/A' }}</span>
                                            </small>
                                        @else
                                            <small class="text-muted">N/A</small>
                                        @endif
                                    </td>
                                    @endif
                                    <td>
                                        @php
                                            // Check both flag and if receipts actually exist
                                            $hasReceipts = $assignment->receipts && $assignment->receipts->count() > 0;
                                            $isSubmitted = $assignment->receipt_submitted || $hasReceipts;
                                        @endphp
                                        @if($isSubmitted)
                                            <span class="badge btn-success">
                                                <i class="fa fa-check"></i> Submitted
                                            </span>
                                            @php
                                                $verifiedReceipts = $assignment->receipts ? $assignment->receipts->where('is_verified', true) : collect();
                                            @endphp
                                            @if($verifiedReceipts && $verifiedReceipts->count() > 0)
                                                <br><small class="text-success mt-1 d-block">
                                                    <i class="fa fa-check-circle"></i> Verified ({{ $verifiedReceipts->count() }})
                                                </small>
                                            @elseif($assignment->receipts && $assignment->receipts->count() > 0)
                                                <br><small class="text-warning mt-1 d-block">
                                                    <i class="fa fa-clock"></i> Awaiting Verification
                                                </small>
                                            @endif
                                        @else
                                            <span class="badge btn-warning">
                                                <i class="fa fa-clock"></i> Pending Submission
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->receipts && $assignment->receipts->count() > 0)
                                            <span class="badge btn-info">{{ $assignment->receipts->count() }} receipt(s)</span>
                                        @else
                                            <small class="text-muted">0</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$isSubmitted && auth()->user()->id == $assignment->staff_id && $imprestRequest->status == 'paid')
                                            <button class="btn btn-sm btn-primary" onclick="openSubmitReceipt({{ $assignment->id }})">
                                                <i class="fa fa-upload"></i> Submit Receipt
                                            </button>
                                        @elseif($isSubmitted && auth()->user()->id == $assignment->staff_id)
                                            <button class="btn btn-sm btn-success" onclick="viewMyReceipts({{ $assignment->id }})">
                                                <i class="fa fa-eye"></i> View My Receipts
                                            </button>
                                        @endif
                                        @if(!$isStaff && $assignment->receipts && $assignment->receipts->count() > 0)
                                            <button class="btn btn-sm btn-info mt-1" onclick="viewReceipts({{ $assignment->id }})">
                                                <i class="fa fa-eye"></i> View Receipts
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif




            <!-- Receipts Section -->
            @if($imprestRequest->receipts && $imprestRequest->receipts->count() > 0)
            @php
                $canVerify = auth()->user()->hasAnyRole(['Accountant', 'System Admin']);
            @endphp
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa fa-receipt"></i> Submitted Receipts ({{ $imprestRequest->receipts->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($imprestRequest->receipts as $receipt)
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="fa fa-file-alt"></i> 
                                                {{ $receipt->receipt_description }}
                                            </h6>
                                            <p class="text-muted mb-1 small">
                                                Amount: <strong>TZS {{ number_format($receipt->receipt_amount, 2) }}</strong>
                                            </p>
                                            <p class="text-muted mb-1 small">
                                                Submitted by: {{ $receipt->submittedBy->name ?? 'N/A' }}
                                            </p>
                                            <p class="text-muted mb-1 small">
                                                Submitted: {{ $receipt->submitted_at ? $receipt->submitted_at->format('d M Y, H:i') : 'N/A' }}
                                            </p>
                                        </div>
                                        <div>
                                            @if($receipt->is_verified)
                                                <span class="badge badge-success">
                                                    <i class="fa fa-check-circle"></i> Verified
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    <i class="fa fa-clock"></i> Pending Verification
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($receipt->verifiedBy)
                                        <p class="text-success mb-1 small">
                                            <i class="fa fa-user-check"></i> Verified by {{ $receipt->verifiedBy->name }}
                                            @if($receipt->verified_at)
                                                on {{ $receipt->verified_at->format('d M Y, H:i') }}
                                            @endif
                                        </p>
                                        @if($receipt->verification_notes)
                                            <p class="text-info mb-1 small">
                                                <i class="fa fa-comment"></i> Notes: {{ $receipt->verification_notes }}
                                            </p>
                                        @endif
                                    @endif
                                    <div class="d-flex gap-2 mt-2">
                                    <a href="{{ asset('storage/' . $receipt->receipt_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-download"></i> View Receipt
                                    </a>
                                        @if($canVerify && !$receipt->is_verified && in_array($imprestRequest->status, ['pending_receipt_verification', 'paid']))
                                            <button class="btn btn-sm btn-success" onclick="openVerifyReceipt({{ $receipt->id }})">
                                                <i class="fa fa-check-circle"></i> Verify Receipt
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Payment Information -->
            @if($imprestRequest->paid_at)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fa fa-credit-card"></i> Payment Information</h5>
                </div>
                <br>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Payment Date</label>
                            <p class="mb-0">
                                <i class="fa fa-calendar"></i> 
                                {{ $imprestRequest->paid_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                        @if($imprestRequest->payment_method)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Payment Method</label>
                            <p class="mb-0">
                                <i class="fa fa-wallet"></i> 
                                {{ ucwords(str_replace('_', ' ', $imprestRequest->payment_method)) }}
                            </p>
                        </div>
                        @endif
                        @if($imprestRequest->payment_reference)
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Payment Reference</label>
                            <p class="mb-0"><strong>{{ $imprestRequest->payment_reference }}</strong></p>
                        </div>
                        @endif
                        @if($imprestRequest->payment_notes)
                        <div class="col-12 mb-3">
                            <label class="text-muted small">Payment Notes</label>
                            <p class="mb-0">{{ $imprestRequest->payment_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Timeline & Actions -->
        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0 text-white"><i class="fa fa-history"></i> Request Timeline</h5>
                </div>
                <br>
                <div class="card-body">
                    <div class="timeline-vertical">
                        <div class="timeline-item completed">
                            <div class="timeline-marker bg-primary">
                                <i class="fa fa-plus"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Request Created</h6>
                                <p class="text-muted mb-1 small">{{ $imprestRequest->created_at->format('d M Y, H:i') }}</p>
                                <small>By {{ $imprestRequest->accountant->name ?? 'Accountant' }}</small>
                            </div>
                        </div>

                        @if($imprestRequest->hod_approved_at)
                        <div class="timeline-item completed">
                            <div class="timeline-marker bg-success">
                                <i class="fa fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">HOD Approved</h6>
                                <p class="text-muted mb-1 small">{{ $imprestRequest->hod_approved_at->format('d M Y, H:i') }}</p>
                                <small>By {{ $imprestRequest->hodApproval->name ?? 'HOD' }}</small>
                            </div>
                        </div>
                        @endif

                        @if($imprestRequest->ceo_approved_at)
                        <div class="timeline-item completed">
                            <div class="timeline-marker bg-success">
                                <i class="fa fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">CEO Approved</h6>
                                <p class="text-muted mb-1 small">{{ $imprestRequest->ceo_approved_at->format('d M Y, H:i') }}</p>
                                <small>By {{ $imprestRequest->ceoApproval->name ?? 'CEO' }}</small>
                            </div>
                        </div>
                        @endif

                        @if($imprestRequest->assignments && $imprestRequest->assignments->count() > 0)
                        <div class="timeline-item completed">
                            <div class="timeline-marker bg-info">
                                <i class="fa fa-users"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Staff Assigned</h6>
                                <p class="text-muted mb-1 small">
                                    @php
                                        $firstAssignment = $imprestRequest->assignments->first();
                                        $assignedDate = $firstAssignment && $firstAssignment->assigned_at 
                                            ? $firstAssignment->assigned_at->format('d M Y') 
                                            : 'N/A';
                                    @endphp
                                    {{ $assignedDate }}
                                </p>
                                <small>{{ $imprestRequest->assignments->count() }} staff member(s)</small>
                            </div>
                        </div>
                        @endif

                        @if($imprestRequest->paid_at)
                        <div class="timeline-item completed">
                            <div class="timeline-marker bg-success">
                                <i class="fa fa-money-bill-wave"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Payment Processed</h6>
                                <p class="text-muted mb-1 small">{{ $imprestRequest->paid_at->format('d M Y, H:i') }}</p>
                                <small>{{ ucwords(str_replace('_', ' ', $imprestRequest->payment_method ?? 'Payment')) }}</small>
                            </div>
                        </div>
                        @endif

                        @if($imprestRequest->completed_at)
                        <div class="timeline-item completed">
                            <div class="timeline-marker bg-dark">
                                <i class="fa fa-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Request Completed</h6>
                                <p class="text-muted mb-1 small">{{ $imprestRequest->completed_at->format('d M Y, H:i') }}</p>
                                <small>All receipts verified</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.imprest-details-container {
    padding: 20px;
}

.badge-lg {
    font-size: 0.95rem;
    padding: 0.5rem 1rem;
}

.bg-gradient-primary {
    background: var(--bs-primary);
}

.bg-gradient-info {
    background: var(--bs-primary);
}

.bg-gradient-success {
    background: var(--bs-primary);
}

.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 14px;
    font-weight: bold;
}

.timeline-vertical {
    position: relative;
    padding-left: 40px;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
    padding-bottom: 25px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -32px;
    top: 30px;
    width: 2px;
    height: calc(100% + 10px);
    background: #e9ecef;
}

.timeline-marker {
    position: absolute;
    left: -40px;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
    color: #2c3e50;
}

.timeline-content p {
    margin-bottom: 5px;
}

.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.progress-bar-animated {
    animation: progress-bar-stripes 1s linear infinite;
}
</style>
