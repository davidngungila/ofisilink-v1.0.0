<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Payment - {{ $payment->payment_no }}</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
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
        $documentRef = $payment->payment_no ?? 'PAYMENT-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'INVOICE PAYMENT RECEIPT',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <table>
        <tr>
            <th>Invoice Number:</th>
            <td>{{ $payment->invoice->invoice_no ?? 'N/A' }}</td>
            <th>Payment Date:</th>
            <td>{{ $payment->payment_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Customer:</th>
            <td>{{ $payment->invoice->customer->name ?? 'N/A' }}</td>
            <th>Payment Method:</th>
            <td>{{ $payment->payment_method }}</td>
        </tr>
        <tr>
            <th>Payment Amount:</th>
            <td class="text-right" style="font-size: 16px; font-weight: bold; color: green;">
                TZS {{ number_format($payment->amount, 2) }}
            </td>
            <th>Reference No:</th>
            <td>{{ $payment->reference_no ?? '-' }}</td>
        </tr>
        <tr>
            <th>Invoice Total:</th>
            <td class="text-right">TZS {{ number_format($payment->invoice->total_amount ?? 0, 2) }}</td>
            <th>Invoice Balance:</th>
            <td class="text-right">TZS {{ number_format($payment->invoice->balance ?? 0, 2) }}</td>
        </tr>
        @if($payment->bankAccount)
        <tr>
            <th>Bank Account:</th>
            <td>{{ $payment->bankAccount->name }}</td>
            <th>Notes:</th>
            <td>{{ $payment->notes ?? '-' }}</td>
        </tr>
        @else
        <tr>
            <th>Notes:</th>
            <td colspan="3">{{ $payment->notes ?? '-' }}</td>
        </tr>
        @endif
    </table>

    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>



