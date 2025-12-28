<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill - {{ $bill->bill_no }}</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 15px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 20px;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-left, .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 0 10px;
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
        .total-section {
            margin-top: 20px;
            border-top: 2px solid #000;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = $bill->bill_no ?? 'BILL-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'VENDOR BILL',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <div class="info-section">
        <div class="info-left">
            <h4>Vendor Information</h4>
            <p><strong>{{ $bill->vendor->name ?? 'N/A' }}</strong></p>
            <p>{{ $bill->vendor->address ?? '' }}</p>
            <p>{{ $bill->vendor->phone ?? '' }}</p>
            <p>{{ $bill->vendor->email ?? '' }}</p>
        </div>
        <div class="info-right">
            <h4>Bill Details</h4>
            <p><strong>Bill Date:</strong> {{ $bill->bill_date->format('d M Y') }}</p>
            <p><strong>Due Date:</strong> {{ $bill->due_date->format('d M Y') }}</p>
            <p><strong>Reference:</strong> {{ $bill->reference_no ?? '-' }}</p>
            <p><strong>Status:</strong> {{ $bill->status }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Tax %</th>
                <th class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">TZS {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->tax_rate, 2) }}%</td>
                <td class="text-right">TZS {{ number_format($item->line_total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <table style="width: 300px; margin-left: auto;">
            <tr>
                <th>Subtotal:</th>
                <td class="text-right">TZS {{ number_format($bill->subtotal, 2) }}</td>
            </tr>
            <tr>
                <th>Tax Amount:</th>
                <td class="text-right">TZS {{ number_format($bill->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Discount:</th>
                <td class="text-right">TZS {{ number_format($bill->discount_amount ?? 0, 2) }}</td>
            </tr>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th>Total Amount:</th>
                <td class="text-right">TZS {{ number_format($bill->total_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Paid Amount:</th>
                <td class="text-right">TZS {{ number_format($bill->paid_amount, 2) }}</td>
            </tr>
            <tr style="background-color: #333; color: white; font-weight: bold;">
                <th>Balance:</th>
                <td class="text-right">TZS {{ number_format($bill->balance, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($bill->notes)
    <div style="margin-top: 20px;">
        <strong>Notes:</strong>
        <p>{{ $bill->notes }}</p>
    </div>
    @endif

    @if($bill->terms)
    <div style="margin-top: 20px;">
        <strong>Terms & Conditions:</strong>
        <p>{{ $bill->terms }}</p>
    </div>
    @endif
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>


