@extends('layouts.app')

@section('title', 'Currency & Regional Settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .currency-card {
        border-radius: 12px;
        transition: all 0.3s;
    }
    .currency-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-success" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-dollar me-2"></i>Currency & Regional Settings
                            </h3>
                            <p class="mb-0 text-white-50">Configure currency, timezone, and regional preferences</p>
                        </div>
                        <div class="mt-3 mt-md-0">
                            <a href="{{ route('admin.settings.organization') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="currencyForm">
        @csrf
        <div class="row">
            <!-- Currency Settings -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4 currency-card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-dollar me-2 text-success"></i>Currency Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Currency Code <span class="text-danger">*</span></label>
                                <input type="text" name="currency" 
                                       class="form-control form-control-lg" 
                                       value="{{ $orgSettings->currency ?? 'TZS' }}" 
                                       maxlength="3" required>
                                <small class="text-muted">e.g., USD, EUR, TZS</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Currency Symbol <span class="text-danger">*</span></label>
                                <input type="text" name="currency_symbol" 
                                       class="form-control form-control-lg" 
                                       value="{{ $orgSettings->currency_symbol ?? 'TSh' }}" 
                                       maxlength="10" required>
                                <small class="text-muted">e.g., $, €, TSh</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Symbol Position <span class="text-danger">*</span></label>
                                <select name="currency_position" class="form-select form-select-lg" required>
                                    <option value="prefix" {{ $orgSettings->currency_position == 'prefix' ? 'selected' : '' }}>Prefix (Before amount)</option>
                                    <option value="suffix" {{ $orgSettings->currency_position == 'suffix' ? 'selected' : '' }}>Suffix (After amount)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Decimal Places <span class="text-danger">*</span></label>
                                <input type="number" name="decimal_places" 
                                       class="form-control form-control-lg" 
                                       value="{{ $orgSettings->decimal_places ?? 2 }}" 
                                       min="0" max="4" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Number Format</label>
                                <input type="text" name="number_format" 
                                       class="form-control form-control-lg" 
                                       value="{{ $orgSettings->number_format ?? '1,234.56' }}" 
                                       placeholder="1,234.56">
                                <small class="text-muted">Example format: 1,234.56</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regional Settings -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4 currency-card">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold">
                            <i class="bx bx-globe me-2 text-info"></i>Regional Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Timezone <span class="text-danger">*</span></label>
                                <select name="timezone" class="form-select form-select-lg" required>
                                    @foreach(timezone_identifiers_list() as $tz)
                                        <option value="{{ $tz }}" {{ $orgSettings->timezone == $tz ? 'selected' : '' }}>
                                            {{ $tz }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Date Format <span class="text-danger">*</span></label>
                                <select name="date_format" class="form-select form-select-lg" required>
                                    <option value="Y-m-d" {{ $orgSettings->date_format == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                    <option value="d/m/Y" {{ $orgSettings->date_format == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                    <option value="m/d/Y" {{ $orgSettings->date_format == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                    <option value="d-m-Y" {{ $orgSettings->date_format == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Time Format <span class="text-danger">*</span></label>
                                <select name="time_format" class="form-select form-select-lg" required>
                                    <option value="H:i:s" {{ $orgSettings->time_format == 'H:i:s' ? 'selected' : '' }}>24 Hour (HH:MM:SS)</option>
                                    <option value="h:i:s A" {{ $orgSettings->time_format == 'h:i:s A' ? 'selected' : '' }}>12 Hour (HH:MM:SS AM/PM)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Week Start Day <span class="text-danger">*</span></label>
                                <select name="week_start_day" class="form-select form-select-lg" required>
                                    <option value="monday" {{ $orgSettings->week_start_day == 'monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="sunday" {{ $orgSettings->week_start_day == 'sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Locale</label>
                                <input type="text" name="locale" 
                                       class="form-control form-control-lg" 
                                       value="{{ $orgSettings->locale ?? 'en' }}" 
                                       placeholder="en">
                                <small class="text-muted">e.g., en, es, fr</small>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold">Country Code</label>
                                <input type="text" name="country_code" 
                                       class="form-control form-control-lg" 
                                       value="{{ $orgSettings->country_code ?? 'TZ' }}" 
                                       maxlength="2" placeholder="TZ">
                                <small class="text-muted">ISO 3166-1 alpha-2 code</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">Ready to save your changes?</h6>
                                <small class="text-muted">All currency and regional settings will be updated</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.settings.organization') }}" class="btn btn-outline-secondary btn-lg">
                                    <i class="bx bx-x me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bx bx-save me-1"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
$('#currencyForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = $(this).serialize();
    const submitBtn = $(this).find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving...');
    
    Swal.fire({
        title: 'Saving...',
        html: 'Please wait while we save your changes',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
    
    $.ajax({
        url: '{{ route("settings.update") }}',
        method: 'PUT',
        data: formData,
        success: function(response) {
            Swal.close();
            if(response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Currency and regional settings updated successfully',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.close();
            const errorMsg = xhr.responseJSON?.message || 'Error updating settings';
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: errorMsg,
                confirmButtonText: 'OK'
            });
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
});
</script>
@endpush










