@extends('layouts.app')

@section('title', 'Activity Reports - OfisiLink')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1"><i class="bx bx-list-ul me-2"></i>Activity Reports</h4>
                            <p class="mb-0 text-muted">Activity: <strong>{{ $activity->activity_name }}</strong></p>
                        </div>
                        <a href="{{ route('assessments.show', $activity->assessment->id) }}" class="btn btn-outline-secondary">
                            <i class="bx bx-arrow-back me-1"></i>Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Progress Reports</h5>
                        <div class="input-group" style="max-width: 240px;">
                            <span class="input-group-text">Year</span>
                            <input type="number" class="form-control" id="reports-year" value="{{ date('Y') }}" min="2000" max="2100">
                            <button class="btn btn-outline-secondary" id="load-reports">Load</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="reports-content">
                        <div class="text-center text-muted py-4">Click "Load" to view reports</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#load-reports').on('click', function() {
    const year = $('#reports-year').val();
    const content = $('#reports-content');
    content.html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Loading...</p></div>');
    
    fetch('{{ route("assessments.activity-reports", $activity->id) }}?year=' + year)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.reports.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Date</th><th>Status</th><th>Text</th><th>Approver</th><th>Approved At</th></tr></thead><tbody>';
                data.reports.forEach(r => {
                    const badge = r.status === 'approved' ? 'success' : (r.status === 'pending_approval' ? 'warning' : 'danger');
                    html += `<tr>
                        <td>${r.date}</td>
                        <td><span class="badge bg-${badge}">${r.status.replace('_', ' ')}</span></td>
                        <td>${(r.text || '').substring(0, 140)}</td>
                        <td>${r.approver || 'N/A'}</td>
                        <td>${r.approved_at || 'N/A'}</td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                content.html(html);
            } else {
                content.html('<div class="text-center text-muted py-4">No reports found for this year.</div>');
            }
        })
        .catch(err => {
            content.html('<div class="alert alert-danger">Failed to load reports</div>');
        });
});
</script>
@endpush






