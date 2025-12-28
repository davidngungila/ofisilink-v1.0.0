@extends('layouts.app')

@section('title', 'Activity Log - Admin')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Activity Log</h4>
</div>
@endsection

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-12 mb-4">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="bx bx-calendar"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Today</h6>
                                <h4 class="mb-0" id="todayCount">-</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-info">
                                    <i class="bx bx-time"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">This Week</h6>
                                <h4 class="mb-0" id="weekCount">-</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="bx bx-calendar-check"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">This Month</h6>
                                <h4 class="mb-0" id="monthCount">-</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-3">
                                <span class="avatar-initial rounded bg-label-warning">
                                    <i class="bx bx-data"></i>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0 text-muted">Total</h6>
                                <h4 class="mb-0" id="totalCount">-</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="col-lg-12 mb-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0"><i class="bx bx-filter"></i> Filter Activity Log</h5>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFilters">
                    <i class="bx bx-x"></i> Clear All
                </button>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Action Type</label>
                            <select name="action" class="form-select">
                                <option value="">All Actions</option>
                                <optgroup label="Basic Operations">
                                    <option value="created">Created</option>
                                    <option value="updated">Updated</option>
                                    <option value="deleted">Deleted</option>
                                    <option value="viewed">Viewed</option>
                                </optgroup>
                                <optgroup label="Authentication">
                                    <option value="login">Login</option>
                                    <option value="logout">Logout</option>
                                    <option value="password_reset">Password Reset</option>
                                </optgroup>
                                <optgroup label="Approval Actions">
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="status_changed">Status Changed</option>
                                </optgroup>
                                <optgroup label="Notifications">
                                    <option value="sms_sent">SMS Sent</option>
                                    <option value="notification_sent">Notification Sent</option>
                                    <option value="email_sent">Email Sent</option>
                                </optgroup>
                                <optgroup label="File Operations">
                                    <option value="file_uploaded">File Uploaded</option>
                                    <option value="file_downloaded">File Downloaded</option>
                                    <option value="file_deleted">File Deleted</option>
                                </optgroup>
                                <optgroup label="Financial">
                                    <option value="payment_processed">Payment Processed</option>
                                </optgroup>
                                <optgroup label="Bulk Operations">
                                    <option value="bulk_approved">Bulk Approved</option>
                                    <option value="bulk_rejected">Bulk Rejected</option>
                                    <option value="bulk_cancelled">Bulk Cancelled</option>
                                </optgroup>
                                <optgroup label="Data Operations">
                                    <option value="exported">Exported</option>
                                    <option value="imported">Imported</option>
                                </optgroup>
                                <optgroup label="System">
                                    <option value="config_changed">Config Changed</option>
                                    <option value="role_changed">Role Changed</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Model Type</label>
                            <input type="text" name="model" class="form-control" placeholder="e.g., User, PettyCash...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <div class="input-group input-group-merge">
                                <input type="date" name="date_from" class="form-control" placeholder="From">
                                <span class="input-group-text">to</span>
                                <input type="date" name="date_to" class="form-control" placeholder="To">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary" id="clearFiltersBtn">
                                <i class="bx bx-x"></i> Clear
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search"></i> Apply Filters
                            </button>
                            <a href="{{ route('activity-log.export') }}" class="btn btn-success" id="exportBtn">
                                <i class="bx bx-download"></i> Export CSV
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Activity Log Table -->
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0"><i class="bx bx-list-ul"></i> Activity Log</h5>
                    <p class="text-muted mb-0">System activities and user actions audit trail</p>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="refreshLog">
                    <i class="bx bx-refresh"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="activityTable">
                        <thead class="table-light">
                            <tr>
                                <th width="12%">Date & Time</th>
                                <th width="15%">User</th>
                                <th width="10%">Action</th>
                                <th width="12%">Model</th>
                                <th width="25%">Description</th>
                                <th width="10%">IP Address</th>
                                <th width="16%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="activityTableBody">
                            @forelse($activities as $activity)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ \Carbon\Carbon::parse($activity->created_at)->format('M d, Y') }}</strong>
                                        <br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($activity->created_at)->format('H:i:s') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($activity->user_name ?? 'S', 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 small">{{ $activity->user_name ?? 'System' }}</h6>
                                            <small class="text-muted">{{ $activity->user_email ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $action = strtolower($activity->action ?? 'unknown');
                                        $actionClasses = [
                                            'created' => 'success',
                                            'updated' => 'info',
                                            'deleted' => 'danger',
                                            'viewed' => 'warning',
                                            'login' => 'primary',
                                            'logout' => 'secondary',
                                            'password_reset' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'cancelled' => 'warning',
                                            'status_changed' => 'info',
                                            'sms_sent' => 'primary',
                                            'notification_sent' => 'info',
                                            'email_sent' => 'success',
                                            'file_uploaded' => 'success',
                                            'file_downloaded' => 'info',
                                            'file_deleted' => 'danger',
                                            'payment_processed' => 'success',
                                            'exported' => 'info',
                                            'imported' => 'success',
                                            'config_changed' => 'warning',
                                            'role_changed' => 'info',
                                            'comment_added' => 'info',
                                            'assigned' => 'success',
                                            'unassigned' => 'warning',
                                        ];
                                        
                                        // Check for partial matches
                                        $badgeClass = 'secondary';
                                        foreach ($actionClasses as $key => $class) {
                                            if (str_contains($action, $key)) {
                                                $badgeClass = $class;
                                                break;
                                            }
                                        }
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">
                                        {{ ucfirst($activity->action ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">
                                        {{ $activity->model_type ? class_basename($activity->model_type) : 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 250px;" title="{{ $activity->description ?? 'No description' }}">
                                        {{ $activity->description ?? 'No description' }}
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted font-monospace">{{ $activity->ip_address ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-info view-details-btn" 
                                            data-activity-id="{{ $activity->id }}">
                                        <i class="bx bx-show"></i> View More
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bx bx-info-circle"></i> No activity logs found
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Load More Button -->
                <div class="d-flex justify-content-center mt-4" id="loadMoreContainer">
                    @if($activities->hasMorePages())
                    <button type="button" class="btn btn-primary" id="loadMoreBtn">
                        <i class="bx bx-down-arrow-alt"></i> Load More ({{ $activities->total() - $activities->count() }} remaining)
                    </button>
                    @elseif($activities->total() > 0)
                    <div class="alert alert-info mb-0">
                        <i class="bx bx-check-circle"></i> All {{ $activities->total() }} activity logs loaded
                    </div>
                    @endif
                </div>

                <!-- Pagination Info -->
                @if($activities->total() > 0)
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} entries
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Activity Details Modal -->
<div class="modal fade" id="activityDetailsModal" tabindex="-1" aria-labelledby="activityDetailsModalLabel">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityDetailsModalLabel">
                    <i class="bx bx-info-circle"></i> Activity Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="activityDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentPage = {{ $activities->currentPage() }};
let hasMorePages = {{ $activities->hasMorePages() ? 'true' : 'false' }};
let isLoading = false;
let filterParams = {};

$(document).ready(function() {
    // Load statistics on page load
    loadStatistics();

    // Filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        filterParams = $(this).serialize();
        currentPage = 1;
        loadActivityData(true);
    });

    // Clear filters
    $('#clearFilters, #clearFiltersBtn').on('click', function() {
        $('#filterForm')[0].reset();
        filterParams = {};
        currentPage = 1;
        loadActivityData(true);
    });

    // Refresh log
    $('#refreshLog').on('click', function() {
        loadActivityData(true);
        loadStatistics();
    });

    // Load more button
    $('#loadMoreBtn').on('click', function() {
        if (!isLoading && hasMorePages) {
            currentPage++;
            loadActivityData(false);
        }
    });

    // View details button
    $(document).on('click', '.view-details-btn', function() {
        const activityId = $(this).data('activity-id');
        showActivityDetails(activityId);
    });

    // Export button with filters
    $('#exportBtn').on('click', function(e) {
        e.preventDefault();
        const formData = $('#filterForm').serialize();
        window.open('{{ route("activity-log.export") }}?' + formData, '_blank');
    });

    function loadStatistics() {
        $.ajax({
            url: '{{ route("activity-log.statistics") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#todayCount').text(response.stats.today.toLocaleString());
                    $('#weekCount').text(response.stats.week.toLocaleString());
                    $('#monthCount').text(response.stats.month.toLocaleString());
                    $('#totalCount').text(response.stats.total.toLocaleString());
                }
            },
            error: function() {
                console.error('Failed to load statistics');
            }
        });
    }

    function loadActivityData(reset = false) {
        if (isLoading) return;
        
        isLoading = true;
        const btn = $('#loadMoreBtn');
        if (btn.length) {
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Loading...');
        }

        $.ajax({
            url: '{{ route("activity-log.data") }}',
            method: 'GET',
            data: {
                ...filterParams,
                page: currentPage
            },
            success: function(response) {
                if (response.success) {
                    hasMorePages = response.activities.current_page < response.activities.last_page;
                    
                    if (reset) {
                        updateActivityTable(response.activities.data, true);
                    } else {
                        appendActivityTable(response.activities.data);
                    }
                    
                    updateLoadMoreButton(response.activities);
                }
            },
            error: function(xhr) {
                console.error('Error loading activities:', xhr);
                Swal.fire('Error', 'Failed to load activity data', 'error');
            },
            complete: function() {
                isLoading = false;
                if (btn.length) {
                    btn.prop('disabled', false).html('<i class="bx bx-down-arrow-alt"></i> Load More');
                }
            }
        });
    }

    function updateActivityTable(activities, reset = false) {
        const tbody = $('#activityTableBody');
        if (reset) {
            tbody.empty();
        }

        if (activities.length === 0 && reset) {
            tbody.append('<tr><td colspan="7" class="text-center py-4"><div class="text-muted"><i class="bx bx-info-circle"></i> No activities found</div></td></tr>');
            return;
        }

        activities.forEach(function(activity) {
            const row = createActivityRow(activity);
            tbody.append(row);
        });
    }

    function appendActivityTable(activities) {
        const tbody = $('#activityTableBody');
        activities.forEach(function(activity) {
            const row = createActivityRow(activity);
            tbody.append(row);
        });
    }

    function createActivityRow(activity) {
        const date = new Date(activity.created_at);
        const actionClass = getActionBadgeClass(activity.action || 'unknown');
        const modelName = activity.model_type ? activity.model_type.split('\\').pop() : 'N/A';
        const userInitials = (activity.user_name || 'S').substring(0, 2).toUpperCase();
        const description = activity.description || 'No description';
        const truncatedDesc = description.length > 50 ? description.substring(0, 50) + '...' : description;

        return `
            <tr>
                <td>
                    <div>
                        <strong>${formatDate(activity.created_at)}</strong>
                        <br>
                        <small class="text-muted">${formatTime(activity.created_at)}</small>
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm me-2">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                ${userInitials}
                            </span>
                        </div>
                        <div>
                            <h6 class="mb-0 small">${activity.user_name || 'System'}</h6>
                            <small class="text-muted">${activity.user_email || 'N/A'}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-${actionClass}">
                        ${(activity.action || 'Unknown').charAt(0).toUpperCase() + (activity.action || 'Unknown').slice(1)}
                    </span>
                </td>
                <td>
                    <span class="badge bg-label-info">${modelName}</span>
                </td>
                <td>
                    <div class="text-truncate" style="max-width: 250px;" title="${description}">
                        ${truncatedDesc}
                    </div>
                </td>
                <td>
                    <small class="text-muted font-monospace">${activity.ip_address || 'N/A'}</small>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-info view-details-btn" 
                            data-activity-id="${activity.id}">
                        <i class="bx bx-show"></i> View More
                    </button>
                </td>
            </tr>
        `;
    }

    function updateLoadMoreButton(pagination) {
        const container = $('#loadMoreContainer');
        const remaining = pagination.total - (pagination.current_page * pagination.per_page);
        
        if (hasMorePages) {
            container.html(`
                <button type="button" class="btn btn-primary" id="loadMoreBtn">
                    <i class="bx bx-down-arrow-alt"></i> Load More (${Math.max(0, remaining)} remaining)
                </button>
            `);
            $('#loadMoreBtn').on('click', function() {
                if (!isLoading && hasMorePages) {
                    currentPage++;
                    loadActivityData(false);
                }
            });
        } else if (pagination.total > 0) {
            container.html(`
                <div class="alert alert-info mb-0">
                    <i class="bx bx-check-circle"></i> All ${pagination.total} activity logs loaded
                </div>
            `);
        }
    }

    function showActivityDetails(activityId) {
        const modal = $('#activityDetailsModal');
        const content = $('#activityDetailsContent');
        
        content.html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        
        modal.modal('show');

        $.ajax({
            url: '{{ route("activity-log.data") }}',
            method: 'GET',
            data: {
                activity_id: activityId
            },
            success: function(response) {
                if (response.success && response.activities && response.activities.data) {
                    const activity = response.activities.data[0];
                    let propertiesData = null;
                    try {
                        if (activity.properties) {
                            if (typeof activity.properties === 'string') {
                                propertiesData = JSON.parse(activity.properties);
                            } else {
                                propertiesData = activity.properties;
                            }
                        }
                    } catch(e) {
                        propertiesData = activity.properties;
                    }
                    
                    const detailsHtml = `
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Activity ID</label>
                                <div class="form-control-plaintext font-monospace">
                                    #${activity.id || 'N/A'}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">User ID</label>
                                <div class="form-control-plaintext font-monospace">
                                    ${activity.user_id || 'System'}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date & Time (Created)</label>
                                <div class="form-control-plaintext">
                                    ${formatDate(activity.created_at)} at ${formatTime(activity.created_at)}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Last Updated</label>
                                <div class="form-control-plaintext">
                                    ${activity.updated_at ? (formatDate(activity.updated_at) + ' at ' + formatTime(activity.updated_at)) : 'N/A'}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">User</label>
                                <div class="form-control-plaintext">
                                    ${activity.user_name || 'System'} <br>
                                    <small class="text-muted">${activity.user_email || 'N/A'}</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Action</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-${getActionBadgeClass(activity.action || 'unknown')}">
                                        ${(activity.action || 'Unknown').charAt(0).toUpperCase() + (activity.action || 'Unknown').slice(1)}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Model Type</label>
                                <div class="form-control-plaintext">
                                    <code>${activity.model_type || 'N/A'}</code>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Model ID</label>
                                <div class="form-control-plaintext">
                                    ${activity.model_id || 'N/A'}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">IP Address</label>
                                <div class="form-control-plaintext font-monospace">
                                    ${activity.ip_address || 'N/A'}
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">User Agent</label>
                                <div class="form-control-plaintext small">
                                    ${activity.user_agent || 'N/A'}
                                </div>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <div class="form-control-plaintext">
                                    ${activity.description || 'No description available'}
                                </div>
                            </div>
                            ${propertiesData ? `
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Properties / Changes</label>
                                <div class="card">
                                    <div class="card-body">
                                        ${propertiesData.changes ? `
                                        <div class="mb-3">
                                            <h6 class="fw-bold text-primary"><i class="bx bx-edit"></i> Field Changes</h6>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Field</th>
                                                            <th>Old Value</th>
                                                            <th>New Value</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        ${Object.keys(propertiesData.changes.new || {}).map(key => `
                                                            <tr>
                                                                <td><strong>${key}</strong></td>
                                                                <td><span class="text-danger">${propertiesData.changes.old && propertiesData.changes.old[key] !== undefined ? (typeof propertiesData.changes.old[key] === 'object' ? JSON.stringify(propertiesData.changes.old[key]) : propertiesData.changes.old[key]) : 'N/A'}</span></td>
                                                                <td><span class="text-success">${propertiesData.changes.new && propertiesData.changes.new[key] !== undefined ? (typeof propertiesData.changes.new[key] === 'object' ? JSON.stringify(propertiesData.changes.new[key]) : propertiesData.changes.new[key]) : 'N/A'}</span></td>
                                                            </tr>
                                                        `).join('')}
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        ` : ''}
                                        ${propertiesData.request_data ? `
                                        <div class="mb-3">
                                            <h6 class="fw-bold text-info"><i class="bx bx-data"></i> Request Data</h6>
                                            <pre class="bg-light p-3 rounded border" style="max-height: 200px; overflow-y: auto; font-size: 11px;">${JSON.stringify(propertiesData.request_data, null, 2)}</pre>
                                        </div>
                                        ` : ''}
                                        <div class="mb-0">
                                            <h6 class="fw-bold text-secondary"><i class="bx bx-info-circle"></i> Full Details</h6>
                                            <pre class="bg-light p-3 rounded border" style="max-height: 300px; overflow-y: auto; font-size: 11px;">${JSON.stringify(propertiesData, null, 2)}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ` : '<div class="col-12 mb-3"><div class="alert alert-info mb-0"><i class="bx bx-info-circle"></i> No properties/changes recorded for this activity</div></div>'}
                        </div>
                    `;
                    content.html(detailsHtml);
                } else {
                    content.html('<div class="alert alert-danger">Activity details not found</div>');
                }
            },
            error: function() {
                content.html('<div class="alert alert-danger">Error loading activity details</div>');
            }
        });
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    function getActionBadgeClass(action) {
        const actionLower = action.toLowerCase();
        const actionClasses = {
            'created': 'success',
            'updated': 'info',
            'deleted': 'danger',
            'viewed': 'warning',
            'login': 'primary',
            'logout': 'secondary',
            'password_reset': 'warning',
            'approved': 'success',
            'rejected': 'danger',
            'cancelled': 'warning',
            'status_changed': 'info',
            'sms_sent': 'primary',
            'notification_sent': 'info',
            'email_sent': 'success',
            'file_uploaded': 'success',
            'file_downloaded': 'info',
            'file_deleted': 'danger',
            'payment_processed': 'success',
            'exported': 'info',
            'imported': 'success',
            'config_changed': 'warning',
            'role_changed': 'info',
            'comment_added': 'info',
            'assigned': 'success',
            'unassigned': 'warning',
        };
        
        // Check for exact match first
        if (actionClasses[actionLower]) {
            return actionClasses[actionLower];
        }
        
        // Check for partial matches
        for (const key in actionClasses) {
            if (actionLower.includes(key)) {
                return actionClasses[key];
            }
        }
        
        return 'secondary';
    }
});
</script>
@endpush
