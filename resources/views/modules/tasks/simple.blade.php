@extends('layouts.app')

@section('title', 'Task Management')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .hero-surface {
        background: linear-gradient(120deg, #2563eb 0%, #1d4ed8 50%, #0f172a 100%);
        border-radius: 16px;
        padding: 24px;
        color: #f8fafc;
        box-shadow: 0 12px 36px rgba(37, 99, 235, 0.25);
    }
    .hero-surface .eyebrow {
        letter-spacing: .08em;
        text-transform: uppercase;
        font-size: 12px;
        opacity: .8;
    }
    .surface-card {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        background: #fff;
    }
    .metric-card {
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid #eef2ff;
        background: #f8fafc;
        height: 100%;
    }
    .metric-label { color: #475569; font-weight: 600; font-size: 12px; }
    .pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
    }
    .pill-success { background: #ecfdf3; color: #16a34a; }
    .pill-danger { background: #fef2f2; color: #dc2626; }
    .pill-warning { background: #fffbeb; color: #d97706; }
    .pill-info { background: #eef2ff; color: #4f46e5; }
    .pill-secondary { background: #f3f4f6; color: #4b5563; }
    .task-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        background: #fff;
        height: 100%;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }
    .task-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .avatar-chip {
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 999px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 600;
    }
    .section-title { font-size: 15px; font-weight: 700; color: #111827; }
    .table td, .table th { vertical-align: middle; }
    .need-action {
        border-left: 4px solid #f97316;
        background: #fff7ed;
    }
    .label-muted { color: #6b7280; font-size: 12px; }
    .input-hint { font-size: 12px; color: #6b7280; }
    .form-section-title { font-size: 14px; font-weight: 700; color: #0f172a; }
    .shadow-slim { box-shadow: 0 10px 30px rgba(15,23,42,0.08); }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 py-3" id="tasksPage">
    <div class="hero-surface mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="eyebrow mb-1">Task Management</div>
                <h2 class="fw-bold mb-1">Plan, assign, and track activities with advanced features</h2>
                <p class="mb-2 text-white-75">Every action has its own space, every report can include documents, and approvals stay on top.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a class="btn btn-light btn-sm" href="#progressSection"><i class="bx bx-upload me-1"></i>Submit Progress</a>
                    @if($isManager)
                        <a class="btn btn-outline-light btn-sm" href="#createTaskSection"><i class="bx bx-plus me-1"></i>Create Task</a>
                    @endif
                    <a class="btn btn-dark btn-sm" href="{{ route('modules.tasks.pdf') }}" target="_blank">
                        <i class="bx bx-download me-1"></i>Export PDF
                    </a>
                    <a class="btn btn-outline-light btn-sm" href="{{ route('modules.tasks.analytics.pdf') }}" target="_blank">
                        <i class="bx bx-bar-chart me-1"></i>Analytics PDF
                    </a>
                </div>
            </div>
            <div class="text-end">
                <div class="pill pill-info mb-2">Auto SMS on submissions & decisions</div>
                <div class="label-muted">Updated {{ now()->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @if($isManager)
            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-label mb-1">Total Tasks</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">{{ $dashboardStats['total'] ?? 0 }}</h3>
                        <span class="pill pill-info">All</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-label mb-1">In Progress</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">{{ $dashboardStats['in_progress'] ?? 0 }}</h3>
                        <span class="pill pill-warning">Moving</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-label mb-1">Completed</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">{{ $dashboardStats['completed'] ?? 0 }}</h3>
                        <span class="pill pill-success">Done</span>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="metric-card">
                    <div class="metric-label mb-1">Overdue</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">{{ $dashboardStats['overdue'] ?? 0 }}</h3>
                        <span class="pill pill-danger">Action</span>
                    </div>
                </div>
            </div>
        @else
            <div class="col-12 col-lg-4">
                <div class="metric-card">
                    <div class="metric-label mb-1">My Tasks</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">{{ $dashboardStats['total_tasks'] ?? 0 }}</h3>
                        <span class="pill pill-info">Assigned</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="metric-card">
                    <div class="metric-label mb-1">Pending</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">{{ $dashboardStats['pending'] ?? 0 }}</h3>
                        <span class="pill pill-warning">Due</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="metric-card">
                    <div class="metric-label mb-1">Overdue</div>
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">{{ $dashboardStats['overdue'] ?? 0 }}</h3>
                        <span class="pill pill-danger">Action</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="card surface-card need-action shadow-slim mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <div class="section-title mb-0">Need Action — Latest submissions waiting for review</div>
                <small class="text-muted">Newest first, documents included.</small>
            </div>
            <span class="badge bg-warning text-dark">{{ $pendingReports->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($pendingReports as $report)
                    @php
                        $taskName = $report->activity->mainTask->name ?? 'Task';
                        $activityName = $report->activity->name ?? 'Activity';
                    @endphp
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <div class="fw-semibold">{{ $activityName }}</div>
                                <div class="text-muted small mb-1">{{ $taskName }}</div>
                                <div class="d-flex flex-wrap gap-2 small align-items-center">
                                    <span class="pill pill-info">{{ $report->completion_status ?? 'Pending' }}</span>
                                    <span class="text-muted">{{ \Illuminate\Support\Carbon::parse($report->report_date ?? $report->created_at)->format('M d, Y') }}</span>
                                    @if($report->attachment_path)
                                        <a class="attachment-link" target="_blank" href="{{ Storage::url($report->attachment_path) }}"><i class="bx bx-link-alt"></i> Attachment</a>
                                    @endif
                                </div>
                                @if($report->work_description)
                                    <div class="text-muted small mt-1">{{ \Illuminate\Support\Str::limit($report->work_description, 140) }}</div>
                                @endif
                            </div>
                            <div class="text-end">
                                <div class="avatar-chip mb-1">{{ $report->user->name ?? 'Reporter' }}</div>
                                @if($isManager)
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-success" onclick="approveReport({{ $report->id }})">Approve</button>
                                        <button class="btn btn-outline-danger" onclick="rejectReport({{ $report->id }})">Reject</button>
                                    </div>
                                @else
                                    <span class="pill pill-warning">Pending review</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted py-4">
                        Everything is up to date. No pending reports.
                    </div>
                @endforelse
            </div>
        </div>
        <div class="card-footer small text-muted">
            SMS alerts go to leaders and approvers whenever progress is submitted or reviewed.
        </div>
    </div>

    <div class="row g-3 mb-3">
        @if($isManager)
        <div class="col-xl-5">
            <div class="card surface-card shadow-slim h-100" id="createTaskSection">
                <div class="card-header">
                    <div class="section-title mb-0">Create Task</div>
                    <small class="text-muted">Structured like the imprest create page — full form, no modal.</small>
                </div>
                <div class="card-body">
                    <form id="createTaskForm">
                        @csrf
                        <div class="form-section-title mb-2">Basics</div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Task Name *</label>
                                <input type="text" id="taskName" name="name" class="form-control" required>
                                <div class="input-hint">Clear, action-oriented title.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">Select</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->name }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select">
                                    <option value="Normal">Normal</option>
                                    <option value="High">High</option>
                                    <option value="Critical">Critical</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-section-title mb-2">Ownership & Dates</div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Team Leader *</label>
                                <select name="team_leader_id" class="form-select" required>
                                    <option value="">Select</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Timeframe</label>
                                <input type="text" name="timeframe" class="form-control" placeholder="e.g. 3 Weeks">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Budget</label>
                                <input type="number" name="budget" class="form-control" placeholder="Optional">
                            </div>
                        </div>

                        <div class="form-section-title mb-2">Details</div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control" placeholder="Objectives, outcomes, constraints"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <input type="text" name="tags" class="form-control" placeholder="comma separated">
                        </div>

                        <div class="form-section-title mb-2">Kick-off Activity (optional)</div>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label">Activity Title</label>
                                <input type="text" id="initialActivity" class="form-control" placeholder="First activity">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Assign To</label>
                                <select id="initialActivityUsers" class="form-select" multiple>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <small class="input-hint">Select team members (optional)</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="reset" class="btn btn-light">Reset</button>
                            <button type="submit" class="btn btn-primary">Save Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif

        <div class="{{ $isManager ? 'col-xl-7' : 'col-xl-8 offset-xl-2' }}">
            <div class="card surface-card shadow-slim h-100" id="progressSection">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="section-title mb-0">Report Progress with Documents</div>
                        <small class="text-muted">Attach evidence, add context, and flag blockers. SMS goes to everyone who must act.</small>
                    </div>
                    <span class="pill pill-info">Live</span>
                </div>
                <div class="card-body">
                    <form id="progressForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Activity *</label>
                                <select name="activity_id" id="progressActivitySelect" class="form-select" required>
                                    <option value="">Select activity</option>
                                    @foreach($flatActivities as $activity)
                                        <option value="{{ $activity['id'] }}">
                                            {{ $activity['name'] }} — {{ $activity['task'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Report Date</label>
                                <input type="date" name="report_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Completion Status</label>
                                <select name="completion_status" id="completionStatus" class="form-select" required>
                                    <option value="On Track">On Track</option>
                                    <option value="Ahead">Ahead</option>
                                    <option value="Behind Schedule">Behind Schedule</option>
                                    <option value="Delayed">Delayed</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Attachment (doc/photo)</label>
                                <input type="file" name="attachment" class="form-control">
                                <div class="input-hint">Upload evidence or supporting document.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Work Done *</label>
                                <textarea name="work_description" rows="3" class="form-control" placeholder="Be concise but detailed. What did you complete?" required></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Next Activities</label>
                                <textarea name="next_activities" rows="2" class="form-control" placeholder="What’s next? Who needs to act?"></textarea>
                            </div>
                            <div class="col-12" id="delayReasonWrap" style="display:none;">
                                <label class="form-label">Reason / Blockers</label>
                                <textarea name="reason_if_delayed" rows="2" class="form-control" placeholder="Why is it behind? What support is needed?"></textarea>
                            </div>
                            <div class="alert alert-info mb-0">
                                SMS alerts are dispatched to leaders, approvers, and other action owners the moment you submit.
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="reset" class="btn btn-light">Clear</button>
                            <button type="submit" class="btn btn-primary">Submit Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card surface-card shadow-slim mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <div class="section-title mb-0">Task Board</div>
                <small class="text-muted">Clean cards for quick scanning</small>
            </div>
            <span class="badge bg-light text-dark">Updated {{ now()->format('M d, Y') }}</span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @forelse($mainTasks->take(8) as $task)
                    @php
                        $totalActivities = $task->activities->count();
                        $done = $task->activities->where('status', 'Completed')->count();
                        $progress = $totalActivities > 0 ? round(($done / $totalActivities) * 100) : ($task->status === 'completed' ? 100 : 15);
                        $due = $task->end_date ? \Illuminate\Support\Carbon::parse($task->end_date)->format('M d') : 'No date';
                    @endphp
                    <div class="col-md-6 col-xl-4">
                        <div class="task-card h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="fw-semibold">{{ $task->name }}</div>
                                <span class="pill pill-secondary text-capitalize">{{ str_replace('_',' ', $task->status) }}</span>
                            </div>
                            <div class="text-muted small mb-2">{{ \Illuminate\Support\Str::limit($task->description, 110) }}</div>
                            <div class="d-flex gap-2 align-items-center mb-2">
                                <span class="pill pill-info">Priority: {{ $task->priority ?? 'Normal' }}</span>
                                <span class="pill pill-warning">Due: {{ $due }}</span>
                            </div>
                            <div class="progress mb-2" style="height:8px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $progress }}%" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted small">Leader: {{ $task->teamLeader->name ?? 'Unassigned' }}</div>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="openReportForm({{ $task->activities->first()->id ?? 'null' }})">Log Progress</button>
                                    @if($isManager)
                                        <button class="btn btn-outline-secondary" onclick="document.getElementById('taskName').value='{{ addslashes($task->name) }}'">Clone</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-4">No tasks yet. Start with "Create Task".</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card surface-card shadow-slim mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="section-title mb-0">Activities & Quick Actions</div>
            <div class="d-flex gap-2">
                <input type="text" id="activitySearch" class="form-control form-control-sm" placeholder="Search activity or task...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="activitiesTable">
                <thead class="table-light">
                    <tr>
                        <th>Activity</th>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Due</th>
                        <th>Assignees</th>
                        <th>Reports</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mainTasks as $task)
                        @foreach($task->activities as $activity)
                            @php
                                $activityReports = $activity->reports ?? collect();
                                $latestReport = $activityReports->sortByDesc('created_at')->first();
                            @endphp
                            <tr>
                                <td class="fw-semibold">{{ $activity->name }}</td>
                                <td>{{ $task->name }}</td>
                                <td><span class="pill pill-secondary">{{ $activity->status ?? 'Not Started' }}</span></td>
                                <td>{{ $activity->end_date ? \Illuminate\Support\Carbon::parse($activity->end_date)->format('M d, Y') : '—' }}</td>
                                <td>
                                    @foreach($activity->assignedUsers ?? [] as $assignee)
                                        <span class="avatar-chip">{{ $assignee->name }}</span>
                                    @endforeach
                                </td>
                                <td class="small text-muted">
                                    {{ $activityReports->count() }} report(s)
                                    @if($latestReport)
                                        <div>Last: {{ \Illuminate\Support\Carbon::parse($latestReport->created_at)->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" onclick="openReportForm({{ $activity->id }})">
                                        Log Progress
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @php
        $recentReports = collect($mainTasks)
            ->flatMap(function($task) {
                return $task->activities->flatMap(function($act) {
                    return $act->reports;
                });
            })
            ->sortByDesc('created_at')
            ->take(8);
    @endphp
    <div class="card surface-card shadow-slim">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="section-title mb-0">Latest Reports</div>
            <small class="text-muted">Includes attachments and blockers</small>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Activity</th>
                        <th>Task</th>
                        <th>Reporter</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Attachment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReports as $report)
                        @php
                            $taskName = $report->activity->mainTask->name ?? 'Task';
                            $activityName = $report->activity->name ?? 'Activity';
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $activityName }}</td>
                            <td>{{ $taskName }}</td>
                            <td>{{ $report->user->name ?? '—' }}</td>
                            <td><span class="pill pill-secondary">{{ $report->status }}</span></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($report->created_at)->format('M d, Y h:i A') }}</td>
                            <td>
                                @if($report->attachment_path)
                                    <a href="{{ Storage::url($report->attachment_path) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bx bx-paperclip"></i> View
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No reports yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
    const actionUrl = '{{ route('modules.tasks.action') }}';
    const csrfToken = '{{ csrf_token() }}';

    document.getElementById('completionStatus').addEventListener('change', function () {
        document.getElementById('delayReasonWrap').style.display = ['Delayed','Behind Schedule'].includes(this.value) ? 'block' : 'none';
    });

    // Quick search on activities table
    const searchInput = document.getElementById('activitySearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('#activitiesTable tbody tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    }

    function handleResponse(res) {
        if (res.success) {
            Swal.fire('Done', res.message || 'Saved', 'success').then(() => window.location.reload());
        } else {
            Swal.fire('Error', res.message || 'Something went wrong', 'error');
        }
    }

    // Create task
    const createTaskForm = document.getElementById('createTaskForm');
    if (createTaskForm) {
        createTaskForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const fd = new FormData(this);
            fd.append('action', 'task_create_main');

            const initActivity = document.getElementById('initialActivity').value.trim();
            if (initActivity) {
                fd.append('activities[0][name]', initActivity);
                const selectedUsers = Array.from(document.getElementById('initialActivityUsers').selectedOptions).map(o => o.value);
                selectedUsers.forEach((id, idx) => fd.append(`activities[0][users][${idx}]`, id));
            }

            fetch(actionUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                body: fd
            }).then(r => r.json()).then(handleResponse).catch(() => Swal.fire('Error', 'Unable to save task', 'error'));
        });
    }

    // Progress submission
    document.getElementById('progressForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('action', 'task_submit_report');

        fetch(actionUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: fd
        }).then(r => r.json()).then(handleResponse).catch(() => Swal.fire('Error', 'Unable to submit report', 'error'));
    });

    // Approve / reject
    window.approveReport = function (id) {
        Swal.fire({
            title: 'Approve report?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Approve'
        }).then(result => {
            if (!result.isConfirmed) return;
            const fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('action', 'task_approve_report');
            fd.append('report_id', id);
            fetch(actionUrl, { method: 'POST', body: fd }).then(r => r.json()).then(handleResponse);
        });
    }

    window.rejectReport = function (id) {
        Swal.fire({
            title: 'Reject report',
            input: 'textarea',
            inputPlaceholder: 'Reason',
            showCancelButton: true,
            confirmButtonText: 'Reject',
            confirmButtonColor: '#dc3545'
        }).then(result => {
            if (!result.isConfirmed || !result.value) return;
            const fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('action', 'task_reject_report');
            fd.append('report_id', id);
            fd.append('comments', result.value);
            fetch(actionUrl, { method: 'POST', body: fd }).then(r => r.json()).then(handleResponse);
        });
    }

    window.openReportForm = function (activityId) {
        if (activityId) {
            const select = document.getElementById('progressActivitySelect');
            if (select) select.value = activityId;
        }
        document.getElementById('progressForm').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
</script>
@endpush

