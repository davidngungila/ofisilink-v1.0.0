<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trial Balance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
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
        <h2>Trial Balance</h2>
        <p>As of: {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
        <p>Generated on: {{ $generatedAt }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th class="text-right">Debits</th>
                <th class="text-right">Credits</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($accounts as $item)
            <tr>
                <td><strong>{{ $item['account']->code }}</strong></td>
                <td>{{ $item['account']->name }}</td>
                <td class="text-right">TZS {{ number_format($item['debits'], 2) }}</td>
                <td class="text-right">TZS {{ number_format($item['credits'], 2) }}</td>
                <td class="text-right">{{ $item['balance'] >= 0 ? '' : '-' }}TZS {{ number_format(abs($item['balance']), 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-right"><strong>TOTALS:</strong></td>
                <td class="text-right"><strong>TZS {{ number_format($totalDebits, 2) }}</strong></td>
                <td class="text-right"><strong>TZS {{ number_format($totalCredits, 2) }}</strong></td>
                <td class="text-right">
                    @php $difference = abs($totalDebits - $totalCredits); @endphp
                    <strong>{{ $difference < 0.01 ? 'BALANCED' : 'TZS ' . number_format($difference, 2) }}</strong>
                </td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px; font-size: 9px; text-align: center; border-top: 1px solid #ddd; padding-top: 10px;">
        <p>Total Accounts: {{ $accounts->count() }}</p>
        @if(abs($totalDebits - $totalCredits) < 0.01)
        <p style="color: green; font-weight: bold;">✓ Trial Balance is Balanced</p>
        @else
        <p style="color: red; font-weight: bold;">✗ Trial Balance Difference: TZS {{ number_format(abs($totalDebits - $totalCredits), 2) }}</p>
        @endif
    </div>
</body>
</html>


