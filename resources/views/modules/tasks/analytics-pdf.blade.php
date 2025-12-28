<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tasks Analytics Report</title>
    <style>
        @page { margin: 20px 30px 60px 30px; size: landscape; }
        body { font-family: "Helvetica", Arial, sans-serif; font-size: 9pt; color: #333; line-height: 1.4; }
        h1, h2, h3 { color: #500000; margin: 0 0 10px 0; font-weight: bold; }
        h1 { font-size: 20pt; border-bottom: 2px solid #940000; padding-bottom: 8px; margin-bottom: 20px; text-align: center; }
        h2 { font-size: 14pt; background-color: #fceeee; padding: 8px; margin-top: 15px; border-left: 4px solid #940000; }
        h3 { font-size: 11pt; margin-top: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt; }
        th, td { padding: 6px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f9f9f9; font-weight: bold; color: #500000; }
        .stats-grid { display: table; width: 100%; margin-bottom: 15px; }
        .stat-box { display: table-cell; width: 20%; padding: 10px; border: 1px solid #ddd; text-align: center; background-color: #f9f9f9; }
        .stat-box h4 { margin: 0 0 5px 0; font-size: 10pt; color: #6c757d; }
        .stat-box .value { font-size: 18pt; font-weight: bold; color: #500000; }
        .priority-badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 8pt; font-weight: bold; }
        .priority-low { background-color: #0dcaf0; color: #000; }
        .priority-normal { background-color: #6c757d; color: #fff; }
        .priority-high { background-color: #ffc107; color: #000; }
        .priority-critical { background-color: #dc3545; color: #fff; }
        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 8pt; font-weight: bold; }
        .status-planning { background-color: #ffc107; color: #000; }
        .status-in_progress { background-color: #0dcaf0; color: #000; }
        .status-completed { background-color: #198754; color: #fff; }
        .status-delayed { background-color: #dc3545; color: #fff; }
        .progress-bar { width: 100%; background-color: #e9ecef; border-radius: 3px; height: 18px; position: relative; }
        .progress-fill { height: 100%; background-color: #940000; border-radius: 3px; text-align: center; color: white; line-height: 18px; font-size: 7pt; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .mb-2 { margin-bottom: 8px; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'TASKS-ANALYTICS-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'TASKS ANALYTICS REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <main>
        <h1>Tasks Analytics Report</h1>
        
        @if($dateFrom || $dateTo || $statusFilter)
        <div style="background-color: #f9f9f9; padding: 10px; margin-bottom: 15px; border-left: 4px solid #940000;">
            <strong>Filters Applied:</strong>
            @if($dateFrom) <span>From: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}</span> @endif
            @if($dateTo) <span>To: {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</span> @endif
            @if($statusFilter) <span>Status: {{ ucwords(str_replace('_', ' ', $statusFilter)) }}</span> @endif
        </div>
        @endif

        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-box">
                <h4>Total Tasks</h4>
                <div class="value">{{ $totalTasks }}</div>
            </div>
            <div class="stat-box">
                <h4>Completed</h4>
                <div class="value" style="color: #198754;">{{ $completedTasks }}</div>
            </div>
            <div class="stat-box">
                <h4>In Progress</h4>
                <div class="value" style="color: #0dcaf0;">{{ $inProgressTasks }}</div>
            </div>
            <div class="stat-box">
                <h4>Planning</h4>
                <div class="value" style="color: #ffc107;">{{ $planningTasks }}</div>
            </div>
            <div class="stat-box">
                <h4>Delayed</h4>
                <div class="value" style="color: #dc3545;">{{ $delayedTasks }}</div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="stats-grid">
            <div class="stat-box">
                <h4>Completion Rate</h4>
                <div class="value">{{ $completionRate }}%</div>
            </div>
            <div class="stat-box">
                <h4>Average Progress</h4>
                <div class="value">{{ $avgProgress }}%</div>
            </div>
            <div class="stat-box">
                <h4>Low Priority</h4>
                <div class="value">{{ $lowPriority }}</div>
            </div>
            <div class="stat-box">
                <h4>Normal Priority</h4>
                <div class="value">{{ $normalPriority }}</div>
            </div>
            <div class="stat-box">
                <h4>High Priority</h4>
                <div class="value" style="color: #ffc107;">{{ $highPriority }}</div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-box">
                <h4>Critical Priority</h4>
                <div class="value" style="color: #dc3545;">{{ $criticalPriority }}</div>
            </div>
        </div>

        <!-- Tasks by Category -->
        @if($categoryStats->count() > 0)
        <h2>Tasks by Category</h2>
        <table>
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="text-center">Total</th>
                    <th class="text-center">Completed</th>
                    <th class="text-center">Average Progress</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoryStats as $category => $stats)
                <tr>
                    <td><strong>{{ $category ?: 'Uncategorized' }}</strong></td>
                    <td class="text-center">{{ $stats['count'] }}</td>
                    <td class="text-center">{{ $stats['completed'] }}</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $stats['avg_progress'] }}%;">{{ $stats['avg_progress'] }}%</div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Tasks by Team Leader -->
        @if($leaderStats->count() > 0)
        <h2>Tasks by Team Leader</h2>
        <table>
            <thead>
                <tr>
                    <th>Team Leader</th>
                    <th class="text-center">Total Tasks</th>
                    <th class="text-center">Completed</th>
                    <th class="text-center">Completion Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaderStats as $leaderStat)
                <tr>
                    <td><strong>{{ $leaderStat['name'] }}</strong></td>
                    <td class="text-center">{{ $leaderStat['count'] }}</td>
                    <td class="text-center">{{ $leaderStat['completed'] }}</td>
                    <td class="text-center">
                        {{ $leaderStat['count'] > 0 ? round(($leaderStat['completed'] / $leaderStat['count']) * 100) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <!-- Detailed Task List -->
        <h2>Task Details</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 20%;">Task Name</th>
                    <th style="width: 12%;">Team Leader</th>
                    <th style="width: 10%;">Priority</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;">Progress</th>
                    <th style="width: 11%;">Start Date</th>
                    <th style="width: 11%;">End Date</th>
                    <th style="width: 11%;">Category</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mainTasks as $index => $task)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $task->name }}</strong></td>
                    <td>{{ $task->teamLeader->name ?? 'Unassigned' }}</td>
                    <td>
                        <span class="priority-badge priority-{{ strtolower($task->priority ?? 'normal') }}">
                            {{ $task->priority ?? 'Normal' }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ $task->status }}">
                            {{ ucwords(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $task->progress_percentage ?? 0 }}%;">{{ $task->progress_percentage ?? 0 }}%</div>
                        </div>
                    </td>
                    <td>{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('d M Y') : 'N/A' }}</td>
                    <td>{{ $task->category ?: 'Uncategorized' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #6c757d;">No tasks found for the selected filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 8pt; color: #6c757d; text-align: center;">
            <p>Report generated on: {{ $generatedAt }}</p>
        </div>
    </main>

    @include('components.pdf-footer')
</body>
</html>






