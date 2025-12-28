@extends('layouts.app')

@section('title', 'Assessments Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Professional Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-target-lock me-2"></i>Assessments Management System
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                Main responsibilities, activities, and progress reports with advanced analytics and performance tracking
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('assessments.create') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-plus-circle me-2"></i>Create New Assessment
                            </a>
                            @if($isAdmin || $isHR)
                            <a href="{{ route('assessments.analytics.page') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-bar-chart-alt-2 me-2"></i>View Analytics
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(($isAdmin || $isHR) && isset($statistics) && !empty($statistics))
    <!-- Advanced Statistics Dashboard -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-primary" style="border-left: 4px solid var(--bs-primary) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-primary">
                            <i class="bx bx-target-lock fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Assessments</h6>
                            <h3 class="mb-0 fw-bold text-primary">{{ $statistics['total_assessments'] }}</h3>
                            <div class="mt-2">
                                <span class="badge bg-success">{{ $statistics['approved_assessments'] }} Approved</span>
                                <span class="badge bg-warning">{{ $statistics['pending_assessments'] }} Pending</span>
                                <span class="badge bg-danger">{{ $statistics['rejected_assessments'] }} Rejected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-info" style="border-left: 4px solid var(--bs-info) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-info">
                            <i class="bx bx-list-ul fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Activities</h6>
                            <h3 class="mb-0 fw-bold text-info">{{ $statistics['total_activities'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-success" style="border-left: 4px solid var(--bs-success) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-success">
                            <i class="bx bx-file fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Total Reports</h6>
                            <h3 class="mb-0 fw-bold text-success">{{ $statistics['total_reports'] }}</h3>
                            <div class="mt-2">
                                <span class="badge bg-success">{{ $statistics['approved_reports'] }} Approved</span>
                                <span class="badge bg-warning">{{ $statistics['pending_reports'] }} Pending</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 border-warning" style="border-left: 4px solid var(--bs-warning) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-lg me-3 bg-warning">
                            <i class="bx bx-calendar fs-2 text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1 small">Reports This Month</h6>
                            <h3 class="mb-0 fw-bold text-warning">{{ $statistics['reports_this_month'] }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(($awaitingMyAction ?? collect())->count() > 0)
    <div class="card border-0 shadow-sm mb-4 border-warning" style="border-left: 4px solid var(--bs-warning) !important;">
        <div class="card-header bg-warning text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-time-five me-2"></i>Awaiting My Review & Approval
                    </h5>
                    <small class="text-white-50">{{ $awaitingMyAction->count() }} assessment(s) pending your action</small>
                </div>
                <span class="badge bg-light text-dark fs-6">{{ $awaitingMyAction->count() }}</span>
            </div>
        </div>
        <div class="card-body">
            @foreach($awaitingMyAction as $a)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">{{ $a->main_responsibility }}</h6>
                            <p class="text-muted small mb-0">
                                <strong>Employee:</strong> {{ optional($a->employee)->name ?? '—' }} 
                                @if(optional($a->employee)->primaryDepartment)
                                | <strong>Department:</strong> {{ optional($a->employee)->primaryDepartment->name }}
                                @endif
                            </p>
                            @if($a->description)
                            <p class="small mb-2">{{ $a->description }}</p>
                            @endif
                        </div>
                        <span class="badge bg-warning">Pending HOD</span>
                    </div>
                    
                    @if($a->activities && $a->activities->count() > 0)
                    <div class="mb-2">
                        <strong class="small">Activities ({{ $a->activities->count() }}):</strong>
                        <ul class="small mb-0">
                            @foreach($a->activities as $act)
                            <li>{{ $act->activity_name }} 
                                <span class="text-muted">({{ $act->reporting_frequency }}, {{ $act->contribution_percentage }}%)</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-success approve-assessment" data-id="{{ $a->id }}">
                            <i class="bx bx-check"></i> Approve
                        </button>
                        <button type="button" class="btn btn-sm btn-danger reject-assessment" data-id="{{ $a->id }}">
                            <i class="bx bx-x"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-my" data-bs-toggle="tab" data-bs-target="#pane-my" type="button" role="tab">
                <i class="bx bx-user"></i> My Assessments 
                <span class="badge bg-primary">{{ ($myAssessments ?? collect())->count() }}</span>
            </button>
        </li>
        @if(($isHOD ?? false) || ($isHR ?? false) || ($isAdmin ?? false))
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-other" data-bs-toggle="tab" data-bs-target="#pane-other" type="button" role="tab">
                <i class="bx bx-group"></i> Other Assessments 
                <span class="badge bg-info">{{ ($otherAssessments ?? collect())->count() }}</span>
            </button>
        </li>
        @endif
        @if(($isHOD ?? false) || ($isHR ?? false) || ($isAdmin ?? false))
        @php
            $pendingCount = ($otherAssessments ?? collect())->where('status', 'pending_hod')->count();
            $approvedCount = ($otherAssessments ?? collect())->where('status', 'approved')->count();
            $rejectedCount = ($otherAssessments ?? collect())->where('status', 'rejected')->count();
        @endphp
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-pending" data-bs-toggle="tab" data-bs-target="#pane-pending" type="button" role="tab">
                <i class="bx bx-time-five"></i> Pending Approval 
                <span class="badge bg-warning">{{ $pendingCount }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-approved" data-bs-toggle="tab" data-bs-target="#pane-approved" type="button" role="tab">
                <i class="bx bx-check-circle"></i> Approved 
                <span class="badge bg-success">{{ $approvedCount }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-rejected" data-bs-toggle="tab" data-bs-target="#pane-rejected" type="button" role="tab">
                <i class="bx bx-x-circle"></i> Rejected 
                <span class="badge bg-danger">{{ $rejectedCount }}</span>
            </button>
        </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-calendar" data-bs-toggle="tab" data-bs-target="#pane-calendar" type="button" role="tab">
                <i class="bx bx-calendar"></i> Calendar View
            </button>
        </li>
        @if(($isHOD ?? false) || ($isHR ?? false) || ($isAdmin ?? false))
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-analytics" data-bs-toggle="tab" data-bs-target="#pane-analytics" type="button" role="tab">
                <i class="bx bx-bar-chart-alt-2"></i> Analytics
            </button>
        </li>
        @endif
    </ul>
    <div class="tab-content border-0 shadow-sm p-4 rounded">
        <div class="tab-pane fade show active" id="pane-my" role="tabpanel" aria-labelledby="tab-my">
            @if(($myAssessments ?? collect())->isEmpty())
                <div class="text-muted">No assessments.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Main Responsibility</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myAssessments as $a)
                            <tr>
                                <td>
                                    <strong>{{ $a->main_responsibility }}</strong>
                                    @if($a->description)
                                    <br><small class="text-muted">{{ Str::limit($a->description, 60) }}</small>
                                    @endif
                                    @if($a->activities)
                                    <br><small class="text-muted"><i class="bx bx-list-ul"></i> {{ $a->activities->count() }} activities | <i class="bx bx-percent"></i> {{ $a->contribution_percentage }}% contribution</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $a->status==='approved'?'success':($a->status==='rejected'?'danger':'warning') }}">
                                        {{ ucfirst(str_replace('_',' ', $a->status)) }}
                                    </span>
                                    @if($a->status === 'pending_hod')
                                    <br><small class="text-muted">Awaiting HOD approval</small>
                                    @elseif($a->status === 'approved' && $a->hod_approved_at)
                                    <br><small class="text-muted">Approved {{ $a->hod_approved_at->diffForHumans() }}</small>
                                    @elseif($a->status === 'rejected' && $a->hod_approved_at)
                                    <br><small class="text-muted">Rejected {{ $a->hod_approved_at->diffForHumans() }}</small>
                                    @endif
                                    @if($a->hodApprover)
                                    <br><small class="text-muted">by {{ $a->hodApprover->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $a->created_at?->format('Y-m-d') }}</div>
                                    <small class="text-muted">{{ $a->created_at?->format('H:i') }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if($a->status==='approved' && $a->activities)
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#acts-{{ $a->id }}">
                                                <i class="bx bx-file"></i> Report Progress
                                            </button>
                                        @endif
                                        <a href="{{ route('assessments.show', $a->id) }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-show"></i> View Details
                                        </a>
                                        @if($isAdmin || $isHR)
                                        <a href="{{ route('assessments.edit', $a->id) }}" class="btn btn-outline-warning">
                                            <i class="bx bx-edit"></i> Edit
                                        </a>
                                        @endif
                                        @if($isAdmin)
                                        <button type="button" class="btn btn-outline-danger btn-delete-assessment" data-assessment-id="{{ $a->id }}" data-assessment-name="{{ $a->main_responsibility }}">
                                            <i class="bx bx-trash"></i> Delete
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if($a->status==='approved' && $a->activities && $a->activities->count()>0)
                            <tr class="collapse" id="acts-{{ $a->id }}">
                                <td colspan="4">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Activity</th>
                                                    <th>Frequency</th>
                                                    <th>Contribution</th>
                                                    <th class="text-end">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($a->activities as $act)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $act->activity_name }}</strong>
                                                        @if($act->description)
                                                        <br><small class="text-muted">{{ Str::limit($act->description, 50) }}</small>
                                                        @endif
                                                        @php $ps = ($currentPeriodStatus[$act->id] ?? null); @endphp
                                                        @if($ps && $ps['exists'])
                                                            <br>
                                                            <small>
                                                                <span class="badge bg-{{ $ps['status']==='approved'?'success':($ps['status']==='pending_approval'?'warning':'danger') }}">
                                                                    {{ ucfirst(str_replace('_',' ', $ps['status'] ?? '')) }} this period
                                                                </span>
                                                                @if($ps['report_date']) on {{ \Carbon\Carbon::parse($ps['report_date'])->format('M d, Y') }} @endif
                                                            </small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">{{ ucfirst($act->reporting_frequency) }}</span>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $act->contribution_percentage }}%</strong>
                                                        <br><small class="text-muted">Weight</small>
                                                    </td>
                                                    <td class="text-end">
                                                        @php 
                                                            // STRICT CHECK: Hide button if already submitted (only show if no report exists OR if rejected)
                                                            $hasExistingReport = false;
                                                            $isRejected = false;
                                                            $canSubmit = true;
                                                            
                                                            if ($ps && isset($ps['exists'])) {
                                                                $hasExistingReport = ($ps['exists'] === true || $ps['exists'] === '1' || $ps['exists'] === 1);
                                                                if ($hasExistingReport && isset($ps['status'])) {
                                                                    $isRejected = ($ps['status'] === 'rejected');
                                                                }
                                                                $canSubmit = !$hasExistingReport || $isRejected;
                                                            }
                                                        @endphp
                                                        
                                                        @if($hasExistingReport && !$isRejected)
                                                            <div class="alert alert-info mb-2 py-2 px-3" style="font-size: 0.875rem;">
                                                                <i class="bx bx-info-circle"></i> 
                                                                <strong>Already Submitted:</strong> 
                                                                Report for this {{ strtolower($act->reporting_frequency) }} period has been submitted
                                                                @if(isset($ps['report_date']) && $ps['report_date'])
                                                                    on {{ \Carbon\Carbon::parse($ps['report_date'])->format('M d, Y') }}
                                                                @endif
                                                                @if(isset($ps['status']) && $ps['status'] === 'pending_approval')
                                                                    <br><small class="text-warning"><i class="bx bx-time"></i> Status: Awaiting HOD approval</small>
                                                                    @if(isset($ps['submitted_at']) && $ps['submitted_at'])
                                                                        <br><small class="text-muted">Submitted: {{ \Carbon\Carbon::parse($ps['submitted_at'])->format('M d, Y H:i') }}</small>
                                                                    @endif
                                                                @elseif(isset($ps['status']) && $ps['status'] === 'approved')
                                                                    <br><small class="text-success"><i class="bx bx-check-circle"></i> Status: Approved</small>
                                                                    @if(isset($ps['approver']) && $ps['approver'])
                                                                        <br><small class="text-muted">by {{ $ps['approver'] }}</small>
                                                                    @endif
                                                                    @if(isset($ps['submitted_at']) && $ps['submitted_at'])
                                                                        <br><small class="text-muted">Submitted: {{ \Carbon\Carbon::parse($ps['submitted_at'])->format('M d, Y H:i') }}</small>
                                                                    @endif
                                                                @endif
                                                            </div>
                                                        @elseif($canSubmit)
                                                            <a href="{{ route('assessments.progress.create', $act->id) }}" class="btn btn-sm btn-primary">
                                                                <i class="bx bx-file"></i> Submit Progress
                                                            </a>
                                                        @endif
                                                        
                                                        <div class="btn-group btn-group-sm mt-1" role="group">
                                                            <a href="{{ route('assessments.activities.reports', $act->id) }}" class="btn btn-sm btn-outline-secondary">
                                                                <i class="bx bx-list-ul"></i> View Reports
                                                            </a>
                                                            @if($isAdmin || $isHR)
                                                            <button type="button" class="btn btn-sm btn-outline-warning btn-edit-activity" data-activity-id="{{ $act->id }}" data-activity-name="{{ $act->activity_name }}" data-frequency="{{ $act->reporting_frequency }}" data-description="{{ $act->description }}" data-contribution="{{ $act->contribution_percentage }}">
                                                                <i class="bx bx-edit"></i> Edit
                                                            </button>
                                                            @endif
                                                            @if($isAdmin)
                                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-activity" data-activity-id="{{ $act->id }}" data-activity-name="{{ $act->activity_name }}">
                                                                <i class="bx bx-trash"></i> Delete
                                                            </button>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @if(($isHOD ?? false) || ($isHR ?? false) || ($isAdmin ?? false))
        <div class="tab-pane fade" id="pane-other" role="tabpanel" aria-labelledby="tab-other">
            @if(($otherAssessments ?? collect())->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bx bx-inbox" style="font-size: 3rem;"></i>
                    <p class="mt-2">No other assessments found.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Main Responsibility</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($otherAssessments as $a)
                            <tr>
                                <td>
                                    <strong>{{ optional($a->employee)->name ?? '—' }}</strong>
                                    @if(optional($a->employee)->email)
                                    <br><small class="text-muted"><i class="bx bx-envelope"></i> {{ $a->employee->email }}</small>
                                    @endif
                                    @if(optional($a->employee)->primaryDepartment)
                                    <br><small class="text-muted"><i class="bx bx-building"></i> {{ optional($a->employee)->primaryDepartment->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $a->main_responsibility }}</strong>
                                    @if($a->description)
                                    <br><small class="text-muted">{{ Str::limit($a->description, 60) }}</small>
                                    @endif
                                    @if($a->activities)
                                    <br><small class="text-muted"><i class="bx bx-list-ul"></i> {{ $a->activities->count() }} activities | <i class="bx bx-percent"></i> {{ $a->contribution_percentage }}% contribution</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $a->status==='approved'?'success':($a->status==='rejected'?'danger':'warning') }}">
                                        {{ ucfirst(str_replace('_',' ', $a->status)) }}
                                    </span>
                                    @if($a->status === 'approved' && $a->hod_approved_at)
                                    <br><small class="text-muted">Approved {{ $a->hod_approved_at->diffForHumans() }}</small>
                                    @elseif($a->status === 'rejected' && $a->hod_approved_at)
                                    <br><small class="text-muted">Rejected {{ $a->hod_approved_at->diffForHumans() }}</small>
                                    @endif
                                    @if($a->hodApprover)
                                    <br><small class="text-muted">by {{ $a->hodApprover->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $a->created_at?->format('Y-m-d') }}</div>
                                    <small class="text-muted">{{ $a->created_at?->format('H:i') }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('assessments.show', $a->id) }}" class="btn btn-outline-secondary">
                                            <i class="bx bx-show"></i> View Details
                                        </a>
                                        @if($isAdmin || $isHR)
                                        <a href="{{ route('assessments.edit', $a->id) }}" class="btn btn-outline-warning">
                                            <i class="bx bx-edit"></i> Edit
                                        </a>
                                        @endif
                                        @if($isAdmin)
                                        <button type="button" class="btn btn-outline-danger btn-delete-assessment" data-assessment-id="{{ $a->id }}" data-assessment-name="{{ $a->main_responsibility }}">
                                            <i class="bx bx-trash"></i> Delete
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <!-- Pending Approval Tab -->
        <div class="tab-pane fade" id="pane-pending" role="tabpanel" aria-labelledby="tab-pending">
            @php $pendingAssessments = ($otherAssessments ?? collect())->where('status', 'pending_hod'); @endphp
            @if($pendingAssessments->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bx bx-time-five" style="font-size: 3rem;"></i>
                    <p class="mt-2">No pending assessments.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead class="table-warning">
                            <tr>
                                <th>Employee</th>
                                <th>Main Responsibility</th>
                                <th>Department</th>
                                <th>Created</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingAssessments as $a)
                            <tr>
                                <td>
                                    <strong>{{ optional($a->employee)->name ?? '—' }}</strong>
                                    @if(optional($a->employee)->email)
                                    <br><small class="text-muted"><i class="bx bx-envelope"></i> {{ $a->employee->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $a->main_responsibility }}</strong>
                                    @if($a->description)
                                    <br><small class="text-muted">{{ Str::limit($a->description, 60) }}</small>
                                    @endif
                                    @if($a->activities)
                                    <br><small class="text-muted"><i class="bx bx-list-ul"></i> {{ $a->activities->count() }} activities | <i class="bx bx-percent"></i> {{ $a->contribution_percentage }}%</small>
                                    @endif
                                </td>
                                <td>
                                    @if(optional($a->employee)->primaryDepartment)
                                    <span class="badge bg-info">{{ optional($a->employee)->primaryDepartment->name }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $a->created_at?->format('Y-m-d') }}</div>
                                    <small class="text-muted">{{ $a->created_at?->format('H:i') }}</small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-view-assessment" data-assessment-id="{{ $a->id }}" data-assessment-name="{{ $a->main_responsibility }}">
                                            <i class="bx bx-show"></i> View
                                        </button>
                                        @if(($isHOD || $isHR || $isAdmin) && $a->status === 'pending_hod')
                                        <button type="button" class="btn btn-success approve-assessment" data-id="{{ $a->id }}">
                                            <i class="bx bx-check"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-danger reject-assessment" data-id="{{ $a->id }}">
                                            <i class="bx bx-x"></i> Reject
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <!-- Approved Tab -->
        <div class="tab-pane fade" id="pane-approved" role="tabpanel" aria-labelledby="tab-approved">
            @php $approvedAssessments = ($otherAssessments ?? collect())->where('status', 'approved'); @endphp
            @if($approvedAssessments->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bx bx-check-circle" style="font-size: 3rem;"></i>
                    <p class="mt-2">No approved assessments.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead class="table-success">
                            <tr>
                                <th>Employee</th>
                                <th>Main Responsibility</th>
                                <th>Approved By</th>
                                <th>Approved Date</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvedAssessments as $a)
                            <tr>
                                <td>
                                    <strong>{{ optional($a->employee)->name ?? '—' }}</strong>
                                    @if(optional($a->employee)->email)
                                    <br><small class="text-muted"><i class="bx bx-envelope"></i> {{ $a->employee->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $a->main_responsibility }}</strong>
                                    @if($a->description)
                                    <br><small class="text-muted">{{ Str::limit($a->description, 60) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($a->hodApprover)
                                    <span class="badge bg-success">{{ $a->hodApprover->name }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($a->hod_approved_at)
                                    <div>{{ $a->hod_approved_at->format('Y-m-d') }}</div>
                                    <small class="text-muted">{{ $a->hod_approved_at->format('H:i') }}</small>
                                    <br><small class="text-muted">{{ $a->hod_approved_at->diffForHumans() }}</small>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-view-assessment" data-assessment-id="{{ $a->id }}" data-assessment-name="{{ $a->main_responsibility }}">
                                            <i class="bx bx-show"></i> View
                                        </button>
                                        @if($isAdmin || $isHR)
                                        <a href="{{ route('assessments.edit', $a->id) }}" class="btn btn-outline-warning">
                                            <i class="bx bx-edit"></i> Edit
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <!-- Rejected Tab -->
        <div class="tab-pane fade" id="pane-rejected" role="tabpanel" aria-labelledby="tab-rejected">
            @php $rejectedAssessments = ($otherAssessments ?? collect())->where('status', 'rejected'); @endphp
            @if($rejectedAssessments->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bx bx-x-circle" style="font-size: 3rem;"></i>
                    <p class="mt-2">No rejected assessments.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover">
                        <thead class="table-danger">
                            <tr>
                                <th>Employee</th>
                                <th>Main Responsibility</th>
                                <th>Rejected By</th>
                                <th>Rejection Date</th>
                                <th>Comments</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rejectedAssessments as $a)
                            <tr>
                                <td>
                                    <strong>{{ optional($a->employee)->name ?? '—' }}</strong>
                                    @if(optional($a->employee)->email)
                                    <br><small class="text-muted"><i class="bx bx-envelope"></i> {{ $a->employee->email }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $a->main_responsibility }}</strong>
                                    @if($a->description)
                                    <br><small class="text-muted">{{ Str::limit($a->description, 60) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($a->hodApprover)
                                    <span class="badge bg-danger">{{ $a->hodApprover->name }}</span>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($a->hod_approved_at)
                                    <div>{{ $a->hod_approved_at->format('Y-m-d') }}</div>
                                    <small class="text-muted">{{ $a->hod_approved_at->format('H:i') }}</small>
                                    <br><small class="text-muted">{{ $a->hod_approved_at->diffForHumans() }}</small>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($a->hod_comments)
                                    <small class="text-muted">{{ Str::limit($a->hod_comments, 80) }}</small>
                                    @else
                                    <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-secondary btn-view-assessment" data-assessment-id="{{ $a->id }}" data-assessment-name="{{ $a->main_responsibility }}">
                                            <i class="bx bx-show"></i> View
                                        </button>
                                        @if($isAdmin || $isHR)
                                        <a href="{{ route('assessments.edit', $a->id) }}" class="btn btn-outline-warning">
                                            <i class="bx bx-edit"></i> Edit
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endif
        
        <!-- Calendar View Tab -->
        <div class="tab-pane fade" id="pane-calendar" role="tabpanel" aria-labelledby="tab-calendar">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bx bx-calendar"></i> Assessment & Progress Report Calendar</h5>
                </div>
                <div class="card-body">
                    <div id="assessment-calendar"></div>
                </div>
            </div>
        </div>
        
        <!-- Analytics Tab -->
        @if(($isHOD ?? false) || ($isHR ?? false) || ($isAdmin ?? false))
        <div class="tab-pane fade" id="pane-analytics" role="tabpanel" aria-labelledby="tab-analytics">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bx bx-bar-chart-alt-2"></i> Assessment Analytics</h5>
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text">Year</span>
                            <input type="number" class="form-control" id="analytics-year-input" value="{{ date('Y') }}" min="2000" max="2100">
                            <button class="btn btn-primary" id="btn-load-analytics-data">
                                <i class="bx bx-refresh"></i> Load
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="analytics-content">
                        <div class="text-center text-muted py-5">
                            <i class="bx bx-bar-chart-alt-2" style="font-size: 3rem;"></i>
                            <p class="mt-2">Click "Load" to view comprehensive analytics</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    @if(($isHOD ?? false) && ($pendingReports ?? collect())->count() > 0)
    <div class="card border-0 shadow-sm mb-4 border-info" style="border-left: 4px solid var(--bs-info) !important;">
        <div class="card-header bg-info text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 text-white">
                        <i class="bx bx-file me-2"></i>Pending Progress Reports
                    </h5>
                    <small class="text-white-50">{{ $pendingReports->count() }} report(s) awaiting your approval</small>
                </div>
                <span class="badge bg-light text-dark fs-6">{{ $pendingReports->count() }}</span>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Activity</th>
                            <th>Report Date</th>
                            <th>Excerpt</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingReports as $r)
                        <tr>
                            <td>{{ optional($r->activity->assessment->employee)->name ?? '—' }}</td>
                            <td>{{ $r->activity->activity_name }}</td>
                            <td>{{ $r->report_date?->format('Y-m-d') }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($r->progress_text, 80) }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-success approve-report" data-id="{{ $r->id }}">Approve</button>
                                    <button type="button" class="btn btn-danger reject-report" data-id="{{ $r->id }}">Reject</button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection



@push('scripts')
<!-- FullCalendar CSS -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css' rel='stylesheet' />
<!-- FullCalendar JS -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js'></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    // HOD/HR decisions on assessments
    function hodDecision(assessmentId, decision){
        const comments = prompt('Enter comments for this decision:');
        if (comments === null) return;
        fetch("{{ route('assessments.hod-approve', ['assessment' => 0]) }}".replace('/0/', '/'+assessmentId+'/'), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ decision: decision, comments: comments })
        }).then(async (res)=>{
            const data = await res.json().catch(()=>({success:false}));
            if(!res.ok || !data.success){ throw new Error(data.message||'Failed'); }
            window.location.reload();
        }).catch((e)=>{ alert(e.message||'Failed'); });
    }
    document.querySelectorAll('.approve-assessment').forEach(function(btn){
        btn.addEventListener('click', function(){ hodDecision(this.dataset.id, 'approve'); });
    });
    document.querySelectorAll('.reject-assessment').forEach(function(btn){
        btn.addEventListener('click', function(){ hodDecision(this.dataset.id, 'reject'); });
    });

    // All modals removed - using dedicated pages instead
    // Approve/Reject progress reports (HOD)
    function reportDecision(reportId, decision){
        const comments = decision==='reject' ? prompt('Enter comments (optional):') : '';
        fetch("{{ route('assessments.progress-approve', ['report' => 0]) }}".replace('/0/', '/'+reportId+'/'), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content'), 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ decision: decision, comments: comments })
        }).then(async (res)=>{
            const data = await res.json().catch(()=>({success:false}));
            if(!res.ok || !data.success){ throw new Error(data.message||'Failed'); }
            window.location.reload();
        }).catch((e)=>{ alert(e.message||'Failed'); });
    }
    document.querySelectorAll('.approve-report').forEach(function(btn){
        btn.addEventListener('click', function(){ reportDecision(this.dataset.id, 'approve'); });
    });
    document.querySelectorAll('.reject-report').forEach(function(btn){
        btn.addEventListener('click', function(){ reportDecision(this.dataset.id, 'reject'); });
    });

    // Annual Performance modal trigger
    const perfBtnHtml = '<div class="d-flex justify-content-end mb-3">\
        <div class="input-group" style="max-width:340px">\
            <span class="input-group-text">Year</span>\
            <input type="number" class="form-control" id="perf-year" min="2000" max="2100" value="'+new Date().getFullYear()+'"/>\
            <button class="btn btn-outline-secondary" id="perf-view">View</button>\
            <button class="btn btn-primary" id="perf-export">Export PDF</button>\
        </div>\
    </div>';
    const paneMy = document.getElementById('pane-my');
    if (paneMy && !paneMy.querySelector('#perf-year')) {
        const wrap = document.createElement('div');
        wrap.innerHTML = perfBtnHtml;
        paneMy.prepend(wrap.firstElementChild);
    }

    // Advanced Annual Performance Modal
    function ensurePerfModal(){
        if (document.getElementById('perfModal')) return;
        const html = '\n<div class="modal fade" id="perfModal" tabindex="-1" aria-hidden="true">\n  <div class="modal-dialog modal-xl modal-dialog-scrollable">\n    <div class="modal-content">\n      <div class="modal-header bg-primary text-white">\n        <h5 class="modal-title text-white"><i class="bx bx-chart"></i> Annual Performance Report</h5>\n        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>\n      </div>\n      <div class="modal-body p-4">\n        <div class="d-flex justify-content-between align-items-center mb-3">\n          <div><strong id="perf-year-display"></strong></div>\n          <div class="btn-group">\n            <button class="btn btn-sm btn-outline-primary" id="perf-export-detailed">Export Detailed PDF</button>\n            <button class="btn btn-sm btn-outline-success" id="perf-export-summary">Export Summary PDF</button>\n            <button class="btn btn-sm btn-outline-info" id="perf-print">Print</button>\n          </div>\n        </div>\n        <div id="perfBody"><div class="text-center text-muted py-4">Loading performance data...</div></div>\n      </div>\n      <div class="modal-footer">\n        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>\n      </div>\n    </div>\n  </div>\n</div>';
        const d = document.createElement('div'); d.innerHTML = html; document.body.appendChild(d.firstElementChild);
    }
    function renderPerf(data){
        const el = document.getElementById('perfBody');
        if (!data || !data.success) { el.innerHTML = '<div class="alert alert-danger">Failed to load performance data</div>'; return; }
        const total = parseFloat(data.total_performance || 0);
        const grade = total >= 90 ? 'Excellent' : total >= 75 ? 'Very Good' : total >= 60 ? 'Good' : total >= 50 ? 'Satisfactory' : 'Needs Improvement';
        const gradeColor = total >= 90 ? 'success' : total >= 75 ? 'primary' : total >= 60 ? 'info' : total >= 50 ? 'warning' : 'danger';
        let html = '<div class="row mb-4">\
            <div class="col-md-4">\
                <div class="card text-center border-0 shadow-sm">\
                    <div class="card-body">\
                        <h2 class="mb-0 text-'+gradeColor+'">'+total.toFixed(2)+'%</h2>\
                        <small class="text-muted">Overall Performance</small>\
                    </div>\
                </div>\
            </div>\
            <div class="col-md-4">\
                <div class="card text-center border-0 shadow-sm">\
                    <div class="card-body">\
                        <h4 class="mb-0">'+grade+'</h4>\
                        <small class="text-muted">Performance Grade</small>\
                    </div>\
                </div>\
            </div>\
            <div class="col-md-4">\
                <div class="card text-center border-0 shadow-sm">\
                    <div class="card-body">\
                        <h4 class="mb-0">'+(data.details||[]).length+'</h4>\
                        <small class="text-muted">Responsibilities</small>\
                    </div>\
                </div>\
            </div>\
        </div>';
        const rows = (data.details||[]).map(function(r){
            const perfPct = parseFloat(r.performance || 0);
            const acts = (r.activities||[]).map(function(a){
                const actScore = parseFloat(a.score || 0);
                const actBadge = actScore >= 90 ? 'success' : actScore >= 75 ? 'primary' : actScore >= 60 ? 'info' : actScore >= 50 ? 'warning' : 'danger';
                return '<tr>\
                    <td>'+escapeHtml(a.activity)+'</td>\
                    <td><span class="badge bg-secondary">'+a.frequency+'</span></td>\
                    <td>'+a.expected+'</td>\
                    <td>'+a.submitted+'</td>\
                    <td><span class="badge bg-'+actBadge+'">'+actScore.toFixed(2)+'%</span></td>\
                    <td>'+a.contribution+'%</td>\
                </tr>';
            }).join('');
            return '<div class="card mb-3 border-0 shadow-sm">\
                <div class="card-header bg-light d-flex justify-content-between align-items-center">\
                    <div><strong>'+escapeHtml(r.responsibility)+'</strong> <span class="badge bg-secondary">'+r.contribution+'% contribution</span></div>\
                    <div><span class="badge bg-'+gradeColor+' fs-6">Performance: '+perfPct.toFixed(2)+'%</span></div>\
                </div>\
                <div class="card-body">\
                    <div class="table-responsive">\
                        <table class="table table-sm table-hover mb-0">\
                            <thead><tr><th>Activity</th><th>Frequency</th><th>Expected</th><th>Submitted</th><th>Score</th><th>Weight</th></tr></thead>\
                            <tbody>'+acts+'</tbody>\
                        </table>\
                    </div>\
                </div>\
            </div>';
        }).join('');
        html += rows || '<div class="alert alert-info">No performance data available for this year.</div>';
        el.innerHTML = html;
        document.getElementById('perf-year-display').textContent = 'Performance Year: ' + data.year;
    }
    document.addEventListener('click', async function(e){
        if (e.target && e.target.id === 'perf-view'){
            ensurePerfModal();
            const year = document.getElementById('perf-year').value;
            const body = document.getElementById('perfBody');
            body.innerHTML = '<div class="text-center text-muted py-4"><div class="spinner-border" role="status"></div><div class="mt-2">Loading performance data...</div></div>';
            try {
                const url = "{{ route('assessments.performance') }}?year="+encodeURIComponent(year);
                const res = await fetch(url);
                
                if (!res.ok) {
                    throw new Error('Server returned status: ' + res.status);
                }
                
                const data = await res.json();
                if (!data || data.success === false) {
                    const msg = (data && data.message) ? escapeHtml(data.message) : 'Failed to load performance data.';
                    body.innerHTML = '<div class="alert alert-warning"><strong>Unable to compute performance</strong><br>' + msg + '<br><small class="text-muted">Please ensure you have approved assessments with activities for the selected year.</small></div>';
                } else {
                    renderPerf(data);
                }
                new bootstrap.Modal(document.getElementById('perfModal')).show();
            } catch(err) {
                console.error('Performance calculation error:', err);
                body.innerHTML = '<div class="alert alert-danger"><strong>Error Loading Performance Data</strong><br>' + escapeHtml(err.message || 'Failed to load performance data. Please try again.') + '<br><small class="text-muted">Check your browser console for more details.</small></div>';
                new bootstrap.Modal(document.getElementById('perfModal')).show();
            }
        }
        if (e.target && e.target.id === 'perf-export' || e.target.id === 'perf-export-detailed'){
            const year = document.getElementById('perf-year').value;
            window.open("{{ route('assessments.export', ['employeeId' => auth()->id()]) }}?year="+encodeURIComponent(year)+'&type=detailed', '_blank');
        }
        if (e.target && e.target.id === 'perf-export-summary'){
            const year = document.getElementById('perf-year').value;
            window.open("{{ route('assessments.export', ['employeeId' => auth()->id()]) }}?year="+encodeURIComponent(year)+'&type=summary', '_blank');
        }
        if (e.target && e.target.id === 'perf-print'){
            window.print();
        }
    });

    // ========== ADMIN MANAGEMENT FUNCTIONS ==========
    @if($isAdmin || $isHR)
    
    // Delete Assessment Handler
    document.addEventListener('click', function(e){
        if (e.target.closest('.btn-delete-assessment')) {
            const btn = e.target.closest('.btn-delete-assessment');
            const id = btn.dataset.assessmentId;
            const name = btn.dataset.assessmentName;
            if (confirm('Are you sure you want to delete the assessment "' + name + '"?\n\nThis will also delete all related activities and progress reports. This action cannot be undone.')) {
                fetch("{{ route('assessments.destroy', ['assessment' => 0]) }}".replace('/0/', '/'+id+'/'), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                }).then(async res => {
                    const data = await res.json();
                    if (res.ok && data.success) {
                        alert('Assessment deleted successfully');
                        window.location.reload();
                    } else {
                        alert('Failed to delete: ' + (data.message || 'Unknown error'));
                    }
                }).catch(err => {
                    alert('Error: ' + err.message);
                });
            }
        }
    });

    // Delete Activity Handler
    document.addEventListener('click', function(e){
        if (e.target.closest('.btn-delete-activity')) {
            const btn = e.target.closest('.btn-delete-activity');
            const id = btn.dataset.activityId;
            const name = btn.dataset.activityName;
            if (confirm('Are you sure you want to delete the activity "' + name + '"?\n\nThis will also delete all related progress reports. This action cannot be undone.')) {
                fetch("{{ route('assessments.activities.destroy', ['activity' => 0]) }}".replace('/0/', '/'+id+'/'), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                }).then(async res => {
                    const data = await res.json();
                    if (res.ok && data.success) {
                        alert('Activity deleted successfully');
                        window.location.reload();
                    } else {
                        alert('Failed to delete: ' + (data.message || 'Unknown error'));
                    }
                }).catch(err => {
                    alert('Error: ' + err.message);
                });
            }
        }
    });

    @endif
    // ========== END ADMIN MANAGEMENT FUNCTIONS ==========
    
    // ========== CALENDAR VIEW ==========
    let assessmentCalendar = null;
    $('#tab-calendar').on('shown.bs.tab', function() {
        if (!assessmentCalendar) {
            const calendarEl = document.getElementById('assessment-calendar');
            if (calendarEl) {
                assessmentCalendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: function(fetchInfo, successCallback, failureCallback) {
                        fetch('{{ route("assessments.calendar.events") }}?start=' + fetchInfo.startStr + '&end=' + fetchInfo.endStr)
                            .then(response => response.json())
                            .then(data => {
                                successCallback(data);
                            })
                            .catch(error => {
                                console.error('Error loading calendar events:', error);
                                failureCallback(error);
                            });
                    },
                    eventClick: function(info) {
                        if (info.event.extendedProps.type === 'assessment') {
                            window.location.href = '/assessments/' + info.event.extendedProps.assessment_id;
                        } else if (info.event.extendedProps.type === 'progress_report') {
                            // Could show report details in a modal or redirect
                            alert('Progress Report: ' + info.event.extendedProps.activity + '\nEmployee: ' + info.event.extendedProps.employee + '\nStatus: ' + info.event.extendedProps.status);
                        }
                    },
                    eventDisplay: 'block',
                    height: 'auto'
                });
                assessmentCalendar.render();
            }
        } else {
            assessmentCalendar.refetchEvents();
        }
    });
    
    // ========== ANALYTICS VIEW ==========
    $('#btn-load-analytics-data').on('click', function() {
        const year = $('#analytics-year-input').val();
        const content = $('#analytics-content');
        
        content.html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Loading analytics...</p></div>');
        
            fetch('{{ route("assessments.analytics.data") }}?year=' + year)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderAnalyticsCharts(data);
                } else {
                    content.html('<div class="alert alert-danger">Failed to load analytics data</div>');
                }
            })
            .catch(error => {
                console.error('Error loading analytics:', error);
                content.html('<div class="alert alert-danger">Error loading analytics: ' + error.message + '</div>');
            });
    });
    
    function renderAnalyticsCharts(data) {
        let html = '<div class="row mb-4">';
        
        // Status Distribution Chart
        html += '<div class="col-md-6 mb-4"><div class="card"><div class="card-header"><h6>Assessment Status Distribution</h6></div><div class="card-body"><canvas id="statusChart"></canvas></div></div></div>';
        
        // Report Status Distribution Chart
        html += '<div class="col-md-6 mb-4"><div class="card"><div class="card-header"><h6>Progress Report Status Distribution</h6></div><div class="card-body"><canvas id="reportStatusChart"></canvas></div></div></div>';
        
        // Monthly Trend Chart
        html += '<div class="col-md-12 mb-4"><div class="card"><div class="card-header"><h6>Monthly Trend (' + data.year + ')</h6></div><div class="card-body"><canvas id="trendChart"></canvas></div></div></div>';
        
        // Department Distribution Chart
        html += '<div class="col-md-6 mb-4"><div class="card"><div class="card-header"><h6>Department Distribution</h6></div><div class="card-body"><canvas id="departmentChart"></canvas></div></div></div>';
        
        // Top Performers Chart
        html += '<div class="col-md-6 mb-4"><div class="card"><div class="card-header"><h6>Top Performers (Reports Submitted)</h6></div><div class="card-body"><canvas id="performersChart"></canvas></div></div></div>';
        
        html += '</div>';
        
        $('#analytics-content').html(html);
        
        // Render charts
        setTimeout(() => {
            // Status Distribution
            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Approved', 'Pending HOD', 'Rejected'],
                    datasets: [{
                        data: [data.status_distribution.approved, data.status_distribution.pending_hod, data.status_distribution.rejected],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
            
            // Report Status Distribution
            new Chart(document.getElementById('reportStatusChart'), {
                type: 'pie',
                data: {
                    labels: ['Approved', 'Pending Approval', 'Rejected'],
                    datasets: [{
                        data: [data.report_status_distribution.approved, data.report_status_distribution.pending_approval, data.report_status_distribution.rejected],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true
                }
            });
            
            // Monthly Trend
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const assessmentData = [];
            const reportData = [];
            for (let i = 1; i <= 12; i++) {
                assessmentData.push(data.monthly_trend[i].assessments);
                reportData.push(data.monthly_trend[i].reports);
            }
            
            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Assessments',
                        data: assessmentData,
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        tension: 0.4
                    }, {
                        label: 'Progress Reports',
                        data: reportData,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Department Distribution
            const deptLabels = Object.keys(data.department_distribution);
            const deptData = Object.values(data.department_distribution);
            
            new Chart(document.getElementById('departmentChart'), {
                type: 'bar',
                data: {
                    labels: deptLabels,
                    datasets: [{
                        label: 'Assessments',
                        data: deptData,
                        backgroundColor: '#007bff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Top Performers
            const performerLabels = Object.keys(data.top_performers);
            const performerData = Object.values(data.top_performers);
            
            new Chart(document.getElementById('performersChart'), {
                type: 'bar',
                data: {
                    labels: performerLabels,
                    datasets: [{
                        label: 'Reports Submitted',
                        data: performerData,
                        backgroundColor: '#28a745'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }, 100);
    }
    
    // Auto-load analytics when tab is shown
    $('#tab-analytics').on('shown.bs.tab', function() {
        if ($('#analytics-content').text().includes('Click "Load"')) {
            $('#btn-load-analytics-data').click();
        }
    });
});
</script>
@endpush

