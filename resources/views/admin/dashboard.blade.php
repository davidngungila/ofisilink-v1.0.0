@extends('layouts.app')

@section('title', 'Admin Dashboard - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
        <h4 class="card-title mb-0">Admin Dashboard</h4>
        <p class="text-muted">Welcome to OfisiLink Administration Panel</p>
      </div>
    </div>
  </div>
</div>
@endsection

@section('content')
<div class="row">
  <!-- Welcome Card -->
  <div class="col-lg-8 mb-4 order-0">
    <div class="card">
      <div class="d-flex align-items-end row">
        <div class="col-sm-7">
          <div class="card-body">
            <h5 class="card-title text-primary">Welcome {{ auth()->user()->name }}! 🎉</h5>
            <p class="mb-4">
              You have full administrative access to the OfisiLink system. Manage users, roles, and system settings.
            </p>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Manage Users</a>
          </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img
              src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}"
              height="140"
              alt="Admin Dashboard"
              data-app-dark-img="illustrations/man-with-laptop-dark.png"
              data-app-light-img="illustrations/man-with-laptop-light.png"
            />
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Stats -->
  <div class="col-lg-4 col-md-4 order-1">
    <div class="row">
      <div class="col-lg-6 col-md-12 col-6 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img
                  src="{{ asset('assets/img/icons/unicons/chart-success.png') }}"
                  alt="chart success"
                  class="rounded"
                />
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Users</span>
            <h3 class="card-title mb-2">{{ \App\Models\User::count() }}</h3>
            <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> Active Users</small>
          </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-12 col-6 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img
                  src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}"
                  alt="Credit Card"
                  class="rounded"
                />
              </div>
            </div>
            <span>Active Roles</span>
            <h3 class="card-title text-nowrap mb-1">{{ \App\Models\Role::where('is_active', true)->count() }}</h3>
            <small class="text-success fw-semibold"><i class="bx bx-up-arrow-alt"></i> System Roles</small>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- System Overview -->
  <div class="col-12 col-lg-8 order-2 order-md-3 order-lg-2 mb-4">
    <div class="card">
      <div class="row row-bordered g-0">
        <div class="col-md-8">
          <h5 class="card-header m-0 me-2 pb-3">System Overview</h5>
          <div class="card-body">
            <div class="row">
              <div class="col-md-6">
                <h6 class="text-primary">Departments</h6>
                <p class="mb-0">{{ \App\Models\Department::where('is_active', true)->count() }} Active Departments</p>
              </div>
              <div class="col-md-6">
                <h6 class="text-primary">Permissions</h6>
                <p class="mb-0">{{ \App\Models\Permission::where('is_active', true)->count() }} System Permissions</p>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-body">
            <div class="text-center">
              <h6 class="text-primary">System Status</h6>
              <div class="mb-3">
                <span class="badge bg-label-success">Online</span>
              </div>
            </div>
            <div class="text-center fw-semibold pt-3 mb-2">100% Uptime</div>
            <div class="d-flex px-xxl-4 px-lg-2 p-4 gap-xxl-3 gap-lg-1 gap-3 justify-content-between">
              <div class="d-flex">
                <div class="me-2">
                  <span class="badge bg-label-primary p-2"><i class="bx bx-server text-primary"></i></span>
                </div>
                <div class="d-flex flex-column">
                  <small>Database</small>
                  <h6 class="mb-0">Connected</h6>
                </div>
              </div>
              <div class="d-flex">
                <div class="me-2">
                  <span class="badge bg-label-info p-2"><i class="bx bx-shield text-info"></i></span>
                </div>
                <div class="d-flex flex-column">
                  <small>Security</small>
                  <h6 class="mb-0">Active</h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="col-12 col-md-8 col-lg-4 order-3 order-md-2">
    <div class="row">
      <div class="col-6 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Users" class="rounded" />
              </div>
            </div>
            <span class="d-block mb-1">Petty Cash</span>
            <h3 class="card-title text-nowrap mb-2">{{ \App\Models\PettyCashVoucher::count() }}</h3>
            <small class="text-info fw-semibold">Total Requests</small>
          </div>
        </div>
      </div>
      <div class="col-6 mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{ asset('assets/img/icons/unicons/cc-primary.png') }}" alt="Transactions" class="rounded" />
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Pending</span>
            <h3 class="card-title mb-2">{{ \App\Models\PettyCashVoucher::whereIn('status', ['pending_accountant', 'pending_hod', 'pending_ceo'])->count() }}</h3>
            <small class="text-warning fw-semibold">Awaiting Approval</small>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Activity -->
<div class="row">
  <div class="col-md-6 col-lg-4 col-xl-4 order-0 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between pb-0">
        <div class="card-title mb-0">
          <h5 class="m-0 me-2">User Statistics</h5>
          <small class="text-muted">System Users Overview</small>
        </div>
      </div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="d-flex flex-column align-items-center gap-1">
            <h2 class="mb-2">{{ \App\Models\User::where('is_active', true)->count() }}</h2>
            <span>Active Users</span>
          </div>
        </div>
        <ul class="p-0 m-0">
          @foreach(\App\Models\Role::where('is_active', true)->take(4)->get() as $role)
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <span class="avatar-initial rounded bg-label-primary">
                <i class="bx bx-user"></i>
              </span>
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <h6 class="mb-0">{{ $role->display_name }}</h6>
                <small class="text-muted">{{ $role->users()->count() }} users</small>
              </div>
              <div class="user-progress">
                <small class="fw-semibold">{{ $role->users()->count() }}</small>
              </div>
            </div>
          </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="col-md-6 col-lg-4 order-1 mb-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title m-0 me-2">Quick Actions</h5>
      </div>
      <div class="card-body">
        <div class="d-flex flex-column gap-3">
          <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bx bx-user-plus me-2"></i>
            Add New User
          </a>
          <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">
            <i class="bx bx-list-ul me-2"></i>
            Manage Users
          </a>
          <a href="{{ route('admin.roles') }}" class="btn btn-outline-primary">
            <i class="bx bx-cog me-2"></i>
            Manage Roles
          </a>
          <a href="{{ route('admin.settings') }}" class="btn btn-outline-primary">
            <i class="bx bx-settings me-2"></i>
            System Settings
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- System Information -->
  <div class="col-md-6 col-lg-4 order-2 mb-4">
    <div class="card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="card-title m-0 me-2">System Info</h5>
      </div>
      <div class="card-body">
        <ul class="p-0 m-0">
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{ asset('assets/img/icons/unicons/paypal.png') }}" alt="Laravel" class="rounded" />
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="text-muted d-block mb-1">Laravel Version</small>
                <h6 class="mb-0">{{ app()->version() }}</h6>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{ asset('assets/img/icons/unicons/wallet.png') }}" alt="PHP" class="rounded" />
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="text-muted d-block mb-1">PHP Version</small>
                <h6 class="mb-0">{{ PHP_VERSION }}</h6>
              </div>
            </div>
          </li>
          <li class="d-flex mb-4 pb-1">
            <div class="avatar flex-shrink-0 me-3">
              <img src="{{ asset('assets/img/icons/unicons/chart.png') }}" alt="Environment" class="rounded" />
            </div>
            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
              <div class="me-2">
                <small class="text-muted d-block mb-1">Environment</small>
                <h6 class="mb-0">{{ app()->environment() }}</h6>
              </div>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize any admin-specific JavaScript here
    console.log('Admin Dashboard loaded');
});
</script>
@endpush