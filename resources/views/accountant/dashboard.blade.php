@extends('layouts.app')

@section('title', 'Accountant Dashboard')

@section('breadcrumb')
    <div class="db-breadcrumb">
        <h4 class="breadcrumb-title">Accountant Dashboard</h4>
        <ul class="db-breadcrumb-list">
            <li><a href="{{ route('accountant.dashboard') }}"><i class="fa fa-home"></i>Home</a></li>
            <li>Finance Overview</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Petty Cash Vouchers</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-primary">--</h2>
                    <p>Pending / Processing</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Impress Requests</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-success">--</h2>
                    <p>Awaiting Action</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Journal Entries</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-warning">--</h2>
                    <p>Created This Month</p>
                </div>
            </div>
        </div>
    </div>
@endsection



