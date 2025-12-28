@extends('layouts.app')

@section('title', 'Depreciation Management')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Depreciation Management</h4>
</div>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Calculate Depreciation</h5>
                    <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Register
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('modules.accounting.fixed-assets.calculate-depreciation') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Period <span class="text-danger">*</span></label>
                                <input type="text" name="period" class="form-control" 
                                       placeholder="2025-01 (Monthly), 2025-Q1 (Quarterly), 2025 (Yearly)" 
                                       value="{{ old('period', now()->format('Y-m')) }}" required>
                                <small class="form-text text-muted">Format: YYYY-MM (Monthly), YYYY-Q1 (Quarterly), YYYY (Yearly)</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Period Type <span class="text-danger">*</span></label>
                                <select name="period_type" class="form-control" required>
                                    <option value="Monthly" {{ old('period_type') == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="Quarterly" {{ old('period_type') == 'Quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    <option value="Yearly" {{ old('period_type') == 'Yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Select Assets (Optional - Leave empty for all active assets)</label>
                                <select name="asset_ids[]" class="form-control select2" multiple>
                                    @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}">{{ $asset->asset_code }} - {{ $asset->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-calculator"></i> Calculate
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Depreciation Entries</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="asset_id" class="form-control">
                                <option value="">All Assets</option>
                                @foreach($assets as $asset)
                                <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                    {{ $asset->asset_code }} - {{ $asset->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" name="period" class="form-control" placeholder="Period" value="{{ request('period') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="posted" class="form-control">
                                <option value="">All</option>
                                <option value="1" {{ request('posted') == '1' ? 'selected' : '' }}>Posted</option>
                                <option value="0" {{ request('posted') == '0' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-info">Filter</button>
                            <a href="{{ route('modules.accounting.fixed-assets.depreciation') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Asset</th>
                                <th>Period</th>
                                <th>Depreciation Amount</th>
                                <th>Accumulated</th>
                                <th>Net Book Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($depreciations as $dep)
                            <tr>
                                <td>{{ $dep->depreciation_date->format('d M Y') }}</td>
                                <td>
                                    <strong>{{ $dep->fixedAsset->asset_code }}</strong><br>
                                    <small>{{ $dep->fixedAsset->name }}</small>
                                </td>
                                <td>{{ $dep->period }} ({{ $dep->period_type }})</td>
                                <td>TZS {{ number_format($dep->depreciation_amount, 2) }}</td>
                                <td>TZS {{ number_format($dep->accumulated_depreciation_after, 2) }}</td>
                                <td>TZS {{ number_format($dep->net_book_value_after, 2) }}</td>
                                <td>
                                    @if($dep->is_posted)
                                    <span class="badge badge-success">Posted</span>
                                    @else
                                    <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$dep->is_posted && auth()->user()->hasAnyRole(['Accountant', 'System Admin']))
                                    <form method="POST" action="{{ route('modules.accounting.fixed-assets.post-depreciation', $dep->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Post this depreciation to journal entries?')">
                                            <i class="fas fa-check"></i> Post
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">No depreciation entries found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $depreciations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize select2 if available
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2();
    }
</script>
@endpush




