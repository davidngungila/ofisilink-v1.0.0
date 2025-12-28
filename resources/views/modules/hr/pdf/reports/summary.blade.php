<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Summary Report - {{ date('Y-m-d') }}</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
            size: A4 landscape;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
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
            margin-bottom: 15px;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
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
            font-size: 18px;
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
        .text-right {
            text-align: right;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
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
        .badge-danger {
            background-color: #dc3545;
        }
        .card {
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .card-header {
            background: #f7f7f7;
            padding: 8px 10px;
            font-weight: bold;
            color: #940000;
        }
        .card-body {
            padding: 10px;
        }
        .small {
            font-size: 10px;
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
        $documentRef = 'EMP-SUMMARY-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'EMPLOYEE SUMMARY REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])
    
    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="label">Total Employees</div>
                <div class="value">{{ $summary['total'] ?? 0 }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Active</div>
                <div class="value" style="color: #28a745;">{{ $summary['active'] ?? 0 }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Inactive</div>
                <div class="value" style="color: #dc3545;">{{ $summary['inactive'] ?? 0 }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Total Salary</div>
                <div class="value">{{ number_format($summary['totalSalary'] ?? 0, 0) }} TZS</div>
            </div>
        </div>
        <div style="margin-top: 10px; font-size: 10px; color: #666;">
            <strong>Average Salary:</strong> {{ number_format($summary['averageSalary'] ?? 0, 0) }} TZS
        </div>
    </div>
    
    <h2 style="margin-top: 20px; margin-bottom: 10px;">Employee Listing</h2>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Department</th>
                <th>Position</th>
                <th class="text-center">Status</th>
                <th class="text-right">Salary</th>
                <th class="text-center">Completion</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $employee)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                <td>{{ $employee->name ?? 'N/A' }}</td>
                <td>{{ $employee->email ?? 'N/A' }}</td>
                <td>{{ $employee->primaryDepartment->name ?? 'N/A' }}</td>
                <td>{{ $employee->employee->position ?? 'N/A' }}</td>
                <td class="text-center">
                    <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-right">{{ number_format($employee->employee->salary ?? 0, 0) }} TZS</td>
                <td class="text-center">{{ number_format($employee->completion_percentage ?? 0, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>

