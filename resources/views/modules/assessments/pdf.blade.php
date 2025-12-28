<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Report</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 14px; margin: 12px 0 6px; }
        .badge { display:inline-block; padding:2px 6px; border-radius:3px; color:#fff; font-size: 10px; }
        .success { background:#28a745; }
        .warning { background:#ffc107; color:#000; }
        .danger { background:#dc3545; }
        .muted { color:#666; }
        .card { border:1px solid #ddd; margin-bottom:10px; }
        .card-header { background:#f7f7f7; padding:6px 8px; font-weight:bold; }
        .card-body { padding:6px 8px; }
        .small { font-size: 11px; }
    </style>
    </head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'PERF-ASSESS-' . $year . '-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'ANNUAL PERFORMANCE REPORT - ' . $year,
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    @foreach($responsibilities as $r)
        <div class="card">
            <div class="card-header">
                {{ $r->title }}
                <span class="badge {{ $r->status==='approved'?'success':($r->status==='pending_approval'?'warning':'muted') }}" style="float:right;">{{ $r->status }}</span>
            </div>
            <div class="card-body">
                <div class="small"><strong>Owner:</strong> {{ $r->owner->name ?? '' }} | <strong>Frequency:</strong> {{ strtoupper($r->frequency) }}</div>
                @forelse($r->subs as $s)
                    <h2>{{ $s->title }}</h2>
                    <div class="small muted">{{ $s->description }}</div>
                    @php $reports = $s->progressReports; @endphp
                    @if($reports->count())
                        <table width="100%" cellpadding="4" cellspacing="0" border="1" style="border-collapse:collapse; margin-top:6px;">
                            <thead><tr><th width="25%">Period</th><th width="15%">Status</th><th>Content</th></tr></thead>
                            <tbody>
                                @foreach($reports as $pr)
                                <tr>
                                    <td>{{ $pr->period_start }} → {{ $pr->period_end }}</td>
                                    <td>{{ $pr->status }}</td>
                                    <td>{{ $pr->content }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="small muted">No progress submitted.</div>
                    @endif
                @empty
                    <div class="small muted">No sub responsibilities.</div>
                @endforelse
            </div>
        </div>
    @endforeach
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>








