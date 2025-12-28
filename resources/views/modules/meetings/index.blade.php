@extends('layouts.app')

@section('title', 'Meeting Management')

@section('breadcrumb')
<div class="row">
    <div class="col-lg-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
                <h4 class="fw-bold py-3 mb-2">
                    <i class="bx bx-calendar-event"></i> Meeting Management
                </h4>
                <p class="text-muted">Manage meetings, agendas, participants, and minutes with approval workflows</p>
    </div>
            <div class="btn-group" role="group">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bx bx-plus"></i> Meetings
        </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="create-meeting-btn"><i class="bx bx-calendar-plus"></i> Create Meeting</a></li>
                        <li><a class="dropdown-item" href="#" id="manage-categories-btn"><i class="bx bx-category"></i> Manage Categories</a></li>
            <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" id="pending-approvals-btn"><i class="bx bx-time"></i> Pending Approvals</a></li>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bx bx-file"></i> Minutes
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="create-minutes-btn"><i class="bx bx-edit"></i> Create Minutes</a></li>
                        <li><a class="dropdown-item" href="#" id="view-minutes-btn"><i class="bx bx-list-ul"></i> View All Minutes</a></li>
        </ul>
                </div>
                <button class="btn btn-outline-dark" id="refresh-btn">
                    <i class="bx bx-refresh"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .meeting-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        overflow: hidden;
    }
    .meeting-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.15);
    }
    .wizard-step {
        padding: 15px 20px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .wizard-step.active {
        background: #007bff;
        color: white;
    }
    .wizard-step.completed {
        background: #28a745;
        color: white;
    }
    .wizard-step .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 10px;
    }
    .participant-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        position: relative;
    }
    .participant-card .remove-btn {
        position: absolute;
        top: 5px;
        right: 5px;
    }
    .agenda-item {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }
    .agenda-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .agenda-item .drag-handle {
        cursor: move;
        color: #6c757d;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    .meeting-timeline {
        position: relative;
        padding-left: 30px;
    }
    .meeting-timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: -24px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #007bff;
    }
    .review-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .review-section h6 {
        color: #007bff;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }
    .minutes-editor {
        min-height: 200px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
    }
    .action-item {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 0 8px 8px 0;
    }
    .action-item.completed {
        background: #d4edda;
        border-left-color: #28a745;
    }
    .action-item.pending {
        background: #f8d7da;
        border-left-color: #dc3545;
    }
    .swal2-container { z-index: 200000 !important; }
    .select2-container { z-index: 200001 !important; }
    .flatpickr-calendar { z-index: 200002 !important; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Dashboard Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Total Meetings</h6>
                        <h3 class="mb-0 text-primary" id="stat-total-meetings">0</h3>
                        <small class="text-success"><i class="bx bx-trending-up"></i> This month</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-primary rounded"><i class="bx bx-calendar"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Upcoming</h6>
                        <h3 class="mb-0 text-success" id="stat-upcoming">0</h3>
                        <small class="text-info"><i class="bx bx-time"></i> Scheduled</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-success rounded"><i class="bx bx-calendar-check"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Pending Approval</h6>
                        <h3 class="mb-0 text-warning" id="stat-pending">0</h3>
                        <small class="text-warning"><i class="bx bx-hourglass"></i> Awaiting</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-warning rounded"><i class="bx bx-time-five"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted mb-1">Minutes Pending</h6>
                        <h3 class="mb-0 text-danger" id="stat-minutes-pending">0</h3>
                        <small class="text-danger"><i class="bx bx-file"></i> Need minutes</small>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-danger rounded"><i class="bx bx-edit"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Sidebar -->
        <div class="col-xl-3 col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-zap"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" id="quick-create-meeting">
                            <i class="bx bx-calendar-plus"></i> New Meeting
                        </button>
                        <button class="btn btn-outline-success btn-sm" id="quick-create-minutes">
                            <i class="bx bx-edit"></i> Create Minutes
                        </button>
                        <button class="btn btn-outline-info btn-sm" id="quick-view-calendar">
                            <i class="bx bx-calendar"></i> View Calendar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Categories -->
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bx bx-category"></i> Categories</h5>
                    <button class="btn btn-sm btn-outline-primary" id="add-category-btn">
                        <i class="bx bx-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="categories-list">
                        <!-- Categories loaded here -->
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bx bx-history"></i> Recent Activity</h5>
                </div>
                <div class="card-body">
                    <div id="recent-activity" style="max-height: 300px; overflow-y: auto;">
                        <!-- Activity loaded here -->
                            </div>
                                </div>
                            </div>
                        </div>

        <!-- Main Area -->
        <div class="col-xl-9 col-lg-8">
            <!-- Filters -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <select class="form-select" id="filter-status">
                                <option value="">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="pending_approval">Pending Approval</option>
                                <option value="approved">Approved</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filter-category">
                                <option value="">All Categories</option>
                                </select>
                            </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control" id="filter-date" placeholder="Select Date Range">
                                </div>
                                            <div class="col-md-3">
                            <input type="text" class="form-control" id="filter-search" placeholder="Search meetings...">
                                            </div>
                                            </div>
                                            </div>
                                            </div>

            <!-- Meetings List -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="bx bx-list-ul"></i> Meetings</h5>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-secondary active" id="view-list"><i class="bx bx-list-ul"></i></button>
                        <button class="btn btn-outline-secondary" id="view-grid"><i class="bx bx-grid-alt"></i></button>
                                            </div>
                                        </div>
                <div class="card-body">
                    <div id="meetings-container">
                        <!-- Meetings loaded here -->
                                            </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>

<!-- Create/Edit Meeting Modal (Multi-Step Wizard) -->
<div class="modal fade" id="meetingWizardModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-calendar-plus"></i> <span id="wizard-title">Create Meeting</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Wizard Steps -->
                    <div class="col-md-3">
                        <div class="wizard-step active" data-step="1">
                            <span class="step-number">1</span> Basic Information
                        </div>
                        <div class="wizard-step" data-step="2">
                            <span class="step-number">2</span> Participants
                        </div>
                        <div class="wizard-step" data-step="3">
                            <span class="step-number">3</span> Agenda Items
                                            </div>
                        <div class="wizard-step" data-step="4">
                            <span class="step-number">4</span> Review & Submit
                                            </div>
                                            </div>

                    <!-- Step Content -->
                    <div class="col-md-9">
                        <form id="meetingForm">
                            @csrf
                            <input type="hidden" name="meeting_id" id="meeting_id">

                            <!-- Step 1: Basic Information -->
                            <div class="wizard-content" id="step-1">
                                <h5 class="mb-4"><i class="bx bx-info-circle"></i> Basic Information</h5>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label class="form-label">Meeting Title *</label>
                                            <input type="text" name="title" class="form-control" required placeholder="Enter meeting title">
                                            </div>
                                            </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Category *</label>
                                            <select name="category_id" class="form-select" required>
                                                <option value="">Select Category</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Date *</label>
                                            <input type="text" name="meeting_date" class="form-control datepicker" required placeholder="Select date">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">Start Time *</label>
                                            <input type="text" name="start_time" class="form-control timepicker" required placeholder="Select time">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">End Time *</label>
                                            <input type="text" name="end_time" class="form-control timepicker" required placeholder="Select time">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Venue/Location *</label>
                                            <input type="text" name="venue" class="form-control" required placeholder="Enter venue or meeting link">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Meeting Type</label>
                                            <select name="meeting_type" class="form-select">
                                                <option value="physical">Physical</option>
                                                <option value="virtual">Virtual</option>
                                                <option value="hybrid">Hybrid</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description/Objectives</label>
                                    <textarea name="description" class="form-control" rows="3" placeholder="Enter meeting description or objectives"></textarea>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-success save-step-btn" data-step="1">
                                        <i class="bx bx-save"></i> Save Basic Info
                                    </button>
                                </div>
                            </div>

                            <!-- Step 2: Participants -->
                            <div class="wizard-content d-none" id="step-2">
                                <h5 class="mb-4"><i class="bx bx-user-plus"></i> Participants</h5>
                                
                                <!-- Staff Participants -->
                                <div class="mb-4">
                                    <h6><i class="bx bx-user"></i> Internal Staff</h6>
                                    <div class="mb-3">
                                        <select id="staff-select" class="form-select" multiple>
                                            <!-- Staff loaded dynamically -->
                                        </select>
                                    </div>
                                    <div id="selected-staff-list" class="row">
                                        <!-- Selected staff shown here -->
                                    </div>
                                </div>

                                <hr>

                                <!-- External Participants -->
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0"><i class="bx bx-user-voice"></i> External Participants</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" id="add-external-btn">
                                            <i class="bx bx-plus"></i> Add External
                                        </button>
                                    </div>
                                    <div id="external-participants-list">
                                        <!-- External participants added here -->
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="button" class="btn btn-success save-step-btn" data-step="2">
                                        <i class="bx bx-save"></i> Save Participants
                                    </button>
                                </div>
                            </div>

                            <!-- Step 3: Agenda Items -->
                            <div class="wizard-content d-none" id="step-3">
                                <h5 class="mb-4"><i class="bx bx-list-check"></i> Agenda Items</h5>
                                
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary" id="add-agenda-btn">
                                        <i class="bx bx-plus"></i> Add Agenda Item
                                    </button>
                                </div>

                                <div id="agenda-items-list" class="sortable-agenda">
                                    <!-- Agenda items added here -->
                                </div>

                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-success save-step-btn" data-step="3">
                                        <i class="bx bx-save"></i> Save Agenda
                                    </button>
                                </div>
                            </div>

                            <!-- Step 4: Review & Submit -->
                            <div class="wizard-content d-none" id="step-4">
                                <h5 class="mb-4"><i class="bx bx-check-double"></i> Review Meeting Details</h5>
                                
                                <div class="review-section">
                                    <h6><i class="bx bx-info-circle"></i> Basic Information</h6>
                                    <div id="review-basic-info">
                                        <!-- Review content loaded here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-warning edit-section-btn" data-step="1">
                                        <i class="bx bx-edit"></i> Edit
                                    </button>
                                </div>

                                <div class="review-section">
                                    <h6><i class="bx bx-user-plus"></i> Participants</h6>
                                    <div id="review-participants">
                                        <!-- Review content loaded here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-warning edit-section-btn" data-step="2">
                                        <i class="bx bx-edit"></i> Edit
                                    </button>
                                </div>

                                <div class="review-section">
                                    <h6><i class="bx bx-list-check"></i> Agenda Items</h6>
                                    <div id="review-agenda">
                                        <!-- Review content loaded here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-warning edit-section-btn" data-step="3">
                                        <i class="bx bx-edit"></i> Edit
                                    </button>
                        </div>

                                <div class="alert alert-info mt-4">
                                    <i class="bx bx-info-circle"></i> 
                                    <strong>Note:</strong> After submission, the meeting will be sent for approval to the selected approver.
                        </div>

                                <div class="mb-3">
                                    <label class="form-label">Send for Approval To *</label>
                                    <select name="approver_id" class="form-select" required>
                                        <option value="">Select Approver</option>
                                        <!-- Approvers loaded dynamically -->
                                    </select>
                                </div>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary d-none" id="prev-step-btn">
                    <i class="bx bx-chevron-left"></i> Previous
                </button>
                <button type="button" class="btn btn-primary" id="next-step-btn">
                    Next <i class="bx bx-chevron-right"></i>
                </button>
                <button type="button" class="btn btn-success d-none" id="submit-meeting-btn">
                    <i class="bx bx-send"></i> Submit for Approval
                </button>
            </div>
                </div>
            </div>
        </div>

<!-- Categories Management Modal -->
<div class="modal fade" id="categoriesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title text-white"><i class="bx bx-category"></i> Manage Meeting Categories</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            <div class="modal-body">
                <div class="mb-3">
                    <form id="categoryForm" class="row g-2">
                        @csrf
                        <input type="hidden" name="category_id" id="category_id">
                        <div class="col-md-5">
                            <input type="text" name="name" class="form-control" placeholder="Category Name *" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="description" class="form-control" placeholder="Description">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-plus"></i> Add Category
                            </button>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="categoriesTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Meetings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Categories loaded here -->
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </div>

<!-- Meeting Review/Approval Modal -->
<div class="modal fade" id="meetingReviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bx bx-check-shield"></i> Review Meeting for Approval</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="meeting-review-content">
                <!-- Meeting details loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="edit-meeting-btn">
                    <i class="bx bx-edit"></i> Edit Meeting
                </button>
                <button type="button" class="btn btn-danger" id="reject-meeting-btn">
                    <i class="bx bx-x"></i> Reject
                </button>
                <button type="button" class="btn btn-success" id="approve-meeting-btn">
                    <i class="bx bx-check"></i> Approve & Send SMS
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Minutes Creation Modal -->
<div class="modal fade" id="minutesModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bx bx-file"></i> Prepare Meeting Minutes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Select Meeting -->
                <div id="minutes-meeting-select" class="mb-4">
                    <label class="form-label">Select Meeting *</label>
                    <select id="select-meeting-for-minutes" class="form-select">
                        <option value="">Select a meeting without minutes...</option>
                        <!-- Meetings loaded dynamically -->
                    </select>
                </div>

                <!-- Minutes Form (Hidden until meeting selected) -->
                <div id="minutes-form-container" class="d-none">
                    <form id="minutesForm">
                        @csrf
                        <input type="hidden" name="meeting_id" id="minutes_meeting_id">

                        <!-- Meeting Info Display -->
                        <div class="alert alert-info mb-4" id="minutes-meeting-info">
                            <!-- Meeting info shown here -->
                        </div>

                        <!-- Previous Actions Reference -->
                        <div class="mb-4" id="previous-actions-section">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="has-previous-actions">
                                <label class="form-check-label" for="has-previous-actions">
                                    <strong>Has Referenced Actions from Previous Meeting?</strong>
                                </label>
                            </div>
                            <div id="previous-actions-container" class="d-none">
                                <h6><i class="bx bx-history"></i> Previous Meeting Actions</h6>
                                <div id="previous-actions-list">
                                    <!-- Previous actions loaded here -->
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-previous-action-btn">
                                    <i class="bx bx-plus"></i> Add Previous Action
                                </button>
                                <div class="text-end mt-2">
                                    <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="previous_actions">
                                        <i class="bx bx-save"></i> Save Previous Actions
                                    </button>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Attendance -->
                        <div class="mb-4">
                            <h6><i class="bx bx-user-check"></i> Attendance</h6>
                            <div id="attendance-list">
                                <!-- Attendance checkboxes loaded here -->
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="attendance">
                                    <i class="bx bx-save"></i> Save Attendance
                                </button>
                            </div>
                        </div>

                        <hr>

                        <!-- Agenda Minutes -->
                        <div class="mb-4">
                            <h6><i class="bx bx-list-check"></i> Agenda Discussions & Resolutions</h6>
                            <div id="agenda-minutes-list">
                                <!-- Agenda items with minutes fields loaded here -->
                            </div>
                        </div>

                        <hr>

                        <!-- Action Items -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="bx bx-task"></i> Action Items</h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-action-item-btn">
                                    <i class="bx bx-plus"></i> Add Action Item
                                </button>
                            </div>
                            <div id="action-items-list">
                                <!-- Action items added here -->
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="action_items">
                                    <i class="bx bx-save"></i> Save Action Items
                                </button>
                            </div>
                        </div>

                        <hr>

                        <!-- AOB -->
                        <div class="mb-4">
                            <h6><i class="bx bx-message-dots"></i> Any Other Business (AOB)</h6>
                            <textarea name="aob" class="form-control" rows="3" placeholder="Enter any other business discussed..."></textarea>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="aob">
                                    <i class="bx bx-save"></i> Save AOB
                                </button>
                            </div>
                        </div>

                        <hr>

                        <!-- Next Meeting -->
                        <div class="mb-4">
                            <h6><i class="bx bx-calendar-event"></i> Next Meeting</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="next_meeting_date" class="form-control datepicker" placeholder="Next meeting date">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="next_meeting_time" class="form-control timepicker" placeholder="Time">
                                </div>
                                <div class="col-md-4">
                                    <input type="text" name="next_meeting_venue" class="form-control" placeholder="Venue">
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-success btn-sm save-minutes-section" data-section="next_meeting">
                                    <i class="bx bx-save"></i> Save Next Meeting
                                </button>
                            </div>
                        </div>

                        <!-- Closing -->
                        <div class="mb-4">
                            <h6><i class="bx bx-time"></i> Meeting Closed</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="closing_time" class="form-control timepicker" placeholder="Closing time">
                                </div>
                                <div class="col-md-6">
                                    <textarea name="closing_remarks" class="form-control" rows="2" placeholder="Closing remarks"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary" id="preview-minutes-btn">
                    <i class="bx bx-show"></i> Preview
                </button>
                <button type="button" class="btn btn-success" id="save-all-minutes-btn">
                    <i class="bx bx-save"></i> Save All Minutes
                </button>
                <button type="button" class="btn btn-primary" id="finalize-minutes-btn">
                    <i class="bx bx-check"></i> Finalize Minutes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- External Participant Template -->
<template id="external-participant-template">
    <div class="participant-card external-participant">
        <button type="button" class="btn btn-sm btn-outline-danger remove-btn remove-external-btn">
            <i class="bx bx-x"></i>
        </button>
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="external_name[]" class="form-control form-control-sm" placeholder="Full Name *" required>
            </div>
            <div class="col-md-3">
                <input type="email" name="external_email[]" class="form-control form-control-sm" placeholder="Email">
            </div>
            <div class="col-md-3">
                <input type="text" name="external_phone[]" class="form-control form-control-sm" placeholder="Phone *" required>
                    </div>
                    <div class="col-md-3">
                <input type="text" name="external_institution[]" class="form-control form-control-sm" placeholder="Institution/Organization">
            </div>
        </div>
    </div>
</template>

<!-- Agenda Item Template -->
<template id="agenda-item-template">
    <div class="agenda-item" data-index="">
        <div class="d-flex align-items-start">
            <span class="drag-handle me-3"><i class="bx bx-menu"></i></span>
            <div class="flex-grow-1">
                <div class="row mb-2">
                    <div class="col-md-8">
                        <input type="text" name="agenda_title[]" class="form-control" placeholder="Agenda Item Title *" required>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="agenda_duration[]" class="form-control" placeholder="Duration (e.g., 15 mins)">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <select name="agenda_presenter[]" class="form-select presenter-select">
                            <option value="">Select Presenter</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="agenda_documents[]" class="form-control" placeholder="Supporting Documents (optional)">
                    </div>
                </div>
                <textarea name="agenda_description[]" class="form-control" rows="2" placeholder="Brief description or notes"></textarea>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger ms-2 remove-agenda-btn">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    </div>
</template>

<!-- Previous Action Template -->
<template id="previous-action-template">
    <div class="action-item previous-action mb-2">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="prev_action_description[]" class="form-control form-control-sm" placeholder="Action Description *" required>
            </div>
            <div class="col-md-2">
                <select name="prev_action_status[]" class="form-select form-select-sm">
                    <option value="done">Done</option>
                    <option value="in_progress">In Progress</option>
                    <option value="pending">Pending</option>
                    <option value="deferred">Deferred</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="prev_action_responsible[]" class="form-select form-select-sm responsible-select">
                    <option value="">Responsible Person</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="prev_action_remarks[]" class="form-control form-control-sm" placeholder="Remarks">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger remove-prev-action-btn">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<!-- Action Item Template -->
<template id="action-item-template">
    <div class="action-item new-action mb-2">
        <div class="row">
            <div class="col-md-4">
                <input type="text" name="action_description[]" class="form-control form-control-sm" placeholder="Action Description *" required>
            </div>
            <div class="col-md-3">
                <select name="action_responsible[]" class="form-select form-select-sm responsible-select">
                    <option value="">Responsible Person *</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="action_deadline[]" class="form-control form-control-sm datepicker" placeholder="Deadline">
            </div>
            <div class="col-md-2">
                <select name="action_priority[]" class="form-select form-select-sm">
                    <option value="normal">Normal</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger remove-action-btn">
                    <i class="bx bx-x"></i>
                </button>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    window.meetingAjaxUrl = '{{ route("modules.meetings.ajax") }}';
</script>
<script src="{{ asset('js/meetings.js') }}"></script>
@endpush
