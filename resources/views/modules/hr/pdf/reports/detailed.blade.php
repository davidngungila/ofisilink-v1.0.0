<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee Detailed Report - {{ date('Y-m-d') }}</title>
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
        $documentRef = 'EMP-DETAILED-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'EMPLOYEE DETAILED REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])
    
    <h2 style="margin-top: 20px; margin-bottom: 10px;">Complete Employee Information</h2>
    
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Employee ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Department</th>
                <th>Position</th>
                <th>Employment Type</th>
                <th class="text-right">Salary</th>
                <th>Hire Date</th>
                <th class="text-center">Status</th>
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
                <td>{{ $employee->phone ?? 'N/A' }}</td>
                <td>{{ $employee->primaryDepartment->name ?? 'N/A' }}</td>
                <td>{{ $employee->employee->position ?? 'N/A' }}</td>
                <td>{{ ucfirst($employee->employee->employment_type ?? 'N/A') }}</td>
                <td class="text-right">{{ number_format($employee->employee->salary ?? 0, 0) }} TZS</td>
                <td>{{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->format('Y-m-d') : 'N/A' }}</td>
                <td class="text-center">
                    <span class="badge {{ $employee->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $employee->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="text-center">{{ number_format($employee->completion_percentage ?? 0, 1) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
