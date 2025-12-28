<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Department Report - {{ date('Y-m-d') }}</title>
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
        .dept-header {
            background-color: #e9ecef;
            font-weight: bold;
            color: #940000;
        }
        .dept-summary {
            background-color: #f8f9fa;
            font-weight: bold;
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
        $documentRef = 'EMP-DEPT-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'DEPARTMENT REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])
    
    <h2 style="margin-top: 20px; margin-bottom: 10px;">Employees by Department</h2>
    
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th class="text-center">Total Employees</th>
                <th class="text-center">Active</th>
                <th class="text-center">Inactive</th>
                <th class="text-right">Total Salary</th>
                <th class="text-right">Average Salary</th>
            </tr>
        </thead>
        <tbody>
            @foreach($byDepartment as $deptName => $deptData)
            <tr class="dept-header">
                <td colspan="6" style="background-color: #940000; color: white; font-size: 12px; padding: 12px;">
                    <strong>{{ $deptName }}</strong>
                </td>
            </tr>
            <tr class="dept-summary">
                <td style="padding-left: 30px;">Department Summary</td>
                <td class="text-center">{{ $deptData['count'] }}</td>
                <td class="text-center">{{ $deptData['active'] }}</td>
                <td class="text-center">{{ $deptData['count'] - $deptData['active'] }}</td>
                <td class="text-right">{{ number_format($deptData['totalSalary'], 0) }} TZS</td>
                <td class="text-right">{{ number_format($deptData['averageSalary'], 0) }} TZS</td>
            </tr>
            @foreach($deptData['employees'] as $emp)
            <tr>
                <td style="padding-left: 40px;">{{ $emp->name }} ({{ $emp->employee_id }})</td>
                <td class="text-center">-</td>
                <td class="text-center">{{ $emp->is_active ? 'Yes' : 'No' }}</td>
                <td class="text-center">{{ $emp->is_active ? 'No' : 'Yes' }}</td>
                <td class="text-right">{{ number_format($emp->employee->salary ?? 0, 0) }} TZS</td>
                <td class="text-right">-</td>
            </tr>
            @endforeach
            @endforeach
        </tbody>
    </table>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
