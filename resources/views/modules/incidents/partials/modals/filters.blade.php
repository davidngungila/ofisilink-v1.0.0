<!-- Filters Modal -->
<div class="modal fade" id="filtersModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">
          <i class="bx bx-filter me-2"></i>Advanced Filters
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="filtersForm" method="GET" action="{{ route('modules.incidents') }}">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="">All Statuses</option>
                <option value="New">New</option>
                <option value="Assigned">Assigned</option>
                <option value="In Progress">In Progress</option>
                <option value="Resolved">Resolved</option>
                <option value="Closed">Closed</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Priority</label>
              <select class="form-select" name="priority">
                <option value="">All Priorities</option>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
                <option value="Critical">Critical</option>
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Category</label>
              <select class="form-select" name="category">
                <option value="">All Categories</option>
                <option value="technical">Technical</option>
                <option value="hr">HR</option>
                <option value="facilities">Facilities</option>
                <option value="security">Security</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Assigned To</label>
              <select class="form-select" name="assigned_to">
                <option value="">All Assignees</option>
                @if(isset($staff) && count($staff) > 0)
                  @foreach($staff as $member)
                    <option value="{{ $member->id }}">{{ $member->name }}</option>
                  @endforeach
                @endif
              </select>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Date From</label>
              <input type="date" class="form-control" name="date_from">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Date To</label>
              <input type="date" class="form-control" name="date_to">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Search</label>
            <input type="text" class="form-control" name="search" placeholder="Search by title, description, or reporter...">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" onclick="clearFilters()">Clear All</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-filter me-1"></i>Apply Filters
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function clearFilters() {
  document.getElementById('filtersForm').reset();
  window.location.href = '{{ route("modules.incidents") }}';
}
</script>


