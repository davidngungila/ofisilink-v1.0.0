<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Income Statement</title>
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
        .net-income {
            font-size: 14px;
            font-weight: bold;
        }
        .profit {
            color: green;
        }
        .loss {
            color: red;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>Income Statement (Profit & Loss)</h2>
        <p>Period: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        <p>Generated on: {{ $generatedAt }}</p>
    </div>

    <div class="section-header">INCOME</div>
    <table>
        <thead>
            <tr>
                <th>Account</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($income as $item)
            <tr>
                <td>{{ $item['account']->code }} - {{ $item['account']->name }}</td>
                <td class="text-right">TZS {{ number_format($item['balance'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td><strong>TOTAL INCOME</strong></td>
                <td class="text-right"><strong>TZS {{ number_format($totalIncome, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="section-header">EXPENSES</div>
    <table>
        <thead>
            <tr>
                <th>Account</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $item)
            <tr>
                <td>{{ $item['account']->code }} - {{ $item['account']->name }}</td>
                <td class="text-right">TZS {{ number_format($item['balance'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td><strong>TOTAL EXPENSES</strong></td>
                <td class="text-right"><strong>TZS {{ number_format($totalExpenses, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <table>
        <tfoot>
            <tr class="total-row net-income {{ $netIncome >= 0 ? 'profit' : 'loss' }}">
                <td><strong>{{ $netIncome >= 0 ? 'NET PROFIT' : 'NET LOSS' }}</strong></td>
                <td class="text-right"><strong>{{ $netIncome >= 0 ? '' : '-' }}TZS {{ number_format(abs($netIncome), 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; font-size: 9px; text-align: center; border-top: 1px solid #ddd; padding-top: 10px;">
        <p>This report shows the financial performance for the specified period.</p>
    </div>
</body>
</html>


