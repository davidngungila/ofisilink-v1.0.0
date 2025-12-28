<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Flow Statement</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
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
            margin-top: 15px;
        }
        .total-row {
            background-color: #333;
            color: white;
            font-weight: bold;
        }
        .net-cash-flow {
            font-size: 14px;
            font-weight: bold;
        }
        .positive {
            color: green;
        }
        .negative {
            color: red;
        }
        .columns {
            display: table;
            width: 100%;
        }
        .column {
            display: table-cell;
            width: 33.33%;
            vertical-align: top;
            padding: 0 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>Cash Flow Statement</h2>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        <p>Generated on: {{ $generatedAt }}</p>
    </div>

    <div class="columns">
        <!-- Operating Activities -->
        <div class="column">
            <div class="section-header">OPERATING ACTIVITIES</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($operating['items'] ?? [] as $item)
                    <tr>
                        <td>
                            {{ $item['description'] ?? 'N/A' }}
                            @if(!empty($item['code']))
                                <br><small><code>{{ $item['code'] }}</code></small>
                            @endif
                        </td>
                        <td class="text-right {{ $item['amount'] >= 0 ? 'positive' : 'negative' }}">
                            {{ $item['amount'] >= 0 ? '' : '-' }}TZS {{ number_format(abs($item['amount'] ?? 0), 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center">No operating activities</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td><strong>TOTAL OPERATING</strong></td>
                        <td class="text-right"><strong>{{ $operatingCash >= 0 ? '' : '-' }}TZS {{ number_format(abs($operatingCash), 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Investing Activities -->
        <div class="column">
            <div class="section-header">INVESTING ACTIVITIES</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investing['items'] ?? [] as $item)
                    <tr>
                        <td>
                            {{ $item['description'] ?? 'N/A' }}
                            @if(!empty($item['code']))
                                <br><small><code>{{ $item['code'] }}</code></small>
                            @endif
                        </td>
                        <td class="text-right {{ $item['amount'] >= 0 ? 'positive' : 'negative' }}">
                            {{ $item['amount'] >= 0 ? '' : '-' }}TZS {{ number_format(abs($item['amount'] ?? 0), 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center">No investing activities</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td><strong>TOTAL INVESTING</strong></td>
                        <td class="text-right"><strong>{{ $investingCash >= 0 ? '' : '-' }}TZS {{ number_format(abs($investingCash), 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Financing Activities -->
        <div class="column">
            <div class="section-header">FINANCING ACTIVITIES</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($financing['items'] ?? [] as $item)
                    <tr>
                        <td>
                            {{ $item['description'] ?? 'N/A' }}
                            @if(!empty($item['code']))
                                <br><small><code>{{ $item['code'] }}</code></small>
                            @endif
                        </td>
                        <td class="text-right {{ $item['amount'] >= 0 ? 'positive' : 'negative' }}">
                            {{ $item['amount'] >= 0 ? '' : '-' }}TZS {{ number_format(abs($item['amount'] ?? 0), 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center">No financing activities</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td><strong>TOTAL FINANCING</strong></td>
                        <td class="text-right"><strong>{{ $financingCash >= 0 ? '' : '-' }}TZS {{ number_format(abs($financingCash), 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Net Cash Flow -->
    <table>
        <tfoot>
            <tr class="total-row net-cash-flow {{ $netCashFlow >= 0 ? 'positive' : 'negative' }}">
                <td><strong>NET CASH FLOW</strong></td>
                <td class="text-right"><strong>{{ $netCashFlow >= 0 ? '' : '-' }}TZS {{ number_format(abs($netCashFlow), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; font-size: 9px; text-align: center; border-top: 1px solid #ddd; padding-top: 10px;">
        <p>This report shows cash inflows and outflows from operating, investing, and financing activities for the specified period.</p>
        @if($netCashFlow >= 0)
        <p style="color: green; font-weight: bold;">✓ Positive cash flow: Cash increased during this period</p>
        @else
        <p style="color: red; font-weight: bold;">✗ Negative cash flow: Cash decreased during this period</p>
        @endif
    </div>
</body>
</html>



