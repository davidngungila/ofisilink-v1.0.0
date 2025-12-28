@extends('layouts.app')

@section('title', 'Fixed Asset Details')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Fixed Asset Details</h4>
</div>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4>{{ $asset->name }} ({{ $asset->asset_code }})</h4>
            <div>
                <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Register
                </a>
                @if(auth()->user()->hasAnyRole(['Accountant', 'System Admin']))
                <a href="{{ route('modules.accounting.fixed-assets.edit', $asset->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Asset Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Asset Code:</th>
                                <td><strong>{{ $asset->asset_code }}</strong></td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>{{ $asset->category->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Serial Number:</th>
                                <td>{{ $asset->serial_number ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Manufacturer:</th>
                                <td>{{ $asset->manufacturer ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Model:</th>
                                <td>{{ $asset->model ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Location:</th>
                                <td>{{ $asset->location ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Department:</th>
                                <td>{{ $asset->department->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Assigned To:</th>
                                <td>{{ $asset->assignedUser->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span class="badge badge-{{ $asset->status == 'Active' ? 'success' : ($asset->status == 'Disposed' ? 'danger' : 'warning') }}">
                                        {{ $asset->status }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Purchase Date:</th>
                                <td>{{ $asset->purchase_date->format('d M Y') }}</td>
                            </tr>
                            <tr>
                                <th>Vendor:</th>
                                <td>{{ $asset->vendor->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Invoice Number:</th>
                                <td>{{ $asset->invoice_number ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                @if($asset->description)
                <div class="row">
                    <div class="col-12">
                        <strong>Description:</strong>
                        <p>{{ $asset->description }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Financial Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="50%">Purchase Cost:</th>
                                <td><strong>TZS {{ number_format($asset->purchase_cost, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Additional Costs:</th>
                                <td>TZS {{ number_format($asset->additional_costs, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Total Cost:</th>
                                <td><strong class="text-primary">TZS {{ number_format($asset->total_cost, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Salvage Value:</th>
                                <td>TZS {{ number_format($asset->salvage_value, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <th width="50%">Accumulated Depreciation:</th>
                                <td><strong class="text-warning">TZS {{ number_format($asset->accumulated_depreciation, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Net Book Value:</th>
                                <td><strong class="text-success">TZS {{ number_format($asset->net_book_value, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Current Market Value:</th>
                                <td>{{ $asset->current_market_value ? 'TZS ' . number_format($asset->current_market_value, 2) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Depreciation Method:</th>
                                <td>{{ $asset->depreciation_method }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Depreciation History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Period</th>
                                <th>Amount</th>
                                <th>Accumulated</th>
                                <th>Net Book Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asset->depreciations as $dep)
                            <tr>
                                <td>{{ $dep->depreciation_date->format('d M Y') }}</td>
                                <td>{{ $dep->period }}</td>
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No depreciation entries yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Depreciation Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless">
                    <tr>
                        <th>Method:</th>
                        <td>{{ $asset->depreciation_method }}</td>
                    </tr>
                    <tr>
                        <th>Rate:</th>
                        <td>{{ number_format($asset->depreciation_rate, 2) }}%</td>
                    </tr>
                    <tr>
                        <th>Useful Life:</th>
                        <td>{{ $asset->useful_life_years }} years</td>
                    </tr>
                    <tr>
                        <th>Start Date:</th>
                        <td>{{ $asset->depreciation_start_date->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <th>End Date:</th>
                        <td>{{ $asset->depreciation_end_date ? $asset->depreciation_end_date->format('d M Y') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('modules.accounting.fixed-assets.depreciation-schedule', $asset->id) }}" class="btn btn-info btn-block mb-2">
                    <i class="fas fa-file-alt"></i> Depreciation Schedule
                </a>
                <a href="{{ route('modules.accounting.fixed-assets.reports', ['asset_id' => $asset->id]) }}" class="btn btn-primary btn-block mb-2">
                    <i class="fas fa-chart-bar"></i> Asset Reports
                </a>
            </div>
        </div>

        @if($asset->maintenanceRecords->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Maintenance</h5>
            </div>
            <div class="card-body">
                @foreach($asset->maintenanceRecords->take(5) as $maintenance)
                <div class="mb-2">
                    <strong>{{ $maintenance->maintenance_date->format('d M Y') }}</strong><br>
                    <small>{{ $maintenance->maintenance_type }} - TZS {{ number_format($maintenance->cost, 2) }}</small>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection




