<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profile Completion Report - {{ date('Y-m-d') }}</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
            size: A4 landscape;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 10px; 
            color: #333;
        }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 14px; margin: 12px 0 6px; color: #940000; }
        .summary-box {
            background-color: #f8f9fa;
            border: 2px solid #940000;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            width: 20%;
            text-align: center;
            padding: 10px;
            border-right: 1px solid #dee2e6;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-item .label {
            font-size: 9px;
            color: #666;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .summary-item .value {
            font-size: 16px;
            font-weight: bold;
            color: #940000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #940000;
            color: white;
            padding: 10px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #7a0000;
        }
        td {
            padding: 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #ddd;
        }
        .progress-fill {
            height: 100%;
            background-color: #28a745;
            text-align: center;
            line-height: 20px;
            color: white;
            font-size: 9px;
            font-weight: bold;
        }
        .progress-fill.warning {
            background-color: #ffc107;
            color: #000;
        }
        .progress-fill.danger {
            background-color: #dc3545;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .badge-danger {
            background-color: #dc3545;
        }
        .small {
            font-size: 9px;
        }
        .muted {
            color: #666;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'EMP-COMPLETION-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'PROFILE COMPLETION REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])
    
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Average Completion</div>
                <div class="value">{{ number_format($completionStats['average'] ?? 0, 1) }}%</div>
            </div>
            <div class="summary-item">
                <div class="label">Complete (100%)</div>
                <div class="value" style="color: #28a745;">{{ $completionStats['complete'] ?? 0 }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Incomplete</div>
                <div class="value" style="color: #dc3545;">{{ $completionStats['incomplete'] ?? 0 }}</div>
            </div>
            <div class="summary-item">
                <div class="label">0-50%</div>
                <div class="value">{{ ($completionStats['byRange']['0-25%'] ?? 0) + ($completionStats['byRange']['25-50%'] ?? 0) }}</div>
            </div>
            <div class="summary-item">
                <div class="label">50-100%</div>
                <div class="value">{{ ($completionStats['byRange']['50-75%'] ?? 0) + ($completionStats['byRange']['75-100%'] ?? 0) + ($completionStats['byRange']['100%'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    
    <h2 style="margin-top: 20px; margin-bottom: 10px;">Employee Profile Completion Status</h2>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Position</th>
                <th>Completion %</th>
                <th>Progress</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $employee)
            @php
                $completion = $employee->completion_percentage ?? 0;
                $progressClass = $completion >= 100 ? '' : ($completion >= 75 ? 'warning' : 'danger');
                $statusClass = $completion >= 100 ? 'badge-success' : ($completion >= 75 ? 'badge-warning' : 'badge-danger');
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                <td>{{ $employee->name ?? 'N/A' }}</td>
                <td>{{ $employee->primaryDepartment->name ?? 'N/A' }}</td>
                <td>{{ $employee->employee->position ?? 'N/A' }}</td>
                <td class="text-center"><strong>{{ number_format($completion, 1) }}%</strong></td>
                <td>
                    <div class="progress-bar">
                        <div class="progress-fill {{ $progressClass }}" style="width: {{ min($completion, 100) }}%;">
                            {{ number_format($completion, 0) }}%
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge {{ $statusClass }}">
                        {{ $completion >= 100 ? 'Complete' : 'Incomplete' }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
