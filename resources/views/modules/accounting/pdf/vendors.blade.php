<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vendors List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
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
        .text-center {
            text-align: center;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-inactive {
            color: #999;
        }
        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    @include('components.pdf-header', [
        'documentTitle' => 'Vendors List Report',
        'documentDate' => $generatedAt
    ])

    @if(!empty($filters))
    <div style="margin-bottom: 10px; font-size: 8px; color: #666;">
        <strong>Filters Applied:</strong>
        @if(isset($filters['status']))
            Status: {{ $filters['status'] }}
        @endif
        @if(isset($filters['currency']))
            | Currency: {{ $filters['currency'] }}
        @endif
        @if(isset($filters['outstanding']))
            | Outstanding: {{ $filters['outstanding'] }}
        @endif
        @if(isset($filters['payment_terms']))
            | Payment Terms: {{ $filters['payment_terms'] }}
        @endif
        @if(isset($filters['q']) && $filters['q'])
            | Search: {{ $filters['q'] }}
        @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Contact Person</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Currency</th>
                <th class="text-right">Credit Limit</th>
                <th>Payment Terms</th>
                <th class="text-right">Outstanding</th>
                <th class="text-right">Overdue</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vendors as $vendor)
            <tr>
                <td><strong>{{ $vendor->vendor_code ?? '-' }}</strong></td>
                <td>{{ $vendor->name ?? '-' }}</td>
                <td>{{ $vendor->contact_person ?? '-' }}</td>
                <td>{{ $vendor->email ?? '-' }}</td>
                <td>{{ $vendor->phone ?? $vendor->mobile ?? '-' }}</td>
                <td>{{ $vendor->currency ?? 'TZS' }}</td>
                <td class="text-right">{{ number_format($vendor->credit_limit ?? 0, 2) }}</td>
                <td>{{ ($vendor->payment_terms_days ?? 30) }} days</td>
                <td class="text-right">{{ number_format($vendor->total_outstanding ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($vendor->overdue_amount ?? 0, 2) }}</td>
                <td class="text-center">
                    <span class="{{ $vendor->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $vendor->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">No vendors found</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2; font-weight: bold;">
                <td colspan="8" class="text-right">Totals:</td>
                <td class="text-right">{{ number_format($vendors->sum('total_outstanding'), 2) }}</td>
                <td class="text-right">{{ number_format($vendors->sum('overdue_amount'), 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <strong>Summary:</strong><br>
        Total Vendors: {{ $vendors->count() }}<br>
        Active Vendors: {{ $vendors->where('is_active', true)->count() }}<br>
        Total Outstanding: TZS {{ number_format($vendors->sum('total_outstanding'), 2) }}<br>
        Total Overdue: TZS {{ number_format($vendors->sum('overdue_amount'), 2) }}
    </div>
</body>
</html>




