@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('breadcrumb')
    <div class="db-breadcrumb">
        <h4 class="breadcrumb-title">Staff Dashboard</h4>
        <ul class="db-breadcrumb-list">
            <li><a href="{{ route('staff.dashboard') }}"><i class="fa fa-home"></i>Home</a></li>
            <li>My Overview</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>My Petty Cash</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-primary">--</h2>
                    <p>To Retire</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>My Impress Requests</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-success">--</h2>
                    <p>In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 m-b30">
            <div class="widget-box">
                <div class="wc-title"><h4>My Tasks</h4></div>
                <div class="widget-inner text-center">
                    <h2 class="text-warning">--</h2>
                    <p>Assigned</p>
                </div>
            </div>
        </div>
    </div>
@endsection



