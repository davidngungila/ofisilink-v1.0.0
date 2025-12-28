@extends('layouts.app')

@section('title', 'Leave Management')

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
                <i class="bx bx-calendar me-2"></i>Leave Management
                            </h4>
                            <p class="card-text text-white-50 mb-0">Manage employee leave requests and approvals</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>

    <!-- Search and Filters Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="bx bx-filter-alt me-2"></i>Search & Filters
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" id="global-search" placeholder="Search by name, type, reason...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="filter-status">
                                <option value="">All Status</option>
                                <option value="pending_hr_review">Pending HR</option>
                                <option value="pending_hod_approval">Pending HOD</option>
                                <option value="pending_ceo_approval">Pending CEO</option>
                                <option value="approved_pending_docs">Pending Docs</option>
                                <option value="on_leave">On Leave</option>
                                <option value="completed">Completed</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Leave Type</label>
                            <select class="form-select" id="filter-leave-type">
                                <option value="">All Leave Types</option>
                                @foreach($leaveTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($isManager)
                        <div class="col-md-2">
                            <label class="form-label">Department</label>
                            <select class="form-select" id="filter-department">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="col-md-{{ $isManager ? '3' : '5' }}">
                            <label class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="filter-date-from" placeholder="From">
                                <input type="date" class="form-control" id="filter-date-to" placeholder="To">
                                <button class="btn btn-outline-secondary" type="button" id="clear-filters" title="Clear Filters">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Tabbed Interface -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="leaveTabs" role="tablist">
                        <!-- Dashboard Tab -->
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab">
                                <i class="bx bx-bar-chart-alt-2 me-1"></i>Dashboard
                            </button>
                        </li>
                        
                        <!-- Request Management Tabs -->
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="my-requests-tab" data-bs-toggle="tab" data-bs-target="#my-requests" type="button" role="tab">
                                <i class="bx bx-history me-1"></i>My Requests
                                <span class="badge bg-primary ms-2" id="my-requests-count">{{ $myRequests->count() }}</span>
                            </button>
                        </li>
                        
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="awaiting-tab" data-bs-toggle="tab" data-bs-target="#awaiting" type="button" role="tab">
                                <i class="bx bx-time me-1"></i>Awaiting Action
                                <span class="badge bg-warning ms-2" id="awaiting-count">{{ $isManager ? $awaitingMyAction->count() : 0 }}</span>
                            </button>
                        </li>
                        
                        @if($isManager)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="all-requests-tab" data-bs-toggle="tab" data-bs-target="#all-requests" type="button" role="tab">
                                <i class="bx bx-globe me-1"></i>All Requests
                                <span class="badge bg-info ms-2" id="all-requests-count">{{ $allOtherRequests->count() }}</span>
                            </button>
                        </li>
                        @endif

                        <!-- Management Tabs -->
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-docs-tab" data-bs-toggle="tab" data-bs-target="#pending-docs" type="button" role="tab">
                                <i class="bx bx-file me-1"></i>Pending Documents
                                <span class="badge bg-secondary ms-2" id="pending-docs-count">{{ $stats['total_pending_docs'] ?? 0 }}</span>
                            </button>
                        </li>
                        
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="overdue-tab" data-bs-toggle="tab" data-bs-target="#overdue" type="button" role="tab">
                                <i class="bx bx-calendar-x me-1"></i>Overdue
                                <span class="badge bg-danger ms-2" id="overdue-count">0</span>
                            </button>
                        </li>
                        
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="records-tab" data-bs-toggle="tab" data-bs-target="#records" type="button" role="tab">
                                <i class="bx bx-list-ul me-1"></i>All Records
                            </button>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body">
                    <div class="tab-content" id="leaveTabsContent">
                        <!-- Dashboard Tab -->
                        <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                            <div class="row mb-4">
        @if($isManager)
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending HR Review</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_pending_hr'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-time-five fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending HOD</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_pending_hod'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-user-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Pending CEO</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_pending_ceo'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Currently on Leave</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_on_leave'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-calendar-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Pending Documents</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_pending_docs'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-file fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_this_month'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-calendar fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">My Pending</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['my_pending'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-time-five fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">My Approved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['my_approved'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">On Leave</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['my_on_leave'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-calendar-check fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Total Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_requests'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto"><i class="bx bx-list-ul fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

                            <!-- Action Buttons -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="card-title mb-0">
                                                <i class="bx bx-menu me-2"></i>Quick Actions
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-3 col-sm-6">
                                                    <button class="btn btn-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="openNewLeaveRequestModal()" style="min-height: 120px;">
                                                        <i class="bx bx-plus-circle" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
                                                        <span class="fw-bold">New Request</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <button class="btn btn-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="showBalanceManagement()" style="min-height: 120px;">
                                                        <i class="bx bx-calculator" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
                                                        <span class="fw-bold">Balance Management</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <button class="btn btn-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="showRecommendationsManagement()" style="min-height: 120px;">
                                                        <i class="bx bx-calendar-check" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
                                                        <span class="fw-bold">Recommendations</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <button class="btn btn-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="showLeaveTypesManagement()" style="min-height: 120px;">
                                                        <i class="bx bx-cog" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
                                                        <span class="fw-bold">Leave Types 2</span>
                                                    </button>
                                                </div>
                                                <div class="col-md-3 col-sm-6">
                                                    <button class="btn btn-secondary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3" onclick="showAnalytics()" style="min-height: 120px;">
                                                        <i class="bx bx-bar-chart-alt-2" style="font-size: 2.5rem; margin-bottom: 10px;"></i>
                                                        <span class="fw-bold">Analytics</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bx bx-info-circle"></i> <strong>Quick Access:</strong> Use the tabs above to navigate to different sections. Statistics are displayed here for a quick overview.
                            </div>
                        </div>
                        
                        <!-- New Request Tab -->
                        <div class="tab-pane fade" id="new-request" role="tabpanel">
                            <div class="text-center py-5">
                                <i class="bx bx-plus-circle" style="font-size: 4rem; color: #6c757d;"></i>
                                <h4 class="mt-3">Create New Leave Request</h4>
                                <p class="text-muted">Click the button below to start a new leave request</p>
                                <button class="btn btn-primary btn-lg mt-3" id="new-leave-request-btn">
                                    <i class="bx bx-plus-circle me-2"></i>Request Leave
                                </button>
                            </div>
                        </div>
                        
                        <!-- Awaiting My Action Tab -->
                        <div class="tab-pane fade" id="awaiting" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Requests Requiring Your Action</h6>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshTab('awaiting')">
                                        <i class="bx bx-refresh"></i> Refresh
                                    </button>
                                    @if($isHR || $isAdmin || $isManager)
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bx bx-check-double"></i> Bulk Actions
                                    </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="bulkApprove('awaiting'); return false;"><i class="bx bx-check text-success"></i> Approve Selected</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="bulkReject('awaiting'); return false;"><i class="bx bx-x text-danger"></i> Reject Selected</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="bulkProcess('awaiting'); return false;"><i class="bx bx-file text-info"></i> Process Documents</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="selectAllRequests('awaiting'); return false;"><i class="bx bx-check-square"></i> Select All</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="deselectAllRequests('awaiting'); return false;"><i class="bx bx-square"></i> Deselect All</a></li>
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div id="awaiting-requests" style="max-height: 70vh; overflow-y: auto;">
                                @if($isManager && $awaitingMyAction->isEmpty())
                        <p class='text-center text-muted mt-3'>No requests are waiting for your review. 👍</p>
                                @elseif($isManager)
                        @foreach($awaitingMyAction as $request)
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input request-checkbox" type="checkbox" value="{{ $request->id }}" id="request-{{ $request->id }}" data-status="{{ $request->status }}">
                                        <label class="form-check-label" for="request-{{ $request->id }}">
                            @include('modules.hr.partials.leave-request-card', [
                                'request' => $request, 
                                'isOwn' => false,
                                'isHR' => $isHR ?? false,
                                'isHOD' => $isHOD ?? false,
                                'isCEO' => $isCEO ?? false,
                                'isAdmin' => $isAdmin ?? false,
                                'user_department_id' => Auth::user()->primary_department_id ?? null
                            ])
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                                @else
                                    <p class='text-center text-muted mt-3'>No requests awaiting your action.</p>
                    @endif
                </div>
            </div>
                        
                        <!-- My Requests Tab -->
                        <div class="tab-pane fade" id="my-requests" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">My Leave Request History</h6>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshTab('my-requests')">
                                    <i class="bx bx-refresh"></i> Refresh
                                </button>
                </div>
                            <div id="my-requests-list" style="max-height: 70vh; overflow-y: auto;">
                    @if($myRequests->isEmpty())
                        <p class='text-center text-muted mt-3'>You have not made any leave requests yet.</p>
                    @else
                        @foreach($myRequests as $request)
                            @include('modules.hr.partials.leave-request-card', [
                                'request' => $request, 
                                'isOwn' => true,
                                'isHR' => $isHR ?? false,
                                'isHOD' => $isHOD ?? false,
                                'isCEO' => $isCEO ?? false,
                                'isAdmin' => $isAdmin ?? false,
                                'user_department_id' => Auth::user()->primary_department_id ?? null
                            ])
                        @endforeach
                    @endif
                            </div>
                        </div>
                        
                        @if($isManager)
                        <!-- All Requests Tab -->
                        <div class="tab-pane fade" id="all-requests" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">All Employee Leave Requests</h6>
                                <div class="btn-group" role="group">
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshTab('all-requests')">
                                        <i class="bx bx-refresh"></i> Refresh
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="exportTab('all-requests')">
                                        <i class="bx bx-export"></i> Export
                                    </button>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="bx bx-check-double"></i> Bulk Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="bulkApprove('all-requests'); return false;"><i class="bx bx-check text-success"></i> Approve Selected</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="bulkReject('all-requests'); return false;"><i class="bx bx-x text-danger"></i> Reject Selected</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="bulkProcess('all-requests'); return false;"><i class="bx bx-file text-info"></i> Process Documents</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="bulkCancel('all-requests'); return false;"><i class="bx bx-x-circle text-warning"></i> Cancel Selected</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="selectAllRequests('all-requests'); return false;"><i class="bx bx-check-square"></i> Select All</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="deselectAllRequests('all-requests'); return false;"><i class="bx bx-square"></i> Deselect All</a></li>
                                        </ul>
                                    </div>
                </div>
                            </div>
                            <div id="all-requests-list" style="max-height: 70vh; overflow-y: auto;">
                    @if($allOtherRequests->isEmpty())
                        <p class='text-center text-muted mt-3'>No other active requests in the system.</p>
                    @else
                        @foreach($allOtherRequests as $request)
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input request-checkbox" type="checkbox" value="{{ $request->id }}" id="request-{{ $request->id }}" data-status="{{ $request->status }}">
                                        <label class="form-check-label" for="request-{{ $request->id }}">
                            @include('modules.hr.partials.leave-request-card', [
                                'request' => $request, 
                                'isOwn' => false,
                                'isHR' => $isHR ?? false,
                                'isHOD' => $isHOD ?? false,
                                'isCEO' => $isCEO ?? false,
                                'isAdmin' => $isAdmin ?? false,
                                'user_department_id' => Auth::user()->primary_department_id ?? null
                            ])
                                        </label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                            </div>
                        </div>
                        
                        <!-- Issues & Alerts Tab -->
                        <div class="tab-pane fade" id="issues" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Issues, Alerts & Anomalies</h6>
                                <button class="btn btn-sm btn-outline-danger" onclick="refreshIssues()">
                                    <i class="bx bx-refresh"></i> Refresh Issues
                                </button>
                            </div>
                            <div id="issues-list" style="max-height: 70vh; overflow-y: auto;">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading issues...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading issues and alerts...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pending Documents Tab -->
                        <div class="tab-pane fade" id="pending-docs" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Leave Requests Pending Document Processing</h6>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshTab('pending-docs')">
                                    <i class="bx bx-refresh"></i> Refresh
                                </button>
                            </div>
                            <div id="pending-docs-list" style="max-height: 70vh; overflow-y: auto;">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading pending documents...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Overdue Tab -->
                        <div class="tab-pane fade" id="overdue" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Overdue Leave Requests & Pending Actions</h6>
                                <button class="btn btn-sm btn-outline-danger" onclick="refreshTab('overdue')">
                                    <i class="bx bx-refresh"></i> Refresh
                                </button>
                            </div>
                            <div id="overdue-list" style="max-height: 70vh; overflow-y: auto;">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading overdue requests...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Analytics Tab -->
                        <div class="tab-pane fade" id="analytics" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Leave Analytics Dashboard</h6>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" onclick="exportAnalytics()">
                                        <i class="bx bx-download"></i> Export
                                    </button>
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshAnalytics()">
                                        <i class="bx bx-refresh"></i> Refresh
                                    </button>
                                </div>
                            </div>
                            <div id="analytics-content"></div>
                        </div>
                        
                        <!-- All Records Tab -->
                        <div class="tab-pane fade" id="records" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">All Leave Records</h6>
                                <button class="btn btn-sm btn-outline-primary" onclick="exportRecords()">
                                    <i class="bx bx-download"></i> Export CSV
                                </button>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-2">
                                    <select class="form-select form-select-sm" id="records-filter">
                                        <option value="all">All Time</option>
                                        <option value="current_year">Current Year</option>
                                        <option value="last_30_days">Last 30 Days</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select form-select-sm" id="records-department-filter">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select class="form-select form-select-sm" id="records-status-filter">
                                        <option value="">All Status</option>
                                        <option value="pending_hr_review">Pending HR</option>
                                        <option value="pending_hod_approval">Pending HOD</option>
                                        <option value="pending_ceo_approval">Pending CEO</option>
                                        <option value="approved_pending_docs">Pending HR Docs</option>
                                        <option value="on_leave">On Leave</option>
                                        <option value="completed">Completed</option>
                                        <option value="rejected">Rejected</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <input type="date" class="form-control" id="records-date-from" placeholder="From Date">
                                        <input type="date" class="form-control" id="records-date-to" placeholder="To Date">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary btn-sm w-100" onclick="applyRecordsFilters()">
                                        <i class="bx bx-filter"></i> Apply Filters
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" onclick="selectAllRecords()">
                                        <i class="bx bx-check-square"></i> Select All
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary" onclick="deselectAllRecords()">
                                        <i class="bx bx-square"></i> Deselect All
                                    </button>
                                    <span class="ms-2 text-muted" id="selected-count">0 selected</span>
                                </div>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="bx bx-check-double"></i> Bulk Actions
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="bulkApprove('records'); return false;"><i class="bx bx-check text-success"></i> Approve Selected</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="bulkReject('records'); return false;"><i class="bx bx-x text-danger"></i> Reject Selected</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="bulkProcess('records'); return false;"><i class="bx bx-file text-info"></i> Process Documents</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="bulkExport('records'); return false;"><i class="bx bx-download text-primary"></i> Export Selected</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="records-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40">
                                                <input type="checkbox" id="select-all-records" onchange="toggleAllRecords(this)">
                                            </th>
                                            <th>Employee</th>
                                            <th>Department</th>
                                            <th>Leave Type</th>
                                            <th>Dates</th>
                                            <th>Days</th>
                                            <th>Status</th>
                                            <th>Applied On</th>
                                            <th>Reviewed By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="records-tbody"></tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Balance Management Tab -->
                        <div class="tab-pane fade" id="balance-management" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Employee Leave Balance Management</h6>
                                        <button class="btn btn-sm btn-primary" onclick="showBalanceModal()">
                                            <i class="bx bx-plus"></i> Manage Balance
                                        </button>
                                    </div>
                                </div>
                                    </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Year</label>
                                    <select class="form-select" id="balance-year-filter" onchange="loadBalanceManagement()">
                                        @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                            <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                    </div>
                                <div class="col-md-4">
                                    <label class="form-label">Search Employee</label>
                                    <input type="text" class="form-control" id="balance-search" placeholder="Search by name..." onkeyup="filterBalanceTable()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Department</label>
                                    <select class="form-select" id="balance-dept-filter" onchange="loadBalanceManagement()">
                                        <option value="">All Departments</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <button class="btn btn-sm btn-outline-primary" onclick="selectAllBalances()">
                                                <i class="bx bx-check-square"></i> Select All
                                            </button>
                                            <button class="btn btn-sm btn-outline-secondary" onclick="deselectAllBalances()">
                                                <i class="bx bx-square"></i> Deselect All
                                            </button>
                                            <span class="ms-2 text-muted" id="selected-balances-count">0 selected</span>
                                        </div>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                <i class="bx bx-check-double"></i> Bulk Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="bulkUpdateBalance(); return false;"><i class="bx bx-edit text-primary"></i> Update Selected Balances</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="bulkResetBalance(); return false;"><i class="bx bx-reset text-warning"></i> Reset Selected</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="bulkExportBalance(); return false;"><i class="bx bx-download text-info"></i> Export Selected</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover table-bordered" id="balance-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th width="40">
                                                        <input type="checkbox" id="select-all-balances" onchange="toggleAllBalances(this)">
                                                    </th>
                                                    <th>Employee</th>
                                                    <th>Department</th>
                                                    <th>Total Days</th>
                                                    <th>Days Taken</th>
                                                    <th>Remaining</th>
                                                    <th>Carry Forward</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="balance-table-body">
                                                <tr>
                                                    <td colspan="8" class="text-center py-4">
                                                        <div class="spinner-border text-primary" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                        <p class="mt-2 text-muted">Loading balances...</p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recommendations Tab -->
                        <div class="tab-pane fade" id="recommendations" role="tabpanel">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Leave Recommendations Management</h6>
                                        <button class="btn btn-sm btn-primary" onclick="showRecommendationModal()">
                                            <i class="bx bx-plus"></i> Add Recommendation
                                        </button>
                                    </div>
                                </div>
                                    </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Year</label>
                                    <select class="form-select" id="recommendation-year-filter" onchange="loadRecommendations()">
                                        @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                            <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Employee</label>
                                    <select class="form-select" id="recommendation-employee-filter" onchange="loadRecommendations()">
                                        <option value="">All Employees</option>
                                        @foreach(\App\Models\User::where('is_active', true)->orderBy('name')->get() as $emp)
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
                            <div class="card">
                                <div class="card-body">
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
                        @endif
                        
                        <!-- Completed Tab -->
                        <div class="tab-pane fade" id="completed" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Completed Leave Requests</h6>
                                <div>
                                    <button class="btn btn-sm btn-outline-primary" onclick="refreshTab('completed')">
                                        <i class="bx bx-refresh"></i> Refresh
                                    </button>
                                    <button class="btn btn-sm btn-outline-success" onclick="exportTab('completed')">
                                        <i class="bx bx-export"></i> Export
                                    </button>
                                </div>
                            </div>
                            <div id="completed-list" style="max-height: 70vh; overflow-y: auto;">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading completed requests...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rejected/Cancelled Tab -->
                        <div class="tab-pane fade" id="rejected" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">Rejected and Cancelled Requests</h6>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshTab('rejected')">
                                    <i class="bx bx-refresh"></i> Refresh
                                </button>
                            </div>
                            <div id="rejected-list" style="max-height: 70vh; overflow-y: auto;">
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Loading rejected/cancelled requests...</p>
                                </div>
                            </div>
                        </div>
                        
                        @if($isHR || $isAdmin)
                        <!-- Leave Types Management Tab -->
                        <div class="tab-pane fade" id="leave-types" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Leave Types Management</h6>
                                <button class="btn btn-sm btn-primary" onclick="showAddLeaveTypeModal()">
                                    <i class="bx bx-plus"></i> Add Leave Type
                                </button>
                            </div>
                            
                            <!-- Search and Filter Section -->
                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Search Leave Types</label>
                                            <input type="text" class="form-control" id="leave-types-search" 
                                                   placeholder="Search by name or description..." 
                                                   onkeyup="filterLeaveTypesTable()">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Filter by Status</label>
                                            <select class="form-select" id="leave-types-status-filter" onchange="filterLeaveTypesTable()">
                                                <option value="">All Status</option>
                                                <option value="active">Active Only</option>
                                                <option value="inactive">Inactive Only</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Filter by Approval</label>
                                            <select class="form-select" id="leave-types-approval-filter" onchange="filterLeaveTypesTable()">
                                                <option value="">All Types</option>
                                                <option value="requires">Requires Approval</option>
                                                <option value="no_approval">No Approval Required</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">&nbsp;</label>
                                            <button class="btn btn-sm btn-outline-secondary w-100" onclick="resetLeaveTypesFilters()">
                                                <i class="bx bx-refresh"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" id="leave-types-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Name</th>
                                            <th width="25%">Description</th>
                                            <th width="10%">Max Days/Year</th>
                                            <th width="10%">Requires Approval</th>
                                            <th width="10%">Paid Leave</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Requests</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="leave-types-table-body">
                                        @foreach($leaveTypes as $index => $type)
                                        <tr data-leave-type-id="{{ $type->id }}" 
                                            data-name="{{ strtolower($type->name) }}" 
                                            data-description="{{ strtolower($type->description ?? '') }}"
                                            data-status="{{ $type->is_active ? 'active' : 'inactive' }}"
                                            data-requires-approval="{{ $type->requires_approval ? 'requires' : 'no_approval' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td><strong>{{ $type->name }}</strong></td>
                                            <td>
                                                <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                                      title="{{ $type->description ?? 'N/A' }}">
                                                    {{ Str::limit($type->description ?? 'N/A', 50) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $type->max_days_per_year ?? $type->max_days ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                @if($type->requires_approval)
                                                    <span class="badge bg-info"><i class="bx bx-check-circle"></i> Yes</span>
                                                @else
                                                    <span class="badge bg-secondary"><i class="bx bx-x-circle"></i> No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($type->is_paid ?? true)
                                                    <span class="badge bg-success"><i class="bx bx-coin"></i> Paid</span>
                                                @else
                                                    <span class="badge bg-warning"><i class="bx bx-coin-stack"></i> Unpaid</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($type->is_active)
                                                    <span class="badge bg-success"><i class="bx bx-check"></i> Active</span>
                                                @else
                                                    <span class="badge bg-danger"><i class="bx bx-x"></i> Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $type->leave_requests_count ?? $type->leaveRequests()->count() }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editLeaveType({{ $type->id }})" 
                                                            data-bs-toggle="tooltip" title="Edit Leave Type">
                                                        <i class="bx bx-edit"></i>
                                                </button>
                                                    @if(($type->leave_requests_count ?? $type->leaveRequests()->count()) == 0)
                                                    <button class="btn btn-sm btn-outline-danger" onclick="deleteLeaveType({{ $type->id }})" 
                                                            data-bs-toggle="tooltip" title="Delete Leave Type">
                                                        <i class="bx bx-trash"></i>
                                                </button>
                                                @else
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="toggleLeaveTypeStatus({{ $type->id }})" 
                                                            data-bs-toggle="tooltip" title="{{ $type->is_active ? 'Deactivate' : 'Activate' }} Leave Type">
                                                        <i class="bx bx-{{ $type->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                                @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div id="leave-types-empty" class="text-center py-4 text-muted" style="display: none;">
                                    <i class="bx bx-info-circle"></i> No leave types found matching your search criteria.
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leave Request Modal -->
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="requestLeaveForm" enctype="multipart/form-data">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="requestModalTitle">New Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="action" id="request_action" value="request_leave">
                    <input type="hidden" name="request_id" id="request_id" value="">
                    
                    <h6>Employee Details</h6>
                    <div class="row bg-light p-3 rounded mb-4">
                        <div class="col-md-4"><strong>Name:</strong> {{ auth()->user()->name }}</div>
                        <div class="col-md-4"><strong>Department:</strong> {{ auth()->user()->primaryDepartment->name ?? 'N/A' }}</div>
                        <div class="col-md-4"><strong>Position:</strong> {{ auth()->user()->employee->position ?? 'N/A' }}</div>
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
                                                        Loading...
                                                    </div>
                                                </div>
                                                <div class="col">
                                                    <div class="progress progress-sm mr-2">
                                                        <div id="annual-balance-progress" class="progress-bar bg-primary" 
                                                             role="progressbar" style="width: 0%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-xs font-weight-bold text-gray-600 mt-1" id="annual-balance-details">
                                                Total: 28 days | Taken: 0 days | Remaining: 28 days
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
                                    <h6 class="m-0 font-weight-bold"><i class="bx bx-lightbulb"></i> Recommended Leave Periods</h6>
                                </div>
                                <div class="card-body">
                                    <div id="personal-recommendations" class="mb-3">
                                        <h6 class="text-success"><i class="bx bx-user-check"></i> Your Personal Recommendations</h6>
                                        <div id="personal-rec-list"></div>
                                    </div>
                                    <div id="optimal-periods">
                                        <h6 class="text-info"><i class="bx bx-trending-up"></i> Optimal Periods for Your Department</h6>
                                        <div id="optimal-periods-list"></div>
                                    </div>
                                    <small class="text-muted"><i class="bx bx-info-circle"></i> These recommendations help maintain office staffing levels</small>
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
                        <small><i class="bx bx-info-circle"></i> Add dependents who will be traveling with you for fare calculation purposes.</small>
                    </div>
                    <div id="dependents-container"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-dependent-btn">+ Add Dependent</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- HR Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bx bx-file-blank me-2"></i>Review Leave Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="review-modal-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading request details...</p>
                </div>
            </div>
            <div class="modal-footer" id="review-modal-footer" style="display: none;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btn-approve-review">
                    <i class="bx bx-check"></i> Approve
                </button>
                <button type="button" class="btn btn-danger" id="btn-reject-review">
                    <i class="bx bx-x"></i> Reject
                </button>
            </div>
        </div>
    </div>
</div>

<!-- HOD Review Modal -->
<div class="modal fade" id="hodReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="hodReviewForm">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bx bx-user-check me-2"></i>HOD Review
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="hod-review-request-id">
                    
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Review Instructions:</strong> Please carefully review the leave request and provide your decision with comments.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Decision *</label>
                        <select name="decision" id="hod-decision" class="form-select" required>
                            <option value="">-- Select Decision --</option>
                            <option value="approve">Approve & Forward to CEO</option>
                            <option value="reject">Reject</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comments *</label>
                        <textarea name="comments" id="hod-comments" class="form-control" rows="6" required 
                                  placeholder="Please provide detailed comments for your decision. This will be visible to the employee..."></textarea>
                        <small class="form-text text-muted">Minimum 10 characters required. Comments will be shared with the employee.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-send me-1"></i> Submit Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CEO Review Modal -->
<div class="modal fade" id="ceoReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="ceoReviewForm">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bx bx-check-circle me-2"></i>CEO Final Approval
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="ceo-review-request-id">
                    
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Final Approval:</strong> This is the final approval step. Your decision will be final.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Decision *</label>
                        <select name="decision" id="ceo-decision" class="form-select" required>
                            <option value="">-- Select Decision --</option>
                            <option value="approve">Approve - Proceed to Document Processing</option>
                            <option value="reject">Reject</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Comments *</label>
                        <textarea name="comments" id="ceo-comments" class="form-control" rows="6" required 
                                  placeholder="Please provide detailed comments for your decision. This will be visible to the employee..."></textarea>
                        <small class="form-text text-muted">Minimum 10 characters required. Comments will be shared with the employee.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-send me-1"></i> Submit Final Decision
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Details Modal -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-info-circle me-2"></i>Leave Request Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="view-details-body">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading request details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-outline-primary" id="btn-download-summary-pdf" style="display: none;">
                    <i class="bx bx-download"></i> Download Summary PDF
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Document Processing Modal -->
<div class="modal fade" id="advancedDocumentsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="advancedDocumentsForm">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bx bx-file-contract"></i> Process Leave Documents - Internal System
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="action" value="hr_process_documents_internal">
                    <input type="hidden" name="request_id" id="adv_doc_request_id">
                    
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle"></i> Complete all required information to generate internal leave documents.
                    </div>
                    
                    <!-- Leave Request Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Leave Request Summary</h6>
                        </div>
                        <div class="card-body" id="document-request-summary">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Leave Approval Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Leave Approval Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Approval Letter Number *</label>
                                        <input type="text" name="approval_letter_number" id="approval_letter_number" class="form-control" 
                                               placeholder="Auto-generated" readonly>
                                        <small class="form-text text-muted"><i class="bx bx-info-circle me-1"></i>Auto-generated official approval letter reference number</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Approval Date *</label>
                                        <input type="date" name="approval_date" class="form-control" 
                                               value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Leave Certificate Number</label>
                                        <input type="text" class="form-control" id="leave_cert_number" readonly>
                                        <small class="form-text text-muted">Auto-generated leave certificate number</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fare Payment Information -->
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-warning">
                                    <h6 class="mb-0">Fare Payment Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Fare Approved Amount (TZS) *</label>
                                        <div class="input-group">
                                            <span class="input-group-text">TZS</span>
                                            <input type="number" name="fare_approved_amount" class="form-control" 
                                                   value="0" min="0" step="1000" required>
                                        </div>
                                        <small class="form-text text-muted">Total approved fare amount for dependents</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Payment Voucher Number *</label>
                                        <input type="text" name="payment_voucher_number" id="payment_voucher_number" class="form-control" 
                                               placeholder="Auto-generated" readonly>
                                        <small class="form-text text-muted"><i class="bx bx-info-circle me-1"></i>Auto-generated payment voucher number</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Payment Date *</label>
                                        <input type="date" name="payment_date" class="form-control" 
                                               value="{{ date('Y-m-d') }}" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Fare Certificate Number</label>
                                        <input type="text" class="form-control" id="fare_cert_number" readonly>
                                        <small class="form-text text-muted">Auto-generated fare certificate number</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="mb-0">Additional Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Processing Notes</label>
                                <textarea name="additional_notes" class="form-control" rows="3" 
                                          placeholder="Any additional notes or special instructions..."></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Document Preview Section -->
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0">Document Preview</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <button type="button" class="btn btn-outline-primary w-100" id="preview-combined-cert">
                                        <i class="bx bx-show"></i> Preview Combined Certificate (Leave + Fare)
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-secondary w-100 mb-2" id="preview-leave-cert">
                                        <i class="bx bx-show"></i> Preview Leave Certificate Only
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-secondary w-100 mb-2" id="preview-fare-cert">
                                        <i class="bx bx-show"></i> Preview Fare Certificate Only
                                    </button>
                                </div>
                            </div>
                            <div id="document-preview" class="mt-3" style="display: none;">
                                <div class="border rounded p-3 bg-light">
                                    <div id="preview-content"></div>
                                    <div class="text-center mt-3">
                                        <button type="button" class="btn btn-success" id="print-preview">
                                            <i class="bx bx-printer"></i> Print Document
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="close-preview">
                                            Close Preview
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle"></i> Process & Generate Documents
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Return from Leave Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="returnForm" enctype="multipart/form-data">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="bx bx-log-in me-2"></i>Return from Leave Form
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="return_request_id">
                    
                    <div class="alert alert-success">
                        <i class="bx bx-check-circle me-2"></i>
                        <strong>Welcome back!</strong> Please complete this form to finalize your leave process and return to work.
                    </div>
                    
                    <div id="return-request-details" class="mb-4">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <p class="mt-2 mb-0 text-muted">Loading leave details...</p>
                        </div>
                    </div>
                    
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Return Information</h6>
                        </div>
                        <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Actual Return Date *</label>
                            <input type="date" name="actual_return_date" id="actual_return_date" class="form-control" required>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle me-1"></i>
                                        The date you actually returned to work. Must be on or after your leave end date.
                                    </small>
                        </div>
                        <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Health Status *</label>
                                    <select name="health_status" id="health_status" class="form-select" required>
                                        <option value="">-- Select Health Status --</option>
                                <option value="excellent">Excellent - Fully fit for work</option>
                                        <option value="good">Good - Minor issues, no restrictions</option>
                                        <option value="fair">Fair - Requires light duties temporarily</option>
                                <option value="poor">Poor - Requires medical attention</option>
                            </select>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Please accurately report your current health status
                                    </small>
                        </div>
                    </div>
                    
                            <div class="mb-0">
                                <label class="form-label fw-bold">Work Readiness *</label>
                                <select name="work_readiness" id="work_readiness" class="form-select" required>
                                    <option value="">-- Select Work Readiness --</option>
                                    <option value="fully_ready">Fully ready to resume all duties immediately</option>
                                    <option value="partially_ready">Partially ready - Need briefing on updates</option>
                                    <option value="needs_training">Needs refresher training before full duties</option>
                                    <option value="not_ready">Not ready - Requires accommodation or support</option>
                        </select>
                                <small class="form-text text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    This helps us plan your return and any necessary support
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bx bx-file me-2"></i>Documentation</h6>
                        </div>
                        <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Resumption Certificate/Medical Report</label>
                                <input type="file" name="resumption_certificate" id="resumption_certificate" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Upload medical certificate or resumption document if required (PDF, JPG, PNG - Max 2MB)
                                </small>
                                <div id="return-file-preview" class="mt-2" style="display: none;">
                                    <div class="alert alert-info py-2 mb-0">
                                        <i class="bx bx-file me-1"></i>
                                        <span id="return-file-name"></span>
                                        <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="$('#resumption_certificate').val(''); $('#return-file-preview').hide();">
                                            <i class="bx bx-x"></i> Remove
                                        </button>
                                    </div>
                                </div>
                    </div>
                    
                            <div class="mb-0">
                        <label class="form-label">Additional Comments</label>
                                <textarea name="comments" id="return_comments" class="form-control" rows="4" 
                                          placeholder="Any additional information about your return, special requirements, or concerns..."></textarea>
                                <small class="form-text text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Optional: Share any information that will help with your return to work
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle me-1"></i> Submit Return Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- HR Balance Management Modal -->
<div class="modal fade" id="balanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="balanceForm">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Employee Leave Balance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="action" value="hr_manage_leave_balance">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee *</label>
                            <select name="employee_id" class="form-select" required>
                                <option value="">-- Select Employee --</option>
                                @foreach(\App\Models\User::where('is_active', true)->orderBy('name')->get() as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->name }}@if(isset($emp->position)) ({{ $emp->position }})@endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Financial Year *</label>
                            <select name="financial_year" class="form-select" required>
                                @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                    <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Total Days Allotted *</label>
                            <input type="number" name="total_days_allotted" class="form-control" value="28" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Carry Forward Days</label>
                            <input type="number" name="carry_forward_days" class="form-control" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Balance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Recommendation Management Modal -->
<div class="modal fade" id="recommendationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="recommendationForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add Leave Recommendation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="action" value="add" id="recommendation_action">
                    <input type="hidden" name="recommendation_id" id="recommendation_id" value="">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee *</label>
                            <select name="employee_id" id="recommendation_employee_id" class="form-select" required>
                                <option value="">-- Select Employee --</option>
                                @foreach(\App\Models\User::where('is_active', true)->orderBy('name')->get() as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Financial Year *</label>
                            <select name="financial_year" id="recommendation_financial_year" class="form-select" required>
                                @for($i = date('Y'); $i <= date('Y') + 1; $i++)
                                    <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recommended Start Date *</label>
                            <input type="date" name="start_date" id="recommendation_start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Recommended End Date *</label>
                            <input type="date" name="end_date" id="recommendation_end_date" class="form-control" required>
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

<!-- Leave Type Management Modal -->
<div class="modal fade" id="leaveTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="leaveTypeForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="leaveTypeModalTitle">
                        <i class="bx bx-plus-circle me-2"></i>Add Leave Type
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="leave_type_id" id="leave_type_id">
                    
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Leave Type Information:</strong> Fill in the details below to create or update a leave type. All fields marked with * are required.
                    </div>
                    
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Basic Information</h6>
                        </div>
                        <div class="card-body">
                    <div class="mb-3">
                                <label class="form-label fw-bold">Name *</label>
                                <input type="text" name="name" id="leave_type_name" class="form-control" required 
                                       placeholder="e.g., Annual Leave, Sick Leave, Maternity Leave"
                                       maxlength="255">
                                <small class="form-text text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Enter a clear and descriptive name for this leave type. This name will be visible to all employees.
                                </small>
                                <div class="invalid-feedback" id="leave_type_name_error"></div>
                    </div>
                    
                            <div class="mb-0">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" id="leave_type_description" class="form-control" rows="4" 
                                          placeholder="Provide a detailed description of this leave type, including eligibility criteria, documentation requirements, etc..."
                                          maxlength="1000"></textarea>
                                <small class="form-text text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Optional: Add a detailed description to help employees understand this leave type better.
                                    <span id="description-char-count" class="float-end">0/1000 characters</span>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Leave Configuration</h6>
                        </div>
                        <div class="card-body">
                    <div class="mb-3">
                                <label class="form-label fw-bold">Maximum Days Per Year *</label>
                                <input type="number" name="max_days_per_year" id="leave_type_max_days" class="form-control" 
                                       required min="0" max="365" value="28" step="1">
                                <small class="form-text text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    The maximum number of days employees can take for this leave type per year. Common values: 28 (Annual), 14 (Sick), 90 (Maternity).
                                </small>
                                <div class="invalid-feedback" id="leave_type_max_days_error"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3 border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Settings & Options</h6>
                        </div>
                        <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-primary">
                                        <div class="card-body">
                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="requires_approval" 
                                                       id="leave_type_requires_approval" checked>
                                                <label class="form-check-label fw-bold" for="leave_type_requires_approval">
                                                    <i class="bx bx-check-double me-1"></i>Requires Approval
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                If enabled, leave requests of this type must go through approval workflow.
                                            </small>
                                        </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-success">
                                        <div class="card-body">
                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_paid" 
                                                       id="leave_type_is_paid" checked>
                                                <label class="form-check-label fw-bold" for="leave_type_is_paid">
                                                    <i class="bx bx-coin me-1"></i>Paid Leave
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                If enabled, employees will receive payment during this leave.
                                            </small>
                                        </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-success">
                                        <div class="card-body">
                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active" 
                                                       id="leave_type_is_active" checked>
                                                <label class="form-check-label fw-bold" for="leave_type_is_active">
                                                    <i class="bx bx-check-circle me-1"></i>Active
                                                </label>
                                            </div>
                                            <small class="text-muted d-block mt-2">
                                                Only active leave types are available for employees to select.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i> Save Leave Type
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.dependent-row { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
.dependent-row input, .dependent-row select { flex-grow: 1; }
.balance-box { font-size: 0.85rem; text-align: center; }
.balance-box strong { display: block; font-size: 1.1rem; }
.analytics-card { border-left: 4px solid #4e73df; }
.stats-number { font-size: 2rem; font-weight: bold; color: #4e73df; }
.table-responsive { max-height: 500px; }
.chart-container { position: relative; height: 300px; }
.loading-overlay { 
    position: absolute; 
    top: 0; left: 0; right: 0; bottom: 0; 
    background: rgba(255,255,255,0.8); 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    z-index: 1000; 
}
.fare-input-group { max-width: 200px; }
.return-form-section { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
.status-badge { font-size: 0.8rem; }
.recommendation-card { border-left: 4px solid #28a745; margin-bottom: 10px; }
.optimal-period-card { border-left: 4px solid #17a2b8; margin-bottom: 10px; }
.recommendation-badge { font-size: 0.7rem; }
.document-preview { max-height: 600px; overflow-y: auto; border: 1px solid #dee2e6; padding: 20px; background: white; }
.certificate-watermark { opacity: 0.1; position: absolute; font-size: 120px; transform: rotate(-45deg); top: 30%; left: 10%; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/chart.js/chart.umd.min.js') }}" onerror="this.onerror=null; this.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';"></script>
<script>
// Quick Action Functions for Dashboard Buttons - Navigate to dedicated pages
// These must be defined globally (outside document.ready) to be accessible from onclick handlers
function openNewLeaveRequestModal() {
    window.location.href = '{{ route("modules.hr.leave.new") }}';
}

function showBalanceManagement() {
    window.location.href = '{{ route("modules.hr.leave.balance") }}';
}

function showRecommendationsManagement() {
    window.location.href = '{{ route("modules.hr.leave.recommendations") }}';
}

function showLeaveTypesManagement() {
    window.location.href = '{{ route("leave.hr.leave-types") }}';
}

function showAnalytics() {
    window.location.href = '{{ route("modules.hr.leave.analytics") }}';
}

// Make all functions globally accessible immediately (before document.ready)
window.showAddLeaveTypeModal = function() {
    if (typeof $ !== 'undefined' && $('#leaveTypeModal').length) {
    $('#leaveTypeForm')[0].reset();
    $('#leave_type_id').val('');
        $('#leave_type_name, #leave_type_max_days').removeClass('is-invalid');
        $('#leave_type_name_error, #leave_type_max_days_error').text('');
        $('#description-char-count').text('0/1000 characters');
        $('#leaveTypeModalTitle').html('<i class="bx bx-plus-circle me-2"></i>Add Leave Type');
    $('#leaveTypeModal').modal('show');
    }
};

// Global Leave Type Functions - Must be accessible from inline onclick handlers
window.editLeaveType = function(id) {
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }
    
    // Show loading state on button
    const editBtn = $(`button[onclick*="editLeaveType(${id})"]`);
    const originalHtml = editBtn.length ? editBtn.html() : '';
    if (editBtn.length) {
        editBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
    }
    
    $.ajax({
        url: `/leave/hr/leave-types/${id}`,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function(response) {
            if (editBtn.length) {
                editBtn.prop('disabled', false).html(originalHtml);
            }
            
            if (response.success && response.leaveType) {
                const type = response.leaveType;
                
                // Clear previous validation errors
                $('#leave_type_name, #leave_type_max_days').removeClass('is-invalid');
                $('#leave_type_name_error, #leave_type_max_days_error').text('');
                
                // Populate form
                $('#leave_type_id').val(type.id);
                $('#leave_type_name').val(type.name || '');
                $('#leave_type_description').val(type.description || '');
                if (typeof updateDescriptionCharCount === 'function') {
                    updateDescriptionCharCount();
                }
                $('#leave_type_max_days').val(type.max_days_per_year || type.max_days || 28);
                $('#leave_type_requires_approval').prop('checked', type.requires_approval !== false);
                $('#leave_type_is_paid').prop('checked', type.is_paid !== false);
                $('#leave_type_is_active').prop('checked', type.is_active !== false);
                
                // Update modal title
                $('#leaveTypeModalTitle').html('<i class="bx bx-edit me-2"></i>Edit Leave Type');
                
                // Show modal
                $('#leaveTypeModal').modal('show');
            } else {
            if (typeof showLeaveTypeToast === 'function') {
                showLeaveTypeToast('danger', response.message || 'Failed to load leave type');
            } else if (typeof showLeaveTypeAlert === 'function') {
                showLeaveTypeAlert('Error', response.message || 'Failed to load leave type', 'danger');
            } else {
                alert('Error: ' + (response.message || 'Failed to load leave type'));
            }
            }
        },
        error: function(xhr) {
            if (editBtn.length) {
                editBtn.prop('disabled', false).html(originalHtml);
            }
            
            let errorMessage = 'Failed to load leave type. Please try again.';
            if (xhr.status === 403) {
                errorMessage = 'You do not have permission to edit leave types.';
            } else if (xhr.status === 404) {
                errorMessage = 'Leave type not found. It may have been deleted.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (typeof showLeaveTypeToast === 'function') {
                showLeaveTypeToast('danger', errorMessage);
            } else if (typeof showLeaveTypeAlert === 'function') {
                showLeaveTypeAlert('Error', errorMessage, 'danger');
            } else {
                alert('Error: ' + errorMessage);
            }
            
            // Refresh table if 404 (item was deleted)
            if (xhr.status === 404 && typeof refreshLeaveTypesTable === 'function') {
                setTimeout(function() {
                    refreshLeaveTypesTable();
                }, 2000);
            }
        }
    });
};

window.deleteLeaveType = function(id) {
    if (!confirm('Are you sure you want to delete this leave type? This action cannot be undone!')) {
        return;
    }
    
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }
    
    // Show loading state on button
    const deleteBtn = $(`button[onclick*="deleteLeaveType(${id})"]`);
    const originalHtml = deleteBtn.length ? deleteBtn.html() : '';
    if (deleteBtn.length) {
        deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
    }
    
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val() || '';
    
    $.ajax({
        url: `/leave/hr/leave-types/${id}`,
        type: 'DELETE',
        data: { 
            _token: csrfToken 
        },
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        success: function(response) {
            if (deleteBtn.length) {
                deleteBtn.prop('disabled', false).html(originalHtml);
            }
            
            if (response.success) {
                if (typeof showLeaveTypeToast === 'function') {
                    showLeaveTypeToast('success', response.message);
                } else if (typeof showLeaveTypeAlert === 'function') {
                    showLeaveTypeAlert('Success', response.message, 'success');
                } else {
                    alert('Success: ' + response.message);
                }
                // Refresh table after short delay to show success message
                if (typeof refreshLeaveTypesTable === 'function') {
                    setTimeout(function() {
                        refreshLeaveTypesTable();
                    }, 500);
                } else {
                    location.reload();
                }
            } else {
                if (typeof showLeaveTypeToast === 'function') {
                    showLeaveTypeToast('danger', response.message || 'Failed to delete leave type');
                } else if (typeof showLeaveTypeAlert === 'function') {
                    showLeaveTypeAlert('Error', response.message || 'Failed to delete leave type', 'danger');
                } else {
                    alert('Error: ' + (response.message || 'Failed to delete leave type'));
                }
            }
        },
        error: function(xhr) {
            if (deleteBtn.length) {
                deleteBtn.prop('disabled', false).html(originalHtml);
            }
            
            let errorMessage = 'Failed to delete leave type. Please try again.';
            
            if (xhr.status === 403) {
                errorMessage = 'You do not have permission to delete leave types.';
            } else if (xhr.status === 404) {
                errorMessage = 'Leave type not found. It may have already been deleted.';
                // Refresh table if 404
                if (typeof refreshLeaveTypesTable === 'function') {
                    setTimeout(function() {
                        refreshLeaveTypesTable();
                    }, 2000);
                } else {
                    location.reload();
                }
            } else if (xhr.status === 422) {
                // Validation error (e.g., has requests)
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (typeof showLeaveTypeToast === 'function') {
                showLeaveTypeToast('danger', errorMessage);
            } else if (typeof showLeaveTypeAlert === 'function') {
                showLeaveTypeAlert('Error', errorMessage, 'danger');
            } else {
                alert('Error: ' + errorMessage);
            }
        }
    });
};

window.toggleLeaveTypeStatus = function(id) {
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }
    
    // Get current leave type data
    const row = $(`tr[data-leave-type-id="${id}"]`);
    const isActive = row.length ? row.data('status') === 'active' : false;
    const newStatus = isActive ? 'Deactivate' : 'Activate';
    
    if (!confirm(`Are you sure you want to ${newStatus.toLowerCase()} this leave type?`)) {
        return;
    }
    
    // Show loading state on button
    const toggleBtn = $(`button[onclick*="toggleLeaveTypeStatus(${id})"]`);
    const originalHtml = toggleBtn.length ? toggleBtn.html() : '';
    if (toggleBtn.length) {
        toggleBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
    }
    
    const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val() || '';
    
    // Load the leave type first to get all data
    $.ajax({
        url: `/leave/hr/leave-types/${id}`,
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        success: function(response) {
            if (response.success && response.leaveType) {
                const type = response.leaveType;
                
                // Update via edit endpoint
                const formData = {
                    _token: csrfToken,
                    name: type.name,
                    description: type.description || '',
                    max_days_per_year: type.max_days_per_year || type.max_days || 28,
                    requires_approval: type.requires_approval ? 1 : 0,
                    is_paid: type.is_paid ? 1 : 0,
                    is_active: !isActive ? 1 : 0 // Toggle the status
                };
                
                $.ajax({
                    url: `/leave/hr/leave-types/${id}`,
                    type: 'PUT',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        if (toggleBtn.length) {
                            toggleBtn.prop('disabled', false).html(originalHtml);
                        }
                        
                        if (response.success) {
                            if (typeof showLeaveTypeToast === 'function') {
                                showLeaveTypeToast('success', response.message);
                            } else if (typeof showLeaveTypeAlert === 'function') {
                                showLeaveTypeAlert('Success', response.message, 'success');
                            } else {
                                alert('Success: ' + response.message);
                            }
                            // Refresh table after short delay
                            if (typeof refreshLeaveTypesTable === 'function') {
                                setTimeout(function() {
                                    refreshLeaveTypesTable();
                                }, 500);
                            } else {
                                location.reload();
                            }
                        } else {
                            if (typeof showLeaveTypeToast === 'function') {
                                showLeaveTypeToast('danger', response.message || 'Failed to update leave type status');
                            } else if (typeof showLeaveTypeAlert === 'function') {
                                showLeaveTypeAlert('Error', response.message || 'Failed to update leave type status', 'danger');
                            } else {
                                alert('Error: ' + (response.message || 'Failed to update leave type status'));
                            }
                        }
                    },
                    error: function(xhr) {
                        if (toggleBtn.length) {
                            toggleBtn.prop('disabled', false).html(originalHtml);
                        }
                        
                        let errorMessage = 'Failed to update leave type status. Please try again.';
                        
                        if (xhr.status === 403) {
                            errorMessage = 'You do not have permission to update leave types.';
                        } else if (xhr.status === 404) {
                            errorMessage = 'Leave type not found. It may have been deleted.';
                            if (typeof refreshLeaveTypesTable === 'function') {
                                setTimeout(function() {
                                    refreshLeaveTypesTable();
                                }, 2000);
                            } else {
                                location.reload();
                            }
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = Object.values(xhr.responseJSON.errors).flat();
                            errorMessage = errors.join(', ');
                        }
                        
                        if (typeof showLeaveTypeToast === 'function') {
                            showLeaveTypeToast('danger', errorMessage);
                        } else if (typeof showLeaveTypeAlert === 'function') {
                            showLeaveTypeAlert('Error', errorMessage, 'danger');
                        } else {
                            alert('Error: ' + errorMessage);
                        }
                    }
                });
            } else {
                if (toggleBtn.length) {
                    toggleBtn.prop('disabled', false).html(originalHtml);
                }
                if (typeof showLeaveTypeToast === 'function') {
                    showLeaveTypeToast('danger', response.message || 'Failed to load leave type details');
                } else if (typeof showLeaveTypeAlert === 'function') {
                    showLeaveTypeAlert('Error', response.message || 'Failed to load leave type details', 'danger');
                } else {
                    alert('Error: ' + (response.message || 'Failed to load leave type details'));
                }
            }
        },
        error: function(xhr) {
            if (toggleBtn.length) {
                toggleBtn.prop('disabled', false).html(originalHtml);
            }
            
            let errorMessage = 'Failed to load leave type details. Please try again.';
            
            if (xhr.status === 403) {
                errorMessage = 'You do not have permission to view leave types.';
            } else if (xhr.status === 404) {
                errorMessage = 'Leave type not found. It may have been deleted.';
                if (typeof refreshLeaveTypesTable === 'function') {
                    setTimeout(function() {
                        refreshLeaveTypesTable();
                    }, 2000);
                } else {
                    location.reload();
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            if (typeof showLeaveTypeToast === 'function') {
                showLeaveTypeToast('danger', errorMessage);
            } else if (typeof showLeaveTypeAlert === 'function') {
                showLeaveTypeAlert('Error', errorMessage, 'danger');
            } else {
                alert('Error: ' + errorMessage);
            }
        }
    });
};

window.showBalanceModal = function() {
    // Wait for jQuery to be ready
    if (typeof $ !== 'undefined' && $('#balanceModal').length) {
        $('#balanceForm')[0].reset();
        $('#balanceModal').modal('show');
    } else {
        setTimeout(window.showBalanceModal, 100);
    }
};

    window.showRecommendationModal = function() {
        // Wait for jQuery to be ready
        if (typeof $ !== 'undefined' && $('#recommendationModal').length) {
            $('#recommendationForm')[0].reset();
            $('#recommendation_id').val('');
            $('#recommendation_action').val('add');
            $('#recommendationModal .modal-title').text('Add Leave Recommendation');
            $('#recommendationModal').modal('show');
        } else {
            setTimeout(window.showRecommendationModal, 100);
        }
    };

window.refreshTab = function(tabName) {
    if (tabName === 'pending-docs') {
        loadPendingDocuments();
    } else {
    location.reload();
    }
};

window.editBalance = function(employeeId, year) {
    // Wait for jQuery and internal function to be ready
    if (typeof $ !== 'undefined' && window.editBalanceInternal) {
        window.editBalanceInternal(employeeId, year);
    } else {
        setTimeout(function() {
            window.editBalance(employeeId, year);
        }, 100);
    }
};

window.filterBalanceTable = function() {
    if (typeof $ !== 'undefined' && window.filterBalanceTableInternal) {
        window.filterBalanceTableInternal();
    } else {
        setTimeout(window.filterBalanceTable, 100);
    }
};

$(document).ready(function() {
    // Fallback if SweetAlert2 isn't loaded
    if (typeof window.Swal === 'undefined') {
        window.Swal = {
            fire: function(optsOrTitle, text, icon){
                if (typeof optsOrTitle === 'object') {
                    alert((optsOrTitle.title? (optsOrTitle.title+'\n') : '') + (optsOrTitle.text||''));
                } else {
                    alert((optsOrTitle? (optsOrTitle+'\n') : '') + (text||''));
                }
            }
        };
    }
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Calculate leave days
    function calculateDays() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());
        if ($('#start_date').val() && $('#end_date').val() && startDate <= endDate) {
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            $('#total_days').val(diffDays);
            validateLeaveDates();
        } else {
            $('#total_days').val('');
        }
    }

    // Load annual leave balance
    function loadAnnualLeaveBalance() {
        $.post('{{ route("leave.annual-balance") }}', { 
            _token: csrfToken,
            year: new Date().getFullYear()
        }, function(response) {
            if (response.success) {
                const balance = response.balance;
                const totalDays = balance.total_days_allotted || 28;
                const takenDays = balance.days_taken || 0;
                const remaining = totalDays - takenDays;
                const percentage = totalDays > 0 ? (takenDays / totalDays) * 100 : 0;
                
                $('#annual-balance-display').text(`${remaining} days remaining`);
                $('#annual-balance-progress').css('width', `${percentage}%`);
                $('#annual-balance-details').html(`
                    Total: ${totalDays} days | 
                    Taken: ${takenDays} days | 
                    Remaining: ${remaining} days
                `);
                
                // Update progress bar color based on usage
                if (percentage > 80) {
                    $('#annual-balance-progress').removeClass('bg-primary bg-warning').addClass('bg-danger');
                } else if (percentage > 50) {
                    $('#annual-balance-progress').removeClass('bg-primary bg-danger').addClass('bg-warning');
                } else {
                    $('#annual-balance-progress').removeClass('bg-warning bg-danger').addClass('bg-primary');
                }
            } else {
                // Show default values on error
                $('#annual-balance-display').text('28 days remaining');
                $('#annual-balance-progress').css('width', '0%');
                $('#annual-balance-details').html('Total: 28 days | Taken: 0 days | Remaining: 28 days');
            }
        }).fail(function(xhr) {
            console.error('Failed to load annual balance:', xhr);
            // Show default values on error
            $('#annual-balance-display').text('28 days remaining');
            $('#annual-balance-progress').css('width', '0%');
            $('#annual-balance-details').html('Total: 28 days | Taken: 0 days | Remaining: 28 days');
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
                    
                    // Personal recommendations
                    let personalHtml = '';
                    if (recommendations.length > 0) {
                        recommendations.forEach(rec => {
                            const startDate = new Date(rec.recommended_start_date).toLocaleDateString();
                            const endDate = new Date(rec.recommended_end_date).toLocaleDateString();
                            personalHtml += `
                                <div class="card recommendation-card">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${startDate} to ${endDate}</strong>
                                                <br><small class="text-muted">HR Recommended Period</small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-success use-recommendation-btn" 
                                                    data-start="${rec.recommended_start_date}" data-end="${rec.recommended_end_date}">
                                                Use This
                                            </button>
                                        </div>
                                    </div>
                                </div>`;
                        });
                    } else {
                        personalHtml = '<p class="text-muted">No personal recommendations available.</p>';
                    }
                    $('#personal-rec-list').html(personalHtml);
                    
                    // Optimal periods
                    let optimalHtml = '';
                    if (optimal_periods.length > 0) {
                        optimal_periods.forEach(period => {
                            const startDate = new Date(period.start_date).toLocaleDateString();
                            const endDate = new Date(period.end_date).toLocaleDateString();
                            optimalHtml += `
                                <div class="card optimal-period-card">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${period.period}</strong>
                                                <br><small class="text-muted">${period.reason}</small>
                                                <br><small>${startDate} to ${endDate}</small>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-info use-recommendation-btn" 
                                                    data-start="${period.start_date}" data-end="${period.end_date}">
                                                Use This
                                            </button>
                                        </div>
                                    </div>
                                </div>`;
                        });
                    } else {
                        optimalHtml = '<p class="text-muted">No optimal periods identified.</p>';
                    }
                    $('#optimal-periods-list').html(optimalHtml);
                }
            }
        });
    }

    // Validate leave dates against balance
    function validateLeaveDates() {
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());
        const totalDays = parseInt($('#total_days').val()) || 0;
        
        if ($('#start_date').val() && $('#end_date').val() && startDate <= endDate) {
            // Check if exceeds annual balance
            const remainingText = $('#annual-balance-display').text();
            const remainingMatch = remainingText.match(/(\d+)/);
            
            if (remainingMatch) {
                const remainingDays = parseInt(remainingMatch[1]);
                if (totalDays > remainingDays) {
                    $('#total_days').addClass('is-invalid');
                    $('#total_days').after('<div class="invalid-feedback">Exceeds your remaining annual leave balance of ' + remainingDays + ' days</div>');
                    return false;
                } else {
                    $('#total_days').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                }
            }
        }
        return true;
    }

    // Add dependent row
    function addDependentRow(dep = {}) {
        const key = Date.now() + Math.random();
        const name = dep.name || '';
        const relationship = dep.relationship || '';
        const dependentRow = `
            <div class="dependent-row" id="dep-row-${key}">
                <input type="text" name="dependent_name[${key}]" class="form-control" placeholder="Full Name" value="${name}" required>
                <select name="dependent_relationship[${key}]" class="form-select" style="width: 200px;" required>
                    <option value="">-- Select --</option>
                    <option value="Spouse" ${relationship === 'Spouse' ? 'selected' : ''}>Spouse</option>
                    <option value="Child" ${relationship === 'Child' ? 'selected' : ''}>Child</option>
                    <option value="Parent" ${relationship === 'Parent' ? 'selected' : ''}>Parent</option>
                </select>
                <input type="file" name="dependent_cert[${key}]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <button type="button" class="btn btn-sm btn-danger remove-dependent-btn" data-row-id="${key}">X</button>
            </div>`;
        $('#dependents-container').append(dependentRow);
    }

    // Event Listeners
    $('#requestModal').on('change', '#start_date, #end_date', calculateDays);
    $('#requestModal').on('click', '#add-dependent-btn', () => addDependentRow());
    $('#requestModal').on('click', '.remove-dependent-btn', function() {
        $(`#dep-row-${$(this).data('row-id')}`).remove();
    });

    // Use recommendation button
    $(document).on('click', '.use-recommendation-btn', function() {
        const startDate = $(this).data('start');
        const endDate = $(this).data('end');
        
        $('#start_date').val(startDate);
        $('#end_date').val(endDate);
        calculateDays();
        
        // Show success message
        Swal.fire({
            title: 'Dates Applied',
            text: 'Dates applied from recommendation',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });
    });

    // New Leave Request
    $('#new-leave-request-btn').on('click', function() {
        $.post('{{ route("leave.check-active") }}', { 
            _token: csrfToken
        }, function(response) {
            if (response.success) {
                $('#requestModalTitle').text('New Leave Request');
                $('#requestLeaveForm')[0].reset();
                $('#request_action').val('request_leave');
                $('#request_id').val('');
                $('#dependents-container').html('');
                $('#recommendations-section').hide();
                $('#requestModal').modal('show');
                
                // Load balance and recommendations
                loadAnnualLeaveBalance();
                loadLeaveRecommendations();
            } else {
                Swal.fire('Action Denied', response.message, 'warning');
            }
        });
    });

    // Submit Leave Request Form
    $('#requestLeaveForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        // Validate dates
        const startDate = new Date($('#start_date').val());
        const endDate = new Date($('#end_date').val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (startDate < today) {
            Swal.fire('Error', 'Start date cannot be in the past.', 'error');
            return;
        }
        
        if (endDate < startDate) {
            Swal.fire('Error', 'End date cannot be before start date.', 'error');
            return;
        }
        
        if (!validateLeaveDates()) {
            Swal.fire('Error', 'Please check your leave dates against your available balance.', 'error');
            return;
        }
        
        $.ajax({
            type: 'POST',
            url: '{{ route("leave.store") }}',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            beforeSend: function() {
                $('#requestModal .modal-footer button').prop('disabled', true);
                $('#requestModal .modal-footer').prepend('<div class="spinner-border spinner-border-sm me-2" role="status"></div>');
            },
            success: function(response) {
                $('#requestModal').modal('hide');
                if (response.success) {
                    Swal.fire('Success!', response.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                let errorMessage = 'Failed to submit leave request. Please try again.';
                console.error('Leave Request Error:', {xhr, status, error});
                
                // Handle different error types
                if (xhr.status === 422) {
                    // Validation errors
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errorList = '<ul class="mb-0 text-start">';
                        Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                            xhr.responseJSON.errors[key].forEach(function(msg) {
                                errorList += '<li>' + msg + '</li>';
                            });
                        });
                        errorList += '</ul>';
                        errorMessage = '<strong>Validation Errors:</strong>' + errorList;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                } else if (xhr.status === 500) {
                    // Server error
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = '<strong>Server Error:</strong><br>' + xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = '<strong>Server Error:</strong><br>' + response.message;
                            }
                        } catch (e) {
                            errorMessage = '<strong>Server Error (500):</strong><br>Internal server error occurred. Please contact system administrator.';
                        }
                    } else {
                        errorMessage = '<strong>Server Error (500):</strong><br>Internal server error occurred. Please check your input and try again.';
                    }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) errorMessage = response.message;
                    } catch (e) {
                        // Not JSON, might be HTML error page
                        errorMessage = '<strong>Error ' + xhr.status + ':</strong><br>' + (error || 'An unexpected error occurred.');
                    }
                } else {
                    errorMessage = '<strong>Error ' + (xhr.status || 'Unknown') + ':</strong><br>' + (error || 'Failed to submit leave request. Please check your connection and try again.');
                }
                
                // Show error in a modal-like alert within the page
                const errorAlert = $(`
                    <div class="alert alert-danger alert-dismissible fade show position-fixed top-50 start-50 translate-middle" 
                         style="z-index: 9999; min-width: 500px; max-width: 600px;" role="alert">
                        <h5 class="alert-heading"><i class="bx bx-error-circle me-2"></i>Submission Error!</h5>
                        <div>${errorMessage}</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
                $('body').append(errorAlert);
                setTimeout(function() {
                    errorAlert.fadeOut(function() {
                        $(this).remove();
                    });
                }, 5000);
            },
            complete: function() {
                $('#requestModal .modal-footer button').prop('disabled', false);
                $('#requestModal .modal-footer .spinner-border').remove();
            }
        });
    });

    // View Details Handler
    $(document).on('click', '.btn-view-details', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const requestId = $(this).data('id');
        if (!requestId) {
            console.error('Request ID not found');
            alert('Error: Request ID not found. Please refresh the page and try again.');
            return;
        }
        
        const viewModal = $('#viewDetailsModal');
        if (!viewModal.length) {
            console.error('View modal not found');
            alert('Error: View modal not found. Please refresh the page.');
            return;
        }
        
        // Show loading state
        $('#view-details-body').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading request details...</p>
            </div>
        `);
        $('#btn-download-summary-pdf').hide();
        viewModal.modal('show');
        
        $.get(`/leave/${requestId}`, function(response) {
            if(response.success) {
                const { details } = response;
                const dependents = details.dependents || [];
                
                // Format dates
                const startDate = new Date(details.start_date).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                });
                const endDate = new Date(details.end_date).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                });
                const createdDate = new Date(details.created_at).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });
                
                // Status badge
                const statusColors = {
                    'pending_hr_review': 'warning',
                    'pending_hod_approval': 'info',
                    'pending_ceo_approval': 'primary',
                    'approved_pending_docs': 'success',
                    'on_leave': 'success',
                    'completed': 'dark',
                    'rejected': 'danger',
                    'rejected_for_edit': 'danger',
                    'cancelled': 'secondary'
                };
                
                const statusText = {
                    'pending_hr_review': 'Pending HR Review',
                    'pending_hod_approval': 'Pending HOD Approval',
                    'pending_ceo_approval': 'Pending CEO Approval',
                    'approved_pending_docs': 'Approved - Pending Documents',
                    'on_leave': 'On Leave',
                    'completed': 'Completed',
                    'rejected': 'Rejected',
                    'rejected_for_edit': 'Rejected - Edit Required',
                    'cancelled': 'Cancelled'
                };
                
                const statusBadge = `<span class="badge bg-${statusColors[details.status] || 'secondary'}">${statusText[details.status] || details.status}</span>`;
                
                // Dependents HTML
                let dependentsHtml = '';
                if (dependents.length > 0) {
                    dependentsHtml = `
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-group me-2"></i>Dependents (${dependents.length})</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Relationship</th>
                                                <th>Fare Amount (TZS)</th>
                                                <th>Certificate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                    `;
                    dependents.forEach(function(dep) {
                        dependentsHtml += `
                            <tr>
                                <td><strong>${dep.name || 'N/A'}</strong></td>
                                <td>${dep.relationship || 'N/A'}</td>
                                <td class="text-end">${dep.fare_amount ? parseFloat(dep.fare_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '0.00'} TZS</td>
                                <td>${dep.certificate_path ? '<a href="/storage/' + dep.certificate_path + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bx bx-file"></i> View</a>' : 'N/A'}</td>
                            </tr>
                        `;
                    });
                    dependentsHtml += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Review timeline
                let timelineHtml = '';
                timelineHtml += `
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bx bx-history me-2"></i>Processing Timeline</h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="timeline-marker bg-primary rounded-circle me-3" style="width: 12px; height: 12px;"></div>
                                        <div>
                                            <strong>Request Submitted</strong>
                                            <div class="text-muted small">${createdDate}</div>
                                            <div class="text-muted small">by ${details.employee?.name || 'Employee'}</div>
                                        </div>
                                    </div>
                                </div>
                `;
                
                if (details.reviewed_at) {
                    const reviewedDate = new Date(details.reviewed_at).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                    timelineHtml += `
                        <div class="timeline-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="timeline-marker bg-info rounded-circle me-3" style="width: 12px; height: 12px;"></div>
                                <div>
                                    <strong>Reviewed</strong>
                                    <div class="text-muted small">${reviewedDate}</div>
                                    <div class="text-muted small">by ${details.reviewer?.name || 'Reviewer'}</div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                if (details.documents_processed_at) {
                    const processedDate = new Date(details.documents_processed_at).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                    timelineHtml += `
                        <div class="timeline-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="timeline-marker bg-success rounded-circle me-3" style="width: 12px; height: 12px;"></div>
                                <div>
                                    <strong>Documents Processed</strong>
                                    <div class="text-muted small">${processedDate}</div>
                                    <div class="text-muted small">by ${details.documentProcessor?.name || 'HR Officer'}</div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                if (details.return_submitted_at) {
                    const returnDate = new Date(details.return_submitted_at).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                    });
                    timelineHtml += `
                        <div class="timeline-item mb-3">
                            <div class="d-flex align-items-center">
                                <div class="timeline-marker bg-dark rounded-circle me-3" style="width: 12px; height: 12px;"></div>
                                <div>
                                    <strong>Return Form Submitted</strong>
                                    <div class="text-muted small">${returnDate}</div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                timelineHtml += `
                            </div>
                        </div>
                    </div>
                `;
                
                // Comments section
                let commentsHtml = '';
                if (details.hr_officer_comments || details.comments) {
                    commentsHtml = `
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-message-square-detail me-2"></i>Review Comments</h6>
                            </div>
                            <div class="card-body">
                    `;
                    if (details.hr_officer_comments) {
                        commentsHtml += `
                            <div class="alert alert-info mb-3">
                                <h6 class="alert-heading"><i class="bx bx-user me-2"></i>HR Officer Comments</h6>
                                <p class="mb-0">${details.hr_officer_comments}</p>
                            </div>
                        `;
                    }
                    if (details.comments) {
                        commentsHtml += `
                            <div class="alert alert-secondary mb-0">
                                <h6 class="alert-heading"><i class="bx bx-user-check me-2"></i>Reviewer Comments (${details.reviewer?.name || 'Reviewer'})</h6>
                                <p class="mb-0">${details.comments}</p>
                            </div>
                        `;
                    }
                    commentsHtml += `
                            </div>
                        </div>
                    `;
                }
                
                // Document information
                let documentsHtml = '';
                if (details.approval_letter_number || details.leave_certificate_number || details.fare_certificate_number) {
                    documentsHtml = `
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-file-blank me-2"></i>Document Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                    `;
                    if (details.approval_letter_number) {
                        documentsHtml += `
                            <div class="col-md-6 mb-2">
                                <strong>Approval Letter Number:</strong><br>
                                <span class="text-primary">${details.approval_letter_number}</span>
                                ${details.approval_date ? '<br><small class="text-muted">Date: ' + new Date(details.approval_date).toLocaleDateString() + '</small>' : ''}
                                <br><a href="/leave/${details.id}/pdf/approval-letter" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                    <i class="bx bx-download"></i> Download Approval Letter PDF
                                </a>
                            </div>
                        `;
                    }
                    if (details.leave_certificate_number) {
                        documentsHtml += `
                            <div class="col-md-6 mb-2">
                                <strong>Leave Certificate Number:</strong><br>
                                <span class="text-success">${details.leave_certificate_number}</span>
                            </div>
                        `;
                    }
                    if (details.fare_certificate_number) {
                        documentsHtml += `
                            <div class="col-md-6 mb-2">
                                <strong>Fare Certificate Number:</strong><br>
                                <span class="text-warning">${details.fare_certificate_number}</span>
                            </div>
                        `;
                    }
                    if (details.payment_voucher_number) {
                        documentsHtml += `
                            <div class="col-md-6 mb-2">
                                <strong>Payment Voucher Number:</strong><br>
                                <span class="text-info">${details.payment_voucher_number}</span>
                                ${details.payment_date ? '<br><small class="text-muted">Date: ' + new Date(details.payment_date).toLocaleDateString() + '</small>' : ''}
                            </div>
                        `;
                    }
                    documentsHtml += `
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="btn-group" role="group">
                                            <a href="/leave/${details.id}/pdf/approval-letter" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-file"></i> Approval Letter PDF
                                            </a>
                                            <a href="/leave/${details.id}/pdf/certificate" target="_blank" class="btn btn-sm btn-outline-success">
                                                <i class="bx bx-file"></i> Leave Certificate PDF
                                            </a>
                                            ${details.fare_certificate_number ? `
                                            <a href="/leave/${details.id}/pdf/fare-certificate" target="_blank" class="btn btn-sm btn-outline-warning">
                                                <i class="bx bx-file"></i> Fare Certificate PDF
                                            </a>
                                            ` : ''}
                                            <a href="/leave/${details.id}/pdf/summary" target="_blank" class="btn btn-sm btn-outline-info">
                                                <i class="bx bx-file"></i> Summary PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                // Return information
                let returnHtml = '';
                if (details.actual_return_date || details.health_status || details.work_readiness) {
                    const healthStatus = {
                        'excellent': 'Excellent',
                        'good': 'Good',
                        'fair': 'Fair',
                        'poor': 'Poor'
                    };
                    const workReadiness = {
                        'fully_ready': 'Fully Ready',
                        'partially_ready': 'Partially Ready',
                        'needs_training': 'Needs Training',
                        'not_ready': 'Not Ready'
                    };
                    
                    returnHtml = `
                        <div class="card mt-3 border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="bx bx-log-in me-2"></i>Return Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    ${details.actual_return_date ? `
                                    <div class="col-md-4 mb-2">
                                        <strong>Actual Return Date:</strong><br>
                                        <span>${new Date(details.actual_return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                                    </div>
                                    ` : ''}
                                    ${details.health_status ? `
                                    <div class="col-md-4 mb-2">
                                        <strong>Health Status:</strong><br>
                                        <span class="badge bg-success">${healthStatus[details.health_status] || details.health_status}</span>
                                    </div>
                                    ` : ''}
                                    ${details.work_readiness ? `
                                    <div class="col-md-4 mb-2">
                                        <strong>Work Readiness:</strong><br>
                                        <span class="badge bg-info">${workReadiness[details.work_readiness] || details.work_readiness}</span>
                                    </div>
                                    ` : ''}
                                </div>
                                ${details.return_comments ? `
                                <div class="mt-2">
                                    <strong>Return Comments:</strong><br>
                                    <p class="mb-0">${details.return_comments}</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    `;
                }
                
                // Build complete details HTML
                const detailsHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0 text-white"><i class="bx bx-user me-2"></i>Employee Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <th width="40%">Name:</th>
                                            <td><strong>${details.employee?.name || 'N/A'}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Employee ID:</th>
                                            <td>${details.employee?.employee?.employee_number || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Department:</th>
                                            <td>${details.employee?.primary_department?.name || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Position:</th>
                                            <td>${details.employee?.employee?.position || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td>${details.employee?.email || 'N/A'}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Leave Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <th width="40%">Status:</th>
                                            <td>${statusBadge}</td>
                                        </tr>
                                        <tr>
                                            <th>Leave Type:</th>
                                            <td><strong>${details.leave_type?.name || 'N/A'}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Start Date:</th>
                                            <td>${startDate}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date:</th>
                                            <td>${endDate}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Days:</th>
                                            <td><strong>${details.total_days || 0} days</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Location:</th>
                                            <td>${details.leave_location || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Applied On:</th>
                                            <td>${createdDate}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bx bx-file-blank me-2"></i>Reason for Leave</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">${details.reason || 'No reason provided'}</p>
                        </div>
                    </div>
                    
                    ${dependentsHtml}
                    
                    ${details.total_fare_approved > 0 ? `
                    <div class="card mt-3 border-warning">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0"><i class="bx bx-dollar me-2"></i>Fare Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Total Fare Approved:</strong><br>
                                    <span class="h5 text-success">${parseFloat(details.total_fare_approved).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} TZS</span>
                                </div>
                                ${details.fare_approved_amount > 0 ? `
                                <div class="col-md-6">
                                    <strong>Fare Approved Amount:</strong><br>
                                    <span class="h5 text-primary">${parseFloat(details.fare_approved_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})} TZS</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    ` : ''}
                    
                    ${documentsHtml}
                    
                    ${commentsHtml}
                    
                    ${timelineHtml}
                    
                    ${returnHtml}
                `;
                
                $('#view-details-body').html(detailsHtml);
                
                // Show download PDF button if available
                if (details.status !== 'cancelled') {
                    $('#btn-download-summary-pdf').show().off('click').on('click', function() {
                        window.open(`/leave/${requestId}/pdf/summary`, '_blank');
                    });
                }
            } else {
                $('#view-details-body').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>Error:</strong> ${response.message || 'Failed to load request details'}
                    </div>
                `);
            }
        }).fail(function(xhr) {
            let errorMsg = 'Failed to load request details';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#view-details-body').html(`
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${errorMsg}
                </div>
            `);
        });
    });

    // HR Review Request
    $(document).on('click', '.btn-review', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const requestId = $(this).data('id');
        if (!requestId) {
            console.error('Request ID not found');
            alert('Error: Request ID not found. Please refresh the page and try again.');
            return;
        }
        
        const reviewModal = $('#reviewModal');
        if (!reviewModal.length) {
            console.error('Review modal not found');
            alert('Error: Review modal not found. Please refresh the page.');
            return;
        }
        
        // Show loading state
        $('#review-modal-body').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading request details...</p>
            </div>
        `);
        $('#review-modal-footer').hide();
        reviewModal.modal('show');
        
        $.get(`/leave/${requestId}`, function(response) {
            if(response.success) {
                const { details } = response;
                const dependents = details.dependents || [];
                
                // Format dates
                const startDate = new Date(details.start_date).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                });
                const endDate = new Date(details.end_date).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                });
                
                let dependentsHtml = '';
                if (dependents.length > 0) {
                    dependentsHtml = `
                        <div class="card mt-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="bx bx-group me-2"></i>Dependents (${dependents.length})</h6>
                    </div>
                            <div class="card-body">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Fare Amount (TZS)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                    `;
                    dependents.forEach(function(dep) {
                        dependentsHtml += `
                            <tr>
                                <td>${dep.name || 'N/A'}</td>
                                <td>${dep.relationship || 'N/A'}</td>
                                <td>
                                    <input type="number" 
                                           name="fare_amount[${dep.id}]" 
                                           class="form-control form-control-sm fare-amount-input" 
                                           value="${dep.fare_amount || 0}" 
                                           min="0" 
                                           step="0.01"
                                           placeholder="0.00">
                                </td>
                            </tr>
                        `;
                    });
                    dependentsHtml += `
                                    </tbody>
                                </table>
                                <small class="text-muted">
                                    <i class="bx bx-info-circle me-1"></i>
                                    Enter fare amounts for each dependent if applicable.
                                </small>
                            </div>
                        </div>
                    `;
                }
                
                let modalContent = `
                        <div class="row">
                            <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="bx bx-user me-2"></i>Employee Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <th width="40%">Name:</th>
                                            <td><strong>${details.employee?.name || 'N/A'}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Department:</th>
                                            <td>${details.employee?.primary_department?.name || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Position:</th>
                                            <td>${details.employee?.employee?.position || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Email:</th>
                                            <td>${details.employee?.email || 'N/A'}</td>
                                        </tr>
                                </table>
                                </div>
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bx bx-calendar me-2"></i>Leave Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <th width="40%">Leave Type:</th>
                                            <td><strong>${details.leave_type?.name || 'N/A'}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Start Date:</th>
                                            <td>${startDate}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date:</th>
                                            <td>${endDate}</td>
                                        </tr>
                                        <tr>
                                            <th>Total Days:</th>
                                            <td><strong>${details.total_days || 0} days</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Location:</th>
                                            <td>${details.leave_location || 'N/A'}</td>
                                        </tr>
                                </table>
                                </div>
                            </div>
                            </div>
                        </div>
                        
                    <div class="card mt-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bx bx-file-blank me-2"></i>Reason for Leave</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-0">${details.reason || 'No reason provided'}</p>
                        </div>
                    </div>
                    
                    ${dependentsHtml}
                    
                    <div class="card mt-3 border-warning">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0"><i class="bx bx-edit me-2"></i>Your Review</h6>
                        </div>
                        <div class="card-body">
                        <form id="reviewForm">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <input type="hidden" name="request_id" value="${requestId}">
                            
                            <div class="mb-3">
                                    <label class="form-label fw-bold">Comments *</label>
                                    <textarea name="comments" 
                                              id="review-comments" 
                                              class="form-control" 
                                              rows="8" 
                                              required 
                                              placeholder="Please provide detailed comments for your review. This will be visible to the employee and other reviewers..."
                                              style="min-height: 150px; resize: vertical;"></textarea>
                                    <small class="form-text text-muted">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Minimum 10 characters required. Your comments will be shared with the employee.
                                    </small>
                            </div>
                        </form>
                        </div>
                    </div>
                `;
                
                $('#review-modal-body').html(modalContent);
                $('#review-modal-footer').show();
                
                // Store request ID for submit
                $('#reviewModal').data('request-id', requestId);
            } else {
                $('#review-modal-body').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>Error:</strong> ${response.message || 'Failed to load request details'}
                    </div>
                `);
            }
        }).fail(function(xhr) {
            let errorMsg = 'Failed to load request details';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#review-modal-body').html(`
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${errorMsg}
                </div>
            `);
        });
    });

    // Submit HR Review
    $('#btn-approve-review, #btn-reject-review').on('click', function() {
        const decision = $(this).attr('id') === 'btn-approve-review' ? 'approve' : 'reject';
        const requestId = $('#reviewModal').data('request-id');
        
        if (!requestId) {
            alert('Error: Request ID not found. Please refresh and try again.');
            return;
        }
        
        const comments = $('#review-comments').val().trim();
        if (!comments || comments.length < 10) {
            alert('Please provide comments with at least 10 characters.');
            $('#review-comments').focus();
            return;
        }
        
        // Collect fare amounts
        const fareAmounts = {};
        $('.fare-amount-input').each(function() {
            const name = $(this).attr('name');
            const match = name.match(/fare_amount\[(\d+)\]/);
            if (match) {
                fareAmounts[match[1]] = parseFloat($(this).val()) || 0;
            }
        });
        
        const formData = {
            _token: csrfToken,
            decision: decision,
            comments: comments,
            fare_amount: fareAmounts
        };
        
        // Disable buttons
        $('#review-modal-footer button').prop('disabled', true);
        const originalText = $(this).html();
        $(this).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');
        
        $.ajax({
            type: 'POST',
            url: `/leave/${requestId}/hr-review`,
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                $('#reviewModal').modal('hide');
                    // Show success message in a simple alert (no external popup)
                    const successMsg = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 400px;">' +
                        '<i class="bx bx-check-circle me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('body').append(successMsg);
                    setTimeout(function() {
                        successMsg.fadeOut(function() {
                            $(this).remove();
                            location.reload();
                        });
                    }, 3000);
                } else {
                    alert('Error: ' + (response.message || 'Failed to submit review'));
                    $('#review-modal-footer button').prop('disabled', false);
                    $('#btn-approve-review, #btn-reject-review').html(originalText);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to submit review. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('\n');
                }
                alert('Error: ' + errorMsg);
                $('#review-modal-footer button').prop('disabled', false);
                $('#btn-approve-review, #btn-reject-review').html(originalText);
            }
        });
    });
    
    // HOD Review
    $(document).on('click', '.btn-hod-review', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const requestId = $(this).data('id');
        if (!requestId) {
            console.error('Request ID not found');
            alert('Error: Request ID not found. Please refresh the page and try again.');
            return;
        }
        
        const hodModal = $('#hodReviewModal');
        if (!hodModal.length) {
            console.error('HOD review modal not found');
            alert('Error: HOD review modal not found. Please refresh the page.');
            return;
        }
        
        $('#hod-review-request-id').val(requestId);
        $('#hodReviewForm')[0].reset();
        $('#hod-review-request-id').val(requestId);
        hodModal.modal('show');
    });
    
    $('#hodReviewForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#hod-review-request-id').val();
        const decision = $('#hod-decision').val();
        const comments = $('#hod-comments').val().trim();
        
        if (!decision) {
            alert('Please select a decision.');
            return;
        }
        
        if (!comments || comments.length < 10) {
            alert('Please provide comments with at least 10 characters.');
            $('#hod-comments').focus();
            return;
        }
        
        const formData = {
            _token: csrfToken,
            decision: decision,
            comments: comments
        };
        
        $('#hodReviewForm button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');
        
        $.ajax({
            type: 'POST',
            url: `/leave/${requestId}/hod-review`,
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#hodReviewModal').modal('hide');
                    const successMsg = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 400px;">' +
                        '<i class="bx bx-check-circle me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('body').append(successMsg);
                    setTimeout(function() {
                        successMsg.fadeOut(function() {
                            $(this).remove();
                            location.reload();
                        });
                    }, 3000);
                } else {
                    alert('Error: ' + (response.message || 'Failed to submit review'));
                    $('#hodReviewForm button[type="submit"]').prop('disabled', false).html('<i class="bx bx-send me-1"></i> Submit Review');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to submit review. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert('Error: ' + errorMsg);
                $('#hodReviewForm button[type="submit"]').prop('disabled', false).html('<i class="bx bx-send me-1"></i> Submit Review');
            }
        });
    });
    
    // CEO Review
    $(document).on('click', '.btn-ceo-review', function() {
        const requestId = $(this).data('id');
        $('#ceo-review-request-id').val(requestId);
        $('#ceoReviewForm')[0].reset();
        $('#ceo-review-request-id').val(requestId);
        $('#ceoReviewModal').modal('show');
    });
    
    $('#ceoReviewForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#ceo-review-request-id').val();
        const decision = $('#ceo-decision').val();
        const comments = $('#ceo-comments').val().trim();
        
        if (!decision) {
            alert('Please select a decision.');
            return;
        }
        
        if (!comments || comments.length < 10) {
            alert('Please provide comments with at least 10 characters.');
            $('#ceo-comments').focus();
            return;
        }
        
        const formData = {
            _token: csrfToken,
            decision: decision,
            comments: comments
        };
        
        $('#ceoReviewForm button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');
        
        $.ajax({
            type: 'POST',
            url: `/leave/${requestId}/ceo-review`,
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#ceoReviewModal').modal('hide');
                    const successMsg = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 400px;">' +
                        '<i class="bx bx-check-circle me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('body').append(successMsg);
                    setTimeout(function() {
                        successMsg.fadeOut(function() {
                            $(this).remove();
                            location.reload();
                        });
                    }, 3000);
                } else {
                    alert('Error: ' + (response.message || 'Failed to submit review'));
                    $('#ceoReviewForm button[type="submit"]').prop('disabled', false).html('<i class="bx bx-send me-1"></i> Submit Final Decision');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to submit review. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert('Error: ' + errorMsg);
                $('#ceoReviewForm button[type="submit"]').prop('disabled', false).html('<i class="bx bx-send me-1"></i> Submit Final Decision');
            }
        });
    });

    // Cancel Request
    $(document).on('click', '.btn-cancel', function() {
        const requestId = $(this).data('id');
        
        if (!confirm('Are you sure you want to cancel this leave request? This action cannot be undone.')) {
            return;
        }
        
                $.ajax({
                    type: 'DELETE',
                    url: `/leave/${requestId}`,
                    data: { _token: csrfToken },
                    success: function(response) {
                        if (response.success) {
                    const successMsg = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 400px;">' +
                        '<i class="bx bx-check-circle me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('body').append(successMsg);
                    setTimeout(function() {
                        successMsg.fadeOut(function() {
                            $(this).remove();
                            location.reload();
                        });
                    }, 3000);
                        } else {
                    alert('Error: ' + (response.message || 'Failed to cancel request'));
                        }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to cancel request. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                    }
                alert('Error: ' + errorMsg);
            }
        });
    });

    // Load Pending Documents
    function loadPendingDocuments() {
        $('#pending-docs-list').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Loading pending documents...</p>
            </div>
        `);
        
        $.post('{{ route("leave.hr.all-requests") }}', {
            _token: csrfToken,
            status_filter: 'approved_pending_docs'
        }, function(response) {
            if (response.success && response.requests && response.requests.length > 0) {
                let html = '';
                response.requests.forEach(function(request) {
                    const startDate = new Date(request.start_date).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric' 
                    });
                    const endDate = new Date(request.end_date).toLocaleDateString('en-US', { 
                        year: 'numeric', month: 'short', day: 'numeric' 
                    });
                    
                    html += `
                        <div class="card mb-3 shadow-sm border-left-success">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="bx bx-file-doc me-2 text-success"></i>
                                            <h6 class="mb-0 fw-bold">${request.leave_type_name || 'Leave Request'}</h6>
                                            <span class="badge bg-success ms-2">Pending Document Processing</span>
                                        </div>
                                        <div class="row text-sm mt-2">
                                            <div class="col-md-6">
                                                <div class="mb-1">
                                                    <i class="bx bx-user me-1 text-muted"></i>
                                                    <strong>Employee:</strong> ${request.employee_name || 'N/A'}
                                                </div>
                                                <div class="mb-1">
                                                    <i class="bx bx-buildings me-1 text-muted"></i>
                                                    <strong>Department:</strong> ${request.department_name || 'N/A'}
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-1">
                                                    <i class="bx bx-calendar me-1 text-muted"></i>
                                                    <strong>Dates:</strong> ${startDate} - ${endDate}
                                                </div>
                                                <div class="mb-1">
                                                    <i class="bx bx-time me-1 text-muted"></i>
                                                    <strong>Days:</strong> ${request.total_days || 0} days
                                                </div>
                                            </div>
                                        </div>
                                        ${request.reason ? `
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="bx bx-message-square-detail me-1"></i>
                                                ${request.reason.length > 100 ? request.reason.substring(0, 100) + '...' : request.reason}
                                            </small>
                                        </div>
                                        ` : ''}
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="btn-group-vertical d-grid gap-2" role="group">
                                            <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="${request.id}">
                                                <i class="bx bx-show"></i> View Details
                                            </button>
                                            <button class="btn btn-sm btn-success btn-process-docs" data-id="${request.id}">
                                                <i class="bx bx-file-doc"></i> Process Documents
                                            </button>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                Applied ${new Date(request.created_at).toLocaleDateString()}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#pending-docs-list').html(html);
            } else {
                $('#pending-docs-list').html(`
                    <div class="text-center py-5">
                        <i class="bx bx-check-circle text-success" style="font-size: 48px;"></i>
                        <p class="mt-3 text-muted">No documents pending processing. All requests are up to date! ✅</p>
                    </div>
                `);
            }
        }).fail(function(xhr) {
            let errorMsg = 'Failed to load pending documents. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#pending-docs-list').html(`
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${errorMsg}
                </div>
            `);
        });
    }
    
    // Load pending documents when tab is shown
    $('#pending-docs-tab').on('shown.bs.tab', function() {
        loadPendingDocuments();
    });
    
    // Also load on page load if tab is active
    $(document).ready(function() {
        if ($('#pending-docs-tab').hasClass('active') || $('#pending-docs').hasClass('show active')) {
            loadPendingDocuments();
        }
    });
    
    // Process Documents Button Handler
    $(document).on('click', '.btn-process-docs', function() {
        const requestId = $(this).data('id');
        
        // Reset form first
        $('#advancedDocumentsForm')[0].reset();
        $('#adv_doc_request_id').val(requestId);
        $('#document-request-summary').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm"></div> <p class="mt-2 mb-0">Loading request details...</p></div>');
        
        // Load request details
        $.get(`/leave/${requestId}`, function(response) {
            if (response.success) {
                const { details } = response;
                const startDate = new Date(details.start_date).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                });
                const endDate = new Date(details.end_date).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                });
                
                // Populate summary
                let summaryHtml = `
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="40%">Employee:</th>
                                    <td><strong>${details.employee?.name || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td>${details.employee?.primary_department?.name || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th>Position:</th>
                                    <td>${details.employee?.employee?.position || 'N/A'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th width="40%">Leave Type:</th>
                                    <td><strong>${details.leave_type?.name || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <th>Dates:</th>
                                    <td>${startDate} - ${endDate}</td>
                                </tr>
                                <tr>
                                    <th>Total Days:</th>
                                    <td><strong>${details.total_days || 0} days</strong></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    ${details.reason ? `
                    <div class="mt-3">
                        <strong>Reason:</strong>
                        <p class="mb-0">${details.reason}</p>
                    </div>
                    ` : ''}
                `;
                
                $('#document-request-summary').html(summaryHtml);
                
                // Set default values
                const todayDate = new Date().toISOString().split('T')[0];
                $('#advancedDocumentsForm input[name="approval_date"]').val(todayDate);
                $('#advancedDocumentsForm input[name="payment_date"]').val(todayDate);
                
                // Calculate total fare from dependents if any
                let totalFare = 0;
                if (details.dependents && details.dependents.length > 0) {
                    details.dependents.forEach(function(dep) {
                        totalFare += parseFloat(dep.fare_amount || 0);
                    });
                }
                if (totalFare > 0) {
                    $('#advancedDocumentsForm input[name="fare_approved_amount"]').val(totalFare.toFixed(2));
                } else {
                    $('#advancedDocumentsForm input[name="fare_approved_amount"]').val('0.00');
                }
                
                // Generate auto certificate numbers for display in date-001 format
                const todayStr = todayDate.replace(/-/g, '');
                const leaveCertNumber = `LC-${todayStr}-001`; // Will be properly generated on backend
                const fareCertNumber = totalFare > 0 ? `FC-${todayStr}-001` : 'N/A'; // Will be properly generated on backend
                
                $('#leave_cert_number').val(leaveCertNumber);
                $('#fare_cert_number').val(fareCertNumber);
                
                // Auto-generate approval letter number in date-001 format
                const approvalDate = $('#advancedDocumentsForm input[name="approval_date"]').val();
                const approvalDateStr = approvalDate ? approvalDate.replace(/-/g, '') : new Date().toISOString().split('T')[0].replace(/-/g, '');
                const approvalLetterNumber = `${approvalDateStr}-001`; // Will be properly generated on backend
                $('#approval_letter_number').val(approvalLetterNumber);
                
                // Auto-generate payment voucher number in date-001 format
                const paymentDate = $('#advancedDocumentsForm input[name="payment_date"]').val();
                const paymentDateStr = paymentDate ? paymentDate.replace(/-/g, '') : new Date().toISOString().split('T')[0].replace(/-/g, '');
                const paymentVoucherNumber = `${paymentDateStr}-001`; // Will be properly generated on backend
                $('#payment_voucher_number').val(paymentVoucherNumber);
                
                // Update when dates change
                $('#advancedDocumentsForm input[name="approval_date"]').on('change', function() {
                    const dateStr = $(this).val().replace(/-/g, '');
                    $('#approval_letter_number').val(`${dateStr}-001`);
                });
                
                $('#advancedDocumentsForm input[name="payment_date"]').on('change', function() {
                    const dateStr = $(this).val().replace(/-/g, '');
                    $('#payment_voucher_number').val(`${dateStr}-001`);
                });
                
                // Show modal
                $('#advancedDocumentsModal').modal('show');
            } else {
                alert('Error: ' + (response.message || 'Failed to load request details'));
            }
        }).fail(function(xhr) {
            let errorMsg = 'Failed to load request details';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#document-request-summary').html(`
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${errorMsg}
                </div>
            `);
        });
    });
    
    // Reset modal when closed
    $('#advancedDocumentsModal').on('hidden.bs.modal', function() {
        $('#advancedDocumentsForm')[0].reset();
        $('#adv_doc_request_id').val('');
        $('#document-request-summary').html('');
        $('#leave_cert_number').val('');
        $('#fare_cert_number').val('');
    });
    
    // Submit Advanced Documents Form
    $('#advancedDocumentsForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#adv_doc_request_id').val();
        
        if (!requestId) {
            alert('Error: Request ID not found. Please refresh and try again.');
            return;
        }
        
        // Client-side validation
        const approvalDate = $('input[name="approval_date"]').val();
        const fareAmount = parseFloat($('input[name="fare_approved_amount"]').val()) || 0;
        const paymentDate = $('input[name="payment_date"]').val();
        
        if (!approvalDate) {
            alert('Please select the Approval Date.');
            $('input[name="approval_date"]').focus();
            return;
        }
        
        if (fareAmount < 0) {
            alert('Fare Approved Amount cannot be negative.');
            $('input[name="fare_approved_amount"]').focus();
            return;
        }
        
        if (!paymentDate) {
            alert('Please select the Payment Date.');
            $('input[name="payment_date"]').focus();
            return;
        }
        
        const formData = new FormData(this);
        
        // Disable submit button
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processing...');
        
        $.ajax({
            type: 'POST',
            url: `/leave/${requestId}/process-documents`,
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#advancedDocumentsModal').modal('hide');
                    const successMsg = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 400px;">' +
                        '<i class="bx bx-check-circle me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('body').append(successMsg);
                    setTimeout(function() {
                        successMsg.fadeOut(function() {
                            $(this).remove();
                            location.reload();
                        });
                    }, 3000);
                } else {
                    alert('Error: ' + (response.message || 'Failed to process documents'));
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to process documents. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('\n');
                }
                alert('Error: ' + errorMsg);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Document Preview Button Handlers
    $(document).on('click', '#preview-combined-cert', function() {
        const requestId = $('#adv_doc_request_id').val();
        
        if (!requestId) {
            alert('Error: Request ID not found. Please select a leave request first.');
            return;
        }
        
        // Show loading state
        $('#document-preview').show();
        $('#preview-content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading combined certificate preview...</p></div>');
        
        // Fetch preview HTML
        $.ajax({
            url: `/leave/${requestId}/preview/combined-certificate`,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                $('#preview-content').html(html);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load combined certificate preview.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#preview-content').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>Error:</strong> ${errorMsg}
                    </div>
                `);
            }
        });
    });
    
    $(document).on('click', '#preview-leave-cert', function() {
        const requestId = $('#adv_doc_request_id').val();
        
        if (!requestId) {
            alert('Error: Request ID not found. Please select a leave request first.');
            return;
        }
        
        // Show loading state
        $('#document-preview').show();
        $('#preview-content').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading leave certificate preview...</p></div>');
        
        // Fetch preview HTML
        $.ajax({
            url: `/leave/${requestId}/preview/certificate`,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                $('#preview-content').html(html);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load leave certificate preview.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                $('#preview-content').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error-circle me-2"></i>
                        <strong>Error:</strong> ${errorMsg}
                    </div>
                `);
            }
        });
    });
    
    $(document).on('click', '#preview-fare-cert', function() {
        const requestId = $('#adv_doc_request_id').val();
        
        if (!requestId) {
            alert('Error: Request ID not found. Please select a leave request first.');
            return;
        }
        
        // Show loading state
        $('#document-preview').show();
        $('#preview-content').html('<div class="text-center py-5"><div class="spinner-border text-warning" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Loading fare certificate preview...</p></div>');
        
        // Fetch preview HTML
        $.ajax({
            url: `/leave/${requestId}/preview/fare-certificate`,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                $('#preview-content').html(html);
            },
            error: function(xhr) {
                let errorMsg = 'Failed to load fare certificate preview.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMsg = 'Fare certificate not available for this leave request.';
                }
                $('#preview-content').html(`
                    <div class="alert alert-warning">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Notice:</strong> ${errorMsg}
                    </div>
                `);
            }
        });
    });
    
    // Close Preview Button
    $(document).on('click', '#close-preview', function() {
        $('#document-preview').hide();
        $('#preview-content').html('');
    });
    
    // Print Preview Button
    $(document).on('click', '#print-preview', function() {
        const printContent = $('#preview-content').html();
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Document Preview</title>
                <style>
                    body { margin: 0; padding: 20px; font-family: Arial, sans-serif; }
                    @media print {
                        body { margin: 0; padding: 0; }
                    }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        setTimeout(function() {
            printWindow.print();
        }, 250);
    });
    
    // Return from Leave Button Handler
    $(document).on('click', '.btn-return', function() {
        const requestId = $(this).data('id');
        
        // Reset form first
        $('#returnForm')[0].reset();
        $('#return_request_id').val(requestId);
        $('#return-request-details').html('<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> <p class="mt-2 mb-0 text-muted">Loading leave details...</p></div>');
        
        // Load request details
        $.get(`/leave/${requestId}`, function(response) {
            if (response.success) {
                const { details } = response;
                const startDate = new Date(details.start_date).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                });
                const endDate = new Date(details.end_date).toLocaleDateString('en-US', { 
                    year: 'numeric', month: 'long', day: 'numeric' 
                });
                const today = new Date().toISOString().split('T')[0];
                const minReturnDate = new Date(details.end_date).toISOString().split('T')[0];
                
                // Populate summary
                let summaryHtml = `
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Leave Request Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <th width="40%">Leave Type:</th>
                                            <td><strong>${details.leave_type?.name || 'N/A'}</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Start Date:</th>
                                            <td>${startDate}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date:</th>
                                            <td>${endDate}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <th width="40%">Total Days:</th>
                                            <td><strong>${details.total_days || 0} days</strong></td>
                                        </tr>
                                        <tr>
                                            <th>Location:</th>
                                            <td>${details.leave_location || 'N/A'}</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td><span class="badge bg-success">On Leave</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#return-request-details').html(summaryHtml);
                
                // Set default return date (today or end date, whichever is later)
                const defaultReturnDate = today >= minReturnDate ? today : minReturnDate;
                $('#actual_return_date').val(defaultReturnDate);
                $('#actual_return_date').attr('min', minReturnDate);
                $('#actual_return_date').attr('max', new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);
                
                // Show modal
                $('#returnModal').modal('show');
            } else {
                alert('Error: ' + (response.message || 'Failed to load leave details'));
            }
        }).fail(function(xhr) {
            let errorMsg = 'Failed to load leave details';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#return-request-details').html(`
                <div class="alert alert-danger">
                    <i class="bx bx-error-circle me-2"></i>
                    <strong>Error:</strong> ${errorMsg}
                </div>
            `);
        });
    });
    
    // File preview for return form
    $(document).on('change', '#resumption_certificate', function() {
        const file = this.files[0];
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must not exceed 2MB. Please select a smaller file.');
                $(this).val('');
                return;
            }
            $('#return-file-name').text(file.name + ' (' + (file.size / 1024).toFixed(2) + ' KB)');
            $('#return-file-preview').show();
        } else {
            $('#return-file-preview').hide();
        }
    });
    
    // Submit Return Form
    $('#returnForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#return_request_id').val();
        
        if (!requestId) {
            alert('Error: Request ID not found. Please refresh and try again.');
            return;
        }
        
        // Client-side validation
        const actualReturnDate = $('#actual_return_date').val();
        const healthStatus = $('#health_status').val();
        const workReadiness = $('#work_readiness').val();
        
        if (!actualReturnDate) {
            alert('Please select your actual return date.');
            $('#actual_return_date').focus();
            return;
        }
        
        if (!healthStatus) {
            alert('Please select your health status.');
            $('#health_status').focus();
            return;
        }
        
        if (!workReadiness) {
            alert('Please select your work readiness status.');
            $('#work_readiness').focus();
            return;
        }
        
        const formData = new FormData(this);
        
        // Disable submit button
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: `/leave/${requestId}/return-form`,
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#returnModal').modal('hide');
                    const successMsg = $('<div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 400px;">' +
                        '<i class="bx bx-check-circle me-2"></i>' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('body').append(successMsg);
                    setTimeout(function() {
                        successMsg.fadeOut(function() {
                            $(this).remove();
                            location.reload();
                        });
                    }, 3000);
                } else {
                    alert('Error: ' + (response.message || 'Failed to submit return form'));
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to submit return form. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMsg = errors.join('\n');
                }
                alert('Error: ' + errorMsg);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Reset return modal when closed
    $('#returnModal').on('hidden.bs.modal', function() {
        $('#returnForm')[0].reset();
        $('#return_request_id').val('');
        $('#return-request-details').html('');
        $('#return-file-preview').hide();
    });
    
    // Reset leave type modal when closed
    $('#leaveTypeModal').on('hidden.bs.modal', function() {
        $('#leaveTypeForm')[0].reset();
        $('#leave_type_id').val('');
        $('#leave_type_name, #leave_type_max_days').removeClass('is-invalid');
        $('#leave_type_name_error, #leave_type_max_days_error').text('');
        $('#description-char-count').text('0/1000 characters');
    });

    // Tab Management Functions - Already defined globally above
    function refreshTab(tabName) {
        if (tabName === 'pending-docs') {
            loadPendingDocuments();
        } else {
        window.refreshTab(tabName);
        }
    }

    function exportTab(tabName) {
        const infoMsg = $('<div class="alert alert-info alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index: 9999; min-width: 400px;">' +
            '<i class="bx bx-info-circle me-2"></i>Export functionality for ' + tabName + ' will be implemented soon.' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
            '</div>');
        $('body').append(infoMsg);
        setTimeout(function() {
            infoMsg.fadeOut(function() {
                $(this).remove();
            });
        }, 4000);
    }

    function refreshIssues() {
        $('#issues-list').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading issues...</p></div>');
        // Load issues logic will be implemented
        setTimeout(() => {
            $('#issues-list').html('<p class="text-center text-muted py-4">No issues found. All systems operational! ✅</p>');
        }, 1000);
    }

    // Make functions globally accessible
    window.showBalanceModal = function() {
        $('#balanceForm')[0].reset();
        $('#balanceModal').modal('show');
    };
    
    window.showRecommendationModal = function() {
        $('#recommendationForm')[0].reset();
        $('#recommendation_id').val('');
        $('#recommendation_action').val('add');
        $('#recommendationModal .modal-title').text('Add Leave Recommendation');
        $('#recommendationModal').modal('show');
    };
    
    // Also define locally for backward compatibility
    function showBalanceModal() {
        window.showBalanceModal();
    }

    function showRecommendationModal() {
        window.showRecommendationModal();
    }

    // Load Balance Management Data
    function loadBalanceManagement() {
        const year = $('#balance-year-filter').val();
        const deptId = $('#balance-dept-filter').val();
        
        $('#balance-table-body').html('<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></td></tr>');
        
        $.post('{{ route("leave.hr.balance-data") }}', {
            _token: csrfToken,
            year: year,
            department_id: deptId
        }, function(response) {
            if (response.success && response.data) {
                renderBalanceTable(response.data);
            } else {
                $('#balance-table-body').html('<tr><td colspan="7" class="text-center py-4 text-muted">No balance data available. Click "Manage Balance" to add new records.</td></tr>');
            }
        }).fail(function(xhr) {
            let errorMsg = 'Failed to load balance data.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            $('#balance-table-body').html(`<tr><td colspan="7" class="text-center py-4 text-danger">${errorMsg}</td></tr>`);
        });
    }

    function renderBalanceTable(balances) {
        let html = '';
        if (balances && balances.length > 0) {
            balances.forEach(balance => {
                const financialYear = balance.financial_year || $('#balance-year-filter').val();
                const remainingDays = balance.remaining_days !== undefined ? balance.remaining_days : (balance.total_days_allotted || 0) - (balance.days_taken || 0);
                html += `
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input balance-checkbox" value="${balance.employee_id}" data-year="${financialYear}" id="balance-${balance.employee_id}-${financialYear}">
                        </td>
                        <td>${balance.employee_name || 'N/A'}</td>
                        <td>${balance.department_name || 'N/A'}</td>
                        <td><strong>${balance.total_days_allotted || 0}</strong></td>
                        <td>${balance.days_taken || 0}</td>
                        <td><span class="badge bg-success">${remainingDays}</span></td>
                        <td>${balance.carry_forward_days || 0}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="editBalance(${balance.employee_id}, ${financialYear})">
                                <i class="bx bx-edit"></i> Edit
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="8" class="text-center py-4 text-muted">No balance records found. Click "Manage Balance" to add new records.</td></tr>';
        }
        $('#balance-table-body').html(html);
        updateBalanceSelectionCount();
    }

    // Make editBalance globally accessible
    window.editBalanceInternal = function(employeeId, year) {
        // Load existing balance data
        $.post('{{ route("leave.hr.balance-data") }}', {
            _token: csrfToken,
            year: year,
            employee_id: employeeId
        }, function(response) {
            if (response.success && response.data && response.data.length > 0) {
                const balance = response.data[0];
                $('#balanceForm select[name="employee_id"]').val(employeeId);
                $('#balanceForm select[name="financial_year"]').val(year);
                $('#balanceForm input[name="total_days_allotted"]').val(balance.total_days_allotted || 28);
                $('#balanceForm input[name="carry_forward_days"]').val(balance.carry_forward_days || 0);
            } else {
                // New balance - set defaults
                $('#balanceForm select[name="employee_id"]').val(employeeId);
                $('#balanceForm select[name="financial_year"]').val(year);
                $('#balanceForm input[name="total_days_allotted"]').val(28);
                $('#balanceForm input[name="carry_forward_days"]').val(0);
            }
            $('#balanceModal').modal('show');
        }).fail(function() {
            // Fallback - just set employee and year
            $('#balanceForm select[name="employee_id"]').val(employeeId);
            $('#balanceForm select[name="financial_year"]').val(year);
            $('#balanceModal').modal('show');
        });
    };
    
    // Update global function to use internal one
    window.editBalance = function(employeeId, year) {
        window.editBalanceInternal(employeeId, year);
    };
    
    // Also define locally for backward compatibility
    function editBalance(employeeId, year) {
        window.editBalanceInternal(employeeId, year);
    }

    // Make filterBalanceTable globally accessible
    window.filterBalanceTableInternal = function() {
        const search = $('#balance-search').val().toLowerCase();
        $('#balance-table tbody tr').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(search));
        });
    };
    
    window.filterBalanceTable = function() {
        window.filterBalanceTableInternal();
    };
    
    function filterBalanceTable() {
        window.filterBalanceTableInternal();
    }

    // Load Recommendations Data
    function loadRecommendations() {
        const year = $('#recommendation-year-filter').val();
        const employeeId = $('#recommendation-employee-filter').val();
        const deptId = $('#recommendation-dept-filter').val();
        
        $('#recommendations-content').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>');
        
        $.post('{{ route("leave.hr.manage-recommendations") }}', {
            _token: csrfToken,
            action: 'list',
            financial_year: year,
            employee_id: employeeId,
            department_id: deptId
        }, function(response) {
            if (response.success && response.data) {
                renderRecommendations(response.data);
            } else {
                $('#recommendations-content').html('<p class="text-center text-muted py-4">No recommendations found. Click "Add Recommendation" to create new ones.</p>');
            }
        }).fail(function() {
            $('#recommendations-content').html('<p class="text-center text-danger py-4">Failed to load recommendations.</p>');
        });
    }

    function renderRecommendations(recommendations) {
        let html = '<div class="table-responsive"><table class="table table-hover table-bordered"><thead class="table-light"><tr><th width="40"><input type="checkbox" id="select-all-recommendations" onchange="toggleAllRecommendations(this)"></th><th>Employee</th><th>Department</th><th>Recommended Period</th><th>Days</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
        
        if (recommendations && recommendations.length > 0) {
            recommendations.forEach(rec => {
                const startDate = new Date(rec.recommended_start_date).toLocaleDateString();
                const endDate = new Date(rec.recommended_end_date).toLocaleDateString();
                const days = Math.ceil((new Date(rec.recommended_end_date) - new Date(rec.recommended_start_date)) / (1000 * 60 * 60 * 24)) + 1;
                
                html += `
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input recommendation-checkbox" value="${rec.id}" id="rec-${rec.id}" data-start="${rec.recommended_start_date}" data-end="${rec.recommended_end_date}" data-employee="${rec.employee_id}">
                        </td>
                        <td>${rec.employee_name || 'N/A'}</td>
                        <td>${rec.department_name || 'N/A'}</td>
                        <td>${startDate} - ${endDate}</td>
                        <td><span class="badge bg-info">${days} days</span></td>
                        <td><span class="badge bg-success">Active</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-success me-1" onclick="createLeaveFromRecommendation(${rec.id})" title="Create Leave Request">
                                <i class="bx bx-plus-circle"></i> Create Leave
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="deleteRecommendation(${rec.id})">
                                <i class="bx bx-trash"></i> Remove
                            </button>
                        </td>
                    </tr>
                `;
            });
        } else {
            html += '<tr><td colspan="7" class="text-center py-4 text-muted">No recommendations found.</td></tr>';
        }
        
        html += '</tbody></table></div>';
        $('#recommendations-content').html(html);
        updateRecommendationSelectionCount();
    }

    function deleteRecommendation(id) {
        Swal.fire({
            title: 'Remove Recommendation?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("leave.hr.manage-recommendations") }}', {
                    _token: csrfToken,
                    action: 'remove',
                    recommendation_id: id
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Removed!', response.message, 'success');
                        loadRecommendations();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    }

    function exportAnalytics() {
        Swal.fire('Info', 'Analytics export will be implemented soon.', 'info');
    }

    function refreshAnalytics() {
        loadAnalytics();
    }

    function exportRecords() {
        Swal.fire('Info', 'Records export will be implemented soon.', 'info');
    }

    function applyRecordsFilters() {
        loadHRRecords();
    }

    // Global Search and Filters
    $('#global-search, #filter-status, #filter-leave-type, #filter-department, #filter-date-from, #filter-date-to').on('change input', function() {
        // Filter logic will be implemented
    });

    $('#clear-filters').on('click', function() {
        $('#global-search').val('');
        $('#filter-status').val('');
        $('#filter-leave-type').val('');
        $('#filter-department').val('');
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        location.reload();
    });

    // HR Management Functions
    @if($isHR || $isAdmin)
    
    function loadHRRecords() {
        const filter = $('#records-filter').val();
        const department = $('#records-department-filter').val();
        const status = $('#records-status-filter').val();
        const dateFrom = $('#records-date-from').val();
        const dateTo = $('#records-date-to').val();
        
        $('#records-tbody').html('<tr><td colspan="10" class="text-center"><div class="spinner-border"></div><p>Loading records...</p></td></tr>');
        
        $.post('{{ route("leave.hr.all-requests") }}', { 
            _token: csrfToken,
            filter: filter,
            department_filter: department,
            status_filter: status,
            date_from: dateFrom,
            date_to: dateTo
        }, function(response) {
            if (response.success) {
                let tbody = '';
                if (response.requests.length === 0) {
                    tbody = '<tr><td colspan="10" class="text-center text-muted">No records found matching your criteria.</td></tr>';
                } else {
                    response.requests.forEach(request => {
                        const statusColors = {
                            'pending_hr_review': 'warning',
                            'pending_hod_approval': 'info', 
                            'pending_ceo_approval': 'primary',
                            'approved_pending_docs': 'success',
                            'on_leave': 'success',
                            'completed': 'dark',
                            'rejected': 'danger',
                            'rejected_for_edit': 'danger',
                            'cancelled': 'secondary'
                        };
                        const statusText = {
                            'pending_hr_review': 'Pending HR',
                            'pending_hod_approval': 'Pending HOD',
                            'pending_ceo_approval': 'Pending CEO',
                            'approved_pending_docs': 'Pending HR Docs',
                            'on_leave': 'On Leave',
                            'completed': 'Completed',
                            'rejected': 'Rejected',
                            'rejected_for_edit': 'Rejected - Edit',
                            'cancelled': 'Cancelled'
                        };

                        tbody += `<tr>
                            <td>
                                <input type="checkbox" class="form-check-input record-checkbox" value="${request.id}" id="record-${request.id}" data-status="${request.status}">
                            </td>
                            <td>${request.employee_name}</td>
                            <td>${request.department_name || 'N/A'}</td>
                            <td>${request.leave_type_name}</td>
                            <td>${new Date(request.start_date).toLocaleDateString()} to ${new Date(request.end_date).toLocaleDateString()}</td>
                            <td>${request.total_days}</td>
                            <td><span class="badge bg-${statusColors[request.status]}">${statusText[request.status]}</span></td>
                            <td>${new Date(request.created_at).toLocaleDateString()}</td>
                            <td>${request.reviewed_by_name || 'N/A'}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="${request.id}">View</button>
                                ${request.status === 'pending_hr_review' ? `<button class="btn btn-sm btn-outline-warning btn-review" data-id="${request.id}">Review</button>` : ''}
                                ${request.status === 'approved_pending_docs' ? `<button class="btn btn-sm btn-outline-success btn-process-docs" data-id="${request.id}">Process Docs</button>` : ''}
                            </td>
                        </tr>`;
                    });
                }
                $('#records-tbody').html(tbody);
                updateRecordSelectionCount();
            }
        });
    }
    
    // Bulk Operations Functions
    function getSelectedRequests(context) {
        let checkboxes;
        if (context === 'records') {
            checkboxes = $('.record-checkbox:checked');
        } else {
            checkboxes = $(`#${context}-requests .request-checkbox:checked, #${context}-list .request-checkbox:checked`);
        }
        return checkboxes.map(function() { return $(this).val(); }).get();
    }
    
    function selectAllRequests(context) {
        if (context === 'records') {
            $('.record-checkbox').prop('checked', true);
            $('#select-all-records').prop('checked', true);
            updateRecordSelectionCount();
        } else {
            $(`#${context}-requests .request-checkbox, #${context}-list .request-checkbox`).prop('checked', true);
        }
    }
    
    function deselectAllRequests(context) {
        if (context === 'records') {
            $('.record-checkbox').prop('checked', false);
            $('#select-all-records').prop('checked', false);
            updateRecordSelectionCount();
        } else {
            $(`#${context}-requests .request-checkbox, #${context}-list .request-checkbox`).prop('checked', false);
        }
    }
    
    function toggleAllRecords(checkbox) {
        $('.record-checkbox').prop('checked', checkbox.checked);
        updateRecordSelectionCount();
    }
    
    function updateRecordSelectionCount() {
        const count = $('.record-checkbox:checked').length;
        $('#selected-count').text(`${count} selected`);
    }
    
    function toggleAllBalances(checkbox) {
        $('.balance-checkbox').prop('checked', checkbox.checked);
        updateBalanceSelectionCount();
    }
    
    function selectAllBalances() {
        $('.balance-checkbox').prop('checked', true);
        $('#select-all-balances').prop('checked', true);
        updateBalanceSelectionCount();
    }
    
    function deselectAllBalances() {
        $('.balance-checkbox').prop('checked', false);
        $('#select-all-balances').prop('checked', false);
        updateBalanceSelectionCount();
    }
    
    function updateBalanceSelectionCount() {
        const count = $('.balance-checkbox:checked').length;
        $('#selected-balances-count').text(`${count} selected`);
    }
    
    function toggleAllRecommendations(checkbox) {
        $('.recommendation-checkbox').prop('checked', checkbox.checked);
        updateRecommendationSelectionCount();
    }
    
    function selectAllRecommendations() {
        $('.recommendation-checkbox').prop('checked', true);
        $('#select-all-recommendations').prop('checked', true);
        updateRecommendationSelectionCount();
    }
    
    function deselectAllRecommendations() {
        $('.recommendation-checkbox').prop('checked', false);
        $('#select-all-recommendations').prop('checked', false);
        updateRecommendationSelectionCount();
    }
    
    function updateRecommendationSelectionCount() {
        const count = $('.recommendation-checkbox:checked').length;
        $('#selected-recommendations-count').text(`${count} selected`);
    }
    
    // Bulk Approve
    function bulkApprove(context) {
        const selected = getSelectedRequests(context);
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one request.', 'warning');
            return;
        }
        
        Swal.fire({
            title: `Approve ${selected.length} Request(s)?`,
            text: "This will approve all selected leave requests.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, approve all!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("leave.hr.bulk-operations") }}', {
                    _token: csrfToken,
                    action: 'approve',
                    request_ids: selected
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    }
    
    // Bulk Reject
    function bulkReject(context) {
        const selected = getSelectedRequests(context);
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one request.', 'warning');
            return;
        }
        
        Swal.fire({
            title: `Reject ${selected.length} Request(s)?`,
            text: "This will reject all selected leave requests.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reject all!',
            input: 'textarea',
            inputPlaceholder: 'Enter rejection reason (optional)...'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("leave.hr.bulk-operations") }}', {
                    _token: csrfToken,
                    action: 'reject',
                    request_ids: selected,
                    reason: result.value || 'Bulk rejection'
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    }
    
    // Bulk Process Documents
    function bulkProcess(context) {
        const selected = getSelectedRequests(context);
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one request.', 'warning');
            return;
        }
        
        $.post('{{ route("leave.hr.bulk-operations") }}', {
            _token: csrfToken,
            action: 'process_documents',
            request_ids: selected
        }, function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        });
    }
    
    // Bulk Cancel
    function bulkCancel(context) {
        const selected = getSelectedRequests(context);
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one request.', 'warning');
            return;
        }
        
        Swal.fire({
            title: `Cancel ${selected.length} Request(s)?`,
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel all!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("leave.hr.bulk-operations") }}', {
                    _token: csrfToken,
                    action: 'cancel',
                    request_ids: selected
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    }
    
    // Bulk Export
    function bulkExport(context) {
        const selected = getSelectedRequests(context);
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one request.', 'warning');
            return;
        }
        
        window.location.href = `{{ route("leave.hr.bulk-operations") }}?action=export&ids=${selected.join(',')}`;
    }
    
    // Balance Bulk Operations
    function bulkUpdateBalance() {
        const selected = $('.balance-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one balance.', 'warning');
            return;
        }
        
        Swal.fire({
            title: 'Bulk Update Balance',
            html: `
                <div class="mb-3">
                    <label>Days to Add/Set:</label>
                    <input type="number" class="form-control" id="bulk-days" value="28" min="0">
                </div>
                <div class="mb-3">
                    <label>Carry Forward Days:</label>
                    <input type="number" class="form-control" id="bulk-carry" value="0" min="0">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Update All',
            preConfirm: () => {
                return {
                    days: $('#bulk-days').val(),
                    carry: $('#bulk-carry').val()
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const balances = selected.map(function() {
                    return {
                        employee_id: $(this).val(),
                        year: $(this).data('year')
                    };
                }).get();
                
                $.post('{{ route("leave.hr.bulk-operations") }}', {
                    _token: csrfToken,
                    action: 'bulk_update_balance',
                    balances: balances,
                    total_days_allotted: result.value.days,
                    carry_forward_days: result.value.carry
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => loadBalanceManagement());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    }
    
    function bulkResetBalance() {
        const selected = $('.balance-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one balance.', 'warning');
            return;
        }
        
        Swal.fire({
            title: `Reset ${selected.length} Balance(s)?`,
            text: "This will reset days taken to 0 for selected balances.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reset!'
        }).then((result) => {
            if (result.isConfirmed) {
                const balances = selected.map(function() {
                    return {
                        employee_id: $(this).val(),
                        year: $(this).data('year')
                    };
                }).get();
                
                $.post('{{ route("leave.hr.bulk-operations") }}', {
                    _token: csrfToken,
                    action: 'bulk_reset_balance',
                    balances: balances
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => loadBalanceManagement());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    }
    
    function bulkExportBalance() {
        const selected = $('.balance-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one balance.', 'warning');
            return;
        }
        
        const ids = selected.map(function() { return $(this).val(); }).get();
        window.location.href = `{{ route("leave.hr.bulk-operations") }}?action=export_balance&ids=${ids.join(',')}`;
    }
    
    // Recommendation Operations
    function autoAssignRecommendations() {
        Swal.fire({
            title: 'Auto-Assign Recommendation Dates',
            text: 'This will automatically assign recommended dates to employees based on their leave balance and workload.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Generate Recommendations'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("leave.hr.manage-recommendations") }}', {
                    _token: csrfToken,
                    action: 'auto_assign'
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => loadRecommendations());
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    }
    
    function createLeaveFromRecommendation(recId) {
        const rec = $(`#rec-${recId}`);
        if (rec.length) {
            const startDate = rec.data('start');
            const endDate = rec.data('end');
            const employeeId = rec.data('employee');
            
            // Pre-fill the leave request form
            $('#start_date').val(startDate);
            $('#end_date').val(endDate);
            $('#requestModal').modal('show');
        }
    }
    
    function bulkCreateLeaveFromRecommendations() {
        const selected = $('.recommendation-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one recommendation.', 'warning');
            return;
        }
        
        Swal.fire({
            title: `Create ${selected.length} Leave Request(s)?`,
            text: "This will create leave requests for all selected recommendations.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, create all!'
        }).then((result) => {
            if (result.isConfirmed) {
                const recommendations = selected.map(function() {
                    return $(this).val();
                }).get();
                
                $.post('{{ route("leave.hr.bulk-operations") }}', {
                    _token: csrfToken,
                    action: 'create_from_recommendations',
                    recommendation_ids: recommendations
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            loadRecommendations();
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            }
        });
    }
    
    function bulkDeleteRecommendations() {
        const selected = $('.recommendation-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one recommendation.', 'warning');
            return;
        }
        
        Swal.fire({
            title: `Delete ${selected.length} Recommendation(s)?`,
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete all!'
        }).then((result) => {
            if (result.isConfirmed) {
                const ids = selected.map(function() { return $(this).val(); }).get();
                
                $.post('{{ route("leave.hr.manage-recommendations") }}', {
                    _token: csrfToken,
                    action: 'bulk_remove',
                    recommendation_ids: ids
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
    
    function bulkExportRecommendations() {
        const selected = $('.recommendation-checkbox:checked');
        if (selected.length === 0) {
            Swal.fire('No Selection', 'Please select at least one recommendation.', 'warning');
            return;
        }
        
        const ids = selected.map(function() { return $(this).val(); }).get();
        window.location.href = `{{ route("leave.hr.bulk-operations") }}?action=export_recommendations&ids=${ids.join(',')}`;
    }
    
    // Update selection counts on checkbox change
    $(document).on('change', '.record-checkbox, .balance-checkbox, .recommendation-checkbox, .request-checkbox', function() {
        if ($(this).hasClass('record-checkbox')) {
            updateRecordSelectionCount();
        } else if ($(this).hasClass('balance-checkbox')) {
            updateBalanceSelectionCount();
        } else if ($(this).hasClass('recommendation-checkbox')) {
            updateRecommendationSelectionCount();
        }
    });

    // Load Analytics
    function loadAnalytics() {
        $('#analytics-content').html('<div class="text-center"><div class="spinner-border"></div><p>Loading analytics...</p></div>');
        
        $.post('{{ route("leave.hr.analytics") }}', { 
            _token: csrfToken
        }, function(response) {
            if (response.success) {
                const { stats, monthly_trend, dept_stats, type_stats, status_stats } = response;
                
                let analyticsHtml = `
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Requests</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">${stats.total_requests}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bx bx-calendar fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">${stats.completed_requests}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bx bx-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Active Requests</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">${stats.active_requests}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bx bx-sync fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg. Leave Days</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">${parseFloat(stats.avg_leave_days || 0).toFixed(1)}</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bx bx-bar-chart-alt-2 fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Monthly Trend</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="monthlyChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Department Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="departmentChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Leave Type Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Leave Type</th>
                                                <th>Requests</th>
                                                <th>Avg. Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${type_stats.map(type => `
                                                <tr>
                                                    <td>${type.leave_type}</td>
                                                    <td>${type.request_count}</td>
                                                    <td>${parseFloat(type.avg_days || 0).toFixed(1)}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Status Distribution</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${status_stats.map(status => `
                                                <tr>
                                                    <td><span class="badge bg-secondary">${status.status}</span></td>
                                                    <td>${status.count}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                $('#analytics-content').html(analyticsHtml);
                
                // Render charts
                renderMonthlyChart(monthly_trend);
                renderDepartmentChart(dept_stats);
            }
        });
    }

    function renderMonthlyChart(monthly_trend) {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        const ctxEl = document.getElementById('monthlyChart');
        if (!ctxEl) {
            console.warn('Monthly chart canvas not found');
            return;
        }
        const ctx = ctxEl.getContext('2d');
        const months = monthly_trend.map(m => m.month);
        const requests = monthly_trend.map(m => m.request_count);
        const days = monthly_trend.map(m => m.completed_days);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Requests',
                    data: requests,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    fill: true
                }, {
                    label: 'Days Taken',
                    data: days,
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function renderDepartmentChart(dept_stats) {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }
        
        const ctxEl = document.getElementById('departmentChart');
        if (!ctxEl) {
            console.warn('Department chart canvas not found');
            return;
        }
        const ctx = ctxEl.getContext('2d');
        const departments = dept_stats.map(d => d.department);
        const requests = dept_stats.map(d => d.request_count);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: departments,
                datasets: [{
                    label: 'Requests',
                    data: requests,
                    backgroundColor: '#4e73df',
                    borderColor: '#2e59d9',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Tab event handlers for analytics and records
    $('button[data-bs-target="#analytics"]').on('shown.bs.tab', function() {
            loadAnalytics();
    });

    $('button[data-bs-target="#records"]').on('shown.bs.tab', function() {
        loadHRRecords();
    });

    // Tab event handlers for balance management and recommendations
    $('button[data-bs-target="#balance-management"]').on('shown.bs.tab', function() {
        loadBalanceManagement();
    });

    $('button[data-bs-target="#recommendations"]').on('shown.bs.tab', function() {
        loadRecommendations();
    });

    // Balance Form Submission
    $('#balanceForm').on('submit', function(e) {
        e.preventDefault();
        $.post('{{ route("leave.hr.manage-balance") }}', $(this).serialize(), function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#balanceModal').modal('hide');
                loadBalanceManagement();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        }).fail(function(xhr) {
            let errorMessage = 'Failed to update balance.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            Swal.fire('Error!', errorMessage, 'error');
        });
    });

    // Recommendation Form Submission
    $('#recommendationForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = $(this).serializeArray();
        
        // Remove recommendation_id if it's empty (when adding new)
        const filteredData = {};
        formData.forEach(function(item) {
            if (item.name === 'recommendation_id' && !item.value) {
                // Skip empty recommendation_id when adding
                return;
            }
            if (filteredData[item.name]) {
                // Handle array values
                if (Array.isArray(filteredData[item.name])) {
                    filteredData[item.name].push(item.value);
                } else {
                    filteredData[item.name] = [filteredData[item.name], item.value];
                }
            } else {
                filteredData[item.name] = item.value;
            }
        });
        
        $.post('{{ route("leave.hr.manage-recommendations") }}', filteredData, function(response) {
            if (response.success) {
                Swal.fire('Success!', response.message, 'success');
                $('#recommendationModal').modal('hide');
                $('#recommendationForm')[0].reset();
                $('#recommendation_id').val('');
                loadRecommendations();
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        }).fail(function(xhr) {
            let errorMessage = 'Failed to save recommendation.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = Object.values(xhr.responseJSON.errors).flat();
                errorMessage = errors.join(', ');
            }
            Swal.fire('Error!', errorMessage, 'error');
        });
    });

    @endif

    @if($isHR || $isAdmin)
    
    // Local function that calls the global one
    function showAddLeaveTypeModal() {
        window.showAddLeaveTypeModal();
    }

    // Local wrappers that call global functions (for backward compatibility)
    function editLeaveType(id) {
        window.editLeaveType(id);
    }
    
    function deleteLeaveType(id) {
        window.deleteLeaveType(id);
    }
    
    function toggleLeaveTypeStatus(id) {
        window.toggleLeaveTypeStatus(id);
    }
    
    // Character count for description
    function updateDescriptionCharCount() {
        if (typeof $ !== 'undefined' && $('#leave_type_description').length) {
            const length = $('#leave_type_description').val().length;
            $('#description-char-count').text(length + '/1000 characters');
        }
    }
    
    // Filter Leave Types Table
    function filterLeaveTypesTable() {
        const searchTerm = $('#leave-types-search').val().toLowerCase();
        const statusFilter = $('#leave-types-status-filter').val();
        const approvalFilter = $('#leave-types-approval-filter').val();
        
        let visibleCount = 0;
        
        $('#leave-types-table tbody tr').each(function() {
            const name = $(this).data('name') || '';
            const description = $(this).data('description') || '';
            const status = $(this).data('status') || '';
            const requiresApproval = $(this).data('requires-approval') || '';
            
            const matchesSearch = !searchTerm || name.includes(searchTerm) || description.includes(searchTerm);
            const matchesStatus = !statusFilter || status === statusFilter;
            const matchesApproval = !approvalFilter || requiresApproval === approvalFilter;
            
            if (matchesSearch && matchesStatus && matchesApproval) {
                $(this).show();
                visibleCount++;
            } else {
                $(this).hide();
            }
        });
        
        // Show/hide empty message
        if (visibleCount === 0) {
            $('#leave-types-empty').show();
        } else {
            $('#leave-types-empty').hide();
        }
    }
    
    // Reset Leave Types Filters
    function resetLeaveTypesFilters() {
        $('#leave-types-search').val('');
        $('#leave-types-status-filter').val('');
        $('#leave-types-approval-filter').val('');
        filterLeaveTypesTable();
    }
    
    // Show Leave Type Toast Notification
    function showLeaveTypeToast(type, message) {
        // Remove existing toast container
        $('#toast-container-leave-types').remove();
        
        // Map type to Bootstrap classes
        const bgClass = type === 'success' ? 'success' : type === 'danger' ? 'danger' : type === 'warning' ? 'warning' : 'info';
        const iconClass = type === 'success' ? 'bx-check-circle' : type === 'danger' ? 'bx-error-circle' : type === 'warning' ? 'bx-error' : 'bx-info-circle';
        
        // Create toast container
        const toastHtml = `
            <div id="toast-container-leave-types" class="position-fixed top-0 end-0 p-3" style="z-index: 9999; margin-top: 70px;">
                <div class="toast align-items-center text-white bg-${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bx ${iconClass} me-2"></i>
                            <strong>${type === 'success' ? 'Success' : type === 'danger' ? 'Error' : type === 'warning' ? 'Warning' : 'Info'}:</strong> ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(toastHtml);
        const toastElement = $('#toast-container-leave-types .toast');
        const toast = new bootstrap.Toast(toastElement[0], {
            autohide: true,
            delay: 5000
        });
        toast.show();
        
        // Remove container after toast is hidden
        toastElement.on('hidden.bs.toast', function() {
            $('#toast-container-leave-types').remove();
        });
    }
    
    // Legacy function for backward compatibility
    function showLeaveTypeAlert(title, message, type) {
        showLeaveTypeToast(type, message);
    }

    function deleteLeaveType(id) {
        if (!confirm('Are you sure you want to delete this leave type? This action cannot be undone!')) {
            return;
        }
        
        // Show loading state on button
        const deleteBtn = $(`button[onclick="deleteLeaveType(${id})"]`);
        const originalHtml = deleteBtn.html();
        deleteBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
                    url: `/leave/hr/leave-types/${id}`,
                    type: 'DELETE',
            data: { 
                _token: csrfToken 
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                deleteBtn.prop('disabled', false).html(originalHtml);
                
            if (response.success) {
                    showLeaveTypeAlert('Success', response.message, 'success');
                    // Refresh table after short delay to show success message
                    setTimeout(function() {
                                refreshLeaveTypesTable();
                    }, 500);
            } else {
                    showLeaveTypeAlert('Error', response.message || 'Failed to delete leave type', 'danger');
            }
            },
            error: function(xhr) {
                deleteBtn.prop('disabled', false).html(originalHtml);
                
                let errorMessage = 'Failed to delete leave type. Please try again.';
                
                if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to delete leave types.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Leave type not found. It may have already been deleted.';
                    // Refresh table if 404
                    setTimeout(function() {
                        refreshLeaveTypesTable();
                    }, 2000);
                } else if (xhr.status === 422) {
                    // Validation error (e.g., has requests)
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                    }
                
                showLeaveTypeAlert('Error', errorMessage, 'danger');
            }
        });
    }

    function refreshLeaveTypesTable() {
        // Show loading indicator
        const tableBody = $('#leave-types-table-body');
        const originalHtml = tableBody.html();
        tableBody.html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 mb-0 text-muted">Refreshing leave types...</p></td></tr>');
        
        // Reload leave types via AJAX
                $.ajax({
            url: '{{ route("leave.hr.leave-types") }}',
            type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                if (response.success && response.leaveTypes) {
                    // Update table with new data
                    let html = '';
                    
                    if (response.leaveTypes.length === 0) {
                        html = '<tr><td colspan="9" class="text-center py-4 text-muted">No leave types found. Click "Add Leave Type" to create one.</td></tr>';
                    } else {
                        response.leaveTypes.forEach(function(type, index) {
                            const requestCount = type.leave_requests_count || 0;
                            html += `
                                <tr data-leave-type-id="${type.id}" 
                                    data-name="${(type.name || '').toLowerCase()}" 
                                    data-description="${(type.description || '').toLowerCase()}"
                                    data-status="${type.is_active ? 'active' : 'inactive'}"
                                    data-requires-approval="${type.requires_approval ? 'requires' : 'no_approval'}">
                                    <td>${index + 1}</td>
                                    <td><strong>${escapeHtml(type.name || 'N/A')}</strong></td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" 
                                              title="${escapeHtml(type.description || 'N/A')}">
                                            ${escapeHtml((type.description || 'N/A').substring(0, 50))}${(type.description || '').length > 50 ? '...' : ''}
                                        </span>
                                    </td>
                                    <td><span class="badge bg-primary">${type.max_days_per_year || type.max_days || 'N/A'}</span></td>
                                    <td>
                                        ${type.requires_approval 
                                            ? '<span class="badge bg-info"><i class="bx bx-check-circle"></i> Yes</span>'
                                            : '<span class="badge bg-secondary"><i class="bx bx-x-circle"></i> No</span>'}
                                    </td>
                                    <td>
                                        ${(type.is_paid !== false) 
                                            ? '<span class="badge bg-success"><i class="bx bx-coin"></i> Paid</span>'
                                            : '<span class="badge bg-warning"><i class="bx bx-coin-stack"></i> Unpaid</span>'}
                                    </td>
                                    <td>
                                        ${type.is_active 
                                            ? '<span class="badge bg-success"><i class="bx bx-check"></i> Active</span>'
                                            : '<span class="badge bg-danger"><i class="bx bx-x"></i> Inactive</span>'}
                                    </td>
                                    <td><span class="badge bg-primary">${requestCount}</span></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" onclick="editLeaveType(${type.id})" 
                                                    data-bs-toggle="tooltip" title="Edit Leave Type">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            ${requestCount == 0 
                                                ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteLeaveType(${type.id})" 
                                                          data-bs-toggle="tooltip" title="Delete Leave Type">
                                                      <i class="bx bx-trash"></i>
                                                   </button>`
                                                : `<button class="btn btn-sm btn-outline-secondary" onclick="toggleLeaveTypeStatus(${type.id})" 
                                                          data-bs-toggle="tooltip" title="${type.is_active ? 'Deactivate' : 'Activate'} Leave Type">
                                                      <i class="bx bx-${type.is_active ? 'pause' : 'play'}"></i>
                                                   </button>`}
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    }
                    
                    tableBody.html(html);
                    
                    // Reapply filters if they have values
                    if ($('#leave-types-search').val() || $('#leave-types-status-filter').val() || $('#leave-types-approval-filter').val()) {
                        filterLeaveTypesTable();
                    }
                    
                    // Update tooltips
                    if (typeof bootstrap !== 'undefined') {
                        const tooltipTriggerList = [].slice.call(tableBody.find('[data-bs-toggle="tooltip"]'));
                        tooltipTriggerList.map(function (tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl);
                        });
                    }
                        } else {
            // Fallback to page reload
                    showLeaveTypeAlert('Warning', 'Failed to refresh leave types. Reloading page...', 'warning');
                    setTimeout(function() {
            location.reload();
                    }, 2000);
                        }
                    },
                    error: function(xhr) {
                let errorMessage = 'Failed to refresh leave types.';
                
                if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to view leave types.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                
                showLeaveTypeAlert('Error', errorMessage, 'danger');
                
                // Restore original HTML or show error
                tableBody.html(originalHtml || '<tr><td colspan="9" class="text-center py-4 text-danger">Failed to load leave types. Please refresh the page.</td></tr>');
                    }
                });
            }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return (text || '').replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function toggleLeaveTypeStatus(id) {
        // Get current leave type data
        const row = $(`tr[data-leave-type-id="${id}"]`);
        const isActive = row.data('status') === 'active';
        const newStatus = isActive ? 'Deactivate' : 'Activate';
        
        if (!confirm(`Are you sure you want to ${newStatus.toLowerCase()} this leave type?`)) {
            return;
        }
        
        // Show loading state on button
        const toggleBtn = $(`button[onclick="toggleLeaveTypeStatus(${id})"]`);
        const originalHtml = toggleBtn.html();
        toggleBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        // Load the leave type first to get all data
        $.ajax({
            url: `{{ route('leave.hr.leave-types.show', ':id') }}`.replace(':id', id),
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success && response.leaveType) {
                    const type = response.leaveType;
                    
                    // Update via edit endpoint
                    const formData = {
                        _token: csrfToken,
                        name: type.name,
                        description: type.description || '',
                        max_days_per_year: type.max_days_per_year || type.max_days || 28,
                        requires_approval: type.requires_approval ? 1 : 0,
                        is_paid: type.is_paid ? 1 : 0,
                        is_active: !isActive ? 1 : 0 // Toggle the status
                    };
                    
                    $.ajax({
                        url: `{{ route('leave.hr.leave-types.update', ':id') }}`.replace(':id', id),
                        type: 'PUT',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            toggleBtn.prop('disabled', false).html(originalHtml);
                            
                            if (response.success) {
                                showLeaveTypeAlert('Success', response.message, 'success');
                                // Refresh table after short delay
                                setTimeout(function() {
                                    refreshLeaveTypesTable();
                                }, 500);
                            } else {
                                showLeaveTypeAlert('Error', response.message || 'Failed to update leave type status', 'danger');
                            }
                        },
                        error: function(xhr) {
                            toggleBtn.prop('disabled', false).html(originalHtml);
                            
                            let errorMessage = 'Failed to update leave type status. Please try again.';
                            
                            if (xhr.status === 403) {
                                errorMessage = 'You do not have permission to update leave types.';
                            } else if (xhr.status === 404) {
                                errorMessage = 'Leave type not found. It may have been deleted.';
                                setTimeout(function() {
                                    refreshLeaveTypesTable();
                                }, 2000);
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                errorMessage = errors.join(', ');
                            }
                            
                            showLeaveTypeAlert('Error', errorMessage, 'danger');
                        }
                    });
                } else {
                    toggleBtn.prop('disabled', false).html(originalHtml);
                    showLeaveTypeAlert('Error', response.message || 'Failed to load leave type details', 'danger');
                }
            },
            error: function(xhr) {
                toggleBtn.prop('disabled', false).html(originalHtml);
                
                let errorMessage = 'Failed to load leave type details. Please try again.';
                
                if (xhr.status === 403) {
                    errorMessage = 'You do not have permission to view leave types.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Leave type not found. It may have been deleted.';
                    setTimeout(function() {
                        refreshLeaveTypesTable();
                    }, 2000);
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showLeaveTypeAlert('Error', errorMessage, 'danger');
            }
        });
    }

    // Leave Type Form Submission
    $('#leaveTypeForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data and properly handle checkboxes (convert to boolean)
        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            name: $('#leave_type_name').val(),
            description: $('#leave_type_description').val(),
            max_days_per_year: $('#leave_type_max_days').val(),
            requires_approval: $('#leave_type_requires_approval').is(':checked') ? 1 : 0,
            is_paid: $('#leave_type_is_paid').is(':checked') ? 1 : 0,
            is_active: $('#leave_type_is_active').is(':checked') ? 1 : 0
        };
        
        // Add leave_type_id if editing
        const leaveTypeId = $('#leave_type_id').val();
        if (leaveTypeId) {
            formData.leave_type_id = leaveTypeId;
        }
        
        const url = leaveTypeId 
            ? `{{ route('leave.hr.leave-types.update', ':id') }}`.replace(':id', leaveTypeId)
            : '{{ route("leave.hr.leave-types.store") }}';
        const method = leaveTypeId ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            type: method,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.success) {
                    $('#leaveTypeModal').modal('hide');
                    $('#leaveTypeForm')[0].reset();
                    $('#leave_type_id').val('');
                    $('#leave_type_name, #leave_type_max_days').removeClass('is-invalid');
                    $('#leave_type_name_error, #leave_type_max_days_error').text('');
                    $('#description-char-count').text('0/1000 characters');
                    
                    if (typeof showLeaveTypeToast === 'function') {
                        showLeaveTypeToast('success', response.message);
                    } else if (typeof showLeaveTypeAlert === 'function') {
                        showLeaveTypeAlert('Success', response.message, 'success');
                    } else {
                        alert('Success: ' + response.message);
                    }
                    
                    // Refresh table after short delay
                    setTimeout(function() {
                        refreshLeaveTypesTable();
                    }, 500);
                } else {
                    if (typeof showLeaveTypeToast === 'function') {
                        showLeaveTypeToast('danger', response.message || 'Failed to save leave type');
                    } else if (typeof showLeaveTypeAlert === 'function') {
                        showLeaveTypeAlert('Error', response.message || 'Failed to save leave type', 'danger');
                    } else {
                        alert('Error: ' + (response.message || 'Failed to save leave type'));
                    }
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to save leave type.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = Object.values(xhr.responseJSON.errors).flat();
                    errorMessage = errors.join(', ');
                    // Show field-specific errors
                    if (xhr.responseJSON.errors.name) {
                        $('#leave_type_name').addClass('is-invalid');
                        $('#leave_type_name_error').text(xhr.responseJSON.errors.name[0]);
                    }
                    if (xhr.responseJSON.errors.max_days_per_year) {
                        $('#leave_type_max_days').addClass('is-invalid');
                        $('#leave_type_max_days_error').text(xhr.responseJSON.errors.max_days_per_year[0]);
                    }
                }
                if (typeof showLeaveTypeToast === 'function') {
                    showLeaveTypeToast('danger', errorMessage);
                } else if (typeof showLeaveTypeAlert === 'function') {
                    showLeaveTypeAlert('Error', errorMessage, 'danger');
                } else {
                    alert('Error: ' + errorMessage);
                }
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    @endif
});
</script>
@endpush