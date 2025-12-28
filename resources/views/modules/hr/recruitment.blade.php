@extends('layouts.app')

@section('title', 'Recruitment & Selection - HR')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bx bx-briefcase me-2"></i>Recruitment & Selection</h2>
        <div>
            @if($canCreateJobs)
                <button class="btn btn-primary" id="create-job-btn">
                    <i class="bx bx-plus me-1"></i> New Job Vacancy
                </button>
            @endif
        </div>
    </div>

    @if($canManageApplications && !empty($stats))
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Active Vacancies</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_vacancies'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-folder-open fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Applications</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_applications'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-user fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if($canApproveJobs)
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Approval</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_approval'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-time fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if($canEditPendingJobs)
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow-sm h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">My Pending Jobs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['my_pending_jobs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-edit fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <ul class="nav nav-tabs" id="jobsTab" role="tablist">
        @if($canEditPendingJobs && $myPendingJobs->isNotEmpty())
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="my-pending-tab" data-bs-toggle="tab" data-bs-target="#my-pending" type="button" role="tab">
                <i class="bx bx-edit me-1"></i> My Pending Jobs
            </button>
        </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ (!$canEditPendingJobs || $myPendingJobs->isEmpty()) ? 'active' : '' }}" id="all-jobs-tab" data-bs-toggle="tab" data-bs-target="#all-jobs" type="button" role="tab">
                <i class="bx bx-briefcase me-1"></i> All Jobs
            </button>
        </li>
        @if($canManageApplications)
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab">
                <i class="bx bx-bar-chart me-1"></i> Analytics
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="interviews-tab" data-bs-toggle="tab" data-bs-target="#interviews" type="button" role="tab">
                <i class="bx bx-calendar me-1"></i> Interview Schedules
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bulk-ops-tab" data-bs-toggle="tab" data-bs-target="#bulk-ops" type="button" role="tab">
                <i class="bx bx-list-check me-1"></i> Bulk Operations
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                <i class="bx bx-history me-1"></i> Application History
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content" id="jobsTabContent">
        @if($canEditPendingJobs && $myPendingJobs->isNotEmpty())
        <div class="tab-pane fade show active" id="my-pending" role="tabpanel">
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-edit me-2"></i>My Jobs Pending Approval (Editable)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Date Created</th>
                                    <th>Deadline</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($myPendingJobs as $job)
                                <tr>
                                    <td>{{ $job->job_title }}</td>
                                    <td>{{ $job->created_at->format('M j, Y') }}</td>
                                    <td>{{ $job->application_deadline->format('M j, Y') }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="{{ $job->id }}" title="View Full Details">
                                            <i class="bx bx-show me-1"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning btn-edit-job" data-id="{{ $job->id }}" title="Edit Job">
                                            <i class="bx bx-edit me-1"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="tab-pane fade {{ (!$canEditPendingJobs || $myPendingJobs->isEmpty()) ? 'show active' : '' }}" id="all-jobs" role="tabpanel">
            @if($canApproveJobs && $pendingApprovalJobs->isNotEmpty())
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-error-circle me-2"></i>Vacancies Pending Your Approval
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Created By</th>
                                    <th>Date Created</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingApprovalJobs as $job)
                                <tr>
                                    <td>{{ $job->job_title }}</td>
                                    <td>{{ $job->creator->name ?? 'N/A' }}</td>
                                    <td>{{ $job->created_at->format('M j, Y') }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary btn-review" data-id="{{ $job->id }}">
                                            <i class="bx bx-show me-1"></i> Review Details
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <div class="card shadow-sm mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Job Vacancies</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="jobsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Status</th>
                                    <th>Deadline</th>
                                    <th>Applications</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobs as $job)
                                    @if($job->status !== 'Pending Approval')
                                    <tr>
                                        <td>{{ $job->job_title }}</td>
                                        <td>
                                            @php
                                                $statusMap = [
                                                    'Active' => 'success',
                                                    'Rejected' => 'danger',
                                                    'Closed' => 'secondary'
                                                ];
                                                $statusClass = $statusMap[$job->status] ?? 'light';
                                            @endphp
                                            <span class="badge bg-{{ $statusClass }}">{{ $job->status }}</span>
                                        </td>
                                        <td>{{ $job->application_deadline->format('M j, Y') }}</td>
                                        <td>
                                            <span class="badge bg-primary rounded-pill">{{ $job->applications_count }}</span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="{{ $job->id }}" title="View Full Details">
                                                <i class="bx bx-show"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success btn-view-applications" data-id="{{ $job->id }}" title="View Applications">
                                                <i class="bx bx-user"></i>
                                            </button>
                                            @if($canCreateJobs && $job->status === 'Active')
                                            <button class="btn btn-sm btn-outline-secondary btn-close-job" data-id="{{ $job->id }}" title="Manually Close">
                                                <i class="bx bx-x"></i>
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
        </div>

        @if($canManageApplications)
        <!-- Analytics Tab -->
        <div class="tab-pane fade" id="analytics" role="tabpanel">
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-primary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="bx bx-bar-chart me-2"></i>Recruitment Analytics Dashboard
                        </h6>
                        <div class="d-flex gap-2">
                            <input type="date" id="analyticsDateFrom" class="form-control form-control-sm" style="width: 150px;">
                            <input type="date" id="analyticsDateTo" class="form-control form-control-sm" style="width: 150px;">
                            <button class="btn btn-sm btn-light" id="refreshAnalytics">
                                <i class="bx bx-refresh me-1"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="analyticsContent">
                        <div class="text-center p-5">
                            <i class="bx bx-loader-alt bx-spin bx-lg"></i>
                            <p>Loading Analytics...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interview Schedules Tab -->
        <div class="tab-pane fade" id="interviews" role="tabpanel">
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-info text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="bx bx-calendar me-2"></i>Interview Schedules
                        </h6>
                        <select id="interviewFilter" class="form-select form-select-sm" style="width: 200px;">
                            <option value="all">All Interviews</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div id="interviewsContent">
                        <div class="text-center p-5">
                            <i class="bx bx-loader-alt bx-spin bx-lg"></i>
                            <p>Loading Interviews...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Operations Tab -->
        <div class="tab-pane fade" id="bulk-ops" role="tabpanel">
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="bx bx-list-check me-2"></i>Bulk Operations
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bx bx-user-check me-2"></i>Bulk Application Status Update</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Select multiple applications and update their status in bulk.</p>
                                    <div class="mb-3">
                                        <label class="form-label">Select Job</label>
                                        <select id="bulkJobSelect" class="form-select">
                                            <option value="">All Jobs</option>
                                            @foreach($jobs->where('status', 'Active') as $job)
                                            <option value="{{ $job->id }}">{{ $job->job_title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button class="btn btn-primary" id="loadBulkApplications">
                                        <i class="bx bx-refresh me-1"></i> Load Applications
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0"><i class="bx bx-trash me-2"></i>Bulk Delete</h6>
                                </div>
                                <div class="card-body">
                                    <p class="text-muted">Delete multiple applications at once. This action cannot be undone.</p>
                                    <div class="alert alert-warning">
                                        <i class="bx bx-error-circle me-2"></i>
                                        <strong>Warning:</strong> This will permanently delete selected applications and their documents.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="bulkOperationsContent" class="mt-4">
                        <div class="alert alert-info">
                            <i class="bx bx-info-circle me-2"></i>
                            Please select a job and click "Load Applications" to begin bulk operations.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application History Tab -->
        <div class="tab-pane fade" id="history" role="tabpanel">
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">
                            <i class="bx bx-history me-2"></i>Application History Timeline
                        </h6>
                        <div class="d-flex gap-2">
                            <input type="text" id="historySearch" class="form-control form-control-sm" placeholder="Search by name or email..." style="width: 250px;">
                            <select id="historyStatusFilter" class="form-select form-select-sm" style="width: 150px;">
                                <option value="">All Statuses</option>
                                <option value="Applied">Applied</option>
                                <option value="Shortlisted">Shortlisted</option>
                                <option value="Interviewing">Interviewing</option>
                                <option value="Offer Extended">Offer Extended</option>
                                <option value="Hired">Hired</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="historyContent">
                        <div class="text-center p-5">
                            <i class="bx bx-loader-alt bx-spin bx-lg"></i>
                            <p>Loading History...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Create/Edit Job Modal -->
<div class="modal fade" id="jobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="jobForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="jobModalTitle">New Job Vacancy</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="action" id="jobAction" value="create_job">
                    <input type="hidden" name="job_id" id="jobId" value="">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Job Title *</label>
                            <input type="text" name="job_title" class="form-control" required maxlength="255">
                            <div class="form-text text-muted">Enter a clear and descriptive job title</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Application Deadline *</label>
                            <input type="date" name="application_deadline" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            <div class="form-text text-muted">Must be a future date</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Job Description *</label>
                            <textarea name="job_description" class="form-control" rows="5" required maxlength="2000"></textarea>
                            <div class="form-text text-muted">Describe the role, responsibilities, and expectations</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Qualifications / Requirements *</label>
                            <textarea name="qualifications" class="form-control" rows="5" required maxlength="2000"></textarea>
                            <div class="form-text text-muted">List required education, experience, and skills</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Interview Mode(s) *</label>
                            <div class="border rounded p-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="interview_mode[]" value="Written" id="mode_written">
                                    <label class="form-check-label" for="mode_written">Written Test</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="interview_mode[]" value="Oral" id="mode_oral">
                                    <label class="form-check-label" for="mode_oral">Oral Interview</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="interview_mode[]" value="Practical" id="mode_practical">
                                    <label class="form-check-label" for="mode_practical">Practical Assessment</label>
                                </div>
                            </div>
                            <div class="form-text text-muted">Select at least one interview mode</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Required Attachments</label>
                            <div class="border rounded p-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="required_attachments[]" value="Letter of Application" id="attach_letter">
                                    <label class="form-check-label" for="attach_letter">Letter of Application</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="required_attachments[]" value="Resume / CV" id="attach_cv">
                                    <label class="form-check-label" for="attach_cv">Resume / CV</label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" name="required_attachments[]" value="Certified Certificates" id="attach_certs">
                                    <label class="form-check-label" for="attach_certs">Certified Certificates</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="required_attachments[]" value="National ID" id="attach_id">
                                    <label class="form-check-label" for="attach_id">National ID</label>
                                </div>
                            </div>
                            <div class="form-text text-muted">Select documents applicants must submit</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="jobSubmitBtn">Submit for Approval</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Job Details View Modal -->
<div class="modal fade" id="jobDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Job Vacancy Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="jobDetailsModalBody">
                <div class="text-center p-5">
                    <i class="bx bx-loader-alt bx-spin bx-lg"></i>
                    <p>Loading Details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Review Job Modal (for CEO approval) -->
<div class="modal fade" id="reviewJobModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Review Job Vacancy</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reviewJobModalBody">
                <div class="text-center p-5">
                    <i class="bx bx-loader-alt bx-spin bx-lg"></i>
                    <p>Loading Details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger btn-reject-from-modal" data-id="">
                    <i class="bx bx-x me-1"></i> Reject
                </button>
                <button type="button" class="btn btn-success btn-approve-from-modal" data-id="">
                    <i class="bx bx-check me-1"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Applications Modal -->
<div class="modal fade" id="applicationsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="applicationsModalTitle">Job Applications</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="applicationsModalBody"></div>
        </div>
    </div>
</div>

<!-- Applicant Details Modal -->
<div class="modal fade" id="applicantDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="applicantDetailsModalTitle">Applicant Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="applicantDetailsModalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    const recruitmentUrl = '{{ route("recruitment.handle") }}';

    $('#jobsTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25
    });

    // Helper function to show alerts using Bootstrap
    function showAlert(title, message, type = 'info', reload = false) {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const iconClass = {
            'success': 'bx-check-circle',
            'error': 'bx-error-circle',
            'warning': 'bx-error-circle',
            'info': 'bx-info-circle'
        }[type] || 'bx-info-circle';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
                 style="z-index: 9999; min-width: 300px; max-width: 500px;" role="alert">
                <h6 class="alert-heading mb-2"><i class="bx ${iconClass} me-2"></i><strong>${title}</strong></h6>
                <div>${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('body').append(alertHtml);
        
        const autoClose = reload ? 2000 : 5000;
        setTimeout(() => {
            $('.alert').fadeOut(function() {
                $(this).remove();
                if (reload) location.reload();
            });
        }, autoClose);
    }

    // Confirmation modal helper
    function showConfirmModal(title, message, confirmText, confirmClass, callback) {
        const modalHtml = `
            <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn ${confirmClass}" id="confirmBtn">${confirmText}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#confirmModal').remove();
        $('body').append(modalHtml);
        
        const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
        modal.show();
        
        $('#confirmBtn').on('click', function() {
            modal.hide();
            if (callback) callback();
        });
        
        $('#confirmModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    // Input prompt modal helper
    function showPromptModal(title, message, inputLabel, inputPlaceholder, maxLength, confirmText, confirmClass, callback) {
        const modalHtml = `
            <div class="modal fade" id="promptModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${title}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>${message}</p>
                            <div class="mb-3">
                                <label for="promptInput" class="form-label">${inputLabel}</label>
                                <textarea class="form-control" id="promptInput" rows="4" placeholder="${inputPlaceholder}" maxlength="${maxLength}"></textarea>
                                <div class="form-text"><span id="charCount">0</span> / ${maxLength} characters</div>
                                <div class="invalid-feedback" id="promptError"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn ${confirmClass}" id="promptConfirmBtn">${confirmText}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#promptModal').remove();
        $('body').append(modalHtml);
        
        const modal = new bootstrap.Modal(document.getElementById('promptModal'));
        modal.show();
        
        // Character counter
        $('#promptInput').on('input', function() {
            $('#charCount').text($(this).val().length);
        });
        
        $('#promptConfirmBtn').on('click', function() {
            const value = $('#promptInput').val().trim();
            if (!value) {
                $('#promptInput').addClass('is-invalid');
                $('#promptError').text('This field is required.');
                return;
            }
            if (value.length > maxLength) {
                $('#promptInput').addClass('is-invalid');
                $('#promptError').text(`Maximum ${maxLength} characters allowed.`);
                return;
            }
            $('#promptInput').removeClass('is-invalid');
            modal.hide();
            if (callback) callback(value);
        });
        
        $('#promptModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    function validateJobForm() {
        const title = $('input[name="job_title"]').val().trim();
        const description = $('textarea[name="job_description"]').val().trim();
        const qualifications = $('textarea[name="qualifications"]').val().trim();
        const deadline = $('input[name="application_deadline"]').val();
        const interviewModes = $('input[name="interview_mode[]"]:checked').length;

        if (!title) {
            showAlert('Validation Error', 'Job title is required.', 'error');
            return false;
        }

        if (!description) {
            showAlert('Validation Error', 'Job description is required.', 'error');
            return false;
        }

        if (!qualifications) {
            showAlert('Validation Error', 'Qualifications are required.', 'error');
            return false;
        }

        if (!deadline) {
            showAlert('Validation Error', 'Application deadline is required.', 'error');
            return false;
        }

        const deadlineDate = new Date(deadline);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (deadlineDate < today) {
            showAlert('Validation Error', 'Application deadline must be a future date.', 'error');
            return false;
        }

        if (interviewModes === 0) {
            showAlert('Validation Error', 'Please select at least one interview mode.', 'error');
            return false;
        }

        return true;
    }

    $('#create-job-btn').on('click', function() {
        $('#jobForm')[0].reset();
        $('#jobModalTitle').text('New Job Vacancy');
        $('#jobAction').val('create_job');
        $('#jobId').val('');
        $('#jobSubmitBtn').text('Submit for Approval');
        $('#jobModal').modal('show');
    });

    $('#jobForm').on('submit', function(e) {
        e.preventDefault();

        if (!validateJobForm()) return;

        const formData = $(this).serialize();
        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            dataType: 'json',
            success: function(response) {
                $('#jobModal').modal('hide');
                showAlert(
                    response.success ? 'Success!' : 'Error!',
                    response.message,
                    response.success ? 'success' : 'error',
                    response.success
                );
            },
            error: function(xhr, status, error) {
                let message = 'An unexpected error occurred: ' + error;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('Error!', message, 'error');
            }
        });
    });

    // View job details (read-only)
    $('.card-body').on('click', '.btn-view-details', function() {
        const jobId = $(this).data('id');
        const modal = $('#jobDetailsModal');
        const modalBody = $('#jobDetailsModalBody');
        modalBody.html('<div class="text-center p-5"><i class="bx bx-loader-alt bx-spin bx-lg"></i><p>Loading Details...</p></div>');
        modal.modal('show');

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_job_details', job_id: jobId },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const d = response.details;

                    let attachmentsHtml = '<p class="text-muted">None specified.</p>';
                    if (d.required_attachments && d.required_attachments.length > 0) {
                        attachmentsHtml = '<ul class="list-unstyled">';
                        d.required_attachments.forEach(item => {
                            attachmentsHtml += `<li><i class="bx bx-check-circle text-success me-2"></i>${item}</li>`;
                        });
                        attachmentsHtml += '</ul>';
                    }

                    let modesHtml = '<p class="text-muted">Not specified.</p>';
                    if (d.interview_mode && d.interview_mode.length > 0) {
                        modesHtml = '<div class="d-flex flex-wrap gap-2">';
                        d.interview_mode.forEach(mode => {
                            modesHtml += `<span class="badge bg-primary">${mode}</span>`;
                        });
                        modesHtml += '</div>';
                    }

                    const statusBadgeClass = {
                        'Active': 'success',
                        'Pending Approval': 'info',
                        'Rejected': 'danger',
                        'Closed': 'secondary'
                    }[d.status] || 'light';

                    const createdDate = new Date(d.created_at).toLocaleDateString('en-GB', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    const deadlineDate = new Date(d.application_deadline).toLocaleDateString('en-GB', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    const content = `
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h3 class="mb-0">${d.job_title}</h3>
                            <span class="badge bg-${statusBadgeClass} fs-6">${d.status}</span>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <p class="text-muted mb-1"><i class="bx bx-user me-2"></i>Created by ${d.creator ? d.creator.name : 'N/A'}</p>
                                <p class="text-muted"><i class="bx bx-calendar-plus me-2"></i>Created on ${createdDate}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1"><i class="bx bx-calendar-times me-2"></i>Application Deadline</p>
                                <p class="fw-bold">${deadlineDate}</p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5><i class="bx bx-file-blank me-2"></i>Job Description</h5>
                            <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">${d.job_description}</div>
                        </div>

                        <div class="mb-4">
                            <h5><i class="bx bx-graduation me-2"></i>Qualifications / Requirements</h5>
                            <div class="p-3 bg-light rounded" style="white-space: pre-wrap;">${d.qualifications}</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h5><i class="bx bx-comment me-2"></i>Interview Mode(s)</h5>
                                ${modesHtml}
                            </div>
                            <div class="col-md-6 mb-3">
                                <h5><i class="bx bx-paperclip me-2"></i>Required Attachments</h5>
                                ${attachmentsHtml}
                            </div>
                        </div>

                        ${d.rejection_reason ? `
                        <div class="alert alert-warning">
                            <h6><i class="bx bx-error-circle me-2"></i>Rejection Reason</h6>
                            <p class="mb-0">${d.rejection_reason}</p>
                        </div>
                        ` : ''}
                    `;
                    modalBody.html(content);
                } else {
                    modalBody.html(`<div class="alert alert-danger">${response.message}</div>`);
                }
            },
            error: function() {
                modalBody.html('<div class="alert alert-danger">Failed to load job details.</div>');
            }
        });
    });

    // Edit job (for pending jobs created by the current user)
    $('.card-body').on('click', '.btn-edit-job', function() {
        const jobId = $(this).data('id');

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_job_details_for_edit', job_id: jobId },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const d = response.details;

                    $('#jobForm')[0].reset();
                    $('#jobModalTitle').text('Edit Job Vacancy');
                    $('#jobAction').val('edit_job');
                    $('#jobId').val(jobId);
                    $('#jobSubmitBtn').text('Update Job');

                    // Populate form fields
                    $('input[name="job_title"]').val(d.job_title);
                    $('textarea[name="job_description"]').val(d.job_description);
                    $('textarea[name="qualifications"]').val(d.qualifications);
                    $('input[name="application_deadline"]').val(d.application_deadline);

                    // Set interview modes
                    if (d.interview_mode) {
                        d.interview_mode.forEach(mode => {
                            $(`input[name="interview_mode[]"][value="${mode}"]`).prop('checked', true);
                        });
                    }

                    // Set required attachments
                    if (d.required_attachments) {
                        d.required_attachments.forEach(attach => {
                            $(`input[name="required_attachments[]"][value="${attach}"]`).prop('checked', true);
                        });
                    }

                    $('#jobModal').modal('show');
                } else {
                    showAlert('Error!', response.message, 'error');
                }
            },
            error: function() {
                showAlert('Error!', 'Failed to load job data for editing.', 'error');
            }
        });
    });

    $('.card-body').on('click', '.btn-review', function() {
        const jobId = $(this).data('id');
        const modal = $('#reviewJobModal');
        const modalBody = $('#reviewJobModalBody');
        modalBody.html('<div class="text-center p-5"><i class="bx bx-loader-alt bx-spin bx-lg"></i><p>Loading Details...</p></div>');
        modal.find('.btn-approve-from-modal').data('id', jobId);
        modal.find('.btn-reject-from-modal').data('id', jobId);
        modal.modal('show');

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_job_details', job_id: jobId },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const d = response.details;

                    let attachmentsHtml = '<p class="text-muted">None specified.</p>';
                    if (d.required_attachments && d.required_attachments.length > 0) {
                        attachmentsHtml = '<ul class="list-unstyled">';
                        d.required_attachments.forEach(item => {
                            attachmentsHtml += `<li><i class="bx bx-check-circle text-success me-2"></i>${item}</li>`;
                        });
                        attachmentsHtml += '</ul>';
                    }

                    let modesHtml = '<p class="text-muted">Not specified.</p>';
                    if (d.interview_mode && d.interview_mode.length > 0) {
                        modesHtml = d.interview_mode.join(', ');
                    }

                    const createdDate = new Date(d.created_at).toLocaleDateString();
                    const deadlineDate = new Date(d.application_deadline).toLocaleDateString('en-GB', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    const content = `
                        <h3>${d.job_title}</h3>
                        <p class="text-muted">Created by ${d.creator ? d.creator.name : 'N/A'} on ${createdDate}</p>
                        <hr>
                        <h5><i class="bx bx-file-blank me-2"></i>Job Description</h5>
                        <div class="p-3 bg-light rounded mb-3" style="white-space: pre-wrap;">${d.job_description}</div>
                        <h5><i class="bx bx-graduation me-2"></i>Qualifications / Requirements</h5>
                        <div class="p-3 bg-light rounded mb-3" style="white-space: pre-wrap;">${d.qualifications}</div>
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="bx bx-calendar-times me-2"></i>Deadline</h5>
                                <p>${deadlineDate}</p>
                            </div>
                            <div class="col-md-6">
                                <h5><i class="bx bx-comment me-2"></i>Interview Mode(s)</h5>
                                <p>${modesHtml}</p>
                            </div>
                        </div>
                        <h5><i class="bx bx-paperclip me-2"></i>Required Attachments</h5>
                        ${attachmentsHtml}
                    `;
                    modalBody.html(content);
                } else {
                    modalBody.html(`<div class="alert alert-danger">${response.message}</div>`);
                }
            }
        });
    });

    $('#reviewJobModal').on('click', '.btn-approve-from-modal', function() {
        const jobId = $(this).data('id');
        showConfirmModal(
            'Approve this Job?',
            'This will make the vacancy active and public.',
            'Yes, approve it!',
            'btn-success',
            function() {
                $('#reviewJobModal').modal('hide');
                $.ajax({
                    type: 'POST',
                    url: recruitmentUrl,
                    data: { action: 'approve_job', job_id: jobId },
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    dataType: 'json',
                    success: (response) => showAlert('Approved!', response.message, response.success ? 'success' : 'error', true)
                });
            }
        );
    });

    $('#reviewJobModal').on('click', '.btn-reject-from-modal', function() {
        const jobId = $(this).data('id');
        showPromptModal(
            'Reject this Job?',
            'Please provide a reason for rejecting this job vacancy.',
            'Reason for Rejection',
            'Type your reason here...',
            500,
            'Yes, reject it!',
            'btn-danger',
            function(reason) {
                $('#reviewJobModal').modal('hide');
                $.ajax({
                    type: 'POST',
                    url: recruitmentUrl,
                    data: { action: 'reject_job', job_id: jobId, reason: reason },
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    dataType: 'json',
                    success: (response) => showAlert('Rejected!', response.message, response.success ? 'success' : 'error', true)
                });
            }
        );
    });

    $('#jobsTable').on('click', '.btn-close-job', function() {
        const jobId = $(this).data('id');
        showConfirmModal(
            'Manually Close Job?',
            'No new applications will be accepted.',
            'Yes, close it',
            'btn-warning',
            function() {
                $.ajax({
                    type: 'POST',
                    url: recruitmentUrl,
                    data: { action: 'close_job', job_id: jobId },
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    dataType: 'json',
                    success: (response) => showAlert('Job Closed', response.message, response.success ? 'success' : 'error', true)
                });
            }
        );
    });

    $('#jobsTable').on('click', '.btn-view-applications', function() {
        const jobId = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_job_details_and_applications', job_id: jobId },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#applicationsModalTitle').text(`Applications for: ${response.details.job_title}`);

                    let content = `
                        <div class="mb-3">
                            <p><strong>Status:</strong> <span class="badge bg-${response.details.status === 'Active' ? 'success' : 'secondary'}">${response.details.status}</span> |
                            <strong>Deadline:</strong> ${new Date(response.details.application_deadline).toLocaleDateString()}</p>
                        </div>`;
                    if (response.applications.length === 0) {
                        content += `<div class="text-center text-muted py-5">No applications received yet.</div>`;
                    } else {
                        // Group applicants by status
                        const applicantsByStatus = response.applications.reduce((acc, app) => {
                            (acc[app.status] = acc[app.status] || []).push(app);
                            return acc;
                        }, {});

                        // Define the desired order for the tabs
                        const statusOrder = ['Shortlisted', 'Interviewing', 'Offer Extended', 'Hired', 'Applied', 'Rejected'];

                        let tabHeaders = '';
                        let tabPanes = '';
                        let isFirstTab = true;

                        statusOrder.forEach(status => {
                            if (applicantsByStatus[status] && applicantsByStatus[status].length > 0) {
                                const tabId = status.replace(/\s+/g, '-').toLowerCase();
                                const activeClass = isFirstTab ? 'active' : '';

                                tabHeaders += `
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link ${activeClass}" id="${tabId}-tab" data-bs-toggle="tab" data-bs-target="#${tabId}-pane" type="button" role="tab">
                                            ${status} <span class="badge rounded-pill bg-secondary">${applicantsByStatus[status].length}</span>
                                        </button>
                                    </li>`;

                                let tableRows = '';
                                applicantsByStatus[status].forEach(app => {
                                    const appDate = new Date(app.application_date).toLocaleDateString();
                                    tableRows += `<tr>
                                        <td>${app.first_name} ${app.last_name}</td>
                                        <td>${app.email}</td>
                                        <td>${app.phone}</td>
                                        <td>${appDate}</td>
                                        <td>${app.total_score ? `<span class="badge bg-dark">${app.total_score}</span>` : 'N/A'}</td>
                                        <td><button class="btn btn-sm btn-info btn-view-applicant" data-id="${app.id}" title="View Details"><i class="bx bx-user"></i></button></td>
                                    </tr>`;
                                });
                                tabPanes += `
                                    <div class="tab-pane fade show ${activeClass}" id="${tabId}-pane" role="tabpanel">
                                        <div class="table-responsive">
                                            <table class="table table-hover applications-table">
                                                <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Date Applied</th><th>Score</th><th>Actions</th></tr></thead>
                                                <tbody>${tableRows}</tbody>
                                            </table>
                                        </div>
                                    </div>`;

                                isFirstTab = false;
                            }
                        });
                        content += `<ul class="nav nav-tabs">${tabHeaders}</ul><div class="tab-content pt-3">${tabPanes}</div>`;
                    }
                    $('#applicationsModalBody').html(content);
                    $('.applications-table').DataTable({ "searching": false, "lengthChange": false });
                    $('#applicationsModal').modal('show');
                } else {
                    showAlert('Error!', response.message, 'error');
                }
            }
        });
    });

    $('#applicationsModal').on('click', '.btn-view-applicant', function() {
        const appId = $(this).data('id');
        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_application_details', application_id: appId },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            dataType: 'json',
            success: function(res) {
                if (!res.success) {
                    showAlert('Error', res.message, 'error');
                    return;
                }
                const details = res.details;
                const documents = res.documents;
                const evaluation = res.evaluation || {};

                $('#applicantDetailsModalTitle').text(`${details.first_name} ${details.last_name}`);

                let docsHtml = '<h6><i class="bx bx-paperclip me-2"></i>Attachments</h6>';
                if (documents.length > 0) {
                    docsHtml += '<ul class="list-group list-group-flush">';
                    documents.forEach(doc => {
                        const fileSize = doc.file_size ? (doc.file_size / 1024).toFixed(1) + ' KB' : 'N/A';
                        docsHtml += `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="/storage/recruitment/${doc.file_path}" target="_blank" class="text-decoration-none">
                               <i class="bx bx-file me-2"></i>${doc.original_filename}
                            </a>
                            <span class="badge bg-secondary rounded-pill">${fileSize}</span>
                        </li>`;
                    });
                    docsHtml += '</ul>';
                } else {
                    docsHtml += '<p class="text-muted">No documents uploaded.</p>';
                }

                const statusOptions = ['Applied', 'Shortlisted', 'Interviewing', 'Offer Extended', 'Hired', 'Rejected']
                    .map(status => `<option value="${status}" ${details.status === status ? 'selected' : ''}>${status}</option>`)
                    .join('');

                const evaluationStatuses = ['Shortlisted', 'Interviewing', 'Offer Extended', 'Hired'];
                const canEvaluate = evaluationStatuses.includes(details.status);
                const appDate = new Date(details.application_date).toLocaleDateString();

                const modalContent = `
                    <ul class="nav nav-tabs" id="applicantTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane" type="button" role="tab">Applicant Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="evaluation-tab" data-bs-toggle="tab" data-bs-target="#evaluation-pane" type="button" role="tab">Evaluation & Actions</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-3" id="applicantTabContent">
                        <div class="tab-pane fade show active" id="details-pane" role="tabpanel">
                            <h5><i class="bx bx-user-circle me-2"></i>Applicant Information</h5>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <p><strong>Email:</strong> ${details.email}</p>
                                    <p><strong>Phone:</strong> ${details.phone}</p>
                                    <p><strong>Applied On:</strong> ${appDate}</p>
                                </div>
                            </div>
                            <hr>
                            ${docsHtml}
                        </div>
                        <div class="tab-pane fade" id="evaluation-pane" role="tabpanel">
                            ${canEvaluate ? `
                            <form id="evaluationForm">
                                <h5><i class="bx bx-clipboard me-2"></i>Interview Evaluation</h5>
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="row g-3 mb-3">
                                            <div class="col-md-4"><label class="form-label">Written Score</label><input type="number" step="0.5" min="0" max="100" name="written_score" class="form-control" value="${evaluation.written_score || ''}" placeholder="0-100"></div>
                                            <div class="col-md-4"><label class="form-label">Practical Score</label><input type="number" step="0.5" min="0" max="100" name="practical_score" class="form-control" value="${evaluation.practical_score || ''}" placeholder="0-100"></div>
                                            <div class="col-md-4"><label class="form-label">Oral Score</label><input type="number" step="0.5" min="0" max="100" name="oral_score" class="form-control" value="${evaluation.oral_score || ''}" placeholder="0-100"></div>
                                        </div>
                                        <div class="mb-3"><label class="form-label">Interviewer Comments</label><textarea name="comments" class="form-control" rows="4" placeholder="Add evaluation comments...">${evaluation.comments || ''}</textarea></div>
                                        <div class="text-end"><button type="submit" class="btn btn-primary" data-id="${details.id}"><i class="bx bx-save me-2"></i>Save Evaluation</button></div>
                                    </div>
                                </div>
                            </form>
                            ` : `
                            <div class="alert alert-info">
                                <h5 class="alert-heading">Evaluation Not Yet Available</h5>
                                <p>The evaluation form can be filled out once the applicant's status is updated to "Shortlisted".</p>
                            </div>
                            `}
                            <hr>
                            <h5><i class="bx bx-edit me-2"></i>Update Application Status</h5>
                            <p><strong>Current Status:</strong> <span id="currentStatusBadge" class="badge bg-${details.status === 'Rejected' ? 'danger' : details.status === 'Hired' ? 'success' : 'info'}">${details.status}</span></p>
                            <div class="input-group">
                                <select class="form-select" id="applicationStatusSelect">${statusOptions}</select>
                                <button class="btn btn-outline-success btn-update-status" data-id="${details.id}">Update Status</button>
                            </div>
                        </div>
                    </div>
                `;
                $('#applicantDetailsModalBody').html(modalContent);
                $('#applicantDetailsModal').modal('show');
            }
        });
    });

    $('#applicantDetailsModal').on('click', '.btn-update-status', function() {
        const appId = $(this).data('id');
        const newStatus = $('#applicationStatusSelect').val();
        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'update_application_status', application_id: appId, status: newStatus },
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            dataType: 'json',
            success: (response) => {
                showAlert('Status Updated', response.message, response.success ? 'success' : 'error', false);
                if (response.success) {
                    $('#applicantDetailsModal').modal('hide');
                }
            }
        });
    });

    $('#applicantDetailsModal').on('submit', '#evaluationForm', function(e) {
        e.preventDefault();
        const appId = $(this).find('button[type=submit]').data('id');
        const formData = $(this).serialize() + `&action=save_evaluation&application_id=${appId}`;

        const written = parseFloat($('input[name="written_score"]').val()) || 0;
        const practical = parseFloat($('input[name="practical_score"]').val()) || 0;
        const oral = parseFloat($('input[name="oral_score"]').val()) || 0;

        if ((written && (written < 0 || written > 100)) ||
            (practical && (practical < 0 || practical > 100)) ||
            (oral && (oral < 0 || oral > 100))) {
            showAlert('Validation Error', 'All scores must be between 0 and 100.', 'error');
            return;
        }

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            dataType: 'json',
            success: (response) => showAlert('Evaluation Saved', response.message, response.success ? 'success' : 'error', false)
        });
    });

    // ==================== ANALYTICS TAB ====================
    function loadAnalytics() {
        const dateFrom = $('#analyticsDateFrom').val() || new Date(Date.now() - 180*24*60*60*1000).toISOString().split('T')[0];
        const dateTo = $('#analyticsDateTo').val() || new Date().toISOString().split('T')[0];

        $('#analyticsContent').html('<div class="text-center p-5"><i class="bx bx-loader-alt bx-spin bx-lg"></i><p>Loading Analytics...</p></div>');

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_analytics', date_from: dateFrom, date_to: dateTo },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.analytics) {
                    const a = response.analytics;
                    let html = `
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h3 class="text-primary mb-0">${a.jobs_by_status.reduce((sum, j) => sum + parseInt(j.count), 0)}</h3>
                                        <p class="text-muted mb-0">Total Jobs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h3 class="text-success mb-0">${a.hiring_rate.total}</h3>
                                        <p class="text-muted mb-0">Total Applications</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h3 class="text-info mb-0">${a.hiring_rate.hired}</h3>
                                        <p class="text-muted mb-0">Hired</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h3 class="text-warning mb-0">${a.hiring_rate.rate}%</h3>
                                        <p class="text-muted mb-0">Hiring Rate</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header"><strong>Jobs by Status</strong></div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <thead><tr><th>Status</th><th class="text-end">Count</th></tr></thead>
                                            <tbody>`;
                    a.jobs_by_status.forEach(j => {
                        const badgeClass = j.status === 'Active' ? 'success' : j.status === 'Rejected' ? 'danger' : 'secondary';
                        html += `<tr><td><span class="badge bg-${badgeClass}">${j.status}</span></td><td class="text-end">${j.count}</td></tr>`;
                    });
                    html += `</tbody></table></div></div></div>

                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header"><strong>Applications by Status</strong></div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <thead><tr><th>Status</th><th class="text-end">Count</th></tr></thead>
                                            <tbody>`;
                    a.applications_by_status.forEach(app => {
                        html += `<tr><td>${app.status}</td><td class="text-end">${app.count}</td></tr>`;
                    });
                    html += `</tbody></table></div></div></div>
                        </div>

                        <div class="card mb-4">
                            <div class="card-header"><strong>Top Jobs by Applications</strong></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead><tr><th>Job Title</th><th class="text-end">Applications</th></tr></thead>
                                        <tbody>`;
                    a.top_jobs.forEach(job => {
                        html += `<tr><td>${job.job_title}</td><td class="text-end"><span class="badge bg-primary">${job.applications_count}</span></td></tr>`;
                    });
                    html += `</tbody></table></div></div></div>

                        <div class="card">
                            <div class="card-header"><strong>Applications Over Time</strong></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead><tr><th>Date</th><th class="text-end">Applications</th></tr></thead>
                                        <tbody>`;
                    a.applications_over_time.forEach(item => {
                        html += `<tr><td>${new Date(item.date).toLocaleDateString()}</td><td class="text-end">${item.count}</td></tr>`;
                    });
                    html += `</tbody></table></div></div></div>`;

                    $('#analyticsContent').html(html);
                } else {
                    $('#analyticsContent').html('<div class="alert alert-danger">Failed to load analytics data.</div>');
                }
            },
            error: function() {
                $('#analyticsContent').html('<div class="alert alert-danger">Error loading analytics data.</div>');
            }
        });
    }

    // Set default dates
    $('#analyticsDateFrom').val(new Date(Date.now() - 180*24*60*60*1000).toISOString().split('T')[0]);
    $('#analyticsDateTo').val(new Date().toISOString().split('T')[0]);

    $('#analytics-tab').on('shown.bs.tab', function() {
        loadAnalytics();
    });

    $('#refreshAnalytics').on('click', function() {
        loadAnalytics();
    });

    // ==================== INTERVIEW SCHEDULES TAB ====================
    function loadInterviewSchedules(filter = 'all') {
        $('#interviewsContent').html('<div class="text-center p-5"><i class="bx bx-loader-alt bx-spin bx-lg"></i><p>Loading Interviews...</p></div>');

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { action: 'get_interview_schedules', filter: filter },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.schedules) {
                    if (response.schedules.length === 0) {
                        $('#interviewsContent').html('<div class="alert alert-info text-center">No interview schedules found.</div>');
                        return;
                    }

                    let html = '<div class="table-responsive"><table class="table table-hover" id="interviewsTable"><thead><tr><th>Applicant</th><th>Job Title</th><th>Interview Date</th><th>Time</th><th>Mode</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                    response.schedules.forEach(schedule => {
                        const statusBadge = {
                            'Scheduled': 'info',
                            'Completed': 'success',
                            'Cancelled': 'danger',
                            'Rescheduled': 'warning'
                        }[schedule.status] || 'secondary';
                        
                        const interviewDate = schedule.interview_date ? new Date(schedule.interview_date) : null;
                        const applicant = schedule.application ? `${schedule.application.first_name} ${schedule.application.last_name}` : 'N/A';
                        const jobTitle = schedule.application && schedule.application.job ? schedule.application.job.job_title : 'N/A';
                        
                        html += `
                            <tr>
                                <td>${applicant}</td>
                                <td>${jobTitle}</td>
                                <td>${interviewDate ? interviewDate.toLocaleDateString() : 'N/A'}</td>
                                <td>${schedule.interview_time || 'N/A'}</td>
                                <td><span class="badge bg-primary">${schedule.interview_mode || 'N/A'}</span></td>
                                <td><span class="badge bg-${statusBadge}">${schedule.status || 'N/A'}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary btn-update-interview" data-id="${schedule.id}">
                                        <i class="bx bx-edit"></i>
                                    </button>
                                </td>
                            </tr>`;
                    });
                    html += '</tbody></table></div>';
                    $('#interviewsContent').html(html);
                    
                    if ($.fn.DataTable.isDataTable('#interviewsTable')) {
                        $('#interviewsTable').DataTable().destroy();
                    }
                    $('#interviewsTable').DataTable({ pageLength: 25 });
                } else {
                    $('#interviewsContent').html('<div class="alert alert-danger">Failed to load interview schedules.</div>');
                }
            },
            error: function() {
                $('#interviewsContent').html('<div class="alert alert-danger">Error loading interview schedules.</div>');
            }
        });
    }

    $('#interviews-tab').on('shown.bs.tab', function() {
        loadInterviewSchedules($('#interviewFilter').val());
    });

    $('#interviewFilter').on('change', function() {
        loadInterviewSchedules($(this).val());
    });

    // ==================== BULK OPERATIONS TAB ====================
    $('#loadBulkApplications').on('click', function() {
        const jobId = $('#bulkJobSelect').val();
        $('#bulkOperationsContent').html('<div class="text-center p-5"><i class="bx bx-loader-alt bx-spin bx-lg"></i><p>Loading Applications...</p></div>');

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: { 
                action: 'get_bulk_applications', 
                job_id: jobId || null 
            },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.applications) {
                    if (response.applications.length === 0) {
                        $('#bulkOperationsContent').html(`
                            <div class="alert alert-info text-center">
                                <i class="bx bx-info-circle me-2"></i>No applications found${jobId ? ' for the selected job' : ''}.
                            </div>
                        `);
                        return;
                    }
                    let html = `
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <strong>Select Applications for Bulk Operations</strong>
                                <div>
                                    <button class="btn btn-sm btn-light me-2" id="selectAllBulk">
                                        <i class="bx bx-check-double me-1"></i> Select All
                                    </button>
                                    <button class="btn btn-sm btn-light" id="deselectAllBulk">
                                        <i class="bx bx-x me-1"></i> Deselect All
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Bulk Status Update</label>
                                        <select id="bulkStatusSelect" class="form-select">
                                            <option value="">Select Status...</option>
                                            <option value="Shortlisted">Shortlisted</option>
                                            <option value="Interviewing">Interviewing</option>
                                            <option value="Offer Extended">Offer Extended</option>
                                            <option value="Hired">Hired</option>
                                            <option value="Rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">&nbsp;</label>
                                        <button class="btn btn-primary w-100" id="bulkUpdateStatusBtn">
                                            <i class="bx bx-user-check me-1"></i> Update Selected
                                        </button>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <button class="btn btn-danger w-100" id="bulkDeleteBtn">
                                            <i class="bx bx-trash me-1"></i> Delete Selected
                                        </button>
                                    </div>
                                </div>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="bulkApplicationsTable">
                                        <thead>
                                            <tr>
                                                <th width="50"><input type="checkbox" id="selectAllCheckbox"></th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Job Title</th>
                                                <th>Status</th>
                                                <th>Applied Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                    response.applications.forEach(app => {
                        const statusBadge = {
                            'Applied': 'info',
                            'Shortlisted': 'primary',
                            'Interviewing': 'warning',
                            'Offer Extended': 'success',
                            'Hired': 'success',
                            'Rejected': 'danger'
                        }[app.status] || 'secondary';
                        html += `
                            <tr>
                                <td><input type="checkbox" class="bulk-checkbox" value="${app.id}"></td>
                                <td>${app.first_name} ${app.last_name}</td>
                                <td>${app.email}</td>
                                <td>${app.phone}</td>
                                <td>${app.job_title || 'N/A'}</td>
                                <td><span class="badge bg-${statusBadge}">${app.status}</span></td>
                                <td>${new Date(app.application_date).toLocaleDateString()}</td>
                            </tr>`;
                    });
                    html += `</tbody></table></div></div></div>`;
                    $('#bulkOperationsContent').html(html);

                    if ($.fn.DataTable.isDataTable('#bulkApplicationsTable')) {
                        $('#bulkApplicationsTable').DataTable().destroy();
                    }
                    $('#bulkApplicationsTable').DataTable({ pageLength: 25 });

                    $('#selectAllCheckbox').on('change', function() {
                        $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
                    });

                    $('#selectAllBulk').on('click', function() {
                        $('.bulk-checkbox').prop('checked', true);
                        $('#selectAllCheckbox').prop('checked', true);
                    });

                    $('#deselectAllBulk').on('click', function() {
                        $('.bulk-checkbox').prop('checked', false);
                        $('#selectAllCheckbox').prop('checked', false);
                    });

                    $('#bulkUpdateStatusBtn').on('click', function() {
                        const selected = $('.bulk-checkbox:checked').map(function() { return $(this).val(); }).get();
                        const newStatus = $('#bulkStatusSelect').val();

                        if (selected.length === 0) {
                            showAlert('No Selection', 'Please select at least one application.', 'warning');
                            return;
                        }

                        if (!newStatus) {
                            showAlert('No Status', 'Please select a status to update.', 'warning');
                            return;
                        }

                        showConfirmModal(
                            'Bulk Update Status?',
                            `Are you sure you want to update ${selected.length} application(s) to "${newStatus}"?`,
                            'Yes, Update',
                            'btn-primary',
                            function() {
                                $.ajax({
                                    type: 'POST',
                                    url: recruitmentUrl,
                                    data: {
                                        action: 'bulk_update_status',
                                        application_ids: selected,
                                        status: newStatus
                                    },
                                    headers: { 'X-CSRF-TOKEN': csrfToken },
                                    dataType: 'json',
                                    success: function(response) {
                                        showAlert('Success!', response.message, response.success ? 'success' : 'error', true);
                                    }
                                });
                            }
                        );
                    });

                    $('#bulkDeleteBtn').on('click', function() {
                        const selected = $('.bulk-checkbox:checked').map(function() { return $(this).val(); }).get();

                        if (selected.length === 0) {
                            showAlert('No Selection', 'Please select at least one application to delete.', 'warning');
                            return;
                        }

                        showConfirmModal(
                            'Delete Applications?',
                            `Are you sure you want to permanently delete ${selected.length} application(s)? This action cannot be undone.`,
                            'Yes, Delete',
                            'btn-danger',
                            function() {
                                $.ajax({
                                    type: 'POST',
                                    url: recruitmentUrl,
                                    data: {
                                        action: 'bulk_delete',
                                        application_ids: selected
                                    },
                                    headers: { 'X-CSRF-TOKEN': csrfToken },
                                    dataType: 'json',
                                    success: function(response) {
                                        showAlert('Deleted!', response.message, response.success ? 'success' : 'error', true);
                                    }
                                });
                            }
                        );
                    });
                } else {
                    $('#bulkOperationsContent').html(`
                        <div class="alert alert-danger">
                            <i class="bx bx-error-circle me-2"></i>${response.message || 'Failed to load applications.'}
                        </div>
                    `);
                }
            },
            error: function(xhr) {
                let message = 'Error loading applications.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                $('#bulkOperationsContent').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i>${message}
                    </div>
                `);
            }
        });
    });

    // ==================== APPLICATION HISTORY TAB ====================
    function loadApplicationHistory(search = '', statusFilter = '') {
        $('#historyContent').html('<div class="text-center p-5"><i class="bx bx-loader-alt bx-spin bx-lg"></i><p>Loading History...</p></div>');

        $.ajax({
            type: 'POST',
            url: recruitmentUrl,
            data: {
                action: 'get_application_history',
                search: search,
                status: statusFilter
            },
            headers: { 'X-CSRF-TOKEN': csrfToken },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.history) {
                    if (response.history.length === 0) {
                        $('#historyContent').html('<div class="alert alert-info text-center">No application history found.</div>');
                        return;
                    }

                    let html = '<div class="timeline">';
                    response.history.forEach(entry => {
                        const date = new Date(entry.created_at);
                        const statusBadge = {
                            'Applied': 'info',
                            'Shortlisted': 'primary',
                            'Interviewing': 'warning',
                            'Offer Extended': 'success',
                            'Hired': 'success',
                            'Rejected': 'danger'
                        }[entry.status_to] || 'secondary';

                        const applicant = entry.application ? `${entry.application.first_name} ${entry.application.last_name}` : 'N/A';
                        const jobTitle = entry.application && entry.application.job ? entry.application.job.job_title : 'N/A';
                        const changedBy = entry.changed_by ? entry.changed_by.name : (entry.changedBy ? entry.changedBy.name : 'System');
                        
                        html += `
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">${applicant}</h6>
                                            <p class="text-muted mb-1 small">
                                                <i class="bx bx-briefcase me-1"></i>${jobTitle}
                                            </p>
                                            <p class="mb-0 small">
                                                ${entry.status_from ? `<span class="badge bg-secondary">${entry.status_from}</span> → ` : ''}
                                                <span class="badge bg-${statusBadge}">${entry.status_to}</span>
                                            </p>
                                            ${entry.notes ? `<p class="mt-2 mb-0 small text-muted"><em>${entry.notes}</em></p>` : ''}
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">${date.toLocaleString()}</small>
                                            <br><small class="text-muted">By: ${changedBy}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>`;
                    });
                    html += '</div>';
                    $('#historyContent').html(html);
                } else {
                    $('#historyContent').html('<div class="alert alert-danger">Failed to load application history.</div>');
                }
            },
            error: function() {
                $('#historyContent').html('<div class="alert alert-danger">Error loading application history.</div>');
            }
        });
    }

    $('#history-tab').on('shown.bs.tab', function() {
        loadApplicationHistory();
    });

    $('#historySearch').on('input', function() {
        clearTimeout(window.historySearchTimeout);
        window.historySearchTimeout = setTimeout(() => {
            loadApplicationHistory($(this).val(), $('#historyStatusFilter').val());
        }, 500);
    });

    $('#historyStatusFilter').on('change', function() {
        loadApplicationHistory($('#historySearch').val(), $(this).val());
    });
});
</script>
@endpush
@endsection
