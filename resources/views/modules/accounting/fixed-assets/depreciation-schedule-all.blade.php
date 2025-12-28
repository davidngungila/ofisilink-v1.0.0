@extends('layouts.app')

@section('title', 'Depreciation Schedule - All Assets')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Depreciation Schedule - All Assets</h4>
    <ul class="db-breadcrumb-list">
        <li><a href="{{ route('modules.accounting.index') }}"><i class="fa fa-home"></i>Accounting</a></li>
        <li><a href="{{ route('modules.accounting.fixed-assets.index') }}">Fixed Assets</a></li>
        <li>Depreciation Schedule</li>
    </ul>
</div>
@endsection

@push('styles')
<style>
    .asset-summary-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
    }
    .asset-summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }
    .collapse-toggle {
        cursor: pointer;
    }
    .depreciation-progress {
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<!-- Summary Statistics -->
@php
    $totalCost = $assets->sum('total_cost');
    $totalDepreciation = $assets->sum('accumulated_depreciation');
    $totalNetValue = $assets->sum('net_book_value');
    $totalDepreciable = $assets->sum(fn($a) => $a->total_cost - $a->salvage_value);
@endphp
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assets</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $assets->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Cost</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">TZS {{ number_format($totalCost, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Depreciation</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">TZS {{ number_format($totalDepreciation, 2) }}</div>
                <small class="text-muted">{{ number_format(($totalDepreciation / max($totalDepreciable, 1)) * 100, 1) }}% depreciated</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Net Book Value</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">TZS {{ number_format($totalNetValue, 2) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt"></i> Depreciation Schedule - All Active Assets
                </h5>
                <div>
                    <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Register
                    </a>
                    <a href="{{ route('modules.accounting.fixed-assets.depreciation') }}" class="btn btn-info">
                        <i class="fas fa-calculator"></i> Calculate Depreciation
                    </a>
                    <a href="?export=pdf" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="searchAssets" placeholder="Search assets...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filterCategory">
                            <option value="">All Categories</option>
                            @foreach($assets->pluck('category')->unique('id')->filter() as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filterMethod">
                            <option value="">All Methods</option>
                            <option value="Straight Line">Straight Line</option>
                            <option value="Declining Balance">Declining Balance</option>
                            <option value="Sum of Years Digits">Sum of Years Digits</option>
                            <option value="Units of Production">Units of Production</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary btn-block" onclick="clearFilters()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assets List -->
<div class="row">
    <div class="col-12">
        @forelse($assets as $asset)
        @php
            $depreciationPercentage = $totalDepreciable > 0 
                ? (($asset->accumulated_depreciation / max($asset->total_cost - $asset->salvage_value, 1)) * 100) 
                : 0;
        @endphp
        <div class="card asset-summary-card mb-3 asset-item" 
             data-name="{{ strtolower($asset->name) }}"
             data-category="{{ $asset->category_id }}"
             data-method="{{ $asset->depreciation_method }}">
            <div class="card-header collapse-toggle" data-toggle="collapse" data-target="#asset-{{ $asset->id }}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">
                            <i class="fas fa-chevron-down mr-2"></i>
                            {{ $asset->asset_code }} - {{ $asset->name }}
                            <small class="text-muted">({{ $asset->depreciation_method }})</small>
                        </h5>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <small><strong>Total Cost:</strong> TZS {{ number_format($asset->total_cost, 2) }}</small>
                            </div>
                            <div class="col-md-3">
                                <small><strong>Accumulated:</strong> TZS {{ number_format($asset->accumulated_depreciation, 2) }}</small>
                            </div>
                            <div class="col-md-3">
                                <small><strong>Net Book Value:</strong> TZS {{ number_format($asset->net_book_value, 2) }}</small>
                            </div>
                            <div class="col-md-3">
                                <small><strong>Depreciation:</strong> {{ number_format($depreciationPercentage, 1) }}%</small>
                                <div class="depreciation-progress bg-light mt-1">
                                    <div class="bg-warning" style="width: {{ min($depreciationPercentage, 100) }}%; height: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('modules.accounting.fixed-assets.depreciation-schedule.asset', $asset->id) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View Full Schedule
                        </a>
                    </div>
                </div>
            </div>
            <div id="asset-{{ $asset->id }}" class="collapse">
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Category:</strong> {{ $asset->category->name ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong>Useful Life:</strong> {{ $asset->useful_life_years }} years
                        </div>
                        <div class="col-md-3">
                            <strong>Start Date:</strong> {{ $asset->depreciation_start_date->format('d M Y') }}
                        </div>
                        <div class="col-md-3">
                            <strong>End Date:</strong> {{ $asset->depreciation_end_date ? $asset->depreciation_end_date->format('d M Y') : 'N/A' }}
                        </div>
                    </div>
                    
                    @if($asset->depreciations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Date</th>
                                    <th class="text-right">Depreciation Amount</th>
                                    <th class="text-right">Accumulated</th>
                                    <th class="text-right">Net Book Value</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asset->depreciations->take(12) as $dep)
                                <tr>
                                    <td>{{ $dep->period }} <span class="badge badge-secondary">{{ $dep->period_type }}</span></td>
                                    <td>{{ $dep->depreciation_date->format('d M Y') }}</td>
                                    <td class="text-right">
                                        <strong class="text-warning">TZS {{ number_format($dep->depreciation_amount, 2) }}</strong>
                                    </td>
                                    <td class="text-right">TZS {{ number_format($dep->accumulated_depreciation_after, 2) }}</td>
                                    <td class="text-right">
                                        <strong class="text-success">TZS {{ number_format($dep->net_book_value_after, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($dep->is_posted)
                                        <span class="badge badge-success">Posted</span>
                                        @else
                                        <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if($asset->depreciations->count() > 12)
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <small class="text-muted">
                                            Showing last 12 entries. 
                                            <a href="{{ route('modules.accounting.fixed-assets.depreciation-schedule.asset', $asset->id) }}">View all {{ $asset->depreciations->count() }} entries</a>
                                        </small>
                                    </td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i>
                        No depreciation entries yet. 
                        <a href="{{ route('modules.accounting.fixed-assets.depreciation') }}">Calculate depreciation</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-info-circle text-muted fa-3x mb-3"></i>
                <p class="text-muted">No active assets found.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
function filterAssets() {
    const searchTerm = $('#searchAssets').val().toLowerCase();
    const categoryFilter = $('#filterCategory').val();
    const methodFilter = $('#filterMethod').val();

    $('.asset-item').each(function() {
        const name = $(this).data('name') || '';
        const category = $(this).data('category') || '';
        const method = $(this).data('method') || '';

        const matchesSearch = !searchTerm || name.includes(searchTerm);
        const matchesCategory = !categoryFilter || category == categoryFilter;
        const matchesMethod = !methodFilter || method === methodFilter;

        if (matchesSearch && matchesCategory && matchesMethod) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function clearFilters() {
    $('#searchAssets').val('');
    $('#filterCategory').val('');
    $('#filterMethod').val('');
    filterAssets();
}

$('#searchAssets').on('input', filterAssets);
$('#filterCategory, #filterMethod').on('change', filterAssets);
</script>
@endpush
