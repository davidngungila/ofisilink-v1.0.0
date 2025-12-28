<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Ledger</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 12px; 
            color: #333; 
        }
        h2 { margin: 0 0 6px 0; }
        .muted { color: #777; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; }
        th { background: #f2f2f2; text-align: left; }
        .right { text-align:right; }
        .small { font-size: 11px; }
        .header { margin-bottom: 10px; }
        .summary { margin-top: 8px; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'GL-' . now()->setTimezone($timezone)->format('YmdHis');
        
        $periodInfo = '';
        if(!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $periodInfo = 'Period: ' . ($filters['date_from'] ?? '—') . ' to ' . ($filters['date_to'] ?? '—');
        }
        if(!empty($filters['account'])) {
            $periodInfo .= ($periodInfo ? ' | ' : '') . 'Account: ' . $filters['account'];
        }
        if(!empty($filters['q'])) {
            $periodInfo .= ($periodInfo ? ' | ' : '') . 'Search: ' . $filters['q'];
        }
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'GENERAL LEDGER REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    @if($periodInfo)
    <div class="small muted" style="margin-bottom: 15px; padding: 8px; background-color: #f9f9f9; border-left: 4px solid #940000;">
        {{ $periodInfo }}
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th style="width:12%">Date</th>
                <th>Description</th>
                <th style="width:16%">Account</th>
                <th style="width:14%">Ref</th>
                <th class="right" style="width:12%">Debit</th>
                <th class="right" style="width:12%">Credit</th>
                <th style="width:18%">Party</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $e)
                <tr>
                    <td>{{ $e->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $e->description }}</td>
                    <td>{{ $e->account->code ?? '' }} - {{ $e->account->name ?? '' }}</td>
                    <td>{{ $e->reference ?? '' }}</td>
                    <td class="right">{{ $e->debit > 0 ? number_format($e->debit, 2) : '' }}</td>
                    <td class="right">{{ $e->credit > 0 ? number_format($e->credit, 2) : '' }}</td>
                    <td>{{ $e->party_name ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(isset($summary))
    <div class="summary">
        <table style="margin-top: 15px;">
            <tr>
                <th>Total Debit</th>
                <td class="right">{{ number_format($summary['total_debit'] ?? 0, 2) }}</td>
                <th>Total Credit</th>
                <td class="right">{{ number_format($summary['total_credit'] ?? 0, 2) }}</td>
                <th>Balance</th>
                <td class="right">{{ number_format(($summary['total_debit'] ?? 0) - ($summary['total_credit'] ?? 0), 2) }}</td>
            </tr>
        </table>
    </div>
    @endif

    @include('components.pdf-footer')
</body>
</html>
