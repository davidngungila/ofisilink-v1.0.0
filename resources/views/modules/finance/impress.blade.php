@extends('layouts.app')

@section('title', 'Imprest Management - Finance')

@section('breadcrumb')
    <div class="db-breadcrumb">
        <h4 class="breadcrumb-title">Imprest Management</h4>
        <ul class="db-breadcrumb-list">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-home"></i>Home</a></li>
            <li>Finance</li>
            <li>Imprest</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 m-b20">
            <div class="widget-box">
                <div class="wc-title"><h4>Request Imprest</h4></div>
                <div class="widget-inner">
                    <form class="row">
                        <div class="col-md-3 m-b10"><input class="form-control" placeholder="Purpose"></div>
                        <div class="col-md-3 m-b10"><input class="form-control" placeholder="Amount" type="number"></div>
                        <div class="col-md-3 m-b10"><input class="form-control" placeholder="Expected Return Date" type="date"></div>
                        <div class="col-md-3 m-b10"><button class="btn btn-primary btn-block" type="button">Submit Request</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="widget-box">
                <div class="wc-title"><h4>Imprest Requests</h4></div>
                <div class="widget-inner">
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>#</th><th>Purpose</th><th>Amount</th><th>Status</th><th>Request Date</th><th>Return Date</th><th></th></tr></thead>
                            <tbody>
                                <tr><td>1</td><td>Field Work</td><td>500,000</td><td><span class="badge badge-warning">Pending Approval</span></td><td>2025-10-01</td><td>2025-10-15</td><td><button class="btn btn-sm btn-outline-primary">View</button></td></tr>
                                <tr><td>2</td><td>Training</td><td>300,000</td><td><span class="badge badge-success">Approved</span></td><td>2025-09-25</td><td>2025-10-05</td><td><button class="btn btn-sm btn-outline-primary">View</button></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

