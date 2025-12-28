@extends('layouts.app')

@section('title', 'Permission Request Details - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1">
                                <i class="bx bx-clipboard me-2"></i>Permission Request Details
                            </h4>
                            <p class="mb-0 text-muted">Request ID: <strong>{{ $permissionRequest->request_id }}</strong></p>
                        </div>
                        <div>
                            <a href="{{ route('modules.hr.permissions') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Back to List
                            </a>
                            <a href="{{ route('permissions.pdf', $permissionRequest->id) }}" class="btn btn-outline-danger" target="_blank">
                                <i class="bx bx-download me-1"></i>Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Main Details -->
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-{{ $permissionRequest->status_badge['class'] }} text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-info-circle me-2"></i>Request Status
                        </h5>
                        <span class="badge bg-light text-dark fs-6">{{ $permissionRequest->status_badge['text'] }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bx bx-user me-2"></i>Staff Name:</strong><br>
                                <span class="ms-4">{{ $permissionRequest->user->name }}</span>
                            </p>
                            <p class="mb-2"><strong><i class="bx bx-building me-2"></i>Department:</strong><br>
                                <span class="ms-4">{{ $permissionRequest->user->primaryDepartment->name ?? 'N/A' }}</span>
                            </p>
                            <p class="mb-2"><strong><i class="bx bx-envelope me-2"></i>Email:</strong><br>
                                <span class="ms-4">{{ $permissionRequest->user->email ?? 'N/A' }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong><i class="bx bx-calendar me-2"></i>Submitted:</strong><br>
                                <span class="ms-4">{{ $permissionRequest->created_at->format('M j, Y g:i A') }}</span>
                            </p>
                            <p class="mb-2"><strong><i class="bx bx-time me-2"></i>Time Mode:</strong><br>
                                <span class="ms-4 badge bg-primary">{{ strtoupper($permissionRequest->time_mode) }}</span>
                            </p>
                            <p class="mb-2"><strong><i class="bx bx-timer me-2"></i>Duration:</strong><br>
                                <span class="ms-4">{{ $duration }} {{ $permissionRequest->time_mode === 'days' ? 'day(s)' : 'hour(s)' }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Permission Period -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-calendar-check me-2"></i>Permission Period</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3 bg-light">
                                <small class="text-muted d-block mb-1">Start Date & Time</small>
                                <h6 class="mb-0">{{ Carbon\Carbon::parse($permissionRequest->start_datetime)->format('l, M j, Y') }}</h6>
                                <p class="mb-0 text-primary">{{ Carbon\Carbon::parse($permissionRequest->start_datetime)->format('g:i A') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 mb-3 bg-light">
                                <small class="text-muted d-block mb-1">End Date & Time</small>
                                <h6 class="mb-0">{{ Carbon\Carbon::parse($permissionRequest->end_datetime)->format('l, M j, Y') }}</h6>
                                <p class="mb-0 text-primary">{{ Carbon\Carbon::parse($permissionRequest->end_datetime)->format('g:i A') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong>Total Duration:</strong> {{ $duration }} {{ $permissionRequest->time_mode === 'days' ? 'day(s)' : 'hour(s)' }}
                    </div>
                </div>
            </div>

            <!-- Reason Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bx bx-file me-2"></i>Reason Details
                        <span class="badge bg-{{ $permissionRequest->reason_type === 'official' ? 'primary' : ($permissionRequest->reason_type === 'medical' ? 'danger' : ($permissionRequest->reason_type === 'emergency' ? 'warning' : 'secondary')) }} ms-2">
                            {{ ucfirst($permissionRequest->reason_type) }}
                        </span>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $permissionRequest->reason_description }}</p>
                </div>
            </div>

            <!-- Review Comments -->
            @if($permissionRequest->hr_initial_comments || $permissionRequest->hod_comments || $permissionRequest->hr_final_comments)
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-message-dots me-2"></i>Review Comments</h5>
                </div>
                <div class="card-body">
                    @if($permissionRequest->hr_initial_comments)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong><i class="bx bx-user-check me-2 text-info"></i>HR Initial Review</strong>
                                @if($permissionRequest->hrInitialReviewer)
                                <small class="text-muted d-block">by {{ $permissionRequest->hrInitialReviewer->name }}</small>
                                @endif
                            </div>
                            <small class="text-muted">{{ $permissionRequest->hr_initial_reviewed->format('M j, Y g:i A') }}</small>
                        </div>
                        <p class="mb-0">{{ $permissionRequest->hr_initial_comments }}</p>
                    </div>
                    @endif

                    @if($permissionRequest->hod_comments)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong><i class="bx bx-check-circle me-2 text-info"></i>HOD Review</strong>
                                @if($permissionRequest->hodReviewer)
                                <small class="text-muted d-block">by {{ $permissionRequest->hodReviewer->name }}</small>
                                @endif
                            </div>
                            <small class="text-muted">{{ $permissionRequest->hod_reviewed->format('M j, Y g:i A') }}</small>
                        </div>
                        <p class="mb-0">{{ $permissionRequest->hod_comments }}</p>
                    </div>
                    @endif

                    @if($permissionRequest->hr_final_comments)
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong><i class="bx bx-check-double me-2 text-success"></i>HR Final Approval</strong>
                                @if($permissionRequest->hrFinalReviewer)
                                <small class="text-muted d-block">by {{ $permissionRequest->hrFinalReviewer->name }}</small>
                                @endif
                            </div>
                            @if($permissionRequest->hr_final_reviewed)
                            <small class="text-muted">{{ $permissionRequest->hr_final_reviewed->format('M j, Y g:i A') }}</small>
                            @endif
                        </div>
                        <p class="mb-0">{{ $permissionRequest->hr_final_comments }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Return Information -->
            @if($permissionRequest->return_datetime)
            <div class="card shadow-sm mb-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bx bx-undo me-2"></i>Return Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Return Date & Time:</strong><br>
                                <span class="ms-4">{{ Carbon\Carbon::parse($permissionRequest->return_datetime)->format('M j, Y g:i A') }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Submitted At:</strong><br>
                                <span class="ms-4">{{ $permissionRequest->return_submitted_at ? Carbon\Carbon::parse($permissionRequest->return_submitted_at)->format('M j, Y g:i A') : 'N/A' }}</span>
                            </p>
                        </div>
                    </div>
                    @if($permissionRequest->return_remarks)
                    <div class="alert alert-light mb-0">
                        <strong>Remarks:</strong> {{ $permissionRequest->return_remarks }}
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column - Timeline & Actions -->
        <div class="col-lg-4">
            <!-- Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-time-five me-2"></i>Request Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($timeline as $index => $event)
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <div class="timeline-marker bg-{{ $event['color'] }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px;">
                                    <i class="bx {{ $event['icon'] }}"></i>
                                </div>
                                <div class="timeline-content ms-3 flex-grow-1">
                                    <h6 class="mb-1">{{ $event['title'] }}</h6>
                                    <p class="mb-1 text-muted small">{{ $event['description'] }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bx bx-time me-1"></i>{{ Carbon\Carbon::parse($event['date'])->format('M j, Y g:i A') }}
                                        </small>
                                        @if(isset($event['decision']))
                                        <span class="badge bg-{{ $event['color'] }}">{{ $event['decision'] }}</span>
                                        @endif
                                    </div>
                                    @if(isset($event['user']))
                                    <small class="text-muted d-block mt-1">
                                        <i class="bx bx-user me-1"></i>{{ $event['user'] }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                            @if(!$loop->last)
                            <div class="timeline-line bg-{{ $event['color'] }}" style="width: 2px; height: 30px; margin-left: 19px;"></div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Processing Times -->
            @if(!empty($processingTimes))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-timer me-2"></i>Processing Times</h5>
                </div>
                <div class="card-body">
                    @if(isset($processingTimes['hr_initial']))
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>HR Initial Review</span>
                            <strong>{{ $processingTimes['hr_initial'] }} hours</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ min(($processingTimes['hr_initial'] / 24) * 100, 100) }}%"></div>
                        </div>
                    </div>
                    @endif

                    @if(isset($processingTimes['hod']))
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>HOD Review</span>
                            <strong>{{ $processingTimes['hod'] }} hours</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ min(($processingTimes['hod'] / 24) * 100, 100) }}%"></div>
                        </div>
                    </div>
                    @endif

                    @if(isset($processingTimes['hr_final']))
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>HR Final Approval</span>
                            <strong>{{ $processingTimes['hr_final'] }} hours</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" style="width: {{ min(($processingTimes['hr_final'] / 24) * 100, 100) }}%"></div>
                        </div>
                    </div>
                    @endif

                    @if(isset($processingTimes['total']))
                    <div class="mt-3 pt-3 border-top">
                        <div class="d-flex justify-content-between">
                            <strong>Total Processing Time</strong>
                            <strong class="text-primary">{{ $processingTimes['total'] }} hours</strong>
                        </div>
                        <small class="text-muted">({{ round($processingTimes['total'] / 24, 1) }} days)</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-cog me-2"></i>Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if(!$isOwn && ($isHR || $isHOD || $isAdmin))
                        @if($isAdmin && $permissionRequest->status !== 'completed')
                        <div class="d-grid gap-2">
                            <small class="text-muted mb-2">Admin: All Available Actions</small>
                            @if(!in_array($permissionRequest->status, ['pending_hr', 'rejected', 'return_rejected']))
                            <button class="btn btn-outline-info btn-sm btn-hr-initial-review" data-id="{{ $permissionRequest->id }}">
                                <i class="bx bx-user-check me-1"></i>HR Initial Review
                            </button>
                            @endif
                            @if(!in_array($permissionRequest->status, ['pending_hod', 'rejected', 'return_rejected']))
                            <button class="btn btn-outline-info btn-sm btn-hod-review" data-id="{{ $permissionRequest->id }}">
                                <i class="bx bx-check-circle me-1"></i>HOD Review
                            </button>
                            @endif
                            @if(!in_array($permissionRequest->status, ['pending_hr_final', 'approved', 'rejected', 'return_rejected']))
                            <button class="btn btn-outline-success btn-sm btn-hr-final-approve" data-id="{{ $permissionRequest->id }}">
                                <i class="bx bx-check-double me-1"></i>HR Final Approval
                            </button>
                            @endif
                            @if($permissionRequest->return_datetime && !in_array($permissionRequest->status, ['completed', 'return_rejected']))
                            <button class="btn btn-outline-warning btn-sm btn-hr-return-verify" data-id="{{ $permissionRequest->id }}">
                                <i class="bx bx-check me-1"></i>Verify Return
                            </button>
                            @endif
                        </div>
                        @else
                        @if(($isHR || $isAdmin) && $permissionRequest->status === 'pending_hr')
                        <button class="btn btn-info btn-sm w-100 mb-2 btn-hr-initial-review" data-id="{{ $permissionRequest->id }}">
                            <i class="bx bx-user-check me-1"></i>HR Initial Review
                        </button>
                        @endif
                        @if(($isHOD || $isAdmin) && $permissionRequest->status === 'pending_hod')
                        <button class="btn btn-info btn-sm w-100 mb-2 btn-hod-review" data-id="{{ $permissionRequest->id }}">
                            <i class="bx bx-check-circle me-1"></i>HOD Review
                        </button>
                        @endif
                        @if(($isHR || $isAdmin) && $permissionRequest->status === 'pending_hr_final')
                        <button class="btn btn-success btn-sm w-100 mb-2 btn-hr-final-approve" data-id="{{ $permissionRequest->id }}">
                            <i class="bx bx-check-double me-1"></i>HR Final Approval
                        </button>
                        @endif
                        @if(($isHR || $isAdmin) && $permissionRequest->status === 'return_pending')
                        <button class="btn btn-warning btn-sm w-100 mb-2 btn-hr-return-verify" data-id="{{ $permissionRequest->id }}">
                            <i class="bx bx-check me-1"></i>Verify Return
                        </button>
                        @endif
                        @endif
                    @elseif($isOwn)
                        {{-- Staff can confirm return --}}
                        @if($permissionRequest->status === 'approved' && !$permissionRequest->return_datetime)
                        <button class="btn btn-primary btn-sm w-100 btn-confirm-return" data-id="{{ $permissionRequest->id }}">
                            <i class="bx bx-undo me-1"></i>Confirm Return
                        </button>
                        @elseif($permissionRequest->return_datetime && $permissionRequest->status === 'return_pending')
                        <div class="alert alert-info mb-0">
                            <i class="bx bx-info-circle me-2"></i>
                            <small>Return confirmation submitted. Waiting for HR verification.</small>
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Employee Info Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bx bx-user me-2"></i>Employee Information</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Name:</strong><br>{{ $permissionRequest->user->name }}</p>
                    <p class="mb-2"><strong>Department:</strong><br>{{ $permissionRequest->user->primaryDepartment->name ?? 'N/A' }}</p>
                    <p class="mb-2"><strong>Email:</strong><br>{{ $permissionRequest->user->email ?? 'N/A' }}</p>
                    @if($permissionRequest->user->phone)
                    <p class="mb-0"><strong>Phone:</strong><br>{{ $permissionRequest->user->phone }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- HR Initial Review Modal -->
<div class="modal fade" id="hrInitialReviewModal" tabindex="-1">
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
                        <select name="decision" id="hodDecision" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="approve">Approve</option>
                            <option value="reject">Reject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments (Required for Rejection) *</label>
                        <textarea name="comments" id="hodComments" class="form-control" rows="4" required placeholder="Provide comments for your decision..."></textarea>
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
<div class="modal fade" id="hrFinalApprovalModal" tabindex="-1">
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
<div class="modal fade" id="hrReturnVerifyModal" tabindex="-1">
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
@endsection

@push('styles')
<style>
.timeline-item {
    position: relative;
}
.timeline-marker {
    font-size: 18px;
}
.timeline-line {
    position: relative;
}
.timeline-content h6 {
    font-size: 0.95rem;
    font-weight: 600;
}

/* Ensure all modals are in front of everything */
.modal {
    z-index: 9999 !important;
}
.modal-backdrop {
    z-index: 9998 !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
}
.modal.show {
    display: block !important;
    z-index: 10000 !important;
}
.modal-dialog {
    position: relative;
    z-index: 10001 !important;
}
/* Specific modal z-index for stacking - ensure they're always on top */
#hrInitialReviewModal.show {
    z-index: 10010 !important;
}
#hrFinalApprovalModal.show {
    z-index: 10020 !important;
}
#hrReturnVerifyModal.show {
    z-index: 10030 !important;
}
#hodReviewModal.show {
    z-index: 10040 !important;
}
#confirmReturnModal.show {
    z-index: 10050 !important;
}
/* Ensure backdrop is always behind modals */
body.modal-open .modal-backdrop {
    z-index: 9998 !important;
}
body.modal-open .modal-backdrop.show {
    z-index: 9999 !important;
}
/* Ensure modals are always visible and on top */
body.modal-open {
    overflow: hidden !important;
    padding-right: 0 !important;
}
/* Prevent body scroll when modal is open */
.modal-open {
    overflow: hidden !important;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    // Ensure modals are properly initialized
    const modals = ['hrInitialReviewModal', 'hodReviewModal', 'hrFinalApprovalModal', 'hrReturnVerifyModal', 'confirmReturnModal'];
    modals.forEach(function(modalId) {
        const modalElement = document.getElementById(modalId);
        if (modalElement) {
            // Clean up any existing modal instances
            const existingModal = bootstrap.Modal.getInstance(modalElement);
            if (existingModal) {
                existingModal.dispose();
            }
        }
    });
    
    // Helper function to show alerts
    function showAlert(title, message, type = 'info') {
        Swal.fire({
            icon: type,
            title: title,
            text: message,
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        });
    }
    
    // Helper function to safely show modal
    function showModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            console.error('Modal element not found:', modalId);
            return null;
        }
        try {
            // Dispose existing instance if any
            const existingModal = bootstrap.Modal.getInstance(modalElement);
            if (existingModal) {
                existingModal.dispose();
            }
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            modal.show();
            return modal;
        } catch (error) {
            console.error('Error showing modal:', error);
            showAlert('Error', 'Failed to open modal. Please try again.', 'error');
            return null;
        }
    }
    
    // Helper function to safely hide modal
    function hideModal(modalId) {
        const modalElement = document.getElementById(modalId);
        if (!modalElement) {
            return;
        }
        try {
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
        } catch (error) {
            console.error('Error hiding modal:', error);
        }
    }
    
    // HR Initial Review
    $(document).on('click', '.btn-hr-initial-review', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        $('#hr-initial-review-request-id').val(requestId);
        // Reset form
        $('#hrInitialReviewForm')[0].reset();
        $('#hr-initial-review-request-id').val(requestId);
        showModal('hrInitialReviewModal');
    });
    
    $('#hrInitialReviewForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#hr-initial-review-request-id').val();
        const decision = $('#hrInitialDecision').val();
        const comments = $('#hrInitialComments').val();
        
        if (!decision || !comments || !comments.trim()) {
            showAlert('Validation Error', 'Please select a decision and provide comments.', 'warning');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: `/permissions/${requestId}/hr-initial-review`,
            data: { _token: csrfToken, decision: decision, comments: comments },
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            success: function(response) {
                if (response && response.success) {
                    hideModal('hrInitialReviewModal');
                    showAlert('Success!', response.message || 'Review submitted successfully', 'success');
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response?.message || 'An error occurred', 'error');
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
    
    // HOD Review
    $(document).on('click', '.btn-hod-review', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        $('#hod-review-request-id').val(requestId);
        // Reset form
        $('#hodReviewForm')[0].reset();
        $('#hod-review-request-id').val(requestId);
        showModal('hodReviewModal');
    });
    
    $('#hodReviewForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#hod-review-request-id').val();
        const decision = $('#hodDecision').val();
        const comments = $('#hodComments').val();
        
        // Validate decision
        if (!decision || decision === '') {
            showAlert('Validation Error', 'Please select a decision.', 'warning');
            $('#hodDecision').focus();
            return;
        }
        
        // Validate comments
        if (!comments || !comments.trim()) {
            showAlert('Validation Error', 'Please provide comments for your decision.', 'warning');
            $('#hodComments').focus();
            return;
        }
        
        const formData = $(this).serialize();
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: `/permissions/${requestId}/hod-review`,
            data: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            success: function(response) {
                if (response.success) {
                    hideModal('hodReviewModal');
                    showAlert('Success!', response.message, 'success');
                    setTimeout(() => { location.reload(); }, 1500);
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
    
    // HR Final Approval
    $(document).on('click', '.btn-hr-final-approve', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        $('#hr-final-approval-request-id').val(requestId);
        // Reset form
        $('#hrFinalApprovalForm')[0].reset();
        $('#hr-final-approval-request-id').val(requestId);
        showModal('hrFinalApprovalModal');
    });
    
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
            data: { _token: csrfToken, decision: decision, comments: comments || '' },
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            success: function(response) {
                if (response && response.success) {
                    hideModal('hrFinalApprovalModal');
                    showAlert('Success!', response.message || 'Decision submitted successfully', 'success');
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response?.message || 'An error occurred', 'error');
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
    
    // HR Return Verification
    $(document).on('click', '.btn-hr-return-verify', function(e) {
        e.preventDefault();
        const requestId = $(this).data('id');
        if (!requestId) {
            showAlert('Error', 'Request ID not found', 'error');
            return;
        }
        $('#hr-return-verify-request-id').val(requestId);
        // Reset form
        $('#hrReturnVerifyForm')[0].reset();
        $('#hr-return-verify-request-id').val(requestId);
        showModal('hrReturnVerifyModal');
    });
    
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
            data: { _token: csrfToken, decision: decision, comments: comments || '' },
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            success: function(response) {
                if (response && response.success) {
                    hideModal('hrReturnVerifyModal');
                    showAlert('Success!', response.message || 'Return verification completed successfully', 'success');
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
                    submitBtn.prop('disabled', false).html(originalBtnText);
                    showAlert('Error', response?.message || 'An error occurred', 'error');
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
        // Reset form
        $('#confirmReturnForm')[0].reset();
        $('#confirm-return-request-id').val(requestId);
        
        // Set default return datetime to now
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        $('#confirmReturnModal input[name="return_datetime"]').val(localDateTime);
        
        showModal('confirmReturnModal');
    });
    
    $('#confirmReturnForm').on('submit', function(e) {
        e.preventDefault();
        const requestId = $('#confirm-return-request-id').val();
        const formData = $(this).serialize();
        
        const returnDateTime = $('input[name="return_datetime"]').val();
        if (!returnDateTime) {
            showAlert('Return Date Required', 'Please select your return date and time.', 'warning');
            return;
        }
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Submitting...');
        
        $.ajax({
            type: 'POST',
            url: `/permissions/${requestId}/confirm-return`,
            data: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    hideModal('confirmReturnModal');
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
                }
                showAlert('Error', message, 'error');
            }
        });
    });
});
</script>
@endpush

