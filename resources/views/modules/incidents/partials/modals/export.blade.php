<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">
          <i class="bx bx-download me-2"></i>Export Incidents
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="exportForm" method="GET" action="{{ route('modules.incidents.export') }}" target="_blank">
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bx bx-info-circle me-2"></i>
            Export incidents to CSV format with optional filters.
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select class="form-select" name="status">
              <option value="">All Statuses</option>
              <option value="New">New</option>
              <option value="Assigned">Assigned</option>
              <option value="In Progress">In Progress</option>
              <option value="Resolved">Resolved</option>
              <option value="Closed">Closed</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Priority</label>
            <select class="form-select" name="priority">
              <option value="">All Priorities</option>
              <option value="Low">Low</option>
              <option value="Medium">Medium</option>
              <option value="High">High</option>
              <option value="Critical">Critical</option>
            </select>
          </div>
          <div class="mb-3">
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">
            <i class="bx bx-download me-1"></i>Export to CSV
          </button>
        </div>
      </form>
    </div>
  </div>
</div>


