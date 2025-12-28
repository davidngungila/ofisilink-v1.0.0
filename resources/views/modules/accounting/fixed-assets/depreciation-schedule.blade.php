@extends('layouts.app')

@section('title', 'Depreciation Schedule - ' . $asset->name)

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Depreciation Schedule</h4>
    <ul class="db-breadcrumb-list">
        <li><a href="{{ route('modules.accounting.index') }}"><i class="fa fa-home"></i>Accounting</a></li>
        <li><a href="{{ route('modules.accounting.fixed-assets.index') }}">Fixed Assets</a></li>
        <li>Depreciation Schedule</li>
    </ul>
</div>
@endsection

@push('styles')
<style>
    .summary-card {
        border-left: 4px solid;
        transition: all 0.3s ease;
    }
    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    .summary-card.cost { border-left-color: #007bff; }
    .summary-card.depreciation { border-left-color: #ffc107; }
    .summary-card.net-value { border-left-color: #28a745; }
    .summary-card.remaining { border-left-color: #17a2b8; }
    .chart-container {
        height: 300px;
        position: relative;
    }
    .depreciation-timeline {
        position: relative;
        padding: 20px 0;
    }
    .timeline-item {
        position: relative;
        padding-left: 30px;
        margin-bottom: 20px;
    }
    .timeline-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #007bff;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #007bff;
    }
    .timeline-item::after {
        content: '';
        position: absolute;
        left: 5px;
        top: 12px;
        width: 2px;
        height: calc(100% + 8px);
        background: #e0e0e0;
    }
    .timeline-item:last-child::after {
        display: none;
    }
</style>
@endpush

@section('content')
<!-- Asset Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card summary-card cost">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Cost</h6>
                <h3 class="mb-0 text-primary">TZS {{ number_format($asset->total_cost, 2) }}</h3>
                <small class="text-muted">Purchase: {{ number_format($asset->purchase_cost, 2) }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card depreciation">
            <div class="card-body">
                <h6 class="text-muted mb-2">Accumulated Depreciation</h6>
                <h3 class="mb-0 text-warning">TZS {{ number_format($asset->accumulated_depreciation, 2) }}</h3>
                <small class="text-muted">{{ number_format(($asset->accumulated_depreciation / max($asset->total_cost - $asset->salvage_value, 1)) * 100, 1) }}% of depreciable</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card net-value">
            <div class="card-body">
                <h6 class="text-muted mb-2">Net Book Value</h6>
                <h3 class="mb-0 text-success">TZS {{ number_format($asset->net_book_value, 2) }}</h3>
                <small class="text-muted">Current value</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card summary-card remaining">
            <div class="card-body">
                <h6 class="text-muted mb-2">Remaining Depreciation</h6>
                <h3 class="mb-0 text-info">TZS {{ number_format(max(0, ($asset->total_cost - $asset->salvage_value) - $asset->accumulated_depreciation), 2) }}</h3>
                <small class="text-muted">Until fully depreciated</small>
            </div>
        </div>
    </div>
</div>

<!-- Asset Details Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle"></i> Asset Information
                </h5>
                <div>
                    <a href="{{ route('modules.accounting.fixed-assets.show', $asset->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> View Asset
                    </a>
                    <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Register
                    </a>
                    <a href="?export=pdf" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Asset Code:</strong><br>
                        <code>{{ $asset->asset_code }}</code>
                    </div>
                    <div class="col-md-3">
                        <strong>Name:</strong><br>
                        {{ $asset->name }}
                    </div>
                    <div class="col-md-3">
                        <strong>Category:</strong><br>
                        {{ $asset->category->name ?? 'N/A' }}
                    </div>
                    <div class="col-md-3">
                        <strong>Status:</strong><br>
                        <span class="badge badge-{{ $asset->status == 'Active' ? 'success' : 'warning' }}">{{ $asset->status }}</span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Depreciation Method:</strong><br>
                        {{ $asset->depreciation_method }}
                    </div>
                    <div class="col-md-3">
                        <strong>Useful Life:</strong><br>
                        {{ $asset->useful_life_years }} years
                    </div>
                    <div class="col-md-3">
                        <strong>Depreciation Start:</strong><br>
                        {{ $asset->depreciation_start_date->format('d M Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Depreciation End:</strong><br>
                        {{ $asset->depreciation_end_date ? $asset->depreciation_end_date->format('d M Y') : 'N/A' }}
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-3">
                        <strong>Purchase Date:</strong><br>
                        {{ $asset->purchase_date->format('d M Y') }}
                    </div>
                    <div class="col-md-3">
                        <strong>Salvage Value:</strong><br>
                        TZS {{ number_format($asset->salvage_value, 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Depreciable Amount:</strong><br>
                        TZS {{ number_format($asset->total_cost - $asset->salvage_value, 2) }}
                    </div>
                    <div class="col-md-3">
                        <strong>Monthly Depreciation:</strong><br>
                        TZS {{ number_format(($asset->total_cost - $asset->salvage_value) / max($asset->useful_life_years * 12, 1), 2) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Depreciation Schedule Table -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt"></i> Depreciation Schedule
                    </h5>
                    <div>
                        <select class="form-control form-control-sm d-inline-block" id="filterStatus" style="width: auto;">
                            <option value="">All Status</option>
                            <option value="posted">Posted Only</option>
                            <option value="pending">Pending Only</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="depreciationTable">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Period Type</th>
                                <th>Date</th>
                                <th class="text-right">Depreciation Amount</th>
                                <th class="text-right">Accumulated Before</th>
                                <th class="text-right">Accumulated After</th>
                                <th class="text-right">Net Book Value Before</th>
                                <th class="text-right">Net Book Value After</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($depreciations as $dep)
                            <tr data-status="{{ $dep->is_posted ? 'posted' : 'pending' }}">
                                <td><strong>{{ $dep->period }}</strong></td>
                                <td>
                                    <span class="badge badge-secondary">{{ $dep->period_type }}</span>
                                </td>
                                <td>{{ $dep->depreciation_date->format('d M Y') }}</td>
                                <td class="text-right">
                                    <strong class="text-warning">TZS {{ number_format($dep->depreciation_amount, 2) }}</strong>
                                </td>
                                <td class="text-right">TZS {{ number_format($dep->accumulated_depreciation_before, 2) }}</td>
                                <td class="text-right">
                                    <strong>TZS {{ number_format($dep->accumulated_depreciation_after, 2) }}</strong>
                                </td>
                                <td class="text-right">TZS {{ number_format($dep->net_book_value_before, 2) }}</td>
                                <td class="text-right">
                                    <strong class="text-success">TZS {{ number_format($dep->net_book_value_after, 2) }}</strong>
                                </td>
                                <td>
                                    @if($dep->is_posted)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Posted
                                        @if($dep->posted_date)
                                        <br><small>{{ $dep->posted_date->format('d M Y') }}</small>
                                        @endif
                                    </span>
                                    @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @if(!$dep->is_posted && auth()->user()->hasAnyRole(['Accountant', 'System Admin']))
                                    <a href="{{ route('modules.accounting.fixed-assets.post-depreciation', $dep->id) }}" 
                                       class="btn btn-sm btn-success" 
                                       onclick="return confirm('Post this depreciation entry to journal?')"
                                       title="Post to Journal">
                                        <i class="fas fa-check"></i> Post
                                    </a>
                                    @endif
                                    @if($dep->journal_entry_id)
                                    <a href="{{ route('modules.accounting.journal-entries.show', $dep->journal_entry_id) }}" 
                                       class="btn btn-sm btn-info" 
                                       title="View Journal Entry">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center py-5">
                                    <i class="fas fa-info-circle text-muted fa-3x mb-3"></i>
                                    <p class="text-muted">No depreciation entries yet. Calculate depreciation from the Depreciation page.</p>
                                    <a href="{{ route('modules.accounting.fixed-assets.depreciation') }}" class="btn btn-primary">
                                        <i class="fas fa-calculator"></i> Calculate Depreciation
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="3" class="text-right">Totals:</th>
                                <th class="text-right">TZS {{ number_format($depreciations->sum('depreciation_amount'), 2) }}</th>
                                <th colspan="2" class="text-right">TZS {{ number_format($asset->accumulated_depreciation, 2) }}</th>
                                <th colspan="2" class="text-right">TZS {{ number_format($asset->net_book_value, 2) }}</th>
                                <th>
                                    <span class="badge badge-info">
                                        Posted: {{ $depreciations->where('is_posted', true)->count() }} / 
                                        Pending: {{ $depreciations->where('is_posted', false)->count() }}
                                    </span>
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Depreciation Projection -->
@if($asset->depreciation_end_date && $asset->depreciation_end_date > now())
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line"></i> Future Depreciation Projection
                </h5>
            </div>
            <div class="card-body">
                @php
                    $remainingMonths = now()->diffInMonths($asset->depreciation_end_date);
                    $monthlyDepreciation = ($asset->total_cost - $asset->salvage_value) / max($asset->useful_life_years * 12, 1);
                    $currentAccumulated = $asset->accumulated_depreciation;
                    $currentNetValue = $asset->net_book_value;
                @endphp
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Date</th>
                                <th class="text-right">Projected Depreciation</th>
                                <th class="text-right">Projected Accumulated</th>
                                <th class="text-right">Projected Net Book Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 1; $i <= min(12, $remainingMonths); $i++)
                            @php
                                $projectedDate = now()->addMonths($i);
                                $projectedAccumulated = min($currentAccumulated + ($monthlyDepreciation * $i), $asset->total_cost - $asset->salvage_value);
                                $projectedNetValue = max($asset->total_cost - $projectedAccumulated, $asset->salvage_value);
                            @endphp
                            <tr>
                                <td>{{ $i }}</td>
                                <td>{{ $projectedDate->format('M Y') }}</td>
                                <td class="text-right">TZS {{ number_format($monthlyDepreciation, 2) }}</td>
                                <td class="text-right">TZS {{ number_format($projectedAccumulated, 2) }}</td>
                                <td class="text-right">TZS {{ number_format($projectedNetValue, 2) }}</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                    @if($remainingMonths > 12)
                    <p class="text-muted text-center">
                        <small>Showing next 12 months. Total remaining: {{ $remainingMonths }} months</small>
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Depreciation Chart -->
@if($depreciations->count() > 0)
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-area"></i> Depreciation Trend
                </h5>
            </div>
            <div class="card-body">
                <canvas id="depreciationChart" class="chart-container"></canvas>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Filter by status
    $('#filterStatus').on('change', function() {
        const filterValue = $(this).val();
        $('#depreciationTable tbody tr').each(function() {
            const status = $(this).data('status');
            if (!filterValue || status === filterValue) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Depreciation Chart
    @if($depreciations->count() > 0)
    const ctx = document.getElementById('depreciationChart');
    if (ctx) {
        const labels = @json($depreciations->map(function($dep) { return $dep->depreciation_date->format('M Y'); }));
        const depreciationData = @json($depreciations->pluck('depreciation_amount'));
        const accumulatedData = @json($depreciations->pluck('accumulated_depreciation_after'));
        const netValueData = @json($depreciations->pluck('net_book_value_after'));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Monthly Depreciation',
                        data: depreciationData,
                        borderColor: 'rgb(255, 193, 7)',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Accumulated Depreciation',
                        data: accumulatedData,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'Net Book Value',
                        data: netValueData,
                        borderColor: 'rgb(40, 167, 69)',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'TZS ' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': TZS ' + context.parsed.y.toLocaleString('en-US', {minimumFractionDigits: 2});
                            }
                        }
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endpush
