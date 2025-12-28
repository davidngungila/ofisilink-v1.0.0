<tr class="account-row" data-id="{{ $account->id }}" data-parent="{{ $account->parent_id }}">
    <td><input type="checkbox" class="form-check-input account-checkbox" value="{{ $account->id }}"></td>
    <td><strong>{{ $account->code }}</strong></td>
    <td>
        @if($level > 0)
            <span class="ms-{{ $level * 3 }} text-muted">└─</span>
        @endif
        {{ $account->name }}
    </td>
    <td><span class="badge bg-info">{{ $account->category ?? 'N/A' }}</span></td>
    <td class="text-end">TZS {{ number_format($account->opening_balance, 2) }}</td>
    <td class="text-end">
        <strong class="{{ $account->current_balance > 0 ? 'balance-positive' : ($account->current_balance < 0 ? 'balance-negative' : 'balance-zero') }}">
            TZS {{ number_format($account->current_balance, 2) }}
        </strong>
    </td>
    <td>
        <span class="badge bg-{{ $account->is_active ? 'success' : 'secondary' }}">
            {{ $account->is_active ? 'Active' : 'Inactive' }}
        </span>
    </td>
    <td class="text-center">
        <div class="btn-group btn-group-sm">
            <button class="btn btn-info" onclick="viewAccount({{ $account->id }})" title="View Details">
                <i class="bx bx-show"></i>
            </button>
            <button class="btn btn-primary" onclick="viewTransactions({{ $account->id }})" title="Transactions">
                <i class="bx bx-history"></i>
            </button>
            <button class="btn btn-warning" onclick="editAccount({{ $account->id }})" title="Edit">
                <i class="bx bx-edit"></i>
            </button>
            @if($account->canBeDeleted())
            <button class="btn btn-danger" onclick="deleteAccount({{ $account->id }})" title="Delete">
                <i class="bx bx-trash"></i>
            </button>
            @endif
        </div>
    </td>
</tr>
@if($account->children && $account->children->count() > 0)
    @foreach($account->children as $child)
        @include('modules.accounting.partials.account-tree-row', ['account' => $child, 'level' => $level + 1])
    @endforeach
@endif






