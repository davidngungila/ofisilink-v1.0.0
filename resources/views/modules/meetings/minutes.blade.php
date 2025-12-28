@extends('layouts.app')

@section('title', 'Meeting Minutes')

@section('breadcrumb')
<div class="db-breadcrumb d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="breadcrumb-title mb-1">Meeting Minutes</h4>
        <small class="text-muted">Prepare, save, and finalize meeting minutes with per-agenda actions.</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('modules.meetings.index') }}" class="btn btn-outline-secondary">Meetings</a>
    </div>
</div>
@endsection

@php
    $userOptions = $users->map(fn ($user) => ['id' => $user->id, 'name' => $user->name]);
    $preselectMeetingId = request('meeting_id');
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Minutes Preparation</h5>
                        <small class="text-muted">Select meetings without completed minutes and record actions.</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="loadAgendasBtn">Load Agendas</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addMinuteRow">Add Action/Agenda Note</button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('modules.meetings.minutes.store') }}">
                        @csrf
                        <input type="hidden" name="save_stage" id="minutesStage" value="full">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Meeting *</label>
                                <select class="form-select" name="meeting_id" id="minutesMeeting" required>
                                    <option value="">-- Select Meeting --</option>
                                    @foreach($meetings as $meeting)
                                        @if($meeting->minutes_status !== 'completed')
                                            <option value="{{ $meeting->id }}">{{ $meeting->title }} ({{ optional($meeting->meeting_date)->format('Y-m-d') }})</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft">Draft</option>
                                    <option value="final">Final</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Next Meeting Date</label>
                                <input type="date" name="next_meeting_date" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Summary / Decisions</label>
                                <textarea name="summary" class="form-control" rows="2" placeholder="Overall summary, key decisions, risks..."></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reference previous meeting actions (optional)</label>
                                <select class="form-select" id="previousMeeting">
                                    <option value="">-- No previous reference --</option>
                                    @foreach(($previousActionMeetings ?? collect()) as $meetingWithActions)
                                        <option value="{{ $meetingWithActions->id }}">{{ $meetingWithActions->title }} ({{ optional($meetingWithActions->meeting_date)->format('Y-m-d') }})</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Selecting a meeting will load its recorded actions so you can update status.</small>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="previous_actions_included" value="1" id="previousActionsIncludedMinutes">
                                    <label class="form-check-label" for="previousActionsIncludedMinutes">These minutes include previous actions</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-minutes-stage="summary">Save summary only</button>
                        </div>

                        <div class="mt-4" id="minutesItems"></div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="submit" class="btn btn-secondary">Save Draft</button>
                            <button type="submit" class="btn btn-primary" onclick="this.form.querySelector('select[name=status]').value='final'">Save & Finalize</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Pending Minutes</h6>
                    <a href="{{ route('modules.meetings.index') }}" class="btn btn-sm btn-outline-secondary">Meetings</a>
                </div>
                <div class="card-body">
                    <ul class="list-group small">
                        @forelse($pendingMinutes as $pending)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $pending->title }}</strong>
                                    <div class="text-muted">{{ optional($pending->meeting_date)->format('Y-m-d') }}</div>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" type="button" onclick="selectMinutesMeeting('{{ $pending->id }}')">Prepare</button>
                            </li>
                        @empty
                            <li class="list-group-item text-muted">No pending minutes.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
(() => {
    const minutesWrapper = document.getElementById('minutesItems');
    const addMinuteBtn = document.getElementById('addMinuteRow');
    const meetingSelect = document.getElementById('minutesMeeting');
    const previousSelect = document.getElementById('previousMeeting');
    const loadAgendasBtn = document.getElementById('loadAgendasBtn');
    const minutesStageInput = document.getElementById('minutesStage');
    const minutesForm = document.querySelector('form[action*="modules/meetings/minutes"]');
    const meetingBaseUrl = "{{ url('modules/meetings') }}";
    const agendasByMeeting = @json($agendasByMeeting);
    const userOptions = @json($userOptions);
    const preselectMeetingId = "{{ $preselectMeetingId }}";
    const statusOptions = [
        { value: 'open', label: 'Open' },
        { value: 'in_progress', label: 'In Progress' },
        { value: 'done', label: 'Done' },
    ];

    if (!minutesWrapper) {
        return;
    }

    function agendaOptionsHtml(meetingId, selected = '') {
        const agendas = (agendasByMeeting && agendasByMeeting[meetingId]) ? agendasByMeeting[meetingId] : [];
        let html = '<option value="">Agenda (optional)</option>';
        agendas.forEach((agenda) => {
            const isSelected = String(selected) === String(agenda.id) ? 'selected' : '';
            html += `<option value="${agenda.id}" ${isSelected}>${agenda.title}</option>`;
        });
        return html;
    }

    function responsibleOptionsHtml(selected = '') {
        let html = '<option value="">Responsible (optional)</option>';
        (userOptions || []).forEach((user) => {
            const isSelected = String(selected) === String(user.id) ? 'selected' : '';
            html += `<option value="${user.id}" ${isSelected}>${user.name}</option>`;
        });
        return html;
    }

    function statusOptionsHtml(selected = '') {
        const chosen = selected || 'open';
        return statusOptions.map((opt) => {
            const isSelected = String(chosen) === opt.value ? 'selected' : '';
            return `<option value="${opt.value}" ${isSelected}>${opt.label}</option>`;
        }).join('');
    }

    function currentMeetingId() {
        return meetingSelect?.value || '';
    }

    function setMinutesStage(stage) {
        if (minutesStageInput) minutesStageInput.value = stage || 'full';
    }

    function bindRowActions(row, data = {}) {
        row.querySelector('.remove-minute-row')?.addEventListener('click', () => row.remove());

        const fromPrevious = row.querySelector('.from-previous');
        if (fromPrevious && data.from_previous) {
            fromPrevious.checked = true;
        }

        const actionRequired = row.querySelector('.action-required');
        if (actionRequired && data.action_required) {
            actionRequired.checked = true;
        }

        const statusSelect = row.querySelector('.status-select');
        if (statusSelect && data.status) {
            statusSelect.value = data.status;
        }

        const responsibleSelect = row.querySelector('.responsible-select');
        if (responsibleSelect && data.responsible_id) {
            responsibleSelect.value = data.responsible_id;
        }

        const sourceInput = row.querySelector('.source-meeting-input');
        if (sourceInput && data.source_meeting_id) {
            sourceInput.value = data.source_meeting_id;
        }
    }

    function addMinuteRow(data = {}) {
        const idx = minutesWrapper.querySelectorAll('.minute-row').length;
        const meetingId = currentMeetingId();
        const row = document.createElement('div');
        row.className = 'border rounded p-3 mb-3 minute-row';
        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-label-secondary">Item #${idx + 1}</span>
                <div class="d-flex align-items-center gap-2">
                    <input type="hidden" name="items[${idx}][source_meeting_id]" class="source-meeting-input" value="${data.source_meeting_id ?? ''}">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-minute-row">Remove</button>
                </div>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="items[${idx}][title]" class="form-control" placeholder="Agenda / action title" value="${data.title ?? ''}" required>
                </div>
                <div class="col-md-3">
                    <select name="items[${idx}][agenda_id]" class="form-select agenda-select">
                        ${agendaOptionsHtml(meetingId, data.agenda_id ?? '')}
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="items[${idx}][responsible_id]" class="form-select responsible-select">
                        ${responsibleOptionsHtml(data.responsible_id ?? '')}
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="items[${idx}][due_date]" class="form-control" value="${data.due_date ?? ''}">
                </div>
                <div class="col-md-3">
                    <select name="items[${idx}][status]" class="form-select status-select">
                        ${statusOptionsHtml(data.status ?? '')}
                    </select>
                </div>
                <div class="col-md-3 mt-2 form-check">
                    <input class="form-check-input from-previous" type="checkbox" name="items[${idx}][from_previous]" value="1" id="fromPrevious${idx}" ${data.from_previous ? 'checked' : ''}>
                    <label class="form-check-label" for="fromPrevious${idx}">Referenced previous action</label>
                </div>
                <div class="col-md-3 mt-2 form-check">
                    <input class="form-check-input action-required" type="checkbox" name="items[${idx}][action_required]" value="1" id="actionRequired${idx}" ${data.action_required ? 'checked' : ''}>
                    <label class="form-check-label" for="actionRequired${idx}">Action Required</label>
                </div>
                <div class="col-md-12 mt-2">
                    <textarea name="items[${idx}][decisions]" class="form-control" rows="2" placeholder="Decisions / resolutions">${data.decisions ?? ''}</textarea>
                </div>
                <div class="col-md-12 mt-2">
                    <textarea name="items[${idx}][notes]" class="form-control" rows="2" placeholder="Notes, context, blockers">${data.notes ?? ''}</textarea>
                </div>
            </div>
        `;
        minutesWrapper.appendChild(row);
        bindRowActions(row, data);
    }

    function refreshAgendaSelects() {
        const meetingId = currentMeetingId();
        minutesWrapper.querySelectorAll('.agenda-select').forEach((select) => {
            const currentValue = select.value;
            select.innerHTML = agendaOptionsHtml(meetingId, currentValue);
        });
    }

    window.selectMinutesMeeting = function (meetingId) {
        if (meetingSelect) {
            meetingSelect.value = meetingId;
            refreshAgendaSelects();
        }
    };

    addMinuteBtn?.addEventListener('click', () => addMinuteRow());

    loadAgendasBtn?.addEventListener('click', () => {
        const meetingId = currentMeetingId();
        if (!meetingId) {
            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Select a meeting first',
                    text: 'Choose a meeting so we can load its agendas into minutes items.'
                });
            } else {
                alert('Select a meeting to load agendas.');
            }
            return;
        }
        minutesWrapper.innerHTML = '';
        const agendas = agendasByMeeting[meetingId] || [];
        if (!agendas.length) {
            addMinuteRow();
            return;
        }
        agendas.forEach((agenda) => {
            addMinuteRow({
                title: agenda.title,
                agenda_id: agenda.id,
                notes: agenda.description || '',
            });
        });
    });

    document.querySelectorAll('[data-minutes-stage]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const stage = btn.getAttribute('data-minutes-stage');
            setMinutesStage(stage);
            minutesForm?.submit();
        });
    });

    previousSelect?.addEventListener('change', async (event) => {
        const meetingId = event.target.value;
        if (!meetingId) {
            return;
        }

        try {
            const response = await fetch(`${meetingBaseUrl}/${meetingId}/previous-actions`);
            if (!response.ok) {
                return;
            }

            const data = await response.json();
            minutesWrapper.innerHTML = '';

            const includePrevious = document.getElementById('previousActionsIncludedMinutes');
            if (includePrevious) {
                includePrevious.checked = true;
            }

            if (!data.length) {
                addMinuteRow({
                    from_previous: true,
                    source_meeting_id: meetingId,
                });
                return;
            }

            data.forEach((item) => {
                addMinuteRow({
                    ...item,
                    source_meeting_id: meetingId,
                    from_previous: true,
                });
            });
        } catch (error) {
            console.error('Unable to load previous actions', error);
        }
    });

    meetingSelect?.addEventListener('change', refreshAgendaSelects);

    // Seed with one default row so minutes can be saved in stages
    addMinuteRow();

    // Preselect meeting if provided via query (?meeting_id=)
    if (preselectMeetingId && meetingSelect) {
        meetingSelect.value = preselectMeetingId;
        refreshAgendaSelects();
    }
})();
</script>
@endpush

