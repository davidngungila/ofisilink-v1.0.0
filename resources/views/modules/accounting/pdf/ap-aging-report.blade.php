<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>A/P Aging Report</title>
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
        .aging-current { background-color: #d4edda; }
        .aging-0-30 { background-color: #fff3cd; }
        .aging-31-60 { background-color: #ffeaa7; }
        .aging-61-90 { background-color: #fdcb6e; }
        .aging-over-90 { background-color: #fd79a8; }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'AP-AGING-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'ACCOUNTS PAYABLE AGING REPORT - As of ' . \Carbon\Carbon::parse($asOfDate)->format('d M Y'),
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <table>
        <thead>
            <tr>
                <th>Vendor</th>
                <th>Bill No</th>
                <th>Bill Date</th>
                <th>Due Date</th>
                <th>Days Overdue</th>
                <th class="text-right">Current</th>
                <th class="text-right">0-30 Days</th>
                <th class="text-right">31-60 Days</th>
                <th class="text-right">61-90 Days</th>
                <th class="text-right">Over 90 Days</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bills as $bill)
            <tr>
                <td>{{ $bill->vendor->name ?? 'N/A' }}</td>
                <td>{{ $bill->bill_no }}</td>
                <td>{{ $bill->bill_date->format('d M Y') }}</td>
                <td>{{ $bill->due_date->format('d M Y') }}</td>
                <td>{{ $bill->days_past_due }}</td>
                <td class="text-right aging-current">TZS {{ number_format($bill->aging['current'], 2) }}</td>
                <td class="text-right aging-0-30">TZS {{ number_format($bill->aging['0-30'], 2) }}</td>
                <td class="text-right aging-31-60">TZS {{ number_format($bill->aging['31-60'], 2) }}</td>
                <td class="text-right aging-61-90">TZS {{ number_format($bill->aging['61-90'], 2) }}</td>
                <td class="text-right aging-over-90">TZS {{ number_format($bill->aging['over_90'], 2) }}</td>
                <td class="text-right"><strong>TZS {{ number_format($bill->balance, 2) }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #333; color: white; font-weight: bold;">
                <td colspan="5"><strong>TOTALS:</strong></td>
                <td class="text-right">TZS {{ number_format($summary['current'], 2) }}</td>
                <td class="text-right">TZS {{ number_format($summary['0-30'], 2) }}</td>
                <td class="text-right">TZS {{ number_format($summary['31-60'], 2) }}</td>
                <td class="text-right">TZS {{ number_format($summary['61-90'], 2) }}</td>
                <td class="text-right">TZS {{ number_format($summary['over_90'], 2) }}</td>
                <td class="text-right">TZS {{ number_format($summary['total'], 2) }}</td>
            </tr>
        </tfoot>
    </table>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>


