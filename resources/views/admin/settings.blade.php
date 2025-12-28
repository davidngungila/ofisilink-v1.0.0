@extends('layouts.app')

@section('title', 'System Settings - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #940000 0%, #a80000 50%, #940000 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
      <div class="card-body text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-3 mb-md-0">
            <h3 class="mb-2 text-white fw-bold">
              <i class="bx bx-cog me-2"></i>System Settings Management
            </h3>
            <p class="mb-0 text-white-50 fs-6">
              Configure organization, system, communication, and profile settings
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="{{ route('admin.system') }}" class="btn btn-light btn-lg shadow-sm">
              <i class="bx bx-server me-2"></i>System Health
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

<!-- Settings Overview Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-primary">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Financial Year</h6>
            <h4 class="mb-0 text-primary fw-bold">{{ $orgSettings->current_financial_year ?? 'Not Set' }}</h4>
            <small class="text-muted">
              @if($orgSettings->financial_year_locked)
                <span class="badge bg-danger">Locked</span>
              @else
                <span class="badge bg-success">Active</span>
              @endif
            </small>
          </div>
          <div class="avatar bg-primary bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-calendar fs-2 text-primary"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-success">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Currency</h6>
            <h4 class="mb-0 text-success fw-bold">{{ $orgSettings->currency ?? 'TZS' }}</h4>
            <small class="text-muted">{{ $orgSettings->currency_symbol ?? 'TSh' }}</small>
          </div>
          <div class="avatar bg-success bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-dollar fs-2 text-success"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-info">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">Timezone</h6>
            <h4 class="mb-0 text-info fw-bold">{{ $orgSettings->timezone ?? 'UTC' }}</h4>
            <small class="text-muted">{{ $orgSettings->locale ?? 'en' }}</small>
          </div>
          <div class="avatar bg-info bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-time fs-2 text-info"></i>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <div class="card border-0 shadow-sm border-start border-4 border-warning">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h6 class="text-muted mb-1">System Settings</h6>
            <h4 class="mb-0 text-warning fw-bold">{{ $systemSettings->count() ?? 0 }}</h4>
            <small class="text-muted">Configured</small>
          </div>
          <div class="avatar bg-warning bg-opacity-10 p-3 rounded-circle">
            <i class="bx bx-cog fs-2 text-warning"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Settings Navigation Cards -->
<div class="row mb-4">
  <div class="col-lg-3 col-md-6 mb-4">
    <a href="{{ route('admin.settings.organization') }}" class="text-decoration-none">
      <div class="card border-0 shadow-lg hover-lift" style="border-radius: 15px; overflow: hidden; transition: all 0.3s ease;">
        <div class="card-body p-4" style="background: linear-gradient(135deg, #940000 0%, #a80000 50%, #940000 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
          <div class="text-center text-white">
            <div class="mb-3">
              <i class="bx bx-building" style="font-size: 3.5rem; opacity: 0.9;"></i>
            </div>
            <h4 class="text-white fw-bold mb-2">Organization Settings</h4>
            <p class="text-white-50 mb-0 small">Configure financial year, currency, and company information</p>
            <div class="mt-3">
              <span class="badge bg-light text-primary px-3 py-2">
                <i class="bx bx-right-arrow-alt me-1"></i> Manage
              </span>
            </div>
          </div>
        </div>
      </div>
    </a>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <a href="{{ route('admin.settings.system.page') }}" class="text-decoration-none">
      <div class="card border-0 shadow-lg hover-lift" style="border-radius: 15px; overflow: hidden; transition: all 0.3s ease;">
        <div class="card-body p-4" style="background: linear-gradient(135deg, #940000 0%, #a80000 50%, #940000 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
          <div class="text-center text-white">
            <div class="mb-3">
              <i class="bx bx-cog" style="font-size: 3.5rem; opacity: 0.9;"></i>
            </div>
            <h4 class="text-white fw-bold mb-2">System Settings</h4>
            <p class="text-white-50 mb-0 small">Manage system-wide configuration and preferences</p>
            <div class="mt-3">
              <span class="badge bg-light text-primary px-3 py-2">
                <i class="bx bx-right-arrow-alt me-1"></i> Manage
              </span>
            </div>
          </div>
        </div>
      </div>
    </a>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <a href="{{ route('admin.settings.communication.page') }}" class="text-decoration-none">
      <div class="card border-0 shadow-lg hover-lift" style="border-radius: 15px; overflow: hidden; transition: all 0.3s ease;">
        <div class="card-body p-4" style="background: linear-gradient(135deg, #940000 0%, #a80000 50%, #940000 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
          <div class="text-center text-white">
            <div class="mb-3">
              <i class="bx bx-envelope" style="font-size: 3.5rem; opacity: 0.9;"></i>
            </div>
            <h4 class="text-white fw-bold mb-2">Communication Settings</h4>
            <p class="text-white-50 mb-0 small">Configure SMS and Email services</p>
            <div class="mt-3">
              <span class="badge bg-light text-primary px-3 py-2">
                <i class="bx bx-right-arrow-alt me-1"></i> Manage
              </span>
            </div>
          </div>
        </div>
      </div>
    </a>
  </div>

  <div class="col-lg-3 col-md-6 mb-4">
    <a href="{{ route('admin.settings.profile.page') }}" class="text-decoration-none">
      <div class="card border-0 shadow-lg hover-lift" style="border-radius: 15px; overflow: hidden; transition: all 0.3s ease;">
        <div class="card-body p-4" style="background: linear-gradient(135deg, #940000 0%, #a80000 50%, #940000 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
          <div class="text-center text-white">
            <div class="mb-3">
              <i class="bx bx-user" style="font-size: 3.5rem; opacity: 0.9;"></i>
            </div>
            <h4 class="text-white fw-bold mb-2">Profile & Image</h4>
            <p class="text-white-50 mb-0 small">Manage your profile information and photo</p>
            <div class="mt-3">
              <span class="badge bg-light text-primary px-3 py-2">
                <i class="bx bx-right-arrow-alt me-1"></i> Manage
              </span>
            </div>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>

<!-- Include Organization Modals -->
@include('admin.partials.settings-modals', ['settings' => $orgSettings])

<!-- Financial Year Modal -->
<div class="modal fade" id="financialYearModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <form id="financialYearForm">
        @csrf
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title text-white"><i class="bx bx-calendar me-2"></i>Configure Financial Year</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info border-0 shadow-sm">
            <i class="bx bx-info-circle me-2"></i>
            <strong>Important:</strong> Financial year settings are critical for accounting, reporting, and compliance. 
            Changes should only be made by authorized personnel.
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Financial Year Start Month <span class="text-danger">*</span></label>
              <select name="financial_year_start_month" class="form-select" required>
                @for($i = 1; $i <= 12; $i++)
                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                        {{ $orgSettings->financial_year_start_month == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                  {{ \Carbon\Carbon::create(null, $i, 1)->format('F') }}
                </option>
                @endfor
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Financial Year Start Day <span class="text-danger">*</span></label>
              <select name="financial_year_start_day" class="form-select" required>
                @for($i = 1; $i <= 31; $i++)
                <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                        {{ $orgSettings->financial_year_start_day == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                  {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                </option>
                @endfor
              </select>
            </div>
            <div class="col-md-12 mb-3">
              <label class="form-label fw-bold">Current Financial Year <span class="text-danger">*</span></label>
              <input type="number" name="current_financial_year" class="form-control" 
                     value="{{ $orgSettings->current_financial_year }}" min="2000" max="2100" required>
              <small class="form-text text-muted">Changing this will create a new financial year period</small>
            </div>
            <div class="col-md-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="initialize_dates" id="initialize_dates" checked>
                <label class="form-check-label" for="initialize_dates">
                  Automatically calculate start and end dates
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bx bx-save me-2"></i>Save Financial Year Settings
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('styles')
<style>
  @keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
  }
  .hover-lift:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
  }
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
@endpush
