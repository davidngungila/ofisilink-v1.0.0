<div class="table-responsive">
  <table class="table table-hover align-middle" id="incidentsTable">
    <thead>
      <tr>
        @if($isManager ?? false)
        <th width="40">
          <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
        </th>
        @endif
        <th>Incident #</th>
        <th>Title</th>
        <th>Reporter</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Assigned To</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      @forelse($incidents as $incident)
      <tr>
        @if($isManager ?? false)
        <td>
          <input type="checkbox" name="incident_ids[]" value="{{ $incident->id }}" onchange="updateSelectedIncidents()">
        </td>
        @endif
        <td><strong>{{ $incident->incident_no ?? $incident->incident_code ?? 'N/A' }}</strong></td>
        <td>{{ Str::limit($incident->title ?? $incident->subject ?? 'No Title', 50) }}</td>
        <td>
          <div>
            <div class="fw-semibold">{{ $incident->reporter_name ?? ($incident->reporter->name ?? 'N/A') }}</div>
            <small class="text-muted">{{ $incident->reporter_email ?? ($incident->reporter->email ?? '') }}</small>
          </div>
        </td>
        <td>
          @php
          $priority = strtolower($incident->priority ?? 'medium');
          $priorityColors = [
            'low' => 'secondary',
            'medium' => 'info',
            'high' => 'warning',
            'critical' => 'danger'
          ];
          @endphp
          <span class="badge bg-{{ $priorityColors[$priority] ?? 'secondary' }}">
            {{ ucfirst($incident->priority ?? 'Medium') }}
          </span>
        </td>
        <td>
          @php
          $status = strtolower(str_replace(' ', '_', $incident->status ?? 'new'));
          $statusColors = [
            'new' => 'primary',
            'assigned' => 'info',
            'in_progress' => 'warning',
            'resolved' => 'success',
            'closed' => 'secondary',
            'cancelled' => 'danger'
          ];
          @endphp
          <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">
            {{ $incident->status ?? 'New' }}
          </span>
        </td>
        <td>
          @if($incident->assignedTo)
            <div class="d-flex align-items-center">
              <i class="bx bx-user me-1"></i>
              <span>{{ $incident->assignedTo->name }}</span>
            </div>
          @else
            <span class="text-muted">Unassigned</span>
          @endif
        </td>
        <td>{{ $incident->created_at->format('M j, Y') }}</td>
        <td>
          <div class="btn-group btn-group-sm">
            <a href="{{ route('modules.incidents.show', $incident->id) }}" class="btn btn-primary" title="View Details">
              <i class="bx bx-show"></i>
            </a>
            @if($isManager && !$incident->assigned_to)
            <button class="btn btn-outline-info" onclick="assignIncident({{ $incident->id }})" title="Assign">
              <i class="bx bx-user-plus"></i>
            </button>
            @endif
          </div>
        </td>
      </tr>
      @empty
      <tr>
        <td colspan="{{ ($isManager ?? false) ? 9 : 8 }}" class="text-center py-5">
          <div class="text-muted">
            <i class="bx bx-inbox" style="font-size: 3rem;"></i>
            <p class="mt-2 mb-0">No incidents found in this category.</p>
          </div>
        </td>
      </tr>
      @endforelse
    </tbody>
  </table>
</div>

@if(isset($incidents) && method_exists($incidents, 'links') && $incidents->hasPages())
<div class="d-flex justify-content-center mt-3">
  {{ $incidents->links() }}
</div>
@endif

@if($isManager ?? false)
<script>
function toggleSelectAll() {
  const selectAll = document.getElementById('selectAll');
  const checkboxes = document.querySelectorAll('input[type="checkbox"][name="incident_ids[]"]');
  checkboxes.forEach(cb => {
    cb.checked = selectAll.checked;
  });
  updateSelectedIncidents();
}
</script>
@endif

