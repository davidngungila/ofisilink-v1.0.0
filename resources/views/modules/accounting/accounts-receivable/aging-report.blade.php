@extends('layouts.app')

@section('title', 'A/R Aging Report')

@section('breadcrumb')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">A/R Aging Report</h4>
</div>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Accounts Receivable Aging Report - As of {{ \Carbon\Carbon::parse($asOfDate)->format('d M Y') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Invoice No</th>
                                <th>Customer</th>
                                <th>Due Date</th>
                                <th>Days Past Due</th>
                                <th>Current</th>
                                <th>0-30</th>
                                <th>31-60</th>
                                <th>61-90</th>
                                <th>Over 90</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_no }}</td>
                                <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                                <td>{{ $invoice->due_date->format('d M Y') }}</td>
                                <td>{{ $invoice->days_past_due }}</td>
                                <td class="text-right">{{ number_format($invoice->aging['current'], 2) }}</td>
                                <td class="text-right">{{ number_format($invoice->aging['0-30'], 2) }}</td>
                                <td class="text-right">{{ number_format($invoice->aging['31-60'], 2) }}</td>
                                <td class="text-right">{{ number_format($invoice->aging['61-90'], 2) }}</td>
                                <td class="text-right">{{ number_format($invoice->aging['over_90'], 2) }}</td>
                                <td class="text-right"><strong>{{ number_format($invoice->balance, 2) }}</strong></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No outstanding invoices</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-primary">
                            <tr>
                                <th colspan="4">TOTAL</th>
                                <th class="text-right">{{ number_format($summary['current'], 2) }}</th>
                                <th class="text-right">{{ number_format($summary['0-30'], 2) }}</th>
                                <th class="text-right">{{ number_format($summary['31-60'], 2) }}</th>
                                <th class="text-right">{{ number_format($summary['61-90'], 2) }}</th>
                                <th class="text-right">{{ number_format($summary['over_90'], 2) }}</th>
                                <th class="text-right"><strong>{{ number_format($summary['total'], 2) }}</strong></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


