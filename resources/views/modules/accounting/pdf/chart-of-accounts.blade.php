<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Chart of Accounts</title>
    <style>
        @page { 
            margin: 20px 30px 60px 30px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 10px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 18px;
        }
        .header p {
            margin: 2px 0;
            font-size: 10px;
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
            font-size: 9px;
        }
        .type-header {
            background-color: #333;
            color: white;
            font-weight: bold;
            padding: 8px;
            margin-top: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $orgSettings = \App\Models\OrganizationSetting::getSettings();
        $timezone = $orgSettings->timezone ?? config('app.timezone', 'Africa/Dar_es_Salaam');
        $documentDate = now()->setTimezone($timezone)->format($orgSettings->date_format ?? 'd M Y');
        $documentRef = 'COA-' . now()->setTimezone($timezone)->format('YmdHis');
    @endphp
    
    @include('components.pdf-header', [
        'documentTitle' => 'CHART OF ACCOUNTS',
        'documentRef' => $documentRef,
        'documentDate' => $documentDate
    ])

    @foreach(['Asset', 'Liability', 'Equity', 'Income', 'Expense'] as $type)
        @if($groupedAccounts->has($type))
        <div class="type-header">{{ $type }}s</div>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Parent</th>
                    <th class="text-right">Opening Balance</th>
                    <th class="text-right">Current Balance</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupedAccounts->get($type) as $account)
                <tr>
                    <td><strong>{{ $account->code }}</strong></td>
                    <td>{{ $account->name }}</td>
                    <td>{{ $account->category ?? 'N/A' }}</td>
                    <td>{{ $account->parent->name ?? '-' }}</td>
                    <td class="text-right">TZS {{ number_format($account->opening_balance, 2) }}</td>
                    <td class="text-right">TZS {{ number_format($account->current_balance, 2) }}</td>
                    <td class="text-center">{{ $account->is_active ? 'Active' : 'Inactive' }}</td>
                </tr>
                @if($account->children->count() > 0)
                    @foreach($account->children as $child)
                    <tr style="background-color: #f9f9f9;">
                        <td style="padding-left: 20px;">{{ $child->code }}</td>
                        <td>{{ $child->name }}</td>
                        <td>{{ $child->category ?? 'N/A' }}</td>
                        <td>{{ $account->name }}</td>
                        <td class="text-right">TZS {{ number_format($child->opening_balance, 2) }}</td>
                        <td class="text-right">TZS {{ number_format($child->current_balance, 2) }}</td>
                        <td class="text-center">{{ $child->is_active ? 'Active' : 'Inactive' }}</td>
                    </tr>
                    @endforeach
                @endif
                @endforeach
            </tbody>
        </table>
        @endif
    @endforeach

    @include('components.pdf-disclaimer')
    @include('components.pdf-footer')
</body>
</html>


