@extends('layouts.app')

@section('title', 'A/P Aging Report')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">A/P Aging Report</h4>
</div>
@endsection

@push('styles')
<style>
    .aging-current { background-color: #d4edda !important; }
    .aging-0-30 { background-color: #fff3cd !important; }
    .aging-31-60 { background-color: #ffeaa7 !important; }
    .aging-61-90 { background-color: #fdcb6e !important; }
    .aging-over-90 { background-color: #fd79a8 !important; }
    .summary-card {
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .aging-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@section('content')
<!-- Summary Cards -->
<div class="row mb-3">
    <div class="col-md-2">
        <div class="card summary-card border-left-success">
            <div class="card-body">
                <h6 class="text-muted mb-1">Current</h6>
                <h4 class="mb-0 text-success">TZS {{ number_format($summary['current'], 2) }}</h4>
                <small class="text-muted">{{ number_format(($summary['current'] / max($summary['total'], 1)) * 100, 1) }}%</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card border-left-warning">
            <div class="card-body">
                <h6 class="text-muted mb-1">0-30 Days</h6>
                <h4 class="mb-0 text-warning">TZS {{ number_format($summary['0-30'], 2) }}</h4>
                <small class="text-muted">{{ number_format(($summary['0-30'] / max($summary['total'], 1)) * 100, 1) }}%</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card border-left-warning" style="border-left-color: #ffc107 !important;">
            <div class="card-body">
                <h6 class="text-muted mb-1">31-60 Days</h6>
                <h4 class="mb-0" style="color: #ffc107;">TZS {{ number_format($summary['31-60'], 2) }}</h4>
                <small class="text-muted">{{ number_format(($summary['31-60'] / max($summary['total'], 1)) * 100, 1) }}%</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card border-left-warning" style="border-left-color: #fd7e14 !important;">
            <div class="card-body">
                <h6 class="text-muted mb-1">61-90 Days</h6>
                <h4 class="mb-0" style="color: #fd7e14;">TZS {{ number_format($summary['61-90'], 2) }}</h4>
                <small class="text-muted">{{ number_format(($summary['61-90'] / max($summary['total'], 1)) * 100, 1) }}%</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card border-left-danger">
            <div class="card-body">
                <h6 class="text-muted mb-1">Over 90 Days</h6>
                <h4 class="mb-0 text-danger">TZS {{ number_format($summary['over_90'], 2) }}</h4>
                <small class="text-muted">{{ number_format(($summary['over_90'] / max($summary['total'], 1)) * 100, 1) }}%</small>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card summary-card border-left-primary">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Outstanding</h6>
                <h4 class="mb-0 text-primary">TZS {{ number_format($summary['total'], 2) }}</h4>
                <small class="text-muted">{{ $bills->count() }} Bills</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-filter"></i> Filters & Options</h6>
                <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="{{ request()->hasAny(['date', 'vendor_id', 'search']) ? 'true' : 'false' }}">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
            <div class="collapse {{ request()->hasAny(['date', 'vendor_id', 'search']) ? 'show' : '' }}" id="filterCollapse">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-calendar"></i> As of Date</label>
                            <input type="date" name="date" class="form-control form-control-sm" value="{{ $asOfDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label"><i class="fas fa-search"></i> Search</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Bill No, Vendor..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vendor</label>
                            <select name="vendor_id" class="form-select form-select-sm">
                                <option value="">All Vendors</option>
                                @php
                                    $vendors = \App\Models\Vendor::where('is_active', true)->orderBy('name')->get();
                                @endphp
                                @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter"></i> Apply
                                </button>
                                <a href="{{ route('modules.accounting.ap.aging-report') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Table -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice-dollar"></i> Accounts Payable Aging Report
                    <small class="text-muted">- As of {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</small>
                </h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-danger" onclick="exportAgingPdf()">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button type="button" class="btn btn-sm btn-success" onclick="exportAgingExcel()">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="agingTable">
                        <thead class="table-dark">
                            <tr>
                                <th>Bill No</th>
                                <th>Vendor</th>
                                <th>Bill Date</th>
                                <th>Due Date</th>
                                <th>Days Past Due</th>
                                <th class="text-end aging-current">Current</th>
                                <th class="text-end aging-0-30">0-30 Days</th>
                                <th class="text-end aging-31-60">31-60 Days</th>
                                <th class="text-end aging-61-90">61-90 Days</th>
                                <th class="text-end aging-over-90">Over 90 Days</th>
                                <th class="text-end"><strong>Total</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bills as $bill)
                            @php
                                $daysPastDue = $bill->days_past_due ?? 0;
                                $rowClass = '';
                                if ($daysPastDue > 90) {
                                    $rowClass = 'table-danger';
                                } elseif ($daysPastDue > 60) {
                                    $rowClass = 'table-warning';
                                } elseif ($daysPastDue > 30) {
                                    $rowClass = '';
                                }
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td>
                                    <strong class="text-primary">{{ $bill->bill_no }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $bill->vendor->name ?? 'N/A' }}</strong>
                                        @if($bill->vendor)
                                        <br><small class="text-muted">{{ $bill->vendor->vendor_code ?? '' }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-calendar text-muted"></i> 
                                    {{ $bill->bill_date ? \Carbon\Carbon::parse($bill->bill_date)->format('d M Y') : 'N/A' }}
                                </td>
                                <td>
                                    <i class="fas fa-calendar-check text-muted"></i> 
                                    {{ $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('d M Y') : 'N/A' }}
                                </td>
                                <td>
                                    @if($daysPastDue > 0)
                                        <span class="aging-badge bg-{{ $daysPastDue > 90 ? 'danger' : ($daysPastDue > 60 ? 'warning' : 'info') }}">
                                            {{ $daysPastDue }} days
                                        </span>
                                    @else
                                        <span class="aging-badge bg-success">Current</span>
                                    @endif
                                </td>
                                <td class="text-end aging-current">
                                    @if($bill->aging['current'] > 0)
                                        <strong>TZS {{ number_format($bill->aging['current'], 2) }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end aging-0-30">
                                    @if($bill->aging['0-30'] > 0)
                                        <strong>TZS {{ number_format($bill->aging['0-30'], 2) }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end aging-31-60">
                                    @if($bill->aging['31-60'] > 0)
                                        <strong>TZS {{ number_format($bill->aging['31-60'], 2) }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end aging-61-90">
                                    @if($bill->aging['61-90'] > 0)
                                        <strong>TZS {{ number_format($bill->aging['61-90'], 2) }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end aging-over-90">
                                    @if($bill->aging['over_90'] > 0)
                                        <strong class="text-danger">TZS {{ number_format($bill->aging['over_90'], 2) }}</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary">TZS {{ number_format($bill->balance, 2) }}</strong>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <p class="mb-0">No outstanding bills found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="5" class="text-end"><strong>TOTAL</strong></th>
                                <th class="text-end aging-current">
                                    <strong>TZS {{ number_format($summary['current'], 2) }}</strong>
                                </th>
                                <th class="text-end aging-0-30">
                                    <strong>TZS {{ number_format($summary['0-30'], 2) }}</strong>
                                </th>
                                <th class="text-end aging-31-60">
                                    <strong>TZS {{ number_format($summary['31-60'], 2) }}</strong>
                                </th>
                                <th class="text-end aging-61-90">
                                    <strong>TZS {{ number_format($summary['61-90'], 2) }}</strong>
                                </th>
                                <th class="text-end aging-over-90">
                                    <strong>TZS {{ number_format($summary['over_90'], 2) }}</strong>
                                </th>
                                <th class="text-end">
                                    <strong class="text-white">TZS {{ number_format($summary['total'], 2) }}</strong>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function exportAgingPdf() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'pdf');
    window.location.href = '{{ route("modules.accounting.ap.aging-report") }}?' + params.toString();
}

function exportAgingExcel() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = '{{ route("modules.accounting.ap.aging-report") }}?' + params.toString();
}
</script>
@endpush
@endsection

