@extends('layouts.app')

@section('title', 'Incident ' . ($incident->incident_no ?? $incident->incident_code ?? 'N/A'))

@php
$user = auth()->user();
$isHR = $user->hasRole('HR Officer') || $user->hasRole('System Admin');
$isHOD = $user->hasRole('HOD') || $user->hasRole('System Admin');
$isManager = $isHR || $isHOD;
$canEdit = $isManager || $incident->assigned_to === $user->id;
$canAssign = $isManager;
$canResolve = $incident->assigned_to === $user->id || $isManager;
$priority = strtolower($incident->priority ?? 'medium');
$priorityColors = [
  'low' => 'secondary',
  'medium' => 'info',
  'high' => 'warning',
  'critical' => 'danger'
];
$status = strtolower(str_replace(' ', '_', $incident->status ?? 'new'));
$statusColors = [
  'new' => 'primary',
  'assigned' => 'info',
  'in_progress' => 'warning',
  'resolved' => 'success',
  'closed' => 'secondary',
  'cancelled' => 'danger'
];
$daysOpen = $incident->getDaysOpen() ?? 0;
@endphp

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Breadcrumb -->
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('modules.incidents.dashboard') }}">
                            <i class="bx bx-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('modules.incidents') }}">All Incidents</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $incident->incident_no ?? $incident->incident_code ?? 'N/A' }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px; overflow: hidden;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-3 mb-md-0">
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-error-circle me-2"></i>{{ $incident->incident_no ?? $incident->incident_code ?? 'N/A' }} - {{ $incident->title ?? $incident->subject ?? 'No Title' }}
                            </h3>
                            <p class="mb-0 text-white-50 fs-6">
                                @if($incident->description)
                                    {{ Str::limit($incident->description, 150) }}
                                @else
                                    Incident reported on {{ $incident->created_at->format('M j, Y g:i A') }}
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($canEdit)
                                <button class="btn btn-light btn-lg shadow-sm" onclick="editIncident()">
                                    <i class="bx bx-edit me-2"></i>Edit
                                </button>
                            @endif
                            @if($canAssign && !$incident->assigned_to)
                                <button class="btn btn-light btn-lg shadow-sm" onclick="showAssignModal()">
                                    <i class="bx bx-user-plus me-2"></i>Assign
                                </button>
                            @endif
                            <a href="{{ route('modules.incidents') }}" class="btn btn-light btn-lg shadow-sm">
                                <i class="bx bx-arrow-back me-2"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-info-circle fs-1 text-{{ $statusColors[$status] ?? 'secondary' }} mb-2"></i>
                    <h4 class="mb-0 text-capitalize">{{ str_replace('_', ' ', $incident->status ?? 'New') }}</h4>
                    <small class="text-muted">Status</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-error-circle fs-1 text-{{ $priorityColors[$priority] ?? 'secondary' }} mb-2"></i>
                    <h4 class="mb-0 text-capitalize">{{ ucfirst($incident->priority ?? 'Medium') }}</h4>
                    <small class="text-muted">Priority</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-calendar fs-1 text-info mb-2"></i>
                    <h4 class="mb-0">{{ $daysOpen }}</h4>
                    <small class="text-muted">Days Open</small>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bx bx-message-rounded-dots fs-1 text-primary mb-2"></i>
                    <h4 class="mb-0">{{ $updates->count() }}</h4>
                    <small class="text-muted">Comments</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#details">
                                <i class="bx bx-info-circle me-2"></i>Details
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#comments">
                                <i class="bx bx-message-rounded-dots me-2"></i>Comments ({{ $updates->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#timeline">
                                <i class="bx bx-time-five me-2"></i>Timeline ({{ $activities->count() }})
                            </a>
                        </li>
                        @if($incident->hasAttachments() || $canEdit)
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#attachments">
                                <i class="bx bx-paperclip me-2"></i>Attachments
                            </a>
                        </li>
                        @endif
                        @if($canResolve)
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#actions">
                                <i class="bx bx-cog me-2"></i>Actions
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Details Tab -->
                        <div class="tab-pane fade show active" id="details" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <h6 class="border-bottom pb-2 mb-3"><i class="bx bx-info-circle me-2"></i>Incident Information</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th class="text-muted" style="width: 40%;">Incident Number:</th>
                                            <td><strong>{{ $incident->incident_no ?? $incident->incident_code ?? 'N/A' }}</strong></td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Title:</th>
                                            <td>{{ $incident->title ?? $incident->subject ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Category:</th>
                                            <td><span class="badge bg-secondary">{{ ucfirst($incident->category ?? 'N/A') }}</span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Source:</th>
                                            <td><span class="badge bg-info">{{ ucfirst($incident->source ?? 'Manual') }}</span></td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Created:</th>
                                            <td>{{ $incident->created_at->format('M j, Y g:i A') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted">Last Updated:</th>
                                            <td>{{ $incident->updated_at->format('M j, Y g:i A') }}</td>
                                        </tr>
                                        @if($incident->due_date)
                                        <tr>
                                            <th class="text-muted">Due Date:</th>
                                            <td>
                                                {{ $incident->due_date->format('M j, Y') }}
                                                @if($incident->isOverdue())
                                                    <span class="badge bg-danger ms-2">Overdue</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <h6 class="border-bottom pb-2 mb-3"><i class="bx bx-user me-2"></i>Reporter Information</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <th class="text-muted" style="width: 40%;">Name:</th>
                                            <td><strong>{{ $incident->reporter_name ?? ($incident->reporter->name ?? 'N/A') }}</strong></td>
                                        </tr>
                                        @if($incident->reporter_email || ($incident->reporter && $incident->reporter->email))
                                        <tr>
                                            <th class="text-muted">Email:</th>
                                            <td>
                                                <a href="mailto:{{ $incident->reporter_email ?? $incident->reporter->email }}">
                                                    {{ $incident->reporter_email ?? $incident->reporter->email }}
                                                </a>
                                            </td>
                                        </tr>
                                        @endif
                                        @if($incident->reporter_phone)
                                        <tr>
                                            <th class="text-muted">Phone:</th>
                                            <td>
                                                <a href="tel:{{ $incident->reporter_phone }}">{{ $incident->reporter_phone }}</a>
                                            </td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="border-bottom pb-2 mb-3"><i class="bx bx-file-blank me-2"></i>Description</h6>
                                <div class="p-3 bg-light rounded">
                                    <p style="white-space: pre-wrap; line-height: 1.6; margin: 0;">{{ $incident->description ?? 'No description provided.' }}</p>
                                </div>
                            </div>

                            @if($incident->assignedTo)
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2 mb-3"><i class="bx bx-user-check me-2"></i>Assignment</h6>
                                <div class="p-3 bg-light rounded">
                                    <p class="mb-1"><strong>{{ $incident->assignedTo->name }}</strong></p>
                                    @if($incident->assigned_at)
                                    <small class="text-muted">
                                        <i class="bx bx-calendar me-1"></i>Assigned on {{ $incident->assigned_at->format('M j, Y g:i A') }}
                                    </small>
                                    @endif
                                    @if($incident->assignedBy)
                                    <br><small class="text-muted">Assigned by {{ $incident->assignedBy->name }}</small>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($incident->resolution_notes || $incident->resolution_details)
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2 mb-3 text-success"><i class="bx bx-check-circle me-2"></i>Resolution</h6>
                                <div class="alert alert-success">
                                    <p style="white-space: pre-wrap; margin: 0;">{{ $incident->resolution_notes ?? $incident->resolution_details }}</p>
                                    @if($incident->resolvedBy)
                                    <hr>
                                    <small class="text-muted">
                                        <i class="bx bx-user me-1"></i>Resolved by {{ $incident->resolvedBy->name }} on {{ $incident->resolved_at->format('M j, Y g:i A') }}
                                        @if($incident->getResolutionTimeInDays())
                                            ({{ $incident->getResolutionTimeInDays() }} days)
                                        @endif
                                    </small>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($incident->internal_notes && $isManager)
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2 mb-3 text-info"><i class="bx bx-lock me-2"></i>Internal Notes</h6>
                                <div class="alert alert-info">
                                    <p style="white-space: pre-wrap; margin: 0;">{{ $incident->internal_notes }}</p>
                                </div>
                            </div>
                            @endif
                        </div>

                        <!-- Comments Tab -->
                        <div class="tab-pane fade" id="comments" role="tabpanel">
                            <form id="commentForm" class="mb-4">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0"><i class="bx bx-message-add me-2"></i>Add Comment</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <textarea class="form-control" id="commentText" rows="3" placeholder="Write your comment here..." required></textarea>
                                        </div>
                                        @if($isManager)
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" id="isInternalComment">
                                            <label class="form-check-label" for="isInternalComment">
                                                Internal note (not visible to reporter)
                                            </label>
                                        </div>
                                        @endif
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-message-add me-1"></i>Add Comment
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <hr>

                            <div id="commentsList">
                                @forelse($updates as $update)
                                <div class="d-flex mb-3 {{ $update->is_internal_note ? 'opacity-75' : '' }}">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center text-white">
                                            {{ substr($update->user->name ?? 'U', 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <strong>{{ $update->user->name ?? 'Unknown' }}</strong>
                                                        @if($update->is_internal_note)
                                                        <span class="badge bg-info ms-2">Internal</span>
                                                        @endif
                                                    </div>
                                                    <small class="text-muted">{{ $update->created_at->format('M j, Y g:i A') }}</small>
                                                </div>
                                                <p class="mb-0" style="white-space: pre-wrap;">{{ $update->update_text }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-muted py-5">
                                    <i class="bx bx-message-square-x" style="font-size: 3rem;"></i>
                                    <p class="mt-2">No comments yet. Be the first to comment!</p>
                                </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Timeline Tab -->
                        <div class="tab-pane fade" id="timeline" role="tabpanel">
                            @if($activities->count() > 0)
                            <div class="timeline">
                                @foreach($activities as $activity)
                                <div class="d-flex mb-3">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-sm bg-secondary rounded-circle d-flex align-items-center justify-content-center text-white">
                                            {{ substr($activity->user->name ?? 'S', 0, 1) }}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-1">
                                                    <div>
                                                        <strong>{{ $activity->user->name ?? 'System' }}</strong>
                                                        <span class="text-muted ms-2">{{ $activity->description }}</span>
                                                    </div>
                                                    <small class="text-muted">{{ $activity->created_at->format('M j, Y g:i A') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center text-muted py-5">
                                <i class="bx bx-time" style="font-size: 3rem;"></i>
                                <p class="mt-2">No activity recorded yet.</p>
                            </div>
                            @endif
                        </div>

                        <!-- Attachments Tab -->
                        @if($incident->hasAttachments() || $canEdit)
                        <div class="tab-pane fade" id="attachments" role="tabpanel">
                            @if($incident->hasAttachments())
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>File Name</th>
                                            <th>Size</th>
                                            <th>Uploaded</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($incident->attachments as $attachment)
                                        @php
                                            $filePath = is_string($attachment) ? $attachment : ($attachment['path'] ?? '');
                                            $fileName = basename($filePath);
                                            $fullPath = 'public/' . $filePath;
                                            $fileSize = Storage::exists($fullPath) ? Storage::size($fullPath) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <i class="bx bx-file me-2"></i>
                                                <strong>{{ $fileName }}</strong>
                                            </td>
                                            <td>{{ $fileSize > 0 ? number_format($fileSize / 1024, 2) . ' KB' : 'N/A' }}</td>
                                            <td>{{ $incident->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <a href="{{ Storage::url($filePath) }}" target="_blank" class="btn btn-sm btn-primary">
                                                    <i class="bx bx-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center text-muted py-5">
                                <i class="bx bx-paperclip" style="font-size: 3rem;"></i>
                                <p class="mt-2">No attachments for this incident.</p>
                            </div>
                            @endif
                        </div>
                        @endif

                        <!-- Actions Tab -->
                        @if($canResolve)
                        <div class="tab-pane fade" id="actions" role="tabpanel">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="bx bx-cog me-2"></i>Update Status</h6>
                                </div>
                                <div class="card-body">
                                    <form id="statusForm">
                                        <input type="hidden" id="statusIncidentId" value="{{ $incident->id }}">
                                        <div class="mb-3">
                                            <label class="form-label">Status <span class="text-danger">*</span></label>
                                            <select class="form-select" id="statusSelect" required>
                                                <option value="New" {{ $incident->status === 'New' ? 'selected' : '' }}>New</option>
                                                <option value="Assigned" {{ $incident->status === 'Assigned' ? 'selected' : '' }}>Assigned</option>
                                                <option value="In Progress" {{ $incident->status === 'In Progress' ? 'selected' : '' }}>In Progress</option>
                                                <option value="Resolved" {{ $incident->status === 'Resolved' ? 'selected' : '' }}>Resolved</option>
                                                @if($isManager)
                                                <option value="Closed" {{ $incident->status === 'Closed' ? 'selected' : '' }}>Closed</option>
                                                <option value="Cancelled" {{ $incident->status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                @endif
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Resolution Notes <span id="notesRequired" style="display:none;" class="text-danger">*</span></label>
                                            <textarea class="form-control" id="resolutionNotes" rows="4" placeholder="Enter resolution details..."></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bx bx-check me-1"></i>Update Status
                                        </button>
                                    </form>
                                </div>
                            </div>

                            @if($canAssign && !$incident->assigned_to)
                            <div class="card border-info mt-4">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="bx bx-user-plus me-2"></i>Assign Incident</h6>
                                </div>
                                <div class="card-body">
                                    <form id="assignForm">
                                        <input type="hidden" id="assignIncidentId" value="{{ $incident->id }}">
                                        <div class="mb-3">
                                            <label class="form-label">Assign To <span class="text-danger">*</span></label>
                                            <select class="form-select" id="assignTo" required>
                                                <option value="">Select Staff...</option>
                                                @if(isset($staff) && count($staff) > 0)
                                                    @foreach($staff as $member)
                                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Internal Notes (Optional)</label>
                                            <textarea class="form-control" id="assignNotes" rows="3"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-info">
                                            <i class="bx bx-user-plus me-1"></i>Assign
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Incident Modal -->
@if($canEdit)
<div class="modal fade" id="editIncidentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Incident</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editIncidentForm">
                <div class="modal-body">
                    <input type="hidden" id="editIncidentId" value="{{ $incident->id }}">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editTitle" value="{{ $incident->title ?? $incident->subject }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="editDescription" rows="4" required>{{ $incident->description }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select class="form-select" id="editPriority" required>
                                <option value="Low" {{ strtolower($incident->priority) === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="Medium" {{ strtolower($incident->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="High" {{ strtolower($incident->priority) === 'high' ? 'selected' : '' }}>High</option>
                                <option value="Critical" {{ strtolower($incident->priority) === 'critical' ? 'selected' : '' }}>Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="editCategory" required>
                                <option value="technical" {{ $incident->category === 'technical' ? 'selected' : '' }}>Technical</option>
                                <option value="hr" {{ $incident->category === 'hr' ? 'selected' : '' }}>HR</option>
                                <option value="facilities" {{ $incident->category === 'facilities' ? 'selected' : '' }}>Facilities</option>
                                <option value="security" {{ $incident->category === 'security' ? 'selected' : '' }}>Security</option>
                                <option value="other" {{ $incident->category === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="editDueDate" value="{{ $incident->due_date ? $incident->due_date->format('Y-m-d') : '' }}">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reporter Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editReporterName" value="{{ $incident->reporter_name }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reporter Email</label>
                            <input type="email" class="form-control" id="editReporterEmail" value="{{ $incident->reporter_email }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Reporter Phone</label>
                            <input type="tel" class="form-control" id="editReporterPhone" value="{{ $incident->reporter_phone }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function showToast(message, type = 'success') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: type,
            title: type === 'success' ? 'Success' : 'Error',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        alert(message);
    }
}

function editIncident() {
    const modal = new bootstrap.Modal(document.getElementById('editIncidentModal'));
    modal.show();
}

function showAssignModal() {
    // Switch to Actions tab and show assign form
    const actionsTab = document.querySelector('a[href="#actions"]');
    if (actionsTab) {
        new bootstrap.Tab(actionsTab).show();
    }
}

// Status form
if (document.getElementById('statusSelect')) {
    document.getElementById('statusSelect').addEventListener('change', function() {
        const notesRequired = document.getElementById('notesRequired');
        if (this.value === 'Resolved') {
            notesRequired.style.display = 'inline';
            document.getElementById('resolutionNotes').required = true;
        } else {
            notesRequired.style.display = 'none';
            document.getElementById('resolutionNotes').required = false;
        }
    });

    document.getElementById('statusForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const incidentId = document.getElementById('statusIncidentId').value;
        const status = document.getElementById('statusSelect').value;
        const notes = document.getElementById('resolutionNotes').value;
        
        const formData = new FormData();
        formData.append('status', status);
        formData.append('resolution_notes', notes);
        
        try {
            const res = await fetch(`/modules/incidents/${incidentId}/status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await res.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Error updating status', 'error');
            }
        } catch (error) {
            showToast('Network error', 'error');
        }
    });
}

// Comment form
document.getElementById('commentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const incidentId = {{ $incident->id }};
    const comment = document.getElementById('commentText').value;
    const isInternal = document.getElementById('isInternalComment')?.checked || false;
    
    const formData = new FormData();
    formData.append('comment', comment);
    formData.append('is_internal', isInternal);
    
    try {
        const res = await fetch(`/modules/incidents/${incidentId}/comment`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById('commentText').value = '';
            if (document.getElementById('isInternalComment')) {
                document.getElementById('isInternalComment').checked = false;
            }
            setTimeout(() => location.reload(), 500);
        } else {
            showToast(data.message || 'Error adding comment', 'error');
        }
    } catch (error) {
        showToast('Network error', 'error');
    }
});

// Edit incident form
@if($canEdit)
document.getElementById('editIncidentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const incidentId = document.getElementById('editIncidentId').value;
    const formData = new FormData();
    formData.append('title', document.getElementById('editTitle').value);
    formData.append('description', document.getElementById('editDescription').value);
    formData.append('priority', document.getElementById('editPriority').value);
    formData.append('category', document.getElementById('editCategory').value);
    formData.append('due_date', document.getElementById('editDueDate').value);
    formData.append('reporter_name', document.getElementById('editReporterName').value);
    formData.append('reporter_email', document.getElementById('editReporterEmail').value);
    formData.append('reporter_phone', document.getElementById('editReporterPhone').value);
    formData.append('_method', 'PUT');
    
    try {
        const res = await fetch(`/modules/incidents/${incidentId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await res.json();
        
        if (data.success) {
            showToast(data.message, 'success');
            bootstrap.Modal.getInstance(document.getElementById('editIncidentModal')).hide();
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Error updating incident', 'error');
        }
    } catch (error) {
        showToast('Network error', 'error');
    }
});
@endif

// Assign form
@if($canAssign)
if (document.getElementById('assignForm')) {
    document.getElementById('assignForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const incidentId = document.getElementById('assignIncidentId').value;
        const assignedTo = document.getElementById('assignTo').value;
        const notes = document.getElementById('assignNotes').value;
        
        const formData = new FormData();
        formData.append('assigned_to', assignedTo);
        formData.append('internal_notes', notes);
        
        try {
            const res = await fetch(`/modules/incidents/${incidentId}/assign`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await res.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Error assigning incident', 'error');
            }
        } catch (error) {
            showToast('Network error', 'error');
        }
    });
}
@endif
</script>
@endpush
@endsection
