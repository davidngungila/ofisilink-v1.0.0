<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tasks Summary Report</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        h1, h2, h3 { color: #500000; margin: 0 0 10px 0; font-weight: bold; }
        h1 { font-size: 22pt; border-bottom: 2px solid #940000; padding-bottom: 10px; margin-bottom: 25px; text-align: center; }
        h2 { font-size: 16pt; background-color: #fceeee; padding: 12px; margin-top: 25px; border-left: 4px solid #940000; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 8px; text-align: left; vertical-align: top; border: 1px solid #ddd; }
        th { background-color: #f9f9f9; font-weight: bold; }
        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 10px; color: white; font-weight: bold; font-size: 9pt; }
        .progress-container { width: 100px; background-color: #e9ecef; border-radius: 5px; height: 18px; display: inline-block; }
        .progress-bar { height: 100%; background-color: #940000; text-align: center; color: white; line-height: 18px; border-radius: 5px; font-weight: bold; font-size: 8pt; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'TASKS-SUMMARY-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'TASKS SUMMARY REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Tasks Summary Report</h1>
        
        <table>
            <thead>
                <tr>
                    <th>Task Name</th>
                    <th>Category</th>
                    <th>Team Leader</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Progress</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mainTasks as $task)
                <tr>
                    <td><strong>{{ $task->name }}</strong></td>
                    <td>{{ $task->category ?: 'Uncategorized' }}</td>
                    <td>{{ $task->teamLeader->name ?? 'N/A' }}</td>
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
                    <td>{{ $task->priority ?? 'Normal' }}</td>
                    <td>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: {{ $task->progress_percentage ?? 0 }}%;">
                                {{ $task->progress_percentage ?? 0 }}%
                            </div>
                        </div>
                    </td>
                    <td>{{ $task->start_date ? $task->start_date->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $task->end_date ? $task->end_date->format('d M Y') : 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">No tasks found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>

