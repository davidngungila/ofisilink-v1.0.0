<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Permission Request - {{ $request['request_id'] }}</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #333; 
            font-size: 11px; 
            line-height: 1.6;
        }
        
        .content-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 15px; 
            margin-bottom: 15px;
        }
        
        .content-table th, .content-table td { 
            border: 1px solid #ccc; 
            padding: 8px; 
            text-align: left; 
        }
        
        .content-table th { 
            background-color: #f2f2f2; 
            font-weight: bold; 
            color: #555; 
            width: 25%;
        }
        
        .content-table td { 
            width: 25%;
        }
        
        .section-title { 
            background-color: {{ $main_color }}; 
            color: #fff; 
            padding: 10px; 
            font-size: 14px; 
            margin-top: 20px; 
            margin-bottom: 0px; 
            font-weight: bold;
        }
        
        .status-box { 
            padding: 12px; 
            text-align: center; 
            font-size: 16px; 
            font-weight: bold; 
            margin-bottom: 20px; 
            border-radius: 5px; 
            text-transform: capitalize; 
        }
        
        .status-approved, .status-hod_approved, .status-completed { 
            background-color: #e4f8e9; 
            color: #28a745; 
            border: 2px solid #28a745; 
        }
        
        .status-rejected, .status-return_rejected { 
            background-color: #fdecec; 
            color: #dc3545; 
            border: 2px solid #dc3545; 
        }
        
        .status-pending_hr, .status-pending_hod, .status-pending_hr_final, .status-return_pending { 
            background-color: #fff8e6; 
            color: #ffc107; 
            border: 2px solid #ffc107; 
        }
        
        .timeline { 
            border-left: 3px solid {{ $main_color }}; 
            padding-left: 20px; 
            margin-top: 10px; 
            margin-bottom: 20px;
        }
        
        .timeline-item { 
            position: relative; 
            padding-bottom: 20px; 
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-item::before { 
            content: ''; 
            position: absolute; 
            left: -27.5px; 
            top: 3px; 
            width: 12px; 
            height: 12px; 
            background-color: #fff; 
            border: 3px solid {{ $main_color }}; 
            border-radius: 50%; 
        }
        
        .timeline-item strong { 
            display: block; 
            font-size: 12px; 
            color: {{ $main_color }};
            margin-bottom: 5px;
        }
        
        .timeline-item .meta { 
            font-size: 10px; 
            color: #777; 
            margin-bottom: 8px;
        }
        
        .timeline-item .comment { 
            background-color: #f9f9f9; 
            border-left: 4px solid #ddd; 
            padding: 10px; 
            margin-top: 8px; 
            font-style: italic; 
            border-radius: 3px;
        }
        
        
        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
        }
        
        .duration-badge {
            display: inline-block;
            background-color: {{ $main_color }};
            color: #fff;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = $request['request_id'] ?? 'PERM-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'PERMISSION REQUEST FORM',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <div class="status-box status-{{ $request['status'] }}">
        Status: {{ ucwords(str_replace('_', ' ', $request['status'])) }}
    </div>

    <h3 class="section-title">Requestor Details</h3>
    <table class="content-table">
        <tr>
            <th>Staff Name</th>
            <td>{{ $request['first_name'] }}</td>
            <th>Request ID</th>
            <td>{{ $request['request_id'] }}</td>
        </tr>
        <tr>
            <th>Department</th>
            <td>{{ $request['department_name'] }}</td>
            <th>Date Submitted</th>
            <td>{{ \Carbon\Carbon::parse($request['created_at'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>{{ $request['email'] }}</td>
            <th>Phone</th>
            <td>{{ $request['phone_number'] }}</td>
        </tr>
    </table>

    <h3 class="section-title">Permission Details</h3>
    <table class="content-table">
        <tr>
            <th>From</th>
            <td>{{ \Carbon\Carbon::parse($request['start_datetime'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }}</td>
            <th>To</th>
            <td>{{ \Carbon\Carbon::parse($request['end_datetime'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }}</td>
        </tr>
        <tr>
            <th>Duration</th>
            <td colspan="3">
                <span class="duration-badge">
                    {{ $duration }} {{ str($time_mode)->plural($duration) }}
                </span>
            </td>
        </tr>
        <tr>
            <th>Reason Type</th>
            <td colspan="3">{{ ucfirst($request['reason_type']) }}</td>
        </tr>
        <tr>
            <th>Reason Description</th>
            <td colspan="3">{!! nl2br(e($request['reason_description'])) !!}</td>
        </tr>
    </table>

    <h3 class="section-title">Approval Workflow</h3>
    <div class="timeline">
        <div class="timeline-item">
            <strong>Request Submitted</strong>
            <div class="meta">
                {{ \Carbon\Carbon::parse($request['created_at'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }} 
                by {{ $request['first_name'] }}
            </div>
        </div>

        @if($request['hr_initial_reviewed'])
        <div class="timeline-item">
            <strong>HR Initial Review</strong>
            <div class="meta">
                {{ \Carbon\Carbon::parse($request['hr_initial_reviewed'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }}
                @if($request['hr_initial_reviewer_name'])
                    by {{ $request['hr_initial_reviewer_name'] }}
                @endif
            </div>
            @if(!empty($request['hr_initial_comments']))
            <div class="comment">
                <strong>Comments:</strong> {{ $request['hr_initial_comments'] }}
            </div>
            @endif
        </div>
        @endif

        @if($request['hod_reviewed'])
        <div class="timeline-item">
            <strong>HOD Review</strong>
            <div class="meta">
                {{ \Carbon\Carbon::parse($request['hod_reviewed'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }}
                @if($request['hod_reviewer_name'])
                    by {{ $request['hod_reviewer_name'] }}
                @endif
            </div>
            @if(!empty($request['hod_comments']))
            <div class="comment">
                <strong>Comments:</strong> {{ $request['hod_comments'] }}
            </div>
            @endif
        </div>
        @endif

        @if($request['hr_final_reviewed'])
        <div class="timeline-item">
            <strong>HR Final Approval</strong>
            <div class="meta">
                {{ \Carbon\Carbon::parse($request['hr_final_reviewed'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }}
                @if($request['hr_final_reviewer_name'])
                    by {{ $request['hr_final_reviewer_name'] }}
                @endif
            </div>
            @if(!empty($request['hr_final_comments']))
            <div class="comment">
                <strong>Comments:</strong> {{ $request['hr_final_comments'] }}
            </div>
            @endif
        </div>
        @endif

        @if($request['return_submitted_at'])
        <div class="timeline-item">
            <strong>Return Confirmed by Staff</strong>
            <div class="meta">
                {{ \Carbon\Carbon::parse($request['return_submitted_at'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }}
            </div>
            @if(!empty($request['return_remarks']))
            <div class="comment">
                <strong>Remarks:</strong> {{ $request['return_remarks'] }}
            </div>
            @endif
            @if($request['return_datetime'])
            <div class="meta" style="margin-top: 5px;">
                Return Time: {{ \Carbon\Carbon::parse($request['return_datetime'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }}
            </div>
            @endif
        </div>
        @endif

        @if($request['hod_return_reviewed'])
        <div class="timeline-item">
            <strong>Return Review by HOD</strong>
            <div class="meta">
                {{ \Carbon\Carbon::parse($request['hod_return_reviewed'])->setTimezone('Africa/Dar_es_Salaam')->format('M j, Y, g:i A') }}
            </div>
            @if(!empty($request['hod_return_comments']))
            <div class="comment">
                <strong>Comments:</strong> {{ $request['hod_return_comments'] }}
            </div>
            @endif
        </div>
        @endif
    </div>

    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>





