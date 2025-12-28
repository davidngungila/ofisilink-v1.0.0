@extends('layouts.app')

@section('title', 'Fixed Assets Register')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Fixed Assets Register</h4>
</div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assets</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_assets']) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Cost</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">TZS {{ number_format($stats['total_value'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Accumulated Depreciation</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">TZS {{ number_format($stats['total_depreciation'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-chart-line-down fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Net Book Value</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">TZS {{ number_format($stats['net_book_value'], 2) }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <h5 class="mb-0">Asset Register</h5>
                    <div class="btn-group">
                        <a href="{{ route('modules.accounting.fixed-assets.categories') }}" class="btn btn-warning" title="Manage Asset Categories">
                            Categories *
                        </a>
                        <a href="{{ route('modules.accounting.fixed-assets.scan-barcode') }}" class="btn btn-info" title="Scan Barcode">
                            Scan Barcode
                        </a>
                        <button type="button" class="btn btn-success" id="btn-bulk-generate-barcodes" title="Generate barcodes for selected assets">
                            Generate Barcodes
                        </button>
                        <button type="button" class="btn btn-info" id="btn-print-barcodes" title="Print barcodes for selected assets">
                            Print Barcodes
                        </button>
                        <a href="{{ route('modules.accounting.fixed-assets.print-barcodes') }}" class="btn btn-secondary" title="Print all asset barcodes">
                            Print All Barcodes
                        </a>
                        <a href="{{ route('modules.accounting.fixed-assets.create') }}" class="btn btn-primary">
                            Add New Asset
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Search assets..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="category_id" class="form-control">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Depreciated" {{ request('status') == 'Depreciated' ? 'selected' : '' }}>Depreciated</option>
                                <option value="Disposed" {{ request('status') == 'Disposed' ? 'selected' : '' }}>Disposed</option>
                                <option value="Under Maintenance" {{ request('status') == 'Under Maintenance' ? 'selected' : '' }}>Under Maintenance</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="department_id" class="form-control">
                                <option value="">All Departments</option>
                                @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info">
                                Filter
                            </button>
                            <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-secondary">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="assetsTable">
                        <thead>
                            <tr>
                                <th width="30">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Asset Code</th>
                                <th>Barcode</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Purchase Date</th>
                                <th>Cost</th>
                                <th>Depreciation</th>
                                <th>Net Book Value</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assets as $asset)
                            <tr>
                                <td>
                                    <input type="checkbox" class="asset-checkbox" value="{{ $asset->id }}">
                                </td>
                                <td><strong>{{ $asset->asset_code }}</strong></td>
                                <td>
                                    @if($asset->barcode_number)
                                        <div class="d-flex align-items-center">
                                            <code class="mr-2" style="font-size: 12px; font-weight: bold;">{{ $asset->barcode_number }}</code>
                                            <a href="{{ route('modules.accounting.fixed-assets.print-barcode', $asset->id) }}" class="btn btn-xs btn-outline-primary" title="Print Barcode" target="_blank">
                                                Print
                                            </a>
                                        </div>
                                    @else
                                        <button class="btn btn-xs btn-outline-success generate-barcode-btn" data-asset-id="{{ $asset->id }}" title="Generate Barcode">
                                            Generate
                                        </button>
                                    @endif
                                </td>
                                <td>{{ $asset->name }}</td>
                                <td>{{ $asset->category->name ?? 'N/A' }}</td>
                                <td>{{ $asset->purchase_date->format('d M Y') }}</td>
                                <td>TZS {{ number_format($asset->total_cost, 2) }}</td>
                                <td>TZS {{ number_format($asset->accumulated_depreciation, 2) }}</td>
                                <td><strong>TZS {{ number_format($asset->net_book_value, 2) }}</strong></td>
                                <td>
                                    <span class="badge badge-{{ $asset->status == 'Active' ? 'success' : ($asset->status == 'Disposed' ? 'danger' : 'warning') }}">
                                        {{ $asset->status }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('modules.accounting.fixed-assets.show', $asset->id) }}" class="btn btn-sm btn-info" title="View">
                                        View
                                    </a>
                                    @if(auth()->user()->hasAnyRole(['Accountant', 'System Admin']))
                                    <a href="{{ route('modules.accounting.fixed-assets.edit', $asset->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        Edit
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center">No fixed assets found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $assets->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox
    $('#selectAll').on('change', function() {
        $('.asset-checkbox').prop('checked', this.checked);
    });

    // Generate barcode for single asset
    $('.generate-barcode-btn').on('click', function() {
        const assetId = $(this).data('asset-id');
        const btn = $(this);
        
        $.ajax({
            url: `/modules/accounting/fixed-assets/${assetId}/generate-barcode`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to generate barcode'));
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Failed to generate barcode'));
            }
        });
    });

    // Bulk generate barcodes
    $('#btn-bulk-generate-barcodes').on('click', function() {
        const selectedIds = $('.asset-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one asset');
            return;
        }

        if (!confirm(`Generate barcodes for ${selectedIds.length} selected asset(s)?`)) {
            return;
        }

        $.ajax({
            url: '{{ route('modules.accounting.fixed-assets.bulk-generate-barcodes') }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({ asset_ids: selectedIds }),
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + (response.message || 'Failed to generate barcodes'));
                }
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Failed to generate barcodes'));
            }
        });
    });

    // Print selected barcodes
    $('#btn-print-barcodes').on('click', function() {
        const selectedIds = $('.asset-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one asset');
            return;
        }

        const params = new URLSearchParams();
        selectedIds.forEach(id => params.append('asset_ids[]', id));
        window.open(`{{ route('modules.accounting.fixed-assets.print-barcodes') }}?${params.toString()}`, '_blank');
    });
});
</script>
@endpush

