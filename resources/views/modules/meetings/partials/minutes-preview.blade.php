<div class="minutes-preview p-4">
    <div class="text-center mb-4">
        <h4>MEETING MINUTES</h4>
        <h5>{{ $meeting->title }}</h5>
    </div>

    <table class="table table-bordered mb-4">
        <tr>
            <td width="20%"><strong>Date:</strong></td>
            <td>{{ \Carbon\Carbon::parse($meeting->meeting_date)->format('l, d F Y') }}</td>
            <td width="20%"><strong>Time:</strong></td>
            <td>{{ $meeting->start_time }} - {{ $meeting->end_time }}</td>
        </tr>
        <tr>
            <td><strong>Venue:</strong></td>
            <td colspan="3">{{ $meeting->venue }}</td>
        </tr>
        <tr>
            <td><strong>Category:</strong></td>
            <td colspan="3">{{ $meeting->category_name ?? 'N/A' }}</td>
        </tr>
    </table>

    <h6 class="mt-4 mb-3"><strong>ATTENDANCE</strong></h6>
    <div class="row mb-4">
        <div class="col-md-6">
            <strong>Present:</strong>
            <ol>
                @foreach($attendees as $attendee)
                    <li>{{ $attendee->user_name ?? $attendee->name }} 
                        @if($attendee->participant_type == 'external')
                            <span class="badge bg-info">External</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

    <h6 class="mt-4 mb-3"><strong>AGENDA DISCUSSIONS</strong></h6>
    @foreach($agendas as $index => $agenda)
        <div class="mb-4 p-3 bg-light rounded">
            <h6><strong>{{ $index + 1 }}. {{ $agenda->title }}</strong></h6>
            @if($agenda->presenter_name)
                <p class="text-muted mb-2"><em>Presented by: {{ $agenda->presenter_name }}</em></p>
            @endif
            @if($agenda->discussion_notes)
                <p><strong>Discussion:</strong><br>{{ $agenda->discussion_notes }}</p>
            @endif
            @if($agenda->resolution)
                <p><strong>Resolution:</strong><br>{{ $agenda->resolution }}</p>
            @endif
        </div>
    @endforeach

    @if($actionItems->count() > 0)
        <h6 class="mt-4 mb-3"><strong>ACTION ITEMS</strong></h6>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Action</th>
                    <th>Responsible</th>
                    <th>Deadline</th>
                    <th>Priority</th>
                </tr>
            </thead>
            <tbody>
                @foreach($actionItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->responsible_name ?? 'TBD' }}</td>
                        <td>{{ $item->deadline ? \Carbon\Carbon::parse($item->deadline)->format('d M Y') : 'TBD' }}</td>
                        <td>
                            <span class="badge bg-{{ $item->priority == 'urgent' ? 'danger' : ($item->priority == 'high' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($item->priority) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($minutes && $minutes->aob)
        <h6 class="mt-4 mb-3"><strong>ANY OTHER BUSINESS</strong></h6>
        <p>{{ $minutes->aob }}</p>
    @endif

    @if($minutes && $minutes->next_meeting_date)
        <h6 class="mt-4 mb-3"><strong>NEXT MEETING</strong></h6>
        <p>
            <strong>Date:</strong> {{ \Carbon\Carbon::parse($minutes->next_meeting_date)->format('l, d F Y') }}<br>
            @if($minutes->next_meeting_time)
                <strong>Time:</strong> {{ $minutes->next_meeting_time }}<br>
            @endif
            @if($minutes->next_meeting_venue)
                <strong>Venue:</strong> {{ $minutes->next_meeting_venue }}
            @endif
        </p>
    @endif

    @if($minutes && $minutes->closing_time)
        <h6 class="mt-4 mb-3"><strong>MEETING CLOSED</strong></h6>
        <p>
            The meeting was closed at {{ $minutes->closing_time }}.
            @if($minutes->closing_remarks)
                <br>{{ $minutes->closing_remarks }}
            @endif
        </p>
    @endif

    <div class="mt-5 pt-4 border-top">
        <div class="row">
            <div class="col-md-6">
                <p>
                    <strong>Minutes Prepared By:</strong><br>
                    ________________________<br>
                    <small>Name & Signature</small>
                </p>
            </div>
            <div class="col-md-6">
                <p>
                    <strong>Approved By:</strong><br>
                    ________________________<br>
                    <small>Chairperson Signature</small>
                </p>
            </div>
        </div>
    </div>
</div>


