<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>General Ledger</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            margin: 0;
            padding: 8px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
        }
        .header h1 {
            margin: 3px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 3px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .debit {
            color: #28a745;
        }
        .credit {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>General Ledger</h2>
        @if($account)
        <p>Account: {{ $account->code }} - {{ $account->name }}</p>
        @endif
        <p>Generated on: {{ $generatedAt }}</p>
        @if(!empty($filters))
        <p>
            @if(isset($filters['date_from']))
                From: {{ $filters['date_from'] }}
            @endif
            @if(isset($filters['date_to']))
                | To: {{ $filters['date_to'] }}
            @endif
        </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Account</th>
                <th>Reference</th>
                <th>Description</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Credit</th>
                <th class="text-right">Balance</th>
                <th>Source</th>
            </tr>
        </thead>
        <tbody>
            @php
                $runningBalance = $runningBalance;
            @endphp
            @forelse($entries as $entry)
            @php
                if(in_array($entry->account->type, ['Asset', 'Expense'])) {
                    $runningBalance += ($entry->type === 'Debit' ? $entry->amount : -$entry->amount);
                } else {
                    $runningBalance += ($entry->type === 'Credit' ? $entry->amount : -$entry->amount);
                }
            @endphp
            <tr>
                <td>{{ $entry->transaction_date->format('d M Y') }}</td>
                <td>{{ $entry->account->code }}<br><small>{{ $entry->account->name }}</small></td>
                <td>{{ $entry->reference_no ?? '-' }}</td>
                <td>{{ Str::limit($entry->description, 30) }}</td>
                <td class="text-right debit">
                    {{ $entry->type === 'Debit' ? 'TZS ' . number_format($entry->amount, 2) : '-' }}
                </td>
                <td class="text-right credit">
                    {{ $entry->type === 'Credit' ? 'TZS ' . number_format($entry->amount, 2) : '-' }}
                </td>
                <td class="text-right">{{ $runningBalance >= 0 ? '' : '-' }}TZS {{ number_format(abs($runningBalance), 2) }}</td>
                <td>{{ $entry->source }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No ledger entries found</td>
            </tr>
            @endforelse
        </tbody>
        @if($entries->count() > 0)
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="4" class="text-right">Totals:</td>
                <td class="text-right">TZS {{ number_format($entries->where('type', 'Debit')->sum('amount'), 2) }}</td>
                <td class="text-right">TZS {{ number_format($entries->where('type', 'Credit')->sum('amount'), 2) }}</td>
                <td class="text-right">{{ $runningBalance >= 0 ? '' : '-' }}TZS {{ number_format(abs($runningBalance), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="margin-top: 15px; font-size: 8px; text-align: center; border-top: 1px solid #ddd; padding-top: 5px;">
        <p>Total Entries: {{ $entries->count() }}</p>
    </div>
</body>
</html>


