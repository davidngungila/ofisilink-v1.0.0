@extends('layouts.app')

@section('title', 'HR Officer Dashboard')

@section('breadcrumb')
    <div class="db-breadcrumb">
        <h4 class="breadcrumb-title">HR Dashboard</h4>
        <ul class="db-breadcrumb-list">
            <li><a href="{{ route('hr.dashboard') }}"><i class="fa fa-home"></i>Home</a></li>
            <li>HR Overview</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Leave Requests</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-primary">--</h2>
                    <p>Pending Approval</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Recruitment</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-success">--</h2>
                    <p>Open Positions</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>Employees</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-info">{{ \App\Models\User::count() }}</h2>
                    <p>Total Active</p>
                </div>
            </div>
        </div>
    </div>
@endsection



