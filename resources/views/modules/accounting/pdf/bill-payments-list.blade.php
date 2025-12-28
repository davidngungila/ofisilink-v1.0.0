<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill Payments Report</title>
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
        $documentRef = 'BILL-PAYMENTS-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'BILL PAYMENTS REPORT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <table>
        <thead>
            <tr>
                <th>Payment No</th>
                <th>Bill No</th>
                <th>Vendor</th>
                <th>Date</th>
                <th class="text-right">Amount</th>
                <th>Method</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td>{{ $payment->payment_no }}</td>
                <td>{{ $payment->bill->bill_no ?? 'N/A' }}</td>
                <td>{{ $payment->bill->vendor->name ?? 'N/A' }}</td>
                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                <td class="text-right">TZS {{ number_format($payment->amount, 2) }}</td>
                <td>{{ $payment->payment_method }}</td>
                <td>{{ $payment->reference_no ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No payments found</td>
            </tr>
            @endforelse
        </tbody>
        @if($payments->count() > 0)
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="4" class="text-right">Total:</td>
                <td class="text-right">TZS {{ number_format($payments->sum('amount'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>


