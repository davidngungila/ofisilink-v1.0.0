@extends('layouts.app')

@section('title', 'Permission Management')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bx bx-clipboard me-2"></i>Permission Management
            </h1>
            <p class="text-muted mb-0">Manage staff permission requests and approvals</p>
        </div>
        <div>
            <button class="btn btn-primary shadow-sm" id="new-permission-btn">
                <i class="bx bx-plus-circle me-1"></i>Request Permission
            </button>
        </div>
    </div>



    
    <!-- Total Pending Banner -->
    @if($totalPending > 0)
    <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bx bx-error-circle bx-lg me-3"></i>
            <div class="flex-grow-1">
                <h5 class="alert-heading mb-1">
                    <strong>Total Pending Requests: {{ $totalPending }}</strong>
                </h5>
                <p class="mb-0">There are {{ $totalPending }} permission request{{ $totalPending > 1 ? 's' : '' }} awaiting review and action.</p>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if($isHOD || $isCEO || $isAdmin)
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Requests</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $allRequests->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-clipboard fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Action</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPending }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-time-five fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approved->count() }}</div>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Completed</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $completed->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="bx bx-task fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Advanced Tabs Interface -->
    <div class="card shadow mb-4">
        <div class="card-header bg-white py-3">
            <ul class="nav nav-tabs card-header-tabs" id="permissionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                        <i class="bx bx-list-ul me-1"></i>All Requests
                        <span class="badge bg-secondary ms-2">{{ $allRequests->count() }}</span>
                    </button>
                </li>
    @if($awaitingMyAction->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="awaiting-tab" data-bs-toggle="tab" data-bs-target="#awaiting" type="button" role="tab">
                        <i class="bx bx-time-five me-1"></i>Awaiting My Action
                        <span class="badge bg-warning ms-2">{{ $awaitingMyAction->count() }}</span>
                    </button>
                </li>
                @endif
                @if($pendingHR->count() > 0 || ($isHR || $isAdmin))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-hr-tab" data-bs-toggle="tab" data-bs-target="#pending-hr" type="button" role="tab">
                        <i class="bx bx-user me-1"></i>Pending HR Review
                        <span class="badge bg-warning ms-2">{{ $pendingHR->count() }}</span>
                    </button>
                </li>
                @endif
                @if($pendingHOD->count() > 0 || ($isHOD || $isAdmin))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-hod-tab" data-bs-toggle="tab" data-bs-target="#pending-hod" type="button" role="tab">
                        <i class="bx bx-user-circle me-1"></i>Pending HOD Review
                        <span class="badge bg-warning ms-2">{{ $pendingHOD->count() }}</span>
                    </button>
                </li>
                @endif
                @if($pendingHRFinal->count() > 0 || ($isHR || $isAdmin))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pending-hr-final-tab" data-bs-toggle="tab" data-bs-target="#pending-hr-final" type="button" role="tab">
                        <i class="bx bx-check-double me-1"></i>Pending HR Final
                        <span class="badge bg-warning ms-2">{{ $pendingHRFinal->count() }}</span>
                    </button>
                </li>
                @endif
                @if($returnPending->count() > 0 || ($isHOD || $isAdmin))
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="return-pending-tab" data-bs-toggle="tab" data-bs-target="#return-pending" type="button" role="tab">
                        <i class="bx bx-undo me-1"></i>Return Pending
                        <span class="badge bg-info ms-2">{{ $returnPending->count() }}</span>
                    </button>
                </li>
                @endif
                @if($myRequests->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="my-requests-tab" data-bs-toggle="tab" data-bs-target="#my-requests" type="button" role="tab">
                        <i class="bx bx-user me-1"></i>My Requests
                        <span class="badge bg-primary ms-2">{{ $myRequests->count() }}</span>
                    </button>
                </li>
                @endif
                @if($approved->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                        <i class="bx bx-check-circle me-1"></i>Approved
                        <span class="badge bg-success ms-2">{{ $approved->count() }}</span>
                    </button>
                </li>
                @endif
                @if($completed->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">
                        <i class="bx bx-task me-1"></i>Completed
                        <span class="badge bg-info ms-2">{{ $completed->count() }}</span>
                    </button>
                </li>
                @endif
                @if($rejected->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">
                        <i class="bx bx-x-circle me-1"></i>Rejected
                        <span class="badge bg-danger ms-2">{{ $rejected->count() }}</span>
                    </button>
                </li>
                @endif
                @if($processedByMe->count() > 0)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="processed-tab" data-bs-toggle="tab" data-bs-target="#processed" type="button" role="tab">
                        <i class="bx bx-history me-1"></i>Processed By Me
                        <span class="badge bg-secondary ms-2">{{ $processedByMe->count() }}</span>
                    </button>
                </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">
                        <i class="bx bx-calendar me-1"></i>Calendar View
                    </button>
                </li>
                @if($isHOD || $isHR || $isAdmin || $isCEO)
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab">
                        <i class="bx bx-bar-chart-alt-2 me-1"></i>Analytics
                    </button>
                </li>
                @endif
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="permissionTabsContent">
                <!-- All Requests Tab -->
                <div class="tab-pane fade show active" id="all" role="tabpanel">
                    @if($allRequests->count() === 0)
                        <div class="text-center py-5">
                            <i class="bx bx-inbox bx-lg text-muted mb-3"></i>
                            <p class="text-muted">No permission requests found.</p>
                            <button class="btn btn-primary" id="new-permission-btn-empty">
                                <i class="bx bx-plus-circle me-1"></i>Create First Request
                            </button>
                        </div>
                    @else
                        <div class="row">
                            @foreach($allRequests as $request)
                                @include('modules.hr.partials.permission-request-card', [
                                    'request' => $request, 
                                    'isOwn' => ($request->user_id == auth()->id()),
                                    'isHR' => $isHR ?? false,
                                    'isAdmin' => $isAdmin ?? false,
                                    'isHOD' => $isHOD ?? false
                                ])
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Awaiting My Action Tab -->
                @if($awaitingMyAction->count() > 0)
                <div class="tab-pane fade" id="awaiting" role="tabpanel">
            <div class="row">
                @foreach($awaitingMyAction as $request)
                    @include('modules.hr.partials.permission-request-card', [
                        'request' => $request, 
                        'isOwn' => false,
                        'isHR' => $isHR ?? false,
                        'isAdmin' => $isAdmin ?? false,
                        'isHOD' => $isHOD ?? false
                    ])
                @endforeach
            </div>
        </div>
                @endif

                <!-- Pending HR Review Tab -->
                <div class="tab-pane fade" id="pending-hr" role="tabpanel">
                    @if($pendingHR->count() === 0)
                        <div class="text-center py-5">
                            <i class="bx bx-check-circle bx-lg text-success mb-3"></i>
                            <p class="text-muted">No requests pending HR review.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($pendingHR as $request)
                                @include('modules.hr.partials.permission-request-card', [
                                    'request' => $request, 
                                    'isOwn' => ($request->user_id == auth()->id()),
                                    'isHR' => $isHR ?? false,
                                    'isAdmin' => $isAdmin ?? false,
                                    'isHOD' => $isHOD ?? false
                                ])
                            @endforeach
    </div>
    @endif
                </div>

                <!-- Pending HOD Review Tab -->
                <div class="tab-pane fade" id="pending-hod" role="tabpanel">
                    @if($pendingHOD->count() === 0)
                        <div class="text-center py-5">
                            <i class="bx bx-check-circle bx-lg text-success mb-3"></i>
                            <p class="text-muted">No requests pending HOD review.</p>
        </div>
                    @else
            <div class="row">
                            @foreach($pendingHOD as $request)
                                @include('modules.hr.partials.permission-request-card', [
                                    'request' => $request, 
                                    'isOwn' => ($request->user_id == auth()->id()),
                                    'isHR' => $isHR ?? false,
                                    'isAdmin' => $isAdmin ?? false,
                                    'isHOD' => $isHOD ?? false
                                ])
                @endforeach
            </div>
                    @endif
        </div>

                <!-- Pending HR Final Tab -->
                <div class="tab-pane fade" id="pending-hr-final" role="tabpanel">
                    @if($pendingHRFinal->count() === 0)
                        <div class="text-center py-5">
                            <i class="bx bx-check-circle bx-lg text-success mb-3"></i>
                            <p class="text-muted">No requests pending HR final review.</p>
                        </div>
                    @else
                        <div class="row">
                            @foreach($pendingHRFinal as $request)
                                @include('modules.hr.partials.permission-request-card', [
                                    'request' => $request, 
                                    'isOwn' => ($request->user_id == auth()->id()),
                                    'isHR' => $isHR ?? false,
                                    'isAdmin' => $isAdmin ?? false,
                                    'isHOD' => $isHOD ?? false
                                ])
                            @endforeach
    </div>
    @endif
                </div>

                <!-- Return Pending Tab -->
                <div class="tab-pane fade" id="return-pending" role="tabpanel">
                    @if($returnPending->count() === 0)
                        <div class="text-center py-5">
                            <i class="bx bx-check-circle bx-lg text-success mb-3"></i>
                            <p class="text-muted">No return confirmations pending.</p>
        </div>
            @else
                <div class="row">
                            @foreach($returnPending as $request)
                                @include('modules.hr.partials.permission-request-card', [
                                    'request' => $request, 
                                    'isOwn' => ($request->user_id == auth()->id()),
                                    'isHR' => $isHR ?? false,
                                    'isAdmin' => $isAdmin ?? false,
                                    'isHOD' => $isHOD ?? false
                                ])
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- My Requests Tab -->
                @if($myRequests->count() > 0)
                <div class="tab-pane fade" id="my-requests" role="tabpanel">
                    <div class="row">
                        @foreach($myRequests as $request)
                        @include('modules.hr.partials.permission-request-card', [
                            'request' => $request, 
                            'isOwn' => true,
                            'isHR' => $isHR ?? false,
                            'isAdmin' => $isAdmin ?? false,
                            'isHOD' => $isHOD ?? false
                        ])
                    @endforeach
                    </div>
                </div>
            @endif

                <!-- Approved Tab -->
                @if($approved->count() > 0)
                <div class="tab-pane fade" id="approved" role="tabpanel">
                    <div class="row">
                        @foreach($approved as $request)
                            @include('modules.hr.partials.permission-request-card', [
                                'request' => $request, 
                                'isOwn' => ($request->user_id == auth()->id()),
                                'isHR' => $isHR ?? false,
                                'isAdmin' => $isAdmin ?? false,
                                'isHOD' => $isHOD ?? false
                            ])
                        @endforeach
        </div>
    </div>
                @endif

                <!-- Completed Tab -->
                @if($completed->count() > 0)
                <div class="tab-pane fade" id="completed" role="tabpanel">
                    <div class="row">
                        @foreach($completed as $request)
                            @include('modules.hr.partials.permission-request-card', [
                                'request' => $request, 
                                'isOwn' => ($request->user_id == auth()->id()),
                                'isHR' => $isHR ?? false,
                                'isAdmin' => $isAdmin ?? false,
                                'isHOD' => $isHOD ?? false
                            ])
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Rejected Tab -->
                @if($rejected->count() > 0)
                <div class="tab-pane fade" id="rejected" role="tabpanel">
                    <div class="row">
                        @foreach($rejected as $request)
                            @include('modules.hr.partials.permission-request-card', [
                                'request' => $request, 
                                'isOwn' => ($request->user_id == auth()->id()),
                                'isHR' => $isHR ?? false,
                                'isAdmin' => $isAdmin ?? false,
                                'isHOD' => $isHOD ?? false
                            ])
                        @endforeach
        </div>
    </div>
    @endif

                <!-- Processed By Me Tab -->
    @if($processedByMe->count() > 0)
                <div class="tab-pane fade" id="processed" role="tabpanel">
            <div class="row">
                @foreach($processedByMe as $request)
                    @include('modules.hr.partials.permission-request-card', [
                        'request' => $request, 
                        'isOwn' => false,
                        'isHR' => $isHR ?? false,
                        'isAdmin' => $isAdmin ?? false,
                        'isHOD' => $isHOD ?? false
                    ])
                @endforeach
        </div>
    </div>
    @endif

                <!-- Calendar View Tab -->
                <div class="tab-pane fade" id="calendar" role="tabpanel">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-0"><i class="bx bx-calendar me-2"></i>Permission Calendar</h5>
                                    <small class="text-muted">
                                        <i class="bx bx-info-circle me-1"></i>
                                        Click and drag to select date range, or switch to <strong>Day/Week view</strong> to select specific times. Then choose your times in the form.
                                    </small>
                                </div>
                                <div>
                                    <button class="btn btn-outline-primary btn-sm me-2" id="switch-to-day-view" title="Switch to Day View">
                                        <i class="bx bx-calendar-check me-1"></i>Day View
                                    </button>
                                    <button class="btn btn-primary btn-sm" id="new-permission-calendar-btn">
                                        <i class="bx bx-plus-circle me-1"></i>New Request
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="permission-calendar"></div>
                        </div>
                    </div>
                </div>

                <!-- Analytics Tab -->
                @if($isHOD || $isHR || $isAdmin || $isCEO)
                <div class="tab-pane fade" id="analytics" role="tabpanel">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><i class="bx bx-filter me-2"></i>Filter Period</h5>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary period-filter active" data-period="month">This Month</button>
                                            <button type="button" class="btn btn-sm btn-outline-primary period-filter" data-period="quarter">This Quarter</button>
                                            <button type="button" class="btn btn-sm btn-outline-primary period-filter" data-period="year">This Year</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Requests</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="analytics-total">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bx bx-clipboard fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approval Rate</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="analytics-approval-rate">0%</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bx bx-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Processing Time</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="analytics-avg-time">0 days</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bx bx-time-five fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Rejected</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="analytics-rejected">0</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="bx bx-x-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="bx bx-pie-chart me-2"></i>Status Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="bx bx-line-chart me-2"></i>Monthly Trend</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="trendChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="bx bx-building me-2"></i>Department Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="departmentChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="bx bx-list-ul me-2"></i>Reason Type Distribution</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="reasonChart" height="300"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="card border-0 shadow-sm">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0"><i class="bx bx-user me-2"></i>Top Requesters</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Rank</th>
                                                    <th>Employee</th>
                                                    <th>Total Requests</th>
                                                    <th>Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody id="top-requesters-table">
                                                <tr>
                                                    <td colspan="4" class="text-center">Loading...</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                </div>
        </div>
    </div>
</div>

<!-- Request Permission Modal -->
<div class="modal fade" id="requestPermissionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="requestPermissionForm">
                <div class="modal-header">
                    <h5 class="modal-title">Request Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="action" value="request_permission">
                    <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                    
                    <div class="alert alert-info mb-3">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Tip:</strong> Select dates on the calendar and choose your preferred times. You can select a range of days even for hours-based permissions.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Time Mode *</label>
                        <select name="time_mode" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="hours">Hours</option>
                            <option value="days">Days</option>
                        </select>
                        <small class="text-muted">Select "Hours" for short permissions or "Days" for full day(s) permissions</small>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Time *</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" required>
                            <small class="text-muted">Select when you want to start your permission</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Time *</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" required>
                            <small class="text-muted">Select when you want to end your permission</small>
                        </div>
                    </div>
                    <input type="hidden" name="start_datetime" id="start_datetime">
                    <input type="hidden" name="end_datetime" id="end_datetime">
                    
                    <div class="mb-3">
                        <label class="form-label">Reason Type *</label>
                        <select name="reason_type" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="official">Official</option>
                            <option value="personal">Personal</option>
                            <option value="medical">Medical</option>
                            <option value="emergency">Emergency</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reason Description *</label>
                        <textarea name="reason_description" class="form-control" rows="4" required 
                                  placeholder="Provide a detailed reason for your permission request..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- HOD Review Modal -->
<div class="modal fade" id="hodReviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hodReviewForm">
                <div class="modal-header">
                    <h5 class="modal-title">HOD Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="hod-review-request-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Decision *</label>
                        <select name="decision" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="approve">Approve</option>
                            <option value="reject">Reject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments (Required for Rejection) *</label>
                        <textarea name="comments" class="form-control" rows="4" required 
                                  placeholder="Provide comments for your decision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CEO Approval Modal -->
<div class="modal fade" id="ceoApprovalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="ceoApprovalForm">
                <div class="modal-header">
                    <h5 class="modal-title">Final Approval</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="ceo-approval-request-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Decision *</label>
                        <select name="decision" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="approve">Approve</option>
                            <option value="reject">Reject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea name="comments" class="form-control" rows="4" 
                                  placeholder="Provide comments for your decision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Return Modal -->
<div class="modal fade" id="confirmReturnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="confirmReturnForm">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Return to Office</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="confirm-return-request-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Return Date/Time *</label>
                        <input type="datetime-local" name="return_datetime" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="return_remarks" class="form-control" rows="3" 
                                  placeholder="e.g., Completed the task successfully."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Return</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- HOD Return Approval Modal -->
<div class="modal fade" id="hodReturnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hodReturnForm">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Staff Return</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="hod-return-request-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Decision *</label>
                        <select name="decision" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="approve">Approve Return</option>
                            <option value="reject">Reject Return</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea name="comments" class="form-control" rows="4" 
                                  placeholder="Provide comments for your decision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- HR Initial Review Modal -->
<div class="modal fade" id="hrInitialReviewModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hrInitialReviewForm">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">HR Initial Review</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="hr-initial-review-request-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Decision *</label>
                        <select name="decision" id="hrInitialDecision" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="approve">Approve & Forward to HOD</option>
                            <option value="reject">Reject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments *</label>
                        <textarea name="comments" id="hrInitialComments" class="form-control" rows="4" required placeholder="Provide comments for your decision..."></textarea>
                        <small class="text-muted">Required for all decisions</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- HR Final Approval Modal -->
<div class="modal fade" id="hrFinalApprovalModal" tabindex="-1" style="z-index: 1061;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hrFinalApprovalForm">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">HR Final Approval</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="hr-final-approval-request-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Decision *</label>
                        <select name="decision" id="hrFinalDecision" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="approve">Approve</option>
                            <option value="reject">Reject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea name="comments" id="hrFinalComments" class="form-control" rows="4" placeholder="Provide comments for your decision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- HR Return Verification Modal -->
<div class="modal fade" id="hrReturnVerifyModal" tabindex="-1" style="z-index: 1062;">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hrReturnVerifyForm">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-white">HR Return Verification</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="request_id" id="hr-return-verify-request-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Decision *</label>
                        <select name="decision" id="hrReturnDecision" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="approve">Approve Return</option>
                            <option value="reject">Reject Return</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea name="comments" id="hrReturnComments" class="form-control" rows="4" placeholder="Provide comments for your decision..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal fade" id="requestDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">Permission Request Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <div class="text-center py-4">
                    <i class="bx bx-loader-alt bx-spin bx-lg text-primary"></i>
                    <p class="mt-3">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">
<style>
/* Ensure modals display properly */
.modal {
    z-index: 1050;
}
.modal-backdrop {
    z-index: 1040;
}
#hrInitialReviewModal {
    z-index: 1060 !important;
}
#hrFinalApprovalModal {
    z-index: 1061 !important;
}
#hrReturnVerifyModal {
    z-index: 1062 !important;
}
#requestDetailsModal {
    z-index: 1063 !important;
}
.modal.show {
    display: block !important;
}
#permission-calendar {
    min-height: 600px;
}
.fc-selectable {
    cursor: crosshair;
}
.fc-highlight {
    background-color: rgba(0, 123, 255, 0.2) !important;
}
.fc-daygrid-day-frame:hover {
    background-color: rgba(0, 123, 255, 0.05);
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Initialize Calendar
    let calendarEl = document.getElementById('permission-calendar');
    let calendar; // Make calendar global for button access
    if (calendarEl) {
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            views: {
                timeGridDay: {
                    slotMinTime: '06:00:00',
                    slotMaxTime: '20:00:00',
                    slotDuration: '00:30:00',
                    selectable: true,
                    selectMirror: true
                },
                timeGridWeek: {
                    slotMinTime: '06:00:00',
                    slotMaxTime: '20:00:00',
                    slotDuration: '00:30:00',
                    selectable: true,
                    selectMirror: true
                },
                dayGridMonth: {
                    selectable: true,
                    selectMirror: true
                }
            },
            selectable: true,
            selectMirror: true,
            selectConstraint: {
                start: 'today' // Can't select past dates, but can select today
            },
            dayMaxEvents: true,
            selectOverlap: false, // Prevent selecting over existing events
            events: function(fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: '{{ route("permissions.calendar.events") }}',
                    type: 'GET',
                    data: {
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr
                    },
                    success: function(events) {
                        successCallback(events);
                    },
                    error: function() {
                        failureCallback();
                    }
                });
            },
            select: function(selectInfo) {
                // When user selects a date range, open the permission request modal
                const startDate = new Date(selectInfo.start);
                const endDate = new Date(selectInfo.end);
                
                // Format date for date input (YYYY-MM-DD)
                const formatDate = function(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                };
                
                // Format time for time input (HH:mm)
                const formatTime = function(date) {
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    return `${hours}:${minutes}`;
                };
                
                // Check if this is a time-based selection (from timeGrid views)
                const isTimeView = selectInfo.view.type.includes('timeGrid');
                
                let startTime, endTime;
                
                if (isTimeView) {
                    // Time view - use selected times
                    startTime = formatTime(startDate);
                    // For time views, end is exclusive, so we need to handle it differently
                    const adjustedEnd = new Date(endDate);
                    // If selecting in time view, end time is already set
                    endTime = formatTime(adjustedEnd);
                } else {
                    // Day/month view - use default times or let user choose
                    // Check if times are set (non-zero)
                    if (startDate.getHours() > 0 || startDate.getMinutes() > 0) {
                        startTime = formatTime(startDate);
                    } else {
                        startTime = '08:00'; // Default start time
                    }
                    
                    // Adjust end date (FullCalendar's end is exclusive, so subtract 1 day for day view)
                    if (selectInfo.view.type === 'dayGridMonth' || selectInfo.view.type === 'dayGridWeek') {
                        endDate.setDate(endDate.getDate() - 1);
                    }
                    
                    if (endDate.getHours() > 0 || endDate.getMinutes() > 0) {
                        endTime = formatTime(endDate);
                    } else {
                        endTime = '17:00'; // Default end time
                    }
                }
                
                // Pre-fill the form with dates and times
                $('#requestPermissionForm input[name="start_date"]').val(formatDate(startDate));
                $('#requestPermissionForm input[name="start_time"]').val(startTime);
                $('#requestPermissionForm input[name="end_date"]').val(formatDate(endDate));
                $('#requestPermissionForm input[name="end_time"]').val(endTime);
                
                // Update hidden datetime fields
                updateDateTimeFields();
                
                // Calculate duration for time mode detection
                const daysDiff = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
                
                // Auto-detect time mode - allow hours even for multiple days
                // Users can manually change this if needed
                if (daysDiff > 1) {
                    // Multiple days - default to days but allow hours
                    $('#requestPermissionForm select[name="time_mode"]').val('days');
                } else {
                    // Single day or same day - check duration
                    const startHour = parseInt(startTime.split(':')[0]);
                    const startMin = parseInt(startTime.split(':')[1]);
                    const endHour = parseInt(endTime.split(':')[0]);
                    const endMin = parseInt(endTime.split(':')[1]);
                    
                    let hourDiff = (endHour * 60 + endMin) - (startHour * 60 + startMin);
                    if (hourDiff < 0) hourDiff += 24 * 60; // Handle overnight
                    
                    if (hourDiff >= 8 * 60) { // 8 hours or more
                        $('#requestPermissionForm select[name="time_mode"]').val('days');
                    } else {
                        $('#requestPermissionForm select[name="time_mode"]').val('hours');
                    }
                }
                
                // Open the modal
                const modal = new bootstrap.Modal(document.getElementById('requestPermissionModal'));
                modal.show();
                
                // Unselect the date range
                calendar.unselect();
            },
            eventClick: function(info) {
                const requestId = info.event.extendedProps.request_id;
                window.location.href = `/permissions/${info.event.id}`;
            },
            eventDidMount: function(info) {
                $(info.el).tooltip({
                    title: `${info.event.extendedProps.request_id}<br>${info.event.extendedProps.duration}<br>${info.event.extendedProps.department}`,
                    html: true,
                    placement: 'top'
                });
            }
        });
        calendar.render();
        
        // Re-render calendar when tab is shown
        $('#calendar-tab').on('shown.bs.tab', function() {
            calendar.render();
        });
        
        // Switch to day view button
        $('#switch-to-day-view').on('click', function() {
            calendar.changeView('timeGridDay');
        });
    }
    
    // Analytics Charts
    let statusChart, trendChart, departmentChart, reasonChart;
    let currentPeriod = 'month';
    
    function loadAnalytics() {
        const startDate = getStartDate(currentPeriod);
        const endDate = getEndDate(currentPeriod);
        
        $.ajax({
            url: '{{ route("permissions.analytics") }}',
            type: 'GET',
            data: {
                period: currentPeriod,
                start_date: startDate,
                end_date: endDate
            },
            success: function(data) {
                updateAnalyticsCards(data);
                updateCharts(data);
                updateTopRequesters(data.top_requesters);
            },
            error: function() {
                console.error('Failed to load analytics');
            }
        });
    }
    
    function getStartDate(period) {
        const now = new Date();
        switch(period) {
            case 'month':
                return new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
            case 'quarter':
                const quarter = Math.floor(now.getMonth() / 3);
                return new Date(now.getFullYear(), quarter * 3, 1).toISOString().split('T')[0];
            case 'year':
                return new Date(now.getFullYear(), 0, 1).toISOString().split('T')[0];
            default:
                return new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        }
    }
    
    function getEndDate(period) {
        const now = new Date();
        switch(period) {
            case 'month':
                return new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
            case 'quarter':
                const quarter = Math.floor(now.getMonth() / 3);
                return new Date(now.getFullYear(), (quarter + 1) * 3, 0).toISOString().split('T')[0];
            case 'year':
                return new Date(now.getFullYear(), 11, 31).toISOString().split('T')[0];
            default:
                return new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
        }
    }
    
    function updateAnalyticsCards(data) {
        $('#analytics-total').text(data.total_requests || 0);
        $('#analytics-approval-rate').text((data.approval_rate || 0) + '%');
        $('#analytics-avg-time').text((data.avg_processing_time || 0) + ' days');
        $('#analytics-rejected').text(data.rejected || 0);
    }
    
    function updateCharts(data) {
        // Status Distribution Chart
        if (statusChart) statusChart.destroy();
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const statusLabels = Object.keys(data.status_distribution || {});
            const statusValues = Object.values(data.status_distribution || {});
            statusChart = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels.map(s => s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())),
                    datasets: [{
                        data: statusValues,
                        backgroundColor: [
                            '#ffc107', '#17a2b8', '#007bff', '#28a745', 
                            '#dc3545', '#fd7e14', '#6c757d', '#6610f2'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
        
        // Monthly Trend Chart
        if (trendChart) trendChart.destroy();
        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            const trendLabels = Object.keys(data.monthly_trend || {}).sort();
            const trendValues = trendLabels.map(m => data.monthly_trend[m] || 0);
            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendLabels,
                    datasets: [{
                        label: 'Requests',
                        data: trendValues,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4,
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
        
        // Department Distribution Chart
        if (departmentChart) departmentChart.destroy();
        const deptCtx = document.getElementById('departmentChart');
        if (deptCtx) {
            const deptLabels = Object.keys(data.department_distribution || {});
            const deptValues = Object.values(data.department_distribution || {});
            departmentChart = new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: deptLabels,
                    datasets: [{
                        label: 'Requests',
                        data: deptValues,
                        backgroundColor: '#28a745'
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
        
        // Reason Distribution Chart
        if (reasonChart) reasonChart.destroy();
        const reasonCtx = document.getElementById('reasonChart');
        if (reasonCtx) {
            const reasonLabels = Object.keys(data.reason_distribution || {});
            const reasonValues = Object.values(data.reason_distribution || {});
            reasonChart = new Chart(reasonCtx, {
                type: 'pie',
                data: {
                    labels: reasonLabels.map(r => r.charAt(0).toUpperCase() + r.slice(1)),
                    datasets: [{
                        data: reasonValues,
                        backgroundColor: [
                            '#007bff', '#28a745', '#ffc107', '#dc3545', '#6c757d'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }
    
    function updateTopRequesters(requesters) {
        let html = '';
        if (requesters && requesters.length > 0) {
            requesters.forEach((requester, index) => {
                const maxCount = requesters[0].count;
                const percentage = (requester.count / maxCount) * 100;
                html += `
                    <tr>
                        <td><span class="badge bg-primary">${index + 1}</span></td>
                        <td>${requester.name}</td>
                        <td>${requester.count}</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" style="width: ${percentage}%">${requester.count}</div>
                            </div>
                        </td>
                    </tr>
                `;
            });
        } else {
            html = '<tr><td colspan="4" class="text-center">No data available</td></tr>';
        }
        $('#top-requesters-table').html(html);
    }
    
    // Period filter buttons
    $('.period-filter').on('click', function() {
        $('.period-filter').removeClass('active');
        $(this).addClass('active');
        currentPeriod = $(this).data('period');
        loadAnalytics();
    });
    
    // Load analytics when tab is shown
    $('#analytics-tab').on('shown.bs.tab', function() {
        if (!statusChart) {
            loadAnalytics();
        }
    });
    
    // Helper function to show alerts using Bootstrap
    function showAlert(title, message, type = 'info') {
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const alertHtml = `
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" 
                 style="z-index: 9999; min-width: 300px; max-width: 500px;" role="alert">
                <strong>${title}</strong><br>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('body').append(alertHtml);
        setTimeout(() => {
            $('.alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
    
    // Reset modals when closed
    $('#hrInitialReviewModal, #hrFinalApprovalModal, #hrReturnVerifyModal, #hodReviewModal, #confirmReturnModal').on('hidden.bs.modal', function() {
        $(this).find('form')[0]?.reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').remove();
    });
    
    // Initialize date and time fields with current date/time
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const currentDate = `${year}-${month}-${day}`;
    const currentTime = `${hours}:${minutes}`;
    
    // Set default values for new requests
    $('#requestPermissionModal').on('show.bs.modal', function() {
        // Only set defaults if fields are empty (not when coming from calendar selection)
        if (!$('#start_date').val()) {
            $('#start_date').val(currentDate);
            $('#start_time').val('08:00');
        }
        if (!$('#end_date').val()) {
            $('#end_date').val(currentDate);
            $('#end_time').val('17:00');
        }
        updateDateTimeFields();
    });
    
    // Reset form when modal is hidden
    $('#requestPermissionModal').on('hidden.bs.modal', function() {
        $('#requestPermissionForm')[0].reset();
        $('#start_date, #end_date, #start_time, #end_time').val('');
        $('#start_datetime, #end_datetime').val('');
    });
    
    // Request Permission
    $('#new-permission-btn, #new-permission-btn-empty, #new-permission-calendar-btn').on('click', function() {
        $('#requestPermissionModal').modal('show');
    });
    
    // Initialize tabs - activate first tab with content
    $(document).ready(function() {
        // Auto-activate first tab with badge > 0 (other than "All")
        const tabs = $('#permissionTabs .nav-link');
        tabs.each(function() {
            const badge = $(this).find('.badge');
            if (badge.length && parseInt(badge.text()) > 0 && !$(this).hasClass('active')) {
                const targetTab = $(this).data('bs-target');
                if (targetTab && $(targetTab).length) {
                    $(this).tab('show');
                    return false; // break loop
                }
            }
        });
    });
    
    // Combine date and time inputs into datetime fields before submission
    function updateDateTimeFields() {
        const startDate = $('#start_date').val();
        const startTime = $('#start_time').val();
        const endDate = $('#end_date').val();
        const endTime = $('#end_time').val();
        
        if (startDate && startTime) {
            $('#start_datetime').val(startDate + 'T' + startTime);
        }
        if (endDate && endTime) {
            $('#end_datetime').val(endDate + 'T' + endTime);
        }
    }
    
    // Update datetime fields when date or time changes
    $('#start_date, #start_time, #end_date, #end_time').on('change', function() {
        updateDateTimeFields();
    });
    
    $('#requestPermissionForm').on('submit', function(e) {
        e.preventDefault();
        
        // Update datetime fields before submission
        updateDateTimeFields();
        
        // Validate dates and times
        const startDate = $('#start_date').val();
        const startTime = $('#start_time').val();
        const endDate = $('#end_date').val();
        const endTime = $('#end_time').val();
        
        if (!startDate || !startTime || !endDate || !endTime) {
            showAlert('Validation Error', 'Please fill in all date and time fields.', 'warning');
            return;
        }
        
        const startDateTime = new Date(startDate + 'T' + startTime);
        const endDateTime = new Date(endDate + 'T' + endTime);
        
        if (endDateTime <= startDateTime) {
            showAlert('Invalid Date/Time', 'The end date and time must be after the start date and time.', 'warning');
            return;
        }
        
        const formData = $(this).serialize();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: '{{ route("permissions.store") }}',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Reset form
                    $('#requestPermissionForm')[0].reset();
                    $('#start_date, #end_date, #start_time, #end_time').val('');
                    $('#start_datetime, #end_datetime').val('');
                    const modal = bootstrap.Modal.getInstance(document.getElementById('requestPermissionModal'));
                    modal.hide();
                    
                    // Show success message
                    showAlert('Success!', response.message || 'Permission request submitted successfully', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response.message || 'Failed to submit permission request', 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                
                let errorMessage = 'An error occurred. Please try again.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        // Validation errors
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        errorMessage = errors.join('<br>');
                    }
                }
                
                showAlert('Submission Failed', errorMessage, 'error');
            }
        });
    });
    
    // Handle dropdown menu clicks for admin actions
    $(document).on('click', '.dropdown-item.btn-hr-initial-review, .dropdown-item.btn-hod-review, .dropdown-item.btn-hr-final-approve, .dropdown-item.btn-hr-return-verify', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        const action = $(this).hasClass('btn-hr-initial-review') ? 'hr-initial-review' :
                      $(this).hasClass('btn-hod-review') ? 'hod-review' :
                      $(this).hasClass('btn-hr-final-approve') ? 'hr-final-approve' :
                      $(this).hasClass('btn-hr-return-verify') ? 'hr-return-verify' : null;
        
        if (action === 'hr-initial-review') {
            $('.btn-hr-initial-review[data-id="' + requestId + '"]').first().click();
        } else if (action === 'hod-review') {
            $('.btn-hod-review[data-id="' + requestId + '"]').first().click();
        } else if (action === 'hr-final-approve') {
            $('.btn-hr-final-approve[data-id="' + requestId + '"]').first().click();
        } else if (action === 'hr-return-verify') {
            $('.btn-hr-return-verify[data-id="' + requestId + '"]').first().click();
        }
    });
    
    // HR Initial Review
    $(document).on('click', '.btn-hr-initial-review', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        
        $('#hr-initial-review-request-id').val(requestId);
        const modal = new bootstrap.Modal(document.getElementById('hrInitialReviewModal'));
        modal.show();
        
        // Focus on decision select when modal opens
        $('#hrInitialReviewModal').on('shown.bs.modal', function() {
            $('#hrInitialDecision').focus();
        });
    });
    
    // HR Initial Review Form Submission
    $('#hrInitialReviewForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#hr-initial-review-request-id').val();
        const decision = $('#hrInitialDecision').val();
        const comments = $('#hrInitialComments').val();
                
                if (!decision) {
            showAlert('Validation Error', 'Please select a decision.', 'warning');
            return;
        }
        if (!comments || !comments.trim()) {
            showAlert('Validation Error', 'Please provide comments.', 'warning');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
                    $.ajax({
                        type: 'POST',
                        url: `/permissions/${requestId}/hr-initial-review`,
            data: {
                _token: csrfToken,
                decision: decision,
                comments: comments
            },
                        headers: {
                'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            if (response && response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('hrInitialReviewModal'));
                    modal.hide();
                    showAlert('Success!', response.message || 'Review submitted successfully', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                            } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response?.message || 'An error occurred. Please try again.', 'error');
                            }
                        },
                        error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                            let message = 'An error occurred. Please try again.';
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                } else if (xhr.responseJSON.errors) {
                                    const errors = Object.values(xhr.responseJSON.errors).flat();
                                    message = errors.join(', ');
                                }
                            } else if (xhr.status === 403) {
                                message = 'You are not authorized to review this request.';
                            } else if (xhr.status === 404) {
                                message = 'Permission request not found.';
                            } else if (xhr.status === 422) {
                                message = 'Validation error. Please check all fields.';
                            }
                showAlert('Error', message, 'error');
            }
        });
    });
    
    // HOD Review
    $(document).on('click', '.btn-hod-review', function() {
        $('#hod-review-request-id').val($(this).data('id'));
        $('#hodReviewModal').modal('show');
    });
    
    $('#hodReviewForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#hod-review-request-id').val();
        const formData = $(this).serialize();
        
        // Validate decision and comments
        const decision = $('select[name="decision"]').val();
        const comments = $('textarea[name="comments"]').val();
        
        if (!decision) {
            showAlert('Decision Required', 'Please select a decision.', 'warning');
            return;
        }
        
        if (!comments || !comments.trim()) {
            showAlert('Comments Required', 'Please provide comments for your decision.', 'warning');
            return;
        }
        
        // Show loading
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: `/permissions/${requestId}/hod-review`,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('hodReviewModal'));
                    modal.hide();
                    showAlert('Success!', response.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response.message || 'An error occurred', 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join('<br>');
                    }
                } else if (xhr.status === 403) {
                    message = 'You are not authorized to review this request.';
                } else if (xhr.status === 404) {
                    message = 'Permission request not found.';
                } else if (xhr.status === 422) {
                    message = 'Validation error. Please check all fields.';
                }
                showAlert('Error', message, 'error');
            }
        });
    });
    
    // HR Final Approval
    $(document).on('click', '.btn-hr-final-approve', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        
        $('#hr-final-approval-request-id').val(requestId);
        const modal = new bootstrap.Modal(document.getElementById('hrFinalApprovalModal'));
        modal.show();
        
        // Focus on decision select when modal opens
        $('#hrFinalApprovalModal').on('shown.bs.modal', function() {
            $('#hrFinalDecision').focus();
        });
    });
    
    // HR Final Approval Form Submission
    $('#hrFinalApprovalForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#hr-final-approval-request-id').val();
        const decision = $('#hrFinalDecision').val();
        const comments = $('#hrFinalComments').val();
                
                if (!decision) {
            showAlert('Validation Error', 'Please select a decision.', 'warning');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
                    type: 'POST',
                    url: `/permissions/${requestId}/hr-final-approval`,
            data: {
                _token: csrfToken,
                decision: decision,
                comments: comments || ''
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
                    success: function(response) {
                if (response && response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('hrFinalApprovalModal'));
                    modal.hide();
                    showAlert('Success!', response.message || 'Decision submitted successfully', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                        } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response?.message || 'An error occurred. Please try again.', 'error');
                        }
                    },
                    error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join(', ');
                    }
                } else if (xhr.status === 403) {
                    message = 'You are not authorized to approve this request.';
                } else if (xhr.status === 404) {
                    message = 'Permission request not found.';
                } else if (xhr.status === 422) {
                    message = 'Validation error. Please check all fields.';
                }
                showAlert('Error', message, 'error');
            }
        });
    });
    
    // HR Return Verification
    $(document).on('click', '.btn-hr-return-verify', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        
        $('#hr-return-verify-request-id').val(requestId);
        const modal = new bootstrap.Modal(document.getElementById('hrReturnVerifyModal'));
        modal.show();
        
        // Focus on decision select when modal opens
        $('#hrReturnVerifyModal').on('shown.bs.modal', function() {
            $('#hrReturnDecision').focus();
        });
    });
    
    // HR Return Verification Form Submission
    $('#hrReturnVerifyForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#hr-return-verify-request-id').val();
        const decision = $('#hrReturnDecision').val();
        const comments = $('#hrReturnComments').val();
                
                if (!decision) {
            showAlert('Validation Error', 'Please select a decision.', 'warning');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
                    type: 'POST',
                    url: `/permissions/${requestId}/hr-return-verification`,
            data: {
                _token: csrfToken,
                decision: decision,
                comments: comments || ''
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
                    success: function(response) {
                if (response && response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('hrReturnVerifyModal'));
                    modal.hide();
                    showAlert('Success!', response.message || 'Return verification completed successfully', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                        } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response?.message || 'An error occurred. Please try again.', 'error');
                        }
                    },
                    error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join(', ');
                    }
                } else if (xhr.status === 403) {
                    message = 'You are not authorized to verify this return.';
                } else if (xhr.status === 404) {
                    message = 'Permission request not found.';
                } else if (xhr.status === 422) {
                    message = 'Validation error. Please check all fields.';
                }
                showAlert('Error', message, 'error');
            }
        });
    });
    
    // CEO Approval (kept for compatibility if needed)
    $(document).on('click', '.btn-ceo-approve', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        
        $('#ceo-approval-request-id').val(requestId);
        const modal = new bootstrap.Modal(document.getElementById('ceoApprovalModal'));
        modal.show();
        
        // Focus on decision select when modal opens
        $('#ceoApprovalModal').on('shown.bs.modal', function() {
            $('#ceoApprovalModal select[name="decision"]').focus();
        });
    });
    
    $('#ceoApprovalForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#ceo-approval-request-id').val();
        const formData = $(this).serialize();
        
        const decision = $('select[name="decision"]').val();
        if (!decision) {
            showAlert('Decision Required', 'Please select a decision.', 'warning');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: `/permissions/${requestId}/ceo-approval`,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('ceoApprovalModal'));
                    modal.hide();
                    showAlert('Success!', response.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response.message || 'An error occurred', 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('Error', message, 'error');
            }
        });
    });
    
    // Confirm Return
    $(document).on('click', '.btn-confirm-return', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        
        $('#confirm-return-request-id').val(requestId);
        const modal = new bootstrap.Modal(document.getElementById('confirmReturnModal'));
        modal.show();
        
        // Focus on return datetime when modal opens
        $('#confirmReturnModal').on('shown.bs.modal', function() {
            $('#confirmReturnModal input[name="return_datetime"]').focus();
        });
    });
    
    $('#confirmReturnForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#confirm-return-request-id').val();
        const formData = $(this).serialize();
        
        // Validate return datetime
        const returnDateTime = $('input[name="return_datetime"]').val();
        if (!returnDateTime) {
            showAlert('Return Date Required', 'Please select your return date and time.', 'warning');
            return;
        }
        
        // Show loading
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: `/permissions/${requestId}/confirm-return`,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmReturnModal'));
                    modal.hide();
                    showAlert('Success!', response.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response.message || 'An error occurred', 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join('<br>');
                    }
                } else if (xhr.status === 403) {
                    message = 'You are not authorized to perform this action.';
                } else if (xhr.status === 404) {
                    message = 'Permission request not found.';
                } else if (xhr.status === 422) {
                    message = 'Validation error. Please check all fields.';
                }
                showAlert('Error', message, 'error');
            }
        });
    });
    
    // HOD Return Approval
    $(document).on('click', '.btn-hod-return', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        
        $('#hod-return-request-id').val(requestId);
        const modal = new bootstrap.Modal(document.getElementById('hodReturnModal'));
        modal.show();
        
        // Focus on decision select when modal opens
        $('#hodReturnModal').on('shown.bs.modal', function() {
            $('#hodReturnModal select[name="decision"]').focus();
        });
    });
    
    $('#hodReturnForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#hod-return-request-id').val();
        const formData = $(this).serialize();
        
        const decision = $('select[name="decision"]').val();
        if (!decision) {
            showAlert('Decision Required', 'Please select a decision.', 'warning');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: `/permissions/${requestId}/hod-return`,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    const modal = bootstrap.Modal.getInstance(document.getElementById('hodReturnModal'));
                    modal.hide();
                    showAlert('Success!', response.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response.message || 'An error occurred', 'error');
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalBtnText);
                let message = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('Error', message, 'error');
            }
        });
    });
    
    // View Details - Now redirects to dedicated page (handled by link in card)
    
    // Close analytics initialization
}); // End $(document).ready
</script>
@endpush
