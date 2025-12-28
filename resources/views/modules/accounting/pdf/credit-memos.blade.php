<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Credit Memos Report</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
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
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'CREDIT-MEMOS-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'CREDIT MEMOS REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <table>
        <thead>
            <tr>
                <th>Memo No</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Invoice No</th>
                <th>Type</th>
                <th class="text-right">Amount</th>
                <th>Status</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse($creditMemos as $memo)
            <tr>
                <td>{{ $memo->memo_no }}</td>
                <td>{{ $memo->memo_date->format('d M Y') }}</td>
                <td>{{ $memo->customer->name ?? 'N/A' }}</td>
                <td>{{ $memo->invoice->invoice_no ?? '-' }}</td>
                <td>{{ $memo->type }}</td>
                <td class="text-right">TZS {{ number_format($memo->amount, 2) }}</td>
                <td>{{ $memo->status }}</td>
                <td>{{ $memo->reason ? (strlen($memo->reason) > 50 ? substr($memo->reason, 0, 50) . '...' : $memo->reason) : '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No credit memos found</td>
            </tr>
            @endforelse
        </tbody>
        @if($creditMemos->count() > 0)
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="5" class="text-right">Total:</td>
                <td class="text-right">TZS {{ number_format($creditMemos->sum('amount'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>

