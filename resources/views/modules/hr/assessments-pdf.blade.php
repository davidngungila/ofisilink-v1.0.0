<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Report - {{ $employee->name ?? 'Employee' }} - {{ $year }}</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #333; }
        h1, h2, h3 { margin: 0 0 10px 0; }
        .section { margin-bottom: 16px; }
        .small { color: #666; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; }
        .right { text-align: right; }
        .muted { color: #777; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 11px; }
        .badge-success { background: #d1e7dd; color: #0f5132; }
        .badge-secondary { background: #e2e3e5; color: #41464b; }
    </style>
    </head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'PERF-' . ($employee->id ?? '') . '-' . $year . '-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'ANNUAL PERFORMANCE REPORT - ' . $year,
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <div class="section">
        <h2>Employee Information</h2>
        <table>
            <tr>
                <th>Name</th>
                <td>{{ $employee->name ?? 'N/A' }}</td>
                <th>Department</th>
                <td>{{ optional($employee->primaryDepartment)->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Employee ID</th>
                <td>{{ $employee->id ?? 'N/A' }}</td>
                <th>Year</th>
                <td>{{ $year }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Summary</h2>
        <table>
            <tr>
                <th>Total Performance</th>
                <td class="right"><strong>{{ number_format($total_performance ?? 0, 2) }}%</strong></td>
            </tr>
            <tr>
                <th>Responsibilities Count</th>
                <td class="right">{{ $assessments->count() }}</td>
            </tr>
        </table>
    </div>

    @foreach($performance_details as $detail)
        <div class="section">
            <h3>Main Responsibility: {{ $detail['responsibility'] }}</h3>
            <div class="small muted">Contribution: {{ $detail['contribution'] }}% | Performance: <strong>{{ number_format($detail['performance'], 2) }}%</strong></div>
            <table>
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Frequency</th>
                        <th class="right">Expected</th>
                        <th class="right">Submitted</th>
                        <th class="right">Score (%)</th>
                        <th class="right">Contribution (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detail['activities'] as $a)
                    <tr>
                        <td>{{ $a['name'] }}</td>
                        <td>{{ ucfirst($a['frequency']) }}</td>
                        <td class="right">{{ $a['expected'] }}</td>
                        <td class="right">{{ $a['submitted'] }}</td>
                        <td class="right">{{ number_format($a['score'], 2) }}</td>
                        <td class="right">{{ number_format($a['contribution'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>








