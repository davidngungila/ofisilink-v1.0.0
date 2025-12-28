<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_no }}</title>
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
        $documentRef = $invoice->invoice_no ?? 'INV-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'INVOICE',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    <div class="info-section">
        <div class="info-left">
            <h4>Customer Information</h4>
            <p><strong>{{ $invoice->customer->name ?? 'N/A' }}</strong></p>
            <p>{{ $invoice->customer->address ?? '' }}</p>
            <p>{{ $invoice->customer->phone ?? '' }}</p>
            <p>{{ $invoice->customer->email ?? '' }}</p>
        </div>
        <div class="info-right">
            <h4>Invoice Details</h4>
            <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d M Y') }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
            <p><strong>Reference:</strong> {{ $invoice->reference_no ?? '-' }}</p>
            <p><strong>Status:</strong> {{ $invoice->status }}</p>
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
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-right">TZS {{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->tax_rate, 2) }}%</td>
                <td class="text-right">TZS {{ number_format($item->quantity * $item->unit_price * (1 + $item->tax_rate / 100), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <table style="width: 300px; margin-left: auto;">
            <tr>
                <th>Subtotal:</th>
                <td class="text-right">TZS {{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            <tr>
                <th>Tax Amount:</th>
                <td class="text-right">TZS {{ number_format($invoice->tax_amount, 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
            <tr>
                <th>Discount:</th>
                <td class="text-right">TZS {{ number_format($invoice->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <th>Total Amount:</th>
                <td class="text-right">TZS {{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            <tr>
                <th>Paid Amount:</th>
                <td class="text-right">TZS {{ number_format($invoice->paid_amount, 2) }}</td>
            </tr>
            <tr style="background-color: #333; color: white; font-weight: bold;">
                <th>Balance:</th>
                <td class="text-right">TZS {{ number_format($invoice->balance, 2) }}</td>
            </tr>
        </table>
    </div>

    @if($invoice->notes)
    <div style="margin-top: 20px;">
        <strong>Notes:</strong>
        <p>{{ $invoice->notes }}</p>
    </div>
    @endif

    @if($invoice->terms)
    <div style="margin-top: 20px;">
        <strong>Terms & Conditions:</strong>
        <p>{{ $invoice->terms }}</p>
    </div>
    @endif
    
    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>



