@php
    $statusBadge = $request->status_badge;
@endphp

<div class="col-md-6 mb-3">
    <div class="card border-left-{{ $statusBadge['class'] }} shadow-sm h-100 permission-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <h6 class="mb-0">{{ $request->user->name }}</h6>
                    <small class="text-muted">Request ID: {{ $request->request_id }}</small>
                </div>
                <span class="badge bg-{{ $statusBadge['class'] }}">{{ $statusBadge['text'] }}</span>
            </div>
            
            <div class="mb-2">
                <p class="mb-1"><strong>Duration:</strong> 
                    {{ \Carbon\Carbon::parse($request->start_datetime)->format('M j, Y H:i') }} to 
                    {{ \Carbon\Carbon::parse($request->end_datetime)->format('M j, Y H:i') }}
                </p>
                <p class="mb-1"><strong>Reason:</strong> {{ ucfirst($request->reason_type) }} - {{ Str::limit($request->reason_description, 50) }}</p>
                @if($request->user->primaryDepartment)
                <p class="mb-1"><small class="text-muted"><i class="bx bx-buildings"></i> {{ $request->user->primaryDepartment->name }}</small></p>
                @endif
            </div>
            
            @if($request->hod_comments)
            <div class="alert alert-info py-2 mb-2">
                <small><strong>HOD Comments:</strong> {{ $request->hod_comments }}</small>
            </div>
            @endif
            
            @if($request->ceo_comments)
            <div class="alert alert-secondary py-2 mb-2">
                <small><strong>CEO Comments:</strong> {{ $request->ceo_comments }}</small>
            </div>
            @endif
            
            <div class="d-flex gap-2 flex-wrap mt-3">
                <a href="{{ route('permissions.show', $request->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-show"></i> View Details
                </a>
                
                <a href="{{ route('permissions.pdf', $request->id) }}" class="btn btn-sm btn-outline-danger" target="_blank" title="Download PDF">
                    <i class="bx bx-download"></i> PDF
                </a>
                
                @if(!$isOwn)
                    {{-- System Admin can perform all actions at any stage (except completed) --}}
                    @if($isAdmin && $request->status !== 'completed')
                        {{-- Show primary action button based on status, plus admin override options --}}
                        @if(in_array($request->status, ['pending_hr']))
                        <button class="btn btn-sm btn-outline-info btn-hr-initial-review" data-id="{{ $request->id }}" title="Admin: HR Initial Review">
                            <i class="bx bx-user-check"></i> HR Review
                        </button>
                        @elseif(in_array($request->status, ['pending_hod']))
                        <button class="btn btn-sm btn-outline-info btn-hod-review" data-id="{{ $request->id }}" title="Admin: HOD Review">
                            <i class="bx bx-check-circle"></i> HOD Review
                        </button>
                        @elseif(in_array($request->status, ['pending_hr_final']))
                        <button class="btn btn-sm btn-outline-success btn-hr-final-approve" data-id="{{ $request->id }}" title="Admin: HR Final Approval">
                            <i class="bx bx-check-double"></i> HR Final
                        </button>
                        @elseif(in_array($request->status, ['return_pending']) && $request->return_datetime)
                        <button class="btn btn-sm btn-outline-warning btn-hr-return-verify" data-id="{{ $request->id }}" title="Admin: Verify Return">
                            <i class="bx bx-check"></i> Verify Return
                        </button>
                        @elseif(in_array($request->status, ['approved']) && $request->return_datetime)
                        <button class="btn btn-sm btn-outline-warning btn-hr-return-verify" data-id="{{ $request->id }}" title="Admin: Verify Return">
                            <i class="bx bx-check"></i> Verify Return
                        </button>
                        @endif
                        
                        {{-- Admin override buttons - show all available actions --}}
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" title="Admin: All Actions">
                                <i class="bx bx-cog"></i> Admin
                            </button>
                            <ul class="dropdown-menu">
                                @if(!in_array($request->status, ['pending_hr', 'rejected', 'return_rejected']))
                                <li><a class="dropdown-item btn-hr-initial-review" href="#" data-id="{{ $request->id }}">
                                    <i class="bx bx-user-check"></i> HR Initial Review
                                </a></li>
                                @endif
                                @if(!in_array($request->status, ['pending_hod', 'rejected', 'return_rejected']))
                                <li><a class="dropdown-item btn-hod-review" href="#" data-id="{{ $request->id }}">
                                    <i class="bx bx-check-circle"></i> HOD Review
                                </a></li>
                                @endif
                                @if(!in_array($request->status, ['pending_hr_final', 'approved', 'rejected', 'return_rejected']))
                                <li><a class="dropdown-item btn-hr-final-approve" href="#" data-id="{{ $request->id }}">
                                    <i class="bx bx-check-double"></i> HR Final Approval
                                </a></li>
                                @endif
                                @if($request->return_datetime && !in_array($request->status, ['completed', 'return_rejected']))
                                <li><a class="dropdown-item btn-hr-return-verify" href="#" data-id="{{ $request->id }}">
                                    <i class="bx bx-check"></i> Verify Return
                                </a></li>
                                @endif
                            </ul>
                        </div>
                    @else
                        {{-- Regular role-based buttons --}}
                        {{-- HR Initial Review --}}
                        @if($isHR && in_array($request->status, ['pending_hr']))
                        <button class="btn btn-sm btn-outline-info btn-hr-initial-review" data-id="{{ $request->id }}">
                            <i class="bx bx-user-check"></i> HR Review
                        </button>
                        @endif
                        
                        {{-- HOD Review --}}
                        @if($isHOD && in_array($request->status, ['pending_hod']))
                        <button class="btn btn-sm btn-outline-info btn-hod-review" data-id="{{ $request->id }}">
                            <i class="bx bx-check-circle"></i> HOD Review
                        </button>
                        @endif
                        
                        {{-- HR Final Approval --}}
                        @if($isHR && in_array($request->status, ['pending_hr_final']))
                        <button class="btn btn-sm btn-outline-success btn-hr-final-approve" data-id="{{ $request->id }}">
                            <i class="bx bx-check-double"></i> HR Final
                        </button>
                        @endif
                        
                        {{-- Return Verification --}}
                        @if($isHR && in_array($request->status, ['return_pending']))
                        <button class="btn btn-sm btn-outline-warning btn-hr-return-verify" data-id="{{ $request->id }}">
                            <i class="bx bx-check"></i> Verify Return
                        </button>
                        @endif
                    @endif
                @else
                    {{-- Staff can confirm return --}}
                    @if($request->status === 'approved' && !$request->return_datetime)
                    <button class="btn btn-sm btn-outline-primary btn-confirm-return" data-id="{{ $request->id }}">
                        <i class="bx bx-undo"></i> Confirm Return
                    </button>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.permission-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.permission-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.25rem 1rem rgba(148, 0, 0, 0.15);
}
</style>
