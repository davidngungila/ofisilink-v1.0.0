<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11px; 
            color: #333;
        }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 14px; margin: 12px 0 6px; }
        .badge { 
            display: inline-block; 
            padding: 2px 6px; 
            border-radius: 3px; 
            color: #fff; 
            font-size: 10px; 
        }
        .success { background: #28a745; }
        .warning { background: #ffc107; color: #000; }
        .danger { background: #dc3545; }
        .info { background: #17a2b8; }
        .muted { color: #666; }
        .card { 
            border: 1px solid #ddd; 
            margin-bottom: 10px; 
            page-break-inside: avoid;
        }
        .card-header { 
            background: #f7f7f7; 
            padding: 6px 8px; 
            font-weight: bold; 
        }
        .card-body { 
            padding: 6px 8px; 
        }
        .small { font-size: 11px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #4472C4;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-EXPORT-' . now()->setTimezone($timezone)->format('YmdHis');
        $totalRecords = $attendances->count();
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'ATTENDANCE REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <!-- Summary Card -->
    <div class="card">
        <div class="card-header">Report Summary</div>
        <div class="card-body">
            <div class="small">
                <strong>Total Records:</strong> {{ $totalRecords }} | 
                <strong>Generated:</strong> {{ now()->setTimezone($timezone)->format('d M Y, H:i:s') }}
            </div>
        </div>
    </div>
    
    @if($totalRecords > 0)
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Employee</th>
                <th>Enroll ID</th>
                <th>Department</th>
                <th>Check In</th>
                <th>Check Out</th>
                <th>Total Hours</th>
                <th>Status</th>
                <th>Verify Mode</th>
                <th>Device IP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->id }}</td>
                <td>{{ $attendance->attendance_date ? $attendance->attendance_date->format('d M Y') : 'N/A' }}</td>
                <td>{{ $attendance->user->name ?? 'N/A' }}</td>
                <td><code>{{ $attendance->enroll_id ?? 'N/A' }}</code></td>
                <td>{{ $attendance->employee->department->name ?? 'N/A' }}</td>
                <td>
                    @if($attendance->check_in_time)
                        {{ $attendance->check_in_time->format('h:i A') }}
                    @elseif($attendance->time_in)
                        {{ is_string($attendance->time_in) ? \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') : $attendance->time_in->format('h:i A') }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($attendance->check_out_time)
                        {{ $attendance->check_out_time->format('h:i A') }}
                    @elseif($attendance->time_out)
                        {{ is_string($attendance->time_out) ? \Carbon\Carbon::parse($attendance->time_out)->format('h:i A') : $attendance->time_out->format('h:i A') }}
                    @else
                        -
                    @endif
                </td>
                <td>
                    @if($attendance->check_in_time && $attendance->check_out_time)
                        @php
                            $checkIn = \Carbon\Carbon::parse($attendance->check_in_time);
                            $checkOut = \Carbon\Carbon::parse($attendance->check_out_time);
                            if ($checkOut->lt($checkIn)) {
                                $checkOut->addDay();
                            }
                            $totalMinutes = $checkOut->diffInMinutes($checkIn);
                            $hours = floor($totalMinutes / 60);
                            $minutes = $totalMinutes % 60;
                        @endphp
                        {{ $hours > 0 ? $hours . 'h ' : '' }}{{ $minutes }}m
                    @elseif($attendance->total_hours)
                        @php
                            $hours = floor($attendance->total_hours / 60);
                            $minutes = $attendance->total_hours % 60;
                        @endphp
                        {{ $hours > 0 ? $hours . 'h ' : '' }}{{ $minutes }}m
                    @else
                        -
                    @endif
                </td>
                <td>
                    @php
                        $status = $attendance->status;
                        if (is_numeric($status)) {
                            $statusMap = [
                                0 => 'present',
                                1 => 'absent',
                                2 => 'late',
                                3 => 'early_leave',
                                4 => 'half_day',
                                5 => 'on_leave'
                            ];
                            $status = $statusMap[$status] ?? 'present';
                        }
                        $statusColors = [
                            'present' => 'success',
                            'absent' => 'danger',
                            'late' => 'warning',
                            'early_leave' => 'warning',
                            'half_day' => 'info',
                            'on_leave' => 'muted'
                        ];
                        $statusColor = $statusColors[$status] ?? 'muted';
                        $statusText = ucfirst(str_replace('_', ' ', $status));
                    @endphp
                    <span class="badge {{ $statusColor }}">{{ $statusText }}</span>
                </td>
                <td>{{ $attendance->verify_mode ?? 'N/A' }}</td>
                <td><code>{{ $attendance->device_ip ?? 'N/A' }}</code></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="card">
        <div class="card-body">
            <div class="small muted" style="text-align: center; padding: 20px;">
                No attendance records found for the selected filters.
            </div>
        </div>
    </div>
    @endif
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
