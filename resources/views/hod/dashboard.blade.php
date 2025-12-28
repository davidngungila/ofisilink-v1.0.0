@extends('layouts.app')

@section('title', 'HOD Dashboard')

@section('breadcrumb')
    <div class="db-breadcrumb">
        <h4 class="breadcrumb-title">HOD Dashboard</h4>
        <ul class="db-breadcrumb-list">
            <li><a href="{{ route('hod.dashboard') }}"><i class="fa fa-home"></i>Home</a></li>
            <li>Department Overview</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Welcome, {{ $user->name }}</h4></div>
                <div class="widget-inner">
                    <p>Department performance and pending approvals.</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Pending Vouchers (Dept)</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-warning">--</h2>
                    <p>Awaiting HOD Review</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Pending Impress (Dept)</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-primary">--</h2>
                    <p>Awaiting HOD Review</p>
                </div>
            </div>
        </div>
    </div>
@endsection



