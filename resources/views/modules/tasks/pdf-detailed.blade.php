<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tasks Detailed Report</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 9pt; color: #333; line-height: 1.4; }
        h1, h2, h3 { color: #500000; margin: 0 0 10px 0; font-weight: bold; }
        h1 { font-size: 20pt; border-bottom: 2px solid #940000; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        h2 { font-size: 14pt; background-color: #fceeee; padding: 10px; margin-top: 20px; border-left: 4px solid #940000; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 6px; text-align: left; vertical-align: top; border: 1px solid #ddd; }
        th { background-color: #f9f9f9; font-weight: bold; font-size: 8pt; }
        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 8px; color: white; font-weight: bold; font-size: 8pt; }
        .task-section { page-break-inside: avoid; margin-bottom: 20px; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'TASKS-DETAILED-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'TASKS DETAILED REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Tasks Detailed Report</h1>
        
        @forelse($mainTasks as $task)
        <div class="task-section">
            <h2>{{ $task->name }}</h2>
            <table>
                <tr>
                    <th style="width: 20%;">Description</th>
                    <td>{!! nl2br(e($task->description ?? 'N/A')) !!}</td>
                </tr>
                <tr>
                    <th>Category</th>
                    <td>{{ $task->category ?: 'Uncategorized' }}</td>
                </tr>
                <tr>
                    <th>Team Leader</th>
                    <td>{{ $task->teamLeader->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        <span class="status-badge" style="background-color: {{ match($task->status) {
                            'completed' => '#28a745',
                            'in_progress' => '#17a2b8',
                            'delayed' => '#dc3545',
                            'planning' => '#ffc107',
                            default => '#6c757d'
                        } }};">
                            {{ ucwords(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Priority</th>
                    <td>{{ $task->priority ?? 'Normal' }}</td>
                </tr>
                <tr>
                    <th>Progress</th>
                    <td>{{ $task->progress_percentage ?? 0 }}%</td>
                </tr>
                <tr>
                    <th>Duration</th>
                    <td>
                        @if($task->start_date && $task->end_date)
                            {{ $task->start_date->format('d M Y') }} to {{ $task->end_date->format('d M Y') }}
                            @if($task->timeframe)
                                ({{ $task->timeframe }})
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @if($task->budget)
                <tr>
                    <th>Budget</th>
                    <td>{{ number_format($task->budget, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <th>Total Activities</th>
                    <td>{{ $task->activities->count() }}</td>
                </tr>
            </table>
        </div>
        @empty
        <p style="text-align: center; padding: 20px;">No tasks found</p>
        @endforelse
    </main>
</body>
</html>

