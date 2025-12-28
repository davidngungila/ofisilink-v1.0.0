<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Credit Memo - {{ $creditMemo->memo_no }}</title>
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
        $documentRef = $creditMemo->memo_no ?? 'CM-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'CREDIT MEMO',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <table>
        <tr>
            <th>Memo Number:</th>
            <td>{{ $creditMemo->memo_no ?? 'N/A' }}</td>
            <th>Memo Date:</th>
            <td>{{ $creditMemo->memo_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Customer:</th>
            <td>{{ $creditMemo->customer->name ?? 'N/A' }}</td>
            <th>Type:</th>
            <td>{{ $creditMemo->type }}</td>
        </tr>
        <tr>
            <th>Credit Amount:</th>
            <td class="text-right" style="font-size: 16px; font-weight: bold; color: green;">
                TZS {{ number_format($creditMemo->amount, 2) }}
            </td>
            <th>Status:</th>
            <td>{{ $creditMemo->status }}</td>
        </tr>
        @if($creditMemo->invoice)
        <tr>
            <th>Invoice Number:</th>
            <td>{{ $creditMemo->invoice->invoice_no ?? 'N/A' }}</td>
            <th>Invoice Date:</th>
            <td>{{ $creditMemo->invoice->invoice_date->format('d M Y') }}</td>
        </tr>
        <tr>
            <th>Invoice Total:</th>
            <td class="text-right">TZS {{ number_format($creditMemo->invoice->total_amount ?? 0, 2) }}</td>
            <th>Invoice Balance:</th>
            <td class="text-right">TZS {{ number_format($creditMemo->invoice->balance ?? 0, 2) }}</td>
        </tr>
        @endif
        @if($creditMemo->reason)
        <tr>
            <th>Reason:</th>
            <td colspan="3">{{ $creditMemo->reason }}</td>
        </tr>
        @endif
    </table>

    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>



