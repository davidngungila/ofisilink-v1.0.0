@extends('layouts.app')

@section('title', 'Assign Staff to Imprest Request')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Assign Staff to Imprest Request</h4>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('imprest.index') }}">Imprest Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('imprest.show', $imprestRequest->id) }}">{{ $imprestRequest->request_no }}</a></li>
            <li class="breadcrumb-item active">Assign Staff</li>
        </ol>
    </nav>
</div>
@endsection

@push('styles')
<style>
    .staff-card {
        border: 2px solid #dee2e6;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .staff-card:hover {
        border-color: #0d6efd;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .staff-card.selected {
        border-color: #0d6efd;
        background-color: #e7f3ff;
    }
    
    .staff-card.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: #f8f9fa;
    }
    
    .staff-card input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
    
    .info-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('imprest.show', $imprestRequest->id) }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i>Back to Details
        </a>
    </div>

    <!-- Header -->
    <div class="card border-0 shadow-sm mb-4 bg-primary">
        <div class="card-body text-white">
            <h2 class="fw-bold mb-2 text-white">
                <i class="bx bx-user-plus me-2"></i>Assign Staff to Imprest Request
            </h2>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Request Number:</strong> {{ $imprestRequest->request_no }}</p>
                    <p class="mb-1"><strong>Purpose:</strong> {{ $imprestRequest->purpose }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Amount:</strong> TZS {{ number_format($imprestRequest->amount, 2) }}</p>
                    <p class="mb-1"><strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $imprestRequest->status)) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Alert -->
    @if(count($staffWithUnretiredImprests) > 0)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <h5 class="alert-heading"><i class="bx bx-info-circle me-2"></i>Important Notice</h5>
        <p class="mb-0">Some staff members have unretired imprests and cannot be assigned to new imprests until their previous assignments are completed. These staff members are marked and disabled below.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Already Assigned Staff -->
    @if(count($assignedStaffIds) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bx bx-user-check me-2"></i>Already Assigned Staff ({{ count($assignedStaffIds) }})</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($imprestRequest->assignments as $assignment)
                <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-user-check text-info me-2"></i>
                        <span><strong>{{ $assignment->staff->name ?? 'Unknown' }}</strong> - TZS {{ number_format($assignment->assigned_amount, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            <p class="text-muted mb-0 mt-2"><small>You can add more staff members below. The amount will be recalculated and distributed equally among all assigned staff.</small></p>
        </div>
    </div>
    @endif

    <!-- Staff Selection Form -->
    <form id="assignStaffForm" method="POST" action="{{ route('imprest.assign-staff') }}">
        @csrf
        <input type="hidden" name="imprest_id" value="{{ $imprestRequest->id }}">
        
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0 text-white"><i class="bx bx-list-ul me-2"></i>Select Staff Members</h5>
            </div>
            <div class="card-body">
                <!-- Search Box -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        <i class="bx bx-search me-1"></i>Search Staff
                    </label>
                    <input type="text" class="form-control" id="staffSearchInput" placeholder="Search by name or department...">
                </div>

                <!-- Staff List -->
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Select Staff Members <span class="text-danger">*</span>
                    </label>
                    <div id="staffList" style="max-height: 500px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.375rem; padding: 1rem;">
                        @foreach($staffMembers as $staff)
                        @php
                            $hasUnretired = isset($staffWithUnretiredImprests[$staff->id]) && count($staffWithUnretiredImprests[$staff->id]) > 0;
                            $unretiredList = $hasUnretired ? collect($staffWithUnretiredImprests[$staff->id])->pluck('request_no')->implode(', ') : '';
                            $isAlreadyAssigned = in_array($staff->id, $assignedStaffIds);
                        @endphp
                        <div class="staff-card {{ $isAlreadyAssigned ? 'disabled' : '' }}" data-staff-name="{{ strtolower($staff->name) }}" data-staff-dept="{{ strtolower(optional($staff->primaryDepartment)->name ?? 'no department') }}">
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input me-3" type="checkbox" 
                                       name="staff_ids[]" 
                                       value="{{ $staff->id }}" 
                                       id="staff_{{ $staff->id }}"
                                       {{ $hasUnretired || $isAlreadyAssigned ? 'disabled' : '' }}
                                       onchange="toggleStaffCard(this)">
                                <label class="form-check-label flex-grow-1" for="staff_{{ $staff->id }}" style="cursor: pointer;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $staff->name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bx bx-building"></i> {{ optional($staff->primaryDepartment)->name ?? 'No Department' }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            @if($isAlreadyAssigned)
                                                <span class="badge bg-info info-badge">Already Assigned</span>
                                            @elseif($hasUnretired)
                                                <span class="badge bg-danger info-badge">Has Unretired Imprest</span>
                                                <br><small class="text-danger">{{ $unretiredList }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bx bx-info-circle"></i> Select one or more staff members to assign to this imprest request.
                        @if(count($staffWithUnretiredImprests) > 0)
                            <span class="text-danger">Staff with unretired imprests cannot be selected.</span>
                        @endif
                    </small>
                </div>

                <!-- Assignment Notes -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Assignment Notes</label>
                    <textarea class="form-control" name="assignment_notes" rows="3" placeholder="Optional notes about this assignment..."></textarea>
                    <small class="text-muted">
                        <i class="bx bx-info-circle"></i> Add any additional information about this assignment
                    </small>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('imprest.show', $imprestRequest->id) }}" class="btn btn-secondary btn-lg">
                <i class="bx bx-x me-1"></i>Cancel
            </a>
            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                <i class="bx bx-check-circle me-1"></i>Assign Staff
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('staffSearchInput');
    const staffCards = document.querySelectorAll('.staff-card');
    
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            staffCards.forEach(card => {
                const staffName = card.getAttribute('data-staff-name') || '';
                const staffDept = card.getAttribute('data-staff-dept') || '';
                
                if (staffName.includes(searchTerm) || staffDept.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Form submission
    const form = document.getElementById('assignStaffForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if at least one staff is selected
            const selectedStaff = form.querySelectorAll('input[name="staff_ids[]"]:checked:not(:disabled)');
            if (selectedStaff.length === 0) {
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', 'Please select at least one staff member to assign.', { duration: 5000 });
                } else {
                    alert('Please select at least one staff member to assign.');
                }
                return;
            }
            
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Assigning...';
            
            const formData = new FormData(form);
            
            fetch('{{ route("imprest.assign-staff") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async response => {
                const result = await response.json();
                
                if (response.ok && result.success) {
                    if (typeof window.AdvancedToast !== 'undefined') {
                        window.AdvancedToast.success('Success', result.message || 'Staff assigned successfully!', { duration: 5000 });
                    } else {
                        alert('Staff assigned successfully!');
                    }
                    
                    // Redirect to imprest details page
                    setTimeout(() => {
                        window.location.href = '{{ route("imprest.show", $imprestRequest->id) }}';
                    }, 1000);
                } else {
                    let errorMsg = result.message || 'Failed to assign staff.';
                    
                    if (result.unretired_imprests && result.unretired_imprests.length > 0) {
                        let detailsHtml = '<div style="text-align: left; margin-top: 10px;">';
                        detailsHtml += '<strong>Staff with unretired imprests:</strong><ul style="margin-top: 10px;">';
                        
                        const staffMap = {};
                        result.unretired_imprests.forEach(function(item) {
                            if (!staffMap[item.staff_id]) {
                                staffMap[item.staff_id] = {
                                    name: item.staff_name,
                                    imprests: []
                                };
                            }
                            staffMap[item.staff_id].imprests.push(item.request_no + ' (' + item.status + ')');
                        });
                        
                        Object.keys(staffMap).forEach(function(staffId) {
                            const staff = staffMap[staffId];
                            detailsHtml += '<li><strong>' + staff.name + ':</strong> ' + staff.imprests.join(', ') + '</li>';
                        });
                        
                        detailsHtml += '</ul></div>';
                        
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Cannot Assign Staff',
                                html: errorMsg + detailsHtml,
                                confirmButtonText: 'OK',
                                width: '600px'
                            });
                        } else {
                            alert(errorMsg);
                        }
                    } else {
                        if (typeof window.AdvancedToast !== 'undefined') {
                            window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                        } else {
                            alert('Error: ' + errorMsg);
                        }
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMsg = error.message || 'Network error occurred';
                if (typeof window.AdvancedToast !== 'undefined') {
                    window.AdvancedToast.error('Error', errorMsg, { duration: 10000 });
                } else {
                    alert('Error: ' + errorMsg);
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});

function toggleStaffCard(checkbox) {
    const card = checkbox.closest('.staff-card');
    if (checkbox.checked) {
        card.classList.add('selected');
    } else {
        card.classList.remove('selected');
    }
}
</script>
@endpush







