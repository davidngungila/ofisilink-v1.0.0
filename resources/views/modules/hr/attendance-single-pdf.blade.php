<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Record #{{ $attendance->id }}</title>
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
            margin: 0;
        }
        table th, table td {
            padding: 6px 8px;
            text-align: left;
            border: none;
        }
        table th {
            width: 40%;
            font-weight: bold;
            color: #555;
        }
        table td {
            color: #333;
        }
        code {
            background-color: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 10px;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'ATT-' . $attendance->id . '-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'ATTENDANCE RECORD',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <!-- Employee Information -->
    <div class="card">
        <div class="card-header">Employee Information</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Name:</th>
                    <td><strong>{{ $attendance->user->name ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th>Employee ID:</th>
                    <td>{{ $attendance->user->employee_id ?? ($attendance->user->employee->employee_id ?? 'N/A') }}</td>
                </tr>
                <tr>
                    <th>Enroll ID:</th>
                    <td><code>{{ $attendance->enroll_id ?? 'N/A' }}</code></td>
                </tr>
                <tr>
                    <th>Department:</th>
                    <td>{{ $attendance->employee->department->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Position:</th>
                    <td>{{ $attendance->employee->position ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>{{ $attendance->user->email ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Attendance Details -->
    <div class="card">
        <div class="card-header">Attendance Details</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Date:</th>
                    <td><strong>{{ $attendance->attendance_date ? $attendance->attendance_date->format('F d, Y') : 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th>Status:</th>
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
                                'on_leave' => 'secondary'
                            ];
                            $statusColor = $statusColors[$status] ?? 'secondary';
                            $statusText = ucfirst(str_replace('_', ' ', $status));
                        @endphp
                        <span class="badge {{ $statusColor }}">{{ $statusText }}</span>
                        @if($attendance->is_late)
                            <span class="badge warning">Late</span>
                        @endif
                        @if($attendance->is_early_leave)
                            <span class="badge warning">Early Leave</span>
                        @endif
                        @if($attendance->is_overtime)
                            <span class="badge info">Overtime</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Verification:</th>
                    <td>
                        @php
                            $verificationStatus = $attendance->verification_status;
                            // Auto-verify device records
                            $biometricMethods = ['biometric', 'fingerprint', 'face_recognition', 'rfid'];
                            $isDeviceRecord = $attendance->device_ip || 
                                             $attendance->attendance_device_id || 
                                             $attendance->verify_mode ||
                                             ($attendance->attendance_method && in_array(strtolower($attendance->attendance_method), $biometricMethods));
                            if ($isDeviceRecord && (!$verificationStatus || $verificationStatus === 'pending')) {
                                $verificationStatus = 'verified';
                            }
                            $verificationColors = [
                                'pending' => 'warning',
                                'verified' => 'success',
                                'rejected' => 'danger'
                            ];
                            $verificationColor = $verificationColors[$verificationStatus] ?? 'secondary';
                            $verificationText = $verificationStatus ? ucfirst($verificationStatus) : 'N/A';
                        @endphp
                        <span class="badge {{ $verificationColor }}">{{ $verificationText }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Method:</th>
                    <td>
                        @php
                            $method = $attendance->attendance_method ?? 'N/A';
                            if ($method && $method !== 'N/A') {
                                $methodMap = [
                                    'biometric' => 'Biometric',
                                    'fingerprint' => 'Fingerprint',
                                    'face_recognition' => 'Face Recognition',
                                    'rfid' => 'RFID',
                                    'manual' => 'Manual',
                                    'mobile_app' => 'Mobile App',
                                    'card_swipe' => 'Card Swipe'
                                ];
                                $method = $methodMap[strtolower($method)] ?? ucwords(str_replace('_', ' ', $method));
                            }
                        @endphp
                        {{ $method }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Time Records -->
    <div class="card">
        <div class="card-header">Time Records</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Check In:</th>
                    <td><strong>
                        @if($attendance->check_in_time)
                            {{ $attendance->check_in_time->format('h:i A') }}
                        @elseif($attendance->time_in)
                            {{ is_string($attendance->time_in) ? \Carbon\Carbon::parse($attendance->time_in)->format('h:i A') : $attendance->time_in->format('h:i A') }}
                        @else
                            N/A
                        @endif
                    </strong></td>
                </tr>
                <tr>
                    <th>Check Out:</th>
                    <td><strong>
                        @if($attendance->check_out_time)
                            {{ $attendance->check_out_time->format('h:i A') }}
                        @elseif($attendance->time_out)
                            {{ is_string($attendance->time_out) ? \Carbon\Carbon::parse($attendance->time_out)->format('h:i A') : $attendance->time_out->format('h:i A') }}
                        @else
                            Not checked out
                        @endif
                    </strong></td>
                </tr>
                <tr>
                    <th>Total Hours:</th>
                    <td><strong>
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
                            N/A
                        @endif
                    </strong></td>
                </tr>
            </table>
        </div>
    </div>
    
    <!-- Additional Information -->
    <div class="card">
        <div class="card-header">Additional Information</div>
        <div class="card-body">
            <table>
                @if($attendance->attendanceLocation || $attendance->location_name || ($attendance->latitude && $attendance->longitude))
                <tr>
                    <th>Location:</th>
                    <td>
                        @if($attendance->attendanceLocation)
                            {{ $attendance->attendanceLocation->name }}
                        @elseif($attendance->location_name)
                            {{ $attendance->location_name }}
                        @elseif($attendance->latitude && $attendance->longitude)
                            {{ $attendance->latitude }}, {{ $attendance->longitude }}
                        @endif
                    </td>
                </tr>
                @endif
                @if($attendance->attendanceDevice || $attendance->device_type || $attendance->device_id || $attendance->device_ip)
                <tr>
                    <th>Device Type:</th>
                    <td>{{ $attendance->attendanceDevice->device_type ?? $attendance->device_type ?? 'N/A' }}</td>
                </tr>
                @if($attendance->attendanceDevice && $attendance->attendanceDevice->device_id)
                <tr>
                    <th>Device ID:</th>
                    <td>{{ $attendance->attendanceDevice->device_id }}</td>
                </tr>
                @endif
                @if($attendance->device_ip)
                <tr>
                    <th>Device IP:</th>
                    <td><code>{{ $attendance->device_ip }}</code></td>
                </tr>
                @endif
                @endif
                @if($attendance->ip_address)
                <tr>
                    <th>IP Address:</th>
                    <td><code>{{ $attendance->ip_address }}</code></td>
                </tr>
                @endif
                @if($attendance->approver)
                <tr>
                    <th>Approved By:</th>
                    <td>{{ $attendance->approver->name }}@if($attendance->approved_at) (on {{ \Carbon\Carbon::parse($attendance->approved_at)->format('M d, Y') }})@endif</td>
                </tr>
                @endif
                @if($attendance->notes)
                <tr>
                    <th>Notes:</th>
                    <td>{{ $attendance->notes }}</td>
                </tr>
                @endif
                <tr>
                    <th>Created At:</th>
                    <td class="small muted">{{ $attendance->created_at->format('M d, Y \a\t h:i A') }}</td>
                </tr>
                <tr>
                    <th>Updated At:</th>
                    <td class="small muted">{{ $attendance->updated_at->format('M d, Y \a\t h:i A') }}</td>
                </tr>
            </table>
        </div>
    </div>
    
    @if($attendance->workSchedule)
    <!-- Work Schedule Details -->
    <div class="card">
        <div class="card-header">Work Schedule Details</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Schedule Name:</th>
                    <td><strong>{{ $attendance->workSchedule->name }}</strong></td>
                </tr>
                <tr>
                    <th>Code:</th>
                    <td><code>{{ $attendance->workSchedule->code }}</code></td>
                </tr>
                <tr>
                    <th>Expected Start:</th>
                    <td>{{ $attendance->workSchedule->start_time->format('h:i A') }}</td>
                </tr>
                <tr>
                    <th>Expected End:</th>
                    <td>{{ $attendance->workSchedule->end_time->format('h:i A') }}</td>
                </tr>
                <tr>
                    <th>Work Hours:</th>
                    <td>{{ $attendance->workSchedule->work_hours ?? 8 }} hours</td>
                </tr>
                <tr>
                    <th>Late Tolerance:</th>
                    <td>{{ $attendance->workSchedule->late_tolerance_minutes ?? 15 }} minutes</td>
                </tr>
                <tr>
                    <th>Early Leave Tolerance:</th>
                    <td>{{ $attendance->workSchedule->early_leave_tolerance_minutes ?? 15 }} minutes</td>
                </tr>
                <tr>
                    <th>Flexible:</th>
                    <td>{{ $attendance->workSchedule->is_flexible ? 'Yes' : 'No' }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endif
    
    @if($policy)
    <!-- Attendance Policy Details -->
    <div class="card">
        <div class="card-header">Attendance Policy Details</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Policy Name:</th>
                    <td><strong>{{ $policy->name }}</strong></td>
                </tr>
                <tr>
                    <th>Code:</th>
                    <td><code>{{ $policy->code }}</code></td>
                </tr>
                <tr>
                    <th>Require Approval for Late:</th>
                    <td>{{ $policy->require_approval_for_late ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Require Approval for Early Leave:</th>
                    <td>{{ $policy->require_approval_for_early_leave ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Max Late Minutes/Month:</th>
                    <td>{{ $policy->max_late_minutes_per_month ?? 'Unlimited' }}</td>
                </tr>
                <tr>
                    <th>Max Early Leave Minutes/Month:</th>
                    <td>{{ $policy->max_early_leave_minutes_per_month ?? 'Unlimited' }}</td>
                </tr>
                <tr>
                    <th>Auto Approve Verified:</th>
                    <td>{{ $policy->auto_approve_verified ? 'Yes' : 'No' }}</td>
                </tr>
                <tr>
                    <th>Allow Remote Attendance:</th>
                    <td>{{ $policy->allow_remote_attendance ? 'Yes' : 'No' }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endif
    
    @if($lateDetails)
    <!-- Arrival Analysis -->
    <div class="card">
        <div class="card-header">Arrival Analysis</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Expected Check-in:</th>
                    <td><strong>{{ $lateDetails['expected_time'] ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th>Actual Check-in:</th>
                    <td><strong>{{ $lateDetails['actual_time'] ?? 'N/A' }}</strong></td>
                </tr>
                @if($lateDetails['is_late'] ?? false)
                <tr>
                    <th>Late Duration:</th>
                    <td>
                        <span class="badge warning">
                            @if(isset($lateDetails['late_hours']) && $lateDetails['late_hours'] > 0)
                                {{ $lateDetails['late_hours'] }}h {{ $lateDetails['late_minutes_remainder'] }}m
                            @else
                                {{ $lateDetails['late_minutes_remainder'] ?? 0 }}m
                            @endif
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Within Tolerance:</th>
                    <td>
                        <span class="badge {{ ($lateDetails['within_tolerance'] ?? false) ? 'success' : 'danger' }}">
                            {{ ($lateDetails['within_tolerance'] ?? false) ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
                @else
                <tr>
                    <th>Status:</th>
                    <td><span class="badge success">On Time</span></td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    @endif
    
    @if($earlyLeaveDetails)
    <!-- Departure Analysis -->
    <div class="card">
        <div class="card-header">Departure Analysis</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Expected Check-out:</th>
                    <td><strong>{{ $earlyLeaveDetails['expected_time'] ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th>Actual Check-out:</th>
                    <td><strong>{{ $earlyLeaveDetails['actual_time'] ?? 'N/A' }}</strong></td>
                </tr>
                @if($earlyLeaveDetails['is_early'] ?? false)
                <tr>
                    <th>Early Leave Duration:</th>
                    <td>
                        <span class="badge warning">
                            @if(isset($earlyLeaveDetails['early_hours']) && $earlyLeaveDetails['early_hours'] > 0)
                                {{ $earlyLeaveDetails['early_hours'] }}h {{ $earlyLeaveDetails['early_minutes_remainder'] }}m
                            @else
                                {{ $earlyLeaveDetails['early_minutes_remainder'] ?? 0 }}m
                            @endif
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Within Tolerance:</th>
                    <td>
                        <span class="badge {{ ($earlyLeaveDetails['within_tolerance'] ?? false) ? 'success' : 'danger' }}">
                            {{ ($earlyLeaveDetails['within_tolerance'] ?? false) ? 'Yes' : 'No' }}
                        </span>
                    </td>
                </tr>
                @else
                <tr>
                    <th>Status:</th>
                    <td><span class="badge success">On Time</span></td>
                </tr>
                @endif
            </table>
        </div>
    </div>
    @endif
    
    @if($timeSpentDetails)
    <!-- Time Spent in Office -->
    <div class="card">
        <div class="card-header">Time Spent in Office</div>
        <div class="card-body">
            <table>
                <tr>
                    <th>Check In:</th>
                    <td><strong>{{ $timeSpentDetails['check_in'] ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th>Check Out:</th>
                    <td><strong>{{ $timeSpentDetails['check_out'] ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th>Total Time:</th>
                    <td>
                        <span class="badge info">
                            @if(isset($timeSpentDetails['total_hours']) && $timeSpentDetails['total_hours'] > 0)
                                {{ $timeSpentDetails['total_hours'] }}h {{ $timeSpentDetails['total_minutes_remainder'] }}m
                            @else
                                {{ $timeSpentDetails['total_minutes_remainder'] ?? 0 }}m
                            @endif
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Work Time:</th>
                    <td>
                        <span class="badge success">
                            @if(isset($timeSpentDetails['work_hours']) && $timeSpentDetails['work_hours'] > 0)
                                {{ $timeSpentDetails['work_hours'] }}h {{ $timeSpentDetails['work_minutes_remainder'] }}m
                            @else
                                {{ $timeSpentDetails['work_minutes_remainder'] ?? 0 }}m
                            @endif
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Expected Hours:</th>
                    <td>{{ $timeSpentDetails['expected_hours'] ?? 8 }} hours</td>
                </tr>
                <tr>
                    <th>Hours Difference:</th>
                    <td>
                        <span class="badge {{ ($timeSpentDetails['hours_difference'] ?? 0) >= 0 ? 'success' : 'danger' }}">
                            {{ ($timeSpentDetails['hours_difference'] ?? 0) >= 0 ? '+' : '' }}{{ $timeSpentDetails['hours_difference'] ?? 0 }} hours
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <span class="badge {{ ($timeSpentDetails['is_complete'] ?? false) ? 'success' : 'warning' }}">
                            {{ ($timeSpentDetails['is_complete'] ?? false) ? 'Complete' : 'In Progress' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>
