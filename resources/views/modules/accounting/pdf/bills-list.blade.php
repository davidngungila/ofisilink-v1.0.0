<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bills Report</title>
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
        $documentRef = 'BILLS-LIST-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'BILLS REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <table>
        <thead>
            <tr>
                <th>Bill No</th>
                <th>Vendor</th>
                <th>Date</th>
                <th>Due Date</th>
                <th class="text-right">Total</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bills as $bill)
            <tr>
                <td>{{ $bill->bill_no }}</td>
                <td>{{ $bill->vendor->name ?? 'N/A' }}</td>
                <td>{{ $bill->bill_date->format('d M Y') }}</td>
                <td>{{ $bill->due_date->format('d M Y') }}</td>
                <td class="text-right">TZS {{ number_format($bill->total_amount, 2) }}</td>
                <td class="text-right">TZS {{ number_format($bill->paid_amount, 2) }}</td>
                <td class="text-right">TZS {{ number_format($bill->balance, 2) }}</td>
                <td>{{ $bill->status }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No bills found</td>
            </tr>
            @endforelse
        </tbody>
        @if($bills->count() > 0)
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="4" class="text-right">Totals:</td>
                <td class="text-right">TZS {{ number_format($bills->sum('total_amount'), 2) }}</td>
                <td class="text-right">TZS {{ number_format($bills->sum('paid_amount'), 2) }}</td>
                <td class="text-right">TZS {{ number_format($bills->sum('balance'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>


