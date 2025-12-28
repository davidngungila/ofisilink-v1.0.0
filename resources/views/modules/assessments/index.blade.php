@extends('layouts.app')

@section('title', 'Assessments')

@section('breadcrumb')
    <div class="db-breadcrumb d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="breadcrumb-title mb-1">Assessments</h4>
            <small class="text-muted">Define responsibilities, submit progress, and approvals</small>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createMainRespModal">
            <i class="fa fa-plus me-2"></i>Create Responsibility
        </button>
    </div>
@endsection

@section('content')
<div class="container-fluid" id="assessmentApp">
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Pending Approvals</div>
                    <div class="h4 mb-0">{{ $stats['pending_approvals'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Pending Reports</div>
                    <div class="h4 mb-0">{{ $stats['pending_reports'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">Total Responsibilities</div>
                    <div class="h4 mb-0">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">My Responsibilities</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="assessTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Owner</th>
                            <th>Frequency</th>
                            <th>Status</th>
                            <th>Subs</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($responsibilities as $r)
                        <tr>
                            <td><strong>{{ $r->title }}</strong><br><small class="text-muted">{{ $r->description }}</small></td>
                            <td>{{ $r->owner->name ?? '' }}</td>
                            <td><span class="badge bg-secondary text-uppercase">{{ $r->frequency }}</span></td>
                            <td><span class="badge bg-{{ $r->status === 'approved' ? 'success' : ($r->status === 'pending_approval' ? 'warning' : 'secondary') }}">{{ $r->status }}</span></td>
                            <td>{{ $r->subs->count() }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-info" onclick="viewResponsibility({{ $r->id }})">View</button>
                                    @if($r->status === 'approved')
                                    <button class="btn btn-outline-primary" onclick="openAddSub({{ $r->id }})">Add Sub</button>
                                    @endif
                                    @if($isManager && $r->status === 'pending_approval')
                                    <button class="btn btn-success" onclick="approveMain({{ $r->id }})">Approve</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Main Responsibility Modal -->
<div class="modal fade" id="createMainRespModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Responsibility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createMainForm" data-ajax-form="true">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" value="as_create_main">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Frequency</label>
                            <select name="frequency" class="form-select">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Owner</label>
                            <select name="user_id" class="form-select">
                                @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>

</div>

<!-- View Responsibility Modal -->
<div class="modal fade" id="viewRespModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="view-resp-content">
            <div class="modal-header">
                <h5 class="modal-title">Loading...</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary"></div>
            </div>
        </div>
    </div>
</div>

<!-- Add Sub Responsibility Modal -->
<div class="modal fade" id="addSubModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Sub Responsibility</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addSubForm" data-ajax-form="true">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" value="as_add_sub">
                    <input type="hidden" name="main_id" id="add-sub-main-id">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Submit Progress Modal -->
<div class="modal fade" id="submitProgressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Submit Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="submitProgressForm" data-ajax-form="true">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="action" value="as_submit_report">
                    <input type="hidden" name="sub_id" id="progress-sub-id">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Period Start</label>
                            <input type="date" name="period_start" id="progress-start" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Period End</label>
                            <input type="date" name="period_end" id="progress-end" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Progress (text only)</label>
                        <textarea name="content" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="alert alert-info small" id="progress-hint">Ensure the dates match the required frequency.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
const assessData = @json($responsibilities);
const isManager = @json($isManager);
const Assess = {
    api(formData) {
        if (!formData.has('_token')) formData.append('_token', CSRF);
        return $.ajax({
            type: 'POST',
            url: '{{ route('modules.assessments.action') }}',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': CSRF }
        });
    },
    getMainById(id){ return assessData.find(r => r.id == id); },
    getSubById(mainId, subId){ const m = this.getMainById(mainId); return m ? m.subs.find(s => s.id == subId) : null; },
    // Frequency window helpers
    getPeriodForFrequency(freq, date = new Date()){
        const pad = n => String(n).padStart(2,'0');
        const toStr = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;
        let start = new Date(date), end = new Date(date);
        if (freq === 'daily') {
            // start and end are the same day
        } else if (freq === 'weekly') {
            const day = date.getDay(); // 0=Sun
            const diffToMonday = (day === 0 ? -6 : 1 - day);
            start = new Date(date); start.setDate(date.getDate()+diffToMonday);
            end = new Date(start); end.setDate(start.getDate()+6);
        } else { // monthly
            start = new Date(date.getFullYear(), date.getMonth(), 1);
            end = new Date(date.getFullYear(), date.getMonth()+1, 0);
        }
        return { start: toStr(start), end: toStr(end) };
    },
    hasReportInPeriod(sub, startStr, endStr){
        const toNum = s => Number((s||'').replaceAll('-',''));
        const sN = toNum(startStr), eN = toNum(endStr);
        return (sub.progress_reports||[]).some(r => {
            const rs = toNum(r.period_start), re = toNum(r.period_end);
            // overlap same window counts as duplicate
            return rs === sN && re === eN;
        });
    }
};

$(document).on('submit', 'form[data-ajax-form]', function(e){
    e.preventDefault();
    const form = this;
    const fd = new FormData(form);
    const btn = $(form).find('button[type="submit"]');
    const original = btn.html();
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
    Assess.api(fd).done(r => {
        if (r && r.success) {
            if (typeof Swal !== 'undefined') Swal.fire('Success', r.message || 'Done', 'success');
            $(form).closest('.modal').modal('hide');
            window.location.reload();
        } else {
            if (typeof Swal !== 'undefined') Swal.fire('Error', (r && r.message) || 'Failed', 'error');
        }
    }).fail(() => {
        if (typeof Swal !== 'undefined') Swal.fire('Error','Network error','error');
    }).always(() => {
        btn.prop('disabled', false).html(original);
    });
});

window.viewResponsibility = function(id){
    $('#viewRespModal').modal('show');
    const main = Assess.getMainById(id);
    if (!main) { $('#view-resp-content').html('<div class="modal-body">Not found</div>'); return; }
    const subs = (main.subs||[]).map(s => {
        const reports = (s.progress_reports||[]).map(r => {
            const badge = r.status==='Approved'?'success':(r.status==='Rejected'?'danger':'warning');
            const actions = isManager && r.status==='Pending' ? `
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-success" onclick="approveReport(${r.id})">Approve</button>
                    <button class="btn btn-danger" onclick="rejectReport(${r.id})">Reject</button>
                </div>` : '';
            return `<li class="list-group-item d-flex justify-content-between align-items-start">
                <div class="ms-2 me-auto">
                    <div><strong>${r.period_start} → ${r.period_end}</strong> <span class="badge bg-${badge}">${r.status}</span></div>
                    <div class="small">${(r.content||'').replace(/</g,'&lt;')}</div>
                </div>
                ${actions}
            </li>`;
        }).join('') || '<li class="list-group-item text-muted">No reports</li>';
        const submitBtn = main.status==='approved' ? `<button class="btn btn-primary btn-sm" onclick="openSubmitProgress(${main.id}, ${s.id})">Submit Progress</button>` : '';
        return `<div class="card mb-2">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div><strong>${s.title}</strong><br><small class="text-muted">${s.description||''}</small></div>
                ${submitBtn}
            </div>
            <ul class="list-group list-group-flush">${reports}</ul>
        </div>`;
    }).join('');
    $('#view-resp-content').html(`
        <div class="modal-header"><h5 class="modal-title">${main.title} <small class="text-muted text-uppercase">(${main.frequency})</small></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><strong>Owner:</strong> ${main.owner?.name||''} | <strong>Status:</strong> <span class="badge bg-${main.status==='approved'?'success':(main.status==='pending_approval'?'warning':'secondary')}">${main.status}</span></div>
            <div>${subs || '<p class="text-muted">No sub responsibilities</p>'}</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button></div>
    `);
}

window.approveMain = function(mainId){
    const fd = new FormData(); fd.append('action','as_approve_main'); fd.append('main_id', mainId);
    Assess.api(fd).done(r => { if (r && r.success){ if (typeof Swal !== 'undefined') Swal.fire('Approved', r.message, 'success').then(()=>window.location.reload()); }});
}

window.openAddSub = function(mainId){
    $('#add-sub-main-id').val(mainId);
    $('#addSubModal').modal('show');
}

window.openSubmitProgress = function(mainId, subId){
    const main = Assess.getMainById(mainId); if (!main) return;
    const period = Assess.getPeriodForFrequency(main.frequency, new Date());
    $('#progress-sub-id').val(subId);
    $('#progress-start').val(period.start);
    $('#progress-end').val(period.end);
    $('#progress-hint').text(`Required frequency: ${main.frequency}. Submissions are limited to one per period.`);
    $('#submitProgressModal').modal('show');
}

window.approveReport = function(reportId){
    const fd = new FormData(); fd.append('action','as_approve_report'); fd.append('report_id', reportId);
    Assess.api(fd).done(r => { if (r && r.success){ if (typeof Swal !== 'undefined') Swal.fire('Success','Report approved','success').then(()=>window.location.reload()); }});
}

window.rejectReport = function(reportId){
    const fd = new FormData(); fd.append('action','as_reject_report'); fd.append('report_id', reportId);
    Assess.api(fd).done(r => { if (r && r.success){ if (typeof Swal !== 'undefined') Swal.fire('Success','Report rejected','success').then(()=>window.location.reload()); }});
}
</script>
@endpush


