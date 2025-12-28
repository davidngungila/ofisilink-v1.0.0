@extends('layouts.app')

@section('title', 'Export Incidents')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-export me-2"></i>Export Incidents
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Export incidents data with custom filters and date ranges
                            </p>
                        </div>
                        <div>
                            <a href="{{ route('modules.incidents.dashboard') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Form -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-filter me-2"></i>Export Filters
                    </h5>
                </div>
                <div class="card-body">
                    <form id="export-form" method="POST" action="{{ route('modules.incidents.export.download') }}">
                        @csrf
                        
                        <!-- Status Filter -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="New">New</option>
                                    <option value="Assigned">Assigned</option>
                                    <option value="In Progress">In Progress</option>
                                    <option value="Resolved">Resolved</option>
                                    <option value="Closed">Closed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="">All Priorities</option>
                                    <option value="Low">Low</option>
                                    <option value="Medium">Medium</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <option value="technical">Technical</option>
                                    <option value="hr">HR</option>
                                    <option value="facilities">Facilities</option>
                                    <option value="security">Security</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Date Range -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to">
                            </div>
                        </div>

                        <!-- Export Options -->
                        <div class="mb-4">
                            <h6 class="mb-3 fw-bold">
                                <i class="bx bx-cog me-2"></i>Export Options
                            </h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="include_resolved" name="include_resolved" checked>
                                <label class="form-check-label" for="include_resolved">
                                    Include Resolved Incidents
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="include_closed" name="include_closed" checked>
                                <label class="form-check-label" for="include_closed">
                                    Include Closed Incidents
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="include_notes" name="include_notes" checked>
                                <label class="form-check-label" for="include_notes">
                                    Include Internal Notes
                                </label>
                            </div>
                        </div>

                        <!-- Export Button -->
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('modules.incidents.dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-x me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="export-btn">
                                <i class="bx bx-download me-2"></i>Export to CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Export History (Optional) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-history me-2"></i>Export Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Export Format:</strong> CSV (Comma Separated Values)<br>
                        <strong>Columns Included:</strong> Incident #, Title, Description, Priority, Status, Category, Reporter Information, Assigned To, Dates, Resolution Notes<br>
                        <strong>File Size:</strong> Varies based on selected filters and date range
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Set default date range (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(today.getDate() - 30);
    
    $('#date_from').val(thirtyDaysAgo.toISOString().split('T')[0]);
    $('#date_to').val(today.toISOString().split('T')[0]);
    
    // Form submission
    $('#export-form').on('submit', function(e) {
        $('#export-btn').prop('disabled', true).html('<i class="bx bx-loader bx-spin me-2"></i>Exporting...');
    });
});
</script>
@endpush
@endsection










