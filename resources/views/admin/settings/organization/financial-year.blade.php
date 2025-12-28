@extends('layouts.app')

@section('title', 'Financial Year Settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.css') }}">
<style>
    .stat-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 25px;
        text-align: center;
    }
    .stat-box.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .stat-box.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-box.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg bg-primary" style="border-radius: 15px;">
                <div class="card-body text-white p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div>
                            <h3 class="mb-2 text-white fw-bold">
                                <i class="bx bx-calendar me-2"></i>Financial Year Configuration
                            </h3>
                            <p class="mb-0 text-white-50">Manage fiscal periods and financial year settings</p>
                        </div>
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <button class="btn btn-light" onclick="toggleFinancialYearLock()">
                                <i class="bx bx-{{ $orgSettings->financial_year_locked ? 'lock' : 'lock-open' }} me-1"></i>
                                {{ $orgSettings->financial_year_locked ? 'Unlock' : 'Lock' }} Year
                            </button>
                            <a href="{{ route('admin.settings.organization') }}" class="btn btn-light">
                                <i class="bx bx-arrow-back me-1"></i>Back
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-box">
                <i class="bx bx-calendar-check fs-1 mb-2"></i>
                <h2 class="mb-0 fw-bold">{{ $orgSettings->current_financial_year ?? 'N/A' }}</h2>
                <p class="mb-0">Current Financial Year</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box info">
                <i class="bx bx-calendar fs-1 mb-2"></i>
                <h5 class="mb-0 fw-bold">{{ $orgSettings->financial_year_start_date ? \Carbon\Carbon::parse($orgSettings->financial_year_start_date)->format('M d, Y') : 'Not Set' }}</h5>
                <p class="mb-0">Start Date</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box success">
                <i class="bx bx-calendar-event fs-1 mb-2"></i>
                <h5 class="mb-0 fw-bold">{{ $orgSettings->financial_year_end_date ? \Carbon\Carbon::parse($orgSettings->financial_year_end_date)->format('M d, Y') : 'Not Set' }}</h5>
                <p class="mb-0">End Date</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box {{ $orgSettings->financial_year_locked ? 'warning' : 'success' }}">
                <i class="bx bx-{{ $orgSettings->financial_year_locked ? 'lock' : 'lock-open' }} fs-1 mb-2"></i>
                <h5 class="mb-0 fw-bold">{{ $orgSettings->financial_year_locked ? 'Locked' : 'Active' }}</h5>
                <p class="mb-0">Status</p>
            </div>
        </div>
    </div>

    <!-- Configuration Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-cog me-2 text-primary"></i>Financial Year Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form id="financialYearForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Current Financial Year <span class="text-danger">*</span></label>
                                <input type="number" name="current_financial_year" 
                                       class="form-control form-control-lg" 
                                       value="{{ $orgSettings->current_financial_year }}" 
                                       min="2000" max="2100" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Start Month <span class="text-danger">*</span></label>
                                <select name="financial_year_start_month" class="form-select form-select-lg" required>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" 
                                                {{ $orgSettings->financial_year_start_month == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Start Day <span class="text-danger">*</span></label>
                                <input type="number" name="financial_year_start_day" 
                                       class="form-control form-control-lg" 
                                       value="{{ $orgSettings->financial_year_start_day }}" 
                                       min="1" max="31" required>
                            </div>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle"></i> 
                                    <strong>Note:</strong> Changing the financial year will automatically calculate the start and end dates based on your settings.
                                </div>
                            </div>
                            <div class="col-12">
                                <input type="hidden" name="initialize_dates" value="1">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bx bx-save me-1"></i>Save & Initialize Dates
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($financialYearHistory) && count($financialYearHistory) > 0)
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-history me-2 text-info"></i>Financial Year History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Previous Year</th>
                                    <th>New Year</th>
                                    <th>Changed At</th>
                                    <th>Changed By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_reverse($financialYearHistory) as $history)
                                <tr>
                                    <td><strong>{{ $history['old_year'] }}</strong></td>
                                    <td><strong class="text-primary">{{ $history['new_year'] }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($history['changed_at'])->format('M d, Y H:i') }}</td>
                                    <td>{{ \App\Models\User::find($history['changed_by'])->name ?? 'Unknown' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 fw-bold">
                        <i class="bx bx-info-circle me-2 text-warning"></i>Information
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        The financial year is used throughout the system for reporting, budgeting, and financial calculations.
                    </p>
                    <div class="alert alert-warning">
                        <i class="bx bx-error-circle"></i>
                        <strong>Warning:</strong> Locking the financial year prevents changes during active periods.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
function toggleFinancialYearLock() {
    Swal.fire({
        title: '{{ $orgSettings->financial_year_locked ? "Unlock" : "Lock" }} Financial Year?',
        text: '{{ $orgSettings->financial_year_locked ? "This will allow changes to the financial year." : "This will prevent changes during active periods." }}',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, {{ $orgSettings->financial_year_locked ? "Unlock" : "Lock" }} it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.settings.fy.toggle-lock") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Success!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    }
                }
            });
        }
    });
}

$('#financialYearForm').on('submit', function(e) {
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
                    text: response.message || 'Financial year settings updated successfully',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            Swal.close();
            const errorMsg = xhr.responseJSON?.message || 'Error updating financial year settings';
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










