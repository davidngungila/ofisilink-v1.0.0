<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Task Report - {{ $mainTask->name }}</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        h1, h2, h3, h4 { color: #500000; margin: 0 0 10px 0; font-weight: bold; }
        h1 { font-size: 22pt; border-bottom: 2px solid #940000; padding-bottom: 10px; margin-bottom: 25px; text-align: center; }
        h2 { font-size: 16pt; background-color: #fceeee; padding: 12px; margin-top: 25px; border-left: 4px solid #940000; }
        h3 { font-size: 13pt; border-bottom: 1px solid #f0d0d0; padding-bottom: 5px; margin-top: 20px; }
        h4 { font-size: 11pt; color: #6c757d; margin-top: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 8px; text-align: left; vertical-align: top; }
        .bordered-table th, .bordered-table td { border: 1px solid #ddd; }
        .bordered-table th { background-color: #f9f9f9; font-weight: bold; }
        .task-summary { background-color: #fff9f9; border: 1px solid #f0d0d0; padding: 20px; margin-bottom: 25px; border-radius: 5px; }
        .summary-table td { padding: 10px; border: 1px solid #f0d0d0; }
        .summary-table td.label { font-weight: bold; color: #500000; width: 20%; background-color: #fceeee;}
        .progress-container { width: 100%; background-color: #e9ecef; border-radius: 5px; height: 22px; }
        .progress-bar { height: 100%; background-color: #940000; text-align: center; color: white; line-height: 22px; border-radius: 5px; font-weight: bold; font-size: 9pt; }
        .report { border: 1px solid #e9ecef; padding: 12px; margin-top: 10px; page-break-inside: avoid; border-radius: 4px; background-color: #fdfdfd; }
        .report-header { font-weight: bold; color: #495057; padding-bottom: 8px; border-bottom: 1px dashed #ccc; margin-bottom: 10px; }
        .report-body p { margin: 0 0 8px 0; }
        .report-body strong { color: #500000; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 10px; color: white; font-weight: bold; font-size: 9pt; }
        .user-badge { display: inline-block; background-color: #f0e6e6; padding: 3px 8px; border-radius: 4px; margin: 2px; font-size: 9pt; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'TASK-' . $mainTask->id . '-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'TASK SUMMARY REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Task Summary Report</h1>
        
        <div class="task-summary">
            <h2 style="margin-top: 0; margin-bottom: 15px;">{{ $mainTask->name }}</h2>
            <table class="summary-table">
                <tr>
                    <td class="label">Description</td>
                    <td>{!! nl2br(e($mainTask->description)) !!}</td>
                </tr>
                <tr>
                    <td class="label">Team Leader</td>
                    <td>{{ $mainTask->teamLeader->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Duration</td>
                    <td>
                        @if($mainTask->start_date && $mainTask->end_date)
                            {{ $mainTask->start_date->format('d M, Y') }} to {{ $mainTask->end_date->format('d M, Y') }} ({{ $mainTask->timeframe ?: 'N/A' }})
                        @elseif($mainTask->start_date)
                            {{ $mainTask->start_date->format('d M, Y') }} (End date not set)
                        @else
                            Not set
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="label">Status</td>
                    <td>
                        <span class="status-badge" style="background-color: {{ match($mainTask->status) {
                            'completed' => '#28a745',
                            'in_progress' => '#17a2b8',
                            'delayed' => '#dc3545',
                            'planning' => '#ffc107',
                            default => '#6c757d'
                        } }};">
                            {{ ucwords(str_replace('_', ' ', $mainTask->status)) }}
                        </span>
                    </td>
                </tr>
            </table>
            <div style="margin-top: 20px;">
                <strong style="color: #500000;">Overall Progress:</strong>
                <div class="progress-container">
                    <div class="progress-bar" style="width: {{ $mainTask->progress_percentage }}%;">{{ $mainTask->progress_percentage }}%</div>
                </div>
                <div style="font-size: 9pt; text-align: right; color: #6c757d; margin-top: 3px;">
                    @php
                        $totalActivities = $mainTask->activities->count();
                        $completedActivities = $mainTask->activities->where('status', 'Completed')->count();
                        $totalReports = $mainTask->activities->sum(function($activity) {
                            return $activity->reports->count();
                        });
                        $approvedReports = $mainTask->activities->sum(function($activity) {
                            return $activity->reports->where('status', 'Approved')->count();
                        });
                    @endphp
                    {{ $completedActivities }} of {{ $totalActivities }} activities completed
                    @if($totalReports > 0)
                        | {{ $totalReports }} report(s) submitted ({{ $approvedReports }} approved)
                    @else
                        | 0 reports submitted
                    @endif
                </div>
            </div>
        </div>
        
        <h2>Activities Details</h2>
        
        @php
            $activityStats = [
                'total' => $mainTask->activities->count(),
                'completed' => $mainTask->activities->where('status', 'Completed')->count(),
                'in_progress' => $mainTask->activities->where('status', 'In Progress')->count(),
                'not_started' => $mainTask->activities->where('status', 'Not Started')->count(),
                'delayed' => $mainTask->activities->where('status', 'Delayed')->count(),
            ];
            $reportStats = [
                'total' => $mainTask->activities->sum(function($activity) {
                    return $activity->reports->count();
                }),
                'approved' => $mainTask->activities->sum(function($activity) {
                    return $activity->reports->where('status', 'Approved')->count();
                }),
                'pending' => $mainTask->activities->sum(function($activity) {
                    return $activity->reports->filter(function($report) {
                        return in_array($report->status, ['Pending', 'pending_approval']);
                    })->count();
                }),
                'rejected' => $mainTask->activities->sum(function($activity) {
                    return $activity->reports->where('status', 'Rejected')->count();
                }),
            ];
        @endphp
        
        <div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-left: 4px solid #940000;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <strong style="color: #500000;">Activity Statistics:</strong><br>
                        <small>Total: {{ $activityStats['total'] }} | 
                        Completed: {{ $activityStats['completed'] }} | 
                        In Progress: {{ $activityStats['in_progress'] }} | 
                        Not Started: {{ $activityStats['not_started'] }} | 
                        Delayed: {{ $activityStats['delayed'] }}</small>
                    </td>
                    <td style="width: 50%; vertical-align: top;">
                        <strong style="color: #500000;">Report Statistics:</strong><br>
                        <small>Total Reports: {{ $reportStats['total'] }} | 
                        Approved: {{ $reportStats['approved'] }} | 
                        Pending: {{ $reportStats['pending'] }} | 
                        Rejected: {{ $reportStats['rejected'] }}</small>
                    </td>
                </tr>
            </table>
        </div>

        @if($mainTask->activities->isEmpty())
        <p>No activities have been created for this task.</p>
        @else
            @foreach($mainTask->activities as $activity)
                @php
                    $activityStatusColor = match($activity->status) {
                        'Completed' => '#28a745',
                        'In Progress' => '#17a2b8',
                        'Delayed' => '#dc3545',
                        'Not Started' => '#ffc107',
                        default => '#6c757d'
                    };
                @endphp
                
                <div style="page-break-inside: avoid; margin-bottom: 25px;">
                    <table style="width:100%; margin-bottom:10px;">
                        <tr>
                            <td><h3>{{ $activity->name }}</h3></td>
                            <td style="text-align:right;">
                                <span class="status-badge" style="background-color: {{ $activityStatusColor }};">
                                    {{ $activity->status }}
                                </span>
                            </td>
                        </tr>
                    </table>
                    <p style="font-size: 9pt; color: #6c757d; margin-top:-5px; margin-bottom: 5px;">
                        <strong>Duration:</strong> 
                        @if($activity->start_date && $activity->end_date)
                            {{ $activity->start_date->format('d M, Y') }} to {{ $activity->end_date->format('d M, Y') }}
                        @elseif($activity->start_date)
                            {{ $activity->start_date->format('d M, Y') }} (End date not set)
                        @else
                            Not set
                        @endif
                        | <strong>Timeframe:</strong> {{ $activity->timeframe ?: 'N/A' }}
                    </p>

                    @if($activity->status === 'Completed' && $activity->actual_end_date && $activity->end_date)
                        @php
                            $plannedEndDay = strtotime($activity->end_date->format('Y-m-d'));
                            $actualEndDay = strtotime($activity->actual_end_date->format('Y-m-d'));
                            $diffDays = ($actualEndDay - $plannedEndDay) / 86400;
                        @endphp
                        
                        @if($diffDays <= 0)
                        <p style="font-size: 9pt; color: #28a745; margin-top: 0; margin-bottom: 10px;">
                            <strong>Performance:</strong> Completed on time{{ $diffDays < 0 ? ' (' . abs($diffDays) . ' day(s) early)' : '' }}
                        </p>
                        @else
                        <p style="font-size: 9pt; color: #dc3545; margin-top: 0; margin-bottom: 10px;">
                            <strong>Performance:</strong> Completed {{ $diffDays }} day(s) late
                        </p>
                        @endif
                    @endif
                    
                    <table class="bordered-table">
                        <tr>
                            <th style="width: 25%;">Assigned Staff:</th>
                            <td>
                                @if($activity->assignedUsers->isNotEmpty())
                                    @foreach($activity->assignedUsers as $user)
                                        <span class="user-badge">{{ $user->name }}</span>
                                    @endforeach
                                @else
                                    N/A
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if($activity->reports->isNotEmpty())
                        <h4>Submitted Reports</h4>
                        @foreach($activity->reports as $report)
                            @php
                                $reportStatusColor = match($report->status) {
                                    'Approved' => '#28a745',
                                    'Rejected' => '#dc3545',
                                    'Pending' => '#ffc107',
                                    default => '#6c757d'
                                };
                            @endphp

                            <div class="report">
                                <table style="width:100%; border:none; margin-bottom: 0;">
                                    <tr>
                                        <td class="report-header">
                                            Report by {{ $report->user->name ?? 'Unknown' }} on {{ $report->report_date ? $report->report_date->format('d M, Y') : 'N/A' }}
                                        </td>
                                        <td class="report-header" style="text-align:right;">
                                            <span class="status-badge" style="background-color: {{ $reportStatusColor }};">
                                                {{ $report->status }}
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                                <div class="report-body">
                                    <p><strong>Work Done:</strong><br>{!! nl2br(e($report->work_description)) !!}</p>
                                    <p><strong>Next Plan:</strong><br>{!! nl2br(e($report->next_activities ?: 'N/A')) !!}</p>
                                    
                                    @if($report->completion_status)
                                    <p><strong>Completion Status:</strong> {{ $report->completion_status }}</p>
                                    @endif
                                    
                                    @if($report->reason_if_delayed)
                                    <p><strong>Reason for Delay:</strong> {{ $report->reason_if_delayed }}</p>
                                    @endif
                                    
                                    @if($report->attachment_path)
                                    <p><strong>Attachment:</strong> {{ basename($report->attachment_path) }}</p>
                                    @endif
                                    
                                    @if($report->approver_comments)
                                    <p><strong>Approver Comments:</strong> {!! nl2br(e($report->approver_comments)) !!}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p style="font-style: italic; color: #6c757d;">No reports submitted for this activity.</p>
                    @endif
                </div>
            @endforeach
        @endif

        @if(!empty($allIssues) || !empty($allDelays))
        <h2>Issues & Delays</h2>
        
        @if(!empty($allDelays))
        <h3>Reported Delays</h3>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Activity</th>
                    <th style="width: 20%;">Reported By</th>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 40%;">Reason for Delay</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allDelays as $delay)
                <tr>
                    <td>{{ $delay['activity'] }}</td>
                    <td>{{ $delay['reporter'] }}</td>
                    <td>
                        @if($delay['date'])
                            @if(is_string($delay['date']))
                                {{ \Carbon\Carbon::parse($delay['date'])->format('d M, Y') }}
                            @else
                                {{ $delay['date']->format('d M, Y') }}
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{!! nl2br(e($delay['reason'])) !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if(!empty($allIssues))
        <h3>Issues & Problems</h3>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Activity</th>
                    <th style="width: 20%;">Reported By</th>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 40%;">Issue Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allIssues as $issue)
                <tr>
                    <td>{{ $issue['activity'] }}</td>
                    <td>{{ $issue['reporter'] }}</td>
                    <td>
                        @if($issue['date'])
                            @if(is_string($issue['date']))
                                {{ \Carbon\Carbon::parse($issue['date'])->format('d M, Y') }}
                            @else
                                {{ $issue['date']->format('d M, Y') }}
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td>{!! nl2br(e($issue['issue'])) !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @endif

        @if($mainTask->comments->isNotEmpty())
        <h2>Task Comments</h2>
        @foreach($mainTask->comments as $comment)
        <div class="report" style="margin-bottom: 15px;">
            <div class="report-header">
                <strong>{{ $comment->user->name ?? 'Unknown' }}</strong> 
                <span style="color: #6c757d; font-size: 9pt;">on {{ $comment->created_at ? $comment->created_at->format('d M, Y h:i A') : 'N/A' }}</span>
                @if($comment->is_internal)
                <span class="status-badge" style="background-color: #ffc107; margin-left: 10px;">Internal</span>
                @endif
            </div>
            <div class="report-body">
                {!! nl2br(e($comment->comment)) !!}
            </div>
        </div>
        @endforeach
        @endif

        @php
            $activityComments = collect();
            foreach($mainTask->activities as $activity) {
                foreach($activity->comments as $comment) {
                    $activityComments->push([
                        'activity' => $activity->name,
                        'comment' => $comment
                    ]);
                }
            }
        @endphp

        @if($activityComments->isNotEmpty())
        <h2>Activity Comments</h2>
        @foreach($activityComments as $item)
        <div class="report" style="margin-bottom: 15px;">
            <div class="report-header">
                <strong>{{ $item['comment']->user->name ?? 'Unknown' }}</strong> 
                <span style="color: #6c757d; font-size: 9pt;">on {{ $item['comment']->created_at ? $item['comment']->created_at->format('d M, Y h:i A') : 'N/A' }}</span>
                <span style="color: #500000; font-weight: bold; margin-left: 10px;">[{{ $item['activity'] }}]</span>
                @if($item['comment']->is_internal)
                <span class="status-badge" style="background-color: #ffc107; margin-left: 10px;">Internal</span>
                @endif
            </div>
            <div class="report-body">
                {!! nl2br(e($item['comment']->comment)) !!}
            </div>
        </div>
        @endforeach
        @endif

        @if($mainTask->attachments->isNotEmpty() || $mainTask->activities->flatMap->attachments->isNotEmpty())
        <h2>Attachments</h2>
        <table class="bordered-table">
            <thead>
                <tr>
                    <th style="width: 30%;">File Name</th>
                    <th style="width: 20%;">Uploaded By</th>
                    <th style="width: 20%;">Date</th>
                    <th style="width: 15%;">Type</th>
                    <th style="width: 15%;">Size</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mainTask->attachments as $attachment)
                <tr>
                    <td>{{ $attachment->file_name }}</td>
                    <td>{{ $attachment->user->name ?? 'Unknown' }}</td>
                    <td>{{ $attachment->created_at ? $attachment->created_at->format('d M, Y') : 'N/A' }}</td>
                    <td>{{ $attachment->file_type ?? 'N/A' }}</td>
                    <td>{{ $attachment->file_size ? number_format($attachment->file_size / 1024, 2) . ' KB' : 'N/A' }}</td>
                </tr>
                @endforeach
                @foreach($mainTask->activities as $activity)
                    @foreach($activity->attachments as $attachment)
                    <tr>
                        <td>{{ $attachment->file_name }} <small style="color: #6c757d;">[{{ $activity->name }}]</small></td>
                        <td>{{ $attachment->user->name ?? 'Unknown' }}</td>
                        <td>{{ $attachment->created_at ? $attachment->created_at->format('d M, Y') : 'N/A' }}</td>
                        <td>{{ $attachment->file_type ?? 'N/A' }}</td>
                        <td>{{ $attachment->file_size ? number_format($attachment->file_size / 1024, 2) . ' KB' : 'N/A' }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
        @endif

        <div style="margin-top: 40px; padding-top: 20px;">
            <table style="width: 100%; border: none;">
                <tr>
                    <td style="width: 50%; text-align: left;">
                        <strong>Task Created By:</strong><br>
                        {{ $mainTask->creator->name ?? 'N/A' }}<br>
                        {{ $mainTask->created_at ? $mainTask->created_at->format('d M, Y') : 'N/A' }}
                    </td>
                    <td style="width: 50%; text-align: right;">
                        <strong>Report Generated:</strong><br>
                        {{ now()->format('d M, Y, h:i A') }}<br>
                        OfisiLink System
                    </td>
                </tr>
            </table>
        </div>
    </main>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
