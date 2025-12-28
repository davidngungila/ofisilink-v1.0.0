@extends('layouts.app')

@section('title', 'Organization Settings - OfisiLink')

@section('breadcrumb')
<div class="row">
  <div class="col-12">
    <div class="card border-0 shadow-lg" style="border-radius: 15px; overflow: hidden; background: linear-gradient(135deg, #940000 0%, #a80000 50%, #940000 100%); background-size: 400% 400%; animation: gradientShift 15s ease infinite;">
      <div class="card-body text-white p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
          <div class="mb-3 mb-md-0">
            <h3 class="mb-2 text-white fw-bold">
              <i class="bx bx-building me-2"></i>Organization Settings
            </h3>
            <p class="mb-0 text-white-50 fs-6">
              Configure your organization's financial year, currency, and company information
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

@include('admin.settings-partials.organization', [
  'orgSettings' => $orgSettings, 
  'organizationSettings' => $organizationSettings ?? collect(),
  'financialYearHistory' => $financialYearHistory ?? []
])

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
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
// Global jQuery availability check - wait for jQuery to load
(function waitForJQuery() {
    if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
        setTimeout(waitForJQuery, 50);
        return;
    }
    
    // jQuery is now available
    document.addEventListener('DOMContentLoaded', function() {
        const $ = jQuery;
        
        $(document).ready(function() {
            // Financial Year Lock Toggle
            window.toggleFinancialYearLock = function() {
                if(!confirm('Are you sure you want to {{ $orgSettings->financial_year_locked ? "unlock" : "lock" }} the financial year?')) return;
                
                $.ajax({
                    url: '{{ route("admin.settings.fy.toggle-lock") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message,
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to toggle financial year lock',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }

            // Financial Year Form Submission
            $('#financialYearForm').on('submit', function(e) {
                e.preventDefault();
                const formData = $(this).serialize();
                
                $.ajax({
                    url: '{{ route("settings.update") }}',
                    method: 'PUT',
                    data: formData,
                    success: function(response) {
                        if(response.success) {
                            $('#financialYearModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Financial year settings updated successfully',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        let errorMsg = xhr.responseJSON?.message || 'Error updating financial year settings';
                        if(Object.keys(errors).length > 0) {
                            errorMsg = Object.values(errors).flat().join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            html: errorMsg,
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    });
})();
</script>
@endpush




