<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance Sheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 18px;
        }
        .columns {
            display: table;
            width: 100%;
        }
        .column {
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
            padding: 6px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .section-header {
            background-color: #333;
            color: white;
            font-weight: bold;
            padding: 8px;
        }
        .total-row {
            background-color: #333;
            color: white;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>Balance Sheet</h2>
        <p>As of: {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
        <p>Generated on: {{ $generatedAt }}</p>
    </div>

    <div class="columns">
        <div class="column">
            <div class="section-header">ASSETS</div>
            <table>
                <thead>
                    <tr>
                        <th>Account</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($assets as $item)
                    <tr>
                        <td>{{ $item['account']->code }} - {{ $item['account']->name }}</td>
                        <td class="text-right">TZS {{ number_format($item['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td><strong>TOTAL ASSETS</strong></td>
                        <td class="text-right"><strong>TZS {{ number_format($totalAssets, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="column">
            <div class="section-header">LIABILITIES & EQUITY</div>
            
            <table style="margin-bottom: 10px;">
                <thead>
                    <tr>
                        <th>Liability Account</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($liabilities as $item)
                    <tr>
                        <td>{{ $item['account']->code }} - {{ $item['account']->name }}</td>
                        <td class="text-right">TZS {{ number_format($item['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td><strong>Total Liabilities</strong></td>
                        <td class="text-right"><strong>TZS {{ number_format($totalLiabilities, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>

            <table>
                <thead>
                    <tr>
                        <th>Equity Account</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($equity as $item)
                    <tr>
                        <td>{{ $item['account']->code }} - {{ $item['account']->name }}</td>
                        <td class="text-right">TZS {{ number_format($item['balance'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td><strong>Total Equity</strong></td>
                        <td class="text-right"><strong>TZS {{ number_format($totalEquity, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>

            <table>
                <tfoot>
                    <tr class="total-row">
                        <td><strong>TOTAL LIABILITIES & EQUITY</strong></td>
                        <td class="text-right"><strong>TZS {{ number_format($totalLiabilities + $totalEquity, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div style="margin-top: 20px; text-align: center; border-top: 2px solid #000; padding-top: 10px;">
        @php $difference = abs($totalAssets - ($totalLiabilities + $totalEquity)); @endphp
        @if($difference < 0.01)
        <p style="color: green; font-weight: bold; font-size: 12px;">✓ Balance Sheet is Balanced</p>
        @else
        <p style="color: red; font-weight: bold; font-size: 12px;">✗ Balance Sheet Difference: TZS {{ number_format($difference, 2) }}</p>
        @endif
    </div>
</body>
</html>


