@extends('layouts.admin')

@section('content')
<div class="db-breadcrumb">
    <h4 class="breadcrumb-title">Dashboard</h4>
    <ul class="db-breadcrumb-list">
        <li><a href="#"><i class="fa fa-home"></i>Home</a></li>
        <li>Dashboard</li>
    </ul>
    <p class="m-t10">Welcome, {{ auth()->user()->name }}</p>
@endsection


