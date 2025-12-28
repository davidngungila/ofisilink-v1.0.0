<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Imprest Requests Export - {{ date('Y-m-d') }}</title>
    <style>
        @page { margin: 20px 30px 60px 30px; }
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #333; 
            font-size: 10px; 
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #940000;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #940000;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #940000;
            color: #fff;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: capitalize;
        }
        .status-pending_hod, .status-pending_ceo, .status-pending_receipt_verification {
            background-color: #fff8e6;
            color: #ffc107;
        }
        .status-approved, .status-assigned {
            background-color: #e4f8e9;
            color: #28a745;
        }
        .status-paid {
            background-color: #e7f3ff;
            color: #007bff;
        }
        .status-completed {
            background-color: #e4f8e9;
            color: #28a745;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>IMPREST REQUESTS EXPORT</h1>
        <p>Generated on: {{ date('d M Y, H:i') }}</p>
        <p>Total Requests: {{ $imprestRequests->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Request No</th>
                <th>Purpose</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Accountant</th>
                <th>Created Date</th>
                <th>Staff Assigned</th>
                <th>Receipts</th>
            </tr>
        </thead>
        <tbody>
            @foreach($imprestRequests as $request)
            <tr>
                <td>{{ $request->request_no }}</td>
                <td>{{ $request->purpose }}</td>
                <td class="text-right">{{ number_format($request->amount, 2) }}</td>
                <td class="text-center">
                    <span class="status-badge status-{{ $request->status }}">
                        {{ ucwords(str_replace('_', ' ', $request->status)) }}
                    </span>
                </td>
                <td>{{ $request->accountant->name ?? 'N/A' }}</td>
                <td>{{ $request->created_at->format('d M Y') }}</td>
                <td class="text-center">{{ $request->assignments->count() }}</td>
                <td class="text-center">{{ $request->receipts->count() }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Total:</th>
                <th class="text-right">{{ number_format($imprestRequests->sum('amount'), 2) }}</th>
                <th colspan="5"></th>
            </tr>
        </tfoot>
    </table>
</body>
</html>



