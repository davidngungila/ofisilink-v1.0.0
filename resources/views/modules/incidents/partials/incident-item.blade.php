<div class="incident-item" data-incident-id="{{ $incident->id }}" data-status="{{ $incident->status }}">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center flex-grow-1">
            <div class="me-3">
                @if(strtolower($incident->priority) === 'critical')
                    <i class="bx bx-error-circle fs-2 text-danger"></i>
                @elseif(strtolower($incident->priority) === 'high')
                    <i class="bx bx-error-circle fs-2 text-warning"></i>
                @else
                    <i class="bx bx-error-circle fs-2 text-info"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1 fw-bold">
                    <a href="{{ route('modules.incidents.show', $incident->id) }}" class="text-decoration-none incident-link" data-incident-id="{{ $incident->id }}">
                        {{ $incident->incident_no ?? $incident->incident_code ?? 'N/A' }} - {{ $incident->subject ?? $incident->title ?? 'N/A' }}
                    </a>
                </h6>
                <div class="d-flex gap-3 flex-wrap">
                    <small class="text-muted">
                        <i class="bx bx-user me-1"></i>{{ $incident->reporter->name ?? $incident->reporter_name ?? 'N/A' }}
                    </small>
                    @if($incident->assignedTo)
                    <small class="text-muted">
                        <i class="bx bx-user-check me-1"></i>Assigned to: {{ $incident->assignedTo->name }}
                    </small>
                    @else
                    <small class="text-muted">
                        <i class="bx bx-user-x me-1"></i>Unassigned
                    </small>
                    @endif
                    <small class="text-muted">
                        <span class="badge bg-{{ strtolower($incident->priority) === 'critical' ? 'danger' : (strtolower($incident->priority) === 'high' ? 'warning' : (strtolower($incident->priority) === 'medium' ? 'info' : 'secondary')) }}">
                            {{ $incident->priority }}
                        </span>
                    </small>
                    <small class="text-muted">
                        <span class="badge bg-{{ strtolower($incident->status) === 'resolved' ? 'success' : (strtolower($incident->status) === 'in progress' ? 'warning' : (strtolower($incident->status) === 'assigned' ? 'info' : 'secondary')) }}">
                            {{ $incident->status }}
                        </span>
                    </small>
                    <small class="text-muted">
                        <i class="bx bx-time me-1"></i>{{ $incident->created_at->diffForHumans() }}
                    </small>
                </div>
                @if($incident->description)
                <p class="text-muted mb-0 mt-1 small">{{ Str::limit($incident->description, 150) }}</p>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('modules.incidents.show', $incident->id) }}" class="btn btn-sm btn-outline-primary view-incident-btn" data-incident-id="{{ $incident->id }}">
                <i class="bx bx-show"></i> View
            </a>
            @if(isset($canManageIncidents) && $canManageIncidents)
            <a href="{{ route('modules.incidents.show', $incident->id) }}" class="btn btn-sm btn-outline-info">
                <i class="bx bx-edit"></i> Edit
            </a>
            @endif
        </div>
    </div>
</div>

