<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Calendar Export - {{ $monthName }}</title>
    <style>
        @page { margin: 15px 20px 50px 20px; }
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 8pt; color: #333; line-height: 1.3; }
        h1, h2 { color: #500000; margin: 0 0 10px 0; font-weight: bold; }
        h1 { font-size: 18pt; border-bottom: 2px solid #940000; padding-bottom: 8px; margin-bottom: 15px; text-align: center; }
        h2 { font-size: 12pt; background-color: #fceeee; padding: 8px; margin-top: 15px; border-left: 4px solid #940000; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; margin-bottom: 10px; }
        .calendar-day-header { background-color: #f9f9f9; font-weight: bold; padding: 6px; text-align: center; border: 1px solid #ddd; font-size: 7pt; }
        .calendar-day-cell { border: 1px solid #ddd; padding: 4px; min-height: 60px; background-color: #fff; }
        .calendar-day-number { font-weight: bold; font-size: 9pt; margin-bottom: 4px; }
        .calendar-task { font-size: 7pt; padding: 2px 4px; margin: 2px 0; border-radius: 3px; background-color: #e7f3ff; border-left: 3px solid #007bff; }
        .calendar-task-name { font-weight: bold; }
        .calendar-task-details { font-size: 6pt; color: #666; }
        .empty-day { background-color: #f5f5f5; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 6px; text-align: left; border: 1px solid #ddd; font-size: 7pt; }
        th { background-color: #f9f9f9; font-weight: bold; }
        .status-badge { display: inline-block; padding: 2px 6px; border-radius: 4px; color: white; font-weight: bold; font-size: 6pt; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'CALENDAR-' . $monthName . '-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'TASK CALENDAR EXPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Task Calendar - {{ $monthName }}</h1>
        
        <div class="calendar-grid">
            @php
                $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                $firstDay = $monthStart->copy()->startOfMonth()->dayOfWeek;
                $daysInMonth = $monthStart->copy()->endOfMonth()->day;
                $currentDay = 1;
            @endphp
            
            @foreach($weekdays as $day)
            <div class="calendar-day-header">{{ $day }}</div>
            @endforeach
            
            @for($i = 0; $i < $firstDay; $i++)
            <div class="calendar-day-cell empty-day"></div>
            @endfor
            
            @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $currentDate = $monthStart->copy()->day($day);
                $dateKey = $currentDate->format('Y-m-d');
                $dayTasks = $tasksByDate[$dateKey] ?? [];
            @endphp
            <div class="calendar-day-cell">
                <div class="calendar-day-number">{{ $day }}</div>
                @foreach($dayTasks as $task)
                <div class="calendar-task">
                    <div class="calendar-task-name">{{ \Illuminate\Support\Str::limit($task->name, 20) }}</div>
                    <div class="calendar-task-details">
                        {{ $task->category ?: 'Uncategorized' }} | 
                        <span class="status-badge" style="background-color: {{ match($task->status) {
                            'completed' => '#28a745',
                            'in_progress' => '#17a2b8',
                            'delayed' => '#dc3545',
                            'planning' => '#ffc107',
                            default => '#6c757d'
                        } }};">
                            {{ ucwords(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endfor
        </div>
        
        <h2>Task Details</h2>
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
                    <td>{{ $task->progress_percentage ?? 0 }}%</td>
                    <td>{{ $task->start_date ? $task->start_date->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $task->end_date ? $task->end_date->format('d M Y') : 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 15px;">No tasks found for this month</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</body>
</html>

