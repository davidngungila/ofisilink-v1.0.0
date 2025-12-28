@extends('layouts.app')

@section('title', 'New Leave Request - OfisiLink')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title text-white mb-1">
                                <i class="bx bx-plus-circle me-2"></i>New Leave Request
                            </h4>
                            <p class="card-text text-white-50 mb-0">Submit a new leave request</p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.leave') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Leave Management
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Leave Request Form -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="requestLeaveForm" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="action" id="request_action" value="request_leave">
                        <input type="hidden" name="request_id" id="request_id" value="">
                        
                        <h6>Employee Details</h6>
                        <div class="row bg-light p-3 rounded mb-4">
                            <div class="col-md-4"><strong>Name:</strong> {{ $user->name }}</div>
                            <div class="col-md-4"><strong>Department:</strong> {{ $user->primaryDepartment->name ?? 'N/A' }}</div>
                            <div class="col-md-4"><strong>Position:</strong> {{ $user->employee->position ?? 'N/A' }}</div>
                        </div>
                        
                        <!-- Annual Leave Balance Display -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Annual Leave Balance ({{ date('Y') }})
                                                </div>
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col-auto">
                                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800" id="annual-balance-display">
                                                            <span class="spinner-border spinner-border-sm text-primary me-2" role="status"></span>
                                                            Loading...
                                                        </div>
                                                    </div>
                                                    <div class="col">
                                                        <div class="progress progress-lg mr-2" style="height: 30px;">
                                                            <div id="annual-balance-progress" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                                                 role="progressbar" style="width: 0%; font-size: 14px; line-height: 30px; font-weight: bold;">
                                                                <span id="balance-percentage">0%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-xs font-weight-bold text-gray-600 mt-2" id="annual-balance-details">
                                                    Total: <span id="balance-total">28</span> days | Taken: <span id="balance-taken">0</span> days | Remaining: <span id="balance-remaining">28</span> days
                                                </div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="bx bx-calendar-check fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Leave Recommendations Section -->
                        <div class="row mb-4" id="recommendations-section" style="display: none;">
                            <div class="col-12">
                                <div class="card border-left-success shadow">
                                    <div class="card-header bg-success text-white py-2">
                                        <h6 class="m-0 font-weight-bold"><i class="bx bx-lightbulb me-2"></i> Recommended Leave Periods</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="personal-recommendations" class="mb-4">
                                            <h6 class="text-success mb-3"><i class="bx bx-user-check me-2"></i> Your Personal Recommendations</h6>
                                            <div id="personal-rec-list" class="row g-3">
                                                <div class="col-12 text-center text-muted py-3">
                                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                                    Loading recommendations...
                                                </div>
                                            </div>
                                        </div>
                                        <div id="optimal-periods" class="mb-3">
                                            <h6 class="text-info mb-3"><i class="bx bx-trending-up me-2"></i> Optimal Periods for Your Department</h6>
                                            <div id="optimal-periods-list" class="row g-3">
                                                <div class="col-12 text-center text-muted py-3">
                                                    <div class="spinner-border spinner-border-sm text-info me-2"></div>
                                                    Loading optimal periods...
                                                </div>
                                            </div>
                                        </div>
                                        <small class="text-muted"><i class="bx bx-info-circle me-1"></i> Click "Use This" to automatically fill the leave dates</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6>Leave Details</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Leave Type *</label>
                                <select name="leave_type_id" id="leave_type_id" class="form-select" required>
                                    <option value="">-- Select --</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Start Date *</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">End Date *</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required min="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Days</label>
                                <input type="text" name="total_days" id="total_days" class="form-control" readonly>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Location During Leave *</label>
                                <input type="text" name="leave_location" id="leave_location" class="form-control" placeholder="e.g., Arusha, Tanzania" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Reason for Leave *</label>
                            <textarea name="reason" id="reason" class="form-control" rows="3" placeholder="Please provide a detailed reason for your leave..." required></textarea>
                        </div>
                        
                        <h6 class="mt-4">Dependents (if applicable for fare/nauli)</h6>
                        <div class="alert alert-info">
                            <small><i class="bx bx-info-circle me-1"></i> Add dependents who will be traveling with you for fare calculation purposes. HR will review and approve fare amounts.</small>
                        </div>
                        <div id="dependents-container" class="mb-3"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-dependent-btn">
                            <i class="bx bx-plus me-1"></i>Add Dependent
                        </button>
                        <small class="text-muted d-block mt-2">
                            <i class="bx bx-info-circle me-1"></i> Each dependent's fare will be calculated separately and reviewed by HR
                        </small>
                        
                        <div class="mt-4">
                            <a href="{{ route('modules.hr.leave') }}" class="btn btn-secondary">
                                <i class="bx bx-x me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-check me-1"></i>Submit Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .recommendation-card {
        border-left: 4px solid #28a745;
        transition: transform 0.2s;
    }
    .recommendation-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .optimal-period-card {
        border-left: 4px solid #17a2b8;
    }
    .use-recommendation-btn {
        white-space: nowrap;
    }
    .dependent-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
    }
    #annual-balance-progress {
        transition: width 0.6s ease;
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Fallback for SweetAlert2
if (typeof window.Swal === 'undefined') {
    window.Swal = {
        fire: function(opts) {
            alert(opts.title + (opts.text ? '\n' + opts.text : ''));
            return Promise.resolve({ isConfirmed: true });
        }
    };
}

const csrfToken = $('meta[name="csrf-token"]').attr('content');
let dependentCount = 0;

// Load annual leave balance with percentage splash
function loadAnnualLeaveBalance() {
    $.post('{{ route("leave.annual-balance") }}', { 
        _token: csrfToken,
        year: new Date().getFullYear()
    }, function(response) {
        if (response.success) {
            const balance = response.balance;
            const totalDays = parseFloat(balance.total_days_allotted || 28);
            const takenDays = parseFloat(balance.days_taken || 0);
            const remaining = totalDays - takenDays;
            const percentage = totalDays > 0 ? Math.round((takenDays / totalDays) * 100) : 0;
            const remainingPercentage = totalDays > 0 ? Math.round((remaining / totalDays) * 100) : 100;
            
            // Update display with percentage
            $('#annual-balance-display').html(`
                <span class="text-primary">${remainingPercentage}%</span> 
                <span class="text-muted">(${remaining} days remaining)</span>
            `);
            
            // Update progress bar with animation
            $('#annual-balance-progress').css('width', `${percentage}%`);
            $('#balance-percentage').text(`${percentage}%`);
            
            // Update details
            $('#balance-total').text(totalDays);
            $('#balance-taken').text(takenDays);
            $('#balance-remaining').text(remaining);
            
            // Update progress bar color based on usage
            const progressBar = $('#annual-balance-progress');
            progressBar.removeClass('bg-primary bg-warning bg-danger bg-success');
            if (percentage >= 90) {
                progressBar.addClass('bg-danger');
            } else if (percentage >= 70) {
                progressBar.addClass('bg-warning');
            } else if (percentage >= 50) {
                progressBar.addClass('bg-info');
            } else {
                progressBar.addClass('bg-success');
            }
        } else {
            // Show default values on error
            $('#annual-balance-display').html('<span class="text-primary">100%</span> <span class="text-muted">(28 days remaining)</span>');
            $('#annual-balance-progress').css('width', '0%').removeClass('bg-warning bg-danger').addClass('bg-success');
            $('#balance-percentage').text('0%');
            $('#balance-total').text('28');
            $('#balance-taken').text('0');
            $('#balance-remaining').text('28');
        }
    }).fail(function(xhr) {
        console.error('Failed to load annual balance:', xhr);
        // Show default values on error
        $('#annual-balance-display').html('<span class="text-primary">100%</span> <span class="text-muted">(28 days remaining)</span>');
        $('#annual-balance-progress').css('width', '0%').removeClass('bg-warning bg-danger').addClass('bg-success');
        $('#balance-percentage').text('0%');
        $('#balance-total').text('28');
        $('#balance-taken').text('0');
        $('#balance-remaining').text('28');
    });
}

// Load leave recommendations
function loadLeaveRecommendations() {
    $.post('{{ route("leave.recommendations") }}', { 
        _token: csrfToken,
        year: new Date().getFullYear()
    }, function(response) {
        if (response.success) {
            const { recommendations, optimal_periods } = response;
            
            // Show recommendations section if we have any
            if (recommendations.length > 0 || optimal_periods.length > 0) {
                $('#recommendations-section').show();
            }
            
            // Personal recommendations
            let personalHtml = '';
            if (recommendations.length > 0) {
                recommendations.forEach(rec => {
                    const startDate = new Date(rec.recommended_start_date).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric' 
                    });
                    const endDate = new Date(rec.recommended_end_date).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric' 
                    });
                    const days = Math.ceil((new Date(rec.recommended_end_date) - new Date(rec.recommended_start_date)) / (1000 * 60 * 60 * 24)) + 1;
                    
                    personalHtml += `
                        <div class="col-md-6">
                            <div class="card recommendation-card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><i class="bx bx-calendar-check text-success me-1"></i>Recommended Period</h6>
                                            <p class="mb-1"><strong>${startDate}</strong> to <strong>${endDate}</strong></p>
                                            <p class="mb-1 text-muted small">${days} days</p>
                                            ${rec.notes ? `<p class="mb-0 text-muted small"><i class="bx bx-info-circle me-1"></i>${rec.notes}</p>` : ''}
                                        </div>
                                        <button type="button" class="btn btn-sm btn-success use-recommendation-btn" 
                                                onclick="useRecommendation('${rec.recommended_start_date}', '${rec.recommended_end_date}')">
                                            <i class="bx bx-check me-1"></i>Use This
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                personalHtml = '<div class="col-12"><p class="text-muted text-center py-2">No personal recommendations available.</p></div>';
            }
            $('#personal-rec-list').html(personalHtml);
            
            // Optimal periods
            let optimalHtml = '';
            if (optimal_periods.length > 0) {
                optimal_periods.forEach(period => {
                    const startDate = new Date(period.start_date).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric' 
                    });
                    const endDate = new Date(period.end_date).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric' 
                    });
                    const days = Math.ceil((new Date(period.end_date) - new Date(period.start_date)) / (1000 * 60 * 60 * 24)) + 1;
                    
                    optimalHtml += `
                        <div class="col-md-6">
                            <div class="card optimal-period-card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><i class="bx bx-trending-up text-info me-1"></i>${period.period || 'Optimal Period'}</h6>
                                            <p class="mb-1"><strong>${startDate}</strong> to <strong>${endDate}</strong></p>
                                            <p class="mb-1 text-muted small">${days} days</p>
                                            ${period.reason ? `<p class="mb-0 text-muted small"><i class="bx bx-info-circle me-1"></i>${period.reason}</p>` : ''}
                                        </div>
                                        <button type="button" class="btn btn-sm btn-info use-recommendation-btn" 
                                                onclick="useRecommendation('${period.start_date}', '${period.end_date}')">
                                            <i class="bx bx-check me-1"></i>Use This
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                optimalHtml = '<div class="col-12"><p class="text-muted text-center py-2">No optimal periods identified.</p></div>';
            }
            $('#optimal-periods-list').html(optimalHtml);
        } else {
            $('#recommendations-section').hide();
        }
    }).fail(function(xhr) {
        console.error('Failed to load recommendations:', xhr);
        $('#recommendations-section').hide();
    });
}

// Use recommendation to fill form
function useRecommendation(startDate, endDate) {
    $('#start_date').val(startDate);
    $('#end_date').val(endDate);
    
    // Calculate total days
    const start = new Date(startDate);
    const end = new Date(endDate);
    const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
    $('#total_days').val(days);
    
    // Show success message
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Dates Applied!',
            text: 'Leave dates have been filled from recommendation.',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
}

// Add dependent
$('#add-dependent-btn').on('click', function() {
    dependentCount++;
    const dependentHtml = `
        <div class="dependent-item" id="dependent-${dependentCount}">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Dependent Name *</label>
                    <input type="text" name="dependents[${dependentCount}][name]" class="form-control form-control-sm" required placeholder="Full name">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Relationship *</label>
                    <select name="dependents[${dependentCount}][relationship]" class="form-select form-select-sm" required>
                        <option value="">-- Select --</option>
                        <option value="spouse">Spouse</option>
                        <option value="child">Child</option>
                        <option value="parent">Parent</option>
                        <option value="sibling">Sibling</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Fare Amount (TZS)</label>
                    <input type="number" name="dependents[${dependentCount}][fare_amount]" class="form-control form-control-sm" 
                           placeholder="0.00" step="0.01" min="0" value="0" readonly>
                    <small class="text-muted"><i class="bx bx-info-circle me-1"></i>HR will assign fare amount during review</small>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeDependent(${dependentCount})">
                        <i class="bx bx-trash"></i> Remove
                    </button>
                </div>
            </div>
        </div>
    `;
    $('#dependents-container').append(dependentHtml);
});

// Remove dependent
function removeDependent(id) {
    $(`#dependent-${id}`).remove();
}

// Calculate total days
$('#start_date, #end_date').on('change', function() {
    const start = $('#start_date').val();
    const end = $('#end_date').val();
    if (start && end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        if (endDate >= startDate) {
            const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
            $('#total_days').val(days);
        } else {
            $('#total_days').val('');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Dates',
                    text: 'End date must be after start date.',
                    timer: 3000,
                    showConfirmButton: false
                });
            }
        }
    }
});

// Form submission
$('#requestLeaveForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Disable submit button
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting...');
    
        $.ajax({
            url: '{{ route("leave.store") }}',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message || 'Leave request submitted successfully!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.href = '{{ route("modules.hr.leave") }}';
                    });
                } else {
                    alert('Leave request submitted successfully!');
                    window.location.href = '{{ route("modules.hr.leave") }}';
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to submit leave request.'
                    });
                } else {
                    alert('Error: ' + (response.message || 'Failed to submit leave request.'));
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Failed to submit leave request. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg
                });
            } else {
                alert('Error: ' + errorMsg);
            }
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});

// Load data on page load
$(document).ready(function() {
    loadAnnualLeaveBalance();
    loadLeaveRecommendations();
});
</script>
@endpush

