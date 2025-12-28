@extends('layouts.app')

@section('title', 'Leave Recommendations Management - OfisiLink')

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
                                <i class="bx bx-calendar-check me-2"></i>Leave Recommendations Management
                            </h4>
                            <p class="card-text text-white-50 mb-0">Manage leave recommendations for employees</p>
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

    <!-- Recommendations Management Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Leave Recommendations Management</h6>
                        <button class="btn btn-sm btn-primary" onclick="showRecommendationModal()">
                            <i class="bx bx-plus"></i> Add Recommendation
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Filter by Year</label>
                            <select class="form-select" id="recommendation-year-filter" onchange="loadRecommendations()">
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter by Employee</label>
                            <select class="form-select" id="recommendation-employee-filter" onchange="loadRecommendations()">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Filter by Department</label>
                            <select class="form-select" id="recommendation-dept-filter" onchange="loadRecommendations()">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <button class="btn btn-sm btn-outline-primary" onclick="selectAllRecommendations()">
                                <i class="bx bx-check-square"></i> Select All
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="deselectAllRecommendations()">
                                <i class="bx bx-square"></i> Deselect All
                            </button>
                            <span class="ms-2 text-muted" id="selected-recommendations-count">0 selected</span>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-success" onclick="autoAssignRecommendations()">
                                <i class="bx bx-calendar-check"></i> Auto-Assign Dates
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bx bx-check-double"></i> Bulk Actions
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#" onclick="bulkCreateLeaveFromRecommendations(); return false;"><i class="bx bx-plus-circle text-success"></i> Create Leave Requests</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkDeleteRecommendations(); return false;"><i class="bx bx-trash text-danger"></i> Delete Selected</a></li>
                                <li><a class="dropdown-item" href="#" onclick="bulkExportRecommendations(); return false;"><i class="bx bx-download text-info"></i> Export Selected</a></li>
                            </ul>
                        </div>
                    </div>

                    <!-- Recommendations Content -->
                    <div id="recommendations-content">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading recommendations...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recommendation Management Modal -->
<div class="modal fade" id="recommendationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="recommendationForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white">Add Leave Recommendation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="action" value="add" id="recommendation_action">
                    <input type="hidden" name="recommendation_id" id="recommendation_id" value="">
                    <input type="hidden" name="update_action" id="update_action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee *</label>
                            <select name="employee_id" id="recommendation_employee_id" class="form-select" required>
                                <option value="">-- Select Employee --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Financial Year *</label>
                            <select name="financial_year" id="recommendation_financial_year" class="form-select" required>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recommended Start Date *</label>
                            <input type="date" name="start_date" id="recommendation_start_date" class="form-control" required>
                            <small class="form-text text-muted">Must be within the selected financial year</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recommended End Date *</label>
                            <input type="date" name="end_date" id="recommendation_end_date" class="form-control" required>
                            <small class="form-text text-muted">Must be after start date</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes/Reason</label>
                        <textarea name="notes" id="recommendation_notes" class="form-control" rows="3" placeholder="Optional notes about this recommendation..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Recommendation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Global functions for recommendations management page - must be defined before document.ready
var csrfToken = '';

// Fallback if SweetAlert2 isn't loaded
if (typeof window.Swal === 'undefined') {
    window.Swal = {
        fire: function(optsOrTitle, text, icon) {
            if (typeof optsOrTitle === 'object') {
                const title = optsOrTitle.title || '';
                const html = optsOrTitle.html || optsOrTitle.text || '';
                alert(title + (html ? '\n\n' + html : ''));
                return Promise.resolve({ isConfirmed: true });
            } else {
                alert(optsOrTitle + (text ? '\n\n' + text : ''));
                return Promise.resolve({ isConfirmed: true });
            }
        },
        close: function() {},
        showLoading: function() {}
    };
}

// Show recommendation modal
function showRecommendationModal() {
    if (typeof $ !== 'undefined' && $('#recommendationModal').length) {
        $('#recommendationForm')[0].reset();
        $('#recommendation_id').val('');
        $('#recommendation_action').val('add');
        $('#recommendationModal .modal-title').text('Add Leave Recommendation');
        $('#recommendationModal').modal('show');
    }
}

// Load recommendations
function loadRecommendations() {
    const year = $('#recommendation-year-filter').val();
    const employeeId = $('#recommendation-employee-filter').val();
    const deptId = $('#recommendation-dept-filter').val();
    const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
    
    $('#recommendations-content').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>');
    
    $.post('{{ route("leave.hr.manage-recommendations") }}', {
        _token: token,
        action: 'list',
        financial_year: year,
        employee_id: employeeId,
        department_id: deptId
    }, function(response) {
        if (response.success) {
            let html = '';
            // The API returns 'data' not 'recommendations'
            const recommendations = response.data || response.recommendations || [];
            if (recommendations.length > 0) {
                html = '<div class="table-responsive"><table class="table table-hover table-bordered"><thead class="table-light"><tr><th width="40"><input type="checkbox" id="select-all-recommendations" onchange="toggleAllRecommendations(this)"></th><th>Employee</th><th>Department</th><th>Recommended Period</th><th>Days</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                recommendations.forEach(function(rec) {
                    // Check if this is an employee without recommendations
                    if (!rec.id || rec.has_recommendations === false) {
                        html += `
                            <tr class="table-secondary">
                                <td>
                                    <input type="checkbox" class="form-check-input recommendation-checkbox" value="emp_${rec.employee_id}" id="emp-${rec.employee_id}" disabled title="No recommendations to select">
                                </td>
                                <td><strong>${rec.employee_name || 'N/A'}</strong></td>
                                <td>${rec.department_name || 'N/A'}</td>
                                <td><span class="text-muted"><i class="bx bx-info-circle"></i> No recommendations</span></td>
                                <td><span class="badge bg-secondary">-</span></td>
                                <td><span class="badge bg-warning">No Recommendations</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="showRecommendationModalForEmployee(${rec.employee_id})" title="Add Recommendation">
                                        <i class="bx bx-plus"></i> Add
                                    </button>
                                </td>
                            </tr>
                        `;
                    } else {
                        // Employee has recommendations
                        const startDate = new Date(rec.recommended_start_date).toLocaleDateString();
                        const endDate = new Date(rec.recommended_end_date).toLocaleDateString();
                        const days = Math.ceil((new Date(rec.recommended_end_date) - new Date(rec.recommended_start_date)) / (1000 * 60 * 60 * 24)) + 1;
                        html += `
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input recommendation-checkbox" value="${rec.id}" id="rec-${rec.id}" onchange="updateSelectedRecommendationsCount()">
                                </td>
                                <td>${rec.employee_name || 'N/A'}</td>
                                <td>${rec.department_name || 'N/A'}</td>
                                <td>${startDate} - ${endDate}</td>
                                <td><span class="badge bg-info">${days} days</span></td>
                                <td><span class="badge bg-success">Active</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info me-1" onclick="viewRecommendation(${rec.id})" title="View Details">
                                        <i class="bx bx-show"></i> View
                                    </button>
                                    <button class="btn btn-sm btn-outline-success me-1" onclick="createLeaveFromRecommendation(${rec.id})" title="Create Leave Request">
                                        <i class="bx bx-plus-circle"></i> Create Leave
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editRecommendation(${rec.id})" title="Edit">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRecommendation(${rec.id})" title="Delete">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    }
                });
                html += '</tbody></table></div>';
            } else {
                html = '<div class="text-center py-4 text-muted"><i class="bx bx-info-circle me-2"></i>No employees found for the selected filters.</div>';
            }
            $('#recommendations-content').html(html);
        } else {
            $('#recommendations-content').html('<div class="text-center py-4 text-danger">Error loading recommendations. Please try again.</div>');
        }
    }).fail(function() {
        $('#recommendations-content').html('<div class="text-center py-4 text-danger">Failed to load recommendations. Please refresh the page.</div>');
    });
}

// Select/Deselect all recommendations
function selectAllRecommendations() {
    // Only select enabled checkboxes (actual recommendations, not placeholder entries)
    $('.recommendation-checkbox:not(:disabled)').prop('checked', true);
    updateSelectedRecommendationsCount();
}

function deselectAllRecommendations() {
    $('.recommendation-checkbox').prop('checked', false);
    updateSelectedRecommendationsCount();
}

function toggleAllRecommendations(checkbox) {
    // Only toggle enabled checkboxes (actual recommendations, not placeholder entries)
    $('.recommendation-checkbox:not(:disabled)').prop('checked', checkbox.checked);
    updateSelectedRecommendationsCount();
}

function updateSelectedRecommendationsCount() {
    // Only count checkboxes that are not disabled (actual recommendations, not placeholder entries)
    const count = $('.recommendation-checkbox:checked:not(:disabled)').length;
    $('#selected-recommendations-count').text(count + ' selected');
}

// Recommendation operations
function viewRecommendation(recId) {
    const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
    
    // Find the recommendation data from the table
    const row = $(`#rec-${recId}`).closest('tr');
    const employeeName = row.find('td').eq(1).text().trim();
    const departmentName = row.find('td').eq(2).text().trim();
    const period = row.find('td').eq(3).text().trim();
    const days = row.find('td').eq(4).text().trim();
    
    // Get full recommendation details via AJAX
    $.ajax({
        url: '{{ route("leave.hr.manage-recommendations") }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token
        },
        data: {
            _token: token,
            action: 'get',
            recommendation_id: recId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.recommendation) {
                const rec = response.recommendation;
                const startDate = new Date(rec.recommended_start_date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                const endDate = new Date(rec.recommended_end_date).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
                const createdDate = rec.created_at ? new Date(rec.created_at).toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                }) : 'N/A';
                
                Swal.fire({
                    title: 'Recommendation Details',
                    html: `
                        <div class="text-start">
                            <div class="mb-3">
                                <strong>Employee:</strong> ${rec.employee_name || employeeName}
                            </div>
                            <div class="mb-3">
                                <strong>Department:</strong> ${rec.department_name || departmentName}
                            </div>
                            <div class="mb-3">
                                <strong>Financial Year:</strong> ${rec.financial_year}
                            </div>
                            <div class="mb-3">
                                <strong>Recommended Period:</strong><br>
                                <span class="text-primary">${startDate}</span> to <span class="text-primary">${endDate}</span>
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong> <span class="badge bg-success">${rec.status || 'Active'}</span>
                            </div>
                            ${rec.notes ? `
                            <div class="mb-3">
                                <strong>Notes:</strong><br>
                                <div class="p-2 bg-light rounded">${rec.notes}</div>
                            </div>
                            ` : ''}
                            <div class="mb-3">
                                <strong>Created:</strong> ${createdDate}
                            </div>
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonText: 'Close',
                    width: '600px'
                });
            } else {
                Swal.fire('Error!', response.message || 'Failed to load recommendation details.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Failed to load recommendation details.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire('Error!', errorMessage, 'error');
        }
    });
}

function createLeaveFromRecommendation(recId) {
    const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
    
    Swal.fire({
        title: 'Create Leave Request?',
        html: `
            <p>This will create a leave request from this recommendation.</p>
            <p class="text-info"><strong>Note:</strong> The leave request will be created with status "Pending HR Review".</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Create Request',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Creating...',
                text: 'Please wait while we create the leave request.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route("leave.hr.bulk-operations") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    _token: token,
                    action: 'create_from_recommendations',
                    recommendation_ids: [recId]
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            html: `
                                <p>${response.message}</p>
                                <p class="text-success mt-2">The leave request has been created and is now pending HR review.</p>
                            `,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Optionally reload or redirect
                            window.location.href = '{{ route("modules.hr.leave") }}';
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to create leave request. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join(', ');
                    }
                    Swal.fire('Error!', errorMessage, 'error');
                }
            });
        }
    });
}

function showRecommendationModalForEmployee(employeeId) {
    if (typeof $ !== 'undefined' && $('#recommendationModal').length) {
        $('#recommendationForm')[0].reset();
        $('#recommendation_id').val('');
        $('#recommendation_action').val('add');
        $('#recommendation_employee_id').val(employeeId);
        $('#recommendationModal .modal-title').text('Add Leave Recommendation');
        $('#recommendationModal').modal('show');
    }
}

function editRecommendation(recId) {
    const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
    
    // Get recommendation details
    $.ajax({
        url: '{{ route("leave.hr.manage-recommendations") }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token
        },
        data: {
            _token: token,
            action: 'get',
            recommendation_id: recId
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.recommendation) {
                const rec = response.recommendation;
                
                // Populate the form with existing data
                if (typeof $ !== 'undefined' && $('#recommendationModal').length) {
                    $('#recommendationForm')[0].reset();
                    $('#recommendation_id').val(rec.id);
                    $('#recommendation_action').val('update');
                    $('#update_action').val('update');
                    $('#recommendation_employee_id').val(rec.employee_id);
                    $('#recommendation_financial_year').val(rec.financial_year);
                    $('#recommendation_start_date').val(rec.recommended_start_date);
                    $('#recommendation_end_date').val(rec.recommended_end_date);
                    $('#recommendation_notes').val(rec.notes || '');
                    
                    // Disable employee and year fields when editing
                    $('#recommendation_employee_id').prop('disabled', true);
                    $('#recommendation_financial_year').prop('disabled', true);
                    
                    $('#recommendationModal .modal-title').text('Edit Leave Recommendation');
                    $('#recommendationModal').modal('show');
                }
            } else {
                Swal.fire('Error!', response.message || 'Failed to load recommendation details.', 'error');
            }
        },
        error: function(xhr) {
            let errorMessage = 'Failed to load recommendation details.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire('Error!', errorMessage, 'error');
        }
    });
}

// Reset form when modal is closed or opened for adding
$('#recommendationModal').on('hidden.bs.modal', function() {
    $('#recommendationForm')[0].reset();
    $('#recommendation_id').val('');
    $('#recommendation_action').val('add');
    $('#update_action').val('add');
    $('#recommendation_employee_id').prop('disabled', false);
    $('#recommendation_financial_year').prop('disabled', false);
    $('#recommendationModal .modal-title').text('Add Leave Recommendation');
});

function showRecommendationModal() {
    if (typeof $ !== 'undefined' && $('#recommendationModal').length) {
        $('#recommendationForm')[0].reset();
        $('#recommendation_id').val('');
        $('#recommendation_action').val('add');
        $('#update_action').val('add');
        $('#recommendation_employee_id').prop('disabled', false);
        $('#recommendation_financial_year').prop('disabled', false);
        $('#recommendationModal .modal-title').text('Add Leave Recommendation');
        $('#recommendationModal').modal('show');
    }
}

function deleteRecommendation(recId) {
    Swal.fire({
        title: 'Are you sure?',
        text: 'This will delete the recommendation permanently.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
            $.post('{{ route("leave.hr.manage-recommendations") }}', {
                _token: token,
                action: 'delete',
                recommendation_id: recId
            }, function(response) {
                if (response.success) {
                    Swal.fire('Deleted!', response.message, 'success').then(() => loadRecommendations());
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            });
        }
    });
}

// Bulk operations
function autoAssignRecommendations() {
    const year = $('#recommendation-year-filter').val();
    
    Swal.fire({
        title: 'Auto-Assign Recommendations?',
        html: `
            <div class="text-start">
                <p class="mb-3">This will automatically assign leave recommendations for <strong>all active employees</strong> for the year <strong>${year}</strong>.</p>
                <div class="alert alert-info mb-3">
                    <strong>Rules:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Maximum 2 staff per department can be on leave at the same time</li>
                        <li>Maximum 3 recommendations per employee per year</li>
                        <li>Uses optimal leave periods based on workload and availability</li>
                    </ul>
                </div>
                <p class="text-warning mb-0"><strong>Note:</strong> This may take a few moments to process all employees.</p>
            </div>
        `,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Yes, Auto-Assign',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
            
            Swal.fire({
                title: 'Auto-Assigning...',
                html: '<p>Please wait while we calculate and assign optimal leave periods for all employees.</p><p class="text-muted small">This may take a few moments...</p>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route("leave.hr.manage-recommendations") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    _token: token,
                    action: 'auto_assign',
                    financial_year: year
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Success!',
                            html: `
                                <p>${response.message}</p>
                                ${response.created ? `<p class="text-success"><strong>Created:</strong> ${response.created} recommendation(s)</p>` : ''}
                                ${response.skipped ? `<p class="text-warning"><strong>Skipped:</strong> ${response.skipped} recommendation(s)</p>` : ''}
                            `,
                            icon: 'success'
                        }).then(() => {
                            loadRecommendations();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to auto-assign recommendations. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', errorMessage, 'error');
                }
            });
        }
    });
}

function bulkCreateLeaveFromRecommendations() {
    // Only get enabled checkboxes (actual recommendations, not placeholder entries)
    const selected = $('.recommendation-checkbox:checked:not(:disabled)');
    
    if (selected.length === 0) {
        Swal.fire('No Selection', 'Please select at least one recommendation to create leave requests from.', 'warning');
        return;
    }
    
    const selectedCount = selected.length;
    
    Swal.fire({
        title: 'Create Leave Requests?',
        html: `
            <p>You are about to create leave requests from <strong>${selectedCount}</strong> recommendation(s).</p>
            <p class="text-info"><strong>Note:</strong> Leave requests will be created with status "Pending HR Review".</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Create Requests',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            const recommendationIds = [];
            selected.each(function() {
                recommendationIds.push($(this).val());
            });
            
            const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
            
            Swal.fire({
                title: 'Creating...',
                text: 'Please wait while we create the leave requests.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route("leave.hr.bulk-operations") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    _token: token,
                    action: 'create_from_recommendations',
                    recommendation_ids: recommendationIds
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            loadRecommendations();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to create leave requests. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', errorMessage, 'error');
                }
            });
        }
    });
}

function bulkDeleteRecommendations() {
    // Only get enabled checkboxes (actual recommendations, not placeholder entries)
    const selected = $('.recommendation-checkbox:checked:not(:disabled)');
    
    if (selected.length === 0) {
        Swal.fire('No Selection', 'Please select at least one recommendation to delete.', 'warning');
        return;
    }
    
    const selectedCount = selected.length;
    
    Swal.fire({
        title: 'Delete Recommendations?',
        html: `
            <p>You are about to delete <strong>${selectedCount}</strong> recommendation(s).</p>
            <p class="text-warning"><strong>Warning:</strong> This action cannot be undone.</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete Selected',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            const recommendationIds = [];
            selected.each(function() {
                recommendationIds.push($(this).val());
            });
            
            const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
            
            Swal.fire({
                title: 'Deleting...',
                text: 'Please wait while we delete the recommendations.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '{{ route("leave.hr.manage-recommendations") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                data: {
                    _token: token,
                    action: 'bulk_remove',
                    recommendation_ids: recommendationIds
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            loadRecommendations();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to delete recommendations. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    Swal.fire('Error!', errorMessage, 'error');
                }
            });
        }
    });
}

function bulkExportRecommendations() {
    // Only get enabled checkboxes (actual recommendations, not placeholder entries)
    const selected = $('.recommendation-checkbox:checked:not(:disabled)');
    
    if (selected.length === 0) {
        Swal.fire('No Selection', 'Please select at least one recommendation to export.', 'warning');
        return;
    }
    
    Swal.fire('Info', 'Export functionality will be implemented soon. This will export the selected recommendations to Excel/CSV.', 'info');
}

// Recommendation form submission
$(document).ready(function() {
    // Get CSRF token
    csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    $('#recommendationForm').on('submit', function(e) {
        e.preventDefault();
        
        const token = csrfToken || $('meta[name="csrf-token"]').attr('content');
        const action = $('#recommendation_action').val();
        const updateAction = $('#update_action').val();
        
        // Determine if this is an update or add
        const finalAction = (action === 'update' || updateAction === 'update') ? 'update' : 'add';
        
        // Build form data manually to include disabled fields
        const formData = {
            _token: token,
            action: finalAction,
            recommendation_id: $('#recommendation_id').val(),
            employee_id: $('#recommendation_employee_id').val(),
            financial_year: $('#recommendation_financial_year').val(),
            start_date: $('#recommendation_start_date').val(),
            end_date: $('#recommendation_end_date').val(),
            notes: $('#recommendation_notes').val()
        };
        
        $.ajax({
            url: '{{ route("leave.hr.manage-recommendations") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token
            },
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success');
                    $('#recommendationModal').modal('hide');
                    loadRecommendations();
                } else {
                    Swal.fire('Error!', response.message || 'Failed to save recommendation.', 'error');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to save recommendation. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                }
                Swal.fire('Error!', errorMessage, 'error');
            }
        });
    });
    
    // Load recommendations on page load
    loadRecommendations();
});
</script>
@endpush

