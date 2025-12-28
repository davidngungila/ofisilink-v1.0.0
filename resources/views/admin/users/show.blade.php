@extends('layouts.app')

@section('title', 'User Details - Admin')

@section('breadcrumb')
    <div class="db-breadcrumb">
        <h4 class="breadcrumb-title">User Details</h4>
        <ul class="db-breadcrumb-list">
            <li><a href="{{ route('dashboard') }}"><i class="fa fa-home"></i>Home</a></li>
            <li><a href="{{ route('admin.users.index') }}">Users</a></li>
            <li>{{ $user->name }}</li>
        </ul>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-4">
            <div class="widget-box">
                <div class="wc-title">
                    <h4>User Profile</h4>
                </div>
                <div class="widget-inner">
                    <div class="text-center mb-4">
                        <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; font-size: 36px;">
                            <span>{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                        <h4>{{ $user->name }}</h4>
                        <p class="text-muted">{{ $user->email }}</p>
                        @if($user->is_active)
                        <span class="badge badge-success">Active</span>
                        @else
                        <span class="badge badge-danger">Inactive</span>
                        @endif
                    </div>

                    <dl class="row">
                        <dt class="col-sm-5">Employee ID:</dt>
                        <dd class="col-sm-7">{{ $user->employee_id }}</dd>
                        
                        <dt class="col-sm-5">Phone:</dt>
                        <dd class="col-sm-7">
                            @if($user->phone || $user->mobile)
                                {{ $user->phone ?? $user->mobile }}
                            @else
                                <span class="text-warning">
                                    <i class="bx bx-info-circle me-1"></i>No phone number registered. Please contact administrator.
                                </span>
                            @endif
                        </dd>
                        
                        <dt class="col-sm-5">Hire Date:</dt>
                        <dd class="col-sm-7">{{ $user->hire_date ? $user->hire_date->format('M d, Y') : 'N/A' }}</dd>
                        
                        <dt class="col-sm-5">Department:</dt>
                        <dd class="col-sm-7">{{ $user->primaryDepartment->name ?? 'N/A' }}</dd>
                        
                        <dt class="col-sm-5">Registered:</dt>
                        <dd class="col-sm-7">{{ $user->created_at->format('M d, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="widget-box">
                <div class="wc-title">
                    <h4>User Roles</h4>
                </div>
                <div class="widget-inner">
                    <div class="row">
                        @foreach($user->roles as $role)
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $role->display_name }}</h5>
                                    <p class="card-text text-muted small">{{ $role->description ?? 'No description' }}</p>
                                    @if($role->name == 'System Admin')
                                    <span class="badge badge-danger">Full Access</span>
                                    @else
                                    <span class="badge badge-info">Limited Access</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        @if($user->roles->count() == 0)
                        <div class="col-12 text-center py-4">
                            <p class="text-muted">No roles assigned</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="widget-box">
                <div class="wc-title">
                    <h4>Departments</h4>
                </div>
                <div class="widget-inner">
                    <div class="list-group">
                        @foreach($user->departments as $dept)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $dept->name }}</strong>
                                @if($dept->pivot->is_primary)
                                <span class="badge badge-primary ml-2">Primary</span>
                                @endif
                            </div>
                            <small class="text-muted">
                                Joined: {{ $dept->pivot->joined_at ? \Illuminate\Support\Carbon::parse($dept->pivot->joined_at)->format('M d, Y') : 'N/A' }}
                            </small>
                        </div>
                        @endforeach
                        
                        @if($user->departments->count() == 0)
                        <div class="text-center py-3">
                            <p class="text-muted">No departments assigned</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12 text-right">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="ti-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="ti-pencil-alt"></i> Edit User
            </a>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .avatar-circle {
        background: linear-gradient(135deg, #940000 0%, #a80000 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style>
@endpush

