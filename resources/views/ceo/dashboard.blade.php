@extends('layouts.app')

@section('title', 'CEO/Director Dashboard')

@section('breadcrumb')
    <div class="db-breadcrumb">
        <h4 class="breadcrumb-title">Executive Dashboard</h4>
        <ul class="db-breadcrumb-list">
            <li><a href="{{ route('ceo.dashboard') }}"><i class="fa fa-home"></i>Home</a></li>
            <li>Executive Overview</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Welcome, {{ $user->name }}</h4></div>
                <div class="widget-inner">
                    <p>Here's an overview of key metrics and approvals requiring your attention.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Pending Voucher Approvals</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-warning">--</h2>
                    <p>Awaiting CEO/Director</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Pending Impress Approvals</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-primary">--</h2>
                    <p>Awaiting CEO/Director</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Departments</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-success">{{ \App\Models\Department::count() }}</h2>
                    <p>Active Departments</p>
                </div>
            </div>
        </div>
    </div>
@endsection



