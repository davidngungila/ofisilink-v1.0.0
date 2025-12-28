@extends('layouts.app')

@section('title', 'User Management - Admin')

@section('breadcrumb')
    <div class="db-breadcrumb">
        <h4 class="breadcrumb-title">User Management</h4>
        <ul class="db-breadcrumb-list">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-home"></i>Home</a></li>
            <li>Admin</li>
            <li>Users</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12 m-b20">
            <div class="widget-box">
                <div class="wc-title"><h4>Add User</h4></div>
                <div class="widget-inner">
                    <form class="row">
                        <div class="col-md-3 m-b10"><input class="form-control" placeholder="Full Name"></div>
                        <div class="col-md-3 m-b10"><input class="form-control" placeholder="Email"></div>
                        <div class="col-md-3 m-b10"><input class="form-control" placeholder="Employee ID"></div>
                        <div class="col-md-3 m-b10"><button class="btn btn-primary btn-block" type="button">Add User</button></div>
                        <div class="col-md-6 m-b10"><select class="form-control"><option>Select Department</option><option>Finance</option><option>HR</option><option>IT</option></select></div>
                        <div class="col-md-6 m-b10"><select class="form-control"><option>Select Role</option><option>Staff</option><option>Accountant</option><option>HR Officer</option></select></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-12">
            <div class="widget-box">
                <div class="wc-title"><h4>System Users</h4></div>
                <div class="widget-inner">
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Employee ID</th><th>Department</th><th>Role</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                <tr><td>1</td><td>Super Admin</td><td>admin@ofisi.com</td><td>ADMIN001</td><td>Administration</td><td>System Admin</td><td><span class="badge badge-success">Active</span></td><td><button class="btn btn-sm btn-outline-primary">Edit</button></td></tr>
                                <tr><td>2</td><td>John Doe</td><td>john@company.com</td><td>EMP001</td><td>Finance</td><td>Accountant</td><td><span class="badge badge-success">Active</span></td><td><button class="btn btn-sm btn-outline-primary">Edit</button></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

