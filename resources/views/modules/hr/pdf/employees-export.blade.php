<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employees Export - {{ date('Y-m-d') }}</title>
    <style>
        @page {
            margin: 20mm;
            size: A4 landscape;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9pt;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #dc3545;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #dc3545;
            margin: 0;
            font-size: 18pt;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background-color: #dc3545;
            color: white;
            padding: 8px 6px;
            text-align: left;
            font-weight: bold;
            font-size: 8.5pt;
            border: 1px solid #b02a37;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 8.5pt;
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
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 8pt;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EMPLOYEES EXPORT REPORT</h1>
        <p>Generated on: {{ $exportDate }}</p>
        <p>Total Employees: {{ $totalCount }}</p>
    </div>
    
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
                <td class="text-center">
                    @php
                        $completion = 0;
                        if ($employee->employee) {
                            $filled = 0;
                            $total = 12;
                            
                            if ($employee->name) $filled++;
                            if ($employee->email) $filled++;
                            if ($employee->phone) $filled++;
                            if ($employee->employee->position) $filled++;
                            if ($employee->employee->employment_type) $filled++;
                            if ($employee->employee->salary > 0) $filled++;
                            if ($employee->primaryDepartment) $filled++;
                            if ($employee->employee->emergency_contact_name) $filled++;
                            if ($employee->family && $employee->family->count() > 0) $filled++;
                            if ($employee->nextOfKin && $employee->nextOfKin->count() > 0) $filled++;
                            if ($employee->educations && $employee->educations->count() > 0) $filled++;
                            if ($employee->bankAccounts && $employee->bankAccounts->count() > 0) $filled++;
                            
                            $completion = ($filled / $total) * 100;
                        }
                    @endphp
                    {{ number_format($completion, 1) }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>This is a system-generated report from OfisiLink HR Management System</p>
        <p>Page generated at {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>









