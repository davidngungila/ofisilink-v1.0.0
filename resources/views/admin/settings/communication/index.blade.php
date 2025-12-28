@extends('layouts.app')

@section('title', 'Communication Settings - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #940000 0%, #a80000 50%, #940000 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
      <div class="card-body text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-3 mb-md-0">
            <h3 class="mb-2 text-white fw-bold">
              <i class="bx bx-envelope me-2"></i>Communication Settings
            </h3>
            <p class="mb-0 text-white-50 fs-6">
              Configure SMS and Email services for system notifications
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('admin.settings') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-arrow-back me-2"></i>Back to Settings
            </a>
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-home me-2"></i>Dashboard
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<br>
@endsection

@section('content')

@include('admin.settings-partials.communication')

@endsection

@push('styles')
<style>
  @keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
</style>
@endpush




