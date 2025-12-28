@extends('layouts.app')

@section('title', 'Asset Reports')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Asset Reports</h4>
</div>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Asset Valuation Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                        </div>
                        <div class="col-md-3">
                            <label>Report Type</label>
                            <select name="report_type" class="form-control">
                                <option value="valuation" {{ $reportType == 'valuation' ? 'selected' : '' }}>Valuation</option>
                                <option value="depreciation" {{ $reportType == 'depreciation' ? 'selected' : '' }}>Depreciation</option>
                                <option value="summary" {{ $reportType == 'summary' ? 'selected' : '' }}>Summary</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">Generate</button>
                                <button type="submit" name="export" value="pdf" class="btn btn-danger">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Summary Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h6>Total Cost</h6>
                                <h4>TZS {{ number_format($data['total_cost'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h6>Total Depreciation</h6>
                                <h4>TZS {{ number_format($data['total_depreciation'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h6>Net Book Value</h6>
                                <h4>TZS {{ number_format($data['net_book_value'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h6>Depreciation %</h6>
                                <h4>{{ $data['total_cost'] > 0 ? number_format(($data['total_depreciation'] / $data['total_cost']) * 100, 2) : 0 }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- By Category -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Assets by Category</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Count</th>
                                                <th>Total Cost</th>
                                                <th>Net Book Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['by_category'] as $item)
                                            <tr>
                                                <td>{{ $item['category'] }}</td>
                                                <td>{{ $item['count'] }}</td>
                                                <td>TZS {{ number_format($item['total_cost'], 2) }}</td>
                                                <td>TZS {{ number_format($item['net_book_value'], 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Assets by Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Count</th>
                                                <th>Total Cost</th>
                                                <th>Net Book Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data['by_status'] as $status => $item)
                                            <tr>
                                                <td>{{ $status }}</td>
                                                <td>{{ $item['count'] }}</td>
                                                <td>TZS {{ number_format($item['total_cost'], 2) }}</td>
                                                <td>TZS {{ number_format($item['net_book_value'], 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Links</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('modules.accounting.fixed-assets.depreciation-schedule') }}" class="btn btn-info">
                    <i class="fas fa-calendar"></i> Depreciation Schedule (All Assets)
                </a>
                <a href="{{ route('modules.accounting.fixed-assets.index') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Asset Register
                </a>
                <a href="{{ route('modules.accounting.fixed-assets.depreciation') }}" class="btn btn-primary">
                    <i class="fas fa-calculator"></i> Depreciation Management
                </a>
            </div>
        </div>
    </div>
</div>
@endsection




