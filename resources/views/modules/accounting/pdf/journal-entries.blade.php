<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Journal Entries</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .status-posted {
            color: green;
            font-weight: bold;
        }
        .status-draft {
            color: orange;
        }
        .entry-details {
            margin-bottom: 15px;
            border: 1px solid #ddd;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>Journal Entries Report</h2>
        <p>Generated on: {{ $generatedAt }}</p>
        @if(!empty($filters))
        <p>
            @if(isset($filters['status']))
                Status: {{ $filters['status'] }}
            @endif
            @if(isset($filters['date_from']))
                | From: {{ $filters['date_from'] }}
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
                <th>Entry No</th>
                <th>Date</th>
                <th>Description</th>
                <th>Reference</th>
                <th>Source</th>
                <th class="text-right">Total Debits</th>
                <th class="text-right">Total Credits</th>
                <th class="text-center">Status</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
            <tr>
                <td><strong>{{ $entry->entry_no }}</strong></td>
                <td>{{ $entry->entry_date->format('d M Y') }}</td>
                <td>{{ Str::limit($entry->description, 40) }}</td>
                <td>{{ $entry->reference_no ?? '-' }}</td>
                <td>{{ $entry->source }}</td>
                <td class="text-right">TZS {{ number_format($entry->total_debits, 2) }}</td>
                <td class="text-right">TZS {{ number_format($entry->total_credits, 2) }}</td>
                <td class="text-center {{ $entry->status === 'Posted' ? 'status-posted' : 'status-draft' }}">
                    {{ $entry->status }}
                </td>
                <td>{{ $entry->creator->name ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No journal entries found</td>
            </tr>
            @endforelse
        </tbody>
        @if($entries->count() > 0)
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="5" class="text-right">Totals:</td>
                <td class="text-right">TZS {{ number_format($entries->sum('total_debits'), 2) }}</td>
                <td class="text-right">TZS {{ number_format($entries->sum('total_credits'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div style="margin-top: 20px; font-size: 8px; text-align: center; border-top: 1px solid #ddd; padding-top: 5px;">
        <p>Total Entries: {{ $entries->count() }}</p>
    </div>
</body>
</html>


