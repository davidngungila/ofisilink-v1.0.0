@extends('layouts.app')

@section('title', 'Budgeting & Forecasting')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Budgeting & Forecasting</h4>
</div>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Budgets</h5>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Budget
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Budget Name</th>
                                <th>Type</th>
                                <th>Fiscal Year</th>
                                <th>Period</th>
                                <th>Total Budgeted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($budgets as $budget)
                            <tr>
                                <td>{{ $budget->budget_name }}</td>
                                <td>{{ $budget->budget_type }}</td>
                                <td>{{ $budget->fiscal_year }}</td>
                                <td>{{ $budget->start_date->format('d M') }} - {{ $budget->end_date->format('d M Y') }}</td>
                                <td>TZS {{ number_format($budget->total_budgeted, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $budget->status === 'Active' ? 'success' : 'secondary' }}">
                                        {{ $budget->status }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info">View</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No budgets found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $budgets->links() }}
            </div>
        </div>
    </div>
</div>
@endsection


