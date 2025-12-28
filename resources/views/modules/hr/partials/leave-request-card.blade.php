@php
    $statusColors = [
        'pending_hr_review' => 'warning',
        'pending_hod_approval' => 'info', 
        'pending_ceo_approval' => 'primary',
        'approved_pending_docs' => 'success',
        'on_leave' => 'success',
        'completed' => 'dark',
        'rejected' => 'danger',
        'rejected_for_edit' => 'danger',
        'cancelled' => 'secondary'
    ];
    
    $statusText = [
        'pending_hr_review' => 'Pending HR',
        'pending_hod_approval' => 'Pending HOD',
        'pending_ceo_approval' => 'Pending CEO',
        'approved_pending_docs' => 'Pending HR Docs',
        'on_leave' => 'On Leave',
        'completed' => 'Completed',
        'rejected' => 'Rejected',
        'rejected_for_edit' => 'Rejected - Edit',
        'cancelled' => 'Cancelled'
    ];
    
    $statusIcons = [
        'pending_hr_review' => 'bx-time',
        'pending_hod_approval' => 'bx-user-check',
        'pending_ceo_approval' => 'bx-crown',
        'approved_pending_docs' => 'bx-file-doc',
        'on_leave' => 'bx-calendar-check',
        'completed' => 'bx-check-circle',
        'rejected' => 'bx-x-circle',
        'rejected_for_edit' => 'bx-edit',
        'cancelled' => 'bx-x'
    ];
@endphp

<div class="card mb-3 border-left-{{ $statusColors[$request->status] }} shadow-sm">
    <div class="card-body py-3">
        <div class="row align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center mb-2">
                    <i class="bx {{ $statusIcons[$request->status] }} me-2 text-{{ $statusColors[$request->status] }}"></i>
                    <h6 class="mb-0 fw-bold">{{ $request->leaveType->name }}</h6>
                    <span class="badge bg-{{ $statusColors[$request->status] }} ms-2">{{ $statusText[$request->status] }}</span>
                </div>
                
                <div class="row text-sm">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bx bx-user me-1 text-muted"></i>
                            <span class="text-muted">{{ $request->employee->name }}</span>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <i class="bx bx-buildings me-1 text-muted"></i>
                            <span class="text-muted">{{ $request->employee->primaryDepartment->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bx bx-calendar me-1 text-muted"></i>
                            <span class="text-muted">
                                {{ \Carbon\Carbon::parse($request->start_date)->format('M d') }} - 
                                {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center mb-1">
                            <i class="bx bx-time me-1 text-muted"></i>
                            <span class="text-muted">{{ $request->total_days }} days</span>
                        </div>
                    </div>
                </div>
                
                @if($request->reason)
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="bx bx-message-square-detail me-1"></i>
                        {{ Str::limit($request->reason, 100) }}
                    </small>
                </div>
                @endif
                
                @if($request->leave_location)
                <div class="mt-1">
                    <small class="text-muted">
                        <i class="bx bx-map me-1"></i>
                        {{ $request->leave_location }}
                    </small>
                </div>
                @endif
            </div>
            
            <div class="col-md-4 text-end">
                <div class="mb-2">
                    <small class="text-muted">Applied {{ \Carbon\Carbon::parse($request->created_at)->diffForHumans() }}</small>
                </div>
                
                <div class="btn-group-vertical d-grid gap-1" role="group">
                    <button class="btn btn-sm btn-outline-primary btn-view-details" data-id="{{ $request->id }}" 
                            data-bs-toggle="tooltip" title="View Details">
                        <i class="bx bx-show"></i> View
                        </button>
                    
                    
                    @if(in_array($request->status, ['approved_pending_docs', 'on_leave', 'completed']))
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-info dropdown-toggle" type="button" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-download"></i> PDF
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            @if($request->approval_letter_number)
                            <li>
                                <a class="dropdown-item" href="{{ route('leave.pdf.approval-letter', $request->id) }}" target="_blank">
                                    <i class="bx bx-file-blank"></i> Official Leave Letter
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            @endif
                            <li>
                                <a class="dropdown-item" href="{{ route('leave.pdf.certificate', $request->id) }}" target="_blank">
                                    <i class="bx bx-file"></i> Leave Certificate
                                </a>
                            </li>
                            @if($request->fare_approved_amount > 0)
                            <li>
                                <a class="dropdown-item" href="{{ route('leave.pdf.fare-certificate', $request->id) }}" target="_blank">
                                    <i class="bx bx-receipt"></i> Fare Certificate
                                </a>
                            </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('leave.pdf.summary', $request->id) }}" target="_blank">
                                    <i class="bx bx-file-blank"></i> Complete Summary
                                </a>
                            </li>
                        </ul>
                    </div>
                    @elseif($request->status !== 'cancelled')
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('leave.pdf.summary', $request->id) }}" 
                       target="_blank" data-bs-toggle="tooltip" title="Download Summary PDF">
                        <i class="bx bx-download"></i> Summary PDF
                    </a>
                    @endif
                    
                    @if($isOwn ?? false)
                        @if(in_array($request->status, ['pending_hr_review', 'rejected_for_edit']))
                        <button class="btn btn-sm btn-outline-warning btn-edit" data-id="{{ $request->id }}" 
                                data-bs-toggle="tooltip" title="Edit Request">
                            <i class="bx bx-edit"></i> Edit
                        </button>
                        @endif
                        
                        @if(in_array($request->status, ['pending_hr_review', 'pending_hod_approval', 'pending_ceo_approval', 'approved_pending_docs']))
                        <button class="btn btn-sm btn-outline-danger btn-cancel" data-id="{{ $request->id }}" 
                                data-bs-toggle="tooltip" title="Cancel Request">
                            <i class="bx bx-x"></i> Cancel
                        </button>
                        @endif
                        
                        @if($request->status === 'on_leave')
                        <button class="btn btn-sm btn-outline-success btn-return" data-id="{{ $request->id }}" 
                                data-bs-toggle="tooltip" title="Return from Leave">
                            <i class="bx bx-log-in"></i> Return
                        </button>
                        @endif
                    @else
                        @if(($isHR ?? false) || ($isAdmin ?? false))
                            @if($request->status === 'pending_hr_review')
                            <button class="btn btn-sm btn-outline-warning btn-review" data-id="{{ $request->id }}" 
                                    data-bs-toggle="tooltip" title="Review Request">
                                <i class="bx bx-check-circle"></i> Review
                            </button>
                            @endif
                            
                            @if($request->status === 'approved_pending_docs')
                            <button class="btn btn-sm btn-outline-success btn-process-docs" data-id="{{ $request->id }}" 
                                    data-bs-toggle="tooltip" title="Process Documents">
                                <i class="bx bx-file-doc"></i> Process Docs
                            </button>
                            @endif
                        @endif
                        
                        @if(($isHOD ?? false) || ($isAdmin ?? false))
                            @php
                                // Get user department ID - check passed variable first, then auth user
                                $userDepartmentId = $user_department_id ?? (auth()->check() ? auth()->user()->primary_department_id : null);
                                // HOD can review if: no department assigned (admin) OR employee is in their department
                                $canReview = !$userDepartmentId || ($request->employee && isset($request->employee->primary_department_id) && $request->employee->primary_department_id == $userDepartmentId);
                            @endphp
                            @if($request->status === 'pending_hod_approval' && $canReview)
                            <button class="btn btn-sm btn-outline-info btn-hod-review" data-id="{{ $request->id }}" 
                                    data-bs-toggle="tooltip" title="HOD Review">
                                <i class="bx bx-user-check"></i> HOD Review
                            </button>
                            @endif
                        @endif
                        
                        @if(($isCEO ?? false) || ($isAdmin ?? false))
                            @if($request->status === 'pending_ceo_approval')
                            <button class="btn btn-sm btn-outline-primary btn-ceo-review" data-id="{{ $request->id }}" 
                                    data-bs-toggle="tooltip" title="CEO Review">
                                <i class="bx bx-crown"></i> CEO Review
                            </button>
                            @endif
                        @endif
                    @endif
                </div>
            </div>
        </div>
        
        @if($request->hr_officer_comments || $request->comments)
        <div class="mt-3 pt-3 border-top">
            <h6 class="text-sm text-muted mb-2">Review Comments:</h6>
            @if($request->hr_officer_comments)
            <div class="alert alert-info py-2 mb-2">
                <small><strong>HR Officer:</strong> {{ $request->hr_officer_comments }}</small>
            </div>
            @endif
            @if($request->comments)
            <div class="alert alert-secondary py-2">
                <small><strong>{{ $request->reviewer ? $request->reviewer->name : 'Reviewer' }}:</strong> {{ $request->comments }}</small>
            </div>
            @endif
        </div>
        @endif
        
        @if($request->dependents && $request->dependents->count() > 0)
        <div class="mt-3 pt-3 border-top">
            <h6 class="text-sm text-muted mb-2">Dependents:</h6>
            <div class="row">
                @foreach($request->dependents as $dependent)
                <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-user me-2 text-muted"></i>
                        <div>
                            <div class="fw-semibold">{{ $dependent->name }}</div>
                            <small class="text-muted">{{ $dependent->relationship }}</small>
                            @if($dependent->fare_amount > 0)
                            <div class="text-success">
                                <small><i class="bx bx-dollar"></i> {{ number_format($dependent->fare_amount) }} TZS</small>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        
        @if($request->total_fare_approved > 0)
        <div class="mt-3 pt-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <span class="text-muted">Total Fare Approved:</span>
                <span class="fw-bold text-success">{{ number_format($request->total_fare_approved) }} TZS</span>
            </div>
            @if($request->approval_letter_number)
            <div class="d-flex justify-content-between align-items-center mt-1">
                <span class="text-muted">Approval Letter:</span>
                <span class="fw-semibold">{{ $request->approval_letter_number }}</span>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
